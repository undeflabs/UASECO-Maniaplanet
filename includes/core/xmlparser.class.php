<?php
/*
 * Class: XmlParser
 * ~~~~~~~~~~~~~~~~
 * » Builds an easy structured array out of a xml file, element names will be the
 *   keys and the data the values.
 * » Based upon xmlparser.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-19
 * Copyright:	2014 by undef.de
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
 * Dependencies:
 *  - none
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class XmlParser {
	private $data;
	private $struct;
	private $parser;
	private $stack;
	private $utf8enc;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Parses a XML structure into an array.
	 */
	public function xmlToArray ($source, $isfile = true, $utf8enc = true) {

		// clear last results
		$this->stack = array();
		$this->struct = array();
		$this->utf8enc = $utf8enc;

		// create the parser
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'openTag', 'closeTag');
		xml_set_character_data_handler($this->parser, 'tagData');

		// load the xml file
		if ($isfile) {
			$this->data = @file_get_contents($source);
		}
		else {
			$this->data = $source;
		}

		// escape '&' characters
		$this->data = str_replace('&', '<![CDATA[&]]>', $this->data);

		// parse xml file
		$parsed = xml_parse($this->parser, $this->data);

		// display errors
		if (!$parsed) {
			$code = xml_get_error_code($this->parser);
			$err = xml_error_string($code);
			$line = xml_get_current_line_number($this->parser);
			trigger_error("[XML Error $code] $err on line $line", E_USER_WARNING);
			return false;
		}
		return $this->struct;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function openTag ($parser, $name, $attributes) {
		$this->stack[] = $name;
		$this->struct[$name] = '';
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function tagData ($parser, $data) {
		// This way it makes sure '0' is not interpreted as 'false' and got handled too
		if (trim($data) != '') {
			$index = $this->stack[count($this->stack)-1];
			// use raw, do not decode '+' into space
			if ($this->utf8enc) {
				$this->struct[$index] .= rawurldecode($data);
			}
			else {
				$this->struct[$index] .= utf8_decode(rawurldecode($data));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function closeTag ($parser, $name) {
		if (count($this->stack) > 1) {
			$from = array_pop($this->stack);
			$to = $this->stack[count($this->stack)-1];
			$this->struct[$to][$from][] = $this->struct[$from];
			unset($this->struct[$from]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Parses an array into an XML structure.
	 */
	public function arrayToXml ($array) {
		$xmlstring = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$xmlstring .= $this->parseArrayElements($array);
		return $xmlstring;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function parseArrayElements ($array, $opt_tag = '') {

		// read each element of the array
		for ($i = 0; $i < count($array); $i++) {

			// check if array is associative
			if ( is_numeric(key($array)) ) {
				$xml .= '<'. $opt_tag .'>';
				if ( is_array(current($array)) ) {
					$xml .= $this->parseArrayElements(current($array), key($array));
				}
				else {
					// use raw, don't encode space into '+'
					$xml .= rawurlencode(utf8_encode(current($array)));
				}
				$xml .= '</'. $opt_tag .'>';
			}
			else {
				if ( is_array(current($array)) ) {
					$xml .= $this->parseArrayElements(current($array), key($array));
				}
				else {
					$xml .= '<'. key($array) .'>';
					// use raw, don't encode space into '+'
					$xml .= rawurlencode(utf8_encode(current($array)));
					$xml .= '</'. key($array) .'>';
				}
			}
			next($array);
		}
		return $xml;
	}
}

?>
