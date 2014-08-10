<?php
/*
 * Class: Window
 * ~~~~~~~~~~~~~
 * » Provides a comfortable, configurable styled Manialink window.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-10
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
 *  - includes/core/windowlist.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Window {
	public $layout;
	public $settings;
	public $content;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($unique_manialink_id = false) {
		global $aseco;

		// Empty content by default
		$this->content = array(
			'title'		=> '',
			'data'		=> array(),
			'page'		=> 0,
			'maxpage'	=> 0,
		);

		// Setup defaults
		$this->layout = array(
			'position' => array(
				'x' => -102.00001,
				'y' => 57.28125,
				'z' => 23,
			),
			'main' => array(
				'background' => array(
					'style'		=> 'Bgs1InRace',
					'substyle'	=> 'BgTitle2',
					'color'		=> '0018',
				),
			),
			'title' => array(
				'background' => array(
					'style'		=> 'Bgs1InRace',
					'substyle'	=> 'BgTitle3_3',
				),
				'icon' => array(
					'style'		=> 'Icons64x64_1',
					'substyle'	=> 'ToolLeague1',
				),
				'textcolor'	=> '09FF',
			),
			'column' => array(
				'background' => array(
					'style'		=> 'BgsPlayerCard',
					'substyle'	=> 'BgRacePlayerName',
				),
			),
		);

		$this->settings = array(
			'id'			=> 'TheWindowFromClassWindow',
			'timeout'		=> 0,
			'hideclick'		=> false,
			'columns'		=> 2,
			'widths'		=> array(),			// Inner columns
			'halign'		=> array(),			// Inner columns
			'bgcolors'		=> array(),			// RGBA
			'textcolors'		=> array(),			// RGBA
		);

		if ($unique_manialink_id === true) {
			// Generate unique ID
			$this->settings['id'] = $this->generateManialinkId();
		}
		else if ($unique_manialink_id !== false) {
			// Use given ID
			$this->settings['id'] = $unique_manialink_id;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function send ($player, $timeout = 0, $hideclick = false) {
		global $aseco;

		$aseco->windows->send($this, $player, $timeout, $hideclick);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setContent ($title, $data) {
		global $aseco;

		$this->content['title'] = $aseco->handleSpecialChars($aseco->formatColors($title));
		$this->content['data'] = $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setColumns ($param = array()) {

		// Check for min. and max. values
		if (isset($param['columns']) && $param['columns']) {
			if ($param['columns'] < 1) {
				$param['columns'] = 1;
			}
			else if ($param['columns'] > 6) {
				$param['columns'] = 6;
			}
			$this->settings['columns'] = $param['columns'];
		}

		// Make sure there is min. 1 alignment
		if (isset($param['halign']) && count($param['halign']) > 0) {
			$this->settings['halign'] = $param['halign'];
		}

		// Make sure there is min. 1 width
		if (isset($param['widths']) && count($param['widths']) > 0) {
			$this->settings['widths'] = $param['widths'];
		}

		// Make sure there is min. 1 background color
		if (isset($param['bgcolors']) && count($param['bgcolors']) > 0) {
			$this->settings['bgcolors'] = $param['bgcolors'];
		}

		// Make sure there is min. 1 text color
		if (isset($param['textcolors']) && count($param['textcolors']) > 0) {
			$this->settings['textcolors'] = $param['textcolors'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setLayoutBackground ($param = array()) {

		if (isset($param['color']) && $param['color']) {
			$this->layout['main']['background']['color'] = trim($param['color']);
		}
		if (isset($param['background']) && $param['background']) {
			list($this->layout['main']['background']['style'], $this->layout['main']['background']['substyle']) = explode(',', $param['background']);
			$this->layout['main']['background']['style'] = trim($this->layout['main']['background']['style']);
			$this->layout['main']['background']['substyle'] = trim($this->layout['main']['background']['substyle']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setLayoutTitle ($param = array()) {

		if (isset($param['textcolor']) && $param['textcolor']) {
			$this->layout['title']['textcolor'] = trim($param['textcolor']);
		}
		if (isset($param['background']) && $param['background']) {
			list($this->layout['title']['background']['style'], $this->layout['title']['background']['substyle']) = explode(',', $param['background']);
			$this->layout['title']['background']['style'] = trim($this->layout['title']['background']['style']);
			$this->layout['title']['background']['substyle'] = trim($this->layout['title']['background']['substyle']);
		}
		if (isset($param['icon']) && $param['icon']) {
			list($this->layout['title']['icon']['style'], $this->layout['title']['icon']['substyle']) = explode(',', $param['icon']);
			$this->layout['title']['icon']['style'] = trim($this->layout['title']['icon']['style']);
			$this->layout['title']['icon']['substyle'] = trim($this->layout['title']['icon']['substyle']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function generateManialinkId () {

		$pool = array_merge(
			range('0', '9'),
			range('a', 'z'),
			range('A', 'Z')
		);
		shuffle($pool);

		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float) $sec + ((float) $usec * 100000));

		$id = array();
		for ($i = 1; $i <= 32; $i++) {
			$id[] = $pool[mt_rand(0, count($pool)-1)];
		}

		return implode('', $id);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildColumns () {
		global $aseco;

		// Total width
		$frame_width = 187.5;

		// Build column background
		$xml = '<frame posn="7.8 -12.1875 0.01">';
		$outer_gap = 2.5;
		$column_width = (($frame_width - (($this->settings['columns'] - 1) * $outer_gap)) / $this->settings['columns']);
		foreach (range(0, ($this->settings['columns'] - 1)) as $i) {
			$xml .= '<quad posn="'. ($i * ($column_width + $outer_gap)) .' 1.5 0.02" sizen="'. $column_width .' 87.9" style="'. $this->layout['column']['background']['style'] .'" substyle="'. $this->layout['column']['background']['substyle'] .'"/>';
		}
		$xml .= '</frame>';


		// Include rows, if there is some data
		if (count($this->content['data']) > 0) {
			$xml .= '<frame posn="8.95 -11.4 0.02">';
			$xml .= '<format textsize="1" textcolor="FFF"/>';

			$entries = 0;
			$row = 0;
			$offset = 0;
			$inner_gap = 0.625;
			for ($i = ($this->content['page'] * ($this->settings['columns'] * 25)); $i < (($this->content['page'] * ($this->settings['columns'] * 25)) + ($this->settings['columns'] * 25)); $i ++) {

				// Is there a entry?
				if ( !isset($this->content['data'][$i]) ) {
					break;
				}
				$item = $this->content['data'][$i];

				$innercol = 0;
				$last_element_width = 0;
				foreach ($item as $value) {
					$inner_width = ($column_width - $outer_gap) - ((count($item) - 1) * $inner_gap);
					$element_width = (($inner_width / 100) * $this->settings['widths'][$innercol]);

					// Setup background <quad...>
					if (count($this->settings['bgcolors']) > 0) {
						$xml .= '<quad posn="'. ($last_element_width + $offset) .' -'. (3.47 * $row) .' 0.03" sizen="'. $element_width .' 3.188" bgcolor="'. ((isset($this->settings['bgcolors'][$innercol])) ? $this->settings['bgcolors'][$innercol] : end($this->settings['bgcolors']) ) .'"/>';
					}

					// Setup <label...>
					$textcolor	= ((isset($this->settings['textcolors'][$innercol])) ? $this->settings['textcolors'][$innercol] : end($this->settings['textcolors']) );
					$sizew		= (($element_width - ($inner_gap/2)) + (($element_width - ($inner_gap/2)) / 100 * 10));		// Add +10% of width because of scale="0.9"
					$posx		= (($inner_gap/2) + $last_element_width + $offset);
					$posy		= -(3.47 * $row + 1.45);
					if (isset($this->settings['halign'][$innercol]) && strtolower($this->settings['halign'][$innercol]) == 'right') {
						$posx = $posx + ($sizew - $inner_gap);
						$xml .= '<label posn="'. $posx .' '. $posy .' 0.04" sizen="'. $sizew .' 3.188" halign="right" valign="center" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->normalizeString($value) .'"/>';
					}
					else {
						$xml .= '<label posn="'. $posx .' '. $posy .' 0.04" sizen="'. $sizew .' 3.188" valign="center" scale="0.9" textcolor="'. $textcolor .'" text="'. $this->normalizeString($value) .'"/>';
					}
					$last_element_width += $element_width + $inner_gap;
					$innercol ++;
				}
				$row ++;
				$entries ++;

				// Check last row, setup next column
				if ($row >= 25) {
					$offset += (($frame_width + $outer_gap) / $this->settings['columns']);
					$row = 0;
				}

				// Break if max. amount of entries reached
				if ($entries >= (25 * $this->settings['columns'])) {
					break;
				}
			}
			$xml .= '</frame>';
		}

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildButtons () {

		$totalentries			= count($this->content['data']);
		$this->content['maxpage']	= ceil($totalentries / ($this->settings['columns'] * 25));

		// Previous button
		$buttons = '';
		if ($this->content['page'] > 0) {
			// First
			$buttons .= '<quad posn="16.875 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPageFirst" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="17.875 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="18.35 -2.25 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="18.875 -3 0.15" sizen="1 3.1875" bgcolor="CCCF"/>';

			// Previous (-5)
			$buttons .= '<quad posn="25.125 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPagePrevTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="26.125 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="24.75 -2.34375 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
			$buttons .= '<quad posn="26.5 -2.34375 0.15" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';

			// Previous (-1)
			$buttons .= '<quad posn="33.375 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPagePrev" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="34.375 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="33.875 -2.25 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowLeft2"/>';
		}
		else {
			// First
			$buttons .= '<quad posn="16.875 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-5)
			$buttons .= '<quad posn="25.125 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Previous (-1)
			$buttons .= '<quad posn="33.375 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}
		$buttons .= '</frame>';

		// Next button (display only if more pages to display)
		$buttons .= '<frame posn="130.125 -99.9375 0.04">';
		if (($this->content['page'] + 1) < $this->content['maxpage']) {
			// Next (+1)
			$buttons .= '<quad posn="41.625 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPageNext" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="42.625 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="42.125 -2.34375 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Next (+5)
			$buttons .= '<quad posn="49.875 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPageNextTwo" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="50.875 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="49.5 -2.34375 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="51.25 -2.34375 0.15" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';

			// Last
			$buttons .= '<quad posn="58.125 -1.875 0.12" sizen="7.5 5.625" action="ClassWindowPageLast" style="Icons64x64_1" substyle="Maximize"/>';
			$buttons .= '<quad posn="59.125 -2.625 0.13" sizen="5.25 3.9375" bgcolor="000F"/>';
			$buttons .= '<quad posn="57.75 -2.34375 0.14" sizen="6.25 4.875" style="Icons64x64_1" substyle="ShowRight2"/>';
			$buttons .= '<quad posn="62.5 -3 0.15" sizen="1 3.1875" bgcolor="CCCF"/>';
		}
		else {
			// Next (+1)
			$buttons .= '<quad posn="41.625 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Next (+5)
			$buttons .= '<quad posn="49.875 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';

			// Last
			$buttons .= '<quad posn="58.125 -2.15625 0.12" sizen="7 5.0625" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		}

		return $buttons;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPageinfo () {
		return '';
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWindow () {

		// Placeholder:
		// - %content%
		// - %page%
		// - %buttons%
		// - %maniascript%

		// Begin Window
		$xml = '<manialink id="'. $this->settings['id'] .'" name="ClassWindow" version="1">';
		$xml .= '<frame posn="'. implode(' ', $this->layout['position']) .'" id="ClassWindow">';	// BEGIN: Window Frame
		$xml .= '<quad posn="-0.5 0.375 0.01" sizen="204.5 110.625" style="'. $this->layout['main']['background']['style'] .'" substyle="'. $this->layout['main']['background']['substyle'] .'" id="ClassWindowBody" ScriptEvents="1"/>';
		$xml .= '<quad posn="4.5 -7.6875 0.02" sizen="194.25 93.5625" bgcolor="'. $this->layout['main']['background']['color'] .'"/>';

		// Header Line
		$xml .= '<quad posn="-1.5 1.125 0.02" sizen="206.5 11.25" style="'. $this->layout['title']['background']['style'] .'" substyle="'. $this->layout['title']['background']['substyle'] .'"/>';
		$xml .= '<quad posn="-1.5 1.125 0.03" sizen="206.5 11.25" style="'. $this->layout['title']['background']['style'] .'" substyle="'. $this->layout['title']['background']['substyle'] .'" id="ClassWindowTitle" ScriptEvents="1"/>';

		// Title
		$xml .= '<quad posn="4.5 -1.3125 0.04" sizen="8 6" style="'. $this->layout['title']['icon']['style'] .'" substyle="'. $this->layout['title']['icon']['substyle'] .'"/>';
		$xml .= '<label posn="13.75 -3.1875 0.04" sizen="188.5 0" textsize="2" scale="0.9" textcolor="'. $this->layout['title']['textcolor'] .'" text="'. $this->content['title'] .'"/>';

		// Minimize Button
		$xml .= '<frame posn="183.5 -0.28125 0.05">';
		$xml .= '<quad posn="0 0 0.01" sizen="11.25 8.4375" style="Icons64x64_1" substyle="ArrowUp" id="ClassWindowMinimize" ScriptEvents="1"/>';
		$xml .= '<quad posn="3 -2.25 0.02" sizen="5 3.75" bgcolor="EEEF"/>';
		$xml .= '<label posn="5.825 -4.5 0.03" sizen="15 0" halign="center" valign="center" textsize="3" textcolor="000F" text="$O-"/>';
		$xml .= '</frame>';

		// Close Button
		$xml .= '<frame posn="191.75 -0.28125 0.05">';
		$xml .= '<quad posn="0 0 0.01" sizen="11.25 8.4375" style="Icons64x64_1" substyle="ArrowUp" id="ClassWindowClose" ScriptEvents="1"/>';
		$xml .= '<quad posn="3 -2.25 0.02" sizen="5 3.75" bgcolor="EEEF"/>';
		$xml .= '<quad posn="1.75 -1.3125 0.03" sizen="7.75 5.8125" style="Icons64x64_1" substyle="Close"/>';
		$xml .= '</frame>';

		// Content
		$xml .= '%content%';

		// Page info
		$xml .= '<frame posn="110.125 -99.9375 0.04">';
		$xml .= '%page%';
		$xml .= '</frame>';

		// Navigation Buttons
		$xml .= '<frame posn="130.125 -99.9375 0.04">';
		$xml .= '%buttons%';
		$xml .= '</frame>';

		// Footer
		$xml .= '</frame>';				// END: Window Frame
		$xml .= '%maniascript%';			// Maniascript
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildManiascript () {
$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.uaseco.org
 * Class:	window.class.php
 * License:	GPLv3
 * ----------------------------------
 */
Void WipeOut (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = 0.0;
	declare Real EndPosnY = 0.0;
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale > 0.0) {
		Container.RelativePosition.X += (PosnDistanceX / 20);
		Container.RelativePosition.Y += (PosnDistanceY / 20);
		Container.RelativeScale -= 0.05;
		yield;
	}
	Container.Unload();
}
Void Minimize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = {$this->layout['position']['x']};
	declare Real EndPosnY = {$this->layout['position']['y']};
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale > 0.2) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale -= 0.05;
		yield;
	}
}
Void Maximize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = {$this->layout['position']['x']};
	declare Real EndPosnY = {$this->layout['position']['y']};
	declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition.X);
	declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition.Y);

	while (Container.RelativeScale < 1.0) {
		Container.RelativePosition.X += (PosnDistanceX / 16);
		Container.RelativePosition.Y += (PosnDistanceY / 16);
		Container.RelativeScale += 0.05;
		yield;
	}
}
main () {
	declare CMlControl Container <=> (Page.GetFirstChild("ClassWindow") as CMlFrame);
	declare CMlQuad Quad;
	declare Boolean MoveWindow = False;
	declare Boolean IsMinimized = False;
	declare Real MouseDistanceX = 0.0;
	declare Real MouseDistanceY = 0.0;

	while (True) {
		yield;
		if (MoveWindow == True) {
			Container.RelativePosition.X = (MouseDistanceX + MouseX);
			Container.RelativePosition.Y = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			foreach (Event in PendingEvents) {
				if (Event.ControlId == "ClassWindowTitle") {
					MouseDistanceX = (Container.RelativePosition.X - MouseX);
					MouseDistanceY = (Container.RelativePosition.Y - MouseY);
					MoveWindow = True;
				}
			}
		}
		else {
			MoveWindow = False;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ClassWindowClose") {
						WipeOut("ClassWindow");
					}
					else if ( (Event.ControlId == "ClassWindowMinimize") && (IsMinimized == False) ) {
						Minimize("ClassWindow");
						IsMinimized = True;
					}
					else if ( (Event.ControlId == "ClassWindowBody") && (IsMinimized == True) ) {
						Maximize("ClassWindow");
						IsMinimized = False;
					}
				}
			}
		}
	}
}
--></script>
EOL;
		return $maniascript;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function normalizeString ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $S (shadow)
		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[SHWILON]/i', '', $string);

		return $aseco->handleSpecialChars($string);
	}
}

?>
