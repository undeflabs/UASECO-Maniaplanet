# Class Player
###### Documentation of includes/core/player.class.php

Structure of a Player, contains information from `GetPlayerInfo` and `GetDetailedPlayerInfo` ListMethods response.



## [Methods](_#Methods)


### [getWins](_#getWins)
Returns the amount of how many times the Player won in total.


#### Description
	int = getWins ( void )


#### Example
	$wins = $player->getWins();


#### Return Values
	32



***




### [getTimePlayed](_#getTimePlayed)
Returns how long the player is online on the current dedicated server in seconds since the first login.

#### Description
	int = getTimePlayed ( void )


#### Example
	$online = $player->getTimePlayed();


#### Return Values
	7634417



***



### [getTimeOnline](_#getTimeOnline)
Returns how long the player is online today in seconds.


#### Description
	int = getTimeOnline ( void )


#### Example
	$online = $player->getTimeOnline();


#### Return Values
	1800



***



### [getRecords](_#getRecords)
Returns a list of Map UIDs and the related records from a Player.


#### Description
	array = getRecords ( void )


#### Example
	$records = $player->getRecords();


#### Return Values
	array(7) {
	  ["LPPYseEb6GIQ5wDvlrrWztsdD_4"]=>
	  int(1)
	  ["QZX2H0Oq7Ixt2YHRAyTYcPbdOy3"]=>
	  int(28)
	  ["g6BQJO4wlyolDbhsLvau7R7g7Ml"]=>
	  int(29)
	  ["FgW4z80ZPAY8zqHxNKABg6n0342"]=>
	  int(30)
	  ["k72OVVjnagMGYrvG0E0byrvhKkl"]=>
	  int(31)
	  ["0CB2ZXEOwQaJBooOKVI5f8gjBag"]=>
	  int(42)
	  ["HQG7iA23WP_79vJeNghQwicWiCh"]=>
	  int(43)
	}



***



### [getRankFormated](_#getRankFormated)
Returns a formated server ranking string.


#### Description
	string = getRankFormated ( void )


#### Example
	$rank = $player->getRankFormated();


#### Return Values
	12/29,871 Average: 14.00



***



### [getSpectatorStatus](_#getSpectatorStatus)
Returns a value to check if the player is currently spectaing.


#### Description
	boolean = getSpectatorStatus ( void )


#### Example
	$status = $player->getSpectatorStatus();


#### Return Values
	false
