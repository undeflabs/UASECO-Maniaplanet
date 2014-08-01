<?php
/*
 * Class: Player List
 * ~~~~~~~~~~~~~~~~~~
 * » Manages Players on the server, add/remove Players and provides several get functions.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-24
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
		$player = false;
		if (isset($this->player_list[$login])) {
			$player = $this->player_list[$login];
			unset($this->player_list[$login]);
		}
		return $player;
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
			return new Player(null);
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
				`Id`
			FROM `players`
			WHERE `Login` = ". $aseco->mysqli->quote($login) .";
			";

			$res = $aseco->mysqli->query($query);
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
				`NickName`
			FROM `players`
			WHERE `Login` = ". $aseco->mysqli->quote($login) .";
			";

			$res = $aseco->mysqli->query($query);
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
				$aseco->client->query('ChatSendServerMessageToLogin', $this->formatColors($message), $player->login);
				return false;
			}
			$pid = ltrim($param, '0');
			$pid--;

			// find player by given #
			if (array_key_exists($pid, $player->playerlist)) {
				$param = $player->playerlist[$pid]['login'];
				// check online players list
				$target = $this->getPlayer($param);
			}
			else {
				// Try param as login string as yet
				$target = $this->getPlayer($param);
				if (!$target) {
					$message = '{#server}» {#error}Player_ID not found! Type {#highlite}$i/players {#error}to see all players.';
					$aseco->client->query('ChatSendServerMessageToLogin', $this->formatColors($message), $player->login);
					return false;
				}
			}
		}
		else {
			// otherwise login string, check online players list
			$target = $this->getPlayer($param);
		}

		// not found and offline allowed?
		if (!$target && $offline) {
			// Check offline players database
			$query = "
			SELECT
				`Id`,
				`Login`,
				`NickName`,
				`Nation`,
				`TeamName`,
				`Wins`,
				`TimePlayed`
			FROM `players`
			WHERE `Login` = ". $aseco->mysqli->quote($param) .";
			";

			$res = $aseco->mysqli->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$row = $res->fetch_object();

					$target = new Player();
					$target->id		= $row->Id;
					$target->login		= $row->Login;
					$target->nickname	= $row->NickName;
					$target->nation		= $row->Nation;
					$target->teamname	= $row->TeamName;
					$target->wins		= $row->Wins;
					$target->timeplayed	= $row->TimePlayed;
				}
				$res->free_result();
			}
		}

		// Found anyone anywhere?
		if (!$target) {
			$message = '{#server}» {#highlite}'. $param .' {#error}is not a valid player! Use {#highlite}$i/players {#error}to find the correct login or Player_ID.';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $player->login);
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

		// Setup nation from Player
		$nation = $aseco->country->countryToIoc($player->nation);

		// Get player stats
		$query = "
		SELECT
			`Id`,
			`Wins`,
			`TimePlayed`,
			`TeamName`
		FROM `players`
		WHERE `Login`= ". $aseco->mysqli->quote($player->login) .";
		";
		$result = $aseco->mysqli->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				$dbplayer = $result->fetch_object();
				$result->free_result();

				// Update player stats
				$player->id = $dbplayer->Id;
				if ($player->teamname == '' && $dbplayer->TeamName != '') {
					$player->teamname = $dbplayer->TeamName;
				}
				if ($player->wins < $dbplayer->Wins) {
					$player->wins = $dbplayer->Wins;
				}
				if ($player->timeplayed < $dbplayer->TimePlayed) {
					$player->timeplayed = $dbplayer->TimePlayed;
				}

				// Update player data
				$query = "
				UPDATE `players` SET
					`NickName` = ". $aseco->mysqli->quote($player->nickname) .",
					`Continent` = ". $aseco->continent->continentToId($player->continent) .",
					`Nation` = ". $aseco->mysqli->quote($nation) .",
					`TeamName` = ". $aseco->mysqli->quote($player->teamname) .",
					`UpdatedAt` = NOW()
				WHERE `Login`= ". $aseco->mysqli->quote($player->login) .";
				";
				$result = $aseco->mysqli->query($query);
				if (!$result) {
					trigger_error('[Database] Could not update connecting player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					return;
				}
			}
			else {
				// Could not be retrieved
				$result->free_result();
				$player->id = 0;

				// Insert player
				$query = "
				INSERT INTO `players` (
					`Login`,
					`Game`,
					`NickName`,
					`Continent`,
					`Nation`,
					`TeamName`,
					`UpdatedAt`
				)
				VALUES (
					". $aseco->mysqli->quote($player->login) .",
					". $aseco->mysqli->quote('MP') .",
					". $aseco->mysqli->quote($player->nickname) .",
					". $aseco->continent->continentToId($player->continent) .",
					". $aseco->mysqli->quote($nation) .",
					". $aseco->mysqli->quote($player->teamname) .",
					NOW()
				);
				";

				$result = $aseco->mysqli->query($query);
				if ($result) {
					$player->id = $aseco->mysqli->lastid();
				}
				else {
					trigger_error('[Database] Could not insert connecting player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
					return;
				}
			}
		}
		else {
			trigger_error('[Database] Could not get stats of connecting player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return;
		}

		// Check for player's extra data
		$query = "
		SELECT
			`PlayerId`
		FROM `players_extra`
		WHERE `PlayerId` = ". $player->id .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			// Was retrieved
			if ($result->num_rows > 0) {
				$result->free_result();
			}
			else {
				// Could not be retrieved
				$result->free_result();

				$query = "
				INSERT INTO `players_extra` (
					`PlayerId`,
					`Cps`,
					`DediCps`,
					`Donations`,
					`Style`
				)
				VALUES (
					". $player->id .",
					". ($aseco->settings['auto_enable_cps'] ? 0 : -1) .",
					". ($aseco->settings['auto_enable_dedicps'] ? 0 : -1) .",
					0,
					'Card'
				);
				";

				$result = $aseco->mysqli->query($query);
				if (!$result) {
					trigger_error('[Database] Could not insert player\'s extra data! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
				}
			}

		}
		else {
			trigger_error('[Database] Could not get player\'s extra data! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return;
		}
	}
}

?>
