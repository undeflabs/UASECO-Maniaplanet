<?php
/*
 * Plugin: Chat Wins
 * ~~~~~~~~~~~~~~~~~
 * » Shows wins for current or given online player
 * » Based upon chat.wins.php from XAseco2/1.03 written by Xymph
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

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-04-27');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription(new Message('chat.wins', 'plugin_description'));

		$this->registerChatCommand('wins', 'chat_wins', new Message('chat.wins', 'plugin_description'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_wins ($aseco, $login, $chat_command, $chat_parameter) {

		if ($chat_parameter != '') {
			if (!$player = $aseco->server->players->getPlayerByLogin($chat_parameter)) {
				$msg = new Message('chat.wins', 'message_player_not_found');
				$msg->addPlaceholders($chat_parameter);
				$msg->sendChatMessage($login);
				return;
			}
			$wins = $player->getWins();

			$msg = new Message('common', 'wins_other');
			$msg->addPlaceholders($aseco->stripStyles($player->nickname), $wins);
			$msg->sendChatMessage($login);
		}
		else {
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				$wins = $player->getWins();

				$msg = new Message('common', 'wins');
				$msg->addPlaceholders($wins);
				$msg->sendChatMessage($login);
			}
		}
	}
}

?>
