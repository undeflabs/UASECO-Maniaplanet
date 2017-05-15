# Class RecordList
###### Documentation of includes/core/recordlist.class.php

Manages a list of records, add records to the list and remove them.



## [Methods](_#Methods)


### [count](_#count)
Returns the amount of records.


#### Description
	int = count ( void )


#### Example
	$amount = $aseco->server->records->count();


#### Return Values
	31



***



### [clear](_#clear)
Clears the current record list.


#### Description
	void = clear ( void )


#### Example
	$aseco->server->records->clear();



***



### [setMaxRecords](_#setMaxRecords)
Setup the max. amount of records that should be handled and stored into the database.


#### Description
	void = setMaxRecords ( int $limit )


#### Parameters
*	`$limit`

	The max. records amount


#### Example
	$aseco->server->records->setMaxRecords(50);



***



### [getMaxRecords](_#getMaxRecords)
Returns the max. records limit.


#### Description
	int = getMaxRecords ( void )


#### Example
	$limit = $aseco->server->records->getMaxRecords();


#### Return Values
	50



***



### [getRecord](_#getRecord)
Returns a record by the given rank.


#### Description
	Class Record object = getRecord ( int $rank )


#### Parameters
*	`$rank`

	A given rank to get the record from.


#### Example
	$record = $aseco->server->records->getRecord();



***



### [setRecord](_#setRecord)
Set a record at a (rank) position.


#### Description
	boolean = setRecord ( int $rank, Class Record object $record )


#### Parameters
*	`$rank`

	A rank to store the record at

*	`$record`

	A Class Record object to store at the given rank.


#### Example
	$rank = 1;
	$record = new Record();
	// Add record data like "score", "player"... here

	$aseco->server->records->setRecord($rank, $record);


#### Return Values
	true



***



### [moveRecord](_#moveRecord)
Move a record from one to an other position.


#### Description
	void = moveRecord ( int $from, int $to )


#### Parameters
*	`$from`

	A rank to get the record that should be moved.

*	`$to`

	A rank of the where the record should be stored.


#### Example
	$from = 10;
	$to = 8;
	$aseco->server->records->moveRecord($from, $to);



***



### [addRecord](_#addRecord)
Add a record to a position at the record list.


#### Description
	boolean = addRecord ( Class Record object $record, xxx $rank )

On success true is returned, otherwise false.


#### Parameters
*	`$record`

	A Class Record object to add.

*	`$rank`

	If passed, put the record to this position. Otherwise add the record to the end of the record list. If $rank is higher then ->records->max_records, this record is rejected.


#### Example
	$rank = 2;
	$record = new Record();
	// Add record data like "score", "player"... here

	$result = $aseco->server->records->addRecord($record, $rank);


#### Return Values
	true



***



### [deleteRecord](_#deleteRecord)
Delete a record from the record list.


#### Description
	boolean = deleteRecord ( int $rank )

On success true is returned, otherwise false.


#### Parameters
*	`$rank`

	Remove the record at this given position.


#### Example
	$aseco->server->records->deleteRecord(5);


#### Return Values
	true


