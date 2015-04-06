<?php
/*
 * Plugin: Round Points
 * ~~~~~~~~~~~~~~~~~~~~
 * » Allows setting common and custom Rounds points systems.
 * » Based upon plugin.rpoints.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-03-24
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
 *  - includes/core/window.class.php
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
	public $rounds_points = array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Allows setting common and custom Rounds points systems.');

		$this->registerEvent('onSync',			'onSync');

		$this->registerChatCommand('setrpoints',	'chat_setrpoints',	'Sets custom Rounds points (see: /setrpoints help)',	Player::ADMINS);
		$this->registerChatCommand('rpoints',		'chat_rpoints',		'Shows current Rounds points system.',			Player::PLAYERS);


		// Define common points systems, any players finishing beyond the last points entry get
		// the same number of points (typically 1) as that last entry

		// http://www.formula1.com/inside_f1/rules_and_regulations/sporting_regulations/6833/
		$this->rounds_points['f1old'] = array(
			'Formula 1 GP Old',
			array(10,8,6,5,4,3,2,1)
		);


		// http://www.formula1.com/inside_f1/rules_and_regulations/sporting_regulations/8681/
		$this->rounds_points['f1new'] = array(
			'Formula 1 GP New',
			array(25,18,15,12,10,8,6,4,2,1)
		);


		// http://www.motogp.com/en/about+MotoGP/key+rules
		$this->rounds_points['motogp'] = array(
			'MotoGP',
			array(25,20,16,13,11,10,9,8,7,6,5,4,3,2,1)
		);


		// MotoGP + 5 points
		$this->rounds_points['motogp5'] = array(
			'MotoGP + 5',
			array(30,25,21,18,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1)
		);


		// http://www.et-leagues.com/fet1/rules.php
		$this->rounds_points['fet1'] = array(
			'Formula ET Season 1',
			array(12,10,9,8,7,6,5,4,4,3,3,3,2,2,2,1)
		);


		// http://www.et-leagues.com/fet2/rules.php (fixed: #17-19 = 2, not #17-21)
		$this->rounds_points['fet2'] = array(
			'Formula ET Season 2',
			array(15,12,11,10,9,8,7,6,6,5,5,4,4,3,3,3,2,2,2,1)
		);


		// http://www.et-leagues.com/fet3/rules.php
		$this->rounds_points['fet3'] = array(
			'Formula ET Season 3',
			array(15,12,11,10,9,8,7,6,6,5,5,4,4,3,3,3,2,2,2,2,1)
		);


		// http://www.champcarworldseries.com/News/Article.asp?ID=7499
		$this->rounds_points['champcar'] = array(
			'Champ Car World Series',
			array(31,27,25,23,21,19,17,15,13,11,10,9,8,7,6,5,4,3,2,1)
		);


		// http://www.eurosuperstars.com/eng/regolamenti.asp
		$this->rounds_points['superstars'] = array(
			'Superstars',
			array(20,15,12,10,8,6,4,3,2,1)
		);


		$this->rounds_points['simple5'] = array(
			'Simple 5',
			array(5,4,3,2,1)
		);


		$this->rounds_points['simple10']   = array(
			'Simple 10',
			array(10,9,8,7,6,5,4,3,2,1)
		);


		// Based upon 'MotoGP' * 10
		$this->rounds_points['highscore'] = array(
			'High Score',
			array(250,200,160,130,110,100,90,80,70,60,50,40,30,20,10)
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Setup only if Gamemode is "Rounds", "Team" or "Cup"
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {

			// Set configured default rounds points system
			$system = $aseco->settings['default_rpoints'];

			// Set original points system
			$points = array('10', '6', '4', '3', '2', '1');

			if (preg_match('/^\d+,[\d,]*\d+$/', $system)) {

				// Convert int to string
				$points = explode(',', $system);
				foreach ($points as &$num) {
					settype($num, 'string');
				}
				unset($num);

				try {
					// Set new custom points
					$aseco->client->query('TriggerModeScriptEventArray', 'Rounds_SetPointsRepartition', $points);
					$aseco->console('[RoundPoints] Setup default rounds points: {1}', $system);
				}
				catch (Exception $exception) {
					$aseco->console('[RoundPoints] Invalid given rounds points: {1}, Error: {2}', $system, $exception->getMessage());
				}
			}
			else if (array_key_exists($system, $this->rounds_points)) {

				// Convert int to string
				$points = $this->rounds_points[$system][1];
				foreach ($points as &$num) {
					settype($num, 'string');
				}
				unset($num);

				try {
					// Set new custom points
					$aseco->client->query('TriggerModeScriptEventArray', 'Rounds_SetPointsRepartition', $points);
					$aseco->console('[RoundPoints] Setup default rounds points: {1} - {2}',
						$this->rounds_points[$system][0],
						implode(',', $this->rounds_points[$system][1])
					);
				}
				catch (Exception $exception) {
					$aseco->console('[RoundPoints] Invalid given rounds points: {1}, Error: {2}', $system, $exception->getMessage());
				}

			}
			else if ($system == '') {
				try {
					$aseco->client->query('TriggerModeScriptEventArray', 'Rounds_SetPointsRepartition', $points);
				}
				catch (Exception $exception) {
					$aseco->console('[RoundPoints] Setting modescript default rounds points: {1} Error: {2}', $points, $exception->getMessage());
				}
			}
			else {
				$aseco->console('[RoundPoints] Unknown rounds points: {1}', $system);
			}


			// Convent string (string are required by 'Rounds_SetPointsRepartition') back to int
			foreach ($points as &$num) {
				settype($num, 'int');
			}
			if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
				$aseco->server->gameinfo->rounds['PointsRepartition'] = $points;
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Points Repartition Loaded');
				}
				$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
				$aseco->server->gameinfo->team['PointsRepartition'] = $points;
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Points Repartition Loaded');
				}
				$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				$aseco->server->gameinfo->cup['PointsRepartition'] = $points;
				if ($aseco->settings['developer']['log_events']['common'] == true) {
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

	public function chat_rpoints ($aseco, $login, $chat_command, $chat_parameter) {

		// Get custom points
		$points = array();
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			$points = $aseco->server->gameinfo->rounds['PointsRepartition'];
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			$points = $aseco->server->gameinfo->team['PointsRepartition'];
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
			$points = $aseco->server->gameinfo->cup['PointsRepartition'];
		}

		// search for known points system
		$system = false;
		foreach ($this->rounds_points as $rpoints) {
			if ($points == $rpoints[1]) {
				$system = $rpoints[0];
				break;
			}
		}

		// check for results
		if (empty($points)) {
			$message = $aseco->formatText($aseco->getChatMessage('NO_RPOINTS'), '');
		}
		else {
			if ($system) {
				$message = $aseco->formatText($aseco->getChatMessage('RPOINTS_NAMED'),
					'',
					$system,
					'',
					implode(',', $points)
				);
			}
			else {
				$message = $aseco->formatText($aseco->getChatMessage('RPOINTS_NAMELESS'),
					'',
					implode(',', $points)
				);
			}
		}
		$aseco->sendChatMessage($message, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_setrpoints ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);

		if ($chat_parameter == 'help') {
			$data = array();
			$data[] = array('/setrpoints help',		'Displays this help information');
			$data[] = array('/setrpoints list',		'Displays available points systems');
			$data[] = array('/setrpoints show',		'Shows current points system');
			$data[] = array('/setrpoints xxx',		'Sets custom points system labelled xxx');
			$data[] = array('/setrpoints X,Y,...,Z',	'Sets custom points system with specified values;');
			$data[] = array('',				'X,Y,...,Z must be decreasing integers and there');
			$data[] = array('',				'must be at least two values with no spaces');
			$data[] = array('/setrpoints off',		'Disables custom points system');

			// Setup settings for Window
			$settings_title = array(
				'icon'	=> 'Icons64x64_1,TrackInfo',
			);
			$settings_heading = array(
				'textcolors'	=> array('FF5F', 'FFFF'),
			);
			$settings_columns = array(
				'columns'	=> 1,
				'widths'	=> array(30, 70),
				'textcolors'	=> array('FF5F', 'FFFF'),
				'heading'	=> array('Command', 'Description'),
			);

			$window = new Window();
			$window->setLayoutTitle($settings_title);
			$window->setLayoutHeading($settings_heading);
			$window->setColumns($settings_columns);
			$window->setContent('Help for /setrpoints', $data);
			$window->send($player, 0, false);
		}
		else if ($chat_parameter == 'list') {
			$data = array();
			foreach ($this->rounds_points as $tag => $points) {
				$data[] = array($tag, $points[0], implode(', ', $points[1]) .', ...');
			}

			// Setup settings for Window
			$settings_title = array(
				'icon'	=> 'Icons128x32_1,RT_Rounds',
			);
			$settings_heading = array(
				'textcolors'	=> array('FF5F', 'FFFF'),
			);
			$settings_columns = array(
				'columns'	=> 1,
				'widths'	=> array(10, 20, 70),
				'textcolors'	=> array('FF5F', 'FF5F', 'FFFF'),
				'heading'	=> array('Label', 'System', 'Distribution'),
			);

			$window = new Window();
			$window->setLayoutTitle($settings_title);
			$window->setLayoutHeading($settings_heading);
			$window->setColumns($settings_columns);
			$window->setContent('Currently available Rounds points systems', $data);
			$window->send($player, 0, false);
		}
		else if ($chat_parameter == 'show') {
			// Get custom points
			$points = array();
			if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
				$points = $aseco->server->gameinfo->rounds['PointsRepartition'];
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
				$points = $aseco->server->gameinfo->team['PointsRepartition'];
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				$points = $aseco->server->gameinfo->cup['PointsRepartition'];
			}

			// Search for known points system
			$system = false;
			foreach ($this->rounds_points as $rpoints) {
				if ($points == $rpoints[1]) {
					$system = $rpoints[0];
					break;
				}
			}

			// Check for results
			if (empty($points)) {
				$message = $aseco->formatText($aseco->getChatMessage('NO_RPOINTS'), '{#admin}');
			}
			else {
				if ($system) {
					$message = $aseco->formatText($aseco->getChatMessage('RPOINTS_NAMED'),
						'{#admin}',
						$system,
						'{#admin}',
						implode(',', $points)
					);
				}
				else {
					$message = $aseco->formatText($aseco->getChatMessage('RPOINTS_NAMELESS'),
						'{#admin}',
						implode(',', $points)
					);
				}
			}
			$aseco->sendChatMessage($message, $login);
		}
		else if ($chat_parameter == 'off') {
			// disable custom points
			$rtn = $aseco->client->query('SetRoundCustomPoints', array(), false);

			// log console message
			$aseco->console('[RoundPoints] [{1}] disabled custom points', $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s{#admin} disables custom rounds points',
				$player->nickname
			);
			$aseco->sendChatMessage($message);
		}
		else if (preg_match('/^\d+,[\d,]*\d+$/', $chat_parameter)) {
			// set new custom points as array of ints
			$points = array_map('intval', explode(',', $chat_parameter));

			try {
				$aseco->client->query('SetRoundCustomPoints', $points, false);

				// log console message
				$aseco->console('[RoundPoints] [{1}] set new custom points: {2}', $login, $chat_parameter);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s{#admin} sets custom rounds points: {#highlite}{2},...',
					$player->nickname,
					$chat_parameter
				);
				$aseco->sendChatMessage($message);
			}
			catch (Exception $exception) {
				$message = '{#server}» {#error}Invalid point distribution! Error: {#highlite}$i '. $exception->getMessage();
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if (array_key_exists(strtolower($chat_parameter), $this->rounds_points)) {
			try {
				$system = strtolower($chat_parameter);

				// Set new custom points
				$aseco->client->query('SetRoundCustomPoints', $this->rounds_points[$system][1], false);

				// log console message
				$aseco->console('[RoundPoints] [{1}] set new custom points [{2}]', $login, strtoupper($chat_parameter));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s{#admin} sets rounds points to {#highlite}{2}{#admin}: {#highlite}{4},...',
					$player->nickname,
					$this->rounds_points[$system][0],
					implode(',', $this->rounds_points[$system][1])
				);
				$aseco->sendChatMessage($message);
			}
			catch (Exception $exception) {
				$aseco->console('[RoundPoints] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SetRoundCustomPoints');
			}
		}
		else {
			$message = '{#server}» {#error}Unknown points system {#highlite}$i '. strtoupper($chat_parameter) .'$z$s {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}
}

?>
