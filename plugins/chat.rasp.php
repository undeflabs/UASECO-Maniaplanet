<?php
/*
 * Plugin: Chat Rasp
 * ~~~~~~~~~~~~~~~~~
 * » Provides private messages and a wide variety of shout-outs.
 * » Based upon plugin.rasp_chat.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-05
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
 *  - plugins/plugin.rasp.php
 *  - plugins/chat.admin.php
 *  - plugins/plugin.manialinks.php
 *  - plugins/plugin.muting.php
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

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides private messages and a wide variety of shout-outs.');

		$this->addDependence('PluginRasp',		Dependence::REQUIRED,	'1.0.0',	null);
		$this->addDependence('PluginChatAdmin',		Dependence::REQUIRED,	'1.0.0',	null);
		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0',	null);
		$this->addDependence('PluginMuting',		Dependence::WANTED,	'1.0.0',	null);

		$this->registerChatCommand('pm',	'chat_pm',		'Sends a private message to login or PlayerId',		Player::PLAYERS);
		$this->registerChatCommand('pma',	'chat_pma',		'Sends a private message to player and admins',		Player::PLAYERS);
		$this->registerChatCommand('pmlog',	'chat_pmlog',		'Displays log of your recent private messages',		Player::PLAYERS);
		$this->registerChatCommand('hi',	'chat_hi',		'Sends a Hi message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('bye',	'chat_bye',		'Sends a Bye message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('thx',	'chat_thx',		'Sends a Thanks message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('lol',	'chat_lol',		'Sends a Lol message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('lool',	'chat_lool',		'Sends a Lool message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('brb',	'chat_brb',		'Sends a Be Right Back message to everyone',		Player::PLAYERS);
		$this->registerChatCommand('afk',	'chat_afk',		'Sends an Away From Keyboard message to everyone',	Player::PLAYERS);
		$this->registerChatCommand('gg',	'chat_gg',		'Sends a Good Game message to everyone',		Player::PLAYERS);
		$this->registerChatCommand('gr',	'chat_gr',		'Sends a Good Race message to everyone',		Player::PLAYERS);
		$this->registerChatCommand('n1',	'chat_n1',		'Sends a Nice One message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('bgm',	'chat_bgm',		'Sends a Bad Game message to everyone',			Player::PLAYERS);
		$this->registerChatCommand('official',	'chat_official',	'Shows a helpful message ;-)',				Player::PLAYERS);
		$this->registerChatCommand('bootme',	'chat_bootme',		'Boot yourself from the server',			Player::PLAYERS);
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

		$player = $aseco->server->players->getPlayer($login);
		$target = $player;

		// get player login or ID
		if (!$target = $aseco->server->players->getPlayerParam($player, $command['params'][0])) {
			return;
		}

		// check for a message
		if (isset($command['params'][1]) && $command['params'][1] != '') {
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
			$msg = '{#error}-pm-$g[' . $plnick . '$z$s$i->' . $tgnick . '$z$s$i]$i {#interact}' . $command['params'][1];
			$aseco->sendChatMessage($msg, $target->login .','. $player->login);

			// check if player muting is enabled
			if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
				// append pm line to both players' buffers
				if (count($target->mutebuf) >= 28) {  // chat window length
					array_shift($target->mutebuf);
				}
				$target->mutebuf[] = $msg;
				if (count($player->mutebuf) >= 28) {  // chat window length
					array_shift($player->mutebuf);
				}
				$player->mutebuf[] = $msg;
			}
		}
		else {
			$msg = '{#server}» {#error}No message!';
			$aseco->sendChatMessage($msg, $player->login);
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

		$player = $aseco->server->players->getPlayer($login);
		$target = $player;

		// check for admin ability
		if ($aseco->allowAbility($player, 'chat_pma')) {
			// get player login or ID
			if (!$target = $aseco->server->players->getPlayerParam($player, $command['params'][0])) {
				return;
			}

			// check for a message
			if ($command['params'][1] != '') {
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
				$msg = '{#error}-pm-$g[' . $plnick . '$z$s$i->' . $tgnick . '$z$s$i]$i {#interact}' . $command['params'][1];
				$aseco->sendChatMessage($msg, $target->login);

				// check if player muting is enabled
				if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
					// drop oldest message if receiver's mute buffer full
					if (count($target->mutebuf) >= 28) {  // chat window length
						array_shift($target->mutebuf);
					}
					// append pm line to receiver's mute buffer
					$target->mutebuf[] = $msg;
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
						$aseco->sendChatMessage($msg, $admin->login);

						// check if player muting is enabled
						if (isset($aseco->plugins['PluginMuting']) && $aseco->plugins['PluginMuting']->muting_available) {
							// append pm line to admin's mute buffer
							if (count($admin->mutebuf) >= 28) {  // chat window length
								array_shift($admin->mutebuf);
							}
							$admin->mutebuf[] = $msg;
						}
					}
				}
			}
			else {
				$msg = '{#server}» {#error}No message!';
				$aseco->sendChatMessage($msg, $player->login);
			}
		}
		else {
			$msg = $aseco->getChatMessage('NO_ADMIN');
			$aseco->sendChatMessage($msg, $player->login);
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

		$player = $aseco->server->players->getPlayer($login);
		$target = $player;

		if (!empty($player->pmbuf)) {
			$head = 'Your recent PM history:';
			$msg = array();
			$lines = 0;
			$player->msgs = array();
			$player->msgs[0] = array(1, $head, array(1.2), array('Icons64x64_1', 'Outbox'));
			foreach ($player->pmbuf as $item) {
				// break up long lines into chunks with continuation strings
				$multi = explode(LF, wordwrap($aseco->stripColors($item[2]), $lnlen + 30, LF . '...'));
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
			$aseco->sendChatMessage('{#server}» {#error}No PM history found!', $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_hi ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/hi');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Hello '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Hello All !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bye ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/bye');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Bye '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}I have to go... Bye All !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_thx ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/thx');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Thanks '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Thanks All !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lol ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/lol');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$msg = '$g['. $player->nickname .'$z$s] {#interact}LoL !';
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lool ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/lool');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$msg = '$g['. $player->nickname .'$z$s] {#interact}LooOOooL !';
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_brb ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/brb');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$msg = '$g['. $player->nickname .'$z$s] {#interact}Be Right Back !';
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_afk ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/afk');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$msg = '$g['. $player->nickname .'$z$s] {#interact}Away From Keyboard !';
		$aseco->sendChatMessage($msg);

		// check for auto force spectator
		if ($aseco->settings['afk_force_spec']) {
			if (!$player->isspectator) {
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

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/gg');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Good Game '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Good Game All !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_gr ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/gr');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Good Race '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Good Race !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_n1 ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/n1');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if ($chat_parameter != '') {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Nice One '. $chat_parameter .' !';
		}
		else {
			$msg = '$g['. $player->nickname .'$z$s] {#interact}Nice One !';
		}
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bgm ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// check if on global mute list
		if (in_array($player->login, $aseco->server->mutelist)) {
			$message = $aseco->formatText($aseco->getChatMessage('MUTED'), '/bgm');
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$msg = '$g['. $player->nickname .'$z$s] {#interact}Bad Game for Me :(';
		$aseco->sendChatMessage($msg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_official ($aseco, $login, $chat_command, $chat_parameter) {

		$msg = $aseco->plugins['PluginRasp']->messages['OFFICIAL'][0];
		$aseco->sendChatMessage($msg, $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_bootme ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);

		// show departure message and kick player
		$msg = $aseco->formatText($aseco->plugins['PluginRasp']->messages['BOOTME'][0],
			$player->nickname
		);
		$aseco->sendChatMessage($msg);
		if (isset($aseco->plugins['PluginRasp']->messages['BOOTME_DIALOG'][0]) && $aseco->plugins['PluginRasp']->messages['BOOTME_DIALOG'][0] != '') {
			$aseco->client->addCall('Kick',
				$player->login,
				$aseco->formatColors($aseco->plugins['PluginRasp']->messages['BOOTME_DIALOG'][0] .'$z')
			);
		}
		else {
			$aseco->client->addCall('Kick', $player->login);
		}
	}
}

?>
