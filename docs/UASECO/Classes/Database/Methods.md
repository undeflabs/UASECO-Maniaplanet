# Class Database
###### Documentation of includes/core/database.class.php

This Class extends the [Class mysqli](http://php.net/manual/en/class.mysqli.php), so all the Properties and Methods this Class provides are available too.



## [Methods](_#Methods)


### [select_one](_#select_one)
Returns the first row of the results from your SQL query.


#### Description
	mixed = select_one ( string $sql )

If null is returned and [`$aseco->db->errno`](http://php.net/manual/en/mysqli.error.php) is not `0`, then your SQL query was wrong. Otherwise your SQL query does not match anything.


#### Parameters
*	`$sql`

	A sql select query

#### Example
	$query = "
	SELECT
		`PlayerId`,
		`Donations`
	FROM `player_extra`
	WHERE `Login` = 'puennt_ennel'
	LIMIT 1;
	";

	$row = $aseco->db->select_one($query);

	echo $row['PlayerId'];


#### Return Values
	1188



***



### [select_all](_#select_all)
Returns the all rows of the results from your SQL query.


#### Description
	mixed = select_all ( string $sql )

If null is returned and [`$aseco->db->errno`](http://php.net/manual/en/mysqli.error.php) is not `0`, then your SQL query was wrong. Otherwise your SQL query does not match anything.


#### Parameters
*	`$sql`

	A sql select query

#### Example
	$query = "
	SELECT
		`PlayerId`,
		`Donations`
	FROM `player_extra`
	WHERE `Login` NOT ''
	LIMIT 100;
	";

	$row = $aseco->db->select_all($query);

	foreach ($row as $item) {
		echo $item['PlayerId'];
	}


#### Return Values
	1188
	147
	247
	6465
	8721
	6467
	...



***



### [quote](_#quote)
Returns the escaped and quoted given string with `'`

#### Description
	string = quote ( string $string )

Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.


#### Parameters
*	`$string`

	A string to be escaped and quoted with '


#### Example
	$string = "Tricky Nickname '''";
	$quoted = $aseco->db->quote($string);


#### Return Values

	'Tricky Nickname \'\'\''



***



### [escape](_#escape)
Returns the escaped given string, this is an alias of [`$aseco->db->real_escape_string()`](http://php.net/manual/en/mysqli.real-escape-string.php).


#### Description
	string = escape ( string $string )

Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.


#### Parameters
*	`$string`

	A string to be escaped


#### Example
	$string = "Tricky Nickname '''";
	$escaped = $aseco->db->escape($string);


#### Return Values
	Tricky Nickname \'\'\'



***



### [list_table_columns](_#list_table_columns)
Returns an array of table columns from the given table.


#### Description
	array = list_table_columns ( string $table )

If the given table does not exists, a empty array is returned.


#### Parameters
*	`$table`

	A string of the wanted table


#### Example
	$columns = $aseco->db->list_table_columns('players_extra');


#### Return Values
	array(
	  'PlayerId',
	  'Cps',
	  'DediCps',
	  'Donations',
	  'Style',
	  'Panels',
	  'PanelBG',
	);




***



### [lastid](_#lastid)
Returns the last inserted id, this is an alias of [`$aseco->db->insert_id`](http://php.net/manual/en/mysqli.insert-id.php).


#### Description
	mixed = lastid ( void )


#### Example
	$id = $aseco->db->lastid();


#### Return Values
	1547



***



### [errmsg](_#errmsg)
Returns a formated and concated version of [`$aseco->db->errno`](http://php.net/manual/en/mysqli.errno.php) and [`$aseco->db->error`](http://php.net/manual/en/mysqli.error.php).


#### Description
	string = errmsg ( void )


#### Example
	$errmsg = $aseco->db->errmsg();


#### Return Values
	(1193) Unknown system variable 'a'



***



### [disconnect](_#disconnect)
Calls mysqli->close(), but checks the connection first.


#### Description
	void = disconnect ( void )


#### Example
	$aseco->db->connection();
