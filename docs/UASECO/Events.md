# Development
###### Description of each event in UASECO


***


## [onStartup ($aseco)](_#onStartup)
Event triggered after loading plugins.
> At this point UASECO does not have syncronized the status with server and does not have the server name or the player count or players loaded yet.


## [onSync ($aseco)](_#onSync)
Event triggered after server status has been syncronized.
> At this event `$aseco->server->players->player_list` is still empty!


## [onMainLoop ($aseco)](_#onMainLoop)
Event triggered every loop after all callbacks and method calls have been sent to server.


## [onEverySecond ($aseco)](_#onEverySecond)
Event triggered every second.
> The time between one triggering to the next can be longer then exact a second, this is when UASECO or a Plugin is busy!


## [onEveryTenSeconds ($aseco)](_#onEveryTenSeconds)
Event triggered every 10 seconds.
> The time between one triggering to the next can be longer then exact 10 seconds, this is when UASECO or a Plugin is busy!


## [onEveryFifteenSeconds ($aseco)](_#onEveryFifteenSeconds)
Event triggered every 15 seconds.
> The time between one triggering to the next can be longer then exact 15 seconds, this is when UASECO or a Plugin is busy!


## [onEveryMinute ($aseco)](_#onEveryMinute)
Event triggered every minute.
> The time between one triggering to the next can be longer then exact one minute, this is when UASECO or a Plugin is busy!


## [onShutdown ($aseco)](_#onShutdown)
Event triggered when UASECO is shutting down, ie. with `/admin shutdown`.


## [onPlayerConnect ($aseco, $player)](_#onPlayerConnect)
Main event, triggered when a Player connects to the server.


## [onPlayerConnectPostfix ($aseco, $player)](_#onPlayerConnectPostfix)
Postfix event, triggered when a Player connects to the server. This event is meant for access control.


## [onPlayerDisconnectPrepare ($aseco, $player)](_#onPlayerDisconnectPrepare)
Prefix event, triggered when a Player disconnects from the server.
This event is mainly used for cleanup the player data before they get stored into the database at the event `onPlayerDisconnect`.


## [onPlayerDisconnect ($aseco, $player)](_#onPlayerDisconnect)
Event triggered when a Player leaves the Server.


## [onPlayerChat ($aseco, $chat)](_#onPlayerChat)
Event triggered when a Player wrote something into the chat.


## [onServerChat ($aseco, $chat)](_#onServerChat)
Event triggered when a message from a Plugin is sent into the chat.


## [onPlayerManialinkPageAnswer ($aseco, $login, $params)](_#onPlayerManialinkPageAnswer)
Event triggered when a Player clicks a server-side manialink that has the `action` attribute, returns the Player login and the Key=Value pairs.


## [onPlayerStartLine ($aseco, $login)](_#onPlayerStartLine)
Event triggered when a Player starts a race.


## [onPlayerCheckpoint ($aseco, $params)](_#onPlayerStartLine)
Event triggered when a Player crosses a checkpoint.


## [onPlayerRespawn ($aseco, $login)](_#onPlayerRespawn)
Event triggered when a Player respawns at a waypoint (checkpoint, multilap, ...).


## [onPlayerGiveUp ($aseco, $login)](_#onPlayerGiveUp)
Event triggered sent when a player restarts.


## [onPlayerStunt ($aseco, $params)](_#onPlayerStunt)
Event triggered when a Player made a stunt figure.


## [onPlayerFinishLine ($aseco, $params)](_#onPlayerFinishLine)
Event triggered when a Player crosses a finish line.


## [onPlayerFinishLap ($aseco, $params)](_#onPlayerFinishLap)
Event triggered when a Player finished a lap on a multilap Map.


## [onPlayerFinishPrefix ($aseco, $finish)](_#onPlayerFinishPrefix)
Event triggered when a Player finished the map. Main event for checkpoint handling.


## [onPlayerFinish ($aseco, $finish)](_#onPlayerFinish)
Event triggered when a Player finished the map.


## [onPlayerWins ($aseco, $player)](_#onPlayerWins)
Event triggered before the Scoretable and only for that Player that wins this race.


## [onPlayerInfoChanged ($aseco, $changes)](_#onPlayerInfoChanged)
Event triggered when the Playerinfo changes, this happens when the Player switch from Player to a Spectator or changes the Team in Teammode.


## [onPlayerAlliesChanged ($aseco, $login)](_#onPlayerAlliesChanged)
TODO.


## [onPlayerIncoherence ($aseco, $state)](_#onPlayerIncoherence)
Event triggered when the red text that says that the Player time is invalid.


## [onPlayerRankingUpdated ($aseco)](_#onPlayerRankingUpdated)
Event triggered when the Player Rankings has been changed/reseted.


## [onLoadingMap ($aseco, $map)](_#onLoadingMap)
Event riggered when the map is loaded (without loaded records).


## [onUnloadingMap ($aseco, $uid)](_#onUnloadingMap)
Event triggered when the script start to unload a map.


## [onWarmUpStatusChanged ($aseco, $params)](_#onWarmUpStatusChanged)
Event triggered when the status of the warm up has been changed.


## [onWarmUpRoundChanged ($aseco, $params)](_#onWarmUpRoundChanged)
Event triggered when the status of the warm up while a round has been changed.


## [onBeginTurn ($aseco, $count)](_#onBeginTurn)
Event triggered at the beginning of each turn, if the mode uses turns.


## [onEndTurn ($aseco, $count)](_#onEndTurn)
Event triggered at the end of each turn, if the mode uses turns.


## [onBeginMatch ($aseco, $count)](_#onBeginMatch)
Event triggered at the beginning of each match, if the mode uses matches.


## [onEndMatch ($aseco, $count)](_#onEndMatch)
Event triggered at the end of each match, if the mode uses matches.


## [onBeginMap ($aseco, $uid)](_#onBeginMap)
Event triggered after `onLoadingMap`, when the script has started the map.


## [onRestartMap ($aseco, $map)](_#onRestartMap)
Event triggered when the map was restarted.


## [onEndMapRanking ($aseco, $map)](_#onEndMapRanking)
Event triggered at the end of map and before the ranking is finished.


## [onEndMapPrefix ($aseco, $map)](_#onEndMapPrefix)
Event triggered at the end of map (prefix event for chat-based votes) and is send before the event `onEndMap`.


## [onEndMap ($aseco, $map)](_#onEndMap)
Event triggered at the end of map (Main event)


## [onBeginRound ($aseco, $count)](_#onBeginRound)
Event triggered at the beginning of each round, if the mode uses rounds.


## [onEndRound ($aseco, $count)](_#onEndRound)
Event triggered at the end of each round, if the mode uses rounds.


## [onBeginPlaying ($aseco, $count)](_#onBeginPlaying)
Event triggered at the beginning of the play loop.


## [onEndPlaying ($aseco, $count)](_#onEndPlaying)
Event triggered at the end of the play loop.


## [onBeginPodium ($aseco)](_#onBeginPodium)
Event triggered at the beginning of podium sequence.


## [onEndPodium ($aseco)](_#onEndPodium)
Event triggered at the end of podium sequence.


## [onBeginChannelProgression ($aseco, $time)](_#onBeginChannelProgression)
Event triggered when the channel progression sequence starts.


## [onEndChannelProgression ($aseco, $time)](_#onEndChannelProgression)
Event triggered when the channel progression sequence ends.


## [onModeScriptChanged ($aseco, $mode)](_#onModeScriptChanged)
Event triggered at the very beginning of the script.
> Different events can cause the script to start/restart:
> * Launch of the server: Most of the time a XML-RPC client won't receive them because it is not yet connected to the server when the script start.
> * Restart map: This cause the script to restart, so the callbacks will be sent again.
> * Change mode: After the next map the new script replaces the old one and start from the beginning.


## [onBillUpdated ($aseco, $bill)](_#onBillUpdated)
Event triggered when the server received a transaction from a Player to check the bill state.
> $bill[0] = BillId
> $bill[1] = State
> $bill[2] = StateName
> $bill[3] = TransactionId


## [onEcho ($aseco, $echo)](_#onEcho)
TODO


## [onTunnelDataReceived ($aseco, $data)](_#onTunnelDataReceived)
Can be use with the Method `TunnelSendDataToLogin()` to communicate with the game server from the relay or the other way around.


## [onVoteUpdated ($aseco, $vote)](_#onVoteUpdated)
TODO


## [onModeScriptCommand ($aseco, $params)](_#onModeScriptCommand)
TODO


## [onMapListModified ($aseco, $data)](_#onMapListModified)
TODO


## [onModeScriptCallbackArray ($aseco, $data)](_#onModeScriptCallbackArray)
TODO


## [onLocalRecordsLoaded ($aseco, $records)](_#onLocalRecordsLoaded)
Event triggered when the `plugin.local_records.php` has loaded the local records.


## [onLocalRecordBestLoaded ($aseco, $time)](_#onLocalRecordBestLoaded)
Event triggered when the `plugin.local_records.php` has loaded the first record.


## [onLocalRecord ($aseco, $record)](_#onLocalRecord)
Event triggered when a Player received or driven a better time or score for his/her current local record.


## [onDedimaniaRecordsLoaded ($aseco, $records)](_#onDedimaniaRecordsLoaded)
Event triggered when the `plugin.dedimania.php` has loaded the dedimania records.


## [onDedimaniaRecord ($aseco, $record)](_#onDedimaniaRecord)
Event triggered when a Player received or driven a better time for his/her current Dedimania record.


## [onManiaExchangeBestLoaded ($aseco, $time)](_#onManiaExchangeBestLoaded)
Event triggered when the `uaseco.php` has loaded the Mania-Exchange first record.


## [onDonation ($aseco, $donation)](_#onDonation)
Event triggered when a Player donate some Planets with `/donate [N]`.


## [onPointsRepartitionLoaded ($aseco, $round_points)](_#onPointsRepartitionLoaded)
Event triggered when the point setup has been changed.


## [onUiProperties ($aseco, $params)](_#onUiProperties)
TODO


## [onModeUseTeams ($aseco, $params)](_#onModeUseTeams)
TODO


## [onPauseStatus ($aseco, $params)](_#onPauseStatus)
TODO


## [onKarmaChange ($aseco, $karma)](_#onKarmaChange)
Event triggered when a Player vote a Track.


## [onJukeboxChanged ($aseco, $command)](_#onJukeboxChanged)
Event triggered when the Jukebox has been changed.
> $command[0] = `add`, `clear`, `drop`, `play`, `replay`, `restart`, vskipv, vprevious`, `nextenvv
> $command[1] = track data (or `null` for the `clear` action)


## [onMapListChanged ($aseco, $command)](_#onMapListChanged)
Event triggered when the Maplist has been changed.
> $command[0] = `add`, `remove`, `rename`, `juke`, `unjuke`, `read`, `write`
> $command[1] = filename of Map (or `null` for the `write` or `read` action)


## [onMusicboxReloaded ($aseco)](_#onMusicboxReloaded)
Event triggered to alert that the music server config file (config/music_server.xml) was reloaded via `/music reload`.


## [onSendWindowMessage ($aseco, $data)](_#onSendWindowMessage)
TODO
