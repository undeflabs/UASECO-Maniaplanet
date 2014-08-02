<?php
/*
 * Plugin: Chat Admin
 * ~~~~~~~~~~~~~~~~~~
 * » Provides regular admin commands.
 * » Based upon chat.admin.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-02
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
 *  - plugins/plugin.access.php
 *  - plugins/plugin.autotime.php
 *  - plugins/plugin.donate.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.panels.php
 *  - plugins/plugin.rasp.php
 *  - plugins/plugin.rasp_jukebox.php
 *  - plugins/plugin.rasp_votes.php
 *  - plugins/plugin.round_points.php
 *  - plugins/plugin.uptodate.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatAdmin();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatAdmin extends Plugin {
	public $pmbuf			= array();	// pm history buffer
	public $pmlen			= 30;           // length of pm history
	public $lnlen			= 40;           // max length of pm line

	public $method_results;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides regular admin commands.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginAccessControl',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginAutotime',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginDonate',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginPanels',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspJukebox',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRoundPoints',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginUptodate',		Dependence::WANTED,	'1.0.0', null);

		// handles action id's "2201"-"2400" for /admin warn
		// handles action id's "2401"-"2600" for /admin ignore
		// handles action id's "2601"-"2800" for /admin unignore
		// handles action id's "2801"-"3000" for /admin kick
		// handles action id's "3001"-"3200" for /admin ban
		// handles action id's "3201"-"3400" for /admin unban
		// handles action id's "3401"-"3600" for /admin black
		// handles action id's "3601"-"3800" for /admin unblack
		// handles action id's "3801"-"4000" for /admin addguest
		// handles action id's "4001"-"4200" for /admin removeguest
		// handles action id's "4201"-"4400" for /admin forcespec
		// handles action id's "4401"-"4600" for /admin unignore in listignores
		// handles action id's "4601"-"4800" for /admin unban in listbans
		// handles action id's "4801"-"5000" for /admin unblack in listblacks
		// handles action id's "5001"-"5200" for /admin removeguest in listguests
		// handles action id's "-7901"-"-8100" for /admin unbanip
		$this->registerEvent('onPlayerManialinkPageAnswer', 'onPlayerManialinkPageAnswer');

		$params = array(
			'help'				=> 'Shows all available /admin commands',
			'helpall'			=> 'Displays help for available /admin commands',
			'setservername'			=> 'Changes the name of the server',
			'setcomment'			=> 'Changes the server comment',
			'setpwd'			=> 'Changes the player password',
			'setspecpwd'			=> 'Changes the spectator password',
			'setrefpwd'			=> 'Changes the referee password',
			'setmaxplayers'			=> 'Sets a new maximum of players',
			'setmaxspecs'			=> 'Sets a new maximum of spectators',
			'setgamemode'			=> 'Sets next mode {ta,rounds,team,laps,stunts,cup}',
			'setrefmode'			=> 'Sets referee mode {0=top3,1=all}',
			'nextmap/next'			=> 'Forces server to load next map',
			'skipmap/skip'			=> 'Forces server to load next map',
			'previous/prev'			=> 'Forces server to load previous map',
			'nextenv'			=> 'Loads next map in same environment',
			'restartmap/res'		=> 'Restarts currently running map',
			'replaymap/replay'		=> 'Replays current map (via jukebox)',
			'dropjukebox/djb'		=> 'Drops a map from the jukebox',
			'clearjukebox/cjb'		=> 'Clears the entire jukebox',
			'clearhist'			=> 'Clears (part of) map history',
			'add'				=> 'Adds maps directly from MX (<ID> ...)',
			'addthis'			=> 'Adds current /add-ed map permanently',
			'addlocal'			=> 'Adds a local map (<filename>)',
			'warn'				=> 'Sends a kick/ban warning to a player',
			'kick'				=> 'Kicks a player from server',
			'kickghost'			=> 'Kicks a ghost player from server',
			'ban'				=> 'Bans a player from server',
			'unban'				=> 'UnBans a player from server',
			'banip'				=> 'Bans an IP address from server',
			'unbanip'			=> 'UnBans an IP address from server',
			'black'				=> 'Blacklists a player from server',
			'unblack'			=> 'UnBlacklists a player from server',
			'addguest'			=> 'Adds a guest player to server',
			'removeguest'			=> 'Removes a guest player from server',
			'pass'				=> 'Passes a chat-based or MX /add vote',
			'cancel/can'			=> 'Cancels any running vote',
			'endround/er'			=> 'Forces end of current round',
			'players'			=> 'Displays list of known players {string}',
			'showbanlist/listbans'		=> 'Displays current ban list',
			'showiplist/listips'		=> 'Displays current banned IPs list',
			'showblacklist/listblacks'	=> 'Displays current black list',
			'showguestlist/listguests'	=> 'Displays current guest list',
			'writeiplist'			=> 'Saves current banned IPs list (def: bannedips.xml)',
			'readiplist'			=> 'Loads current banned IPs list (def: bannedips.xml)',
			'writeblacklist'		=> 'Saves current black list (def: blacklist.txt)',
			'readblacklist'			=> 'Loads current black list (def: blacklist.txt)',
			'writeguestlist'		=> 'Saves current guest list (def: guestlist.txt)',
			'readguestlist'			=> 'Loads current guest list (def: guestlist.txt)',
			'cleanbanlist'			=> 'Cleans current ban list',
			'cleaniplist'			=> 'Cleans current banned IPs list',
			'cleanblacklist'		=> 'Cleans current black list',
			'cleanguestlist'		=> 'Cleans current guest list',
			'mergegbl'			=> 'Merges a global black list {URL}',
			'access'			=> 'Handles player access control (see: /admin access help)',
			'writemaplist'			=> 'Saves current map list (def: maplist.txt)',
			'readmaplist'			=> 'Loads current map list (def: maplist.txt)',
			'shuffle/shufflemaps'		=> 'Randomizes current map list',
			'listdupes'			=> 'Displays list of duplicate maps',
			'remove'			=> 'Removes a map from rotation',
			'erase'				=> 'Removes a map from rotation & deletes map file',
			'removethis'			=> 'Removes this map from rotation',
			'erasethis'			=> 'Removes this map from rotation & deletes map file',
			'mute/ignore'			=> 'Adds a player to global mute/ignore list',
			'unmute/unignore'		=> 'Removes a player from global mute/ignore list',
			'mutelist/listmutes'		=> 'Displays global mute/ignore list',
			'ignorelist/listignores'	=> 'Displays global mute/ignore list',
			'cleanmutes/cleanignores'	=> 'Cleans global mute/ignore list',
			'addadmin'			=> 'Adds a new admin',
			'removeadmin'			=> 'Removes an admin',
			'addop'				=> 'Adds a new operator',
			'removeop'			=> 'Removes an operator',
			'listmasters'			=> 'Displays current masteradmin list',
			'listadmins'			=> 'Displays current admin list',
			'listops'			=> 'Displays current operator list',
			'adminability'			=> 'Shows/changes admin ability {ON/OFF}',
			'opability'			=> 'Shows/changes operator ability {ON/OFF}',
			'listabilities'			=> 'Displays current abilities list',
			'writeabilities'		=> 'Saves current abilities list (def: config/adminops.xml)',
			'readabilities'			=> 'Loads current abilities list (def: config/adminops.xml)',
			'wall/mta'			=> 'Displays popup message to all players',
			'delrec'			=> 'Deletes specific record on current map',
			'prunerecs'			=> 'Deletes records for specified map',
			'rpoints'			=> 'Sets custom Rounds points (see: /admin rpoints help)',
			'amdl'				=> 'Sets AllowMapDownload {ON/OFF}',
			'autotime'			=> 'Sets Auto TimeLimit {ON/OFF}',
			'disablerespawn'		=> 'Disables respawn at CPs {ON/OFF}',
			'forceshowopp'			=> 'Forces to show opponents {##/ALL/OFF}',
			'scorepanel'			=> 'Shows automatic scorepanel {ON/OFF}',
			'roundsfinish'			=> 'Shows rounds panel upon first finish {ON/OFF}',
			'forceteam'			=> 'Forces player into {Blue} or {Red} team',
			'forcespec'			=> 'Forces player into free spectator',
			'specfree'			=> 'Forces spectator into free mode',
			'panel'				=> 'Selects admin panel (see: /admin panel help)',
			'style'				=> 'Selects default window style',
			'admpanel'			=> 'Selects default admin panel',
			'donpanel'			=> 'Selects default donate panel',
			'recpanel'			=> 'Selects default records panel',
			'votepanel'			=> 'Selects default vote panel',
			'panelbg'			=> 'Selects default panel background',
			'planets'			=> 'Shows server\'s planets amount',
			'pay'				=> 'Pays server planets to login',
			'relays'			=> 'Displays relays list or shows relay master',
			'server'			=> 'Displays server\'s detailed settings',
			'pm'				=> 'Sends private message to all available admins',
			'pmlog'				=> 'Displays log of recent private admin messages',
			'call'				=> 'Executes direct server call (see: /admin call help)',
			'unlock'			=> 'Unlocks admin commands & features',
			'debug'				=> 'Toggles debugging output',
			'shutdown'			=> 'Shuts down UASECO',
			'shutdownall'			=> 'Shuts down Server & UASECO',
		);
		ksort($params);

		$this->registerChatCommand(
			'admin',					// Chat command
			'chat_admin',					// Callback function
			'Provides admin commands (see: /admin help)',	// Description of chat command
			Player::OPERATORS,				// Access rights
			$params						// Chat command available parameters and descriptions (only for display in /helpall)
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_admin ($aseco, $login, $chat_command, $chat_parameter) {

		$admin = $aseco->server->players->getPlayer($login);
		$command['params'] = $chat_parameter;

		// split params into arrays & insure optional parameters exist
		$arglist = explode(' ', $command['params'], 2);
		if (!isset($arglist[1])) {
			$arglist[1] = '';
		}

		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $command['params']));
		if (!isset($command['params'][1])) {
			$command['params'][1] = '';
		}

		// check if chat command was allowed for a masteradmin/admin/operator
		if ($aseco->isMasterAdmin($admin)) {
			$logtitle = 'MasterAdmin';
			$chattitle = $aseco->titles['MASTERADMIN'][0];
		}
		else {
			if ($aseco->isAdmin($admin) && $aseco->allowAdminAbility($command['params'][0])) {
				$logtitle = 'Admin';
				$chattitle = $aseco->titles['ADMIN'][0];
			}
			else {
				if ($aseco->isOperator($admin) && $aseco->allowOpAbility($command['params'][0])) {
					$logtitle = 'Operator';
					$chattitle = $aseco->titles['OPERATOR'][0];
				}
				else {
					// write warning in console
					$aseco->console($login . ' tried to use admin chat command (no permission!): ' . $arglist[0] . ' ' . $arglist[1]);
					// show chat message
					$aseco->client->query('ChatSendToLogin', $aseco->formatColors('{#error}You don\'t have the required admin rights to do that!'), $login);
					return false;
				}
			}
		}

		// check for unlocked password (or unlock command)
		if ($aseco->settings['lock_password'] != '' && !$admin->unlocked &&
		    $command['params'][0] != 'unlock') {
			// write warning in console
			$aseco->console($login . ' tried to use admin chat command (not unlocked!): ' . $arglist[0] . ' ' . $arglist[1]);
			// show chat message
			$aseco->client->query('ChatSendToLogin', $aseco->formatColors('{#error}You don\'t have the required admin rights to do that!'), $login);
			return false;
		}

		if ($command['params'][0] == 'help') {
			/**
			 * Show admin help.
			 */

			// Build list of currently active commands
			$active_commands = array();
			foreach ($aseco->registered_chatcmds as $name => $cc) {

				// check if admin command is within this admin's tier
				$allowed = false;
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
				if ( ($allowed == true) && ($aseco->allowAbility($admin, $name)) ) {
					$active_commands[$name] = $cc;
				}
			}

			// Show active admin commands on command line
			$aseco->showHelp($aseco, $login, $active_commands, $logtitle, true, false);
		}
		else if ($command['params'][0] == 'helpall') {
			/**
			 * Display admin help.
			 */

			// Build list of currently active commands
			$active_commands = array();
			foreach ($aseco->registered_chatcmds as $name => $cc) {

				// Check if admin command is within this admin's tier
				$allowed = false;
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
				if ( ($allowed == true) && ($aseco->allowAbility($admin, $name)) ) {
					$active_commands[$name] = $cc;
				}
			}

			// Display active admin commands in popup with descriptions
			$aseco->showHelp($aseco, $login, $active_commands, $logtitle, true, true, 0.42);
		}
		else if ($command['params'][0] == 'setservername' && $command['params'][1] != '') {
			/**
			 * Sets a new server name (on the fly).
			 */

			// set a new servername
			$aseco->client->query('SetServerName', $arglist[1]);

			// log console message
			$aseco->console('[Admin] {1} [{2}] set new server name [{3}]', $logtitle, $login, $arglist[1]);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets servername to {#highlite}{3}',
				$chattitle,
				$admin->nickname,
				$arglist[1]
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'setcomment' && $command['params'][1] != '') {
			/**
			 * Sets a new server comment (on the fly).
			 */

			// set a new server comment
			$aseco->client->query('SetServerComment', $arglist[1]);

			// log console message
			$aseco->console('[Admin] {1} [{2}] set new server comment [{3}]', $logtitle, $login, $arglist[1]);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets server comment to {#highlite}{3}',
				$chattitle,
				$admin->nickname,
				$arglist[1]
			);
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'setpwd') {
			/**
			 * Sets a new player password (on the fly).
			 */

			// set a new player password
			$aseco->client->query('SetServerPassword', $arglist[1]);

			if ($arglist[1] != '') {
				// log console message
				$aseco->console('[Admin] {1} [{2}] set new player password [{3}]', $logtitle, $login, $arglist[1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets player password to {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$arglist[1]
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				// log console message
				$aseco->console('[Admin] {1} [{2}] disabled player password', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} disables player password',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'setspecpwd') {
			/**
			 * Sets a new spectator password (on the fly).
			 */

			// set a new spectator password
			$aseco->client->query('SetServerPasswordForSpectator', $arglist[1]);

			if ($arglist[1] != '') {
				// log console message
				$aseco->console('[Admin] {1} [{2}] set new spectator password [{3}]', $logtitle, $login, $arglist[1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets spectator password to {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$arglist[1]
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				// log console message
				$aseco->console('[Admin] {1} [{2}] disabled spectator password', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} disables spectator password',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'setrefpwd') {
			/**
			 * Sets a new referee password (on the fly).
			 */

			// set a new referee password
			$aseco->client->query('SetRefereePassword', $arglist[1]);

			if ($arglist[1] != '') {
				// log console message
				$aseco->console('[Admin] {1} [{2}] set new referee password [{3}]', $logtitle, $login, $arglist[1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets referee password to {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$arglist[1]
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				// log console message
				$aseco->console('[Admin] {1} [{2}] disabled referee password', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} disables referee password',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'setmaxplayers' && is_numeric($command['params'][1]) && $command['params'][1] > 0) {
			/**
			 * Sets a new player maximum that is able to connect to the server.
			 */

			// tell server to set new player max
			$aseco->client->query('SetMaxPlayers', (int) $command['params'][1]);

			// log console message
			$aseco->console('[Admin] {1} [{2}] set new player maximum [{3}]', $logtitle, $login, $command['params'][1]);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets new player maximum to {#highlite}{3}{#admin} !',
				$chattitle,
				$admin->nickname,
				$command['params'][1]
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'setmaxspecs' && is_numeric($command['params'][1]) && $command['params'][1] >= 0) {
			/**
			 * Sets a new spectator maximum that is able to connect to the server.
			 */

			// tell server to set new spectator max
			$aseco->client->query('SetMaxSpectators', (int) $command['params'][1]);

			// log console message
			$aseco->console('[Admin] {1} [{2}] set new spectator maximum [{3}]', $logtitle, $login, $command['params'][1]);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets new spectator maximum to {#highlite}{3}{#admin} !',
				$chattitle,
				$admin->nickname,
				$command['params'][1]
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'setgamemode' && $command['params'][1] != '') {
			/**
			 * Sets new game mode that will be active upon the next map:
			 * timeattack, rounds, team, laps, stunts, teamattack
			 */

			// check mode parameter
			$mode = false;
			switch (strtolower($command['params'][1])) {
				case 'rounds':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::ROUNDS);
					break;

				case 'timeattack':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::TIMEATTACK);
					break;

				case 'team':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::TEAM);
					break;

				case 'laps':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::LAPS);
					break;

				case 'cup':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::CUP);
					break;

				case 'stunts':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::STUNTS);
					break;

				case 'teamattack':
					$mode = $aseco->server->gameinfo->getGamemodeName(Gameinfo::TEAMATTACK);
					break;

				default:
					$mode = false;
			}

			if ($mode !== false) {
				if ($aseco->changing_to_gamemode !== false || $mode !== $aseco->server->gameinfo->mode) {

					// Store the next Gamemode
					$aseco->changing_to_gamemode = $mode;

					// tell server to set new game mode
					$aseco->client->query('SetScriptName', $mode .'.Script.txt');

					// log console message
					$aseco->console('[Admin] {1} [{2}] set new game mode [{3}]', $logtitle, $login, $mode);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets next game mode to {#highlite}{3}{#admin} !',
						$chattitle,
						$admin->nickname,
						$mode
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$aseco->changing_to_gamemode = false;
					$message = '{#server}» Same game mode {#highlite}' . strtoupper($command['params'][1]);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = '{#server}» {#error}Invalid game mode {#highlite}$i ' . strtoupper($command['params'][1]) . '$z$s {#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'setrefmode') {
			/**
			 * Sets new referee mode (0 = top3, 1 = all).
			 */

			if (($mode = $command['params'][1]) != '') {
				if (is_numeric($mode) && ($mode == 0 || $mode == 1)) {
					// tell server to set new referee mode
					$aseco->client->query('SetRefereeMode', (int) $mode);

					// log console message
					$aseco->console('[Admin] {1} [{2}] set new referee mode [{3}]', $logtitle, $login, strtoupper($mode));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets referee mode to {#highlite}{3}{#admin} !',
						$chattitle,
						$admin->nickname,
						$mode
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = '{#server}» {#error}Invalid referee mode {#highlite}$i ' . strtoupper($mode) . '$z$s {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				// tell server to get current referee mode
				$aseco->client->query('GetRefereeMode');
				$mode = $aseco->client->getResponse();

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Referee mode is set to {#highlite}{1}',
					($mode == 1 ? 'All' : 'Top-3')
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
		else if ($command['params'][0] == 'nextmap' || $command['params'][0] == 'next' || $command['params'][0] == 'skipmap' || $command['params'][0] == 'skip') {
			/**
			 * Forces the server to load next map.
			 */

			// load the next map
			// don't clear scores if in Cup mode
			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				$aseco->client->query('NextMap', true);
			}
			else {
				$aseco->client->query('NextMap');
			}

			// log console message
			$aseco->console('[Admin] {1} [{2}] skips map!', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} skips map!',
				$chattitle,
				$admin->nickname
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'previous' || $command['params'][0] == 'prev') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Forces the server to load previous map.
				 */

				// prepend previous map to start of jukebox
				$uid = $aseco->server->maps->previous->uid;
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $aseco->server->maps->previous->filename;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $aseco->server->maps->previous->name;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $aseco->server->maps->previous->environment;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $admin->login;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $admin->nickname;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'Previous';
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);

				if ($aseco->debug) {
					$aseco->console_text('/admin prev jukebox:' . CRLF .
						print_r($aseco->plugins['PluginRaspJukebox']->jukebox, true)
					);
				}

				// load the previous map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->client->query('NextMap', true);
				}
				else {
					$aseco->client->query('NextMap');
				}

				// log console message
				$aseco->console('[Admin] {1} [{2}] revisits previous map!', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} revisits previous map!',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('previous', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Loading previous map unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'nextenv') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Loads the next map in the same environment.
				 */

				// dummy player to easily obtain environment map list
				$list = new Player();
				$aseco->plugins['PluginRasp']->getAllMaps($list, '*', $aseco->server->maps->current->environment);

				// search for current map
				$next = null;
				$found = false;
				foreach ($list->maplist as $map) {
					if ($found) {
						$next = $map;
						break;
					}
					if ($map['uid'] == $aseco->server->maps->current->uid) {
						$found = true;
					}
				}
				// check for last map and loop back to first
				if ($next === null) {
					$next = $list->maplist[0];				// TODO
				}
				unset($list);

				// prepend next env map to start of jukebox
				$uid = $next['uid'];
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $next['filename'];
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $next['name'];
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $next['environment'];
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $admin->login;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $admin->nickname;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'Previous';
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);

				if ($aseco->debug) {
					$aseco->console_text('/admin nextenv jukebox:' . CRLF .
						print_r($aseco->plugins['PluginRaspJukebox']->jukebox, true)
					);
				}

				// load the next environment map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->client->query('NextMap', true);
				}
				else {
					$aseco->client->query('NextMap');
				}

				// log console message
				$aseco->console('[Admin] {1} [{2}] skips to next {3} map!', $logtitle, $login, $aseco->server->maps->current->environment);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} skips to next {#highlite}{3}{#admin} map!',
					$chattitle,
					$admin->nickname,
					$aseco->server->maps->current->environment
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('nextenv', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Load next map with same environment unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'restartmap' || $command['params'][0] == 'res') {
			/**
			 * Restarts the currently running map.
			 */

			// Restart the map
			if ( isset($aseco->plugins['PluginAutotime']) ) {
				// from plugin.autotime.php
				$aseco->plugins['PluginAutotime']->restart = true;
			}

			// don't clear scores if in Cup mode
			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				$aseco->client->query('RestartMap', true);
			}
			else {
				$aseco->client->query('RestartMap');
			}

			// log console message
			$aseco->console('[Admin] {1} [{2}] restarts map!', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} restarts map!',
				$chattitle,
				$admin->nickname
			);

			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'replaymap' || $command['params'][0] == 'replay') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Replays the current map (queues it at start of jukebox).
				 */

				// cancel possibly ongoing replay/restart vote
				$aseco->client->query('CancelVote');
				if ( isset($aseco->plugins['PluginRaspVotes']) ) {
					if (!empty($aseco->plugins['PluginRaspVotes']->chatvote) && $aseco->plugins['PluginRaspVotes']->chatvote['type'] == 2) {
						$aseco->plugins['PluginRaspVotes']->chatvote = array();

						// disable all vote panels
						if ( isset($aseco->plugins['PluginPanels']) ) {
							$aseco->plugins['PluginPanels']->allvotepanels_off($aseco);
						}
					}
				}

				// check if map already in jukebox
				if (!empty($aseco->plugins['PluginRaspJukebox']->jukebox) && array_key_exists($aseco->server->maps->current->uid, $aseco->plugins['PluginRaspJukebox']->jukebox)) {
					$message = '{#server}» {#error}Map is already getting replayed!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return;
				}

				// prepend current map to start of jukebox
				$uid = $aseco->server->maps->current->uid;
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $aseco->server->maps->current->filename;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $aseco->server->maps->current->name;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $aseco->server->maps->current->environment;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $admin->login;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $admin->nickname;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'AdminReplay';
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
				$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
				$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);

				if ($aseco->debug) {
					$aseco->console_text('/admin replay jukebox:'. CRLF . print_r($aseco->plugins['PluginRaspJukebox']->jukebox, true) );
				}

				// log console message
				$aseco->console('[Admin] {1} [{2}] requeues map!', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} queues map for replay!',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('replay', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Replay map unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'dropjukebox' || $command['params'][0] == 'djb') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Drops a map from the jukebox (for use with rasp jukebox plugin).
				 */

				// verify parameter
				if (is_numeric($command['params'][1]) &&
				    $command['params'][1] >= 1 && $command['params'][1] <= count($aseco->plugins['PluginRaspJukebox']->jukebox)) {
					$i = 1;
					foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $item) {
						if ($i++ == $command['params'][1]) {
							$name = $aseco->stripColors($item['Name']);
							$uid = $item['uid'];
							break;
						}
					}
					$drop = $aseco->plugins['PluginRaspJukebox']->jukebox[$uid];
					unset($aseco->plugins['PluginRaspJukebox']->jukebox[$uid]);

					// log console message
					$aseco->console('[Admin] {1} [{2}] drops map {3} from jukebox!', $logtitle, $login, $aseco->stripColors($name, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} drops map {#highlite}{3}{#admin} from jukebox!',
						$chattitle,
						$admin->nickname,
						$name
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

					// throw 'jukebox changed' event
					$aseco->releaseEvent('onJukeboxChanged', array('drop', $drop));
				}
				else {
					$message = '{#server}» {#error}Jukebox entry not found! Type {#highlite}$i /jukebox list{#error} or {#highlite}$i /jukebox display{#error} for its contents.';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Drop jukebox unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'clearjukebox' || $command['params'][0] == 'cjb') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Clears the jukebox (for use with rasp jukebox plugin).
				 */

				// clear jukebox
				$aseco->plugins['PluginRaspJukebox']->jukebox = array();

				// log console message
				$aseco->console('[Admin] {1} [{2}] clears jukebox!', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} clears jukebox!',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('clear', null));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Clear jukebox unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'clearhist') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Clears (part of) map history.
				 */

				// check for optional portion (pos = newest, neg = oldest)
				if ($command['params'][1] != '' && is_numeric($command['params'][1]) && $command['params'][1] != 0) {
					$clear = intval($command['params'][1]);

					// log console message
					$aseco->console('[Admin] {1} [{2}] clears {3} map{4} from history!', $logtitle, $login,
						($clear > 0 ? 'newest ' : 'oldest ') . abs($clear),
						(abs($clear) == 1 ? '' : 's')
					);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} clears {3}{#admin} map{4} from history!',
						$chattitle,
						$admin->nickname,
						($clear > 0 ? 'newest {#highlite}' : 'oldest {#highlite}') . abs($clear),
						(abs($clear) == 1 ? '' : 's')
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else if (strtolower($command['params'][1]) == 'all') {  // entire history
					$clear = $aseco->plugins['PluginRaspJukebox']->buffersize;

					// log console message
					$aseco->console('[Admin] {1} [{2}] clears entire map history!', $logtitle, $login);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} clears entire map history!',
						$chattitle,
						$admin->nickname
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}The map history contains {#highlite}{3}{#admin} map{4}',
						$chattitle,
						$admin->nickname,
						count($aseco->plugins['PluginRaspJukebox']->jb_buffer),
						(count($aseco->plugins['PluginRaspJukebox']->jb_buffer) == 1 ? '' : 's')
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return;
				}

				// clear map history (portion)
				$i = 0;
				if ($clear > 0) {
					if ($clear > $aseco->plugins['PluginRaspJukebox']->buffersize) {
						$clear = $aseco->plugins['PluginRaspJukebox']->buffersize;
					}
					while ($i++ < $clear) {
						array_pop($aseco->plugins['PluginRaspJukebox']->jb_buffer);
					}
				}
				else {
					if ($clear < -$aseco->plugins['PluginRaspJukebox']->buffersize) {
						$clear = -$aseco->plugins['PluginRaspJukebox']->buffersize;
					}
					while ($i-- > $clear) {
						array_shift($aseco->plugins['PluginRaspJukebox']->jb_buffer);
					}
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Clear history unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'add') {
			/**
			 * Adds MX maps to the map rotation.
			 */

			$source = 'MX';
			$remotelink = 'http://tm.mania-exchange.com/tracks/download/';

			if (count($command['params']) == 1) {
				$message = '{#server}» {#error}You must include a MX map ID!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				return;
			}

			// try all specified maps
			for ($id = 1; $id < count($command['params']); $id++) {
				// check for valid MX ID
				if (is_numeric($command['params'][$id]) && $command['params'][$id] >= 0) {
					$trkid = ltrim($command['params'][$id], '0');
					$file = $aseco->http_get_file($remotelink . $trkid);
					if ($file === false || $file == -1) {
						$message = '{#server}» {#error}Error downloading, or MX is down!';
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
					else {
						// check for maximum online map size
						if (strlen($file) >= $aseco->server->maps->size_limit) {
							$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['MAP_TOO_LARGE'][0],
								round(strlen($file) / $aseco->server->maps->size_limit)
							);
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							continue;
						}
						$sepchar = substr($aseco->server->mapdir, -1, 1);
						$partialdir = $aseco->plugins['PluginRasp']->mxdir . $sepchar . $trkid . '.Map.gbx';
						$localfile = $aseco->server->mapdir . $partialdir;
						if ($nocasepath = $aseco->file_exists_nocase($localfile)) {
							if (!unlink($nocasepath)) {
								$message = '{#server}» {#error}Error erasing old file - unable to erase {#highlite}$i ' . $localfile;
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
								continue;
							}
						}
						if (!$lfile = @fopen($localfile, 'wb')) {
							$message = '{#server}» {#error}Error creating file - unable to create {#highlite}$i ' . $localfile;
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							continue;
						}
						if (!fwrite($lfile, $file)) {
							$message = '{#server}» {#error}Error saving file - unable to write data';
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							fclose($lfile);
							continue;
						}
						fclose($lfile);

						$gbx = $aseco->server->maps->parseMap($localfile);
						if ( !isset($gbx->uid) ) {
							$message = '{#server}» {#error}No such map on '. $source .'!';
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							unlink($localfile);
							continue;
						}

						// check for map presence on server
						$tmp = $aseco->server->maps->getMapByUid($gbx->uid);
						if (isset($tmp->uid) && $tmp->uid == $gbx->uid) {
							$message = $aseco->plugins['PluginRasp']->messages['ADD_PRESENT'][0];
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							unlink($localfile);
							continue;
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
								$partialdir = $aseco->plugins['PluginRasp']->mxdir . $sepchar . $filename . '_' . $trkid . '-' . $i++ . '.Map.gbx';
							}
						}
						if ($dupl) {
							unlink($localfile);
						}
						else {
							rename($localfile, $aseco->server->mapdir . $partialdir);
						}

						// check map vs. server settings
						$rtn = $aseco->client->query('CheckMapForCurrentServerParams', $partialdir);
						if (!$rtn) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] CheckMapForCurrentServerParams - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
							$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['JUKEBOX_IGNORED'][0],
								$aseco->stripColors($gbx->name_stripped),
								$aseco->client->getErrorMessage()
							);
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						}
						else {
							// permanently add the map to the server list
							$rtn = $aseco->client->query('AddMap', $partialdir);
							if (!$rtn) {
								trigger_error('[' . $aseco->client->getErrorCode() . '] AddMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
							}
							else {
								$aseco->client->resetError();
								$aseco->client->query('GetMapInfo', $partialdir);
								$mapinfo = $aseco->client->getResponse();
								if ($aseco->client->isError()) {
									trigger_error('[' . $aseco->client->getErrorCode() . '] GetMapInfo - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
									$message = $aseco->formatText('{#server}» {#error}Error getting info on map {#highlite}$i {1} {#error}!',
									                      $partialdir);
									$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
								}
								else {
									$mapinfo['Name'] = $aseco->stripNewlines($mapinfo['Name']);

									// Create Map object
									$map = new Map($gbx, $mapinfo['FileName']);
									$map = $aseco->server->maps->insertMapIntoDatabase($map);
									$aseco->server->maps->map_list[$map->uid] = $map;

									// check whether to jukebox as well
									// overrules /add-ed but not yet played map
									if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['FileName'] = $map->filename;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['Name'] = $map->name;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['Env'] = $map->environment;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['Login'] = $login;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['Nick'] = $admin->nickname;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['source'] = $source;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['mx'] = false;
										$aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]['uid'] = $map->uid;
									}

									// log console message
									$aseco->console('[Admin] {1} [{2}] adds map [{3}] from {4}!', $logtitle, $login, $map->name_stripped, $source);

									// show chat message
									$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}adds {3}map: {#highlite}{4} {#admin}from {5}',
										$chattitle,
										$admin->nickname,
										((isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) ? '& jukeboxes ' : ''),
										$map->name_stripped,
										$source
									);
									$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

									// throw 'maplist changed' event
									$aseco->releaseEvent('onMapListChanged', array('add', $partialdir));

									// throw 'jukebox changed' event
									if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
										$aseco->releaseEvent('onJukeboxChanged', array('add', $aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]));
									}
								}
							}
						}
					}
				}
				else {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a valid MX map ID!',
						$command['params'][$id]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'addthis') {
			/**
			 * Adds current /add-ed map permanently to server's map list
			 * by preventing its removal that normally occurs afterwards
			 */

			if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
				// check for MX /add-ed map
				if ($aseco->plugins['PluginRaspJukebox']->mxplayed) {
					// remove map with old path
					$rtn = $aseco->client->query('RemoveMap', $aseco->plugins['PluginRaspJukebox']->mxplayed);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] RemoveMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						return;
					}
					else {
						// move the map file
						$mxnew = str_replace($aseco->plugins['PluginRasp']->mxtmpdir, $aseco->plugins['PluginRasp']->mxdir, $aseco->plugins['PluginRaspJukebox']->mxplayed);
						if (!rename($aseco->server->mapdir . $aseco->plugins['PluginRaspJukebox']->mxplayed, $aseco->server->mapdir . $mxnew)) {
							trigger_error('Could not rename MX map ' . $aseco->plugins['PluginRaspJukebox']->mxplayed . ' to ' . $mxnew, E_USER_WARNING);
							return;
						}
						else {
							// add map with new path
							$rtn = $aseco->client->query('AddMap', $mxnew);
							if (!$rtn) {
								trigger_error('[' . $aseco->client->getErrorCode() . '] AddMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
								return;
							}
							else {
								// store new path
								$aseco->server->maps->current->filename = $mxnew;

								// throw 'maplist changed' event
								$aseco->releaseEvent('onMapListChanged', array('rename', $mxnew));
							}
						}
					}

					// disable map removal afterwards
					$aseco->plugins['PluginRaspJukebox']->mxplayed = false;

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}permanently adds current map: {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$aseco->stripColors($aseco->server->maps->current->name)
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}Current map {#highlite}$i {1} {#error}already permanently in map list!',
						$aseco->stripColors($aseco->server->maps->current->name)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Addthis unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'addlocal') {
			/**
			 * Add a local map to the map rotation.
			 */

			// check for local map file
			if ($arglist[1] != '') {
				$sepchar = substr($aseco->server->mapdir, -1, 1);
				$partialdir = 'Downloaded' . $sepchar . $arglist[1];
				if (!stristr($partialdir, '.Map.gbx')) {
					$partialdir .= '.Map.gbx';
				}
				$localfile = $aseco->server->mapdir . $partialdir;
				if ($nocasepath = $aseco->file_exists_nocase($localfile)) {
					// check for maximum online map size (1024 KB)
					if (filesize($nocasepath) >= 1024 * 1024) {
						$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['MAP_TOO_LARGE'][0],
							round(filesize($nocasepath) / 1024)
						);
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						return;
					}
					$partialdir = str_replace($aseco->server->mapdir, '', $nocasepath);

					// check map vs. server settings
					$rtn = $aseco->client->query('CheckMapForCurrentServerParams', $partialdir);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] CheckMapForCurrentServerParams - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						$message = $aseco->formatText($aseco->plugins['PluginRasp']->messages['JUKEBOX_IGNORED'][0],
							$aseco->stripColors(str_replace('Downloaded' . $sepchar, '', $partialdir)),
							$aseco->client->getErrorMessage()
						);
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
					else {
						// permanently add the map to the server list
						$rtn = $aseco->client->query('AddMap', $partialdir);
						if (!$rtn) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] AddMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						}
						else {
							$aseco->client->resetError();
							$aseco->client->query('GetMapInfo', $partialdir);
							$mapinfo = $aseco->client->getResponse();
							if ($aseco->client->isError()) {
								trigger_error('[' . $aseco->client->getErrorCode() . '] GetMapInfo - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
								$message = $aseco->formatText('{#server}» {#error}Error getting info on map {#highlite}$i {1} {#error}!',
								                      $partialdir);
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}
							else {
								$mapinfo['Name'] = $aseco->stripNewlines($mapinfo['Name']);

								// check whether to jukebox as well
								// overrules /add-ed but not yet played map
								if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
									$uid = $mapinfo['UId'];
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $mapinfo['FileName'];
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $mapinfo['Name'];
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $mapinfo['Environnement'];
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $login;
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $admin->nickname;
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'Local';
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
									$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
								}

								// log console message
								$aseco->console('[Admin] {1} [{2}] adds local map {3} !', $logtitle, $login, $aseco->stripColors($mapinfo['Name'], false));

								// show chat message
								$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}adds {3}map: {#highlite}{4}',
									$chattitle,
									$admin->nickname,
									((isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) ? '& jukeboxes ' : ''),
									$aseco->stripColors($mapinfo['Name'])
								);
								$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

								// throw 'maplist changed' event
								$aseco->releaseEvent('onMapListChanged', array('add', $partialdir));

								// throw 'jukebox changed' event
								if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
									$aseco->releaseEvent('onJukeboxChanged', array('add', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
								}
							}
						}
					}
				}
				else {
					$message = '{#server}» {#highlite}' . $partialdir . '{#error} not found!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = '{#server}» {#error}You must include a local map filename!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'warn' && $command['params'][1] != '') {
			/**
			 * Warns a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// display warning message
				$message = $aseco->getChatMessage('WARNING');
				$message = preg_split('/{br}/', $aseco->formatColors($message));
				foreach ($message as &$line)
					$line = array($line);

				$aseco->plugins['PluginManialinks']->display_manialink($target->login, $aseco->formatColors('{#welcome}WARNING:'), array('Icons64x64_1', 'TV'),
					$message,
					array(0.8),
					'OK'
				);

				// log console message
				$aseco->console('[Admin] {1} [{2}] warned Player [{3}], Nickname [{4}]!',
					$logtitle,
					$login,
					$target->login,
					$aseco->stripColors($target->nickname, false)
				);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} warned {#highlite}{3}$z$s{#admin} !',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
		else if ($command['params'][0] == 'kick' && $command['params'][1] != '') {
			/**
			 * Kicks a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// log console message
				$aseco->console('{1} [{2}] kicked player {3}!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} kicked {#highlite}{3}$z$s{#admin} !',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// kick the player
				$aseco->client->query('Kick', $target->login);
			}
		}
		else if ($command['params'][0] == 'kickghost' && $command['params'][1] != '') {
			/**
			 * Kicks a ghost player with the specified login.
			 * This variant for ghost players that got disconnected doesn't
			 * check the login for validity and doesn't work with Player_IDs.
			 */

			// get player login without validation
			$target = $command['params'][1];

			// log console message
			$aseco->console('{1} [{2}] kicked ghost player {3}!', $logtitle, $login, $target);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} kicked ghost {#highlite}{3}$z$s{#admin} !',
				$chattitle,
				$admin->nickname,
				$target
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

			// kick the ghost player
			$aseco->client->query('Kick', $target);
		}
		else if ($command['params'][0] == 'ban' && $command['params'][1] != '') {
			/**
			 * Ban a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// log console message
				$aseco->console('{1} [{2}] bans player {3}!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} bans {#highlite}{3}$z$s{#admin} !',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// update banned IPs file
				$aseco->banned_ips[] = $target->ip;
				$aseco->writeIPs();

				// ban the player and also kick him
				$aseco->client->query('Ban', $target->login);
			}
		}
		else if ($command['params'][0] == 'unban' && $command['params'][1] != '') {
			/**
			 * Un-bans player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				$bans = $this->getBanlist($aseco);
				// unban the player
				$rtn = $aseco->client->query('UnBan', $target->login);
				if (!$rtn) {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a banned player!',
					                      $command['params'][1]);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					if (($i = array_search($bans[$target->login][2], $aseco->banned_ips)) !== false) {
						// update banned IPs file
						$aseco->banned_ips[$i] = '';
						$aseco->writeIPs();
					}

					// log console message
					$aseco->console('{1} [{2}] unbans player {3}', $logtitle, $login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} un-bans {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						str_ireplace('$w', '', $target->nickname)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'banip' && $command['params'][1] != '') {
			/**
			 * Ban a player with the specified IP address.
			 */

			// check for valid IP not already banned
			$ipaddr = $command['params'][1];
			if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ipaddr)) {
				if (empty($aseco->banned_ips) || !in_array($ipaddr, $aseco->banned_ips)) {
					// log console message
					$aseco->console('{1} [{2}] banned IP {3}!', $logtitle, $login, $ipaddr);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} bans IP {#highlite}{3}$z$s{#admin} !',
						$chattitle,
						$admin->nickname,
						$ipaddr
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

					// update banned IPs file
					$aseco->banned_ips[] = $ipaddr;
					$aseco->writeIPs();
				}
				else {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is already banned!',
						$ipaddr
					);
				}
			}
			else {
				$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a valid IP address!',
					$ipaddr
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'unbanip' && $command['params'][1] != '') {
			/**
			 * Un-bans player with the specified IP address.
			 */

			// check for banned IP
			if (($i = array_search($command['params'][1], $aseco->banned_ips)) === false) {
				$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a banned IP address!',
					$command['params'][1]
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				// update banned IPs file
				$aseco->banned_ips[$i] = '';
				$aseco->writeIPs();

				// log console message
				$aseco->console('{1} [{2}] unbans IP {3}', $logtitle, $login, $command['params'][1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} un-bans IP {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$command['params'][1]
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'black' && $command['params'][1] != '') {
			/**
			 * Blacklists a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// log console message
				$aseco->console('{1} [{2}] blacklists player {3}!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} blacklists {#highlite}{3}$z$s{#admin} !',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// blacklist the player and then kick him
				$aseco->client->query('BlackList', $target->login);
				$aseco->client->query('Kick', $target->login);

				// update blacklist file
				$filename = $aseco->settings['blacklist_file'];
				$rtn = $aseco->client->query('SaveBlackList', $filename);
				if (!$rtn) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] SaveBlackList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				}
			}
		}
		else if ($command['params'][0] == 'unblack' && $command['params'][1] != '') {
			/**
			 * Un-blacklists player with the specified login/PlayerID.
			 */

			$target = false;
			$param = $command['params'][1];

			// get new list of all blacklisted players
			$blacks = $this->getBlacklist($aseco);
			// check as login
			if (array_key_exists($param, $blacks)) {
				$target = new Player();
			}
			else if (is_numeric($param) && $param > 0) {
				// check as player ID

				if (empty($admin->playerlist)) {
					$message = '{#server}» {#error}Use {#highlite}$i/players {#error}first (optionally {#highlite}$i/players <string>{#error})';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return false;
				}
				$pid = ltrim($param, '0');
				$pid--;
				// find player by given #
				if (array_key_exists($pid, $admin->playerlist)) {
					$param = $admin->playerlist[$pid]['login'];
					$target = new Player();
				}
				else {
					$message = '{#server}» {#error}Player_ID not found! Type {#highlite}$i/players {#error}to see all players.';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return false;
				}
			}

			// check for valid param
			if ($target !== false) {
				$target->login = $param;
				$target->nickname = $aseco->server->players->getPlayerNickname($param);
				if ($target->nickname == '')
					$target->nickname = $param;

				// unblacklist the player
				$rtn = $aseco->client->query('UnBlackList', $target->login);
				if (!$rtn) {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a blacklisted player!',
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					// log console message
					$aseco->console('{1} [{2}] unblacklists player {3}', $logtitle, $login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} un-blacklists {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						str_ireplace('$w', '', $target->nickname)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);

					// update blacklist file
					$filename = $aseco->settings['blacklist_file'];
					$rtn = $aseco->client->query('SaveBlackList', $filename);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] SaveBlackList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					}
				}
			}
			else {
				$message = '{#server}» {#highlite}' . $param . ' {#error}is not a valid player! Use {#highlite}$i/players {#error}to find the correct login or Player_ID.';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'addguest' && $command['params'][1] != '') {
			/**
			 * Adds a guest player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// add the guest player
				$aseco->client->query('AddGuest', $target->login);

				// log console message
				$aseco->console('{1} [{2}] adds guest player {3}', $logtitle, $login, $aseco->stripColors($target->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} adds guest {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);

				// update guestlist file
				$filename = $aseco->settings['guestlist_file'];
				$rtn = $aseco->client->query('SaveGuestList', $filename);
				if (!$rtn) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] SaveGuestList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				}
			}
		}
		else if ($command['params'][0] == 'removeguest' && $command['params'][1] != '') {
			/**
			 * Removes a guest player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// remove the guest player
				$rtn = $aseco->client->query('RemoveGuest', $target->login);
				if (!$rtn) {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a guest player!',
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					// log console message
					$aseco->console('{1} [{2}] removes guest player {3}', $logtitle, $login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} removes guest {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						str_ireplace('$w', '', $target->nickname)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);

					// update guestlist file
					$filename = $aseco->settings['guestlist_file'];
					$rtn = $aseco->client->query('SaveGuestList', $filename);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] SaveGuestList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					}
				}
			}
		}
		else if ($command['params'][0] == 'pass') {
			/**
			 * Passes a chat-based or MX /add vote.
			 */

			// pass any MX and chat vote
			if (isset($aseco->plugins['PluginRaspJukebox']) && !empty($aseco->plugins['PluginRaspJukebox']->mxadd)) {
				// force required votes down to the last one
				$aseco->plugins['PluginRaspJukebox']->mxadd['votes'] = 1;
			}
			else if (isset($aseco->plugins['PluginRaspVotes']) && !empty($aseco->plugins['PluginRaspVotes']->chatvote)) {
				$aseco->plugins['PluginRaspVotes']->chatvote['votes'] = 1;
			}
			else {  // no vote in progress
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}There is no vote right now!'), $login);
				return;
			}

			// log console message
			$aseco->console('{1} [{2}] passes vote!', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} passes vote!',
				$chattitle,
				$admin->nickname
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

			// bypass double vote check
			if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
				$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
			}

			// enter the last vote
			$aseco->releaseChatCommand('/y', $login);
		}
		else if ($command['params'][0] == 'cancel' || $command['params'][0] == 'can') {
			/**
			 * Cancels any vote.
			 */

			// cancel any CallVote, MX and chat vote
			$aseco->client->query('CancelVote');
			if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
				$aseco->plugins['PluginRaspJukebox']->mxadd = array();
			}
			if ( isset($aseco->plugins['PluginRaspVotes']) ) {
				$aseco->plugins['PluginRaspVotes']->chatvote = array();

				// disable all vote panels
				if ( isset($aseco->plugins['PluginPanels']) ) {
					$aseco->plugins['PluginPanels']->allvotepanels_off($aseco);
				}
			}

			// log console message
			$aseco->console('{1} [{2}] cancels vote!', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} cancels vote!',
				$chattitle,
				$admin->nickname
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'endround' || $command['params'][0] == 'er') {
			/**
			 * Forces end of current round.
			 */

			// cancel possibly ongoing endround vote
			if ( isset($aseco->plugins['PluginRaspVotes']) ) {
				if (!empty($aseco->plugins['PluginRaspVotes']->chatvote) && $aseco->plugins['PluginRaspVotes']->chatvote['type'] == 0) {
					$aseco->plugins['PluginRaspVotes']->chatvote = array();

					// disable all vote panels
					if ( isset($aseco->plugins['PluginPanels']) ) {
						$aseco->plugins['PluginPanels']->allvotepanels_off($aseco);
					}
				}
			}

			// end this round
			$aseco->client->query('TriggerModeScriptEvent', 'Rounds_ForceEndRound', '');

			// log console message
			$aseco->console('{1} [{2}] forces round end!', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} forces round end!',
				$chattitle,
				$admin->nickname
			);
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
		else if ($command['params'][0] == 'players') {
			/**
			 * Displays the live or known players (on/offline) list.
			 * Player management inspired by Mistral.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// remember players parameter for possible refresh
			$admin->panels['plyparam'] = $command['params'][1];
			$onlineonly = (strtolower($command['params'][1]) == 'live');
			// get current ignore/ban/black/guest lists
			$ignores = $this->getIgnorelist($aseco);
			$bans = $this->getBanlist($aseco);
			$blacks = $this->getBlacklist($aseco);
			$guests = $this->getGuestlist($aseco);

			// create new list of online players
			$aseco->client->resetError();
			$onlinelist = array();
			// get current players on the server (hardlimited to 300)
			$aseco->client->query('GetPlayerList', 300, 0, 1);
			$players = $aseco->client->getResponse();
			if ($aseco->client->isError()) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] GetPlayerList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
			}
			else {
				foreach ($players as $pl) {
					// on relay, check for player from master server
					if (!$aseco->server->isrelay || floor($pl['Flags'] / 10000) % 10 == 0) {
						$onlinelist[$pl['Login']] = array(
							'login' => $pl['Login'],
							'nick' => $pl['NickName'],
							'spec' => $pl['SpectatorStatus']
						);
					}
				}
			}

			// use online list?
			if ($onlineonly) {
				$playerlist = $onlinelist;
			}
			else {
				// search for known players
				$playerlist = array();
				$query = "
				SELECT
					`Login`,
					`NickName`
				FROM `players`
				WHERE `Login` LIKE ". $aseco->mysqli->quote('%'. $arglist[1] .'%') ."
				OR `NickName` LIKE ". $aseco->mysqli->quote('%'. $arglist[1] .'%') ."
				LIMIT 5000;
				";
				$result = $aseco->mysqli->query($query);
				if ($result) {
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_row()) {
							// skip any LAN logins
							if (!$aseco->isLANLogin($row[0])) {
								$playerlist[$row[0]] = array(
									'login' => $row[0],
									'nick' => $row[1],
									'spec' => false
								);
							}
						}
					}
					$result->free_result();
				}
			}

			if (!empty($playerlist)) {
				$head = ($onlineonly ? 'Online' : 'Known') . ' Players On This Server:';
				$msg = array();
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login', 'Warn', 'Ignore', 'Kick', 'Ban', 'Black', 'Guest', 'Spec');
				$pid = 1;
				$lines = 0;
				$admin->msgs[0] = array(1, $head, array(1.49, 0.15, 0.5, 0.12, 0.12, 0.12, 0.12, 0.12, 0.12, 0.12), array('Icons128x128_1', 'Buddies'));

				foreach ($playerlist as $lg => $pl) {
					$plarr = array();
					$plarr['login'] = $lg;
					$admin->playerlist[] = $plarr;

					// format nickname & login
					$ply = '{#black}' . str_ireplace('$w', '', $pl['nick']) . '$z / '
					       . ($aseco->isAnyAdminL($pl['login']) ? '{#logina}' : '{#login}' )
					       . $pl['login'];
					// define colored column strings
					$wrn = '$ff3Warn';
					$ign = '$f93Ignore';
					$uig = '$d93UnIgn';
					$kck = '$c3fKick';
					$ban = '$f30Ban';
					$ubn = '$c30UnBan';
					$blk = '$f03Black';
					$ubk = '$c03UnBlack';
					$gst = '$3c3Add';
					$ugt = '$393Remove';
					$frc = '$09fForce';
					$off = '$09cOffln';
					$spc = '$09cSpec';

					// always add clickable buttons
					if ($pid <= 200) {
						$ply = array($ply, $pid + 2000);
						if (array_key_exists($lg, $onlinelist)) {
							// determine online operations
							$wrn = array($wrn,   $pid+2200);
							if (array_key_exists($lg, $ignores)) {
								$ign = array($uig, $pid+2600);
							}
							else {
								$ign = array($ign, $pid+2400);
							}
							$kck = array($kck,   $pid+2800);
							if (array_key_exists($lg, $bans)) {
								$ban = array($ubn, $pid+3200);
							}
							else {
								$ban = array($ban, $pid+3000);
							}
							if (array_key_exists($lg, $blacks)) {
								$blk = array($ubk, $pid+3600);
							}
							else {
								$blk = array($blk, $pid+3400);
							}
							if (array_key_exists($lg, $guests)) {
								$gst = array($ugt, $pid+4000);
							}
							else {
								$gst = array($gst, $pid+3800);
							}
							if (!$onlinelist[$lg]['spec']) {
								$spc = array($frc, $pid+4200);
							}
						}
						else {
							// determine offline operations
							if (array_key_exists($lg, $ignores)) {
								$ign = array($uig, $pid+2600);
							}
							if (array_key_exists($lg, $bans)) {
								$ban = array($ubn, $pid+3200);
							}
							if (array_key_exists($lg, $blacks)) {
								$blk = array($ubk, $pid+3600);
							}
							else {
								$blk = array($blk, $pid+3400);
							}
							if (array_key_exists($lg, $guests)) {
								$gst = array($ugt, $pid+4000);
							}
							else {
								$gst = array($gst, $pid+3800);
							}
							$spc = $off;
						}
					}
					else {
						// no more buttons
						if (array_key_exists($lg, $ignores)) {
							$ign = $uig;
						}
						if (array_key_exists($lg, $bans)) {
							$ban = $ubn;
						}
						if (array_key_exists($lg, $blacks)) {
							$blk = $ubk;
						}
						if (array_key_exists($lg, $guests)) {
							$gst = $ugt;
						}
						if (array_key_exists($lg, $onlinelist)) {
							if (!$onlinelist[$lg]['spec']) {
								$spc = $frc;
							}
						}
						else {
							$spc = $off;
						}
					}

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply,
					               $wrn, $ign, $kck, $ban, $blk, $gst, $spc);
					$pid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login', 'Warn', 'Ignore', 'Kick', 'Ban', 'Black', 'Guest', 'Spec');
					}
				}

				// add if last batch exists
				if (count($msg) > 1) {
					$admin->msgs[] = $msg;
				}

				// display ManiaLink message
				if (count($admin->msgs) > 1) {
					$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
				}
				else {  // == 1
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No player(s) found!'), $login);
				}
			}
			else {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No player(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'showbanlist' || $command['params'][0] == 'listbans') {
			/**
			 * Displays the ban list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all banned players
			$newlist = $this->getBanlist($aseco);

			$head = 'Currently Banned Players:';
			$msg = array();
			if ($aseco->settings['clickable_lists']) {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnBan)');
			}
			else {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			}
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons64x64_1', 'NotBuddy'));
			foreach ($newlist as $player) {
				$plarr = array();
				$plarr['login'] = $player[0];
				$admin->playerlist[] = $plarr;

				// format nickname & login
				$ply = '{#black}' . str_ireplace('$w', '', $player[1])
				       . '$z / {#login}' . $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, $pid+4600);  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply);
				$pid++;
				if (++$lines > 14) {
					$admin->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					if ($aseco->settings['clickable_lists']) {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnBan)');
					}
					else {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No banned player(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'showiplist' || $command['params'][0] == 'listips') {
			/**
			 * Displays the banned IPs list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all banned IPs
			$newlist = $aseco->banned_ips;
			if (empty($newlist)) {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No banned IP(s) found!'), $login);
				return;
			}

			$head = 'Currently Banned IPs:';
			$msg = array();
			if ($aseco->settings['clickable_lists']) {
				$msg[] = array('Id', '{#nick}IP$g (click to UnBan)');
			}
			else {
				$msg[] = array('Id', '{#nick}IP');
			}
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.6, 0.1, 0.5), array('Icons64x64_1', 'NotBuddy'));
			foreach ($newlist as $ip) {
				if ($ip != '') {
					$plarr = array();
					$plarr['ip'] = $ip;
					$admin->playerlist[] = $plarr;

					// format IP
					$ply = '{#black}' . $ip;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $pid <= 200) {
						$ply = array($ply, -7900-$pid);  // action id
					}

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply);
					$pid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						if ($aseco->settings['clickable_lists']) {
							$msg[] = array('Id', '{#login}IP$g (click to UnBan)');
						}
						else {
							$msg[] = array('Id', '{#login}IP');
						}
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No banned IP(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'showblacklist' || $command['params'][0] == 'listblacks') {
			/**
			 * Displays the black list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all blacklisted players
			$newlist = $this->getBlacklist($aseco);

			$head = 'Currently Blacklisted Players:';
			$msg = array();
			if ($aseco->settings['clickable_lists']) {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnBlack)');
			}
			else {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			}
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons64x64_1', 'NotBuddy'));
			foreach ($newlist as $player) {
				$plarr = array();
				$plarr['login'] = $player[0];
				$admin->playerlist[] = $plarr;

				// format nickname & login
				$ply = '{#black}' . str_ireplace('$w', '', $player[1])
				       . '$z / {#login}' . $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, $pid+4800);  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply);
				$pid++;
				if (++$lines > 14) {
					$admin->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					if ($aseco->settings['clickable_lists']) {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnBlack)');
					}
					else {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No blacklisted player(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'showguestlist' || $command['params'][0] == 'listguests') {
			/**
			 * Displays the guest list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all guest players
			$newlist = $this->getGuestlist($aseco);

			$head = 'Current Guest Players:';
			$msg = array();
			if ($aseco->settings['clickable_lists']) {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to Remove)');
			}
			else {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			}
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Invite'));
			foreach ($newlist as $player) {
				$plarr = array();
				$plarr['login'] = $player[0];
				$admin->playerlist[] = $plarr;

				// format nickname & login
				$ply = '{#black}' . str_ireplace('$w', '', $player[1])
				       . '$z / {#login}' . $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, $pid+5000);  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply);
				$pid++;
				if (++$lines > 14) {
					$admin->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					if ($aseco->settings['clickable_lists']) {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to Remove)');
					}
					else {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No guest player(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'writeiplist') {
			/**
			 * Saves the banned IPs list to bannedips.xml (default).
			 */

			// write banned IPs file
			$filename = $aseco->settings['bannedips_file'];
			if (!$aseco->writeIPs()) {
				$message = '{#server}» {#error}Error writing {#highlite}$i ' . $filename . ' {#error}!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] wrote ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}written';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'readiplist') {
			/**
			 * Loads the banned IPs list from bannedips.xml (default).
			 */

			// read banned IPs file
			$filename = $aseco->settings['bannedips_file'];
			if (!$aseco->readIPs()) {
				$message = '{#server}» {#highlite}' . $filename . ' {#error}not found, or error reading!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] read ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}read';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'writeblacklist') {
			/**
			 * Saves the black list to blacklist.txt (default).
			 */

			$filename = $aseco->settings['blacklist_file'];
			$rtn = $aseco->client->query('SaveBlackList', $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] SaveBlackList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = '{#server}» {#error}Error writing {#highlite}$i ' . $filename . ' {#error}!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] wrote ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}written';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'readblacklist') {
			/**
			 * Loads the black list from blacklist.txt (default).
			 */

			$filename = $aseco->settings['blacklist_file'];
			$rtn = $aseco->client->query('LoadBlackList', $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] LoadBlackList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = '{#server}» {#highlite}' . $filename . ' {#error}not found, or error reading!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] read ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}read';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'writeguestlist') {
			/**
			 * Saves the guest list to guestlist.txt (default).
			 */

			$filename = $aseco->settings['guestlist_file'];
			$rtn = $aseco->client->query('SaveGuestList', $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] SaveGuestList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = '{#server}» {#error}Error writing {#highlite}$i ' . $filename . ' {#error}!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] wrote ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}written';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'readguestlist') {
			/**
			 * Loads the guest list from guestlist.txt (default).
			 */

			$filename = $aseco->settings['guestlist_file'];
			$rtn = $aseco->client->query('LoadGuestList', $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] LoadGuestList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = '{#server}» {#highlite}' . $filename . ' {#error}not found, or error loading!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] read ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}read';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'cleanbanlist') {
			/**
			 * Cleans the ban list.
			 */

			// clean server ban list
			$aseco->client->query('CleanBanList');

			// log console message
			$aseco->console('{1} [{2}] cleaned ban list!', $logtitle, $login);

			// show chat message
			$message = '{#server}» {#admin}Cleaned ban list!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'cleaniplist') {
			/**
			 * Cleans the banned IPs list.
			 */

			// clean banned IPs file
			$aseco->banned_ips = array();
			$aseco->writeIPs();

			// log console message
			$aseco->console('{1} [{2}] cleaned banned IPs list!', $logtitle, $login);

			// show chat message
			$message = '{#server}» {#admin}Cleaned banned IPs list!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'cleanblacklist') {
			/**
			 * Cleans the black list.
			 */

			// clean server black list
			$aseco->client->query('CleanBlackList');

			// log console message
			$aseco->console('{1} [{2}] cleaned black list!', $logtitle, $login);

			// show chat message
			$message = '{#server}» {#admin}Cleaned black list!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'cleanguestlist') {
			/**
			 * Cleans the guest list.
			 */

			// clean server guest list
			$aseco->client->query('CleanGuestList');

			// log console message
			$aseco->console('{1} [{2}] cleaned guest list!', $logtitle, $login);

			// show chat message
			$message = '{#server}» {#admin}Cleaned guest list!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'mergegbl') {
			/**
			 * Merges a global black list.
			 */

			if ( isset($aseco->plugins['PluginUptodate']) ) {
				if (isset($command['params'][1]) && $command['params'][1] != '') {
					if (preg_match('/^https?:\/\/[-\w:.]+\//i', $command['params'][1])) {
						// from plugin.uptodate.php
						$aseco->plugins['PluginUptodate']->admin_mergegbl($aseco, $logtitle, $login, true, $command['params'][1]);
					}
					else {
						$message = '{#server}» {#highlite}' . $command['params'][1] . ' {#error}is an invalid HTTP URL!';
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
				}
				else {
					// from plugin.uptodate.php
					$aseco->plugins['PluginUptodate']->admin_mergegbl($aseco, $logtitle, $login, true, $aseco->settings['global_blacklist_url']);
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Merge Global BL unavailable - include plugin.uptodate.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'access') {
			/**
			 * Shows/reloads player access control.
			 */

			if ( isset($aseco->plugins['PluginAccessControl']) ) {
				$aseco->plugins['PluginAccessControl']->admin_access($aseco, $login, $command['params'][1]);
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Access control unavailable - include plugin.access.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'writemaplist') {
			/**
			 * Saves the map list to maplist.txt (default).
			 */

			$filename = $aseco->settings['default_maplist'];
			// check for optional alternate filename
			if ($command['params'][1] != '') {
				$filename = $command['params'][1];
				if (!stristr($filename, '.txt')) {
					$filename .= '.txt';
				}
			}
			$rtn = $aseco->client->query('SaveMatchSettings', 'MatchSettings/' . $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] SaveMatchSettings - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = '{#server}» {#error}Error writing {#highlite}$i ' . $filename . ' {#error}!';
			}
			else {
				// should a random filter be added?
				if ($aseco->settings['writemaplist_random']) {
					$mapsfile = $aseco->server->mapdir . 'MatchSettings/' . $filename;
					// read the match settings file
					if (!$list = @file_get_contents($mapsfile)) {
						trigger_error('Could not read match settings file ' . $mapsfile . ' !', E_USER_WARNING);
					}
					else {
						// insert random filter after <gameinfos> section
						$list = preg_replace('/<\/gameinfos>/', '$0' . CRLF . CRLF .
							"\t<filter>" . CRLF .
							"\t\t<random_map_order>1</random_map_order>" . CRLF .
							"\t</filter>", $list
						);

						// write out the match settings file
						if (!@file_put_contents($mapsfile, $list)) {
							trigger_error('Could not write match settings file ' . $mapsfile . ' !', E_USER_WARNING);
						}
					}
				}

				// log console message
				$aseco->console('{1} [{2}] wrote map list: {3} !', $logtitle, $login, $filename);

				$message = '{#server}» {#highlite}' . $filename . '{#admin} written';

				// throw 'maplist changed' event
				$aseco->releaseEvent('onMapListChanged', array('write', null));
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'readmaplist') {
			/**
			 * Loads the map list from maplist.txt (default).
			 */

			$filename = $aseco->settings['default_maplist'];
			// check for optional alternate filename
			if ($command['params'][1] != '') {
				$filename = $command['params'][1];
				if (!stristr($filename, '.txt')) {
					$filename .= '.txt';
				}
			}
			if (file_exists($aseco->server->mapdir . 'MatchSettings/' . $filename)) {
				$rtn = $aseco->client->query('LoadMatchSettings', 'MatchSettings/' . $filename);
				if (!$rtn) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] LoadMatchSettings - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					$message = '{#server}» {#error}Error reading {#highlite}$i ' . $filename . ' {#error}!';
				}
				else {
					// get map count
					$cnt = $aseco->client->getResponse();

					// log console message
					$aseco->console('{1} [{2}] read map list: {3} ({4} maps)!', $logtitle, $login, $filename, $cnt);

					$message = '{#server}» {#highlite}' . $filename . '{#admin} read with {#highlite}' . $cnt . '{#admin} map' . ($cnt == 1 ? '' : 's');

					// throw 'maplist changed' event
					$aseco->releaseEvent('onMapListChanged', array('read', null));
				}
			}
			else {
				$message = '{#server}» {#error}Cannot find {#highlite}$i ' . $filename . ' {#error}!';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'shuffle' || $command['params'][0] == 'shufflemaps') {
			/**
			 * Randomizes current maps list.
			 */

			if ($aseco->settings['writemaplist_random']) {
				if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings) {
					if (file_exists($aseco->server->mapdir . 'MatchSettings/' . $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings)) {
						$rtn = $aseco->client->query('LoadMatchSettings', 'MatchSettings/' . $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings);
						if (!$rtn) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] LoadMatchSettings - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
							$message = '{#server}» {#error}Error reading {#highlite}$i ' . $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings . ' {#error}!';
						}
						else {
							// get map count
							$cnt = $aseco->client->getResponse();

							// log console message
							$aseco->console('{1} [{2}] shuffled map list: {3} ({4} maps)!', $logtitle, $login, $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings, $cnt);

							$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} shuffled map list with {#highlite}{3}{#admin} map{4}!',
								$chattitle,
								$admin->nickname,
								$cnt,
								($cnt == 1 ? '' : 's')
							);
							$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
							return;
						}
					}
					else {
						$message = '{#server}» {#error}Cannot find autosave matchsettings file {#highlite}$i '. $aseco->plugins['PluginRaspJukebox']->autosave_matchsettings .' {#error}!';
					}
				}
				else {
					$message = '{#server}» {#error}No autosave matchsettings file defined in {#highlite}$i [config/rasp.xml] or [plugin.rasp_jukebox.php] not enabled{#error}!';
				}
			}
			else {
				$message = '{#server}» {#error}No maplist randomization defined in {#highlite}$i [config/UASECO.xml] {#error}!';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'listdupes') {
			/**
			 * Displays list of duplicate maps.
			 */

			$admin->maplist = array();
			$admin->msgs = array();

			// get new list of all maps
			$aseco->client->resetError();
			$dupelist = array();
			$newlist = array();
			$done = false;
			$size = 300;
			$i = 0;
			while (!$done) {
				$aseco->client->query('GetMapList', $size, $i);
				$maps = $aseco->client->getResponse();
				if (!empty($maps)) {
					if ($aseco->client->isError()) {
						// warning if no maps found
						if (empty($newlist)) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] GetMapList - ' . $aseco->client->getErrorMessage() . ' - No maps found!', E_USER_WARNING);
						}
						$done = true;
						break;
					}
					foreach ($maps as $trow) {
						$trow['Name'] = $aseco->stripNewlines($trow['Name']);
						// store duplicate maps
						if (isset($newlist[$trow['UId']])) {
							$dupelist[] = $trow;
						}
						else {
							$newlist[$trow['UId']] = $trow;
						}
					}
					if (count($maps) < $size) {
						// got less than 300 maps, might as well leave
						$done = true;
					}
					else {
						$i += $size;
					}
				}
				else {
					$done = true;
				}
			}

			// check for duplicate maps
			if (!empty($dupelist)) {
				$head = 'Duplicate Maps On This Server:';
				$msg = array();
				$msg[] = array('Id', 'Name');
				$tid = 1;
				$lines = 0;

				// reserve extra width for $w tags
				$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);

				$admin->msgs[0] = array(1, $head, array(0.75+$extra, 0.15, 0.6+$extra), array('Icons128x128_1', 'Challenge'));
				foreach ($dupelist as $row) {
					$mapname = $aseco->stripColors($row['Name']);
					if (!$aseco->settings['lists_colormaps']) {
						$mapname = $aseco->stripColors($mapname);
					}

					// store map in player object for remove/erase
					$trkarr = array();
					$trkarr['name'] = $row['Name'];
					$trkarr['environment'] = $row['Environnement'];
					$trkarr['filename'] = $row['FileName'];
					$trkarr['uid'] = $row['UId'];
					$admin->maplist[] = $trkarr;

					$msg[] = array(
						str_pad($tid, 3, '0', STR_PAD_LEFT) . '.',
						'{#black}' . $mapname
					);
					$tid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						$msg[] = array('Id', 'Name');
					}
				}

				// add if last batch exists
				if (count($msg) > 1) {
					$admin->msgs[] = $msg;
				}

				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);

			}
			else {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No duplicate map(s) found!'), $login);
				return;
			}
		}
		else if (($command['params'][0] == 'remove' && $command['params'][1] != '') || ($command['params'][0] == 'erase' && $command['params'][1] != '')) {
			/**
			 * Remove a map from the active rotation, optionally erase map file too.
			 * Doesn't update match settings unfortunately - command 'writemaplist' will though.
			 */

			// verify parameter
			$param = $command['params'][1];
			if (is_numeric($param) && $param >= 0) {
				if (empty($admin->maplist)) {
					$message = $aseco->plugins['PluginRasp']->messages['LIST_HELP'][0];
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return;
				}
				// find map by given #
				$tid = ltrim($param, '0');
				$tid--;
				if (array_key_exists($tid, $admin->maplist)) {
					$name = $aseco->stripColors($admin->maplist[$tid]['name']);
					$filename = $aseco->server->mapdir . $admin->maplist[$tid]['filename'];
					$rtn = $aseco->client->query('RemoveMap', $filename);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] RemoveMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						$message = $aseco->formatText('{#server}» {#error}Error removing map {#highlite}$i {1} {#error}!',
							$filename
						);
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
					else {
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}removes map: {#highlite}{3}',
							$chattitle,
							$admin->nickname,
							$name
						);
						if ($command['params'][0] == 'erase' && is_file($filename)) {
							if (unlink($filename)) {
								$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}erases map: {#highlite}{3}',
									$chattitle,
									$admin->nickname,
									$name
								);
							}
							else {
								$message = '{#server}» {#error}Delete file {#highlite}$i ' . $filename . '{#error} failed';
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
								$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}erase map failed: {#highlite}{3}',
									$chattitle,
									$admin->nickname,
									$name
								);
							}
						}
						// show chat message
						$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
						// log console message
						$aseco->console('{1} [{2}] ' . $command['params'][0] . 'd map {3}', $logtitle, $login, $aseco->stripColors($name, false));

						// throw 'maplist changed' event
						$aseco->releaseEvent('onMapListChanged', array('remove', $filename));
					}
				}
				else {
					$message = $aseco->plugins['PluginRasp']->messages['JUKEBOX_NOTFOUND'][0];
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = $aseco->plugins['PluginRasp']->messages['JUKEBOX_HELP'][0];
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'removethis' || $command['params'][0] == 'erasethis') {
			/**
			 * Remove current map from the active rotation, optionally erase map file too.
			 * Doesn't update match settings unfortunately - command 'writemaplist' will though.
			 */

			// get current map info and remove it from rotation
			$name = $aseco->stripColors($aseco->server->maps->current->name);
			$filename = $aseco->server->mapdir . $aseco->server->maps->current->filename;
			$rtn = $aseco->client->query('RemoveMap', $filename);
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] RemoveMap - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				$message = $aseco->formatText('{#server}» {#error}Error removing map {#highlite}$i {1} {#error}!',
					$filename
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}removes current map: {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$name
				);
				if ($command['params'][0] == 'erasethis' && is_file($filename)) {
					if (unlink($filename)) {
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}erases current map: {#highlite}{3}',
							$chattitle,
							$admin->nickname,
							$name
						);
					}
					else {
						$message = '{#server}» {#error}Delete file {#highlite}$i ' . $filename . '{#error} failed';
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}erase map failed: {#highlite}{3}',
							$chattitle,
							$admin->nickname,
							$name
						);
					}
				}
				// show chat message
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				// log console message
				$aseco->console('{1} [{2}] ' . $command['params'][0] . '-ed map {3}', $logtitle, $login, $aseco->stripColors($name, false));

				// throw 'maplist changed' event
				$aseco->releaseEvent('onMapListChanged', array('remove', $filename));
			}
		}
		else if (($command['params'][0] == 'mute' || $command['params'][0] == 'ignore') && $command['params'][1] != '') {
			/**
			 * Adds a player to global mute/ignore list
			 */


			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// ignore the player
				$aseco->client->query('Ignore', $target->login);

				// check if in global mute/ignore list
				if (!in_array($target->login, $aseco->server->mutelist)) {
					// add player to list
					$aseco->server->mutelist[] = $target->login;
				}

				// log console message
				$aseco->console('{1} [{2}] ignores player {3}!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} ignores {#highlite}{3}$z$s{#admin} !',
					$chattitle,
					$admin->nickname,
					str_ireplace('$w', '', $target->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
		}
		else if (($command['params'][0] == 'unmute' || $command['params'][0] == 'unignore') && $command['params'][1] != '') {
			/**
			 * Removes a player from global mute/ignore list
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// unignore the player
				$rtn = $aseco->client->query('UnIgnore', $target->login);
				if (!$rtn) {
					$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not an ignored player!',
					                      $command['params'][1]);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					// check if in global mute/ignore list
					if (($i = array_search($target->login, $aseco->server->mutelist)) !== false) {
						// remove player from list
						$aseco->server->mutelist[$i] = '';
					}

					// log console message
					$aseco->console('{1} [{2}] unignores player {3}', $logtitle, $login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} un-ignores {#highlite}{3}',
					                      $chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'mutelist' || $command['params'][0] == 'listmutes' || $command['params'][0] == 'ignorelist' || $command['params'][0] == 'listignores') {
			/**
			 * Displays the global mute/ignore list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all ignored players
			$newlist = $this->getIgnorelist($aseco);

			$head = 'Globally Muted/Ignored Players:';
			$msg = array();
			if ($aseco->settings['clickable_lists']) {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnIgnore)');
			}
			else {
				$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			}
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Padlock', 0.01));
			foreach ($newlist as $player) {
				$plarr = array();
				$plarr['login'] = $player[0];
				$admin->playerlist[] = $plarr;

				// format nickname & login
				$ply = '{#black}' . str_ireplace('$w', '', $player[1])
				       . '$z / {#login}' . $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, $pid+4400);  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.', $ply);
				$pid++;
				if (++$lines > 14) {
					$admin->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					if ($aseco->settings['clickable_lists']) {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login$g (click to UnIgnore)');
					}
					else {
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No muted/ignored players found!'), $login);
			}
		}
		else if ($command['params'][0] == 'cleanmutes' ||
			/**
			 * Cleans the global mute/ignore list.
			 */

			$command['params'][0] == 'cleanignores') {

			// clean internal and server list
			$aseco->server->mutelist = array();
			$aseco->client->query('CleanIgnoreList');

			// log console message
			$aseco->console('{1} [{2}] cleaned global mute/ignore list!', $logtitle, $login);

			// show chat message
			$message = '{#server}» {#admin}Cleaned global mute/ignore list!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'addadmin' && $command['params'][1] != '') {
			/**
			 * Adds a new admin.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// check if player not already admin
				if (!$aseco->isAdminL($target->login)) {
					// add the new admin
					$aseco->admin_list['TMLOGIN'][] = $target->login;
					$aseco->admin_list['IPADDRESS'][] = ($aseco->settings['auto_admin_addip'] ? $target->ip : '');
					$aseco->writeLists();

					// log console message
					$aseco->console('{1} [{2}] adds admin [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} adds new {3}$z$s{#admin}: {#highlite}{4}$z$s{#admin} !',
						$chattitle,
						$admin->nickname,
						$aseco->titles['ADMIN'][0],
						$target->nickname
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}Login {#highlite}$i {1}{#error} is already in Admin List!', $target->login);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'removeadmin' && $command['params'][1] != '') {
			/**
			 * Removes an admin.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// check if player is indeed admin
				if ($aseco->isAdminL($target->login)) {
					$i = array_search($target->login, $aseco->admin_list['TMLOGIN']);
					$aseco->admin_list['TMLOGIN'][$i] = '';
					$aseco->admin_list['IPADDRESS'][$i] = '';
					$aseco->writeLists();

					// log console message
					$aseco->console('{1} [{2}] removes admin [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} removes {3}$z$s{#admin}: {#highlite}{4}$z$s{#admin} !',
						$chattitle,
						$admin->nickname,
						$aseco->titles['ADMIN'][0],
						$target->nickname
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}Login {#highlite}$i {1}{#error} is not in Admin List!', $target->login);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'addop' && $command['params'][1] != '') {
			/**
			 * Adds a new operator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// check if player not already operator
				if (!$aseco->isOperatorL($target->login)) {
					// add the new operator
					$aseco->operator_list['TMLOGIN'][] = $target->login;
					$aseco->operator_list['IPADDRESS'][] = ($aseco->settings['auto_admin_addip'] ? $target->ip : '');
					$aseco->writeLists();

					// log console message
					$aseco->console('{1} [{2}] adds operator [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} adds new {3}$z$s{#admin}: {#highlite}{4}$z$s{#admin} !',
						$chattitle,
						$admin->nickname,
						$aseco->titles['OPERATOR'][0],
						$target->nickname
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}Login {#highlite}$i {1}{#error} is already in Operator List!', $target->login);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'removeop' && $command['params'][1] != '') {
			/**
			 * Removes an operator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// check if player is indeed operator
				if ($aseco->isOperatorL($target->login)) {
					$i = array_search($target->login, $aseco->operator_list['TMLOGIN']);
					$aseco->operator_list['TMLOGIN'][$i] = '';
					$aseco->operator_list['IPADDRESS'][$i] = '';
					$aseco->writeLists();

					// log console message
					$aseco->console('{1} [{2}] removes operator [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripColors($target->nickname, false));

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} removes {3}$z$s{#admin}: {#highlite}{4}$z$s{#admin} !',
						$chattitle,
						$admin->nickname,
						$aseco->titles['OPERATOR'][0],
						$target->nickname
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}Login {#highlite}$i {1}{#error} is not in Operator List!', $target->login);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'listmasters') {
			/**
			 * Displays the masteradmins list.
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			$head = 'Current MasterAdmins:';
			$msg = array();
			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
			foreach ($aseco->masteradmin_list['TMLOGIN'] as $player) {
				// skip any LAN logins
				if ($player != '' && !$aseco->isLANLogin($player)) {
					$plarr = array();
					$plarr['login'] = $player;
					$admin->playerlist[] = $plarr;

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
					               '{#black}' . $aseco->server->players->getPlayerNickname($player)
					               . '$z / {#login}' . $player);
					$pid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No masteradmin(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'listadmins') {
			/**
			 * Displays the admins list.
			 */

			if (empty($aseco->admin_list['TMLOGIN'])) {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No admin(s) found!'), $login);
				return;
			}

			$admin->playerlist = array();
			$admin->msgs = array();

			$head = 'Current Admins:';
			$msg = array();
			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
			foreach ($aseco->admin_list['TMLOGIN'] as $player) {
				if ($player != '') {
					$plarr = array();
					$plarr['login'] = $player;
					$admin->playerlist[] = $plarr;

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
					               '{#black}' . $aseco->server->players->getPlayerNickname($player)
					               . '$z / {#login}' . $player);
					$pid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No admin(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'listops') {
			/**
			 * Displays the operators list.
			 */

			if (empty($aseco->operator_list['TMLOGIN'])) {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No operator(s) found!'), $login);
				return;
			}

			$admin->playerlist = array();
			$admin->msgs = array();

			$head = 'Current Operators:';
			$msg = array();
			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
			$pid = 1;
			$lines = 0;
			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
			foreach ($aseco->operator_list['TMLOGIN'] as $player) {
				if ($player != '') {
					$plarr = array();
					$plarr['login'] = $player;
					$admin->playerlist[] = $plarr;

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
					               '{#black}' . $aseco->server->players->getPlayerNickname($player)
					               . '$z / {#login}' . $player);
					$pid++;
					if (++$lines > 14) {
						$admin->msgs[] = $msg;
						$lines = 0;
						$msg = array();
						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
					}
				}
			}

			// add if last batch exists
			if (count($msg) > 1) {
				$admin->msgs[] = $msg;
			}

			// display ManiaLink message
			if (count($admin->msgs) > 1) {
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {  // == 1
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No operator(s) found!'), $login);
			}
		}
		else if ($command['params'][0] == 'adminability') {
			/**
			 * Show/change an admin ability
			 */

			// check for ability parameter
			if ($command['params'][1] != '') {
				// map to uppercase before checking list
				$ability = strtoupper($command['params'][1]);

				// check for valid ability
				if (isset($aseco->admin_abilities[$ability])) {
					if (isset($command['params'][2]) && $command['params'][2] != '') {
						// update ability
						if (strtoupper($command['params'][2]) == 'ON') {
							$aseco->admin_abilities[$ability][0] = true;
							$aseco->writeLists();

							// log console message
							$aseco->console('{1} [{2}] set new Admin ability: {3} ON', $logtitle, $login, strtolower($ability));
						}
						else if (strtoupper($command['params'][2]) == 'OFF') {
							$aseco->admin_abilities[$ability][0] = false;
							$aseco->writeLists();

							// log console message
							$aseco->console('{1} [{2}] set new Admin ability: {3} OFF', $logtitle, $login, strtolower($ability));
						}  // else ignore bogus parameter
					}
					// show current/new ability message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#admin}ability {#highlite}{2}{#admin} is: {#highlite}{3}',
						$aseco->titles['ADMIN'][0],
						strtolower($ability),
						($aseco->admin_abilities[$ability][0] ? 'ON' : 'OFF')
						);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}No ability {#highlite}$i {1}{#error} known!',
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = '{#server}» {#error}No ability specified - see {#highlite}$i /admin helpall{#error} and {#highlite}$i /admin listabilities{#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'opability') {
			/**
			 * Show/change an operator ability
			 */

			// check for ability parameter
			if ($command['params'][1] != '') {
				// map to uppercase before checking list
				$ability = strtoupper($command['params'][1]);

				// check for valid ability
				if (isset($aseco->operator_abilities[$ability])) {
					if (isset($command['params'][2]) && $command['params'][2] != '') {
						// update ability
						if (strtoupper($command['params'][2]) == 'ON') {
							$aseco->operator_abilities[$ability][0] = true;
							$aseco->writeLists();

							// log console message
							$aseco->console('{1} [{2}] set new Operator ability: {3} ON', $logtitle, $login, strtolower($ability));
						}
						else if (strtoupper($command['params'][2]) == 'OFF') {
							$aseco->operator_abilities[$ability][0] = false;
							$aseco->writeLists();

							// log console message
							$aseco->console('{1} [{2}] set new Operator ability: {3} OFF', $logtitle, $login, strtolower($ability));
						}  // else ignore bogus parameter
					}
					// show current/new ability message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#admin}ability {#highlite}{2}{#admin} is: {#highlite}{3}',
						$aseco->titles['OPERATOR'][0],
						strtolower($ability),
						($aseco->operator_abilities[$ability][0] ? 'ON' : 'OFF')
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}No ability {#highlite}$i {1}{#error} known!',
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = '{#server}» {#error}No ability specified - see {#highlite}$i /admin helpall{#error} and {#highlite}$i /admin listabilities{#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'listabilities') {
			/**
			 * Displays Admin and Operator abilities
			 */

			$master = false;
			if ($aseco->isMasterAdminL($login)) {
				if ($command['params'][1] == '') {
					$master = true;
					$abilities = $aseco->admin_abilities;
					$title = 'MasterAdmin';
				}
				else {
					if (stripos('admin', $command['params'][1]) === 0) {
						$abilities = $aseco->admin_abilities;
						$title = 'Admin';
					}
					else if (stripos('operator', $command['params'][1]) === 0) {
						$abilities = $aseco->operator_abilities;
						$title = 'Operator';
					}
					else {
						// all three above fall through to listing below
						$message = $aseco->formatText('{#server}» {#highlite}{1}{#error} is not a valid administrator tier!',
							$command['params'][1]
						);
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						return;
					}
				}
			}
			else if ($aseco->isAdminL($login)) {
				$abilities = $aseco->admin_abilities;
				$title = 'Admin';
			}
			else {  // isOperator
				$abilities = $aseco->operator_abilities;
				$title = 'Operator';
			}

			// compile current ability listing
			$header = 'Current ' . $title . ' abilities:';
			$help = array();
			$chat = false;
			foreach ($abilities as $ability => $value) {
				switch (strtolower($ability)) {
				case 'chat_pma':
					if ($value[0] || $master) {
						$help[] = array('chat_pma', '{#black}/pma$g sends a PM to player & admins');
						$chat = true;
					}
					break;
				case 'chat_bestworst':
					if ($value[0] || $master) {
						$help[] = array('chat_bestworst', '{#black}/best$g & {#black}/worst$g accept login/Player_ID');
						$chat = true;
					}
					break;
				case 'chat_statsip':
					if ($value[0] || $master) {
						$help[] = array('chat_statsip', '{#black}/stats$g includes IP address');
						$chat = true;
					}
					break;
				case 'chat_summary':
					if ($value[0] || $master) {
						$help[] = array('chat_summary', '{#black}/summary$g accepts login/Player_ID');
						$chat = true;
					}
					break;
				case 'chat_jb_multi':
					if ($value[0] || $master) {
						$help[] = array('chat_jb_multi', '{#black}/jukebox$g adds more than one map');
						$chat = true;
					}
					break;
				case 'chat_jb_recent':
					if ($value[0] || $master) {
						$help[] = array('chat_jb_recent', '{#black}/jukebox$g adds recently played map');
						$chat = true;
					}
					break;
				case 'chat_add_mref':
					if ($value[0] || $master) {
						$help[] = array('chat_add_mref', '{#black}/add mapref$g writes MX mapref file');
						$chat = true;
					}
					break;
				case 'chat_match':
					if ($value[0] || $master) {
						$help[] = array('chat_match', '{#black}/match$g allows match control');
						$chat = true;
					}
					break;
				case 'chat_tc_listen':
					if ($value[0] || $master) {
						$help[] = array('chat_tc_listen', '{#black}/tc$g will copy team chat to admins');
						$chat = true;
					}
					break;
				case 'chat_jfreu':
					if ($value[0] || $master) {
						$help[] = array('chat_jfreu', 'use all {#black}/jfreu$g commands');
						$chat = true;
					}
					break;
				case 'chat_musicadmin':
					if ($value[0] || $master) {
						$help[] = array('chat_musicadmin', 'use {#black}/music$g admin commands');
						$chat = true;
					}
					break;
				case 'noidlekick_play':
					if ($value[0] || $master) {
						$help[] = array('noidlekick_play', 'no idlekick when {#black}player$g');
						$chat = true;
					}
					break;
				case 'noidlekick_spec':
					if ($value[0] || $master) {
						$help[] = array('noidlekick_spec', 'no idlekick when {#black}spectator$g');
						$chat = true;
					}
					break;
				case 'server_planets':
					if ($value[0] || $master) {
						$help[] = array('server_planets', 'view planets amount in {#black}/server$g');
						$chat = true;
					}
					break;
				}
			}

			if ($chat) {
				$help[] = array();
			}
			$help[] = array('See {#black}/admin helpall$g for available /admin commands');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons128x128_1', 'ProfileAdvanced', 0.02), $help, array(1.0, 0.3, 0.7), 'OK');
		}
		else if ($command['params'][0] == 'writeabilities') {
			/**
			 * Saves the admins/operators/abilities list to adminops.xml (default).
			 */

			// write admins/operators file
			$filename = $aseco->settings['adminops_file'];
			if (!$aseco->writeLists()) {
				$message = '{#server}» {#error}Error writing {#highlite}$i ' . $filename . ' {#error}!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] wrote ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}written';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'readabilities') {
			/**
			 * Loads the admins/operators/abilities list from adminops.xml (default).
			 */

			// read admins/operators file
			$filename = $aseco->settings['adminops_file'];
			if (!$aseco->readLists()) {
				$message = '{#server}» {#highlite}' . $filename . ' {#error}not found, or error reading!';
			}
			else {
				// log console message
				$aseco->console('{1} [{2}] read ' . $filename . '!', $logtitle, $login);

				$message = '{#server}» {#highlite}' . $filename . ' {#admin}read';
			}
			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'wall' ||
			/**
			 * Display message in pop-up to all players
			 */

		          $command['params'][0] == 'mta') {

			// check for non-empty message
			if ($arglist[1] != '') {
				$header = '{#black}' . $chattitle . ' ' . $admin->nickname . '$z :';
				// insure window doesn't become too wide
				$message = wordwrap('{#welcome}' . $arglist[1], 40, LF . '{#welcome}');
				$message = explode(LF, $aseco->formatColors($message));
				foreach ($message as &$line)
					$line = array($line);

				// display ManiaLink message to all players
				foreach ($aseco->server->players->player_list as $target) {
					$aseco->plugins['PluginManialinks']->display_manialink($target->login, $header, array('Icons64x64_1', 'Inbox'), $message, array(0.8), 'OK');
				}

				// log console message
				$aseco->console('{1} [{2}] sent wall message: {3}', $logtitle, $login, $arglist[1]);
			}
			else {
				$message = '{#server}» {#error}No message!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'delrec' && $command['params'][1] != '') {
			/**
			 * Delete records/rs_times database entries for specific record & sync.
			 */

			if ( isset($aseco->plugins['PluginLocalRecords']) ) {

				// verify parameter
				$param = $command['params'][1];
				if (is_numeric($param) && $param > 0 && $param <= $aseco->plugins['PluginLocalRecords']->records->count()) {
					$param = ltrim($param, '0');
					$param--;

					// Get record info
					$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($param);
					$pid = $aseco->server->players->getPlayerId($record->player->login);

					// Remove times before record
					$aseco->plugins['PluginRasp']->deleteTime($aseco->server->maps->current->id, $pid);

					$aseco->plugins['PluginLocalRecords']->removeRecord($aseco, $aseco->server->maps->current->id, $pid, $param);
					$param++;

					// log console message
					$aseco->console('{1} [{2}] removed record {3} by {4} !', $logtitle, $login, $param, $record->player->login);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}removes record {#highlite}{3}{#admin} by {#highlite}{4}',
						$chattitle,
						$admin->nickname,
						$param,
						$aseco->stripColors($record->player->nickname)
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					$message = '{#server}» {#error}No such record {#highlite}$i ' . $param . ' {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Local records unavailable - include plugin.local_records.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'prunerecs' && $command['params'][1] != '') {
			/**
			 * Prune records/rs_times database entries for specific map.
			 */

			// verify parameter
			$param = $command['params'][1];
			if (is_numeric($param) && $param >= 0) {
				if (empty($admin->maplist)) {
					$message = $aseco->plugins['PluginRasp']->messages['LIST_HELP'][0];
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					return;
				}
				// find map by given #
				$jid = ltrim($param, '0');
				$jid--;
				if (array_key_exists($jid, $admin->maplist)) {
					$uid = $admin->maplist[$jid]['uid'];
					$name = $aseco->stripColors($admin->maplist[$jid]['name']);
					$map = $aseco->server->maps->getMapByUid($uid);

					if ($map->id > 0) {
						// delete the records and rs_times
						$query = 'DELETE FROM `records` WHERE `MapId` = '. $map->id .';';
						$aseco->mysqli->query($query);
						$query = 'DELETE FROM `rs_times` WHERE `MapId` = '. $map->id .';';
						$aseco->mysqli->query($query);

						// log console message
						$aseco->console('{1} [{2}] pruned records/times for map {3} !', $logtitle, $login, $aseco->stripColors($name, false));

						// show chat message
						$message = '{#server}» {#admin}Deleted all records & times for map: {#highlite}' . $name;
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
					else {
						$message = '{#server}» {#error}Can\'t find MapId for map: {#highlite}$i ' . $name . ' / ' . $uid;
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
				}
				else {
					$message = $aseco->plugins['PluginRasp']->messages['JUKEBOX_NOTFOUND'][0];
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = $aseco->plugins['PluginRasp']->messages['JUKEBOX_HELP'][0];
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'rpoints' && $command['params'][1] != '') {
			/**
			 * Sets custom rounds points.
			 */

			// from plugin.round_points.php
			if ( isset($aseco->plugins['PluginRoundPoints']) ) {
				$aseco->plugins['PluginRoundPoints']->admin_rpoints($aseco, $admin, $logtitle, $chattitle, $arglist[1]);
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Custom Rounds points unavailable - include plugin.round_points.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'amdl') {
			/**
			 * Shows or sets AllowMapDownload status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param == 'on' || $param == 'off') {
				$enabled = ($param == 'on');
				$aseco->client->query('AllowMapDownload', $enabled);

				// log console message
				$aseco->console('{1} [{2}] set AllowMapDownload {3} !', $logtitle, $login, ($enabled ? 'ON' : 'OFF'));

				// show chat message
				$message = '{#server}» {#admin}AllowMapDownload set to ' . ($enabled ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				$aseco->client->query('IsMapDownloadAllowed');
				$enabled = $aseco->client->getResponse();

				// show chat message
				$message = '{#server}» {#admin}AllowMapDownload is currently ' . ($enabled ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'autotime') {
			/**
			 * Shows or sets Auto TimeLimit status.
			 */

			// check for autotime plugin
			if ( isset($aseco->plugins['PluginAutotime']) ) {
				$param = strtolower($command['params'][1]);
				if ($param == 'on' || $param == 'off') {

					// from plugin.autotime.php
					$aseco->plugins['PluginAutotime']->active = ($param == 'on');

					// log console message
					$aseco->console('{1} [{2}] set Auto TimeLimit {3} !', $logtitle, $login, ($aseco->plugins['PluginAutotime']->active ? 'ON' : 'OFF'));

					// show chat message
					$message = '{#server}» {#admin}Auto TimeLimit set to ' . ($aseco->plugins['PluginAutotime']->active ? 'Enabled' : 'Disabled');
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					// show chat message
					$message = '{#server}» {#admin}Auto TimeLimit is currently ' . ($aseco->plugins['PluginAutotime']->active ? 'Enabled' : 'Disabled');
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Auto TimeLimit unavailable - include plugin.autotime.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'disablerespawn') {
			/**
			 * Shows or sets DisableRespawn status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param == 'on' || $param == 'off') {
				$enabled = ($param == 'on');
				$aseco->client->query('SetDisableRespawn', $enabled);

				// log console message
				$aseco->console('{1} [{2}] set DisableRespawn {3} !', $logtitle, $login, ($enabled ? 'ON' : 'OFF'));

				// show chat message
				$message = '{#server}» {#admin}DisableRespawn set to ' . ($enabled ? 'Enabled' : 'Disabled') . ' on the next map';
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				$aseco->client->query('GetDisableRespawn');
				$enabled = $aseco->client->getResponse();

				// show chat message
				$message = '{#server}» {#admin}DisableRespawn is currently ' . ($enabled['CurrentValue'] ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'forceshowopp') {
			/**
			 * Shows or sets ForceShowAllOpponents status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param == 'all' || $param == 'off') {
				$enabled = ($param == 'all' ? 1 : 0);
				$aseco->client->query('SetForceShowAllOpponents', $enabled);

				// log console message
				$aseco->console('{1} [{2}] set ForceShowAllOpponents {3} !', $logtitle, $login, ($enabled ? 'ALL' : 'OFF'));

				// show chat message
				$message = '{#server}» {#admin}ForceShowAllOpponents set to {#highlite}' . ($enabled ? 'Enabled' : 'Disabled') . '{#admin} on the next map';
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else if (is_numeric($param) && $param > 1) {
				$enabled = intval($param);
				$aseco->client->query('SetForceShowAllOpponents', $enabled);

				// log console message
				$aseco->console('{1} [{2}] set ForceShowAllOpponents to {3} !', $logtitle, $login, $enabled);

				// show chat message
				$message = '{#server}» {#admin}ForceShowAllOpponents set to {#highlite}' . $enabled . '{#admin} on the next map';
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				$aseco->client->query('GetForceShowAllOpponents');
				$enabled = $aseco->client->getResponse();
				$enabled = $enabled['CurrentValue'];

				// show chat message
				$message = '{#server}» {#admin}ForceShowAllOpponents is set to: {#highlite}' . ($enabled != 0 ? ($enabled > 1 ? $enabled : 'All') : 'Off');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'scorepanel') {
			/**
			 * Shows or sets Automatic ScorePanel status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param == 'on' || $param == 'off') {
				$aseco->plugins['PluginManialinks']->auto_scorepanel = ($param == 'on');
				$aseco->plugins['PluginManialinks']->scorepanel_off($aseco, null);

				// log console message
				$aseco->console('{1} [{2}] set Automatic ScorePanel {3} !', $logtitle, $login, ($aseco->plugins['PluginManialinks']->auto_scorepanel ? 'ON' : 'OFF'));

				// show chat message
				$message = '{#server}» {#admin}Automatic ScorePanel set to ' . ($aseco->plugins['PluginManialinks']->auto_scorepanel ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Automatic ScorePanel is currently ' . ($aseco->plugins['PluginManialinks']->auto_scorepanel ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'roundsfinish') {
			/**
			 * Shows or sets Rounds Finishpanel status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param == 'on' || $param == 'off') {
				$aseco->plugins['PluginManialinks']->rounds_finishpanel = ($param == 'on');

				// log console message
				$aseco->console('{1} [{2}] set Rounds Finishpanel {3} !', $logtitle, $login, ($aseco->plugins['PluginManialinks']->rounds_finishpanel ? 'ON' : 'OFF'));

				// show chat message
				$message = '{#server}» {#admin}Rounds Finishpanel set to ' . ($aseco->plugins['PluginManialinks']->rounds_finishpanel ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Rounds Finishpanel is currently ' . ($aseco->plugins['PluginManialinks']->rounds_finishpanel ? 'Enabled' : 'Disabled');
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'forceteam' && $command['params'][1] != '') {
			/**
			 * Forces a player into Blue or Red team.
			 */

			// check for Team mode
			if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
				// get player information
				if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
					// get player's team
					$aseco->client->query('GetPlayerInfo', $target->login);
					$info = $aseco->client->getResponse();
					// check for new team
					if (isset($command['params'][2]) && $command['params'][2] != '') {
						$team = strtolower($command['params'][2]);

						if (strpos('blue', $team) === 0) {
							if ($info['TeamId'] != 0) {
								// set player to Blue team
								$aseco->client->query('ForcePlayerTeam', $target->login, 0);

								// log console message
								$aseco->console('{1} [{2}] forces {3} into Blue team!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

								// show chat message
								$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} forces {#highlite}{3}$z$s{#admin} into $00fBlue{#admin} team!',
								                      $chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
								$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
							}
							else {
								$message = '{#server}» {#admin}Player {#highlite}' .
								           $aseco->stripColors($target->nickname) .
								           '{#admin} is already in $00fBlue{#admin} team';
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}

						}
						else if (strpos('red', $team) === 0) {
							if ($info['TeamId'] != 1) {
								// set player to Red team
								$aseco->client->query('ForcePlayerTeam', $target->login, 1);

								// log console message
								$aseco->console('{1} [{2}] forces {3} into Red team!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

								// show chat message
								$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} forces {#highlite}{3}$z$s{#admin} into $f00Red{#admin} team!',
								                      $chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
								$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
							}
							else {
								$message = '{#server}» {#admin}Player {#highlite}' .
								           $aseco->stripColors($target->nickname) .
								           '{#admin} is already in $f00Red{#admin} team';
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}

						}
						else {
							$message = '{#server}» {#highlite}' . $team . '$z$s{#error} is not a valid team!';
							$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
						}
					}
					else {
						// show current team
						$message = '{#server}» {#admin}Player {#highlite}' .
						           $aseco->stripColors($target->nickname) . '{#admin} is in ' .
						           ($info['TeamId'] == 0 ? '$00fBlue' : '$f00Red') .
						           '{#admin} team';
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
				}
			}
			else {
				$message = '{#server}» {#error}Command only available in {#highlite}$i Team {#error}mode!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'forcespec' && $command['params'][1] != '') {
			/**
			 * Forces player into free camera spectator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				if (!$target->isspectator) {
					// force player into free spectator
					$rtn = $aseco->client->query('ForceSpectator', $target->login, 1);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] ForceSpectator - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					}
					else {
						// allow spectator to switch back to player
						$rtn = $aseco->client->query('ForceSpectator', $target->login, 0);
						// force free camera mode on spectator
						$aseco->client->addCall('ForceSpectatorTarget', array($target->login, '', 2));
						// free up player slot
						$aseco->client->addCall('SpectatorReleasePlayerSlot', array($target->login));
						// log console message
						$aseco->console('{1} [{2}] forces player {3} into spectator!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

						// show chat message
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} forces player {#highlite}{3}$z$s{#admin} into spectator!',
							$chattitle,
							$admin->nickname,
							str_ireplace('$w', '', $target->nickname)
						);
						$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
					}
				}
				else {
					$message = $aseco->formatText('{#server}» {#highlite}{1} {#error}is already a spectator!',
						$aseco->stripColors($target->nickname)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'specfree' && $command['params'][1] != '') {
			/**
			 * Forces a spectator into free camera mode.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				if ($target->isspectator) {
					// force free camera mode on spectator
					$rtn = $aseco->client->query('ForceSpectatorTarget', $target->login, '', 2);
					if (!$rtn) {
						trigger_error('[' . $aseco->client->getErrorCode() . '] ForceSpectatorTarget - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					}
					else {
						// log console message
						$aseco->console('{1} [{2}] forces spectator free mode on {3}!', $logtitle, $login, $aseco->stripColors($target->nickname, false));

						// show chat message
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} forces spectator free mode on {#highlite}{3}$z$s{#admin} !',
							$chattitle,
							$admin->nickname,
							str_ireplace('$w', '', $target->nickname)
						);
						$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
					}
				}
				else {
					$message = $aseco->formatText('{#server}» {#highlite}{1} {#error}is not a spectator!',
						$aseco->stripColors($target->nickname)
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'panel') {
			/**
			 * Selects default window style.
			 */

			if ( isset($aseco->plugins['PluginPanels']) ) {
				// from plugin.panels.php
				$aseco->plugins['PluginPanels']->admin_panel($aseco, $login, 'panel', $command['params'][1]);
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Admin panel unavailable - include plugin.panels.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'style' && $command['params'][1] != '') {
			/**
			 * Selects default window style.
			 */

			$style_file = 'styles/' . $command['params'][1] . '.xml';
			// load default style
			if (($style = $aseco->parser->xmlToArray($style_file, true, true)) && isset($style['STYLES'])) {
				$aseco->style = $style['STYLES'];

				// log console message
				$aseco->console('{1} [{2}] selects default window style [{3}]', $logtitle, $login, $command['params'][1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default window style {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$command['params'][1]
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// Could not read/parse XML file
				$message = '{#server}» {#error}No valid style file, use {#highlite}$i /style list {#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'admpanel' && $command['params'][1] != '') {
			/**
			 * Selects default admin panel.
			 */

			if (strtolower($command['params'][1]) == 'off') {
				$aseco->panels['admin'] = '';
				$aseco->settings['admin_panel'] = 'Off';

				// log console message
				$aseco->console('{1} [{2}] reset default admin panel', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} reset default admin panel',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				// added file prefix
				$panel = $command['params'][1];
				if (strtolower(substr($command['params'][1], 0, 5)) != 'admin') {
					$panel = 'Admin' . $panel;
				}
				$panel_file = 'panels/' . $panel . '.xml';

				// load default panel
				if ($panel = @file_get_contents($panel_file)) {
					$aseco->panels['admin'] = $panel;

					// log console message
					$aseco->console('{1} [{2}] selects default admin panel [{3}]', $logtitle, $login, $command['params'][1]);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default admin panel {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					// Could not read XML file
					$message = '{#server}» {#error}No valid admin panel file, use {#highlite}$i /admin panel list {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'donpanel' && $command['params'][1] != '') {
			/**
			 * Selects default donate panel.
			 */

			if (strtolower($command['params'][1]) == 'off') {
				$aseco->panels['donate'] = '';
				$aseco->settings['donate_panel'] = 'Off';

				// log console message
				$aseco->console('{1} [{2}] reset default donate panel', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} reset default donate panel',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// added file prefix
				$panel = $command['params'][1];
				if (strtolower(substr($command['params'][1], 0, 6)) != 'donate') {
					$panel = 'Donate' . $panel;
				}
				$panel_file = 'panels/' . $panel . '.xml';

				// load default panel
				if ($panel = @file_get_contents($panel_file)) {
					$aseco->panels['donate'] = $panel;

					// log console message
					$aseco->console('{1} [{2}] selects default donate panel [{3}]', $logtitle, $login, $command['params'][1]);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default donate panel {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					// Could not read XML file
					$message = '{#server}» {#error}No valid donate panel file, use {#highlite}$i /donpanel list {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'recpanel' && $command['params'][1] != '') {
			/**
			 * Selects default records panel.
			 */

			if (strtolower($command['params'][1]) == 'off') {
				$aseco->panels['records'] = '';
				$aseco->settings['records_panel'] = 'Off';

				// log console message
				$aseco->console('{1} [{2}] reset default records panel', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} reset default records panel',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// added file prefix
				$panel = $command['params'][1];
				if (strtolower(substr($command['params'][1], 0, 7)) != 'records') {
					$panel = 'Records' . $panel;
				}
				$panel_file = 'panels/' . $panel . '.xml';

				// load default panel
				if ($panel = @file_get_contents($panel_file)) {
					$aseco->panels['records'] = $panel;

					// log console message
					$aseco->console('{1} [{2}] selects default records panel [{3}]', $logtitle, $login, $command['params'][1]);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default records panel {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					// Could not read XML file
					$message = '{#server}» {#error}No valid records panel file, use {#highlite}$i /recpanel list {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'votepanel' && $command['params'][1] != '') {
			/**
			 * Selects default vote panel.
			 */

			if (strtolower($command['params'][1]) == 'off') {
				$aseco->panels['vote'] = '';
				$aseco->settings['vote_panel'] = 'Off';

				// log console message
				$aseco->console('{1} [{2}] reset default vote panel', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} reset default vote panel',
					$chattitle,
					$admin->nickname
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// added file prefix
				$panel = $command['params'][1];
				if (strtolower(substr($command['params'][1], 0, 4)) != 'vote') {
					$panel = 'Vote' . $panel;
				}
				$panel_file = 'panels/' . $panel . '.xml';

				// load default panel
				if ($panel = @file_get_contents($panel_file)) {
					$aseco->panels['vote'] = $panel;

					// log console message
					$aseco->console('{1} [{2}] selects default vote panel [{3}]', $logtitle, $login, $command['params'][1]);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default vote panel {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$command['params'][1]
					);
					$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				}
				else {
					// Could not read XML file
					$message = '{#server}» {#error}No valid vote panel file, use {#highlite}$i /votepanel list {#error}!';
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
		}
		else if ($command['params'][0] == 'panelbg' && $command['params'][1] != '') {
			/**
			 * Selects default panel background.
			 */

			// added file prefix
			$panel = $command['params'][1];
			if (strtolower(substr($command['params'][1], 0, 7)) != 'panelbg') {
				$panel = 'PanelBG' . $panel;
			}
			$panelbg_file = 'panels/' . $panel . '.xml';

			// load default background
			if (($panelbg = $aseco->parser->xmlToArray($panelbg_file, true, true)) && isset($panelbg['PANEL']['BACKGROUND'][0])) {
				$aseco->panelbg = $panelbg['PANEL']['BACKGROUND'][0];

				// log console message
				$aseco->console('{1} [{2}] selects default panel background [{3}]', $logtitle, $login, $command['params'][1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default panel background {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$command['params'][1]
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
			}
			else {
				// Could not read/parse XML file
				$message = '{#server}» {#error}No valid background file, use {#highlite}$i /panelbg list {#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'planets') {
			/**
			 * Shows server's planets amount.
			 */

			// get server planets
			$aseco->client->query('GetServerPlanets');
			$planets = $aseco->client->getResponse();

			// show chat message
			$message = $aseco->formatText($aseco->getChatMessage('PLANETS'),
				$aseco->server->name,
				$planets
			);
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'pay') {
			/**
			 * Pays server planets to login.
			 */

			if ( isset($aseco->plugins['PluginDonate']) ) {
				if (!isset($command['params'][2])) {
					$command['params'][2] = '';
				}

				// from plugin.donate.php
				$aseco->plugins['PluginDonate']->admin_payment(
					$aseco,
					$login,
					$command['params'][1],
					$command['params'][2]
				);
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Server payment unavailable - include plugin.donate.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'relays') {
			/**
			 * Displays relays list or shows relay master.
			 */

			if ($aseco->server->isrelay) {
				// show chat message
				$message = $aseco->formatText($aseco->getChatMessage('RELAYMASTER'),
					$aseco->server->relaymaster['Login'],
					$aseco->server->relaymaster['NickName']
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
			else {
				if (empty($aseco->server->relay_list)) {
					// show chat message
					$message = $aseco->formatText($aseco->getChatMessage('NO_RELAYS'));
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
				else {
					$header = 'Relay servers:';
					$relays = array();
					$relays[] = array('{#login}Login', '{#nick}Nick');
					foreach ($aseco->server->relay_list as $relay) {
						$relays[] = array($relay['Login'], $relay['NickName']);
					}

					// display ManiaLink message
					$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('BgRaceScore2', 'Spectator'), $relays, array(1.0, 0.35, 0.65), 'OK');
				}
			}
		}
		else if ($command['params'][0] == 'server') {
			/**
			 * Shows server's detailed settings.
			 */

			// get all server settings in one go
			$version = $aseco->client->addCall('GetVersion', array());
			$info = $aseco->client->addCall('GetSystemInfo', array());
			$planets = $aseco->client->addCall('GetServerPlanets', array());
			$ladderlim = $aseco->client->addCall('GetLadderServerLimits', array());
			$options = $aseco->client->addCall('GetServerOptions', array(1));
			$gameinfo = $aseco->client->addCall('GetCurrentGameInfo', array(1));
			$network = $aseco->client->addCall('GetNetworkStats', array());
			$callvotes = $aseco->client->addCall('GetCallVoteRatios', array());
			if (!$aseco->client->multiquery()) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] GetServer (multi) - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				return;
			}
			else {
				$response = $aseco->client->getResponse();
				$version = $response[$version][0];
				$info = $response[$info][0];
				$planets = $response[$planets][0];
				$ladderlim = $response[$ladderlim][0];
				$options = $response[$options][0];
				$gameinfo = $response[$gameinfo][0];
				$network = $response[$network][0];
				$callvotes = $response[$callvotes][0];
			}

			// compile settings overview
			$head = 'System info for: ' . $options['Name'];
			$admin->msgs = array();
			$admin->msgs[0] = array(1, $head, array(1.1, 0.6, 0.5), array('Icons128x32_1', 'Settings', 0.01));
			$stats = array();

			$stats[] = array('{#black}GetVersion:', '');
			foreach ($version as $key => $val) {
				$stats[] = array($key, '{#black}' . $val);
			}

			$stats[] = array();
			$stats[] = array('{#black}GetSystemInfo:', '');
			foreach ($info as $key => $val) {
				$stats[] = array($key, '{#black}' . $val);
			}

			$stats[] = array();
			$stats[] = array('Planets', '{#black}' . $planets);
			if ($aseco->server->isrelay) {
				$stats[] = array('Relays', '{#black}' . $aseco->server->relaymaster['Login']);
			}
			else {
				$stats[] = array('Master to', '{#black}' . count($aseco->server->relay_list) .
				                 ' $grelay' . (count($aseco->server->relay_list) == 1 ? '' : 's'));
			}
			$stats[] = array();

			$admin->msgs[] = $stats;
			$stats = array();

			$stats[] = array('{#black}GetServerOptions:', '');
			foreach ($options as $key => $val) {
				// show only Current values, not Next ones
				if ($key != 'Name' && $key != 'Comment' && substr($key, 0, 4) != 'Next') {
					if (is_bool($val)) {
						$stats[] = array($key, '{#black}' . $aseco->bool2string($val));
					}
					else {
						$stats[] = array($key, '{#black}' . $val);
					}
				}
			}

			$admin->msgs[] = $stats;
			$stats = array();

			$lines = 0;
			$stats[] = array('{#black}GetCurrentGameInfo:', '');
			foreach ($gameinfo as $key => $val) {
				if (is_bool($val)) {
					$stats[] = array($key, '{#black}' . $aseco->bool2string($val));
				}
				else {
					if ($key == 'GameMode') {
						$stats[] = array($key, '{#black}' . $val . '$g  (' . $aseco->server->gameinfo->getGamemodeName() . ')');
					}
					else {
						$stats[] = array($key, '{#black}' . $val);
					}
				}

				if (++$lines > 18) {
					$admin->msgs[] = $stats;
					$stats = array();
					$stats[] = array('{#black}GetCurrentGameInfo:', '');
					$lines = 0;
				}
			}

			$stats[] = array();
			$stats[] = array('{#black}GetNetworkStats:', '');
			foreach ($network as $key => $val) {
				if ($key != 'PlayerNetInfos')
					$stats[] = array($key, '{#black}' . $val);
			}

			$stats[] = array();
			$stats[] = array('{#black}GetLadderServerLimits:', '');
			foreach ($ladderlim as $key => $val) {
				$stats[] = array($key, '{#black}' . $val);
			}

			$admin->msgs[] = $stats;
			$stats = array();

			$stats[] = array('{#black}GetCallVoteRatios:', '');
			$stats[] = array('Command', 'Ratio');
			foreach ($callvotes as $entry) {
				$stats[] = array('{#black}' . $entry['Command'], '{#black}' . round($entry['Ratio'], 2));
			}

			$admin->msgs[] = $stats;
			$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);

		}
		else if ($command['params'][0] == 'pm') {
			/**
			 * Send private message to all available admins.
			 */
			global $pmbuf, $pmlen;

			// check for non-empty message
			if ($arglist[1] != '') {
				// drop oldest pm line if buffer full
				if (count($pmbuf) >= $pmlen) {
					array_shift($pmbuf);
				}
				// append timestamp, admin nickname (but strip wide font) and pm line to history
				$nick = str_ireplace('$w', '', $admin->nickname);
				$pmbuf[] = array(date('H:i:s'), $nick, $arglist[1]);

				// find and pm other masteradmins/admins/operators
				$nicks = '';
				$msg = '{#error}-pm-$g[' . $nick . '$z$s$i->{#logina}Admins$g]$i {#interact}' . $arglist[1];
				$msg = $aseco->formatColors($msg);
				foreach ($aseco->server->players->player_list as $pl) {
					// check for admin ability
					if ($pl->login != $login && $aseco->allowAbility($pl, 'pm')) {
						$nicks .= str_ireplace('$w', '', $pl->nickname) . '$z$s$i,';
						$aseco->client->addCall('ChatSendServerMessageToLogin', array($msg, $pl->login));

						// check if player muting is enabled
						if ((class_exists('PluginMuting')) && (PluginMuting::$muting_available) ) {
							// drop oldest message if receiver's mute buffer full
							if (count($pl->mutebuf) >= 28) {  // chat window length
								array_shift($pl->mutebuf);
							}
							// append pm line to receiver's mute buffer
							$pl->mutebuf[] = $msg;
						}
					}
				}

				// CC message to self
				if ($nicks) {
					$nicks = substr($nicks, 0, strlen($nicks)-1);  // strip trailing ','
					$msg = '{#error}-pm-$g[' . $nick . '$z$s$i->' . $nicks . ']$i {#interact}' . $arglist[1];
				}
				else {
					$msg = '{#server}» {#error}No other admins currectly available!';
				}
				$msg = $aseco->formatColors($msg);
				$aseco->client->addCall('ChatSendServerMessageToLogin', array($msg, $login));
				if (!$aseco->client->multiquery()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] ChatSend PM (multi) - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
				}

				// check if player muting is enabled
				if (isset($aseco->plugins['PluginMuting']) && ($aseco->plugins['PluginMuting']->muting_available) ) {
					// drop oldest message if sender's mute buffer full
					if (count($admin->mutebuf) >= 28) {  // chat window length
						array_shift($admin->mutebuf);
					}
					// append pm line to sender's mute buffer
					$admin->mutebuf[] = $msg;
				}
			}
			else {
				$msg = '{#server}» {#error}No message!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($msg), $login);
			}
		}
		else if ($command['params'][0] == 'pmlog') {
			/**
			 * Displays log of recent private admin messages.
			 */
			global $pmbuf, $lnlen;

			if (!empty($pmbuf)) {
				$head = 'Recent Admin PM history:';
				$msg = array();
				$lines = 0;
				$admin->msgs = array();
				$admin->msgs[0] = array(1, $head, array(1.2), array('Icons64x64_1', 'Outbox'));
				foreach ($pmbuf as $item) {
					// break up long lines into chunks with continuation strings
					$multi = explode(LF, wordwrap($aseco->stripColors($item[2]), $lnlen+30, LF . '...'));
					foreach ($multi as $line) {
						$line = substr($line, 0, $lnlen+33);  // chop off excessively long words
						$msg[] = array('$z' . ($aseco->settings['chatpmlog_times'] ? '<{#server}' . $item[0] . '$z> ' : '') .
						               '[{#black}' . $item[1] . '$z] ' . $line);
						if (++$lines > 14) {
							$admin->msgs[] = $msg;
							$lines = 0;
							$msg = '';
						}
					}
				}

				// add if last batch exists
				if (!empty($msg)) {
					$admin->msgs[] = $msg;
				}

				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
			}
			else {
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors('{#server}» {#error}No PM history found!'), $login);
			}
		}
		else if ($command['params'][0] == 'call') {
			/**
			 * Executes direct server call
			 */
			global $method_results;

			// extra admin tier check
			if (!$aseco->isMasterAdmin($admin)) {
				$aseco->client->query('ChatSendToLogin', $aseco->formatColors('{#error}You don\'t have the required admin rights to do that!'), $login);
				return;
			}

			// check parameter(s)
			if ($command['params'][1] != '') {
				if ($command['params'][1] == 'help') {
					if (isset($command['params'][2]) && $command['params'][2] != '') {
						// generate help message for method
						$method = $command['params'][2];
						$sign = $aseco->client->addCall('system.methodSignature', array($method));
						$help = $aseco->client->addCall('system.methodHelp', array($method));
						if (!$aseco->client->multiquery()) {
							trigger_error('[' . $aseco->client->getErrorCode() . '] system.method - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
						}
						else {
							$response = $aseco->client->getResponse();
							if (isset($response[0]['faultCode'])) {
								$message = '{#server}» {#error}No such method {#highlite}$i ' . $method . ' {#error}!';
								$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
							}
							else {
								$sign = $response[$sign][0][0];
								$help = $response[$help][0];

								// format signature & help
								$params = '';
								for ($i = 1; $i < count($sign); $i++) {
									$params .= $sign[$i] . ', ';
								}
								$params = substr($params, 0, strlen($params)-2);  // strip trailing ", "
								$sign = $sign[0] . ' {#black}' . $method . '$g (' . $params . ')';
								$sign = explode(LF, wordwrap($sign, 58, LF));
								$help = str_replace(array('<i>', '</i>'),
								                    array('$i', '$i'), $help);
								$help = explode(LF, wordwrap($help, 58, LF));

								// compile & display help message
								$header = 'Server Method help for:';
								$info = array();
								foreach ($sign as $line) {
									$info[] = array($line);
								}

								$info[] = array();
								foreach ($help as $line) {
									$info[] = array($line);
								}

								// display ManiaLink message
								$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons128x128_1', 'Advanced', 0.02), $info, array(1.05), 'OK');
							}
						}
					}
					else {
						// compile & display help message
						$header = '{#black}/admin call$g executes server method:';
						$help = array();
						$help[] = array('...', '{#black}help',
						                'Displays this help information');
						$help[] = array('...', '{#black}help Method',
						                'Displays help for method');
						$help[] = array('...', '{#black}list',
						                'Lists all available methods');
						$help[] = array('...', '{#black}Method {params}',
						                'Executes method & displays result');

						// display ManiaLink message
						$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.0, 0.05, 0.35, 0.6), 'OK');
					}

				}
				else if ($command['params'][1] == 'list') {
					// get list of methods
					$aseco->client->query('system.listMethods');
					$methods = $aseco->client->getResponse();
					$admin->msgs = array();

					$head = 'Available Methods on this Server:';
					$msg = array();
					$msg[] = array('Id', 'Method');
					$mid = 1;
					$lines = 0;
					$admin->msgs[0] = array(1, $head, array(0.9, 0.15, 0.75), array('Icons128x128_1', 'Advanced', 0.02));
					foreach ($methods as $method) {
						$msg[] = array(str_pad($mid, 2, '0', STR_PAD_LEFT) . '.',
						               '{#black}' . $method);
						$mid++;
						if (++$lines > 14) {
							$admin->msgs[] = $msg;
							$lines = 0;
							$msg = array();
							$msg[] = array('Id', 'Method');
						}
					}

					// add if last batch exists
					if (count($msg) > 1) {
						$admin->msgs[] = $msg;
					}

					// display ManiaLink message
					$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);

				}
				else {
					// server method
					$method = $command['params'][1];

					// collect parameters with correct types
					$args = array();
					$multistr = '';
					$in_multi = false;
					for ($i = 2; $i < count($command['params']); $i++) {
						if (!$in_multi && strtolower($command['params'][$i]) == 'true') {
							$args[] = true;
						}
						else if (!$in_multi && strtolower($command['params'][$i]) == 'false') {
							$args[] = false;
						}
						else if (!$in_multi && is_numeric($command['params'][$i])) {
							$args[] = intval($command['params'][$i]);
						}
						else {
							// check for multi-word strings
							if ($in_multi) {
								if (substr($command['params'][$i], -1) == '"') {
									$args[] = $multistr . ' ' . substr($command['params'][$i], 0, -1);
									$multistr = '';
									$in_multi = false;
								}
								else {
									$multistr .= ' ' . $command['params'][$i];
								}
							}
							else {
								if (substr($command['params'][$i], 0, 1) == '"') {
									$multistr = substr($command['params'][$i], 1);
									$in_multi = true;
								}
								else {
									$args[] = $command['params'][$i];
								}
							}
						}
					}

					// execute method
					switch (count($args)) {
						case 0: $res = $aseco->client->query($method);
						        break;
						case 1: $res = $aseco->client->query($method, $args[0]);
						        break;
						case 2: $res = $aseco->client->query($method, $args[0], $args[1]);
						        break;
						case 3: $res = $aseco->client->query($method, $args[0], $args[1], $args[2]);
						        break;
						case 4: $res = $aseco->client->query($method, $args[0], $args[1], $args[2], $args[3]);
						        break;
						case 5: $res = $aseco->client->query($method, $args[0], $args[1], $args[2], $args[3], $args[4]);
						        break;
					}

					// process result
					if ($res) {
						$res = $aseco->client->getResponse();
						$admin->msgs = array();
						$method_results = array();
						$this->collect_results($method, $res, '');

						// compile & display result message
						$head = 'Method results for:';
						$msg = array();
						$mid = 1;
						$lines = 0;
						$admin->msgs[0] = array(1, $head, array(1.1), array('Icons128x128_1', 'Advanced', 0.02));
						foreach ($method_results as $line) {
							$msg[] = array($line);
							$mid++;
							if (++$lines > 20) {
								$admin->msgs[] = $msg;
								$lines = 0;
								$msg = array();
							}
						}

						// add if last batch exists
						if (!empty($msg)) {
							$admin->msgs[] = $msg;
						}

						// display ManiaLink message
						$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
					}
					else {
						$message = '{#server}» {#error}Method error for {#highlite}$i ' . $method . '{#error}: [' . $aseco->client->getErrorCode() . '] ' . $aseco->client->getErrorMessage();
						$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
					}
				}
			}
			else {
				$message = '{#server}» {#error}No call specified - see {#highlite}$i /admin call help{#error} and {#highlite}$i /admin call list{#error}!';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else if ($command['params'][0] == 'unlock' && $command['params'][1] != '') {
			/**
			 * Unlocks admin commands & features.
			 */

			// check unlock password
			if ($aseco->settings['lock_password'] == $command['params'][1]) {
				$admin->unlocked = true;
				$message = '{#server}» {#admin}Password accepted: admin commands unlocked!';
			}
			else {
				$message = '{#server}» {#error}Invalid password!';
			}
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'debug') {
			/**
			 * Toggle debug on/off.
			 */

			$aseco->debug = !$aseco->debug;
			if ($aseco->debug) {
				$message = '{#server}» Debug is now enabled';
			}
			else {
				$message = '{#server}» Debug is now disabled';
			}
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else if ($command['params'][0] == 'shutdown') {
			/**
			 * Shuts down UASECO.
			 */

			$aseco->console('Shutdown UASECO!');

			// Throw 'shutting down' event
			$aseco->releaseEvent('onShutdown', null);

			// Clear all ManiaLinks
			$aseco->client->query('SendHideManialinkPage');

			exit();
		}
		else if ($command['params'][0] == 'shutdownall') {
			/**
			 * Shuts down Server & UASECO.
			 */

			$message = '{#server}» {#error}$wShutting down server now!';
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

			$rtn = $aseco->client->query('StopServer');
			if (!$rtn) {
				trigger_error('[' . $aseco->client->getErrorCode() . '] StopServer - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
			}
			else {
				// test for /noautoquit
				sleep(2);
				$autoquit = new IXR_ClientMulticall_Gbx();
				if ($autoquit->InitWithIp($aseco->server->xmlrpc['ip'], $aseco->server->xmlrpc['port'])) {
					$aseco->client->query('QuitGame');
				}

				// Throw 'shutting down' event
				$aseco->releaseEvent('onShutdown', null);

				// Clear all ManiaLinks
				$aseco->client->query('SendHideManialinkPage');

				$aseco->console('Shutdown dedicated server & UASECO!');
				exit();
			}
		}
		else if ($command['params'][0] == 'uptodate') {
			/**
			 * Checks current version of UASECO.
			 */

			if ( isset($aseco->plugins['PluginUptodate']) ) {
				// from plugin.uptodate.php
				$aseco->releaseChatCommand('/uptodate', $login);
			}
			else {
				// show chat message
				$message = '{#server}» {#admin}Version checking unavailable - include plugin.uptodate.php in [config/plugins.xml]';
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}

		}
		else {
			$message = '{#server}» {#error}Unknown admin command or missing parameter(s): {#highlite}$i ' . $arglist[0] . ' ' . $arglist[1];
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getIgnorelist ($aseco) {

		$aseco->client->resetError();
		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			$aseco->client->query('GetIgnoreList', $size, $i);
			$players = $aseco->client->getResponse();
			if (!empty($players)) {
				if ($aseco->client->isError()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] GetIgnoreList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					$done = true;
					break;
				}
				foreach ($players as $prow) {
					// fetch nickname for this login
					$lgn = $prow['Login'];
					$nick = $aseco->server->players->getPlayerNickname($lgn);
					$newlist[$lgn] = array($lgn, $nick);
				}
				if (count($players) < $size) {
					// got less than 300 players, might as well leave
					$done = true;
				}
				else {
					$i += $size;
				}
			}
			else {
				$done = true;
			}
		}
		return $newlist;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getBanlist ($aseco) {

		$aseco->client->resetError();
		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			$aseco->client->query('GetBanList', $size, $i);
			$players = $aseco->client->getResponse();
			if (!empty($players)) {
				if ($aseco->client->isError()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] GetBanList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					$done = true;
					break;
				}
				foreach ($players as $prow) {
					// fetch nickname for this login
					$lgn = $prow['Login'];
					$nick = $aseco->server->players->getPlayerNickname($lgn);
					$newlist[$lgn] = array($lgn, $nick,
					                 preg_replace('/:\d+/', '', $prow['IPAddress']));  // strip port
				}
				if (count($players) < $size) {
					// got less than 300 players, might as well leave
					$done = true;
				}
				else {
					$i += $size;
				}
			}
			else {
				$done = true;
			}
		}
		return $newlist;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getBlacklist ($aseco) {

		$aseco->client->resetError();
		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			$aseco->client->query('GetBlackList', $size, $i);
			$players = $aseco->client->getResponse();
			if (!empty($players)) {
				if ($aseco->client->isError()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] GetBlackList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					$done = true;
					break;
				}
				foreach ($players as $prow) {
					// fetch nickname for this login
					$lgn = $prow['Login'];
					$nick = $aseco->server->players->getPlayerNickname($lgn);
					$newlist[$lgn] = array($lgn, $nick);
				}
				if (count($players) < $size) {
					// got less than 300 players, might as well leave
					$done = true;
				}
				else {
					$i += $size;
				}
			}
			else {
				$done = true;
			}
		}
		return $newlist;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getGuestlist ($aseco) {

		$aseco->client->resetError();
		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			$aseco->client->query('GetGuestList', $size, $i);
			$players = $aseco->client->getResponse();
			if (!empty($players)) {
				if ($aseco->client->isError()) {
					trigger_error('[' . $aseco->client->getErrorCode() . '] GetGuestList - ' . $aseco->client->getErrorMessage(), E_USER_WARNING);
					$done = true;
					break;
				}
				foreach ($players as $prow) {
					// fetch nickname for this login
					$lgn = $prow['Login'];
					$nick = $aseco->server->players->getPlayerNickname($lgn);
					$newlist[$lgn] = array($lgn, $nick);
				}
				if (count($players) < $size) {
					// got less than 300 players, might as well leave
					$done = true;
				}
				else {
					$i += $size;
				}
			}
			else {
				$done = true;
			}
		}
		return $newlist;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function collect_results ($key, $val, $indent) {
		global $method_results;

		if (is_array($val)) {
			// recursively compile array results
			$method_results[] = $indent .'*'. $key .' :';
			foreach ($val as $key2 => $val2) {
				$this->collect_results($key2, $val2, '   '. $indent);
			}
		}
		else {
			if (!is_string($val)) {
				$val = strval($val);
			}

			// format result key/value pair
			$val = explode(LF, wordwrap($val, 32, LF . $indent .'      ', true));
			$firstline = true;
			foreach ($val as $line) {
				if ($firstline) {
					$method_results[] = $indent . $key .' = '. $line;
				}
				else {
					$method_results[] = $line;
				}
				$firstline = false;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles ManiaLink admin responses
	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// leave actions outside 2201 - 5200 to other handlers
		$action = (int) $answer[2];
		if ($action < 2201 && $action > 5200 &&
		    $action < -8100 && $action > -7901)
			return;

		// get player & possible parameter
		$player = $aseco->server->players->getPlayer($answer[1]);
		if (isset($player->panels['plyparam']))
			$param = $player->panels['plyparam'];

		// check for /admin warn command
		if ($action >= 2201 && $action <= 2400) {
			$target = $player->playerlist[$action-2201]['login'];

			// warn selected player
			$aseco->releaseChatCommand('/admin warn '. $target, $player->login);
		}

		// check for /admin ignore command
		else if ($action >= 2401 && $action <= 2600) {
			$target = $player->playerlist[$action-2401]['login'];

			// ignore selected player
			$aseco->releaseChatCommand('/admin ignore '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin unignore command
		else if ($action >= 2601 && $action <= 2800) {
			$target = $player->playerlist[$action-2601]['login'];

			// unignore selected player
			$aseco->releaseChatCommand('/admin unignore '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin kick command
		else if ($action >= 2801 && $action <= 3000) {
			$target = $player->playerlist[$action-2801]['login'];

			// kick selected player
			$aseco->releaseChatCommand('/admin kick '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin ban command
		else if ($action >= 3001 && $action <= 3200) {
			$target = $player->playerlist[$action-3001]['login'];

			// ban selected player
			$aseco->releaseChatCommand('/admin ban '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin unban command
		else if ($action >= 3201 && $action <= 3400) {
			$target = $player->playerlist[$action-3201]['login'];

			// unban selected player
			$aseco->releaseChatCommand('/admin unban '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin black command
		else if ($action >= 3401 && $action <= 3600) {
			$target = $player->playerlist[$action-3401]['login'];

			// black selected player
			$aseco->releaseChatCommand('/admin black '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin unblack command
		else if ($action >= 3601 && $action <= 3800) {
			$target = $player->playerlist[$action-3601]['login'];

			// unblack selected player
			$aseco->releaseChatCommand('/admin unblack '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin addguest command
		else if ($action >= 3801 && $action <= 4000) {
			$target = $player->playerlist[$action-3801]['login'];

			// addguest selected player
			$aseco->releaseChatCommand('/admin addguest '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin removeguest command
		else if ($action >= 4001 && $action <= 4200) {
			$target = $player->playerlist[$action-4001]['login'];

			// removeguest selected player
			$aseco->releaseChatCommand('/admin removeguest '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin forcespec command
		else if ($action >= 4201 && $action <= 4400) {
			$target = $player->playerlist[$action-4201]['login'];

			// forcespec selected player
			$aseco->releaseChatCommand('/admin forcespec '. $target, $player->login);

			// refresh players window
			$aseco->releaseChatCommand('/admin players '. $param, $player->login);
		}

		// check for /admin unignore command in listignores
		else if ($action >= 4401 && $action <= 4600) {
			$target = $player->playerlist[$action-4401]['login'];

			// unignore selected player
			$aseco->releaseChatCommand('/admin unignore '. $target, $player->login);

			// check whether last player was unignored
			$ignores = $this->getIgnorelist($aseco);
			if (empty($ignores)) {
				// close main window
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);
			}
			else {
				// refresh listignores window
				$aseco->releaseChatCommand('/admin listignores', $player->login);
			}
		}

		// check for /admin unban command in listbans
		else if ($action >= 4601 && $action <= 4800) {
			$target = $player->playerlist[$action-4601]['login'];

			// unban selected player
			$aseco->releaseChatCommand('/admin unban '. $target, $player->login);

			// check whether last player was unbanned
			$bans = $this->getBanlist($aseco);
			if (empty($bans)) {
				// close main window
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);
			}
			else {
				// refresh listbans window
				$aseco->releaseChatCommand('/admin listbans', $player->login);
			}
		}

		// check for /admin unblack command in listblacks
		else if ($action >= 4801 && $action <= 5000) {
			$target = $player->playerlist[$action-4801]['login'];

			// unblack selected player
			$aseco->releaseChatCommand('/admin unblack '. $target, $player->login);

			// check whether last player was unblacked
			$blacks = $this->getBlacklist($aseco);
			if (empty($blacks)) {
				// close main window
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);
			}
			else {
				// refresh listblacks window
				$aseco->releaseChatCommand('/admin listblacks', $player->login);
			}
		}

		// check for /admin removeguest command in listguests
		else if ($action >= 5001 && $action <= 5200) {
			$target = $player->playerlist[$action-5001]['login'];

			// removeguest selected player
			$aseco->releaseChatCommand('/admin removeguest '. $target, $player->login);

			// check whether last guest was removed
			$guests = $this->getGuestlist($aseco);
			if (empty($guests)) {
				// close main window
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);
			}
			else {
				// refresh listguests window
				$aseco->releaseChatCommand('/admin listguest', $player->login);
			}
		}

		// check for /admin unbanip command
		else if ($action >= -8100 && $action <= -7901) {
			$target = $player->playerlist[abs($action)-7901]['ip'];

			// unbanip selected IP
			$aseco->releaseChatCommand('/admin unbanip '. $target, $player->login);

			// check whether last IP was unbanned
			if (!$empty = empty($aseco->banned_ips)) {
				$empty = true;
				for ($i = 0; $i < count($aseco->banned_ips); $i++) {
					if ($aseco->banned_ips[$i] != '') {
						$empty = false;
						break;
					}
				}
			}
			if ($empty) {
				// close main window
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);
			}
			else {
				// refresh listips window
				$aseco->releaseChatCommand('/admin listips '. $target, $player->login);
			}
		}
	}
}

?>
