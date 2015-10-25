<?php
/*
 * Class: Locales
 * ~~~~~~~~~~~~~~
 * » Provides multilanguage support
 *
 * ----------------------------------------------------------------------------------
 * Author:	askuri
 * Date:	2015-09-08
 * Copyright:	2015 Martin Weber (askuri)
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

class Locales {
	private static $playerlang_cache = array();
	private static $translations;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$aseco->registerEvent('onPlayerConnect', array($this, 'onPlayerConnect'));
		$aseco->registerEvent('onPlayerDisconnect',  array($this, 'onPlayerDisconnect'));

		foreach (glob('locales/*.xml') as $filename) {
			$plugin = basename($filename, '.xml'); // filename without .xml is the plugin identification
			self::$translations[$plugin] = $aseco->parser->xmlToArray($filename, true, true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Is also called on startup. Needed to use this event, because on onSync $aseco->server->players->player_list is still empty (grrr!!)
	public function onPlayerConnect ($aseco, $player) {
		self::$playerlang_cache[$player->login] = $player->language;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {
		unset(self::$playerlang_cache[$player->login]); // caching his language not needed anymore
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Wrapper
	public function getMessage ($msg, $login) {
		return $this->_($msg, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Translate a string
	 * $msg must be formatted this way: #locales:your_file_with_translations:id_that_identifies_your_message_in_xml
	 * .xml is automatically added
	 */
	public static function _ ($msg, $login) {
		@list($locales_check, $msg_file, $msg_id) = explode(':', $msg, 3);

		$locales_check	= strtoupper($locales_check);
		$msg_file	= strtolower($msg_file);
		$msg_id		= strtoupper($msg_id);

		if ($locales_check == '#LOCALES') {
			$msg_file_array = self::$translations[$msg_file]['LOCALES'];

			if ($msg_file_array[$msg_id][0]['EN'][0]) {
				$lang = self::$playerlang_cache[$login];
				$lang = 'DE';
				$translation = $msg_file_array[$msg_id][0][strtoupper($lang)][0];
				if ($translation) {
					return $translation;
				}
				else {
					return $msg_file_array[$msg_id][0]['EN'][0];
				}
			}
			else {
				trigger_error('[LOCALES] No text/translation found for '. $msg_id .' in '. $msg_file .'.xml!', E_USER_WARNING);
			}
		}

		return $msg; // no translation requested, return original message
	}
}

?>
