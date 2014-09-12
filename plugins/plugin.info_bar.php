<?php
/*
 * Plugin: Info Bar
 * ~~~~~~~~~~~~~~~~
 * » Displays a multi functional bar.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-10
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
 *  - plugins/plugin.local_records.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginInfoBar();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginInfoBar extends Plugin {
	public $config = array();
	public $records = array();
	public $update = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a multi functional bar.');

		$this->addDependence('PluginLocalRecords',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onEveryTenSeconds',		'onEveryTenSeconds');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerFinish',			'onPlayerFinish');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onBeginMap1',			'onBeginMap1');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onLocalRecordBestLoaded',		'onLocalRecordBestLoaded');
		$this->registerEvent('onLocalRecord',			'onLocalRecord');
		$this->registerEvent('onDedimaniaRecordsLoaded',	'onDedimaniaRecordsLoaded');
		$this->registerEvent('onDedimaniaRecord',		'onDedimaniaRecord');
		$this->registerEvent('onManiaExchangeBestLoaded',	'onManiaExchangeBestLoaded');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

//		// Read Configuration
//		if (!$this->config = $aseco->parser->xmlToArray('config/welcome_center.xml', true, true)) {
//			trigger_error('[WelcomeCenter] Could not read/parse config file "config/welcome_center.xml"!', E_USER_ERROR);
//		}
//		$this->config = $this->config['WELCOME_CENTER'];
//
//		// Transform 'TRUE' or 'FALSE' from string to boolean
//		$this->config['WELCOME_WINDOW'][0]['ENABLED'][0]			= ((strtoupper($this->config['WELCOME_WINDOW'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
//		$this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]	= ((strtoupper($this->config['WELCOME_WINDOW'][0]['HIDE'][0]['RANKED_PLAYER'][0]) == 'TRUE')	? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ENABLED'][0]) == 'TRUE')			? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]		= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['MESSAGES_IN_WINDOW'][0]) == 'TRUE')		? true : false);
//		$this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]			= ((strtoupper($this->config['JOIN_LEAVE_INFO'][0]['ADD_RIGHTS'][0]) == 'TRUE')			? true : false);
//		$this->config['INFO_MESSAGES'][0]['ENABLED'][0]				= ((strtoupper($this->config['INFO_MESSAGES'][0]['ENABLED'][0]) == 'TRUE')			? true : false);

		$this->config['bar']['background_color']			= '5569';
		$this->config['bar']['logo']					= array('http://www.uaseco.org/media/uaseco/logo-uaseco.png', 'http://www.uaseco.org/media/uaseco/logo-uaseco.png');
		$this->config['bar']['position']['x']				= -160;
		$this->config['bar']['position']['y']				= 87.5;
		$this->config['bar']['position']['z']				= 20;


		$this->config['box']['font_color_top']				= 'FFFF';
		$this->config['box']['font_color_bottom']			= 'DDDF';
		$this->config['box']['seperator_color']				= 'DDD6';
		$this->config['box']['background_color_default']		= 'FFF0';
		$this->config['box']['background_color_focus']			= '09FF';

		$this->config['clock']['icon']					= 'http://static.undef.name/ingame/records-eyepiece/clock-icon.png';


		$this->config['records']['position']				= 264;
		$this->config['records']['personalbest']['Label']		= 'PERSONAL BEST';
		$this->config['records']['personalbest']['Style']		= 'Icons128x128_1';
		$this->config['records']['personalbest']['Substyle']		= 'ChallengeAuthor';
		$this->config['records']['personalbest']['ImageUrl']		= '';
		$this->config['records']['local']['Label']			= 'LOCAL RECORD';
		$this->config['records']['local']['Style']			= 'BgRaceScore2';
		$this->config['records']['local']['Substyle']			= 'LadderRank';
		$this->config['records']['local']['ImageUrl']			= '';
		$this->config['records']['dedimania']['Label']			= 'DEDIMANIA';
		$this->config['records']['dedimania']['Style']			= 'Icons128x128_1';
		$this->config['records']['dedimania']['Substyle']		= 'Vehicles';
		$this->config['records']['dedimania']['ImageUrl']		= '';
		$this->config['records']['maniaexchange']['Label']		= 'MANIA EXCHANGE';
		$this->config['records']['maniaexchange']['Style']		= '';
		$this->config['records']['maniaexchange']['Substyle']		= '';
		$this->config['records']['maniaexchange']['ImageUrl']		= 'http://static.undef.name/ingame/records-eyepiece/logo-maniaexchange-normal.png';


		$this->config['manialinkid']					= 'PluginInfoBar';
		$this->config['placeholder']					= array(
			'score'	=> '---',
			'time'	=> '-:--.---',
		);


		$this->records['local']						= 0;
		$this->records['dedimania']					= 0;
		$this->records['maniaexchange']					= 0;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEveryTenSeconds ($aseco) {
		// Check for required updates
		$this->updateRecordsBox();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Send Bar
		$this->sendInfoBar($player->login);

		// Store it into the $player object
		$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $aseco->server->maps->current->id);
		$player->personal_best = $score['time'];

		// Add Player login to update/send RecordsBox
		$this->update[$player->login] = true;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finished) {

		if ($finished->score > 0) {
			// check for improved score (Stunts) or time (others)
			if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
				if ($finished->player->personal_best < $finished->score) {
					$finished->player->personal_best = $finished->score;
					$this->sendRecordsBox($finished->player);
				}
			}
			else {
				if ($finished->player->personal_best == 0 || $finished->player->personal_best > $finished->score) {
					$finished->player->personal_best = $finished->score;
					$this->sendRecordsBox($finished->player);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// Get Personal Best for all Players
		foreach ($aseco->server->players->player_list as $player) {
			// Store it into the $player object
			$score = $aseco->plugins['PluginLocalRecords']->getPersonalBest($player->login, $map->id);
			$player->personal_best = $score['time'];

			// Mark for update
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap1 ($aseco, $map) {
		// Send records box
		$this->updateRecordsBox();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		// Reset Personal Best for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$player->personal_best = 0;
		}

		// Reset all records
		$this->records['local']		= 0;
		$this->records['dedimania']	= 0;
		$this->records['maniaexchange']	= 0;

		// Reset update list
		$this->update = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.mania_exchange_info.php
	public function onManiaExchangeBestLoaded ($aseco, $score) {

		// Store time
		$this->records['maniaexchange'] = $score;

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecordBestLoaded ($aseco, $score) {

		// Store time
		$this->records['local'] = $score;

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.local_records.php
	public function onLocalRecord ($aseco, $record) {

		if ($record->score > 0) {
			if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS && $record->score > $this->records['local']) {
				$this->records['local'] = $record->score;
			}
			else if ($aseco->server->gameinfo->mode != Gameinfo::STUNTS && $record->score < $this->records['local']) {
				$this->records['local'] = $record->score;
			}
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecordsLoaded ($aseco, $records) {

		if (count($records) > 0) {
			$this->records['dedimania'] = $records[0]['Best'];
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Event from plugin.dedimania.php
	public function onDedimaniaRecord ($aseco, $record) {

		if ($record['Best'] > 0 && $record['Best'] < $this->records['dedimania']) {
			$this->records['dedimania'] = $record['Best'];
		}

		// Update RecordsBox for all Players
		foreach ($aseco->server->players->player_list as $player) {
			$this->update[$player->login] = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateRecordsBox () {
		global $aseco;

		// Update RecordsBox for Players
		foreach ($this->update as $login => $value) {
			if ($value == true) {
				$this->update[$login] = false;
				$player = $aseco->server->players->getPlayer($login);
				$this->sendRecordsBox($player);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendInfoBar ($logins = false) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'MainBar" name="'. $this->config['manialinkid'] .'MainBar" version="1">';
		$xml .= '<frame posn="'. $this->config['bar']['position']['x'] .' '. $this->config['bar']['position']['y'] .' '. $this->config['bar']['position']['z'] .'">';
		$xml .= '<quad posn="0 0 0.01" sizen="380 7" bgcolor="'. $this->config['bar']['background_color'] .'"/>';
		$xml .= '<quad posn="3 -1.5 0.02" sizen="22.1875 4.03125" image="'. $this->config['bar']['logo'][0] .'" imagefocus="'. $this->config['bar']['logo'][1] .'"/>';
		$xml .= '</frame>';
		$xml .= '</manialink>';
		$xml .= $this->buildClock();

		$aseco->sendManiaLink($xml, $logins);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function buildClock () {

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	Clock
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
#Include "TextLib" as TextLib
main() {
	declare LabelLocalTime <=> (Page.GetFirstChild("{$this->config['manialinkid']}LabelLocalTime") as CMlLabel);
	declare PrevTime = CurrentLocalDateText;
	while (True) {
		yield;

		// Throttling to work only on every second
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;
			LabelLocalTime.SetText(TextLib::SubString(CurrentLocalDateText, 11, 20));
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Clock" name="'. $this->config['manialinkid'] .'Clock" version="1">';
		$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + 292) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
		$xml .= '<label posn="0 0 0.02" sizen="23 7" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_default'] .'" text=" "/>';
		$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '<quad posn="23 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" image="'. $this->config['clock']['icon'] .'"/>';
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="10 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="" id="'. $this->config['manialinkid'] .'LabelLocalTime"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="22 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="LOCAL TIME"/>';
		$xml .= '</frame>';
		$xml .= $maniascript;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendRecordsBox ($player) {
		global $aseco;

		// Formate time/score
		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$recs['personalbest']	= $aseco->formatNumber($player->personal_best);
			$recs['local']		= $aseco->formatNumber($this->records['local']);
			$recs['dedimania']	= $aseco->formatNumber($this->records['dedimania']);
			$recs['maniaexchange']	= $aseco->formatNumber($this->records['maniaexchange']);
		}
		else {
			$recs['personalbest']	= $aseco->formatTime($player->personal_best);
			$recs['local']		= $aseco->formatTime($this->records['local']);
			$recs['dedimania']	= $aseco->formatTime($this->records['dedimania']);
			$recs['maniaexchange']	= $aseco->formatTime($this->records['maniaexchange']);
		}

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	RecordsRotation
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
Void ReplaceRecords (Text _Time, Text _Label, Text _Style, Text _Substyle, Text _ImageUrl) {
	declare QuadIcon <=> (Page.GetFirstChild("QuadIcon") as CMlQuad);
	declare LabelTime <=> (Page.GetFirstChild("LabelTime") as CMlLabel);
	declare LabelText <=> (Page.GetFirstChild("LabelText") as CMlLabel);

	// Fade out <label>s
	while (LabelTime.Opacity > 0.0) {
		if ((LabelTime.Opacity - 0.05) < 0.0) {
			QuadIcon.Opacity = 0.0;
			LabelTime.Opacity = 0.0;
			LabelText.Opacity = 0.0;
			break;
		}
		QuadIcon.Opacity -= 0.05;
		LabelTime.Opacity -= 0.05;
		LabelText.Opacity -= 0.05;
		yield;
	}

	// Replace content
	if (_Style != "" && _Substyle != "") {
		QuadIcon.Style = _Style;
		QuadIcon.Substyle = _Substyle;
		QuadIcon.ImageUrl = "";
	}
	if (_ImageUrl != "") {
		QuadIcon.Style = "";
		QuadIcon.Substyle = "";
		QuadIcon.ImageUrl = _ImageUrl;
	}
	LabelTime.SetText(_Time);
	LabelText.SetText(_Label);

	// Fade in <label>s
	while (LabelTime.Opacity < 1.0) {
		if ((LabelTime.Opacity + 0.05) > 1.0) {
			QuadIcon.Opacity = 1.0;
			LabelTime.Opacity = 1.0;
			LabelText.Opacity = 1.0;
			break;
		}
		QuadIcon.Opacity += 0.05;
		LabelTime.Opacity += 0.05;
		LabelText.Opacity += 0.05;
		yield;
	}
}
Void WipeIn (Text ChildId, Vec2 EndSize) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);

	Container.Hide();
	Container.RelativePosition.X = Container.RelativePosition.X + (EndSize.X / 2);
	Container.RelativeScale = 0.0;
	Container.Show();

	while (Container.RelativeScale < 1.0) {
		Container.RelativePosition.X = Container.RelativePosition.X - (EndSize.X / 2 / 10);
		Container.RelativeScale += 0.10;
		yield;
	}
}
main() {
	declare CMlControl RecordsDropDown <=> (Page.GetFirstChild("RecordsDropDown") as CMlFrame);

	declare RecordScores = Text[Text][Integer];
	RecordScores[0] = [
		"Score"		=> "{$recs['personalbest']}",
		"Label"		=> "{$this->config['records']['personalbest']['Label']}",
		"Style"		=> "{$this->config['records']['personalbest']['Style']}",
		"Substyle"	=> "{$this->config['records']['personalbest']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['personalbest']['ImageUrl']}"
	];
	RecordScores[1] = [
		"Score"		=> "{$recs['local']}",
		"Label"		=> "{$this->config['records']['local']['Label']}",
		"Style"		=> "{$this->config['records']['local']['Style']}",
		"Substyle"	=> "{$this->config['records']['local']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['local']['ImageUrl']}"
	];
	RecordScores[2] = [
		"Score"		=> "{$recs['dedimania']}",
		"Label"		=> "{$this->config['records']['dedimania']['Label']}",
		"Style"		=> "{$this->config['records']['dedimania']['Style']}",
		"Substyle"	=> "{$this->config['records']['dedimania']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['dedimania']['ImageUrl']}"
	];
	RecordScores[3] = [
		"Score"		=> "{$recs['maniaexchange']}",
		"Label"		=> "{$this->config['records']['maniaexchange']['Label']}",
		"Style"		=> "{$this->config['records']['maniaexchange']['Style']}",
		"Substyle"	=> "{$this->config['records']['maniaexchange']['Substyle']}",
		"ImageUrl"	=> "{$this->config['records']['maniaexchange']['ImageUrl']}"
	];

	declare Integer TimeOut = 7;
	declare Integer Timer = (CurrentTime / 1000);
	declare Integer SecondsCounter = 0;
	declare Integer DisplayedRecord = 0;
	while (True) {
		yield;

		// Throttling to change only every "TimeOut" seconds
		if (Timer <= (CurrentTime / 1000) && RecordsDropDown.Visible == False) {
			Timer = (CurrentTime / 1000) + TimeOut;

			// Replace displayed record
			ReplaceRecords(
				RecordScores[DisplayedRecord]["Score"],
				RecordScores[DisplayedRecord]["Label"],
				RecordScores[DisplayedRecord]["Style"],
				RecordScores[DisplayedRecord]["Substyle"],
				RecordScores[DisplayedRecord]["ImageUrl"]
			);

			DisplayedRecord += 1;
			if (DisplayedRecord >= RecordScores.count) {
				DisplayedRecord = 0;
			}
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "RecordsButton") {
						if (RecordsDropDown.Visible == True) {
							RecordsDropDown.Hide();
						}
						else {
							WipeIn("RecordsDropDown", <28.0, 28.6>);
						}
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Records" name="'. $this->config['manialinkid'] .'Records" version="1">';
		$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + $this->config['records']['position']) .' '. $this->config['bar']['position']['y'] .' '. ($this->config['bar']['position']['z'] + 0.01) .'">';
		$xml .= '<label posn="0 0 0.02" sizen="28 7" focusareacolor1="'. $this->config['box']['background_color_default'] .'" focusareacolor2="'. $this->config['box']['background_color_focus'] .'" text=" " id="RecordsButton" ScriptEvents="1"/>';
		$xml .= '<quad posn="0 0 0.03" sizen="0.1 7" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" id="QuadIcon"/>';
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text=" " id="LabelTime"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text=" " id="LabelText"/>';
		$xml .= '</frame>';

		// Build on click full widget
		$xml .= '<frame posn="'. ($this->config['bar']['position']['x'] + $this->config['records']['position']) .' '. ($this->config['bar']['position']['y'] - 7) .' '. ($this->config['bar']['position']['z'] + 0.01) .'" id="RecordsDropDown" hidden="true">';
		$xml .= '<quad posn="0 -0.1 0.02" sizen="28 28.6" bgcolor="'. $this->config['bar']['background_color'] .'"/>';


		// Personal Best
		$xml .= '<frame posn="0 -0 0.02">';
		if ($this->config['records']['personalbest']['Style'] != '' && $this->config['records']['personalbest']['Substyle'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['personalbest']['Style'] .'" substyle="'. $this->config['records']['personalbest']['Substyle'] .'"/>';
		}
		else if ($this->config['records']['personalbest']['ImageUrl'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" image="'. $this->config['records']['personalbest']['ImageUrl'] .'"/>';
		}
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['personalbest'] .'"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['personalbest']['Label'] .'"/>';
		$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '</frame>';


		// Local Record
		$xml .= '<frame posn="0 -7.2 0.02">';
		if ($this->config['records']['local']['Style'] != '' && $this->config['records']['local']['Substyle'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['local']['Style'] .'" substyle="'. $this->config['records']['local']['Substyle'] .'"/>';
		}
		else if ($this->config['records']['local']['ImageUrl'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" image="'. $this->config['records']['local']['ImageUrl'] .'"/>';
		}
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['local'] .'"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['local']['Label'] .'"/>';
		$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '</frame>';


		// Dedimania Record
		$xml .= '<frame posn="0 -14.4 0.02">';
		if ($this->config['records']['dedimania']['Style'] != '' && $this->config['records']['dedimania']['Substyle'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['dedimania']['Style'] .'" substyle="'. $this->config['records']['dedimania']['Substyle'] .'"/>';
		}
		else if ($this->config['records']['dedimania']['ImageUrl'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" image="'. $this->config['records']['dedimania']['ImageUrl'] .'"/>';
		}
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['dedimania'] .'"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['dedimania']['Label'] .'"/>';
		$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '</frame>';


		// Mania Exchange Offline Record
		$xml .= '<frame posn="0 -21.6 0.02">';
		if ($this->config['records']['maniaexchange']['Style'] != '' && $this->config['records']['maniaexchange']['Substyle'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" style="'. $this->config['records']['maniaexchange']['Style'] .'" substyle="'. $this->config['records']['maniaexchange']['Substyle'] .'"/>';
		}
		else if ($this->config['records']['maniaexchange']['ImageUrl'] != '') {
			$xml .= '<quad posn="1.6 -1 0.03" sizen="7 5.25" image="'. $this->config['records']['maniaexchange']['ImageUrl'] .'"/>';
		}
		$xml .= '<label posn="9.9 -1.4 0.03" sizen="15 2.625" textcolor="'. $this->config['box']['font_color_top'] .'" textsize="1" text="'. $recs['maniaexchange'] .'"/>';
		$xml .= '<label posn="9.9 -4.2 0.03" sizen="25 2.625" textcolor="'. $this->config['box']['font_color_bottom'] .'" textsize="1" scale="0.6" text="'. $this->config['records']['maniaexchange']['Label'] .'"/>';
		$xml .= '<quad posn="0 0 0.04" sizen="28 0.2" bgcolor="'. $this->config['box']['seperator_color'] .'"/>';
		$xml .= '</frame>';


		$xml .= '</frame>';
		$xml .= $maniascript;
		$xml .= '</manialink>';

		$aseco->sendManiaLink($xml, $player->login);
	}
}

?>
