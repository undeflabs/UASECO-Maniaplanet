<?php
/*
 * Plugin: Vote Manager
 * ~~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Vote-Manager.php
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
	$_PLUGIN = new PluginVoteManager();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginVoteManager extends Plugin {
	public $config;
	private $startup_phase = true;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-05-16');
		$this->setCopyright('2012 - 2017 by undef.de');
		$this->setDescription('Provides a Widget and handles Skip, Restart, Balance votings.');

		$this->addDependence('PluginRaspVotes',			Dependence::DISALLOWED,	null, null);
		$this->addDependence('PluginRaspJukebox',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',			Dependence::WANTED,	null, null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onPlayerChat',			'onPlayerChat');
		$this->registerEvent('onEverySecond',			'onEverySecond');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMapPrefix',			'onEndMapPrefix');
		$this->registerEvent('onShutdown',			'onShutdown');

		$this->registerChatCommand('votemanager',		'chat_votemanager',	'Command for MasterAdins',			Player::MASTERADMINS);
		$this->registerChatCommand('helpvote',			'chat_helpvote',	'Displays info about the chat-based votes',	Player::PLAYERS);
		$this->registerChatCommand('res',			'chat_restart',		'Start a vote to restart the current Map',	Player::PLAYERS);
		$this->registerChatCommand('restart',			'chat_restart',		'Start a vote to restart the current Map',	Player::PLAYERS);
		$this->registerChatCommand('skip',			'chat_skip',		'Start a vote to skip the current Map',		Player::PLAYERS);
		$this->registerChatCommand('next',			'chat_skip',		'Start a vote to skip the current Map',		Player::PLAYERS);
		$this->registerChatCommand('yes',			'chat_yes',		'Accept the current vote',			Player::PLAYERS);
		$this->registerChatCommand('no',			'chat_no',		'Reject the current vote',			Player::PLAYERS);
		$this->registerChatCommand('balance',			'chat_balance',		'Start a vote to balance the teams',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.0';
		if (defined('UASECO_VERSION')) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[VoteManager] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[VoteManager] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}

		if (!$this->config = $aseco->parser->xmlToArray('config/vote_manager.xml')) {
			trigger_error('[VoteManager] Could not read/parse config file "config/vote_manager.xml"!', E_USER_ERROR);
		}

		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		// Check/Setup the limits
		if ($this->config['VOTING'][0]['RATIO'][0] < 0.2) {
			$this->config['VOTING'][0]['RATIO'][0] = 0.2;
		}

		$this->config['VOTING'][0]['TIMEOUT_LIMIT'][0]						= (int)$this->config['VOTING'][0]['TIMEOUT_LIMIT'][0];
		$this->config['VOTING'][0]['COUNTDOWN'][0]						= (int)$this->config['VOTING'][0]['COUNTDOWN'][0];
		$this->config['VOTING'][0]['MAX_VOTES'][0]						= (int)$this->config['VOTING'][0]['MAX_VOTES'][0];
		$this->config['VOTING'][0]['MAX_RESTARTS'][0]						= (int)$this->config['VOTING'][0]['MAX_RESTARTS'][0];
		$this->config['DEDICATED_SERVER'][0]['DISABLE_CALLVOTES'][0]				= ((strtoupper($this->config['DEDICATED_SERVER'][0]['DISABLE_CALLVOTES'][0]) == 'TRUE') ? true : false);
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0]				= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['DEFAULT'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0]				= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['JUMPTOMAPIDENT'][0]			= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['JUMPTOMAPIDENT'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0]				= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETMODESCRIPTSETTINGSANDCOMMANDS'][0]	= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETMODESCRIPTSETTINGSANDCOMMANDS'][0];
		$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETNEXTMAPIDENT'][0]			= (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETNEXTMAPIDENT'][0];
		$this->config['NUMBER_FORMAT'][0]							= strtolower($this->config['NUMBER_FORMAT'][0]);
		$this->config['MODE'][0]								= strtolower($this->config['MODE'][0]);

		if (empty($this->config['WIDGET'][0]['SCALE'][0])) {
			$this->config['WIDGET'][0]['SCALE'][0] = 1.0;
		}

		// Preset defaults
		$this->config['TimeAttackTimelimit']			= -1;
		$this->config['RunningVote']['Votes']['Restart']	= 0;
		$this->config['RunningVote']['Votes']['Skip']		= 0;
		$this->config['RunningVote']				= $this->cleanupCurrentVote();
		$this->config['Cache']['Todo']['onEndMap']		= false;
		$this->config['Cache']['LastMap']['Runs']		= 0;
		$this->config['Cache']['IgnoreLogin']			= array();
		$this->config['Cache']['AllowLogin']			= array();


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


		// Working on the <allow_list>
		foreach ($this->config['ALLOW_LIST'][0]['LOGIN'] as $login) {
			if ( !empty($login) ) {
				$this->config['Cache']['AllowLogin'][] = trim($login);
			}
		}
		unset($this->config['ALLOW_LIST']);


		// Store the original CallVoteRatios and CallVoteTimeOut for restoring at onShutdown
		$this->config['OriginalCallVoteRatios'] = $aseco->client->query('GetCallVoteRatios');
		$GetCallVoteTimeOut = $aseco->client->query('GetCallVoteTimeOut');
		$this->config['OriginalCallVoteTimeOut'] = $GetCallVoteTimeOut['CurrentValue'];
		unset($GetCallVoteTimeOut);


		// Disable the CallVotes 'RestartMap', 'NextMap' and 'AutoTeamBalance'
		$callvotes = array();
		$callvotes[] = array(
			'Command'	=> 'RestartMap',
			'Ratio'		=> (float)-1,
		);
		$callvotes[] = array(
			'Command'	=> 'NextMap',
			'Ratio'		=> (float)-1,
		);
		$callvotes[] = array(
			'Command'	=> 'AutoTeamBalance',
			'Ratio'		=> (float)-1,
		);

		// Setup the configured CallVotes
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'Ban',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['BAN'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['JUMPTOMAPIDENT'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'JumpToMapIdent',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['JUMPTOMAPIDENT'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'Kick',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['KICK'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETMODESCRIPTSETTINGSANDCOMMANDS'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'SetModeScriptSettingsAndCommands',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETMODESCRIPTSETTINGSANDCOMMANDS'][0],
			);
		}
		if ( is_float($this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETNEXTMAPIDENT'][0]) ) {
			$callvotes[] = array(
				'Command'	=> 'SetNextMapIdent',
				'Ratio'		=> (float)$this->config['DEDICATED_SERVER'][0]['RATIO'][0]['SETNEXTMAPIDENT'][0],
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

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		if ($answer['Action'] == 'Vote') {
			if ($answer['Value'] == 'Yes') {
				$aseco->releaseChatCommand('/yes', $login);
			}
			else if ($answer['Value'] == 'No') {
				$aseco->releaseChatCommand('/no', $login);
			}
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
				else if ($this->config['RunningVote']['Mode'] == 'Balance') {
					// Balance passed, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_BALANCE_SUCCESS'][0]);

					// Balance now
					$this->handleTodo('Balance');
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="VoteManagerWidget"></manialink>';
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
				else if ($this->config['RunningVote']['Mode'] == 'Balance') {
					// Balance did not pass, send the info message
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_BALANCE_FAILED'][0]);
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="VoteManagerWidget"></manialink>';
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

				// Check if all Players have voted and end voting
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
			}
			else if ($this->config['RunningVote']['Countdown'] > -3 && $this->config['RunningVote']['Countdown'] <= 0) {

				// Countdown
				$this->config['RunningVote']['Countdown'] --;

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
					else if ($this->config['RunningVote']['Mode'] == 'Balance') {
						// Balance passed, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_BALANCE_SUCCESS'][0]);

						// Balance now
						$this->handleTodo('Balance');
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
					else if ($this->config['RunningVote']['Mode'] == 'Balance') {
						// Balance did not pass, send the info message
						$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_BALANCE_FAILED'][0]);
					}
				}

				// Cleanup ended vote
				$this->config['RunningVote'] = $this->cleanupCurrentVote();

				// Hide all Widgets from all Players
				$xml = '<manialink id="VoteManagerWidget"></manialink>';
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

		if ( $aseco->isAnyAdminByLogin($chat[1]) ) {
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

		// Preload the Images
		$xml  = '<manialink id="PreloadImages" version="3">';
		$xml .= '<quad pos="320 240" z-index="1.1" size="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_UP'][0] .'"/>';
		$xml .= '<quad pos="320 240" z-index="1.1" size="3.2 3.2" image="'. $this->config['IMAGES'][0]['THUMB_DOWN'][0] .'"/>';
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

	public function onLoadingMap ($aseco, $map_item) {

		// Reset all before votings
		$this->config['RunningVote'] = $this->cleanupCurrentVote();
		$this->config['RunningVote']['Votes']['Restart']	= 0;
		$this->config['RunningVote']['Votes']['Skip']		= 0;

		// Reset
		$this->config['Cache']['Todo']['onEndMap'] = false;

		// Store the Timelimit at TA
		if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK && $aseco->server->gameinfo->time_attack['TimeLimit'] > 0) {
			$this->config['TimeAttackTimelimit'] = time() + $aseco->server->gameinfo->time_attack['TimeLimit'];
		}
		else {
			$this->config['TimeAttackTimelimit'] = -1;
		}

		// Find Uid and count the restarts or reset
		if ($aseco->server->maps->previous->uid == $aseco->server->maps->current->uid && $this->startup_phase == false) {
			// Count the restarts
			$this->config['Cache']['LastMap']['Runs'] ++;
		}
		else {
			// Reset
			$this->config['Cache']['LastMap']['Runs'] = 0;
		}

		// Reset class internal $this->startup_phase
		$this->startup_phase = false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		// -1 because the Admin has restarted
		$this->config['Cache']['LastMap']['Runs'] --;
		if ($this->config['Cache']['LastMap']['Runs'] < 0) {
			$this->config['Cache']['LastMap']['Runs'] = 0;
		}

		// Reset Todo's
		$this->config['Cache']['Todo']['onEndMap'] = false;

		// Emulate event onLoadingMap
		$this->onLoadingMap($aseco, $map);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMapPrefix ($aseco, $data) {

		// Hide all Widgets from all Players
		$xml = '<manialink id="VoteManagerWidget"></manialink>';
		$aseco->sendManialink($xml, false, 0, false);

		// Reset all before votings if a running vote was interrupted (by Admin skip/restart)
		if ($this->config['RunningVote']['Active'] == true) {
			$this->config['RunningVote'] = $this->cleanupCurrentVote();
			$this->config['RunningVote']['Votes']['Restart']	= 0;
			$this->config['RunningVote']['Votes']['Skip']		= 0;
			$this->config['TimeAttackTimelimit']			= -1;
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
			// Simulate a onEndMap for Dedimania, otherwise new driven records are lost!
			if ( isset($aseco->plugins['PluginDedimania']) ) {
				$aseco->plugins['PluginDedimania']->onEndMap($aseco, $aseco->server->maps->current);
			}

			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				// Don't clear scores if in Cup mode
				$aseco->client->query('RestartMap', true);
			}
			else {
				$aseco->client->query('RestartMap');
			}
			$aseco->console('[VoteManager] "'. $mode .'" the current Map ['. $aseco->stripStyles($aseco->server->maps->current->name) .']');
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
			$aseco->console('[VoteManager] "'. $mode .'" the current Map ['. $aseco->stripStyles($aseco->server->maps->current->name) .']');
		}
		else if ($mode == 'Skip') {
			if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
				// Don't clear scores if in Cup mode
				$aseco->client->query('NextMap', true);
			}
			else {
				$aseco->client->query('NextMap');
			}
			$aseco->console('[VoteManager] "'. $mode .'" the current Map ['. $aseco->stripStyles($aseco->server->maps->current->name) .']');
		}
		else if ($mode == 'Balance') {
			$aseco->client->query('AutoTeamBalance');
			$aseco->console('[VoteManager] "'. $mode .'" the Teams on Map ['. $aseco->stripStyles($aseco->server->maps->current->name) .']');
		}
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

		$xml = str_replace(
			array('%LOGIN%', '%USERNAME%', '%QUESTION%'),
			array($this->config['RunningVote']['StartedFrom']['Login'], $this->handleSpecialChars($this->config['RunningVote']['StartedFrom']['Nickname']), $this->config['RunningVote']['Question']),
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
		global $aseco;

		// Placeholder: %LOGIN%, %USERNAME%, %QUESTION%
		$xml  = '<manialink name="VoteManagerWidget" id="VoteManagerWidget" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';
		$xml .= '<frame pos="'. $this->config['WIDGET'][0]['POS_X'][0] .' '. $this->config['WIDGET'][0]['POS_Y'][0] .'" z-index="0" id="VoteManagerFrame">';
		$xml .= '<quad pos="0 -4" z-index="0" size="104.75 16.625" bgcolor="032942DD"/>';

		// Icon and Title
		$xml .= '<quad pos="0 0" z-index="0.01" size="104.75 4" bgcolor="55556699" bgcolorfocus="555566BB" id="VoteManagerWidgetTitle" ScriptEvents="1"/>';
		$xml .= '<quad pos="1 -0.5" z-index="0.02" size="2.75 2.75" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label pos="5 -0.9" z-index="0.02" size="188.5 5" class="labels" textsize="1" scale="0.9" textcolor="FFFFFFFF" text="'. $this->config['WIDGET'][0]['TITLE'][0] .'"/>';

		// BEGIN: Question
		$xml .= '<frame pos="0 -5.625" z-index="0.02">';
		$xml .= '<label pos="52.5 0" z-index="0.03" size="125 4.3125" class="labels" halign="center" textsize="2" text="$S%USERNAME%$Z%QUESTION%"/>';
		$xml .= '</frame>';
		// END: Question

		// BEGIN: Statistic-Background
		$xml .= '<frame pos="21.25 -14" z-index="0.02">';
		$xml .= '<quad pos="0 0" z-index="0.04" size="62.5 5" bgcolor="FFFFFF33"/>';
		$xml .= '</frame>';
		// END: Statistic-Background

		// BEGIN: Statistics
		$xml .= '<frame pos="21.25 -14" z-index="0.03">';
		$xml .= '<quad pos="0 0" z-index="0.06" size="0 2.5" bgcolor="339900FF" id="VoteManagerQuadBarYes"/>';
		$xml .= '<label pos="5.25 -1.25" z-index="0.07" size="6 2.5" class="labels" halign="right" valign="center2" textsize="1" scale="0.7" text="0" id="VoteManagerLabelAmountYes"/>';
		$xml .= '<label pos="16 -1.25" z-index="0.07" size="12.5 2.5" class="labels" halign="right" valign="center2" textsize="1" scale="0.7" text="0.0%" id="VoteManagerLabelPercentYes"/>';
		$xml .= '</frame>';

		$xml .= '<frame pos="21.25 -16.5" z-index="0.03">';
		$xml .= '<quad pos="0 0" z-index="0.06" size="0 2.5" bgcolor="DD0022FF" id="VoteManagerQuadBarNo"/>';
		$xml .= '<label pos="5.25 -1.25" z-index="0.07" size="6 2.5" class="labels" halign="right" valign="center2" textsize="1" scale="0.7" text="0" id="VoteManagerLabelAmountNo"/>';
		$xml .= '<label pos="16 -1.25" z-index="0.07" size="12.5 2.5" class="labels" halign="right" valign="center2" textsize="1" scale="0.7" text="0.0%" id="VoteManagerLabelPercentNo"/>';
		$xml .= '</frame>';
		// END: Statistics

		// BEGIN: Ratio marker
		$xml .= '<frame pos="21.25 -13.5" z-index="0.04">';
		$xml .= '<quad pos="'. (0.625 * ($this->config['VOTING'][0]['RATIO'][0] * 100)) .' 0.01" z-index="0.16" size="0.25 6" bgcolor="000000AA"/>';
		$xml .= '<quad pos="'. (0.625 * ($this->config['VOTING'][0]['RATIO'][0] * 100) + 0.05) .' 0.01" z-index="0.17" size="0.25 6" bgcolor="FFFFFFAA"/>';
		$xml .= '</frame>';
		// END: Ratio marker

		// BEGIN: YES Button
		$xml .= '<frame pos="2 -14.0625" z-index="0.02">';
		$xml .= '<quad pos="0 0" z-index="0.03" size="17.75 5" bgcolor="0099FFFF" bgcolorfocus="DDDDDDFF" id="VoteManagerButtonYes" ScriptEvents="1"/>';
		$xml .= '<label pos="8.875 -2.5" z-index="0.04" size="17.75 0" class="labels" halign="center" valign="center2" textsize="1" scale="0.8" text="$OYES (F5)"/>';
		$xml .= '</frame>';
		// END: YES Button

		// BEGIN: NO Button
		$xml .= '<frame pos="85.25 -14.0625" z-index="0.02">';
		$xml .= '<quad pos="0 0" z-index="0.03" size="17.75 5" bgcolor="0099FFFF" bgcolorfocus="DDDDDDFF" id="VoteManagerButtonNo" ScriptEvents="1"/>';
		$xml .= '<label pos="8.875 -2.5" z-index="0.04" size="17.75 0" class="labels" halign="center" valign="center2" textsize="1" scale="0.8" text="$ONO (F6)"/>';
		$xml .= '</frame>';
		// END: NO Button

		// BEGIN: Countdown
		$xml .= '<frame pos="52.5 -10.3125" z-index="0.02">';
		$xml .= '<label pos="0 0" z-index="0.01" size="100 2.8125" class="labels" halign="center" textsize="1" textcolor="FFFFFFFF" text="" id="VoteManagerLabelCountdown"/>';
		$xml .= '</frame>';
		// END: Countdown

		// BEGIN: Vote marker
		$xml .= '<frame pos="0 -13.6875" z-index="0.04">';
		$xml .= '<quad pos="-1.25 0" z-index="0.01" size="6 6" image="'. $this->config['IMAGES'][0]['THUMB_UP'][0] .'" id="VoteManagerMarkerThumbUp" hidden="true"/>';
		$xml .= '<quad pos="99.75 0" z-index="0.01" size="6 6" image="'. $this->config['IMAGES'][0]['THUMB_DOWN'][0] .'" id="VoteManagerMarkerThumbDown" hidden="true"/>';
		$xml .= '</frame>';
		// END: Vote marker

		$xml .= '</frame>';

		$message = $aseco->formatText($this->config['MESSAGES'][0]['TIME_REMAINING'][0], '%1');

$maniascript = <<<EOL
<script><!--
/*
 * ----------------------------------
 * Function:	Widget @ plugin.vote_manager.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
#Include "MathLib" as MathLib
Void MarkPlayer (Text _Login, Text _Vote) {
	foreach (Player in Players) {
		if (Player.User.Login == _Login) {
			declare Text VoteManager_CurrentVote for Player = "None";
			if (VoteManager_CurrentVote != _Vote) {
				VoteManager_CurrentVote = _Vote;
			}
		}
	}
}
main () {
	declare FrameVoteManager		<=> (Page.GetFirstChild("VoteManagerFrame") as CMlFrame);
	declare LabelCountdown			<=> (Page.GetFirstChild("VoteManagerLabelCountdown") as CMlLabel);
	declare QuadMarkerThumbUp		<=> (Page.GetFirstChild("VoteManagerMarkerThumbUp") as CMlQuad);
	declare QuadMarkerThumbDown		<=> (Page.GetFirstChild("VoteManagerMarkerThumbDown") as CMlQuad);
	declare QuadBarYes			<=> (Page.GetFirstChild("VoteManagerQuadBarYes") as CMlQuad);
	declare LabelAmountYes			<=> (Page.GetFirstChild("VoteManagerLabelAmountYes") as CMlLabel);
	declare LabelPercentYes			<=> (Page.GetFirstChild("VoteManagerLabelPercentYes") as CMlLabel);
	declare QuadBarNo			<=> (Page.GetFirstChild("VoteManagerQuadBarNo") as CMlQuad);
	declare LabelAmountNo			<=> (Page.GetFirstChild("VoteManagerLabelAmountNo") as CMlLabel);
	declare LabelPercentNo			<=> (Page.GetFirstChild("VoteManagerLabelPercentNo") as CMlLabel);

	declare Integer Countdown		= {$this->config['RunningVote']['Countdown']};
	declare Integer RefreshInterval		= 250;
	declare Integer RefreshTime		= CurrentTime;
	declare Real MouseDistanceX		= 0.0;
	declare Real MouseDistanceY		= 0.0;
	declare Boolean MoveWindow		= False;
	declare Text InitiatorLogin		= "%LOGIN%";
	declare Text MessageCount		= "{$message}";
	declare Text MessageFinish		= "{$this->config['MESSAGES'][0]['VOTE_FINISHED'][0]}";
	declare Text PrevTime			= CurrentLocalDateText;

	FrameVoteManager.RelativeScale		= {$this->config['WIDGET'][0]['SCALE'][0]};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		if (InputPlayer.Login == InitiatorLogin && QuadMarkerThumbUp.Visible != True) {
			MarkPlayer(InputPlayer.Login, "Yes");
			QuadMarkerThumbUp.Visible = True;
			QuadMarkerThumbDown.Visible = False;
		}

		if (MoveWindow == True) {
			FrameVoteManager.RelativePosition_V3.X = (MouseDistanceX + MouseX);
			FrameVoteManager.RelativePosition_V3.Y = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			if (PendingEvents.count > 0) {
				foreach (Event in PendingEvents) {
					if (Event.ControlId == "VoteManagerWidgetTitle") {
						MouseDistanceX = (FrameVoteManager.RelativePosition_V3.X - MouseX);
						MouseDistanceY = (FrameVoteManager.RelativePosition_V3.Y - MouseY);
						MoveWindow = True;
					}
				}
			}
		}
		else {
			MoveWindow = False;
		}

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			// Countdown
			Countdown = Countdown - 1;
			if (Countdown < 0) {
				LabelCountdown.SetText(MessageFinish);
				break;
			}
			LabelCountdown.Value = TextLib::Compose(MessageCount, TextLib::ToText(Countdown));
		}

		// Throttling to work only on every "RefreshInterval"
		if (CurrentTime > RefreshTime) {
			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);

			// Do vote statistics
			declare Real VotesYes	= 0.0;
			declare Real VotesNo	= 0.0;
			declare Real VotesTotal	= 0.0;
			foreach (Player in Players) {
				declare Text VoteManager_CurrentVote for Player = "None";
				if (VoteManager_CurrentVote == "Yes") {
					VotesYes += 1.0;
				}
				else if (VoteManager_CurrentVote == "No") {
					VotesNo += 1.0;
				}
			}
			VotesTotal = VotesYes + VotesNo;
			if (VotesTotal == 0.0) {
				VotesTotal = 0.00000000000000001; // Division by Zero prevention
			}

			declare Real VotesYesPercent = (VotesYes / VotesTotal * 100);
			QuadBarYes.Size.X = (0.625 * VotesYesPercent);
			LabelAmountYes.Value = ""^ MathLib::FloorInteger(VotesYes);
			LabelPercentYes.Value = TextLib::FormatReal(VotesYesPercent, 2, False, False) ^"%";

			declare Real VotesNoPercent = (VotesNo / VotesTotal * 100);
			QuadBarNo.Size.X = (0.625 * VotesNoPercent);
			LabelAmountNo.Value = ""^ MathLib::FloorInteger(VotesNo);
			LabelPercentNo.Value = TextLib::FormatReal(VotesNoPercent, 2, False, False) ^"%";
		}

		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseOver : {
					if (Event.ControlId == "VoteManagerButtonYes" ||Event.ControlId == "VoteManagerButtonNo") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
				case CMlEvent::Type::MouseClick : {
					// Prevent the initiator from changing his vote
					if (InputPlayer.Login != InitiatorLogin) {
						if (Event.ControlId == "VoteManagerButtonYes") {
							TriggerPageAction("PluginVoteManager?Action=Vote&Value=Yes");
							MarkPlayer(InputPlayer.Login, "Yes");
							QuadMarkerThumbUp.Visible = True;
							QuadMarkerThumbDown.Visible = False;
						}
						else if (Event.ControlId == "VoteManagerButtonNo") {
							TriggerPageAction("PluginVoteManager?Action=Vote&Value=No");
							MarkPlayer(InputPlayer.Login, "No");
							QuadMarkerThumbUp.Visible = False;
							QuadMarkerThumbDown.Visible = True;
						}
					}
				}
				case CMlEvent::Type::KeyPress : {
					// Prevent the initiator from changing his vote
					if (InputPlayer.Login != InitiatorLogin) {
						if (Event.KeyName == "F5") {
							TriggerPageAction("PluginVoteManager?Action=Vote&Value=Yes");
							MarkPlayer(InputPlayer.Login, "Yes");
							QuadMarkerThumbUp.Visible = True;
							QuadMarkerThumbDown.Visible = False;
						}
						else if (Event.KeyName == "F6") {
							TriggerPageAction("PluginVoteManager?Action=Vote&Value=No");
							MarkPlayer(InputPlayer.Login, "No");
							QuadMarkerThumbUp.Visible = False;
							QuadMarkerThumbDown.Visible = True;
						}
					}
				}
			}
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

	public function buildHelpWindow ($player) {
		global $aseco;

		$xml = '<frame pos="2.5 -2.5" z-index="0.02">';
		$xml .= '<label pos="0 0" z-index="0.01" size="194.55 0" textsize="1" textcolor="FF0F" autonewline="1" text="With this Plugin you can start a Voting for restarting or skipping the current Map, please use one of the described commands below for a new vote.'. LF.LF .'A restart vote for a Map did not restart as soon as the vote passed, the restart is delayed until the end of the Race. If a restart vote passed and a Player want to start a skip vote, then this is rejected."/>';

		// Command "/helpvote"
		$xml .= '<label pos="0 -18.75" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/helpvote"/>';
		$xml .= '<label pos="47.5 -18.75" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Display this help"/>';

		// Command "/restart" or "/res"
		$xml .= '<label pos="0 -22.5" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/restart $FF0or$FFF /res"/>';
		$xml .= '<label pos="47.5 -22.5" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Start a vote to restart the current Map"/>';

		// Command "/skip" or "/next"
		$xml .= '<label pos="0 -26.25" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/skip $FF0or$FFF /next"/>';
		$xml .= '<label pos="47.5 -26.25" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Start a vote to skip the current Map"/>';

		// Command "/yes" or F5
		$xml .= '<label pos="0 -30" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/yes $FF0or$FFF F5"/>';
		$xml .= '<label pos="47.5 -30" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Accept the current vote"/>';

		// Command "/no" or F6
		$xml .= '<label pos="0 -33.75" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/no $FF0or$FFF F6"/>';
		$xml .= '<label pos="47.5 -33.75" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Reject the current vote"/>';

		// Command "/votemanager reload"
		$xml .= '<label pos="0 -41.25" z-index="0.01" size="42.5 3.75" textsize="1" textcolor="FFFF" text="/votemanager reload"/>';
		$xml .= '<label pos="47.5 -41.25" z-index="0.01" size="95 3.75" textsize="1" textcolor="FF0F" text="Reload the vote_manager.xml (for MasterAdmins only)"/>';

		$xml .= '</frame>';


		// Setup settings for Window
		$settings_styles = array(
			'icon'	=> $this->config['WIDGET'][0]['ICON_STYLE'][0] .','. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0],
		);
		$settings_content = array(
			'title'			=> 'Help for Vote Manager',
			'data'			=> array($xml),
			'about'			=> 'VOTE MANAGER/'. $this->getVersion(),
			'mode'			=> 'pages',
			'add_background'	=> true,
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
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
		$preset['Votes']['Restart']	= $this->config['RunningVote']['Votes']['Restart'];	// Reseted at onLoadingMap
		$preset['Votes']['Skip']	= $this->config['RunningVote']['Votes']['Skip'];	// Reseted at onLoadingMap

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
//			// Do not allow to change the own started vote
//			if ($this->config['RunningVote']['StartedFrom']['Login'] == $login) {
//				$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_NO_OWN_VOTE'][0], $login);
//				return;
//			}

			// Check if Player have already voted before
			$not_voted = (($type == 'Yes') ? 'No' : 'Yes');
			if (isset($this->config['RunningVote']['Votes'][$type][$login])) {
				// Player voted the same
				return;
			}
			else if (isset($this->config['RunningVote']['Votes'][$not_voted][$login])) {
				// Player voted already but change his mind, so unset the old vote
				unset($this->config['RunningVote']['Votes'][$not_voted][$login]);
			}

			// Store the current vote
			$this->config['RunningVote']['Votes'][$type][$login] = true;
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

	public function checkVotePossibility ($type, $login) {
		global $aseco;

		if (in_array($login, $this->config['Cache']['IgnoreLogin'])) {
			// Login are on the <ignore_list>, skipping
			$aseco->console('[VoteManager] Skipping vote attempt from ['. $login .'] because player is in the <ignore_list>.');
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_IGNORED'][0]);
			$aseco->sendChatMessage($message, $login);
			return false;
		}

		if (count($this->config['Cache']['AllowLogin']) > 0 && !in_array($login, $this->config['Cache']['AllowLogin'])) {
			// <allow_list> is not empty and Login is not in the <allow_list>, skipping
			$aseco->console('[VoteManager] Skipping vote attempt from ['. $login .'] because player is NOT in the <allow_list>.');
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_IGNORED'][0]);
			$aseco->sendChatMessage($message, $login);
			return false;
		}

		if ($type == 'Restart' && $this->config['Cache']['LastMap']['Runs'] >= $this->config['VOTING'][0]['MAX_RESTARTS'][0]) {
			// Max. restarts reached, cancel this request
			$message = $aseco->formatText($this->config['MESSAGES'][0]['VOTE_RESTART_LIMITED'][0],
				$this->config['VOTING'][0]['MAX_RESTARTS'][0],
				(($this->config['VOTING'][0]['MAX_RESTARTS'][0] == 1) ? '' : 's')
			);
			$aseco->sendChatMessage($message, $login);
			return false;
		}

		if ($type == 'Skip' && $this->config['Cache']['Todo']['onEndMap'] == 'Restart') {
			// There was a successfully restart vote before, cancel this skip request
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_CANCEL'][0], $login);
			return false;
		}

		if ($type == 'Skip' && ($aseco->server->maps->previous->uid == $aseco->server->maps->current->uid && $this->config['Cache']['LastMap']['Runs'] >= 1)) {
			// For the current Map was a successfully restart vote before, cancel this skip request
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_SKIP_CANCEL'][0], $login);
			return false;
		}

		if ($this->config['RunningVote']['Active'] == true) {
			// There is already a running vote, cancel this request
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['VOTE_ALREADY_RUNNING'][0], $login);
			return false;
		}

		if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
			if ($this->config['TimeAttackTimelimit'] > (time() + $this->config['VOTING'][0]['TIMEOUT_LIMIT'][0]) || $aseco->server->gameinfo->time_attack['TimeLimit'] == 0) {
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
		$player = $aseco->server->players->getPlayerByLogin($login);
		$this->buildHelpWindow($player);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_restart ($aseco, $login, $chat_command, $chat_parameter) {

		if ( $this->checkVotePossibility('Restart', $login) ) {
			// Get Player object
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				// Setup new vote
				$this->setupNewVote('Restart', $player->login, $player->nickname, $this->config['MESSAGES'][0]['QUESTION_RESTART'][0]);
			}
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
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				// Setup new vote
				$this->setupNewVote('Skip', $player->login, $player->nickname, $this->config['MESSAGES'][0]['QUESTION_SKIP'][0]);
			}
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

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_balance ($aseco, $login, $chat_command, $chat_parameter) {

		if ( $this->checkVotePossibility('Balance', $login) ) {
			// Get Player object
			if ($player = $aseco->server->players->getPlayerByLogin($login)) {
				// Setup new vote
				$this->setupNewVote('Balance', $player->login, $player->nickname, $this->config['MESSAGES'][0]['QUESTION_BALANCE'][0]);
			}
		}
	}
}

?>
