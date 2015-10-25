<?php
/*
 * Plugin: Welcome Center
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Displays a message in the chat and can display a Welcome-Window on Player connects.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-09-10
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
 *  - includes/core/window.class.php
 *  - plugins/plugin.rasp.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginWelcomeCenter();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginWelcomeCenter extends Plugin {
	public $config = array();
	private $messages = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a message in the chat and can display a Welcome-Window on Player connects.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onEndMap',		'onEndMap');

		$this->registerChatCommand('message', 'chat_message', 'Shows random informational message', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/welcome_center.xml', true, true)) {
			trigger_error('[WelcomeCenter] Could not read/parse config file "config/welcome_center.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['WELCOME_WINDOW'][0]['ENABLED'][0]			= ((strtoupper($this->config['WELCOME_WINDOW'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]	= ((strtoupper($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]) == 'TRUE')	? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]		= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]) == 'TRUE')		? true : false);
		$this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]) == 'TRUE')			? true : false);
		$this->config['INFO_MESSAGES'][0]['ENABLED'][0]				= ((strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'TRUE')			? true : false);

		foreach ($this->config['INFO_MESSAGES'][0]['MESSAGES'][0] as $msg) {
			$this->messages[] = $msg[0];
		}
		unset($this->config['INFO_MESSAGES'][0]['MESSAGES'][0]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_message ($aseco, $login, $chat_command, $chat_parameter) {

		// Get random message
		$i = mt_rand(0, count($this->messages) - 1);
		$message = $aseco->formatColors($this->config['INFO_MESSAGES'][0]['MESSAGE_PREFIX'][0] . $this->messages[$i]);

		// Send the message
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addInfoMessage ($message) {
		$this->messages[] = $message;
		$this->messages = array_unique($this->messages);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		if ($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0] == true && $aseco->startup_phase == false) {

			$show = true;
			if (strtolower($this->config['JOIN_LEAVE_INFO'][0]['SHOW_CONNECT'][0]) == 'operators' && (!$aseco->isOperator($player) && !$aseco->isAdmin($player) && !$aseco->isMasterAdmin($player))) {
				$show = false;
			}
			else if (strtolower($this->config['JOIN_LEAVE_INFO'][0]['SHOW_CONNECT'][0]) == 'admins' && (!$aseco->isAdmin($player) && !$aseco->isMasterAdmin($player))) {
				$show = false;
			}
			else if (strtolower($this->config['JOIN_LEAVE_INFO'][0]['SHOW_CONNECT'][0]) == 'masteradmins' && !$aseco->isMasterAdmin($player)) {
				$show = false;
			}
			if ($show === true) {
				// Define Admin/Player title
				$title = 'New Player';
				if ($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0] == true) {
					$title = $aseco->isMasterAdmin($player) ? '{#logina}'. $aseco->titles['MASTERADMIN'][0] :
						($aseco->isAdmin($player) ? '{#logina}'. $aseco->titles['ADMIN'][0] :
						($aseco->isOperator($player) ? '{#logina}'. $aseco->titles['OPERATOR'][0] :
						'New Player')
					);
				}

				// Setup Ladderrank, Serverrank, Nation and Zone
				$ladderrank = (($player->ladderrank >= 0) ? $aseco->formatNumber($player->ladderrank, 0) : 0);
				$serverrank = $aseco->plugins['PluginRasp']->getRank($player->login);
				$zone = $player->zone;
				array_shift($zone);		// Remove continent from $zone array

				// Show new Player joins message to all Players
				$message = str_replace(
					array(
						'{title}',
						'{nickname}',
						'{continent}',
						'{nation}',
						'{zone}',
						'{visits}',
						'{ladderrank}',
						'{serverrank}',
					),
					array(
						$title,
						$aseco->stripColors($player->nickname),
						$player->continent,
						$aseco->country->iocToCountry($player->nation),
						implode(', ', $zone),
						$player->visits,
						$ladderrank,
						$serverrank,
					),
					$this->config['JOIN_LEAVE_INFO'][0]['JOIN_MESSAGE'][0]
				);
				if (!empty($message)) {
					if ($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0] == true && function_exists('send_window_message')) {
						send_window_message($aseco, $message, false);
					}
					else {
						$aseco->sendChatMessage($message);
					}
				}
			}
		}

		if ($this->config['WELCOME_WINDOW'][0]['ENABLED'][0] == true) {
			$skip = false;
			if ($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0] == true) {
				$query = "
				SELECT
					`Average`
				FROM `%prefix%rankings`
				WHERE `PlayerId` = ". $aseco->db->quote($player->id) ."
				LIMIT 1;
				";

				$result = $aseco->db->query($query);
				if ($result) {
					if ($result->num_rows > 0) {
						$skip = true;
					}
					$result->free_result();
				}
			}
			if ($skip == false) {
				// Send it direct to the Player
				$this->buildWelcomeWindow($player);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		if ($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0] == true) {

			// Setup Zone
			$zone = $player->zone;
			array_shift($zone);	// Remove continent from $zone array

			// Show Player leaves message to all remaining Players
			$message = $this->config['JOIN_LEAVE_INFO'][0]['LEAVE_MESSAGE'][0];
			$message = str_replace(
				array(
					'{nickname}',
					'{continent}',
					'{nation}',
					'{zone}',
					'{playtime}',
				),
				array(
					$aseco->stripColors($player->nickname),
					$player->continent,
					$aseco->country->iocToCountry($player->nation),
					implode(', ', $zone),
					$aseco->timeString($player->getTimeOnline(), true),
				),
				$message
			);
			if ($message != '') {
				if ( ($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0] == true) && (function_exists('send_window_message')) ) {
					send_window_message($aseco, $message, false);
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// If not enabled, bail out immediately
		if ($this->config['INFO_MESSAGES'][0]['ENABLED'][0] == false) {
			return;
		}

		// If no info messages, bail out immediately
		if (count($this->messages) == 0) {
			return;
		}

		// Get random message
		$i = mt_rand(0, count($this->messages) - 1);
		$message = $aseco->formatColors($this->config['INFO_MESSAGES'][0]['MESSAGE_PREFIX'][0] . $this->messages[$i]);


		// Send the Message to all connected Players...
		if (strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'WINDOW') {
			// ..into message window
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			// ..into chat
			$aseco->sendChatMessage($message, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWelcomeWindow ($player) {
		global $aseco;

		$xml = '';

		// Replace line break markers with line break
		$message = str_replace('{br}', LF, $this->config['WELCOME_WINDOW'][0]['MESSAGE'][0]);
		$message = str_replace('{server}', $aseco->handleSpecialChars($aseco->stripColors($aseco->server->name)), $message);
		$message = str_replace('{player}', $aseco->handleSpecialChars($player->nickname.'$Z'), $message);

		// Set the content
		if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['NORMAL'][0] != '') {
			$xml .= '<quad posn="150 -11.25 0.02" sizen="45 87.1875" image="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['NORMAL'][0] .'"';
			if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['FOCUS'][0] != '') {
	 			$xml .= ' imagefocus="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['FOCUS'][0] .'"';
			}
			if ($this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['LINK'][0] != '') {
	 			$xml .= ' url="'. $this->config['WELCOME_WINDOW'][0]['IMAGE'][0]['LINK'][0] .'"';
			}
			$xml .= '/>';
			$xml .= '<label posn="7.5 -11.25 0.01" sizen="137.5 0" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		}
		else {
			$xml .= '<label posn="7.5 -11.25 0.01" sizen="181 0" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		}


		// Setup settings for Window
		$settings_title = array(
			'icon'	=> $this->config['WELCOME_WINDOW'][0]['ICON_STYLE'][0] .','. $this->config['WELCOME_WINDOW'][0]['ICON_SUBSTYLE'][0],
		);
		$window = new Window();
		$window->setLayoutTitle($settings_title);
		$window->setContent('Welcome to '. $aseco->stripColors($aseco->server->name) .'!', $xml);
		$window->send($player, $this->config['WELCOME_WINDOW'][0]['AUTOHIDE'][0], false);
	}
}

?>
