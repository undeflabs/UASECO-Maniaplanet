<?php
/*
 * Class: Player List
 * ~~~~~~~~~~~~~~~~~~
 * » Manages Players on the server, add/remove Players and provides several get functions.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-02-16
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
 *  - none
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PlayerList {
	public $player_list;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
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
			if ($player->isspectator == true) {
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
		if (get_class($player) == 'Player' && $player->login != '') {

			// Check for existing Player, otherwise insert into Database
			$this->checkDatabase($player);

			// Add to Playerlist
			$this->player_list[$player->login] = $player;

			return true;
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removePlayer ($login) {
		if (isset($this->player_list[$login])) {
			$this->player_list[$login];
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

	public function getPlayer ($login) {
		if (isset($this->player_list[$login])) {
			return $this->player_list[$login];
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Finds a player ID from its login.
	public function getPlayerId ($login, $forcequery = false) {
		global $aseco;

		if (isset($this->server->players->player_list[$login]) && $this->server->players->player_list[$login]->id > 0 && !$forcequery) {
			return $this->server->players->player_list[$login]->id;
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
			return $id;
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

		if (isset($this->server->players->player_list[$login]) && $this->server->players->player_list[$login]->nickname != '' && !$forcequery) {
			return $this->server->players->player_list[$login]->nickname;
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
				$target = $this->getPlayer($param);
			}
			else {
				// Try param as login string as yet
				if (!$target = $this->getPlayer($param)) {
					$message = '{#server}» {#error}PlayerId not found! Type {#highlite}$i/players {#error}to see all players.';
					$aseco->sendChatMessage($message, $player->login);
					return false;
				}
			}
		}
		else {
			// Otherwise login string, check online players list
			$target = $this->getPlayer($param);
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
					$target->timeplayed	= $row->TimePlayed;
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
				if ($player->timeplayed < $dbplayer->TimePlayed) {
					$player->timeplayed = $dbplayer->TimePlayed;
				}

				// Update Player data
				$query = "
				UPDATE `%prefix%players` SET
					`Nickname` = ". $aseco->db->quote($player->nickname) .",
					`Zone` = ". $aseco->db->quote(implode('|', $player->zone)) .",
					`Continent` = ". $aseco->db->quote($aseco->continent->continentToAbbr($player->continent)) .",
					`Nation` = ". $aseco->db->quote($player->nation) .",
					`LastVisit` = NOW()
				WHERE `Login`= ". $aseco->db->quote($player->login) .";
				";
				$result = $aseco->db->query($query);
				if (!$result) {
					trigger_error('[Database] Could not update connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
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
					". $aseco->db->quote($aseco->continent->continentToAbbr($player->continent)) .",
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
					$player->id = $aseco->db->lastid();
				}
				else {
					trigger_error('[Database] Could not insert connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					return;
				}
			}
		}
		else {
			trigger_error('[Database] Could not get stats of connecting player! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return;
		}
	}
}

?>
