<?php
/*
 * Plugin: Record Relations
 * ~~~~~~~~~~~~~~~~~~~~~~~~
 * » Shows ranked records and their relations on the current map.
 * » Based upon chat.recrels.php from XAseco2/1.03 written by Xymph
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
 *  - plugins/plugin.local_records.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginRecordRelations();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRecordRelations extends Plugin {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Shows ranked records and their relations on the current map.');

		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);

		$this->registerChatCommand('firstrec',	'chat_firstrec',	'Shows first ranked record on current map',	Player::PLAYERS);
		$this->registerChatCommand('lastrec',	'chat_lastrec',		'Shows last ranked record on current map',	Player::PLAYERS);
		$this->registerChatCommand('nextrec',	'chat_nextrec',		'Shows next better ranked record to beat',	Player::PLAYERS);
		$this->registerChatCommand('diffrec',	'chat_diffrec',		'Shows your difference to first ranked record',	Player::PLAYERS);
		$this->registerChatCommand('recrange',	'chat_recrange',	'Shows difference first to last ranked record',	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_firstrec ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($aseco->plugins['PluginLocalRecords']->records->count() > 0) {
			// get the first ranked record
			$record = $aseco->plugins['PluginLocalRecords']->records->getRecord(0);

			// show chat message
			$message = $aseco->formatText($aseco->getChatMessage('FIRST_RECORD'))
			         . $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				1,
				$aseco->stripColors($record->player->nickname),
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $record->score : $aseco->formatTime($record->score))
			);
			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lastrec ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			// get the last ranked record
			$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($total-1);

			// show chat message
			$message = $aseco->formatText($aseco->getChatMessage('LAST_RECORD'))
			         . $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				$total,
				$aseco->stripColors($record->player->nickname),
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $record->score : $aseco->formatTime($record->score))
			);
			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nextrec ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			$found = false;

			// find ranked record
			for ($i = 0; $i < $total; $i++) {
				$rec = $aseco->plugins['PluginLocalRecords']->records->getRecord($i);
				if ($rec->player->login == $player->login) {
					$rank = $i;
					$found = true;
					break;
				}
			}

			if ($found) {
				// get current and next better ranked records
				$nextrank = ($rank > 0 ? $rank-1 : 0);
				$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($rank);
				$next = $aseco->plugins['PluginLocalRecords']->records->getRecord($nextrank);

				// compute difference to next record
				if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
					$diff = $record->score - $next->score;
					$sec = floor($diff / 1000);
					$ths = $diff - ($sec * 1000);
				}
				else {
					// Stunts mode
					$diff = $next->score - $record->score;
				}

				// show chat message
				$message1 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
					$rank + 1,
					$aseco->stripColors($record->player->nickname),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $record->score : $aseco->formatTime($record->score))
				);
				$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "

				$message2 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
					$nextrank + 1,
					$aseco->stripColors($next->player->nickname),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $record->score : $aseco->formatTime($next->score))
				);
				$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "

				$message = $aseco->formatText($aseco->getChatMessage('DIFF_RECORD'),
					$message1,
					$message2,
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $diff : sprintf("%d.%03d", $sec, $ths))
				);

				$aseco->sendChatMessage($message, $player->login);
			}
			else {
				// look for unranked time instead
				$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
				$query = "
				SELECT
					`score`
				FROM `%prefix%times`
				WHERE `PlayerId` = ". $player->id ."
				AND `MapId` = ". $aseco->server->maps->current->id ."
				ORDER BY `Score` ". $order ."
				LIMIT 1;
				";

				$result = $aseco->db->query($query);
				if ($result) {
					if ($result->num_rows > 0) {
						$unranked = $result->fetch_object();
						$found = true;
					}
					$result->free_result();
				}

				if ($found) {
					// get the last ranked record
					$last = $aseco->plugins['PluginLocalRecords']->records->getRecord($total-1);

					// compute difference to next record
					if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
						$diff = $unranked->Score - $last->score;
						$sec = floor($diff/1000);
						$ths = $diff - ($sec * 1000);
					}
					else {
						// Stunts mode
						$diff = $last->score - $unranked->Score;
					}

					// show chat message
					$message1 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
						'PB',
						$aseco->stripColors($command['author']->nickname),
						($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $unranked->Score : $aseco->formatTime($unranked->Score))
					);
					$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "

					$message2 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
						$total,
						$aseco->stripColors($last->player->nickname),
						($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $last->score : $aseco->formatTime($last->score))
					);
					$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "

					$message = $aseco->formatText($aseco->getChatMessage('DIFF_RECORD'),
						$message1,
						$message2,
						($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $diff : sprintf("%d.%03d", $sec, $ths))
					);

					$aseco->sendChatMessage($message, $player->login);
				}
				else {
					$message = '{#server}» {#error}You don\'t have a record on this map yet... use {#highlite}$i/lastrec';
					$aseco->sendChatMessage($message, $player->login);
				}
			}
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No records found!', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_diffrec ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			$found = false;
			// find ranked record
			for ($i = 0; $i < $total; $i++) {
				$rec = $aseco->plugins['PluginLocalRecords']->records->getRecord($i);
				if ($rec->player->login == $login) {
					$rank = $i;
					$found = true;
					break;
				}
			}

			if ($found) {
				// get current and first ranked records
				$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($rank);
				$first = $aseco->plugins['PluginLocalRecords']->records->getRecord(0);

				// compute difference to first record
				if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
					$diff = $record->score - $first->score;
					$sec = floor($diff/1000);
					$ths = $diff - ($sec * 1000);
				}
				else {
					// Stunts mode
					$diff = $first->score - $record->score;
				}

				// show chat message
				$message1 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
					$rank + 1,
					$aseco->stripColors($record->player->nickname),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $record->score : $aseco->formatTime($record->score))
				);
				$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "

				$message2 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
					1,
					$aseco->stripColors($first->player->nickname),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $first->score : $aseco->formatTime($first->score))
				);
				$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "

				$message = $aseco->formatText($aseco->getChatMessage('DIFF_RECORD'),
					$message1,
					$message2,
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $diff : sprintf("%d.%03d", $sec, $ths))
				);

				$aseco->sendChatMessage($message, $login);
			}
			else {
				$message = '{#server}» {#error}You don\'t have a record on this map yet... use {#highlite}$i/lastrec';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_recrange ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			// get the first & last ranked records
			$first = $aseco->plugins['PluginLocalRecords']->records->getRecord(0);
			$last = $aseco->plugins['PluginLocalRecords']->records->getRecord($total-1);

			// compute difference between records
			if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
				$diff = $last->score - $first->score;
				$sec = floor($diff/1000);
				$ths = $diff - ($sec * 1000);
			}
			else {
				// Stunts mode
				$diff = $first->score - $last->score;
			}

			// show chat message
			$message1 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				1,
				$aseco->stripColors($first->player->nickname),
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $first->score : $aseco->formatTime($first->score))
			);
			$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "

			$message2 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				$total,
				$aseco->stripColors($last->player->nickname),
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $last->score : $aseco->formatTime($last->score))
			);
			$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "

			$message = $aseco->formatText($aseco->getChatMessage('DIFF_RECORD'),
				$message1,
				$message2,
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $diff : sprintf("%d.%03d", $sec, $ths))
			);

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No records found!', $login);
		}
	}
}

?>
