<?php
/*
 * Plugin: Info Bar
 * ~~~~~~~~~~~~~~~~
 * » Displays a multi information bar.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-22
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
 *  - plugins/plugin.donate.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.dedimania.php
 *  - plugins/plugin.mania_exchange_info.php
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

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a multi information bar.');

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDonate',			Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',			Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginManiaExchangeInfo',		Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEveryTenSeconds',		'onEveryTenSeconds');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onBeginMap1',			'onBeginMap1');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onBeginPodium',			'onBeginPodium');
		$this->registerEvent('onUnloadingMap',			'onUnloadingMap');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onLocalRecordBestLoaded',		'onLocalRecordBestLoaded');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onManiaExchangeBestLoaded',	'onManiaExchangeBestLoaded');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

//		// Read Configuration
//		if (!$this->config = $aseco->parser->xmlToArray('config/info_bar.xml', true, true)) {
//			trigger_error('[WelcomeCenter] Could not read/parse config file "config/info_bar.xml"!', E_USER_ERROR);
//		}
//		$this->config = $this->config['INFO_BAR'];
//
//		// Transform 'TRUE' or 'FALSE' from string to boolean
//		$this->config['xxx'][0]['ENABLED'][0]			= ((strtoupper($this->config['xxx'][0]['ENABLED'][0]) == 'TRUE')			? true : false);

		$this->config['bar']['background_color']			= '55556699';	// RRGGBBAA
		$this->config['bar']['position']['x']				= -160.0;
		$this->config['bar']['position']['y']				= 90.0;
		$this->config['bar']['position']['z']				= 20.0;

		$this->config['box']['font_color_top']				= 'FFFFFFFF';	// RRGGBBAA
		$this->config['box']['font_color_bottom']			= 'DDDDDDFF';	// RRGGBBAA
		$this->config['box']['seperator_color']				= 'DDDDDD66';	// RRGGBBAA
		$this->config['box']['background_color_default']		= 'FFFFFF00';	// RRGGBBAA
		$this->config['box']['background_color_focus']			= '0099FFFF';	// RRGGBBAA

		$this->config['player_count']['label']				= 'PLAYER';
		$this->config['player_count']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-players.png';
		$this->config['player_count']['modulatecolor']			= 'DDDDDD';	// RRGGBB

		$this->config['spectator_count']['label']			= 'SPECTATOR';
		$this->config['spectator_count']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-spectators.png';
		$this->config['spectator_count']['modulatecolor']		= 'DDDDDD';	// RRGGBB

		$this->config['donation']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-donate.png';
		$this->config['donation']['modulatecolor']			= 'DDDDDD';

		$this->config['current_ranking']['label']			= 'RANKING';
		$this->config['current_ranking']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-player-ranking.png';
		$this->config['current_ranking']['modulatecolor']		= 'DDDDDD';	// RRGGBB
		$this->config['current_ranking']['action']			= 'showLiveRankingsWindow';

		$this->config['records']['personal_best']['label']		= 'PERSONAL BEST';
		$this->config['records']['personal_best']['icon']		= 'http://static.undef.name/ingame/info-bar/icon-personal-best-time.png';
		$this->config['records']['personal_best']['modulatecolor']	= 'DDDDDD';	// RRGGBB
		$this->config['records']['personal_best']['action']		= '';

		$this->config['records']['local']['label']			= '1. LOCAL RECORD';
		$this->config['records']['local']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-local-record.png';
		$this->config['records']['local']['modulatecolor']		= 'DDDDDD';	// RRGGBB
		$this->config['records']['local']['action']			= 'showLocalRecordsWindow';

		$this->config['records']['dedimania']['label']			= '1. DEDIMANIA';
		$this->config['records']['dedimania']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-dedimania-record.png';
		$this->config['records']['dedimania']['modulatecolor']		= 'DDDDDD';	// RRGGBB
		$this->config['records']['dedimania']['action']			= 'showDedimaniaRecordsWindow';

		$this->config['records']['mania_exchange']['label']		= 'MANIA EXCHANGE';
		$this->config['records']['mania_exchange']['icon']		= 'http://static.undef.name/ingame/info-bar/icon-maniaexchange.png';
		$this->config['records']['mania_exchange']['modulatecolor']	= 'DDDDDD';	// RRGGBB
		$this->config['records']['mania_exchange']['action']		= 'showManiaExchangeMapInfoWindow';

		$this->config['best_last_time']['best']['label']		= 'BEST RACE TIME';
		$this->config['best_last_time']['best']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-best-race-time.png';
		$this->config['best_last_time']['best']['modulatecolor']	= 'DDDDDD';	// RRGGBB

		$this->config['best_last_time']['last']['label']		= 'LAST RACE TIME';
		$this->config['best_last_time']['last']['icon']			= 'http://static.undef.name/ingame/info-bar/icon-last-race-time.png';
		$this->config['best_last_time']['last']['modulatecolor']	= 'DDDDDD';	// RRGGBB

		$this->config['clock']['label']					= 'LOCAL TIME';
		$this->config['clock']['icon']					= 'http://static.undef.name/ingame/info-bar/icon-clock.png';
		$this->config['clock']['modulatecolor']				= 'DDDDDD';	// RRGGBB

		$this->config['ladder_limits']['label']				= 'LADDER';
		$this->config['ladder_limits']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-ladder-limits.png';
		$this->config['ladder_limits']['modulatecolor']			= 'DDDDDD';	// RRGGBB

		$this->config['gamemode']['icon']				= 'http://static.undef.name/ingame/info-bar/icon-gamemode.png';
		$this->config['gamemode']['modulatecolor']			= 'DDDDDD';	// RRGGBB

		$this->config['manialinkid']					= 'InfoBar';

		$this->records['local']						= 0;
		$this->records['dedimania']					= 0;
		$this->records['mania_exchange']				= 0;

		$this->update['local']						= false;
		$this->update['dedimania']					= false;
		$this->update['mania_exchange']					= false;

		// Disable parts of the UI
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('map_info', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('position', false);

		// Send the UI settings
		$aseco->plugins['PluginModescriptHandler']->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEveryTenSeconds ($aseco) {

		// Check for required updates
		$this->updateRecords();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Setup records
		$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $aseco->server->maps->current->id);
		$this->players[$player->login]['records']['personal_best']	= $score['time'];
		$this->players[$player->login]['records']['local']		= $this->records['local'];
		$this->players[$player->login]['records']['dedimania']		= $this->records['dedimania'];
		$this->players[$player->login]['records']['mania_exchange']	= $this->records['mania_exchange'];

		// Send Info-Bar to all Players
		$this->sendInfoBar($player->login, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// If id = 0, bail out immediately
		if ($answer[2] === 0) {
			return;
		}

		// InfoBar?Action=DonatePlanets&Amount=500
		if (substr($answer[2], 0, 7) == 'InfoBar') {
			// Parse get parameter
			parse_str(str_replace('InfoBar?', '', $answer[2]), $param);

			if ($param['Action'] == 'DonatePlanets') {
				$aseco->releaseChatCommand('/donate '. $param['Amount'] , $answer[1]);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		foreach ($aseco->server->players->player_list as $player) {
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $map->id);
			$this->players[$player->login]['records']['personal_best']	= $score['time'];
			$this->players[$player->login]['records']['local']		= $this->records['local'];
			$this->players[$player->login]['records']['dedimania']		= $this->records['dedimania'];
			$this->players[$player->login]['records']['mania_exchange']	= $this->records['mania_exchange'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {

		// Send Info-Bar to all Players
		$this->sendInfoBar(false, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $uid) {

		// Send Info-Bar to all Players
		$this->sendInfoBar(false, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginPodium ($aseco, $data) {

		// Hide from all Players
		$this->sendInfoBar(false, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $data) {
		// Reset all records
		$this->records['local']			= 0;
		$this->records['dedimania']		= 0;
		$this->records['mania_exchange']	= 0;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.localdatabase.php
	public function onLocalRecord ($aseco, $record) {

		if ($this->players[$record->player->login]['records']['local'] > $record->score || $this->players[$record->player->login]['records']['local'] == 0) {
			// Store new 1. Local Record at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['records']['local'] = $record->score;
			}
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
		$this->records['local'] = $score;

		// Store at each connected Player
		foreach ($aseco->server->players->player_list as $player) {
			$this->players[$player->login]['records']['local'] = $score;
		}

		// Mark for required update
		$this->update['local'] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecord ($aseco, $record) {

		if ($this->players[$record['Login']]['records']['dedimania'] > $record['Best'] || $this->players[$record['Login']]['records']['dedimania'] == 0) {
			// Store new 1. Dedimania Record at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['records']['dedimania'] = $record['Best'];
			}
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
			$this->records['dedimania'] = $records[0]['Best'];

			// Store at each connected Player
			foreach ($aseco->server->players->player_list as $player) {
				$this->players[$player->login]['records']['dedimania'] = $records[0]['Best'];
			}

			// Mark for required update
			$this->update['dedimania'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.mania_exchange_info.php
	public function onManiaExchangeBestLoaded ($aseco, $score) {

		// Store global for new Player connections
		$this->records['mania_exchange'] = $score;

		// Store at each connected Player
		foreach ($aseco->server->players->player_list as $player) {
			$this->players[$player->login]['records']['mania_exchange'] = $score;
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

		$update = false;
		if ($this->update['local'] == true) {
			$this->update['local'] = false;
			$update = true;
		}
		if ($this->update['dedimania'] == true) {
			$this->update['dedimania'] = false;
			$update = true;
		}
		if ($this->update['mania_exchange'] == true) {
			$this->update['mania_exchange'] = false;
			$update = true;
		}

		if ($update == true) {
			// Send Records
			foreach ($aseco->server->players->player_list as $player) {
				$xml = $this->buildRecords($this->players[$player->login]['records'], true);
				$aseco->sendManiaLink($xml, $player->login);
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
		$xml .= $this->buildPlayerSpectatorCount($show);
		$xml .= $this->buildDonation($show);
		$xml .= $this->buildCurrentRanking($show);
		$xml .= $this->buildBestLastTime($show);
		$xml .= $this->buildGamemode($show);
		$xml .= $this->buildLadderLimits($show);
		$xml .= $this->buildClock($show);

		if ($logins == false) {
			$mls = $xml;
			foreach ($aseco->server->players->player_list as $player) {
				$mls = $xml;
				$mls .= $this->buildRecords($this->players[$player->login]['records'], $show);
				$aseco->sendManiaLink($mls, $player->login);
			}
		}
		else {
			foreach (explode(',', $logins) as $login) {
				$mls = $xml;
				$mls .= $this->buildRecords($this->players[$login]['records'], $show);
				$aseco->sendManiaLink($mls, $login);
			}
		}
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
			LabelLocalTime.SetText(TextLib::SubString(CurrentLocalDateText, 11, 20));
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Clock" name="'. $this->config['manialinkid'] .'Clock" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 297) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
//			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonClock" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="23 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['clock']['modulatecolor'] .'" image="'. $this->config['clock']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="00:00:00" id="'. $this->config['manialinkid'] .'LabelLocalTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['clock']['label'] .'"/>';
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
//			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonLadderLimits" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['ladder_limits']['modulatecolor'] .'" image="'. $this->config['ladder_limits']['icon'] .'"/>';
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

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	GamemodeInfo
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
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
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonGamemodeHelp") {
						ShowModeHelp();
					}
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Gamemode" name="'. $this->config['manialinkid'] .'Gamemode" version="1">';
		if ($show == true) {
			$modename = $aseco->server->gameinfo->getGamemodeName($aseco->server->gameinfo->mode);
			$limits = '---';
			switch ($aseco->server->gameinfo->mode) {
				case Gameinfo::ROUNDS:
					$limits = $aseco->server->gameinfo->rounds['PointsLimit'] .' pts.';
					break;

				case Gameinfo::TIMEATTACK:
					$limits = $aseco->formatTime($aseco->server->gameinfo->time_attack['TimeLimit'] * 1000, false) .' min.';
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

				case Gameinfo::TEAMATTACK:
					$limits = '???';
					break;

				case Gameinfo::STUNTS:
					$limits = 'NONE';
					break;

				default:
					// Do nothing
					break;
			}

			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 251) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonGamemodeHelp" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['gamemode']['modulatecolor'] .'" image="'. $this->config['gamemode']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $limits .'" id="'. $this->config['manialinkid'] .'Gamemode"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. strtoupper($modename) .'"/>';
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
//			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonPlayerCount" ScriptEvents="1"/>';
//			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['player_count']['modulatecolor'] .'" image="'. $this->config['player_count']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxPlayers'] .'" id="'. $this->config['manialinkid'] .'LabelPlayerCount"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['player_count']['label'] .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 23) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
//			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonSpectatorCount" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['spectator_count']['modulatecolor'] .'" image="'. $this->config['spectator_count']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxSpectators'] .'" id="'. $this->config['manialinkid'] .'LabelSpectatorCount"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['spectator_count']['label'] .'"/>';
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

	private function buildDonation ($show = true) {
		global $aseco;

		// Get the min. amount of donation
		$mindonation = $aseco->plugins['PluginDonate']->mindonation;

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	Donation
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
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
	declare CMlControl DropDownDonation <=> (Page.GetFirstChild("DropDownDonation") as CMlFrame);
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
				case CMlEvent::Type::MouseClick : {
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
							TriggerPageAction("InfoBar?Action=DonatePlanets&Amount="^ EntryDonate.Value);
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							DropDownDonation.Hide();
						}
						else {
							LabelTooltipDonations.Show();
							EntryDonate.Focus();
						}
					}
				}
				case CMlEvent::Type::MouseOver : {
					LabelTooltipDonations.Hide();
					if (Event.ControlId == "ButtonDonation") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
				case CMlEvent::Type::EntrySubmit : {
					LabelTooltipDonations.Hide();
					if (Event.ControlId == "EntryDonate") {
						if (TextLib::ToInteger(EntryDonate.Value) >= {$mindonation}) {
							TriggerPageAction("InfoBar?Action=DonatePlanets&Amount="^ EntryDonate.Value);
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Donation" name="'. $this->config['manialinkid'] .'Donation" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 46) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.02" sizen="28 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonDonation" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['donation']['modulatecolor'] .'" image="'. $this->config['donation']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="DONATE"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="PLANETS PLEASE"/>';
			$xml .= '</frame>';

			// Build onClick full Widget
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 46) .' '. ($this->config['bar']['position']['y'] - 7.05) .' '. ($this->config['bar']['position']['z'] + 0.02) .'" id="DropDownDonation" hidden="true">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="55.6 12" bgcolor="'. $this->config['bar']['background_color'] .'" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="55.6 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<label posn="3 -1.7 0.04" sizen="55 2.625" textcolor="FFFF" textsize="1" scale="0.9" text="Please enter the value you want to Donate:"/>';
			$xml .= '<quad posn="3 -7.8 0.03" sizen="19 5.1" valign="center" style="Bgs1InRace" substyle="BgColorContour"/>';
			$xml .= '<entry posn="12.45 -7.8 0.04" sizen="17.9 4" halign="center" valign="center2" style="TextValueSmall" textsize="1" textcolor="FFFF" default="500" autonewline="0" id="EntryDonate" ScriptEvents="1"/>';
			$xml .= '<label posn="38.5 -7.9 0.04" sizen="12 4.5" halign="center" valign="center" textsize="1" style="CardButtonSmallS" text="SEND DONATION" id="ButtonSendDonation" ScriptEvents="1"/>';

			// Build Tooltip
			$xml .= '<label posn="0 -13 0.05" sizen="62 2.625" textsize="1" scale="0.9" textcolor="FF0F" text="$SThe minimum value for a donation are '. $mindonation .' Planets." hidden="true" id="LabelTooltipDonations"/>';
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
	declare CMlLabel LabelCurrentRanking <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelCurrentRanking") as CMlLabel);

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
				if (Score.User.Login == InputPlayer.Login) {
					if (Score != Null && (Score.BestRace.Time > 0 || Score.Points > 0)) {
						LabelCurrentRanking.SetText(CurrentPlayerRank ^"/"^ (Players.count - 1));
					}
					else {
						LabelCurrentRanking.SetText("0/"^ (Players.count - 1));
					}
				}
			}
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonRanking") {
						TriggerPageAction("{$this->config['current_ranking']['action']}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'CurrentRanking" name="'. $this->config['manialinkid'] .'CurrentRanking" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 74) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.02" sizen="23 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonRanking" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['current_ranking']['modulatecolor'] .'" image="'. $this->config['current_ranking']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0/0" id="'. $this->config['manialinkid'] .'LabelCurrentRanking"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['current_ranking']['label'] .'"/>';
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

	public function buildRecords ($records, $show = true) {
		global $aseco;

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
Void ReplaceRecords (Text _Time, Text _Label, Text _ImageUrl) {
	declare CMlQuad QuadIcon <=> (Page.GetFirstChild("QuadIcon") as CMlQuad);
	declare CMlLabel LabelTime <=> (Page.GetFirstChild("LabelTime") as CMlLabel);
	declare CMlLabel LabelText <=> (Page.GetFirstChild("LabelText") as CMlLabel);

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
	if (_ImageUrl != "") {
		QuadIcon.ImageUrl = _ImageUrl;
	}
	LabelTime.SetText(FormatTime(TextLib::ToInteger(_Time)));
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
	declare CMlControl DropDownRecords <=> (Page.GetFirstChild("DropDownRecords") as CMlFrame);
	declare CMlLabel LabelPersonalBest <=> (Page.GetFirstChild("LabelPersonalBest") as CMlLabel);
	declare CMlLabel LabelLocalRecord <=> (Page.GetFirstChild("LabelLocalRecord") as CMlLabel);
	declare CMlLabel LabelDedimania <=> (Page.GetFirstChild("LabelDedimania") as CMlLabel);
	declare CMlLabel LabelManiaExchange <=> (Page.GetFirstChild("LabelManiaExchange") as CMlLabel);

	declare RecordScores = Text[Text][Integer];
	RecordScores[0] = [
		"Score"		=> "{$records['personal_best']}",
		"Label"		=> "{$this->config['records']['personal_best']['label']}",
		"ImageUrl"	=> "{$this->config['records']['personal_best']['icon']}"
	];
	RecordScores[1] = [
		"Score"		=> "{$records['local']}",
		"Label"		=> "{$this->config['records']['local']['label']}",
		"ImageUrl"	=> "{$this->config['records']['local']['icon']}"
	];
	RecordScores[2] = [
		"Score"		=> "{$records['dedimania']}",
		"Label"		=> "{$this->config['records']['dedimania']['label']}",
		"ImageUrl"	=> "{$this->config['records']['dedimania']['icon']}"
	];
	RecordScores[3] = [
		"Score"		=> "{$records['mania_exchange']}",
		"Label"		=> "{$this->config['records']['mania_exchange']['label']}",
		"ImageUrl"	=> "{$this->config['records']['mania_exchange']['icon']}"
	];

	// Insert the Records into the Labels
	LabelPersonalBest.SetText(FormatTime(TextLib::ToInteger(RecordScores[0]["Score"])));
	LabelLocalRecord.SetText(FormatTime(TextLib::ToInteger(RecordScores[1]["Score"])));
	LabelDedimania.SetText(FormatTime(TextLib::ToInteger(RecordScores[2]["Score"])));
	LabelManiaExchange.SetText(FormatTime(TextLib::ToInteger(RecordScores[3]["Score"])));


	declare Integer TimeOut = 7;
	declare Integer NextRotation = (CurrentTime / 1000);
	declare Integer NextUpdate = CurrentTime;
	declare Integer SecondsCounter = 0;
	declare Integer DisplayedRecord = 0;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				// Skip on Login from Server, that is not a Player ;)
				if (Player.Login == CurrentServerLogin) {
					continue;
				}
				if (Player.Score != Null && Player.Score.BestRace.Time > 0) {
					// Check for improved PersonalBest of InputPlayer
					if ((Player.Login == InputPlayer.Login) && (Player.Score.BestRace.Time < TextLib::ToInteger(RecordScores[0]["Score"]) || TextLib::ToInteger(RecordScores[0]["Score"]) == 0)) {
						RecordScores[0]["Score"] = TextLib::ToText(Player.Score.BestRace.Time);
						LabelPersonalBest.SetText(FormatTime(TextLib::ToInteger(RecordScores[0]["Score"])));
					}
					// Check for improved LocalRecord
					if (Player.Score.BestRace.Time < TextLib::ToInteger(RecordScores[1]["Score"]) || TextLib::ToInteger(RecordScores[1]["Score"]) == 0) {
						RecordScores[1]["Score"] = TextLib::ToText(Player.Score.BestRace.Time);
						LabelLocalRecord.SetText(FormatTime(TextLib::ToInteger(RecordScores[1]["Score"])));
					}
					// Check for improved DedimaniaRecord
					if ((Player.Score.BestRace.Time < TextLib::ToInteger(RecordScores[2]["Score"]) || TextLib::ToInteger(RecordScores[2]["Score"]) == 0) && (Player.CurRace.Checkpoints.count >= 2 || Map.AuthorLogin == "Nadeo")) {
						RecordScores[2]["Score"] = TextLib::ToText(Player.Score.BestRace.Time);
						LabelDedimania.SetText(FormatTime(TextLib::ToInteger(RecordScores[2]["Score"])));
					}
				}
//log("best: "^ FormatTime(Player.Score.BestRace.Time) ^", prev: "^ FormatTime(Player.Score.PrevRace.Time));
//log("State: "^ Player.RaceState ^", current: "^ FormatTime(Player.CurRace.Time) ^", PB: "^ FormatTime(TextLib::ToInteger(RecordScores[0]["Score"])));
			}

		}

		// Throttling to change only every "TimeOut" seconds
		if (NextRotation <= (CurrentTime / 1000) && DropDownRecords.Visible == False) {
			NextRotation = (CurrentTime / 1000) + TimeOut;

			// Replace displayed record
			ReplaceRecords(
				RecordScores[DisplayedRecord]["Score"],
				RecordScores[DisplayedRecord]["Label"],
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
					if (Event.ControlId == "ButtonRecords") {
						if (DropDownRecords.Visible == True) {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							DropDownRecords.Hide();
						}
						else {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::ShowMenu, 0, 1.0);
							WipeIn("DropDownRecords", <28.0, 28.6>);
						}
					}
//					else if (Event.ControlId == "ButtonPersonalBest") {
//						TriggerPageAction("{$this->config['records']['personal_best']['action']}");
//						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
//					}
					else if (Event.ControlId == "ButtonLocalRecord") {
						TriggerPageAction("{$this->config['records']['local']['action']}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
					else if (Event.ControlId == "ButtonDedimaniaRecord") {
						TriggerPageAction("{$this->config['records']['dedimania']['action']}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
					else if (Event.ControlId == "ButtonManiaExchange") {
						TriggerPageAction("{$this->config['records']['mania_exchange']['action']}");
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
					}
				}
				case CMlEvent::Type::MouseOver : {
					if (Event.ControlId != "ButtonPersonalBest") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
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
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 97) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.02" sizen="28 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonRecords" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['records']['personal_best']['modulatecolor'] .'" id="QuadIcon"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text=" " id="LabelText"/>';
			$xml .= '</frame>';

			// Build onClick full Widget
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 97) .' '. ($this->config['bar']['position']['y'] - 7.05) .' '. ($this->config['bar']['position']['z'] + 0.02) .'" id="DropDownRecords" hidden="true">';

			// Personal Best
			$xml .= '<frame posn="0 0 0.02">';
//			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 7" bgcolor="'. $this->config['bar']['background_color'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonPersonalBest" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 7" bgcolor="'. $this->config['bar']['background_color'] .'" bgcolorfocus="'. $this->config['bar']['background_color'] .'" id="ButtonPersonalBest" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['records']['personal_best']['modulatecolor'] .'" image="'. $this->config['records']['personal_best']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelPersonalBest"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['personal_best']['label'] .'"/>';
			$xml .= '</frame>';

			// Local Record
			$xml .= '<frame posn="0 -7.2 0.02">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 7" bgcolor="'. $this->config['bar']['background_color'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonLocalRecord" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['records']['local']['modulatecolor'] .'" image="'. $this->config['records']['local']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelLocalRecord"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['local']['label'] .'"/>';
			$xml .= '</frame>';

			// Dedimania Record
			$xml .= '<frame posn="0 -14.4 0.02">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 7" bgcolor="'. $this->config['bar']['background_color'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonDedimaniaRecord" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['records']['dedimania']['modulatecolor'] .'" image="'. $this->config['records']['dedimania']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelDedimania"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['dedimania']['label'] .'"/>';
			$xml .= '</frame>';

			// Mania Exchange Offline Record
			$xml .= '<frame posn="0 -21.6 0.02">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="28 7" bgcolor="'. $this->config['bar']['background_color'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonManiaExchange" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['records']['mania_exchange']['modulatecolor'] .'" image="'. $this->config['records']['mania_exchange']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelManiaExchange"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['mania_exchange']['label'] .'"/>';
			$xml .= '</frame>';

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

	private function buildBestLastTime ($show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	PlayerBestLastTime
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
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
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Throttling to work only on every half-second
		if (NextUpdate <= CurrentTime) {
			NextUpdate = CurrentTime + 500;

			foreach (Player in Players) {
				if (Player.Login == InputPlayer.Login) {
					if (Player.Score != Null) {
						if (Player.Score.BestRace.Time > 0) {
							LabelBestTime.SetText(FormatTime(Player.Score.BestRace.Time));
						}
						if (Player.Score.PrevRace.Time > 0) {
							LabelLastTime.SetText(FormatTime(Player.Score.PrevRace.Time));
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerBestLastTime" name="'. $this->config['manialinkid'] .'PlayerBestLastTime" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 125) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
//			$xml .= '<quad posn="0 0 0.02" sizen="28 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonBestTime" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['best_last_time']['best']['modulatecolor'] .'" image="'. $this->config['best_last_time']['best']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelBestTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['best_last_time']['best']['label'] .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 153) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
//			$xml .= '<quad posn="0 0 0.02" sizen="28 7" bgcolor="'. $this->config['box']['background_color_default'] .'" bgcolorfocus="'. $this->config['box']['background_color_focus'] .'" id="ButtonLastTime" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="28 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" modulatecolor="'. $this->config['best_last_time']['last']['modulatecolor'] .'" image="'. $this->config['best_last_time']['last']['icon'] .'"/>';
			$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelLastTime"/>';
			$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['best_last_time']['last']['label'] .'"/>';
			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}
}

?>
