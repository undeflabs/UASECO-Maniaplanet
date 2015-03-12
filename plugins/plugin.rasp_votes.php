<?php
/*
 * Plugin: Rasp Votes
 * ~~~~~~~~~~~~~~~~~~
 * » Provides sophisticated chat-based voting features, similar to (and fully
 *   integrated with) MX /add votes.
 * » Based upon plugin.rasp_votes.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-02-28
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
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.map.php
 *  - plugins/plugin.rasp_jukebox.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginRaspVotes();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginRaspVotes extends Plugin {
	public $disabled_scoreboard;
	public $chatvote;

	public $num_laddervotes;
	public $num_replayvotes;
	public $num_skipvotes;

	public $r_expire_num;

	public $ta_expire_start;
	public $ta_show_num;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides sophisticated chat-based voting features, similar to (and fully integrated with) MX /add votes.');

		$this->addDependence('PluginRaspJukebox',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginMap',		Dependence::REQUIRED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onEndMap1',		'onEndMap1');		// use pre event before all other processing
		$this->registerEvent('onLoadingMap',		'onLoadingMap');
		$this->registerEvent('onPlayerConnect',		'onPlayerConnect');
		$this->registerEvent('onPlayerCheckpoint',	'onPlayerCheckpoint');
		$this->registerEvent('onPlayerDisconnect',	'onPlayerDisconnect');
		$this->registerEvent('onEndRound',		'onEndRound');


		$this->registerChatCommand('helpvote',	'chat_helpvote',	'Displays info about the chat-based votes',	Player::PLAYERS);
		$this->registerChatCommand('votehelp',	'chat_helpvote',	'Displays info about the chat-based votes',	Player::PLAYERS);
		$this->registerChatCommand('endround',	'chat_endround',	'Starts a vote to end current round',		Player::PLAYERS);
		$this->registerChatCommand('ladder',	'chat_ladder',		'Starts a vote to restart map for ladder',	Player::PLAYERS);
		$this->registerChatCommand('replay',	'chat_replay',		'Starts a vote to replay this map',		Player::PLAYERS);
		$this->registerChatCommand('skip',	'chat_skip',		'Starts a vote to skip this map',		Player::PLAYERS);
		$this->registerChatCommand('ignore',	'chat_ignore',		'Starts a vote to ignore a player',		Player::PLAYERS);
		$this->registerChatCommand('kick',	'chat_kick',		'Starts a vote to kick a player',		Player::PLAYERS);
		$this->registerChatCommand('cancel',	'chat_cancel',		'Cancels your current vote',			Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $data) {

		// Get settings
		$this->readSettings();

		// init votes
		$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
		$aseco->plugins['PluginRaspJukebox']->replays_counter = 0;

		$this->onEndMap1($aseco, null);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $data) {

		// check for ongoing chat vote
		if (!empty($this->chatvote)) {
			$aseco->console('[RaspVotes] Vote by {1} to {2} reset!',
				$this->chatvote['login'],
				$this->chatvote['desc']
			);
			$message = $this->messages['VOTE_CANCEL'][0];
			if ($this->vote_in_window) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, false));
			}
			else {
				$aseco->sendChatMessage($message);
			}
			$this->chatvote = array();  // $this->mxadd is already reset in rasp_newmap()
		}

		// reset counters
		$this->num_laddervotes = 0;
		$this->num_replayvotes = 0;
		$this->num_skipvotes = 0;

		// disable voting during scoreboard?
		if ($this->disable_while_sb) {
			$this->disabled_scoreboard = true;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $data) {

		// always enable voting after scoreboard
		$this->disabled_scoreboard = false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// if starting up, bail out immediately
		if ($aseco->startup_phase) {
			return;
		}

		// check for active voting system
		if ($this->feature_votes) {
			// show info message
			$message = $this->messages['VOTE_EXPLAIN'][0];

			// check for global explanation
			if ($this->global_explain == 2) {
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
			else if ($this->global_explain == 1) {  // just to the new player
				// strip 1 leading '>' to indicate a player message instead of system-wide
				$message = str_replace('{#server}»> ', '{#server}» ', $message);
				$aseco->sendChatMessage($message, $player->login);
			}  // == 0, no explanation
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		// check for ongoing vote
		if ($this->feature_votes && !empty($this->chatvote)) {
			// check for vote to kick this player
			if ($this->chatvote['type'] == 4 && $this->chatvote['target'] == $player->login) {
				$aseco->console('[RaspVotes] Vote by {1} to {2} reset!',
					$this->chatvote['login'],
					$this->chatvote['desc']
				);
				$message = $this->messages['VOTE_CANCEL'][0];
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
				$this->chatvote = array();
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndRound ($aseco) {

		// in TimeAttack/Laps/Stunts modes, bail out immediately
		// (ignoring the 1 EndRound event that happens at the end of the map)
		if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			return;
		}

		// expire an /endround vote immediately
		if (!empty($this->chatvote) && $this->chatvote['type'] == 0) {
			$message = $aseco->formatText($this->messages['VOTE_END'][0],
				$this->chatvote['desc'],
				'expired',
				'Server'
			);
			if ($this->vote_in_window) {
				$aseco->releaseEvent('onSendWindowMessage', array($message, false));
			}
			else {
				$aseco->sendChatMessage($message);
			}
			$this->chatvote = array();
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			// check for expiration limit
			$expire_limit = !empty($this->mxadd) ? $this->r_expire_limit[5] : $this->r_expire_limit[$this->chatvote['type']];
			if (++$this->r_expire_num >= $expire_limit) {
				// check for type of vote
				if (!empty($this->chatvote)) {
					$aseco->console('[RaspVotes] Vote by {1} to {2} expired!',
						$this->chatvote['login'],
						$this->chatvote['desc']
					);
					$message = $aseco->formatText($this->messages['VOTE_END'][0],
						$this->chatvote['desc'],
						'expired',
						'Server'
					);
					if ($this->vote_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, false));
					}
					else {
						$aseco->sendChatMessage($message);
					}
					$this->chatvote = array();
				}
				else {
					// !empty($this->mxadd)
					$aseco->console('[RaspVotes] Vote by {1} to add {2} expired!',
						$this->mxadd['login'],
						$aseco->stripColors($this->mxadd['name'], false)
					);
					$message = $aseco->formatText($this->messages['JUKEBOX_END'][0],
						$aseco->stripColors($this->mxadd['name']),
						'expired',
						'Server'
					);
					if ($this->jukebox_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, false));
					}
					else {
						$aseco->sendChatMessage($message);
					}
					$this->mxadd = array();
				}
			}
			else {
				// optionally remind players to vote
				if ($this->r_show_reminder) {
					// check for type of vote
					if (!empty($this->chatvote)) {
						$message = $aseco->formatText($this->messages['VOTE_Y'][0],
							$this->chatvote['votes'],
							($this->chatvote['votes'] == 1 ? '' : 's'),
							$this->chatvote['desc']
						);
						if ($this->vote_in_window) {
							$aseco->releaseEvent('onSendWindowMessage', array($message, false));
						}
						else {
							$aseco->sendChatMessage($message);
						}
					}
					else {
						// !empty($this->mxadd)
						$message = $aseco->formatText($this->messages['JUKEBOX_Y'][0],
							$this->mxadd['votes'],
							($this->mxadd['votes'] == 1 ? '' : 's'),
							$aseco->stripColors($this->mxadd['name'])
						);
						if ($this->jukebox_in_window) {
							$aseco->releaseEvent('onSendWindowMessage', array($message, false));
						}
						else {
							$aseco->sendChatMessage($message);
						}
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $param = [0]=Login, [1]=WaypointBlockId, [2]=Time [3]=WaypointIndex, [4]=CurrentLapTime, [6]=LapWaypointNumber
	public function onPlayerCheckpoint ($aseco, $data) {

		// in Rounds/Team/Cup modes, bail out immediately
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS || $aseco->server->gameinfo->mode == Gameinfo::TEAM || $aseco->server->gameinfo->mode == Gameinfo::CUP) {
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			// check for expiration limit
			$expire_limit = !empty($this->mxadd) ? $this->ta_expire_limit[5] : $this->ta_expire_limit[$this->chatvote['type']];
			$played = (time() - $aseco->server->maps->current->starttime);
			if (($played - $this->ta_expire_start) >= $expire_limit) {
				// check for type of vote
				if (!empty($this->chatvote)) {
					$aseco->console('[RaspVotes] Vote by {1} to {2} expired!',
						$this->chatvote['login'],
						$this->chatvote['desc']
					);
					$message = $aseco->formatText($this->messages['VOTE_END'][0],
						$this->chatvote['desc'],
						'expired',
						'Server'
					);
					if ($this->vote_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, false));
					}
					else {
						$aseco->sendChatMessage($message);
					}
					$this->chatvote = array();
				}
				else {
					// !empty($this->mxadd)
					$aseco->console('[RaspVotes] Vote by {1} to add {2} expired!',
						$this->mxadd['login'],
						$aseco->stripColors($this->mxadd['name'], false)
					);
					$message = $aseco->formatText($this->messages['JUKEBOX_END'][0],
						$aseco->stripColors($this->mxadd['name']),
						'expired',
						'Server'
					);
					if ($this->jukebox_in_window) {
						$aseco->releaseEvent('onSendWindowMessage', array($message, false));
					}
					else {
						$aseco->sendChatMessage($message);
					}
					$this->mxadd = array();
				}
			}
			else {
				// optionally remind players to vote
				if ($this->ta_show_reminder) {
					// compute how many $this->ta_show_interval's have passed
					$intervals = floor(($played - $this->ta_expire_start) / $this->ta_show_interval);

					// check whether this is more than the previous interval count
					if ($intervals > $this->ta_show_num) {
						// remember new interval count
						$this->ta_show_num = $intervals;

						// check for type of vote
						if (!empty($this->chatvote)) {
							$message = $aseco->formatText($this->messages['VOTE_Y'][0],
								$this->chatvote['votes'],
								($this->chatvote['votes'] == 1 ? '' : 's'),
								$this->chatvote['desc']
							);
							if ($this->vote_in_window) {
								$aseco->releaseEvent('onSendWindowMessage', array($message, false));
							}
							else {
								$aseco->sendChatMessage($message);
							}
						}
						else {
							// !empty($this->mxadd)
							$message = $aseco->formatText($this->messages['JUKEBOX_Y'][0],
								$this->mxadd['votes'],
								($this->mxadd['votes'] == 1 ? '' : 's'),
								$aseco->stripColors($this->mxadd['name'])
							);
							if ($this->jukebox_in_window) {
								$aseco->releaseEvent('onSendWindowMessage', array($message, false));
							}
							else {
								$aseco->sendChatMessage($message);
							}
						}
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_helpvote ($aseco, $login, $chat_command, $chat_parameter) {

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $login);
			return;
		}

		$header = '{#vote}Chat-based votes$g are available for these actions:';
		$data = array();
		$data[] = array('Ratio', '{#black}Command', '');
		$data[] = array($this->vote_ratios[0] * 100 . '%', '{#black}/endround',
		                'Starts a vote to end current round');
		$data[] = array($this->vote_ratios[1] * 100 . '%', '{#black}/ladder',
		                'Starts a vote to restart map for ladder');
		$data[] = array($this->vote_ratios[2] * 100 . '%', '{#black}/replay',
		                'Starts a vote to play this map again');
		$data[] = array($this->vote_ratios[3] * 100 . '%', '{#black}/skip',
		                'Starts a vote to skip this map');
		if ($this->allow_ignorevotes) {
		$data[] = array($this->vote_ratios[6] * 100 . '%', '{#black}/ignore',
		                'Starts a vote to ignore a player');
		}
		if ($this->allow_kickvotes) {
		$data[] = array($this->vote_ratios[4] * 100 . '%', '{#black}/kick',
		                'Starts a vote to kick a player');
		}
		$data[] = array('', '{#black}/cancel',
		                'Cancels your current vote');
		$data[] = array();
		$data[] = array('Players can vote with {#black}/y$g until the required number of votes');
		$data[] = array('is reached, or the vote expires.');

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $data, array(1.0, 0.1, 0.2, 0.7), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_endround ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for endround
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}End this Round';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for TimeAttack/Laps/Stunts modes
		if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK || $aseco->server->gameinfo->mode == Gameinfo::LAPS || $aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
			$message = '{#server}» {#error}Running {#highlite}$i ' .
			           str_replace('_', ' ', $aseco->server->gameinfo->getModeName()) .
			           '{#error} mode - end round disabled!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// start endround vote
		$this->chatvote['login'] = $login;
		$this->chatvote['nick'] = $player->nickname;
		$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[0]);
		$this->chatvote['type'] = 0;
		$this->chatvote['desc'] = 'End this Round';

		// reset votes
		$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
		// no need to reset $this->r_expire_num etc as vote expires automatically

		// compile & show chat message
		$message = $aseco->formatText($this->messages['VOTE_START'][0],
			$aseco->stripColors($this->chatvote['nick']),
			$this->chatvote['desc'],
			$this->chatvote['votes']
		);
		$message = str_replace('{br}', LF, $message);  // split long message
		if ($this->vote_in_window) {
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			$aseco->sendChatMessage($message);
		}

		// vote automatically for vote starter?
		if ($this->auto_vote_starter) {
			$aseco->releaseChatCommand('/y', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_ladder ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for ladder restart
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}Restart Map for Ladder';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether ladder votes are allowed
		if ($this->max_laddervotes == 0) {
			$message = '{#server}» {#error}Ladder restart votes not allowed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for max ladder vote limit
		if ($this->num_laddervotes >= $this->max_laddervotes) {
			$message = $aseco->formatText($this->messages['VOTE_LIMIT'][0],
				$this->max_laddervotes,
				'/ladder',
				($this->max_laddervotes == 1 ? '' : 's')
			);
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for mode-specific restrictions
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS && $this->r_points_limits) {
			// in Rounds mode, get points of first player & points limit
			$info = $aseco->client->query('GetCurrentRanking', 1, 0);
			$points = $info[0]['Score'];
			$info = $aseco->client->query('GetRoundPointsLimit');
			$limit = $info['CurrentValue'];

			// check whether to disable /ladder
			if ($points > ($limit * $this->r_ladder_max)) {
				$message = '{#server}» {#error}First player already has {#highlite}$i ' .
					$points .
					'{#error} points - too late for ladder restart!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK && $this->ta_time_limits) {
			// in TimeAttack mode, get map playing time & time limit
			$played = (time() - $aseco->server->maps->current->starttime);
			$info = $aseco->client->query('GetTimeAttackLimit');
			$limit = $info['CurrentValue'] / 1000;  // convert to seconds

			// check whether to disable /ladder
			if ($played > ($limit * $this->ta_ladder_max)) {
				$message = '{#server}» {#error}Map is already playing for {#highlite}$i ' .
					preg_replace('/^00:/', '', $aseco->formatTime($played * 1000, false)) .
					'{#error} minutes - too late for ladder restart!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}  // no restrictions in other modes

		// start ladder vote
		$this->num_laddervotes++;
		$this->chatvote['login'] = $player->login;
		$this->chatvote['nick'] = $player->nickname;
		$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[1]);
		$this->chatvote['type'] = 1;
		$this->chatvote['desc'] = 'Restart Map for Ladder';

		// reset votes, rounds counter, TA interval counter & start time
		$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
		$this->r_expire_num = 0;
		$this->ta_show_num = 0;
		$this->ta_expire_start = (time() - $aseco->server->maps->current->starttime);

		// compile & show chat message
		$message = $aseco->formatText($this->messages['VOTE_START'][0],
			$aseco->stripColors($this->chatvote['nick']),
			$this->chatvote['desc'],
			$this->chatvote['votes']
		);
		$message = str_replace('{br}', LF, $message);  // split long message
		if ($this->vote_in_window) {
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			$aseco->sendChatMessage($message);
		}

		// vote automatically for vote starter?
		if ($this->auto_vote_starter) {
			$aseco->releaseChatCommand('/y', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_replay ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for replay
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}Replay Map after Finish';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether replay votes are allowed
		if ($this->max_replayvotes == 0) {
			$message = '{#server}» {#error}Replay votes not allowed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for max replay vote limit
		if ($this->num_replayvotes >= $this->max_replayvotes) {
			$message = $aseco->formatText($this->messages['VOTE_LIMIT'][0],
				$this->max_replayvotes,
				'/replay',
				($this->max_replayvotes == 1 ? '' : 's')
			);
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for replay count limit
		if ($this->replays_limit > 0 && $aseco->plugins['PluginRaspJukebox']->replays_counter >= $this->replays_limit) {
			$message = $aseco->formatText($this->messages['NO_MORE_REPLAY'][0],
				$this->replays_limit,
				($this->replays_limit == 1 ? '' : 's')
			);
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check if map already in jukebox
		if (!empty($this->jukebox) && array_key_exists($aseco->server->maps->current->uid, $this->jukebox)) {
			$message = '{#server}» {#error}Map is already getting replayed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for mode-specific restrictions
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS && $this->r_points_limits) {
			// in Rounds mode, get points of first player & points limit
			$info = $aseco->client->query('GetCurrentRanking', 1, 0);
			$points = $info[0]['Score'];
			$info = $aseco->client->query('GetRoundPointsLimit');
			$limit = $info['CurrentValue'];

			// check whether to disable /replay
			if ($points < ($limit * $this->r_replay_min)) {
				$message = '{#server}» {#error}First player has only {#highlite}$i ' .
					$points .
					'{#error} points - too early for replay!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK && $this->ta_time_limits) {
			// in TimeAttack mode, get map playing time & time limit
			$played = (time() - $aseco->server->maps->current->starttime);
			$info = $aseco->client->query('GetTimeAttackLimit');
			$limit = $info['CurrentValue'] / 1000;  // convert to seconds

			// check whether to disable /replay
			if ($played < ($limit * $this->ta_replay_min)) {
				$message = '{#server}» {#error}Map is only playing for {#highlite}$i ' .
					preg_replace('/^00:/', '', $aseco->formatTime($played * 1000, false)) .
					'{#error} minutes - too early for replay!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}  // no restrictions in other modes

		// start replay vote
		$this->num_replayvotes++;
		$this->chatvote['login'] = $player->login;
		$this->chatvote['nick'] = $player->nickname;
		$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[2]);
		$this->chatvote['type'] = 2;
		$this->chatvote['desc'] = 'Replay Map after Finish';

		// reset votes, rounds counter, TA interval counter & start time
		$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
		$this->r_expire_num = 0;
		$this->ta_show_num = 0;
		$this->ta_expire_start = (time() - $aseco->server->maps->current->starttime);

		// compile & show chat message
		$message = $aseco->formatText($this->messages['VOTE_START'][0],
			$aseco->stripColors($this->chatvote['nick']),
			$this->chatvote['desc'],
			$this->chatvote['votes']
		);
		$message = str_replace('{br}', LF, $message);  // split long message
		if ($this->vote_in_window) {
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			$aseco->sendChatMessage($message);
		}

		// vote automatically for vote starter?
		if ($this->auto_vote_starter) {
			$aseco->releaseChatCommand('/y', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_skip ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for skip
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}Skip this Map';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether skip votes are allowed
		if ($this->max_skipvotes == 0) {
			$message = '{#server}» {#error}Skip votes not allowed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for max skip vote limit
		if ($this->num_skipvotes >= $this->max_skipvotes) {
			$message = $aseco->formatText($this->messages['VOTE_LIMIT'][0],
				$this->max_skipvotes,
				'/skip',
				($this->max_skipvotes == 1 ? '' : 's')
			);
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for mode-specific restrictions
		if ($aseco->server->gameinfo->mode == Gameinfo::ROUNDS && $this->r_points_limits) {
			// in Rounds mode, get points of first player & points limit
			$info = $aseco->client->query('GetCurrentRanking', 1, 0);
			$points = $info[0]['Score'];
			$info = $aseco->client->query('GetRoundPointsLimit');
			$limit = $info['CurrentValue'];

			// check whether to disable /skip
			if ($points > ($limit * $this->r_skip_max)) {
				$message = '{#server}» {#error}First player already has {#highlite}$i ' .
					$points .
					'{#error} points - too late for skip!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}
		else if ($aseco->server->gameinfo->mode == Gameinfo::TIME_ATTACK && $this->ta_time_limits) {
			// in TimeAttack mode, get map playing time & time limit
			$played = (time() - $aseco->server->maps->current->starttime);
			$info = $aseco->client->query('GetTimeAttackLimit');
			$limit = $info['CurrentValue'] / 1000;  // convert to seconds

			// check whether to disable /skip
			if ($played > ($limit * $this->ta_skip_max)) {
				$message = '{#server}» {#error}Map is already playing for {#highlite}$i ' .
					preg_replace('/^00:/', '', $aseco->formatTime($played * 1000, false)) .
					'{#error} minutes - too late for skip!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}  // no restrictions in other modes

		// start skip vote
		$this->num_skipvotes++;
		$this->chatvote['login'] = $player->login;
		$this->chatvote['nick'] = $player->nickname;
		$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[3]);
		$this->chatvote['type'] = 3;
		$this->chatvote['desc'] = 'Skip this Map';

		// reset votes, rounds counter, TA interval counter & start time
		$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
		$this->r_expire_num = 0;
		$this->ta_show_num = 0;
		$this->ta_expire_start = (time() - $aseco->server->maps->current->starttime);

		// compile & show chat message
		$message = $aseco->formatText($this->messages['VOTE_START'][0],
			$aseco->stripColors($this->chatvote['nick']),
			$this->chatvote['desc'],
			$this->chatvote['votes']
		);
		$message = str_replace('{br}', LF, $message);  // split long message
		if ($this->vote_in_window) {
			$aseco->releaseEvent('onSendWindowMessage', array($message, false));
		}
		else {
			$aseco->sendChatMessage($message);
		}

		// vote automatically for vote starter?
		if ($this->auto_vote_starter) {
			$aseco->releaseChatCommand('/y', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_ignore ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for ignore
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}Ignore a Player';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for permission to ignore
		if (!$this->allow_ignorevotes) {
			$message = '{#server}» {#error}Ignore votes not allowed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// get player information
		if ($target = $aseco->server->players->getPlayerParam($player, $chat_parameter)) {
			// check for admin ignore
			if ($this->allow_admin_ignore || !$aseco->isAnyAdmin($target)) {
				// start ignore vote
				$this->chatvote['login'] = $player->login;
				$this->chatvote['nick'] = $player->nickname;
				$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[6]);
				$this->chatvote['type'] = 6;
				$this->chatvote['desc'] = 'Ignore ' . $aseco->stripColors($target->nickname);
				$this->chatvote['target'] = $target->login;

				// reset votes, rounds counter, TA interval counter & start time
				$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
				$this->r_expire_num = 0;
				$this->ta_show_num = 0;
				$this->ta_expire_start = (time() - $aseco->server->maps->current->starttime);

				// compile & show chat message
				$message = $aseco->formatText($this->messages['VOTE_START'][0],
					$aseco->stripColors($this->chatvote['nick']),
					$this->chatvote['desc'],
					$this->chatvote['votes']
				);
				$message = str_replace('{br}', LF, $message);  // split long message
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// vote automatically for vote starter?
				if ($this->auto_vote_starter) {
					$aseco->releaseChatCommand('/y', $player->login);
				}
			}
			else {
				// expose naughty player ;)
				$message = $aseco->formatText($this->messages['NO_ADMIN_IGNORE'][0],
					$aseco->stripColors($player->nickname),
					$aseco->stripColors($target->nickname)
				);
				$aseco->sendChatMessage($message);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_kick ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for active voting system
		if (!$this->feature_votes) {
			$message = $this->messages['NO_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($this->disabled_scoreboard) {
			$message = $this->messages['NO_SB_VOTE'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether this player is spectator
		if (!$this->allow_spec_startvote && $player->isspectator) {
			$message = $this->messages['NO_SPECTATORS'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check whether available admin should be asked for kick
		if ($this->disable_upon_admin && $this->admin_online()) {
			$message = $this->messages['ASK_ADMIN'][0] . ' {#highlite}Kick a Player';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote) || !empty($this->mxadd)) {
			$message = $this->messages['VOTE_ALREADY'][0];
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check for permission to kick
		if (!$this->allow_kickvotes) {
			$message = '{#server}» {#error}Kick votes not allowed!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// get player information
		if ($target = $aseco->server->players->getPlayerParam($player, $chat_parameter)) {
			// check for admin kick
			if ($this->allow_admin_kick || !$aseco->isAnyAdmin($target)) {
				// start kick vote
				$this->chatvote['login'] = $player->login;
				$this->chatvote['nick'] = $player->nickname;
				$this->chatvote['votes'] = $this->required_votes($this->vote_ratios[4]);
				$this->chatvote['type'] = 4;
				$this->chatvote['desc'] = 'Kick ' . $aseco->stripColors($target->nickname);
				$this->chatvote['target'] = $target->login;

				// reset votes, rounds counter, TA interval counter & start time
				$aseco->plugins['PluginRaspJukebox']->plrvotes = array();
				$this->r_expire_num = 0;
				$this->ta_show_num = 0;
				$this->ta_expire_start = (time() - $aseco->server->maps->current->starttime);

				// compile & show chat message
				$message = $aseco->formatText($this->messages['VOTE_START'][0],
					$aseco->stripColors($this->chatvote['nick']),
					$this->chatvote['desc'],
					$this->chatvote['votes']
				);
				$message = str_replace('{br}', LF, $message);  // split long message
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}

				// vote automatically for vote starter?
				if ($this->auto_vote_starter) {
					$aseco->releaseChatCommand('/y', $player->login);
				}
			}
			else {
				// expose naughty player ;)
				$message = $aseco->formatText($this->messages['NO_ADMIN_KICK'][0],
					$aseco->stripColors($player->nickname),
					$aseco->stripColors($target->nickname)
				);
				$aseco->sendChatMessage($message);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_cancel ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// check for ongoing chat or MX vote
		if (!empty($this->chatvote)) {
			// check for vote ownership or admin
			if ($player->login == $this->chatvote['login'] || $aseco->allowAbility($player, 'cancel')) {
				$aseco->console('[RaspVotes] Vote to {1} cancelled by {2}!',
					$this->chatvote['desc'],
					$player->login
				);
				$message = $aseco->formatText($this->messages['VOTE_END'][0],
					$this->chatvote['desc'],
					'cancelled',
					$aseco->stripColors($player->nickname)
				);
				if ($this->vote_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
				$this->chatvote = array();
			}
			else {
				$message = '{#server}» {#error}You didn\'t start the current vote!';
				$aseco->sendChatMessage($message, $player->login);
			}
		}
		else if (!empty($this->mxadd)) {
			// check for vote ownership or admin
			if ($player->login == $this->mxadd['login'] || $aseco->allowAbility($player, 'cancel')) {
				$aseco->console('[RaspVotes] Vote to add {1} cancelled by {2}!',
					$aseco->stripColors($this->mxadd['name'], false),
					$player->login
				);
				$message = $aseco->formatText($this->messages['JUKEBOX_END'][0],
					$aseco->stripColors($this->mxadd['name']),
					'cancelled',
					$aseco->stripColors($player->nickname)
				);
				if ($this->jukebox_in_window) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
				$this->mxadd = array();
			}
			else {
				$message = '{#server}» {#error}You didn\'t start the current vote!';
				$aseco->sendChatMessage($message, $player->login);
			}
		}
		else {
			$message = '{#server}» {#error}There is no vote in progress!';
			$aseco->sendChatMessage($message, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Determine required number of votes
	public function required_votes ($ratio) {

		$numplrs = $this->active_players();

		// compute normal vote count
		if ($numplrs <= 7) {
			$votes = round($numplrs * $ratio);
		}
		else {
			$votes = floor($numplrs * $ratio);
		}

		// exceptions for low player count
		if ($votes == 0) {
			$votes = 1;  // needed for /y
		}
		else if ($numplrs >= 2 && $numplrs <= 3 && $votes == 1) {
			$votes = 2;  // minimum
		}

		return $votes;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Count players but not spectators
	public function active_players () {
		global $aseco;

		$total = 0;
		// check all connected players
		foreach ($aseco->server->players->player_list as $player) {
			// get current player status
			if ($this->allow_spec_voting || !$player->isspectator) {
				$total++;
			}
		}
		return $total;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Check whether there's an admin (any tier) online
	public function admin_online () {
		global $aseco;

		// check all connected players
		foreach ($aseco->server->players->player_list as $player) {
			// get current player status
			if ($aseco->isAnyAdmin($player)) {
				return true;
			}
		}
		return false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function readSettings () {
		global $aseco;

		$config_file = 'config/rasp.xml';	// Settings XML File
		if (file_exists($config_file)) {
			$aseco->console('[RaspVotes] Loading config file ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {

				/***************************** MESSAGES **************************************/
				$this->messages			= $xml['RASP']['MESSAGES'][0];

				/***************************** FEATURES **************************************/
				$this->feature_ranks		= $aseco->string2bool($xml['RASP']['FEATURE_RANKS'][0]);
				$this->nextrank_show_rp		= $aseco->string2bool($xml['RASP']['NEXTRANK_SHOW_POINTS'][0]);
				$this->feature_stats		= $aseco->string2bool($xml['RASP']['FEATURE_STATS'][0]);
				$this->always_show_pb		= $aseco->string2bool($xml['RASP']['ALWAYS_SHOW_PB'][0]);
				$this->feature_karma		= $aseco->string2bool($xml['RASP']['FEATURE_KARMA'][0]);
				$this->allow_public_karma	= $aseco->string2bool($xml['RASP']['ALLOW_PUBLIC_KARMA'][0]);
				$this->karma_show_start		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_START'][0]);
				$this->karma_show_details	= $aseco->string2bool($xml['RASP']['KARMA_SHOW_DETAILS'][0]);
				$this->karma_show_votes		= $aseco->string2bool($xml['RASP']['KARMA_SHOW_VOTES'][0]);
				$this->karma_require_finish	= $xml['RASP']['KARMA_REQUIRE_FINISH'][0];
				$this->remind_karma		= $xml['RASP']['REMIND_KARMA'][0];
				$this->feature_jukebox		= $aseco->string2bool($xml['RASP']['FEATURE_JUKEBOX'][0]);
				$this->feature_mxadd		= $aseco->string2bool($xml['RASP']['FEATURE_MXADD'][0]);
				$this->jukebox_skipleft		= $aseco->string2bool($xml['RASP']['JUKEBOX_SKIPLEFT'][0]);
				$this->jukebox_adminnoskip	= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINNOSKIP'][0]);
				$this->jukebox_permadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_PERMADD'][0]);
				$this->jukebox_adminadd		= $aseco->string2bool($xml['RASP']['JUKEBOX_ADMINADD'][0]);
				$this->jukebox_in_window	= $aseco->string2bool($xml['RASP']['JUKEBOX_IN_WINDOW'][0]);
				$this->reset_cache_start	= $aseco->string2bool($xml['RASP']['RESET_CACHE_START'][0]);
				$this->autosave_matchsettings	= $xml['RASP']['AUTOSAVE_MATCHSETTINGS'][0];
				$this->feature_votes		= $aseco->string2bool($xml['RASP']['FEATURE_VOTES'][0]);
				$this->prune_records_times	= $aseco->string2bool($xml['RASP']['PRUNE_RECORDS_TIMES'][0]);


				/***************************** PERFORMANCE VARIABLES ***************************/
				if (isset($xml['RASP']['MIN_RANK'][0])) {
					$this->minrank = $xml['RASP']['MIN_RANK'][0];
				}
				else {
					$this->minrank = 3;
				}

				if (isset($xml['RASP']['MAX_AVG'][0])) {
					$this->maxavg = $xml['RASP']['MAX_AVG'][0];
				}
				else {
					$this->maxavg = 10;
				}


				/***************************** JUKEBOX VARIABLES *******************************/
				$this->buffersize		= $xml['RASP']['BUFFER_SIZE'][0];
				$this->mxvoteratio		= $xml['RASP']['MX_VOTERATIO'][0];
				$this->mxdir			= $xml['RASP']['MX_DIR'][0];
				$this->mxtmpdir			= $xml['RASP']['MX_TMPDIR'][0];
				$this->maphistory_file		= $xml['RASP']['MAPHISTORY_FILE'][0];

				$this->jukebox			= array();
				$this->jb_buffer		= array();
				$this->mxadd			= array();
				$this->mxplaying		= false;
				$this->mxplayed			= false;


				/******************************* IRC VARIABLES *********************************/
				$this->irc = new stdClass();
				$this->irc->server		= $xml['RASP']['IRC_SERVER'][0];
				$this->irc->nick		= $xml['RASP']['IRC_BOTNICK'][0];
				$this->irc->port		= $xml['RASP']['IRC_PORT'][0];
				$this->irc->channel		= $xml['RASP']['IRC_CHANNEL'][0];
				$this->irc->name		= $xml['RASP']['IRC_BOTNAME'][0];
				$this->irc->show_connect	= $aseco->string2bool($xml['RASP']['IRC_SHOW_CONNECT'][0]);

				$this->irc->linesbuffer		= array();
				$this->irc->ircmsgs		= array();
				$this->irc->con			= array();



				/******************************* VOTES VARIABLES *********************************/
			  	$this->auto_vote_starter	= $aseco->string2bool($xml['RASP']['AUTO_VOTE_STARTER'][0]);
			  	$this->allow_spec_startvote	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_STARTVOTE'][0]);
			  	$this->allow_spec_voting	= $aseco->string2bool($xml['RASP']['ALLOW_SPEC_VOTING'][0]);

				// maximum number of rounds before a vote expires
			  	$this->r_expire_limit = array(
			  		0 => $xml['RASP']['R_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['R_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['R_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['R_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['R_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['R_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['R_EXPIRE_LIMIT_IGNORE'][0],
			  	);
		    		$this->r_show_reminder = $aseco->string2bool($xml['RASP']['R_SHOW_REMINDER'][0]);

			    	// maximum number of seconds before a vote expires
			  	$this->ta_expire_limit = array(
			  		0 => $xml['RASP']['TA_EXPIRE_LIMIT_ENDROUND'][0],
			  		1 => $xml['RASP']['TA_EXPIRE_LIMIT_LADDER'][0],
			  		2 => $xml['RASP']['TA_EXPIRE_LIMIT_REPLAY'][0],
			  		3 => $xml['RASP']['TA_EXPIRE_LIMIT_SKIP'][0],
			  		4 => $xml['RASP']['TA_EXPIRE_LIMIT_KICK'][0],
			  		5 => $xml['RASP']['TA_EXPIRE_LIMIT_ADD'][0],
			  		6 => $xml['RASP']['TA_EXPIRE_LIMIT_IGNORE'][0],
			  	);
				$this->ta_show_reminder = $aseco->string2bool($xml['RASP']['TA_SHOW_REMINDER'][0]);

				// interval length at which to (approx.) repeat reminder [s]
				$this->ta_show_interval = $xml['RASP']['TA_SHOW_INTERVAL'][0];

		  		// disable CallVotes
		  		$aseco->client->query('SetCallVoteRatio', 1.0);

		  		// really disable all CallVotes
		  		$ratios = array(array('Command' => '*', 'Ratio' => -1.0));
		  		$aseco->client->query('SetCallVoteRatios', $ratios);

				$this->global_explain = $xml['RASP']['GLOBAL_EXPLAIN'][0];

		  		// define the vote ratios for all types
		  		$this->vote_ratios = array(
		  			0 => $xml['RASP']['VOTE_RATIO_ENDROUND'][0],
		  			1 => $xml['RASP']['VOTE_RATIO_LADDER'][0],
		  			2 => $xml['RASP']['VOTE_RATIO_REPLAY'][0],
		  			3 => $xml['RASP']['VOTE_RATIO_SKIP'][0],
		  			4 => $xml['RASP']['VOTE_RATIO_KICK'][0],
		  			5 => $xml['RASP']['VOTE_RATIO_ADD'][0],
		  			6 => $xml['RASP']['VOTE_RATIO_IGNORE'][0],
		  		);

		  		$this->vote_in_window		= $aseco->string2bool($xml['RASP']['VOTE_IN_WINDOW'][0]);
		  		$this->disable_upon_admin	= $aseco->string2bool($xml['RASP']['DISABLE_UPON_ADMIN'][0]);
		  		$this->disable_while_sb		= $aseco->string2bool($xml['RASP']['DISABLE_WHILE_SB'][0]);

		   		// allow kicks & allow user to kick-vote any admin?
		  		$this->allow_kickvotes		= $aseco->string2bool($xml['RASP']['ALLOW_KICKVOTES'][0]);
		  		$this->allow_admin_kick		= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_KICK'][0]);

		  		// allow ignores & allow user to ignore-vote any admin?
		  		$this->allow_ignorevotes	= $aseco->string2bool($xml['RASP']['ALLOW_IGNOREVOTES'][0]);
		  		$this->allow_admin_ignore	= $aseco->string2bool($xml['RASP']['ALLOW_ADMIN_IGNORE'][0]);

		  		$this->max_laddervotes		= $xml['RASP']['MAX_LADDERVOTES'][0];
		  		$this->max_replayvotes		= $xml['RASP']['MAX_REPLAYVOTES'][0];
		  		$this->max_skipvotes		= $xml['RASP']['MAX_SKIPVOTES'][0];

		  		$this->replays_limit		= $xml['RASP']['REPLAYS_LIMIT'][0];

		  		$this->ladder_fast_restart	= $aseco->string2bool($xml['RASP']['LADDER_FAST_RESTART'][0]);

		  		$this->r_points_limits		= $aseco->string2bool($xml['RASP']['R_POINTS_LIMITS'][0]);
		  		$this->r_ladder_max		= $xml['RASP']['R_LADDER_MAX'][0];
		  		$this->r_replay_min		= $xml['RASP']['R_REPLAY_MIN'][0];
		  		$this->r_skip_max		= $xml['RASP']['R_SKIP_MAX'][0];

		  		$this->ta_time_limits		= $aseco->string2bool($xml['RASP']['TA_TIME_LIMITS'][0]);
		  		$this->ta_ladder_max		= $xml['RASP']['TA_LADDER_MAX'][0];
		  		$this->ta_replay_min		= $xml['RASP']['TA_REPLAY_MIN'][0];
		  		$this->ta_skip_max		= $xml['RASP']['TA_SKIP_MAX'][0];
			}
			else {
				trigger_error('Could not read/parse rasp config file ['. $config_file .']!', E_USER_WARNING);
			}
		}
		else {
			trigger_error('Could not find rasp config file ['. $config_file .']!', E_USER_WARNING);
		}
	}
}

?>
