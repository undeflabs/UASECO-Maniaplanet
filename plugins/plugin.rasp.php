<?php
/*
 * Plugin: Rasp
 * ~~~~~~~~~~~~
 * » Provides rank and personal best handling, and related chat commands.
 * » Based upon plugin.rasp.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-26
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
 *  - includes/core/gbxdatafetcher.class.php
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.local_records.php
 *  - plugins/plugin.rasp_votes.php
 *  - plugins/plugin.rasp_jukebox.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginRasp();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRasp extends Plugin {
	public $aseco;
	public $map_list_cache;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides rank and personal best handling, and related chat commands.');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginRaspJukebox',	Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRaspVotes',		Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onStartup',		'onStartup');
		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onMapListModified',	'onMapListModified');
		$this->registerEvent('onBeginMap1',		'onBeginMap1');
		$this->registerEvent('onEndMap',		'onEndMap');
		$this->registerEvent('onPlayerFinish',		'onPlayerFinish');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');

		$this->registerChatCommand('pb',	'chat_pb',	'Shows your personal best on current map',	Player::PLAYERS);
		$this->registerChatCommand('rank',	'chat_rank',	'Shows your current server rank',		Player::PLAYERS);
		$this->registerChatCommand('top10',	'chat_top10',	'Displays top 10 best ranked players',		Player::PLAYERS);
		$this->registerChatCommand('top100',	'chat_top100',	'Displays top 100 best ranked players',		Player::PLAYERS);
		$this->registerChatCommand('topwins',	'chat_topwins',	'Displays top 100 victorious players',		Player::PLAYERS);
		$this->registerChatCommand('active',	'chat_active',	'Displays top 100 most active players',		Player::PLAYERS);


		$this->map_list_cache = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onStartup ($aseco) {

		// Get settings
		$this->readSettings();

		$aseco->console('[Rasp] Checking database structure...');
		if (!$this->checkTables($aseco)) {
			trigger_error('[Rasp] ERROR: Table structure incorrect, use [newinstall/database/uaseco.sql] to correct this!', E_USER_ERROR);
		}
		$aseco->console('[Rasp] ...successfully done!');

		$aseco->console('[Rasp] Cleaning up unused data...');
		$this->cleanData($aseco);

		// prune records and rs_times entries for maps deleted from server
		if ($this->prune_records_times) {
			$aseco->console('[Rasp] Pruning `records` and `rs_times` for deleted maps:');
			$maps = $aseco->server->maps->map_list;

			// Get list of maps IDs with records in the database
			$query = "
			SELECT DISTINCT
				`MapId`
			FROM `records`;
			";

			$res = $aseco->mysqli->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					$removed = array();
					while ($row = $res->fetch_row()) {
						$map = $row[0];
						// delete records & rs_times if it's not in server's maps list
						if (!in_array($map, $maps)) {
							$removed[] = $map;
							$query = 'DELETE FROM `records` WHERE `MapId` = '. $map .';';
							$aseco->mysqli->query($query);

							$query = 'DELETE FROM `rs_times` WHERE `MapId` = '. $map .';';
							$aseco->mysqli->query($query);
						}
					}
					$res->free_result();
					if (count($removed) > 0) {
						$aseco->console('[Rasp] » Cleaned data from map ['. implode(', ', $removed) .'].');
					}
				}
			}
			$aseco->console('[Rasp] ...successfully done!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $data) {

		$sepchar = substr($aseco->server->mapdir, -1, 1);
		if ($sepchar == '\\') {
			$this->mxdir = str_replace('/', $sepchar, $this->mxdir);
		}

		if (!file_exists($aseco->server->mapdir . $this->mxdir)) {
			if (!mkdir($aseco->server->mapdir . $this->mxdir)) {
				$aseco->console_text('[Rasp] ERROR: MX Directory (' . $aseco->server->mapdir . $this->mxdir . ') cannot be created');
			}
		}

		if (!is_writeable($aseco->server->mapdir . $this->mxdir)) {
			$aseco->console_text('[Rasp] ERROR: MX Directory (' . $aseco->server->mapdir . $this->mxdir . ') cannot be written to');
		}

		// check if user /add votes are enabled
		if ($this->feature_mxadd) {
			if (!file_exists($aseco->server->mapdir . $this->mxtmpdir)) {
				if (!mkdir($aseco->server->mapdir . $this->mxtmpdir)) {
					$aseco->console_text('[Rasp] ERROR: MXtmp Directory (' . $aseco->server->mapdir . $this->mxtmpdir . ') cannot be created');
					$this->feature_mxadd = false;
				}
			}

			if (!is_writeable($aseco->server->mapdir . $this->mxtmpdir)) {
				$aseco->console_text('[Rasp] ERROR: MXtmp Directory (' . $aseco->server->mapdir . $this->mxtmpdir . ') cannot be written to');
				$this->feature_mxadd = false;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onMapListModified ($aseco, $data) {

		// clear cache if map list modified
		if ($data[2]) {
			$this->map_list_cache = array();
			if ($aseco->debug) {
				$aseco->console_text('maps cache cleared');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {

		if ($this->feature_stats && !$aseco->server->isrelay) {
			foreach ($aseco->server->players->player_list as $pl) {
				$this->showPb($pl, $map->id, $this->always_show_pb);
			}
		}

		if ($this->reset_cache_start) {
			$this->map_list_cache = array();
			if ($aseco->debug) {
				$aseco->console_text('maps cache reset');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Check for relay server
		if ($aseco->server->isrelay) {
			return;
		}

		if ($this->feature_ranks) {
			if (isset($aseco->plugins['PluginRaspJukebox']) && !$aseco->plugins['PluginRaspJukebox']->mxplayed) {
				$this->resetRanks($aseco);
			}
			foreach ($aseco->server->players->player_list as $pl) {
				$this->showRank($pl->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {
		if ($this->feature_ranks) {
			$this->showRank($player->login);
		}
		if ( ($this->feature_stats) && ($aseco->startup_phase == false) ) {
			$this->showPb($player, $aseco->server->maps->current->id, $this->always_show_pb);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// check for actual finish & no Laps mode
		if ($this->feature_stats && $finish_item->score > 0 && $aseco->server->gameinfo->mode != Gameinfo::LAPS) {
			$this->insertTime(
				$finish_item,
				isset($finish_item->checkpoints) ? implode(',', $finish_item->checkpoints) : ''
			);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_pb ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($this->feature_stats) {
			$player = $aseco->server->players->getPlayer($login);
			$this->showPb($player, $aseco->server->maps->current->id, true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_rank ($aseco, $login, $chat_command, $chat_parameter) {

		if ($this->feature_ranks) {
			$this->showRank($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_top10 ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);
		$top = 10;

		$query = "
		SELECT
			`p`.`NickName`,
			`r`.`Avg`
		FROM `players` AS `p`
		LEFT JOIN `rs_rank` AS `r` ON `p`.`Id` = `r`.`PlayerId`
		WHERE `r`.`Avg` != 0
		ORDER BY `r`.`Avg` ASC
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$i = 1;
				$recs = array();
				while ($row = $res->fetch_object()) {
					$nickname = $row->NickName;
					if (!$aseco->settings['lists_colornicks']) {
						$nickname = $aseco->stripColors($nickname);
					}
					$recs[] = array(
						$i .'.',
						sprintf("%4.1F", $row->Avg / 10000),
						$nickname,
					);
					$i++;
				}


				// Setup settings for Window
				$settings_title = array(
					'icon'	=> 'BgRaceScore2,LadderRank',
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
				$window->setContent('Current TOP 10 Players', $recs);
				$window->send($player, 0, false);
			}
			else {
				$aseco->sendChatMessage('{#server}» {#error}No ranked players found!', $player->login);
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_top100 ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);

		$top = 100;
		$query = "
		SELECT
			`p`.`NickName`,
			`r`.`Avg`
		FROM `players` AS `p`
		LEFT JOIN `rs_rank` AS `r` ON `p`.`Id` = `r`.`PlayerId`
		WHERE `r`.`Avg` != 0
		ORDER BY `r`.`Avg` ASC
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$i = 1;
				$recs = array();
				while ($row = $res->fetch_object()) {
					$nickname = $row->NickName;
					if (!$aseco->settings['lists_colornicks']) {
						$nickname = $aseco->stripColors($nickname);
					}
					$recs[] = array(
						$i .'.',
						sprintf("%4.1F", $row->Avg / 10000),
						$nickname,
					);
					$i++;
				}


				// Setup settings for Window
				$settings_title = array(
					'icon'	=> 'BgRaceScore2,LadderRank',
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
				$window->setContent('Current TOP 100 Players', $recs);
				$window->send($player, 0, false);
			}
			else {
				$aseco->sendChatMessage('{#server}» {#error}No ranked players found!', $player->login);
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_topwins ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);
		$top = 100;

		$query = "
		SELECT
			`NickName`,
			`Wins`
		FROM `players`
		ORDER BY `Wins` DESC
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$i = 1;
				$wins = array();
				while ($row = $res->fetch_object()) {
					$nickname = $row->NickName;
					if (!$aseco->settings['lists_colornicks']) {
						$nickname = $aseco->stripColors($nickname);
					}
					$wins[] = array(
						$i .'.',
						$aseco->formatNumber($row->Wins),
						$nickname,
					);
					$i++;
				}


				// Setup settings for Window
				$settings_title = array(
					'icon'	=> 'BgRaceScore2,LadderRank',
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
				$window->setContent('Current TOP 100 Victors', $wins);
				$window->send($player, 0, false);
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_active ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);

		$top = 100;
		$query = "
		SELECT
			`NickName`,
			`TimePlayed`
		FROM `players`
		ORDER BY `TimePlayed` DESC
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			$i = 1;
			$active = array();
			while ($row = $res->fetch_object()) {
				$nickname = $row->NickName;
				if (!$aseco->settings['lists_colornicks']) {
					$nickname = $aseco->stripColors($nickname);
				}
				$active[] = array(
					$i .'.',
					$aseco->formatNumber(round($row->TimePlayed / 3600), 0) .' h',
					$nickname,
				);
				$i += 1;
			}
			$res->free_result();


			// Setup settings for Window
			$settings_title = array(
				'icon'	=> 'BgRaceScore2,LadderRank',
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
			$window->setContent('TOP 100 of the most active Players', $active);
			$window->send($player, 0, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkTables ($aseco) {

		$aseco->console('[Rasp] » Checking table `rs_rank`.');
		$query = "
		CREATE TABLE IF NOT EXISTS `rs_rank` (
			`PlayerId` mediumint(9) NOT NULL DEFAULT '0',
			`Avg` float NOT NULL DEFAULT '0',
			KEY `PlayerId` (`PlayerId`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE 'utf8_bin';
		";
		$aseco->mysqli->query($query);


		$aseco->console('[Rasp] » Checking table `rs_times`.');
		$query = "
		CREATE TABLE IF NOT EXISTS `rs_times` (
			`Id` int(11) NOT NULL AUTO_INCREMENT,
			`MapId` mediumint(9) NOT NULL DEFAULT '0',
			`PlayerId` mediumint(9) NOT NULL DEFAULT '0',
			`Score` int(11) NOT NULL DEFAULT '0',
			`Date` int(10) unsigned NOT NULL default 0,
			`Checkpoints` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			PRIMARY KEY (`Id`),
			KEY `PlayerMapId` (`PlayerId`,`MapId`),
			KEY `MapId` (`MapId`),
			KEY `Score` (`Score`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;
		";
		$aseco->mysqli->query($query);


		$aseco->console('[Rasp] » Checking table `rs_karma`.');
		$query = "
		CREATE TABLE IF NOT EXISTS `rs_karma` (
			`Id` int(11) NOT NULL AUTO_INCREMENT,
			`MapId` mediumint(9) NOT NULL DEFAULT '0',
			`PlayerId` mediumint(9) NOT NULL DEFAULT '0',
			`Score` tinyint(3) NOT NULL DEFAULT '0',
			PRIMARY KEY (`Id`),
			UNIQUE KEY `PlayerMapId` (`PlayerId`,`MapId`),
			KEY `MapId` (`MapId`),
			KEY `Score` (`Score`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE 'utf8_bin' AUTO_INCREMENT=1;
		";
		$aseco->mysqli->query($query);


		// check for rs_* tables
		$tables = array();
		$res = $aseco->mysqli->query('SHOW TABLES');
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_row()) {
					$tables[] = $row[0];
				}
			}
			$res->free_result();
		}

		$check = array();
		$check[1] = in_array('rs_rank', $tables);
		$check[2] = in_array('rs_times', $tables);
		$check[3] = in_array('rs_karma', $tables);

		return ($check[1] && $check[2] && $check[3]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function cleanData ($aseco) {

		$aseco->console('[Rasp] » Cleaning up `maps`.');
		$sql = "DELETE FROM `maps` WHERE `Uid` = '';";
		$aseco->mysqli->query($sql);

		$aseco->console('[Rasp] » Cleaning up `players`.');
		$sql = "DELETE FROM `players` WHERE `Login` = '';";
		$aseco->mysqli->query($sql);


		if (!$this->prune_records_times) {
			$aseco->console('[Rasp] ...successfully done!');
			return;
		}

		// Delete records for deleted maps
		$deletelist = array();
		$sql = "
		SELECT DISTINCT
			`r`.`MapId`,
			`m`.`Id`
		FROM `records` AS `r`
		LEFT JOIN `maps` AS `m` ON `r`.`MapId` = `m`.`Id`
		WHERE `m`.`Id` IS NULL;
		";
		$res = $aseco->mysqli->query($sql);
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_row()) {
				$deletelist[] = $row[0];
			}
			$res->free_result();

			$aseco->console('[Rasp] » Deleting records for deleted maps: '. implode(', ', $deletelist));
			$sql = "
			DELETE FROM `records`
			WHERE `MapId` IN (". implode(', ', $deletelist) .");
			";
			$aseco->mysqli->query($sql);
		}


		// Delete records for deleted players
		$deletelist = array();
		$sql = "
		SELECT DISTINCT
			`r`.`PlayerId`,
			`p`.`Id`
		FROM `records` AS `r`
		LEFT JOIN `players` AS `p` ON `r`.`PlayerId` = `p`.`Id`
		WHERE `p`.`Id` IS NULL;
		";
		$res = $aseco->mysqli->query($sql);
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_row()) {
				$deletelist[] = $row[0];
			}
			$res->free_result();

			$aseco->console('[Rasp] » Deleting records for deleted players: '. implode(', ', $deletelist));
			$sql = "
			DELETE FROM `records`
			WHERE `PlayerId` IN (". implode(', ', $deletelist) .");
			";
			$aseco->mysqli->query($sql);
		}


		// Delete from `rs_times` for deleted maps
		$deletelist = array();
		$sql = "
		SELECT DISTINCT
			`r`.`MapId`,
			`m`.`Id`
		FROM `rs_times` AS `r`
		LEFT JOIN `maps` AS `m` ON `r`.`MapId` = `m`.`Id`
		WHERE `m`.`Id` IS NULL;
		";
		$res = $aseco->mysqli->query($sql);
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_row()) {
				$deletelist[] = $row[0];
			}
			$res->free_result();

			$aseco->console('[Rasp] » Deleting rs_times for deleted maps: '. implode(', ', $deletelist));
			$sql = "
			DELETE FROM `rs_times`
			WHERE `MapId` IN (". implode(', ', $deletelist) .");
			";
			$aseco->mysqli->query($sql);
		}


		// Delete from `rs_times` for deleted players
		$deletelist = array();
		$sql = "
		SELECT DISTINCT
			`r`.`PlayerId`,
			`p`.`Id`
		FROM `rs_times` AS `r`
		LEFT JOIN `players` AS `p` ON `r`.`PlayerId` = `p`.`Id`
		WHERE `p`.`Id` IS NULL;
		";
		$res = $aseco->mysqli->query($sql);
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_row()) {
				$deletelist[] = $row[0];
			}
			$res->free_result();

			$aseco->console('[Rasp] » Deleting rs_times for deleted players: '. implode(', ', $deletelist));
			$sql = "
			DELETE FROM `rs_times`
			WHERE `PlayerId` IN (". implode(', ', $deletelist) .");
			";
			$aseco->mysqli->query($sql);
		}

		$aseco->console('[Rasp] ...successfully done!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function resetRanks ($aseco) {

		$players = array();
		$aseco->console('[Rasp] Calculating ranks...');
		$maps = $aseco->server->maps->map_list;
		$total = count($maps);

		// Erase old average data
		$aseco->mysqli->query('TRUNCATE TABLE `rs_rank`;');

		// get list of players with at least $minrecs records (possibly unranked)
		$query = "
		SELECT
			`PlayerId`,
			COUNT(*) AS `Cnt`
		FROM `records`
		GROUP BY `PlayerId`
		HAVING `Cnt` >= ". $this->minrank .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			while ($row = $res->fetch_object()) {
				$players[$row->PlayerId] = array(0, 0);  // sum, count
			}
			$res->free_result();

			if (!empty($players)) {
				// get ranked records for all maps
				$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
				foreach ($maps as $map) {
					$query = "
					SELECT
						`PlayerId`
					FROM `records`
					WHERE `MapId` = ". $map->id ."
					ORDER BY `Score` ". $order .", `Date` ASC
					LIMIT ". $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() .";
					";

					$res = $aseco->mysqli->query($query);
					if ($res) {
						if ($res->num_rows > 0) {
							$i = 1;
							while ($row = $res->fetch_object()) {
								$pid = $row->PlayerId;
								if (isset($players[$pid])) {
									$players[$pid][0] += $i;
									$players[$pid][1] ++;
								}
								$i++;
							}
						}
						$res->free_result();
					}
				}

				// one-shot insert for queries up to 1 MB (default max_allowed_packet),
				// or about 75K rows at 14 bytes/row (avg)
				$query = 'INSERT INTO `rs_rank` VALUES ';
				// compute each player's new average score
				foreach ($players as $player => $ranked) {
					// ranked maps sum + $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() rank for all remaining maps
					$avg = ($ranked[0] + ($total - $ranked[1]) * $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) / $total;
					$query .= '('. $player .','. round($avg * 10000) .'),';
				}
				$query = substr($query, 0, strlen($query)-1);  // strip trailing ','
				$aseco->mysqli->query($query);
				if ($aseco->mysqli->affected_rows === -1) {
					trigger_error('[Rasp] ERROR: Could not insert any player averages! ('. $aseco->mysqli->errmsg() .')', E_USER_WARNING);
				}
				else if ($aseco->mysqli->affected_rows != count($players)) {
					trigger_error('[Rasp] ERROR: Could not insert all '. count($players) .' player averages! ('. $aseco->mysqli->errmsg() .')', E_USER_WARNING);
					// increase MySQL's max_allowed_packet setting
				}
			}

		}
		$aseco->console('[Rasp] ...Done!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function showPb ($player, $map, $always_show) {
		global $aseco;

		$ret = array();
		$found = false;
		// find ranked record
		for ($i = 0; $i < $aseco->plugins['PluginLocalRecords']->records->getMaxRecords(); $i++) {
			if (($rec = $aseco->plugins['PluginLocalRecords']->records->getRecord($i)) !== false) {
				if ($rec->player->login == $player->login) {
					$ret['time'] = $rec->score;
					$ret['rank'] = $i + 1;
					$found = true;
					break;
				}
			}
			else {
				break;
			}
		}

		// check whether to show PB (e.g. for /pb)
		if (!$always_show) {
			// check for ranked record that's already shown at map start,
			// or for player's records panel showing it
			if (($found && $aseco->settings['show_recs_before'] == 2) || $player->panels['records'] != '') {
				return;
			}
		}

		if (!$found) {
			// find unranked time/score
			$order = ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC');
			$query2 = "
			SELECT
				`Score`
			FROM `rs_times`
			WHERE `PlayerId` = ". $player->id ."
			AND `MapId` = ". $map ."
			ORDER BY `Score` ". $order ."
			LIMIT 1;
			";
			$res2 = $aseco->mysqli->query($query2);
			if ($res2) {
				if ($res2->num_rows > 0) {
					$row = $res2->fetch_object();
					$ret['time'] = $row->Score;
					$ret['rank'] = '$nUNRANKED$m';
					$found = true;
				}
				$res2->free_result();
			}
		}

		// Compute average time of last $this->maxavg times
		$query = "
		SELECT
			`Score`
		FROM `rs_times`
		WHERE `PlayerId` = ". $player->id ."
		AND `MapId` = ". $map ."
		ORDER BY `Date` DESC
		LIMIT ". $this->maxavg .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			$size = $res->num_rows;
			if ($size > 0) {
				$total = 0;
				while ($row = $res->fetch_object()) {
					$total += $row->Score;
				}
				$avg = floor($total / $size);
				if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS) {
					$avg = $aseco->formatTime($avg);
				}
			}
			else {
				$avg = 'No Average';
			}
			$res->free_result();
		}

		if ($found) {
			$message = $aseco->formatText($this->messages['PB'][0],
				($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $ret['time'] : $aseco->formatTime($ret['time'])),
				$ret['rank'],
				$avg
			);
			$aseco->sendChatMessage($message, $player->login);
		}
		else {
			$message = $this->messages['PB_NONE'][0];
			$aseco->sendChatMessage($message, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function showRank ($login) {
		global $aseco;

		$pid = $aseco->server->players->getPlayerId($login);
		$query = "
		SELECT
			`Avg`
		FROM `rs_rank`
		WHERE `PlayerId` = ". $pid .";
		";
		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$row = $res->fetch_array();
				$query2 = 'SELECT `PlayerId` FROM `rs_rank` ORDER BY `Avg` ASC;';
				$res2 = $aseco->mysqli->query($query2);
				if ($res2) {
					$rank = 1;
					while ($row2 = $res2->fetch_array()) {
						if ($row2['PlayerId'] == $pid) {
							break;
						}
						$rank++;
					}
					$message = $aseco->formatText($this->messages['RANK'][0],
						$rank,
						$res2->num_rows,
						sprintf("%4.1F", $row['Avg'] / 10000)
					);
					$aseco->sendChatMessage($message, $login);
					$res2->free_result();
				}
			}
			else {
				$message = $aseco->formatText($this->messages['RANK_NONE'][0], $this->minrank);
				$aseco->sendChatMessage($message, $login);
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getRank ($login) {
		global $aseco;

		$pid = $aseco->server->players->getPlayerId($login);
		$query = "
		SELECT
			`Avg`
		FROM `rs_rank`
		WHERE `PlayerId` = ". $pid .";
		";
		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$row = $res->fetch_array();
				$query2 = "
				SELECT
					`PlayerId`
				FROM `rs_rank`
				ORDER BY `Avg` ASC;
				";
				$res2 = $aseco->mysqli->query($query2);
				if ($res2) {
					$rank = 1;
					while ($row2 = $res2->fetch_array()) {
						if ($row2['PlayerId'] == $pid) {
							break;
						}
						$rank++;
					}
					$message = $aseco->formatText('{1}/{2} Avg: {3}',
						$rank,
						$res2->num_rows,
						sprintf("%4.1F", $row['Avg'] / 10000)
					);
					$res2->free_result();
				}
			}
			else {
				$message = 'None';
			}
			$res->free_result();
			return $message;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function insertTime ($time, $cps) {
		global $aseco;

		$pid = $time->player->id;
		if ($pid != 0) {
			$query = "
			INSERT INTO `rs_times` (
				`MapId`,
				`PlayerId`,
				`Score`,
				`Date`,
				`Checkpoints`
			)
			VALUES (
				". $time->map->id .",
				". $pid .",
				". $time->score .",
				UNIX_TIMESTAMP(),
				". $aseco->mysqli->quote($cps) ."
			);
			";
			$aseco->mysqli->query($query);
			if ($aseco->mysqli->affected_rows === -1) {
				trigger_error('[Rasp] ERROR: Could not insert time! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			}
		}
		else {
			trigger_error('[Rasp] ERROR: Could not get Player ID for ['. $time->player->login .']!', E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function deleteTime ($cid, $pid) {
		global $aseco;

		$query = "
		DELETE FROM `rs_times`
		WHERE `MapId` = ". $cid ."
		AND `PlayerId` = ". $pid .";
		";
		$aseco->mysqli->query($query);
		if ($aseco->mysqli->affected_rows === -1) {
			trigger_error('[Rasp] ERROR: Could not remove time(s)! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getAllMaps ($player, $wildcard, $env) {
		global $aseco;

		$player->maplist = array();

		// Get list of ranked records
		$reclist = $player->getRecords();

		$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
		$head = 'Maps on this Server:';
		$msg = array();
		$msg[] = array('Id', 'Rec', 'Name', 'Author');

		$tid = 1;
		$lines = 0;
		$player->msgs = array();

		// Reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colormaps'] ? 0.2 : 0);
		$player->msgs[0] = array(1, $head, array(1.22+$extra, 0.12, 0.1, 0.6+$extra, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

		foreach ($aseco->server->maps->map_list as $map) {
			// Check for wildcard, map name or author name
			if ($wildcard == '*') {
				$pos = 0;
			}
			else {
				$pos = stripos($map->name_stripped, $wildcard);
				if ($pos === false) {
					$pos = stripos($map->author, $wildcard);
				}
			}

			// Check for environment
			if ($env == '*') {
				$pose = 0;
			}
			else {
				$pose = stripos($map->environment, $env);
			}

			// Check for any match
			if ($pos !== false && $pose !== false) {
				// store map in player object for jukeboxing
				$trkarr = array();
				$trkarr['uid']		= $map->uid;
				$trkarr['name']		= $map->name;
				$trkarr['author']	= $map->author;
				$trkarr['environment']	= $map->environment;
				$trkarr['filename']	= $map->filename;
				$player->maplist[] = $trkarr;

				// Format map name
				$mapname = $map->name;
				if (!$aseco->settings['lists_colormaps']) {
					$mapname = $map->name_stripped;
				}

				// Grey out if in history
				if (isset($aseco->plugins['PluginRaspJukebox']) && in_array($map->uid, $aseco->plugins['PluginRaspJukebox']->jb_buffer)) {
					$mapname = '{#grey}'. $map->name_stripped;
				}
				else {
					$mapname = '{#black}'. $mapname;
					// add clickable button
					if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
						$mapname = array($mapname, $tid+100);  // action id
					}
				}

				// Format author name
				$mapauthor = $map->author;

				// Add clickable button
				if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
					$mapauthor = array($mapauthor, -100-$tid);  // action id
				}

				// Format env name
				$mapenv = $map->environment;

				// Add clickable button
				if ($aseco->settings['clickable_lists']) {
					$mapenv = array($mapenv, $envids[$mapenv]);  // action id
				}

				// Get corresponding record
				$pos = isset($reclist[$map->uid]) ? $reclist[$map->uid] : 0;
				$pos = ($pos >= 1 && $pos <= $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) ? str_pad($pos, 2, '0', STR_PAD_LEFT) : '--';

				$msg[] = array(
					str_pad($tid, 3, '0', STR_PAD_LEFT) .'.',
					$pos .'.',
					$mapname,
					$mapauthor
				);
				$tid++;
				if (++$lines > 14) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', 'Rec', 'Name', 'Author');
				}
			}
		}

		// Add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapData ($filename, $rtnvotes) {
		global $aseco;

		$ret = array();
		if (!file_exists($filename)) {
			$ret['name'] = 'file not found';
			$ret['votes'] = 500;
			return $ret;
		}

		// check whether votes are needed
		if ($rtnvotes && isset($aseco->plugins['PluginRaspVotes'])) {
			$ret['votes'] = $aseco->plugins['PluginRaspVotes']->required_votes($this->mxvoteratio);
			if ($aseco->debug) {
				$ret['votes'] = 1;
			}
		}
		else {
			$ret['votes'] = 1;
		}

		$gbx = new GBXChallMapFetcher();
		try {
			$gbx->processFile($filename);

			$ret['uid'] = $gbx->uid;
			$ret['name'] = $aseco->stripNewlines($gbx->name);
			$ret['author'] = $gbx->author;
			$ret['environment'] = $gbx->envir;
			$ret['authortime'] = $gbx->authorTime;
			$ret['authorscore'] = $gbx->authorScore;
//			$ret['coppers'] = $gbx->cost;
		}
		catch (Exception $e) {
			$ret['votes'] = 500;
			$ret['name'] = $e->getMessage();
		}
		return $ret;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function readSettings () {
		global $aseco;

		$config_file = 'config/rasp.xml';	// Settings XML File
		if (file_exists($config_file)) {
			$aseco->console('[Rasp] Loading config file ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {

				/***************************** MESSAGES **************************************/
				$this->messages			= $xml['RASP']['MESSAGES'][0];

				/***************************** FEATURES **************************************/
				$this->feature_ranks		= $aseco->string2bool($xml['RASP']['FEATURE_RANKS'][0]);
				$this->nextrank_show_rp		= $aseco->string2bool($xml['RASP']['NEXTRANK_SHOW_POINTS'][0]);
				$this->feature_stats		= $aseco->string2bool($xml['RASP']['FEATURE_STATS'][0]);
				$this->always_show_pb		= $aseco->string2bool($xml['RASP']['ALWAYS_SHOW_PB'][0]);
				$this->feature_karma		= $aseco->string2bool($xml['RASP']['FEATURE_KARMA'][0]);
				$this->allow_public_karma	= $aseco->string2bool($xml['RASP']['ALLOW_PUBLIC_KARMA'][0]);
				$this->karma_show_start		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_START'][0]);
				$this->karma_show_details	= $aseco->string2bool($xml['RASP']['KARMA_SHOW_DETAILS'][0]);
				$this->karma_show_votes		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_VOTES'][0]);
				$this->karma_require_finish	= $xml['RASP']['KARMA_REQUIRE_FINISH'][0];
				$this->remind_karma		= $xml['RASP']['REMIND_KARMA'][0];
				$this->feature_jukebox		= $aseco->string2bool($xml['RASP']['FEATURE_JUKEBOX'][0]);
				$this->feature_mxadd		= $aseco->string2bool($xml['RASP']['FEATURE_MXADD'][0]);
				$this->jukebox_skipleft		= $aseco->string2bool($xml['RASP']['JUKEBOX_SKIPLEFT'][0]);
				$this->jukebox_adminnoskip	= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINNOSKIP'][0]);
				$this->jukebox_permadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_PERMADD'][0]);
				$this->jukebox_adminadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINADD'][0]);
				$this->jukebox_in_window	= $aseco->string2bool($xml['RASP']['JUKEBOX_IN_WINDOW'][0]);
				$this->reset_cache_start	= $aseco->string2bool($xml['RASP']['RESET_CACHE_START'][0]);
				$this->autosave_matchsettings	= $xml['RASP']['AUTOSAVE_MATCHSETTINGS'][0];
				$this->feature_votes		= $aseco->string2bool($xml['RASP']['FEATURE_VOTES'][0]);
				$this->prune_records_times	= $aseco->string2bool($xml['RASP']['PRUNE_RECORDS_TIMES'][0]);


				/***************************** PERFORMANCE VARIABLES ***************************/
				if (isset($xml['RASP']['MIN_RANK'][0])) {
					$this->minrank = $xml['RASP']['MIN_RANK'][0];
				}
				else {
					$this->minrank = 3;
				}

				if (isset($xml['RASP']['MAX_AVG'][0])) {
					$this->maxavg = $xml['RASP']['MAX_AVG'][0];
				}
				else {
					$this->maxavg = 10;
				}


				/***************************** JUKEBOX VARIABLES *******************************/
				$this->buffersize		= $xml['RASP']['BUFFER_SIZE'][0];
				$this->mxvoteratio		= $xml['RASP']['MX_VOTERATIO'][0];
				$this->mxdir			= $xml['RASP']['MX_DIR'][0];
				$this->mxtmpdir			= $xml['RASP']['MX_TMPDIR'][0];
				$this->maphistory_file		= $xml['RASP']['MAPHISTORY_FILE'][0];

				$this->jukebox			= array();
				$this->jb_buffer		= array();
				$this->mxadd			= array();
				$this->mxplaying		= false;
				$this->mxplayed			= false;


				/******************************* IRC VARIABLES *********************************/
				$this->irc = new stdClass();
				$this->irc->server		= $xml['RASP']['IRC_SERVER'][0];
				$this->irc->nick		= $xml['RASP']['IRC_BOTNICK'][0];
				$this->irc->port		= $xml['RASP']['IRC_PORT'][0];
				$this->irc->channel		= $xml['RASP']['IRC_CHANNEL'][0];
				$this->irc->name		= $xml['RASP']['IRC_BOTNAME'][0];
				$this->irc->show_connect	= $aseco->string2bool($xml['RASP']['IRC_SHOW_CONNECT'][0]);

				$this->irc->linesbuffer		= array();
				$this->irc->ircmsgs		= array();
				$this->irc->con			= array();



				/******************************* VOTES VARIABLES *********************************/
			  	$this->auto_vote_starter	= $aseco->string2bool($xml['RASP']['AUTO_VOTE_STARTER'][0]);
			  	$this->allow_spec_startvote	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_STARTVOTE'][0]);
			  	$this->allow_spec_voting	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_VOTING'][0]);

				// maximum number of rounds before a vote expires
			  	$this->r_expire_limit = array(
			  		0 => $xml['RASP']['R_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['R_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['R_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['R_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['R_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['R_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['R_EXPIRE_LIMIT_IGNORE'][0],
			  	);
		    		$this->r_show_reminder = $aseco->string2bool($xml['RASP']['R_SHOW_REMINDER'][0]);

			    	// maximum number of seconds before a vote expires
			  	$this->ta_expire_limit = array(
			  		0 => $xml['RASP']['TA_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['TA_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['TA_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['TA_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['TA_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['TA_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['TA_EXPIRE_LIMIT_IGNORE'][0],
			  	);
				$this->ta_show_reminder = $aseco->string2bool($xml['RASP']['TA_SHOW_REMINDER'][0]);

				// interval length at which to (approx.) repeat reminder [s]
				$this->ta_show_interval = $xml['RASP']['TA_SHOW_INTERVAL'][0];

		  		// disable CallVotes
		  		$aseco->client->query('SetCallVoteRatio', 1.0);

		  		// really disable all CallVotes
		  		$ratios = array(array('Command' => '*', 'Ratio' => -1.0));
		  		$aseco->client->query('SetCallVoteRatios', $ratios);

				$this->global_explain = $xml['RASP']['GLOBAL_EXPLAIN'][0];

		  		// define the vote ratios for all types
		  		$this->vote_ratios = array(
		  			0 => $xml['RASP']['VOTE_RATIO_ENDROUND'][0],
		  			1 => $xml['RASP']['VOTE_RATIO_LADDER'][0],
		  			2 => $xml['RASP']['VOTE_RATIO_REPLAY'][0],
		  			3 => $xml['RASP']['VOTE_RATIO_SKIP'][0],
		  			4 => $xml['RASP']['VOTE_RATIO_KICK'][0],
		  			5 => $xml['RASP']['VOTE_RATIO_ADD'][0],
		  			6 => $xml['RASP']['VOTE_RATIO_IGNORE'][0],
		  		);

		  		$this->vote_in_window		= $aseco->string2bool($xml['RASP']['VOTE_IN_WINDOW'][0]);
		  		$this->disable_upon_admin	= $aseco->string2bool($xml['RASP']['DISABLE_UPON_ADMIN'][0]);
		  		$this->disable_while_sb		= $aseco->string2bool($xml['RASP']['DISABLE_WHILE_SB'][0]);

		   		// allow kicks & allow user to kick-vote any admin?
		  		$this->allow_kickvotes		= $aseco->string2bool($xml['RASP']['ALLOW_KICKVOTES'][0]);
		  		$this->allow_admin_kick		= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_KICK'][0]);

		  		// allow ignores & allow user to ignore-vote any admin?
		  		$this->allow_ignorevotes	= $aseco->string2bool($xml['RASP']['ALLOW_IGNOREVOTES'][0]);
		  		$this->allow_admin_ignore	= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_IGNORE'][0]);

		  		$this->max_laddervotes		= $xml['RASP']['MAX_LADDERVOTES'][0];
		  		$this->max_replayvotes		= $xml['RASP']['MAX_REPLAYVOTES'][0];
		  		$this->max_skipvotes		= $xml['RASP']['MAX_SKIPVOTES'][0];

		  		$this->replays_limit		= $xml['RASP']['REPLAYS_LIMIT'][0];

		  		$this->ladder_fast_restart	= $aseco->string2bool($xml['RASP']['LADDER_FAST_RESTART'][0]);

		  		$this->r_points_limits		= $aseco->string2bool($xml['RASP']['R_POINTS_LIMITS'][0]);
		  		$this->r_ladder_max		= $xml['RASP']['R_LADDER_MAX'][0];
		  		$this->r_replay_min		= $xml['RASP']['R_REPLAY_MIN'][0];
		  		$this->r_skip_max		= $xml['RASP']['R_SKIP_MAX'][0];

		  		$this->ta_time_limits		= $aseco->string2bool($xml['RASP']['TA_TIME_LIMITS'][0]);
		  		$this->ta_ladder_max		= $xml['RASP']['TA_LADDER_MAX'][0];
		  		$this->ta_replay_min		= $xml['RASP']['TA_REPLAY_MIN'][0];
		  		$this->ta_skip_max		= $xml['RASP']['TA_SKIP_MAX'][0];
			}
			else {
				trigger_error('Could not read/parse rasp config file ['. $config_file .']!', E_USER_WARNING);
			}
		}
		else {
			trigger_error('Could not find rasp config file ['. $config_file .']!', E_USER_WARNING);
		}
	}
}

?>
