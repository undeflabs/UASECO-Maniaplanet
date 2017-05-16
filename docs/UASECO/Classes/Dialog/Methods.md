# Class Dialog
###### Documentation of includes/core/dialog.class.php

Provides a comfortable, configurable styled Manialink dialog.



## [Methods](_#Methods)


### [setStyles](_#setStyles)
Setup the style of the Dialog.


#### Description
	void = setStyles ( array $params )


#### Parameters
*	`textcolor`

	Color for the text in [RGBA](http://en.wikipedia.org/wiki/RGBA_color_space) format, e.g. `09FF`.

*	`icon`

	Icon for the header, choose one from the Manialink [styles](maniaplanet:///:styles).


#### Example
	// Setup the styles
	$settings_style = array(
		'textcolor'		=> '09FF',
		'icon'			=> 'Icons64x64_1,ToolLeague1',
	);

	// Create the Dialog
	$dialog = new Dialog();
	$dialog->setStyles($settings_style);



***



### [setContent](_#setContent)
Setup the content of the title and the main content to display.


#### Description
	void = setContent ( array $params )


#### Parameters
*	`title`

	Dialog title which will be shown right after the Icon.

*	`message`

	Dialog message which will be shown inside of the dialog.

*	`buttons`

	An array which each inner array reflects a button (`title` and `action`), the maximum amount of buttons is `5`. More buttons will be ignored.


#### Example
	// Setup title
	$title = 'Initiating payment from server';

	// Setup message
	$message = 'Are you sure to pay 100.000 to "undef.de"?';

	// Build the buttons
	$buttons = array(
		array(
			'title'		=> 'Yes',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Confirm',
		),
		array(
			'title'		=> 'No',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Cancel',
		),
		array(
			'title'		=> 'Abort',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Cancel',
		),
	);

	// Setup content
	$settings_content = array(
		'title'			=> $title,
		'message'		=> $message,
		'buttons'		=> $buttons,
	);

	// Create the Dialog
	$dialog = new Dialog();
	$dialog->setContent($settings_content);



***



### [send](_#send)
Build, store and send the Dialog to the Player.


#### Description
	void = send ( Class Player object $player, boolean $hideclick )


#### Parameters
*	`$player`

	[Class Player](/development/classes/player.php) object

*	`$hideclick`

	If set to `false`, then the Dialog stays open after the Player clicks on it.
	Default value is `true`.


#### Example
	// Setup the styles
	$settings_style = array(
		'textcolor'		=> '09FF',
		'icon'			=> 'Icons64x64_1,ToolLeague1',
	);

	// Setup title
	$title = 'Initiating payment from server';

	// Setup message
	$message = 'Are you sure to pay 100.000 to "undef.de"?';

	// Build the buttons
	$buttons = array(
		array(
			'title'		=> 'Yes',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Confirm',
		),
		array(
			'title'		=> 'No',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Cancel',
		),
		array(
			'title'		=> 'Abort',
			'action'	=> 'PluginDonate?Action=Payout&Answer=Cancel',
		),
	);

	// Setup content
	$settings_content = array(
		'title'			=> $title,
		'message'		=> $message,
		'buttons'		=> $buttons,
	);

	// Create the Dialog
	$dialog = new Dialog();
	$dialog->setStyles($settings_style);
	$dialog->setContent($settings_content);
	$dialog->send($player, false);
