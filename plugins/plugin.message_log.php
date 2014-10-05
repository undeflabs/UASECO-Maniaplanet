<?php
/*
 * Plugin: Message Log
 * ~~~~~~~~~~~~~~~~~~~
 * » Keeps log of system messages, and displays the messages log.
 * » Based upon plugin.msglog.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-03
 * Copyright:	2014 by undef.de
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
 *  - plugins/plugin.manialinks.php
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

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Keeps log of system messages, and displays the messages log.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		// handles action id "7223" for /msglog button
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

		$xml  = '<manialink id="MessageLogButton">';
		$xml .= '<frame posn="-64.1 -36.6 0">';
		$xml .= '<quad posn="0 0 0" sizen="2.6 2.6" style="UIConstructionSimple_Buttons" substyle="Text" action="MessageLogShow"/>';
		$xml .= '</frame>';
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, $player->login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		if ($answer[2] == 'MessageLogShow') {
			// Get player
			$player = $aseco->server->players->getPlayer($answer[1]);

			// Call /msglog
			$aseco->releaseChatCommand('/msglog', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

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
		$aseco->plugins['PluginManialinks']->display_msgwindow($aseco, $lines, $timeout);
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

}

?>
