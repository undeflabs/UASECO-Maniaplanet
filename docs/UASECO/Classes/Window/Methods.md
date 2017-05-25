# Class Window
###### Documentation of includes/core/window.class.php

Provides a comfortable, configurable styled Manialink window, with automatic handling of actions when a Player click on the pagination buttons.



## [Methods](_#Methods)


### [setStyles](_#setStyles)
Setup the style of the Window.


#### Description
	void = setStyles ( array $params )


#### Parameters
*	`textcolor`

	Color for the text in [RGBA](http://en.wikipedia.org/wiki/RGBA_color_space) format, e.g. `09FF`.

*	`seperatorcolor`

	Color for the seperator at the header in [RGBA](http://en.wikipedia.org/wiki/RGBA_color_space) format, e.g. `09FF`.

*	`icon`

	Icon for the header, choose one from the Manialink [styles](maniaplanet:///:styles).


#### Example
	// Setup the styles
	$settings_style = array(
		'textcolor'		=> '09FF',
		'seperatorcolor'	=> 'FFFF',
		'icon'			=> 'Icons64x64_1,ToolLeague1',
	);

	// Create the Window
	$window = new Window();
	$window->setStyles($settings_style);



***



### [setColumns](_#setColumns)
Setup the amount, width, alignment and text colors of the columns.


#### Description
	void = setColumns ( array $params )


#### Parameters
*	`columns`

	Setup the amount of outer columns the window should have, default is `1`.

*	`widths`

	The widths in percent of the inner columns of the `columns`.

*	`halign`

	Adjust the direction of each inner column, supported options are: `left` (default), `center` and `right`.

*	`textcolors`

	Setup for the inner columns text color, each inner column can have an own text color.

*	`heading`

	If set, then each inner column will get a heading with the given title.


#### Example
	$settings_columns = array(
		'columns'	=> 2,
		'widths'	=> array(25, 75),
		'halign'	=> array('left', 'center'),
		'textcolors'	=> array('FF5F', 'FFFF'),
		'heading'	=> array('Command', 'Description'),
	);

	// Create the Window
	$window = new Window();
	$window->setColumns($settings_columns);



***



### [setContent](_#setContent)
Setup the content of the title and the main content to display.


#### Description
	void = setContent ( array $params )


#### Parameters
*	`title`

	Window title which will be shown right after the Icon.

*	`mode`

	Mode for the window content, supported values are `columns` or `pages`.

*	`data`

	In mode `columns`: An array which each entry reflects a rows in a inner column.<br>
	In mode `pages`: An array which each entry reflects a complete build manialink page which should be displayed inside the window.

*	`add_background`

	Boolean value: Include a background for `pages`? The `columns` will get them by default!


#### Example

	// Build the data
	$data = array(
		'/help',	'Display help',
		'/plugins',	'Display Plugins',
		'/players',	'Display Players',
		'/list',	'Display Maps',
	);

	// Setup content
	$settings_content = array(
		'title'			=> 'Currently supported chat commands',
		'data'			=> $data,
		'mode'			=> 'columns',
	);

	// Create the Window
	$window = new Window();
	$window->setContent($settings_content);



***



### [setFooter](_#setFooter)
Setup the footer to display.


#### Description
	void = setFooter ( array $params )


#### Parameters
*	`about_title`

	Title which will be shown at the buttom left.

*	`about_link`

	Link for the `about_title`

*	`button_title`

	Title which will be shown at the center.

*	`button_link`

	Link for the `button_title`


#### Example

	// Setup footer
	$settings_footer = array(
		'about_title'		=> 'MANIA-KARMA/'. $this->getVersion(),
		'about_link'		=> 'http://www.mania-karma.com,
		'button_title'		=> 'MORE INFO ON MANIA-KARMA.COM',
		'button_link'		=> 'http://www.mania-karma.com/goto?uid='. $this->karma['data']['uid'],
	);

	// Create the Window
	$window = new Window();
	$window->setFooter($settings_footer);



***



### [send](_#send)
Build, store and send the Window to the Player.


#### Description
	void = send ( Class Player object $player, int $timeout, boolean $hideclick )


#### Parameters
*	`$player`

	[Class Player](/development/classes/player.php) object

*	`$timeout`

	A timeout after that the Window should be hidden from display.
	Default value is `0`.

*	`$hideclick`

	If set to `true`, then the Window is closed after the Player clicks on it.
	Default value is `false`.


#### Example
	// Setup the styles
	$settings_style = array(
		'textcolor'		=> '09FF',
		'seperatorcolor'	=> 'FFFF',
		'icon'			=> 'Icons64x64_1,ToolLeague1',
	);

	// Setup the columns
	$settings_columns = array(
		// Split Window into two (outer)columns
		'columns'	=> 2,

		// Each outer columns have two inner columns with 25% and 75% width
		'widths'	=> array(25, 75),

		// Left inner column text halign to 'left', the right to 'center'
		'halign'	=> array('left', 'center'),

		'textcolors'	=> array('FF5F', 'FFFF'),
		'bgcolors'	=> array('555F', '333F'),

		// Setup the headings
		'heading'	=> array('Command', 'Description'),
	);

	// Build the data
	$data = array(
		'/help',	'Display help',
		'/plugins',	'Display Plugins',
		'/players',	'Display Players',
		'/list',	'Display Maps',
	);

	// Setup content
	$settings_content = array(
		'title'			=> 'Currently supported chat commands',
		'data'			=> $data,
		'mode'			=> 'columns',
	);

	// Create the Window
	$window = new Window();
	$window->setStyles($settings_style);
	$window->setColumns($settings_columns);
	$window->setContent($settings_content);
	$window->send($player, 0, false);
