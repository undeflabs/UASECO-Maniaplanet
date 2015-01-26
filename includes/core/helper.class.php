<?php
/*
 * Class: Helper
 * ~~~~~~~~~~~~~
 * » Provides several function for use in UASECO and plugins.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-01-16
 * Copyright:	2014 - 2015 by undef.de
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
 *  - none
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Helper {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function displayLoadStatus ($message, $ratio = 0.0) {

		$xml = '<manialink id="UASECO-LoadStatus" version="1">';
		if ($message !== false && $this->startup_phase === true) {
			$xml .= '<frame posn="-44.375 60.75 0.01">';
			$xml .= '<quad posn="0 0 0.01" sizen="88.75 16.125" url="'. UASECO_WEBSITE .'" image="http://www.uaseco.org/media/uaseco/logo-uaseco.png"/>';
			$xml .= '<label posn="0 -16 0.02" sizen="125.9 10" textsize="2" scale="0.9" style="TextValueSmallSm" textcolor="FFFF" text="'. $this->handleSpecialChars($message) .'"/>';
			$xml .= '<gauge posn="0 -18 0.03" sizen="88.75 10" ratio="'. $ratio .'" style="ProgressBarSmall" drawbg="1" drawblockbg="1"/>';
			$xml .= '</frame>';
		}
		$xml .= '</manialink>';

		// Send to all connected Players
		$this->sendManialink($xml, false, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Converts [&"'<>] to HTML entities, removes "\n\n", \n" and "\r" and validates the string
	public function handleSpecialChars ($string) {

		$string = str_replace(
				array(
					'&',
					'"',
					"'",
					'>',
					'<'
				),
				array(
					'&amp;',
					'&quot;',
					'&apos;',
					'&gt;',
					'&lt;'
				),
				$string
		);
		$string = $this->stripNewlines($string);
		return $this->validateUTF8String($string);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * http://sourcecookbook.com/en/recipes/8/function-to-slugify-strings-in-php
	 * This is a function to slugify (replace non-ASCII characters with ASCII characters) strings in PHP.
	 * It tries to replace some characters like ñ or ç to a similar ASCII character (for example, it will transform a ñ to a n).
	 */
	public function slugify ($string) {
		// Replace non letter by "-"
		$string = preg_replace('#[^\\pL]+#u', '-', $string);

		// Transliterate
		if (function_exists('iconv')) {
			$string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
		}

		// Remove unwanted characters
		$string = preg_replace('#[^-\w]+#', '', $string);

		// Replace multiple "---" with single "-"
		$string = preg_replace('#-{2,}#', '-', $string);

		// Trim
		$string = trim($string, '-');

		if (empty($string)) {
			return date('Y-m-d-H-i-s') .'_unnamed';
		}
		return $string;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function reportServerInfo () {

		$amount_players = $this->server->players->count();
		$amount_spectators = 0;
		foreach ($this->server->players->player_list as $player) {
				if ($player->isspectator == 1) {
					$amount_spectators++;
				}
		}
		$amount_players = $amount_players - $amount_spectators;

		// Create Server informations
		$xml = '<?xml version="1.0" encoding="utf-8"?>'.LF;
		$xml .= '<info>'.LF;
		$xml .= ' <uaseco>'.LF;
		$xml .= '  <version>'. UASECO_VERSION .'</version>'.LF;
		$xml .= '  <build>'. UASECO_BUILD .'</build>'.LF;
		$xml .= '  <uptime>'. (time() - $this->uptime) .'</uptime>'.LF;
		$xml .= ' </uaseco>'.LF;
		$xml .= ' <dedicated>'.LF;
		$xml .= '  <version>'. $this->server->version .'</version>'.LF;
		$xml .= '  <build>'. $this->server->build .'</build>'.LF;
		$xml .= '  <uptime>'. $this->server->networkstats['Uptime'] .'</uptime>'.LF;
		$xml .= ' </dedicated>'.LF;
		$xml .= ' <server>'.LF;
		$xml .= '  <login>'. $this->server->login .'</login>'.LF;
		$xml .= '  <name>'. $this->stripColors($this->server->name) .'</name>'.LF;
		$xml .= '  <continent>'. $this->server->zone[0] .'</continent>'.LF;
		$xml .= '  <country>'. $this->server->zone[1] .'</country>'.LF;
		$xml .= '  <protected>'. ((!empty($this->server->options['Password'])) ? 'true' : 'false') .'</protected>'.LF;
		$xml .= '  <mode>'.LF;
		$xml .= '   <title>'. $this->server->title .'</title>'.LF;
		$xml .= '   <script>'.LF;
		$xml .= '    <name>'. $this->server->gameinfo->getGamemodeScriptname() .'</name>'.LF;
		$xml .= '    <version>'. $this->server->gameinfo->getGamemodeVersion() .'</version>'.LF;
		$xml .= '   </script>'.LF;
		$xml .= '  </mode>'.LF;
		$xml .= '  <players>'.LF;
		$xml .= '   <current>'. $amount_players .'</current>'.LF;
		$xml .= '   <maximum>'. $this->server->options['CurrentMaxPlayers'] .'</maximum>'.LF;
		$xml .= '  </players>'.LF;
		$xml .= '  <spectators>'.LF;
		$xml .= '   <current>'. $amount_spectators .'</current>'.LF;
		$xml .= '   <maximum>'. $this->server->options['CurrentMaxSpectators'] .'</maximum>'.LF;
		$xml .= '  </spectators>'.LF;
		$xml .= '  <ladder>'.LF;
		$xml .= '   <minimum>'. $this->server->ladder_limit_min .'</minimum>'.LF;
		$xml .= '   <maximum>'. $this->server->ladder_limit_max .'</maximum>'.LF;
		$xml .= '  </ladder>'.LF;
		$xml .= ' </server>'.LF;
		$xml .= ' <current>'.LF;
		$xml .= '  <map>'.LF;
		$xml .= '   <name>'. $this->handleSpecialChars($this->server->maps->current->name_stripped) .'</name>'.LF;
		$xml .= '   <author>'. $this->server->maps->current->author .'</author>'.LF;
		$xml .= '   <environment>'. $this->server->maps->current->environment .'</environment>'.LF;
		$xml .= '   <mood>'. $this->server->maps->current->mood .'</mood>'.LF;
		$xml .= '   <authortime>'. (($this->server->gameinfo->mode == Gameinfo::STUNTS)	? $this->server->maps->current->author_score	: $this->server->maps->current->author_time) .'</authortime>'.LF;
		$xml .= '   <goldtime>'. (($this->server->gameinfo->mode == Gameinfo::STUNTS)	? $this->server->maps->current->goldtime	: $this->server->maps->current->goldtime) .'</goldtime>'.LF;
		$xml .= '   <silvertime>'. (($this->server->gameinfo->mode == Gameinfo::STUNTS)	? $this->server->maps->current->silvertime	: $this->server->maps->current->silvertime) .'</silvertime>'.LF;
		$xml .= '   <bronzetime>'. (($this->server->gameinfo->mode == Gameinfo::STUNTS)	? $this->server->maps->current->bronzetime	: $this->server->maps->current->bronzetime) .'</bronzetime>'.LF;
		$xml .= '   <mxurl>'. str_replace('&', '&amp;', (isset($this->server->maps->current->mx->pageurl)) ? $this->server->maps->current->mx->pageurl : '') .'</mxurl>'.LF;
		$xml .= '  </map>'.LF;
//		$xml .= '  <players>'.LF;
//		foreach ($this->server->players->player_list as $player) {
//				$xml .= '   <player>'.LF;
//				$xml .= '     <nickname>'. $this->handleSpecialChars($this->stripColors($player->nickname)) .'</nickname>'.LF;
//				$xml .= '     <login>'. $player->login .'</login>'.LF;
//				$xml .= '     <zone>'. implode('|', $player->zone) .'</zone>'.LF;
//				$xml .= '     <ladder>'. $player->ladderrank .'</ladder>'.LF;
//				$xml .= '     <spectator>'. $this->bool2string($player->isspectator) .'</spectator>'.LF;
//				$xml .= '   </player>'.LF;
//		}
//		$xml .= '  </players>'.LF;
		$xml .= ' </current>'.LF;
		$xml .= '</info>'.LF;

		// Send and ignore response
		$this->webaccess->request(
			UASECO_WEBSITE .'usagereport.php',				// URL
			null,								// Callback
			$xml,								// POST data
			false,								// IsXmlRpc
			100,								// KeepaliveMinTimeout
			30,								// OpenTimeout
			40,								// WaitTimeout
			UASECO_NAME .'/'. UASECO_VERSION .' ('. UASECO_BUILD .')'	// UserAgent
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Created by Xymph: Univeral show help for user, admin & Jfreu commands.
	// $width is the width of the first column in the ManiaLink window
	public function showHelp ($aseco, $login, $chat_commands, $head, $showadmin = false, $dispall = false, $width = 0.3) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		if ($dispall) {
			// display full help

			$head = "Currently supported $head commands:";
			if (!empty($chat_commands)) {
				// define admin or non-admin padding string
				$pad = ($showadmin ? '$f00... ' : '$f00/');
				$help = array();
				$lines = 0;
				$player->msgs = array();
				$player->msgs[0] = array(1, $head, array(1.1, $width, 1.1 - $width), array('Icons64x64_1', 'TrackInfo', -0.01));
				// create list of chat commands
				foreach ($chat_commands as $name => $cc) {
					// collect either admin or non-admin commands
					$allowed = false;
					if ($showadmin == true) {
						if ($cc['rights'] & Player::OPERATORS) {
							// Chat command is only allowed for Operators, Admins or MasterAdmins
							$allowed = true;
						}
						else if ($cc['rights'] & Player::ADMINS) {
							// Chat command is only allowed for Admins or MasterAdmins
							$allowed = true;
						}
						else if ($cc['rights'] & Player::MASTERADMINS) {
							// Chat command is only allowed for MasterAdmins
							$allowed = true;
						}
						if ($allowed == true) {
							foreach ($cc['params'] as $cmd => $description) {
								$help[] = array($pad . $cmd, $description);
								if (++$lines > 14) {
									$player->msgs[] = $help;
									$lines = 0;
									$help = array();
								}
							}
						}
					}
					else {
						if ($cc['rights'] & Player::PLAYERS) {
							// Chat command is allowed for everyone
							$allowed = true;
						}
						if ($allowed == true) {
							$help[] = array($pad . $name, $cc['help']);
							if (++$lines > 14) {
								$player->msgs[] = $help;
								$lines = 0;
								$help = array();
							}
						}
					}
				}

				// add if last batch exists
				if (!empty($help))
					$player->msgs[] = $help;

				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
			}
		}
		else {
			// show plain help

			$head = "Currently supported $head commands:" . LF;
			$help = $aseco->formatColors('{#interact}' . $head);
			foreach ($chat_commands as $name => $cc) {
				// collect either admin or non-admin commands
				$allowed = false;
				if ($showadmin == true) {
					if ($cc['rights'] & Player::OPERATORS) {
						// Chat command is only allowed for Operators, Admins or MasterAdmins
						$allowed = true;
					}
					else if ($cc['rights'] & Player::ADMINS) {
						// Chat command is only allowed for Admins or MasterAdmins
						$allowed = true;
					}
					else if ($cc['rights'] & Player::MASTERADMINS) {
						// Chat command is only allowed for MasterAdmins
						$allowed = true;
					}
					if ($allowed == true) {
						foreach ($cc['params'] as $cmd => $description) {
							$help .= $cmd . ', ';
						}
					}
				}
				else {
					if ($cc['rights'] & Player::PLAYERS) {
						// Chat command is allowed for everyone
						$allowed = true;
					}
					if ($allowed == true) {
						$help .= $name . ', ';
					}
				}
			}
			// show chat message
			$help = substr($help, 0, strlen($help) - 2);  // strip trailing ", "
			$aseco->sendChatMessage($help, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Sends one or more Manialinks immediately to the given $logins, or all Players
	public function sendManialink ($widgets, $logins = false, $timeout = 0, $hideclick = false) {

		if ($widgets != '') {
			$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= $widgets;

			if ($logins !== false) {
				try {
					// Remove whitespace and empty entries from the list
					$logins = $this->cleanupLoginList($logins);

					// Send to given Players
					$this->client->query('SendDisplayManialinkPageToLogin', $logins, $xml, ($timeout * 1000), $hideclick);
				}
				catch (Exception $exception) {
					$errmsg = $exception->getMessage();
					if ($errmsg != 'Login unknown.') {
						$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $errmsg .'" - sendManialink(): SendDisplayManialinkPageToLogin: '. $logins);
						$this->console('[DUMP] '. $widgets);
					}
				}
			}
			else {
				try {
					// Send to all connected Players
					$this->client->query('SendDisplayManialinkPage', $xml, ($timeout * 1000), $hideclick);
				}
				catch (Exception $exception) {
					$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - sendManialink(): SendDisplayManialinkPage');
					$this->console('[DUMP] '. $widgets);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Adds one or more Manialinks to the multiquery and send later together with other Manialinks waiting in the query queue
	public function addManialink ($widgets, $logins = false, $timeout = 0, $hideclick = false) {

		if ($widgets != '') {
			$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= $widgets;

			if ($logins !== false) {
				try {
					// Remove whitespace and empty entries from the list
					$logins = $this->cleanupLoginList($logins);

					// Send to given Players
					$this->client->addCall('SendDisplayManialinkPageToLogin', $logins, $xml, ($timeout * 1000), $hideclick);
				}
				catch (Exception $exception) {
					$errmsg = $exception->getMessage();
					if ($errmsg != 'Login unknown.') {
						$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $errmsg .'" - addManialink(): SendDisplayManialinkPageToLogin: '. $logins);
						$this->console('[DUMP] '. $widgets);
					}
				}
			}
			else {
				try {
					// Send to all connected Players
					$this->client->addCall('SendDisplayManialinkPage', $xml, ($timeout * 1000), $hideclick);
				}
				catch (Exception $exception) {
					$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - addManialink(): SendDisplayManialinkPage');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Sends a chat message to the given $logins, or all Players
	public function sendChatMessage ($message, $logins = false) {

		if ($message != '') {

			// Replace all entities back to normal for chat.
			$message = str_replace(
					array(
						'&amp;',
						'&quot;',
						'&apos;',
						'&gt;',
						'&lt;',
					),
					array(
						'&',
						'"',
						"'",
						'>',
						'<',
					),
					$message
			);

			if ($logins !== false) {
				try {
					// Remove whitespace and empty entries from the list
					$logins = $this->cleanupLoginList($logins);

					// Send to given Players
					$this->client->query('ChatSendServerMessageToLogin', $this->formatColors($message), $logins);
				}
				catch (Exception $exception) {
					$errmsg = $exception->getMessage();
					if ($errmsg != 'Login unknown.') {
						$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $errmsg .'" - sendChatMessage(): ChatSendServerMessageToLogin: '. $logins);
					}
				}
			}
			else {
				try {
					// Send to all connected Players
					$this->client->query('ChatSendServerMessage', $this->formatColors($message));
				}
				catch (Exception $exception) {
					$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - sendChatMessage(): ChatSendServerMessage');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Remove whitespace and empty entries from a csv string, e.g. 'login1, login2, , login3,' to 'login1,login2,login3'
	public function cleanupLoginList ($csv) {
		$newlist = array();
		foreach (explode(',', $csv) as $item) {
			$item = trim($item);
			if ($item != false) {
				$newlist[] = $item;
			}
		}
		return implode(',', $newlist);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Created by Xymph: Case-insensitive file_exists replacement function.
	// Returns matching path, otherwise false.
	public function file_exists_nocase ($filepath) {

		// try case-sensitive path first
		if (file_exists($filepath)) {
			return $filepath;
		}

		// extract directory path
		if (DIRECTORY_SEPARATOR == '/') {
			preg_match('|^(.+/)([^/]+)$|', $filepath, $paths);
		}
		else {
			// '\'
			preg_match('|^(.+\\\\)([^\\\\]+)$|', $filepath, $paths);
		}

		$dirpath = $paths[1];

		// collect all files inside directory
		$checkpaths = glob($dirpath . '*');
		if ($checkpaths === false || empty($checkpaths)) {
			return false;
		}

		// check case-insensitive paths
		foreach ($checkpaths as $path) {
			if (strtolower($filepath) == strtolower($path)) {
				return $path;
			}
		}

		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Puts an element at a specific position into an array.
	 * Increases original size by one element.
	 */
	public function insertArrayElement (&$array, $value, $pos) {

		// get current size
		$size = count($array);

		// if position is in array range
		if ($pos < 0 && $pos >= $size) {
			return false;
		}

		// shift values down
		for ($i = $size-1; $i >= $pos; $i--) {
			$array[$i+1] = $array[$i];
		}

		// now put in the new element
		$array[$pos] = $value;
		return true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Removes an element from a specific position in an array.
	 * Decreases original size by one element.
	 *
	 */
	public function removeArrayElement (&$array, $pos) {

		// get current size
		$size = count($array);

		// if position is in array range
		if ($pos < 0 && $pos >= $size) {
			return false;
		}

		// remove specified element
		unset($array[$pos]);

		// shift values up
		$array = array_values($array);
		return true;
	}  // removeArrayElement

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Moves an element from one position to the other.
	 * All items between are shifted down or up as needed.
	 *
	 */
	public function moveArrayElement (&$array, $from, $to) {

		// get current size
		$size = count($array);

		// destination and source have to be among the array borders!
		if ($from < 0 || $from >= $size || $to < 0 || $to >= $size) {
			return false;
		}

		// backup the element we have to move
		$moving_element = $array[$from];

		if ($from > $to) {
			// shift values between downwards
			for ($i = $from-1; $i >= $to; $i--) {
				$array[$i+1] = $array[$i];
			}
		}
		else {  // $from < $to
			// shift values between upwards
			for ($i = $from; $i <= $to; $i++) {
				$array[$i] = $array[$i+1];
			}
		}

		// now put in the element which was to move
		$array[$to] = $moving_element;
		return true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Formats aseco color codes in a string,
	// for example '{#server} hello' will end up as '$ff0 hello'.
	// It depends on what you have set in the config file.
	public function formatColors ($text) {
		// Replace all chat colors
		foreach ($this->chat_colors as $key => $value) {
			$text = str_replace('{#'. strtolower($key) .'}', $value[0], $text);
		}
		return $text;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Formats a text, replaces parameters in the text which are marked with {n}
	public function formatText ($text) {

		// get all function's parameters
		$args = func_get_args();

		// first parameter is the text to format
		$text = array_shift($args);

		// further parameters will be replaced in the text
		$i = 1;
		foreach ($args as $param) {
			$text = str_replace('{'. $i++ .'}', $param, $text);
		}
		return $text;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Formats a string from the format ssssttt into the format:
	// - if $MwTime has hours "hh:mm:ss.ttt" or "mm:ss.ttt" if not and $tsec is true
	// - if $MwTime has hours "hh:mm:ss" or "mm:ss" if not and $tsec is false
	public function formatTime ($MwTime, $tsec = true) {

		if ($MwTime > 0) {
			$tseconds = substr($MwTime, strlen($MwTime)-3);
			$MwTime = floor($MwTime / 1000);
			$hours = floor($MwTime / 3600);
			$MwTime = $MwTime - ($hours * 3600);
			$minutes = floor($MwTime / 60);
			$MwTime = $MwTime - ($minutes * 60);
			$seconds = floor($MwTime);
			if ($tsec) {
				if ($hours) {
					return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $seconds, $tseconds);
				}
				else {
					return sprintf('%d:%02d.%03d', $minutes, $seconds, $tseconds);
				}
			}
			else {
				if ($hours) {
					return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
				}
				else {
					return sprintf('%d:%02d', $minutes, $seconds);
				}
			}
		}
		else {
			return '0:00:000';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function formatNumber ($number, $decimals, $dec_point = '.', $thousands_sep = ',') {
		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function formatFloat ($number, $decimals = 4, $dec_point = '.', $thousands_sep = '') {
		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function timeString ($given) {

		$seconds = (int)($given % 60);
		$given /= 60;
		$minutes = (int)($given % 60);
		$given /= 60;
		$hours = (int)($given % 24);
		$days = (int)($given / 24);

		$timestring = '';
		if ($days) {
			$timestring .= sprintf("%d day%s", $days, ($days == 1 ? ' ' : 's '));
		}
		if ($hours) {
			$timestring .= sprintf("%d hour%s", $hours, ($hours == 1 ? ' ' : 's '));
		}
		if ($minutes) {
			$timestring .= sprintf("%d minute%s", $minutes, ($minutes == 1 ? ' ' : 's '));
		}
		$timestring .= sprintf("%d second%s", $seconds, ($seconds == 1 ? ' ' : 's'));

		return $timestring;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Convert boolean value to text string
	public function bool2string ($boolean) {
		if ($boolean === true) {
			return 'true';
		}
		else if ($boolean === false) {
			return 'false';
		}
		else {
			return 'null';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Convert text string to boolean value
	public function string2bool ($string) {
		if (strtoupper($string) == 'TRUE') {
			return true;
		}
		else if (strtoupper($string) == 'FALSE') {
			return false;
		}
		else {
			return null;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Summary: Strips all display formatting from an input string, suitable for display
	 *          within the game ('$$' escape pairs are preserved) and for logging
	 * Params : $input - The input string to strip formatting from
	 *          $for_tm - Optional flag to double up '$' into '$$' (default, for TM) or not (for logs, etc)
	 * Returns: The content portions of $input without formatting
	 * Authors: Bilge/Assembler Maniac/Xymph/Slig
	 *
	 * "$af0Brat$s$fffwurst" will become "Bratwurst".
	 * 2007-08-27 Xymph - replaced with Bilge/AM's code (w/o the H&L tags bit)
	 *                    http://www.tm-forum.com/viewtopic.php?p=55867#p55867
	 * 2008-04-24 Xymph - extended to handle the H/L/P tags for TMF
	 *                    http://www.tm-forum.com/viewtopic.php?p=112856#p112856
	 * 2009-05-16 Slig  - extended to emit non-TM variant & handle incomplete colors
	 *                    http://www.tm-forum.com/viewtopic.php?p=153368#p153368
	 * 2010-10-05 Slig  - updated to handle incomplete colors & tags better
	 *                    http://www.tm-forum.com/viewtopic.php?p=183410#p183410
	 * 2010-10-09 Xymph - updated to handle $[ and $] properly
	 *                    http://www.tm-forum.com/viewtopic.php?p=183410#p183410
	 * 2014-06-01 undef - added trim()
	 */
	public function stripColors ($input, $for_tm = true) {

		return trim(
				//Replace all occurrences of a null character back with a pair of dollar
				//signs for displaying in TM, or a single dollar for log messages etc.
				str_replace("\0", ($for_tm ? '$$' : '$'),
					//Replace links (introduced in TMU)
					preg_replace(
						'/
						#Strip TMF H, L & P links by stripping everything between each square
						#bracket pair until another $H, $L or $P sequence (or EoS) is found;
						#this allows a $H to close a $L and vice versa, as does the game
						\\$[hlp](.*?)(?:\\[.*?\\](.*?))*(?:\\$[hlp]|$)
						/ixu',
						//Keep the first and third capturing groups if present
						'$1$2',
						//Replace various patterns beginning with an unescaped dollar
						preg_replace(
							'/
							#Match a single dollar sign and any of the following:
							\\$
							(?:
								#Strip color codes by matching any hexadecimal character and
								#any other two characters following it (except $)
								[0-9a-f][^$][^$]
								#Strip any incomplete color codes by matching any hexadecimal
								#character followed by another character (except $)
								|[0-9a-f][^$]
								#Strip any single style code (including an invisible UTF8 char)
								#that is not an H, L or P link or a bracket ($[ and $])
								|[^][hlp]
								#Strip the dollar sign if it is followed by [ or ], but do not
								#strip the brackets themselves
								|(?=[][])
								#Strip the dollar sign if it is at the end of the string
								|$
							)
							#Ignore alphabet case, ignore whitespace in pattern & use UTF-8 mode
							/ixu',
							//Replace any matches with nothing (i.e. strip matches)
							'',
							//Replace all occurrences of dollar sign pairs with a null character
							str_replace('$$', "\0", $input)
						)
					)
				)
			)
		;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Strips only size tags from TM strings.
	 * "$w$af0Brat$n$fffwurst" will become "$af0Brat$fffwurst".
	 * 2009-03-27 Xymph - derived from stripColors above
	 *                    http://www.tm-forum.com/viewtopic.php?f=127&t=20602
	 * 2009-05-16 Slig  - extended to emit non-TM variant
	 *                    http://www.tm-forum.com/viewtopic.php?p=153368#p153368
	 * 2014-06-01 undef - added trim()
	 */
	public function stripSizes ($input, $for_tm = true) {

		return trim(
				//Replace all occurrences of a null character back with a pair of dollar
				//signs for displaying in TM, or a single dollar for log messages etc.
				str_replace("\0", ($for_tm ? '$$' : '$'),
					//Replace various patterns beginning with an unescaped dollar
					preg_replace(
						'/
						#Match a single dollar sign and any of the following:
						\\$
						(?:
							#Strip any size code
							[nwo]
							#Strip the dollar sign if it is at the end of the string
							|$
						)
						#Ignore alphabet case, ignore whitespace in pattern & use UTF-8 mode
						/ixu',
						//Replace any matches with nothing (i.e. strip matches)
						'',
						//Replace all occurrences of dollar sign pairs with a null character
						str_replace('$$', "\0", $input)
					)
				)
			)
		;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Strips newlines from strings.
	public function stripNewlines ($string) {
		return str_replace(
			array(
				"\n\n",
				"\r",
				"\n"
			),
			array(
				' ',
				'',
				''
			),
			$string
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Remove BOM-header, see http://en.wikipedia.org/wiki/Byte_order_mark
	public function stripBOM ($string) {
		return str_replace("\xEF\xBB\xBF", '', $string);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Return valid UTF-8 string, replacing faulty byte values with a given string
	 * Created by (OoR-F)~fuckfish (fish@stabb.de)
	 * http://www.tm-forum.com/viewtopic.php?p=117639#p117639
	 * Based on the original tm_substr function by Slig (slig@free.fr)
	 * Updated by Xymph;  More info: http://en.wikipedia.org/wiki/UTF-8
	 */
	public function validateUTF8String ($input, $invalidRepl = '') {

		$str = (string) $input;
		$len = strlen($str);  // byte string length
		$pos = 0;  // current byte pos in string
		$new = '';

		while ($pos < $len) {
			$co = ord($str[$pos]);

			// 4-6 bytes UTF8 => unsupported
			if ($co >= 240) {
				// bad multibyte char
				$new .= $invalidRepl;
				$pos++;
			}
			else if ($co >= 224) {
				// 3 bytes UTF8 => 1110bbbb 10bbbbbb 10bbbbbb
				if (($pos+2 < $len) &&
				    (ord($str[$pos+1]) >= 128 && ord($str[$pos+1]) < 192) &&
				    (ord($str[$pos+2]) >= 128 && ord($str[$pos+2]) < 192)) {
					// ok, it was 1 character, increase counters
					$new .= substr($str, $pos, 3);
					$pos += 3;
				}
				else {
					// bad multibyte char
					$new .= $invalidRepl;
					$pos++;
				}
			}
			else if ($co >= 194) {
				// 2 bytes UTF8 => 110bbbbb 10bbbbbb
				if (($pos+1 < $len) && (ord($str[$pos+1]) >= 128 && ord($str[$pos+1]) < 192)) {
					// ok, it was 1 character, increase counters
					$new .= substr($str, $pos, 2);
					$pos += 2;
				}
				else {
					// bad multibyte char
					$new .= $invalidRepl;
					$pos++;
				}
			}
			else if ($co >= 192) {
				// 2 bytes overlong encoding => unsupported
				// bad multibyte char 1100000b
				$new .= $invalidRepl;
				$pos++;
			}
			else {
				// $co < 192
				// 1 byte ASCII => 0bbbbbbb, or invalid => 10bbbbbb or 11111bbb
				// erroneous middle multibyte char?
				if ($co >= 128 || $co == 0) {
					$new .= $invalidRepl;
				}
				else {
					$new .= $str[$pos];
				}
				$pos++;
			}
		}
		return $new;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Outputs a formatted string without datetime.
	public function console_text () {
		$args = func_get_args();
		$message = call_user_func_array(array($this, 'formatText'), $args) . CRLF;
		$this->logMessage($message);
		flush();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Outputs a string to console with datetime prefix.
	public function console () {
		$args = func_get_args();
		$message = '['. date('Y-m-d H:i:s') .'] '. call_user_func_array(array($this, 'formatText'), $args) . CRLF;
		$this->logMessage($message);
		flush();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Wrapper for var_dump() to log into console()
	public function dump () {
		$args = func_get_args();
		ob_start();
		foreach ($args as $param) {
			var_dump($param);
		}
		$this->console_text(ob_get_clean());
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Checks two version strings
	 *
	 * @param string $operator: '<' = lt, '<=' = le, '>' = gt, '>=' = ge, '==' and '=' = eq, '!=' and '<>' = ne
	 * @return true or false
	 */
	public function versionCheck ($wanted, $current, $operator = '>') {

		// Convert e.g. "1.03b" to "1.03.2", required for version_compare()
		$wanted = str_replace(
			array('a',	'b',	'c',	'd',	'e',	'f',	'g',	'h',	'i',	'j',	'k',	'l',	'm',	'n'),
			array('.1',	'.2',	'.3',	'.4',	'.5',	'.6',	'.7',	'.8',	'.9',	'.10',	'.11',	'.12', '.13',	'.14'),
			$wanted
		);

		// Convert e.g. "1.03b" to "1.03.2", required for version_compare()
		$current = str_replace(
			array('a',	'b',	'c',	'd',	'e',	'f',	'g',	'h',	'i',	'j',	'k',	'l',	'm',	'n'),
			array('.1',	'.2',	'.3',	'.4',	'.5',	'.6',	'.7',	'.8',	'.9',	'.10',	'.11',	'.12', '.13',	'.14'),
			$current
		);

		return version_compare($wanted, $current, $operator);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Gets the specified chat message out of the settings file, only from "config/UASECO.xml"
	public function getChatMessage ($name) {
		return htmlspecialchars_decode($this->chat_messages[$name][0]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if an admin is allowed to perform this ability
	public function allowAdminAbility ($ability) {

		// Map to uppercase before checking list
		$ability = strtoupper($ability);
		if (isset($this->admin_abilities[$ability])) {
			return $this->admin_abilities[$ability][0];
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if an operator is allowed to perform this ability
	public function allowOpAbility ($ability) {

		// Map to uppercase before checking list
		$ability = strtoupper($ability);
		if (isset($this->operator_abilities[$ability])) {
			return $this->operator_abilities[$ability][0];
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player is allowed to perform this ability
	public function allowAbility ($player, $ability) {

		// Check for unlocked password
		if ($this->settings['lock_password'] != '' && !$player->unlocked) {
			return false;
		}

		// MasterAdmins can always do everything
		if ($this->isMasterAdmin($player)) {
			return true;
		}

		// Check Admins & their abilities
		if ($this->isAdmin($player)) {
			return $this->allowAdminAbility($ability);
		}

		// Check Operators & their abilities
		if ($this->isOperator($player)) {
			return $this->allowOpAbility($ability);
		}

		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player is in masteradmin list with, optionally, an authorized IP.
	public function isMasterAdmin ($player) {

		// Check for masteradmin list entry
		if (isset($player->login) && $player->login != '' && isset($this->masteradmin_list['TMLOGIN'])) {
			if (($i = array_search($player->login, $this->masteradmin_list['TMLOGIN'])) !== false) {
				// Check for matching IP if set
				if ($this->masteradmin_list['IPADDRESS'][$i] != '') {
					if (!$this->matchIP($player->ip, $this->masteradmin_list['IPADDRESS'][$i])) {
						trigger_error("Attempt to use MasterAdmin login [". $player->login ."] from IP [". $player->ip ."]!", E_USER_WARNING);
						return false;
					}
					else {
						return true;
					}
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player is in admin list with, optionally, an authorized IP.
	public function isAdmin ($player) {

		// Check for admin list entry
		if (isset($player->login) && $player->login != '' && isset($this->admin_list['TMLOGIN'])) {
			if (($i = array_search($player->login, $this->admin_list['TMLOGIN'])) !== false) {
				// Check for matching IP if set
				if ($this->admin_list['IPADDRESS'][$i] != '') {
					if (!$this->matchIP($player->ip, $this->admin_list['IPADDRESS'][$i])) {
						trigger_error("Attempt to use Admin login [". $player->login ."] from IP [". $player->ip ."]!", E_USER_WARNING);
						return false;
					}
					else {
						return true;
					}
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player is in operator list with, optionally, an authorized IP.
	public function isOperator ($player) {

		// Check for operator list entry
		if (isset($player->login) && $player->login != '' && isset($this->operator_list['TMLOGIN'])) {
			if (($i = array_search($player->login, $this->operator_list['TMLOGIN'])) !== false) {
				// check for matching IP if set
				if ($this->operator_list['IPADDRESS'][$i] != '') {
					if (!$this->matchIP($player->ip, $this->operator_list['IPADDRESS'][$i])) {
						trigger_error("Attempt to use Operator login [" . $player->login ."] from IP [". $player->ip ."]!", E_USER_WARNING);
						return false;
					}
					else {
						return true;
					}
				}
				else {
					return true;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player is in any admin tier with, optionally, an authorized IP.
	public function isAnyAdmin ($player) {
		return ($this->isMasterAdmin($player) || $this->isAdmin($player) || $this->isOperator($player));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player login is in masteradmin list.
	public function isMasterAdminL ($login) {
		if ($login != '' && isset($this->masteradmin_list['TMLOGIN'])) {
			return in_array($login, $this->masteradmin_list['TMLOGIN']);
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player login is in admin list.
	public function isAdminL ($login) {
		if ($login != '' && isset($this->admin_list['TMLOGIN'])) {
			return in_array($login, $this->admin_list['TMLOGIN']);
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player login is in operator list.
	public function isOperatorL ($login) {
		// Check for operator list entry
		if ($login != '' && isset($this->operator_list['TMLOGIN'])) {
			return in_array($login, $this->operator_list['TMLOGIN']);
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player login is in any admin tier.
	public function isAnyAdminL ($login) {
		return ($this->isMasterAdminL($login) || $this->isAdminL($login) || $this->isOperatorL($login));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Check login string for LAN postfix (pre/post v2.11.21).
	// http://www.tm-forum.com/viewtopic.php?p=152885#p152885
	// Generated unique lan logins are now "name_1.2.3.4_2350" instead of "name/1.2.3.4:2350"
	public function isLANLogin ($login) {

		$n = "(25[0-5]|2[0-4]\d|[01]?\d\d|\d)";
		return (
			preg_match("/(\/{$n}\\.{$n}\\.{$n}\\.{$n}:\d+)$/", $login)
			||
		        preg_match("/(_{$n}\\.{$n}\\.{$n}\\.{$n}_\d+)$/", $login)
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks if the given player IP matches the corresponding list IP,
	// allowing for class C and B wildcards, and multiple comma-separated
	// IPs / wildcards.
	public function matchIP ($playerip, $listip) {

		// Check for offline player (removeadmin / removeop)
		if ($playerip == '') {
			return true;
		}

		$match = false;
		// Check all comma-separated IPs/wildcards
		foreach (explode(',', $listip) as $ip) {
			if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip)) {
				// check for complete list IP
				$match = ($playerip == $ip);
			}
			else if (substr($ip, -4) == '.*.*') {
				// check class B wildcard
				$match = (preg_replace('/\.\d+\.\d+$/', '', $playerip) == substr($ip, 0, -4));
			}
			else if (substr($ip, -2) == '.*') {
				// check class C wildcard
				$match = (preg_replace('/\.\d+$/', '', $playerip) == substr($ip, 0, -2));
			}
			if ($match) {
				return true;
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Read Admin/Operator/Ability lists and apply them on the current instance.
	public function readLists () {

		// Get lists file name
		$adminops_file = $this->settings['adminops_file'];

		if ($lists = $this->parser->xmlToArray($adminops_file, true, true)) {
			// read the XML structure into arrays
			$this->titles = $lists['LISTS']['TITLES'][0];

			if (is_array($lists['LISTS']['ADMINS'][0])) {
				$this->admin_list = $lists['LISTS']['ADMINS'][0];
				// check admin list consistency
				if (empty($this->admin_list['IPADDRESS'])) {
					// fill <ipaddress> list to same length as <tmlogin> list
					if (($cnt = count($this->admin_list['TMLOGIN'])) > 0) {
						$this->admin_list['IPADDRESS'] = array_fill(0, $cnt, '');
					}
				}
				else {
					if (count($this->admin_list['TMLOGIN']) != count($this->admin_list['IPADDRESS'])) {
						trigger_error("Admin mismatch between <tmlogin>'s and <ipaddress>'s!", E_USER_WARNING);
					}
				}
			}

			if (is_array($lists['LISTS']['OPERATORS'][0])) {
				$this->operator_list = $lists['LISTS']['OPERATORS'][0];
				// check operator list consistency
				if (empty($this->operator_list['IPADDRESS'])) {
					// fill <ipaddress> list to same length as <tmlogin> list
					if (($cnt = count($this->operator_list['TMLOGIN'])) > 0) {
						$this->operator_list['IPADDRESS'] = array_fill(0, $cnt, '');
					}
				}
				else {
					if (count($this->operator_list['TMLOGIN']) != count($this->operator_list['IPADDRESS'])) {
						trigger_error("Operators mismatch between <tmlogin>'s and <ipaddress>'s!", E_USER_WARNING);
					}
				}
			}

			$this->admin_abilities = $lists['LISTS']['ADMIN_ABILITIES'][0];
			$this->operator_abilities = $lists['LISTS']['OPERATOR_ABILITIES'][0];

			// convert strings to booleans
			foreach ($this->admin_abilities as $ability => $value) {
				if (strtoupper($value[0]) == 'TRUE') {
					$this->admin_abilities[$ability][0] = true;
				}
				else {
					$this->admin_abilities[$ability][0] = false;
				}
			}
			foreach ($this->operator_abilities as $ability => $value) {
				if (strtoupper($value[0]) == 'TRUE') {
					$this->operator_abilities[$ability][0] = true;
				}
				else {
					$this->operator_abilities[$ability][0] = false;
				}
			}
			return true;
		}
		else {
			// could not parse XML file
			trigger_error('Could not read/parse adminops file ['. $adminops_file .']!', E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Write Admin/Operator/Ability lists to save them for future runs.
	public function writeLists () {

		// get lists file name
		$adminops_file = $this->settings['adminops_file'];

		// compile lists file contents
		$lists  = '<?xml version="1.0" encoding="utf-8"?>'. CRLF;
		$lists .= '<lists>'. CRLF;
		$lists .= "\t" . '<titles>'. CRLF;
		foreach ($this->titles as $title => $value) {
			$lists .= "\t\t" . '<'. strtolower($title) .'>';
			$lists .= $value[0];
			$lists .= '</'. strtolower($title) .'>'. CRLF;
		}
		$lists .= "\t" . '</titles>'. CRLF . CRLF;
		$lists .= "\t" . '<admins>'. CRLF;
		$empty = true;
		if (isset($this->admin_list['TMLOGIN'])) {
			for ($i = 0; $i < count($this->admin_list['TMLOGIN']); $i++) {
				if ($this->admin_list['TMLOGIN'][$i] != '') {
					$lists .= "\t\t" . '<tmlogin>'. $this->admin_list['TMLOGIN'][$i] .'</tmlogin>';
					$lists .= ' <ipaddress>'. $this->admin_list['IPADDRESS'][$i] .'</ipaddress>'. CRLF;
					$empty = false;
				}
			}
		}
		if ($empty) {
			$lists .= '<!-- format:'. CRLF;
			$lists .= "\t\t" . '<tmlogin>YOUR_ADMIN_LOGIN</tmlogin> <ipaddress></ipaddress>'. CRLF;
			$lists .= '-->'. CRLF;
		}
		$lists .= "\t" . '</admins>'. CRLF . CRLF;
		$lists .= "\t" . '<operators>'. CRLF;
		$empty = true;
		if (isset($this->operator_list['TMLOGIN'])) {
			for ($i = 0; $i < count($this->operator_list['TMLOGIN']); $i++) {
				if ($this->operator_list['TMLOGIN'][$i] != '') {
					$lists .= "\t\t" . '<tmlogin>'. $this->operator_list['TMLOGIN'][$i] .'</tmlogin>';
					$lists .= ' <ipaddress>'. $this->operator_list['IPADDRESS'][$i] .'</ipaddress>'. CRLF;
					$empty = false;
				}
			}
		}
		if ($empty) {
			$lists .= '<!-- format:'. CRLF;
			$lists .= "\t\t" . '<tmlogin>YOUR_OPERATOR_LOGIN</tmlogin> <ipaddress></ipaddress>'. CRLF;
			$lists .= '-->'. CRLF;
		}
		$lists .= "\t" . '</operators>'. CRLF . CRLF;
		$lists .= "\t" . '<admin_abilities>'. CRLF;
		foreach ($this->admin_abilities as $ability => $value) {
			$lists .= "\t\t<". strtolower($ability) .'>';
			$lists .= ($value[0] ? 'true' : 'false');
			$lists .= '</'. strtolower($ability) .'>'. CRLF;
		}
		$lists .= "\t" . '</admin_abilities>'. CRLF . CRLF;
		$lists .= "\t" . '<operator_abilities>'. CRLF;
		foreach ($this->operator_abilities as $ability => $value) {
			$lists .= "\t\t<". strtolower($ability) .'>';
			$lists .= ($value[0] ? 'true' : 'false');
			$lists .= '</'. strtolower($ability) .'>'. CRLF;
		}
		$lists .= "\t" . '</operator_abilities>'. CRLF;
		$lists .= '</lists>'. CRLF;

		// Write out the lists file
		if (!@file_put_contents($adminops_file, $lists)) {
			trigger_error('Could not write adminops file ['. $adminops_file .']!', E_USER_WARNING);
			return false;
		} else {
			return true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Read Banned IPs list and apply it on the current instance.
	public function readIPs () {
		// Get banned IPs file name
		$bannedips_file = $this->settings['bannedips_file'];

		if ($list = $this->parser->xmlToArray($bannedips_file)) {
			// Read the XML structure into variable
			if (isset($list['BAN_LIST']['IPADDRESS'])) {
				$this->banned_ips = $list['BAN_LIST']['IPADDRESS'];
			}
			else {
				$this->banned_ips = array();
			}
			return true;
		}
		else {
			// Could not parse XML file
			trigger_error('Could not read/parse banned IPs file ['. $bannedips_file .']!', E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Write Banned IPs list to save it for future runs.
	public function writeIPs () {
		// Get banned IPs file name
		$bannedips_file = $this->settings['bannedips_file'];
		$empty = true;

		// compile banned IPs file contents
		$list  = '<?xml version="1.0" encoding="utf-8"?>'. CRLF;
		$list .= '<ban_list>'. CRLF;
		for ($i = 0; $i < count($this->banned_ips); $i++) {
			if ($this->banned_ips[$i] != '') {
				$list .= "\t\t" . '<ipaddress>'. $this->banned_ips[$i] .'</ipaddress>'. CRLF;
				$empty = false;
			}
		}
		if ($empty) {
			$list .= '<!-- format:'. CRLF;
			$list .= "\t\t" . '<ipaddress>xx.xx.xx.xx</ipaddress>'. CRLF;
			$list .= '-->'. CRLF;
		}
		$list .= '</ban_list>'. CRLF;

		// Write out the list file
		if (!@file_put_contents($bannedips_file, $list)) {
			trigger_error('Could not write banned IPs file ['. $bannedips_file .']!', E_USER_WARNING);
			return false;
		}
		else {
			return true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function customErrorHandler ($errno, $errstr, $errfile, $errline) {

		// Check for error suppression
		if (error_reporting() == 0) {
			return;
		}

		switch ($errno) {
			case E_USER_ERROR:
				$message = "[UASECO Fatal Error] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);

				// Throw 'shutting down' event
				$this->releaseEvent('onShutdown', null);

				try {
					// Clear all ManiaLinks
					$this->client->query('SendHideManialinkPage');
				}
				catch (Exception $exception) {
					$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - customErrorHandler(): SendHideManialinkPage');
				}

				if (function_exists('xdebug_get_function_stack')) {
					$this->logMessage(print_r(xdebug_get_function_stack()), true);
				}
				die();
				break;

			case E_USER_WARNING:
				$message = "[UASECO Warning] $errstr". CRLF;
				$this->logMessage($message);
				break;

			case E_ERROR:
				$message = "[PHP Fatal Error] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			case E_WARNING:
				$message = "[PHP Warning] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			case E_NOTICE:
				$message = "[PHP Notice] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			default:
//				if (strpos($errstr, 'Function call_user_method') !== false) {
//					break;
//				}
				$message = "[PHP $errno] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				// do nothing, only treat known errors
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function customFatalErrorShutdownHandler () {
		$last_error = error_get_last();

		if ($last_error['type'] === E_ERROR) {
			$this->customErrorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Setup the current logfile
	public function setupLogfile () {

		// Create logs/ directory if needed
		$dir = './logs';
		if (!file_exists($dir)) {
			mkdir($dir);
		}

		// Define daily file inside dir
		$this->logfile['file'] = $dir . DIRECTORY_SEPARATOR . date('Y-m-d') .'-current.txt';

		// On stop or crash replace old logfile
		if (file_exists($this->logfile['file']) && !$this->logfile['handle']) {
			rename($this->logfile['file'], $dir . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s') .'.txt');
		}

		// Check for logfiles from the past
		if ($dh = opendir($dir)) {
			$list = array();
			while (($logfile = readdir($dh)) !== false) {
				if ( is_file($dir . DIRECTORY_SEPARATOR . $logfile) ) {
					$lastmodified = filemtime($dir . DIRECTORY_SEPARATOR . $logfile);

					if ($lastmodified < (time() - 60*60*24*14)) {
						// Delete all logfiles older then 14 days
						unlink($dir . DIRECTORY_SEPARATOR . $logfile);
					}

					$result = preg_match('/-current\.txt$/', $logfile);
					if ($result !== false && $result >= 1) {
						// Rename all logfiles marked with "-current.txt" and older then one hour
						rename(
							$dir . DIRECTORY_SEPARATOR . $logfile,
							$dir . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s', $lastmodified) .'.txt'
						);
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Writes a logfile of all output messages in daily chunks inside the logs/ directory.
	public function logMessage ($text) {

		// If new daily file, close old logfile
		if (!file_exists($this->logfile['file']) && $this->logfile['handle']) {
			fclose($this->logfile['handle']);
			$this->logfile['handle'] = false;
		}

		if (!$this->logfile['handle']) {
			$this->logfile['handle'] = fopen($this->logfile['file'], 'wb+');
		}
		fwrite($this->logfile['handle'], $text);
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			chmod($this->logfile['file'], 0666);
		}
		else {
			// Echo to console on Windows
			echo str_replace('»', '>', $text);
		}
	}
}

?>
