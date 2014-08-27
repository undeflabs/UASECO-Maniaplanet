<?php
/*
 * Plugin: Chat Help
 * ~~~~~~~~~~~~~~~~~
 * » Displays help for public chat commands.
 * » Based upon chat.help.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-19
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
 *  - includes/core/window.class.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatHelp();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatHelp extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays help for public chat commands.');

		// Register chat-commands
		$this->registerChatCommand('help', 'chat_help', 'Displays help for available commands.', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_help ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);

		// Check for higher rights of Player
		$showadmin = false;

		$commands = $aseco->registered_chatcmds;
		ksort($commands);

		$data = array();
		foreach ($commands as $name => $cc) {
			// collect either admin or non-admin commands
			$allowed = false;
			if ($showadmin == true) {
				if ($cc['rights'] & Player::OPERATORS) {
					// Chat command is only allowed for Operators, Admins or MasterAdmins
					$allowed = true;
				}
				else if ($cc['rights'] & Player::ADMINS) {
					// Chat command is only allowed for Admins or MasterAdmins
					$allowed = true;
				}
				else if ($cc['rights'] & Player::MASTERADMINS) {
					// Chat command is only allowed for MasterAdmins
					$allowed = true;
				}
				if ($allowed == true) {
					foreach ($cc['params'] as $cmd => $description) {
						$data[] = array('/'. $cmd, $description);
					}
				}
			}
			else {
				if ($cc['rights'] & Player::PLAYERS) {
					// Chat command is allowed for everyone
					$allowed = true;
				}
				if ($allowed == true) {
					$data[] = array('/'.$name, $cc['help']);
				}
			}
		}

		// Setup settings for Window
		$settings_title = array(
			'icon'	=> 'ManiaPlanetMainMenu,IconStore',
		);
		$settings_heading = array(
			'textcolors'	=> array('FF5F', 'FFFF'),
		);
		$settings_columns = array(
			'columns'	=> 2,
			'widths'	=> array(25, 75),
			'textcolors'	=> array('FF5F', 'FFFF'),
			'heading'	=> array('Command', 'Description'),
		);

		$window = new Window();
		$window->setLayoutTitle($settings_title);
		$window->setLayoutHeading($settings_heading);
		$window->setColumns($settings_columns);
		$window->setContent('Currently supported chat commands', $data);
		$window->send($player, 0, false);
	}
}

?>
