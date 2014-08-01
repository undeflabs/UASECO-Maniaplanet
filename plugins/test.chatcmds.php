<?php
/*
 * Plugin: Test Chat
 * ~~~~~~~~~~~~~~~~~
 * » Testing all chat commands with all parameters.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-06-14
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
	$_PLUGIN = new PluginTestChat();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginTestChat extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Testing all chat commands with all parameters.');

		$this->registerChatCommand('testchat', 'chat_testchat', 'Testing all chat commands with all parameters.', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_testchat ($aseco, $login, $chat_command, $chat_parameter) {


		foreach ($aseco->registered_chatcmds as $command => $data) {
			if ($command == 'testchat') {
				continue;
			}

			if (count($data['params']) > 0) {
				foreach ($data['params'] as $param => $help) {
					$aseco->console('Calling "/'. $command .' '. $param .'"');
					$aseco->releaseChatCommand('/'. $command .' '. $param, $login);
				}
			}
			else {
				$aseco->console('Calling "/'. $command .'"');
				$aseco->releaseChatCommand('/'. $command, $login);
			}
		}
	}
}

?>
