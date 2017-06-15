<?php
/*
 * Plugin: Lazy Buttons
 * ~~~~~~~~~~~~~~~~~~~~
 * » A buttons Widget for lazy Players.
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

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-06-03');
		$this->setCopyright('2012 - 2017 by undef.de');
		$this->setDescription('A buttons Widget for lazy Players.');

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMapPrefix',			'onEndMapPrefix');

		$this->registerChatCommand('lazyreload',		'chat_lazyreload',	'Reload the "Lazy Buttons" settings.',	Player::MASTERADMINS);
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
		unset($xml);

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
		$xml = '<manialink name="PluginLazyButtonsWidget" id="PluginLazyButtonsWidget" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';

		// Figure out how many commands where added
		$command_amount = count($this->config['COMMANDS'][0]['ENTRY']);
		$command_amount = (($command_amount > 20) ? 20 : $command_amount);

		if ($state == 'Race') {
			$posx = -(160 + ($command_amount * 10.75) + 14.25);
			$xml .= '<frame pos="'. $posx .' '. $this->config['WIDGET'][0]['RACE'][0]['POS_Y'][0] .'" z-index="20" id="LazyButtonsFrame">';
		}
		else {
			$posx = -(160 + ($command_amount * 10.75) + 14.25);
			$xml .= '<frame pos="'. $posx  .' '. $this->config['WIDGET'][0]['SCORE'][0]['POS_Y'][0] .'" z-index="20" id="LazyButtonsFrame">';
		}

		$xml .= '<quad pos="0 0" z-index="0.001" size="'. (($command_amount * 10.75) + 25) .' 6.7" bgcolor="55556699" bgcolorfocus="555566BB" ScriptEvents="1"/>';
		$xml .= '<quad pos="'. (($command_amount * 10.75) + 16.5) .' -0.2" z-index="0.002" size="6 6" style="'. $this->config['WIDGET'][0]['ICON_STYLE'][0] .'" substyle="'. $this->config['WIDGET'][0]['ICON_SUBSTYLE'][0] .'"/>';
		$xml .= '<label pos="'. (($command_amount * 10.75) + 14.25) .' -6.94" z-index="0.1" size="37.5 0" class="labels" halign="right" textcolor="FC0F" scale="0.8" text="LAZY-BUTTONS/'. $this->getVersion() .'" url="http://www.undef.name/UASECO/Lazy-Buttons.php"/>';
		$xml .= '<quad pos="4 0" z-index="0.2" size="8.75 6.6" style="Icons128x128_1" substyle="BackFocusable" ScriptEvents="1"/>';

		$offset = 14.5;
		$col = 0;
		$command_count = 0;
		foreach ($this->config['COMMANDS'][0]['ENTRY'] as $item) {
			$xml .= '<quad pos="'. ($col + $offset) .' -0.9375" z-index="0.2" size="10 4.6875" action="PluginLazyButtons?Action=Button&MessageId='. $command_count .'" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="LazyButtons'. $command_count .'" ScriptEvents="1"/>';
			$xml .= '<label pos="'. (($col + $offset) + 5) .' -3.28125" z-index="0.3" size="13 5.8125" class="labels" halign="center" valign="center2" scale="0.7" autonewline="1" textcolor="'. $item['TEXT_COLOR'][0] .'" text="'. $this->handleSpecialChars($item['TITLE'][0]) .'"/>';
			$col += 10.75;

			// Limited to 20 entries
			if ($command_count >= 19) {
				break;
			}

			$command_count ++;
		}
		$xml .= '</frame>';

$xml .= <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	Widget @ plugin.lazy_buttons.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
Void Scrolling(Text ChildId, Boolean Direction) {
	declare CMlFrame Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real PositionClosed = {$posx};
	declare Real PositionOpen = -163.5;
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
					if (TextLib::SubString(Event.ControlId, 0, 11) == "LazyButtons") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}

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

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		if ($answer['Action'] == 'Button') {

			// Get the Player object
			$player = $aseco->server->players->player_list[$login];

			if ( isset($this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['MESSAGE'][0]) && $this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['MESSAGE'][0] != '') {
				$aseco->sendChatMessage('$FF0[$Z'. $player->nickname .'$Z$S$FF0] $I'. $this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['MESSAGE'][0], false);
			}
			else if ( isset($this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['CHAT_COMMAND'][0]) && $this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['CHAT_COMMAND'][0] != '' ) {
				$aseco->releaseChatCommand($this->config['COMMANDS'][0]['ENTRY'][$answer['MessageId']]['CHAT_COMMAND'][0], $player->login);
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

	public function onLoadingMap ($aseco, $map) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {
		$aseco->sendManialink($this->config['Widget']['Race'], false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMapPrefix ($aseco, $map) {
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
