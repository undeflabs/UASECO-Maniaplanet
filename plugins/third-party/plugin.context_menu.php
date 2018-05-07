<?php
/*
 * Plugin: Context Menu
 * ~~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Context-Menu.php
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

	// Start the plugin
	$_PLUGIN = new PluginContextMenu();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginContextMenu extends Plugin {
	public $config = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2015 - 2018 by undef.de');
		$this->setDescription('A configurable Right-Mouse-Button-Menu (Context-Menu).');

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.0';
		if (defined('UASECO_VERSION')) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[ContextMenu] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[ContextMenu] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}


		$aseco->console('[ContextMenu] ********************************************************');
		$aseco->console('[ContextMenu] Starting version '. $this->getVersion() .' - Maniaplanet');

		// Load the context_menu.xml
		libxml_use_internal_errors(true);
		if (!$config = @simplexml_load_file('config/context_menu.xml', null, LIBXML_COMPACT) ) {
			$aseco->console('[ContextMenu] Could not read/parse config file "config/context_menu.xml"!');
			foreach (libxml_get_errors() as $error) {
				$aseco->console("\t". $error->message);
			}
			libxml_clear_errors();
			trigger_error("[ContextMenu] Please copy the 'context_menu.xml' from this Package into the 'config' directory and do not forget to edit it!", E_USER_ERROR);
		}
		else {
			$aseco->console('[ContextMenu] Parsed "config/context_menu.xml" successfully...');
		}
		libxml_use_internal_errors(false);

		// Remove all comments
		unset($config->comment);

		$this->config['entries'] = $this->simplexml2array($config->entries);
//		$this->recursive($data);

		$aseco->console('[ContextMenu] Preparing context menu...');
		$aseco->console('[ContextMenu] ********************************************************');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Modified version of http://jamesroberts.name/blog/2010/02/10/simplexml-to-array/
	public function simplexml2array ($xml) {
		global $aseco;

		if (isset($xml) && is_object($xml) && $xml instanceof SimpleXMLElement) {
			$xml = get_object_vars($xml);
		}
		if (is_array($xml)) {
			foreach ($xml as $key => $value) {
				if ((string)$key === '@attributes') {
					$r[] = $this->simplexml2array($value);
				}
				else {
					$r[$key] = $this->simplexml2array($value);
				}
			}
			return $r;
		}
		return (string)$xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function recursive ($array) {
		global $aseco;

		foreach ($array as $key => $value){
//$aseco->dump($key, $value);
			// If $value is an array.
			if (is_array($value)) {
				// We need to loop through it.
				$aseco->dump($value);
				$this->recursive($value);
			}
			else {
				// It is not an array, so print it out.
//				$aseco->dump('xxx', $key, $value);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {
		$aseco->sendManialink($this->buildContextMenu(), $player->login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $params) {
		if ($params['Action'] === 'executeAction') {
			if (substr($params['Call'], 0, 1) === '/') {
				$aseco->releaseChatCommand($params['Call'], $login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildContextMenu () {
		global $aseco;

		$total_entries = 1;
		$xml  = '<manialink id="ManialinkContextMenu" name="ContextMenu" version="3">';
		$xml .= '<quad pos="-160 90" z-index="34" size="320 180" bgcolor="00000055" id="ContextMenuLightbox"/>';
		$xml .= '<frame pos="0 0" z-index="35" id="ContextMenu">';

		foreach ($this->config['entries']['group'] as $group) {
			if (isset($group['title'])) {
				$xml .= '<quad pos="0 0" z-index="0.002" size="48.75 %context_menu_height%" bgcolor="555566AA"/>';
				$xml .= '<quad pos="0.5 -0.5" z-index="0.003" size="47.75 3.75" bgcolor="0099FFDD"/>';
				$xml .= '<quad pos="43.5 -0.6" z-index="0.004" size="4 3.6" style="Icons128x32_1" substyle="Close" id="ContextMenuClose'. str_replace(' ', '', $group['title']) .'" scriptevents="1"/>';
				$xml .= '<label pos="2 -1.2" z-index="0.004" size="44.3 3" textsize="1" scale="0.9" textcolor="FFFFFFFF" text="$O'. (string)$group['title'] .'"/>';
			}

			$offset = 0.6;
			$menu_height = 4;
			foreach ($group as $entry) {
				$entry = $entry[0];

				// Check for a missing Plugin or no dependency to skip entry.
				if ($this->checkDependency($entry) === false) {
					continue;
				}

				if (isset($entry['type']) && $entry['type'] === 'separator') {
					$offset += 0.45;
					$menu_height += 0.45;
				}
				else if (isset($entry['action'])) {
					// Action handling
					$action = false;
					if (substr($entry['action'], 0, 14) === 'maniaplanet://') {
	 					$action = ' data-manialink="<![CDATA['. (string)$entry['action'] .']]>"';
					}
					else if (substr($entry['action'], 0, 7) === 'http://') {
	 					$action = ' data-httplink="<![CDATA['. (string)$entry['action'] .']]>"';
					}
					else if (substr($entry['action'], 0, 1) === '/') {
	 					$action = ' data-chatcmd="<![CDATA[PluginContextMenu?Action=executeAction&amp;Call='. (string)$entry['action'] .']]>"';
					}
					$xml .= '<quad pos="0.5 -'. ($menu_height + $offset) .'" z-index="0.003" size="47.75 3.75"'. $action .' bgcolor="00000033" bgcolorfocus="88AA0077" id="ContextMenuActionEntry'. $total_entries .'" scriptevents="1"/>';

					// Entry icon
					$icon[0] = false;
					if (isset($entry['icon'])) {
						$icon = explode('|', (string)$entry['icon']);
					}
					if (substr($icon[0], 0, 7) === 'http://') {
						$xml .= '<quad pos="0.9 -'. ($menu_height + $offset) .'" z-index="0.004" size="3.8 3.8" image="'. $icon[0] .'"/>';
					}
					else if ($icon[0] !== false) {
						$xml .= '<quad pos="0.9 -'. ($menu_height + $offset) .'" z-index="0.004" size="3.8 3.8" style="'. $icon[0] .'" substyle="'. $icon[1] .'"/>';
					}
					$xml .= '<label pos="5.5 -'. ($menu_height + $offset + 0.7) .'" z-index="0.004" size="42.75 3" textsize="1" scale="0.9" textcolor="FFFFFFFF" text="'. (string)$entry['title'] .'"/>';

//					if (count($entry) > 0) {
//						// Submenu indicator
//						$xml .= '<quad pos="44 -'. ($menu_height + $offset + 0.635) .'" z-index="0.003" size="3.8 2.53" style="Icons64x64_1" substyle="ShowRight2" colorize="5BF"/>';
//					}

					$menu_height += 4;
					$total_entries += 1;
				}
			}
		}
		// Setup height
		$xml = str_replace('%context_menu_height%', ($menu_height + 0.6 + 0.6), $xml);
		$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	ContextMenu @ plugin.context_menu.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
main () {
	declare CMlControl QuadContextMenuLightbox <=> (Page.GetFirstChild("ContextMenuLightbox") as CMlQuad);
	declare CMlControl FrameContextMenu <=> (Page.GetFirstChild("ContextMenu") as CMlFrame);

	QuadContextMenuLightbox.Visible	= False;
	FrameContextMenu.Visible	= False;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		if (MouseRightButton == True) {
			FrameContextMenu.RelativePosition_V3.X = MouseX;
			FrameContextMenu.RelativePosition_V3.Y = MouseY;
			FrameContextMenu.Visible = True;
			QuadContextMenuLightbox.Visible = True;

			// Enable catching ESC key
			EnableMenuNavigationInputs = True;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::KeyPress : {
					if (Event.KeyName == "Escape") {
						FrameContextMenu.Visible = False;
						QuadContextMenuLightbox.Visible = False;

						// Disable catching ESC key
						EnableMenuNavigationInputs = False;
					}
				}
				case CMlEvent::Type::MouseClick : {
					if (TextLib::SubString(Event.ControlId, 0, 16) == "ContextMenuClose") {
						FrameContextMenu.Visible = False;
						QuadContextMenuLightbox.Visible = False;
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);

						// Disable catching ESC key
						EnableMenuNavigationInputs = False;
					}
					else if (TextLib::SubString(Event.ControlId, 0, 22) == "ContextMenuActionEntry") {
						declare CMlQuad Quad <=> (Page.GetFirstChild(Event.ControlId) as CMlQuad);
						if (Quad.DataAttributeExists("chatcmd") == True) {
							declare Text Action = Quad.DataAttributeGet("chatcmd");
							Action = TextLib::Replace(Action, "<![CDATA[", "");
							Action = TextLib::Replace(Action, "]]>", "");
							TriggerPageAction(Action);
						}
						else if (Quad.DataAttributeExists("manialink") == True) {
							declare Text Link = Quad.DataAttributeGet("manialink");
							Link = TextLib::Replace(Link, "<![CDATA[", "");
							Link = TextLib::Replace(Link, "]]>", "");
							OpenLink(Link, CMlScript::LinkType::ManialinkBrowser);
						}
						else if (Quad.DataAttributeExists("httplink") == True) {
							declare Text Link = Quad.DataAttributeGet("httplink");
							Link = TextLib::Replace(Link, "<![CDATA[", "");
							Link = TextLib::Replace(Link, "]]>", "");
							OpenLink(Link, CMlScript::LinkType::ExternalBrowser);
						}
						FrameContextMenu.Visible = False;
						QuadContextMenuLightbox.Visible = False;
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);

						// Disable catching ESC key
						EnableMenuNavigationInputs = False;
					}
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript .'</manialink>';
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkDependency ($entry) {
		global $aseco;

		// No required dependency, no need to check!
		if (!isset($entry['dependency'])) {
			return true;
		}

		foreach ($aseco->plugins as $plugin) {
			if ($plugin->getClassname() === (string)$entry['dependency']) {
				return true;
			}
		}
		return false;
	}
}

?>
