<?php
/*
 * Plugin: Playlist
 * ~~~~~~~~~~~~~~~~
 * » Provides and handles a Playlist for Maps.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-07-07
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
 *  - includes/core/playlist.class.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginPlaylist();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginPlaylist extends Plugin {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides and handles a Playlist for Maps.');

		// Register functions for events
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onEndMap',			'onEndMap');
//		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {

	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {
//		if ($params['Action'] == 'AddMapToPlaylist') {
//			$aseco->dump('AddMapToPlaylist', $login, $params);
//		}
//		else if ($params['Action'] == 'RemoveMapFromPlaylist') {
//			$aseco->dump('RemoveMapFromPlaylist', $login, $params);
//		}
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addMapToPlaylist ($uid, $login, $method, $first_position = false) {
		global $aseco;

		$map = $aseco->server->maps->getMapByUid($uid);

		try {
			// Set the next Map
			$aseco->client->query('ChooseNextMap', $map->filename);
		}
		catch (Exception $exception) {
			$aseco->console('[Playlist] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ChooseNextMap');
		}

	}
}

?>
