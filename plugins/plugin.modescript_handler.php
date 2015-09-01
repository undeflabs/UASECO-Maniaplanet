<?php
/*
 * Plugin: Modescript Handler
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Handle the Modescript Callbacks send by the dedicated server and related settings.
 * » Based upon the plugin.modescriptcallback.php from MPAseco, written by the MPAseco team for ShootMania
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-23
 * Copyright:	2014 - 2015 by undef.de
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
 * Documentation:
 * » http://doc.maniaplanet.com/dedicated-server/xmlrpc/xml-rpc-scripts.html
 * » http://doc.maniaplanet.com/dedicated-server/settings-list.html
 * » http://doc.maniaplanet.com/dedicated-server/xmlrpc/methods/latest.html
 * » http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
 * » http://doc.maniaplanet.com/creation/maniascript/libraries/library-ui.html
 * » docs/Dedicated Server/ListCallbacks_2013-04-16.html
 *
 * Dependencies:
 * - plugins/plugin.checkpoints.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginModescriptHandler();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginModescriptHandler extends Plugin {

	// Block some callbacks we did not want to use
	public $callback_blocklist = array(
		// Nadeo officials
		'LibXmlRpc_BeginMatchStop',
		'LibXmlRpc_BeginMapStop',
		'LibXmlRpc_BeginSubmatchStop',
		'LibXmlRpc_BeginRoundStop',
		'LibXmlRpc_BeginTurnStop',
		'LibXmlRpc_EndTurnStop',
		'LibXmlRpc_EndRoundStop',
		'LibXmlRpc_EndSubmatchStop',
		'LibXmlRpc_EndMapStop',
		'LibXmlRpc_EndMatchStop',
		'LibXmlRpc_EndServerStop',
		'LibXmlRpc_PlayersTimes',				// LibXmlRpc_GetPlayersTimes
		'LibXmlRpc_PlayersScores',				// LibXmlRpc_GetPlayersScores
		'LibXmlRpc_TeamsMode',					// LibXmlRpc_GetTeamsMode

		// Knockout.Script.txt					https://forum.maniaplanet.com/viewtopic.php?p=247611
		'KOPlayerAdded',
		'KOPlayerRemoved',
		'KOSendWinner',
	);

	// Stores the modescript_settings.xml settings
	private $settings		= array();

	// Stores the <ui_properties>
	private $ui_properties		= array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Handle the Modescript Callbacks send by the dedicated server and related settings.');

		$this->addDependence('PluginCheckpoints',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEndRound',			'onEndRound');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onBeginScriptInitialisation',	'onBeginScriptInitialisation');
		$this->registerEvent('onModeScriptCallbackArray',	'onModeScriptCallbackArray');
		$this->registerEvent('onModeScriptCallback',		'onModeScriptCallback');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $restart = false) {

		// Block some Callbacks we did not want to use
		$this->setupBlockCallbacks();

		// Read Configuration
		if (!$this->settings = $aseco->parser->xmlToArray('config/modescript_settings.xml', true, true)) {
			trigger_error('[ModescriptHandler] Could not read/parse config file "config/modescript_settings.xml"!', E_USER_ERROR);
		}
		$this->settings = $this->settings['SETTINGS'];
		unset($this->config['SETTINGS']);


		if ($restart == false) {
			// Check the installed Scripts from the dedicated Server
			$this->checkModescriptVersions();
		}


		// MatchMaking
		$aseco->server->gameinfo->matchmaking['MatchmakingAPIUrl']			= $this->settings['MATCHMAKING'][0]['MATCHMAKING_API_URL'][0];
		$aseco->server->gameinfo->matchmaking['MatchmakingMode']			= (int)$this->settings['MATCHMAKING'][0]['MATCHMAKING_MODE'][0];
		$aseco->server->gameinfo->matchmaking['MatchmakingRematchRatio']		= (float)$this->settings['MATCHMAKING'][0]['MATCHMAKING_REMATCH_RATIO'][0];
		$aseco->server->gameinfo->matchmaking['MatchmakingRematchNbMax']		= (int)$this->settings['MATCHMAKING'][0]['MATCHMAKING_REMATCH_NUMBER_MAX'][0];
		$aseco->server->gameinfo->matchmaking['MatchmakingVoteForMap']			= $aseco->string2bool($this->settings['MATCHMAKING'][0]['MATCHMAKING_VOTE_FOR_MAP'][0]);
		$aseco->server->gameinfo->matchmaking['MatchmakingProgressive']			= $aseco->string2bool($this->settings['MATCHMAKING'][0]['MATCHMAKING_PROGRESSIVE'][0]);
		$aseco->server->gameinfo->matchmaking['MatchmakingWaitingTime']			= (int)$this->settings['MATCHMAKING'][0]['MATCHMAKING_WAITING_TIME'][0];
		$aseco->server->gameinfo->matchmaking['LobbyRoundPerMap']			= (int)$this->settings['MATCHMAKING'][0]['LOBBY_ROUND_PER_MAP'][0];
		$aseco->server->gameinfo->matchmaking['LobbyMatchmakerPerRound']		= (int)$this->settings['MATCHMAKING'][0]['LOBBY_MATCHMAKER_PER_ROUND'][0];
		$aseco->server->gameinfo->matchmaking['LobbyMatchmakerWait']			= (int)$this->settings['MATCHMAKING'][0]['LOBBY_MATCHMAKER_WAIT'][0];
		$aseco->server->gameinfo->matchmaking['LobbyMatchmakerTime']			= (int)$this->settings['MATCHMAKING'][0]['LOBBY_MATCHMAKER_TIME'][0];
		$aseco->server->gameinfo->matchmaking['LobbyDisplayMasters']			= $aseco->string2bool($this->settings['MATCHMAKING'][0]['LOBBY_DISPLAY_MASTERS'][0]);
		$aseco->server->gameinfo->matchmaking['LobbyDisableUi']				= $aseco->string2bool($this->settings['MATCHMAKING'][0]['LOBBY_DISABLE_UI'][0]);
		$aseco->server->gameinfo->matchmaking['MatchmakingErrorMessage']		= $this->settings['MATCHMAKING'][0]['MATCHMAKING_ERROR_MESSAGE'][0];
		$aseco->server->gameinfo->matchmaking['MatchmakingLogAPIError']			= $aseco->string2bool($this->settings['MATCHMAKING'][0]['MATCHMAKING_LOG_API_ERROR'][0]);
		$aseco->server->gameinfo->matchmaking['MatchmakingLogAPIDebug']			= $aseco->string2bool($this->settings['MATCHMAKING'][0]['MATCHMAKING_LOG_API_DEBUG'][0]);
		$aseco->server->gameinfo->matchmaking['MatchmakingLogMiscDebug']		= $aseco->string2bool($this->settings['MATCHMAKING'][0]['MATCHMAKING_LOG_MISC_DEBUG'][0]);
		$aseco->server->gameinfo->matchmaking['ProgressiveActivation_WaitingTime']	= (int)$this->settings['MATCHMAKING'][0]['PROGRESSIVE_ACTIVATION_WAITING_TIME'][0];
		$aseco->server->gameinfo->matchmaking['ProgressiveActivation_PlayersNbRatio']	= (int)$this->settings['MATCHMAKING'][0]['PROGRESSIVE_ACTIVATION_PLAYERS_NUMBER_RATIO'][0];

		// ModeBase
		$aseco->server->gameinfo->modebase['UseScriptCallbacks']	= true;		// Turn on the script callbacks
		$aseco->server->gameinfo->modebase['UseLegacyCallbacks']	= false;	// Disable the legacy callbacks (default value: True)
		$aseco->server->gameinfo->modebase['ChatTime']			= (int)$this->settings['MODEBASE'][0]['CHAT_TIME'][0];
		$aseco->server->gameinfo->modebase['AllowRespawn']		= $aseco->string2bool($this->settings['MODEBASE'][0]['ALLOW_RESPAWN'][0]);
		$aseco->server->gameinfo->modebase['WarmUpDuration']		= (int)$this->settings['MODEBASE'][0]['WARM_UP_DURATION'][0];
		$aseco->server->gameinfo->modebase['ScoresTableStylePath']	= $this->settings['MODEBASE'][0]['SCORES_TABLE_STYLE_PATH'][0];

		// Rounds +RoundsBase
		$aseco->server->gameinfo->rounds['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->rounds['FinishTimeout']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->rounds['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->rounds['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->rounds['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['DISPLAY_TIME_DIFF'][0]);
		$aseco->server->gameinfo->rounds['UseTieBreak']			= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['USE_TIE_BREAK'][0]);

		// TimeAttack
		$aseco->server->gameinfo->time_attack['TimeLimit']		= (int)$this->settings['MODESETUP'][0]['TIMEATTACK'][0]['TIME_LIMIT'][0];

		// Team +RoundsBase
		$aseco->server->gameinfo->team['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->team['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->team['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['TEAM'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->team['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->team['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['TEAM'][0]['DISPLAY_TIME_DIFF'][0]);
		$aseco->server->gameinfo->team['MaxPointsPerRound']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MAX_POINTS_PER_ROUND'][0];
		$aseco->server->gameinfo->team['PointsGap']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['POINTS_GAP'][0];
		$aseco->server->gameinfo->team['UsePlayerClublinks']		= $aseco->string2bool($this->settings['MODESETUP'][0]['TEAM'][0]['USE_PLAYER_CLUBLINKS'][0]);
		$aseco->server->gameinfo->team['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MAX_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->team['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MIN_PLAYERS_PER_TEAM'][0];

		// Laps
		$aseco->server->gameinfo->laps['TimeLimit']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->laps['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->laps['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['FORCE_LAPS_NUMBER'][0];

		// Cup +RoundsBase
		$aseco->server->gameinfo->cup['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->cup['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->cup['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CUP'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->cup['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->cup['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CUP'][0]['DISPLAY_TIME_DIFF'][0]);
		$aseco->server->gameinfo->cup['RoundsPerMap']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['ROUNDS_PER_MAP'][0];
		$aseco->server->gameinfo->cup['NbOfWinners']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['NUMBER_OF_WINNERS'][0];
		$aseco->server->gameinfo->cup['WarmUpDuration']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['WARM_UP_DURATION'][0];
		$aseco->server->gameinfo->cup['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MAX_PLAYERS_NUMBER'][0];
		$aseco->server->gameinfo->cup['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MIN_PLAYERS_NUMBER'][0];

		// TeamAttack
		$aseco->server->gameinfo->team_attack['TimeLimit']		= (int)$this->settings['MODESETUP'][0]['TEAMATTACK'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->team_attack['MinPlayerPerClan']	= (int)$this->settings['MODESETUP'][0]['TEAMATTACK'][0]['MIN_PLAYER_PER_CLAN'][0];
		$aseco->server->gameinfo->team_attack['MaxPlayerPerClan']	= (int)$this->settings['MODESETUP'][0]['TEAMATTACK'][0]['MAX_PLAYER_PER_CLAN'][0];
		$aseco->server->gameinfo->team_attack['MaxClanNb']		= (int)$this->settings['MODESETUP'][0]['TEAMATTACK'][0]['MAX_CLAN_NUMBER'][0];

		// Chase
		$aseco->server->gameinfo->chase['TimeLimit']			= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->chase['MapPointsLimit']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MAP_POINTS_LIMIT'][0];
		$aseco->server->gameinfo->chase['RoundPointsLimit']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['ROUND_POINTS_LIMIT'][0];
		$aseco->server->gameinfo->chase['RoundPointsGap']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['ROUND_POINTS_GAP'][0];
		$aseco->server->gameinfo->chase['GiveUpMax']			= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['GIVE_UP_MAX'][0];
		$aseco->server->gameinfo->chase['MinPlayersNb']			= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MIN_PLAYERS_NUMBER'][0];
		$aseco->server->gameinfo->chase['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->chase['FinishTimeout']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->chase['DisplayWarning']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CHASE'][0]['DISPLAY_WARNING'][0]);
		$aseco->server->gameinfo->chase['UsePlayerClublinks']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CHASE'][0]['USE_PLAYER_CLUBLINKS'][0]);
		$aseco->server->gameinfo->chase['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MAX_NUMBER_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->chase['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MIN_NUMBER_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->chase['CompetitiveMode']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CHASE'][0]['COMPETITIVE_MODE'][0]);
		$aseco->server->gameinfo->chase['WaypointEventDelay']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WAYPOINT_EVENT_DELAY'][0];
		$aseco->server->gameinfo->chase['PauseBetweenRound']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['PAUSE_BETWEEN_ROUND'][0];
		$aseco->server->gameinfo->chase['WaitingTimeMax']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WAITING_TIME_MAX'][0];

		// Knockout
		$aseco->server->gameinfo->knockout['FinishTimeout']		= (int)$this->settings['MODESETUP'][0]['KNOCKOUT'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->knockout['RoundsPerMap']		= (int)$this->settings['MODESETUP'][0]['KNOCKOUT'][0]['ROUNDS_PER_MAP'][0];
		$aseco->server->gameinfo->knockout['DoubleKnockUntil']		= (int)$this->settings['MODESETUP'][0]['KNOCKOUT'][0]['DOUBLE_KNOCKOUT_UNTIL'][0];
		$aseco->server->gameinfo->knockout['ForceLapsNb']		= (int)$this->settings['MODESETUP'][0]['KNOCKOUT'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->knockout['ShowMultilapInfo']		= $aseco->string2bool($this->settings['MODESETUP'][0]['KNOCKOUT'][0]['SHOW_MULTILAP_INFO'][0]);

		// Doppler
		$aseco->server->gameinfo->doppler['TimeLimit']			= (int)$this->settings['MODESETUP'][0]['DOPPLER'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->doppler['LapsSpeedMode']		= $aseco->string2bool($this->settings['MODESETUP'][0]['DOPPLER'][0]['LAPS_SPEED_MODE'][0]);
		$aseco->server->gameinfo->doppler['DumpSpeedOnReset']		= $aseco->string2bool($this->settings['MODESETUP'][0]['DOPPLER'][0]['DUMP_SPEED_ON_RESET'][0]);
		$aseco->server->gameinfo->doppler['VelocityUnit']		= $this->settings['MODESETUP'][0]['DOPPLER'][0]['VELOCITY_UNIT'][0];
		$aseco->server->gameinfo->doppler['ModuleBestPlayersShow']	= $aseco->string2bool($this->settings['MODESETUP'][0]['DOPPLER'][0]['MODULE_BEST_PLAYERS_SHOW'][0]);
		$aseco->server->gameinfo->doppler['ModuleBestPlayersPosition']	= $this->settings['MODESETUP'][0]['DOPPLER'][0]['MODULE_BEST_PLAYERS_POSITION'][0];



		// Store the settings at the dedicated Server
		$this->setupModescriptSettings();

		// Setup the custom Scoretable
		$this->setupCustomScoretable();


		// Setup the UI
		$this->ui_properties = $this->settings['UI_PROPERTIES'][0];

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['CHAT'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['CHAT'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['ROUND_SCORES'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['ROUND_SCORES'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['COUNTDOWN'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['COUNTDOWN'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['GO'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['GO'][0]['VISIBLE'][0]) == 'TRUE')				? true : false);
		$this->ui_properties['CHRONO'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['CHRONO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['SPEED_AND_DISTANCE'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['SPEED_AND_DISTANCE'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['VISIBLE'][0]) == 'TRUE')	? true : false);
		$this->ui_properties['POSITION'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['POSITION'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['CHECKPOINT_TIME'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_TIME'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['CHAT_AVATAR'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['CHAT_AVATAR'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['WARMUP'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['WARMUP'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['ENDMAP_LADDER_RECAP'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['ENDMAP_LADDER_RECAP'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['MULTILAP_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['MULTILAP_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);

		// Send the UI settings
		$this->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco, $round) {

		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
			$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		// When changing Gamemode force all Plugins to resync
		if ($aseco->changing_to_gamemode !== false) {
			$aseco->console('[ModescriptHandler] ########################################################');
			$aseco->console('[ModescriptHandler] Gamemode change detected, forcing all Plugins to resync!');
			$aseco->console('[ModescriptHandler] ########################################################');
			$aseco->releaseEvent('onSync', null);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginScriptInitialisation ($aseco) {

		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
			$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptCallbackArray ($aseco, $data) {

		$name = $data[0];
		$params = isset($data[1]) ? $data[1] : '';

		// Bail out if callback is on blocklist
		if (in_array($name, $this->callback_blocklist)) {
			return;
		}

		switch($name) {
			// [0]=Login
			case 'LibXmlRpc_OnStartLine':
				$aseco->releaseEvent('onPlayerStartLine', $params[0]);
		    		break;



			// [0]=Login
			case 'LibXmlRpc_OnStartCountdown':
				$aseco->releaseEvent('onPlayerStartCountdown', $params[0]);
		    		break;



			// [0]=Login, [1]=WaypointBlockId, [2]=Time, [3]=WaypointIndex, [4]=WaypointIsFinishLine, [5]=CurrentLapTime, [6]=LapWaypointNumber, [7]=WaypointIsFinishLap
			case 'LibXmlRpc_OnWayPoint':
				if ($aseco->string2bool($params[4]) === false && $aseco->string2bool($params[7]) === false) {
					$aseco->releaseEvent('onPlayerCheckpoint', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));

					if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
						// Call 'LibXmlRpc_GetPlayerRanking' to get 'LibXmlRpc_PlayerRanking',
						// required to be up-to-date on each Checkpoint in Laps
						$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetPlayerRanking', $params[0]);
					}
				}
				else {
					if ($aseco->server->maps->current->multilap === true && ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::CUP || $aseco->server->gameinfo->mode == Gameinfo::CHASE)) {
						if ($aseco->string2bool($params[4]) === false && $aseco->string2bool($params[7]) === true) {
							$aseco->releaseEvent('onPlayerFinishLap', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));
						}
						else if ($aseco->string2bool($params[4]) === true && $aseco->string2bool($params[7]) === false) {
							$aseco->releaseEvent('onPlayerFinishLine', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));
						}
						else if ($aseco->string2bool($params[4]) === true && $aseco->string2bool($params[7]) === true) {
							$aseco->releaseEvent('onPlayerFinishLap', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));
							$aseco->releaseEvent('onPlayerFinishLine', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));
						}
					}
					else {
						$aseco->releaseEvent('onPlayerFinishLine', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], ((int)$params[6]+1)));
					}
				}
				if ($aseco->string2bool($params[4]) === true || $aseco->string2bool($params[7]) === true) {
					if ($aseco->warmup_phase == false && $aseco->server->gameinfo->mode != Gameinfo::TEAM) {
						// Call 'LibXmlRpc_GetPlayerRanking' to get 'LibXmlRpc_PlayerRanking'
						$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetPlayerRanking', $params[0]);
					}
				}
		    		break;



			// [0]=Login, [1]=WaypointBlockId, [2]=WaypointIndexRace, [3]=WaypointIndexLap, [4]=TotalRespawns
			case 'LibXmlRpc_OnRespawn':
				$aseco->releaseEvent('onPlayerRespawn', array($params[0], $params[1], (int)$params[2], (int)$params[3], (int)$params[4]));
		    		break;



			// [0]=Login
			case 'LibXmlRpc_OnGiveUp':
				$aseco->releaseEvent('onPlayerGiveUp', $params[0]);
		    		break;



			// [0]=Login, [1]=StuntPoints, [2]=Combo, [3]=TotalStuntsScore, [4]=StuntFactor, [5]=StuntName, [6]=StuntAngle, [7]=IsStraightStunt, [8]=IsStuntReversed, [9]=IsMasterJump
			case 'LibXmlRpc_OnStunt':
				$aseco->releaseEvent('onPlayerStunt', $params);
		    		break;



			// [0]=Login, [1]=FinishBlockId, [2]=Time(/Score?)
			case 'LibXmlRpc_OnPlayerFinish':
				$this->playerFinish($aseco, $params[0], (int)$params[2]);
		    		break;



			case 'LibXmlRpc_BeginServer':
				$aseco->releaseEvent('onBeginScriptInitialisation', null);
		    		break;



			case 'LibXmlRpc_BeginServerStop':
				$aseco->releaseEvent('onEndScriptInitialisation', null);
		    		break;



			case 'LibXmlRpc_BeginPlaying':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Playing');
				}
				$aseco->releaseEvent('onBeginPlaying', null);
		    		break;



			case 'LibXmlRpc_EndPlaying':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Playing');
				}
				$aseco->releaseEvent('onEndPlaying', null);
		    		break;



			case 'LibXmlRpc_BeginPodium':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Podium');
				}
				$aseco->releaseEvent('onBeginPodium', null);
		    		break;



			case 'LibXmlRpc_EndPodium':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Podium');
				}
				$aseco->releaseEvent('onEndPodium', null);
		    		break;



			// [0]=IndexOfMap, [1]=Uid, [2]=RestartFlag
			case 'LibXmlRpc_LoadingMap':
				// Cleanup rankings
				$aseco->server->rankings->reset();

				// Refresh the current round point system (Rounds, Team and Cup)
				if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->client->query('TriggerModeScriptEvent', 'Rounds_GetPointsRepartition', '');
				}
				if ($aseco->string2bool($params[2]) === true) {
					$aseco->restarting = true;			// Map was restarted
				}
				else {
					$aseco->restarting = false;			// No Restart
				}
///start work-a-round for https://forum.maniaplanet.com/viewtopic.php?p=241929#p241929
///uncomment after bugfix
//				$aseco->loadingMap($params[1]);
///END
		    		break;



			// [0]=IndexOfMap, [1]=Uid
			case 'LibXmlRpc_UnloadingMap':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Unloading Map');
				}
				$aseco->releaseEvent('onUnloadingMap', (int)$params[1]);
		    		break;



			// [0]=NbRound
			case 'LibXmlRpc_BeginRound':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Round');
				}
				$aseco->releaseEvent('onBeginRound', (int)$params[0]);
				break;



			// [0]=NbRound
			case 'LibXmlRpc_EndRound':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Round');
				}
				$aseco->releaseEvent('onEndRound', (int)$params[0]);
				break;



			// [0]=IndexOfMap, [1]=Uid, [2]=RestartFlag
			case 'LibXmlRpc_BeginMap':
///start work-a-round for https://forum.maniaplanet.com/viewtopic.php?p=241929#p241929
///remove after bugfix
	if ($aseco->string2bool($params[2]) === true) {
		$aseco->restarting = true;			// Map was restarted
	}
	else {
		$aseco->restarting = false;			// No Restart
	}
	$aseco->loadingMap($params[1]);
///END
				if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
					// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
					$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
				}

				// Reset status
				$aseco->changing_to_gamemode = false;

				$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_GetWarmUp', '');
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Map');
				}
				$aseco->releaseEvent('onBeginMap', $params[1]);
				break;



			// [0]=IndexOfMap, [1]=Uid
			case 'LibXmlRpc_EndMap':
				$aseco->endMap(array((int)$params[0], $params[1]));
				break;



			// [0]=NbMatch, [1]=ScriptRestartFlag
			case 'LibXmlRpc_BeginMatch':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Match');
				}
				$aseco->releaseEvent('onBeginMatch', (int)$params[0], $aseco->string2bool($params[1]));
				break;



			// [0]=NbMatch
			case 'LibXmlRpc_EndMatch':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Match');
				}
				$aseco->releaseEvent('onEndMatch', (int)$params[0]);
				break;



			// [0]=NbSubMatch
			case 'LibXmlRpc_BeginSubmatch':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin SubMatch');
				}
				$aseco->releaseEvent('onBeginSubMatch', (int)$params[0]);
				break;



			// [0]=NbSubMatch
			case 'LibXmlRpc_EndSubMatch':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End SubMatch');
				}
				$aseco->releaseEvent('onEndSubMatch', (int)$params[0]);
				break;



			case 'LibXmlRpc_BeginWarmUp':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] WarmUp Status Changed');
				}
				$aseco->warmup_phase = true;
				$aseco->releaseEvent('onWarmUpStatusChanged', $aseco->warmup_phase);
		    		break;



			case 'LibXmlRpc_EndWarmUp':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] WarmUp Status Changed');
				}
				$aseco->warmup_phase = false;
				$aseco->releaseEvent('onWarmUpStatusChanged', $aseco->warmup_phase);
		    		break;



			// [0]=StatusOfWarmUp
			case 'LibXmlRpc_WarmUp':
				$status = $aseco->string2bool($params[0]);
				if ($aseco->warmup_phase !== $status) {
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] WarmUp Status Changed');
					}
					$aseco->warmup_phase = $status;
					$aseco->releaseEvent('onWarmUpStatusChanged', $aseco->warmup_phase);
				}
		    		break;



			// [0]=NbTurn
			case 'LibXmlRpc_BeginTurn':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Turn');
				}
				$aseco->releaseEvent('onBeginTurn', (int)$params[0]);
				break;



			// [0]=NbTurn
			case 'LibXmlRpc_EndTurn':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Turn');
				}
				$aseco->releaseEvent('onEndTurn', (int)$params[0]);
				break;



			// [0]=Status
			case 'LibXmlRpc_Pause':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] ModeScript Pause changed');
				}
				$aseco->releaseEvent('onModeScriptPauseChanged', $aseco->string2bool($params[0]));
				break;



			case 'LibXmlRpc_ScoresReady':
				// Trigger 'LibXmlRpc_PlayersRanking' response
				$aseco->client->query('TriggerModeScriptEventArray', 'LibXmlRpc_GetPlayersRanking', array('300','0'));

				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Scores ready');
				}
				$aseco->releaseEvent('onScoresReady', null);
				break;



			// [0]=Rank, [1]=Login, [2]=NickName, [3]=TeamId, [4]=IsSpectator, [5]=IsAway, [6]=BestTime, [7]=Zone, [8]=RoundScore, [9]=BestCheckpoints, [10]=TotalScore
			case 'LibXmlRpc_PlayerRanking':
				if ( isset($params[1]) ) {
					if ($player = $aseco->server->players->getPlayerByLogin($params[1])) {
						// Get current Ranking object from Player
						if ($rank = $aseco->server->rankings->getRankByLogin($player->login)) {

							// Explode string and convert to integer
							$cps = array_map('intval', explode(',', $params[9]));
							if (count($cps) == 1 && $cps[0] === -1) {
								$cps = array();
							}

							// Check for improved time/score
							if ($rank->time == 0 || $rank->time > (int)$params[6] || (int)$params[10] > 0 || count($cps) > count($rank->cps)) {
								$update = array(
									'rank'		=> (int)$params[0],
									'login'		=> $player->login,
									'nickname'	=> $player->nickname,
									'time'		=> (int)$params[6],
									'score'		=> (int)$params[10],
									'cps'		=> $cps,
			 						'team'		=> (int)$params[3],
									'spectator'	=> $aseco->string2bool($params[4]),
									'away'		=> $aseco->string2bool($params[5]),
								);

								// Update current ranking cache
								$aseco->server->rankings->update($update);
								if ($aseco->settings['developer']['log_events']['common'] == true) {
									$aseco->console('[Event] Player Ranking Updated (Player)');
								}
								$aseco->releaseEvent('onPlayerRankingUpdated', null);
							}
						}
					}
				}
		    		break;



			// [0]=Login, [1]=Rank, [2]=BestCheckpoints, [3]=TeamId, [4]=IsSpectator, [5]=IsAway, [6]=BestTime, [7]=Zone, [8]=RoundScore, [9]=TotalScore
			case 'LibXmlRpc_PlayersRanking':
				if ($aseco->server->gameinfo->mode != Gameinfo::TEAM && count($params) > 0) {
					foreach ($params as $item) {
						$rank = explode(':', $item);
						if ($player = $aseco->server->players->getPlayerByLogin($rank[0])) {
							// Explode string and convert to integer
							$cps = array_map('intval', explode(',', $rank[2]));
							if (count($cps) == 1 && $cps[0] === -1) {
								$cps = array();
							}

							$update = array(
								'rank'		=> (int)$rank[1],
								'login'		=> $player->login,
								'nickname'	=> $player->nickname,
								'time'		=> (int)$rank[6],
								'score'		=> (int)$rank[9],
								'cps'		=> $cps,
		 						'team'		=> (int)$rank[3],
								'spectator'	=> $aseco->string2bool($rank[4]),
								'away'		=> $aseco->string2bool($rank[5]),
							);

							// Update current ranking cache
							$aseco->server->rankings->update($update);
						}
					}

					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Player Ranking Updated (Players)');
					}
					$aseco->releaseEvent('onPlayerRankingUpdated', null);
				}
		    		break;



			// [0]=TeamBlueRoundScore, [1]=TeamRedRoundScore, [2]=TeamBlueTotalScore, [3]=TeamRedTotalScore
			case 'LibXmlRpc_TeamsScores':
				if ( isset($params) ) {
					$rank_blue = PHP_INT_MAX;
					$rank_red = PHP_INT_MAX;

					// Check which team has a higher score
					if ((int)$params[2] > (int)$params[3]) {
						// Set "Team Blue" to Rank 1 and "Team Red" to 2
						$rank_blue = 1;
						$rank_red = 2;
					}
					else {
						// Set "Team Blue" to Rank 2 and "Team Red" to 1
						$rank_blue = 2;
						$rank_red = 1;
					}

					// Store "Team Blue"
					$update = array(
						'rank'		=> $rank_blue,
						'login'		=> '*team:blue',
						'nickname'	=> '$08FTeam Blue',
						'time'		=> 0,
						'score'		=> (int)$params[2],
						'cps'		=> array(),
 						'team'		=> 0,
						'spectator'	=> false,
						'away'		=> false,
					);
					$aseco->server->rankings->update($update);

					// Store "Team Red"
					$update = array(
						'rank'		=> $rank_red,
						'login'		=> '*team:red',
						'nickname'	=> '$F50Team Red',
						'time'		=> 0,
						'score'		=> (int)$params[3],
						'cps'		=> array(),
 						'team'		=> 1,
						'spectator'	=> false,
						'away'		=> false,
					);
					$aseco->server->rankings->update($update);

					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Player Ranking Updated (Team)');
					}
					$aseco->releaseEvent('onPlayerRankingUpdated', null);
				}
		    		break;



			case 'Rounds_PointsRepartition':
				// Read and set the current round points repartition
				$points = array_map('intval', $params);
				if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
					$aseco->server->gameinfo->rounds['PointsRepartition'] = $points;
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
				}
				else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
					$aseco->server->gameinfo->team['PointsRepartition'] = $points;
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
				}
				else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->server->gameinfo->cup['PointsRepartition'] = $points;
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $points);
				}
		    		break;



			default:
				$aseco->console('[ModescriptHandler] Unsupported callback at onModeScriptCallbackArray() received: ['. $name .'], please report this at '. UASECO_WEBSITE);
		    		break;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptCallback ($aseco, $data) {
		$aseco->console('[ModescriptHandler] Unsupported callback at onModeScriptCallback() received: ['. $data[0] .'], please report this at '. UASECO_WEBSITE);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Player reaches finish.
	public function playerFinish ($aseco, $login, $score) {

		// If no Map info bail out immediately
		if ($aseco->server->maps->current->id === 0) {
			return;
		}

		// If relay server or not in Play status, bail out immediately
		if ($aseco->server->isrelay || $aseco->current_status != 4) {
			return;
		}

		// Check for valid player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Build a record object with the current finish information
		$finish			= new Record();
		$finish->player		= $player;
		$finish->score		= $score;
		$finish->checkpoints	= (isset($aseco->plugins['PluginCheckpoints']->checkpoints[$player->login]) ? $aseco->plugins['PluginCheckpoints']->checkpoints[$player->login]->current['cps'] : array());
		$finish->date		= strftime('%Y-%m-%d %H:%M:%S');
		$finish->new		= false;
		$finish->map		= clone $aseco->server->maps->current;
		unset($finish->map->mx);	// reduce memory usage

		// Throw prefix 'player finishes' event (checkpoints)
		$aseco->releaseEvent('onPlayerFinish1', $finish);

		// Throw main 'player finishes' event
		$aseco->releaseEvent('onPlayerFinish', $finish);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkModescriptVersions () {
		global $aseco;

		$aseco->console('[ModescriptHandler] Checking version from dedicated Server Modescripts...');

		$path = $aseco->settings['dedicated_installation'] .'/UserData/Scripts/';
		if (!is_dir($path)) {
			trigger_error('Please setup <dedicated_installation> in [config/UASECO.xml]!', E_USER_ERROR);
		}
		foreach ($this->settings['SCRIPTS'][0]['ENTRY'] as $item) {
			list($script, $version) = explode('|', $item);
			$rversion = (int)str_replace('-', '', $version);
			if ($fh = @fopen($path.$script, 'r')) {
				while (($line = fgets($fh)) !== false) {
					if (preg_match('/#Const\s+\w*Version\s+"(\d{4}-\d{2}-\d{2})"/', $line, $matches) === 1) {
						$mversion = (int)str_replace('-', '', $matches[1]);
						if ($mversion >= $rversion) {
							$aseco->console('[ModescriptHandler] » version '. $matches[1] .' from "'. $script .'" ok.');
						}
						else if ($mversion < $rversion) {
							$aseco->console('[ModescriptHandler] » version '. $matches[1] .' from "'. $script .'" to old, please update from "newinstall/dedicated server/" to minimum version "'. $version .'" and restart the dedicated Server!');
							exit(0);
						}
						break;
					}
				}
				fclose($fh);
			}
		}
		$aseco->console('[ModescriptHandler] ...successfully done!');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setupUserInterface () {
		global $aseco;

		// Check some limitations, details:
		// http://doc.maniaplanet.com/creation/maniascript/libraries/library-ui.html
		if ($this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0] > 0) {
			$this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0] = 0.0;
		}
		if ($this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0] < -3.2) {
			$this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0] = -3.2;
		}
		if ($this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0] < 0) {
			$this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0] = 0.0;
		}
		if ($this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0] > 1.8) {
			$this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0] = 1.8;
		}
		if ($this->ui_properties['CHAT'][0]['LINECOUNT'][0] < 0) {
			$this->ui_properties['CHAT'][0]['LINECOUNT'][0] = 0;
		}
		if ($this->ui_properties['CHAT'][0]['LINECOUNT'][0] > 40) {
			$this->ui_properties['CHAT'][0]['LINECOUNT'][0] = 40;
		}

		$ui  = '<ui_properties>';
		$ui .= ' <map_info visible="'. $aseco->bool2string($this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <opponents_info visible="'. $aseco->bool2string($this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <chat visible="'. $aseco->bool2string($this->ui_properties['CHAT'][0]['VISIBLE'][0]) .'" offset="'. $aseco->formatFloat($this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0]) .'" linecount="'. $this->ui_properties['CHAT'][0]['LINECOUNT'][0] .'" />';
		$ui .= ' <checkpoint_list visible="'. $aseco->bool2string($this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <round_scores visible="'. $aseco->bool2string($this->ui_properties['ROUND_SCORES'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <countdown visible="'. $aseco->bool2string($this->ui_properties['COUNTDOWN'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <go visible="'. $aseco->bool2string($this->ui_properties['GO'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <chrono visible="'. $aseco->bool2string($this->ui_properties['CHRONO'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <speed_and_distance visible="'. $aseco->bool2string($this->ui_properties['SPEED_AND_DISTANCE'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <personal_best_and_rank visible="'. $aseco->bool2string($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <position visible="'. $aseco->bool2string($this->ui_properties['POSITION'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <checkpoint_time visible="'. $aseco->bool2string($this->ui_properties['CHECKPOINT_TIME'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <chat_avatar visible="'. $aseco->bool2string($this->ui_properties['CHAT_AVATAR'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <warmup visible="'. $aseco->bool2string($this->ui_properties['WARMUP'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POSITION'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POSITION'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POSITION'][0]['Z'][0]) .'" />';
		$ui .= ' <endmap_ladder_recap visible="'. $aseco->bool2string($this->ui_properties['ENDMAP_LADDER_RECAP'][0]['VISIBLE'][0]) .'" />';
		$ui .= ' <multilap_info visible="'. $aseco->bool2string($this->ui_properties['MULTILAP_INFO'][0]['VISIBLE'][0]) .'" />';
		$ui .= '</ui_properties>';

		$aseco->client->query('TriggerModeScriptEvent', 'UI_SetProperties', $ui);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setUserInterfaceVisibility ($field, $value = true) {

		if ( array_key_exists(strtoupper($field), $this->ui_properties) ) {
			$this->ui_properties[strtoupper($field)][0]['VISIBLE'][0] = $value;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setUserInterfacePosition ($field, $values = array()) {
		global $aseco;

		if (array_key_exists(strtoupper($field), $this->ui_properties) && array_key_exists('POSITION', $this->ui_properties[strtoupper($field)][0]) && count($values) == 3) {
			$this->ui_properties[strtoupper($field)][0]['POSITION'][0]['X'][0] = $aseco->formatFloat($values[0]);
			$this->ui_properties[strtoupper($field)][0]['POSITION'][0]['Y'][0] = $aseco->formatFloat($values[1]);
			$this->ui_properties[strtoupper($field)][0]['POSITION'][0]['Z'][0] = $aseco->formatFloat($values[2]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getUserInterfaceField ($field) {

		if ( array_key_exists(strtoupper($field), $this->ui_properties) ) {
			return $this->ui_properties[strtoupper($field)][0];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setupBlockCallbacks () {
		global $aseco;

		foreach ($this->callback_blocklist as $callback) {
			$aseco->client->query('TriggerModeScriptEvent', 'LibXmlRpc_BlockCallback', $callback);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// http://doc.maniaplanet.com/dedicated-server/settings-list.html
	public function setupModescriptSettings () {
		global $aseco;

		// ModeBase
		$modebase = array(
			'S_UseScriptCallbacks'			=> $aseco->server->gameinfo->modebase['UseScriptCallbacks'],
			'S_UseLegacyCallbacks'			=> $aseco->server->gameinfo->modebase['UseLegacyCallbacks'],
			'S_ChatTime'				=> $aseco->server->gameinfo->modebase['ChatTime'],
			'S_AllowRespawn'			=> $aseco->server->gameinfo->modebase['AllowRespawn'],
			'S_WarmUpDuration'			=> $aseco->server->gameinfo->modebase['WarmUpDuration'],
			'S_ScoresTableStylePath'		=> $aseco->server->gameinfo->modebase['ScoresTableStylePath'],
		);

		$modesetup = array();
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			// Rounds (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> $aseco->server->gameinfo->rounds['PointsLimit'],
				'S_FinishTimeout'		=> $aseco->server->gameinfo->rounds['FinishTimeout'],
				'S_UseAlternateRules'		=> $aseco->server->gameinfo->rounds['UseAlternateRules'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->rounds['ForceLapsNb'],
				'S_DisplayTimeDiff'		=> $aseco->server->gameinfo->rounds['DisplayTimeDiff'],

				// Rounds
				'S_UseTieBreak'			=> $aseco->server->gameinfo->rounds['UseTieBreak'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
			// TimeAttack
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->time_attack['TimeLimit'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Team  (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> $aseco->server->gameinfo->team['PointsLimit'],
				'S_FinishTimeout'		=> $aseco->server->gameinfo->team['FinishTimeout'],
				'S_UseAlternateRules'		=> $aseco->server->gameinfo->team['UseAlternateRules'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->team['ForceLapsNb'],
				'S_DisplayTimeDiff'		=> $aseco->server->gameinfo->team['DisplayTimeDiff'],

				// Team
				'S_MaxPointsPerRound'		=> $aseco->server->gameinfo->team['MaxPointsPerRound'],
				'S_PointsGap'			=> $aseco->server->gameinfo->team['PointsGap'],
				'S_UsePlayerClublinks'		=> $aseco->server->gameinfo->team['UsePlayerClublinks'],
				'S_NbPlayersPerTeamMax'		=> $aseco->server->gameinfo->team['NbPlayersPerTeamMax'],
				'S_NbPlayersPerTeamMin'		=> $aseco->server->gameinfo->team['NbPlayersPerTeamMin'],

			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
			// Laps
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->laps['TimeLimit'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->laps['ForceLapsNb'],
				'S_FinishTimeout'		=> $aseco->server->gameinfo->laps['FinishTimeout'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
			// Cup (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> $aseco->server->gameinfo->cup['PointsLimit'],
				'S_FinishTimeout'		=> $aseco->server->gameinfo->cup['FinishTimeout'],
				'S_UseAlternateRules'		=> $aseco->server->gameinfo->cup['UseAlternateRules'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->cup['ForceLapsNb'],
				'S_DisplayTimeDiff'		=> $aseco->server->gameinfo->cup['DisplayTimeDiff'],

				// Cup
				'S_RoundsPerMap'		=> $aseco->server->gameinfo->cup['RoundsPerMap'],
				'S_NbOfWinners'			=> $aseco->server->gameinfo->cup['NbOfWinners'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->cup['WarmUpDuration'],
				'S_NbOfPlayersMax'		=> $aseco->server->gameinfo->cup['NbOfPlayersMax'],
				'S_NbOfPlayersMin'		=> $aseco->server->gameinfo->cup['NbOfPlayersMin'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM_ATTACK) {
			// TeamAttack
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->team_attack['TimeLimit'],
				'S_MinPlayerPerClan'		=> $aseco->server->gameinfo->team_attack['MinPlayerPerClan'],
				'S_MaxPlayerPerClan'		=> $aseco->server->gameinfo->team_attack['MaxPlayerPerClan'],
				'S_MaxClanNb'			=> $aseco->server->gameinfo->team_attack['MaxClanNb'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			// Chase
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->chase['TimeLimit'],
				'S_MapPointsLimit'		=> $aseco->server->gameinfo->chase['MapPointsLimit'],
				'S_RoundPointsLimit'		=> $aseco->server->gameinfo->chase['RoundPointsLimit'],
				'S_RoundPointsGap'		=> $aseco->server->gameinfo->chase['RoundPointsGap'],
				'S_GiveUpMax'			=> $aseco->server->gameinfo->chase['GiveUpMax'],
				'S_MinPlayersNb'		=> $aseco->server->gameinfo->chase['MinPlayersNb'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->chase['ForceLapsNb'],
				'S_FinishTimeout'		=> $aseco->server->gameinfo->chase['FinishTimeout'],
				'S_DisplayWarning'		=> $aseco->server->gameinfo->chase['DisplayWarning'],
				'S_UsePlayerClublinks'		=> $aseco->server->gameinfo->chase['UsePlayerClublinks'],
				'S_NbPlayersPerTeamMax'		=> $aseco->server->gameinfo->chase['NbPlayersPerTeamMax'],
				'S_NbPlayersPerTeamMin'		=> $aseco->server->gameinfo->chase['NbPlayersPerTeamMin'],
				'S_CompetitiveMode'		=> $aseco->server->gameinfo->chase['CompetitiveMode'],
				'S_WaypointEventDelay'		=> $aseco->server->gameinfo->chase['WaypointEventDelay'],
				'S_PauseBetweenRound'		=> $aseco->server->gameinfo->chase['PauseBetweenRound'],
				'S_WaitingTimeMax'		=> $aseco->server->gameinfo->chase['WaitingTimeMax'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::KNOCKOUT) {
			// Knockout
			$modesetup = array(
				'S_FinishTimeout'		=> $aseco->server->gameinfo->knockout['FinishTimeout'],
				'S_RoundsPerMap'		=> $aseco->server->gameinfo->knockout['RoundsPerMap'],
				'S_DoubleKnockUntil'		=> $aseco->server->gameinfo->knockout['DoubleKnockUntil'],
				'S_ForceLapsNb'			=> $aseco->server->gameinfo->knockout['ForceLapsNb'],
				'S_ShowMultilapInfo'		=> $aseco->server->gameinfo->knockout['ShowMultilapInfo'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::DOPPLER) {
			// Doppler
			list($x, $y) = explode(',', $aseco->server->gameinfo->doppler['ModuleBestPlayersPosition']);
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->doppler['TimeLimit'],
				'S_LapsSpeedMode'		=> $aseco->server->gameinfo->doppler['LapsSpeedMode'],
				'S_DumpSpeedOnReset'		=> $aseco->server->gameinfo->doppler['DumpSpeedOnReset'],
				'S_KPH'				=> ((strtoupper($aseco->server->gameinfo->doppler['VelocityUnit']) == 'KPH') ? true : false),
				'S_HideModule'			=> (($aseco->server->gameinfo->doppler['ModuleBestPlayersShow'] == false) ? true : false),
				'S_ModulePosDX'			=> (int)$x,
				'S_ModulePosDY'			=> (int)$y,
			);
		}

		// Setup the settings
		$aseco->client->query('SetModeScriptSettings', array_merge($modebase, $modesetup));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
	private function setupCustomScoretable () {
		global $aseco;

//		foreach (range(0,3) as $id) {
//			$aseco->client->query('ConnectFakePlayer');
//		}
//		$aseco->client->query('DisconnectFakePlayer', '*');

		// http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
		$xml = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
		$xml .= '<scorestable version="1">';
		$xml .= ' <properties>';
		$xml .= '  <position x="0.0" y="51.0" z="20.0" />';
		$xml .= '  <headersize x="70.0" y="8.7" />';
		$xml .= '  <modeicon icon="Bgs1|BgEmpty" />';
		$xml .= '  <tablesize x="182.0" y="67.0" />';
		$xml .= '  <taleformat columns="2" lines="8" />';
		$xml .= '  <footersize x="180.0" y="17.0" />';
		$xml .= '</properties>';

		$xml .= ' <settings>';
		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::TEAM_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			$xml .= '  <setting name="TeamsMode" value="True" />';
			$xml .= '  <setting name="TeamsScoresVisibility" value="True" />';
			$xml .= '  <setting name="RevertPlayerCardInTeamsMode" value="False" />';
		}
		else {
			$xml .= '  <setting name="TeamsMode" value="False" />';
			$xml .= '  <setting name="TeamsScoresVisibility" value="False" />';
			$xml .= '  <setting name="RevertPlayerCardInTeamsMode" value="False" />';
		}
		$xml .= '  <setting name="PlayerDarkening" value="True" />';
		$xml .= '  <setting name="PlayerInfoVisibility" value="True" />';
		$xml .= '  <setting name="ServerNameVisibility" value="True" />';
		$xml .= ' </settings>';

		$xml .= '<images>';
		$xml .= ' <background>';
		$xml .= '  <position x="0.0" y="6.0" />';
		$xml .= '  <size width="240.0" height="108.0" />';
//		$xml .= '  <collection>';
//		$xml .= '   <image environment="Canyon" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
//		$xml .= '   <image environment="Valley" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
//		$xml .= '   <image environment="Stadium" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
////		$xml .= '   <image environment="Canyon" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-canyon.dds" />';
////		$xml .= '   <image environment="Valley" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-valley.dds" />';
////		$xml .= '   <image environment="Stadium" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-stadium.dds" />';
//		$xml .= '  </collection>';
		$xml .= ' </background>';
		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::TEAM_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			$xml .= ' <team1>';
			$xml .= '  <image path="file://Media/Manialinks/Trackmania/ScoresTable/teamversus-left.dds" />';
			$xml .= '  <position x="0.0" y="3.8" />';
			$xml .= '  <size width="120.0" height="25.0" />';
			$xml .= ' </team1>';
			$xml .= ' <team2>';
			$xml .= '  <image path="file://Media/Manialinks/Trackmania/ScoresTable/teamversus-right.dds" />';
			$xml .= '  <position x="0.0" y="3.8" />';
			$xml .= '  <size width="120.0" height="25.0" />';
			$xml .= ' </team2>';
		}
		$xml .= '</images>';

//		$xml .= '<columns>';
//		$xml .= ' <column id="LibST_Avatar" action="create" />';
//		$xml .= ' <column id="LibST_Name" action="create" />';
//		$xml .= ' <column id="LibST_ManiaStars" action="create" />';
//		$xml .= ' <column id="LibST_Tools" action="create" />';
//		$xml .= ' <column id="LibST_TMBestTime" action="destroy" />';
//		$xml .= ' <column id="LibST_PrevTime" action="destroy" />';
//		$xml .= ' <column id="LibST_TMStunts" action="destroy" />';
//		$xml .= ' <column id="LibST_TMRespawns" action="destroy" />';
//		$xml .= ' <column id="LibST_TMCheckpoints" action="destroy" />';
//		$xml .= ' <column id="LibST_TMPoints" action="create" />';
//		$xml .= ' <column id="LibST_TMPrevRaceDeltaPoints" action="destroy" />';
//
//		$xml .= ' <column id="LibST_Avatar" action="create">';
//		$xml .= '  <legend>TestFull</legend>';
//		$xml .= '  <defaultvalue>DefaultValue</defaultvalue>';
//		$xml .= '  <width>20.0</width>';
//		$xml .= '  <weight>20.0</weight>';
//		$xml .= '  <textstyle>TextRaceMessageBig</textstyle>';
//		$xml .= '  <textsize>1</textsize>';
//		$xml .= '  <textalign>left</textalign>';
//		$xml .= ' </column>';
//		$xml .= '</columns>';

		$xml .= '</scorestable>';

		$aseco->client->query('TriggerModeScriptEventArray', 'LibScoresTable2_SetStyleFromXml', array('TM', $xml));
	}
}

?>
