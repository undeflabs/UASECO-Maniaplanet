<?php
/*
 * Class: Playlist
 * ~~~~~~~~~~~~~~~
 * » Structure for a Map Playlist.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-17
 * Copyright:	2015 by undef.de
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

class Playlist {
	public $settings	= array();
	public $history		= array();
	public $playlist	= array();

	private $debug		= false;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($debug, $max_history_entries) {
		$this->debug = $debug;

		$this->settings['max_history_entries'] = $max_history_entries;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function readMapHistory () {
		global $aseco;

		// Read MapHistory
		$query = "
		SELECT
			`MapId`,
			MAX(`Date`) as `Time`
		FROM `%prefix%maphistory`
		GROUP BY `MapId`
		ORDER BY `Date` DESC
		LIMIT ". $this->settings['max_history_entries'] .";
		";

		$result = $aseco->db->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				$count = 0;
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					if ($count == 0) {
						$aseco->server->maps->previous = $aseco->server->maps->getMapById($row['MapId']);
					}

					$map = $aseco->server->maps->getMapById($row['MapId']);
					$this->history[] = array(
						'played'	=> $row['Time'],
						'id'		=> $map->id,
						'uid'		=> $map->uid,
					);

					$count += 1;
				}

				// Clean up the MapHistory table
				$aseco->db->begin_transaction();
				$query = "TRUNCATE TABLE `%prefix%maphistory`;";

				$aseco->db->query($query);
				if ($aseco->db->affected_rows === 0) {
					$values = array();
					foreach ($this->history as $item) {
						$values[] = "(". $item['id'] .", ". $aseco->db->quote($item['played']) .")";
					}
					if (count($values) > 0) {
						$query = "
						INSERT INTO `%prefix%maphistory` (
							`MapId`,
							`Date`
						)
						VALUES ". implode(',', $values) .";
						";

						$aseco->db->query($query);
						if ($aseco->db->affected_rows === -1) {
							if ($aseco->debug) {
								trigger_error('[Playlist] readMapHistory(): Could not clean up table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
							}
							$aseco->db->rollback();
						}
						else {
							$aseco->db->commit();
						}
					}
					else {
						$aseco->db->commit();
					}
				}
			}
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addMapToHistory ($map) {
		global $aseco;

		if (isset($map->id) && $map->id > 0 && isset($aseco->server->maps->map_list[$map->uid])) {

			// Update MapHistory in DB
			$query = "
			INSERT INTO `%prefix%maphistory` (
				`MapId`,
				`Date`
			)
			VALUES (
				". $map->id .",
				". $aseco->db->quote(date('Y-m-d H:i:s', time() - date('Z'))) ."
			);
			";

			$aseco->db->query($query);
			if ($aseco->db->affected_rows === -1) {
				if ($aseco->debug) {
					trigger_error('[Playlist] addMapToHistory(): Could not add Map into table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
				}
				return false;
			}
			else {
				return true;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function isMapInHistoryById ($id) {
		if (!empty($uid)) {
			foreach ($this->history as $item) {
				if ($item['id'] == $id) {
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

	public function isMapInHistoryByUid ($uid) {
		if (!empty($uid)) {
			foreach ($this->history as $item) {
				if ($item['uid'] == $uid) {
					return true;
				}
			}
		}
		return false;
	}
}

?>
