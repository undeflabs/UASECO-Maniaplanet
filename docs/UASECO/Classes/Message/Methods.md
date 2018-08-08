# Class Message
###### Documentation of includes/core/message.class.php

Part of multilanguage support.



## [Methods](_#Methods)


### [addPlaceholders](_#addPlaceholders)
Set content for the placeholders, which will be replaced.


#### Description
	void = addPlaceholders ( string $value1 [, string $value2 ] )


#### Parameters
*	`$value`

	One or more content for the placeholders.


#### Example
	$msg = new Message('plugin.example', 'example_link');
	$msg->addPlaceholders(
		'1.0.0',
		'$L['. UASECO_WEBSITE .']'. UASECO_WEBSITE .'$L'
	);
	$msg->sendChatMessage($login);



***



### [finish](_#finish)
xxx


#### Description
	string = finish ( string $id [, string $is_login = true ] )


#### Parameters
*	`$id`

	xxx

*	`$is_login`

	A player login.


#### Example
	new Message('common', 'enabled'))->finish($login)


#### Return Values
	eingeschaltet

If the player is using `de` (german) for his game.


***



### [finishMultiline](_#finishMultiline)
xxx


#### Description
	string = finishMultiline ( string $login )


#### Parameters
*	`$login`

	A player login.


#### Example
	$message = (new Message('common', 'player_warning'))->finishMultiline('undef.de');


#### Return Values
	$s$F00This is an administrative warning.

	$gWhatever you wrote is against our server's policy.
	Not respecting other players, or
	using offensive language might result in a $F00kick, or ban $39Fthe next time.

	$gThe server administrators.



***



### [sendChatMessage](_#sendChatMessage)
Sends the multilanguage chat message to the chat.


#### Description
	void = sendChatMessage ( string $logins = null )


#### Parameters
*	`$logins`

	A comma separated list of player logins, or one player login. If no player login is given, then the message is send to all players.


#### Example
	$msg = new Message('plugin.example', 'example_welcome');
	$msg->sendChatMessage('undef.de,askuri');
