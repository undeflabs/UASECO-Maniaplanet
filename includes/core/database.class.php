<?php
/*
 * Class: Database
 * ~~~~~~~~~~~~~~~
 * » Provides access to a MySQL- or MariaDB-Server over the mysqli extension.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2017-08-18
 * Copyright:	2014 - 2017 by undef.de
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

class Database extends mysqli {
	public $debug			= false;
	public $settings		= array();
	public $table_prefix		= 'uaseco_';
	public $type			= 'MySQL';
	public $version			= '0.0.0';
	public $version_full		= '0.0.0';

	// $placeholder will be replaced with $this->table_prefix at each query
	private $placeholder		= '%prefix%';

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
		if ( !$this->autocommit($setup['autocommit']) ) {
			trigger_error('[MySQL] Could not set autocommit to '. (($setup['autocommit']) ? 'true' : 'false') .'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET character_set_client = "'. $setup['charset'] .'";') ) {
			trigger_error('[MySQL] Could not "SET character_set_client \''. $setup['charset'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET character_set_results = "'. $setup['charset'] .'";') ) {
			trigger_error('[MySQL] Could not "SET character_set_results \''. $setup['charset'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET character_set_connection = "'. $setup['charset'] .'";') ) {
			trigger_error('[MySQL] Could not "SET character_set_connection \''. $setup['charset'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}
		if ( !$this->query('SET collation_connection = "'. $setup['collate'] .'";') ) {
			trigger_error('[MySQL] Could not "SET collation_connection \''. $setup['collate'] .'\'": ['. $this->errno .'] '. $this->error, E_USER_ERROR);
		}

		$this->debug = $setup['debug'];
		if (!empty($setup['table_prefix'])) {
			$this->table_prefix = $setup['table_prefix'];
		}


		// Get the settings of the database
		$res = $this->query('SHOW VARIABLES;');
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_object()) {
					$this->settings[$row->Variable_name] = $row->Value;
				}
			}
			$res->free_result();
		}

		// Create the version strings
		$this->version_full = $this->settings['version'];

		// Examples:
		// MySQL:	"5.7.16", "5.7.17-0ubuntu1"
		// MariaDB:	"10.0.27-MariaDB-0ubuntu0.16.04.1"
		$this->version = explode('-', $this->version_full);
		if (isset($this->version[0]) && !empty($this->version[0])) {
			$this->version = $this->version[0];
		}
		else {
			$this->version = $this->version_full;
		}

		// Get the type of the database
		if (mb_stripos($this->version_full, 'mariadb') !== false) {
			$this->type = 'MariaDB';
		}
//		else if (mb_stripos($this->version_full, 'percona') !== false) {
//			$this->type = 'Percona-Server';
//		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __destruct () {
		$this->disconnect();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function query ($sql, $resultmode = MYSQLI_STORE_RESULT) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
		return parent::query($sql, $resultmode);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function multi_query ($sql) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
		return parent::multi_query($sql);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function real_query ($sql) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
		return parent::real_query($sql);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function send_query ($sql) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
		return parent::send_query($sql);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function prepare ($sql) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
		return parent::prepare($sql);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function select_one ($sql) {
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
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
		$sql = str_replace($this->placeholder, $this->table_prefix, $sql);
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
		$table = str_replace($this->placeholder, $this->table_prefix, $table);
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
		if (isset($this) && is_object($this) && $this instanceof Database && $this->stat() !== false) {
			$this->close();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function column_schema ($table) {

		$table = str_replace($this->placeholder, $this->table_prefix, $table);
		$schema = array();
		$query = "
		SELECT
			`column_name`,
			`data_type`,
			`character_maximum_length`
		FROM `information_schema`.`columns`
		WHERE `table_name` = '". $this->real_escape_string($table) ."';
		";

		if ($result = $this->query($query)) {
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$schema[$row['column_name']]['type']	= $row['data_type'];
					$schema[$row['column_name']]['length']	= $row['character_maximum_length'];
				}
			}
			$result->free_result();
		}
		return $schema;
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
			return $this->type .'/'. $this->version_full;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function client_version () {
		if ($this->stat() !== false) {
			// Example: mysqlnd 5.0.10 - 20111026 - $Id: c85105d7c6f7d70d609bb4c000257868a40840ab $
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
