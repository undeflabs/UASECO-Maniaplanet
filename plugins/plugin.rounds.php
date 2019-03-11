<?php
/*
 * Plugin: Rounds
 * ~~~~~~~~~~~~~~
 * » Reports finishes in each individual round.
 * » Based upon plugin.rounds.php from XAseco2/1.03 written by Xymph and .anDy
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
	$_PLUGIN = new PluginRounds();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRounds extends Plugin {
	public $rounds_count;
	public $round_times;
	public $round_pbs;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-03-11');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription('Reports finishes in each individual round.');

		$this->addDependence('PluginLocalRecords',	Dependence::REQUIRED,	'1.0.0', null);

		// Register functions for events
		$this->registerEvent('onBeginMap',	'resetRounds');
		$this->registerEvent('onRestartMap',	'resetRounds');
		$this->registerEvent('onEndRound',	'onEndRound');
		$this->registerEvent('onPlayerFinish',	'onPlayerFinish');

		$this->rounds_count	= 0;
		$this->round_times	= array();
		$this->round_pbs	= array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function resetRounds ($aseco, $response) {

		// Reset counter, times & PBs
		$this->rounds_count	= 0;
		$this->round_times	= array();
		$this->round_pbs	= array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco) {

		// If someone finished (in Rounds/Team/Cup mode), then report this round
		if (!empty($this->round_times)) {
			$this->rounds_count++;

			// Sort by times, PBs & PIDs
			$round_scores = array();

			ksort($this->round_times);
			foreach ($this->round_times as &$item){
				// Sort only times which were driven more than once
				if (count($item) > 1) {
					$scores = array();
					$pbs = array();
					$pids = array();
					foreach ($item as $key => &$row) {
						$scores[$key] = $row['score'];
						$pbs[$key] = $this->round_pbs[$row['login']];
						$pids[$key] = $row['playerid'];
					}
					// Sort order: SCORE, PB and PID, like the game does
					array_multisort($scores, SORT_NUMERIC, $pbs, SORT_NUMERIC, $pids, SORT_NUMERIC, $item);
				}
				// Merge all score arrays
				$round_scores = array_merge($round_scores, $item);
			}

			$pos = 1;
			$message = $aseco->formatText($aseco->getChatMessage('ROUND'), $this->rounds_count);

			// Report all new records, first 'show_min_recs' w/ time, rest w/o
			foreach ($round_scores as $tm) {
				// Check if player still online
				if ($player = $aseco->server->players->getPlayerByLogin($tm['login'])) {
					$nick = $aseco->stripStyles($player->nickname);
				}
				else {  // fall back on login
					$nick = $tm['login'];
				}
				$new = false;

				// Go through each record
				for ($i = 0; $i < $aseco->plugins['PluginLocalRecords']->records->count(); $i++) {
					$cur_record = $aseco->plugins['PluginLocalRecords']->records->getRecord($i);

					// if the record is new on this map then check if it's in this round
					if ($cur_record->new && $cur_record->player->login === $tm['login'] && $cur_record->score === $tm['score']) {
						$new = true;
						break;
					}
				}

				if ($new) {
					$message .= $aseco->formatText($aseco->getChatMessage('RANKING_RECORD_NEW'),
						$pos,
						$nick,
						$aseco->formatTime($tm['score'])
					);
				}
				else if ($pos <= $aseco->plugins['PluginLocalRecords']->settings['show_min_recs']) {
					$message .= $aseco->formatText($aseco->getChatMessage('RANKING_RECORD'),
						$pos,
						$nick,
						$aseco->formatTime($tm['score'])
					);
				}
				else {
					$message .= $aseco->formatText($aseco->getChatMessage('RANKING_RECORD2'),
						$pos,
						$nick
					);
				}
				$pos++;
			}

			// Show chat message
			$message = substr($message, 0, strlen($message)-2);  // strip trailing ", "
			$message = $aseco->formatColors($message);
//			$aseco->console('[Rounds] '. $aseco->stripStyles($message, false));

			if ($aseco->settings['rounds_in_window']) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, false));
			}
			else {
				$aseco->sendChatMessage($message);
			}

			// Reset times
			$this->round_times = array();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// if Rounds/Team/Cup mode & actual finish, then store time & PB
		if (($aseco->server->gameinfo->mode === Gameinfo::ROUNDS || $aseco->server->gameinfo->mode === Gameinfo::TEAM || $aseco->server->gameinfo->mode === Gameinfo::CUP) && $finish_item->score > 0) {

			$player = $aseco->server->players->getPlayerByLogin($finish_item->player_login);

			$this->round_times[$finish_item->score][] = array(
				'playerid'	=> $player->pid,
				'login'		=> $player->login,
				'score'		=> $finish_item->score,
			);
			if (isset($this->round_pbs[$player->login])) {
				if ($this->round_pbs[$player->login] > $finish_item->score) {
					$this->round_pbs[$player->login] = $finish_item->score;
				}
			}
			else {
				$this->round_pbs[$player->login] = $finish_item->score;
			}
		}
	}
}

?>
