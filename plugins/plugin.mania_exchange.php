<?php
/*
 * Plugin: Mania Exchange
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Handles maps from ManiaExchange and provides ManiaExchange records message at start of each map.
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
 * https://api.mania-exchange.com/documents/reference
 * https://api.mania-exchange.com/documents/enums#modes
 * https://api.mania-exchange.com/documents/enums#environments
 *
 */


	// Start the plugin
	$_PLUGIN = new PluginManiaExchange();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginManiaExchange extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.3');
		$this->setBuild('2019-09-20');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.mania_exchange', 'plugin_description'));

		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');

		$this->registerChatCommand('mxlist',		'chat_mxlist',		new Message('plugin.mania_exchange', 'slash_mxlist_description'),	Player::PLAYERS);
//		$this->registerChatCommand('mxinfo',		'chat_mxinfo',		new Message('plugin.mania_exchange', 'slash_mxinfo_description'),	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onSync ($aseco) {

		if (!$settings = $aseco->parser->xmlToArray('config/mania_exchange.xml', true, true)) {
			trigger_error('[ManiaExchange] Could not read/parse config file [config/mania_exchange.xml]!', E_USER_ERROR);
		}
		$settings = $settings['SETTINGS'];
		unset($settings['SETTINGS']);

		// Create a User-Agent-Identifier for the authentication
		$this->config['user_agent'] = 'ManiaExchange/'. $this->getVersion() .' '. USER_AGENT;

		$this->config['show_records']			= (int)$settings['SHOW_RECORDS'][0];
		$this->config['media']['progress_indicator']	= (string)$settings['MEDIA'][0]['PROGRESS_INDICATOR'][0];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onLoadingMap ($aseco, $data) {

		// Obtain MX records
		if ($aseco->server->maps->current->mx && !empty($aseco->server->maps->current->mx->recordlist)) {
			// check whether to show MX record at start of map
			if ($this->config['show_records'] > 0) {
				$message = new Message('plugin.mania_exchange', 'chat_records');
				$message->addPlaceholders(
					$aseco->formatTime($aseco->server->maps->current->mx->recordlist[0]['replaytime']),
					$aseco->server->maps->current->mx->recordlist[0]['username']
				);
				$message->sendChatMessage();

//				if ($this->config['show_records'] === 2) {
//					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
//				}
//				else {
//					$aseco->sendChatMessage($message);
//				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mxlist ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$this->sendProgressIndicator($player);


		$params = array();

		// Default settings
		$params['page'] = 1;
		$params['priord'] = 0;
		$params['gv'] = 1;						// Game version. 1: show only MP4 maps or newer. -1: show only pre-MP4 maps.
		$params['limit'] = 100;


		// Setup search mode
		if (empty($chat_parameter)) {
			$params['mode'] = 2;					// Latest tracks - only one track per user
		}
		else {
			foreach (explode(' ', $chat_parameter) as $item) {
				$upararm = explode(':', $item);
				if ($upararm[0] === 'auth' || $upararm[0] === 'author') {
					$params['anyauthor'] = trim($upararm[1]);
					$chat_parameter = str_replace($upararm[0].':'.$upararm[1], '', $chat_parameter);
				}
			}
			$params['trackname'] = trim($chat_parameter);
			$params['mode'] = 0;
		}


		// Setup the current environment of the dedicated server
		if ($aseco->server->title === 'TMCanyon@nadeo') {
			$params['environments'] = 1;				// Canyon/CanyonCar
		}
		else if ($aseco->server->title === 'TMStadium@nadeo') {
			$params['environments'] = 2;				// Stadium/StadiumCar
		}
		else if ($aseco->server->title === 'TMValley@nadeo') {
			$params['environments'] = 3;				// Valley/ValleyCar
		}
		else if ($aseco->server->title === 'TMLagoon@nadeo') {
			$params['environments'] = 4;				// Lagoon/LagoonCar
		}
		else {
			$params['environments'] = 0;				// Custom Vehicle/Any
		}



		// Create the URL for the API call
		$api_url = 'https://tm.mania-exchange.com/tracksearch2/search?api=on&format=json';
		foreach ($params as $name => $value) {
			$api_url .= '&'. $name .'='. urlencode($value);
		}


		try {
			// Start async GET request
			$params = array(
				'url'			=> $api_url,
				'callback'		=> array(array($this, 'handleWebrequest'), array($login, $api_url)),
				'sync'			=> false,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> 100,
				'timeout_connect'	=> 30,
				'timeout'		=> 40,
			);
			$aseco->webrequest->GET($params);
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaExchange] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleWebrequest ($request, $params) {
		global $aseco;

		if ($request->response['header']['code'] === 200) {

			$mx_response = json_decode($request->response['content'], true);
			$this->buildMaplistWindow($mx_response, $params[0], $params[1]);

		}
		else {
			$aseco->console('[ManiaExchange] handleWebrequest(): Connection failed with "'. $request->response['header']['code'] .' - '. $request->response['header']['code_text'] .'" for URL ['. $api_url .']');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMaplistWindow ($maps, $login, $api_url) {
		global $aseco;

		// Get Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}


		//    object(stdClass)#339 (50) {
		//      ["TrackID"]=>
		//      int(29523)
		//      ["UserID"]=>
		//      int(2137)
		//      ["Username"]=>
		//      string(5) "undef"
		//      ["UploadedAt"]=>
		//      string(23) "2013-02-28T19:04:43.953"
		//      ["UpdatedAt"]=>
		//      string(23) "2017-06-20T17:23:13.943"
		//      ["Name"]=>
		//      string(17) "Short Distance 02"
		//      ["TypeName"]=>
		//      string(4) "Race"
		//      ["MapType"]=>
		//      string(4) "Race"
		//      ["TitlePack"]=>
		//      string(7) "Stadium"
		//      ["Hide"]=>
		//      bool(false)
		//      ["StyleName"]=>
		//      string(4) "Tech"
		//      ["Mood"]=>
		//      string(3) "Day"
		//      ["DisplayCost"]=>
		//      int(1035)
		//      ["ModName"]=>
		//      string(0) ""
		//      ["Lightmap"]=>
		//      int(7)
		//      ["ExeVersion"]=>
		//      string(5) "3.3.0"
		//      ["ExeBuild"]=>
		//      string(16) "2017-06-15_16_35"
		//      ["EnvironmentName"]=>
		//      string(7) "Stadium"
		//      ["VehicleName"]=>
		//      string(10) "StadiumCar"
		//      ["UnlimiterRequired"]=>
		//      bool(false)
		//      ["RouteName"]=>
		//      string(6) "Single"
		//      ["LengthName"]=>
		//      string(7) "15 secs"
		//      ["Laps"]=>
		//      int(1)
		//      ["DifficultyName"]=>
		//      string(12) "Intermediate"
		//      ["ReplayTypeName"]=>
		//      NULL
		//      ["ReplayWRID"]=>
		//      int(238170)
		//      ["ReplayCount"]=>
		//      int(3)
		//      ["TrackValue"]=>
		//      int(15)
		//      ["Comments"]=>
		//      string(0) ""
		//      ["Unlisted"]=>
		//      bool(false)
		//      ["AwardCount"]=>
		//      int(0)
		//      ["CommentCount"]=>
		//      int(0)
		//      ["MappackID"]=>
		//      int(0)
		//      ["ReplayWRTime"]=>
		//      int(16368)
		//      ["ReplayWRUserID"]=>
		//      int(12380)
		//      ["ReplayWRUsername"]=>
		//      string(5) "Synth"
		//      ["Unreleased"]=>
		//      bool(false)
		//      ["Downloadable"]=>
		//      bool(true)
		//      ["GbxMapName"]=>
		//      string(23) "$S$CF5Short Distance 02"
		//      ["RatingVoteCount"]=>
		//      int(0)
		//      ["RatingVoteAverage"]=>
		//      float(0)
		//      ["TrackUID"]=>
		//      string(27) "tDCCW13Ib6nY8_i8qo1tBWG5WN1"
		//      ["HasScreenshot"]=>
		//      bool(false)
		//      ["HasThumbnail"]=>
		//      bool(true)
		//      ["HasGhostBlocks"]=>
		//      bool(false)
		//      ["EmbeddedObjectsCount"]=>
		//      int(0)
		//      ["AuthorLogin"]=>
		//      string(8) "undef.de"
		//      ["IsMP4"]=>
		//      bool(true)
		//      ["SizeWarning"]=>
		//      bool(false)
		//      ["InPLList"]=>
		//      bool(false)
		//    }

		// https://tm.mania-exchange.com/tracks/thumbnail/29523
		// https://tm.mania-exchange.com/tracks/screenshot/normal/29546


		// List all found maps
		$data = array();
		foreach ($maps['results'] as $map) {
			$data[] = array(
				$map['GbxMapName'],
				$map['LengthName'],
				$map['AuthorLogin'],
			);
		}

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons64x64_1,ToolLeague1',
			'textcolors'		=> array('FF5F', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 4,
			'widths'		=> array(50, 25, 25),
			'halign'		=> array('left', 'right', 'left'),
			'textcolors'		=> array('FFFF', 'FFFF', 'FFFF'),
			'heading'		=> array('Name', 'Length', 'Author'),
		);
		$settings_content = array(
			'title'			=> (new Message('plugin.mania_exchange', 'window_title_mxlist'))->finish($player->login),
			'data'			=> $data,
			'mode'			=> 'columns',
			'add_background'	=> true,
		);
		$settings_footer = array(
			'about_title'		=> 'MANIA-EXCHANGE/'. $this->getVersion(),
			'about_link'		=> 'PluginManiaExchange?Action=showHelpWindow',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->setFooter($settings_footer);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendProgressIndicator ($player) {

		$content  = '<quad pos="100.5 -40.35" z-index="0.11" size="55 55" halign="center" valign="center" image="'. $this->config['media']['progress_indicator'] .'"/>';
		$content .= '<label pos="100.5 -65.2" z-index="0.12" size="55 55" halign="center" textsize="2" textcolor="FFFF" text="$SRetrieving maps... please wait."/>';

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons128x128_1,ManiaZones',
			'textcolors'		=> array('FF5F', 'FFFF'),
		);
		$settings_content = array(
			'title'			=> (new Message('plugin.mania_exchange', 'window_title_retrieving'))->finish($player->login),
			'data'			=> array($content),
			'mode'			=> 'pages',
			'add_background'	=> true,
		);
		$settings_footer = array(
			'about_title'		=> 'MANIA-EXCHANGE/'. $this->getVersion(),
			'about_link'		=> 'PluginManiaExchange?Action=showHelpWindow',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setContent($settings_content);
		$window->setFooter($settings_footer);
		$window->send($player, 0, false);
	}
}

?>
