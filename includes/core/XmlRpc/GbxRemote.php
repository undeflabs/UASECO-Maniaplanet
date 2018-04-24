<?php
/*
 * Class: GbxRemote
 * ~~~~~~~~~~~~~~~~
 * » ManiaPlanet dedicated server Xml-RPC client.
 * » Based upon GbxRemote.php from 'https://github.com/maniaplanet/dedicated-server-api',
 *   version from 2017-02-24 and changed to UASECO requirements.
 *
 *   2014-10-03: Renamed query() into singlequery(), changed parameter for addCall() and query()
 *   2014-10-10: Set constant MAX_REQUEST_SIZE to real 512 kb limit (thanks reaby)
 *   2015-01-28: Added a try/catch at query() to catch all exception (possibly forgotten by Plugin authors)
 *   2015-02-18: Added ignore list for error messages for ListMethods
 *   2017-03-23: Changed [UASECO Exception] logging to full output
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2017-04-02
 * Copyright:	2014 - 2017 by undef.de
 * ----------------------------------------------------------------------------------
 *
 * LICENSE: http://www.gnu.org/licenses/lgpl.html LGPL License 3
 *
 * ----------------------------------------------------------------------------------
 *
 * Dependencies:
 *  - includes/core/XmlRpc/Base64.php
 *  - includes/core/XmlRpc/Request.php
 *  - includes/core/XmlRpc/FaultException.php
 *
 */

	require_once('includes/core/XmlRpc/Base64.php');
	require_once('includes/core/XmlRpc/Request.php');
	require_once('includes/core/XmlRpc/FaultException.php');

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class GbxRemote {
//	const MAX_REQUEST_SIZE	= 0x200000;				// 2,097,152 bytes
	const MAX_REQUEST_SIZE	= 0x080000;				//   524,288 bytes
	const MAX_RESPONSE_SIZE	= 0x400000;				// 4,194,304 bytes

	public static $received;
	public static $sent;

	private $socket;
	private $requestHandle;
	private $readTimeout		= array('sec' => 5, 'usec' => 0);
	private $writeTimeout		= array('sec' => 5, 'usec' => 0);
	private $callbacksBuffer	= array();
	private $multicallBuffer	= array();
	private $lastNetworkActivity	= 0;

	private $ignore_error_messages	= array(
		'Flow control not enabled.',					// ManualFlowControlProceed
		'Not in script mode.',						// TriggerModeScriptEvent, TriggerModeScriptEventArray
		'Connection not initialized',					// *ToLogin
		'Login unknown.',						// *ToLogin, GetDetailedPlayerInfo
		'Start index out of bound.',					// GetMapList
	);

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		$this->requestHandle	= (int) 0x80000000;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __destruct () {
		$this->terminate();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Change timeouts
	 * @param int $read read timeout (in ms), 0 to leave unchanged
	 * @param int $write write timeout (in ms), 0 to leave unchanged
	 */
	public function setTimeouts ($read = 0, $write = 0) {
		if ($read) {
			$this->readTimeout['sec'] = (int) ($read / 1000);
			$this->readTimeout['usec'] = ($read % 1000) * 1000;
		}
		if ($write) {
			$this->writeTimeout['sec'] = (int) ($write / 1000);
			$this->writeTimeout['usec'] = ($write % 1000) * 1000;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @return int Network idle time in seconds
	 */
	public function getIdleTime () {
		$this->assertConnected();
		return time() - $this->lastNetworkActivity;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 * @throws TransportException
	 */
	public function connect ($host = '127.0.0.1', $port = 5000, $timeout = 5) {
		$this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$this->socket) {
			throw new TransportException('Cannot open socket', TransportException::NOT_INITIALIZED);
		}

		stream_set_read_buffer($this->socket, 0);
		stream_set_write_buffer($this->socket, 0);

		// handshake
		$header = $this->read(15);
		if ($header === false) {
			$this->onIoFailure(sprintf('during handshake (%s)', socket_strerror(socket_last_error($this->socket))));
		}

		extract(unpack('Vsize/a*protocol', $header));
		/** @var $size int */
		/** @var $protocol string */
		if ($size != 11 || $protocol != 'GBXRemote 2') {
			throw new TransportException('Wrong protocol header', TransportException::WRONG_PROTOCOL);
		}
		$this->lastNetworkActivity = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function terminate () {
		if ($this->socket) {
			fclose($this->socket);
			$this->socket = null;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $method
	 * @param mixed[] $args
	 * @return mixed
	 * @throws MessageException
	 */
	public function query () {
		global $aseco;

		$args = func_get_args();
		$method = array_shift($args);

		if (isset($aseco->server->gameinfo->mode) && $aseco->server->gameinfo->mode == Gameinfo::CUP && ($method === 'RestartMap' || $method === 'NextMap')) {
			$aseco->console('[GbxRemote] Method [{1}] does not work while running Modescript "Cup.Script.txt"!', $method);
		}
		else {
			try {
				return $this->singlequery($method, $args);
			}
			catch (Exception $exception) {
				$message = $exception->getMessage();
				if (!in_array($message, $this->ignore_error_messages)) {
					$aseco->console_text('[UASECO Exception] Error returned: "'. $message .'" ['. $exception->getCode() .'] at '. get_class($this) .'::query() for method "'. $method .'" with arguments:');
					$aseco->dump($args);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $method
	 * @param mixed[] $args
	 */
	public function addCall () {
		$args = func_get_args();
		$method = array_shift($args);

		$this->multicallBuffer[] = array(
			'methodName'	=> $method,
			'params'	=> $args
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $method
	 * @param mixed[] $args
	 * @return mixed
	 * @throws MessageException
	 */
	private function singlequery ($method, $args = array()) {

		$this->assertConnected();
		$xml = Request::encode($method, $args);

		if(strlen($xml) > self::MAX_REQUEST_SIZE - 8) {
			if ($method != 'system.multicall' || count($args[0]) < 2) {
				throw new MessageException('Request too large', MessageException::REQUEST_TOO_LARGE);
			}

			$mid = count($args[0]) >> 1;
			$res1 = $this->singlequery('system.multicall', array(array_slice($args[0], 0, $mid)));
			$res2 = $this->singlequery('system.multicall', array(array_slice($args[0], $mid)));
			return array_merge($res1, $res2);
		}

		$this->writeMessage($xml);
		return $this->flush(true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @return mixed
	 */
	public function multiquery () {
		switch(count($this->multicallBuffer)) {
			case 0:
				return array();

			case 1:
				$call = array_shift($this->multicallBuffer);
				return array($this->singlequery($call['methodName'], $call['params']));

			default:
				$result = $this->singlequery('system.multicall', array($this->multicallBuffer));
				foreach ($result as &$value) {
					if (isset($value['faultCode'])) {
						$value = FaultException::create($value['faultString'], $value['faultCode']);
					}
					else {
						$value = $value[0];
					}
				}
				$this->multicallBuffer = array();
				return $result;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @return mixed[]
	 */
	public function getCallbacks () {
		$this->assertConnected();
		$this->flush();
		$cb = $this->callbacksBuffer;
		$this->callbacksBuffer = array();
		return $cb;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @throws TransportException
	 */
	private function assertConnected () {
		if (!$this->socket) {
			throw new TransportException('Connection not initialized', TransportException::NOT_INITIALIZED);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param bool $waitResponse
	 * @return mixed
	 * @throws FaultException
	 */
	private function flush ($waitResponse = false) {
		$r = array($this->socket);
		while($waitResponse || @stream_select($r, $w, $e, 0) > 0) {
			list($handle, $xml) = $this->readMessage();
			list($type, $value) = Request::decode($xml);
			switch($type) {
				case 'fault':
					throw FaultException::create($value['faultString'], $value['faultCode']);

				case 'response':
					if ($handle == $this->requestHandle) {
						return $value;
					}
					break;

				case 'call':
					$this->callbacksBuffer[] = $value;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @return mixed[]
	 * @throws TransportException
	 * @throws MessageException
	 */
	private function readMessage () {
		$header = $this->read(8);
		if ($header === false) {
			$this->onIoFailure('while reading header');
		}

		extract(unpack('Vsize/Vhandle', $header));
		/** @var $size int */
		/** @var $handle int */
		if ($size == 0 || $handle == 0) {
			throw new TransportException('Incorrect header', TransportException::PROTOCOL_ERROR);
		}

		if ($size > self::MAX_RESPONSE_SIZE) {
			throw new MessageException('Response too large', MessageException::RESPONSE_TOO_LARGE);
		}

		$data = $this->read($size);
		if ($data === false) {
			$this->onIoFailure('while reading data');
		}

		$this->lastNetworkActivity = time();
		return array($handle, $data);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $xml
	 * @throws TransportException
	 */
	private function writeMessage ($xml) {
		if ($this->requestHandle == (int) 0xffffffff) {
			$this->requestHandle = (int) 0x80000000;
		}
		$data = pack('V2', strlen($xml), ++$this->requestHandle).$xml;
		if (!$this->write($data)) {
			$this->onIoFailure('while writing');
		}
		$this->lastNetworkActivity = time();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param int $size
	 * @return boolean|string
	 */
	private function read ($size) {
		@stream_set_timeout($this->socket, $this->readTimeout['sec'], $this->readTimeout['usec']);

		$data = '';
		while(strlen($data) < $size) {
			$buf = @fread($this->socket, $size - strlen($data));
			if ($buf === '' || $buf === false) {
				return false;
			}
			$data .= $buf;
		}

		self::$received += $size;
		return $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $data
	 * @return boolean
	 */
	private function write ($data) {
		@stream_set_timeout($this->socket, $this->writeTimeout['sec'], $this->writeTimeout['usec']);
		self::$sent += strlen($data);

		while(strlen($data) > 0) {
			$written = @fwrite($this->socket, $data);
			if ($written === 0 || $written === false) {
				return false;
			}

			$data = substr($data, $written);
		}
		return true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * @param string $when
	 * @throws TransportException
	 */
	private function onIoFailure ($when) {
		$meta = stream_get_meta_data($this->socket);
		if ($meta['timed_out']) {
			throw new TransportException('Connection timed out '.$when, TransportException::TIMED_OUT);
		}
		throw new TransportException('Connection interrupted '.$when, TransportException::INTERRUPTED);
	}
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class TransportException extends Exception {
	const NOT_INITIALIZED	= 1;
	const INTERRUPTED	= 2;
	const TIMED_OUT		= 3;
	const WRONG_PROTOCOL	= 4;
	const PROTOCOL_ERROR	= 5;
}

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class MessageException extends Exception {
	const REQUEST_TOO_LARGE		= 1;
	const RESPONSE_TOO_LARGE	= 2;
}
