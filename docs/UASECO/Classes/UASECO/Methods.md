# Class UASECO
###### Documentation of UASECO

Core Class of UASECO with many useful Methods.



## [Methods](_#Methods)




### [allowAbility](_#allowAbility)
Checks if the given player is allowed to perform this ability, e.g. `/help`.


#### Description
	void = allowAbility ( Class Player object $player, string $ability )


#### Parameters
*	`$player`

	[Class Player](/Development/Classes/Player.php) object

*	`$ability`

	Chat ability to check the rights for, e.g. `help` of the chat command `/help`.


#### Example
	$result = $aseco->allowAbility($player, $chat_command);


#### Return Values
	true



***



### [allowAdminAbility](_#allowAdminAbility)
Checks if an admin is allowed to perform this ability, e.g. `/admin help`.


#### Description
	boolean = allowAdminAbility ( string $ability )


#### Parameters
*	`$ability`

	Chat ability to check the rights for, e.g. `help` of the chat command `/admin help`.


#### Example
	$result = $aseco->allowAdminAbility('help');


#### Return Values
	true



***



### [allowOperatorAbility](_#allowOperatorAbility)
Checks if an operator is allowed to perform this ability, e.g. `/admin help`.


#### Description
	boolean = allowOperatorAbility ( string $ability )


#### Parameters
*	`$ability`

	Chat ability to check the rights for, e.g. `help` of the chat command `/admin help`.


#### Example
	$result = $aseco->allowOperatorAbility('help');


#### Return Values
	false



***



### [isAnyAdmin](_#isAnyAdmin)
Checks if the given player is in any admin tier with, optionally, an authorized IP.


#### Description
	boolean = isAnyAdmin ( Class Player object $player )


#### Parameters
*	`$player`

	[Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isAnyAdmin($player);


#### Return Values
	false



***



### [isAnyAdminByLogin](_#isAnyAdminByLogin)
Checks if the given player login is in any admin tier.


#### Description
	boolean = isAnyAdminByLogin ( string $login )


#### Parameters
*	`$login`

	The `login` from a [Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isAnyAdminByLogin($player->login);


#### Return Values
	false



***



### [isAdmin](_#isAdmin)
Checks if the given player is in admin list with, optionally, an authorized IP.


#### Description
	boolean = isAdmin ( Class Player object $player )


#### Parameters
*	`$player`

	[Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isAdmin($player);


#### Return Values
	false



***



### [isAdminByLogin](_#isAdminByLogin)
Checks if the given player login is in admin list.


#### Description
	boolean = isAdminByLogin ( string $login )


#### Parameters
*	`$login`

	The `login` from a [Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isAdminByLogin($player->login);


#### Return Values
	false



***



### [isMasterAdmin](_#isMasterAdmin)
Checks if the given player is in masteradmin list with, optionally, an authorized IP.


#### Description
	boolean = isMasterAdmin ( Class Player object $player )


#### Parameters
*	`$player`

	[Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isMasterAdmin($player);


#### Return Values
	false



***



### [isMasterAdminByLogin](_#isMasterAdminByLogin)
Checks if the given player login is in masteradmin list.


#### Description
	boolean = isMasterAdminByLogin ( string $login )


#### Parameters
*	`$login`

	The `login` from a [Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isMasterAdminByLogin($player->login);


#### Return Values
	false



***




### [isOperator](_#isOperator)
Checks if the given player is in operator list with, optionally, an authorized IP.


#### Description
	boolean = isOperator ( Class Player object $player )


#### Parameters
*	`$player`

	[Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isOperator($player);


#### Return Values
	false



***



### [isOperatorByLogin](_#isOperatorByLogin)
Checks if the given player login is in operator list.


#### Description
	boolean = isOperatorByLogin ( string $login )


#### Parameters
*	`$login`

	The `login` from a [Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isOperatorByLogin($player->login);


#### Return Values
	false



***



### [isLANLogin](_#isLANLogin)
Check login string for LAN postfix (pre/post v2.11.21).


#### Description
	boolean = isLANLogin ( string $login )


#### Parameters
*	`$login`

	The `login` from a [Class Player](/Development/Classes/Player.php) object


#### Example
	$result = $aseco->isLANLogin($player->login);


#### Return Values
	true



***



### [matchIP](_#matchIP)
Checks if the given player IP matches the corresponding list IP, allowing for class C and B wildcards, and multiple comma-separated IPs / wildcards.


#### Description
	boolean = matchIP ( string $playerip, string $listip )


#### Parameters
*	`$playerip`

	The `ip` from a [Class Player](/Development/Classes/Player.php) object

*	`$listip`

	The `ip` from a `config/UASECO.xml` at `<masteradmins><ipaddress>` or `config/adminops.xml` at `<admins><ipaddress>`


#### Example
	$i = array_search($player->login, $aseco->masteradmin_list['TMLOGIN']);
	$result = $aseco->matchIP($player->ip, $aseco->masteradmin_list['IPADDRESS'][$i]);


#### Return Values
	false



***



### [readLists](_#readLists)
Read Admin/Operator/Ability lists and apply them on the current instance.


#### Description
	boolean = readLists ( void )


#### Example
	$aseco->readLists();


#### Return Values
	true


***



### [writeLists](_#writeLists)
Write Admin/Operator/Ability lists to save them for future runs.


#### Description
	boolean = writeLists ( void )


#### Example
	$result = $aseco->writeLists();


#### Return Values
	true



***



### [readIPs](_#readIPs)
Read Banned IPs list and apply it on the current instance.


#### Description
	boolean = readIPs ( void )


#### Example
	$result = $aseco->readIPs();


#### Return Values
	true



***



### [writeIPs](_#writeIPs)
Write Banned IPs list to save it for future runs.


#### Description
	boolean = writeIPs ( void )


#### Example
	$result = $aseco->writeIPs();


#### Return Values
	true



***



### [console_text](_#console_text)
Outputs a formatted string without datetime.


#### Description
	void = console_text ( mixed $param [, mixed $param ] )


#### Parameters
*	`$param`

	Can be one or more string(s) or array(s).


#### Example
	$aseco->console_text('Output No.1', 'Output No.2');



***



### [console](_#console)
Outputs a string to console with datetime prefix.


#### Description
	void = console ( mixed $param [, mixed $param ] )


#### Parameters
*	`$param`

	Can be one or more string(s) or array(s).


#### Example
	$aseco->console('Output No.1', 'Output No.2');



***



### [dump](_#dump)
Wrapper for [`var_dump()`](http://php.net/manual/en/function.var-dump.php) to log into [`console()`](#console).


#### Description
	void = dump ( mixed $param [, mixed $param ] )


#### Parameters
*	`$param`

	Can be one or more string(s) or array(s).


#### Example
	$aseco->dump('Output No.1', 'Output No.2');



***



### [versionCheck](_#versionCheck)
Checks two version strings with [`version_compare()`](http://php.net/manual/en/function.version-compare.php).


#### Description
	boolean = versionCheck ( $wanted, $current [, $operator = '>' ] )


#### Parameters
*	`$wanted`

	First version number.

*	`$current`

	Second version number.

*	`$operator`

	If the third optional operator argument is specified, test for a particular relationship.
	The possible operators are: `<`, `lt`, `<=`, `le`, `>`, `gt`, `>=`, `ge`, `==`, `=`, `eq`, `!=`, `<>`, `ne` respectively.


#### Example
	$result = $aseco->versionCheck('1.0.0', '1.0.1', '>');


#### Return Values
	true



***



### [bool2string](_#bool2string)
Convert boolean value to text string.


#### Description
	string = bool2string ( boolean $input )


#### Parameters
*	`$input`

	Boolean value that should be converted to a string, e.g. `true`, `FALSE` or `null`.


#### Example
	$result = $aseco->bool2string('TrUe');


#### Return Values
	true



***



### [string2bool](_#string2bool)
Convert text string to boolean value.


#### Description
	boolean = string2bool ( string $input )


#### Parameters
*	`$input`

	String which could be `true`, `false` or `null` in any variation, e.g. `TrUe`, `fALsE`...


#### Example
	$result = $aseco->string2bool('TrUe');


#### Return Values
	true



***



### [bytes2shorthand](_#bytes2shorthand)
Convert bytes into a php.ini memory shorthand string.


#### Description
	string = bytes2shorthand ( int $bytes, string $size_format )


#### Parameters
*	`$bytes`

	Bytes to convert

*	`$size_format`

	Possible values are `K` (kibibyte), `M` (mebibyte) or `G` (gibibyte)


#### Example
	$result = $aseco->bytes2shorthand(1048576, 'M');


#### Return Values
	1M



***



### [cleanupLoginList](_#cleanupLoginList)
Remove whitespace and empty entries from a csv string, e.g. 'login1, login2, , login3,' to 'login1,login2,login3'.


#### Description
	string = cleanupLoginList ( $csv )


#### Parameters
*	`$csv`

	A comma seperated list of [Class Player](/Development/Classes/Player.php) object logins


#### Example
	$result = $aseco->cleanupLoginList('login1, login2, , login3,');


#### Return Values
	login1,login2,login3



***



### [handleSpecialChars](_#handleSpecialChars)
Converts `&`, `"`, `'`, `<`, `>` to HTML entities, removes `\n\n`, `\n` and `\r` and validates the string.


#### Description
	string = handleSpecialChars ( $string )


#### Parameters
*	`$string`

	String to work with.


#### Example
	$result = $aseco->handleSpecialChars("<hello>You're welcome &\n\ni guess you're looking nice!");


#### Return Values
	&lt;hello&gt;You&apos;re welcome &amp;i guess you&apos;re looking nice!



***



### [encodeEntities](_#encodeEntities)
Converts `&`, `"`, `'`, `<`, `>` to HTML entities.


#### Description
	string = encodeEntities ( string $input )


#### Parameters
*	`$input`

	String to work with.


#### Example
	$result = $aseco->encodeEntities("<hello>You're welcome &\n\ni guess you're looking nice!");


#### Return Values
	&lt;hello&gt;You&apos;re welcome &amp;\n\ni guess you&apos;re looking nice!



***



### [decodeEntities](_#decodeEntities)
Converts HTML entities into `&`, `"`, `'`, `<`, `>`.


#### Description
	string = encodeEntities ( string $input )


#### Parameters
*	`$input`

	String to work with.


#### Example
	$result = $aseco->encodeEntities("&lt;hello&gt;You&apos;re welcome &amp;\n\ni guess you&apos;re looking nice!");


#### Return Values
	<hello>You're welcome &\n\ni guess you're looking nice!



***



### [slugify](_#slugify)
This is a function to slugify (replace non-ASCII characters with ASCII characters) strings in PHP.
It tries to replace some characters to a similar ASCII character, e.g.:
`ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ` will be changed to `AAAAAEAAAECEEEEIIIIDNOOOOOEUUUUEYssaaaaaeaaaeceeeeiiiidnoooooeuuuueyy`


#### Description
	string = slugify ( string $input [, $delimiter = '-' ] )


#### Parameters
*	`$input`

	The string to work with.

*	`$delimiter`

	A character which will be used for unsupported characters.


#### Example
	$result = $aseco->slugify('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöùúûüýÿ');


#### Return Values
	AAAAAEAAAECEEEEIIIIDNOOOOOEUUUUEYssaaaaaeaaaeceeeeiiiidnoooooeuuuueyy



***



### [insertArrayElement](_#insertArrayElement)
Puts an element at a specific position into an array. Increases original size by one element.


#### Description
	array = insertArrayElement ( &$array, $value, $pos )


#### Parameters
*	`$array`

	That array that should be manipulated.

*	`$value`

	The value to add to the array at the given `$pos`.

*	`$pos`

	The array position where the `$value` should be placed.


#### Example
	$new_record_list = $aseco->insertArrayElement($record_list, 125441, 2);



***



### [removeArrayElement](_#removeArrayElement)
Removes an element from a specific position in an array. Decreases original size by one element.


#### Description
	boolean = removeArrayElement ( &$array, $pos )


#### Parameters
*	`$array`

	That array that should be manipulated.

*	`$pos`

	The array position which should be removed.


#### Example
	$aseco->removeArrayElement($record_list, 2);



***



### [moveArrayElement](_#moveArrayElement)
Moves an element from one position to the other. All items between are shifted down or up as needed.


#### Description
	boolean = moveArrayElement ( &$array, $from, $to )


#### Parameters
*	`$array`

	That array that should be manipulated.

*	`$from`

	The array position from which the value should be moved to `$to`.

*	`$to`

	The array position where the value from `$from` should be moved to.


#### Example
	$aseco->moveArrayElement($record_list, 2, 10);



***



### [fileExistsNoCase](_#fileExistsNoCase)
Case-insensitive file_exists replacement function. Returns matching path, otherwise false.


#### Description
	mixed = fileExistsNoCase ( $filepath )


#### Parameters
*	`$filepath`

	Filename with path to check for existence.


#### Example
	if ($nocasepath = $aseco->fileExistsNoCase($localfile)) {
		// Do something...
	}



***



### [stripStyles](_#stripStyles)
Strips all formatting from an input string, suitable for display within the game (`$$` escape pairs are preserved) and for logging, removes also `$g`, `$t`, `$i`, `$<`, `$>`, `$z`.


#### Description
	string = stripStyles ( $input [, $for_tm = true ] )


#### Parameters
*	`$input`

	The input string to strip formatting from.

*	`$for_tm`

	Optional flag to double up `$` into `$$` (default, for TM) or not (for logs, etc.).


#### Example
	$result = $aseco->stripStyles('$af0Brat$s$fffwurst');


#### Return Values
	Bratwurst



***



### [stripSizes](_#stripSizes)
Strips only size tags from TM strings, `$w$af0Brat$n$fffwurst` will become `$af0Brat$fffwurst`.


#### Description
	string = stripSizes ( $input )


#### Parameters
*	`$input`

	The input string to strip sizing from.


#### Example
	$result = $aseco->stripStyles('$w$af0Brat$n$fffwurst');


#### Return Values
	$af0Brat$fffwurst



***



### [stripNewlines](_#stripNewlines)
Strips newlines from strings.


#### Description
	string = stripNewlines ( $string )


#### Parameters
*	`$string`

	The input string to strip newlines from.


#### Example
	$result = $aseco->stripStyles("Bratwurst\nist\nlecker!");


#### Return Values
	Bratwurstistlecker!



***



### [stripBOM](_#stripBOM)
Remove BOM-header, see [http://en.wikipedia.org/wiki/Byte_order_mark]


#### Description
	string = stripBOM ( $string )


#### Parameters
*	`$string`

	The input string to strip the BOM-header from.


#### Example
	$result = $aseco->stripBOM("\xEF\xBB\xBFBratwurst!");


#### Return Values
	Bratwurst



***



### [validateUTF8String](_#validateUTF8String)
Return valid UTF-8 string, replacing faulty byte values with a given string.


#### Description
	string = validateUTF8String ( $input [, $invalidRepl = '' ] )


#### Parameters
*	`$input`

	The input string to work with.

*	`$invalidRepl`

	Character that will be replace unsupported bad multibyte character.


#### Example
	$result = $aseco->validateUTF8String('HÃ¶he, Ã¼ber, SÃ¼ÃŸ');


***



### [formatColors](_#formatColors)
Formats aseco color codes in a string, for example `{#server} hello` will returned as `$ff0 hello`.


#### Description
	string = formatColors ( string $message )


#### Parameters
*	`$message`

	A string for converting e.g. {#server} to $ff0


#### Example
	$message = $aseco->formatColors('Hello {#highlite}dude!');


#### Return Values
	Hello $FFFdude!



***



### [formatText](_#formatText)
Formats a text, replaces parameters in the text which are marked with `{n}`.


#### Description
	string = formatText ( string $message [, mixed $str1, mixed $str2, mixed $strN ... ] )


#### Parameters
*	`$message`

	The message to convert

*	`$str1...$strN`

	One or more parameter to replace in $message, parameter can be a string or int


#### Example
	$message = $aseco->formatText('{1} {2} sets servername to "{3}"',
		'MasterAdmin',
		$player->nickname,
		'New Cool Server Name'
	);


#### Return Values
	MasterAdmin ぎтяα¢кєяѕ|υηפєғ sets servername to "New Cool Server Name"



***



### [formatTime](_#formatTime)
Formats a string from the format `ssssttt` into the format: `hh:mm:ss.ttt`, `mm:ss.ttt`, `hh:mm:ss` or `mm:ss`.


#### Description
	string = formatTime ( int $MwTime [, boolean $tsec ] )


#### Parameters
*	`$MwTime`

	A time from a race from a Player

*	`$tsec`

	If passed and set to false this Method returns `hh:mm:ss` (if time has hours), or `mm:ss`.
	Default value is `true`.


#### Example
	$time_formated = $aseco->formatTime(13336);


#### Return Values
	0:13.336



***



### [timeString](_#timeString)
DETAILED_DESCRIPTION


#### Description
	string = timeString ( int $time [, $short = false] )


#### Parameters
*	`$time`

	The time to work with.

*	`$short`

	If set to `true` then the output is the short variant.
	Default value is `false`.


#### Example
	$result = $aseco->timeString(130323);


#### Return Values
	1 day 12 hours 12 minutes 3 seconds



***



### [formatNumber](_#formatNumber)
Format a number with grouped thousands.


#### Description
	string = formatNumber ( int $number, int $decimals [, string $dec_point, string $thousands_sep ] )


#### Parameters
*	`$number`

	The number being formatted.

*	`$decimals`

	Sets the number of decimal points, e.g. `2` to get `2,147.87` or `0` to get `2,147`

*	`$dec_point`

	If passed, sets the separator for the decimal point.
	Default value is `.`

*	`$thousands_sep`

	If passed, sets the thousands separator.
	Default value is `,`


#### Example
	$number_formated = $aseco->formatNumber(2147.87, 2);


#### Return Values
	2,147.87



***



### [formatFloat](_#formatFloat)
Format a float number.


#### Description
	float = formatFloat ( int $number, int $decimals [, string $dec_point, string $thousands_sep ] )


#### Parameters
*	`$number`

	The number being formatted.

*	`$decimals`

	Sets the number of decimal points, e.g. `4` to get `147.8724` or `0` to get `147`

*	`$dec_point`

	If passed, sets the separator for the decimal point.
	Default value is `.`

*	`$thousands_sep`

	If passed, sets the thousands separator.
	Default value is empty.


#### Example
	$number_formated = $aseco->formatNumber(2147.87, 2);


#### Return Values
	2,147.87



***



### [addManialink](_#addManialink)
Adds one or more Manialinks to the multiquery and send later together with other Manialinks waiting in the query queue.


#### Description
	void = addManialink ( string $widgets, string $logins, int $timeout, boolean $hideclick )


#### Parameters
*	`$widgets`

	A XML Manialink

*	`$logins`

	If passed, send the given Manialink to a single Login or or a comma-seperated list of Logins.
	Default value is `false` (send to all connected Players).

*	`$timeout`

	If passed, set a timeout for the Manialink after that the game client removes the Manialink from the Viewport.
	Default value is `0`.

*	`$hideclick`

	If passed `true`, then the game client removes the Manialink as soon the Player clicks on it.
	Default value is `false`.


#### Example
	$widget = '<manialink id="08154711"><quad posn="0 0 0" sizen="10 2" bgcolor="FFFF"></manialink>';
	$logins = array('leia_organa', 'darth_vader', 'han_solo', 'luke_skywalker');
	$timeout = 10;
	$hideclick = true;
	$aseco->addManialink($widget, $logins, $timeout, $hideclick);



***



### [sendManialink](_#sendManialink)
Sends one or more Manialinks immediately to the given `$logins`, or all Players.


#### Description
	void = sendManialink ( string $widgets, string $logins, int $timeout, boolean $hideclick )


#### Parameters
*	`$widgets`

	A XML Manialink

*	`$logins`

	If passed, send the given Manialink to a single Login or a comma-seperated list of Logins.
	Default value is `false` (send to all connected Players).

*	`$timeout`

	If passed, set a timeout for the Manialink after that the game client removes the Manialink from the Viewport.
	Default value is `0`.

*	`$hideclick`

	If passed `true`, then the game client removes the Manialink as soon the Player clicks on it.
	Default value is `false`.


#### Example
	$widget = '<manialink id="08154711"><quad posn="0 0 0" sizen="10 2" bgcolor="FFFF"></manialink>';
	$logins = array('leia_organa', 'darth_vader', 'han_solo', 'luke_skywalker');
	$timeout = 10;
	$hideclick = true;
	$aseco->sendManialink($widget, $logins, $timeout, $hideclick);



***



### [sendChatMessage](_#sendChatMessage)
Sends a chat message to the given `$logins`, or all Players.

> Deprecated, use [Class Message](/Development/Classes/Message.php) for locale support instead!


#### Description
	void = sendChatMessage ( string $message, string $logins )

This Method performs a `$aseco->formatColors()` on the given $message, so you does not need to call this by yourself.
Also all entities like `&lt; &gt; &amp; &apos; &quot;` are converted back to normal for chat.


#### Parameters
*	`$message`

	A message to send to the given Player

*	`$logins`

	If passed, send the given message to a single Login or a comma-seperated list of Logins.
	Default value is `false` (send to all connected Players).


#### Example
	$message = 'Hello {#highlite}dude!';
	$logins = array('leia_organa', 'darth_vader', 'han_solo', 'luke_skywalker');
	$aseco->sendChatMessage($message, $logins);



***



### [generateManialinkId](_#generateManialinkId)
Generates a unique ManialinkId


#### Description
	string = generateManialinkId ( void )


#### Example
	$hideclick = $aseco->generateManialinkId();


#### Return Values
	FgaoxbzMehVlnslkmyjpJioLczzHnha7



***



### [releaseEvent](_#releaseEvent)
Executes the functions which were registered for specified events.


#### Description
	void = releaseEvent ( $event_type, $callback_param )


#### Parameters
*	`$event_type`

	On of the [default events](/Development/Events.php#Eventlist) or a custom.

*	`$callback_param`

	Parameter to bypass to the plugins which have registered to be called when this event occurs.


#### Example
	$aseco->releaseEvent('onKarmaChange', $karma_array);



***



### [releaseChatCommand](_#releaseChatCommand)
Release a chat command from a plugin.


#### Description
	void = releaseChatCommand ( $command, $login )


#### Parameters
*	`$command`

	Chat command which should be released, e.g. `/help`.

*	`$login`

	A player login from a [Class Player](/Development/Classes/Player.php) object.


#### Example
	$aseco->releaseChatCommand('/help', $player->login);



***



### [getPlugin](_#getPlugin)
Returns a Class Plugin object of the given classname.


#### Description
	Class Plugin object = getPlugin ( $classname )


#### Parameters
*	`$classname`

	Classname of the plugin to receive the [Class Plugin](/Development/Classes/Plugin.php) object.


#### Example
	$plugin = $aseco->getPlugin('PluginAutotime');
