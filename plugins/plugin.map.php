<?php
/*
 * Plugin: Map
 * ~~~~~~~~~~~
 * » Times playing time of a map, and provides map and time info.
 * » Based upon plugin.map.php from XAseco2/1.03 written by Xymph
 *   and plugin.rasp_nextmap.php from XAseco2/1.03 updated by Xymph and AssemblerManiac
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-06-29
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
 *  - plugins/plugin.rasp_jukebox.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginMap();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMap extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Times playing time of a map, and provides map and time info.');

		$this->addDependence('PluginRaspJukebox',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onBeginMap',	'onBeginMap');
		$this->registerEvent('onBeginMap1',	'onBeginMap1');		// use 2nd event to start timer just before racing commences
		$this->registerEvent('onEndMap',	'onEndMap');

		$this->registerChatCommand('map',	'chat_map',		'Shows info about the current map',		Player::PLAYERS);
		$this->registerChatCommand('nextmap',	'chat_nextmap',		'Shows name of the next map',			Player::PLAYERS);
		$this->registerChatCommand('playtime',	'chat_playtime',	'Shows time current map has been playing',	Player::PLAYERS);
		$this->registerChatCommand('time',	'chat_time',		'Shows current server time and date',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_map ($aseco, $login, $chat_command, $chat_parameter) {

		$name = $aseco->stripColors($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error == '') {
			$name = '$l[http://'. $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name .'$l';
		}

		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$message = $aseco->formatText($aseco->getChatMessage('MAP'),
				$name,
				$aseco->server->maps->current->author,
				$aseco->server->maps->current->authorscore,
				$aseco->server->maps->current->goldtime,
				$aseco->server->maps->current->silvertime,
				$aseco->server->maps->current->bronzetime,
				$aseco->server->maps->current->cost
			);
		}
		else {
			$message = $aseco->formatText($aseco->getChatMessage('MAP'),
				$name,
				$aseco->server->maps->current->author,
				$aseco->formatTime($aseco->server->maps->current->authortime),
				$aseco->formatTime($aseco->server->maps->current->goldtime),
				$aseco->formatTime($aseco->server->maps->current->silvertime),
				$aseco->formatTime($aseco->server->maps->current->bronzetime),
				$aseco->server->maps->current->cost
			);
		}

		// show chat message
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nextmap ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			return;
		}

		// Check jukebox first
		if (isset($aseco->plugins['PluginRaspJukebox']) && !empty($aseco->plugins['PluginRaspJukebox']->jukebox)) {
			$jbtemp = $aseco->plugins['PluginRaspJukebox']->jukebox;
			$map = array_shift($jbtemp);

			$map = $aseco->server->maps->getMapByUid($map['uid']);
			$next = $map->name;
			$env = $map->environment;
		}
		else {
			$map = $aseco->server->maps->getNextMap();
			$next = $map->name;
			$env = $map->environment;
		}

		// Show chat message
		$message = $aseco->formatText($aseco->getChatMessage('NEXT_MAP'),
			$env,
			$aseco->stripColors($next)
		);
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_playtime ($aseco, $login, $chat_command, $chat_parameter) {

		$name = $aseco->stripColors($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error == '') {
			$name = '$l[http://'. $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name . '$l';
		}

		// compute map playing time
		$playtime = time() - $aseco->server->maps->current->starttime;
		$totaltime = time() - $aseco->server->starttime;

		// show chat message
		$message = $aseco->formatText($aseco->getChatMessage('PLAYTIME'),
			$name,
			$aseco->formatTime($playtime * 1000, false)
		);
		if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->replays_total > 0) {
			$message .= $aseco->formatText($aseco->getChatMessage('PLAYTIME_REPLAY'),
				$aseco->plugins['PluginRaspJukebox']->replays_total,
				($aseco->plugins['PluginRaspJukebox']->replays_total == 1 ? '' : 's'),
				$aseco->formatTime($totaltime * 1000, false)
			);
		}

		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_time ($aseco, $login, $chat_command, $chat_parameter) {
		// show chat message
		$message = $aseco->formatText($aseco->getChatMessage('TIME'),
			date('H:i:s T'),
			date('Y/M/d')
		);
		$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $data) {

		if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
			$aseco->plugins['PluginRaspJukebox']->replays_counter = 0;
			$aseco->plugins['PluginRaspJukebox']->replays_total = 0;
		}
		$aseco->server->starttime = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// check for divider message
		if ($aseco->settings['show_curmap'] > 0) {
			$name = $aseco->stripColors($map->name);
			if (isset($map->mx->error) && $map->mx->error == '') {
				$name = '$l[http://' . $map->mx->prefix .'.mania-exchange.com/tracks/view/'. $map->mx->id .']'. $name . '$l';
			}

			// compile message
			$message = $aseco->formatText($aseco->getChatMessage('CURRENT_MAP'),
				$name,
				$map->author,
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $map->gbx->authorScore : $aseco->formatTime($map->authortime))
			);

			// show chat message
			if ($aseco->settings['show_curmap'] == 2) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, false));
			}
			else {
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $data) {

		// remember time this map starts playing
		$aseco->server->maps->current->starttime = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Skip if TimeAttack/Stunts mode (always same playing time), or if disabled
		if ($aseco->settings['show_playtime'] == 0 || $aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK || $aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			return;
		}

		$name = $aseco->stripColors($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error == '') {
			$name = '$l[http://' . $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name .'$l';
		}

		// Compute map playing time
		$playtime = time() - $aseco->server->maps->current->starttime;
		$playtime = $aseco->formatTime($playtime * 1000);
		$totaltime = time() - $aseco->server->starttime;
		$totaltime = $aseco->formatTime($totaltime * 1000);

		// Show chat message
		$message = $aseco->formatText($aseco->getChatMessage('PLAYTIME_FINISH'),
			$name,
			$playtime
		);
		if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->replays_total > 0) {
			$message .= $aseco->formatText($aseco->getChatMessage('PLAYTIME_REPLAY'),
				$aseco->plugins['PluginRaspJukebox']->replays_total,
				($aseco->plugins['PluginRaspJukebox']->replays_total == 1 ? '' : 's'),
				$totaltime
			);
		}

		if ($aseco->settings['show_playtime'] == 2) {
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}

		if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
			// Log console message
			if ($aseco->plugins['PluginRaspJukebox']->replays_total == 0) {
				$aseco->console('[Map] The Map [{1}] finished after {2}',
					$aseco->stripColors($aseco->server->maps->current->name, false),
					$playtime
				);
			}
			else {
				$aseco->console('[Map] The Map [{1}] finished after {2} ({3} replay{4}, total {5})',
					$aseco->stripColors($aseco->server->maps->current->name, false),
					$playtime,
					$aseco->plugins['PluginRaspJukebox']->replays_total,
					($aseco->plugins['PluginRaspJukebox']->replays_total == 1 ? '' : 's'),
					$totaltime
				);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTimePlayingMap ($aseco) {

		// return map playing time
		return (time() - $aseco->server->maps->current->starttime);
	}
}

?>
