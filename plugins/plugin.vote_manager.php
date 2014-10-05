<?php
/*
 * Plugin: Vote Manager
 * ~~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Vote-Manager.php
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Version:		1.0.0
 * Date:		2014-10-03
 * Copyright:		2012 - 2014 by undef.de
 * System:		UASECO/1.0.0+
 * Game:		ManiaPlanet Trackmania2 (TM2)
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
 *  - plugins/plugin.jfreu_max.php
 *  - plugins/plugin.rasp_votes.php
 *  - plugins/plugin.rasp_jukebox.php
 *
 */

/* The following manialink id's are used in this plugin (the 921 part of id can be changed on trouble):
 *
 * ManialinkID's
 * ~~~~~~~~~~~~~
 * 92200		id for manialink Widget
 * 92201		id for manialink CountdownWidget
 * 92202		id for manialink VoteStatistics
 * 92203		id for manialink HelpWindow
 * 92204		id for manialink PlayerVoteMarker
 *
 * ActionID's
 * ~~~~~~~~~~
 * 92200		id for action close Window
 * 92201		id for action YES
 * 92202		id for action NO
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginVoteManager();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginVoteManager extends Plugin {
	public $config;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides a Widget and handles Skip/Restart votings.');

		$this->addDependence('PluginRaspVotes',			Dependence::DISALLOWED,	null, null);
		$this->addDependence('PluginJfreuMax',			Dependence::DISALLOWED,	null, null);
		$this->addDependence('PluginRaspJukebox',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onPlayerChat',			'onPlayerChat');
		$this->registerEvent('onEverySecond',			'onEverySecond');
		$this->registerEvent('onBeginMap1',			'onBeginMap1');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap1',			'onEndMap1');
		$this->registerEvent('onShutdown',			'onShutdown');

		$this->registerChatCommand('votemanager',		'chat_votemanager',	'Command for MasterAdins',			Player::MASTERADMINS);
		$this->registerChatCommand('helpvote',			'chat_helpvote',	'Displays info about the chat-based votes',	Player::PLAYERS);
		$this->registerChatCommand('res',			'chat_restart',		'Start a vote to restart the current Map',	Player::PLAYERS);
		$this->registerChatCommand('restart',			'chat_restart',		'Start a vote to restart the current Map',	Player::PLAYERS);
		$this->registerChatCommand('skip',			'chat_skip',		'Start a vote to skip the current Map',		Player::PLAYERS);
		$this->registerChatCommand('next',			'chat_skip',		'Start a vote to skip the current Map',		Player::PLAYERS);
		$this->registerChatCommand('yes',			'chat_yes',		'Accept the current vote',			Player::PLAYERS);
		$this->registerChatCommand('no',			'chat_no',		'Reject the current vote',			Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '1.0.0';
		if ( defined('UASECO_VERSION') ) {
			$version = str_replace(
				array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i'),
				array('.1','.2','.3','.4','.5','.6','.7','.8','.9'),
				UASECO_VERSION
			);
			if ( version_compare($version, $uaseco_min_version, '<') ) {
				trigger_error('[VoteManager] Not supported USAECO version ('. $version .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[VoteManager] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}

		if (!$this->config = $aseco->parser->xmlToArray('config/vote_manager.xml')) {
			trigger_error('[VoteManager] Could not read/parse config file "config/vote_manager.xml"!', E_USER_ERROR);
		}

		$this->config = $this->config['VOTE_MANAGER'];

		$this->config['ManialinkId'] = '922';

		// Check/Setup the limits
		if ($this->config['VOTING'][0]['RATIO'][0] < 0.2) {
			$this->config['VOTING'][0]['RATIO'][0] = 0.2;
		}

		$this->config['VOTING'][0]['TIMEOUT_LIMIT'][0]			= (int)$this->config['VOTING'][0]['TIMEOUT_LIMIT'][0];
		$this->config['VOTING'][0]['COUNTDOWN'][0]			= (int)$this->config['VOTING'][0]['COUNTDOWN'][0];
		$this->config['VOTING'][0]['MAX_VOTES'][0]			= (int)$this->config['VOTING'][0]['MAX_VOTES'][0];
		$this->config['VOTING'][0]['MAX_RESTARTS'][0]			= (int)$this->config['VOTING'][0]['MAX_RESTARTS'][0];
		$this->config['DEDICATED_SERVER'][0]['DISABLE_CALLVOTES'][0]	= ((strtoupper($this->config['DEDICATED_SERVER'][0]['DISABLE_CALLVOTES'][0]) == 'TRUE') ? true : false);
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0]	= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0]	= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0]	= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0];
		$this->config['NUMBER_FORMAT'][0]				= strtolower($this->config['NUMBER_FORMAT'][0]);
		$this->config['MODE'][0]					= strtolower($this->config['MODE'][0]);


		// Preset defaults
		$this->config['TimeAttackTimelimit']			= -1;
		$this->config['RunningVote']['Votes']['Restart']	= 0;
		$this->config['RunningVote']['Votes']['Skip']		= 0;
		$this->config['RunningVote']				= $this->cleanupCurrentVote();
		$this->config['Cache']['Todo']['onEndMap']		= false;
		$this->config['Cache']['LastMap']['Uid']		= false;
		$this->config['Cache']['LastMap']['Runs']		= 0;
		$this->config['Cache']['IgnoreLogin']			= array();


		// Define the formats for number_format()
		$this->config['NumberFormat'] = array(
			'english'	=> array(
				'decimal_sep'	=> '.',
				'thousands_sep'	=> ',',
			),
			'german'	=> array(
				'decimal_sep'	=> ',',
				'thousands_sep'	=> '.',
			),
			'french'	=> array(
				'decimal_sep'	=> ',',
				'thousands_sep'	=> ' ',
			),
		);


		// Working on the <ignore_list>
		foreach ($this->config['IGNORE_LIST'][0]['LOGIN'] as $login) {
			if ( !empty($login) ) {
				$this->config['Cache']['IgnoreLogin'][] = trim($login);
			}
		}
		unset($this->config['IGNORE_LIST']);


		// Store the original CallVoteRatios and CallVoteTimeOut for restoring at onShutdown
		$this->config['OriginalCallVoteRatios'] = $aseco->client->query('GetCallVoteRatios');
		$GetCallVoteTimeOut = $aseco->client->query('GetCallVoteTimeOut');
		$this->config['OriginalCallVoteTimeOut'] = $GetCallVoteTimeOut['CurrentValue'];
		unset($GetCallVoteTimeOut);


		// Disable the CallVotes 'RestartMap' and 'NextMap'
		$callvotes = array();
		$callvotes[] = array(
			'Command'	=> 'RestartMap',
			'Ratio'		=> (float)-1,
		);
		$callvotes[] = array(
			'Command'	=> 'NextMap',
			'Ratio'		=> (float)-1,
		);

		// Setup the configured CallVotes
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'Kick',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'Ban',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0]) ) {
			$callvotes[] = array(
				'Command'	=> '*',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0],
			);
		}
		$aseco->client->query('SetCallVoteRatios', $callvotes);

		if ($this->config['DEDICATED_SERVER'][0]['DISABLE_CALLVOTES'][0] == true) {
			$aseco->client->query('SetCallVoteTimeOut', (int)0);
		}

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {

		// Restore the original CallVoteRatios and CallVoteTimeOut
		if ( isset($this->config['OriginalCallVoteRatios']) ) {
			$aseco->client->query('SetCallVoteRatios', $this->config['OriginalCallVoteRatios']);
		}
		if ( isset($this->config['OriginalCallVoteTimeOut']) ) {
			$aseco->client->query('SetCallVoteTimeOut', $this->config['OriginalCallVoteTimeOut']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// If id = 0, bail out immediately
		if ($answer[2] == 0) {
			return;
		}

		if ($answer[2] == (int)$this->config['ManialinkId'] .'00') {
			$xml = '<manialink id="'. $this->config['ManialinkId'] .'03"></manialink>';	// HelpWindow
			$aseco->sendManialink($xml, $answer[1], 0, false);
		}
		else if ($answer[2] == (int)$this->config['ManialinkId'] .'01') {
			$aseco->releaseChatCommand('/yes', $answer[1]);
		}
		else if ($answer[2] == (int)$this->config['ManialinkId'] .'02') {
			$aseco->releaseChatCommand('/no', $answer[1]);
		}
		else if ($answer[2] == 25) {
			// Admin has clicked "Pass" for the current Vote
			$this->handleAdminAction('pass');
		}
		else if ($answer[2] == 26) {
			// Admin has clicked "Cancel" for the current Vote
			$this->handleAdminAction('cancel');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleAdminAction ($action) {
		global $aseco;

		if ($action == 'pass') {

			// Admin "Pass" for the current Vote
			if ($this->config['RunningVote']['Active'] == true) {
				if ($this->config['RunningVote']['Mode'] == 'Restart') {
					// Restart passed, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_SUCCESS'][0]);

					// Setup restart
					$this->config['Cache']['Todo']['onEndMap'] = $this->config['RunningVote']['Mode'];

					// Add to Jukebox for replay
					if ($this->config['MODE'][0] == 'replay') {
						$this->handleTodo('Replay');
					}

					// Release the event
					$aseco->releaseEvent('onVotingRestartMap', null);
				}
				else if ($this->config['RunningVote']['Mode'] == 'Skip') {
					// Skip passed, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_SUCCESS'][0]);

					// Skip now
					$this->handleTodo('Skip');
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="'. $this->config['ManialinkId'] .'00"></manialink>';	// Widget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'01"></manialink>';	// CountdownWidget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'02"></manialink>';	// VoteStatistics
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'04"></manialink>';	// PlayerVoteMarker
				$aseco->sendManialink($xml, false, 0, false);
			}

		}
		else if ($action == 'cancel') {

			// Admin "Cancel" for the current Vote
			if ($this->config['RunningVote']['Active'] == true) {
				if ($this->config['RunningVote']['Mode'] == 'Restart') {
					// Restart did not pass, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_FAILED'][0]);
				}
				else if ($this->config['RunningVote']['Mode'] == 'Skip') {
					// Skip did not pass, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_FAILED'][0]);
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="'. $this->config['ManialinkId'] .'00"></manialink>';	// Widget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'01"></manialink>';	// CountdownWidget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'02"></manialink>';	// VoteStatistics
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'04"></manialink>';	// PlayerVoteMarker
				$aseco->sendManialink($xml, false, 0, false);
			}

		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {

		if ($this->config['RunningVote']['Active'] == true) {
			if ($this->config['RunningVote']['Countdown'] > 0) {

				// Countdown
				$this->config['RunningVote']['Countdown'] --;

				// Check if all Players has voted and end voting
				$stop = true;
				foreach ($aseco->server->players->player_list as $player) {
					if ( (!isset($this->config['RunningVote']['Votes']['Yes'][$player->login])) && (!isset($this->config['RunningVote']['Votes']['No'][$player->login])) ) {
						$stop = false;
					}
				}
				unset($player);
				if ($stop == true) {
					$this->config['RunningVote']['Countdown'] = 0;
				}

				// Add vote statistics
				$xml = $this->buildWidgetVoteStatistics();

				$aseco->sendManialink($xml, implode(',', $this->config['RunningVote']['Players']), 0, false);
			}
			else if ( ($this->config['RunningVote']['Countdown'] > -3) && ($this->config['RunningVote']['Countdown'] <= 0) ) {

				// Countdown
				$this->config['RunningVote']['Countdown'] --;

				// Add vote statistics
				$xml = $this->buildWidgetCountdownFinished();
				$xml .= $this->buildWidgetVoteStatistics();

				$aseco->sendManialink($xml, implode(',', $this->config['RunningVote']['Players']), 0, false);
			}
			else {
				// Turn off current running Vote
				$this->config['RunningVote']['Active'] = false;

				// Find out if the vote passed
				$count_yes = count($this->config['RunningVote']['Votes']['Yes']);
				$count_no = count($this->config['RunningVote']['Votes']['No']);
				$totalvotes = $count_yes + $count_no;
				if ($totalvotes == 0) {
					$totalvotes = 0.0001;
				}
				if ( ($count_yes / $totalvotes * 100) >= ($this->config['VOTING'][0]['RATIO'][0] * 100) ) {
					if ($this->config['RunningVote']['Mode'] == 'Restart') {
						// Restart passed, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_SUCCESS'][0]);

						// Setup restart
						$this->config['Cache']['Todo']['onEndMap'] = $this->config['RunningVote']['Mode'];

						// Add to Jukebox for replay
						if ($this->config['MODE'][0] == 'replay') {
							$this->handleTodo('Replay');
						}

						// Release the event
						$aseco->releaseEvent('onVotingRestartMap', null);
					}
					else if ($this->config['RunningVote']['Mode'] == 'Skip') {
						// Skip passed, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_SUCCESS'][0]);

						// Skip now
						$this->handleTodo('Skip');
					}
				}
				else {
					if ($this->config['RunningVote']['Mode'] == 'Restart') {
						// Restart did not pass, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_FAILED'][0]);
					}
					else if ($this->config['RunningVote']['Mode'] == 'Skip') {
						// Skip did not pass, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_FAILED'][0]);
					}
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="'. $this->config['ManialinkId'] .'00"></manialink>';	// Widget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'01"></manialink>';	// CountdownWidget
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'02"></manialink>';	// VoteStatistics
				$xml .= '<manialink id="'. $this->config['ManialinkId'] .'04"></manialink>';	// PlayerVoteMarker
				$aseco->sendManialink($xml, false, 0, false);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		if ( $aseco->isAnyAdminL($chat[1]) ) {
			if (strtolower($chat[2]) == '/admin pass') {
				// Admin has used "/admin pass" for the current Vote
				$this->handleAdminAction('pass');
			}
			else if (strtolower($chat[2]) == '/admin cancel') {
				// Admin has used "/admin cancel" for the current Vote
				$this->handleAdminAction('cancel');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Send info message "Callvote disabled"
		$aseco->sendChatMessage($this->config['MESSAGES'][0]['CALLVOTE_DISABLED'][0], $player->login);

		// Preload the Thumbs-Images
		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'04">';
		$xml .= '<quad posn="128 128 1.1" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_UP'][0] .'"/>';
		$xml .= '<quad posn="128 128 1.1" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_DOWN'][0] .'"/>';
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, $player->login, 0, false);

		// Check for current vote and show it
		if ($this->config['RunningVote']['Active'] == true) {
			$this->config['RunningVote']['Players'][] = $player->login;
			$this->sendOutVote($player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map_item) {

		// Reset all before votings
		$this->config['RunningVote'] = $this->cleanupCurrentVote();
		$this->config['RunningVote']['Votes']['Restart']	= 0;
		$this->config['RunningVote']['Votes']['Skip']		= 0;

		// Reset
		$this->config['Cache']['Todo']['onEndMap'] = false;

		// Store the Timelimit at TA
		if ($aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK) {
			$this->config['TimeAttackTimelimit'] = time() + $aseco->server->gameinfo->time_attack['TimeLimit'];
		}
		else {
			$this->config['TimeAttackTimelimit'] = -1;
		}

		// Find Uid and count the restarts or reset
		if ($this->config['Cache']['LastMap']['Uid'] == $aseco->server->maps->current->uid) {
			// Count the restarts
			$this->config['Cache']['LastMap']['Runs'] ++;
		}
		else {
			// Reset
			$this->config['Cache']['LastMap']['Uid'] = $aseco->server->maps->current->uid;
			$this->config['Cache']['LastMap']['Runs'] = 0;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map_item) {

		// -1 because the Admin has restarted
		$this->config['Cache']['LastMap']['Runs'] --;
		if ($this->config['Cache']['LastMap']['Runs'] < 0) {
			$this->config['Cache']['LastMap']['Runs'] = 0;
		}

		// Emulate event onBeginMap1
		$this->onBeginMap1($aseco, $map_item);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $data) {

		// Test for "Restart Map (with ChatTime)"
		if ($aseco->restarting == 2) {
			// Reset Todo's
			$this->config['Cache']['Todo']['onEndMap'] = false;

			// -1 because the Admin has restarted
			$this->config['Cache']['LastMap']['Runs'] --;
			if ($this->config['Cache']['LastMap']['Runs'] < 0) {
				$this->config['Cache']['LastMap']['Runs'] = 0;
			}
		}

		// Hide all Widgets from all Players
		$xml = '<manialink id="'. $this->config['ManialinkId'] .'00"></manialink>';	// Widget
		$xml .= '<manialink id="'. $this->config['ManialinkId'] .'01"></manialink>';	// CountdownWidget
		$xml .= '<manialink id="'. $this->config['ManialinkId'] .'02"></manialink>';	// VoteStatistics
		$xml .= '<manialink id="'. $this->config['ManialinkId'] .'03"></manialink>';	// HelpWindow
		$xml .= '<manialink id="'. $this->config['ManialinkId'] .'04"></manialink>';	// PlayerVoteMarker
		$aseco->sendManialink($xml, false, 0, false);

		// Reset all before votings if a running vote was interrupted (by Admin skip/restart)
		if ($this->config['RunningVote']['Active'] == true) {
			$this->config['RunningVote'] = $this->cleanupCurrentVote();
			$this->config['RunningVote']['Votes']['Restart']	= 0;
			$this->config['RunningVote']['Votes']['Skip']	= 0;
			$this->config['TimeAttackTimelimit']		= -1;
		}

		if ($this->config['Cache']['Todo']['onEndMap'] == 'Restart') {
			// Restart if it is the whished mode
			if ($this->config['MODE'][0] == 'restart') {
				$this->handleTodo('Restart');
			}

			// Set to 'done'
			$this->config['Cache']['Todo']['onEndMap'] = false;

			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_DONE'][0]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleTodo ($mode) {
		global $aseco;

		if ($mode == 'Restart') {
			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				// Don't clear scores if in Cup mode
				$aseco->client->query('RestartMap', true);
			}
			else {
				$aseco->client->query('RestartMap');
			}
		}
		else if ($mode == 'Replay') {
			// prepend current map to start of jukebox
			$uid = $aseco->server->maps->current->uid;
			$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $aseco->server->maps->current->filename;
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $aseco->server->maps->current->name;
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $aseco->server->maps->current->environment;
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $this->config['RunningVote']['StartedFrom']['Login'];
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $this->config['RunningVote']['StartedFrom']['Nickname'];
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'ReplayByVoteManager';		// Prevent from counting = $replays_limit
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
			$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
			$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
		}
		else if ($mode == 'Skip') {
			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				// Don't clear scores if in Cup mode
				$aseco->client->query('NextMap', true);
			}
			else {
				$aseco->client->query('NextMap');
			}
		}
		$aseco->console('[VoteManager] "'. $mode .'" the current Map ['. $aseco->stripColors($aseco->server->maps->current->name) .']');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setupNewVote ($mode, $login, $nickname, $question) {
		global $aseco;

		if ($this->config['Cache']['Todo']['onEndMap'] == 'Restart') {
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_RESTART_CANCEL'][0], $login);
			return;
		}

		if ( ($this->config['VOTING'][0]['MAX_VOTES'][0] == 0) || ( ($this->config['VOTING'][0]['MAX_VOTES'][0] > 0) && ($this->config['RunningVote']['Votes'][$mode] < $this->config['VOTING'][0]['MAX_VOTES'][0]) ) ) {
			// Who started the vote and what kind of vote
			$this->config['RunningVote']['StartedFrom'] = array('Login' => $login, 'Nickname' => $nickname);
			$this->config['RunningVote']['Question'] = $question;
			$this->config['RunningVote']['Mode'] = $mode;

			// Add all current connected Players
			foreach ($aseco->server->players->player_list as $player) {
				$this->config['RunningVote']['Players'][] = $player->login;
			}
			unset($player);

			// Set Timeount and Activate the Voting
			$this->config['RunningVote']['Countdown'] = $this->config['VOTING'][0]['COUNTDOWN'][0];
			$this->config['RunningVote']['Active'] = true;
			$this->config['RunningVote']['Votes']['Yes'][$login] = true;

			// Count up the current $mode Vote
			$this->config['RunningVote']['Votes'][$mode] ++;

			$this->sendOutVote(false);

			// Mark the Player Vote
			$this->buildPlayerVoteMarker($login, 'Yes');
		}
		else {
			// Limit reached
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_LIMIT_REACHED'][0],
				$mode,
				$this->config['VOTING'][0]['MAX_VOTES'][0],
				($this->config['VOTING'][0]['MAX_VOTES'][0] == 1) ? '' : 's'
			);
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendOutVote ($login = false) {
		global $aseco;

		$xml = $this->buildWidgetCountdown();
		$xml .= str_replace(
			array('%USERNAME%', '%QUESTION%'),
			array($this->handleSpecialChars($this->config['RunningVote']['StartedFrom']['Nickname']), $this->config['RunningVote']['Question']),
			$this->buildWidget()
		);

		if ($login == false) {
			// Send to all Players
			$aseco->sendManialink($xml, implode(',', $this->config['RunningVote']['Players']), 0, false);
		}
		else {
			// Send only to given
			$aseco->sendManialink($xml, $login, 0, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWidget () {

		// Placeholder: %USERNAME%, %QUESTION%
		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'00">';
		$xml .= '<frame posn="'. $this->config['WIDGET'][0]['POS_X'][0] .' '. $this->config['WIDGET'][0]['POS_Y'][0] .' 0">';
		$xml .= '<quad posn="0 0 0" sizen="41.9 11" bgcolor="'. $this->config['WIDGET'][0]['BACKGROUND_COLOR'][0] .'"/>';
		$xml .= '<quad posn="-0.2 0.3 0.001" sizen="42.3 11.6" style="'. $this->config['WIDGET'][0]['BORDER_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BORDER_SUBSTYLE'][0] .'"/>';
		$xml .= '<quad posn="0 0 0.002" sizen="42.3 11.6" style="'. $this->config['WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';

		// Icon and Title
		$xml .= '<quad posn="0.4 -0.36 0.003" sizen="41.1 2" style="'. $this->config['WIDGET'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		$xml .= '<quad posn="0.6 0 0.004" sizen="2.5 2.5" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="3.2 -0.65 0.004" sizen="37 0" textsize="1" text="'. $this->config['WIDGET'][0]['TITLE'][0] .'"/>';
		$xml .= '<format textsize="1" textcolor="FFF"/>';

		// BEGIN: Question
		$xml .= '<frame posn="0 -3 0">';
		$xml .= '<label posn="21 0 0.005" sizen="50 2.3" halign="center" textsize="3" scale="0.8" text="$S%USERNAME%$Z%QUESTION%"/>';
		$xml .= '</frame>';
		// END: Question

		// BEGIN: Ratio marker and Statistic-Background
		$xml .= '<frame posn="8.5 -7.75 0">';
		$xml .= '<quad posn="0 0 0.005" sizen="25 2.45" bgcolor="0003"/>';
		$xml .= '<quad posn="'. (0.25 * ($this->config['VOTING'][0]['RATIO'][0] * 100)) .' 0.3 0.007" sizen="0.1 3.05" bgcolor="000A"/>';
		$xml .= '<quad posn="'. (0.25 * ($this->config['VOTING'][0]['RATIO'][0] * 100) + 0.01) .' 0.3 0.007" sizen="0.1 3.05" bgcolor="FFFA"/>';
		$xml .= '</frame>';
		// END: Ratio marker

		// BEGIN: YES Button
		$xml .= '<frame posn="0.8 -7.5 0">';
		$xml .= '<quad posn="0 0 0.005" sizen="7.1 2.9" action="'. $this->config['ManialinkId'] .'01" actionkey="1" style="'. $this->config['WIDGET'][0]['BUTTON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BUTTON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="3.55 -0.9 0.006" sizen="7.1 0" halign="center" textsize="1" scale="0.8" text="$5B0$OYES / F5"/>';
		$xml .= '</frame>';
		// END: YES Button

		// BEGIN: NO Button
		$xml .= '<frame posn="34.1 -7.5 0">';
		$xml .= '<quad posn="0 0 0.005" sizen="7.1 2.9" action="'. $this->config['ManialinkId'] .'02" actionkey="2" style="'. $this->config['WIDGET'][0]['BUTTON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BUTTON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="3.55 -0.9 0.006" sizen="7.1 0" halign="center" textsize="1" scale="0.8" text="$C10$ONO / F6"/>';
		$xml .= '</frame>';
		// END: NO Button

		$xml .= '</frame>';
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWidgetCountdown () {
		global $aseco;

		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'01" name="VoteManagerCountdown">';
		$xml .= '<label posn="'. ($this->config['WIDGET'][0]['POS_X'][0] + 21) .' '. ($this->config['WIDGET'][0]['POS_Y'][0] - 5.5) .' 1.01" sizen="40 1.5" halign="center" textsize="1" textcolor="FFFF" text="" id="VoteManagerLabelCountdown"/>';

		$message = $aseco->formatText($this->config['MESSAGES'][0]['TIME_REMAINING'][0], '%1');
$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Vote-Manager
 * Widget:	Countdown
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare LabelCountdown	<=> (Page.GetFirstChild("VoteManagerLabelCountdown") as CMlLabel);
	declare PrevTime	= CurrentLocalDateText;

	declare Countdown	= {$this->config['RunningVote']['Countdown']};
	declare MessageCount	= "{$message}";
	declare MessageFinish	= "{$this->config['MESSAGES'][0]['VOTE_FINISHED'][0]}";

	while (True) {
		yield;

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			Countdown = Countdown - 1;
			if (Countdown < 0) {
				LabelCountdown.SetText(MessageFinish);
				break;
			}
			LabelCountdown.SetText(TextLib::Compose(MessageCount, TextLib::ToText(Countdown)));
		}
	}
}
--></script>
EOL;

		$xml .= $maniascript;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWidgetCountdownFinished () {
		global $aseco;

		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'01" name="VoteManagerCountdown">';
		$xml .= '<label posn="'. ($this->config['WIDGET'][0]['POS_X'][0] + 21) .' '. ($this->config['WIDGET'][0]['POS_Y'][0] - 5.5) .' 1.01" sizen="40 1.5" halign="center" textsize="1" textcolor="FFFF" text="'. $this->config['MESSAGES'][0]['VOTE_FINISHED'][0] .'"/>';
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPlayerVoteMarker ($login, $vote) {
		global $aseco;

		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'04">';
		if ($vote == 'Yes') {
			$xml .= '<quad posn="'. ($this->config['WIDGET'][0]['POS_X'][0] - 1) .' '. ($this->config['WIDGET'][0]['POS_Y'][0] - 7.3) .' 1.1" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_UP'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="'. ($this->config['WIDGET'][0]['POS_X'][0] + 39.9) .' '. ($this->config['WIDGET'][0]['POS_Y'][0] - 7.3) .' 1.1" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_DOWN'][0] .'"/>';
		}
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWidgetVoteStatistics () {

		$count_yes = count($this->config['RunningVote']['Votes']['Yes']);
		$count_no = count($this->config['RunningVote']['Votes']['No']);
		$totalvotes = $count_yes + $count_no;
		if ($totalvotes == 0) {
			$totalvotes = 0.0001;
		}

		$xml  = '<manialink id="'. $this->config['ManialinkId'] .'02">';
		$xml .= '<frame posn="'. ($this->config['WIDGET'][0]['POS_X'][0] + 8.5) .' '. ($this->config['WIDGET'][0]['POS_Y'][0] - 7.75) .' 0">';

		$percent_yes = ($count_yes / $totalvotes * 100);
		$xml .= '<quad posn="0 0 0.006" sizen="'. (0.25 * $percent_yes).' 1.225" bgcolor="390F"/>';
		$xml .= '<label posn="2.1 -0.05 0.007" sizen="2.4 1.225" halign="right" textsize="1" scale="0.8" text="$000'. $count_yes .'"/>';
		$xml .= '<label posn="6.4 -0.05 0.007" sizen="5 1.225" halign="right" textsize="1" scale="0.8" text="$000'. number_format($percent_yes, 2, $this->config['NumberFormat'][$this->config['NUMBER_FORMAT'][0]]['decimal_sep'], $this->config['NumberFormat'][$this->config['NUMBER_FORMAT'][0]]['thousands_sep']) .'%"/>';

		$percent_no = ($count_no / $totalvotes * 100);
		$xml .= '<quad posn="0 -1.225 0.006" sizen="'. (0.25 * $percent_no).' 1.225" bgcolor="D02F"/>';
		$xml .= '<label posn="2.1 -1.275 0.007" sizen="2.4 1.225" halign="right" textsize="1" scale="0.8" text="$000'. $count_no .'"/>';
		$xml .= '<label posn="6.4 -1.275 0.007" sizen="5 1.225" halign="right" textsize="1" scale="0.8" text="$000'. number_format($percent_no, 2, $this->config['NumberFormat'][$this->config['NUMBER_FORMAT'][0]]['decimal_sep'], $this->config['NumberFormat'][$this->config['NUMBER_FORMAT'][0]]['thousands_sep']) .'%"/>';

		$xml .= '</frame>';
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildHelpWindow ($login) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['ManialinkId'] .'03">';
		$xml .= '<frame posn="-40.8 30.55 18.50">';	// BEGIN: Window Frame
		$xml .= '<quad posn="-0.2 0.2 0.01" sizen="81.8 59" style="Bgs1InRace" substyle="BgTitle2"/>';
		$xml .= '<quad posn="1.8 -4.1 0.02" sizen="77.7 49.9" bgcolor="0018"/>';

		// Header Line
		$xml .= '<quad posn="-0.6 0.6 0.02" sizen="82.6 6" style="Bgs1InRace" substyle="BgTitle3_3"/>';
		$xml .= '<quad posn="-0.6 0.6 0.03" sizen="82.6 6" style="Bgs1InRace" substyle="BgTitle3_3"/>';

		// Title
		$xml .= '<quad posn="1.8 -0.8 0.04" sizen="3.2 3.2" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="5.5 -1.7 0.04" sizen="75.4 0" textsize="2" scale="0.9" textcolor="000F" text="Help for Vote Manager"/>';

		// Close Button
		$xml .= '<frame posn="76.7 -0.15 0.05">';
		$xml .= '<quad posn="0 0 0.01" sizen="4.5 4.5" action="'. $this->config['ManialinkId'] .'00" style="Icons64x64_1" substyle="ArrowUp"/>';
		$xml .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$xml .= '<quad posn="0.7 -0.7 0.03" sizen="3.1 3.1" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '</frame>';

		// About
		$xml .= '<label posn="6 -55.8 0.04" sizen="13 2" halign="center" valign="center" textsize="1" scale="0.7" url="http://www.undef.name/XAseco2/Vote-Manager.php" focusareacolor1="0000" focusareacolor2="FFF5" textcolor="000F" text="VOTE-MANAGER/'. $this->getVersion() .'"/>';

		// Set the content
		$xml .= '<frame posn="3 -6 0.01">';

		$xml .= '<label posn="0 0 0.01" sizen="75 0" textsize="1" textcolor="FF0F" autonewline="1" text="With this Plugin you can start a Voting for restarting or skipping the current Map, please use one of the described commands below for a new vote.'. LF.LF .'A restart vote for a Map did not restart as soon as the vote passed, the restart is delayed until the end of the Race. If a restart vote passed and a Player want to start a skip vote, then this is rejected."/>';

		// Command "/helpvote"
		$xml .= '<label posn="0 -10 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/helpvote"/>';
		$xml .= '<label posn="19 -10 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Display this help"/>';

		// Command "/restart" or "/res"
		$xml .= '<label posn="0 -12 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/restart $FF0or$FFF /res"/>';
		$xml .= '<label posn="19 -12 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Start a vote to restart the current Map"/>';

		// Command "/skip" or "/next"
		$xml .= '<label posn="0 -14 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/skip $FF0or$FFF /next"/>';
		$xml .= '<label posn="19 -14 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Start a vote to skip the current Map"/>';

		// Command "/yes" or F5
		$xml .= '<label posn="0 -16 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/yes $FF0or$FFF F5"/>';
		$xml .= '<label posn="19 -16 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Accept the current vote"/>';

		// Command "/no" or F6
		$xml .= '<label posn="0 -18 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/no $FF0or$FFF F6"/>';
		$xml .= '<label posn="19 -18 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Reject the current vote"/>';

		// Command "/votemanager reload"
		$xml .= '<label posn="0 -22 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/votemanager reload"/>';
		$xml .= '<label posn="19 -22 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Reload the vote_manager.xml (for MasterAdmins only)"/>';

		$xml .= '</frame>';

		$xml .= '</frame>';	// Window
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function cleanupCurrentVote () {

		$preset = array();
		$preset['Active']		= false;
		$preset['Players']		= array();
		$preset['StartedFrom']		= array();
		$preset['Countdown']		= -3;
		$preset['Question']		= '';
		$preset['Mode']			= false;
		$preset['Votes']['Yes']		= array();
		$preset['Votes']['No']		= array();
		$preset['Votes']['Restart']	= $this->config['RunningVote']['Votes']['Restart'];	// Are reseted at onBeginMap2
		$preset['Votes']['Skip']	= $this->config['RunningVote']['Votes']['Skip'];		// Are reseted at onBeginMap2

		return $preset;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleVote ($login, $type) {
		global $aseco;

		if ( ($this->config['RunningVote']['Active'] == true) && ($this->config['RunningVote']['Countdown'] > 0) ) {
			// Do not allow to change the own started vote
			if ($this->config['RunningVote']['StartedFrom']['Login'] == $login) {
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_NO_OWN_VOTE'][0], $login);
				return;
			}

			// Check if Player have already voted before
			$not_voted = (($type == 'Yes') ? 'No' : 'Yes');
			if ( isset($this->config['RunningVote']['Votes'][$type][$login]) ) {
				// Player voted the same
				return;
			}
			else if ( isset($this->config['RunningVote']['Votes'][$not_voted][$login]) ) {
				// Player voted already but change his mind, so unset the old vote
				unset($this->config['RunningVote']['Votes'][$not_voted][$login]);
			}

			// Store the current vote
			$this->config['RunningVote']['Votes'][$type][$login] = true;

			// Mark the Player Vote
			$this->buildPlayerVoteMarker($login, $type);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleSpecialChars ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $S (shadow)
		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[SHWILON]/i', '', $string);


		// Convert &
		// Convert "
		// Convert '
		// Convert >
		// Convert <
		$string = str_replace(
				array(
					'&',
					'"',
					"'",
					'>',
					'<'
				),
				array(
					'&amp;',
					'&quot;',
					'&apos;',
					'&gt;',
					'&lt;'
				),
				$string
		);
		$string = $aseco->stripNewlines($string);

		return $aseco->validateUTF8String($string);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkVotePossibility ($type, $login = false) {
		global $aseco;

		if ( in_array($login, $this->config['Cache']['IgnoreLogin']) ) {
			// Login are on the <ignore_list>, skipping
			$aseco->console('[VoteManager] Skipping vote attempt from "'. $login .'" because player is in the <ignore_list>.');
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_IGNORED'][0]);
			$aseco->sendChatMessage($message, $login);
			return false;
		}

		if ( ($type == 'Restart') && ($this->config['Cache']['LastMap']['Runs'] >= $this->config['VOTING'][0]['MAX_RESTARTS'][0]) ) {
			// Max. restarts reached, cancel this request
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_RESTART_LIMITED'][0],
				$this->config['VOTING'][0]['MAX_RESTARTS'][0],
				(($this->config['VOTING'][0]['MAX_RESTARTS'][0] == 1) ? '' : 's')
			);
			$aseco->sendChatMessage($message, $login);
			return false;
		}

		if ( ($type == 'Skip') && ($this->config['Cache']['Todo']['onEndMap'] == 'Restart') ) {
			// There was a successfully restart vote before, cancel this skip request
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_CANCEL'][0], $login);
			return false;
		}

		if ($this->config['RunningVote']['Active'] == true) {
			// There is already a running vote, cancel this request
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_ALREADY_RUNNING'][0], $login);
			return false;
		}

		if ( ($aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK) && ($this->config['TimeAttackTimelimit'] != -1) ) {
			if ($this->config['TimeAttackTimelimit'] > (time() + $this->config['VOTING'][0]['TIMEOUT_LIMIT'][0])) {
				return true;
			}
			else {
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_TOO_LATE'][0], $login);
				return false;
			}
		}

		// All ok, go ahead
		return true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_votemanager ($aseco, $login, $chat_command, $chat_parameter) {

		// Init
		$message = false;

		// Check optional parameter
		if (strtoupper($chat_parameter) == 'RELOAD') {

			$aseco->console('[VoteManager] MasterAdmin '. $login .' reloads the configuration.');
			$this->onSync($aseco);
			$message = '{#admin}>> Reload of the configuration "config/vote_manager.xml" done.';
		}


		// Show message
		if ($message != false) {
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_helpvote ($aseco, $login, $chat_command, $chat_parameter) {
		$this->buildHelpWindow($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_restart ($aseco, $login, $chat_command, $chat_parameter) {

		if ( $this->checkVotePossibility('Restart', $login) ) {
			// Get Player object
			$player = $aseco->server->players->getPlayer($login);

			// Setup new vote
			$this->setupNewVote('Restart', $player->login, $player->nickname, $this->config['MESSAGES'][0]['QUESTION_RESTART'][0]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_skip ($aseco, $login, $chat_command, $chat_parameter) {

		if ( $this->checkVotePossibility('Skip', $login) ) {
			// Get Player object
			$player = $aseco->server->players->getPlayer($login);

			// Setup new vote
			$this->setupNewVote('Skip', $player->login, $player->nickname, $this->config['MESSAGES'][0]['QUESTION_SKIP'][0]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_yes ($aseco, $login, $chat_command, $chat_parameter) {

		if ($this->config['RunningVote']['Active'] == false) {
			// Send info message
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_NONE_RUNNING'][0], $login);
		}
		else {
			$this->handleVote($login, 'Yes');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_no ($aseco, $login, $chat_command, $chat_parameter) {

		if ($this->config['RunningVote']['Active'] == false) {
			// Send info message
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_NONE_RUNNING'][0], $login);
		}
		else {
			$this->handleVote($login, 'No');
		}
	}
}

?>
