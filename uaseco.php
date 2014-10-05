<?php
/*
 * Projectname: UASECO
 * ~~~~~~~~~~~~~~~~~~~~
 * » ASECO is an abbreviation of "Automatic SErver COntrol", the prefix U at UASECO
 *   stays for "undef", which follows the same naming convention initiated by Xymph
 *   for XAseco.
 *
 *   This project was forked in May 2014 from XAseco2/1.03 release and was mixed
 *   with parts/ideas from MPAseco and ASECO/2.2.0c, for supporting the Trackmania²
 *   Modescript Gamemodes from the Maniaplanet/3+ update.
 *
 *   Visit the official site from this fork at http://www.UASECO.org/
 *
 * » Original project:
 *   Authored & copyright Aug 2011 - May 2013 by Xymph <tm@gamers.org>
 *   Derived from XAseco (formerly ASECO/RASP) by Xymph, Flo and others
 *
 * ----------------------------------------------------------------------------------
 * Requires:	PHP/5.2.1 (or higher), MySQL/5.x (or higher)
 * Author:	undef.de
 * Copyright:	May 2014 - October 2014 by undef.de
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


	// Current project name, version and website
	define('UASECO_NAME',		'UASECO');
	define('UASECO_VERSION',	'1.0.0');
	define('UASECO_BUILD',		'2014-10-05');
	define('UASECO_WEBSITE',	'http://www.UASECO.org/');

	// Setup required official dedicated server build, Api-Version and PHP-Version
	define('MANIAPLANET_BUILD',	'2014-09-10_14_00');
	define('API_VERSION',		'2013-04-16');
	define('MIN_PHP_VERSION',	'5.2.1');

	// Setup misc.
	define('USER_AGENT',		UASECO_NAME .'/'. UASECO_VERSION .' build '. UASECO_BUILD);	// used in includes/core/webaccess.class.php
	define('CRLF',			PHP_EOL);

	if (!defined('LF')) {
		define('LF', "\n");
	}

	// Report all
	error_reporting(-1);

	// Include required classes
	require_once('includes/core/helper.class.php');			// Misc. functions for UASECO, e.g. $aseco->console()... based upon basic.inc.php
	require_once('includes/core/XmlRpc/GbxRemote.php');
	require_once('includes/core/webaccess.class.php');
	require_once('includes/core/xmlparser.class.php');		// Provides an XML parser
	require_once('includes/core/gbxdatafetcher.class.php');		// Provides access to GBX data
	require_once('includes/core/mxinfofetcher.class.php');		// Provides access to ManiaExchange info
	require_once('includes/core/continent.class.php');
	require_once('includes/core/country.class.php');
	require_once('includes/core/database.class.php');
	require_once('includes/core/gameinfo.class.php');		// Required by includes/core/server.class.php
	require_once('includes/core/server.class.php');
	require_once('includes/core/dependence.class.php');		// Required by includes/core/plugin.class.php
	require_once('includes/core/plugin.class.php');
	require_once('includes/core/window.class.php');			// Required by includes/core/windowlist.class.php
	require_once('includes/core/windowlist.class.php');
	require_once('includes/core/player.class.php');
	require_once('includes/core/playerlist.class.php');
	require_once('includes/core/checkpoint.class.php');
	require_once('includes/core/record.class.php');
	require_once('includes/core/recordlist.class.php');
	require_once('includes/core/ranking.class.php');		// Required by includes/core/rankinglist.class.php
	require_once('includes/core/rankinglist.class.php');
	require_once('includes/core/map.class.php');			// Required by includes/core/maplist.class.php
	require_once('includes/core/maplist.class.php');


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class UASECO extends Helper {
	public $debug;
	public $logfile;
	public $client;
	public $parser;
	public $webaccess;
	public $mysqli;
	public $continent;
	public $country;
	public $windows;
	public $server;
	public $registered_events;
	public $registered_chatcmds;
	public $chat_colors;
	public $chat_messages;
	public $plugins;
	public $settings;
	public $titles;
	public $masteradmin_list;
	public $admin_list;
	public $admin_abilities;
	public $operator_list;
	public $operator_abilities;
	public $banned_ips;
	public $uptime;						// UASECO start-up time
	public $startup_phase;					// UASECO start-up phase
	public $shutdown_phase;					// UASECO shutdown phase
	public $warmup_phase;					// warm-up phase
	public $restarting;					// restarting map (true or false)
	public $changing_to_gamemode;
	public $checkpoints;

	private $current_status;				// server status changes
	private $next_second;
	private $next_tenth;
	private $next_quarter;
	private $next_minute;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Initializes the server.
	public function __construct () {

		// Error function, report errors in a regular way.
		$this->logfile['handle'] = false;
		set_error_handler(array($this, 'customErrorHandler'));
		register_shutdown_function(array($this, 'customFatalErrorShutdownHandler'));

		// Setup logfile
		$this->setupLogfile();

		$this->console('###############################################################################');
		$this->console('Initializing UASECO...');

		if ( version_compare(PHP_VERSION, MIN_PHP_VERSION, '<') ) {
			$this->console('[ERROR] UASECO requires min. PHP/'. MIN_PHP_VERSION .' and can not run with current PHP/'. PHP_VERSION .', please update PHP!');
			die();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Runs the server.
	public function run () {

		// Initialize
		$this->debug			= false;
		$this->uptime			= time();
		$this->registered_events	= array();
		$this->registered_chatcmds	= array();
		$this->client			= new GbxRemote();			// includes/core/XmlRpc/GbxRemote.php
		$this->parser			= new XmlParser();
		$this->webaccess		= new Webaccess();
		$this->continent		= new Continent();
		$this->country			= new Country();
		$this->windows			= new WindowList($this);
		$this->server			= new Server('127.0.0.1', 5000, 'SuperAdmin', 'SuperAdmin');
		$this->server->maps		= new MapList();
		$this->server->players		= new PlayerList();
		$this->server->rankings		= new RankingList();
		$this->server->mutelist		= array();
		$this->plugins			= array();
		$this->titles			= array();
		$this->masteradmin_list		= array();
		$this->admin_list		= array();
		$this->admin_abilities		= array();
		$this->operator_list		= array();
		$this->operator_abilities	= array();
		$this->banned_ips		= array();
		$this->startup_phase		= true;
		$this->shutdown_phase		= false;
		$this->warmup_phase		= false;
		$this->restarting		= false;
		$this->changing_to_gamemode	= false;
		$this->current_status		= 0;
		$this->checkpoints		= array();

		// Setup config file
		$config_file = 'config/UASECO.xml';

		// Load new settings, if available
		$this->console('[Config] Load settings [{1}]', $config_file);
		$this->loadSettings($config_file);

		// Load admin/operator/ability lists, if available
		$this->console('[Config] Load admin/ops lists [{1}]', $this->settings['adminops_file']);
		$this->readLists();

		// Load banned IPs list, if available
		$this->console('[Config] Load banned IPs list [{1}]', $this->settings['bannedips_file']);
		$this->readIPs();

		// Connect to Trackmania Dedicated Server
		if (!$this->connectDedicated()) {
			// kill program with an error
			trigger_error('[Dedicated] ...connection could not be established!', E_USER_ERROR);
		}
		// Log status message
		$this->console('[Dedicated] ...connection established successfully!');

		// Clear possible leftover ManiaLinks
		$this->client->query('SendHideManialinkPage');

		// Connect to the database
		$this->displayLoadStatus('Connecting to database...', 0.0);
		if ($this->settings['mask_password'] == true) {
			$this->console("[Database] Try to connect to MySQL server on '{1}' with database '{2}' and password '{3}' (masked password)",
				$this->settings['mysql']['host'],
				$this->settings['mysql']['database'],
				preg_replace('#.#', '*', $this->settings['mysql']['password'])
			);
		}
		else {
			$this->console("[Database] Try to connect to MySQL server on '{1}' with database '{2}' and password '{3}'",
				$this->settings['mysql']['host'],
				$this->settings['mysql']['database'],
				$this->settings['mysql']['password']
			);
		}
		$this->connectDatabase();
		$this->displayLoadStatus('Connection established successfully!', 0.5);

		// Check database structure
		$this->displayLoadStatus('Checking database structure...', 0.6);
		$this->checkDatabaseStructure();
		$this->displayLoadStatus('Structure successfully checked!', 1.0);

		// Load plugins and register chat commands
		$this->console('[Plugin] Loading plugins [config/plugins.xml]');
		$this->loadPlugins();

		// Log admin lock message
		if ($this->settings['lock_password'] != '') {
			if ($this->settings['mask_password'] == true) {
				$this->console('[Config] Locked admin commands and features with password "{1}" (masked password)',
					preg_replace('#.#', '*', $this->settings['lock_password'])
				);
			}
			else {
				$this->console('[Config] Locked admin commands and features with password "{1}"',
					$this->settings['lock_password']
				);
			}
		}

		// Throw 'starting up' event
		$this->releaseEvent('onStartup', null);

		// Synchronize information with server
		$this->serverSync();

		// Make a visual header
		$this->sendHeader();

		// Get current players/servers on the server (hardlimited to 300)
		$playerlist = $this->client->query('GetPlayerList', 300, 0, 2);

		// Update players/relays lists
		if (!empty($playerlist)) {
			foreach ($playerlist as $player) {
				// fake it into thinking it's a connecting player:
				// it gets team & ladder info this way & will also throw an
				// onPlayerConnect event for players (not relays) to all plugins
				$this->playerConnect($player['Login'], false);
			}
		}
		unset($playerlist);

		// Get current game infos if server loaded a map yet
		if ($this->current_status == 100) {
			$this->console('[UASECO] Waiting for the server to start a map...');
		}
		else {
			$this->beginMap($this->server->maps->current->uid);
		}

		// Startup done
		$this->startup_phase = false;
		$this->displayLoadStatus(false);

		// Main loop
		while (true) {
			$starttime = microtime(true);

			if ($this->shutdown_phase == false) {
				// Get callbacks from the server
				$this->executeCallbacks();

				// Sends calls to the server
				$this->executeMulticall();
			}

			// Throw timing events
			$this->releaseEvent('onMainLoop', null);

			if (time() >= $this->next_second) {
				// Trigger pending callbacks
				$read = array();
				$write = null;
				$except = null;
				$this->webaccess->select($read, $write, $except, 0);

				$this->next_second = (time() + 1);
				$this->releaseEvent('onEverySecond', null);
			}

			if (time() >= $this->next_tenth) {
				// Check for Database connection and reconnect on lost connection
				if ($this->mysqli->ping() === false) {
					$this->console('[Database] Lost connection, try to reconnect...');
					$this->connectDatabase();
				}

				$this->next_tenth = (time() + 10);
				$this->releaseEvent('onEveryTenSeconds', null);
			}

			if (time() >= $this->next_quarter) {
				$this->next_quarter = (time() + 15);
				$this->releaseEvent('onEveryFifteenSeconds', null);
			}

			if (time() >= $this->next_minute) {
				$this->next_minute = (time() + 60);
				$this->releaseEvent('onEveryMinute', null);
			}

			// Reduce CPU usage if main loop has time left
			$endtime = microtime(true);
			$delay = 200000 - ($endtime - $starttime) * 1000000;
			if ($delay > 0) {
				usleep($delay);
			}

			// Make sure the script does not timeout
			@set_time_limit($this->settings['script_timeout']);
		}

		// Close the client connection
		$this->client->Terminate();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Initializes the server, loads all server variables
	// and reads all Maps from server.
	private function serverSync () {

		// Setup API-Version
		$this->client->query('SetApiVersion', API_VERSION);

		// Trigger 'LibXmlRpc_PlayersRanking'
		$this->client->query('TriggerModeScriptEventArray', 'LibXmlRpc_GetPlayersRanking', array('300','0'));

		// Get basic server info, server id, login, nickname, zone, name, options, mode, limits...
		$this->server->getServerSettings();

		// Check server build
		if (strlen($this->server->build) == 0 || ($this->server->game == 'ManiaPlanet' && strcmp($this->server->build, MANIAPLANET_BUILD) < 0)) {
			trigger_error("Obsolete server build '". $this->server->build ."' - must be at least '". MANIAPLANET_BUILD ."'!", E_USER_ERROR);
		}

		// Get status
		$status = $this->client->query('GetStatus');
		$this->current_status = $status['Code'];
		unset($status);

		// Get all maps from server
		$this->console('[MapList] Reading complete map list from server...');
		$this->server->maps->readMapList();
		$count = count($this->server->maps->map_list);
		$this->console('[MapList] ...successfully done, read '. $count .' map'. ($count == 1 ? '' : 's') .' which matches server settings.');

		// Throw 'synchronisation' event
		$this->releaseEvent('onSync', null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Sends program header to console and ingame chat.
	private function sendHeader () {

		// Calculate uptime for dedicated server
		$updays = floor($this->server->networkstats['Uptime'] / (24 * 3600));
		$uptime = $this->server->networkstats['Uptime'] - ($updays * 24 * 3600);
		$uptime_dedicated = $updays .' day'. ($updays == 1 ? ' ' : 's ') . $this->formatTime($uptime * 1000, false);

		$this->console_text('#####################################################################################');
		$this->console_text('» Server:    {1} ({2}), join link: "maniaplanet://#join={3}@{4}"', $this->stripColors($this->server->name, false), $this->server->login, $this->server->login, $this->server->title);
		if ($this->server->isrelay) {
			$this->console_text('=> Relays:    {1} - {2}', $this->stripColors($this->server->relaymaster['NickName'], false), $this->server->relaymaster['Login']);
		}
		$this->console_text('» Title:     {1}', $this->server->title);
		$this->console_text('» Gamemode:  {1} with script {2} version {3}', $this->server->gameinfo->getGamemodeName(), $this->server->gameinfo->getGamemodeScriptname(), $this->server->gameinfo->getGamemodeVersion());
		$this->console_text('» Dedicated: {1}/{2} build {3}, using API-Version {4}', $this->server->game, $this->server->version, $this->server->build, $this->server->api_version);
		$this->console_text('»            Ports: Connections {1}, P2P {2}, XmlRpc {3}', $this->server->port, $this->server->p2pport, $this->server->xmlrpc['port']);
		$this->console_text('»            Network: Send {1} KB, Receive {2} KB', $this->server->networkstats['TotalSendingSize'], $this->server->networkstats['TotalReceivingSize']);
		$this->console_text('»            Uptime: {1}', $uptime_dedicated);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» UASECO:    Version {1} build {2}, running on {3}:{4}', UASECO_VERSION, UASECO_BUILD, $this->server->xmlrpc['ip'], $this->server->xmlrpc['port'] .',');
    		$this->console_text('»            based upon work of the authors and projects of:');
    		$this->console_text('»            - Xymph (XAseco2),');
    		$this->console_text('»            - Florian Schnell, AssemblerManiac and many others (ASECO),');
    		$this->console_text('»            - Kremsy (MPASECO)');
		$this->console_text('» Author:    undef.de (UASECO)');
		$this->console_text('» Website:   {1}', UASECO_WEBSITE);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» OS:        {1}', php_uname());
		$this->console_text('» PHP:       PHP/{1} with settings: SafeMode: {2}, MemoryLimit: {3}, MaxExecutionTime: {4}, AllowUrlFopen: {5}', phpversion(), ini_get('safe_mode'), ini_get('memory_limit'), ini_get('max_execution_time'), ini_get('allow_url_fopen'));
		$this->console_text('» MySQL:     Server:  {1}', $this->mysqli->server_version());
		$this->console_text('»            Client:  {1}', $this->mysqli->client_version());
		$this->console_text('»            Connect: {1}', $this->mysqli->connection_info());
		$this->console_text('»            Status:  {1}', $this->mysqli->host_status());
		$this->console_text('#####################################################################################');

		// Format the text of the message
		$message = $this->formatText($this->getChatMessage('STARTUP'),
			UASECO_VERSION,
			$this->server->xmlrpc['ip'],
			$this->server->xmlrpc['port']
		);

		// Show startup message
		$this->sendChatMessage($message);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function logDebugInformations () {

		// Calculate uptime for UASECO
		$uptime = time() - $this->uptime;
		$updays = floor($uptime / (24 * 3600));
		$uptime = $uptime - ($updays * 24 * 3600);
		$uptime_uaseco = $updays .' day'. ($updays == 1 ? ' ' : 's ') . $this->formatTime($uptime * 1000, false);

		// Calculate uptime for dedicated server
		$updays = floor($this->server->networkstats['Uptime'] / (24 * 3600));
		$uptime = $this->server->networkstats['Uptime'] - ($updays * 24 * 3600);
		$uptime_dedicated = $updays .' day'. ($updays == 1 ? ' ' : 's ') . $this->formatTime($uptime * 1000, false);

		$this->console_text('#### DEBUG ##########################################################################');
		$this->console_text('» StartupPhase:  {1}', $this->bool2string($this->startup_phase));
		$this->console_text('» WarmupPhase:   {1}', $this->bool2string($this->warmup_phase));
		$this->console_text('» ChangingMode:  {1}', $this->bool2string($this->changing_to_gamemode));
		$this->console_text('» Restarting:    {1}', $this->bool2string($this->restarting));
		$this->console_text('» CurrentStatus: [{1}] {2}', $this->current_status, $this->server->state_names[$this->current_status]);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Uptime:        {1}', $uptime_uaseco);
		if ( function_exists('sys_getloadavg') ) {
			$this->console_text('» System Load:   {1}', implode(', ', sys_getloadavg()));
		}
		$this->console_text('» MEM-Usage:     {1} MB, PEAK: {2} MB', round(memory_get_usage() / pow(1024,2), 4), round(memory_get_peak_usage() / pow(1024,2), 4));
		if (function_exists('posix_getuid') && function_exists('posix_getgid')) {
			$this->console_text('» Process user:  {1}:{2} (UID/GID)', posix_getuid(), posix_getgid());
		}
		$this->console_text('» Script owner:  {1}:{2} (UID/GID)', getmyuid(), getmygid());
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» NbMaps:        {1} : {2} bytes', sprintf("%5s", $this->server->maps->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->maps->map_list)),0,'.','.')));
		$this->console_text('» NbPlayers:     {1} : {2} bytes', sprintf("%5s", $this->server->players->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->players->player_list)),0,'.','.')));
		$this->console_text('» PlayerRanks:   {1} : {2} bytes', sprintf("%5s", $this->server->rankings->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->rankings->ranking_list)),0,'.','.')));
		$this->console_text('» NbPlugins:     {1} : {2} bytes', sprintf("%5s", count($this->plugins)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->plugins)),0,'.','.')));
		$this->console_text('» RegEvents:     {1} : {2} bytes', sprintf("%5s", count($this->registered_events)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->registered_events)),0,'.','.')));
		$this->console_text('» RegChatCmds:   {1} : {2} bytes', sprintf("%5s", count($this->registered_chatcmds)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->registered_chatcmds)),0,'.','.')));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Title:         {1}', $this->server->title);
		$this->console_text('» Gamemode:      {1} with script {2} version {3}', $this->server->gameinfo->getGamemodeName(), $this->server->gameinfo->getGamemodeScriptname(), $this->server->gameinfo->getGamemodeVersion());
		$this->console_text('» Dedicated:     {1}/{2} build {3}, using API-Version {4}', $this->server->game, $this->server->version, $this->server->build, $this->server->api_version);
		$this->console_text('»                Ports: Connections {1}, P2P {2}, XmlRpc {3}', $this->server->port, $this->server->p2pport, $this->server->xmlrpc['port']);
		$this->console_text('»                Network: Send {1} KB, Receive {2} KB', $this->server->networkstats['TotalSendingSize'], $this->server->networkstats['TotalReceivingSize']);
		$this->console_text('»                Uptime: {1}', $uptime_dedicated);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» OS:            {1}', php_uname());
		$this->console_text('» PHP:           PHP/{1} with settings: SafeMode: {2}, MemoryLimit: {3}, MaxExecutionTime: {4}, AllowUrlFopen: {5}', phpversion(), ini_get('safe_mode'), ini_get('memory_limit'), ini_get('max_execution_time'), ini_get('allow_url_fopen'));
		$this->console_text('» MySQL:         Server:  {1}', $this->mysqli->server_version());
		$this->console_text('»                Client:  {1}', $this->mysqli->client_version());
		$this->console_text('»                Connect: {1}', $this->mysqli->connection_info());
		$this->console_text('»                Status:  {1}', $this->mysqli->host_status());
		$this->console_text('#####################################################################################');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function logDebugPluginUsage ($list) {

		$this->console_text('#### DEBUG ##########################################################################');
		$this->console_text('» Plugin memory usage on initialization:');
		foreach ($list as $plugin => $usage) {
			$this->console_text('» {1} {2} bytes', str_pad('['.$plugin.']', 30, ' ', STR_PAD_RIGHT), str_pad($this->formatNumber($usage, 0, '.', '.'), 15, ' ', STR_PAD_LEFT));
		}
		$this->console_text('#####################################################################################');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Load settings and apply them on the current instance.
	private function loadSettings ($config_file) {

		if ($settings = $this->parser->xmlToArray($config_file, true, true)) {
			// read the XML structure into an array
			$settings = $settings['SETTINGS'];

			// Read <developer_options> settings and apply them
			$this->debug = $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['DEBUG'][0]);
			$this->settings['developer']['log_events']['common']		= $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['LOG_EVENTS'][0]['COMMON'][0]);
			$this->settings['developer']['log_events']['registered_types']	= $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['LOG_EVENTS'][0]['REGISTERED_TYPES'][0]);
			$this->settings['developer']['log_events']['all_types']		= $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['LOG_EVENTS'][0]['ALL_TYPES'][0]);

			// read settings and apply them
			$this->chat_colors = $settings['COLORS'][0];
			$this->chat_messages = $settings['MESSAGES'][0];
			$this->masteradmin_list = $settings['MASTERADMINS'][0];
			if (!isset($this->masteradmin_list) || !is_array($this->masteradmin_list)) {
				trigger_error('No MasterAdmin(s) configured in [config/UASECO.xml]!', E_USER_ERROR);
			}

			// check masteradmin list consistency
			if (empty($this->masteradmin_list['IPADDRESS'])) {
				// fill <ipaddress> list to same length as <tmlogin> list
				if (($cnt = count($this->masteradmin_list['TMLOGIN'])) > 0)
					$this->masteradmin_list['IPADDRESS'] = array_fill(0, $cnt, '');
			}
			else {
				if (count($this->masteradmin_list['TMLOGIN']) != count($this->masteradmin_list['IPADDRESS']))
					trigger_error("MasterAdmin mismatch between <tmlogin>'s and <ipaddress>'s!", E_USER_WARNING);
			}

			// set admin contact
			$this->settings['admin_contact'] = $settings['ADMIN_CONTACT'][0];

			// set admin lock password
			$this->settings['lock_password'] = $settings['LOCK_PASSWORD'][0];

			// set cheater action
			$this->settings['cheater_action'] = $settings['CHEATER_ACTION'][0];

			// set script timeout
			$this->settings['script_timeout'] = $settings['SCRIPT_TIMEOUT'][0];

			// show MX world record?
			$this->settings['show_mxrec'] = $settings['SHOW_MXREC'][0];

			// show played time at end of map?
			$this->settings['show_playtime'] = $settings['SHOW_PLAYTIME'][0];

			// show current map at start?
			$this->settings['show_curmap'] = $settings['SHOW_CURMAP'][0];

			// set default filename for readmaplist/writemaplist
			$this->settings['default_maplist'] = $settings['DEFAULT_MAPLIST'][0];

			// add random filter to /admin writemaplist output
			$this->settings['writemaplist_random'] = $this->string2bool($settings['WRITEMAPLIST_RANDOM'][0]);

			// set minimum number of ranked players in a clan to be included in /topclans
			$this->settings['topclans_minplayers'] = $settings['TOPCLANS_MINPLAYERS'][0];

			// set multiple of win count to show global congrats message
			$this->settings['global_win_multiple'] = ($settings['GLOBAL_WIN_MULTIPLE'][0] > 0 ? $settings['GLOBAL_WIN_MULTIPLE'][0] : 1);

			// timeout of the message window in seconds
			$this->settings['window_timeout'] = $settings['WINDOW_TIMEOUT'][0];

			// set filename of admin/operator/ability lists file
			$this->settings['adminops_file'] = $settings['ADMINOPS_FILE'][0];

			// set filename of banned IPs list file
			$this->settings['bannedips_file'] = $settings['BANNEDIPS_FILE'][0];

			// set filename of blacklist file
			$this->settings['blacklist_file'] = $settings['BLACKLIST_FILE'][0];

			// set filename of guestlist file
			$this->settings['guestlist_file'] = $settings['GUESTLIST_FILE'][0];

			// add random filter to /admin writemaplist output
			$this->settings['writemaplist_random'] = $this->string2bool($settings['WRITEMAPLIST_RANDOM'][0]);

			// set minimum admin client version
			$this->settings['admin_client'] = $settings['ADMIN_CLIENT_VERSION'][0];

			// set minimum player client version
			$this->settings['player_client'] = $settings['PLAYER_CLIENT_VERSION'][0];

			// set default rounds points system
			$this->settings['default_rpoints'] = $settings['DEFAULT_RPOINTS'][0];

			// display welcome message as window ?
			$this->settings['welcome_msg_window'] = $this->string2bool($settings['WELCOME_MSG_WINDOW'][0]);

			// log all chat, not just chat commands ?
			$this->settings['log_all_chat'] = $this->string2bool($settings['LOG_ALL_CHAT'][0]);

			// show timestamps in /chatlog, /pmlog & /admin pmlog ?
			$this->settings['chatpmlog_times'] = $this->string2bool($settings['CHATPMLOG_TIMES'][0]);

			// show round reports in message window?
			$this->settings['rounds_in_window'] = $this->string2bool($settings['ROUNDS_IN_WINDOW'][0]);

			// color nicknames in the various /top... etc lists?
			$this->settings['lists_colornicks'] = $this->string2bool($settings['LISTS_COLORNICKS'][0]);

			// color mapnames in the various /lists... lists?
			$this->settings['lists_colormaps'] = $this->string2bool($settings['LISTS_COLORMAPS'][0]);

			// display checkpoints panel?
			$this->settings['display_checkpoints'] = $this->string2bool($settings['DISPLAY_CHECKPOINTS'][0]);

			// enable /cpsspec command?
			$this->settings['enable_cpsspec'] = $this->string2bool($settings['ENABLE_CPSSPEC'][0]);

			// automatically enable /cps for new players?
			$this->settings['auto_enable_cps'] = $this->string2bool($settings['AUTO_ENABLE_CPS'][0]);

			// automatically enable /dedicps for new players?
			$this->settings['auto_enable_dedicps'] = $this->string2bool($settings['AUTO_ENABLE_DEDICPS'][0]);

			// automatically add IP for new admins/operators?
			$this->settings['auto_admin_addip'] = $this->string2bool($settings['AUTO_ADMIN_ADDIP'][0]);

			// automatically force spectator on player using /afk ?
			$this->settings['afk_force_spec'] = $this->string2bool($settings['AFK_FORCE_SPEC'][0]);

			// provide clickable buttons in lists?
			$this->settings['clickable_lists'] = $this->string2bool($settings['CLICKABLE_LISTS'][0]);

			// show logins in /recs?
			$this->settings['show_rec_logins'] = $this->string2bool($settings['SHOW_REC_LOGINS'][0]);

			// perform UASECO version check at start-up & MasterAdmin connect
			$this->settings['uptodate_check'] = $this->string2bool($settings['UPTODATE_CHECK'][0]);

			// set global blacklist settings
			$this->settings['global_blacklist_merge'] = $this->string2bool($settings['GLOBAL_BLACKLIST_MERGE'][0]);
			$this->settings['global_blacklist_url'] = $settings['GLOBAL_BLACKLIST_URL'][0];

			// Log passwords in logfile?
			$this->settings['mask_password'] = $this->string2bool($settings['MASK_PASSWORD'][0]);

			// Read <mysql> settings and apply them
			$this->settings['mysql']['host'] = $settings['MYSQL'][0]['HOST'][0];
			$this->settings['mysql']['login'] = $settings['MYSQL'][0]['LOGIN'][0];
			$this->settings['mysql']['password'] = $settings['MYSQL'][0]['PASSWORD'][0];
			$this->settings['mysql']['database'] = $settings['MYSQL'][0]['DATABASE'][0];

			// Read <dedicated_server> settings and apply them
			$this->server->xmlrpc['login'] = $settings['DEDICATED_SERVER'][0]['LOGIN'][0];
			$this->server->xmlrpc['pass'] = $settings['DEDICATED_SERVER'][0]['PASSWORD'][0];
			$this->server->xmlrpc['port'] = $settings['DEDICATED_SERVER'][0]['PORT'][0];
			$this->server->xmlrpc['ip'] = $settings['DEDICATED_SERVER'][0]['IP'][0];
			if (isset($settings['DEDICATED_SERVER'][0]['TIMEOUT'][0])) {
				$this->server->timeout = (int)$settings['DEDICATED_SERVER'][0]['TIMEOUT'][0];
			}
			else {
				$this->server->timeout = null;
				trigger_error('Server init timeout not specified in [config/UASECO.xml]!', E_USER_WARNING);
			}

			if ($this->settings['admin_client'] != '' && preg_match('/^2\.11\.[12][0-9]$/', $this->settings['admin_client']) != 1 || $this->settings['admin_client'] == '2.11.10') {
				trigger_error('Invalid admin client version : '. $this->settings['admin_client'] .'!', E_USER_ERROR);
			}
			if ($this->settings['player_client'] != '' && preg_match('/^2\.11\.[12][0-9]$/', $this->settings['player_client']) != 1 || $this->settings['player_client'] == '2.11.10') {
				trigger_error('Invalid player client version: '. $this->settings['player_client'] .'!', E_USER_ERROR);
			}
		}
		else {
			// could not parse XML file
			trigger_error('Could not read/parse config file ['. $config_file .']!', E_USER_ERROR);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Loads files in the plugins directory.
	private function loadPlugins () {

		// Debug
		$plugin_usage = array();

		$this->displayLoadStatus('Loading Plugins...', 0.0);

		// Load and parse the plugins file
		if ($plugins = $this->parser->xmlToArray('config/plugins.xml')) {
			if (!empty($plugins['PLUGINS']['PLUGIN'])) {
				// Take each plugin tag
				$count = 0;
				$amount = count($plugins['PLUGINS']['PLUGIN']);
				foreach ($plugins['PLUGINS']['PLUGIN'] as $plugin) {

					// Reset plugin variables... useless, but removes warning!
					$_PLUGIN = null;
					unset($_PLUGIN);

					// Debug
					$usage_before = memory_get_usage();

					// Log plugin message
					$this->console('[Plugin] » initialize [plugins/'. $plugin .']');

					// Include the plugin
					require_once('plugins/'. $plugin);

					// Load only plugins that were configured right...
					if (!isset($_PLUGIN) || get_parent_class($_PLUGIN) != 'Plugin') {
						trigger_error('$_PLUGIN was not set or was set in a wrong way in the file [plugins/'. $plugin .'].'. CRLF .'This is probably an old version of the plugin, update it or remove it from the [config/plugins.xml]!', E_USER_ERROR);
					}

					$count ++;
					$ratio = (1.0 / $amount) * $count;
					$this->displayLoadStatus('Loading Plugin '. $_PLUGIN->getClassname() .'...', $ratio);

					// Store filename from plugin
					$_PLUGIN->setFilename($plugin);

					// Register plugin...
					$this->plugins[$_PLUGIN->getClassname()] = $_PLUGIN;

					// Debug
					$usage_after = memory_get_usage();
					$plugin_usage[$_PLUGIN->getClassname()] = ($usage_after - $usage_before);
				}
				$this->displayLoadStatus('Plugins successfully initialized.', 1.0);

				// Check if all plugins are working right...
				$this->checkDependencies();

				// Now register events and chat-commands
				$this->console('[Plugin] Registering events and chat commands...');
				$this->displayLoadStatus('Registering events and chat commands...', 0.0);
				$count = 0;
				$amount = count($this->plugins);
				foreach ($this->plugins as $plugin) {

					$count ++;
					$ratio = (1.0 / $amount) * $count;
					$this->displayLoadStatus('Registering for '. $plugin->getClassname() .'...', $ratio);

					// Register events from plugin...
					foreach ($plugin->getEvents() as $event => $callback) {
						$this->registerEvent($event, $callback);
					}

					// Register chat commands from plugin...
					foreach ($plugin->getChatCommands() as $command => $cmd) {
						$this->registerChatCommand($command, $cmd['callback'], $cmd['help'], $cmd['rights'], $cmd['params']);
					}
				}
				$this->console('[Plugin] ...successfully done!');
				$this->displayLoadStatus('Registering events and chat commands successfully done.', 1.0);

				// Log plugin mem. usage
				if ($this->debug) {
					$this->logDebugPluginUsage($plugin_usage);
				}
			}
		}
		else {
			trigger_error('[Plugin] Could not read/parse plugins list [config/plugins.xml]!', E_USER_ERROR);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Checks the included plugins for their dependencies.
	 *
	 */
	private function checkDependencies () {

		// Go through the list of plugins if there are any ...
		$this->console('[Plugin] Checking dependencies of Plugins...');
		$this->displayLoadStatus('Checking dependencies of Plugins...', 0.0);
		$count = 0;
		$amount = count($this->plugins);
		foreach ($this->plugins as $plugin) {

			$count ++;
			$ratio = (1.0 / $amount) * $count;
			$this->displayLoadStatus('Checking dependencies for '. $plugin->getClassname() .'...', $ratio);

			// Now check the dependencies of each of them ...
			foreach ($plugin->getDependencies() as $dependence) {

				// Check for plugin required version?
				$check_version = false;

				if (!isset($this->plugins[$dependence->classname]) && $dependence->permissions == Dependence::REQUIRED) {
					// Check if required dependence exists...
					trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] requires the Plugin ['. $dependence->classname .'] to run, disclude this Plugin or add the required Plugin in [config/plugins.xml] to continue!', E_USER_ERROR);
				}
				else if (!isset($this->plugins[$dependence->classname]) && $dependence->permissions == Dependence::WANTED) {
					// Check if wanted dependence exists...
//					$this->console('[Plugin] The Plugin ['. $plugin->getClassname() .'] wants the Plugin ['. $dependence->classname .'] to run full featured, if you want, add the wanted Plugin in [config/plugins.xml].');
				}
				else {
					$check_version = true;
				}

				// Check if disallowed dependence exists...
				if (isset($this->plugins[$dependence->classname]) && $dependence->permissions == Dependence::DISALLOWED) {
					trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] can not run together with the plugin ['. $dependence->classname .'], disclude this or the disallowed Plugin in [config/plugins.xml] to continue!', E_USER_ERROR);
				}

				if ($check_version == true) {
					// Check if dependence has min version...
					if (isset($dependence->min_version) && isset($this->plugins[$dependence->classname]) && $this->versionCheck($this->plugins[$dependence->classname]->getVersion(), $dependence->min_version, '<')) {
						trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] requires a more recent version of the Plugin ['. $dependence->classname .'] (current version: '. $this->plugins[$dependence->classname]->getVersion() .', expected version: '. $dependence->min_version .')!', E_USER_ERROR);
					}

					// Check if dependence is lower than max version...
					if (isset($dependence->max_version) && isset($this->plugins[$dependence->classname]) && $this->versionCheck($this->plugins[$dependence->classname]->getVersion(), $dependence->max_version, '>')) {
						trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] requires an older version of the Plugin ['. $dependence->classname .'] (current version: '. $this->plugins[$dependence->classname]->getVersion() .', expected version: '. $dependence->max_version .')!', E_USER_ERROR);
					}
				}
			}
		}
		$this->console('[Plugin] ...successfully done!');
		$this->displayLoadStatus('Dependencies of Plugins successfully checked.', 1.0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Registers functions which are called on specific events.
	public function registerEvent ($event, $callback_function) {
		// Registers a new event
		if (is_callable($callback_function)) {
			$this->registered_events[$event][] = $callback_function;
		}
		else {
			$this->console('[Plugin] Can not register callback Method "'. $callback_function[1] .'()" of class "'. $callback_function[0]->getClassname() .'" for event "'. $event .'", because the Method was not found, ignoring!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Executes the functions which were registered for specified events.
	public function releaseEvent ($event_type, $callback_param) {

		// Executes registered event functions, if there are any events for that type
		if ( !empty($this->registered_events[$event_type]) ) {

			if ( ($this->settings['developer']['log_events']['registered_types'] == true) && ($this->settings['developer']['log_events']['all_types'] == false) ) {
				if ($event_type != 'onEverySecond' && $event_type != 'onMainLoop' && $event_type != 'onModeScriptCallbackArray' && $event_type != 'onModeScriptCallback') {
					$this->console('[EventType] Releasing "'. $event_type .'"');
				}
			}

			// For each registered function of this type
			if ($this->startup_phase === true) {
				$count = 0;
				$amount = count($this->registered_events[$event_type]);
			}
			foreach ($this->registered_events[$event_type] as $callback_func) {
				// If function for the specified player connect event can be found
				if (is_callable($callback_func)) {
					if ($this->startup_phase === true) {
						$count ++;
						$ratio = (1.0 / $amount) * $count;
						$this->displayLoadStatus('Event '. $event_type .' calls '. get_class($callback_func[0]) .'...', $ratio);
					}

					// ... execute it!
					call_user_func($callback_func, $this, $callback_param);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Register a new chat command
	public function registerChatCommand ($chat_command, $callback_function, $help, $rights = Player::PLAYERS, $params = array()) {
		if (is_callable($callback_function)) {
			$chat_command =  strtolower(trim($chat_command));
			if ( !isset($this->registered_chatcmds[$chat_command]) ) {
				$this->registered_chatcmds[$chat_command] = array(
					'callback'	=> $callback_function,
					'help'		=> $help,
					'rights'	=> $rights,
					'params'	=> $params,
				);
			}
			else {
				$this->console('[Plugin] » Can not register chat command "/'. $chat_command .'" for class "'. $callback_function[0]->getClassname() .'", because it is already registered to the callback Method "'. $this->registered_chatcmds[$chat_command]['callback'][1] .'()" of class "'. $this->registered_chatcmds[$chat_command]['callback'][0]->getClassname() .'", ignoring!');
			}
		}
		else {
			$this->console('[Plugin] » Can not register chat command "/'. $chat_command .'" because callback Method "'. $callback_function[1] .'()" of class "'. $callback_function[0]->getClassname() .'" is not callable, ignoring!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Removes a chat command
	public function unregisterChatCommand ($chat_command) {
		$chat_command =  strtolower(trim($chat_command));
		if (isset($this->registered_chatcmds[$chat_command])) {
			unset($this->registered_chatcmds[$chat_command]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Release a chat command from a plugin
	public function releaseChatCommand ($command, $login) {

		$player = $this->server->players->getPlayer($login);
		$chat = array(
			$player->pid,
			$player->login,
			$command,
		);
		$this->playerChat($chat);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Returns a Class Plugin object of the given classname.
	 *
	 * @param string $classname
	 * @return Plugin object
	 */
	public function getPlugin ($classname) {
		return $this->plugins[$classname];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function connectDatabase () {

		$settings = array(
			'host'			=> $this->settings['mysql']['host'],
	                'login'			=> $this->settings['mysql']['login'],
	                'password'		=> $this->settings['mysql']['password'],
			'database'		=> $this->settings['mysql']['database'],
			'autocommit'		=> 1,
			'charset'		=> 'utf8',
			'collate'		=> 'utf8_bin',
			'debug'			=> $this->debug,
		);

		// Connect
		$this->mysqli = new Database($settings);
		$this->console('[Database] ...connection established successfully!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkDatabaseStructure () {

		// Create main tables
		$this->console('[Database] Checking database structure:');
		$this->console('[Database] » Checking table `maps`');
		$query = "
		CREATE TABLE IF NOT EXISTS `maps` (
			`Id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`Uid` varchar(27) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Filename` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Name` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Comment` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Author` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`AuthorNickname` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`AuthorZone` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`AuthorContinent` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`AuthorNation` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`AuthorScore` int(4) UNSIGNED NOT NULL,
			`AuthorTime` int(4) UNSIGNED NOT NULL,
			`GoldTime` int(4) UNSIGNED NOT NULL,
			`SilverTime` int(4) UNSIGNED NOT NULL,
			`BronzeTime` int(4) UNSIGNED NOT NULL,
			`Environment` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Mood` enum('unknown', 'Sunrise', 'Day', 'Sunset', 'Night') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			`Cost` mediumint(3) unsigned NOT NULL,
			`Type` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Style` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`MultiLap` enum('false', 'true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			`NbLaps` tinyint(1) UNSIGNED NOT NULL,
			`NbCheckpoints` tinyint(1) UNSIGNED NOT NULL,
			`Validated` enum('null','false','true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			`ExeVersion` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`ExeBuild` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`ModName` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`ModFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`ModUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`SongFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`SongUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			PRIMARY KEY (`Id`),
			UNIQUE KEY `Uid` (`Uid`),
			Key `Author` (`Author`),
			Key `AuthorScore` (`AuthorScore`),
			Key `AuthorTime` (`AuthorTime`),
			Key `GoldTime` (`GoldTime`),
			Key `SilverTime` (`SilverTime`),
			Key `BronzeTime` (`BronzeTime`),
			Key `Environment` (`Environment`),
			Key `Mood` (`Mood`),
			Key `MultiLap` (`MultiLap`),
			Key `NbLaps` (`NbLaps`),
			Key `NbCheckpoints` (`NbCheckpoints`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;
		";
		$this->mysqli->query($query);
		$this->displayLoadStatus('Checking database structure...', 0.65);


		$this->console('[Database] » Checking table `players`');
		$query = "
		CREATE TABLE IF NOT EXISTS `players` (
			`Id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`Login` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Game` varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`NickName` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Continent` tinyint(3) NOT NULL DEFAULT '0',
			`Nation` varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`UpdatedAt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Wins` mediumint(9) NOT NULL DEFAULT '0',
			`Visits` mediumint(9) UNSIGNED NOT NULL DEFAULT '0',
			`TimePlayed` int(10) UNSIGNED NOT NULL DEFAULT '0',
			`TeamName` char(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			PRIMARY KEY (`Id`),
			UNIQUE KEY `Login` (`Login`),
			KEY `Game` (`Game`),
			KEY `Continent` (`Continent`),
			KEY `Nation` (`Nation`),
			KEY `UpdatedAt` (`UpdatedAt`),
			KEY `Wins` (`Wins`),
			KEY `Visits` (`Visits`),
			KEY `TimePlayed` (`TimePlayed`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;
		";
		$this->mysqli->query($query);
		$this->displayLoadStatus('Checking database structure...', 0.7);


		$this->console('[Database] » Checking table `players_extra`');
		$query = "
		CREATE TABLE IF NOT EXISTS `players_extra` (
			`PlayerId` mediumint(9) NOT NULL DEFAULT '0',
			`Cps` smallint(3) NOT NULL DEFAULT '-1',
			`DediCps` smallint(3) NOT NULL DEFAULT '-1',
			`Donations` mediumint(9) NOT NULL DEFAULT '0',
			`Style` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`Panels` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			`PanelBG` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
			PRIMARY KEY (`PlayerId`),
			KEY `Donations` (`Donations`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin';
		";
		$this->mysqli->query($query);
		$this->displayLoadStatus('Checking database structure...', 0.75);


		// Check for main tables
		$tables = array();
		$res = $this->mysqli->query('SHOW TABLES;');
		if ($res) {
			while ($row = $res->fetch_row()) {
				$tables[] = $row[0];
			}
			$res->free_result();
		}

		$check = array();
		$check[1] = in_array('maps', $tables);
		$check[2] = in_array('players', $tables);
		$check[3] = in_array('players_extra', $tables);
		if (!($check[1] && $check[2] && $check[3])) {
			trigger_error('[Database] ERROR: Table structure incorrect, use [newinstall/database/uaseco.sql] to correct this!', E_USER_ERROR);
		}
		$this->displayLoadStatus('Checking database structure...', 0.8);


		// Add players `Continent` and `Visits` column
		$fields = array();
		$res = $this->mysqli->query('SHOW COLUMNS FROM `players`;');
		if ($res) {
			while ($row = $res->fetch_row()) {
				$fields[] = $row[0];
			}
			$res->free_result();
		}
		if (!in_array('Continent', $fields)) {
			$this->console("[Database] » Add `players` column `Continent`...");
			$this->mysqli->query("ALTER TABLE `players` ADD `Continent` tinyint(3) NOT NULL DEFAULT 0 AFTER `NickName`;");
		}
		if (!in_array('Visits', $fields)) {
			$this->mysqli->query('ALTER TABLE `players` ADD `Visits` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" AFTER `Wins`, ADD INDEX (`Visits`);');
		}
		$this->displayLoadStatus('Checking database structure...', 0.85);


		// Add `players_extra` `PanelBG` column
		$fields = array();
		$res = $this->mysqli->query('SHOW COLUMNS FROM `players_extra`;');
		if ($res) {
			while ($row = $res->fetch_row()) {
				$fields[] = $row[0];
			}
			$res->free_result();
		}
		if (!in_array('PanelBG', $fields)) {
			$this->console("[Database] » Add `players_extra` column `PanelBG`...");
			$this->mysqli->query("ALTER TABLE `players_extra` ADD `PanelBG` varchar(30) NOT NULL DEFAULT '';");
		}
		$this->displayLoadStatus('Checking database structure...', 0.9);


		// Add at `maps` the new columns
		$fields = array();
		$res = $this->mysqli->query('SHOW COLUMNS FROM `maps`;');
		if ($res) {
			while ($row = $res->fetch_row()) {
				$fields[] = $row[0];
			}
			$res->free_result();
		}
		if (!in_array('Filename', $fields)) {
			$this->console("[Database] » Add `maps` column `Filename`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Filename` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Uid`;");
		}
		if (!in_array('Comment', $fields)) {
			$this->console("[Database] » Add `maps` column `Comment`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Comment` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Name`;");
		}
		if (!in_array('AuthorNickname', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorNickname`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorNickname` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Author`;");
		}
		if (!in_array('AuthorZone', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorZone`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorZone` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `AuthorNickname`;");
		}
		if (!in_array('AuthorContinent', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorContinent`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorContinent` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `AuthorZone`;");
		}
		if (!in_array('AuthorNation', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorNation`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorNation` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `AuthorContinent`;");
		}
		if (!in_array('AuthorScore', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorScore`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorScore` int(4) UNSIGNED NOT NULL AFTER `AuthorNation`, ADD INDEX (`AuthorScore`);");
		}
		if (!in_array('AuthorTime', $fields)) {
			$this->console("[Database] » Add `maps` column `AuthorTime`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `AuthorTime` int(4) UNSIGNED NOT NULL AFTER `AuthorScore`, ADD INDEX (`AuthorTime`);");
		}
		if (!in_array('GoldTime', $fields)) {
			$this->console("[Database] » Add `maps` column `GoldTime`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `GoldTime` int(4) UNSIGNED NOT NULL AFTER `AuthorTime`, ADD INDEX (`GoldTime`);");
		}
		if (!in_array('SilverTime', $fields)) {
			$this->console("[Database] » Add `maps` column `SilverTime`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `SilverTime` int(4) UNSIGNED NOT NULL AFTER `GoldTime`, ADD INDEX (`SilverTime`);");
		}
		if (!in_array('BronzeTime', $fields)) {
			$this->console("[Database] » Add `maps` column `BronzeTime`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `BronzeTime` int(4) UNSIGNED NOT NULL AFTER `SilverTime`, ADD INDEX (`BronzeTime`);");
		}
		if (!in_array('Mood', $fields)) {
			$this->console("[Database] » Add `maps` column `Mood`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Mood` enum('unknown', 'Sunrise', 'Day', 'Sunset', 'Night') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `Environment`, ADD INDEX (`Mood`);");
		}
		if (!in_array('Cost', $fields)) {
			$this->console("[Database] » Add `maps` column `Cost`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Cost` mediumint(3) unsigned NOT NULL AFTER `Mood`;");
		}
		if (!in_array('Type', $fields)) {
			$this->console("[Database] » Add `maps` column `Type`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Type` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Cost`;");
		}
		if (!in_array('Style', $fields)) {
			$this->console("[Database] » Add `maps` column `Style`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Style` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Type`;");
		}
		if (!in_array('MultiLap', $fields)) {
			$this->console("[Database] » Add `maps` column `MultiLap`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `MultiLap` enum('false', 'true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `Style`, ADD INDEX (`MultiLap`);");
		}
		if (!in_array('NbLaps', $fields)) {
			$this->console("[Database] » Add `maps` column `NbLaps`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `NbLaps` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `MultiLap`;");
		}
		if (!in_array('NbCheckpoints', $fields)) {
			$this->console("[Database] » Add `maps` column `NbCheckpoints`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `NbCheckpoints` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `NbLaps`;");
		}
		if (!in_array('Validated', $fields)) {
			$this->console("[Database] » Add `maps` column `Validated`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `Validated` enum('null','false','true') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `NbCheckpoints`;");
		}
		if (!in_array('ExeVersion', $fields)) {
			$this->console("[Database] » Add `maps` column `ExeVersion`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `ExeVersion` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `Validated`;");
		}
		if (!in_array('ExeBuild', $fields)) {
			$this->console("[Database] » Add `maps` column `ExeBuild`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `ExeBuild` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ExeVersion`;");
		}
		if (!in_array('ModName', $fields)) {
			$this->console("[Database] » Add `maps` column `ModName`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `ModName` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ExeBuild`;");
		}
		if (!in_array('ModFile', $fields)) {
			$this->console("[Database] » Add `maps` column `ModFile`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `ModFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ModName`;");
		}
		if (!in_array('ModUrl', $fields)) {
			$this->console("[Database] » Add `maps` column `ModUrl`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `ModUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ModFile`;");
		}
		if (!in_array('SongFile', $fields)) {
			$this->console("[Database] » Add `maps` column `SongFile`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `SongFile` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `ModUrl`;");
		}
		if (!in_array('SongUrl', $fields)) {
			$this->console("[Database] » Add `maps` column `SongUrl`...");
			$this->mysqli->query("ALTER TABLE `maps` ADD `SongUrl` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' AFTER `SongFile`;");
		}
		$this->displayLoadStatus('Checking database structure...', 0.95);

		$this->console('[Database] ...successfully done!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Authenticates USASEO at the server.
	private function connectDedicated () {
		// Only if logins are set
		if ($this->server->xmlrpc['ip'] && $this->server->xmlrpc['port'] && $this->server->xmlrpc['login'] && $this->server->xmlrpc['pass']) {
			// Log console message
			$this->console('[Dedicated] Try to connect to Maniaplanet dedicated server at {1}:{2} timeout {3}s',
				$this->server->xmlrpc['ip'],
				$this->server->xmlrpc['port'],
				($this->server->timeout !== null ? $this->server->timeout : 0)
			);

			try {
				// Connect to the server
				$this->client->connect($this->server->xmlrpc['ip'], $this->server->xmlrpc['port'], $this->server->timeout);
			}
			catch (Exception $exception) {
				trigger_error('[Dedicated] ['. $exception->getCode() .'] connect - '. $exception->getMessage(), E_USER_WARNING);
				return false;
			}

			// Check login
			if ($this->server->xmlrpc['login'] != 'SuperAdmin') {
				trigger_error("[Dedicated] Invalid login '". $this->server->xmlrpc['login'] ."' - must be 'SuperAdmin' in [config/UASECO.xml]!", E_USER_WARNING);
				return false;
			}

			// Check password
			if ($this->server->xmlrpc['pass'] == 'SuperAdmin') {
				trigger_error("[Dedicated] Insecure (default) password '" . $this->server->xmlrpc['pass'] . "' - should be changed in dedicated config and [config/UASECO.xml]!", E_USER_WARNING);
			}

			// Log console message
			if ($this->settings['mask_password'] == true) {
				$this->console("[Dedicated] Try to authenticate with login '{1}' and password '{2}' (masked password)",
					$this->server->xmlrpc['login'],
					preg_replace('#.#', '*', $this->server->xmlrpc['pass'])
				);
			}
			else {
				$this->console("[Dedicated] Try to authenticate with login '{1}' and password '{2}'",
					$this->server->xmlrpc['login'],
					$this->server->xmlrpc['pass']
				);
			}

			try {
				// Log into the server
				$this->client->query('Authenticate', $this->server->xmlrpc['login'], $this->server->xmlrpc['pass']);
			}
			catch (Exception $exception) {
				trigger_error('[Dedicated] ['. $exception->getCode() .'] Authenticate - '. $exception->getMessage(), E_USER_WARNING);
				return false;
			}

			// Enable callback system
			$this->client->query('EnableCallbacks', true);

			// Wait for server to be ready
			$this->waitServerReady();

			// Connection established
			return true;
		}
		else {
			// Connection failed
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Waits for the server to be ready (status 4, 'Running - Play')
	private function waitServerReady () {
		$status = $this->client->query('GetStatus');
		if ($status['Code'] != 4) {
			$this->console("[Dedicated] » Waiting for dedicated server to reach status 'Running - Play'...");
			$this->console('[Dedicated] » Status: ['. $status['Code'] .'] '. $status['Name']);
			$timeout = 0;
			$laststatus = $status['Name'];
			while ($status['Code'] != 4) {
				sleep(1);
				$status = $this->client->query('GetStatus');
				if ($laststatus != $status['Name']) {
					$this->console('[Dedicated] » Status: ['. $status['Code'] .'] '. $status['Name']);
					$laststatus = $status['Name'];
				}
				if (isset($this->server->timeout) && $timeout++ > $this->server->timeout) {
					trigger_error('[Dedicated] Timed out while waiting for dedicated server!', E_USER_ERROR);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Gets callbacks from the TM Dedicated Server and reacts on them.
	private function executeCallbacks () {

		// Get the responses
		$calls = array();

		try {
			$calls = $this->client->getCallbacks();
		}
		catch (Exception $exception) {
			trigger_error('ExecuteCallbacks XmlRpc Error ['. $exception->getCode() .'] - '. $exception->getMessage(), E_USER_ERROR);
		}

		if (!empty($calls)) {
			while ($call = array_shift($calls)) {
				switch ($call[0]) {
					case 'ManiaPlanet.PlayerChat':
						// [0]=PlayerUid, [1]=Login, [2]=Text, [3]=IsRegistredCmd
						if ($call[1][0] === $this->server->id) {
							$this->releaseEvent('onServerChat', $call[1]);
						}
						else {
							$this->playerChat($call[1]);
						}
						break;

					case 'ManiaPlanet.PlayerConnect':
						// [0]=Login, [1]=IsSpectator
						$this->playerConnect($call[1][0], $call[1][1]);
						break;

					case 'ManiaPlanet.PlayerInfoChanged':
						// [0]=PlayerInfo
						$this->playerInfoChanged($call[1][0]);
						break;

					case 'ManiaPlanet.StatusChanged':
						// [0]=StatusCode, [1]=StatusName
						// update status changes
						$this->current_status = $call[1][0];
						$this->releaseEvent('onStatusChangeTo'. $this->current_status, $call[1]);
						break;

					case 'ManiaPlanet.PlayerManialinkPageAnswer':
						// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
						$this->releaseEvent('onPlayerManialinkPageAnswer', $call[1]);
						break;

					case 'ManiaPlanet.PlayerDisconnect':
						// [0]=Login, [1]=DisconnectionReason
						$this->playerDisconnect($call[1]);
						break;

					case 'ManiaPlanet.MapListModified':
						// [0]=CurMapIndex, [1]=NextMapIndex, [2]=IsListModified
						$this->releaseEvent('onMapListModified', $call[1]);
						break;

					case 'ManiaPlanet.BillUpdated':
						// [0]=BillId, [1]=State, [2]=StateName, [3]=TransactionId
						$this->releaseEvent('onBillUpdated', $call[1]);
						break;

					case 'ManiaPlanet.PlayerAlliesChanged':
						// [0]=Login
						$this->releaseEvent('onPlayerAlliesChanged', $call[1]);
						break;


					case 'ManiaPlanet.PlayerIncoherence':
						// [0]=PlayerUid, [1]=Login
						$this->releaseEvent('onPlayerIncoherence', $call[1]);
						break;

					case 'ManiaPlanet.TunnelDataReceived':
						// [0]=PlayerUid, [1]=Login, [2]=Data
						$this->releaseEvent('onTunnelDataReceived', $call[1]);
						break;

					case 'ManiaPlanet.Echo':
						// [0]=Internal, [1]=Public
						$this->releaseEvent('onEcho', $call[1]);
						break;

					case 'ManiaPlanet.VoteUpdated':
						// [0]=StateName, [1]=Login, [2]=CmdName, [3]=CmdParam
						$this->releaseEvent('onVoteUpdated', $call[1]);
						break;

					case 'ManiaPlanet.ModeScriptCallback':
						// [0]=Param1, [1]=Param2
						$this->releaseEvent('onModeScriptCallback', $call[1]);
						break;

					case 'ManiaPlanet.ModeScriptCallbackArray':
						// [0]=Param1, [1]=Param2
						$this->releaseEvent('onModeScriptCallbackArray', $call[1]);
						break;

					default:
						// do nothing
				}
			}
			return $calls;
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function executeMulticall () {

		// Sends multiquery to the server
		try {
			$this->client->multiquery();
		}
		catch (Exception $exception) {
			trigger_error('ExecuteMulticall XmlRpc Error ['. $exception->getCode() .'] - '. $exception->getMessage(), E_USER_ERROR);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// When a new map is started we have to get information about the new map,
	// get record to current map etc.
	public function beginMap ($uid) {

		// Setup race status
		$this->server->gamestate = Server::RACE;

		// Check for changing the daily logfile
		if ($this->logfile['file'] != './logs'.DIRECTORY_SEPARATOR.date('Y-m-d').'-current.txt') {
			// Setup new logfile
			$this->setupLogfile();
		}

		// Cleanup Player rankings
		$this->server->rankings->reset();

		// Check for restarting map
		$this->changing_to_gamemode = false;
		if ($this->restarting == true) {

			// Throw postfix 'restart map' event
			if ($this->settings['developer']['log_events']['common'] == true) {
				$this->console('[Event] Restart Map');
			}
			$this->releaseEvent('onRestartMap', $uid);
			return;
		}

		// Refresh the current round point system (only Rounds, Team and Cup)
		if ($this->server->gameinfo->mode == Gameinfo::ROUNDS || $this->server->gameinfo->mode == Gameinfo::TEAM || $this->server->gameinfo->mode == Gameinfo::CUP) {
			$this->client->query('TriggerModeScriptEvent', 'Rounds_GetPointsRepartition', '');
		}

		// Setup previous map
		$this->server->maps->previous = $this->server->maps->current;

		// Get current map object
		$map = $this->server->maps->getCurrentMapInfo();

		// Search MX for map
		if ( ($map->mx == false) || (time() > ($map->mx->timestamp_fetched + $this->server->maps->max_age_mxinfo)) ) {
			$response = new MXInfoFetcher('TM2', $map->uid, true);
			if ($response->error == '') {
				$map->mx = $response;
			}
		}
		else if ($this->debug) {
			$this->console('[Map] MX infos cached, last fetched at '. date('Y-m-d H:i:s', $map->mx->timestamp_fetched));
		}

		// Report usage back to home website
		$this->reportServerInfo();

		// Refresh game info
		$this->server->getCurrentGameInfo();

		// Refresh server name and options
		$this->server->updateServerOptions();

		// Log debug information
		if ($this->debug) {
			$this->logDebugInformations();
		}

		// Log console message
		if ($this->server->maps->current->uid == $map->uid) {
			$this->console("[Map] Running on [{1}] made by [{2}] [Env: '{3}', Uid: '{4}', Id: {5}]",
				$this->stripColors($map->name, false),
				$this->stripColors($map->author, false),
				$map->environment,
				$map->uid,
				$map->id
			);
		}
		else {
			$this->console("[Map] Changing from [{1}] to [{2}] [Env: '{3}', Uid: '{4}', Id: {5}]",
				$this->stripColors($this->server->maps->current->name, false),
				$this->stripColors($map->name, false),
				$map->environment,
				$map->uid,
				$map->id
			);
		}

		// Update the field which contains current map
		$this->server->maps->current = $map;

		// Throw main 'begin map' event
		if ($this->settings['developer']['log_events']['common'] == true) {
			$this->console('[Event] Begin Map');
		}
		$this->releaseEvent('onBeginMap', $map);

		// Throw postfix 'begin map' event (various)
		$this->releaseEvent('onBeginMap1', $map);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function endMap ($map) {

		$this->server->gamestate = Server::SCORE;

		// Throw 'end map' event (Records)
		$this->releaseEvent('onEndMapRanking', $this->server->maps->current);

		if (!$this->server->isrelay) {

			// Check out who won the current map and increment his/her wins by one.
			if (count($this->server->rankings->ranking_list) > 1) {
				$rank = array();
				foreach ($this->server->rankings->ranking_list as $pl => $data) {
					$rank = $data;
					break;
				}
				if (($player = $this->server->players->getPlayer($rank->login)) !== false) {
					// Check for winner if there's more than one player
					if ( ($this->server->gameinfo->mode == Gameinfo::STUNTS ? ($rank->score > 0) : ($rank->time > 0)) ) {
						// Increase the player's wins
						$player->newwins++;

						// Log console message
						$this->console('[Rank] Player [{1}] won for the {2}. time!',
							$player->login,
							$player->getWins()
						);

						if ($player->getWins() % $this->settings['global_win_multiple'] == 0) {
							// Replace parameters
							$message = $this->formatText($this->getChatMessage('WIN_MULTI'),
								$this->stripColors($player->nickname),
								$player->getWins()
							);

							// Show chat message
							$this->sendChatMessage($message);
						}
						else {
							// Replace parameters
							$message = $this->formatText($this->getChatMessage('WIN_NEW'),
								$player->getWins()
							);

							// Show chat message
							$this->sendChatMessage($message, $player->login);
						}

						// Throw 'player wins' event
						$this->releaseEvent('onPlayerWins', $player);
					}
				}
			}
		}

		if ($this->settings['developer']['log_events']['common'] == true) {
			$this->console('[Event] End Map');
		}

		// Throw prefix 'end map' event (chat-based votes)
		$this->releaseEvent('onEndMap1', $this->server->maps->current);

		// Throw main 'end map' event
		$this->releaseEvent('onEndMap', $this->server->maps->current);

		// Reset status before begin map
		$this->restarting == false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles connections of new players.
	public function playerConnect ($login, $is_spectator) {

		// Request information about the new player
		$details = $this->client->query('GetDetailedPlayerInfo', $login);
		$info = $this->client->query('GetPlayerInfo', $login, 1);
		$data = array_merge($details, $info);
		unset($details, $info);

		// Check for server
		if (isset($data['Flags']) && floor($data['Flags'] / 100000) % 10 != 0) {
			// register relay server
			if (!$this->server->isrelay && $data['Login'] != $this->server->login) {
				$this->server->relay_list[$data['Login']] = $data;

				// log console message
				$this->console('[Relay] Connect of relay server {1} ({2})',
					$data['Login'],
					$this->stripColors($data['NickName'], false)
				);
			}
		}
		else if ($this->server->isrelay && floor($data['Flags'] / 10000) % 10 != 0) {
			// DO NOTHING on player from master server on relay
		}
		else {
			$ipaddr = isset($data['IPAddress']) ? preg_replace('/:\d+/', '', $data['IPAddress']) : '';  // strip port

			// if no data fetched, notify & kick the player
			if (!isset($data['Login']) || $data['Login'] == '') {
				$message = str_replace('{br}', LF, $this->getChatMessage('CONNECT_ERROR'));
				$this->sendChatMessage(str_replace(LF.LF, LF, $message), $login);
				sleep(5);  // allow time to connect and see the notice
				$this->client->addCall('Kick', $login, $this->formatColors($this->getChatMessage('CONNECT_DIALOG')));
				// log console message
				$this->console('[Player] GetPlayerInfo failed for ['. $login .'] -- notified & kicked');
				return;

			}
			else if (!empty($this->banned_ips) && in_array($ipaddr, $this->banned_ips)) {
				// if player IP in ban list, notify & kick the player
				$message = str_replace('{br}', LF, $this->getChatMessage('BANIP_ERROR'));
				$this->sendChatMessage(str_replace(LF.LF, LF, $message), $login);
				sleep(5);  // allow time to connect and see the notice
				$this->client->addCall('Ban', $login, $this->formatColors($this->getChatMessage('BANIP_DIALOG')));
				$this->console('[Player] Player ['. $login .'] banned from '. $ipaddr .' -- notified & kicked');
				return;
			}
			else {
				// client version checking, extract version number
				$version = str_replace(')', '', preg_replace('/.*\(/', '', $data['ClientVersion']));
				if ($version == '') $version = '2.11.11';
				$message = str_replace('{br}', LF, $this->getChatMessage('CLIENT_ERROR'));

				// if invalid version, notify & kick the player
				if ($this->settings['player_client'] != '' && strcmp($version, $this->settings['player_client']) < 0) {
					$this->sendChatMessage($message, $login);
					sleep(5);  // allow time to connect and see the notice
					$this->client->addCall('Kick', $login, $this->formatColors($this->getChatMessage('CLIENT_DIALOG')));
					$this->console('[Player] Obsolete player client version '. $version .' for ['. $login .'] -- notified & kicked');
					return;
				}

				// if invalid version, notify & kick the admin
				if ($this->settings['admin_client'] != '' && $this->isAnyAdminL($data['Login']) && strcmp($version, $this->settings['admin_client']) < 0) {
					$this->sendChatMessage($message, $login);
					sleep(5);  // allow time to connect and see the notice
					$this->client->addCall('Kick', $login, $this->formatColors($this->getChatMessage('CLIENT_DIALOG')));
					$this->console('[Player] Obsolete admin client version '. $version .' for ['. $login .'] -- notified & kicked');
					return;
				}
			}

			// Create player object
			$player = new Player($data);

			// Get the current ranking for this player, required to have the rankings up-to-date on a running race,
			// but not in TEAM mode (requires a special handling).
			if ($this->server->gameinfo->mode == Gameinfo::TEAM) {
				// Do not $this->server->rankings->addPlayer(), because it is a "Team" mode!
				// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
				$this->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
			}
			else {
				// Add to ranking list
				$this->server->rankings->addPlayer($player);

				// Call 'LibXmlRpc_GetPlayerRanking' to get 'LibXmlRpc_PlayerRanking'
				$this->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetPlayerRanking', $player->login);
			}

			// Adds a new player to the player list
			$this->server->players->addPlayer($player);

			// Log console message
			$this->console('[Player] Connection from Player [{1}] from {2} [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
				$player->login,
				$this->country->iocToCountry($player->nation),
				$this->stripColors($player->nickname),
				$player->ip,
				$player->ladderrank,
				$player->id
			);

			// Update the Visits, but only when Player connects and not when UASECO restarts
			if ($this->startup_phase == false && $this->restarting == false) {
				$query = "UPDATE `players` SET `Visits` = `Visits` + 1 WHERE `Id` = ". $player->id ." LIMIT 1;";
				$result = $this->mysqli->query($query);
				if (!$result) {
					$this->console('[Player] UPDATE `Visits` at `players` failed. [for statement "'. $query .'"]!');
				}
			}

			// Replace parameters
			$message = $this->formatText($this->getChatMessage('WELCOME'),
				$this->stripColors($player->nickname),
				$this->server->name,
				UASECO_VERSION
			);
			// Hyperlink package name & version number
			$message = preg_replace('/UASECO.+'. UASECO_VERSION .'/', '$l['. UASECO_WEBSITE .']$0$l', $message);

			// Send welcome popup or chat message
			if ($this->settings['welcome_msg_window']) {
				$message = str_replace('{#highlite}', '{#message}', $message);
				$message = preg_split('/{br}/', $this->formatColors($message));
				// repack all lines
				foreach ($message as &$line) {
					$line = array($line);
				}
				$this->plugins['PluginManialinks']->display_manialink(
					$player->login,
					'',
					array('Icons64x64_1', 'Inbox'), $message,
					array(1.2), 'OK'
				);
			}
			else {
				$message = str_replace('{br}', LF, $message);
				$this->sendChatMessage(str_replace(LF.LF, LF, $message), $player->login);
			}

			// Throw main 'player connects' event
			$this->releaseEvent('onPlayerConnect', $player);

			// Throw postfix 'player connects' event (access control)
			$this->releaseEvent('onPlayerConnect1', $player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles disconnections of players.
	public function playerDisconnect ($player) {

		// Check for relay server
		if (!$this->server->isrelay && array_key_exists($player[0], $this->server->relay_list)) {
			// log console message
			$this->console('[Relay] Disconnect of relay server {1} ({2})',
				$player[0],
				$this->stripColors($this->server->relay_list[$player[0]]['NickName'], false)
			);

			unset($this->server->relay_list[$player[0]]);
			return;
		}

		// Delete player and put him into the player item
		// ignore event if disconnect fluke after player already left,
		// or on relay if player from master server (which wasn't added)
		if (!$player_item = $this->server->players->removePlayer($player[0])) {
			return;
		}

		// Log console message
		$this->console('[Player] Disconnection from Player [{1}] after {2} playtime [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
			$player_item->login,
			$this->formatTime($player_item->getTimeOnline() * 1000, false),
			$this->stripColors($player_item->nickname, false),
			$player_item->ip,
			$player_item->ladderrank,
			$player_item->id
		);

		// Throw 'player disconnects' event
		$this->releaseEvent('onPlayerDisconnect', $player_item);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Player reaches finish.
	public function playerFinish ($login, $score) {

		// If no map info bail out immediately
		if ($this->server->maps->current->name == '') {
			return;
		}

		// If relay server or not in Play status, bail out immediately
		if ($this->server->isrelay || $this->current_status != 4) {
			return;
		}

		// Check for valid player
		if ((!$player = $this->server->players->getPlayer($login)) || $player->login == '') {
			return;
		}

		// Build a record object with the current finish information
		$finish			= new Record();
		$finish->player		= $player;
		$finish->score		= $score;
		$finish->checkpoints	= (isset($this->checkpoints[$player->login]) ? $this->checkpoints[$player->login]->current['cps'] : array());
		$finish->date		= strftime('%Y-%m-%d %H:%M:%S');
		$finish->new		= false;
		$finish->map		= clone $this->server->maps->current;
		unset($finish->map->mx);	// reduce memory usage

		// Throw prefix 'player finishes' event (checkpoints)
		$this->releaseEvent('onPlayerFinish1', $finish);

		// Throw main 'player finishes' event
		$this->releaseEvent('onPlayerFinish', $finish);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Receives chat messages and reacts on them, reactions are done by the chat plugins.
	public function playerChat ($chat) {
		// Verify login
		if ($chat[1] == '' || $chat[1] == '???') {
			$this->console('[Chat] WARN: PlayerUid [{1}], with login [{2}] attempted to use chat command "{3}"',
				$chat[0],
				$chat[1],
				$chat[2]
			);
			return;
		}

		// Ignore master server messages on relay
		if ($this->server->isrelay && $chat[1] == $this->server->relaymaster['Login']) {
			return;
		}

		// Check for chat command '/' prefix
		$command = $chat[2];
		if ($command != '' && $command[0] == '/') {
			// Remove '/' prefix
			$command = substr($command, 1);

			// Split strings at spaces and add them into an array
			$params = explode(' ', $command, 2);

			// Insure parameter exists & is trimmed
			if (isset($params[1])) {
				$params[1] = trim($params[1]);
			}
			else {
				$params[1] = '';
			}

			// Make chat-command lowercase and trim it
			$command = strtolower(trim($params[0]));

			// Get & verify player object
			$caller = $this->server->players->getPlayer($chat[1]);
			if ($caller->login != '') {
				if (!empty($this->registered_chatcmds[$command])) {
					$allowed = false;
					if ($this->registered_chatcmds[$command]['rights'] & Player::PLAYERS) {
						// Chat command is allowed for everyone
						$allowed = true;
					}
					else if ( ($this->registered_chatcmds[$command]['rights'] & Player::OPERATORS) && ($this->isOperator($caller) || $this->isAdmin($caller) || $this->isMasterAdmin($caller)) ) {
						// Chat command is only allowed for Operators, Admins or MasterAdmins
						$allowed = true;
					}
					else if ( ($this->registered_chatcmds[$command]['rights'] & Player::ADMINS) && ($this->isAdmin($caller) || $this->isMasterAdmin($caller)) ) {
						// Chat command is only allowed for Admins or MasterAdmins
						$allowed = true;
					}
					else if ( ($this->registered_chatcmds[$command]['rights'] & Player::MASTERADMINS) && ($this->isMasterAdmin($caller)) ) {
						// Chat command is only allowed for MasterAdmins
						$allowed = true;
					}
					if ($allowed == true) {
						// log console message
						if (empty($params[1])) {
							$this->console('[Chat] Player [{1}] used command "/{2}"',
								$caller->login,
								$command
							);
						}
						else {
							$this->console('[Chat] Player [{1}] used command "/{2} {3}"',
								$caller->login,
								$command,
								$params[1]
							);
						}

						// call the function which belongs to the command
						call_user_func($this->registered_chatcmds[$command]['callback'],
							$this,						// $aseco
							$caller->login,					// Login from caller
							$command,					// "/" stripped chat command
							((isset($params[1])) ? trim($params[1]) : '')	// given parameters like "/admin setgamemode timeattack"
						);
					}
					else if ($this->settings['log_all_chat']) {
						// Optionally log attempts of chat commands from players with insufficient rights too
						$rights = 'Everyone';
						if ($this->registered_chatcmds[$command]['rights'] & Player::OPERATORS) {
							$rights = 'Operators';
						}
						else if ($this->registered_chatcmds[$command]['rights'] & Player::ADMINS) {
							$rights = 'Admins';
						}
						else if ($this->registered_chatcmds[$command]['rights'] & Player::MASTERADMINS) {
							$rights = 'MasterAdmins';
						}
						$this->console('[Chat] RESTRICTED: Player [{1}] attempted to use command "{2}" which requires min. rights of "{3}"!',
							$caller->login,
							$this->stripColors($chat[2], false),
							$rights
						);
					}
				}
				else if ($params[0] == 'version' || $params[0] == 'serverlogin') {
					// Log built-in commands fomr dedicated server
					$this->console('[Chat] Player [{1}] used built-in command "/{2}"',
						$caller->login,
						$command
					);
				}
				else if ($this->settings['log_all_chat']) {
					// Optionally log bogus chat commands too
					$this->console('[Chat] NOTICE: Player [{1}] (Id: {2}) attempted to use command "{3}"',
						$caller->login,
						$chat[0],
						$this->stripColors($chat[2], false)
					);
				}
			}
			else {
				$this->console('[Chat] WARN: Player object for [{1}] not found, but was attempted to use command "/{2} {3}"',
					$chat[1],
					$command,
					$params[1]
				);
			}
		}
		else {
			// optionally log all normal chat too
			if ($this->settings['log_all_chat']) {
				if ($chat[2] != '') {
					$this->console('[Chat] NOTICE: Player [{1}] (Id: {2}) attempted to use command "{3}"',
						$chat[1],
						$chat[0],
						$this->stripColors($chat[2], false)
					);
				}
			}
		}

		$this->releaseEvent('onPlayerChat', $chat);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// When a player's info changed, signal the event. Fields:  Login, NickName, PlayerId, TeamId, SpectatorStatus, LadderRanking, Flags
	public function playerInfoChanged ($playerinfo) {

		// On relay, check for player from master server
		if ($this->server->isrelay && floor($playerinfo['Flags'] / 10000) % 10 != 0) {
			return;
		}

		// Check for valid player
		if (!$player = $this->server->players->getPlayer($playerinfo['Login'])) {
			return;
		}

		// Check ladder ranking
		if ($playerinfo['LadderRanking'] > 0) {
			$player->ladderrank = $playerinfo['LadderRanking'];
			$player->isofficial = true;
		}
		else {
			$player->isofficial = false;
		}

		// Check spectator status (ignoring temporary changes)
		$player->previous_status = $player->isspectator;
		if (($playerinfo['SpectatorStatus'] % 10) != 0) {
			$player->isspectator = true;
		}
		else {
			$player->isspectator = false;
		}

		$this->releaseEvent('onPlayerInfoChanged', $playerinfo);
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

// Convert php.ini memory shorthand string to integer bytes
// http://www.php.net/manual/en/function.ini-get.php#96996
function shorthand2bytes ($size_str) {
	switch (substr($size_str, -1)) {
		case 'M': case 'm': return (int)$size_str * 1048576;
		case 'K': case 'k': return (int)$size_str * 1024;
		case 'G': case 'g': return (int)$size_str * 1073741824;
		default: return (int)$size_str;
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

// Define process settings
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set')) {
	date_default_timezone_set(@date_default_timezone_get());
}

setlocale(LC_NUMERIC, 'C');
mb_internal_encoding('UTF-8');

$limit = shorthand2bytes(ini_get('memory_limit'));
if ( ($limit != -1) && ($limit < 192 * 1048576) ) {
	ini_set('memory_limit', '192M');
}

// Create an instance of UASECO and run it
$aseco = new UASECO();
$aseco->run();

?>
