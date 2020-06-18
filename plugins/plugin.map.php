<?php
/*
 * Plugin: Map
 * ~~~~~~~~~~~
 * » Times playing time of a map, provides map and time info and shows (file)names of current map's and song mod.
 * » Based upon plugin.map.php from XAseco2/1.03 written by Xymph
 *   and chat.songmod.php from XAseco2/1.03 written by Xymph
 *   and plugin.rasp_nextmap.php from XAseco2/1.03 updated by Xymph and AssemblerManiac
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

		$this->setAuthor('undef.de');
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.2');
		$this->setBuild('2019-10-03');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.map', 'plugin_description'));

		$this->addDependence('PluginRaspJukebox',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onLoadingMap',	'onLoadingMap');
		$this->registerEvent('onRestartMap',	'onRestartMap');
		$this->registerEvent('onEndMap',	'onEndMap');

		$this->registerChatCommand('map',	'chat_map',		new Message('plugin.map','slash_chat_map_description'),		Player::PLAYERS);
		$this->registerChatCommand('song',	'chat_song',		new Message('plugin.map','slash_chat_song_description'),	Player::PLAYERS);
		$this->registerChatCommand('mod',	'chat_mod',		new Message('plugin.map','slash_chat_mod_description'),		Player::PLAYERS);
		$this->registerChatCommand('nextmap',	'chat_nextmap',		new Message('plugin.map','slash_chat_nextmap_description'),	Player::PLAYERS);
		$this->registerChatCommand('playtime',	'chat_playtime',	new Message('plugin.map','slash_chat_playtime_description'),	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_map ($aseco, $login, $chat_command, $chat_parameter) {

		$name = $aseco->stripStyles($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error === '') {
			$name = '$l[https://'. $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name .'$l';
		}

		$message = new Message('plugin.map', 'map');
		$message->addPlaceholders($name,
			$aseco->server->maps->current->author,
			$aseco->formatTime($aseco->server->maps->current->author_time),
			$aseco->formatTime($aseco->server->maps->current->gold_time),
			$aseco->formatTime($aseco->server->maps->current->silver_time),
			$aseco->formatTime($aseco->server->maps->current->bronze_time),
			$aseco->server->maps->current->cost
		);

		// show chat message
		$message->sendChatMessage($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_song ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check for map's song
		if ($aseco->server->maps->current->song_file) {
			$info = '';
			// Use only first parameter
			$chat_parameter = explode(' ', $chat_parameter, 2);
			if ((strtolower($chat_parameter[0]) === 'url' || strtolower($chat_parameter[0]) === 'loc') && $aseco->server->maps->current->song_url) {
				$info = LF .'{#highlite}$l['. $aseco->server->maps->current->song_url .']'. $aseco->server->maps->current->song_url .'$l';
			}
			$message = new Message('plugin.map', 'song');
			$message->addPlaceholders($aseco->stripStyles($aseco->server->maps->current->name),
				$aseco->server->maps->current->song_file . $info
			);

		}
		else {
			$message = new Message('plugin.map', 'no_song');
			if ((class_exists('PluginMusicServer')) && (is_callable('PluginMusicServer::chat_music')) ) {
				$message->addPlaceholders(new Message('plugin.map', 'no_song_proposal'));
			}
			else {
				$message->addPlaceholders('');
			}
		}

		// Show chat message
		$message->sendChatMessage($player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mod ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Check for map's mod
		if ($aseco->server->maps->current->modname) {
			$info = '';
			// Use only first parameter
			$chat_parameter = explode(' ', $chat_parameter, 2);
			if ((strtolower($chat_parameter[0]) === 'url' || strtolower($chat_parameter[0]) === 'loc') && $aseco->server->maps->current->mod_url) {
				$info = LF .'{#highlite}$l['. $aseco->server->maps->current->mod_url .']'. $aseco->server->maps->current->mod_url .'$l';
			}

			$message = new Message('plugin.map','mod');
			$message->addPlaceholders($aseco->stripStyles($aseco->server->maps->current->name),
				$aseco->server->maps->current->mod_name,
				$aseco->server->maps->current->mod_file,
				$info
			);
		}
		else {
			$message = new Message('plugin.map', 'no_mod');
		}

		// Show chat message
		$message->sendChatMessage($player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_nextmap ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = new Message('plugin.map', 'notonrelay');
			$message->sendChatMessage($login);
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
		$message = new Message('plugin.map', 'next_map');
		$message->addPlaceholders($env,
			$aseco->stripStyles($next)
		);
		$message->sendChatMessage($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_playtime ($aseco, $login, $chat_command, $chat_parameter) {

		$name = $aseco->stripStyles($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error === '') {
			$name = '$l[https://'. $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name . '$l';
		}

		$replaysTotal = '';
		// show chat message
		if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->replays_total > 0) {
			$msg = new Message('plugin.map', 'playtime_replay');
			$msg->addPlaceholders($aseco->plugins['PluginRaspJukebox']->replays_total,
				$aseco->timeString(time() - $aseco->server->starttime, true)
			);
			$replaysTotal = ' '. $msg->finish($login);
		}

		$message = new Message('plugin.map', 'playtime');
		$message->addPlaceholders($name,
			$aseco->timeString(time() - $aseco->server->maps->current->starttime, true),
			$replaysTotal
		);

		$message->sendChatMessage($login);
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

	public function onLoadingMap ($aseco, $map) {

		// Remember time this map starts playing
		$aseco->server->maps->current->starttime = time();

		// Check for divider message
		if ($aseco->settings['show_curmap'] > 0) {
			$name = $aseco->stripStyles($map->name);
			if (isset($map->mx->error) && $map->mx->error === '') {
				$name = '$l[https://' . $map->mx->prefix .'.mania-exchange.com/tracks/view/'. $map->mx->id .']'. $name . '$l';
			}

			// compile message
			$message = new Message('plugin.map', 'current_map');
			$message->addPlaceholders($name,
				$map->author,
				$aseco->formatTime($map->author_time)
			);

			// show chat message
			if ($aseco->settings['show_curmap'] === 2) {
				$aseco->releaseEvent('onSendWindowMessage', array($message->finish('en', false), false));
			}
			else {
				$message->sendChatMessage();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		// Remember time this map starts playing
		$aseco->server->maps->current->starttime = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Skip if TimeAttack mode (always same playing time), or if disabled
		if ($aseco->settings['show_playtime'] === 0 || $aseco->server->gameinfo->mode === Gameinfo::TIME_ATTACK) {
			return;
		}

		$name = $aseco->stripStyles($aseco->server->maps->current->name);
		if (isset($aseco->server->maps->current->mx->error) && $aseco->server->maps->current->mx->error === '') {
			$name = '$l[https://' . $aseco->server->maps->current->mx->prefix .'.mania-exchange.com/tracks/view/'. $aseco->server->maps->current->mx->id .']'. $name .'$l';
		}

		// Compute map playing time
		$playtime = $aseco->timeString(time() - $aseco->server->maps->current->starttime, true);
		$totaltime = $aseco->timeString(time() - $aseco->server->starttime, true);

		// Show chat message
		$replaysTotal = '';
		if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->replays_total > 0) {
			$msg = new Message('plugin.map', 'playtime_replay');
			$msg->addPlaceholders($aseco->plugins['PluginRaspJukebox']->replays_total,
				($aseco->plugins['PluginRaspJukebox']->replays_total === 1 ? '' : 's'),
				$totaltime
			);
			$replaysTotal = $msg;
		}
		$message = new Message('plugin.map', 'playtime_finish');
		$message->addPlaceholders($name,
			$playtime,
			$replaysTotal
		);

		if ($aseco->settings['show_playtime'] === 2) {
			$aseco->releaseEvent('onSendWindowMessage', array($message->finish('en',false), false));
		}
		else {
			$message->sendChatMessage();
		}

		if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
			// Log console message
			if ($aseco->plugins['PluginRaspJukebox']->replays_total === 0) {
				$aseco->console('[Map] The Map [{1}] finished after {2}',
					$aseco->stripStyles($aseco->server->maps->current->name, false),
					$playtime
				);
			}
			else {
				$aseco->console('[Map] The Map [{1}] finished after {2} ({3} replay{4}, total {5})',
					$aseco->stripStyles($aseco->server->maps->current->name, false),
					$playtime,
					$aseco->plugins['PluginRaspJukebox']->replays_total,
					($aseco->plugins['PluginRaspJukebox']->replays_total === 1 ? '' : 's'),
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
