<?php
/*
 * Plugin: Stunt Messages
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Displays Stunt messages at the top of the screen like "Free Style 360!!"...
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-23
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
 *  - none
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginStuntMessages();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginStuntMessages extends Plugin {
	private $manialinkid;

	public $db;
	public $text_colors;
	public $stunt_events;
	public $stunt_figures;
	public $jump_names;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays Stunt messages at the top of the screen like "Free Style 360!!"...');

		// Register functions for events
		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerStunt',		'onPlayerStunt');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onBeginMap',		'onBeginMap');
		$this->registerEvent('onEndMap1',		'onEndMap1');
		$this->registerEvent('onRestartMap',		'onRestartMap');
		$this->registerEvent('onPlayerStartCountdown',	'onPlayerStartCountdown');
//		$this->registerEvent('onPlayerRespawn',		'onPlayerRespawn');
		$this->registerEvent('onPlayerGiveUp',		'onPlayerGiveUp');

		$this->manialinkid				= 'StuntsMessage';
		$this->db					= array();

		// http://en.tm-wiki.org/wiki/Stunt_Mode
		$this->stunt_events = array(
			'::EStuntFigure::TimePenalty'		=> 'Time Penalty',
			'::EStuntFigure::RespawnPenalty'	=> 'Respawn Penalty',
			'::EStuntFigure::Reset'			=> 'Reset Penalty',
		);

		$this->stunt_figures = array(
			'::EStuntFigure::None'			=> 'No Stunt, Try Harder',
			'::EStuntFigure::StraightJump'		=> 'Straight Jump',
			'::EStuntFigure::Flip'			=> 'Flip',
			'::EStuntFigure::BackFlip'		=> 'Back Flip',
			'::EStuntFigure::Spin'			=> 'Spin',
			'::EStuntFigure::Aerial'		=> 'Aerial',
			'::EStuntFigure::AlleyOop'		=> 'Alley Oop',
			'::EStuntFigure::Roll'			=> 'Roll',
			'::EStuntFigure::Corkscrew'		=> 'Corkscrew',
			'::EStuntFigure::SpinOff'		=> 'Spin Off',
			'::EStuntFigure::Rodeo'			=> 'Rodeo',
			'::EStuntFigure::FlipFlap'		=> 'Flip Flap',
			'::EStuntFigure::Twister'		=> 'Twister',
			'::EStuntFigure::FreeStyle'		=> 'Free Style',
			'::EStuntFigure::SpinningMix'		=> 'Spinning Mix',
			'::EStuntFigure::FlippingChaos'		=> 'Flipping Chaos',
			'::EStuntFigure::RollingMadness'	=> 'Rolling Madness',
			'::EStuntFigure::WreckNone'		=> 'Wrecking',
			'::EStuntFigure::WreckStraightJump'	=> 'Wrecking Straight Jump',
			'::EStuntFigure::WreckFlip'		=> 'Wrecking Flip',
			'::EStuntFigure::WreckBackFlip'		=> 'Wrecking Back Flip',
			'::EStuntFigure::WreckSpin'		=> 'Wrecking Spin',
			'::EStuntFigure::WreckAerial'		=> 'Wrecking Aerial',
			'::EStuntFigure::WreckAlleyOop'		=> 'Wrecking Alley Oop',
			'::EStuntFigure::WreckRoll'		=> 'Wrecking Roll',
			'::EStuntFigure::WreckCorkscrew'	=> 'Wrecking Corkscrew',
			'::EStuntFigure::WreckSpinOff'		=> 'Wrecking Spin Off',
			'::EStuntFigure::WreckRodeo'		=> 'Wrecking Rodeo',
			'::EStuntFigure::WreckFlipFlap'		=> 'Wrecking Flip Flap',
			'::EStuntFigure::WreckTwister'		=> 'Wrecking Twister',
			'::EStuntFigure::WreckFreeStyle'	=> 'Wrecking Free Style',
			'::EStuntFigure::WreckSpinningMix'	=> 'Wrecking Spining Mix',
			'::EStuntFigure::WreckFlippingChaos'	=> 'Wrecking Flipping Chaos',
			'::EStuntFigure::WreckRollingMadness'	=> 'Wrecking Rolling Madness',
			'::EStuntFigure::Grind'			=> 'Grinding',
		);

		// Names with points range: 0-5, 5-10...
		// to replace from "Straight" Jump to have more different jumps
		$this->jump_names = array(
			0 => array(
				'Straight',
				'Basic',
				'Simple',
				'Easy',
				'Relaxed',
				'Slag',
				'Lightweight',
				'Soft',
				'Effortless',
				'Slight',
				'Modest',
				'Minor',
				'Moderate',
				'Not Worth Mentioning',
				'Humble',
			),
			5 => array(
				'Beautiful',
				'Pretty',
				'Nice',
				'Opulent',
				'Good',
				'Shining',
				'Lowly',
				'Sweet',
				'Cute',
				'Smart',
				'Lovely',
				'Cool',
				'Great',
				'Groovy',
				'Fine',
			),
			10 => array(
				'Correct',
				'Elegant',
				'Super',
				'Stunning',
				'Surprising',
				'Amazing',
				'Exotic',
				'Faultless',
				'Excellent',
				'Splendid',
				'Strange',
				'Weird',
				'Bizarre',
				'Wonderful',
				'Superb',
				'Marvelous',
				'Brilliant',
				'Sparkling',
				'Blinding',
			),
			15 => array(
				'Extensive',
				'Wide',
				'Major',
				'LoL',
				'ROFL',
				'Notable',
				'Breathtaking',
				'Spectacular',
				'Overwhelming',
				'Thrilling',
				'Sensational',
				'Exciting',
				'Outstanding',
				'Majestic',
				'Perfect',
			),
			20 => array(
				'Maniac',
				'Distinguished',
				'Demonstrative',
				'Captivating',
				'Gorgeous',
				'Fascinating',
				'Significant',
				'Epic',
				'Invincible',
				'Superior',
				'Unbeatable',
				'Superlative',
				'Fabulous',
				'Magnificent',
				'Phenomenal',
				'Insane',
				'Crazy',
				'Lunatic',
				'Loony',
				'Freaky',
				'Screwy',
			),
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$config = $aseco->parser->xmlToArray('config/stunt_messages.xml', true, true)) {
			trigger_error('[StuntMessages] Could not read/parse config file "config/stunt_messages.xml"!', E_USER_ERROR);
		}
		$config = $config['SETTINGS'];
		unset($config['SETTINGS']);

		$this->position['x']		= $config['POSITION'][0]['X'][0];
		$this->position['y']		= $config['POSITION'][0]['Y'][0];
		$this->position['z']		= $config['POSITION'][0]['Z'][0];

		$this->text_colors['stunts']	= $config['TEXT_COLORS'][0]['STUNTS'][0];
		$this->text_colors['points']	= $config['TEXT_COLORS'][0]['POINTS'][0];
		$this->text_colors['bonus']	= $config['TEXT_COLORS'][0]['BONUS'][0];
		$this->text_colors['penalty']	= $config['TEXT_COLORS'][0]['PENALTY'][0];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Init for Player
		$this->db[$player->login] = array(
			'total_points'	=> 0,
			'total_bonus'	=> 0,
			'total_penalty'	=> 0,
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

//	public function onPlayerRespawn ($aseco, $login) {
//		$this->hideWidget($login);
//	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerGiveUp ($aseco, $login) {
		// Reset all points
		$this->db[$login] = array(
			'total_points'	=> 0,
			'total_bonus'	=> 0,
			'total_penalty'	=> 0,
		);
//		$this->hideWidget($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerStartCountdown ($aseco, $login) {
		// Reset all points
		$this->db[$login] = array(
			'total_points'	=> 0,
			'total_bonus'	=> 0,
			'total_penalty'	=> 0,
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {
		$this->cleanDatabase();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {
		$this->cleanDatabase();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $map) {
		// TODO: Store into own database

		foreach ($aseco->server->players->player_list as $player) {
			$message = '{#record}» You made {#highlite}'. ($this->db[$player->login]['total_points'] + $this->db[$player->login]['total_bonus']) .'{#record} Stunt-Point'. ($this->db[$player->login]['total_points'] == 1 ? '' : 's');
			if ($this->db[$player->login]['total_bonus'] > 0) {
				$message .= ', {#highlite}'. $this->db[$player->login]['total_bonus'] .'{#record} Bonus-Point'. ($this->db[$player->login]['total_bonus'] == 1 ? '' : 's');
			}
			else {
				$message .= ', {#highlite}NO{#record} Bonus-Points';
			}
			if ($this->db[$player->login]['total_penalty'] > 0) {
				$message .= ' but lost {#highlite}'. $this->db[$player->login]['total_penalty'] .'{#record} Point'. ($this->db[$player->login]['total_bonus'] == 1 ? '' : 's') .' because of Penalties';
			}
			else {
				$message .= ' and {#highlite}NO{#record} Penalty-Points!!';
			}
			$message .= '!';
			$aseco->sendChatMessage($message, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=Login, [1]=StuntPoints, [2]=Combo, [3]=TotalStuntsScore, [4]=StuntFactor, [5]=StuntName,
	// [6]=StuntAngle, [7]=IsStraightStunt, [8]=IsStuntReversed, [9]=IsMasterJump
	public function onPlayerStunt ($aseco, $params) {

		// 2014-05-23: Bug that sends stunts too, if the server is at score:
		// Bail out if not at Server::RACE
		if ($aseco->server->gamestate == Server::SCORE) {
			return;
		}

		$stunt = array();
		if ( isset($this->stunt_events[$params[5]]) ) {
			$stunt[] = $this->stunt_events[$params[5]];

			// Setup "-" points
			$positive_points = false;
		}
		else {
			// Setup "+" points
			$positive_points = true;

			// Add "Combo"
			if ($params[2] == 1) {
				$stunt[] = 'Chained';
			}
			else if ($params[2] > 1) {
				$stunt[] = $params[2] .'X Chained';
			}

			// Add "StraightStunt", but not where containing already "Straight" in the figure
			if ( ($aseco->string2bool($params[7]) == true) && (($params[5] != '::EStuntFigure::StraightJump') && ($params[5] != '::EStuntFigure::WreckStraightJump')) ) {
				$stunt[] = 'Straight';
			}

			// Add "ReversedStunt"
			if ($aseco->string2bool($params[8]) == true) {
				$stunt[] = 'Reversed';
			}

			// Add "MasterJump"
			if ($aseco->string2bool($params[9]) == true) {
				$stunt[] = 'Master';
			}

			// Add "StuntFigure"
			if ($params[5] == '::EStuntFigure::StraightJump') {
				// Rename "Straight" to others name to have more figures and fun =)
				$range = 0;
				if ($params[1] < 5) {
					$range = 0;
				}
				else if ($params[1] < 10) {
					$range = 5;
				}
				else if ($params[1] < 15) {
					$range = 10;
				}
				else if ($params[1] < 20) {
					$range = 15;
				}
				else if ($params[1] >= 20) {
					$range = 20;
				}
				$rename = $this->stunt_figures[$params[5]];
				$stunt[] = str_replace('Straight', $this->jump_names[$range][mt_rand(0, count($this->jump_names[$range])-1)] , $rename);
			}
			else {
				$stunt[] = $this->stunt_figures[$params[5]];
			}

			// Add "StuntAngle"
			if ($params[6] > 0) {
				$stunt[] = $params[6];
			}
		}

		// Build manialink
		$points = array();
		$bonus = '';
		$xml = '<manialink id="'. $this->manialinkid .'" name="'. $this->manialinkid .'">';
		$xml .= '<frame posn="'. $this->position['x'] .' '. $this->position['y'] .' '. $this->position['z'] .'" id="'. $this->manialinkid .'Frame">';
		$xml .= '<label posn="0 0 0.01" sizen="60 2.2" textsize="3" style="TextButtonBig" scale="0.9" halign="center" textcolor="'. $this->text_colors['stunts'] .'" text="$S$O'. implode(' ', $stunt) .'!!" id="'. $this->manialinkid .'Name" hidden="true" opacity="0.0"/>';
		if ($params[1] > 0) {
			if ($positive_points == true) {
				$textcolor = $this->text_colors['points'];
				$this->db[$params[0]]['total_points'] += $params[1];
			}
			else {
				$textcolor = $this->text_colors['penalty'];
				$this->db[$params[0]]['total_points'] -= $params[1];
				if ($this->db[$params[0]]['total_points'] < 0) {
					$this->db[$params[0]]['total_points'] = 0;
				}
				$this->db[$params[0]]['total_penalty'] += $params[1];
			}

			// Add "StuntPoints"
			$points[] = (($positive_points == true) ? '+' : '-') . $params[1] .' '. (($params[1] == 1) ? 'point' : 'points');

			// Add "StuntFactor", but only on a higher amount of points
			if ($params[1] > 10 && $params[4] > 1 && $positive_points == true) {
				$sum = (ceil($params[1] * $params[4]) - $params[1]);
				$bonus = '+'. $sum .' Bonus '. (($sum == 1) ? 'Point' : 'Points') .'!!';
				$this->db[$params[0]]['total_bonus'] += $sum;
			}
		}
		$xml .= '<label posn="0 -2.3 0.01" sizen="50 2.2" textsize="2" scale="0.8" style="TextButtonNavBack" halign="center" textcolor="'. $textcolor .'" text="$S'. implode(' ', $points) .'" id="'. $this->manialinkid .'Points" hidden="true" opacity="0.0"/>';
		$xml .= '<label posn="0 -4 0.01" sizen="50 2.2" textsize="2" scale="0.6" style="TextButtonNavBack" halign="center" textcolor="'. $this->text_colors['bonus'] .'" text="$S'. $bonus .'" id="'. $this->manialinkid .'Bonus" hidden="true" opacity="0.0"/>';
		$xml .= '</frame>';
$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Widget:	StuntsMessage @ plugin.stunt_messages.php
 * License:	GPLv3
 * ----------------------------------
 */

Void StuntsMessageAnimateOut () {
	declare StuntsMessageName	<=> (Page.GetFirstChild("{$this->manialinkid}Name") as CMlLabel);
	declare StuntsMessagePoints	<=> (Page.GetFirstChild("{$this->manialinkid}Points") as CMlLabel);
	declare StuntsMessageBonus	<=> (Page.GetFirstChild("{$this->manialinkid}Bonus") as CMlLabel);

	while (StuntsMessageName.Opacity > 0.0) {
		if ((StuntsMessageName.Opacity - 0.05) < 0.0) {
			StuntsMessageName.Opacity = 0.0;
			StuntsMessagePoints.Opacity = 0.0;
			StuntsMessageBonus.Opacity = 0.0;
			break;
		}

		// FadeOut
		StuntsMessageName.Opacity -= 0.05;
		StuntsMessagePoints.Opacity -= 0.05;
		StuntsMessageBonus.Opacity -= 0.05;

		// ScrollDown
		StuntsMessageName.RelativePosition.Y -= 0.25;
		StuntsMessagePoints.RelativePosition.Y -= 0.25;
		StuntsMessageBonus.RelativePosition.Y -= 0.25;
		yield;
	}
}

Void StuntsMessageAnimateIn () {
	declare StuntsMessageFrame	<=> (Page.GetFirstChild("{$this->manialinkid}Frame") as CMlFrame);
	declare StuntsMessageName	<=> (Page.GetFirstChild("{$this->manialinkid}Name") as CMlLabel);
	declare StuntsMessagePoints	<=> (Page.GetFirstChild("{$this->manialinkid}Points") as CMlLabel);
	declare StuntsMessageBonus	<=> (Page.GetFirstChild("{$this->manialinkid}Bonus") as CMlLabel);
	declare Real FadeSteps		= 0.05;
	declare Real EndPosnY		= StuntsMessageFrame.RelativePosition.Y;

	// Set frame at top
	StuntsMessageFrame.RelativePosition.Y = 86.0;

	// Set visible
	StuntsMessageName.Visible = True;
	StuntsMessagePoints.Visible = True;
	StuntsMessageBonus.Visible = True;

	declare Real MovementSteps = ((StuntsMessageFrame.RelativePosition.Y - EndPosnY) / (1.0 / FadeSteps));
	while (StuntsMessageName.Opacity < 1.0) {
		if ((StuntsMessageName.Opacity + FadeSteps) > 1.0) {
			StuntsMessageName.Opacity = 1.0;
			StuntsMessagePoints.Opacity = 1.0;
			StuntsMessageBonus.Opacity = 1.0;

			StuntsMessageName.RelativePosition.Y -= MovementSteps;
			StuntsMessagePoints.RelativePosition.Y -= MovementSteps;
			StuntsMessageBonus.RelativePosition.Y -= MovementSteps;
			break;
		}

		// FadeIn
		StuntsMessageName.Opacity += FadeSteps;
		StuntsMessagePoints.Opacity += FadeSteps;
		StuntsMessageBonus.Opacity += FadeSteps;

		// ScrollDown
		StuntsMessageName.RelativePosition.Y -= MovementSteps;
		StuntsMessagePoints.RelativePosition.Y -= MovementSteps;
		StuntsMessageBonus.RelativePosition.Y -= MovementSteps;
		yield;
	}
}
main() {
	declare Integer StuntsMessageTimeOut = 5;
	declare Integer SecondsCounter = 0;
	declare PrevTime = CurrentLocalDateText;

	StuntsMessageAnimateIn();
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}
		if (PrevTime != CurrentLocalDateText) {
			PrevTime = CurrentLocalDateText;
			SecondsCounter += 1;
		}
		if (SecondsCounter >= StuntsMessageTimeOut) {
			StuntsMessageAnimateOut();
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, $params[0], 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function hideWidget ($login) {
		global $aseco;

		$xml = '<manialink id="'. $this->manialinkid .'"></manialink>';
		$aseco->sendManialink($xml, $login, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function cleanDatabase () {
		global $aseco;

		// Clean database
		unset($this->db);

		// Reset database
		foreach ($aseco->server->players->player_list as $player) {
			$this->db[$player->login] = array(
				'total_points'	=> 0,
				'total_bonus'	=> 0,
				'total_penalty'	=> 0,
			);
		}
	}
}

?>
