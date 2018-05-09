<?php
/*
 * Class: Player List
 * ~~~~~~~~~~~~~~~~~~
 * » Manages Players on the server, add/remove Players and provides several get functions.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
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



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PlayerList extends BaseClass {
	public $player_list;

	private $debug = false;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($debug) {
		$this->debug = $debug;

		$this->setAuthor('undef.de');
		$this->setContributors(array('brakerb'));
		$this->setVersion('1.0.1');
		$this->setBuild('2018-05-09');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Manages Players on the server, add/remove Players and provides several get functions.');

		$this->player_list = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function count () {
		return count($this->player_list);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getSpectatorCount () {
		$count = 0;
		foreach ($this->player_list as $player) {
			if ($player->is_spectator === true) {
				$count += 1;
			}
		}
		return $count;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addPlayer ($player) {
		if (isset($player) && is_object($player) && $player instanceof Player && $player->login !== '') {

			// Check for existing Player, otherwise insert into Database
			$this->checkDatabase($player);

			// Add to Playerlist
			$this->player_list[$player->login] = $player;

			return true;
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removePlayer ($login) {
		if (array_key_exists($login, $this->player_list)) {
			unset($this->player_list[$login]);
			return true;
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerByLogin ($login) {
		if (array_key_exists($login, $this->player_list)) {
			return $this->player_list[$login];
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerById ($id) {
		if (!empty($id)) {
			foreach ($this->player_list as $player) {
				if ($player->id === (int)$id) {
					return $player;
				}
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerByPid ($pid) {
		if (!empty($pid)) {
			foreach ($this->player_list as $player) {
				if ($player->pid === $pid) {
					return $player;
				}
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Finds a player ID from its login.
	public function getPlayerIdByLogin ($login, $forcequery = false) {
		global $aseco;

		if (array_key_exists($login, $this->player_list) && $this->player_list[$login]->id > 0 && $forcequery === false) {
			return (int)$this->player_list[$login]->id;
		}
		else {
			$id = 0;
			$query = "
			SELECT
				`PlayerId`
			FROM `%prefix%players`
			WHERE `Login` = ". $aseco->db->quote($login) ."
			LIMIT 1;
			";

			$res = $aseco->db->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$row = $res->fetch_row();
					$id = $row[0];
				}
				$res->free_result();
			}
			return (int)$id;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Finds a player Nickname from its login.
	public function getPlayerNickname ($login, $forcequery = false) {
		global $aseco;

		if (array_key_exists($login, $this->player_list) && $this->player_list[$login]->id > 0 && $forcequery === false) {
			return $this->player_list[$login]->nickname;
		}
		else {
			$nickname = 'Unknown';
			$query = "
			SELECT
				`Nickname`
			FROM `%prefix%players`
			WHERE `Login` = ". $aseco->db->quote($login) ."
			LIMIT 1;
			";

			$res = $aseco->db->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$row = $res->fetch_row();
					$nickname = $row[0];
				}
				$res->free_result();
			}
			return $nickname;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Finds an online player object from its login or Player_ID
	// If $offline = true, search player database instead
	// Returns false if not found
	public function getPlayerParam ($player, $param, $offline = false) {
		global $aseco;

		$target = false;

		// If numeric param, find Player_ID from /players list (hardlimited to 300)
		if (is_numeric($param) && $param >= 0 && $param < 300) {
			if (empty($player->playerlist)) {
				$message = '{#server}» {#error}Use {#highlite}$i/players {#error}first (optionally {#highlite}$i/players <string>{#error})';
				$aseco->sendChatMessage($message, $player->login);
				return false;
			}
			$pid = ltrim($param, '0');
			$pid--;

			// Find player by given #
			if (array_key_exists($pid, $player->playerlist)) {
				$param = $player->playerlist[$pid]['login'];
				// check online players list
				$target = $this->getPlayerByLogin($param);
			}
			else {
				// Try param as login string as yet
				if (!$target = $this->getPlayerByLogin($param)) {
					$message = '{#server}» {#error}PlayerId not found! Type {#highlite}$i/players {#error}to see all players.';
					$aseco->sendChatMessage($message, $player->login);
					return false;
				}
			}
		}
		else {
			// Otherwise login string, check online players list
			$target = $this->getPlayerByLogin($param);
		}

		// Not found and offline allowed?
		if (!$target && $offline) {
			// Check offline players database
			$query = "
			SELECT
				`PlayerId`,
				`Login`,
				`Nickname`,
				`Nation`,
				`Wins`,
				`TimePlayed`
			FROM `%prefix%players`
			WHERE `Login` = ". $aseco->db->quote($param) ."
			LIMIT 1;
			";

			$res = $aseco->db->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$row = $res->fetch_object();

					$target = new Player();
					$target->id		= $row->Id;
					$target->login		= $row->Login;
					$target->nickname	= $row->Nickname;
					$target->nation		= $row->Nation;
					$target->wins		= $row->Wins;
					$target->time_played	= $row->TimePlayed;
				}
				$res->free_result();
			}
		}

		// Found anyone anywhere?
		if (!$target) {
			$message = '{#server}» {#highlite}'. $param .' {#error}is not a valid player! Use {#highlite}$i/players {#error}to find the correct login or Player_ID.';
			$aseco->sendChatMessage($message, $player->login);
		}
		return $target;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkDatabase ($player) {
		global $aseco;

		// Do not add Fakeplayers into the database
		if (preg_match('#^\*fakeplayer\d+\*$#', $player->login) === 1) {
			return;
		}

		// Get Player stats
		$query = "
		SELECT
			`PlayerId`,
			`Visits`,
			`Wins`,
			`Donations`,
			`TimePlayed`
		FROM `%prefix%players`
		WHERE `Login`= ". $aseco->db->quote($player->login) ."
		LIMIT 1;
		";
		$result = $aseco->db->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				$dbplayer = $result->fetch_object();
				$result->free_result();

				// Update Player stats
				$player->id = $dbplayer->PlayerId;
				if ($player->visits < $dbplayer->Visits) {
					$player->visits = $dbplayer->Visits;
				}
				if ($player->wins < $dbplayer->Wins) {
					$player->wins = $dbplayer->Wins;
				}
				if ($player->donations < $dbplayer->Donations) {
					$player->donations = $dbplayer->Donations;
				}
				if ($player->time_played < $dbplayer->TimePlayed) {
					$player->time_played = $dbplayer->TimePlayed;
				}

				// Update Player data
				$query = "
				UPDATE `%prefix%players` SET
					`Nickname` = ". $aseco->db->quote($player->nickname) .",
					`Zone` = ". $aseco->db->quote(implode('|', $player->zone)) .",
					`Continent` = ". $aseco->db->quote($aseco->continent->continentToAbbreviation($player->continent)) .",
					`Nation` = ". $aseco->db->quote($player->nation) .",
					`LastVisit` = NOW()
				WHERE `Login` = ". $aseco->db->quote($player->login) .";
				";
				$result = $aseco->db->query($query);
				if (!$result) {
					trigger_error('[PlayerList] Could not update connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					return;
				}
			}
			else {
				// Could not be retrieved
				$result->free_result();
				$player->id = 0;

				// Insert player
				$query = "
				INSERT INTO `%prefix%players` (
					`Login`,
					`Nickname`,
					`Zone`,
					`Continent`,
					`Nation`,
					`LastVisit`,
					`Visits`,
					`Wins`,
					`Donations`,
					`TimePlayed`
				)
				VALUES (
					". $aseco->db->quote($player->login) .",
					". $aseco->db->quote($player->nickname) .",
					". $aseco->db->quote(implode('|', $player->zone)) .",
					". $aseco->db->quote($aseco->continent->continentToAbbreviation($player->continent)) .",
					". $aseco->db->quote($player->nation) .",
					NOW(),
					0,
					0,
					0,
					0
				);
				";

				$result = $aseco->db->query($query);
				if ($result) {
					$player->id = (int)$aseco->db->lastid();
				}
				else {
					trigger_error('[PlayerList] Could not insert connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					return;
				}
			}
		}
		else {
			trigger_error('[PlayerList] Could not get stats of connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function recalculateRanks () {
		global $aseco;

		$players = array();
		$aseco->console('[PlayerList] Calculating and updating server ranks for players...');
		$amount_maps = count($aseco->server->maps->map_list);

		// Erase old average data
		$aseco->db->query('TRUNCATE TABLE `%prefix%rankings`;');

		// Get list of players with at least $minrecs records (possibly unranked)
		$aseco->db->begin_transaction();				// Require PHP >= 5.5.0

		$query = '
		SELECT
			`PlayerId`,
			COUNT(*) AS `AmountOfRecords`
		FROM `%prefix%records`
		GROUP BY `PlayerId`
		HAVING `AmountOfRecords` >= '. $aseco->settings['server_rank_min_records'] .';
		';

		$res = $aseco->db->query($query);
		if ($res) {
			while ($row = $res->fetch_object()) {
				$players[$row->PlayerId] = array(
					'sum'	=> 0,
					'count'	=> 0,
				);
			}
			$res->free_result();

			if (!empty($players)) {
				if (isset($aseco->plugins['PluginLocalRecords']) === true) {
					$max_records = $aseco->plugins['PluginLocalRecords']->records->getMaxRecords();
				}
				else {
					$max_records = 500;	// Use a default value
				}

//				// RASP OLD: Get ranked records for all maps
//				foreach ($aseco->server->maps->map_list as $map) {
//					$query = "
//					SELECT
//						`PlayerId`
//					FROM `%prefix%records`
//					WHERE `MapId` = ". $map->id ."
//					AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
//					ORDER BY `Score` ASC, `Date` ASC
//					LIMIT ". $max_records .";
//					";
//
//					$res = $aseco->db->query($query);
//					if ($res) {
//						if ($res->num_rows > 0) {
//							$i = 1;
//							while ($row = $res->fetch_object()) {
//								if (isset($players[$row->PlayerId])) {
//									$players[$row->PlayerId]['sum'] += $i;
//									$players[$row->PlayerId]['count'] ++;
//								}
//								$i++;
//							}
//						}
//						$res->free_result();
//					}
//				}


				// Get ranked records for all maps
				$map_ids = array();
				foreach ($aseco->server->maps->map_list as $map) {
					$map_ids[] = $map->id;
				}
				sort($map_ids, SORT_NUMERIC);

				$data = array();
				$query = "
				SELECT
					`MapId`,
					`PlayerId`
				FROM `%prefix%records`
				WHERE `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				AND `MapId` IN (". implode(',', $map_ids) .")
				ORDER BY FIELD(`MapId`, ". implode(',', $map_ids) ."), `Score` ASC , `Date` ASC;
				";
				$res = $aseco->db->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						while ($row = $res->fetch_object()) {
							$data[$row->MapId][] = $row->PlayerId;
						}
					}
					$res->free_result();
				}
				foreach ($data as $map_id => $player_ids) {
					$rank = 0;
					foreach ($player_ids as $pid) {
						if (isset($players[$pid])) {
							$count = 1;
							foreach ($data[$map_id] as $ply_pid) {
								if ($pid === $ply_pid) {
									$rank = $count;
									break;
								}
								$count += 1;
							}
							$players[$pid]['sum'] += $rank;
							$players[$pid]['count'] ++;
						}
					}
				}


				$query = 'INSERT INTO `%prefix%rankings` (`PlayerId`, `Average`) VALUES ';
				$entries = array();
				foreach ($players as $pid => $ranked) {
					// ranked maps sum + $max_records rank for all remaining maps
					$avg = ($ranked['sum'] + ($amount_maps - $ranked['count']) * $max_records) / $amount_maps;
					$entries[] = '('. $pid .','. round($avg * 10000) .')';
				}
				$query .= implode(',', $entries);
				unset($entries);


				// Check for size and warn
				if (strlen($query) >= $aseco->db->settings['max_allowed_packet']) {
					$aseco->console('[PlayerList][WARNING] SQL statement is larger then database "max_allowed_packet" of '. $aseco->db->settings['max_allowed_packet'] .' bytes! Please increase the database settings "max_allowed_packet" to a larger value!');
				}

				$result = $aseco->db->query($query);
				if ($result === false) {
					if ($aseco->db->affected_rows === -1) {
						$aseco->console('[PlayerList][ERROR] Could not insert any player averages ('. $aseco->db->errmsg() .') for statement ['. $query .']');
					}
					else if ($aseco->db->affected_rows !== count($players)) {
						$aseco->console('[PlayerList][ERROR] Could not insert all '. count($players) .' player averages ('. $aseco->db->errmsg() .')! Please increase the database settings "max_allowed_packet" to a larger value!');
					}
				}
				else {
					$aseco->db->commit();

					$query = '
					SELECT
						`PlayerId`,
						`Average`
					FROM `%prefix%rankings`
					ORDER BY `Average` ASC, `PlayerId` ASC;
					';
					$res = $aseco->db->query($query);
					if ($res) {
						if ($res->num_rows > 0) {
							$total = $res->num_rows;
							$rank = 1;
							while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
								if ($pl = $this->getPlayerById($row['PlayerId'])) {
									$pl->server_rank		= $rank;
									$pl->server_rank_total		= $total;
									$pl->server_rank_average	= sprintf('%4.1F', $row['Average'] / 10000);
								}
								$rank += 1;
							}
						}
						$res->free_result();
					}
				}
			}
		}
		$aseco->console('[PlayerList] ...finished!');
	}
}

?>
