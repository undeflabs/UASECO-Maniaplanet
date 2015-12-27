<?php
/*
 * Plugin: Chat Me
 * ~~~~~~~~~~~~~~~
 * » Builds a chat message starting with the nickname from player.
 * » Based upon chat.me.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Co-Authors:	askuri
 * Date:	2015-11-11
 * Copyright:	2014 - 2015 by undef.de, askuri
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
	$_PLUGIN = new PluginChatMe();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatMe extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription(new Message('chat.me', 'plugin_description'));

		$this->registerChatCommand('me', 'chat_me', new Message('chat.me', 'plugin_description'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_me ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$msg = new Message('common', 'muted');
			$msg->addPlaceholders('/me');
			$msg->sendChatMessage($login);
			return;
		}

		// Replace parameters
		$message = $aseco->formatText('$i{1}$z$s$i {#emotic}{2}',
			$player->nickname,
			$chat_parameter
		);

		// Show chat message
		$aseco->sendChatMessage($message);
	}
}

?>
