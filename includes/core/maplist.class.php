<?php
/*
 * Class: MapList
 * ~~~~~~~~~~~~~~
 * » Stores information about all Maps on the dedicated server and provides several
 *   functions for sorting.
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
 *  - includes/core/map.class.php
 *  - includes/core/gbxdatafetcher.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class MapList extends BaseClass {
	public $map_list;						// Holds the whole map list with all installed maps
	public $previous;						// Holds the previous map object
	public $current;						// Holds the current map object
	public $next;							// Holds the next map object

	public $max_age_mxinfo		= 86400;			// Age max. 86400 = 1 day
	public $size_limit		= 4194304;			// 2017-03-10: 4096 kB: Changed map size limit to 4 MB: https://forum.maniaplanet.com/viewtopic.php?p=275568#p275568 (closed Beta MP4 forum)

	public $moods			= array(
		'Sunrise',
		'Day',
		'Sunset',
		'Night',
	);

	private $debug			= false;
	private $force_maplist_update	= false;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($debug, $force_maplist_update) {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2017-05-27');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Stores information about all Maps on the dedicated server and provides several functions for sorting.');

		$this->debug			= $debug;
		$this->force_maplist_update	= $force_maplist_update;

		$this->map_list			= array();
		$this->previous			= new Map(null, null);
		$this->current			= new Map(null, null);
		$this->next			= new Map(null, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentMapInfo () {
		global $aseco;

		$response = $aseco->client->query('GetCurrentMapInfo');

		// Get Map from map_list[]
		$map = $this->getMapByUid($response['UId']);
		if ($map->uid !== false) {
			// Update 'NbLaps' and 'NbCheckpoints' for current Map from $response,
			// this is required for old Maps (e.g. early Canyon beta or converted TMF Stadium)
			$map->nb_laps = $response['NbLaps'];
			$map->nb_checkpoints = $response['NbCheckpoints'];

			// Store updated 'NbLaps' and 'NbCheckpoints' into database
			$this->updateMapInDatabase($map);

			return $map;
		}
		return new Map(null, null);
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
			if ($this->debug) {
				trigger_error('[MapList] getMapByUid(): Can not find map with uid "'. $uid .'" in map_list[]', E_USER_WARNING);
			}
			return new Map(null, null);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removeMapByUid ($uid) {
		if (isset($this->map_list[$uid])) {
			unset($this->map_list[$uid]);
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

	public function getMapById ($id) {
		foreach ($this->map_list as $map) {
			if ($map->id == $id) {
				return $map;
			}
		}
		if ($this->debug) {
			trigger_error('[MapList] getMapByFilename(): Can not find map with ID "'. $id .'" in map_list[]', E_USER_WARNING);
		}
		return new Map(null, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapByFilename ($filename) {
		foreach ($this->map_list as $map) {
			if ($map->filename == $filename) {
				return $map;
			}
		}
		if ($this->debug) {
			trigger_error('[MapList] getMapByFilename(): Can not find map with filename "'. $filename .'" in map_list[]', E_USER_WARNING);
		}
		return new Map(null, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removeMapByFilename ($filename) {
		foreach ($this->map_list as $map) {
			if ($map->filename == $filename) {
				unset($this->map_list[$map->uid]);
				return true;
			}
		}
		return false;
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
				// Need just the next Map UID
				$uid = $map['uid'];
				break;
			}
			unset($map);
		}
		else {
			// Get next map using 'GetNextMapInfo' list method
			$response = $aseco->client->query('GetNextMapInfo');
			$uid = $response['UId'];
			unset($response);
		}

		$this->next = $this->getMapByUid($uid);
		return $this->next;
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

	public function readMapList () {
		global $aseco;

		// Init
		$this->map_list = array();

		// Get the MapList from Server
		$maplist = array();
		$newlist = array();
		$done = false;
		$size = 50;
		$i = 0;
		while (!$done) {
			try {
				// GetMapList response: Name, UId, FileName, Environnement, Author, GoldTime, CopperPrice, MapType, MapStyle.
				$newlist = $aseco->client->query('GetMapList', $size, $i);
				if (!empty($newlist)) {
					// Add the new Maps
					$maplist = array_merge($maplist, $newlist);

					if (count($newlist) < $size) {
						// Got less than $size maps, might as well leave
						$done = true;
					}
					else {
						$i += $size;
					}
					$newlist = array();
				}
				else {
					$done = true;
				}
			}
			catch (Exception $exception) {
				$aseco->console('[ClassMaplist] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetMapList: Error getting the current map list from the dedicated Server!');
				$done = true;
				break;
			}
		}
		unset($newlist);


		// Load map infos from Database for all maps
		$uids = array();
		foreach ($maplist as $map) {
			$uids[] = $aseco->db->quote($map['UId']);
		}
		$dbinfos = $this->getDatabaseMapInfos($uids);

		// Calculate karma for each map in database
		$karma = $this->calculateRaspKarma();


		$database = array();
		$database['insert'] = array();
		$database['update'] = array();
		$database['filenames'] = array();
		foreach ($maplist as $mapinfo) {
			// Setup from database, if not present, add this to the list for adding into database
			if (isset($dbinfos[$mapinfo['UId']]) && !empty($dbinfos[$mapinfo['UId']]['filename']) && $this->force_maplist_update === false) {
				// Create a dummy Map and setup it with data from the database
				$map			= new Map(null, null);
				$map->id		= $dbinfos[$mapinfo['UId']]['mapid'];
				$map->uid		= $dbinfos[$mapinfo['UId']]['uid'];
				$map->filename		= $mapinfo['FileName'];
				$map->name		= $dbinfos[$mapinfo['UId']]['name'];
				$map->name_stripped	= $dbinfos[$mapinfo['UId']]['name_stripped'];
				$map->comment		= $dbinfos[$mapinfo['UId']]['comment'];
				$map->author		= $dbinfos[$mapinfo['UId']]['author'];
				$map->author_nickname	= $dbinfos[$mapinfo['UId']]['author_nickname'];
				$map->author_zone	= $dbinfos[$mapinfo['UId']]['author_zone'];
				$map->author_continent	= $dbinfos[$mapinfo['UId']]['author_continent'];
				$map->author_nation	= $dbinfos[$mapinfo['UId']]['author_nation'];
				$map->author_score	= $dbinfos[$mapinfo['UId']]['author_score'];
				$map->author_time	= $dbinfos[$mapinfo['UId']]['author_time'];
				$map->gold_time		= $dbinfos[$mapinfo['UId']]['gold_time'];
				$map->silver_time	= $dbinfos[$mapinfo['UId']]['silver_time'];
				$map->bronze_time	= $dbinfos[$mapinfo['UId']]['bronze_time'];
				$map->nb_laps		= $dbinfos[$mapinfo['UId']]['nb_laps'];
				$map->multi_lap		= $dbinfos[$mapinfo['UId']]['multi_lap'];
				$map->nb_checkpoints	= $dbinfos[$mapinfo['UId']]['nb_checkpoints'];
				$map->cost		= $dbinfos[$mapinfo['UId']]['cost'];
				$map->environment	= $dbinfos[$mapinfo['UId']]['environment'];
				$map->mood		= $dbinfos[$mapinfo['UId']]['mood'];
				$map->type		= $dbinfos[$mapinfo['UId']]['type'];
				$map->style		= $dbinfos[$mapinfo['UId']]['style'];
				$map->validated		= $dbinfos[$mapinfo['UId']]['validated'];
				$map->exeversion	= $dbinfos[$mapinfo['UId']]['exeversion'];
				$map->exebuild		= $dbinfos[$mapinfo['UId']]['exebuild'];
				$map->mod_name		= $dbinfos[$mapinfo['UId']]['mod_name'];
				$map->mod_file		= $dbinfos[$mapinfo['UId']]['mod_file'];
				$map->mod_url		= $dbinfos[$mapinfo['UId']]['mod_url'];
				$map->song_file		= $dbinfos[$mapinfo['UId']]['song_file'];
				$map->song_url		= $dbinfos[$mapinfo['UId']]['song_url'];

				// Always update Map pathes in the database, to make sure the map can be found
				// if the admin has moved the Map files
				$database['filenames'][$mapinfo['UId']] = $mapinfo['FileName'];

				// Check for saved Thumbnail from the map, otherwise force to save it
				if ($this->getThumbnailByUid($map->uid) === false) {
					$this->parseMap($aseco->server->mapdir . $mapinfo['FileName'], false);
				}

			}
			else {
				// Retrieve MapInfo from GBXInfoFetcher
				$gbx = $this->parseMap($aseco->server->mapdir . $mapinfo['FileName'], true);

				// Create Map object
				$map = new Map($gbx, $mapinfo['FileName']);

				if (!empty($dbinfos[$mapinfo['UId']])) {
					// Update this Map in the database
					$map->id = $dbinfos[$mapinfo['UId']]['mapid'];
					$database['update'][] = $map;
				}
				else {
					// Add this new Map to the database
					$database['insert'][] = $map;
				}
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

		// Override after finished
		$this->force_maplist_update = false;


		// Add Maps that are not yet stored in the database
		$aseco->db->begin_transaction();				// Require PHP >= 5.5.0
		foreach ($database['insert'] as $map) {
			$new = $this->insertMapIntoDatabase($map);

			// Update the Maplist
			if ($new->id > 0) {
				$this->map_list[$new->uid] = $new;
			}
		}

		// Update Maps that are not up-to-date in the database
		foreach ($database['update'] as $map) {
			$result = $this->updateMapInDatabase($map);

			// Update the Maplist
			if ($result == true) {
				$this->map_list[$map->uid] = $map;
			}
		}

		// Update all current Filenames
		$this->updateFilenamesInDatabase($database['filenames']);

		$aseco->db->commit();
		unset($database);


		// Find the current running map
		$this->current = $this->getCurrentMapInfo();

		// Find the next map
		$this->next = $this->getNextMap();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addMapToListByUid ($uid) {
		global $aseco;

		// Get the MapList from Server
		$maplist = array();
		$newlist = array();
		$done = false;
		$size = 50;
		$i = 0;
		while (!$done) {
			try {
				// GetMapList response: Name, UId, FileName, Environnement, Author, GoldTime, CopperPrice, MapType, MapStyle.
				$newlist = $aseco->client->query('GetMapList', $size, $i);
				if (!empty($newlist)) {
					// Add the new Maps
					foreach ($newlist as $mapinfo) {
						if ($mapinfo['UId'] == $uid) {
							$maplist[] = $mapinfo;
							$done = true;
						}
					}

					if (count($newlist) < $size) {
						// Got less than $size maps, might as well leave
						$done = true;
					}
					else {
						$i += $size;
					}
					$newlist = array();
				}
				else {
					$done = true;
				}
			}
			catch (Exception $exception) {
				$aseco->console('[ClassMaplist] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetMapList: Error getting the current map list from the dedicated Server!');
				$done = true;
				break;
			}
		}
		unset($newlist);


		// Load map infos from Database for all maps
		$dbinfos = $this->getDatabaseMapInfos(array($uid));

		// Calculate karma for each map in database
		$karma = $this->calculateRaspKarma();


		$database = array();
		$database['insert'] = array();
		$database['update'] = array();
		$database['filenames'] = array();
		foreach ($maplist as $mapinfo) {
			// Retrieve MapInfo from GBXInfoFetcher
			$gbx = $this->parseMap($aseco->server->mapdir . $mapinfo['FileName'], true);

			// Create Map object
			$map = new Map($gbx, $mapinfo['FileName']);

			if (!empty($dbinfos[$mapinfo['UId']])) {
				// Update this Map in the database
				$map->id = $dbinfos[$mapinfo['UId']]['mapid'];
				$database['update'][] = $map;
			}
			else {
				// Add this new Map to the database
				$database['insert'][] = $map;
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

		// Add Maps that are not yet stored in the database
		$aseco->db->begin_transaction();				// Require PHP >= 5.5.0
		foreach ($database['insert'] as $map) {
			$new = $this->insertMapIntoDatabase($map);

			// Update the Maplist
			if ($new->id > 0) {
				$this->map_list[$new->uid] = $new;
			}
		}

		// Update Maps that are not up-to-date in the database
		foreach ($database['update'] as $map) {
			$result = $this->updateMapInDatabase($map);

			// Update the Maplist
			if ($result == true) {
				$this->map_list[$map->uid] = $map;
			}
		}

		// Update all current Filenames
		$this->updateFilenamesInDatabase($database['filenames']);

		$aseco->db->commit();
		unset($database);

		// Find the next map
		$this->next = $this->getNextMap();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkMapAuthor ($map) {
		global $aseco;

		$query = "
		SELECT
			`AuthorId`
		FROM `%prefix%authors`
		WHERE `Login` = ". $aseco->db->quote($map->author) ."
		LIMIT 1;
		";
		$result = $aseco->db->select_one($query);
		if (!isset($result['AuthorId'])) {
			$query = "
			INSERT INTO `%prefix%authors` (
				`Login`,
				`Nickname`,
				`Zone`,
				`Continent`,
				`Nation`
			)
			VALUES (
				". $aseco->db->quote($map->author) .",
				". $aseco->db->quote($map->author_nickname) .",
				". $aseco->db->quote(implode('|', $map->author_zone)) .",
				". $aseco->db->quote($map->author_continent) .",
				". $aseco->db->quote($map->author_nation) ."
			);
			";

			$result = $aseco->db->query($query);
			if ($result) {
				return (int)$aseco->db->lastid();
			}
			else {
				return 0;
			}
		}
		else {
			$aid = $result['AuthorId'];
			if ($map->author_continent != '' && $map->author_nation != 'OTH') {
				$query = "
				UPDATE `%prefix%authors`
				SET
					`Nickname` = ". $aseco->db->quote($map->author_nickname) .",
					`Zone` = ". $aseco->db->quote(implode('|', $map->author_zone)) .",
					`Continent` = ". $aseco->db->quote($map->author_continent) .",
					`Nation` = ". $aseco->db->quote($map->author_nation) ."
				WHERE `AuthorId` = ". $aid ."
				LIMIT 1;
				";

				$result = $aseco->db->query($query);
				if ($result) {
					return $aid;
				}
				else {
					return 0;
				}
			}
			else {
				$query = "
				SELECT
					`AuthorId`,
					`Login`,
					`Nickname`,
					`Zone`,
					`Continent`,
					`Nation`
				FROM `%prefix%authors`
				WHERE `Login` = ". $aseco->db->quote($map->author) ."
				LIMIT 1;
				";
				$result = $aseco->db->select_one($query);
				if ($result) {
					$map->author		= $result['Login'];
					$map->author_nickname	= $result['Nickname'];
					$map->author_zone	= explode('|', $result['Zone']);
					$map->author_continent	= $result['Continent'];
					$map->author_nation	= $result['Nation'];

					return $result['AuthorId'];
				}
				else {
					return 0;
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function insertMapIntoDatabase ($map) {
		global $aseco;

		// Bail out on Maps without an UniqueId
		if (!$map->uid) {
			return new Map(null, null);
		}

		// Insert or Update the Map Author
		$authorid = $this->checkMapAuthor($map);

		// `NbLaps` and `NbCheckpoints` only accessible with the ListMethod 'GetCurrentMapInfo'
		// and when the Map is actual loaded, update where made at $this->getCurrentMapInfo().
		// But in Maps with chunk version 13 (0x03043002), these information are accessible (newer Maps).
		$query = "
		INSERT INTO `%prefix%maps` (
			`Uid`,
			`Filename`,
			`Name`,
			`Comment`,
			`AuthorId`,
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
			". $aseco->db->quote($map->uid) .",
			". $aseco->db->quote($map->filename) .",
			". $aseco->db->quote($map->name) .",
			". $aseco->db->quote($map->comment) .",
			". $authorid .",
			". $map->author_score .",
			". $map->author_time .",
			". $map->gold_time .",
			". $map->silver_time .",
			". $map->bronze_time .",
			". $aseco->db->quote($map->environment) .",
			". $aseco->db->quote( (in_array($map->mood, $this->moods) ? $map->mood : 'unknown') ) .",
			". $map->cost .",
			". $aseco->db->quote($map->type) .",
			". $aseco->db->quote($map->style) .",
			". $aseco->db->quote( (($map->multi_lap == true) ? 'true' : 'false') ) .",
			". (($map->nb_laps > 1) ? $map->nb_laps : 0) .",
			". $map->nb_checkpoints .",
			". $aseco->db->quote( (($map->validated == true) ? 'true' : (($map->validated == false) ? 'false' : 'unknown')) ) .",
			". $aseco->db->quote($map->exeversion) .",
			". $aseco->db->quote($map->exebuild) .",
			". $aseco->db->quote($map->mod_name) .",
			". $aseco->db->quote($map->mod_file) .",
			". $aseco->db->quote($map->mod_url) .",
			". $aseco->db->quote($map->song_file) .",
			". $aseco->db->quote($map->song_url) ."
		);
		";

		$aseco->db->query($query);
		if ($aseco->db->affected_rows === -1) {
			if ($this->debug) {
				trigger_error('[MapList] Could not insert map in database: ('. $aseco->db->errmsg() .')'. CRLF .' with statement ['. $query .']', E_USER_WARNING);
			}
			return new Map(null, null);
		}
		else {
			$map->id = (int)$aseco->db->lastid();
			return $map;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateMapInDatabase ($map) {
		global $aseco;

		// Bail out on Maps without an UniqueId
		if (!$map->uid) {
			return false;
		}

		// Insert or Update the Map Author
		$authorid = $this->checkMapAuthor($map);

		$query = "
		UPDATE `%prefix%maps`
		SET
			`Filename` = ". $aseco->db->quote($map->filename) .",
			`Name` = ". $aseco->db->quote($map->name) .",
			`Comment` = ". $aseco->db->quote($map->comment) .",
			`AuthorId` = ". $authorid .",
			`AuthorScore` = ". $map->author_score .",
			`AuthorTime` = ". $map->author_time .",
			`GoldTime` = ". $map->gold_time .",
			`SilverTime` = ". $map->silver_time .",
			`BronzeTime` = ". $map->bronze_time .",
			`Environment` = ". $aseco->db->quote($map->environment) .",
			`Mood` = ". $aseco->db->quote( (in_array($map->mood, $this->moods) ? $map->mood : 'unknown') ) .",
			`Cost` = ". $map->cost .",
			`Type` = ". $aseco->db->quote($map->type) .",
			`Style` = ". $aseco->db->quote($map->style) .",
			`MultiLap` = ". $aseco->db->quote( (($map->multi_lap == true) ? 'true' : 'false') ) .",
			`NbLaps` = ". (($map->nb_laps > 1) ? $map->nb_laps : 0) .",
			`NbCheckpoints` = ". $map->nb_checkpoints .",
			`Validated` = ". $aseco->db->quote( (($map->validated == true) ? 'true' : (($map->validated == false) ? 'false' : 'unknown')) ) .",
			`ExeVersion` = ". $aseco->db->quote($map->exeversion) .",
			`ExeBuild` = ". $aseco->db->quote($map->exebuild) .",
			`ModName` = ". $aseco->db->quote($map->mod_name) .",
			`ModFile` = ". $aseco->db->quote($map->mod_file) .",
			`ModUrl` = ". $aseco->db->quote($map->mod_url) .",
			`SongFile` = ". $aseco->db->quote($map->song_file) .",
			`SongUrl` = ". $aseco->db->quote($map->song_url) ."
		WHERE `Uid` = ". $aseco->db->quote($map->uid) ."
		LIMIT 1;
		";

		$result = $aseco->db->query($query);
		if ($aseco->db->affected_rows === -1) {
			if ($this->debug) {
				trigger_error('[MapList] Could not update map in database: ('. $aseco->db->errmsg() .')'. CRLF .' with statement ['. $query .']', E_USER_WARNING);
			}
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

	private function updateFilenamesInDatabase ($list) {
		global $aseco;

		foreach ($list as $uid => $filename) {
			$query = "
			UPDATE `%prefix%maps`
			SET
				`Filename` = ". $aseco->db->quote($filename) ."
			WHERE `Uid` = ". $aseco->db->quote($uid) ."
			LIMIT 1;
			";

			$result = $aseco->db->query($query);
			if (!$result && $this->debug) {
				trigger_error('[MapList] Could not update map in database: ('. $aseco->db->errmsg() .')'. CRLF .' with statement ['. $query .']', E_USER_WARNING);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function getDatabaseMapInfos ($uids) {
		global $aseco;

		$data = array();

		// Read Map infos
		$query = "
		SELECT
			`MapId`,
			`Uid`,
			`Filename`,
			`Name`,
			`Comment`,
			`a`.`Login`,
			`a`.`Nickname`,
			`a`.`Zone`,
			`a`.`Continent`,
			`a`.`Nation`,
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
		FROM `%prefix%maps` AS `m`
		LEFT JOIN `%prefix%authors` AS `a` ON `a`.`AuthorId` = `m`.`AuthorId`
		WHERE `Uid` IN (". implode(',', $uids) .");
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
					$data[$row['Uid']] = array(
						'mapid'			=> (int)$row['MapId'],
						'uid'			=> $row['Uid'],
						'filename'		=> $row['Filename'],
						'name'			=> $row['Name'],
						'name_stripped'		=> $aseco->stripStyles($row['Name'], true),
						'comment'		=> $row['Comment'],
						'author'		=> $row['Login'],
						'author_nickname'	=> $row['Nickname'],
						'author_zone'		=> explode('|', $row['Zone']),
						'author_continent'	=> $row['Continent'],
						'author_nation'		=> $row['Nation'],
						'author_score'		=> $row['AuthorScore'],
						'author_time'		=> $row['AuthorTime'],
						'gold_time'		=> $row['GoldTime'],
						'silver_time'		=> $row['SilverTime'],
						'bronze_time'		=> $row['BronzeTime'],
						'nb_laps'		=> (int)$row['NbLaps'],
						'multi_lap'		=> $aseco->string2bool($row['MultiLap']),
						'nb_checkpoints'	=> (int)$row['NbCheckpoints'],
						'cost'			=> $row['Cost'],
						'environment'		=> $row['Environment'],
						'mood'			=> $row['Mood'],
						'type'			=> $row['Type'],
						'style'			=> $row['Style'],
						'validated'		=> $aseco->string2bool($row['Validated']),
						'exeversion'		=> $row['ExeVersion'],
						'exebuild'		=> $row['ExeBuild'],
						'mod_name'		=> $row['ModName'],
						'mod_file'		=> $row['ModFile'],
						'mod_url'		=> $row['ModUrl'],
						'song_file'		=> $row['SongFile'],
						'song_url'		=> $row['SongUrl'],
					);
				}
			}
			$res->free_result();
		}
		else if ($this->debug) {
			trigger_error('[MapList] Could not query map datas: ('. $aseco->db->errmsg() .')'. CRLF .' for statement ['. $query .']', E_USER_WARNING);
		}
		return $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// http://forum.maniaplanet.com/viewtopic.php?p=219824#p219824
	// The_Big_Boo: Because Windows doesn't support UTF-8 filenames natively but the API used in the
	// dedicated server has some workaround which needs the UTF-8 BOM to be prepended.
	// = stripBOM() on filenames for PHP
	public function parseMap ($file, $force_thumbnail = false) {
		global $aseco;

		$gbx = new GBXChallMapFetcher(true, true, false);
		try {
			if (OPERATING_SYSTEM === 'WINDOWS') {
				$file = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $aseco->stripBOM($file));
				if ($file !== false) {
					$gbx->processFile($file);
				}
				else if ($this->debug) {
					trigger_error('[MapList] Could not read Map ['. $file .'] because iconv() returned "false".', E_USER_WARNING);
				}
			}
			else {
				$gbx->processFile($aseco->stripBOM($file));
			}

			// Check for saved Thumbnail from the map, otherwise save it (if not forced to do)
			if ($force_thumbnail === true || $this->getThumbnailByUid($gbx->uid) === false) {
				$this->saveThumbnail($gbx);
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

	public function getThumbnailByUid ($uid) {
		global $aseco;

		if (isset($this->map_list[$uid])) {
			$thumbnail = @file_get_contents($aseco->settings['mapimages_path']. $uid .'.jpg');
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

	public function saveThumbnail ($gbx) {
		global $aseco;

		if (!is_dir($aseco->settings['mapimages_path'])) {
			if (!is_file($aseco->settings['mapimages_path'])) {
				mkdir($aseco->settings['mapimages_path'], 0755, true);
			}
			else {
				trigger_error('[MapList] Configured directory at <mapimages_path> in UASECO.xml ['. $aseco->settings['mapimages_path'] .'] can not be created, because a file with that name exists!', E_USER_WARNING);
			}
		}

		if (is_writeable($aseco->settings['mapimages_path'])) {
			$filename = $gbx->uid .'.jpg';
			if ($fh = fopen($aseco->settings['mapimages_path'] . $filename, 'wb')) {
				fwrite($fh, $gbx->thumbnail);
				fclose($fh);
			}

			// Free MEM
			$gbx->thumbnail = null;
			unset($gbx->thumbnail);
		}
		else {
			trigger_error('[MapList] Configured directory at <mapimages_path> in UASECO.xml ['. $aseco->settings['mapimages_path'] .'] is not writeable!', E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function calculateRaspKarma () {
		global $aseco;

		// Calculate the local Karma like RASP/Karma
		$data = array();
		$query = "
		SELECT
			`m`.`Uid`,
			SUM(`k`.`Score`) AS `Karma`,
			COUNT(`k`.`Score`) AS `Count`
		FROM `%prefix%ratings` AS `k`
		LEFT JOIN `%prefix%maps` AS `m` ON `m`.`MapId` = `k`.`MapId`
		GROUP BY `k`.`MapId`;
		";

		$res = $aseco->db->query($query);
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
