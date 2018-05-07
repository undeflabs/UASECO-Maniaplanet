<?php
/*
 * Plugin: Chat Help
 * ~~~~~~~~~~~~~~~~~
 * » Displays help for public chat commands.
 * » Based upon chat.help.php from XAseco2/1.03 written by Xymph and others
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

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription(new Message('chat.help', 'plugin_description'));

		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');

		// Register chat-commands
		$this->registerChatCommand('help', 'chat_help', new Message('chat.help', 'slash_help_description'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_help ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check for higher rights of Player
		$showadmin = false;

		$commands = $aseco->registered_chatcmds;
		ksort($commands);

		$data = array();
		foreach ($commands as $name => $cc) {
			// collect either admin or non-admin commands
			$allowed = false;
			if ($showadmin === true) {
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
				if ($allowed === true) {
					foreach ($cc['params'] as $cmd => $description) {
						$data[] = array('/'. $cmd, $description);
					}
				}
			}
			else {
				if ($cc['rights'] & Player::PLAYERS) {
					// Chat command is allowed for everyone
					$message = $aseco->locales->handleMessage($cc['help'], $login);
					$data[] = array(
						array(
							'action'	=> 'PluginChatHelp?Action=ReleaseChatCommand&amp;command=/'. $name,		// Execute on click
							'title'		=> '/'.$name,									// Display name
						),
						$message,
					);
				}
			}
		}

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'ManiaPlanetMainMenu,IconStore',
			'textcolors'		=> array('FF5F', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 2,
			'widths'		=> array(25, 75),
			'textcolors'		=> array('FF5F', 'FFFF'),
			'heading'		=> array('Command', 'Description'),
		);
		$settings_content = array(
			'title'			=> (new Message('chat.help', 'help_window_title'))->finish($login),
			'data'			=> $data,
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {

		if ($params['Action'] === 'ReleaseChatCommand') {
			$aseco->releaseChatCommand($params['command'], $login);
		}
	}
}

?>
