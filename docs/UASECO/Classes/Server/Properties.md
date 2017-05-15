# Class Server
###### Documentation of includes/core/server.class.php

Stores basic information of the server UASECO is running on.



## [Properties](_#Properties)


| Members								| Description
|-----------------------------------------------------------------------|------------
| `$aseco->server->xmlrpc['ip']`					| Settings from UASECO.xml <dedicated_server>
| `$aseco->server->xmlrpc['port']`					| Settings from UASECO.xml <dedicated_server>
| `$aseco->server->xmlrpc['login']`					| Settings from UASECO.xml <dedicated_server>
| `$aseco->server->xmlrpc['pass']`					| Settings from UASECO.xml <dedicated_server>
| `$aseco->server->maps`						| Used by [Class MapList](/Development/Classes/MapList.php)
| `$aseco->server->records`						| Used by [Class RecordList](/Development/Classes/RecordList.php)
| `$aseco->server->players`						| Used by [Class PlayerList](/Development/Classes/PlayerList.php)
| `$aseco->server->mutelist`						| Server wide mutelist
| `$aseco->server->gamestate`						| Holds actual gamestate: [`Server::RACE`](#Constants) or [`Server::SCORE`](#Constants)
| `$aseco->server->state_names`						| Holds a named representation array of loading states.



***


#### This class provides also the following data from the ListMethods of the dedicated server and keeps them up-to-date:



## [GetVersion](_#GetVersion)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->game`						| ManiaPlanet
| `$aseco->server->version`						| 3.3.0
| `$aseco->server->build`						| 2014-07-11_18_00
| `$aseco->server->title`						| TMCanyon
| `$aseco->server->api_version`						| 2013-04-16



## [GetSystemInfo](_#GetSystemInfo)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->id`							| 0
| `$aseco->server->login`						| labs_undef
| `$aseco->server->ip`							| 77.23.200.000
| `$aseco->server->port`						| 2351
| `$aseco->server->p2pport`						| 3451
| `$aseco->server->name`						| $I$09F»$FFFυηפєғ$09Fױ$FFFĿαвѕ
| `$aseco->server->zone`						| array('Europe', 'Germany', 'Bremen')
| `$aseco->server->comment`						| Enjoy the fun!



## [GetServerPlanets](_#GetServerPlanets)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->amount_planets`					| 305143



## [GetLadderServerLimits](_#GetLadderServerLimits)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->ladder_limit_min`					| 0
| `$aseco->server->ladder_limit_max`					| 50000



## [GameDataDirectory](_#GameDataDirectory)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->gamedir`						| /home/midi/bin/labs_undef/GameServer/UserData/



## [GetMapsDirectory](_#GetMapsDirectory)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->mapdir`						| /home/midi/bin/labs_undef/GameServer/UserData/Maps/



## [GetMainServerPlayerInfo](_#GetMainServerPlayerInfo)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->isrelay`						| false
| `$aseco->server->relaymaster`						| &nbsp;
| `$aseco->server->relay_list`						| array()

> Only available when `IsRelayServer` returns `true`.


## [GetServerOptions](_#GetServerOptions)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->options['Password']`					| yourchosenpassword
| `$aseco->server->options['PasswordForSpectator']`			| yourchosenpassword
| `$aseco->server->options['CurrentMaxPlayers']`			| 32
| `$aseco->server->options['NextMaxPlayers']`				| 32
| `$aseco->server->options['CurrentMaxSpectators']`			| 8
| `$aseco->server->options['NextMaxSpectators']`			| 8
| `$aseco->server->options['KeepPlayerSlots']`				| true
| `$aseco->server->options['IsP2PUpload']`				| true
| `$aseco->server->options['IsP2PDownload']`				| false
| `$aseco->server->options['CurrentLadderMode']`			| 1
| `$aseco->server->options['NextLadderMode']`				| 1
| `$aseco->server->options['CurrentVehicleNetQuality']`			| 0
| `$aseco->server->options['NextVehicleNetQuality']`			| 0
| `$aseco->server->options['CurrentCallVoteTimeOut']`			| 0
| `$aseco->server->options['NextCallVoteTimeOut']`			| 0
| `$aseco->server->options['CallVoteRatio']`				| -1
| `$aseco->server->options['AllowMapDownload']`				| false
| `$aseco->server->options['AutoSaveReplays']`				| false
| `$aseco->server->options['RefereePassword']`				| &nbsp;
| `$aseco->server->options['RefereeMode']`				| 0
| `$aseco->server->options['AutoSaveValidationReplays']`		| false
| `$aseco->server->options['HideServer']`				| 0
| `$aseco->server->options['CurrentUseChangingValidationSeed']`		| false
| `$aseco->server->options['NextUseChangingValidationSeed']`		| false
| `$aseco->server->options['ClientInputsMaxLatency']`			| 0



## [GetNetworkStats](_#GetNetworkStats)
| Members								| Example data
|-----------------------------------------------------------------------|-------------
| `$aseco->server->networkstats['Uptime']`				| 20496
| `$aseco->server->networkstats['NbrConnection']`			| 1121
| `$aseco->server->networkstats['MeanConnectionTime']`			| 20419
| `$aseco->server->networkstats['MeanNbrPlayer']`			| 0
| `$aseco->server->networkstats['RecvNetRate']`				| 3
| `$aseco->server->networkstats['SendNetRate']`				| 0
| `$aseco->server->networkstats['TotalReceivingSize']`			| 171
| `$aseco->server->networkstats['TotalSendingSize']`			| 350
