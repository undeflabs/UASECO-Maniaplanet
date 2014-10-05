<?php
/*
 * Class: Ranking List
 * ~~~~~~~~~~~~~~~~~~~
 * » Manages Player Ranking from the dedicated server.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-03
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
 *  - includes/class/ranking.class.php
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class RankingList {
	public $ranking_list;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		$this->ranking_list = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addPlayer ($player) {
		global $aseco;

		if ($player->id > 0) {
			// Preset
			$entry = new Ranking();
			$entry->rank		= 0;
			$entry->pid		= $player->pid;
			$entry->login		= $player->login;
			$entry->nickname	= $player->nickname;
			$entry->time		= 0;
			$entry->score		= 0;
			$entry->cps		= array();
			$entry->team		= $player->teamid;
			$entry->spectator	= $player->isspectator;
			$entry->away		= false;

			// Insert
			$this->ranking_list[$player->login] = $entry;

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

	public function update ($item) {
		global $aseco;

		// Update full player entry
		$entry					= $this->ranking_list[$item['login']];
		$entry->rank				= $item['rank'];
		$entry->login				= $item['login'];
		$entry->nickname			= $item['nickname'];
		$entry->time				= $item['time'];
		$entry->score				= $item['score'];
		$entry->cps				= $item['cps'];
		$entry->team				= $item['team'];
		$entry->spectator			= $item['spectator'];
		$entry->away				= $item['away'];
		$this->ranking_list[$entry->login]	= $entry;


		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP || $aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$scores = array();
			$times = array();
			$pids = array();
			foreach ($this->ranking_list as $key => &$row) {
				$scores[$key] = $row->score;
				$times[$key] = $row->time;
				$pids[$key] = $row->pid;
			}
			unset($key, $row);

			// Sort order: SCORE, PERSONAL_BEST and PID
			array_multisort(
				$scores, SORT_NUMERIC, SORT_DESC,
				$times, SORT_NUMERIC, SORT_ASC,
				$pids, SORT_NUMERIC, SORT_ASC,
				$this->ranking_list
			);
			unset($scores, $times, $pids);

		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
			$cps = array();
			$scores = array();
			$pids = array();
			foreach ($this->ranking_list as $key => &$row) {
				$cps[$key]	= count($row->cps);
				$scores[$key]	= $row->score;
				$pids[$key]	= $row->pid;
			}
			unset($key, $row);

			// Sort order: AMOUNT_CHECKPOINTS, SCORE and PID
			array_multisort(
				$cps, SORT_NUMERIC, SORT_DESC,
				$scores, SORT_NUMERIC, SORT_ASC,
				$pids, SORT_NUMERIC, SORT_ASC,
				$this->ranking_list
			);
			unset($cps, $scores, $pids);
		}
		else {
			$times = array();
			foreach ($this->ranking_list as $key => &$row) {
				if ($row->time <= 0) {
					$row->time = PHP_INT_MAX;
				}
				$times[$key] = $row->time;
			}
			unset($key, $row);

			// Sort order: TIME
			array_multisort(
				$times, SORT_NUMERIC, SORT_ASC,
				$this->ranking_list
			);
			unset($times);
		}

		$i = 1;
		foreach ($this->ranking_list as $login => &$data) {
			// Replace PHP_INT_MAX "times" to back "0"
			if ($data->time == PHP_INT_MAX) {
				$data->time = 0;
			}

			// Give each Player a Rank
			$data->rank = $i;
			$i += 1;
		}
		unset($login, $data);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function reset () {
		global $aseco;

		// Reset
		$this->ranking_list = array();

		// Setup empty rankings, but not in Team mode (not required)
		if ($aseco->server->gameinfo->mode != Gameinfo::TEAM) {
			foreach ($aseco->server->players->player_list as $player) {
				$this->addPlayer($player);
			}
		}
		$aseco->releaseEvent('onPlayerRankingUpdated', null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function count () {
		return count($this->ranking_list);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getRankByLogin ($login) {
		if (isset($this->ranking_list[$login])) {
			return $this->ranking_list[$login];
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

	public function getRank ($rank) {

		// Decrease by 1
		$rank -= 1;
		if (count($this->ranking_list) >= $rank) {
			$item = array_values(
				array_slice($this->ranking_list, $rank, 1, true)
			);
			return $item[0];
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

	public function getRange ($offset, $length) {
		return array_slice($this->ranking_list, $offset, $length);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTop3 () {

		$count = 1;
		$top = array();
		foreach ($this->ranking_list as $item) {
			$top[$item->login] = $item;
			if ($count == 3) {
				break;
			}
			$count++;
		}
		return $top;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTop10 () {

		$count = 1;
		$top = array();
		foreach ($this->ranking_list as $item) {
			$top[$item->login] = $item;
			if ($count == 10) {
				break;
			}
			$count++;
		}
		return $top;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTop50 () {

		$count = 1;
		$top = array();
		foreach ($this->ranking_list as $item) {
			$top[$item->login] = $item;
			if ($count == 50) {
				break;
			}
			$count++;
		}
		return $top;
	}
}

?>
