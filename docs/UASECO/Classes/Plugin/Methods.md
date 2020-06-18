# Class Plugin
###### Documentation of includes/core/plugin.class.php


***


Structure for all plugins, extend this class to build your own one.



## [Methods](_#Methods)


### [setVersion](_#setVersion)
Stores the version of the Plugin. Please use this structure:

	2.3.5
	│ │ └───────── Maintenance
	│ └─────────── Minor
	└───────────── Major


#### Description
	void = setVersion ( string $version )


#### Parameters
*	`$version`

	A string that contains the current version of the Plugin.


#### Example
	$this->setVersion('2.3.5');



***



### [getVersion](_#getVersion)
Returns the version of the Plugin.


#### Description
	string = getVersion ( void )


#### Example
	$version = $this->getVersion();


#### Return Values
	2.3.5



***



### [setBuild](_#setBuild)
Stores the build of the Plugin. Please use this structure:

	2017-04-22 14:54
	│    │  │  │  └────── Minutes
	│    │  │  └───────── Hours
	│    │  └──────────── Day
	│    └─────────────── Month
	└──────────────────── Year

`Hours` and `Minutes` can be omitted, but for releases on the same day they are **required**.


#### Description
	void = setBuild ( string $build )


#### Parameters
*	`$version`

	A string that contains the current build of the Plugin.


#### Example
	$this->setBuild('2017-04-22 14:54');



***



### [getBuild](_#getBuild)
Returns the build of the Plugin.


#### Description
	string = getBuild ( void )


#### Example
	$build = $this->getBuild();


#### Return Values
	2017-04-22 14:54



***



### [setCopyright](_#setCopyright)
Stores the copyright of the Plugin.


#### Description
	void = setCopyright ( string $copyright )


#### Parameters
*	`$version`

	A string that contains the copyright of the Plugin.


#### Example
	$this->setCopyright('2014 - 2017 by undef.de');



***



### [getCopyright](_#getCopyright)
Returns the copyright of the Plugin.


#### Description
	string = getCopyright ( void )


#### Example
	$copyright = $this->getCopyright();


#### Return Values
	2014 - 2017 by undef.de



***



### [setFilename](_#setFilename)
Stores the filename the Plugin.


#### Description
	void = setFilename ( string $filename )

This method is primarily used by UASECO while loading the plugin to store the filename, you do not have to uses this.


#### Parameters
*	`$filename`

	A string that contains the current filename of the Plugin.


#### Example
	$this->setFilename('plugin.example.php');



***



### [getFilename](_#getFilename)
Returns the filename of the Plugin.


#### Description
	string = getFilename ( void )


#### Example
	$filename = $this->getFilename();				// Inside current Plugin
	$filename = $aseco->plugins['PluginExample']->getFilename();	// From a foreign Plugin


#### Return Values
	plugin.example.php



***



### [setAuthor](_#setAuthor)
Stores the authorname the Plugin.


#### Description
	void = setAuthor ( string $author )


#### Parameters
*	`$author`

	A string that contains the name.


#### Example
	$this->setAuthor('undef.de');



***



### [getAuthor](_#getAuthor)
Returns the authorname of the Plugin.


#### Description
	string = getAuthor ( void )


#### Example

	$author = $this->getAuthor();					// Inside current Plugin
	$author = $aseco->plugins['PluginExample']->getAuthor();	// From a foreign Plugin



***



### [setCoAuthors](_#setCoAuthors)
Stores the co-authornames the Plugin, duplicated entries will be made unique


#### Description
	void = setCoAuthors ( string $author, ... )


#### Parameters
*	`$authors`

	An array that contains the name.


#### Example
	$this->setCoAuthors('askuri','Bueddl');



***



### [getCoAuthors](_#getCoAuthors)
Returns the co-authornames of the Plugin.


#### Description
	array = getCoAuthors ( void )


#### Example
	$authors = $this->getCoAuthors();



***



### [setContributors](_#setContributors)
Stores the contributors the Plugin, duplicated entries will be made unique


#### Description
	void = setContributors ( string $contributor, ... )


#### Parameters
*	`$author`

	An array that contains the name.


#### Example
	$this->setContributors('reaby','leigham');



***



### [getContributors](_#getContributors)
Returns the contributors of the Plugin.


#### Description
	array = getContributors ( void )


#### Example
	$contributors = $this->getContributors();



***



### [setDescription](_#setDescription)
Stores the description the Plugin.


#### Description
	void = setDescription ( string $description )


#### Parameters
*	`$description`

	A string that contains the description from the Plugin.


#### Example
	$this->setDescription('Short description what the Plugin does...');



***



### [getDescription](_#getDescription)
Returns the description of the Plugin.


#### Description
	string = getDescription ( void )


#### Example
	$description = $this->getDescription();					// Inside current Plugin
	$description = $aseco->plugins['PluginExample']->getDescription();	// From a foreign Plugin


#### Return Values
	Short description what the Plugin does...



***



### [getClassname](_#getClassname)
Returns the classname of the Plugin.


#### Description
	Class Plugin object = getClassname ( void )


#### Example
	$class = $this->getClassname();					// Inside current Plugin
	$class = $aseco->plugins['PluginExample']->getClassname();	// From a foreign Plugin


#### Return Values
	Class Plugin object



***



### [addDependence](_#addDependence)
Add dependecies of the Plugin.


#### Description
	void = addDependence ( string $plugin, [ Class Dependece object $permissions, string $min_version, string $max_version ] )


#### Parameters
*	`$plugin`

	A string of a classname from the Plugin or `UASECO` to add a dependence on.

*	`$permissions`

	If passed, set the given permissions of the foreign Plugin.
	Default value is [`Dependence::REQUIRED`](/development/classes/dependence.php)
	For `UASECO` only [`Dependence::REQUIRED`](/development/classes/dependence.php) is used.

*	`$min_version`

	If passed, set the given min. required version of the foreign Plugin.
	Default value is `null`

*	`$max_version`

	If passed, set the given max. required version of the foreign Plugin.
	Default value is `null`


#### Example
	$this->addDependence('UASECO', Dependence::REQUIRED, '0.9.6', null);
	$this->addDependence('PluginLocalRecords', Dependence::REQUIRED, '1.0.0', null);



***



### [getDependencies](_#getDependencies)
Returns a Class Dependence object from the Plugin.


#### Description
	Class Dependence object = getDependencies ( void )


#### Example
	$dependence = $this->getDependencies();					// Inside current Plugin
	$dependence = $aseco->plugins['PluginExample']->getDependencies();	// From a foreign Plugin


#### Return Values
	Class Dependence object



***



### [registerEvent](_#registerEvent)
Register a callback function to a event.


#### Description
	void = registerEvent ( string $event, string $callback_function )


#### Parameters
*	`$event`

	A string of an [event](/Development/Events.php#Eventlist) to be called back and interact on.

*	`$callback_function`

	The callback function which should be called when the event is send.


#### Example
	$this->registerEvent('onPlayerConnect', 'onPlayerConnect');



***



### [getEvents](_#getEvents)
Returns a array with all events which the Plugin has registered.


#### Description
	array = getEvents ( void )

This method is primarily used by UASECO while loading the plugin to get all the events the Plugin has registered.



***



### [registerChatCommand](_#registerChatCommand)
Register a callback function to the chat commands list.


#### Description
	void = registerChatCommand ( string $chat_command, string $callback_function, string $help, [ Class Player constant $rights, array $params ] )


#### Parameters
*	`$chat_command`

	A chat command to interact on.

*	`$callback_function`

	The callback function which should be called when a Player calls the chat command.

*	`$help`

	A description of the chat command

*	`$rights`

	If passed, set the [Class Player](/development/classes/player.php#Constants) constant who is allowed to call this chat command.
	Default value is `Player::PLAYERS`

*	`$params`

	A array of parameter for the chat command and the related help description.


#### Example
	$params = array(
		'help'	=> 'Shows all available /example command parameter',	// e.g. "/example help"
		'time'	=> 'Display the current local time',			// e.g. "/example time"
		'date'	=> 'Display the current local date',			// e.g. "/example date"
	);
	$this->registerChatCommand('example', 'chat_example', Player::PLAYERS, $params);



***



### [getChatCommands](_#getChatCommands)
Returns a array with all registered chat commands which the Plugin has registered.


#### Description
	array = getChatCommands ( void )

This method is primarily used by UASECO while loading the plugin to get all the chat commands the Plugin has registered.




***



### [storePlayerData](_#storePlayerData)
Stores data into the Class Player object by a given key and on Player disconnect this data is stored into the database and is accessible with [`getPlayerData`](#getPlayerData) on a reconnection of the Player.

If you do not want that the stored data is saved into the database (because you used it only for temporary reason), then you can remove the data with [`removePlayerData`](#removePlayerData) on the event [`onPlayerDisconnectPrepare`](/Development/Events.php#Eventlist).


#### Description
	void = storePlayerData ( Class Player object $player, string $key, mixed $data )


#### Parameters
*	`$player`

	A [Class Player](/development/classes/player.php) object to store the wanted data in.

*	`$key`

	The key under which the data should be stored.

*	`$data`

	All kind of data (array, boolean, string...) you want to store.
	Please note that there is a PHP-Memory-Limit!


#### Example
	$this->storePlayerData($player, 'WidgetsVisible', true);



***



### [getPlayerData](_#getPlayerData)
Returns the data which is stored in a Class Player object by the given key.


#### Description
	mixed = getPlayerData ( Class Player object $player, string $key )


#### Parameters
*	`$player`

	A [Class Player](/development/classes/player.php) object where the data was stored in.

*	`$key`

	The key under which the data was stored.


#### Example
	$data = $this->getPlayerData($player, 'WidgetsVisible');


#### Return Values
	true



***



### [existsPlayerData](_#existsPlayerData)
Returns the data which is stored in a Class Player object by the given key.


#### Description
	boolean = existsPlayerData ( Class Player object $player, string $key )


#### Parameters
*	`$player`

	A [Class Player](/development/classes/player.php) object where the data could be stored in.

*	`$key`

	The key to check if there are data stored.


#### Example
	$result = $this->existsPlayerData($player, 'WidgetsVisible');


#### Return Values
	true



***



### [removePlayerData](_#removePlayerData)
Removes the data which is stored in a Class Player object by the given key.


#### Description
	void = removePlayerData ( Class Player object $player, string $key )


#### Parameters
*	`$player`

	A [Class Player](/development/classes/player.php) object where the data is stored in.

*	`$key`

	The key of which data should be removed.


#### Example
	$this->removePlayerData($player, 'WidgetsVisible');
