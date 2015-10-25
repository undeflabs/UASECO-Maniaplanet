<?php
/*
 * Class: MapHistory
 * ~~~~~~~~~~~~~~~~~
 * » Map history for the dedicated server and provides several methods for the required
 *   handling of the history.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-09-19
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

class MapHistory {
	public $settings	= array();
	public $map_list	= array();

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
			`Date`
		FROM `%prefix%maphistory`
		ORDER BY `Date` DESC;
		";

		$result = $aseco->db->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				$this->map_list = array();

				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$map = $aseco->server->maps->getMapById($row['MapId']);
					if (isset($map->id) && $map->id > 0) {
						$this->map_list[$map->id] = array(
							'played'	=> $row['Date'],
							'id'		=> $map->id,
							'uid'		=> $map->uid,
						);
					}

					if (count($this->map_list) >= $this->settings['max_history_entries']) {
						break;
					}
				}

				// Clean up the MapHistory table
				$aseco->db->begin_transaction();		// Require PHP >= 5.5.0
				$query = "TRUNCATE TABLE `%prefix%maphistory`;";

				$aseco->db->query($query);
				if ($aseco->db->affected_rows === 0) {
					$values = array();
					foreach ($this->map_list as $item) {
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

						$result2 = $aseco->db->query($query);
						if ($aseco->db->affected_rows === -1) {
							if ($aseco->debug) {
								trigger_error('[MapHistory] readMapHistory(): Could not clean up table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
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
				". $aseco->db->quote(date('Y-m-d H:i:s', time())) ."
			);
			";

			$aseco->db->query($query);
			if ($aseco->db->affected_rows === -1) {
				if ($aseco->debug) {
					trigger_error('[MapHistory] addMapToHistory(): Could not add Map into table "maphistory": ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
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

	public function getPreviousMapFromHistory () {
		global $aseco;

		$last = $this->map_list;
		$last = array_shift($last);

		$map = $aseco->server->maps->getMapByUid($last['uid']);
		if (isset($map->id) && $map->id > 0) {
			return $map;
		}
		return new Map(null, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function isMapInHistoryById ($id) {
		if (!empty($id)) {
			foreach ($this->map_list as $item) {
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
			foreach ($this->map_list as $item) {
				if ($item['uid'] == $uid) {
					return true;
				}
			}
		}
		return false;
	}
}

?>
