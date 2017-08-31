
# Dedicated Server
###### Methods from API version: 2013-04-16

***

### [system.listMethods](_#system.listMethods)
Return an array of all available XML-RPC methods on this server.

#### Description
	array system.listMethods()

***

### [system.methodSignature](_#system.methodSignature)
Given the name of a method, return an array of legal signatures. Each signature is an array of strings.  The first item of each signature is the return type, and any others items are parameter types.

#### Description
	array system.methodSignature(string)

***

### [system.methodHelp](_#system.methodHelp)
Given the name of a method, return a help string.

#### Description
	string system.methodHelp(string)

***

### [system.multicall](_#system.multicall)
Process an array of calls, and return an array of results.  Calls should be structs of the form {`methodName`: string, `params`: array}. Each result will either be a single-item array containing the result value, or a struct of the form {`faultCode`: int, `faultString`: string}.  This is useful when you need to make lots of small calls without lots of round trips.

#### Description
	array system.multicall(array)

***

### [Authenticate](_#Authenticate)
Allow user authentication by specifying a login and a password, to gain access to the set of functionalities corresponding to this authorization level.

#### Description
	boolean Authenticate(string, string)

***

### [ChangeAuthPassword](_#ChangeAuthPassword)
Change the password for the specified login/user. Only available to SuperAdmin.

#### Description
	boolean ChangeAuthPassword(string, string)

***

### [EnableCallbacks](_#EnableCallbacks)
Allow the GameServer to call you back.

#### Description
	boolean EnableCallbacks(boolean)

***

### [SetApiVersion](_#SetApiVersion)
Define the wanted api.

#### Description
	boolean SetApiVersion(string)

***

### [GetVersion](_#GetVersion)
Returns a struct with the `Name`, `TitleId`, `Version`, `Build` and `ApiVersion` of the application remotely controlled.

#### Description
	struct GetVersion()

***

### [GetStatus](_#GetStatus)
Returns the current status of the server.

#### Description
	struct GetStatus()

***

### [QuitGame](_#QuitGame)
Quit the application. Only available to SuperAdmin.

#### Description
	boolean QuitGame()

***

### [CallVote](_#CallVote)
Call a vote for a cmd. The command is a XML string corresponding to an XmlRpc request. Only available to Admin.

#### Description
	boolean CallVote(string)

***

### [CallVoteEx](_#CallVoteEx)
Extended call vote. Same as CallVote, but you can additionally supply specific parameters for this vote: a ratio, a time out and who is voting. Special timeout values: a ratio of `-1` means default; a timeout of `0` means default, `1` means indefinite; Voters values: `0` means only active players, `1` means any player, `2` is for everybody, pure spectators included. Only available to Admin.

#### Description
	boolean CallVoteEx(string, double, int, int)

***

### [InternalCallVote](_#InternalCallVote)
Used internally by game.

#### Description
	boolean InternalCallVote()

***

### [CancelVote](_#CancelVote)
Cancel the current vote. Only available to Admin.

#### Description
	boolean CancelVote()

***

### [GetCurrentCallVote](_#GetCurrentCallVote)
Returns the vote currently in progress. The returned structure is { CallerLogin, CmdName, CmdParam }.

#### Description
	struct GetCurrentCallVote()

***

### [SetCallVoteTimeOut](_#SetCallVoteTimeOut)
Set a new timeout for waiting for votes. A zero value disables callvote. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetCallVoteTimeOut(int)

***

### [GetCallVoteTimeOut](_#GetCallVoteTimeOut)
Get the current and next timeout for waiting for votes. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetCallVoteTimeOut()

***

### [SetCallVoteRatio](_#SetCallVoteRatio)
Set a new default ratio for passing a vote. Must lie between 0 and 1. Only available to Admin.

#### Description
	boolean SetCallVoteRatio(double)

***

### [GetCallVoteRatio](_#GetCallVoteRatio)
Get the current default ratio for passing a vote. This value lies between 0 and 1.

#### Description
	double GetCallVoteRatio()

***

### [SetCallVoteRatios](_#SetCallVoteRatios)
Set the ratios list for passing specific votes. The parameter is an array of structs {string `Command`, double `Ratio`}, ratio is in [0,1] or -1 for vote disabled. Only available to Admin.

#### Description
	boolean SetCallVoteRatios(array)

***

### [GetCallVoteRatios](_#GetCallVoteRatios)
Get the current ratios for passing votes.

#### Description
	array GetCallVoteRatios()

***

### [SetCallVoteRatiosEx](_#SetCallVoteRatiosEx)
Set the ratios list for passing specific votes, extended version with parameters matching. The parameters, a boolean `ReplaceAll` (or else, only modify specified ratios, leaving the previous ones unmodified) and an array of structs {string `Command`, string `Param`, double `Ratio`}, ratio is in [0,1] or -1 for vote disabled. Param is matched against the vote parameters to make more specific ratios, leave empty to match all votes for the command. Only available to Admin.

#### Description
	boolean SetCallVoteRatiosEx(boolean, array)

***

### [GetCallVoteRatiosEx](_#GetCallVoteRatiosEx)
Get the current ratios for passing votes, extended version with parameters matching.

#### Description
	array GetCallVoteRatiosEx()

***

### [ChatSendServerMessage](_#ChatSendServerMessage)
Send a text message to all clients without the server login. Only available to Admin.

#### Description
	boolean ChatSendServerMessage(string)

***

### [ChatSendServerMessageToLanguage](_#ChatSendServerMessageToLanguage)
Send a localised text message to all clients without the server login, or optionally to a Login (which can be a single login or a list of comma-separated logins). The parameter is an array of structures {`Lang`=`xx`, `Text`=`...`}. If no matching language is found, the last text in the array is used. Only available to Admin.

#### Description
	boolean ChatSendServerMessageToLanguage(array, string)

***

### [ChatSendServerMessageToId](_#ChatSendServerMessageToId)
Send a text message without the server login to the client with the specified PlayerId. Only available to Admin.

#### Description
	boolean ChatSendServerMessageToId(string, int)

***

### [ChatSendServerMessageToLogin](_#ChatSendServerMessageToLogin)
Send a text message without the server login to the client with the specified login. Login can be a single login or a list of comma-separated logins. Only available to Admin.

#### Description
	boolean ChatSendServerMessageToLogin(string, string)

***

### [ChatSend](_#ChatSend)
Send a text message to all clients. Only available to Admin.

#### Description
	boolean ChatSend(string)

***

### [ChatSendToLanguage](_#ChatSendToLanguage)
Send a localised text message to all clients, or optionally to a Login (which can be a single login or a list of comma-separated logins). The parameter is an array of structures {`Lang`=`xx`, `Text`=`...`}. If no matching language is found, the last text in the array is used. Only available to Admin.

#### Description
	boolean ChatSendToLanguage(array, string)

***

### [ChatSendToLogin](_#ChatSendToLogin)
Send a text message to the client with the specified login. Login can be a single login or a list of comma-separated logins. Only available to Admin.

#### Description
	boolean ChatSendToLogin(string, string)

***

### [ChatSendToId](_#ChatSendToId)
Send a text message to the client with the specified PlayerId. Only available to Admin.

#### Description
	boolean ChatSendToId(string, int)

***

### [GetChatLines](_#GetChatLines)
Returns the last chat lines. Maximum of 40 lines. Only available to Admin.

#### Description
	array GetChatLines()

***

### [ChatEnableManualRouting](_#ChatEnableManualRouting)
The chat messages are no longer dispatched to the players, they only go to the rpc callback and the controller has to manually forward them. The second (optional) parameter allows all messages from the server to be automatically forwarded. Only available to Admin.

#### Description
	boolean ChatEnableManualRouting(boolean, boolean)

***

### [ChatForwardToLogin](_#ChatForwardToLogin)
(Text, SenderLogin, DestLogin) Send a text message to the specified DestLogin (or everybody if empty) on behalf of SenderLogin. DestLogin can be a single login or a list of comma-separated logins. Only available if manual routing is enabled. Only available to Admin.

#### Description
	boolean ChatForwardToLogin(string, string, string)

***

### [SendNotice](_#SendNotice)
Display a notice on all clients. The parameters are the text message to display, and the login of the avatar to display next to it (or an empty string for no avatar), and an optional `variant` in [0 = normal, 1 = Sad, 2 = Happy]. Only available to Admin.

#### Description
	boolean SendNotice(string, string, int)

***

### [SendNoticeToId](_#SendNoticeToId)
Display a notice on the client with the specified UId. The parameters are the Uid of the client to whom the notice is sent, the text message to display, and the UId of the avatar to display next to it (or `255` for no avatar), and an optional `variant` in [0 = normal, 1 = Sad, 2 = Happy]. Only available to Admin.

#### Description
	boolean SendNoticeToId(int, string, int, int)

***

### [SendNoticeToLogin](_#SendNoticeToLogin)
Display a notice on the client with the specified login. The parameters are the login of the client to whom the notice is sent, the text message to display, and the login of the avatar to display next to it (or an empty string for no avatar), and an optional `variant` in [0 = normal, 1 = Sad, 2 = Happy]. Login can be a single login or a list of comma-separated logins.  Only available to Admin.

#### Description
	boolean SendNoticeToLogin(string, string, string, int)

***

### [SendDisplayManialinkPage](_#SendDisplayManialinkPage)
Display a manialink page on all clients. The parameters are the xml description of the page to display, a timeout to autohide it (0 = permanent), and a boolean to indicate whether the page must be hidden as soon as the user clicks on a page option. Only available to Admin.

#### Description
	boolean SendDisplayManialinkPage(string, int, boolean)

***

### [SendDisplayManialinkPageToId](_#SendDisplayManialinkPageToId)
Display a manialink page on the client with the specified UId. The first parameter is the UId of the player, the other are identical to `SendDisplayManialinkPage`. Only available to Admin.

#### Description
	boolean SendDisplayManialinkPageToId(int, string, int, boolean)

***

### [SendDisplayManialinkPageToLogin](_#SendDisplayManialinkPageToLogin)
Display a manialink page on the client with the specified login. The first parameter is the login of the player, the other are identical to `SendDisplayManialinkPage`. Login can be a single login or a list of comma-separated logins. Only available to Admin.

#### Description
	boolean SendDisplayManialinkPageToLogin(string, string, int, boolean)

***

### [SendHideManialinkPage](_#SendHideManialinkPage)
Hide the displayed manialink page on all clients. Only available to Admin.

#### Description
	boolean SendHideManialinkPage()

***

### [SendHideManialinkPageToId](_#SendHideManialinkPageToId)
Hide the displayed manialink page on the client with the specified UId. Only available to Admin.

#### Description
	boolean SendHideManialinkPageToId(int)

***

### [SendHideManialinkPageToLogin](_#SendHideManialinkPageToLogin)
Hide the displayed manialink page on the client with the specified login. Login can be a single login or a list of comma-separated logins. Only available to Admin.

#### Description
	boolean SendHideManialinkPageToLogin(string)

***

### [GetManialinkPageAnswers](_#GetManialinkPageAnswers)
Returns the latest results from the current manialink page, as an array of structs {string `Login`, int `PlayerId`, int `Result`} Result==0 -> no answer, Result>0.... -> answer from the player.

#### Description
	array GetManialinkPageAnswers()

***

### [SendOpenLinkToId](_#SendOpenLinkToId)
Opens a link in the client with the specified UId. The parameters are the Uid of the client to whom the link to open is sent, the link url, and the `LinkType` (0 in the external browser, 1 in the internal manialink browser). Only available to Admin.

#### Description
	boolean SendOpenLinkToId(int, string, int)

***

### [SendOpenLinkToLogin](_#SendOpenLinkToLogin)
Opens a link in the client with the specified login. The parameters are the login of the client to whom the link to open is sent, the link url, and the `LinkType` (0 in the external browser, 1 in the internal manialink browser). Login can be a single login or a list of comma-separated logins.  Only available to Admin.

#### Description
	boolean SendOpenLinkToLogin(string, string, int)

***

### [Kick](_#Kick)
Kick the player with the specified login, with an optional message. Only available to Admin.

#### Description
	boolean Kick(string, string)

***

### [KickId](_#KickId)
Kick the player with the specified PlayerId, with an optional message. Only available to Admin.

#### Description
	boolean KickId(int, string)

***

### [Ban](_#Ban)
Ban the player with the specified login, with an optional message. Only available to Admin.

#### Description
	boolean Ban(string, string)

***

### [BanAndBlackList](_#BanAndBlackList)
Ban the player with the specified login, with a message. Add it to the black list, and optionally save the new list. Only available to Admin.

#### Description
	boolean BanAndBlackList(string, string, boolean)

***

### [BanId](_#BanId)
Ban the player with the specified PlayerId, with an optional message. Only available to Admin.

#### Description
	boolean BanId(int, string)

***

### [UnBan](_#UnBan)
Unban the player with the specified login. Only available to Admin.

#### Description
	boolean UnBan(string)

***

### [CleanBanList](_#CleanBanList)
Clean the ban list of the server. Only available to Admin.

#### Description
	boolean CleanBanList()

***

### [GetBanList](_#GetBanList)
Returns the list of banned players. This method takes two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the list. The list is an array of structures. Each structure contains the following fields : `Login`, `ClientName` and `IPAddress`.

#### Description
	array GetBanList(int, int)

***

### [BlackList](_#BlackList)
Blacklist the player with the specified login. Only available to SuperAdmin.

#### Description
	boolean BlackList(string)

***

### [BlackListId](_#BlackListId)
Blacklist the player with the specified PlayerId. Only available to SuperAdmin.

#### Description
	boolean BlackListId(int)

***

### [UnBlackList](_#UnBlackList)
UnBlackList the player with the specified login. Only available to SuperAdmin.

#### Description
	boolean UnBlackList(string)

***

### [CleanBlackList](_#CleanBlackList)
Clean the blacklist of the server. Only available to SuperAdmin.

#### Description
	boolean CleanBlackList()

***

### [GetBlackList](_#GetBlackList)
Returns the list of blacklisted players. This method takes two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the list. The list is an array of structures. Each structure contains the following fields : `Login`.

#### Description
	array GetBlackList(int, int)

***

### [LoadBlackList](_#LoadBlackList)
Load the black list file with the specified file name. Only available to Admin.

#### Description
	boolean LoadBlackList(string)

***

### [SaveBlackList](_#SaveBlackList)
Save the black list in the file with specified file name. Only available to Admin.

#### Description
	boolean SaveBlackList(string)

***

### [AddGuest](_#AddGuest)
Add the player with the specified login on the guest list. Only available to Admin.

#### Description
	boolean AddGuest(string)

***

### [AddGuestId](_#AddGuestId)
Add the player with the specified PlayerId on the guest list. Only available to Admin.

#### Description
	boolean AddGuestId(int)

***

### [RemoveGuest](_#RemoveGuest)
Remove the player with the specified login from the guest list. Only available to Admin.

#### Description
	boolean RemoveGuest(string)

***

### [RemoveGuestId](_#RemoveGuestId)
Remove the player with the specified PlayerId from the guest list. Only available to Admin.

#### Description
	boolean RemoveGuestId(int)

***

### [CleanGuestList](_#CleanGuestList)
Clean the guest list of the server. Only available to Admin.

#### Description
	boolean CleanGuestList()

***

### [GetGuestList](_#GetGuestList)
Returns the list of players on the guest list. This method takes two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the list. The list is an array of structures. Each structure contains the following fields : `Login`.

#### Description
	array GetGuestList(int, int)

***

### [LoadGuestList](_#LoadGuestList)
Load the guest list file with the specified file name. Only available to Admin.

#### Description
	boolean LoadGuestList(string)

***

### [SaveGuestList](_#SaveGuestList)
Save the guest list in the file with specified file name. Only available to Admin.

#### Description
	boolean SaveGuestList(string)

***

### [SetBuddyNotification](_#SetBuddyNotification)
Sets whether buddy notifications should be sent in the chat. `login` is the login of the player, or an empty string for global setting, and `enabled` is the value. Only available to Admin.

#### Description
	boolean SetBuddyNotification(string, boolean)

***

### [GetBuddyNotification](_#GetBuddyNotification)
Gets whether buddy notifications are enabled for `login`, or an empty string to get the global setting.

#### Description
	boolean GetBuddyNotification(string)

***

### [WriteFile](_#WriteFile)
Write the data to the specified file. The filename is relative to the Maps path. Only available to Admin.

#### Description
	boolean WriteFile(string, base64)

***

### [TunnelSendDataToId](_#TunnelSendDataToId)
Send the data to the specified player. Only available to Admin.

#### Description
	boolean TunnelSendDataToId(int, base64)

***

### [TunnelSendDataToLogin](_#TunnelSendDataToLogin)
Send the data to the specified player. Login can be a single login or a list of comma-separated logins. Only available to Admin.

#### Description
	boolean TunnelSendDataToLogin(string, base64)

***

### [Echo](_#Echo)
Just log the parameters and invoke a callback. Can be used to talk to other xmlrpc clients connected, or to make custom votes. If used in a callvote, the first parameter will be used as the vote message on the clients. Only available to Admin.

#### Description
	boolean Echo(string, string)

***

### [Ignore](_#Ignore)
Ignore the player with the specified login. Only available to Admin.

#### Description
	boolean Ignore(string)

***

### [IgnoreId](_#IgnoreId)
Ignore the player with the specified PlayerId. Only available to Admin.

#### Description
	boolean IgnoreId(int)

***

### [UnIgnore](_#UnIgnore)
Unignore the player with the specified login. Only available to Admin.

#### Description
	boolean UnIgnore(string)

***

### [UnIgnoreId](_#UnIgnoreId)
Unignore the player with the specified PlayerId. Only available to Admin.

#### Description
	boolean UnIgnoreId(int)

***

### [CleanIgnoreList](_#CleanIgnoreList)
Clean the ignore list of the server. Only available to Admin.

#### Description
	boolean CleanIgnoreList()

***

### [GetIgnoreList](_#GetIgnoreList)
Returns the list of ignored players. This method takes two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the list. The list is an array of structures. Each structure contains the following fields : `Login`.

#### Description
	array GetIgnoreList(int, int)

***

### [Pay](_#Pay)
Pay planets from the server account to a player, returns the BillId. This method takes three parameters: `Login` of the payee, `Cost` in planets to pay and a `Label` to send with the payment. The creation of the transaction itself may cost planets, so you need to have planets on the server account. Only available to Admin.

#### Description
	int Pay(string, int, string)

***

### [SendBill](_#SendBill)
Create a bill, send it to a player, and return the BillId. This method takes four parameters: `LoginFrom` of the payer, `Cost` in planets the player has to pay, `Label` of the transaction and an optional `LoginTo` of the payee (if empty string, then the server account is used). The creation of the transaction itself may cost planets, so you need to have planets on the server account. Only available to Admin.

#### Description
	int SendBill(string, int, string, string)

***

### [GetBillState](_#GetBillState)
Returns the current state of a bill. This method takes one parameter, the `BillId`. Returns a struct containing `State`, `StateName` and `TransactionId`. Possible enum values are: `CreatingTransaction`, `Issued`, `ValidatingPayement`, `Payed`, `Refused`, `Error`.

#### Description
	struct GetBillState(int)

***

### [GetServerPlanets](_#GetServerPlanets)
Returns the current number of planets on the server account.

#### Description
	int GetServerPlanets()

***

### [GetSystemInfo](_#GetSystemInfo)
Get some system infos, including connection rates (in kbps).

#### Description
	struct GetSystemInfo()

***

### [SetConnectionRates](_#SetConnectionRates)
Set the download and upload rates (in kbps).

#### Description
	boolean SetConnectionRates(int, int)

***

### [GetServerTags](_#GetServerTags)
Returns the list of tags and associated values set on this server. Only available to Admin.

#### Description
	array GetServerTags()

***

### [SetServerTag](_#SetServerTag)
Set a tag and its value on the server. This method takes two parameters. The first parameter specifies the name of the tag, and the second one its value. The list is an array of structures {string `Name`, string `Value`}. Only available to Admin.

#### Description
	boolean SetServerTag(string, string)

***

### [UnsetServerTag](_#UnsetServerTag)
Unset the tag with the specified name on the server. Only available to Admin.

#### Description
	boolean UnsetServerTag(string)

***

### [ResetServerTags](_#ResetServerTags)
Reset all tags on the server. Only available to Admin.

#### Description
	boolean ResetServerTags()

***

### [SetServerName](_#SetServerName)
Set a new server name in utf8 format. Only available to Admin.

#### Description
	boolean SetServerName(string)

***

### [GetServerName](_#GetServerName)
Get the server name in utf8 format.

#### Description
	string GetServerName()

***

### [SetServerComment](_#SetServerComment)
Set a new server comment in utf8 format. Only available to Admin.

#### Description
	boolean SetServerComment(string)

***

### [GetServerComment](_#GetServerComment)
Get the server comment in utf8 format.

#### Description
	string GetServerComment()

***

### [SetHideServer](_#SetHideServer)
Set whether the server should be hidden from the public server list (0 = visible, 1 = always hidden, 2 = hidden from nations). Only available to Admin.

#### Description
	boolean SetHideServer(int)

***

### [GetHideServer](_#GetHideServer)
Get whether the server wants to be hidden from the public server list.

#### Description
	int GetHideServer()

***

### [IsRelayServer](_#IsRelayServer)
Returns true if this is a relay server.

#### Description
	boolean IsRelayServer()

***

### [SetServerPassword](_#SetServerPassword)
Set a new password for the server. Only available to Admin.

#### Description
	boolean SetServerPassword(string)

***

### [GetServerPassword](_#GetServerPassword)
Get the server password if called as Admin or Super Admin, else returns if a password is needed or not.

#### Description
	string GetServerPassword()

***

### [SetServerPasswordForSpectator](_#SetServerPasswordForSpectator)
Set a new password for the spectator mode. Only available to Admin.

#### Description
	boolean SetServerPasswordForSpectator(string)

***

### [GetServerPasswordForSpectator](_#GetServerPasswordForSpectator)
Get the password for spectator mode if called as Admin or Super Admin, else returns if a password is needed or not.

#### Description
	string GetServerPasswordForSpectator()

***

### [SetMaxPlayers](_#SetMaxPlayers)
Set a new maximum number of players. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetMaxPlayers(int)

***

### [GetMaxPlayers](_#GetMaxPlayers)
Get the current and next maximum number of players allowed on server. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetMaxPlayers()

***

### [SetMaxSpectators](_#SetMaxSpectators)
Set a new maximum number of Spectators. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetMaxSpectators(int)

***

### [GetMaxSpectators](_#GetMaxSpectators)
Get the current and next maximum number of Spectators allowed on server. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetMaxSpectators()

***

### [SetLobbyInfo](_#SetLobbyInfo)
Declare if the server is a lobby, the number and maximum number of players currently managed by it, and the average level of the players. Only available to Admin.

#### Description
	boolean SetLobbyInfo(boolean, int, int, double)

***

### [GetLobbyInfo](_#GetLobbyInfo)
Get whether the server if a lobby, the number and maximum number of players currently managed by it. The struct returned contains 4 fields `IsLobby`, `LobbyPlayers`, `LobbyMaxPlayers`, and `LobbyPlayersLevel`.

#### Description
	struct GetLobbyInfo()

***

### [CustomizeQuitDialog](_#CustomizeQuitDialog)
Customize the clients `leave server` dialog box. Parameters are: `ManialinkPage`, `SendToServer` url `#qjoin=login@title`, `ProposeAddToFavorites` and `DelayQuitButton` (in milliseconds). Only available to Admin.

#### Description
	boolean CustomizeQuitDialog(string, string, boolean, int)

***

### [SendToServerAfterMatchEnd](_#SendToServerAfterMatchEnd)
Prior to loading next map, execute `SendToServer` url `#qjoin=login@title`. Only available to Admin.

#### Description
	boolean SendToServerAfterMatchEnd(string)

***

### [KeepPlayerSlots](_#KeepPlayerSlots)
Set whether, when a player is switching to spectator, the server should still consider him a player and keep his player slot, or not. Only available to Admin.

#### Description
	boolean KeepPlayerSlots(boolean)

***

### [IsKeepingPlayerSlots](_#IsKeepingPlayerSlots)
Get whether the server keeps player slots when switching to spectator.

#### Description
	boolean IsKeepingPlayerSlots()

***

### [EnableP2PUpload](_#EnableP2PUpload)
Enable or disable peer-to-peer upload from server. Only available to Admin.

#### Description
	boolean EnableP2PUpload(boolean)

***

### [IsP2PUpload](_#IsP2PUpload)
Returns if the peer-to-peer upload from server is enabled.

#### Description
	boolean IsP2PUpload()

***

### [EnableP2PDownload](_#EnableP2PDownload)
Enable or disable peer-to-peer download for server. Only available to Admin.

#### Description
	boolean EnableP2PDownload(boolean)

***

### [IsP2PDownload](_#IsP2PDownload)
Returns if the peer-to-peer download for server is enabled.

#### Description
	boolean IsP2PDownload()

***

### [AllowMapDownload](_#AllowMapDownload)
Allow clients to download maps from the server. Only available to Admin.

#### Description
	boolean AllowMapDownload(boolean)

***

### [IsMapDownloadAllowed](_#IsMapDownloadAllowed)
Returns if clients can download maps from the server.

#### Description
	boolean IsMapDownloadAllowed()

***

### [GameDataDirectory](_#GameDataDirectory)
Returns the path of the game datas directory. Only available to Admin.

#### Description
	string GameDataDirectory()

***

### [GetMapsDirectory](_#GetMapsDirectory)
Returns the path of the maps directory. Only available to Admin.

#### Description
	string GetMapsDirectory()

***

### [GetSkinsDirectory](_#GetSkinsDirectory)
Returns the path of the skins directory. Only available to Admin.

#### Description
	string GetSkinsDirectory()

***

### [SetTeamInfo](_#SetTeamInfo)
Set Team names and colors (deprecated). Only available to Admin.

#### Description
	boolean SetTeamInfo(string, double, string, string, double, string, string, double, string)

***

### [GetTeamInfo](_#GetTeamInfo)
Return Team info for a given clan (0 = no clan, 1, 2). The structure contains: `Name`, `ZonePath`, `City`, `EmblemUrl`, `HuePrimary`, `HueSecondary`, `RGB`, `ClubLinkUrl`. Only available to Admin.

#### Description
	struct GetTeamInfo(int)

***

### [SetForcedClubLinks](_#SetForcedClubLinks)
Set the clublinks to use for the two clans. Only available to Admin.

#### Description
	boolean SetForcedClubLinks(string, string)

***

### [GetForcedClubLinks](_#GetForcedClubLinks)
Get the forced clublinks.

#### Description
	struct GetForcedClubLinks()

***

### [ConnectFakePlayer](_#ConnectFakePlayer)
(debug tool) Connect a fake player to the server and returns the login. Only available to Admin.

#### Description
	string ConnectFakePlayer()

***

### [DisconnectFakePlayer](_#DisconnectFakePlayer)
(debug tool) Disconnect a fake player, or all the fake players if login is `*`. Only available to Admin.

#### Description
	boolean DisconnectFakePlayer(string)

***

### [GetDemoTokenInfosForPlayer](_#GetDemoTokenInfosForPlayer)
Returns the token infos for a player. The returned structure is { TokenCost, CanPayToken }.

#### Description
	struct GetDemoTokenInfosForPlayer(string)

***

### [DisableHorns](_#DisableHorns)
Disable player horns. Only available to Admin.

#### Description
	boolean DisableHorns(boolean)

***

### [AreHornsDisabled](_#AreHornsDisabled)
Returns whether the horns are disabled.

#### Description
	boolean AreHornsDisabled()

***

### [DisableServiceAnnounces](_#DisableServiceAnnounces)
Disable the automatic mesages when a player connects/disconnects from the server. Only available to Admin.

#### Description
	boolean DisableServiceAnnounces(boolean)

***

### [AreServiceAnnouncesDisabled](_#AreServiceAnnouncesDisabled)
Returns whether the automatic mesages are disabled.

#### Description
	boolean AreServiceAnnouncesDisabled()

***

### [AutoSaveReplays](_#AutoSaveReplays)
Enable the autosaving of all replays (vizualisable replays with all players, but not validable) on the server. Only available to SuperAdmin.

#### Description
	boolean AutoSaveReplays(boolean)

***

### [AutoSaveValidationReplays](_#AutoSaveValidationReplays)
Enable the autosaving on the server of validation replays, every time a player makes a new time. Only available to SuperAdmin.

#### Description
	boolean AutoSaveValidationReplays(boolean)

***

### [IsAutoSaveReplaysEnabled](_#IsAutoSaveReplaysEnabled)
Returns if autosaving of all replays is enabled on the server.

#### Description
	boolean IsAutoSaveReplaysEnabled()

***

### [IsAutoSaveValidationReplaysEnabled](_#IsAutoSaveValidationReplaysEnabled)
Returns if autosaving of validation replays is enabled on the server.

#### Description
	boolean IsAutoSaveValidationReplaysEnabled()

***

### [SaveCurrentReplay](_#SaveCurrentReplay)
Saves the current replay (vizualisable replays with all players, but not validable). Pass a filename, or an empty string for an automatic filename. Only available to Admin.

#### Description
	boolean SaveCurrentReplay(string)

***

### [SaveBestGhostsReplay](_#SaveBestGhostsReplay)
Saves a replay with the ghost of all the players` best race. First parameter is the login of the player (or an empty string for all players), Second parameter is the filename, or an empty string for an automatic filename. Only available to Admin.

#### Description
	boolean SaveBestGhostsReplay(string, string)

***

### [GetValidationReplay](_#GetValidationReplay)
Returns a replay containing the data needed to validate the current best time of the player. The parameter is the login of the player.

#### Description
	base64 GetValidationReplay(string)

***

### [SetLadderMode](_#SetLadderMode)
Set a new ladder mode between ladder disabled (0) and forced (1). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetLadderMode(int)

***

### [GetLadderMode](_#GetLadderMode)
Get the current and next ladder mode on server. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetLadderMode()

***

### [GetLadderServerLimits](_#GetLadderServerLimits)
Get the ladder points limit for the players allowed on this server. The struct returned contains two fields `LadderServerLimitMin` and `LadderServerLimitMax`.

#### Description
	struct GetLadderServerLimits()

***

### [SetVehicleNetQuality](_#SetVehicleNetQuality)
Set the network vehicle quality to Fast (0) or High (1). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetVehicleNetQuality(int)

***

### [GetVehicleNetQuality](_#GetVehicleNetQuality)
Get the current and next network vehicle quality on server. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetVehicleNetQuality()

***

### [SetServerOptions](_#SetServerOptions)
Set new server options using the struct passed as parameters. This struct must contain the following fields : `Name`, `Comment`, `Password`, `PasswordForSpectator`, `NextMaxPlayers`, `NextMaxSpectators`, `IsP2PUpload`, `IsP2PDownload`, `NextLadderMode`, `NextVehicleNetQuality`, `NextCallVoteTimeOut`, `CallVoteRatio`, `AllowMapDownload`, `AutoSaveReplays`, and optionally for forever: `RefereePassword`, `RefereeMode`, `AutoSaveValidationReplays`, `HideServer`, `UseChangingValidationSeed`, `ClientInputsMaxLatency`, `DisableHorns`, `DisableServiceAnnounces`, `KeepPlayerSlots`, `ServerPlugin`. Only available to Admin. A change of NextMaxPlayers, NextMaxSpectators, NextLadderMode, NextVehicleNetQuality, NextCallVoteTimeOut or UseChangingValidationSeed requires a map restart to be taken into account.

#### Description
	boolean SetServerOptions(struct)

***

### [GetServerOptions](_#GetServerOptions)
Returns a struct containing the server options: `Name`, `Comment`, `Password`, `PasswordForSpectator`, `CurrentMaxPlayers`, `NextMaxPlayers`, `CurrentMaxSpectators`, `NextMaxSpectators`, `KeepPlayerSlots`, `IsP2PUpload`, `IsP2PDownload`, `CurrentLadderMode`, `NextLadderMode`, `CurrentVehicleNetQuality`, `NextVehicleNetQuality`, `CurrentCallVoteTimeOut`, `NextCallVoteTimeOut`, `CallVoteRatio`, `AllowMapDownload`, `AutoSaveReplays`, `RefereePassword`, `RefereeMode`, `AutoSaveValidationReplays`, `HideServer`, `CurrentUseChangingValidationSeed`, `NextUseChangingValidationSeed`, `ClientInputsMaxLatency`, `DisableHorns`, `DisableServiceAnnounces`.

#### Description
	struct GetServerOptions()

***

### [SetForcedTeams](_#SetForcedTeams)
Set whether the players can choose their side or if the teams are forced by the server (using ForcePlayerTeam()). Only available to Admin.

#### Description
	boolean SetForcedTeams(boolean)

***

### [GetForcedTeams](_#GetForcedTeams)
Returns whether the players can choose their side or if the teams are forced by the server.

#### Description
	boolean GetForcedTeams()

***

### [SetForcedMods](_#SetForcedMods)
Set the mods to apply on the clients. Parameters: `Override`, if true even the maps with a mod will be overridden by the server setting; and `Mods`, an array of structures [{`EnvName`, `Url`}, ...]. Requires a map restart to be taken into account. Only available to Admin.

#### Description
	boolean SetForcedMods(boolean, array)

***

### [GetForcedMods](_#GetForcedMods)
Get the mods settings.

#### Description
	struct GetForcedMods()

***

### [SetForcedMusic](_#SetForcedMusic)
Set the music to play on the clients. Parameters: `Override`, if true even the maps with a custom music will be overridden by the server setting, and a `UrlOrFileName` for the music. Requires a map restart to be taken into account. Only available to Admin.

#### Description
	boolean SetForcedMusic(boolean, string)

***

### [GetForcedMusic](_#GetForcedMusic)
Get the music setting.

#### Description
	struct GetForcedMusic()

***

### [SetForcedSkins](_#SetForcedSkins)
Defines a list of remappings for player skins. It expects a list of structs `Orig`, `Name`, `Checksum`, `Url`.  Orig is the name of the skin to remap, or `*` for any other. Name, Checksum, Url define the skin to use. (They are optional, you may set value an empty string for any of those. All 3 null means same as Orig). Will only affect players connecting after the value is set. Only available to Admin.

#### Description
	boolean SetForcedSkins(array)

***

### [GetForcedSkins](_#GetForcedSkins)
Get the current forced skins.

#### Description
	array GetForcedSkins()

***

### [GetLastConnectionErrorMessage](_#GetLastConnectionErrorMessage)
Returns the last error message for an internet connection. Only available to Admin.

#### Description
	string GetLastConnectionErrorMessage()

***

### [SetRefereePassword](_#SetRefereePassword)
Set a new password for the referee mode. Only available to Admin.

#### Description
	boolean SetRefereePassword(string)

***

### [GetRefereePassword](_#GetRefereePassword)
Get the password for referee mode if called as Admin or Super Admin, else returns if a password is needed or not.

#### Description
	string GetRefereePassword()

***

### [SetRefereeMode](_#SetRefereeMode)
Set the referee validation mode. 0 = validate the top3 players, 1 = validate all players. Only available to Admin.

#### Description
	boolean SetRefereeMode(int)

***

### [GetRefereeMode](_#GetRefereeMode)
Get the referee validation mode.

#### Description
	int GetRefereeMode()

***

### [SetServerPlugin](_#SetServerPlugin)
Set a the ServerPlugin settings. Parameters: `ForceReload` to reload from disk, optionnally: `Name` the filename relative to Scripts/ directory, `Settings` the script #Settings to apply. Only available to Admin.

#### Description
	boolean SetServerPlugin(boolean, string, struct)

***

### [GetServerPlugin](_#GetServerPlugin)
Get the ServerPlugin current settings.

#### Description
	struct GetServerPlugin()

***

### [SetUseChangingValidationSeed](_#SetUseChangingValidationSeed)
Set whether the game should use a variable validation seed or not. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetUseChangingValidationSeed(boolean)

***

### [GetUseChangingValidationSeed](_#GetUseChangingValidationSeed)
Get the current and next value of UseChangingValidationSeed. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetUseChangingValidationSeed()

***

### [SetClientInputsMaxLatency](_#SetClientInputsMaxLatency)
Set the maximum time the server must wait for inputs from the clients before dropping data, or `0` for auto-adaptation. Only used by ShootMania. Only available to Admin.

#### Description
	boolean SetClientInputsMaxLatency(int)

***

### [GetClientInputsMaxLatency](_#GetClientInputsMaxLatency)
Get the current ClientInputsMaxLatency. Only used by ShootMania.

#### Description
	int GetClientInputsMaxLatency()

***

### [SetWarmUp](_#SetWarmUp)
Sets whether the server is in warm-up phase or not. Only available to Admin.

#### Description
	boolean SetWarmUp(boolean)

***

### [GetWarmUp](_#GetWarmUp)
Returns whether the server is in warm-up phase.

#### Description
	boolean GetWarmUp()

***

### [GetModeScriptText](_#GetModeScriptText)
Get the current mode script.

#### Description
	string GetModeScriptText()

***

### [SetModeScriptText](_#SetModeScriptText)
Set the mode script and restart. Only available to Admin.

#### Description
	boolean SetModeScriptText(string)

***

### [GetModeScriptInfo](_#GetModeScriptInfo)
Returns the description of the current mode script, as a structure containing: Name, CompatibleTypes, Description, Version and the settings available.

#### Description
	struct GetModeScriptInfo()

***

### [GetModeScriptSettings](_#GetModeScriptSettings)
Returns the current settings of the mode script.

#### Description
	struct GetModeScriptSettings()

***

### [SetModeScriptSettings](_#SetModeScriptSettings)
Change the settings of the mode script. Only available to Admin.

#### Description
	boolean SetModeScriptSettings(struct)

***

### [SendModeScriptCommands](_#SendModeScriptCommands)
Send commands to the mode script. Only available to Admin.

#### Description
	boolean SendModeScriptCommands(struct)

***

### [SetModeScriptSettingsAndCommands](_#SetModeScriptSettingsAndCommands)
Change the settings and send commands to the mode script. Only available to Admin.

#### Description
	boolean SetModeScriptSettingsAndCommands(struct, struct)

***

### [GetModeScriptVariables](_#GetModeScriptVariables)
Returns the current xml-rpc variables of the mode script.

#### Description
	struct GetModeScriptVariables()

***

### [SetModeScriptVariables](_#SetModeScriptVariables)
Set the xml-rpc variables of the mode script. Only available to Admin.

#### Description
	boolean SetModeScriptVariables(struct)

***

### [TriggerModeScriptEvent](_#TriggerModeScriptEvent)
Send an event to the mode script. Only available to Admin.

#### Description
	boolean TriggerModeScriptEvent(string, string)

***

### [TriggerModeScriptEventArray](_#TriggerModeScriptEventArray)
Send an event to the mode script. Only available to Admin.

#### Description
	boolean TriggerModeScriptEventArray(string, array)

***

### [GetScriptCloudVariables](_#GetScriptCloudVariables)
Get the script cloud variables of given object. Only available to Admin.

#### Description
	struct GetScriptCloudVariables(string, string)

***

### [SetScriptCloudVariables](_#SetScriptCloudVariables)
Set the script cloud variables of given object. Only available to Admin.

#### Description
	boolean SetScriptCloudVariables(string, string, struct)

***

### [RestartMap](_#RestartMap)
Restarts the map, with an optional boolean parameter `DontClearCupScores` (only available in cup mode). Only available to Admin.

#### Description
	boolean RestartMap()

***

### [NextMap](_#NextMap)
Switch to next map, with an optional boolean parameter `DontClearCupScores` (only available in cup mode). Only available to Admin.

#### Description
	boolean NextMap()

***

### [AutoTeamBalance](_#AutoTeamBalance)
Attempt to balance teams. Only available to Admin.

#### Description
	boolean AutoTeamBalance()

***

### [StopServer](_#StopServer)
Stop the server. Only available to SuperAdmin.

#### Description
	boolean StopServer()

***

### [ForceEndRound](_#ForceEndRound)
In Rounds or Laps mode, force the end of round without waiting for all players to giveup/finish. Only available to Admin.

#### Description
	boolean ForceEndRound()

***

### [SetGameInfos](_#SetGameInfos)
Set new game settings using the struct passed as parameters. This struct must contain the following fields : `GameMode`, `ChatTime`, `RoundsPointsLimit`, `RoundsUseNewRules`, `RoundsForcedLaps`, `TimeAttackLimit`, `TimeAttackSynchStartPeriod`, `TeamPointsLimit`, `TeamMaxPoints`, `TeamUseNewRules`, `LapsNbLaps`, `LapsTimeLimit`, `FinishTimeout`, and optionally: `AllWarmUpDuration`, `DisableRespawn`, `ForceShowAllOpponents`, `RoundsPointsLimitNewRules`, `TeamPointsLimitNewRules`, `CupPointsLimit`, `CupRoundsPerMap`, `CupNbWinners`, `CupWarmUpDuration`. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetGameInfos(struct)

***

### [GetCurrentGameInfo](_#GetCurrentGameInfo)
Returns a struct containing the current game settings, ie: `GameMode`, `ChatTime`, `NbMaps`, `RoundsPointsLimit`, `RoundsUseNewRules`, `RoundsForcedLaps`, `TimeAttackLimit`, `TimeAttackSynchStartPeriod`, `TeamPointsLimit`, `TeamMaxPoints`, `TeamUseNewRules`, `LapsNbLaps`, `LapsTimeLimit`, `FinishTimeout`, and additionally for version 1: `AllWarmUpDuration`, `DisableRespawn`, `ForceShowAllOpponents`, `RoundsPointsLimitNewRules`, `TeamPointsLimitNewRules`, `CupPointsLimit`, `CupRoundsPerMap`, `CupNbWinners`, `CupWarmUpDuration`.

#### Description
	struct GetCurrentGameInfo()

***

### [GetNextGameInfo](_#GetNextGameInfo)
Returns a struct containing the game settings for the next map, ie: `GameMode`, `ChatTime`, `NbMaps`, `RoundsPointsLimit`, `RoundsUseNewRules`, `RoundsForcedLaps`, `TimeAttackLimit`, `TimeAttackSynchStartPeriod`, `TeamPointsLimit`, `TeamMaxPoints`, `TeamUseNewRules`, `LapsNbLaps`, `LapsTimeLimit`, `FinishTimeout`, and additionally for version 1: `AllWarmUpDuration`, `DisableRespawn`, `ForceShowAllOpponents`, `RoundsPointsLimitNewRules`, `TeamPointsLimitNewRules`, `CupPointsLimit`, `CupRoundsPerMap`, `CupNbWinners`, `CupWarmUpDuration`.

#### Description
	struct GetNextGameInfo()

***

### [GetGameInfos](_#GetGameInfos)
Returns a struct containing two other structures, the first containing the current game settings and the second the game settings for next map. The first structure is named `CurrentGameInfos` and the second `NextGameInfos`.

#### Description
	struct GetGameInfos()

***

### [SetGameMode](_#SetGameMode)
Set a new game mode between Script (0), Rounds (1), TimeAttack (2), Team (3), Laps (4), Cup (5) and Stunts (6). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetGameMode(int)

***

### [GetGameMode](_#GetGameMode)
Get the current game mode.

#### Description
	int GetGameMode()

***

### [SetChatTime](_#SetChatTime)
Set a new chat time value in milliseconds (actually `chat time` is the duration of the end race podium, 0 means no podium displayed.). Only available to Admin.

#### Description
	boolean SetChatTime(int)

***

### [GetChatTime](_#GetChatTime)
Get the current and next chat time. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetChatTime()

***

### [SetFinishTimeout](_#SetFinishTimeout)
Set a new finish timeout (for rounds/laps mode) value in milliseconds. 0 means default. 1 means adaptative to the duration of the map. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetFinishTimeout(int)

***

### [GetFinishTimeout](_#GetFinishTimeout)
Get the current and next FinishTimeout. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetFinishTimeout()

***

### [SetAllWarmUpDuration](_#SetAllWarmUpDuration)
Set whether to enable the automatic warm-up phase in all modes. 0 = no, otherwise it is the duration of the phase, expressed in number of rounds (in rounds/team mode), or in number of times the gold medal time (other modes). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetAllWarmUpDuration(int)

***

### [GetAllWarmUpDuration](_#GetAllWarmUpDuration)
Get whether the automatic warm-up phase is enabled in all modes. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetAllWarmUpDuration()

***

### [SetDisableRespawn](_#SetDisableRespawn)
Set whether to disallow players to respawn. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetDisableRespawn(boolean)

***

### [GetDisableRespawn](_#GetDisableRespawn)
Get whether players are disallowed to respawn. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetDisableRespawn()

***

### [SetForceShowAllOpponents](_#SetForceShowAllOpponents)
Set whether to override the players preferences and always display all opponents (0=no override, 1=show all, other value=minimum number of opponents). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetForceShowAllOpponents(int)

***

### [GetForceShowAllOpponents](_#GetForceShowAllOpponents)
Get whether players are forced to show all opponents. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetForceShowAllOpponents()

***

### [SetScriptName](_#SetScriptName)
Set a new mode script name for script mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetScriptName(string)

***

### [GetScriptName](_#GetScriptName)
Get the current and next mode script name for script mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetScriptName()

***

### [SetTimeAttackLimit](_#SetTimeAttackLimit)
Set a new time limit for time attack mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetTimeAttackLimit(int)

***

### [GetTimeAttackLimit](_#GetTimeAttackLimit)
Get the current and next time limit for time attack mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetTimeAttackLimit()

***

### [SetTimeAttackSynchStartPeriod](_#SetTimeAttackSynchStartPeriod)
Set a new synchronized start period for time attack mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetTimeAttackSynchStartPeriod(int)

***

### [GetTimeAttackSynchStartPeriod](_#GetTimeAttackSynchStartPeriod)
Get the current and synchronized start period for time attack mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetTimeAttackSynchStartPeriod()

***

### [SetLapsTimeLimit](_#SetLapsTimeLimit)
Set a new time limit for laps mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetLapsTimeLimit(int)

***

### [GetLapsTimeLimit](_#GetLapsTimeLimit)
Get the current and next time limit for laps mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetLapsTimeLimit()

***

### [SetNbLaps](_#SetNbLaps)
Set a new number of laps for laps mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetNbLaps(int)

***

### [GetNbLaps](_#GetNbLaps)
Get the current and next number of laps for laps mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetNbLaps()

***

### [SetRoundForcedLaps](_#SetRoundForcedLaps)
Set a new number of laps for rounds mode (0 = default, use the number of laps from the maps, otherwise forces the number of rounds for multilaps maps). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetRoundForcedLaps(int)

***

### [GetRoundForcedLaps](_#GetRoundForcedLaps)
Get the current and next number of laps for rounds mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetRoundForcedLaps()

***

### [SetRoundPointsLimit](_#SetRoundPointsLimit)
Set a new points limit for rounds mode (value set depends on UseNewRulesRound). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetRoundPointsLimit(int)

***

### [GetRoundPointsLimit](_#GetRoundPointsLimit)
Get the current and next points limit for rounds mode (values returned depend on UseNewRulesRound). The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetRoundPointsLimit()

***

### [SetRoundCustomPoints](_#SetRoundCustomPoints)
Set the points used for the scores in rounds mode. `Points` is an array of decreasing integers for the players from the first to last. And you can add an optional boolean to relax the constraint checking on the scores. Only available to Admin.

#### Description
	boolean SetRoundCustomPoints(array, boolean)

***

### [GetRoundCustomPoints](_#GetRoundCustomPoints)
Gets the points used for the scores in rounds mode.

#### Description
	array GetRoundCustomPoints()

***

### [SetUseNewRulesRound](_#SetUseNewRulesRound)
Set if new rules are used for rounds mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetUseNewRulesRound(boolean)

***

### [GetUseNewRulesRound](_#GetUseNewRulesRound)
Get if the new rules are used for rounds mode (Current and next values). The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetUseNewRulesRound()

***

### [SetTeamPointsLimit](_#SetTeamPointsLimit)
Set a new points limit for team mode (value set depends on UseNewRulesTeam). Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetTeamPointsLimit(int)

***

### [GetTeamPointsLimit](_#GetTeamPointsLimit)
Get the current and next points limit for team mode (values returned depend on UseNewRulesTeam). The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetTeamPointsLimit()

***

### [SetMaxPointsTeam](_#SetMaxPointsTeam)
Set a new number of maximum points per round for team mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetMaxPointsTeam(int)

***

### [GetMaxPointsTeam](_#GetMaxPointsTeam)
Get the current and next number of maximum points per round for team mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetMaxPointsTeam()

***

### [SetUseNewRulesTeam](_#SetUseNewRulesTeam)
Set if new rules are used for team mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetUseNewRulesTeam(boolean)

***

### [GetUseNewRulesTeam](_#GetUseNewRulesTeam)
Get if the new rules are used for team mode (Current and next values). The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetUseNewRulesTeam()

***

### [SetCupPointsLimit](_#SetCupPointsLimit)
Set the points needed for victory in Cup mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetCupPointsLimit(int)

***

### [GetCupPointsLimit](_#GetCupPointsLimit)
Get the points needed for victory in Cup mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetCupPointsLimit()

***

### [SetCupRoundsPerMap](_#SetCupRoundsPerMap)
Sets the number of rounds before going to next map in Cup mode. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetCupRoundsPerMap(int)

***

### [GetCupRoundsPerMap](_#GetCupRoundsPerMap)
Get the number of rounds before going to next map in Cup mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetCupRoundsPerMap()

***

### [SetCupWarmUpDuration](_#SetCupWarmUpDuration)
Set whether to enable the automatic warm-up phase in Cup mode. 0 = no, otherwise it is the duration of the phase, expressed in number of rounds. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetCupWarmUpDuration(int)

***

### [GetCupWarmUpDuration](_#GetCupWarmUpDuration)
Get whether the automatic warm-up phase is enabled in Cup mode. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetCupWarmUpDuration()

***

### [SetCupNbWinners](_#SetCupNbWinners)
Set the number of winners to determine before the match is considered over. Only available to Admin. Requires a map restart to be taken into account.

#### Description
	boolean SetCupNbWinners(int)

***

### [GetCupNbWinners](_#GetCupNbWinners)
Get the number of winners to determine before the match is considered over. The struct returned contains two fields `CurrentValue` and `NextValue`.

#### Description
	struct GetCupNbWinners()

***

### [GetCurrentMapIndex](_#GetCurrentMapIndex)
Returns the current map index in the selection, or -1 if the map is no longer in the selection.

#### Description
	int GetCurrentMapIndex()

***

### [GetNextMapIndex](_#GetNextMapIndex)
Returns the map index in the selection that will be played next (unless the current one is restarted...)

#### Description
	int GetNextMapIndex()

***

### [SetNextMapIndex](_#SetNextMapIndex)
Sets the map index in the selection that will be played next (unless the current one is restarted...)

#### Description
	boolean SetNextMapIndex(int)

***

### [SetNextMapIdent](_#SetNextMapIdent)
Sets the map in the selection that will be played next (unless the current one is restarted...)

#### Description
	boolean SetNextMapIdent(string)

***

### [JumpToMapIndex](_#JumpToMapIndex)
Immediately jumps to the map designated by the index in the selection.

#### Description
	boolean JumpToMapIndex(int)

***

### [JumpToMapIdent](_#JumpToMapIdent)
Immediately jumps to the map designated by its identifier (it must be in the selection).

#### Description
	boolean JumpToMapIdent(string)

***

### [GetCurrentMapInfo](_#GetCurrentMapInfo)
Returns a struct containing the infos for the current map. The struct contains the following fields : `Name`, `UId`, `FileName`, `Author`, `Environnement`, `Mood`, `BronzeTime`, `SilverTime`, `GoldTime`, `AuthorTime`, `CopperPrice`, `LapRace`, `NbLaps`, `NbCheckpoints`, `MapType`, `MapStyle`.

#### Description
	struct GetCurrentMapInfo()

***

### [GetNextMapInfo](_#GetNextMapInfo)
Returns a struct containing the infos for the next map. The struct contains the following fields : `Name`, `UId`, `FileName`, `Author`, `Environnement`, `Mood`, `BronzeTime`, `SilverTime`, `GoldTime`, `AuthorTime`, `CopperPrice`, `LapRace`, `MapType`, `MapStyle`. (`NbLaps` and `NbCheckpoints` are also present but always set to -1)

#### Description
	struct GetNextMapInfo()

***

### [GetMapInfo](_#GetMapInfo)
Returns a struct containing the infos for the map with the specified filename. The struct contains the following fields : `Name`, `UId`, `FileName`, `Author`, `Environnement`, `Mood`, `BronzeTime`, `SilverTime`, `GoldTime`, `AuthorTime`, `CopperPrice`, `LapRace`, `MapType`, `MapStyle`. (`NbLaps` and `NbCheckpoints` are also present but always set to -1)

#### Description
	struct GetMapInfo(string)

***

### [CheckMapForCurrentServerParams](_#CheckMapForCurrentServerParams)
Returns a boolean if the map with the specified filename matches the current server settings. 

#### Description
	boolean CheckMapForCurrentServerParams(string)

***

### [GetMapList](_#GetMapList)
Returns a list of maps among the current selection of the server. This method take two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the selection. The list is an array of structures. Each structure contains the following fields : `Name`, `UId`, `FileName`, `Environnement`, `Author`, `GoldTime`, `CopperPrice`, `MapType`, `MapStyle`.

#### Description
	array GetMapList(int, int)

***

### [AddMap](_#AddMap)
Add the map with the specified filename at the end of the current selection. Only available to Admin.

#### Description
	boolean AddMap(string)

***

### [AddMapList](_#AddMapList)
Add the list of maps with the specified filenames at the end of the current selection. The list of maps to add is an array of strings. Only available to Admin.

#### Description
	int AddMapList(array)

***

### [RemoveMap](_#RemoveMap)
Remove the map with the specified filename from the current selection. Only available to Admin.

#### Description
	boolean RemoveMap(string)

***

### [RemoveMapList](_#RemoveMapList)
Remove the list of maps with the specified filenames from the current selection. The list of maps to remove is an array of strings. Only available to Admin.

#### Description
	int RemoveMapList(array)

***

### [InsertMap](_#InsertMap)
Insert the map with the specified filename after the current map. Only available to Admin.

#### Description
	boolean InsertMap(string)

***

### [InsertMapList](_#InsertMapList)
Insert the list of maps with the specified filenames after the current map. The list of maps to insert is an array of strings. Only available to Admin.

#### Description
	int InsertMapList(array)

***

### [ChooseNextMap](_#ChooseNextMap)
Set as next map the one with the specified filename, if it is present in the selection. Only available to Admin.

#### Description
	boolean ChooseNextMap(string)

***

### [ChooseNextMapList](_#ChooseNextMapList)
Set as next maps the list of maps with the specified filenames, if they are present in the selection. The list of maps to choose is an array of strings. Only available to Admin.

#### Description
	int ChooseNextMapList(array)

***

### [LoadMatchSettings](_#LoadMatchSettings)
Set a list of maps defined in the playlist with the specified filename as the current selection of the server, and load the gameinfos from the same file. Only available to Admin.

#### Description
	int LoadMatchSettings(string)

***

### [AppendPlaylistFromMatchSettings](_#AppendPlaylistFromMatchSettings)
Add a list of maps defined in the playlist with the specified filename at the end of the current selection. Only available to Admin.

#### Description
	int AppendPlaylistFromMatchSettings(string)

***

### [SaveMatchSettings](_#SaveMatchSettings)
Save the current selection of map in the playlist with the specified filename, as well as the current gameinfos. Only available to Admin.

#### Description
	int SaveMatchSettings(string)

***

### [InsertPlaylistFromMatchSettings](_#InsertPlaylistFromMatchSettings)
Insert a list of maps defined in the playlist with the specified filename after the current map. Only available to Admin.

#### Description
	int InsertPlaylistFromMatchSettings(string)

***

### [GetPlayerList](_#GetPlayerList)
Returns the list of players on the server. This method take two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the list, an optional 3rd parameter is used for compatibility: struct version (0 = united, 1 = forever, 2 = forever, including the servers). The list is an array of PlayerInfo structures. Forever PlayerInfo struct is: `Login`, `NickName`, `PlayerId`, `TeamId`, `SpectatorStatus`, `LadderRanking`, and `Flags`. <br/>
`LadderRanking` is 0 when not in official mode, <br/>
`Flags` = `ForceSpectator`(0,1,2) + `IsReferee` * 10 + `IsPodiumReady` * 100 + `StereoDisplayMode` * 1000 + `IsManagedByAnOtherServer` * 10000 + `IsServer` * 100000 + `HasPlayerSlot` * 1000000 + `IsBroadcasting` * 10000000 + `HasJoinedGame` * 100000000<br/>
`SpectatorStatus` = `Spectator` + `TemporarySpectator` * 10 + `PureSpectator` * 100 + `AutoTarget` * 1000 + `CurrentTargetId` * 10000

#### Description
	array GetPlayerList(int, int, int)

***

### [GetPlayerInfo](_#GetPlayerInfo)
Returns a struct containing the infos on the player with the specified login, with an optional parameter for compatibility: struct version (0 = united, 1 = forever). The structure is identical to the ones from GetPlayerList. Forever PlayerInfo struct is: `Login`, `NickName`, `PlayerId`, `TeamId`, `SpectatorStatus`, `LadderRanking`, and `Flags`. <br/>
`LadderRanking` is 0 when not in official mode, <br/>
`Flags` = `ForceSpectator`(0,1,2) + `IsReferee` * 10 + `IsPodiumReady` * 100 + `StereoDisplayMode` * 1000 + `IsManagedByAnOtherServer` * 10000 + `IsServer` * 100000 + `HasPlayerSlot` * 1000000 + `IsBroadcasting` * 10000000 + `HasJoinedGame` * 100000000<br/>
`SpectatorStatus` = `Spectator` + `TemporarySpectator` * 10 + `PureSpectator` * 100 + `AutoTarget` * 1000 + `CurrentTargetId` * 10000

#### Description
	struct GetPlayerInfo(string, int)

***

### [GetDetailedPlayerInfo](_#GetDetailedPlayerInfo)
Returns a struct containing the infos on the player with the specified login. The structure contains the following fields : `Login`, `NickName`, `PlayerId`, `TeamId`, `IPAddress`, `DownloadRate`, `UploadRate`, `Language`, `IsSpectator`, `IsInOfficialMode`, a structure named `Avatar`, an array of structures named `Skins`, a structure named `LadderStats`, `HoursSinceZoneInscription` and `OnlineRights` (0: nations account, 3: united account). Each structure of the array `Skins` contains two fields `Environnement` and a struct `PackDesc`. Each structure `PackDesc`, as well as the struct `Avatar`, contains two fields `FileName` and `Checksum`.

#### Description
	struct GetDetailedPlayerInfo(string)

***

### [GetMainServerPlayerInfo](_#GetMainServerPlayerInfo)
Returns a struct containing the player infos of the game server (ie: in case of a basic server, itself; in case of a relay server, the main server), with an optional parameter for compatibility: struct version (0 = united, 1 = forever). The structure is identical to the ones from GetPlayerList. Forever PlayerInfo struct is: `Login`, `NickName`, `PlayerId`, `TeamId`, `SpectatorStatus`, `LadderRanking`, and `Flags`. <br/>
`LadderRanking` is 0 when not in official mode, <br/>
`Flags` = `ForceSpectator`(0,1,2) + `IsReferee` * 10 + `IsPodiumReady` * 100 + `StereoDisplayMode` * 1000 + `IsManagedByAnOtherServer` * 10000 + `IsServer` * 100000 + `HasPlayerSlot` * 1000000 + `IsBroadcasting` * 10000000 + `HasJoinedGame` * 100000000<br/>
`SpectatorStatus` = `Spectator` + `TemporarySpectator` * 10 + `PureSpectator` * 100 + `AutoTarget` * 1000 + `CurrentTargetId` * 10000

#### Description
	struct GetMainServerPlayerInfo(int)

***

### [GetCurrentRanking](_#GetCurrentRanking)
Returns the current rankings for the race in progress. (In trackmania legacy team modes, the scores for the two teams are returned. In other modes, it is the individual players` scores) This method take two parameters. The first parameter specifies the maximum number of infos to be returned, and the second one the starting index in the ranking. The ranking returned is a list of structures. Each structure contains the following fields : `Login`, `NickName`, `PlayerId` and `Rank`. In addition, for legacy trackmania modes it also contains `BestTime`, `Score`, `NbrLapsFinished`, `LadderScore`, and an array `BestCheckpoints` that contains the checkpoint times for the best race.

#### Description
	array GetCurrentRanking(int, int)

***

### [GetCurrentRankingForLogin](_#GetCurrentRankingForLogin)
Returns the current ranking for the race in progressof the player with the specified login (or list of comma-separated logins). The ranking returned is a list of structures. Each structure contains the following fields : `Login`, `NickName`, `PlayerId` and `Rank`. In addition, for legacy trackmania modes it also contains `BestTime`, `Score`, `NbrLapsFinished`, `LadderScore`, and an array `BestCheckpoints` that contains the checkpoint times for the best race.

#### Description
	array GetCurrentRankingForLogin(string)

***

### [GetCurrentWinnerTeam](_#GetCurrentWinnerTeam)
Returns the current winning team for the race in progress. (-1: if not in team mode, or draw match)

#### Description
	int GetCurrentWinnerTeam()

***

### [ForceScores](_#ForceScores)
Force the scores of the current game. Only available in rounds and team mode. You have to pass an array of structs {int `PlayerId`, int `Score`}. And a boolean `SilentMode` - if true, the scores are silently updated (only available for SuperAdmin), allowing an external controller to do its custom counting... Only available to Admin/SuperAdmin.

#### Description
	boolean ForceScores(array, boolean)

***

### [ForcePlayerTeam](_#ForcePlayerTeam)
Force the team of the player. Only available in team mode. You have to pass the login and the team number (0 or 1). Only available to Admin.

#### Description
	boolean ForcePlayerTeam(string, int)

***

### [ForcePlayerTeamId](_#ForcePlayerTeamId)
Force the team of the player. Only available in team mode. You have to pass the playerid and the team number (0 or 1). Only available to Admin.

#### Description
	boolean ForcePlayerTeamId(int, int)

***

### [ForceSpectator](_#ForceSpectator)
Force the spectating status of the player. You have to pass the login and the spectator mode (0: user selectable, 1: spectator, 2: player, 3: spectator but keep selectable). Only available to Admin.

#### Description
	boolean ForceSpectator(string, int)

***

### [ForceSpectatorId](_#ForceSpectatorId)
Force the spectating status of the player. You have to pass the playerid and the spectator mode (0: user selectable, 1: spectator, 2: player, 3: spectator but keep selectable). Only available to Admin.

#### Description
	boolean ForceSpectatorId(int, int)

***

### [ForceSpectatorTarget](_#ForceSpectatorTarget)
Force spectators to look at a specific player. You have to pass the login of the spectator (or an empty string for all) and the login of the target (or an empty string for automatic), and an integer for the camera type to use (-1 = leave unchanged, 0 = replay, 1 = follow, 2 = free). Only available to Admin.

#### Description
	boolean ForceSpectatorTarget(string, string, int)

***

### [ForceSpectatorTargetId](_#ForceSpectatorTargetId)
Force spectators to look at a specific player. You have to pass the id of the spectator (or -1 for all) and the id of the target (or -1 for automatic), and an integer for the camera type to use (-1 = leave unchanged, 0 = replay, 1 = follow, 2 = free). Only available to Admin.

#### Description
	boolean ForceSpectatorTargetId(int, int, int)

***

### [SpectatorReleasePlayerSlot](_#SpectatorReleasePlayerSlot)
Pass the login of the spectator. A spectator that once was a player keeps his player slot, so that he can go back to race mode. Calling this function frees this slot for another player to connect. Only available to Admin.

#### Description
	boolean SpectatorReleasePlayerSlot(string)

***

### [SpectatorReleasePlayerSlotId](_#SpectatorReleasePlayerSlotId)
Pass the playerid of the spectator. A spectator that once was a player keeps his player slot, so that he can go back to race mode. Calling this function frees this slot for another player to connect. Only available to Admin.

#### Description
	boolean SpectatorReleasePlayerSlotId(int)

***

### [ManualFlowControlEnable](_#ManualFlowControlEnable)
Enable control of the game flow: the game will wait for the caller to validate state transitions. Only available to Admin.

#### Description
	boolean ManualFlowControlEnable(boolean)

***

### [ManualFlowControlProceed](_#ManualFlowControlProceed)
Allows the game to proceed. Only available to Admin.

#### Description
	boolean ManualFlowControlProceed()

***

### [ManualFlowControlIsEnabled](_#ManualFlowControlIsEnabled)
Returns whether the manual control of the game flow is enabled. 0 = no, 1 = yes by the xml-rpc client making the call, 2 = yes, by some other xml-rpc client. Only available to Admin.

#### Description
	int ManualFlowControlIsEnabled()

***

### [ManualFlowControlGetCurTransition](_#ManualFlowControlGetCurTransition)
Returns the transition that is currently blocked, or an empty string if none. (That is exactly the value last received by the callback.) Only available to Admin.

#### Description
	string ManualFlowControlGetCurTransition()

***

### [CheckEndMatchCondition](_#CheckEndMatchCondition)
Returns the current match ending condition. Return values are: `Playing`, `ChangeMap` or `Finished`.

#### Description
	string CheckEndMatchCondition()

***

### [GetNetworkStats](_#GetNetworkStats)
Returns a struct containing the networks stats of the server. The structure contains the following fields : `Uptime`, `NbrConnection`, `MeanConnectionTime`, `MeanNbrPlayer`, `RecvNetRate`, `SendNetRate`, `TotalReceivingSize`, `TotalSendingSize` and an array of structures named `PlayerNetInfos`. Each structure of the array PlayerNetInfos contains the following fields : `Login`, `IPAddress`, `LastTransferTime`, `DeltaBetweenTwoLastNetState`, `PacketLossRate`. Only available to SuperAdmin.

#### Description
	struct GetNetworkStats()

***

### [StartServerLan](_#StartServerLan)
Start a server on lan, using the current configuration. Only available to SuperAdmin.

#### Description
	boolean StartServerLan()

***

### [StartServerInternet](_#StartServerInternet)
Start a server on internet, using the current configuration. Only available to SuperAdmin.

#### Description
	boolean StartServerInternet()

***

