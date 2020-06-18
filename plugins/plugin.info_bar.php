<?php
/*
 * Plugin: Info Bar
 * ~~~~~~~~~~~~~~~~
 * » Displays a multi information bar.
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
	$_PLUGIN = new PluginInfoBar();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginInfoBar extends Plugin {
	private $config = array();
	private $records = array();
	private $players = array();
	private $update = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.2');
		$this->setBuild('2019-09-22');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.info_bar', 'plugin_description'));

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDonate',			Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEverySecond',			'onEverySecond');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onUnloadingMap',			'onUnloadingMap');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onLocalRecordBestLoaded',		'onLocalRecordBestLoaded');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onManiaExchangeBestLoaded',	'onManiaExchangeBestLoaded');
		$this->registerEvent('onModeScriptSettingsChanged',	'onModeScriptSettingsChanged');

		$this->registerChatCommand('infobar',			'chat_infobar',	new Message('plugin.info_bar', 'chat_infobar'),	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_infobar ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if (strtoupper($chat_parameter) === 'RELOAD') {
			$aseco->console('[InfoBar] MasterAdmin '. $player->login .' reloads the configuration.');

			// Show chat message
			$msg = new Message('plugin.info_bar', 'message_chat_infobar_reload');
			$msg->sendChatMessage($player->login);

			$this->onSync($aseco);

			// Send Info-Bar to all Players
			$this->sendInfoBar(false, true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/info_bar.xml', true, true)) {
			trigger_error('[InfoBar] Could not read/parse config file "config/info_bar.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		$this->config['manialinkid']					= 'InfoBar';

		$this->config['bar']['position']['x']				= -160.0;
		$this->config['bar']['position']['y']				= 90.0;
		$this->config['bar']['position']['z']				= 20.0;

		$this->records['local_record']					= 0;
		$this->records['dedimania_record']				= 0;
		$this->records['mania_exchange']				= 0;

		$this->update['local_record']					= false;
		$this->update['dedimania_record']				= false;
		$this->update['mania_exchange']					= false;

		// Disable parts of the UI
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('map_info', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('position', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('personal_best_and_rank', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfacePosition('live_info', array(-118.0, 75.0, 5.0));

		// Send the UI settings
		$aseco->plugins['PluginModescriptHandler']->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {

		// Check for required updates
		if ($aseco->server->gamestate !== Server::SCORE) {
			$this->updateRecords();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptSettingsChanged ($aseco) {
		$aseco->sendManiaLink($this->buildGamemode(true), false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Setup records
		$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $aseco->server->maps->current->id);
		$this->players[$player->login]['personal_best']		= $score['time'];
		$this->players[$player->login]['local_record']		= $this->records['local_record'];
		$this->players[$player->login]['dedimania_record']	= $this->records['dedimania_record'];
		$this->players[$player->login]['mania_exchange']	= $this->records['mania_exchange'];

		// Send Info-Bar to Player
		$this->sendInfoBar($player->login, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {
		if ($params['Action'] === 'DonatePlanets') {
			$aseco->releaseChatCommand('/donate '. $params['Amount'], $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		foreach ($aseco->server->players->player_list as $player) {
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $map->id);
			$this->players[$player->login]['personal_best']		= $score['time'];
			$this->players[$player->login]['local_record']		= $this->records['local_record'];
			$this->players[$player->login]['dedimania_record']	= $this->records['dedimania_record'];
			$this->players[$player->login]['mania_exchange']	= $this->records['mania_exchange'];
		}

		// Send Info-Bar to all Players
		$this->sendInfoBar(false, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		foreach ($aseco->server->players->player_list as $player) {
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $map->id);
			$this->players[$player->login]['personal_best'] = $score['time'];
		}

		// Send Info-Bar to all Players
		$this->sendInfoBar(false, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Hide from all Players
		$this->sendInfoBar(false, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $uid) {

		// Reset all records
		$this->records['local_record']		= 0;
		$this->records['dedimania_record']	= 0;
		$this->records['mania_exchange']	= 0;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecord ($aseco, $record) {

		if ($this->players[$record->player->login]['local_record'] > $record->score || $this->players[$record->player->login]['local_record'] === 0) {
			// Store new 1. Local Record at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['local_record'] = $record->score;
			}

//			// Mark for required update
//			$this->update['local_record'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecordBestLoaded ($aseco, $score) {

		// Store global for new Player connections
		$this->records['local_record'] = $score;

		// Store at each connected Player
		foreach ($aseco->server->players->player_list as $player) {
			$this->players[$player->login]['local_record'] = $score;
		}

		// Mark for required update
		$this->update['local_record'] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecord ($aseco, $record) {

		if ($this->players[$record['Login']]['dedimania_record'] > $record['Best'] || $this->players[$record['Login']]['dedimania_record'] === 0) {
			// Store new 1. Dedimania Record at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['dedimania_record'] = $record['Best'];
			}

//			// Mark for required update
//			$this->update['dedimania_record'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecordsLoaded ($aseco, $records) {

		if (count($records) > 0) {
			// Store global for new Player connections
			$this->records['dedimania_record'] = $records[0]['Best'];

			// Store at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['dedimania_record'] = $records[0]['Best'];
			}

			// Mark for required update
			$this->update['dedimania_record'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onManiaExchangeBestLoaded ($aseco, $score) {

		// Store global for new Player connections
		$this->records['mania_exchange'] = $score;

		// Store at each connected Player
		foreach ($aseco->server->players->player_list as $player) {
			$this->players[$player->login]['mania_exchange'] = $score;
		}

		// Mark for required update
		$this->update['mania_exchange'] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateRecords () {
		global $aseco;

		foreach ($aseco->server->players->player_list as $player) {
			$xml = false;
			if ($this->update['local_record'] === true) {
				$xml .= $this->buildLocalRecord($player->login, $this->players[$player->login]['local_record'], true);
			}
			if ($this->update['dedimania_record'] === true) {
				$xml .= $this->buildDedimaniaRecord($player->login, $this->players[$player->login]['dedimania_record'], true);
			}
			if ($this->update['mania_exchange'] === true) {
				$xml .= $this->buildManiaExchange($player->login, $this->players[$player->login]['mania_exchange'], true);
			}
			if ($xml !== false) {
				// Send Records
				$aseco->sendManiaLink($xml, $player->login);
			}
		}

		if ($this->update['local_record'] === true) {
			$this->update['local_record'] = false;
		}
		if ($this->update['dedimania_record'] === true) {
			$this->update['dedimania_record'] = false;
		}
		if ($this->update['mania_exchange'] === true) {
			$this->update['mania_exchange'] = false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendInfoBar ($logins = false, $show = true) {
		global $aseco;

		if ($logins === false) {
			foreach ($aseco->server->players->player_list as $player) {
				$xml = $this->buildPlayerSpectatorCount($player->login, $show);
				$xml .= $this->buildDonation($player->login, $show);
				$xml .= $this->buildCurrentRanking($player->login, $show);
				$xml .= $this->buildLastBestTime($player->login, $show);
				$xml .= $this->buildGamemode($show);
				$xml .= $this->buildLadderLimits($player->login, $show);
				$xml .= $this->buildClock($player->login, $show);
				$xml .= $this->buildPersonalBest($player->login, $this->players[$player->login]['personal_best'], $show);
				$xml .= $this->buildLocalRecord($player->login, $this->players[$player->login]['local_record'], $show);
				$xml .= $this->buildDedimaniaRecord($player->login, $this->players[$player->login]['dedimania_record'], $show);
				$xml .= $this->buildManiaExchange($player->login, $this->players[$player->login]['mania_exchange'], $show);
				$aseco->sendManiaLink($xml, $player->login);
			}
		}
		else {
			foreach (explode(',', $logins) as $login) {
				$xml = $this->buildPlayerSpectatorCount($login, $show);
				$xml .= $this->buildDonation($login, $show);
				$xml .= $this->buildCurrentRanking($login, $show);
				$xml .= $this->buildLastBestTime($login, $show);
				$xml .= $this->buildGamemode($show);
				$xml .= $this->buildLadderLimits($login, $show);
				$xml .= $this->buildClock($login, $show);
				$xml .= $this->buildPersonalBest($login, $this->players[$login]['personal_best'], $show);
				$xml .= $this->buildLocalRecord($login, $this->players[$login]['local_record'], $show);
				$xml .= $this->buildDedimaniaRecord($login, $this->players[$login]['dedimania_record'], $show);
				$xml .= $this->buildManiaExchange($login, $this->players[$login]['mania_exchange'], $show);
				$aseco->sendManiaLink($xml, $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildClock ($login, $show = true) {

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<clock> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
main() {
	declare CMlLabel LabelLocalTime <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelLocalTime") as CMlLabel);
	declare Text PrevTime = CurrentLocalDateText;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;
			LabelLocalTime.Value = TextLib::SubString(CurrentLocalDateText, 11, 20);
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Clock" name="'. $this->config['manialinkid'] .':Clock" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 299) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="21 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="21 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonClock" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['CLOCK'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['CLOCK'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="10.5 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="00:00:00" id="'. $this->config['manialinkid'] .'LabelLocalTime"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="18 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_clock'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildLadderLimits ($login, $show = true) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'LadderLimits" name="'. $this->config['manialinkid'] .':LadderLimits" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 278.75) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLadderLimits" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['LADDER_LIMITS'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LADDER_LIMITS'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="10.5 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. substr(($aseco->server->ladder_limit_min / 1000), 0, 3) .'-'. substr(($aseco->server->ladder_limit_max / 1000), 0, 3) .'k" id="'. $this->config['manialinkid'] .'LabelLadderLimits"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="18 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_ladder_limits'))->finish($login) .'"/>';
			$xml .= '</frame>';
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildGamemode ($show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<gamemode> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
main() {
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlScriptEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonGamemodeHelp") {
						ShowModeHelp();
					}
				}
				case CMlScriptEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Gamemode" name="'. $this->config['manialinkid'] .':Gamemode" version="3">';
		if ($show === true) {
			$modename = str_replace('_', ' ', $aseco->server->gameinfo->getModeName($aseco->server->gameinfo->mode));
			$limits = '---';
			switch ($aseco->server->gameinfo->mode) {
				case Gameinfo::ROUNDS:
					$limits = $aseco->server->gameinfo->rounds['PointsLimit'] .' pts.';
					break;

				case Gameinfo::TIME_ATTACK:
					$limits = $aseco->formatTime($aseco->server->gameinfo->time_attack['TimeLimit'] * 1000, false) . (($aseco->server->gameinfo->time_attack['TimeLimit'] >= 3600) ? ' h' : ' min.');
					break;

				case Gameinfo::TEAM:
					$limits = $aseco->server->gameinfo->team['PointsLimit'] .' pts.';
					break;

				case Gameinfo::LAPS:
					if ($aseco->server->gameinfo->laps['TimeLimit'] > 0) {
						$limits = $aseco->formatTime($aseco->server->gameinfo->laps['TimeLimit'] * 1000, false) .' min.';
					}
					else {
						$limits = $aseco->server->gameinfo->laps['ForceLapsNb'] .' laps';
					}
					break;

				case Gameinfo::CUP:
					$limits = $aseco->server->gameinfo->cup['PointsLimit'] .' pts.';
					break;

				case Gameinfo::TEAM_ATTACK:
					$limits = '???';
					break;

				case Gameinfo::CHASE:
					if ($aseco->server->gameinfo->chase['RoundPointsLimit'] < 0) {
						$limits = (abs($aseco->server->gameinfo->chase['RoundPointsLimit']) * $aseco->server->maps->current->nbcheckpoints) .' / '. $aseco->server->gameinfo->chase['RoundPointsGap'] .' pts.';
					}
					else {
						$limits = $aseco->server->gameinfo->chase['RoundPointsLimit'] .' / '. $aseco->server->gameinfo->chase['RoundPointsGap'] .' pts.';
					}
					break;

				case Gameinfo::KNOCKOUT:
					$limits = $aseco->server->gameinfo->knockout['RoundsPerMap'] . (($aseco->server->gameinfo->knockout['RoundsPerMap'] === 1) ? ' round' : ' rounds');
					break;

				case Gameinfo::DOPPLER:
					$limits = $aseco->formatTime($aseco->server->gameinfo->doppler['TimeLimit'] * 1000, false) .' min.';
					break;

				default:
					// Do nothing
					break;
			}

			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 251.5) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonGamemodeHelp" ScriptEvents="1"/>';
			$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['GAMEMODE'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['GAMEMODE'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['GAMEMODE'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $limits .'" id="'. $this->config['manialinkid'] .'Gamemode"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="29 2.625" textcolor="'. $this->config['GAMEMODE'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. strtoupper($modename) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildPlayerSpectatorCount ($login, $show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<player_count> and <spectator_count> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
main() {
	declare CMlLabel LabelPlayerCount <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelPlayerCount") as CMlLabel);
	declare CMlLabel LabelSpectatorCount <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelSpectatorCount") as CMlLabel);

	declare Integer CurrentMaxPlayers = {$aseco->server->options['CurrentMaxPlayers']};
	declare Integer CurrentMaxSpectators = {$aseco->server->options['CurrentMaxSpectators']};
	declare Integer SpectatorThreshold = 10;
	declare PrevTime = CurrentLocalDateText;
	declare WatchList = Integer[Text];
	declare SpectatorsList = Integer[Text];
	declare Integer PlayerCount = 0;
	declare Integer SpectatorCount = 0;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			foreach (Player in Players) {
				if (Player.IsSpawned == False) {
					if (WatchList.existskey(Player.User.Login) == True) {
						WatchList[Player.User.Login] = WatchList[Player.User.Login] + 1;
					}
					else {
						WatchList[Player.User.Login] = 1;
					}
				}
				else {
					WatchList[Player.User.Login] = 0;
				}

				if (WatchList[Player.User.Login] > SpectatorThreshold) {
					SpectatorsList[Player.User.Login] = 0;
				}
				else {
					if (SpectatorsList.existskey(Player.User.Login) == True) {
						SpectatorsList.removekey(Player.User.Login);
					}
				}
			}

			// Update labels
			SpectatorCount = SpectatorsList.count;
			PlayerCount = (Players.count - SpectatorCount);
			if (PlayerCount >= CurrentMaxPlayers) {
				LabelPlayerCount.SetText("\$FA0"^ PlayerCount ^"/"^ CurrentMaxPlayers);
			}
			else {
				LabelPlayerCount.SetText(PlayerCount ^"/"^ CurrentMaxPlayers);
			}
			if (SpectatorCount >= CurrentMaxPlayers) {
				LabelSpectatorCount.SetText("\$FA0"^ SpectatorCount ^"/"^ CurrentMaxSpectators);
			}
			else {
				LabelSpectatorCount.SetText(SpectatorCount ^"/"^ CurrentMaxSpectators);
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerSpectatorCount" name="'. $this->config['manialinkid'] .':PlayerSpectatorCount" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. $this->config['bar']['position']['x'] .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonPlayerCount" ScriptEvents="1"/>';
//			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['PLAYER_COUNT'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['PLAYER_COUNT'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="10.5 2.625" textcolor="'. $this->config['PLAYER_COUNT'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxPlayers'] .'" id="'. $this->config['manialinkid'] .'LabelPlayerCount"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="18 2.625" textcolor="'. $this->config['PLAYER_COUNT'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_player_count'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 20.25) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonSpectatorCount" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['SPECTATOR_COUNT'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['SPECTATOR_COUNT'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="10.5 2.625" textcolor="'. $this->config['SPECTATOR_COUNT'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxSpectators'] .'" id="'. $this->config['manialinkid'] .'LabelSpectatorCount"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="18 2.625" textcolor="'. $this->config['SPECTATOR_COUNT'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_spectator_count'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildDonation ($login, $show = true) {
		global $aseco;

		// Get the min. amount of donation
		$mindonation = $aseco->plugins['PluginDonate']->mindonation;

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<donation> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
#Include "AnimLib" as AnimLib
Void WipeIn (Text ChildId, Vec2 EndSize) {
	declare CMlFrame Container	<=> (Page.GetFirstChild(ChildId) as CMlFrame);
//	declare Vec2 OriginalPosition	= Container.RelativePosition_V3;
//	declare Vec2 OriginalZIndex	= Container.ZIndex;

	Container.Hide();
//	Container.RelativePosition_V3.X = Container.RelativePosition_V3.X + (EndSize.X / 2);
	Container.RelativeScale = 0.0;
	Container.Show();

	declare Real AnimDuration = 500.0;
	declare Real AnimFactor = 0.0;
	declare Integer AnimStartTime = Now;
	while ((Now - AnimStartTime * 1.0) < AnimDuration) {
		AnimFactor = AnimLib::Ease("BounceOut", ((Now - AnimStartTime) * 1.0), 0.0, 1.0, AnimDuration);

//		Container.RelativePosition_V3.X = Container.RelativePosition_V3.X - ((EndSize.X / 2) / (AnimDuration / 10));
		Container.RelativeScale = AnimFactor;
		yield;
	}
//	Container.RelativePosition_V3.X = OriginalPosition.X;
}
main() {
	declare CMlFrame DropDownDonation <=> (Page.GetFirstChild("DropDownDonation") as CMlFrame);
	declare CMlEntry EntryDonate <=> (Page.GetFirstChild("EntryDonate") as CMlEntry);
	declare CMlLabel LabelTooltipDonations <=> (Page.GetFirstChild("LabelTooltipDonations") as CMlLabel);

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlScriptEvent::Type::MouseClick : {
					LabelTooltipDonations.Hide();
					if (Event.ControlId == "ButtonDonation") {
						if (DropDownDonation.Visible == True) {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							DropDownDonation.Hide();
						}
						else {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::ShowMenu, 0, 1.0);
							WipeIn("DropDownDonation", <28.0, 28.6>);
						}
					}
					else if (Event.ControlId == "ButtonSendDonation") {
						if (TextLib::ToInteger(EntryDonate.Value) >= {$mindonation}) {
							TriggerPageAction("PluginInfoBar?Action=DonatePlanets&Amount="^ EntryDonate.Value);
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							DropDownDonation.Hide();
						}
						else {
							LabelTooltipDonations.Show();
							EntryDonate.Focus();
						}
					}
				}
				case CMlScriptEvent::Type::MouseOver : {
					LabelTooltipDonations.Hide();
					if (Event.ControlId == "ButtonDonation") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
				case CMlScriptEvent::Type::EntrySubmit : {
					LabelTooltipDonations.Hide();
					if (Event.ControlId == "EntryDonate") {
						if (TextLib::ToInteger(EntryDonate.Value) >= {$mindonation}) {
							TriggerPageAction("PluginInfoBar?Action=DonatePlanets&Amount="^ EntryDonate.Value);
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							DropDownDonation.Hide();
						}
						else {
							LabelTooltipDonations.Show();
							EntryDonate.Focus();
						}
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Donation" name="'. $this->config['manialinkid'] .':Donation" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 60.75) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonDonation" ScriptEvents="1"/>';
			$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['DONATION'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['DONATION'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['DONATION'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. (new Message('plugin.info_bar', 'label_donate_heading'))->finish($login) .'"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="24 2.625" textcolor="'. $this->config['DONATION'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_donate_description'))->finish($login) .'"/>';
			$xml .= '</frame>';

			// Build onClick full Widget
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 60.75) .' '. ($this->config['bar']['position']['y'] - 7.05) .'" z-index="'. ($this->config['bar']['position']['z'] + 0.02) .'" id="DropDownDonation" hidden="true">';
			$xml .= '<quad pos="0 -0.1" z-index="0.02" size="55.6 12" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.04" size="55.6 0.2" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<label pos="3 -1.7" z-index="0.04" size="55 2.625" textcolor="FFFF" textsize="1" scale="0.9" text="'. (new Message('plugin.info_bar', 'label_donate_intro'))->finish($login) .'"/>';
			$xml .= '<quad pos="3 -7.8" z-index="0.03" size="19 5.1" valign="center" style="Bgs1InRace" substyle="BgColorContour"/>';
			$xml .= '<entry pos="12.45 -7.8" z-index="0.05" size="17.9 4" halign="center" valign="center2" style="TextValueSmall" textsize="1" textcolor="FFFF" default="500" autonewline="0" id="EntryDonate" ScriptEvents="1"/>';
			$xml .= '<label pos="38.5 -7.9" z-index="0.05" size="12 4.5" halign="center" valign="center" textsize="1" style="CardButtonSmallS" text="'. (new Message('plugin.info_bar', 'label_donate_button'))->finish($login) .'" id="ButtonSendDonation" ScriptEvents="1"/>';

			// Build Tooltip
			$message_min_amount = new Message('plugin.info_bar', 'label_donate_minimum_amount');
			$message_min_amount->addPlaceholders($mindonation);
			$xml .= '<label pos="0 -13" z-index="0.05" size="62 2.625" textsize="1" scale="0.9" textcolor="FF0F" text="'. $message_min_amount->finish($login) .'" hidden="true" id="LabelTooltipDonations"/>';
			$xml .= '</frame>';

			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildCurrentRanking ($login, $show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<current_ranking> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
main() {
	declare CMlLabel LabelCurrentRanking <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelCurrentRanking") as CMlLabel);

	// Turn off some ClientUI parts (we replace)
	ClientUI.OverlayHidePosition		= True;

	declare Text PrevTime = CurrentLocalDateText;
	declare Integer CurrentPlayerRank = 0;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			CurrentPlayerRank = 0;
			foreach (Score in Scores) {
				CurrentPlayerRank += 1;

				// Did the Player already finished the Map?
				if (Score.User.Login == InputPlayer.User.Login) {
					if (Score != Null && (Score.BestRace.Time > 0 || Score.Points > 0)) {
						LabelCurrentRanking.SetText(CurrentPlayerRank ^"/"^ Players.count);
					}
					else {
						LabelCurrentRanking.SetText("0/"^ Players.count);
					}
				}
			}
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlScriptEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonCurrentRanking") {
						TriggerPageAction("{$this->config['CURRENT_RANKING'][0]['ACTION'][0]}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
				}
				case CMlScriptEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'CurrentRanking" name="'. $this->config['manialinkid'] .':CurrentRanking" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 40.5) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['CURRENT_RANKING'][0]['ACTION'][0])) {
				list($plugin, $unused) = explode('?', $this->config['CURRENT_RANKING'][0]['ACTION'][0]);
				if (isset($aseco->plugins[$plugin])) {
					$xml .= '<quad pos="0 0" z-index="0.02" size="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonCurrentRanking" ScriptEvents="1"/>';
					$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
				}
			}
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['CURRENT_RANKING'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['CURRENT_RANKING'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="10.5 2.625" textcolor="'. $this->config['CURRENT_RANKING'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/0" id="'. $this->config['manialinkid'] .'LabelCurrentRanking"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="18 2.625" textcolor="'. $this->config['CURRENT_RANKING'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_current_ranking'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildLastBestTime ($login, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			return;
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<best_time> and <last_time> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
Text FormatTime (Integer _Time) {
	declare Text Time = "0:00.000";

	if (_Time > 0) {
		declare Text _TimeText = TextLib::ToText(_Time);
		declare Hundredth = TextLib::ToInteger(TextLib::SubString(_TimeText, TextLib::Length(_TimeText)-3, 3));
		declare Seconds = (_Time / 1000) % 60;
		declare Minutes = (_Time / 60000) % 60;
		declare Hours = (_Time / 3600000);

		if (Hours > 0) {
			Time = Hours ^":"^ TextLib::FormatInteger(Minutes, 2) ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
		else {
			Time = Minutes ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
	}
	return Time;
}
main() {
	declare CMlLabel LabelBestTime <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelBestTime") as CMlLabel);
	declare CMlLabel LabelLastTime <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelLastTime") as CMlLabel);

	declare Integer NextUpdate = CurrentTime;
	declare Integer LastBestRace = 0;
	declare Integer LastPrevRace = 0;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				if (Player.User.Login == InputPlayer.User.Login) {
					if (Player.Score != Null) {
						if (Player.Score.BestRace.Time > 0 && Player.Score.BestRace.Time != LastBestRace) {
							LastBestRace = Player.Score.BestRace.Time;
							LabelBestTime.SetText(FormatTime(LastBestRace));
						}
						if (Player.Score.PrevRace.Time > 0 && Player.Score.PrevRace.Time != LastPrevRace) {
							LastPrevRace = Player.Score.PrevRace.Time;
							LabelLastTime.SetText(FormatTime(LastPrevRace));
						}
					}
					break;
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerLastBestTime" name="'. $this->config['manialinkid'] .':PlayerLastBestTime" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 88) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLastTime" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['LAST_TIME'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LAST_TIME'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="17.5 2.625" textcolor="'. $this->config['LAST_TIME'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelLastTime"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="29 2.625" textcolor="'. $this->config['LAST_TIME'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_last_time'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 115.25) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonBestTime" ScriptEvents="1"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.04" size="5.25 5.25" modulatecolor="'. $this->config['BEST_TIME'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['BEST_TIME'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.04" size="17.5 2.625" textcolor="'. $this->config['BEST_TIME'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelBestTime"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.04" size="29 2.625" textcolor="'. $this->config['BEST_TIME'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_best_time'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildPersonalBest ($login, $score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			return;
		}

		$HandlePendingEvents = 'True';
		if (empty($this->config['PERSONAL_BEST'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<personal_best> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
Text FormatTime (Integer _Time) {
	declare Text Time = "0:00.000";

	if (_Time > 0) {
		declare Text _TimeText = TextLib::ToText(_Time);
		declare Hundredth = TextLib::ToInteger(TextLib::SubString(_TimeText, TextLib::Length(_TimeText)-3, 3));
		declare Seconds = (_Time / 1000) % 60;
		declare Minutes = (_Time / 60000) % 60;
		declare Hours = (_Time / 3600000);

		if (Hours > 0) {
			Time = Hours ^":"^ TextLib::FormatInteger(Minutes, 2) ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
		else {
			Time = Minutes ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
	}
	return Time;
}
main() {
	declare CMlLabel LabelPersonalBest <=> (Page.GetFirstChild("LabelPersonalBest") as CMlLabel);
	declare Integer NextUpdate = CurrentTime;
	declare Integer PersonalBestScore = {$score};
	declare Boolean HandlePendingEvents = {$HandlePendingEvents};

	// Turn off some ClientUI parts (we replace)
	ClientUI.OverlayHidePersonnalBestAndRank	= True;

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				if (Player.Score != Null && Player.Score.BestRace.Time > 0) {
					// Check for improved PersonalBest of InputPlayer
					if (Player.User.Login == InputPlayer.User.Login && (Player.Score.BestRace.Time < PersonalBestScore || PersonalBestScore == 0)) {
						PersonalBestScore = Player.Score.BestRace.Time;
						LabelPersonalBest.SetText(FormatTime(PersonalBestScore));
					}
				}
//log("best: "^ FormatTime(Player.Score.BestRace.Time) ^", prev: "^ FormatTime(Player.Score.PrevRace.Time));
//log("State: "^ Player.RaceState ^", current: "^ FormatTime(Player.CurRace.Time) ^", PB: "^ FormatTime(PersonalBestScore));
			}

		}

		if (HandlePendingEvents == True) {
			// Check for MouseEvents
			foreach (Event in PendingEvents) {
				switch (Event.Type) {
					case CMlScriptEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonPersonalBest") {
							TriggerPageAction("{$this->config['PERSONAL_BEST'][0]['ACTION'][0]}");
							Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						}
					}
					case CMlScriptEvent::Type::MouseOver : {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PersonalBest" name="'. $this->config['manialinkid'] .':PersonalBest" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 142.5) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['PERSONAL_BEST'][0]['ACTION'][0])) {
				list($plugin, $unused) = explode('?', $this->config['PERSONAL_BEST'][0]['ACTION'][0]);
				if (isset($aseco->plugins[$plugin])) {
					$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonPersonalBest" ScriptEvents="1"/>';
					$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
				}
			}
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['PERSONAL_BEST'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['PERSONAL_BEST'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['PERSONAL_BEST'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelPersonalBest"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="29 2.625" textcolor="'. $this->config['PERSONAL_BEST'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_personal_best'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildLocalRecord ($login, $score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			return;
		}


		$HandlePendingEvents = 'True';
		if (empty($this->config['LOCAL_RECORD'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<local_record> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
Text FormatTime (Integer _Time) {
	declare Text Time = "0:00.000";

	if (_Time > 0) {
		declare Text _TimeText = TextLib::ToText(_Time);
		declare Hundredth = TextLib::ToInteger(TextLib::SubString(_TimeText, TextLib::Length(_TimeText)-3, 3));
		declare Seconds = (_Time / 1000) % 60;
		declare Minutes = (_Time / 60000) % 60;
		declare Hours = (_Time / 3600000);

		if (Hours > 0) {
			Time = Hours ^":"^ TextLib::FormatInteger(Minutes, 2) ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
		else {
			Time = Minutes ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
	}
	return Time;
}
main() {
	declare CMlLabel LabelLocalRecord <=> (Page.GetFirstChild("LabelLocalRecord") as CMlLabel);
	declare Integer NextUpdate = CurrentTime;
	declare Integer LocalRecordScore = {$score};
	declare Boolean HandlePendingEvents = {$HandlePendingEvents};
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				if (Player.Score != Null && Player.Score.BestRace.Time > 0) {
					// Check for improved LocalRecord
					if (Player.Score.BestRace.Time < LocalRecordScore || LocalRecordScore == 0) {
						LocalRecordScore = Player.Score.BestRace.Time;
						LabelLocalRecord.SetText(FormatTime(LocalRecordScore));
					}
				}
//log("best: "^ FormatTime(Player.Score.BestRace.Time) ^", prev: "^ FormatTime(Player.Score.PrevRace.Time));
//log("State: "^ Player.RaceState ^", current: "^ FormatTime(Player.CurRace.Time) ^", PB: "^ FormatTime(TextLib::ToInteger(RecordScores[0]["Score"])));
			}

		}

		if (HandlePendingEvents == True) {
			// Check for MouseEvents
			foreach (Event in PendingEvents) {
				switch (Event.Type) {
					case CMlScriptEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonLocalRecord") {
							TriggerPageAction("{$this->config['LOCAL_RECORD'][0]['ACTION'][0]}");
							Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						}
					}
					case CMlScriptEvent::Type::MouseOver : {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'LocalRecord" name="'. $this->config['manialinkid'] .':LocalRecord" version="3">';
		if ($show == true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 169.75) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['LOCAL_RECORD'][0]['ACTION'][0])) {
				list($plugin, $unused) = explode('?', $this->config['LOCAL_RECORD'][0]['ACTION'][0]);
				if (isset($aseco->plugins[$plugin])) {
					$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLocalRecord" ScriptEvents="1"/>';
					$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
				}
			}
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['LOCAL_RECORD'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LOCAL_RECORD'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['LOCAL_RECORD'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelLocalRecord"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="29 2.625" textcolor="'. $this->config['LOCAL_RECORD'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_local_record'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildDedimaniaRecord ($login, $score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			return;
		}

		$HandlePendingEvents = 'True';
		if (empty($this->config['DEDIMANIA_RECORD'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<dedimania_record> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
Text FormatTime (Integer _Time) {
	declare Text Time = "0:00.000";

	if (_Time > 0) {
		declare Text _TimeText = TextLib::ToText(_Time);
		declare Hundredth = TextLib::ToInteger(TextLib::SubString(_TimeText, TextLib::Length(_TimeText)-3, 3));
		declare Seconds = (_Time / 1000) % 60;
		declare Minutes = (_Time / 60000) % 60;
		declare Hours = (_Time / 3600000);

		if (Hours > 0) {
			Time = Hours ^":"^ TextLib::FormatInteger(Minutes, 2) ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
		else {
			Time = Minutes ^":"^ TextLib::FormatInteger(Seconds, 2) ^"."^ TextLib::FormatInteger(Hundredth, 3);
		}
	}
	return Time;
}
main() {
	declare CMlLabel LabelDedimaniaRecord <=> (Page.GetFirstChild("LabelDedimaniaRecord") as CMlLabel);
	declare Integer NextUpdate = CurrentTime;
	declare Integer DedimaniaRecordScore = {$score};
	declare Boolean HandlePendingEvents = {$HandlePendingEvents};
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				if (Player.Score != Null && Player.Score.BestRace.Time > 0) {
					// Check for improved DedimaniaRecord
					if ((Player.Score.BestRace.Time < DedimaniaRecordScore || DedimaniaRecordScore == 0) && (Player.CurRace.Checkpoints.count >= 2 || Map.AuthorLogin == "Nadeo")) {
						DedimaniaRecordScore = Player.Score.BestRace.Time;
						LabelDedimaniaRecord.SetText(FormatTime(DedimaniaRecordScore));
					}
				}
//log("best: "^ FormatTime(Player.Score.BestRace.Time) ^", prev: "^ FormatTime(Player.Score.PrevRace.Time));
//log("State: "^ Player.RaceState ^", current: "^ FormatTime(Player.CurRace.Time) ^", PB: "^ FormatTime(TextLib::ToInteger(RecordScores[0]["Score"])));
			}

		}

		if (HandlePendingEvents == True) {
			// Check for MouseEvents
			foreach (Event in PendingEvents) {
				switch (Event.Type) {
					case CMlScriptEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonDedimaniaRecord") {
							TriggerPageAction("{$this->config['DEDIMANIA_RECORD'][0]['ACTION'][0]}");
							Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						}
					}
					case CMlScriptEvent::Type::MouseOver : {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
			}
		}
	}
}
--></script>
EOL;
		$xml = '<manialink id="'. $this->config['manialinkid'] .'DedimaniaRecord" name="'. $this->config['manialinkid'] .':DedimaniaRecord" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 197) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['DEDIMANIA_RECORD'][0]['ACTION'][0])) {
				list($plugin, $unused) = explode('?', $this->config['DEDIMANIA_RECORD'][0]['ACTION'][0]);
				if (isset($aseco->plugins[$plugin])) {
					$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonDedimaniaRecord" ScriptEvents="1"/>';
					$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
				}
			}
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['DEDIMANIA_RECORD'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['DEDIMANIA_RECORD'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['DEDIMANIA_RECORD'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelDedimaniaRecord"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="29 2.625" textcolor="'. $this->config['DEDIMANIA_RECORD'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_dedimania_record'))->finish($login) .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildManiaExchange ($login, $score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode === Gameinfo::CHASE) {
			return;
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	<mania_exchange> @ plugin.info_bar.php
 * Author:	undef.de
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
main() {

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlScriptEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonManiaExchange") {
						TriggerPageAction("{$this->config['MANIA_EXCHANGE'][0]['ACTION'][0]}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
				}
				case CMlScriptEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$xml = '<manialink id="'. $this->config['manialinkid'] .'ManiaExchange" name="'. $this->config['manialinkid'] .':ManiaExchange" version="3">';
		if ($show === true) {
			$xml .= '<frame pos="'. ($this->config['bar']['position']['x'] + 224.25) .' '. $this->config['bar']['position']['y'] .'" z-index="'. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if ($aseco->server->maps->current->mx !== false && !empty($this->config['MANIA_EXCHANGE'][0]['ACTION'][0])) {
				list($plugin, $unused) = explode('?', $this->config['MANIA_EXCHANGE'][0]['ACTION'][0]);
				if (isset($aseco->plugins[$plugin])) {
					$xml .= '<quad pos="0 0" z-index="0.02" size="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonManiaExchange" ScriptEvents="1"/>';
					$xml .= '<quad pos="0.05 -4.325" z-index="0.03" size="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
				}
			}
			$xml .= '<quad pos="0 0" z-index="0.04" size="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad pos="1.6 -1" z-index="0.05" size="5.25 5.25" modulatecolor="'. $this->config['MANIA_EXCHANGE'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['MANIA_EXCHANGE'][0]['ICON'][0] .'"/>';
			$xml .= '<label pos="8.15 -1.4" z-index="0.05" size="17.5 2.625" textcolor="'. $this->config['MANIA_EXCHANGE'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'"/>';
			$xml .= '<label pos="8.15 -4.2" z-index="0.05" size="29 2.625" textcolor="'. $this->config['MANIA_EXCHANGE'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. (new Message('plugin.info_bar', 'label_mania_exchange'))->finish($login) .'"/>';
			$xml .= '</frame>';
			if (!empty($this->config['MANIA_EXCHANGE'][0]['ACTION'][0])) {
				$xml .= $maniascript;
			}
		}
		$xml .= '</manialink>';

		return $xml;
	}
}

?>
