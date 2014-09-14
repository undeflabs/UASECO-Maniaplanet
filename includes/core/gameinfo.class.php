<?php
/*
 * Class: Gameinfo
 * ~~~~~~~~~~~~~~~
 * » Provides information to the current game which is running.
 * » Based upon types.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-14
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

class Gameinfo {
	public $mode;
	public $script;

	public $options;

	public $rounds;					// array()
	public $time_attack;				// array()
	public $team;					// array()
	public $laps;					// array()
	public $cup;					// array()
	public $team_attack;				// array()
//	public $stunts;					// array() unused

	const ROUNDS		= 1;
	const TIMEATTACK	= 2;
	const TEAM		= 4;
	const LAPS		= 8;
	const CUP		= 16;
	const TEAMATTACK	= 32;
	const STUNTS		= 64;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($aseco, $clone = false) {

		$aseco->client->query('GetCurrentGameInfo', 1);
		$gameinfo = $aseco->client->getResponse();

		if ($gameinfo['GameMode'] !== 0) {
			// Bail out if <playlist><gameinfos><game_mode> is not "0"
			trigger_error('[Gameinfo] UASECO can only be used for scripted Gamemodes! Please set in "UserData/Maps/MatchSettings/'. $aseco->settings['default_maplist'] .'" <playlist><gameinfos><game_mode> to "0" and <script_name> to e.g. "TimeAttack.Script.txt".', E_USER_ERROR);
		}


		// 2014-06-12: Name, CompatibleMapTypes, Description, Version, ParamDescs, CommandDescs
		$aseco->client->query('GetModeScriptInfo');
		$modescript['info'] = $aseco->client->getResponse();

		$aseco->client->query('GetModeScriptSettings');
		$modescript['settings'] = $aseco->client->getResponse();

//		$aseco->dump($gameinfo, $modescript['info'], $modescript['settings']);

		$this->script['Name']			= $modescript['info']['Name'];
		$this->script['Version']		= $modescript['info']['Version'];
		$this->script['CompatibleMapTypes']	= $modescript['info']['CompatibleMapTypes'];

		switch (str_replace('.Script.txt', '', $this->script['Name'])) {
			case 'Rounds':
				$this->mode = self::ROUNDS;
				break;

			case 'TimeAttack':
				$this->mode = self::TIMEATTACK;
				break;

			case 'Team':
				$this->mode = self::TEAM;
				break;

			case 'Laps':
				$this->mode = self::LAPS;
				break;

			case 'Cup':
				$this->mode = self::CUP;
				break;

			case 'TeamAttack':
				$this->mode = self::TEAMATTACK;
				break;

			case 'Stunts':
				$this->mode = self::STUNTS;
				break;

			default:
				$aseco->console('[Gameinfo] Not supported Modescript "'. $this->script['Name'] .'" loaded, please report this at '. UASECO_WEBSITE);
				break;
		}


		// ModeBase
		$this->options['UseScriptCallbacks']	= $modescript['settings']['S_UseScriptCallbacks'];
		$this->options['UseLegacyCallbacks']	= $modescript['settings']['S_UseLegacyCallbacks'];
		$this->options['ChatTime']		= $modescript['settings']['S_ChatTime'];
		$this->options['AllowRespawn']		= $modescript['settings']['S_AllowRespawn'];
		$this->options['WarmUpDuration']	= $modescript['settings']['S_WarmUpDuration'];
		$this->options['ScoresTableStylePath']	= $modescript['settings']['S_ScoresTableStylePath'];


		// http://maniaplanet.github.io/documentation/dedicated-server/settings-list.html
		if ($this->mode == self::ROUNDS) {
			// Rounds (+RoundsBase)
			if ( isset($clone->rounds['PointsRepartition']) ) {
				// Custom settings
				$this->rounds['PointsRepartition']	= $clone->rounds['PointsRepartition'];	// Refreshed every 'onBeginMap' event
			}
			else {
				// Dedicated defaults
				$this->rounds['PointsRepartition']	= array(10, 6, 4, 3, 2, 1);		// Refreshed every 'onBeginMap' event
			}
			if ($modescript['settings']['S_UseAlternateRules'] == true) {
				$this->rounds['UseAlternateRules']	= true;
				$this->rounds['PointsLimit']		= $modescript['settings']['S_PointsLimit'];
			}
			else {
				$this->rounds['UseAlternateRules']	= false;
				$this->rounds['PointsLimit']		= $modescript['settings']['S_PointsLimit'];
			}
			$this->rounds['ForceLapsNb']			= $modescript['settings']['S_ForceLapsNb'];
			$this->rounds['FinishTimeout']			= $modescript['settings']['S_FinishTimeout'];
			$this->rounds['DisplayTimeDiff']		= false;

			$this->rounds['UseTieBreak']			= $modescript['settings']['S_UseTieBreak'];
		}
		else if ($this->mode == self::TIMEATTACK) {
			// TimeAttack
			$this->time_attack['TimeLimit']			= $modescript['settings']['S_TimeLimit'];
		}
		else if ($this->mode == self::TEAM) {
			// Team  (+RoundsBase)
			if ( isset($clone->team['PointsRepartition']) ) {
				$this->team['PointsRepartition']	= $clone->team['PointsRepartition'];	// Refreshed every 'onBeginMap' event
			}
			else {
				$this->team['PointsRepartition']	= array(10, 6, 4, 3, 2, 1);		// Refreshed every 'onBeginMap' event
			}
			if ($modescript['settings']['S_UseAlternateRules'] == true) {
				$this->team['UseAlternateRules']	= true;
				$this->team['PointsLimit']		= $modescript['settings']['S_PointsLimit'];
			}
			else {
				$this->team['UseAlternateRules']	= false;
				$this->team['PointsLimit']		= $modescript['settings']['S_PointsLimit'];
			}
			$this->team['ForceLapsNb']			= $modescript['settings']['S_ForceLapsNb'];
			$this->team['FinishTimeout']			= $modescript['settings']['S_FinishTimeout'];
			$this->team['DisplayTimeDiff']			= false;

			$this->team['MaxPointsPerRound']		= $modescript['settings']['S_MaxPointsPerRound'];
			$this->team['PointsGap']			= $modescript['settings']['S_PointsGap'];
			$this->team['UsePlayerClublinks']		= $modescript['settings']['S_UsePlayerClublinks'];
		}
		else if ($this->mode == self::LAPS) {
			// Laps
			$this->laps['FinishTimeout']			= $modescript['settings']['S_FinishTimeout'];
			$this->laps['ForceLapsNb']			= $modescript['settings']['S_ForceLapsNb'];
			$this->laps['TimeLimit']			= $modescript['settings']['S_TimeLimit'];
		}
		else if ($this->mode == self::CUP) {
			// Cup (+RoundsBase)
			if ( isset($clone->cup['PointsRepartition']) ) {
				$this->cup['PointsRepartition']		= $clone->cup['PointsRepartition'];	// Refreshed every 'onBeginMap' event
			}
			else {
				$this->cup['PointsRepartition']		= array(10, 6, 4, 3, 2, 1);		// Refreshed every 'onBeginMap' event
			}
			$this->cup['PointsLimit']			= $modescript['settings']['S_PointsLimit'];
			$this->cup['DisplayTimeDiff']			= false;

			$this->cup['RoundsPerMap']			= $modescript['settings']['S_RoundsPerMap'];
			$this->cup['NbOfWinners']			= $modescript['settings']['S_NbOfWinners'];
			$this->cup['WarmUpDuration']			= $modescript['settings']['S_WarmUpDuration'];
		}
		else if ($this->mode == self::TEAMATTACK) {
			// TeamAttack
			$this->team_attack['TimeLimit']			= $modescript['settings']['S_TimeLimit'];
			$this->team_attack['MinPlayerPerClan']		= $modescript['settings']['S_MinPlayerPerClan'];
			$this->team_attack['MaxPlayerPerClan']		= $modescript['settings']['S_MaxPlayerPerClan'];
			$this->team_attack['MaxClanNb']			= $modescript['settings']['S_MaxClanNb'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns current Gamemode version (e.g. "2014-07-02")
	public function getGamemodeVersion () {
		return $this->script['Version'];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns current Gamemode Scriptname (e.g. "TimeAttack.Script.txt")
	public function getGamemodeScriptname () {
		return $this->script['Name'];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns current or given Gamemode as string
	public function getGamemodeName ($mode = false) {

		if ($mode === false) {
			$mode = $this->mode;
		}
		switch ($mode) {
			case self::ROUNDS:
				return 'Rounds';

			case self::TIMEATTACK:
				return 'TimeAttack';

			case self::TEAM:
				return 'Team';

			case self::LAPS:
				return 'Laps';

			case self::CUP:
				return 'Cup';

			case self::TEAMATTACK:
				return 'TeamAttack';

			case self::STUNTS:
				return 'Stunts';

			default:
				return 'Undefined';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns current or given Gamemode as Id
	public function getGamemodeId ($name = false) {

		if ($name === false) {
			$name = $this->getGamemodeName();
		}
		switch (strtolower($name)) {
			case 'rounds':
				return self::ROUNDS;

			case 'timeattack':
				return self::TIMEATTACK;

			case 'team':
				return self::TEAM;

			case 'laps':
				return self::LAPS;

			case 'cup':
				return self::CUP;

			case 'teamattack':
				return self::TEAMATTACK;

			case 'stunts':
				return self::STUNTS;

			default:
				return false;
		}
	}
}

?>
