<?php
/*
 * Plugin: Chat Admin
 * ~~~~~~~~~~~~~~~~~~
 * » Provides regular admin commands.
 * » Based upon chat.admin.php from XAseco2/1.03 written by Xymph and others
 *
 * Translation status: command descriptions done, everything else missing
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

//	public $method_results;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.2');
		$this->setBuild('2019-07-16');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('chat.admin', 'plugin_description'));

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginAccessControl',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginAutotime',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginDonate',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginPanels',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRasp',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspJukebox',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED,	'1.0.0', null);
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
			'help'				=> new Message('chat.admin', 'help'),
			'helpall'			=> new Message('chat.admin', 'helpall'),
			'setservername'			=> new Message('chat.admin', 'setservername'),
			'setcomment'			=> new Message('chat.admin', 'setcomment'),
			'setpwd'			=> new Message('chat.admin', 'setpwd'),
			'setspecpwd'			=> new Message('chat.admin', 'setspecpwd'),
			'setrefpwd'			=> new Message('chat.admin', 'setrefpwd'),
			'setmaxplayers'			=> new Message('chat.admin', 'setmaxplayers'),
			'setmaxspecs'			=> new Message('chat.admin', 'setmaxspecs'),
			'setgamemode'			=> new Message('chat.admin', 'setgamemode'),
			'setrefmode'			=> new Message('chat.admin', 'setrefmode'),
			'nextmap/next'			=> new Message('chat.admin', 'nextmap'),
			'skipmap/skip'			=> new Message('chat.admin', 'skipmap'),
			'previous/prev'			=> new Message('chat.admin', 'previous'),
			'nextenv'			=> new Message('chat.admin', 'nextenv'),
			'restartmap/restart/res'	=> new Message('chat.admin', 'restartmap'),
			'replaymap/replay'		=> new Message('chat.admin', 'replaymap'),
			'dropjukebox/djb'		=> new Message('chat.admin', 'dropjukebox'),
			'clearjukebox/cjb'		=> new Message('chat.admin', 'clearjukebox'),
//			'clearhist'			=> 'Clears (part of) map history', // UNTRANSLATED
			'add'				=> new Message('chat.admin', 'add'),
			'addthis'			=> new Message('chat.admin', 'addthis'),
			'addlocal'			=> new Message('chat.admin', 'addlocal'),
			'warn'				=> new Message('chat.admin', 'warn'),
			'kick'				=> new Message('chat.admin', 'kick'),
			'kickghost'			=> new Message('chat.admin', 'kickghost'),
			'ban'				=> new Message('chat.admin', 'ban'),
			'unban'				=> new Message('chat.admin', 'unban'),
			'banip'				=> new Message('chat.admin', 'banip'),
			'unbanip'			=> new Message('chat.admin', 'unbanip'),
			'black'				=> new Message('chat.admin', 'black'),
			'unblack'			=> new Message('chat.admin', 'unblack'),
			'addguest'			=> new Message('chat.admin', 'addguest'),
			'removeguest'			=> new Message('chat.admin', 'removeguest'),
			'pass'				=> new Message('chat.admin', 'pass'),
			'cancel/can'			=> new Message('chat.admin', 'cancel'),
			'endround/er'			=> new Message('chat.admin', 'endround'),
			'players'			=> new Message('chat.admin', 'players'),
			'showbanlist/listbans'		=> new Message('chat.admin', 'showbanlist'),
			'showiplist/listips'		=> new Message('chat.admin', 'showiplist'),
			'showblacklist/listblacks'	=> new Message('chat.admin', 'showblacklist'),
			'showguestlist/listguests'	=> new Message('chat.admin', 'showguestlist'),
			'writeiplist'			=> new Message('chat.admin', 'writeiplist'),
			'readiplist'			=> new Message('chat.admin', 'readiplist'),
			'writeblacklist'		=> new Message('chat.admin', 'writeblacklist'),
			'readblacklist'			=> new Message('chat.admin', 'readblacklist'),
			'writeguestlist'		=> new Message('chat.admin', 'writeguestlist'),
			'readguestlist'			=> new Message('chat.admin', 'readguestlist'),
			'cleanbanlist'			=> new Message('chat.admin', 'cleanbanlist'),
			'cleaniplist'			=> new Message('chat.admin', 'cleaniplist'),
			'cleanblacklist'		=> new Message('chat.admin', 'cleanblacklist'),
			'cleanguestlist'		=> new Message('chat.admin', 'cleanguestlist'),
			'mergegbl'			=> new Message('chat.admin', 'mergegbl'),
			'access'			=> new Message('chat.admin', 'access'),
			'writemaplist'			=> new Message('chat.admin', 'writemaplist'),
			'readmaplist'			=> new Message('chat.admin', 'readmaplist'),
			'shuffle/shufflemaps'		=> new Message('chat.admin', 'shuffle'),
			'remove'			=> new Message('chat.admin', 'remove'),
			'erase'				=> new Message('chat.admin', 'erase'),
			'removethis'			=> new Message('chat.admin', 'removethis'),
			'erasethis'			=> new Message('chat.admin', 'erasethis'),
			'mute/ignore'			=> new Message('chat.admin', 'mute'),
			'unmute/unignore'		=> new Message('chat.admin', 'unmute'),
			'mutelist/listmutes'		=> new Message('chat.admin', 'mutelist'),
			'ignorelist/listignores'	=> new Message('chat.admin', 'mutelist'),
			'cleanmutes/cleanignores'	=> new Message('chat.admin', 'cleanmutes'),
			'addadmin'			=> new Message('chat.admin', 'addadmin'),
			'removeadmin'			=> new Message('chat.admin', 'removeadmin'),
			'addop'				=> new Message('chat.admin', 'addop'),
			'removeop'			=> new Message('chat.admin', 'removeop'),
//			'listmasters'			=> new Message('chat.admin', 'listmasters'),
//			'listadmins'			=> new Message('chat.admin', 'listadmins'),
//			'listops'			=> new Message('chat.admin', 'listops'),
			'adminability'			=> new Message('chat.admin', 'adminability'),
			'opability'			=> new Message('chat.admin', 'opability'),
			'listabilities'			=> new Message('chat.admin', 'listabilities'),
			'writeabilities'		=> new Message('chat.admin', 'writeabilities'),
			'readabilities'			=> new Message('chat.admin', 'readabilities'),
			'wall/mta'			=> new Message('chat.admin', 'wall'),
			'delrec'			=> new Message('chat.admin', 'delrec'),
			'prunerecs'			=> new Message('chat.admin', 'prunerecs'),
			'amdl'				=> new Message('chat.admin', 'amdl'),
			'autotime'			=> new Message('chat.admin', 'autotime'),
			'disablerespawn'		=> new Message('chat.admin', 'disablerespawn'),
			'forceshowopp'			=> new Message('chat.admin', 'forceshowopp'),
			'scorepanel'			=> new Message('chat.admin', 'scorepanel'),
			'roundsfinish'			=> new Message('chat.admin', 'roundsfinish'),
			'forceteam'			=> new Message('chat.admin', 'forceteam'),
			'forcespec'			=> new Message('chat.admin', 'forcespec'),
			'specfree'			=> new Message('chat.admin', 'specfree'),
			'panel'				=> new Message('chat.admin', 'panel'),
			'admpanel'			=> new Message('chat.admin', 'admpanel'),
			'panelbg'			=> new Message('chat.admin', 'panelbg'),
			'planets'			=> new Message('chat.admin', 'planets'),
			'pay'				=> new Message('chat.admin', 'pay'),
			'relays'			=> new Message('chat.admin', 'relays'),
			'server'			=> new Message('chat.admin', 'server'),
			'pm'				=> new Message('chat.admin', 'pm'),
			'pmlog'				=> new Message('chat.admin', 'pmlog'),
			'call'				=> new Message('chat.admin', 'call'),
			'unlock'			=> new Message('chat.admin', 'unlock'),
			'debug'				=> new Message('chat.admin', 'debug'),
			'shutdown'			=> new Message('chat.admin', 'shutdown'),
			'shutdownall'			=> new Message('chat.admin', 'shutdownall'),
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

		if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
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
				if ($aseco->isOperator($admin) && $aseco->allowOperatorAbility($command['params'][0])) {
					$logtitle = 'Operator';
					$chattitle = $aseco->titles['OPERATOR'][0];
				}
				else {
					// write warning in console
					$aseco->console('[ChatAdmin] Player ['. $login .'] tried to use admin chat command (no permission!): '. $arglist[0] .' '. $arglist[1]);
					// show chat message
					$aseco->client->query('ChatSendToLogin', $aseco->formatColors((new Message('chat.admin', 'insufficient_rights'))->finish($login)), $login);
					return false;
				}
			}
		}

		// check for unlocked password (or unlock command)
		if ($aseco->settings['lock_password'] !== '' && !$admin->unlocked && $command['params'][0] !== 'unlock') {
			// write warning in console
			$aseco->console('[ChatAdmin] Player ['. $login .'] tried to use admin chat command, but was not unlocked: '. $arglist[0] .' '. $arglist[1]);

			// show chat message
			$aseco->client->query('ChatSendToLogin', $aseco->formatColors((new Message('chat.admin', 'insufficient_rights'))->finish($login)), $login);
			return false;
		}

		$command['params'][0] = strtolower($command['params'][0]);
		if ($command['params'][0] === 'help') {
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
				if ( ($allowed === true) && ($aseco->allowAbility($admin, $name)) ) {
					$active_commands[$name] = $cc;
				}
			}

			// Show active admin commands on command line
			$aseco->showHelp($aseco, $login, $active_commands, $logtitle, true, false);
		}
		else if ($command['params'][0] === 'helpall') {
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
				if ( ($allowed === true) && ($aseco->allowAbility($admin, $name)) ) {
					$active_commands[$name] = $cc;
				}
			}

			// Display active admin commands in popup with descriptions
			$aseco->showHelp($aseco, $login, $active_commands, $logtitle, true, true, 0.42);
		}
		else if ($command['params'][0] === 'setservername' && $command['params'][1] !== '') {
			/**
			 * Sets a new server name (on the fly).
			 */

			// set a new servername
			$aseco->client->query('SetServerName', $arglist[1]);
			$aseco->server->name = $arglist[1];

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] set new server name [{3}]', $logtitle, $login, $arglist[1]);

			// show chat message
			$msg = new Message('chat.admin', 'message_setservername');
			$msg->addPlaceholders($chattitle, $admin->nickname, $arglist[1]);
			$msg->sendChatMessage();

		}
		else if ($command['params'][0] === 'setcomment' && $command['params'][1] !== '') {
			/**
			 * Sets a new server comment (on the fly).
			 */

			// set a new server comment
			$aseco->client->query('SetServerComment', $arglist[1]);

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] set new server comment [{3}]', $logtitle, $login, $arglist[1]);

			// show chat message
			$msg = new Message('chat.admin', 'message_setservercomment');
			$msg->addPlaceholders($chattitle, $admin->nickname,	$arglist[1]);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'setpwd') {
			/**
			 * Sets a new player password (on the fly).
			 */

			// set a new player password
			$aseco->client->query('SetServerPassword', $arglist[1]);

			if ($arglist[1] !== '') {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set new player password [{3}] (masked password)', $logtitle, $login, preg_replace('#.#', '*', $arglist[1]));

				// show chat message
				$msg = new Message('chat.admin', 'message_setplayerpwd');
				$msg->addPlaceholders($chattitle, $admin->nickname,	$arglist[1]);
				$msg->sendChatMessage();
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] disabled player password', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_disableplayerpwd');
				$msg->addPlaceholders($chattitle, $admin->nickname);
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'setspecpwd') {
			/**
			 * Sets a new spectator password (on the fly).
			 */

			// set a new spectator password
			$aseco->client->query('SetServerPasswordForSpectator', $arglist[1]);

			if ($arglist[1] !== '') {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set new spectator password [{3}] (masked password)', $logtitle, $login, preg_replace('#.#', '*', $arglist[1]));

				// show chat message
				$msg = new Message('chat.admin', 'message_setplayerpwd');
				$msg->addPlaceholders($chattitle, $admin->nickname,	$arglist[1]);
				$msg->sendChatMessage();
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] disabled spectator password', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_disablespecpwd');
				$msg->addPlaceholders($chattitle, $admin->nickname,	$arglist[1]);
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'setrefpwd') {
			/**
			 * Sets a new referee password (on the fly).
			 */

			// set a new referee password
			$aseco->client->query('SetRefereePassword', $arglist[1]);

			if ($arglist[1] !== '') {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set new referee password [{3}] (masked password)', $logtitle, $login, preg_replace('#.#', '*', $arglist[1]));

				// show chat message
				/* Players shouldnt know about that
				$message = $aseco->formatText('',
					$chattitle,
					$admin->nickname,
					$arglist[1]
				);
				$aseco->sendChatMessage($message, $login);
				*/
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] disabled referee password', $logtitle, $login);

				// show chat message
				/* Players shouldnt know about that
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} disables referee password',
					$chattitle,
					$admin->nickname
				);
				$aseco->sendChatMessage($message, $login);
				*/
			}
		}
		else if ($command['params'][0] === 'setmaxplayers' && is_numeric($command['params'][1]) && $command['params'][1] > 0) {
			/**
			 * Sets a new player maximum that is able to connect to the server.
			 */

			// tell server to set new player max
			$aseco->client->query('SetMaxPlayers', (int) $command['params'][1]);

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] set new player maximum [{3}]', $logtitle, $login, $command['params'][1]);

			// show chat message
			$msg = new Message('chat.admin', 'message_setmaxplayers');
			$msg->addPlaceholders($chattitle, $admin->nickname,	$command['params'][1]);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'setmaxspecs' && is_numeric($command['params'][1]) && $command['params'][1] >= 0) {
			/**
			 * Sets a new spectator maximum that is able to connect to the server.
			 */

			// tell server to set new spectator max
			$aseco->client->query('SetMaxSpectators', (int) $command['params'][1]);

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] set new spectator maximum [{3}]', $logtitle, $login, $command['params'][1]);

			// show chat message
			$msg = new Message('chat.admin', 'message_setmaxspecs');
			$msg->addPlaceholders($chattitle, $admin->nickname,	$command['params'][1]);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'setgamemode' && $command['params'][1] !== '') {
			/**
			 * Sets new game mode that will be active upon the next map.
			 */

			// Remove possible given fullname
			$command['params'][1] = str_replace('.script.txt', '', strtolower($command['params'][1]));

			// check mode parameter
			$modeId = false;
			$modeScript = false;
			switch ($command['params'][1]) {
				case 'rounds':
					$modeId = Gameinfo::ROUNDS;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::ROUNDS);
					break;

				case 'timeattack':
					$modeId = Gameinfo::TIME_ATTACK;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::TIME_ATTACK);
					break;

				case 'team':
					$modeId = Gameinfo::TEAM;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::TEAM);
					break;

				case 'laps':
					$modeId = Gameinfo::LAPS;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::LAPS);
					break;

				case 'cup':
					$modeId = Gameinfo::CUP;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::CUP);
					break;

				case 'teamattack':
					$modeId = Gameinfo::TEAM_ATTACK;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::TEAM_ATTACK);
					break;

				case 'chase':
					$modeId = Gameinfo::CHASE;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::CHASE);
					break;

				case 'knockout':
					$modeId = Gameinfo::KNOCKOUT;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::KNOCKOUT);
					break;

				case 'doppler':
					$modeId = Gameinfo::DOPPLER;
					$modeScript = $aseco->server->gameinfo->getModeScriptName(Gameinfo::DOPPLER);
					break;

				default:
					$modeId = false;
			}

			if ($modeId !== false) {
				if ($aseco->changing_to_gamemode !== false || $modeId !== $aseco->server->gameinfo->mode) {

					// Store the next Gamemode
					$aseco->changing_to_gamemode = $modeId;

					// Tell server to set new game mode
					$aseco->client->query('SetScriptName', $modeScript);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] set new game mode [{3}]', $logtitle, $login, $modeScript);

					// show chat message
					$msg = new Message('chat.admin', 'message_setgamemode');
					$msg->addPlaceholders($chattitle, $admin->nickname, $modeScript);
					$msg->sendChatMessage();
				}
				else {
					$aseco->changing_to_gamemode = false;
					/* $message = '{#server}» Same game mode {#highlite}'. $modeScript;
					$aseco->sendChatMessage($message, $login); */
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_setgamemode_invalid');
				$msg->addPlaceholders(strtoupper($command['params'][1]));
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'setrefmode') {
			/**
			 * Sets new referee mode (0 = top3, 1 = all).
			 */

			if (($mode = $command['params'][1]) !== '') {
				if (is_numeric($mode) && ($mode === 0 || $mode === 1)) {
					// tell server to set new referee mode
					$aseco->client->query('SetRefereeMode', (int) $mode);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] set new referee mode [{3}]', $logtitle, $login, strtoupper($mode));

					// show chat message
					$msg = new Message('chat.admin', 'message_setrefmode');
					$msg->addPlaceholders($chattitle, $admin->nickname,	$mode);
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_setrefmode_invalid');
					$msg->addPlaceholders(strtoupper($mode));
					$msg->sendChatMessage();
				}
			}
			else {
				// tell server to get current referee mode
				$mode = $aseco->client->query('GetRefereeMode');

				// show chat message
				$msg = new Message('chat.admin', 'message_getrefmode');
				$msg->addPlaceholders($mode === 1 ? 'All' : 'Top-3');
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'nextmap' || $command['params'][0] === 'next' || $command['params'][0] === 'skipmap' || $command['params'][0] === 'skip') {
			/**
			 * Forces the server to load next map.
			 */

			try {
				// Load the next map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
					$aseco->client->query('NextMap', true);
				}
				else {
					$aseco->client->query('NextMap');
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] skips map!', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_nextmap');
				$msg->addPlaceholders($chattitle, $admin->nickname);
				$msg->sendChatMessage();
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - NextMap');
				$message = '{#server}» {#error}Error skip to next map!';
				$aseco->sendChatMessage($message, $login);
			}

		}
		else if ($command['params'][0] === 'previous' || $command['params'][0] === 'prev') {
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
					$aseco->console_text('/admin prev jukebox:'. CRLF .
						print_r($aseco->plugins['PluginRaspJukebox']->jukebox, true)
					);
				}

				// load the previous map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
					$aseco->client->query('NextMap', true);
				}
				else {
					$aseco->client->query('NextMap');
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] revisits previous map!', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_prevmap');
				$msg->addPlaceholders($chattitle, $admin->nickname);
				$msg->sendChatMessage();

				// Setup next Map
				$aseco->server->maps->next = $aseco->server->maps->getNextMap();

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('previous', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_prevmap_failed');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'nextenv') {
			if (isset($aseco->plugins['PluginRaspJukebox']) === true && isset($aseco->plugins['PluginRasp']) === true) {
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
					if ($map['uid'] === $aseco->server->maps->current->uid) {
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
					$aseco->console_text('/admin nextenv jukebox:'. CRLF .
						print_r($aseco->plugins['PluginRaspJukebox']->jukebox, true)
					);
				}

				// load the next environment map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
					$aseco->client->query('NextMap', true);
				}
				else {
					$aseco->client->query('NextMap');
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] skips to next {3} map!', $logtitle, $login, $aseco->server->maps->current->environment);

				// show chat message
				$msg = new Message('chat.admin', 'message_nextenv');
				$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->server->maps->current->environment);
				$msg->sendChatMessage();

				// Setup next Map
				$aseco->server->maps->next = $aseco->server->maps->getNextMap();

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('nextenv', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_nextenv_failed');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'restartmap' || $command['params'][0] === 'restart' || $command['params'][0] === 'res') {
			/**
			 * Restarts the currently running map.
			 */

			// Simulate a onEndMap for Dedimania, otherwise new driven records are lost!
			if ( isset($aseco->plugins['PluginDedimania']) ) {
				$aseco->plugins['PluginDedimania']->onEndMap($aseco, $aseco->server->maps->current);
			}

			// Do not clear scores if in Cup mode
			if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
				$aseco->client->query('RestartMap', true);
			}
			else {
				$aseco->client->query('RestartMap');
			}

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] restarts map!', $logtitle, $login);

			// show chat message
			$msg = new Message('chat.admin', 'message_restartmap');
			$msg->addPlaceholders($chattitle, $admin->nickname);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'replaymap' || $command['params'][0] === 'replay') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Replays the current map (queues it at start of jukebox).
				 */

				// cancel possibly ongoing replay/restart vote
				$aseco->client->query('CancelVote');
				if (isset($aseco->plugins['PluginRaspVotes']) === true) {
					if (!empty($aseco->plugins['PluginRaspVotes']->chatvote) && $aseco->plugins['PluginRaspVotes']->chatvote['type'] === 2) {
						$aseco->plugins['PluginRaspVotes']->chatvote = array();
					}
				}

				// check if map already in jukebox
				if (!empty($aseco->plugins['PluginRaspJukebox']->jukebox) && array_key_exists($aseco->server->maps->current->uid, $aseco->plugins['PluginRaspJukebox']->jukebox)) {
					$msg = new Message('chat.admin', 'message_replaymap_alreadyreplayed');
					$msg->sendChatMessage($login);
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
				$aseco->console('[ChatAdmin] {1} [{2}] requeues map!', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_replaymap');
				$msg->addPlaceholders($chattitle, $admin->nickname);
				$msg->sendChatMessage();

				// Setup next Map (which is the current one in this case)
				$aseco->server->maps->next = $aseco->server->maps->current;

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('replay', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_replaymap_failed');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'dropjukebox' || $command['params'][0] === 'djb') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Drops a map from the jukebox (for use with rasp jukebox plugin).
				 */

				// verify parameter
				if (is_numeric($command['params'][1]) &&
				    $command['params'][1] >= 1 && $command['params'][1] <= count($aseco->plugins['PluginRaspJukebox']->jukebox)) {
					$i = 1;
					foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $item) {
						if ($i++ === (int)$command['params'][1]) {
							$name = $aseco->stripStyles($item['Name']);
							$uid = $item['uid'];
							break;
						}
					}
					$drop = $aseco->plugins['PluginRaspJukebox']->jukebox[$uid];
					unset($aseco->plugins['PluginRaspJukebox']->jukebox[$uid]);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] drops map {3} from jukebox!', $logtitle, $login, $aseco->stripStyles($name, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_dropjukebox');
					$msg->addPlaceholders($chattitle, $admin->nickname, $name);
					$msg->sendChatMessage();

					// Setup next Map
					$aseco->server->maps->next = $aseco->server->maps->getNextMap();

					// throw 'jukebox changed' event
					$aseco->releaseEvent('onJukeboxChanged', array('drop', $drop));
				}
				else {
					$msg = new Message('chat.admin', 'message_dropjukebox_notfound');
					$msg->sendChatMessage();
				}
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_dropjukebox_failed');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'clearjukebox' || $command['params'][0] === 'cjb') {
			if (isset($aseco->plugins['PluginRaspJukebox'])) {
				/**
				 * Clears the jukebox (for use with rasp jukebox plugin).
				 */

				// clear jukebox
				$aseco->plugins['PluginRaspJukebox']->jukebox = array();

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] clears jukebox!', $logtitle, $login);

				// show chat message
				$msg = new Message('chat.admin', 'message_clearjukebox');
				$msg->addPlaceholders($chattitle, $admin->nickname);
				$msg->sendChatMessage();

				// Setup next Map
				$aseco->server->maps->next = $aseco->server->maps->getNextMap();

				// throw 'jukebox changed' event
				$aseco->releaseEvent('onJukeboxChanged', array('clear', null));
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_clearjukebox_failed');
				$msg->sendChatMessage($login);
			}
		}
//		else if ($command['params'][0] === 'clearhist') {
//			if (isset($aseco->plugins['PluginRaspJukebox'])) {
//				/**
//				 * Clears (part of) map history.
//				 */
//
//				// check for optional portion (pos = newest, neg = oldest)
//				if ($command['params'][1] !== '' && is_numeric($command['params'][1]) && $command['params'][1] !== 0) {
//					$clear = intval($command['params'][1]);
//
//					// log console message
//					$aseco->console('[ChatAdmin] {1} [{2}] clears {3} map{4} from history!', $logtitle, $login,
//						($clear > 0 ? 'newest ' : 'oldest ') . abs($clear),
//						(abs($clear) === 1 ? '' : 's')
//					);
//
//					// show chat message
//					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} clears {3}{#admin} map{4} from history!',
//						$chattitle,
//						$admin->nickname,
//						($clear > 0 ? 'newest {#highlite}' : 'oldest {#highlite}') . abs($clear),
//						(abs($clear) === 1 ? '' : 's')
//					);
//					$aseco->sendChatMessage($message);
//				}
//				else if (strtolower($command['params'][1]) === 'all') {  // entire history
//					$clear = $aseco->plugins['PluginRaspJukebox']->buffersize;
//
//					// log console message
//					$aseco->console('[ChatAdmin] {1} [{2}] clears entire map history!', $logtitle, $login);
//
//					// show chat message
//					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} clears entire map history!',
//						$chattitle,
//						$admin->nickname
//					);
//					$aseco->sendChatMessage($message);
//				}
//				else {
//					// show chat message
//					$message = $aseco->formatText('{#server}» {#admin}The map history contains {#highlite}{3}{#admin} map{4}',
//						$chattitle,
//						$admin->nickname,
//						count($aseco->plugins['PluginRaspJukebox']->jb_buffer), (count($aseco->plugins['PluginRaspJukebox']->jb_buffer) === 1 ? '' : 's')
//					);
//					$aseco->sendChatMessage($message, $login);
//					return;
//				}
//
//				// clear map history (portion)
//				$i = 0;
//				if ($clear > 0) {
//					if ($clear > $aseco->plugins['PluginRaspJukebox']->buffersize) {
//						$clear = $aseco->plugins['PluginRaspJukebox']->buffersize;
//					}
//					while ($i++ < $clear) {
//						array_pop($aseco->plugins['PluginRaspJukebox']->jb_buffer);
//					}
//				}
//				else {
//					if ($clear < -$aseco->plugins['PluginRaspJukebox']->buffersize) {
//						$clear = -$aseco->plugins['PluginRaspJukebox']->buffersize;
//					}
//					while ($i-- > $clear) {
//						array_shift($aseco->plugins['PluginRaspJukebox']->jb_buffer);
//					}
//				}
//			}
//			else {
//				// show chat message
//				$message = '{#server}» {#admin}Clear history unavailable - include plugin.rasp_jukebox.php in [config/plugins.xml]';
//				$aseco->sendChatMessage($message, $login);
//			}
//		}
		else if ($command['params'][0] === 'add') {
			/**
			 * Adds MX maps to the map rotation.
			 */
			$this->admin_add($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'addthis') {
			/**
			 * Adds current /add-ed map permanently to server's map list
			 * by preventing its removal that normally occurs afterwards
			 */
			$this->admin_addthis($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'addlocal') {
			/**
			 * Add a local map to the map rotation.
			 */
			$this->admin_addlocal($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'warn' && $command['params'][1] !== '') {
			/**
			 * Warns a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// display warning message
				$message = (new Message('chat.admin', 'player_warning'))->finishMultiline($target->login);

				foreach ($message as &$line) {
					$line = array($line);
				}

				// Setup settings for Window
				$settings_styles = array(
					'icon'			=> 'Icons64x64_1,TV',
					'textcolors'		=> array('FF5F', 'FFFF'),
				);
				$settings_columns = array(
					'columns'		=> 1,
					'widths'		=> array(100),
					'textcolors'		=> array('FF5F'),
				);
				$settings_content = array(
					'title'			=> 'Administrative WARNING!',
					'data'			=> $message,
					'about'			=> 'CHAT ADMIN/'. $this->getVersion(),
					'mode'			=> 'columns',
					'add_background'	=> true,
				);

				$window = new Window();
				$window->setStyles($settings_styles);
				$window->setColumns($settings_columns);
				$window->setContent($settings_content);
				$window->send($target, 0, false);


				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] warned Player [{3}] -> Nickname [{4}]!',
					$logtitle,
					$login,
					$target->login,
					$aseco->stripStyles($target->nickname, false)
				);

				// show chat message
				$msg = new Message('chat.admin', 'message_warn');
				$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'kick' && $command['params'][1] !== '') {
			/**
			 * Kicks a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] kicked player {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

				// show chat message
				$msg = new Message('chat.admin', 'message_kick');
				$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
				$msg->sendChatMessage();

				// kick the player
				$aseco->client->query('Kick', $target->login);
			}
		}
		else if ($command['params'][0] === 'kickghost' && $command['params'][1] !== '') {
			/**
			 * Kicks a ghost player with the specified login.
			 * This variant for ghost players that got disconnected doesn't
			 * check the login for validity and doesn't work with Player_IDs.
			 */

			// get player login without validation
			$target = $command['params'][1];

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] kicked ghost player {3}!', $logtitle, $login, $target);

			// show chat message
			$msg = new Message('chat.admin', 'message_kick_ghost');
			$msg->addPlaceholders($chattitle, $admin->nickname, $target);
			$msg->sendChatMessage();

			// kick the ghost player
			$aseco->client->query('Kick', $target);
		}
		else if ($command['params'][0] === 'ban' && $command['params'][1] !== '') {
			/**
			 * Ban a player with the specified login/PlayerID.
			 */

			if ($aseco->isAnyAdmin($target->login)) {

				// show chat message
				$msg = new Message('chat.admin', 'message_ban_any_admin');
				$msg->sendChatMessage();

				$aseco->console('{1} [{2}] try to ban MasterAdmin {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));
			}
			else if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] bans player {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

				// show chat message
				$msg = new Message('chat.admin', 'message_ban');
				$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
				$msg->sendChatMessage();

				// update banned IPs file
				$aseco->banned_ips[] = $target->ip;
				$aseco->writeIPs();

				// ban the player and also kick him
				$aseco->client->query('Ban', $target->login);
			}
		}
		else if ($command['params'][0] === 'unban' && $command['params'][1] !== '') {
			/**
			 * Un-bans player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				$bans = $this->getBanlist($aseco);
				// unban the player
				$rtn = $aseco->client->query('UnBan', $target->login);
				if (!$rtn) {
					// show fail message
					$msg = new Message('chat.admin', 'message_unban_notbanned');
					$msg->addPlaceholders($command['params'][1]);
					$msg->sendChatMessage($login);
				}
				else {
					if (($i = array_search($bans[$target->login][2], $aseco->banned_ips)) !== false) {
						// update banned IPs file
						$aseco->banned_ips[$i] = '';
						$aseco->writeIPs();
					}

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] unbans player {3}', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_unban');
					$msg->addPlaceholders($chattitle, $admin->nickname,	str_ireplace('$w', '', $target->nickname));
					$msg->sendChatMessage();
				}
			}
		}
		else if ($command['params'][0] === 'banip' && $command['params'][1] !== '') {
			/**
			 * Ban a player with the specified IP address.
			 */

			// check for valid IP not already banned
			$ipaddr = $command['params'][1];
			if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ipaddr)) {
				if (empty($aseco->banned_ips) || !in_array($ipaddr, $aseco->banned_ips)) {
					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] banned IP {3}!', $logtitle, $login, $ipaddr);

					// show chat message
					$message = $aseco->formatText('',
						$chattitle,
						$admin->nickname,
						$ipaddr
					);
					$aseco->sendChatMessage($message);

					$msg = new Message('chat.admin', 'message_banip');
					$msg->addPlaceholders($chattitle, $admin->nickname,	$ipaddr);
					$msg->sendChatMessage();

					// update banned IPs file
					$aseco->banned_ips[] = $ipaddr;
					$aseco->writeIPs();
				}
				else {
					$msg = new Message('chat.admin', 'message_ip_already_banned');
					$msg->addPlaceholders($ipaddr);
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_ip_invalid');
				$msg->addPlaceholders($ipaddr);
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'unbanip' && $command['params'][1] !== '') {
			/**
			 * Un-bans player with the specified IP address.
			 */

			// check for banned IP
			if (($i = array_search($command['params'][1], $aseco->banned_ips)) === false) {
				$msg = new Message('chat.admin', 'message_ip_not_banned');
				$msg->addPlaceholders($command['params'][1]);
				$msg->sendChatMessage($login);
			}
			else {
				// update banned IPs file
				$aseco->banned_ips[$i] = '';
				$aseco->writeIPs();

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] unbans IP {3}', $logtitle, $login, $command['params'][1]);

				// show chat message
				$msg = new Message('chat.admin', 'message_unbanip');
				$msg->addPlaceholders($command['params'][1]);
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'black' && $command['params'][1] !== '') {
			/**
			 * Blacklists a player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] blacklists player {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

				// show chat message
				$msg = new Message('chat.admin', 'message_blacklist_player');
				$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
				$msg->sendChatMessage();

				try {
					// blacklist the player...
					$aseco->client->query('BlackList', $target->login);
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - BlackList');
				}

				try {
					// ...and then kick him
					$aseco->client->query('Kick', $target->login);
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick');
				}

				try {
					// update blacklist file
					$filename = $aseco->settings['blacklist_file'];
					$aseco->client->query('SaveBlackList', $filename);
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBlackList');
				}
			}
		}
		else if ($command['params'][0] === 'unblack' && $command['params'][1] !== '') {
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
					$msg = new Message('chat.admin', 'message_unblack_use_cmd_first');
					$msg->sendChatMessage($login);
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
					$msg = new Message('chat.admin', 'message_playerid_notfound');
					$msg->sendChatMessage($login);
					return false;
				}
			}

			// check for valid param
			if ($target !== false) {
				$target->login = $param;
				$target->nickname = $aseco->server->players->getPlayerNickname($param);
				if ($target->nickname === false) {
					$target->nickname = $param;
				}

				try {
					// unblacklist the player
					$aseco->client->query('UnBlackList', $target->login);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] unblacklists player {3}', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_unblack');
					$msg->addPlaceholders($chattitle, $admin->nickname,	str_ireplace('$w', '', $target->nickname));
					$msg->sendChatMessage();

					try {
						// update blacklist file
						$filename = $aseco->settings['blacklist_file'];
						$aseco->client->query('SaveBlackList', $filename);
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBlackList');
					}
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - UnBlackList');

					$msg = new Message('chat.admin', 'message_not_blacklisted');
					$msg->addPlaceholders($command['params'][1]);
					$msg->sendChatMessage();
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_playerid_notfound');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'addguest' && $command['params'][1] !== '') {
			/**
			 * Adds a guest player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				try {
					// add the guest player
					$aseco->client->query('AddGuest', $target->login);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] adds guest player {3}', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_add_guest');
					$msg->addPlaceholders($chattitle, $admin->nickname,	str_ireplace('$w', '', $target->nickname));
					$msg->sendChatMessage();

					try {
						// update guestlist file
						$filename = $aseco->settings['guestlist_file'];
						$aseco->client->query('SaveGuestList', $filename);
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveGuestList');
					}
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - AddGuest');
				}
			}
		}
		else if ($command['params'][0] === 'removeguest' && $command['params'][1] !== '') {
			/**
			 * Removes a guest player with the specified login/PlayerID.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				try {
					// remove the guest player
					$rtn = $aseco->client->query('RemoveGuest', $target->login);

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] removes guest player {3}', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_remove_guest');
					$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
					$msg->sendChatMessage();

					try {
						// update guestlist file
						$filename = $aseco->settings['guestlist_file'];
						$aseco->client->query('SaveGuestList', $filename);
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveGuestList');
					}
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - RemoveGuest');

					$msg = new Message('chat.admin', 'message_no_guest');
					$msg->addPlaceholders($command['params'][1]);
					$msg->sendChatMessage($login);
				}
			}
		}
		else if ($command['params'][0] === 'pass') {
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
				$msg = new Message('chat.admin', 'message_no_open_vote');
				$msg->sendChatMessage($login);
				return;
			}

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] passes vote!', $logtitle, $login);

			// show chat message
			$msg = new Message('chat.admin', 'message_vote_passed');
			$msg->addPlaceholders($chattitle, $admin->nickname);
			$msg->sendChatMessage();

			// bypass double vote check
			if ( isset($aseco->plugins['PluginRaspJukebox']) ) {
				$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
			}

			// enter the last vote
			$aseco->releaseChatCommand('/y', $login);
		}
		else if ($command['params'][0] === 'cancel' || $command['params'][0] === 'can') {
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
			}

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cancels vote!', $logtitle, $login);

			// show chat message
			$msg = new Message('chat.admin', 'message_cancel_vote');
			$msg->addPlaceholders($chattitle, $admin->nickname);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'endround' || $command['params'][0] === 'er') {
			/**
			 * Forces end of current round.
			 */

			// cancel possibly ongoing endround vote
			if ( isset($aseco->plugins['PluginRaspVotes']) ) {
				if (!empty($aseco->plugins['PluginRaspVotes']->chatvote) && $aseco->plugins['PluginRaspVotes']->chatvote['type'] === 0) {
					$aseco->plugins['PluginRaspVotes']->chatvote = array();
				}
			}

			// end this round
			$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.ForceEndRound', array((string)time()));

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] forces round end!', $logtitle, $login);

			// show chat message
			$msg = new Message('chat.admin', 'message_force_end_round');
			$msg->addPlaceholders($chattitle, $admin->nickname);
			$msg->sendChatMessage();
		}
		else if ($command['params'][0] === 'players') {
			/**
			 * Displays the live or known players (on/offline) list.
			 * Player management inspired by Mistral.
			 */
			$this->admin_players($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'showbanlist' || $command['params'][0] === 'listbans') {
			/**
			 * Displays the ban list.
			 * TODO: Port to new framework and translate
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
				$ply = '{#black}'. str_ireplace('$w', '', $player[1]) .'$z / {#login}'. $player[0];

				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, 'PluginChatAdmin?Action='. ($pid+4600));  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.', $ply);
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
			else {  // === 1
				$aseco->sendChatMessage('{#server}» {#error}No banned player(s) found!', $login);
			}
		}
		else if ($command['params'][0] === 'showiplist' || $command['params'][0] === 'listips') {
			/**
			 * Displays the banned IPs list.
			 * TODO: Port to new framework and translate
			 */

			$admin->playerlist = array();
			$admin->msgs = array();

			// get new list of all banned IPs
			$newlist = $aseco->banned_ips;
			if (empty($newlist)) {
				$aseco->sendChatMessage('{#server}» {#error}No banned IP(s) found!', $login);
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
				if ($ip !== '') {
					$plarr = array();
					$plarr['ip'] = $ip;
					$admin->playerlist[] = $plarr;

					// format IP
					$ply = '{#black}'. $ip;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $pid <= 200) {
						$ply = array($ply, 'PluginChatAdmin?Action='. (-7900-$pid));  // action id
					}

					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.', $ply);
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
			else {  // === 1
				$aseco->sendChatMessage('{#server}» {#error}No banned IP(s) found!', $login);
			}
		}
		else if ($command['params'][0] === 'showblacklist' || $command['params'][0] === 'listblacks') {
			/**
			 * Displays the black list.
			 * TODO: Port to new framework and translate
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
				$ply = '{#black}'. str_ireplace('$w', '', $player[1])
				       .'$z / {#login}'. $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, 'PluginChatAdmin?Action='. ($pid+4800));  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.', $ply);
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
			else {  // === 1
				$aseco->sendChatMessage('{#server}» {#error}No blacklisted player(s) found!', $login);
			}
		}
		else if ($command['params'][0] === 'showguestlist' || $command['params'][0] === 'listguests') {
			/**
			 * Displays the guest list.
			 * TODO: Port to new framework and translate
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
				$ply = '{#black}'. str_ireplace('$w', '', $player[1])
				       .'$z / {#login}'. $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, 'PluginChatAdmin?Action='. ($pid+5000));  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.', $ply);
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
			else {  // === 1
				$aseco->sendChatMessage('{#server}» {#error}No guest player(s) found!', $login);
			}
		}
		else if ($command['params'][0] === 'writeiplist') {
			/**
			 * Saves the banned IPs list to bannedips.xml (default).
			 */

			// write banned IPs file
			$filename = $aseco->settings['bannedips_file'];
			if (!$aseco->writeIPs()) {
				$message = new Message('chat.admin', 'message_failed_writing');
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] wrote '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_written');
			}
			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'readiplist') {
			/**
			 * Loads the banned IPs list from bannedips.xml (default).
			 */

			// read banned IPs file
			$filename = $aseco->settings['bannedips_file'];
			if (!$aseco->readIPs()) {
				$message = new Message('chat.admin', 'message_failed_reading');
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] read '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_read');
			}
			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'writeblacklist') {
			/**
			 * Saves the black list to blacklist.txt (default).
			 */

			try {
				$filename = $aseco->settings['blacklist_file'];
				$aseco->client->query('SaveBlackList', $filename);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] wrote '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_written');
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBlackList');
				$message = new Message('chat.admin', 'message_failed_writing');
			}

			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'readblacklist') {
			/**
			 * Loads the black list from blacklist.txt (default).
			 */

			try {
				$filename = $aseco->settings['blacklist_file'];
				$aseco->client->query('LoadBlackList', $filename);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] read '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_read');
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - LoadBlackList');
				$message = new Message('chat.admin', 'message_failed_reading');
			}

			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'writeguestlist') {
			/**
			 * Saves the guest list to guestlist.txt (default).
			 */

			try {
				$filename = $aseco->settings['guestlist_file'];
				$aseco->client->query('SaveGuestList', $filename);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] wrote '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_written');
				$aseco->sendChatMessage($message, $login);
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveGuestList');
				$message = new Message('chat.admin', 'message_failed_writing');
			}

			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'readguestlist') {
			/**
			 * Loads the guest list from guestlist.txt (default).
			 */

			try {
				$filename = $aseco->settings['guestlist_file'];
				$aseco->client->query('LoadGuestList', $filename);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] read '. $filename .'!', $logtitle, $login);

				$message = new Message('chat.admin', 'message_file_read');
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - LoadGuestList');
				$message = new Message('chat.admin', 'message_failed_reading');
			}

			// show chat message
			$message->addPlaceholders($filename);
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'cleanbanlist') {
			/**
			 * Cleans the ban list.
			 */

			// clean server ban list
			$aseco->client->query('CleanBanList');

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cleaned ban list!', $logtitle, $login);

			// show chat message
			$message = new Message('chat.admin', 'message_clean_banlist');
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'cleaniplist') {
			/**
			 * Cleans the banned IPs list.
			 */

			// clean banned IPs file
			$aseco->banned_ips = array();
			$aseco->writeIPs();

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cleaned banned IPs list!', $logtitle, $login);

			// show chat message
			$message = new Message('chat.admin', 'message_cleaned_bannediplist');
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'cleanblacklist') {
			/**
			 * Cleans the black list.
			 */

			// clean server black list
			$aseco->client->query('CleanBlackList');

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cleaned black list!', $logtitle, $login);

			// show chat message
			$message = new Message('chat.admin', 'message_cleaned_blacklist');
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'cleanguestlist') {
			/**
			 * Cleans the guest list.
			 */

			// clean server guest list
			$aseco->client->query('CleanGuestList');

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cleaned guest list!', $logtitle, $login);

			// show chat message
			$message = new Message('chat.admin', 'message_cleaned_guestlist');
			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'mergegbl') {
			/**
			 * Merges a global black list.
			 */

			if ( isset($aseco->plugins['PluginUptodate']) ) {
				if (isset($command['params'][1]) && $command['params'][1] !== '') {
					if (preg_match('/^https?:\/\/[-\w:.]+\//i', $command['params'][1])) {
						// from plugin.uptodate.php
						$aseco->plugins['PluginUptodate']->admin_mergegbl($aseco, $logtitle, $login, true, $command['params'][1]);
					}
					else {
						$msg = new Message('chat.admin', 'message_inavlid_url');
						$msg->addPlaceholders($command['params'][1]);
						$msg->sendChatMessage();
					}
				}
				else {
					// from plugin.uptodate.php
					$aseco->plugins['PluginUptodate']->admin_mergegbl($aseco, $logtitle, $login, true);
				}
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_include_uptodate');
				$msg->sendChatMessage();
			}
		}
		else if ($command['params'][0] === 'access') {
			/**
			 * Shows/reloads player access control.
			 */

			if ( isset($aseco->plugins['PluginAccessControl']) ) {
				$aseco->plugins['PluginAccessControl']->admin_access($aseco, $login, $command['params'][1]);
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_access');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'writemaplist') {
			/**
			 * Saves the map list to maplist.txt (default).
			 */

			$filename = $aseco->settings['default_maplist'];
			// check for optional alternate filename
			if ($command['params'][1] !== '') {
				$filename = $command['params'][1];
				if (!stristr($filename, '.txt')) {
					$filename .= '.txt';
				}
			}

			try {
				// Make a backup
				$source = $aseco->server->mapdir .'MatchSettings/'. $filename;
				$destination = $aseco->server->mapdir .'MatchSettings/'. date('Y-m-d-H-i-s') .'_'. $filename .'.bak';
				if (!copy($source, $destination)) {
					trigger_error('Could not copy match settings file "'. $source .'" to "'. $destination .'"!', E_USER_WARNING);
				}

				// Let the dedicated store the current settings
				$aseco->client->query('SaveMatchSettings', 'MatchSettings/'. $filename);

				// Should a random filter be added?
				if ($aseco->settings['writemaplist_random']) {
					$mapsfile = $aseco->server->mapdir .'MatchSettings/'. $filename;
					// read the match settings file
					if (!$list = @file_get_contents($mapsfile)) {
						trigger_error('Could not read match settings file "'. $mapsfile .'"!', E_USER_WARNING);
					}
					else {
						// insert random filter after <gameinfos> section
						$list = preg_replace('/<\/gameinfos>/', '$0'. CRLF . CRLF .
							"\t<filter>" . CRLF .
							"\t\t<random_map_order>1</random_map_order>" . CRLF .
							"\t</filter>", $list
						);

						// write out the match settings file
						if (!@file_put_contents($mapsfile, $list)) {
							trigger_error('Could not write match settings file "'. $mapsfile .'"!', E_USER_WARNING);
						}
					}
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] wrote map list: [{3}]!', $logtitle, $login, $mapsfile.$filename);

				$message = new Message('chat.admin', 'message_written_detailed');
				$message->addPlaceholders($aseco->server->mapdir .'MatchSettings/'. $filename);

				// throw 'maplist changed' event
				$aseco->releaseEvent('onMapListChanged', array('write', null));
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveMatchSettings');

				$message = new Message('chat.admin', 'message_failed_writing_detailed');
			}

			$message->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'readmaplist') {
			/**
			 * Loads the map list from maplist.txt (default).
			 */
			$this->admin_readmaplist($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'shuffle' || $command['params'][0] === 'shufflemaps') {
			/**
			 * Randomizes current maps list.
			 */
			$this->admin_shufflemaps($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if (($command['params'][0] === 'remove' && $command['params'][1] !== '') || ($command['params'][0] === 'erase' && $command['params'][1] !== '')) {
			/**
			 * Remove a map from the active rotation, optionally erase map file too.
			 * Doesn't update match settings unfortunately - command 'writemaplist' will though.
			 */

			// verify parameter
			$param = $command['params'][1];
			if (is_numeric($param) && $param >= 0) {
				if (empty($admin->maplist)) {
					$msg = new Message('plugin.rasp', 'list_help');
					$msg->sendChatMessage($login);
					return;
				}
				// find map by given #
				$tid = ltrim($param, '0');
				$tid--;
				if (array_key_exists($tid, $admin->maplist)) {
					$name = $aseco->stripStyles($admin->maplist[$tid]['name']);
					$filename = $aseco->server->mapdir . $admin->maplist[$tid]['filename'];

					try {
						$aseco->client->query('RemoveMap', $filename);

						if ($command['params'][0] === 'remove') {
							$msg = new Message('chat.admin', 'message_removemap');
							$msg->addPlaceholders($chattitle, $admin->nickname, $name);
							$msg->sendChatMessage();
						}
						else if ($command['params'][0] === 'erase' && is_file($filename)) {
							if (unlink($filename)) {
								$msg = new Message('chat.admin', 'message_erasemap');
								$msg->addPlaceholders($chattitle, $admin->nickname, $name);
								$msg->sendChatMessage();
							}
							else {
								$msg = new Message('chat.admin', 'message_erasemap_failed');
								$msg->addPlaceholders($chattitle, $admin->nickname, $name);
								$msg->sendChatMessage($login);
							}
						}

						// Remove map from MapList too
						$aseco->server->maps->removeMapByUid($admin->maplist[$tid]['uid']);

						// log console message
						$aseco->console('[ChatAdmin] {1} [{2}] '. $command['params'][0] .'d map {3}', $logtitle, $login, $aseco->stripStyles($name, false));

						// throw 'maplist changed' event
						$aseco->releaseEvent('onMapListChanged', array('remove', $filename));
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - RemoveMap');

						if ($command['params'][0] === 'remove') {
							$msg = new Message('chat.admin', 'message_removemap_failed');
							$msg->addPlaceholders($filename);
							$msg->sendChatMessage($login);
						}
						else {
							$msg = new Message('chat.admin', 'message_erasemap_failed');
							$msg->addPlaceholders($chattitle, $admin->nickname, $name);
							$msg->sendChatMessage($login);
						}
					}
				}
				else {
					$msg = new Message('plugin.rasp', 'jukebox_notfound');
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('plugin.rasp', 'jukebox_help');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'removethis' || $command['params'][0] === 'erasethis') {
			/**
			 * Remove current map from the active rotation, optionally erase map file too.
			 * Doesn't update match settings unfortunately - command 'writemaplist' will though.
			 */

			// get current map info and remove it from rotation
			$name = $aseco->stripStyles($aseco->server->maps->current->name);
			$filename = $aseco->server->mapdir . $aseco->server->maps->current->filename;

			try {
				$aseco->client->query('RemoveMap', $filename);

				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}removes current map: {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$name
				);

				$msg = new Message('chat.admin', 'message_removemap');
				$msg->addPlaceholders($chattitle, $admin->nickname, $name);

				if ($command['params'][0] === 'erasethis') {
					if (is_file($filename) && unlink($filename)) {
						$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s {#admin}erases current map: {#highlite}{3}',
							$chattitle,
							$admin->nickname,
							$name
						);
					}
					else if (is_file($aseco->stripBOM($filename)) && unlink($aseco->stripBOM($filename))) {
						$msg = new Message('chat.admin', 'message_erasemap');
						$msg->addPlaceholders($chattitle, $admin->nickname, $name);
					}
					else {
						$msg = new Message('chat.admin', 'message_erasemap_failed');
						$msg->addPlaceholders($chattitle, $admin->nickname, $name);
					}
				}

				$msg->sendChatMessage();

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] '. $command['params'][0] .'-ed map [{3}]', $logtitle, $login, $aseco->stripStyles($name, false));

				try {
					// Force to load the next map,
					// don't clear scores if in Cup mode
					if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
						$aseco->client->query('NextMap', true);
					}
					else {
						$aseco->client->query('NextMap');
					}
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - NextMap');

					$msg = new Message('chat.admin', 'message_load_next_map_failed');
					$msg->sendChatMessage($login);
				}

				// Remove Map from Maplist
				$aseco->server->maps->removeMapByFilename($aseco->server->maps->current->filename);

				// throw 'maplist changed' event
				$aseco->releaseEvent('onMapListChanged', array('remove', $filename));
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - RemoveMap');

				$msg = new Message('chat.admin', 'message_removemap_failed');
				$msg->addPlaceholders($filename);
				$msg->sendChatMessage($login);
			}
		}
		else if (($command['params'][0] === 'mute' || $command['params'][0] === 'ignore') && $command['params'][1] !== '') {
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
				$aseco->console('[ChatAdmin] {1} [{2}] ignores player {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

				// show chat message
				$msg = new Message('chat.admin', 'message_admin_ignores_player');
				$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
				$msg->sendChatMessage();
			}
		}
		else if (($command['params'][0] === 'unmute' || $command['params'][0] === 'unignore') && $command['params'][1] !== '') {
			/**
			 * Removes a player from global mute/ignore list
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// unignore the player
				$rtn = $aseco->client->query('UnIgnore', $target->login);
				if (!$rtn) {
					$msg = new Message('chat.admin', 'message_not_an_ignored_player');
					$msg->addPlaceholders($command['params'][1]);
					$msg->sendChatMessage($login);
				}
				else {
					// check if in global mute/ignore list
					if (($i = array_search($target->login, $aseco->server->mutelist)) !== false) {
						// remove player from list
						$aseco->server->mutelist[$i] = ''; // TODO why not unset()?
					}

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] unignores player {3}', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_unignore');
					$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
					$msg->sendChatMessage();
				}
			}
		}
		else if ($command['params'][0] === 'mutelist' || $command['params'][0] === 'listmutes' || $command['params'][0] === 'ignorelist' || $command['params'][0] === 'listignores') {
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
				$ply = '{#black}'. str_ireplace('$w', '', $player[1])
				       .'$z / {#login}'. $player[0];
				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, 'PluginChatAdmin?Action='. ($pid+4400));  // action id
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.', $ply);
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
			else {  // === 1
				$msg = new Message('chat.admin', 'message_nobody_muted');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'cleanmutes' ||
			/**
			 * Cleans the global mute/ignore list.
			 */

			$command['params'][0] === 'cleanignores') {

			// clean internal and server list
			$aseco->server->mutelist = array();
			$aseco->client->query('CleanIgnoreList');

			// log console message
			$aseco->console('[ChatAdmin] {1} [{2}] cleaned global mute/ignore list!', $logtitle, $login);

			// show chat message
			$msg = new Message('chat.admin', 'message_mutelist_cleaned');
			$msg->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'addadmin' && $command['params'][1] !== '') {
			/**
			 * Adds a new admin.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// check if player not already admin
				if (!$aseco->isAdminByLogin($target->login)) {
					// add the new admin
					$aseco->admin_list['TMLOGIN'][] = $target->login;
					$aseco->writeLists();

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] adds admin [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_add_admin');
					$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->titles['ADMIN'][0],	$target->nickname);
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_already_admin');
					$msg->addPlaceholders($target->login, $aseco->titles['ADMIN'][0]);
					$msg->sendChatMessage($login);
				}
			}
		}
		else if ($command['params'][0] === 'removeadmin' && $command['params'][1] !== '') {
			/**
			 * Removes an admin.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// check if player is indeed admin
				if ($aseco->isAdminByLogin($target->login)) {
					$i = array_search($target->login, $aseco->admin_list['TMLOGIN']);
					$aseco->admin_list['TMLOGIN'][$i] = '';
					$aseco->writeLists();

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] removes admin [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_remove_admin');
					$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->titles['ADMIN'][0],	$target->nickname);
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_no_admin');
					$msg->addPlaceholders($target->login, $aseco->titles['ADMIN'][0]);
					$msg->sendChatMessage($login);
				}
			}
		}
		else if ($command['params'][0] === 'addop' && $command['params'][1] !== '') {
			/**
			 * Adds a new operator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				// check if player not already operator
				if (!$aseco->isOperatorByLogin($target->login)) {
					// add the new operator
					$aseco->operator_list['TMLOGIN'][] = $target->login;
					$aseco->writeLists();

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] adds operator [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_add_admin');
					$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->titles['OPERATOR'][0], $target->nickname);
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_already_admin');
					$msg->addPlaceholders($target->login, $aseco->titles['OPERATOR'][0]);
					$msg->sendChatMessage($login);
				}
			}
		}
		else if ($command['params'][0] === 'removeop' && $command['params'][1] !== '') {
			/**
			 * Removes an operator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1], true)) {
				// check if player is indeed operator
				if ($aseco->isOperatorByLogin($target->login)) {
					$i = array_search($target->login, $aseco->operator_list['TMLOGIN']);
					$aseco->operator_list['TMLOGIN'][$i] = '';
					$aseco->writeLists();

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] removes operator [{3} : {4}]!', $logtitle, $login, $target->login, $aseco->stripStyles($target->nickname, false));

					// show chat message
					$msg = new Message('chat.admin', 'message_remove_admin');
					$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->titles['OPERATOR'][0],	$target->nickname);
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_no_admin');
					$msg->addPlaceholders($target->login, $aseco->titles['OPERATOR'][0]);
					$msg->sendChatMessage($login);
				}
			}
		}
//		else if ($command['params'][0] === 'listmasters') {
//			/**
//			 * Displays the masteradmins list.
//			 */
//
//			$admin->playerlist = array();
//			$admin->msgs = array();
//
//			$head = 'Current MasterAdmins:';
//			$msg = array();
//			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//			$pid = 1;
//			$lines = 0;
//			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
//			foreach ($aseco->masteradmin_list['TMLOGIN'] as $player) {
//				// skip any LAN logins
//				if ($player !== '' && !$aseco->isLANLogin($player)) {
//					$plarr = array();
//					$plarr['login'] = $player;
//					$admin->playerlist[] = $plarr;
//
//					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
//					               '{#black}'. $aseco->server->players->getPlayerNickname($player)
//					               .'$z / {#login}'. $player);
//					$pid++;
//					if (++$lines > 14) {
//						$admin->msgs[] = $msg;
//						$lines = 0;
//						$msg = array();
//						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//					}
//				}
//			}
//
//			// add if last batch exists
//			if (count($msg) > 1) {
//				$admin->msgs[] = $msg;
//			}
//
//			// display ManiaLink message
//			if (count($admin->msgs) > 1) {
//				$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
//			}
//			else {  // === 1
//				$msg = new Message('chat.admin', 'message_no_masteradmins_found');
//				$msg->sendChatMessage($login);
//			}
//		}
//		else if ($command['params'][0] === 'listadmins') {
//			/**
//			 * Displays the admins list.
//			 */
//
//			if (empty($aseco->admin_list['TMLOGIN'])) {
//				$msg = new Message('chat.admin', 'message_no_admins_found');
//				$msg->sendChatMessage($login);
//				return;
//			}
//
//			$admin->playerlist = array();
//			$admin->msgs = array();
//
//			$head = 'Current Admins:';
//			$msg = array();
//			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//			$pid = 1;
//			$lines = 0;
//			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
//			foreach ($aseco->admin_list['TMLOGIN'] as $player) {
//				if ($player !== '') {
//					$plarr = array();
//					$plarr['login'] = $player;
//					$admin->playerlist[] = $plarr;
//
//					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
//					               '{#black}'. $aseco->server->players->getPlayerNickname($player)
//					               .'$z / {#login}'. $player);
//					$pid++;
//					if (++$lines > 14) {
//						$admin->msgs[] = $msg;
//						$lines = 0;
//						$msg = array();
//						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//					}
//				}
//			}
//
//			// add if last batch exists
//			if (count($msg) > 1) {
//				$admin->msgs[] = $msg;
//			}
//
//			// display ManiaLink message
//			$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
//		}
//		else if ($command['params'][0] === 'listops') {
//			/**
//			 * Displays the operators list.
//			 */
//
//			if (empty($aseco->operator_list['TMLOGIN'])) {
//				$msg = new Message('chat.admin', 'message_no_operators_found');
//				$msg->sendChatMessage($login);
//				return;
//			}
//
//			$admin->playerlist = array();
//			$admin->msgs = array();
//
//			$head = 'Current Operators:';
//			$msg = array();
//			$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//			$pid = 1;
//			$lines = 0;
//			$admin->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Solo'));
//			foreach ($aseco->operator_list['TMLOGIN'] as $player) {
//				if ($player !== '') {
//					$plarr = array();
//					$plarr['login'] = $player;
//					$admin->playerlist[] = $plarr;
//
//					$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
//					               '{#black}'. $aseco->server->players->getPlayerNickname($player)
//					               .'$z / {#login}'. $player);
//					$pid++;
//					if (++$lines > 14) {
//						$admin->msgs[] = $msg;
//						$lines = 0;
//						$msg = array();
//						$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
//					}
//				}
//			}
//
//			// add if last batch exists
//			if (count($msg) > 1) {
//				$admin->msgs[] = $msg;
//			}
//
//			// display ManiaLink message
//			$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
//		}
		else if ($command['params'][0] === 'adminability') {
			/**
			 * Show/change an admin ability
			 */

			// check for ability parameter
			if ($command['params'][1] !== '') {
				// map to uppercase before checking list
				$ability = strtoupper($command['params'][1]);

				// check for valid ability
				if (isset($aseco->admin_abilities[$ability])) {
					if (isset($command['params'][2]) && $command['params'][2] !== '') {
						// update ability
						if (strtoupper($command['params'][2]) === 'ON') {
							$aseco->admin_abilities[$ability][0] = true;
							$aseco->writeLists();

							// log console message
							$aseco->console('[ChatAdmin] {1} [{2}] set new Admin ability: {3} ON', $logtitle, $login, strtolower($ability));
						}
						else if (strtoupper($command['params'][2]) === 'OFF') {
							$aseco->admin_abilities[$ability][0] = false;
							$aseco->writeLists();

							// log console message
							$aseco->console('[ChatAdmin] {1} [{2}] set new Admin ability: {3} OFF', $logtitle, $login, strtolower($ability));
						}  // else ignore bogus parameter
					}
					// show current/new ability message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#admin}ability {#highlite}{2}{#admin} is: {#highlite}{3}',
						$aseco->titles['ADMIN'][0],
						strtolower($ability),
						($aseco->admin_abilities[$ability][0] ? 'ON' : 'OFF')
						);
					$aseco->sendChatMessage($message, $login);
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}No ability {#highlite}$i {1}{#error} known!',
						$command['params'][1]
					);
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				$message = '{#server}» {#error}No ability specified - see {#highlite}$i /admin helpall{#error} and {#highlite}$i /admin listabilities{#error}!';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if ($command['params'][0] === 'opability') {
			/**
			 * Show/change an operator ability
			 */

			// check for ability parameter
			if ($command['params'][1] !== '') {
				// map to uppercase before checking list
				$ability = strtoupper($command['params'][1]);

				// check for valid ability
				if (isset($aseco->operator_abilities[$ability])) {
					if (isset($command['params'][2]) && $command['params'][2] !== '') {
						// update ability
						if (strtoupper($command['params'][2]) === 'ON') {
							$aseco->operator_abilities[$ability][0] = true;
							$aseco->writeLists();

							// log console message
							$aseco->console('[ChatAdmin] {1} [{2}] set new Operator ability: {3} ON', $logtitle, $login, strtolower($ability));
						}
						else if (strtoupper($command['params'][2]) === 'OFF') {
							$aseco->operator_abilities[$ability][0] = false;
							$aseco->writeLists();

							// log console message
							$aseco->console('[ChatAdmin] {1} [{2}] set new Operator ability: {3} OFF', $logtitle, $login, strtolower($ability));
						}  // else ignore bogus parameter
					}
					// show current/new ability message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#admin}ability {#highlite}{2}{#admin} is: {#highlite}{3}',
						$aseco->titles['OPERATOR'][0],
						strtolower($ability),
						($aseco->operator_abilities[$ability][0] ? 'ON' : 'OFF')
					);
					$aseco->sendChatMessage($message, $login);
				}
				else {
					$message = $aseco->formatText('{#server}» {#error}No ability {#highlite}$i {1}{#error} known!',
						$command['params'][1]
					);
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				$message = '{#server}» {#error}No ability specified - see {#highlite}$i /admin helpall{#error} and {#highlite}$i /admin listabilities{#error}!';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if ($command['params'][0] === 'listabilities') {
			/**
			 * Displays Admin and Operator abilities
			 * Isn't this incomplete? //Askuri
			 */

			$master = false;
			if ($aseco->isMasterAdminByLogin($login)) {
				if ($command['params'][1] === '') {
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
						$msg = new Message('chat.admin', 'message_invalid_admin_tier');
						$msg->addPlaceholders($command['params'][1]);
						$msg->sendChatMessage($login);
						return;
					}
				}
			}
			else if ($aseco->isAdminByLogin($login)) {
				$abilities = $aseco->admin_abilities;
				$title = 'Admin';
			}
			else {  // isOperator
				$abilities = $aseco->operator_abilities;
				$title = 'Operator';
			}

			// compile current ability listing
			$header = 'Current '. $title .' abilities:';
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
			$help[] = array('', 'See {#black}/admin helpall$g for available /admin commands');


			// Setup settings for Window
			$settings_styles = array(
				'icon'			=> 'Icons128x128_1,ProfileAdvanced',
				'textcolors'		=> array('FF5F', 'FFFF'),
			);
			$settings_columns = array(
				'columns'		=> 2,
				'widths'		=> array(25, 75),
				'textcolors'		=> array('FF5F', 'FFFF'),
				'heading'		=> array('Function', 'Description'),
			);
			$settings_content = array(
				'title'			=> $header,
				'data'			=> $help,
				'about'			=> 'CHAT ADMIN/'. $this->getVersion(),
				'mode'			=> 'columns',
			);

			$window = new Window();
			$window->setStyles($settings_styles);
			$window->setColumns($settings_columns);
			$window->setContent($settings_content);
			$window->send($aseco->server->players->getPlayerByLogin($login), 0, false);
		}
		else if ($command['params'][0] === 'writeabilities') {
			/**
			 * Saves the admins/operators/abilities list to adminops.xml (default).
			 */

			// write admins/operators file
			$filename = $aseco->settings['adminops_file'];
			if (!$aseco->writeLists()) {
				$message = '{#server}» {#error}Error writing {#highlite}$i '. $filename .' {#error}!';
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] wrote '. $filename .'!', $logtitle, $login);

				$message = '{#server}» {#highlite}'. $filename .' {#admin}written';
			}
			// show chat message
			$aseco->sendChatMessage($message, $login);
		}
		else if ($command['params'][0] === 'readabilities') {
			/**
			 * Loads the admins/operators/abilities list from adminops.xml (default).
			 */

			// read admins/operators file
			$filename = $aseco->settings['adminops_file'];
			if (!$aseco->readLists()) {
				$message = '{#server}» {#highlite}'. $filename .' {#error}not found, or error reading!';
			}
			else {
				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] read '. $filename .'!', $logtitle, $login);

				$message = '{#server}» {#highlite}'. $filename .' {#admin}read';
			}
			// show chat message
			$aseco->sendChatMessage($message, $login);
		}
		else if ($command['params'][0] === 'wall' ||
			/**
			 * Display message in pop-up to all players
			 */

		          $command['params'][0] === 'mta') {

			// check for non-empty message
			if ($arglist[1] !== '') {
				$header = '{#black}'. $chattitle .' '. $admin->nickname .'$z :';
				// insure window doesn't become too wide
				$message = wordwrap('{#welcome}'. $arglist[1], 40, LF .'{#welcome}');
				$message = explode(LF, $aseco->formatColors($message));
				foreach ($message as &$line)
					$line = array($line);

				// display ManiaLink message to all players
				foreach ($aseco->server->players->player_list as $target) {
					$aseco->plugins['PluginManialinks']->display_manialink($target->login, $header, array('Icons64x64_1', 'Inbox'), $message, array(0.8), 'OK');
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] sent wall message: {3}', $logtitle, $login, $arglist[1]);
			}
			else {
				$msg = new Message('chat.admin', 'message_no_message');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'delrec' && $command['params'][1] !== '') {
			/**
			 * Delete `records` and `times` database entries for specific record & sync.
			 */

			if (isset($aseco->plugins['PluginLocalRecords']) === true && isset($aseco->plugins['PluginRasp']) === true) {

				// verify parameter
				$param = $command['params'][1];
				if (is_numeric($param) && $param > 0 && $param <= $aseco->plugins['PluginLocalRecords']->records->count()) {
					$param = ltrim($param, '0');
					$param--;

					// Get record info
					$record = $aseco->plugins['PluginLocalRecords']->records->getRecord($param);
					$pid = $aseco->server->players->getPlayerIdByLogin($record->player->login);

					// Remove times before record
					$aseco->plugins['PluginRasp']->deleteTime($aseco->server->maps->current->id, $pid);

					$aseco->plugins['PluginLocalRecords']->removeRecord($aseco, $aseco->server->maps->current->id, $pid, $param);
					$param++;

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] removed record {3} by [{4}]!', $logtitle, $login, $param, $record->player->login);

					// show chat message
					$msg = new Message('chat.admin', 'message_record_removed');
					$msg->addPlaceholders($chattitle, $admin->nickname, $param, $aseco->stripStyles($record->player->nickname));
					$msg->sendChatMessage();
				}
				else {
					$msg = new Message('chat.admin', 'message_invalid_record');
					$msg->addPlaceholders($param);
					$msg->sendChatMessage($login);
				}
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_activate_localrecords');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'prunerecs' && $command['params'][1] !== '') {
			/**
			 * Prune `records` and `times` database entries for specific map.
			 */

			// verify parameter
			$param = $command['params'][1];
			if (is_numeric($param) && $param >= 0) {
				if (empty($admin->maplist)) {
					$msg = new Message('plugin.rasp', 'list_help');
					$msg->sendChatMessage($login);
					return;
				}
				// find map by given #
				$jid = ltrim($param, '0');
				$jid--;
				if (array_key_exists($jid, $admin->maplist)) {
					$uid = $admin->maplist[$jid]['uid'];
					$name = $aseco->stripStyles($admin->maplist[$jid]['name']);
					$map = $aseco->server->maps->getMapByUid($uid);

					if ($map->id > 0) {
						// delete the `records` and `times`
						$query = 'DELETE FROM `%prefix%records` WHERE `MapId` = '. $map->id .';';
						$aseco->db->query($query);
						$query = 'DELETE FROM `%prefix%times` WHERE `MapId` = '. $map->id .';';
						$aseco->db->query($query);

						// log console message
						$aseco->console('[ChatAdmin] {1} [{2}] pruned records/times for map {3} !', $logtitle, $login, $aseco->stripStyles($name, false));

						// show chat message
						$msg = new Message('chat.admin', 'message_records_on_map_deleted');
						$msg->addPlaceholders($name);
						$msg->sendChatMessage($login);
					}
					else {
						$msg = new Message('chat.admin', 'message_cant_find_mapid');
						$msg->addPlaceholders($name, $uid);
						$msg->sendChatMessage($login);
					}
				}
				else {
					$msg = new Message('plugin.rasp', 'jukebox_notfound');
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('plugin.rasp', 'jukebox_help');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'amdl') {
			/**
			 * Shows or sets AllowMapDownload status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param === 'on' || $param === 'off') {
				$enabled = ($param === 'on');
				$aseco->client->query('AllowMapDownload', $enabled);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set AllowMapDownload {3} !', $logtitle, $login, ($enabled ? 'ON' : 'OFF'));

				// show chat message
				$msg = new Message('chat.admin', 'message_set_allowmapdownload');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
			else {
				$enabled = $aseco->client->query('IsMapDownloadAllowed');

				// show chat message
				$msg = new Message('chat.admin', 'message_get_allowmapdownload');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'autotime') {
			/**
			 * Shows or sets Auto TimeLimit status.
			 */

			// check for autotime plugin
			if ( isset($aseco->plugins['PluginAutotime']) ) {
				$param = strtolower($command['params'][1]);
				if ($param === 'on' || $param === 'off') {

					// from plugin.autotime.php
					$aseco->plugins['PluginAutotime']->active = ($param === 'on');

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] set Auto TimeLimit {3} !', $logtitle, $login, ($aseco->plugins['PluginAutotime']->active ? 'ON' : 'OFF'));

					// show chat message
					$message = ''. ($aseco->plugins['PluginAutotime']->active ? 'Enabled' : 'Disabled');
					$aseco->sendChatMessage($message, $login);

					$msg = new Message('chat.admin', 'message_set_autotimelimit');
					$msg->addPlaceholders($aseco->plugins['PluginAutotime']->active ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
					$msg->sendChatMessage($login);
				}
				else {
					// show chat message
					$msg = new Message('chat.admin', 'message_get_autotimelimit');
					$msg->addPlaceholders($aseco->plugins['PluginAutotime']->active ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
					$msg->sendChatMessage($login);
				}
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_activate_autotime');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'disablerespawn') {
			/**
			 * Shows or sets DisableRespawn status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param === 'on' || $param === 'off') {
				$enabled = ($param === 'on');
				$aseco->client->query('SetDisableRespawn', $enabled);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set DisableRespawn {3} !', $logtitle, $login, ($enabled ? 'ON' : 'OFF'));

				// show chat message
				$msg = new Message('chat.admin', 'message_set_disablerespawn');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
			else {
				$enabled = $aseco->client->query('GetDisableRespawn');

				// show chat message
				$msg = new Message('chat.admin', 'message_get_disablerespawn');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'forceshowopp') {
			/**
			 * Shows or sets ForceShowAllOpponents status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param === 'all' || $param === 'off') {
				$enabled = ($param === 'all' ? 1 : 0);
				$aseco->client->query('SetForceShowAllOpponents', $enabled);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set ForceShowAllOpponents {3} !', $logtitle, $login, ($enabled ? 'ALL' : 'OFF'));

				// show chat message
				$msg = new Message('chat.admin', 'message_set_forceshowopp');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
			else if (is_numeric($param) && $param > 1) {
				$enabled = intval($param);
				$aseco->client->query('SetForceShowAllOpponents', $enabled);

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set ForceShowAllOpponents to {3} !', $logtitle, $login, $enabled);

				// show chat message
				$msg = new Message('chat.admin', 'message_set_forceshowopp');
				$msg->addPlaceholders($param);
				$msg->sendChatMessage($login);
			}
			else {
				$enabled = $aseco->client->query('GetForceShowAllOpponents');
				$enabled = $enabled['CurrentValue'];

				// show chat message
				$message = '{#server}» {#admin}ForceShowAllOpponents is set to: {#highlite}'. ($enabled !== 0 ? ($enabled > 1 ? $enabled : 'All') : 'Off');
				$aseco->sendChatMessage($message, $login);

				$msg = new Message('chat.admin', 'message_get_forceshowopp');
				$msg->addPlaceholders($enabled ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'scorepanel') {
			/**
			 * Shows or sets Automatic ScorePanel status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param === 'on' || $param === 'off') {
				$aseco->plugins['PluginManialinks']->auto_scorepanel = ($param === 'on');

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set Automatic ScorePanel {3} !', $logtitle, $login, ($aseco->plugins['PluginManialinks']->auto_scorepanel ? 'ON' : 'OFF'));

				// show chat message
				$msg = new Message('chat.admin', 'message_set_autoscorepanel');
				$msg->addPlaceholders($aseco->plugins['PluginManialinks']->auto_scorepanel ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_get_autoscorepanel');
				$msg->addPlaceholders($aseco->plugins['PluginManialinks']->auto_scorepanel ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'roundsfinish') {
			/**
			 * Shows or sets Rounds Finishpanel status.
			 */

			$param = strtolower($command['params'][1]);
			if ($param === 'on' || $param === 'off') {
				$aseco->plugins['PluginManialinks']->rounds_finishpanel = ($param === 'on');

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] set Rounds Finishpanel {3} !', $logtitle, $login, ($aseco->plugins['PluginManialinks']->rounds_finishpanel ? 'ON' : 'OFF'));

				// show chat message
				$message = ''. ($aseco->plugins['PluginManialinks']->rounds_finishpanel ? 'Enabled' : 'Disabled');
				$aseco->sendChatMessage($message);

				$msg = new Message('chat.admin', 'message_set_roundsfinishpanel');
				$msg->addPlaceholders($aseco->plugins['PluginManialinks']->rounds_finishpanel ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_get_roundsfinishpanel');
				$msg->addPlaceholders($aseco->plugins['PluginManialinks']->rounds_finishpanel ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'forceteam' && $command['params'][1] !== '') {
			/**
			 * Forces a player into Blue or Red team.
			 */

			// check for Team mode
			if ($aseco->server->gameinfo->mode === Gameinfo::TEAM) {
				// get player information
				if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
					// get player's team
					$info = $aseco->client->query('GetPlayerInfo', $target->login);
					// check for new team
					if (isset($command['params'][2]) && $command['params'][2] !== '') {
						$team = strtolower($command['params'][2]);

						if (strpos('blue', $team) === 0) {
							if ($info['TeamId'] !== 0) {
								// set player to Blue team
								$aseco->client->query('ForcePlayerTeam', $target->login, 0);

								// log console message
								$aseco->console('[ChatAdmin] {1} [{2}] forces {3} into Blue team!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

								// show chat message
								$msg = new Message('chat.admin', 'message_forceteam_blue');
								$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
								$msg->sendChatMessage();
							}
							else {
								$msg = new Message('chat.admin', 'message_forceteam_already_blue');
								$msg->sendChatMessage($login);
							}

						}
						else if (strpos('red', $team) === 0) {
							if ($info['TeamId'] !== 1) {
								// set player to Red team
								$aseco->client->query('ForcePlayerTeam', $target->login, 1);

								// log console message
								$aseco->console('[ChatAdmin] {1} [{2}] forces {3} into Red team!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

								// show chat message
								$msg = new Message('chat.admin', 'message_forceteam_red');
								$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
								$msg->sendChatMessage();
							}
							else {
								$msg = new Message('chat.admin', 'message_forceteam_already_red');
								$msg->sendChatMessage($login);
							}

						}
						else {
							$msg = new Message('chat.admin', 'message_invalid_team');
							$msg->addPlaceholders($team);
							$msg->sendChatMessage($login);
						}
					}
					else {
						// show current team
						$message = '{#server}» {#admin}Player {#highlite}'.
						           $aseco->stripStyles($target->nickname) .'{#admin} is in '.
						           ($info['TeamId'] === 0 ? '$00fBlue' : '$f00Red') .
						           '{#admin} team';
						$aseco->sendChatMessage($message, $login);
					}
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_only_in_teammode');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'forcespec' && $command['params'][1] !== '') {
			/**
			 * Forces player into free camera spectator.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				if (!$target->is_spectator) {
					try {
						// force player into free spectator
						$aseco->client->query('ForceSpectator', $target->login, 1);

						// allow spectator to switch back to player
						$aseco->client->query('ForceSpectator', $target->login, 0);

						try {
							// force free camera mode on spectator
							$aseco->client->addCall('ForceSpectatorTarget', $target->login, '', 2);

							try {
								// free up player slot
								$aseco->client->addCall('SpectatorReleasePlayerSlot', $target->login);

								// log console message
								$aseco->console('[ChatAdmin] {1} [{2}] forces player {3} into spectator!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

								// show chat message
								$msg = new Message('chat.admin', 'message_forcespec');
								$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
								$msg->sendChatMessage();
							}
							catch (Exception $exception) {
								$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SpectatorReleasePlayerSlot');
							}
						}
						catch (Exception $exception) {
							$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectatorTarget');
						}
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectator');
					}
				}
				else {
					$msg = new Message('chat.admin', 'message_is_already_spec');
					$msg->addPlaceholders($aseco->stripStyles($target->nickname));
					$msg->sendChatMessage($login);
				}
			}
		}
		else if ($command['params'][0] === 'specfree' && $command['params'][1] !== '') {
			/**
			 * Forces a spectator into free camera mode.
			 */

			// get player information
			if ($target = $aseco->server->players->getPlayerParam($admin, $command['params'][1])) {
				if ($target->is_spectator) {
					try {
						// force free camera mode on spectator
						$aseco->client->query('ForceSpectatorTarget', $target->login, '', 2);

						// log console message
						$aseco->console('[ChatAdmin] {1} [{2}] forces spectator free mode on {3}!', $logtitle, $login, $aseco->stripStyles($target->nickname, false));

						// show chat message
						$msg = new Message('chat.admin', 'message_force_specfree');
						$msg->addPlaceholders($chattitle, $admin->nickname, str_ireplace('$w', '', $target->nickname));
						$msg->sendChatMessage();
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectatorTarget');
					}
				}
				else {
					$msg = new Message('chat.admin', 'message_is_not_spec');
					$msg->addPlaceholders($aseco->stripStyles($target->nickname));
					$msg->sendChatMessage();
				}
			}
		}
		else if ($command['params'][0] === 'panel') {
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
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if ($command['params'][0] === 'admpanel' && $command['params'][1] !== '') {
			/**
			 * Selects default admin panel.
			 */

			if (strtolower($command['params'][1]) === 'off') {
				$aseco->panels['admin'] = '';
				$aseco->settings['admin_panel'] = 'Off';

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] reset default admin panel', $logtitle, $login);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} reset default admin panel',
					$chattitle,
					$admin->nickname
				);
				$aseco->sendChatMessage($message, $login);
			}
			else {
				// added file prefix
				$panel = $command['params'][1];
				if (strtolower(substr($command['params'][1], 0, 5)) !== 'admin') {
					$panel = 'Admin'. $panel;
				}
				$panel_file = 'panels/'. $panel .'.xml';

				// load default panel
				if ($panel = @file_get_contents($panel_file)) {
					$aseco->panels['admin'] = $panel;

					// log console message
					$aseco->console('[ChatAdmin] {1} [{2}] selects default admin panel [{3}]', $logtitle, $login, $command['params'][1]);

					// show chat message
					$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default admin panel {#highlite}{3}',
						$chattitle,
						$admin->nickname,
						$command['params'][1]
					);
					$aseco->sendChatMessage($message, $login);
				}
				else {
					// Could not read XML file
					$message = '{#server}» {#error}No valid admin panel file, use {#highlite}$i /admin panel list {#error}!';
					$aseco->sendChatMessage($message, $login);
				}
			}
		}
		else if ($command['params'][0] === 'panelbg' && $command['params'][1] !== '') {
			/**
			 * Selects default panel background.
			 */

			// added file prefix
			$panel = $command['params'][1];
			if (strtolower(substr($command['params'][1], 0, 7)) !== 'panelbg') {
				$panel = 'PanelBG'. $panel;
			}
			$panelbg_file = 'panels/'. $panel .'.xml';

			// load default background
			if (($panelbg = $aseco->parser->xmlToArray($panelbg_file, true, true)) && isset($panelbg['PANEL']['BACKGROUND'][0])) {
				$aseco->panelbg = $panelbg['PANEL']['BACKGROUND'][0];

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] selects default panel background [{3}]', $logtitle, $login, $command['params'][1]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} selects default panel background {#highlite}{3}',
					$chattitle,
					$admin->nickname,
					$command['params'][1]
				);
				$aseco->sendChatMessage($message);
			}
			else {
				// Could not read/parse XML file
				$message = '{#server}» {#error}No valid background file, use {#highlite}$i /panelbg list {#error}!';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if ($command['params'][0] === 'planets') {
			/**
			 * Shows server's planets amount.
			 */

			// show chat message
			$msg = new Message('chat.admin', 'show_planets');
			$msg->addPlaceholders($aseco->server->name, $aseco->server->amount_planets);
			$msg->sendChatMessage($login);

		}
		else if ($command['params'][0] === 'pay') {
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
				$msg = new Message('chat.admin', 'message_activate_donate');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'relays') {
			/**
			 * Displays relays list or shows relay master.
			 */

			if ($aseco->server->isrelay) {
				// show chat message
				$msg = new Message('chat.admin', 'relaymaster');
				$msg->addPlaceholders($aseco->server->relaymaster['Login'],	$aseco->server->relaymaster['NickName']);
				$msg->sendChatMessage($login);
			}
			else {
				if (empty($aseco->server->relay_list)) {
					// show chat message
					$msg = new Message('chat.admin', 'no_relays');
					$msg->sendChatMessage($login);
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
		else if ($command['params'][0] === 'server') {
			/**
			 * Shows server's detailed settings.
			 */

			// get all server settings in one go
			$version = $aseco->client->query('GetVersion');
			$info = $aseco->client->query('GetSystemInfo');
			$planets = $aseco->client->query('GetServerPlanets');
			$ladderlim = $aseco->client->query('GetLadderServerLimits');
			$options = $aseco->client->query('GetServerOptions');
			$gameinfo = $aseco->client->query('GetCurrentGameInfo');
			$network = $aseco->client->query('GetNetworkStats');
			$callvotes = $aseco->client->query('GetCallVoteRatios');

			// compile settings overview
			$head = 'System info for: '. $options['Name'];
			$admin->msgs = array();
			$admin->msgs[0] = array(1, $head, array(1.1, 0.6, 0.5), array('Icons128x32_1', 'Settings', 0.01));
			$stats = array();

			$stats[] = array('{#black}GetVersion:', '');
			foreach ($version as $key => $val) {
				$stats[] = array($key, '{#black}'. $val);
			}

			$stats[] = array();
			$stats[] = array('{#black}GetSystemInfo:', '');
			foreach ($info as $key => $val) {
				$stats[] = array($key, '{#black}'. $val);
			}

			$stats[] = array();
			$stats[] = array('Planets', '{#black}'. $planets);
			if ($aseco->server->isrelay) {
				$stats[] = array('Relays', '{#black}'. $aseco->server->relaymaster['Login']);
			}
			else {
				$stats[] = array('Master to', '{#black}'. count($aseco->server->relay_list) .
				                 ' $grelay'. (count($aseco->server->relay_list) === 1 ? '' : 's'));
			}
			$stats[] = array();

			$admin->msgs[] = $stats;
			$stats = array();

			$stats[] = array('{#black}GetServerOptions:', '');
			foreach ($options as $key => $val) {
				// show only Current values, not Next ones
				if ($key !== 'Name' && $key !== 'Comment' && substr($key, 0, 4) !== 'Next') {
					if (is_bool($val)) {
						$stats[] = array($key, '{#black}'. $aseco->bool2string($val));
					}
					else {
						$stats[] = array($key, '{#black}'. $val);
					}
				}
			}

			$admin->msgs[] = $stats;
			$stats = array();

			$lines = 0;
			$stats[] = array('{#black}GetCurrentGameInfo:', '');
			foreach ($gameinfo as $key => $val) {
				if (is_bool($val)) {
					$stats[] = array($key, '{#black}'. $aseco->bool2string($val));
				}
				else {
					if ($key === 'GameMode') {
						$stats[] = array($key, '{#black}'. $val .'$g  ('. str_replace('_', ' ', $aseco->server->gameinfo->getModeName()) .')');
					}
					else {
						$stats[] = array($key, '{#black}'. $val);
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
				if ($key !== 'PlayerNetInfos')
					$stats[] = array($key, '{#black}'. $val);
			}

			$stats[] = array();
			$stats[] = array('{#black}GetLadderServerLimits:', '');
			foreach ($ladderlim as $key => $val) {
				$stats[] = array($key, '{#black}'. $val);
			}

			$admin->msgs[] = $stats;
			$stats = array();

			$stats[] = array('{#black}GetCallVoteRatios:', '');
			$stats[] = array('Command', 'Ratio');
			foreach ($callvotes as $entry) {
				$stats[] = array('{#black}'. $entry['Command'], '{#black}'. round($entry['Ratio'], 2));
			}

			$admin->msgs[] = $stats;
			$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);

		}
		else if ($command['params'][0] === 'pm') {
			/**
			 * Send private message to all available admins.
			 */
			global $pmbuf, $pmlen;

			// check for non-empty message
			if ($arglist[1] !== '') {
				// drop oldest pm line if buffer full
				if (count($pmbuf) >= $pmlen) {
					array_shift($pmbuf);
				}
				// append timestamp, admin nickname (but strip wide font) and pm line to history
				$nick = str_ireplace('$w', '', $admin->nickname);
				$pmbuf[] = array(date('H:i:s'), $nick, $arglist[1]);

				// find and pm other masteradmins/admins/operators
				$nicks = '';
				$msg = '{#error}-pm-$g['. $nick .'$z$s$i->{#logina}Admins$g]$i {#interact}'. $arglist[1];
				foreach ($aseco->server->players->player_list as $pl) {
					// check for admin ability
					if ($pl->login !== $login && $aseco->allowAbility($pl, 'pm')) {
						$nicks .= str_ireplace('$w', '', $pl->nickname) .'$z$s$i,';
						$aseco->sendChatMessage($msg, $pl->login);

						// check if player muting is enabled
						if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
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
					$msg = '{#error}-pm-$g['. $nick .'$z$s$i->'. $nicks .']$i {#interact}'. $arglist[1];
					$aseco->sendChatMessage($msg, $login);
				}
				else {
					$msg = new Message('chat.admin', 'message_no_other_admins_on');
					$msg->sendChatMessage($login);
				}

				// check if player muting is enabled
				if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
					// drop oldest message if sender's mute buffer full
					if (count($admin->mutebuf) >= 28) {  // chat window length
						array_shift($admin->mutebuf);
					}
					// append pm line to sender's mute buffer
					$admin->mutebuf[] = $msg;
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_nomsg');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'pmlog') {
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
					$multi = explode(LF, wordwrap($aseco->stripStyles($item[2]), $lnlen+30, LF .'...'));
					foreach ($multi as $line) {
						$line = substr($line, 0, $lnlen+33);  // chop off excessively long words
						$msg[] = array('$z'. ($aseco->settings['chatpmlog_times'] ? '<{#server}'. $item[0] .'$z> ' : '') .
						               '[{#black}'. $item[1] .'$z] '. $line);
						if (++$lines > 14) {
							$admin->msgs[] = $msg;
							$lines = 0;
							$msg = array();
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
				$msg = new Message('chat.admin', 'message_nopmhistory');
				$msg->sendChatMessage($login);
			}
		}
		else if ($command['params'][0] === 'call') {
			/**
			 * Executes direct server call
			 */

			// TODO: Rebuild complete
			$message = '{#server}» {#error}The "/admin call" command is currently not supported!';
			$aseco->sendChatMessage($message, $login);

//			global $method_results;
//
//			// extra admin tier check
//			if (!$aseco->isMasterAdmin($admin)) {
//				$aseco->client->query('ChatSendToLogin', $aseco->formatColors((new Message('common', 'insufficient_rights'))->finish($login)), $login);
//				return;
//			}
//
//			// check parameter(s)
//			if ($command['params'][1] !== '') {
//				if ($command['params'][1] === 'help') {
//					if (isset($command['params'][2]) && $command['params'][2] !== '') {
//						// generate help message for method
//						$method = $command['params'][2];
//						$sign = $aseco->client->addCall('system.methodSignature', $method);
//						$help = $aseco->client->addCall('system.methodHelp', $method);
//						if (!$aseco->client->multiquery()) {
//							trigger_error('['. $aseco->client->getErrorCode() .'] system.method - '. $aseco->client->getErrorMessage(), E_USER_WARNING);
//						}
//						else {
//							$response = $aseco->client->getResponse();
//							if (isset($response[0]['faultCode'])) {
//								$message = '{#server}» {#error}No such method {#highlite}$i '. $method .' {#error}!';
//								$aseco->sendChatMessage($message, $login);
//							}
//							else {
//								$sign = $response[$sign][0][0];
//								$help = $response[$help][0];
//
//								// format signature & help
//								$params = '';
//								for ($i = 1; $i < count($sign); $i++) {
//									$params .= $sign[$i] .', ';
//								}
//								$params = substr($params, 0, strlen($params)-2);  // strip trailing ", "
//								$sign = $sign[0] .' {#black}'. $method .'$g ('. $params .')';
//								$sign = explode(LF, wordwrap($sign, 58, LF));
//								$help = str_replace(array('<i>', '</i>'),
//								                    array('$i', '$i'), $help);
//								$help = explode(LF, wordwrap($help, 58, LF));
//
//								// compile & display help message
//								$header = 'Server Method help for:';
//								$info = array();
//								foreach ($sign as $line) {
//									$info[] = array($line);
//								}
//
//								$info[] = array();
//								foreach ($help as $line) {
//									$info[] = array($line);
//								}
//
//								// display ManiaLink message
//								$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons128x128_1', 'Advanced', 0.02), $info, array(1.05), 'OK');
//							}
//						}
//					}
//					else {
//						// compile & display help message
//						$header = '{#black}/admin call$g executes server method:';
//						$help = array();
//						$help[] = array('...', '{#black}help',
//						                'Displays this help information');
//						$help[] = array('...', '{#black}help Method',
//						                'Displays help for method');
//						$help[] = array('...', '{#black}list',
//						                'Lists all available methods');
//						$help[] = array('...', '{#black}Method {params}',
//						                'Executes method & displays result');
//
//						// display ManiaLink message
//						$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.0, 0.05, 0.35, 0.6), 'OK');
//					}
//
//				}
//				else if ($command['params'][1] === 'list') {
//					// get list of methods
//					$aseco->client->query('system.listMethods');
//					$methods = $aseco->client->getResponse();
//					$admin->msgs = array();
//
//					$head = 'Available Methods on this Server:';
//					$msg = array();
//					$msg[] = array('Id', 'Method');
//					$mid = 1;
//					$lines = 0;
//					$admin->msgs[0] = array(1, $head, array(0.9, 0.15, 0.75), array('Icons128x128_1', 'Advanced', 0.02));
//					foreach ($methods as $method) {
//						$msg[] = array(str_pad($mid, 2, '0', STR_PAD_LEFT) .'.',
//						               '{#black}'. $method);
//						$mid++;
//						if (++$lines > 14) {
//							$admin->msgs[] = $msg;
//							$lines = 0;
//							$msg = array();
//							$msg[] = array('Id', 'Method');
//						}
//					}
//
//					// add if last batch exists
//					if (count($msg) > 1) {
//						$admin->msgs[] = $msg;
//					}
//
//					// display ManiaLink message
//					$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
//
//				}
//				else {
//					// server method
//					$method = $command['params'][1];
//
//					// collect parameters with correct types
//					$args = array();
//					$multistr = '';
//					$in_multi = false;
//					for ($i = 2; $i < count($command['params']); $i++) {
//						if (!$in_multi && strtolower($command['params'][$i]) === 'true') {
//							$args[] = true;
//						}
//						else if (!$in_multi && strtolower($command['params'][$i]) === 'false') {
//							$args[] = false;
//						}
//						else if (!$in_multi && is_numeric($command['params'][$i])) {
//							$args[] = intval($command['params'][$i]);
//						}
//						else {
//							// check for multi-word strings
//							if ($in_multi) {
//								if (substr($command['params'][$i], -1) === '"') {
//									$args[] = $multistr .' '. substr($command['params'][$i], 0, -1);
//									$multistr = '';
//									$in_multi = false;
//								}
//								else {
//									$multistr .= ' '. $command['params'][$i];
//								}
//							}
//							else {
//								if (substr($command['params'][$i], 0, 1) === '"') {
//									$multistr = substr($command['params'][$i], 1);
//									$in_multi = true;
//								}
//								else {
//									$args[] = $command['params'][$i];
//								}
//							}
//						}
//					}
//
//					// execute method
//					switch (count($args)) {
//						case 0: $res = $aseco->client->query($method);
//						        break;
//						case 1: $res = $aseco->client->query($method, $args[0]);
//						        break;
//						case 2: $res = $aseco->client->query($method, $args[0], $args[1]);
//						        break;
//						case 3: $res = $aseco->client->query($method, $args[0], $args[1], $args[2]);
//						        break;
//						case 4: $res = $aseco->client->query($method, $args[0], $args[1], $args[2], $args[3]);
//						        break;
//						case 5: $res = $aseco->client->query($method, $args[0], $args[1], $args[2], $args[3], $args[4]);
//						        break;
//					}
//
//					// process result
//					if ($res) {
//						$res = $aseco->client->getResponse();
//						$admin->msgs = array();
//						$method_results = array();
//						$this->collect_results($method, $res, '');
//
//						// compile & display result message
//						$head = 'Method results for:';
//						$msg = array();
//						$mid = 1;
//						$lines = 0;
//						$admin->msgs[0] = array(1, $head, array(1.1), array('Icons128x128_1', 'Advanced', 0.02));
//						foreach ($method_results as $line) {
//							$msg[] = array($line);
//							$mid++;
//							if (++$lines > 20) {
//								$admin->msgs[] = $msg;
//								$lines = 0;
//								$msg = array();
//							}
//						}
//
//						// add if last batch exists
//						if (!empty($msg)) {
//							$admin->msgs[] = $msg;
//						}
//
//						// display ManiaLink message
//						$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
//					}
//					else {
//						$message = '{#server}» {#error}Method error for {#highlite}$i '. $method .'{#error}: ['. $aseco->client->getErrorCode() .'] '. $aseco->client->getErrorMessage();
//						$aseco->sendChatMessage($message, $login);
//					}
//				}
//			}
//			else {
//				$message = '{#server}» {#error}No call specified - see {#highlite}$i /admin call help{#error} and {#highlite}$i /admin call list{#error}!';
//				$aseco->sendChatMessage($message, $login);
//			}
		}
		else if ($command['params'][0] === 'unlock' && $command['params'][1] !== '') {
			/**
			 * Unlocks admin commands & features.
			 */

			// check unlock password
			if ($aseco->settings['lock_password'] === $command['params'][1]) {
				$admin->unlocked = true;
				$msg = new Message('chat.admin', 'message_password_accepted');
			}
			else {
				$msg = new Message('chat.admin', 'message_invalid_password');
			}
			$msg->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'debug') {
			/**
			 * Toggle debug on/off.
			 */

			$aseco->debug = !$aseco->debug;

			$msg = new Message('chat.admin', 'message_nextmap');
			$msg->addPlaceholders($aseco->debug ? (new Message('chat.admin', 'enabled'))->finish($login) : (new Message('chat.admin', 'disabled'))->finish($login));
			$msg->sendChatMessage($login);
		}
		else if ($command['params'][0] === 'shutdown') {
			/**
			 * Shuts down UASECO.
			 */

			$aseco->console('[ChatAdmin] Shutdown UASECO!');

			// Throw 'shutting down' event
			$aseco->releaseEvent('onShutdown', null);

			// Now skip the handling of Callbacks
			$aseco->shutdown_phase = true;

			exit(0);
		}
		else if ($command['params'][0] === 'shutdownall') {
			/**
			 * Shuts down Server & UASECO.
			 */
			$this->admin_shutdownall($login, $command, $arglist, $logtitle, $chattitle);

		}
		else if ($command['params'][0] === 'uptodate') {
			/**
			 * Checks current version of UASECO.
			 */

			if ( isset($aseco->plugins['PluginUptodate']) ) {
				// from plugin.uptodate.php
				$aseco->releaseChatCommand('/uptodate', $login);
			}
			else {
				// show chat message
				$msg = new Message('chat.admin', 'message_activate_uptodate');
				$msg->sendChatMessage($login);
			}

		}
		else {
			$msg = new Message('chat.admin', 'message_unknown_cmd');
			$msg->addPlaceholders($arglist[0], $arglist[1]);
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_add ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		if (isset($aseco->plugins['PluginRasp']) === true) {
			$source = 'MX';
			$remotelink = 'http://tm.mania-exchange.com/tracks/download/';

			if (count($command['params']) === 1) {
				$msg = new Message('chat.admin', 'message_mxid_required');
				$msg->sendChatMessage($login);
				return;
			}

			if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
				return;
			}

			// try all specified maps
			for ($id = 1; $id < count($command['params']); $id++) {
				// check for valid MX ID
				if (is_numeric($command['params'][$id]) && $command['params'][$id] >= 0) {
					$trkid = ltrim($command['params'][$id], '0');

					try {
						// Start sync GET request
						$params = array(
							'url'			=> $remotelink . $trkid,
							'sync'			=> true,
							'user_agent'		=> USER_AGENT,
							'extra_headers'=> 	"X-ManiaPlanet-ServerLogin:" .$aseco->server->login."\r\n".
										"X-ManiaPlanet-ServerName:" .$aseco->server->name."\r\n".
										"X-ManiaPlanet-Link:" .$aseco->server->link."\r\n",
						);
						$request = $aseco->webrequest->GET($params);
						if (isset($request->response['header']['code']) && $request->response['header']['code'] === 200) {
							$file = $request->response['content'];

							// check for maximum online map size
							if (strlen($file) >= $aseco->server->maps->size_limit) {
								$msg = new Message('chat.admin', 'map_too_large');
								$msg->addPlaceholders(round(strlen($file) / $aseco->server->maps->size_limit));
								$msg->sendChatMessage($login);
								continue;
							}
							$partialdir = $aseco->plugins['PluginRasp']->mxdir . DIRECTORY_SEPARATOR . $trkid .'.Map.gbx';
							$localfile = $aseco->server->mapdir . $partialdir;
							if ($nocasepath = $aseco->fileExistsNoCase($localfile)) {
								if (!unlink($nocasepath)) {
									$msg = new Message('chat.admin', 'message_error_erasing');
									$msg->addPlaceholders($localfile);
									$msg->sendChatMessage($login);
									continue;
								}
							}
							if (!$lfile = @fopen($localfile, 'wb')) {
								$msg = new Message('chat.admin', 'message_error_create_file');
								$msg->addPlaceholders($localfile);
								$msg->sendChatMessage($login);
								continue;
							}
							if (!fwrite($lfile, $file)) {
								$msg = new Message('chat.admin', 'message_error_write_file');
								$msg->addPlaceholders($localfile);
								$msg->sendChatMessage($login);
								fclose($lfile);
								continue;
							}
							fclose($lfile);

							$gbx = $aseco->server->maps->parseMap($localfile);
							if ( !isset($gbx->uid) ) {
								$msg = new Message('chat.admin', 'message_is_no_map');
								$msg->addPlaceholders($source);
								$msg->sendChatMessage($login);
								unlink($localfile);
								continue;
							}

							// Check for map presence on server
							$tmp = $aseco->server->maps->getMapByUid($gbx->uid);
							if ($tmp->uid === $gbx->uid) {
								$msg = new Message('plugin.rasp', 'add_present');
								$msg->sendChatMessage($login);
								unlink($localfile);
								continue;
							}

							// rename ID filename to map's name
							$md5new = md5_file($localfile);
							$response = new MXInfoFetcher('TM2', $gbx->uid, true);
							if ($response !== null && empty($response->error)) {
								$filename = $aseco->slugify($response->name);
							}
							else {
								$filename = $aseco->slugify($gbx->name);
							}
							$partialdir = $aseco->plugins['PluginRasp']->mxdir . DIRECTORY_SEPARATOR . $gbx->envir . DIRECTORY_SEPARATOR . $filename .'_'. $trkid .'.Map.gbx';

							// insure unique filename by incrementing sequence number,
							// if not a duplicate map
							$i = 1;
							$dupl = false;
							while ($nocasepath = $aseco->fileExistsNoCase($aseco->server->mapdir . $partialdir)) {
								$md5old = md5_file($nocasepath);
								if ($md5old === $md5new) {
									$dupl = true;
									$partialdir = str_replace($aseco->server->mapdir, '', $nocasepath);
									break;
								}
								else {
									$partialdir = $aseco->plugins['PluginRasp']->mxdir . DIRECTORY_SEPARATOR . $gbx->environment . DIRECTORY_SEPARATOR . $filename .'_'. $trkid .'-'. $i++ .'.Map.gbx';
								}
							}
							if ($dupl) {
								unlink($localfile);
							}
							else {
								rename($localfile, $aseco->server->mapdir . $partialdir);
							}

							// Check map vs. server settings
							try {
								$aseco->client->query('CheckMapForCurrentServerParams', $partialdir);

								// Permanently add the map to the server list
								try {
									$aseco->client->query('AddMap', $partialdir);
									$mapinfo = $aseco->client->query('GetMapInfo', $partialdir);
									if (!$mapinfo) {
										$message = 'Method [GetMapInfo]: Error getting info on Map ['. $aseco->stripStyles($gbx->name) .']!';
										$aseco->console('[ChatAdmin] '. $message);
										$aseco->sendChatMessage($message, $login);
									}
									else {
										$mapinfo['Name'] = $aseco->stripNewlines($mapinfo['Name']);

										// Create Map object
										$map = new Map($gbx, $mapinfo['FileName']);

										// Add map to the MapList
										$map = $aseco->server->maps->addMapToListByUid($map->uid);

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
										$aseco->console('[ChatAdmin] {1} [{2}] adds map [{3}] from {4}!', $logtitle, $login, $map->name_stripped, $source);

										// show chat message
										$msg = new Message('chat.admin', 'message_map_added');
										$msg->addPlaceholders($chattitle, $admin->nickname, ((isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) ? '& jukeboxes ' : ''), $map->name_stripped, $source);
										$msg->sendChatMessage();

										// Setup next Map
										$aseco->server->maps->next = $aseco->server->maps->getNextMap();

										// throw 'maplist changed' event
										$aseco->releaseEvent('onMapListChanged', array('add', $partialdir));

										// throw 'jukebox changed' event
										if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
											$aseco->releaseEvent('onJukeboxChanged', array('add', $aseco->plugins['PluginRaspJukebox']->jukebox[$map->uid]));
										}
									}
								}
								catch (Exception $exception) {
									$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - AddMap: Map ['. $aseco->stripStyles($gbx->name) .']');
									$message = $aseco->formatText('{#server}» {#error}Could not add Map {#highlite}{1}$Z$S{#error}: {2}!',
										$aseco->stripStyles($gbx->name),
										$exception->getMessage()
									);
									$aseco->sendChatMessage($message, $login);
								}
							}
							catch (Exception $exception) {
								$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - CheckMapForCurrentServerParams: Map ['. $aseco->stripStyles($gbx->name) .']');

								$msg = new Message('chat.admin', 'message_map_not_added_incompatible');
								$msg->addPlaceholders($aseco->stripStyles($gbx->name));
								$msg->sendChatMessage($login);
							}
						}
						else {
							$msg = new Message('chat.admin', 'message_mx_download_error');
							$msg->sendChatMessage($login);
						}
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
					}
				}
				else {
					$msg = new Message('chat.admin', 'message_invalid_mxid');
					$msg->addPlaceholders($command['params'][$id]);
					$msg->sendChatMessage($login);
				}
			}
		}
		else {
			// show chat message
			$msg = new Message('chat.admin', 'message_activate_rasp');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_addthis ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		if (isset($aseco->plugins['PluginRaspJukebox']) === true && isset($aseco->plugins['PluginRasp']) === true) {

			if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
				return;
			}

			// Check for MX /add-ed map
			if ($aseco->plugins['PluginRaspJukebox']->mxplayed) {
				try {
					// Remove map with old path
					$aseco->client->query('RemoveMap', $aseco->plugins['PluginRaspJukebox']->mxplayed);

					// Move the map file
					$mxnew = str_replace($aseco->plugins['PluginRasp']->mxtmpdir, $aseco->plugins['PluginRasp']->mxdir, $aseco->plugins['PluginRaspJukebox']->mxplayed);
					if (!rename($aseco->server->mapdir . $aseco->plugins['PluginRaspJukebox']->mxplayed, $aseco->server->mapdir . $mxnew)) {
						trigger_error('Could not rename MX map '. $aseco->plugins['PluginRaspJukebox']->mxplayed .' to '. $mxnew, E_USER_WARNING);
						return;
					}
					else {
						// Add map with new path
						try {
							$aseco->client->query('AddMap', $mxnew);

							// store new path
							$aseco->server->maps->current->filename = $mxnew;

							// throw 'maplist changed' event
							$aseco->releaseEvent('onMapListChanged', array('rename', $mxnew));
						}
						catch (Exception $exception) {
							$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - AddMap: Map ['. $aseco->stripStyles($aseco->server->maps->current->name) .']');
							$message = $aseco->formatText('{#server}» {#error} Could not add Map {#highlite}{1}$Z$S{#error}: {2}!',
								$aseco->stripStyles($aseco->server->maps->current->name),
								$exception->getMessage()
							);
							$aseco->sendChatMessage($message, $login);
						}
					}
					// disable map removal afterwards
					$aseco->plugins['PluginRaspJukebox']->mxplayed = false;

					// show chat message
					$msg = new Message('chat.admin', 'message_add_permanent');
					$msg->addPlaceholders($chattitle, $admin->nickname, $aseco->stripStyles($aseco->server->maps->current->name));
					$msg->sendChatMessage();
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - RemoveMap: Could not remove temporary added Map!');

					$msg = new Message('chat.admin', 'message_no_temp_remove_map');
					$msg->addPlaceholders($aseco->stripStyles($gbx->name));
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_already_perm_added_map');
				$msg->addPlaceholders($aseco->stripStyles($aseco->server->maps->current->name));
				$msg->sendChatMessage($login);
			}
		}
		else {
			// show chat message
			$msg = new Message('chat.admin', 'message_activate_addthis');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_addlocal ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for local map file
		if ($arglist[1] !== '') {
			$sepchar = substr($aseco->server->mapdir, -1, 1);
			$partialdir = 'Downloaded'. $sepchar . $arglist[1];
			if (!stristr($partialdir, '.Map.gbx')) {
				$partialdir .= '.Map.gbx';
			}
			$localfile = $aseco->server->mapdir . $partialdir;
			if ($nocasepath = $aseco->fileExistsNoCase($localfile)) {
				// check for maximum online map size
				if (filesize($nocasepath) >= $aseco->server->maps->size_limit) {
					$msg = new Message('chat.admin', 'map_too_large');
					$msg->addPlaceholders(round(strlen($file) / $aseco->server->maps->size_limit));
					$msg->sendChatMessage($login);
					return;
				}
				$partialdir = str_replace($aseco->server->mapdir, '', $nocasepath);

				// Check map vs. server settings
				try {
					$aseco->client->query('CheckMapForCurrentServerParams', $partialdir);
					try {
						// Permanently add the map to the server list
						$aseco->client->query('AddMap', $partialdir);
						$mapinfo = $aseco->client->query('GetMapInfo', $partialdir);
						if (!$mapinfo) {
							$message = 'Method [GetMapInfo]: Error getting info on Map ['. $aseco->stripStyles($gbx->name) .']!';
							$aseco->console('[ChatAdmin] '. $message);
							$aseco->sendChatMessage($message, $login);
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
							$aseco->console('[ChatAdmin] {1} [{2}] adds local map {3}!', $logtitle, $login, $aseco->stripStyles($mapinfo['Name'], false));

							// show chat message
							$msg = new Message('chat.admin', 'message_map_added');
							$msg->addPlaceholders($chattitle, $admin->nickname, ((isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) ? '& jukeboxes ' : ''), $aseco->stripStyles($mapinfo['Name']),	$aseco->stripStyles($mapinfo['Author']));
							$msg->sendChatMessage();

							// Add map to the MapList
							$map = $aseco->server->maps->addMapToListByUid($mapinfo['UId']);

							// Setup next Map
							$aseco->server->maps->next = $aseco->server->maps->getNextMap();

							// throw 'maplist changed' event
							$aseco->releaseEvent('onMapListChanged', array('add', $partialdir));

							// throw 'jukebox changed' event
							if (isset($aseco->plugins['PluginRaspJukebox']) && $aseco->plugins['PluginRaspJukebox']->jukebox_adminadd) {
								$aseco->releaseEvent('onJukeboxChanged', array('add', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
							}
						}
					}
					catch (Exception $exception) {
						$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - AddMap: Map ['. $aseco->stripStyles($gbx->name) .']');
						$message = $aseco->formatText('{#server}» {#error}Could not add Map {#highlite}{1}$Z$S{#error}: {2}!',
							$aseco->stripStyles($gbx->name),
							$exception->getMessage()
						);
						$aseco->sendChatMessage($message, $login);
					}
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - CheckMapForCurrentServerParams: Map ['. $aseco->stripStyles($gbx->name) .']');
					$msg = new Message('chat.admin', 'message_map_not_added_incompatible');
					$msg->addPlaceholders($aseco->stripStyles($gbx->name));
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_not_found');
				$msg->addPlaceholders($partialdir);
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('chat.admin', 'message_include_local_file');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_readmaplist ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		$filename = $aseco->settings['default_maplist'];

		// Check for optional alternate filename
		if ($command['params'][1] !== '') {
			$filename = $command['params'][1];
			if (!stristr($filename, '.txt')) {
				$filename .= '.txt';
			}
		}
		if (file_exists($aseco->server->mapdir .'MatchSettings/'. $filename)) {
			try {
				// Get map count
				$cnt = $aseco->client->query('LoadMatchSettings', 'MatchSettings/'. $filename);
				if (!$cnt) {
					$cnt = 0;
				}

				// Log console message
				$aseco->console('[ChatAdmin] {1} [{2}] read map list: {3} ({4} maps)!', $logtitle, $login, $filename, $cnt);

				// Refresh the Maplist
				$aseco->server->maps->readMapList();

				// Throw 'maplist changed' event
				$aseco->releaseEvent('onMapListChanged', array('read', null));

				$msg = new Message('chat.admin', 'message_maplist_read');
				$msg->addPlaceholders($filename, $cnt);
				$msg->sendChatMessage($login);
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - LoadMatchSettings: ['.'MatchSettings/'. $filename .']');

				$msg = new Message('chat.admin', 'message_failed_reading');
				$msg->addPlaceholders('MatchSettings/'. $filename);
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('chat.admin', 'message_failed_reading');
			$msg->addPlaceholders('MatchSettings/'. $filename);
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_shufflemaps ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		if ($aseco->settings['writemaplist_random']) {
			try {
				if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
					return;
				}

				// Get map count
				$cnt = $aseco->client->query('LoadMatchSettings', 'MatchSettings/'. $aseco->settings['default_maplist']);
				if (!$cnt) {
					$cnt = 0;
				}

				// log console message
				$aseco->console('[ChatAdmin] {1} [{2}] shuffled map list: {3} ({4} maps)!', $logtitle, $login, '[MatchSettings/'.$aseco->settings['default_maplist'] .']', $cnt);

				$msg = new Message('chat.admin', 'message_maps_shuffled');
				$msg->addPlaceholders($chattitle, $admin->nickname, $cnt);
				$msg->sendChatMessage($login);
				return;
			}
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - LoadMatchSettings: [MatchSettings/'. $aseco->settings['default_maplist'] .']');

				$msg = new Message('chat.admin', 'message_failed_reading');
				$msg->addPlaceholders('MatchSettings/'. $aseco->settings['default_maplist']);
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('chat.admin', 'message_no_maplist_randomness');
		}

		// Show chat message
		$msg->sendChatMessage($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_players ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		if (!$admin = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		$admin->playerlist = array();
		$admin->msgs = array();

		// Remember players parameter for possible refresh
		$admin->panels['plyparam'] = $command['params'][1];
		$onlineonly = (strtolower($command['params'][1]) === 'live');

		// Get current ignore/ban/black/guest lists
		$ignores = $this->getIgnorelist($aseco);
		$bans = $this->getBanlist($aseco);
		$blacks = $this->getBlacklist($aseco);
		$guests = $this->getGuestlist($aseco);

		// Create new list of online players
		$onlinelist = array();

		try {
			// Get current players on the server (hardlimited to 300)
			$players = $aseco->client->query('GetPlayerList', 300, 0, 1);
			foreach ($players as $pl) {
				// on relay, check for player from master server
				if (!$aseco->server->isrelay || floor($pl['Flags'] / 10000) % 10 === 0) {
					$onlinelist[$pl['Login']] = array(
						'login'	=> $pl['Login'],
						'nick'	=> $pl['NickName'],
						'spec'	=> $pl['SpectatorStatus']
					);
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
					`Nickname`
				FROM `%prefix%players`
				WHERE `Login` LIKE ". $aseco->db->quote('%'. $arglist[1] .'%') ."
				OR `Nickname` LIKE ". $aseco->db->quote('%'. $arglist[1] .'%') ."
				LIMIT 5000;
				";
				$result = $aseco->db->query($query);
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
				$head = ($onlineonly ? 'Online' : 'Known') .' Players On This Server:';
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
					$ply = '{#black}'. str_ireplace('$w', '', $pl['nick']) .'$z / '. ($aseco->isAnyAdminByLogin($pl['login']) ? '{#logina}' : '{#login}' ) . $pl['login'];

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
						$ply = array($ply, 'PluginPlayerInfos?Action='. ($pid+2000));
						if (array_key_exists($lg, $onlinelist)) {
							// determine online operations
							$wrn = array($wrn, 'PluginChatAdmin?Action='. ($pid+2200));
							if (array_key_exists($lg, $ignores)) {
								$ign = array($uig, 'PluginChatAdmin?Action='. ($pid+2600));
							}
							else {
								$ign = array($ign, 'PluginChatAdmin?Action='. ($pid+2400));
							}
							$kck = array($kck, 'PluginChatAdmin?Action='. ($pid+2800));
							if (array_key_exists($lg, $bans)) {
								$ban = array($ubn, 'PluginChatAdmin?Action='. ($pid+3200));
							}
							else {
								$ban = array($ban, 'PluginChatAdmin?Action='. ($pid+3000));
							}
							if (array_key_exists($lg, $blacks)) {
								$blk = array($ubk, 'PluginChatAdmin?Action='. ($pid+3600));
							}
							else {
								$blk = array($blk, 'PluginChatAdmin?Action='. ($pid+3400));
							}
							if (array_key_exists($lg, $guests)) {
								$gst = array($ugt, 'PluginChatAdmin?Action='. ($pid+4000));
							}
							else {
								$gst = array($gst, 'PluginChatAdmin?Action='. ($pid+3800));
							}
							if (!$onlinelist[$lg]['spec']) {
								$spc = array($frc, 'PluginChatAdmin?Action='. ($pid+4200));
							}
						}
						else {
							// determine offline operations
							if (array_key_exists($lg, $ignores)) {
								$ign = array($uig, 'PluginChatAdmin?Action='. ($pid+2600));
							}
							if (array_key_exists($lg, $bans)) {
								$ban = array($ubn, 'PluginChatAdmin?Action='. ($pid+3200));
							}
							if (array_key_exists($lg, $blacks)) {
								$blk = array($ubk, 'PluginChatAdmin?Action='. ($pid+3600));
							}
							else {
								$blk = array($blk, 'PluginChatAdmin?Action='. ($pid+3400));
							}
							if (array_key_exists($lg, $guests)) {
								$gst = array($ugt, 'PluginChatAdmin?Action='. ($pid+4000));
							}
							else {
								$gst = array($gst, 'PluginChatAdmin?Action='. ($pid+3800));
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

					$msg[] = array(
						str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
						$ply,
						$wrn,
						$ign,
						$kck,
						$ban,
						$blk,
						$gst,
						$spc,
					);

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
				else {  // === 1
					$msg = new Message('chat.admin', 'message_no_players_found');
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('chat.admin', 'message_no_players_found');
				$msg->sendChatMessage($login);
			}
		}
		catch (Exception $exception) {
			$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetPlayerList');
			$msg = new Message('chat.admin', 'message_cannot_get_playerlist');
			$msg->sendChatMessage();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function admin_shutdownall ($login, $command, $arglist, $logtitle, $chattitle) {
		global $aseco;

		$msg = new Message('chat.admin', 'message_shutdownall');
		$msg->sendChatMessage();
		try {
			$aseco->console('[ChatAdmin] Shutdown UASECO!');

			// Skip map to run into score
			$aseco->client->query('NextMap');

			// Throw 'shutting down' event
			$aseco->releaseEvent('onShutdown', null);

			// Now skip the handling of Callbacks
			$aseco->shutdown_phase = true;

			$aseco->console('[ChatAdmin] Shutdown dedicated server!');
			$result = $aseco->client->query('StopServer');
			if ($result !== true) {
				sleep(2);
				try {
					$aseco->client->query('QuitGame');
					exit();
				}
				catch (Exception $exception) {
					$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - QuitGame');
					$message = '{#server}» {#error}Quitting the dedicated Server failed!';
					$aseco->sendChatMessage($message, $login);
				}
			}
			exit(0);
		}
		catch (Exception $exception) {
			$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - StopServer');
			$message = '{#server}» {#error}Can not stop the dedicated Server!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getIgnorelist ($aseco) {

		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			try {
				$players = $aseco->client->query('GetIgnoreList', $size, $i);
				if (!empty($players)) {
					foreach ($players as $prow) {
						// fetch nickname for this login
						$lgn = $prow['Login'];
						$nick = $aseco->server->players->getPlayerNickname($lgn);
						if ($nick === false) {
							$nick = $lgn;
						}
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
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetIgnoreList');
				$message = '{#server}» {#error}Can not get the current ignore list from the dedicated Server!';
				$aseco->sendChatMessage($message, $login);

				$done = true;
				break;
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

		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			try {
				$players = $aseco->client->query('GetBanList', $size, $i);
				if (!empty($players)) {
					foreach ($players as $prow) {
						// fetch nickname for this login
						$lgn = $prow['Login'];
						$nick = $aseco->server->players->getPlayerNickname($lgn);
						if ($nick === false) {
							$nick = $lgn;
						}
						$newlist[$lgn] = array(
							$lgn,
							$nick,
							preg_replace('/:\d+/', '', $prow['IPAddress']),		// strip port
						);
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
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetBanList');
				$message = '{#server}» {#error}Can not get the current ban list from the dedicated Server!';
				$aseco->sendChatMessage($message, $login);

				$done = true;
				break;
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

		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			try {
				$players = $aseco->client->query('GetBlackList', $size, $i);
				if (!empty($players)) {
					foreach ($players as $prow) {
						// fetch nickname for this login
						$lgn = $prow['Login'];
						$nick = $aseco->server->players->getPlayerNickname($lgn);
						if ($nick === false) {
							$nick = $lgn;
						}
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
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetBlackList');
				$message = '{#server}» {#error}Can not get the current black list from the dedicated Server!';
				$aseco->sendChatMessage($message, $login);

				$done = true;
				break;
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

		$newlist = array();
		$done = false;
		$size = 300;
		$i = 0;
		while (!$done) {
			try {
				$players = $aseco->client->query('GetGuestList', $size, $i);
				if (!empty($players)) {
					foreach ($players as $prow) {
						// fetch nickname for this login
						$lgn = $prow['Login'];
						$nick = $aseco->server->players->getPlayerNickname($lgn);
						if ($nick === false) {
							$nick = $lgn;
						}
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
			catch (Exception $exception) {
				$aseco->console('[ChatAdmin] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetGuestList');
				$message = '{#server}» {#error}Can not get the current guest list from the dedicated Server!';
				$aseco->sendChatMessage($message, $login);

				$done = true;
				break;
			}
		}
		return $newlist;
	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function collect_results ($key, $val, $indent) {
//		global $method_results;
//
//		if (is_array($val)) {
//			// recursively compile array results
//			$method_results[] = $indent .'*'. $key .' :';
//			foreach ($val as $key2 => $val2) {
//				$this->collect_results($key2, $val2, '   '. $indent);
//			}
//		}
//		else {
//			if (!is_string($val)) {
//				$val = strval($val);
//			}
//
//			// format result key/value pair
//			$val = explode(LF, wordwrap($val, 32, LF . $indent .'      ', true));
//			$firstline = true;
//			foreach ($val as $line) {
//				if ($firstline) {
//					$method_results[] = $indent . $key .' = '. $line;
//				}
//				else {
//					$method_results[] = $line;
//				}
//				$firstline = false;
//			}
//		}
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles ManiaLink admin responses
	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		// leave actions outside 2201 - 5200 to other handlers
		$action = (int) $answer['Action'];
		if ($action < 2201 && $action > 5200 && $action < -8100 && $action > -7901) {
			return;
		}

		// get player & possible parameter
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

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
					if ($aseco->banned_ips[$i] !== '') {
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
