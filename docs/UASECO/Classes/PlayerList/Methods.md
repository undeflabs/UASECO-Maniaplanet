# Class PlayerList
###### Documentation of includes/core/playerlist.class.php


***


Manages Players on the server, add/remove Players and provides several get functions.



## [Methods](_#Methods)


### [count](_#count)
Returns the amount of connected Players.


#### Description
	int = count ( void )


#### Example
	$amount = $aseco->server->players->count();


#### Return Values
	32



***



### [getSpectatorCount](_#getSpectatorCount)
Returns the amount of connected Spectators.


#### Description
	int = getSpectatorCount ( void )


#### Example
	$amount = $aseco->server->players->getSpectatorCount();


#### Return Values
	16



***



### [addPlayer](_#addPlayer)
Adds a given Player object to the Player list.


#### Description
	boolean = addPlayer ( Class Player object $player )


#### Parameters
*	`$player`

	A Player object from the Class Player.


#### Example
	$player = new Player($data);
	$result = $aseco->server->players->addPlayer($player);



***



### [removePlayer](_#removePlayer)
Removes a Player object from the Player list by a given login.


#### Description
	boolean = removePlayer ( string $login )

If the login was found and the Class Player object was removed, then this Method returns a boolean true, otherwise a boolean false.


#### Parameters
*	`$login`

	A login from a Player.


#### Example
	$result = $aseco->server->players->removePlayer($player);



***



### [getPlayerByLogin](_#getPlayerByLogin)
Returns a Player object from the given login, or boolean false if the login was not found.


#### Description
	mixed = getPlayerByLogin ( string $login )

If login can not be found, then false is returned, otherwise a Class Player object is returned.


#### Parameters
*	`$login`

	A login from a Player.


#### Example
	if ($player = $aseco->server->players->getPlayerByLogin($login)) {
		// Player object returned, go ahead
	}
	else {
		// Given login was not found
	}



***



### [getPlayerIdByLogin](_#getPlayerIdByLogin)
Returns the database Player ID from the given login.


#### Description
	int = getPlayerIdByLogin ( string $login, [ boolean $forcequery ] )

If login can not be found, then 0 is returned. Otherwise the representing Player ID of the Database.


#### Parameters
*	`$login`

	A login from a Player for seaching in the Player list.

*	`$forcequery`

	If passed, forces a database query instead of using the Player list.


#### Example
	$login = 'undef.de';
	$forcequery = false;
	$id = $aseco->server->players->getPlayerIdByLogin($login, $forcequery);



***



### [getPlayerNickname](_#getPlayerNickname)
Returns the Player nickname from the given login.


#### Description
	mixed = getPlayerNickname ( string $login, [ boolean $forcequery ] )

If login can not be found, then `false` is returned, otherwise a string containing the nickname.


#### Parameters
*	`$login`

	A login from a Player for seaching in the Player list.

*	`$forcequery`

	If passed, forces a database query instead of using the Player list.


#### Example
	$login = 'puennt_ennel';
	$forcequery = false;
	$id = $aseco->server->players->getPlayerNickname($login, $forcequery);


#### Return Values
	$S$W$F90Gιммє$FF0ツ$z



***



### [getPlayerParam](_#getPlayerParam)
Finds an online Player object from its login or Player ID.


#### Description
	mixed = getPlayerParam ( Class Player object $player, mixed $param, boolean $offline ])

Returns false if nothing was found, otherwise a Class Player object is returned.


#### Parameters

*	`$player`

	A Class Player object from the actual Player.

*	`$param`

	Can be numeric between 0 and 299 to search in the Class Player object list player_list, or login from a Player to search for.

*	`$offline`

	If passed and set to true, then a database search is started too when the given login at $param is not online.


#### Example
	$player = $aseco->server->players->getPlayer('puennt_ennel');
	$param = 'puennt_ennel';
	$offline = true;
	$id = $aseco->server->players->getPlayerParam($player, $param, $offline);

