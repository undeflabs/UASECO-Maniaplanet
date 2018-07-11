<?php
/*
 * Plugin: Checkpoints
 * ~~~~~~~~~~~~~~~~~~~
 * » Stores Checkpoint timing and displays a Checkpoint Widget with timings from
 *   local/dedimania records.
 * » Based upon plugin.checkpoints.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginCheckpoints();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginCheckpoints extends Plugin {
	public $config;

	public $checkpoints		= array();
	public $nb_checkpoints		= 0;
	public $totalcps		= 0;
	public $nb_laps			= 0;
	public $forced_laps		= 0;

	private $update_tracking	= array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2018-07-11');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Stores Checkpoint timing and displays a Checkpoint Widget with timings from local/dedimania records.');

		$this->addDependence('PluginLocalRecords',		Dependence::WANTED,	'1.0.0',	null);
		$this->addDependence('PluginDedimania',			Dependence::WANTED,	'1.0.0',	null);

		// Register functions for events
		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnectPrepare',	'onPlayerDisconnectPrepare');
		$this->registerEvent('onPlayerDisconnect',		'onPlayerDisconnect');
		$this->registerEvent('onPlayerStartCountdown',		'onPlayerStartCountdown');
		$this->registerEvent('onPlayerCheckpoint',		'onPlayerCheckpoint');
		$this->registerEvent('onPlayerFinishLap',		'onPlayerFinishLap');
		$this->registerEvent('onPlayerFinishLine',		'onPlayerFinishLine');
		$this->registerEvent('onLocalRecordsLoaded',		'onLocalRecordsLoaded');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');

		$this->registerChatCommand('cps', 			'chat_cps', 		'Sets local record checkpoints tracking', 	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/checkpoints.xml', true, true)) {
			trigger_error('[Checkpoints] Could not read/parse config file "config/checkpoints.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0]		= ((strtoupper($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0]) === 'TRUE')		? true : false);
		$this->config['COUNT_WIDGET'][0]['ENABLED'][0]			= ((strtoupper($this->config['COUNT_WIDGET'][0]['ENABLED'][0]) === 'TRUE')		? true : false);
		$this->config['TIME_DIFF_WIDGET'][0]['ENABLE_COLORBAR'][0]	= ((strtoupper($this->config['TIME_DIFF_WIDGET'][0]['ENABLE_COLORBAR'][0]) === 'TRUE')	? true : false);
		$this->config['AUTO_ENABLE_CPS'][0]				= ((strtoupper($this->config['AUTO_ENABLE_CPS'][0]) === 'TRUE')				? true : false);
		$this->config['AUTO_ENABLE_DEDICPS'][0]				= ((strtoupper($this->config['AUTO_ENABLE_DEDICPS'][0]) === 'TRUE')			? true : false);

		$this->config['CHEATER_ACTION'][0]				= (int)$this->config['CHEATER_ACTION'][0];

		$this->nb_checkpoints	= 0;
		$this->totalcps		= 0;
		$this->nb_laps		= 0;
		$this->forced_laps	= 0;

		if (isset($aseco->plugins['PluginDedimania'])) {
			$aseco->registerChatCommand('dedicps', array($this, 'chat_dedicps'), 'Sets dedimania record checkspoints tracking', Player::PLAYERS);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_cps ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check for relay server
		if ($aseco->server->isrelay) {
			$message = $this->config['MESSAGES'][0]['NOT_ON_RELAY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			// Set local checkpoints tracking
			if (strtolower($chat_parameter) === 'off') {
				$this->checkpoints[$player->login]->tracking['local_records'] = -1;
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = -1;

				$message = $this->config['MESSAGES'][0]['LOCAL_RECORDS'][0]['TRACKING_OFF'][0];
			}
			else if ($chat_parameter === '') {
				$this->checkpoints[$player->login]->tracking['local_records'] = 0;
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = -1;

				$message = $this->config['MESSAGES'][0]['LOCAL_RECORDS'][0]['TRACKING_ON'][0];
			}
			else if (is_numeric($chat_parameter) && $chat_parameter > 0) {
				$this->checkpoints[$player->login]->tracking['local_records'] = intval($chat_parameter);
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = -1;

				$message = $aseco->formatText($this->config['MESSAGES'][0]['LOCAL_RECORDS'][0]['TRACKING_RECORD'][0],
					$this->checkpoints[$player->login]->tracking['local_records']
				);
			}
			else {
				$message = $aseco->formatText($this->config['MESSAGES'][0]['LOCAL_RECORDS'][0]['NO_RECORD_FOUND'][0],
					$chat_parameter
				);
			}

			// Store settings into database
			$this->setCheckpointSettings(
				$player,
				$this->checkpoints[$player->login]->tracking['local_records'],
				$this->checkpoints[$player->login]->tracking['dedimania_records']
			);

			// Refresh Widget
			$this->handleCheckpointTracking($player->login);
		}
		else {
			$message = $this->config['MESSAGES'][0]['TRACKING_DISABLED'][0];
		}
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedicps ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check for relay server
		if ($aseco->server->isrelay) {
			$message = $this->config['MESSAGES'][0]['NOT_ON_RELAY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			// Set local checkpoints tracking
			if (strtolower($chat_parameter) === 'off') {
				$this->checkpoints[$player->login]->tracking['local_records'] = -1;
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = -1;

				$message = $this->config['MESSAGES'][0]['DEDIMANIA_RECORDS'][0]['TRACKING_OFF'][0];
			}
			else if ($chat_parameter === '') {
				$this->checkpoints[$player->login]->tracking['local_records'] = -1;
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = 0;

				$message = $this->config['MESSAGES'][0]['DEDIMANIA_RECORDS'][0]['TRACKING_ON'][0];
			}
			else if (is_numeric($chat_parameter) && $chat_parameter > 0) {
				$this->checkpoints[$player->login]->tracking['local_records'] = -1;
				$this->checkpoints[$player->login]->tracking['dedimania_records'] = intval($chat_parameter);

				$message = $aseco->formatText($this->config['MESSAGES'][0]['DEDIMANIA_RECORDS'][0]['TRACKING_RECORD'][0],
					$this->checkpoints[$player->login]->tracking['dedimania_records']
				);
			}
			else {
				$message = $aseco->formatText($this->config['MESSAGES'][0]['DEDIMANIA_RECORDS'][0]['NO_RECORD_FOUND'][0],
					$chat_parameter
				);
			}

			// Store settings into database
			$this->setCheckpointSettings(
				$player,
				$this->checkpoints[$player->login]->tracking['local_records'],
				$this->checkpoints[$player->login]->tracking['dedimania_records']
			);

			// Refresh Widget
			$this->handleCheckpointTracking($player->login);
		}
		else {
			$message = $this->config['MESSAGES'][0]['TRACKING_DISABLED'][0];
		}
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		// Clear all Checkpoints
		foreach ($aseco->server->players->player_list as $player) {
			$this->checkpoints[$player->login]->best['finish'] = PHP_INT_MAX;
			$this->checkpoints[$player->login]->best['cps'] = array();

			$this->checkpoints[$player->login]->current['cps'] = array();
			if ($aseco->server->gameinfo->mode === Gameinfo::LAPS) {
				$this->checkpoints[$player->login]->current['finish'] = 0;
			}
			else {
				$this->checkpoints[$player->login]->current['finish'] = PHP_INT_MAX;
			}
		}


		// Clean up storages
		$this->nb_checkpoints	= 0;
		$this->totalcps		= 0;
		$this->nb_laps		= 0;
		$this->forced_laps	= 0;

		$this->update_tracking = array();
		foreach ($aseco->server->players->player_list as $player) {
			$this->update_tracking[$player->login] = false;
		}


		// At Gamemode 'Laps' store the NbLabs from Dedicated-Server and NOT the
		// value from the $map_item, because they does not match the reality!
		if ($aseco->server->gameinfo->mode === Gameinfo::LAPS) {
			$this->nb_laps = $aseco->server->gameinfo->laps['ForceLapsNb'];
		}
		else {
			$this->nb_laps = $map->nb_laps;
		}

		// Store the amount of Checkpoints from the map
		$this->nb_checkpoints = $map->nb_checkpoints;

		// Store the amount of Forced Laps
		if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
			$this->forced_laps = $aseco->server->gameinfo->rounds['ForceLapsNb'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::TEAM) {
			$this->forced_laps = $aseco->server->gameinfo->team['ForceLapsNb'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::LAPS) {
			$this->forced_laps = $aseco->server->gameinfo->laps['ForceLapsNb'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
			$this->forced_laps = $aseco->server->gameinfo->cup['ForceLapsNb'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			$this->forced_laps = $aseco->server->gameinfo->chase['ForceLapsNb'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::KNOCKOUT) {
			$this->forced_laps = $aseco->server->gameinfo->knockout['ForceLapsNb'];
		}

		// Setup the total count of Checkpoints
		if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS || $aseco->server->gameinfo->mode === Gameinfo::TEAM || $aseco->server->gameinfo->mode === Gameinfo::CUP || $aseco->server->gameinfo->mode === Gameinfo::CHASE || $aseco->server->gameinfo->mode === Gameinfo::KNOCKOUT) {
			if ($this->forced_laps > 0) {
				$this->totalcps = $this->nb_checkpoints * $this->forced_laps;
			}
			else if ($this->nb_laps > 0) {
				$this->totalcps = $this->nb_checkpoints * $this->nb_laps;
			}
			else {
				$this->totalcps = $this->nb_checkpoints;
			}
		}
		else if ($this->nb_laps > 0 && $aseco->server->gameinfo->mode === Gameinfo::LAPS) {
			// In Laps.Script.txt Maps that are not multilaps are playable too,
			// in that case do not do a multiplication with 'NbLaps'!
			if ($aseco->server->maps->current->multi_lap === true) {
				$this->totalcps = $this->nb_checkpoints * $this->nb_laps;
			}
			else {
				$this->totalcps = $this->nb_checkpoints;
			}
		}
		else {
			// All other Gamemodes
			$this->totalcps = $this->nb_checkpoints;
		}

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			foreach ($aseco->server->players->player_list as $player) {
				if (isset($this->checkpoints[$player->login]->tracking) && ($this->checkpoints[$player->login]->tracking['local_records'] !== -1 || $this->checkpoints[$player->login]->tracking['dedimania_records'] !== -1)) {
					$this->handleCheckpointTracking($player->login);
				}
			}
		}

		if ($this->config['COUNT_WIDGET'][0]['ENABLED'][0] === true) {
			$logins = array();
			foreach ($aseco->server->players->player_list as $player) {
				$logins[] = $player->login;
			}
			$this->buildCounterWidget(-1, implode(',', $logins));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {
		$xml = '<manialink id="CheckpointTimeDiff"></manialink>';
		$xml .= '<manialink id="CheckpointCounter"></manialink>';
		$aseco->sendManialink($xml, false, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		// Simple reset for each Player
		foreach ($aseco->server->players->player_list as $player) {
			$this->onPlayerConnect($aseco, $player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Init
		$this->checkpoints[$player->login] = new Checkpoint();
		$this->update_tracking[$player->login] = false;

		// Set first lap reference in Laps mode
		if ($aseco->server->gameinfo->mode === Gameinfo::LAPS) {
			$this->checkpoints[$player->login]->current['finish'] = 0;
		}

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			$this->handleCheckpointTracking($player->login);
		}

		if ($this->config['COUNT_WIDGET'][0]['ENABLED'][0] === true) {
			$this->buildCounterWidget(-1, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnectPrepare ($aseco, $player) {

		// Store current settings from Player
		$this->setCheckpointSettings(
			$player,
			$this->checkpoints[$player->login]->tracking['local_records'],
			$this->checkpoints[$player->login]->tracking['dedimania_records']
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		// free up memory
		unset($this->checkpoints[$player->login]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerStartCountdown ($aseco, $params) {

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true && $this->update_tracking[$params['login']] === true) {
			$this->handleCheckpointTracking($params['login']);
			$this->update_tracking[$params['login']] = false;
		}

		// Reset for next run in TimeAttack mode
		if ($aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK) {
			$this->checkpoints[$params['login']]->current['cps'] = array();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerCheckpoint ($aseco, $params) {

		// If undefined login, bail out too
		if (!isset($this->checkpoints[$params['login']])) {
			return;
		}

		// Check for multilap Maps and unsupported Gamemodes
		if ($aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK && $aseco->server->maps->current->multi_lap === true) {
			$time = (int)$params['lap_time'];
			$cpid = (int)$params['checkpoint_in_lap'];

			// Reset
			$this->checkpoints[$params['login']]->current['cps'] = array_slice($this->checkpoints[$params['login']]->current['cps'], 0, $cpid, false);
			$this->checkpoints[$params['login']]->current['finish'] = PHP_INT_MAX;
		}
		else {
			$time = (int)$params['race_time'];
			$cpid = (int)$params['checkpoint_in_race'];
		}

		// Store current checkpoint
		$this->checkpoints[$params['login']]->current['cps'][$cpid - 1] = $time;
		ksort($this->checkpoints[$params['login']]->current['cps']);

		// Check for cheated checkpoints:
		// non-positive time, wrong index, or time less than preceding one
		if ($time <= 0 || ($cpid > 1 && $time < $this->checkpoints[$params['login']]->current['cps'][$cpid - 1])) {
			$this->processCheater($params['login'], $cpid, $time, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinishLap ($aseco, $params) {

		// If undefined login, bail out too
		if (!isset($this->checkpoints[$params['login']])) {
			return;
		}

		// Check for multilap Maps and unsupported Gamemodes
		if ($aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK && $aseco->server->maps->current->multi_lap === true) {
			$time = (int)$params['lap_time'];
			$cpid = (int)$params['checkpoint_in_lap'];
		}
		else {
			$time = (int)$params['race_time'];
			$cpid = (int)$params['checkpoint_in_race'];
		}

		// Store finish as checkpoint too
		$this->checkpoints[$params['login']]->current['cps'][$cpid - 1] = $time;
		ksort($this->checkpoints[$params['login']]->current['cps']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinishLine ($aseco, $params) {

		// If checkpoint is undefined, bail out
		if (!isset($this->checkpoints[$params['login']])) {
			return;
		}

		// Check for multilap Maps and unsupported Gamemodes
		if ($aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK && $aseco->server->maps->current->multi_lap === true) {
			$time = (int)$params['lap_time'];
			$cpid = (int)$params['checkpoint_in_lap'];
		}
		else {
			$time = (int)$params['race_time'];
			$cpid = (int)$params['checkpoint_in_race'];
		}

		// Store finish as finish time
		$this->checkpoints[$params['login']]->current['finish'] = $time;

		// Store finish as checkpoint too
		$this->checkpoints[$params['login']]->current['cps'][$cpid - 1] = $time;
		ksort($this->checkpoints[$params['login']]->current['cps']);

		// Check for improvement and update
		if ($this->checkpoints[$params['login']]->current['finish'] < $this->checkpoints[$params['login']]->best['finish']) {
			$this->checkpoints[$params['login']]->best['finish'] = $this->checkpoints[$params['login']]->current['finish'];
			$this->checkpoints[$params['login']]->best['cps'] = $this->checkpoints[$params['login']]->current['cps'];
			// store timestamp for sorting in case of equal bests
			$this->checkpoints[$params['login']]->best['timestamp'] = microtime(true);
		}

		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true && $this->update_tracking[$params['login']] === true) {
			$this->handleCheckpointTracking($params['login']);
			$this->update_tracking[$params['login']] = false;
		}

		// Check for cheated checkpoints:
		// non-positive time, wrong index, or time less than preceding one
		if ($time <= 0 || $cpid !== count($this->checkpoints[$params['login']]->current['cps']) || ($cpid > 0 && $time < end($this->checkpoints[$params['login']]->current['cps']))) {
			$this->processCheater($params['login'], $cpid, $time, true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLocalRecordsLoaded ($aseco, $records) {
		if (count($records->record_list) > 0 && $this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			// Set local checkpoint references
			foreach ($aseco->server->players->player_list as $player) {
				if (isset($this->checkpoints[$player->login]) && $this->checkpoints[$player->login]->tracking['local_records'] !== -1) {
					$this->handleCheckpointTracking($player->login);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onDedimaniaRecordsLoaded ($aseco, $records) {
		if (count($records) > 0 && $this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true) {
			// Set dedimania checkpoint references
			foreach ($aseco->server->players->player_list as $player) {
				if (isset($this->checkpoints[$player->login]) && $this->checkpoints[$player->login]->tracking['dedimania_records'] !== -1) {
					$this->handleCheckpointTracking($player->login);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLocalRecord ($aseco, $finish) {
		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true && !in_array($finish->player->login, $this->update_tracking)) {
			$this->update_tracking[$finish->player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onDedimaniaRecord ($aseco, $record) {
		if ($this->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] === true && !in_array($record['Login'], $this->update_tracking)) {
			$this->update_tracking[$record['Login']] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTimeDiffWidget ($login, $label, $replace_time = false) {
		global $aseco;

		$cp_times = '';
		if (isset($this->checkpoints[$login]->best['cps']) && count($this->checkpoints[$login]->best['cps']) > 0) {
			$cp_times = implode(',', $this->checkpoints[$login]->best['cps']);
		}
		else {
			$tmp = array();
			foreach (range(1,$this->totalcps) as $i) {
				$tmp[] = 0;
			}
			$cp_times = implode(',', $tmp);
		}

		$colorbar_status	= (($this->config['TIME_DIFF_WIDGET'][0]['ENABLE_COLORBAR'][0] === true) ? 'True' : 'False');
		$multilapmap		= (($aseco->server->maps->current->multi_lap === true) ? 'True' : 'False');
		$replace_time		= (($replace_time === true) ? 'True' : 'False');

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<time_diff_widget> @ plugin.checkpoints.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
#Include "MathLib" as MathLib
Text FormatTime (Integer MwTime) {
	declare Text FormatedTime = "0:00.000";

	if (MwTime > 0) {
		FormatedTime = TextLib::TimeToText(MwTime, True) ^ MwTime % 10;
	}
	return FormatedTime;
}
Text TimeToTextDiff (Integer _Time) {
	declare InputTime	= MathLib::Abs(_Time);
	declare Seconds		= (InputTime / 1000) % 60;
	declare Minutes		= (InputTime / 60000) % 60;
	declare Hours		= (InputTime / 3600000);

	declare Time = "";
	if (Hours > 0) {
		Time = Hours ^":"^ TextLib::FormatInteger(Minutes, 2) ^":"^ TextLib::FormatInteger(Seconds, 2);
	}
	else if (Minutes > 0) {
		Time = Minutes ^":"^ TextLib::FormatInteger(Seconds, 2);
	}
	else {
		Time = ""^ Seconds;
	}
	Time ^= "."^ TextLib::FormatInteger(InputTime % 1000, 3);

	if (Time != "") {
		return ""^ Time;
	}
	return "0.000";
}
main() {
	// Declarations
	declare CMlFrame FrameCheckpointTimeDiff	<=> (Page.GetFirstChild("CheckpointTimeDiff") as CMlFrame);
	declare CMlQuad QuadColorbar			<=> (Page.GetFirstChild("Colorbar") as CMlQuad);
	declare CMlLabel LabelCheckpointTimeDiff	<=> (Page.GetFirstChild("LabelCheckpointTimeDiff") as CMlLabel);
	declare CMlLabel LabelBestTime			<=> (Page.GetFirstChild("LabelBestTime") as CMlLabel);

	declare Integer TotalCheckpoints		= {$this->totalcps};		// Incl. Finish
	declare Boolean MultilapMap			= {$multilapmap};
	declare Integer CurrentCheckpoint		= 0;
	declare Integer LastCheckpointCount		= 0;
	declare Integer TimeDifference			= 0;
	declare Integer[] CheckpointTimes		= [{$cp_times}];
	declare Boolean EnableColorBar			= {$colorbar_status};
	declare Boolean ReplaceTime			= {$replace_time};
	declare Integer RefreshInterval			= 250;
	declare Integer RefreshTime			= CurrentTime;
	declare Text TrackingLabel			= "{$label}";
	declare Text TextColor				= "";

	declare LabelColors = [
		"Improved"	=> "\${$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_IMPROVED'][0]}",
		"Equal"		=> "\${$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_EQUAL'][0]}",
		"Worst"		=> "\${$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_WORSE'][0]}"
	];
	declare ColorBarColors = [
		"Improved"	=> TextLib::ToColor("{$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_IMPROVED'][0]}"),
		"Equal"		=> TextLib::ToColor("{$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_EQUAL'][0]}"),
		"Worst"		=> TextLib::ToColor("{$this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['TIME_WORSE'][0]}")
	];

	if (EnableColorBar == True) {
		QuadColorbar.RelativeRotation		= 180.0;
		QuadColorbar.Opacity			= 0.75;
	}
	else {
		QuadColorbar.Visible			= False;
	}
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Hide the Widget for Spectators (also temporary one)
		if (InputPlayer.IsSpawned == False) {
			FrameCheckpointTimeDiff.Hide();
		}
		else {
			FrameCheckpointTimeDiff.Show();
		}

		// On UASECO start-up TotalCheckpoints is 0, skip in that case
		if (TotalCheckpoints == 0) {
			continue;
		}

		if (CurrentTime > RefreshTime) {
			if (LastCheckpointCount != InputPlayer.CurRace.Checkpoints.count) {
				LastCheckpointCount = InputPlayer.CurRace.Checkpoints.count;
				if (MultilapMap == True) {
					CurrentCheckpoint = LastCheckpointCount - (InputPlayer.CurrentNbLaps * TotalCheckpoints);
				}
				else {
					CurrentCheckpoint = LastCheckpointCount;
				}
			}
			declare Integer CurrentRaceTime = 0;
			if (MultilapMap == True) {
				CurrentRaceTime = InputPlayer.CurCheckpointLapTime;
			}
			else {
				CurrentRaceTime = InputPlayer.CurCheckpointRaceTime;
			}

//	log(Now ^" LR: " ^ InputPlayer.User.Login ^" CurrentRaceTime: "^ CurrentRaceTime ^" : Current CP: " ^ CurrentCheckpoint ^ " of " ^ TotalCheckpoints ^ " on lap " ^ InputPlayer.CurrentNbLaps ^", CP-Times: "^ InputPlayer.CurRace.Checkpoints ^" MultiLap: "^ MultilapMap);


			if (CurrentRaceTime > 0) {
				if (CheckpointTimes.existskey(CurrentCheckpoint - 1) && CheckpointTimes[CurrentCheckpoint - 1] != 0) {
					// Setup text colors
					TimeDifference = (CheckpointTimes[CurrentCheckpoint - 1] - CurrentRaceTime);
					if (TimeDifference < 0) {
						TextColor = LabelColors["Worst"] ^"+";
						if (EnableColorBar == True) {
							QuadColorbar.Visible = True;
							QuadColorbar.Colorize = ColorBarColors["Worst"];
						}
					}
					else if (TimeDifference == 0) {
						TextColor = LabelColors["Equal"];
						if (EnableColorBar == True) {
							QuadColorbar.Visible = True;
							QuadColorbar.Colorize = ColorBarColors["Equal"];
						}
					}
					else if (TimeDifference > 0) {
						TextColor = LabelColors["Improved"] ^"-";
						if (EnableColorBar == True) {
							QuadColorbar.Visible = True;
							QuadColorbar.Colorize = ColorBarColors["Improved"];
						}
					}
				}
				else {
					TimeDifference = CurrentRaceTime;
					TextColor = "";
					if (EnableColorBar == True) {
						QuadColorbar.Visible = False;
					}
				}
			}
			else {
				TimeDifference = 0;
				TextColor = "";
				if (EnableColorBar == True) {
					QuadColorbar.Visible = False;
				}
			}

			// Change Labels
			if (CurrentCheckpoint == 0) {
				LabelCheckpointTimeDiff.Value = "\$OSTART: "^ TimeToTextDiff(0);
				if (CheckpointTimes.count == TotalCheckpoints) {
					LabelBestTime.Value = TrackingLabel ^" "^ FormatTime(CheckpointTimes[TotalCheckpoints - 1]);
				}
			}
			else if (CurrentCheckpoint > 0 && CurrentCheckpoint < TotalCheckpoints) {
				LabelCheckpointTimeDiff.Value = "\$OCP"^ CurrentCheckpoint ^": "^ TextColor ^ TimeToTextDiff(MathLib::Abs(TimeDifference));
				if (CheckpointTimes.count == TotalCheckpoints) {
					LabelBestTime.Value = TrackingLabel ^" "^ FormatTime(CheckpointTimes[CurrentCheckpoint - 1]);
				}
			}
			else if (CurrentCheckpoint == TotalCheckpoints) {
				LabelCheckpointTimeDiff.Value = "\$OFINISH: "^ TextColor ^ TimeToTextDiff(MathLib::Abs(TimeDifference));
				if (CheckpointTimes.count == TotalCheckpoints) {
					LabelBestTime.Value = TrackingLabel ^" "^ FormatTime(CheckpointTimes[CheckpointTimes.count - 1]);
				}
			}

			// Check for a time improvement
			if (ReplaceTime == True && InputPlayer.RaceState == CTmMlPlayer::ERaceState::BeforeStart) {
				if (CurrentCheckpoint != -1 && InputPlayer != Null && InputPlayer.Score != Null && InputPlayer.Score.BestRace != Null) {
					declare CpCount = 0;
					foreach (CpTime in InputPlayer.Score.BestRace.Checkpoints) {
						if (CheckpointTimes.existskey(CpCount) && (CheckpointTimes[CpCount] == 0 || CheckpointTimes[CpCount] > CpTime)) {
							CheckpointTimes[CpCount] = CpTime;
						}
//						else {
//							CheckpointTimes[CpCount] = CpTime;
//						}
						CpCount += 1;
					}
				}
			}

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;

		// Build Manialink
		$xml = '<manialink id="CheckpointTimeDiff" name="CheckpointTimeDiff" version="3">';

		$xml .= '<frame pos="175 -90" z-index="-40">';
		$xml .= '<quad pos="0 0" z-index="0" size="350 28.125" style="BgsPlayerCard" substyle="BgRacePlayerLine" id="Colorbar" hidden="true"/>';
		$xml .= '</frame>';

		$xml .= '<frame pos="'. $this->config['TIME_DIFF_WIDGET'][0]['POS_X'][0] .' '. $this->config['TIME_DIFF_WIDGET'][0]['POS_Y'][0] .'" z-index="0" id="CheckpointTimeDiff">';
		if ($this->config['TIME_DIFF_WIDGET'][0]['BACKGROUND_COLOR'][0] !== '') {
			$xml .= '<quad pos="0 0" z-index="0.01" size="40 7.5" bgcolor="'. $this->config['TIME_DIFF_WIDGET'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$xml .= '<quad pos="0 0" z-index="0.01" size="40 7.5" style="'. $this->config['TIME_DIFF_WIDGET'][0]['STYLE'][0] .'" substyle="'. $this->config['TIME_DIFF_WIDGET'][0]['SUBSTYLE'][0] .'"/>';
		}
		$xml .= '<label pos="20 -1.21875" z-index="0.02" size="50 3.75" textsize="2" scale="0.8" halign="center" textprefix="$T" textcolor="'. $this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['DEFAULT_CHECKPOINT'][0] .'" text="" id ="LabelCheckpointTimeDiff"/>';
		$xml .= '<label pos="20 -4.6875" z-index="0.02" size="50 2.625" textsize="1" scale="0.8" halign="center" textprefix="$T" textcolor="'. $this->config['TIME_DIFF_WIDGET'][0]['TEXTCOLORS'][0]['DEFAULT_BESTTIME'][0] .'" text="" id="LabelBestTime"/>';
		$xml .= '</frame>';
		$xml .= $maniascript;
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, $login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildCounterWidget ($checkpoint = -1, $login = false) {
		global $aseco;

		$xml = '<manialink id="CheckpointCounter" name="CheckpointCounter" version="3">';
		$xml .= '<frame pos="'. $this->config['COUNT_WIDGET'][0]['POS_X'][0] .' '. $this->config['COUNT_WIDGET'][0]['POS_Y'][0] .'" z-index="0" id="Frame_CheckpointCounter">';
		if ($this->config['COUNT_WIDGET'][0]['BACKGROUND_COLOR'][0] !== '') {
			$xml .= '<quad pos="0 0" z-index="0.001" size="40 7.5" bgcolor="'. $this->config['COUNT_WIDGET'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$xml .= '<quad pos="0 0" z-index="0.001" size="40 7.5" style="'. $this->config['COUNT_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['COUNT_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		}
		$xml .= '<label pos="20 -1.21875" z-index="0.01" halign="center" textsize="1" scale="0.6" textcolor="FC0F" text="" id="CheckpointLine1"/>';
		$xml .= '<label pos="20 -3.375" z-index="0.01" halign="center" textsize="2" scale="0.9" textcolor="'. $this->config['COUNT_WIDGET'][0]['TEXT_COLOR'][0] .'" text="" id="CheckpointLine2"/>';
		$xml .= '<label pos="20 -3.375" z-index="0.01" halign="center" style="TextTitle2Blink" textsize="2" scale="0.9" textcolor="'. $this->config['COUNT_WIDGET'][0]['TEXT_COLOR'][0] .'" text="" id="CheckpointLine2Blink" ScriptEvents="1" hidden="true"/>';

		$multilapmap = (($aseco->server->maps->current->multi_lap === true) ? 'True' : 'False');
		$timeattack = (($aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK) ? 'True' : 'False');

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<count_widget> @ plugin.checkpoints.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
main() {
	declare CMlFrame Container			<=> (Page.GetFirstChild("Frame_CheckpointCounter") as CMlFrame);
	Container.RelativeScale				= {$this->config['COUNT_WIDGET'][0]['SCALE'][0]};

	declare LabelCheckpointLine1			<=> (Page.GetFirstChild("CheckpointLine1") as CMlLabel);
	declare LabelCheckpointLine2			<=> (Page.GetFirstChild("CheckpointLine2") as CMlLabel);
	declare LabelCheckpointLine2Blink		<=> (Page.GetFirstChild("CheckpointLine2Blink") as CMlLabel);

	declare Integer TotalCheckpoints		= {$this->totalcps};	// Incl. Finish
	declare Boolean MultilapMap			= {$multilapmap};
	declare Boolean TimeAttack			= {$timeattack};
	declare Integer CurrentLap			= 0;			// Using own CurrentLap instead of Player.CurrentNbLaps
	declare Integer CurrentCheckpoint		= 0;
	declare Integer RefreshInterval			= 250;
	declare Integer RefreshTime			= CurrentTime;
	declare Integer BlinkEndTime			= -1;

	declare Text MessageCheckpoint			= "CHECKPOINT";
	declare Text MessageWithoutCheckpoints		= "WITHOUT CHECKPOINTS";
	declare Text MessageAllCheckpointsReached	= "ALL CHECKPOINTS REACHED";
	declare Text MessageMapSuccessfully		= "MAP SUCCESSFULLY";
	declare Text MessageFinishNow			= "\$OFinish now!";
	declare Text MessageFinished			= "\$OFinished";
	declare Text MessageFinishedNextLap		= "Finished, next Lap!";
	declare Text MessageWarmUp			= "\$OWarm-up";

	// Init first view
	if ((TotalCheckpoints-1) == 0) {
		LabelCheckpointLine1.SetText(MessageWithoutCheckpoints);
		LabelCheckpointLine2.Visible = False;
		LabelCheckpointLine2Blink.Visible = True;
		LabelCheckpointLine2Blink.SetText(MessageFinishNow);
	}
	else {
		LabelCheckpointLine1.SetText(MessageCheckpoint);
		LabelCheckpointLine2.SetText("\$O0 \$Zof\$O "^ (TotalCheckpoints - 1));
	}

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Hide the Widget for Spectators (also temporary one)
		if (InputPlayer.IsSpawned == False) {
			Container.Hide();
			continue;
		}
		else {
			Container.Show();
		}

		if (BlinkEndTime != -1 && BlinkEndTime < CurrentTime) {
			BlinkEndTime = -1;
			LabelCheckpointLine2.Visible = True;
			LabelCheckpointLine2Blink.Visible = False;
			LabelCheckpointLine1.SetText(MessageCheckpoint);
			LabelCheckpointLine2.SetText("\$O0 \$Zof\$O "^ (TotalCheckpoints - 1));
		}

		if (CurrentTime > RefreshTime) {
			foreach (Player in Players) {
				if (Player.Login != InputPlayer.User.Login) {
					continue;
				}

				declare CheckpointCounter_LastCheckpointCount for Player = -1;
				if (CheckpointCounter_LastCheckpointCount != Player.CurRace.Checkpoints.count) {
					CheckpointCounter_LastCheckpointCount = Player.CurRace.Checkpoints.count;

					if (MultilapMap == True) {
						if (CurrentCheckpoint > (TotalCheckpoints - 1)) {
							CurrentLap += 1;
						}
						CurrentCheckpoint = CheckpointCounter_LastCheckpointCount - (CurrentLap * TotalCheckpoints);
					}
					else {
						CurrentCheckpoint = CheckpointCounter_LastCheckpointCount;
					}

					// Check for respawn and reset count of current Checkpoint and Laps
					if (CurrentCheckpoint < 0 && Player.CurRace.Checkpoints.count == 0) {
						CurrentCheckpoint = 0;
						CurrentLap = 0;
					}
//					log("CPC: Current CP: " ^ CurrentCheckpoint ^ " of " ^ TotalCheckpoints ^ " on lap " ^ CurrentLap ^", Time: "^ Player.CurCheckpointRaceTime ^", CP-Times: "^ Player.CurRace.Checkpoints);

					// Reset blinking
					if (BlinkEndTime != -1) {
						BlinkEndTime = -1;
					}

					if ((CurrentCheckpoint + 1) == TotalCheckpoints) {
						if ((TotalCheckpoints - 1) == 0) {
							LabelCheckpointLine1.SetText(MessageWithoutCheckpoints);
						}
						else {
							LabelCheckpointLine1.SetText(MessageAllCheckpointsReached);
						}
						LabelCheckpointLine2.Visible = False;
						LabelCheckpointLine2Blink.Visible = True;
						LabelCheckpointLine2Blink.SetText(MessageFinishNow);
					}
					else if (CurrentCheckpoint > (TotalCheckpoints - 1)) {
						LabelCheckpointLine1.SetText(MessageMapSuccessfully);
						if ( (MultilapMap == True) && (TimeAttack == True) ) {
							LabelCheckpointLine2.Visible = False;
							LabelCheckpointLine2Blink.Visible = True;
							LabelCheckpointLine2Blink.SetText(MessageFinishedNextLap);
							BlinkEndTime = (CurrentTime + 2500);
						}
						else {
							LabelCheckpointLine2.Visible = True;
							LabelCheckpointLine2Blink.Visible = False;
							LabelCheckpointLine2.SetText(MessageFinished);
						}
					}
					else {
						LabelCheckpointLine2.Visible = True;
						LabelCheckpointLine2Blink.Visible = False;
						LabelCheckpointLine1.SetText(MessageCheckpoint);
						LabelCheckpointLine2.SetText("\$O"^ CurrentCheckpoint ^" \$Zof\$O "^ (TotalCheckpoints - 1));
					}
				}
			}

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= '</frame>';
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, $login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCheckpointSettings ($login) {
		global $aseco;

		$player = $aseco->server->players->getPlayerByLogin($login);

		$lcp = $this->getPlayerData($player, 'LocalCheckpointTracking');
		$dcp = $this->getPlayerData($player, 'DedimaniaCheckpointTracking');
		if (isset($lcp) && isset($dcp)) {
			// Setup custom settings
			$settings = array(
				'LocalCheckpointTracking'	=> $lcp,
				'DedimaniaCheckpointTracking'	=> $dcp
			);
		}
		else {
			// Setup defaults
			$settings = array(
				'LocalCheckpointTracking'	=> (($this->config['AUTO_ENABLE_CPS'][0] === true) ? 0 : -1),
				'DedimaniaCheckpointTracking'	=> (($this->config['AUTO_ENABLE_DEDICPS'][0] === true) ? 0 : -1)
			);
		}
		return $settings;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setCheckpointSettings ($player, $localcps, $dedicps) {
		$this->storePlayerData($player, 'LocalCheckpointTracking', $localcps);
		$this->storePlayerData($player, 'DedimaniaCheckpointTracking', $dedicps);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleCheckpointTracking ($login) {
		global $aseco;

		// Get personal or default Checkpoint tracking
		if ($setup = $this->getCheckpointSettings($login)) {
			$this->checkpoints[$login]->tracking['local_records'] = $setup['LocalCheckpointTracking'];
			$this->checkpoints[$login]->tracking['dedimania_records'] = $setup['DedimaniaCheckpointTracking'];
		}
		else {
			if ($this->config['AUTO_ENABLE_CPS'][0] === true) {
				$this->checkpoints[$login]->tracking['local_records'] = 0;
			}
			if ($this->config['AUTO_ENABLE_DEDICPS'][0] === true) {
				$this->checkpoints[$login]->tracking['dedimania_records'] = 0;
			}
		}


		// Check for specific record
		if ($this->checkpoints[$login]->tracking['local_records'] > 0 && isset($aseco->plugins['PluginLocalRecords']) && $aseco->plugins['PluginLocalRecords']->records->count() > 0) {
			// If specific record unavailable, use last one
			$record = $this->checkpoints[$login]->tracking['local_records'];
			if ($record > $aseco->plugins['PluginLocalRecords']->records->count()) {
				$record = $aseco->plugins['PluginLocalRecords']->records->count();
			}
			$current = $aseco->plugins['PluginLocalRecords']->records->getRecord($record - 1);

			// Check for valid checkpoints
			if (!empty($current->checkpoints) && $current->score === end($current->checkpoints)) {
				$this->checkpoints[$login]->best['finish'] = (int)$current->score;
				$this->checkpoints[$login]->best['cps'] = $current->checkpoints;
			}

			// Send Widget
			if ($current->player->login === $login) {
//				$this->buildTimeDiffWidget($login, '$<$NPersonal Best$>', true);
				$this->buildTimeDiffWidget($login, '$<$NOwn '. $record .'. Local Record ($>', false);
			}
			else {
				$this->buildTimeDiffWidget($login, '$<$N'. $record .'. Local Record$>', false);
			}
		}
		else if ($this->checkpoints[$login]->tracking['local_records'] === 0 && isset($aseco->plugins['PluginLocalRecords']) && $aseco->plugins['PluginLocalRecords']->records->count() > 0) {
			// Search for own/last record
			$record = 0;
			$current = false;
			while ($record < $aseco->plugins['PluginLocalRecords']->records->count()) {
				$current = $aseco->plugins['PluginLocalRecords']->records->getRecord($record++);
				if ($current->player->login === $login) {
					break;
				}
			}

			// Check for valid checkpoints
			if (!empty($current->checkpoints) && $current->score === end($current->checkpoints)) {
				$this->checkpoints[$login]->best['finish'] = (int)$current->score;
				$this->checkpoints[$login]->best['cps'] = $current->checkpoints;
			}

			// Send Widget
			if ($current->player->login === $login) {
//				$this->buildTimeDiffWidget($login, '$<$NPersonal Best$>', true);
				$this->buildTimeDiffWidget($login, '$<$NOwn '. $record .'. Local Record$>', false);
			}
			else {
				$this->buildTimeDiffWidget($login, '$<$N'. $record .'. Local Record$>', false);
			}
		}
		else if ($this->checkpoints[$login]->tracking['dedimania_records'] > 0 && isset($aseco->plugins['PluginDedimania']) && isset($aseco->plugins['PluginDedimania']->db['Map']) && isset($aseco->plugins['PluginDedimania']->db['Map']['Records']) && !empty($aseco->plugins['PluginDedimania']->db['Map']['Records'])) {
			// If specific record unavailable, use last one
			$record = $this->checkpoints[$login]->tracking['dedimania_records'];
			if ($record > count($aseco->plugins['PluginDedimania']->db['Map']['Records'])) {
				$record = count($aseco->plugins['PluginDedimania']->db['Map']['Records']);
			}
			$current = $aseco->plugins['PluginDedimania']->db['Map']['Records'][$record - 1];

			// Check for valid checkpoints
			if (!empty($current['Checks']) && $current['Best'] === end($current['Checks'])) {
				$this->checkpoints[$login]->best['finish'] = (int)$current['Best'];
				$this->checkpoints[$login]->best['cps'] = $current['Checks'];
			}

			// Send Widget
			if ($current['Login'] === $login) {
//				$this->buildTimeDiffWidget($login, '$<$NPersonal Best$>', true);
				$this->buildTimeDiffWidget($login, '$<$NOwn '. $record .'. Dedimania Record$>', false);
			}
			else {
				$this->buildTimeDiffWidget($login, '$<$N'. $record .'. Dedimania Record$>', false);
			}
		}
		else if ($this->checkpoints[$login]->tracking['dedimania_records'] === 0 && isset($aseco->plugins['PluginDedimania']) && isset($aseco->plugins['PluginDedimania']->db['Map']) && isset($aseco->plugins['PluginDedimania']->db['Map']['Records']) && !empty($aseco->plugins['PluginDedimania']->db['Map']['Records'])) {
			// Search for own/last record
			$record = 0;
			$current = false;
			while ($record < count($aseco->plugins['PluginDedimania']->db['Map']['Records'])) {
				$current = $aseco->plugins['PluginDedimania']->db['Map']['Records'][$record++];
				if ($current['Login'] === $login) {
					break;
				}
			}

			// Check for valid checkpoints
			if (!empty($current['Checks']) && is_array($current['Checks']) && $current['Best'] === end($current['Checks'])) {
				$this->checkpoints[$login]->best['finish'] = (int)$current['Best'];
				$this->checkpoints[$login]->best['cps'] = $current['Checks'];
			}

			// Send Widget
			if ($current['Login'] === $login) {
//				$this->buildTimeDiffWidget($login, '$<$NPersonal Best$>', true);
				$this->buildTimeDiffWidget($login, '$<$NOwn '. $record .'. Dedimania Record$>', false);
			}
			else {
				$this->buildTimeDiffWidget($login, '$<$N'. $record .'. Dedimania Record$>', false);
			}
		}
		else {
			// Send Widget
			$this->buildTimeDiffWidget($login, '$<$NPersonal Best$>', true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles cheating player.
	public function processCheater ($login, $cpid, $time, $finish = false) {
		global $aseco;

		// Collect checkpoint times
		$cps = '';
		foreach ($this->checkpoints[$login]->current['cps'] as $cp) {
			$cps .= $aseco->formatTime($cp) .'|';
		}
		$cps = substr($cps, 0, strlen($cps)-1);  // strip trailing '|'

		// Report cheat
		if ($finish === false) {
			$aseco->console('[Checkpoints] Cheat by ['. $login . '] detected! [CheckpointTimes: ('. $cps .'), LastTime: '. $aseco->formatTime($time) .', CheckpointId: '. $cpid .']');
		}
		else {
			$aseco->console('[Checkpoints] Cheat by ['. $login .'] detected! [CheckpointTimes: ('. $cps .'), FinishTime: '. $aseco->formatTime($this->checkpoints[$login]->current['finish']) .']');
		}

		// Check for valid Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			$aseco->console('[Checkpoints] processCheater(): Player object for ['. $login .'] not found, can not handle cheater!');
			return;
		}

		switch ($this->config['CHEATER_ACTION'][0]) {
			case 1:  // set to spec
				try {
					$aseco->client->query('ForceSpectator', $login, 1);

					// Allow spectator to switch back to player
					$rtn = $aseco->client->query('ForceSpectator', $login, 0);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectator');
				}

				try {
					// Force free camera mode on spectator
					$aseco->client->query('ForceSpectatorTarget', $login, '', 2);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectatorTarget');
				}

				try {
					// Free up player slot
					$aseco->client->query('SpectatorReleasePlayerSlot', $login);

					// Log console message
					$aseco->console('[Checkpoints] Cheater Player [{1}] from {2} forced into free spectator! [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
						$player->login,
						$aseco->country->iocToCountry($player->nation),
						$aseco->stripStyles($player->nickname),
						$player->ip,
						$player->ladder_rank,
						$player->id
					);

					// Show chat message
					$message = $aseco->formatText($this->config['MESSAGES'][0]['FORCED_INTO_SPECTATOR'][0],
						str_ireplace('$W', '', $player->nickname)
					);
					$aseco->sendChatMessage($message);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SpectatorReleasePlayerSlot');
				}
				break;

			case 2:  // kick
				// Log console message
				$aseco->console('[Checkpoints] Cheater Player [{1}] from {2} kicked! [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
					$player->login,
					$aseco->country->iocToCountry($player->nation),
					$aseco->stripStyles($player->nickname),
					$player->ip,
					$player->ladder_rank,
					$player->id
				);

				// Show chat message
				$message = $aseco->formatText($this->config['MESSAGES'][0]['CHEATER_KICKED'][0],
					str_ireplace('$W', '', $player->nickname)
				);
				$aseco->sendChatMessage($message);

				try {
					// Kick the cheater
					$aseco->client->query('Kick', $login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick');
				}
				break;

			case 3:  // ban (& kick)
				// Log console message
				$aseco->console('[Checkpoints] Cheater Player [{1}] from {2} banned! [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
					$player->login,
					$aseco->country->iocToCountry($player->nation),
					$aseco->stripStyles($player->nickname),
					$player->ip,
					$player->ladder_rank,
					$player->id
				);

				// Show chat message
				$message = $aseco->formatText($this->config['MESSAGES'][0]['CHEATER_BANNED'][0],
					str_ireplace('$W', '', $player->nickname)
				);
				$aseco->sendChatMessage($message);

				// Update banned IPs file
				$aseco->banned_ips[] = $player->ip;
				$aseco->writeIPs();

				try {
					// ban the cheater and also kick him
					$aseco->client->query('Ban', $player->login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Ban');
				}
				break;

			case 4:  // blacklist & kick
				// Log console message
				$aseco->console('[Checkpoints] Cheater Player [{1}] from {2} blacklisted! [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
					$player->login,
					$aseco->country->iocToCountry($player->nation),
					$aseco->stripStyles($player->nickname),
					$player->ip,
					$player->ladder_rank,
					$player->id
				);

				// Show chat message
				$message = $aseco->formatText($this->config['MESSAGES'][0]['CHEATER_BLACKLISTED'][0],
					str_ireplace('$W', '', $player->nickname)
				);
				$aseco->sendChatMessage($message);

				try {
					// Blacklist the cheater...
					$aseco->client->query('BlackList', $player->login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - BlackList');
				}

				try {
					// ...and then kick him
					$aseco->client->query('Kick', $player->login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick');
				}

				try {
					// Update blacklist file
					$filename = $aseco->settings['blacklist_file'];
					$aseco->client->query('SaveBlackList', $filename);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBlackList');
				}
				break;

			case 5:  // blacklist & ban
				// Log console message
				$aseco->console('[Checkpoints] Cheater Player [{1}] from {2} blacklisted & banned! [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
					$player->login,
					$aseco->country->iocToCountry($player->nation),
					$aseco->stripStyles($player->nickname),
					$player->ip,
					$player->ladder_rank,
					$player->id
				);

				// Show chat message
				$message = $aseco->formatText($this->config['MESSAGES'][0]['CHEATER_BLACKLISTED_AND_BANNED'][0],
					str_ireplace('$W', '', $player->nickname)
				);
				$aseco->sendChatMessage($message);

				// Update banned IPs file
				$aseco->banned_ips[] = $player->ip;
				$aseco->writeIPs();

				try {
					// Blacklist cheater...
					$aseco->client->query('BlackList', $player->login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - BlackList');
				}

				try {
					// And ban
					$aseco->client->query('Ban', $player->login);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Ban');
				}

				try {
					// Update blacklist file
					$filename = $aseco->settings['blacklist_file'];
					$aseco->client->query('SaveBlackList', $filename);
				}
				catch (Exception $exception) {
					$aseco->console('[Checkpoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBlackList');
				}
				break;

			default: // ignore
		}
	}
}

?>
