<?php
/*
 * Class: WebRequest
 * ~~~~~~~~~~~~~~~~~
 * » Provides asynchronous and synchronous communication for HTTP GET-, POST- and HEAD-Requests.
 *
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----------------------------------------------------------------------------------
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WebRequest extends BaseClass {
	private $timeout;
	private $max_redirect;

	private $spool;
	private $path;
	private $commands;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2017-05-17');
		$this->setCopyright('2016 - 2017 by undef.de');
		$this->setDescription('Provides asynchronous and synchronous communication for HTTP GET-, POST- and HEAD-Requests.');

		$this->timeout		= 20;
		$this->max_redirect	= 50;

		$this->spool		= array();
		$this->path		= 'cache'. DIRECTORY_SEPARATOR .'webrequest'. DIRECTORY_SEPARATOR;

		// Make sure the directory exists
		@mkdir($this->path, 0755, true);


		// Check for instances of the worker process "webrequest.php"
		$found = false;
		if ($dir = opendir($this->path)) {
			while ($entry = readdir($dir)) {
				if ($entry === '.' || $entry === '..') {
					continue;
				}
				if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, 'webrequest.pid.') !== false) {
					$found = true;
				}
			}
		}

		if ($found === false) {
//			if (function_exists('exec')) {
//				exec($aseco->php .' webrequest.php');
//			}
			trigger_error('[WebRequest] Could not found any worker processes of "webrequest.php", can not life without some!', E_USER_ERROR);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __destruct () {

		$worker_pids = array();
		if ($dir = opendir($this->path)) {
			while ($entry = readdir($dir)) {
				if ($entry === '.' || $entry === '..') {
					continue;
				}
				if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, 'webrequest.pid.') !== false) {
					$worker_pids[] = str_replace('webrequest.pid.', '', $entry);
				}
			}
		}

		// Let the worker(s) "webrequest.php" kill himselfs
		foreach ($worker_pids as $pid) {
			file_put_contents($this->path.'worker.suicide.'.$pid, '');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function HEAD ($params) {

		// Setup method
		$params['method'] = 'HEAD';

		if (!isset($params['url']) || empty($params['url'])) {
			throw new WebRequestException('No required URL given for a request!', WebRequestException::NO_URL_GIVEN_FOR_REQUEST);
		}
		if (!isset($params['user_agent']) || empty($params['user_agent'])) {
			$params['user_agent'] = get_class($this) .'/'. $this->getVersion() .' '. USER_AGENT;
		}
		if (!isset($params['timeout']) || empty($params['timeout'])) {
			$params['timeout'] = $this->timeout;
		}
		if (!isset($params['max_redirect']) || empty($params['max_redirect'])) {
			$params['max_redirect'] = $this->max_redirect;
		}
		if (!isset($params['extra_headers']) || empty($params['extra_headers'])) {
			$params['extra_headers'] = array();
		}

		$params['user_agent'] = $this->_normalize_user_agent($params['user_agent']);
		$stream_context = stream_context_create(
			array(
				'ssl'		=> array(
					'verify_peer'		=> false,
					'verify_peer_name'	=> false,
					'allow_self_signed'	=> true,
					'SNI_enabled'		=> true,
				),
				'http'		=> array(
					'ignore_errors'		=> false,
					'timeout'		=> $params['timeout'],
					'follow_location'	=> true,
					'max_redirects'		=> $params['max_redirect'],
					'protocol_version'	=> 1.1,
					'user_agent'		=> $params['user_agent'],
					'method'		=> $params['method'],
					'header'		=> $params['extra_headers'],
				),
			)
		);

		// Send sync request
		$params['url'] = str_replace(' ','%20', $params['url']);
		$response = file_get_contents($params['url'], false, $stream_context);
		if ($response !== false) {

			$headers = '';
			foreach ($http_response_header as $line) {
				$headers .= $line ."\r\n";
			}

			// Create new construct
			$request = new WebRequestConstruct($params);

			// Parse the response
			return $this->_parse($request, $headers, $response);
		}
		throw new WebRequestException('The given URL could not be found!', WebRequestException::GIVEN_URL_NOT_FOUND);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function GET ($params) {

		// Setup method
		$params['method'] = 'GET';

		if (!isset($params['url']) || empty($params['url'])) {
			throw new WebRequestException('No required URL given for a request!', WebRequestException::NO_URL_GIVEN_FOR_REQUEST);
		}
		if (!isset($params['user_agent']) || empty($params['user_agent'])) {
			$params['user_agent'] = get_class($this) .'/'. $this->getVersion() .' '. USER_AGENT;
		}
		if (!isset($params['timeout']) || empty($params['timeout'])) {
			$params['timeout'] = $this->timeout;
		}
		if (!isset($params['max_redirect']) || empty($params['max_redirect'])) {
			$params['max_redirect'] = $this->max_redirect;
		}
		if (!isset($params['extra_headers']) || empty($params['extra_headers'])) {
			$params['extra_headers'] = array();
		}


		$params['user_agent'] = $this->_normalize_user_agent($params['user_agent']);
		if (isset($params['sync']) && $params['sync'] === true) {
			$stream_context = stream_context_create(
				array(
					'ssl'		=> array(
						'verify_peer'		=> false,
						'verify_peer_name'	=> false,
						'allow_self_signed'	=> true,
						'SNI_enabled'		=> true,
					),
					'http'		=> array(
						'ignore_errors'		=> false,
						'timeout'		=> $params['timeout'],
						'follow_location'	=> true,
						'max_redirects'		=> $params['max_redirect'],
						'protocol_version'	=> 1.1,
						'user_agent'		=> $params['user_agent'],
						'method'		=> $params['method'],
						'header'		=> $params['extra_headers'],
					),
				)
			);

			// Send sync request
			$params['url'] = str_replace(' ','%20', $params['url']);
			$response = file_get_contents($params['url'], false, $stream_context);
			if ($response !== false) {

				$headers = '';
				foreach ($http_response_header as $line) {
					$headers .= $line ."\r\n";
				}

				// Create new construct
				$request = new WebRequestConstruct($params);

				// Parse the response
				return $this->_parse($request, $headers, $response);
			}
			throw new WebRequestException('The given URL could not be found!', WebRequestException::GIVEN_URL_NOT_FOUND);
		}
		else {
			if (!isset($params['callback']) && $params['callback'] !== null) {
				throw new WebRequestException('No required callback given for a asynchronos request!', WebRequestException::NO_CALLBACK_GIVEN_ON_ASYNC_REQUEST);
			}
			else if ($params['callback'] !== null && (!is_callable($params['callback']) && !is_callable($params['callback'][0]))) {
				throw new WebRequestException('Given callback is not callable!', WebRequestException::GIVEN_CALLBACK_IS_NOT_CALLABLE);
			}

			// Create new construct
			$request = new WebRequestConstruct($params);

			// Add to the async pool
			$this->spool[$request->id] = $request;
			return $request->id;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function POST ($params) {

		// Setup method
		$params['method'] = 'POST';

		if (!isset($params['url']) || empty($params['url'])) {
			throw new WebRequestException('No required URL given for a request!', WebRequestException::NO_URL_GIVEN_FOR_REQUEST);
		}
		if (!isset($params['data']) || empty($params['data'])) {
			throw new WebRequestException('No required POST data given!', WebRequestException::NO_POST_DATA_GIVEN);
		}
		if (!isset($params['user_agent']) || empty($params['user_agent'])) {
			$params['user_agent'] = get_class($this) .'/'. $this->getVersion() .' '. USER_AGENT;
		}
		if (!isset($params['timeout']) || empty($params['timeout'])) {
			$params['timeout'] = $this->timeout;
		}
		if (!isset($params['max_redirect']) || empty($params['max_redirect'])) {
			$params['max_redirect'] = $this->max_redirect;
		}
		if (!isset($params['extra_headers']) || empty($params['extra_headers'])) {
			$params['extra_headers'] = array();
		}


		$params['user_agent'] = $this->_normalize_user_agent($params['user_agent']);
		if (isset($params['sync']) && $params['sync'] === true) {
			$params['data'] = urlencode($params['data']);
			$stream_context = stream_context_create(
				array(
					'ssl'		=> array(
						'verify_peer'		=> false,
						'verify_peer_name'	=> false,
						'allow_self_signed'	=> true,
						'SNI_enabled'		=> true,
					),
					'http'		=> array(
						'ignore_errors'		=> false,
						'timeout'		=> $params['timeout'],
						'follow_location'	=> true,
						'max_redirects'		=> $params['max_redirect'],
						'protocol_version'	=> 1.1,
						'user_agent'		=> $params['user_agent'],
						'method'		=> $params['method'],
						'header'		=> array_merge(
							array(
								'Content-Type: application/x-www-form-urlencoded',
								'Connection: close',
								'Content-Length: '. strlen($params['data']),
							),
							$params['extra_headers']
						),
						'content'		=> $params['data'],
					),
				)
			);

			// Send sync request
			$params['url'] = str_replace(' ','%20', $params['url']);
			$response = file_get_contents($params['url'], false, $stream_context);
			if ($response !== false) {

				$headers = '';
				foreach ($http_response_header as $line) {
					$headers .= $line ."\r\n";
				}

				// Create new construct
				$request = new WebRequestConstruct($params);

				// Parse the response
				return $this->_parse($request, $headers, $response);
			}
			throw new WebRequestException('The given URL could not be found!', WebRequestException::GIVEN_URL_NOT_FOUND);
		}
		else {
			if (!isset($params['callback']) && $params['callback'] !== null) {
				throw new WebRequestException('No required callback given for a asynchronos request!', WebRequestException::NO_CALLBACK_GIVEN_ON_ASYNC_REQUEST);
			}
			else if ($params['callback'] !== null && (!is_callable($params['callback']) && !is_callable($params['callback'][0]))) {
				throw new WebRequestException('Given callback is not callable!', WebRequestException::GIVEN_CALLBACK_IS_NOT_CALLABLE);
			}

			// Create new construct
			$request = new WebRequestConstruct($params);

			// Add to the async pool
			$this->spool[$request->id] = $request;
			return $request->id;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function update () {

		if (count($this->spool) > 0) {
			foreach ($this->spool as $id => $request) {
				if ($request->sent === false) {

					file_put_contents($this->path . $request->id .'.new', json_encode($request));
					rename(
						$this->path . $request->id .'.new',
						$this->path . $request->id .'.job'
					);

					$request->sent = true;
				}
				else if ($request->sent === true) {
					if (file_exists($this->path . $request->id .'.done')) {
						$content = file_get_contents($this->path . $request->id .'.done');

						// Remove the response file
						@unlink($this->path . $request->id .'.done');

						if ($request->callback !== null) {
							// Parse the response
							$request = $this->_parse($request, $content, false);

							// Call the callback function
							if (is_callable($request->callback)) {
								call_user_func($request->callback, $request);
							}
							else if (is_callable($request->callback[0])) {
								call_user_func($request->callback[0], $request, $request->callback[1]);
							}
						}

						// Destroy the request
						$request = null;
						unset($this->spool[$id]);
					}
				}
			}
		}

		// Cleanup
		if ($dir = opendir($this->path)) {
			while ($entry = readdir($dir)) {
				if ($entry === '.' || $entry === '..') {
					continue;
				}
				if (@is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.new') !== false) {
					if (@filemtime($this->path.DIRECTORY_SEPARATOR.$entry) + 86400 < time()) {
						@unlink($this->path.DIRECTORY_SEPARATOR.$entry);
					}
				}
				if (@is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.job') !== false) {
					if (@filemtime($this->path.DIRECTORY_SEPARATOR.$entry) + 86400 < time()) {
						@unlink($this->path.DIRECTORY_SEPARATOR.$entry);
					}
				}
				if (@is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.working') !== false) {
					if (@filemtime($this->path.DIRECTORY_SEPARATOR.$entry) + 86400 < time()) {
						@unlink($this->path.DIRECTORY_SEPARATOR.$entry);
					}
				}
				if (@is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.done') !== false) {
					if (@filemtime($this->path.DIRECTORY_SEPARATOR.$entry) + 86400 < time()) {
						@unlink($this->path.DIRECTORY_SEPARATOR.$entry);
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _parse ($request, $header, $content = false) {

		if ($content === false) {
			// webrequest.php
			$data = explode("\n\n", $header);		// Split header and content
			$request->response['content'] = (isset($data[1]) ? $data[1] : '');

			// Purge memory
			$content = null;
			$header = null;
			$data[1] = null;
		}
		else {
			// file_get_contents()
			$data[0] = $header;
			$request->response['content'] = $content;

			// Purge memory
			$content = null;
			$header = null;
		}

		// Parse headers
		foreach (explode("\n", $data[0]) as $line) {
			if (substr($line, 0, 5) === 'HTTP/' || substr($line, 0, 6) === 'HTTPS/') {
				$parts = explode(' ', $line);
				$request->response['header']['protocol']	= $parts[0];
				$request->response['header']['code']		= (int)$parts[1];
				$request->response['header']['code_text']	= $parts[2];
			}
			else {
				$item = explode(': ', $line);
				if (strtolower($item[0]) === 'last-modified') {
					$request->response['header']['last_modified'] = $item[1];
				}
				else if (strtolower($item[0]) === 'content-type') {
					$request->response['header']['content_type'] = $item[1];
				}
			}
		}
		return $request;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function _normalize_user_agent ($ua) {
		return str_replace(
			array(
				'"',
				'`'
			),
			array(
				"'",
				''
			),
			$ua
		);
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WebRequestConstruct {
	public $timeout			= 20;
	public $max_redirect		= 50;

	public $id			= false;
	public $timestamp		= 0;
	public $method			= 'GET';
	public $url			= false;
	public $extra_headers		= array();
	public $data			= false;
	public $user_agent		= false;
	public $sent			= false;
	public $callback		= null;

	public $response		= array(
		'header'	 => array(
			'protocol'		=> '',
			'code'			=> 0,
			'code_text'		=> '',
			'last_modified'		=> 0,
			'content_type'		=> false,
		),
		'content'	=> false,
	);

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($params) {
		$this->timeout		= $params['timeout'];
		$this->max_redirect	= $params['max_redirect'];

		$this->id		= md5($params['method'] . str_replace(' ','%20', $params['url']) . microtime(false));
		$this->timestamp	= microtime(false);
		$this->method		= strtoupper($params['method']);
		$this->url		= str_replace(' ','%20', $params['url']);
		$this->extra_headers	= (isset($params['extra_headers']) ? $params['extra_headers'] : array());
		$this->data		= (isset($params['data']) ? $params['data'] : false);
		$this->callback		= (isset($params['callback']) ? $params['callback'] : null);
		$this->user_agent	= $params['user_agent'];
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WebRequestException extends Exception {
	const GIVEN_URL_NOT_FOUND			= 404;

	const NO_URL_GIVEN_FOR_REQUEST			= 501;
	const NO_POST_DATA_GIVEN			= 502;
	const NO_CALLBACK_GIVEN_ON_ASYNC_REQUEST	= 503;
	const GIVEN_CALLBACK_IS_NOT_CALLABLE		= 504;
}

?>
