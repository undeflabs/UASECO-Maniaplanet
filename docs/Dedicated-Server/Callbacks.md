
# Dedicated Server
###### Callbacks from API version: 2013-04-16

***

### [ManiaPlanet.PlayerConnect](_#ManiaPlanet.PlayerConnect)

#### Description
	ManiaPlanet.PlayerConnect(string Login, bool IsSpectator)

***

### [ManiaPlanet.PlayerDisconnect](_#ManiaPlanet.PlayerDisconnect)

#### Description
	ManiaPlanet.PlayerDisconnect(string Login, string DisconnectionReason)

***

### [ManiaPlanet.PlayerChat](_#ManiaPlanet.PlayerChat)

#### Description
	ManiaPlanet.PlayerChat(int PlayerUid, string Login, string Text, bool IsRegistredCmd)

***

### [ManiaPlanet.PlayerManialinkPageAnswer](_#ManiaPlanet.PlayerManialinkPageAnswer)

#### Description
	ManiaPlanet.PlayerManialinkPageAnswer(int PlayerUid, string Login, string Answer, SEntryVal Entries[]);

	struct SEntryVal {
		string Name;
		string Value;
	}

***

### [ManiaPlanet.Echo](_#ManiaPlanet.Echo)

#### Description
	ManiaPlanet.Echo(string Internal, string Public)

***

### [ManiaPlanet.ServerStart](_#ManiaPlanet.ServerStart)

#### Description
	ManiaPlanet.ServerStart()

***

### [ManiaPlanet.ServerStop](_#ManiaPlanet.ServerStop)

#### Description
	ManiaPlanet.ServerStop()

***

### [ManiaPlanet.BeginMatch](_#ManiaPlanet.BeginMatch)

#### Description
	ManiaPlanet.BeginMatch()

***

### [ManiaPlanet.EndMatch](_#ManiaPlanet.EndMatch)

#### Description
	ManiaPlanet.EndMatch(SPlayerRanking Rankings[], int WinnerTeam)

	struct SPlayerRanking {
		string Login;
		string NickName;
		int PlayerId;
		int Rank;
	}

***

### [ManiaPlanet.BeginMap](_#ManiaPlanet.BeginMap)

#### Description
	ManiaPlanet.BeginMap(SMapInfo Map)

	struct SMapInfo {
		string Uid;
		string Name;
		string FileName;
		string Author;
		string Environnement;
		string Mood;
		int BronzeTime;
		int SilverTime;
		int GoldTime;
		int AuthorTime;
		int CopperPrice;
		bool LapRace;
		int NbLaps;
		int NbCheckpoints;
		string MapType;
		string MapStyle;
	}

***

### [ManiaPlanet.EndMap](_#ManiaPlanet.EndMap)

#### Description
	ManiaPlanet.EndMap(SMapInfo Map)

	struct SMapInfo {
		string Uid;
		string Name;
		string FileName;
		string Author;
		string Environnement;
		string Mood;
		int BronzeTime;
		int SilverTime;
		int GoldTime;
		int AuthorTime;
		int CopperPrice;
		bool LapRace;
		int NbLaps;
		int NbCheckpoints;
		string MapType;
		string MapStyle;
	}

***

### [ManiaPlanet.StatusChanged](_#ManiaPlanet.StatusChanged)

#### Description
	ManiaPlanet.StatusChanged(int StatusCode, string StatusName)

***

### [TrackMania.PlayerCheckpoint](_#TrackMania.PlayerCheckpoint)

#### Description
	TrackMania.PlayerCheckpoint(int PlayerUid, string Login, int TimeOrScore, int CurLap, int CheckpointIndex)

***

### [TrackMania.PlayerFinish](_#TrackMania.PlayerFinish)

#### Description
	TrackMania.PlayerFinish(int PlayerUid, string Login, int TimeOrScore)

***

### [TrackMania.PlayerIncoherence](_#TrackMania.PlayerIncoherence)

#### Description
	TrackMania.PlayerIncoherence(int PlayerUid, string Login)

***

### [ManiaPlanet.BillUpdated](_#ManiaPlanet.BillUpdated)

#### Description
	ManiaPlanet.BillUpdated(int BillId, int State, string StateName, int TransactionId)

***

### [ManiaPlanet.TunnelDataReceived](_#ManiaPlanet.TunnelDataReceived)

#### Description
	ManiaPlanet.TunnelDataReceived(int PlayerUid, string Login, base64 Data)

***

### [ManiaPlanet.MapListModified](_#ManiaPlanet.MapListModified)

#### Description
	ManiaPlanet.MapListModified(int CurMapIndex, int NextMapIndex, bool IsListModified)

***

### [ManiaPlanet.PlayerInfoChanged](_#ManiaPlanet.PlayerInfoChanged)

#### Description
	ManiaPlanet.PlayerInfoChanged(SPlayerInfo PlayerInfo)

	struct SPlayerInfo {
		string Login;
		string NickName;
		int PlayerId;
		int TeamId;
		int SpectatorStatus;
		int LadderRanking;
		int Flags;
	}

***

### [ManiaPlanet.VoteUpdated](_#ManiaPlanet.VoteUpdated)

#### Description
	ManiaPlanet.VoteUpdated(string StateName, string Login, string CmdName, string CmdParam)

StateName values: `NewVote`, `VoteCancelled`, `VotePassed` or `VoteFailed`

***

### [ManiaPlanet.ModeScriptCallback](_#ManiaPlanet.ModeScriptCallback)

#### Description
	ManiaPlanet.ModeScriptCallback(string Param1, string Param2)

***

### [ManiaPlanet.ModeScriptCallbackArray](_#ManiaPlanet.ModeScriptCallbackArray)

#### Description
	ManiaPlanet.ModeScriptCallbackArray(string Param1, string Params[])

***

### [ManiaPlanet.PlayerAlliesChanged](_#ManiaPlanet.PlayerAlliesChanged)

#### Description
	ManiaPlanet.PlayerAlliesChanged(string Login)

***

### [ScriptCloud.LoadData](_#ScriptCloud.LoadData)

#### Description
	ScriptCloud.LoadData(string Type, string Id)

> You must answer this callback by calling [`SetScriptCloudVariables`](/Dedicated-Server/Methods.php#SetScriptCloudVariables) for given object.

***

### [ScriptCloud.SaveData](_#ScriptCloud.SaveData)

#### Description
	ScriptCloud.SaveData(string Type, string Id)

> You must answer this callback by calling [`GetScriptCloudVariables`](/Dedicated-Server/Methods.php#GetScriptCloudVariables) for given object.
