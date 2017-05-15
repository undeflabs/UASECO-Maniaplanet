# Class Gameinfo
###### Documentation of includes/core/gameinfo.class.php

Provides information to the current game which is running.



## [Properties](_#Properties)


| Members								| Example data or description
|-----------------------------------------------------------------------|----------------------------
| `$aseco->server->gameinfo->mode`					| The [Gamemode ID](/Development/Classes/Gameinfo.php#Constants) e.g. `2`
| `$aseco->server->gameinfo->script`					| `array()` with values from [`GetModeScriptInfo`](/Dedicated-Server/List-Methods.php#GetModeScriptInfo)
| `$aseco->server->gameinfo->matchmaking`				| `array()` with [ModeMatchmaking](http://doc.maniaplanet.com/dedicated-server/settings-list.html#ModeMatchmaking) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->modebase`					| `array()` with [ModeBase](http://doc.maniaplanet.com/dedicated-server/settings-list.html#All--ModeBase-) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->rounds`					| `array()` with [Rounds](http://doc.maniaplanet.com/dedicated-server/settings-list.html#Rounds---RoundsBase-) and [RoundsBase](http://doc.maniaplanet.com/dedicated-server/settings-list.html#RoundsBase) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->time_attack`				| `array()` with [TimeAttack](http://doc.maniaplanet.com/dedicated-server/settings-list.html#TimeAttack) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->team`					| `array()` with [Team](http://doc.maniaplanet.com/dedicated-server/settings-list.html#Team---RoundsBase-) and [RoundsBase](http://doc.maniaplanet.com/dedicated-server/settings-list.html#RoundsBase) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->laps`					| `array()` with [Laps](http://doc.maniaplanet.com/dedicated-server/settings-list.html#Laps) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->cup`					| `array()` with [Cup](http://doc.maniaplanet.com/dedicated-server/settings-list.html#Cup---RoundsBase-) and [RoundsBase](http://doc.maniaplanet.com/dedicated-server/settings-list.html#RoundsBase) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->team_attack`				| `array()` with [TeamAttack](http://doc.maniaplanet.com/dedicated-server/settings-list.html#TeamAttack) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->chase`					| `array()` with [Chase](http://doc.maniaplanet.com/dedicated-server/settings-list.html#Chase) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->knockout`					| `array()` with [Knockout](https://forum.maniaplanet.com/viewtopic.php?p=247611) (supported) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)
| `$aseco->server->gameinfo->doppler`					| `array()` with [Doppler](https://forum.maniaplanet.com/viewtopic.php?p=240367) settings from [`GetModeScriptSettings`](/Dedicated-Server/List-Methods.php#GetModeScriptSettings)


> The names in a array (e.g. `$aseco->server->gameinfo->rounds`) are named as they are returned from `GetModeScriptSettings`,
> but the prefix `S_` is removed.
> Example: `S_UseAlternateRules` is accessible as `$aseco->server->gameinfo->rounds['UseAlternateRules']`.

See the full list of supported Modescript Settings in the [Modescript Settings documentation](/Dedicated-Server/Modescript-Settings.php).
