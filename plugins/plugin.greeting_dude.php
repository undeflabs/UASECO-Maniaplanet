<?php
/*
 * Plugin: Greeting Dude
 * ~~~~~~~~~~~~~~~~~~~~
 * » Automated greeting robot for new connected Players.
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Version:		1.0.0
 * Date:		2015-01-20
 * Copyright:		2012 - 2015 by undef.de
 * System:		UASECO/0.9.5+
 * Game:		ManiaPlanet Trackmania2 (TM2)
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
 *  -
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginGreetingDude();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginGreetingDude extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Automated greeting robot for new connected Players.');

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerConnect1',	'onPlayerConnect1');

		$this->registerChatCommand('dudereload',	'chat_dudereload',	'Reload the "Greeting Dude" settings.',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.5';
		if ( defined('UASECO_VERSION') ) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[GreetingDude] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[GreetingDude] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}


		// Read Configuration
		if (!$xml = $aseco->parser->xmlToArray('config/greeting_dude.xml', true, true)) {
			trigger_error('[LazyButtons] Could not read/parse config file "config/greeting_dude.xml"!', E_USER_ERROR);
		}
		$this->config = $xml['SETTINGS'];


		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['ONLY_PERSONAL_GREETINGS'][0]	= ((strtoupper($this->config['ONLY_PERSONAL_GREETINGS'][0]) == 'TRUE')	? true : false);
		$this->config['PUBLIC_GREETINGS'][0]		= ((strtoupper($this->config['PUBLIC_GREETINGS'][0]) == 'TRUE')		? true : false);


		// Build the array of personal greetings for special Players
		$this->config['PersonalMessages'] = array();
		foreach ($this->config['PLAYERS'][0]['PLAYER'] as &$item) {
			foreach (explode('|', $item['LOGIN'][0]) as $login) {
				$this->config['PersonalMessages'][$login] = $item['GREETING'][0];
			}
		}
		unset($item);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect1 ($aseco, $player) {

		$message = false;
		$nickname = '{#highlite}'. $this->handleSpecialChars($player->nickname) .'$Z'. $this->config['TEXT_FORMATTING'][0];
		if ( isset($this->config['PersonalMessages'][$player->login]) ) {
			// Setup the personal greeting
			$message = $this->config['GREETER_NAME'][0] .'$Z '. $this->config['TEXT_FORMATTING'][0] . $this->config['PersonalMessages'][$player->login];
			$message = str_replace('{nickname}', $nickname, $message);
		}
		else if ($this->config['ONLY_PERSONAL_GREETINGS'][0] == false) {
			// Setup the global greeting
			$message = $this->config['GREETER_NAME'][0] .'$Z '. $this->config['TEXT_FORMATTING'][0] . $this->config['MESSAGES'][0]['GREETING'][rand(0,count($this->config['MESSAGES'][0]['GREETING'])-1)];
			$message = str_replace('{nickname}', $nickname, $message);
		}
		if ($message != false) {
			if ($this->config['PUBLIC_GREETINGS'][0] == true) {
				$aseco->sendChatMessage($message, false);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dudereload ($aseco, $login, $chat_command, $chat_parameter) {

		// Reload the "greeting_dude.xml"
		$this->onSync($aseco);

		$message = '{#admin}>> Reload of the configuration "greeting_dude.xml" done.';
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	function handleSpecialChars ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $S (shadow)
		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[SHWILON]/i', '', $string);

		$string = $aseco->stripNewlines($string);

		return $aseco->validateUTF8String($string);
	}
}

?>
