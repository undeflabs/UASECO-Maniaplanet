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
 * Date:	2014-11-01
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

	public $visits;
	public $wins;
	public $newwins;
	public $donations;
	public $timeplayed;

	public $data;

	public $style;
	public $panels;
	public $panelbg;

	public $unlocked;
	public $pmbuf;
	public $mutelist;
	public $mutebuf;
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
			$this->zone			= explode('|', substr($data['Path'], 6));  // Strip 'World|' and split into array()
			if (isset($this->zone[0])) {
				switch ($this->zone[0]) {
					case 'Europe':
					case 'Africa':
					case 'Asia':
					case 'Middle East':
					case 'North America':
					case 'South America':
					case 'Oceania':
						$this->continent = $this->zone[0];
						$this->nation = $aseco->country->countryToIoc($this->zone[1]);
						break;
					default:
						$this->continent = '';
						$this->nation = $aseco->country->countryToIoc($this->zone[0]);
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
			$this->login			= false;
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

			$this->teamid			= -1;
			$this->allies			= array();

			$this->ladderrank		= 0;
			$this->ladderscore		= 0;
			$this->nbwins			= 0;
			$this->nbdraws			= 0;
			$this->nblosses			= 0;

			$this->client			= '';
			$this->created			= 0;

			$this->zone			= array();
			$this->continent		= '';
			$this->nation			= 'OTH';
		}
		$this->wins				= 0;
		$this->newwins				= 0;
		$this->timeplayed			= 0;
		$this->donations			= 0;

		$this->data				= $this->getDatabasePlayerSettings();

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

	public function getDatabasePlayerSettings () {
		global $aseco;

		$settings = array();

		$query = "
		SELECT
			`Plugin`,
			`Key`,
			`Value`
		FROM `%prefix%settings`
		WHERE `PlayerId` = ". $aseco->db->quote($this->id) .";
		";

		$result = $aseco->db->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$settings[$row['Plugin']][$row['Key']] = unserialize($row['Value']);
				}
			}
			$result->free_result();
		}
		return $settings;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function storeDatabasePlayerSettings () {
		global $aseco;

		$aseco->db->query('START TRANSACTION;');
		foreach ($this->data as $plugin => $entries) {
			foreach ($entries as $key => $value) {
				$query = "
				INSERT INTO `%prefix%settings` (
					`Plugin`,
					`PlayerId`,
					`Key`,
					`Value`
				)
				VALUES (
					". $aseco->db->quote($plugin) .",
					". $aseco->db->quote($this->id) .",
					". $aseco->db->quote($key) .",
					". $aseco->db->quote(serialize($value)) ."
				)
				ON DUPLICATE KEY UPDATE
					`Value` = VALUES(`Value`);
				";
				$result = $aseco->db->query($query);
				if (!$result) {
					trigger_error('Saving Player settings failed for statement ['. $query .']', E_USER_WARNING);
				}
			}
		}
		$aseco->db->query('COMMIT;');
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
		FROM `%prefix%records` AS `r`
		LEFT JOIN `%prefix%maps` AS `m` ON `r`.`MapId` = `m`.`MapId`
		WHERE `Uid` IS NOT NULL
		ORDER BY `MapId` ASC, `Score` ". $order .", `Date` ASC;
		";

		$result = $aseco->db->query($query);
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
}

?>
