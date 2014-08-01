<?php
/*
 * Class: Ranking List
 * ~~~~~~~~~~~~~~~~~~~
 * » Manages Player Ranking from the dedicated server.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-14
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
		if ($player->login != '') {
			// Preset
			$this->ranking_list[$player->login] = array(
				'rank'		=> PHP_INT_MAX,
				'login'		=> $player->login,
				'nickname'	=> $player->nickname,
				'time'		=> 0,
				'score'		=> 0,
				'cps'		=> array(),
				'team'		=> $player->teamid,
				'spectator'	=> $player->isspectator,
				'away'		=> false,
			);
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
		$this->ranking_list[$item['login']] = $item;

		// Now sort array at Rank
		$rank = array();
		foreach ($this->ranking_list as $key => $row) {
			$rank[$key] = $row['rank'];
		}
		unset($key, $row);

		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			array_multisort($rank, SORT_NUMERIC, SORT_DESC, $this->ranking_list);
		}
		else {
			array_multisort($rank, SORT_NUMERIC, SORT_ASC, $this->ranking_list);
		}
		unset($rank);
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
			$top[$item['login']] = $item;
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
			$top[$item['login']] = $item;
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
			$top[$item['login']] = $item;
			if ($count == 50) {
				break;
			}
			$count++;
		}
		return $top;
	}
}

?>
