<?php
/*
 * Plugin: Round Points
 * ~~~~~~~~~~~~~~~~~~~~
 * » Allows setting common and custom Rounds points systems.
 * » Based upon plugin.rpoints.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginRoundPoints();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRoundPoints extends Plugin {
	public $rounds_points	= array();
	public $config		= array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-09-25');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.round_points', 'plugin_description'));

		$this->addDependence('PluginModescriptHandler',	Dependence::REQUIRED,	'1.0.0',	null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onModeScriptChanged',		'onModeScriptChanged');

		$this->registerChatCommand('setrpoints',	'chat_setrpoints',	new Message('plugin.round_points', 'slash_setrpoints_description'),	Player::ADMINS);
		$this->registerChatCommand('rpoints',		'chat_rpoints',		new Message('plugin.round_points', 'slash_rpoints_description'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/round_points.xml', true, true)) {
			trigger_error('[RoundPoints] Could not read/parse config file "config/round_points.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);


		// Setup points systems
		foreach ($this->config['POINTS_SYSTEMS'][0]['SYSTEM'] as $system) {
			$this->rounds_points[$system['ID'][0]] = array(
				'id'		=> $system['ID'][0],
				'label'		=> $system['LABEL'][0],
				'points'	=> array_map('strval', explode(',', $system['POINTS'][0])),
				'limit'		=> $system['LIMIT'][0],
			);
		}


		// Setup only if Gamemode is "Rounds" or "Cup"
		if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS || $aseco->server->gameinfo->mode === Gameinfo::CUP) {

			// Set configured default rounds points system
			$system = $this->config['DEFAULT_SYSTEM'][0];

			// Set original points system (array of strings!)
			$points = array('10', '6', '4', '3', '2', '1');

			if (array_key_exists($system, $this->rounds_points)) {

				// Convert (int) to (string)
				$points = array_map('strval', $this->rounds_points[$system]['points']);

				try {
					// Set new custom points
					$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.SetPointsRepartition', $points);
					$aseco->console('[RoundPoints] Setup default rounds points: "{1}" -> {2}',
						$this->rounds_points[$system]['label'],
						implode(',', $this->rounds_points[$system]['points'])
					);
					$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));

					// Setup limits
					if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
						$aseco->server->gameinfo->rounds['PointsLimit'] = (int)$this->rounds_points[$system]['limit'];
					}
					else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
						$aseco->server->gameinfo->cup['PointsLimit'] = (int)$this->rounds_points[$system]['limit'];
					}
					$aseco->plugins['PluginModescriptHandler']->setupModescriptSettings();
				}
				catch (Exception $exception) {
					$aseco->console('[RoundPoints] Invalid given rounds points: {1}, Error: {2}', $system, $exception->getMessage());
				}

			}
			else if ($system === '') {
				try {
					$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.SetPointsRepartition', $points);
					$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));
				}
				catch (Exception $exception) {
					$aseco->console('[RoundPoints] Setting modescript default rounds points: {1} Error: {2}', $points, $exception->getMessage());
				}
			}
			else {
				$aseco->console('[RoundPoints] Unknown rounds points: {1}', $system);
			}


			// Convent string (string are required by 'Trackmania.SetPointsRepartition') back to int
			$points = array_map('intval', $points);

			if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
				$aseco->server->gameinfo->rounds['PointsRepartition'] = $points;
				if ($aseco->settings['developer']['log_events']['common'] === true) {
					$aseco->console('[Event] Points Repartition Loaded');
				}
				$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
			}
			else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
				$aseco->server->gameinfo->cup['PointsRepartition'] = $points;
				if ($aseco->settings['developer']['log_events']['common'] === true) {
					$aseco->console('[Event] Points Repartition Loaded');
				}
				$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptChanged ($aseco, $mode) {
		$this->onSync($aseco);						// Reload settings
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {

		if ($params['Action'] === 'ReleaseChatCommand') {
			$aseco->releaseChatCommand($params['command'], $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_rpoints ($aseco, $login, $chat_command, $chat_parameter) {

		// Get custom points
		$points = array();
		if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
			$points = $aseco->server->gameinfo->rounds['PointsRepartition'];
		}
		else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
			$points = $aseco->server->gameinfo->cup['PointsRepartition'];
		}

		// search for known points system
		$system = false;
		foreach ($this->rounds_points as $rpoints) {
			if ($points === $rpoints['points']) {
				$system = $rpoints['label'];
				break;
			}
		}

		// check for results
		if (empty($points)) {
			$message = new Message('plugin.round_points', 'no_rpoints');
			$message->addPlaceholders('');
		}
		else {
			if ($system !== false) {
				$message = new Message('plugin.round_points', 'rpoints_named');
				$message->addPlaceholders('',
					$system,
					'',
					implode(',', $points)
				);
			}
			else {
				$message = new Message('plugin.round_points', 'rpoints_nameless');
				$message->addPlaceholders('',
					implode(',', $points)
				);
			}
		}
		$message->sendChatMessage($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_setrpoints ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayerByLogin($login);

		if ($chat_parameter === 'help') {
			$data = array();
			$data[] = array('/setrpoints help',		(new Message('plugin.round_points', 'set_rpoints_help'))->finish($login));
			$data[] = array('/setrpoints list',		(new Message('plugin.round_points', 'setrpoints_list'))->finish($login));
			$data[] = array('/setrpoints show',		(new Message('plugin.round_points', 'setrpoints_show'))->finish($login));
			$data[] = array('/setrpoints xxx',		(new Message('plugin.round_points', 'setrpoints_xxx'))->finish($login));
			$data[] = array('/setrpoints X,Y,...,Z',	(new Message('plugin.round_points', 'setrpoints_X_Y_Z'))->finish($login));
			$data[] = array('',				(new Message('plugin.round_points', 'decreasing_integers'))->finish($login));
			$data[] = array('',				(new Message('plugin.round_points', 'no_spaces'))->finish($login));
			$data[] = array('/setrpoints off',		(new Message('plugin.round_points', 'setrpoints_off'))->finish($login));


			// Setup settings for Window
			$settings_styles = array(
				'icon'			=> 'Icons64x64_1,TrackInfo',
				'textcolors'		=> array('FF5F', 'FFFF'),
			);
			$settings_columns = array(
				'columns'		=> 1,
				'widths'		=> array(30, 70),
				'textcolors'		=> array('FF5F', 'FFFF'),
				'heading'		=> array((new Message('plugin.round_points', 'help_heading_command'))->finish($login), (new Message('plugin.round_points', 'help_heading_description'))->finish($login)),
			);
			$settings_content = array(
				'title'			=> (new Message('plugin.round_points', 'help_title'))->finish($login),
				'data'			=> $data,
				'about'			=> 'ROUND POINTS/'. $this->getVersion(),
				'mode'			=> 'columns',
			);

			$window = new Window();
			$window->setStyles($settings_styles);
			$window->setColumns($settings_columns);
			$window->setContent($settings_content);
			$window->send($player, 0, false);
		}
		else if ($chat_parameter === 'list') {
			$data = array();
			foreach ($this->rounds_points as $points) {
				$data[] = array(
					array(
						'action'	=> 'PluginRoundPoints?Action=ReleaseChatCommand&amp;command=/setrpoints '. $points['id'],	// Execute on click
						'title'		=> $points['id'],										// Display name
					),
					$points['label'],
					$points['limit'],
					implode(', ', $points['points']) .', ...'
				);
			}

			// Setup settings for Window
			$settings_styles = array(
				'icon'			=> 'Icons128x32_1,RT_Rounds',
				'textcolors'		=> array('FF5F', 'FFFF', 'FFFF', 'FFFF'),
			);
			$settings_columns = array(
				'columns'		=> 1,
				'widths'		=> array(10, 20, 5, 65),
				'textcolors'		=> array('FF5F', 'FF5F', 'FFFF'),
				'heading'		=> array('ID', 'Label', 'Limit', (new Message('plugin.round_points', 'list_heading_points'))->finish($login)),
			);
			$settings_content = array(
				'title'			=> (new Message('plugin.round_points', 'list_heading_title'))->finish($login),
				'data'			=> $data,
				'about'			=> 'ROUND POINTS/'. $this->getVersion(),
				'mode'			=> 'columns',
			);

			$window = new Window();
			$window->setStyles($settings_styles);
			$window->setColumns($settings_columns);
			$window->setContent($settings_content);
			$window->send($player, 0, false);
		}
		else if ($chat_parameter === 'show') {
			// Get custom points
			$points = array();
			if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
				$points = $aseco->server->gameinfo->rounds['PointsRepartition'];
			}
			else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
				$points = $aseco->server->gameinfo->cup['PointsRepartition'];
			}

			// Search for known points system
			$system = false;
			foreach ($this->rounds_points as $rpoints) {
				if ($points === $rpoints[1]) {
					$system = $rpoints[0];
					break;
				}
			}

			// Check for results
			if (empty($points)) {
				$message = new Message('plugin.round_points','no_rpoints');
				$message->addPlaceholders('{#admin}');
			}
			else {
				if ($system) {
					$message =  new Message('plugin.round_points','rpoints_named');
					$message->addPlaceholders('{#admin}',
						$system,
						'{#admin}',
						implode(',', $points)
					);
				}
				else {
					$message =  new Message('plugin.round_points','rpoints_nameless');
					$message->addPlaceholders('{#admin}',
						implode(',', $points)
					);
				}
			}
			$message->sendChatMessage($login);
		}
		else if ($chat_parameter === 'off') {

			// Set original points system
			$points = array('10', '6', '4', '3', '2', '1');

			try {
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.SetPointsRepartition', $points);
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));
			}
			catch (Exception $exception) {
				$aseco->console('[RoundPoints] Setting modescript default rounds points: {1} Error: {2}', $points, $exception->getMessage());
			}

			// log console message
			$aseco->console('[RoundPoints] [{1}] disabled custom points', $login);

			// show chat message
			$message = new Message('plugin.round_points','admin_disables');
			$message->addPlaceholders($player->nickname);
			$message->sendChatMessage();
		}
		else if (preg_match('/^\d+,[\d,]*\d+$/', $chat_parameter)) {
			// Set new custom points as array of strings
			$points = array_map('strval', explode(',', $chat_parameter));

			try {
				// Set new custom points
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.SetPointsRepartition', $points);
				$aseco->console('[RoundPoints] [{1}] set new custom points: {2}',
					$login,
					$chat_parameter
				);
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));
			}
			catch (Exception $exception) {
				$aseco->console('[RoundPoints] Invalid given rounds points: {1}, Error: {2}', $points, $exception->getMessage());
			}

			// Show chat message
			$message = new Message('plugin.round_points','admin_sets_custom');
			$message->addPlaceholders($player->nickname,
				$chat_parameter
			);
			$message->sendChatMessage();

		}
		else if (array_key_exists(strtolower($chat_parameter), $this->rounds_points)) {

			$system = strtolower($chat_parameter);

			// Convert int to string
			$points = $this->rounds_points[strtolower($system)]['points'];
			foreach ($points as &$num) {
				settype($num, 'string');
			}
			unset($num);

			try {
				// Set new custom points
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.SetPointsRepartition', $points);
				$aseco->console('[RoundPoints] [{1}] set new custom points [{2}]',
					$login,
					$this->rounds_points[$system]['label']
				);
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));

				// Setup limits
				if ($aseco->server->gameinfo->mode === Gameinfo::ROUNDS) {
					$aseco->server->gameinfo->rounds['PointsLimit'] = (int)$this->rounds_points[$system]['limit'];
				}
				else if ($aseco->server->gameinfo->mode === Gameinfo::CUP) {
					$aseco->server->gameinfo->cup['PointsLimit'] = (int)$this->rounds_points[$system]['limit'];
				}
				$aseco->plugins['PluginModescriptHandler']->setupModescriptSettings();
			}
			catch (Exception $exception) {
				$aseco->console('[RoundPoints] Invalid given rounds points: {1}, Error: {2}', $system, $exception->getMessage());
			}

			// Show chat message
			$message = new Message('plugin.round_points','admin_sets');
			$message->addPlaceholders($player->nickname,
				$this->rounds_points[$system]['label'],
				implode(', ', $this->rounds_points[$system]['points'])
			);
			$message->sendChatMessage();
		}
		else {
			$message = new Message('plugin.round_points','unknown_system');
			$message->addPlaceholders(strtoupper($chat_parameter));
			$message->sendChatMessage($login);
		}
	}
}

?>
