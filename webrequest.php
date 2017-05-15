<?php
/*
 * WebRequestWorker
 * ~~~~~~~~~~~~~~~~
 * » Worker script for the WebRequest Class
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Copyright:	2017 by undef.de
 * Version:	1.0.0
 * Build:	2017-05-15
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


	$worker = new WebRequestWorker();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WebRequestWorker {
	private $path = 'cache'. DIRECTORY_SEPARATOR .'webrequest'. DIRECTORY_SEPARATOR;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		// Store the current PID
		file_put_contents($this->path .'webrequest.pid', getmypid(), LOCK_EX);

		while (true) {
			$starttime = microtime(true);

			if ($dir = opendir($this->path)) {
				while ($entry = readdir($dir)) {
					if ($entry == '.' || $entry == '..') {
						continue;
					}
					if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && strpos($entry, '.job') !== false) {

						$file = str_replace('.job', '', $this->path.DIRECTORY_SEPARATOR.$entry);
						if (rename($this->path.DIRECTORY_SEPARATOR.$entry, $file.'.working')) {

							// Ok, no other Worker has taken this job...
							$request = json_decode(file_get_contents($file.'.working'));
							$response = $this->request($request);

							file_put_contents($file.'.working', $response, LOCK_EX);

							rename($file.'.working', $file.'.done');
						}
					}

					// Got a kill signal?
					if (is_file($this->path.DIRECTORY_SEPARATOR.$entry) === true && $entry == 'worker.suicide') {
						unlink($this->path .'webrequest.pid');
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

		if (strtoupper($request->method) == 'POST') {
			// HTTP POST
			$request->data = urlencode($request->data);
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
}

?>
