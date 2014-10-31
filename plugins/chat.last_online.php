<?php
/*
 * Plugin: Last Online
 * ~~~~~~~~~~~~~~~~~~~
 * » Shows when a player was last online.
 * » Based upon chat.laston.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-26
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
	$_PLUGIN = new PluginLastOnline();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginLastOnline extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Shows when a player was last online...');

		$this->registerChatCommand('laston', 'chat_laston', 'Shows when a player was last online', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_laston ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$target = $player;

		// Get given player login for last online query
		if ($chat_parameter != '') {
			if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
				return;
			}
		}

		// Obtain last online timestamp
		$query = "
		SELECT
			DATE_FORMAT(`LastVisit`, '%W, %D %M %Y at %H:%i o\'clock')
		FROM `%prefix%players`
		WHERE `Login` = ". $aseco->db->quote($target->login) ."
		LIMIT 1;
		";

		$result = $aseco->db->query($query);
		if ($result) {
			$laston = $result->fetch_row();
			$result->free_result();
			$laston = $laston[0];

			$message = '{#server}» Player {#highlite}'. $target->nickname .'$z$s{#server} was last online on: {#highlite}'. $laston;
		}
		else {
			trigger_error('Could not query last online for player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}

		// Show chat message
		$aseco->sendChatMessage($message, $player->login);
	}
}

?>
