# Class UASECO
###### Documentation of UASECO


***


Core Class of UASECO with many useful Methods.



## [Properties](_#Properties)


| Members								| Description
|-----------------------------------------------------------------------|----------------------------
| `$aseco->client`							| Class GbxRemote (includes/core/XmlRpc/GbxRemote.php)
| `$aseco->parser`							| [Class XmlParser](/development/classes/xmlparser.php) object
| `$aseco->checkpoints`							| [Class Checkpoint](/development/classes/checkpoint.php) object
| `$aseco->continent`							| [Class Continent](/development/classes/continent.php) object
| `$aseco->country`							| [Class Country](/development/classes/country.php) object
| `$aseco->db`								| [Class Database](/development/classes/database.php) object
| `$aseco->locales`							| [Class Message](/development/classes/message.php) object
| `$aseco->webrequest`							| [Class WebRequest](/development/classes/webrequest.php) object
| `$aseco->windows`							| [Class WindowList](/development/classes/windowlist.php) object, which handles the actions for [Class Window](/development/classes/window.php)
| `$aseco->server`							| [Class Server](/development/classes/server.php) object
| `$aseco->server->maps`						| [Class MapList](/development/classes/maplist.php) object
| `$aseco->server->players`						| [Class PlayerList](/development/classes/playerlist.php) object
| `$aseco->server->rankings`						| [Class RankingList](/development/classes/rankinglist.php) object
| `$aseco->server->mutelist`						| &nbsp;
| `$aseco->debug`							| Holds the boolean status for logging debugging informations
| `$aseco->registered_events`						| Holds a list of registered events and the callbacks of the listening Plugins
| `$aseco->registered_chatcmds`						| Holds a list of registered chat commands and the callbacks of the Plugins
| `$aseco->chat_colors`							| Holds the list of `config/UASECO.xml` at `<colors>`
| `$aseco->logfile`							| array which holds the `$aseco->logfile['handle']` and `$aseco->logfile['file']` of the current logfile.
| `$aseco->plugins`							| Holds a list of active Plugins
| `$aseco->settings`							| Holds the whole settings of `config/UASECO.xml`, `config/access.xml`, `config/bannedips.xml`...
| `$aseco->titles`							| &nbsp;
| `$aseco->masteradmin_list`						| &nbsp;
| `$aseco->admin_list`							| &nbsp;
| `$aseco->admin_abilities`						| &nbsp;
| `$aseco->operator_list`						| &nbsp;
| `$aseco->operator_abilities`						| &nbsp;
| `$aseco->banned_ips`							| &nbsp;
| `$aseco->startup_phase`						| Holds the status of the startup phase, if false UASECO has finished startup successfully
| `$aseco->shutdown_phase`						| &nbsp;
| `$aseco->warmup_phase`						| Holds the status of the warm-up phase, if false warm-up is finished or there was no warm-up
| `$aseco->restarting`							| Holds the status of the map is restarting or not
| `$aseco->changing_to_gamemode`					| If not false then this contains the upcoming name of the Gamemode
| `$aseco->current_status`						| Dedicated Server status changes
| `$aseco->uptime`							| Holds the UASECO start-up time
| `$aseco->environments`						| Array of environments (e.g. 'Canyon', 'Stadium', 'Valley' and 'Lagoon')
