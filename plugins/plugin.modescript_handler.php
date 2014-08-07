<?php
/* Plugin: Modescript Handler
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Handle the Modescript Callbacks send by the dedicated server and related settings.
 * » Based upon the plugin.modescriptcallback.php from MPAseco, written by the MPAseco team for ShootMania
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-07
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
 * Documentation:
 * » http://doc.maniaplanet.com/dedicated-server/xmlrpc/xml-rpc-scripts.html
 * » http://doc.maniaplanet.com/dedicated-server/settings-list.html
 * » http://doc.maniaplanet.com/dedicated-server/xmlrpc/methods/latest.html
 * » http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
 * » docs/Dedicated Server/ListCallbacks_2013-04-16.html
 *
 * Dependencies:
 * - none
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

	// stores/inits <custom_ui> block fields
	private $custom_ui = array(
		'global'		=> true,	// 2014-07-25: Works, for all working settings below
		'challenge_info'	=> true,	// 2014-07-25: Works
		'chat'			=> true,	// 2014-07-25: Works
		'scoretable'		=> true,	// 2014-07-25: Works
//		'notice'		=> true,	// 2014-07-25: No effect, because this messages are removed(?)
//		'net_infos'		=> true,	// 2014-07-25: No effect
//		'checkpoint_list'	=> true,	// 2014-07-25: No effect
//		'round_scores'		=> true,	// 2014-07-25: No effect
	);

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Handle the Modescript Callbacks send by the dedicated server and related settings.');

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEndRound',			'onEndRound');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onModeScriptCallbackArray',	'onModeScriptCallbackArray');
		$this->registerEvent('onModeScriptCallback',		'onModeScriptCallback');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setCustomUIField ($field, $value) {
		if ( isset($this->custom_ui[$field]) ) {
			$this->custom_ui[$field] = $value;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCustomUIBlock () {
		global $aseco;

		$xml  = '<custom_ui>';
		$xml .= '<global visible="'. $aseco->bool2string($this->custom_ui['global']) .'"/>';
		$xml .= '<challenge_info visible="'. $aseco->bool2string($this->custom_ui['challenge_info']) .'"/>';
		$xml .= '<chat visible="'. $aseco->bool2string($this->custom_ui['chat']) .'"/>';
		$xml .= '<scoretable visible="'. $aseco->bool2string($this->custom_ui['scoretable']) .'"/>';
//		$xml .= '<notice visible="'. $aseco->bool2string($this->custom_ui['notice']) .'"/>';
//		$xml .= '<net_infos visible="'. $aseco->bool2string($this->custom_ui['net_infos']) .'"/>';
//		$xml .= '<checkpoint_list visible="'. $aseco->bool2string($this->custom_ui['checkpoint_list']) .'"/>';
//		$xml .= '<round_scores visible="'. $aseco->bool2string($this->custom_ui['round_scores']) .'"/>';
		$xml .= '</custom_ui>';
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		// http://doc.maniaplanet.com/dedicated-server/settings-list.html


		// ModeBase
		$modebase = array(
			'S_ChatTime'				=> 20,			// Chat time at the end of the map
			'S_AllowRespawn'			=> true,		// Allow the players to respawn or not
			'S_WarmUpDuration'			=> 1,			// Duration of the warm up phase (<= 0 to disable)
			'S_UseScriptCallbacks'			=> true,		// Turn on/off the script callbacks, useful for server manager
			'S_UseLegacyCallbacks'			=> false,		// Enable the legacy callbacks (default value: True)
			'S_ScoresTableStylePath'		=> '',			// Try to load a scores table style from an XML file
		);

		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
			// Rounds (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> 99,			// Points limit (<= 0 to disable)
				'S_FinishTimeout'		=> -1,			// Finish timeout (<= 0 to disable)
				'S_UseAlternateRules'		=> false,		// Use alternate rules
				'S_ForceLapsNb'			=> -1,			// Force number of laps (<= 0 to disable)
				'S_DisplayTimeDiff'		=> false,		// Display time difference at checkpoint

				// Rounds
				'S_UseTieBreak'			=> true,		// Continue to play the map until the tie is broken
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK) {
			// TimeAttack
			$modesetup = array(
				'S_TimeLimit'			=> 1200,		// Time limit
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Team  (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> 5,			// Points limit (<= 0 to disable)
				'S_FinishTimeout'		=> -1,			// Finish timeout (<= 0 to disable)
				'S_UseAlternateRules'		=> false,		// Use alternate rules
				'S_ForceLapsNb'			=> -1,			// Force number of laps (<= 0 to disable)
				'S_DisplayTimeDiff'		=> false,		// Display time difference at checkpoint

				// Team
				'S_MaxPointsPerRound'		=> 6,			// The maxium number of points attributed to the first player to cross the finish line
				'S_PointsGap'			=> 1,			// The number of points lead a team must have to win the map
				'S_UsePlayerClublinks'		=> false,		// Use the players clublinks, or otherwise use the default teams
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
			// Laps
			$modesetup = array(
				'S_FinishTimeout'		=> -1,			// Finish timeout (<= 0 to disable)
				'S_ForceLapsNb'			=> -1,			// Number of Laps (<= 0 to disable)
				'S_TimeLimit'			=> 1200,		// Time limit (<= 0 to disable)
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
			// Cup (+RoundsBase)
			$modesetup = array(
				// RoundsBase
				'S_PointsLimit'			=> 99,			// Points limit (<= 0 to disable)
				'S_FinishTimeout'		=> -1,			// Finish timeout (<= 0 to disable)
				'S_UseAlternateRules'		=> false,		// Use alternate rules
				'S_ForceLapsNb'			=> -1,			// Force number of laps (<= 0 to disable)
				'S_DisplayTimeDiff'		=> false,		// Display time difference at checkpoint

				// Cup
				'S_RoundsPerMap'		=> 5,			// Rounds per map
				'S_NbOfWinners'			=> 3,			// Number of winners
				'S_WarmUpDuration'		=> 2,			// Duration of the warm up phase (<= 0 to disable)
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TEAMATTACK) {
			// TeamAttack
			$modesetup = array(
				'S_TimeLimit'			=> 300,			// Time limit
				'S_MinPlayerPerClan'		=> 3,			// Minimum number of players per clan
				'S_MaxPlayerPerClan'		=> 3,			// Maximum number of players per clan
				'S_MaxClanNb'			=> -1,			// Maximum number of clans (<= 0 to disable)
			);
		}
//		else if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
//			// Stunt
//			$modesetup = array(
//
//			);
//		}

		// Setup the settings
		$aseco->client->query('SetModeScriptSettings', array_merge($modebase, $modesetup));


		// Show/Hide the small scores table displayed on the right of the screen when finishing the map.
		// http://doc.maniaplanet.com/dedicated-server/xmlrpc/xml-rpc-scripts.html
		$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'UI_DisplaySmallScoresTable', 'False');

//		foreach (range(0,20) as $id) {
//			$aseco->client->queryIgnoreResult('ConnectFakePlayer');
//		}
//		$aseco->client->queryIgnoreResult('DisconnectFakePlayer', '*');

		// http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<scorestable version="1">';
		$xml .= ' <properties>';
		$xml .= '  <position x="0.0" y="55.0" z="20.0" />';
		$xml .= '  <headersize x="70.0" y="8.7" />';
		$xml .= '  <modeicon icon="Icons64x64_1|ToolLeague1" />';
		$xml .= '  <tablesize x="182.0" y="69.0" />';
		$xml .= '  <taleformat columns="2" lines="8" />';
		$xml .= '  <footersize x="180.0" y="20.0" />';
		$xml .= '</properties>';

		$xml .= '<images>';
		$xml .= ' <background>';
		$xml .= '  <position x="0.0" y="6.0" />';
		$xml .= '  <size width="240.0" height="115.0" />';
//		$xml .= '  <collection>';
//		$xml .= '   <image environment="Canyon" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
//		$xml .= '   <image environment="Valley" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
//		$xml .= '   <image environment="Stadium" path="http://static.undef.name/scorestable/uaseco-bg-canyon.dds?20140615213000.dds" />';
////		$xml .= '   <image environment="Canyon" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-canyon.dds" />';
////		$xml .= '   <image environment="Valley" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-valley.dds" />';
////		$xml .= '   <image environment="Stadium" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-stadium.dds" />';
//		$xml .= '  </collection>';
		$xml .= ' </background>';
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

		$aseco->client->queryIgnoreResult('TriggerModeScriptEventArray', 'LibScoresTable2_SetStyleFromXml', array('TM', $xml));

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco, $round) {

		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
			$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $uid) {

		// On restart it is required to set the most settings again,
		// because a restart resets the most settings in a Modescript.
		// Details: http://forum.maniaplanet.com/viewtopic.php?p=221734#p221734
		$this->onSync($aseco);

		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			// Call 'LibXmlRpc_GetTeamsScores' to get 'LibXmlRpc_TeamsScores'
			$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'LibXmlRpc_GetTeamsScores', '');
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

		switch($name) {
			// [0]=Login
			case 'LibXmlRpc_OnStartLine':
				$aseco->releaseEvent('onPlayerStartLine', $params[0]);
		    		break;


			// [0]=Login
			case 'LibXmlRpc_OnStartCountdown':
				$aseco->releaseEvent('onPlayerStartCountdown', $params[0]);
		    		break;



			// [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=WaypointIsFinishLine, [5]=CurrentLapTime, [6]=LapWaypointNumber, [7]=WaypointIsFinishLap
			case 'LibXmlRpc_OnWayPoint':
				// Go ahead, only when
				// - 'WaypointIsFinishLine' == true
				// - 'WaypointIsFinishLap' == true
				if ( ($aseco->string2bool($params[4]) === true) || ($aseco->string2bool($params[7]) === true) ) {
					if ($aseco->warmup_phase == false && $aseco->server->gameinfo->mode != Gameinfo::TEAM) {
						// Call 'LibXmlRpc_GetPlayerRanking' to get 'LibXmlRpc_PlayerRanking'
						$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'LibXmlRpc_GetPlayerRanking', $params[0]);
					}
				}

				if ($aseco->string2bool($params[4]) === false) {
					$aseco->releaseEvent('onPlayerCheckpoint', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], (int)$params[6]));
				}
				else if ($aseco->string2bool($params[4]) === true) {
					$aseco->releaseEvent('onPlayerFinishLine', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], (int)$params[6]));
				}
				if ($aseco->string2bool($params[7]) === true) {
					$aseco->releaseEvent('onPlayerFinishLap', array($params[0], $params[1], (int)$params[2], ((int)$params[3]+1), (int)$params[5], (int)$params[6]));
				}
				if ( ($aseco->string2bool($params[4]) === true) || ($aseco->string2bool($params[7]) === true) ) {
					// Player finished the Map or the Lap
					$aseco->playerFinish(array($params[0], (int)$params[2]));
				}
		    		break;



			// [0]=Login, [1]=WaypointBlockId, [2]=WaypointIndexRace, [3]=WaypointIndexLap, [4]=TotalRespawns
			case 'LibXmlRpc_OnRespawn':
$aseco->dump('LibXmlRpc_OnRespawn: ', $params );
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



			// [0]=IndexOfMap
			case 'LibXmlRpc_LoadingMap':
				// Cleanup rankings
				$aseco->server->rankings->reset();

				// Refresh the current round point system (Rounds, Team and Cup)
				if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'Rounds_GetPointsRepartition', '');
				}

				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Loading Map');
				}
				$aseco->releaseEvent('onLoadingMap', (int)$params[0]);
		    		break;



			// [0]=IndexOfMap
			case 'LibXmlRpc_UnloadingMap':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Unloading Map');
				}
				$aseco->releaseEvent('onUnloadingMap', (int)$params[0]);
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
				$aseco->client->queryIgnoreResult('TriggerModeScriptEvent', 'LibXmlRpc_GetWarmUp', '');
				if ($aseco->string2bool($params[2]) === true) {
					$aseco->restarting = true;			// Map was restarted
					$this->onSync($aseco);				// It is required to re-setup the settings for the Modescripts: http://forum.maniaplanet.com/viewtopic.php?p=221734#p221734
				}
				else {
					$aseco->restarting = false;			// No Restart
				}
				$aseco->beginMap($params[1]);
				break;



			// [0]=IndexOfMap, [1]=Uid
			case 'LibXmlRpc_EndMap':
				$aseco->endMap(array((int)$params[0], $params[1]));
				break;



			// [0]=NbMatch
			case 'LibXmlRpc_BeginMatch':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Match');
				}
				$aseco->releaseEvent('onBeginMatch', (int)$params[0]);
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



			// [0]=Rank, [1]=Login, [2]=NickName, [3]=TeamId, [4]=IsSpectator, [5]=IsAway, [6]=BestTime, [7]=Zone, [8]=RoundScore, [9]=BestCheckpoints, [10]=TotalScore
			case 'LibXmlRpc_PlayerRanking':
				if ( isset($params[1]) ) {
					// Explode string and convert to integer
					$cps = array_map('intval', explode(',', $params[9]));
					if (count($cps) == 1 && $cps[0] === -1) {
						$cps = array();
					}

					$player = $aseco->server->players->getPlayer($params[1]);
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
						$aseco->console('[Event] Player Ranking Updated');
					}
					$aseco->releaseEvent('onPlayerRankingUpdated', null);
				}
		    		break;



			// [0]=Login, [1]=Rank, [2]=BestCheckpoints, [3]=TeamId, [4]=IsSpectator, [5]=IsAway, [6]=BestTime, [7]=Zone, [8]=RoundScore, [9]=TotalScore
			case 'LibXmlRpc_PlayersRanking':
				if (count($params) > 0) {
					foreach ($params as $item) {
						$rank = explode(':', $item);

						// Explode string and convert to integer
						$cps = array_map('intval', explode(',', $rank[2]));
						if (count($cps) == 1 && $cps[0] === -1) {
							$cps = array();
						}

						$player = $aseco->server->players->getPlayer($rank[0]);
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

					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Player Ranking Updated');
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
						$aseco->console('[Event] Player Ranking Updated');
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



			case 'LibXmlRpc_PlayersTimes':
			case 'LibXmlRpc_PlayersScores':
			case 'LibXmlRpc_TeamsMode':			// Maybe used later
				// Ignore this, not required yet.
		    		break;



			default:
				$aseco->console('[onModeScriptCallbackArray] Unsupported callback received: ['. $name .'], please report this!');
		    		break;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptCallback ($aseco, $data) {

		$name = $data[0];
		$params = isset($data[1]) ? $data[1] : '';

		$aseco->console('[onModeScriptCallback] Unsupported callback received: ['. $name .'], please report this!');
	}
}

?>
