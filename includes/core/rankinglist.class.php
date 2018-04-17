<?php
/*
 * Class: Ranking List
 * ~~~~~~~~~~~~~~~~~~~
 * » Manages Player Ranking from the dedicated server.
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
 * Dependencies:
 *  - includes/class/ranking.class.php
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class RankingList extends BaseClass {
	public $ranking_list;

	private $debug		= false;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($debug) {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2018-04-17');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Manages Player Ranking from the dedicated server.');

		$this->debug = $debug;
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
			$entry->rank				= 0;
			$entry->pid				= $player->pid;
			$entry->login				= (string)$player->login;
			$entry->nickname			= (string)$player->nickname;
			$entry->round_points			= 0;
			$entry->map_points			= 0;
			$entry->match_points			= 0;
			$entry->best_race_time			= -1;
			$entry->best_race_respawns		= -1;
			$entry->best_race_checkpoints		= array();
			$entry->best_lap_time			= -1;
			$entry->best_lap_respawns		= -1;
			$entry->best_lap_checkpoints		= array();
			$entry->prev_race_time			= -1;
			$entry->prev_race_respawns		= -1;
			$entry->prev_race_checkpoints		= array();
			$entry->stunts_score			= 0;
			$entry->prev_stunts_score		= 0;

			// Insert
			$this->ranking_list[$entry->login]	= $entry;

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

		// Create a ranking entry
		$entry = new Ranking();
		$entry->rank				= $item['rank'];
		$entry->login				= (string)$item['login'];
		$entry->nickname			= (string)$item['nickname'];
		$entry->round_points			= $item['round_points'];
		$entry->map_points			= $item['map_points'];
		$entry->match_points			= $item['match_points'];
		$entry->best_race_time			= $item['best_race_time'];
		$entry->best_race_respawns		= $item['best_race_respawns'];
		$entry->best_race_checkpoints		= $item['best_race_checkpoints'];
		$entry->best_lap_time			= $item['best_lap_time'];
		$entry->best_lap_respawns		= $item['best_lap_respawns'];
		$entry->best_lap_checkpoints		= $item['best_lap_checkpoints'];
		$entry->prev_race_time			= $item['prev_race_time'];
		$entry->prev_race_respawns		= $item['prev_race_respawns'];
		$entry->prev_race_checkpoints		= $item['prev_race_checkpoints'];
		$entry->stunts_score			= $item['stunts_score'];
		$entry->prev_stunts_score		= $item['prev_stunts_score'];

		// Update full entry
		$this->ranking_list[$entry->login]	= $entry;


		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {
			$scores = array();
			$times = array();
			$pids = array();
			foreach ($this->ranking_list as $key => $row) {
				$scores[$key] = $row->map_points;
				$times[$key] = $row->best_race_time;
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
			$best_lap_checkpoints = array();
			$scores = array();
			$pids = array();
			foreach ($this->ranking_list as $key => $row) {
				$best_lap_checkpoints[$key]	= count($row->best_lap_checkpoints);
				$scores[$key]			= $row->map_points;
				$pids[$key]			= $row->pid;
			}
			unset($key, $row);

			// Sort order: AMOUNT_CHECKPOINTS, SCORE and PID
			array_multisort(
				$best_lap_checkpoints, SORT_NUMERIC, SORT_DESC,
				$scores, SORT_NUMERIC, SORT_ASC,
				$pids, SORT_NUMERIC, SORT_ASC,
				$this->ranking_list
			);
			unset($best_lap_checkpoints, $scores, $pids);
		}
		else {
			$times = array();
			foreach ($this->ranking_list as $key => $row) {
				if ($row->best_race_time <= 0) {
					$row->best_race_time = PHP_INT_MAX;
				}
				$times[$key] = $row->best_race_time;
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
		foreach ($this->ranking_list as $login => $data) {
			// Replace PHP_INT_MAX "times" to back "0"
			if ($data->best_race_time == PHP_INT_MAX) {
				$data->best_race_time = 0;
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
		$login = (string)$login;
		if (!empty($login) && isset($this->ranking_list[$login])) {
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
