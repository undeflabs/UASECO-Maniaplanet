# Class Player
###### Documentation of includes/core/player.class.php


***


Structure of a Player, contains information from `GetPlayerInfo` and `GetDetailedPlayerInfo` ListMethods response.



## [Properties](_#Properties)


| Members								| Example data						| Description
|-----------------------------------------------------------------------|--------------------------------------------------------------------
| `$player->id`								| 1188							| ID of the Player in the Database
| `$player->pid`							| 237							| ID of the Player at the dedicated Server
| `$player->login`							| puennt_ennel						| &nbsp;
| `$player->nickname`							| $I$09Fぎтяα¢кєяѕ$AAA|$FFFυηפєғ $W$000'$F00'$FF0'	| &nbsp;
| `$player->nickname_stripped`						| ぎтяα¢кєяѕ|υηפєғ '''					| &nbsp;
| `$player->nickname_slug`						| ぎtrackers|undef '''					| &nbsp;
| `$player->language`							| de							| &nbsp;
| `$player->avatar`							| Skins/Avatars/my-very-own-avatar.dds			| &nbsp;
| `$player->clublink`							| http://www.example.com/clublink.xml			| &nbsp;
| `$player->ip`								| 77.23.200.000						| &nbsp;
| `$player->port`							| 2350							| &nbsp;
| `$player->downloadrate`						| 1835008						| &nbsp;
| `$player->uploadrate`							| 114688						| &nbsp;
| `$player->is_official`						| true							| &nbsp;
| `$player->is_referee`							| false							| &nbsp;
| `$player->is_podium_ready`						| true							| &nbsp;
| `$player->is_using_stereoscopy`					| false							| &nbsp;
| `$player->is_managed_by_other_server`					| false							| &nbsp;
| `$player->is_server`							| false							| &nbsp;
| `$player->is_broadcasting`						| false							| &nbsp;
| `$player->has_joined_game`						| true							| &nbsp;
| `$player->has_player_slot`						| true							| &nbsp;
| `$player->is_spectator`						| false							| &nbsp;
| `$player->forced_spectator`						| 0							| &nbsp;
| `$player->temporary_spectator`					| false							| &nbsp;
| `$player->pure_spectator`						| false							| &nbsp;
| `$player->target_autoselect`						| false							| &nbsp;
| `$player->target_spectating`						| undef.de						| If not false, then this contains the login from a Player the current Player is spectating.
| `$player->team_id`							| 1							| &nbsp;
| `$player->allies`							| array()						| &nbsp;
| `$player->server_rank`						| 14							| Server rank
| `$player->server_rank_total`						| 7027							| Max. amount of server ranks
| `$player->server_rank_average`					| 500.0							| Server average
| `$player->ladder_rank`						| 575							| &nbsp;
| `$player->ladder_score`						| 85340							| &nbsp;
| `$player->last_match_score`						| 0							| The dedicated Server retrieves this value not on demand, it takes a while until this value is up-to-date
| `$player->nb_wins`							| 24987							| &nbsp;
| `$player->nb_draws`							| 10721							| &nbsp;
| `$player->nb_losses`							| 15725							| &nbsp;
| `$player->client`							| v2014-07-11_16_43 (3.3.0)				| &nbsp;
| `$player->created`							| 1405188219						| Timestamp of connection from the Player
| `$player->zone_inscription`						| -1							| &nbsp;
| `$player->zone`							| array('Europe', 'Germany', 'Bremen')			| &nbsp;
| `$player->continent`							| Europe						| &nbsp;
| `$player->nation`							| GER							| &nbsp;
| `$player->visits`							| 154							| &nbsp;
| `$player->wins`							| 12							| &nbsp;
| `$player->new_wins`							| 1							| &nbsp;
| `$player->time_played`						| 1241							| &nbsp;
| `$player->data`							| array()						| Holds all Plugins settings
| `$player->unlocked`							| false							| &nbsp;
| `$player->pmbuf`							| array()						| &nbsp;
| `$player->mutelist`							| array()						| &nbsp;
| `$player->mutebuf`							| array()						| &nbsp;
| `$player->style`							| array()						| &nbsp;
| `$player->panels`							| array()						| &nbsp;
| `$player->panelbg`							| array()						| &nbsp;
| `$player->speclogin`							| undef.de						| &nbsp;
| `$player->dedirank`							| 0							| &nbsp;
| `$player->maplist`							| array()						| &nbsp;
| `$player->playerlist`							| array()						| &nbsp;
| `$player->msgs`							| array()						| &nbsp;
