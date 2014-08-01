<?php
/*
 * Plugin: Last Window
 * ~~~~~~~~~~~~~~~~~~~
 * » Re-displays last closed multi-page window.
 * » Based upon chat.lastwin.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-07
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
 *  - plugins/plugin.manialinks.php
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

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Re-displays last closed multi-page window.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerChatCommand('lastwin', 'chat_lastwin', 'Re-opens the last closed multi-page window', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lastwin ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);
		if (!isset($player->msgs) || empty($player->msgs)) {
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No multi-page window available!'), $player->login);
			return;
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
	}
}

?>
