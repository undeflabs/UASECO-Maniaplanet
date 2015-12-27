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
 * Date:	2015-10-30
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

class Player {
	public $id;			// Database Id
	public $pid;			// Dedicated Id
	public $login;
	public $nickname;
	public $language;
	public $avatar;
	public $clublink;

	public $ip;
	public $port;
	public $downloadrate;
	public $uploadrate;

	public $is_official;
	public $is_referee;
	public $is_podium_ready;
	public $is_using_stereoscopy;
	public $is_managed_by_other_server;
	public $is_server;
	public $is_broadcasting;

	public $has_joined_game;
	public $has_player_slot;

	public $is_spectator;
	public $forced_spectator;
	public $temporary_spectator;
	public $pure_spectator;

	public $target_autoselect;
	public $target_spectating;

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
			$this->id			= $aseco->server->players->getPlayerIdByLogin($data['Login'], true);
			$this->pid			= $data['PlayerId'];
			$this->login			= $data['Login'];
			$this->nickname			= $data['NickName'];
			$this->language			= $data['Language'];
			$this->avatar			= $data['Avatar']['FileName'];
			$this->clublink			= $data['ClubLink'];

			list($this->ip, $this->port)	= explode(':', $data['IPAddress']);
			$this->downloadrate		= $data['DownloadRate'];
			$this->uploadrate		= $data['UploadRate'];

			$this->is_spectator		= $data['IsSpectator'];
			$this->is_official		= $data['IsInOfficialMode'];
			$this->is_referee		= $data['IsReferee'];

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

			// Work on Player flags...
			$this->updateInfo($data);

			$this->data			= $this->getDatabasePlayerSettings();
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

			$this->is_spectator		= false;
			$this->is_official		= false;
			$this->is_referee		= false;

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

			// Work on Player flags...
			$info['Flags'] = 0;
			$info['SpectatorStatus'] = 0;
			$this->updateInfo($data);

			$this->data			= array();
		}
		$this->visits				= 0;
		$this->wins				= 0;
		$this->newwins				= 0;
		$this->timeplayed			= 0;
		$this->donations			= 0;

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

		// Bail out on *fakeplayer[N]*
		if ($this->id === 0) {
			return;
		}

		$aseco->db->begin_transaction();				// Require PHP >= 5.5.0
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
		$aseco->db->commit();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateInfo ($info) {
		global $aseco;

		// Updates without required handlings
		$this->nickname	= $info['NickName'];
		$this->teamid	= $info['TeamId'];

		// Check LadderRanking
		if ($info['LadderRanking'] > 0) {
			$this->ladderrank = $info['LadderRanking'];
			$this->is_official = true;
		}
		else {
			$this->is_official = false;
		}

		// Based upon https://github.com/NewboO/dedicated-server-api/blob/master/libraries/Maniaplanet/DedicatedServer/Structures/PlayerInfo.php
		$this->is_referee			= (bool)(intval($info['Flags'] / 10) % 10);
		$this->is_podium_ready			= (bool)(intval($info['Flags'] / 100) % 10);
		$this->is_using_stereoscopy		= (bool)(intval($info['Flags'] / 1000) % 10);
		$this->is_managed_by_other_server	= (bool)(intval($info['Flags'] / 10000) % 10);
		$this->is_server			= (bool)(intval($info['Flags'] / 100000) % 10);
		$this->has_player_slot			= (bool)(intval($info['Flags'] / 1000000) % 10);
		$this->is_broadcasting			= (bool)(intval($info['Flags'] / 10000000) % 10);
		$this->has_joined_game			= (bool)(intval($info['Flags'] / 100000000) % 10);

		$this->is_spectator			= (bool)($info['SpectatorStatus'] % 10);
		$this->forced_spectator			= $info['Flags'] % 10;					// 0, 1 or 2
		$this->temporary_spectator		= (bool)(intval($info['SpectatorStatus'] / 10) % 10);
		$this->pure_spectator			= (bool)(intval($info['SpectatorStatus'] / 100) % 10);

		$this->target_autoselect		= (bool)(intval($info['SpectatorStatus'] / 1000) % 10);
		$target					= $aseco->server->players->getPlayerByPid(intval($info['SpectatorStatus'] / 10000));
		$this->target_spectating		= ((!$target) ? false : $target->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getSpectatorStatus () {
		$status = false;
		if ($this->is_spectator == true) {
			$status = true;
		}
		if ($this->forced_spectator > 0) {
			$status = true;
		}
		if ($this->temporary_spectator == true) {
			$status = true;
		}
		if ($this->pure_spectator == true) {
			$status = true;
		}
		return $status;
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

		$last = false;
		$query = "
		SELECT
			`Uid`,
			`PlayerId`
		FROM `%prefix%records` AS `r`
		LEFT JOIN `%prefix%maps` AS `m` ON `r`.`MapId` = `m`.`MapId`
		WHERE `Uid` IS NOT NULL
		AND `r`.`GamemodeId` = '". $aseco->server->gameinfo->mode ."'
		ORDER BY `r`.`MapId` ASC, `Score` ASC, `Date` ASC;
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
