<?php
/*
 * Class: Database
 * ~~~~~~~~~~~~~~~
 * » Provides access to a MySQL-Server over the mysqli extension.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-21
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

class Database extends mysqli {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($setup) {

		// Connect to the DB
		parent::__construct($setup['host'], $setup['login'], $setup['password'], $setup['database']);
		if ($this->connect_error) {
			trigger_error('[MySQL] Could not authenticate at MySQL server: ['. $this->connect_errno .'] '. $this->connect_error, E_USER_ERROR);
		}
		if ( !$this->query('SET AUTOCOMMIT='. $setup['autocommit'] .';') ) {
			trigger_error('[MySQL] Could not "SET AUTOCOMMIT='. $setup['autocommit'] .'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET NAMES "'. $setup['charset_keys'] .'" COLLATE "'. $setup['charset_collate'] .'";') ) {
			trigger_error('[MySQL] Could not "SET NAMES \''. $setup['charset_keys'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET CHARACTER SET "'. $setup['charset_values'] .'";') ) {
			trigger_error('[MySQL] Could not "SET CHARACTER SET \''. $setup['charset_values'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		$this->debug = $setup['debug'];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __destruct() {
		$this->disconnect();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function select_one ($sql) {
		if ($res = $this->query($sql)) {
			if ($res->num_rows > 0) {
				$row = $res->fetch_array(MYSQLI_ASSOC);
				$res->free_result();
				return $row;
			}
			else {
				if ($this->errno) {
					trigger_error('[MySQL] Error ('. $this->errno .') "'. $this->error .'" for statement ["'. $sql = trim( preg_replace('/\s+/', ' ', $sql) ) .'"]', E_USER_WARNING);
				}
				$res->free_result();
				return null;
			}
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

	public function select_all ($sql) {
		if ($res = $this->query($sql)) {
			if ($res->num_rows > 0) {
				$data = array();
				while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
					$data[] = $row;
				}
				$res->free_result();
				return $data;
			}
			else {
				if ($this->errno) {
					trigger_error('[MySQL] Error ('. $this->errno .') "'. $this->error .'" for statement ["'. $sql = trim( preg_replace('/\s+/', ' ', $sql) ) .'"]', E_USER_WARNING);
				}
				$res->free_result();
				return null;
			}
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

	public function quote ($string) {
		return "'". $this->real_escape_string($string) ."'";
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function escape ($string) {
		return $this->real_escape_string($string);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function list_table_columns ($table) {
		$fieldnames = array();
		if ($result = $this->query("SHOW COLUMNS FROM `". $this->real_escape_string($table) ."`;")) {
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_object()) {
					$fieldnames[] = $row->Field;
				}
			}
			$result->free_result();
		}
		return $fieldnames;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function lastid () {
		return $this->insert_id;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function errmsg () {
		return '('. $this->errno .') '. $this->error;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function disconnect () {
		if ($this->stat() !== false) {
			$this->close();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function host_status () {
		if ($this->stat() !== false) {

			$status = $this->stat();
			$status = str_replace(
				array(
					'Uptime: ',
					'Threads: ',
					'Questions: ',
					'Slow queries: ',
					'Opens: ',
					'Flush tables: ',
					'Open tables: ',
					'Queries per second avg: ',
				),
				array(
					'Uptime:_',
					'Threads:_',
					'Questions:_',
					'Slow_queries:_',
					'Opens:_',
					'Flush_tables:_',
					'Open_tables:_',
					'Queries_per_second_avg:_',
				),
				$status
			);
			$status = str_replace('  ', '; ', $status);
			$status = str_replace('_', ' ', $status);

			// Format "Uptime"
			$status = preg_replace_callback(
				'#Uptime: (\d+?);#i',
				function ($matches) {
					return 'Started: '. date('Y-m-d H:i', time() - $matches[1]) .';';
				},
				$status
			);

			return $status;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function server_version () {
		if ($this->stat() !== false) {
			// 5.5.36
			return 'MySQL/'. $this->server_info;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function client_version () {
		if ($this->stat() !== false) {
			// mysqlnd 5.0.10 - 20111026 - $Id: c85105d7c6f7d70d609bb4c000257868a40840ab $
			$client_info = $this->get_client_info();
			$client_info = explode(' - ', $client_info);

			return str_replace(' ', '/', $client_info[0]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function connection_info () {
		if ($this->stat() !== false) {
			return $this->host_info;
		}
	}
}

?>
