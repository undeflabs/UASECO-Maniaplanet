<?php
/*
 * Plugin: Effect Studio
 * ~~~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Effect-Studio.php
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Version:		1.0.0
 * Date:		2015-08-21
 * Copyright:		2012 - 2015 by undef.de
 * System:		UASECO/0.9.5+
 * Game:		ManiaPlanet Trackmania2 (TM2)
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
 *
 */


	// Start the plugin
	$_PLUGIN = new PluginEffectStudio();


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginEffectStudio extends Plugin {
	public $config			= array();

	private $manialinks		= array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Plays/Displays several effects on configured events.');

		$this->addDependence('PluginModescriptHandler',	Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');

//		$this->registerChatCommand('xxx',		'chat_xxx',	'xxx',			Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.5';
		if ( defined('UASECO_VERSION') ) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[EffectStudio] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[EffectStudio] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}

		if (!$this->config = $aseco->parser->xmlToArray('config/effect_studio.xml')) {
			trigger_error('[EffectStudio] Could not read/parse config file "config/effect_studio.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];


		// Listing of currently supported events
		$valid_events = array(
			// Event name (XML)		=> realname in UASECO
			'onNewMap'			=> 'onLoadingMap',
			'onBeginRound'			=> 'onBeginRound',
			'onPlayerCheckpoint'		=> 'onPlayerCheckpoint',
			'onPlayerConnect'		=> 'onPlayerConnect1',
			'onPlayerStartCountdown'	=> 'onPlayerStartCountdown',
			'onPlayerStartLine'		=> 'onPlayerStartLine',
			'onPlayerFinish'		=> 'onPlayerFinish1',
			'onPlayerWins'			=> 'onPlayerWins',
			'onEndRound'			=> 'onEndRound',
			'onEndMap'			=> 'onEndMap1',
			'onRestartMap'			=> 'onRestartMap',
			'onLocalRecord'			=> 'onLocalRecord',
			'onDedimaniaRecord'		=> 'onDedimaniaRecord',

//			'onPlayerToSpectator'		=> 'onPlayerInfoChanged',
//			'onSpectatorToPlayer'		=> 'onPlayerInfoChanged',
//			'onPlayerDisconnect'		=> 'onPlayerDisconnect',
//			'onDonation'			=> 'onDonation',
//			'onJukeboxChanged'		=> 'onJukeboxChanged',
//
//			'onMultilapFinishLap'		=> 'xxx',					// only in Laps (maybe Rounds + Cup?)
//			'onMultilapEndLap'		=> 'xxx',					// only in Laps (maybe Rounds + Cup?)
//
//			'onKarmaChange'			=> 'onKarmaChange',				// Event from plugin.mania_karma.php / plugin.rasp_karma.php
			'onPlayerWinCurrency'		=> 'onPlayerWinPlanets',			// Event from plugin.records_eyepiece.php
		);


		$aseco->console('[EffectStudio] Setup events and effects...');

		// Event 'onLoadingMap' already registered, let skip them
		$registered_events = array('onLoadingMap');

		// Register related events and build the database
		$this->config['EventActions'] = array();
		foreach ($this->config['AUDITIVE'][0]['EFFECT'] as &$item) {
			if ( array_key_exists($item['EVENT'][0], $valid_events) ) {
				if (strtoupper($item['ENABLED'][0]) == 'TRUE') {
					// Retrieve the file (max. 1024 bytes) to build a MD5-Hash from
					$file = @file_get_contents($item['URL'][0], NULL, NULL, 1024);
					if ($file === false) {
						$headers = get_headers($item['URL'][0]);
						$aseco->console('[EffectStudio] Error while trying to read file "'. $item['URL'][0] .'": '. $headers[0]);
						continue;
					}

					// Add into register and translate e.g. 'onNewMap' to 'onLoadingMap'
					$this->config['EventActions'][$item['EVENT'][0]] = array(
						'Url'		=> $item['URL'][0],
						'ChkSum'	=> md5($file),
						'Loop'		=> ((strtoupper($item['LOOP'][0]) == 'TRUE') ? 1 : 0),
						'Timeout'	=> (intval($item['LENGTH'][0]) + 2),
					);

					// Check for already registered events and skip them
					if ( !in_array($valid_events[$item['EVENT'][0]], $registered_events) ) {
						$aseco->registerEvent($valid_events[$item['EVENT'][0]], array($this, $valid_events[$item['EVENT'][0]]));
						$registered_events[] = $valid_events[$item['EVENT'][0]];
					}
				}
			}
			else {
				$aseco->console('[EffectStudio] Found a none valid event "'. $item['EVENT'][0] .'", ignoring...');
			}
		}
		unset($item);

		foreach ($this->config['SCRIPTS'][0]['SCRIPT'] as &$script) {
			if (strtoupper($script['ENABLED'][0]) == 'TRUE') {
				$file = @file_get_contents($script['PATH'][0]);
				if ($file !== false) {
					$this->manialinks[] = $file;
				}
				else {
					$aseco->console('[EffectStudio] Error while trying to read file "'. $script['PATH'][0] .'".');
				}
			}
		}
		$aseco->console('[EffectStudio] ...successfully done!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerStartCountdown ($aseco, $login) {
		if (isset($this->config['EventActions']['onPlayerStartCountdown'])) {
			$this->eventHandler('auditive', $login, 'onPlayerStartCountdown');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerStartLine ($aseco, $login) {
		if (isset($this->config['EventActions']['onPlayerStartLine'])) {
			$this->eventHandler('auditive', $login, 'onPlayerStartLine');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $unused) {
		if (count($this->manialinks) > 0) {
			foreach ($this->manialinks as $manialink) {
				$aseco->sendManialink($manialink, false);
			}
		}
		if (isset($this->config['EventActions']['onNewMap'])) {
			$this->eventHandler('auditive', false, 'onNewMap');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginRound ($aseco) {
		$this->eventHandler('auditive', false, 'onBeginRound');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $param = [0]=Login, [1]=WaypointBlockId, [2]=Time, [3]=WaypointIndex, [4]=CurrentLapTime, [5]=LapWaypointNumber
	public function onPlayerCheckpoint ($aseco, $param) {
		$this->eventHandler('auditive', $param[0], 'onPlayerCheckpoint');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {
		if (count($this->manialinks) > 0) {
			$aseco->sendManialink(implode('', $this->manialinks), $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect1 ($aseco, $player) {
		$this->eventHandler('auditive', $player->login, 'onPlayerConnect');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish1 ($aseco, $finish) {
		if ($finish->score > 0) {
			if (isset($this->config['EventActions']['onPlayerFinish'])) {
				$this->eventHandler('auditive', $finish->player->login, 'onPlayerFinish');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerWins ($aseco, $player) {
		$this->eventHandler('auditive', $player->login, 'onPlayerWins');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco) {
		$this->eventHandler('auditive', false, 'onEndRound');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $unused) {
		$this->eventHandler('auditive', false, 'onEndMap');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $unused) {
		$this->eventHandler('auditive', false, 'onRestartMap');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLocalRecord ($aseco, $record) {
		$this->eventHandler('auditive', $record->player->login, 'onLocalRecord');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onDedimaniaRecord ($aseco, $record) {
		$this->eventHandler('auditive', $record['Login'], 'onDedimaniaRecord');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildScriptPlayerFinishMapTime () {

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function eventHandler ($typ, $login, $event) {
		global $aseco;

		$xml = false;
		if (($typ == 'auditive') || ($typ == 'both') ) {
			$xml .= '<manialink name="EffectStudioAuditive'. ucfirst($event) .'" id="EffectStudioAuditive'. ucfirst($event) .'">';
			$xml .= '<audio posn="140 0 0" sizen="3 3" data="'. $this->config['EventActions'][$event]['Url'] .'?chksum='. $this->config['EventActions'][$event]['ChkSum'] .'.ogg" play="1" looping="'. $this->config['EventActions'][$event]['Loop'] .'" />';
			$xml .= '</manialink>';
		}
		elseif (($typ == 'visual') || ($typ == 'both') ) {

		}

//		$aseco->console('*** Sending to '. $login .' -> '. $xml);
		if ($xml !== false) {
			$aseco->sendManialink($xml, $login, $this->config['EventActions'][$event]['Timeout']);
		}
	}
}

?>
