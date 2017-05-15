# Class Gameinfo
###### Documentation of includes/core/gameinfo.class.php

Provides information to the current game which is running.



## [Methods](_#Methods)


### [getModeId](_#getModeId)
Returns the ID from the current running Modescript or given Modescript name.


#### Description
	int = getModeId ( [ $name ] )


#### Parameters
*	`$name`

	If passed, then this Method returns the ID of the given Modescript name, instead of the current running Modescript.


#### Example
	$name = 'time_attack';
	$id = $aseco->server->gameinfo->getModeId($name);


#### Return Values
	2



***



### [getModeName](_#getModeName)
Returns the shorten Scriptname from the current running Modescript or given Modescript ID.

#### Description
	string = getModeName ( [ $id ] )


#### Parameters
*	`$id`

	If passed, then this Method returns the name of the given Modescript ID, instead of the current running Modescript.


#### Example
	$id = 8;
	$name = $aseco->server->gameinfo->getModeName($id);


#### Return Values
	Laps



***



### [getModeScriptName](_#getModeScriptName)
Returns the ScriptName from the current running Modescript.


#### Description
	string = getModeScriptName ( [ $id ] )


#### Parameters
*	`$id`

	If passed, then this Method returns the Scriptname of the given Modescript, instead of the current running Modescript.


#### Example
	$scriptname = $aseco->server->gameinfo->getModeScriptName();


#### Return Values
	TimeAttack.Script.txt



***



### [getModeVersion](_#getModeVersion)
Returns the version from the current running Modescript.


#### Description
	string = getModeVersion ( void )


#### Example
	$version = $aseco->server->gameinfo->getModeVersion();


#### Return Values
	2014-07-02



***



### [getNextModeId](_#getNextModeId)
Returns the Modescript ID from the next running Modescript.


#### Description
	string = getNextModeId ( void )


#### Example
	$id = $aseco->server->gameinfo->getNextModeId();


#### Return Values
	2



***



### [getNextModeName](_#getNextModeName)
Returns the shorten Scriptname from the next running Modescript.


#### Description
	string = getNextModeName ( void )


#### Example
	$name = $aseco->server->gameinfo->getNextModeName();


#### Return Values
	Laps
