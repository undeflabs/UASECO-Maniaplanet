# Class XmlParser
###### Documentation of includes/core/XmlParser.class.php

Builds an easy structured array out of a xml file, element names will be the keys and the data the values.



## [Methods](_#Methods)


### [xmlToArray](_#xmlToArray)
Parses a XML structure into an array.


#### Description
	array = parse ( mixed $source, [ boolean $isfile, boolean $utf8enc ] )


#### Parameters
*	`$source`

	A string of a XML structur or a filename of a XML file.

*	`$isfile`

	If passed and set to `true`, then treat `$source` as file.
	Default value is `true`.

*	`$utf8enc`

	If passed and set to `false`, then treat XML structur or XML file not as UTF-8 encoded.
	Default value is `true`.


#### Example
	$config = $aseco->xml->xmlToArray('config/my_plugin.xml', true, true);



***



### [arrayToXml](_#arrayToXml)
Parses an array into an XML structure.


#### Description
	string = arrayToXml ( array $data )


#### Parameters
*	`$data`

	A data array.


#### Example
	$xml = $aseco->xml->arrayToXml($data);
