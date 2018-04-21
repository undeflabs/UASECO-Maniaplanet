<?php
/*
 * Plugin: Modescript Handler
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Handle the Modescript Callbacks send by the dedicated server and related settings.
 * » Based upon the plugin.modescriptcallback.php from MPAseco, written by the MPAseco team for ShootMania
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
 * Documentation:
 * » https://www.maniaplanet.com/documentation/dedicated-server/references/xml-rpc-scripts
 * » https://www.maniaplanet.com/documentation/dedicated-server/references/settings-list-for-nadeo-gamemodes
 * » https://www.maniaplanet.com/documentation/dedicated-server/references/xml-rpc-methods
 * » http://doc.maniaplanet.com/creation/maniascript/libraries/library-ui.html (2017-05-13 still outdated)
 * » https://www.uaseco.org/dedicated-server/callbacks.php
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
	public $callback_blocklist;

	private $settings;
	private $ui_properties;
	private $forms;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.5');
		$this->setBuild('2018-04-19');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription(new Message('plugin.modescript_handler', 'plugin_description'));

		$this->addDependence('PluginCheckpoints',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onModeScriptCallbackArray',	'onModeScriptCallbackArray');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onShutdown',			'onShutdown');

		$this->registerChatCommand('modescript',		'chat_modescript',		'Adjust some settings for the Modescript plugin (see: /modescript)',	Player::MASTERADMINS);

		// Block some callbacks we did not want to use
		$this->callback_blocklist = array(
			// Nadeo officials
			'Maniaplanet.StartServer_End',
			'Maniaplanet.EndServer_Start',
			'Maniaplanet.EndServer_End',
			'Maniaplanet.StartMatch_End',
			'Maniaplanet.StartTurn_End',
			'Maniaplanet.EndTurn_End',
			'Maniaplanet.EndMatch_Start',
			'Maniaplanet.StartMap_End',
			'Maniaplanet.EndMap_End',
			'Maniaplanet.StartRound_End',
			'Maniaplanet.EndRound_End',
			'Maniaplanet.LoadingMap_Start',
			'Maniaplanet.UnloadingMap_End',

//			'Trackmania.Event.Default',
			'Trackmania.Event.OnPlayerAdded',
			'Trackmania.Event.OnPlayerRemoved',

//			'UI.Event.Default',
//			'UI.Event.OnModuleCustomEvent',
//			'UI.Event.OnModuleShowRequest',
//			'UI.Event.OnModuleHideRequest',
//			'UI.Event.OnModuleStorePurchase',
//			'UI.Event.OnModuleInventoryDrop',
//			'UI.Event.OnModuleInventoryEquip',

			// Knockout.Script.txt					https://forum.maniaplanet.com/viewtopic.php?p=247611
			'KOPlayerAdded',
			'KOPlayerRemoved',
			'KOSendWinner',
		);

		// Stores the modescript_settings.xml settings
		$this->settings			= array();

		// Stores the <ui_properties>
		$this->ui_properties		= array();

		$this->forms = array(
			true	=> array(
				'textcolor'	=> '63A910',
				'symbole'	=> '',
				'text'		=> 'Enabled',
			),
			false	=> array(
				'textcolor'	=> 'C62121',
				'symbole'	=> '',
				'text'		=> 'Disabled',
			),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_modescript ($aseco, $login, $chat_command, $chat_parameter) {

		// Check optional parameter
		if (strtoupper($chat_parameter) == 'RELOAD') {

			// Reload the config
			$this->onSync($aseco, true);

			// Show chat message
			$msg = new Message('plugin.modescript_handler', 'message_reload');
			$msg->sendChatMessage();

		}
//		else if (strtoupper($chat_parameter) == 'UI') {
//
//			if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
//				return;
//			}
//
//			$xml = '';
//
//			$xml .= '<frame pos="0 0">';
//				$declarations .= $this->buildFormScriptDeclarations('Chat');
//				$mouse_click .= $this->buildFormScriptMouseClick('Chat');
//				$xml .= '<frame pos="0 0">';
//				$xml .= $this->buildFormUI('Chat');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Chat Avatar');
//				$mouse_click .= $this->buildFormScriptMouseClick('Chat Avatar');
//				$xml .= '<frame pos="0 -18.4">';
//				$xml .= $this->buildFormUI('Chat Avatar');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Checkpoint List');
//				$mouse_click .= $this->buildFormScriptMouseClick('Checkpoint List');
//				$xml .= '<frame pos="0 -36.8">';
//				$xml .= $this->buildFormUI('Checkpoint List');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Checkpoint Ranking');
//				$mouse_click .= $this->buildFormScriptMouseClick('Checkpoint Ranking');
//				$xml .= '<frame pos="0 -55.2">';
//				$xml .= $this->buildFormUI('Checkpoint Ranking');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Checkpoint Time');
//				$mouse_click .= $this->buildFormScriptMouseClick('Checkpoint Time');
//				$xml .= '<frame pos="0 -73.6">';
//				$xml .= $this->buildFormUI('Checkpoint Time');
//				$xml .= '</frame>';
//			$xml .= '</frame>';
//
//
//			$xml .= '<frame pos="50.5 0">';
//				$declarations .= $this->buildFormScriptDeclarations('Chrono');
//				$mouse_click .= $this->buildFormScriptMouseClick('Chrono');
//				$xml .= '<frame pos="0 0">';
//				$xml .= $this->buildFormUI('Chrono');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Countdown');
//				$mouse_click .= $this->buildFormScriptMouseClick('Countdown');
//				$xml .= '<frame pos="0 -18.4">';
//				$xml .= $this->buildFormUI('Countdown');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Endmap Ladder Recap');
//				$mouse_click .= $this->buildFormScriptMouseClick('Endmap Ladder Recap');
//				$xml .= '<frame pos="0 -36.8">';
//				$xml .= $this->buildFormUI('Endmap Ladder Recap');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Go');
//				$mouse_click .= $this->buildFormScriptMouseClick('Go');
//				$xml .= '<frame pos="0 -55.2">';
//				$xml .= $this->buildFormUI('Go');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Live Info');
//				$mouse_click .= $this->buildFormScriptMouseClick('Live Info');
//				$xml .= '<frame pos="0 -73.6">';
//				$xml .= $this->buildFormUI('Live Info');
//				$xml .= '</frame>';
//			$xml .= '</frame>';
//
//
//			$xml .= '<frame pos="101 0">';
//				$declarations .= $this->buildFormScriptDeclarations('Map Info');
//				$mouse_click .= $this->buildFormScriptMouseClick('Map Info');
//				$xml .= '<frame pos="0 0">';
//				$xml .= $this->buildFormUI('Map Info');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Multilap Info');
//				$mouse_click .= $this->buildFormScriptMouseClick('Multilap Info');
//				$xml .= '<frame pos="0 -18.4">';
//				$xml .= $this->buildFormUI('Multilap Info');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Opponents Info');
//				$mouse_click .= $this->buildFormScriptMouseClick('Opponents Info');
//				$xml .= '<frame pos="0 -36.8">';
//				$xml .= $this->buildFormUI('Opponents Info');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Personal Best and Rank');
//				$mouse_click .= $this->buildFormScriptMouseClick('Personal Best and Rank');
//				$xml .= '<frame pos="0 -55.2">';
//				$xml .= $this->buildFormUI('Personal Best and Rank');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Position');
//				$mouse_click .= $this->buildFormScriptMouseClick('Position');
//				$xml .= '<frame pos="0 -73.6">';
//				$xml .= $this->buildFormUI('Position');
//				$xml .= '</frame>';
//			$xml .= '</frame>';
//
//
//			$xml .= '<frame pos="151.5 0">';
//				$declarations .= $this->buildFormScriptDeclarations('Round Scores');
//				$mouse_click .= $this->buildFormScriptMouseClick('Round Scores');
//				$xml .= '<frame pos="0 0">';
//				$xml .= $this->buildFormUI('Round Scores');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Spectator Info');
//				$mouse_click .= $this->buildFormScriptMouseClick('Spectator Info');
//				$xml .= '<frame pos="0 -18.4">';
//				$xml .= $this->buildFormUI('Spectator Info');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('Speed and Distance');
//				$mouse_click .= $this->buildFormScriptMouseClick('Speed and Distance');
//				$xml .= '<frame pos="0 -36.8">';
//				$xml .= $this->buildFormUI('Speed and Distance');
//				$xml .= '</frame>';
//
//				$declarations .= $this->buildFormScriptDeclarations('WarmUp');
//				$mouse_click .= $this->buildFormScriptMouseClick('WarmUp');
//				$xml .= '<frame pos="0 -55.2">';
//				$xml .= $this->buildFormUI('WarmUp');
//				$xml .= '</frame>';
//
////				$declarations .= $this->buildFormScriptDeclarations('');
////				$mouse_click .= $this->buildFormScriptMouseClick('');
//				$xml .= '<frame pos="0 -73.6">';
//				$xml .= $this->buildFormUI('');
//				$xml .= '</frame>';
//			$xml .= '</frame>';
//
////	TriggerPageAction("PluginModescriptHandler?Action=Save&NAME="^ ID.Value);
//
//
//
//
//			$settings_style = array(
//				'textcolor'		=> '09FF',
//				'seperatorcolor'	=> 'FFFF',
//				'icon'			=> 'Icons64x64_1,ToolLeague1',
//			);
//			$settings_content = array(
//				'title'			=> (new Message('plugin.modescript_handler', 'window_ui_properties'))->finish($player->login),
//				'data'			=> array($xml),
//				'mode'			=> 'pages',
//			);
//			$settings_footer = array(
//				'about_title'		=> 'MODESCRIPT-HANDLER/'. $this->getVersion(),
//			);
//			$maniascript = array(
//				'functions'		=> '',
//				'declarations'		=> $declarations,
//				'mainloop'		=> '',
//				'mouse_click'		=> $mouse_click,
//				'mouse_over'		=> '',
//				'key_press'		=> '',
//			);
//
//			// Create the Window
//			$window = new Window();
//			$window->setStyles($settings_style);
//			$window->setContent($settings_content);
//			$window->setFooter($settings_footer);
//			$window->setManiascript($maniascript);
//			$window->send($player, 0, false);
//
//		}
		else {
			// Show chat message
			$msg = new Message('plugin.modescript_handler', 'message_help');
			$msg->sendChatMessage();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $restart = false) {

		// We need to enable XmlRpc-Callbacks for ModeScripts
		$aseco->client->query('TriggerModeScriptEventArray', 'XmlRpc.EnableCallbacks', array('true'));

		// Set the min. required ApiVersion
		$aseco->client->query('TriggerModeScriptEventArray', 'XmlRpc.SetApiVersion', array(MODESCRIPT_API_VERSION));

		// Write the ModeScript documentation?
		if ($aseco->settings['developer']['write_documentation'] === true) {
			$aseco->client->query('TriggerModeScriptEventArray', 'XmlRpc.GetDocumentation', array($aseco->server->gameinfo->getModeVersion() .'_'. $aseco->server->gameinfo->getModeScriptName() .'.md'));
			$this->writeMethodsDocumentation();
		}

		// Block some Callbacks we do not want to use
		$aseco->client->query('TriggerModeScriptEventArray', 'XmlRpc.BlockCallbacks', $this->callback_blocklist);

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
		$aseco->server->gameinfo->modebase['ChatTime']			= (int)$this->settings['MODEBASE'][0]['CHAT_TIME'][0];
		$aseco->server->gameinfo->modebase['AllowRespawn']		= $aseco->string2bool($this->settings['MODEBASE'][0]['ALLOW_RESPAWN'][0]);
		$aseco->server->gameinfo->modebase['RespawnBehaviour']		= (int)$this->settings['MODEBASE'][0]['RESPAWN_BEHAVIOUR'][0];

		// Rounds +RoundsBase
		$aseco->server->gameinfo->rounds['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->rounds['FinishTimeout']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->rounds['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->rounds['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->rounds['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['DISPLAY_TIME_DIFF'][0]);
		$aseco->server->gameinfo->rounds['RoundsPerMap']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['ROUNDS_PER_MAP'][0];
		$aseco->server->gameinfo->rounds['MapsPerMatch']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['MAPS_PER_MATCH'][0];
		$aseco->server->gameinfo->rounds['UseTieBreak']			= $aseco->string2bool($this->settings['MODESETUP'][0]['ROUNDS'][0]['USE_TIE_BREAK'][0]);
		$aseco->server->gameinfo->rounds['WarmUpNumber']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->rounds['WarmUpDuration']		= (int)$this->settings['MODESETUP'][0]['ROUNDS'][0]['WARM_UP_DURATION'][0];

		// TimeAttack
		$aseco->server->gameinfo->time_attack['TimeLimit']		= (int)$this->settings['MODESETUP'][0]['TIMEATTACK'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->time_attack['WarmUpNumber']		= (int)$this->settings['MODESETUP'][0]['TIMEATTACK'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->time_attack['WarmUpDuration']		= (int)$this->settings['MODESETUP'][0]['TIMEATTACK'][0]['WARM_UP_DURATION'][0];

		// Team +RoundsBase
		$aseco->server->gameinfo->team['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->team['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->team['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['TEAM'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->team['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->team['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['TEAM'][0]['DISPLAY_TIME_DIFF'][0]);
		$aseco->server->gameinfo->team['MaxPointsPerRound']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MAX_POINTS_PER_ROUND'][0];
		$aseco->server->gameinfo->team['PointsGap']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['POINTS_GAP'][0];
		$aseco->server->gameinfo->team['WarmUpNumber']			= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->team['WarmUpDuration']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['WARM_UP_DURATION'][0];
		$aseco->server->gameinfo->team['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MAX_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->team['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['TEAM'][0]['MIN_PLAYERS_PER_TEAM'][0];

		// Laps
		$aseco->server->gameinfo->laps['TimeLimit']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['TIME_LIMIT'][0];
		$aseco->server->gameinfo->laps['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->laps['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->laps['WarmUpNumber']			= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->laps['WarmUpDuration']		= (int)$this->settings['MODESETUP'][0]['LAPS'][0]['WARM_UP_DURATION'][0];

		// Cup +RoundsBase
		$aseco->server->gameinfo->cup['PointsLimit']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['POINTS_LIMIT'][0];
		$aseco->server->gameinfo->cup['FinishTimeout']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['FINISH_TIMEOUT'][0];
		$aseco->server->gameinfo->cup['UseAlternateRules']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CUP'][0]['USE_ALTERNATE_RULES'][0]);
		$aseco->server->gameinfo->cup['ForceLapsNb']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['FORCE_LAPS_NUMBER'][0];
		$aseco->server->gameinfo->cup['DisplayTimeDiff']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CUP'][0]['DISPLAY_TIME_DIFF'][0]);

		$aseco->server->gameinfo->cup['RoundsPerMap']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['ROUNDS_PER_MAP'][0];
		$aseco->server->gameinfo->cup['NbOfWinners']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['NUMBER_OF_WINNERS'][0];
		$aseco->server->gameinfo->cup['NbOfPlayersMax']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MAX_PLAYERS_NUMBER'][0];
		$aseco->server->gameinfo->cup['NbOfPlayersMin']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MIN_PLAYERS_NUMBER'][0];
		$aseco->server->gameinfo->cup['WarmUpNumber']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->cup['WarmUpDuration']			= (int)$this->settings['MODESETUP'][0]['CUP'][0]['WARM_UP_DURATION'][0];
		$aseco->server->gameinfo->cup['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MAX_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->cup['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['CUP'][0]['MIN_PLAYERS_PER_TEAM'][0];

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
		$aseco->server->gameinfo->chase['CompetitiveMode']		= $aseco->string2bool($this->settings['MODESETUP'][0]['CHASE'][0]['COMPETITIVE_MODE'][0]);
		$aseco->server->gameinfo->chase['PauseBetweenRound']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['PAUSE_BETWEEN_ROUND'][0];
		$aseco->server->gameinfo->chase['WaitingTimeMax']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WAITING_TIME_MAX'][0];
		$aseco->server->gameinfo->chase['WaypointEventDelay']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WAYPOINT_EVENT_DELAY'][0];
		$aseco->server->gameinfo->chase['WarmUpNumber']			= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WARM_UP_NUMBER'][0];
		$aseco->server->gameinfo->chase['WarmUpDuration']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['WARM_UP_DURATION'][0];
		$aseco->server->gameinfo->chase['NbPlayersPerTeamMax']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MAX_NUMBER_PLAYERS_PER_TEAM'][0];
		$aseco->server->gameinfo->chase['NbPlayersPerTeamMin']		= (int)$this->settings['MODESETUP'][0]['CHASE'][0]['MIN_NUMBER_PLAYERS_PER_TEAM'][0];

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
//		$this->setupCustomScoretable();


		// Setup the UI
		$this->ui_properties = $this->settings['UI_PROPERTIES'][0];

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['LIVE_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['LIVE_INFO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['CHAT'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['CHAT'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['CHECKPOINT_RANKING'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_RANKING'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
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
		$this->ui_properties['SPECTATOR_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['SPECTATOR_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['SCORESTABLE'][0]['ALT_VISIBLE'][0]	= ((strtoupper($this->ui_properties['SCORESTABLE'][0]['ALT_VISIBLE'][0]) == 'TRUE')		? true : false);
		$this->ui_properties['SCORESTABLE'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['SCORESTABLE'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
		$this->ui_properties['VIEWERS_COUNT'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['VIEWERS_COUNT'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);

		// Send the UI settings
		$this->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		if ($answer['Action'] == 'Vote') {

		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onModeScriptCallbackArray ($aseco, $data) {

		// Bail out if callback is on blocklist
		if (in_array($data[0], $this->callback_blocklist)) {
			return;
		}

		$params = array();
		if (isset($data[1][0]) && !empty($data[1][0])) {
			$params = json_decode($data[1][0], true);
		}

		switch($data[0]) {
			case 'Trackmania.Event.WayPoint':
				$response = array(
					'time'				=> $params['time'],				// Server time when the event occured
					'login'				=> $params['login'],				// PlayerLogin
					'race_time'			=> $params['racetime'],				// Total race time in milliseconds
					'lap_time'			=> $params['laptime'],				// Lap time in milliseconds
					'stunts_score'			=> $params['stuntsscore'],			// Stunts score
					'checkpoint_in_race'		=> ($params['checkpointinrace'] + 1),		// Number of checkpoints crossed since the beginning of the race
					'current_race_checkpoints'	=> $params['curracecheckpoints'],		// Checkpoints times since the beginning of the race
					'checkpoint_in_lap'		=> ($params['checkpointinlap'] + 1),		// Number of checkpoints crossed since the beginning of the lap
					'current_lap_checkpoints'	=> $params['curlapcheckpoints'],		// Checkpoints time since the beginning of the lap
					'is_endrace'			=> $params['isendrace'],			// Is it the finish line checkpoint
					'is_endlap'			=> $params['isendlap'],				// Is it the multilap checkpoint
					'block_id'			=> $params['blockid'],				// Id of the checkpoint block
					'speed'				=> $params['speed'],				// Speed of the player in km/h
					'distance'			=> $params['distance'],				// Distance traveled by the player since the beginning of the race
				);
				if ($response['is_endrace'] === false && $response['is_endlap'] === false) {
					$aseco->releaseEvent('onPlayerCheckpoint', $response);

					// 2017-05-28: Maybe we can use 'checkpoint_in_lap' instead of calling 'Trackmania.GetScores'?
					if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
						// Call 'Trackmania.GetScores' to get 'Trackmania.Scores', required to be up-to-date on each Checkpoint in Laps
						$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetScores', array((string)time()));
					}
				}
				else {
					if ($aseco->server->maps->current->multi_lap === true) {
 						if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
							if ($response['is_endlap'] === true) {
								$aseco->releaseEvent('onPlayerFinishLine', $response);
								$this->playerFinish($response);
							}
						}
						else if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::CUP || $aseco->server->gameinfo->mode == Gameinfo::CHASE) {
							if ($response['is_endrace'] === false && $response['is_endlap'] === true) {
								$aseco->releaseEvent('onPlayerFinishLap', $response);
							}
							else if ($response['is_endrace'] === true && $response['is_endlap'] === false) {
								$aseco->releaseEvent('onPlayerFinishLine', $response);
							}
							else if ($response['is_endrace'] === true && $response['is_endlap'] === true) {
								$aseco->releaseEvent('onPlayerFinishLap', $response);
								$aseco->releaseEvent('onPlayerFinishLine', $response);
						}
						}
					}
					else {
						$aseco->releaseEvent('onPlayerFinishLine', $response);
					}
				}
				if ($response['is_endrace'] === true) {
					if ($aseco->warmup_phase == false && $aseco->server->gameinfo->mode != Gameinfo::TEAM) {
						// Call 'Trackmania.GetScores' to get 'Trackmania.Scores'
						$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetScores', array((string)time()));
					}
					$this->playerFinish($response);
				}
		    		break;





			case 'Trackmania.Event.StartLine':
				$aseco->releaseEvent('onPlayerStartLine', $params);
		    		break;





			case 'Trackmania.Event.StartCountdown':
				$aseco->releaseEvent('onPlayerStartCountdown', $params);
		    		break;





			case 'Trackmania.Event.Respawn':
				$response = array(
					'time'			=> $params['time'],					// Server time when the event occured
					'login'			=> $params['login'],					// PlayerLogin
					'nb_respawns'		=> $params['nbrespawns'],				// Number of respawns since the beginning of the race
					'race_time'		=> $params['racetime'],					// Total race time in milliseconds
					'lap_time'		=> $params['laptime'],					// Lap time in milliseconds
					'stunts_score'		=> $params['stuntsscore'],				// Stunts score
					'checkpoint_in_race'	=> ($params['checkpointinrace'] + 1),			// Number of checkpoints crossed since the beginning of the race
					'checkpoint_in_lap'	=> ($params['checkpointinlap'] + 1),			// Number of checkpoints crossed since the beginning of the lap
					'speed'			=> $params['speed'],					// Speed of the player in km/h
					'distance'		=> $params['distance'],					// Distance traveled by the player since the beginning of the race
				);
				$aseco->releaseEvent('onPlayerRespawn', $response);
		    		break;





			case 'Trackmania.Event.GiveUp':
				$aseco->releaseEvent('onPlayerGiveUp', $params);
		    		break;





			case 'Trackmania.Event.Stunt':
				$response = array(
					'time'			=> $params['time'],					// Server time when the event occured
					'login'			=> $params['login'],					// PlayerLogin
					'race_time'		=> $params['racetime'],					// Total race time in milliseconds
					'lap_time'		=> $params['laptime'],					// Lap time in milliseconds
					'stunts_score'		=> $params['stuntsscore'],				// Stunts score
					'figure'		=> $params['figure'],					// Name of the figure
					'angle'			=> $params['angle'],					// Angle of the car
					'points'		=> $params['points'],					// Point awarded by the figure
					'combo'			=> $params['combo'],					// Combo counter
					'is_straight'		=> $params['isstraight'],				// Is the car straight
					'is_reverse'		=> $params['isreverse'],				// Is the car reversed
					'is_masterjump'		=> $params['ismasterjump'],
					'factor'		=> $params['factor'],					// Points multiplier
				);
				$aseco->releaseEvent('onPlayerStunt', $response);
		    		break;





			case 'Maniaplanet.StartPlayLoop':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Playing ['.  $params['count'] .']');
				}
				$aseco->releaseEvent('onBeginPlaying', $params['count']);
		    		break;





			case 'Maniaplanet.EndPlayLoop':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Playing ['. $params['count'] .']');
				}
				$aseco->releaseEvent('onEndPlaying', $params['count']);
		    		break;





			case 'Maniaplanet.Podium_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Podium');
				}
				$aseco->releaseEvent('onBeginPodium', null);
		    		break;





			case 'Maniaplanet.Podium_End':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Podium');
				}
				$aseco->releaseEvent('onEndPodium', null);
		    		break;





			case 'Maniaplanet.ChannelProgression_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Channel Progression');
				}
				$aseco->releaseEvent('onBeginChannelProgression', $params['time']);
		    		break;





			case 'Maniaplanet.ChannelProgression_End':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Channel Progression');
				}
				$aseco->releaseEvent('onEndChannelProgression', $params['time']);
		    		break;





			case 'Maniaplanet.LoadingMap_End':
				// Cleanup rankings
				$aseco->server->rankings->reset();

				// Refresh the current round point system (Rounds, Team and Cup)
				if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetPointsRepartition', array((string)time()));
				}

// BEGIN 2017-05-25 work-a-round for https://forum.maniaplanet.com/viewtopic.php?p=285553#p285553
				if ($params['restarted'] === true && $aseco->server->maps->current->uid == $params['map']['uid']) {
//				if ($params['restarted'] === true) {
// END
					$aseco->restarting = true;							// Map was restarted
				}
				else {
					$aseco->restarting = false;							// Not restarted
				}
				$aseco->loadingMap($params['map']['uid']);
		    		break;





			case 'Maniaplanet.UnloadingMap_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Unloading Map');
				}
				$aseco->unLoadingMap($params['map']['uid']);
				$aseco->releaseEvent('onUnloadingMap', $params['map']['uid']);
		    		break;





			case 'Maniaplanet.StartMap_Start':
				// Call 'Trackmania.GetScores' to get 'Trackmania.Scores'
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.GetScores', array((string)time()));

				// Call 'Trackmania.WarmUp.GetStatus' to get 'Trackmania.WarmUp.Status'
				$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.WarmUp.GetStatus', array((string)time()));

				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Map');
				}
				$aseco->releaseEvent('onBeginMap', $params['map']['uid']);
				break;





			case 'Maniaplanet.EndMap_Start':
				$aseco->endMap();
				break;





			case 'Maniaplanet.StartRound_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Round ['. $params['count'] .']');
				}
				$aseco->releaseEvent('onBeginRound', $params['count']);
				break;





			case 'Maniaplanet.EndRound_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Round ['. $params['count'] .']');
				}
				$aseco->releaseEvent('onEndRound', $params['count']);
				break;






			case 'Maniaplanet.StartMatch_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Match ['.  $params['count'] .']');
				}
				$aseco->releaseEvent('onBeginMatch',  $params['count']);
				break;





			case 'Maniaplanet.EndMatch_End':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Match ['.  $params['count'] .']');
				}
				$aseco->releaseEvent('onEndMatch',  $params['count']);
				break;





			case 'Maniaplanet.StartTurn_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] Begin Turn ['.  $params['count'] .']');
				}
				$aseco->releaseEvent('onBeginTurn', $params['count']);
				break;





			case 'Maniaplanet.EndTurn_Start':
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] End Turn ['.  $params['count'] .']');
				}
				$aseco->releaseEvent('onEndTurn', $params['count']);
				break;





//			case 'LibXmlRpc_Pause':
//				if ($aseco->settings['developer']['log_events']['common'] == true) {
//					$aseco->console('[Event] ModeScript Pause changed');
//				}
//				$aseco->releaseEvent('onModeScriptPauseChanged', $aseco->string2bool($params[0]));
//				break;





			case 'Trackmania.Scores':
				if ($aseco->server->gameinfo->mode === Gameinfo::TEAM && isset($params['teams']) && is_array($params['teams'])) {
					$rank_blue = PHP_INT_MAX;
					$rank_red = PHP_INT_MAX;

					// Check which team has a higher score
					if ($params['teams'][0]['mappoints'] > $params['teams'][1]['mappoints']) {
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
						'rank'				=> $rank_blue,
						'pid'				=> 0,
						'login'				=> '*team:blue',
						'nickname'			=> '$08FTeam Blue',
						'round_points'			=> $params['teams'][0]['roundpoints'],
						'map_points'			=> $params['teams'][0]['mappoints'],
						'match_points'			=> $params['teams'][0]['matchpoints'],
						'best_race_time'		=> 0,
						'best_race_respawns'		=> 0,
						'best_race_checkpoints'		=> array(),
						'best_lap_time'			=> 0,
						'best_lap_respawns'		=> 0,
						'best_lap_checkpoints'		=> array(),
						'prev_race_time'		=> -1,
						'prev_race_respawns'		=> -1,
						'prev_race_checkpoints'		=> array(),
						'stunts_score'			=> 0,
						'prev_stunts_score'		=> 0,
					);
					$aseco->server->rankings->update($update);

					// Store "Team Red"
					$update = array(
						'rank'				=> $rank_red,
						'pid'				=> 1,
						'login'				=> '*team:red',
						'nickname'			=> '$F50Team Red',
						'round_points'			=> $params['teams'][1]['roundpoints'],
						'map_points'			=> $params['teams'][1]['mappoints'],
						'match_points'			=> $params['teams'][1]['matchpoints'],
						'best_race_time'		=> 0,
						'best_race_respawns'		=> 0,
						'best_race_checkpoints'		=> array(),
						'best_lap_time'			=> 0,
						'best_lap_respawns'		=> 0,
						'best_lap_checkpoints'		=> array(),
						'prev_race_time'		=> -1,
						'prev_race_respawns'		=> -1,
						'prev_race_checkpoints'		=> array(),
						'stunts_score'			=> 0,
						'prev_stunts_score'		=> 0,
					);
					$aseco->server->rankings->update($update);

					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Player Ranking Updated (Team)');
					}
					$aseco->releaseEvent('onPlayerRankingUpdated', null);
				}
				else {
					$found_improvement = false;
					if (isset($params['players']) && is_array($params['players'])) {
						foreach ($params['players'] as $item) {
							if ($player = $aseco->server->players->getPlayerByLogin($item['login'])) {
								$update = array(
									'rank'				=> $item['rank'],
									'pid'				=> $player->pid,
									'login'				=> $player->login,
									'nickname'			=> $player->nickname,
									'round_points'			=> $item['roundpoints'],
									'map_points'			=> $item['mappoints'],
									'match_points'			=> $item['matchpoints'],
									'best_race_time'		=> $item['bestracetime'],		// Best race time in milliseconds
									'best_race_respawns'		=> $item['bestracerespawns'],		// Number of respawn during best race
									'best_race_checkpoints'		=> $item['bestracecheckpoints'],	// Checkpoints times during best race
									'best_lap_time'			=> $item['bestlaptime'],		// Best lap time in milliseconds
									'best_lap_respawns'		=> $item['bestlaprespawns'],		// Number of respawn during best lap
									'best_lap_checkpoints'		=> $item['bestlapcheckpoints'],		// Checkpoints times during best lap
									'prev_race_time'		=> $item['prevracetime'],
									'prev_race_respawns'		=> $item['prevracerespawns'],
									'prev_race_checkpoints'		=> $item['prevracecheckpoints'],
									'stunts_score'			=> $item['stuntsscore'],
									'prev_stunts_score'		=> $item['prevstuntsscore'],
								);

								$rank = $aseco->server->rankings->getRankByLogin($item['login']);
								if (($update['map_points'] > 0 || $rank->map_points > $update['map_points']) || ($update['match_points'] > 0 || $rank->match_points > $update['match_points']) || ($update['best_race_time'] > 0 || $rank->best_race_time > $update['best_race_time']) || ($update['best_lap_time'] > 0 || $rank->best_lap_time > $update['best_lap_time'])) {
									// Update current ranking cache
									$aseco->server->rankings->update($update);

									// Lets send the event 'onPlayerRankingUpdated'
									$found_improvement = true;
								}

								// Special handling for 'round_points', details at 'docs/nadeo/Detailed description of Trackmania.Scores.txt'
								if ($update['round_points'] > 0) {
									$aseco->releaseEvent('onPlayerRoundFinish', $update);
								}
							}
						}
					}
					if ($found_improvement == true) {
						if ($aseco->settings['developer']['log_events']['common'] == true) {
							$aseco->console('[Event] Player Ranking Updated (Players)');
						}
						$aseco->releaseEvent('onPlayerRankingUpdated', null);
					}
				}
		    		break;





			case 'Maniaplanet.WarmUp.Status':
			case 'Trackmania.WarmUp.Status':
				if ($aseco->warmup_phase !== $params['active']) {
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] WarmUp Status Changed');
					}
					$aseco->warmup_phase = $params['active'];
					$aseco->releaseEvent('onWarmUpStatusChanged', $params);
				}
		    		break;





			case 'Maniaplanet.WarmUp.Start':
			case 'Trackmania.WarmUp.Start':
				$aseco->warmup_phase = true;
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] WarmUp Status changed to "WarmUp starting"');
				}
				$aseco->releaseEvent('onWarmUpStatusChanged', $aseco->warmup_phase);
		    		break;





			case 'Maniaplanet.WarmUp.End':
			case 'Trackmania.WarmUp.End':
				$aseco->warmup_phase = false;
				if ($aseco->settings['developer']['log_events']['common'] == true) {
					$aseco->console('[Event] WarmUp Status changed to "WarmUp ending"');
				}
				$aseco->releaseEvent('onWarmUpStatusChanged', $aseco->warmup_phase);
		    		break;





			case 'Trackmania.WarmUp.StartRound':
				$aseco->releaseEvent('onWarmUpRoundChanged', $params);
		    		break;





			case 'Trackmania.WarmUp.EndRound':
				$aseco->releaseEvent('onWarmUpRoundChanged', $params);
		    		break;





			case 'Trackmania.Event.OnCommand':
				if ($aseco->settings['developer']['log_events']['all_types'] == true) {
					$aseco->console('[Event] ModeScript Command');
				}
				$aseco->releaseEvent('onModeScriptCommand', $params);
				break;





			case 'Trackmania.PointsRepartition':
				if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS) {
					$aseco->server->gameinfo->rounds['PointsRepartition'] = $params['pointsrepartition'];
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $params['pointsrepartition']);
				}
				else if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
					$aseco->server->gameinfo->team['PointsRepartition'] = $params['pointsrepartition'];
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $params['pointsrepartition']);
				}
				else if ($aseco->server->gameinfo->mode == Gameinfo::CUP) {
					$aseco->server->gameinfo->cup['PointsRepartition'] = $params['pointsrepartition'];
					if ($aseco->settings['developer']['log_events']['common'] == true) {
						$aseco->console('[Event] Points Repartition Loaded');
					}
					$aseco->releaseEvent('onPointsRepartitionLoaded', $params['pointsrepartition']);
				}
		    		break;





			case 'Trackmania.UI.Properties':
				$aseco->releaseEvent('onUiProperties', $params);
		    		break;





			case 'Maniaplanet.Mode.UseTeams':
				$aseco->releaseEvent('onModeUseTeams', $params);
		    		break;





			case 'Maniaplanet.Pause.Status':
				$aseco->releaseEvent('onPauseStatus', $params);
		    		break;





			case 'Maniaplanet.StartServer_Start':
				// When changing Gamemode force all Plugins to resync
				if ($aseco->changing_to_gamemode !== false && $params['mode']['updated'] === true) {
					$aseco->console('[ModescriptHandler] Gamemode change detected, running "'. $aseco->server->gameinfo->getModeScriptName() .'"...');

					// Refresh server game info
					$aseco->server->getCurrentGameInfo();

//					// Reload settings
//					$this->onSync($aseco, true);

					// Trigger 'onModeScriptChanged' event
					$aseco->releaseEvent('onModeScriptChanged', $params['mode']['name']);

					// Reset status
					$aseco->changing_to_gamemode = false;
				}
		    		break;





			case 'XmlRpc.Documentation':
				// Called 'XmlRpc.GetDocumentation'
				if (isset($data[1][1]) && !empty($data[1][1])) {
					$aseco->console('[ModescriptHandler] Generating documentation of the Gamemode...');

					@mkdir('docs/Gamemodes/', 0755, true);

					$destination = 'docs/Gamemodes/'. $params['responseid'];
					if (file_put_contents($destination, $data[1][1], LOCK_EX) !== false) {
						$aseco->console('[ModescriptHandler] ... successfully written to "'. $destination .'"!');
					}
					else {
						$aseco->console('[ModescriptHandler] ... could not write to "'. $destination .'"!');
					}
				}
		    		break;





			default:
				$aseco->console('[ModescriptHandler] Unsupported callback at onModeScriptCallbackArray() received: ['. $data[0] .'], please report this at '. UASECO_WEBSITE);
		    		break;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {
		if (isset($this->settings['UI_PROPERTIES'])) {
			// Setup the default UI
			$this->ui_properties = $this->settings['UI_PROPERTIES'][0];

			// Transform 'TRUE' or 'FALSE' from string to boolean
			$this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
			$this->ui_properties['LIVE_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['LIVE_INFO'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
			$this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
			$this->ui_properties['CHAT'][0]['VISIBLE'][0]			= ((strtoupper($this->ui_properties['CHAT'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
			$this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
			$this->ui_properties['CHECKPOINT_RANKING'][0]['VISIBLE'][0]	= ((strtoupper($this->ui_properties['CHECKPOINT_RANKING'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
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
			$this->ui_properties['SPECTATOR_INFO'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['SPECTATOR_INFO'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);
			$this->ui_properties['SCORESTABLE'][0]['ALT_VISIBLE'][0]	= ((strtoupper($this->ui_properties['SCORESTABLE'][0]['ALT_VISIBLE'][0]) == 'TRUE')		? true : false);
			$this->ui_properties['SCORESTABLE'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['SCORESTABLE'][0]['VISIBLE'][0]) == 'TRUE')			? true : false);
			$this->ui_properties['VIEWERS_COUNT'][0]['VISIBLE'][0]		= ((strtoupper($this->ui_properties['VIEWERS_COUNT'][0]['VISIBLE'][0]) == 'TRUE')		? true : false);

			// Send the UI settings
			$this->setupUserInterface();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function playerFinish ($response) {
		global $aseco;

		// If no Map info bail out immediately
		if ($aseco->server->maps->current->id === 0) {
			return;
		}

		// Bail out if in warm-up (method "GetValidationReplay" returns "Not in race." in warm-up)
		if ($aseco->warmup_phase === true) {
			return;
		}

		// If relay server or not in Play status, bail out immediately
		if ($aseco->server->isrelay || $aseco->current_status != 4) {
			return;
		}

		// Check for valid player
		if (!$player = $aseco->server->players->getPlayerByLogin($response['login'])) {
			return;
		}

		// Build a record object with the current finish information
		$finish			= new Record();
		$finish->player		= $player;
		if ($response['is_endrace'] === true) {
			$finish->score	= $response['race_time'];
		}
		else if ($response['is_endlap'] === true && $aseco->server->maps->current->multi_lap === true) {
			$finish->score	= $response['lap_time'];
		}
		$finish->checkpoints	= (isset($aseco->plugins['PluginCheckpoints']->checkpoints[$player->login]) ? $aseco->plugins['PluginCheckpoints']->checkpoints[$player->login]->current['cps'] : array());
		$finish->date		= strftime('%Y-%m-%d %H:%M:%S');
		$finish->new		= false;
		$finish->map		= clone $aseco->server->maps->current;
		unset($finish->map->mx);	// reduce memory usage

		// Throw prefix 'player finishes' event
		$aseco->releaseEvent('onPlayerFinishPrefix', $finish);

		// Throw main 'player finishes' event
		$aseco->releaseEvent('onPlayerFinish', $finish);

		// Throw posfix 'player finishes' event (checkpoints)
		$aseco->releaseEvent('onPlayerFinishPostfix', $finish);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function checkModescriptVersions () {
		global $aseco;

		$aseco->console('[ModescriptHandler] Checking version from dedicated Server Modescripts...');

		$path = $aseco->settings['dedicated_installation'] .'GameData/Scripts/';
		if (!is_dir($path)) {
			trigger_error('[ModescriptHandler] Please setup <dedicated_installation> in [config/UASECO.xml]!', E_USER_ERROR);
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
		$ui .= ' <map_info visible="'. $aseco->bool2string($this->ui_properties['MAP_INFO'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['MAP_INFO'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['MAP_INFO'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['MAP_INFO'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <live_info visible="'. $aseco->bool2string($this->ui_properties['LIVE_INFO'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['LIVE_INFO'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['LIVE_INFO'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['LIVE_INFO'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <opponents_info visible="'. $aseco->bool2string($this->ui_properties['OPPONENTS_INFO'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <chat visible="'. $aseco->bool2string($this->ui_properties['CHAT'][0]['VISIBLE'][0]) .'" offset="'. $aseco->formatFloat($this->ui_properties['CHAT'][0]['OFFSET'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHAT'][0]['OFFSET'][0]['Y'][0]) .'" linecount="'. $this->ui_properties['CHAT'][0]['LINECOUNT'][0] .'"/>';
		$ui .= ' <checkpoint_list visible="'. $aseco->bool2string($this->ui_properties['CHECKPOINT_LIST'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_LIST'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <checkpoint_ranking visible="'. $aseco->bool2string($this->ui_properties['CHECKPOINT_RANKING'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHECKPOINT_RANKING'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_RANKING'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_RANKING'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <round_scores visible="'. $aseco->bool2string($this->ui_properties['ROUND_SCORES'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['ROUND_SCORES'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <countdown visible="'. $aseco->bool2string($this->ui_properties['COUNTDOWN'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['COUNTDOWN'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <go visible="'. $aseco->bool2string($this->ui_properties['GO'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <chrono visible="'. $aseco->bool2string($this->ui_properties['CHRONO'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHRONO'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <speed_and_distance visible="'. $aseco->bool2string($this->ui_properties['SPEED_AND_DISTANCE'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPEED_AND_DISTANCE'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <personal_best_and_rank visible="'. $aseco->bool2string($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['PERSONAL_BEST_AND_RANK'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <position visible="'. $aseco->bool2string($this->ui_properties['POSITION'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['POSITION'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['POSITION'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['POSITION'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <checkpoint_time visible="'. $aseco->bool2string($this->ui_properties['CHECKPOINT_TIME'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['CHECKPOINT_TIME'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <chat_avatar visible="'. $aseco->bool2string($this->ui_properties['CHAT_AVATAR'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <warmup visible="'. $aseco->bool2string($this->ui_properties['WARMUP'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['WARMUP'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <endmap_ladder_recap visible="'. $aseco->bool2string($this->ui_properties['ENDMAP_LADDER_RECAP'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <multilap_info visible="'. $aseco->bool2string($this->ui_properties['MULTILAP_INFO'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <spectator_info visible="'. $aseco->bool2string($this->ui_properties['SPECTATOR_INFO'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['SPECTATOR_INFO'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPECTATOR_INFO'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['SPECTATOR_INFO'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= ' <scorestable alt_visible="'. $aseco->bool2string($this->ui_properties['SCORESTABLE'][0]['ALT_VISIBLE'][0]) .'" visible="'. $aseco->bool2string($this->ui_properties['SCORESTABLE'][0]['VISIBLE'][0]) .'"/>';
		$ui .= ' <viewers_count visible="'. $aseco->bool2string($this->ui_properties['VIEWERS_COUNT'][0]['VISIBLE'][0]) .'" pos="'. $aseco->formatFloat($this->ui_properties['VIEWERS_COUNT'][0]['POS'][0]['X'][0]) .' '. $aseco->formatFloat($this->ui_properties['VIEWERS_COUNT'][0]['POS'][0]['Y'][0]) .' '. $aseco->formatFloat($this->ui_properties['VIEWERS_COUNT'][0]['POS'][0]['Z'][0]) .'"/>';
		$ui .= '</ui_properties>';

		$aseco->client->query('TriggerModeScriptEventArray', 'Trackmania.UI.SetProperties', array($ui));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setUserInterfaceVisibility ($field, $value = true) {

		if (array_key_exists(strtoupper($field), $this->ui_properties)) {
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

		if (array_key_exists(strtoupper($field), $this->ui_properties) && array_key_exists('POS', $this->ui_properties[strtoupper($field)][0]) && count($values) == 3) {
			$this->ui_properties[strtoupper($field)][0]['POS'][0]['X'][0] = $aseco->formatFloat($values[0]);
			$this->ui_properties[strtoupper($field)][0]['POS'][0]['Y'][0] = $aseco->formatFloat($values[1]);
			$this->ui_properties[strtoupper($field)][0]['POS'][0]['Z'][0] = $aseco->formatFloat($values[2]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getUserInterfaceField ($field) {

		if (array_key_exists(strtoupper($field), $this->ui_properties)) {
			return $this->ui_properties[strtoupper($field)][0];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function writeMethodsDocumentation () {
		global $aseco;

		$aseco->console('[ModescriptHandler] Generating documentation of the Methods from the dedicated server...');

		$methods = $aseco->client->query('system.listMethods');

		$docs = LF;
		$docs .= '# Dedicated Server' .LF;
		$docs .= '###### Methods from API version: '. XMLRPC_API_VERSION .LF.LF;
		$docs .= '***' .LF.LF;
		foreach ($methods as $method) {
			$docs .= '### ['. $method .'](_#'. $method .')' .LF;

			$help = $aseco->client->query('system.methodHelp', $method);
			$help = str_replace(
				array(
					"''",
					"That's",
					"it's",
					'<i>',
					'</i>',
					"'",
				),
				array(
					"an empty string",
					"That is",
					"it is",
					'`',
					'`',
					'`',
				),
				$help
			);
			$docs .= $help .LF.LF;

			$signatures = $aseco->client->query('system.methodSignature', $method);
			$docs .= '#### Description' .LF;
			$docs .= '	'. array_shift($signatures[0]) .' '. $method .'('. implode(', ', $signatures[0]) .')';
			$docs .= LF.LF;
			$docs .= '***';
			$docs .= LF.LF;
		}

		@mkdir('docs/Dedicated-Server/', 0755, true);

		$destination = 'docs/Dedicated-Server/Methods.md';
		if (file_put_contents($destination, $docs, LOCK_EX) !== false) {
			$aseco->console('[ModescriptHandler] ... successfully written to "'. $destination .'"!');
		}
		else {
			$aseco->console('[ModescriptHandler] ... could not write to "'. $destination .'"!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// https://www.maniaplanet.com/documentation/dedicated-server/references/settings-list-for-nadeo-gamemodes
	public function setupModescriptSettings () {
		global $aseco;

		// ModeBase
		$modebase = array(
			'S_ChatTime'				=> $aseco->server->gameinfo->modebase['ChatTime'],
			'S_AllowRespawn'			=> $aseco->server->gameinfo->modebase['AllowRespawn'],
			'S_RespawnBehaviour'			=> $aseco->server->gameinfo->modebase['RespawnBehaviour'],
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
				'S_RoundsPerMap'		=> $aseco->server->gameinfo->rounds['RoundsPerMap'],
				'S_MapsPerMatch'		=> $aseco->server->gameinfo->rounds['MapsPerMatch'],
				'S_UseTieBreak'			=> $aseco->server->gameinfo->rounds['UseTieBreak'],
				'S_WarmUpNb'			=> $aseco->server->gameinfo->rounds['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->rounds['WarmUpDuration'],
			);
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK) {
			// TimeAttack
			$modesetup = array(
				'S_TimeLimit'			=> $aseco->server->gameinfo->time_attack['TimeLimit'],
				'S_WarmUpNb'			=> $aseco->server->gameinfo->time_attack['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->time_attack['WarmUpDuration'],
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
				'S_WarmUpNb'			=> $aseco->server->gameinfo->team['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->team['WarmUpDuration'],
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
				'S_WarmUpNb'			=> $aseco->server->gameinfo->laps['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->laps['WarmUpDuration'],
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
				'S_NbOfPlayersMax'		=> $aseco->server->gameinfo->cup['NbOfPlayersMax'],
				'S_NbOfPlayersMin'		=> $aseco->server->gameinfo->cup['NbOfPlayersMin'],
				'S_WarmUpNb'			=> $aseco->server->gameinfo->cup['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->cup['WarmUpDuration'],
				'S_NbPlayersPerTeamMax'		=> $aseco->server->gameinfo->cup['NbPlayersPerTeamMax'],
				'S_NbPlayersPerTeamMin'		=> $aseco->server->gameinfo->cup['NbPlayersPerTeamMin'],
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
				'S_CompetitiveMode'		=> $aseco->server->gameinfo->chase['CompetitiveMode'],
				'S_PauseBetweenRound'		=> $aseco->server->gameinfo->chase['PauseBetweenRound'],
				'S_WaitingTimeMax'		=> $aseco->server->gameinfo->chase['WaitingTimeMax'],
				'S_WaypointEventDelay'		=> $aseco->server->gameinfo->chase['WaypointEventDelay'],
				'S_WarmUpNb'			=> $aseco->server->gameinfo->chase['WarmUpNumber'],
				'S_WarmUpDuration'		=> $aseco->server->gameinfo->chase['WarmUpDuration'],
				'S_NbPlayersPerTeamMax'		=> $aseco->server->gameinfo->chase['NbPlayersPerTeamMax'],
				'S_NbPlayersPerTeamMin'		=> $aseco->server->gameinfo->chase['NbPlayersPerTeamMin'],
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

		// Release event, but not while start-up
		if ($aseco->startup_phase != true) {
			$aseco->releaseEvent('onModeScriptSettingsChanged', null);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildFormUI ($name) {

		$title = $name;
		$id = str_replace(' ', '', $name);
		$var = strtoupper(str_replace(' ', '_', $name));

		$xml = '<quad pos="0 0" z-index="0" size="48.5 16.4" bgcolor="FFFFFF33"/>';
		$xml .= '<quad pos="0 0" z-index="0" size="48.5 4" bgcolor="0099FFFF"/>';
		$xml .= '<label pos="1 -0.5" z-index="0.01" size="46.5 3.1" textsize="1.4" textfont="Oswald" text="'. $title .'"/>';

		if (!empty($name)) {
			// Checkbox
			$xml .= '<frame pos="1 -5.5" z-index="0.02">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="14.8 4" bgcolor="FFFF" bgcolorfocus="DDDF" id="'. $id .'QuadBackground" scriptevents="1"/>';
			$xml .= '<label pos="1 -2" z-index="0.02" size="4 3" valign="center2" textsize="1.5" textcolor="'. $this->forms[$this->ui_properties[$var][0]['VISIBLE'][0]]['textcolor'] .'" text="'. $this->forms[$this->ui_properties[$var][0]['VISIBLE'][0]]['symbole'] .'" id="'. $id .'CheckboxLabelIcon"/>';
			$xml .= '<label pos="5 -2" z-index="0.02" size="9.8 3" valign="center2" textsize="0.6" textcolor="555555" textfont="Oswald" text="'. $this->forms[$this->ui_properties[$var][0]['VISIBLE'][0]]['text'] .'" id="'. $id .'CheckboxLabelValue"/>';
			$xml .= '</frame>';
		}

		$pos = array(
			'X'	=> 1,
			'Y'	=> 16.8,
			'Z'	=> 32.6,
		);
		if ($name == 'CHAT') {
			foreach (array('X','Y') as $direction) {
				if (isset($this->ui_properties[$var][0]['OFFSET'][0][$direction][0])) {
					// Input for (int) or (float) with arrows
					$xml .= '<frame pos="'. $pos[$direction] .' -10.8" z-index="0.02">';
					$xml .= '<quad pos="0 0" z-index="0" size="14.8 4" bgcolor="FFFF"/>';
					$xml .= '<entry pos="10.8 -2" z-index="0.01" size="10.8 4" halign="right" valign="center2" textcolor="555" textsize="0.1" textfont="Oswald" default="'. $this->ui_properties[$var][0]['OFFSET'][0][$direction][0] .' " id="'. $id .'EntryPos'. $direction .'"/>';
					$xml .= '<label pos="1 -2" z-index="0.02" size="8 4" valign="center2" textsize="1" scale="0.6" textcolor="555555" textfont="Oswald" text="'. $direction .'"/>';
					$xml .= '<frame pos="10.8 0" z-index="0.01">';
					$xml .= '<quad pos="0 0" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowUpPos'. $direction .'" scriptevents="1"/>';
					$xml .= '<quad pos="0 -2" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowDownPos'. $direction .'" scriptevents="1"/>';
					$xml .= '<label pos="2.125 -0.9" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏶"/>';
					$xml .= '<label pos="2.125 -2.8" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏷"/>';
					$xml .= '</frame>';
					$xml .= '</frame>';
				}
			}

			// Input for (int) or (float) with arrows
			$xml .= '<frame pos="'. $pos['Z'] .' -10.8" z-index="0.02">';
			$xml .= '<quad pos="0 0" z-index="0" size="14.8 4" bgcolor="FFFF"/>';
			$xml .= '<entry pos="10.8 -2" z-index="0.01" size="10.8 4" halign="right" valign="center2" textcolor="555" textsize="0.1" textfont="Oswald" default="'. $this->ui_properties[$var][0]['LINECOUNT'][0] .' " id="'. $id .'EntryPosLinecount"/>';
			$xml .= '<label pos="1 -2" z-index="0.02" size="8 4" valign="center2" textsize="1" scale="0.6" textcolor="555555" textfont="Oswald" text="Lines"/>';
			$xml .= '<frame pos="10.8 0" z-index="0.01">';
			$xml .= '<quad pos="0 0" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowUpPosLinecount" scriptevents="1"/>';
			$xml .= '<quad pos="0 -2" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowDownPosLinecount" scriptevents="1"/>';
			$xml .= '<label pos="2.125 -0.9" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏶"/>';
			$xml .= '<label pos="2.125 -2.8" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏷"/>';
			$xml .= '</frame>';
			$xml .= '</frame>';
		}
		else {
			foreach (array('X','Y','Z') as $direction) {
				if (isset($this->ui_properties[$var][0]['POS'][0][$direction][0])) {
					// Input for (int) or (float) with arrows
					$xml .= '<frame pos="'. $pos[$direction] .' -10.8" z-index="0.02">';
					$xml .= '<quad pos="0 0" z-index="0" size="14.8 4" bgcolor="FFFF"/>';
					$xml .= '<entry pos="10.8 -2" z-index="0.01" size="10.8 4" halign="right" valign="center2" textcolor="555" textsize="0.1" textfont="Oswald" default="'. $this->ui_properties[$var][0]['POS'][0][$direction][0] .' " id="'. $id .'EntryPos'. $direction .'"/>';
					$xml .= '<label pos="1 -2" z-index="0.02" size="8 4" valign="center2" textsize="1" scale="0.6" textcolor="555555" textfont="Oswald" text="'. $direction .'"/>';
					$xml .= '<frame pos="10.8 0" z-index="0.01">';
					$xml .= '<quad pos="0 0" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowUpPos'. $direction .'" scriptevents="1"/>';
					$xml .= '<quad pos="0 -2" z-index="0.01" size="4 2" bgcolor="AAAF" bgcolorfocus="09FF" id="'. $id .'ButtonArrowDownPos'. $direction .'" scriptevents="1"/>';
					$xml .= '<label pos="2.125 -0.9" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏶"/>';
					$xml .= '<label pos="2.125 -2.8" z-index="0.02" size="4 2" halign="center" valign="center2" textsize="1" textcolor="FFF" text="⏷"/>';
					$xml .= '</frame>';
					$xml .= '</frame>';
				}
			}
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildFormScriptDeclarations ($name) {
		global $aseco;

		$id = str_replace(' ', '', $name);
		$var = strtoupper(str_replace(' ', '_', $name));

		$boolean = ucfirst($aseco->bool2string($this->ui_properties[$var][0]['VISIBLE'][0]));

$maniascript = <<<EOL
	declare CMlLabel ModescriptHandler{$id}CheckboxLabelIcon <=> (Page.GetFirstChild("{$id}CheckboxLabelIcon") as CMlLabel);
	declare CMlLabel ModescriptHandler{$id}CheckboxLabelValue <=> (Page.GetFirstChild("{$id}CheckboxLabelValue") as CMlLabel);
	declare Boolean ModescriptHandler{$id}Visible = $boolean;
EOL;
		return $maniascript;
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildFormScriptMouseClick ($name) {

		$id = str_replace(' ', '', $name);

$maniascript = <<<EOL
	if (Event.ControlId == "{$id}QuadBackground") {
		if (ModescriptHandler{$id}Visible == True) {
			ModescriptHandler{$id}CheckboxLabelIcon.TextColor	= TextLib::ToColor("{$this->forms[false]['textcolor']}");
			ModescriptHandler{$id}CheckboxLabelIcon.Value		= "{$this->forms[false]['symbole']}";
			ModescriptHandler{$id}CheckboxLabelValue.Value		= "{$this->forms[false]['text']}";
			ModescriptHandler{$id}Visible = False;
		}
		else if (ModescriptHandler{$id}Visible == False) {
			ModescriptHandler{$id}CheckboxLabelIcon.TextColor	= TextLib::ToColor("{$this->forms[true]['textcolor']}");
			ModescriptHandler{$id}CheckboxLabelIcon.Value		= "{$this->forms[true]['symbole']}";
			ModescriptHandler{$id}CheckboxLabelValue.Value		= "{$this->forms[true]['text']}";
			ModescriptHandler{$id}Visible = True;
		}
	}
EOL;
		return $maniascript;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
	private function setupCustomScoretable () {
		global $aseco;

//		$aseco->client->query('DisconnectFakePlayer', '*');
//		foreach (range(0,50) as $id) {
//			$aseco->client->query('ConnectFakePlayer');
//		}

		// http://doc.maniaplanet.com/dedicated-server/customize-scores-table.html
		$xml = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
		$xml .= '<scorestable version="1">';
		$xml .= ' <properties>';
		$xml .= '  <position x="0.0" y="51.0" z="20.0"/>';
		$xml .= '  <headersize x="70.0" y="8.7"/>';
		$xml .= '  <modeicon icon="Bgs1|BgEmpty"/>';
		$xml .= '  <tablesize x="182.0" y="67.0"/>';
		$xml .= '  <taleformat columns="2" lines="8"/>';
		$xml .= '  <footersize x="180.0" y="17.0"/>';
		$xml .= '</properties>';

		$xml .= ' <settings>';
		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::TEAM_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			$xml .= '  <setting name="TeamsMode" value="True"/>';
			$xml .= '  <setting name="TeamsScoresVisibility" value="True"/>';
			$xml .= '  <setting name="RevertPlayerCardInTeamsMode" value="False"/>';
		}
		else {
			$xml .= '  <setting name="TeamsMode" value="False"/>';
			$xml .= '  <setting name="TeamsScoresVisibility" value="False"/>';
			$xml .= '  <setting name="RevertPlayerCardInTeamsMode" value="False"/>';
		}
		$xml .= '  <setting name="PlayerDarkening" value="True"/>';
		$xml .= '  <setting name="PlayerInfoVisibility" value="True"/>';
		$xml .= '  <setting name="ServerNameVisibility" value="True"/>';
		$xml .= ' </settings>';

		$xml .= '<images>';
		$xml .= ' <background>';
		$xml .= '  <position x="0.0" y="6.0"/>';
		$xml .= '  <size width="240.0" height="108.0"/>';
//		$xml .= '  <collection>';
//		$xml .= '   <image environment="Canyon" path="http://maniacdn.net/undef.de/dedicated-server/ScoresTable2.Script.txt/uaseco-bg-canyon.dds"/>';
//		$xml .= '   <image environment="Valley" path="http://maniacdn.net/undef.de/dedicated-server/ScoresTable2.Script.txt/uaseco-bg-canyon.dds"/>';
//		$xml .= '   <image environment="Stadium" path="http://maniacdn.net/undef.de/dedicated-server/ScoresTable2.Script.txt/uaseco-bg-canyon.dds"/>';
////		$xml .= '   <image environment="Canyon" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-canyon.dds"/>';
////		$xml .= '   <image environment="Valley" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-valley.dds"/>';
////		$xml .= '   <image environment="Stadium" path="file://Media/Manialinks/Trackmania/ScoresTable/bg-stadium.dds"/>';
//		$xml .= '  </collection>';
		$xml .= ' </background>';
		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::TEAM_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::CHASE) {
			$xml .= ' <team1>';
			$xml .= '  <image path="file://Media/Manialinks/Trackmania/ScoresTable/teamversus-left.dds"/>';
			$xml .= '  <position x="0.0" y="3.8"/>';
			$xml .= '  <size width="120.0" height="25.0"/>';
			$xml .= ' </team1>';
			$xml .= ' <team2>';
			$xml .= '  <image path="file://Media/Manialinks/Trackmania/ScoresTable/teamversus-right.dds"/>';
			$xml .= '  <position x="0.0" y="3.8v';
			$xml .= '  <size width="120.0" height="25.0"/>';
			$xml .= ' </team2>';
		}
		$xml .= '</images>';

//		$xml .= '<columns>';
//		$xml .= ' <column id="LibST_Avatar" action="createv';
//		$xml .= ' <column id="LibST_Name" action="create"/>';
//		$xml .= ' <column id="LibST_ManiaStars" action="create"/>';
//		$xml .= ' <column id="LibST_Tools" action="create"/>';
//		$xml .= ' <column id="LibST_TMBestTime" action="destroy"/>';
//		$xml .= ' <column id="LibST_PrevTime" action="destroy"/>';
//		$xml .= ' <column id="LibST_TMStunts" action="destroyv';
//		$xml .= ' <column id="LibST_TMRespawns" action="destroy"/>';
//		$xml .= ' <column id="LibST_TMCheckpoints" action="destroy"/>';
//		$xml .= ' <column id="LibST_TMPoints" action="create"/>';
//		$xml .= ' <column id="LibST_TMPrevRaceDeltaPoints" action="destroy"/>';
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
