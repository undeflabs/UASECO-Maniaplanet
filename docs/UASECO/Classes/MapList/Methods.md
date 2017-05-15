# Class MapList
###### Documentation of includes/core/maplist.class.php

Stores information about all Maps on the dedicated server and provides several functions for sorting.



## [Methods](_#Methods)


### [getMapByUid](_#getMapByUid)
Returns a [Class Map](/Development/Classes/Map.php) object of the given UID form the current map list from the dedicated server.


#### Description
	Class Map object = getMapByUid ( $uid )

If map can not be found, then a empty map object is returned. When $map->uid is false, then the map was not present in the current map list.


#### Parameters
*	`$uid`

	The UID from a map.


#### Example
	$uid = 'g6BQJO4wlyolDbhsLvau7R7g7Ml';
	$map = $aseco->server->maps->getMapByUid($uid);



***



### [removeMapByUid](_#removeMapByUid)
Removes the Map with the given UID form the current map list from the dedicated server.


#### Description
	boolean = removeMapByUid ( $uid )


#### Parameters
*	`$uid`

	The UID from a map.


#### Example
	$uid = 'g6BQJO4wlyolDbhsLvau7R7g7Ml';
	$result = $aseco->server->maps->removeMapByUid($uid);



***



### [getMapById](_#getMapById)
Returns a [Class Map](/Development/Classes/Map.php) object of the given ID form the current map list from the dedicated server.


#### Description
	Class Map object = getMapById ( $id )

If map can not be found, then a empty map object is returned. When $map->uid is false, then the map was not present in the current map list.


#### Parameters
*	`$id`

	The ID from a map.


#### Example
	$id = 87;
	$map = $aseco->server->maps->getMapById($id);




***



### [getMapByFilename](_#getMapByFilename)
Returns a [Class Map](/Development/Classes/Map.php) object of the given filename form the current map list from the dedicated server.


#### Description
	Class Map object = getMapByFilename ( $filename )

If map can not be found, then a empty map object is returned. When $map->uid is false, then the map was not present in the current map list.


#### Parameters
*	`$filename`

	The filename from a map.


#### Example
	$filename = 'Short Distance N_ 07_13156.Map.gbx';
	$map = $aseco->server->maps->getMapByFilename($filename);



***



### [removeMapByFilename](_#removeMapByFilename)
Removes the Map with the given Filename form the current map list from the dedicated server.


#### Description
	boolean = removeMapByFilename ( $filename )


#### Parameters
*	`$filename`

	The filename from a map.


#### Example
	$filename = 'Short Distance N_ 07_13156.Map.gbx';
	$result = $aseco->server->maps->removeMapByFilename($filename);



***



### [getPreviousMap](_#getPreviousMap)
Returns a [Class Map](/Development/Classes/Map.php) object of the previous map played.


#### Description
	Class Map object = getPreviousMap ( void )


#### Example
	$map = $aseco->server->maps->getPreviousMap();



***


### [getCurrentMap](_#getCurrentMap)
Returns a [Class Map](/Development/Classes/Map.php) object of the current map.


#### Description
	Class Map object = getCurrentMap ( void )


#### Example
	$map = $aseco->server->maps->getCurrentMap();



***



### [getNextMap](_#getNextMap)
Returns a [Class Map](/Development/Classes/Map.php) object of the next map in the dedicated server map list.


#### Description
	Class Map object = getNextMap ( void )

If map can not be found, then a empty map object is returned. When $map->uid is false, then the map was not present in the current map list.


#### Example
	$map = $aseco->server->maps->getNextMap();



***



### [getThumbnailByUid](_#getThumbnailByUid)
Returns the JPEG-Image from the Map of the given UID form the current map list from the dedicated server.


#### Description
	JPEG-Image = getThumbnailByUid ( $uid )

If map can not be found or the image does not exists, then false is returned.


#### Parameters
*	`$uid`

	The UID from a map.


#### Example
	$uid = 'g6BQJO4wlyolDbhsLvau7R7g7Ml';
	$image = $aseco->server->maps->getThumbnailByUid($uid);



***



### [count](_#count)
Returns the amount of maps in the map list.


#### Description
	int = count ( void )


#### Example
	$amount = $aseco->server->maps->count();


#### Return Values
	138



***



### [parseMap](_#parseMap)
Parses a given Map file with the GBXChallMapFetcher Class.


#### Description
	Class GBXChallMapFetcher object = parseMap ( $file )

Please note that you have to give the filename of the Map with full path informations.


#### Example
	$file = 'GameServer/UserData/Maps/MX/Short Distance N_ 07_13156.Map.gbx';
	$gbx = $aseco->server->maps->parseMap($file);
