<?php
/*
 * Plugin: Lazy Buttons
 * ~~~~~~~~~~~~~~~~~~~~
 * » A buttons Widget for lazy Players.
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Version:		1.0.0
 * Date:		2015-01-20
 * Copyright:		2012 - 2015 by undef.de
 * System:		UASECO/0.9.5+
 * Game:		ManiaPlanet Trackmania2 (TM2)
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
 *  -
 *
 */

/* The following manialink id's are used in this plugin (the 925 part of id can be changed on trouble):
 *
 * ManialinkID's
 * ~~~~~~~~~~~~~
 * 92500		id for manialink Widget
 *
 * ActionID's
 * ~~~~~~~~~~
 *  92500 - 92519	ids for action clicks
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginLazyButtons();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginLazyButtons extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('A buttons Widget for lazy Players.');

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap1',			'onEndMap1');

		$this->registerChatCommand('lazyreload',		'chat_lazyreload',	'Reload the "Lazy Buttons" settings.',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '0.9.5';
		if ( defined('UASECO_VERSION') ) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[LazyButtons] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[LazyButtons] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}

		// Read Configuration
		if (!$xml = $aseco->parser->xmlToArray('config/lazy_buttons.xml', true, true)) {
			trigger_error('[LazyButtons] Could not read/parse config file "config/lazy_buttons.xml"!', E_USER_ERROR);
		}
		$this->config = $xml['SETTINGS'];

		$this->config['ManialinkId'] = '925';

		$this->config['Widget']['Race'] = $this->loadTemplate('Race');
		$this->config['Widget']['Score'] = $this->loadTemplate('Score');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function loadTemplate ($state) {

		// Setup Widget
		$xml = '<manialink id="'. $this->config['ManialinkId'] .'00">';

		// Figure out how many commands where added
		$command_amount = count($this->config['COMMANDS'][0]['ENTRY']);
		$command_amount = (($command_amount > 20) ? 20 : $command_amount);

		if ($state == 'Race') {
			$posx = -(64 + ($command_amount * 4.3) + 5.7);
			$xml .= '<frame posn="'. $posx .' '. $this->config['WIDGET'][0]['RACE'][0]['POS_Y'][0] .' 20" id="LazyButtonsFrame">';
		}
		else {
			$posx = -(64 + ($command_amount * 4.3) + 5.7);
			$xml .= '<frame posn="'. $posx  .' '. $this->config['WIDGET'][0]['SCORE'][0]['POS_Y'][0] .' 20" id="LazyButtonsFrame">';
		}

		$xml .= '<format textsize="1"/>';
		$xml .= '<quad posn="0 0 0.001" sizen="'. (($command_amount * 4.3) + 10) .' 3.4" style="'. $this->config['WIDGET'][0]['BACKGROUND_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BACKGROUND_SUBSTYLE'][0] .'" ScriptEvents="1"/>';
		$xml .= '<quad posn="'. (($command_amount * 4.3) + 6.3) .' -0.3 0.002" sizen="3.2 2.7" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label posn="'. (($command_amount * 4.3) + 5.7) .' -3.7 0.1" sizen="15 0" halign="right" textcolor="FC0F" scale="0.8" text="LAZY-BUTTONS/'. $this->getVersion() .'" url="http://www.undef.name/UASECO/Lazy-Buttons.php"/>';
		$xml .= '<quad posn="1.5 0 0.2" sizen="4 3.5" style="Icons128x128_1" substyle="BackFocusable" ScriptEvents="1"/>';

		$offset = 5.8;
		$col = 0;
		$command_count = 0;
		foreach ($this->config['COMMANDS'][0]['ENTRY'] as $item) {
			$xml .= '<quad posn="'. ($col + $offset) .' -0.5 0.2" sizen="4.3 2.5" action="'. $this->config['ManialinkId'] . sprintf('%02d', $command_count) .'" style="'. $this->config['WIDGET'][0]['BUTTON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['BUTTON_SUBSTYLE'][0] .'" ScriptEvents="1"/>';
			$xml .= '<label posn="'. (($col + $offset) + 2.15) .' -1.75 0.3" sizen="5.45 3.1" halign="center" valign="center" scale="0.7" autonewline="1" textcolor="'. $item['TEXT_COLOR'][0] .'" text="'. $this->handleSpecialChars($item['TITLE'][0]) .'"/>';
			$col += 4.3;

			// Limited to 20 entries
			if ($command_count >= 19) {
				break;
			}

			$command_count ++;
		}
		$xml .= '</frame>';

$xml .= <<<EOL
<script><!--
Void Scrolling(Text ChildId, Boolean Direction) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real PositionClosed = {$posx} * 2.5;
	declare Real PositionOpen = -65.4 * 2.5;
	declare Real Distance = (PositionClosed - PositionOpen);
	if (Direction == True) {
		while (Container.PosnX > PositionClosed) {
			Container.PosnX += (Distance / 10);
			yield;
		}
		Container.PosnX = PositionClosed;
	}
	else {
		while (Container.PosnX < PositionOpen) {
			Container.PosnX -= (Distance / 20);
			yield;
		}
		Container.PosnX = PositionOpen;
	}
}
main() {
	declare Boolean WindowState = False;
	declare Integer AutoCloseTimer = 0;
	while (True) {
		foreach(Event in PendingEvents) {
			switch(Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (WindowState == False) {
						WindowState = True;
						AutoCloseTimer = (CurrentTime + 7000);
						Scrolling("LazyButtonsFrame", False);
					}
					else if (WindowState == True) {
						WindowState = False;
						AutoCloseTimer = 0;
						Scrolling("LazyButtonsFrame", True);
					}
				}
				case CMlEvent::Type::MouseOver : {
					AutoCloseTimer = (CurrentTime + 7000);
				}
			}
		}
		if ( (AutoCloseTimer != 0) && (CurrentTime >= AutoCloseTimer) ) {
			WindowState = False;
			AutoCloseTimer = 0;
			Scrolling("LazyButtonsFrame", True);
		}
		yield;
	}
}
--></script>
EOL;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lazyreload ($aseco, $login, $chat_command, $chat_parameter) {

		// Reload the "lazy_buttons.xml"
		$this->onSync($aseco);

		if ($aseco->server->gamestate == Server::RACE) {
			$aseco->sendManialink($this->config['Widget']['Race'], false);
		}
		else if ($aseco->server->gamestate == Server::SCORE) {
			$aseco->sendManialink($this->config['Widget']['Score'], false);
		}

		$aseco->sendChatMessage('{#admin}>> Reload of the configuration "lazy_buttons.xml" done.', $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $answer = [0]=PlayerUid, [1]=Login, [2]=Answer
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// If id = 0, bail out immediately
		if ($answer[2] == 0) {
			return;
		}

		if ( ($answer[2] >= (int)$this->config['ManialinkId'] .'00') && ($answer[2] <= (int)$this->config['ManialinkId'] .'19') ) {

			// Get the Player object
			$player = $aseco->server->players->player_list[$answer[1]];

			// Get the wished MessageId
			$msgid = intval( str_replace($this->config['ManialinkId'], '', abs($answer[2])) );

			if ( isset($this->config['COMMANDS'][0]['ENTRY'][$msgid]['MESSAGE'][0]) && $this->config['COMMANDS'][0]['ENTRY'][$msgid]['MESSAGE'][0] != '') {
				$aseco->sendChatMessage('$FF0[$Z'. $player->nickname .'$Z$S$FF0] $I'. $this->config['COMMANDS'][0]['ENTRY'][$msgid]['MESSAGE'][0], false);
			}
			else if ( isset($this->config['COMMANDS'][0]['ENTRY'][$msgid]['CHAT_COMMAND'][0]) && $this->config['COMMANDS'][0]['ENTRY'][$msgid]['CHAT_COMMAND'][0] != '' ) {
				$aseco->releaseChatCommand($this->config['COMMANDS'][0]['ENTRY'][$msgid]['CHAT_COMMAND'][0], $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {
		$aseco->sendManialink($this->config['Widget']['Race'], $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $unused) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $unused) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $unused) {
		$aseco->sendManialink($this->config['Widget']['Score'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleSpecialChars ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[HWILON]/i', '', $string);


		// Convert &
		// Convert "
		// Convert '
		// Convert >
		// Convert <
		$string = str_replace(
				array(
					'&',
					'"',
					"'",
					'>',
					'<'
				),
				array(
					'&amp;',
					'&quot;',
					'&apos;',
					'&gt;',
					'&lt;'
				),
				$string
		);
		$string = $aseco->stripNewlines($string);

		return $aseco->validateUTF8String($string);
	}
}

?>
