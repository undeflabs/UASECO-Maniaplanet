<?php
/*
 * Plugin: Round Points
 * ~~~~~~~~~~~~~~~~~~~~
 * » Allows setting common and custom Rounds points systems.
 * » Based upon plugin.rpoints.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-11-05
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

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onRestartMap',	'onRestartMap');

		$this->registerChatCommand('rpoints', 'chat_rpoints', 'Shows current Rounds points system.', Player::PLAYERS);



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

	public function onRestartMap ($aseco, $uid) {

		// On restart it is required to set the round points again,
		// because this resets the most settings in a Modescript,
		// "Rounds_SetPointsRepartition" is one of it.
		// Details: http://forum.maniaplanet.com/viewtopic.php?p=221734#p221734
		$this->onSync($aseco);
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

	public function admin_rpoints ($aseco, $admin, $logtitle, $chattitle, $command) {

		$login = $admin->login;
		$command = explode(' ', preg_replace('/ +/', ' ', $command));
		$system = strtolower($command[0]);

		if ($command[0] == 'help') {
			$header = '{#black}/admin rpoints$g sets custom Rounds points:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available points systems');
			$help[] = array('...', '{#black}show',
			                'Shows current points system');
			$help[] = array('...', '{#black}xxx',
			                'Sets custom points system labelled xxx');
			$help[] = array('...', '{#black}X,Y,...,Z',
			                'Sets custom points system with specified values;');
			$help[] = array('', '',
			                'X,Y,...,Z must be decreasing integers and there');
			$help[] = array('', '',
			                'must be at least two values with no spaces');
			$help[] = array('...', '{#black}off',
			                'Disables custom points system');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.05, 0.05, 0.2, 0.8), 'OK');
		}
		else if ($command[0] == 'list') {
			$head = 'Currently available Rounds points systems:';
			$list = array();
			$list[] = array('Label', '{#black}System', '{#black}Distribution');
			$lines = 0;
			$admin->msgs = array();
			$admin->msgs[0] = array(1, $head, array(1.3, 0.2, 0.4, 0.7), array('Icons128x32_1', 'RT_Rounds'));
			foreach ($this->rounds_points as $tag => $points) {
				$list[] = array('{#black}'. $tag, $points[0],
				                implode(',', $points[1]) .',...');
				if (++$lines > 14) {
					$admin->msgs[] = $list;
					$lines = 0;
					$list = array();
					$list[] = array('Label', '{#black}System', '{#black}Distribution');
				}
			}
			if (!empty($list)) {
				$admin->msgs[] = $list;
			}
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($admin);
		}
		else if ($command[0] == 'show') {
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
		else if ($command[0] == 'off') {
			// disable custom points
			$rtn = $aseco->client->query('SetRoundCustomPoints', array(), false);

			// log console message
			$aseco->console('[RoundPoints] {1} [{2}] disabled custom points', $logtitle, $login);

			// show chat message
			$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} disables custom rounds points',
				$chattitle,
				$admin->nickname
			);
			$aseco->sendChatMessage($message);
		}
		else if (preg_match('/^\d+,[\d,]*\d+$/', $command[0])) {
			// set new custom points as array of ints
			$points = array_map('intval', explode(',', $command[0]));

			try {
				$aseco->client->query('SetRoundCustomPoints', $points, false);

				// log console message
				$aseco->console('[RoundPoints] {1} [{2}] set new custom points: {3}', $logtitle, $login, $command[0]);

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets custom rounds points: {#highlite}{3},...',
					$chattitle,
					$admin->nickname,
					$command[0]
				);
				$aseco->sendChatMessage($message);
			}
			catch (Exception $exception) {
				$message = '{#server}» {#error}Invalid point distribution! Error: {#highlite}$i '. $exception->getMessage();
				$aseco->sendChatMessage($message, $login);
			}
		}
		else if (array_key_exists($system, $this->rounds_points)) {
			try {
				// Set new custom points
				$aseco->client->query('SetRoundCustomPoints', $this->rounds_points[$system][1], false);
				// log console message
				$aseco->console('[RoundPoints] {1} [{2}] set new custom points [{3}]', $logtitle, $login, strtoupper($command[0]));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}{1}$z$s {#highlite}{2}$z$s{#admin} sets rounds points to {#highlite}{3}{#admin}: {#highlite}{4},...',
					$chattitle,
					$admin->nickname,
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
			$message = '{#server}» {#error}Unknown points system {#highlite}$i '. strtoupper($command[0]) .'$z$s {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}
}

?>
