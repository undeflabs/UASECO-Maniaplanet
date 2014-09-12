<?php
/*
 * Class: MapList
 * ~~~~~~~~~~~~~~
 * » Stores information about all Maps on the dedicated server and provides several
 *   functions for sorting.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-17
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
 *  - includes/core/map.class.php
 *  - includes/core/gbxdatafetcher.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class MapList {
	public $map_list;						// Holds the whole map list with all installed maps
	public $current;						// Holds the current map object
	public $previous;						// Holds the previous map object

	public $max_age_mxinfo	= 86400;				// Age max. 86400 = 1 day
	public $size_limit	= 2097152;				// 2048 kB: Changed map size limit to 2MB: http://forum.maniaplanet.com/viewtopic.php?p=212999#p212999

	private $moods		= array(
		'Sunrise',
		'Day',
		'Sunset',
		'Night',
	);


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		$this->map_list = array();
		$this->current = false;
		$this->previous = false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapByUid ($uid) {
		if (isset($this->map_list[$uid])) {
			return $this->map_list[$uid];
		}
		else {
//			trigger_error('[MapList] getMapByUid(): Can not find map with uid "'. $uid .'" in map_list[]', E_USER_WARNING);
			return new Map(null, null);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapByFilename ($filename) {
		foreach ($this->map_list as $map) {
			if ($map['filename'] == $filename) {
				return $map;
			}
		}
//		trigger_error('[MapList] getMapByFilename(): Can not find map with filename "'. $filename .'" in map_list[]', E_USER_WARNING);
		return new Map(null, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPreviousMap () {
		return $this->previous;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentMap () {
		return $this->current;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getNextMap () {
		global $aseco;

		$uid = false;
		if (isset($aseco->plugins['PluginRaspJukebox']->jukebox) && count($aseco->plugins['PluginRaspJukebox']->jukebox) > 0) {
			foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $map) {
				// Need just the next juke'd Map Information, not more
				$uid = $map->uid;
				break;
			}
			unset($map);
		}
		else {
			// Get next map using 'GetNextMapInfo' list method
			$aseco->client->query('GetNextMapInfo');
			$response = $aseco->client->getResponse();
			$uid = $response['UId'];
			unset($response);
		}

		return $this->getMapByUid($uid);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function count () {
		return count($this->map_list);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function writeMapListCacheFile () {
		global $aseco;

		if ($fh = @fopen($aseco->settings['maplist_cache_file'], 'wb')) {
			fwrite($fh, serialize($aseco->server->maps->map_list));
			fclose($fh);
		}
		else {
			trigger_error('[MapList] Could not write map cache file ['. $aseco->settings['maplist_cache_file'] .']!', E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function readMapListCacheFile () {
		global $aseco;

		if (file_exists($aseco->settings['maplist_cache_file']) && is_readable($aseco->settings['maplist_cache_file'])) {
			$cache = @file_get_contents($aseco->settings['maplist_cache_file']);
			return unserialize($cache);
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

	public function readMapList () {
		global $aseco;

		// Try to read <maplist_cache_file>
		$mapcache = $this->readMapListCacheFile();
		if ($mapcache !== false) {
			$count = count($mapcache);
			$aseco->console('[MapList] Found maplist cache file with '. $count .' map'. ($count == 1 ? '' : 's') .'.');
		}


		// Get the MapList from Server
		$maplist = array();
		$newlist = array();
		$done = false;
		$size = 50;
		$i = 0;

		$aseco->client->resetError();
		while (!$done) {
			// GetMapList response: Name, UId, FileName, Environnement, Author, GoldTime, CopperPrice, MapType, MapStyle.
			$aseco->client->query('GetMapList', $size, $i);
			$newlist = $aseco->client->getResponse();

			if (!empty($newlist)) {
				if ( $aseco->client->isError() ) {
					trigger_error('[MapList] Error at the ListMethod "GetMapList": ['. $aseco->client->getErrorCode() .'] '. $aseco->client->getErrorMessage(), E_USER_WARNING);
					$done = true;
					break;
				}

				// Add the new Maps
				$maplist = array_merge($maplist, $newlist);

				if (count($newlist) < $size) {
					// got less than $size maps, might as well leave
					$done = true;
				}
				else {
					$i += $size;
				}
				unset($newlist);
			}
			else {
				$done = true;
			}
		}


		// Compare mapcache with received maplist
		if ($mapcache !== false) {
			$found = array();
			foreach ($maplist as $map) {
				if ( isset($mapcache[$map['UId']]) ) {
					$found[$map['UId']] = $mapcache[$map['UId']];
				}
			}
			if (count($maplist) == count($found)) {
				// 100% same, add and return
				$aseco->console('[MapList] Maplist cache file is identical to dedicated server maplist, using it.');
				$this->map_list = $found;

				// Find the current running map
				$this->current = $this->getCurrentMapInfo();

				// Remove outdated MX data
				foreach ($this->map_list as &$map) {
					if ( ($map->mx != false) && (time() > ($map->mx->timestamp_fetched + $this->max_age_mxinfo)) ) {
						$map->mx = false;
					}
				}
				unset($map);

				// No need for more work
				return;
			}
		}


		// Load map Ids from Database for all maps
		$uids = array();
		foreach ($maplist as $map) {
			$uids[] = $aseco->mysqli->quote($map['UId']);
		}
		$dbinfos = $this->getDatabaseMapInfos($uids);



		// Calculate karma for each map in database
		$karma = $this->calculateRaspKarma();



		$add_database = array();
		foreach ($maplist as $mapinfo) {

			// Retrieve MapInfo from GBXInfoFetcher
			$gbx = $this->parseMap($aseco->server->mapdir . $mapinfo['FileName']);

			// Create Map object
			$map = new Map($gbx, $mapinfo['FileName']);

			// Setup database id, if not present, add this to the list for adding into database
			if (isset($dbinfos[$mapinfo['UId']])) {
				$map->id		= $dbinfos[$mapinfo['UId']]['id'];
				$map->nblaps		= $dbinfos[$mapinfo['UId']]['nblaps'];
				$map->nbcheckpoints	= $dbinfos[$mapinfo['UId']]['nbcheckpoints'];

				// Update Map in database
				$this->updateMapInDatabase($map);
			}
			else {
				$add_database[] = $map;
			}


			// Add calculated karma to map
			if ( isset($karma[$map->uid]) ) {
				$map->karma = $karma[$map->uid];
			}
			else {
				$map->karma = array(
					'value'	=> 0,
					'votes'	=> 0,
				);
			}


			// Add to the Maplist
			if ($map->uid) {
				$this->map_list[$map->uid] = $map;
			}
		}
		unset($maplist, $dbinfos);



		// Add maps that are not yet stored in the database
		foreach ($add_database as $map) {
			$new = $this->insertMapIntoDatabase($map);

			// Update the Maplist
			if ($new->uid) {
				$this->map_list[$new->uid] = $new;
			}
		}
		unset($add_database);


		// Find the current running map
		$this->current = $this->getCurrentMapInfo();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function insertMapIntoDatabase ($map) {
		global $aseco;

		// `NbLaps` and `NbCheckpoints` only accessible with the ListMethod 'GetCurrentMapInfo'
		// and when the Map is actual loaded, update where made at $this->getCurrentMapInfo()
		$query = "
		INSERT INTO `maps` (
			`Uid`,
			`Name`,
			`Author`,
			`AuthorNickname`,
			`AuthorZone`,
			`AuthorContinent`,
			`AuthorNation`,
			`AuthorScore`,
			`AuthorTime`,
			`GoldTime`,
			`SilverTime`,
			`BronzeTime`,
			`Environment`,
			`Mood`,
			`Cost`,
			`Type`,
			`Style`,
			`MultiLap`,
			`NbLaps`,
			`NbCheckpoints`,
			`Validated`,
			`ExeVersion`,
			`ExeBuild`,
			`ModName`,
			`ModFile`,
			`ModUrl`,
			`SongFile`,
			`SongUrl`
		)
		VALUES (
			". $aseco->mysqli->quote($map->uid) .",
			". $aseco->mysqli->quote($map->name) .",
			". $aseco->mysqli->quote($map->author) .",
			". $aseco->mysqli->quote($map->author_nickname) .",
			". $aseco->mysqli->quote($map->author_zone) .",
			". $aseco->mysqli->quote($map->author_continent) .",
			". $aseco->mysqli->quote($map->author_nation) .",
			". $map->authorscore .",
			". $map->authortime .",
			". $map->goldtime .",
			". $map->silvertime .",
			". $map->bronzetime .",
			". $aseco->mysqli->quote($map->environment) .",
			". $aseco->mysqli->quote( (in_array($map->mood, $this->moods) ? $map->mood : 'unknown') ) .",
			". $map->cost .",
			". $aseco->mysqli->quote($map->type) .",
			". $aseco->mysqli->quote($map->style) .",
			". $aseco->mysqli->quote( (($map->multilap == true) ? 'true' : 'false') ) .",
			". 0 .",
			". 0 .",
			". $aseco->mysqli->quote( (($map->validated == true) ? 'true' : (($map->validated == false) ? 'false' : 'unknown')) ) .",
			". $aseco->mysqli->quote($map->exeversion) .",
			". $aseco->mysqli->quote($map->exebuild) .",
			". $aseco->mysqli->quote($map->modname) .",
			". $aseco->mysqli->quote($map->modfile) .",
			". $aseco->mysqli->quote($map->modurl) .",
			". $aseco->mysqli->quote($map->songfile) .",
			". $aseco->mysqli->quote($map->songurl) ."
		);
		";

		$aseco->mysqli->query($query);
		if ($aseco->mysqli->affected_rows === -1) {
			trigger_error('[MapList] Could not insert map in database: ('. $aseco->mysqli->errmsg() .')'. CRLF .' with statement ['. $query .']', E_USER_WARNING);
			return new Map(null, null);
		}
		else {
			$map->id = (int)$aseco->mysqli->lastid();
			return $map;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function updateMapInDatabase ($map) {
		global $aseco;

		$query = "
		UPDATE `maps`
		SET
			`Name` = ". $aseco->mysqli->quote($map->name) .",
			`Author` = ". $aseco->mysqli->quote($map->author) .",
			`AuthorNickname` = ". $aseco->mysqli->quote($map->author_nickname) .",
			`AuthorZone` = ". $aseco->mysqli->quote($map->author_zone) .",
			`AuthorContinent` = ". $aseco->mysqli->quote($map->author_continent) .",
			`AuthorNation` = ". $aseco->mysqli->quote($map->author_nation) .",
			`AuthorScore` = ". $map->authorscore .",
			`AuthorTime` = ". $map->authortime .",
			`GoldTime` = ". $map->goldtime .",
			`SilverTime` = ". $map->silvertime .",
			`BronzeTime` = ". $map->bronzetime .",
			`Environment` = ". $aseco->mysqli->quote($map->environment) .",
			`Mood` = ". $aseco->mysqli->quote( (in_array($map->mood, $this->moods) ? $map->mood : 'unknown') ) .",
			`Cost` = ". $map->cost .",
			`Type` = ". $aseco->mysqli->quote($map->type) .",
			`Style` = ". $aseco->mysqli->quote($map->style) .",
			`MultiLap` = ". $aseco->mysqli->quote( (($map->multilap == true) ? 'true' : 'false') ) .",
			`NbLaps` = ". $map->nblaps .",
			`NbCheckpoints` = ". $map->nbcheckpoints .",
			`Validated` = ". $aseco->mysqli->quote( (($map->validated == true) ? 'true' : (($map->validated == false) ? 'false' : 'unknown')) ) .",
			`ExeVersion` = ". $aseco->mysqli->quote($map->exeversion) .",
			`ExeBuild` = ". $aseco->mysqli->quote($map->exebuild) .",
			`ModName` = ". $aseco->mysqli->quote($map->modname) .",
			`ModFile` = ". $aseco->mysqli->quote($map->modfile) .",
			`ModUrl` = ". $aseco->mysqli->quote($map->modurl) .",
			`SongFile` = ". $aseco->mysqli->quote($map->songfile) .",
			`SongUrl` = ". $aseco->mysqli->quote($map->songurl) ."
		WHERE `Uid` = ". $aseco->mysqli->quote($map->uid) ."
		LIMIT 1;
		";
		$aseco->mysqli->query($query);
		if ($aseco->mysqli->affected_rows === -1) {
			trigger_error('[MapList] Could not update map in database: ('. $aseco->mysqli->errmsg() .')'. CRLF .' with statement ['. $query .']', E_USER_WARNING);
			return false;
		}
		else {
			return true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function parseMap ($file) {
		global $aseco;

		$gbx = new GBXChallMapFetcher(true, false, false);
		try {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$gbx->processFile(iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $aseco->stripBOM($file)));
			}
			else {
				$gbx->processFile($aseco->stripBOM($file));
			}
		}
		catch (Exception $e) {
			trigger_error('[MapList] Could not read Map ['. $aseco->stripBOM($file) .']: '. $e->getMessage(), E_USER_WARNING);

			// Ignore if Map could not be parsed
			return false;
		}
		return $gbx;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentMapInfo () {
		global $aseco;

		$aseco->client->query('GetCurrentMapInfo');
		$response = $aseco->client->getResponse();

		// Get Map from map_list[]
		$map = $this->getMapByUid($response['UId']);

		// Update 'NbLaps' and 'NbCheckpoints' for current Map from $response
		$map->nblaps = $response['NbLaps'];
		$map->nbcheckpoints = $response['NbCheckpoints'];

		// Store updated 'NbLaps' and 'NbCheckpoints' into database
		$this->updateMapInDatabase($map);

		return $map;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function getDatabaseMapInfos ($uids) {
		global $aseco;

		$data = array();

		// Find a map ID from its UID.
		$query = "
		SELECT
			`Id`,
			`Uid`,
			`NbLaps`,
			`NbCheckpoints`
		FROM `maps`
		WHERE `Uid` IN (". implode(',', $uids) .");
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_array()) {
					$data[$row['Uid']] = array(
						'id'		=> (int)$row['Id'],
						'nblaps'	=> (int)$row['NbLaps'],
						'nbcheckpoints'	=> (int)$row['NbCheckpoints'],
					);
				}
			}
			$res->free_result();
		}
		else {
			trigger_error('[MapList] Could not query map ids: ('. $aseco->mysqli->errmsg() .')'. CRLF .' for statement ['. $query .']', E_USER_WARNING);
		}
		return $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function calculateRaspKarma () {
		global $aseco;

		// Calculate the local Karma like RASP/Karma
		$data = array();
		$query = "
		SELECT
			`m`.`Uid`,
			SUM(`k`.`Score`) AS `Karma`,
			COUNT(`k`.`Score`) AS `Count`
		FROM `rs_karma` AS `k`
		LEFT JOIN `maps` AS `m` ON `m`.`Id` = `k`.`MapId`
		GROUP BY `k`.`MapId`;
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_object()) {
					$data[$row->Uid]['value'] = $row->Karma;
					$data[$row->Uid]['votes'] = $row->Count;
				}
			}
			$res->free_result();
		}
		return $data;
	}
}

?>
