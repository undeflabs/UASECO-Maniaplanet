# Class MapHistory
###### Documentation of includes/core/maphistory.class.php

Map history for the dedicated server and provides several methods for the required handling of the history.



## [Methods](_#Methods)


### [getPreviousMap](_#getPreviousMap)
Returns a [Class Map](/Development/Classes/Map.php) object from the map that was played before.


#### Description
	Class Map object = getPreviousMap ( void )

This method returns a empty [Class Map](/Development/Classes/Map.php) object if the previous map can not be found.


#### Example
	$name = 'time_attack';
	$id = $aseco->server->gameinfo->getModeId($name);


#### Return Values
	2

> You do not need to call this method by yourself, just use `$this->server->maps->previous` (which holds a [Class Map](/Development/Classes/Map.php)) object instead!



***



### [isMapInHistoryById](_#isMapInHistoryById)
With this method you can check if a map is already in the history.


#### Description
	boolean = isMapInHistoryById ( $id )


#### Parameters
*	`$id`

	Database ID of the map to check.


#### Example
	$result = $aseco->server->maps->history->isMapInHistoryById(10);


#### Return Values
	true



***



### [isMapInHistoryByUid](_#isMapInHistoryByUid)
With this method you can check if a map is already in the history.


#### Description
	boolean = isMapInHistoryByUid ( $uid )


#### Parameters
*	`$uid`

	[Class Map](/Development/Classes/Map.php) object uid of the map to check.


#### Example
	$result = $aseco->server->maps->history->isMapInHistoryByUid($map->uid);


#### Return Values
	true
