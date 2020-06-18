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
		$this->setCoAuthors('undef.de', 'aca');
		$this->setVersion('1.0.5');
		$this->setBuild('2019-09-24');
		$this->setCopyright('2014 - 2019 by Martin Weber (askuri)');
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
	/**
	 *	Set content for the placeholders
	 *
	 *	@author	askuri <askuri@uaseco.org>
	 *	@param mixed $unused 	String or Message-Object or Message-Object-Array
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
	 * Takes the messages of the Array, lets them get translated via finish() and concatenates them to a single String
	 *
	 * @param 		String $lang 					language-code
	 * @param 		Message[] $placeholderArray		Array with Message-Objects
	 * @return 		String							the concatenated messages
	 *
	 */

	private function finishPlaceholderArray($lang, $placeholderArray){
		$message = '';
		foreach($placeholderArray as $placeholder){
			if($placeholder instanceof Message){
				$message .= $placeholder->finish($lang, false);
			}
		}
		return $message;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Returns the translated message. (If no translation is available, returns the English message)
	 *
	 * @param 		String $id 			login of a player or language-code
	 * @param 		boolean $is_login 	false if language-code is given
	 * @return 		String
	 *
	 */

	public function finish ($id, $is_login = true) {
		global $aseco;

		//first parameter is login
		if ($is_login === true) {
			$lang = $aseco->locales->getPlayerLanguage($id);
		}
		//first parameter is language-code
		else {
			$lang = $id;
		}

		$message = $aseco->formatColors($this->chooseTranslation($lang));
		$message = str_replace('»', $aseco->settings['chat_prefix_replacement'], $message);
		$message = preg_replace("/(\n{#.*?})»/", '${1}'.$aseco->settings['chat_prefix_replacement'], $message, 1);
		$message = str_replace('{br}', LF, $message);									// Replace {br}'s with real LF

		// Placeholders
		if ($this->placeholders !== false) {
			foreach($this->placeholders as &$placeholder) {
				if ($placeholder instanceof Message) {
					$placeholder = $placeholder->finish($lang, false);
				}
				else if(is_array($placeholder)){
					$placeholder = $this->finishPlaceholderArray($lang, $placeholder);
				}
			}
			$message = call_user_func_array(
				array($aseco, 'formatText'),									// The function $aseco->formatText
				array_merge(array($message), $this->placeholders)						// Its params
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
	 * Returns the translated message as an array, splitted by {br}. (If no translation is available, returns it in English)
	 *
	 * @author	askuri <askuri@uaseco.org>
	 * @param 	String $login 	login of a player
	 * @return 	String
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
	 * @param	string $logins 	a comma separated list
	 */

	public function sendChatMessage ($logins = null) {
		global $aseco;

		$messages = array();
		foreach ($this->translations as $lang => $text) {
			$text = str_replace('»', $aseco->settings['chat_prefix_replacement'], $text);
			$text = preg_replace("/(\n{#.*?})»/", '${1}'.$aseco->settings['chat_prefix_replacement'], $text, 1);
			$text = str_replace('{br}', LF, $text);									// Replace {br}'s with real LF
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
