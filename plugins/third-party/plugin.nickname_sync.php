<?php
/*
 * Plugin: Nickname Sync
 * ~~~~~~~~~~~~~~~~~~~~~
 * » Keeps the Player Nicknames from the database in sync with the dedimania records.
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

	// Start the plugin
	$_PLUGIN = new PluginNicknameSync();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginNicknameSync extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('.anDy');
		$this->setVersion('1.2.1');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2011 - 2018 by undef.de');
		$this->setDescription('Keeps the Player Nicknames from the database in sync with the dedimania records.');

		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',			Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.0';
		if (defined('UASECO_VERSION')) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[NicknameSync] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[NicknameSync] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onDedimaniaRecordsLoaded ($aseco, $records) {

		// Sync Nicknames
		if (count($records) > 0) {
			$this->comparePlayers($records, 'Dedimania', 'Login', 'NickName');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function comparePlayers ($publics, $database, $keyLogin, $keyNick) {
		global $aseco;

		$playersToUpdate = array();
		$playersUnknownPublic = array();
		foreach ($publics as &$public) {

			// Check if Player is currently on Server
			if (isset($aseco->server->players->player_list[$public[$keyLogin]])) {
				// Player is online, no action required
				continue;
			}

			// Search Nickname of Player in Local Records
			foreach ($aseco->plugins['PluginLocalRecords']->records->record_list as &$local) {
				if ($local->player->login === $public[$keyLogin]) {
					// Check for different Nickname
					if ($local->player->nickname !== $public[$keyNick]) {
						// Update Nickname in local Records (impermanent, only for showing new Nickname in Widgets)
						$local->player->nickname = $public[$keyNick];

						// Add it to the update list to make this Nickname permament
						$playersToUpdate[$public[$keyLogin]] = $public[$keyNick];
					}
					continue 2;
				}
			}

			// Nickname not found, cache it for database search
			$playersUnknownPublic[$public[$keyLogin]] = $public[$keyNick];
			continue;
		}
		unset($public, $local);

		if (count($playersUnknownPublic) > 0) {

			// Check if Players exists on database
			$playersKnownLocal = $this->getPlayers($playersUnknownPublic);

			foreach ($playersKnownLocal as $login => &$nick) {
				// Check for different Nickname
				if ($playersUnknownPublic[$login] !== $nick) {
					// Add it to update list to make it permament
					$playersToUpdate[$login] = $playersUnknownPublic[$login];
				}
			}
			unset($playersKnownLocal, $nick);
		}
		unset($playersUnknownPublic);

		// Update all new Nicknames to Databases
		$this->updatePlayers($playersToUpdate, $database);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updatePlayers ($players, $database) {
		global $aseco;

		if (count($players) > 0) {
			$aseco->console('[NicknameSync] Update Nicknames of ['. implode(', ', array_keys($players)) .'] from '. $database);

			foreach ($players as $login => &$nick) {
				// Update Player Nickname
				$query = "
				UPDATE `%prefix%players`
				SET
					`Nickname` = ". $aseco->db->quote($nick) ."
				WHERE `Login` = ". $aseco->db->quote($login) ."
				LIMIT 1;
				";

				$result = $aseco->db->query($query);
				if ($aseco->db->affected_rows === -1) {
					trigger_error('[NicknameSync] Could not update Player from '. $database .' Database! ('. $aseco->db->errmsg() .') for statement ['. $query .']', E_USER_WARNING);
				}
			}
			unset($nick);
		}
		else {
			$aseco->console('[NicknameSync] No update of Nicknames required from '. $database);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayers ($players) {
		global $aseco;

		// Find Players from Players-Array in Database
		$players = array();
		$query = "
		SELECT
			`Login`,
			`Nickname`
		FROM `%prefix%players`
		WHERE `Login` IN ('". implode("','", array_keys($players)) ."');
		";

		$result = $aseco->db->query($query);
		if ($result) {
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$players[$row['Login']] = $row['Nickname'];
				}
			}

			// Free up the results
			$result->free_result();
		}

		return $players;
	}
}

?>
