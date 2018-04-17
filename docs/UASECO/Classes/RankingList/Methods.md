# Class RankingList
###### Documentation of includes/core/rankinglist.class.php

Manages Player Ranking from the dedicated server.



## [Methods](_#Methods)


### [addPlayer](_#addPlayer)
Adds a given Player object to the ranking list.

#### Description
	boolean = addPlayer ( player class $player )


#### Parameters
*	`$player`

	A Class Player object from the Player Class to the ranking list.


#### Example
	$player = new Player($data);
	$result = $aseco->server->rankings->addPlayer($player);


#### Return Values
	true



***



### [count](_#count)
Returns the amount of rankings.


#### Description
	int = count ( void )


#### Example
	$amount = $aseco->server->rankings->count();


#### Return Values
	32



***



### [update](_#update)
Update the ranking list with an new ranking from a Player.


#### Description
	void = update ( array $update )


#### Parameters
*	`$update`

	A array with ranking informations for the ranking list.


#### Example
	$update = array(
		'rank'				=> 1,
		'login'				=> 'puennt_ennel',
		'nickname'			=> '$S$W$F90Gιммє$FF0ツ',
		'round_points'			=> 12,
		'map_points'			=> 10,
		'match_points'			=> 8,
		'best_race_time'		=> 3741,				// Best race time in milliseconds
		'best_race_respawns'		=> 2,					// Number of respawn during best race
		'best_race_checkpoints'		=> array(1740,2475,3122,3741),		// Checkpoints times during best race
		'best_lap_time'			=> 0,					// Best lap time in milliseconds (only in ModeScript "Laps")
		'best_lap_respawns'		=> 0,					// Number of respawn during best lap (only in ModeScript "Laps")
		'best_lap_checkpoints'		=> 0,					// Checkpoints times during best lap (only in ModeScript "Laps")
		'prev_race_time'		=> 7411,				// Best race time in milliseconds of the previous race
		'prev_race_respawns'		=> 3,					// Number of respawn of the previous race
		'prev_race_checkpoints'		=> array(2871,3012,4587,7411),		// Checkpoints times of the previous race
		'stunts_score'			=> 50,
		'prev_stunts_score'		=> 125,
	);
	$result = $aseco->server->rankings->update($update);



***



### [reset](_#reset)
Resets the ranking list.


#### Description
	void = reset ( void )


#### Example
	$aseco->server->rankings->reset();



***



### [getRankByLogin](_#getRankByLogin)
Returns a Class Ranking object from the given Player login.


#### Description
	Class Ranking object = getRankByLogin ( string $login )


#### Parameters
*	`$login`

	Login from a Player to get the ranking from.


#### Example
	$login = 'puennt_ennel';
	$rank = $aseco->server->rankings->getRankByLogin($login);



***



### [getRank](_#getRank)
Returns a Class Ranking object from the given rank.


#### Description
	Class Ranking object = getRank ( int $rank )


#### Parameters
*	`$rank`

	Number of a rank.


#### Example
	$ranking = $aseco->server->rankings->getRank(3);



***



### [getRange](_#getRange)
Returns a array with Class Ranking objects from the given amount of rankings.


#### Description
	array = getRange ( int $offset, int $length )


#### Parameters
*	`$offset`

	The starting index in the ranking to start from.

*	`$length`

	Specifies the maximum number of infos to be returned.


#### Example
	// Get two rankings starting from rank 10
	$offset = 10;
	$length = 2;
	$ranking = $aseco->server->rankings->getRange($offset, $length);



***



### [getTop3](_#getTop3)
Returns a array with Class Ranking objects from the TOP 3.


#### Description
	array = getTop3 ( void )


#### Example
	$ranking = $aseco->server->rankings->getTop3();



***



### [getTop10](_#getTop10)
Returns a array with Class Ranking objects from the TOP 10.


#### Description
	array = getTop10 ( void )


#### Example
	$ranking = $aseco->server->rankings->getTop10();



***



### [getTop50](_#getTop50)
Returns a array with Class Ranking objects from the TOP 50.


#### Description
	array = getTop50 ( void )


#### Example
	$ranking = $aseco->server->rankings->getTop50();
