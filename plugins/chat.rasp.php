<?php
/*
 * Plugin: Chat Rasp
 * ~~~~~~~~~~~~~~~~~
 * » Provides private messages and a wide variety of shout-outs.
 * » Based upon plugin.rasp_chat.php from XAseco2/1.03 written by Xymph and others
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
 * Dependencies:
 * » plugins/plugin.rasp.php
 * » plugins/chat.admin.php
 * » plugins/plugin.manialinks.php
 * » plugins/plugin.muting.php
 * » plugins/plugin.welcome_center.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginChatRasp();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatRasp extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-10-03');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('chat.rasp', 'plugin_description'));
		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginChatAdmin',		Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginMuting',		Dependence::WANTED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');

		$this->registerChatCommand('pm',	'chat_pm',		new Message('chat.rasp', 'slash_pm_description'),		Player::PLAYERS);
		$this->registerChatCommand('pma',	'chat_pma',		new Message('chat.rasp', 'slash_pma_description'),		Player::PLAYERS);
		$this->registerChatCommand('pmlog',	'chat_pmlog',		new Message('chat.rasp', 'slash_pmlog_description'),		Player::PLAYERS);
		$this->registerChatCommand('hi',	'chat_hi',		new Message('chat.rasp', 'slash_hi_description'),		Player::PLAYERS);
		$this->registerChatCommand('bye',	'chat_bye',		new Message('chat.rasp', 'slash_bye_description'),		Player::PLAYERS);
		$this->registerChatCommand('thx',	'chat_thx',		new Message('chat.rasp', 'slash_thx_description'),		Player::PLAYERS);
		$this->registerChatCommand('lol',	'chat_lol',		new Message('chat.rasp', 'slash_lol_description'),		Player::PLAYERS);
		$this->registerChatCommand('lool',	'chat_lool',		new Message('chat.rasp', 'slash_lool_description'),		Player::PLAYERS);
		$this->registerChatCommand('brb',	'chat_brb',		new Message('chat.rasp', 'slash_brb_description'),		Player::PLAYERS);
		$this->registerChatCommand('afk',	'chat_afk',		new Message('chat.rasp', 'slash_afk_description'),		Player::PLAYERS);
		$this->registerChatCommand('gg',	'chat_gg',		new Message('chat.rasp', 'slash_gg_description'),		Player::PLAYERS);
		$this->registerChatCommand('gr',	'chat_gr',		new Message('chat.rasp', 'slash_gr_description'),		Player::PLAYERS);
		$this->registerChatCommand('n1',	'chat_n1',		new Message('chat.rasp', 'slash_n1_description'),		Player::PLAYERS);
		$this->registerChatCommand('bgm',	'chat_bgm',		new Message('chat.rasp', 'slash_bgm_description'),		Player::PLAYERS);
		$this->registerChatCommand('dammit',	'chat_dammit',		new Message('chat.rasp', 'slash_dammit_description'),		Player::PLAYERS);
		$this->registerChatCommand('bootme',	'chat_bootme',		new Message('chat.rasp', 'slash_bootme_description'),		Player::PLAYERS);
		$this->registerChatCommand('ragequit',	'chat_ragequit',	new Message('chat.rasp', 'slash_ragequit_description'),		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {
		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
			$msg = new Message('chat.rasp', 'pm_info');
			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage($msg);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_pm ($aseco, $login, $chat_command, $chat_parameter) {
		global $pmlen;  // from chat.admin.php

		// TODO: Replace "global $pmlen;"
		if ( isset($aseco->plugins['PluginChatAdmin']) ) {
			$pmlen = $aseco->plugins['PluginChatAdmin']->pmlen;
		}

		$command['params'] = explode(' ', $chat_parameter, 2);

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// get player login or ID
		if (!$target = $aseco->server->players->getPlayerParam($player, $command['params'][0])) {
			return;
		}

		// check for a message
		if (isset($command['params'][1]) && $command['params'][1] !== '') {
			$stamp = date('H:i:s');
			// strip wide fonts from nicks
			$plnick = str_ireplace('$w', '', $player->nickname);
			$tgnick = str_ireplace('$w', '', $target->nickname);

			// drop oldest pm line if sender's buffer full
			if (count($player->pmbuf) >= $pmlen) {
				array_shift($player->pmbuf);
			}
			// append timestamp, sender nickname and pm line to sender's history
			$player->pmbuf[] = array($stamp, $plnick, $command['params'][1]);

			// drop oldest pm line if receiver's buffer full
			if (count($target->pmbuf) >= $pmlen) {
				array_shift($target->pmbuf);
			}
			// append timestamp, sender nickname and pm line to receiver's history
			$target->pmbuf[] = array($stamp, $plnick, $command['params'][1]);

			// show chat message to both players
			$msg = new Message('chat.rasp', 'pm');
			$msg->addPlaceholders($plnick,
				$tgnick,
				$command['params'][1]
			);
			$msg->sendChatMessage($target->login .','. $player->login);

			// check if player muting is enabled
			if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
				// append pm line to both players' buffers
				if (count($target->mutebuf) >= 28) {  // chat window length
					array_shift($target->mutebuf);
				}
				$target->mutebuf[] = $msg->finish($target->login);
				if (count($player->mutebuf) >= 28) {  // chat window length
					array_shift($player->mutebuf);
				}
				$player->mutebuf[] = $msg->finish($player->login);
			}
		}
		else {
			$msg = new Message('chat.rasp', 'pm_error');
			$msg->sendChatMessage($player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_pma ($aseco, $login, $chat_command, $chat_parameter) {
		global $pmlen;  // from chat.admin.php

		// TODO: Replaces "global $pmlen;"
		if ( isset($aseco->plugins['PluginChatAdmin']) ) {
			$pmlen = $aseco->plugins['PluginChatAdmin']->pmlen;
		}

		$command['params'] = explode(' ', $chat_parameter, 2);

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// check for admin ability
		if ($aseco->allowAbility($player, 'chat_pma')) {
			// get player login or ID
			if (!$target = $aseco->server->players->getPlayerParam($player, $command['params'][0])) {
				return;
			}

			// check for a message
			if ($command['params'][1] !== '') {
				$stamp = date('H:i:s');
				// strip wide fonts from nicks
				$plnick = str_ireplace('$w', '', $player->nickname);
				$tgnick = str_ireplace('$w', '', $target->nickname);

				// drop oldest pm line if receiver's history full
				if (count($target->pmbuf) >= $pmlen) {
					array_shift($target->pmbuf);
				}
				// append timestamp, sender nickname and pm line to receiver's history
				$target->pmbuf[] = array($stamp, $plnick, $command['params'][1]);

				// show chat message to receiver
				$msg = new Message('chat.rasp', 'pm');
				$msg->addPlaceholders($plnick,
					$tgnick,
					$command['params'][1]
				);
				$msg->sendChatMessage($target->login);

				// check if player muting is enabled
				if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
					// drop oldest message if receiver's mute buffer full
					if (count($target->mutebuf) >= 28) {  // chat window length
						array_shift($target->mutebuf);
					}
					// append pm line to receiver's mute buffer
					$target->mutebuf[] = $msg->finish($target->login);
				}

				// show chat message to all admins
				foreach ($aseco->server->players->player_list as $admin) {
					// check for admin ability
					if ($aseco->allowAbility($admin, 'chat_pma')) {
						// drop oldest pm line if admin's buffer full
						if (count($admin->pmbuf) >= $pmlen) {
							array_shift($admin->pmbuf);
						}
						// append timestamp, sender nickname and pm line to admin's history
						$admin->pmbuf[] = array($stamp, $plnick, $command['params'][1]);

						// CC the message
						$msg->sendChatMessage($admin->login);

						// check if player muting is enabled
						if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
							// append pm line to admin's mute buffer
							if (count($admin->mutebuf) >= 28) {  // chat window length
								array_shift($admin->mutebuf);
							}
							$admin->mutebuf[] = $msg->finish($admin->login);
						}
					}
				}
			}
			else {
				$msg = new Message('chat.rasp', 'pm_error');
				$msg->sendChatMessage($player->login);
			}
		}
		else {
			$msg = new Message('chat.rasp', 'no_admin');
			$msg->sendChatMessage($player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_pmlog ($aseco, $login, $chat_command, $chat_parameter) {
		global $lnlen;  // from chat.admin.php

		// TODO: Replaces "global $lnlen;"
		if ( isset($aseco->plugins['PluginChatAdmin']) ) {
			$lnlen = $aseco->plugins['PluginChatAdmin']->lnlen;
		}

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		if (!empty($player->pmbuf)) {
			$head = (new Message('chat.rasp', 'pmlog_head'))->finish($login);
			$msg = array();
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(1.2), array('Icons64x64_1', 'Outbox'));
			foreach ($player->pmbuf as $item) {
				// break up long lines into chunks with continuation strings
				$multi = explode(LF, wordwrap($aseco->stripStyles($item[2]), $lnlen + 30, LF . '...'));
				foreach ($multi as $line) {
					$line = substr($line, 0, $lnlen + 33);  // chop off excessively long words
					$msg[] = array(
						'$z'. ($aseco->settings['chatpmlog_times'] ? '<{#server}'. $item[0] .'$z> ' : '') .
						'[{#black}'. $item[1] .'$z] '. $line
					);
					if (++$lines > 14) {
						$player->msgs[] = $msg;
						$lines = 0;
						$msg = array();
					}
				}
			}

			// add if last batch exists
			if (!empty($msg)) {
				$player->msgs[] = $msg;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else {
			$msg = new Message('chat.rasp', 'pm_no_history');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_hi ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/hi');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {
			$msg = new Message('chat.rasp', 'hello');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter);
		}
		else {
			$msg = new Message('chat.rasp', 'hello_all');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bye ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/bye');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {

			$msg = new Message('chat.rasp', 'bye');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter);
		}
		else {
			$msg = new Message('chat.rasp', 'bye_all');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_thx ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/thx');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {
			$msg = new Message('chat.rasp', 'thx');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter);

		}
		else {
			$msg = new Message('chat.rasp', 'thx_all');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lol ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/lol');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'lol');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lool ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/lool');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'lool');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_brb ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/brb');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'brb');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_afk ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp','muted');
			$message->addPlaceholders('/afk');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'afk');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();

		// check for auto force spectator
		if ($aseco->settings['afk_force_spec']) {
			if (!$player->is_spectator) {
				try {
					// force player into spectator
					$aseco->client->query('ForceSpectator', $player->login, 1);

					// allow spectator to switch back to player
					$rtn = $aseco->client->query('ForceSpectator', $player->login, 0);
				}
				catch (Exception $exception) {
					$aseco->console('[ChatRasp] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectator');
				}
			}

			try {
				// force free camera mode on spectator
				$aseco->client->addCall('ForceSpectatorTarget', $player->login, '', 2);
			}
			catch (Exception $exception) {
				$aseco->console('[ChatRasp] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - ForceSpectatorTarget');
			}

			try {
				// free up player slot
				$aseco->client->addCall('SpectatorReleasePlayerSlot', $player->login);
			}
			catch (Exception $exception) {
				$aseco->console('[ChatRasp] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SpectatorReleasePlayerSlot');
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_gg ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp', 'muted');
			$message->addPlaceholders('/gg');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {
			$msg = new Message('chat.rasp', 'gg');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter
			);
		}
		else {
			$msg = new Message('chat.rasp', 'gga');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_gr ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp', 'muted');
			$message->addPlaceholders('/gr');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {
			$msg = new Message('chat.rasp', 'gr');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter
			);
		}
		else {
			$msg = new Message('chat.rasp', 'gra');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_n1 ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp', 'muted');
			$message->addPlaceholders('/n1');
			$message->sendChatMessage($player->login);
			return;
		}

		if ($chat_parameter !== '') {
			$msg = new Message('chat.rasp', 'n1');
			$msg->addPlaceholders($player->nickname,
				$chat_parameter
			);
		}
		else {
			$msg = new Message('chat.rasp', 'n1a');
			$msg->addPlaceholders($player->nickname);
		}
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bgm ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp', 'muted');
			$message->addPlaceholders('/bgm');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'bgm');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_dammit ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = new Message('chat.rasp', 'muted');
			$message->addPlaceholders('/dammit');
			$message->sendChatMessage($player->login);
			return;
		}

		$msg = new Message('chat.rasp', 'dammit');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bootme ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// show departure message and kick player
		$msg = new Message('chat.rasp', 'bootme');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();

		try {
			$message = (new Message('chat.rasp', 'bootme_dialog'))->finish($player->login);
			if ($message !== '') {
				$aseco->client->addCall('Kick',
					$player->login,
					$message.'$z'
				);
			}
			else {
				$aseco->client->addCall('Kick', $player->login);
			}
		}
		catch (Exception $exception) {
			$aseco->console('[ChatRasp] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick: ['. $player->login .']');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_ragequit ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// show departure message and kick player
		$msg = new Message('chat.rasp', 'ragequit');
		$msg->addPlaceholders($player->nickname);
		$msg->sendChatMessage();

		try {
			$message = (new Message('chat.rasp', 'ragequit_dialog'))->finish($player->login);
			if ($message !== '') {
				$aseco->client->addCall('Kick',
					$player->login,
					$message .'$z'
				);
			}
			else {
				$aseco->client->addCall('Kick', $player->login);
			}
		}
		catch (Exception $exception) {
			$aseco->console('[ChatRasp] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - Kick: ['. $player->login .']');
		}
	}
}

?>
