<?php
/*
 * Plugin: Checkpoints
 * ~~~~~~~~~~~~~~~~~~~
 * » Stores Checkpoint timing and displays a Checkpoint Widget with timings from
 *   local/dedimania records.
 * » Based upon plugin.checkpoints.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-08-07
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
 *  - includes/core/checkpoint.class.php
 *  - plugins/plugin.local_records.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginCheckpoint();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginCheckpoint extends Plugin {
	private $manialinkid;
	private $nbcheckpoints;
	private $textcolors;
	private $panelbg;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Stores Checkpoint timing and displays a Checkpoint Widget with timings from local/dedimania records.');

		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);

		// Register functions for events
		$this->registerEvent('onBeginMap',		'onBeginMap');
		$this->registerEvent('onEndMap',		'onEndMap');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onPlayerStartCountdown',	'onPlayerStartCountdown');
		$this->registerEvent('onPlayerCheckpoint',	'onPlayerCheckpoint');
		$this->registerEvent('onPlayerFinishLine',	'onPlayerFinishHandling');
		$this->registerEvent('onPlayerFinishLap',	'onPlayerFinishHandling');
		$this->registerEvent('onLocalRecord',		'onLocalRecord');


		$this->manialinkid			= 'PluginCheckpointWidget';
		$this->nbcheckpoints			= 0;

		$this->textcolors['default_checkpoint']	= 'DDEF';	// RGBA
		$this->textcolors['default_besttime']	= 'BBBF';	// RGBA
		$this->textcolors['time_improved']	= '3B3';	// RGB
		$this->textcolors['time_equal']		= '29F';	// RGB
		$this->textcolors['time_worse']		= 'F00';	// RGB

		$this->panelbg['style']			= 'BgsPlayerCard';
		$this->panelbg['substyle']		= 'BgCardSystem';
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// Clear all checkpoints
		foreach ($aseco->checkpoints as $login => $cp) {
			$aseco->checkpoints[$login]->best['cps'] = array();
			$aseco->checkpoints[$login]->current['cps'] = array();
			$aseco->checkpoints[$login]->best['finish'] = PHP_INT_MAX;

			if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
				$aseco->checkpoints[$login]->current['finish'] = 0;
			}
			else {
				$aseco->checkpoints[$login]->current['finish'] = PHP_INT_MAX;
			}
		}


		// Set local checkpoint references
		if ($aseco->settings['display_checkpoints']) {
			foreach ($aseco->checkpoints as $login => $cp) {
				$lrec = $aseco->checkpoints[$login]->tracking['local_records'] - 1;

				// Check for specific record
				if ($lrec + 1 > 0) {
					// If specific record unavailable, use last one
					if ($lrec > $aseco->plugins['PluginLocalRecords']->records->count() - 1) {
						$lrec = $aseco->plugins['PluginLocalRecords']->records->count() - 1;
					}
					$curr = $aseco->plugins['PluginLocalRecords']->records->getRecord($lrec);

					// Check for valid checkpoints
					if (!empty($curr->checkpoints) && $curr->score == end($curr->checkpoints)) {
						$aseco->checkpoints[$login]->best['finish'] = $curr->score;
						$aseco->checkpoints[$login]->best['cps'] = $curr->checkpoints;
					}
				}
				else if ($lrec + 1 == 0) {
					// Search for own/last record
					$lrec = 0;
					while ($lrec < $aseco->plugins['PluginLocalRecords']->records->count()) {
						$curr = $aseco->plugins['PluginLocalRecords']->records->getRecord($lrec++);
						if ($curr->player->login == $login) {
							break;
						}
					}

					// check for valid checkpoints
					if (!empty($curr->checkpoints) && $curr->score == end($curr->checkpoints)) {
						$aseco->checkpoints[$login]->best['finish'] = $curr->score;
						$aseco->checkpoints[$login]->best['cps'] = $curr->checkpoints;
					}
				}  // else = -1

				$cpid = 'START';
				if (isset($aseco->checkpoints[$login]) && count($aseco->checkpoints[$login]->best['cps']) > 0) {
					$diff = '0.000';
					$best = $aseco->formatTime($aseco->checkpoints[$login]->best['finish']);
				}
				else {
					$diff = '-.---';
					$best = '-.---';
				}
				$this->buildCheckpointWidget($login, $cpid, $diff, $best);
			}
		}

		// CP count only for Laps mode
		$this->nbcheckpoints = $map->nbcheckpoints;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $map) {
		$xml = '<manialink id="'. $this->manialinkid .'"></manialink>';
		$aseco->sendManialink($xml, false, 0);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Init
		$aseco->checkpoints[$player->login] = new Checkpoint();

		// Set first lap reference in Laps mode
		if ($aseco->server->gameinfo->mode == Gameinfo::LAPS) {
			$aseco->checkpoints[$player->login]->current['finish'] = 0;
		}
		if ($aseco->settings['display_checkpoints']) {
			// Set personal or default CPs
			if ($setup = $player->getCheckpointSettings()) {
				$aseco->checkpoints[$player->login]->tracking['local_records'] = $setup['localcps'];
				$aseco->checkpoints[$player->login]->tracking['dedimania_records'] = $setup['dedicps'];
			}
			else {
				if ($aseco->settings['auto_enable_cps']) {
					$aseco->checkpoints[$player->login]->tracking['local_records'] = 0;
				}
				if ($aseco->settings['auto_enable_dedicps']) {
					$aseco->checkpoints[$player->login]->tracking['dedimania_records'] = 0;
				}
			}
		}

		$cpid = 'START';
		if (count($aseco->checkpoints[$player->login]->best['cps']) > 0) {
			$diff = '0.000';
			$best = $aseco->formatTime($aseco->checkpoints[$player->login]->best['finish']);
		}
		else {
			$diff = '-.---';
			$best = '-.---';
		}
		$this->buildCheckpointWidget($player->login, $cpid, $diff, $best);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		// Store current settings from Player
		$player->setCheckpointSettings(
			$aseco->checkpoints[$player->login]->tracking['local_records'],
			$aseco->checkpoints[$player->login]->tracking['dedimania_records']
		);

		// free up memory
		unset($aseco->checkpoints[$player->login]);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerStartCountdown ($aseco, $login) {

		// Reset for next run in TimeAttack mode
		if ($aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK) {
			$aseco->checkpoints[$login]->current['cps'] = array();
		}

		$cpid = 'START';
		if (count($aseco->checkpoints[$login]->best['cps']) > 0) {
			$diff = '0.000';
			$best = $aseco->formatTime($aseco->checkpoints[$login]->best['finish']);
		}
		else {
			$diff = '-.---';
			$best = '-.---';
		}
		$this->buildCheckpointWidget($login, $cpid, $diff, $best);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $param = [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=CurrentLapTime, [6]=LapWaypointNumber
	public function onPlayerCheckpoint ($aseco, $param) {

		// If Stunts mode, bail out immediately
		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			return;
		}

		// If undefined login, bail out too
		if (!isset($aseco->checkpoints[$param[0]])) {
			return;
		}

		$login = $param[0];
		$time = $param[2];
		$cpid = $param[3];

		// Check for Laps mode
		if ($aseco->server->gameinfo->mode != Gameinfo::LAPS) {

			// check for cheated checkpoints:
			// non-positive time, wrong index, or time less than preceding one
//			if ($time <= 0 || $cpid != count($aseco->checkpoints[$login]->current['cps']) || ($cpid > 0 && $time < end($aseco->checkpoints[$login]->current['cps']))) {
//				if ($checkpoint_tests) {
//					$aseco->processCheater($login, $aseco->checkpoints[$login]->current['cps'], $param, -1);
//					return;
//				}
//			}

			// Store current checkpoint
			$aseco->checkpoints[$login]->current['cps'][($cpid-1)] = $time;
			ksort($aseco->checkpoints[$login]->current['cps']);

			// Check if displaying for this player, and for best checkpoints
			$this->checkUpdateCheckpointWidget($login, $cpid);

		}
//		else {
// TODO
//			// check for cheated checkpoints:
//			// non-positive time, negative index
//			if ($checkpt[2] <= 0 || $checkpt[4] < 0) {
//				if ($checkpoint_tests) {
//					$aseco->processCheater($login, $checkpoints[$login]->current['cps'], $checkpt, -1);
//					return;
//				}
//			}
//
//			// get relative CP in this lap
//			$relcheck = $checkpt[4] % $nbcheckpoints;
//
//			// check for cheated checkpoints:
//			// wrong index, time not more than reference, relative time less than preceding one
//			if ($relcheck != count($checkpoints[$login]->current['cps']) ||
//			    $checkpt[2] < $checkpoints[$login]->current['finish'] ||
//			    ($relcheck > 0 && $checkpt[2] - $checkpoints[$login]->current['finish'] < end($checkpoints[$login]->current['cps']))) {
//				if ($checkpoint_tests) {
//					$aseco->processCheater($login, $checkpoints[$login]->current['cps'], $checkpt, -1);
//					return;
//				}
//			}
//
//			// store current checkpoint for current lap, relative to reference
//			$checkpoints[$login]->current['cps'][$relcheck] = $checkpt[2] - $checkpoints[$login]->current['finish'];
//
//			// check for a completed lap
//			if ($checkpt[3] * $nbcheckpoints != $checkpt[4] + 1) {
//
//				// check if displaying for this player, and for best checkpoints
//				if ($checkpoints[$login]->tracking['local_records'] != -1 &&
//				    isset($checkpoints[$login]->best['cps'][$relcheck])) {
//
//					// check for improvement
//					$diff = $checkpoints[$login]->current['cps'][$relcheck] - $checkpoints[$login]->best['cps'][$relcheck];
//					if ($diff < 0) {
//						$diff = abs($diff);
//						$sign = '$00f-';  // blue
//					} else if ($diff == 0) {
//						$sign = '$00f';  // blue
//					} else {  // $diff > 0
//						$sign = '$f00+';  // red
//					}
//					$sec = floor($diff/1000);
//					$ths = $diff - ($sec * 1000);
//
//					// update CP panel
//					if ($aseco->settings['enable_cpsspec'] && !empty($checkpoints[$login]->spectators))
//						$aseco->plugins['PluginManialinks']->display_cpspanel($aseco, $login . ',' . implode(',', $checkpoints[$login]->spectators), $relcheck + 1,
//						                 $sign . sprintf('%d.%03d', $sec, $ths));
//					else
//						$aseco->plugins['PluginManialinks']->display_cpspanel($aseco, $login, $relcheck + 1,
//						                 $sign . sprintf('%d.%03d', $sec, $ths));
//				}
//
//			}
//			else {  // completed lap
//
//				// store current lap finish as reference for next lap
//				$checkpoints[$login]->current['finish'] = $checkpt[2];
//
//				// build a record object with the current lap information
//				$finish_item = new Record();
//				$finish_item->player = $aseco->server->players->getPlayer($login);
//				$finish_item->score = $checkpoints[$login]->current['cps'][$relcheck];
//				$finish_item->date = strftime('%Y-%m-%d %H:%M:%S');
//				$finish_item->map = clone $aseco->server->maps->current;
//				unset($finish_item->map->mx);	// reduce memory usage
//
//				// store current lap
//				if ($aseco->plugins['PluginRasp']->feature_stats) {
//					$rasp->insertTime($finish_item, implode(',', $checkpoints[$login]->current['cps']));
//				}
//
//				// process for local and Dedimania records
//				$finish_item->new = true;  // set lap 'Finish' flag
//				ldb_playerFinish($aseco, $finish_item);
//				$finish_item->new = true;  // ditto
//				if (function_exists('dedimania_playerfinish'))
//					dedimania_playerfinish($aseco, $finish_item);
//
//				// check for new best lap
//				$diff = $checkpoints[$login]->current['cps'][$relcheck] - $checkpoints[$login]->best['finish'];
//				if ($diff < 0) {
//					// store new best lap
//					$checkpoints[$login]->best['finish'] = $checkpoints[$login]->current['cps'][$relcheck];
//					$checkpoints[$login]->best['cps'] = $checkpoints[$login]->current['cps'];
//					// store timestamp for sorting in case of equal bests
//					$checkpoints[$login]->best['timestamp'] = microtime(true);
//				}
//
//				// check if displaying for this player, and not first lap
//				if ($checkpoints[$login]->tracking['local_records'] != -1 && $checkpt[4] + 1 >= $nbcheckpoints) {
//					// check for improvement
//					if ($diff < 0) {
//						$diff = abs($diff);
//						$sign = '$00f-';  // blue
//					} else if ($diff == 0) {
//						$sign = '$00f';  // blue
//					} else {  // $diff > 0
//						$sign = '$f00+';  // red
//					}
//					$sec = floor($diff/1000);
//					$ths = $diff - ($sec * 1000);
//
//					// indicate Lap Finish checkpoint
//					$relcheck = 'L';
//					// update CP panel
//					if ($aseco->settings['enable_cpsspec'] && !empty($checkpoints[$login]->spectators))
//						$aseco->plugins['PluginManialinks']->display_cpspanel($aseco, $login . ',' . implode(',', $checkpoints[$login]->spectators), $relcheck,
//						                 $sign . sprintf('%d.%03d', $sec, $ths));
//					else
//						$aseco->plugins['PluginManialinks']->display_cpspanel($aseco, $login, $relcheck,
//						                 $sign . sprintf('%d.%03d', $sec, $ths));
//				}
//
//				// reset for next lap
//				$checkpoints[$login]->current['cps'] = array();
//			}
//		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=CurrentLapTime, [6]=LapWaypointNumber
	public function onPlayerFinishHandling ($aseco, $param) {

		// If Stunts mode, bail out immediately
		if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			return;
		}

		// If undefined login, bail out too
		if (!isset($aseco->checkpoints[$param[0]])) {
			return;
		}

		$login = $param[0];
		$time = $param[2];
		$cpid = $param[3];

		// Check for Laps mode
		if ($aseco->server->gameinfo->mode != Gameinfo::LAPS) {

			// Store finish
			$aseco->checkpoints[$login]->current['finish'] = $time;

			// Store finish as checkpoint too
			$aseco->checkpoints[$login]->current['cps'][($cpid-1)] = $time;
			ksort($aseco->checkpoints[$login]->current['cps']);

			// Check if displaying for this player, and for best checkpoints
			$this->checkUpdateCheckpointWidget($login, $cpid);

			// Check for improvement and update
			if ($aseco->checkpoints[$login]->current['finish'] < $aseco->checkpoints[$login]->best['finish']) {
				$aseco->checkpoints[$login]->best['finish'] = $aseco->checkpoints[$login]->current['finish'];
				$aseco->checkpoints[$login]->best['cps'] = $aseco->checkpoints[$login]->current['cps'];
				// store timestamp for sorting in case of equal bests
				$aseco->checkpoints[$login]->best['timestamp'] = microtime(true);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLocalRecord ($aseco, $finish_item) {

		$login = $finish_item->player->login;

		// If previously tracking own/last local record, now track new one
		if (isset($aseco->checkpoints[$login]) && $aseco->checkpoints[$login]->tracking['local_records'] == 0 && $aseco->checkpoints[$login]->tracking['dedimania_records'] == -1) {
			$aseco->checkpoints[$login]->best['finish'] = $aseco->checkpoints[$login]->current['finish'];
			$aseco->checkpoints[$login]->best['cps'] = $aseco->checkpoints[$login]->current['cps'];
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkUpdateCheckpointWidget ($login, $cpid) {
		global $aseco;

		// Check if displaying for this player, and for best checkpoints
		if ($aseco->checkpoints[$login]->tracking['local_records'] != -1 && isset($aseco->checkpoints[$login]->best['cps'][($cpid-1)])) {

			// Check for improvement
			$diff = $aseco->checkpoints[$login]->current['cps'][($cpid-1)] - $aseco->checkpoints[$login]->best['cps'][($cpid-1)];
			if ($diff < 0) {
				$diff = abs($diff);
				$sign = '$'. $this->textcolors['time_improved'] .'-';
			}
			else if ($diff == 0) {
				$sign = '$'. $this->textcolors['time_equal'];
			}
			else {
				// $diff > 0
				$sign = '$'. $this->textcolors['time_worse'] .'+';
			}
			$sec = floor($diff/1000);
			$ths = $diff - ($sec * 1000);

			// Setup format of the time
			if ($sec >= 60) {
				$current_checkpoint_time = $aseco->formatTime($diff);
			}
			else {
				$current_checkpoint_time = sprintf('%d.%03d', $sec, $ths);
			}

			// Check for Finish checkpoint
			$cpcount = count($aseco->checkpoints[$login]->best['cps']);
			if ($cpid == $cpcount) {
				$best_checkpoint_time = $aseco->formatTime($aseco->checkpoints[$login]->best['cps'][$cpid-1]);
				$cpid = 'FINISH';
			}
			else {
				$best_checkpoint_time = $aseco->formatTime($aseco->checkpoints[$login]->best['cps'][($cpid-1)]);
				$cpid = 'CP'. $cpid;
			}

			// Update CheckpointWidget
			if ($aseco->settings['enable_cpsspec'] && !empty($aseco->checkpoints[$login]->spectators)) {
				$this->buildCheckpointWidget($login .','. implode(',', $aseco->checkpoints[$login]->spectators), $cpid, $sign . $current_checkpoint_time, $best_checkpoint_time);
			}
			else {
				$this->buildCheckpointWidget($login, $cpid, $sign . $current_checkpoint_time, $best_checkpoint_time);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildCheckpointWidget ($logins, $cp, $diff, $besttime) {
		global $aseco;

		// Build manialink
		$xml = '<manialink id="'. $this->manialinkid .'">';
		$xml .= '<frame posn="-7.9 -38.1 0">';
		$xml .= '<quad posn="0 0 0.01" sizen="16 4" style="'. $this->panelbg['style'] .'" substyle="'. $this->panelbg['substyle'] .'"/>';
		$xml .= '<label posn="8 -0.65 0.02" sizen="16 2.2" textsize="2" scale="0.8" halign="center" textcolor="'. $this->textcolors['default_checkpoint'] .'" text="$O'. $cp .': '. $diff .'"/>';
		$xml .= '<label posn="8 -2.5 0.02" sizen="16 2.2" textsize="1" scale="0.8" halign="center" textcolor="'. $this->textcolors['default_besttime'] .'" text="BEST '. $besttime .'"/>';
		$xml .= '</frame>';
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, $logins, 0);
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Handles cheating player.
	public function processCheater ($login, $checkpoints, $chkpt, $finish) {
		global $aseco;

		// Collect checkpoints
		$cps = '';
		foreach ($checkpoints as $cp) {
			$cps .= $aseco->formatTime($cp) . '/';
		}
		$cps = substr($cps, 0, strlen($cps)-1);  // strip trailing '/'

		// report cheat
		if ($finish == -1) {
			trigger_error('Cheat by ['. $login . '] detected! CPs: '. $cps .' Last: '. $aseco->formatTime($chkpt[2]) .' index: '. $chkpt[4], E_USER_WARNING);
		}
		else {
			trigger_error('Cheat by ['. $login .'] detected! CPs: '. $cps .' Finish: '. $aseco->formatTime($finish), E_USER_WARNING);
		}

		// check for valid player
		if (!$player = $aseco->server->players->getPlayer($login)) {
			trigger_error('[Player] Player object for ['. $login .'] not found!', E_USER_WARNING);
			return;
		}

		switch ($aseco->settings['cheater_action']) {

			case 1:  // set to spec
				$rtn = $aseco->client->query('ForceSpectator', $login, 1);
				if (!$rtn) {
					trigger_error('['. $aseco->client->getErrorCode() .'] ForceSpectator - '. $aseco->client->getErrorMessage(), E_USER_WARNING);
				}
				else {
					// allow spectator to switch back to player
					$rtn = $aseco->client->query('ForceSpectator', $login, 0);
				}
				// force free camera mode on spectator
				$aseco->client->addCall('ForceSpectatorTarget', array($login, '', 2));
				// free up player slot
				$aseco->client->addCall('SpectatorReleasePlayerSlot', array($login));

				// log console message
				$aseco->console('Cheater [{1} : {2}] forced into free spectator!', $login, $aseco->stripColors($player->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Cheater {#highlite}{1}$z$s{#admin} forced into spectator!',
					str_ireplace('$w', '', $player->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
				break;

			case 2:  // kick
				// log console message
				$aseco->console('Cheater [{1} : {2}] kicked!', $login, $aseco->stripColors($player->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Cheater {#highlite}{1}$z$s{#admin} kicked!',
					str_ireplace('$w', '', $player->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// kick the cheater
				$aseco->client->query('Kick', $login);
				break;

			case 3:  // ban (& kick)
				// log console message
				$aseco->console('Cheater [{1} : {2}] banned!', $login, $aseco->stripColors($player->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Cheater {#highlite}{1}$z$s{#admin} banned!',
					str_ireplace('$w', '', $player->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// update banned IPs file
				$aseco->banned_ips[] = $player->ip;
				$aseco->writeIPs();

				// ban the cheater and also kick him
				$aseco->client->query('Ban', $player->login);
				break;

			case 4:  // blacklist & kick
				// log console message
				$aseco->console('Cheater [{1} : {2}] blacklisted!', $login, $aseco->stripColors($player->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Cheater {#highlite}{1}$z$s{#admin} blacklisted!',
					str_ireplace('$w', '', $player->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// blacklist the cheater and then kick him
				$aseco->client->query('BlackList', $player->login);
				$aseco->client->query('Kick', $player->login);

				// update blacklist file
				$filename = $aseco->settings['blacklist_file'];
				$rtn = $aseco->client->query('SaveBlackList', $filename);
				if (!$rtn) {
					trigger_error('['. $aseco->client->getErrorCode() .'] SaveBlackList (kick) - '. $aseco->client->getErrorMessage(), E_USER_WARNING);
				}
				break;

			case 5:  // blacklist & ban
				// log console message
				$aseco->console('Cheater [{1} : {2}] blacklisted & banned!', $login, $aseco->stripColors($player->nickname, false));

				// show chat message
				$message = $aseco->formatText('{#server}» {#admin}Cheater {#highlite}{1}$z$s{#admin} blacklisted & banned!',
					str_ireplace('$w', '', $player->nickname)
				);
				$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));

				// update banned IPs file
				$aseco->banned_ips[] = $player->ip;
				$aseco->writeIPs();

				// blacklist & ban the cheater
				$aseco->client->query('BlackList', $player->login);
				$aseco->client->query('Ban', $player->login);

				// update blacklist file
				$filename = $aseco->settings['blacklist_file'];
				$rtn = $aseco->client->query('SaveBlackList', $filename);
				if (!$rtn) {
					trigger_error('['. $aseco->client->getErrorCode() .'] SaveBlackList (ban) - '. $aseco->client->getErrorMessage(), E_USER_WARNING);
				}
				break;

			default: // ignore
		}
	}
}

?>
