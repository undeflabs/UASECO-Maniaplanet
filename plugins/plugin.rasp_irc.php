<?php
/*
 * Plugin: Rasp IRC
 * ~~~~~~~~~~~~~~~~
 * » Provides IRC bot to link the server to a channel on an IRC server.
 * » Based upon plugin.rasp_irc.php from XAseco2/1.03
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
	$_PLUGIN = new PluginRaspIrc();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRaspIrc extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Provides IRC bot to link the server to a channel on an IRC server.');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onMainLoop',	'onMainLoop');
	}
	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $null) {
		global $aseco;

		set_time_limit(0);

		// Get settings
		$this->readSettings();

		// We need this to see if we need to JOIN (the channel) during
		// the first iteration of the main loop
		$firstTime = true;

		$aseco->console('[RaspIRC] Connecting to IRC...');
		$this->irc->con['socket'] = fsockopen($this->irc->server, $this->irc->port);

		// Check that we have connected
		if (!$this->irc->con['socket']) {
			$aseco->console('[RaspIRC] Could not connect to: ' . $this->irc->server . ' on port ' . $this->irc->port);
		}
		else {
			// Send the username and nick
			$this->irc_send('USER '. $this->irc->nick .' codedemons.net codedemons.net :'. $this->irc->name);
			$this->irc_send('NICK '. $this->irc->nick .' codedemons.net');

			// Here is the loop. Read the incoming data (from the socket connection)
			while (!feof($this->irc->con['socket'])) {
				// Think of $this->irc->con['buffer']['all'] as a line of chat messages.
				// We are getting a 'line' and getting rid of whitespace around it.
				$this->irc->con['buffer']['all'] = trim(fgets($this->irc->con['socket'], 4096));

				if ($this->irc->show_connect) {
					$aseco->console('[RaspIRC] '. $this->irc->con['buffer']['all']);
				}

				// If the server is PINGing, then PONG. This is to tell the server that
				// we are still here, and have not lost the connection
				if (substr($this->irc->con['buffer']['all'], 0, 6) === 'PING :') {
					// PONG : is followed by the line that the server sent us when PINGing
					$this->irc_send('PONG :'.substr($this->irc->con['buffer']['all'], 6));

					// If this is the first time we have reached this point, then JOIN the channel
					if ($firstTime) {
						$this->irc_send('JOIN '. $this->irc->channel);

						// The next time we get here, it will NOT be the firstTime
						$firstTime = false;
					}

					// Make sure that we have a NEW line of chats to analyse. If we don't,
					// there is no need to parse the data again
				}
				else if ($old_buffer !== $this->irc->con['buffer']['all']) {
					if (strpos($this->irc->con['buffer']['all'], ':End of /NAMES list.') !== false) {
						$aseco->console('[RaspIRC] ...successfully connected to IRC!');
						break;
					}
				}

				$old_buffer = $this->irc->con['buffer']['all'];
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onMainLoop ($aseco, $null) {
		global $aseco;

		//executeCallbacks();
		$srch = '[';
		if (!$this->irc->con['socket']) {
			sleep(2);
			$null = '';
			$this->onSync($aseco, $null);
			return;
		}

		if (!feof($this->irc->con['socket'])) {
			$this->irc_getMessages();
			if (!empty($this->irc->ircmsgs)) {
				foreach ($this->irc->ircmsgs as $msg) {
					if (strstr($msg, $srch)) {
						$this->irc_send($this->irc_prep('', $aseco->stripColors($msg)));
					}
				}
				$this->irc->ircmsgs = array();
			}
			$this->irc_send($this->irc_prep2('', '-'));

			if ($buffer = fgets($this->irc->con['socket'], 4096)) {
				$buffer = trim($buffer);
				$name_buffer = explode(' ', $buffer, 4);
				$msg_buffer = explode(' ', str_replace('\'', '', $buffer), 4);
				$player = substr($name_buffer[0], 1, strpos($name_buffer['0'], '!')-1);
				$text = substr($msg_buffer[3], 1);
				if ($player !== $this->irc->nick && strlen($player) > 0) {
					$player = '$f00'.$player;
					$msg = '$0f0-IRC-$fff['.$player.'$fff] '.$text;
					$aseco->sendChatMessage($msg);
				}
			}
		}
		else {
			sleep(2);
			$null = '';
			$this->onSync($aseco, $null);
			return;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function irc_getMessages () {
		global $aseco;

		$lines = array();
		try {
			$lines = $aseco->client->query('GetChatLines', 50, 0);
		}
		catch (Exception $exception) {
			$aseco->console('[RaspIRC] Error ['. $exception->getCode() .'] GetChatLines - '. $exception->getMessage());
		}

		if (count($lines) > 0) {
			foreach ($lines as $msg) {
				if (!in_array($msg, $this->irc->linesbuffer)) {
					if (!strstr($msg, '-IRC-')) {
						$this->irc->ircmsgs[] = $msg;
					}
					if (count($this->irc->linesbuffer) >= 100) {
						$drop = array_shift($this->irc->linesbuffer);
						$this->irc->linesbuffer[] = $msg;
					}
					else {
						$this->irc->linesbuffer[] = $msg;
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function irc_send ($command) {
		global $aseco;

		if (!$this->irc->con['socket']) {
			sleep(2);
			$null = '';
			$this->onSync($aseco, $null);
			return;
		}
		fwrite($this->irc->con['socket'], $command . CRLF);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function irc_prep ($type, $message) {
		global $aseco;

		return ('PRIVMSG '. $this->irc->channel .' :'. $type.$message);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function irc_prep2 ($type, $message) {
		global $aseco;

		return ('PRIVMSG '. $this->irc->nick .', '. $this->irc->channel .' :'. $type.$message);
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
			$aseco->console('[RaspIrc] Loading config file ['. $config_file .']');
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
				$this->autosave_matchsettings	= $xml['RASP']['AUTOSAVE_MATCHSETTINGS'][0];
				$this->feature_votes		= $aseco->string2bool($xml['RASP']['FEATURE_VOTES'][0]);
				$this->prune_records_times	= $aseco->string2bool($xml['RASP']['PRUNE_RECORDS_TIMES'][0]);


				/***************************** PERFORMANCE VARIABLES ***************************/
				if (isset($xml['RASP']['MAX_AVG'][0])) {
					$this->maxavg = $xml['RASP']['MAX_AVG'][0];
				}
				else {
					$this->maxavg = 10;
				}


				/***************************** JUKEBOX VARIABLES *******************************/
				$this->mxvoteratio		= $xml['RASP']['MX_VOTERATIO'][0];
				$this->mxdir			= $xml['RASP']['MX_DIR'][0];
				$this->mxtmpdir			= $xml['RASP']['MX_TMPDIR'][0];

				$this->jukebox			= array();
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
