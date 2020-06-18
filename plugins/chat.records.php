<?php
/*
 * Plugin: Chat Records
 * ~~~~~~~~~~~~~~~~~~~~
 * » Displays all records of the current map.
 * » Based upon chat.records.php and chat.records2.php from XAseco2/1.03 written
 *   by Xymph and others
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
	$_PLUGIN = new PluginChatRecords();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatRecords extends Plugin {


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-09-15');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription('Displays all records of the current map.');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);

		$this->registerChatCommand('recs',		'chat_recs',		new Message('chat.records', 'slash_recs'),	Player::PLAYERS);
		$this->registerChatCommand('newrecs',		'chat_newrecs',		new Message('chat.records', 'slash_newrecs'),	Player::PLAYERS);
		$this->registerChatCommand('liverecs',		'chat_liverecs',	new Message('chat.records', 'slash_liverecs'),	Player::PLAYERS);
		$this->registerChatCommand('best',		'chat_best',		new Message('chat.records', 'slash_best'),	Player::PLAYERS);
		$this->registerChatCommand('worst',		'chat_worst',		new Message('chat.records', 'slash_worst'),	Player::PLAYERS);
		$this->registerChatCommand('summary',		'chat_summary',		new Message('chat.records', 'slash_summary'),	Player::PLAYERS);
		$this->registerChatCommand('topsums',		'chat_topsums',		new Message('chat.records', 'slash_topsums'),	Player::PLAYERS);
		$this->registerChatCommand('toprecs',		'chat_toprecs',		new Message('chat.records', 'slash_toprecs'),	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_recs ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// split params into array
		$arglist = explode(' ', strtolower(preg_replace('/ +/', ' ', $chat_parameter)));

		// process optional relations commands
		if ($arglist[0] === 'help') {
			$header = '{#black}/recs <option>$g shows local records and relations:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}pb',
			                'Shows your personal best on current map');
			$help[] = array('...', '{#black}new',
			                'Shows newly driven records');
			$help[] = array('...', '{#black}live',
			                'Shows records of online players');
			$help[] = array('...', '{#black}first',
			                'Shows first ranked record on current map');
			$help[] = array('...', '{#black}last',
			                'Shows last ranked record on current map');
			$help[] = array('...', '{#black}next',
			                'Shows next better ranked record to beat');
			$help[] = array('...', '{#black}diff',
			                'Shows your difference to first ranked record');
			$help[] = array('...', '{#black}range',
			                'Shows difference first to last ranked record');
			$help[] = array();
			$help[] = array('Without an option, the normal records list is displayed.');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.1, 0.05, 0.3, 0.75), 'OK');
			return;
		}
		else if ($arglist[0] === 'pb') {
			$aseco->releaseChatCommand('/pb', $login);
			return;
		}
		else if ($arglist[0] === 'new') {
			$aseco->releaseChatCommand('/newrecs', $login);
			return;
		}
		else if ($arglist[0] === 'live') {
			$aseco->releaseChatCommand('/liverecs', $login);
			return;
		}
		else if ($arglist[0] === 'first') {
			$aseco->releaseChatCommand('/firstrec', $login);
			return;
		}
		else if ($arglist[0] === 'last') {
			$aseco->releaseChatCommand('/lastrec', $login);
			return;
		}
		else if ($arglist[0] === 'next') {
			$aseco->releaseChatCommand('/nextrec', $login);
			return;
		}
		else if ($arglist[0] === 'diff') {
			$aseco->releaseChatCommand('/diffrec', $login);
			return;
		}
		else if ($arglist[0] === 'range') {
			$aseco->releaseChatCommand('/recrange', $login);
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($login);
			return;
		}

		if (!$total = $aseco->plugins['PluginLocalRecords']->records->count()) {
			(new Message('chat.records', 'no_records_found'))->sendChatMessage($login);
			return;
		}

		// display ManiaLink window
		$head = 'Current TOP '. $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() .' Local Records:';
		$msg = array();
		$lines = 0;
		$player->msgs = array();
		// reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colornicks'] ? 0.2 : 0);
		if ($aseco->settings['show_rec_logins']) {
			$player->msgs[0] = array(1, $head, array(1.2+$extra, 0.1, 0.45+$extra, 0.4, 0.25), array('BgRaceScore2', 'Podium'));
		}
		else {
			$player->msgs[0] = array(1, $head, array(0.8+$extra, 0.1, 0.45+$extra, 0.25), array('BgRaceScore2', 'Podium'));
		}

		// create list of records
		for ($i = 0; $i < $total; $i++) {
			$cur_record = $aseco->plugins['PluginLocalRecords']->records->getRecord($i);
			$nick = $cur_record->player->nickname;
			if (!$aseco->settings['lists_colornicks']) {
				$nick = $aseco->stripStyles($nick);
			}
			if ($aseco->settings['show_rec_logins']) {
				$msg[] = array(str_pad($i+1, 2, '0', STR_PAD_LEFT) . '.',
					'{#black}' . $nick,
					'{#login}' . $cur_record->player->login,
					($cur_record->new ? '{#black}' : '') .
					$aseco->formatTime($cur_record->score)
				);
			}
			else {
				$msg[] = array(str_pad($i+1, 2, '0', STR_PAD_LEFT) . '.',
					'{#black}' . $nick,
					($cur_record->new ? '{#black}' : '') .
					$aseco->formatTime($cur_record->score)
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

	public function chat_newrecs ($aseco, $login, $chat_command, $chat_parameter) {

		// Check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($login);
			return;
		}

		// Show only newly driven records
		$aseco->plugins['PluginLocalRecords']->show_maprecs($aseco, $login, 0, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_liverecs ($aseco, $login, $chat_command, $chat_parameter) {

		// Check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($login);
			return;
		}

		// Show online & show_min_recs-2 records
		$aseco->plugins['PluginLocalRecords']->show_maprecs($aseco, $login, 2, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_best ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($login);
			return;
		}

		// display player records, best first
		if ($player = $aseco->server->players->getPlayerByLogin($login)) {
			$command = array();
			$command['author'] = $player;
			$command['params'] = $chat_parameter;
			$this->displayRecords($aseco, $command, true);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_worst ($aseco, $login, $chat_command, $chat_parameter) {

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($login);
			return;
		}

		// display player records, worst first
		if ($player = $aseco->server->players->getPlayerByLogin($login)) {
			$command = array();
			$command['author'] = $player;
			$command['params'] = $chat_parameter;
			$this->displayRecords($aseco, $command, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_summary ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$target = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($target->login);
			return;
		}

		// check for optional login parameter if any admin
		if ($chat_parameter !== '' && $aseco->allowAbility($target, 'chat_summary')) {
			if (!$target = $aseco->server->players->getPlayerParam($target, $chat_parameter, true)) {
				return;
			}
		}

		// check for records
		if ($list = $target->getRecords()) {
			// sort for best records
			asort($list);

			// collect summary of first 3 records and count total
			$show = 3;
			$message = '';
			$total = 0;
			$cntrec = 0;
			$currec = 0;
			foreach ($list as $uid => $rec) {
				// stop upon unranked record
				if ($rec > $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) {
					break;
				}

				// check if rec is for existing map
				if (array_key_exists($uid, $aseco->server->maps->map_list)) {
					// count total ranked records
					$total++;

					// check for first 3 records
					if ($show > 0) {
						// check for same record
						if ($rec === $currec) {
							$cntrec++;
						}
						else {
							// collect next record sum
							if ($currec > 0) {
								$message = new Message('chat.records', 'sum_entry');
								$message->addPlaceholders(
									$cntrec,
									($cntrec > 1 ? 's' : ''),
									$currec
								);
								$message = $message->finish($target->login);

								$show--;
							}
							// count first occurance of next record
							$cntrec = 1;
							$currec = $rec;
						}
					}
				}
			}
			// if less than 3 records, add the last one found
			if ($show > 0 && $currec > 0) {
				$message = new Message('chat.records', 'sum_entry');
				$message->addPlaceholders(
					$cntrec,
					($cntrec > 1 ? 's' : ''),
					$currec
				);
				$message = $message->finish($target->login);

				$show--;
			}

			if ($message) {
				// define text version of number of top-3 records
				// TODO translate
				/*
				switch (3-$show) {
					case 1:
						$show = 'one';
						break;
					case 2:
						$show = 'two';
						break;
					case 3:
						$show = 'three';
						break;
				} */
				$show = 3-$show;

				// show chat message
				$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "

				$msg = new Message('chat.records', 'summary');
				$msg->addPlaceholders($target->nickname, $total, $show);
				$message = $msg->finish($target->login) . $message;

				$aseco->sendChatMessage($message, $target->login);
			}
			else {
				(new Message('chat.records', 'no_records_found'))->sendChatMessage($target->login);
			}
		}
		else {
			(new Message('chat.records', 'no_records_found'))->sendChatMessage($target->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_topsums ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($player->login);
			return;
		}

		// collect top-3 records
		$recs = array();
		foreach ($aseco->server->maps->map_list as $map) {
			// get top-3 ranked records on this map
			$query = "
			SELECT
				`Login`
			FROM `%prefix%players`, `%prefix%records`
			WHERE `%prefix%players`.`PlayerId` = `%prefix%records`.`Playerid`
			AND `MapId` = ". $map->id ."
			AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
			ORDER BY `Score` ASC, `Date` ASC
			LIMIT 3;
			";
			$result = $aseco->db->query($query);
			if ($result) {
				// tally top-3 record totals by login
				if ($row = $result->fetch_array(MYSQLI_NUM)) {
					if (isset($recs[$row[0]])) {
						$recs[$row[0]][0]++;
					}
					else {
						$recs[$row[0]] = array(1,0,0);
					}
					if ($row = $result->fetch_array(MYSQLI_NUM)) {
						if (isset($recs[$row[0]])) {
							$recs[$row[0]][1]++;
						}
						else {
							$recs[$row[0]] = array(0,1,0);
						}
						if ($row = $result->fetch_array(MYSQLI_NUM)) {
							if (isset($recs[$row[0]])) {
								$recs[$row[0]][2]++;
							}
							else {
								$recs[$row[0]] = array(0,0,1);
							}
						}
					}
				}
				$result->free_result();
			}
		}

		if (empty($recs)) {
			(new Message('chat.records', 'no_records_found'))->sendChatMessage($player->login);
			return;
		}

		// sort players by #1, #2 & #3 records
		uasort($recs, array($this, 'top3_compare'));

		$records = array();
		$top = 100;
		$i = 1;
		foreach ($recs as $login => $top3) {
			// obtain nickname for this login
			$nick = $aseco->server->players->getPlayerNickname($login);
			if ($nick === false) {
				$nick = $lgn;
			}
			if (!$aseco->settings['lists_colornicks']) {
				$nick = $aseco->stripStyles($nick);
			}

			$records[] = array(
				$i .'.',
				$top3[0],
				$top3[1],
				$top3[2],
				' ',
				$nick,
			);
			if ($i >= $top) {
				break;
			}
			$i++;
		}


		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'BgRaceScore2,LadderRank',
			'textcolors'		=> array('FFFF', '0F0F', '0F0F', '0F0F', 'FFFF', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 2,
			'widths'		=> array(5, 8, 8, 8, 2, 69),
			'halign'		=> array('right', 'right', 'right', 'right', 'left', 'left'),
			'textcolors'		=> array('EEEF', 'EEEF', 'EEEF', 'EEEF', 'FFFF', 'FFFF'),
			'heading'		=> array('#', '#1', '#2', '#3', '', 'Player'),
		);
		$settings_content = array(
			'title'			=> 'TOP 100 of Top-3 Record Holders',
			'data'			=> $records,
			'about'			=> 'CHAT RECORDS/'. $this->getVersion(),
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_toprecs ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			(new Message('chat.records', 'notonrelay'))->sendChatMessage($player->login);
			return;
		}

		// collect record totals
		$recs = array();
		foreach ($aseco->server->maps->map_list as $map) {
			// get ranked records on this map
			$query = "
			SELECT
				`Login`
			FROM `%prefix%players`, `%prefix%records`
			WHERE `%prefix%players`.`PlayerId` = `%prefix%records`.`Playerid`
			AND `MapId` = ". $map->id ."
			AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
			ORDER BY `Score` ASC, `Date` ASC
			LIMIT ". $aseco->plugins['PluginLocalRecords']->records->getMaxRecords() .";
			";
			$result = $aseco->db->query($query);
			if ($result) {
				if ($result->num_rows > 0) {
					// update record totals by login
					while ($row = $result->fetch_array(MYSQLI_NUM)) {
						if (isset($recs[$row[0]])) {
							$recs[$row[0]]++;
						}
						else {
							$recs[$row[0]] = 1;
						}
					}
				}
				$result->free_result();
			}
		}

		if (empty($recs)) {
			(new Message('chat.records', 'no_records_found'))->sendChatMessage($player->login);
			return;
		}

		// sort for most records
		arsort($recs);


		$top = 100;
		$records = array();
		$i = 1;
		foreach ($recs as $login => $rec) {
			// obtain nickname for this login
			$nick = $aseco->server->players->getPlayerNickname($login);
			if ($nick === false) {
				$nick = $lgn;
			}
			if (!$aseco->settings['lists_colornicks']) {
				$nick = $aseco->stripStyles($nick);
			}

			$records[] = array(
				$i .'.',
				$rec,
				$nick,
			);
			if ($i >= $top) {
				break;
			}
			$i++;
		}


		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'BgRaceScore2,LadderRank',
			'textcolors'		=> array('FFFF', 'FFFF', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 4,
			'widths'		=> array(11, 22, 67),
			'halign'		=> array('right', 'right', 'left'),
			'textcolors'		=> array('EEEF', 'EEEF', 'FFFF'),
			'heading'		=> array('#', 'Records', 'Player'),
		);
		$settings_content = array(
			'title'			=> 'TOP 100 Ranked Record Holders',
			'data'			=> $records,
			'about'			=> 'CHAT RECORDS/'. $this->getVersion(),
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function displayRecords ($aseco, $command, $order) {

		$player = $command['author'];
		$target = $player;

		// check for optional login parameter if any admin
		if ($command['params'] !== '' && $aseco->allowAbility($player, 'chat_bestworst')) {
			if (!$target = $aseco->server->players->getPlayerParam($player, $command['params'], true)) {
				return;
			}
		}

		// check for records
		if ($list = $target->getRecords()) {
			// sort for best or worst records
			$order ? asort($list) : arsort($list);

			// create list of records
			$player->maplist = array();
			$envids = array('Canyon' => 11, 'Stadium' => 12, 'Valley' => 13);
			$head = ($order ? 'Best' : 'Worst') . ' Records for ' . str_ireplace('$w', '', $target->nickname) . '$z:';
			$recs = array();
			$recs[] = array('Id', 'Rec', 'Name', 'Author');

			$tid = 1;
			$lines = 0;
			$player->msgs = array();
			// no extra width for $w tags due to nickname width in header
			$player->msgs[0] = array(1, $head, array(1.42, 0.12, 0.1, 0.8, 0.4), array('Icons128x128_1', 'NewTrack', 0.02));

			foreach ($list as $uid => $pos) {
				// does the uid exist in the current server map list?
				if (array_key_exists($uid, $aseco->server->maps->map_list)) {
					$map = $aseco->server->maps->getMapByUid($uid);
					// store map in player object for jukeboxing
					$trkarr = array();
					$trkarr['name'] = $map->name;
					$trkarr['author'] = $map->author;
					$trkarr['environment'] = $map->environment;
					$trkarr['filename'] = $map->filename;
					$trkarr['uid'] = $map->uid;
					$player->maplist[] = $trkarr;

					// format map name
					$mapname = $map->name;
					if (!$aseco->settings['lists_colormaps']) {
						$mapname = $aseco->stripStyles($mapname);
					}

					// grey out if in history
					if ($aseco->server->maps->history->isMapInHistoryByUid($map->uid) === true) {
						$mapname = '{#grey}' . $aseco->stripStyles($mapname);
					}
					else {
						$mapname = '{#black}' . $mapname;
						// add clickable button
						if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
							$mapname = array($mapname, $tid+100);  // action id
						}
					}

					// format author name
					$mapauthor = $map->author;

					// add clickable button
					if ($aseco->settings['clickable_lists'] && $tid <= 1900) {
						$mapauthor = array($mapauthor, -100-$tid);  // action id
					}

					// format env name
					$mapenv = $map->environment;

					// add clickable button
					if ($aseco->settings['clickable_lists']) {
						$mapenv = array($mapenv, $envids[$map->environment]);  // action id
					}

					$recs[] = array(
						str_pad($tid, 3, '0', STR_PAD_LEFT) . '.',
						str_pad($pos, 2, '0', STR_PAD_LEFT) . '.',
						$mapname,
						$mapauthor
					);

					$tid++;
					if (++$lines > 14) {
						$player->msgs[] = $recs;
						$lines = 0;
						$recs = array();
						$recs[] = array('Id', 'Rec', 'Name', 'Author');
					}
				}
			}

			// add if last batch exists
			if (count($recs) > 1) {
				$player->msgs[] = $recs;
			}

			if (count($player->msgs) > 1) {
				// display ManiaLink message
				$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
			}
			else {
				(new Message('chat.records', 'no_records_found'))->sendChatMessage($player->login);
			}
		}
		else {
			(new Message('chat.records', 'no_records_found'))->sendChatMessage($player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// define sorting function for descending top-3's
	private function top3_compare ($a, $b) {

		// compare #1 records
		if ($a[0] < $b[0]) {
			return 1;
		}
		else if ($a[0] > $b[0]) {
			return -1;
		}

		// compare #2 records
		if ($a[1] < $b[1]) {
			return 1;
		}
		else if ($a[1] > $b[1]) {
			return -1;
		}

		// compare #3 records
		if ($a[2] < $b[2]) {
			return 1;
		}
		else if ($a[2] > $b[2]) {
			return -1;
		}

		// all equal
		return 0;
	}
}

?>
