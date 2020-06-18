# Class Message
###### Documentation of includes/core/message.class.php


***


Part of multilanguage support.



## [Methods](_#Methods)


### [addPlaceholders](_#addPlaceholders)
Set content for the placeholders


#### Description
	void = addPlaceholders (mixed $value1 [, mixed $value2 ])


#### Parameters
*	`$value`

	Content for the placeholders. Datatype can be
	- String
	- Message
	- Message[]


#### Example
	$msg = new Message('plugin.example', 'example_link');
	$msg->addPlaceholders(
		'1.0.0',
		'$L['. UASECO_WEBSITE .']'. UASECO_WEBSITE .'$L'
	);



***



### [finish](_#finish)
Returns the translated message. (If no translation is available, returns the English message)


#### Description
	String = finish ( String $id [, boolean $is_login] )


#### Parameters
*	`$id`

	Can be a Player-Login or a language-code

*	`$is_login`

	Pass 'false' if language-code is given


#### Example
	$message = (new Message('plugin.example', 'slash_hello_message'))->finish($login);
	$message = (new Message('plugin.example', 'slash_hello_message'))->finish('de', false);


#### Return Values
	Hello dude!		(if language of $login is English)
	Hallo Freund!



***



### [finishMultiline](_#finishMultiline)
Returns the translated message as an array, splitted by {br}. (If no translation is available, returns it in English)


#### Description
	String = finishMultiline ( String $login )


#### Parameters
*	`$login`

	A player login.


#### Example
	$message = (new Message('plugin.<your_plugin_name>', '<your_xml_tag>'))->finishMultiline($login);



***



### [sendChatMessage](_#sendChatMessage)
Sends the multilanguage chat message to the chat, either to all players or to a specific player.


#### Description
	void = sendChatMessage ( [String $logins] )


#### Parameters
*	`$logins`

	A comma separated list of player logins, or one player login. If no player login is given, then the message is send to all players.


#### Example
	$msg = new Message('plugin.example', 'example_welcome');
	$msg->sendChatMessage('undef.de,askuri');
