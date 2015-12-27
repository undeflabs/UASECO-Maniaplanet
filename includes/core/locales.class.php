<?php
/*
 * Class: Locales
 * ~~~~~~~~~~~~~~
 * » Provides multilanguage support
 *
 * ----------------------------------------------------------------------------------
 * Author:	askuri
 * Date:	2015-11-11
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
	private $aseco;
	private $playerlang_cache = array();				// Stores which player speaks which language | struct: [login => language, login2 => language2, ...]
	private $locales;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$aseco->registerEvent('onPlayerConnect',	array($this, 'onPlayerConnect'));
		$aseco->registerEvent('onPlayerDisconnect',	array($this, 'onPlayerDisconnect'));

		foreach (glob('locales/*.xml') as $filename) {
			$plugin = basename($filename, '.xml');			// Filename without .xml is the plugin identification

			$xml = simplexml_load_file($filename);
			if (!$xml) {
				trigger_error('[LOCALES] Unable to parse '. $filename. '! Please check its syntax and the encoding (has to be UTF8!)', E_USER_ERROR);
			}

			$this->locales[$plugin] = json_decode(json_encode($xml), true); // Read with simplexml and use a trick to convert it to an array
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Is also called on startup. Needed to use this event, because on onSync $aseco->server->players->player_list is still empty
	public function onPlayerConnect ($aseco, $player) {
		$this->playerlang_cache[$player->login] = strtolower($player->language);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {
		unset($this->playerlang_cache[$player->login]);			// Caching his language not needed anymore
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns an array of all languages, spoken by current players on the server
	public function getOnlinePlayerLanguages () {
		return array_values($this->locales->playerlang_cache);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getSingleTranslation ($sourcefile, $id, $lang) {
		return $this->locales[$sourcefile][$id][$lang];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getAllTranslations ($sourcefile, $id) {
		return $this->locales[$sourcefile][$id];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerLanguage ($login) {
		return $this->playerlang_cache[$login];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Used when outputting messages. Sadly not every message is made with Message class, some are still strings
	// this function ensures backwardcompatibility and avoids errors
	public function handleMessage ($message, $login) {
		if ($message instanceof Message) { // Is Message object?
			return $message->finish($login);
		}
		else if (is_string($message)) {
			return $message;
		}
		else {
			trigger_error('[LOCALES] handleMessage() is unable to handle the following message due to an invalid datatype:', E_USER_WARNING);
		}
	}
}

?>
