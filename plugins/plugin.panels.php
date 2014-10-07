<?php
/*
 * Plugin: Panels
 * ~~~~~~~~~~~~~~
 * » Selects ManiaLink panel templates.
 * » Based upon plugin.panels.php from XAseco2/1.03 written by Xymph
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
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.rasp.php
 *  - plugins/plugin.donate.php
 *  - plugins/plugin.rasp_votes.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginPanels();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginPanels extends Plugin {
	public $settings;
	public $style;
	public $panels;
	public $panelbg;
	public $statspanel;
	public $placeholder;
	public $record_defaults;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Selects ManiaLink panel templates.');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDonate',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onStartup',		'onStartup');
		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onEndMap1',		'onEndMap1');
		$this->registerEvent('onEndMap',		'onEndMap');
		$this->registerEvent('onBeginMap',		'onBeginMap');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');


		// handles action id's "-100"-"-49" for selecting from max. 50 record panel templates
		// handles action id's "-48"-"-7" for selecting from max. 40 admin panel templates
		// handles action id's "37"-"48" for selecting from max. 10 vote panel templates
		// handles action id's "7231"-"7262" for selecting from max. 30 panel background templates
		$this->registerEvent('onPlayerManialinkPageAnswer', 'onPlayerManialinkPageAnswer');

// Maybe move to "plugin.rasp_jukebox.php"
		$this->registerChatCommand('votepanel',	'chat_votepanel',	'Selects vote panel (see: /votepanel help)',		Player::PLAYERS);

		$this->registerChatCommand('panelbg',	'chat_panelbg',		'Selects panel background (see: /panelbg help)',	Player::PLAYERS);

		$this->placeholder = array(
			'score'	=> '---',
			'time'	=> '-:--.---',
		);

		$this->record_defaults = array(
			'pb'	=> '-:--.---',
			'local'	=> '-:--.---',
			'dedi'	=> '-:--.---',
			'mx'	=> '-:--.---',
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_recpanel ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if ($chat_parameter == 'help') {
			$header = '{#black}/recpanel$g will change the records panel:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available panels');
			$help[] = array('...', '{#black}default',
			                'Resets panel to server default');
			$help[] = array('...', '{#black}off',
			                'Disables records panel');
			$help[] = array('...', '{#black}xxx',
			                'Selects records panel xxx');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($chat_parameter == 'list') {
			$player->maplist = array();

			// read list of records panel files
			$paneldir = 'config/panels/';
			$dir = opendir($paneldir);
			$files = array();
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, 0, 7)) == 'records' &&
				    strtolower(substr($file, -4)) == '.xml')
					$files[] = substr($file, 7, strlen($file)-11);
			}
			closedir($dir);
			sort($files, SORT_STRING);
			if (count($files) > 50) {
				$files = array_slice($files, 0, 50);  // maximum 50 templates
				trigger_error('Too many records panel templates - maximum 50!', E_USER_WARNING);
			}
			// sneak in standard entries
			$files[] = 'default';
			$files[] = 'off';

			$head = 'Currently available records panels:';
			$list = array();
			$pid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(0.8, 0.1, 0.7), array('Icons128x128_1', 'Custom'));
			foreach ($files as $file) {
				// store panel in player object for jukeboxing
				$trkarr = array();
				$trkarr['panel'] = $file;
				$player->maplist[] = $trkarr;

				$list[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
				                array('{#black}' . $file, -48-$pid));  // action id
				$pid++;
				if (++$lines > 14) {
					$player->msgs[] = $list;
					$lines = 0;
					$list = array();
				}
			}

			// add if last batch exists
			if (!empty($list)) {
				$player->msgs[] = $list;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($chat_parameter != '') {
			$panel = $chat_parameter;
			if (is_numeric($panel) && $panel > 0) {
				$pid = ltrim($panel, '0');
				$pid--;
				if (array_key_exists($pid, $player->maplist) &&
				    isset($player->maplist[$pid]['panel'])) {
					$panel = $player->maplist[$pid]['panel'];
				}
			}
			if ($panel == 'off') {
				$player->panels['records'] = '';
				$this->recpanel_off($aseco, $login);
				$message = '{#server}» Records panel disabled!';
				$this->setPanel($login, 'records', '');
			}
			else if ($panel == 'default') {
				$player->panels['records'] = $this->replacePanelBG($this->panels['records'], $player->panelbg);
				$this->updateRecordsPanel($aseco, $player, $player->panels['pb']);
				$message = '{#server}» Records panel reset to server default {#highlite}' . substr($this->settings['records_panel'], 7) . '{#server} !';
				$this->setPanel($login, 'records', $this->settings['records_panel']);
			}
			else {
				// add file prefix
				if (strtolower(substr($panel, 0, 7)) != 'records')
					$panel = 'Records' . $panel;
				$panel_file = 'config/panels/' . $panel . '.xml';
				// load new panel
				if ($paneldata = @file_get_contents($panel_file)) {
					$player->panels['records'] = $this->replacePanelBG($paneldata, $player->panelbg);
					$this->updateRecordsPanel($aseco, $player, $player->panels['pb']);
					$message = '{#server}» Records panel {#highlite}' . $chat_parameter . '{#server} selected!';
					$this->setPanel($login, 'records', $panel);
				}
				else {
					// Could not read XML file
					trigger_error('[Panel] Could not read records panel file ['. $panel_file .']!', E_USER_WARNING);
					$message = '{#server}» {#error}No valid records panel file, use {#highlite}$i /recpanel list {#error}!';
				}
			}
			$aseco->sendChatMessage($message, $login);
		}

		else {
			$message = '{#server}» {#error}No records panel specified, use {#highlite}$i /recpanel help {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_votepanel ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if ($chat_parameter == 'help') {
			$header = '{#black}/votepanel$g will change the vote panel:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available panels');
			$help[] = array('...', '{#black}default',
			                'Resets panel to server default');
			$help[] = array('...', '{#black}off',
			                'Disables vote panel');
			$help[] = array('...', '{#black}xxx',
			                'Selects vote panel xxx');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($chat_parameter == 'list') {
			$player->maplist = array();

			// read list of vote panel files
			$paneldir = 'config/panels/';
			$dir = opendir($paneldir);
			$files = array();
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, 0, 4)) == 'vote' && strtolower(substr($file, -4)) == '.xml') {
					$files[] = substr($file, 4, strlen($file)-8);
				}
			}
			closedir($dir);
			sort($files, SORT_STRING);
			if (count($files) > 10) {
				$files = array_slice($files, 0, 10);  // maximum 10 templates
				trigger_error('Too many vote panel templates - maximum 10!', E_USER_WARNING);
			}
			// sneak in standard entries
			$files[] = 'default';
			$files[] = 'off';

			$head = 'Currently available vote panels:';
			$list = array();
			$pid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(0.8, 0.1, 0.7), array('Icons128x128_1', 'Custom'));
			foreach ($files as $file) {
				// store panel in player object for jukeboxing
				$trkarr = array();
				$trkarr['panel'] = $file;
				$player->maplist[] = $trkarr;

				$list[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
				                array('{#black}' . $file, $pid+36));  // action id
				$pid++;
				if (++$lines > 14) {
					$player->msgs[] = $list;
					$lines = 0;
					$list = array();
				}
			}

			// add if last batch exists
			if (!empty($list)) {
				$player->msgs[] = $list;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($chat_parameter != '') {
			$panel = $chat_parameter;
			if (is_numeric($panel) && $panel > 0) {
				$pid = ltrim($panel, '0');
				$pid--;
				if (array_key_exists($pid, $player->maplist) && isset($player->maplist[$pid]['panel'])) {
					$panel = $player->maplist[$pid]['panel'];
				}
			}
			if ($panel == 'off') {
				$player->panels['vote'] = '';
				$message = '{#server}» Vote panel disabled!';
				$this->setPanel($login, 'vote', '');
			}
			else if ($panel == 'default') {
				$player->panels['vote'] = $this->replacePanelBG($this->panels['vote'], $player->panelbg);
				$this->display_votepanel($aseco, $player, $aseco->formatColors('{#emotic}') . 'Yes - F5', '$333No - F6', 2000);
				$message = '{#server}» Vote panel reset to server default {#highlite}' . substr($this->settings['vote_panel'], 4) . '{#server} !';
				$this->setPanel($login, 'vote', $this->settings['vote_panel']);
			}
			else {
				// add file prefix
				if (strtolower(substr($panel, 0, 4)) != 'vote')
					$panel = 'Vote' . $panel;
				$panel_file = 'config/panels/' . $panel . '.xml';
				// load new panel
				if ($paneldata = @file_get_contents($panel_file)) {
					$player->panels['vote'] = $this->replacePanelBG($paneldata, $player->panelbg);
					$this->display_votepanel($aseco, $player, $aseco->formatColors('{#vote}') . 'Yes - F5', '$333No - F6', 2000);
					$message = '{#server}» Vote panel {#highlite}' . $chat_parameter . '{#server} selected!';
					$this->setPanel($login, 'vote', $panel);
				} else {
					// Could not read XML file
					trigger_error('[Panel] Could not read vote panel file ['. $panel_file .']!', E_USER_WARNING);
					$message = '{#server}» {#error}No valid vote panel file, use {#highlite}$i /votepanel list {#error}!';
				}
			}
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$message = '{#server}» {#error}No vote panel specified, use {#highlite}$i /votepanel help {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_panelbg ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if ($chat_parameter == 'help') {
			$header = '{#black}/panelbg$g will change the panel background:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available backgrounds');
			$help[] = array('...', '{#black}default',
			                'Resets background to server default');
			$help[] = array('...', '{#black}xxx',
			                'Selects panel background xxx');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($chat_parameter == 'list') {
			$player->maplist = array();

			// read list of background files
			$paneldir = 'config/panels/';
			$dir = opendir($paneldir);
			$files = array();
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, 0, 7)) == 'panelbg' && strtolower(substr($file, -4)) == '.xml') {
					$files[] = substr($file, 7, strlen($file)-11);
				}
			}
			closedir($dir);
			sort($files, SORT_STRING);
			if (count($files) > 30) {
				$files = array_slice($files, 0, 30);  // maximum 30 templates
				trigger_error('Too many panel background templates - maximum 30!', E_USER_WARNING);
			}
			// sneak in standard entry
			$files[] = 'default';

			$head = 'Currently available panel backgrounds:';
			$list = array();
			$sid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(0.8, 0.1, 0.7), array('Icons128x32_1', 'Windowed'));
			foreach ($files as $file) {
				// store background in player object for jukeboxing
				$trkarr = array();
				$trkarr['panel'] = $file;
				$player->maplist[] = $trkarr;

				$list[] = array(str_pad($sid, 2, '0', STR_PAD_LEFT) . '.',
				                array('{#black}' . $file, $sid+7230));  // action id
				$sid++;
				if (++$lines > 14) {
					$player->msgs[] = $list;
					$lines = 0;
					$list = array();
				}
			}

			// add if last batch exists
			if (!empty($list)) {
				$player->msgs[] = $list;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($chat_parameter != '') {
			$panelbg = $chat_parameter;
			if (is_numeric($panelbg) && $panelbg > 0) {
				$sid = ltrim($panelbg, '0');
				$sid--;
				if (array_key_exists($sid, $player->maplist) &&
				    isset($player->maplist[$sid]['panel'])) {
					$panelbg = $player->maplist[$sid]['panel'];
				}
			}
			if ($panelbg == 'default') {
				$player->panelbg = $aseco->panelbg;
				$message = '{#server}» Panel background reset to server default {#highlite}' . $this->settings['panel_bg'] . '{#server} !';
				$this->setPanelBG($login, $this->settings['panel_bg']);

				$this->init_playerpanels($aseco, $player);
				$this->load_admpanel($aseco, $player);
				$this->display_votepanel($aseco, $player, $aseco->formatColors('{#emotic}') . 'Yes - F5', '$333No - F6', 2000);
			}
			else {
				// add file prefix
				if (strtolower(substr($panelbg, 0, 7)) != 'panelbg')
	                                $panelbg = 'PanelBG' . $panelbg;
				$panelbg_file = 'config/panels/' . $panelbg . '.xml';
				// load new background
				if (($panelbgdata = $aseco->parser->xmlToArray($panelbg_file)) && isset($panelbgdata['PANEL']['BACKGROUND'][0])) {
					$player->panelbg = $panelbgdata['PANEL']['BACKGROUND'][0];
					$message = '{#server}» Panel background {#highlite}' . $chat_parameter . '{#server} selected!';
					$this->setPanelBG($login, $panelbg);

					$this->init_playerpanels($aseco, $player);
					$this->load_admpanel($aseco, $player);
					$this->display_votepanel($aseco, $player, $aseco->formatColors('{#emotic}') . 'Yes - F5', '$333No - F6', 2000);
				}
				else {
					// Could not parse XML file
					trigger_error('[Panel] Could not read/parse panel background file ['. $panelbg_file .']!', E_USER_WARNING);
					$message = '{#server}» {#error}No valid panel background file, use {#highlite}$i /panelbg list {#error}!';
				}
			}
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$message = '{#server}» {#error}No panel background specified, use {#highlite}$i /panelbg help {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onStartup ($aseco) {

		$config_file = 'config/panels.xml';
		if ($settings = $aseco->parser->xmlToArray($config_file, true, true)) {
			// read the XML structure into an array
			$settings = $settings['SETTINGS'];

//			// set windows style (none = Card)
//			$this->settings['window_style'] = $settings['WINDOW_STYLE'][0];
//			if ($this->settings['window_style'] == '') {
//				$this->settings['window_style'] = 'Card';
//			}

			// set admin panel (none = no panel)
			$this->settings['admin_panel'] = $settings['ADMIN_PANEL'][0];

			// set records panel (none = no panel)
			$this->settings['records_panel'] = $settings['RECORDS_PANEL'][0];

			// set vote panel (none = no panel)
			$this->settings['vote_panel'] = $settings['VOTE_PANEL'][0];

			// display individual stats panels at scoreboard?
			$this->settings['sb_stats_panels'] = $aseco->string2bool($settings['SB_STATS_PANELS'][0]);

			// set panel background (none = Card)
			$this->settings['panel_bg'] = $settings['PANEL_BG'][0];
			if ($this->settings['panel_bg'] == '') {
				$this->settings['panel_bg'] = 'PanelBGCard';
			}

			// initialise default panel background
			$panelbg_file = 'config/panels/'. $this->settings['panel_bg'] .'.xml';
			$aseco->console('[Config] Load default panel background [{1}]', $panelbg_file);
			// load default background
			if (($this->panelbg = $aseco->parser->xmlToArray($panelbg_file)) && isset($this->panelbg['PANEL']['BACKGROUND'][0])) {
				$this->panelbg = $this->panelbg['PANEL']['BACKGROUND'][0];
			}
			else {
				// Could not parse XML file
				trigger_error('[Config] Could not read/parse panel background file ['. $panelbg_file .']!', E_USER_ERROR);
			}

			$this->panels = array();
			$this->panels['admin'] = '';
			$this->panels['records'] = '';
			$this->panels['vote'] = '';


			// check for default admin panel
			if ($this->settings['admin_panel'] != '') {
				$panel_file = 'config/panels/' . $this->settings['admin_panel'] . '.xml';
				$aseco->console('[Panel] Load default admin panel [{1}]', $panel_file);
				// load default panel
				if (!$this->panels['admin'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read admin panel file ['. $panel_file .']!', E_USER_ERROR);
				}
			}

			// check for default records panel
			if ($this->settings['records_panel'] != '') {
				$panel_file = 'config/panels/' . $this->settings['records_panel'] . '.xml';
				$aseco->console('[Panel] Load default records panel [{1}]', $panel_file);
				// load default panel
				if (!$this->panels['records'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read records panel file ['. $panel_file .']!', E_USER_ERROR);
				}
			}

			// check for default vote panel
			if ($this->settings['vote_panel'] != '') {
				$panel_file = 'config/panels/' . $this->settings['vote_panel'] . '.xml';
				$aseco->console('[Panel] Load default vote panel [{1}]', $panel_file);
				// load default panel
				if (!$this->panels['vote'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read vote panel file ['. $panel_file .']!', E_USER_ERROR);
				}
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

	public function onSync ($aseco) {
		if ($this->settings['sb_stats_panels']) {
			$panel_file = 'config/panels/Stats2.xml';
			$aseco->console('[Panel] Load stats panel [{1}]', $panel_file);
			if (!$aseco->statspanel = @file_get_contents($panel_file)) {
				// Could not read XML file
				trigger_error('[Panel] Could not read stats panel file '. $panel_file .']!', E_USER_ERROR);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		if ($this->settings['sb_stats_panels']) {
			// get list of online players
			$onlinelist = array(0); // init for implode
			foreach ($aseco->server->players->player_list as $pl) {
				$onlinelist[] = $pl->id;
			}

			// collect these players' record totals
			$recslist = array();
			$query = "
			SELECT
				`p`.`Login`,
				COUNT(`p`.`Id`) AS `Count`
			FROM `players` AS `p`, `records` AS `r`
			WHERE `p`.`Id` = `r`.`PlayerId`
			AND `p`.`Id` IN (". implode(',', $onlinelist) .")
			GROUP BY `p`.`Id`;
			";

			$result = $aseco->mysqli->query($query);
			if ($result) {
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_object()) {
						$recslist[$row->Login] = $row->Count;
					}
				}
				$result->free_result();

				// display stats panels for all these players
				foreach ($aseco->server->players->player_list as $pl) {
					$rank = $aseco->plugins['PluginRasp']->getRank($pl->login);
					$avg = preg_replace('/.+ Avg: /', '', $rank);
					$rank = preg_replace('/ Avg: .+/', '', $rank);
					$recs = (isset($recslist[$pl->login]) ? $recslist[$pl->login] : 0);
					$wins = ($pl->getWins() > $pl->wins ? $pl->getWins() : $pl->wins);
					$play = $aseco->formatTime($pl->getTimeOnline() * 1000, false);
					$dons = 0;
					if ( isset($aseco->plugins['PluginDonate']) ) {
						$dons = $aseco->plugins['PluginDonate']->getDonations($pl->login);
					}
					$this->display_statspanel($aseco, $pl, $rank, $avg, $recs, $wins, $play, $dons);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $data) {

		$this->record_defaults = array(
			'pd'	=> ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->placeholder['score'] : $this->placeholder['time']),
			'local'	=> ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->placeholder['score'] : $this->placeholder['time']),
			'dedi'	=> ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->placeholder['score'] : $this->placeholder['time']),
			'mx'	=> ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->placeholder['score'] : $this->placeholder['time']),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $data) {

		$this->statspanels_off($aseco);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Set default window style, panels & background
		$player->panels['admin'] = $this->replacePanelBG($this->panels['admin'], $this->panelbg);
		$player->panels['records'] = $this->replacePanelBG($this->panels['records'], $this->panelbg);
		$player->panels['vote'] = $this->replacePanelBG($this->panels['vote'], $this->panelbg);
		$player->panelbg = $this->panelbg;


		// Check for player's extra data
		$query = "
		SELECT
			`PlayerId`
		FROM `players_extra`
		WHERE `PlayerId` = ". $player->id .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			// Was retrieved
			if ($result->num_rows > 0) {
				$result->free_result();
			}
			else {
				// Could not be retrieved
				$result->free_result();

				// Update default Player data
				$panels = implode('/', array(
					$this->settings['admin_panel'],
					'',
					$this->settings['records_panel'],
					$this->settings['vote_panel']
				));
				$query = "
				UPDATE `players_extra`
				SET
					`Panels` = ". $aseco->mysqli->quote($panels) .",
					`PanelBG` = ". $aseco->mysqli->quote($this->settings['panel_bg']) ."
				WHERE `PlayerId`= ". $aseco->mysqli->quote($player->id) .";
				";
				$result = $aseco->mysqli->query($query);
				if (!$result) {
					trigger_error('[Panels] Could not update connecting player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
				}
			}
		}


		$this->init_playerpanels($aseco, $player);
		$this->load_admpanel($aseco, $player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// leave actions outside -7 - -100 & 7201 - 7222 & 7231 - 7262 to other handlers
		$action = (int) $answer[2];
		if ($action >= -100 && $action <= -49) {
			// get player & records panel
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$panel = $player->maplist[abs($action)-49]['panel'];

				// select new panel
				$aseco->releaseChatCommand('/recpanel '. $panel, $player->login);
			}
		}
		else if ($action >= -48 && $action <= -7) {
			// get player & admin panel
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$panel = $player->maplist[abs($action)-7]['panel'];

				// select new panel
				$aseco->releaseChatCommand('/admin panel '. $panel, $player->login);
			}
		}
		else if ($action >= 37 && $action <= 48) {
			// get player & vote panel
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$panel = $player->maplist[$action-37]['panel'];

				// select new panel
				$aseco->releaseChatCommand('/votepanel '. $panel, $player->login);
			}
		}
		else if ($action >= 7231 && $action <= 7262) {
			// get player & panel background
			if ($player = $aseco->server->players->getPlayer($answer[1])) {
				$panel = $player->maplist[abs($action)-7231]['panel'];

				// select new background
				$aseco->releaseChatCommand('/panelbg '. $panel, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function init_playerpanels ($aseco, $player) {

		if (($panels = $this->getPanels($player->login)) && ($panelbg = $this->getPanelBG($player->login))) {
			// load player's panel background
			$panelbg_file = 'config/panels/'. $panelbg .'.xml';
			if (($player->panelbg = $aseco->parser->xmlToArray($panelbg_file, true, true)) && isset($player->panelbg['PANEL']['BACKGROUND'][0])) {
				$player->panelbg = $player->panelbg['PANEL']['BACKGROUND'][0];
			}
			else {
				// Could not parse XML file
				trigger_error('[Panel] Could not read/parse panel background file ['. $panelbg_file .']!', E_USER_WARNING);
			}

			// load player's personal panels
			if ($panels['admin'] != '') {
				$panel_file = 'config/panels/' . $panels['admin'] . '.xml';
				if (!$player->panels['admin'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read admin panel file ['. $panel_file .']!', E_USER_WARNING);
				}
				$player->panels['admin'] = $this->replacePanelBG($player->panels['admin'], $player->panelbg);
			}
			else {
				$player->panels['admin'] = '';
			}

			if ($panels['records'] != '') {
				$panel_file = 'config/panels/' . $panels['records'] . '.xml';
				if (!$player->panels['records'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read records panel file ['. $panel_file .']!', E_USER_WARNING);
				}
				$player->panels['records'] = $this->replacePanelBG($player->panels['records'], $player->panelbg);
			}
			else {
				$player->panels['records'] = '';
			}

			if ($panels['vote'] != '') {
				$panel_file = 'config/panels/' . $panels['vote'] . '.xml';
				if (!$player->panels['vote'] = @file_get_contents($panel_file)) {
					// Could not read XML file
					trigger_error('[Panel] Could not read vote panel file ['. $panel_file .']!', E_USER_WARNING);
				}
				$player->panels['vote'] = $this->replacePanelBG($player->panels['vote'], $player->panelbg);
			}
			else {
				$player->panels['vote'] = '';
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function load_admpanel ($aseco, $player) {

		// check for any admin
		if ($aseco->isAnyAdmin($player) && $player->panels['admin'] != '') {
			$this->display_adminpanel($aseco, $player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function allvotepanels_on ($aseco, $login, $ycolor) {
		// enable all vote panels
		foreach ($aseco->server->players->player_list as $player) {
			// check if vote starter hasn't auto-voted
			if ($player->login != $login || (isset($aseco->plugins['PluginRaspVotes']) && !$aseco->plugins['PluginRaspVotes']->auto_vote_starter)) {
				// check for spectators
				if ($player->isspectator) {
					// check whether they can vote (no function keys)
					if ((isset($aseco->plugins['PluginRaspVotes']) && $aseco->plugins['PluginRaspVotes']->allow_spec_voting) || $aseco->isAnyAdmin($player)) {
						$this->display_votepanel($aseco, $player, $ycolor . 'Yes', '$333No', 0);
					}
				}
				else {
					// player, so function keys work
					$this->display_votepanel($aseco, $player, $ycolor . 'Yes - F5', '$333No - F6', 0);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function admin_panel ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if ($chat_parameter == 'help') {
			$header = '{#black}/admin panel$g will change the admin panel:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays available panels');
			$help[] = array('...', '{#black}default',
			                'Resets panel to server default');
			$help[] = array('...', '{#black}off',
			                'Disables admin panel');
			$help[] = array('...', '{#black}xxx',
			                'Selects admin panel xxx');
			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($chat_parameter == 'list') {
			$player->maplist = array();

			// read list of admin panel files
			$paneldir = 'config/panels/';
			$dir = opendir($paneldir);
			$files = array();
			while (($file = readdir($dir)) !== false) {
				if (strtolower(substr($file, 0, 5)) == 'admin' && strtolower(substr($file, -4)) == '.xml') {
					$files[] = substr($file, 5, strlen($file)-9);
				}
			}
			closedir($dir);
			sort($files, SORT_STRING);
			if (count($files) > 40) {
				$files = array_slice($files, 0, 40);  // maximum 40 templates
				trigger_error('Too many admin panel templates - maximum 40!', E_USER_WARNING);
			}
			// sneak in standard entries
			$files[] = 'default';
			$files[] = 'off';

			$head = 'Currently available admin panels:';
			$list = array();
			$pid = 1;
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(0.8, 0.1, 0.7), array('Icons128x128_1', 'Custom'));
			foreach ($files as $file) {
				// store panel in player object for jukeboxing
				$trkarr = array();
				$trkarr['panel'] = $file;
				$player->maplist[] = $trkarr;

				$list[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) . '.',
				                array('{#black}' . $file, -6-$pid));  // action id
				$pid++;
				if (++$lines > 14) {
					$player->msgs[] = $list;
					$lines = 0;
					$list = array();
				}
			}

			// add if last batch exists
			if (!empty($list)) {
				$player->msgs[] = $list;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($chat_parameter != '') {
			$panel = $chat_parameter;
			if (is_numeric($panel) && $panel > 0) {
				$pid = ltrim($panel, '0');
				$pid--;
				if (array_key_exists($pid, $player->maplist) &&
				    isset($player->maplist[$pid]['panel'])) {
					$panel = $player->maplist[$pid]['panel'];
				}
			}
			if ($panel == 'off') {
				$player->panels['admin'] = '';
				$this->adminpanel_off($aseco, $login);
				$message = '{#server}» Admin panel disabled!';
				$this->setPanel($login, 'admin', '');
			}
			else if ($panel == 'default') {
				$player->panels['admin'] = $this->replacePanelBG($this->panels['admin'], $player->panelbg);
				$this->load_admpanel($aseco, $player);
				$message = '{#server}» Admin panel reset to server default {#highlite}' . substr($this->settings['admin_panel'], 5) . '{#server} !';
				$this->setPanel($login, 'admin', $this->settings['admin_panel']);
			}
			else {
				// add file prefix
				if (strtolower(substr($panel, 0, 5)) != 'admin')
					$panel = 'Admin' . $panel;
				$panel_file = 'config/panels/' . $panel . '.xml';
				// load new panel
				if ($paneldata = @file_get_contents($panel_file)) {
					$player->panels['admin'] = $this->replacePanelBG($paneldata, $player->panelbg);
					$this->load_admpanel($aseco, $player);
					$message = '{#server}» Admin panel {#highlite}' . $chat_parameter . '{#server} selected!';
					$this->setPanel($login, 'admin', $panel);
				}
				else {
					// Could not read XML file
					trigger_error('[Panel] Could not read admin panel file ['. $panel_file .']!', E_USER_WARNING);
					$message = '{#server}» {#error}No valid admin panel file, use {#highlite}$i /admin panel list {#error}!';
				}
			}
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$message = '{#server}» {#error}No admin panel specified, use {#highlite}$i /admin panel help {#error}!';
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPanels ($login) {
		global $aseco;

		// Get panels from player
		$query = "
		SELECT
			`Panels`
		FROM `players_extra`
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			$dbextra = $result->fetch_object();
			$result->free_result();

			if ($dbextra->Panels != '') {
				$panel = explode('/', $dbextra->Panels);
				$panels = array();
				$panels['admin'] = $panel[0];
				$panels['records'] = $panel[2];
				$panels['vote'] = $panel[3];
				return $panels;
			}
			else {
				return false;
			}
		}
		else {
			trigger_error('[LocalRecords] Could not get player\'s panels! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setPanel ($login, $type, $panel) {
		global $aseco;

		// Update player's panels
		$panels = $this->getPanels($login);
		$panels[$type] = $panel;

		$query = "
		UPDATE `players_extra` SET
			`Panels` = ". $aseco->mysqli->quote($panels['admin'] .'//'. $panels['records'] .'/'. $panels['vote']) ."
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not update player\'s panels! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPanelBG ($login) {
		global $aseco;

		// Get player's panel background
		$query = "
		SELECT
			`PanelBG`
		FROM `players_extra`
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			$dbextra = $result->fetch_object();
			$result->free_result();
			return $dbextra->PanelBG;
		}
		else {
			trigger_error('[LocalRecords] Could not get player\'s panel background! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setPanelBG ($login, $panelbg) {
		global $aseco;

		// update player's panel background
		$query = "
		UPDATE `players_extra` SET
			`PanelBG` = ". $aseco->mysqli->quote($panelbg) ."
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[LocalRecords] Could not update panel background for Player! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Sets a panel's background
	// $xml     : panel XML in which to set the background
	// $panelbg : panel background style and substyle
	public function replacePanelBG ($xml, $panelbg) {
		return str_replace('%STYLE%', $panelbg['STYLE'][0], str_replace('%SUBST%', $panelbg['SUBSTYLE'][0], $xml));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays an Admin panel
	 *
	 * $player: player to send panel to
	 */
	public function display_adminpanel ($aseco, $player) {

		// build manialink
		$xml = $player->panels['admin'];
		$aseco->addManialink($xml, $player->login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Disables an Admin panel
	 *
	 * $login: player login to disable panel for
	 */
	public function adminpanel_off ($aseco, $login) {

		$xml = '<manialink id="UASECO-3"></manialink>';
		$aseco->addManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Disables a Records panel
	 *
	 * $login: player login to disable panel for
	 */
	public function recpanel_off ($aseco, $login) {

		$xml = '<manialink id="UASECO-4"></manialink>';
		$aseco->addManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays a Vote panel
	 *
	 * $player : player to send panel to
	 * $yesstr : string for the Yes button
	 * $nostr  : string for the No button
	 * $timeout: timeout for temporary panel (used only by /votepanel list)
	 */
	public function display_votepanel ($aseco, $player, $yesstr, $nostr, $timeout) {

		// build manialink
		$xml = str_replace(
			array('%YES%', '%NO%'),
			array($yesstr, $nostr),
			$player->panels['vote']
		);

		// disable panel once clicked
		$aseco->addManialink($xml, $player->login, $timeout, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Disables a Vote panel
	 *
	 * $login: player login to disable panel for
	 */
	public function votepanel_off ($aseco, $login) {

		$xml = '<manialink id="UASECO-5"></manialink>';
		$aseco->addManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Disables all Vote panels
	 */
	public function allvotepanels_off ($aseco) {

		$xml = '<manialink id="UASECO-5"></manialink>';
		$aseco->addManialink($xml, false, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays a Scoreboard Stats panel
	 *
	 * $player : player to send panel to
	 * $rank   : server rank
	 * $avg    : record average
	 * $recs   : records total
	 * $wins   : wins total
	 * $play   : session play time
	 * $dons   : donations total
	 */
	public function display_statspanel ($aseco, $player, $rank, $avg, $recs, $wins, $play, $dons) {

		// build manialink
		$xml = str_replace(
			array('%RANK%', '%AVG%', '%RECS%', '%WINS%', '%PLAY%', '%DONS%'),
			array($rank, $avg, $recs, $wins, $play, $dons),
			$aseco->statspanel
		);
		$xml = $this->set_panel_bg($xml, $player->panelbg);

		$aseco->addManialink($xml, $player->login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Disables all Scoreboard Stats panels
	public function statspanels_off ($aseco) {

		$xml = '<manialink id="UASECO-9"></manialink>';
		$aseco->addManialink($xml, false, 0);
	}
}

?>
