<?php
/*
 * Plugin: Last Window
 * ~~~~~~~~~~~~~~~~~~~
 * » Re-opens the last closed window.
 * » Based upon chat.lastwin.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginLastWindow();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginLastWindow extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-04-18');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Re-opens the last closed window.');

		$this->registerChatCommand('lastwin', 'chat_lastwin', new Message('chat.last_window', 'slash_lastwin'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lastwin ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
		$param = array(
			$player->pid,
			$login,
			'WindowList?Action=ClassWindowRefreshPage',
			false,
		);

		// Simulate a Player click event
		$aseco->releaseEvent('onPlayerManialinkPageAnswer', $param);
	}
}

?>
