<?php
/*
 * Plugin: Message Log
 * ~~~~~~~~~~~~~~~~~~~
 * » Keeps log of system messages, and displays the messages log.
 * » Based upon plugin.msglog.php from XAseco2/1.03 written by Xymph
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

	// Start the plugin
	$_PLUGIN = new PluginMessageLog();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMessageLog extends Plugin {
	public $message_buffer		= array();			// message history buffer
	public $message_length		= 21;				// length of message history
	public $line_length		= 800;				// max length of message line
	public $window_length		= 5;				// number of message lines
	public $podium_chat_time	= 0;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-05-14');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Keeps log of system messages, and displays the messages log.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);


		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onSendWindowMessage',		'onSendWindowMessage');

		$this->registerChatCommand('msglog', 'chat_msglog', 'Displays log of recent system messages', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		$xml  = '<manialink id="MessageLogButton" name="MessageLogButton" version="3">';
		$xml .= '<frame pos="-159.4 -68" z-index="0">';
		$xml .= '<quad pos="0 0" z-index="0" size="4.875 4.875" style="UIConstructionSimple_Buttons" substyle="Text" action="PluginMessageLog?Action=MessageLogShow"/>';
		$xml .= '</frame>';
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, $player->login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		if ($answer['Action'] == 'MessageLogShow') {
			// Get player
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				// Call /msglog
				$aseco->releaseChatCommand('/msglog', $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $response) {

		$timeout = $aseco->client->query('GetChatTime');
		$this->podium_chat_time = $timeout['CurrentValue'];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSendWindowMessage ($aseco, $params) {

		$message = $params[0];
		$scoreboard = $params[1];

		// append message line(s) to history
		$message = explode(LF, $message);
		foreach ($message as $item) {
			// break up long (report) lines into chunks
			$multi = explode(LF, wordwrap('$z$s'. $item, $this->line_length, LF .'$z$s$n'));
			foreach ($multi as $line) {
				// drop oldest message line if buffer full
				if (count($this->message_buffer) >= $this->message_length) {
					array_shift($this->message_buffer);
				}
				$this->message_buffer[] = $aseco->formatColors($line);
			}
		}

		// check for display at end of map
		if ($scoreboard) {
			$timeout = $this->podium_chat_time + 5000;  // podium animation
		}
		else {
			$timeout = $aseco->settings['window_timeout'] * 1000;
		}

		$lines = array_slice($this->message_buffer, -$this->window_length);
		$this->display_msgwindow($aseco, $lines, $timeout);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_msglog ($aseco, $login, $chat_command, $chat_parameter) {

		if (!empty($this->message_buffer)) {
			$header = 'Recent system message history:';
			$msgs = array();
			foreach ($this->message_buffer as $line) {
				$msgs[] = array($line);
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'NewMessage'), $msgs, array(1.53), 'OK');
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No system message history found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays the Message window
	 *
	 * $msgs   : lines to be displayed
	 * $timeout: timeout for window in msec
	 */
	public function display_msgwindow ($aseco, $msgs, $timeout) {

		$cnt = count($msgs);
		$xml = '<manialink id="MessageLogWindow" name="MessageLogWindow" version="3"><frame pos="-122.5 81.5625" z-index="0">';
		$pos = -1.875;
		foreach ($msgs as $msg) {
			$xml .= '<label pos="2.5 '. $pos .'" z-index="1" sizen="227.5 1.875" style="TextRaceChat" text="'. $aseco->handleSpecialChars($msg) .'"/>';
			$pos -= 4.6875;
		}
		$xml .= '</frame></manialink>';
		$aseco->addManialink($xml, false, $timeout, false);
	}
}

?>
