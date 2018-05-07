<?php
/*
 * Class: Message
 * ~~~~~~~~~~~~~~
 * » Part of multilanguage support.
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

class Message extends BaseClass {
	public $translations = array();
	public $placeholders = false;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($file, $id) {
		global $aseco;

		$this->setAuthor('askuri');
		$this->setCoAuthors('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by Martin Weber (askuri)');
		$this->setDescription('Part of multilanguage support.');

		$this->translations = $aseco->locales->getAllTranslations(strtolower($file), strtolower($id));
		// if the line above fails, this will return false
		return $this->translations;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addPlaceholders ($unused) {

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
/*
	// Applies formatText from helper.class.php on the correct translation
	private function replacePlaceholders ($text, $id) {
		global $aseco;

		if ($this->placeholders === false) {
			return $text;						// No formatting required, just return the text in the correct language
		}

		if ($text instanceof Message) {
			$text = $text->finish($login, false);
		}

		return call_user_func_array(
			array($aseco, 'formatText'),				// The function $aseco->formatText
			array_merge(array($text), $this->placeholders)		// Its params
		);
	} */

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function chooseTranslation ($lang) {
		if (isset($this->translations[$lang])) {
			return $this->translations[$lang];			// Translation in Player's language available, return it
		}
		else {
			return $this->translations['en'];			// English is default
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Takes the result of finish() and splits it by {br} to an array
	 *
	 * @author	askuri <askuri@uaseco.org>
	 * @param	string $id a string
	 * @return	array List of xxx
	 */
	public function finish ($id, $is_login = true) {
		global $aseco;

		if ($is_login === true) {
			$lang = $aseco->locales->getPlayerLanguage($id); 			// login was given, get his language
		}
		else {
			$lang = $id;								// language given
		}

		$message = $aseco->formatColors($this->chooseTranslation($lang));

		// Placeholders
		if ($this->placeholders !== false) {
			foreach($this->placeholders as &$placeholder) {
				if ($placeholder instanceof Message) {
					$placeholder = $placeholder->finish($lang, false);
				}
			}

			$message = call_user_func_array(
				array($aseco, 'formatText'),					// The function $aseco->formatText
				array_merge(array($message), $this->placeholders)		// Its params
			);
		}
		return $aseco->decodeEntities($message, $lang);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Gets the language of the player, chooses the correct translation, replaces variables and returns the message
	 *
	 * @author	askuri <askuri@uaseco.org>
	 * @param	string $login A Player login
	 * @return	array List of xxx
	 */
	public function finishMultiline ($login) {
		global $aseco;

		return explode('{br}', $this->finish($login));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Sends the multilanguage chat message to the chat
	 *
	 * @author	askuri <askuri@uaseco.org>
	 * @param	string $logins A comma separated list
	 */
	public function sendChatMessage ($logins = null) {
		global $aseco;

		$messages = array();
		foreach ($this->translations as $lang => $text) {
			$text = preg_replace('/»/', $aseco->getChatMessage('CHAT_PREFIX_REPLACEMENT'), $text, 1);
			$text = preg_replace("/(\n{#.*?})»/", '${1}'.$aseco->getChatMessage('CHAT_PREFIX_REPLACEMENT'), $text, 1);
			if ($lang !== 'en') {
				// Replace all entities back to normal for chat.
				// $text = $aseco->decodeEntities($this->replacePlaceholders($this->chooseTranslation($lang), $lang));
				$text = $this->finish($lang, false);
				$messages[] = array(
					'Lang' => $lang,
					'Text' => $aseco->formatColors($text),
				);
			}
		}

		// Adding english to the end, because the last one is default
		// $text = $aseco->decodeEntities($this->replacePlaceholders($this->chooseTranslation('en'), 'en'));		// Replace all entities back to normal for chat.
		$text = $this->finish('en', false);
		$text = preg_replace('/»/', $aseco->getChatMessage('CHAT_PREFIX_REPLACEMENT'), $text, 1);
		$text = preg_replace("/(\n{#.*?})»/", '${1}'.$aseco->getChatMessage('CHAT_PREFIX_REPLACEMENT'), $text, 1);
		$messages[] = array(
			'Lang' => 'en',
			'Text' => $aseco->formatColors($text)
		);

		try {
			$aseco->client->query('ChatSendServerMessageToLanguage', $messages, $aseco->cleanupLoginList($logins));
		}
		catch (Exception $exception) {
			$aseco->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - sendChatMessage(): ChatSendServerMessageToLanguage');
		}
	}
}

?>
