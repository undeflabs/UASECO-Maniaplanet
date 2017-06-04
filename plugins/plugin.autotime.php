<?php
/*
 * Plugin: Autotime
 * ~~~~~~~~~~~~~~~~
 * » Changes Timelimit dynamically depending on the next map's author time.
 * » Based upon plugin.autotime.php from XAseco2/1.03 written by ck|cyrus and Xymph
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
	$_PLUGIN = new PluginAutotime();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginAutotime extends Plugin {
	public $config;
	public $active;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-06-04');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription(new Message('plugin.autotime', 'plugin_description'));

		$this->addDependence('PluginModescriptHandler',	Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onLoadingMap',	'onLoadingMap');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Load config file
		$config_file = 'config/autotime.xml';
		if (file_exists($config_file)) {
			$aseco->console('[AutoTime] Load auto timelimit config ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {
				$this->config = $xml['SETTINGS'];
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

	public function onLoadingMap ($aseco, $map) {

		// Check for compatible Gamemode on next map
		$gamemode = $aseco->server->gameinfo->mode;
		if ($gamemode == Gameinfo::TIME_ATTACK || $gamemode == Gameinfo::LAPS || $gamemode == Gameinfo::TEAM_ATTACK || $gamemode == Gameinfo::CHASE) {
			// Check if auto timelimit enabled
			if ($this->config['MULTIPLICATOR'][0] > 0) {
				// Setup calculation base
				if (strtoupper($this->config['CALCULATION_BASE'][0]) == 'AUTHOR') {
					$newtime = substr((int)$map->author_time, 0, -3);
				}
				else if (strtoupper($this->config['CALCULATION_BASE'][0]) == 'GOLD') {
					$newtime = substr((int)$map->gold_time, 0, -3);
				}
				else if (strtoupper($this->config['CALCULATION_BASE'][0]) == 'SILVER') {
					$newtime = substr((int)$map->silver_time, 0, -3);
				}
				else if (strtoupper($this->config['CALCULATION_BASE'][0]) == 'BRONZE') {
					$newtime = substr((int)$map->bronze_time, 0, -3);
				}

				// Compute new timelimit
				if ($newtime <= 0) {
					$newtime = $this->config['DEFAULTTIME'][0] * 60;
					$msg = new Message('plugin.autotime', 'message_default');
					$tag = 'default';
				}
				else {
					$newtime *= $this->config['MULTIPLICATOR'][0];
					$msg = new Message('plugin.autotime', 'message_new');
					$tag = 'new';
				}

				// Check for min/max times
				if ($newtime < $this->config['MINTIME'][0] * 60) {
					$newtime = $this->config['MINTIME'][0] * 60;
					$msg = new Message('plugin.autotime', 'message_minimum');
					$tag = 'minimum';
				}
				else if ($newtime > $this->config['MAXTIME'][0] * 60) {
					$newtime = $this->config['MAXTIME'][0] * 60;
					$msg = new Message('plugin.autotime', 'message_maximum');
					$tag = 'maximum';
				}

				// Send new time
				if ($gamemode == Gameinfo::TIME_ATTACK) {
					$aseco->server->gameinfo->time_attack['TimeLimit'] = (int)$newtime;
				}
				else if ($gamemode == Gameinfo::LAPS) {
					$aseco->server->gameinfo->laps['TimeLimit'] = (int)$newtime;
				}
				else if ($gamemode == Gameinfo::TEAM_ATTACK) {
					$aseco->server->gameinfo->team_attack['TimeLimit'] = (int)$newtime;
				}
				else if ($gamemode == Gameinfo::CHASE) {
					$aseco->server->gameinfo->chase['TimeLimit'] = (int)$newtime;
				}
				$aseco->plugins['PluginModescriptHandler']->setupModescriptSettings();

				// Set and log timelimit (strip .000 sec)
				$aseco->console('[AutoTime] Setting {1} timelimit for Map [{2}] to [{3}], based upon {4} time [{5}]',
					$tag,
					$aseco->stripStyles($map->name, false),
					substr($aseco->formatTime($newtime * 1000), 0, -4),
					strtolower($this->config['CALCULATION_BASE'][0]),
					$aseco->formatTime($map->author_time)
				);

				// Display timelimit (strip .000 sec)
				$msg->addPlaceholders(
					$aseco->stripStyles($map->name),
					substr($aseco->formatTime($newtime * 1000), 0, -4),
					new Message('common', 'medal_'.strtolower($this->config['CALCULATION_BASE'][0])),
					$aseco->formatTime($map->author_time)
				);
				$msg->sendChatMessage();

				if ($this->config['DISPLAY'][0] == 2) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, true));
				}
				else if ($this->config['DISPLAY'][0] > 0) {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}
}

?>
