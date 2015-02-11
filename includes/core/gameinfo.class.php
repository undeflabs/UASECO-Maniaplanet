<?php
/*
 * Class: Gameinfo
 * ~~~~~~~~~~~~~~~
 * » Provides information to the current game which is running.
 * » Based upon types.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-02-11
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
	public $chase;					// array()
//	public $stunts;					// array() unused

	const ROUNDS		= 1;
	const TIMEATTACK	= 2;
	const TEAM		= 3;
	const LAPS		= 4;
	const CUP		= 5;
	const TEAMATTACK	= 6;
	const CHASE		= 7;
	const STUNTS		= 8;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($aseco, $clone = false) {

		$gameinfo = $aseco->client->query('GetCurrentGameInfo', 1);

		if ($gameinfo['GameMode'] !== 0) {
			// Bail out if <playlist><gameinfos><game_mode> is not "0"
			trigger_error('[Gameinfo] UASECO can only be used for scripted Gamemodes! Please set in "UserData/Maps/MatchSettings/'. $aseco->settings['default_maplist'] .'" <playlist><gameinfos><game_mode> to "0" and <script_name> to e.g. "TimeAttack.Script.txt".', E_USER_ERROR);
		}


		// 2014-06-12: Name, CompatibleMapTypes, Description, Version, ParamDescs, CommandDescs
		$modescript['info'] = $aseco->client->query('GetModeScriptInfo');

		$modescript['settings'] = $aseco->client->query('GetModeScriptSettings');

// $aseco->dump($gameinfo, $modescript['info'], $modescript['settings']);

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

			case 'Chase':
				$this->mode = self::CHASE;
				break;

			case 'Stunts':
				$this->mode = self::STUNTS;
				break;

			default:
				$aseco->console('[Gameinfo] Unsupported Modescript "'. $this->script['Name'] .'" loaded, please report this at '. UASECO_WEBSITE);
				break;
		}


		// ModeBase
		$this->options['UseScriptCallbacks']	= $modescript['settings']['S_UseScriptCallbacks'];
		$this->options['UseLegacyCallbacks']	= $modescript['settings']['S_UseLegacyCallbacks'];
		$this->options['ChatTime']		= $modescript['settings']['S_ChatTime'];
		$this->options['AllowRespawn']		= $modescript['settings']['S_AllowRespawn'];
		$this->options['WarmUpDuration']	= $modescript['settings']['S_WarmUpDuration'];
		$this->options['ScoresTableStylePath']	= $modescript['settings']['S_ScoresTableStylePath'];


		// http://doc.maniaplanet.com/dedicated-server/settings-list.html
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
		else if ($this->mode == self::CHASE) {
			// Chase
			$this->chase['TimeLimit']			= $modescript['settings']['S_TimeLimit'];
			$this->chase['PointsLimit']			= $modescript['settings']['S_PointsLimit'];
			$this->chase['PointsGap']			= $modescript['settings']['S_PointsGap'];
			$this->chase['GiveUpMax']			= $modescript['settings']['S_GiveUpMax'];
			$this->chase['MinPlayersNb']			= $modescript['settings']['S_MinPlayersNb'];
			$this->chase['ForceLapsNb']			= $modescript['settings']['S_ForceLapsNb'];
			$this->chase['FinishTimeout']			= $modescript['settings']['S_FinishTimeout'];
			$this->chase['UsePlayerClublinks']		= $modescript['settings']['S_UsePlayerClublinks'];
			$this->chase['NbPlayersPerTeamMax']		= $modescript['settings']['S_NbPlayersPerTeamMax'];
			$this->chase['NbPlayersPerTeamMin']		= $modescript['settings']['S_NbPlayersPerTeamMin'];
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

	// Returns current or given Gamemode Scriptname (e.g. "TimeAttack.Script.txt")
	public function getGamemodeScriptname ($id = false) {
		if ($id === false) {
			return $this->script['Name'];
		}
		switch ($id) {
			case self::ROUNDS:
				return 'Rounds.Script.txt';

			case self::TIMEATTACK:
				return 'TimeAttack.Script.txt';

			case self::TEAM:
				return 'Team.Script.txt';

			case self::LAPS:
				return 'Laps.Script.txt';

			case self::CUP:
				return 'Cup.Script.txt';

			case self::TEAMATTACK:
				return 'TeamAttack.Script.txt';

			case self::CHASE:
				return 'Chase.Script.txt';

			case self::STUNTS:
				return 'Stunts.Script.txt';

			default:
				return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Returns current or given Gamemode as string
	public function getGamemodeName ($id = false) {

		if ($id === false) {
			$id = $this->mode;
		}
		switch ($id) {
			case self::ROUNDS:
				return 'Rounds';

			case self::TIMEATTACK:
				return 'Time Attack';

			case self::TEAM:
				return 'Team';

			case self::LAPS:
				return 'Laps';

			case self::CUP:
				return 'Cup';

			case self::TEAMATTACK:
				return 'Team Attack';

			case self::CHASE:
				return 'Chase';

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

			case 'time attack':
				return self::TIMEATTACK;

			case 'team':
				return self::TEAM;

			case 'laps':
				return self::LAPS;

			case 'cup':
				return self::CUP;

			case 'team attack':
				return self::TEAMATTACK;

			case 'chase':
				return self::CHASE;

			case 'stunts':
				return self::STUNTS;

			default:
				return false;
		}
	}
}

?>
