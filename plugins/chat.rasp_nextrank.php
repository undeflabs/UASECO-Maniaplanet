<?php
/*
 * Plugin: Chat Rasp Nextrank
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Shows the next better ranked player.
 * » Based upon plugin.rasp_nextrank.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-07-03
 * Copyright:	2014 - 2015 by undef.de
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
 *  - plugins/plugin.rasp.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatRaspNextrank();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatRaspNextrank extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Shows the next better ranked player.');

		$this->addDependence('PluginRasp', Dependence::REQUIRED, '1.0.0', null);

		$this->registerChatCommand('nextrank', 'chat_nextrank', 'Shows the next better ranked player.', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nextrank ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($aseco->plugins['PluginRasp']->feature_ranks) {
			// find current player's avg
			$query = "
			SELECT
				`Average`
			FROM `%prefix%rankings`
			WHERE `PlayerId` = ". $player->id .";
			";

			$res = $aseco->db->query($query);
			if ($res->num_rows > 0) {
				$row = $res->fetch_array(MYSQLI_ASSOC);
				$avg = $row['Average'];

				// find players with better avgs
				$query = "
				SELECT
					`PlayerId`,
					`Average`
				FROM `%prefix%rankings`
				WHERE `Average` <". $avg ."
				ORDER BY `Average`;
				";

				$res2 = $aseco->db->query($query);
				if ($res2->num_rows > 0) {
					// find last player before current one
					while ($row2 = $res2->fetch_array(MYSQLI_ASSOC)) {
						$pid = $row2['PlayerId'];
						$avg2 = $row2['Average'];
					}

					// obtain next player's info
					$query = "
					SELECT
						`Login`,
						`Nickname`
					FROM `%prefix%players`
					WHERE `PlayerId` = ". $pid .";
					";
					$res3 = $aseco->db->query($query);
					$row3 = $res3->fetch_array(MYSQLI_ASSOC);

					$rank = $aseco->plugins['PluginRasp']->getRank($row3['Login']);
					$rank = preg_replace('|^(\d+)/|', '{#rank}$1{#record}/{#highlite}', $rank);

					// show chat message
					$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['NEXTRANK'][0],
						$aseco->stripColors($row3['Nickname']),
						$rank
					);

					// show difference in record positions too?
					if ($aseco->plugins['PluginRasp']->nextrank_show_rp) {
						// compute difference in record positions
						$diff = ($avg - $avg2) / 10000 * count($aseco->server->maps->map_list);
						$message .= $aseco->formatText($aseco->plugins['PluginRasp']->messages['NEXTRANK_RP'][0], ceil($diff));
					}
					$aseco->sendChatMessage($message, $player->login);
					$res3->free_result();
				}
				else {
					$message = $aseco->plugins['PluginRasp']->messages['TOPRANK'][0];
					$aseco->sendChatMessage($message, $player->login);
				}
				$res2->free_result();
			}
			else {
				$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['RANK_NONE'][0], $aseco->plugins['PluginRasp']->minrank);
				$aseco->sendChatMessage($message, $player->login);
			}
			$res->free_result();
		}
	}
}

?>
