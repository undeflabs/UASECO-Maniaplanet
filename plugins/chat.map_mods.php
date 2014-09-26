<?php
/*
 * Plugin: Song Mod
 * ~~~~~~~~~~~~~~~~~
 * » Shows (file)names of current map's and song mod.
 * » Based upon chat.songmod.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-26
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
	$_PLUGIN = new PluginChatMapMods();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatMapMods extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Shows (file)names of current map\'s and song mod.');

		$this->registerChatCommand('song',	'chat_song',	'Shows filename of current map\'s song',	Player::PLAYERS);
		$this->registerChatCommand('mod',	'chat_mod',	'Shows (file)name of current map\'s mod',	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_song ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// Check for map's song
		if ($aseco->server->maps->current->songfile) {
			$message = $aseco->formatText($aseco->getChatMessage('SONG'),
				$aseco->stripColors($aseco->server->maps->current->name),
				$aseco->server->maps->current->songfile
			);

			// Use only first parameter
			$chat_parameter = explode(' ', $chat_parameter, 2);
			if ((strtolower($chat_parameter[0]) == 'url' || strtolower($chat_parameter[0]) == 'loc') && $aseco->server->maps->current->songurl) {
				$message .= LF .'{#highlite}$l['. $aseco->server->maps->current->songurl .']'. $aseco->server->maps->current->songurl .'$l';
			}
		}
		else {
			$message = '{#server}» {#error}No map song found!';
			if ((class_exists('PluginMusicServer')) && (is_callable('PluginMusicServer::chat_music')) ) {
				$message .= ' Try {#highlite}$i /music current {#error}instead.';
			}
		}

		// Show chat message
		$aseco->sendChatMessage($message, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mod ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// Check for map's mod
		if ($aseco->server->maps->current->modname) {
			$message = $aseco->formatText($aseco->getChatMessage('MOD'),
				$aseco->stripColors($aseco->server->maps->current->name),
				$aseco->server->maps->current->modname,
				$aseco->server->maps->current->modfile
			);
			// Use only first parameter
			$chat_parameter = explode(' ', $chat_parameter, 2);
			if ((strtolower($chat_parameter[0]) == 'url' || strtolower($chat_parameter[0]) == 'loc') && $aseco->server->maps->current->modurl) {
				$message .= LF .'{#highlite}$l['. $aseco->server->maps->current->modurl .']'. $aseco->server->maps->current->modurl .'$l';
			}
		}
		else {
			$message = '{#server}» {#error}No map mod found!';
		}

		// Show chat message
		$aseco->sendChatMessage($message, $player->login);
	}
}

?>
