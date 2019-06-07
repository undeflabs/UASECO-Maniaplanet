# Class BaseClass
###### Documentation of includes/core/baseclass.class.php


***


Structure for all classes, extend this class to build your own one.



***



## [Methods](_#Methods)


### [setAuthor](_#setAuthor)
Set the Author for the current class.


#### Description
	 void setAuthor ( [ $name ] )


#### Example
	public function __construct () {
		$this->setAuthor('undef.de');
	}



***



### [getAuthor](_#getAuthor)
Returns the Author, which was set with [`setAuthor`](#setAuthor).


#### Description
	string = getAuthor ( void )


#### Example
	$aseco->locales->getAuthor();


#### Return Values
	undef.de



***



### [setCoAuthors](_#setCoAuthors)
Adds the given Co-Authors to a list of Co-Authors of this class. If you add a Co-Author more then once, then other occurrence will be removed.


#### Description
	void = setCoAuthors ( string $author1 [, string $author2] )


#### Example
	$aseco->locales->setCoAuthors('undef.de', 'askuri');



***



### [getCoAuthors](_#getCoAuthors)
Returns an array of the Co-Author, which was set with [`setCoAuthors`](#setCoAuthors).


#### Description
	array = getCoAuthors ( void )


#### Example
	$coauthors = $aseco->locales->getCoAuthors();


#### Return Values
	array(
		'undef.de',
		'askuri',
	)



***



### [setVersion](_#setVersion)
Set the version of this class. Please use this structure:

	2.3.5
	│ │ └───────── Maintenance
	│ └─────────── Minor
	└───────────── Major


#### Description
	void = setVersion ( string $version )


#### Example
	$aseco->locales->setVersion('2.3.5');



***



### [getVersion](_#getVersion)
Returns an array of the Co-Author, which was set with [`setVersion`](#setVersion).


#### Description
	string = getVersion ( void )


#### Example
	$version = $aseco->locales->getVersion();


#### Return Values
	2.3.5



***



### [setBuild](_#setBuild)
Set the version of this class. Please use this structure:

	2017-04-30 10:53:31

The time part can be omitted.


#### Description
	void = setVersion ( string $version )


#### Example
	$aseco->locales->setBuild('2017-04-30 10:53:31');



***



### [getBuild](_#getBuild)
Returns the build, which was set with [`setBuild`](#setBuild).


#### Description
	string = getBuild ( void )


#### Example
	$build = $aseco->locales->getBuild();


#### Return Values
	2017-04-30 10:53:31



***



### [setCopyright](_#setCopyright)
Set the copyright notice of this class.


#### Description
	void = setCopyright ( string $copyright )


#### Example
	$aseco->locales->setCopyright('2014 - 2017 by undef.de');



***



### [getCopyright](_#getCopyright)
Returns the copyright, which was set with [`setCopyright`](#setCopyright).


#### Description
	string = getCopyright ( void )


#### Example
	$copyright = $aseco->locales->getCopyright();


#### Return Values
	2014 - 2017 by undef.de



***



### [setDescription](_#setDescription)
Set the description of this class.


#### Description
	void = setDescription ( string $description )


#### Example
	$aseco->locales->setDescription('Provides multilanguage support.');



***



### [getDescription](_#getDescription)
Returns the copyright, which was set with [`setDescription`](#setDescription).


#### Description
	string = getDescription ( void )


#### Example
	$description = $aseco->locales->getDescription();


#### Return Values
	Provides multilanguage support.



***



### [getClassname](_#getClassname)
Returns the name of the class.

#### Description
	string = getClassname ( void )


#### Example
	$aseco->locales->getClassname();


#### Return Values
	Locales
