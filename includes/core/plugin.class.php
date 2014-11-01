<?php
/*
 * Class: Plugin
 * ~~~~~~~~~~~~~
 * » Structure for all plugins, extend this class to build your own one.
 * » Based upon plugin.class.php from ASECO/2.2.0c
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
 *  - includes/core/dependence.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

abstract class Plugin {
	private $author		= 'Unknown';
	private $version	= null;
	private $filename	= 'Unknown';
	private $description	= 'No description';
	private $events		= array();
	private $chat_commands	= array();
	private $dependencies	= array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setVersion ($version) {
		$this->version = $version;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getVersion () {
		return $this->version;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setFilename ($filename) {
		$this->filename = $filename;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getFilename () {
		return $this->filename;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setAuthor ($author) {
		$this->author = $author;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getAuthor () {
		return $this->author;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setDescription ($description) {
		$this->description = $description;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDescription () {
		return $this->description;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getClassname () {
		return get_class($this);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDependencies () {
		return $this->dependencies;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addDependence ($plugin, $permissions = Dependence::REQUIRED, $min_version = null, $max_version = null) {
		$this->dependencies[] = new Dependence($plugin, $permissions, $min_version, $max_version);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function registerEvent ($event, $callback_function) {
		$this->events[$event] = array($this, $callback_function);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getEvents () {
		return $this->events;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function registerChatCommand ($chat_command, $callback_function, $help, $rights = Player::PLAYERS, $params = array()) {
		$this->chat_commands[$chat_command] = array(
			'callback'	=> array($this, $callback_function),
			'help'		=> $help,
			'rights'	=> $rights,
			'params'	=> $params,
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getChatCommands () {
		return $this->chat_commands;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function storePlayerData ($player, $key, $data) {
		if (isset($player) && get_class($player) != 'Player') {
			return;
		}
		if (!empty($key) && !empty($data)) {
			$player->data[$this->getClassname()][$key] = $data;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerData ($player, $key) {
		if (isset($player) && get_class($player) != 'Player') {
			return;
		}
		if (!empty($key) && isset($player->data[$this->getClassname()][$key])) {
			return $player->data[$this->getClassname()][$key];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function removePlayerData ($player, $key) {
		if (isset($player) && get_class($player) != 'Player') {
			return;
		}
		if (!empty($key) && isset($player->data[$this->getClassname()][$key])) {
			unset($player->data[$this->getClassname()][$key]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function existsPlayerData ($player, $key) {
		if (!empty($key) && isset($player->data[$this->getClassname()][$key])) {
			return true;
		}
		return false;
	}
}

?>
