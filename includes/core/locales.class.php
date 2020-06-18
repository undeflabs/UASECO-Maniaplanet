<?php
/*
 * Class: Locales
 * ~~~~~~~~~~~~~~
 * » Provides multilanguage support.
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

class Locales extends BaseClass {
	public $playerlang_cache = array();				// Stores which player speaks which language | struct: [login => language, login2 => language2, ...]
	public $locales;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$this->setAuthor('askuri');
		$this->setCoAuthors('undef.de');
		$this->setVersion('1.0.4');
		$this->setBuild('2019-09-25');
		$this->setCopyright('2014 - 2019 by Martin Weber (askuri)');
		$this->setDescription('Provides multilanguage support.');

		$aseco->registerEvent('onPlayerConnect',	array($this, 'onPlayerConnect'));
		$aseco->registerEvent('onPlayerDisconnect',	array($this, 'onPlayerDisconnect'));

		foreach (glob('locales/*/*.xml') as $filename) {
			$plugin = basename($filename, '.xml');			// Filename without .xml is the plugin identification
			$language = str_replace('locales/', '', dirname($filename));

			$xml = simplexml_load_file($filename);
			if (!$xml) {
				trigger_error('[ClassLocales] Unable to parse '. $filename. '! Please check its syntax and the encoding (has to be UTF8!)', E_USER_ERROR);
			}

			// Remove comments
			unset($xml->comment);

			// Read with simplexml and use a trick to convert it to an array
			$this->locales[$plugin][$language] = json_decode(json_encode($xml), true);
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

	public function getSingleTranslation ($sourcefile, $id, $language) {
		global $aseco;

		if (isset($this->locales[$sourcefile]) && isset($this->locales[$sourcefile][$language]) && isset($this->locales[$sourcefile][$language][$id])) {
			return $this->locales[$sourcefile][$language][$id];
		}
		else {
			$aseco->console('[ClassLocales][ERROR] getSingleTranslation(): Translation file [locales/'. $language .'/'. $sourcefile .'.xml] does not contain the requested entry <'. $id .'>!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getAllTranslations ($sourcefile, $id) {
		global $aseco;

		if (isset($this->locales[$sourcefile])) {
			$translations = array();
			foreach ($this->locales[$sourcefile] as $language => $item) {
				if (isset($this->locales[$sourcefile][$language][$id])) {
					$translations[$language] = $this->locales[$sourcefile][$language][$id];
				}
			}
			return $translations;
		}
		else {
			$aseco->console('[ClassLocales][ERROR] getAllTranslations(): Translation file ['. $sourcefile .'.xml] does not exist in any language!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerLanguage ($login) {
		if (is_string($login) && isset($this->playerlang_cache[$login])) {
			return $this->playerlang_cache[$login];
		}
		else {
			return 'en';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Used when outputting messages. Sadly not every message is made with Message class, some are still strings
	// this function ensures backwardcompatibility and avoids errors
	public function handleMessage ($message, $login) {
		if ($message instanceof Message) {				// Is Message object?
			return $message->finish($login);
		}
		else if (is_string($message)) {
			return $message;
		}
		else {
			trigger_error('[ClassLocales] handleMessage(): unable to handle the following message due to an invalid datatype:', E_USER_WARNING);
		}
	}
}

?>
