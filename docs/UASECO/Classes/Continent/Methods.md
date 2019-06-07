# Class Continent
###### Documentation of includes/core/continent.class.php


***


Provides lists of Continents and converter methods.



## [Methods](_#Methods)


### [continentToAbbreviation](_#continentToAbbreviation)
Returns a abbreviation from a given Continent


#### Description
	string = continentToAbbreviation ( string $continent )


#### Parameters
*	`$continent`

	A string from with a Continent name.

#### Example
	$country = $aseco->continent->continentToAbbreviation('Europe');


#### Return Values
	EU


> The return value is an empty string, if the Continent was not found.



***



### [abbreviationToContinent](_#abbreviationToContinent)
Returns a Continent from a given abbreviation


#### Description
	string = abbreviationToContinent ( string $abbreviation )


#### Parameters
*	`$abbreviation`

	A Continent abbreviation.

#### Example
	$continent = $aseco->continent->abbreviationToContinent('NA');


#### Return Values
	North America


> The return value is an empty string, if the abbreviation was not found.
