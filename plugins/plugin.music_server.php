<?php
/*
 * Plugin: Music Server
 * ~~~~~~~~~~~~~~~~~~~~
 * » Handles all server-controlled music.
 * » Based upon plugin.musicserver.php from XAseco2/1.03 written by Xymph
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

	require_once('includes/musicserver/getid3/getid3.php');

	// Start the plugin
	$_PLUGIN = new PluginMusicServer();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMusicServer extends Plugin {
	public $server;
	public $songs;
	public $tags;
	public $current;
	public $override;
	public $autonext;
	public $autoshuffle;
	public $allowjb;
	public $stripdirs;
	public $stripexts;
	public $cachetags;
	public $cacheread;
	public $cachefile;
	public $mannext;
	public $jukebox;
	public $messages;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.3');
		$this->setBuild('2020-07-06');
		$this->setCopyright('2014 - 2020 by undef.de');
		$this->setDescription(new Message('plugin.music_server', 'plugin_description'));

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onShutdown',			'onShutdown');
		$this->registerEvent('onEndMap',			'onEndMap');

		// handles action id's "-2101"-"-4000" for selecting from max. 1900 songs
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');

		$this->registerChatCommand('music', 'chat_music', new Message('plugin.music_server', 'slash_chat_music_description'), Player::PLAYERS);

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// read & parse config file
		$aseco->console('[MusicServer] ********************************************************');
		$aseco->console('[MusicServer] Load music server config [config/music_server.xml]');
		if (!$settings = $aseco->parser->xmlToArray('config/music_server.xml', true, true)) {
			trigger_error('[MusicServer] Could not read/parse Music server config file [config/music_server.xml]!', E_USER_ERROR);
		}
		$settings = $settings['SETTINGS'];
		unset($settings['SETTINGS']);

		$this->override		= $aseco->string2bool($settings['OVERRIDE_MAP'][0]);
		$this->autonext		= $aseco->string2bool($settings['AUTO_NEXTSONG'][0]);
		$this->autoshuffle	= $aseco->string2bool($settings['AUTO_SHUFFLE'][0]);
		$this->allowjb		= $aseco->string2bool($settings['ALLOW_JUKEBOX'][0]);
		$this->stripdirs	= $aseco->string2bool($settings['STRIP_SUBDIRS'][0]);
		$this->stripexts	= $aseco->string2bool($settings['STRIP_EXTS'][0]);
		$this->cachetags	= $aseco->string2bool($settings['CACHE_TAGS'][0]);
		$this->cacheread	= $aseco->string2bool($settings['CACHE_READONLY'][0]);

		$this->cachefile	= $settings['CACHE_FILE'][0];
		$this->server		= $settings['MUSIC_SERVER'][0];
		$this->maxlen		= $settings['MAXLEN'][0];
		$this->localpath	= str_replace('UserData', 'GameData', $aseco->server->gamedir);		// https://forum.maniaplanet.com/viewtopic.php?p=217924#p217924

		$this->stream_context = stream_context_create(
			array(
				'ssl'		=> array(
					'verify_peer'		=> true,
					'verify_peer_name'	=> true,
					'allow_self_signed'	=> true,
					'SNI_enabled'		=> true,
				),
				'http'		=> array(
					'ignore_errors'		=> false,
					'method'		=> 'GET',
					'timeout'		=> 10,
					'follow_location'	=> true,
					'max_redirects'		=> 20,
					'protocol_version'	=> 1.1,
					'user_agent'		=> 'MusicServer/'. $this->getVersion() .' '. USER_AGENT,
				),
			)
		);


		// Check for remote or local path
		if ($this->checkRemoteLocal($this->server)) {
			// Remote: append / if missing
			if (substr($this->server, -1) !== '/') {
				$this->server .= '/';
			}
		}
		else {
			// Local: append DIRECTORY_SEPARATOR if missing
			if (substr($this->server, -1) !== DIRECTORY_SEPARATOR) {
				$this->server .= DIRECTORY_SEPARATOR;
			}
		}

		$this->songs = array();
		if (isset($settings['SONG_FILES'][0]['SONG']) === true) {
			foreach ($settings['SONG_FILES'][0]['SONG'] as $song) {
				$this->songs[] = $song;
			}
			unset($settings['SONG_FILES'][0]['SONG']);

			// remove duplicates
			$this->songs = array_values(array_unique($this->songs));

			// randomize list
			if ($this->autoshuffle) {
				shuffle($this->songs);
			}
		}

		$this->mannext = false;
		$this->current = 0;
		$this->jukebox = array();
		$this->tags = array();

		if ($this->cachetags) {
			$this->refreshTags($aseco);
		}
		$aseco->console('[MusicServer] ********************************************************');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		// Leave actions outside -4000 - -2101 to other handlers
		$action = (int) $answer['Action'];
		if ($action >= -4000 && $action <= -2101) {
			// Get Player
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				// Jukebox selected song
				$aseco->releaseChatCommand('/music '. (abs($action) - 2100), $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {

		// Disable music
		$aseco->client->query('SetForcedMusic', false, '');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// check for manual next song by admin
		if ($this->mannext) {
			$this->mannext = false;
			return;
		}

		// check for jukeboxed song
		if (!empty($this->jukebox)) {
			$next = array_shift($this->jukebox);

			// check remote or local song access
			$song = $this->setNextSong($next);
			if ($this->checkRemoteLocal($this->server) && !$this->httpHead($this->server . $song)) {
				$aseco->console('[MusicServer] Could not access remote song ['. $this->server . $song .']!!!');
			}
			else if (!$this->checkRemoteLocal($this->server) && !file_exists($this->localpath . $song)) {
				$aseco->console('[MusicServer] Could not access local song ['. $this->localpath . $song .']!!!');
			}
			else {
				// log console message
				$aseco->console('[MusicServer] Setting next song to ['. $song .']');

				// load next song
				$aseco->client->query('SetForcedMusic', $this->override, $this->server . $song);
				return;
			}
		}

		// check for automatic next song
		if ($this->autonext === true) {
			// check remote or local song access
			$song = $this->getNextSong();
			if ($this->checkRemoteLocal($this->server) && !$this->httpHead($this->server . $song)) {
				$aseco->console('[MusicServer] Could not access remote song ['. $this->server . $song .']!!!');
			}
			else if (!$this->checkRemoteLocal($this->server) && !file_exists($this->localpath . $song)) {
				$aseco->console('[MusicServer] Could not access local song ['. $this->localpath . $song .']!!!');
			}
			else {
				// log console message
				$aseco->console('[MusicServer] Setting next song to ['. $song .']');

				// load next song
				$aseco->client->query('SetForcedMusic', $this->override, $this->server . $song);
			}
		}
		else {
			// disable next song
			$aseco->client->query('SetForcedMusic', $this->override, '');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function refreshTags ($aseco) {

		// read tags cache, if present
		if ($cache = $aseco->parser->xmlToArray($this->cachefile, true, true)) {
			if (isset($cache['TAGS']['SONG'])) {
				foreach ($cache['TAGS']['SONG'] as $song) {
					$this->tags[$song['FILE'][0]] = array(
						'Title'		=> $song['TITLE'][0],
						'Artist'	=> $song['ARTIST'][0]
					);
				}
			}
		}

		// Define full path to server
		if ($this->checkRemoteLocal($this->server)) {
			$server = $this->server;
			$remote_file = true;
		}
		else {
			$server = $this->localpath . $this->server;
			$remote_file = false;
		}

		// Init
		$getID3 = new getID3;

		// Check all .OGG songs for cached or new tags
		$aseco->console('[MusicServer] Parsing OGG files for ID3-Tags:');
		foreach ($this->songs as $song) {
			if (strtoupper(substr($song, -4)) === '.OGG') {
				if (!isset($this->tags[$song])) {
					$aseco->console('[MusicServer] » Parsing "'. $server.$song .'"');
					if ($remote_file === true) {
						$fileparts = false;
						if ($this->maxlen > 0) {
							// Get only the configured bytes from the song/file
							$fileparts = file_get_contents($server.str_replace(' ', '%20', $song), false, $this->stream_context, 0, $this->maxlen);
						}
						else {
							$fileparts = file_get_contents($server.str_replace(' ', '%20', $song), false, $this->stream_context);
						}
						if ($fileparts !== false) {
							$tmpfname = tempnam('./', $song .'_');
							file_put_contents($tmpfname, $fileparts);

							// Get the ID3 tag
							$id3 = $getID3->analyze($tmpfname);

							// Remove temporary file
							unlink($tmpfname);
						}
					}
					else {
						// Get the ID3 tag
						$id3 = $getID3->analyze($server.$song);
					}

					$this->tags[$song] = array(
						'Title'		=> ((isset($id3['tags']['vorbiscomment']['title'][0]) && !empty($id3['tags']['vorbiscomment']['title'][0])) ? $id3['tags']['vorbiscomment']['title'][0] : ''),
						'Artist'	=> ((isset($id3['tags']['vorbiscomment']['artist'][0]) && !empty($id3['tags']['vorbiscomment']['artist'][0])) ? $id3['tags']['vorbiscomment']['artist'][0] : ''),
					);
				}
				else {
					$aseco->console('[MusicServer] » Skip "'. $server.$song .'": already parsed');
				}
			}
			else {
				$aseco->console('[MusicServer] » Skip "'. $server.$song .'": not a OGG-File');
			}
		}

		// check for read-only cache
		if ($this->cacheread) {
			return;
		}

		// compile updated tags cache
		$list = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'. CRLF;
		$list .= '<tags>'. CRLF;

		foreach ($this->tags as $song => $tags) {
			$list .= "\t<song>". CRLF;
			$list .= "\t\t<file>". $song ."</file>". CRLF;
			$list .= "\t\t<artist>". utf8_encode($tags['Artist']) ."</artist>". CRLF;
			$list .= "\t\t<title>". utf8_encode($tags['Title']) ."</title>". CRLF;
			$list .= "\t</song>". CRLF;
		}
		$list .= '</tags>'. CRLF;

		// write out cache file
		if (!@file_put_contents($this->cachefile, $list)) {
			trigger_error('[MusicServer] Could not write music tags cache ['. $this->cachefile .']!', E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_music ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		$arglist = $chat_parameter;
		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));
		if (!isset($command['params'][1])) {
			$command['params'][1] = '';
		}

		// get masteradmin/admin/operator titles
		if ($aseco->isMasterAdmin($player)) {
			$logtitle = 'MasterAdmin';
			$chattitle = $aseco->titles['MASTERADMIN'][0];
		}
		else {
			if ($aseco->isAdmin($player)) {
				$logtitle = 'Admin';
				$chattitle = $aseco->titles['ADMIN'][0];
			}
			else {
				if ($aseco->isOperator($player)) {
					$logtitle = 'Operator';
					$chattitle = $aseco->titles['OPERATOR'][0];
				}
			}
		}

		if ($command['params'][0] === 'help') {

			$header = '{#black}/music$g handles server music:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}settings',
			                'Displays current music settings');
			$help[] = array('...', '{#black}list',
			                'Displays all available songs');
			$help[] = array('...', '{#black}list <xxx>',
			                'Searches song names/tags for <xxx>');
			$help[] = array('...', '{#black}current',
			                'Shows the current song');
		if ($aseco->allowAbility($player, 'chat_musicadmin')) {
			$help[] = array('...', '{#black}reload',
			                'Reloads music_server.xml config file');
			$help[] = array('...', '{#black}next',
			                'Skips to next song (upon next map)');
			$help[] = array('...', '{#black}sort',
			                'Sorts the song list');
			$help[] = array('...', '{#black}shuffle',
			                'Randomizes the song list');
			$help[] = array('...', '{#black}override',
			                'Changes map override setting');
			$help[] = array('...', '{#black}autonext',
			                'Changes automatic next song setting');
			$help[] = array('...', '{#black}autoshuffle',
			                'Changes automatic shuffle setting');
			$help[] = array('...', '{#black}allowjb',
			                'Changes allow jukebox setting');
			$help[] = array('...', '{#black}stripdirs',
			                'Changes strip subdirs setting');
			$help[] = array('...', '{#black}stripexts',
			                'Changes strip extensions setting');
			$help[] = array('...', '{#black}off',
			                'Disables music, auto next & jukebox');
		}
			$help[] = array('...', '{#black}jukebox/jb',
			                'Displays upcoming songs in jukebox');
			$help[] = array('...', '{#black}drop',
			                'Drops your currently added song');
			$help[] = array('...', '{#black}##',
			                'Adds a song to jukebox where ## is');
			$help[] = array('', '',
			                'the song Id from {#black}/music list');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.9, 0.05, 0.2, 0.65), 'OK');
		}
		else if ($command['params'][0] === 'settings') {

			$header = 'Music server settings:';
			$info = array();
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$info[] = array('Server', $this->server);
			}
			// get current song and strip server path
			$current = $aseco->client->query('GetForcedMusic');
			if ($current['Url'] !== '' || $current['File'] !== '') {
				//$current = preg_replace('|^' . $this->server . '|', '',
				$current = str_replace($this->server, '',
					($current['Url'] !== '' ? $current['Url'] : $current['File'])
				);
				if ($this->cachetags && isset($this->tags[$current])) {
					$tags = $this->tags[$current];
				}
				if ($this->stripdirs) {
					$current = preg_replace('|.*[/\\\\]|', '', $current);
				}
				if ($this->stripexts) {
					$current = preg_replace('|\.[^.]+$|', '', $current);
				}
			}
			else {
				$current = 'In-game music';
			}

			$info[] = array('Current', $current);
			if ($this->cachetags && isset($tags)) {
				$info[] = array('', $tags['Title'] . '{#black} by $g' . $tags['Artist']);
			}
			$info[] = array('Override', $aseco->bool2string($this->override));
			$info[] = array('AutoNext', $aseco->bool2string($this->autonext));
			$info[] = array('AutoShuffle', $aseco->bool2string($this->autoshuffle));
			$info[] = array('AllowJB', $aseco->bool2string($this->allowjb));
			$info[] = array('StripDirs', $aseco->bool2string($this->stripdirs));
			$info[] = array('StripExts', $aseco->bool2string($this->stripexts));
			$info[] = array('CacheTags', $aseco->bool2string($this->cachetags));
			$info[] = array('CacheRead', $aseco->bool2string($this->cacheread));
			$info[] = array('CacheFile', $this->cachefile);

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x32_1', 'Sound'), $info, array(1.0, 0.23, 0.77), 'OK');
		}
		else if ($command['params'][0] === 'list') {

			// check for search parameter
			if (isset($command['params'][1])) {
				$search = $command['params'][1];
			}
			else {
				$search = '';
			}

			$head = 'Songs On This Server:';
			$page = array();
			$page[] = array('Id', 'Filename');
			$sid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(1.0, 0.1, 0.9), array('Icons128x32_1', 'Music'));
			foreach ($this->songs as $song) {
				if ($this->cachetags && isset($this->tags[$song])) {
					$tags = $this->tags[$song];
				}
				// check for match in filename or, if available, title & artist tags
				if ($search === '') {
					$pos = 0;
				}
				else {
					$pos = stripos($song, $search);
					if ($pos === false && isset($tags)) {
						$pos = stripos($tags['Title'], $search);
						if ($pos === false) {
							$pos = stripos($tags['Artist'], $search);
						}
					}
				}

				if ($pos !== false) {
					if ($this->stripdirs) {
						$song = preg_replace('|.*[/\\\\]|', '', $song);
					}
					if ($this->stripexts) {
						$song = preg_replace('|\.[^.]+$|', '', $song);
					}
					$page[] = array(str_pad($sid, 2, '0', STR_PAD_LEFT) . '.',
						// add clickable button
						(($aseco->settings['clickable_lists'] && $sid <= 1900) ?
						array('{#black}' . $song, 'PluginMusicServer?Action='. (-2100-$sid)) :  // action id
						'{#black}' . $song)
					);
					if ($this->cachetags) {
						if (isset($tags))
							$page[] = array('', $tags['Title'] . '{#black} by $g' . $tags['Artist']);
						else
							$page[] = array();
					}

					if (++$lines > ($this->cachetags ? 7 : 14)) {
						$player->msgs[] = $page;
						$lines = 0;
						$page = array();
						$page[] = array('Id', 'Filename');
					}
				}
				$sid++;
				unset($tags);
			}

			// add if last batch exists
			if (count($page) > 1) {
				$player->msgs[] = $page;
			}

			if (count($player->msgs) > 1) {
				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
			}
			else {
				$message = new Message('plugin.music_server', 'no_songs_found');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'current') {

			// get current song and strip server path
			$current = $aseco->client->query('GetForcedMusic');
			if ($current['Url'] !== '' || $current['File'] !== '') {
				//$current = preg_replace('|^' . $this->server . '|', '',
				$current = str_replace($this->server, '',
					($current['Url'] !== '' ? $current['Url'] : $current['File'])
				);

				if ($this->cachetags && isset($this->tags[$current])) {
					$tags = $this->tags[$current];
				}
				if ($this->stripdirs) {
					$current = preg_replace('|.*[/\\\\]|', '', $current);
				}
				if ($this->stripexts) {
					$current = preg_replace('|\.[^.]+$|', '', $current);
				}
				if ($this->cachetags && isset($tags)) {
					$current .= '{#music} : {#highlite}' . $tags['Title'] . '{#music} by {#highlite}' . $tags['Artist'];
				}
			}
			else {
				$current = 'In-game music';
			}

			// show chat message
			$message = new Message('plugin.music_server', 'current');
			$message->addPlaceholders($current);
			$message->sendChatMessage($player->login);
		}
		else if ($command['params'][0] === 'reload') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				// read & parse config file
				if (!$settings = $aseco->parser->xmlToArray('config/music_server.xml', true, true)) {
					trigger_error('[MusicServer] Could not read/parse Music server config file config/music_server.xml !', E_USER_WARNING);
					$message = new Message('plugin.music_server', 'error_config');
					$message->sendChatMessage($player->login);
					return;
				}
				$this->onSync($aseco);
				if ($this->cachetags) {
					$this->refreshTags($aseco);
				}

				// log console message
				$aseco->console('[MusicServer] {1} [{2}] reloaded config {3} !', $logtitle, $player->login, 'music_server.xml');

				// show chat message
				$message = new Message('plugin.music_server', 'reloaded');
				$message->addPlaceholders($chattitle,
					$player->nickname
				);
				$message->sendChatMessage();

				// throw 'musicbox reloaded' event
				$aseco->releaseEvent('onMusicboxReloaded', null);
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'next') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				// check remote or local song access
				$song = $this->getNextSong();
				if ($this->checkRemoteLocal($this->server) && !$this->httpHead($this->server . $song)) {
					$aseco->console('[MusicServer]3 Could not access remote song ['. $this->server . $song .']!!!');
				}
				else if (!$this->checkRemoteLocal($this->server) && !file_exists($this->localpath . $song)) {
					$aseco->console('[MusicServer]3 Could not access local song ['. $this->localpath . $song .']!!!');
				}
				else {
					// load next song
					$aseco->client->query('SetForcedMusic', $this->override, $this->server . $song);
					$this->mannext = true;
					$song = $this->getCurrentSong();

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] loaded next song [{3}]', $logtitle, $player->login, $song);

					// show chat message
					if ($this->stripdirs) {
						$song = preg_replace('|.*[/\\\\]|', '', $song);
					}
					if ($this->stripexts) {
						$song = preg_replace('|\.[^.]+$|', '', $song);
					}
					$message = new Message('plugin.music_server', 'next');
					$message->addPlaceholders($chattitle,
						$player->nickname,
						$song
					);
					$message->sendChatMessage();
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'sort') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				// sort songs list and clear jukebox
				sort($this->songs);
				$this->jukebox = array();

				// log console message
				$aseco->console('[MusicServer] {1} [{2}] sorted song list!', $logtitle, $player->login);

				// show chat message
				$message = new Message('plugin.music_server', 'sorted');
				$message->addPlaceholders($chattitle,
					$player->nickname
				);
				$message->sendChatMessage();
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'shuffle') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				// randomize songs list and clear jukebox
				shuffle($this->songs);
				$this->jukebox = array();

				// log console message
				$aseco->console('[MusicServer] {1} [{2}] shuffled song list!', $logtitle, $player->login);

				// show chat message
				$message = new Message('plugin.music_server', 'shuffled');
				$message->addPlaceholders($chattitle,
					$player->nickname
				);
				$message->sendChatMessage();
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'override' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->override = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set music override {3} !', $logtitle, $player->login, ($this->override ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'override');
					$message->addPlaceholders(($this->override ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'autonext' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->autonext = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set music autonext {3} !', $logtitle, $player->login, ($this->autonext ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'auto_next');
					$message->addPlaceholders(($this->autonext ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'autoshuffle' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->autoshuffle = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set music autoshuffle {3} !', $logtitle, $player->login, ($this->autoshuffle ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'auto_shuffle');
					$message->addPlaceholders(($this->autoshuffle ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'allowjb' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->allowjb = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set allow music jukebox {3} !', $logtitle, $player->login, ($this->allowjb ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'allow_jb');
					$message->addPlaceholders(($this->allowjb ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'stripdirs' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->stripdirs = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set strip subdirs {3} !', $logtitle, $player->login, ($this->stripdirs ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'strip_dirs');
					$message->addPlaceholders(($this->stripdirs ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'stripexts' && $command['params'][1] !== '') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				$param = strtoupper($command['params'][1]);
				if ($param === 'ON' || $param === 'OFF') {
					$this->stripexts = ($param === 'ON');

					// log console message
					$aseco->console('[MusicServer] {1} [{2}] set strip extensions {3} !', $logtitle, $player->login, ($this->stripexts ? 'ON' : 'OFF'));

					// show chat message
					$message = new Message('plugin.music_server', 'strip_exts');
					$message->addPlaceholders(($this->stripexts ? (new Message('plugin.music_server', 'enabled'))->finish($player->login) : (new Message('plugin.music_server', 'disabled'))->finish($player->login)));
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'off') {

			// check for admin ability
			if ($aseco->allowAbility($player, 'chat_musicadmin')) {
				// disable music
				$aseco->client->query('SetForcedMusic', $this->override, '');

				// disable autonext and jukebox
				$this->autonext = false;
				$this->allowjb = false;
				$this->jukebox = array();

				// log console message
				$aseco->console('[MusicServer] {1} [{2}] disabled music & song jukebox!', $logtitle, $player->login);

				// show chat message
				$message = new Message('plugin.music_server', 'shutdown');
				$message->addPlaceholders($chattitle,
					$player->nickname
				);
				$message->sendChatMessage();
			}
			else {
				$message = new Message('plugin.music_server', 'no_admin');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'jukebox' || $command['params'][0] === 'jb') {

			if (!empty($this->jukebox)) {
				$head = 'Upcoming songs in the jukebox:';
				$page = array();
				$page[] = array('Id', 'Filename');
				$sid = 1;
				$lines = 0;
				$player->msgs = array();
				$player->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x32_1', 'Music'));
				foreach ($this->jukebox as $sid) {
					$song = $this->getCurrentSong($sid);
					if ($this->stripdirs) {
						$song = preg_replace('|.*[/\\\\]|', '', $song);
					}
					if ($this->stripexts) {
						$song = preg_replace('|\.[^.]+$|', '', $song);
					}
					$page[] = array(str_pad($sid, 2, '0', STR_PAD_LEFT) . '.',
					                '{#black}' . $song);
					$sid++;
					if (++$lines > 14) {
						$player->msgs[] = $page;
						$lines = 0;
						$page = array();
						$page[] = array('Id', 'Filename');
					}
				}

				// add if last batch exists
				if (count($page) > 1) {
					$player->msgs[] = $page;
				}

				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
			}
			else {
				$message = new Message('plugin.music_server', 'strip_exts');
				$message->sendChatMessage($player->login);
			}
		}
		else if ($command['params'][0] === 'drop') {

			// check for a song by this player
			if (array_key_exists($player->login, $this->jukebox)) {
				// delete song from jukebox
				$sid = $this->jukebox[$player->login];
				unset($this->jukebox[$player->login]);
				$song = $this->getCurrentSong($sid);

				// show chat message
				if ($this->stripdirs) {
					$song = preg_replace('|.*[/\\\\]|', '', $song);
				}
				if ($this->stripexts) {
					$song = preg_replace('|\.[^.]+$|', '', $song);
				}
				$message = new Message('plugin.music_server', 'jukebox_drop');
				$message->addPlaceholders($aseco->stripStyles($player->nickname),
					$song
				);
				$message->sendChatMessage();
			}
			else {
				$message = new Message('plugin.music_server', 'jukebox_nodrop');
				$message->sendChatMessage($player->login);
			}
		}
		else if (is_numeric($command['params'][0])) {

			// check whether jukeboxing is allowed
			if ($this->allowjb) {
				// check song ID
				$sid = intval($command['params'][0]);
				if ($sid > 0 && $sid <= count($this->songs)) {
					// check for song by this player in jukebox
					if (!array_key_exists($player->login, $this->jukebox)) {
						// check if song is already queued in jukebox
						if (!in_array($sid, $this->jukebox)) {
							// jukebox song
							$this->jukebox[$player->login] = $sid;
							$song = $this->getCurrentSong($sid);

							// show chat message
							if ($this->stripdirs) {
								$song = preg_replace('|.*[/\\\\]|', '', $song);
							}
							if ($this->stripexts) {
								$song = preg_replace('|\.[^.]+$|', '', $song);
							}
							$message = new Message('plugin.music_server', 'jukebox');
							$message->addPlaceholders($aseco->stripStyles($player->nickname),
								$song
							);
							$message->sendChatMessage();
						}
						else {
							$message = new Message('plugin.music_server', 'jukebox_dupl');
							$message->sendChatMessage($player->login);
						}
					}
					else {
						$message = new Message('plugin.music_server', 'jukebox_already');
						$message->sendChatMessage($player->login);
					}
				}
				else {
					$message = new Message('plugin.music_server', 'jukebox_notfound');
					$message->sendChatMessage($player->login);
				}
			}
			else {
				$message = new Message('plugin.music_server', 'no_jukebox');
				$message->sendChatMessage($player->login);
			}
		}
		else {
			$message = new Message('plugin.music_server', 'unknown_cmd');
			$message->addPlaceholders($arglist);
			$message->sendChatMessage($player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkRemoteLocal ($location) {

		$result = parse_url($location);
		if ((isset($result['scheme']) && !empty($result['scheme'])) || (isset($result['host']) && !empty($result['host']))) {
			return true;
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentSong ($sid = false) {

		if ($sid === false) {
			return $this->songs[$this->current];
		}
		else {
			return $this->songs[--$sid];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getNextSong () {

		$this->current++;
		if ($this->current === count($this->songs)) {
			$this->current = 0;
		}

		return $this->songs[$this->current];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setNextSong ($sid) {
		global $aseco;

		$sid--;
		$this->current++;
		if ($this->current === count($this->songs)) {
			$this->current = 0;
		}

		if ($sid !== $this->current) {
			$aseco->moveArrayElement($this->songs, $sid, $this->current);
		}

		return $this->songs[$this->current];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function httpHead ($url) {
		global $aseco;

		// http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
		$http_status_codes = array(200, 206, 301, 302, 303, 304, 307, 308);

		try {
			// Start sync request
			$params = array(
				'url'			=> $url,
				'user_agent'		=> 'MusicServer/'. $this->getVersion() .' '. USER_AGENT,
			);

			$request = $aseco->webrequest->HEAD($params);
			if (isset($request->response['header']['code']) && in_array($request->response['header']['code'], $http_status_codes)) {
				return true;
			}
			return false;
		}
		catch (Exception $exception) {
			$aseco->console('[MusicServer] webrequest->HEAD(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
	}
}

?>
