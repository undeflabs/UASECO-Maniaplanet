<?php
/*
 * Plugin: Record Relations
 * ~~~~~~~~~~~~~~~~~~~~~~~~
 * » Shows ranked records and their relations on the current map.
 * » Based upon chat.recrels.php from XAseco2/1.03 written by Xymph
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

		$this->registerChatCommand('firstrec',	'chat_firstrec',	new Message('chat.record_relations', 'firstrec'),	Player::PLAYERS);
		$this->registerChatCommand('lastrec',	'chat_lastrec',		new Message('chat.record_relations', 'lastrec'),	Player::PLAYERS);
		$this->registerChatCommand('nextrec',	'chat_nextrec',		new Message('chat.record_relations', 'nextrec'),	Player::PLAYERS);
		$this->registerChatCommand('diffrec',	'chat_diffrec',		new Message('chat.record_relations', 'diffrec'),	Player::PLAYERS);
		$this->registerChatCommand('recrange',	'chat_recrange',	new Message('chat.record_relations', 'recrange'),	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_firstrec ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$msg = new Message('common', 'notonrelay');
			$msg->sendChatMessage($login);
			return;
		}

		if ($aseco->plugins['PluginLocalRecords']->records->count() > 0) {
			// get the first ranked record
			$record = $aseco->plugins['PluginLocalRecords']->records->getRecord(0);

			// show chat message
			$message = (new Message('common', 'first_record'))->finish($login);

			$msg = new Message('common', 'ranking_record_new');
			$msg->addPlaceholders(1, $aseco->stripColors($record->player->nickname),	$aseco->formatTime($record->score));
			$message.= $msg->finish($login);

			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$msg = new Message('chat.rasp_nextrank', 'no_records_found');
			$msg->sendChatMessage($login);
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
			$msg = new Message('common', 'notonrelay');
			$msg->sendChatMessage($login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			// get the last ranked record
			$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($total-1);

			// show chat message
			$message = (new Message('common', 'last_record'))->finish($login);

			$msg = new Message('common', 'ranking_record_new');
			$msg->addPlaceholders($total, $aseco->stripColors($record->player->nickname),	$aseco->formatTime($record->score));
			$message.= $msg->finish($login);

			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$msg = new Message('chat.rasp_nextrank', 'no_records_found');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nextrec ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$msg = new Message('common', 'notonrelay');
			$msg->sendChatMessage($login);
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
				$diff = $record->score - $next->score;
				$sec = floor($diff / 1000);
				$ths = $diff - ($sec * 1000);

				// show chat message
				$msg = new Message('common', 'ranking_record_new');
				$msg->addPlaceholders($rank + 1, $aseco->stripColors($record->player->nickname), $aseco->formatTime($record->score));
				$message1.= $msg->finish($login);
				substr($message1, 0, strlen($message1)-2);

				$msg = new Message('common', 'ranking_record_new');
				$msg->addPlaceholders($nextrank + 1, $aseco->stripColors($record->player->nickname), $aseco->formatTime($record->score));
				$message2.= $msg->finish($login);
				substr($message2, 0, strlen($message1)-2);

				$msg = new Message('common', 'diff_record');
				$msg->addPlaceholders($message1, $message2,	sprintf("%d.%03d", $sec, $ths));
				$msg->sendChatMessage($login);
			}
			else {
				// look for unranked time instead
				$query = "
				SELECT
					`score`
				FROM `%prefix%times`
				WHERE `PlayerId` = ". $player->id ."
				AND `MapId` = ". $aseco->server->maps->current->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				ORDER BY `Score` ASC
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
					$diff = $unranked->Score - $last->score;
					$sec = floor($diff/1000);
					$ths = $diff - ($sec * 1000);

					// show chat message
					$msg = new Message('common', 'ranking_record_new');
					$msg->addPlaceholders('PB', $aseco->stripColors($command['author']->nickname), $aseco->formatTime($unranked->Score));
					$message1.= $msg->finish($login);
					substr($message1, 0, strlen($message1)-2);

					$msg = new Message('common', 'ranking_record_new');
					$msg->addPlaceholders($total, $aseco->stripColors($last->player->nickname),	$aseco->formatTime($last->score));
					$message2.= $msg->finish($login);
					substr($message2, 0, strlen($message1)-2);

					$msg = new Message('common', 'diff_record');
					$msg->addPlaceholders($message1, $message2,	sprintf("%d.%03d", $sec, $ths));
					$msg->sendChatMessage($login);
				}
				else {
					$msg = new Message('chat.rasp_nextrank', 'no_records_found');
					$msg->sendChatMessage($login);
				}
			}
		}
		else {
			$msg = new Message('chat.rasp_nextrank', 'no_records_found');
			$msg->sendChatMessage($login);
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
			$msg = new Message('common', 'notonrelay');
			$msg->sendChatMessage($login);
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
				$diff = $record->score - $first->score;
				$sec = floor($diff/1000);
				$ths = $diff - ($sec * 1000);

				// show chat message
				$msg = new Message('common', 'ranking_record_new');
				$msg->addPlaceholders($rank + 1, $aseco->stripColors($record->player->nickname), $aseco->formatTime($record->score));
				$message1.= $msg->finish($login);
				substr($message1, 0, strlen($message1)-2);

				$msg = new Message('common', 'ranking_record_new');
				$msg->addPlaceholders(1, $aseco->stripColors($first->player->nickname), $aseco->formatTime($first->score));
				$message2.= $msg->finish($login);
				substr($message2, 0, strlen($message1)-2);

				$msg = new Message('common', 'diff_record');
				$msg->addPlaceholders($message1, $message2,	sprintf("%d.%03d", $sec, $ths));
				$msg->sendChatMessage($login);
			}
			else {
				$msg = new Message('chat.rasp_nextrank', 'no_records_found');
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('chat.rasp_nextrank', 'no_records_found');
			$msg->sendChatMessage($login);
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
			$msg = new Message('common', 'notonrelay');
			$msg->sendChatMessage($login);
			return;
		}

		if ($total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			// get the first & last ranked records
			$first = $aseco->plugins['PluginLocalRecords']->records->getRecord(0);
			$last = $aseco->plugins['PluginLocalRecords']->records->getRecord($total-1);

			// compute difference between records
			$diff = $last->score - $first->score;
			$sec = floor($diff/1000);
			$ths = $diff - ($sec * 1000);

			// show chat message
			$message1 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				1,
				$aseco->stripColors($first->player->nickname),
				$aseco->formatTime($first->score)
			);
			$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "

			$message2 = $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
				$total,
				$aseco->stripColors($last->player->nickname),
				$aseco->formatTime($last->score)
			);
			$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "

			$message = $aseco->formatText($aseco->getChatMessage('DIFF_RECORD'),
				$message1,
				$message2,
				sprintf("%d.%03d", $sec, $ths)
			);

			$aseco->sendChatMessage($message, $login);


			$msg = new Message('common', 'ranking_record_new');
			$msg->addPlaceholders(1, $aseco->stripColors($first->player->nickname), $aseco->formatTime($first->score));
			$message1.= $msg->finish($login);
			substr($message1, 0, strlen($message1)-2);

			$msg = new Message('common', 'ranking_record_new');
			$msg->addPlaceholders($total, $aseco->stripColors($last->player->nickname), $aseco->formatTime($last->score));
			$message2.= $msg->finish($login);
			substr($message2, 0, strlen($message1)-2);

			$msg = new Message('common', 'diff_record');
			$msg->addPlaceholders($message1, $message2,	sprintf("%d.%03d", $sec, $ths));
			$msg->sendChatMessage($login);
		}
		else {
			$msg = new Message('chat.rasp_nextrank', 'no_records_found');
			$msg->sendChatMessage($login);
		}
	}
}

?>
