<?php
/*
 * WebRequestWorker
 * ~~~~~~~~~~~~~~~~
 * » Worker script for the WebRequest Class
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Copyright:	2017 - 2019 by undef.de
 * Version:	1.0.2
 * Build:	2019-06-07
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

	define('CRLF', PHP_EOL);
	mb_internal_encoding('UTF-8');

	if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN') {
		define('OPERATING_SYSTEM', 'WINDOWS');
	}
	else {
		define('OPERATING_SYSTEM', 'POSIX');
	}


	$worker = new WebRequestWorker();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WebRequestWorker {
	private $path = 'cache'. DIRECTORY_SEPARATOR .'webrequest'. DIRECTORY_SEPARATOR;
	private $logfile;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		// Error function, report errors in a regular way.
		$this->logfile['handle'] = false;
		set_error_handler(array($this, 'customErrorHandler'));
		register_shutdown_function(array($this, 'customFatalErrorShutdownHandler'));

		// Setup logfile
		$this->setupLogfile();

		// Store the current PID
		$pid = getmypid();
		$pid_file = 'webrequest.pid.'.$pid;
		$suicide_file = 'worker.suicide.'.$pid;
		file_put_contents($this->path . $pid_file, $pid, LOCK_EX);


		$this->logMessage('[WebRequest] started pid '. $pid .'...');

		while (true) {
			$starttime = microtime(true);

			if ($dir = opendir($this->path)) {
				while ($entry = readdir($dir)) {
					if ($entry === '.' || $entry === '..') {
						continue;
					}
					if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.job') !== false) {

						$file = str_replace('.job', '', $this->path.DIRECTORY_SEPARATOR.$entry);
						if (@rename($this->path.DIRECTORY_SEPARATOR.$entry, $file.'.working')) {

							// Ok, no other Worker has taken this job...
							$request = json_decode(file_get_contents($file.'.working'));
							$response = $this->request($request);

							file_put_contents($file.'.working', $response, LOCK_EX);

							@rename($file.'.working', $file.'.done');
						}
					}

					// Got a kill signal?
					if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && $entry === $suicide_file) {
						$this->logMessage('[WebRequest] suicide pid '. $pid .'...');
						unlink($this->path.$suicide_file);
						unlink($this->path.$pid_file);
						exit(0);
					}
				}
			}

			// Reduce CPU usage if main loop has time left
			$endtime = microtime(true);
			$delay = 200000 - ($endtime - $starttime) * 1000000;
			if ($delay > 0) {
				usleep($delay);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function request ($request) {

		if (strtoupper($request->method) === 'POST') {
			// HTTP POST
			$request->data = urlencode($request->data);
			$stream_context = stream_context_create(
				array(
					'ssl'		=> array(
						'verify_peer'		=> true,
						'verify_peer_name'	=> true,
						'allow_self_signed'	=> true,
						'SNI_enabled'		=> true,
					),
					'http'		=> array(
						'ignore_errors'		=> false,
						'timeout'		=> $request->timeout,
						'follow_location'	=> true,
						'max_redirects'		=> $request->max_redirect,
						'protocol_version'	=> 1.1,
						'user_agent'		=> $request->user_agent,
						'method'		=> $request->method,
						'header'		=> array_merge(
							array(
								'Content-Type: application/x-www-form-urlencoded',
								'Connection: close',
								'Content-Length: '. strlen($request->data),
							),
							$request->extra_headers
						),
						'content'		=> $request->data,
					),
					'ftp'		=> array(
						'overwrite'		=> true,
						'resume_pos'		=> 0,
					),
				)
			);
		}
		else {
			// HTTP GET
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
						'timeout'		=> $request->timeout,
						'follow_location'	=> true,
						'max_redirects'		=> $request->max_redirect,
						'protocol_version'	=> 1.1,
						'user_agent'		=> $request->user_agent,
						'method'		=> 'GET',
						'header'		=> $request->extra_headers,
					),
					'ftp'		=> array(
						'overwrite'		=> true,
						'resume_pos'		=> 0,
					),
				)
			);
		}

		// Send request
		$request->url = str_replace(' ','%20', $request->url);
		$response = file_get_contents($request->url, false, $stream_context);
		if ($response !== false) {
			$headers = '';
			foreach ($http_response_header as $line) {
				$headers .= $line ."\n";
			}

			return $headers ."\n". $response;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Writes a logfile of all output messages in daily chunks inside the logs/ directory.
	public function logMessage ($text) {

		// If new daily file, close old logfile
		if (!file_exists($this->logfile['file']) && $this->logfile['handle']) {
			fclose($this->logfile['handle']);
			$this->logfile['handle'] = false;
		}

		if (!$this->logfile['handle']) {
			$this->logfile['handle'] = fopen($this->logfile['file'], 'wb+');
		}
		fwrite($this->logfile['handle'], '['. date('Y-m-d H:i:s') .'] '. $text . CRLF);
		if (OPERATING_SYSTEM === 'POSIX') {
			chmod($this->logfile['file'], 0666);
		}
//		else if (OPERATING_SYSTEM === 'WINDOWS') {
//			// Echo to console on Windows
//			echo str_replace('»', '>', $text);
//		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Setup the current logfile
	public function setupLogfile () {

		// Create logs/ directory if needed
		$dir = './logs';
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		// Define daily file inside dir
		$this->logfile['file'] = $dir . DIRECTORY_SEPARATOR . date('Y-m-d') .'-webrequest-current.log';

		// On stop or crash replace old logfile
		if (file_exists($this->logfile['file']) && !$this->logfile['handle']) {
			@rename($this->logfile['file'], $dir . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s') .'-webrequest.log');
		}

		// Check for logfiles from the past
		if ($dh = opendir($dir)) {
			$list = array();
			while (($logfile = readdir($dh)) !== false) {
				if ( is_file($dir . DIRECTORY_SEPARATOR . $logfile) ) {
					$lastmodified = filemtime($dir . DIRECTORY_SEPARATOR . $logfile);

					if ($lastmodified < (time() - 60*60*24*14)) {
						// Delete all logfiles older then 14 days
						unlink($dir . DIRECTORY_SEPARATOR . $logfile);
					}

					$result = preg_match('/-webrequest-current\.log$/', $logfile);
					if ($result !== false && $result >= 1) {
						// Rename all logfiles marked with "-webrequest-current.log" and older then one hour
						@rename(
							$dir . DIRECTORY_SEPARATOR . $logfile,
							$dir . DIRECTORY_SEPARATOR . date('Y-m-d-H-i-s', $lastmodified) .'-webrequest.log'
						);
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

	public function customErrorHandler ($errno, $errstr, $errfile, $errline) {

		// Check for error suppression
		if (error_reporting() === 0) {
			return;
		}

		switch ($errno) {
			case E_USER_ERROR:
				$message = "[WebRequest Fatal Error] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);

				// Throw 'shutting down' event
				$this->releaseEvent('onShutdown', null);

				// Make sure the Dedicated-Server have the control
				try {
					$this->client->query('ManualFlowControlEnable', false);

					try {
						$this->client->query('ManualFlowControlProceed');

						// Clear all ManiaLinks
						try {
							$this->client->query('SendHideManialinkPage');
						}
						catch (Exception $exception) {
							$this->console('[WebRequest] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - customErrorHandler(): SendHideManialinkPage');
						}

					}
					catch (Exception $exception) {
						$this->console('[WebRequest] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - customErrorHandler(): ManualFlowControlProceed');
					}
				}
				catch (Exception $exception) {
					$this->console('[WebRequest] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - customErrorHandler(): ManualFlowControlEnable');
				}


				if (function_exists('xdebug_get_function_stack')) {
					$this->logMessage(print_r(xdebug_get_function_stack()), true);
				}
				die();
				break;

			case E_USER_WARNING:
				$message = "[WebRequest Warning] $errstr". CRLF;
				$this->logMessage($message);
				break;

			case E_ERROR:
				$message = "[PHP Fatal Error] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			case E_WARNING:
				$message = "[PHP Warning] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			case E_NOTICE:
				$message = "[PHP Notice] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				break;

			default:
//				if (strpos($errstr, 'Function call_user_method') !== false) {
//					break;
//				}
				$message = "[PHP $errno] $errstr on line $errline in file $errfile". CRLF;
				$this->logMessage($message);
				// do nothing, only treat known errors
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function customFatalErrorShutdownHandler () {

		$last_error = error_get_last();
		if ($last_error['type'] === E_ERROR) {
			$this->customErrorHandler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

?>
