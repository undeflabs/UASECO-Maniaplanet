<?php

/*
 * Class: Message
 * ~~~~~~~~~~~~~~
 * » Part of multilanguage support. Also able to sendChat 
 *
 * ----------------------------------------------------------------------------------
 * Author:	askuri
 * Date:	2015-10-27
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

class Message {
	
	private $translations = array();
	private $placeholders = false;
	
	public function __construct ($file, $id) {
		global $aseco;
		
		$this->translations = $aseco->locales->getAllTranslations($file, $id);
		if (!$this->translations) return false;
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function addPlaceholders($unused) {
		$args = func_get_args();
		
		foreach ($args as $placeholder) {
			$this->placeholders[] = $placeholder;
		}
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	// applies formatText from helper.class.php on the correct translation
	private function replacePlaceholders ($text) {
		global $aseco;
		
		if ($this->placeholders === false) {
			// no formatting needed, just return the text in the correct language
			return $text;
		}
		
		$result = call_user_func_array (
			array($aseco, 'formatText'), // the function $aseco->formatText
			array_merge(array($text), $this->placeholders) // its params
		);
		
		return $result;
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	private function chooseTranslation ($lang) {
		if ($this->translations[$lang]) {
			// translation in player's language available, return it
			return $this->translations[$lang];
		}
		else {
			return $this->translations['en']; // English is default
		}
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	public function finish ($login) {
		global $aseco;
		
		return $this->replacePlaceholders($this->chooseTranslation($aseco->locales->getPlayerLanguage($login)));
	}
	
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	
	// $logins is a comma separated list
	public function toChat ($logins = null) {
		global $aseco;
		
		$message_array = array();
		foreach ($this->translations as $lang => $text) {
			if ($lang != 'en') {
				$message_array[] = array('Lang' => $lang,
										 'Text' => $aseco->handleSpecialChars($aseco->formatColors($this->replacePlaceholders($this->chooseTranslation($lang))))
										);
			}
		}
		
		// adding English to the end, because the last one is default
		$message_array[] = array('Lang' => 'en',
								 'Text' => $aseco->handleSpecialChars($aseco->formatColors($this->replacePlaceholders($this->chooseTranslation('en'))))
								);
		
		$aseco->client->query('ChatSendServerMessageToLanguage', $message_array, $aseco->cleanupLoginList($logins));
	}
}

?>
