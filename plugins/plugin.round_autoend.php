<?php
/*
 * Plugin: Rounds
 * ~~~~~~~~~~~~~~
 * » Ends the current round after a computed amount of time automatically.
 * » Based upon plugin.autoendround.php/1.0 written by -nocturne=-
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
	$_PLUGIN = new PluginRoundAutoEnd();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRoundAutoEnd extends Plugin {
	public $config;
	public $timer;
	public $time_delta;
	public $time_scoreboard;
	public $time_countdown;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2017-05-28');
		$this->setCopyright('2015 - 2017 by undef.de');
		$this->setDescription(new Message('plugin.round_autoend', 'plugin_description'));

		// Register functions for events
		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');
		$this->registerEvent('onBeginRound',		'onBeginRound');
		$this->registerEvent('onEverySecond',		'onEverySecond');
		$this->registerEvent('onEndRound',		'onEndRound');
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
		$this->time_delta = 0;
		$this->time_scoreboard = 7;					// Add 7 seconds for the scoreboard
		$this->time_countdown = 4;					// Add 4 seconds for the 3-2-1-GO!
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			if ($this->timer > 0 && time() >= $this->timer) {
				// Reset timer
				$this->timer = 0;

				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.ForceEndRound', array((string)time()));

				$aseco->console('[RoundAutoEnd] Round automatically ended');

				$message = new Message('plugin.round_autoend', 'message_round_end');
				$message->addPlaceholders(
					$aseco->formatTime(($this->time_delta + $this->time_scoreboard) * 1000)
				);
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
			$this->time_delta = ceil(($map->author_time / 1000) * $this->config['multiplicator']);

			$aseco->console('[RoundAutoEnd] Setting round end time to ['. ($this->time_delta + $this->time_scoreboard) .'] seconds, based upon author time ['. $aseco->formatTime($map->author_time) .']');

			$message = new Message('plugin.round_autoend', 'message_round_info');
			$message->addPlaceholders(
				$aseco->formatTime(($this->time_delta + $this->time_scoreboard) * 1000),
				$aseco->formatTime($map->author_time)
			);
			$message->sendChatMessage();

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
			$this->timer = (time() + $this->time_delta + $this->time_countdown + $this->time_scoreboard);
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
