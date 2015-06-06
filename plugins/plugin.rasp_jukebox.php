<?php
/*
 * Plugin: Rasp Jukebox
 * ~~~~~~~~~~~~~~~~~~~~
 * » Allow players to add maps to the "jukebox" so they can play favorites
 *   without waiting. Each player can only have one map in jukebox at a time.
 *   Also allows to add a map from MX, and provides related chat commands,
 *   including MX searches.
 *   Finally, handles the voting and passing for chat-based votes.
 * » Based upon plugin.rasp_jukebox.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-05-10
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
 *  - includes/rasp/mxinfosearcher.inc.php
 *  - plugins/plugin.rasp.php
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.dedimania.php
 *  - plugins/chat.records.php
 *  - plugins/plugin.rasp_votes.php
 *
 */

	require_once('includes/rasp/mxinfosearcher.inc.php');		// Provides MX searches

	// Start the plugin
	$_PLUGIN = new PluginRaspJukebox();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRaspJukebox extends Plugin {
	public $plrvotes		= array();
	public $replays_counter		= 0;
	public $replays_total		= 0;
	public $jukebox_check;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Allow players to add maps to the "jukebox" so they can play favorites without waiting.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginChatRecords',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED,	'1.0.0', null);

		// handles action id's "101"-"2000" for jukeboxing max. 1900 maps
		// handles action id's "-101"-"-2000" for listing max. 1900 authors
		// handles action id's "-2001"-"-2100" for dropping max. 100 jukeboxed maps
		// handles action id's "-6001"-"-7900" for invoking /karma on max. 1900 maps
		// handles action id's "5201"-"5700" for invoking /mxinfo on max. 500 maps
		// handles action id's "5701"-"6200" for invoking /add on max. 500 maps
		// handles action id's "6201"-"6700" for invoking /admin add on max. 500 maps
		// handles action id's "6701"-"7200" for invoking /xlist auth: on max. 500 authors
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onEndMap',	'onEndMap');
		$this->registerEvent('onLoadingMap',	'onLoadingMap');

		$this->registerChatCommand('list',	'chat_list',		'Lists maps currently on the server (see: /list help)',		Player::PLAYERS);
		$this->registerChatCommand('jukebox',	'chat_jukebox',		'Sets map to be played next (see: /jukebox help)',		Player::PLAYERS);
		$this->registerChatCommand('autojuke',	'chat_autojuke',	'Jukeboxes map from /list (see: /autojuke help)',		Player::PLAYERS);
		$this->registerChatCommand('add',	'chat_add',		'Adds a map directly from MX (<ID>)',				Player::PLAYERS);
		$this->registerChatCommand('y',		'chat_y',		'Votes Yes for a MX map or chat-based vote',			Player::PLAYERS);
		$this->registerChatCommand('history',	'chat_history',		'Shows the 10 most recently played maps',			Player::PLAYERS);
		$this->registerChatCommand('xlist',	'chat_xlist',		'Lists maps on MX (see: /xlist help)',				Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $data) {

		// Get settings
		$this->readSettings();

		// read map history from file in case of UASECO restart
		$this->jb_buffer = array();
		if ($fp = @fopen($this->maphistory_file, 'rb')) {
			while (!feof($fp)) {
				$uid = rtrim(fgets($fp));
				if ($uid != '') {
					$this->jb_buffer[] = $uid;
				}
			}
			fclose($fp);

			// keep only most recent $this->buffersize entries
			$this->jb_buffer = array_slice($this->jb_buffer, -$this->buffersize);

			// drop current (last) map as onLoadingMap() will add it back
			array_pop($this->jb_buffer);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles ManiaLink jukebox responses
	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		// leave actions outside 101 - 2000, -2000 - -101, -2100 - -2001,
		// -6001 - -7900 & 5201 - 7200 to other handlers
		$action = (int)$answer['Action'];
		if ($action >= 101 && $action <= 2000) {

			// jukebox selected map
			$aseco->releaseChatCommand('/jukebox '. ($action - 100), $login);

		}
		else if ($action >= -7900 && $action <= -6001) {

			// karma selected map
			$aseco->releaseChatCommand('/karma '. (abs($action) - 6000), $login);

		}
		else if ($action >= -2000 && $action <= -101) {
			// get player
			if ($player = $aseco->server->players->getPlayer($login)) {
				$author = $player->maplist[abs($action) - 101]['author'];

				// close main window because /list can take a while
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);

				// search for maps by author
				$aseco->releaseChatCommand('/list '. $author, $player->login);
			}
		}
		else if ($action >= -2100 && $action <= -2001) {
			// get player
			if ($player = $aseco->server->players->getPlayer($login)) {
				$login = $player->login;

				// determine admin ability to drop all jukeboxed maps
				if ($aseco->allowAbility($player, 'dropjukebox')) {

					// drop any jukeboxed map by admin
					$aseco->releaseChatCommand('/admin dropjukebox '. (abs($action) - 2000), $player->login);

					// check whether last map was dropped
					if (empty($this->jukebox)) {
						// close main window
						$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $login);
					}
					else {
						// display updated list
						$aseco->releaseChatCommand('/jukebox display', $player->login);
					}
				}
				else {
					// drop user's jukeboxed map
					$aseco->releaseChatCommand('/jukebox drop', $player->login);

					// check whether last map was dropped
					if (empty($this->jukebox)) {
						// close main window
						$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $login);
					}
					else {
						// display updated list
						$aseco->releaseChatCommand('/jukebox display', $player->login);
					}
				}
			}
		}
		else if ($action >= 5201 && $action <= 5700) {
			// get player & map ID
			if ($player = $aseco->server->players->getPlayer($login)) {
				$mxid = $player->maplist[$action - 5201]['id'];

				// /mxinfo selected map
				$aseco->releaseChatCommand('/mxinfo '. $mxid, $player->login);
			}
		}
		else if ($action >= 5701 && $action <= 6200) {
			// get player & map ID
			if ($player = $aseco->server->players->getPlayer($login)) {
				$mxid = $player->maplist[$action - 5701]['id'];

				// /add selected map
				$aseco->releaseChatCommand('/add '. $mxid, $player->login);
			}
		}
		else if ($action >= 6201 && $action <= 6700) {
			// get player & map ID
			if ($player = $aseco->server->players->getPlayer($login)) {
				$mxid = $player->maplist[$action - 6201]['id'];

				// /admin add selected map
				$aseco->releaseChatCommand('/admin add '. $mxid, $player->login);
			}
		}
		else if ($action >= 6701 && $action <= 7200) {
			// get player & map author
			if ($player = $aseco->server->players->getPlayer($login)) {
				$author = $player->maplist[$action - 6701]['author'];
				// insure multi-word author is single parameter
				$author = str_replace(' ', '%20', $author);

				// /xlist auth: selected author
				$aseco->releaseChatCommand('/xlist auth:'. $author, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		// check for relay server
		if ($aseco->server->isrelay) {
			return;
		}

		// don't duplicate replayed map in history
		if (!empty($this->jb_buffer)) {
			$previous = array_pop($this->jb_buffer);
			// put back previous map if different
			if ($previous != $map->uid) {
				$this->jb_buffer[] = $previous;
			}
		}
		// remember current map in history
		if (count($this->jb_buffer) >= $this->buffersize) {
			// drop oldest map if buffer full
			array_shift($this->jb_buffer);
		}
		// append current map to history
		$this->jb_buffer[] = $map->uid;

		// write map history to file in case of UASECO restart
		if ($fp = @fopen($this->maphistory_file, 'wb')) {
			foreach ($this->jb_buffer as $uid) {
				fwrite($fp, $uid . CRLF);
			}
			fclose($fp);
		}
		else {
			trigger_error('Could not write map history file ['. $this->maphistory_file .']!', E_USER_WARNING);
		}

		// process jukebox
		if (!empty($this->jukebox)) {
			if ($aseco->debug) {
				$aseco->console_text('onLoadingMap step1 - $map->uid: '. $map->uid);
				$aseco->console_text('onLoadingMap step1 - $this->jukebox_check: '. $this->jukebox_check);
				$aseco->console_text('onLoadingMap step1 - $this->jukebox:'. CRLF .
					print_r($this->jukebox, true)
				);
			}

			// look for current map in jukebox
			if (array_key_exists($map->uid, $this->jukebox)) {
				if ($aseco->debug) {
					$message = '[RaspJukebox] Current Map '.
						$aseco->stripColors($this->jukebox[$map->uid]['Name'], false) .' loaded - index: '.
						array_search($map->uid, array_keys($this->jukebox)
					);
					$aseco->console($message);
				}

				// check for /replay-ed map
				if ($this->jukebox[$map->uid]['source'] == 'Replay') {
					$this->replays_counter ++;
				}
				else {
					$this->replays_counter = 0;
				}
				if (substr($this->jukebox[$map->uid]['source'], -6) == 'Replay') { // AdminReplay
					$this->replays_total ++;
				}
				else {
					$this->replays_total = 0;
				}

				// remove loaded map
				$play = $this->jukebox[$map->uid];
				unset($this->jukebox[$map->uid]);

				if ($aseco->debug) {
					$aseco->console_text('onLoadingMap step2a - $this->jukebox:'. CRLF .
						print_r($this->jukebox, true)
					);
				}

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('play', $play));
			}
			else {
				// look for intended map in jukebox
				if ($this->jukebox_check != '') {
					if (array_key_exists($this->jukebox_check, $this->jukebox)) {
						if ($aseco->debug) {
							$message = '[RaspJukebox] Intended Map '.
								$aseco->stripColors($this->jukebox[$this->jukebox_check]['Name'], false) .' dropped - index: '.
								array_search($this->jukebox_check, array_keys($this->jukebox)
							);
							$aseco->console($message);
						}

						// drop stuck map
						$stuck = $this->jukebox[$this->jukebox_check];
						unset($this->jukebox[$this->jukebox_check]);

						if ($aseco->debug) {
							$aseco->console_text('onLoadingMap step2b - $this->jukebox:'. CRLF .
								print_r($this->jukebox, true)
							);
						}

						// throw 'jukebox changed' event
						$aseco->releaseEvent('onJukeboxChanged', array('drop', $stuck));
					}
					else {
						if ($aseco->debug) {
							$message = '[RaspJukebox] Intended Map '. $this->jukebox_check .' not found!';
							$aseco->console_text($message);
						}
					}
				}
			}
		}

		// remove previous MX map from server
		if ($this->mxplayed) {
			// unless it is permanent
			if (!$this->jukebox_permadd) {
				if ($aseco->debug) {
					$aseco->console_text('onLoadingMap step3 - remove: '. $this->mxplayed);
				}
				try {
					$aseco->client->query('RemoveMap', $this->mxplayed);

					// throw 'maplist changed' event
					$aseco->releaseEvent('onMapListChanged', array('unjuke', $this->mxplayed));
				}
				catch (Exception $exception) {
					$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - RemoveMap');
				}
			}
			$this->mxplayed = false;
		}
		// check whether current map was from MX
		if ($this->mxplaying) {
			// remember it for removal afterwards
			$this->mxplayed = $this->mxplaying;
			$this->mxplaying = false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// check for relay server
		if ($aseco->server->isrelay) {
			return;
		}

		// check for & cancel ongoing MX vote
		if (!empty($this->mxadd)) {
			$aseco->console('[RaspJukebox] Vote by {1} to add {2} reset!',
				$this->mxadd['login'],
				$aseco->stripColors($this->mxadd['name'], false)
			);
			$message = $this->messages['JUKEBOX_CANCEL'][0];
			if ($this->jukebox_in_window) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, true));
			}
			else {
				$aseco->sendChatMessage($message);
			}
			$this->mxadd = array();
		}

		// reset UID check
		$this->jukebox_check = '';

		// check for jukeboxed map(s)
		if (!empty($this->jukebox)) {
			if ($aseco->debug) {
				$aseco->console_text('onEndMap step1 - $this->jukebox:'. CRLF . print_r($this->jukebox, true)
				);
			}

			// skip jukeboxed map(s) if their requesters left
			if ($this->jukebox_skipleft) {
				// go over jukeboxed maps
				while ($next = array_shift($this->jukebox)) {
					// check if requester is still online, or was admin
					foreach ($aseco->server->players->player_list as $pl) {
						if ($pl->login == $next['Login'] || ($this->jukebox_adminnoskip && $aseco->isAnyAdminL($next['Login']))) {
							// found player, so proceed to play this map
							// put it back for onLoadingMap to remove
							$uid = $next['uid'];
							$this->jukebox = array_merge(array($uid => $next), $this->jukebox);
							break 2;  // exit foreach & while
						}
					}
					// player offline, so report skip
					$message = '[RaspJukebox] Skipping Next Map '. $aseco->stripColors($next['Name'], false) .' because requester '. $aseco->stripColors($next['Nick'], false) .' left';
					$aseco->console($message);
					$message = $aseco->formatText($this->messages['JUKEBOX_SKIPLEFT'][0],
						$aseco->stripColors($next['Name']),
						$aseco->stripColors($next['Nick'])
					);
					if ($this->jukebox_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, true));
					}
					else {
						$aseco->sendChatMessage($message);
					}

					// throw 'jukebox changed' event
					$aseco->releaseEvent('onJukeboxChanged', array('skip', $next));
				}

				// if jukebox went empty, bail out
				if (!isset($next)) {
					return;
				}
			}
			else {
				// just play the next map
				$next = array_shift($this->jukebox);

				// put it back for onLoadingMap to remove
				$uid = $next['uid'];
				$this->jukebox = array_merge(array($uid => $next), $this->jukebox);
			}

			// remember UID of next map to check whether it really plays
			$this->jukebox_check = $uid;

			if ($aseco->debug) {
				$aseco->console_text('onEndMap step2 - $this->jukebox_check: '. $this->jukebox_check);
				$aseco->console_text('onEndMap step2 - $this->jukebox:'. CRLF . print_r($this->jukebox, true));
			}

			// if a MX map, add it to server
			if ($next['mx']) {
				if ($aseco->debug) {
					$aseco->console_text('[RaspJukebox] '. $next['source'] .' map filename is '. $next['FileName']);
				}
				try {
					$aseco->client->query('AddMap', $next['FileName']);

					// throw 'maplist changed' event
					$aseco->releaseEvent('onMapListChanged', array('juke', $next['FileName']));
				}
				catch (Exception $exception) {
					$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - AddMap');
					return;
				}
			}

			try {
				// select jukebox/MX map as next map
				$aseco->client->query('ChooseNextMap', $next['FileName']);

				// report map change from MX or jukebox
				if ($next['mx']) {
					$logmsg = '[RaspJukebox] Setting next map to ['. $aseco->stripColors($next['Name'], false) .'], file downloaded from '. $next['source'];
					// remember it for later removal
					$this->mxplaying = $next['FileName'];
				}
				else {
					$logmsg = '[RaspJukebox] Setting next map to ['. $aseco->stripColors($next['Name'], false) .'] as requested by ['. $aseco->stripColors($next['Nick'] .']', false);
				}
				$message = $aseco->formatText($this->messages['JUKEBOX_NEXT'][0],
					$aseco->stripColors($next['Name']),
					$aseco->stripColors($next['Nick'])
				);

				$aseco->console($logmsg);
				if ($this->jukebox_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, true));
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
			catch (Exception $exception) {
				$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ChooseNextMap');
			}
		}
		else {
			// reset just in case current map was replayed
			$this->replays_counter = 0;
			$this->replays_total = 0;
		}

		// check for autosaving maplist
		if ($this->autosave_matchsettings != '') {
			try {
				$aseco->client->query('SaveMatchSettings', 'MatchSettings/'. $this->autosave_matchsettings);

				// should a random filter be added?
				if ($aseco->settings['writemaplist_random']) {
					$mapsfile = $aseco->server->mapdir .'MatchSettings/'. $this->autosave_matchsettings;
					// read the match settings file
					if (!$list = @file_get_contents($mapsfile)) {
						$aseco->console('[RaspJukebox] Could not read match settings file ['. $mapsfile .']!');
					}
					else {
						// insert random filter after <gameinfos> section
						$list = preg_replace('/<\/gameinfos>/', '$0'. CRLF . CRLF .
							"\t<filter>" . CRLF .
							"\t\t<random_map_order>1</random_map_order>" . CRLF .
							"\t</filter>",
							$list
						);

						// write out the match settings file
						if (!@file_put_contents($mapsfile, $list)) {
							$aseco->console('[RaspJukebox] Could not write match settings file ['. $mapsfile .']!');
						}
					}
				}
			}
			catch (Exception $exception) {
				$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveMatchSettings');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_list ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$command['author'] = $player;

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		// split params into array
		$arglist = preg_replace('/ +/', ' ', $chat_parameter);
		$command['params'] = explode(' ', $arglist);
		$cmdcount = count($command['params']);

		if ($cmdcount == 1 && $command['params'][0] == 'help') {
			$header = '{#black}/list$g will show maps in rotation on the server:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}nofinish',
			                'Shows maps you haven\'t completed');
			$help[] = array('...', '{#black}norank',
			                'Shows maps you don\'t have a rank on');
			$help[] = array('...', '{#black}nogold',
			                'Shows maps you didn\'t beat gold '.
			                 ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'score on' : 'time on'));
			$help[] = array('...', '{#black}noauthor',
			                'Shows maps you didn\'t beat author '.
			                 ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'score on' : 'time on'));
			$help[] = array('...', '{#black}norecent',
			                'Shows maps you didn\'t play recently');
			$help[] = array('...', '{#black}best$g/{#black}worst',
			                'Shows maps with your best/worst records');
			if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
				$help[] = array('...', '{#black}longest$g/{#black}shortest',
				                'Shows the longest/shortest maps');
			}
			$help[] = array('...', '{#black}newest$g/{#black}oldest #',
			                'Shows newest/oldest # maps (def: 50)');
			$help[] = array('...', '{#black}xxx',
			                'Where xxx is part of a map or author name');
			if ($this->feature_karma) {
				$help[] = array('...', '{#black}novote',
				                'Shows maps you didn\'t karma vote for');
				$help[] = array('...', '{#black}karma +/-#',
				                'Shows all maps with karma >= or <=');
				$help[] = array('', '',
				                'given value (example: {#black}/list karma -3$g shows all');
				$help[] = array('', '',
				                'maps with karma equal or worse than -3)');
			}
			$help[] = array();
			$help[] = array('Pick an Id number from the list, and use {#black}/jukebox #');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.1, 0.05, 0.3, 0.75), 'OK');
			return;
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'nofinish') {
			$this->getMapsNoFinish($player);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'norank') {
			$this->getMapsNoRank($player);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'nogold') {
			$this->getMapsNoGold($player);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'noauthor') {
			$this->getMapsNoAuthor($player);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'norecent') {
			$this->getMapsNoRecent($player);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'best') {
			// avoid interference from possible parameters
			$command['params'] = '';
			// display player records, best first
			$aseco->plugins['PluginChatRecords']->displayRecords($aseco, $command, true);
			return;
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'worst') {
			// avoid interference from possible parameters
			$command['params'] = '';
			// display player records, worst first
			$aseco->plugins['PluginChatRecords']->displayRecords($aseco, $command, false);
			return;
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'longest') {
			$this->getMapsByLength($player, false);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'shortest') {
			$this->getMapsByLength($player, true);
		}
		else if ($cmdcount >= 1 && $command['params'][0] == 'newest') {
			$count = 50;  // default
			if ($cmdcount == 2 && is_numeric($command['params'][1]) && $command['params'][1] > 0) {
				$count = intval($command['params'][1]);
			}
			$this->getMapsByAdd($player, true, $count);
		}
		else if ($cmdcount >= 1 && $command['params'][0] == 'oldest') {
			$count = 50;  // default
			if ($cmdcount == 2 && is_numeric($command['params'][1]) && $command['params'][1] > 0) {
				$count = intval($command['params'][1]);
			}
			$this->getMapsByAdd($player, false, $count);
		}
		else if ($cmdcount == 1 && $command['params'][0] == 'novote' && $this->feature_karma) {
			$this->getMapsNoVote($player);
		}
		else if ($cmdcount == 2 && $command['params'][0] == 'karma' && $this->feature_karma) {
			$karmaval = intval($command['params'][1]);
			$this->getMapsByKarma($player, $karmaval);
		}
		else if ($cmdcount >= 1 && strlen($command['params'][0]) > 0) {
			$aseco->plugins['PluginRasp']->getAllMaps($player, $arglist, '*');  // wildcard
		}
		else {
			$aseco->plugins['PluginRasp']->getAllMaps($player, '*', '*');  // wildcards
		}

		if (empty($player->maplist)) {
			$message = '{#server}» {#error}No maps found, try again!';
			$aseco->sendChatMessage($message, $login);
			return;
		}
		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_jukebox ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($this->feature_jukebox || $aseco->allowAbility($player, 'chat_jukebox')) {
			// check parameter
			if (is_numeric($chat_parameter) && $chat_parameter >= 0) {
				if (empty($player->maplist)) {
					$message = $this->messages['LIST_HELP'][0];
					$aseco->sendChatMessage($message, $login);
					return;
				}

				// check for map by this player in jukebox
				if (!$aseco->allowAbility($player, 'chat_jb_multi')) {
					foreach ($this->jukebox as $key) {
						if ($login == $key['Login']) {
							$message = $this->messages['JUKEBOX_ALREADY'][0];
							$aseco->sendChatMessage($message, $login);
							return;
						}
					}
				}

				// find map by given #
				$jid = ltrim($chat_parameter, '0');
				$jid--;
				if (array_key_exists($jid, $player->maplist)) {
					$uid = $player->maplist[$jid]['uid'];
					// check if map is already queued in jukebox
					if (array_key_exists($uid, $this->jukebox)) {  // find by uid in jukebox
						$message = $this->messages['JUKEBOX_DUPL'][0];
						$aseco->sendChatMessage($message, $login);
						return;
					}
					else if (in_array($uid, $this->jb_buffer)) {

						// if not an admin with this ability, bail out
						if (!$aseco->allowAbility($player, 'chat_jb_recent')) {

							// map was recently played
							$message = $this->messages['JUKEBOX_REPEAT'][0];
							$aseco->sendChatMessage($message, $login);

							return;
						}
					}

					try {
						// check map vs. server settings
						$aseco->client->query('CheckMapForCurrentServerParams', $player->maplist[$jid]['filename']);

						// add map to jukebox
						$this->jukebox[$uid]['FileName'] = $player->maplist[$jid]['filename'];
						$this->jukebox[$uid]['Name'] = $player->maplist[$jid]['name'];
						$this->jukebox[$uid]['Env'] = $player->maplist[$jid]['environment'];
						$this->jukebox[$uid]['Login'] = $player->login;
						$this->jukebox[$uid]['Nick'] = $player->nickname;
						$this->jukebox[$uid]['source'] = 'Jukebox';
						$this->jukebox[$uid]['mx'] = false;
						$this->jukebox[$uid]['uid'] = $uid;

						$message = $aseco->formatText($this->messages['JUKEBOX'][0],
							$aseco->stripColors($player->maplist[$jid]['name']),
							$aseco->stripColors($player->nickname)
						);

						if ($this->jukebox_in_window) {
							$aseco->releaseEvent('onSendWindowMessage', array($message, false));
						}
						else {
							$aseco->sendChatMessage($message);
						}

						// throw 'jukebox changed' event
						$aseco->releaseEvent('onJukeboxChanged', array('add', $this->jukebox[$uid]));
					}
					catch (Exception $exception) {
						$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - CheckMapForCurrentServerParams');

						$message = $aseco->formatText($this->messages['JUKEBOX_IGNORED'][0],
							$aseco->stripColors($player->maplist[$jid]['name']),
							$exception->getMessage()
						);
						$aseco->sendChatMessage($message, $login);
					}
				}
				else {
					$message = $this->messages['JUKEBOX_NOTFOUND'][0];
					$aseco->sendChatMessage($message, $login);
				}
			}
			else if ($chat_parameter == 'list') {
				if (!empty($this->jukebox)) {
					$message = $this->messages['JUKEBOX_LIST'][0];
					$i = 1;
					foreach ($this->jukebox as $item) {
						$message .= '{#highlite}'. $i .'{#emotic}.[{#highlite}'. $aseco->stripColors($item['Name']) .'{#emotic}], ';
						$i++;
					}
					$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "
					$aseco->sendChatMessage($message, $login);
				}
				else {
					$message = $this->messages['JUKEBOX_EMPTY'][0];
					$aseco->sendChatMessage($message, $login);
				}
			}
			else if ($chat_parameter == 'display') {
				if (!empty($this->jukebox)) {
					// determine admin ability to drop all jukeboxed maps
					$dropall = $aseco->allowAbility($player, 'dropjukebox');
					$head = 'Upcoming maps in the jukebox:';
					$page = array();
					if ($aseco->settings['clickable_lists']) {
						$page[] = array('Id', 'Name (click to drop)', 'Requester');
					}
					else {
						$page[] = array('Id', 'Name', 'Requester');
					}

					$tid = 1;
					$lines = 0;
					$player->msgs = array();

					// reserve extra width for $w tags
					$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
					$player->msgs[0] = array(1, $head, array(1.10+$extra, 0.1, 0.6+$extra, 0.4), array('Icons128x128_1', 'LoadTrack', 0.02));
					foreach ($this->jukebox as $item) {
						$mapname = $item['Name'];
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $aseco->stripColors($mapname);
						}

						// add clickable button if admin with 'dropjukebox' ability or map by this player
						if ($aseco->settings['clickable_lists'] && $tid <= 100 && ($dropall || $item['Login'] == $login)) {
							$mapname = array('{#black}'. $mapname, 'PluginRaspJukebox?Action='. (-2000-$tid));  // action id
						}
						else {
							$mapname = '{#black}'. $mapname;
						}
						$page[] = array(
							str_pad($tid, 2, '0', STR_PAD_LEFT) .'.',
							$mapname,
							'{#black}'. $aseco->stripColors($item['Nick'])
						);
						$tid++;
						if (++$lines > 14) {
							if ($aseco->allowAbility($player, 'clearjukebox')) {
								$page[] = array();
								$page[] = array('', array('{#emotic}                  Clear Entire Jukebox', 20), '');  // action id
							}
							$player->msgs[] = $page;
							$lines = 0;
							$page = array();
							if ($aseco->settings['clickable_lists']) {
								$page[] = array('Id', 'Name (click to drop)', 'Requester');
							}
							else {
								$page[] = array('Id', 'Name', 'Requester');
							}
						}
					}

					// add if last batch exists
					if (count($page) > 1) {
						if ($aseco->allowAbility($player, 'clearjukebox')) {
							$page[] = array();
							$page[] = array('', array('{#emotic}                  Clear Entire Jukebox', 20), '');  // action id
						}
						$player->msgs[] = $page;
					}

					// display ManiaLink message
					$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
				}
				else {
					$message = $this->messages['JUKEBOX_EMPTY'][0];
					$aseco->sendChatMessage($message, $login);
				}
			}
			else if ($chat_parameter == 'drop') {
				// find map by current player
				$uid = '';
				foreach ($this->jukebox as $item) {
					if ($item['Login'] == $login) {
						$name = $item['Name'];
						$uid = $item['uid'];
						break;
					}
				}
				if ($uid) {
					// drop it from the jukebox
					$drop = $this->jukebox[$uid];
					unset($this->jukebox[$uid]);

					$message = $aseco->formatText($this->messages['JUKEBOX_DROP'][0],
						$aseco->stripColors($player->nickname),
						$aseco->stripColors($name)
					);
					if ($this->jukebox_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, false));
					}
					else {
						$aseco->sendChatMessage($message);
					}

					// throw 'jukebox changed' event
					$aseco->releaseEvent('onJukeboxChanged', array('drop', $drop));
				}
				else {
					$message = $this->messages['JUKEBOX_NODROP'][0];
					$aseco->sendChatMessage($message, $login);
				}
			}
			else if ($chat_parameter == 'help') {
				$header = '{#black}/jukebox$g will add a map to the jukebox:';
				$help = array();
				$help[] = array('...', '{#black}help',
				                'Displays this help information');
				$help[] = array('...', '{#black}list',
				                'Shows upcoming maps');
				$help[] = array('...', '{#black}display',
				                'Displays upcoming maps and requesters');
				$help[] = array('...', '{#black}drop',
				                'Drops your currently added map');
				$help[] = array('...', '{#black}##',
				                'Adds a map where ## is the Map_ID');
				$help[] = array('', '',
				                'from your most recent {#black}/list$g command');
				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.9, 0.05, 0.15, 0.7), 'OK');
			}
			else {
				$message = $this->messages['JUKEBOX_HELP'][0];
				$aseco->sendChatMessage($message, $login);
			}
		}
		else {
			$message = $this->messages['NO_JUKEBOX'][0];
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_autojuke ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		// split params into array
		$chat_parameter = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));
		$cmdcount = count($chat_parameter);

		if ($cmdcount == 1 && $chat_parameter[0] == 'help') {
			$header = '{#black}/autojuke$g will jukebox a map from /list selection:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}nofinish',
			                'Selects maps you haven\'t completed');
			$help[] = array('...', '{#black}norank',
			                'Selects maps you don\'t have a rank on');
			$help[] = array('...', '{#black}nogold',
			                'Selects maps you didn\'t beat gold '.
			                 ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'score on' : 'time on'));
			$help[] = array('...', '{#black}noauthor',
			                'Selects maps you didn\'t beat author '.
			                 ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'score on' : 'time on'));
			$help[] = array('...', '{#black}norecent',
			                'Selects maps you didn\'t play recently');
			if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
				$help[] = array('...', '{#black}longest$g/{#black}shortest',
				                'Selects the longest/shortest maps');
			}
			$help[] = array('...', '{#black}newest$g/{#black}oldest',
			                'Selects the newest/oldest maps');
			if ($this->feature_karma) {
				$help[] = array('...', '{#black}novote',
				                'Selects maps you didn\'t karma vote for');
			}
			$help[] = array();
			$help[] = array('The jukeboxed map is the first one from the chosen selection');
			$help[] = array('that is not in the map history.');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.1, 0.05, 0.3, 0.75), 'OK');
			return;
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'nofinish') {
			$this->getMapsNoFinish($player);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'norank') {
			$this->getMapsNoRank($player);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'nogold') {
			$this->getMapsNoGold($player);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'noauthor') {
			$this->getMapsNoAuthor($player);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'norecent') {
			$this->getMapsNoRecent($player);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'longest') {
			$this->getMapsByLength($player, false);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'shortest') {
			$this->getMapsByLength($player, true);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'newest') {
			$this->getMapsByAdd($player, true, $this->buffersize+1);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'oldest') {
			$this->getMapsByAdd($player, false, $this->buffersize+1);
		}
		else if ($cmdcount == 1 && $chat_parameter[0] == 'novote' && $this->feature_karma) {
			$this->getMapsNoVote($player);
		}
		else {
			$message = '{#server}» {#error}Invalid selection, try again!';
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if (empty($player->maplist)) {
			$message = '{#server}» {#error}No maps found, try again!';
			$aseco->sendChatMessage($message, $login);
			return;
		}

		// find first available map
		$ctr = 1;
		$found = false;
		foreach ($player->maplist as $key) {
			if (!array_key_exists($key['uid'], $this->jukebox) && !in_array($key['uid'], $this->jb_buffer)) {
				$found = true;
				break;
			}
			$ctr++;
		}
		if ($found) {
			// jukebox it
			$aseco->releaseChatCommand('/jukebox '. $ctr, $login);
		}
		else {
			$message = '{#server}» {#highlite}'. $chat_parameter[0] .'{#error} maps currently unavailable, try again later!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_add ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether jukebox & /add are enabled
		if ($this->feature_jukebox && $this->feature_mxadd && isset($aseco->plugins['PluginRaspVotes'])) {
			// check whether this player is spectator
			if (!$this->allow_spec_startvote && $player->isspectator) {
				$message = $this->messages['NO_SPECTATORS'][0];
				$aseco->sendChatMessage($message, $login);
				return;
			}

			if ( isset($aseco->plugins['PluginRaspVotes']) ) {
				// check for ongoing MX or chat vote
				if (!empty($this->mxadd) || !empty($aseco->plugins['PluginRaspVotes']->chatvote)) {
					$message = $this->messages['VOTE_ALREADY'][0];
					$aseco->sendChatMessage($message, $login);
					return;
				}
			}

			// check for special 'mapref' parameter & write file
			if ($chat_parameter == 'mapref' && $aseco->allowAbility($player, 'chat_add_mref')) {
				$this->build_mx_mapref($aseco);
				$message = '{#server}» {#emotic}Wrote mapref.txt files';
				$aseco->sendChatMessage($message, $login);
				return;
			}

			// split params into array
			$chat_parameter = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));

			// check for valid MX ID
			if (is_numeric($chat_parameter[0]) && $chat_parameter[0] >= 0) {
				$trkid = ltrim($chat_parameter[0], '0');
				$source = 'MX';

				// try to load the map from MX
				$remotefile = 'http://tm.mania-exchange.com/tracks/download/'. $trkid;
				$response = $aseco->webaccess->request($remotefile, null, 'none');
				if ($response['Code'] == 200) {

					// check for maximum online map size
					$file = &$response['Message'];
					if (strlen($file) >= $aseco->server->maps->size_limit) {
						$message = $aseco->formatText($this->messages['MAP_TOO_LARGE'][0],
							round(strlen($file) / $aseco->server->maps->size_limit),
							$aseco->server->maps->size_limit / 1024
						);
						$aseco->sendChatMessage($message, $login);
						return;
					}
					$sepchar = substr($aseco->server->mapdir, -1, 1);
					$partialdir = $this->mxtmpdir . $sepchar . $trkid .'.Map.gbx';
					$localfile = $aseco->server->mapdir . $partialdir;
					if ($aseco->debug) {
						$aseco->console_text('/add - mxtmpdir='. $this->mxtmpdir);
						$aseco->console_text('/add - path + filename='. $partialdir);
						$aseco->console_text('/add - aseco->server->mapdir = '. $aseco->server->mapdir);
					}
					if ($nocasepath = $aseco->file_exists_nocase($localfile)) {
						if (!unlink($nocasepath)) {
							$message = '{#server}» {#error}Error erasing old file. Please contact admin.';
							$aseco->sendChatMessage($message, $login);
							return;
						}
					}
					if (!$lfile = @fopen($localfile, 'wb')) {
						$message = '{#server}» {#error}Error creating file. Please contact admin.';
						$aseco->sendChatMessage($message, $login);
						return;
					}
					if (!fwrite($lfile, $file)) {
						$message = '{#server}» {#error}Error saving file - unable to write data. Please contact admin.';
						$aseco->sendChatMessage($message, $login);
						fclose($lfile);
						return;
					}
					fclose($lfile);

					$gbx = $aseco->server->maps->parseMap($localfile);
					if ( !isset($gbx->uid) ) {
						$message = '{#server}» {#error}No such map on '. $source .'!';
						$aseco->sendChatMessage($message, $login);
						unlink($localfile);
						return;
					}

					// Check for map presence on server
					$tmp = $aseco->server->maps->getMapByUid($gbx->uid);
					if (isset($tmp->uid) && $tmp->uid == $gbx->uid) {
						$message = $aseco->plugins['PluginRasp']->messages['ADD_PRESENT'][0];
						$aseco->sendChatMessage($message, $login);
						unlink($localfile);
						return;
					}

					// Check for map presence in jukebox via previous /add
					if (isset($this->jukebox[$tmp->uid])) {
						$message = $this->messages['ADD_DUPL'][0];
						$aseco->sendChatMessage($message, $login);
						unlink($localfile);
						return;
					}

					// rename ID filename to map's name
					$md5new = md5_file($localfile);
					$filename = trim($aseco->stripColors($aseco->stripNewlines($aseco->stripBOM($gbx->name)), true));
					$filename = preg_replace('#[^A-Za-z0-9]+#u', '-', $filename);
					$filename = preg_replace('# +#u', '-', $filename);
					$filename = preg_replace('#^-#u', '', $filename);
					$filename = preg_replace('#-$#u', '', $filename);
					$partialdir = $aseco->plugins['PluginRasp']->mxdir . $sepchar . $filename .'_'. $trkid .'.Map.gbx';

					// insure unique filename by incrementing sequence number,
					// if not a duplicate map
					$i = 1;
					$dupl = false;
					while ($nocasepath = $aseco->file_exists_nocase($aseco->server->mapdir . $partialdir)) {
						$md5old = md5_file($nocasepath);
						if ($md5old == $md5new) {
							$dupl = true;
							$partialdir = str_replace($aseco->server->mapdir, '', $nocasepath);
							break;
						}
						else {
							$partialdir = $this->mxtmpdir . $sepchar . $filename .'_'. $trkid .'-'. $i++ .'.Map.gbx';
						}
					}
					if ($dupl) {
						unlink($localfile);
					}
					else {
						rename($localfile, $aseco->server->mapdir . $partialdir);
					}

					try {
						// check map vs. server settings
						$aseco->client->query('CheckMapForCurrentServerParams', $partialdir);

						// start /add vote
						$this->mxadd['filename'] = $partialdir;
						$this->mxadd['votes'] = $aseco->plugins['PluginRaspVotes']->required_votes($this->mxvoteratio);
						$this->mxadd['name'] = $gbx->name;
						$this->mxadd['environment'] = $gbx->envir;
						$this->mxadd['login'] = $player->login;
						$this->mxadd['nick'] = $player->nickname;
						$this->mxadd['source'] = $source;
						$this->mxadd['uid'] = $gbx->uid;

						// reset votes, rounds counter, TA interval counter & start time
						$this->plrvotes = array();
						if ( isset($aseco->plugins['PluginRaspVotes']) ) {
							$aseco->plugins['PluginRaspVotes']->r_expire_num = 0;
							$aseco->plugins['PluginRaspVotes']->ta_show_num = 0;
							$aseco->plugins['PluginRaspVotes']->ta_expire_start = (time() - $aseco->server->maps->current->starttime);
						}

						// compile & show chat message
						$message = $aseco->formatText($this->messages['JUKEBOX_ADD'][0],
							$aseco->stripColors($this->mxadd['nick']),
							$aseco->stripColors($this->mxadd['name']),
							$this->mxadd['source'],
							$this->mxadd['votes']
						);
						$message = str_replace('{br}', LF, $message);  // split long message
						if ($this->jukebox_in_window) {
							$aseco->releaseEvent('onSendWindowMessage', array($message, true));
						}
						else {
							$aseco->sendChatMessage($message);
						}

						// enable all vote panels
						if (function_exists('allvotepanels_on')) {
							allvotepanels_on($aseco, $login, $aseco->formatColors('{#emotic}'));
						}

						// vote automatically by vote starter?
						if ($this->auto_vote_starter) {
							$aseco->releaseChatCommand('/y '. $chat_parameter, $login);
						}
					}
					catch (Exception $exception) {
						$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - CheckMapForCurrentServerParams');

						$message = $aseco->formatText($this->messages['JUKEBOX_IGNORED'][0],
							$aseco->stripColors($gbx->name),
							$exception->getMessage()
						);
						$aseco->sendChatMessage($message, $login);
					}
				}
				else {
					$message = '{#server}» {#error}Error downloading, or MX is down!';
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				$message = '{#server}» {#error}You must include a MX map ID!';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else {
			$message = $this->messages['NO_ADD'][0];
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_y ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator but not any admin
		if (!$this->allow_spec_voting && $player->isspectator && !$aseco->isAnyAdmin($player)) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $login);
			return;
		}

		// check whether this player already voted
		if (in_array($login, $this->plrvotes)) {
			$message = '{#server}» {#error}You have already voted!';
			$aseco->sendChatMessage($message, $login);
			return;
		}

		// check for ongoing MX vote
		if (!empty($this->mxadd) && $this->mxadd['votes'] >= 0) {
			$votereq = $this->mxadd['votes'];
			$votereq--;
			// check for sufficient votes
			if ($votereq > 0) {
				// remind all players to vote
				$this->mxadd['votes'] = $votereq;
				$message = $aseco->formatText($this->messages['JUKEBOX_Y'][0],
					$votereq,
					($votereq == 1 ? '' : 's'),
					$aseco->stripColors($this->mxadd['name'])
				);
				if ($this->jukebox_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// register this player's vote
				$this->plrvotes[] = $login;
			}
			else {
				// pass, so add it to jukebox
				$uid = $this->mxadd['uid'];
				$this->jukebox[$uid]['FileName'] = $this->mxadd['filename'];
				$this->jukebox[$uid]['Name'] = $this->mxadd['name'];
				$this->jukebox[$uid]['Env'] = $this->mxadd['environment'];
				$this->jukebox[$uid]['Login'] = $this->mxadd['login'];
				$this->jukebox[$uid]['Nick'] = $this->mxadd['nick'];
				$this->jukebox[$uid]['source'] = $this->mxadd['source'];
				$this->jukebox[$uid]['mx'] = true;
				$this->jukebox[$uid]['uid'] = $uid;

				// show chat message
				$message = $aseco->formatText($this->messages['JUKEBOX_PASS'][0],
					$aseco->stripColors($this->mxadd['name'])
				);
				if ($this->jukebox_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// clear for next vote
				$this->mxadd = array();

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('add', $this->jukebox[$uid]));
			}
		}
		else if (isset($aseco->plugins['PluginRaspVotes']) && !empty($aseco->plugins['PluginRaspVotes']->chatvote) && $aseco->plugins['PluginRaspVotes']->chatvote['votes'] >= 0) {
			// check for ongoing chat vote

			$votereq = $aseco->plugins['PluginRaspVotes']->chatvote['votes'];
			$votereq--;
			// check for sufficient votes
			if ($votereq > 0) {
				// remind players to vote
				$aseco->plugins['PluginRaspVotes']->chatvote['votes'] = $votereq;
				$message = $aseco->formatText($this->messages['VOTE_Y'][0],
					$votereq,
					($votereq == 1 ? '' : 's'),
					$aseco->plugins['PluginRaspVotes']->chatvote['desc']
				);
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// register this player's vote
				$this->plrvotes[] = $login;
			}
			else {
				// show chat message
				$message = $aseco->formatText($this->messages['VOTE_PASS'][0],
					$aseco->plugins['PluginRaspVotes']->chatvote['desc']
				);
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// Pass, so perform action
				switch ($aseco->plugins['PluginRaspVotes']->chatvote['type']) {
					case 0:  // endround
						$aseco->client->query('TriggerModeScriptEvent', 'Rounds_ForceEndRound', '');
						$aseco->console('[RaspJukebox] Vote by {1} forced round end!',
							$aseco->plugins['PluginRaspVotes']->chatvote['login']
						);
						break;

					case 1:  // ladder
						if ($this->ladder_fast_restart) {

							// Simulate a onEndMap for Dedimania, otherwise new driven records are lost!
							if ( isset($aseco->plugins['PluginDedimania']) ) {
								$aseco->plugins['PluginDedimania']->onEndMap($aseco, $aseco->server->maps->current);
							}

							if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
								// don't clear scores if in Cup mode
								$aseco->client->query('RestartMap', true);
							}
							else {
								$aseco->client->query('RestartMap');
							}
						}
						else {
							// prepend current map to start of jukebox
							$uid = $aseco->server->maps->current->uid;
							$this->jukebox = array_reverse($this->jukebox, true);
							$this->jukebox[$uid]['FileName'] = $aseco->server->maps->current->filename;
							$this->jukebox[$uid]['Name'] = $aseco->server->maps->current->name;
							$this->jukebox[$uid]['Env'] = $aseco->server->maps->current->environment;
							$this->jukebox[$uid]['Login'] = $aseco->plugins['PluginRaspVotes']->chatvote['login'];
							$this->jukebox[$uid]['Nick'] = $aseco->plugins['PluginRaspVotes']->chatvote['nick'];
							$this->jukebox[$uid]['source'] = 'Ladder';
							$this->jukebox[$uid]['mx'] = false;
							$this->jukebox[$uid]['uid'] = $uid;
							$this->jukebox = array_reverse($this->jukebox, true);

							if ($aseco->debug) {
								$aseco->console_text('/ladder pass - $this->jukebox:'. CRLF .
									print_r($this->jukebox, true)
								);
							}

							// throw 'jukebox changed' event
							$aseco->releaseEvent('onJukeboxChanged', array('restart', $this->jukebox[$uid]));

							// ...and skip to it
							if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
								// don't clear scores if in Cup mode
								$aseco->client->query('NextMap', true);
							}
							else {
								$aseco->client->query('NextMap');
							}
						}
						$aseco->console('[RaspJukebox] Vote by {1} restarted map for ladder!',
							$aseco->plugins['PluginRaspVotes']->chatvote['login']
						);
						break;

					case 2:  // replay
						// prepend current map to start of jukebox
						$uid = $aseco->server->maps->current->uid;
						$this->jukebox = array_reverse($this->jukebox, true);
						$this->jukebox[$uid]['FileName'] = $aseco->server->maps->current->filename;
						$this->jukebox[$uid]['Name'] = $aseco->server->maps->current->name;
						$this->jukebox[$uid]['Env'] = $aseco->server->maps->current->environment;
						$this->jukebox[$uid]['Login'] = $aseco->plugins['PluginRaspVotes']->chatvote['login'];
						$this->jukebox[$uid]['Nick'] = $aseco->plugins['PluginRaspVotes']->chatvote['nick'];
						$this->jukebox[$uid]['source'] = 'Replay';
						$this->jukebox[$uid]['mx'] = false;
						$this->jukebox[$uid]['uid'] = $uid;
						$this->jukebox = array_reverse($this->jukebox, true);

						if ($aseco->debug) {
							$aseco->console_text('/replay pass - $this->jukebox:'. CRLF .
								print_r($this->jukebox, true)
							);
						}

						$aseco->console('[RaspJukebox] Vote by {1} replays map after finish!',
							$aseco->plugins['PluginRaspVotes']->chatvote['login']
						);

						// throw 'jukebox changed' event
						$aseco->releaseEvent('onJukeboxChanged', array('replay', $this->jukebox[$uid]));
						break;

					case 3:  // skip
						// skip immediately to next map
						if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
							// don't clear scores if in Cup mode
							$aseco->client->query('NextMap', true);
						}
						else {
							$aseco->client->query('NextMap');
						}
						$aseco->console('[RaspJukebox] Vote by {1} skips this map!',
							$aseco->plugins['PluginRaspVotes']->chatvote['login']
						);
						break;

					case 4:  // kick
						try {
							$aseco->client->query('Kick', $aseco->plugins['PluginRaspVotes']->chatvote['target']);
							$aseco->console('[RaspJukebox] Vote by {1} kicked player {2}!',
								$aseco->plugins['PluginRaspVotes']->chatvote['login'],
								$aseco->plugins['PluginRaspVotes']->chatvote['target']
							);
						}
						catch (Exception $exception) {
							$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick');
						}
						break;

					case 6:  // ignore
						try {
							$aseco->client->query('Ignore', $aseco->plugins['PluginRaspVotes']->chatvote['target']);
							// check if in global mute/ignore list
							if (!in_array($aseco->plugins['PluginRaspVotes']->chatvote['target'], $aseco->server->mutelist)) {
								// add player to list
								$aseco->server->mutelist[] = $aseco->plugins['PluginRaspVotes']->chatvote['target'];
							}
							$aseco->console('[RaspJukebox] Vote by {1} ignored player {2}!',
								$aseco->plugins['PluginRaspVotes']->chatvote['login'],
								$aseco->plugins['PluginRaspVotes']->chatvote['target']
							);
						}
						catch (Exception $exception) {
							$aseco->console('[RaspJukebox] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Ignore');
						}
						break;

					case 5:  // add - can't occur here
						break;
				}

				// clear for next vote
				$aseco->plugins['PluginRaspVotes']->chatvote = array();
			}
		}
		else {
			// all quiet on the voting front :)
			$message = '{#server}» {#error}There is no vote right now!';
			if ($this->feature_mxadd) {
				if ($this->feature_votes) {
					$message .= ' Use {#highlite}$i/add <ID>{#error} or see {#highlite}$i/helpvote{#error} to start one.';
				}
				else {
					$message .= ' Use {#highlite}$i/add <ID>{#error} to start one.';
				}
			}
			else {
				if ($this->feature_votes) {
					$message .= ' See {#highlite}$i/helpvote{#error} to start one.';
				}
				else {
					$message .= '';
				}
			}
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_history ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if (!empty($this->jb_buffer)) {
			$message = $this->messages['HISTORY'][0];
			// Loop over last 10 (max) entries in buffer
			for ($i = 1, $j = count($this->jb_buffer)-1; $i <= 10 && $j >= 0; $i++, $j--) {
				if ( isset($this->jb_buffer[$j]) ) {
					$map = $aseco->server->maps->getMapByUid($this->jb_buffer[$j]);
					$message .= '{#highlite}'. $i .'{#emotic}.[{#highlite}'. $aseco->stripColors($map->name) .'{#emotic}], ';
				}
			}

			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "
			$aseco->sendChatMessage($message, $player->login);
			return;
		}
		else {
			$message = '{#server}» {#error}No map history available!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_xlist ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// split params into array
		$chat_parameter = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));
		$cmdcount = count($chat_parameter);

		$section = 'TM2';

		if ($cmdcount == 1 && $chat_parameter[0] == 'help') {
			$header = '{#black}/xlist$g will show maps on MX:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}recent',
			                'Lists the 10 most recent maps');
			$help[] = array('...', '{#black}xxx',
			                'Lists maps matching (partial) name');
			$help[] = array('...', '{#black}auth:yyy',
			                'Lists maps matching (partial) author');
			$help[] = array('...', '{#black}xxx auth:yyy',
			                'Combines the name and author searches');
			$help[] = array();
			$help[] = array('Pick a MX Id number from the list, and use {#black}/add #');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.2, 0.05, 0.35, 0.8), 'OK');
			return;
		}
		else if ($chat_parameter[0] == 'recent') {
			// get 10 most recent maps
			$maps = new MXInfoSearcher($section, '', '', '', true);
		}
		else {
			$name = '';
			$auth = '';
			$env = $aseco->server->title;
			// collect search parameters
			foreach ($chat_parameter as $param) {
				if (strtolower(substr($param, 0, 5)) == 'auth:') {
					$auth = substr($param, 5);
				}
				else {
					if ($name == '')
						$name = $param;
					else  // concatenate words in name
						$name .= '%20'. $param;
				}
			}

			// search for matching maps
			$maps = new MXInfoSearcher($section, $name, $auth, $env, false);
		}

		// check for any results
		if (!$maps->valid()) {
			$message = '{#server}» {#error}No maps found, or MX is down!';
			$aseco->sendChatMessage($message, $login);
			if ($maps->error != '') {
				trigger_error($maps->error, E_USER_WARNING);
			}
			return;
		}
		$player->maplist = array();

		$adminadd = $aseco->allowAbility($player, 'add');
		$head = 'Maps On MX Section {#black}TM$g:';
		$msg = array();
		if ($aseco->settings['clickable_lists']) {
			if ($adminadd) {
				$msg[] = array('Id', 'MX', 'Name (click to /add)', '$nAdmin', 'Author', 'Env');
			}
			else {
				$msg[] = array('Id', 'MX', 'Name (click to /add)', 'Author', 'Env');
			}
		}
		else {
			$msg[] = array('Id', 'MX', 'Name', 'Author', 'Env');
		}

		$tid = 1;
		$lines = 0;
		$player->msgs = array();
		if ($adminadd && $aseco->settings['clickable_lists']) {
			$player->msgs[0] = array(1, $head, array(1.55, 0.12, 0.16, 0.6, 0.1, 0.4, 0.17), array('Icons128x128_1', 'LoadTrack', 0.02));
		}
		else {
			$player->msgs[0] = array(1, $head, array(1.45, 0.12, 0.16, 0.6, 0.4, 0.17), array('Icons128x128_1', 'LoadTrack', 0.02));
		}

		// list all found maps
		foreach ($maps as $row) {
			$mxid = '{#black}'. $row->id;
			$name = '{#black}'. $row->name;
			$author = $row->author;

			// add clickable buttons
			if ($aseco->settings['clickable_lists'] && $tid <= 500) {
				$mxid = array($mxid, 'PluginRaspJukebox?Action='. ($tid + 5200));  // action ids
				$name = array($name, 'PluginRaspJukebox?Action='. ($tid + 5700));
				$author = array($author, 'PluginRaspJukebox?Action='. ($tid + 6700));

				// store map in player object for action buttons
				$trkarr = array();
				$trkarr['id'] = $row->id;
				$trkarr['author'] = $row->author;
				$player->maplist[] = $trkarr;
			}

			if ($adminadd) {
				if ($aseco->settings['clickable_lists'] && $tid <= 500) {
					$msg[] = array(
						str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
						$mxid,
						$name,
						array('Add', 'PluginRaspJukebox?Action='. ($tid + 6200)),
						$author,
						$row->envir
					);
				}
				else {
					$msg[] = array(
						str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					        $mxid,
						$name,
						'Add',
						$author,
						$row->envir
					);
				}
			}
			else {
				$msg[] = array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$mxid,
					$name,
					$author,
					$row->envir
				);
			}

			$tid++;
			if (++$lines > 14) {
				$player->msgs[] = $msg;
				$lines = 0;
				$msg = array();
				if ($aseco->settings['clickable_lists']) {
					if ($adminadd) {
						$msg[] = array('Id', 'MX', 'Name (click to /add)', '$nAdmin', 'Author', 'Env');
					}
					else {
						$msg[] = array('Id', 'MX', 'Name (click to /add)', 'Author', 'Env');
					}
				}
				else {
					$msg[] = array('Id', 'MX', 'Name', 'Author', 'Env');
				}
			}
		}

		// add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function build_mx_mapref ($aseco) {

		$td = $aseco->server->mapdir . $this->mxdir;
		if (is_dir($td)) {
			$dir = opendir($td);
			$fp = fopen($td .'/mapref.txt', 'w');
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, -4)) == '.gbx') {
					$ci = $aseco->plugins['PluginRasp']->getMapData($td .'/'. $file, false);
					$file = str_ireplace('.map.gbx', '', $file);
					fwrite($fp, $file . "\t" . $ci['environment'] . "\t" . $ci['author'] . "\t" . $aseco->stripColors($ci['name']) . "\t" . $ci['cost'] . CRLF);
				}
			}
			fclose($fp);
			closedir($dir);
		}

		$td = $aseco->server->mapdir . $this->mxtmpdir;
		if (is_dir($td)) {
			$dir = opendir($td);
			$fp = fopen($td .'/mapref.txt', 'w');
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, -4)) == '.gbx') {
					$ci = $aseco->plugins['PluginRasp']->getMapData($td .'/'. $file, false);
					$file = str_ireplace('.map.gbx', '', $file);
					fwrite($fp, $file . "\t" . $ci['environment'] . "\t" . $ci['author'] . "\t" . $aseco->stripColors($ci['name']) . "\t" . $ci['cost'] . CRLF);
				}
			}
			fclose($fp);
			closedir($dir);
		}

	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

// BIG TODO
	public function getMapsByKarma ($player, $karmaval) {
		global $aseco;

		$player->maplist = array();

		// get list of karma values for all matching maps
		$order = ($karmaval <= 0 ? 'ASC' : 'DESC');
		if ($karmaval == 0) {
			$sql = "
			(SELECT
				`Uid`,
				SUM(`Score`) AS `Karma`
			FROM `%prefix%maps` AS `m`, `%prefix%ratings` AS `k`
			WHERE `m`.`MapId` = `k`.`MapId`
			GROUP BY `Uid` HAVING `Karma` = 0)
			UNION (
				SELECT
					`Uid`,
					0
				FROM `%prefix%maps`
				WHERE `MapId` NOT IN (
					SELECT DISTINCT
						`MapId`
					FROM `%prefix%ratings`
				)
			)
			ORDER BY `Karma` ". $order .";
			";
		}
		else {
			$sql = "
			SELECT
				`Uid`,
				SUM(`Score`) AS `Karma`
			FROM `%prefix%maps` AS `m`, `%prefix%ratings` AS `k`
			WHERE `m`.`MapId` = `k`.`MapId`
			GROUP BY `Uid`
			HAVING `Karma` ". ($karmaval < 0 ? '<= $karmaval' : '>= $karmaval') ."
			ORDER BY `Karma` ". $order .";
			";
		}
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows > 0) {

				$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
				$head = 'Maps by Karma ('. $order .'):';
				$msg = array();
				$msg[] = array('Id', 'Karma', 'Name', 'Author');

				$tid = 1;
				$lines = 0;
				$player->msgs = array();
				// reserve extra width for $w tags
				$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
				$player->msgs[0] = array(1, $head, array(1.27+$extra, 0.12, 0.15, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

				while ($row = $result->fetch_object()) {
					// does the uid exist in the current server map list?
					$map = $aseco->server->maps->getMapByUid($row->Uid);
					if ($row->Uid == $map->uid) {

						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
						}

						// Format author name
						$mapauthor = $map->author;

						// Format karma
						$mapkarma = str_pad($row->Karma, 4, '  ', STR_PAD_LEFT);

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));
						}

						// Add clickable buttons
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action ids
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));
							$mapkarma = array($mapkarma, 'PluginRaspJukebox?Action='. (-6000-$tid));
						}

						$msg[] = array(str_pad($tid, 3, '0', STR_PAD_LEFT) .'.', $mapkarma, $mapname, $mapauthor);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Karma', 'Name', 'Author');
						}
					}
				}

				// Add if last batch exists
				if (count($msg) > 1) {
					$player->msgs[] = $msg;
				}
			}
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoFinish ($player) {
		global $aseco;

		$player->maplist = array();

		// Get list of finished maps
		$sql = "
		SELECT DISTINCT
			`MapId`
		FROM `%prefix%times`
		WHERE `PlayerId` = ". $player->id ."
		AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
		ORDER BY `MapId`;
		";
		$result = $aseco->db->query($sql);
		if ($result) {
			$finished = array();
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_object()) {
					$finished[] = $row->MapId;
				}
			}
			$result->free_result();
		}

		// Get list of unfinished maps
		$sql = "
		SELECT
			`Uid`
		FROM `%prefix%maps`
		";
		if (!empty($finished)) {
			$sql .= " WHERE `MapId` NOT IN (". implode(',', $finished) .");";
		}
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows > 0) {

				$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
				$head = 'Maps you have not finished:';
				$msg = array();
				$msg[] = array('Id', 'Name', 'Author');

				$tid = 1;
				$lines = 0;
				$player->msgs = array();

				// Reserve extra width for $w tags
				$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
				$player->msgs[0] = array(1, $head, array(1.12+$extra, 0.12, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

				while ($row = $result->fetch_object()) {
					// Does the uid exist in the current server map list?
					$map = $aseco->server->maps->getMapByUid($row->Uid);
					if ($row->Uid == $map->uid) {

						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
							// add clickable button
							if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
								$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
							}
						}

						// Format author name
						$mapauthor = $map->author;

						// Add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
						}

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
						}

						$msg[] = array(
							str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
							$mapname,
							$mapauthor
						);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Name', 'Author');
						}
					}
				}

				// Add if last batch exists
				if (count($msg) > 1) {
					$player->msgs[] = $msg;
				}
			}
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoRank ($player) {
		global $aseco;

		$player->maplist = array();

		// Get list of finished maps
		$finished = array();
		$sql = "
		SELECT DISTINCT
			`MapId`
		FROM `%prefix%times`
		WHERE `PlayerId` = ". $player->id ."
		AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
		ORDER BY `MapId`;
		";
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows > 0) {
				while ($dbrow = $result->fetch_array(MYSQLI_NUM)) {
					$finished[] = $dbrow[0];
				}
			}
			$result->free_result();
		}


		// Get list of finished maps
		$sql = "
		SELECT
			`MapId`,
			`Uid`
		FROM `%prefix%maps`
		WHERE `MapId` ";
		if (!empty($finished)) {
			$sql .= 'IN ('. implode(',', $finished) .')';
		}
		else {
			$sql .= '= 0';  // empty list
		}
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows == 0) {
				$result->free_result();
				return;
			}
		}

		$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
		$unranked = array();
		$i = 0;
		// Check if player not in top $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() on each map
		while ($dbrow = $result->fetch_array(MYSQLI_NUM)) {
			$found = false;
			$sql2 = "
			SELECT
				`PlayerId`
			FROM `%prefix%records`
			WHERE `MapId` = ". $dbrow[0] ."
			AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
			ORDER by `Score` ". $order .", `Date` ASC
			LIMIT ". $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() .";
			";
			$result2 = $aseco->db->query($sql2);
			if ($result2) {
				if ($result2->num_rows > 0) {
					while ($plrow = $result2->fetch_array(MYSQLI_NUM)) {
						if ($player->id == $plrow[0]) {
							$found = true;
							break;
						}
					}
				}
				$result2->free_result();
			}
			if (!$found) {
				$unranked[$i++] = $dbrow[1];
			}
		}
		if (empty($unranked)) {
			$result->free_result();
			return;
		}

		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = 'Maps you have no rank on:';
		$msg = array();
		$msg[] = array('Id', 'Name', 'Author');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();

		// Reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.12+$extra, 0.12, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

		for ($i = 0; $i < count($unranked); $i++) {
			// Does the uid exist in the current server map list?
			$map = $aseco->server->maps->getMapByUid($unranked[$i]);
			if ($unranked[$i] == $map->uid) {

				// Store map in player object for jukeboxing
				$trkarr = array();
				$trkarr['uid']		= $map->uid;
				$trkarr['name']		= $map->name;
				$trkarr['author']	= $map->author;
				$trkarr['environment']	= $map->environment;
				$trkarr['filename']	= $map->filename;
				$player->maplist[] = $trkarr;

				// Format map name
				$mapname = $map->name;
				if (!$aseco->settings['lists_colormaps']) {
					$mapname = $map->name_stripped;
				}

				// Grey out if in history
				if (in_array($map->uid, $this->jb_buffer)) {
					$mapname = '{#grey}'. $map->name_stripped;
				}
				else {
					$mapname = '{#black}'. $mapname;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $tid <= 1900)
						$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
				}

				// Format author name
				$mapauthor = $map->author;

				// Add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
				}

				// Format env name
				$mapenv = $map->environment;

				// Add clickable button
				if ($aseco->settings['clickable_lists']) {
					$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
				}

				$msg[] = array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$mapname,
					$mapauthor
				);
				$tid++;
				if (++$lines > 9) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', 'Name', 'Author');
				}
			}
		}

		// Add if last batch exists
		if (count($msg)) {
			$player->msgs[] = $msg;
		}

		$result->free_result();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoGold ($player) {
		global $aseco;

		$player->maplist = array();

		// Check for Stunts mode
		if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {

			// Get list of finished maps with their best (minimum) times
			$sql = "
			SELECT DISTINCT
				`m`.`Uid`,
				`t1`.`Score`
			FROM `%prefix%times` AS `t1`, `%prefix%maps` AS `m`
			WHERE (`PlayerId` = ". $player->id ."
			AND `t1`.`MapId` = `m`.`MapId`
			AND `Score` = (
				SELECT
					MIN(`t2`.`Score`)
				FROM `%prefix%times` AS `t2`
				WHERE `PlayerId` = ". $player->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				AND `t1`.`MapId` = `t2`.`MapId`
			));
			";
			$result = $aseco->db->query($sql);
			if ($result) {
				if ($result->num_rows == 0) {
					$result->free_result();
					return;
				}
			}

			$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
			$head = 'Maps you did not beat gold time on:';
			$msg = array();
			$msg[] = array('Id', 'Name', 'Author', 'Time');

			$tid = 1;
			$lines = 0;
			$player->msgs = array();

			// Reserve extra width for $w tags
			$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
			$player->msgs[0] = array(1, $head, array(1.27+$extra, 0.12, 0.6+$extra, 0.4, 0.15), array('Icons128x128_1', 'NewTrack', 0.02));

			while ($row = $result->fetch_object()) {
				// Does the uid exist in the current server map list?
				$map = $aseco->server->maps->getMapByUid($row->Uid);
				if ($row->Uid == $map->uid) {
					// does best time beat map's Gold time?
					if ($row->Score > $map->goldtime) {
						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
							// Add clickable button
							if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
								$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
							}
						}

						// Format author name
						$mapauthor = $map->author;

						// Add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
						}

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
						}

						// Compute difference to Gold time
						$diff = $row->Score - $map->goldtime;
						$sec = floor($diff/1000);
						$hun = ($diff - ($sec * 1000)) / 10;

						$msg[] = array(
							str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
							$mapname,
							$mapauthor,
							'+'. sprintf("%d.%02d", $sec, $hun)
						);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Name', 'Author', 'Time');
						}
					}
				}
			}

			// Add if last batch exists
			if (count($msg) > 1) {
				$player->msgs[] = $msg;
			}

			if ($result) {
				$result->free_result();
			}

		}
		else { // Stunts mode

			// Get list of finished maps with their best (maximum) scores
			$sql = "
			SELECT DISTINCT
				`m`.`Uid`,
				`t1`.`Score`
			FROM `%prefix%times` AS `t1`, `%prefix%maps` AS `m`
			WHERE (`PlayerId` = ". $player->id ."
			AND `t1`.`MapId` = `m`.`MapId`
			AND `Score` = (
				SELECT
					MAX(`t2`.`Score`)
				FROM `%prefix%times` AS `t2`
				WHERE `PlayerId` = ". $player->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				AND `t1`.`MapId` = `t2`.`MapId`
			));
			";
			$result = $aseco->db->query($sql);
			if ($result) {
				if ($result->num_rows == 0) {
					$result->free_result();
					return;
				}
			}

			$head = 'Maps you did not beat gold score on:';
			$msg = array();
			$msg[] = array('Id', 'Name', 'Author', 'Env', 'Score');
			$tid = 1;
			$lines = 0;
			$player->msgs = array();

			// reserve extra width for $w tags
			$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
			$player->msgs[0] = array(1, $head, array(1.42+$extra, 0.12, 0.6+$extra, 0.4, 0.15, 0.15), array('Icons128x128_1', 'NewTrack', 0.02));


			while ($row = $result->fetch_object()) {
				// Does the uid exist in the current server map list?
				$map = $aseco->server->maps->getMapByUid($row->Uid);
				if ($row->Uid == $map->uid) {
					// does best time beat map's Gold time?
					if ($row->Score < $map->goldtime) {
						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
							// add clickable button
							if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
								$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
							}
						}

						// Format author name
						$mapauthor = $map->author;

						// Add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
						}

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
						}

						// Compute difference to Gold score
						$diff = $map->goldtime - $row->Score;

						$msg[] = array(
							str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
							$mapname,
							$mapauthor,
							$mapenv,
							'-'. $diff
						);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Name', 'Author', 'Env', 'Score');
						}
					}
				}
			}

			// Add if last batch exists
			if (count($msg) > 1) {
				$player->msgs[] = $msg;
			}

			if ($result) {
				$result->free_result();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoAuthor ($player) {
		global $aseco;

		$player->maplist = array();

		// Check for Stunts mode
		if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {

			// Get list of finished maps with their best (minimum) times
			$sql = "
			SELECT DISTINCT
				`m`.`Uid`,
				`t1`.`Score`
			FROM `%prefix%times` AS `t1`, `%prefix%maps` AS `m`
			WHERE (`PlayerId` = ". $player->id ."
			AND `t1`.`MapId` = `m`.`MapId`
			AND `Score` = (
				SELECT
					MIN(`t2`.`Score`)
				FROM `%prefix%times` AS `t2`
				WHERE `PlayerId` = ". $player->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				AND `t1`.`MapId` = `t2`.`MapId`
			));
			";
			$result = $aseco->db->query($sql);
			if ($result) {
				if ($result->num_rows == 0) {
					$result->free_result();
					return;
				}
			}

			$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
			$head = 'Maps you did not beat author time on:';
			$msg = array();
			$msg[] = array('Id', 'Name', 'Author', 'Time');

			$tid = 1;
			$lines = 0;
			$player->msgs = array();
			// reserve extra width for $w tags
			$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
			$player->msgs[0] = array(1, $head, array(1.27+$extra, 0.12, 0.6+$extra, 0.4, 0.15), array('Icons128x128_1', 'NewTrack', 0.02));

			while ($row = $result->fetch_object()) {
				// Does the uid exist in the current server map list?
				$map = $aseco->server->maps->getMapByUid($row->Uid);
				if ($row->Uid == $map->uid) {
					// does best time beat map's Gold time?
					if ($row->Score > $map->author_time) {
						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
							// add clickable button
							if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
								$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
							}
						}

						// Format author name
						$mapauthor = $map->author;

						// Add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
						}

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
						}

						// Compute difference to Author time
						$diff = $row->Score - $map->author_time;
						$sec = floor($diff/1000);
						$hun = ($diff - ($sec * 1000)) / 10;

						$msg[] = array(
							str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
							$mapname,
							$mapauthor,
							'+'. sprintf("%d.%02d", $sec, $hun)
						);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Name', 'Author', 'Time');
						}
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$player->msgs[] = $msg;
			}

			if ($result) {
				$result->free_result();
			}
		}
		else {  // Stunts mode

			// get list of finished maps with their best (maximum) scores
			$sql = "
			SELECT DISTINCT
				`m`.`Uid`,
				`t1`.`Score`
			FROM `%prefix%times` AS `t1`, `%prefix%maps` AS `m`
			WHERE (`PlayerId` = ". $player->id ."
			AND `t1`.`MapId` = `m`.`MapId`
			AND `Score` = (
				SELECT
					MAX(`t2`.`Score`)
				FROM `%prefix%times` AS `t2`
				WHERE `PlayerId` = ". $player->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				AND `t1`.`MapId` = `t2`.`MapId`
			));
			";
			$result = $aseco->db->query($sql);
			if ($result) {
				if ($result->num_rows == 0) {
					$result->free_result();
					return;
				}
			}

			$head = 'Maps you did not beat author score on:';
			$msg = array();
			$msg[] = array('Id', 'Name', 'Author', 'Env', 'Score');
			$tid = 1;
			$lines = 0;
			$player->msgs = array();
			// reserve extra width for $w tags
			$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
			$player->msgs[0] = array(1, $head, array(1.42+$extra, 0.12, 0.6+$extra, 0.4, 0.15, 0.15), array('Icons128x128_1', 'NewTrack', 0.02));

			while ($row = $result->fetch_object()) {
				// Does the uid exist in the current server map list?
				$map = $aseco->server->maps->getMapByUid($row->Uid);
				if ($row->Uid == $map->uid) {
					// does best time beat map's Gold time?
					if ($row->Score < $map->author_score) {
						// Store map in player object for jukeboxing
						$trkarr = array();
						$trkarr['uid']		= $map->uid;
						$trkarr['name']		= $map->name;
						$trkarr['author']	= $map->author;
						$trkarr['environment']	= $map->environment;
						$trkarr['filename']	= $map->filename;
						$player->maplist[] = $trkarr;

						// Format map name
						$mapname = $map->name;
						if (!$aseco->settings['lists_colormaps']) {
							$mapname = $map->name_stripped;
						}

						// Grey out if in history
						if (in_array($map->uid, $this->jb_buffer)) {
							$mapname = '{#grey}'. $map->name_stripped;
						}
						else {
							$mapname = '{#black}'. $mapname;
							// add clickable button
							if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
								$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
							}
						}

						// Format author name
						$mapauthor = $map->author;

						// Add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
						}

						// Format env name
						$mapenv = $map->environment;

						// Add clickable button
						if ($aseco->settings['clickable_lists']) {
							$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
						}

						// Compute difference to Author score
						$diff = $map->author_score - $row->Score;

						$msg[] = array(
							str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
							$mapname,
							$mapauthor,
							$mapenv,
							'-'. $diff
						);
						$tid++;
						if (++$lines > 14) {
							$player->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Name', 'Author', 'Env', 'Score');
						}
					}
				}
			}

			// Add if last batch exists
			if (count($msg) > 1) {
				$player->msgs[] = $msg;
			}

			if ($result) {
				$result->free_result();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoRecent ($player) {
		global $aseco;

		$player->maplist = array();

		// Get list of finished maps with their most recent (maximum) dates
		$sql = "
		SELECT DISTINCT
			`m`.`Uid`,
			`t1`.`Date`
		FROM `%prefix%times` AS `t1`, `%prefix%maps` AS `m`
		WHERE (`PlayerId` = ". $player->id ."
		AND `t1`.`MapId` = `m`.`MapId`
		AND `Date` = (
			SELECT
				MAX(`t2`.`Date`)
			FROM `%prefix%times` AS `t2`
			WHERE `PlayerId` = ". $player->id ."
			AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
			AND `t1`.`MapId` = `t2`.`MapId`
		))
		ORDER BY `t1`.`date`;
		";

		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows == 0) {
				$result->free_result();
				return;
			}
		}

		// Get list of ranked records
		$reclist = $player->getRecords();

		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = 'Maps you did not play recently:';
		$msg = array();
		$msg[] = array('Id', 'Rec', 'Name', 'Author', 'Date');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();

		// Reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.43+$extra, 0.12, 0.1, 0.6+$extra, 0.4, 0.21), array('Icons128x128_1', 'NewTrack', 0.02));

		while ($row = $result->fetch_object()) {
			// Does the uid exist in the current server map list?
			$map = $aseco->server->maps->getMapByUid($row->Uid);
			if ($row->Uid == $map->uid) {
				// Store map in player object for jukeboxing
				$trkarr = array();
				$trkarr['uid']		= $map->uid;
				$trkarr['name']		= $map->name;
				$trkarr['author']	= $map->author;
				$trkarr['environment']	= $map->environment;
				$trkarr['filename']	= $map->filename;
				$player->maplist[] = $trkarr;

				// Format map name
				$mapname = $map->name;
				if (!$aseco->settings['lists_colormaps']) {
					$mapname = $map->name_stripped;
				}

				// Grey out if in history
				if (in_array($map->uid, $this->jb_buffer)) {
					$mapname = '{#grey}'. $map->name_stripped;
				}
				else {
					$mapname = '{#black}'. $mapname;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
						$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
					}
				}

				// Format author name
				$mapauthor = $map->author;

				// Add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
				}

				// Format env name
				$mapenv = $map->environment;

				// Add clickable button
				if ($aseco->settings['clickable_lists']) {
					$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
				}

				// Get corresponding record
				$pos = isset($reclist[$map->uid]) ? $reclist[$map->uid] : 0;
				$pos = ($pos >= 1 && $pos <= $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) ? str_pad($pos, 2, '0', STR_PAD_LEFT) : '-- ';

				$msg[] = array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$pos .'.',
					$mapname,
					$mapauthor,
					date('Y-m-d', $row->Date)
				);
				$tid++;
				if (++$lines > 14) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', 'Rec', 'Name', 'Author', 'Date');
				}
			}
		}

		// Add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}

		if ($result) {
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsByLength ($player, $order) {
		global $aseco;

		$player->maplist = array();

		// If Stunts mode, bail out immediately
		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			return;
		}

		// Build list of author times
		$times = array();
		foreach ($aseco->server->maps->map_list as $map) {
			$times[$map->uid] = $map->author_time;
		}

		// Sort for shortest or longest author times
		$order ? asort($times) : arsort($times);

		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = ($order ? 'Shortest' : 'Longest') .' Maps On This Server:';
		$msg = array();
		$msg[] = array('Id', 'Name', 'Author', 'AuthTime');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();

		// Reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.29+$extra, 0.12, 0.6+$extra, 0.4, 0.17), array('Icons128x128_1', 'NewTrack', 0.02));

		foreach ($times as $uid => $time) {
			$map = $aseco->server->maps->getMapByUid($uid);

			// Store map in player object for jukeboxing
			$trkarr = array();
			$trkarr['uid']		= $map->uid;
			$trkarr['name']		= $map->name;
			$trkarr['author']	= $map->author;
			$trkarr['environment']	= $map->environment;
			$trkarr['filename']	= $map->filename;
			$player->maplist[] = $trkarr;

			// format map name
			$mapname = $map->name;
			if (!$aseco->settings['lists_colormaps']) {
				$mapname = $map->name_stripped;
			}

			// grey out if in history
			if (in_array($map->uid, $this->jb_buffer)) {
				$mapname = '{#grey}'. $map->name_stripped;
			}
			else {
				$mapname = '{#black}'. $mapname;
				// Add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
				}
			}
			// Format author name
			$mapauthor = $map->author;

			// Add clickable button
			if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
				$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
			}

			// Format env name
			$mapenv = $map->environment;

			// add clickable button
			if ($aseco->settings['clickable_lists']) {
				$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id

				$msg[] = array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$mapname,
					$mapauthor,
					$aseco->formatTime($time)
				);
			}

			$tid++;
			if (++$lines > 14) {
				$player->msgs[] = $msg;
				$lines = 0;
				$msg = array();
				$msg[] = array('Id', 'Name', 'Author', 'AuthTime');
			}
		}

		// Add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsByAdd ($player, $order, $count) {
		global $aseco;

		$player->maplist = array();

		// Get list of maps in reverse order of addition
		$sql = "
		SELECT
			`Uid`
		FROM `%prefix%maps`
		ORDER BY `MapId` ". ($order ? 'DESC' : 'ASC') .";
		";
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows == 0) {
				$result->free_result();
				return;
			}
		}

		$tcnt = 0;
		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = ($order ? 'Newest' : 'Oldest') .' Maps On This Server:';
		$msg = array();
		$msg[] = array('Id', 'Name', 'Author');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();
		// reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.12+$extra, 0.12, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));


		while ($row = $result->fetch_object()) {
			// Does the uid exist in the current server map list?
			$map = $aseco->server->maps->getMapByUid($row->Uid);
			if ($row->Uid == $map->uid) {
				// Store map in player object for jukeboxing
				$trkarr = array();
				$trkarr['uid']		= $map->uid;
				$trkarr['name']		= $map->name;
				$trkarr['author']	= $map->author;
				$trkarr['environment']	= $map->environment;
				$trkarr['filename']	= $map->filename;
				$player->maplist[] = $trkarr;

				// Format map name
				$mapname = $map->name;
				if (!$aseco->settings['lists_colormaps']) {
					$mapname = $map->name_stripped;
				}

				// Grey out if in history
				if (in_array($map->uid, $this->jb_buffer)) {
					$mapname = '{#grey}'. $map->name_stripped;
				}
				else {
					$mapname = '{#black}'. $mapname;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
						$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
					}
				}

				// Format author name
				$mapauthor = $map->author;

				// Add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
				}

				// Format env name
				$mapenv = $map->environment;

				// add clickable button
				if ($aseco->settings['clickable_lists']) {
					$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
				}

				$msg[] =  array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$mapname,
					$mapauthor
				);
				$tid++;
				if (++$lines > 14) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', 'Name', 'Author');
				}

				// Check if we have enough maps already
				if (++$tcnt == $count) {
					break;
				}
			}
		}

		// Add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}

		if ($result) {
			$result->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapsNoVote ($player) {
		global $aseco;

		$player->maplist = array();

		// Get list of ranked records
		$reclist = $player->getRecords();

		// Copy map list, later we remove maps that are voted by the player
		$newlist = $aseco->server->maps->map_list;

		// Get list of voted maps and remove those
		$sql = "
		SELECT
			`Uid`
		FROM `%prefix%maps` AS `m`, `%prefix%ratings` AS `k`
		WHERE `m`.`MapId` = `k`.`MapId`
		AND `k`.`PlayerId` = ". $player->id .";
		";
		$result = $aseco->db->query($sql);
		if ($result) {
			if ($result->num_rows > 0) {
				while ($dbrow = $result->fetch_array(MYSQLI_NUM)) {
					unset($newlist[$dbrow[0]]);
				}
			}
			$result->free_result();
		}

		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = 'Maps you did not vote for:';
		$msg = array();
		$msg[] = array('Id', 'Rec', 'Name', 'Author');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();
		// reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.22+$extra, 0.12, 0.1, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

		foreach ($newlist as $map) {
			// Store map in player object for jukeboxing
			$trkarr = array();
			$trkarr['uid']		= $map->uid;
			$trkarr['name']		= $map->name;
			$trkarr['author']	= $map->author;
			$trkarr['environment']	= $map->environment;
			$trkarr['filename']	= $map->filename;
			$player->maplist[] = $trkarr;

			// Format map name
			$mapname = $map->name;
			if (!$aseco->settings['lists_colormaps']) {
				$mapname = $map->name_stripped;
			}

			// Grey out if in history
			if (in_array($map->uid, $this->jb_buffer)) {
				$mapname = '{#grey}'. $map->name_stripped;
			}
			else {
				$mapname = '{#black}'. $mapname;
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapname = array($mapname, 'PluginRaspJukebox?Action='. ($tid+100));  // action id
				}
			}

			// Format author name
			$mapauthor = $map->author;

			// Add clickable button
			if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
				$mapauthor = array($mapauthor, 'PluginRaspJukebox?Action='. (-100-$tid));  // action id
			}

			// Format env name
			$mapenv = $map->environment;

			// add clickable button
			if ($aseco->settings['clickable_lists']) {
				$mapenv = array($mapenv, 'PluginRaspJukebox?Action='. ($envids[$mapenv]));  // action id
			}

			// Get corresponding record
			$pos = isset($reclist[$map->uid]) ? $reclist[$map->uid] : 0;
			$pos = ($pos >= 1 && $pos <= $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) ? str_pad($pos, 2, '0', STR_PAD_LEFT) : '-- ';

			$msg[] = array(
				str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
				$pos .'.',
				$mapname,
				$mapauthor
			);
			$tid++;
			if (++$lines > 14) {
				$player->msgs[] = $msg;
				$lines = 0;
				$msg = array();
				$msg[] = array('Id', 'Rec', 'Name', 'Author');
			}
		}

		// Add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function readSettings () {
		global $aseco;

		$config_file = 'config/rasp.xml';	// Settings XML File
		if (file_exists($config_file)) {
			$aseco->console('[RaspJukebox] Loading config file ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {

				/***************************** MESSAGES **************************************/
				$this->messages			= $xml['RASP']['MESSAGES'][0];

				/***************************** FEATURES **************************************/
				$this->feature_ranks		= $aseco->string2bool($xml['RASP']['FEATURE_RANKS'][0]);
				$this->nextrank_show_rp		= $aseco->string2bool($xml['RASP']['NEXTRANK_SHOW_POINTS'][0]);
				$this->feature_stats		= $aseco->string2bool($xml['RASP']['FEATURE_STATS'][0]);
				$this->always_show_pb		= $aseco->string2bool($xml['RASP']['ALWAYS_SHOW_PB'][0]);
				$this->feature_karma		= $aseco->string2bool($xml['RASP']['FEATURE_KARMA'][0]);
				$this->allow_public_karma	= $aseco->string2bool($xml['RASP']['ALLOW_PUBLIC_KARMA'][0]);
				$this->karma_show_start		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_START'][0]);
				$this->karma_show_details	= $aseco->string2bool($xml['RASP']['KARMA_SHOW_DETAILS'][0]);
				$this->karma_show_votes		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_VOTES'][0]);
				$this->karma_require_finish	= $xml['RASP']['KARMA_REQUIRE_FINISH'][0];
				$this->remind_karma		= $xml['RASP']['REMIND_KARMA'][0];
				$this->feature_jukebox		= $aseco->string2bool($xml['RASP']['FEATURE_JUKEBOX'][0]);
				$this->feature_mxadd		= $aseco->string2bool($xml['RASP']['FEATURE_MXADD'][0]);
				$this->jukebox_skipleft		= $aseco->string2bool($xml['RASP']['JUKEBOX_SKIPLEFT'][0]);
				$this->jukebox_adminnoskip	= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINNOSKIP'][0]);
				$this->jukebox_permadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_PERMADD'][0]);
				$this->jukebox_adminadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINADD'][0]);
				$this->jukebox_in_window	= $aseco->string2bool($xml['RASP']['JUKEBOX_IN_WINDOW'][0]);
				$this->autosave_matchsettings	= $xml['RASP']['AUTOSAVE_MATCHSETTINGS'][0];
				$this->feature_votes		= $aseco->string2bool($xml['RASP']['FEATURE_VOTES'][0]);
				$this->prune_records_times	= $aseco->string2bool($xml['RASP']['PRUNE_RECORDS_TIMES'][0]);


				/***************************** PERFORMANCE VARIABLES ***************************/
				if (isset($xml['RASP']['MIN_RANK'][0])) {
					$this->minrank = $xml['RASP']['MIN_RANK'][0];
				}
				else {
					$this->minrank = 3;
				}

				if (isset($xml['RASP']['MAX_AVG'][0])) {
					$this->maxavg = $xml['RASP']['MAX_AVG'][0];
				}
				else {
					$this->maxavg = 10;
				}


				/***************************** JUKEBOX VARIABLES *******************************/
				$this->buffersize		= $xml['RASP']['BUFFER_SIZE'][0];
				$this->mxvoteratio		= $xml['RASP']['MX_VOTERATIO'][0];
				$this->mxdir			= $xml['RASP']['MX_DIR'][0];
				$this->mxtmpdir			= $xml['RASP']['MX_TMPDIR'][0];
				$this->maphistory_file		= $xml['RASP']['MAPHISTORY_FILE'][0];

				$this->jukebox			= array();
				$this->jb_buffer		= array();
				$this->mxadd			= array();
				$this->mxplaying		= false;
				$this->mxplayed			= false;


				/******************************* IRC VARIABLES *********************************/
				$this->irc = new stdClass();
				$this->irc->server		= $xml['RASP']['IRC_SERVER'][0];
				$this->irc->nick		= $xml['RASP']['IRC_BOTNICK'][0];
				$this->irc->port		= $xml['RASP']['IRC_PORT'][0];
				$this->irc->channel		= $xml['RASP']['IRC_CHANNEL'][0];
				$this->irc->name		= $xml['RASP']['IRC_BOTNAME'][0];
				$this->irc->show_connect	= $aseco->string2bool($xml['RASP']['IRC_SHOW_CONNECT'][0]);

				$this->irc->linesbuffer		= array();
				$this->irc->ircmsgs		= array();
				$this->irc->con			= array();



				/******************************* VOTES VARIABLES *********************************/
			  	$this->auto_vote_starter	= $aseco->string2bool($xml['RASP']['AUTO_VOTE_STARTER'][0]);
			  	$this->allow_spec_startvote	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_STARTVOTE'][0]);
			  	$this->allow_spec_voting	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_VOTING'][0]);

				// maximum number of rounds before a vote expires
			  	$this->r_expire_limit = array(
			  		0 => $xml['RASP']['R_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['R_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['R_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['R_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['R_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['R_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['R_EXPIRE_LIMIT_IGNORE'][0],
			  	);
		    		$this->r_show_reminder = $aseco->string2bool($xml['RASP']['R_SHOW_REMINDER'][0]);

			    	// maximum number of seconds before a vote expires
			  	$this->ta_expire_limit = array(
			  		0 => $xml['RASP']['TA_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['TA_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['TA_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['TA_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['TA_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['TA_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['TA_EXPIRE_LIMIT_IGNORE'][0],
			  	);
				$this->ta_show_reminder = $aseco->string2bool($xml['RASP']['TA_SHOW_REMINDER'][0]);

				// interval length at which to (approx.) repeat reminder [s]
				$this->ta_show_interval = $xml['RASP']['TA_SHOW_INTERVAL'][0];

		  		// disable CallVotes
		  		$aseco->client->query('SetCallVoteRatio', 1.0);

		  		// really disable all CallVotes
		  		$ratios = array(array('Command' => '*', 'Ratio' => -1.0));
		  		$aseco->client->query('SetCallVoteRatios', $ratios);

				$this->global_explain = $xml['RASP']['GLOBAL_EXPLAIN'][0];

		  		// define the vote ratios for all types
		  		$this->vote_ratios = array(
		  			0 => $xml['RASP']['VOTE_RATIO_ENDROUND'][0],
		  			1 => $xml['RASP']['VOTE_RATIO_LADDER'][0],
		  			2 => $xml['RASP']['VOTE_RATIO_REPLAY'][0],
		  			3 => $xml['RASP']['VOTE_RATIO_SKIP'][0],
		  			4 => $xml['RASP']['VOTE_RATIO_KICK'][0],
		  			5 => $xml['RASP']['VOTE_RATIO_ADD'][0],
		  			6 => $xml['RASP']['VOTE_RATIO_IGNORE'][0],
		  		);

		  		$this->vote_in_window		= $aseco->string2bool($xml['RASP']['VOTE_IN_WINDOW'][0]);
		  		$this->disable_upon_admin	= $aseco->string2bool($xml['RASP']['DISABLE_UPON_ADMIN'][0]);
		  		$this->disable_while_sb		= $aseco->string2bool($xml['RASP']['DISABLE_WHILE_SB'][0]);

		   		// allow kicks & allow user to kick-vote any admin?
		  		$this->allow_kickvotes		= $aseco->string2bool($xml['RASP']['ALLOW_KICKVOTES'][0]);
		  		$this->allow_admin_kick		= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_KICK'][0]);

		  		// allow ignores & allow user to ignore-vote any admin?
		  		$this->allow_ignorevotes	= $aseco->string2bool($xml['RASP']['ALLOW_IGNOREVOTES'][0]);
		  		$this->allow_admin_ignore	= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_IGNORE'][0]);

		  		$this->max_laddervotes		= $xml['RASP']['MAX_LADDERVOTES'][0];
		  		$this->max_replayvotes		= $xml['RASP']['MAX_REPLAYVOTES'][0];
		  		$this->max_skipvotes		= $xml['RASP']['MAX_SKIPVOTES'][0];

		  		$this->replays_limit		= $xml['RASP']['REPLAYS_LIMIT'][0];

		  		$this->ladder_fast_restart	= $aseco->string2bool($xml['RASP']['LADDER_FAST_RESTART'][0]);

		  		$this->r_points_limits		= $aseco->string2bool($xml['RASP']['R_POINTS_LIMITS'][0]);
		  		$this->r_ladder_max		= $xml['RASP']['R_LADDER_MAX'][0];
		  		$this->r_replay_min		= $xml['RASP']['R_REPLAY_MIN'][0];
		  		$this->r_skip_max		= $xml['RASP']['R_SKIP_MAX'][0];

		  		$this->ta_time_limits		= $aseco->string2bool($xml['RASP']['TA_TIME_LIMITS'][0]);
		  		$this->ta_ladder_max		= $xml['RASP']['TA_LADDER_MAX'][0];
		  		$this->ta_replay_min		= $xml['RASP']['TA_REPLAY_MIN'][0];
		  		$this->ta_skip_max		= $xml['RASP']['TA_SKIP_MAX'][0];
			}
			else {
				trigger_error('Could not read/parse rasp config file ['. $config_file .']!', E_USER_WARNING);
			}
		}
		else {
			trigger_error('Could not find rasp config file ['. $config_file .']!', E_USER_WARNING);
		}
	}
}

?>
