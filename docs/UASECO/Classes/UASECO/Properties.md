# Class UASECO
###### Documentation of UASECO

Core Class of UASECO with many useful Methods.



## [Properties](_#Properties)


| Members								| Description
|-----------------------------------------------------------------------|----------------------------
| `$aseco->client`							| Class GbxRemote (includes/core/XmlRpc/GbxRemote.php)
| `$aseco->parser`							| [Class XmlParser](/Development/Classes/XmlParser.php) object
| `$aseco->checkpoints`							| [Class Checkpoint](/Development/Classes/Checkpoint.php) object
| `$aseco->continent`							| [Class Continent](/Development/Classes/Continent.php) object
| `$aseco->country`							| [Class Country](/Development/Classes/Country.php) object
| `$aseco->db`								| [Class Database](/Development/Classes/Database.php) object
| `$aseco->locales`							| [Class Message](/Development/Classes/Message.php) object
| `$aseco->webrequest`							| [Class WebRequest](/Development/Classes/WebRequest.php) object
| `$aseco->windows`							| Class WindowList object, which handles the actions for [Class Window](/Development/Classes/Window.php)
| `$aseco->server`							| [Class Server](/Development/Classes/Server.php) object
| `$aseco->server->maps`						| [Class MapList](/Development/Classes/MapList.php) object
| `$aseco->server->players`						| [Class PlayerList](/Development/Classes/PlayerList.php) object
| `$aseco->server->rankings`						| [Class RankingList](/Development/Classes/RankingList.php) object
| `$aseco->server->mutelist`						| &nbsp;
| `$aseco->debug`							| Holds the boolean status for logging debugging informations
| `$aseco->registered_events`						| Holds a list of registered events and the callbacks of the listening Plugins
| `$aseco->registered_chatcmds`						| Holds a list of registered chat commands and the callbacks of the Plugins
| `$aseco->chat_colors`							| Holds the list of `config/UASECO.xml` at `<colors>`
| `$aseco->chat_messages`						| Holds the list of `config/UASECO.xml` at `<messages>`
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
