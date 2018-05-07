<?php
/*
 * Plugin: Player Infos
 * ~~~~~~~~~~~~~~~~~~~~
 * » Kick idle Players to let waiting spectators play.
 * » Based upon mistral.idlekick.php from XAseco2/1.03 written by Mistral and Xymph
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
	$_PLUGIN = new PluginMistralIdlekick();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMistralIdlekick extends Plugin {
	public $kick_player_after;
	public $kick_spectator_after;
	public $kick_spectators;
	public $force_spectator_first;
	public $reset_onchat;
	public $reset_oncheckpoint;
	public $reset_onfinish;
	public $messages;
	public $debug;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {
		global $aseco;

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Kick idle Players to let waiting spectators play.');

		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnectPrepare',	'onPlayerDisconnectPrepare');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onEndMap',			'onEndMap');

		if (!$settings = $aseco->parser->xmlToArray('config/mistral_idlekick.xml', true, true)) {
			trigger_error('[MistralIdlekick] Could not read/parse config file [config/mistral_idlekick.xml]!', E_USER_ERROR);
		}
		$settings = $settings['SETTINGS'];
		unset($settings['SETTINGS']);

		$this->kick_player_after		= (int)$settings['KICK_PLAYER_AFTER'][0];
		$this->kick_spectator_after		= (int)$settings['KICK_SPECTATOR_AFTER'][0];
		$this->kick_spectators			= $aseco->string2bool($settings['KICK_SPECTATORS'][0]);
		$this->force_spectator_first		= $aseco->string2bool($settings['FORCE_SPECTATOR_FIRST'][0]);
		$this->reset_onchat			= $aseco->string2bool($settings['RESET'][0]['CHAT'][0]);
		$this->reset_oncheckpoint		= $aseco->string2bool($settings['RESET'][0]['CHECKPOINT'][0]);
		$this->reset_onfinish			= $aseco->string2bool($settings['RESET'][0]['FINISH'][0]);

		$this->messages['idlekick_play']	= $settings['MESSAGES'][0]['IDLEKICK_PLAY'][0];
		$this->messages['idlespec_play']	= $settings['MESSAGES'][0]['IDLESPEC_PLAY'][0];
		$this->messages['idlekick_spec']	= $settings['MESSAGES'][0]['IDLEKICK_SPEC'][0];

		// Register required events
		if ($this->reset_onchat === true) {
			$this->registerEvent('onPlayerChat', 'onPlayerChat');
		}
		if ($this->reset_oncheckpoint === true) {
			$this->registerEvent('onPlayerCheckpoint', 'onPlayerCheckpoint');
		}
		if ($this->reset_onfinish === true) {
			$this->registerEvent('onPlayerFinish', 'onPlayerFinish');
		}

		// Do not touch:
		$this->debug				= false;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		$this->storePlayerData($player, 'IdleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] Player [{1}] initialised with "0".', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnectPrepare ($aseco, $player) {

		// Remove temporary Player data, do not need to be stored into the database.
		$this->removePlayerData($player, 'IdleCount');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		// If no check on chat use, bail out too
		if (!$this->reset_onchat) {
			return;
		}

		if ($player = $aseco->server->players->getPlayerByLogin($chat[1])) {
			$this->storePlayerData($player, 'IdleCount', 0);
			if ($this->debug) {
				$aseco->console('[MistralIdlekick] Player [{1}] reset on chat.', $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerCheckpoint ($aseco, $params) {

		// If no check on checkpoints, bail out
		if (!$this->reset_oncheckpoint) {
			return;
		}

		if ($player = $aseco->server->players->getPlayerByLogin($params['login'])) {
			$this->storePlayerData($player, 'IdleCount', 0);
			if ($this->debug) {
				$aseco->console('[MistralIdlekick] Player [{1}] reset on checkpoint.', $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// if no check on finishes, bail out
		if (!$this->reset_onfinish) {
			return;
		}

		$player = $aseco->server->players->getPlayerByLogin($finish_item->player_login);
		$this->storePlayerData($player, 'IdleCount', 0);
		if ($this->debug) {
			$aseco->console('[MistralIdlekick] Player [{1}] reset on finish.', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		foreach ($aseco->server->players->player_list as $player) {
			// Check for admin immunity
			if ($player->is_spectator ? $aseco->allowAbility($player, 'noidlekick_spec') : $aseco->allowAbility($player, 'noidlekick_play')) {
				continue;  // Go check next player
			}

			// Check for spectator kicking
			if ($this->kick_spectators || !$player->is_spectator) {
				$this->storePlayerData($player, 'IdleCount', ($this->getPlayerData($player, 'IdleCount') + 1));
			}
			if ($this->debug) {
				$aseco->console('[MistralIdlekick] Player [{1}] set to "{2}"', $player->login, $this->getPlayerData($player, 'IdleCount'));
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap ($aseco, $data) {

		foreach ($aseco->server->players->player_list as $player) {
			// Check for spectator or player map counts
			if ($this->getPlayerData($player, 'IdleCount') === ($player->is_spectator ? $this->kick_spectator_after : $this->kick_player_after)) {
				$dokick = false;
				if ($player->is_spectator) {
					$dokick = true;
					// Log console message
					$aseco->console('[MistralIdlekick] Spectator [{1}] after "{2}" map(s) without action.', $player->login, $this->kick_spectator_after);
					$message = $aseco->formatText($this->messages['idlekick_spec'],
						$player->nickname,
						$this->kick_spectator_after,
						($this->kick_spectator_after === 1 ? '' : 's')
					);
				}
				else {
					if ($this->force_spectator_first) {
						// Log console message
						$aseco->console('[MistralIdlekick] Set Player [{1}] after "{2}" map(s) without action as Spectator.', $player->login, $this->kick_player_after);
						$message = $aseco->formatText($this->messages['idlespec_play'],
							$player->nickname,
							$this->kick_player_after,
							($this->kick_player_after === 1 ? '' : 's')
						);

						try {
							// Force player into spectator
							$aseco->client->query('ForceSpectator', $player->login, 1);

							// Allow spectator to switch back to player
							$aseco->client->query('ForceSpectator', $player->login, 0);

							try {
								// Force free camera mode on spectator
								$aseco->client->addCall('ForceSpectatorTarget', $player->login, '', 2);
							}
							catch (Exception $exception) {
								$aseco->console('[MistralIdlekick] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectatorTarget');
							}
						}
						catch (Exception $exception) {
							$aseco->console('[MistralIdlekick] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectator');
						}
					}
					else {
						$dokick = true;

						// Log console message
						$aseco->console('[MistralIdlekick] Kick Player [{1}] after "{2}" map(s) without action.', $player->login, $this->kick_player_after);
						$message = $aseco->formatText($this->messages['idlekick_play'],
							$player->nickname,
							$this->kick_player_after,
							($this->kick_player_after === 1 ? '' : 's')
						);
					}
				}

				// Show chat message
				$aseco->sendChatMessage($message);

				// Kick idle player
				if ($dokick) {
					try {
						$aseco->client->query('Kick', $player->login);
					}
					catch (Exception $exception) {
						$aseco->console('[MistralIdlekick] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick');
					}
				}
			}
		}
	}
}

?>
