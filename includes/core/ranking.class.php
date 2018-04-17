<?php
/*
 * Class: Ranking
 * ~~~~~~~~~~~~~~
 * » Structure of a ranking.
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

class Ranking extends BaseClass {
	public $rank;			// Current Rank
	public $pid;			// PlayerId at the dedicated Server
	public $login;			// PlayerLogin
	public $nickname;		// PlayerNickname
	public $round_points;
	public $map_points;
	public $match_points;
	public $best_race_time;		// Best race time in milliseconds
	public $best_race_respawns;	// Number of respawn during best race
	public $best_race_checkpoints;	// Checkpoints times during best race
	public $best_lap_time;		// Best lap time in milliseconds
	public $best_lap_respawns;	// Number of respawn during best lap
	public $best_lap_checkpoints;	// Checkpoints times during best lap
	public $prev_race_time;		// Best race time in milliseconds of the previous race
	public $prev_race_respawns;	// Number of respawn of the previous race
	public $prev_race_checkpoints;	// Checkpoints times of the previous race
	public $stunts_score;
	public $prev_stunts_score;



	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2018-04-17');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Structure of a ranking.');

		$this->rank			= 0;
		$this->pid			= 0;
		$this->login			= 'Unset';
		$this->nickname			= 'Unset';
		$this->round_points		= 0;
		$this->map_points		= 0;
		$this->match_points		= 0;
		$this->best_race_time		= -1;
		$this->best_race_respawns	= -1;
		$this->best_race_checkpoints	= array();
		$this->best_lap_time		= -1;
		$this->best_lap_respawns	= -1;
		$this->best_lap_checkpoints	= array();
		$this->prev_race_time		= -1;
		$this->prev_race_respawns	= -1;
		$this->prev_race_checkpoints	= array();
		$this->stunts_score		= 0;
		$this->prev_stunts_score	= 0;
	}
}

?>
