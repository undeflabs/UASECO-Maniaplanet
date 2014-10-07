<?php
/*
 * Plugin: Chat Wins
 * ~~~~~~~~~~~~~~~~~
 * » Shows wins for current or given online player
 * » Based upon chat.wins.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-07
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
 *  - none
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatWins();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatWins extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Shows wins for current or given online player');

		$this->registerChatCommand('wins', 'chat_wins', 'Shows wins for current or given online player', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_wins ($aseco, $login, $chat_command, $chat_parameter) {

		if ($chat_parameter != '') {
			if (!$player = $aseco->server->players->getPlayer($chat_parameter)) {
				$message = '{#server}» {#error}Given player login {#highlite}'. $chat_parameter .'{#error} not found!';
				$aseco->sendChatMessage($message, $login);
				return;
			}
			$wins = $player->getWins();

			// use plural unless 1, and add ! for 2 or more
			$message = $aseco->formatText($aseco->getChatMessage('WINS_OTHER'),
				$aseco->stripColors($player->nickname),
				$wins,
				($wins == 1 ? '.' : ($wins > 1 ? 's!' : 's.'))
			);
		}
		else {
			if (!$player = $aseco->server->players->getPlayer($login)) {
				$wins = $player->getWins();

				// use plural unless 1, and add ! for 2 or more
				$message = $aseco->formatText($aseco->getChatMessage('WINS'),
					$wins,
					($wins == 1 ? '.' : ($wins > 1 ? 's!' : 's.'))
				);
			}
		}

		// Show chat message
		$aseco->sendChatMessage($message, $login);
	}
}

?>
