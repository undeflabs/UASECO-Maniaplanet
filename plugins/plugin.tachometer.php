<?php
/*
 * Plugin: Tachometer
 * ~~~~~~~~~~~~~~~~~~
 * » Displays a smart tachometer on the HUD.
 *
 * ----------------------------------------------------------------------------------
 * Authors:	undef.de, reaby
 * Date:	2015-12-27
 * Copyright:	2014 - 2015 by undef.de
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
 *  - plugins/plugin.modescript_handler.php
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

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays a smart tachometer on the HUD.');

		$this->addDependence('PluginModescriptHandler',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onEndMap',			'onEndMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
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
			'position' => array(
				'x'					=> $aseco->formatFloat($settings['POSITION'][0]['X'][0]),
				'y'					=> $aseco->formatFloat($settings['POSITION'][0]['Y'][0]),
				'z'					=> $aseco->formatFloat($settings['POSITION'][0]['Z'][0]),
			),
			'sizes' => array(
				'scale'					=> $aseco->formatFloat($settings['SCALE'][0]),
				'background' => array(
					'x'				=> 95.5,
					'y'				=> 95.5,
				),
				'needle' => array(
					'x'				=> 87.625,
					'y'				=> 87.625,
				),
			),
			'images' => array(
				'background' 				=> $settings['IMAGES'][0]['BACKGROUND'][0],
				'needle'				=> $settings['IMAGES'][0]['NEEDLE'][0],
//				'needle'				=> 'http://maniacdn.net/undef.de/uaseco/tachometer/needle-dark.png',
//				'needle'				=> 'http://maniacdn.net/undef.de/uaseco/tachometer/needle-test.png',
				'scale' => array(
					'colorscale'			=> $settings['IMAGES'][0]['SCALE'][0]['COLORSCALE'][0],
					'overlay'			=> $settings['IMAGES'][0]['SCALE'][0]['OVERLAY'][0],
					'tiles' => array(
						1			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE01'][0],
						2			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE02'][0],
						3			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE03'][0],
						4			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE04'][0],
						5			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE05'][0],
						6			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE06'][0],
						7			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE07'][0],
						8			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE08'][0],
						9			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE09'][0],
						10			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE10'][0],
						11			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE11'][0],
						12			=> $settings['IMAGES'][0]['SCALE'][0]['TILES'][0]['TILE12'][0],
					),
				),
				'icons' => array(
					'sounds'			=> $settings['IMAGES'][0]['ICONS'][0]['SOUNDS'][0],
					'lights'			=> $settings['IMAGES'][0]['ICONS'][0]['LIGHTS'][0],
					'fuel'				=> $settings['IMAGES'][0]['ICONS'][0]['FUEL'][0],
					'temperature'			=> $settings['IMAGES'][0]['ICONS'][0]['TEMPERATURE'][0],
				),
			),
			'modulation' => array(
				'colorscale'				=> $settings['MODULATION'][0]['COLORSCALE'][0],
				'needle'				=> $settings['MODULATION'][0]['NEEDLE'][0],
				'overlay'				=> $settings['MODULATION'][0]['OVERLAY'][0],
				'tiles' => array(
					1				=> $settings['MODULATION'][0]['TILES'][0]['TILE01'][0],
					2				=> $settings['MODULATION'][0]['TILES'][0]['TILE02'][0],
					3				=> $settings['MODULATION'][0]['TILES'][0]['TILE03'][0],
					4				=> $settings['MODULATION'][0]['TILES'][0]['TILE04'][0],
					5				=> $settings['MODULATION'][0]['TILES'][0]['TILE05'][0],
					6				=> $settings['MODULATION'][0]['TILES'][0]['TILE06'][0],
					7				=> $settings['MODULATION'][0]['TILES'][0]['TILE07'][0],
					8				=> $settings['MODULATION'][0]['TILES'][0]['TILE08'][0],
					9				=> $settings['MODULATION'][0]['TILES'][0]['TILE09'][0],
					10				=> $settings['MODULATION'][0]['TILES'][0]['TILE10'][0],
					11				=> $settings['MODULATION'][0]['TILES'][0]['TILE11'][0],
					12				=> $settings['MODULATION'][0]['TILES'][0]['TILE12'][0],
				),
			),
			'sounds' => array(
				'drive_backward'			=> $settings['SOUNDS'][0]['DRIVE_BACKWARD'][0],
				'gear_shift'				=> $settings['SOUNDS'][0]['GEAR_SHIFT'][0],
			),
		);

		$this->config['manialinkid']				= 'Tachometer';

		// Disable parts of the UI
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('position', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('speed_and_distance', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfaceVisibility('personal_best_and_rank', false);
		$aseco->plugins['PluginModescriptHandler']->setUserInterfacePosition('countdown', array(105.0, -76.5, 5.0));

		// Send the UI settings
		$aseco->plugins['PluginModescriptHandler']->setupUserInterface();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		$xml = $this->buildTachometer(true);
		$aseco->sendManiaLink($xml, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		$xml = $this->buildTachometer(true);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {

		$xml = $this->buildTachometer(false);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		$xml = $this->buildTachometer(true);
		$aseco->sendManiaLink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildTachometer ($show = true) {
		global $aseco;

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Function:	Tachmeter @ plugin.tachometer.php
 * Authors:	undef.de, reaby
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
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
	declare persistent Boolean PersistentStorage_TachometerSoundStatus		= True;
	declare persistent Integer PersistentStorage_TachometerCurrentVelocityUnit	= 0;
	declare persistent Integer PersistentStorage_TachometerCurrentModulateColor	= 0;

	declare netread Integer Net_LibUI_SettingsUpdate for Teams[0];
	declare netread Text[Text] Net_LibUI_Settings for Teams[0];

	declare CMlFrame FrameTachometer	<=> (Page.GetFirstChild("FrameTachometer") as CMlFrame);

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

	declare CMlLabel LabelGearParking	<=> (Page.GetFirstChild("LabelGearParking") as CMlLabel);
	declare CMlLabel LabelGearReverse	<=> (Page.GetFirstChild("LabelGearReverse") as CMlLabel);
	declare CMlLabel LabelGearNeutral	<=> (Page.GetFirstChild("LabelGearNeutral") as CMlLabel);
	declare CMlLabel LabelGearDriving	<=> (Page.GetFirstChild("LabelGearDriving") as CMlLabel);

	declare CMlQuad QuadIconSounds		<=> (Page.GetFirstChild("QuadIconSounds") as CMlQuad);
	declare CMlQuad QuadIconLights		<=> (Page.GetFirstChild("QuadIconLights") as CMlQuad);
	declare CMlQuad QuadIconFuel		<=> (Page.GetFirstChild("QuadIconFuel") as CMlQuad);
	declare CMlQuad QuadIconTemperature	<=> (Page.GetFirstChild("QuadIconTemperature") as CMlQuad);

	declare SoundDriveBackward		= Audio.CreateSound("{$this->config['tachometer']['sounds']['drive_backward']}", 1.0, False, True, False);
	declare SoundGearShift			= Audio.CreateSound("{$this->config['tachometer']['sounds']['gear_shift']}", 1.0, False, False, False);
	declare Text LastGear			= "P";
	declare Text[] VelocityUnits		= ["KPH", "KP/H", "KMH", "KM/H", "MPH", "MP/H", "MIH", "MI/H", "SPH", "SP/H"];
	declare Text[] ModulateColors		= ["777777", "555555", "FFFFFF", "FEFFC5", "ACC720", "5E8ADB", "FFF700", "FFA700", "E74F3C", "F74BD3"];

	declare Integer RefreshInterval		= 100;
	declare Integer RefreshTime		= CurrentTime;
	declare Integer MeasuredTopSpeed	= 0;
	declare Integer TimeCount		= 1;

	declare PrevSettingsUpdate		= -1;
	declare CutOffTimeLimit			= -1;

	// Setup to the stored values
	LabelVelocityUnit.Value			= VelocityUnits[PersistentStorage_TachometerCurrentVelocityUnit];
	QuadTachoscale.ModulateColor		= TextLib::ToColor(ModulateColors[PersistentStorage_TachometerCurrentModulateColor]);

	// Turn sounds on/off
	if (PersistentStorage_TachometerSoundStatus == True) {
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

	LabelGearParking.Opacity		= 1.0;
	LabelGearReverse.Opacity		= 0.5;
	LabelGearNeutral.Opacity		= 0.5;
	LabelGearDriving.Opacity		= 0.5;

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

//	declare CMlGraph GraphStatistic			<=> (Page.GetFirstChild("GraphStatistic") as CMlGraph);
//	GraphStatistic.CoordsMin			= <0.0, -1200.0>;
//	GraphStatistic.CoordsMax			= <720.0, 7200.0>;
//
//	declare CMlGraphCurve[] Curves			= [GraphStatistic.AddCurve(), GraphStatistic.AddCurve()];
//	Curves[0].Color					= <0.9, 0.9, 0.9>;
//	Curves[1].Color					= <0.0, 7.0, 0.0>;
//
//	declare CMlLabel LabelSpeedStatistic		<=> (Page.GetFirstChild("LabelSpeedStatistic") as CMlLabel);
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
					case "Countdown_CutOffTimeLimit": {
						CutOffTimeLimit = TextLib::ToInteger(SettingValue);
					}
				}
			}
		}
		RestPlayTime = (CutOffTimeLimit - GameTime + 1);

		// Update Speed display
		LabelSpeed.Value = ""^ InputPlayer.DisplaySpeed;

		// Calculate the rotation for the Needle
		declare Real NeedleRotation = ((InputPlayer.DisplaySpeed / 1200.0) * 239.6) + 59.6;
		if (InputPlayer.DisplaySpeed >= 1200.0) {
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

//		// Store TopSpeed
//		if (InputPlayer.DisplaySpeed > MeasuredTopSpeed) {
//			MeasuredTopSpeed = InputPlayer.DisplaySpeed;
//		}
//		if (CurrentTime > RefreshTime) {
//			// Check for max. width and reset if required
//			if (TimeCount >= GraphStatistic.CoordsMax.X || InputPlayer.RaceState == CTmMlPlayer::ERaceState::BeforeStart) {
//				declare ColorSpeed = Curves[0].Color;
//				declare ColorAltitude = Curves[1].Color;
//
//				GraphStatistic.RemoveCurve(Curves[0]);
//				GraphStatistic.RemoveCurve(Curves[1]);
//				Curves = [GraphStatistic.AddCurve(), GraphStatistic.AddCurve()];
//				Curves[0].Color = ColorSpeed;
//				Curves[1].Color = ColorAltitude;
//
//				if (InputPlayer.RaceState == CTmMlPlayer::ERaceState::BeforeStart) {
//					MeasuredTopSpeed = 0;
//				}
//				TimeCount = 1;
//			}
//
//			// Store current Speed at timestamp
//			Curves[0].Points.add(<(TimeCount + 0.00001), (InputPlayer.Speed * 3.6)>);
//
//			// Compensate differences and store current Altitude
//			declare Real Altitude = InputPlayer.Position.Y;
//			if (Map.CollectionName == "Canyon") {
//				Altitude += 0.005517;
//			}
//			else if (Map.CollectionName == "Stadium") {
//				Altitude -= 9.01413;
//			}
//			else if (Map.CollectionName == "Valley") {
//				Altitude -= 2.00138;
//			}
//			Curves[1].Points.add(<(TimeCount + 0.00001), (Altitude * 3)>);
//			LabelSpeedStatistic.Value = "Top Speed: "^ MeasuredTopSpeed ^ VelocityUnits[PersistentStorage_TachometerCurrentVelocityUnit];
//
//			// Reset RefreshTime and update Counter
//			RefreshTime = (CurrentTime + RefreshInterval);
//			TimeCount += 1;
//		}

		// Let the needle tremble a little bit depending on speed
		QuadTachoneedle.RelativeRotation = NeedleRotation + MathLib::Rand(0.0, MathLib::Abs((((InputPlayer.Speed * 3.6) / 1000.0) * 4)));

		// Update Speed Indicators
		declare Real CurrentSpeed = MathLib::Abs(InputPlayer.Speed * 3.6);
		declare Integer ActiveId = MathLib::FloorInteger(CurrentSpeed / 100.0) % 100;
		declare Integer Index = 0;
		for (Index, 0, ActiveId - 1) {
			declare CMlQuad QuadActive <=> (Page.GetFirstChild(QuadTachoscaleIds[Index]) as CMlQuad);
			QuadActive.Opacity = 1.0;
		}
		declare CMlQuad ActiveQuad <=> (Page.GetFirstChild(QuadTachoscaleIds[ActiveId]) as CMlQuad);
		ActiveQuad.Opacity = 0.01 * (CurrentSpeed - (ActiveId * 100));
		for (Index, ActiveId + 1, 11) {
			declare CMlQuad QuadActive <=> (Page.GetFirstChild(QuadTachoscaleIds[Index]) as CMlQuad);
			QuadActive.Opacity = 0.0;
		}

		// Update Distance
		declare CurrentDistance = PadString( TextLib::ToText( MathLib::NearestInteger(InputPlayer.Distance) ), 6);
		LabelDistance1.Value = TextLib::SubString(CurrentDistance, 0, 1);
		LabelDistance2.Value = TextLib::SubString(CurrentDistance, 1, 1);
		LabelDistance3.Value = TextLib::SubString(CurrentDistance, 2, 1);
		LabelDistance4.Value = TextLib::SubString(CurrentDistance, 3, 1);
		LabelDistance5.Value = TextLib::SubString(CurrentDistance, 4, 1);
		LabelDistance6.Value = TextLib::SubString(CurrentDistance, 5, 1);

		if (InputPlayer.RaceState == CTmMlPlayer::ERaceState::Finished && InputPlayer.DisplaySpeed != 0) {
			if (LastGear != "N") {
				LastGear = "N";
				if (PersistentStorage_TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}
			LabelGearParking.Opacity = 0.5;
			LabelGearReverse.Opacity = 0.5;
			LabelGearNeutral.Opacity = 1.0;
			LabelGearDriving.Opacity = 0.5;
		}
		else if (InputPlayer.DisplaySpeed == 0) {
			if (LastGear != "P") {
				LastGear = "P";
				if (PersistentStorage_TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}
			LabelGearParking.Opacity = 1.0;
			LabelGearReverse.Opacity = 0.5;
			LabelGearNeutral.Opacity = 0.5;
			LabelGearDriving.Opacity = 0.5;
		}
		else if ((InputPlayer.Speed * 3.6) > 0.0) {
			if (LastGear != "D") {
				LastGear = "D";
				if (PersistentStorage_TachometerSoundStatus == True) {
					SoundDriveBackward.Stop();
					SoundGearShift.Stop();
					SoundGearShift.Play();
				}
			}
			LabelGearParking.Opacity = 0.5;
			LabelGearReverse.Opacity = 0.5;
			LabelGearNeutral.Opacity = 0.5;
			LabelGearDriving.Opacity = 1.0;
		}
		else if ((InputPlayer.Speed * 3.6) <= -0.1) {
			if (LastGear != "R") {
				LastGear = "R";
				if (PersistentStorage_TachometerSoundStatus == True) {
					SoundGearShift.Stop();
					SoundGearShift.Play();
					SoundDriveBackward.Play();
				}
			}
			LabelGearParking.Opacity = 0.5;
			LabelGearReverse.Opacity = 1.0;
			LabelGearNeutral.Opacity = 0.5;
			LabelGearDriving.Opacity = 0.5;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseOver : {
					if (Event.ControlId == "QuadIconSounds") {
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
				case CMlEvent::Type::MouseOut : {
					if (Event.ControlId == "QuadIconSounds") {
						if (PersistentStorage_TachometerSoundStatus == True) {
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
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "QuadIconSounds") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						if (PersistentStorage_TachometerSoundStatus == True) {
							SoundGearShift.Stop();
							SoundDriveBackward.Stop();
							PersistentStorage_TachometerSoundStatus = False;
							QuadIconSounds.ModulateColor = TextLib::ToColor("FF0000");
						}
						else {
							PersistentStorage_TachometerSoundStatus = True;
							QuadIconSounds.ModulateColor = TextLib::ToColor("FFFFFF");
							if (LastGear == "R") {
								SoundDriveBackward.Play();
							}
						}
					}
					else if (Event.ControlId == "LabelVelocityUnit") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						PersistentStorage_TachometerCurrentVelocityUnit += 1;
						if (PersistentStorage_TachometerCurrentVelocityUnit > (VelocityUnits.count - 1)) {
							PersistentStorage_TachometerCurrentVelocityUnit = 0;
						}
						LabelVelocityUnit.Value = VelocityUnits[PersistentStorage_TachometerCurrentVelocityUnit];
					}
					else if (Event.ControlId == "QuadTachoscale") {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
						PersistentStorage_TachometerCurrentModulateColor += 1;
						if (PersistentStorage_TachometerCurrentModulateColor > (ModulateColors.count - 1)) {
							PersistentStorage_TachometerCurrentModulateColor = 0;
						}
						QuadTachoscale.ModulateColor = TextLib::ToColor(ModulateColors[PersistentStorage_TachometerCurrentModulateColor]);
					}
				}
			}
		}
	}
}
--></script>
EOL;

		$xml = '<manialink id="'. $this->config['manialinkid'] .'" name="'. $this->config['manialinkid'] .'" version="1">';
		if ($show == true) {

//			$xml .= '<frame posn="-115 -15 20.01">';
//			$xml .= '<label posn="0 -40.3 0.05" sizen="60 4" textsize="1" scale="0.8" text="" id="LabelSpeedStatistic"/>';
////			$xml .= '<quad posn="0 25 0.01" sizen="230 75" bgcolor="AAA6"/>';
//			$xml .= '<quad posn="0 -39.2 0.02" sizen="230 0.2" bgcolor="0005"/>';
//			$xml .= '<graph posn="0 25 2.0" sizen="690 225" scale="'. (1.0 / 3) .'" id="GraphStatistic"/>';
//			$xml .= '</frame>';

			$xml .= '<frame posn="'. $this->config['tachometer']['position']['x'] .' '. $this->config['tachometer']['position']['y'] .' '. $this->config['tachometer']['position']['z'] .'" id="FrameTachometer">';
//			$xml .= '<quad posn="0 1.6 0.01" sizen="'. ($this->config['tachometer']['sizes']['background']['x'] + 8.5) .' '. ($this->config['tachometer']['sizes']['background']['y'] + 8.5) .'" halign="center" valign="center" image="'. $this->config['tachometer']['images']['background'] .'" id="QuadTachometer"/>';
			$xml .= '<quad posn="0 0 0.01" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" image="'. $this->config['tachometer']['images']['background'] .'" id="QuadTachometer"/>';
			$xml .= '<quad posn="0 -2.2 0.10" sizen="'. $this->config['tachometer']['sizes']['needle']['x'] .' '. $this->config['tachometer']['sizes']['needle']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['needle'] .'" image="'. $this->config['tachometer']['images']['needle'] .'" id="QuadTachoneedle"/>';
			$xml .= '<quad posn="0 0 0.03" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['colorscale'] .'" image="'. $this->config['tachometer']['images']['scale']['colorscale'] .'" id="QuadTachoscale" scriptevents="1"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][1] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][1] .'" id="QuadTachoscale01"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][2] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][2] .'" id="QuadTachoscale02"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][3] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][3] .'" id="QuadTachoscale03"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][4] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][4] .'" id="QuadTachoscale04"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][5] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][5] .'" id="QuadTachoscale05"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][6] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][6] .'" id="QuadTachoscale06"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][7] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][7] .'" id="QuadTachoscale07"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][8] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][8] .'" id="QuadTachoscale08"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][9] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][9] .'" id="QuadTachoscale09"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][10] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][10] .'" id="QuadTachoscale10"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][11] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][11] .'" id="QuadTachoscale11"/>';
			$xml .= '<quad posn="0 0 0.04" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['tiles'][12] .'" image="'. $this->config['tachometer']['images']['scale']['tiles'][12] .'" id="QuadTachoscale12"/>';
			$xml .= '<quad posn="0 0 0.05" sizen="'. $this->config['tachometer']['sizes']['background']['x'] .' '. $this->config['tachometer']['sizes']['background']['y'] .'" halign="center" valign="center" modulatecolor="'. $this->config['tachometer']['modulation']['overlay'] .'"image="'. $this->config['tachometer']['images']['scale']['overlay'] .'" id="QuadTachoscaleOverlay"/>';
			$xml .= '<label posn="0 14.4 0.04" sizen="20 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text=" " id="LabelVelocityUnit" scriptevents="1"/>';
			$xml .= '<label posn="0 6.8 0.04" sizen="80 20" halign="center" valign="center2" style="TextButtonBig" textsize="10" text="0" id="LabelSpeed"/>';

			$xml .= '<frame posn="-11.1 -1.9 0.04">';
			$xml .= '<label posn="0 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance1"/>';
			$xml .= '<label posn="4.5 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance2"/>';
			$xml .= '<label posn="9 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance3"/>';
			$xml .= '<label posn="13.5 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance4"/>';
			$xml .= '<label posn="18 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance5"/>';
			$xml .= '<label posn="22.5 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonSmall" textsize="2" text="0" id="LabelDistance6"/>';
			$xml .= '</frame>';

			$xml .= '<frame posn="-12 -10.8 0.04">';
			$xml .= '<label posn="0 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonMedium" textsize="3" text="P" id="LabelGearParking"/>';
			$xml .= '<label posn="8 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonMedium" textsize="3" text="R" id="LabelGearReverse"/>';
			$xml .= '<label posn="16 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonMedium" textsize="3" text="N" id="LabelGearNeutral"/>';
			$xml .= '<label posn="24 0 0.04" sizen="8 4" halign="center" valign="center2" style="TextButtonMedium" textsize="3" text="D" id="LabelGearDriving"/>';
			$xml .= '</frame>';

			$xml .= '<frame posn="-12 -21.5 0.04">';
			$xml .= '<quad posn="0 0 0.04" sizen="5.5 5.5" halign="center" valign="center" modulatecolor="888888" image="'. $this->config['tachometer']['images']['icons']['sounds'] .'" scriptevents="1" id="QuadIconSounds"/>';
			$xml .= '<quad posn="8 0 0.04" sizen="5.5 5.5" halign="center" valign="center" modulatecolor="888888" image="'. $this->config['tachometer']['images']['icons']['lights'] .'" scriptevents="1" id="QuadIconLights"/>';
			$xml .= '<quad posn="16 0 0.04" sizen="5.5 5.5" halign="center" valign="center" modulatecolor="FFFFFF" image="'. $this->config['tachometer']['images']['icons']['fuel'] .'" scriptevents="1" id="QuadIconFuel"/>';
			$xml .= '<quad posn="24 0 0.04" sizen="5.5 5.5" halign="center" valign="center" modulatecolor="FFFFFF" image="'. $this->config['tachometer']['images']['icons']['temperature'] .'" scriptevents="1" id="QuadIconTemperature"/>';
			$xml .= '</frame>';

			$xml .= '</frame>';
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';

		return $xml;
	}
}

?>
