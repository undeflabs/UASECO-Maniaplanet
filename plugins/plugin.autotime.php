<?php
/*
 * Plugin: Autotime
 * ~~~~~~~~~~~~~~~~
 * » Changes Timelimit for TimeAttack dynamically depending on the next map's author time.
 * » Based upon plugin.autotime.php from XAseco2/1.03 written by ck|cyrus and Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-04
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
 *  - none, but must be after plugin.rasp_jukebox.php in config/plugins.xml
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginAutotime();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginAutotime extends Plugin {
	public $config;
	public $active;
	public $restart;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Changes Timelimit for TimeAttack dynamically depending on the next map\'s author time.');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onEndMap',	'onEndMap');		// use post event after all join processing
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Initialize flags
		$this->active = false;
		$this->restart = false;

		// Load config file
		$config_file = 'config/autotime.xml';
		if (file_exists($config_file)) {
			$aseco->console('[AutoTime] Load auto timelimit config ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {
				$this->config = $xml['AUTOTIME'];
				$this->active = true;
			}
			else {
				trigger_error('[AutoTime] Could not read/parse config file ['. $config_file .']!', E_USER_WARNING);
			}
		}
		else {
			trigger_error('[AutoTime] Could not find config file ['. $config_file .']!', E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// If not active, bail out immediately
		if (!$this->active) {
			return;
		}

		// If restarting, bail out immediately
		if ($this->restart) {
			$this->restart = false;
			return;
		}

		// Get next game settings
		$nextgame = $aseco->client->query('GetNextGameInfo');

		// Check for TimeAttack on next map
		if ($nextgame['GameMode'] == Gameinfo::TIMEATTACK) {
			// Check if auto timelimit enabled
			if ($this->config['MULTIPLICATOR'][0] > 0) {
				// Check if at least one active player on the server
				if ( $this->checkForActivePlayer() ) {
					// Get next map object
					$map = $aseco->server->maps->getNextMap();
					$newtime = intval($map->author_time);
				}
				else {
					// Server already switched so get current map name
					$newtime = 0;  // force default
					$newtime = intval($aseco->server->maps->current->author_time);
				}

				// Compute new timelimit
				if ($newtime <= 0) {
					$newtime = $this->config['DEFAULTTIME'][0] * 60 * 1000;
					$tag = 'default';
				}
				else {
					$newtime *= $this->config['MULTIPLICATOR'][0];
					$newtime -= ($newtime % 1000);  // round down to seconds
					$tag = 'new';
				}

				// Check for min/max times
				if ($newtime < $this->config['MINTIME'][0] * 60 * 1000) {
					$newtime = $this->config['MINTIME'][0] * 60 * 1000;
					$tag = 'min';
				}
				else if ($newtime > $this->config['MAXTIME'][0] * 60 * 1000) {
					$newtime = $this->config['MAXTIME'][0] * 60 * 1000;
					$tag = 'max';
				}

				// Set and log timelimit (strip .00 sec)
				$aseco->client->addcall('SetTimeAttackLimit', $newtime);
				$aseco->console('[AutoTime] set {1} timelimit for [{2}]: {3} (Author time: {4})',
					$tag, stripColors($map->name, false),
					substr($aseco->formatTime($newtime), 0, -3),
					$aseco->formatTime($map->author_time)
				);

				// Display timelimit (strip .00 sec)
				$message = $aseco->formatText($this->config['MESSAGE'][0], $tag,
					stripColors($map->name),
					substr($aseco->formatTime($newtime), 0, -3),
					$aseco->formatTime($map->author_time)
				);

				if ($this->config['DISPLAY'][0] == 2) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, true));
				}
				else if ($this->config['DISPLAY'][0] > 0) {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Check for at least one active player
	public function checkForActivePlayer () {
		global $aseco;

		// Check all connected players
		foreach ($aseco->server->players->player_list as $player) {
			// Get current player status
			if (!$player->isspectator) {
				return true;
			}
		}
		return false;
	}
}

?>
