# Class WebRequest
###### Documentation of includes/core/webrequest.class.php


***


Provides asynchronous and synchronous communication for HTTP GET-, POST- and HEAD-Requests.



## [Methods](_#Methods)


### [GET](_#GET)
Handles HTTP-GET requests.


#### Description
	string = GET ( array $params )


#### Parameters
*	`url`

	__Required__: String with an HTTP-URL which should be requested.

*	`callback`

	Optional/__Required__: The callback which should be called when the request is finished (only required for asynchronous requests).

*	`sync`

	Optional: Switch for asynchronous and synchronous requests, default value is `false`.

*	`timeout`

	Optional: Over all timeout in seconds after the request should give up, default value is `20`.

*	`timeout_dns`

	Optional: DNS request timeout in seconds after that the request should give up, default value is `5`.

*	`timeout_connect`

	Optional: Connection timeout in seconds after that the request should give up, default value is `5`.

*	`tries`

	Optional: Amount of tries until the request should give up, default value is `10`.

*	`max_redirect`

	Optional: Follow max. this amount of redirects, default value is `50`.

*	`user_agent`

	Optional: String which will be used as USER-AGENT within the request.

*	`extra_headers`

	Optional: Array which holds extra headers to add to the request, e.g. `array('X-ServerLogin: labs01','X-ServerNation: DEU')`.


#### Example
	public function example () {
		try {
			// Grab a text file
			$params = array(
				'url'		=> 'http://www.uaseco.org/uptodate/current_release.txt',
				'callback'	=> array(array($this, 'handleWebrequest'), $login),
			);
			$result = $aseco->webrequest->GET($params);
		}
		catch (Exception $exception) {
			$aseco->console('[UpToDate] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
			return false;
		}
	}

	public function handleWebrequest ($request, $login = false) {
		// Handle the request answer here...
	}



***





### [POST](_#POST)
Handles HTTP-POST requests.


#### Description
	string = POST ( array $params )


#### Parameters
*	`url`

	__Required__: String with an HTTP-URL which should be requested.

*	`data`

	__Required__: Data for the request body of a POST-Request, only required at POST-Request).

*	`callback`

	Optional/__Required__: The callback which should be called when the request is finished (only required for asynchronous requests).

*	`sync`

	Optional: Switch for asynchronous and synchronous requests, default value is `false`.

*	`timeout`

	Optional: Over all timeout in seconds after the request should give up, default value is `20`.

*	`timeout_dns`

	Optional: DNS request timeout in seconds after that the request should give up, default value is `5`.

*	`timeout_connect`

	Optional: Connection timeout in seconds after that the request should give up, default value is `5`.

*	`tries`

	Optional: Amount of tries until the request should give up, default value is `10`.

*	`max_redirect`

	Optional: Follow max. this amount of redirects, default value is `50`.

*	`user_agent`

	Optional: String which will be used as USER-AGENT within the request.

*	`extra_headers`

	Optional: Array which holds extra headers to add to the request, e.g. `array('X-ServerLogin: labs01','X-ServerNation: DEU')`.


#### Example
	public function example () {
		try {
			// Grab a text file
			$params = array(
				'url'		=> 'http://www.uaseco.org/example.php',
				'data'		=> $data,						// e.g. CSV-, XML-Data or an Image
				'callback'	=> array(array($this, 'handleWebrequest'), $login),
			);
			$result = $aseco->webrequest->POST($params);
		}
		catch (Exception $exception) {
			$aseco->console('[UpToDate] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
			return false;
		}
	}

	public function handleWebrequest ($request, $login = false) {
		// Handle the request answer here...
	}



***



### [HEAD](_#HEAD)
Handles HTTP-HEAD requests.


#### Description
	string = HEAD ( array $params )


#### Parameters
*	`url`

	__Required__: String with an HTTP-URL which should be requested.

*	`timeout`

	Optional: Over all timeout in seconds after the request should give up, default value is `20`.

*	`timeout_dns`

	Optional: DNS request timeout in seconds after that the request should give up, default value is `5`.

*	`timeout_connect`

	Optional: Connection timeout in seconds after that the request should give up, default value is `5`.

*	`tries`

	Optional: Amount of tries until the request should give up, default value is `10`.

*	`max_redirect`

	Optional: Follow max. this amount of redirects, default value is `50`.

*	`user_agent`

	Optional: String which will be used as USER-AGENT within the request.

*	`extra_headers`

	Optional: Array which holds extra headers to add to the request, e.g. `array('X-ServerLogin: labs01','X-ServerNation: DEU')`.


#### Example
	public function example () {
		try {
			// Does the file exists?
			$params = array(
				'url'		=> 'http://www.uaseco.org/uptodate/current_release.txt',
			);
			$result = $aseco->webrequest->HEAD($params);
		}
		catch (Exception $exception) {
			$aseco->console('[UpToDate] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
			return false;
		}
	}
