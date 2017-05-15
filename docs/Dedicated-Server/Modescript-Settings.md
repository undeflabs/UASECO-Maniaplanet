
# Dedicated Server
###### Description of the Modescript Settings





## [ModeMatchmaking](_#ModeMatchmaking)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_MatchmakingAPIUrl`							| https://matchmaking.maniaplanet.com/v8			| URL of the matchmaking API. If you do not plan to use a custom matchmaking function leave this setting at its default value.
| `S_MatchmakingMode`							| 0								| This is the most important setting. It can take one of these five values : 0 -> matchmaking turned off, standard server; 1 -> matchmaking turned on, use this server as a lobby server; 2 -> matchmaking turned on, use this server as a match server; 3 -> matchmaking turned off, use this server as a universal lobby server; 4 -> matchmaking turned off, use this server as a universal match server.
| `S_MatchmakingRematchRatio`						| -1.0								| Set the minimum ratio of players that have to agree to play a rematch before launching one. The value range from 0.0 to 1.0. Any negative value turns off the rematch vote.
| `S_MatchmakingRematchNbMax`						| 2								| Set the maximum number of consecutive rematches possible.
| `S_MatchmakingVoteForMap`						| false								| (Dis-)Allow the players to vote for the next map.
| `S_MatchmakingProgressive`						| false								| Enable or disable the progressive matchmaking.
| `S_MatchmakingWaitingTime`						| 45								| Waiting time at the beginning of the matches.
| `S_LobbyRoundPerMap`							| 6								| Number of rounds played before switching to the next map.
| `S_LobbyMatchmakerPerRound`						| 30								| Set how many times the matchmaking function is called before ending the current round of King of the Lobby.
| `S_LobbyMatchmakerWait`						| 2								| Set the waiting time before calling the matchmaking function again.
| `S_LobbyMatchmakerTime`						| 8								| Duration (in seconds) of the matchmaking function. It allows the players to see who they will play their match with or cancel it if necessary.
| `S_LobbyDisplayMasters`						| true								| Display a list of Masters players in the lobby.
| `S_LobbyDisableUI`							| false								| Disable lobby UI
| `S_MatchmakingErrorMessage`						| An error occured in the matchmaking API. If the problem persist please try to contact this server administrator.	| This message is displayed in the chat to inform the players that an error occured in the matchmaking system.
| `S_MatchmakingLogAPIError`						| false								| Log the API errors. You can activate it if something doesn't work and you have to investigate. Otherwise it's better to leave it turned off because this can quickly write huge log files.
| `S_MatchmakingLogAPIDebug`						| false								| Same as above, only turn it on if necessary.
| `S_MatchmakingLogMiscDebug`						| false								| Same as above, only turn it on if necessary.
| `S_ProgressiveActivation_WaitingTime`					| 180000							| Average waiting time before progressive matchmaking activate.
| `S_ProgressiveActivation_PlayersNbRatio`				| 1								| Multiply the required players nb by this, if theres less player in the lobby activate progressive.



***



## [ModeBase](_#ModeBase) (for all Modescripts)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_ChatTime`								| 15								| Chat time at the end of the map
| `S_AllowRespawn`							| true								| Allow the players to respawn or not
| `S_WarmUpDuration`							| -1								| Duration of the warm up phase (-1 to disable)
| `S_UseScriptCallbacks`						| false								| Turn on/off the script callbacks, useful for server manager
| `S_UseLegacyCallbacks`						| true								| Turn on/off the legacy callbacks
| `S_ScoresTableStylePath`						| ""								| Try to load a scores table style from an XML file



***



## [RoundsBase](_#RoundsBase) (for all Rounds based Modescripts)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_PointsLimit`							| 100								| Points limit (<= 0 to disable)
| `S_FinishTimeout`							| -1								| Finish timeout (<= 0 to disable)
| `S_UseAlternateRules`							| false								| Use alternate rules
| `S_ForceLapsNb`							| -1								| Force number of laps (<= 0 to disable)
| `S_DisplayTimeDiff`							| false								| Display time difference at checkpoint



***



## [Rounds](_#Rounds) (+RoundsBase)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_PointsLimit`							| 50								| Points limit (<= 0 to disable)
| `S_UseTieBreak`							| true								| Continue to play the map until the tie is broken



***



## [TimeAttack](_#TimeAttack)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_TimeLimit`								| 300								| Time limit



***



## [Team](_#Team) (+RoundsBase)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_PointsLimit`							| 5								| Points limit (<= 0 to disable)
| `S_MaxPointsPerRound`							| 6								| The maxium number of points attributed to the first player to cross the finish line
| `S_PointsGap`								| 1								| The number of points lead a team must have to win the map
| `S_UsePlayerClublinks`						| false								| Use the players clublinks, or otherwise use the default teams



***


## [Laps](_#Laps)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_TimeLimit`								| 0								| Time limit (<= 0 to disable)
| `S_ForceLapsNb`							| 5								| Number of Laps (<= 0 to disable)
| `S_FinishTimeout`							| -1								| Finish timeout (<= 0 to disable)



***



## [Cup](_#Cup) (+RoundsBase)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_RoundsPerMap`							| 5								| Rounds per map
| `S_NbOfWinners`							| 3								| Number of winners
| `S_WarmUpDuration`							| 2								| Duration of the warm up phase (<= 0 to disable)



***



## [TeamAttack](_#TeamAttack)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_TimeLimit`								| 300								| Time limit
| `S_MinPlayerPerClan`							| 3								| Minimum number of players per clan
| `S_MaxPlayerPerClan`							| 3								| Maximum number of players per clan
| `S_MaxClanNb`								| -1								| Maximum number of clans (<= 0 to disable)



***



## [Chase](_#Chase)

| Setting								| Default value							| Description
|-----------------------------------------------------------------------|---------------------------------------------------------------|-------------
| `S_TimeLimit`								| 900								| Time limit (0 to disable, -1 automatic based on author time)
| `S_MapPointsLimit`							| 3								| Map points limit
| `S_RoundPointsLimit`							| -5								| Round points limit (0 to disable, negative values automatic based on number of checkpoints)
| `S_RoundPointsGap`							| 3								| The number of round points lead a team must have to win the round
| `S_GiveUpMax`								| 1								| Maximum number of give up per team
| `S_MinPlayersNb`							| 3								| Minimum number of players in a team
| `S_ForceLapsNb`							| 10								| Number of Laps (-1 to use the map default, 0 to disable laps limit)
| `S_FinishTimeout`							| -1								| Finish timeout (-1 automatic based on author time)
| `S_DisplayWarning`							| true								| Display a big red message in the middle of the screen of the player that crosses a checkpoint when it wasn't it's turn.
| `S_CompetitiveMode`							| false								| Use competitive mode.
| `S_PauseBetweenRound`							| 15								| Pause duration between rounds.
| `S_WaitingTimeMax`							| 600								| Maximum waiting time before next map.
| `S_WaypointEventDelay`						| 500								| Waypoint event buffer delay.
| `S_UsePlayerClublinks`						| false								| Use the players clublinks, or otherwise use the default teams
| `S_NbPlayersPerTeamMax`						| 3								| Maximum number of players per team in matchmaking
| `S_NbPlayersPerTeamMin`						| 3								| Minimum number of players per team in matchmaking
