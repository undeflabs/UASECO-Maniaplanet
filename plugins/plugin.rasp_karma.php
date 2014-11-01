<?php
/*
 * Plugin: Rasp Karma
 * ~~~~~~~~~~~~~~~~~~
 * » Votes for a map and displays current score of it.
 * » Based upon plugin.rasp_karma.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-11-01
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
 *  - none
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginRaspKarma();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRaspKarma extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Votes for a map and displays current score of it.');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onBeginMap1',	'onBeginMap1');
		$this->registerEvent('onPlayerChat',	'onPlayerChat');
		$this->registerEvent('onPlayerFinish',	'onPlayerFinish');
		$this->registerEvent('onEndMap',	'onEndMap');

		$this->registerChatCommand('karma',	'chat_karma',		'Shows karma for the current map {Map_ID}',	Player::PLAYERS);
		$this->registerChatCommand('++',	'chat_playervote',	'Increases karma for the current map',		Player::PLAYERS);
		$this->registerChatCommand('--',	'chat_playervote',	'Decreases karma for the current map',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		// Get settings
		$this->readSettings();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_karma ($aseco, $login, $chat_command, $chat_parameter) {

		// if karma system disabled, bail out immediately
		if (!$this->feature_karma) {
			return;
		}

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check optional parameter
		if (is_numeric($chat_parameter) && $chat_parameter >= 0) {
			if (empty($player->maplist)) {
				$message = $this->messages['LIST_HELP'][0];
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
			$jid = ltrim($chat_parameter, '0');
			$jid--;

			// find map by given #
			if (array_key_exists($jid, $player->maplist)) {
				$uid = $player->maplist[$jid]['uid'];

				// Get map object
				$map = $aseco->server->maps->getMapByUid($uid);

				$karma = $this->getKarma($map->id, $player->login);
				$message = $aseco->formatText($this->messages['KARMA_MAP'][0],
					$aseco->stripColors($map->name),
					$karma
				);
			}
			else {
				$message = $this->messages['JUKEBOX_NOTFOUND'][0];
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}
		else {
			// get karma info
			$karma = $this->getKarma($aseco->server->maps->current->id, $player->login);
			$message = $aseco->formatText($this->messages['KARMA'][0], $karma);
		}

		// strip 1 leading '>' to indicate a player message instead of system-wide
		$message = str_replace('{#server}» ', '{#server}» ', $message);

		// show chat message
		$aseco->sendChatMessage($message, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_playervote ($aseco, $login, $chat_command, $chat_parameter) {

		// if karma system disabled, bail out immediately
		if (!$this->feature_karma) {
			return;
		}

		if ($chat_command == '++') {
			$vote = 1;
		}
		else if ($chat_command == '--') {
			$vote = -1;
		}

		if (!$caller = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for relay server
		if ($aseco->server->isrelay) {
			$message = $aseco->formatText($aseco->getChatMessage('NOTONRELAY'));
			$aseco->sendChatMessage($message, $caller->login);
			return;
		}

		// check if finishes are required
		if ($this->karma_require_finish > 0) {
			$query = "
			SELECT
				`MapId`
			FROM `%prefix%times`
			WHERE `PlayerId` = ". $caller->id ."
			AND `MapId` = ". $aseco->server->maps->current->id .";
			";

			$res = $aseco->db->query($query);
			if ($res) {
				// check whether player finished required number of times
				if ($res->num_rows < $this->karma_require_finish) {
					// show chat message
					$message = $aseco->formatText($this->messages['KARMA_REQUIRE'][0],
						$this->karma_require_finish,
						($this->karma_require_finish == 1 ? '' : 's')
					);
					$aseco->sendChatMessage($message, $caller->login);

					$res->free_result();
					return;
				}
				$res->free_result();
			}
		}

		$chat_parameter = '';  // clear sneaky params before chat_karma()
		$query = "
		SELECT
			`MapId`,
			`Score`
		FROM `%prefix%ratings`
		WHERE `PlayerId` = ". $caller->id ."
		AND `MapId` = ". $aseco->server->maps->current->id .";
		";

		$res = $aseco->db->query($query);
		if ($res->num_rows > 0) {
			$row = $res->fetch_object();
			if ($row->Score == $vote) {
				$message = $this->messages['KARMA_VOTED'][0];
				$aseco->sendChatMessage($message, $caller->login);
			}
			else {
				$query2 = "
				UPDATE `%prefix%ratings` SET
					`Score` = ". $vote ."
				WHERE `MapId` = ". $row->MapId ."
				AND `PlayerId` = ". $caller->id .";
				";
				$aseco->db->query($query2);
				if ($aseco->db->affected_rows === -1) {
					$message = $this->messages['KARMA_FAIL'][0];
					$aseco->sendChatMessage($message, $caller->login);
				}
				else {
					$message = $this->messages['KARMA_CHANGE'][0];
					$aseco->sendChatMessage($message, $caller->login);
					$this->chat_karma($aseco, $caller->login, 'karma', '');
					$aseco->releaseEvent('onKarmaChange', $this->getKarmaValues($aseco->server->maps->current->id));
				}
			}
		}
		else {
			$query2 = "
			INSERT INTO `%prefix%ratings` (
				`Score`,
				`PlayerId`,
				`MapId`
			)
			VALUES (
				". $vote .",
				". $caller->id .",
				". $aseco->server->maps->current->id ."
			);
			";
			$aseco->db->query($query2);
			if ($aseco->db->affected_rows === -1) {
				$message = $this->messages['KARMA_FAIL'][0];
				$aseco->sendChatMessage($message, $caller->login);
			}
			else {
				$message = $this->messages['KARMA_DONE'][0];
				$aseco->sendChatMessage($message, $caller->login);
				$this->chat_karma($aseco, $caller->login, 'karma', '');
				$aseco->releaseEvent('onKarmaChange', $this->getKarmaValues($aseco->server->maps->current->id));
			}
		}
		$res->free_result();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=PlayerUid, [1]=Login, [2]=Text, [3]=IsRegistredCmd
	public function onPlayerChat ($aseco, $chat) {

		// check for possible public karma vote
		if ($this->allow_public_karma) {
			if ( ($chat[2] == '++') || ($chat[2] == '--') ) {
				$this->chat_playervote($aseco, $chat[1], $chat[2], '');
			}
		}
		else {
			$message = $this->messages['KARMA_NOPUBLIC'][0];
			$aseco->sendChatMessage($message);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {

		if ($this->feature_karma && $this->karma_show_start) {
			// Show players' actual votes, or global karma message?
			if ($this->karma_show_votes) {
				// Send individual player messages
				foreach ($aseco->server->players->player_list as $pl) {
					$karma = $this->getKarma($map->id, $pl->login);
					$message = $aseco->formatText($this->messages['KARMA'][0], $karma);
					$aseco->sendChatMessage($message, $pl->login);
				}
			}
			else {
				// Send more efficient global message
				$karma = $this->getKarma($map->id, false);
				$message = $aseco->formatText($this->messages['KARMA'][0], $karma);
				$aseco->sendChatMessage($message);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// if no finish reminders, bail out immediately
		if (!$this->feature_karma || $this->remind_karma != 2) {
			return;
		}

		// if no actual finish, bail out too
		if ($finish_item->score == 0) {
			return;
		}

		// check whether player already voted
		$query = "
		SELECT
			`MapId`,
			`Score`
		FROM `%prefix%ratings`
		WHERE `PlayerId` = ". $finish_item->player->id ."
		AND `MapId` = ". $aseco->server->maps->current->id .";
		";

		$res = $aseco->db->query($query);
		if ($res->num_rows == 0) {
			// show reminder message
			$message = $this->messages['KARMA_REMIND'][0];
			$aseco->sendChatMessage($message, $finish_item->player->login);
		}
		$res->free_result();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// if no end race reminders, bail out immediately
		if (!$this->feature_karma || $this->remind_karma != 1) {
			return;
		}

		// check all connected players
		foreach ($aseco->server->players->player_list as $player) {
			// get current player status
			if (!$player->isspectator) {
				// check whether player already voted
				$query = "
				SELECT
					`MapId`,
					`Score`
				FROM `%prefix%ratings`
				WHERE `PlayerId` = ". $player->id ."
				AND `MapId` = ". $aseco->server->maps->current->id .";
				";

				$res = $aseco->db->query($query);
				if ($res->num_rows == 0) {
					// show reminder message
					$message = $this->messages['KARMA_REMIND'][0];
					$aseco->sendChatMessage($message, $player->login);
				}
				$res->free_result();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getKarmaValues ($mapid) {
		global $aseco;

		// get vote sum and count
		$query = "
		SELECT
			SUM(`Score`) AS `Karma`,
			COUNT(`Score`) AS `Total`
		FROM `%prefix%ratings`
		WHERE `MapId` = ". $mapid .";
		";

		$res = $aseco->db->query($query);
		if ($res->num_rows == 1) {
			$row = $res->fetch_object();
			$karma = $row->Karma;
			$total = $row->Total;
			$res->free_result();

			// get vote counts & percentages
			if ($total > 0) {
				$query2 = "
				SELECT
				(
					SELECT
						COUNT(*)
					FROM `%prefix%ratings`
					WHERE `Score` > 0
					AND `MapId` = `karma`.`MapId`
				) AS `Plus`,
				(
					SELECT
						COUNT(*)
					FROM `%prefix%ratings`
					WHERE `Score` < 0
					AND `MapId` = `karma`.`MapId`
				) AS `Minus`
				FROM `%prefix%ratings` AS `karma`
				WHERE `MapId` =". $mapid ."
				GROUP BY `MapId`;
				";

				$res2 = $aseco->db->query($query2);
				if ($res2->num_rows == 1) {
					$row2 = $res2->fetch_object();
					$plus = $row2->Plus;
					$minus = $row2->Minus;
				}
				else {
					$plus = 0;
					$minus = 0;
				}
				$res2->free_result();
				return array(
					'Karma'		=> $karma,
					'Total'		=> $total,
					'Good'		=> $plus,
					'Bad'		=> $minus,
					'GoodPct'	=> $plus / $total * 100,
					'BadPct'	=> $minus / $total * 100
				);
			}
		}
		return array(
			'Karma'		=> 0,
			'Total'		=> 0,
			'Good'		=> 0,
			'Bad'		=> 0,
			'GoodPct'	=> 0.0,
			'BadPct'	=> 0.0
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getKarma ($mapid, $login) {
		global $aseco;

		$karmavalues = $this->getKarmaValues($mapid);
		$karma = $karmavalues['Karma'];
		$total = $karmavalues['Total'];
		$plus = $karmavalues['Good'];
		$minus = $karmavalues['Bad'];
		$pluspct = $karmavalues['GoodPct'];
		$minuspct = $karmavalues['BadPct'];

		// optionally add vote counts & percentages
		if ($this->karma_show_details) {
			if ($total > 0) {
				$karma = $aseco->formatText($this->messages['KARMA_DETAILS'][0],
					$karma,
					$plus,
					round($pluspct),
					$minus,
					round($minuspct)
				);
			}
			else {
				// no votes yet
				$karma = $aseco->formatText($this->messages['KARMA_DETAILS'][0],
					$karma,
					0,
					0,
					0,
					0
				);
			}
		}

		// optionally add player's actual vote
		if ($this->karma_show_votes) {
			$playerid = $aseco->server->players->getPlayerId($login);
			if ($playerid != 0) {
				$query3 = "
				SELECT
					`Score`
				FROM `%prefix%ratings`
				WHERE `PlayerId` = ". $playerid ."
				AND `MapId` =". $mapid .";
				";

				$res3 = $aseco->db->query($query3);
				if ($res3->num_rows > 0) {
					$row3 = $res3->fetch_object();
					if ($row3->Score == 1) {
						$vote = '++';
					}
					else {  // -1
						$vote = '--';
					}
				}
				else {
					$vote = 'none';
				}
				$karma .= $aseco->formatText($this->messages['KARMA_VOTE'][0], $vote);
				$res3->free_result();
			}
		}
		return $karma;
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
			$aseco->console('[RaspKarma] Loading config file ['. $config_file .']');
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
