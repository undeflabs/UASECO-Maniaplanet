<?php
/*
 * Plugin: Styles
 * ~~~~~~~~~~~~~~
 * » Selects ManiaLink window style templates.
 * » Based upon plugin.styles.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-07
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
	$_PLUGIN = new PluginStyles();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginStyles extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Selects ManiaLink window style templates.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		// handles action id's "49"-"100" for selecting from max. 50 style templates
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');

		$this->registerChatCommand('style', 'chat_style', 'Selects window style (see: /style help)', Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		if ($style = $this->getStyle($player->login)) {
			// load player's personal style
			$style_file = 'config/styles/'. $style .'.xml';
			if (($player->style = $aseco->parser->xmlToArray($style_file, true, true)) && isset($player->style['STYLES'])) {
				$player->style = $player->style['STYLES'];
			}
			else {
				// Could not parse XML file
				trigger_error('[Style] Could not read/parse style file [config/styles/'. $style_file .']!', E_USER_WARNING);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_style ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if ($chat_parameter == 'help') {
			$header = '{#black}/style$g will change the window style:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available styles');
			$help[] = array('...', '{#black}default',
			                'Resets style to server default');
			$help[] = array('...', '{#black}xxx',
			                'Selects window style xxx');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($chat_parameter == 'list') {
			$player->maplist = array();

			// read list of style files
			$styledir = 'config/styles/';
			$dir = opendir($styledir);
			$files = array();
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, -4)) == '.xml') {
					$files[] = substr($file, 0, strlen($file)-4);
				}
			}
			closedir($dir);
			sort($files, SORT_STRING);
			if (count($files) > 50) {
				$files = array_slice($files, 0, 50);  // maximum 50 templates
				trigger_error('[Style] Too many style templates - maximum 50!', E_USER_WARNING);
			}

			// sneak in standard entry
			$files[] = 'default';

			$head = 'Currently available window styles:';
			$list = array();
			$sid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(0.8, 0.1, 0.7), array('Icons128x32_1', 'Windowed'));
			foreach ($files as $file) {
				// store style in player object for jukeboxing
				$trkarr = array();
				$trkarr['style'] = $file;
				$player->maplist[] = $trkarr;

				$list[] = array(str_pad($sid, 2, '0', STR_PAD_LEFT) . '.',
				                array('{#black}' . $file, $sid+48));  // action id
				$sid++;
				if (++$lines > 14) {
					$player->msgs[] = $list;
					$lines = 0;
					$list = array();
				}
			}

			// add if last batch exists
			if (!empty($list)) {
				$player->msgs[] = $list;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($chat_parameter != '') {
			$style = $chat_parameter;
			if (is_numeric($style) && $style > 0) {
				$sid = ltrim($style, '0');
				$sid--;
				if (array_key_exists($sid, $player->maplist) &&
				    isset($player->maplist[$sid]['style'])) {
					$style = $player->maplist[$sid]['style'];
				}
			}
			if ($style == 'default') {
				$player->style = $aseco->style;
				$message = '{#server}» Style reset to server default {#highlite}' . $aseco->settings['window_style'] . '{#server} !';
				$this->setStyle($player->login, $aseco->settings['window_style']);
			}
			else {
				$style_file = 'config/styles/' . $style . '.xml';
				// load new style
				if (($styledata = $aseco->parser->xmlToArray($style_file, true, true)) && isset($styledata['STYLES'])) {
					$player->style = $styledata['STYLES'];
					$message = '{#server}» Style {#highlite}' . $chat_parameter . '{#server} selected!';
					$this->setStyle($player->login, $style);
				}
				else {
					// Could not parse XML file
					trigger_error('[Style] Could not read/parse style file ' . $style_file . ' !', E_USER_WARNING);
					$message = '{#server}» {#error}No valid style file, use {#highlite}$i /style list {#error}!';
				}
			}
			$aseco->sendChatMessage($message, $player->login);
		}
		else {
			$message = '{#server}» {#error}No style specified, use {#highlite}$i /style help {#error}!';
			$aseco->sendChatMessage($message, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles ManiaLink style responses
	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// leave actions outside 49 - 100 to other handlers
		$action = (int) $answer[2];
		if ($action >= 49 && $action <= 100) {
			// Get player & style
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$style = $player->maplist[$action-49]['style'];

				// select new style & refresh list
				$this->chat_style($aseco, $player->login, 'style', $style);

				// display restyled list
				$this->chat_style($aseco, $player->login, 'style', 'list');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getStyle ($login) {
		global $aseco;

		$id = $aseco->server->players->getPlayerId($login);
		if ($id) {
			// Get player's style
			$query = "
			SELECT
				`Style`
			FROM `players_extra`
			WHERE `PlayerId` = ". $id .";
			";

			$result = $aseco->mysqli->query($query);
			if ($result) {
				$dbextra = $result->fetch_object();
				$result->free_result();
				return $dbextra->Style;
			}
			else {
				trigger_error('[Style] Could not get player\'s style! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
				return false;
			}
		}
		else {
			trigger_error('[Style] Could not found player!', E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setStyle ($login, $style) {
		global $aseco;

		$id = $aseco->server->players->getPlayerId($login);
		if ($id) {
			$query = "
			UPDATE `players_extra` SET
				`Style` = ". $aseco->mysqli->quote($style) ."
			WHERE `PlayerId` = ". $id .";
			";

			$result = $aseco->mysqli->query($query);
			if (!$result) {
				trigger_error('[Style] Could not update player\'s style! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			}
		}
		else {
			trigger_error('[Style] Could not found player!', E_USER_WARNING);
		}
	}
}

?>
