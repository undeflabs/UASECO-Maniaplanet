<?php
/*
 * Class: Dialog
 * ~~~~~~~~~~~~~
 * » Provides a comfortable, configurable styled Manialink dialog.
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

class Dialog extends BaseClass {
	public $layout;
	public $settings;
	public $content;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-05-31');
		$this->setCopyright('2017 by undef.de');
		$this->setDescription(new Message('class.dialog', 'dialog_description'));

		// Empty content by default
		$this->content = array(
			'title'				=> '',
			'message'			=> '',
			'buttons'			=> array(),
		);

		// Setup defaults
		$this->layout = array(
			'position' => array(
				'x' 			=> -52.5,
				'y' 			=> 28.64,
				'z' 			=> 30.0,
			),
			'position_minimize' => array(
				'x' 			=> -102.00001,
				'y' 			=> 57.28125,
			),
			'title' => array(
				'icon' => array(
					'style'		=> 'Icons64x64_1',
					'substyle'	=> 'TrackInfo',
				),
				'textcolor'		=> 'FFFFFFFF',
			),
			'backgrounds' => array(
				'main'			=> '032942F0',
				'title'			=> '000000AA',
				'title_hover'		=> '000000CC',
			),
		);

		$this->settings = array(
			'id'				=> 'TheDialogFromClassDialog',
			'timeout'			=> 0,
			'hideclick'			=> true,
			'stripcodes'			=> false,
		);

		// Generate unique ID
		$this->settings['id'] = $aseco->generateManialinkId();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function send ($player, $hideclick = true) {
		global $aseco;

		if (isset($player) && is_object($player) && $player instanceof Player && $player->id > 0) {

			// Concat all the elements
			$xml = str_replace(
				array(
					'%content%',
					'%buttons%',
					'%maniascript%',
				),
				array(
					$this->buildContent(),
					$this->buildButtons(),
					$this->buildManiascript(),
				),
				$this->buildWindow($player->login)
			);

			// Send to Player
			$aseco->sendManialink($xml, $player->login, 0, $hideclick);
		}
		else {
			if ($aseco->debug) {
				if ($player->id == 0) {
					$aseco->console('[ClassDialog] Ignoring the given Fakeplayer.');
				}
				else {
					$aseco->console('[ClassDialog] Given Player does not exists in the current PlayerList.');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setContent ($param = array()) {
		global $aseco;

		if (isset($param['title']) && $param['title']) {
			$this->content['title'] = trim($aseco->validateUTF8String($aseco->encodeEntities($aseco->formatColors($param['title']))));
		}
		if (isset($param['message']) && $param['message']) {
			$this->content['message'] = trim($aseco->validateUTF8String($aseco->encodeEntities($aseco->formatColors($param['message']))));
		}
		if (isset($param['buttons']) && is_array($param['buttons'])) {
			$amount = 0;
			foreach ($param['buttons'] as $button) {
				if ($amount < 5) {
					$this->content['buttons'][] = $button;
					$amount += 1;
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setStyles ($param = array()) {

		// Title
		if (isset($param['icon']) && $param['icon']) {
			list($this->layout['title']['icon']['style'], $this->layout['title']['icon']['substyle']) = explode(',', $param['icon']);
			$this->layout['title']['icon']['style'] = trim($this->layout['title']['icon']['style']);
			$this->layout['title']['icon']['substyle'] = trim($this->layout['title']['icon']['substyle']);
		}

		// Heading
		if (isset($param['textcolor']) && $param['textcolor']) {
			$this->layout['title']['textcolor'] = $param['textcolor'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildButtons () {

		// Previous buttons
		$xml = '<frame pos="11.85 -53" z-index="0.04">';

		$count = 0;
		foreach ($this->content['buttons'] as $button) {
			$xml .= '<label pos="'. (19.7 * $count)  .' 0" z-index="0.01" size="18.7 5" class="labels" halign="center" valign="center2" textsize="1.5" textcolor="FFFFFFFF" focusareacolor1="0099FFFF" focusareacolor2="DDDDDDFF" action="'. $button['action'] .'" text="$O'. $button['title'] .'" id="ClassDialogButton'. $count .'" ScriptEvents="1"/>';
			$count += 1;
		}
		$xml .= '</frame>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildContent () {

		$xml = '<label pos="2.5 -2.5" z-index="0.01" size="97.5 37.5" class="labels" textsize="1" autonewline="1" maxline="10" textcolor="FFFFFFFF" text="'. $this->content['message'] .'"/>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWindow ($login) {

		// Placeholder:
		// - %content%
		// - %buttons%
		// - %maniascript%

		// Begin Window
		$xml = '<manialink id="'. $this->settings['id'] .'" name="ClassDialog" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';
		$xml .= '<frame pos="'. $this->layout['position']['x'] .' '. $this->layout['position']['y'] .'" z-index="'. $this->layout['position']['z'] .'" id="ClassDialog">';	// BEGIN: Window Frame
		$xml .= '<quad pos="0 -8" z-index="0.01" size="102.5 50.25" bgcolor="'. $this->layout['backgrounds']['main'] .'" id="ClassDialogBody" ScriptEvents="1"/>';

		// Title
		$xml .= '<quad pos="0 0" z-index="0.04" size="102.5 8" bgcolor="'. $this->layout['backgrounds']['title'] .'" bgcolorfocus="'. $this->layout['backgrounds']['title_hover'] .'" id="ClassDialogTitle" ScriptEvents="1"/>';
		$xml .= '<quad pos="2.5 -1.075" z-index="0.05" size="5.5 5.5" style="'. $this->layout['title']['icon']['style'] .'" substyle="'. $this->layout['title']['icon']['substyle'] .'"/>';
		$xml .= '<label pos="10 -2.575" z-index="0.05" size="94.25 3.75" class="labels" textsize="2" scale="0.9" textcolor="FFFFFFFF" text="'. $this->content['title'] .'"/>';

		// Minimize Button
		$xml .= '<frame pos="93.5 0.125" z-index="0.05">';
		$xml .= '<quad pos="2.25 -2.4" z-index="0.02" size="3.75 3.75" bgcolor="EEEEEEFF" bgcolorfocus="0099FFFF" id="ClassDialogMinimize" ScriptEvents="1"/>';
		$xml .= '<label pos="4.2 -4.5" z-index="0.03" size="15 0" class="labels" halign="center" valign="center2" textsize="3" textcolor="333333FF" text="$O-"/>';
		$xml .= '</frame>';

//		// Close Button
//		$xml .= '<frame pos="93.5 0.125" z-index="0.05">';
//		$xml .= '<quad pos="2.25 -2.4" z-index="0.02" size="3.75 3.75" bgcolor="EEEEEEFF" bgcolorfocus="0099FFFF" id="ClassDialogClose" ScriptEvents="1"/>';
//		$xml .= '<label pos="4.2 -4.375" z-index="0.03" size="15 0" class="labels" halign="center" valign="center2" textsize="1" scale="0.9" textcolor="333333FF" text="$OX"/>';
//		$xml .= '</frame>';

		// Content
		$xml .= '<frame pos="2.5 -10.5" z-index="0.05">';
		$xml .= '<quad pos="0 0" z-index="0" size="97.5 37.5" bgcolor="FFFFFF33"/>';
		$xml .= '%content%';
		$xml .= '</frame>';

		// Buttons
		$xml .= '%buttons%';

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

		$buttons = array(
//			'Event.ControlId == "ClassDialogClose"',
			'Event.ControlId == "ClassDialogMinimize"',
		);

		$count = 1;
		foreach ($this->content['buttons'] as $item) {
			$buttons[] = 'Event.ControlId == "ClassDialogButton'. $count .'"';

			$count += 1;
		}

		$buttons = implode('||', $buttons);

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.uaseco.org
 * Class:	dialog.class.php
 * License:	GPLv3
 * ----------------------------------
 */
Void WipeOut (Text ChildId) {
	declare CMlFrame Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	if (Container != Null) {
		declare Real EndPosnX = 0.0;
		declare Real EndPosnY = 0.0;
		declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition_V3.X);
		declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition_V3.Y);

		while (Container.RelativeScale > 0.0) {
			Container.RelativePosition_V3.X += (PosnDistanceX / 20);
			Container.RelativePosition_V3.Y += (PosnDistanceY / 20);
			Container.RelativeScale -= 0.05;
			yield;
		}
		Container.Unload();

//		// Disable catching ESC key
//		EnableMenuNavigationInputs = False;
	}
}
Void Minimize (Text ChildId) {
	declare CMlFrame Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	if (Container != Null) {
		declare Real PosnDistanceX = ({$this->layout['position_minimize']['x']} - Container.RelativePosition_V3.X);
		declare Real PosnDistanceY = ({$this->layout['position_minimize']['y']} - Container.RelativePosition_V3.Y);

		while (Container.RelativeScale > 0.2) {
			Container.RelativePosition_V3.X += (PosnDistanceX / 16);
			Container.RelativePosition_V3.Y += (PosnDistanceY / 16);
			Container.RelativeScale -= 0.05;
			yield;
		}
	}
}
Void Maximize (Text ChildId) {
	declare CMlFrame Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	if (Container != Null) {
		declare Real EndPosnX = {$this->layout['position']['x']};
		declare Real EndPosnY = {$this->layout['position']['y']};
		declare Real PosnDistanceX = (EndPosnX - Container.RelativePosition_V3.X);
		declare Real PosnDistanceY = (EndPosnY - Container.RelativePosition_V3.Y);

		while (Container.RelativeScale < 1.0) {
			Container.RelativePosition_V3.X += (PosnDistanceX / 16);
			Container.RelativePosition_V3.Y += (PosnDistanceY / 16);
			Container.RelativeScale += 0.05;
			yield;
		}
	}
}
main () {
	declare CMlFrame Container <=> (Page.GetFirstChild("ClassDialog") as CMlFrame);
	declare Boolean MoveWindow = False;
	declare Boolean IsMinimized = False;
	declare Real MouseDistanceX = 0.0;
	declare Real MouseDistanceY = 0.0;

//	// Enable catching ESC key
//	EnableMenuNavigationInputs = True;

	while (True) {
		yield;
		if (MoveWindow == True) {
			Container.RelativePosition_V3.X = (MouseDistanceX + MouseX);
			Container.RelativePosition_V3.Y = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			if (PendingEvents.count > 0) {
				foreach (Event in PendingEvents) {
					if (Event.ControlId == "ClassDialogTitle") {
						MouseDistanceX = (Container.RelativePosition_V3.X - MouseX);
						MouseDistanceY = (Container.RelativePosition_V3.Y - MouseY);
						MoveWindow = True;
					}
				}
			}
		}
		else {
			MoveWindow = False;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ClassDialogClose") {
						WipeOut("ClassDialog");
					}
					else if (Event.ControlId == "ClassDialogMinimize" && IsMinimized == False) {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::ShowMenu, 0, 1.0);
						Minimize("ClassDialog");
						IsMinimized = True;
					}
					else if (Event.ControlId == "ClassDialogBody" && IsMinimized == True) {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
						Maximize("ClassDialog");
						IsMinimized = False;
					}
				}
				case CMlEvent::Type::MouseOver : {
					if ({$buttons}) {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "Cut") {		// CTRL + X
						WipeOut("ClassDialog");
					}
					else if (Event.KeyName == "NumpadSubstract" && IsMinimized == False) {
						if (Container != Null) {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::ShowMenu, 0, 1.0);
							Minimize("ClassDialog");
							IsMinimized = True;
						}
					}
					else if (Event.KeyName == "NumpadAdd" && IsMinimized == True) {
						if (Container != Null) {
							Audio.PlaySoundEvent(CAudioManager::ELibSound::HideMenu, 0, 1.0);
							Maximize("ClassDialog");
							IsMinimized = False;
						}
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

		if ($this->settings['stripcodes'] == true) {
			// Remove all formating codes
			$string = $aseco->stripStyles($string);
		}
		else {
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
		}

		return $aseco->handleSpecialChars($string);
	}
}

?>
