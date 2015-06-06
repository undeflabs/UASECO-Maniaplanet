<?php
/*
 * Plugin: Info Bar
 * ~~~~~~~~~~~~~~~~
 * » Displays a multi information bar.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-05-30
 * Copyright:	2014 - 2015 by undef.de
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

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEveryTenSeconds',		'onEveryTenSeconds');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
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

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/info_bar.xml', true, true)) {
			trigger_error('[WelcomeCenter] Could not read/parse config file "config/info_bar.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];

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
		if ($aseco->server->gamestate != Server::SCORE) {
			$this->updateRecords();
		}
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

		// Send Info-Bar to all Players
		$this->sendInfoBar($player->login, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {
		if ($params['Action'] == 'DonatePlanets') {
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

	public function onRestartMap ($aseco, $uid) {

		foreach ($aseco->server->players->player_list as $player) {
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $aseco->server->maps->current->id);
			$this->players[$player->login]['personal_best']		= $score['time'];
		}

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

		if ($this->players[$record->player->login]['local_record'] > $record->score || $this->players[$record->player->login]['local_record'] == 0) {
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

		if ($this->players[$record['Login']]['dedimania_record'] > $record['Best'] || $this->players[$record['Login']]['dedimania_record'] == 0) {
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
;
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
			if ($this->update['local_record'] == true) {
				$this->update['local_record'] = false;
				$xml .= $this->buildLocalRecord($this->players[$player->login]['local_record'], true);
			}
			if ($this->update['dedimania_record'] == true) {
				$this->update['dedimania_record'] = false;
				$xml .= $this->buildDedimaniaRecord($this->players[$player->login]['dedimania_record'], true);
			}
			if ($this->update['mania_exchange'] == true) {
				$this->update['mania_exchange'] = false;
				$xml .= $this->buildManiaExchange($this->players[$player->login]['mania_exchange'], true);
			}
			if ($xml !== false) {
				// Send Records
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

		$xml = $this->buildPlayerSpectatorCount($show);
		$xml .= $this->buildDonation($show);
		$xml .= $this->buildCurrentRanking($show);
		$xml .= $this->buildLastBestTime($show);
		$xml .= $this->buildGamemode($show);
		$xml .= $this->buildLadderLimits($show);
		$xml .= $this->buildClock($show);

		if ($logins == false) {
			foreach ($aseco->server->players->player_list as $player) {
				$mls = $xml;
				$mls .= $this->buildPersonalBest($this->players[$player->login]['personal_best'], $show);
				$mls .= $this->buildPersonalBest($this->players[$player->login]['personal_best'], $show);
				$mls .= $this->buildLocalRecord($this->players[$player->login]['local_record'], $show);
				$mls .= $this->buildDedimaniaRecord($this->players[$player->login]['dedimania_record'], $show);
				$mls .= $this->buildManiaExchange($this->players[$player->login]['mania_exchange'], $show);
				$aseco->sendManiaLink($mls, $player->login);
			}
		}
		else {
			foreach (explode(',', $logins) as $login) {
				$mls = $xml;
				$mls .= $this->buildPersonalBest($this->players[$login]['personal_best'], $show);
				$mls .= $this->buildLocalRecord($this->players[$login]['local_record'], $show);
				$mls .= $this->buildDedimaniaRecord($this->players[$login]['dedimania_record'], $show);
				$mls .= $this->buildManiaExchange($this->players[$login]['mania_exchange'], $show);
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Clock" name="'. $this->config['manialinkid'] .'/Clock" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 299) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="21 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="21 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonClock" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['CLOCK'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['CLOCK'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="10.5 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="00:00:00" id="'. $this->config['manialinkid'] .'LabelLocalTime"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['CLOCK'][0]['LABEL'][0] .'"/>';
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'LadderLimits" name="'. $this->config['manialinkid'] .'/LadderLimits" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 278.75) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLadderLimits" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['LADDER_LIMITS'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LADDER_LIMITS'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="10.5 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. substr(($aseco->server->ladder_limit_min / 1000), 0, 3) .'-'. substr(($aseco->server->ladder_limit_max / 1000), 0, 3) .'k" id="'. $this->config['manialinkid'] .'LabelLadderLimits"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['LADDER_LIMITS'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="LADDER"/>';
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Gamemode" name="'. $this->config['manialinkid'] .'/Gamemode" version="1">';
		if ($show == true) {
			$modename = str_replace('_', ' ', $aseco->server->gameinfo->getModeName($aseco->server->gameinfo->mode));
			$limits = '---';
			switch ($aseco->server->gameinfo->mode) {
				case Gameinfo::ROUNDS:
					$limits = $aseco->server->gameinfo->rounds['PointsLimit'] .' pts.';
					break;

				case Gameinfo::TIME_ATTACK:
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

				case Gameinfo::STUNTS:
					$limits = 'NONE';
					break;

				default:
					// Do nothing
					break;
			}

			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 251.5) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonGamemodeHelp" ScriptEvents="1"/>';
			$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['GAMEMODE'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['GAMEMODE'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['GAMEMODE'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $limits .'" id="'. $this->config['manialinkid'] .'Gamemode"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['GAMEMODE'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. strtoupper($modename) .'"/>';
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerSpectatorCount" name="'. $this->config['manialinkid'] .'/PlayerSpectatorCount" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. $this->config['bar']['position']['x'] .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonPlayerCount" ScriptEvents="1"/>';
//			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['PLAYER_COUNT'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['PLAYER_COUNT'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="10.5 2.625" textcolor="'. $this->config['PLAYER_COUNT'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxPlayers'] .'" id="'. $this->config['manialinkid'] .'LabelPlayerCount"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['PLAYER_COUNT'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['PLAYER_COUNT'][0]['LABEL'][0] .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 20.25) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonSpectatorCount" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['SPECTATOR_COUNT'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['SPECTATOR_COUNT'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="10.5 2.625" textcolor="'. $this->config['SPECTATOR_COUNT'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/'. $aseco->server->options['CurrentMaxSpectators'] .'" id="'. $this->config['manialinkid'] .'LabelSpectatorCount"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="18 2.625" textcolor="'. $this->config['SPECTATOR_COUNT'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['SPECTATOR_COUNT'][0]['LABEL'][0] .'"/>';
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
#Include "AnimLib" as AnimLib
Void WipeIn (Text ChildId, Vec2 EndSize) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
//	declare Vec3 OriginalPosition = Container.RelativePosition;

	Container.Hide();
//	Container.RelativePosition.X = Container.RelativePosition.X + (EndSize.X / 2);
	Container.RelativeScale = 0.0;
	Container.Show();

	declare Real AnimDuration = 500.0;
	declare Real AnimFactor = 0.0;
	declare Integer AnimStartTime = Now;
	while ((Now - AnimStartTime * 1.0) < AnimDuration) {
		AnimFactor = AnimLib::Ease("BounceOut", ((Now - AnimStartTime) * 1.0), 0.0, 1.0, AnimDuration);

//		Container.RelativePosition.X = Container.RelativePosition.X - ((EndSize.X / 2) / (AnimDuration / 10));
		Container.RelativeScale = AnimFactor;
		yield;
	}
//	Container.RelativePosition.X = OriginalPosition.X;
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Donation" name="'. $this->config['manialinkid'] .'/Donation" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 60.75) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonDonation" ScriptEvents="1"/>';
			$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['DONATION'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['DONATION'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['DONATION'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="DONATE"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['DONATION'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="PLANETS PLEASE"/>';
			$xml .= '</frame>';

			// Build onClick full Widget
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 60.75) .' '. ($this->config['bar']['position']['y'] - 7.05) .' '. ($this->config['bar']['position']['z'] + 0.02) .'" id="DropDownDonation" hidden="true">';
			$xml .= '<quad posn="0 -0.1 0.02" sizen="55.6 12" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="55.6 0.2" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
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
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonCurrentRanking") {
						TriggerPageAction("{$this->config['CURRENT_RANKING'][0]['ACTION'][0]}");
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'CurrentRanking" name="'. $this->config['manialinkid'] .'/CurrentRanking" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 40.5) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="20.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['CURRENT_RANKING'][0]['ACTION'][0])) {
				$xml .= '<quad posn="0 0 0.02" sizen="20.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonCurrentRanking" ScriptEvents="1"/>';
				$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			}
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['CURRENT_RANKING'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['CURRENT_RANKING'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="10.5 2.625" textcolor="'. $this->config['CURRENT_RANKING'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0/0" id="'. $this->config['manialinkid'] .'LabelCurrentRanking"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="18 2.625" textcolor="'. $this->config['CURRENT_RANKING'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['CURRENT_RANKING'][0]['LABEL'][0] .'"/>';
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

	private function buildLastBestTime ($show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			return;
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	PlayerLastBestTime
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
				if (Player.Login == InputPlayer.Login) {
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

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PlayerLastBestTime" name="'. $this->config['manialinkid'] .'/PlayerLastBestTime" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 88) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLastTime" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['LAST_TIME'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LAST_TIME'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="17.5 2.625" textcolor="'. $this->config['LAST_TIME'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelLastTime"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="24 2.625" textcolor="'. $this->config['LAST_TIME'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['LAST_TIME'][0]['LABEL'][0] .'"/>';
			$xml .= '</frame>';
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 115.25) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonBestTime" ScriptEvents="1"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.03" sizen="5.25 5.25" modulatecolor="'. $this->config['BEST_TIME'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['BEST_TIME'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.03" sizen="17.5 2.625" textcolor="'. $this->config['BEST_TIME'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="0:00.000" id="'. $this->config['manialinkid'] .'LabelBestTime"/>';
			$xml .= '<label posn="8.15 -4.2 0.03" sizen="24 2.625" textcolor="'. $this->config['BEST_TIME'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['BEST_TIME'][0]['LABEL'][0] .'"/>';
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

	private function buildPersonalBest ($score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			return;
		}

		$HandlePendingEvents = 'True';
		if (empty($this->config['PERSONAL_BEST'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	PersonalBest
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
	declare CMlLabel LabelPersonalBest <=> (Page.GetFirstChild("LabelPersonalBest") as CMlLabel);
	declare Integer NextUpdate = CurrentTime;
	declare Integer PersonalBestScore = {$score};
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
					// Check for improved PersonalBest of InputPlayer
					if (Player.Login == InputPlayer.Login && (Player.Score.BestRace.Time < PersonalBestScore || PersonalBestScore == 0)) {
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
					case CMlEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonPersonalBest") {
							TriggerPageAction("{$this->config['PERSONAL_BEST'][0]['ACTION'][0]}");
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
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'PersonalBest" name="'. $this->config['manialinkid'] .'/PersonalBest" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 142.5) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['PERSONAL_BEST'][0]['ACTION'][0])) {
				$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonPersonalBest" ScriptEvents="1"/>';
				$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			}
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['PERSONAL_BEST'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['PERSONAL_BEST'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['PERSONAL_BEST'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelPersonalBest"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['PERSONAL_BEST'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['PERSONAL_BEST'][0]['LABEL'][0] .'"/>';
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

	private function buildLocalRecord ($score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			return;
		}


		$HandlePendingEvents = 'True';
		if (empty($this->config['LOCAL_RECORD'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	LocalRecord
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
					case CMlEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonLocalRecord") {
							TriggerPageAction("{$this->config['LOCAL_RECORD'][0]['ACTION'][0]}");
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
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'LocalRecord" name="'. $this->config['manialinkid'] .'/LocalRecord" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 169.75) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['LOCAL_RECORD'][0]['ACTION'][0])) {
				$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonLocalRecord" ScriptEvents="1"/>';
				$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			}
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['LOCAL_RECORD'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['LOCAL_RECORD'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['LOCAL_RECORD'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelLocalRecord"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['LOCAL_RECORD'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['LOCAL_RECORD'][0]['LABEL'][0] .'"/>';
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

	private function buildDedimaniaRecord ($score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			return;
		}

		$HandlePendingEvents = 'True';
		if (empty($this->config['DEDIMANIA_RECORD'][0]['ACTION'][0])) {
			$HandlePendingEvents = 'False';
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	DedimaniaRecord
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
					case CMlEvent::Type::MouseClick : {
						if (Event.ControlId == "ButtonDedimaniaRecord") {
							TriggerPageAction("{$this->config['DEDIMANIA_RECORD'][0]['ACTION'][0]}");
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
}
--></script>
EOL;
		$xml = '<manialink id="'. $this->config['manialinkid'] .'DedimaniaRecord" name="'. $this->config['manialinkid'] .'/DedimaniaRecord" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 197) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['DEDIMANIA_RECORD'][0]['ACTION'][0])) {
				$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonDedimaniaRecord" ScriptEvents="1"/>';
				$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			}
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['DEDIMANIA_RECORD'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['DEDIMANIA_RECORD'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['DEDIMANIA_RECORD'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'" id="LabelDedimaniaRecord"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['DEDIMANIA_RECORD'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['DEDIMANIA_RECORD'][0]['LABEL'][0] .'"/>';
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

	private function buildManiaExchange ($score, $show = true) {
		global $aseco;

		if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			return;
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	ManiaExchange
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
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
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ButtonManiaExchange") {
						TriggerPageAction("{$this->config['MANIA_EXCHANGE'][0]['ACTION'][0]}");
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
		$xml = '<manialink id="'. $this->config['manialinkid'] .'ManiaExchange" name="'. $this->config['manialinkid'] .'/ManiaExchange" version="1">';
		if ($show == true) {
			$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 224.25) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
			$xml .= '<quad posn="0 0 0.01" sizen="27.25 7" bgcolor="'. $this->config['BAR'][0]['BACKGROUND_COLOR'][0] .'"/>';
			if (!empty($this->config['MANIA_EXCHANGE'][0]['ACTION'][0])) {
				$xml .= '<quad posn="0 0 0.02" sizen="27.25 7" bgcolor="'. $this->config['BOX'][0]['BACKGROUND_COLOR_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['BOX'][0]['BACKGROUND_COLOR_FOCUS'][0] .'" id="ButtonManiaExchange" ScriptEvents="1"/>';
				$xml .= '<quad posn="0.05 -4.325 0.03" sizen="2.625 2.625" image="'. $this->config['BOX'][0]['CLICKABLE_INDICATOR'][0] .'"/>';
			}
			$xml .= '<quad posn="0 0 0.04" sizen="0.1 7" bgcolor="'. $this->config['BOX'][0]['SEPERATOR_COLOR'][0] .'"/>';
			$xml .= '<quad posn="1.6 -1 0.04" sizen="5.25 5.25" modulatecolor="'. $this->config['MANIA_EXCHANGE'][0]['MODULATECOLOR'][0] .'" image="'. $this->config['MANIA_EXCHANGE'][0]['ICON'][0] .'"/>';
			$xml .= '<label posn="8.15 -1.4 0.04" sizen="17.5 2.625" textcolor="'. $this->config['MANIA_EXCHANGE'][0]['FONT_COLOR_TOP'][0] .'" textsize="1" text="'. $aseco->formatTime($score) .'"/>';
			$xml .= '<label posn="8.15 -4.2 0.04" sizen="24 2.625" textcolor="'. $this->config['MANIA_EXCHANGE'][0]['FONT_COLOR_BOTTOM'][0] .'" textsize="1" scale="0.6" text="'. $this->config['MANIA_EXCHANGE'][0]['LABEL'][0] .'"/>';
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
