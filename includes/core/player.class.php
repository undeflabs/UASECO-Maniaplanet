<?php
/*
 * Class: Player
 * ~~~~~~~~~~~~~
 * » Structure of a Player, contains information from 'GetPlayerInfo' and
 *   'GetDetailedPlayerInfo' ListMethods response.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-04
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

class Player {
	public $id;			// Database Id
	public $pid;			// Dedicated Id
	public $login;
	public $nickname;
	public $language;
	public $avatar;
	public $clublink;

	public $ip;
	public $ipport;
	public $downloadrate;
	public $uploadrate;

	public $prevstatus;
	public $isspectator;
	public $isofficial;
	public $isreferee;

	public $teamname;
	public $teamid;
	public $allies;

	public $ladderrank;
	public $ladderscore;
	public $lastmatchscore;
	public $nbwins;
	public $nbdraws;
	public $nblosses;

	public $client;
	public $created;

	public $zone_inscription;
	public $zone;
	public $continent;
	public $nation;

	public $wins;
	public $newwins;
	public $timeplayed;
	public $unlocked;
	public $pmbuf;
	public $mutelist;
	public $mutebuf;
	public $style;
	public $panels;
	public $panelbg;
	public $speclogin;
	public $dedirank;

	public $maplist;
	public $playerlist;
	public $msgs;

	const PLAYERS		= 1;
	const OPERATORS		= 2;
	const ADMINS		= 4;
	const MASTERADMINS	= 8;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($data = null) {
		global $aseco;

		if ($data) {
			$this->id			= $aseco->server->players->getPlayerId($data['Login'], true);
			$this->pid			= $data['PlayerId'];
			$this->login			= $data['Login'];
			$this->nickname			= $data['NickName'];
			$this->language			= $data['Language'];
			$this->avatar			= $data['Avatar']['FileName'];
			$this->clublink			= $data['ClubLink'];

			$this->ipport			= $data['IPAddress'];
			$this->ip			= preg_replace('/:\d+/', '', $data['IPAddress']);  // strip port
			$this->downloadrate		= $data['DownloadRate'];
			$this->uploadrate		= $data['UploadRate'];

			$this->prevstatus		= false;
			$this->isspectator		= $data['IsSpectator'];
			$this->isofficial		= $data['IsInOfficialMode'];
			$this->isreferee		= $data['IsReferee'];

			$this->teamname			= $data['LadderStats']['TeamName'];
			$this->teamid			= $data['TeamId'];
			$this->allies			= $data['Allies'];

			$this->ladderrank		= $data['LadderStats']['PlayerRankings'][0]['Ranking'];
			$this->ladderscore		= round($data['LadderStats']['PlayerRankings'][0]['Score'], 2);
			$this->lastmatchscore		= $data['LadderStats']['LastMatchScore'];
			$this->nbwins			= $data['LadderStats']['NbrMatchWins'];
			$this->nbdraws			= $data['LadderStats']['NbrMatchDraws'];
			$this->nblosses			= $data['LadderStats']['NbrMatchLosses'];

			$this->client			= $data['ClientVersion'];
			$this->created			= time();

			$this->zone_inscription		= $data['HoursSinceZoneInscription'];
			$this->zone = explode('|', substr($data['Path'], 6));  // Strip 'World|' and split into array()
			$zones = explode('|', $data['Path']);
			if (isset($zones[1])) {
				switch ($zones[1]) {
					case 'Europe':
					case 'Africa':
					case 'Asia':
					case 'Middle East':
					case 'North America':
					case 'South America':
					case 'Oceania':
						$this->continent = $zones[1];
						$this->nation = $aseco->country->countryToIoc($zones[2]);
						break;
					default:
						$this->continent = '';
						$this->nation = $aseco->country->countryToIoc($zones[1]);
				}
			}
			else {
				$this->continent = '';
				$this->nation = 'OTH';
			}
		}
		else {
			// Set empty defaults
			$this->id			= 0;
			$this->pid			= 0;
			$this->login			= 'Unknown '. microtime();
			$this->nickname			= 'Unknown';
			$this->language			= '';
			$this->avatar			= '';

			$this->ipport			= '';
			$this->ip			= '';
			$this->downloadrate		= 0;
			$this->uploadrate		= 0;

			$this->prevstatus		= false;
			$this->isspectator		= false;
			$this->isofficial		= false;
			$this->isreferee		= false;

			$this->teamname			= '';
			$this->teamid			= -1;
			$this->allies			= array();

			$this->ladderrank		= 0;
			$this->ladderscore		= 0;
			$this->nbwins			= 0;
			$this->nbdraws			= 0;
			$this->nblosses			= 0;

			$this->client			= '';
			$this->created			= 0;

			$this->zone			= '';
			$this->continent		= '';
			$this->nation			= 'OTH';
		}
		$this->wins				= 0;
		$this->newwins				= 0;
		$this->timeplayed			= 0;
		$this->unlocked				= false;
		$this->pmbuf				= array();
		$this->mutelist				= array();
		$this->mutebuf				= array();
		$this->style				= array();
		$this->panels				= array();
		$this->panelbg				= array();
		$this->speclogin			= '';
		$this->dedirank				= 0;

		$this->maplist				= array();
		$this->playerlist			= array();
		$this->msgs				= array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getWins () {
		return $this->wins + $this->newwins;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTimePlayed () {
		return $this->timeplayed + $this->getTimeOnline();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTimeOnline () {
		return $this->created > 0 ? time() - $this->created : 0;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Get a list of map uid and the related records from a Player.
	public function getRecords () {
		global $aseco;

		// get player's record for each map
		$list = array();
		$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');

		$last = false;
		$query = "
		SELECT
			`Uid`,
			`PlayerId`
		FROM `records` AS `r`
		LEFT JOIN `maps` AS `m` ON `r`.`MapId` = `m`.`Id`
		WHERE `Uid` IS NOT NULL
		ORDER BY `MapId` ASC, `Score` ". $order .", `Date` ASC;
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_object()) {
					// check for new map & reset rank
					if ($last != $row->Uid) {
						$last = $row->Uid;
						$pos = 1;
					}
					if (isset($list[$row->Uid])) {
						continue;
					}

					// Store player's maps & records
					if ($row->PlayerId == $this->id) {
						$list[$row->Uid] = $pos;
						continue;
					}
					$pos++;
				}
			}
			$result->free_result();
		}
		return $list;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCheckpointSettings () {
		global $aseco;

		// Setup defaults
		$settings = array('cps' => -1, 'dedicps' => -1);

		// Get CPs settings from player
		$query = "
		SELECT
			`Cps`,
			`DediCps`
		FROM `players_extra`
		WHERE `PlayerId` = ". $this->id .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$players_extra = $res->fetch_object();
				$settings = array('localcps' => (int)$players_extra->Cps, 'dedicps' => (int)$players_extra->DediCps);
			}
			else {
				trigger_error('[Player] Could not get checkpoint tracking settings for Player ['. $this->login .']! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			}
			$res->free_result();
		}
		return $settings;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setCheckpointSettings ($localcps, $dedicps) {
		global $aseco;

		$query = "
		UPDATE `players_extra` SET
			`Cps` = ". $localcps .",
			`DediCps` = ". $dedicps ."
		WHERE `PlayerId` = ". $this->id .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[Player] Could not set checkpoint tracking settings for Player ['. $this->login .']! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return false;
		}
		return true;
	}

}

?>
