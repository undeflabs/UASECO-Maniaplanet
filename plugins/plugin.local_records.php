<?php
/*
 * Plugin: Local Records
 * ~~~~~~~~~~~~~~~~~~~~~
 * » Saves record into a local database.
 * » Based upon plugin.localdatabase.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-02
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
 *  - includes/core/record.class.php
 *  - includes/core/recordlist.class.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginLocalRecords();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginLocalRecords extends Plugin {
	public $settings;
	public $records;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Saves record into a local database.');

		$this->registerEvent('onStartup',		'onStartup');
		$this->registerEvent('onBeginMap',		'onBeginMap');
		$this->registerEvent('onBeginMap1',		'onBeginMap1');
		$this->registerEvent('onEndMapRanking',		'onEndMapRanking');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onPlayerFinish',		'onPlayerFinish');
		$this->registerEvent('onPlayerWins',		'onPlayerWins');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onStartup ($aseco) {

		$aseco->console('[LocalRecords] Load config file [config/local_records.xml]');
		if (!$settings = $aseco->parser->xmlToArray('config/local_records.xml', true, true)) {
			trigger_error('[LocalRecords] Could not read/parse config file [config/local_records.xml]!', E_USER_ERROR);
		}
		$settings = $settings['SETTINGS'];

		// Store messages
		$this->settings['messages'] = $settings['MESSAGES'][0];

		// Display records in game?
		$this->settings['display'] = $aseco->string2bool($settings['DISPLAY'][0]);

		// Show records in message window?
		$this->settings['recs_in_window'] = $aseco->string2bool($settings['RECS_IN_WINDOW'][0]);

		// Set highest record still to be displayed
		$this->settings['max_records'] = (int)$settings['MAX_RECORDS'][0];

		// Set highest record still to be displayed
		$this->settings['limit'] = (int)$settings['LIMIT'][0];

		// Set minimum number of records to be displayed
		$this->settings['show_min_recs'] = $settings['SHOW_MIN_RECS'][0];

		// Show records before start of map?
		$this->settings['show_recs_before'] = $settings['SHOW_RECS_BEFORE'][0];

		// Show records after end of map?
		$this->settings['show_recs_after'] = $settings['SHOW_RECS_AFTER'][0];

		// Show records range?
		$this->settings['show_recs_range'] = $aseco->string2bool($settings['SHOW_RECS_RANGE'][0]);

		// Check database
		$this->checkDatabaseStructure($aseco);

		// Initiate records list
		$this->records = new RecordList($this->settings['max_records']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Show top-8 & records of all online players before map
		if (($this->settings['show_recs_before'] & 2) == 2) {
			$this->show_maprecs($aseco, $player->login, 1, 0);
		}
		else if (($this->settings['show_recs_before'] & 1) == 1) {
			// Or show original record message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $player->login);
		}

		// If there's a record on current map
		$cur_record = $this->records->getRecord(0);
		if ($cur_record !== false && $cur_record->score > 0) {
			// set message to the current record
			$message = $aseco->formatText($this->settings['messages']['RECORD_CURRENT'][0],
				$aseco->stripColors($aseco->server->maps->current->name),
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score)),
				$aseco->stripColors($cur_record->player->nickname)
			);
		}
		else {
			// If there should be no record to display
			// display a no-record message
			$message = $aseco->formatText($this->settings['messages']['RECORD_NONE'][0],
				$aseco->stripColors($aseco->server->maps->current->name)
			);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		// Ignore fluke disconnects with empty logins
		if ($player->login == '') {
			return;
		}

		// Update player
		$query = "
		UPDATE `players` SET
			`UpdatedAt` = NOW(),
			`TimePlayed` = `TimePlayed` + ". $player->getTimeOnline() ."
		WHERE `Login` = ". $aseco->mysqli->quote($player->login) .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not update disconnecting player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// Reset record list
		$this->records->clear();

		// on relay, ignore master server's map
		if ($aseco->server->isrelay) {
			return;
		}

		// Load all current local records for current Map
		$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
		$query = "
		SELECT
			`m`.`Id`,
			`r`.`Score`,
			`p`.`NickName`,
			`p`.`Login`,
			`r`.`Date`,
			`r`.`Checkpoints`
		FROM `maps` AS `m`
		LEFT JOIN `records` AS `r` ON `r`.`MapId` = `m`.`Id`
		LEFT JOIN `players` AS `p` ON `r`.`PlayerId` = `p`.`Id`
		WHERE `m`.`Uid` = ". $aseco->mysqli->quote($map->uid) ."
		GROUP BY `r`.`Id`
		ORDER BY `r`.`Score` ". $order .", `r`.`Date` ASC
		LIMIT ". ($this->records->getMaxRecords() ? $this->records->getMaxRecords() : 50) .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			// map found?
			if ($result->num_rows > 0) {
				// Get each record
				while ($record = $result->fetch_array()) {

					// create record object
					$record_item = new Record();
					$record_item->score = $record['Score'];
					$record_item->checkpoints = ($record['Checkpoints'] != '' ? explode(',', $record['Checkpoints']) : array());
					$record_item->new = false;

					// create a player object to put it into the record object
					$player_item = new Player();
					$player_item->nickname = $record['NickName'];
					$player_item->login = $record['Login'];
					$record_item->player = $player_item;

					// add the map information to the record object
					$record_item->map = clone $map;
					unset($record_item->map->mx);	// reduce memory usage

					// add the created record to the list
					$this->records->addRecord($record_item);
				}
				$aseco->releaseEvent('onLocalRecordsLoaded', $this->records);
				// log records when debugging is set to true
				//if ($aseco->debug) $aseco->console('onBeginMap records:' . CRLF . print_r($this->records, true));
			}
			$result->free_result();
		}
		else {
			trigger_error('[LocalRecords] Could not get map info! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}


		// Check for relay server
		if (!$aseco->server->isrelay) {
			// Check if record exists on new map
			$cur_record = $this->records->getRecord(0);
			if ($cur_record !== false && $cur_record->score > 0) {
				$score = $cur_record->score;

				// Log console message of current record
				$aseco->console('[LocalRecords] Current record on [{1}] is {2} and held by [{3}]',
					$aseco->stripColors($map->name, false),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score)),
					$aseco->stripColors($cur_record->player->login, false)
				);

				// Replace parameters
				$message = $aseco->formatText($this->settings['messages']['RECORD_CURRENT'][0],
					$aseco->stripColors($map->name),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score)),
					$aseco->stripColors($cur_record->player->nickname)
				);
			}
			else {
				$score = 0;

				// Log console message of no record
				$aseco->console('[LocalRecords] Currently no record on [{1}]',
					$aseco->stripColors($map->name, false)
				);

				// Replace parameters
				$message = $aseco->formatText($this->settings['messages']['RECORD_NONE'][0],
					$aseco->stripColors($map->name)
				);
			}
			$aseco->releaseEvent('onLocalRecordBestLoaded', $score);


			// If no maprecs, show the original record message to all players
			if (($this->settings['show_recs_before'] & 1) == 1) {
				if (($this->settings['show_recs_before'] & 4) == 4) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {

		// Show top-8 & records of all online players before map
		if (($this->settings['show_recs_before'] & 2) == 2) {
			$this->show_maprecs($aseco, false, 1, $this->settings['show_recs_before']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMapRanking ($aseco, $map) {

		// Show top-8 & all new records after map
		if (($this->settings['show_recs_after'] & 2) == 2) {
			$this->show_maprecs($aseco, false, 3, $this->settings['show_recs_after']);
		}
		else if (($this->settings['show_recs_after'] & 1) == 1) {
			// fall back on old top-5
			$records = '';

			if ($this->records->count() == 0) {
				// display a no-new-record message
				$message = $aseco->formatText($this->settings['messages']['RANKING_NONE'][0],
					$aseco->stripColors($aseco->server->maps->current->name),
					'after'
				);
			}
			else {
				// Display new records set up this round
				$message = $aseco->formatText($this->settings['messages']['RANKING'][0],
					$aseco->stripColors($aseco->server->maps->current->name),
					'after'
				);

				// Go through each record
				for ($i = 0; $i < 5; $i++) {
					$cur_record = $this->records->getRecord($i);

					// If the record is set then display it
					if ($cur_record !== false && $cur_record->score > 0) {
						// replace parameters
						$record_msg = $aseco->formatText($this->settings['messages']['RANKING_RECORD_NEW'][0],
							$i+1,
							$aseco->stripColors($cur_record->player->nickname),
							($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score))
						);
						$records .= $record_msg;
					}
				}
			}

			// Append the records if any
			if ($records != '') {
				$records = substr($records, 0, strlen($records)-2);  // strip trailing ", "
				$message .= LF . $records;
			}

			// Show ranking message to all players
			if (($this->settings['show_recs_after'] & 4) == 4) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, true));
			}
			else {
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// If no actual finish, bail out immediately
		if ($finish_item->score == 0) {
			return;
		}

		// In Laps mode on real PlayerFinish event, bail out too
		if ($aseco->server->gameinfo->mode == Gameinfo::LAPS && !$finish_item->new) {
			return;
		}

		$login = $finish_item->player->login;
		$nickname = $aseco->stripColors($finish_item->player->nickname);

		// reset lap 'Finish' flag & add checkpoints
		$finish_item->new = false;

		// drove a new record?
		// go through each of the XX records
		for ($i = 0; $i < $this->records->getMaxRecords(); $i++) {
			$cur_record = $this->records->getRecord($i);

			// if player's time/score is better, or record isn't set (thanks eyez)
			if ($cur_record === false || ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $finish_item->score > $cur_record->score : $finish_item->score < $cur_record->score)) {

				// does player have a record already?
				$cur_rank = -1;
				$cur_score = 0;
				for ($rank = 0; $rank < $this->records->count(); $rank++) {
					$rec = $this->records->getRecord($rank);

					if ($rec->player->login == $login) {

						// new record worse than old one
						if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $finish_item->score < $rec->score : $finish_item->score > $rec->score) {
							return;
						}
						else {
							// new record is better than or equal to old one
							$cur_rank = $rank;
							$cur_score = $rec->score;
							break;
						}
					}
				}

				$finish_time = $finish_item->score;
				if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
					$finish_time = $aseco->formatTime($finish_time);
				}

				if ($cur_rank != -1) {  // player has a record in topXX already

					// compute difference to old record
					if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
						$diff = $cur_score - $finish_item->score;
						$sec = floor($diff/1000);
						$ths = $diff - ($sec * 1000);
					}
					else {  // Stunts
						$diff = $finish_item->score - $cur_score;
					}

					// update record if improved
					if ($diff > 0) {
						$finish_item->new = true;
						$this->records->setRecord($cur_rank, $finish_item);
					}

					// player moved up in LR list
					if ($cur_rank > $i) {

						// move record to the new position
						$this->records->moveRecord($cur_rank, $i);

						// do a player improved his/her LR rank message
						$message = $aseco->formatText($this->settings['messages']['RECORD_NEW_RANK'][0],
							$nickname,
							$i + 1,
							($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'Score' : 'Time'),
							$finish_time,
							$cur_rank + 1,
							($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? '+'. $diff : sprintf('-%d.%03d', $sec, $ths))
						);

						// show chat message to all or player
						if ($this->settings['display']) {
							if ($i < $this->settings['limit']) {
								if ($this->settings['recs_in_window']) {
									$aseco->releaseEvent('onSendWindowMessage', array($message, false));
								}
								else {
									$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
								}
							}
							else {
								$message = str_replace('{#server}» ', '{#server}» ', $message);
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}
						}

					}
					else {

						if ($diff == 0) {
							// do a player equaled his/her record message
							$message = $aseco->formatText($this->settings['messages']['RECORD_EQUAL'][0],
								$nickname,
								$cur_rank + 1,
								($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'Score' : 'Time'),
								$finish_time
							);
						}
						else {
							// do a player secured his/her record message
							$message = $aseco->formatText($this->settings['messages']['RECORD_NEW'][0],
								$nickname,
								$i + 1,
								($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'Score' : 'Time'),
								$finish_time,
								$cur_rank + 1,
								($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? '+'. $diff : sprintf('-%d.%03d', $sec, $ths))
							);
						}

						// show chat message to all or player
						if ($this->settings['display']) {
							if ($i < $this->settings['limit']) {
								if ($this->settings['recs_in_window']) {
									$aseco->releaseEvent('onSendWindowMessage', array($message, false));
								}
								else {
									$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
								}
							}
							else {
								$message = str_replace('{#server}» ', '{#server}» ', $message);
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}
						}
					}
				}
				else {  // player hasn't got a record yet

					// insert new record at the specified position
					$finish_item->new = true;
					$this->records->addRecord($finish_item, $i);

					// do a player drove first record message
					$message = $aseco->formatText($this->settings['messages']['RECORD_FIRST'][0],
						$nickname,
						$i + 1,
						($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'Score' : 'Time'),
						$finish_time
					);

					// show chat message to all or player
					if ($this->settings['display']) {
						if ($i < $this->settings['limit']) {
							if ($this->settings['recs_in_window']) {
								$aseco->releaseEvent('onSendWindowMessage', array($message, false));
							}
							else {
								$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
							}
						}
						else {
							$message = str_replace('{#server}» ', '{#server}» ', $message);
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						}
					}
				}

				// log records when debugging is set to true
				//if ($aseco->debug) $aseco->console('onPlayerFinish records:' . CRLF . print_r($this->records, true));

				// insert and log a new local record (not an equalled one)
				if ($finish_item->new) {
					$this->insertRecord($finish_item);

					// Log record message in console
					$aseco->console('[LocalRecords] Player [{1}] finished with {2} and took the {3}. LR place!',
						$login,
						(($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? $finish_item->score : $aseco->formatTime($finish_item->score)),
						$i+1
					);

					// Throw 'local record' event
					$finish_item->position = $i + 1;
					$aseco->releaseEvent('onLocalRecord', $finish_item);
				}

				// Got the record, now stop!
				return;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerWins ($aseco, $player) {

		$query = "
		UPDATE `players` SET
			`Wins` = ". $player->getWins() ."
		WHERE `Login` = ". $aseco->mysqli->quote($player->login) .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not update winning player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function insertRecord ($record) {
		global $aseco;

		$cps = implode(',', $record->checkpoints);

		// Insert new record or update existing
		$query = "
		INSERT INTO `records` (
			`MapId`,
			`PlayerId`,
			`Score`,
			`Date`,
			`Checkpoints`
		)
		VALUES (
			". $aseco->server->maps->current->id .",
			". $record->player->id .",
			". $record->score .",
			NOW(),
			". $aseco->mysqli->quote($cps) ."
		)
		ON DUPLICATE KEY UPDATE
			`Score` = VALUES(`Score`),
			`Date` = VALUES(`Date`),
			`Checkpoints` = VALUES(`Checkpoints`);
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not insert/update record! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removeRecord ($aseco, $cid, $pid, $recno) {

		// remove record
		$query = "
		DELETE FROM `records`
		WHERE `MapId` = ". $cid ."
		AND `PlayerId` = ". $pid .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not remove record! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}

		// remove record from specified position
		$this->records->deleteRecord($recno);

		// check if fill up is needed
		if ($this->records->count() == ($this->records->getMaxRecords() - 1)) {
			// get max'th time
			$query = "
			SELECT DISTINCT
				`PlayerId`,
				`Score`
			FROM `rs_times` AS `t1`
			WHERE `MapId` = ". $cid ."
			AND `Score` = (
				SELECT
					MIN(`t2`.`Score`)
				FROM `rs_times` AS `t2`
				WHERE `MapId` = ". $cid ."
				AND `t1`.`PlayerId` = `t2`.`PlayerId`
			)
			ORDER BY `Score`, `Date`
			LIMIT ". ($this->records->getMaxRecords() - 1) .",1;
			";

			$result = $aseco->mysqli->query($query);
			if ($result) {
	 			if ($result->num_rows == 1) {
					$timerow = $result->fetch_object();

					// get corresponding date/time & checkpoints
					$query2 = "
					SELECT
						`Date`,
						`Checkpoints`
					FROM `rs_times`
					WHERE `MapId` = ". $cid ."
					AND `PlayerId` = ". $timerow->PlayerId ."
					ORDER BY `Score`, `Date`
					LIMIT 1;
					";

					$result2 = $aseco->mysqli->query($query2);
					$timerow2 = $result2->fetch_object();
					$datetime = date('Y-m-d H:i:s', $timerow2->Date);
					$result2->free_result();

					// insert/update new max'th record
					$query2 = "
					INSERT INTO `records` (
						`MapId`,
						`PlayerId`,
						`Score`,
						`Date`,
						`Checkpoints`
					)
					VALUES (
						". $cid . ",
						". $timerow->PlayerId .",
						". $timerow->Score .",
						". $aseco->mysqli->quote($datetime) .",
						". $aseco->mysqli->quote($timerow2->Checkpoints) ."
					)
					ON DUPLICATE KEY UPDATE
						`Score` = VALUES(`Score`),
						`Date` = VALUES(`Date`),
						`Checkpoints` = VALUES(`Checkpoints`);
					";

					$result2 = $aseco->mysqli->query($query2);
					if (!$result2) {
						trigger_error('[LocalRecords] Could not insert/update record! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					}

					// get player info
					$query2 = "
					SELECT
						*
					FROM `players`
					WHERE `Id` = ". $timerow->PlayerId .";
					";
					$result2 = $aseco->mysqli->query($query2);
					$playrow = $result2->fetch_array();
					$result2->free_result();

					// create record object
					$record_item = new Record();
					$record_item->score = $timerow->Score;
					$record_item->checkpoints = ($timerow2->Checkpoints != '' ? explode(',', $timerow2->Checkpoints) : array());
					$record_item->new = false;

					// create a player object to put it into the record object
					$player_item = new Player();
					$player_item->nickname = $playrow['NickName'];
					$player_item->login = $playrow['Login'];
					$record_item->player = $player_item;

					// add the map information to the record object
					$record_item->map = clone $aseco->server->maps->current;
					unset($record_item->map->mx);

					// add the created record to the list
					$this->records->addRecord($record_item);
				}
			}
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPersonalBest ($login, $mapid) {
		global $aseco;

		$pb = array();

		// Find ranked record
		$found = false;
		for ($i = 0; $i < $this->records->getMaxRecords(); $i++) {
			if (($rec = $this->records->getRecord($i)) !== false) {
				if ($rec->player->login == $login) {
					$pb['time'] = $rec->score;
					$pb['rank'] = $i + 1;
					$found = true;
					break;
				}
			}
			else {
				break;
			}
		}

		if (!$found) {

			// find unranked time/score
			$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
			$query = "
			SELECT
				`Score`
			FROM `rs_times`
			WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) ."
			AND `MapId` = ". $mapid ."
			ORDER BY `Score` ". $order ."
			LIMIT 1;
			";

			$res = $aseco->mysqli->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$row = $res->fetch_object();
					$pb['time'] = $row->Score;
					$pb['rank'] = '$nUNRANKED$m';
				}
				else {
					$pb['time'] = 0;
					$pb['rank'] = '$nNONE$m';
				}
				$res->free_result();
			}
		}
		return $pb;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/*
	 * Universal function to generate list of records for current map.
	 * Called by chat_newrecs, chat_liverecs, endMap & beginMap (uaseco.php).
	 * Show to a player if $login defined, otherwise show to all players.
	 * $mode = 0 (only new), 1 (top-8 & online players at start of map),
	 *         2 (top-6 & online during map), 3 (top-8 & new at end of map)
	 * In modes 1/2/3 the last ranked record is also shown
	 * top-8 is configurable via 'show_min_recs'; top-6 is show_min_recs-2
	 */
	public function show_maprecs ($aseco, $login, $mode, $window) {

		$records = '$n';  // use narrow font

		// check for records
		if (($total = $this->records->count()) == 0) {
			$totalnew = -1;
		}
		else {
			// check whether to show range
			if ($this->settings['show_recs_range']) {
				// get the first & last ranked records
				$first	= $this->records->getRecord(0);
				$last	= $this->records->getRecord($total-1);

				// compute difference between records
				if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
					$diff = $last->score - $first->score;
					$sec = floor($diff/1000);
					$ths = $diff - ($sec * 1000);
				}
				else {
					// Stunts
					$diff = $first->score - $last->score;
				}
			}

			// get list of online players
			$players = array();
			foreach ($aseco->server->players->player_list as $pl) {
				$players[] = $pl->login;
			}

			// collect new records and records by online players
			$totalnew = 0;

			// go through each record
			for ($i = 0; $i < $total; $i++) {
				$cur_record = $this->records->getRecord($i);

				// if the record is new then display it
				if ($cur_record->new) {
					$totalnew++;
					$record_msg = $aseco->formatText($this->settings['messages']['RANKING_RECORD_NEW_ON'][0],
						$i + 1,
						$aseco->stripColors($cur_record->player->nickname),
						($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score))
					);

					// always show new record
					$records .= $record_msg;
				}
				else {
					// check if player is online
					if (in_array($cur_record->player->login, $players)) {
						$record_msg = $aseco->formatText($this->settings['messages']['RANKING_RECORD_ON'][0],
							$i + 1,
							$aseco->stripColors($cur_record->player->nickname),
							($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score))
						);

						if ($mode != 0 && $i == $total-1) {
							// check if last ranked record
							$records .= $record_msg;
						}
						else if ($mode == 1 || $mode == 2) {
							// check if always show (start of/during map)
							$records .= $record_msg;
						}
						else {
							// show record if < show_min_recs (end of map)
							if ($mode == 3 && $i < $this->settings['show_min_recs']) {
								$records .= $record_msg;
							}
						}
					}
					else {
						$record_msg = $aseco->formatText($this->settings['messages']['RANKING_RECORD'][0],
							$i + 1,
							$aseco->stripColors($cur_record->player->nickname),
							($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $cur_record->score : $aseco->formatTime($cur_record->score))
						);

						if ($mode != 0 && $i == $total-1) {
							// check if last ranked record
							$records .= $record_msg;
						}
						else if (($mode == 2 && $i < $this->settings['show_min_recs']-2) || (($mode == 1 || $mode == 3) && $i < $this->settings['show_min_recs'])) {
							// show offline record if < show_min_recs-2 (during map)
							// show offline record if < show_min_recs (start/end of map)
							$records .= $record_msg;
						}
					}
				}
			}
		}

		// define wording of the ranking message
		switch ($mode) {
			case 0:
				$timing = 'during';
				break;
			case 1:
				$timing = 'before';
				break;
			case 2:
				$timing = 'during';
				break;
			case 3:
				$timing = 'after';
				break;
		}

		$name = $aseco->stripColors($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error == '') {
			$name = '$l[http://' . $aseco->server->maps->current->mx->prefix .
			        '.mania-exchange.com/tracks/view/'.
			        $aseco->server->maps->current->mx->id .']'. $name .'$l';
		}

		// define the ranking message
		if ($totalnew > 0) {
			$message = $aseco->formatText($this->settings['messages']['RANKING_NEW'][0],
				$name,
				$timing,
				$totalnew
			);
		}
		else if ($totalnew == 0 && $records != '$n') {
			// check whether to show range
			if ($this->settings['show_recs_range']) {
				$message = $aseco->formatText($this->settings['messages']['RANKING_RANGE'][0],
					$name,
					$timing,
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $diff : sprintf("%d.%03d", $sec, $ths))
				);
			}
			else {
				$message = $aseco->formatText($this->settings['messages']['RANKING'][0],
					$name,
					$timing
				);
			}
		}
		else if ($totalnew == 0 && $records == '$n') {
			$message = $aseco->formatText($this->settings['messages']['RANKING_NO_NEW'][0],
				$name,
				$timing
			);
		}
		else {
			// $totalnew == -1
			$message = $aseco->formatText($this->settings['messages']['RANKING_NONE'][0],
				$name,
				$timing
				);
		}

		// append the records if any
		if ($records != '$n') {
			$records = substr($records, 0, strlen($records)-2);  // strip trailing ", "
			$message .= LF . $records;
		}

		// show to player or all
		if ($login) {
			// strip 1 leading '>' to indicate a player message instead of system-wide
			$message = str_replace('{#server}» ', '{#server}» ', $message);
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else {
			if (($window & 4) == 4) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, ($mode == 3)));
			}
			else {
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkDatabaseStructure ($aseco) {

		// Create main tables
		$aseco->console('[LocalRecords] Checking database structure:');
		$aseco->console('[LocalRecords] » Checking table `records`.');
		$query = "
		CREATE TABLE IF NOT EXISTS `records` (
			`Id` int(11) NOT NULL AUTO_INCREMENT,
			`MapId` mediumint(9) NOT NULL DEFAULT '0',
			`PlayerId` mediumint(9) NOT NULL DEFAULT '0',
			`Score` int(11) NOT NULL DEFAULT '0',
			`Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Checkpoints` text NOT NULL,
			PRIMARY KEY (`Id`),
			UNIQUE KEY `PlayerId` (`PlayerId`,`MapId`),
			KEY `MapId` (`MapId`),
			KEY `Score` (`Score`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;
		";
		$aseco->mysqli->query($query);


		// Check for main tables
		$tables = array();
		$res = $aseco->mysqli->query('SHOW TABLES;');
		if ($res) {
			while ($row = $res->fetch_row()) {
				$tables[] = $row[0];
			}
			$res->free_result();
		}

		$check = array();
		$check[1] = in_array('records', $tables);
		if ( !$check[1] ) {
			trigger_error('[LocalRecords] ERROR: Table structure incorrect, use [newinstall/database/uaseco.sql] to correct this!', E_USER_ERROR);
		}

		$aseco->console('[LocalRecords] ...successfully done!');
	}
}

?>
