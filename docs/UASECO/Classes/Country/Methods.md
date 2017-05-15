# Class Country
###### Documentation of includes/core/country.class.php

Provides lists of Countries and IOC codes, and converter methods.



## [Methods](_#Methods)


### [countryToIoc](_#countryToIoc)
Returns a IOC code from a given Country


#### Description
	string = countryToIoc ( string $country )


#### Parameters
*	`$country`

	A string from with a Country name.


#### Example
	$ioc = $aseco->country->countryToIoc('Netherlands');


#### Return Values
	NED



***



### [iocToCountry](_#iocToCountry)
Returns a Country from a given IOC code

#### Description
	string = iocToCountry ( string $IOC )


#### Parameters
*	`$IOC`

	A string from a IOC code.


#### Example
	$country = $aseco->country->iocToCountry('NED');


#### Return Values
	Netherlands
