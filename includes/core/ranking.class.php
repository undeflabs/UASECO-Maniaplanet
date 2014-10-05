<?php
/*
 * Class: Ranking
 * ~~~~~~~~~~~~~~
 * » Structure of a ranking.
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
 *  - none
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Ranking {
	public $rank;			// Current Rank
	public $pid;			// PlayerId at the dedicated Server
	public $login;			// PlayerLogin
	public $nickname;		// PlayerNickname
	public $time;			// Players best time
	public $score;			// Players current score
	public $cps;			// Array of Checkpoint times from the best time
	public $team;			// TeamId of that Team the Player is member from, -1 = no Team
	public $spectator;		// boolean indicator
	public $away;			// boolean indicator

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		$this->rank		= 0;
		$this->pid		= 0;
		$this->login		= 'Unset';
		$this->nickname		= 'Unset';
		$this->time		= 0;
		$this->score		= 0;
		$this->cps		= array();
		$this->team		= -1;
		$this->spectator	= false;
		$this->away		= false;
	}
}

?>
