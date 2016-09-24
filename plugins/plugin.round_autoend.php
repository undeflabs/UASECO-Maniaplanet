<?php
/*
 * Plugin: Rounds
 * ~~~~~~~~~~~~~~
 * » Ends the current round after a computed amount of time automatically.
 * » Based upon plugin.autoendround.php/1.0 written by -nocturne=-
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-11-08
 * Copyright:	2015 by undef.de
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
 *  - none
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginRoundAutoEnd();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRoundAutoEnd extends Plugin {
	public $config;
	public $timer;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Ends the current round after a computed amount of time automatically.');

		// Register functions for events
		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onLoadingMap',	'onLoadingMap');
		$this->registerEvent('onBeginRound',	'onBeginRound');
		$this->registerEvent('onEverySecond',	'onEverySecond');
		$this->registerEvent('onEndRound',	'onEndRound');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$xml = $aseco->parser->xmlToArray('config/round_autoend.xml', true, true)) {
			trigger_error('[RoundAutoEnd] Could not read/parse config file "config/round_autoend.xml"!', E_USER_ERROR);
		}
		$this->config['multiplicator'] = $xml['SETTINGS']['MULTIPLICATOR'][0];
		unset($xml);

		// Init
		$this->timer = 0;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			if ($this->timer > 0 && time() >= $this->timer) {
				$aseco->client->query('TriggerModeScriptEvent', 'Rounds_ForceEndRound', '');

				$aseco->console('[RoundAutoEnd] Round automatically ended');

				$message = new Message('plugin.round_autoend', 'message_roundend');
				$message->addPlaceholders($this->config['time_delta']);
				$message->sendChatMessage();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			$this->config['time_delta'] = ceil(($map->author_time / 1000) * $this->config['multiplicator']);
			$aseco->console('[RoundAutoEnd] Setting round end time to '. $this->config['time_delta'] .' seconds, based upon author time '. $aseco->formatTime($map->author_time));

			// On startup execute onBeginRound(), the ModeScript does this only when the round really begins!
			if ($aseco->startup_phase == true) {
				$this->onBeginRound($aseco);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginRound ($aseco) {
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			$this->timer = (time() + $this->config['time_delta'] + 4);		// Add 4 seconds for the 3-2-1-GO!
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco) {
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			$this->timer = 0;
		}
	}
}

?>
