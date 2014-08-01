<?php
/*
 * Plugin: Chat Stats
 * ~~~~~~~~~~~~~~~~~~
 * » Displays player statistics and personal settings.
 * » Based upon chat.stats.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-26
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
 *  - plugins/plugin.rasp.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.panels.php
 *  - plugins/plugin.styles.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatStats();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatStats extends Plugin {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays player statistics and personal settings.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginPanels',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginStyles',		Dependence::WANTED,	'1.0.0', null);

		$this->registerChatCommand('stats',	'chat_stats',		'Displays statistics of current player',	Player::PLAYERS);
		$this->registerChatCommand('settings',	'chat_settings',	'Displays your personal settings',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_stats ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);
		$target = $player;

		// check for optional player parameter
		if ($chat_parameter != '') {
			if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
				return;
			}
		}

		// Setup current player info
		$rank = $target->ladderrank;
		$score = $target->ladderscore;
		$lastm = $target->lastmatchscore;
		$wins = $target->nbwins;
		$draws = $target->nbdraws;
		$losses = $target->nblosses;

		// get zone info
		$inscr = $target->zone_inscription;
		$inscrdays = floor($inscr / 24);
		$inscrhours = $inscr - ($inscrdays * 24);

		// format numbers with narrow spaces between the thousands
		$frank = str_replace(' ', '$n $m', number_format($rank, 0, ' ', ' '));
		$fwins = str_replace(' ', '$n $m', number_format($wins, 0, ' ', ' '));
		$fdraws = str_replace(' ', '$n $m', number_format($draws, 0, ' ', ' '));
		$flosses = str_replace(' ', '$n $m', number_format($losses, 0, ' ', ' '));

		// obtain last online timestamp
		$query = "
		SELECT
			`UpdatedAt`
		FROM `players`
		WHERE `Login` = ". $aseco->mysqli->quote($target->login) .";
		";

		$result = $aseco->mysqli->query($query);
		$laston = $result->fetch_row();
		$result->free_result();

		$records = 0;
		if ($list = $target->getRecords()) {
			// sort for best records
			asort($list);

			// count total ranked records
			foreach ($list as $name => $rec) {
				// stop upon unranked record
				if ($rec > $aseco->plugins['PluginLocalRecords']->settings['max_recs']) {
					break;
				}

				// count ranked record
				$records++;
			}
		}

		$header = 'Stats for: ' . $target->nickname . '$z / {#login}' . $target->login;
		$stats = array();
		$stats[] = array('Server Date', '{#black}' . date('M d, Y'));
		$stats[] = array('Server Time', '{#black}' . date('H:i:s T'));
		$value = '{#black}' . $aseco->formatTime($target->getTimePlayed() * 1000, false);
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, -5);  // action id
		}
		$stats[] = array('Time Played', $value);
		$stats[] = array('Last Online', '{#black}' . preg_replace('/:\d\d$/', '', $laston[0]));
		if ($aseco->plugins['PluginRasp']->feature_ranks) {
			$value = '{#black}' . $aseco->plugins['PluginRasp']->getRank($target->login);
			// add clickable button
			if ($aseco->settings['clickable_lists']) {
				$value = array($value, -6);  // action id
			}
			$stats[] = array('Server Rank', $value);
		}
		$value = '{#black}' . $records;
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 5);  // action id
		}
		$stats[] = array('Records', $value);
		$value = '{#black}' . ($target->getWins() > $target->wins ? $target->getWins() : $target->wins);
		// add clickable button
		if ($aseco->settings['clickable_lists']) {
			$value = array($value, 6);  // action id
		}
		$stats[] = array('Races Won', $value);
		$stats[] = array('Ladder Rank', '{#black}' . $frank);
		$stats[] = array('Ladder Score', '{#black}' . round($score, 1));
		$stats[] = array('Last Match', '{#black}' . round($lastm, 1));
		$stats[] = array('Wins', '{#black}' . $fwins);
		$stats[] = array('Draws', '{#black}' . $fdraws . ($losses != 0 ? '   $gW/L: {#black}' . round($wins / $losses, 3) : ''));
		$stats[] = array('Losses', '{#black}' . $flosses);
		$stats[] = array('Zone', '{#black}' . implode(', ', $target->zone));
		$stats[] = array('Inscribed', '{#black}' . $inscrdays . ' day' . ($inscrdays == 1 ? ' ' : 's ') . $inscrhours . ' hours');
		$stats[] = array('Clan', '{#black}' . ($target->teamname ? $target->teamname . '$z' : '<none>'));
		$stats[] = array('Client', '{#black}' . $target->client);
		if ($aseco->allowAbility($player, 'chat_statsip')) {
			$stats[] = array('IP', '{#black}' . $target->ipport);
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x128_1', 'Statistics', 0.03), $stats, array(1.0, 0.3, 0.7), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_settings ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);
		$target = $player;

		// check for optional login parameter if any admin
		if ($chat_parameter != '' && $aseco->allowAbility($player, 'chat_settings')) {
			if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
				return;
			}
		}

		// get style setting
		if ( isset($aseco->plugins['PluginStyles']) ) {
			$style = $aseco->plugins['PluginStyles']->getStyle($target->login);
		}
		else {
			$style = false;
		}

		// get panel settings
		if ( isset($aseco->plugins['PluginPanels']) ) {
			$panels = $aseco->plugins['PluginPanels']->getPanels($target->login);
		}
		else {
			$panels = false;
		}

		// get panel background
		if ( isset($aseco->plugins['PluginPanels']) ) {
			$panelbg = $aseco->plugins['PluginPanels']->getPanelBG($target->login);
		}
		else {
			$panelbg = false;
		}

		$header = 'Settings for: ' . $target->nickname . '$z / {#login}' . $target->login;
		$settings = array();

		// collect available settings
		if ($cps = $player->getCheckpointSettings()) {
			$settings[] = array('Local CPS', '{#black}' . $cps['localcps']);
			$settings[] = array('Dedimania CPS', '{#black}' . $cps['dedicps']);
			$settings[] = array();
		}

		$settings[] = array('Window Style', '{#black}' . $style);
		$settings[] = array('Panel Background', '{#black}' . $panelbg);

		if ($panels) {
			$settings[] = array();
			if ($aseco->isAnyAdmin($target)) {
				$settings[] = array('Admin Panel', '{#black}' . substr($panels['admin'], 5));
			}
			$settings[] = array('Donate Panel', '{#black}' . substr($panels['donate'], 6));
			$settings[] = array('Records Panel', '{#black}' . substr($panels['records'], 7));
			$settings[] = array('Vote Panel', '{#black}' . substr($panels['vote'], 4));
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons128x128_1', 'Inputs', 0.03), $settings, array(1.0, 0.3, 0.7), 'OK');
	}
}

?>
