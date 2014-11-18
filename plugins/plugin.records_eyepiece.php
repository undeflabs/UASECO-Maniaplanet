<?php
/*
 * Plugin: Records Eyepiece
 * ~~~~~~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Records-Eyepiece.php
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Contributors:	.anDy, Bueddl
 * Version:		1.1.0
 * Date:		2014-11-16
 * Copyright:		2009 - 2014 by undef.de
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
 *  - plugins/plugin.modescript_handler.php	Required
 *  - plugins/plugin.local_records.php		Required, for Datasbase access and LocalRecordsWidget
 *  - plugins/plugin.welcome_center.php		Required, for TopVisitors
 *  - plugins/plugin.mania_exchange_info.php	Required, for MapWidget, MXMapInfoWindow and <placement>-Placeholder
 *  - plugins/plugin.rasp_jukebox.php		Required, for MaplistWindow
 *  - plugins/plugin.rasp.php			Required, only if you enable the TopRankingsWidget (enabled by default)
 *  - plugins/plugin.dedimania.php		Required, only if you enable the DedimaniaWidget (enabled by default)
 *  - plugins/plugin.donate.php			Optional, only if you want the TopDonators or the DonationWidget at Score
 *  - plugins/plugin.music_server.php		Optional, only if you want the MusicWidget
 *  - plugins/plugin.nouse_betting.php		Optional, only if you want the Betwins-Widget at Score.
 *  - plugins/plugin.round_points.php		Recommended, useful if you want to setup own point limits in the RoundScoreWidget
 *  - plugins/plugin.mania_karma.php		Recommended, useful if you want to have the Karma-Widget.
 */

/* The following manialink id's are used in this plugin (the 918 part of id can be changed on trouble):
 *
 * ManialinkID's
 * ~~~~~~~~~~~~~
 * MainWindow
 * SubWindow
 * ActionKeys
 * PlacementWidgetRace
 * PlacementWidgetScore
 * PlacementWidgetAlways
 * PlacementWidgetGamemode
 * DedimaniaRecordsWidget			(at all Gamemodes, except 'Stunts')
 * LocalRecordsWidget				(at all Gamemodes)
 * LiveRankingsWidget				(at all Gamemodes, but not at Score)
 * RoundScoreWidget
 * MapWidget
 * MusicWidget
 * CheckpointCountWidget
 * ToplistWidget
 * ClockWidget
 * RecordsEyepieceAdvertiserWidget
 * PlayerSpectatorWidget
 * AddToFavoriteWidget
 * GamemodeWidget
 * VisitorsWidget
 * CurrentRankingWidget
 * MapCountWidget
 * LadderLimitWidget
 * ManiaExchangeWidget
 * TopRankingsWidgetAtScore
 * TopWinnersWidgetAtScore
 * MostRecordsWidgetAtScore
 * MostFinishedWidgetAtScore
 * TopPlaytimeWidgetAtScore
 * TopDonatorsWidgetAtScore
 * TopNationsWidgetAtScore
 * TopMapsWidgetAtScore
 * TopVotersWidgetAtScore
 * TopBetwinsWidgetAtScore
 * TopAverageTimesWidgetAtScore
 * TopRoundscoreWidgetAtScore
 * NextGamemodeWidgetAtScore
 * NextEnvironmentWidgetAtScore
 * WinningPayoutWidgetAtScore
 * DonationWidgetAtScore
 * TopWinningPayoutsWidgetAtScore
 * TopVisitorsWidgetAtScore
 * TopActivePlayersWidgetAtScore
 * ImagePreloadBox1 to ImagePreloadBox4		id for manialink ImagePreload
 *
 * ActionID's
 * ~~~~~~~~~~
 *  382009003					id for action pressed Key F7 to toggle Widget (same ManialinkId as plugin.fufi.widgets.php for compatibility with other Plugins)
 *  closeMainWindow
 *  closeSubWindow
 *  showDedimaniaRecordsWindow
 *  showLocalRecordsWindow
 *  showLiveRankingsWindow
 *  showLastCurrentNextMapWindow
 *  showManiaExchangeMapInfoWindow
 *  showMusiclistWindow
 *  dropCurrentJukedSong			(and refresh MusiclistWindow)
 *  showHelpWindow
 *  showTopNationsWindow
 *  showTopRankingsWindow
 *  showTopWinnersWindow
 *  showMostRecordsWindow
 *  showMostFinishedWindow
 *  showTopPlaytimeWindow
 *  showTopDonatorsWindow
 *  showTopMapsWindow
 *  showTopVotersWindow
 *  showTopActivePlayersWindow
 *  showTopWinningPayoutWindow
 *  showToplistWindow
 *  showTopBetwinsWindow
 *  showTopRoundscoreWindow
 *  showTopVisitorsWindow
 *  showTopContinentsWindow
 *  showMaplistWindow
 *  showMapAuthorlistWindow
 *  showMaplistFilterWindow
 *  showMaplistWindowFilterOnlyCanyonMaps
 *  showMaplistWindowFilterOnlyStadiumMaps
 *  showMaplistWindowFilterOnlyValleyMaps
 *  showMaplistWindowFilterJukeboxedMaps
 *  showMaplistWindowFilterNoRecentMaps
 *  showMaplistWindowFilterOnlyRecentMaps
 *  showMaplistWindowFilterOnlyMapsWithoutRank
 *  showMaplistWindowFilterOnlyMapsWithRank
 *  showMaplistWindowFilterOnlyMapsNoGoldTime
 *  showMaplistWindowFilterOnlyMapsNoAuthorTime
 *  showMaplistWindowFilterMapsWithMoodSunrise
 *  showMaplistWindowFilterMapsWithMoodDay
 *  showMaplistWindowFilterMapsWithMoodSunset
 *  showMaplistWindowFilterMapsWithMoodNight
 *  showMaplistWindowFilterOnlyMultilapMaps
 *  showMaplistWindowFilterNoMultilapMaps
 *  showMaplistWindowFilterOnlyMapsNoSilverTime
 *  showMaplistWindowFilterOnlyMapsNoBronzeTime
 *  showMaplistWindowFilterOnlyMapsNotFinished
 *  showMaplistWindowFilterAuthor01 to showMaplistWindowFilterAuthor80	action select an Author from the MapAuthorlistWindow to filter
 *  showMaplistSortingWindow
 *  showMaplistWindowSortingBestPlayerRank
 *  showMaplistWindowSortingWorstPlayerRank
 *  showMaplistWindowSortingShortestAuthorTime
 *  showMaplistWindowSortingLongestAuthorTime
 *  showMaplistWindowSortingNewestMapsFirst
 *  showMaplistWindowSortingOldestMapsFirst
 *  showMaplistWindowSortingByMapname
 *  showMaplistWindowSortingByAuthorname
 *  showMaplistWindowSortingByKarmaBestMapsFirst
 *  showMaplistWindowSortingByKarmaWorstMapsFirst
 *  showMaplistWindowSortingByAuthorNation
 *  askDropMapJukebox
 *  dropMapJukebox
 *  donateAmount01				Donate amount 1 (20 by default)
 *  donateAmount02				Donate amount 2 (50 by default)
 *  donateAmount03				Donate amount 3 (100 by default)
 *  donateAmount04				Donate amount 4 (200 by default)
 *  donateAmount05				Donate amount 5 (500 by default)
 *  donateAmount06				Donate amount 6 (1000 by default)
 *  donateAmount07				Donate amount 7 (1500 by default)
 *  donateAmount08				Donate amount 8 (2000 by default)
 *  donateAmount09				Donate amount 9 (2500 by default)
 *  donateAmount10				Donate amount 10 (5000 by default)
 *  chatCommand01 to chatCommand25		action of chat-commands in the <placement_widget>
 *  addMapToJukebox01 to addMapToJukebox20	action add a Map from the MaplistWindow to the Jukebox
 */

	// Start the plugin
	$_PLUGIN = new PluginRecordsEyepiece();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRecordsEyepiece extends Plugin {
	public $config		= array();
	public $scores		= array();
	public $cache		= array();
	public $templates	= array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.1.0');
		$this->setAuthor('undef.de');
		$this->setDescription('A fully configurable HUD for all type of records and gamemodes.');

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManiaExchangeInfo',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginRasp',			Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginRaspJukebox',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginDedimania',			Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginDonate',			Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginMusicServer',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginNouseBetting',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginRoundPoints',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginManiaKarma',		Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnectPrepare',	'onPlayerDisconnectPrepare');
		$this->registerEvent('onPlayerDisconnect',		'onPlayerDisconnect');
		$this->registerEvent('onPlayerInfoChanged',		'onPlayerInfoChanged');
		$this->registerEvent('onPlayerRankingUpdated',		'onPlayerRankingUpdated');
		$this->registerEvent('onPlayerFinish1',			'onPlayerFinish1');
		$this->registerEvent('onPlayerWins',			'onPlayerWins');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onWarmUpStatusChanged',		'onWarmUpStatusChanged');
//		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onUnloadingMap',			'onUnloadingMap');
		$this->registerEvent('onBeginRound',			'onBeginRound');
		$this->registerEvent('onEndRound',			'onEndRound');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onBeginMap1',			'onBeginMap1');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap1',			'onEndMap1');
		$this->registerEvent('onEverySecond',			'onEverySecond');
		$this->registerEvent('onKarmaChange',			'onKarmaChange');
		$this->registerEvent('onDonation',			'onDonation');
		$this->registerEvent('onMapListChanged',		'onMapListChanged');
		$this->registerEvent('onMapListModified',		'onMapListModified');
		$this->registerEvent('onJukeboxChanged',		'onJukeboxChanged');
		$this->registerEvent('onMusicboxReloaded',		'onMusicboxReloaded');
		$this->registerEvent('onShutdown',			'onShutdown');
		$this->registerEvent('onVotingRestartMap',		'onVotingRestartMap');		// from plugin.vote_manager.php

		$this->registerChatCommand('togglewidgets',		'chat_togglewidgets',	'Toggle the display of the Records-Eyepiece widgets (see: /eyepiece)',		Player::PLAYERS);
		$this->registerChatCommand('eyepiece',			'chat_eyepiece',	'Displays the help for the Records-Eyepiece widgets (see: /eyepiece)',		Player::PLAYERS);
		$this->registerChatCommand('elist',			'chat_elist',		'Lists maps currently on the server (see: /eyepiece)',				Player::PLAYERS);
		$this->registerChatCommand('estat',			'chat_estat',		'Display one of the MoreRankingLists (see: /eyepiece)',				Player::PLAYERS);

		$this->registerChatCommand('eyeset',			'chat_eyeset',		'Adjust some settings for the Records-Eyepiece plugin (see: /eyepiece)',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $reload = null) {


		// Check for the right UASECO-Version
		$uaseco_min_version = '1.0.0';
		if ( defined('UASECO_VERSION') ) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[RecordsEyepiece] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[RecordsEyepiece] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}


		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/records_eyepiece.xml', true, true)) {
			trigger_error('[RecordsEyepiece] Could not read/parse config file "config/records_eyepiece.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['RECORDS_EYEPIECE'];

		// Static settings
		$this->config['LineHeight'] = 1.8;

		$aseco->console('[RecordsEyepiece] ********************************************************');
		$aseco->console('[RecordsEyepiece] Starting version '. $this->getVersion() .' - Maniaplanet');
		$aseco->console('[RecordsEyepiece] Parsed "config/records_eyepiece.xml" successfully, starting checks...');

		if ( !isset($this->config['MUSIC_WIDGET'][0]['ADVERTISE'][0]) ) {
			$this->config['MUSIC_WIDGET'][0]['ADVERTISE'][0] = 'true';
		}

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['MAP_WIDGET'][0]['ENABLED'][0]					= ((strtoupper($this->config['MAP_WIDGET'][0]['ENABLED'][0]) == 'TRUE')					? true : false);
		$this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0]			= ((strtoupper($this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['CLOCK_WIDGET'][0]['ENABLED'][0]					= ((strtoupper($this->config['CLOCK_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['NEXT_ENVIRONMENT_WIDGET'][0]['ENABLED'][0]			= ((strtoupper($this->config['NEXT_ENVIRONMENT_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['NEXT_GAMEMODE_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['NEXT_GAMEMODE_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['PLAYER_SPECTATOR_WIDGET'][0]['ENABLED'][0]			= ((strtoupper($this->config['PLAYER_SPECTATOR_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0]			= ((strtoupper($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['WINNING_PAYOUT'][0]['ENABLED'][0]				= ((strtoupper($this->config['WINNING_PAYOUT'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['OPERATOR'][0]			= ((strtoupper($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['OPERATOR'][0]) == 'TRUE')		? true : false);
		$this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['ADMIN'][0]			= ((strtoupper($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['ADMIN'][0]) == 'TRUE')			? true : false);
		$this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['MASTERADMIN'][0]		= ((strtoupper($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['MASTERADMIN'][0]) == 'TRUE')		? true : false);
		$this->config['DONATION_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['DONATION_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['LADDERLIMIT_WIDGET'][0]['ROC_SERVER'][0]				= ((strtoupper($this->config['LADDERLIMIT_WIDGET'][0]['ROC_SERVER'][0]) == 'TRUE')			? true : false);
		$this->config['VISITORS_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['VISITORS_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['FAVORITE_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['TOPLIST_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['TOPLIST_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['MANIAEXCHANGE_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['MANIAEXCHANGE_WIDGET'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['MUSIC_WIDGET'][0]['ENABLED'][0]					= ((strtoupper($this->config['MUSIC_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['MUSIC_WIDGET'][0]['ADVERTISE'][0]				= ((strtoupper($this->config['MUSIC_WIDGET'][0]['ADVERTISE'][0]) == 'TRUE')				? true : false);
		$this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['ENABLED'][0]			= ((strtoupper($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
		$this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['ENABLED'][0]			= ((strtoupper($this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['ENABLED'][0]) == 'TRUE')		? true : false);
		$this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0]				= ((strtoupper($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0]) == 'TRUE')				? true : false);
		$this->config['NICEMODE'][0]['ENABLED'][0]					= ((strtoupper($this->config['NICEMODE'][0]['ENABLED'][0]) == 'TRUE')					? true : false);
		$this->config['NICEMODE'][0]['FORCE'][0]					= ((strtoupper($this->config['NICEMODE'][0]['FORCE'][0]) == 'TRUE')					? true : false);
		$this->config['STYLE'][0]['WINDOW'][0]['LIGHTBOX'][0]['ENABLED'][0]		= ((strtoupper($this->config['STYLE'][0]['WINDOW'][0]['LIGHTBOX'][0]['ENABLED'][0]) == 'TRUE')		? true : false);
		$this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['DISPLAY'][0]		= ((strtoupper($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['DISPLAY'][0]) == 'AVERAGE')	? true : false);
		$this->config['UI_PROPERTIES'][0]['MAP_INFO'][0]				= ((strtoupper($this->config['UI_PROPERTIES'][0]['MAP_INFO'][0]) == 'TRUE')				? true : false);
		$this->config['UI_PROPERTIES'][0]['ROUND_SCORES'][0]				= ((strtoupper($this->config['UI_PROPERTIES'][0]['ROUND_SCORES'][0]) == 'TRUE')				? true : false);
		$this->config['UI_PROPERTIES'][0]['WARMUP'][0]					= ((strtoupper($this->config['UI_PROPERTIES'][0]['WARMUP'][0]) == 'TRUE')				? true : false);
		$this->config['FEATURES'][0]['MARK_ONLINE_PLAYER_RECORDS'][0]			= ((strtoupper($this->config['FEATURES'][0]['MARK_ONLINE_PLAYER_RECORDS'][0]) == 'TRUE')		? true : false);
		$this->config['FEATURES'][0]['ILLUMINATE_NAMES'][0]				= ((strtoupper($this->config['FEATURES'][0]['ILLUMINATE_NAMES'][0]) == 'TRUE')				? true : false);
		$this->config['FEATURES'][0]['NUMBER_FORMAT'][0]				= strtolower($this->config['FEATURES'][0]['NUMBER_FORMAT'][0]);
		$this->config['FEATURES'][0]['SHORTEN_NUMBERS'][0]				= ((strtoupper($this->config['FEATURES'][0]['SHORTEN_NUMBERS'][0]) == 'TRUE')				? true : false);
		$this->config['FEATURES'][0]['SONGLIST'][0]['SORTING'][0]			= ((strtoupper($this->config['FEATURES'][0]['SONGLIST'][0]['SORTING'][0]) == 'TRUE')			? true : false);
		$this->config['FEATURES'][0]['SONGLIST'][0]['FORCE_SONGLIST'][0]		= ((strtoupper($this->config['FEATURES'][0]['SONGLIST'][0]['FORCE_SONGLIST'][0]) == 'TRUE')		? true : false);
		$this->config['FEATURES'][0]['MAPLIST'][0]['SORTING'][0]			= strtoupper($this->config['FEATURES'][0]['MAPLIST'][0]['SORTING'][0]);
		$this->config['FEATURES'][0]['MAPLIST'][0]['FORCE_MAPLIST'][0]			= ((strtoupper($this->config['FEATURES'][0]['MAPLIST'][0]['FORCE_MAPLIST'][0]) == 'TRUE')		? true : false);
		$this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0]	= ((strtoupper($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0]) == 'TRUE')	? true : false);
		$this->config['FEATURES'][0]['KARMA'][0]['CALCULATION_METHOD'][0]		= strtolower($this->config['FEATURES'][0]['KARMA'][0]['CALCULATION_METHOD'][0]);


		// Autodisable unsupported Widgets in some Gamemodes
		$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0]['Stunts'][0]['ENABLED'][0]		= 'false';
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0]['TIME_ATTACK'][0]['ENABLED'][0]		= 'false';
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0]['STUNTS'][0]['ENABLED'][0]		= 'false';

		$widgets = array('DEDIMANIA_RECORDS', 'LOCAL_RECORDS', 'LIVE_RANKINGS', 'ROUND_SCORE');
		$gamemodes = array(
			'ROUNDS'	=> Gameinfo::ROUNDS,
			'TIME_ATTACK'	=> Gameinfo::TIMEATTACK,
			'TEAM'		=> Gameinfo::TEAM,
			'LAPS'		=> Gameinfo::LAPS,
			'CUP'		=> Gameinfo::CUP,
			'STUNTS'	=> Gameinfo::STUNTS,
		);

		// RecordWidgets like Dedimania...
		foreach ($gamemodes as $gamemode => $id) {
			foreach ($widgets as $widget) {
				if ( isset($this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0]) ) {
					$this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] = ((strtoupper($this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0]) == 'TRUE') ? true : false);

					// Topcount are required to be lower then entries.
					// But not in 'Team', both need to be '2'
					if ( (isset($this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0])) && (isset($this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0])) ) {
						if ( ($widget == 'LIVE_RANKINGS') && ($gamemode == 'TEAM') ) {
							$this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] = 2;
							$this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0] = 2;
						}
						else {
							if ($this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0] >= $this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0]) {
								$this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0] = $this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] - 1;
							}
						}
					}

					// Setup scale factor
					if ( (!isset($this->config[$widget][0]['SCALE'][0])) || ($this->config[$widget][0]['SCALE'][0] > 1.0) ) {
						$this->config[$widget][0]['SCALE'][0] = 1.0;
					}
					$this->config[$widget][0]['SCALE'][0] = (float)$this->config[$widget][0]['SCALE'][0];
				}
				else {
					// Auto disable this
					$this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] = false;
					$this->config[$widget][0]['WIDTH'][0] = 0;
					$aseco->console('[RecordsEyepiece] » Auto disable <'. strtolower($widget) .'> in gamemode "'. strtolower($gamemode) .'", missing entry.');
				}
			}
		}
		unset($id, $widget);

		// Special checks for <rounds>
		if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['ENABLED'][0] == true) {
			$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['DISPLAY_TYPE'][0] = ((strtoupper($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['DISPLAY_TYPE'][0]) == 'TIME') ? true : false);
			$format = ((isset($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['FORMAT'][0])) ? $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['FORMAT'][0] : false);
			if ( (preg_match('/\{score\}/', $format) === 0) && ((preg_match('/\{remaining\}/', $format) === 0) || (preg_match('/\{pointlimit\}/', $format) === 0)) ) {
				// Setup default
				$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['ROUNDS'][0]['FORMAT'][0] = '{score} ({remaining})';
				$aseco->console('[RecordsEyepiece] » LiveRankingsWidget placeholder not (complete) found, setup default format: "{score} ({remaining})"');
			}
		}

		// Special checks for <laps>
		if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['LAPS'][0]['ENABLED'][0] == true) {
			$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['LAPS'][0]['DISPLAY_TYPE'][0] = ((strtoupper($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0]['LAPS'][0]['DISPLAY_TYPE'][0]) == 'TIME') ? true : false);
		}

		// Check all Widgets in NiceMode
		foreach ($widgets as $widget) {
			if ( isset($this->config['NICEMODE'][0]['ALLOW'][0][$widget][0]) ) {
				$this->config['NICEMODE'][0]['ALLOW'][0][$widget][0] = ((strtoupper($this->config['NICEMODE'][0]['ALLOW'][0][$widget][0]) == 'TRUE') ? true : false);
			}
		}
		unset($widget);

		// All Scoretable-Lists
		$scorelists = array('TOP_AVERAGE_TIMES', 'DEDIMANIA_RECORDS', 'LOCAL_RECORDS', 'TOP_RANKINGS', 'TOP_WINNERS', 'MOST_RECORDS', 'MOST_FINISHED', 'TOP_PLAYTIME', 'TOP_DONATORS', 'TOP_NATIONS', 'TOP_CONTINENTS', 'TOP_MAPS', 'TOP_VOTERS', 'TOP_VISITORS', 'TOP_ACTIVE_PLAYERS', 'TOP_WINNING_PAYOUTS', 'TOP_BETWINS', 'TOP_ROUNDSCORE');
		foreach ($scorelists as $widget) {
			if ( isset($this->config['SCORETABLE_LISTS'][0][$widget][0]['ENABLED'][0]) ) {
				$this->config['SCORETABLE_LISTS'][0][$widget][0]['ENABLED'][0] = ((strtoupper($this->config['SCORETABLE_LISTS'][0][$widget][0]['ENABLED'][0]) == 'TRUE') ? true : false);
			}
			else {
				// Auto disable this
				$this->config['SCORETABLE_LISTS'][0][$widget][0]['ENABLED'][0] = false;
				$aseco->console('[RecordsEyepiece] » Auto disable <'. strtolower($widget) .'> from <scoretable_lists>, missing entry.');
			}

			// Setup scale factor
			if ( (!isset($this->config['SCORETABLE_LISTS'][0][$widget][0]['SCALE'][0])) || ($this->config['SCORETABLE_LISTS'][0][$widget][0]['SCALE'][0] > 1.0) ) {
				$this->config['SCORETABLE_LISTS'][0][$widget][0]['SCALE'][0] = 1.0;
			}
			$this->config['SCORETABLE_LISTS'][0][$widget][0]['SCALE'][0] = sprintf("%.1f", $this->config['SCORETABLE_LISTS'][0][$widget][0]['SCALE'][0]);
		}
		unset($widget);
		unset($scorelists);


		// Translate e.g. 'rounds' to id '1', 'time_attack' to id '2'...
		foreach ($widgets as $widget) {
			foreach ($gamemodes as $gamemode => $id) {
				if ( isset($this->config[$widget][0]['GAMEMODE'][0][$gamemode]) ) {
					$this->config[$widget][0]['GAMEMODE'][0][$id] = $this->config[$widget][0]['GAMEMODE'][0][$gamemode];
					unset($this->config[$widget][0]['GAMEMODE'][0][$gamemode]);
				}
			}
		}
		unset($widgets, $widget, $id);


		// Autodisable unsupported Widgets in some Gamemodes
		$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][Gameinfo::STUNTS][0]['ENABLED'][0]	= false;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::TIMEATTACK][0]['ENABLED'][0]	= false;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::STUNTS][0]['ENABLED'][0]	= false;


		// Set max. values for <round_score> Widget
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::ROUNDS][0]['WARMUP'][0]['ENTRIES'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::ROUNDS][0]['WARMUP'][0]['TOPCOUNT'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::TEAM][0]['WARMUP'][0]['ENTRIES'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::TEAM][0]['WARMUP'][0]['TOPCOUNT'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['WARMUP'][0]['ENTRIES'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['WARMUP'][0]['TOPCOUNT'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::CUP][0]['WARMUP'][0]['ENTRIES'][0]	= 2;
		$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::CUP][0]['WARMUP'][0]['TOPCOUNT'][0]	= 2;


		// Register /emusic chat command if the MusicWidget is enabled
		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			$aseco->registerChatCommand('emusic', array($this, 'chat_emusic'), 'Lists musics currently on the server (see: /eyepiece)', Player::PLAYERS);
			$aseco->console('[RecordsEyepiece] » Registering chat command "/emusic", because <music_widget> is enabled too.');
		}


		// Check the Widget width's
		if ( ($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] < 15.5) || (!$this->config['MUSIC_WIDGET'][0]['WIDTH'][0]) ) {
			$this->config['MUSIC_WIDGET'][0]['WIDTH'][0] = 15.5;
		}
		if ( ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] < 15.5) || (!$this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0]) ) {
			$this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] = 15.5;
		}
		if ( ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] < 15.5) || (!$this->config['LOCAL_RECORDS'][0]['WIDTH'][0]) ) {
			$this->config['LOCAL_RECORDS'][0]['WIDTH'][0] = 15.5;
		}
		if ( ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] < 15.5) || (!$this->config['LIVE_RANKINGS'][0]['WIDTH'][0]) ) {
			$this->config['LIVE_RANKINGS'][0]['WIDTH'][0] = 15.5;
		}
		if ( ($this->config['ROUND_SCORE'][0]['WIDTH'][0] < 15.5) || (!$this->config['ROUND_SCORE'][0]['WIDTH'][0]) ) {
			$this->config['ROUND_SCORE'][0]['WIDTH'][0] = 15.5;
		}

		if ( (!isset($this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0])) || ($this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0] == '') ) {
			$this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0] = 0;
		}
		if ( (!isset($this->config['FEATURES'][0]['KARMA'][0]['MIN_VOTES'][0])) || ($this->config['FEATURES'][0]['KARMA'][0]['MIN_VOTES'][0] == '') ) {
			$this->config['FEATURES'][0]['KARMA'][0]['MIN_VOTES'][0] = 0;
		}
		if ( (!isset($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0])) || ($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0] == '') ) {
			$this->config['FEATURES'][0]['TOPLIST_LIMIT'][0] = 5000;
		}

		// Check for additional Features
		if ( ($this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0] < 0) || (!$this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0]) ) {
			$this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0] = 0;
		}
		else {
			$this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0] = (int)$this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0];
		}

		// Check for Background-Colors
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] == '') {
			$this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] = '0000';
		}
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_FOCUS'][0] == '') {
			$this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_FOCUS'][0] = '0000';
		}

		// Check for additional settings
		if ($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0] == true) {
			if ( ( !isset($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['STORING_PATH'][0]) ) || ($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['STORING_PATH'][0] == '') ) {
				// Autodisable
				$this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0] = false;
				$aseco->console('[RecordsEyepiece] » Setup for <features><maplist><mapimages><storing_path> is not correct in "records_eyepiece.xml"');
			}
			else if ( !is_writeable($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['STORING_PATH'][0]) ) {
				$this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0] = false;
				$aseco->console('[RecordsEyepiece] » Directory "'. $this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['STORING_PATH'][0] .'" configured at <features><maplist><mapimages><storing_path> is not writeable!');
			}
			if (($this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ACCESS_URL'][0] == '') || (preg_match('/^http.*/', $this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ACCESS_URL'][0]) === 0) ) {
				// Autodisable
				$this->config['FEATURES'][0]['MAPLIST'][0]['MAPIMAGES'][0]['ENABLED'][0] = false;
				$aseco->console('[RecordsEyepiece] » Setup for <features><maplist><mapimages><access_url> is not correct in "records_eyepiece.xml"');
			}
		}
		if ($this->config['MAP_WIDGET'][0]['ENABLED'][0] == true) {
			if ( (isset($this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0])) && (strtoupper($this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0]) == 'NEXT') ) {
				$this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0] = 'Next';
			}
			else if ( (isset($this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0])) && (strtoupper($this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0]) == 'CURRENT') ) {
				$this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0] = 'Current';
			}
			else {
				$this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0] = 'Next';
			}

			if ( (!isset($this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0])) || ($this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0] > 1.0) ) {
				$this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0] = 1.0;
			}
			if ( (!isset($this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0])) || ($this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0] > 1.0) ) {
				$this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0] = 1.0;
			}
			$this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0]);
			$this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0]);
		}

		if ( (!isset($this->config['MUSIC_WIDGET'][0]['SCALE'][0])) || ($this->config['MUSIC_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['MUSIC_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['MUSIC_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['MUSIC_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0])) || ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0])) || ($this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0])) || ($this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0])) || ($this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['GAMEMODE_WIDGET'][0]['SCALE'][0])) || ($this->config['GAMEMODE_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['GAMEMODE_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['GAMEMODE_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['GAMEMODE_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0])) || ($this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0])) || ($this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0])) || ($this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['TOPLIST_WIDGET'][0]['SCALE'][0])) || ($this->config['TOPLIST_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['TOPLIST_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['TOPLIST_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['TOPLIST_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0])) || ($this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['VISITORS_WIDGET'][0]['SCALE'][0])) || ($this->config['VISITORS_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['VISITORS_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['VISITORS_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['VISITORS_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['CLOCK_WIDGET'][0]['RACE'][0]['SCALE'][0])) || ($this->config['CLOCK_WIDGET'][0]['RACE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['CLOCK_WIDGET'][0]['RACE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['CLOCK_WIDGET'][0]['RACE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['CLOCK_WIDGET'][0]['RACE'][0]['SCALE'][0]);
		if ( (!isset($this->config['CLOCK_WIDGET'][0]['SCORE'][0]['SCALE'][0])) || ($this->config['CLOCK_WIDGET'][0]['SCORE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['CLOCK_WIDGET'][0]['SCORE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['CLOCK_WIDGET'][0]['SCORE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['CLOCK_WIDGET'][0]['SCORE'][0]['SCALE'][0]);
		if ( (!isset($this->config['FAVORITE_WIDGET'][0]['RACE'][0]['SCALE'][0])) || ($this->config['FAVORITE_WIDGET'][0]['RACE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['FAVORITE_WIDGET'][0]['RACE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['FAVORITE_WIDGET'][0]['RACE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['FAVORITE_WIDGET'][0]['RACE'][0]['SCALE'][0]);
		if ( (!isset($this->config['FAVORITE_WIDGET'][0]['SCORE'][0]['SCALE'][0])) || ($this->config['FAVORITE_WIDGET'][0]['SCORE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['FAVORITE_WIDGET'][0]['SCORE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['FAVORITE_WIDGET'][0]['SCORE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['FAVORITE_WIDGET'][0]['SCORE'][0]['SCALE'][0]);
		if ( (!isset($this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0])) || ($this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0])) || ($this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0] > 1.0) ) {
			$this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0] = 1.0;
		}
		$this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0] = sprintf("%.1f", $this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0]);
		if ( (!isset($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0])) || ($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0]);
		if ( (!isset($this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0])) || ($this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0] > 1.0) ) {
			$this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0] = 1.0;
		}
		$this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0] = sprintf("%.1f", $this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0]);


		if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			// Check setup Limits
			if ( (!$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MINIMUM_AMOUNT'][0]) || ($this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MINIMUM_AMOUNT'][0] < 3) ) {
				$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MINIMUM_AMOUNT'][0] = 3;
			}
			if ( !$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RANK_LIMIT'][0] ) {
				$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RANK_LIMIT'][0] = 0;
			}
			if ( !$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MAXIMUM_PLANETS'][0] ) {
				$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MAXIMUM_PLANETS'][0] = 1000000;	// Set to unlimited
			}
			if ( !$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RESET_LIMIT'][0] ) {
				$this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RESET_LIMIT'][0] = 0;		// Disable
			}

			// Check setup Planets
			if ( (!$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0]) || ($this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0] < 20) ) {
				$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0] = 20;
			}
			if ( (!$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0]) || ($this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0] < 15) ) {
				$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0] = 15;
			}
			if ( (!$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0]) || ($this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0] < 10) ) {
				$this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0] = 10;
			}
			if ( (!$this->config['WINNING_PAYOUT'][0]['MINIMUM_SERVER_PLANETS'][0]) || ($this->config['WINNING_PAYOUT'][0]['MINIMUM_SERVER_PLANETS'][0] < 50) ) {
				$this->config['WINNING_PAYOUT'][0]['MINIMUM_SERVER_PLANETS'][0] = 50;
			}

			// Check for the max. length (256 signs) of <messages><winning_mail_body>
			$message = $aseco->formatText($this->config['MESSAGES'][0]['WINNING_MAIL_BODY'][0],
				9999,
				$aseco->server->login,
				$aseco->server->name
			);
			$message = str_replace('{br}', "%0A", $message);  // split long message
			$message = $aseco->formatColors($message);
			if (strlen($message) >= 256) {
				trigger_error('[RecordsEyepiece] » The <messages><winning_mail_body> is '. strlen($message) .' signs long (incl. the replaced placeholder), please remove '. (strlen($message) - 256) .' signs to fit into 256 signs limit!', E_USER_ERROR);
			}
		}


		// Initialise States
		$this->config['States']['DedimaniaRecords']['NeedUpdate']		= true;			// Interact with onDedimaniaRecord and onDediRecsLoaded
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']		= true;
		$this->config['States']['LocalRecords']['NeedUpdate']			= true;			// Interact with onLocalRecord
		$this->config['States']['LocalRecords']['UpdateDisplay']		= true;
		$this->config['States']['LocalRecords']['NoRecordsFound']		= false;
		$this->config['States']['LocalRecords']['ChkSum']			= false;
		$this->config['States']['LiveRankings']['NeedUpdate']			= true;			// Interact with onPlayerFinish
		$this->config['States']['LiveRankings']['UpdateDisplay']		= true;
		$this->config['States']['LiveRankings']['NoRecordsFound']		= false;
		$this->config['States']['RoundScore']['WarmUpPhase']			= false;
		$this->config['States']['TopMaps']['NeedUpdate']			= true;			// Interact with onKarmaChange
		$this->config['States']['TopVoters']['NeedUpdate']			= true;			// Interact with onKarmaChange
		$this->config['States']['MusicServerPlaylist']['NeedUpdate']		= false;
		$this->config['States']['NiceMode']					= false;
		$this->config['States']['RefreshTimestampRecordWidgets']		= -1000;		// Update now :D
		$this->config['States']['RefreshTimestampPreload']			= time();
		$this->config['States']['MaplistRefreshProgressed']			= false;

		// Preset the Placeholder which can be used in <placement>, filled later when info loaded by XAseco
		$this->config['PlacementPlaceholders']['MAP_MX_PREFIX']			= false;
		$this->config['PlacementPlaceholders']['MAP_MX_ID']			= false;
		$this->config['PlacementPlaceholders']['MAP_MX_PAGEURL']		= false;
		$this->config['PlacementPlaceholders']['MAP_NAME']			= false;
		$this->config['PlacementPlaceholders']['MAP_UID']			= false;

		// Definitions of Icon- and Title-Positions for the RecordWidgets
		$this->config['Positions'] = array(
			'left'	=> array(
				'icon'		=> array(
					'x'		=> 0.6,
					'y'		=> 0
				),
				'title'		=> array(
					'x'		=> 3.2,
					'y'		=> -0.65,
					'halign'	=> 'left'
				),
				'image_open'	=> array(
					'x'		=> -0.3,
					'image'		=> $this->config['IMAGES'][0]['WIDGET_OPEN_LEFT'][0]
				)
			),
			'right'	=> array(
				'icon'		=> array(
					'x'		=> 12.5,
					'y'		=> 0
				),
				'title'		=> array(
					'x'		=> 12.4,
					'y'		=> -0.65,
					'halign'	=> 'right'
				),
				'image_open'	=> array(
					'x'		=> 12.2,
					'image'		=> $this->config['IMAGES'][0]['WIDGET_OPEN_RIGHT'][0]
				)
			)
		);


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


		// Load the templates
		$this->loadTemplates();


		// Initialise DataArrays
		$this->scores['DedimaniaRecords']		= array();
		$this->scores['LocalRecords']			= array();
		$this->scores['LiveRankings']			= array();
		$this->scores['RoundScore']			= array();
		$this->scores['RoundScorePB']			= array();
		$this->scores['TopAverageTimes']		= array();
		$this->scores['TopRankings']			= array();
		$this->scores['TopWinners']			= array();
		$this->scores['MostRecords']			= array();
		$this->scores['MostFinished']			= array();
		$this->scores['TopPlaytime']			= array();
		$this->scores['TopDonators']			= array();
		$this->scores['TopNations']			= array();
		$this->scores['TopMaps']			= array();
		$this->scores['TopVoters']			= array();
		$this->scores['TopBetwins']			= array();
		$this->scores['TopRoundscore']			= array();
		$this->scores['TopVisitors']			= array();
		$this->scores['TopContinents']			= array();
		$this->scores['TopActivePlayers']		= array();
		$this->scores['TopWinningPayouts']		= array();

		// Init Cache
		$this->cache['MusicWidget']			= false;
		$this->cache['ToplistWidget']			= false;
		$this->cache['GamemodeWidget']			= false;
		$this->cache['VisitorsWidget']			= false;
		$this->cache['ManiaExchangeWidget']		= false;
		$this->cache['MapcountWidget']			= false;
		$this->cache['TopRankings']			= false;
		$this->cache['TopWinners']			= false;
		$this->cache['TopPlaytime']			= false;
		$this->cache['TopNations']			= false;
		$this->cache['TopMaps']				= false;
		$this->cache['TopVoters']			= false;
		$this->cache['TopWinningPayouts']		= false;
		$this->cache['TopVisitors']			= false;
		$this->cache['TopContinents']			= false;
		$this->cache['TopActivePlayers']		= false;
		$this->cache['TopBetwins']			= false;
		$this->cache['TopRoundscore']			= false;
		$this->cache['ManialinkActionKeys']		= $this->buildManialinkActionKeys();
		$this->cache['MapWidget']['Race']		= false;
		$this->cache['MapWidget']['Window']		= false;
		$this->cache['MapWidget']['Score']		= false;
		$this->cache['AddToFavoriteWidget']['Race']	= false;
		$this->cache['AddToFavoriteWidget']['Score']	= false;
		$this->cache['DonationWidget']['Default']	= false;
		$this->cache['DonationWidget']['Loading']	= false;
		$this->cache['PlayerStates']			= array();
		$this->cache['MusicServerPlaylist']		= array();
		$this->cache['MapList']				= array();
		$this->cache['MapAuthors']			= array();
		$this->cache['CurrentRankings']			= array();
		$this->cache['MapAuthorNation']			= $this->loadPlayerNations();
		if ( !isset($this->cache['PlayerWinnings']) ) {
			// Only setup if unset, otherwise it is overridden by "/eyeset reload"!
			$this->cache['PlayerWinnings']		= array();
		}

		// Setup the RecordWidgets and prebuild all enabled Gamemodes
		$aseco->console('[RecordsEyepiece] » Build and cache all Widget bodies...');
		$this->cache['DedimaniaRecords']['NiceMode']	= false;
		$this->cache['LocalRecords']['NiceMode']	= false;
		$this->cache['LiveRankings']['NiceMode']	= false;
		$widgets = array('DEDIMANIA_RECORDS', 'LOCAL_RECORDS', 'LIVE_RANKINGS', 'ROUND_SCORE');
		foreach ($widgets as $widget) {
			foreach ($gamemodes as $gamemode => $id) {
				if ( ($widget == 'DEDIMANIA_RECORDS') && (($this->config[$widget][0]['GAMEMODE'][0][$id][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['DEDIMANIA_RECORDS'][0] == true) && ($this->config['States']['NiceMode'] == true))) ) {
					$build = $this->buildDedimaniaRecordsWidgetBody($id);
					$this->cache['DedimaniaRecords'][$id]['WidgetHeader'] = $build['header'];
					$this->cache['DedimaniaRecords'][$id]['WidgetFooter'] = $build['footer'];
				}
				if ( ($widget == 'LOCAL_RECORDS') && (($this->config[$widget][0]['GAMEMODE'][0][$id][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['LOCAL_RECORDS'][0] == true) && ($this->config['States']['NiceMode'] == true))) ) {
					$build = $this->buildLocalRecordsWidgetBody($id);
					$this->cache['LocalRecords'][$id]['WidgetHeader'] = $build['header'];
					$this->cache['LocalRecords'][$id]['WidgetFooter'] = $build['footer'];
				}
				if ( ($widget == 'LIVE_RANKINGS') && (($this->config[$widget][0]['GAMEMODE'][0][$id][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['LIVE_RANKINGS'][0] == true) && ($this->config['States']['NiceMode'] == true))) ) {
					$build = $this->buildLiveRankingsWidgetBody($id);
					$this->cache['LiveRankings'][$id]['WidgetHeader'] = $build['header'];
					$this->cache['LiveRankings'][$id]['WidgetFooter'] = $build['footer'];
				}
				if ( ($widget == 'ROUND_SCORE') && ($this->config[$widget][0]['GAMEMODE'][0][$id][0]['ENABLED'][0] == true) ) {
					$build = $this->buildRoundScoreWidgetBody($id, 'RACE');
					$this->cache['RoundScore'][$id]['Race']['WidgetHeader'] = $build['header'];
					$this->cache['RoundScore'][$id]['Race']['WidgetFooter'] = $build['footer'];

					$build = $this->buildRoundScoreWidgetBody($id, 'WARMUP');
					$this->cache['RoundScore'][$id]['WarmUp']['WidgetHeader'] = $build['header'];
					$this->cache['RoundScore'][$id]['WarmUp']['WidgetFooter'] = $build['footer'];
				}
			}
		}
		unset($widgets, $widget, $gamemode, $id);


		// Make sure refresh's are set, otherwise set default
		$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] = ((isset($this->config['FEATURES'][0]['REFRESH_INTERVAL'][0])) ? (int)$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] : 10);
		$this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0] = ((isset($this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0])) ? (int)$this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0] : 10);

		// Store the default refresh interval, in NiceMode the $this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] are replaced
		$this->config['REFRESH_INTERVAL_DEFAULT'][0] = $this->config['FEATURES'][0]['REFRESH_INTERVAL'][0];

		// Stores $this->getCurrentSong() the ListMethod GetForcedMusic()
		$this->config['CurrentMusicInfos'] = array(
			'Artist'	=> 'unknown',
			'Title'		=> 'unknown',
		);

		// Stores the Last-, Current-, Next-Map and the next Map from $jukebox by plugin.rasp_jukebox.php, also the Numbers of Checkpoints
		$this->cache['Map']['Last']		= $this->getEmptyMapInfo();
		$this->cache['Map']['Current']		= $this->getEmptyMapInfo();
		$this->cache['Map']['Next']		= $this->getEmptyMapInfo();
		$this->cache['Map']['Jukebox']		= false;
		$this->cache['Map']['NbCheckpoints']	= 0;



		// Store the Name and associated Icons if one of the Widgets is enabled
		if ( ($this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) || ($this->config['NEXT_GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) ) {
			// Need for Gamemode-Widget
			$this->config['Gamemodes'] = array(
				Gameinfo::ROUNDS	=> array('name' => 'ROUNDS',		'icon' => 'RT_Rounds'),
				Gameinfo::TIMEATTACK	=> array('name' => 'TIME ATTACK',	'icon' => 'RT_TimeAttack'),
				Gameinfo::TEAM		=> array('name' => 'TEAM',		'icon' => 'RT_Team'),
				Gameinfo::LAPS		=> array('name' => 'LAPS',		'icon' => 'RT_Laps'),
				Gameinfo::CUP		=> array('name' => 'CUP',		'icon' => 'RT_Cup'),
				Gameinfo::STUNTS	=> array('name' => 'STUNTS',		'icon' => 'RT_Stunts'),
//				Gameinfo::TEAMATTACK	=> array('name' => 'TEAM ATTACK',	'icon' => 'RT_Team'),
			);
		}

		if ($reload === null) {
			$aseco->console('[RecordsEyepiece] » Checking Database for required extensions...');

			// Check the Database-Table for required extensions
			$fields = array();
			$result = $aseco->db->query('SHOW COLUMNS FROM `%prefix%players`;');
			if ($result) {
				while ($row = $result->fetch_row()) {
					$fields[] = $row[0];
				}
				$result->free_result();
			}

			// Add `MostFinished` column if not yet done
			if ( !in_array('MostFinished', $fields) ) {
				$aseco->console('[RecordsEyepiece] » Adding column `MostFinished`.');
				$aseco->db->query('ALTER TABLE `%prefix%players` ADD `MostFinished` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Added by plugin.records_eyepiece.php", ADD INDEX (`MostFinished`);');
			}
			else {
				$aseco->console('[RecordsEyepiece] » Found column `MostFinished`');
			}

			// Add `MostRecords` column if not yet done
			if ( !in_array('MostRecords', $fields) ) {
				$aseco->console('[RecordsEyepiece] » Adding column `MostRecords`.');
				$aseco->db->query('ALTER TABLE `%prefix%players` ADD `MostRecords` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Added by plugin.records_eyepiece.php", ADD INDEX (`MostRecords`);');
			}
			else {
				$aseco->console('[RecordsEyepiece] » Found column `MostRecords`.');
			}

			// Add `RoundPoints` column if not yet done
			if ( !in_array('RoundPoints', $fields) ) {
				$aseco->console('[RecordsEyepiece] » Adding column `RoundPoints`.');
				$aseco->db->query('ALTER TABLE `%prefix%players` ADD `RoundPoints` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Added by plugin.records_eyepiece.php", ADD INDEX (`RoundPoints`);');
			}
			else {
				$aseco->console('[RecordsEyepiece] » Found column `RoundPoints`.');
			}

			// Add `TeamPoints` column if not yet done
			if ( !in_array('TeamPoints', $fields) ) {
				$aseco->console('[RecordsEyepiece] » Adding column `TeamPoints`.');
				$aseco->db->query('ALTER TABLE `%prefix%players` ADD `TeamPoints` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Added by plugin.records_eyepiece.php", ADD INDEX (`TeamPoints`);');
			}
			else {
				$aseco->console('[RecordsEyepiece] » Found column `TeamPoints`.');
			}

			// Add `WinningPayout` column if not yet done
			if ( !in_array('WinningPayout', $fields) ) {
				$aseco->console('[RecordsEyepiece] » Adding column `WinningPayout`.');
				$aseco->db->query('ALTER TABLE `%prefix%players` ADD `WinningPayout` MEDIUMINT(3) UNSIGNED NOT NULL DEFAULT "0" COMMENT "Added by plugin.records_eyepiece.php", ADD INDEX (`WinningPayout`);');
			}
			else {
				$aseco->console('[RecordsEyepiece] » Found column `WinningPayout`.');
			}


			if ($this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0]['ENABLED'][0] == true) {
				// Update own `MostFinished`
				$aseco->console('[RecordsEyepiece] Updating `MostFinished` counts for all Players...');
				$mostfinished = array();
				$query = "
				SELECT
					`PlayerId`,
					COUNT(`Score`) AS `Count`
				FROM `%prefix%times`
				GROUP BY `PlayerId`;
				";
				$res = $aseco->db->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						while ($row = $res->fetch_object()) {
							$mostfinished[$row->PlayerId] = $row->Count;
						}
						$aseco->db->query('START TRANSACTION;');
						foreach ($mostfinished as $id => $count) {
							$res1 = $aseco->db->query("
								UPDATE `%prefix%players`
								SET `MostFinished` = ". $count ."
								WHERE `PlayerId` = ". $id ."
								LIMIT 1;
							");
						}
						$aseco->db->query('COMMIT;');
						unset($mostfinished);
					}
					$res->free_result();
				}
			}
			else {
				$aseco->console('[RecordsEyepiece] Skip updating `MostFinished` counts for all Players, because Widget is disabled.');
			}

			if ($this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0]['ENABLED'][0] == true) {
				// Update own `MostRecords`
				$aseco->console('[RecordsEyepiece] Updating `MostRecords` counts for all Players...');
				$mostrecords = array();
				$query = "
				SELECT
					`PlayerId`,
					COUNT(`Score`) AS `Count`
				FROM `%prefix%records`
				GROUP BY `PlayerId`;
				";
				$res = $aseco->db->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						while ($row = $res->fetch_object()) {
							$mostrecords[$row->PlayerId] = $row->Count;
						}
						$aseco->db->query('START TRANSACTION;');
						foreach ($mostrecords as $id => $count) {
							$res1 = $aseco->db->query("
								UPDATE `%prefix%players`
								SET `MostRecords` = ". $count ."
								WHERE `PlayerId` = ". $id ."
								LIMIT 1;
							");
						}
						$aseco->db->query('COMMIT;');
						unset($mostfinished);
					}
					$res->free_result();
				}
			}
			else {
				$aseco->console('[RecordsEyepiece] Skip updating `MostRecords` counts for all Players, because Widget is disabled.');
			}
		}


		// Check if is NiceMode been forced
		if ( ($this->config['NICEMODE'][0]['ENABLED'][0] == true) && ($this->config['NICEMODE'][0]['FORCE'][0] == true) ) {
			// Turn nicemode on
			$this->config['States']['NiceMode'] = true;

			// Set new refresh interval
			$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] = $this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0];

			$aseco->console('[RecordsEyepiece] Setup and forcing <nicemode>...');
		}


		if ( ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) && ( isset($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT']) ) ) {

			$aseco->console('[RecordsEyepiece] Checking entries for the <placement_widget>...');

			// Remove disabled <placement> (freed mem.) and setup for each <chat_command> entry an own id
			$new_placements = array();
			$chat_id = 1;	// Start ID for <chat_command>'s
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ( (isset($placement['ENABLED'][0])) && (strtoupper($placement['ENABLED'][0]) == 'TRUE') ) {

					if ( isset($placement['INCLUDE'][0]) ) {
						// WITH <include>: Check for min. required entries <display>, <include>,
						// skip if one was not found.
						if ( !isset($placement['DISPLAY'][0]) ) {
							$aseco->console('[RecordsEyepiece] » One of your <placement> did not have all min. required entries, missing <display>!');
							continue;
						}

						if ( !is_readable($placement['INCLUDE'][0]) ) {
							$aseco->console('[RecordsEyepiece] » One of your <placement> are unable to display, because the file "'. $placement['INCLUDE'][0] .'" at <include> could not be accessed!');
							continue;
						}
					}
					else {
						// WITHOUT <include>: Check for min. required entries <pos_x>, <pos_y>, <width> and <height>,
						// skip if one was not found.
						if ( ( !isset($placement['DISPLAY'][0]) ) || ( !isset($placement['POS_X'][0]) ) || ( !isset($placement['POS_Y'][0]) ) || ( !isset($placement['WIDTH'][0]) ) || ( !isset($placement['HEIGHT'][0]) ) ) {
							$aseco->console('[RecordsEyepiece] » One of your <placement> did not have all min. required entries, missing one of <pos_x>, <pos_y>, <width> or <height>!');
							continue;
						}
					}

					$placement['DISPLAY'][0] = strtoupper($placement['DISPLAY'][0]);

					// Transform all Gamemode-Names from e.g. 'TIME_ATTACK' to '2'
					foreach ($gamemodes as $gamemode => $id) {
						if ($placement['DISPLAY'][0] == $gamemode) {
							$placement['DISPLAY'][0] = $id;
						}
					}
					unset($id);

					// Remove empty and unused tags to free mem. too.
					foreach ($placement as $tag => $value) {
						if ($value[0] == '') {
							unset($placement[$tag]);
						}
					}
					unset($placement['ENABLED'], $placement['DESCRIPTION'], $value);

					// Skip this part from <placement> with <include> inside
					if ( !isset($placement['INCLUDE'][0]) ) {

						// Check for <layer> and adjust the min./max.
						if ( isset($placement['LAYER'][0]) ) {
							if ($placement['LAYER'][0] < -3) {
								$placement['LAYER'][0] = -3;	// Set min.
							}
							else if ($placement['LAYER'][0] > 20) {
								$placement['LAYER'][0] = 20;	// Set max.
							}
						}
						else {
							$placement['LAYER'][0] = 3;		// Set default
						}

						// If this <placement> has a <chat_command>, then setup an ID for this (max. 1 till 25)
						if ( (isset($placement['CHAT_COMMAND'][0])) && ($placement['CHAT_COMMAND'][0] != '') && ($chat_id <= 25) ) {
							$placement['CHAT_MLID'][0] = $chat_id;
							$chat_id ++;
						}
					}

					// Add Placentment
					$new_placements[] = $placement;
				}
			}
			$this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] = $new_placements;
			unset($new_placements, $placement);

			$aseco->console('[RecordsEyepiece] » Working on all <placement> with <display> "always"...');
			$this->cache['PlacementWidget']['Always'] = $this->buildPlacementWidget('always');

			$aseco->console('[RecordsEyepiece] » Working on all <placement> with <display> "race"...');
			$this->cache['PlacementWidget']['Race'] = $this->buildPlacementWidget('race');

			// Build all Placements for the Gamemodes
			foreach ($this->config['Gamemodes'] as $gamemode => $array) {
				$aseco->console('[RecordsEyepiece] » Working on all <placement> with <display> "'. strtolower($array['name']) .'"...');
				$this->cache['PlacementWidget'][$gamemode] = $this->buildPlacementWidget($gamemode);
			}
			// 'Score' is build at onEndMap1, because of the dependence of the placeholder
		}
		else {
			// Autodisable when there is no setup for <placement>
			$this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] = false;
		}


		// Setup the "no-score" Placeholder depending at the current Gamemode
		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$this->config['PlaceholderNoScore'] = '---';
		}
		else {
			$this->config['PlaceholderNoScore'] = '-:--.---';
		}


		// Setup 'map_info'
		if ( ($this->config['UI_PROPERTIES'][0]['MAP_INFO'][0] == false) || ($this->config['MAP_WIDGET'][0]['ENABLED'][0] == true) ) {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('map_info', false);
		}
		else {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('map_info', true);
		}

		// Setup 'round_scores' and use own RoundScoreWidget (if enabled)
		if ( ($this->config['UI_PROPERTIES'][0]['ROUND_SCORES'][0] == false) || ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::ROUNDS][0]['ENABLED'][0] == true) || ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::TEAM][0]['ENABLED'][0] == true) || ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::CUP][0]['ENABLED'][0] == true) ) {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('round_scores', false);
		}
		else {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('round_scores', true);
		}

		// Setup 'warmup'
		if ($this->config['UI_PROPERTIES'][0]['WARMUP'][0] == false) {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('warmup', false);
		}
		else {
			$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('warmup', true);
		}

		// Send the UI settings
		$aseco->plugins['PluginModescriptHandler']->setupUserInterface();



		// Get the current Maplist
		$this->getMaplist(false);

		// Get TopRoundscore
		$this->getTopRoundscore($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);

		// Refresh Scoretable lists
		$this->refreshScorelists();

		// Build the Playlist-Cache
		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			$this->getMusicServerPlaylist(false, true);
		}

		// Build the LadderLimitWidget
		if ($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0] == true) {
			$this->cache['LadderLimitWidget'] = $this->buildLadderLimitWidget();
		}

		// Build the Toplist Widget
		if ($this->config['TOPLIST_WIDGET'][0]['ENABLED'][0] == true) {
			$this->cache['ToplistWidget'] = $this->buildToplistWidget();
		}

		// Build the AddToFavorite Widget
		if ($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0] == true) {
			$this->cache['AddToFavoriteWidget']['Race'] = $this->buildAddToFavoriteWidget('RACE');
			$this->cache['AddToFavoriteWidget']['Score'] = $this->buildAddToFavoriteWidget('SCORE');
		}

		// Build the DonationWidget
		if ($this->config['DONATION_WIDGET'][0]['ENABLED'][0] == true) {
			$val = explode(',', $this->config['DONATION_WIDGET'][0]['AMOUNTS'][0]);
			if (count($val) < 7) {
				trigger_error('[RecordsEyepiece] » The amount of <donation_widget><amounts> is lower then the required min. of 7 in records_eyepiece.xml!', E_USER_ERROR);
			}
			$this->cache['DonationWidget']['Default'] = $this->buildDonationWidget('DEFAULT');
			$this->cache['DonationWidget']['Loading'] = $this->buildDonationWidget('LOADING');
		}


		$aseco->console('[RecordsEyepiece] Finished.');
		$aseco->console('[RecordsEyepiece] ********************************************************');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {

		// Make sure the Dedicated-Server have the control
		$aseco->client->query('ManualFlowControlEnable', false);

		if (isset($this->config['WINNING_PAYOUT'][0]) && $this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			foreach ($aseco->server->players->player_list as $player) {
				if ($this->cache['PlayerWinnings'][$player->login]['FinishPayment'] > 0) {
					$this->winningPayout($player);
				}
			}
			unset($player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_eyepiece ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		if (strtoupper($chat_parameter) == 'HIDE') {

			// Set display to hidden
			$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = false;

			// Hide the RecordWidgets
			$this->closeRaceWidgets($player->login, false);

			// Store the preferences
			$this->storePlayerData($player, 'DisplayWidgets', false);

			// Give feedback to the Player
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['WIDGETS_PREFERENCE_DISABLED'][0], $player->login);

		}
		else if (strtoupper($chat_parameter) == 'SHOW') {

			// Init
			$widgets = '';

			// Set display to displaying
			$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = true;

			// Build the RecordWidgets and in normal mode send it to each or given Player (if refresh is required)
			$this->cache['PlayerStates'][$player->login]['DedimaniaRecords'] = -1;
			$this->cache['PlayerStates'][$player->login]['LocalRecords'] = -1;
			$this->cache['PlayerStates'][$player->login]['LiveRankings'] = -1;
			$this->buildRecordWidgets($player, array('DedimaniaRecords' => true, 'LocalRecords' => true, 'LiveRankings' => true));

			if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
				// Display the Music Widget to given Player
				$widgets .= (($this->cache['MusicWidget'] != false) ? $this->cache['MusicWidget'] : '');
			}

			// Store the preferences
			$this->storePlayerData($player, 'DisplayWidgets', true);

			// Give feedback to the Player
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['WIDGETS_PREFERENCE_ENABLED'][0], $player->login);


			// Send all widgets
			if ($widgets != '') {
				// Send Manialink
				$this->sendManialink($widgets, $player->login, 0);
			}

		}
		else if (strtoupper($chat_parameter) == 'PAYOUTS') {

			if ($aseco->isAnyAdminL($login)) {

				if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
					$message = '{#server}>> There are outstanding disbursements in the amount of ';
					$outstanding = 0;
					foreach ($this->cache['PlayerWinnings'] as $login => $struct) {
						$outstanding += $this->cache['PlayerWinnings'][$login]['FinishPayment'];
					}
					unset($login, $struct);
					$message .= $this->formatNumber($outstanding, 0) .' Planets.';
				}
				else {
					$message = '{#server}>> WinningPayoutWidget is not enabled, no payouts to do!';
				}

				// Show message
				$aseco->sendChatMessage($message, $login);
			}

		}
		else {
			if ($aseco->server->gamestate == Server::RACE) {

				// Call the HelpWindow
				$answer = array(
					$player->pid,
					$player->login,
					'showHelpWindow',
					array(),
				);
				$this->onPlayerManialinkPageAnswer($aseco, $answer);

			}
			else {
				// Show message that the display at score is impossible
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['DISALLOW_WINDOWS_AT_SCORE'][0], $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_estat ($aseco, $login, $chat_command, $chat_parameter) {

		if ($aseco->server->gamestate == Server::RACE) {

			// Get Player object
			if (!$player = $aseco->server->players->getPlayer($login)) {
				return;
			}

			// Get current Gamemode
			$gamemode = $aseco->server->gameinfo->mode;

			$id = false;
			if ( (strtoupper($chat_parameter) == 'DEDIRECS') && ($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
				$id = 'showDedimaniaRecordsWindow';
			}
			else if (strtoupper($chat_parameter) == 'LOCALRECS') {
				$id = 'showLocalRecordsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPNATIONS') {
				$id = 'showTopNationsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPRANKS') {
				$id = 'showTopRankingsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPWINNERS') {
				$id = 'showTopWinnersWindow';
			}
			else if (strtoupper($chat_parameter) == 'MOSTRECORDS') {
				$id = 'showMostRecordsWindow';
			}
			else if (strtoupper($chat_parameter) == 'MOSTFINISHED') {
				$id = 'showMostFinishedWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPPLAYTIME') {
				$id = 'showTopPlaytimeWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPDONATORS') {
				$id = 'showTopDonatorsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPMAPS') {
				$id = 'showTopMapsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPVOTERS') {
				$id = 'showTopVotersWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPACTIVE') {
				$id = 'showTopActivePlayersWindow';
			}
			else if ( (strtoupper($chat_parameter) == 'TOPPAYOUTS') && ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) ) {
				$id = 'showTopWinningPayoutWindow';
			}
			else if ( (strtoupper($chat_parameter) == 'TOPBETWINS') && ($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ENABLED'][0] == true) ) {
				$id = 'showTopBetwinsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPROUNDSCORE') {
				$id = 'showTopRoundscoreWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPVISITORS') {
				$id = 'showTopVisitorsWindow';
			}
			else if (strtoupper($chat_parameter) == 'TOPCONTINENTS') {
				$id = 'showTopContinentsWindow';
			}
			else {
				$id = 'showHelpWindow';
			}

			if ($id !== false) {
				// Simulate a PlayerManialinkPageAnswer:
				// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
				$answer = array(
					$player->pid,
					$player->login,
					$id,
					array(),
				);

				// Wrap "/elist [PARAMETER]" to an ManialinkPageAnswer
				$this->onPlayerManialinkPageAnswer($aseco, $answer);
			}
		}
		else {
			// Show message that the display at score is impossible
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['DISALLOW_WINDOWS_AT_SCORE'][0], $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_eyeset ($aseco, $login, $chat_command, $chat_parameter) {

		// Init
		$message = false;

		// Check optional parameter
		if (strtoupper($chat_parameter) == 'RELOAD') {
			if ($aseco->server->gamestate == Server::RACE) {
				$aseco->console('[RecordsEyepiece] MasterAdmin '. $login .' reloads the configuration.');

				// Close all Widgets at all Players
				$xml  = $this->closeRaceWidgets(false, true);
				$xml .= $this->closeScoretableLists();
				$xml .= '<manialink id="PlacementWidgetRace" name="PlacementWidgetRace"></manialink>';
				$xml .= '<manialink id="PlacementWidgetScore" name="PlacementWidgetScore"></manialink>';
				$xml .= '<manialink id="PlacementWidgetAlways" name="PlacementWidgetAlways"></manialink>';
				$this->sendManialink($xml, false, 0);

				// Reload the config
				$this->onSync($aseco, true);

				// Simulate the event 'onBeginMap'
				$this->onBeginMap($aseco, $aseco->server->maps->current);

				// Simulate the event 'onBeginMap1'
				$this->onBeginMap1($aseco, $aseco->server->maps->current);

				// Display the PlacementWidgets at state 'always'
				if ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) {
					$xml = $this->cache['PlacementWidget']['Always'];
					$this->sendManialink($xml, false, 0);
				}

				$message = '{#admin}>> Reload of the configuration "config/records_eyepiece.xml" done.';
			}
			else {
				$message = '{#admin}>> Can not reload the configuration at Score!';
			}
		}
		else if ( preg_match("/^lfresh \d+$/i", $chat_parameter) ) {

			$param = preg_split("/^lfresh (\d+)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <refresh_interval> (normal mode) to "'. $param[0] .'" sec.';
			$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] = $param[0];

		}
		else if ( preg_match("/^hfresh \d+$/i", $chat_parameter) ) {

			$param = preg_split("/^hfresh (\d+)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <refresh_interval> (nice mode) to "'. $param[0] .'" sec.';
			$this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0] = $param[0];

		}
		else if ( preg_match("/^llimit \d+$/i", $chat_parameter) ) {

			$param = preg_split("/^llimit (\d+)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <lower_limit> (nice mode) to "'. $param[0] .'" Players.';
			$this->config['NICEMODE'][0]['LIMITS'][0]['LOWER_LIMIT'][0] = $param[0];

		}
		else if ( preg_match("/^ulimit \d+$/i", $chat_parameter) ) {

			$param = preg_split("/^ulimit (\d+)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <upper_limit> (nice mode) to "'. $param[0] .'" Players.';
			$this->config['NICEMODE'][0]['LIMITS'][0]['UPPER_LIMIT'][0] = $param[0];

		}
		else if ( preg_match("/^forcenice (true|false)$/i", $chat_parameter) ) {

			$param = preg_split("/^forcenice (true|false)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <nicemode><force> to "'. $param[0] .'".';
			$this->config['NICEMODE'][0]['FORCE'][0]	= ((strtoupper($param[0]) == 'TRUE') ? true : false);
			$this->config['States']['NiceMode']	= ((strtoupper($param[0]) == 'TRUE') ? true : false);

			// Lets refresh the Widgets
			$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
			$this->config['States']['LocalRecords']['UpdateDisplay']		= true;
			$this->config['States']['LiveRankings']['UpdateDisplay']		= true;
		}
		else if ( preg_match("/^playermarker (true|false)$/i", $chat_parameter) ) {

			$param = preg_split("/^playermarker (true|false)$/", $chat_parameter, 0, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
			$message = '{#admin}>> Set <features><mark_online_player_records> to "'. $param[0] .'".';
			$this->config['FEATURES'][0]['MARK_ONLINE_PLAYER_RECORDS'][0]	= ((strtoupper($param[0]) == 'TRUE') ? true : false);

			// Lets refresh the Widgets
			$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
			$this->config['States']['LocalRecords']['UpdateDisplay']		= true;
		}
		else {
			$message = '{#admin}>> Did not found any possible parameter to set!';
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

	// Wrapper/Chat command for opening the MaplistWindow
	public function chat_elist ($aseco, $login, $chat_command, $chat_parameter) {

		// Do not display at score
		if ($aseco->server->gamestate == Server::RACE) {

			// Get Player object
			if (!$player = $aseco->server->players->getPlayer($login)) {
				return;
			}

			if (count($player->data['PluginRecordsEyepiece']['Maplist']['Records']) == 0) {
				if ( (count($this->cache['MapList']) > $this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0]) && ($this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0] != 0) ) {
					$this->sendProgressIndicator($player->login);
				}

				// Load all local Records from calling Player
				$player->data['PluginRecordsEyepiece']['Maplist']['Records'] = $this->getPlayerLocalRecords($player->id);

				// Load all Maps that the calling Player did not finished yet
				$player->data['PluginRecordsEyepiece']['Maplist']['Unfinished'] = $this->getPlayerUnfinishedMaps($player->id);
			}

			$id = false;
			if (strtoupper($chat_parameter) == 'JUKEBOX') {

				// Show the MaplistWindow (but only jukeboxed Maps)
				$id = 'showMaplistWindowFilterJukeboxedMaps';
			}
			else if (strtoupper($chat_parameter) == 'AUTHOR') {

				// Show the MapAuthorlistWindow
				$id = 'showMapAuthorlistWindow';
			}
			else if (strtoupper($chat_parameter) == 'NORECENT') {

				// Show the MaplistWindow (but no recent Maps)
				$id = 'showMaplistWindowFilterNoRecentMaps';
			}
			else if (strtoupper($chat_parameter) == 'ONLYRECENT') {

				// Show the MaplistWindow (but only recent Maps)
				$id = 'showMaplistWindowFilterOnlyRecentMaps';
			}
			else if (strtoupper($chat_parameter) == 'NORANK') {

				// Show the MaplistWindow (but only Maps without a rank)
				$id = 'showMaplistWindowFilterOnlyMapsWithoutRank';
			}
			else if (strtoupper($chat_parameter) == 'ONLYRANK') {

				// Show the MaplistWindow (but only Maps with a rank)
				$id = 'showMaplistWindowFilterOnlyMapsWithRank';
			}
			else if (strtoupper($chat_parameter) == 'NOMULTI') {

				// Show the MaplistWindow (but no Multilap Maps)
				$id = 'showMaplistWindowFilterNoMultilapMaps';
			}
			else if (strtoupper($chat_parameter) == 'ONLYMULTI') {

				// Show the MaplistWindow (but only Multilap Maps)
				$id = 'showMaplistWindowFilterOnlyMultilapMaps';
			}
			else if (strtoupper($chat_parameter) == 'NOAUTHOR') {

				// Show the MaplistWindow (but only Maps no author time)
				$id = 'showMaplistWindowFilterOnlyMapsNoAuthorTime';
			}
			else if (strtoupper($chat_parameter) == 'NOGOLD') {

				// Show the MaplistWindow (but only Maps no gold time)
				$id = 'showMaplistWindowFilterOnlyMapsNoGoldTime';
			}
			else if (strtoupper($chat_parameter) == 'NOSILVER') {

				// Show the MaplistWindow (but only Maps no silver time)
				$id = 'showMaplistWindowFilterOnlyMapsNoSilverTime';
			}
			else if (strtoupper($chat_parameter) == 'NOBRONZE') {

				// Show the MaplistWindow (but only Maps no bronze time)
				$id = 'showMaplistWindowFilterOnlyMapsNoBronzeTime';
			}
			else if (strtoupper($chat_parameter) == 'NOFINISH') {

				// Show the MaplistWindow (but only Maps not finished)
				$id = 'showMaplistWindowFilterOnlyMapsNotFinished';
			}
			else if (strtoupper($chat_parameter) == 'BEST') {

				// Show the MaplistWindow (sort Maps 'Best Player Rank')
				$id = 'showMaplistWindowSortingBestPlayerRank';
			}
			else if (strtoupper($chat_parameter) == 'WORST') {

				// Show the MaplistWindow (sort Maps 'Worst Player Rank')
				$id = 'showMaplistWindowSortingWorstPlayerRank';
			}
			else if (strtoupper($chat_parameter) == 'SHORTEST') {

				// Show the MaplistWindow (sort Maps 'Shortest Author Time')
				$id = 'showMaplistWindowSortingShortestAuthorTime';
			}
			else if (strtoupper($chat_parameter) == 'LONGEST') {

				// Show the MaplistWindow (sort Maps 'Longest Author Time')
				$id = 'showMaplistWindowSortingLongestAuthorTime';
			}
			else if (strtoupper($chat_parameter) == 'NEWEST') {

				// Show the MaplistWindow (sort Maps 'Newest Maps First')
				$id = 'showMaplistWindowSortingNewestMapsFirst';
			}
			else if (strtoupper($chat_parameter) == 'OLDEST') {

				// Show the MaplistWindow (sort Maps 'Oldest Maps First')
				$id = 'showMaplistWindowSortingOldestMapsFirst';
			}
			else if (strtoupper($chat_parameter) == 'MAP') {

				// Show the MaplistWindow (sort Maps 'By Mapname')
				$id = 'showMaplistWindowSortingByMapname';
			}
			else if (strtoupper($chat_parameter) == 'AUTHOR') {

				// Show the MaplistWindow (sort Maps 'By Authorname')
				$id = 'showMaplistWindowSortingByAuthorname';
			}
			else if (strtoupper($chat_parameter) == 'BESTKARMA') {

				// Show the MaplistWindow (sort Maps 'By Karma: Best Maps First')
				$id = 'showMaplistWindowSortingByKarmaBestMapsFirst';
			}
			else if (strtoupper($chat_parameter) == 'WORSTKARMA') {

				// Show the MaplistWindow (sort Maps 'By Karma: Worst Maps First')
				$id = 'showMaplistWindowSortingByKarmaWorstMapsFirst';
			}
			else {
				if (strlen($chat_parameter) > 0) {
					// Show the MaplistWindow (Search for Keyword at Mapname/Author/Filename)
					$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('key' => $chat_parameter);
					$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;

					// Show the MaplistWindow (filtered by given keyword)
					$id = 'showMaplistWindowFilterByKeyword';
				}
				else {
					// Show the MaplistWindow (display all Maps)
					$id = 'showMaplistWindow';
				}
			}

			if ($id !== false) {
				// Simulate a PlayerManialinkPageAnswer:
				// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
				$answer = array(
					$player->pid,
					$player->login,
					$id,
					array(),
				);

				// Wrap "/elist [PARAMETER]" to an ManialinkPageAnswer
				$this->onPlayerManialinkPageAnswer($aseco, $answer);
			}
		}
		else {
			// Show message that the display at score is impossible
			$aseco->sendChatMessage($this->config['MESSAGES'][0]['DISALLOW_WINDOWS_AT_SCORE'][0], $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Wrapper/Chat command for opening the MusiclistWindow
	public function chat_emusic ($aseco, $login, $chat_command, $chat_parameter) {

		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			// Do not display at score
			if ($aseco->server->gamestate == Server::RACE) {
				// Get Player object
				if ($player = $aseco->server->players->getPlayer($login)) {

					// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
					$answer = array(
						$player->pid,
						$player->login,
						'showMusiclistWindow',
						array(),
					);
					$this->onPlayerManialinkPageAnswer($aseco, $answer);
				}
			}
			else {
				// Show message that the display at score is impossible
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['DISALLOW_WINDOWS_AT_SCORE'][0], $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_togglewidgets ($aseco, $login, $chat_command, $chat_parameter) {

		if ($aseco->server->gamestate == Server::RACE) {
			if ($this->config['States']['NiceMode'] == false) {
				$this->toggleWidgets($login);
			}
			else {
				// RecordWidgets are in NiceMode and can not be hidden so give feedback to the Player
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['TOGGLING_DISABLED'][0], $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function toggleWidgets ($login) {
		global $aseco;

		if ($aseco->server->gamestate == Server::RACE) {
			if ($this->config['States']['NiceMode'] == false) {

				// Get Player object
				if (!$player = $aseco->server->players->getPlayer($login)) {
					return;
				}

				if ($player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] == true) {

					// Set display to hidden
					$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = false;

					// Hide the RecordWidgets
					$this->closeRaceWidgets($player->login, false);

					// Give feedback to the Player
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['WIDGETS_DISABLED'][0], $player->login);
				}
				else {
					// Init
					$widgets = '';

					// Get current Gamemode
					$gamemode = $aseco->server->gameinfo->mode;

					// Set display to displaying
					$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = true;

					// Build the RecordWidgets and in normal mode send it to each or given Player (if refresh is required)
					$this->cache['PlayerStates'][$player->login]['DedimaniaRecords'] = -1;
					$this->cache['PlayerStates'][$player->login]['LocalRecords'] = -1;
					$this->cache['PlayerStates'][$player->login]['LiveRankings'] = -1;
					$this->buildRecordWidgets($player, array('DedimaniaRecords' => true, 'LocalRecords' => true, 'LiveRankings' => true));

					if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
						// Display the Music Widget to given Player
						$widgets .= (($this->cache['MusicWidget'] != false) ? $this->cache['MusicWidget'] : '');
					}

					// Display the RoundScoreWidget
					if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
						$widgets .= $this->buildRoundScoreWidget($gamemode, false);
					}

					// Give feedback to the Player
					$aseco->sendChatMessage($this->config['MESSAGES'][0]['WIDGETS_ENABLED'][0], $player->login);


					// Send all widgets
					if ($widgets != '') {
						// Send Manialink
						$this->sendManialink($widgets, $player->login, 0);
					}
				}
			}
			else {
				// RecordWidgets are in NiceMode and can not be hidden so give feedback to the Player
				$aseco->sendChatMessage($this->config['MESSAGES'][0]['TOGGLING_DISABLED'][0], $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEverySecond ($aseco) {


		// Is it time for refresh the RecordWidgets?
		if (time() >= $this->config['States']['RefreshTimestampRecordWidgets']) {

			// Get current Gamemode
			$gamemode = $aseco->server->gameinfo->mode;

			// Set next refresh timestamp
			$this->config['States']['RefreshTimestampRecordWidgets'] = (time() + $this->config['FEATURES'][0]['REFRESH_INTERVAL'][0]);

			// Check for changed LocalRecords, e.g. on "/admin delrec 1"...
			if ($this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
				if (count($aseco->plugins['PluginLocalRecords']->records->record_list) >= 1) {
					$localDigest = $this->buildRecordDigest('locals', $aseco->plugins['PluginLocalRecords']->records->record_list);
					if ($this->config['States']['LocalRecords']['ChkSum'] != $localDigest) {
						$this->config['States']['LocalRecords']['NeedUpdate'] = true;
					}
				}
			}

			// Load the current Rankings
			if ( ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true) || ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
				$this->cache['CurrentRankings'] = array();
				if ($gamemode == Gameinfo::TEAM) {
					$this->cache['CurrentRankings'] = $this->getCurrentRanking(2,0);
				}
				else {
					// All other GameModes
					$this->cache['CurrentRankings'] = $this->getCurrentRanking(300,0);
				}
			}

			// Build the RecordWidgets and ONLY in normal mode send it to each Player (if refresh is required)
			$this->buildRecordWidgets(false, false);


			$widgets = '';
			if ($this->config['States']['NiceMode'] == true) {
				// Display the RecordWidgets to all Players (if refresh is required)
				$widgets .= $this->showRecordWidgets(false);
			}

			// Send all widgets to ALL Players
			if ($widgets != '') {
				// Send Manialink
				$this->sendManialink($widgets, false, 0);
			}


			// Just refreshed, mark as fresh
			$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= false;
			$this->config['States']['LocalRecords']['UpdateDisplay']		= false;
			$this->config['States']['LiveRankings']['UpdateDisplay']		= false;
		}

		// Required to load the Preloader for external Images
		if (time() >= $this->config['States']['RefreshTimestampPreload']) {
			$this->config['States']['RefreshTimestampPreload'] = (time() + 5);

			foreach ($aseco->server->players->player_list as $player) {
				if ( (time() >= $player->data['PluginRecordsEyepiece']['Preload']['Timestamp']) && ($player->data['PluginRecordsEyepiece']['Preload']['LoadedPart'] != 5) ) {
					$player->data['PluginRecordsEyepiece']['Preload']['LoadedPart'] += 1;
					$widgets = $this->buildImagePreload($player->data['PluginRecordsEyepiece']['Preload']['LoadedPart']);

					if ($widgets != '') {
						// Send Manialink
						$this->sendManialink($widgets, $player->login, 0);
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

	public function refreshScorelists () {


		// Refresh MostRecords Array
		$this->getMostRecords($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);

		// Refresh the MostFinished Array
		$this->getMostFinished($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);

		// Refresh the TopDonators Array
		$this->getTopDonators($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);

		// Refresh the TopContinents Array
		$this->getTopContinents($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);

		// Refresh the TopRankings Array
		$this->getTopRankings($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0]['ENABLED'][0] == true) {
			$this->cache['TopRankings'] = $this->buildScorelistWidgetEntry('TopRankingsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0], $this->scores['TopRankings'], array('score', 'nickname'));
		}

		// Refresh the TopWinners Array
		$this->getTopWinners($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['ENABLED'][0] == true) {
			$this->cache['TopWinners'] = $this->buildScorelistWidgetEntry('TopWinnersWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0], $this->scores['TopWinners'], array('score', 'nickname'));
		}

		// Refresh TopMaps Array (if required)
		if ($this->config['States']['TopMaps']['NeedUpdate'] == true) {
			$this->calculateMapKarma();
			$this->getTopMaps($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0]['ENABLED'][0] == true) {
			$this->cache['TopMaps'] = $this->buildScorelistWidgetEntry('TopMapsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0], $this->scores['TopMaps'], array('karma', 'map'));
		}

		// Refresh TopVoters Array (if required)
		if ($this->config['States']['TopVoters']['NeedUpdate'] == true) {
			$this->getTopVoters($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0]['ENABLED'][0] == true) {
			$this->cache['TopVoters'] = $this->buildScorelistWidgetEntry('TopVotersWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0], $this->scores['TopVoters'], array('score', 'nickname'));
		}

		// Refresh the TopBetwins Array
		$this->getTopBetwins($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ENABLED'][0] == true) {
			$this->cache['TopBetwins'] = $this->buildScorelistWidgetEntry('TopBetwinsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0], $this->scores['TopBetwins'], array('won', 'nickname'));
		}

		// Refresh the TopWinningPayout Array
		$this->getTopWinningPayout($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0]['ENABLED'][0] == true) {
			$this->cache['TopWinningPayouts'] = $this->buildScorelistWidgetEntry('TopWinningPayoutsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0], $this->scores['TopWinningPayouts'], array('won', 'nickname'));
		}

		// Refresh the TopVisitors Array
		$this->getTopVisitors($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0]['ENABLED'][0] == true) {
			$this->cache['TopVisitors'] = $this->buildScorelistWidgetEntry('TopVisitorsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0], $this->scores['TopVisitors'], array('score', 'nickname'));
		}

		// Refresh the TopActivePlayers Array
		$this->getTopActivePlayers($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0]['ENABLED'][0] == true) {
			$this->cache['TopActivePlayers'] = $this->buildScorelistWidgetEntry('TopActivePlayersWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0], $this->scores['TopActivePlayers'], array('score', 'nickname'));
		}

		// Refresh the TopPlaytime Array
		$this->getTopPlaytime($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0]['ENABLED'][0] == true) {
			$this->cache['TopPlaytime'] = $this->buildScorelistWidgetEntry('TopPlaytimeWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0], $this->scores['TopPlaytime'], array('score', 'nickname'));
		}

		// Refresh the TopNations Array
		$this->getTopNationList($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ENABLED'][0] == true) {
			$this->cache['TopNations'] = $this->buildTopNationsForScore($this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ENTRIES'][0]);
		}

		// Refresh the Visitors Array
		$this->getVisitors($this->config['FEATURES'][0]['TOPLIST_LIMIT'][0]);
		if ($this->config['VISITORS_WIDGET'][0]['ENABLED'][0] == true) {
			$this->cache['VisitorsWidget'] = $this->buildVisitorsWidget();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Check if it is time to switch from "normal" to NiceMode or back
		$this->checkServerLoad();

		// Get the detailed Players TeamId (refreshed onPlayerInfoChanged)
		$player->data['PluginRecordsEyepiece']['Prefs']['TeamId'] = $player->teamid;

		// Init Player-Storages
		$player->data['PluginRecordsEyepiece']['Window']['Action'] = false;
		$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
		$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = 0;
		$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = false;
		$player->data['PluginRecordsEyepiece']['Maplist']['Records'] = array();
		$player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'] = array();

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Init
		$widgets = '';

		// Set Widget to displayed default (need for F7 or /togglewidgets)
		$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = true;

		// Preset an empty RecordEntry for the RecordWidgets, required
		// for an empty entry for this Player if he/she did not has a Record yet
		$item = array();
		$item['login'] = $player->login;
		$item['nickname'] = $this->handleSpecialChars($player->nickname);
		$item['self'] = 0;
		$item['rank'] = '--';
		$player->data['PluginRecordsEyepiece']['Prefs']['WidgetEmptyEntry'] = $item;


		// Add this Player to the Hash-Compare-Process
		$this->cache['PlayerStates'][$player->login]['DedimaniaRecords']	= false;
		$this->cache['PlayerStates'][$player->login]['LocalRecords']	= false;
		$this->cache['PlayerStates'][$player->login]['LiveRankings']	= false;
		$this->cache['PlayerStates'][$player->login]['FinishScore']	= -1;


		if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			// Check for WinningPayment: If this exists, do not override!
			if ( !isset($this->cache['PlayerWinnings'][$player->login]['TimeStamp']) ) {
				$this->cache['PlayerWinnings'][$player->login]['FinishPayment']	= 0;
				$this->cache['PlayerWinnings'][$player->login]['FinishPaid']	= 0;
				$this->cache['PlayerWinnings'][$player->login]['TimeStamp']	= 0;
			}

			// Add this Player to the Cache
			$this->cache['WinningPayoutPlayers'][$player->login] = array(
				'id'		=> $player->id,
				'login'		=> $player->login,
				'nickname'	=> $player->nickname,
				'ladderrank'	=> $player->ladderrank
			);
		}

		// Look if Player is in $this->scores['TopActivePlayers'] and if, then update and resort
		$found = false;
		foreach ($this->scores['TopActivePlayers'] as &$item) {
			if ($item['login'] == $player->login) {
				$item['score']		= 'Today';
				$item['scoplain']	= 0;
				$found = true;
				break;
			}
		}
		unset($item);
		if ($found == true) {
			// Resort by 'score'
			$data = array();
			foreach ($this->scores['TopActivePlayers'] as $key => $row) {
				$data[$key] = $row['scoplain'];
			}
			array_multisort($data, SORT_NUMERIC, SORT_ASC, $this->scores['TopActivePlayers']);
			unset($data, $key, $row);
		}


		// Load the Player preferences
		$display_widgets = $this->getPlayerData($player, 'DisplayWidgets');
		if ($display_widgets) {
			$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = $display_widgets;
		}
		else {
			// Setup defaults
			$this->storePlayerData($player, 'DisplayWidgets', true);
			$player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] = true;
		}


		if ($this->config['CLOCK_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildClockWidget();
		}

		if ($this->config['TOPLIST_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the TopList Widget to connecting Player
			$widgets .= $this->cache['ToplistWidget'];
		}

		if ($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the AddToFavorite Widget
			$widgets .= $this->cache['AddToFavoriteWidget']['Race'];
		}

		if ($this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Gamemode Widget to connecting Player
			$widgets .= (($this->cache['GamemodeWidget'] != false) ? $this->cache['GamemodeWidget'] : '');
		}

		if ($this->config['VISITORS_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Visitors-Widget to connecting Player
			$widgets .= (($this->cache['VisitorsWidget'] != false) ? $this->cache['VisitorsWidget'] : '');
		}

		if ($this->config['MANIAEXCHANGE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the ManiaExchangeWidget to connecting Player
			$widgets .= (($this->cache['ManiaExchangeWidget'] != false) ? $this->cache['ManiaExchangeWidget'] : '');
		}

		if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the MapcountWidget to connecting Player
			$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
		}

		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Music Widget to connecting Player
			$widgets .= (($this->cache['MusicWidget'] != false) ? $this->cache['MusicWidget'] : '');
		}

		if ($this->config['States']['NiceMode'] == true) {
			// Display the RecordWidgets to calling Player
			$widgets .= $this->showRecordWidgets(true);
		}
		else {
			// Find any Records for this Player and if found, refresh the concerned Widgets
			$result = $this->findPlayerRecords($player->login);
			if ( ($result['DedimaniaRecords'] == true) || ($result['LocalRecords'] == true) ) {
				// New Player has one Record, need to refresh concerned Widgets (without LiveRankings) at ALL Players, but not current Player
				$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
				$this->config['States']['LocalRecords']['UpdateDisplay']		= true;
			}

			// Now the connected Player need all Widgets to be displayed, not only that where he/she has a record
			$this->buildRecordWidgets($player, array('DedimaniaRecords' => true, 'LocalRecords' => true, 'LiveRankings' => true));
		}

		// Set ActionKeys
		$widgets .= (($this->cache['ManialinkActionKeys'] != false) ? $this->cache['ManialinkActionKeys'] : '');

		// Display the PlacementWidgets at state 'always'
		if ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->cache['PlacementWidget']['Always'];
		}

		// Display the PlacementWidgets at state 'race'
		if ( ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) && ($aseco->server->gamestate == Server::RACE) ) {
			$widgets .= $this->cache['PlacementWidget']['Race'];
			$widgets .= $this->cache['PlacementWidget'][$gamemode];
		}

		// Display the MapWidget
		$widgets .= (($this->cache['MapWidget']['Race'] != false) ? $this->cache['MapWidget']['Race'] : '');

		// Mark this Player for need to preload Images
		$player->data['PluginRecordsEyepiece']['Preload']['Timestamp'] = (time() + 15);
		$player->data['PluginRecordsEyepiece']['Preload']['LoadedPart'] = 0;


		// Display the RoundScoreWidget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildRoundScoreWidget($gamemode, false);
		}

		// Display the PlayersSpectatorsCountWidget
		if ($this->config['PLAYER_SPECTATOR_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildPlayerSpectatorWidget();
		}

		// Display the CurrentRankingWidget
		if ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildCurrentRankingWidget($player->login);
		}

		// Display the LadderLimitWidget
		if ($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['LadderLimitWidget'] != false) ? $this->cache['LadderLimitWidget'] : '');
		}

		if ($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['ENABLED'][0] == true) {
			$widgets .= $this->templates['RECORDSEYEPIECEAD']['RACE'];
		}

//		if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
//			$widgets .= $this->buildLiveRankingsWidgetMS($gamemode, $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0]);
//		}

		// Send all widgets
		if ($widgets != '') {
			// Send Manialink
			$this->sendManialink($widgets, $player->login, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnectPrepare ($aseco, $player) {

		// Remove temporary Player data, do not need to be stored into the database.
		$this->removePlayerData($player, 'Maplist');
		$this->removePlayerData($player, 'Prefs');
		$this->removePlayerData($player, 'Preload');
		$this->removePlayerData($player, 'Window');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {


		// Check if it is time to switch from "normal" to NiceMode or back
		$this->checkServerLoad();

		if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			$this->winningPayout($player);
		}

		// Find any Records for this Player and if found, refresh the concerned Widgets
		$result = $this->findPlayerRecords($player->login);
		if ($result['DedimaniaRecords'] == true) {
			$this->config['States']['DedimaniaRecords']['UpdateDisplay'] = true;
		}
		if ($result['LocalRecords'] == true) {
			$this->config['States']['LocalRecords']['UpdateDisplay'] = true;
		}


		// Remove this Player from the Hash-Compare-Process
		unset($this->cache['PlayerStates'][$player->login]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerRankingUpdated ($aseco) {

		// Update LiveRankings only
		$this->buildRecordWidgets(false, array('DedimaniaRecords' => false, 'LocalRecords' => false, 'LiveRankings' => true));
		$this->config['States']['LiveRankings']['NeedUpdate']		= true;
		$this->config['States']['LiveRankings']['UpdateDisplay']	= true;

		// Build and send the CurrentRankingWidget to all Players
		if ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true && $aseco->server->gamestate == Server::RACE) {
			$this->buildCurrentRankingWidget(false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish1 ($aseco, $finish_item) {


		if ($finish_item->score == 0) {
			// No actual finish, bail out immediately
			return;
		}

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Get the Player object (possible required below)
		$player = $aseco->server->players->player_list[$finish_item->player->login];


		// Check if the Player has a better score as before
		$refresh = false;
		if ($this->cache['PlayerStates'][$player->login]['FinishScore'] == -1) {
			// New Score, store them
			$this->cache['PlayerStates'][$player->login]['FinishScore'] = $finish_item->score;

			// Let the Widget refresh
			$refresh = true;
		}
		else if ( ($finish_item->score < $this->cache['PlayerStates'][$player->login]['FinishScore']) && ($gamemode != Gameinfo::STUNTS) ) {
			// All Gamemodes (except Gamemode 'Stunts'): Lower = Better

			// Better Score, store them
			$this->cache['PlayerStates'][$player->login]['FinishScore'] = $finish_item->score;

			// Let the Widget refresh
			$refresh = true;
		}
		else if ( ($finish_item->score > $this->cache['PlayerStates'][$player->login]['FinishScore']) && ($gamemode == Gameinfo::STUNTS) ) {
			// Only at Gamemode 'Stunts': Higher = Better

			// Better Score, store them
			$this->cache['PlayerStates'][$player->login]['FinishScore'] = $finish_item->score;

			// Let the Widget refresh
			$refresh = true;
		}
		// Refresh the LiveRankingsWidget only if there is a better or new Score/Time
		if ($refresh == true) {
			// Player finished the Map, need to Update the 'LiveRanking',
			// but not at Gamemode 'Rounds' - that are only updated at the event 'onEndRound'!
			if ($gamemode != Gameinfo::ROUNDS) {
				$this->config['States']['LiveRankings']['NeedUpdate'] = true;
				$this->config['States']['LiveRankings']['NoRecordsFound'] = false;
			}
		}


		// Increase finish count for this Player, required for MostFinished List at Score and in the TopLists
		$query = "UPDATE `%prefix%players` SET `MostFinished` = `MostFinished` + 1 WHERE `PlayerId` = '". $player->id ."';";
		$result = $aseco->db->query($query);
		if (!$result) {
			$aseco->console('[RecordsEyepiece] UPDATE `MostFinished` failed. [for statement "'. $query .'"]!');
		}


		// Store the finish time for the RoundScore and display the RoundScoreWidget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {

			if ($gamemode == Gameinfo::LAPS) {
				// Add the Score
				$this->scores['RoundScore'][$player->login] = array(
					'checkpointid'	=> ($aseco->server->maps->current->nbcheckpoints - 1),
					'playerid'	=> $player->pid,
					'login'		=> $player->login,
					'nickname'	=> $this->handleSpecialChars($player->nickname),
					'score'		=> $aseco->formatTime($finish_item->score),
					'scoplain'	=> $finish_item->score
				);
			}
			else {
				// Add the Score
				$this->scores['RoundScore'][$finish_item->score][] = array(
					'team'		=> $player->data['PluginRecordsEyepiece']['Prefs']['TeamId'],
					'playerid'	=> $player->pid,
					'login'		=> $player->login,
					'nickname'	=> $this->handleSpecialChars($player->nickname),
					'score'		=> $aseco->formatTime($finish_item->score),
					'scoplain'	=> $finish_item->score
				);

				// Store personal best round-score for sorting on equal times of more Players
				if ( ( isset($this->scores['RoundScorePB'][$player->login]) ) && ($this->scores['RoundScorePB'][$player->login] > $finish_item->score) ) {
					$this->scores['RoundScorePB'][$player->login] = $finish_item->score;
				}
				else {
					$this->scores['RoundScorePB'][$player->login] = $finish_item->score;
				}
			}

			// Display the Widget
			$this->buildRoundScoreWidget($gamemode, true);
		}


		// Store the $finish_item->score to build the average
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ENABLED'][0] == true) {
			$this->scores['TopAverageTimes'][$player->login][] = $finish_item->score;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerWins ($aseco, $player) {


		// Look if Player is in Array
		foreach ($this->scores['TopWinners'] as $item) {
			if ($item['login'] == $player->login) {
				// Lets refresh them now
				$this->getTopWinners();
				if ($this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['ENABLED'][0] == true) {
					$this->cache['TopWinners'] = $this->buildScorelistWidgetEntry('TopWinnersWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0], $this->scores['TopWinners'], array('score', 'nickname'));
				}
				break;
			}
		}
		unset($item);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerInfoChanged ($aseco, $changes) {

		// Skip work at Score
		if ($aseco->server->gamestate == Server::RACE) {

			// Get current Gamemode
			$gamemode = $aseco->server->gameinfo->mode;

//			// Is the CheckpointCountWidget enabled?
//			if ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
//
//				// Catch all Spectators (e.g.: Spectator, TemporarySpectator or PureSpectator)
//				if ($changes['SpectatorStatus'] > 0) {
//					$xml = '<manialink id="CheckpointCountWidget" name="CheckpointCountWidget"></manialink>';
//					$this->sendManialink($xml, $changes['Login'], 0, false, 0);
//				}
//				else {
//					$this->buildCheckpointCountWidget(-1, $changes['Login']);
//				}
//			}

			// Refresh Player and Team membership
			if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {

				// Get Player
				if ($player = $aseco->server->players->getPlayer($changes['Login'])) {
					// Store the (possible changed) TeamId
					$player->data['PluginRecordsEyepiece']['Prefs']['TeamId'] = $changes['TeamId'];
				}
			}

		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// If id = 0, bail out immediately
		if ($answer[2] === 0) {
			return;
		}

		// Get Player
		if (!$player = $aseco->server->players->getPlayer($answer[1])) {
			return;
		}

		// Init
		$widgets = '';
		$require_action = false;

		// Setup the answer index
		$answer_index = array(
			'showDedimaniaRecordsWindow',
			'showLocalRecordsWindow',
			'showLiveRankingsWindow',
			'showMusiclistWindow',
			'dropCurrentJukedSong',
			'showMaplistWindow',
			'showMaplistFilterWindow',
			'showMaplistSortingWindow',
			'showMapAuthorlistWindow',
			'showHelpWindow',
			'showToplistWindow',
			'showTopRankingsWindow',
			'showTopNationsWindow',
			'showTopWinnersWindow',
			'showMostRecordsWindow',
			'showMostFinishedWindow',
			'showTopPlaytimeWindow',
			'showTopDonatorsWindow',
			'showTopMapsWindow',
			'showTopVotersWindow',
			'showTopActivePlayersWindow',
			'showTopWinningPayoutWindow',
			'showTopBetwinsWindow',
			'showTopRoundscoreWindow',
			'showTopVisitorsWindow',
			'showTopContinentsWindow',
		);

		// Setup the donate answer index
		$donate_answer_index = array();
		foreach (range(1,10) as $id) {
			$donate_answer_index[] = 'donateAmount'. sprintf("%02d", $id);
		}

		// Setup the donate answer index
		$placement_answer_index = array();
		foreach (range(1,25) as $id) {
			$placement_answer_index[] = 'chatCommand'. sprintf("%02d", $id);
		}

		// Setup the jukebox answer index
		$jukebox_answer_index = array();
		foreach (range(1,20) as $id) {
			$jukebox_answer_index[] = 'addMapToJukebox'. sprintf("%02d", $id);
		}

		// Setup the authorname filter answer index
		$authorname_answer_index = array();
		foreach (range(1,80) as $id) {
			$authorname_answer_index[] = 'showMaplistWindowFilterAuthor'. sprintf("%02d", $id);
		}

		if ($answer[2] == 382009003) {

			// Toggle RecordsWidget for calling Player (F7)
			$this->toggleWidgets($answer[1]);

		}
		else if ($answer[2] == 'closeMainWindow') {

			$widgets .= $this->closeAllWindows();

		}
		else if ($answer[2] == 'closeSubWindow') {

			$widgets .= $this->closeAllSubWindows();

		}
		else if ($answer[2] == 'showLastCurrentNextMapWindow') {

			$widgets .= (($this->cache['MapWidget']['Window'] != false) ? $this->cache['MapWidget']['Window'] : '');

		}
		else if ($answer[2] == 'showManiaExchangeMapInfoWindow') {

			$widgets .= $this->buildManiaExchangeMapInfoWindow();

		}
		else if ($answer[2] == 'askDropMapJukebox') {

			$widgets .= $this->buildAskDropMapJukebox();

		}
		else if ($answer[2] == 'dropMapJukebox') {

			// Drop all Maps from the Jukebox
			if ( function_exists('chat_admin') ) {
				$command['author'] = $player;
				$command['params'] = 'clearjukebox';
				chat_admin($aseco, $command);
			}

			// Close SubWindow
			$widgets .= $this->closeAllSubWindows();

			// Rebuild the Maplist
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$require_action = true;

		}
		else if ($answer[2] == 'dropCurrentJukedSong') {

			if ( function_exists('chat_music') ) {
				$command['author'] = $player;
				$command['params'] = 'drop';
				chat_music($aseco, $command);
			}

		}
		else if ($answer[2] == 'showMaplistWindow') {

			if (count($player->data['PluginRecordsEyepiece']['Maplist']['Records']) == 0) {
				if ( (count($this->cache['MapList']) > $this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0]) && ($this->config['SHOW_PROGRESS_INDICATOR'][0]['MAPLIST'][0] != 0) ) {
					$this->sendProgressIndicator($player->login);
				}

				// Load all local records from calling Player
				$player->data['PluginRecordsEyepiece']['Maplist']['Records'] = $this->getPlayerLocalRecords($player->id);

				// Load all Maps that the calling Player did not finished yet
				$player->data['PluginRecordsEyepiece']['Maplist']['Unfinished'] = $this->getPlayerUnfinishedMaps($player->id);
			}
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = false;
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyCanyonMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('environment' => 'CANYON');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyStadiumMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('environment' => 'STADIUM');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyValleyMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('environment' => 'VALLEY');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterJukeboxedMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'JUKEBOX');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterNoRecentMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NORECENT');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyRecentMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'ONLYRECENT');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsWithoutRank') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NORANK');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsWithRank') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'ONLYRANK');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsNoGoldTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOGOLD');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsNoAuthorTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOAUTHOR');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterMapsWithMoodSunrise') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('mood' => 'SUNRISE');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterMapsWithMoodDay') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('mood' => 'DAY');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterMapsWithMoodSunset') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('mood' => 'SUNSET');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterMapsWithMoodNight') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('mood' => 'NIGHT');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMultilapMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'ONLYMULTILAP');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterNoMultilapMaps') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOMULTILAP');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsNoSilverTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOSILVER');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsNoBronzeTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOBRONZE');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterOnlyMapsNotFinished') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('cmd' => 'NOFINISH');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingBestPlayerRank') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'BEST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingWorstPlayerRank') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'WORST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingShortestAuthorTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'SHORTEST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingLongestAuthorTime') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'LONGEST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingNewestMapsFirst') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'NEWEST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingOldestMapsFirst') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'OLDEST');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingByMapname') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'MAPNAME');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingByAuthorname') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'AUTHORNAME');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingByKarmaBestMapsFirst') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'BESTMAPS');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingByKarmaWorstMapsFirst') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'WORSTMAPS');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowSortingByAuthorNation') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('sort' => 'AUTHORNATION');
			$require_action = true;

		}
		else if ($answer[2] == 'showMaplistWindowFilterByKeyword') {

			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
	//		$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;						// already setup in chat_elist()!
	//		$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('key' => $command['params']);	// already setup in chat_elist()!
			$require_action = true;

		}
		else if ( in_array($answer[2], $donate_answer_index) ) {

			// Get the wished amount
			$action = intval( str_replace('donateAmount', '', $answer[2]) );

			// Activate the Donation
			if ( function_exists('chat_donate') ) {
				$amount = explode(',', $this->config['DONATION_WIDGET'][0]['AMOUNTS'][0]);

				$command['author'] = $player;
				$command['params'] = (int)$amount[$action];
				chat_donate($aseco, $command);
			}

			// Let the Player know that the Donation starts at Race (and not within Score)
			$widgets .= $this->cache['DonationWidget']['Loading'];

		}
		else if ( in_array($answer[2], $placement_answer_index) ) {

			// Find the ID
			$mlid = intval( str_replace('chatCommand', '', $answer[2]) );
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ( (isset($placement['CHAT_MLID'][0])) && ($placement['CHAT_MLID'][0] == $mlid) ) {

					$chat = explode(' ', $placement['CHAT_COMMAND'][0], 2);
					$chat[0] = str_replace('/', '', $chat[0]);		// Remove possible "/"

					if ( function_exists('chat_'. $chat[0]) ) {
						$command['author'] = $player;
						$command['params'] = $chat[1];

						call_user_func('chat_'. $chat[0], $aseco, $command);
					}
					break;
				}
			}
			unset($placement);

		}
		else if ( in_array($answer[2], $jukebox_answer_index) ) {

			// Get the selected Map
			$id = intval( str_replace('addMapToJukebox', '', $answer[2]) - 1 );

			// Store wished Map in Player object for jukeboxing with plugin.rasp_jukebox.php
			$item = array();
			$item['name']		= $player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][$id]['name'];
			$item['author']		= $player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][$id]['author'];
			$item['environment']	= $player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][$id]['environment'];
			$item['filename']	= $player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][$id]['filename'];
			$item['uid']		= $player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][$id]['uid'];
			$player->maplist = array();
			$player->maplist[] = $item;

			// Juke the selected Map
			$aseco->releaseChatCommand('/jukebox 1', $player->login);

			// Refresh on juke'd map
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$require_action = true;

		}
		else if ( in_array($answer[2], $authorname_answer_index) ) {

			// Find the selected MapAuthor
			$current_page = ((($player->data['PluginRecordsEyepiece']['Window']['Page'] + 1) * 80) - 80);
			$id = abs( ($current_page + intval( str_replace('showMaplistWindowFilterAuthor', '', $answer[2]) ) ) ) - 1;

			// Show the MaplistWindow (but only Maps from the selected MapAuthor)
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = array('author' => $this->cache['MapAuthors'][$id]);
			$require_action = true;

		}
		else if ( ($answer[2] >= -2100) && ($answer[2] <= -2001) ) {

			if ($this->config['FEATURES'][0]['MAPLIST'][0]['FORCE_MAPLIST'][0] == true) {
				// Refresh on drop map from jukebox (action from plugin.rasp_jukebox.php)

				$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
				$require_action = true;
			}

		}
		else if ( ($answer[2] >= -4000) && ($answer[2] <= -2101) ) {

			// It is required to refresh the SongIds from $aseco->plugins['PluginMusicServer']->songs
			$this->config['States']['MusicServerPlaylist']['NeedUpdate'] = true;

			if ($this->config['FEATURES'][0]['SONGLIST'][0]['FORCE_SONGLIST'][0] == true) {
				// Refresh on juke'd song (action from plugin.musicserver.php)
				$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'MusiclistWindow';
				$require_action = true;
			}

		}
		else if ( ($answer[2] == 20) && ($this->config['FEATURES'][0]['MAPLIST'][0]['FORCE_MAPLIST'][0] == true) ) {

			// Refresh on drop complete jukebox (action from plugin.rasp_jukebox.php)
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = 'showMaplistWindow';
			$require_action = true;

		}
		else if ( in_array($answer[2], $answer_index) ) {

			// Set the Window action
			if ($player->data['PluginRecordsEyepiece']['Window']['Action'] != $answer[2]) {
				$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			}
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = $answer[2];
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPagePrev') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] -= 1;
			if ($player->data['PluginRecordsEyepiece']['Window']['Page'] < 0) {
				$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			}
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPagePrevTwo') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] -= 2;
			if ($player->data['PluginRecordsEyepiece']['Window']['Page'] < 0) {
				$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			}
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPageFirst') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPageNext') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] += 1;
			if ($player->data['PluginRecordsEyepiece']['Window']['Page'] > $player->data['PluginRecordsEyepiece']['Window']['MaxPage']) {
				$player->data['PluginRecordsEyepiece']['Window']['Page'] = $player->data['PluginRecordsEyepiece']['Window']['MaxPage'];
			}
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPageNextTwo') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] += 2;
			if ($player->data['PluginRecordsEyepiece']['Window']['Page'] > $player->data['PluginRecordsEyepiece']['Window']['MaxPage']) {
				$player->data['PluginRecordsEyepiece']['Window']['Page'] = $player->data['PluginRecordsEyepiece']['Window']['MaxPage'];
			}
			$require_action = true;

		}
		else if ($answer[2] == 'WindowPageLast') {

			$player->data['PluginRecordsEyepiece']['Window']['Page'] = $player->data['PluginRecordsEyepiece']['Window']['MaxPage'];
			$require_action = true;

		}


		// Nothing above matched, so the Player want to see the prev/next page in the current open Window
		if ($require_action == true) {
			if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showDedimaniaRecordsWindow') {

				$widgets .= $this->buildDedimaniaRecordsWindow($player->login);

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showLocalRecordsWindow') {

				$result = $this->buildLocalRecordsWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showLiveRankingsWindow') {

				if ( function_exists('ast_showScoretable') ) {
					$widgets .= $this->closeAllWindows();
					ast_showScoretable($aseco, $player->login, 0, true, 0);		// $aseco, $caller, $timeout, $display_close, $page
				}
				else {
					$result = $this->buildLiveRankingsWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login);
					$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
					$widgets .= $result['xml'];
				}

			}
			else if ( ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMusiclistWindow') || ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'dropCurrentJukedSong') ) {

				$result = $this->buildMusiclistWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMaplistWindow') {

				$result = $this->buildMaplistWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMapAuthorlistWindow') {

				$result = $this->buildMapAuthorlistWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showHelpWindow') {

				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = 2;
				$widgets .= $this->buildHelpWindow($player->data['PluginRecordsEyepiece']['Window']['Page']);

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showToplistWindow') {

				$result = $this->buildToplistWindow($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopRankingsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_RANKINGS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopWinnersWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_WINNERS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMostRecordsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'MOST_RECORDS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMostFinishedWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'MOST_FINISHED', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopPlaytimeWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_PLAYTIME', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopDonatorsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_DONATORS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopMapsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_MAPS', array('karma', 'map'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopVotersWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_VOTERS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopActivePlayersWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_ACTIVE_PLAYERS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopWinningPayoutWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_WINNING_PAYOUTS', array('won', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopBetwinsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_BETWINS', array('won', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopRoundscoreWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_ROUNDSCORE', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopVisitorsWindow') {

				$result = $this->buildScorelistWindowEntry($player->data['PluginRecordsEyepiece']['Window']['Page'], $player->login, 'TOP_VISITORS', array('score', 'nickname'));
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopNationsWindow') {

				$result = $this->buildTopNationsWindow($player->data['PluginRecordsEyepiece']['Window']['Page']);
				$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = $result['maxpage'];
				$widgets .= $result['xml'];

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showTopContinentsWindow') {

				$widgets .= $this->buildTopContinentsWindow();

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMaplistFilterWindow') {

				$widgets .= $this->buildMaplistFilterWindow();

			}
			else if ($player->data['PluginRecordsEyepiece']['Window']['Action'] == 'showMaplistSortingWindow') {

				$widgets .= $this->buildMaplistSortingWindow();

			}
		}


		// Send all widgets
		if ($widgets != '') {
			// Send Manialink
			$this->sendManialink($widgets, $player->login, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onDedimaniaRecordsLoaded ($aseco, $records) {

		if ( ( isset($aseco->plugins['PluginDedimania']->db['Map']['Records']) ) && (count($aseco->plugins['PluginDedimania']->db['Map']['Records']) > 0) ) {
			// Records are loaded, now we can get them into 'DedimaniaRecords' and force reload of the DedimaniaRecordsWidget
			$this->config['States']['DedimaniaRecords']['NeedUpdate'] = true;
			$this->buildRecordWidgets(false, array('DedimaniaRecords' => true, 'LocalRecords' => false, 'LiveRankings' => false));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecord ($aseco, $record) {

		// Player reached an new Record at the Map, need to Update the 'DedimaniaRecords'
		$this->config['States']['DedimaniaRecords']['NeedUpdate']	= true;
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.localdatabase.php
	public function onLocalRecord ($aseco, $finish_item) {


		// Check if the Player has already a LocalRecord, if not, only then increase MostRecords
		// to prevent double countings.
		$found = false;
		foreach ($this->scores['LocalRecords'] as $item) {
			if ($finish_item->player->login == $item['login']) {
				$found = true;
				break;
			}
		}
		unset($item);
		if ($found == false) {
			// Get the Player object
			$player = $aseco->server->players->player_list[$finish_item->player->login];

			// Increase Record count for this Player
			$query = "UPDATE `%prefix%players` SET `MostRecords` = `MostRecords` + 1 WHERE `PlayerId` = '". $player->id ."';";
			$result = $aseco->db->query($query);
			if (!$result) {
				$aseco->console('[RecordsEyepiece] UPDATE `MostRecords` failed. [for statement "'. $query .'"]!');
			}

			foreach ($this->scores['MostRecords'] as &$item) {
				if ($finish_item->player->login == $item['login']) {
					$item['score']++;
					break;
				}
			}
			unset($item);

			// Resort by 'score'
			$data = array();
			foreach ($this->scores['MostRecords'] as $key => $row) {
				$data[$key] = $row['scoplain'];
			}
			array_multisort($data, SORT_NUMERIC, SORT_DESC, $this->scores['MostRecords']);
			unset($data, $key, $row);
		}

		// Player reached an new Record at the Map, need to Update the 'LocalRecords'
		$this->config['States']['LocalRecords']['NeedUpdate']		= true;
		$this->config['States']['LocalRecords']['NoRecordsFound']	= false;
		$this->config['States']['LocalRecords']['ChkSum']		= false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.rasp_jukebox.php/chat.admin.php
	// $command[0] = 'add', 'clear', 'drop', 'play', 'replay', 'restart', 'skip', 'previous', 'nextenv'
	// $command[1] = map data (or 'null' for the 'clear' action)
	public function onJukeboxChanged ($aseco, $command) {


		// Init
		$widgets = '';

		if ($command[0] == 'clear') {
			$this->cache['Map']['Jukebox'] = false;

			// Store the Next-Map
			$this->cache['Map']['Next']		= $this->getNextMap();

			// Rebuild the Widgets
			$this->cache['MapWidget']['Window']	= $this->buildLastCurrentNextMapWindow();
			$this->cache['MapWidget']['Score']		= $this->buildMapWidget('score');
		}

		// Check for changed Jukebox and refresh if required
		$actions = array('add', 'drop', 'play', 'replay', 'restart', 'skip', 'previous', 'nextenv');
		if ( in_array($command[0], $actions) ) {
			// Is a Map in the Jukebox?
			if (count($aseco->plugins['PluginRaspJukebox']->jukebox) > 0) {
				foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $map) {
					// Need just the next juke'd Map Information, not more
					$this->cache['Map']['Jukebox'] = $map;
					break;
				}
				unset($map);
			}
			else {
				$this->cache['Map']['Jukebox'] = false;
			}


			// Store the Next-Map
			$this->cache['Map']['Next']		= $this->getNextMap();

			// Rebuild the Widgets
			$this->cache['MapWidget']['Window']	= $this->buildLastCurrentNextMapWindow();
			$this->cache['MapWidget']['Score']		= $this->buildMapWidget('score');

			// Check if we are at score and refresh the "Next Map" Widget
			if ($aseco->server->gamestate == Server::SCORE) {

				if ( ($command[0] == 'replay') || ($command[0] == 'restart') || ($command[0] == 'skip') || ($command[0] == 'previous') || ($command[0] == 'nextenv') ) {
					// Display the MapWidget (if enabled)
					$widgets .= (($this->cache['MapWidget']['Score'] != false) ? $this->cache['MapWidget']['Score'] : '');
				}
			}
		}

		if ($widgets != '') {
			// Send Manialink to all Players
			$this->sendManialink($widgets, false, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from chat.admin.php, plugin.rasp_jukebox.php
	// $command[0] = 'add', 'remove', 'rename', 'juke', 'unjuke', 'read' & 'write'
	// $command[1] = filename of Map (or 'null' for the 'write' or 'read' action)
	public function onMapListChanged ($aseco, $command) {


		// Init
		$widgets = '';

		// Set to 'true' on several parameter to prevent redo this at the event 'onMapListModified'
		if ( ($command[0] == 'add') || ($command[0] == 'remove') || ($command[0] == 'rename') || ($command[0] == 'juke') ) {
			$this->config['States']['MaplistRefreshProgressed'] = true;
		}

		// Check for changed Maplist and refresh complete or partial
		if ($command[0] == 'read') {
			// Get the new Maplist
			$this->getMaplist(false);

			if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
				// Refresh the MapcountWidget
				$this->cache['MapcountWidget'] = $this->buildMapcountWidget();

				// Display the MapcountWidget to all Player
				$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
			}
		}
		else if ( ($command[0] == 'add') || ($command[0] == 'juke') ) {
			// Get the new Maplist
			$this->getMaplist($command[1]);

			if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
				// Refresh the MapcountWidget
				$this->cache['MapcountWidget'] = $this->buildMapcountWidget();

				// Display the MapcountWidget to all Player
				$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
			}
		}
		else if ( ($command[0] == 'remove') || ($command[0] == 'unjuke') ) {

			// Remove server path
			$filename = str_replace($aseco->server->mapdir, '', $command[1]);

			// Find the removed Map and remove them here too
			$maplist = array();
			$i = 0;
			foreach ($this->cache['MapList'] as &$map) {
				if ($map['filename'] != $filename) {
					// Rebuild the ID for each Map (hole away)
					$map['id'] = $i;
					$maplist[] = $map;

					$i ++;
				}
			}
			unset($map);

			// Replace with the new list
			$this->cache['MapList'] = $maplist;

			if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
				// Refresh the MapcountWidget
				$this->cache['MapcountWidget'] = $this->buildMapcountWidget();

				// Display the MapcountWidget to all Player
				$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
			}
		}

		// Clean the local records cache from MaplistWindow at every Player
		if ( ($command[0] == 'read') || ($command[0] == 'remove') ) {
			foreach ($aseco->server->players->player_list as $player) {
				$player->data['PluginRecordsEyepiece']['Maplist']['Records'] = array();
			}
			unset($player);
		}

		if ($widgets != '') {
			// Send Manialink to all Players
			$this->sendManialink($widgets, false, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $data[0]=CurChallengeIndex, $data[1]=NextChallengeIndex, $data[2]=IsListModified
	public function onMapListModified ($aseco, $data) {


		// Reload the Maplist now
		if ($data[2] !== false) {
			// Do the work not again, if already done at 'onMaplistChanged'
			if ($this->config['States']['MaplistRefreshProgressed'] == true) {
				$this->config['States']['MaplistRefreshProgressed'] = false;
				return;
			}

			// Init
			$widgets = '';

			if ($aseco->plugins['PluginRasp']->reset_cache_start) {
				// Get the new Maplist
				$this->getMaplist(false);

				if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
					// Refresh the MapcountWidget
					$this->cache['MapcountWidget'] = $this->buildMapcountWidget();

					// Display the MapcountWidget to all Player
					if ($aseco->server->gamestate == Server::RACE) {
						$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
					}
				}
			}

			// Store the Next-Map
			$this->cache['Map']['Next'] = $this->getNextMap();

			// Rebuild the Widgets
			$this->cache['MapWidget']['Window'] = $this->buildLastCurrentNextMapWindow();
			$this->cache['MapWidget']['Score']	= $this->buildMapWidget('score');


			// Include the MapWidget (if enabled)
			if ($aseco->server->gamestate == Server::SCORE) {
				$widgets .= (($this->cache['MapWidget']['Score'] != false) ? $this->cache['MapWidget']['Score'] : '');
			}

			if ($widgets != '') {
				// Send Manialink to all Players
				$this->sendManialink($widgets, false, 0);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.musicserver.php
	public function onMusicboxReloaded ($aseco) {


		// Build the Playlist-Cache
		$this->getMusicServerPlaylist(false, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.vote_manager.php
	public function onVotingRestartMap ($aseco) {


		// Store the Current-Map as the Next-Map (restart voting passed)
		$this->cache['Map']['Next'] = $this->cache['Map']['Current'];

		// Rebuild the Widgets
		$this->cache['MapWidget']['Score']		= $this->buildMapWidget('score');
		$this->cache['MapWidget']['Window']	= $this->buildLastCurrentNextMapWindow();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.donate.php
	// $donation = [0]=login, [1]=planets
	public function onDonation ($aseco, $donation) {

		// Increase donations for this Player if in TOP
		$found = false;
		foreach ($this->scores['TopDonators'] as &$item) {
			if ($item['login'] == $donation[0]) {
				$item['scoplain'] += $donation[1];
				$item['score'] = $this->formatNumber($item['scoplain'], 0) .' P';

				// Maybe need to resort if one Player now donate more then an other
				$found = true;
				break;
			}
		}
		unset($item);
		if ($found == false) {
			// Get Player object
			if ($player = $aseco->server->players->getPlayer($donation[0])) {
				// Add the Player to the TopDonators
				$this->scores['TopDonators'][] = array(
					'login'		=> $player->login,
					'nickname'	=> $this->handleSpecialChars($player->nickname),
					'score'		=> $this->formatNumber((int)$donation[1], 0) .' P',
					'scoplain'	=> (int)$donation[1]
				);
			}
		}

		// Now resort the TopDonators by score
		$score = array();
		foreach ($this->scores['TopDonators'] as $key => $row) {
			$score[$key] = $row['scoplain'];
		}
		array_multisort($score, SORT_DESC, $this->scores['TopDonators']);
		unset($score, $row);

		// Refresh the Array now
		$this->getTopDonators();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.tm-karma-dot-com.php or plugin.rasp_karma.php
	public function onKarmaChange ($aseco, $karma) {

		// Notice that the Karma need a refresh
		$this->config['States']['TopMaps']['NeedUpdate'] = true;
		$this->config['States']['TopVoters']['NeedUpdate'] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onWarmUpStatusChanged ($aseco, $status) {
		$this->config['States']['RoundScore']['WarmUpPhase'] = $status;
	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function onLoadingMap ($aseco, $id) {
//
//		// Get current Gamemode
//		$gamemode = $aseco->server->gameinfo->mode;
//
//		// At $gamemode 'Rounds', 'Team' and 'Cup' need to emulate 'onBeginMap'
//		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) || ($gamemode == Gameinfo::CUP) ) {
//			$this->onBeginMap($aseco, false);
//		}
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $id) {

		// Close the Scoretable-Lists at all Players
		$widgets = $this->closeScoretableLists();
		$this->sendManialink($widgets, false, 0);

		// Check if it is time to switch from "normal" to NiceMode or back
		$this->checkServerLoad();

		// Refresh Scoretable lists
		$this->refreshScorelists();

		// Refresh the Playlist-Cache
		if ( ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) && ($this->config['States']['MusicServerPlaylist']['NeedUpdate'] == true) ) {
			$this->getMusicServerPlaylist(true, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginRound ($aseco) {

		// Init
		$widgets = '';

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// At Gamemode 'Rounds' or 'Team' need to refresh now
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) ) {

			// Build the RecordWidgets and ONLY in normal mode send it to each or given Player (if refresh is required)
			$this->buildRecordWidgets(false, false);

			if ($this->config['States']['NiceMode'] == true) {
				// Display the RecordWidgets to all Players
				$widgets .= $this->showRecordWidgets(false);
			}
		}

		// Build the RoundScoreWidget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			// Reset round and display an empty Widget
			$this->scores['RoundScore'] = array();
			$widgets .= $this->buildRoundScoreWidget($gamemode, false);
		}

		// Send widgets to all Players
		if ($widgets != '') {
			// Send Manialink
			$this->sendManialink($widgets, false, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco) {

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// At Gamemode 'Rounds', 'Team' or 'Cup' need to refresh now
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) || ($gamemode == Gameinfo::CUP) ) {
			$this->config['States']['LiveRankings']['NeedUpdate']	= true;
			$this->config['States']['LiveRankings']['NoRecordsFound']	= false;

			// Force the refresh
			$this->config['States']['RefreshTimestampRecordWidgets'] = 0;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map_item) {

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Special handling for Gamemode 'Laps'
		if ( ($gamemode != Gameinfo::LAPS) && (!empty($aseco->registered_events['onPlayerCheckpoint'])) ) {
			// Unregister (possible registered) 'onPlayerCheckpoint' event for Gamemode 'Laps' if this is not 'Laps'
			foreach ($aseco->registered_events['onPlayerCheckpoint'] as &$item) {
				if ($item[0]->getClassname() == $this->getClassname()) {
					$aseco->console('[RecordsEyepiece] Unregister event "onPlayerCheckpoint", currently not required.');
					unset($item);
					break;
				}
			}
			unset($item);
		}
		else if ( ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['ENABLED'][0] == true) || ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['ENABLED'][0] == true) ) {
			// Register event 'onPlayerCheckpoint' in Gamemode 'Laps'
			// if <live_rankings><laps> is enabled
			// or when <round_score><gamemode><laps> is enabled
			$found = false;
			foreach ($aseco->registered_events['onPlayerCheckpoint'] as $item) {
				if ($item[0]->getClassname() == $this->getClassname()) {
					$found = true;
					break;
				}
			}
			unset($item);
			if ($found == false) {
				$aseco->registerEvent('onPlayerCheckpoint', array($this, 'onPlayerCheckpoint'));
				$aseco->console('[RecordsEyepiece] Register event "onPlayerCheckpoint" to enabled wanted Widgets.');
			}
		}


		// Setup the no-score Placeholder depending at the current Gamemode
		if ($gamemode == Gameinfo::STUNTS) {
			$this->config['PlaceholderNoScore'] = '---';
		}
		else {
			$this->config['PlaceholderNoScore'] = '-:--.---';
		}


		// Build the RoundScorePoints for the current Gamemode
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::CUP) ) {
				if ($aseco->server->gameinfo->rounds['UseAlternateRules'] == true) {
					// Only the first wins, no draw!
					$this->config['RoundScore']['Points'][Gameinfo::ROUNDS] = array(1,0);
				}
				else {
					$this->config['RoundScore']['Points'][Gameinfo::ROUNDS] = $aseco->server->gameinfo->rounds['PointsRepartition'];
				}

				// Copy 'Rounds' to 'Cup', always the same, also with "new rules" enabled
				$this->config['RoundScore']['Points'][Gameinfo::CUP] = $this->config['RoundScore']['Points'][Gameinfo::ROUNDS];
			}
			else if ($gamemode == Gameinfo::TEAM) {
				$this->config['RoundScore']['Points'][Gameinfo::TEAM] = array(1);
//				$this->config['RoundScore']['Points'][Gameinfo::TEAM] = $aseco->server->gameinfo->team['PointsRepartition'];
			}
		}

		foreach ($aseco->server->players->player_list as $player) {
			// Reset at each Player the Hash
			$this->cache['PlayerStates'][$player->login]['DedimaniaRecords']	= false;
			$this->cache['PlayerStates'][$player->login]['LocalRecords']	= false;
			$this->cache['PlayerStates'][$player->login]['LiveRankings']	= false;
			$this->cache['PlayerStates'][$player->login]['FinishScore']	= -1;

			// Clean the local recs cache from MaplistWindow and reset WindowAction to default
			$player->data['PluginRecordsEyepiece']['Window']['Action'] = false;
			$player->data['PluginRecordsEyepiece']['Window']['Page'] = 0;
			$player->data['PluginRecordsEyepiece']['Window']['MaxPage'] = 0;
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = false;
			$player->data['PluginRecordsEyepiece']['Maplist']['Records'] = array();
			$player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'] = array();
		}


		if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			// Clean up
			$this->cache['WinningPayoutPlayers'] = array();

			// Add all Players to the Cache
			foreach ($aseco->server->players->player_list as $player) {
				$this->cache['WinningPayoutPlayers'][$player->login] = array(
					'id'		=> $player->id,
					'login'		=> $player->login,
					'nickname'	=> $player->nickname,
					'ladderrank'	=> $player->ladderrank
				);
			}

			// Reset the limit and let the Player win again, only if this is not disabled.
			if ($this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RESET_LIMIT'][0] > 0) {
				foreach ($this->cache['PlayerWinnings'] as $login => $struct) {
					if ($this->cache['PlayerWinnings'][$login]['TimeStamp'] > 0) {
						if ( (time() >= ($this->cache['PlayerWinnings'][$login]['TimeStamp'] + $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RESET_LIMIT'][0])) ) {
							// Reset when <reset_limit> was reached
							$this->cache['PlayerWinnings'][$login]['FinishPayment']	= 0;
							$this->cache['PlayerWinnings'][$login]['FinishPaid']	= 0;
							$this->cache['PlayerWinnings'][$login]['TimeStamp']	= 0;
						}
					}
				}
			}

			// Remove all old Players from the array.
			$new['PlayerWinnings'] = array();
			foreach ($this->cache['PlayerWinnings'] as $login => $struct) {
				if ($this->cache['PlayerWinnings'][$login]['TimeStamp'] > 0) {
					// Add all Players with an none empty Players
					$new['PlayerWinnings'][$login] = $struct;
				}
				else if ( isset($aseco->server->players->player_list[$login]) ) {
					// Add all Players that are currently connected
					$new['PlayerWinnings'][$login] = $struct;
				}
			}
			unset($this->cache['PlayerWinnings']);
			$this->cache['PlayerWinnings'] = $new['PlayerWinnings'];
			unset($new['PlayerWinnings']);
		}


		// If it is Sunday and the check for LadderLimits is enabled, request the
		// LadderLimits and rebuild the LadderLimitWidget
		if ( ($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0] == true) && ($this->config['LADDERLIMIT_WIDGET'][0]['ROC_SERVER'][0] == true) && (date('N') == 7) ) {
			$this->cache['LadderLimitWidget'] = $this->buildLadderLimitWidget();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map_item) {

		// Take control from the server
		$aseco->client->query('ManualFlowControlEnable', true);

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Store the Last/Current/Next-Map data
		$this->cache['Map']['Last']		= $this->getLastMap($map_item);
		$this->cache['Map']['Current']		= $this->getCurrentMap();
		$this->cache['Map']['Next']		= $this->getNextMap();

		// Display the MapWidget (need this placed here, then only now all required data filled at event onBeginMap1)
		$this->cache['MapWidget']['Race']	= $this->buildMapWidget('race');
		$this->cache['MapWidget']['Score']	= $this->buildMapWidget('score');
		$this->cache['MapWidget']['Window']	= $this->buildLastCurrentNextMapWindow();


		// At Gamemode 'Laps' and with enabled <checkpointcount_widget> store the NbLabs from Dedicated-Server
		// and NOT the value from the $map_item, because they does not match the reality!
		if ( ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0] == true) && ($gamemode == Gameinfo::LAPS) ) {
			$this->cache['Map']['NbCheckpoints'] = $map_item->nbcheckpoints;
			$this->cache['Map']['NbLaps'] = $aseco->server->gameinfo->laps['ForceLapsNb'];
		}
		else if ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
			$this->cache['Map']['NbCheckpoints'] = $map_item->nbcheckpoints;
			$this->cache['Map']['NbLaps'] = $map_item->nblaps;
		}


	        // Store the forced Laps for 'Rounds', 'Team' and 'Cup'
		$this->cache['Map']['ForcedLaps'] = $aseco->server->gameinfo->rounds['ForceLapsNb'];

		// Init
		$widgets = '';

		// Is the CheckpointCountWidget enabled?
		if ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildCheckpointCountWidget(-1, false);
		}

		if ($this->config['CLOCK_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildClockWidget();
		}

		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Music Widget to all Players
			$this->getCurrentSong();
			$this->cache['MusicWidget'] = $this->buildMusicWidget();

			if ($this->config['States']['NiceMode'] == false) {
				foreach ($aseco->server->players->player_list as $player) {

					// Display the MusicWidget only to the Player if they did'nt has them set to hidden
					if ( ($player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] == true) && ($this->cache['MusicWidget'] != false) ) {
						$this->sendManialink($this->cache['MusicWidget'], $player->login, 0);
					}
				}
				unset($player);
			}
			else {
				$widgets .= (($this->cache['MusicWidget'] != false) ? $this->cache['MusicWidget'] : '');
			}
		}

		if ($this->config['TOPLIST_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the TopList Widget to all Players
			$widgets .= $this->cache['ToplistWidget'];
		}

		if ($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the AddToFavorite Widget
			$widgets .= $this->cache['AddToFavoriteWidget']['Race'];
		}

		if ($this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) {
			// Build & Display the Gamemode Widget to all Players
			$this->cache['GamemodeWidget'] = $this->buildGamemodeWidget($gamemode);
			$widgets .= (($this->cache['GamemodeWidget'] != false) ? $this->cache['GamemodeWidget'] : '');
		}

		if ($this->config['VISITORS_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Visitors-Widget to all Players
			$widgets .= (($this->cache['VisitorsWidget'] != false) ? $this->cache['VisitorsWidget'] : '');
		}

		if ($this->config['MANIAEXCHANGE_WIDGET'][0]['ENABLED'][0] == true) {
			// Refresh the ManiaExchangeWidget
			$this->cache['ManiaExchangeWidget'] = $this->buildManiaExchangeWidget();

			// Display the MapcountWidget to all Player
			$widgets .= (($this->cache['ManiaExchangeWidget'] != false) ? $this->cache['ManiaExchangeWidget'] : '');
		}

		if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
			// Refresh the MapcountWidget
			$this->cache['MapcountWidget'] = $this->buildMapcountWidget();

			// Display the MapcountWidget to all Player
			$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
		}

		// Display the PlacementWidgets at state 'race'
		if ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->cache['PlacementWidget']['Race'];
			$widgets .= $this->cache['PlacementWidget'][$gamemode];
		}


		// Reset states of the Widgets
		$this->config['States']['DedimaniaRecords']['NeedUpdate']		= true;
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
		$this->config['States']['LocalRecords']['NeedUpdate']		= true;
		$this->config['States']['LocalRecords']['UpdateDisplay']		= true;
		$this->config['States']['LocalRecords']['NoRecordsFound']		= false;
		$this->config['States']['LiveRankings']['NeedUpdate']		= true;
		$this->config['States']['LiveRankings']['UpdateDisplay']		= true;
		$this->config['States']['LiveRankings']['NoRecordsFound']		= false;


		// Load the current Rankings
		$this->cache['CurrentRankings'] = array();
		if ( ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true) || ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
			if ($gamemode == Gameinfo::TEAM) {
				$this->cache['CurrentRankings'] = $this->getCurrentRanking(2,0);
			}
			else {
				// All other GameModes
				$this->cache['CurrentRankings'] = $this->getCurrentRanking(300,0);
			}
		}

		// Build the RecordWidgets and ONLY in normal mode send it to each or given Player (if refresh is required)
		$this->buildRecordWidgets(false, false);

		if ($this->config['States']['NiceMode'] == true) {
			// Display the RecordWidgets to all Players
			$widgets .= $this->showRecordWidgets(false);
		}

		// Just refreshed, mark as fresh
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']		= false;
		$this->config['States']['LocalRecords']['UpdateDisplay']		= false;
		$this->config['States']['LiveRankings']['UpdateDisplay']		= false;

		// Set next refresh timestamp
		$this->config['States']['RefreshTimestampRecordWidgets']		= (time() + $this->config['FEATURES'][0]['REFRESH_INTERVAL'][0]);

		// Set next refresh preload timestamp
		$this->config['States']['RefreshTimestampPreload']			= (time() + 5);

		// Store the possible Placeholder from MX
		if ( (isset($aseco->server->maps->current->mx) ) && ($aseco->server->maps->current->mx != false) ) {
			$this->config['PlacementPlaceholders']['MAP_MX_PREFIX']		= $aseco->server->maps->current->mx->prefix;
			$this->config['PlacementPlaceholders']['MAP_MX_ID']		= $aseco->server->maps->current->mx->id;
			$this->config['PlacementPlaceholders']['MAP_MX_PAGEURL']	= $aseco->server->maps->current->mx->pageurl;
		}
		else {
			// Map not at MX or MX down
			$this->config['PlacementPlaceholders']['MAP_MX_PREFIX']		= false;
			$this->config['PlacementPlaceholders']['MAP_MX_ID']		= false;
			$this->config['PlacementPlaceholders']['MAP_MX_PAGEURL']	= false;
		}
		$this->config['PlacementPlaceholders']['MAP_NAME']			= $aseco->server->maps->current->name;
		$this->config['PlacementPlaceholders']['MAP_UID']			= $aseco->server->maps->current->uid;


		// Include the MapWidget (if enabled)
		$widgets .= (($this->cache['MapWidget']['Race'] != false) ? $this->cache['MapWidget']['Race'] : '');


		// Build an empty RoundScoreWidget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			// Display an empty Widget
			$widgets .= $this->buildRoundScoreWidget($gamemode, false);
		}

		// Display the PlayerSpectatorWidget
		if ($this->config['PLAYER_SPECTATOR_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildPlayerSpectatorWidget();
		}

		// Display the CurrentRankingWidget
		if ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildCurrentRankingWidget(null);
		}

		// Display the LadderLimitWidget
		if ($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['LadderLimitWidget'] != false) ? $this->cache['LadderLimitWidget'] : '');
		}

		if ($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['ENABLED'][0] == true) {
			$widgets .= $this->templates['RECORDSEYEPIECEAD']['RACE'];
		}

//		if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
//			$widgets .= $this->buildLiveRankingsWidgetMS($gamemode, $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0]);
//		}

		// Send widgets to all Players
		if ($widgets != '') {
			// Send Manialink
			$this->sendManialink($widgets, false, 0);
		}


		// Reset state
		$this->config['States']['MaplistRefreshProgressed'] = false;

		// Give control back to the server
		$aseco->client->query('ManualFlowControlEnable', false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $checkpt = [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=CurrentLapTime, [6]=LapWaypointNumber
	// This event is only activated in Gamemode 'Laps'
	// if <live_rankings><laps> is enabled
	// or when <checkpointcount_widget> is enabled
	// or when <round_score><gamemode><laps> is enabled
	public function onPlayerCheckpoint ($aseco, $checkpt) {

		if ($aseco->server->gameinfo->mode == Gameinfo::LAPS && $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['ENABLED'][0] == true) {

			// Get the Player object
			$player = $aseco->server->players->player_list[$checkpt[0]];

			// Add the Score
			$this->scores['RoundScore'][$player->login] = array(
				'checkpointid'	=> ($checkpt[3] - 1),
				'playerid'	=> $player->pid,
				'login'		=> $player->login,
				'nickname'	=> $this->handleSpecialChars($player->nickname),
				'score'		=> $aseco->formatTime($checkpt[2]),
				'scoplain'	=> $checkpt[2]
			);

			// Display the Widget
			$this->buildRoundScoreWidget($aseco->server->gameinfo->mode, true);
		}

		// Only work at 'Laps'
		if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][Gameinfo::LAPS][0]['ENABLED'][0] == true && $this->cache['Map']['NbCheckpoints'] > 0) {
			// Let the LiveRankings refresh, when a Player drive through one
			$this->config['States']['LiveRankings']['NeedUpdate'] = true;
			$this->config['States']['LiveRankings']['NoRecordsFound'] = false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $uid) {

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Close the Scoretable-Lists at all Players
		$this->sendManialink($this->closeScoretableLists(), false, 0);

		// Init
		$widgets = '';

		// Display the PlacementWidgets at state 'race'
		if ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->cache['PlacementWidget']['Race'];
			$widgets .= $this->cache['PlacementWidget'][$gamemode];
		}

		if ($this->config['CLOCK_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildClockWidget();
		}

		if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Music Widget to all Players
			if ($this->config['States']['NiceMode'] == false) {
				foreach ($aseco->server->players->player_list as $player) {

					// Display the MusicWidget only to the Player if they did'nt has them set to hidden
					if ( ($player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] == true) && ($this->cache['MusicWidget'] != false) ) {
						$this->sendManialink($this->cache['MusicWidget'], $player->login, 0);
					}
				}
				unset($player);
			}

			else {
				$widgets .= (($this->cache['MusicWidget'] != false) ? $this->cache['MusicWidget'] : '');
			}
		}

		if ($this->config['TOPLIST_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the TopList Widget to all Players
			$widgets .= $this->cache['ToplistWidget'];
		}

		if ($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the AddToFavorite Widget
			$widgets .= $this->cache['AddToFavoriteWidget']['Race'];
		}

		if ($this->config['VISITORS_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the Visitors-Widget to all Players
			$widgets .= (($this->cache['VisitorsWidget'] != false) ? $this->cache['VisitorsWidget'] : '');
		}

		if ($this->config['MANIAEXCHANGE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the ManiaExchangeWidget to connecting Player
			$widgets .= (($this->cache['ManiaExchangeWidget'] != false) ? $this->cache['ManiaExchangeWidget'] : '');
		}

		if ($this->config['MAPCOUNT_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the MapcountWidget to all Player
			$widgets .= (($this->cache['MapcountWidget'] != false) ? $this->cache['MapcountWidget'] : '');
		}

		if ($this->config['LADDERLIMIT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['LadderLimitWidget'] != false) ? $this->cache['LadderLimitWidget'] : '');
		}

		if ($this->config['GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) {
			// Build & Display the Gamemode Widget to all Players
			$this->cache['GamemodeWidget'] = $this->buildGamemodeWidget($aseco->server->gameinfo->mode);
			$widgets .= (($this->cache['GamemodeWidget'] != false) ? $this->cache['GamemodeWidget'] : '');
		}

		if ($this->config['PLAYER_SPECTATOR_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildPlayerSpectatorWidget();
		}

		if ($this->config['CURRENT_RANKING_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildCurrentRankingWidget(null);
		}

		if ($this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['ENABLED'][0] == true) {
			$widgets .= $this->templates['RECORDSEYEPIECEAD']['RACE'];
		}

		// Reset at each Player the Hash
		foreach ($aseco->server->players->player_list as $player) {
			$this->cache['PlayerStates'][$player->login]['DedimaniaRecords']	= false;
			$this->cache['PlayerStates'][$player->login]['LocalRecords']		= false;
			$this->cache['PlayerStates'][$player->login]['LiveRankings']		= false;
			$this->cache['PlayerStates'][$player->login]['FinishScore']		= -1;
		}
		unset($player);

		// Reset states of the Widgets
		$this->config['States']['DedimaniaRecords']['NeedUpdate']	= true;
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= true;
		$this->config['States']['LocalRecords']['NeedUpdate']		= true;
		$this->config['States']['LocalRecords']['UpdateDisplay']	= true;
		$this->config['States']['LocalRecords']['NoRecordsFound']	= false;
		$this->config['States']['LiveRankings']['NeedUpdate']		= true;
		$this->config['States']['LiveRankings']['UpdateDisplay']	= true;
		$this->config['States']['LiveRankings']['NoRecordsFound']	= false;

		// Build the RecordWidgets and ONLY in normal mode send it to each or given Player (if refresh is required)
		$this->buildRecordWidgets(false, false);

		if ($this->config['States']['NiceMode'] == true) {
			// Display the RecordWidgets to all Players
			$widgets .= $this->showRecordWidgets(false);
		}

		// Just refreshed, mark as fresh
		$this->config['States']['DedimaniaRecords']['UpdateDisplay']	= false;
		$this->config['States']['LocalRecords']['UpdateDisplay']	= false;
		$this->config['States']['LiveRankings']['UpdateDisplay']	= false;

		// Set next refresh timestamp
		$this->config['States']['RefreshTimestampRecordWidgets'] = (time() + $this->config['FEATURES'][0]['REFRESH_INTERVAL'][0]);


		// Display the MapWidget (if enabled)
		$widgets .= (($this->cache['MapWidget']['Race'] != false) ? $this->cache['MapWidget']['Race'] : '');


		// Clear the RoundScore array and hide the Widget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			// Reset round
			$this->scores['RoundScore']	= array();
			$this->scores['RoundScorePB']	= array();

			// Reset Widget
			$widgets .= $this->buildRoundScoreWidget($gamemode, false);
		}

		// Send all widgets
		if ($widgets != '') {
			// Send Manialink
			$this->sendManialink($widgets, false, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $race) {

		// Bail out if there are no Players
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Init
		$widgets = '';

		// Close all RaceWidgets at all connected Players (incl. all Windows)
		$widgets .= $this->closeRaceWidgets(false, true);

		// Build the PlacementWidgets at state 'score'
		if ($this->config['PLACEMENT_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildPlacementWidget('score');
		}

		if ($this->config['WINNING_PAYOUT'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildWinningPayoutWidget();
		}

		if ($this->config['DONATION_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->cache['DonationWidget']['Default'];
		}

		if ($this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildTopAverageTimesForScore($this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ENTRIES'][0]);

			// Reset for the new Map
			$this->scores['TopAverageTimes'] = array();
		}
		if ($this->config['SCORETABLE_LISTS'][0]['DEDIMANIA_RECORDS'][0]['ENABLED'][0] == true) {
			// Hide Dedimania at Stunts-Mode
			if ($gamemode != Gameinfo::STUNTS) {
				if ($this->config['States']['DedimaniaRecords']['NeedUpdate'] == true) {
					$this->getDedimaniaRecords();
				}
				$widgets .= $this->buildScorelistWidgetEntry('DedimaniaRecordsWidget', $this->config['SCORETABLE_LISTS'][0]['DEDIMANIA_RECORDS'][0], $this->scores['DedimaniaRecords'], array('score', 'nickname'));
			}
		}
		if ($this->config['SCORETABLE_LISTS'][0]['LOCAL_RECORDS'][0]['ENABLED'][0] == true) {
			if ($this->config['States']['LocalRecords']['NeedUpdate'] == true) {
				$this->getLocalRecords($gamemode);
			}
			$widgets .= $this->buildScorelistWidgetEntry('LocalRecordsWidget', $this->config['SCORETABLE_LISTS'][0]['LOCAL_RECORDS'][0], $this->scores['LocalRecords'], array('score', 'nickname'));
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopRankings'] != false) ? $this->cache['TopRankings'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopWinners'] != false) ? $this->cache['TopWinners'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildScorelistWidgetEntry('MostRecordsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0], $this->scores['MostRecords'], array('score', 'nickname'));
		}
		if ($this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildScorelistWidgetEntry('MostFinishedWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0], $this->scores['MostFinished'], array('score', 'nickname'));
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopPlaytime'] != false) ? $this->cache['TopPlaytime'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_DONATORS'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildScorelistWidgetEntry('TopDonatorsWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_DONATORS'][0], $this->scores['TopDonators'], array('score', 'nickname'));
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopNations'] != false) ? $this->cache['TopNations'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopMaps'] != false) ? $this->cache['TopMaps'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopVoters'] != false) ? $this->cache['TopVoters'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopBetwins'] != false) ? $this->cache['TopBetwins'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopWinningPayouts'] != false) ? $this->cache['TopWinningPayouts'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopVisitors'] != false) ? $this->cache['TopVisitors'] : '');
		}
		if ($this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0]['ENABLED'][0] == true) {
			$widgets .= (($this->cache['TopActivePlayers'] != false) ? $this->cache['TopActivePlayers'] : '');
		}
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::CUP) ) {
			// Store the won RoundScore to the Database-Table
			$this->storePlayersRoundscore();

			// Refresh the TopRoundscore Array
			$this->getTopRoundscore();
			if ($this->config['SCORETABLE_LISTS'][0]['TOP_ROUNDSCORE'][0]['ENABLED'][0] == true) {
				$this->cache['TopRoundscore'] = $this->buildScorelistWidgetEntry('TopRoundscoreWidgetAtScore', $this->config['SCORETABLE_LISTS'][0]['TOP_ROUNDSCORE'][0], $this->scores['TopRoundscore'], array('score', 'nickname'));
				$widgets .= $this->cache['TopRoundscore'];
			}
		}
		if ($this->config['CLOCK_WIDGET'][0]['ENABLED'][0] == true) {
			$widgets .= $this->buildClockWidget();
		}

		// Display the MapWidget (if enabled)
		$widgets .= (($this->cache['MapWidget']['Score'] != false) ? $this->cache['MapWidget']['Score'] : '');


		// Clear the RoundScore array and hide the Widget
		if ($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			// Reset round
			$this->scores['RoundScore']	= array();
			$this->scores['RoundScorePB']	= array();

			// Hide the Widget
			$widgets .= '<manialink id="RoundScoreWidget" name="RoundScoreWidget"></manialink>';
		}

		if ($this->config['NEXT_ENVIRONMENT_WIDGET'][0]['ENABLED'][0] == true) {
			// Build & display the NextEnvironmentWidget
			$widgets .= $this->buildNextEnvironmentWidgetForScore();
		}

		if ($this->config['NEXT_GAMEMODE_WIDGET'][0]['ENABLED'][0] == true) {
			// Build & display the NextGamemodeWidget
			$widgets .= $this->buildNextGamemodeWidgetForScore();
		}

		if ($this->config['FAVORITE_WIDGET'][0]['ENABLED'][0] == true) {
			// Display the AddToFavorite Widget
			$widgets .= $this->cache['AddToFavoriteWidget']['Score'];
		}

		if ($this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['ENABLED'][0] == true) {
			$widgets .= $this->templates['RECORDSEYEPIECEAD']['SCORE'];
		}

		if ($widgets != '') {
			// Send Manialink to all Players
			$this->sendManialink($widgets, false, 0);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildToplistWidget () {
		return $this->templates['TOPLIST_WIDGET']['CONTENT'];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildAddToFavoriteWidget ($mode = 'RACE') {

		if ($this->config['FAVORITE_WIDGET'][0][$mode][0]['BACKGROUND_DEFAULT'][0] != '') {
			$bg = '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['FAVORITE_WIDGET'][0][$mode][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['FAVORITE_WIDGET'][0][$mode][0]['BACKGROUND_FOCUS'][0] .'" scriptevents="1" id="ButtonAddToFavoriteWidget"/>';
			$xml = str_replace(
				array(
					'%posx%',
					'%posy%',
					'%widgetscale%',
					'%background%',
				),
				array(
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['POS_X'][0],
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['POS_Y'][0],
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['SCALE'][0],
					$bg,
				),
				$this->templates['FAVORITE_WIDGET']['CONTENT']
			);
		}
		else {
			$bg = '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['FAVORITE_WIDGET'][0][$mode][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['FAVORITE_WIDGET'][0][$mode][0]['BACKGROUND_SUBSTYLE'][0] .'" scriptevents="1" id="ButtonAddToFavoriteWidget"/>';
			$xml = str_replace(
				array(
					'%posx%',
					'%posy%',
					'%widgetscale%',
					'%background%',
				),
				array(
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['POS_X'][0],
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['POS_Y'][0],
					$this->config['FAVORITE_WIDGET'][0][$mode][0]['SCALE'][0],
					$bg,
				),
				$this->templates['FAVORITE_WIDGET']['CONTENT']
			);
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildCurrentRankingWidget ($login = false) {
		global $aseco;

		if ($login === false) {
			// Called from onPlayerRankingUpdated
			if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {

				$info = 'FIRST';
				$first	= $aseco->server->rankings->getRank(1);
				$second	= $aseco->server->rankings->getRank(2);

				if (isset($first['score']) && isset($second['score'])) {
					if ($first['score'] == $second['score']) {
						if ($first['score'] > 0) {
							$team = 'TEAM';
							$info = 'DRAW';
						}
						else {
							$team = '---';
							$info = 'FIRST';
						}
					}
					else {
						if ($first['login'] == '*team:red') {
							$team = 'RED';
						}
						else {
							$team = 'BLUE';
						}
					}
				}
				else {
					$team = '---';
					$info = '---';
				}
				unset($first, $second);

				// Build Team Widget
				$xml = str_replace(
					array(
						'%ranks%',
						'%info%'
					),
					array(
						$team,
						$info
					),
					$this->templates['CURRENTRANKING_WIDGET']['CONTENT']
				);

				// Send Widget to all Players
				if ($xml != '') {
					// Send Manialink
					$this->sendManialink($xml, false, 0);
				}

			}
			else {
				// All other Gamemodes
				foreach ($aseco->server->rankings->ranking_list as $unsed => $data) {
					if ($player = $aseco->server->players->getPlayer($data->login)) {

						$rank = 0;
						if ( ((isset($data->time)) && ($data->time > 0)) || ((isset($data->score)) && ($data->score > 0)) ) {
							$rank = $data->rank;
						}

						$xml = str_replace(
							array(
								'%ranks%',
								'%info%'
							),
							array(
								$rank .'/'. count($aseco->server->rankings->ranking_list),
								'RANKING'
							),
							$this->templates['CURRENTRANKING_WIDGET']['CONTENT']
						);

						// Send Widget to $player->login
						if ($xml != '') {
							// Send Manialink
							$this->sendManialink($xml, $player->login, 0);
						}
					}
				}
			}
		}
		else if ($login === null) {
			// Called from onBeginMap1 and onRestartMap

			// Set for all Gamemodes
			$ranks = '0/'. count($aseco->server->players->player_list);
			$info = 'RANKING';

			// Override at TEAM
			if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
				$ranks = '---';
				$info = '---';
			}

			$xml = str_replace(
				array(
					'%ranks%',
					'%info%'
				),
				array(
					$ranks,
					$info
				),
				$this->templates['CURRENTRANKING_WIDGET']['CONTENT']
			);
			return $xml;
		}
		else {
			// Only do if it is not Gamemode 'Team'
			if ($aseco->server->gameinfo->mode != Gameinfo::TEAM) {
				// Called from onPlayerConnect
				foreach ($aseco->server->rankings->ranking_list as $unsed => $data) {
					if ($data->login == $login) {

						$rank = 0;
						if ( ($data->time > 0) || ($data->score > 0) ) {
							$rank = $data->rank;
						}

						$xml = str_replace(
							array(
								'%ranks%',
								'%info%'
							),
							array(
								$rank .'/'. count($aseco->server->rankings->ranking_list),
								'RANKING'
							),
							$this->templates['CURRENTRANKING_WIDGET']['CONTENT']
						);
						return $xml;
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

	public function buildPlayerSpectatorWidget () {
		global $aseco;

		$xml = str_replace(
			array(
				'%max_players%',
				'%max_spectators%'
			),
			array(
				$aseco->server->options['CurrentMaxPlayers'],
				$aseco->server->options['CurrentMaxSpectators']
			),
			$this->templates['PLAYERSPECTATOR_WIDGET']['CONTENT']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLadderLimitWidget () {
		global $aseco;

		$xml = str_replace(
			array(
				'%ladder_minimum%',
				'%ladder_maximum%'
			),
			array(
				substr(($aseco->server->ladder_limit_min / 1000), 0, 3),
				substr(($aseco->server->ladder_limit_max / 1000), 0, 3)
			),
			$this->templates['LADDERLIMIT_WIDGET']['CONTENT']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildGamemodeWidget ($gamemode) {
		global $aseco;

		$limits = false;
		switch ($gamemode) {
			case Gameinfo::ROUNDS:
				// Rounds
				$limits = $aseco->server->gameinfo->rounds['PointsLimit'] .' pts.';
				break;

			case Gameinfo::TIMEATTACK:
				// TimeAttack
				$limits = $aseco->formatTime($aseco->server->gameinfo->time_attack['TimeLimit'] * 1000, false, 2);
				break;

			case Gameinfo::TEAM:
				// Team
				$limits = $aseco->server->gameinfo->team['PointsLimit'] .' pts.';
				break;

			case Gameinfo::LAPS:
				// Laps
				if ($aseco->server->gameinfo->laps['TimeLimit'] > 0) {
					$limits = $aseco->formatTime($aseco->server->gameinfo->laps['TimeLimit'] * 1000, false, 2) .' min.';
				}
				else {
					$limits = $aseco->server->gameinfo->laps['ForceLapsNb'] .' laps';
				}
				break;

			case Gameinfo::CUP:
				// Cup
				$limits = $aseco->server->gameinfo->cup['PointsLimit'] .' pts.';
				break;

			case Gameinfo::STUNTS:
				// Stunts
				// Do nothing
				break;

			default:
				// Do nothing
				break;
		}

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%gamemode%'
			),
			array(
				'Icons128x32_1',
				$this->config['Gamemodes'][$gamemode]['icon'],
				$this->config['Gamemodes'][$gamemode]['name']
			),
			$this->templates['CURRENT_GAMEMODE']['HEADER']
		);

		if ($limits != false) {
			$xml .= str_replace(
				array(
					'%posx%',
					'%posy%',
					'%limits%'
				),
				array(
					$this->config['GAMEMODE_WIDGET'][0]['POS_X'][0],
					$this->config['GAMEMODE_WIDGET'][0]['POS_Y'][0],
					$limits
				),
				$this->templates['CURRENT_GAMEMODE']['LIMITS']
			);
		}

		$xml .= $this->templates['CURRENT_GAMEMODE']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildNextEnvironmentWidgetForScore () {

		$env = '';
		if ($this->cache['Map']['Next']['environment'] == 'Canyon') {
			$env = '<quad posn="1.61 -0.7 0.06" sizen="8 4" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_CANYON'][0] .'"/>';
		}
		else if ($this->cache['Map']['Next']['environment'] == 'Stadium') {
			$env = '<quad posn="1.61 -0.7 0.06" sizen="8 4" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_STADIUM'][0] .'"/>';
		}
		else if ($this->cache['Map']['Next']['environment'] == 'Valley') {
			$env = '<quad posn="1.61 -0.7 0.06" sizen="8 4" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_VALLEY'][0] .'"/>';
		}

		$xml = str_replace(
			'%icon%',
			$env,
			$this->templates['NEXT_ENVIRONMENT']['CONTENT']
		);
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildNextGamemodeWidgetForScore () {
		global $aseco;

		if ($aseco->changing_to_gamemode !== false) {
			// Set coming Gamemode
			$gamemode = $aseco->server->gameinfo->getGamemodeId($aseco->changing_to_gamemode);
		}
		else {
			// Current Gamemode is the same as next Gamemode
			$gamemode = $aseco->server->gameinfo->mode;
		}
		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%'
			),
			array(
				'Icons128x32_1',
				$this->config['Gamemodes'][$gamemode]['icon']
			),
			$this->templates['NEXT_GAMEMODE']['CONTENT']
		);
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildVisitorsWidget () {

		// Build the VisitorsWidget
		$xml = str_replace(
			array(
				'%visitorcount%'
			),
			array(
				$this->scores['Visitors']
			),
			$this->templates['VISITORS_WIDGET']['CONTENT']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildImagePreload ($part = 1) {

//		// Free the display from the preloaded images
//		if ($part == 5) {
//			$xml  = '<manialink id="ImagePreloadBox1" name="ImagePreloadBox1"></manialink>';
//			$xml .= '<manialink id="ImagePreloadBox2" name="ImagePreloadBox2"></manialink>';
//			$xml .= '<manialink id="ImagePreloadBox3" name="ImagePreloadBox3"></manialink>';
//			$xml .= '<manialink id="ImagePreloadBox4" name="ImagePreloadBox4"></manialink>';
//			$xml .= '<manialink id="ImagePreloadBox5" name="ImagePreloadBox5"></manialink>';
//			return $xml;
//		}

		$xml  = '<manialink id="ImagePreloadBox'. $part .'" name="ImagePreloadBox'. $part .'">';
		$xml .= '<frame posn="-120 -120 0">';		// Place outside visibility

		if ($part == 1) {
//			$xml .= '<quad posn="0 0 0" sizen="3.5 3.5" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_LEFT'][0] .'"/>';	// Loaded in Widgets, no need to preload
//			$xml .= '<quad posn="0 0 0" sizen="3.5 3.5" image="'. $this->config['IMAGES'][0]['WIDGET_CLOSE_LEFT'][0] .'"/>';	// Loaded in Widgets, no need to preload
//			$xml .= '<quad posn="0 0 0" sizen="3.5 3.5" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_RIGHT'][0] .'"/>';	// Loaded in Widgets, no need to preload
//			$xml .= '<quad posn="0 0 0" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';	// Loaded in Widgets, no need to preload

			// Advertiser at Score
			$xml .= '<quad posn="0 0 0" sizen="3.87 4.03" image="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-normal.png" imagefocus="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-focus.png"/>';

			// Progress Bar
			$xml .= '<quad posn="0 0 0" sizen="22 22" halign="center" valign="center" image="'. $this->config['IMAGES'][0]['PROGRESS_INDICATOR'][0] .'"/>';

			$xml .= '<quad posn="0 0 0" sizen="21.25 16.09" image="'. $this->config['IMAGES'][0]['NO_SCREENSHOT'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_NORMAL'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_FOCUS'][0] .'"/>';
		}
		else if ($part == 2) {
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_MINUS_NORMAL'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_MINUS_FOCUS'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="4 4" image="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="7.2 4.344" image="'. $this->config['IMAGES'][0]['WORLDMAP'][0] .'"/>';
//			$xml .= '<quad posn="0 0 0" sizen="11 5.5" image="'. $this->config['IMAGES'][0]['MX_LOGO_NORMAL'][0] .'"/>';	// Loaded in Widget, no need to preload
//			$xml .= '<quad posn="0 0 0" sizen="11 5.5" image="'. $this->config['IMAGES'][0]['MX_LOGO_FOCUS'][0] .'"/>';	// Loaded in Widget, no need to preload
		}
		else if ($part == 3) {
			// <environment>
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_CANYON'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_CANYON'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_STADIUM'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_STADIUM'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_VALLEY'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_VALLEY'][0] .'"/>';
		}
		else if ($part == 4) {
			// <mood>
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNRISE'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['SUNRISE'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['DAY'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['DAY'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNSET'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['SUNSET'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['NIGHT'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['NIGHT'][0] .'"/>';
		}
		else if ($part == 5) {
			// <continents>
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['AFRICA'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['ASIA'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['EUROPE'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['MIDDLE_EAST'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['NORTH_AMERICA'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['SOUTH_AMERICA'][0] .'"/>';
			$xml .= '<quad posn="0 0 0" sizen="10.88 5.44" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['OCEANIA'][0] .'"/>';
		}

		$xml .= '</frame>';
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildManiaExchangeWidget () {
		global $aseco;

		$xml = '';
		if ( isset($aseco->server->maps->current->mx->id) ) {
			if ( (isset($aseco->server->maps->current->mx->recordlist)) && (count($aseco->server->maps->current->mx->recordlist) > 0) ) {
				if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
					$score = $this->formatNumber($aseco->server->maps->current->mx->recordlist[0]['replaytime'], 0);
				}
				else {
					$score = $aseco->formatTime($aseco->server->maps->current->mx->recordlist[0]['replaytime']);
				}
			}
			else {
				$score = 'NO';
			}

			// Build the ManiaExchangeWidget with ActionId
			$xml = $this->templates['MANIA_EXCHANGE']['HEADER'];
			if ($this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_DEFAULT'][0] != '') {
				$xml .= '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_FOCUS'][0] .'" scriptevents="1" id="ButtonManiaExchangeWidget"/>';
			}
			else {
				$xml .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'" scriptevents="1" id="ButtonManiaExchangeWidget"/>';
			}
			$xml .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
			$xml .= str_replace(
				array(
					'%offline_record%',
					'%text%'
				),
				array(
					$score,
					'WORLD-RECORD'
				),
				$this->templates['MANIA_EXCHANGE']['FOOTER']
			);
		}
		else {
			// Build the ManiaExchangeWidget WITHOUT ActionId
			$xml = $this->templates['MANIA_EXCHANGE']['HEADER'];
			if ($this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_DEFAULT'][0] != '') {
				$xml .= '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_FOCUS'][0] .'"/>';
			}
			else {
				$xml .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
			}
			$xml .= str_replace(
				array(
					'%offline_record%',
					'%text%'
				),
				array(
					'NOT AT',
					'MANIA-EXCHANGE'
				),
				$this->templates['MANIA_EXCHANGE']['FOOTER']
			);
		}
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMapcountWidget () {

		// Build the MapcountWidget
		$xml = str_replace(
			array(
				'%mapcount%'
			),
			array(
				$this->formatNumber(count($this->cache['MapList']), 0)
			),
			$this->templates['MAPCOUNT']['CONTENT']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMusicWidget () {
		global $aseco;

		// Set the right Icon and Title position
		$position = (($this->config['MUSIC_WIDGET'][0]['POS_X'][0] < 0) ? 'right' : 'left');

		if ($position == 'right') {
			$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] - 15.5));
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$imagex	= $this->config['Positions'][$position]['image_open']['x'];
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		// Build the MusicWidget
		$xml = str_replace(
			array(
				'%manialinkid%',
				'%actionid%',
				'%posx%',
				'%posy%',
				'%image_open_pos_x%',
				'%image_open_pos_y%',
				'%image_open%',
				'%posx_icon%',
				'%posy_icon%',
				'%posx_title%',
				'%posy_title%',
				'%halign%',
				'%backgroundwidth%',
				'%borderwidth%',
				'%widgetwidth%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'MusicWidget',
				'showMusiclistWindow',
				$this->config['MUSIC_WIDGET'][0]['POS_X'][0],
				$this->config['MUSIC_WIDGET'][0]['POS_Y'][0],
				$imagex,
				-5.33,
				$this->config['Positions'][$position]['image_open']['image'],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				$this->config['Positions'][$position]['title']['halign'],
				($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] - 0.2),
				($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] + 0.4),
				$this->config['MUSIC_WIDGET'][0]['WIDTH'][0],
				($this->config['MUSIC_WIDGET'][0]['WIDTH'][0] - 0.8),
				$this->config['MUSIC_WIDGET'][0]['TITLE'][0]
			),
			$this->templates['MUSIC_WIDGET']['HEADER']
		);

		$xml .= '<label posn="1 -2.7 0.04" sizen="13.55 2" scale="1" text="'. $this->config['CurrentMusicInfos']['Title'] .'"/>';
		$xml .= '<label posn="1 -4.5 0.04" sizen="14.85 2" scale="0.9" text="by '. $this->config['CurrentMusicInfos']['Artist'] .'"/>';
		if ($this->config['MUSIC_WIDGET'][0]['ADVERTISE'][0] == true) {
			$xml .= '<quad posn="9.5 -6.2 0.05" sizen="5.2 1.7" url="http://www.amazon.com/gp/search?ie=UTF8&amp;keywords='. urlencode($aseco->stripColors($this->config['CurrentMusicInfos']['Artist'], true)) .'&amp;tag=undefde-20&amp;index=digital-music&amp;linkCode=ur2&amp;camp=1789&amp;creative=9325" image="http://static.undef.name/ingame/records-eyepiece/logo-amazon-normal.png" imagefocus="http://static.undef.name/ingame/records-eyepiece/logo-amazon-focus.png"/>';
		}
		$xml .= $this->templates['MUSIC_WIDGET']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildClockWidget () {
		global $aseco;

		// Transform lowercase GameState 'race' (Server::RACE) or 'score' (Server::SCORE) to UPPERCASE
		$gamestate = strtoupper($aseco->server->gamestate);

		// Build the ClockWidget
		$xml = str_replace(
			array(
				'%background_style%',
				'%background_substyle%',
				'%posx%',
				'%posy%',
				'%widgetscale%'
			),
			array(
				$this->config['CLOCK_WIDGET'][0][$gamestate][0]['BACKGROUND_STYLE'][0],
				$this->config['CLOCK_WIDGET'][0][$gamestate][0]['BACKGROUND_SUBSTYLE'][0],
				$this->config['CLOCK_WIDGET'][0][$gamestate][0]['POS_X'][0],
				$this->config['CLOCK_WIDGET'][0][$gamestate][0]['POS_Y'][0],
				$this->config['CLOCK_WIDGET'][0][$gamestate][0]['SCALE'][0]
			),
			$this->templates['CLOCK_WIDGET']['CONTENT']
		);
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPlacementWidget ($gamestate = 'score') {
		global $aseco;

		// Init
		$xml = '';

		// Preset the search pattern
		$searchpattern = '(%MAP_MX_PREFIX%|%MAP_MX_ID%|%MAP_MX_PAGEURL%|%MAP_UID%|%MAP_NAME%)';

		$mx = false;
		if ($this->config['PlacementPlaceholders']['MAP_MX_PREFIX'] != false) {
			$mx = true;
		}

		if ($gamestate === 'always') {

			// Build the Widgets at 'always'
			$xml .= '<manialink id="PlacementWidgetAlways" name="PlacementWidgetAlways">';
			$xml .= '<frame posn="0 0 0">';
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ($placement['DISPLAY'][0] == 'ALWAYS') {

					// First: Remove all Placeholder, that are not supported here,
					// because this <placement> are never refreshed!
					$xml = str_replace(
						array('%MAP_MX_PREFIX%','%MAP_MX_ID%','%MAP_UID%','%MAP_NAME%','%MAP_MX_PAGEURL%'),
						array('','','','',''),
						$xml
					);

					// Second: Build the <placement>
					$xml .= $this->getPlacementEntry($placement);
				}
			}
			$xml .= '</frame>';
			$xml .= '</manialink>';
		}

		if ($gamestate === Server::RACE) {

			// Build the Widgets at 'race'
			$xml .= '<manialink id="PlacementWidgetRace" name="PlacementWidgetRace">';
			$xml .= '<frame posn="0 0 0">';
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ($placement['DISPLAY'][0] == 'RACE') {

					// First: Remove all Placeholder, that are not supported here,
					// because this <placement> are never refreshed after map change!
					$xml = str_replace(
						array('%MAP_MX_PREFIX%','%MAP_MX_ID%','%MAP_UID%','%MAP_NAME%','%MAP_MX_PAGEURL%'),
						array('','','','',''),
						$xml
					);

					// Second: Build the <placement>
					$xml .= $this->getPlacementEntry($placement);
				}
			}
			$xml .= '</frame>';
			$xml .= '</manialink>';

			// Hide 'score' PlacementWidgets
			$xml .= '<manialink id="PlacementWidgetScore" name="PlacementWidgetScore"></manialink>';
		}

		if ( ($gamestate === Gameinfo::ROUNDS) || ($gamestate === Gameinfo::TIMEATTACK) || ($gamestate === Gameinfo::TEAM) || ($gamestate === Gameinfo::LAPS) || ($gamestate === Gameinfo::CUP) || ($gamestate === Gameinfo::STUNTS) ) {

			// Build the Widgets at 'gamemode'
			$xml .= '<manialink id="PlacementWidgetGamemode" name="PlacementWidgetGamemode">';
			$xml .= '<frame posn="0 0 0">';
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ($placement['DISPLAY'][0] === $gamestate) {

					// First: Remove all Placeholder, that are not supported here,
					// because this <placement> are never refreshed after map change!
					$xml = str_replace(
						array('%MAP_MX_PREFIX%','%MAP_MX_ID%','%MAP_UID%','%MAP_NAME%','%MAP_MX_PAGEURL%'),
						array('','','','',''),
						$xml
					);

					// Second: Build the <placement>
					$xml .= $this->getPlacementEntry($placement);
				}
			}
			$xml .= '</frame>';
			$xml .= '</manialink>';

			// Hide 'score' PlacementWidgets
			$xml .= '<manialink id="PlacementWidgetScore" name="PlacementWidgetScore"></manialink>';
		}

		if ($gamestate === Server::SCORE) {

			// Build the Widgets at 'score'
			$xml .= '<manialink id="PlacementWidgetScore" name="PlacementWidgetScore">';
			$xml .= '<frame posn="0 0 0">';
			foreach ($this->config['PLACEMENT_WIDGET'][0]['PLACEMENT'] as $placement) {
				if ($placement['DISPLAY'][0] == 'SCORE') {
					if ($mx == false) {
						// Try to find Placeholders and skip
						if ( isset($placement['URL'][0]) ) {
							if (preg_match($searchpattern, $placement['URL'][0]) > 0) {
								continue;
							}
						}
						if ( isset($placement['MANIALINK'][0]) ) {
							if (preg_match($searchpattern, $placement['MANIALINK'][0]) > 0) {
								continue;
							}
						}
					}
					$xml .= $this->getPlacementEntry($placement);
				}
			}
			$xml .= '</frame>';
			$xml .= '</manialink>';

			// Hide 'race' and 'gamemode' PlacementWidgets
			$xml .= '<manialink id="PlacementWidgetRace" name="PlacementWidgetRace"></manialink>';
			$xml .= '<manialink id="PlacementWidgetGamemode" name="PlacementWidgetGamemode"></manialink>';
		}




		// Replace the supported Placeholder, if already loaded
		// (at startup the event onPlayerConnect is always to early, the Map is not loaded yet)
		if ($mx == true) {
			$xml = str_replace(
				array(
					'%MAP_MX_PREFIX%',
					'%MAP_MX_ID%',
					'%MAP_MX_PAGEURL%'
				),
				array(
					$this->config['PlacementPlaceholders']['MAP_MX_PREFIX'],
					$this->config['PlacementPlaceholders']['MAP_MX_ID'],
					$this->config['PlacementPlaceholders']['MAP_MX_PAGEURL']
				),
				$xml
			);
		}
		if ($this->config['PlacementPlaceholders']['MAP_UID'] != false) {
			$xml = str_replace(
				array(
					'%MAP_UID%',
					'%MAP_NAME%'
				),
				array(
					$this->config['PlacementPlaceholders']['MAP_UID'],
					$this->config['PlacementPlaceholders']['MAP_NAME']
				),
				$xml
			);
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlacementEntry ($placement) {

		// Check for includes and load/return only this content
		if ( isset($placement['INCLUDE'][0]) ) {
			$xml = file_get_contents($placement['INCLUDE'][0]);
			return str_replace(
				array(
					"\t",		// tab
					"\r",		// carriage return
					"\n",		// new line
					"\0",		// NUL-byte
					"\x0B",		// vertical tab
				),
				array(
					'',
					'',
					'',
					'',
					'',
				),
				trim($xml)
			);
		}


		$xml = '';

		// Build the background for the Widget
		if ( isset($placement['BACKGROUND_STYLE'][0]) ) {
			$xml .= '<quad posn="'. $placement['POS_X'][0] .' '. $placement['POS_Y'][0] .' '. ($placement['LAYER'][0] + 0.001) .'" sizen="'. $placement['WIDTH'][0] .' '. $placement['HEIGHT'][0] .'"';

			if ( (isset($placement['BACKGROUND_STYLE'][0])) && (isset($placement['BACKGROUND_SUBSTYLE'][0])) ) {
				$xml .= ' style="'. $placement['BACKGROUND_STYLE'][0] .'" substyle="'. $placement['BACKGROUND_SUBSTYLE'][0] .'"';
			}
			if ( isset($placement['URL'][0]) ) {
				$xml .= ' url="'. $placement['URL'][0] .'"';
			}
			else if ( isset($placement['MANIALINK'][0]) ) {
				$xml .= ' manialink="'. $placement['MANIALINK'][0] .'"';
			}
			else if ( isset($placement['ACTION_ID'][0]) ) {
				$xml .= ' action="'. $placement['ACTION_ID'][0] .'"';
			}
			else if ( isset($placement['CHAT_MLID'][0]) ) {
				$xml .= ' action="chatCommand'. sprintf("%02d", $placement['CHAT_MLID'][0]) .'"';
			}

			$xml .= '/>';
		}

		// Build the image quad for the Widget if required
		if ( isset($placement['IMAGE'][0]) ) {
			$xml .= '<quad posn="'. $placement['POS_X'][0] .' '. $placement['POS_Y'][0] .' '. ($placement['LAYER'][0] + 0.002) .'" sizen="'. $placement['WIDTH'][0] .' '. $placement['HEIGHT'][0] .'" image="'. $placement['IMAGE'][0] .'"';

			if ( isset($placement['IMAGEFOCUS'][0]) ) {
				$xml .= ' imagefocus="'. $placement['IMAGEFOCUS'][0] .'"';
			}
			if ( isset($placement['HALIGN'][0]) ) {
				$xml .= ' halign="'. $placement['HALIGN'][0] .'"';
			}
			if ( isset($placement['VALIGN'][0]) ) {
				$xml .= ' valign="'. $placement['VALIGN'][0] .'"';
			}
			if ( isset($placement['OPACITY'][0]) ) {
				$xml .= ' opacity="'. $placement['OPACITY'][0] .'"';
			}
			if ( isset($placement['COLORIZE'][0]) ) {
				$xml .= ' colorize="'. $placement['COLORIZE'][0] .'"';
			}
			if ( isset($placement['MODULATECOLOR'][0]) ) {
				$xml .= ' modulatecolor="'. $placement['MODULATECOLOR'][0] .'"';
			}
			if ( isset($placement['URL'][0]) ) {
				$xml .= ' url="'. $placement['URL'][0] .'"';
			}
			else if ( isset($placement['MANIALINK'][0]) ) {
				$xml .= ' manialink="'. $placement['MANIALINK'][0] .'"';
			}
			else if ( isset($placement['ACTION_ID'][0]) ) {
				$xml .= ' action="'. $placement['ACTION_ID'][0] .'"';
			}
			else if ( isset($placement['CHAT_MLID'][0]) ) {
				$xml .= ' action="chatCommand'. sprintf("%02d", $placement['CHAT_MLID'][0]) .'"';
			}

			$xml .= '/>';
		}

		// Build the icon quad for the Widget if required
		if ( isset($placement['ICON_STYLE'][0]) ) {
			$xml .= '<quad posn="'. $placement['POS_X'][0] .' '. $placement['POS_Y'][0] .' '. ($placement['LAYER'][0] + 0.003) .'" sizen="'. $placement['WIDTH'][0] .' '. $placement['HEIGHT'][0] .'" style="'. $placement['ICON_STYLE'][0] .'" substyle="'. $placement['ICON_SUBSTYLE'][0] .'"';

			if ( isset($placement['HALIGN'][0]) ) {
				$xml .= ' halign="'. $placement['HALIGN'][0] .'"';
			}
			if ( isset($placement['VALIGN'][0]) ) {
				$xml .= ' valign="'. $placement['VALIGN'][0] .'"';
			}
			if ( isset($placement['URL'][0]) ) {
				$xml .= ' url="'. $placement['URL'][0] .'"';
			}
			else if ( isset($placement['MANIALINK'][0]) ) {
				$xml .= ' manialink="'. $placement['MANIALINK'][0] .'"';
			}
			else if ( isset($placement['ACTION_ID'][0]) ) {
				$xml .= ' action="'. $placement['ACTION_ID'][0] .'"';
			}
			else if ( isset($placement['CHAT_MLID'][0]) ) {
				$xml .= ' action="chatCommand'. sprintf("%02d", $placement['CHAT_MLID'][0]) .'"';
			}

			$xml .= '/>';
		}

		// Build the text label for the Widget if required
		if ( isset($placement['TEXT'][0]) ) {
			$xml .= '<label posn="'. $placement['POS_X'][0] .' '. $placement['POS_Y'][0] .' '. ($placement['LAYER'][0] + 0.004) .'" sizen="'. $placement['WIDTH'][0] .' '. $placement['HEIGHT'][0] .'"';

			if ( isset($placement['HALIGN'][0]) ) {
				$xml .= ' halign="'. $placement['HALIGN'][0] .'"';
			}
			if ( isset($placement['VALIGN'][0]) ) {
				$xml .= ' valign="'. $placement['VALIGN'][0] .'"';
			}
			if ( isset($placement['TEXTSIZE'][0]) ) {
				$xml .= ' textsize="'. $placement['TEXTSIZE'][0] .'"';
			}
			if ( isset($placement['TEXTSCALE'][0]) ) {
				$xml .= ' scale="'. $placement['TEXTSCALE'][0] .'"';
			}
			if ( isset($placement['OPACITY'][0]) ) {
				$xml .= ' opacity="'. $placement['OPACITY'][0] .'"';
			}

			$xml .= ' text="'. $placement['TEXT'][0] .'"/>';
		}

		return $xml;
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function showRecordWidgets ($force_display = false) {
		global $aseco;

		// Bail out if Scoretable is displayed
		if ($aseco->server->gamestate == Server::SCORE) {
			return;
		}

		// Bail out if there are no Players
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		$widgets = '';
		if ( ($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) && ($this->config['NICEMODE'][0]['ALLOW'][0]['DEDIMANIA_RECORDS'][0] == true) ) {
			if ( ($this->config['States']['DedimaniaRecords']['UpdateDisplay'] == true) || ($force_display == true) ) {
				$widgets .= (($this->cache['DedimaniaRecords']['NiceMode'] != false) ? $this->cache['DedimaniaRecords']['NiceMode'] : '');
			}
		}
		if ( ($this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) && ($this->config['NICEMODE'][0]['ALLOW'][0]['LOCAL_RECORDS'][0] == true) ) {
			if ( ($this->config['States']['LocalRecords']['UpdateDisplay'] == true) || ($force_display == true) ) {
				$widgets .= (($this->cache['LocalRecords']['NiceMode'] != false) ? $this->cache['LocalRecords']['NiceMode'] : '');
			}
		}
		if ( ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) && ($this->config['NICEMODE'][0]['ALLOW'][0]['LIVE_RANKINGS'][0] == true) ) {
			if ( ($this->config['States']['LiveRankings']['UpdateDisplay'] == true) || ($force_display == true) ) {
				$widgets .= (($this->cache['LiveRankings']['NiceMode'] != false) ? $this->cache['LiveRankings']['NiceMode'] : '');
			}
		}

		return $widgets;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $target = the same as the normal $player object, but only this Player gets the requested Widgets
	// $force  = an array where the required Widgets are identified
	public function buildRecordWidgets ($target = false, $force = false) {
		global $aseco;

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		$buildDedimaniaRecordsWidget = false;
		$buildLocalRecordsWidget = false;
		$buildLiveRankingsWidget = false;
		if ( ($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['DEDIMANIA_RECORDS'][0] == true) && ($this->config['States']['NiceMode'] == true)) ) {
			// Refresh the Widget only if it needs an update
			if ($this->config['States']['DedimaniaRecords']['NeedUpdate'] == true) {

				// Get current Records
				$this->getDedimaniaRecords();
				$this->config['States']['DedimaniaRecords']['NeedUpdate'] = false;

				// Say yes to build the Widget
				$buildDedimaniaRecordsWidget = true;
			}
			if ($this->config['States']['DedimaniaRecords']['UpdateDisplay'] == true) {

				// Say yes to build the Widget
				$buildDedimaniaRecordsWidget = true;
			}
		}
		if ( ($this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['LOCAL_RECORDS'][0] == true) && ($this->config['States']['NiceMode'] == true)) ) {
			// Refresh the Widget only if it needs an update
			if ($this->config['States']['LocalRecords']['NeedUpdate'] == true) {

				// Get current Records
				$this->getLocalRecords($gamemode);

				// Only set to false if records are loaded and displayed,
				// but only if there are Records. If nobody reached a Record, do not try again.
				if ($this->config['States']['LocalRecords']['NoRecordsFound'] == false) {
					$this->config['States']['LocalRecords']['NeedUpdate'] = false;
				}

				// Say yes to build the Widget
				$buildLocalRecordsWidget = true;
			}
			if ($this->config['States']['LocalRecords']['UpdateDisplay'] == true) {

				// Say yes to build the Widget
				$buildLocalRecordsWidget = true;
			}
		}
		if ( ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) || (($this->config['NICEMODE'][0]['ALLOW'][0]['LIVE_RANKINGS'][0] == true) && ($this->config['States']['NiceMode'] == true)) ) {
			// Refresh the Widget only if it needs an update
			if ($this->config['States']['LiveRankings']['NeedUpdate'] == true) {

				// Get current Records
				$this->getLiveRankings($gamemode);

				// Only set to false if records are loaded and displayed,
				// but only if there are Players finished the map. If nobody finished this map, do not try again.
				if ($this->config['States']['LiveRankings']['NoRecordsFound'] == false) {
					$this->config['States']['LiveRankings']['NeedUpdate'] = false;
				}

				// Say yes to build the Widget
				$buildLiveRankingsWidget = true;
			}
			if ($this->config['States']['LiveRankings']['UpdateDisplay'] == true) {

				// Say yes to build the Widget
				$buildLiveRankingsWidget = true;
			}
		}



		if ($this->config['States']['NiceMode'] == false) {

			// Clean mem (from possible reverted NiceMode)
			$this->cache['DedimaniaRecords']['NiceMode']	= false;
			$this->cache['LocalRecords']['NiceMode']	= false;
			$this->cache['LiveRankings']['NiceMode']	= false;

			// If we switched to score, bail out
			if ($aseco->server->gamestate == Server::SCORE) {
				return;
			}

			// Build the Widgets for all connected Players or given Player ($target same as $player)
			if ($target != false) {
				$player_list = array($target);
			}
			else {
				$player_list = $aseco->server->players->player_list;
			}
			foreach ($player_list as $player) {

				// Did the Player has the Records Widget set to hidden?
				if ($player->data['PluginRecordsEyepiece']['Prefs']['WidgetState'] == false) {
					continue;
				}

				$widgets = '';
				if ( (($buildDedimaniaRecordsWidget == true) || ($force['DedimaniaRecords'] == true)) && ($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
					$widgets .= $this->buildRecordWidgetContent(
						$gamemode,
						$player,
						$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0],
						'DEDIMANIA_RECORDS'
					);
				}
				if ( (($buildLocalRecordsWidget == true) || ($force['LocalRecords'] == true)) && ($this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
					$widgets .= $this->buildRecordWidgetContent(
						$gamemode,
						$player,
						$this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0],
						'LOCAL_RECORDS'
					);
				}
				if ( (($buildLiveRankingsWidget == true) || ($force['LiveRankings'] == true)) && ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) ) {
					$widgets .= $this->buildLiveRankingsWidget($player->login, $player->data['PluginRecordsEyepiece']['Prefs']['WidgetEmptyEntry'], $gamemode, $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0]);
				}

				if ($widgets != '') {
					// Send Manialink to given Player
					$this->sendManialink($widgets, $player->login, 0);
				}
			}
			unset($player);
		}
		else {

			// Build the RecordWidgets for all connected Players and ignore the Player specific highlites
			if ($buildDedimaniaRecordsWidget == true) {
				$this->cache['DedimaniaRecords']['NiceMode'] = $this->buildRecordWidgetContent(
					$gamemode,
					false,
					$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0],
					'DEDIMANIA_RECORDS'
				);
				$this->config['States']['DedimaniaRecords']['UpdateDisplay'] = true;
			}
			if ($buildLocalRecordsWidget == true) {
				$this->cache['LocalRecords']['NiceMode'] = $this->buildRecordWidgetContent(
					$gamemode,
					false,
					$this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0],
					'LOCAL_RECORDS'
				);
				$this->config['States']['LocalRecords']['UpdateDisplay'] = true;
			}
			if ($buildLiveRankingsWidget == true) {
				$this->cache['LiveRankings']['NiceMode'] = $this->buildLiveRankingsWidget(false, false, $gamemode, $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0]);
				$this->config['States']['LiveRankings']['UpdateDisplay'] = true;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendManialink ($widgets, $login = false, $timeout = 0) {
		global $aseco;

		if ($login != false) {
			// Send to given Player
			$aseco->sendManialink($widgets, $login, ($timeout * 1000), false);
		}
		else {
			// Send to all connected Players
			$aseco->sendManialink($widgets, false, ($timeout * 1000), false);
		}
	}

	///*
	//#///////////////////////////////////////////////////////////////////////#
	//#									#
	//#///////////////////////////////////////////////////////////////////////#
	//*/
	//
	//function convertPosnToVersion1 ($matches) {
	//	return 'posn="'. ($matches[1] * 2.5) .' '. ($matches[2] * 1.875) .' '. $matches[3] .'"';
	//}
	//
	///*
	//#///////////////////////////////////////////////////////////////////////#
	//#									#
	//#///////////////////////////////////////////////////////////////////////#
	//*/
	//
	//function convertSizenToVersion1 ($matches) {
	//	return 'sizen="'. ($matches[1] * 2.5) .' '. ($matches[2] * 1.875) .'"';
	//}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendProgressIndicator ($login) {

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'United',
				'Loading... please wait.',
				''
			),
			$this->templates['WINDOW']['HEADER']
		);
		$xml .= $this->templates['PROGRESS_INDICATOR']['CONTENT'];
		$xml .= $this->templates['WINDOW']['FOOTER'];

		// Send the progress indicator
		$this->sendManialink($xml, $login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function closeScoretableLists () {

		$ids = array(
			'MapWidget',
			'ClockWidget',
			'AddToFavoriteWidget',
			'DedimaniaRecordsWidget',
			'LocalRecordsWidget',
			'TopRankingsWidgetAtScore',
			'TopWinnersWidgetAtScore',
			'MostRecordsWidgetAtScore',
			'MostFinishedWidgetAtScore',
			'TopPlaytimeWidgetAtScore',
			'TopDonatorsWidgetAtScore',
			'TopNationsWidgetAtScore',
			'TopMapsWidgetAtScore',
			'TopVotersWidgetAtScore',
			'TopBetwinsWidgetAtScore',
			'TopRoundscoreWidgetAtScore',
			'RecordsEyepieceAdvertiserWidget',
			'TopAverageTimesWidgetAtScore',
			'NextGamemodeWidgetAtScore',
			'NextEnvironmentWidgetAtScore',
			'WinningPayoutWidgetAtScore',
			'DonationWidgetAtScore',
			'TopWinningPayoutsWidgetAtScore',
			'TopVisitorsWidgetAtScore',
			'TopActivePlayersWidgetAtScore',
		);

		$xml = '';
		foreach ($ids as $id) {
			$xml .= '<manialink id="'. $id .'" name="'. $id .'"></manialink>';
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function closeRaceWidgets ($login = false, $all = true) {

		if ($all == false) {
			// Do NOT close:
			//  - GamemodeWidget
			//  - VisitorsWidget
			//  - MapCountWidget
			//  - ToplistWidget
			//  - RoundScoreWidget
			//  - CheckpointCountWidget
			//  - AddToFavoriteWidget
			//  - PlayerSpectatorWidget
			//  - CurrentRankingWidget
			//  - LadderLimitWidget
			//  - RecordsEyepieceAdvertiserWidget
			//  - ManiaExchangeWidget
			$ids = array(
//				'DedimaniaRecordsWidget',
//				'LocalRecordsWidget',
//				'LiveRankingsWidget',
				'MusicWidget',
			);
		}
		else {
			$ids = array(
				'GamemodeWidget',
				'VisitorsWidget',
				'MapCountWidget',
				'ToplistWidget',
				'DedimaniaRecordsWidget',
				'LocalRecordsWidget',
				'LiveRankingsWidget',
				'MusicWidget',
				'RoundScoreWidget',
				'CheckpointCountWidget',
				'RecordsEyepieceAdvertiserWidget',
				'AddToFavoriteWidget',
				'PlayerSpectatorWidget',
				'LadderLimitWidget',
				'CurrentRankingWidget',
				'ManiaExchangeWidget',
				'ClockWidget',
			);
		}

		$xml = '';
		foreach ($ids as $id) {
			$xml .= '<manialink id="'. $id .'" name="'. $id .'"></manialink>';
		}

		// Close all Windows (incl. SubWindows)
		$xml .= $this->closeAllWindows();

		if ($login != false) {
			// Send to given Player
			$this->sendManialink($xml, $login, 0);
		}
		else {
			// Return the xml
			return $xml;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function closeAllWindows () {

		$xml  = '<manialink id="MainWindow" name="MainWindow"></manialink>';
		$xml .= '<manialink id="SubWindow" name="SubWindow"></manialink>';
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function closeAllSubWindows () {
		return '<manialink id="SubWindow" name="SubWindow"></manialink>';
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildDonationWidget ($action) {
		global $aseco;

		$val = explode(',', $this->config['DONATION_WIDGET'][0]['AMOUNTS'][0]);
		if ( isset($aseco->plugins['PluginDonate']) ) {
			$aseco->plugins['PluginDonate']->donation_values = array((int)$val[0], (int)$val[1], (int)$val[2], (int)$val[3], (int)$val[4], (int)$val[5], (int)$val[6]);
			$aseco->plugins['PluginDonate']->publicappr = (int)$this->config['DONATION_WIDGET'][0]['PUBLIC_APPRECIATION_THRESHOLD'][0];
		}


		// Setup Widget
		$xml = str_replace(
			array(
				'%widgetheight%'
			),
			array(
				(6.55 + (count($val) * 1.85))
			),
			$this->templates['DONATION_WIDGET']['HEADER']
		);

		if ($action == 'DEFAULT') {
			$xml .= '<format textsize="1" textcolor="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['BUTTON_COLOR'][0] .'"/>';

			$offset = 6.75;
			$row = 0;
			foreach (range(0,9) as $i) {
				if ( isset($val[$i]) ) {
					$xml .= '<quad posn="0.2 -'. ($offset + $row) .' 0.2" sizen="4.2 1.7" action="donateAmount'. sprintf("%02d", $i) .'" style="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['BUTTON_STYLE'][0] .'" substyle="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['BUTTON_SUBSTYLE'][0] .'"/>';
					$xml .= '<label posn="2.2 -'. ($offset + $row + 0.35) .' 0.3" sizen="4 2.5" halign="center" scale="0.8" text="'. $val[$i] .'$n $mP"/>';
					$row += 1.8;
				}
			}
		}
		else {
			// Loading indicator
			$xml .= '<quad posn="2.2 -10.8 0.3" sizen="4.2 4.2" halign="center" valign="center" image="'. $this->config['IMAGES'][0]['PROGRESS_INDICATOR'][0] .'"/>';
			$xml .= '<label posn="2.2 -13.2 0.3" sizen="4 1.8" halign="center" textsize="1" scale="0.8" text="Please"/>';
			$xml .= '<label posn="2.2 -14.4 0.3" sizen="4 1.8" halign="center" textsize="1" scale="0.8" text="wait!"/>';
		}
		$xml .= $this->templates['DONATION_WIDGET']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWinningPayoutWidget () {
		global $aseco;

		// Bail out immediately at Gamemode 'Team'
		if ($aseco->server->gameinfo->mode == Gameinfo::TEAM) {
			return;
		}

		// Bail out if there are no Players
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}


		// Find out how many Planets the Players has won total
		$players_planets = 0;
		foreach ($aseco->server->players->player_list as $player) {
			$players_planets += $this->cache['PlayerWinnings'][$player->login]['FinishPayment'];
		}
		unset($player);

		// Setup Widget
		$xml = $this->templates['WINNING_PAYOUT']['HEADER'];

		// If the Server runs out of Planets, disable payout until Planets are high enough
		if ( ($aseco->server->amount_planets - $players_planets) > $this->config['WINNING_PAYOUT'][0]['MINIMUM_SERVER_PLANETS'][0]) {

			// Get the current Rankings
			$ranks = $aseco->server->rankings->getTop50();

			// Find all Player they finished the Map
			$score = array();
			$i = 0;
			foreach ($ranks as $item) {
				if ( ($item->time > 0) || ($item->score > 0) ) {

					// Get Player object
					if (!$player = $aseco->server->players->getPlayer($item->login)) {
						continue;
					}

					// Check ignore list
					$ignore = false;
					if ( ($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['OPERATOR'][0] == true) && ($aseco->isOperator($player)) ) {
						$ignore = true;
					}
					if ( ($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['ADMIN'][0] == true) && ($aseco->isAdmin($player)) ) {
						$ignore = true;
					}
					if ( ($this->config['WINNING_PAYOUT'][0]['IGNORE'][0]['MASTERADMIN'][0] == true) && ($aseco->isMasterAdmin($player)) ) {
						$ignore = true;
					}

					if ($player == false) {
						// If the Player is already disconnected, use own Cache
						$player = $this->cache['WinningPayoutPlayers'][$item->login];

						$score[$i]['rank']		= $item->rank;
						$score[$i]['id']		= $player['id'];
						$score[$i]['login']		= $player['login'];
						$score[$i]['nickname']		= $this->handleSpecialChars($player['nickname']);
						$score[$i]['ladderrank']	= $player['ladderrank'];
						$score[$i]['won']		= 0;
						$score[$i]['disconnected']	= true;
						$score[$i]['ignore']		= $ignore;
					}
					else {
						$score[$i]['rank']		= $item->rank;
						$score[$i]['id']		= $player->id;
						$score[$i]['login']		= $player->login;
						$score[$i]['nickname']		= $this->handleSpecialChars($player->nickname);
						$score[$i]['ladderrank']	= $player->ladderrank;
						$score[$i]['won']		= 0;
						$score[$i]['disconnected']	= false;
						$score[$i]['ignore']		= $ignore;
					}
				}
				$i ++;
			}

			// Did enough Players finished this Map?
			if (count($score) >= $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MINIMUM_AMOUNT'][0]) {

				// Add to the first three Player Planets, if they have an TMU account, above the <rank_limit> and connected
				foreach ($score as &$item) {
					if ($item['ladderrank'] >= $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RANK_LIMIT'][0]) {
						if ( ($this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] + $this->cache['PlayerWinnings'][$item['login']]['FinishPaid']) < $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MAXIMUM_PLANETS'][0]) {
							if ( ($item['disconnected'] == false) && ($item['ignore'] == false) ) {
								switch ($item['rank']) {
									case 1:
										$this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] += $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0];
										$item['won'] = $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0];
										break;
									case 2:
										$this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] += $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0];
										$item['won'] = $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0];
										break;
									case 3:
										$this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] += $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0];
										$item['won'] = $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0];
										break;
									default:
										break;
								}
							}
						}
					}

					if ($item['won'] > 0) {
						// Set a timestamp to activate the disable-to-win check
						$this->cache['PlayerWinnings'][$item['login']]['TimeStamp'] = time();
					}

					if ($item['rank'] >= 4) {
						// Skip now, only the first three Player can win
						break;
					}
				}
				unset($item);

				// Calculate the switch of message, from "Congratulation!" to the "Total: [N] P"
				$total_switch = $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['FIRST'][0] + $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['SECOND'][0] + $this->config['WINNING_PAYOUT'][0]['PAY_PLANETS'][0]['THIRD'][0];

				// Build the entries
				$line = 0;
				$offset = 3;
				$eventdata = array();
				foreach ($score as &$item) {
					switch ($item['rank']) {
						case 1:
							$xml .= '<quad posn="0.85 -'. ($this->config['LineHeight'] * $line + $offset - 0.15) .' 0.002" sizen="1.7 1.6" style="Icons64x64_1" substyle="First"/>';
							$eventdata[] = array(
								'place'		=> 1,
								'login'		=> $item['login'],
								'amount'	=> $item['won']
							);
							break;
						case 2:
							$xml .= '<quad posn="0.85 -'. ($this->config['LineHeight'] * $line + $offset - 0.15) .' 0.002" sizen="1.7 1.6" style="Icons64x64_1" substyle="Second"/>';
							$eventdata[] = array(
								'place'		=> 2,
								'login'		=> $item['login'],
								'amount'	=> $item['won']
							);
							break;
						case 3:
							$xml .= '<quad posn="0.87 -'. ($this->config['LineHeight'] * $line + $offset - 0.15) .' 0.002" sizen="1.7 1.6" style="Icons64x64_1" substyle="Third"/>';
							$eventdata[] = array(
								'place'		=> 3,
								'login'		=> $item['login'],
								'amount'	=> $item['won']
							);
							break;
					}

					// Build the Won and the Info column
					if ($item['disconnected'] == true) {
						// Player already disconnected
						$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.95 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="0 P"/>';
						$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['DISCONNECTED'][0] .'" text="Disconnected!"/>';
					}
					else if ($item['ignore'] == true) {
						// Player is in <winning_payout><ignore>
						$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.95 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="0 P"/>';
						$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['DISCONNECTED'][0] .'" text="No Payout!"/>';
					}
					else if ($item['ladderrank'] < $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['RANK_LIMIT'][0]) {
						// <rank_limit> reached
						$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.95 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="0 P"/>';
						$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['RANK_LIMIT'][0] .'" text="Over Rank-Limit!"/>';
					}
					else if ( ( ($this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] + $this->cache['PlayerWinnings'][$item['login']]['FinishPaid']) >= $this->config['WINNING_PAYOUT'][0]['PLAYERS'][0]['MAXIMUM_PLANETS'][0]) && ($item['won'] == 0) ) {
						// <maximum_planets> reached
						$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.95 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="0 P"/>';
						$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['RANK_LIMIT'][0] .'" text="Over Payout-Limit!"/>';
					}
					else {
						$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.95 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="+'. $item['won'] .' P"/>';

						// Display "Congratulation!" or "Total [N] P"
						if ($this->cache['PlayerWinnings'][$item['login']]['FinishPayment'] > $total_switch) {
							$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['WON'][0] .'" text="'. $this->formatNumber((int)$this->cache['PlayerWinnings'][$item['login']]['FinishPayment'], 0) .' P total"/>';
						}
						else {
							$xml .= '<label posn="24.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['WON'][0] .'" text="Congratulation!"/>';
						}
					}
					$xml .= '<label posn="6.5 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="11.4 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item['nickname'] .'"/>';

					$line ++;
					if ($line >= 3) {
						break;
					}
				}
				unset($item);


				// Release Winning Planets event
				$aseco->releaseEvent('onPlayerWinPlanets', $eventdata);
				unset($eventdata);


				// Update `WinningPayout`...
				$query = "
				UPDATE `%prefix%players`
				SET `WinningPayout` = CASE `PlayerId`
				";

				$playerids = array();
				foreach ($score as $item) {
					if ($item['won'] > 0) {
						$playerids[] = $item['id'];
						$query .= 'WHEN '. $item['id'] .' THEN `WinningPayout` + '. $item['won'] .LF;
					}
				}
				unset($item);

				$query .= "
				END
				WHERE `PlayerId` IN (". implode(',', $playerids) .");
				";

				// ...only if one Player has a Score
				if (count($playerids) > 0) {
					$result = $aseco->db->query($query);
					if (!$result) {
						$aseco->console('[RecordsEyepiece] UPDATE `WinningPayout` failed: [for statement "'. str_replace("\t", '', $query) .'"]');
					}
				}
				unset($playerids);

			}
			else {
				// Not enough Players has finished this Map
				$xml .= '<quad posn="0.85 -2.6 0.04" sizen="5 5" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="3.45 -4.2 0.05" sizen="9.2 0" halign="center" textsize="3.5" text="$O$000!"/>';
				$xml .= '<label posn="6.7 -3 0.002" sizen="23.95 1.7" scale="0.9" autonewline="1" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="Not enough Players finished Map,'. LF .'winning payment temporary off."/>';
			}
		}
		else {
			// Server out of Planets
			$xml .= '<quad posn="0.85 -2.6 0.04" sizen="5 5" style="Icons64x64_1" substyle="YellowHigh"/>';
			$xml .= '<label posn="3.45 -4.2 0.05" sizen="9.2 0" halign="center" textsize="3.5" text="$O$000!"/>';
			$xml .= '<label posn="6.7 -3 0.002" sizen="23.95 1.7" scale="0.9" autonewline="1" textcolor="'. $this->config['WINNING_PAYOUT'][0]['COLORS'][0]['PLANETS'][0] .'" text="Server out of Planets now,'. LF .'winning payment turned off.'. LF .'Please donate some. =D"/>';
		}
		$xml .= $this->templates['WINNING_PAYOUT']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function winningPayout ($player) {
		global $aseco;

		if ($this->cache['PlayerWinnings'][$player->login]['FinishPayment'] > 0) {
			// Pay the won Planets to the disconnected Player now
			$message = $aseco->formatText($this->config['MESSAGES'][0]['WINNING_MAIL_BODY'][0],
				(int)$this->cache['PlayerWinnings'][$player->login]['FinishPayment'],
				$aseco->server->login,
				$aseco->server->name
			);
			$message = str_replace('{br}', "%0A", $message);  // split long message

			$billid = false;
			try {
				$billid = $aseco->client->query('Pay', (string)$player->login, (int)$this->cache['PlayerWinnings'][$player->login]['FinishPayment'], (string)$aseco->formatColors($message) );
			}
			catch (Exception $exception) {
				$aseco->console('[RecordsEyepiece] Pay '. $this->cache['PlayerWinnings'][$player->login]['FinishPayment'] .' Planets to Player "'. $player->login .'" failed: [' . $exception->getCode() . '] ' . $exception->getMessage());
				return false;
			}

			// Payment done...
			$aseco->console('[RecordsEyepiece] Pay '. $this->cache['PlayerWinnings'][$player->login]['FinishPayment'] .' Planets to Player "'. $player->login .'" done. (BillId #'. $billid .')');

			// Store the paid-off amount of Planets
			$this->cache['PlayerWinnings'][$player->login]['FinishPaid'] = $this->cache['PlayerWinnings'][$player->login]['FinishPayment'];

			// Reset the counter to prevent payment at /admin shutdown
			$this->cache['PlayerWinnings'][$player->login]['FinishPayment'] = 0;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDedimaniaRecords () {
		global $aseco;

		// Clean array
		$this->scores['DedimaniaRecords'] = array();

		if ( ( isset($aseco->plugins['PluginDedimania']->db['Map']['Records']) ) && (count($aseco->plugins['PluginDedimania']->db['Map']['Records']) > 0) ) {
			for ($i = 0; $i < count($aseco->plugins['PluginDedimania']->db['Map']['Records']); $i ++) {
				if ( ($this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0] === 0) || ($this->config['DEDIMANIA_RECORDS'][0]['DISPLAY_MAX_RECORDS'][0] > $i) ) {
					if ($aseco->plugins['PluginDedimania']->db['Map']['Records'][$i]['Best'] > 0) {
						$this->scores['DedimaniaRecords'][$i]['rank']		= ($i+1);
						$this->scores['DedimaniaRecords'][$i]['login']		= $aseco->plugins['PluginDedimania']->db['Map']['Records'][$i]['Login'];
						$this->scores['DedimaniaRecords'][$i]['nickname']	= $this->handleSpecialChars($aseco->plugins['PluginDedimania']->db['Map']['Records'][$i]['NickName']);
						$this->scores['DedimaniaRecords'][$i]['score']		= $aseco->formatTime($aseco->plugins['PluginDedimania']->db['Map']['Records'][$i]['Best']);
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

	public function getLocalRecords ($gamemode) {
		global $aseco;

		// Clean array
		$this->scores['LocalRecords'] = array();

		if (count($aseco->plugins['PluginLocalRecords']->records->record_list) == 0) {
			$this->config['States']['LocalRecords']['NoRecordsFound'] = true;
		}
		else {
			$i = 0;
			foreach ($aseco->plugins['PluginLocalRecords']->records->record_list as $entry) {
				$this->scores['LocalRecords'][$i]['rank']	= ($i+1);
				$this->scores['LocalRecords'][$i]['login']	= $entry->player->login;
				$this->scores['LocalRecords'][$i]['nickname']	= $this->handleSpecialChars($entry->player->nickname);
				if ($gamemode == Gameinfo::STUNTS) {
					$this->scores['LocalRecords'][$i]['score'] = $this->formatNumber($entry->score, 0);
				}
				else {
					$this->scores['LocalRecords'][$i]['score'] = $aseco->formatTime($entry->score);
				}

				$i++;
			}
			unset($entry);

			$this->config['States']['LocalRecords']['ChkSum'] = $this->buildRecordDigest('locals', $aseco->plugins['PluginLocalRecords']->records->record_list);
			$this->config['States']['LocalRecords']['NoRecordsFound'] = false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getLiveRankings ($gamemode) {
		global $aseco;

		if ($aseco->server->rankings->count() > 0) {
			// Clean before filling
			$this->scores['LiveRankings'] = array();

			$i = 0;
			foreach ($aseco->server->rankings->ranking_list as $login => $data) {
				if ( ($data->time > 0) || ($data->score > 0) ) {

					$this->scores['LiveRankings'][$i]['rank']	= $data->rank;
					$this->scores['LiveRankings'][$i]['login']	= $data->login;
					$this->scores['LiveRankings'][$i]['nickname']	= $this->handleSpecialChars($data->nickname);
					if ($gamemode == Gameinfo::ROUNDS) {
						// Display Score instead Time?
						if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['DISPLAY_TYPE'][0] == true) {
							$this->scores['LiveRankings'][$i]['score'] = $aseco->formatTime($data->time);
						}
						else {
							if ( isset($aseco->server->gameinfo->rounds['PointsLimit']) ) {
								$remaining = ($aseco->server->gameinfo->rounds['PointsLimit'] - $data->score);
								if ($remaining < 0) {
									$remaining = 0;
								}
								$this->scores['LiveRankings'][$i]['score'] = str_replace(
									array(
										'{score}',
										'{remaining}',
										'{pointlimit}',
									),
									array(
										$data->score,
										$remaining,
										$aseco->server->gameinfo->rounds['PointsLimit']
									),
									$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['FORMAT'][0]
								);
							}
							else {
								$this->scores['LiveRankings'][$i]['score'] = $data->score;
							}
						}
					}
					else if ($gamemode == Gameinfo::TIMEATTACK) {
						$this->scores['LiveRankings'][$i]['score'] = $aseco->formatTime($data->time);
					}
					else if ($gamemode == Gameinfo::TEAM) {
						// Player(Team) with score
						$this->scores['LiveRankings'][$i]['rank']	= $data->rank;
						$this->scores['LiveRankings'][$i]['score']	= $data->score;
					}
					else if ($gamemode == Gameinfo::LAPS) {
						// Display Checkpoints instead Time?
						if ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['DISPLAY_TYPE'][0] == true) {
							$this->scores['LiveRankings'][$i]['score'] = $aseco->formatTime($data->time);
						}
						else {
							if (isset($this->cache['Map']['NbCheckpoints']) && isset($this->cache['Map']['NbLaps'])) {
								if ($aseco->server->maps->current->multilap == true) {
									$this->scores['LiveRankings'][$i]['score'] = count($data->cps) .'/'. ($this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps']);
								}
								else {
									$this->scores['LiveRankings'][$i]['score'] = count($data->cps) .'/'. $this->cache['Map']['NbCheckpoints'];
								}
							}
							else {
								$this->scores['LiveRankings'][$i]['score'] = count($data->cps) . ((count($data->cps) == 1) ? ' cp.' : ' cps.');
							}
						}
					}
					else if ($gamemode == Gameinfo::CUP) {
						if ( isset($aseco->server->gameinfo->cup['PointsLimit']) ) {
							$this->scores['LiveRankings'][$i]['score'] = $data->score .'/'. $aseco->server->gameinfo->cup['PointsLimit'];
						}
						else {
							$this->scores['LiveRankings'][$i]['score'] = $data->score;
						}
					}
					else if ($gamemode == Gameinfo::STUNTS) {
						$this->scores['LiveRankings'][$i]['score'] = $this->formatNumber($data->score, 0);
					}
				}
				else if ($gamemode == Gameinfo::TEAM) {
					// Team without score
					$this->scores['LiveRankings'][$i]['rank']	= $data->rank;
					$this->scores['LiveRankings'][$i]['score']	= 0;
					$this->scores['LiveRankings'][$i]['login']	= $data->login;
					$this->scores['LiveRankings'][$i]['nickname']	= $this->handleSpecialChars($data->nickname);
				}

				$i++;
			}

			if ($gamemode == Gameinfo::TEAM) {
				// Was TeamPointsLimit set?
				if ( isset($aseco->server->gameinfo->team['PointsLimit']) ) {
					$this->scores['LiveRankings'][0]['score'] = $this->scores['LiveRankings'][0]['score'] .'/'. $aseco->server->gameinfo->team['PointsLimit'] .' pts.';
					$this->scores['LiveRankings'][1]['score'] = $this->scores['LiveRankings'][1]['score'] .'/'. $aseco->server->gameinfo->team['PointsLimit'] .' pts.';
				}
				else {
					$this->scores['LiveRankings'][0]['score'] .= ' pts.';
					$this->scores['LiveRankings'][1]['score'] .= ' pts.';
				}
			}

			$this->config['States']['LiveRankings']['NoRecordsFound'] = false;
		}
		else {
			$this->config['States']['LiveRankings']['NoRecordsFound'] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getVisitors () {
		global $aseco;

		$query = "
		SELECT
			MAX(`PlayerId`) AS `PlayerCount`
		FROM `%prefix%players`;
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['Visitors'] = 0;

				if ($row = $res->fetch_object()) {
					$this->scores['Visitors'] = $this->formatNumber((int)$row->PlayerCount, 0);
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopRankings ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`p`.`Login`,
			`p`.`Nickname`,
			(ROUND(`r`.`Average` / 1000) / 10) AS `Average`
		FROM `%prefix%players` AS `p`
		LEFT JOIN `%prefix%ranks` AS `r` ON `p`.`PlayerId` = `r`.`PlayerId`
		WHERE `r`.`Average` != 0
		ORDER BY `r`.`Average` ASC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopRankings'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopRankings'][$i]['rank']	= ($i+1);
					$this->scores['TopRankings'][$i]['login']	= $row->Login;
					$this->scores['TopRankings'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopRankings'][$i]['score']	= sprintf("%.1f", $row->Average);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopWinners ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`p`.`Login`,
			`p`.`Nickname`,
			`p`.`Wins`
		FROM `%prefix%players` AS `p`
		WHERE `p`.`Wins` > 0
		ORDER BY `p`.`Wins` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopWinners'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopWinners'][$i]['rank']		= ($i+1);
					$this->scores['TopWinners'][$i]['login']	= $row->Login;
					$this->scores['TopWinners'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopWinners'][$i]['score']	= $this->formatNumber((int)$row->Wins, 0);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMostRecords ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`Login`,
			`Nickname`,
			`MostRecords`
		FROM `%prefix%players`
		WHERE `MostRecords` > 0
		ORDER BY `MostRecords` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['MostRecords'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['MostRecords'][$i]['rank']	= ($i+1);
					$this->scores['MostRecords'][$i]['login']	= $row->Login;
					$this->scores['MostRecords'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['MostRecords'][$i]['score']	= $this->formatNumber((int)$row->MostRecords, 0);
					$this->scores['MostRecords'][$i]['scoplain']	= (int)$row->MostRecords;

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMostFinished ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`Login`,
			`Nickname`,
			`MostFinished`
		FROM `%prefix%players`
		ORDER BY `MostFinished` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['MostFinished'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['MostFinished'][$i]['rank']	= ($i+1);
					$this->scores['MostFinished'][$i]['login']	= $row->Login;
					$this->scores['MostFinished'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['MostFinished'][$i]['score']	= $this->formatNumber((int)$row->MostFinished, 0);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopPlaytime ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`p`.`Login`,
			`p`.`Nickname`,
			`p`.`TimePlayed`
		FROM `%prefix%players` AS `p`
		WHERE `p`.`TimePlayed` > 3600
		ORDER BY `p`.`TimePlayed` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopPlaytime'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopPlaytime'][$i]['rank']	= ($i+1);
					$this->scores['TopPlaytime'][$i]['login']	= $row->Login;
					$this->scores['TopPlaytime'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopPlaytime'][$i]['score']	= $this->formatNumber(round($row->TimePlayed / 3600), 0) . ' h';

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopDonators ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`Login`,
			`Nickname`,
			`Donations`
		FROM `%prefix%players`
		WHERE `Donations` != 0
		ORDER BY `Donations` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopDonators'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopDonators'][$i]['rank']	= ($i+1);
					$this->scores['TopDonators'][$i]['login']	= $row->Login;
					$this->scores['TopDonators'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopDonators'][$i]['score']	= $this->formatNumber((int)$row->Donations, 0) .' P';
					$this->scores['TopDonators'][$i]['scoplain']	= (int)$row->Donations;

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopNationList ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			COUNT(`Nation`) AS `Count`,
			`Nation`
		FROM `%prefix%players`
		GROUP BY `Nation`
		ORDER BY `Count` DESC
		". $appendix .";
		";

		$flagfix = array(
			'SCG'	=> 'SRB',
			'ROM'	=> 'ROU',
			'CAR'	=> 'CMR'
		);

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopNations'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopNations'][$i]['nation']	= (isset($flagfix[$row->Nation]) ? $flagfix[$row->Nation] : $row->Nation);
					$this->scores['TopNations'][$i]['count']	= $this->formatNumber((int)$row->Count, 0);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopMaps () {

		// Clean before filling
		$this->scores['TopMaps'] = array();

		// Copy the Maplist
		$data = $this->cache['MapList'];

		// Sort by Karma
		$karma = array();
		foreach ($data as $key => $row) {
			$karma[$key] = $row['karma'];
		}
		array_multisort($karma, SORT_NUMERIC, SORT_DESC, $data);
		unset($karma, $key, $row);

		$i = 0;
		foreach ($data as $key => $row) {

			// Do not add Maps with lower amount of votes
			if ($row['karma_votes'] < $this->config['FEATURES'][0]['KARMA'][0]['MIN_VOTES'][0]) {
				continue;
			}

			// Do not add Map with a Karma lower then 1 (only necessary for <calculation_method> 'rasp')
			if ($row['karma'] < 1) {
				continue;
			}

			// Do not add Maps without any votes
			if ($row['karma_votes'] == 0) {
				continue;
			}

			$this->scores['TopMaps'][$i]['rank']	= ($i+1);
			$this->scores['TopMaps'][$i]['karma']	= $row['karma'];
			$this->scores['TopMaps'][$i]['map']	= $row['name'];
			$i ++;
		}
		unset($data, $key, $row);

		$this->config['States']['TopMaps']['NeedUpdate'] = false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopVoters ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			COUNT(`r`.`Score`) AS `vote_count`,
			`p`.`Login` AS `login`,
			`p`.`Nickname` AS `nickname`
		FROM `%prefix%ratings` AS `r`, `%prefix%players` AS `p`
		WHERE `r`.`PlayerId` = `p`.`PlayerId`
		GROUP BY `r`.`PlayerId`
		ORDER BY `vote_count` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopVoters'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopVoters'][$i]['rank']	= ($i+1);
					$this->scores['TopVoters'][$i]['score']	= $this->formatNumber((int)$row->vote_count, 0);
					$this->scores['TopVoters'][$i]['login']	= $row->login;
					$this->scores['TopVoters'][$i]['nickname']	= $this->handleSpecialChars($row->nickname);

					$i++;
				}

				$this->config['States']['TopVoters']['NeedUpdate'] = false;
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopBetwins ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		if ($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['DISPLAY'][0] == true) {
			// Calculate the Average
			$query = "
			SELECT
				`p`.`Login`,
				`p`.`Nickname`,
				((`b`.`wins` / `b`.`stake`) * `b`.`countwins`) AS `won`
			FROM `betting` AS `b`
			LEFT JOIN `%prefix%players` AS `p` ON `p`.`Login` = `b`.`login`
			WHERE `b`.`wins` > 0
			AND `p`.`Nickname` IS NOT NULL
			ORDER BY `won` DESC
			". $appendix .";
			";
		}
		else {
			// Get the Planets
			$query = "
			SELECT
				`p`.`Login`,
				`p`.`Nickname`,
				`b`.`wins` AS `won`
			FROM `betting` AS `b`
			LEFT JOIN `%prefix%players` AS `p` ON `p`.`Login` = `b`.`login`
			WHERE `b`.`wins` > 0
			AND `p`.`Nickname` IS NOT NULL
			ORDER BY `won` DESC
			". $appendix .";
			";
		}

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopBetwins'] = array();

				$i = 0;
				if ($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['DISPLAY'][0] == true) {
					// Wanna have the average
					while ($row = $res->fetch_object()) {
						$this->scores['TopBetwins'][$i]['rank']		= ($i+1);
						$this->scores['TopBetwins'][$i]['login']	= $row->Login;
						$this->scores['TopBetwins'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
						$this->scores['TopBetwins'][$i]['won']		= sprintf("%.2f", $row->won);

						$i++;
					}
				}
				else {
					// Wanna have the Planets
					while ($row = $res->fetch_object()) {
						$this->scores['TopBetwins'][$i]['rank']		= ($i+1);
						$this->scores['TopBetwins'][$i]['login']	= $row->Login;
						$this->scores['TopBetwins'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
						$this->scores['TopBetwins'][$i]['won']		= $this->formatNumber((int)$row->won, 0) .' P';

						$i++;
					}
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopWinningPayout ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		// Get the Planets
		$query = "
		SELECT
			`Login`,
			`Nickname`,
			`WinningPayout` AS `won`
		FROM `%prefix%players`
		WHERE `WinningPayout` > 0
		AND `Nickname` IS NOT NULL
		ORDER BY `won` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopWinningPayouts'] = array();

				// Wanna have the Planets
				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopWinningPayouts'][$i]['rank']		= ($i+1);
					$this->scores['TopWinningPayouts'][$i]['login']		= $row->Login;
					$this->scores['TopWinningPayouts'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopWinningPayouts'][$i]['won']		= $this->formatNumber((int)$row->won, 0) .' P';

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopRoundscore ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`RoundPoints`,
			`Login`,
			`Nickname`
		FROM `%prefix%players`
		WHERE `RoundPoints` > 0
		ORDER BY `RoundPoints` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopRoundscore'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopRoundscore'][$i]['rank']	= ($i+1);
					$this->scores['TopRoundscore'][$i]['score']	= $this->formatNumber((int)$row->RoundPoints, 0);
					$this->scores['TopRoundscore'][$i]['login']	= $row->Login;
					$this->scores['TopRoundscore'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopVisitors ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`Visits`,
			`Login`,
			`Nickname`
		FROM `%prefix%players`
		WHERE `Visits` > 0
		ORDER BY `Visits` DESC
		". $appendix .";
		";


		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopVisitors'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopVisitors'][$i]['rank']	= ($i+1);
					$this->scores['TopVisitors'][$i]['score']	= $this->formatNumber((int)$row->Visits, 0);
					$this->scores['TopVisitors'][$i]['login']	= $row->Login;
					$this->scores['TopVisitors'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopActivePlayers ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`Login`,
			`Nickname`,
			DATEDIFF('". date('Y-m-d H:i:s') ."', `LastVisit`) AS `Days`
		FROM `%prefix%players`
		ORDER BY `LastVisit` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopActivePlayers'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopActivePlayers'][$i]['rank']		= ($i+1);
					$this->scores['TopActivePlayers'][$i]['login']		= $row->Login;
					$this->scores['TopActivePlayers'][$i]['nickname']	= $this->handleSpecialChars($row->Nickname);
					$this->scores['TopActivePlayers'][$i]['score']		= (($row->Days == 0) ? 'Today' : $this->formatNumber(-$row->Days, 0) .' d');
					$this->scores['TopActivePlayers'][$i]['scoplain']	= (int)$row->Days;

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getTopContinents ($limit = -1) {
		global $aseco;

		$appendix = '';
		if ($limit > 0) {
			$appendix = 'LIMIT '. $limit;
		}

		$query = "
		SELECT
			`p`.`Continent`,
			COUNT(`p`.`Continent`) AS `ContinentCount`
		FROM `%prefix%players` AS `p`
		WHERE `p`.`Continent` > 0
		GROUP BY `p`.`Continent`
		ORDER BY `ContinentCount` DESC
		". $appendix .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				// Clean before filling
				$this->scores['TopContinents'] = array();

				$i = 0;
				while ($row = $res->fetch_object()) {
					$this->scores['TopContinents'][$i]['count']	= $this->formatNumber((int)$row->ContinentCount, 0);
					$this->scores['TopContinents'][$i]['continent']	= $aseco->continent->abbrToContinent($row->Continent);

					$i++;
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentSong () {
		global $aseco;

		// Get current song and strip server path
		$current = $aseco->client->query('GetForcedMusic');
		if ( ($current['Url'] != '') || ($current['File'] != '') ) {
			if ( isset($aseco->plugins['PluginMusicServer']) ) {
				$songname = str_replace(strtolower($aseco->plugins['PluginMusicServer']->server), '', ($current['Url'] != '' ? strtolower($current['Url']) : strtolower($current['File'])));
			}
			else {
				$songname = ($current['Url'] != '' ? strtolower($current['Url']) : strtolower($current['File']));
			}

			for ($i = 0; $i < count($this->cache['MusicServerPlaylist']); $i ++) {
				if (strtolower($this->cache['MusicServerPlaylist'][$i]['File']) == $songname) {
					$this->config['CurrentMusicInfos'] = array(
						'Artist'	=> $this->cache['MusicServerPlaylist'][$i]['Artist'],
						'Title'		=> $this->cache['MusicServerPlaylist'][$i]['Title']
					);
					return;
				}
			}
		}

		$this->config['CurrentMusicInfos'] = array(
			'Artist'	=> 'nadeo',
			'Title'		=> 'In-game music',
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMusicServerPlaylist ($only_refresh = false, $output_info = false) {
		global $aseco;

		if ( !isset($aseco->plugins['PluginMusicServer']) ) {
			return;
		}

		if ($only_refresh == false) {

			// Clean before refill
			$this->cache['MusicServerPlaylist'] = array();

			if ($output_info == true) {
				$amount = count($aseco->plugins['PluginMusicServer']->songs);
				$aseco->console('[RecordsEyepiece] Reading '. $amount . (($amount == 1) ? ' Song' : ' Songs') .'...');
			}

			$id = 1;	// SongId starts from 1
			foreach ($aseco->plugins['PluginMusicServer']->songs as $song) {

				if ( (isset($aseco->plugins['PluginMusicServer']->tags[$song]['Artist'])) && (!empty($aseco->plugins['PluginMusicServer']->tags[$song]['Artist'])) ) {
					$this->cache['MusicServerPlaylist'][] = array(
						'SongId'	=> $id,
						'File'		=> $song,
						'Artist'	=> '$Z'. $this->handleSpecialChars(utf8_decode($aseco->plugins['PluginMusicServer']->tags[$song]['Artist'])),
						'Title'		=> '$Z'. $this->handleSpecialChars(utf8_decode($aseco->plugins['PluginMusicServer']->tags[$song]['Title']))
					);
				}
				else {
					// Try to convert filename into "Artist" and "Title",
					// e.g. "paul_kalkbrenner_-_gebrunn_gebrunn_(berlin_calling_mix).ogg"
					// to $artist = 'Paul Kalkbrenner', $title = 'Gebrunn Gebrunn (Berlin Calling Mix)'
					$artist = '---';
					$title = '(Without Ogg Vorbis Infotag)';

					// Replace "_" with " "
					$music = str_replace('_', ' ', $song);

					// Remove ".ogg" or ".mux"
					$music = str_ireplace(
						array(
							'.ogg',
							'.mux'
						),
						array(
							'',
							''
						),
						$music
					);

					$pieces = explode('-', $music);
					foreach ($pieces as &$item) {
						$item = trim($item);
					}
					unset($item);
					if (count($pieces) > 2) {
						$artist = $pieces[0];
						$title = $pieces[count($pieces)-1];
					}
					else {
						$artist = (isset($pieces[0]) ? $pieces[0] : $artist);
						$title = (isset($pieces[1]) ? $pieces[1] : $title);
					}

					$this->cache['MusicServerPlaylist'][] = array(
						'SongId'	=> $id,
						'File'		=> $song,
						'Artist'	=> '$Z'. $this->handleSpecialChars(utf8_decode(ucwords($artist))),
						'Title'		=> '$Z'. $this->handleSpecialChars(utf8_decode(ucwords($title)))
					);

					// Setup the $aseco->plugins['PluginMusicServer']->tags also
					$aseco->plugins['PluginMusicServer']->tags[$song]['Artist'] = $this->handleSpecialChars(utf8_decode(ucwords($artist)));
					$aseco->plugins['PluginMusicServer']->tags[$song]['Title'] = $this->handleSpecialChars(utf8_decode(ucwords($title)));
				}
				$id ++;
			}
			unset($song);


			if ($this->config['FEATURES'][0]['SONGLIST'][0]['SORTING'][0] == true) {
				// Build the arrays for sorting
				$artists = array();
				$titles = array();
				foreach ($this->cache['MusicServerPlaylist'] as $key => $row) {
					$artists[$key]	= strtolower($row['Artist']);
					$titles[$key]	= strtolower($row['Title']);
				}
				unset($row);

				// Sort by Artist and Title
				array_multisort($artists, SORT_ASC, $titles, SORT_ASC, $this->cache['MusicServerPlaylist']);
				unset($artists, $titles);
			}
		}
		else {
			// It is required to refresh the SongIds if a Player juke'd a Song,
			// because plugin.musicserver.php resorts the SongIds at this situation
			// in the function selectSong() at the event onEndMap.

			$id = 1;	// SongId starts from 1
			foreach ($aseco->plugins['PluginMusicServer']->songs as $song) {
				foreach ($this->cache['MusicServerPlaylist'] as &$item) {
					if ($item['File'] ==  strtolower($song)) {
						$item['SongId'] = $id;
					}
				}
				unset($item);

				$id ++;
			}
			unset($song);

			// Set status to "done"
			$this->config['States']['MusicServerPlaylist']['NeedUpdate'] = false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerLocalRecords ($pid) {
		global $aseco;

		// Get Player's Record for each Map, order in 'Stunts' DESC and all other ASC
		$query = "
		SELECT
			`r`.`PlayerId`,
			`r`.`Score`,
			`m`.`Uid`,
			`r`.`MapId`
		FROM `%prefix%records` AS `r`
		LEFT JOIN `%prefix%maps` AS `m` ON `m`.`MapId` = `r`.`MapId`
		WHERE `r`.`Score` != ''
		ORDER BY `r`.`MapId` ASC, `Score` ". ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 'DESC' : 'ASC') .",`Date` ASC;
		";
		$result = $aseco->db->query($query);

		if ($result) {
			$last = false;
			$list = array();
			$pos = 1;
			while ($row = $result->fetch_object()) {

				// Reset Rank counter
				if ($last != $row->Uid) {
					$last = $row->Uid;
					$pos = 1;
				}

				// Do not count Rank if already in Maplist
				if ( isset($list[$row->Uid]) ) {
					continue;
				}

				// Only add the calling Player
				if ($row->PlayerId == $pid) {
					$list[$row->Uid] = array(
						'rank'	=> $pos,
						'score'	=> $row->Score,
					);
					continue;
				}
				$pos ++;
			}
			$result->free_result();
		}

		return $list;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getPlayerUnfinishedMaps ($pid) {
		global $aseco;

		// Get list of finished Maps
		$query = "
		SELECT
			`MapId`
		FROM `%prefix%times`
		WHERE `PlayerId` = '". $pid ."'
		GROUP BY `MapId`
		ORDER BY `MapId`;
		";
		$result = $aseco->db->query($query);

		if ($result) {
			$finished = array();
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_object())
					$finished[] = $row->MapId;
			}
			$result->free_result();

			if ( !empty($finished) ) {
				// Get list of unfinished Maps
				$query = "
				SELECT
					`Uid`
				FROM `%prefix%maps`
				WHERE `MapId` NOT IN (". implode(',', $finished) .");
				";
				$result = $aseco->db->query($query);

				if ($result) {
					$unfinished = array();
					while ($row = $result->fetch_object()) {
						// Add only Maps that are in the Maplist
						foreach ($this->cache['MapList'] as $map) {
							if ($map['uid'] == $row->Uid) {
								$unfinished[] = $row->Uid;
								break 1;
							}
						}
						unset($map);
					}
					$result->free_result();

					return $unfinished;
				}
				return array();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function findPlayerRecords ($login) {

		$DedimaniaRecords	= false;
		$LocalRecords		= false;

		// Check for DedimaniaRecords
		if ( count($this->scores['DedimaniaRecords']) > 0) {
			foreach ($this->scores['DedimaniaRecords'] as $item) {
				if ($item['login'] == $login) {
					$DedimaniaRecords = true;
					break;
				}
			}
			unset($item);
		}

		// Check for LocalRecords
		if ( count($this->scores['LocalRecords']) > 0) {
			foreach ($this->scores['LocalRecords'] as $item) {
				if ($item['login'] == $login) {
					$LocalRecords = true;
					break;
				}
			}
			unset($item);
		}

		return array(
			'DedimaniaRecords'	=> $DedimaniaRecords,
			'LocalRecords'		=> $LocalRecords
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildScorelistWidgetEntry ($manialinkid, $settings, $list, $fieldnames) {

		$limit = $settings['ENTRIES'][0];
		if (!$limit) {
			$limit = 6;
		}
		$xml = str_replace(
			array(
				'%manialinkid%',
				'%posx%',
				'%posy%',
				'%widgetheight%',
				'%icon_style%',
				'%icon_substyle%',
				'%title%'
			),
			array(
				$manialinkid,
				$settings['POS_X'][0],
				$settings['POS_Y'][0],
				($this->config['LineHeight'] * $settings['ENTRIES'][0] + 3.3),
				$settings['ICON_STYLE'][0],
				$settings['ICON_SUBSTYLE'][0],
				$settings['TITLE'][0]
			),
			$this->templates['SCORETABLE_LISTS']['HEADER']
		);

		if ( count($list) > 0 ) {
			// Build the entries
			$line = 0;
			$offset = 3;
			foreach ($list as $item) {
				$xml .= '<label posn="2.1 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="1.7 1.7" halign="right" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item['rank'] .'."/>';
				$xml .= '<label posn="5.7 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item[$fieldnames[0]] .'"/>';
				$xml .= '<label posn="5.9 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="10.2 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item[$fieldnames[1]] .'"/>';

				$line ++;

				if ($line >= $limit) {
					break;
				}
			}
		}
		$xml .= str_replace(
			'%widgetscale%',
			$settings['SCALE'][0],
			$this->templates['SCORETABLE_LISTS']['FOOTER']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTopNationsForScore ($limit = 6) {
		global $aseco;

		$xml = str_replace(
			array(
				'%manialinkid%',
				'%posx%',
				'%posy%',
				'%widgetheight%',
				'%icon_style%',
				'%icon_substyle%',
				'%title%'
			),
			array(
				'TopNationsWidgetAtScore',
				$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['POS_X'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['POS_Y'][0],
				($this->config['LineHeight'] * $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ENTRIES'][0] + 3.3),
				$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_STYLE'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_SUBSTYLE'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['TITLE'][0]
			),
			$this->templates['SCORETABLE_LISTS']['HEADER']
		);

		if ( count($this->scores['TopNations']) > 0 ) {
			// Build the entries
			$line = 0;
			$offset = 3;
			foreach ($this->scores['TopNations'] as $item) {
				$xml .= '<label posn="4 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item['count'] .'"/>';
				$xml .= '<quad posn="4.65 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.002" sizen="2 2" image="file://Skins/Avatars/Flags/'. (($item['nation'] == 'OTH') ? 'other' : $item['nation']) .'.dds"/>';
				$xml .= '<label posn="7 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="8.75 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $aseco->country->iocToCountry($item['nation']) .'"/>';

				$line ++;

				if ($line >= $limit) {
					break;
				}
			}
			unset($item);
		}
		$xml .= str_replace(
			'%widgetscale%',
			$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['SCALE'][0],
			$this->templates['SCORETABLE_LISTS']['FOOTER']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTopAverageTimesForScore ($limit = 6) {
		global $aseco;

		$xml = str_replace(
			array(
				'%manialinkid%',
				'%posx%',
				'%posy%',
				'%widgetheight%',
				'%icon_style%',
				'%icon_substyle%',
				'%title%'
			),
			array(
				'TopAverageTimesWidgetAtScore',
				$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['POS_X'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['POS_Y'][0],
				($this->config['LineHeight'] * $this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ENTRIES'][0] + 3.3),
				$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ICON_STYLE'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['ICON_SUBSTYLE'][0],
				$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['TITLE'][0]
			),
			$this->templates['SCORETABLE_LISTS']['HEADER']
		);

		if ( count($this->scores['TopAverageTimes']) > 0 ) {

			// Calculate the averaves for each Player
			$data = array();
			foreach ($aseco->server->players->player_list as $player) {

				// Skip Player without any finish
				if ( isset($this->scores['TopAverageTimes'][$player->login]) ) {
					$score = floor( array_sum($this->scores['TopAverageTimes'][$player->login]) / count($this->scores['TopAverageTimes'][$player->login]) );
					$data[] = array(
						'score'		=> $score,
						'nickname'	=> $this->handleSpecialChars($player->nickname)
					);
				}
			}
			unset($player);

			// Sort the result
			$scores = array();
			foreach ($data as $key => $row) {
				$scores[$key] = $row['score'];
			}
			array_multisort($scores, SORT_NUMERIC, $data);
			unset($scores, $row);

			// Build the entries
			$line = 0;
			$offset = 3;
			foreach ($data as $item) {
				$xml .= '<label posn="2.1 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="1.7 1.7" halign="right" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . ($line + 1) .'."/>';
				$xml .= '<label posn="5.7 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . (($aseco->server->gameinfo->mode != Gameinfo::STUNTS) ? $aseco->formatTime($item['score']) : $this->formatNumber($item['score'], 0)) .'"/>';
				$xml .= '<label posn="5.9 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.002" sizen="10.2 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $item['nickname'] .'"/>';

				$line ++;

				if ($line >= $limit) {
					break;
				}
			}
			unset($item);
		}
		$xml .= str_replace(
			'%widgetscale%',
			$this->config['SCORETABLE_LISTS'][0]['TOP_AVERAGE_TIMES'][0]['SCALE'][0],
			$this->templates['SCORETABLE_LISTS']['FOOTER']
		);

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildRecordWidgetContent ($gamemode, $player, $limit = 100, $widget) {

		// Setup the listname from given $widget, e.g. 'DEDIMANIA_RECORDS' to 'DedimaniaRecords'
		$list = str_replace(' ', '', ucwords(implode(' ', explode('_', strtolower($widget)))));

		// Set the preset
		$preset = false;
		if ($player !== false) {
			$preset = $player->data['PluginRecordsEyepiece']['Prefs']['WidgetEmptyEntry'];
		}

		// Set the Topcount
		$topcount = $this->config[$widget][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Add Widget header
		$xml = $this->cache[$list][$gamemode]['WidgetHeader'];

		// Build the entries if already loaded
		if ( count($this->scores[$list]) > 0 ) {

			if ($this->config['States']['NiceMode'] == false) {
				// Build the "CloseToYou" Array
				$records = $this->buildCloseToYouArray($this->scores[$list], $preset, $limit, $topcount);

				// Now check if it is required to build this Manialink (only required in normal mode, nice mode send always)
				$digest = $this->buildCloseToYouDigest($records);
				if ($this->cache['PlayerStates'][$player->login][$list] != false) {
					if ( ($this->cache['PlayerStates'][$player->login][$list] != $digest) || ($this->config['States'][$list]['UpdateDisplay'] == true) ) {

						// Widget is different as before, store them and build the new Widget
						$this->cache['PlayerStates'][$player->login][$list] = $digest;
					}
					else {
						// Widget is unchanged, no need to send now
						return;
					}
				}
				else {
					// Widget is build first time for this Player, store them and build the new Widget
					$this->cache['PlayerStates'][$player->login][$list] = $digest;
				}
			}
			else {
				$records = $this->scores[$list];
			}

			// Create the Widget entries
			$line = 0;
			$behind_rankings = false;
			foreach ($records as $item) {

				// Mark all Players behind the current with an orange icon instead a green one
				if ($item['login'] == $player->login) {
					$behind_rankings = true;
				}

				// Mark connected Players with a record
				if ( ($this->config['FEATURES'][0]['MARK_ONLINE_PLAYER_RECORDS'][0] == true) && ($this->config['States']['NiceMode'] == false) && ($item['login'] != $player->login) ) {
					$xml .= $this->getConnectedPlayerRecord($item['login'], $line, $topcount, $behind_rankings, $this->config[$widget][0]['WIDTH'][0]);
				}

				// Build record entries
				$xml .= $this->getCloseToYouEntry($item, $line, $topcount, $this->config['PlaceholderNoScore'], $this->config[$widget][0]['WIDTH'][0]);

				$line ++;

				if ($line >= $limit) {
					break;
				}
			}
			unset($item);

		}
		else if ($player->login != false) {
			// Create an empty entry
			$xml .= $this->getCloseToYouEntry($preset, 0, $topcount, $this->config['PlaceholderNoScore'], $this->config[$widget][0]['WIDTH'][0]);
		}

		// Add Widget footer
		$xml .= $this->cache[$list][$gamemode]['WidgetFooter'];

		// Send the Widget now
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildDedimaniaRecordsWidgetBody ($gamemode) {

		// Set the right Icon and Title position
		$position = (($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0] < 0) ? 'right' : 'left');

		// Set the Topcount
		$topcount = $this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Calculate the widget height (+ 3.2 for title)
		$widget_height = ($this->config['LineHeight'] * $this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] + 3.2);

		if ($position == 'right') {
			$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 15.5));
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$imagex	= $this->config['Positions'][$position]['image_open']['x'];
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		$build['header'] = str_replace(
			array(
				'%manialinkid%',
				'%actionid%',
				'%posx%',
				'%posy%',
				'%image_open_pos_x%',
				'%image_open_pos_y%',
				'%image_open%',
				'%posx_icon%',
				'%posy_icon%',
				'%icon_style%',
				'%icon_substyle%',
				'%halign%',
				'%posx_title%',
				'%posy_title%',
				'%backgroundwidth%',
				'%backgroundheight%',
				'%borderwidth%',
				'%borderheight%',
				'%widgetwidth%',
				'%widgetheight%',
				'%column_width_name%',
				'%column_height%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'DedimaniaRecordsWidget',
				'showDedimaniaRecordsWindow',
				$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0],
				$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_Y'][0],
				$imagex,
				-($widget_height - 3.18),
				$this->config['Positions'][$position]['image_open']['image'],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$this->config['DEDIMANIA_RECORDS'][0]['ICON_STYLE'][0],
				$this->config['DEDIMANIA_RECORDS'][0]['ICON_SUBSTYLE'][0],
				$this->config['Positions'][$position]['title']['halign'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 0.2),
				($widget_height - 0.2),
				($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] + 0.4),
				($widget_height + 0.6),
				$this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0],
				$widget_height,
				($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 6.45),
				($widget_height - 3.1),
				($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 0.8),
				$this->config['DEDIMANIA_RECORDS'][0]['TITLE'][0]
			),
			$this->templates['RECORD_WIDGETS']['HEADER']
		);

		// Add Background for top X Players
		if ($topcount > 0) {
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] != '') {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] .'"/>';
			}
			else {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['DEDIMANIA_RECORDS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_SUBSTYLE'][0] .'"/>';
			}
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<dedimania_records>
 * License:	GPLv3
 * ----------------------------------
 */
main() {
	declare persistent Boolean RecordsEyepieceDedimaniaRecordsVisible = True;
	declare DedimaniaRecordsWidget		<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	declare Vec3 OrigPosition		= DedimaniaRecordsWidget.RelativePosition;

	DedimaniaRecordsWidget.RelativeScale	= {$this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['SCALE'][0]};
	DedimaniaRecordsWidget.Visible = RecordsEyepieceDedimaniaRecordsVisible;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for pressed F7 to hide the Widget
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "F7") {
						if (DedimaniaRecordsWidget.Visible == False) {
							RecordsEyepieceDedimaniaRecordsVisible = True;
						}
						else {
							RecordsEyepieceDedimaniaRecordsVisible = False;
						}
						DedimaniaRecordsWidget.Visible = RecordsEyepieceDedimaniaRecordsVisible;
					}
				}
			}
		}
	}
}
--></script>
EOL;
		$build['footer'] = $maniascript . $this->templates['RECORD_WIDGETS']['FOOTER'];

		return $build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLocalRecordsWidgetBody ($gamemode) {

		// Set the right Icon and Title position
		$position = (($this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0] < 0) ? 'right' : 'left');

		// Set the Topcount
		$topcount = $this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Calculate the widget height (+ 3.2 for title)
		$widget_height = ($this->config['LineHeight'] * $this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] + 3.2);

		if ($position == 'right') {
			$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 15.5));
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$imagex	= $this->config['Positions'][$position]['image_open']['x'];
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		$build['header'] = str_replace(
			array(
				'%manialinkid%',
				'%actionid%',
				'%posx%',
				'%posy%',
				'%image_open_pos_x%',
				'%image_open_pos_y%',
				'%image_open%',
				'%posx_icon%',
				'%posy_icon%',
				'%icon_style%',
				'%icon_substyle%',
				'%halign%',
				'%posx_title%',
				'%posy_title%',
				'%backgroundwidth%',
				'%backgroundheight%',
				'%borderwidth%',
				'%borderheight%',
				'%widgetwidth%',
				'%widgetheight%',
				'%column_width_name%',
				'%column_height%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'LocalRecordsWidget',
				'showLocalRecordsWindow',
				$this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0],
				$this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['POS_Y'][0],
				$imagex,
				-($widget_height - 3.18),
				$this->config['Positions'][$position]['image_open']['image'],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$this->config['LOCAL_RECORDS'][0]['ICON_STYLE'][0],
				$this->config['LOCAL_RECORDS'][0]['ICON_SUBSTYLE'][0],
				$this->config['Positions'][$position]['title']['halign'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 0.2),
				($widget_height - 0.2),
				($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] + 0.4),
				($widget_height + 0.6),
				$this->config['LOCAL_RECORDS'][0]['WIDTH'][0],
				$widget_height,
				($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 6.45),
				($widget_height - 3.1),
				($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 0.8),
				$this->config['LOCAL_RECORDS'][0]['TITLE'][0]
			),
			$this->templates['RECORD_WIDGETS']['HEADER']
		);

		// Add Background for top X Players
		if ($topcount > 0) {
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] != '') {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] .'"/>';
			}
			else {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LOCAL_RECORDS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_SUBSTYLE'][0] .'"/>';
			}
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<local_records>
 * License:	GPLv3
 * ----------------------------------
 */
main() {
	declare persistent Boolean RecordsEyepieceLocalRecordsVisible = True;
	declare LocalRecordsWidget		<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	LocalRecordsWidget.RelativeScale	= {$this->config['LOCAL_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['SCALE'][0]};

	LocalRecordsWidget.Visible = RecordsEyepieceLocalRecordsVisible;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for pressed F7 to hide the Widget
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "F7") {
						if (LocalRecordsWidget.Visible == False) {
							RecordsEyepieceLocalRecordsVisible = True;
						}
						else {
							RecordsEyepieceLocalRecordsVisible = False;
						}
						LocalRecordsWidget.Visible = RecordsEyepieceLocalRecordsVisible;
					}
				}
			}
		}
	}
}
--></script>
EOL;
		$build['footer'] = $maniascript . $this->templates['RECORD_WIDGETS']['FOOTER'];

		return $build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLiveRankingsWidgetMS ($gamemode, $limit = 50) {

		// Set the right Icon and Title position
		$position = (($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0] < 0) ? 'right' : 'left');

		// Set the Topcount
		$topcount = $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Calculate the widget height (+ 3.2 for title)
		$widget_height = ($this->config['LineHeight'] * $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] + 3.2);

		if ($position == 'right') {
			$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$imagex	= $this->config['Positions'][$position]['image_open']['x'];
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		$xml = str_replace(
			array(
				'%manialinkid%',
				'%actionid%',
				'%posx%',
				'%posy%',
				'%image_open_pos_x%',
				'%image_open_pos_y%',
				'%image_open%',
				'%posx_icon%',
				'%posy_icon%',
				'%icon_style%',
				'%icon_substyle%',
				'%halign%',
				'%posx_title%',
				'%posy_title%',
				'%backgroundwidth%',
				'%backgroundheight%',
				'%borderwidth%',
				'%borderheight%',
				'%widgetwidth%',
				'%widgetheight%',
				'%column_width_name%',
				'%column_height%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'LiveRankingsWidgetMS',
				'showLiveRankingsWindow',
				$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0] -20,
				$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_Y'][0],
				$imagex,
				-($widget_height - 3.18),
				$this->config['Positions'][$position]['image_open']['image'],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$this->config['LIVE_RANKINGS'][0]['ICON_STYLE'][0],
				$this->config['LIVE_RANKINGS'][0]['ICON_SUBSTYLE'][0],
				$this->config['Positions'][$position]['title']['halign'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.2),
				($widget_height - 0.2),
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] + 0.4),
				($widget_height + 0.6),
				$this->config['LIVE_RANKINGS'][0]['WIDTH'][0],
				$widget_height,
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 6.45),
				($widget_height - 3.1),
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8),
				$this->config['LIVE_RANKINGS'][0]['TITLE'][0]
			),
			$this->templates['RECORD_WIDGETS']['HEADER']
		);

		// Add Background for top X Players
		if ($topcount > 0) {
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] != '') {
				$xml .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] .'"/>';
			}
			else {
				$xml .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_SUBSTYLE'][0] .'"/>';
			}
		}


		$offset = 3;
		$textcolor = 'FFFF';
		foreach (range(0,$limit) as $line) {
			$xml .= '<label posn="2.3 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="1.7 1.7" halign="right" scale="0.9" text="" id="RecordsEyepieceLiveRankingsRank'. $line .'"/>';
			$xml .= '<label posn="6 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="3.9 1.7" halign="right" scale="0.9" textcolor="'. $textcolor .'" text="" id="RecordsEyepieceLiveRankingsScore'. $line .'"/>';
			$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="'. sprintf("%.02f", ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 6)) .' 1.7" scale="0.9" text="" id="RecordsEyepieceLiveRankingsNickname'. $line .'"/>';
		}

		// Add marker for LocalUser Rank left and right from the Widget
		$line = 0;
		$xml .= '<frame posn="0 -'. ($this->config['LineHeight'] * $line + $offset - 0.55) .' 0.004" id="RecordsEyepieceLiveRankingsMarker" hidden="true">';
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
			$xml .= '<quad posn="0.4 -0.3 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'" id="RecordsEyepieceLiveRankingsBackgroundMarker" hidden="true"/>';
			$xml .= '<quad posn="-2 -0.3 0.004" sizen="2 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
			$xml .= '<quad posn="'. $this->config['LIVE_RANKINGS'][0]['WIDTH'][0] .' -0.3 0.004" sizen="2 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="0.4 -0.3 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'" id="RecordsEyepieceLiveRankingsBackgroundMarker" hidden="true"/>';
			$xml .= '<quad posn="-2 -0.3 0.004" sizen="2 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
			$xml .= '<quad posn="'. $this->config['LIVE_RANKINGS'][0]['WIDTH'][0] .' -0.3 0.004" sizen="2 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
		}
		$xml .= '<quad posn="-1.8 -0.5 0.005" sizen="1.6 1.6" style="Icons64x64_1" substyle="ShowRight2"/>';
		$xml .= '<quad posn="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] + 0.2) .' -0.5 0.005" sizen="1.6 1.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
		$xml .= '</frame>';


		// Setup the total count of Checkpoints
		$totalcps = 0;
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) || ($gamemode == Gameinfo::CUP) ) {
			if ($this->cache['Map']['ForcedLaps'] > 0) {
				$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['ForcedLaps'];
			}
			else if ($this->cache['Map']['NbLaps'] > 0) {
				$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps'];
			}
			else {
				$totalcps = $this->cache['Map']['NbCheckpoints'];
			}
		}
		else if ( ($this->cache['Map']['NbLaps'] > 0) && ($gamemode == Gameinfo::LAPS) ) {
			$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps'];
		}
		else {
			// All other Gamemodes
			$totalcps = $this->cache['Map']['NbCheckpoints'];
		}


		$ColorSelf	= '$'. substr($this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SELF'][0], 0, 3);
		$ColorTop	= '$'. substr($this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['TOP'][0], 0, 3);
		$ColorBetter	= '$'. substr($this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BETTER'][0], 0, 3);
		$ColorWorse	= '$'. substr($this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['WORSE'][0], 0, 3);

		$multilapmap = (($this->cache['Map']['Current']['multilap'] == true) ? 'True' : 'False');

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<live_rankings> (ManiaScriptBeta)
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
#Include "MathLib" as MathLib
Text FormatTime (Integer MwTime) {
	declare Text FormatedTime = "-:--.---";

	if (MwTime > 0) {
		// Format "246656" to "4:06.65"
		FormatedTime = TextLib::TimeToText(MwTime, True);

		// Strip TSeconds "656" from "246656"
		declare Text MwTimeText = TextLib::ToText(MwTime);
		declare Text TSeconds = TextLib::SubString(MwTimeText, TextLib::Length(MwTimeText)-3, 3);

		// Split "4:06.65" to "4:06" and "65"
		declare Text[] TimeParts = TextLib::Split(".", FormatedTime);

		// Add long TSecond ("656")
		FormatedTime = TimeParts[0] ^ "." ^ TSeconds;
	}
	return FormatedTime;
}
main() {
	declare persistent Boolean RecordsEyepieceLiveRankingsVisible = True;

//	declare Text[Text] RecordsEyepiece;
//	RecordsEyepiece["LiveRankings"] = [
//		"Visible"	=> True,
//		"Position"	=> <12.333, 2.0, 0.5>
//	];
//log(RecordsEyepiece);

	declare LiveRankingsWidget		<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	declare LiveRankingsMarker		<=> (Page.GetFirstChild("RecordsEyepieceLiveRankingsMarker") as CMlFrame);
	declare LiveRankingsMarkerBG		<=> (Page.GetFirstChild("RecordsEyepieceLiveRankingsBackgroundMarker") as CMlQuad);

	declare Text ColorSelf			= "{$ColorSelf}";
	declare Text ColorTop			= "{$ColorTop}";
	declare Text ColorBetter		= "{$ColorBetter}";
	declare Text ColorWorse			= "{$ColorWorse}";
	declare Integer MaxEntries		= {$limit} - 1;
	declare Integer TopCount		= {$topcount};
	declare Integer MarkerOffset		= {$offset};
	declare Real LineHeight			= {$this->config['LineHeight']};
	declare Boolean MultilapMap		= {$multilapmap};
	declare Integer TotalCheckpoints	= {$totalcps};
	declare Boolean UpdateWidget		= True;
	declare Integer RefreshInterval		= 500;
	declare Integer RefreshTime		= CurrentTime;
	declare Integer CurrentLap		= 0;		// Using own CurrentLap instead of Player.CurrentNbLaps
	declare Integer CurrentCheckpoint	= 0;
	declare Integer[Text] LiveRankings;

	LiveRankingsWidget.RelativeScale	= {$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['SCALE'][0]};

	LiveRankingsWidget.Visible = RecordsEyepieceLiveRankingsVisible;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for pressed F7 to hide the Widget
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "F7") {
						if (LiveRankingsWidget.Visible == False) {
							RecordsEyepieceLiveRankingsVisible = True;
						}
						else {
							RecordsEyepieceLiveRankingsVisible = False;
						}
						LiveRankingsWidget.Visible = RecordsEyepieceLiveRankingsVisible;
					}
				}
			}
		}

		if (LiveRankingsWidget.Visible == False) {
			continue;
		}

//LiveRankings["Player01"] = 20000;      // Same time
//LiveRankings["Player02"] = 22000;
//LiveRankings["Player03"] = 24000;
//LiveRankings["Player04"] = 26000;
//LiveRankings["Player05"] = 28000;      // Same time
//LiveRankings["Player06"] = 30000;
//LiveRankings["Player07"] = 32000;
//LiveRankings["Player08"] = 34000;
//LiveRankings["Player09"] = 36000;
//LiveRankings["Player10"] = 38000;
//LiveRankings["Player11"] = 40000;
//LiveRankings["Player12"] = 42000;
//LiveRankings["Player13"] = 44000;
//LiveRankings["Player14"] = 46000;
//LiveRankings["Player15"] = 48000;
//LiveRankings["Player16"] = 50000;
//LiveRankings["Player17"] = 52000;
//LiveRankings["Player18"] = 54000;
//LiveRankings["Player19"] = 56000;
//LiveRankings["Player20"] = 58000;

		foreach (Player in Players) {
			// Skip on Login from Server, that is not a Player ;)
			if (Player.Login == CurrentServerLogin) {
				continue;
			}

			CurrentCheckpoint = 0;
			declare LiveRankings_LastCheckpointCount for Player = -1;
			if (LiveRankings_LastCheckpointCount != Player.CurRace.Checkpoints.count) {
				LiveRankings_LastCheckpointCount = Player.CurRace.Checkpoints.count;

				if (MultilapMap == True) {
					// Reset when Player restarts
					if (LiveRankings_LastCheckpointCount == 0) {
						CurrentLap = 0;
					}

					// Count the Lap and reset the CurrentCheckpoint count
					if ( (CurrentCheckpoint > (TotalCheckpoints-1)) ) {
						CurrentLap += 1;
					}
					CurrentCheckpoint = LiveRankings_LastCheckpointCount - (CurrentLap * TotalCheckpoints);
				}
				else {
					CurrentCheckpoint = LiveRankings_LastCheckpointCount;
				}
//				log("LR: " ^ Player.Login ^ ": Current CP: " ^ CurrentCheckpoint ^ " of " ^ TotalCheckpoints ^ " on lap " ^ CurrentLap ^", CP-Times: "^ Player.CurRace.Checkpoints ^" MultiLap: "^ MultilapMap);
			}

			// Players finish the Map?
			if (CurrentCheckpoint == TotalCheckpoints) {

				declare Integer FinishScore = Player.CurRace.Checkpoints[LiveRankings_LastCheckpointCount-1];
				if (MultilapMap == True) {
					// Reduce last FinishScore with the before Scores
					declare Integer Index = (LiveRankings_LastCheckpointCount - TotalCheckpoints) - 1;
					if (Index > 0) {
						FinishScore -= Player.CurRace.Checkpoints[Index];
					}
				}

				// Check for a better FinishScore as before
				declare Boolean FoundPlayer = False;
				foreach (Nickname => LastScore in LiveRankings) {
					if (Player.Name == Nickname) {
						if (FinishScore < LastScore) {
							// Update entry with an new time
							LiveRankings[Player.Name] = FinishScore;
							UpdateWidget = True;
						}
						else {
							// Add entry with an time from last run
							LiveRankings[Player.Name] = LastScore;
						}
						FoundPlayer = True;
					}
				}
				if (FoundPlayer == False) {
					// Add an new entry
					LiveRankings[Player.Name] = FinishScore;
					UpdateWidget = True;
				}
			}
		}

		if ( (UpdateWidget == True) && (CurrentTime > RefreshTime) ) {

			// Sort by finish time/score
			declare SortedRanking = Integer[Text];
			SortedRanking = LiveRankings.sort();

			// Find the Rank of LocalUser
			declare Integer LocalUserRank = 0;
			declare Integer LocalUserScore = 0;
			declare Integer CurrentRank = 0;
			foreach (Nickname => Score in SortedRanking) {
				if (Nickname == LocalUser.Name) {
					LocalUserRank	= CurrentRank;
					LocalUserScore	= Score;
					break;
				}
				CurrentRank += 1;
			}
			if (SortedRanking.existskey(LocalUser.Name) == False) {
				SortedRanking[LocalUser.Name] = 0;
			}

			// Init RankingList-Array
			declare RankingList = Text[Text][Integer];
			for (Pos, 0, MaxEntries) {
				RankingList[Pos] = ["Rank" => "NULL", "Nickname" => "NULL", "Score" => "NULL"];
			}

			// Add Players that are in the TopCount
			CurrentRank = 0;
			foreach (Nickname => Score in SortedRanking) {
				if ((CurrentRank+1) <= TopCount) {
					RankingList[CurrentRank] = ["Rank" => TextLib::ToText(CurrentRank+1), "Nickname" => Nickname, "Score" => FormatTime(Score)];
				}
				CurrentRank += 1;
				if (CurrentRank > TopCount) {
					break;
				}
			}


			declare Integer LocalUserPosition = 0;
			if (SortedRanking.count == 0) {
				// Add the LocalUser as first entry of RankingList[] and remove the rest
				RankingList = Text[Text][Integer];
				RankingList[LocalUserPosition] = ["Rank" => TextLib::ToText(LocalUserRank+1), "Nickname" => LocalUser.Name, "Score" => FormatTime(LocalUserScore)];
			}
			else if ((LocalUserRank+1) > TopCount) {
				// Center the LocalUser into the rest of (MaxEntries - TopCount)
				LocalUserPosition = MathLib::CeilingInteger(MathLib::ToReal(((MaxEntries+1) - TopCount) / 2)) + TopCount;

				// Are there enough other entries to fill the space between TopCount and LocalUser,
				// if not adjust LocalUserPosition to fit into
				declare Integer[] PlayersBeforeLocalUserPosition;
				CurrentRank = 0;
				foreach (Nickname => Score in SortedRanking) {
					if (Nickname != LocalUser.Name) {
						PlayersBeforeLocalUserPosition.add(CurrentRank);
					}
					else {
						break;
					}
					CurrentRank += 1;
				}
				if ((LocalUserRank+1) > (PlayersBeforeLocalUserPosition.count - TopCount)) {
					LocalUserPosition = PlayersBeforeLocalUserPosition.count;
				}
				RankingList[LocalUserPosition] = ["Rank" => TextLib::ToText(LocalUserRank+1), "Nickname" => LocalUser.Name, "Score" => FormatTime(LocalUserScore)];
			}
			else if ( ((LocalUserRank+1) == SortedRanking.count) && ((LocalUserRank+1) > TopCount) ) {
				// Add the LocalUser to the end of RankingList[]
				RankingList[MaxEntries] = ["Rank" => TextLib::ToText(LocalUserRank+1), "Nickname" => LocalUser.Name, "Score" => FormatTime(LocalUserScore)];
				LocalUserPosition = MaxEntries;
			}
			else {
				LocalUserPosition = LocalUserRank;
			}


			// Find the space between TopCount <-> LocalUser <-> MaxEntries
			declare Integer[] FreeSpotPosBeforeLocalUser;
			declare Integer[] FreeSpotPosAfterLocalUser;
			foreach (EntryId => Item in RankingList) {
				if (Item["Score"] == "NULL") {
					if (EntryId < LocalUserPosition) {
						FreeSpotPosBeforeLocalUser.add(EntryId);
					}
					else if (EntryId > LocalUserPosition) {
						FreeSpotPosAfterLocalUser.add(EntryId);
					}
				}
			}

			// Fill the space between TopCount and LocalUserPosition
			declare Integer Index = 0;
			if (FreeSpotPosBeforeLocalUser.count > 0) {
				for (Pos, ((LocalUserRank+1) - FreeSpotPosBeforeLocalUser.count), LocalUserRank) {
					CurrentRank = 0;
					foreach (Nickname => Score in SortedRanking) {
						if ((CurrentRank+1) == Pos) {
							RankingList[FreeSpotPosBeforeLocalUser[Index]] = ["Rank" => TextLib::ToText(CurrentRank+1), "Nickname" => Nickname, "Score" => FormatTime(Score)];
							break;
						}
						CurrentRank += 1;
					}
					Index += 1;
				}
			}

			// Fill the space between LocalUserPosition and MaxEntries
			Index = 0;
			if (FreeSpotPosAfterLocalUser.count > 0) {
				declare Integer StartPos = LocalUserRank;
				if ((LocalUserRank+1) <= TopCount) {
					StartPos = TopCount - 1;
				}
				for (Pos, (StartPos+2), (StartPos + 1 + FreeSpotPosAfterLocalUser.count)) {
					CurrentRank = 0;
					foreach (Nickname => Score in SortedRanking) {
						if ((CurrentRank+1) == Pos) {
							RankingList[FreeSpotPosAfterLocalUser[Index]] = ["Rank" => TextLib::ToText(CurrentRank+1), "Nickname" => Nickname, "Score" => FormatTime(Score)];
							break;
						}
						CurrentRank += 1;
					}
					Index += 1;
				}
			}

			// Remove all "NULL" entries
			foreach (EntryId => Item in RankingList) {
				if (Item["Score"] == "NULL") {
					RankingList.removekey(EntryId);
				}
			}


			// Build the list entries and display it
			declare Integer Pos = 0;
			LocalUserPosition = 0;
			foreach (EntryId => Item in RankingList) {
				declare LiveRankingsRank	<=> (Page.GetFirstChild("RecordsEyepieceLiveRankingsRank" ^ Pos) as CMlLabel);
				declare LiveRankingsScore	<=> (Page.GetFirstChild("RecordsEyepieceLiveRankingsScore" ^ Pos) as CMlLabel);
				declare LiveRankingsNickname	<=> (Page.GetFirstChild("RecordsEyepieceLiveRankingsNickname" ^ Pos) as CMlLabel);

				if (Item["Nickname"] == LocalUser.Name) {
					LiveRankingsMarker.RelativePosition.Y = -((LineHeight * Pos + MarkerOffset - 0.65) * 1.875);
					LiveRankingsMarker.Visible = True;

					if ((Pos+1) > TopCount) {
						LiveRankingsMarkerBG.Visible = True;
					}
					else {
						LiveRankingsMarkerBG.Visible = False;
					}

					if (Item["Score"] == FormatTime(0)) {
						LiveRankingsRank.SetText("--.");
					}
					else {
						LiveRankingsRank.SetText(Item["Rank"] ^ ".");
					}
					LiveRankingsScore.SetText(ColorSelf ^ Item["Score"]);
					LiveRankingsNickname.SetText(Item["Nickname"]);

					LocalUserPosition = Pos;
				}
				else {
					declare Text HighlightColor = ColorWorse;
					if ((Pos+1) <= TopCount) {
						HighlightColor = ColorTop;
					}
					else if ( (TextLib::ToInteger(Item["Rank"]) < (LocalUserRank+1)) || (LocalUserRank == 0) ) {
						HighlightColor = ColorBetter;
					}

					LiveRankingsRank.SetText(Item["Rank"] ^ ".");
					LiveRankingsScore.SetText(HighlightColor ^ Item["Score"]);
					LiveRankingsNickname.SetText(Item["Nickname"]);
				}

				Pos += 1;
				if (Pos > MaxEntries) {
					break;
				}
			}
			if (LocalUserPosition > MaxEntries) {
				LiveRankingsMarker.Visible = False;
			}
			UpdateWidget = False;

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= $this->templates['RECORD_WIDGETS']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLiveRankingsWidget ($login, $preset, $gamemode, $limit = 50) {
		global $aseco;

		// Set the Placeholder for "No Score"
		if ( ($gamemode == Gameinfo::ROUNDS) && ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['DISPLAY_TYPE'][0] == false) ) {
			// Only set this if 'score' are to display, if 'time' use the default
			if ( isset($aseco->server->gameinfo->rounds['PointsLimit']) ) {
				$placeholder = str_replace(
					array(
						'{score}',
						'{remaining}',
						'{pointlimit}'
					),
					array(
						0,
						$aseco->server->gameinfo->rounds['PointsLimit'],
						$aseco->server->gameinfo->rounds['PointsLimit']
					),
					$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['FORMAT'][0]
				);
			}
			else {
				$placeholder = 0;
			}
		}
		else if ( ($gamemode == Gameinfo::LAPS) && ($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['DISPLAY_TYPE'][0] == false) ) {
			// Only set this if 'checkpoints' are to display, if 'time' use the default
			if (isset($this->cache['Map']['NbCheckpoints']) && isset($this->cache['Map']['NbLaps'])) {
				$placeholder = '0/'. ($this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps']);
			}
			else {
				$placeholder = '0 cps.';
			}
		}
		else {
			// All other set the default
			$placeholder = $this->config['PlaceholderNoScore'];
		}

		// Set the Topcount
		$topcount = $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Add Widget header
		$xml = $this->cache['LiveRankings'][$gamemode]['WidgetHeader'];

		// Build the entries if already loaded
		if ( count($this->scores['LiveRankings']) > 0 ) {

			if ( ($this->config['States']['NiceMode'] == false) && ($gamemode != Gameinfo::TEAM) ) {
				// Build the "CloseToYou" Array, but not in 'Team' and NiceMode
				$records = $this->buildCloseToYouArray($this->scores['LiveRankings'], $preset, $limit, $topcount);
			}
			else if ($gamemode == Gameinfo::TEAM) {
				// Need to handle 'Team' other then all other Gamemodes
				$records = $this->scores['LiveRankings'];
				$records[0]['self'] = 0;
				$records[0]['rank'] = false;
				$records[1]['self'] = 0;
				$records[1]['rank'] = false;
			}
			else {
				$records = $this->scores['LiveRankings'];
			}


			// Now check if it is required to build this Manialink (only required in normal mode, nice mode send always)
			if ($this->config['States']['NiceMode'] == false) {
				$digest = $this->buildCloseToYouDigest($records);
				if ($this->cache['PlayerStates'][$login]['LiveRankings'] != false) {
					if ($this->cache['PlayerStates'][$login]['LiveRankings'] != $digest) {

						// Widget is different as before, store them and build the new Widget
						$this->cache['PlayerStates'][$login]['LiveRankings'] = $digest;
					}
					else {
						// Widget is unchanged, no need to send now
						return;
					}
				}
				else {
					// Widget is build first time for this Player, store them and build the new Widget
					$this->cache['PlayerStates'][$login]['LiveRankings'] = $digest;
				}
			}


			// Create the Widget entries
			$line = 0;
			foreach ($records as $item) {
				// No markers of connected Players with a record in LiveRankings,
				// that overload the Widget with marker, because (maybe) all Players are online right now.

				// Build record entries
				$xml .= $this->getCloseToYouEntry($item, $line, $topcount, $placeholder, $this->config['LIVE_RANKINGS'][0]['WIDTH'][0]);

				$line ++;

				if ($line >= $limit) {
					break;
				}
			}
			unset($item);

		}
		else if ($login != false) {
			// Create an empty entry
			$xml .= $this->getCloseToYouEntry($preset, 0, $topcount, $placeholder, $this->config['LIVE_RANKINGS'][0]['WIDTH'][0]);
		}

		// Add Widget footer
		$xml .= $this->cache['LiveRankings'][$gamemode]['WidgetFooter'];

		// Send the Widget now
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLiveRankingsWidgetBody ($gamemode) {

		// Set the right Icon and Title position
		$position = (($this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0] < 0) ? 'right' : 'left');

		// Set the Topcount
		$topcount = $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['TOPCOUNT'][0];

		// Calculate the widget height (+ 3.2 for title)
		$widget_height = ($this->config['LineHeight'] * $this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['ENTRIES'][0] + 3.2);

		if ($position == 'right') {
			$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$imagex	= $this->config['Positions'][$position]['image_open']['x'];
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		$build['header'] = str_replace(
			array(
				'%manialinkid%',
				'%actionid%',
				'%posx%',
				'%posy%',
				'%image_open_pos_x%',
				'%image_open_pos_y%',
				'%image_open%',
				'%posx_icon%',
				'%posy_icon%',
				'%icon_style%',
				'%icon_substyle%',
				'%halign%',
				'%posx_title%',
				'%posy_title%',
				'%backgroundwidth%',
				'%backgroundheight%',
				'%borderwidth%',
				'%borderheight%',
				'%widgetwidth%',
				'%widgetheight%',
				'%column_width_name%',
				'%column_height%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'LiveRankingsWidget',
				'showLiveRankingsWindow',
				$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_X'][0],
				$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['POS_Y'][0],
				$imagex,
				-($widget_height - 3.18),
				$this->config['Positions'][$position]['image_open']['image'],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$this->config['LIVE_RANKINGS'][0]['ICON_STYLE'][0],
				$this->config['LIVE_RANKINGS'][0]['ICON_SUBSTYLE'][0],
				$this->config['Positions'][$position]['title']['halign'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.2),
				($widget_height - 0.2),
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] + 0.4),
				($widget_height + 0.6),
				$this->config['LIVE_RANKINGS'][0]['WIDTH'][0],
				$widget_height,
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 6.45),
				($widget_height - 3.1),
				($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8),
				$this->config['LIVE_RANKINGS'][0]['TITLE'][0]
			),
			$this->templates['RECORD_WIDGETS']['HEADER']
		);

		// Add Background for top X Players
		if ($topcount > 0) {
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] != '') {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] .'"/>';
			}
			else {
				$build['header'] .= '<quad posn="0.4 -2.7 0.004" sizen="'. ($this->config['LIVE_RANKINGS'][0]['WIDTH'][0] - 0.8) .' '. ($topcount * $this->config['LineHeight']) .'" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_SUBSTYLE'][0] .'"/>';
			}
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<live_rankings>
 * License:	GPLv3
 * ----------------------------------
 */
main() {
	declare persistent Boolean RecordsEyepieceLiveRankingsVisible = True;
	declare LiveRankingsWidget		<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	LiveRankingsWidget.RelativeScale	= {$this->config['LIVE_RANKINGS'][0]['GAMEMODE'][0][$gamemode][0]['SCALE'][0]};

	LiveRankingsWidget.Visible = RecordsEyepieceLiveRankingsVisible;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for pressed F7 to hide the Widget
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "F7") {
						if (LiveRankingsWidget.Visible == False) {
							RecordsEyepieceLiveRankingsVisible = True;
						}
						else {
							RecordsEyepieceLiveRankingsVisible = False;
						}
						LiveRankingsWidget.Visible = RecordsEyepieceLiveRankingsVisible;
					}
				}
			}
		}
	}
}
--></script>
EOL;
		$build['footer'] = $maniascript . $this->templates['RECORD_WIDGETS']['FOOTER'];

		return $build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildRoundScoreWidget ($gamemode, $send_direct = true) {
		global $aseco;

		if (count($this->scores['RoundScore']) > 0) {

			// Add Widget header
			$xml = $this->cache['RoundScore'][$gamemode]['Race']['WidgetHeader'];

			// Set the right Icon and Title position
			$position = (($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['RACE'][0]['POS_X'][0] < 0) ? 'right' : 'left');

			// Adjust the Points to the connected Player count
			if ( ($gamemode == Gameinfo::TEAM) && ($aseco->server->gameinfo->team['UseAlternateRules'] == true) ) {

				// Get Playercount
				$limit = count($aseco->server->players->player_list);
				if ($limit > $aseco->server->gameinfo->team['MaxPointsPerRound']) {
					$limit = $aseco->server->gameinfo->team['MaxPointsPerRound'];
				}

				// Build new Points array
				$rpoints = array();
				$i = 0;
				for ($pts = 0; $pts <= $limit; $pts++) {
					$rpoints[] = $limit - $i;
					$i++;
				}
			}
			else if ($gamemode != Gameinfo::LAPS) {
				$rpoints = $this->config['RoundScore']['Points'][$gamemode];
			}



			// BEGIN: Sort the times
			$round_score = array();

			if ($gamemode == Gameinfo::LAPS) {
				$cps = array();
				$scores = array();
				$pids = array();
				foreach ($this->scores['RoundScore'] as $key => $row) {
					$cps[$key]	= $row['checkpointid'];
					$scores[$key]	= $row['scoplain'];
					$pids[$key]	= $row['playerid'];
				}
				unset($key, $row);

				// Sort order: CHECKPOINTID, SCORE and PID
				array_multisort($cps, SORT_NUMERIC, SORT_DESC, $scores, SORT_NUMERIC, $pids, SORT_NUMERIC, $this->scores['RoundScore']);
				unset($cps, $scores, $pids);

				foreach ($this->scores['RoundScore'] as $item) {
					// Merge the score arrays together
					$round_score[] = $item;
				}
				unset($item);
			}
			else {
				// Sort all the Scores, look for equal times and sort them with the
				// personal best from this whole round and pid where required
				ksort($this->scores['RoundScore']);
				foreach ($this->scores['RoundScore'] as $item) {

					// Sort only times which was more then once driven
					if (count($item) > 1) {
						$scores = array();
						$pbs = array();
						$pids = array();
						foreach ($item as $key => $row) {
							$scores[$key]	= $row['scoplain'];
							$pbs[$key]  	= $this->scores['RoundScorePB'][$row['login']];
							$pids[$key]	= $row['playerid'];
						}
						// Sort order: SCORE, PB and PID, like the same way the dedicated server does
						array_multisort($scores, SORT_NUMERIC, $pbs, SORT_NUMERIC, $pids, SORT_NUMERIC, $item);
						unset($scores, $pbs, $pids, $row);
					}
					// Merge the score arrays together
					$round_score = array_merge($round_score, $item);
				}
				unset($item, $row);
			}
			// END: Sort the times


//	$aseco->dump('RoundScore', $this->config['RoundScore']['Points'], $round_score);

			$line = 0;
			$offset = 3;
			$team_break = false;
			foreach ($round_score as $item) {

				// Adjust Team points
				if ($gamemode == Gameinfo::TEAM) {
					if ($aseco->server->gameinfo->team['UseAlternateRules'] == false) {
						if ($team_break == true) {
							$points = '0';
						}
						else if ($round_score[0]['team'] != $item['team']) {
							$points = '0';
							$team_break = true;
						}
						else {
							$points = ((isset($rpoints[$line])) ? $rpoints[$line] : end($rpoints));
						}
					}
					else {
						$points = ((isset($rpoints[$line])) ? $rpoints[$line] : end($rpoints));
					}
				}
				else if ($gamemode != Gameinfo::LAPS) {
					// All other Gamemodes except 'Laps'
					$points = ((isset($rpoints[$line])) ? $rpoints[$line] : end($rpoints));
				}

				// Switch Color of Topcount
				if (($line+1) <= $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['RACE'][0]['TOPCOUNT'][0]) {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['TOP'][0];
				}
				else if (($line+1) > $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['RACE'][0]['TOPCOUNT'][0]) {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['WORSE'][0];
				}

				if ($position == 'left') {
					if ($gamemode == Gameinfo::TEAM) {
						$xml .= '<quad posn="-3.9 -'. ($this->config['LineHeight'] * $line + $offset - 0.14) .' 0.004" sizen="3.4 1.68" bgcolor="'. (($item['team'] == 0) ? '03DF' : 'D30F') .'"/>';
						$xml .= '<label posn="-0.6 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="3 2" halign="right" scale="0.9" textcolor="FFFF" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $points .'"/>';
					}
					else if ($gamemode == Gameinfo::LAPS) {
						if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_BACKGROUND'][0] != '') {
							$xml .= '<quad posn="-7.1 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="7 1.9" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_BACKGROUND'][0] .'"/>';
						}
						else {
							$xml .= '<quad posn="-7.1 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="7 1.9" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_SUBSTYLE'][0] .'"/>';
						}
						$xml .= '<label posn="-2.4 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="4.8 2" halign="right" scale="0.9" textcolor="'. (($item['checkpointid'] < $round_score[0]['checkpointid']) ? 'D02F' : '0D3F').'" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $aseco->formatTime(abs($item['scoplain'] - $round_score[0]['scoplain'])) .'"/>';
						$xml .= '<label posn="-0.4 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="1.3 2" halign="right" scale="0.9" textcolor="'. (($item['checkpointid'] < $round_score[0]['checkpointid']) ? 'D02F' : '0D3F').'" text="$O'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . ($item['checkpointid']+1) .'"/>';
					}
					else {
						// Gameinfo::ROUNDS or Gameinfo::CUP
						if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_BACKGROUND'][0] != '') {
							$xml .= '<quad posn="-4.1 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="4 1.9" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_BACKGROUND'][0] .'"/>';
						}
						else {
							$xml .= '<quad posn="-4.1 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="4 1.9" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_COMMON_SUBSTYLE'][0] .'"/>';
						}
						$xml .= '<label posn="-0.6 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="3 2" halign="right" scale="0.9" textcolor="0D3F" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $points .'"/>';
					}
				}
				else {
					if ($gamemode == Gameinfo::TEAM) {
						$xml .= '<quad posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 0.5) .' -'. ($this->config['LineHeight'] * $line + $offset - 0.14) .' 0.004" sizen="3.4 1.68" bgcolor="'. (($item['team'] == 0) ? '03DF' : 'D30F') .'"/>';
						$xml .= '<label posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 3.6) .' -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="3 2" halign="right" scale="0.9" textcolor="FFFF" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $points .'"/>';
					}
					else if ($gamemode == Gameinfo::LAPS) {
						$xml .= '<quad posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 0.1) .' -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="7 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
						$xml .= '<label posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 4.6) .' -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="4.8 2" halign="right" scale="0.9" textcolor="'. (($item['checkpointid'] < $round_score[0]['checkpointid']) ? 'D02F' : '0D3F').'" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $aseco->formatTime(abs($item['scoplain'] - $round_score[0]['scoplain'])) .'"/>';
						$xml .= '<label posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 7) .' -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="1.3 2" halign="right" scale="0.9" textcolor="'. (($item['checkpointid'] < $round_score[0]['checkpointid']) ? 'D02F' : '0D3F').'" text="$O'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . ($item['checkpointid']+1) .'"/>';
					}
					else {
						// Gameinfo::ROUNDS or Gameinfo::CUP
						$xml .= '<quad posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 0.1) .' -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.003" sizen="4 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
						$xml .= '<label posn="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 3.6) .' -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="3 2" halign="right" scale="0.9" textcolor="0D3F" text="$O+'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $points .'"/>';
					}
				}

				$xml .= '<label posn="2.3 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="1.7 1.7" halign="right" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . ($line+1) .'."/>';
				$xml .= '<label posn="5.9 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['score'] .'"/>';
				$xml .= '<label posn="6.1 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.004" sizen="'. sprintf("%.02f", ($this->config['ROUND_SCORE'][0]['WIDTH'][0] / 100 * 62.58)) .' 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['nickname'] .'"/>';

				$line ++;

				if ($line >= $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0]['RACE'][0]['ENTRIES'][0]) {
					break;
				}
			}
			unset($item);

			// Add Widget footer
			$xml .= $this->cache['RoundScore'][$gamemode]['Race']['WidgetFooter'];
		}
		else if ($this->config['States']['RoundScore']['WarmUpPhase'] == true) {
			// Add Widget header
			$xml = $this->cache['RoundScore'][$gamemode]['WarmUp']['WidgetHeader'];

			// WarmUp note
			$xml .= '<label posn="2.3 -3.2 0.004" sizen="'. sprintf("%.02f", ($this->config['ROUND_SCORE'][0]['WIDTH'][0] / 100 * 62.58 + 5.5)) .' 1.7" scale="0.9" autonewline="1" textcolor="FA0F" text="No Score during'. LF .'Warm-Up!"/>';

			// Add Widget footer
			$xml .= $this->cache['RoundScore'][$gamemode]['WarmUp']['WidgetFooter'];
		}
		else {
			// Add Widget header
			$xml = $this->cache['RoundScore'][$gamemode]['Race']['WidgetHeader'];

			// Empty entry
			$xml .= '<label posn="2.3 -3 0.004" sizen="1.7 1.7" halign="right" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] .'--."/>';
			$xml .= '<label posn="5.9 -3 0.004" sizen="3.8 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['TOP'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] .'-:--.---"/>';
			$xml .= '<label posn="6.1 -3 0.004" sizen="'. sprintf("%.02f", ($this->config['ROUND_SCORE'][0]['WIDTH'][0] / 100 * 62.58)) .' 1.7" scale="0.9" textcolor="FA0F" text=" Free For You!"/>';

			// Add Widget footer
			$xml .= $this->cache['RoundScore'][$gamemode]['Race']['WidgetFooter'];
		}

		if ($send_direct == true) {
			// Send the Widget now to all Player
			$this->sendManialink($xml, false, 0);
		}
		else {
			return $xml;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildRoundScoreWidgetBody ($gamemode, $operation) {

		// Set the right Icon and Title position
		$position = (($this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['POS_X'][0] < 0) ? 'right' : 'left');

		// Set the Topcount
		$topcount = $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['TOPCOUNT'][0];

		// Calculate the widget height (+ 3.2 for title)
		$widget_height = ($this->config['LineHeight'] * $this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['ENTRIES'][0] + 3.2);

		if ($position == 'right') {
			$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 15.5));
			$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 15.5));
		}
		else {
			$iconx	= $this->config['Positions'][$position]['icon']['x'];
			$titlex	= $this->config['Positions'][$position]['title']['x'];
		}

		$build['header'] = str_replace(
			array(
				'%manialinkid%',
				'%posx%',
				'%posy%',
				'%widgetscale%',
				'%posx_icon%',
				'%posy_icon%',
				'%icon_style%',
				'%icon_substyle%',
				'%halign%',
				'%posx_title%',
				'%posy_title%',
				'%borderwidth%',
				'%borderheight%',
				'%widgetwidth%',
				'%widgetheight%',
				'%column_width_name%',
				'%column_height%',
				'%title_background_width%',
				'%title%'
			),
			array(
				'RoundScoreWidget',
				$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['POS_X'][0],
				$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['POS_Y'][0],
				$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['SCALE'][0],
				$iconx,
				$this->config['Positions'][$position]['icon']['y'],
				$this->config['ROUND_SCORE'][0][$operation][0]['ICON_STYLE'][0],
				$this->config['ROUND_SCORE'][0][$operation][0]['ICON_SUBSTYLE'][0],
				$this->config['Positions'][$position]['title']['halign'],
				$titlex,
				$this->config['Positions'][$position]['title']['y'],
				($this->config['ROUND_SCORE'][0]['WIDTH'][0] + 0.4),
				($widget_height + 0.6),
				$this->config['ROUND_SCORE'][0]['WIDTH'][0],
				$widget_height,
				($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 6.45),
				($widget_height - 3.1),
				($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 0.8),
				$this->config['ROUND_SCORE'][0]['TITLE'][0]
			),
			$this->templates['ROUNDSCOWIDGET']['HEADER']
		);

		// Add Background for top X Players
		if ($topcount > 0) {
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] != '') {
				$build['header'] .= '<quad posn="0.4 -2.6 0.004" sizen="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 0.8) .' '. (($topcount * $this->config['LineHeight']) + 0.3) .'" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_BACKGROUND'][0] .'"/>';
			}
			else {
				$build['header'] .= '<quad posn="0.4 -2.6 0.004" sizen="'. ($this->config['ROUND_SCORE'][0]['WIDTH'][0] - 0.8) .' '. (($topcount * $this->config['LineHeight']) + 0.3) .'" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TOP_SUBSTYLE'][0] .'"/>';
			}
		}

		$build['footer'] = str_replace(
			'%widgetscale%',
			$this->config['ROUND_SCORE'][0]['GAMEMODE'][0][$gamemode][0][$operation][0]['SCALE'][0],
			$this->templates['ROUNDSCOWIDGET']['FOOTER']
		);

		return $build;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Fully stolen from plugins.fufi.widgets.php (thanks fufi) and changed to my needs
	public function buildCloseToYouArray ($records, $preset, $ctuCount, $topCount) {

		// Set login to compare later
		$login = $preset['login'];

		// Init arrays
		$result = array_fill(0, $ctuCount, null);
		$better = array();
		$worse = array();
		$self = null;
		$isbetter = true;

		// Constructs arrays with records of better and worse players than the specified player
		for ($i=0; $i<count($records); $i++) {

			// When Player leaves, then (in some situation) this could be incomplete, just ignore in this case
			if ( !isset($records[$i]) ) {
				continue;
			}

			$entry = $records[$i];
			$entry['rank'] = $i + 1;
			if ($isbetter) {
				if ($records[$i]['login'] == $login) {
					$self = $entry;
					$isbetter = false;
				}
				else {
					$better[] = $entry;
				}
			}
			else {
				$worse[] = $entry;
			}
		}

		// Do the top x stuff
		$arrayTop = array();
		if ( count($better) > $topCount){
			for ($i=0; $i<$topCount; $i++) {
				$arrayTop[$i] = array();
				$arrayTop[$i] = array_shift($better);
				$arrayTop[$i]['self'] = -1;
			}
			$ctuCount -= $topCount;
		}

		// Go through the possibile scenarios and choose the right one (wow, what an explanation^^)
		if (!$self) {
			$lastIdx = $ctuCount - 1;
			$result[$lastIdx] = array();
			$result[$lastIdx]['rank'] = $preset['rank'];
			$result[$lastIdx]['login'] = $preset['login'];
			$result[$lastIdx]['nickname'] = $preset['nickname'];
			$result[$lastIdx]['score'] = $this->config['PlaceholderNoScore'];		// Changed onBeginMap at related Gamemode (e.g. 'Stunts')
			$result[$lastIdx]['self'] = 0;
			for ($i=count($better)-1; $i>=0; $i--) {
				if (--$lastIdx >= 0) {
					$result[$lastIdx] = $better[$i];
					$result[$lastIdx]['self'] = -1;
				}
			}
		}
		else {
			$hasbetter = true;
			$hasworse = true;
			$resultNew = array();

			$resultNew[0] = $self;
			$resultNew[0]['self'] = 0;

			$idx = 0;

			while ( (count($resultNew) < $ctuCount) && (($hasbetter) || ($hasworse)) ) {

				if ( ($hasbetter) && (count($better) >= ($idx+1)) ) {

					// Push one record before
					$rec = $better[count($better) - 1 - $idx];
					$rec['self'] = -1;
					$help = array();
					$help[0] = $rec;
					for ($i=0; $i<count($resultNew); $i++) {
						$help[$i+1] = $resultNew[$i];
					}
					$resultNew = $help;
				}
				else {
					$hasbetter = false;
				}
				if ( count($resultNew) < ($ctuCount) ) {
					if ( ($hasworse) && (count($worse) >= ($idx+1)) ) {

						// Push one record behind
						$rec = $worse[$idx];
						$rec['self'] = 1;
						$resultNew[] = $rec;
					}
					else {
						$hasworse = false;
					}
				}
				$idx ++;
			}
			$result = $resultNew;
		}
		$result = array_merge($arrayTop, $result);

		$resultNew = array();
		$count = 0;
		for ($i=0; $i<count($result); $i++) {
			if ($result[$i] != null){
				$resultNew[] = $result[$i];
				$count ++;
			}
		}
		$result = $resultNew;

		return $result;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Fully stolen from plugins.fufi.widgets.php (thanks fufi)
	public function buildCloseToYouDigest ($array) {


		$result = '';
		for ($i=0; $i<count($array); $i++) {

			// When Player leaves, then (in some situation) this could be incomplete, just ignore in this case
			if ( !isset($array[$i]) ) {
				continue;
			}

			$result .= $array[$i]['rank'];
			$result .= $array[$i]['login'];
			$result .= $array[$i]['nickname'];
			$result .= $array[$i]['score'];
		}
		return md5($result);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $list = 'locals'
	public function buildRecordDigest ($list, $array) {

		if ($list == 'locals') {
			$result = '';
			foreach ($array as $entry) {
				$result .= $entry->player->login;
				$result .= $entry->player->nickname;
				$result .= $entry->score;
			}
			unset($entry);
			return md5($result);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCloseToYouEntry ($item, $line, $topcount, $noscore, $widgetwidth) {

		// Set offset for calculation the line-heights
		$offset = 3;

		// Set default Text color
		$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0];

		// Do not build the Player related highlites if in NiceMode!
		$xml = '';
		if ($this->config['States']['NiceMode'] == false) {
			if ($item['self'] == -1) {
				if ($item['rank'] < ($topcount+1)) {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['TOP'][0];
				}
				else {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BETTER'][0];
				}
			}
			else if ($item['self'] == 1) {
				if ($item['rank'] < ($topcount+1)) {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['TOP'][0];
				}
				else {
					$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['WORSE'][0];
				}
			}
			else {
				$textcolor = $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SELF'][0];

				// Add a background for this Player with an record here
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="0.4 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.005" sizen="'. ($widgetwidth - 0.8) .' 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="0.4 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.005" sizen="'. ($widgetwidth - 0.8) .' 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
				}
				if ($item['rank'] != false) {
					// $item['rank'] is set 'false' in Team to skip the highlite here in $this->buildLiveRankingsWidget()
					if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
						$xml .= '<quad posn="-2 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.005" sizen="2 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
						$xml .= '<quad posn="'. $widgetwidth .' -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.004" sizen="2 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
					}
					else {
						$xml .= '<quad posn="-2 -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.005" sizen="2 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
						$xml .= '<quad posn="'. $widgetwidth .' -'. ($this->config['LineHeight'] * $line + $offset - 0.3) .' 0.004" sizen="2 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
					}
					$xml .= '<quad posn="-1.8 -'. ($this->config['LineHeight'] * $line + $offset - 0.2) .' 0.006" sizen="1.6 1.6" style="Icons64x64_1" substyle="ShowRight2"/>';
					$xml .= '<quad posn="'. ($widgetwidth + 0.2) .' -'. ($this->config['LineHeight'] * $line + $offset - 0.2) .' 0.006" sizen="1.6 1.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
				}
			}
		}

		if ($item['rank'] != false) {
			if ( ($this->config['States']['NiceMode'] == true) && ($item['rank'] <= $topcount) ) {
				$textcolor = $this->config['NICEMODE'][0]['COLORS'][0]['TOP'][0];
			}
			else if ( ($this->config['States']['NiceMode'] == true) && ($item['rank'] > $topcount) ) {
				$textcolor = $this->config['NICEMODE'][0]['COLORS'][0]['WORSE'][0];
			}

			$xml .= '<label posn="2.3 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="1.7 1.7" halign="right" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['rank'] .'."/>';
			$xml .= '<label posn="6 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="3.9 1.7" halign="right" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . ( (isset($item['score'])) ? $item['score'] : $noscore) .'"/>';
		}
		else {
			// In Team nobody has a rank
			$xml .= '<label posn="6 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="5.4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . ( (isset($item['score'])) ? $item['score'] : $noscore) .'"/>';
		}
		$xml .= '<label posn="6.2 -'. ($this->config['LineHeight'] * $line + $offset) .' 0.005" sizen="'. sprintf("%.02f", ($widgetwidth - 6)) .' 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['nickname'] .'"/>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getConnectedPlayerRecord ($login, $line, $topcount, $behind_rank = false, $widgetwidth) {
		global $aseco;

		// Is the given Player currently online? If true, mark her/his Record at other Players.
		if ( isset($aseco->server->players->player_list[$login]) ) {
			$xml = '';

			// Add a background for this Player with an record here
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
				$xml .= '<quad posn="0.4 -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="'. ($widgetwidth - 0.8) .' 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
			}
			else {
				$xml .= '<quad posn="0.4 -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="'. ($widgetwidth - 0.8) .' 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
			}

			// Add a marker for Player with an record here (left and right from the Widget)
			if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
				$xml .= '<quad posn="-2 -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="2 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
				$xml .= '<quad posn="'. $widgetwidth .' -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="2 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
			}
			else {
				$xml .= '<quad posn="-2 -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="2 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
				$xml .= '<quad posn="'. $widgetwidth .' -'. ($this->config['LineHeight'] * $line + 2.7) .' 0.005" sizen="2 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
			}
			if ($behind_rank == true) {
				$marker = array('style' => 'Icons64x64_1', 'substyle' => 'NotBuddy');
			}
			else {
				$marker = array('style' => 'Icons64x64_1', 'substyle' => 'Buddy');
			}
			$xml .= '<quad posn="-1.7 -'. ($this->config['LineHeight'] * $line + 2.9) .' 0.006" sizen="1.4 1.4" style="'. $marker['style'] .'" substyle="'. $marker['substyle'] .'"/>';
			$xml .= '<quad posn="'. ($widgetwidth + 0.3) .' -'. ($this->config['LineHeight'] * $line + 2.9) .' 0.006" sizen="1.4 1.4" style="'. $marker['style'] .'" substyle="'. $marker['substyle'] .'"/>';

			return $xml;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildDedimaniaRecordsWindow ($login) {
		global $aseco;

		$buttons = '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';
		$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				$this->config['DEDIMANIA_RECORDS'][0]['ICON_STYLE'][0],
				$this->config['DEDIMANIA_RECORDS'][0]['ICON_SUBSTYLE'][0],
				$this->config['DEDIMANIA_RECORDS'][0]['TITLE'][0],
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		// Build Link to the Map at dedimania.net
		$dedimode = '';
		$gamemode = $aseco->server->gameinfo->mode;
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) || ($gamemode == Gameinfo::CUP) ) {
			$dedimode = '&amp;Mode=M1';
		}
		else if ( ($gamemode == Gameinfo::TIMEATTACK) || ($gamemode == Gameinfo::LAPS) ) {
			$dedimode = '&amp;Mode=M2';
		}
		$xml .= '<frame posn="28.6 -54.5 0.04">';
		$xml .= '<label posn="12 0 0.02" sizen="30 2.6" halign="center" textsize="1" scale="0.8" url="http://dedimania.net/tm2stats/?do=stat'. $dedimode .'&amp;&RecOrder3=RANK-ASC&amp;UId='. $this->cache['Map']['Current']['uid'] .'&amp;Show=RECORDS" text="MORE INFO ON DEDIMANIA.NET" style="CardButtonMediumWide"/>';
		$xml .= '</frame>';

		$xml .= '<frame posn="3.2 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';


		// Add all connected PlayerLogins
		$players = array();
		foreach ($aseco->server->players->player_list as $player) {
			$players[] = $player->login;
		}
		unset($player);


		$rank = 1;
		$line = 0;
		$offset = 0;
		foreach ($this->scores['DedimaniaRecords'] as $item) {
			// Mark current connected Players
			if ($item['login'] == $login) {
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
				}
			}
			else if ( in_array($item['login'], $players) ) {
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
				}
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="'. $rank .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $item['score'] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="'. $item['nickname'] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		unset($item);
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLocalRecordsWindow ($page, $login) {
		global $aseco;

		// Get the total of records
		$totalrecs = count($this->scores['LocalRecords']);

		// Determind the maxpages
		$maxpages = ceil($totalrecs / 100);
		if ($page > $maxpages) {
			$page = $maxpages - 1;
		}

		// Frame for Previous-/Next-Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="3.45 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';

		// Previous button
		if ($page > 0) {
			// First
			$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="7.34 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if (($page + 1) < $maxpages) {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';


		// Create Windowtitle
		if (count($this->scores['LocalRecords']) == 0) {
			$title = $this->config['LOCAL_RECORDS'][0]['TITLE'][0];
		}
		else {
			$title = $this->config['LOCAL_RECORDS'][0]['TITLE'][0] .'   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalrecs, 0) . (($totalrecs == 1) ? ' Record' : ' Records');
		}

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				$this->config['LOCAL_RECORDS'][0]['ICON_STYLE'][0],
				$this->config['LOCAL_RECORDS'][0]['ICON_SUBSTYLE'][0],
				$title,
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="3.2 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';


		// Add all connected PlayerLogins
		$players = array();
		foreach ($aseco->server->players->player_list as $player) {
			$players[] = $player->login;
		}


		$entries = 0;
		$line = 0;
		$offset = 0;
		for ($i = ($page * 100); $i < (($page * 100) + 100); $i ++) {

			// Is there a record?
			if ( !isset($this->scores['LocalRecords'][$i]) ) {
				break;
			}

			$item = $this->scores['LocalRecords'][$i];

			// Mark current connected Players
			if ($item['login'] == $login) {
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
				}
			}
			else if ( in_array($item['login'], $players) ) {
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
				}
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="'. $item['rank'] .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $item['score'] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="'. $item['nickname'] .'"/>';

			$line ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}
		}
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return array(
			'xml'		=> $xml,
			'maxpage'	=> ($maxpages - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLiveRankingsWindow ($page, $login) {
		global $aseco;

		// Get the total entries
		$totalentries = count($this->scores['LiveRankings']);

		// Determind the maxpages
		$maxpages = ceil($totalentries / 100);
		if ($page > $maxpages) {
			$page = $maxpages - 1;
		}

		// Frame for Previous/Next Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';

		// Previous button
		if ($page > 0) {
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="20.15 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if ( ($page < 3) && ($totalentries > 100) && (($page + 1) < $maxpages) ) {
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.45 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
		}
		else {
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		$buttons .= '</frame>';


		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				$this->config['LIVE_RANKINGS'][0]['ICON_STYLE'][0],
				$this->config['LIVE_RANKINGS'][0]['ICON_SUBSTYLE'][0],
				$this->config['LIVE_RANKINGS'][0]['TITLE'][0],
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="3.2 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

		$line = 0;
		$offset = 0;
		for ($i = ($page * 100); $i < (($page * 100) + 100); $i ++) {

			// Is there a rank?
			if ( !isset($this->scores['LiveRankings'][$i]) ) {
				break;
			}

			$item = $this->scores['LiveRankings'][$i];

			// Mark current Player
			if ($item['login'] == $login) {
				if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
				}
				else {
					$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
				}
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.03" sizen="2 1.7" halign="right" scale="0.9" text="'. $item['rank'] .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.03" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $item['score'] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.03" sizen="11.2 1.7" scale="0.9" text="'. $item['nickname'] .'"/>';

			$line ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}
		}
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return array(
			'xml'		=> $xml,
			'maxpage'	=> ($maxpages - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildScorelistWindowEntry ($page, $target, $widget, $fieldnames) {
		global $aseco;

		// Setup the listname from given $widget, e.g. 'TOP_RANKINGS' to 'TopRankings'
		$list = str_replace(' ', '', ucwords(implode(' ', explode('_', strtolower($widget)))));

		// Get the total of records
		$totalentries = count($this->scores[$list]);

		// Determind the maxpages
		$maxpages = ceil($totalentries / 100);
		if ($page > $maxpages) {
			$page = $maxpages - 1;
		}

		// Frame for Previous-/Next-Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="3.45 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';

		// Previous button
		if ($page > 0) {
			// First
			$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="7.34 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if (($page + 1) < $maxpages) {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';


		// Create Windowtitle
		if (count($this->scores[$list]) == 0) {
			$title = $this->config['SCORETABLE_LISTS'][0][$widget][0]['TITLE'][0];
		}
		else {
			$title = $this->config['SCORETABLE_LISTS'][0][$widget][0]['TITLE'][0] .'   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalentries, 0) . (($totalentries == 1) ? ' Entry' : ' Entries');
		}

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				$this->config['SCORETABLE_LISTS'][0][$widget][0]['ICON_STYLE'][0],
				$this->config['SCORETABLE_LISTS'][0][$widget][0]['ICON_SUBSTYLE'][0],
				$title,
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		// Add all connected PlayerLogins
		$logins = array();
		foreach ($aseco->server->players->player_list as $player) {
			if ($player->login != $target) {
				$logins[] = $player->login;
			}
		}

		$xml .= '<frame posn="3.2 -6.5 1">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

		$entries = 0;
		$rank = 1;
		$line = 0;
		$offset = 0;
		for ($i = ($page * 100); $i < (($page * 100) + 100); $i ++) {

			// Is there a record?
			if ( !isset($this->scores[$list][$i]) ) {
				break;
			}

			$item = $this->scores[$list][$i];

			// Mark current connected Players
			if (isset($item['login'])) {
				if ($item['login'] == $target) {
					if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
						$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
					}
					else {
						$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
					}
				}
				else if (in_array($item['login'], $logins)) {
					if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
						$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
					}
					else {
						$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
					}
				}
			}
			$xml .= '<label posn="'. (2.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="2 1.7" halign="right" scale="0.9" text="'. $item['rank'] .'."/>';
			$xml .= '<label posn="'. (6.4 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $item[$fieldnames[0]] .'"/>';
			$xml .= '<label posn="'. (6.9 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="11.2 1.7" scale="0.9" text="'. $item[$fieldnames[1]] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}
		}
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return array(
			'xml'		=> $xml,
			'maxpage'	=> ($maxpages - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildLastCurrentNextMapWindow () {
		global $aseco;

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'Browse',
				'Map playlist overview',
				''
			),
			$this->templates['WINDOW']['HEADER']
		);


		$xml .= '<frame posn="3.2 -5.7 0.05">';		// BEGIN: Content Frame

		// Last Map
		$xml .= '<frame posn="0 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="24.05 47" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="23.25 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="'. $this->config['Positions']['left']['icon']['x'] .' '. $this->config['Positions']['left']['icon']['y'] .' 0.05" sizen="2.5 2.5" style="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['LAST_MAP'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['LAST_MAP'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.05" sizen="23.6 0" textsize="1" text="'. $this->config['MAP_WIDGET'][0]['TITLE'][0]['LAST_MAP'][0] .'"/>';
		$xml .= '<quad posn="1.4 -3.6 0.03" sizen="21.45 16.29" bgcolor="FFF9"/>';
		$xml .= '<label posn="12.1 -11 0.04" sizen="20 2" halign="center" textsize="1" text="Press DEL if can not see an Image here!"/>';
		$xml .= '<quad posn="1.5 -3.7 0.50" sizen="21.25 16.09" image="'. $this->cache['Map']['Last']['imageurl'] .'"/>';
		$xml .= '<label posn="1.4 -21 0.02" sizen="21 3" textsize="2" text="$S'. $this->cache['Map']['Last']['name'] .'"/>';
		$xml .= '<quad posn="1.5 -23 0.04" sizen="2 2" image="file://Skins/Avatars/Flags/'. (strtoupper($this->cache['Map']['Last']['author_nation']) == 'OTH' ? 'other' : $this->cache['Map']['Last']['author_nation']) .'.dds"/>';
		$xml .= '<label posn="4 -23.3 0.02" sizen="18.4 3" textsize="1" text="by '. $this->cache['Map']['Last']['author'] .'"/>';
		$xml .= '<frame posn="3.2 -33 0">';	// BEGIN: Times frame
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$xml .= '<quad posn="0 7.1 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalNadeo"/>';
		$xml .= '<quad posn="0 4.8 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalGold"/>';
		$xml .= '<quad posn="0 2.5 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalSilver"/>';
		$xml .= '<quad posn="0 0.2 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalBronze"/>';
		$xml .= '<quad posn="0.2 -1.8 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Advanced"/>';
		$xml .= '<quad posn="0.2 -4.1 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Manialink"/>';
		$xml .= '<label posn="0.5 6.9 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['authortime'] .'"/>';
		$xml .= '<label posn="0.5 4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['goldtime'] .'"/>';
		$xml .= '<label posn="0.5 2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['silvertime'] .'"/>';
		$xml .= '<label posn="0.5 0 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['bronzetime'] .'"/>';
		$xml .= '<label posn="0.5 -2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['environment'] .'"/>';
		$xml .= '<label posn="0.5 -4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Last']['mood'] .'"/>';
		$xml .= '</frame>';			// END: Times frame
		if ($this->cache['Map']['Last']['pageurl'] != false) {
			$xml .= '<frame posn="10.6 -33 0">';	// BEGIN: MX Mapinfos
			$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
			$xml .= '<label posn="0 6.9 0.1" sizen="5 2.2" text="Type:"/>';
			$xml .= '<label posn="0 4.6 0.1" sizen="5 2" text="Style:"/>';
			$xml .= '<label posn="0 2.3 0.1" sizen="5 2" text="Difficult:"/>';
			$xml .= '<label posn="0 0 0.1" sizen="5 2" text="Routes:"/>';
			$xml .= '<label posn="0 -2.3 0.1" sizen="5 2.6" text="Awards:"/>';
			$xml .= '<label posn="0 -4.6 0.1" sizen="5 2.6" text="Section:"/>';
			$xml .= '<label posn="5.1 6.9 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['type'] .'"/>';
			$xml .= '<label posn="5.1 4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['style'] .'"/>';
			$xml .= '<label posn="5.1 2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['diffic'] .'"/>';
			$xml .= '<label posn="5.1 0 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['routes'] .'"/>';
			$xml .= '<label posn="5.1 -2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['awards'] .'"/>';
			$xml .= '<label posn="5.1 -4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Last']['section'] .'"/>';
			$xml .= '</frame>';			// END: MX Mapinfos

			// Button "Visit Page"
			if ($this->cache['Map']['Last']['pageurl'] != false) {
				$xml .= '<frame posn="6 -39.50 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Last']['pageurl']) .'" text="VISIT MAP PAGE" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Map"
			if ($this->cache['Map']['Last']['dloadurl'] != false) {
				$xml .= '<frame posn="6 -41.75 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Last']['dloadurl']) .'" text="DOWNLOAD MAP" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Replay"
			if ($this->cache['Map']['Last']['replayurl'] != false) {
				$xml .= '<frame posn="6 -44 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Last']['replayurl']) .'" text="DOWNLOAD REPLAY" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}
		}
		$xml .= '</frame>';


		// Current Map
		$xml .= '<frame posn="25.45 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="24.05 47" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="23.25 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="'. $this->config['Positions']['left']['icon']['x'] .' '. $this->config['Positions']['left']['icon']['y'] .' 0.05" sizen="2.5 2.5" style="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.05" sizen="23.6 0" textsize="1" text="'. $this->config['MAP_WIDGET'][0]['TITLE'][0]['CURRENT_MAP'][0] .'"/>';
		$xml .= '<quad posn="1.4 -3.6 0.03" sizen="21.45 16.29" bgcolor="FFF9"/>';
		$xml .= '<label posn="12.1 -11 0.04" sizen="20 2" halign="center" textsize="1" text="Press DEL if can not see an Image here!"/>';
		$xml .= '<quad posn="1.5 -3.7 0.50" sizen="21.25 16.09" image="'. $this->cache['Map']['Current']['imageurl'] .'"/>';
		$xml .= '<label posn="1.4 -21 0.02" sizen="21 3" textsize="2" text="$S'. $this->cache['Map']['Current']['name'] .'"/>';
		$xml .= '<quad posn="1.5 -23 0.04" sizen="2 2" image="file://Skins/Avatars/Flags/'. (strtoupper($this->cache['Map']['Current']['author_nation']) == 'OTH' ? 'other' : $this->cache['Map']['Current']['author_nation']) .'.dds"/>';
		$xml .= '<label posn="4 -23.3 0.02" sizen="18.4 3" textsize="1" text="by '. $this->cache['Map']['Current']['author'] .'"/>';
		$xml .= '<frame posn="3.2 -33 0">';	// BEGIN: Times frame
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$xml .= '<quad posn="0 7.1 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalNadeo"/>';
		$xml .= '<quad posn="0 4.8 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalGold"/>';
		$xml .= '<quad posn="0 2.5 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalSilver"/>';
		$xml .= '<quad posn="0 0.2 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalBronze"/>';
		$xml .= '<quad posn="0.2 -1.8 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Advanced"/>';
		$xml .= '<quad posn="0.2 -4.1 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Manialink"/>';
		$xml .= '<label posn="0.5 6.9 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['authortime'] .'"/>';
		$xml .= '<label posn="0.5 4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['goldtime'] .'"/>';
		$xml .= '<label posn="0.5 2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['silvertime'] .'"/>';
		$xml .= '<label posn="0.5 0 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['bronzetime'] .'"/>';
		$xml .= '<label posn="0.5 -2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['environment'] .'"/>';
		$xml .= '<label posn="0.5 -4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['mood'] .'"/>';
		$xml .= '</frame>';			// END: Times frame
		if ($this->cache['Map']['Current']['pageurl'] != false) {
			$xml .= '<frame posn="10.6 -33 0">';	// BEGIN: MX Mapinfos
			$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
			$xml .= '<label posn="0 6.9 0.1" sizen="5 2.2" text="Type:"/>';
			$xml .= '<label posn="0 4.6 0.1" sizen="5 2" text="Style:"/>';
			$xml .= '<label posn="0 2.3 0.1" sizen="5 2" text="Difficult:"/>';
			$xml .= '<label posn="0 0 0.1" sizen="5 2" text="Routes:"/>';
			$xml .= '<label posn="0 -2.3 0.1" sizen="5 2.6" text="Awards:"/>';
			$xml .= '<label posn="0 -4.6 0.1" sizen="5 2.6" text="Section:"/>';
			$xml .= '<label posn="5.1 6.9 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['type'] .'"/>';
			$xml .= '<label posn="5.1 4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['style'] .'"/>';
			$xml .= '<label posn="5.1 2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['diffic'] .'"/>';
			$xml .= '<label posn="5.1 0 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['routes'] .'"/>';
			$xml .= '<label posn="5.1 -2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['awards'] .'"/>';
			$xml .= '<label posn="5.1 -4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Current']['section'] .'"/>';
			$xml .= '</frame>';			// END: MX Mapinfos

			// Button "Visit Page"
			if ($this->cache['Map']['Current']['pageurl'] != false) {
				$xml .= '<frame posn="6 -39.50 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['pageurl']) .'" text="VISIT MAP PAGE" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Map"
			if ($this->cache['Map']['Current']['dloadurl'] != false) {
				$xml .= '<frame posn="6 -41.75 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['dloadurl']) .'" text="DOWNLOAD MAP" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Replay"
			if ($this->cache['Map']['Current']['replayurl'] != false) {
				$xml .= '<frame posn="6 -44 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['replayurl']) .'" text="DOWNLOAD REPLAY" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}
		}
		$xml .= '</frame>';


		// Next Map
		$xml .= '<frame posn="50.85 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="24.05 47" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="23.25 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="'. $this->config['Positions']['left']['icon']['x'] .' '. $this->config['Positions']['left']['icon']['y'] .' 0.05" sizen="2.5 2.5" style="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['NEXT_MAP'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['NEXT_MAP'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.05" sizen="23.6 0" textsize="1" text="'. $this->config['MAP_WIDGET'][0]['TITLE'][0]['NEXT_MAP'][0] .'"/>';
		$xml .= '<quad posn="1.4 -3.6 0.03" sizen="21.45 16.29" bgcolor="FFF9"/>';
		$xml .= '<label posn="12.1 -11 0.04" sizen="20 2" halign="center" textsize="1" text="Press DEL if can not see an Image here!"/>';
		$xml .= '<quad posn="1.5 -3.7 0.50" sizen="21.25 16.09" image="'. $this->cache['Map']['Next']['imageurl'] .'"/>';
		$xml .= '<label posn="1.4 -21 0.02" sizen="21 3" textsize="2" text="$S'. $this->cache['Map']['Next']['name'] .'"/>';
		$xml .= '<quad posn="1.5 -23 0.04" sizen="2 2" image="file://Skins/Avatars/Flags/'. (strtoupper($this->cache['Map']['Next']['author_nation']) == 'OTH' ? 'other' : $this->cache['Map']['Next']['author_nation']) .'.dds"/>';
		$xml .= '<label posn="4 -23.3 0.02" sizen="18.4 3" textsize="1" text="by '. $this->cache['Map']['Next']['author'] .'"/>';
		$xml .= '<frame posn="3.2 -33 0">';	// BEGIN: Times frame
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$xml .= '<quad posn="0 7.1 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalNadeo"/>';
		$xml .= '<quad posn="0 4.8 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalGold"/>';
		$xml .= '<quad posn="0 2.5 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalSilver"/>';
		$xml .= '<quad posn="0 0.2 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalBronze"/>';
		$xml .= '<quad posn="0.2 -1.8 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Advanced"/>';
		$xml .= '<quad posn="0.2 -4.1 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Manialink"/>';
		$xml .= '<label posn="0.5 6.9 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['authortime'] .'"/>';
		$xml .= '<label posn="0.5 4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['goldtime'] .'"/>';
		$xml .= '<label posn="0.5 2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['silvertime'] .'"/>';
		$xml .= '<label posn="0.5 0 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['bronzetime'] .'"/>';
		$xml .= '<label posn="0.5 -2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['environment'] .'"/>';
		$xml .= '<label posn="0.5 -4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Next']['mood'] .'"/>';
		$xml .= '</frame>';			// END: Times frame
		if ($this->cache['Map']['Next']['pageurl'] != false) {
			$xml .= '<frame posn="10.6 -33 0">';	// BEGIN: MX Mapinfos
			$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
			$xml .= '<label posn="0 6.9 0.1" sizen="5 2.2" text="Type:"/>';
			$xml .= '<label posn="0 4.6 0.1" sizen="5 2" text="Style:"/>';
			$xml .= '<label posn="0 2.3 0.1" sizen="5 2" text="Difficult:"/>';
			$xml .= '<label posn="0 0 0.1" sizen="5 2" text="Routes:"/>';
			$xml .= '<label posn="0 -2.3 0.1" sizen="5 2.6" text="Awards:"/>';
			$xml .= '<label posn="0 -4.6 0.1" sizen="5 2.6" text="Section:"/>';
			$xml .= '<label posn="5.1 6.9 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['type'] .'"/>';
			$xml .= '<label posn="5.1 4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['style'] .'"/>';
			$xml .= '<label posn="5.1 2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['diffic'] .'"/>';
			$xml .= '<label posn="5.1 0 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['routes'] .'"/>';
			$xml .= '<label posn="5.1 -2.3 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['awards'] .'"/>';
			$xml .= '<label posn="5.1 -4.6 0.1" sizen="10.5 2" text=" '. $this->cache['Map']['Next']['section'] .'"/>';
			$xml .= '</frame>';			// END: MX Mapinfos

			// Button "Visit Page"
			if ($this->cache['Map']['Next']['pageurl'] != false) {
				$xml .= '<frame posn="6 -39.50 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Next']['pageurl']) .'" text="VISIT MAP PAGE" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Map"
			if ($this->cache['Map']['Next']['dloadurl'] != false) {
				$xml .= '<frame posn="6 -41.75 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Next']['dloadurl']) .'" text="DOWNLOAD MAP" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}

			// Button "Download Replay"
			if ($this->cache['Map']['Next']['replayurl'] != false) {
				$xml .= '<frame posn="6 -44 0.04">';
				$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Next']['replayurl']) .'" text="DOWNLOAD REPLAY" style="CardButtonSmall"/>';
				$xml .= '</frame>';
			}
		}
		$xml .= '</frame>';


		$xml .= '</frame>';				// END: Content Frame
		$xml .= $this->templates['WINDOW']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildAskDropMapJukebox () {
		global $aseco;

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons64x64_1',
				'TrackInfo',
				'Notice',
				''
			),
			$this->templates['SUBWINDOW']['HEADER']
		);

		// Ask
		$xml .= '<label posn="3.5 -6 0.04" sizen="34 0" textsize="2" scale="0.8" autonewline="1" maxline="7" text="Do you really want to drop the complete Jukebox?"/>';
		$xml .= '<label posn="23.75 -22.4 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" action="dropMapJukebox" text="YES" style="CardButtonMediumS"/>';
		$xml .= '<label posn="33 -22.4 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" action="closeSubWindow" text="NO" style="CardButtonMediumS"/>';

		$xml .= $this->templates['SUBWINDOW']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMaplistWindow ($page, $player) {
		global $aseco;

		// Filter activity requested?
		$maplist = array();
		$listoptions = '';	// Title addition
		if ( is_array($player->data['PluginRecordsEyepiece']['Maplist']['Filter']) ) {
			if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['environment']) ){
				// Filter for environment
				foreach ($this->cache['MapList'] as $map) {
					if (strtoupper($map['environment']) == $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['environment']) {
						$maplist[] = $map;
					}
				}
				unset($map);
				$listoptions = '(Filter: Only env. '. ucfirst(strtolower($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['environment'])) .')';
			}
			else if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['mood'])) {
				foreach ($this->cache['MapList'] as $map) {
					if (strtoupper($map['mood']) == $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['mood']) {
						$maplist[] = $map;
					}
				}
				unset($map);
				$listoptions = '(Filter: Only mood '. ucfirst(strtolower($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['mood'])) .')';
			}
			else if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['author']) ){
				// Filter for MapAuthor
				foreach ($this->cache['MapList'] as $map) {
					if ($map['author'] == $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['author']) {
						$maplist[] = $map;
					}
				}
				unset($map);
				$listoptions = '(Filter: Only Maps by '. $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['author'] .')';
			}
			else if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'])) {
				if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NORANK') {
					foreach ($this->cache['MapList'] as $map) {
						if ( !isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank']) ) {
							$maplist[] = $map;
						}
					}
					unset($map);
					$listoptions = '(Filter: Not Ranked Maps)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'ONLYRANK') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank']) ) {
							$maplist[] = $map;
						}
					}
					unset($map);
					$listoptions = '(Filter: Only Ranked Maps)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOAUTHOR') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]) ) {
							if ( ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) && ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] <= $map['authortime_filter']) ) {
								$maplist[] = $map;
							}
							else if ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] > $map['authortime_filter']) {
								$maplist[] = $map;
							}
						}
					}
					unset($map);
					$listoptions = '(Filter: No Author Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOGOLD') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]) ) {
							if ( ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) && ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] <= $map['goldtime_filter']) ) {
								$maplist[] = $map;
							}
							else if ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] > $map['goldtime_filter']) {
								$maplist[] = $map;
							}
						}
					}
					unset($map);
					$listoptions = '(Filter: No Gold Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOSILVER') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]) ) {
							if ( ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) && ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] <= $map['silvertime_filter']) ) {
								$maplist[] = $map;
							}
							else if ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] > $map['silvertime_filter']) {
								$maplist[] = $map;
							}
						}
					}
					unset($map);
					$listoptions = '(Filter: No Silver Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOBRONZE') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]) ) {
							if ( ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) && ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] <= $map['bronzetime_filter']) ) {
								$maplist[] = $map;
							}
							else if ($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['score'] > $map['bronzetime_filter']) {
								$maplist[] = $map;
							}
						}
					}
					unset($map);
					$listoptions = '(Filter: No Bronze Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NORECENT') {
					foreach ($this->cache['MapList'] as $map) {
						if ( !in_array($map['uid'], $aseco->plugins['PluginRaspJukebox']->jb_buffer) ) {
							$maplist[] = $map;
						}
					}
					$listoptions = '(Filter: No Recent)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'ONLYRECENT') {
					foreach (array_reverse($aseco->plugins['PluginRaspJukebox']->jb_buffer, true) as $uid) {
						foreach ($this->cache['MapList'] as $map) {
							if ($map['uid'] == $uid) {
								$maplist[] = $map;
							}
						}
						unset($map);
					}
					unset($uid);
					$listoptions = '(Filter: Only Recent)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'JUKEBOX') {
					foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $item) {
						foreach ($this->cache['MapList'] as $map) {
							// Find the Maps from the Jukebox
							if ($item['uid'] == $map['uid']) {
								$maplist[] = $map;
								break;
							}
						}
						unset($map);
					}
					unset($item);
					$listoptions = '(Filter: Only Jukebox)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'ONLYMULTILAP') {
					foreach ($this->cache['MapList'] as $map) {
						if ($map['multilap'] == true) {
							$maplist[] = $map;
						}
					}
					unset($map);
					$listoptions = '(Filter: Only Multilap)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOMULTILAP') {
					foreach ($this->cache['MapList'] as $map) {
						if ($map['multilap'] != true) {
							$maplist[] = $map;
						}
					}
					unset($map);
					$listoptions = '(Filter: No Multilap)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['cmd'] == 'NOFINISH') {
					foreach ($this->cache['MapList'] as $map) {
						if ( in_array($map['uid'], $player->data['PluginRecordsEyepiece']['Maplist']['Unfinished']) ) {
							$maplist[] = $map;
						}
					}
					unset($map);
					$listoptions = '(Filter: Not Finished Maps)';
				}
			}
			else if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'])) {
				if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'BEST') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank']) ) {
							$map['rank'] = $player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank'];
							$maplist[] = $map;
						}
					}

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['rank'];
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Best Player Rank)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'WORST') {
					foreach ($this->cache['MapList'] as $map) {
						if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank']) ) {
							$map['rank'] = $player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank'];
							$maplist[] = $map;
						}
					}

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['rank'];
					}
					array_multisort($sort, SORT_DESC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Worst Player Rank)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'SHORTEST') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['authortime_filter'];
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Shortest Author Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'LONGEST') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['authortime_filter'];
					}
					array_multisort($sort, SORT_DESC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Longest Author Time)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'NEWEST') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['dbid'];
					}
					array_multisort($sort, SORT_DESC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Newest Maps First)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'OLDEST') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['dbid'];
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Oldest Maps First)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'MAPNAME') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key]	= strtolower($row['name_stripped']);
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: By Map Name)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'AUTHORNAME') {
					$maplist = $this->cache['MapList'];

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key]	= strtolower($row['author']);
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: By Author Name)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'BESTMAPS') {
					foreach ($this->cache['MapList'] as $map) {
						$maplist[] = $map;
					}

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['karma'];
					}
					array_multisort($sort, SORT_DESC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Karma Best Maps)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'WORSTMAPS') {
					foreach ($this->cache['MapList'] as $map) {
						$maplist[] = $map;
					}

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['karma'];
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: Karma Worst Maps)';
				}
				else if ($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['sort'] == 'AUTHORNATION') {
					foreach ($this->cache['MapList'] as $map) {
						$maplist[] = $map;
					}

					// Sort the array now
					$sort = array();
					foreach ($maplist as $key => $row) {
						$sort[$key] = $row['author_nation'];
					}
					array_multisort($sort, SORT_ASC, $maplist);
					unset($sort, $row);

					$listoptions = '(Sorting: By Author Nation)';
				}
			}
			else if ( isset($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['key'])) {
				foreach ($this->cache['MapList'] as $map) {
					if (
						(stripos($map['author'], $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['key']) !== false)
						||
						(stripos($map['name_stripped'], $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['key']) !== false)
						||
						(stripos($map['filename'], $player->data['PluginRecordsEyepiece']['Maplist']['Filter']['key']) !== false)
					)
					{
						$maplist[] = $map;
					}
					$listoptions = '(Search: &apos;'. $this->handleSpecialChars($player->data['PluginRecordsEyepiece']['Maplist']['Filter']['key']) .'&apos;)';
				}
				unset($map);
			}
		}
		else {
			// No Filter, show all Maps
			$maplist = $this->cache['MapList'];
		}


		$subwin = '';
		if (count($maplist) == 0) {

			$subwin = str_replace(
				array(
					'%icon_style%',
					'%icon_substyle%',
					'%window_title%',
					'%prev_next_buttons%'
				),
				array(
					'Icons64x64_1',
					'TrackInfo',
					'Notice',
					''
				),
				$this->templates['SUBWINDOW']['HEADER']
			);
			$subwin .= '<label posn="3 -6 0.04" sizen="42 0" textsize="2" scale="0.8" autonewline="1" maxline="7" text="This filter return an empty result, which means that no Track match your wished filter."/>';
			$subwin .= '<label posn="19.8 -22.4 0.02" sizen="8 2.0" halign="center" textsize="1" scale="0.8" action="closeSubWindow" text="OK" style="CardButtonMediumS"/>';
			$subwin .= $this->templates['SUBWINDOW']['FOOTER'];

			// Filter does not match, show all Tracks
			$maplist = $this->cache['MapList'];

			// Reset all Filters/Titles
			$listoptions = '';
			$player->data['PluginRecordsEyepiece']['Maplist']['Filter'] = false;
		}


		// Get the total of Maps
		$totalmaps = count($maplist);

		// Determind the maxpages
		$maxpages = ceil($totalmaps / 20);
		if ($page > $maxpages) {
			$page = $maxpages - 1;
		}

		// Determine admin ability to drop all jukeboxed and to add recent played Maps
		$dropall = $aseco->allowAbility($player, 'dropjukebox');
		$add_recent = $aseco->allowAbility($player, 'chat_jb_recent');



		$buttons = '';

		// Button "Drop current juke'd Map"
		if ($aseco->allowAbility($player, 'clearjukebox')) {
			$buttons .= '<frame posn="38.85 -53.3 0.04">';
			$buttons .= '<quad posn="0 -1 0.12" sizen="3 3" action="askDropMapJukebox" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.4 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="0.3 -1.3 0.14" sizen="2.4 2.4" style="Icons128x32_1" substyle="Settings"/>';
			$buttons .= '<label posn="1.1 -1.5 0.15" sizen="8 0" textsize="2" style="TextCardRaceRank" text="$S$W$O$F00/"/>';
			$buttons .= '</frame>';
		}

		// Filter Buttons
		$buttons .= '<frame posn="44.75 -53.3 0.04">';
			$buttons .= '<quad posn="0 -1 0.12" sizen="3 3" action="showMaplistFilterWindow" style="Icons64x64_1" substyle="Maximize"/>';

			$buttons .= '<quad posn="3.3 -1 0.12" sizen="3 3" action="showMaplistSortingWindow" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="3.7 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="3.5 -1.2 0.14" sizen="2.6 2.6" style="UIConstructionSimple_Buttons" substyle="Validate"/>';

			$buttons .= '<quad posn="6.6 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
//			$buttons .= '<quad posn="6.6 -1 0.12" sizen="3 3" action="showMaplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';
		$buttons .= '</frame>';


		// Frame for Previous-/Next-Buttons
		$buttons .= '<frame posn="52.05 -53.3 0.04">';

		// Previous button
		if ($page > 0) {
			// First
			$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="7.34 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if ( ($totalmaps > 20) && (($page + 1) < $maxpages) ) {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';


		// Create Windowtitle depending on the $maplist
		if (count($maplist) == 0) {
			$title = 'Maps on this Server';
		}
		else {
			$title = 'Maps on this Server   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalmaps, 0) . (($totalmaps == 1) ? ' Map' : ' Maps') .' '. $listoptions;
		}

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'Browse',
				$title,
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);


		$line = 0;
		$offset = 0;
		$player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'] = array();

		$xml .= '<frame posn="3.2 -5.7 0.05">';
		if (count($maplist) > 0) {
			$map_count = 1;
			for ($i = ($page * 20); $i < (($page * 20) + 20); $i ++) {

				// Is there a Map?
				if ( !isset($maplist[$i]) ) {
					break;
				}

				// Get Map
				$map = &$maplist[$i];

				// Add this map to the current list from Player
				$player->data['PluginRecordsEyepiece']['Maplist']['CurrentDisplay'][] = $map;

				// Find the Player who has juked this Map
				$login = false;
				$juked = 0;
				$tid = 1;
				foreach ($aseco->plugins['PluginRaspJukebox']->jukebox as $item) {
					if ($item['uid'] == $map['uid']) {
						$login = $item['Login'];
						$juked = $tid;
						break;
					}
					$tid++;
				}
				unset($item);

				$xml .= '<frame posn="'. $offset .' -'. (9.45 * $line) .' 1">';
				if ( (!in_array($map['uid'], $aseco->plugins['PluginRaspJukebox']->jb_buffer)) && ($juked == 0) ) {
					// Default (not recent and not juked)
					$xml .= '<format textsize="1" textcolor="FFFF"/>';
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="addMapToJukebox'. sprintf("%02d", $map_count) .'" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_PLUS_FOCUS'][0] .'"/>';
					$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
					$xml .= '<label posn="6 -0.65 0.05" sizen="7.3 0" textsize="1" text="#'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.8 -0.5 0.05" sizen="5 1.5" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['LOGOS'][0][strtoupper($map['environment'])][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="1" text="'. $map['name'] .'"/>';
					$xml .= '<quad posn="1 -4.2 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/'. (strtoupper($map['author_nation']) == 'OTH' ? 'other' : $map['author_nation']) .'.dds"/>';
					$xml .= '<label posn="3.3 -4.5 0.04" sizen="13 2" scale="0.9" text="by '. $map['author'] .'"/>';
				}
				else if ( (in_array($map['uid'], $aseco->plugins['PluginRaspJukebox']->jb_buffer)) && ($juked > 0) ) {
					// This is a recent but juked Map, action are handled by plugin.rasp_jukebox.php
					$xml .= '<format textsize="1" textcolor="FFF8"/>';
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					if ( ($dropall) || ($login == $player->login) ) {
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="'. (-2000-$juked) .'" image="'. $this->config['IMAGES'][0]['WIDGET_MINUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_MINUS_FOCUS'][0] .'"/>';
					}
					$xml .= '<quad posn="0.27 -0.34 0.04" sizen="17.4 2.2" style="BgsButtons" substyle="BgButtonMediumSpecial"/>';
					$xml .= '<label posn="6 -0.65 0.05" sizen="7.3 0" textcolor="000F" textsize="1" text="#'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.8 -0.5 0.05" sizen="5 1.5" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['LOGOS'][0][strtoupper($map['environment'])][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="1" text="'. $aseco->stripColors($map['name'], true) .'"/>';
					$xml .= '<quad posn="1 -4.2 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/'. (strtoupper($map['author_nation']) == 'OTH' ? 'other' : $map['author_nation']) .'.dds"/>';
					$xml .= '<label posn="3.3 -4.5 0.04" sizen="13 2" scale="0.9" text="by '. $aseco->stripColors($map['author'], true) .'"/>';
				}
				else if (in_array($map['uid'], $aseco->plugins['PluginRaspJukebox']->jb_buffer)) {
					// This is a recent Map
					$xml .= '<format textsize="1" textcolor="FFF8"/>';
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					if ($add_recent) {
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="addMapToJukebox'. sprintf("%02d", $map_count) .'" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_PLUS_FOCUS'][0] .'"/>';
					}
					$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					$xml .= '<label posn="6 -0.65 0.05" sizen="7.3 0" textsize="1" text="#'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.8 -0.5 0.05" sizen="5 1.5" opacity="0.3" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['LOGOS'][0][strtoupper($map['environment'])][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="1" text="'. $aseco->stripColors($map['name'], true) .'"/>';
					$xml .= '<quad posn="1 -4.2 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/'. (strtoupper($map['author_nation']) == 'OTH' ? 'other' : $map['author_nation']) .'.dds"/>';
					$xml .= '<label posn="3.3 -4.5 0.04" sizen="13 2" scale="0.9" text="by '. $aseco->stripColors($map['author'], true) .'"/>';
				}
				else {
					// This is a juked Map, action are handled by plugin.rasp_jukebox.php
					$xml .= '<format textsize="1" textcolor="FFFF"/>';
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					if ( ($dropall) || ($login == $player->login) ) {
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="'. (-2000-$juked) .'" image="'. $this->config['IMAGES'][0]['WIDGET_MINUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_MINUS_FOCUS'][0] .'"/>';
					}
					$xml .= '<quad posn="0.27 -0.34 0.04" sizen="17.4 2.2" style="BgsButtons" substyle="BgButtonMediumSpecial"/>';
					$xml .= '<label posn="6 -0.65 0.05" sizen="7.3 0" textcolor="000F" textsize="1" text="#'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.8 -0.5 0.05" sizen="5 1.5" modulatecolor="000" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['LOGOS'][0][strtoupper($map['environment'])][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="1" text="'. $map['name'] .'"/>';
					$xml .= '<quad posn="1 -4.2 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/'. (strtoupper($map['author_nation']) == 'OTH' ? 'other' : $map['author_nation']) .'.dds"/>';
					$xml .= '<label posn="3.3 -4.5 0.04" sizen="13 2" scale="0.9" text="by '. $map['author'] .'"/>';
				}

				// Mark current Map
				if ($map['uid'] == $this->cache['Map']['Current']['uid']) {
					$xml .= '<quad posn="16 0 0.06" sizen="2.5 2.5" style="BgRaceScore2" substyle="Fame"/>';
				}

				// Authortime
				$xml .= '<quad posn="0.7 -6.9 0.04" sizen="1.6 1.5" style="BgRaceScore2" substyle="ScoreReplay"/>';
				$xml .= '<label posn="2.4 -7.15 0.04" sizen="5 1.5" scale="0.75" text="'. $map['authortime'] .'"/>';

				// Player Rank
				$pos = isset($player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank']) ? $player->data['PluginRecordsEyepiece']['Maplist']['Records'][$map['uid']]['rank'] : 0;
				$xml .= '<quad posn="6.3 -6.8 0.04" sizen="2 1.6" style="BgRaceScore2" substyle="LadderRank"/>';
				$xml .= '<label posn="8.1 -7.15 0.04" sizen="3.8 1.5" scale="0.75" text="'. (($pos >= 1 && $pos <= $aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) ? sprintf("%0". strlen($aseco->plugins['PluginLocalRecords']->records->getMaxRecords()) ."d.", $pos) : '$ZNone') .'"/>';

				// Local Map Karma
				$xml .= '<quad posn="11.2 -6.8 0.04" sizen="1.6 1.6" style="Icons64x64_1" substyle="StateFavourite"/>';
				$xml .= '<label posn="12.8 -7.15 0.04" sizen="2.2 1.5" scale="0.75" text="L'. $map['karma'] .'"/>';

				$xml .= '</frame>';

				$line ++;

				// Reset lines
				if ($line >= 5) {
					$offset += 19.05;
					$line = 0;
				}

				$map_count ++;
			}
		}
		$xml .= '</frame>';
		$xml .= $this->templates['WINDOW']['FOOTER'];

		// Add the SubWindow (if there is one)
		$xml .= $subwin;

		return array(
			'xml'		=> $xml,
			'maxpage'	=> ($maxpages - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMaplistFilterWindow () {
		global $aseco;

		$buttons = '<frame posn="38.85 -53.3 0.04">';
		$buttons .= '<quad posn="0 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$buttons .= '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'NewTrack',
				'Filter options for Maplist',
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="3.2 -5.7 1">'; // Content Window

		// No Author Time
		$xml .= '<frame posn="0 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsNoAuthorTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="MedalsBig" substyle="MedalNadeo"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Author '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'Score' : 'Time') .'"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you did not beat the author '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'score' : 'time') .' on."/>';
		$xml .= '</frame>';

		// Only Recent Maps
		$xml .= '<frame posn="0 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyRecentMaps" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.5 0 0.05" sizen="2.6 2.6" style="Icons128x128_1" substyle="LoadTrack"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Only Recent Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps that have been played recently."/>';
		$xml .= '</frame>';

		// No Recent Maps
		$xml .= '<frame posn="0 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterNoRecentMaps" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.5 0 0.05" sizen="2.6 2.6" style="Icons128x128_1" substyle="LoadTrack"/>';
		$xml .= '<quad posn="0.5 0 0.06" sizen="2.6 2.6" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Recent Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps that have been played not recently."/>';
		$xml .= '</frame>';

		// No Gold Time
		$xml .= '<frame posn="19.05 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsNoGoldTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="MedalsBig" substyle="MedalGold"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Gold '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'Score' : 'Time') .'"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you did not beat the gold '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'score' : 'time') .' on."/>';
		$xml .= '</frame>';

		// Only Ranked Maps
		$xml .= '<frame posn="19.05 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsWithRank" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.7 0 0.05" sizen="2.5 2.5" style="BgRaceScore2" substyle="LadderRank"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Only Ranked Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you already have a rank received."/>';
		$xml .= '</frame>';

		// Not Ranked Maps
		$xml .= '<frame posn="19.05 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsWithoutRank" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.7 0 0.05" sizen="2.5 2.5" style="BgRaceScore2" substyle="LadderRank"/>';
		$xml .= '<quad posn="0.7 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Not Ranked Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you not already have a rank received."/>';
		$xml .= '</frame>';

		// No Silver Time
		$xml .= '<frame posn="38.1 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsNoSilverTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="MedalsBig" substyle="MedalSilver"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Silver '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'Score' : 'Time') .'"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you did not beat the silver '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'score' : 'time') .' on."/>';
		$xml .= '</frame>';

		// Only Multilap Maps
		$xml .= '<frame posn="38.1 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMultilapMaps" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Only Multilap Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps that are multilaps Maps."/>';
		$xml .= '</frame>';

		// No Multilap Maps
		$xml .= '<frame posn="38.1 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterNoMultilapMaps" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Multilap Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps that are not multilaps Maps."/>';
		$xml .= '</frame>';

		// No Bronze Time
		$xml .= '<frame posn="57.15 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsNoBronzeTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="MedalsBig" substyle="MedalBronze"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Bronze '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'Score' : 'Time') .'"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps where you did not beat the bronze '. (($aseco->server->gameinfo->mode == Gameinfo::STUNTS) ? 'score' : 'time') .' on."/>';
		$xml .= '</frame>';

		// Not Finished
		$xml .= '<frame posn="57.15 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterOnlyMapsNotFinished" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="Icons64x64_1" substyle="Finish"/>';
		$xml .= '<quad posn="0.6 0 0.06" sizen="2.5 2.5" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="No Finish"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps that you did not have finished yet."/>';
		$xml .= '</frame>';

		// Select Authorname
		$xml .= '<frame posn="57.15 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMapAuthorlistWindow" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="ChallengeAuthor"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Select Authorname"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Select an Authorname and display only Maps from this author."/>';
		$xml .= '</frame>';

		// Current Jukebox
		$xml .= '<frame posn="57.15 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowFilterJukeboxedMaps" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="Load"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Current Jukebox"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display only Maps, that are in the jukebox to get played."/>';
		$xml .= '</frame>';


		// Mood
		$xml .= '<frame posn="0 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="55.85 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="55.05 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 -0.1 0.05" sizen="2.6 2.6" style="Icons128x128_1" substyle="Manialink"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="55.05 0" textsize="1" text="Map mood"/>';

		// Sunrise
		if ($this->cache['MaplistCounts']['Mood']['SUNRISE'] > 0) {
			$xml .= '<quad posn="1.6 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterMapsWithMoodSunrise" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNRISE'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['SUNRISE'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="1.6 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNRISE'][0] .'"/>';
		}

		// Day
		if ($this->cache['MaplistCounts']['Mood']['DAY'] > 0) {
			$xml .= '<quad posn="15.5 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterMapsWithMoodDay" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['DAY'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['DAY'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="15.5 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['DAY'][0] .'"/>';
		}

		// Sunset
		if ($this->cache['MaplistCounts']['Mood']['SUNSET'] > 0) {
			$xml .= '<quad posn="29.4 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterMapsWithMoodSunset" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNSET'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['SUNSET'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="29.4 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['SUNSET'][0] .'"/>';
		}

		// Night
		if ($this->cache['MaplistCounts']['Mood']['NIGHT'] > 0) {
			$xml .= '<quad posn="43.3 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterMapsWithMoodNight" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['NIGHT'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MOOD'][0]['FOCUS'][0]['NIGHT'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="43.3 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['MOOD'][0]['ENABLED'][0]['NIGHT'][0] .'"/>';
		}
		$xml .= '</frame>';



		// Map environment
		$xml .= '<frame posn="0 -37.8 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="74.9 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="74.13 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 -0.1 0.05" sizen="2.6 2.6" style="Icons128x128_1" substyle="Advanced"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="55.05 0" textsize="1" text="Map environment"/>';

		// 'Canyon'
		if ($this->cache['MaplistCounts']['Environment']['CANYON'] > 0) {
			$xml .= '<quad posn="1.6 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterOnlyCanyonMaps" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_CANYON'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_CANYON'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="1.6 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_CANYON'][0] .'"/>';
		}

		// 'Stadium'
		if ($this->cache['MaplistCounts']['Environment']['STADIUM'] > 0) {
			$xml .= '<quad posn="13.28 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterOnlyStadiumMaps" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_STADIUM'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_STADIUM'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="13.28 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_STADIUM'][0] .'"/>';
		}

		// 'Valley'
		if ($this->cache['MaplistCounts']['Environment']['VALLEY'] > 0) {
			$xml .= '<quad posn="24.96 -3 0.06" sizen="10.88 5.44" action="showMaplistWindowFilterOnlyValleyMaps" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_VALLEY'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['FOCUS'][0]['ICON_VALLEY'][0] .'"/>';
		}
		else {
			$xml .= '<quad posn="24.96 -3 0.06" sizen="10.88 5.44" opacity="0.5" colorize="FFF" image="'. $this->config['IMAGES'][0]['ENVIRONMENT'][0]['ENABLED'][0]['ICON_VALLEY'][0] .'"/>';
		}
		$xml .= '</frame>';

		$xml .= '</frame>'; // Content Window


		// Filter Buttons
		$xml .= '<frame posn="44.75 -53.3 0.04">';
			$xml .= '<quad posn="0 -1 0.12" sizen="3 3" action="showMaplistFilterWindow" style="Icons64x64_1" substyle="Maximize"/>';

			$xml .= '<quad posn="3.3 -1 0.12" sizen="3 3" action="showMaplistSortingWindow" style="Icons64x64_1" substyle="Maximize"/>';
			$xml .= '<quad posn="3.7 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$xml .= '<quad posn="3.5 -1.2 0.14" sizen="2.6 2.6" style="UIConstructionSimple_Buttons" substyle="Validate"/>';

			$xml .= '<quad posn="6.6 -1 0.12" sizen="3 3" action="showMaplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMaplistSortingWindow () {
		global $aseco;

		$buttons = '<frame posn="38.85 -53.3 0.04">';
		$buttons .= '<quad posn="0 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$buttons .= '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'NewTrack',
				'Sort options for Maplist',
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="3.2 -5.7 1">'; // Content Window

		// All Maps
		$xml .= '<frame posn="0 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindow" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="Browse"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="All Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Display all Maps, that are currently available on this Server."/>';
		$xml .= '</frame>';

		// Best Ranked Maps
		$xml .= '<frame posn="0 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingBestPlayerRank" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.5 0 0.05" sizen="2.6 2.6" style="BgRaceScore2" substyle="LadderRank"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Best Ranked Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Rank,'. LF .' from best to worst."/>';
		$xml .= '</frame>';

		// Worst Ranked Maps
		$xml .= '<frame posn="0 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingWorstPlayerRank" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.5 0 0.05" sizen="2.6 2.6" style="BgRaceScore2" substyle="LadderRank"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Worst Ranked Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Rank,'. LF .' from worst to best."/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="0 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="0 -37.8 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// Sort by Mapname
		$xml .= '<frame posn="19.05 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingByMapname" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="NewTrack"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Sort by Mapname"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort all currently available Maps by Mapname."/>';
		$xml .= '</frame>';

		// Shortest Maps
		$xml .= '<frame posn="19.05 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingShortestAuthorTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.7 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="Race"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Shortest Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Authortime,'. LF .'from shortest to longest."/>';
		$xml .= '</frame>';

		// Longest Maps
		$xml .= '<frame posn="19.05 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingLongestAuthorTime" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.7 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="Race"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Longest Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Authortime,'. LF .'from longest to shortest."/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="19.05 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="19.05 -37.8 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// Sort by Authorname
		$xml .= '<frame posn="38.1 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingByAuthorname" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="ChallengeAuthor"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Sort by Authorname"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort all currently available Maps by Authorname."/>';
		$xml .= '</frame>';

		// Newest Maps First
		$xml .= '<frame posn="38.1 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingNewestMapsFirst" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="LoadTrack"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Newest Maps First"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by age,'. LF .'from newest to oldest."/>';
		$xml .= '</frame>';

		// Oldest Maps First
		$xml .= '<frame posn="38.1 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingOldestMapsFirst" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="LoadTrack"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Oldest Maps First"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by age,'. LF .'from oldest to newest."/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="38.1 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="38.1 -37.8 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Laps"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// Sort by AuthorNation
		$xml .= '<frame posn="57.15 0 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingByAuthorNation" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="UIConstructionSimple_Buttons" substyle="Validate"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Sort by AuthorNation"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort all currently available Maps by AuthorNation."/>';
		$xml .= '</frame>';

		// Karma Best Maps
		$xml .= '<frame posn="57.15 -9.45 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingByKarmaBestMapsFirst" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="Icons128x128_1" substyle="Challenge"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Karma Best Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Karma,'. LF .'from best to worst."/>';
		$xml .= '</frame>';

		// Karma Worst Maps
		$xml .= '<frame posn="57.15 -18.9 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingByKarmaWorstMapsFirst" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="Icons128x128_1" substyle="Challenge"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Karma Worst Maps"/>';
		$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Sort Maps by Karma,'. LF .'from worst to best."/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="57.15 -28.35 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.8 -0.2 0.05" sizen="2.2 2.2" style="MedalsBig" substyle="MedalBronze"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="xxx"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="xxx"/>';
		$xml .= '</frame>';

		// FREE
		$xml .= '<frame posn="57.15 -37.8 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
	//	$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="showMaplistWindowSortingXXX" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
	//	$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x128_1" substyle="Browse"/>';
	//	$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="Select Mapauthor"/>';
	//	$xml .= '<label posn="1 -2.7 0.04" sizen="16 2" scale="0.9" autonewline="1" text="Select a Mapauthor and display only Maps from this author."/>';
		$xml .= '</frame>';

		$xml .= '</frame>'; // Content Window


		// Filter Buttons
		$xml .= '<frame posn="44.75 -53.3 0.04">';
			$xml .= '<quad posn="0 -1 0.12" sizen="3 3" action="showMaplistFilterWindow" style="Icons64x64_1" substyle="Maximize"/>';

			$xml .= '<quad posn="3.3 -1 0.12" sizen="3 3" action="showMaplistSortingWindow" style="Icons64x64_1" substyle="Maximize"/>';
			$xml .= '<quad posn="3.7 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$xml .= '<quad posn="3.5 -1.2 0.14" sizen="2.6 2.6" style="UIConstructionSimple_Buttons" substyle="Validate"/>';

			$xml .= '<quad posn="6.6 -1 0.12" sizen="3 3" action="showMaplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';
		$xml .= '</frame>';


		$xml .= $this->templates['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMapAuthorlistWindow ($page, $player) {

		// Get the total of authors
		$totalauthors = ((count($this->cache['MapAuthors']) < 5000) ? count($this->cache['MapAuthors']) : 5000);

		// Determind the maxpages
		$maxpages = ceil($totalauthors / 80);
		if ($page > $maxpages) {
			$page = $maxpages - 1;
		}

		$buttons = '';

		// Filter Buttons
		$buttons .= '<frame posn="44.75 -53.3 0.04">';
			$buttons .= '<quad posn="0 -1 0.12" sizen="3 3" action="showMaplistFilterWindow" style="Icons64x64_1" substyle="Maximize"/>';

			$buttons .= '<quad posn="3.3 -1 0.12" sizen="3 3" action="showMaplistSortingWindow" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="3.7 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="3.5 -1.2 0.14" sizen="2.6 2.6" style="UIConstructionSimple_Buttons" substyle="Validate"/>';

			$buttons .= '<quad posn="6.6 -1 0.12" sizen="3 3" action="showMaplistFilterWindow" style="Icons64x64_1" substyle="ToolUp"/>';
		$buttons .= '</frame>';


		// Frame for Previous-/Next-Buttons
		$buttons .= '<frame posn="52.05 -53.3 0.04">';

		// Previous button
		if ($page > 0) {
			// First
			$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="7.35 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if ( ($page < 250) && ($totalauthors > 80) && (($page + 1) < (ceil($totalauthors / 80))) ) {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';


		// Create Windowtitle depending on the $this->cache['MapAuthors']
		if (count($this->cache['MapAuthors']) == 0) {
			$title = 'Select Mapauthor for filtering the Maplist';
		}
		else {
			$title = 'Select Mapauthor for filtering the Maplist   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalauthors, 0) . (($totalauthors == 1) ? ' Author' : ' Authors');
		}

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'NewTrack',
				$title,
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);


		$line_height = 2.34;
		$line = 0;
		$author_count = 1;
		$offset = 0;
		$xml .= '<frame posn="3.1 -5 0">';
		for ($i = ($page * 80); $i < (($page * 80) + 80); $i ++) {

			// Is there a Author?
			if ( !isset($this->cache['MapAuthors'][$i]) ) {
				break;
			}

			$xml .= '<quad posn="'. (0 + $offset) .' -'. ($line_height * $line + 0.9) .' 0.10" sizen="17.75 2.4" action="showMaplistWindowFilterAuthor'. sprintf("%02d", $author_count) .'" style="Bgs1InRace" substyle="BgCard"/>';
			$xml .= '<label posn="'. (1 + $offset) .' -'. ($line_height * $line + 1.5) .' 0.11" sizen="16.75 0" textsize="1" scale="0.9" textcolor="05CF" text="'. $this->cache['MapAuthors'][$i] .'"/>';

			$line ++;

			// Reset lines
			if ($line >= 20) {
				$offset += 19.05;
				$line = 0;
			}

			$author_count++;
		}
		$xml .= '</frame>';

		$xml .= $this->templates['WINDOW']['FOOTER'];
		return array(
			'xml'		=> $xml,
			'maxpage'	=> ($maxpages - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTopNationsWindow ($page) {
		global $aseco;

		if ( count($this->scores['TopNations']) > 0) {
			// Get the total of records
			$totalentries = count($this->scores['TopNations']);

			// Determind the maxpages
			$maxpages = ceil($totalentries / 100);
			if ($page > $maxpages) {
				$page = $maxpages - 1;
			}

			// Frame for Previous-/Next-Buttons
			$buttons = '<frame posn="52.05 -53.3 0.04">';
			$buttons .= '<quad posn="3.45 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';

			// Previous button
			if ($page > 0) {
				// First
				$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="7.34 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
				$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

				// Previous (-5)
				$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
				$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

				// Previous (-1)
				$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			}
			else {
				// First
				$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Previous (-5)
				$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Previous (-1)
				$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			}

			// Next button (display only if more pages to display)
			if (($page + 1) < $maxpages) {
				// Next (+1)
				$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

				// Next (+5)
				$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
				$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

				// Last
				$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
				$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
			}
			else {
				// Next (+1)
				$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Next (+5)
				$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Last
				$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			}
			$buttons .= '</frame>';


			// Create Windowtitle
			if (count($this->scores['TopNations']) == 0) {
				$title = $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['TITLE'][0];
			}
			else {
				$title = $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['TITLE'][0] .'   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalentries, 0) . (($totalentries == 1) ? ' Entry' : ' Entries');
			}

			$xml = str_replace(
				array(
					'%icon_style%',
					'%icon_substyle%',
					'%window_title%',
					'%prev_next_buttons%'
				),
				array(
					$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_STYLE'][0],
					$this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_SUBSTYLE'][0],
					$title,
					$buttons
				),
				$this->templates['WINDOW']['HEADER']
			);

			$xml .= '<frame posn="3.2 -6.5 1">';
			$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

			$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
			$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
			$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
			$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

			$entry = 1;
			$line = 0;
			$offset = 0;
			foreach ($this->scores['TopNations'] as $item) {
				$xml .= '<label posn="'. (2.75 + $offset) .' -'. (1.83 * $line) .' 0.03" sizen="2.5 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['count'] .'"/>';
				$xml .= '<quad posn="'. (3.5 + $offset) .' '. (($line == 0) ? 0.3 : -(1.83 * $line - 0.3)) .' 0.03" sizen="2 2" image="file://Skins/Avatars/Flags/'. (($item['nation'] == 'OTH') ? 'other' : $item['nation']) .'.dds"/>';
				$xml .= '<label posn="'. (6.2 + $offset) .' -'. (1.83 * $line) .' 0.03" sizen="11.2 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $aseco->country->iocToCountry($item['nation']) .'"/>';

				$line ++;
				$entry ++;

				// Reset lines
				if ($line >= 25) {
					$offset += 19.05;
					$line = 0;
				}

				// Display max. 100 entries, count start from 1
				if ($entry >= 101) {
					break;
				}
			}
			$xml .= '</frame>';

			$xml .= $this->templates['WINDOW']['FOOTER'];
			return array(
				'xml'		=> $xml,
				'maxpage'	=> ($maxpages - 1),
			);

		}
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTopContinentsWindow () {
		global $aseco;

		if ( count($this->scores['TopContinents']) > 0) {
			$buttons = '<frame posn="52.05 -53.3 0.04">';
			$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="showToplistWindow" style="Icons64x64_1" substyle="ToolUp"/>';
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			$buttons .= '</frame>';

			$xml = str_replace(
				array(
					'%icon_style%',
					'%icon_substyle%',
					'%window_title%',
					'%prev_next_buttons%'
				),
				array(
					$this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['ICON_STYLE'][0],
					$this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['ICON_SUBSTYLE'][0],
					$this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['TITLE'][0],
					$buttons
				),
				$this->templates['WINDOW']['HEADER']
			);

			// Get the Continent counts
			$ccounts = array(
				'Europe'	=> 0,
				'Africa'	=> 0,
				'Asia'		=> 0,
				'Middle East'	=> 0,
				'North America'	=> 0,
				'South America'	=> 0,
				'Oceania'	=> 0,
			);
			foreach ($this->scores['TopContinents'] as $item) {
				$ccounts[$item['continent']] = $item['count'];
			}

			// Worldmap
			$xml .= '<frame posn="1 -5 0.01">';
			$xml .= '<quad posn="5 -5 0.02" sizen="65 39.2" image="'. $this->config['IMAGES'][0]['WORLDMAP'][0] .'"/>';
			$xml .= '</frame>';

			// Europe
			$xml .= '<frame posn="26 -6 0.01">';
			$xml .= '<quad posn="16.2 0 0.03" sizen="0.1 17" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['EUROPE'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OEUROPE"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['Europe'] .' '. (($ccounts['Europe'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// Asia
			$xml .= '<frame posn="55 -7 0.01">';
			$xml .= '<quad posn="-0.2 0 0.03" sizen="0.1 15" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['ASIA'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OASIA"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['Asia'] .' '. (($ccounts['Asia'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// Oceania
			$xml .= '<frame posn="61.5 -28 0.01">';
			$xml .= '<quad posn="-0.2 0 0.03" sizen="0.1 12" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['OCEANIA'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OOCEANIA"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['Oceania'] .' '. (($ccounts['Oceania'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// North America
			$xml .= '<frame posn="4 -8 0.01">';
			$xml .= '<quad posn="16.2 0 0.03" sizen="0.1 15" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['NORTH_AMERICA'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$ONORTH AMERICA"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['North America'] .' '. (($ccounts['North America'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// South America
			$xml .= '<frame posn="5 -36 0.01">';
			$xml .= '<quad posn="0 0.2 0.03" sizen="21 0.1" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['SOUTH_AMERICA'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OSOUTH AMERICA"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['South America'] .' '. (($ccounts['South America'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// Africa
			$xml .= '<frame posn="25 -47.5 0.01">';
			$xml .= '<quad posn="16.2 14 0.03" sizen="0.1 19" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['AFRICA'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OAFRICA"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['Africa'] .' '. (($ccounts['Africa'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			// Middle East
			$xml .= '<frame posn="48 -46.5 0.01">';
			$xml .= '<quad posn="-0.2 18 0.03" sizen="0.1 23" bgcolor="999F"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="16 5" bgcolor="0009"/>';
			$xml .= '<quad posn="0.5 -0.5 0.04" sizen="4.05 4.05" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0]['MIDDLE_EAST'][0] .'"/>';
			$xml .= '<label posn="5.2 -1 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="$OMIDDLE EAST"/>';
			$xml .= '<label posn="5.2 -2.8 0.04" sizen="16 2" fontsize="1" scale="0.6" textcolor="FFFF" text="'. $ccounts['Middle East'] .' '. (($ccounts['Middle East'] == 1) ? 'Player' : 'Players') .'"/>';
			$xml .= '</frame>';

			$xml .= $this->templates['WINDOW']['FOOTER'];
			return $xml;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildManiaExchangeMapInfoWindow () {
		global $aseco;

		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'Icons128x128_1',
				'LoadTrack',
				'Mania-Exchange Map Info',
				''
			),
			$this->templates['WINDOW']['HEADER']
		);

		$xml .= '<frame posn="0.7 0 0">';	// BEGIN: Main frame
		$xml .= '<quad posn="2.5 -5.7 0.03" sizen="32.2 24.2" bgcolor="FFF9"/>';
		$xml .= '<label posn="7.5 -16.5 0.04" sizen="25 2" textsize="1" text="Press DEL if can not see an Image here!"/>';
		$xml .= '<quad posn="2.6 -5.8 0.50" sizen="32 24" image="'. $aseco->handleSpecialChars($aseco->server->maps->current->mx->imageurl .'?.jpg') .'"/>';
		$xml .= '<label posn="2.9 -31 0.04" sizen="32 3" textsize="3" text="$S'. $this->cache['Map']['Current']['name'] .'"/>';
		$xml .= '<quad posn="2.9 -33.5 0.04" sizen="2.2 2.2" image="file://Skins/Avatars/Flags/'. (strtoupper($this->cache['Map']['Current']['author_nation']) == 'OTH' ? 'other' : $this->cache['Map']['Current']['author_nation']) .'.dds"/>';
		$xml .= '<label posn="5.8 -33.8 0.04" sizen="32 2" textsize="2" scale="0.9" text="by '. $this->cache['Map']['Current']['author'] .'"/>';

		$date_time = $aseco->server->maps->current->mx->uploaded;
		if ($aseco->server->maps->current->mx->uploaded != $aseco->server->maps->current->mx->updated) {
			$date_time = $aseco->server->maps->current->mx->updated;
		}
		$xml .= '<label posn="2.9 -36 0.04" sizen="18 1.5" textsize="1" scale="0.8" text="from '. preg_replace('/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})\.\d+$/', "\\1 \\2", $date_time) .'"/>';
		$xml .= '<label posn="20.9 -36 0.04" sizen="18 1.5" textsize="1" scale="0.8" text="MX-ID: '. $aseco->server->maps->current->mx->id .'"/>';

		// Author comment
		if ($aseco->server->maps->current->mx->acomment != '') {
			$acomment = $aseco->server->maps->current->mx->acomment;

			// Replace <br> with LF
			$acomment = str_ireplace('<br>' , LF, $acomment);
			$acomment = str_ireplace('<br />' , LF, $acomment);

			// Remove BB Code
			$acomment = preg_replace('/\[.*?\]/i', '', $acomment);
			$acomment = preg_replace('/\[\/.*?\]/i', '', $acomment);

			// Remove (simple) HTML Code
			$acomment = preg_replace('/\<.*?\>/i', '', $acomment);
			$acomment = preg_replace('/\<\/.*?\>/i', '', $acomment);

			// Make URL clickable
			$acomment = preg_replace('#(^|[^\"=]{1})(http://|https://|ftp://)([^\s<>]+)([\s\n<>]|$)#sm', "$1\$L[$2$3]$2$3\$L$4", $acomment);

			$xml .= '<label posn="2.9 -38.2 0.04" sizen="61 16" textsize="1" scale="0.9" autonewline="1" maxline="8" text="'. $aseco->handleSpecialChars($acomment) .'"/>';
		}

		// Times
		$xml .= '<frame posn="38.6 -23.5 0">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$xml .= '<quad posn="0 7.2 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalNadeo"/>';
		$xml .= '<quad posn="0 4.8 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalGold"/>';
		$xml .= '<quad posn="0 2.5 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalSilver"/>';
		$xml .= '<quad posn="0 0.2 0.1" sizen="2 2" halign="right" style="MedalsBig" substyle="MedalBronze"/>';
		$xml .= '<quad posn="0.2 -1.8 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Advanced"/>';
		$xml .= '<quad posn="0.2 -4.1 0.1" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Manialink"/>';

		$xml .= '<label posn="0.5 6.9 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['authortime'] .'"/>';
		$xml .= '<label posn="0.5 4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['goldtime'] .'"/>';
		$xml .= '<label posn="0.5 2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['silvertime'] .'"/>';
		$xml .= '<label posn="0.5 0 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['bronzetime'] .'"/>';
		$xml .= '<label posn="0.5 -2.3 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['environment'] .'"/>';
		$xml .= '<label posn="0.5 -4.6 0.1" sizen="8 2" text="'. $this->cache['Map']['Current']['mood'] .'"/>';
		$xml .= '</frame>';

		// MX Mapinfos
		$xml .= '<frame posn="45.5 -23.5 0">';
		$xml .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$xml .= '<label posn="0 6.9 0.1" sizen="5 2.2" text="Type:"/>';
		$xml .= '<label posn="0 4.6 0.1" sizen="5 2" text="Style:"/>';
		$xml .= '<label posn="0 2.3 0.1" sizen="5 2" text="Difficult:"/>';
		$xml .= '<label posn="0 0 0.1" sizen="5 2" text="Routes:"/>';
		$xml .= '<label posn="0 -2.3 0.1" sizen="5 2.6" text="Awards:"/>';
		$xml .= '<label posn="0 -4.6 0.1" sizen="5 2.6" text="Section:"/>';

		$xml .= '<label posn="5.1 6.9 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->type .'"/>';
		$xml .= '<label posn="5.1 4.6 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->style .'"/>';
		$xml .= '<label posn="5.1 2.3 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->diffic .'"/>';
		$xml .= '<label posn="5.1 0 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->routes .'"/>';
		$xml .= '<label posn="5.1 -2.3 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->awards .'"/>';
		$xml .= '<label posn="5.1 -4.6 0.1" sizen="14.5 2" text=" '. $aseco->server->maps->current->mx->section .'"/>';
		$xml .= '</frame>';


		// MX Top15 Records
		$xml .= '<frame posn="59.65 -5.7 1">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 47" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="0.6 0 0.05" sizen="2.5 2.5" style="Icons128x32_1" substyle="RT_Cup"/>';
		$xml .= '<label posn="3.2 -0.65 0.05" sizen="17.3 0" textsize="1" text="MX Top15 Records"/>';
		$xml .= '<frame posn="0 -2.7 0.04">';	// Entries
		if ( (isset($aseco->server->maps->current->mx->recordlist)) && (count($aseco->server->maps->current->mx->recordlist) > 0) ) {
			$entry = 1;
			$line = 0;
			foreach ($aseco->server->maps->current->mx->recordlist as $item) {
				$xml .= '<label posn="2.1 -'. (1.75 * $line) .' 0.01" sizen="2.65 1.7" halign="right" scale="0.9" text="'. $entry .'."/>';
				$xml .= '<label posn="6.1 -'. (1.75 * $line) .' 0.01" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $aseco->formatTime($item['replaytime']) .'"/>';
				$xml .= '<label posn="6.6 -'. (1.75 * $line) .' 0.01" sizen="9.6 1.7" scale="0.9" text="'. $this->handleSpecialChars($item['username']) .'"/>';

				$entry ++;
				$line ++;

				// Display max. 15 entries (thats the max. from MX), count start from 1
				if ($entry >= 16) {
					break;
				}
			}
		}
		$xml .= '</frame>';	// Entries
		$xml .= '</frame>';


		// MX-Logo
		$xml .= '<frame posn="37.2 -7 1">';
		$xml .= '<format textsize="1" style="TextCardScores2"/>';
		$xml .= '<quad posn="0 0 0.3" sizen="5.5 5.5" image="'. $this->config['IMAGES'][0]['MX_LOGO_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MX_LOGO_FOCUS'][0] .'" url="http://tm.mania-exchange.com/"/>';
		$xml .= '</frame>';

		// Button "Visit Page"
		if ($this->cache['Map']['Current']['pageurl'] != false) {
			$xml .= '<frame posn="45.2 -6.6 0.04">';
			$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['pageurl']) .'" text="VISIT MAP PAGE" style="CardButtonSmall"/>';
			$xml .= '</frame>';
		}

		// Button "Download Map"
		if ($this->cache['Map']['Current']['dloadurl'] != false) {
			$xml .= '<frame posn="45.2 -8.8 0.04">';
			$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['dloadurl']) .'" text="DOWNLOAD MAP" style="CardButtonSmall"/>';
			$xml .= '</frame>';
		}

		// Button "Download Replay"
		if ($this->cache['Map']['Current']['replayurl'] != false) {
			$xml .= '<frame posn="45.2 -11 0.04">';
			$xml .= '<label posn="6 0 0.02" sizen="12 2.0" halign="center" textsize="1" scale="0.8" url="'. $aseco->handleSpecialChars($this->cache['Map']['Current']['replayurl']) .'" text="DOWNLOAD REPLAY" style="CardButtonSmall"/>';
			$xml .= '</frame>';
		}

		$xml .= '</frame>';		// END: Main frame

		$xml .= $this->templates['WINDOW']['FOOTER'];

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMusiclistWindow ($page, $caller) {
		global $aseco;

		// Get the total of songs
		$totalsongs = ((count($this->cache['MusicServerPlaylist']) < 1900) ? count($this->cache['MusicServerPlaylist']) : 1900);

		if ($totalsongs > 0) {

			// Determind the maxpages
			$maxpages = ceil($totalsongs / 20);
			if ($page > $maxpages) {
				$page = $maxpages - 1;
			}

			// Button "Drop current juke'd Song"
			$buttons = '<frame posn="35.55 -53.3 0.04">';
			$buttons .= '<quad posn="0 -1 0.12" sizen="3 3" action="dropCurrentJukedSong" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="0.4 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="0.3 -1.3 0.14" sizen="2.4 2.4" style="Icons128x32_1" substyle="Settings"/>';
			$buttons .= '<label posn="0.81 -1.25 0.15" sizen="6 6" textsize="1" style="TextCardRaceRank" text="$S$W$O$F00/"/>';
			$buttons .= '</frame>';

			// Frame for Previous-/Next-Buttons
			$buttons .= '<frame posn="52.05 -53.3 0.04">';

			// Previous button
			if ($page > 0) {
				// First
				$buttons .= '<quad posn="6.75 -1 0.12" sizen="3 3" action="WindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="7.15 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="7.34 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
				$buttons .= '<quad posn="7.55 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';

				// Previous (-5)
				$buttons .= '<quad posn="10.05 -1 0.12" sizen="3 3" action="WindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="10.45 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="9.9 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
				$buttons .= '<quad posn="10.6 -1.2 0.15" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';

				// Previous (-1)
				$buttons .= '<quad posn="13.35 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="13.75 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="13.55 -1.2 0.14" sizen="2.5 2.6" style="Icons64x64_1" substyle="ShowLeft2"/>';
			}
			else {
				// First
				$buttons .= '<quad posn="6.75 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Previous (-5)
				$buttons .= '<quad posn="10.05 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Previous (-1)
				$buttons .= '<quad posn="13.35 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			}

			// Next button (display only if more pages to display)
			if ( ($page < 95) && ($totalsongs > 20) && (($page + 1) < $maxpages) ) {
				// Next (+1)
				$buttons .= '<quad posn="16.65 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="17.05 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="16.85 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

				// Next (+5)
				$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="19.8 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
				$buttons .= '<quad posn="20.5 -1.25 0.15" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';

				// Last
				$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
				$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
				$buttons .= '<quad posn="23.1 -1.25 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
				$buttons .= '<quad posn="25 -1.6 0.15" sizen="0.4 1.7" bgcolor="CCCF"/>';
			}
			else {
				// Next (+1)
				$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Next (+5)
				$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

				// Last
				$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
			}
			$buttons .= '</frame>';


			$xml = str_replace(
				array(
					'%icon_style%',
					'%icon_substyle%',
					'%window_title%',
					'%prev_next_buttons%'
				),
				array(
					$this->config['MUSIC_WIDGET'][0]['ICON_STYLE'][0],
					$this->config['MUSIC_WIDGET'][0]['ICON_SUBSTYLE'][0],
					'Choose the next Song   |   Page '. ($page+1) .'/'. $maxpages .'   |   '. $this->formatNumber($totalsongs, 0) . (($totalsongs == 1) ? ' Song' : ' Songs'),
					$buttons
				),
				$this->templates['WINDOW']['HEADER']
			);

			$line = 0;
			$offset = 0;

			$xml .= '<frame posn="3.2 -5.7 1">';
			for ($i = ($page * 20); $i < (($page * 20) + 20); $i ++) {

				// Is there a song?
				if ( !isset($this->cache['MusicServerPlaylist'][$i]) ) {
					break;
				}

				// Get filename of Song
				$song = &$this->cache['MusicServerPlaylist'][$i];

				// Find the Player who has juked this Song (if it is juked)
				$login = false;
				$juked = false;
				if ( isset($aseco->plugins['PluginMusicServer']) ) {
					foreach ($aseco->plugins['PluginMusicServer']->jukebox as $pl => $songid) {
						if ($song['SongId'] == $songid) {
							$login = $pl;
							$juked = true;
							break;
						}
					}
					unset($songid);
				}

				$xml .= '<frame posn="'. $offset .' -'. (9.45 * $line) .' 1">';
				if ($juked == false) {
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					if ( ($this->config['CurrentMusicInfos']['Artist'] == $song['Artist']) && ($this->config['CurrentMusicInfos']['Title'] == $song['Title']) ) {
						// Current Song
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="'. (-2100 - $song['SongId']) .'" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_PLUS_FOCUS'][0] .'"/>';
						$xml .= '<quad posn="0.4 -0.36 0.03" sizen="16.95 2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
						$xml .= '<format textsize="1" textcolor="FFF8"/>';
					}
					else {
						// Default
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="'. (-2100 - $song['SongId']) .'" image="'. $this->config['IMAGES'][0]['WIDGET_PLUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_PLUS_FOCUS'][0] .'"/>';
						$xml .= '<quad posn="0.4 -0.36 0.03" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
						$xml .= '<format textsize="1" textcolor="FFFF"/>';
					}
					$xml .= '<label posn="3.2 -0.65 0.04" sizen="17.3 0" textsize="1" text="Song #'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.6 0 0.04" sizen="2.5 2.5" style="'. $this->config['MUSIC_WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MUSIC_WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="15.85 2" scale="1" text="'. $song['Title'] .'"/>';
					$xml .= '<label posn="1 -4.5 0.04" sizen="17.15 2" scale="0.9" text="by '. $song['Artist'] .'"/>';
				}
				else {
					// Juked Song
					$xml .= '<format textsize="1" textcolor="FFF8"/>';
					$xml .= '<quad posn="0 0 0.02" sizen="17.75 9.2" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
					if ($login == $caller) {
						$xml .= '<quad posn="14.15 -5.65 0.03" sizen="4 4" action="dropCurrentJukedSong" image="'. $this->config['IMAGES'][0]['WIDGET_MINUS_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_MINUS_FOCUS'][0] .'"/>';
					}
					$xml .= '<quad posn="0.27 -0.34 0.04" sizen="17.4 2.2" style="BgsButtons" substyle="BgButtonMediumSpecial"/>';
					$xml .= '<label posn="3.2 -0.65 0.04" sizen="17.3 0" textsize="1" textcolor="000F" text="Song #'. ($i+1) .'"/>';
					$xml .= '<quad posn="0.6 0 0.04" sizen="2.5 2.5" style="'. $this->config['MUSIC_WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MUSIC_WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
					$xml .= '<label posn="1 -2.7 0.04" sizen="15.85 2" scale="1" text="'. $aseco->stripColors($song['Title'], true) .'"/>';
					$xml .= '<label posn="1 -4.5 0.04" sizen="17.15 2" scale="0.9" text="by '. $aseco->stripColors($song['Artist'], true) .'"/>';
				}

				// Mark current Song
				if ( ($this->config['CurrentMusicInfos']['Artist'] == $song['Artist']) && ($this->config['CurrentMusicInfos']['Title'] == $song['Title']) ) {
					$xml .= '<quad posn="16 0 0.06" sizen="2.5 2.5" style="BgRaceScore2" substyle="Fame"/>';
				}
				$xml .= '<quad posn="0.9 -6.9 0.05" sizen="5.2 1.7" url="http://www.amazon.com/gp/search?ie=UTF8&amp;keywords='. urlencode($aseco->stripColors(str_replace('&amp;', '&', $song['Artist']), true)) .'&amp;tag=undefde-20&amp;index=digital-music&amp;linkCode=ur2&amp;camp=1789&amp;creative=9325" image="http://static.undef.name/ingame/records-eyepiece/logo-amazon-normal.png" imagefocus="http://static.undef.name/ingame/records-eyepiece/logo-amazon-focus.png"/>';
				$xml .= '</frame>';

				$line ++;

				// Reset lines
				if ($line >= 5) {
					$offset += 19.05;
					$line = 0;
				}
			}
			$xml .= '</frame>';

			$xml .= $this->templates['WINDOW']['FOOTER'];
			return array(
				'xml'		=> $xml,
				'maxpage'	=> ($maxpages - 1),
			);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildMapWidget ($state = 'race') {
		global $aseco;

		if ($this->config['MAP_WIDGET'][0]['ENABLED'][0] == true) {

			$xml = false;
			if ($state == 'race') {

				// Set the right Icon and Title position
				$position = (($this->config['MAP_WIDGET'][0]['RACE'][0]['POS_X'][0] < 0) ? 'right' : 'left');

				if ($position == 'right') {
					$imagex	= ($this->config['Positions'][$position]['image_open']['x'] + ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 15.5));
					$iconx	= ($this->config['Positions'][$position]['icon']['x'] + ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 15.5));
					$titlex	= ($this->config['Positions'][$position]['title']['x'] + ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 15.5));
				}
				else {
					$imagex	= $this->config['Positions'][$position]['image_open']['x'];
					$iconx	= $this->config['Positions'][$position]['icon']['x'];
					$titlex	= $this->config['Positions'][$position]['title']['x'];
				}


				// Create the MapWidget at Race
				$xml = str_replace(
					array(
						'%manialinkid%',
						'%actionid%',
						'%posx%',
						'%posy%',
						'%image_open_pos_x%',
						'%image_open_pos_y%',
						'%image_open%',
						'%posx_icon%',
						'%posy_icon%',
						'%posx_title%',
						'%posy_title%',
						'%halign%',
						'%mapname%',
						'%authortime%',
						'%author%',
						'%author_nation%'
					),
					array(
						'MapWidget',
						'showLastCurrentNextMapWindow',
						$this->config['MAP_WIDGET'][0]['RACE'][0]['POS_X'][0],
						$this->config['MAP_WIDGET'][0]['RACE'][0]['POS_Y'][0],
						$imagex,
						-5.33,
						$this->config['Positions'][$position]['image_open']['image'],
						$iconx,
						$this->config['Positions'][$position]['icon']['y'],
						$titlex,
						$this->config['Positions'][$position]['title']['y'],
						$this->config['Positions'][$position]['title']['halign'],
						$this->cache['Map']['Current']['name'],
						$this->cache['Map']['Current']['authortime'],
						$this->cache['Map']['Current']['author'],
						(strtoupper($this->cache['Map']['Current']['author_nation']) == 'OTH' ? 'other' : $this->cache['Map']['Current']['author_nation'])
					),
					$this->templates['MAP_WIDGET']['RACE']['HEADER']
				);
				$xml .= $this->templates['MAP_WIDGET']['RACE']['FOOTER'];

			}
			else if ($state == 'score') {

				// Setup defaults
				$type = $this->config['MAP_WIDGET'][0]['SCORE'][0]['DISPLAY'][0];
				$title = $this->config['MAP_WIDGET'][0]['TITLE'][0]['NEXT_MAP'][0];
				$icon = array(
					$this->config['MAP_WIDGET'][0]['ICONS'][0]['NEXT_MAP'][0]['ICON_STYLE'][0],
					$this->config['MAP_WIDGET'][0]['ICONS'][0]['NEXT_MAP'][0]['ICON_SUBSTYLE'][0]
				);

				// Check for changing display
				if ($type == 'Current') {
					$title = $this->config['MAP_WIDGET'][0]['TITLE'][0]['CURRENT_MAP'][0];
					$icon = array(
						$this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_STYLE'][0],
						$this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_SUBSTYLE'][0]
					);
				}

				// Create the MapWidget at Score
				$xml = str_replace(
					array(
						'%manialinkid%',
						'%posx%',
						'%posy%',
						'%icon_style%',
						'%icon_substyle%',
						'%title%',
						'%nextmapname%',
						'%nextauthortime%',
						'%nextauthor%',
						'%nextauthor_nation%',
						'%nextenv%',
						'%nextmood%',
						'%nextgoldtime%',
						'%nextsilvertime%',
						'%nextbronzetime%'
					),
					array(
						'MapWidget',
						$this->config['MAP_WIDGET'][0]['SCORE'][0]['POS_X'][0],
						$this->config['MAP_WIDGET'][0]['SCORE'][0]['POS_Y'][0],
						$icon[0],
						$icon[1],
						$title,
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['name'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['authortime'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['author'],
						(strtoupper($this->cache['Map'][$type]['author_nation']) == 'OTH' ? 'other' : $this->cache['Map'][$type]['author_nation']),
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['environment'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['mood'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['goldtime'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['silvertime'],
						$this->config['STYLE'][0]['WIDGET_SCORE'][0]['FORMATTING_CODES'][0] . $this->cache['Map'][$type]['bronzetime']
					),
					$this->templates['MAP_WIDGET']['SCORE']['HEADER']
				);
				$xml .= $this->templates['MAP_WIDGET']['SCORE']['FOOTER'];
			}


			if ($xml != false) {
				return $xml;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildCheckpointCountWidget ($checkpoint = -1, $login = false) {
		global $aseco;

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Setup the total count of Checkpoints
		$totalcps = 0;
		if ( ($gamemode == Gameinfo::ROUNDS) || ($gamemode == Gameinfo::TEAM) || ($gamemode == Gameinfo::CUP) ) {
			if ($this->cache['Map']['ForcedLaps'] > 0) {
				$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['ForcedLaps'];
			}
			else if ($this->cache['Map']['NbLaps'] > 0) {
				$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps'];
			}
			else {
				$totalcps = $this->cache['Map']['NbCheckpoints'];
			}
		}
		else if ( ($this->cache['Map']['NbLaps'] > 0) && ($gamemode == Gameinfo::LAPS) ) {
			// In Laps.Script.txt not multilaps Maps are playable, add not 'NbLaps' in this case!
			if ($aseco->server->maps->current->multilap == true) {
				$totalcps = $this->cache['Map']['NbCheckpoints'] * $this->cache['Map']['NbLaps'];
			}
			else {
				$totalcps = $this->cache['Map']['NbCheckpoints'];
			}
		}
		else {
			// All other Gamemodes
			$totalcps = $this->cache['Map']['NbCheckpoints'];
		}


		$xml = $this->templates['CHECKPOINTCOUNTER_WIDGET']['HEADER'];
		$xml .= '<label posn="8 -0.65 0.01" halign="center" textsize="1" scale="0.6" textcolor="FC0F" text="" id="RecordsEyepieceCheckpointLine1"/>';
		$xml .= '<label posn="8 -1.8 0.01" halign="center" textsize="2" scale="0.9" textcolor="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['TEXT_COLOR'][0] .'" text="" id="RecordsEyepieceCheckpointLine2"/>';
		$xml .= '<label posn="8 -1.8 0.01" halign="center" style="TextTitle2Blink" textsize="2" scale="0.9" textcolor="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['TEXT_COLOR'][0] .'" text="" id="RecordsEyepieceCheckpointLine2Blink" ScriptEvents="1" hidden="true"/>';

		$multilapmap = (($this->cache['Map']['Current']['multilap'] == true) ? 'True' : 'False');
		$timeattack = (($gamemode == Gameinfo::TIMEATTACK) ? 'True' : 'False');

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<checkpointcounter_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main() {
	declare CMlControl Container			<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale				= {$this->config['CHECKPOINTCOUNT_WIDGET'][0]['SCALE'][0]};

	declare LabelCheckpointLine1			<=> (Page.GetFirstChild("RecordsEyepieceCheckpointLine1") as CMlLabel);
	declare LabelCheckpointLine2			<=> (Page.GetFirstChild("RecordsEyepieceCheckpointLine2") as CMlLabel);
	declare LabelCheckpointLine2Blink		<=> (Page.GetFirstChild("RecordsEyepieceCheckpointLine2Blink") as CMlLabel);

	declare Integer TotalCheckpoints		= {$totalcps};		// Incl. Finish
	declare Boolean MultilapMap			= {$multilapmap};
	declare Boolean TimeAttack			= {$timeattack};
	declare Integer CurrentLap			= 0;			// Using own CurrentLap instead of Player.CurrentNbLaps
	declare Integer CurrentCheckpoint		= 0;
	declare Integer RefreshInterval			= 500;
	declare Integer RefreshTime			= CurrentTime;

	declare Text MessageCheckpoint			= "CHECKPOINT";
	declare Text MessageWithoutCheckpoints		= "WITHOUT CHECKPOINTS";
	declare Text MessageAllCheckpointsReached	= "ALL CHECKPOINTS REACHED";
	declare Text MessageMapSuccessfully		= "MAP SUCCESSFULLY";
	declare Text MessageFinishNow			= "\$OFinish now!";
	declare Text MessageFinished			= "\$OFinished";
	declare Text MessageFinishedNextLap		= "Finished, next Lap!";
	declare Text MessageWarmUp			= "\$OWarm-Up";

	// Init first view
	if ((TotalCheckpoints-1) == 0) {
		LabelCheckpointLine1.SetText(MessageWithoutCheckpoints);
		LabelCheckpointLine2.Visible = False;
		LabelCheckpointLine2Blink.Visible = True;
		LabelCheckpointLine2Blink.SetText(MessageFinishNow);
	}
	else {
		LabelCheckpointLine1.SetText(MessageCheckpoint);
		LabelCheckpointLine2.SetText("\$O " ^ CurrentCheckpoint ^ " \$Zof\$O " ^ (TotalCheckpoints-1));
	}

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Hide the Widget for Spectators (also temporary one)
		if (InputPlayer.IsSpawned == False) {
			Container.Hide();
			continue;
		}
		else {
			Container.Show();
		}

		if (CurrentTime > RefreshTime) {
			foreach (Player in Players) {
				// Only work on LocalUser
				if (Player.Login != LocalUser.Login) {
					continue;
				}

				declare CheckpointCountWidget_LastCheckpointCount for Player = -1;
				if (CheckpointCountWidget_LastCheckpointCount != Player.CurRace.Checkpoints.count) {
					CheckpointCountWidget_LastCheckpointCount = Player.CurRace.Checkpoints.count;

					if (MultilapMap == True) {
						if (CurrentCheckpoint > (TotalCheckpoints - 1)) {
							CurrentLap += 1;
						}
						CurrentCheckpoint = CheckpointCountWidget_LastCheckpointCount - (CurrentLap * TotalCheckpoints);
					}
					else {
						CurrentCheckpoint = CheckpointCountWidget_LastCheckpointCount;
					}

					// Check for respawn and reset count of current Checkpoint and Laps
					if (CurrentCheckpoint < 0 && Player.CurRace.Checkpoints.count == 0) {
						CurrentCheckpoint = 0;
						CurrentLap = 0;
					}
//					log("CPC: Current CP: " ^ CurrentCheckpoint ^ " of " ^ TotalCheckpoints ^ " on lap " ^ CurrentLap ^", CP-Times: "^ Player.CurRace.Checkpoints);

					if ((CurrentCheckpoint + 1) == TotalCheckpoints) {
						if ((TotalCheckpoints - 1) == 0) {
							LabelCheckpointLine1.SetText(MessageWithoutCheckpoints);
						}
						else {
							LabelCheckpointLine1.SetText(MessageAllCheckpointsReached);
						}
						LabelCheckpointLine2.Visible = False;
						LabelCheckpointLine2Blink.Visible = True;
						LabelCheckpointLine2Blink.SetText(MessageFinishNow);
					}
					else if (CurrentCheckpoint > (TotalCheckpoints - 1)) {
						LabelCheckpointLine1.SetText(MessageMapSuccessfully);
						if ( (MultilapMap == True) && (TimeAttack == True) ) {
							LabelCheckpointLine2.Visible = False;
							LabelCheckpointLine2Blink.Visible = True;
							LabelCheckpointLine2Blink.SetText(MessageFinishedNextLap);
						}
						else {
							LabelCheckpointLine2.Visible = True;
							LabelCheckpointLine2Blink.Visible = False;
							LabelCheckpointLine2.SetText(MessageFinished);
						}
					}
					else {
						LabelCheckpointLine2.Visible = True;
						LabelCheckpointLine2Blink.Visible = False;
						LabelCheckpointLine1.SetText(MessageCheckpoint);
						LabelCheckpointLine2.SetText("\$O" ^ CurrentCheckpoint ^ " \$Zof\$O " ^ (TotalCheckpoints - 1));
					}
				}
			}

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= $this->templates['CHECKPOINTCOUNTER_WIDGET']['FOOTER'];

		if ($login != false) {
			// Send to given Player
			$this->sendManialink($xml, $login, 0);
		}
		else {
			// Send to all Players
			return $xml;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildManialinkActionKeys () {

		$xml  = '<manialink id="ActionKeys" name="ActionKeys">';
		$xml .= '<quad posn="70 70 1" sizen="0 0" action="382009003" actionkey="3"/>';	// ActionKey F7 for toggle RecordWidgets (same id as plugin.fufi.widgets.php)
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildToplistWindowEntry ($data, $logins, $target) {
		global $aseco;

		$xml = '<format textsize="1" textcolor="FFFF"/>';
		$xml .= '<quad posn="0 0 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="14.15 -43.33 0.03" sizen="4 4" action="'. $data['actionid'] .'" image="'. $this->config['IMAGES'][0]['WIDGET_OK_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['WIDGET_OK_FOCUS'][0] .'"/>';
		$xml .= '<quad posn="0.4 -0.36 0.04" sizen="16.95 2" style="BgsPlayerCard" substyle="ProgressBar"/>';
		$xml .= '<quad posn="'. $this->config['Positions']['left']['icon']['x'] .' '. $this->config['Positions']['left']['icon']['y'] .' 0.05" sizen="2.5 2.5" style="'. $data['icon_style'] .'" substyle="'. $data['icon_substyle'] .'"/>';
		$xml .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.05" sizen="17.3 0" textsize="1" text="'. $data['title'] .'"/>';
		if ( count($this->scores[$data['list']]) > 0) {
			$xml .= '<frame posn="0 -2.7 0.04">';	// Entries
			$rank = 1;
			$line = 0;
			foreach ($this->scores[$data['list']] as $item) {
				if ($data['list'] == 'TopNations') {
					$xml .= '<label posn="3.15 -'. (1.75 * $line) .' 0.02" sizen="2.65 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['count'] .'"/>';
					$xml .= '<quad posn="3.9 '. (($line == 0) ? 0.3 : -(1.75 * $line - 0.3)) .' 0.02" sizen="2 2" image="file://Skins/Avatars/Flags/'. (($item['nation'] == 'OTH') ? 'other' : $item['nation']) .'.dds"/>';
					$xml .= '<label posn="6.6 -'. (1.75 * $line) .' 0.02" sizen="11.2 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $aseco->country->iocToCountry($item['nation']) .'"/>';
				}
				else if ($data['list'] == 'TopContinents') {
					$continent = str_replace(' ', '_', strtoupper($item['continent']));

					$xml .= '<label posn="3.15 -'. (1.75 * $line) .' 0.03" sizen="2.5 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['count'] .'"/>';
					$xml .= '<quad posn="3.9 '. (($line == 0) ? 0.3 : -(1.75 * $line - 0.3)) .' 0.03" sizen="2 2" image="'. $this->config['IMAGES'][0]['CONTINENTS'][0][$continent][0] .'"/>';
					$xml .= '<label posn="6.6 -'. (1.75 * $line) .' 0.03" sizen="11.2 1.7" scale="0.9" text="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['FORMATTING_CODES'][0] . $item['continent'] .'"/>';
				}
				else {
					// Mark current connected Players
					if (isset($item['login'])) {
						if ($item['login'] == $target) {
							if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] != '') {
								$xml .= '<quad posn="0.4 '. (((1.75 * $line - 0.2) > 0) ? -(1.75 * $line - 0.2) : 0.2) .' 0.01" sizen="16.95 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_BACKGROUND'][0] .'"/>';
							}
							else {
								$xml .= '<quad posn="0.4 '. (((1.75 * $line - 0.2) > 0) ? -(1.75 * $line - 0.2) : 0.2) .' 0.01" sizen="16.95 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_SELF_SUBSTYLE'][0] .'"/>';
							}
						}
						else if (in_array($item['login'], $logins)) {
							if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] != '') {
								$xml .= '<quad posn="0.4 '. (((1.75 * $line - 0.2) > 0) ? -(1.75 * $line - 0.2) : 0.2) .' 0.01" sizen="16.95 1.8" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_BACKGROUND'][0] .'"/>';
							}
							else {
								$xml .= '<quad posn="0.4 '. (((1.75 * $line - 0.2) > 0) ? -(1.75 * $line - 0.2) : 0.2) .' 0.01" sizen="16.95 1.8" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['HIGHLITE_OTHER_SUBSTYLE'][0] .'"/>';
							}
						}
					}
					$xml .= '<label posn="2.6 -'. (1.75 * $line) .' 0.02" sizen="2 1.7" halign="right" scale="0.9" text="'. $rank .'."/>';
					$xml .= '<label posn="6.4 -'. (1.75 * $line) .' 0.02" sizen="4 1.7" halign="right" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['SCORES'][0] .'" text="'. $item[$data['fieldnames'][0]] .'"/>';
					$xml .= '<label posn="6.9 -'. (1.75 * $line) .' 0.02" sizen="11.2 1.7" scale="0.9" text="'. $item[$data['fieldnames'][1]] .'"/>';
				}

				$line ++;
				$rank ++;

				// Display max. 26 entries, count start from 1
				if ($rank >= 26) {
					break;
				}
			}
			unset($item);
			$xml .= '</frame>';			// Entries
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildToplistWindow ($page = 0, $login) {
		global $aseco;

		// Get current Gamemode
		$gamemode = $aseco->server->gameinfo->mode;

		// Add all connected PlayerLogins
		$players = array();
		foreach ($aseco->server->players->player_list as $player) {
			if ($player->login != $login) {
				$players[] = $player->login;
			}
		}


		$toplists = array();

		// DedimaniaRecords
		if ($this->config['DEDIMANIA_RECORDS'][0]['GAMEMODE'][0][$gamemode][0]['ENABLED'][0] == true) {
			// 6 = Stunts and unsupported by Dedimania
			$toplists[] = array(
				'actionid'	=> 'showDedimaniaRecordsWindow',
				'icon_style'	=> $this->config['DEDIMANIA_RECORDS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['DEDIMANIA_RECORDS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['DEDIMANIA_RECORDS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'DedimaniaRecords',
			);
		}

		// LocalRecords
		$toplists[] = array(
			'actionid'	=> 'showLocalRecordsWindow',
			'icon_style'	=> $this->config['LOCAL_RECORDS'][0]['ICON_STYLE'][0],
			'icon_substyle'	=> $this->config['LOCAL_RECORDS'][0]['ICON_SUBSTYLE'][0],
			'title'		=> $this->config['LOCAL_RECORDS'][0]['TITLE'][0],
			'fieldnames'	=> array('score', 'nickname'),
			'list'		=> 'LocalRecords',
		);

		// LiveRankingsWindow
		$toplists[] = array(
			'actionid'	=> 'showLiveRankingsWindow',
			'icon_style'	=> $this->config['LIVE_RANKINGS'][0]['ICON_STYLE'][0],
			'icon_substyle'	=> $this->config['LIVE_RANKINGS'][0]['ICON_SUBSTYLE'][0],
			'title'		=> $this->config['LIVE_RANKINGS'][0]['TITLE'][0],
			'fieldnames'	=> array('score', 'nickname'),
			'list'		=> 'LiveRankings',
		);

		// TopRanks
		if ( count($this->scores['TopRankings']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopRankingsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_RANKINGS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopRankings',
			);
		}

		// TopWinners
		if ( count($this->scores['TopWinners']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopWinnersWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNERS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopWinners',
			);
		}

		// MostRecords
		if ( count($this->scores['MostRecords']) ) {
			$toplists[] = array(
				'actionid'	=> 'showMostRecordsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['MOST_RECORDS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'MostRecords',
			);
		}

		// MostFinished
		if ( count($this->scores['MostFinished']) ) {
			$toplists[] = array(
				'actionid'	=> 'showMostFinishedWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['MOST_FINISHED'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'MostFinished',
			);
		}

		// TopPlaytime
		if ( count($this->scores['TopPlaytime']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopPlaytimeWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_PLAYTIME'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopPlaytime',
			);
		}

		// TopActivePlayers
		if ( count($this->scores['TopActivePlayers']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopActivePlayersWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_ACTIVE_PLAYERS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopActivePlayers',
			);
		}

		// TopRoundscore
		if ( count($this->scores['TopRoundscore']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopRoundscoreWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_ROUNDSCORE'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_ROUNDSCORE'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_ROUNDSCORE'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopRoundscore',
			);
		}

		// TopVisitors
		if ( count($this->scores['TopVisitors']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopVisitorsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_VISITORS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopVisitors',
			);
		}

		// TopNations
		if ( count($this->scores['TopNations']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopNationsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_NATIONS'][0]['TITLE'][0],
				'fieldnames'	=> array('count', 'nation'),
				'list'		=> 'TopNations',
			);
		}

		// TopContinents
		if ( count($this->scores['TopContinents']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopContinentsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_CONTINENTS'][0]['TITLE'][0],
				'fieldnames'	=> array('count', 'continent'),
				'list'		=> 'TopContinents',
			);
		}

		// TopVoters
		if ( count($this->scores['TopVoters']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopVotersWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_VOTERS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopVoters',
			);
		}

		// TopMaps
		if ( count($this->scores['TopMaps']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopMapsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_MAPS'][0]['TITLE'][0],
				'fieldnames'	=> array('karma', 'map'),
				'list'		=> 'TopMaps',
			);
		}

		// TopDonators
		if ( count($this->scores['TopDonators']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopDonatorsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_DONATORS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_DONATORS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_DONATORS'][0]['TITLE'][0],
				'fieldnames'	=> array('score', 'nickname'),
				'list'		=> 'TopDonators',
			);
		}

		// TopWinnigPayout
		if ( count($this->scores['TopWinningPayouts']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopWinningPayoutWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_WINNING_PAYOUTS'][0]['TITLE'][0],
				'fieldnames'	=> array('won', 'nickname'),
				'list'		=> 'TopWinningPayouts',
			);
		}

		// TopBetwins
		if ( count($this->scores['TopBetwins']) ) {
			$toplists[] = array(
				'actionid'	=> 'showTopBetwinsWindow',
				'icon_style'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ICON_STYLE'][0],
				'icon_substyle'	=> $this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ICON_SUBSTYLE'][0],
				'title'		=> $this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['TITLE'][0],
				'fieldnames'	=> array('won', 'nickname'),
				'list'		=> 'TopBetwins',
			);
		}

		if ($page >= ceil(count($toplists) / 4)) {
			return;
		}


		// Frame for Previous/Next Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';
		$buttons .= '<quad posn="16.65 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

		// Previous button
		if ($page > 0) {
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="20.15 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if ($page < ceil(count($toplists) / 4 - 1)) {
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.45 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
		}
		else {
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		$buttons .= '</frame>';


		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'BgRaceScore2',
				'LadderRank',
				'Top Rankings   |   Page '. ($page+1) .'/'. ceil(count($toplists) / 4),
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);
		$xml .= '<frame posn="3.2 -5.7 1">';	// Content Window

		// Build the Content of this Page
		$pos = 0;
		foreach (range(($page*4),($page*4+3)) as $id) {
			if ( isset($toplists[$id]) ) {
				$xml .= '<frame posn="'. (19.05 * $pos) .' 0 1">';
				$xml .= $this->buildToplistWindowEntry($toplists[$id], $players, $login);
				$xml .= '</frame>';
				$pos ++;
			}
		}

		$xml .= '</frame>';	// Content Window
		$xml .= $this->templates['WINDOW']['FOOTER'];
		return array(
			'xml'		=> $xml,
			'maxpage'	=> ceil(count($toplists) / 4 - 1),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildHelpWindow ($page) {
		global $aseco;

		// Frame for Previous/Next Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';

		// Previous button
		if ($page > 0) {
			$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="WindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="20.15 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		// Next button (display only if more pages to display)
		if ($page < 2) {	// Currently only 3 page there
			$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="WindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
			$buttons .= '<quad posn="23.45 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
		}
		else {
			$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		$buttons .= '</frame>';


		$xml = str_replace(
			array(
				'%icon_style%',
				'%icon_substyle%',
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'BgRaceScore2',
				'LadderRank',
				'$L[http://www.undef.name/UASECO/Records-Eyepiece.php]Records-Eyepiece/'. $this->getVersion() .'$L for UASECO',
				$buttons
			),
			$this->templates['WINDOW']['HEADER']
		);

		// Set the content
		$xml .= '<frame posn="3 -6 0.01">';
		$xml .= '<quad posn="57 0 0.11" sizen="18 46.5" image="http://static.undef.name/ingame/records-eyepiece/welcome-records-eyepiece-normal.jpg" imagefocus="http://static.undef.name/ingame/records-eyepiece/welcome-records-eyepiece-focus.jpg" url="http://www.undef.name/UASECO/Records-Eyepiece.php"/>';

		if ($page == 0) {
			// Begin Help for Players

			// Command "/eyepiece"
			$xml .= '<label posn="0 0 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyepiece"/>';
			$xml .= '<label posn="19 0 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Display this help"/>';

			// Command "/eyepiece hide"
			$xml .= '<label posn="0 -2 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyepiece hide"/>';
			$xml .= '<label posn="19 -2 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Hide the Records-Widgets and store this as your preference"/>';

			// Command "/eyepiece show"
			$xml .= '<label posn="0 -4 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyepiece show"/>';
			$xml .= '<label posn="19 -4 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Show the Records-Widgets and store this as your preference"/>';

			// Command "/togglewidgets"
			$xml .= '<label posn="0 -10 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/togglewidgets $FF0or$FFF F7"/>';
			$xml .= '<label posn="19 -10 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Toggle the display of the Records-Widgets"/>';

			// Command "/estat [PARAMETER]"
			$xml .= '<label posn="0 -14 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/estat [PARAMETER]"/>';
			$xml .= '<label posn="19 -14 0.01" sizen="38 2" autonewline="1" textsize="1" textcolor="FF0F" text="Optional parameter can be:$FFF'. LF .'dedirecs, localrecs, topnations, topranks, topwinners, mostrecords, mostfinished, topplaytime, topdonators, toptracks, topvoters, topvisitors, topactive, toppayouts, toproundscore'. (($this->config['SCORETABLE_LISTS'][0]['TOP_BETWINS'][0]['ENABLED'][0] == true) ? ', topbetwins' : '') .'"/>';

			// Command "/emusic"
			if ($this->config['MUSIC_WIDGET'][0]['ENABLED'][0] == true) {
				$xml .= '<label posn="0 -24 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/emusic"/>';
				$xml .= '<label posn="19 -24 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Lists musics currently on the server"/>';
			}

			// Command "/elist [PARAMETER]"
			$xml .= '<label posn="0 -28 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/elist [PARAMETER]"/>';
			$xml .= '<label posn="19 -28 0.01" sizen="38 2" autonewline="1" textsize="1" textcolor="FF0F" text="Lists tracks currently on the server, optional parameter can be:'. LF .'$FFFjukebox, author, authorname, map, norecent, onlyrecent, norank, onlyrank, nomulti, onlymulti, noauthor, nogold, nosilver, nobronze, nofinish, best, worst, shortest, longest, newest, oldest, sortauthor, bestkarma, worstkarma'. LF .'$FF0or a keyword to search for"/>';

			if ($this->config['FEATURES'][0]['MARK_ONLINE_PLAYER_RECORDS'][0] == true) {
				$xml .= '<quad posn="0.45 -39.2 0.02" sizen="1.3 1.4" style="Icons64x64_1" substyle="Buddy"/>';
				$xml .= '<label posn="3 -39.2 0.01" sizen="70 0" textsize="1" textcolor="FFFF" text="Marker for an other Player that is currently online at this Server with a record and is ranked before you"/>';

				$xml .= '<quad posn="0.53 -42 0.02" sizen="1.1 1.4" style="Icons64x64_1" substyle="NotBuddy"/>';
				$xml .= '<label posn="3 -42 0.01" sizen="70 0" textsize="1" textcolor="FFFF" text="Marker for an other Player that is currently online at this Server with a record and is ranked behind you"/>';
			}

			$xml .= '<quad posn="0.3 -44.7 0.02" sizen="1.6 1.6" style="Icons64x64_1" substyle="ShowRight2"/>';
			$xml .= '<label posn="3 -44.8 0.01" sizen="70 0" textsize="1" textcolor="FFFF" text="Marker for your driven record"/>';
		}
		else if ($page == 1) {
			// Begin Help for MasterAdmins only
			$xml .= '<label posn="0 0 0.01" sizen="57 2" textsize="1" textcolor="FF0F" text="Commands for MasterAdmins only:"/>';

			// Command "/eyeset reload"
			$xml .= '<label posn="0 -2 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset reload"/>';
			$xml .= '<label posn="19 -2 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Reloads the records_eyepiece.xml"/>';

			// Command "/eyeset lfresh [INT]"
			$xml .= '<label posn="0 -4 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset lfresh [INT]"/>';
			$xml .= '<label posn="19 -4 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the normal &lt;refresh_interval&gt; sec."/>';

			// Command "/eyeset hfresh [INT]"
			$xml .= '<label posn="0 -6 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset hfresh [INT]"/>';
			$xml .= '<label posn="19 -6 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the nice &lt;refresh_interval&gt; sec."/>';

			// Command "/eyeset llimit [INT]"
			$xml .= '<label posn="0 -8 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset llimit [INT]"/>';
			$xml .= '<label posn="19 -8 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the nice &lt;lower_limit&gt; Players"/>';

			// Command "/eyeset ulimit [INT]"
			$xml .= '<label posn="0 -10 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset ulimit [INT]"/>';
			$xml .= '<label posn="19 -10 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the nice &lt;upper_limit&gt; Players"/>';

			// Command "/eyeset forcenice (true|false)"
			$xml .= '<label posn="0 -12 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset forcenice (true|false)"/>';
			$xml .= '<label posn="19 -12 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the &lt;nicemode&gt;&lt;force&gt;"/>';

			// Command "/eyeset playermarker (true|false)"
			$xml .= '<label posn="0 -14 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyeset playermarker (true|false)"/>';
			$xml .= '<label posn="19 -14 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Set the &lt;features&gt;&lt;mark_online_player_records&gt;"/>';


			// Begin Help for MasterAdmins only
			$xml .= '<label posn="0 -22 0.01" sizen="57 2" textsize="1" textcolor="FF0F" text="Commands for Op/Admin/MasterAdmin:"/>';

			// Command "/eyepiece payouts"
			$xml .= '<label posn="0 -24 0.01" sizen="17 2" textsize="1" textcolor="FFFF" text="/eyepiece payouts"/>';
			$xml .= '<label posn="19 -24 0.01" sizen="38 2" textsize="1" textcolor="FF0F" text="Show the outstanding winning payouts"/>';
		}
		else {
			// Begin About
			$xml .= '<label posn="0 0 0.01" sizen="55 0" autonewline="1" textsize="1" textcolor="FF0F" text="This plugin based upon the well known and good old FuFi.Widgets who accompanied us for years, it was written from scratch to change the look and feel of the Widgets and to make it easier to configure. Also to use the new XAseco features and events for more speed (since 1.12).'. LF.LF .'Some new features are included to have more information available and easily accessible. The famous feature (i think) is the clock which displays the local time without to choose the local timezone, no more need to calculate the local time from a Server far away!'. LF.LF .'Another nice feature are the clickable Record-Widgets to display all the driven records and not just a few in the small Widgets.'. LF.LF .'The extended $FFF$L[http://www.mania-exchange.com/]ManiaExchange-Mapinfo$L$FF0 Window display more information of a Map as the default currently does and also in a very nice way.'. LF.LF .'The next very nice thing is the Maplist where you can easily add a Map to the Jukebox. The integrated filter options makes it easy for e.g. list only Maps with the mood night or only Canyon Maps or only Maps from a selected Mapauthor...'. LF.LF .'$OHave fun with the Records-Eyepiece!"/>';
		}
		$xml .= '</frame>';


		$xml .= $this->templates['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function loadTemplates () {
		global $aseco;


		//--------------------------------------------------------------//
		// BEGIN: Widget ProgressIndicator				//
		//--------------------------------------------------------------//
		$content  = '<quad posn="40.2 -26.85 0.11" sizen="22 22" halign="center" valign="center" image="'. $this->config['IMAGES'][0]['PROGRESS_INDICATOR'][0] .'"/>';
		$content .= '<label posn="40.2 -36.85 0.12" sizen="22 22" halign="center" textsize="2" textcolor="FFFF" text="$SLoading... please wait."/>';

		$this->templates['PROGRESS_INDICATOR']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for ProgressIndicator				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget Donation (at Score)				//
		//--------------------------------------------------------------//
		// %widgetheight%
		$header  = '<manialink id="DonationWidgetAtScore" name="DonationWidgetAtScore">';
		$header .= '<frame posn="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['POS_X'][0] .' '. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['POS_Y'][0] .' 0" id="DonationWidgetAtScore">';
		$header .= '<format textsize="1"/>';
		$header .= '<quad posn="0 0 0.001" sizen="4.6 %widgetheight%" style="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0.7 -0.3 0.002" sizen="3.2 2.7" style="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['DONATION_WIDGET'][0]['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$header .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="PLEASE"/>';
		$header .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['DONATION_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="DONATE"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<donation_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['DONATION_WIDGET'][0]['WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['DONATION_WIDGET']['HEADER'] = $header;
		$this->templates['DONATION_WIDGET']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for Donation (at Score)				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for CheckpointCounter				//
		//--------------------------------------------------------------//
		$header  = '<manialink id="CheckpointCountWidget" name="CheckpointCountWidget">';
		$header .= '<frame posn="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['POS_X'][0] .' '. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['POS_Y'][0] .' 0" id="CheckpointCountWidget">';
		if ($this->config['CHECKPOINTCOUNT_WIDGET'][0]['BACKGROUND_COLOR'][0] != '') {
			$header .= '<quad posn="0 0 0.001" sizen="16 4" bgcolor="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0 0 0.001" sizen="16 4" style="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['CHECKPOINTCOUNT_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		}

		$footer  = '</frame>';
		$footer .= '</manialink>';

		$this->templates['CHECKPOINTCOUNTER_WIDGET']['HEADER'] = $header;
		$this->templates['CHECKPOINTCOUNTER_WIDGET']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for CheckpointCounter				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for WinningPayout				//
		//--------------------------------------------------------------//
		$header  = '<manialink id="WinningPayoutWidgetAtScore" name="WinningPayoutWidgetAtScore">';
		$header .= '<frame posn="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['POS_X'][0] .' '. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['POS_Y'][0] .' 0" id="WinningPayoutWidgetAtScore">';
		if ($this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['BACKGROUND_COLOR'][0] != '') {
			$header .= '<quad posn="0 0 0.001" sizen="25.5 '. ($this->config['LineHeight'] * 3 + 3.4) .'" bgcolor="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0 0 0.001" sizen="25.5 '. ($this->config['LineHeight'] * 3 + 3.4) .'" style="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		}

		// Icon and Title
		if ($this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="24.7 2" bgcolor="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="24.7 2" style="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="'. $this->config['Positions']['left']['icon']['x'] .' '. $this->config['Positions']['left']['icon']['y'] .' 0.004" sizen="2.5 2.5" style="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$header .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.004" sizen="20.2 0" textsize="1" text="'. $this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['TITLE'][0] .'"/>';
		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<winning_payout>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['WINNING_PAYOUT'][0]['WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['WINNING_PAYOUT']['HEADER'] = $header;
		$this->templates['WINNING_PAYOUT']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for WinningPayout				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for Scoretable Lists				//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %posx%, %posy%, %widgetscale%
		// %widgetheight%
		// %icon_style%, %icon_substyle%
		// %title%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';
		if ($this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_COLOR'][0] != '') {
			$header .= '<quad posn="0 0 0.001" sizen="15.76 %widgetheight%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0 0 0.001" sizen="15.76 %widgetheight%" style="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		}

		// Icon and Title
		if ($this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="14.96 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="14.96 2" style="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="0.6 -0.15 0.004" sizen="2.76 2.5" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.004" sizen="10.2 0" textsize="1" text="%title%"/>';
		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<scoretable_lists>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= %widgetscale%;
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['SCORETABLE_LISTS']['HEADER'] = $header;
		$this->templates['SCORETABLE_LISTS']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for Scoretable Lists				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: MapWidget (default)					//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %posx%, %posy%
		// %actionid%
		// %image_open_pos_x%, %image_open_pos_y%, %image_open%
		// %mapname%, %authortime%, %author%, %author_nation%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';

		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		$header .= '<label posn="0.1 -0.1 0" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 0.2) .' 8.35" action="%actionid%" text=" " focusareacolor1="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] .'" focusareacolor2="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_FOCUS'][0] .'"/>';
		$header .= '<quad posn="-0.2 0.3 0.001" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] + 0.4) .' 9.15" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0 0 0.02" sizen="'. $this->config['MAP_WIDGET'][0]['WIDTH'][0] .' 8.55" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="%image_open_pos_x% %image_open_pos_y% 0.03" sizen="3.5 3.5" image="%image_open%"/>';
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.03" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 0.8) .' 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.03" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 0.8) .' 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="%posx_icon% %posy_icon% 0.04" sizen="2.5 2.5" style="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MAP_WIDGET'][0]['ICONS'][0]['CURRENT_MAP'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$header .= '<label posn="%posx_title% %posy_title% 0.04" sizen="10.2 0" halign="%halign%" textsize="1" text="'. $this->config['MAP_WIDGET'][0]['TITLE'][0]['CURRENT_MAP'][0] .'"/>';
		$header .= '<label posn="1 -2.7 0.04" sizen="13.55 2" scale="1" text="%mapname%"/>';
		$header .= '<quad posn="1 -4.2 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/%author_nation%.dds"/>';
		$header .= '<label posn="3.3 -4.5 0.04" sizen="13 2" scale="0.9" text="by %author%"/>';
		$header .= '<quad posn="0.7 -6.25 0.04" sizen="1.7 1.7" style="BgRaceScore2" substyle="ScoreReplay"/>';
		$header .= '<label posn="2.7 -6.55 0.04" sizen="6 2" scale="0.75" text="%authortime%"/>';

		$header .= '<quad posn="0 100 0" sizen="5.5 5.5" image="'. $this->config['IMAGES'][0]['MX_LOGO_NORMAL'][0] .'"/>';		// Preload
		$header .= '<quad posn="0 100 0" sizen="5.5 5.5" image="'. $this->config['IMAGES'][0]['MX_LOGO_FOCUS'][0] .'"/>';		// Preload
		$header .= '<quad posn="0 100 0.05" sizen="3.5 3.5" image="'. $this->config['IMAGES'][0]['WIDGET_CLOSE_LEFT'][0] .'"/>';	// Preload

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<map_widget><race>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['MAP_WIDGET'][0]['RACE'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['MAP_WIDGET']['RACE']['HEADER'] = $header;
		$this->templates['MAP_WIDGET']['RACE']['FOOTER'] = $footer;

		unset($header, $mx, $footer);
		//--------------------------------------------------------------//
		// END: MapWidget (default)					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: MapWidget (score)					//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %posx%, %posy%, %icon_style%, %icon_substyle%, %title%
		// %nextmapname%, %nextauthor%, %nextauthor_nation%, %nextenv%, %nextmood%, %nextauthortime%, %nextgoldtime%, %nextsilvertime%, %nextbronzetime%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';

		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';
		if ($this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_COLOR'][0] != '') {
			$header .= '<quad posn="0 0 0.001" sizen="'. $this->config['MAP_WIDGET'][0]['WIDTH'][0] .' 14.1" bgcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_COLOR'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0 0 0.001" sizen="'. $this->config['MAP_WIDGET'][0]['WIDTH'][0] .' 14.1" style="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		}

		// Icon and Title
		if ($this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 0.8) .' 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.002" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 0.8) .' 2" style="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_SCORE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="0.6 -0.15 0.004" sizen="2.5 2.5" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="'. $this->config['Positions']['left']['title']['x'] .' '. $this->config['Positions']['left']['title']['y'] .' 0.004" sizen="10.2 0" text="%title%"/>';

		// Map Name
		$header .= '<label posn="1.1 -3 0.11" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 2.6) .' 2" text="%nextmapname%"/>';

		// Frame for the Mapinfo "Details"
		$header .= '<frame posn="0.25 -10.2 0">';
		$header .= '<quad posn="0.85 5.3 0.04" sizen="1.8 1.8" image="file://Skins/Avatars/Flags/%nextauthor_nation%.dds"/>';
		$header .= '<label posn="3.15 5 0.11" sizen="'. ($this->config['MAP_WIDGET'][0]['WIDTH'][0] - 2.95) .' 2" scale="0.9" text="by %nextauthor%"/>';
		$header .= '<quad posn="2.95 3.38 0.11" sizen="2.5 2.5" halign="right" style="Icons128x128_1" substyle="Advanced"/>';
		$header .= '<label posn="3.3 2.9 0.11" sizen="12 2" scale="0.9" text="%nextenv%"/>';
		$header .= '<quad posn="10 3.53 0.11" sizen="2.6 2.6" halign="right" style="Icons128x128_1" substyle="Manialink"/>';
		$header .= '<label posn="10.2 2.9 0.11" sizen="12 2" scale="0.9" text="%nextmood%"/>';
		$header .= '</frame>';

		// Frame for the Mapinfo "Times"
		$header .= '<frame posn="0.25 -14.5 0">';
		$header .= '<quad posn="2.75 5.1 0.11" sizen="1.9 1.9" halign="right" style="MedalsBig" substyle="MedalNadeo"/>';
		$header .= '<label posn="3.3 5 0.11" sizen="6 2" scale="0.9" text="%nextauthortime%"/>';
		$header .= '<quad posn="2.75 3.1 0.11" sizen="1.9 1.9" halign="right" style="MedalsBig" substyle="MedalGold"/>';
		$header .= '<label posn="3.3 2.9 0.11" sizen="6 2" scale="0.9" text="%nextgoldtime%"/>';
		$header .= '<quad posn="9.65 5.1 0.11" sizen="1.9 1.9" halign="right" style="MedalsBig" substyle="MedalSilver"/>';
		$header .= '<label posn="10.2 5 0.11" sizen="6 2" scale="0.9" text="%nextsilvertime%"/>';
		$header .= '<quad posn="9.65 3.1 0.11" sizen="1.9 1.9" halign="right" style="MedalsBig" substyle="MedalBronze"/>';
		$header .= '<label posn="10.2 2.9 0.11" sizen="6 2" scale="0.9" text="%nextbronzetime%"/>';
		$header .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<map_widget><score>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['MAP_WIDGET'][0]['SCORE'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['MAP_WIDGET']['SCORE']['HEADER'] = $header;
		$this->templates['MAP_WIDGET']['SCORE']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: MapWidget (score)					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for Clock					//
		//--------------------------------------------------------------//
		// %posx%, %posy%, %widgetscale%
		// %background_style%, %background_substyle%
		$content  = '<manialink id="ClockWidget" name="ClockWidget">';
		$content .= '<frame posn="%posx% %posy% 0" id="ClockWidget">';
		$content .= '<format textsize="1"/>';

		// Content
		$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="%background_style%" substyle="%background_substyle%"/>';
		$content .= '<quad posn="0.7 -0.1 0.002" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['CLOCK_ICON'][0] .'"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="" id="RecordsEyepieceLabelLocalTime"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['CLOCK_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="LOCALTIME"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<clock_widget>
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= %widgetscale%;

	declare LabelLocalTime		<=> (Page.GetFirstChild("RecordsEyepieceLabelLocalTime") as CMlLabel);
	declare PrevTime		= CurrentLocalDateText;

	while (True) {
		yield;

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;

			// Split "2013/12/30 15:35:12" to "2013/12/30" and "15:35:12"
			declare LocalDateTimeParts = TextLib::Split(" ", CurrentLocalDateText);
			LabelLocalTime.SetText(LocalDateTimeParts[1]);
//			LabelLocalTime.SetText("20:38:58");
		}
	}
}
--></script>
EOL;

		$content .= '</frame>';
		$content .= $maniascript;
		$content .= '</manialink>';

		$this->templates['CLOCK_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for Clock					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for PlayerSpectatorWidget			//
		//--------------------------------------------------------------//
		// %max_players%
		// %max_spectators%
		$content  = '<manialink id="PlayerSpectatorWidget" name="PlayerSpectatorWidget">';
		$content .= '<frame posn="'. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['POS_X'][0] .' '. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['POS_Y'][0] .' 0" id="PlayerSpectatorWidget">';
		$content .= '<format textsize="1"/>';
		$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$content .= '<label posn="2.3 -0.6 0.1" sizen="3.65 2" halign="center" text="" id="RecordsEyepiecePlayerSpectatorWidgetAmountPlayers"/>';
		$content .= '<label posn="2.3 -2.1 0.1" sizen="6.35 2" halign="center" textcolor="'. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="PLAYER"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="" id="RecordsEyepiecePlayerSpectatorWidgetAmountSpectators"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['PLAYER_SPECTATOR_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="SPECTATOR"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container			<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale				= {$this->config['PLAYER_SPECTATOR_WIDGET'][0]['SCALE'][0]};

	declare CMlLabel LabelAmountPlayers		<=> (Page.GetFirstChild("RecordsEyepiecePlayerSpectatorWidgetAmountPlayers") as CMlLabel);
	declare CMlLabel LabelAmountSpectators		<=> (Page.GetFirstChild("RecordsEyepiecePlayerSpectatorWidgetAmountSpectators") as CMlLabel);
	declare Integer MaxPlayers			= %max_players%;
	declare Integer MaxSpectators			= %max_spectators%;
	declare Integer RefreshInterval			= 500;
	declare Integer RefreshTime			= CurrentTime;
	declare Text DefaultColor			= "\$FFF";
	declare Text MaxColor				= "\$F00";

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		if (CurrentTime > RefreshTime) {
			declare Integer PlayerCount = 0;
			declare Integer SpectatorCount = 0;
			foreach (Player in Players) {
				// Skip on Login from Server, that is not a Player ;)
				if (Player.Login == CurrentServerLogin) {
					continue;
				}
				if (Player.RequestsSpectate == True) {
					SpectatorCount += 1;
				}
				else {
					PlayerCount += 1;
				}
			}
			if (PlayerCount >= MaxPlayers) {
				LabelAmountPlayers.SetText(MaxColor ^ PlayerCount ^ "/" ^ MaxPlayers);
			}
			else {
				LabelAmountPlayers.SetText(DefaultColor ^ PlayerCount ^ "/" ^ MaxPlayers);
			}

			if (SpectatorCount >= MaxSpectators) {
				LabelAmountSpectators.SetText(MaxColor ^ SpectatorCount ^ "/" ^ MaxSpectators);
			}
			else {
				LabelAmountSpectators.SetText(DefaultColor ^ SpectatorCount ^ "/" ^ MaxSpectators);
			}

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['PLAYERSPECTATOR_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for PlayerSpectatorWidget			//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for CurrentRankingWidget			//
		//--------------------------------------------------------------//
		// %ranks%
		// %info%
		$content  = '<manialink id="CurrentRankingWidget" name="CurrentRankingWidget">';
		$content .= '<frame posn="'. $this->config['CURRENT_RANKING_WIDGET'][0]['POS_X'][0] .' '. $this->config['CURRENT_RANKING_WIDGET'][0]['POS_Y'][0] .' 0" id="CurrentRankingWidget">';
		$content .= '<format textsize="1"/>';
		$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" action="showLiveRankingsWindow" style="'. $this->config['CURRENT_RANKING_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['CURRENT_RANKING_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$content .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$content .= '<quad posn="0.7 -0.3 0.003" sizen="3.35 3" style="BgRaceScore2" substyle="LadderRank"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" textcolor="FFFF" text="%ranks%"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['CURRENT_RANKING_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="%info%"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<current_ranking_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['CURRENT_RANKING_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['CURRENTRANKING_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for CurrentRankingWidget				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for LadderLimitWidget				//
		//--------------------------------------------------------------//
		// %ladder_minimum%, %ladder_maximum%
		$content  = '<manialink id="LadderLimitWidget" name="LadderLimitWidget">';
		$content .= '<frame posn="'. $this->config['LADDERLIMIT_WIDGET'][0]['POS_X'][0] .' '. $this->config['LADDERLIMIT_WIDGET'][0]['POS_Y'][0] .' 0" id="LadderLimitWidget">';
		$content .= '<format textsize="1"/>';
		$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['LADDERLIMIT_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['LADDERLIMIT_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$content .= '<quad posn="0.7 -0.3 0.002" sizen="3.35 3" style="Icons128x128_1" substyle="LadderPoints"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="%ladder_minimum%-%ladder_maximum%k"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['LADDERLIMIT_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="LADDER"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<ladderlimit_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['LADDERLIMIT_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['LADDERLIMIT_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for LadderLimitWidget				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for TopList					//
		//--------------------------------------------------------------//
		$content  = '<manialink id="ToplistWidget" name="ToplistWidget">';
		$content .= '<frame posn="'. $this->config['TOPLIST_WIDGET'][0]['POS_X'][0] .' '. $this->config['TOPLIST_WIDGET'][0]['POS_Y'][0] .' 0" id="ToplistWidget">';
		$content .= '<format textsize="1"/>';
		if ($this->config['TOPLIST_WIDGET'][0]['BACKGROUND_DEFAULT'][0] != '') {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['TOPLIST_WIDGET'][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['TOPLIST_WIDGET'][0]['BACKGROUND_FOCUS'][0] .'" scriptevents="1" id="ButtonToplistWidget"/>';
		}
		else {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['TOPLIST_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['TOPLIST_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'" scriptevents="1" id="ButtonToplistWidget"/>';
		}
		$content .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$content .= '<quad posn="0.7 -0.3 0.002" sizen="3.35 3" style="BgRaceScore2" substyle="LadderRank"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="MORE"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['TOPLIST_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="RANKING"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<toplist_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['TOPLIST_WIDGET'][0]['SCALE'][0]};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					TriggerPageAction("showToplistWindow");
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['TOPLIST_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for TopList					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for Gamemode					//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		// %limits%
		// %gamemode%
		$header  = '<manialink id="GamemodeWidget" name="GamemodeWidget">';
		$header .= '<frame posn="'. $this->config['GAMEMODE_WIDGET'][0]['POS_X'][0] .' '. $this->config['GAMEMODE_WIDGET'][0]['POS_Y'][0] .' 0" id="GamemodeWidget">';
		$header .= '<format textsize="1"/>';
		$header .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['GAMEMODE_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['GAMEMODE_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0.85 -0.3 0.002" sizen="2.9 2.9" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="2.3 -4.9 0.1" sizen="6.35 2" halign="center" textcolor="'. $this->config['GAMEMODE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="%gamemode%"/>';
		$header .= '</frame>';

		$limits  = '<frame posn="%posx% %posy% 0">';
		$limits .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" textsize="1" text="%limits%"/>';
		$limits .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<gamemode_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['GAMEMODE_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = $maniascript;
		$footer .= '</manialink>';

		$this->templates['CURRENT_GAMEMODE']['HEADER'] = $header;
		$this->templates['CURRENT_GAMEMODE']['LIMITS'] = $limits;
		$this->templates['CURRENT_GAMEMODE']['FOOTER'] = $footer;

		unset($header, $limits, $footer);
		//--------------------------------------------------------------//
		// END: Widget for Gamemode					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: NextEnvironment at Score				//
		//--------------------------------------------------------------//
		// %icon%
		$content  = '<manialink id="NextEnvironmentWidgetAtScore" name="NextEnvironmentWidgetAtScore">';
		$content .= '<frame posn="'. $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['POS_X'][0] .' '. $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['POS_Y'][0] .' 0" id="NextEnvironmentWidgetAtScore">';
		$content .= '<format textsize="1"/>';
		$content .= '<quad posn="0 0 0.001" sizen="11.1 6.5" style="'. $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$content .= '%icon%';
		$content .= '<label posn="5.6 -5.2 0.002" sizen="16.5 1.8" halign="center" textcolor="'. $this->config['NEXT_ENVIRONMENT_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="UPCOMING ENVIRONMENT"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<next_environment_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['NEXT_ENVIRONMENT_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['NEXT_ENVIRONMENT']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: NextEnvironment at Score				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: NextGamemode at Score					//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		$content  = '<manialink id="NextGamemodeWidgetAtScore" name="NextGamemodeWidgetAtScore">';
		$content .= '<frame posn="'. $this->config['NEXT_GAMEMODE_WIDGET'][0]['POS_X'][0] .' '. $this->config['NEXT_GAMEMODE_WIDGET'][0]['POS_Y'][0] .' 0" id="NextGamemodeWidgetAtScore">';
		$content .= '<format textsize="1"/>';
		$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['NEXT_GAMEMODE_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['NEXT_GAMEMODE_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$content .= '<quad posn="0.7 -0.5 0.002" sizen="3.2 3.2" style="%icon_style%" substyle="%icon_substyle%"/>';
		$content .= '<label posn="2.3 -4.2 0.002" sizen="6.35 2" halign="center" textcolor="'. $this->config['NEXT_GAMEMODE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="UPCOMING"/>';
		$content .= '<label posn="2.3 -5.2 0.002" sizen="6.35 2" halign="center" textcolor="'. $this->config['NEXT_GAMEMODE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="GAMEMODE"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<next_gamemode_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['NEXT_GAMEMODE_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['NEXT_GAMEMODE']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: NextGamemode at Score					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for Visitors					//
		//--------------------------------------------------------------//
		// %visitorcount%
		$content  = '<manialink id="VisitorsWidget" name="VisitorsWidget">';
		$content .= '<frame posn="'. $this->config['VISITORS_WIDGET'][0]['POS_X'][0] .' '. $this->config['VISITORS_WIDGET'][0]['POS_Y'][0] .' 0" id="VisitorsWidget">';
		$content .= '<format textsize="1"/>';
		if ($this->config['VISITORS_WIDGET'][0]['BACKGROUND_DEFAULT'][0] != '') {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['VISITORS_WIDGET'][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['VISITORS_WIDGET'][0]['BACKGROUND_FOCUS'][0] .'" scriptevents="1" id="ButtonVisitorsWidget"/>';
		}
		else {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['VISITORS_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['VISITORS_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'" scriptevents="1" id="ButtonVisitorsWidget"/>';
		}
		$content .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$content .= '<quad posn="0.7 -0.3 0.002" sizen="3.2 3.2" style="Icons128x128_1" substyle="Buddies"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="%visitorcount%"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['VISITORS_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="VISITORS"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<visitors_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['VISITORS_WIDGET'][0]['SCALE'][0]};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					TriggerPageAction("showTopNationsWindow");
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['VISITORS_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for Visitors					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for ManiaExchange				//
		//--------------------------------------------------------------//
		// %offline_record%, %text%
		$header  = '<manialink id="ManiaExchangeWidget" name="ManiaExchangeWidget">';
		$header .= '<frame posn="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['POS_X'][0] .' '. $this->config['MANIAEXCHANGE_WIDGET'][0]['POS_Y'][0] .' 0" id="ManiaExchangeWidget">';
		$header .= '<format textsize="1"/>';

		$footer = '<quad posn="0.7 -0.1 0.002" sizen="3.2 3.2" image="'. $this->config['IMAGES'][0]['MX_LOGO_NORMAL'][0] .'" imagefocus="'. $this->config['IMAGES'][0]['MX_LOGO_FOCUS'][0] .'"/>';
		$footer .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="%offline_record%"/>';
		$footer .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['MANIAEXCHANGE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="%text%"/>';
		$footer .= '</frame>';
$footer .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<maniaexchange_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['MANIAEXCHANGE_WIDGET'][0]['SCALE'][0]};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					TriggerPageAction("showManiaExchangeMapInfoWindow");
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$footer .= '</manialink>';

		$this->templates['MANIA_EXCHANGE']['HEADER'] = $header;
		$this->templates['MANIA_EXCHANGE']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for ManiaExchangeWidget				//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for MapCount					//
		//--------------------------------------------------------------//
		// %mapcount%
		$content  = '<manialink id="MapCountWidget" name="MapCountWidget">';
		$content .= '<frame posn="'. $this->config['MAPCOUNT_WIDGET'][0]['POS_X'][0] .' '. $this->config['MAPCOUNT_WIDGET'][0]['POS_Y'][0] .' 0" id="MapCountWidget">';
		$content .= '<format textsize="1"/>';
		if ($this->config['MAPCOUNT_WIDGET'][0]['BACKGROUND_DEFAULT'][0] != '') {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" bgcolor="'. $this->config['MAPCOUNT_WIDGET'][0]['BACKGROUND_DEFAULT'][0] .'" bgcolorfocus="'. $this->config['MAPCOUNT_WIDGET'][0]['BACKGROUND_FOCUS'][0] .'" scriptevents="1" id="ButtonMapCountWidget"/>';
		}
		else {
			$content .= '<quad posn="0 0 0.001" sizen="4.6 6.5" style="'. $this->config['MAPCOUNT_WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['MAPCOUNT_WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'" scriptevents="1" id="ButtonMapCountWidget"/>';
		}
		$content .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$content .= '<quad posn="0.2 0 0.002" sizen="3.8 3.8" style="Icons128x128_1" substyle="Browse"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="%mapcount%"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['MAPCOUNT_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="MAPS"/>';
		$content .= '</frame>';
$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<mapcount_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['MAPCOUNT_WIDGET'][0]['SCALE'][0]};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					TriggerPageAction("showMaplistWindow");
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['MAPCOUNT']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for Mapcount					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for Favorite					//
		//--------------------------------------------------------------//
		// %posx%, %posy%, %widgetscale%
		// %background%
		$content  = '<manialink id="AddToFavoriteWidget" name="AddToFavoriteWidget">';
		$content .= '<frame posn="%posx% %posy% 0" id="AddToFavoriteWidget">';
		$content .= '<format textsize="1"/>';
		$content .= '%background%';
		$content .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$content .= '<quad posn="0.9 -0.2 0.002" sizen="3.2 3.2" style="Icons128x128_Blink" substyle="ServersFavorites"/>';
		$content .= '<label posn="2.3 -3.4 0.1" sizen="4 1.4" halign="center" scale="0.9" text="ADD"/>';
		$content .= '<label posn="2.3 -4.9 0.1" sizen="6.35 0.5" halign="center" textcolor="'. $this->config['FAVORITE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="FAVORITE"/>';
		$content .= '</frame>';

		$url = 'addfavorite?action=add&game=ManiaPlanet&server='. rawurlencode($aseco->server->login) .'&name='. rawurlencode($aseco->server->name) .'&zone='. rawurlencode(implode('|', $aseco->server->zone)) .'&player=';

$content .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<favorite_widget>
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= %widgetscale%;

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					OpenLink("$url" ^ InputPlayer.Login ^"&nickname="^ TextLib::StripFormatting(InputPlayer.Name), CMlScript::LinkType::ManialinkBrowser);
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$content .= '</manialink>';

		$this->templates['FAVORITE_WIDGET']['CONTENT'] = $content;

		unset($content);
		//--------------------------------------------------------------//
		// END: Widget for Favorite					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Widget for MusicWidget				//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %posx%, %posy%
		// %backgroundwidth%
		// %borderwidth%
		// %widgetwidth%
		// %title_background_width%
		// %actionid%
		// %image_open_pos_x%, %image_open_pos_y%, %image_open%
		// %posx_icon%, %posy_icon%
		// %posx_title%, %posy_title%
		// %halign%, %title%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';
		$header .= '<label posn="0.1 -0.1 0" sizen="%backgroundwidth% 8.35" action="%actionid%" text=" " focusareacolor1="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] .'" focusareacolor2="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_FOCUS'][0] .'"/>';
		$header .= '<quad posn="-0.2 0.3 0.001" sizen="%borderwidth% 9.15" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0 0 0.002" sizen="%widgetwidth% 8.55" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="%image_open_pos_x% %image_open_pos_y% 0.05" sizen="3.5 3.5" image="%image_open%"/>';

		// Icon and Title
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="%posx_icon% %posy_icon% 0.004" sizen="2.5 2.5" style="'. $this->config['MUSIC_WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['MUSIC_WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$header .= '<label posn="%posx_title% %posy_title% 0.004" sizen="10.2 0" halign="%halign%" textsize="1" text="%title%"/>';
		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<music_widget>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['MUSIC_WIDGET'][0]['SCALE'][0]};
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['MUSIC_WIDGET']['HEADER'] = $header;
		$this->templates['MUSIC_WIDGET']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Widget for MusicWidget					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: RecordWidgets (Dedimania, Locals, Live)		//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %actionid%
		// %posx%, %posy%
		// %backgroundwidth% %backgroundheight%
		// %borderwidth%, %borderheight%
		// %widgetwidth%, %widgetheight%
		// %column_width_name%, %column_height%
		// %image_open_pos_x%, %image_open_pos_y%, %image_open%
		// %title_background_width%
		// %posx_icon%, %posy_icon%, %icon_style%, %icon_substyle%
		// %posx_title%, %posy_title%
		// %halign%, %title%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';
		$header .= '<label posn="0.1 -0.1 0" sizen="%backgroundwidth% %backgroundheight%" action="%actionid%" text=" " focusareacolor1="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] .'" focusareacolor2="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_FOCUS'][0] .'"/>';
		$header .= '<quad posn="-0.2 0.3 0.001" sizen="%borderwidth% %borderheight%" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0 0 0.002" sizen="%widgetwidth% %widgetheight%" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0.4 -2.6 0.003" sizen="2 %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_RANK'][0] .'"/>';
		$header .= '<quad posn="2.4 -2.6 0.003" sizen="3.65 %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_SCORE'][0] .'"/>';
		$header .= '<quad posn="6.05 -2.6 0.003" sizen="%column_width_name% %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_NAME'][0] .'"/>';
		$header .= '<quad posn="%image_open_pos_x% %image_open_pos_y% 0.05" sizen="3.5 3.5" image="%image_open%"/>';

		// Icon and Title
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="%posx_icon% %posy_icon% 0.004" sizen="2.5 2.5" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="%posx_title% %posy_title% 0.004" sizen="10.2 0" halign="%halign%" textsize="1" text="%title%"/>';
		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

		$footer  = '</frame>';
		$footer .= '</manialink>';

		$this->templates['RECORD_WIDGETS']['HEADER'] = $header;
		$this->templates['RECORD_WIDGETS']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: RecordWidgets (Dedimania, Locals, Live)			//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: RoundScoreWidget 					//
		//--------------------------------------------------------------//
		// %manialinkid%
		// %posx%, %posy%, %widgetscale%
		// %borderwidth%, %borderheight%
		// %widgetwidth%, %widgetheight%
		// %column_width_name%, %column_height%
		// %title_background_width%
		// %image_open_pos_x%, %image_open_pos_y%, %image_open%
		// %posx_icon%, %posy_icon%, %icon_style%, %icon_substyle%
		// %posx_title%, %posy_title%
		// %halign%, %title%
		$header  = '<manialink id="%manialinkid%" name="%manialinkid%">';
		$header .= '<frame posn="%posx% %posy% 0" id="%manialinkid%">';

		$header .= '<quad posn="0.1 -0.1 0" sizen="%widgetwidth% %widgetheight%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_DEFAULT'][0] .'"/>';
		$header .= '<quad posn="-0.2 0.3 0.001" sizen="%borderwidth% %borderheight%" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BORDER_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0 0 0.002" sizen="%widgetwidth% %widgetheight%" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="0.4 -2.6 0.003" sizen="2 %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_RANK'][0] .'"/>';
		$header .= '<quad posn="2.4 -2.6 0.003" sizen="3.65 %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_SCORE'][0] .'"/>';
		$header .= '<quad posn="6.05 -2.6 0.003" sizen="%column_width_name% %column_height%" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['BACKGROUND_NAME'][0] .'"/>';

		// Icon and Title
		if ($this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] != '') {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" bgcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_BACKGROUND'][0] .'"/>';
		}
		else {
			$header .= '<quad posn="0.4 -0.36 0.003" sizen="%title_background_width% 2" style="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		}
		$header .= '<quad posn="%posx_icon% %posy_icon% 0.004" sizen="2.5 2.5" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="%posx_title% %posy_title% 0.004" sizen="10.2 0" halign="%halign%" textsize="1" text="%title%"/>';
		$header .= '<format textsize="1" textcolor="'. $this->config['STYLE'][0]['WIDGET_RACE'][0]['COLORS'][0]['DEFAULT'][0] .'"/>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<round_score>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= %widgetscale%;
}
--></script>
EOL;

		$footer  = '</frame>';
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['ROUNDSCOWIDGET']['HEADER'] = $header;
		$this->templates['ROUNDSCOWIDGET']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: RoundScoreWidget					//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Window						//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		// %window_title%
		// %prev_next_buttons%
		$header  = '<manialink id="SubWindow" name="SubWindow"></manialink>';		// Always close sub windows
		$header .= '<manialink id="MainWindow" name="MainWindow">';
		if ($this->config['STYLE'][0]['WINDOW'][0]['LIGHTBOX'][0]['ENABLED'][0] == true) {
			$header .= '<quad posn="-64 48 18.49" sizen="128 96" bgcolor="'. $this->config['STYLE'][0]['WINDOW'][0]['LIGHTBOX'][0]['BGCOLOR'][0] .'" id="RecordsEyepieceLightbox"/>';
		}
		else {
			$header .= '<quad posn="-128 0 18.49" sizen="1 1" bgcolor="FFF0" id="RecordsEyepieceLightbox"/>';
		}
		$header .= '<frame posn="-40.8 30.55 18.50" id="RecordsEyepieceWindow">';	// BEGIN: Window Frame
		$header .= '<quad posn="-0.2 0.2 0.01" sizen="81.8 59" style="'. $this->config['STYLE'][0]['WINDOW'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['BACKGROUND_SUBSTYLE'][0] .'" id="RecordsEyepieceWindowBody" ScriptEvents="1"/>';
		$header .= '<quad posn="1.8 -4.1 0.02" sizen="77.7 49.9" bgcolor="'. $this->config['STYLE'][0]['WINDOW'][0]['CONTENT_BGCOLOR'][0] .'"/>';

		// Header Line
		$header .= '<quad posn="-0.6 0.6 0.02" sizen="82.6 6" style="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="-0.6 0.6 0.03" sizen="82.6 6" style="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_SUBSTYLE'][0] .'" id="RecordsEyepieceWindowTitle" ScriptEvents="1"/>';

		// Title
		$header .= '<quad posn="1.8 -0.7 0.04" sizen="3.2 3.2" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="5.5 -1.7 0.04" sizen="75.4 0" textsize="2" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WINDOW'][0]['HEADLINE_TEXTCOLOR'][0] .'" text="%window_title%"/>';

		// Close Button
		$header .= '<frame posn="76.7 -0.15 0.05">';
	//	$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" action="closeMainWindow" style="Icons64x64_1" substyle="ArrowUp"/>';
		$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" style="Icons64x64_1" substyle="ArrowUp" id="RecordsEyepieceWindowClose" ScriptEvents="1"/>';
		$header .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$header .= '<quad posn="0.7 -0.7 0.03" sizen="3.1 3.1" style="Icons64x64_1" substyle="Close"/>';
		$header .= '</frame>';

		// Minimize Button
		$header .= '<frame posn="73.4 -0.15 0.05">';
		$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" style="Icons64x64_1" substyle="ArrowUp" id="RecordsEyepieceWindowMinimize" ScriptEvents="1"/>';
		$header .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$header .= '<label posn="2.33 -2.4 0.03" sizen="6 0" halign="center" valign="center" textsize="3" textcolor="000F" text="$O-"/>';
		$header .= '</frame>';

		$header .= '<label posn="6.8 -55.8 0.04" sizen="16 2" halign="center" valign="center2" textsize="1" scale="0.7" action="showHelpWindow" focusareacolor1="0000" focusareacolor2="FFF5" textcolor="000F" text="RECORDS-EYEPIECE/'. $this->getVersion() .'"/>';
		$header .= '%prev_next_buttons%';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * License:	GPLv3
 * ----------------------------------
 */
Void HideFrame (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	Container.Unload();
}
Void WipeOut (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = 0.0;
	declare Real EndPosnY = 0.0;
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale > 0.0) {
		Container.RelativePosition.X += (PosnDistanceX / 20);
		Container.RelativePosition.Y += (PosnDistanceY / 20);
		Container.RelativeScale -= 0.05;
		yield;
	}
	Container.Unload();
}
Void Minimize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = (-40.8 * 2.5);
	declare Real EndPosnY = (30.55 * 1.875);
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale > 0.2) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale -= 0.05;
		yield;
	}
}
Void Maximize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = (-40.8 * 2.5);
	declare Real EndPosnY = (30.55 * 1.875);
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale < 1.0) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale += 0.05;
		yield;
	}
}
main () {
	declare Boolean RecordsEyepieceSubWindowVisible for UI = True;
	declare CMlControl Container <=> (Page.GetFirstChild("RecordsEyepieceWindow") as CMlFrame);
	declare CMlQuad Quad;
	declare Boolean MoveWindow = False;
	declare Boolean IsMinimized = False;
	declare Real MouseDistanceX = 0.0;
	declare Real MouseDistanceY = 0.0;

	while (True) {
		yield;
		if (MoveWindow == True) {
			Container.RelativePosition.X = (MouseDistanceX + MouseX);
			Container.RelativePosition.Y = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			foreach (Event in PendingEvents) {
				if (Event.ControlId == "RecordsEyepieceWindowTitle") {
					MouseDistanceX = (Container.RelativePosition.X - MouseX);
					MouseDistanceY = (Container.RelativePosition.Y - MouseY);
					MoveWindow = True;
				}
			}
		}
		else {
			MoveWindow = False;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "RecordsEyepieceWindowClose") {
						RecordsEyepieceSubWindowVisible = False;
						WipeOut("RecordsEyepieceWindow");
						HideFrame("RecordsEyepieceLightbox");
					}
					else if ( (Event.ControlId == "RecordsEyepieceWindowMinimize") && (IsMinimized == False) ) {
						Minimize("RecordsEyepieceWindow");
						IsMinimized = True;
					}
					else if ( (Event.ControlId == "RecordsEyepieceWindowBody") && (IsMinimized == True) ) {
						Maximize("RecordsEyepieceWindow");
						IsMinimized = False;
					}
				}
			}
		}
	}
}
--></script>
EOL;
		// Footer
		$footer  = '</frame>';				// END: Window Frame
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['WINDOW']['HEADER'] = $header;
		$this->templates['WINDOW']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Window							//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: SubWindow						//
		//--------------------------------------------------------------//
		// %icon_style%, %icon_substyle%
		// %window_title%
		// %prev_next_buttons%
		$header  = '<manialink id="SubWindow" name="SubWindow">';
		$header .= '<frame posn="-19.8 16 21.5" id="RecordsEyepieceSubWindow">';	// BEGIN: Window Frame
		$header .= '<quad posn="-0.2 0.2 0.01" sizen="39.7 27.85" style="'. $this->config['STYLE'][0]['WINDOW'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="1.8 -4.1 0.02" sizen="35.6 17.75" bgcolor="'. $this->config['STYLE'][0]['WINDOW'][0]['CONTENT_BGCOLOR'][0] .'"/>';

		// Header Line
		$header .= '<quad posn="-0.6 0.6 0.02" sizen="40.5 6" style="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_SUBSTYLE'][0] .'"/>';
		$header .= '<quad posn="-0.6 0.6 0.03" sizen="40.5 6" style="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_STYLE'][0] .'" substyle="'. $this->config['STYLE'][0]['WINDOW'][0]['TITLE_SUBSTYLE'][0] .'"/>';

		// Title
		$header .= '<quad posn="1.8 -1.4 0.04" sizen="2.2 2.2" style="%icon_style%" substyle="%icon_substyle%"/>';
		$header .= '<label posn="4.5 -1.6 0.04" sizen="37 0" textsize="2" scale="0.9" textcolor="'. $this->config['STYLE'][0]['WINDOW'][0]['HEADLINE_TEXTCOLOR'][0] .'" text="%window_title%"/>';

		// Close Button
		$header .= '<frame posn="34.6 -0.15 0.05">';
	//	$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" action="closeSubWindow" style="Icons64x64_1" substyle="ArrowUp"/>';
		$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" style="Icons64x64_1" substyle="ArrowUp" id="RecordsEyepieceSubWindowClose" ScriptEvents="1"/>';
		$header .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$header .= '<quad posn="0.7 -0.7 0.03" sizen="3.1 3.1" style="Icons64x64_1" substyle="Close"/>';
		$header .= '</frame>';

		$header .= '%prev_next_buttons%';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare Boolean RecordsEyepieceSubWindowVisible for UI = True;
	declare CMlControl Container <=> (Page.GetFirstChild("RecordsEyepieceSubWindow") as CMlFrame);
	RecordsEyepieceSubWindowVisible = True;

	while (True) {
		yield;
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "RecordsEyepieceSubWindowClose") {
						RecordsEyepieceSubWindowVisible = False;
					}
				}
			}
		}
		if (RecordsEyepieceSubWindowVisible == False) {
			Container.Hide();
		}
	}
}
--></script>
EOL;

		// Footer
		$footer  = '</frame>';				// END: Window Frame
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$this->templates['SUBWINDOW']['HEADER'] = $header;
		$this->templates['SUBWINDOW']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: SubWindow							//
		//--------------------------------------------------------------//




		//--------------------------------------------------------------//
		// BEGIN: Records-Eyepiece Advertising at Race/Score		//
		//--------------------------------------------------------------//
		$race  = '<manialink id="RecordsEyepieceAdvertiserWidget" name="RecordsEyepieceAdvertiserWidget">';
		$race .= '<frame posn="'. $this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['POS_X'][0] .' '. $this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['POS_Y'][0] .' 0" id="RecordsEyepieceAdvertiserWidget">';
		$race .= '<quad posn="0 0 0" sizen="6.19 6.45" url="http://www.undef.name/UASECO/Records-Eyepiece.php" image="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-opacity.png" imagefocus="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-focus.png"/>';
		$race .= '</frame>';
$race .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<eyepiece_widget><race>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['EYEPIECE_WIDGET'][0]['RACE'][0]['SCALE'][0]};
}
--></script>
EOL;
		$race .= '</manialink>';

		$score  = '<manialink id="RecordsEyepieceAdvertiserWidget" name="RecordsEyepieceAdvertiserWidget">';
		$score .= '<frame posn="'. $this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['POS_X'][0] .' '. $this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['POS_Y'][0] .' 0" id="RecordsEyepieceAdvertiserWidget">';
		$score .= '<format textsize="1"/>';
		$score .= '<quad posn="0 0 0.001" sizen="4.6 6.5" url="http://www.undef.name/UASECO/Records-Eyepiece.php" style="'. $this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['BACKGROUND_SUBSTYLE'][0] .'"/>';
		$score .= '<quad posn="-0.18 -4.6 0.002" sizen="2.1 2.1" image="'. $this->config['IMAGES'][0]['WIDGET_OPEN_SMALL'][0] .'"/>';
		$score .= '<quad posn="0.365 -0.3 0.002" sizen="3.87 4.03" url="http://www.undef.name/UASECO/Records-Eyepiece.php" image="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-normal.png" imagefocus="http://static.undef.name/ingame/records-eyepiece/logo-records-eyepiece-focus.png"/>';
		$score .= '<label posn="2.3 -4.2 0.002" sizen="6.35 2" halign="center" textcolor="'. $this->config['EYEPIECE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="RECORDS"/>';
		$score .= '<label posn="2.3 -5.2 0.002" sizen="6.35 2" halign="center" textcolor="'. $this->config['EYEPIECE_WIDGET'][0]['TEXT_COLOR'][0] .'" scale="0.6" text="EYEPIECE"/>';
		$score .= '</frame>';
$score .= <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Records-Eyepiece
 * Widget:	<eyepiece_widget><score>
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container	<=> (Page.GetFirstChild(Page.MainFrame.ControlId) as CMlFrame);
	Container.RelativeScale		= {$this->config['EYEPIECE_WIDGET'][0]['SCORE'][0]['SCALE'][0]};
}
--></script>
EOL;
		$score .= '</manialink>';

		$this->templates['RECORDSEYEPIECEAD']['RACE'] = $race;
		$this->templates['RECORDSEYEPIECEAD']['SCORE'] = $score;

		unset($race, $score);
		//--------------------------------------------------------------//
		// END: Records-Eyepiece Advertising at Race/Score		//
		//--------------------------------------------------------------//
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkServerLoad () {
		global $aseco;

		if ( ($this->config['NICEMODE'][0]['ENABLED'][0] == true) && ($this->config['NICEMODE'][0]['FORCE'][0] == false) ) {

			// Get Playercount
			$player_count = count($aseco->server->players->player_list);

			// Check Playercount and if to high, switch to nicemode
			if ( ($this->config['States']['NiceMode'] == false) && ($player_count >= $this->config['NICEMODE'][0]['LIMITS'][0]['UPPER_LIMIT'][0]) ) {

				// Turn nicemode on
				$this->config['States']['NiceMode'] = true;

				// Make sure the Widgets are refreshed without the Player highlites
				$this->config['States']['DedimaniaRecords']['NeedUpdate']	= true;
				$this->config['States']['LocalRecords']['NeedUpdate']	= true;

				// Set new refresh interval
				$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] = $this->config['NICEMODE'][0]['REFRESH_INTERVAL'][0];
			}
			else if ( ($this->config['States']['NiceMode'] == true) && ($player_count <= $this->config['NICEMODE'][0]['LIMITS'][0]['LOWER_LIMIT'][0]) ) {

				// Turn nicemode off
				$this->config['States']['NiceMode'] = false;

				// Restore default refresh interval
				$this->config['FEATURES'][0]['REFRESH_INTERVAL'][0] = $this->config['REFRESH_INTERVAL_DEFAULT'][0];
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function formatNumber ($num, $dec) {

		if ( ($this->config['FEATURES'][0]['SHORTEN_NUMBERS'][0] == true) && ($num > 1000) ) {
			return intval($num / 1000) .'k';
		}
		else {
			return number_format($num, $dec, $this->config['NumberFormat'][$this->config['FEATURES'][0]['NUMBER_FORMAT'][0]]['decimal_sep'], $this->config['NumberFormat'][$this->config['FEATURES'][0]['NUMBER_FORMAT'][0]]['thousands_sep']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getLastMap ($map) {
		global $aseco;

		if ($this->cache['Map']['Current']['uid'] === false) {
			$uid = false;
			foreach (array_reverse($aseco->plugins['PluginRaspJukebox']->jb_buffer) as $entry) {
				if ($entry != $map->uid) {
					$uid = $entry;
					break;
				}
			}
			$last = $this->getMapData($uid);

			// Retrieve MX data
			if ( function_exists('findMXdata') ) {
				$mx = findMXdata($last['uid'], $last['environment'], $last['exever'], true);		// findMXdata() from basic.inc.php
				$last['type']		= ((isset($mx->type) ) ? $mx->type : 'unknown');
				$last['style']		= ((isset($mx->style) ) ? $mx->style : 'unknown');
				$last['diffic']		= ((isset($mx->diffic) ) ? $mx->diffic : 'unknown');
				$last['routes']		= ((isset($mx->routes) ) ? $mx->routes : 'unknown');
				$last['awards']		= ((isset($mx->awards) ) ? $mx->awards : 'unknown');
				$last['section']	= ((isset($mx->section) ) ? $mx->section: 'unknown');
				$last['imageurl']	= ((isset($mx->imageurl) ) ? $aseco->handleSpecialChars($mx->imageurl .'.jpg') : (($last['imageurl'] !== false) ? $last['imageurl'] : $this->config['IMAGES'][0]['NO_SCREENSHOT'][0]));
				$last['pageurl']	= ((isset($mx->pageurl) ) ? $aseco->handleSpecialChars($mx->pageurl) : false);
				$last['dloadurl']	= ((isset($mx->dloadurl) ) ? $aseco->handleSpecialChars($mx->dloadurl) : false);
				$last['replayurl']	= ((isset($mx->replayurl) ) ? $aseco->handleSpecialChars($mx->replayurl) : false);
			}
			else {
				$last['type']		= 'unknown';
				$last['style']		= 'unknown';
				$last['diffic']		= 'unknown';
				$last['routes']		= 'unknown';
				$last['awards']		= 'unknown';
				$last['section']	= 'unknown';
				$last['imageurl']	= $this->config['IMAGES'][0]['NO_SCREENSHOT'][0];
				$last['pageurl']	= false;
				$last['dloadurl']	= false;
				$last['replayurl']	= false;
			}

			return $last;
		}
		else {
			return $this->cache['Map']['Current'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentMap () {
		global $aseco;

		$current = $this->getMapData($aseco->server->maps->current->uid);

		// MX-Part
		$current['type']		= ((isset($aseco->server->maps->current->mx->type) ) ? $aseco->server->maps->current->mx->type : 'unknown');
		$current['style']		= ((isset($aseco->server->maps->current->mx->style) ) ? $aseco->server->maps->current->mx->style : 'unknown');
		$current['diffic']		= ((isset($aseco->server->maps->current->mx->diffic) ) ? $aseco->server->maps->current->mx->diffic : 'unknown');
		$current['routes']		= ((isset($aseco->server->maps->current->mx->routes) ) ? $aseco->server->maps->current->mx->routes : 'unknown');
		$current['awards']		= ((isset($aseco->server->maps->current->mx->awards) ) ? $aseco->server->maps->current->mx->awards : 'unknown');
		$current['section']		= ((isset($aseco->server->maps->current->mx->section) ) ? $aseco->server->maps->current->mx->section : 'unknown');
		$current['imageurl']		= ((isset($aseco->server->maps->current->mx->imageurl) ) ? $aseco->handleSpecialChars($aseco->server->maps->current->mx->imageurl .'?.jpg') : $this->config['IMAGES'][0]['NO_SCREENSHOT'][0]);
		$current['pageurl']		= ((isset($aseco->server->maps->current->mx->pageurl) ) ? $aseco->handleSpecialChars($aseco->server->maps->current->mx->pageurl) : false);
		$current['dloadurl']		= ((isset($aseco->server->maps->current->mx->dloadurl) ) ? $aseco->handleSpecialChars($aseco->server->maps->current->mx->dloadurl) : false);
		$current['replayurl']		= ((isset($aseco->server->maps->current->mx->replayurl) ) ? $aseco->handleSpecialChars($aseco->server->maps->current->mx->replayurl) : false);

		return $current;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getNextMap () {
		global $aseco;

		// Set $filename to false to see where or if a Map is found
		$uid = false;

		// Is a Map in the Jukebox?
		if ($this->cache['Map']['Jukebox'] !== false) {
			$uid = $this->cache['Map']['Jukebox']['uid'];
		}

		// Was a Map in the Jukebox? If not, ask the Dedicated-Server which Map is next.
		if ($uid === false) {
			// Get next Map
			$nextmap = $aseco->client->query('GetNextMapInfo');
			$uid = $nextmap['UId'];
		}

		if ($uid !== false) {
			// Retrieve the map data
			$next = $this->getMapData($uid);

			// Retrieve MX data
			if ( function_exists('findMXdata') ) {
				$mx = findMXdata($next['uid'], $next['environment'], $next['exever'], true);		// findMXdata() from basic.inc.php
				$next['type']		= ((isset($mx->type) ) ? $mx->type : 'unknown');
				$next['style']		= ((isset($mx->style) ) ? $mx->style : 'unknown');
				$next['diffic']		= ((isset($mx->diffic) ) ? $mx->diffic : 'unknown');
				$next['routes']		= ((isset($mx->routes) ) ? $mx->routes : 'unknown');
				$next['awards']		= ((isset($mx->awards) ) ? $mx->awards : 'unknown');
				$next['section']	= ((isset($mx->section) ) ? $mx->section: 'unknown');
				$next['imageurl']	= ((isset($mx->imageurl) ) ? $aseco->handleSpecialChars($mx->imageurl .'?.jpg') : (($next['imageurl'] !== false) ? $next['imageurl'] : $this->config['IMAGES'][0]['NO_SCREENSHOT'][0]));
				$next['pageurl']	= ((isset($mx->pageurl) ) ? $aseco->handleSpecialChars($mx->pageurl) : false);
				$next['dloadurl']	= ((isset($mx->dloadurl) ) ? $aseco->handleSpecialChars($mx->dloadurl) : false);
				$next['replayurl']	= ((isset($mx->replayurl) ) ? $aseco->handleSpecialChars($mx->replayurl) : false);
			}
			else {
				$next['type']		= 'unknown';
				$next['style']		= 'unknown';
				$next['diffic']		= 'unknown';
				$next['routes']		= 'unknown';
				$next['awards']		= 'unknown';
				$next['section']	= 'unknown';
				$next['imageurl']	= $this->config['IMAGES'][0]['NO_SCREENSHOT'][0];
				$next['pageurl']	= false;
				$next['dloadurl']	= false;
				$next['replayurl']	= false;
			}

			return $next;
		}
		else {
			return $this->getEmptyMapInfo();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMapData ($uid) {

		foreach ($this->cache['MapList'] as $map) {
			if ($map['uid'] == $uid) {
				return $map;
			}
		}

		// Fallback
		return $this->getEmptyMapInfo();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getCurrentRanking ($limit, $start) {
		global $aseco;

		return $aseco->server->rankings->getRange($start, $limit);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMaplist ($mapfile = false) {
		global $aseco;

		$mapinfos = array();

		// If $map == false, read the whole Maplist from Server,
		// otherwise add only given Map to the $this->cache['MapList']
		if ($mapfile == false) {

			// Init environment/mood counter
			$this->cache['MaplistCounts']['Environment'] = array(
				'CANYON'	=> 0,
				'STADIUM'	=> 0,
				'VALLEY'	=> 0,
			);
			$this->cache['MaplistCounts']['Mood'] = array(
				'SUNRISE'	=> 0,
				'DAY'		=> 0,
				'SUNSET'	=> 0,
				'NIGHT'		=> 0
			);

			// Clean up before filling
			$this->cache['MapList'] = array();
			$this->cache['MapAuthors'] = array();


		}
		else {
//			// Parse the GBX Mapfile
//			$gbx = new GBXChallMapFetcher(true);
//			try {
//				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
//					$gbx->processFile($aseco->server->mapdir . iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $aseco->stripBOM($mapfile)));
//				}
//				else {
//					$gbx->processFile($aseco->server->mapdir . $aseco->stripBOM($mapfile));
//				}
//
//				// Try to find this Map in the current Maplist and if, do not add them again!
//				// The reason for this behavior is, that this Map is only added to the Jukebox and is not an new Map.
//				$found = false;
//				foreach ($this->cache['MapList'] as $key => $row) {
//					if ($row['uid'] == $gbx->uid) {
//						$found = true;
//						break;
//					}
//				}
//				unset($row);
//				if ($found == false) {
//					// Just work on this added Map only
//					$mapinfos[] = array(
//						'FileName'	=> $mapfile
//					);
//				}
//			}
//			catch (Exception $e) {
//				// Ignore if Map could not be parsed
//				trigger_error('[RecordsEyepiece] Could not read Map ['. $aseco->server->mapdir . $aseco->stripBOM($mapfile) .'] at $this->getMaplist(): '. $e->getMessage(), E_USER_WARNING);
//			}
		}

//		if ( !empty($mapinfos) ) {

			// Clean up before filling
			$this->cache['MapList'] = array();
			$this->cache['MapAuthors'] = array();

			foreach ($aseco->server->maps->map_list as $mapob) {
				$map = array();

				$map['uid']		= $mapob->uid;
				$map['dbid']		= $mapob->id;
				$map['name']		= $this->handleSpecialChars($mapob->name);
				$map['name_stripped']	= $this->handleSpecialChars($mapob->name_stripped);
				$map['author']		= $mapob->author;
				$map['author_nation']	= $mapob->author_nation;
				$map['mood']		= $mapob->mood;					// Sunrise, Day, Sunset, Night
				$map['multilap']	= $mapob->multilap;				// true, false
				$map['filename']	= $mapob->filename;
				$map['environment']	= $mapob->environment;
				$map['exever']		= $mapob->exeversion;
				$map['karma']		= 0;						// Preset, Karma are calculated later

				if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
					$map['authortime']		= $this->formatNumber($mapob->author_score, 0);
					$map['goldtime']		= $this->formatNumber($mapob->goldtime, 0);
					$map['silvertime']		= $this->formatNumber($mapob->silvertime, 0);
					$map['bronzetime']		= $this->formatNumber($mapob->bronzetime, 0);

					// Unformated for Maplist-Filter
					$map['authortime_filter']	= $mapob->author_score;
					$map['goldtime_filter']		= $mapob->goldtime;
					$map['silvertime_filter']	= $mapob->silvertime;
					$map['bronzetime_filter']	= $mapob->bronzetime;
				}
				else {
					// All other GameModes
					$map['authortime']		= $aseco->formatTime($mapob->author_time);
					$map['goldtime']		= $aseco->formatTime($mapob->goldtime);
					$map['silvertime']		= $aseco->formatTime($mapob->silvertime);
					$map['bronzetime']		= $aseco->formatTime($mapob->bronzetime);

					// Unformated for Maplist-Filter
					$map['authortime_filter']	= $mapob->author_time;
					$map['goldtime_filter']		= $mapob->goldtime;
					$map['silvertime_filter']	= $mapob->silvertime;
					$map['bronzetime_filter']	= $mapob->bronzetime;
				}

				// Add to the Maplist
				$this->cache['MapList'][] = $map;

				// Add the MapAuthor to the list
				$this->cache['MapAuthors'][] = $map['author'];

				// Setup the Cache for the AuthorNation
				if ( ( isset($map['author_nation']) ) && ($map['author_nation'] != 'OTH') ) {
					$this->cache['MapAuthorNation'][$map['author']] = $map['author_nation'];
				}

				// Count this environment for Maplistfilter
				$this->cache['MaplistCounts']['Environment'][strtoupper($map['environment'])] ++;

				// Count this mood for Maplistfilter
				$this->cache['MaplistCounts']['Mood'][strtoupper($map['mood'])] ++;
			}

			if (count($this->cache['MapList']) > 0) {

				if ($this->config['FEATURES'][0]['MAPLIST'][0]['SORTING'][0] == 'AUTHOR') {

					// Now sort Maplist by Author and Map
					$name = array();
					$author = array();
					foreach ($this->cache['MapList'] as $key => $row) {
						$name[$key]	= strtolower($row['name_stripped']);
						$author[$key]	= strtolower($row['author']);
					}
					array_multisort($author, SORT_ASC, $name, SORT_ASC, $this->cache['MapList']);
					unset($name, $author);
				}
				else if ($this->config['FEATURES'][0]['MAPLIST'][0]['SORTING'][0] == 'MAP') {

					// Now sort Maplist by Mapname
					$name = array();
					foreach ($this->cache['MapList'] as $key => $row) {
						$name[$key] = strtolower($row['name_stripped']);
					}
					array_multisort($name, SORT_ASC, $this->cache['MapList']);
					unset($name);
				}


				// Load the Karma for all Maps
				$this->calculateMapKarma();

				// Now try to find the AuthorNation for Maps that did not have stored that,
				// but other maps maybe does or the author has already visited this server.
				foreach ($this->cache['MapList'] as $map) {
					if ( isset($this->cache['MapAuthorNation'][$map['author']]) ) {
						$map['author_nation'] = $this->cache['MapAuthorNation'][$map['author']];
					}
				}
			}

			if (count($this->cache['MapAuthors']) > 0) {
				// Make the MapAuthors list unique and sort them
				$this->cache['MapAuthors'] = array_unique($this->cache['MapAuthors']);
				natcasesort($this->cache['MapAuthors']);
				$this->cache['MapAuthors'] = array_values($this->cache['MapAuthors']);
			}
//		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function calculateMapKarma () {
		global $aseco;

		$data = array();
		if ($this->config['FEATURES'][0]['KARMA'][0]['CALCULATION_METHOD'][0] == 'tmkarma') {
			$votings = array();
			$values = array(
				'Fantastic'	=> 3,
				'Beautiful'	=> 2,
				'Good'		=> 1,
				'Bad'		=> -1,
				'Poor'		=> -2,
				'Waste'		=> -3,
			);

			// Count all votings for each Map
			foreach ($values as $name => $vote) {
				$query = "
				SELECT
					`m`.`MapId`,
					COUNT(`Score`) AS `Count`
				FROM `%prefix%maps` AS `m`
				LEFT JOIN `%prefix%ratings` AS `k` ON `m`.`MapId` = `k`.`MapId`
				WHERE `k`.`Score` = ". $vote ."
				GROUP BY `m`.`MapId`;
				";

				$res = $aseco->db->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						while ($row = $res->fetch_object()) {
							$votings[$row->MapId][$name] = $row->Count;
						}
					}
					$res->free_result();
				}
			}
			unset( $vote);


			// Make sure all Maps has set all possible "votes"
			foreach ($votings as $id => $unused) {
				foreach ($values as $name => $vote) {
					if ( !isset($votings[$id][$name]) ) {
						$votings[$id][$name] = 0;
					}
				}
			}
			unset($values, $vote, $id, $unused);


			foreach ($votings as $MapId => $unused) {
				$totalvotes = (
					$votings[$MapId]['Fantastic'] +
					$votings[$MapId]['Beautiful'] +
					$votings[$MapId]['Good'] +
					$votings[$MapId]['Bad'] +
					$votings[$MapId]['Poor'] +
					$votings[$MapId]['Waste']
				);

				// Prevention of "illegal division by zero"
				if ($totalvotes == 0) {
					$totalvotes = 0.0000000000001;
				}

				$good_votes = (
					($votings[$MapId]['Fantastic'] * 100) +
					($votings[$MapId]['Beautiful'] * 80) +
					($votings[$MapId]['Good'] * 60)
				);
				$bad_votes = (
					($votings[$MapId]['Bad'] * 40) +
					($votings[$MapId]['Poor'] * 20) +
					($votings[$MapId]['Waste'] * 0)
				);

				// Store on MapId the Karma and Totalvotes
				$data[$MapId] = array(
					'karma'		=> floor( ($good_votes + $bad_votes) / $totalvotes),
					'votes'		=> $totalvotes,
				);
			}
			unset($MapId, $unused);
		}
		else {
			// Calculate the local Karma like RASP/Karma
			$query = "
			SELECT
				`MapId`,
				SUM(`Score`) AS `Karma`,
				COUNT(`Score`) AS `Count`
			FROM `%prefix%ratings`
			GROUP BY `MapId`;
			";

			$res = $aseco->db->query($query);
			if ($res) {
				if ($res->num_rows > 0) {
					while ($row = $res->fetch_object()) {
						$data[$row->MapId]['karma'] = $row->Karma;
						$data[$row->MapId]['votes'] = $row->Count;
					}
				}
				$res->free_result();
			}
		}


		// Add Karma to Maplist
		foreach ($this->cache['MapList'] as &$map) {
			$map['karma']		= (isset($data[$map['dbid']]) ? $data[$map['dbid']]['karma'] : 0);
			$map['karma_votes']	= (isset($data[$map['dbid']]) ? $data[$map['dbid']]['votes'] : 0);
		}
		unset($data, $map);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function loadPlayerNations () {
		global $aseco;

		$logins = array();
		$query = "
		SELECT
			`Login`,
			`Nation`
		FROM `%prefix%players`;
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_object()) {
					$logins[$row->Login] = $row->Nation;
				}
			}
			$res->free_result();
		}
		return $logins;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getEmptyMapInfo () {
		global $aseco;

		// Create an empty Map Info (required for some situations)
		$empty = array();
		$empty['name']		= 'unknown';
		$empty['name_stripped']	= 'unknown';
		$empty['author']	= 'unknown';
		$empty['author_nation']	= 'other';
		$empty['uid']		= false;
		$empty['mood']		= 'unknown';
		$empty['multilap']	= false;
		$empty['karma']		= 0;
		$empty['filename']	= 'unknown';
		$empty['environment']	= 'unknown';
		$empty['exever']	= 'unknown';
		$empty['imageurl']	= $this->config['IMAGES'][0]['NO_SCREENSHOT'][0];

		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$empty['authortime']	= '---';	// AuthorScore
			$empty['goldtime']	= '---';
			$empty['silvertime']	= '---';
			$empty['bronzetime']	= '---';

			// Unformated for Maplist-Filter
			$empty['authortime_filter']	= '---';
			$empty['goldtime_filter']	= '---';
			$empty['silvertime_filter']	= '---';
			$empty['bronzetime_filter']	= '---';
		}
		else {
			// All other GameModes
			$empty['authortime']	= '-:--.---';	// AuthorTime
			$empty['goldtime']	= '-:--.---';
			$empty['silvertime']	= '-:--.---';
			$empty['bronzetime']	= '-:--.---';

			// Unformated for Maplist-Filter
			$empty['authortime_filter']	= '-:--.---';
			$empty['goldtime_filter']	= '-:--.---';
			$empty['silvertime_filter']	= '-:--.---';
			$empty['bronzetime_filter']	= '-:--.---';
		}

		// MX part
		$empty['type']		= 'unknown';
		$empty['style']		= 'unknown';
		$empty['diffic']	= 'unknown';
		$empty['routes']	= 'unknown';
		$empty['awards']	= 'unknown';
		$empty['section']	= 'unknown';
		$empty['imageurl']	= $this->config['IMAGES'][0]['NO_SCREENSHOT'][0];
		$empty['pageurl']	= false;
		$empty['dloadurl']	= false;
		$empty['replayurl']	= false;
		return $empty;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function storePlayersRoundscore () {
		global $aseco;

		if ($aseco->server->rankings->count() > 0) {
			$query = "
			UPDATE `%prefix%players`
			SET `RoundPoints` = CASE `PlayerId`
			";

			// Add all Players with a Score
			$ranks = array();

			$i = 0;
			foreach ($aseco->server->rankings->ranking_list as $login => $data) {
				if ($data->score > 0) {
					$ranks[] = array(
						'pid'	=> $aseco->server->players->getPlayerId($login),
						'score'	=> $data->score,
					);
				}
			}

			if (count($ranks) > 0) {
				// Sort by PlayerId
				$sort = array();
				foreach ($ranks as $key => $row) {
					$sort[$key] = $row['pid'];
				}
				array_multisort($sort, SORT_NUMERIC, SORT_ASC, $ranks);
				unset($sort);

				$playerids = array();
				foreach ($ranks as $key => $row) {
					$playerids[] = $row['pid'];
					$query .= "WHEN ". $row['pid'] ." THEN `RoundPoints` + ". $row['score'] .LF;
				}

				$query .= "
				END
				WHERE `PlayerId` IN (". implode(',', $playerids) .");
				";

				// Update only if one Player has a Score
				if (count($playerids) > 0) {
					$result = $aseco->db->query($query);
					if (!$result) {
						$aseco->console('[RecordsEyepiece] UPDATE `RoundPoints` failed: [for statement "'. str_replace("\t", '', $query) .'"]');
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


		if ($this->config['FEATURES'][0]['ILLUMINATE_NAMES'][0] == true) {
			// Replace too dark colors with lighter ones
			$string = preg_replace('/\${1}(000|111|222|333|444|555)/i', '\$AAA', $string);
		}


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
}

?>
