<?php
/*
 * Plugin: Tachometer
 * ~~~~~~~~~~~~~~~~~~
 * » Displays a smart tachometer on the HUD.
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
	$_PLUGIN = new PluginTachometer();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginTachometer extends Plugin {
	private $config = array();


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('reaby');
		$this->setVersion('1.0.2');
		$this->setBuild('2019-09-15');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription('Displays a smart tachometer on the HUD.');

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');

		$this->registerChatCommand('tachometer',		'chat_tachometer',		'Adjust some settings for the Tachometer plugin (see: /tachometer)',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$config = $aseco->parser->xmlToArray('config/tachometer.xml', true, true)) {
			trigger_error('[WelcomeCenter] Could not read/parse config file "config/tachometer.xml"!', E_USER_ERROR);
		}
		$settings = $config['SETTINGS'];
		unset($config);

		$this->config['tachometer'] = array(
			'template'					=> file_get_contents('config/tachometer/'. $settings['TEMPLATE'][0]),

			'sizes' => array(
				'scale'					=> $aseco->formatFloat($settings['SCALE'][0]),
			),

			'sounds' => array(
				'enabled'				=> ($aseco->string2bool($settings['SOUNDS'][0]['ENABLED'][0]) === true ? 'True' : 'False'),

				'drive_backward_url'			=> $settings['SOUNDS'][0]['DRIVE_BACKWARD'][0],
				'gear_shift_url'			=> $settings['SOUNDS'][0]['GEAR_SHIFT'][0],
			),
		);

		$this->config['manialinkid'] = 'Tachometer';

		// Disable parts of the UI
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('position', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('speed_and_distance', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('personal_best_and_rank', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('checkpoint_list', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfacePosition('countdown', array(105.0, -66.5, 5.0));
		$aseco->plugins['PluginModescriptHandler']->setUserInterfacePosition('chrono', array(95.5, -84.0, 5.0));

		// Send the UI settings
		$aseco->plugins['PluginModescriptHandler']->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		$xml = $this->buildTachometer($aseco->server->maps->current, true);
		$aseco->sendManiaLink($xml, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		$xml = $this->buildTachometer($map, true);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {

		$xml = $this->buildTachometer($map, false);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		$xml = $this->buildTachometer($map, true);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_tachometer ($aseco, $login, $chat_command, $chat_parameter) {

		// Init
		$message = false;

		// Check optional parameter
		if (strtoupper($chat_parameter) === 'RELOAD') {

			// Reload the config
			$this->onSync($aseco, true);

			// Simulate the event 'onLoadingMap'
			$this->onLoadingMap($aseco, $aseco->server->maps->current);

			$message = '{#admin}» Reload of the configuration "config/tachometer.xml" done.';

		}
		else {
			$message = '{#admin}» Use "/tachometer reload" to reload "config/tachometer.xml".';
		}

		// Show message
		if ($message !== false) {
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTachometer ($map, $show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	Tachmeter @ plugin.tachometer.php
 * Authors:	undef.de, reaby
 * License:	GPLv3
 * ==================================
 */
#Include "TextLib" as TextLib
#Include "MathLib" as MathLib
Text PadString (Text _String, Integer _MaxLength) {
	declare Text String = _String;
	while (TextLib::Length(String) < _MaxLength) {
		String = "0"^ String;
	}
	if (TextLib::Length(String) > _MaxLength) {
		String = TextLib::SubString(String, (TextLib::Length(String) - _MaxLength), _MaxLength);
	}
	return String;
}
Integer Blink (Text _ChildId, Integer _NextChange, Boolean _BlinkSpeed) {
	declare CMlQuad Container <=> (Page.GetFirstChild(_ChildId) as CMlQuad);
	declare Vec3 ColorDefault = TextLib::ToColor("888888");
	declare Vec3 ColorBlink = TextLib::ToColor("FFF500");

	if (CurrentTime >= _NextChange) {
		if (Container.ModulateColor == ColorBlink) {
			Container.ModulateColor = ColorDefault;
		}
		else {
			Container.ModulateColor = ColorBlink;
		}
		if (_BlinkSpeed == True) {
			return (CurrentTime + 250);
		}
		else {
			return (CurrentTime + 500);
		}
	}
	return _NextChange;
}
main() {
	// Declarations
	declare Boolean TachometerStatisticsStatus for LocalUser	= False;
	declare Boolean TachometerSoundStatus for LocalUser		= True;
	declare Integer TachometerCurrentVelocityUnit for LocalUser	= 0;
	declare Integer TachometerCurrentModulateColor for LocalUser	= 0;

	declare netread Integer Net_LibUI_SettingsUpdate for Teams[0];
	declare netread Text[Text] Net_LibUI_Settings for Teams[0];

	declare CMlFrame FrameTachometer	<=> (Page.GetFirstChild("FrameTachometer") as CMlFrame);
	declare CMlFrame FrameSpeedStatistic	<=> (Page.GetFirstChild("FrameSpeedStatistic") as CMlFrame);

	declare CMlQuad QuadTachometer		<=> (Page.GetFirstChild("QuadTachometer") as CMlQuad);
	declare CMlQuad QuadTachoneedle		<=> (Page.GetFirstChild("QuadTachoneedle") as CMlQuad);
	declare CMlQuad QuadTachoscale		<=> (Page.GetFirstChild("QuadTachoscale") as CMlQuad);
	declare CMlQuad QuadTachoscaleOverlay	<=> (Page.GetFirstChild("QuadTachoscaleOverlay") as CMlQuad);
	declare CMlQuad QuadTachoscale01	<=> (Page.GetFirstChild("QuadTachoscale01") as CMlQuad);
	declare CMlQuad QuadTachoscale02	<=> (Page.GetFirstChild("QuadTachoscale02") as CMlQuad);
	declare CMlQuad QuadTachoscale03	<=> (Page.GetFirstChild("QuadTachoscale03") as CMlQuad);
	declare CMlQuad QuadTachoscale04	<=> (Page.GetFirstChild("QuadTachoscale04") as CMlQuad);
	declare CMlQuad QuadTachoscale05	<=> (Page.GetFirstChild("QuadTachoscale05") as CMlQuad);
	declare CMlQuad QuadTachoscale06	<=> (Page.GetFirstChild("QuadTachoscale06") as CMlQuad);
	declare CMlQuad QuadTachoscale07	<=> (Page.GetFirstChild("QuadTachoscale07") as CMlQuad);
	declare CMlQuad QuadTachoscale08	<=> (Page.GetFirstChild("QuadTachoscale08") as CMlQuad);
	declare CMlQuad QuadTachoscale09	<=> (Page.GetFirstChild("QuadTachoscale09") as CMlQuad);
	declare CMlQuad QuadTachoscale10	<=> (Page.GetFirstChild("QuadTachoscale10") as CMlQuad);
	declare CMlQuad QuadTachoscale11	<=> (Page.GetFirstChild("QuadTachoscale11") as CMlQuad);
	declare CMlQuad QuadTachoscale12	<=> (Page.GetFirstChild("QuadTachoscale12") as CMlQuad);

	declare CMlLabel LabelVelocityUnit	<=> (Page.GetFirstChild("LabelVelocityUnit") as CMlLabel);
	declare CMlLabel LabelSpeed		<=> (Page.GetFirstChild("LabelSpeed") as CMlLabel);
	declare CMlLabel LabelDistance1		<=> (Page.GetFirstChild("LabelDistance1") as CMlLabel);
	declare CMlLabel LabelDistance2		<=> (Page.GetFirstChild("LabelDistance2") as CMlLabel);
	declare CMlLabel LabelDistance3		<=> (Page.GetFirstChild("LabelDistance3") as CMlLabel);
	declare CMlLabel LabelDistance4		<=> (Page.GetFirstChild("LabelDistance4") as CMlLabel);
	declare CMlLabel LabelDistance5		<=> (Page.GetFirstChild("LabelDistance5") as CMlLabel);
	declare CMlLabel LabelDistance6		<=> (Page.GetFirstChild("LabelDistance6") as CMlLabel);

	declare CMlLabel LabelGear		<=> (Page.GetFirstChild("LabelGear") as CMlLabel);

	declare CMlQuad QuadIconStatistics	<=> (Page.GetFirstChild("QuadIconStatistics") as CMlQuad);
	declare CMlQuad QuadIconSounds		<=> (Page.GetFirstChild("QuadIconSounds") as CMlQuad);
	declare CMlQuad QuadIconLights		<=> (Page.GetFirstChild("QuadIconLights") as CMlQuad);
	declare CMlQuad QuadIconFuel		<=> (Page.GetFirstChild("QuadIconFuel") as CMlQuad);
	declare CMlQuad QuadIconTemperature	<=> (Page.GetFirstChild("QuadIconTemperature") as CMlQuad);

	declare Boolean SoundsEnabled		= {$this->config['tachometer']['sounds']['enabled']};
	declare SoundDriveBackward		= Audio.CreateSound("{$this->config['tachometer']['sounds']['drive_backward_url']}", 1.0, False, True, False);
	declare SoundGearShift			= Audio.CreateSound("{$this->config['tachometer']['sounds']['gear_shift_url']}", 1.0, False, False, False);
	declare Text LastGear			= "P";
	declare Text[] VelocityUnits		= ["KPH", "KP/H", "KMH", "KM/H", "MPH", "MP/H", "MIH", "MI/H", "SPH", "SP/H"];
	declare Text[] ModulateColors		= ["777777", "555555", "FFFFFF", "FEFFC5", "ACC720", "005893", "FFF700", "FFA700", "E74F3C", "F74BD3"];

	declare Integer RefreshInterval		= 100;
	declare Integer RefreshTime		= CurrentTime;
	declare Integer MeasuredTopSpeed	= 0;
	declare Real MeasuredTopAltitude	= 0.0;
	declare Real MeasuredTopRpm		= 0.0;
	declare Integer TimeCount		= 1;

	declare PrevSettingsUpdate		= -1;
	declare CutOffTimeLimit			= -1;

	// Setup to the stored values
	LabelVelocityUnit.Value			= VelocityUnits[TachometerCurrentVelocityUnit];
	QuadTachoscale.ModulateColor		= TextLib::ToColor(ModulateColors[TachometerCurrentModulateColor]);

	// Turn statistics icon on/off
	if (TachometerStatisticsStatus == True) {
		QuadIconStatistics.ModulateColor = TextLib::ToColor("50B7FF");
	}
	else {
		QuadIconStatistics.ModulateColor = TextLib::ToColor("FFFFFF");
	}

	// Turn sounds on/off
	if (SoundsEnabled == False) {
		TachometerSoundStatus		= False;
	}
	if (TachometerSoundStatus == True) {
		QuadIconSounds.ModulateColor = TextLib::ToColor("FFFFFF");
	}
	else {
		QuadIconSounds.ModulateColor = TextLib::ToColor("FF0000");
	}

	// Turn lights icon on
	declare Text MapMood = "{$aseco->server->maps->current->mood}";
	if (MapMood == "Sunset" || MapMood == "Night") {
		QuadIconLights.ModulateColor = TextLib::ToColor("50B7FF");
	}
	else {
		QuadIconLights.ModulateColor = TextLib::ToColor("888888");
	}

	// Setup Fuel and Temperature display
	declare Integer RestPlayTime		= -1;
	declare Integer BlinkNextChangeFuel	= 0;
	declare Integer BlinkNextChangeTemp	= 0;

	// Settings
	FrameTachometer.RelativeScale		= {$this->config['tachometer']['sizes']['scale']};

	SoundDriveBackward.Volume		= 1.0;
	SoundGearShift.Volume			= 1.0;

	QuadTachoscale01.Opacity		= 0.0;
	QuadTachoscale02.Opacity		= 0.0;
	QuadTachoscale03.Opacity		= 0.0;
	QuadTachoscale04.Opacity		= 0.0;
	QuadTachoscale05.Opacity		= 0.0;
	QuadTachoscale06.Opacity		= 0.0;
	QuadTachoscale07.Opacity		= 0.0;
	QuadTachoscale08.Opacity		= 0.0;
	QuadTachoscale09.Opacity		= 0.0;
	QuadTachoscale10.Opacity		= 0.0;
	QuadTachoscale11.Opacity		= 0.0;
	QuadTachoscale12.Opacity		= 0.0;

	LabelVelocityUnit.Opacity		= 0.65;

	declare Text[] QuadTachoscaleIds = [
		"QuadTachoscale01",
		"QuadTachoscale02",
		"QuadTachoscale03",
		"QuadTachoscale04",
		"QuadTachoscale05",
		"QuadTachoscale06",
		"QuadTachoscale07",
		"QuadTachoscale08",
		"QuadTachoscale09",
		"QuadTachoscale10",
		"QuadTachoscale11",
		"QuadTachoscale12"
	];

	declare CMlGraph GraphStatistic			<=> (Page.GetFirstChild("GraphStatistic") as CMlGraph);
	GraphStatistic.CoordsMin			= <0.0, -1200.0>;
	GraphStatistic.CoordsMax			= <720.0, 7200.0>;

	declare CMlGraphCurve[] Curves			=
	 [GraphStatistic.AddCurve(), GraphStatistic.AddCurve()]; Curves[0].Color
		= <0.9, 0.9, 0.9>; Curves[1].Color
		= <0.0, 7.0, 0.0>;

	declare CMlLabel LabelSpeedStatistic		<=> (Page.GetFirstChild("LabelSpeedStatistic") as CMlLabel);
	declare CMlLabel LabelAltitudeStatistic		<=> (Page.GetFirstChild("LabelAltitudeStatistic") as CMlLabel);
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Hide the Widget for Spectators (also temporary one)
		if (InputPlayer.IsSpawned == False) {
			SoundGearShift.Stop();
			SoundDriveBackward.Stop();
			FrameTachometer.Hide();
			continue;
		}
		else {
			FrameTachometer.Show();
		}

		if (PrevSettingsUpdate != Net_LibUI_SettingsUpdate) {
			PrevSettingsUpdate = Net_LibUI_SettingsUpdate;
			foreach (SettingName => SettingValue in Net_LibUI_Settings) {
				switch (SettingName) {
					case "TMUIModule_Countdown_CutOffTimeLimit": {
						CutOffTimeLimit = TextLib::ToInteger(SettingValue);
					}
				}
			}
		}
		RestPlayTime = (CutOffTimeLimit - GameTime + 1);

//log(MeasuredTopRpm ^", "^ InputPlayer.EngineTurboRatio ^", "^ InputPlayer.StuntLast ^", "^ InputPlayer.StuntPoints);

		// Update Speed display
		LabelSpeed.Value = ""^ InputPlayer.DisplaySpeed;

		// Calculate the rotation for the Needle
		declare Real NeedleRotation = ((InputPlayer.EngineRpm / 12000.0) * 239.6) + 59.6;
		if (InputPlayer.EngineRpm >= 12000.0) {
			NeedleRotation = 299.2;
		}


		if (InputPlayer.RaceState != CTmMlPlayer::ERaceState::Finished && RestPlayTime >= 0 && RestPlayTime <= 15000) {
			BlinkNextChangeFuel = Blink("QuadIconFuel", BlinkNextChangeFuel, True);
			BlinkNextChangeTemp = Blink("QuadIconTemperature", BlinkNextChangeTemp, True);
		}
		else if (InputPlayer.RaceState != CTmMlPlayer::ERaceState::Finished && RestPlayTime >= 0 && RestPlayTime <= 30000) {
			BlinkNextChangeFuel = Blink("QuadIconFuel", BlinkNextChangeFuel, True);
			BlinkNextChangeTemp = Blink("QuadIconTemperature", BlinkNextChangeTemp, False);
		}
		else if (InputPlayer.RaceState != CTmMlPlayer::ERaceState::Finished && RestPlayTime >= 0 && RestPlayTime <= 60000) {
			BlinkNextChangeFuel = Blink("QuadIconFuel", BlinkNextChangeFuel, False);
		}
		else {
			QuadIconFuel.ModulateColor = TextLib::ToColor("FFFFFF");
			QuadIconTemperature.ModulateColor = TextLib::ToColor("FFFFFF");
		}

		// Store TopSpeed
		if (InputPlayer.DisplaySpeed > MeasuredTopSpeed) {
			MeasuredTopSpeed = InputPlayer.DisplaySpeed;
		}
		// Store TopRpm
		if (InputPlayer.EngineRpm > MeasuredTopRpm) {
			MeasuredTopRpm = InputPlayer.EngineRpm;
		}
		if (CurrentTime > RefreshTime) {
			// Check for max. width and reset if required
			if (TimeCount >= GraphStatistic.CoordsMax.X || InputPlayer.RaceState == CTmMlPlayer::ERaceState::BeforeStart) {
				declare ColorSpeed = Curves[0].Color;
				declare ColorAltitude = Curves[1].Color;

				GraphStatistic.RemoveCurve(Curves[0]);
				GraphStatistic.RemoveCurve(Curves[1]);
				Curves = [GraphStatistic.AddCurve(), GraphStatistic.AddCurve()];
				Curves[0].Color = ColorSpeed;
				Curves[1].Color = ColorAltitude;

				if (InputPlayer.RaceState == CTmMlPlayer::ERaceState::BeforeStart) {
					MeasuredTopSpeed = 0;
					MeasuredTopAltitude = 0.0;
				}
				TimeCount = 1;
			}

			// Store current Speed at timestamp
			Curves[0].Points.add(<(TimeCount + 0.00001), (InputPlayer.Speed * 3.6)>);

			// Compensate differences and store current Altitude
			declare Real Altitude = InputPlayer.Position.Y;
			if (Map.CollectionName == "Canyon") {
				Altitude += 0.005517;
			}
			else if (Map.CollectionName == "Stadium") {
				Altitude -= 9.01413;
			}
			else if (Map.CollectionName == "Valley") {
				Altitude -= 2.00138;
			}
			Curves[1].Points.add(<(TimeCount + 0.00001), (Altitude * 3)>);

			// Store TopAltitude
			if (Altitude > MeasuredTopAltitude) {
				MeasuredTopAltitude = Altitude;
			}

			// Build new label
			LabelSpeedStatistic.Value = "Top speed: "^ MeasuredTopSpeed ^" "^ TextLib::ToLowerCase(VelocityUnits[TachometerCurrentVelocityUnit]);
			LabelAltitudeStatistic.Value = "Top altitude: "^ MathLib::FloorInteger(MeasuredTopAltitude) ^" m";

			// Reset RefreshTime and update Counter
			RefreshTime = (CurrentTime + RefreshInterval);
			TimeCount += 1;
		}

		// Let the needle tremble a little bit depending on speed
//		QuadTachoneedle.RelativeRotation = NeedleRotation + MathLib::Rand(0.0, MathLib::Abs((((InputPlayer.Speed * 3.6) / 1000.0) * 4)));
		QuadTachoneedle.RelativeRotation = NeedleRotation + MathLib::Rand(0.0, MathLib::Abs(((InputPlayer.EngineRpm / 8000.0) * 2)));

		// Update RPM Indicators
		declare Real CurrentRpm = MathLib::Abs(InputPlayer.EngineRpm / 10);
		declare Integer ActiveId = MathLib::FloorInteger(CurrentRpm / 100.0) % 100;
		declare Integer Index = 0;
		for (Index, 0, ActiveId - 1) {
			declare CMlQuad QuadActive <=> (Page.GetFirstChild(QuadTachoscaleIds[Index]) as CMlQuad);
			QuadActive.Opacity = 1.0;
		}
		declare CMlQuad ActiveQuad <=> (Page.GetFirstChild(QuadTachoscaleIds[ActiveId]) as CMlQuad);
		ActiveQuad.Opacity = 0.01 * (CurrentRpm - (ActiveId * 100));
		for (Index, ActiveId + 1, 11) {
			declare CMlQuad QuadActive <=> (Page.GetFirstChild(QuadTachoscaleIds[Index]) as CMlQuad);
			QuadActive.Opacity = 0.0;
		}

		// Update Distance
		declare CurrentDistance = PadString( TextLib::ToText( MathLib::NearestInteger(InputPlayer.Distance / 10) ), 6);
		LabelDistance1.Value = TextLib::SubString(CurrentDistance, 0, 1);
		LabelDistance2.Value = TextLib::SubString(CurrentDistance, 1, 1);
		LabelDistance3.Value = TextLib::SubString(CurrentDistance, 2, 1);
		LabelDistance4.Value = TextLib::SubString(CurrentDistance, 3, 1);
		LabelDistance5.Value = TextLib::SubString(CurrentDistance, 4, 1);
		LabelDistance6.Value = TextLib::SubString(CurrentDistance, 5, 1);

		if (InputPlayer.EngineCurGear == 0 && (InputPlayer.Speed * 3.6) > 0.0) {
			if (LastGear != "N") {
				LastGear = "N";
				if (TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}
			LabelGear.Value = "N";
		}
		else if (InputPlayer.DisplaySpeed == 0) {
			if (LastGear != "P") {
				LastGear = "P";
				if (TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}
			LabelGear.Value = "P";
		}
		else if ((InputPlayer.Speed * 3.6) > 0.0) {
			if (LastGear != "D") {
				LastGear = "D";
				if (TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}

			// Calculate s gear
			LabelGear.Value = ""^ InputPlayer.EngineCurGear;
		}
		else if ((InputPlayer.Speed * 3.6) <= -0.1) {
			if (LastGear != "R") {
				LastGear = "R";
				if (TachometerSoundStatus == True) {
					SoundGearShift.Stop();
					SoundGearShift.Play();
					SoundDriveBackward.Play();
				}
			}
			LabelGear.Value = "R";
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlScriptEvent::Type::MouseOver : {
					if (Event.ControlId == "QuadIconStatistics") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
						QuadIconStatistics.ModulateColor = TextLib::ToColor("00AA00");
					}
					else if (Event.ControlId == "QuadIconSounds") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
						QuadIconSounds.ModulateColor = TextLib::ToColor("00AA00");
					}
					else if (Event.ControlId == "LabelVelocityUnit") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
						LabelVelocityUnit.TextColor = TextLib::ToColor("00AA00");
						LabelVelocityUnit.Opacity = 1.0;
					}
//					else if (Event.ControlId == "QuadTachoscale") {
//						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
//					}
				}
				case CMlScriptEvent::Type::MouseOut : {
					if (Event.ControlId == "QuadIconStatistics") {
						if (TachometerStatisticsStatus == True) {
							QuadIconStatistics.ModulateColor = TextLib::ToColor("50B7FF");
						}
						else {
							QuadIconStatistics.ModulateColor = TextLib::ToColor("FFFFFF");
						}
					}
					else if (Event.ControlId == "QuadIconSounds") {
						if (TachometerSoundStatus == True) {
							QuadIconSounds.ModulateColor = TextLib::ToColor("FFFFFF");
						}
						else {
							QuadIconSounds.ModulateColor = TextLib::ToColor("FF0000");
						}
					}
					else if (Event.ControlId == "LabelVelocityUnit") {
						LabelVelocityUnit.TextColor = TextLib::ToColor("FFFFFF");
						LabelVelocityUnit.Opacity = 0.65;
					}
				}
				case CMlScriptEvent::Type::MouseClick : {
					if (Event.ControlId == "QuadIconStatistics") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						if (TachometerStatisticsStatus == True) {
							TachometerStatisticsStatus = False;
							QuadIconStatistics.ModulateColor = TextLib::ToColor("00AA00");
						}
						else {
							TachometerStatisticsStatus = True;
							QuadIconStatistics.ModulateColor = TextLib::ToColor("FFFFFF");
						}
					}
					else if (Event.ControlId == "QuadIconSounds") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						if (TachometerSoundStatus == True) {
							SoundGearShift.Stop();
							SoundDriveBackward.Stop();
							TachometerSoundStatus = False;
							QuadIconSounds.ModulateColor = TextLib::ToColor("FF0000");
						}
						else {
							TachometerSoundStatus = True;
							QuadIconSounds.ModulateColor = TextLib::ToColor("FFFFFF");
						}
					}
					else if (Event.ControlId == "LabelVelocityUnit") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						TachometerCurrentVelocityUnit += 1;
						if (TachometerCurrentVelocityUnit > (VelocityUnits.count - 1)) {
							TachometerCurrentVelocityUnit = 0;
						}
						LabelVelocityUnit.Value = VelocityUnits[TachometerCurrentVelocityUnit];
					}
					else if (Event.ControlId == "QuadTachoscale") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						TachometerCurrentModulateColor += 1;
						if (TachometerCurrentModulateColor > (ModulateColors.count - 1)) {
							TachometerCurrentModulateColor = 0;
						}
						QuadTachoscale.ModulateColor = TextLib::ToColor(ModulateColors[TachometerCurrentModulateColor]);
					}
				}
			}
		}

		// Change visibility for the Statistics
		FrameSpeedStatistic.Visible = TachometerStatisticsStatus;
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'" name="'. $this->config['manialinkid'] .'" version="3">';
		if ($show === true) {
			$xml .= $this->config['tachometer']['template'];
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}
}

?>
