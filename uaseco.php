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
 *   Many parts has been rewritten in 2017 again because of the ModeScript API
 *   changes for the Maniaplanet/4 update.
 *
 *   Visit the official site from this fork at http://www.UASECO.org/
 *
 * » Original project:
 *   Authored & copyright Aug 2011 - May 2013 by Xymph <tm@gamers.org>
 *   Derived from XAseco (formerly ASECO/RASP) by Xymph, Flo and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Copyright:	May 2014 - May 2018 by undef.de
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
	define('UASECO_NAME',			'UASECO');
	define('UASECO_VERSION',		'0.9.6');
	define('UASECO_BUILD',			'2018-05-04');
	define('UASECO_WEBSITE',		'https://www.UASECO.org');

	// Setup required official dedicated server build, Api-Version and PHP-Version
	define('MANIAPLANET_BUILD_POSIX',	'2018-03-29_21_00');
	define('MANIAPLANET_BUILD_WINDOWS',	'2018-03-29_21_43');
	define('XMLRPC_API_VERSION',		'2013-04-16');
	define('MODESCRIPT_API_VERSION',	'2.5.0');				// https://github.com/maniaplanet/script-xmlrpc/releases
	define('MIN_PHP_VERSION',		'5.6.0');
	define('MIN_MYSQL_VERSION',		'5.1.0');
	define('MIN_MARIADB_VERSION',		'5.5.20');

	// Setup misc.
	define('CRLF',				PHP_EOL);
	define('LF',				"\n");

	if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN') {
		define('OPERATING_SYSTEM',	'WINDOWS');
		define('MANIAPLANET_BUILD',	MANIAPLANET_BUILD_WINDOWS);
	}
	else {
		define('OPERATING_SYSTEM',	'POSIX');
		define('MANIAPLANET_BUILD',	MANIAPLANET_BUILD_POSIX);
	}

	// Report all
	error_reporting(-1);

	date_default_timezone_set(@date_default_timezone_get());
	setlocale(LC_NUMERIC, 'C');
	mb_internal_encoding('UTF-8');

	// Init random generator
	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));


	// Include required classes
	require_once('includes/core/baseclass.class.php');		// Base class
	require_once('includes/core/helper.class.php');			// Misc. functions for UASECO, e.g. $aseco->console()... based upon basic.inc.php
	require_once('includes/core/XmlRpc/GbxRemote.php');		// https://github.com/maniaplanet/dedicated-server-api
	require_once('includes/core/webrequest.class.php');
	require_once('includes/core/xmlparser.class.php');
	require_once('includes/core/gbxdatafetcher.class.php');		// Provides access to GBX data
	require_once('includes/core/mxinfofetcher.class.php');		// Provides access to ManiaExchange info
	require_once('includes/core/continent.class.php');
	require_once('includes/core/country.class.php');
	require_once('includes/core/database.class.php');
	require_once('includes/core/locales.class.php');		// Required by includes/core/message.class.php
	require_once('includes/core/message.class.php');
	require_once('includes/core/gameinfo.class.php');		// Required by includes/core/server.class.php
	require_once('includes/core/server.class.php');
	require_once('includes/core/dependence.class.php');		// Required by includes/core/plugin.class.php
	require_once('includes/core/plugin.class.php');
	require_once('includes/core/dialog.class.php');
	require_once('includes/core/window.class.php');			// Required by includes/core/windowlist.class.php
	require_once('includes/core/windowlist.class.php');
	require_once('includes/core/player.class.php');
	require_once('includes/core/playerlist.class.php');
	require_once('includes/core/playlist.class.php');		// Holds the Playlist (aka Jukebox) for Maps
	require_once('includes/core/checkpoint.class.php');
	require_once('includes/core/record.class.php');
	require_once('includes/core/recordlist.class.php');
	require_once('includes/core/ranking.class.php');		// Required by includes/core/rankinglist.class.php
	require_once('includes/core/rankinglist.class.php');
	require_once('includes/core/map.class.php');			// Required by includes/core/maplist.class.php
	require_once('includes/core/maplist.class.php');
	require_once('includes/core/maphistory.class.php');		// Holds the Maphistory


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
	public $webrequest;
	public $db;
	public $locales;
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
	public $current_status;					// server status changes
	public $characters;

	public $environments = array(
		'Canyon',
		'Stadium',
		'Valley',
		'Lagoon',
	);

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

		$this->console('####[INIT]###########################################################################');

		if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
			$this->console('[ERROR] UASECO requires min. PHP/'. MIN_PHP_VERSION .' and can not run with current PHP/'. PHP_VERSION .', please update PHP!');
			die();
		}


		$extensions = array(
			array(
				'required'	=> false,
				'extension'	=> 'exif',
				'name'		=> 'Exchangeable Image Information',
				'link'		=> 'http://php.net/manual/en/book.exif.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'ftp',
				'name'		=> 'File Transfer Protocol',
				'link'		=> 'http://php.net/manual/en/book.ftp.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'iconv',
				'name'		=> 'ICONV character set conversion facility',
				'link'		=> 'http://php.net/manual/en/book.iconv.php',
			),

			array(
				'required'	=> true,
				'extension'	=> 'gd',
				'name'		=> 'Image Processing and GD',
				'link'		=> 'http://php.net/manual/en/book.image.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'json',
				'name'		=> 'JavaScript Object Notation',
				'link'		=> 'http://php.net/manual/en/book.json.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'libxml',
				'name'		=> 'LibXML',
				'link'		=> 'http://php.net/manual/en/book.libxml.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'mbstring',
				'name'		=> 'Multibyte String',
				'link'		=> 'http://php.net/manual/en/book.mbstring.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'mysqli',
				'name'		=> 'MySQL Improved',
				'link'		=> 'http://php.net/manual/en/book.mysqli.php',
			),
			array(
				'required'	=> true,
				'extension'	=> 'SimpleXML',
				'name'		=> 'SimpleXML',
				'link'		=> 'http://php.net/manual/en/book.simplexml.php',
			),
		);

		$this->console('[PHP] Checking for required PHP extensions...');
		$this->console('[PHP] PHP is using "'. php_ini_loaded_file() .'"');
		$pass = true;
		foreach ($extensions as $item) {
			$found = extension_loaded($item['extension']);

			$msg = '[PHP] » Checking "'. $item['name'] .'" ('. $item['extension'] .'): ';
			if ($found === false && $item['required'] === true) {
				$msg .= 'extension not loaded, it is REQUIRED to enable this extension.. See "'. $item['link'] .'" for installation details.';
				$pass = false;
			}
			else if ($found === false && $item['required'] === false) {
				$msg .= 'extension not loaded, it is RECOMMENDED to enable this extension. See "'. $item['link'] .'" for installation details.';
			}
			else if ($found === true) {
				$msg .= 'OK!';
			}
			$this->console($msg);
		}
		if ($pass === false) {
			$this->console('[PHP] » Please enable the required PHP extensions and try again!');
			die();
		}

		// Extended characters list from askuri: https://forum.maniaplanet.com/viewtopic.php?p=266201#p266201
		$this->characters = array(
			'a'	=> explode(' ', '@ 4 À Á Â Ã Ä Å ª à á â ã ä å ƛ Ǎ ǎ Ǟ ǟ Ǻ ǻ Ā ā Ă ă Ą ą А Д а д Ѧ ѧ ג Ά Α Δ Λ ά α λ'),
			'b'	=> explode(' ', 'Þ ß þ ƀ Б В Ъ Ь в ь ъ ѣ Ѣ Β β ϐ'),
			'c'	=> explode(' ', '¢ © Ç ç Ć ć Ĉ ĉ Ċ ċ Č č С с Ҁ ҁ Ҫ ҫ ζ ς'),
			'd'	=> explode(' ', 'Ð ð Ď ď Đ đ δ の פ'),
			'e'	=> explode(' ', 'È Ê É Ë è é ê ë Ə Ē ē Ĕ ĕ Ė ė Ę ę Ě ě Ё Є Е е ё є Ҽ ҽ Ҿ ҿ Έ Ε Ξ Σ έ ε ξ ミ ɛ ϵ'),
			'f'	=> explode(' ', 'Ƒ ƒ Ŧ Ғ ғ'),
			'g'	=> explode(' ', 'Ǥ Ǧ ǥ ǧ ǵ Ĝ ĝ Ğ ğ Ġ ġ Ģ ģ'),
			'h'	=> explode(' ', 'Ĥ ĥ Ħ ħ Н Ч н ч ђ ћ Ң ң Ҥ ҥ Һ һ Ӈ ӈ Ή Η'),
			'i'	=> explode(' ', 'Ì Í Î Ï ì í î ï Ǐ ǐ ǰ Ĩ ĩ Ī ī Ĭ ĭ Į į İ ı ĺ ļ ľ ŀ ł І Ї ї і ׀ Ί ΐ Ι Ϊ ί ι ϊ エ エ'),
			'j'	=> explode(' ', 'ǰ ĵ Ĵ Ј ј'),
			'k'	=> explode(' ', 'Ǩ ǩ ĸ ķ Ķ Ќ К к ќ Қ қ Ҝ ҝ Ҟ ҟ Ҡ ҡ Ӄ ӄ Κ κ'),
			'l'	=> explode(' ', 'Ĺ ĺ Ļ ļ Ľ ľ Ŀ ŀ Ł ł І ׀ し じ ム レ'),
			'm'	=> explode(' ', 'М м Μ'),
			'n'	=> explode(' ', 'Ñ ñ Ń ń Ņ ņ Ň ň ŉ Ŋ ŋ Й И Л П и й л п ה ח מ ת Ν ή η'),
			'o'	=> explode(' ', 'Ò Ó Ô Õ Ö Ø ð ò ó ô õ ö ø Ɵ Ơ ơ Ǒ ǒ ǫ Ǫ Ǭ ǭ Ǿ ǿ Ō ō Ŏ ŏ Ő ő Ф О о Ѳ ѳ Ѻ ѻ ט ס Ό Ώ Θ Ο Φ Ω θ ο σ φ ό ϕ 〇 °'),
			'p'	=> explode(' ', 'Þ þ Р р ק Ρ ρ ア ァ ヤ ャ'),
			'q'	=> explode(' ', 'Ǫ ǫ Ǭ ǭ'),
			'r'	=> explode(' ', '® Ŕ ŕ Ŗ ŗ Ř ř Ѓ Г Я г я ѓ Γ'),
			's'	=> explode(' ', '§ Ś ś Ŝ ŝ Ş ş Š š Ѕ ѕ ς'),
			't'	=> explode(' ', 'Ɨ ƚ ƫ Ʈ ł Ţ ţ Ť ť ŧ Т т Ҭ ҭ ד Τ τ て 〒 〶 ィ イ'),
			'u'	=> explode(' ', 'Ù Ú Û Ü ù ú û ü ý Ư ư Ǔ ǔ Ǖ ǖ Ǘ ǘ Ǚ ǚ ǜ Ǜ Ĳ Ũ ũ Ū ū Ŭ ŭ Ů ů Ű ű Ų ų Џ Ц ц ט ΰ μ υ ϋ ύ ひ び ぴ'),
			'v'	=> explode(' ', 'Ѵ ѵ Ѷ ѷ ν'),
			'w'	=> explode(' ', 'Ŵ ŵ Ш Щ ш щ Ѡ ѡ Ѽ ѽ Ѿ ѿ ω ώ ϖ'),
			'x'	=> explode(' ', '× æ ǣ ǽ Ж Х ж х Җ җ Ҳ ҳ Ӂ ӂ Χ χ メ'),
			'y'	=> explode(' ', '¥ Ý ÿ Ŷ ŷ Ÿ Ў У у ў Ү ү Ұ ұ ע ץ Ύ Ϋ γ ϒ ϓ ϔ'),
			'z'	=> explode(' ', 'Ƶ ƶ Ʒ Ǯ ǯ Ź ź Ż ż Ž ž Ζ'),
			'0'	=> explode(' ', 'º ʘ'),
			'1'	=> explode(' ', '¹'),
			'2'	=> explode(' ', '²'),
			'3'	=> explode(' ', '³ Ʒ Ǯ ǯ З Э з Ѯ ѯ Ҙ ҙ ɝ ɜ ヨ ョ ʒ ʓ ϶'),
			'4'	=> explode(' ', 'Ч ч'),
			'6'	=> explode(' ', 'б'),
		);
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
		$this->locales			= new Locales();
		$this->client			= new GbxRemote();			// includes/core/XmlRpc/GbxRemote.php
		$this->parser			= new XmlParser();
		$this->webrequest		= new WebRequest();
		$this->continent		= new Continent();
		$this->country			= new Country();
		$this->server			= new Server('127.0.0.1', 5000, 'SuperAdmin', 'SuperAdmin');
		$this->windows			= new WindowList($this);
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

		// Setup config file
		$config_file = 'config/UASECO.xml';

		// Load new settings, if available
		$this->console('[Config] Load settings [{1}]', $config_file);
		$this->loadSettings($config_file);

		// Initialize further
		$this->server->maps		= new MapList($this->debug, $this->settings['developer']['force_maplist_update']);
		$this->server->maps->history	= new MapHistory($this->debug, $this->settings['max_history_entries']);
		$this->server->maps->playlist	= new PlayList($this->debug);
		$this->server->players		= new PlayerList($this->debug);
		$this->server->rankings		= new RankingList($this->debug);
		$this->server->mutelist		= array();

		// Load admin/operator/ability lists, if available
		$this->console('[Config] Load admin and operator lists [{1}]', $this->settings['adminops_file']);
		$this->readLists();

		// Load banned IPs list, if available
		$this->console('[Config] Load banned IPs list [{1}]', $this->settings['bannedips_file']);
		$this->readIPs();

		// Setup PHP memory_limit
		$limit = $this->shorthand2bytes(ini_get('memory_limit'));
		if ($limit !== -1) {
			ini_set('memory_limit', $this->settings['memory_limit']);
		}
		$limit = $this->shorthand2bytes(ini_get('memory_limit'));
		if ($limit !== -1 && $limit < 256 * 1048576) {
			ini_set('memory_limit', '256M');
		}
		$limit = $this->shorthand2bytes(ini_get('memory_limit'));
		if ($limit === -1) {
			$this->console('[PHP] Setup memory limit to unlimited');
		}
		else {
			$this->console('[PHP] Setup memory limit to '. $this->bytes2shorthand($limit, 'M'));
		}

		// Setup PHP script_timeout
		@set_time_limit($this->settings['script_timeout']);
		$this->console('[PHP] Setup script timeout to '. $this->settings['script_timeout'] .' second'. ($this->settings['script_timeout'] === 1 ? '' : 's'));

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
		if ($this->settings['mask_password'] === true) {
			$this->console("[Database] Try to connect to database server on [{1}] with database [{2}], login [{3}] and password [{4}] (masked password)",
				$this->settings['dbms']['host'],
				$this->settings['dbms']['database'],
				$this->settings['dbms']['login'],
				preg_replace('#.#', '*', $this->settings['dbms']['password'])
			);
		}
		else {
			$this->console("[Database] Try to connect to database server on [{1}] with database [{2}], login [{3}] and password [{4}]",
				$this->settings['dbms']['host'],
				$this->settings['dbms']['database'],
				$this->settings['dbms']['login'],
				$this->settings['dbms']['password']
			);
		}
		$this->connectDatabase();
		$this->displayLoadStatus('Connection established successfully!', 0.1);

		// Check database structure
		$this->displayLoadStatus('Checking database structure...', 0.15);
		$this->checkDatabaseStructure();
		$this->displayLoadStatus('Structure successfully checked!', 1.0);

		// Load plugins and register chat commands
		$this->console('[Plugin] Loading plugins [config/plugins.xml]');
		$this->loadPlugins();

		// Register own onShutdown() function
		$this->registerEvent('onShutdown', array($this, 'onShutdown'));

		// Log admin lock message
		if ($this->settings['lock_password'] !== '') {
			if ($this->settings['mask_password'] === true) {
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
				// Fake it into thinking it's a connecting Player:
				// It gets team and ladder info this way and will also throw an
				// onPlayerConnect event for Players (not relays) to all Plugins
				$this->playerConnect($player['Login'], false);
			}
		}
		unset($playerlist);


		// Get current game infos if server loaded a map yet
		if ($this->current_status === 100) {
			$this->console('[UASECO] Waiting for the server to start a map...');
		}
		else {
			$this->loadingMap($this->server->maps->current->uid);
		}


		// Startup done
		$this->startup_phase = false;
		$this->displayLoadStatus(false);


		// Main loop
		while (true) {
			$starttime = microtime(true);

			if ($this->shutdown_phase === false) {
				// Get callbacks from the server
				$this->executeCallbacks();

				// Sends calls to the server
				$this->executeMulticall();
			}

			// Throw timing events
			$this->releaseEvent('onMainLoop', null);

			if (time() >= $this->next_second) {
				$this->webrequest->update();
				$this->next_second = (time() + 1);
				$this->releaseEvent('onEverySecond', null);
			}

			if (time() >= $this->next_tenth) {
				// Check for Database connection and reconnect on lost connection
				if ($this->db->ping() === false) {
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

		// Get basic server info, server id, login, nickname, zone, name, options, mode, limits...
		$this->server->getServerSettings();

		// Add 'POWERED BY UASECO' to server comment
		if (strpos($this->stripStyles($this->server->comment, true), 'POWERED BY UASECO') === false) {
			$this->client->query('SetServerComment', $this->server->comment ."\n". '$Z$O$FFFPOWERED BY $0AF$L['. UASECO_WEBSITE .']'. UASECO_NAME .'$L');
		}

		// Check server build
		if (strlen($this->server->build) === 0 || ($this->server->game === 'ManiaPlanet' && strcmp($this->server->build, MANIAPLANET_BUILD) < 0)) {
			trigger_error("Obsolete server build '". $this->server->build ."' - must be at least '". MANIAPLANET_BUILD ."'!", E_USER_ERROR);
		}

		// Create a USER_AGENT string
		define('USER_AGENT', UASECO_NAME .'/'. UASECO_VERSION .'_'. UASECO_BUILD .' '. $this->server->game .'/'. $this->server->build .' php/'. phpversion() .' '. php_uname());

		// Get status
		$status = $this->client->query('GetStatus');
		$this->current_status = $status['Code'];
		unset($status);

		// Get all Maps from server
		$this->console('[MapList] Reading complete map list from server...');
		$this->server->maps->readMapList();
		$count = count($this->server->maps->map_list);
		$this->console('[MapList] ...successfully done, read '. $count .' map'. ($count === 1 ? '' : 's') .' which matches server settings!');

		// Load MapHistory
		$this->console('[Playlist] Reading map history...');
		$this->server->maps->history->readMapHistory();
		$this->console('[Playlist] ...successfully done!');

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

		$max_execution_time = ini_get('max_execution_time') .' second'. (ini_get('max_execution_time') === 1 ? '' : 's');
		$wrappers = stream_get_wrappers();
		sort($wrappers, SORT_STRING);
		$gd = gd_info();

		$this->console_text('####[ABOUT]##########################################################################');
		$this->console_text('» Server:        {1} ({2}), join link: "maniaplanet://#join={3}@{4}"', $this->stripStyles($this->server->name, false), $this->server->login, $this->server->login, $this->server->title);
		if ($this->server->isrelay) {
			$this->console_text('=> Relays:        {1} - {2}', $this->stripStyles($this->server->relaymaster['NickName'], false), $this->server->relaymaster['Login']);
		}
		$this->console_text('» Title:         {1}', $this->server->title);
		$this->console_text('» Gamemode:      "{1}" with script "{2}" version "{3}"', str_replace('_', '', $this->server->gameinfo->getModeName()), $this->server->gameinfo->getModeScriptName(), $this->server->gameinfo->getModeVersion());
		$this->console_text('» Dedicated:     {1}/{2} build {3}, using Method-API {4}, ModeScript-API {5}', $this->server->game, $this->server->version, $this->server->build, $this->server->api_version, MODESCRIPT_API_VERSION);
		$this->console_text('»                MatchSettings: {1}', $this->settings['default_maplist']);
		$this->console_text('»                Ports: Connections {1}, P2P {2}, XmlRpc {3}', $this->server->port, $this->server->p2pport, $this->server->xmlrpc['port']);
		$this->console_text('»                Network: Send {1} KB, Receive {2} KB', $this->formatNumber($this->server->networkstats['TotalSendingSize'],0,',','.'), $this->formatNumber($this->server->networkstats['TotalReceivingSize'],0,',','.'));
		$this->console_text('»                Uptime: {1}', $this->timeString($this->server->networkstats['Uptime']));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» UASECO:        Version {1} build {2}, running on {3}:{4}', UASECO_VERSION, UASECO_BUILD, $this->server->xmlrpc['ip'], $this->server->xmlrpc['port'] .',');
    		$this->console_text('»                based upon the work of the authors and projects of:');
    		$this->console_text('»                - Xymph (XAseco2),');
    		$this->console_text('»                - Florian Schnell, AssemblerManiac and many others (ASECO),');
    		$this->console_text('»                - Kremsy (MPASECO)');
		$this->console_text('» Author:        undef.de (UASECO)');
		$this->console_text('» Website:       {1}', UASECO_WEBSITE);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» OS:            {1}', php_uname());
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» PHP:           PHP/{1}', phpversion());
		$this->console_text('»                INI-File: {1}', php_ini_loaded_file());
		$this->console_text('»                MemoryLimit: {1}', ini_get('memory_limit'));
		$this->console_text('»                MaxExecutionTime: {1}', $max_execution_time);
		$this->console_text('»                AllowUrlFopen: {1}', $this->bool2string((ini_get('allow_url_fopen') === 1 ? true : false)));
		$this->console_text('»                Streams: {1}', implode(', ', $wrappers));
		$this->console_text('»                GD-Lib: Version: {1}, JPEG: {2}, PNG: {3}, FreeType: {4}', $gd['GD Version'], $this->bool2string($gd['JPEG Support']), $this->bool2string($gd['PNG Support']), $this->bool2string($gd['FreeType Support']));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Database:      Server:  {1}', $this->db->server_version());
		$this->console_text('»                Client:  {1}', $this->db->client_version());
		$this->console_text('»                Connect: {1}', $this->db->connection_info());
		$this->console_text('»                Status:  {1}', $this->db->host_status());
		$this->console_text('#####################################################################################');

		// Format the text of the message
		$message = $this->formatText($this->getChatMessage('STARTUP'),
			UASECO_VERSION,
			UASECO_BUILD,
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

		$max_execution_time = ini_get('max_execution_time') .' second'. (ini_get('max_execution_time') === 1 ? '' : 's');
		$wrappers = stream_get_wrappers();
		sort($wrappers, SORT_STRING);
		$gd = gd_info();

		$this->console_text('####[DEBUG]##########################################################################');
		$this->console_text('» StartupPhase:  {1}', $this->bool2string($this->startup_phase));
		$this->console_text('» WarmupPhase:   {1}', $this->bool2string($this->warmup_phase));
		$this->console_text('» Restarting:    {1}', $this->bool2string($this->restarting));
		$this->console_text('» CurrentStatus: [{1}] {2}', $this->current_status, $this->server->state_names[$this->current_status]);
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Uptime:        {1}', $this->timeString(time() - $this->uptime));
		if ( function_exists('sys_getloadavg') ) {
			$this->console_text('» System Load:   {1}', implode(', ', sys_getloadavg()));
		}
		$this->console_text('» MEM-Usage:     {1} MB, PEAK: {2} MB', round(memory_get_usage() / pow(1024,2), 4), round(memory_get_peak_usage() / pow(1024,2), 4));
		if (function_exists('posix_getuid') && function_exists('posix_getgid')) {
			$this->console_text('» Process user:  {1}:{2} (UID/GID)', posix_getuid(), posix_getgid());
		}
		$this->console_text('» Script owner:  {1}:{2} (UID/GID)', getmyuid(), getmygid());
		$this->console_text('» -----------------------------------------------------------------------------------');
//		$this->console_text('» NbPlugins:     {1} : {2} bytes', sprintf("%5s", count($this->plugins)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->plugins)),0,'.','.')));				// serialize() on SimpleXMLElement are bad
//		$this->console_text('» RegEvents:     {1} : {2} bytes', sprintf("%5s", count($this->registered_events)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->registered_events)),0,'.','.')));		// serialize() on SimpleXMLElement are bad
//		$this->console_text('» RegChatCmds:   {1} : {2} bytes', sprintf("%5s", count($this->registered_chatcmds)), sprintf("%10s", $this->formatNumber(strlen(serialize($this->registered_chatcmds)),0,'.','.')));	// serialize() on SimpleXMLElement are bad
		$this->console_text('» NbPlugins:     {1}', sprintf("%5s", count($this->plugins)));
		$this->console_text('» RegEvents:     {1}', sprintf("%5s", count($this->registered_events)));
		$this->console_text('» RegChatCmds:   {1}', sprintf("%5s", count($this->registered_chatcmds)));
		$this->console_text('» NbMaps:        {1} : {2} bytes', sprintf("%5s", $this->server->maps->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->maps->map_list)),0,'.','.')));
		$this->console_text('» NbPlayers:     {1} : {2} bytes', sprintf("%5s", $this->server->players->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->players->player_list)),0,'.','.')));
		$this->console_text('» PlayerRanks:   {1} : {2} bytes', sprintf("%5s", $this->server->rankings->count()), sprintf("%10s", $this->formatNumber(strlen(serialize($this->server->rankings->ranking_list)),0,'.','.')));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Server:        {1} ({2}), join link: "maniaplanet://#join={3}@{4}"', $this->stripStyles($this->server->name, false), $this->server->login, $this->server->login, $this->server->title);
		if ($this->server->isrelay) {
			$this->console_text('=> Relays:        {1} - {2}', $this->stripStyles($this->server->relaymaster['NickName'], false), $this->server->relaymaster['Login']);
		}
		$this->console_text('» Title:         {1}', $this->server->title);
		$this->console_text('» Gamemode:      "{1}" with script "{2}" version "{3}"', str_replace('_', '', $this->server->gameinfo->getModeName()), $this->server->gameinfo->getModeScriptName(), $this->server->gameinfo->getModeVersion());
		$this->console_text('» Dedicated:     {1}/{2} build {3}, using Method-API {4}, ModeScript-API {5}', $this->server->game, $this->server->version, $this->server->build, $this->server->api_version, MODESCRIPT_API_VERSION);
		$this->console_text('»                MatchSettings: {1}', $this->settings['default_maplist']);
		$this->console_text('»                Ports: Connections {1}, P2P {2}, XmlRpc {3}', $this->server->port, $this->server->p2pport, $this->server->xmlrpc['port']);
		$this->console_text('»                Network: Send {1} KB, Receive {2} KB', $this->formatNumber($this->server->networkstats['TotalSendingSize'],0,',','.'), $this->formatNumber($this->server->networkstats['TotalReceivingSize'],0,',','.'));
		$this->console_text('»                Uptime: {1}', $this->timeString($this->server->networkstats['Uptime']));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» OS:            {1}', php_uname());
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» PHP:           PHP/{1}', phpversion());
		$this->console_text('»                INI-File: {1}', php_ini_loaded_file());
		$this->console_text('»                MemoryLimit: {1}', ini_get('memory_limit'));
		$this->console_text('»                MaxExecutionTime: {1}', $max_execution_time);
		$this->console_text('»                AllowUrlFopen: {1}', $this->bool2string((ini_get('allow_url_fopen') === 1 ? true : false)));
		$this->console_text('»                Streams: {1}', implode(', ', $wrappers));
		$this->console_text('»                GD-Lib: Version: {1}, JPEG: {2}, PNG: {3}, FreeType: {4}', $gd['GD Version'], $this->bool2string($gd['JPEG Support']), $this->bool2string($gd['PNG Support']), $this->bool2string($gd['FreeType Support']));
		$this->console_text('» -----------------------------------------------------------------------------------');
		$this->console_text('» Database:      Server:  {1}', $this->db->server_version());
		$this->console_text('»                Client:  {1}', $this->db->client_version());
		$this->console_text('»                Connect: {1}', $this->db->connection_info());
		$this->console_text('»                Status:  {1}', $this->db->host_status());
		$this->console_text('#####################################################################################');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function logDebugPluginUsage ($list) {

		$this->console_text('####[DEBUG]##########################################################################');
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
			$this->settings['developer']['force_maplist_update']		= $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['FORCE_MAPLIST_UPDATE'][0]);
			$this->settings['developer']['write_documentation']		= $this->string2bool($settings['DEVELOPER_OPTIONS'][0]['WRITE_DOCUMENTATION'][0]);

			// Read settings and apply them
			$this->chat_colors = $settings['COLORS'][0];
			$this->chat_messages = $settings['MESSAGES'][0];
			$this->masteradmin_list = $settings['MASTERADMINS'][0];
			if (!isset($this->masteradmin_list) || !is_array($this->masteradmin_list)) {
				trigger_error('No MasterAdmin(s) configured in [config/UASECO.xml]!', E_USER_ERROR);
			}

			// Check masteradmin list consistency
			if (empty($this->masteradmin_list['IPADDRESS'])) {
				// Fill <ipaddress> list to same length as <tmlogin> list
				if (($cnt = count($this->masteradmin_list['TMLOGIN'])) > 0)
					$this->masteradmin_list['IPADDRESS'] = array_fill(0, $cnt, '');
			}
			else {
				if (count($this->masteradmin_list['TMLOGIN']) !== count($this->masteradmin_list['IPADDRESS']))
					trigger_error("MasterAdmin mismatch between <tmlogin>'s and <ipaddress>'s!", E_USER_WARNING);
			}

			// Set admin contact
			$this->settings['admin_contact'] = $settings['ADMIN_CONTACT'][0];
			if (strtolower($this->settings['admin_contact']) === 'your@email.com' || filter_var($this->settings['admin_contact'], FILTER_VALIDATE_EMAIL) === false) {
				$this->console('[UASECO][WARNING] ###############################################################################################################');
				$this->console('[UASECO][WARNING] You should setup a working mail to be able to contact you, change it in [config/UASECO.xml] at <admin_contact>!');
				$this->console('[UASECO][WARNING] ###############################################################################################################');
			}

			// Set admin lock password
			$this->settings['lock_password'] = $settings['LOCK_PASSWORD'][0];
			if (empty($this->settings['lock_password'])) {
				$this->console('[UASECO][WARNING] To increase security you should setup a lock password at <lock_password> in [config/UASECO.xml]!');
			}

			// Show played time at end of map?
			$this->settings['show_playtime'] = $settings['SHOW_PLAYTIME'][0];

			// Show current map at start?
			$this->settings['show_curmap'] = $settings['SHOW_CURMAP'][0];

			// Set default filename for readmaplist/writemaplist
			$this->settings['default_maplist'] = $settings['DEFAULT_MAPLIST'][0];

			// Add random filter to /admin writemaplist output
			$this->settings['writemaplist_random'] = $this->string2bool($settings['WRITEMAPLIST_RANDOM'][0]);

			// Automatic refresh of the maplist when the callback "ManiaPlanet.MapListModified" is received
			$this->settings['automatic_refresh_maplist'] = $this->string2bool($settings['AUTOMATIC_REFRESH_MAPLIST'][0]);

			// Specifies how large the Map(List) history buffer is.
			$this->settings['max_history_entries'] = (int)$settings['MAX_HISTORY_ENTRIES'][0];

			// Sets the minimum amount of records required for a player to be ranked: Higher = Faster
			$this->settings['server_rank_min_records'] = (int)$settings['SERVER_RANK_MIN_RECORDS'][0];

			// Setup default storing path for the map images
			$this->settings['mapimages_path'] = $settings['MAPIMAGES_PATH'][0];
			if ((OPERATING_SYSTEM === 'POSIX' && substr($this->settings['mapimages_path'], -1) !== '/') || (OPERATING_SYSTEM === 'WINDOWS' && substr($this->settings['mapimages_path'], -1) !== '\\')) {
				$this->console('[Config] Adding missing trailing "'. DIRECTORY_SEPARATOR .'" <mapimages_path> from [config/UASECO.xml]!');
				$this->settings['mapimages_path'] = $this->settings['mapimages_path'] . DIRECTORY_SEPARATOR;
			}

			// Set multiple of win count to show global congrats message
			$this->settings['global_win_multiple'] = ($settings['GLOBAL_WIN_MULTIPLE'][0] > 0 ? $settings['GLOBAL_WIN_MULTIPLE'][0] : 1);

			// Timeout of the message window in seconds
			$this->settings['window_timeout'] = $settings['WINDOW_TIMEOUT'][0];

			// Set filename of admin/operator/ability lists file
			$this->settings['adminops_file'] = $settings['ADMINOPS_FILE'][0];

			// Set filename of banned IPs list file
			$this->settings['bannedips_file'] = $settings['BANNEDIPS_FILE'][0];

			// Set filename of blacklist file
			$this->settings['blacklist_file'] = $settings['BLACKLIST_FILE'][0];

			// Set filename of guestlist file
			$this->settings['guestlist_file'] = $settings['GUESTLIST_FILE'][0];

			// Add random filter to /admin writemaplist output
			$this->settings['writemaplist_random'] = $this->string2bool($settings['WRITEMAPLIST_RANDOM'][0]);

			// Set minimum admin client version
			$this->settings['admin_client'] = $settings['ADMIN_CLIENT_VERSION'][0];

			// Set minimum player client version
			$this->settings['player_client'] = $settings['PLAYER_CLIENT_VERSION'][0];

			// Log all chat, not just chat commands ?
			$this->settings['log_all_chat'] = $this->string2bool($settings['LOG_ALL_CHAT'][0]);

			// Show timestamps in /chatlog, /pmlog & /admin pmlog ?
			$this->settings['chatpmlog_times'] = $this->string2bool($settings['CHATPMLOG_TIMES'][0]);

			// Show round reports in message window?
			$this->settings['rounds_in_window'] = $this->string2bool($settings['ROUNDS_IN_WINDOW'][0]);

			// Color nicknames in the various /top... etc lists?
			$this->settings['lists_colornicks'] = $this->string2bool($settings['LISTS_COLORNICKS'][0]);

			// Color mapnames in the various /lists... lists?
			$this->settings['lists_colormaps'] = $this->string2bool($settings['LISTS_COLORMAPS'][0]);

			// Automatically add IP for new admins/operators?
			$this->settings['auto_admin_addip'] = $this->string2bool($settings['AUTO_ADMIN_ADDIP'][0]);

			// Automatically force spectator on player using /afk ?
			$this->settings['afk_force_spec'] = $this->string2bool($settings['AFK_FORCE_SPEC'][0]);

			// Provide clickable buttons in lists?
			$this->settings['clickable_lists'] = $this->string2bool($settings['CLICKABLE_LISTS'][0]);

			// Show logins in /recs?
			$this->settings['show_rec_logins'] = $this->string2bool($settings['SHOW_REC_LOGINS'][0]);

			// Set stripling path
			$this->settings['stripling_path'] = $settings['STRIPLING_PATH'][0];

			// Set dedicated Server installation path
			$this->settings['dedicated_installation'] = $settings['DEDICATED_INSTALLATION'][0];
			if (strtoupper($this->settings['dedicated_installation']) === 'PATH_TO_DEDICATED_SERVER' || empty($this->settings['dedicated_installation'])) {
				trigger_error('Please setup <dedicated_installation> in [config/UASECO.xml]!', E_USER_ERROR);
			}
			if ((OPERATING_SYSTEM === 'POSIX' && substr($this->settings['dedicated_installation'], -1) !== '/') || (OPERATING_SYSTEM === 'WINDOWS' && substr($this->settings['dedicated_installation'], -1) !== '\\')) {
				$this->console('[Config] Adding missing trailing "'. DIRECTORY_SEPARATOR .'" <dedicated_installation> from [config/UASECO.xml]!');
				$this->settings['dedicated_installation'] = $this->settings['dedicated_installation'] . DIRECTORY_SEPARATOR;
			}

			// Log passwords in logfile?
			$this->settings['mask_password'] = $this->string2bool($settings['MASK_PASSWORD'][0]);

			$this->settings['show_load_status'] = $this->string2bool($settings['SHOW_LOAD_STATUS'][0]);

			// PHP related stuff
			$this->settings['script_timeout'] = $settings['SCRIPT_TIMEOUT'][0];
			$this->settings['memory_limit'] = $settings['MEMORY_LIMIT'][0];

			// Read <dbms> settings and apply them
			$this->settings['dbms']['host'] = $settings['DBMS'][0]['HOST'][0];
			$this->settings['dbms']['login'] = $settings['DBMS'][0]['LOGIN'][0];
			$this->settings['dbms']['password'] = $settings['DBMS'][0]['PASSWORD'][0];
			$this->settings['dbms']['database'] = $settings['DBMS'][0]['DATABASE'][0];
			$this->settings['dbms']['table_prefix'] = $settings['DBMS'][0]['TABLE_PREFIX'][0];
			if (empty($this->settings['dbms']['table_prefix'])) {
				$this->settings['dbms']['table_prefix'] = 'uaseco_';
			}

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

			if ($this->settings['admin_client'] !== '' && preg_match('/^2\.11\.[12][0-9]$/', $this->settings['admin_client']) !== 1 || $this->settings['admin_client'] === '2.11.10') {
				trigger_error('Invalid admin client version: '. $this->settings['admin_client'] .'!', E_USER_ERROR);
			}
			if ($this->settings['player_client'] !== '' && preg_match('/^2\.11\.[12][0-9]$/', $this->settings['player_client']) !== 1 || $this->settings['player_client'] === '2.11.10') {
				trigger_error('Invalid player client version: '. $this->settings['player_client'] .'!', E_USER_ERROR);
			}
		}
		else {
			// Could not parse XML file
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
					if (!isset($_PLUGIN) || get_parent_class($_PLUGIN) !== 'Plugin') {
						trigger_error('require_once() does not load the file [plugins/'. $plugin .'] from <plugin> position '. ($count + 1) .', which means that this Plugin is probably an old version or it is added twice at [config/plugins.xml]!', E_USER_WARNING);
						continue;
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

				if (!isset($this->plugins[$dependence->classname]) && $dependence->permissions === Dependence::REQUIRED) {
					// Check if required dependence exists...
					trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] requires the Plugin ['. $dependence->classname .'] to run, disclude this Plugin or add the required Plugin in [config/plugins.xml] to continue!', E_USER_ERROR);
				}
				else if (!isset($this->plugins[$dependence->classname]) && $dependence->permissions === Dependence::WANTED) {
					// Check if wanted dependence exists...
//					$this->console('[Plugin] The Plugin ['. $plugin->getClassname() .'] wants the Plugin ['. $dependence->classname .'] to run full featured, if you want, add the wanted Plugin in [config/plugins.xml].');
				}
				else {
					$check_version = true;
				}

				// Check if disallowed dependence exists...
				if (isset($this->plugins[$dependence->classname]) && $dependence->permissions === Dependence::DISALLOWED) {
					trigger_error('[Plugin] The Plugin ['. $plugin->getClassname() .'] can not run together with the plugin ['. $dependence->classname .'], disclude this or the disallowed Plugin in [config/plugins.xml] to continue!', E_USER_ERROR);
				}

				if ($check_version === true) {
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
			if ( ($this->settings['developer']['log_events']['registered_types'] === true) && ($this->settings['developer']['log_events']['all_types'] === false) ) {
				$skip = array(
					'onEverySecond',
					'onMainLoop',
					'onModeScriptCallbackArray',
				);
				if (!in_array($event_type, $skip)) {
					$this->console('[Event] Releasing "'. $event_type .'"');
				}
			}

			$caller = false;
			if ($event_type === 'onPlayerManialinkPageAnswer') {
				$caller = explode('?', $callback_param[2]);
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

					if ($event_type === 'onPlayerManialinkPageAnswer') {
						$class = get_class($callback_func[0]);
						if ($class === $caller[0]) {
							// Parse get parameter and add them...
							parse_str(str_replace($class.'?', '', $callback_param[2]), $param);

							// Handle <entry> tags and their attributes
							foreach ($callback_param[3] as $item) {
								$param[$item['Name']] = $item['Value'];
							}

							// ...execute only the plugin that handles this answer!
							call_user_func($callback_func, $this, $callback_param[1], $param);
						}
					}
					else {
						// ... execute it!
						call_user_func($callback_func, $this, $callback_param);
					}
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
			if (!isset($this->registered_chatcmds[$chat_command])) {
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

	// Release a chat command from a plugin
	public function releaseChatCommand ($command, $login) {

		if ($player = $this->server->players->getPlayerByLogin($login)) {
			$chat = array(
				$player->pid,
				$player->login,
				$command,
			);
			$this->playerChat($chat);
		}
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
			'host'			=> $this->settings['dbms']['host'],
	                'login'			=> $this->settings['dbms']['login'],
	                'password'		=> $this->settings['dbms']['password'],
			'database'		=> $this->settings['dbms']['database'],
			'table_prefix'		=> $this->settings['dbms']['table_prefix'],
			'autocommit'		=> true,
			'charset'		=> 'utf8mb4',
			'collate'		=> 'utf8mb4_unicode_ci',
			'debug'			=> $this->debug,
		);

		// Connect
		$this->db = new Database($settings);

		// Check for minimum required version of the Database-Server
		if (strtolower($this->db->type) === 'mysql') {
			if (version_compare($this->db->version, MIN_MYSQL_VERSION, '<')) {
				$this->console('[ERROR] UASECO requires min. MySQL/'. MIN_MYSQL_VERSION .' and can not run with current MySQL/'. $this->db->version  .' ('. $this->db->version_full .'), please update MySQL!');
				die();
			}
			else {
				$this->console('[Database] ...connection established successfully to a MySQL/'. $this->db->version .' server!');
			}
		}
		else if (strtolower($this->db->type) === 'mariadb') {
			if (version_compare($this->db->version, MIN_MARIADB_VERSION, '<')) {
				$this->console('[ERROR] UASECO requires min. MariaDB/'. MIN_MARIADB_VERSION .' and can not run with current MariaDB/'. $this->db->version .' ('. $this->db->version_full .'), please update MariaDB!');
				die();
			}
			else {
				$this->console('[Database] ...connection established successfully to a MariaDB/'. $this->db->version .' server!');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkDatabaseStructure () {

		$this->console('[Database] Checking database structure:');

		// Check for tables
		$tables = array();
		$result = $this->db->query('SHOW TABLES;');
		if ($result) {
			while ($row = $result->fetch_row()) {
				$tables[] = $row[0];
			}
			$result->free_result();
		}


		$check_step1 = array();
		$check_step1['authors']		= in_array($this->settings['dbms']['table_prefix'] .'authors', $tables);
		$check_step1['maphistory']	= in_array($this->settings['dbms']['table_prefix'] .'maphistory', $tables);
		$check_step1['maps']		= in_array($this->settings['dbms']['table_prefix'] .'maps', $tables);
		$check_step1['players']		= in_array($this->settings['dbms']['table_prefix'] .'players', $tables);
		$check_step1['playlist']	= in_array($this->settings['dbms']['table_prefix'] .'playlist', $tables);
		$check_step1['rankings']	= in_array($this->settings['dbms']['table_prefix'] .'rankings', $tables);
		$check_step1['ratings']		= in_array($this->settings['dbms']['table_prefix'] .'ratings', $tables);
		$check_step1['records']		= in_array($this->settings['dbms']['table_prefix'] .'records', $tables);
		$check_step1['settings']	= in_array($this->settings['dbms']['table_prefix'] .'settings', $tables);
		$check_step1['times']		= in_array($this->settings['dbms']['table_prefix'] .'times', $tables);
		if ($check_step1['authors'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'authors`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'authors`');
		}
		if ($check_step1['maphistory'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'maphistory`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'maphistory`');
		}
		if ($check_step1['maps'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'maps`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'maps`');
		}
		if ($check_step1['players'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'players`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'players`');
		}
		if ($check_step1['playlist'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'playlist`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'playlist`');
		}
		if ($check_step1['rankings'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'rankings`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'rankings`');
		}
		if ($check_step1['ratings'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'ratings`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'ratings`');
		}
		if ($check_step1['records'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'records`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'records`');
		}
		if ($check_step1['settings'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'settings`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'settings`');
		}
		if ($check_step1['times'] === true) {
			$this->console('[Database] » Found table `'. $this->settings['dbms']['table_prefix'] .'times`');
		}
		else {
			$this->console('[Database] » Missing table `'. $this->settings['dbms']['table_prefix'] .'times`');
		}
		if ($check_step1['authors'] && $check_step1['maphistory'] && $check_step1['maps'] && $check_step1['players'] && $check_step1['playlist'] && $check_step1['rankings'] && $check_step1['ratings'] && $check_step1['records'] && $check_step1['settings'] && $check_step1['times']) {
			$this->console('[Database] ...successfully done!');
			return;
		}


		// Create tables
		$this->console('[Database] » '. ($check_step1['authors'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'authors`');
		$this->displayLoadStatus(($check_step1['authors'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'authors`', 0.2);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%authors` (
		  `AuthorId` mediumint(3) unsigned AUTO_INCREMENT,
		  `Login` varchar(64) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Nickname` varchar(100) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Zone` varchar(256) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Continent` varchar(2) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Nation` varchar(3) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  PRIMARY KEY (`AuthorId`),
		  UNIQUE KEY `Login` (`Login`),
		  KEY `Continent` (`Continent`),
		  KEY `Nation` (`Nation`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['maphistory'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'maphistory`');
		$this->displayLoadStatus(($check_step1['maphistory'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'maphistory`', 0.25);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%maphistory` (
		  `MapId` mediumint(3) unsigned NOT NULL,
		  `Date` datetime DEFAULT '1970-01-01 00:00:00',
		  KEY `MapId` (`MapId`),
		  KEY `Date` (`Date`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['maps'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'maps`');
		$this->displayLoadStatus(($check_step1['maps'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'maps`', 0.3);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%maps` (
		  `MapId` mediumint(3) UNSIGNED AUTO_INCREMENT,
		  `Uid` varchar(27) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Filename` text COLLATE 'utf8mb4_unicode_ci',
		  `Name` varchar(100) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Comment` text COLLATE 'utf8mb4_unicode_ci',
		  `AuthorId` mediumint(3) unsigned DEFAULT '0',
		  `AuthorScore` int(4) unsigned DEFAULT '0',
		  `AuthorTime` int(4) unsigned DEFAULT '0',
		  `GoldTime` int(4) unsigned DEFAULT '0',
		  `SilverTime` int(4) unsigned DEFAULT '0',
		  `BronzeTime` int(4) unsigned DEFAULT '0',
		  `Environment` varchar(10) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Mood` enum('unknown','Sunrise','Day','Sunset','Night') COLLATE 'utf8mb4_unicode_ci' NOT NULL,
		  `Cost` mediumint(3) unsigned DEFAULT '0',
		  `Type` varchar(32) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Style` varchar(32) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `MultiLap` enum('false','true') COLLATE 'utf8mb4_unicode_ci' NOT NULL,
		  `NbLaps` tinyint(1) unsigned DEFAULT '0',
		  `NbCheckpoints` tinyint(1) unsigned DEFAULT '0',
		  `Validated` enum('null','false','true') COLLATE 'utf8mb4_unicode_ci' NOT NULL,
		  `ExeVersion` varchar(16) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `ExeBuild` varchar(32) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `ModName` varchar(64) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `ModFile` varchar(256) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `ModUrl` text COLLATE 'utf8mb4_unicode_ci',
		  `SongFile` varchar(256) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `SongUrl` text COLLATE 'utf8mb4_unicode_ci',
		  PRIMARY KEY (`MapId`),
		  UNIQUE KEY `Uid` (`Uid`),
		  KEY `AuthorId` (`AuthorId`),
		  KEY `AuthorScore` (`AuthorScore`),
		  KEY `AuthorTime` (`AuthorTime`),
		  KEY `GoldTime` (`GoldTime`),
		  KEY `SilverTime` (`SilverTime`),
		  KEY `BronzeTime` (`BronzeTime`),
		  KEY `Environment` (`Environment`),
		  KEY `Mood` (`Mood`),
		  KEY `MultiLap` (`MultiLap`),
		  KEY `NbLaps` (`NbLaps`),
		  KEY `NbCheckpoints` (`NbCheckpoints`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['players'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'players`');
		$this->displayLoadStatus(($check_step1['players'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'players`', 0.35);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%players` (
		  `PlayerId` mediumint(3) unsigned AUTO_INCREMENT,
		  `Login` varchar(64) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Nickname` varchar(100) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Zone` varchar(256) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Continent` varchar(2) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `Nation` varchar(3) COLLATE 'utf8mb4_unicode_ci' DEFAULT '',
		  `LastVisit` datetime DEFAULT '1970-01-01 00:00:00',
		  `Visits` mediumint(3) unsigned DEFAULT '0',
		  `Wins` mediumint(3) unsigned DEFAULT '0',
		  `Donations` mediumint(3) unsigned DEFAULT '0',
		  `TimePlayed` int(4) unsigned DEFAULT '0',
		  PRIMARY KEY (`PlayerId`),
		  UNIQUE KEY `Login` (`Login`),
		  KEY `Continent` (`Continent`),
		  KEY `Nation` (`Nation`),
		  KEY `LastVisit` (`LastVisit`),
		  KEY `Visits` (`Visits`),
		  KEY `Wins` (`Wins`),
		  KEY `Donations` (`Donations`),
		  KEY `TimePlayed` (`TimePlayed`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;
		";
		$this->db->query($query);

		$this->console('[Database] » '. ($check_step1['playlist'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'playlist`');
		$this->displayLoadStatus(($check_step1['playlist'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'playlist`', 0.4);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%playlist` (
		  `Timestamp` decimal(17,3) unsigned DEFAULT '0.000',
		  `MapId` mediumint(3) unsigned DEFAULT '0',
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `Method` enum('select','vote','pay','add') COLLATE 'utf8mb4_unicode_ci' DEFAULT 'select',
		  KEY `Timestamp` (`Timestamp`),
		  KEY `MapId` (`MapId`),
		  KEY `PlayerId` (`PlayerId`),
		  KEY `Method` (`Method`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['rankings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'rankings`');
		$this->displayLoadStatus(($check_step1['rankings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'rankings`', 0.45);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%rankings` (
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `Average` int(4) unsigned DEFAULT '0',
		  PRIMARY KEY (`PlayerId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['ratings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'ratings`');
		$this->displayLoadStatus(($check_step1['ratings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'ratings`', 0.5);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%ratings` (
		  `MapId` mediumint(3) unsigned DEFAULT '0',
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `Date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Score` tinyint(1) signed DEFAULT '0',
		  PRIMARY KEY (`MapId`,`PlayerId`),
		  KEY `MapId` (`MapId`),
		  KEY `PlayerId` (`PlayerId`),
		  KEY `Date` (`Date`),
		  KEY `Score` (`Score`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['records'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'records`');
		$this->displayLoadStatus(($check_step1['records'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'records`', 0.6);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%records` (
		  `MapId` mediumint(3) unsigned DEFAULT '0',
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `GamemodeId` tinyint(1) unsigned DEFAULT '0',
		  `Date` datetime DEFAULT '1970-01-01 00:00:00',
		  `Score` int(4) unsigned DEFAULT '0',
		  `Checkpoints` text COLLATE 'utf8mb4_unicode_ci',
		  PRIMARY KEY (`MapId`,`PlayerId`,`GamemodeId`),
		  KEY `MapId` (`MapId`),
		  KEY `PlayerId` (`PlayerId`),
		  KEY `GamemodeId` (`GamemodeId`),
		  KEY `Date` (`Date`),
		  KEY `Score` (`Score`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['settings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'settings`');
		$this->displayLoadStatus(($check_step1['settings'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'settings`', 0.7);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%settings` (
		  `Plugin` varchar(64) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `Key` varchar(64) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
		  `Value` text COLLATE 'utf8mb4_unicode_ci',
		  PRIMARY KEY (`Plugin`,`PlayerId`,`Key`),
		  KEY `PlayerId` (`PlayerId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		$this->console('[Database] » '. ($check_step1['times'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'times`');
		$this->displayLoadStatus(($check_step1['times'] === true ? 'Checking' : 'Creating') .' table `'. $this->settings['dbms']['table_prefix'] .'times`', 0.8);
		$query = "
		CREATE TABLE IF NOT EXISTS `%prefix%times` (
		  `MapId` mediumint(3) unsigned DEFAULT '0',
		  `PlayerId` mediumint(3) unsigned DEFAULT '0',
		  `GamemodeId` tinyint(1) unsigned DEFAULT '0',
		  `Date` datetime DEFAULT '1970-01-01 00:00:00',
		  `Score` int(4) unsigned DEFAULT '0',
		  `Checkpoints` text COLLATE 'utf8mb4_unicode_ci',
		  PRIMARY KEY (`MapId`,`PlayerId`,`GamemodeId`,`Score`),
		  KEY `MapId` (`MapId`),
		  KEY `PlayerId` (`PlayerId`),
		  KEY `GamemodeId` (`GamemodeId`),
		  KEY `Date` (`Date`),
		  KEY `Score` (`Score`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
		";
		$this->db->query($query);


		// Check for tables
		$percentage_done = 0.8;
		$tables = array();
		$result = $this->db->query('SHOW TABLES;');
		if ($result) {
			while ($row = $result->fetch_row()) {
				$tables[] = $row[0];
			}
			$result->free_result();
		}

		$check_step2 = array();
		$check_step2['authors']		= in_array($this->settings['dbms']['table_prefix'] .'authors', $tables);
		$check_step2['maphistory']	= in_array($this->settings['dbms']['table_prefix'] .'maphistory', $tables);
		$check_step2['maps']		= in_array($this->settings['dbms']['table_prefix'] .'maps', $tables);
		$check_step2['players']		= in_array($this->settings['dbms']['table_prefix'] .'players', $tables);
		$check_step2['playlist']	= in_array($this->settings['dbms']['table_prefix'] .'playlist', $tables);
		$check_step2['rankings']	= in_array($this->settings['dbms']['table_prefix'] .'rankings', $tables);
		$check_step2['ratings']		= in_array($this->settings['dbms']['table_prefix'] .'ratings', $tables);
		$check_step2['records']		= in_array($this->settings['dbms']['table_prefix'] .'records', $tables);
		$check_step2['settings']	= in_array($this->settings['dbms']['table_prefix'] .'settings', $tables);
		$check_step2['times']		= in_array($this->settings['dbms']['table_prefix'] .'times', $tables);
		if (!$check_step2['authors'] && !$check_step2['maphistory'] && !$check_step2['maps'] && !$check_step2['players'] && !$check_step2['playlist'] && !$check_step2['rankings'] && !$check_step2['ratings'] && !$check_step2['records'] && !$check_step2['settings'] && !$check_step2['times']) {
			trigger_error('[Database] Table structure incorrect, automatic setup failed!', E_USER_ERROR);
		}


		if ($check_step1['maphistory'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'maphistory`');
			$query = "
			ALTER TABLE `%prefix%maphistory`
			  ADD CONSTRAINT `%prefix%maphistory_ibfk_1` FOREIGN KEY (`MapId`) REFERENCES `%prefix%maps` (`MapId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'maphistory` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['maps'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'maps`');
			$query = "
			ALTER TABLE `%prefix%maps`
			  ADD CONSTRAINT `%prefix%maps_ibfk_1` FOREIGN KEY (`AuthorId`) REFERENCES `%prefix%authors` (`AuthorId`);
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'maps` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['playlist'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'playlist`');
			$query = "
			ALTER TABLE `%prefix%playlist`
			  ADD CONSTRAINT `%prefix%playlist_ibfk_1` FOREIGN KEY (`MapId`) REFERENCES `%prefix%maps` (`MapId`) ON DELETE CASCADE ON UPDATE CASCADE,
			  ADD CONSTRAINT `%prefix%playlist_ibfk_2` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'playlist` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['rankings'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'rankings`');
			$query = "
			ALTER TABLE `%prefix%rankings`
			  ADD CONSTRAINT `%prefix%ranks_ibfk_1` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'rankings` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['ratings'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'ratings`');
			$query = "
			ALTER TABLE `%prefix%ratings`
			  ADD CONSTRAINT `%prefix%ratings_ibfk_2` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE,
			  ADD CONSTRAINT `%prefix%ratings_ibfk_1` FOREIGN KEY (`MapId`) REFERENCES `%prefix%maps` (`MapId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints: '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['records'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'records`');
			$query = "
			ALTER TABLE `%prefix%records`
			  ADD CONSTRAINT `%prefix%records_ibfk_2` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE,
			  ADD CONSTRAINT `%prefix%records_ibfk_1` FOREIGN KEY (`MapId`) REFERENCES `%prefix%maps` (`MapId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints: '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['settings'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'settings`');
			$query = "
			ALTER TABLE `%prefix%settings`
			  ADD CONSTRAINT `%prefix%settings_ibfk_1` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'settings` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		if ($check_step1['times'] === false) {
			$percentage_done += 0.1;
			$this->displayLoadStatus('Adding foreign key constraints...', $percentage_done);
			$this->console('[Database] » Adding foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'times`');
			$query = "
			ALTER TABLE `%prefix%times`
			  ADD CONSTRAINT `%prefix%times_ibfk_2` FOREIGN KEY (`PlayerId`) REFERENCES `%prefix%players` (`PlayerId`) ON DELETE CASCADE ON UPDATE CASCADE,
			  ADD CONSTRAINT `%prefix%times_ibfk_1` FOREIGN KEY (`MapId`) REFERENCES `%prefix%maps` (`MapId`) ON DELETE CASCADE ON UPDATE CASCADE;
			";
			$result = $this->db->query($query);
			if (!$result) {
				trigger_error('[Database] Failed to add required foreign key constraints for table `'. $this->settings['dbms']['table_prefix'] .'times` '. $this->db->errmsg(), E_USER_ERROR);
			}
		}

		$this->displayLoadStatus('Checking database structure...', 0.95);
		$query = "
		SET FOREIGN_KEY_CHECKS=1;
		";
		$result = $this->db->query($query);
		if (!$result) {
			trigger_error('[Database] Failed to enable foreign key checks: '. $this->db->errmsg(), E_USER_ERROR);
		}

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
			$this->console('[Dedicated] Try to connect to Maniaplanet dedicated server at {1}:{2} (timeout {3}s)',
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
			if ($this->server->xmlrpc['login'] !== 'SuperAdmin') {
				trigger_error("[Dedicated] Invalid login '". $this->server->xmlrpc['login'] ."' - must be 'SuperAdmin' in [config/UASECO.xml]!", E_USER_WARNING);
				return false;
			}

			// Check password
			if ($this->server->xmlrpc['pass'] === 'SuperAdmin') {
				trigger_error("[Dedicated] Insecure (default) password '" . $this->server->xmlrpc['pass'] . "' - should be changed in dedicated config and [config/UASECO.xml]!", E_USER_WARNING);
			}

			// Log console message
			if ($this->settings['mask_password'] === true) {
				$this->console("[Dedicated] Try to authenticate with login [{1}] and password [{2}] (masked password)",
					$this->server->xmlrpc['login'],
					preg_replace('#.#', '*', $this->server->xmlrpc['pass'])
				);
			}
			else {
				$this->console("[Dedicated] Try to authenticate with login [{1}] and password [{2}]",
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

			// Setup API-Version
			$this->client->query('SetApiVersion', XMLRPC_API_VERSION);

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
		if ($status['Code'] !== 4) {
			$this->console("[Dedicated] » Waiting for dedicated server to reach status 'Running - Play'...");
			$this->console('[Dedicated] » Status: ['. $status['Code'] .'] '. $status['Name']);
			$timeout = 0;
			$laststatus = $status['Name'];
			while ($status['Code'] !== 4) {
				sleep(1);
				$status = $this->client->query('GetStatus');
				if ($laststatus !== $status['Name']) {
					$this->console('[Dedicated] » Status: ['. $status['Code'] .'] '. $status['Name']);
					$laststatus = $status['Name'];
				}
				if (empty($status['Code'])) {
					trigger_error('[Dedicated] Connection failed on empty status!', E_USER_ERROR);
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
					case 'ManiaPlanet.ModeScriptCallbackArray':
						// [0] = string Param1, [1] = string Params[]
						$this->releaseEvent('onModeScriptCallbackArray', $call[1]);
						break;

					case 'ManiaPlanet.PlayerChat':
						// [0] = int PlayerUid, [1] =  string Login, [2] = string Text, [3] = bool IsRegistredCmd
						if ($call[1][0] === $this->server->id) {
							$this->releaseEvent('onServerChat', $call[1]);
						}
						else {
							$this->playerChat($call[1]);
						}
						break;

					case 'ManiaPlanet.PlayerInfoChanged':
						// [0] = SPlayerInfo PlayerInfo
						$this->playerInfoChanged($call[1][0]);
						break;

					case 'ManiaPlanet.PlayerManialinkPageAnswer':
						// [0] = int PlayerUid, [1] = string Login, [2] = string Answer, [3] = SEntryVal Entries[]
						$this->releaseEvent('onPlayerManialinkPageAnswer', $call[1]);
						break;

					case 'ManiaPlanet.PlayerConnect':
						// [0] = string Login, [1] = bool IsSpectator
						$this->playerConnect($call[1][0], $call[1][1]);
						break;

					case 'ManiaPlanet.PlayerDisconnect':
						// [0] = string Login, [1] = string DisconnectionReason
						$this->playerDisconnect($call[1][0], $call[1][1]);
						break;

//					case 'ManiaPlanet.StatusChanged':
//						// [0] = int StatusCode, [1] = string StatusName
//						$this->current_status = $call[1][0];				// update status changes
//						$this->releaseEvent('onStatusChangeTo'. $this->current_status, $call[1]);
//						break;

					case 'ManiaPlanet.MapListModified':
						// [0] = int CurMapIndex, [1] = int NextMapIndex, [2] = bool IsListModified
						if ($call[1][2] === true && $this->settings['automatic_refresh_maplist'] === true) {
							$this->console('[MapList] Re-reading complete map list from server...');
							$this->server->maps->readMapList();
							$count = count($this->server->maps->map_list);
							$this->console('[MapList] ...successfully done, read '. $count .' map'. ($count === 1 ? '' : 's') .' which matches server settings!');
							$this->releaseEvent('onMapListChanged', array('read', null));
						}
						$this->releaseEvent('onMapListModified', $call[1]);
						break;

					case 'ManiaPlanet.BillUpdated':
						// [0] = int BillId, [1] = int State, [2] = string StateName, [3] = int TransactionId
						$this->releaseEvent('onBillUpdated', $call[1]);
						break;

					case 'ManiaPlanet.PlayerAlliesChanged':
						// [0] = string Login
						$this->releaseEvent('onPlayerAlliesChanged', $call[1]);
						break;

					case 'ManiaPlanet.PlayerIncoherence':
						// [0] = int PlayerUid, [1] = string Login
						$this->releaseEvent('onPlayerIncoherence', $call[1]);
						break;

					case 'ManiaPlanet.TunnelDataReceived':
						// [0] = int PlayerUid, [1] = string Login, [2] = base64 Data
						$this->releaseEvent('onTunnelDataReceived', $call[1]);
						break;

					case 'ManiaPlanet.Echo':
						// [0] = string Internal, [1] = string Public
						if ($call[1][0] === 'AdminServ.Map.Added') {
							// Add external added map to our MapList too
							$param = json_decode($call[1][1]);
							$this->server->maps->addMapToListByUid($param->map->uid);
						}
						else if ($call[1][0] === 'AdminServ.Map.Deleted') {
							// Remove external removed map fromo our MapList too
							$param = json_decode($call[1][1]);
							$this->server->maps->removeMapByUid($param->map->uid);
						}
						$this->releaseEvent('onEcho', $call[1][0], $call[1][1]);
						break;

					case 'ManiaPlanet.VoteUpdated':
						// [0] = string StateName, [1] = string Login, [2] = string CmdName, [3] = string CmdParam
						$this->releaseEvent('onVoteUpdated', $call[1]);
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
			$errmsg = $exception->getMessage();
			if ($errmsg !== 'Login unknown.') {
				$this->console('[UASECO] Exception occurred: ['. $exception->getCode() .'] "'. $errmsg .'" - executeMulticall()');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// When a new map is started we have to get information about the new map,
	// get record to current map etc.
	public function loadingMap ($uid) {

		// Setup race status
		$this->server->gamestate = Server::RACE;

		// Check for changing the daily logfile
		if ($this->logfile['file'] !== './logs'. DIRECTORY_SEPARATOR . date('Y-m-d') .'-uaseco-current.log') {
			// Setup new logfile
			$this->setupLogfile();
			$this->sendHeader();
		}

		// Cleanup Player rankings
		$this->server->rankings->reset();

		// Get current map object
		$map = $this->server->maps->getCurrentMapInfo();

		// Check for restarting map
		if ($this->restarting === true) {
			// Throw postfix 'restart map' event
			if ($this->settings['developer']['log_events']['common'] === true) {
				$this->console('[Event] Restart Map');
			}
			$this->releaseEvent('onRestartMap', $map);

			// Reset status
			$this->restarting = false;
			return;
		}

		if ($this->startup_phase === false) {
			// Add new Map into MapHistory
			$this->server->maps->history->addMapToHistory($this->server->maps->current);

			// (Re-)read MapHistory
			$this->server->maps->history->readMapHistory();

			// Setup previous Map
			$this->server->maps->previous = $this->server->maps->current;
		}
		else {
			// Setup previous Map (from history)
			$this->server->maps->previous = $this->server->maps->history->getPreviousMap();
		}

		// Setup next Map
		$this->server->maps->next = $this->server->maps->getNextMap();

		// Search MX for current Map
		if ($map->mx === false || time() > ($map->mx->timestamp_fetched + $this->server->maps->max_age_mxinfo)) {
			$response = new MXInfoFetcher('TM2', $map->uid, true);
			if ($response !== null && empty($response->error)) {
				$map->mx = $response;
			}
		}
		else if ($this->debug) {
			$this->console('[Map] MX infos cached, last fetched at '. date('Y-m-d H:i:s', $map->mx->timestamp_fetched));
		}
		$this->releaseEvent('onManiaExchangeBestLoaded', (isset($map->mx->recordlist[0]['replaytime']) ? $map->mx->recordlist[0]['replaytime'] : 0));

		// Search MX for previous Map
		if ($this->server->maps->previous->mx === false || time() > ($this->server->maps->previous->mx->timestamp_fetched + $this->server->maps->max_age_mxinfo)) {
			$response = new MXInfoFetcher('TM2', $this->server->maps->previous->uid, true);
			if ($response !== null && empty($response->error)) {
				$this->server->maps->previous->mx = $response;
			}
		}

		// Search MX for next Map
		if ($this->server->maps->next->mx === false || time() > ($this->server->maps->next->mx->timestamp_fetched + $this->server->maps->max_age_mxinfo)) {
			$response = new MXInfoFetcher('TM2', $this->server->maps->next->uid, true);
			if ($response !== null && empty($response->error)) {
				$this->server->maps->next->mx = $response;
			}
		}

		// Refresh game info
		$this->server->getCurrentGameInfo();

		// Refresh server name and options
		$this->server->updateServerOptions();

		// Log debug information
		if ($this->debug) {
			$this->logDebugInformations();
		}

		// Log console message
		if ($this->server->maps->current->uid === $map->uid) {
			$this->console("[Map] Running on Map [{1}] made by [{2}] [Env: {3}, Uid: {4}, Id: {5}]",
				$map->name_stripped,
				$map->author,
				$map->environment,
				$map->uid,
				$map->id
			);
		}
		else {
			$this->console("[Map] Changing from Map [{1}] to [{2}] [Env: {3}, Uid: {4}, Id: {5}]",
				$this->server->maps->current->name_stripped,
				$map->name_stripped,
				$map->environment,
				$map->uid,
				$map->id
			);
		}

		// Update the field which contains current map
		$this->server->maps->current = $map;

		// Throw main 'loading map' event
		if ($this->settings['developer']['log_events']['common'] === true) {
			$this->console('[Event] Loading Map');
		}
		$this->releaseEvent('onLoadingMap', $map);


		// Simulate 'Maniaplanet.StartMap_Start' while start-up phase
		if ($this->startup_phase === true) {
			$data['map']['uid'] = $map->uid;
			$param[0] = 'Maniaplanet.StartMap_Start';
			$param[1][0] = json_encode($data);
			$this->plugins['PluginModescriptHandler']->onModeScriptCallbackArray($this, $param);
		}

		// Store usage into "stripling.xml" file
		$this->buildStriplingInfo();

		// Report usage back to home website from "stripling.xml" file
		$this->reportServerInfo();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function unLoadingMap ($uid) {
		// Recalculate ranks of each player
		$this->server->players->recalculateRanks();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function endMap () {

		$this->server->gamestate = Server::SCORE;

		// Throw 'end map' event (Records)
		$this->releaseEvent('onEndMapRanking', $this->server->maps->current);

		if (!$this->server->isrelay) {

			// Check out who won the current map and increment his/her wins by one.
			if ($this->server->rankings->count() > 1) {

				$rank = $this->server->rankings->getRank(1);
				if (($player = $this->server->players->getPlayerByLogin($rank->login)) !== false) {
					// Check for winner if there's more than one player
					if ($rank->round_points > 0 || $rank->map_points > 0 || $rank->match_points > 0 || $rank->best_race_time > 0 || $rank->best_lap_time > 0) {
						// Increase the player's wins
						$player->new_wins++;

						// Log console message
						$this->console('[Rank] Player [{1}] won for the {2}. time!',
							$player->login,
							$player->getWins()
						);

						if ($player->getWins() % $this->settings['global_win_multiple'] === 0) {
							// Replace parameters
							$message = $this->formatText($this->getChatMessage('WIN_MULTI'),
								$this->stripStyles($player->nickname),
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

		if ($this->settings['developer']['log_events']['common'] === true) {
			$this->console('[Event] End Map');
		}

		// Throw prefix 'end map' event (e.g. chat-based votes)
		$this->releaseEvent('onEndMapPrefix', $this->server->maps->current);

		// Throw main 'end map' event
		$this->releaseEvent('onEndMap', $this->server->maps->current);
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
		$data = @array_merge($details, $info);
		unset($details, $info);

		// Check for Server
		if (isset($data['Flags']) && floor($data['Flags'] / 100000) % 10 !== 0) {
			// Register relay server
			if (!$this->server->isrelay && $data['Login'] !== $this->server->login) {
				$this->server->relay_list[$data['Login']] = $data;

				// log console message
				$this->console('[Relay] Connect of relay server {1} ({2})',
					$data['Login'],
					$this->stripStyles($data['NickName'], false)
				);
			}
			// else: DO NOTHING on master server connect
		}
		else if (isset($data['Flags']) && $this->server->isrelay && floor($data['Flags'] / 10000) % 10 !== 0) {
			// DO NOTHING on player from master server on relay
		}
		else {
			$ipaddr = isset($data['IPAddress']) ? preg_replace('/:\d+/', '', $data['IPAddress']) : '';  // strip port

			// if no data fetched, notify & kick the player
			if (!isset($data['Login']) || $data['Login'] === '') {
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
				if ($version === '') {
					$version = '3.3.0';
				}
				$message = str_replace('{br}', LF, $this->getChatMessage('CLIENT_ERROR'));

				// if invalid version, notify & kick the player
				if ($this->settings['player_client'] !== '' && strcmp($version, $this->settings['player_client']) < 0) {
					$this->sendChatMessage($message, $login);
					sleep(5);  // allow time to connect and see the notice
					$this->client->addCall('Kick', $login, $this->formatColors($this->getChatMessage('CLIENT_DIALOG')));
					$this->console('[Player] Obsolete player client version '. $version .' for ['. $login .'] -- notified & kicked');
					return;
				}

				// if invalid version, notify & kick the admin
				if ($this->settings['admin_client'] !== '' && $this->isAnyAdminByLogin($data['Login']) && strcmp($version, $this->settings['admin_client']) < 0) {
					$this->sendChatMessage($message, $login);
					sleep(5);  // allow time to connect and see the notice
					$this->client->addCall('Kick', $login, $this->formatColors($this->getChatMessage('CLIENT_DIALOG')));
					$this->console('[Player] Obsolete admin client version '. $version .' for ['. $login .'] -- notified & kicked');
					return;
				}
			}

			// Create Player object, and adds new Player to the Player list
			$player = new Player($data);
			$this->server->players->addPlayer($player);

			// Get the current ranking for this player, required to have the rankings up-to-date on a running race,
			// but not in TEAM mode (requires a special handling).
			if ($this->server->gameinfo->mode !== Gameinfo::TEAM) {
				// Add to ranking list
				$this->server->rankings->addPlayer($player);

				if ($this->startup_phase === false) {
					// Call 'Trackmania.GetScores' to get 'Trackmania.Scores'
					$this->client->query('TriggerModeScriptEventArray', 'Trackmania.GetScores', array((string)time()));
				}
			}

			// Log console message
			$this->console('[Player] Connection from Player [{1}] from {2} [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
				$player->login,
				$this->country->iocToCountry($player->nation),
				$this->stripStyles($player->nickname),
				$player->ip,
				$player->ladder_rank,
				$player->id
			);

			// Update the Visits, but only when Player connects and not when UASECO restarts
			if ($this->startup_phase === false && $this->restarting === false) {
				$query = "UPDATE `%prefix%players` SET `Visits` = `Visits` + 1 WHERE `PlayerId` = ". $player->id ." LIMIT 1;";
				$result = $this->db->query($query);
				if (!$result) {
					$this->console('[Player] UPDATE `Visits` at `%prefix%players` failed [for statement "'. $query .'"]!');
				}
			}

			// Replace parameters
			$message = $this->formatText($this->getChatMessage('WELCOME'),
				$this->stripStyles($player->nickname),
				$this->server->name,
				UASECO_VERSION
			);
			// Hyperlink package name & version number
			$message = preg_replace('/UASECO.+'. UASECO_VERSION .'/', '$L['. UASECO_WEBSITE .']$0$L', $message);
			$message = str_replace('{br}', LF, $message);
			$this->sendChatMessage(str_replace(LF.LF, LF, $message), $player->login);

			// Store usage into "stripling.xml" file
			$this->buildStriplingInfo();

			// Throw main 'player connects' event
			$this->releaseEvent('onPlayerConnect', $player);

			// Throw postfix 'player connects' event (access control)
			$this->releaseEvent('onPlayerConnectPostfix', $player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles disconnections of Players.
	public function playerDisconnect ($login, $reason) {

		// Check for relay server
		if (!$this->server->isrelay && array_key_exists($login, $this->server->relay_list)) {
			// log console message
			$this->console('[Relay] Disconnect of relay server {1} ({2})',
				$login,
				$this->stripStyles($this->server->relay_list[$login]['NickName'], false)
			);

			unset($this->server->relay_list[$login]);
			return;
		}

		// Get Player object, if available. Otherwise bail out.
		if (!$player = $this->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Throw 'prepare player disconnect' event
		$this->releaseEvent('onPlayerDisconnectPrepare', $player);

		// Store eventually changed Plugin settings to the database
		$player->storeDatabasePlayerSettings();

		// Delete Player, skip the rest on relay if Player from master server (which was not added)
		if (!$result = $this->server->players->removePlayer($login)) {
			return;
		}

		// Log console message
		$this->console('[Player] Disconnection from Player [{1}] after {2} playtime [Nick: {3}, IP: {4}, Rank: {5}, Id: {6}]',
			$player->login,
			$this->timeString($player->getTimeOnline(), true),
			$this->stripStyles($player->nickname, false),
			$player->ip,
			$player->ladder_rank,
			$player->id
		);

		// Store usage into "stripling.xml" file
		$this->buildStriplingInfo();

		// Throw 'player disconnects' event
		$this->releaseEvent('onPlayerDisconnect', $player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Receives chat messages and reacts on them, reactions are done by the chat plugins.
	public function playerChat ($chat) {
		// Verify login
		if ($chat[1] === '' || $chat[1] === '???') {
			$this->console('[Chat] WARN: PlayerUid [{1}], with login [{2}] attempted to use chat command "{3}"',
				$chat[0],
				$chat[1],
				$chat[2]
			);
			return;
		}

		// Ignore master server messages on relay
		if ($this->server->isrelay && $chat[1] === $this->server->relaymaster['Login']) {
			return;
		}

		// Check for chat command '/' prefix
		$command = $chat[2];
		if ($command !== '' && $command[0] === '/') {
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
			if ($caller = $this->server->players->getPlayerByLogin($chat[1])) {
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
					if ($allowed === true) {
						// log console message
						if (empty($params[1])) {
							$this->console('[Chat] Player [{1}] used command "/{2}"',
								$caller->login,
								$command
							);
						}
						else {
							$masked_password = false;
							$exploded = explode(' ', strtolower($params[1]));
							if (in_array($exploded[0], array('unlock'))) {
								$params[1] = $exploded[0] .' '. preg_replace('#.#', '*', $exploded[1]);
								$masked_password = true;
							}
							if ($masked_password === true) {
								$this->console('[Chat] Player [{1}] used command "/{2} {3}" (masked password)',
									$caller->login,
									$command,
									$params[1]
								);
							}
							else {
								$this->console('[Chat] Player [{1}] used command "/{2} {3}"',
									$caller->login,
									$command,
									$params[1]
								);
							}
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
							$this->stripStyles($chat[2], false),
							$rights
						);
					}
				}
				else if ($params[0] === 'version' || $params[0] === 'serverlogin') {
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
						$this->stripStyles($chat[2], false)
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
				if ($chat[2] !== '') {
					$this->console('[Chat] NOTICE: Player [{1}] (Id: {2}) attempted to use command "{3}"',
						$chat[1],
						$chat[0],
						$this->stripStyles($chat[2], false)
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

	// When a player's info changed, signal the event. Fields: Login, NickName, PlayerId, TeamId, SpectatorStatus, LadderRanking, Flags
	public function playerInfoChanged ($playerinfo) {

		// On relay, check for player from master server
		if ($this->server->isrelay && floor($playerinfo['Flags'] / 10000) % 10 !== 0) {
			return;
		}

		// Check for valid player
		if (!$player = $this->server->players->getPlayerByLogin($playerinfo['Login'])) {
			return;
		}

		// Update Player object
		$player->updateInfo($playerinfo);

		$this->releaseEvent('onPlayerInfoChanged', $playerinfo['Login']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {

		// Simulate a "playerDisconnect"
		foreach ($this->server->players->player_list as $player) {
			$this->playerDisconnect($player->login, '');
		}
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

// Create an instance of UASECO and run it
$aseco = new UASECO();
$aseco->run();

?>
