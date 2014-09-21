<?php
/*
 * Plugin: Info Bar
 * ~~~~~~~~~~~~~~~~
 * » Displays a multi information bar.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-21
 * Copyright:	2014 by undef.de
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
 * Dependencies:
 *  - plugins/plugin.modescript_handler.php
 *  - plugins/plugin.local_records.php
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
	public $config = array();
	public $records = array();
	public $update = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a multi information bar.');

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEveryTenSeconds',		'onEveryTenSeconds');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerFinish',			'onPlayerFinish');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onBeginMap1',			'onBeginMap1');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onLocalRecordBestLoaded',		'onLocalRecordBestLoaded');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');
		$this->registerEvent('onManiaExchangeBestLoaded',	'onManiaExchangeBestLoaded');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

//		// Read Configuration
//		if (!$this->config = $aseco->parser->xmlToArray('config/welcome_center.xml', true, true)) {
//			trigger_error('[WelcomeCenter] Could not read/parse config file "config/welcome_center.xml"!', E_USER_ERROR);
//		}
//		$this->config = $this->config['WELCOME_CENTER'];
//
//		// Transform 'TRUE' or 'FALSE' from string to boolean
//		$this->config['WELCOME_WINDOW'][0]['ENABLED'][0]			= ((strtoupper($this->config['WELCOME_WINDOW'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
//		$this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]	= ((strtoupper($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]) == 'TRUE')	? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]		= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]) == 'TRUE')		? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]) == 'TRUE')			? true : false);
//		$this->config['INFO_MESSAGES'][0]['ENABLED'][0]				= ((strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'TRUE')			? true : false);

		$this->config['bar']['background_color']			= '5569';
		$this->config['bar']['position']['x']				= -160.0;
		$this->config['bar']['position']['y']				= 90.0;
		$this->config['bar']['position']['z']				= 20.0;


		$this->config['box']['font_color_top']				= 'FFFF';
		$this->config['box']['font_color_bottom']			= 'DDDF';
		$this->config['box']['seperator_color']				= 'DDD6';
		$this->config['box']['background_color_default']		= 'FFF0';
		$this->config['box']['background_color_focus']			= '09FF';

		$this->config['clock']['icon']					= 'http://static.undef.name/ingame/info-bar/icon-clock.png';
		$this->config['ladder_limits']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-ladder-limits.png';
		$this->config['gamemode']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-gamemode.png';
		$this->config['player_count']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-players.png';
		$this->config['spectator_count']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-spectators.png';
		$this->config['current_ranking']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-player-ranking.png';

		$this->config['records']['position']				= 69;
		$this->config['records']['personalbest']['Label']		= 'PERSONAL BEST';
		$this->config['records']['personalbest']['Style']		= '';
		$this->config['records']['personalbest']['Substyle']		= '';
		$this->config['records']['personalbest']['ImageUrl']		= 'http://static.undef.name/ingame/info-bar/icon-personal-best-time.png';
		$this->config['records']['local']['Label']			= 'LOCAL RECORD';
		$this->config['records']['local']['Style']			= '';
		$this->config['records']['local']['Substyle']			= '';
		$this->config['records']['local']['ImageUrl']			= 'http://static.undef.name/ingame/info-bar/icon-local-record.png';
		$this->config['records']['dedimania']['Label']			= 'DEDIMANIA';
		$this->config['records']['dedimania']['Style']			= '';
		$this->config['records']['dedimania']['Substyle']		= '';
		$this->config['records']['dedimania']['ImageUrl']		= 'http://static.undef.name/ingame/info-bar/icon-dedimania-record.png';
		$this->config['records']['maniaexchange']['Label']		= 'MANIA EXCHANGE';
		$this->config['records']['maniaexchange']['Style']		= '';
		$this->config['records']['maniaexchange']['Substyle']		= '';
		$this->config['records']['maniaexchange']['ImageUrl']		= 'http://static.undef.name/ingame/info-bar/icon-maniaexchange.png';


		$this->config['manialinkid']					= 'PluginInfoBar';
		$this->config['placeholder']					= array(
			'score'	=> '---',
			'time'	=> '-:--.---',
		);


		$this->records['local']						= 0;
		$this->records['dedimania']					= 0;
		$this->records['maniaexchange']					= 0;


		// Disable parts of the UI
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('map_list', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('position', false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEveryTenSeconds ($aseco) {
		// Check for required updates
		$this->updateRecordsBox();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Send Bar
		$this->sendInfoBar($player->login, true);

		// Store it into the $player object
		$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $aseco->server->maps->current->id);
		$player->personal_best = $score['time'];

		// Add Player login to update/send RecordsBox
		$this->update[$player->login] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finished) {

		if ($finished->score > 0) {
			// check for improved score (Stunts) or time (others)
			if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
				if ($finished->player->personal_best < $finished->score) {
					$finished->player->personal_best = $finished->score;
					$this->sendRecordsBox($finished->player, true);
				}
			}
			else {
				if ($finished->player->personal_best == 0 || $finished->player->personal_best > $finished->score) {
					$finished->player->personal_best = $finished->score;
					$this->sendRecordsBox($finished->player, true);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// Get Personal Best for all Players
		foreach ($aseco->server->players->player_list as $player) {
			// Store it into the $player object
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $map->id);
			$player->personal_best = $score['time'];

			// Mark for update
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {

		// Send Bar
		$this->sendInfoBar(false, true);

		// Send records box
		$this->updateRecordsBox();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Hide from all Players
		$this->sendInfoBar(false, false);
		$this->sendRecordsBox(false, false);

		// Reset Personal Best for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$player->personal_best = 0;
		}

		// Reset all records
		$this->records['local']		= 0;
		$this->records['dedimania']	= 0;
		$this->records['maniaexchange']	= 0;

		// Reset update list
		$this->update = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.mania_exchange_info.php
	public function onManiaExchangeBestLoaded ($aseco, $score) {

		// Store time
		$this->records['maniaexchange'] = $score;

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecordBestLoaded ($aseco, $score) {

		// Store time
		$this->records['local'] = $score;

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecord ($aseco, $record) {

		if ($record->score > 0) {
			if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS && $record->score > $this->records['local']) {
				$this->records['local'] = $record->score;
			}
			else if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS && $record->score < $this->records['local']) {
				$this->records['local'] = $record->score;
			}
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
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
			$this->records['dedimania'] = $records[0]['Best'];
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecord ($aseco, $record) {

		if ($record['Best'] > 0 && $record['Best'] < $this->records['dedimania']) {
			$this->records['dedimania'] = $record['Best'];
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateRecordsBox () {
		global $aseco;

		// Update RecordsBox for Players
		foreach ($this->update as $login => $value) {
			if ($value == true) {
				$this->update[$login] = false;
				$player = $aseco->server->players->getPlayer($login);
				$this->sendRecordsBox($player, true);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendInfoBar ($logins = false, $show = true) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'MainBar" name="'. $this->config['manialinkid'] .'MainBar" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. $this->config['bar']['position']['x'] .' '. $this->config['bar']['position']['y'] .' '. $this->config['bar']['position']['z'] .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="380 7" bgcolor="'. $this->config['bar']['background_color'] .'"/>';
			$xml .= '</frame>';
		}
		$xml .= '</manialink>';
		$xml .= $this->buildClock($show);
		$xml .= $this->buildLadderLimits($show);
		$xml .= $this->buildGamemode($show);
		$xml .= $this->buildPlayerSpectatorCount($show);
		$xml .= $this->buildCurrentRanking($show);

		$aseco->sendManiaLink($xml, $logins);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildClock ($show = true) {

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	Clock
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare LabelLocalTime <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelLocalTime") as CMlLabel);
	declare PrevTime = CurrentLocalDateText;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;
			LabelLocalTime.SetText(TextLib::SubString(CurrentLocalDateText, 11, 20));
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Clock" name="'. $this->config['manialinkid'] .'Clock" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 297) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
//			$xml .= '<quad posn="23 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['clock']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="00:00:00" id="'. $this->config['manialinkid'] .'LabelLocalTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="LOCAL TIME"/>';
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

	private function buildLadderLimits ($show = true) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'LadderLimits" name="'. $this->config['manialinkid'] .'LadderLimits" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 274) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25"  modulatecolor="DDD" image="'. $this->config['ladder_limits']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. substr(($aseco->server->ladder_limit_min / 1000), 0, 3) .'-'. substr(($aseco->server->ladder_limit_max / 1000), 0, 3) .'k" id="'. $this->config['manialinkid'] .'LabelLadderLimits"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="LADDER"/>';
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Gamemode" name="'. $this->config['manialinkid'] .'Gamemode" version="1">';
		if ($show == true) {
			$modename = $aseco->server->gameinfo->getGamemodeName($aseco->server->gameinfo->mode);
			$limits = '';
			switch ($aseco->server->gameinfo->mode) {
				case Gameinfo::ROUNDS:
					// Rounds
					$limits = $aseco->server->gameinfo->rounds['PointsLimit'] .' pts.';
					break;

				case Gameinfo::TIMEATTACK:
					// TimeAttack
					$limits = $aseco->formatTime($aseco->server->gameinfo->time_attack['TimeLimit'] * 1000, false, 2);
					break;

				case Gameinfo::TEAM:
					// Team
					$limits = $aseco->server->gameinfo->team['PointsLimit'] .' pts.';
					break;

				case Gameinfo::LAPS:
					// Laps
					if ($aseco->server->gameinfo->laps['TimeLimit'] > 0) {
						$limits = $aseco->formatTime($aseco->server->gameinfo->laps['TimeLimit'] * 1000, false, 2) .' min.';
					}
					else {
						$limits = $aseco->server->gameinfo->laps['ForceLapsNb'] .' laps';
					}
					break;

				case Gameinfo::CUP:
					// Cup
					$limits = $aseco->server->gameinfo->cup['PointsLimit'] .' pts.';
					break;

				case Gameinfo::STUNTS:
					// Stunts
					$limits = 'NONE';
					break;

				default:
					// Do nothing
					break;
			}

			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 251) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25"  modulatecolor="DDD" image="'. $this->config['gamemode']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $limits .'" id="'. $this->config['manialinkid'] .'Gamemode"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. strtoupper($modename) .'"/>';
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

	private function buildPlayerSpectatorCount ($show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	PlayerSpectatorCount
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare LabelPlayerCount <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelPlayerCount") as CMlLabel);
	declare LabelSpectatorCount <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelSpectatorCount") as CMlLabel);

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
				if (Player.Login == CurrentServerLogin) {
					continue;
				}
				if (Player.IsSpawned == False) {
					if (WatchList.existskey(Player.Login) == True) {
						WatchList[Player.Login] = WatchList[Player.Login] + 1;
					}
					else {
						WatchList[Player.Login] = 1;
					}
				}
				else {
					WatchList[Player.Login] = 0;
				}

				if (WatchList[Player.Login] > SpectatorThreshold) {
					SpectatorsList[Player.Login] = 0;
				}
				else {
					if (SpectatorsList.existskey(Player.Login) == True) {
						SpectatorsList.removekey(Player.Login);
					}
				}
			}

			// Update labels
			SpectatorCount = SpectatorsList.count;
			PlayerCount = (Players.count - SpectatorCount - 1);
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerSpectatorCount" name="'. $this->config['manialinkid'] .'PlayerSpectatorCount" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. $this->config['bar']['position']['x'] .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
//			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25"  modulatecolor="DDD" image="'. $this->config['player_count']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxPlayers'] .'" id="'. $this->config['manialinkid'] .'LabelPlayerCount"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="PLAYER"/>';
			$xml .= '</frame>';
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 23) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25"  modulatecolor="DDD" image="'. $this->config['spectator_count']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxSpectators'] .'" id="'. $this->config['manialinkid'] .'LabelSpectatorCount"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="SPECTATOR"/>';
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

	private function buildCurrentRanking ($show = true) {

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	CurrentPlayerRanking
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare LabelCurrentRanking <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelCurrentRanking") as CMlLabel);
	declare PrevTime = CurrentLocalDateText;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			declare Integer CurrentPlayerRank = 0;
			foreach (Player in Players) {
				// Skip on Login from Server, that is not a Player ;)
				if (Player.Login == CurrentServerLogin) {
					continue;
				}
				CurrentPlayerRank += 1;

				// Did the Player already finished the Map?
				if (Player.Score.BestRace.Time > 0 || Player.Score.Points > 0) {
					LabelCurrentRanking.SetText(CurrentPlayerRank ^"/"^ (Players.count - 1));
				}
				else {
					LabelCurrentRanking.SetText("0/"^ (Players.count - 1));
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'CurrentRanking" name="'. $this->config['manialinkid'] .'CurrentRanking" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 46) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="23 7" action="0" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" "/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['current_ranking']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/0" id="'. $this->config['manialinkid'] .'LabelCurrentRanking"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="RANKING"/>';
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

	public function sendRecordsBox ($player, $show = true) {
		global $aseco;

		// Show or hide?
		if ($show == true) {
			// Formate time/score
			if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
				$recs['personalbest']	= $aseco->formatNumber($player->personal_best);
				$recs['local']		= $aseco->formatNumber($this->records['local']);
				$recs['dedimania']	= $aseco->formatNumber($this->records['dedimania']);
				$recs['maniaexchange']	= $aseco->formatNumber($this->records['maniaexchange']);
			}
			else {
				$recs['personalbest']	= $aseco->formatTime($player->personal_best);
				$recs['local']		= $aseco->formatTime($this->records['local']);
				$recs['dedimania']	= $aseco->formatTime($this->records['dedimania']);
				$recs['maniaexchange']	= $aseco->formatTime($this->records['maniaexchange']);
			}
		}
		else {
			$recs['personalbest']	= 0;
			$recs['local']		= 0;
			$recs['dedimania']	= 0;
			$recs['maniaexchange']	= 0;
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	RecordsRotation
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
Void ReplaceRecords (Text _Time, Text _Label, Text _Style, Text _Substyle, Text _ImageUrl) {
	declare QuadIcon <=> (Page.GetFirstChild("QuadIcon") as CMlQuad);
	declare LabelTime <=> (Page.GetFirstChild("LabelTime") as CMlLabel);
	declare LabelText <=> (Page.GetFirstChild("LabelText") as CMlLabel);

	// Fade out <label>s
	while (LabelTime.Opacity > 0.0) {
		if ((LabelTime.Opacity - 0.05) < 0.0) {
			QuadIcon.Opacity = 0.0;
			LabelTime.Opacity = 0.0;
			LabelText.Opacity = 0.0;
			break;
		}
		QuadIcon.Opacity -= 0.05;
		LabelTime.Opacity -= 0.05;
		LabelText.Opacity -= 0.05;
		yield;
	}

	// Replace content
	if (_Style != "" && _Substyle != "") {
		QuadIcon.Style = _Style;
		QuadIcon.Substyle = _Substyle;
		QuadIcon.ImageUrl = "";
	}
	if (_ImageUrl != "") {
		QuadIcon.Style = "";
		QuadIcon.Substyle = "";
		QuadIcon.ImageUrl = _ImageUrl;
	}
	LabelTime.SetText(_Time);
	LabelText.SetText(_Label);

	// Fade in <label>s
	while (LabelTime.Opacity < 1.0) {
		if ((LabelTime.Opacity + 0.05) > 1.0) {
			QuadIcon.Opacity = 1.0;
			LabelTime.Opacity = 1.0;
			LabelText.Opacity = 1.0;
			break;
		}
		QuadIcon.Opacity += 0.05;
		LabelTime.Opacity += 0.05;
		LabelText.Opacity += 0.05;
		yield;
	}
}
Void WipeIn (Text ChildId, Vec2 EndSize) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);

	Container.Hide();
	Container.RelativePosition.X = Container.RelativePosition.X + (EndSize.X / 2);
	Container.RelativeScale = 0.0;
	Container.Show();

	while (Container.RelativeScale < 1.0) {
		Container.RelativePosition.X = Container.RelativePosition.X - (EndSize.X / 2 / 10);
		Container.RelativeScale += 0.10;
		yield;
	}
}
main() {
	declare CMlControl RecordsDropDown <=> (Page.GetFirstChild("RecordsDropDown") as CMlFrame);

	declare RecordScores = Text[Text][Integer];
	RecordScores[0] = [
		"Score"		=> "{$recs['personalbest']}",
		"Label"		=> "{$this->config['records']['personalbest']['Label']}",
		"Style"		=> "{$this->config['records']['personalbest']['Style']}",
		"Substyle"	=> "{$this->config['records']['personalbest']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['personalbest']['ImageUrl']}"
	];
	RecordScores[1] = [
		"Score"		=> "{$recs['local']}",
		"Label"		=> "{$this->config['records']['local']['Label']}",
		"Style"		=> "{$this->config['records']['local']['Style']}",
		"Substyle"	=> "{$this->config['records']['local']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['local']['ImageUrl']}"
	];
	RecordScores[2] = [
		"Score"		=> "{$recs['dedimania']}",
		"Label"		=> "{$this->config['records']['dedimania']['Label']}",
		"Style"		=> "{$this->config['records']['dedimania']['Style']}",
		"Substyle"	=> "{$this->config['records']['dedimania']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['dedimania']['ImageUrl']}"
	];
	RecordScores[3] = [
		"Score"		=> "{$recs['maniaexchange']}",
		"Label"		=> "{$this->config['records']['maniaexchange']['Label']}",
		"Style"		=> "{$this->config['records']['maniaexchange']['Style']}",
		"Substyle"	=> "{$this->config['records']['maniaexchange']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['maniaexchange']['ImageUrl']}"
	];

	declare Integer TimeOut = 7;
	declare Integer Timer = (CurrentTime / 1000);
	declare Integer SecondsCounter = 0;
	declare Integer DisplayedRecord = 0;
	declare PrevTime = CurrentLocalDateText;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to change only every "TimeOut" seconds
		if (Timer <= (CurrentTime / 1000) && RecordsDropDown.Visible == False) {
			Timer = (CurrentTime / 1000) + TimeOut;

			// Replace displayed record
			ReplaceRecords(
				RecordScores[DisplayedRecord]["Score"],
				RecordScores[DisplayedRecord]["Label"],
				RecordScores[DisplayedRecord]["Style"],
				RecordScores[DisplayedRecord]["Substyle"],
				RecordScores[DisplayedRecord]["ImageUrl"]
			);

			DisplayedRecord += 1;
			if (DisplayedRecord >= RecordScores.count) {
				DisplayedRecord = 0;
			}
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "RecordsButton") {
						if (RecordsDropDown.Visible == True) {
							RecordsDropDown.Hide();
						}
						else {
							WipeIn("RecordsDropDown", <28.0, 28.6>);
						}
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Records" name="'. $this->config['manialinkid'] .'Records" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + $this->config['records']['position']) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<label posn="0 0 0.02" sizen="28 7" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" " id="RecordsButton" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="28 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" id="QuadIcon"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text=" " id="LabelText"/>';
			$xml .= '</frame>';

			// Build on click full widget
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + $this->config['records']['position']) .' '. ($this->config['bar']['position']['y'] - 7) .' '. ($this->config['bar']['position']['z'] + 0.01) .'" id="RecordsDropDown" hidden="true">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 28.6" bgcolor="'. $this->config['bar']['background_color'] .'"/>';


			// Personal Best
			$xml .= '<frame posn="0 -0 0.02">';
			if ($this->config['records']['personalbest']['Style'] != '' && $this->config['records']['personalbest']['Substyle'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['personalbest']['Style'] .'" substyle="'. $this->config['records']['personalbest']['Substyle'] .'"/>';
			}
			else if ($this->config['records']['personalbest']['ImageUrl'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['records']['personalbest']['ImageUrl'] .'"/>';
			}
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['personalbest'] .'"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['personalbest']['Label'] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '</frame>';


			// Local Record
			$xml .= '<frame posn="0 -7.2 0.02">';
			if ($this->config['records']['local']['Style'] != '' && $this->config['records']['local']['Substyle'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['local']['Style'] .'" substyle="'. $this->config['records']['local']['Substyle'] .'"/>';
			}
			else if ($this->config['records']['local']['ImageUrl'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['records']['local']['ImageUrl'] .'"/>';
			}
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['local'] .'"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['local']['Label'] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '</frame>';


			// Dedimania Record
			$xml .= '<frame posn="0 -14.4 0.02">';
			if ($this->config['records']['dedimania']['Style'] != '' && $this->config['records']['dedimania']['Substyle'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['dedimania']['Style'] .'" substyle="'. $this->config['records']['dedimania']['Substyle'] .'"/>';
			}
			else if ($this->config['records']['dedimania']['ImageUrl'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['records']['dedimania']['ImageUrl'] .'"/>';
			}
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['dedimania'] .'"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['dedimania']['Label'] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '</frame>';


			// Mania Exchange Offline Record
			$xml .= '<frame posn="0 -21.6 0.02">';
			if ($this->config['records']['maniaexchange']['Style'] != '' && $this->config['records']['maniaexchange']['Substyle'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['maniaexchange']['Style'] .'" substyle="'. $this->config['records']['maniaexchange']['Substyle'] .'"/>';
			}
			else if ($this->config['records']['maniaexchange']['ImageUrl'] != '') {
				$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="DDD" image="'. $this->config['records']['maniaexchange']['ImageUrl'] .'"/>';
			}
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['maniaexchange'] .'"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['maniaexchange']['Label'] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '</frame>';

			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		if ($show == true) {
			$aseco->sendManiaLink($xml, $player->login);
		}
		else {
			$aseco->sendManiaLink($xml, false);
		}
	}
}

?>
