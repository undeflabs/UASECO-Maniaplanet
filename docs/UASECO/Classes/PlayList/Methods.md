# Class PlayList
###### Documentation of includes/core/playlist.class.php


***


Provides and handles a Playlist for Maps.

> PLEASE NOTE THAT THIS CLASS IS STIL UNDER DEVELOPMENT AND CURRENTLY WITHOUT ANY FUNCTION!


## [Methods](_#Methods)


### [addMapToPlaylist](_#addMapToPlaylist)
Adds a map to the play list.


#### Description
	void = addMapToPlaylist ( string $uid, string $login, string $method = 'select' )


#### Parameters
*	`$uid`

	`UID` of a [Class Map](/Development/Classes/Map.php) object

*	`$login`

	`login` of a [Class Player](/Development/Classes/Player.php) object

*	`$method`

	Possible values for are `select`, `vote`, `pay`, `add`


#### Example
	$aseco->server->maps->playlist->addMapToPlaylist($map->uid, $player->login, 'select');



***



### [isMapInPlaylistByUid](_#isMapInPlaylistByUid)
xxx


#### Description
	boolean = isMapInPlaylistByUid ( string $uid )


#### Parameters
*	`$uid`

	`UID` of a [Class Map](/Development/Classes/Map.php) object


#### Example
	$result = $aseco->server->maps->playlist->isMapInPlaylistByUid($map->uid);



***



### [getPlaylistEntryByUid](_#getPlaylistEntryByUid)
xxx


#### Description
	mixed = getPlaylistEntryByUid ( string $uid )


#### Parameters
*	`$uid`

	`UID` of a [Class Map](/Development/Classes/Map.php) object


#### Example
	$result = $aseco->server->maps->playlist->getPlaylistEntryByUid($map->uid);


#### Return Values
	true
