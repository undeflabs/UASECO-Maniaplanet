<?php
/*
 * Plugin: Player Infos
 * ~~~~~~~~~~~~~~~~~~~~
 * » Displays current list of nicks/logins.
 * » Based upon chat.players2.php and chat.player.php from XAseco2/1.03 written by
 *   Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-07
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
 *  - includes/core/window.class.php
 *  - plugins/plugin.rasp.php
 *  - plugins/plugin.manialinks.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginPlayerInfos();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginPlayerInfos extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays current list of nicks/logins.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);

		// Handles action id's "2001"-"2200" for /stats
		$this->registerEvent('onPlayerManialinkPageAnswer', 'onPlayerManialinkPageAnswer');

		$this->registerChatCommand('players',	'chat_players',		'Displays current list of nicks/logins',	Player::PLAYERS);
		$this->registerChatCommand('ranks',	'chat_ranks',		'Displays list of online ranks/nicks',		Player::PLAYERS);
		$this->registerChatCommand('clans',	'chat_clans',		'Displays list of online clans/nicks',		Player::PLAYERS);
		$this->registerChatCommand('topclans',	'chat_topclans',	'Displays top 10 best ranked clans',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// leave actions outside 2001 - 2200 to other handlers
		$action = (int)$answer[2];
		if ($action >= 2001 && $action <= 2200) {
			// get player
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$target = $player->playerlist[$action-2001]['login'];

				// close main window because /stats can take a while
				$aseco->plugins['PluginManialinks']->mainwindow_off($aseco, $player->login);

				// /stats selected player
				$aseco->releaseChatCommand('/stats '. $target, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_players ($aseco, $login, $chat_command, $chat_parameter) {

		// use only first parameter
		$command['params'] = explode(' ', $chat_parameter, 2);
		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$player->playerlist = array();

		$head = 'Players On This Server:';
		$msg = array();
		$msg[] = array('Id', '{#nick}Nick $g/{#login} Login', '{#black}Nation');
		$pid = 1;
		$lines = 0;
		$player->msgs = array();
		$player->msgs[0] = array(1, $head, array(1.3, 0.1, 0.9, 0.3), array('Icons128x128_1', 'Buddies'));

		// create list of players, optionally by (sub)string
		foreach ($aseco->server->players->player_list as $pl) {
			if (strlen($command['params'][0]) == 0 || stripos($aseco->stripColors($pl->nickname), $command['params'][0]) !== false || stripos($pl->login, $command['params'][0]) !== false) {
				$plarr = array();
				$plarr['login'] = $pl->login;
				$player->playerlist[] = $plarr;

				// format nickname & login
				$ply = '{#black}' . $pl->nickname .'$z / '. ($aseco->isAnyAdmin($pl) ? '{#logina}' : '{#login}') . $pl->login;

				// add clickable button
				if ($aseco->settings['clickable_lists'] && $pid <= 200) {
					$ply = array($ply, $pid+2000);  // action id
				}

				$nat = $pl->nation;
				if (strlen($nat) > 14) {
					$nat = $aseco->country->countryToIoc($nat);
				}

				$msg[] = array(
					str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
					$ply,
					'{#black}'. $nat
				);

				$pid++;
				if (++$lines > 14) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', '{#nick}Nick $g/{#login} Login', '{#black}Nation');
				}
			}
		}

		// add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}

		// display ManiaLink message
		if (count($player->msgs) > 1) {
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else {
			// == 1
			$aseco->sendChatMessage('{#server}» {#error}No player(s) found!', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_ranks ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$ranks = array();

		// sort players by rank, insuring rankless are last by sorting on INT_MAX
		foreach ($aseco->server->players->player_list as $pl) {
			$rank = $aseco->plugins['PluginRasp']->getRank($pl->login);
			$ranks[$pl->login] = $rank != 'None' ? (integer) preg_replace('/\/.*/', '', $rank) : PHP_INT_MAX;
		}
		asort($ranks);

		// compile the message
		$head = 'Online Ranks ({#login}rank $g/{#nick} nick$g):';
		$msg = array();
		$lines = 0;
		$player->msgs = array();
		$player->msgs[0] = array(1, $head, array(0.8, 0.15, 0.65), array('Icons128x128_1', 'Buddies'));
		foreach ($ranks as $pl => $rk) {
			if ($play = $aseco->server->players->getPlayer($pl)) {
				$msg[] = array('{#login}'. ($rk != PHP_INT_MAX ? $rk : '{#grey}<none>'),
					'{#black}'. $play->nickname
				);
			}
			if (++$lines > 14) {
				$player->msgs[] = $msg;
				$lines = 0;
				$msg = array();
			}
		}

		// add if last batch exists
		if (!empty($msg)) {
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

	public function chat_clans ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$clans = array();

		// sort players by clan, insuring clanless are last by sorting on chr(255)
		foreach ($aseco->server->players->player_list as $pl) {
			$clans[$pl->login] = $pl->teamname ? $pl->teamname : chr(255);
		}
		asort($clans);

		// Compile the message
		foreach ($clans as $pl => $tm) {
			if ($play = $aseco->server->players->getPlayer($pl)) {
				$msg[] = array(
					($tm != chr(255) ? $tm : 'none'),
					$play->nickname
				);
			}
		}

		// Setup settings for Window
		$settings_title = array(
			'icon'	=> 'Icons128x128_1,Buddies',
		);
		$settings_columns = array(
			'columns'	=> 4,
			'widths'	=> array(50, 50),
			'halign'	=> array('left', 'left'),
			'textcolors'	=> array('FFFF', 'FFFF'),
		);
		$window = new Window();
		$window->setLayoutTitle($settings_title);
		$window->setColumns($settings_columns);
		$window->setContent('Online Clans', $msg);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_topclans ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		$top = 100;
		// Find best ranked
		$query = "
		SELECT
			`TeamName`,
			`Count`,
			`TeamRank`
		FROM (
			SELECT
				`TeamName`,
				COUNT(`avg`) AS `Count`,
				SUM(`Avg`) / COUNT(`Avg`) AS `TeamRank`
			FROM `players`, `rs_rank`
			WHERE `players`.`Id` = `rs_rank`.`PlayerId`
			GROUP BY `TeamName`
		) AS `Sub`
		WHERE `Sub`.`Count` >= ". $aseco->settings['topclans_minplayers'] ."
		ORDER BY `Sub`.`TeamRank`
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// compile the message with sorted clans
				$i = 1;
				$msg = array();
				while ($row = $res->fetch_object()) {
					$msg[] = array(
						$i .'.',
						sprintf("%4.1F", $row->TeamRank / 10000),
						$row->TeamName .'$z $n('. $row->Count .')$m',
					);
					$i++;
				}


				// Setup settings for Window
				$settings_title = array(
					'icon'	=> 'BgRaceScore2,Podium',
				);
				$settings_columns = array(
					'columns'	=> 4,
					'widths'	=> array(11, 19, 70),
					'halign'	=> array('right', 'right', 'left'),
					'textcolors'	=> array('EEEF', 'EEEF', 'FFFF'),
				);
				$window = new Window();
				$window->setLayoutTitle($settings_title);
				$window->setColumns($settings_columns);
				$window->setContent('Current TOP 100 Clans (min. '. $aseco->settings['topclans_minplayers'] .' Players)', $msg);
				$window->send($player, 0, false);
			}
			else {
				$aseco->sendChatMessage('{#server}» {#error}No clan(s) found!', $player->login);
			}
			$res->free_result();
		}
	}
}

?>
