<?php
/*
 * Plugin: Dedimania
 * ~~~~~~~~~~~~~~~~~
 * » Handles interaction with the Dedimania world database and shows new/online
 *   Dedimania world records and their relations on the current track.
 * » Based upon plugin.dedimania.php and chat.dedimania.php from XAseco2/1.03 written
 *   by Xymph, based on FAST
 *   Protocol documentation: http://dedimania.net:8082/Dedimania
 *   Connection status: http://dedimania.net:8082/stats
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

	require_once('includes/dedimania/GbxRemote.inc.php');
	require_once('includes/dedimania/GbxRemote.response.php');
	require_once('includes/dedimania/xmlrpc_db.inc.php');
	require_once('includes/dedimania/webaccess.inc.php');

	// Start the plugin
	$_PLUGIN = new PluginDedimania();


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginDedimania extends Plugin {
	public $db;

	// Overrule these in config/dedimania.xml, do not change them here!
	public $defaults = array(
		'Name'			=> 'Dedimania',
		'ShowWelcome'		=> true,
		'ShowMinRecs'		=> 8,
		'ShowRecsBefore'	=> 1,
		'ShowRecsAfter'		=> 1,
		'ShowRecsRange'		=> true,
		'DisplayRecs'		=> true,
		'RecsInWindow'		=> false,
		'ShowRecLogins'		=> true,
		'LimitRecs'		=> 10,
		'KeepVReplays'		=> false,
	);

	public $last_sent;
	public $timeout			= 1800;				// how many seconds before retrying connection, default 30 mins
	public $refresh			= 240;				// how many seconds before reannouncing server, default 4 mins

	// minimum author & finish times that are still accepted
	public $min_author_time		= 10000;			// 10 secs
	public $min_finish_time		= 8000;				// 8 secs
	public $webaccess;

	/* max debug level = 5:
	 * 1 +internal warnings
	 * 2 +main data structure, initial connection response, progress messages, dedicated callback data
	 * 3 +config defaults, XML config, full record lists, data in XML responses
	 * 4 +full XML responses
	 * 5 +record checkpoints
	 */
	public $debug			= 0;

	private $config_file		= 'config/dedimania.xml';
	private $greplay_dir		= 'GReplays';
	private $vreplay_dir		= 'VReplays';

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-06-15');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Handles interaction with the Dedimania world database and shows new/online Dedimania world records and their relations on the current track.');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginCheckpoints',	Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onEverySecond',		'onEverySecond');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerFinish',		'onPlayerFinish');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');
		$this->registerEvent('onEndMap',		'onEndMap');

		$this->registerChatCommand('helpdedi',		'chat_helpdedi',	'Displays info about the Dedimania records system',	Player::PLAYERS);
		$this->registerChatCommand('dedihelp',		'chat_helpdedi',	'Displays info about the Dedimania records system',	Player::PLAYERS);
		$this->registerChatCommand('dedirecs',		'chat_dedirecs',	'Displays all Dedimania records on current track',	Player::PLAYERS);
		$this->registerChatCommand('dedinew',		'chat_dedinew',		'Shows newly driven Dedimania records',			Player::PLAYERS);
		$this->registerChatCommand('dedilive',		'chat_dedilive',	'Shows Dedimania records of online players',		Player::PLAYERS);
		$this->registerChatCommand('dedipb',		'chat_dedipb',		'Shows your Dedimania personal best on current track',	Player::PLAYERS);
		$this->registerChatCommand('dedifirst',		'chat_dedifirst',	'Shows first Dedimania record on current track',	Player::PLAYERS);
		$this->registerChatCommand('dedilast',		'chat_dedilast',	'Shows last Dedimania record on current track',		Player::PLAYERS);
		$this->registerChatCommand('dedinext',		'chat_dedinext',	'Shows next better Dedimania record to beat',		Player::PLAYERS);
		$this->registerChatCommand('dedidiff',		'chat_dedidiff',	'Shows your difference to first Dedimania record',	Player::PLAYERS);
		$this->registerChatCommand('dedirange',		'chat_dedirange',	'Shows difference first to last Dedimania record',	Player::PLAYERS);
		$this->registerChatCommand('dedistats',		'chat_dedistats',	'Displays Dedimania track statistics',			Player::PLAYERS);
//		$this->registerChatCommand('dedicptms',		'chat_dedicptms',	'Displays all Dedimania records\' checkpoint times',	Player::PLAYERS);
//		$this->registerChatCommand('dedisectms',	'chat_dedisectms',	'Displays all Dedimania records\' sector times',	Player::PLAYERS);

        try{
            $this->client = new \GuzzleHttp\Client();
            $this->discord = new SimpleXMLElement(file_get_contents('config/discord.xml'));
        }catch(\Exception $e){
            $this->discord = null;
            trigger_error('[Discord] Could not read/parse config file [config/discord.xml]!', E_USER_ERROR);
        }
	}

    private function sendDiscordMessage($message){
        if(!$this->discord){
            return;
        }

        $message = preg_replace('/{#\w+}/', '', $message);

        $options = [
            'form_params' => [
                'content' => $message
            ]
        ];

        $channel_id = $this->discord->channel_id;
        $token = $this->discord->token;

        $this->client->request('POST', "https://discordapp.com/api/webhooks/$channel_id/$token", $options);
    }

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_helpdedi ($aseco, $login, $chat_command, $chat_parameter) {

		// compile & display help message
		$header = 'Dedimania information:';
		$data = array();
		$data[] = array('{#dedimsg}Dedimania$g is an online World Records database for {#black}all');
		$data[] = array('TrackMania games.  See its official site at:');
		$data[] = array('{#black}$l[http://www.dedimania.com/SITE/]http://www.dedimania.com/SITE/$l$g and the records database:');
		$data[] = array('{#black}$l[http://www.dedimania.com/tm2stats/?do=stat]http://www.dedimania.com/tm2stats/?do=stat$l$g .');
		$data[] = array();
		$data[] = array('Dedimania records are stored per game (TM2, TMF, etc)');
		$data[] = array('and mode (TimeAttack, Rounds, etc) and shared between');
		$data[] = array('all servers that operate with Dedimania support.');
		$data[] = array();
		$data[] = array('The available Dedimania commands are similar to local');
		$data[] = array('record commands:');
		$data[] = array('{#black}/dedirecs$g, {#black}/dedinew$g, {#black}/dedilive$g, {#black}/dedipb$g, {#black}/dedicps$g, {#black}/dedistats$g,');
		$data[] = array('{#black}/dedifirst$g, {#black}/dedilast$g, {#black}/dedinext$g, {#black}/dedidiff$g, {#black}/dedirange$g');
		$data[] = array();
		$data[] = array('See the {#black}/helpall$g command for detailed descriptions.');

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $data, array(0.95), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedirecs ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];

		// split params into array
		$arglist = explode(' ', strtolower(preg_replace('/ +/', ' ', $chat_parameter)));

		// process optional relations commands
		if ($arglist[0] == 'help') {
			$header = '{#black}/dedirecs <option>$g shows Dedimania records and relations:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}pb',
			                'Shows your personal best on current track');
			$help[] = array('...', '{#black}new',
			                'Shows newly driven records');
			$help[] = array('...', '{#black}live',
			                'Shows records of online players');
			$help[] = array('...', '{#black}first',
			                'Shows first ranked record on current track');
			$help[] = array('...', '{#black}last',
			                'Shows last ranked record on current track');
			$help[] = array('...', '{#black}next',
			                'Shows next better ranked record to beat');
			$help[] = array('...', '{#black}diff',
			                'Shows your difference to first ranked record');
			$help[] = array('...', '{#black}range',
			                'Shows difference first to last ranked record');
			$help[] = array();
			$help[] = array('Without an option, the normal records list is displayed.');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(1.2, 0.05, 0.3, 0.85), 'OK');
			return;
		}
		else if ($arglist[0] == 'pb') {
			$this->chat_dedipb($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'new') {
			$this->chat_dedinew($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'live') {
			$this->chat_dedilive($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'first') {
			$this->chat_dedifirst($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'last') {
			$this->chat_dedilast($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'next') {
			$this->chat_dedinext($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'diff') {
			$this->chat_dedidiff($aseco, $login, $chat_command, $chat_parameter);
			return;
		}
		else if ($arglist[0] == 'range') {
			$this->chat_dedirange($aseco, $login, $chat_command, $chat_parameter);
			return;
		}

		if (!$total = count($dedi_recs)) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$maxrank = max($this->db['ServerMaxRank'], $player->dedirank);

		// display ManiaLink window
		$head = 'Current TOP '. $maxrank .' Dedimania Records:';
		$msg = array();
		$lines = 0;
		$player->msgs = array();
		// reserve extra width for $w tags
		$extra = ($aseco->settings['lists_colornicks'] ? 0.2 : 0);
		if ($this->db['ShowRecLogins']) {
			$player->msgs[0] = array(1, $head, array(1.2+$extra, 0.1, 0.45+$extra, 0.4, 0.25), array('BgRaceScore2', 'Podium'));
		}
		else {
			$player->msgs[0] = array(1, $head, array(0.8+$extra, 0.1, 0.45+$extra, 0.25), array('BgRaceScore2', 'Podium'));
		}

		// create list of records
		for ($i = 0; $i < $total; $i++) {
			$cur_record = $dedi_recs[$i];
			$nick = $cur_record['NickName'];
			if (!$aseco->settings['lists_colornicks']) {
				$nick = $aseco->stripStyles($nick);
			}
			if ($this->db['ShowRecLogins']) {
				$msg[] = array(str_pad($i+1, 2, '0', STR_PAD_LEFT) .'.',
					'{#black}'. $nick,
					'{#login}'. $cur_record['Login'],
					((isset($cur_record['NewBest']) && $cur_record['NewBest']) ? '{#black}': '') .
					$aseco->formatTime($cur_record['Best'])
				);
			}
			else {
				$msg[] = array(str_pad($i+1, 2, '0', STR_PAD_LEFT) .'.',
					'{#black}'. $nick,
					((isset($cur_record['NewBest']) && $cur_record['NewBest']) ? '{#black}': '') .
					$aseco->formatTime($cur_record['Best'])
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

	public function chat_dedinew ($aseco, $login, $chat_command, $chat_parameter) {

		// show only newly driven records
		$this->showDedimaniaRecords($aseco, $aseco->server->maps->current->name, $aseco->server->maps->current->uid, $this->db['Map']['Records'], $login, 0, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedilive ($aseco, $login, $chat_command, $chat_parameter) {

		// show online & ShowMinRecs-2 records
		$this->showDedimaniaRecords($aseco, $aseco->server->maps->current->name, $aseco->server->maps->current->uid, $this->db['Map']['Records'], $login, 2, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedipb ($aseco, $login, $chat_command, $chat_parameter) {

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];

		$found = false;
		// find Dedimania record
		for ($i = 0; $i < count($dedi_recs); $i++) {
			$rec = $dedi_recs[$i];
			if ($rec['Login'] == $login) {
				$score = $rec['Best'];
				$rank = $i;
				$found = true;
				break;
			}
		}

		if ($found) {
			$message = $aseco->formatText($this->db['Messages']['PB'][0],
				$aseco->formatTime($score),
				$rank + 1
			);
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$message = $this->db['Messages']['PB_NONE'][0];
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedifirst ($aseco, $login, $chat_command, $chat_parameter) {

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];
		if (!empty($dedi_recs)) {
			// get the first Dedimania record
			$record = $dedi_recs[0];

			// show chat message
			$message = $aseco->formatText($this->db['Messages']['FIRST_RECORD'][0]);
			$message .= $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
				1,
				$aseco->stripStyles($record['NickName']),
				$aseco->formatTime($record['Best'])
			);

			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedilast ($aseco, $login, $chat_command, $chat_parameter) {

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];
		if ($total = count($dedi_recs)) {
			// get the last Dedimania record
			$record = $dedi_recs[$total-1];

			// show chat message
			$message = $aseco->formatText($this->db['Messages']['LAST_RECORD'][0]);
			$message .= $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
				$total,
				$aseco->stripStyles($record['NickName']),
				$aseco->formatTime($record['Best'])
			);

			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "
			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedinext ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];
		if ($total = count($dedi_recs)) {
			$found = false;
			// find Dedimania record
			for ($i = 0; $i < $total; $i++) {
				$rec = $dedi_recs[$i];
				if ($rec['Login'] == $player->login) {
					$rank = $i;
					$found = true;
					break;
				}
			}

			if ($found) {
				// get current and next better Dedimania records
				$nextrank = ($rank > 0 ? $rank-1 : 0);
				$record = $dedi_recs[$rank];
				$next = $dedi_recs[$nextrank];

				// compute difference to next record
				$diff = $record['Best'] - $next['Best'];
				$sec = floor($diff/1000);
				$ths = $diff - ($sec * 1000);

				// show chat message
				$message1 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
					$rank + 1,
					$aseco->stripStyles($record['NickName']),
					$aseco->formatTime($record['Best'])
				);
				$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "
				$message2 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
					$nextrank + 1,
					$aseco->stripStyles($next['NickName']),
					$aseco->formatTime($next['Best'])
				);
				$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "
				$message = $aseco->formatText($this->db['Messages']['DIFF_RECORD'][0],
					$message1,
					$message2,
					sprintf("%d.%03d", $sec, $ths)
				);

				$aseco->sendChatMessage($message, $player->login);
			}
			else {
				// look for unranked time instead
				$found = false;
				$query = "
				SELECT
					`Score`
				FROM `%prefix%times`
				WHERE `PlayerId` = ". $player->id ."
				AND `MapId` = ". $aseco->server->maps->current->id ."
				AND `GamemodeId` = ". $aseco->server->gameinfo->mode ."
				ORDER BY `Score` ASC
				LIMIT 1;
				";

				$result = $aseco->db->query($query);
				if ($result) {
					if ($result->num_rows > 0) {
						$unranked = $result->fetch_object();
						$found = true;
					}
					$result->free_result();
				}

				if ($found) {
					// get the last Dedimania record
					$last = $dedi_recs[$total-1];

					// compute difference to next record
					$sign = ($unranked->Score < $last['Best'] ? '-' : '');
					$diff = abs($unranked->Score - $last['Best']);
					$sec = floor($diff/1000);
					$ths = $diff - ($sec * 1000);

					// show chat message
					$message1 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
						'PB',
						$aseco->stripStyles($player->nickname),
						$aseco->formatTime($unranked->Score)
					);
					$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "
					$message2 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
						$total,
						$aseco->stripStyles($last['NickName']),
						$aseco->formatTime($last['Best'])
					);
					$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "
					$message = $aseco->formatText($this->db['Messages']['DIFF_RECORD'][0],
						$message1,
						$message2,
						sprintf("%s%d.%03d", $sign, $sec, $ths)
					);

					$aseco->sendChatMessage($message, $player->login);
				}
				else {
					$message = '{#server}» {#error}You don\'t have Dedimania a record on this track yet... use {#highlite}$i/dedilast';
					$aseco->sendChatMessage($message, $player->login);
				}
			}
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedidiff ($aseco, $login, $chat_command, $chat_parameter) {

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];
		if ($total = count($dedi_recs)) {
			$found = false;
			// find Dedimania record
			for ($i = 0; $i < $total; $i++) {
				$rec = $dedi_recs[$i];
				if ($rec['Login'] == $login) {
					$rank = $i;
					$found = true;
					break;
				}
			}

			if ($found) {
				// get current and first Dedimania records
				$record = $dedi_recs[$rank];
				$first = $dedi_recs[0];

				// compute difference to first record
				$diff = $record['Best'] - $first['Best'];
				$sec = floor($diff/1000);
				$ths = $diff - ($sec * 1000);

				// show chat message
				$message1 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
					$rank + 1,
					$aseco->stripStyles($record['NickName']),
					$aseco->formatTime($record['Best'])
				);
				$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "
				$message2 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
					1,
					$aseco->stripStyles($first['NickName']),
					$aseco->formatTime($first['Best'])
				);
				$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "
				$message = $aseco->formatText($this->db['Messages']['DIFF_RECORD'][0],
					$message1,
					$message2,
					sprintf("%d.%03d", $sec, $ths)
				);

				$aseco->sendChatMessage($message, $login);
			}
			else {
				$message = '{#server}» {#error}You don\'t have a Dedimania record on this track yet... use {#highlite}$i/dedilast';
				$aseco->sendChatMessage($message, $login);
			}
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedirange ($aseco, $login, $chat_command, $chat_parameter) {

		if (!isset($this->db['Map']['Records'])) {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
			return;
		}
		$dedi_recs = $this->db['Map']['Records'];
		if ($total = count($dedi_recs)) {
			// get the first & last Dedimania records
			$first = $dedi_recs[0];
			$last = $dedi_recs[$total-1];

			// compute difference between records
			$diff = $last['Best'] - $first['Best'];
			$sec = floor($diff/1000);
			$ths = $diff - ($sec * 1000);

			// show chat message
			$message1 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
				1,
				$aseco->stripStyles($first['NickName']),
				$aseco->formatTime($first['Best'])
			);
			$message1 = substr($message1, 0, strlen($message1)-2);  // strip trailing ", "
			$message2 = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW'][0],
				$total,
				$aseco->stripStyles($last['NickName']),
				$aseco->formatTime($last['Best'])
			);
			$message2 = substr($message2, 0, strlen($message2)-2);  // strip trailing ", "
			$message = $aseco->formatText($this->db['Messages']['DIFF_RECORD'][0],
				$message1,
				$message2,
				sprintf("%d.%03d", $sec, $ths)
			);

			$aseco->sendChatMessage($message, $login);
		}
		else {
			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dedistats ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// compile & display stats message
		$header = 'Dedimania Stats: {#black}'. $aseco->stripStyles($aseco->server->maps->current->name);
		$stats = array();
		$stats[] = array('Server MaxRank', '{#black}'. $this->db['ServerMaxRank']);
		$stats[] = array('Your MaxRank', '{#black}'. $player->dedirank);
		$stats[] = array();
		$stats[] = array('UID', '{#black}'. $this->db['Map']['UId']);
		$stats[] = array('Total Races', '{#black}'. $this->db['Map']['TotalRaces']);
		$stats[] = array('Total Players', '{#black}'. $this->db['Map']['TotalPlayers']);
		$stats[] = array('Avg. Players', '{#black}'. ($this->db['Map']['TotalRaces'] > 0 ? round($this->db['Map']['TotalPlayers'] / $this->db['Map']['TotalRaces'], 2) : 0));
		$stats[] = array();
		$stats[] = array('               {#black}$l[http://dedimania.com/tm2stats/?do=stat&RecOrder3=RANK-ASC&UId='. $this->db['Map']['UId'] .'&Show=RECORDS]View all Dedimania records for this track$l');

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons64x64_1', 'Maximize', -0.01), $stats, array(1.0, 0.3, 0.7), 'OK');
	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function chat_dedicptms ($aseco, $login, $chat_command, $chat_parameter) {
//		$this->chat_dedisectms($aseco, $login, $chat_command, $chat_parameter, false);
//	}
//
//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function chat_dedisectms ($aseco, $login, $chat_command, $chat_parameter, $diff = true) {
//
//		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
//			return;
//		}
//
//		if (!isset($this->db['Map']['Records'])) {
//			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $login);
//			return;
//		}
//		$dedi_recs = $this->db['Map']['Records'];
//
//		if (!$total = count($dedi_recs)) {
//			$aseco->sendChatMessage('{#server}» {#error}No Dedimania records found!', $player->login);
//			return;
//		}
//		$maxrank = max($this->db['ServerMaxRank'], $player->dedirank);
//		$cpscnt = count(explode(',', $dedi_recs[0]['Checks']));
//
//		// display ManiaLink window
//		$head = 'Current TOP '. $maxrank .' Dedimania '. ($diff ? 'Sector' : 'CP') .' Times ('. $cpscnt .'):';
//		$cpsmax = 12;
//
//		// compute widths
//		$width = 0.1 + 0.18 + min($cpscnt, $cpsmax) * 0.1 + ($cpscnt > $cpsmax ? 0.06 : 0.0);
//		if ($width < 1.0) {
//			$width = 1.0;
//		}
//		$widths = array($width, 0.1, 0.18);
//		for ($i = 0; $i < min($cpscnt, $cpsmax); $i++) {
//			$widths[] = 0.1; // cp
//		}
//		if ($cpscnt > $cpsmax) {
//			$widths[] = 0.06;
//		}
//
//		$msg = array();
//		$lines = 0;
//		$player->msgs = array();
//		$player->msgs[0] = array(1, $head, $widths, array('BgRaceScore2', 'Podium'));
//
//		// create list of records
//		for ($i = 0; $i < $total; $i++) {
//			$cur_record = $dedi_recs[$i];
//			$cpsrec = explode(',', $cur_record['Checks']);
//			$line = array();
//			$line[] = str_pad($i+1, 2, '0', STR_PAD_LEFT) .'.';
//			$line[] = ((isset($cur_record['NewBest']) && $cur_record['NewBest']) ? '{#black}' : '') . $aseco->formatTime($cur_record['Best']);
//
//			// append up to $cpsmax sector/CP times
//			if (!empty($cpsrec)) {
//				$j = 1;
//				$pr = 0;
//				foreach ($cpsrec as $cp) {
//					$line[] = '$n'. $aseco->formatTime($cp - $pr);
//					if ($diff) {
//						$pr = $cp;
//					}
//					if (++$j > $cpsmax) {
//						if ($cpscnt > $cpsmax) $line[] = '+';
//						break;
//					}
//				}
//			}
//			$msg[] = $line;
//			if (++$lines > 14) {
//				$player->msgs[] = $msg;
//				$lines = 0;
//				$msg = array();
//			}
//		}
//
//		// add if last batch exists
//		if (!empty($msg)) {
//			$player->msgs[] = $msg;
//		}
//
//		// display ManiaLink message
//		$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/*
	 * Universal function to generate list of Dedimania records for current track.
	 * Called by chat_dedinew(), chat_dedilive(), onEndMap() and onLoadingMap()
	 * Show to a player if $login defined, otherwise show to all players.
	 * $mode = 0 (only new), 1 (top-8 & online players at start of track),
	 *         2 (top-6 & online during track), 3 (top-8 & new at end of track)
	 * In modes 1/2/3 the last Dedimania record is also shown
	 * top-8 is configurable via $this->db['ShowMinRecs']; top-6 is ShowMinRecs-2
	 */
	public function showDedimaniaRecords ($aseco, $name, $uid, $dedi_recs, $login, $mode, $window) {

		$records = '$n';  // use narrow font

		if ($this->debug > 2) {
			$aseco->console('[Dedimania] showDedimaniaRecords() - dedi_recs'. CRLF . print_r($dedi_recs, true));
		}

		// check for records
		if (!isset($dedi_recs) || ($total = count($dedi_recs)) == 0) {
			$totalnew = -1;
		}
		else {
			// check whether to show range
			if ($this->db['ShowRecsRange']) {
				// get the first & last Dedimania records
				$first = $dedi_recs[0];
				$last = $dedi_recs[$total-1];

				// compute difference between records
				$diff = $last['Best'] - $first['Best'];
				$sec = floor($diff/1000);
				$ths = $diff - ($sec * 1000);
			}

			// get list of online players
			$players = array();
			foreach ($aseco->server->players->player_list as $pl) {
				$players[] = $pl->login;
			}

			// collect new records and records by online players
			$totalnew = 0;

			// go through each record
			for ($i = 0; $i < $total; $i++) {
				$cur_record = $dedi_recs[$i];

				// if the record is new then display it
				if (isset($cur_record['NewBest']) && $cur_record['NewBest']) {
					$totalnew++;
					$record_msg = $aseco->formatText($this->db['Messages']['RANKING_RECORD_NEW_ON'][0],
						$i + 1,
						$aseco->stripStyles($cur_record['NickName']),
						$aseco->formatTime($cur_record['Best'])
					);
					// always show new record
					$records .= $record_msg;
				}
				else {
					// check if player is online
					if ( in_array($cur_record['Login'], $players) ) {
						$record_msg = $aseco->formatText($this->db['Messages']['RANKING_RECORD_ON'][0],
							$i + 1,
							$aseco->stripStyles($cur_record['NickName']),
							$aseco->formatTime($cur_record['Best'])
						);

						// check if last Dedimania record
						if ($mode != 0 && $i == $total-1) {
							$records .= $record_msg;
						}
						else if ($mode == 1 || $mode == 2) {
							// check if always show (start of/during track)
							$records .= $record_msg;
						}
						else {
							// show record if < ShowMinRecs (end of track)
							if ($mode == 3 && $i < $this->db['ShowMinRecs']) {
								$records .= $record_msg;
							}
						}
					}
					else {
						$record_msg = $aseco->formatText($this->db['Messages']['RANKING_RECORD'][0],
							$i + 1,
							$aseco->stripStyles($cur_record['NickName']),
							$aseco->formatTime($cur_record['Best'])
						);

						// check if last Dedimania record
						if ($mode != 0 && $i == $total-1) {
							$records .= $record_msg;
						}
						else if (($mode == 2 && $i < $this->db['ShowMinRecs']-2) || (($mode == 1 || $mode == 3) && $i < $this->db['ShowMinRecs'])) {
							// show offline record if < ShowMinRecs (start/end of track)
							// show offline record if < ShowMinRecs-2 (during track)
							$records .= $record_msg;
						}
					}
				}
			}
		}

		// define wording of the ranking message
		switch ($mode) {
			case 0:
				$timing = 'during';
				break;
			case 1:
				$timing = 'before';
				break;
			case 2:
				$timing = 'during';
				break;
			case 3:
				$timing = 'after';
				break;
		}

		// hyperlink map name
		$name = $aseco->stripStyles($name);
		$name = '$l[http://www.dedimania.com/tm2stats/?do=stat&Show=RECORDS&RecOrder3=RANK-ASC&UId='. $uid .']'. $name .'$l';

		// define the ranking message
		if ($totalnew > 0) {
			$message = $aseco->formatText($this->db['Messages']['RANKING_NEW'][0],
				$name,
				$timing,
				$totalnew
			);
		}
		else if ($totalnew == 0 && $records != '$n') {
			// check whether to show range
			if ($this->db['ShowRecsRange']) {
				$message = $aseco->formatText($this->db['Messages']['RANKING_RANGE'][0],
					$name,
					$timing,
					sprintf("%d.%03d", $sec, $ths)
				);
			}
			else {
				$message = $aseco->formatText($this->db['Messages']['RANKING'][0],
					$name,
					$timing
				);
			}
		}
		else if ($totalnew == 0 && $records == '$n') {
			$message = $aseco->formatText($this->db['Messages']['RANKING_NONEW'][0],
				$name,
				$timing
			);
		}
		else {
			// $totalnew == -1
			$message = $aseco->formatText($this->db['Messages']['RANKING_NONE'][0],
				$name,
				$timing
			);
		}

		// append the records if any
		if ($records != '$n') {
			$records = substr($records, 0, strlen($records)-2);  // strip trailing ", "
			$message .= LF . $records;
		}

		// show to player or all
		if ($login) {
			// strip 1 leading '>' to indicate a player message instead of system-wide
			$message = str_replace('{#server}» ', '{#server}» ', $message);
			$aseco->sendChatMessage($message, $login);
		}
		else {
			if ($window == 2) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, ($mode == 3)));
			}
			else {
				$aseco->sendChatMessage($message);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Initialize Dedimania subsystem
	public function onSync ($aseco) {

		// create web access
		$this->webaccess = new Webaccess();

		if ($this->debug > 2) {
			print_r($this->defaults);
		}

		// read & parse config file
		$this->db = array();
		if ($config = $aseco->parser->xmlToArray($this->config_file, true, true)) {
			if ($this->debug > 2) {
				print_r($config);
			}

			// read the XML structure into array
			if (isset($config['DEDIMANIA']['DATABASE']) && is_array($config['DEDIMANIA']['DATABASE']) && isset($config['DEDIMANIA']['MASTERSERVER_ACCOUNT']) && is_array($config['DEDIMANIA']['MASTERSERVER_ACCOUNT'])) {
				$dbdata = &$config['DEDIMANIA']['DATABASE'][0];

				if ($this->debug > 2) {
					print_r($dbdata);
				}

				if (isset($dbdata['URL'][0])) {
					if (!is_array($dbdata['URL'][0])) {
						$this->db['Url'] = $dbdata['URL'][0];
					}
					else {
						trigger_error('Multiple URLs specified in your Dedimania config file!', E_USER_ERROR);
					}

					if (isset($dbdata['WELCOME'][0])) {
						$this->db['Welcome'] = $dbdata['WELCOME'][0];
					}
					else {
						$this->db['Welcome'] = '';
					}

					if (isset($dbdata['TIMEOUT'][0])) {
						$this->db['Timeout'] = $dbdata['TIMEOUT'][0];
					}
					else {
						$this->db['Timeout'] = '';
					}

					if (isset($dbdata['NAME'][0])) {
						$this->db['Name'] = $dbdata['NAME'][0];
					}
					else {
						$this->db['Name'] = $this->defaults['Name'];
					}

					if (isset($dbdata['SHOW_WELCOME'][0])) {
						$this->db['ShowWelcome'] = (strtolower($dbdata['SHOW_WELCOME'][0]) == 'true');
					}
					else {
						$this->db['ShowWelcome'] = $this->defaults['ShowWelcome'];
					}

					if (isset($dbdata['SHOW_MIN_RECS'][0])) {
						$this->db['ShowMinRecs'] = intval($dbdata['SHOW_MIN_RECS'][0]);
					}
					else {
						$this->db['ShowMinRecs'] = $this->defaults['ShowMinRecs'];
					}

					if (isset($dbdata['SHOW_RECS_BEFORE'][0])) {
						$this->db['ShowRecsBefore'] = intval($dbdata['SHOW_RECS_BEFORE'][0]);
					}
					else {
						$this->db['ShowRecsBefore'] = $this->defaults['ShowRecsBefore'];
					}

					if (isset($dbdata['SHOW_RECS_AFTER'][0])) {
						$this->db['ShowRecsAfter'] = intval($dbdata['SHOW_RECS_AFTER'][0]);
					}
					else {
						$this->db['ShowRecsAfter'] = $this->defaults['ShowRecsAfter'];
					}

					if (isset($dbdata['SHOW_RECS_RANGE'][0])) {
						$this->db['ShowRecsRange'] = (strtolower($dbdata['SHOW_RECS_RANGE'][0]) == 'true');
					}
					else {
						$this->db['ShowRecsRange'] = $this->defaults['ShowRecsRange'];
					}

					if (isset($dbdata['DISPLAY_RECS'][0])) {
						$this->db['DisplayRecs'] = (strtolower($dbdata['DISPLAY_RECS'][0]) == 'true');
					}
					else {
						$this->db['DisplayRecs'] = $this->defaults['DisplayRecs'];
					}

					if (isset($dbdata['RECS_IN_WINDOW'][0])) {
						$this->db['RecsInWindow'] = (strtolower($dbdata['RECS_IN_WINDOW'][0]) == 'true');
					}
					else {
						$this->db['RecsInWindow'] = $this->defaults['RecsInWindow'];
					}

					if (isset($dbdata['SHOW_REC_LOGINS'][0])) {
						$this->db['ShowRecLogins'] = (strtolower($dbdata['SHOW_REC_LOGINS'][0]) == 'true');
					}
					else {
						$this->db['ShowRecLogins'] = $this->defaults['ShowRecLogins'];
					}

					if (isset($dbdata['LIMIT_RECS'][0])) {
						$this->db['LimitRecs'] = intval($dbdata['LIMIT_RECS'][0]);
					}
					else {
						$this->db['LimitRecs'] = $this->defaults['LimitRecs'];
					}

					// set default MaxRank depending on title
					$this->db['MaxRank'] = ($aseco->server->title == 'TMStadium' ? 15 : 30);

					if (isset($dbdata['KEEP_BEST_VREPLAYS'][0])) {
						$this->db['KeepVReplays'] = (strtolower($dbdata['KEEP_BEST_VREPLAYS'][0]) == 'true');
					}
					else {
						$this->db['KeepVReplays'] = $this->defaults['KeepVReplays'];
					}

					// check/create validation replays directory
					if ($this->db['KeepVReplays']) {
						if (!file_exists($aseco->server->gamedir .'Replays/'. $this->vreplay_dir)) {
							if (!mkdir($aseco->server->gamedir .'Replays/'. $this->vreplay_dir, 0755, true)) {
								$aseco->console('[Dedimania] Validation Replays Directory ('. $aseco->server->gamedir .'Replays/'. $this->vreplay_dir .') cannot be created');
							}
						}
						if (!is_writeable($aseco->server->gamedir .'Replays/'. $this->vreplay_dir)) {
							$aseco->console('[Dedimania] Validation Replays Directory ('. $aseco->server->gamedir .'Replays/'. $this->vreplay_dir .') cannot be written to');
						}
					}

					// check/initialise server configuration
					$dbdata = &$config['DEDIMANIA']['MASTERSERVER_ACCOUNT'][0];
					$this->db['Login'] = $dbdata['LOGIN'][0];
					$this->db['DediCode'] = $dbdata['DEDIMANIACODE'][0];
					if ($this->db['Login'] == '' || $this->db['Login'] == 'YOUR_SERVER_LOGIN' || $this->db['DediCode'] == '' || $this->db['DediCode'] == 'YOUR_DEDIMANIA_CODE') {
						trigger_error('Dedimania not configured! <masterserver_account> contains default or empty value(s)', E_USER_ERROR);
					}

					if (strtolower($this->db['Login']) != $aseco->server->login) {
						trigger_error('Dedimania misconfigured! <masterserver_account><login> ('. $this->db['Login'] .') is not the actual server login ('. $aseco->server->login .')', E_USER_ERROR);
					}

					$this->db['Messages'] = &$config['DEDIMANIA']['MESSAGES'][0];
					$this->db['RecsValid'] = false;
					$this->db['BannedLogins'] = array();

					$this->db['ModeList'] = array();
					$this->db['ModeList'][Gameinfo::ROUNDS]		= 'Rounds';
					$this->db['ModeList'][Gameinfo::TIME_ATTACK]	= 'TA';
					$this->db['ModeList'][Gameinfo::TEAM]		= 'Rounds';
					$this->db['ModeList'][Gameinfo::LAPS]		= 'TA';
					$this->db['ModeList'][Gameinfo::CUP]		= 'Rounds';
					$this->db['ModeList'][Gameinfo::TEAM_ATTACK]	= false;	// 2015-06-20: unsupported mode
					$this->db['ModeList'][Gameinfo::CHASE]		= false;	// 2015-06-20: unsupported mode
					$this->db['ModeList'][Gameinfo::KNOCKOUT]	= 'Rounds';
					$this->db['ModeList'][Gameinfo::DOPPLER]	= 'TA';
				}
				else {
					trigger_error('No URL specified in your Dedimania config file!', E_USER_ERROR);
				}
			}
			else {
				trigger_error('Structure error in your Dedimania config file!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('Could not read/parse Dedimania config file ['. $this->config_file .']!', E_USER_ERROR);
		}

		if ($this->debug > 1) {
			print_r($this->db);
		}

		// connect to Dedimania server
		$this->dedimaniaConnect($aseco);

		$this->last_sent = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaConnect ($aseco) {

		$time = time();

		// check for no or timed-out connection
		if (!isset($this->db['XmlrpcDB']) && (!isset($this->db['XmlrpcDBbadTime']) || ($time - $this->db['XmlrpcDBbadTime']) > $this->timeout)) {

			$aseco->console('[Dedimania] ********************************************************');
			$aseco->console('[Dedimania] Dataserver connection on '. $this->db['Name'] .'...');
			$aseco->console('[Dedimania] Try connection on '. $this->db['Url'] .'...');

			// establish Dedimania connection and login
			$xmlrpcdb = new XmlrpcDB($this->webaccess, $this->db['Url']);

			if (!isset($this->db['SessionId'])) {
				$srvconnect = array(
					'Game'		=> 'TM2',
					'Login'		=> $this->db['Login'],
					'Code'		=> $this->db['DediCode'],
					'Path'		=> implode('|', array_merge(array('World'), $aseco->server->zone)),
					'Packmask'	=> 'United',
					'ServerVersion'	=> $aseco->server->version,
					'ServerBuild'	=> $aseco->server->build,
					'Tool'		=> UASECO_NAME,
					'Version'	=> UASECO_VERSION
				);

				$response = $xmlrpcdb->RequestWait('dedimania.OpenSession', $srvconnect);
				if ($this->debug > 3) {
					$aseco->console('[Dedimania] dedimaniaConnect() - response'. CRLF . print_r($response, true));
				}
				else if ($this->debug > 2) {
					$aseco->console('[Dedimania] dedimaniaConnect() - response[Data]'. CRLF . print_r($response['Data'], true));
				}

				// Reply a struct {'SessionId': string, 'Error': string}

				// check response
				if ($response === false) {
					$aseco->console('[Dedimania] Error bad database response!!!!');
				}
				else if (isset($response['Data']['params']['SessionId']) && $response['Data']['params']['SessionId'] != '') {
					$this->db['XmlrpcDB'] = $xmlrpcdb;
					$this->db['SessionId'] = $response['Data']['params']['SessionId'];
					$this->db['ConnectName'] = $response['Headers']['server'][0];
					$aseco->console('[Dedimania] Connection and status ok! ('. $response['Headers']['server'][0] .')');
					if (($errors = $this->is_error($response)) !== false) {
						$aseco->console('[Dedimania] ...with authentication warning(s): '. $errors);
					}
				}
				else if (($errors = $this->is_error($response)) !== false) {
					$aseco->console('[Dedimania] Connection Error: '. $errors . CRLF .'!!!');
				}
				else if (!isset($response['Code'])) {
					$aseco->console('[Dedimania] Error no database response ('. $this->db['Url'] .')'. CRLF .'  !!!');
				}
				else {
					$aseco->console('[Dedimania] Error bad database response or contents ('. $response['Headers']['server'][0] .') ['
					                     . $response['Code'] .', '. $response['Reason'] .']'. CRLF .'  !!!');
					if ($this->debug > 1) {
						if ($response['Code'] == 200) {
							$aseco->console('[Dedimania] dedimaniaConnect() - response[Message]'. CRLF . $response['Message']);
						}
						else if ($response['Code'] != 404) {
							$aseco->console('[Dedimania] dedimaniaConnect() - response'. CRLF . print_r($response, true));
						}
					}
				}
			}
			$aseco->console('[Dedimania] ********************************************************');

			// check for valid connection
			if (isset($this->db['XmlrpcDB'])) {
				return;
			}

			// prepare for next connection attempt
			$this->db['XmlrpcDBbadTime'] = $time;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaAnnounce () {
		global $aseco;

		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			if ($this->debug > 1) {
				$aseco->console('[Dedimania] Unsupported gamemode, records ignored!');
			}
			return;
		}

		// check for valid map
		if (isset($aseco->server->maps->current->uid)) {
			// check for valid connection
			if (isset($this->db['XmlrpcDB']) && !$this->db['XmlrpcDB']->isBad()) {
				if ($this->debug > 1) {
					$aseco->console('[Dedimania] Update server Dedimania info...');
				}

				// collect server, vote & players info
				$serverinfo = $this->dedimania_serverinfo($aseco);
				$voteinfo = array(
					'UId'		=> $aseco->server->maps->current->uid,
					'GameMode'	=> $this->db['ModeList'][$aseco->server->gameinfo->mode]
				);
				$players = $this->dedimania_players($aseco);

				$this->last_sent = time();
				$callback = array(array($this, 'dedimaniaAnnounceCallbackHandler'));
				$this->db['XmlrpcDB']->addRequest(
					$callback,
					'dedimania.UpdateServerPlayers',
					$this->db['SessionId'],
					$serverinfo,
					$voteinfo,
					$players
				);
				// UpdateServerPlayers(string SessionId, struct SrvInfo, struct VotesInfo, array Players)
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaAnnounceCallbackHandler ($response) {
		global $aseco;

		// Reply true
		if (($errors = $this->is_error($response)) !== false) {
			if ($this->debug > 3) {
				$aseco->console('[Dedimania] dedimaniaAnnounceCallbackHandler() - response'. CRLF . print_r($response, true));
			}
			else if ($this->debug > 2) {
				$aseco->console('[Dedimania] dedimaniaAnnounceCallbackHandler() - response[Data]'. CRLF . print_r($response['Data'], true));
			}
			else {
				$aseco->console('[Dedimania] dedimaniaAnnounceCallbackHandler() - error(s): '. $errors);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {

		// check for valid connection
		if (isset($this->db['XmlrpcDB'])) {
			// refresh DB every 4 mins after last DB update
			if ($this->last_sent + $this->refresh < time()) {
				$this->dedimaniaAnnounce();
			}

			if ($this->db['XmlrpcDB']->isBad()) {
				// retry after 30 mins of bad state
				if ($this->db['XmlrpcDB']->badTime() > $this->timeout) {
					$aseco->console('[Dedimania] Retry to send after '. round($this->timeout/60) .' minutes...');
					$this->db['XmlrpcDB']->retry();
				}
			}
			else {
				$response = $this->db['XmlrpcDB']->sendRequests();
				if (!$response) {
					$message = '{#server}» '. $aseco->formatText($this->db['Timeout'], round($this->timeout/60));
					$aseco->sendChatMessage($message);
					trigger_error('Dedimania has consecutive connection errors!', E_USER_WARNING);
				}
			}
		}
		else {
			// reconnect to Dedimania server
			$this->dedimaniaConnect($aseco);
		}

		// trigger pending callbacks
		$read = array();
		$write = null;
		$except = null;
		$this->webaccess->select($read, $write, $except, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		if ($this->debug > 1) {
			$aseco->console('[Dedimania] onPlayerConnect() - '. $player->login .' : '. $aseco->stripStyles($player->nickname, false));
		}

		// get player info & check for non-LAN login
		if ($pinfo = $this->dedimania_playerinfo($aseco, $player)) {
			if ($this->debug > 1) {
				$aseco->console('[Dedimania] onPlayerConnect() - pinfo'. CRLF . print_r($pinfo, true));
			}

			// check for valid connection
			if (isset($this->db['XmlrpcDB']) && !$this->db['XmlrpcDB']->isBad()) {
				$callback = array(array($this, 'dedimaniaPlayerConnectCallbackHandler'), $player->login);
				$this->db['XmlrpcDB']->addRequest(
					$callback,
					'dedimania.PlayerConnect',
					$this->db['SessionId'],
					$player->login,
					$player->nickname,
					$pinfo['Path'],
					$pinfo['IsSpec']
				);
				// PlayerConnect(string SessionId, string Login, string Nickname, string Path, boolean IsSpec)
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaPlayerConnectCallbackHandler ($response, $login) {
		global $aseco;

		// Reply a struct {'Login': string, 'MaxRank': int, 'Banned': boolean,
		//                 'OptionsEnabled': boolean, 'ToolOption': string}

		if ($this->debug > 3) {
			$aseco->console('[Dedimania] dedimaniaPlayerConnectCallbackHandler() - response'. CRLF . print_r($response, true));
		}
		else if ($this->debug > 2) {
			$aseco->console('[Dedimania] dedimaniaPlayerConnectCallbackHandler() - response[Data]'. CRLF . print_r($response['Data'], true));
		}
		else if (($errors = $this->is_error($response)) !== false) {
			$aseco->console('[Dedimania] dedimaniaPlayerConnectCallbackHandler() - error(s): '. $errors);
		}

		// check response
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			if ($this->debug > 0) {
				$aseco->console('[Dedimania] dedimaniaPlayerConnectCallbackHandler() - '. $login .' does not exist!');
			}
		}
		else if (isset($response['Data']['params'])) {
			// update nickname in record
			if ($this->db['RecsValid'] && !empty($this->db['Map']['Records']) && isset($player->nickname)) {
				foreach ($this->db['Map']['Records'] as &$rec) {
					if ($rec['Login'] == $login) {
						$rec['NickName'] = $player->nickname;
						break;
					}
				}
			}

			// show welcome message
			if ($this->db['ShowWelcome']) {
				$message = '{#server}» '. $this->db['Welcome'];
				$message = str_replace('{br}', LF, $message);  // split long message
				// hyperlink Dedimania site
				$message = str_replace('www.dedimania.com', '$l[http://www.dedimania.com/]www.dedimania.com$l', $message);
				$aseco->sendChatMessage($message, $login);
			}

			// get player rank
			$player->dedirank = $this->db['MaxRank'];
			if (isset($response['Data']['params']['MaxRank'])) {
				$player->dedirank = $response['Data']['params']['MaxRank'] + 0;
			}

			// check for banned player
			if (!isset($response['Data']['params']['Banned'])) {
				trigger_error('[Dedimania] Incomplete response on PlayerConnect - missing Banned field!'. CRLF . print_r($response['Data']['params'], true), E_USER_WARNING);
			}
			else if ($response['Data']['params']['Banned']) {
				// remember banned login
				$this->db['BannedLogins'][] = $login;
				// show chat message to all
				$message = $aseco->formatText($this->db['Messages']['BANNED_LOGIN'][0],
					$aseco->stripStyles($player->nickname),
					$login
				);
				$aseco->sendChatMessage($message);

				// log banned player
				$aseco->console('[Dedimania] Player [{1}] is banned - finishes ignored!', $login);
			}
		}
		else {
			if ($this->debug > 2) {
				$aseco->console('[Dedimania] dedimaniaPlayerConnectCallbackHandler() - bad response!');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		if ($this->debug > 1) {
			$aseco->console('[Dedimania] onPlayerDisconnect() - '. $player->login .' : '. $aseco->stripStyles($player->nickname, false));
		}

		// check for non-LAN login
		if (!$aseco->isLANLogin($player->login)) {
			// check for valid connection
			if (isset($this->db['XmlrpcDB']) && !$this->db['XmlrpcDB']->isBad()) {
				$this->db['XmlrpcDB']->addRequest(
					null,
					'dedimania.PlayerDisconnect',
					$this->db['SessionId'],
					$player->login,
					''
				);
				// PlayerDisconnect(string SessionId, string Login, string ToolOption)
				// ignore: Reply a struct {'Login': string}
			}
		}

		// clear possible banned login
		if (($i = array_search($player->login, $this->db['BannedLogins'])) !== false) {
			unset($this->db['BannedLogins'][$i]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		if ($this->debug > 1) {
			$aseco->console('[Dedimania] onLoadingMap() - map'. CRLF . print_r($map, true));
		}

		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			$aseco->console('[Dedimania] Unsupported gamemode, records ignored!');
			return;
		}

		// check for valid connection
		$this->db['Map'] = array();
		if (isset($this->db['XmlrpcDB']) && !$this->db['XmlrpcDB']->isBad()) {
			// collect server & players info
			$serverinfo = $this->dedimania_serverinfo($aseco);
			$players = $this->dedimania_players($aseco);
			$mapinfo = array(
				'UId'		=> $map->uid,
				'Name'		=> $map->name,
				'Environment'	=> $map->environment,
				'Author'	=> $map->author,
				'NbCheckpoints'	=> $map->nb_checkpoints,
				'NbLaps'	=> $map->nb_laps
			);

			$callback = array(array($this, 'dedimaniaLoadingMapCallbackHandler'), $map);
			$this->db['XmlrpcDB']->addRequest(
				$callback,
				'dedimania.GetChallengeRecords',
				$this->db['SessionId'],
				$mapinfo,
				$this->db['ModeList'][$aseco->server->gameinfo->mode],
				$serverinfo,
				$players
			);
			// GetChallengeRecords(string SessionId, struct MapInfo, string GameMode, struct SrvInfo, array Players)
		}

		$this->db['RecsValid'] = false;
		$this->db['TrackValid'] = false;
		$this->db['ServerMaxRank'] = $this->db['MaxRank'];
		$this->db['Top1Init'] = -1;

		if ($map->nb_checkpoints < 2 && $map->author != 'Nadeo') {
			// check for map without actual checkpoints
			$aseco->console('[Dedimania] Map\'s NbCheckpoints < 2: records ignored');
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS && $map->multi_lap && $aseco->server->gameinfo->rounds['ForceLapsNb'] > 0) {
			// check for multilap map in Rounds modes
			$aseco->console('[Dedimania] ForceLapsNb > 0: records ignored');
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM && $map->multi_lap && $aseco->server->gameinfo->team['ForceLapsNb'] > 0) {
			// check for multilap map in Team modes
			$aseco->console('[Dedimania] ForceLapsNb > 0: records ignored');
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::CUP && $map->multi_lap && $aseco->server->gameinfo->cup['ForceLapsNb'] > 0) {
			// check for multilap map in Cup modes
			$aseco->console('[Dedimania] ForceLapsNb > 0: records ignored');
		}
		else if ($map->author_time < $this->min_author_time) {
			// check for minimum author time
			$aseco->console('[Dedimania] Map\'s Author time < '. ($this->min_author_time / 1000) .'s: records ignored');
		}
		else {
			$this->db['TrackValid'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaLoadingMapCallbackHandler ($response, $map) {
		global $aseco;

		// Reply a struct {'UId': string, 'ServerMaxRank': int, 'AllowedGameModes': string(-list),
		//                 'Records': array of struct {'Login': string, 'NickName': string,
		//                                             'Best': int, 'Rank': int, 'MaxRank': int,
		//                                             'Checks': string (list of int), 'Vote': int},
		//                 'Players': array of struct {'Login': string, 'MaxRank': int},
		//                 'TotalRaces': int, 'TotalPlayers': int}

		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			return;
		}

		if ($this->debug > 3) {
			$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - response'. CRLF . print_r($response, true));
		}
		else if ($this->debug > 2) {
			$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - response[Data]'. CRLF . print_r($response['Data'], true));
		}
		else if (($errors = $this->is_error($response)) !== false) {
			$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - error(s): '. $errors);
		}

		// check response
		if (isset($response['Data']['params']) && $this->db['TrackValid']) {
			$this->db['Map'] = $response['Data']['params'];
			$this->db['RecsValid'] = true;
			if (isset($response['Data']['params']['ServerMaxRank'])) {
				$this->db['ServerMaxRank'] = $response['Data']['params']['ServerMaxRank'] + 0;
			}

			if ($this->debug > 1) {
				$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - records'. CRLF . print_r($this->db['Map']['Records'], true));
			}

			// check for records
			if (!empty($this->db['Map']['Records'])) {
				$this->db['Top1Init'] = $this->db['Map']['Records'][0]['Best'];
				// strip line breaks in nicknames
				foreach ($this->db['Map']['Records'] as &$rec) {
					$rec['NickName'] = str_replace("\n", '', $rec['NickName']);
				}

				// Set Dedimania record/checkpoints references
				if ($aseco->plugins['PluginCheckpoints']->config['TIME_DIFF_WIDGET'][0]['ENABLED'][0] == true) {
					foreach ($aseco->plugins['PluginCheckpoints']->checkpoints as $login => $cp) {
						if (isset($aseco->plugins['PluginCheckpoints']->checkpoints[$login]->tracking)) {
							$drec = $aseco->plugins['PluginCheckpoints']->checkpoints[$login]->tracking['dedimania_records'] - 1;

							// check for specific record
							if ($drec+1 > 0) {
								// if specific record unavailable, use last one
								if ($drec > count($this->db['Map']['Records']) - 1) {
									$drec = count($this->db['Map']['Records']) - 1;
								}
								// store record/checkpoints reference
								$aseco->plugins['PluginCheckpoints']->checkpoints[$login]->best['finish'] = $this->db['Map']['Records'][$drec]['Best'];
								$aseco->plugins['PluginCheckpoints']->checkpoints[$login]->best['cps'] = explode(',', $this->db['Map']['Records'][$drec]['Checks']);
							}
							else if ($drec+1 == 0) {
								// search for own/last record
								$drec = 0;
								while ($drec < count($this->db['Map']['Records'])) {
									if ($this->db['Map']['Records'][$drec++]['Login'] == $login) {
										break;
									}
								}
								$drec--;
								// store record/checkpoints reference
								$aseco->plugins['PluginCheckpoints']->checkpoints[$login]->best['finish'] = $this->db['Map']['Records'][$drec]['Best'];
								$aseco->plugins['PluginCheckpoints']->checkpoints[$login]->best['cps'] = explode(',', $this->db['Map']['Records'][$drec]['Checks']);
							}  // else -1
						}
					}
				}
				if ($this->debug > 4) {
					$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - checkpoints'. CRLF . print_r($aseco->plugins['PluginCheckpoints']->checkpoints, true));
				}
			}

			if ($this->db['ShowRecsBefore'] > 0) {
				$this->showDedimaniaRecords(
					$aseco,
					$map->name,
					$map->uid,
					$this->db['Map']['Records'],
					false,
					1,
					$this->db['ShowRecsBefore']
				);
			}

			if (!empty($this->db['Map']['Records'])) {
				// throw 'Dedimania records loaded' event
				$aseco->releaseEvent('onDedimaniaRecordsLoaded', $this->db['Map']['Records']);
			}
		}
		else {
			if ($this->debug > 2) {
				$aseco->console('[Dedimania] dedimaniaLoadingMapCallbackHandler() - bad response or map invalid!');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {

		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			return;
		}

		if ($this->debug > 1) {
			$aseco->console('[Dedimania] onEndMap - data'. CRLF . print_r($map, true));
		}

		// check for valid map
		if (isset($map->uid) && isset($this->db['TrackValid']) && $this->db['TrackValid']) {
			// check for valid connection
			if (isset($this->db['XmlrpcDB']) && !$this->db['XmlrpcDB']->isBad()) {
				// collect/sort new finish times & checkpoints
				if ($this->db['RecsValid'] && !empty($this->db['Map']['Records'])) {
					$times = array();
					foreach ($this->db['Map']['Records'] as $rec) {
						// check for valid, minimum finish time
						if (isset($rec['NewBest']) && $rec['NewBest'] && $rec['Best'] >= $this->min_finish_time) {
							$times[] = array(
								'Login'		=> $rec['Login'],
								'Best'		=> $rec['Best'],
								'Checks'	=> implode(',', $rec['Checks']),
							);
						}
					}
					if (!empty($times)) {
						usort($times, array($this, 'time_compare'));
					}
					else {
						$replays = array('VReplay' => '', 'VReplayChecks' => '', 'Top1GReplay' => '');
					}

					if ($this->debug > 1) {
						$aseco->console('[Dedimania] onEndMap - numchecks: '. $aseco->server->maps->current->nb_checkpoints);
						$aseco->console('[Dedimania] onEndMap - times'. CRLF . print_r($times, true));
					}

					// Collect logins with all checkpoints
					$rankings = array();
					foreach ($aseco->server->rankings->ranking_list as $rank) {
						$rankings[$rank->login] = $rank->best_race_checkpoints;
					}


					// Get replay(s) of best player, skip first if validation replay is not OK
					$first_time_ok = false;
					while (!$first_time_ok && !empty($times)) {
						$replays = array('VReplay' => '', 'VReplayChecks' => '', 'Top1GReplay' => '');

						// get & check validation replay
						$vreplay = $this->get_vreplay(
							$aseco,
							$aseco->server->maps->current->uid,
							$times[0],
							($aseco->server->gameinfo->mode == Gameinfo::TEAM ? array_fill(0, $aseco->server->maps->current->nb_checkpoints, 0) : $rankings[$times[0]['Login']])
						);
						if ($vreplay === false) {
							array_shift($times);
							continue;
						}
						else if ($vreplay === null) {
							return;
						}
						$replays['VReplay'] = new IXR_Base64($vreplay);

						// check for new top-1
						if ($this->db['Top1Init'] <= 0 || $times[0]['Best'] < $this->db['Top1Init']) {
							// get & check ghost replay
							$greplay = $this->get_greplay(
								$aseco,
								$aseco->server->maps->current->uid,
								$times[0],
								($aseco->server->gameinfo->mode == Gameinfo::TEAM ? array_fill(0, $aseco->server->maps->current->nb_checkpoints, 0) : $rankings[$times[0]['Login']])
							);
							if ($greplay === false) {
								array_shift($times);
								continue;
							}
							else if ($greplay === null) {
								return;
							}
							$replays['Top1GReplay'] = new IXR_Base64($greplay);
						}

						// in Laps mode, include all checkpoints too
						if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
							$replays['VReplayChecks'] = implode(',', $rankings[$times[0]['Login']]);
						}

						// store validation replay
						if ($this->db['KeepVReplays']) {
							$vrfile = sprintf('/Valid.%s.%d.%07d.%s.Replay.Gbx',
								$aseco->server->maps->current->uid,
								$aseco->server->gameinfo->mode,
								$times[0]['Best'], $times[0]['Login']
							);
							@file_put_contents($aseco->server->gamedir .'Replays/'. $this->vreplay_dir . $vrfile, $vreplay);
						}

						$first_time_ok = true;
					}

					$mapinfo = array(
						'UId'		=> $aseco->server->maps->current->uid,
						'Name'		=> $aseco->server->maps->current->name,
						'Environment'	=> $aseco->server->maps->current->environment,
						'Author'	=> $aseco->server->maps->current->author,
						'NbCheckpoints'	=> $aseco->server->maps->current->nb_checkpoints,
						'NbLaps'	=> $aseco->server->maps->current->nb_laps
					);

					$this->last_sent = time();
					$callback = array(array($this, 'dedimaniaEndMapCallbackHandler'), $map);
					$this->db['XmlrpcDB']->addRequest(
						$callback,
						'dedimania.SetChallengeTimes',
						$this->db['SessionId'],
						$mapinfo,
						$this->db['ModeList'][$aseco->server->gameinfo->mode],
						$times,
						$replays
					);
					// SetChallengeTimes(string SessionId, struct MapInfo, string GameMode, array Times, struct Replays)
					// Times: array of struct {'Login': string, 'Best': int, 'Checks': string (list of int}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimaniaEndMapCallbackHandler ($response, $map) {
		global $aseco;

		//Reply a struct {'UId': string, 'ServerMaxRank': int, 'AllowedGameModes': string(-list),
		//                'Records': array of struct {'Login': string, 'NickName': string,
		//                                            'Best': int, 'Rank': int, 'MaxRank': int,
		//                                            'Checks': string (list of int), 'NewBest': boolean} }

		if ($this->debug > 3) {
			$aseco->console('[Dedimania] dedimaniaEndMapCallbackHandler() - response'. CRLF . print_r($response, true));
		}
		else if ($this->debug > 2) {
			$aseco->console('[Dedimania] dedimaniaEndMapCallbackHandler() - response[Data]'. CRLF . print_r($response['Data'], true));
		}
		else if (($errors = $this->is_error($response)) !== false) {
			$aseco->console('[Dedimania] dedimaniaEndMapCallbackHandler() - error(s): '. $errors);
		}

		// check response
		if (isset($response['Data']['params'])) {
			$this->db['Results'] = $response['Data']['params'];

			// check for records
			if (!empty($this->db['Results']['Records'])) {
				// strip line breaks in nicknames
				foreach ($this->db['Results']['Records'] as &$rec) {
					$rec['NickName'] = str_replace("\n", '', $rec['NickName']);
				}
				if ($this->debug > 1) {
					$aseco->console('[Dedimania] dedimaniaEndMapCallbackHandler() - results'. CRLF . print_r($this->db['Results'], true));
				}

				if ($this->db['ShowRecsAfter'] > 0) {
					$this->showDedimaniaRecords(
						$aseco,
						$map->name,
						$map->uid,
						$this->db['Results']['Records'],
						false,
						3,
						$this->db['ShowRecsAfter']
					);
				}
			}

			// check for banned players
			if (isset($response['Data']['errors']) && preg_match('/Warning.+Player .+ is banned on Dedimania/', $response['Data']['errors'])) {
				// log banned players
				$errors = explode("\n", $response['Data']['errors']);
				foreach ($errors as $error) {
					if (preg_match('/Warning.+Player (.+) is banned on Dedimania/', $error, $login)) {
						$aseco->console('[Dedimania] Player [{1}] is banned - record ignored!', $login[1]);
					}
				}
			}
		}
		else {
			if ($this->debug > 2) {
				$aseco->console('dedimaniaEndMapCallbackHandler - bad response!');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// if no Dedimania records, bail out
		if (!$this->db['RecsValid']) {
			return;
		}

		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			return;
		}

		// if no actual finish, bail out immediately
		if ($finish_item->score == 0) {
			return;
		}

//		// in Laps mode on real PlayerFinish event, bail out too
//		if ($aseco->server->gameinfo->mode == Gameinfo::LAPS && !$finish_item->new) {
//			return;
//		}

		$login = $finish_item->player->login;
		$nickname = $aseco->stripStyles($finish_item->player->nickname);

		// if LAN login, bail out immediately
		if ($aseco->isLANLogin($login)) {
			return;
		}

		// if banned login, notify player and bail out
		if (in_array($login, $this->db['BannedLogins'])) {
			$message = $aseco->formatText($this->db['Messages']['BANNED_FINISH'][0]);
			$aseco->sendChatMessage($message, $login);
			return;
		}

		if ($this->debug > 4) {
			$aseco->console('[Dedimania] onPlayerFinish - checkpoints '. $login . CRLF . print_r($aseco->plugins['PluginCheckpoints']->checkpoints[$login], true));
		}


		// check finish/checkpoints consistency, only for supported gamemodes
		// Bail out on unsupported gamemodes
		if (!isset($this->db['ModeList'][$aseco->server->gameinfo->mode]) || $this->db['ModeList'][$aseco->server->gameinfo->mode] === false) {
			if ($aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['finish'] == PHP_INT_MAX) {
				// Skip if no checkpoint times are stored
				return;
			}
			if (count($aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['cps']) < 2 && $aseco->server->maps->current->author != 'Nadeo') {
				$aseco->console('[Dedimania] Player ['. $login .'] checks < 2, finish ignored: '. $aseco->formatTime($finish_item->score));
				return;
			}
			if ($aseco->server->gameinfo->mode != Gameinfo::LAPS && $finish_item->score != $aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['finish']) {
				$aseco->console('[Dedimania] Player ['. $login .'] inconsistent finish time and checkpoint finish time, ignored time ['. $finish_item->score .'] != ['. $aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['finish'] .']');
				return;
			}
			if ($finish_item->score != end($aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['cps'])) {
				$aseco->console('[Dedimania] Player ['. $login .'] inconsistent finish time and last checkpoint time, ignored time ['. $finish_item->score .'] != ['. end($aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['cps']) .']');
				return;
			}
		}

		// point to master records list
		$dedi_recs = &$this->db['Map']['Records'];
		$maxrank = max($this->db['ServerMaxRank'], $finish_item->player->dedirank);

		// go through all records
		for ($i = 0; $i < $maxrank; $i++) {
			// check if no record, or player's time/score is better
			if (!isset($dedi_recs[$i]) || $finish_item->score < $dedi_recs[$i]['Best']) {
				// does player have a record already?
				$cur_rank = -1;
				$cur_score = 0;
				for ($rank = 0; $rank < count($dedi_recs); $rank++) {
					$rec = $dedi_recs[$rank];

					if ($login == $rec['Login']) {
						if ($finish_item->score > $rec['Best']) {
							// new record worse than old one
							return;
						}
						else {
							// new record is better than or equal to old one
							$cur_rank = $rank;
							$cur_score = $rec['Best'];
							break;
						}
					}
				}

				$finish_time = $aseco->formatTime($finish_item->score);

				if ($cur_rank != -1) {  // player has a record in topXX already
					// Compute difference to old record
					$diff = $cur_score - $finish_item->score;
					$sec = floor($diff/1000);
					$ths = $diff - ($sec * 1000);

					// Update the record if improved
					if ($diff > 0) {
						// Ignore 'Rank' field - not used in /dedi* commands
						$dedi_recs[$cur_rank]['Best'] = $finish_item->score;
						$dedi_recs[$cur_rank]['Checks'] = $aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['cps'];
						$dedi_recs[$cur_rank]['NewBest'] = true;
					}

					// Player moved up in Dedimania list
					if ($cur_rank > $i) {

						// move record to the new position
						$aseco->moveArrayElement($dedi_recs, $cur_rank, $i);

						// do a player improved his/her Dedimania rank message
						$message = $aseco->formatText($this->db['Messages']['RECORD_NEW_RANK'][0],
							$nickname,
							$i + 1,
							$finish_time,
							$cur_rank + 1,
							'-'. $aseco->formatTime($diff)
						);

						// show chat message to all or player
						if ($this->db['DisplayRecs']) {
							if ($i < $this->db['LimitRecs']) {
								if ($this->db['RecsInWindow']) {
									$aseco->releaseEvent('onSendWindowMessage', array($message, false));
								}
								else {
									$aseco->sendChatMessage($message);
								}
							}
							else {
								$message = str_replace('{#server}» ', '{#server}» ', $message);
								$aseco->sendChatMessage($message, $login);
							}
						}
					}
					else {
						if ($diff == 0) {
							// do a player equaled his/her record message
							$message = $aseco->formatText($this->db['Messages']['RECORD_EQUAL'][0],
								$nickname,
								$cur_rank + 1,
								$finish_time
							);
						}
						else {
							// do a player secured his/her record message
							$message = $aseco->formatText($this->db['Messages']['RECORD_NEW'][0],
								$nickname,
								$i + 1,
								$finish_time,
								$cur_rank + 1,
								'-'. $aseco->formatTime($diff)
							);
						}

						// show chat message to all or player
						if ($this->db['DisplayRecs']) {
							if ($i < $this->db['LimitRecs']) {
								if ($this->db['RecsInWindow']) {
									$aseco->releaseEvent('onSendWindowMessage', array($message, false));
								}
								else {
									$aseco->sendChatMessage($message);
								}
							}
							else {
								$message = str_replace('{#server}» ', '{#server}» ', $message);
								$aseco->sendChatMessage($message, $login);
							}
						}
					}

				}
				else {
					// player hasn't got a record yet

					// insert new record at the specified position
					// ignore 'Rank' field - not used in /dedi* commands
					$record = array(
						'Login'		=> $login,
						'NickName'	=> $finish_item->player->nickname,
						'Best'		=> $finish_item->score,
						'Checks'	=> $aseco->plugins['PluginCheckpoints']->checkpoints[$login]->current['cps'],
						'NewBest'	=> true
					);
					$aseco->insertArrayElement($dedi_recs, $record, $i);

					// do a player drove first record message
					$message = $aseco->formatText($this->db['Messages']['RECORD_FIRST'][0],
						$nickname,
						$i + 1,
						$finish_time
					);

					// show chat message to all or player
					if ($this->db['DisplayRecs']) {
						if ($i < $this->db['LimitRecs']) {
							if ($this->db['RecsInWindow']) {
								$aseco->releaseEvent('onSendWindowMessage', array($message, false));
							}
							else {
								$aseco->sendChatMessage($message);
							}
						}
						else {
							$message = str_replace('{#server}» ', '{#server}» ', $message);
							$aseco->sendChatMessage($message, $login);
						}
					}
				}

				$this->sendDiscordMessage($message);

				// log a new Dedimania record (not an equalled one)
				if (isset($dedi_recs[$i]['NewBest']) && $dedi_recs[$i]['NewBest']) {
					// log record message in console
					$aseco->console('[Dedimania] Player [{1}] finished with [{2}] and took the {3}. Dedimania Record!',
						$login,
						$aseco->formatTime($finish_item->score),
						$i + 1
					);

					// throw 'Dedimania record' event
					$dedi_recs[$i]['Pos'] = $i+1;
					$aseco->releaseEvent('onDedimaniaRecord', $dedi_recs[$i]);
				}
				if ($this->debug > 1) {
					$aseco->console('[Dedimania] onPlayerFinish - dedi_recs'. CRLF . print_r($dedi_recs, true));
				}

				// got the record, now stop!
				return;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/*
	 * Support functions
	 */
	public function dedimania_players ($aseco) {

		// collect all players
		$players = array();
		foreach ($aseco->server->players->player_list as $pl) {
			$pinfo = $this->dedimania_playerinfo($aseco, $pl, true);
			if ($pinfo !== false) {
				$players[] = $pinfo;
			}
		}
		if ($this->debug > 2 || ($this->debug > 1 && count($players) > 0)) {
			$aseco->console('[Dedimania] dedimania_players() - players'. CRLF . print_r($players, true));
		}

		return $players;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimania_playerinfo ($aseco, $player, $short = false) {

		// check for non-LAN login
		if (!$aseco->isLANLogin($player->login)) {
			try {
				// Get current player info
				$info = $aseco->client->query('GetDetailedPlayerInfo', $player->login);
				if ($short) {
					return array(
						'Login'		=> $info['Login'],
						'IsSpec'	=> $info['IsSpectator'],
						'Vote'		=> -1
					);
				}
				else {
					return array(
						'Login'		=> $info['Login'],
						'NickName'	=> $info['NickName'],
						'Path'		=> $info['Path'],
						'IsSpec'	=> $info['IsSpectator']
					);
				}
			}
			catch (Exception $exception) {
				$aseco->console('[Dedimania] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetDetailedPlayerInfo: Player ['. $player->login .']');
				return false;
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function dedimania_serverinfo ($aseco) {

		// Compute number of players and spectators
		$numplayers = 0;
		$numspecs = 0;
		foreach ($aseco->server->players->player_list as $player) {
			if ($player->is_spectator) {
				$numspecs++;
			}
			else {
				$numplayers++;
			}
		}

		$serverinfo = array(
			'SrvName'	=> $aseco->server->name,
			'Comment'	=> $aseco->server->comment,
			'Private'	=> ($aseco->server->options['Password'] != ''),
			'NumPlayers'	=> $numplayers,
			'MaxPlayers'	=> $aseco->server->options['CurrentMaxPlayers'],
			'NumSpecs'	=> $numspecs,
			'MaxSpecs'	=> $aseco->server->options['CurrentMaxSpectators'],
		);
		if ($this->debug > 1) {
			$aseco->console('[Dedimania] dedimania_serverinfo() - serverinfo'. CRLF . print_r($serverinfo, true));
		}
		return $serverinfo;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function get_vreplay ($aseco, $uid, $entry, $allcps) {
		try {
			// Get validation replay
			if (!$vreplay = $aseco->client->query('GetValidationReplay', $entry['Login'])) {
				$aseco->console('[Dedimania] Unable to get validation replay for Player ['. $entry['Login'] .']: skipped ['. $aseco->formatTime($entry['Best']) .']');
				return false;
			}

			// parse validation replay and check UID
			$parser = new GBXReplayFetcher(true);
			try {
				$parser->processData($vreplay->scalar);
			}
			catch (Exception $e) {
				$aseco->console('[Dedimania] Unable to parse validation replay for Player ['. $entry['Login'] .']: skipped ['. $aseco->formatTime($entry['Best']) .']: '. $e->getMessage());
				return false;
			}

			if ($this->debug > 1) {
				$aseco->console('[Dedimania] get_vreplay() - parsed validation replay:'. CRLF . print_r($parser->xml, true));
			}
			if ($parser->uid != $uid) {
				$aseco->console('[Dedimania] Validation replay UID not matched for Player ['. $entry['Login'] .']: skipped all records');
				return null;
			}


			// check finish/checkpoints consistency
			if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
				// In Laps.Script.txt not multilaps Maps are playable, add not 'NbLaps' in this case!
				if ($aseco->server->maps->current->multi_lap == true) {
					$cpsrace = $aseco->server->maps->current->nb_checkpoints * $aseco->server->gameinfo->laps['ForceLapsNb'];
				}
				else {
					$cpsrace = $aseco->server->maps->current->nb_checkpoints;
				}
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
				$cpsrace = $aseco->server->maps->current->nb_checkpoints;
			}
			else {
				$cpsrace = $aseco->server->maps->current->nb_checkpoints * ($aseco->server->maps->current->nb_laps > 0 ? $aseco->server->maps->current->nb_laps : 1);
			}

if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
	$aseco->dump($parser, $uid, $entry, $allcps, $cpsrace);
}
			$validation_success = true;
			if ($parser->cpsLap != $aseco->server->maps->current->nb_checkpoints) {
				$aseco->console('[Dedimania] Validation replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints at lap difference between validation replay ['. $cpsrace .'] and map ['. $aseco->server->maps->current->nb_checkpoints .'], all checkpoint times ['. $entry['Checks'] .']');
				$validation_success = false;
			}
			if ($aseco->server->maps->current->multi_lap == true && ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::CUP)) {
				if ($cpsrace != count($allcps)) {
					$aseco->console('[Dedimania] Validation replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints difference between calculate ['. $cpsrace .'] and driven ['. count($allcps) .'] in Gamemode "'. $aseco->server->gameinfo->getModeScriptName() .'".');
					$validation_success = false;
				}
			}
			else {
				if ($cpsrace != count(explode(',', $entry['Checks']))) {
					$aseco->console('[Dedimania] Validation replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints difference between calculate ['. $cpsrace .'] and driven ['. count(explode(',', $entry['Checks'])) .']');
					$validation_success = false;
				}
			}
			if ($parser->cpsCur != count($allcps)) {
				$aseco->console('[Dedimania] Validation replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints at race difference between validation replay ['. $parser->cpsCur .'] and record ['. count($allcps) .'], all checkpoint times ['. $entry['Checks'] .']');
				$validation_success = false;
			}
			if ($parser->replay != ($aseco->server->gameinfo->mode == Gameinfo::LAPS ? end($allcps) : $entry['Best'])) {
				$aseco->console('[Dedimania] Validation replay inconsistent for Player ['. $entry['Login'] .'] skipped: Finish-Time difference between validation replay ['. $parser->replay .'] and best time ['. ($aseco->server->gameinfo->mode == Gameinfo::LAPS ? end($allcps) : $entry['Best']) .']');
				$validation_success = false;
			}

			// Success?
			if ($validation_success === true) {
				return $vreplay->scalar;
			}
			else {
				return false;
			}
		}
		catch (Exception $exception) {
			$aseco->console('[Dedimania] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - GetValidationReplay: Player ['. $entry['Login'] .']');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function get_greplay ($aseco, $uid, $entry, $allcps) {
		try {
			// save ghost replay
			$grfile = sprintf('/Ghost.%s.%d.%07d.%s.Replay.Gbx',
				$aseco->server->maps->current->uid,
				$aseco->server->gameinfo->mode,
				$entry['Best'],
				$entry['Login']
			);
			if (!$aseco->client->query('SaveBestGhostsReplay', $entry['Login'], $this->greplay_dir . $grfile)) {
				$aseco->console('[Dedimania] Unable to save ghost replay for Player ['. $entry['Login'] .']: skipped ['. $aseco->formatTime($entry['Best']) .']');
				return false;
			}
			if (!$greplay = file_get_contents($aseco->server->gamedir .'Replays/'. $this->greplay_dir . $grfile)) {
				$aseco->console('[Dedimania] Unable to load ghost replay for Player ['. $entry['Login'] .']: skipped ['. $aseco->formatTime($entry['Best']) .']');
				return false;
			}

			// parse ghost replay and check UID
			$parser = new GBXReplayFetcher(true);
			try {
				$parser->processData($greplay);
			}
			catch (Exception $e) {
				$aseco->console('[Dedimania] Unable to parse ghost replay for Player ['. $entry['Login'] .']: skipped ['. $aseco->formatTime($entry['Best']) .']: '. $e->getMessage());
				return false;
			}

			if ($this->debug > 1) {
				$aseco->console('[Dedimania] get_greplay() - parsed ghost replay:'. CRLF . print_r($parser->xml, true));
			}
			if ($parser->uid != $uid) {
				$aseco->console('[Dedimania] Ghost replay UID not matched for Player ['. $entry['Login'] .']: skipped all records');
				return null;
			}


			// check finish/checkpoints consistency
			if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
				// In Laps.Script.txt not multilaps Maps are playable, add not 'NbLaps' in this case!
				if ($aseco->server->maps->current->multi_lap == true) {
					$cpsrace = $aseco->server->maps->current->nb_checkpoints * $aseco->server->gameinfo->laps['ForceLapsNb'];
				}
				else {
					$cpsrace = $aseco->server->maps->current->nb_checkpoints;
				}
			}
			else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
				$cpsrace = $aseco->server->maps->current->nb_checkpoints;
			}
			else {
				$cpsrace = $aseco->server->maps->current->nb_checkpoints * ($aseco->server->maps->current->nb_laps > 0 ? $aseco->server->maps->current->nb_laps : 1);
			}

			$validation_success = true;
			if ($parser->cpsLap != $aseco->server->maps->current->nb_checkpoints) {
				$aseco->console('[Dedimania] Ghost replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints at lap difference between validation replay ['. $cpsrace .'] and map ['. $aseco->server->maps->current->nb_checkpoints .'], all checkpoint times ['. $entry['Checks'] .']');
				$validation_success = false;
			}
			if ($aseco->server->maps->current->multi_lap == true && ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::CUP)) {
				if ($cpsrace != count($allcps)) {
					$aseco->console('[Dedimania] Ghost replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints difference between calculate ['. $cpsrace .'] and driven ['. count($allcps) .'] in Gamemode "'. $aseco->server->gameinfo->getModeScriptName() .'".');
					$validation_success = false;
				}
			}
			else {
				if ($cpsrace != count(explode(',', $entry['Checks']))) {
					$aseco->console('[Dedimania] Ghost replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints difference between calculate ['. $cpsrace .'] and driven ['. count(explode(',', $entry['Checks'])) .']');
					$validation_success = false;
				}
			}
			if ($parser->cpsCur != count($allcps)) {
				$aseco->console('[Dedimania] Ghost replay inconsistent for Player ['. $entry['Login'] .'] skipped: Amount of checkpoints at race difference between validation replay ['. $parser->cpsCur .'] and record ['. count($allcps) .'], all checkpoint times ['. $entry['Checks'] .']');
				$validation_success = false;
			}
			if ($parser->replay != ($aseco->server->gameinfo->mode == Gameinfo::LAPS ? end($allcps) : $entry['Best'])) {
				$aseco->console('[Dedimania] Ghost replay inconsistent for Player ['. $entry['Login'] .'] skipped: Finish-Time difference between validation replay ['. $parser->replay .'] and best time ['. ($aseco->server->gameinfo->mode == Gameinfo::LAPS ? end($allcps) : $entry['Best']) .']');
				$validation_success = false;
			}

			// Success?
			if ($validation_success === true) {
				return $greplay;
			}
			else {
				return false;
			}
		}
		catch (Exception $exception) {
			$aseco->console('[Dedimania] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SaveBestGhostsReplay: Player ['. $entry['Login'] .']');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function is_error (&$response) {

		if (!isset($response)) {
			return 'No response!';
		}
		if (isset($response['Error'])) {
			if (is_string($response['Error']) && strlen($response['Error']) > 0) {
				return $response['Error'];
			}
		}
		if (isset($response['Data']['errors'])) {
			if (is_string($response['Data']['errors']) && strlen($response['Data']['errors']) > 0) {
				return $response['Data']['errors'];
			}
			if (is_array($response['Data']['errors']) && count($response['Data']['errors']) > 0) {
				return print_r($response['Data']['errors'], true);
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// usort comparison function: return -1 if $a should be before $b, 1 if vice-versa
	public function time_compare ($a, $b) {

		if ($a['Best'] < $b['Best']) {
			// best a better than best b
			return -1;
		}
		else if ($a['Best'] > $b['Best']) {
			// best b better than best a
			return 1;
		}
		else {
			// same best, use timestamp
			return ($aseco->plugins['PluginCheckpoints']->checkpoints[$a['Login']]->best['timestamp'] < $aseco->plugins['PluginCheckpoints']->checkpoints[$b['Login']]->best['timestamp']) ? -1 : 1;
		}
	}
}

?>
