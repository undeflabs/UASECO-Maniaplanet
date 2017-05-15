<?php
/*
 * Class: PlayList
 * ~~~~~~~~~~~~~~~
 * » Provides and handles a Playlist for Maps.
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

class PlayList extends BaseClass {
	private $debug		= false;
	private $playlist	= array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($debug) {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-04-30');
		$this->setCopyright('2015 - 2017 by undef.de');
		$this->setDescription('Provides and handles a Playlist for Maps.');

		$this->debug = $debug;
	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function readMapHistory () {
//		global $aseco;
//
//		// Read MapHistory
//		$query = "
//		SELECT
//			`MapId`,
//			`Date`
//		FROM `%prefix%maphistory`
//		ORDER BY `Date` DESC;
//		";
//
//		$result = $aseco->db->query($query);
//		if ($result) {
//			if ($result->num_rows > 0) {
//				$this->history = array();
//
//				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
//					$map = $aseco->server->maps->getMapById($row['MapId']);
//					if (isset($map->id) && $map->id > 0) {
//						$this->history[$map->id] = array(
//							'played'	=> $row['Date'],
//							'id'		=> $map->id,
//							'uid'		=> $map->uid,
//						);
//					}
//
//					if (count($this->history) >= $this->settings['max_history_entries']) {
//						break;
//					}
//				}
//
//				// Clean up the MapHistory table
////				$aseco->db->begin_transaction();		// Require PHP >= 5.5.0
//				$aseco->db->query('START TRANSACTION;');
//				$query = "TRUNCATE TABLE `%prefix%maphistory`;";
//
//				$aseco->db->query($query);
//				if ($aseco->db->affected_rows === 0) {
//					$values = array();
//					foreach ($this->history as $item) {
//						$values[] = "(". $item['id'] .", ". $aseco->db->quote($item['played']) .")";
//					}
//					if (count($values) > 0) {
//						$query = "
//						INSERT INTO `%prefix%maphistory` (
//							`MapId`,
//							`Date`
//						)
//						VALUES ". implode(',', $values) .";
//						";
//
//						$result2 = $aseco->db->query($query);
//						if ($aseco->db->affected_rows === -1) {
//							if ($aseco->debug) {
//								trigger_error('[MapHistory] readMapHistory(): Could not clean up table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
//							}
//							$aseco->db->rollback();
//						}
//						else {
//							$aseco->db->commit();
//						}
//					}
//					else {
//						$aseco->db->commit();
//					}
//				}
//			}
//			$result->free_result();
//		}
//	}
//
//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function addMapToHistory ($map) {
//		global $aseco;
//
//		if (isset($map->id) && $map->id > 0 && isset($aseco->server->maps->map_list[$map->uid])) {
//
//			// Update MapHistory in DB
//			$query = "
//			INSERT INTO `%prefix%maphistory` (
//				`MapId`,
//				`Date`
//			)
//			VALUES (
//				". $map->id .",
//				". $aseco->db->quote(date('Y-m-d H:i:s', time())) ."
//			);
//			";
//
//			$aseco->db->query($query);
//			if ($aseco->db->affected_rows === -1) {
//				if ($aseco->debug) {
//					trigger_error('[MapHistory] addMapToHistory(): Could not add Map into table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
//				}
//				return false;
//			}
//			else {
//				return true;
//			}
//		}
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Possible values for $method are ('select', 'vote', 'pay', 'add')

//	public function addMapToPlaylist ($uid, $login, $skippable = true, $first_position = false) {
	public function addMapToPlaylist ($uid, $login, $method = 'select') {
		global $aseco;

//		Test "SetNextMapIdent" instead of "ChooseNextMap"
//		Test "JumpToMapIdent"

		// Check for a Map which has to be "present in the selection" of the dedicated server
		$map = $aseco->server->maps->getMapByUid($uid);
		if ($map->id > 0) {

			$player = $aseco->server->players->getPlayerByLogin($login);

			// Add Map to Playlist array
			$this->playlist[] = array(
				'timestamp'	=> microtime(true),
				'map'		=> $map->id,
				'player'	=> $player->id,
				'method'	=> $method,
			);

			// Update Playlist in DB
			$query = "
			INSERT INTO `%prefix%playlist` (
				`Timestamp`,
				`MapId`,
				`PlayerId`,
				`Method`
			)
			VALUES (
				". microtime(true) .",
				". $map->id .",
				". $player->id .",
				". $aseco->db->quote($method) ."
			);
			";
			$aseco->db->query($query);
			if ($aseco->db->affected_rows === -1 && $aseco->debug) {
				trigger_error('[Playlist] addMapToPlaylist(): Could not add Map into table "playlist": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
			}

			// If Playlist is empty, setup the next Map at the dedicated server
			if (count($this->playlist) == 0) {
				try {
					// Set the next Map
					$result = $aseco->client->query('SetNextMapIdent', $map->uid);
					$aseco->dump($result, $uid, $login);
				}
				catch (Exception $exception) {
					$aseco->console('[Playlist] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SetNextMapIdent');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function isMapInPlaylistByUid ($uid) {
		if (!empty($uid)) {
			foreach ($this->playlist as $item) {
				if ($item['uid'] == $uid) {
					return true;
				}
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlaylistEntryByUid ($uid) {
		if (!empty($uid)) {
			foreach ($this->playlist as $item) {
				if ($item['uid'] == $uid) {
					return $item;
				}
			}
		}
		return false;
	}
}

?>
