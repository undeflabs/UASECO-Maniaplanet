<?php
/*
 * Plugin: Muting
 * ~~~~~~~~~~~~~~
 * » Handles individual and global player muting, and provides /mute, /unmute, /mutelist and /refresh commands.
 * » Based upon plugin.muting.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginMuting();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginMuting extends Plugin {
	public $muting_available = true;				// signal to chat.admin.php and chat.rasp.php
	public $global_pattern;						// pre-defined pattern for global messages

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-04-27');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Handles individual and global player muting');

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onPlayerChat',	'onPlayerChat');
		$this->registerEvent('onServerChat',	'onServerChat');

		$this->registerChatCommand('mute',	'chat_mute',		'Mute another player\'s chat messages',		Player::PLAYERS);
		$this->registerChatCommand('unmute',	'chat_unmute',		'UnMute another player\'s chat messages',	Player::PLAYERS);
		$this->registerChatCommand('mutelist',	'chat_mutelist',	'Display list of muted players',		Player::PLAYERS);
		$this->registerChatCommand('refresh',	'chat_refresh',		'Refresh chat window',				Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Define pattern for known global messages to reduce overhead
		$this->global_pattern = '/'. $aseco->formatColors($aseco->formatText($aseco->getChatMessage('ROUND'), '\d+'))
		             .'|'. $aseco->formatColors('$z$s{#server}» ')
		             .'|'. $aseco->formatColors('{#server}» ') .'/A';		// anchor at start
		$this->global_pattern = str_replace('$', '\$', $this->global_pattern);	// escape dollars

		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage('Use "/mute" and "/unmute" to mute / unmute other players, and "/mutelist" to list them!');
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		// check if not a registered (== hidden) chat command
		if ($chatter = $aseco->server->players->getPlayerByLogin($chat[1])) {

			// check each player's mute list and global mute list
			foreach ($aseco->server->players->player_list as $player) {
				if (in_array($chat[1], $player->mutelist) ||
				    in_array($chat[1], $aseco->server->mutelist)) {
					// spew buffer back to player and thus mute the chatter
					if (!empty($player->mutebuf)) {
						$buf = '';
						foreach ($player->mutebuf as $line) {
							// double '$z' to avoid match with $this->global_pattern that would cause
							// spewed buffer to be buffered again
							$buf .= LF .'$z$z$s'. $line;
						}
						$aseco->sendChatMessage($buf, $player->login);
					}
				}
				else {
					// append chatter line to buffer
					if (count($player->mutebuf) >= 28) {  // chat window length
						array_shift($player->mutebuf);
					}
					$player->mutebuf[] = '$z$s['. $chatter->nickname .'$z$s] '. $chat[2];
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onServerChat ($aseco, $chat) {

		// any server chat, check for global server message
		if (preg_match($this->global_pattern, $chat[2])) {
			// append global server message to all players' buffers
			foreach ($aseco->server->players->player_list as $player) {
				if (count($player->mutebuf) >= 28) {  // chat window length
					array_shift($player->mutebuf);
				}
				$player->mutebuf[] = $chat[2];
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mute ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// get player login or ID
		if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter)) {
			return;
		}

		// check for any admin tier
		if ($aseco->isAnyAdmin($target)) {
			// obtain correct title
			$title = $aseco->isMasterAdmin($target) ? $aseco->titles['MASTERADMIN'][0] :
				($aseco->isAdmin($target) ? $aseco->titles['ADMIN'][0] :
				($aseco->isOperator($target) ? $aseco->titles['OPERATOR'][0] :
				'Player')
			);
			$message = $aseco->formatText('{#server}» {#error}Cannot mute {#logina}$i {1} {#highlite}{2}$z$s{#error} !',
				$title,
				$aseco->stripStyles($target->nickname)
			);
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// check if not yet in mute list
		if (!in_array($target->login, $player->mutelist)) {
			// mute this player
			$player->mutelist[] = $target->login;

			$message = $aseco->formatText($aseco->getChatMessage('MUTE'),
				$target->nickname
			);
		}
		else {
			$message = '{#server}» {#error}Player {#highlite}$i '. $aseco->stripStyles($target->nickname) .'$z$s{#error} is already in your mute list!';
		}

		// show chat message
		$aseco->sendChatMessage($message, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_unmute ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$target = $player;

		// get player login or ID
		if (!$target = $aseco->server->players->getPlayerParam($player, $chat_parameter, true)) {
			return;
		}

		// check if indeed in mute list
		if (($i = array_search($target->login, $player->mutelist)) !== false) {
			// unmute this player
			$player->mutelist[$i] = '';

			$message = $aseco->formatText($aseco->getChatMessage('UNMUTE'),
				$target->nickname
			);
		}
		else {
			$message = '{#server}» {#error}Player {#highlite}$i '. $aseco->stripStyles($target->nickname) .'$z$s{#error} is not in your mute list!';
		}

		// show chat message
		$aseco->sendChatMessage($message, $player->login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mutelist ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// check for muted players
		if (empty($player->mutelist)) {
			$aseco->sendChatMessage('{#server}» {#error}No muted players found!', $player->login);
			return;
		}

		$player->playerlist = array();

		$head = 'Currently Muted Players:';
		$msg = array();
		$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
		$pid = 1;
		$lines = 0;
		$player->msgs = array();
		$player->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Padlock', 0.01));
		foreach ($player->mutelist as $pl) {
			if ($pl != '') {
				$plarr = array();
				$plarr['login'] = $pl;
				$player->playerlist[] = $plarr;

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
					'{#black}'. str_ireplace('$w', '', $aseco->server->players->getPlayerNickname($pl))
					.'$z / {#login}'. $pl
				);
				$pid++;
				if (++$lines > 14) {
					$player->msgs[] = $msg;
					$lines = 0;
					$msg = array();
					$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
				}
			}
		}

		// add if last batch exists
		if (count($msg) > 1) {
			$player->msgs[] = $msg;
		}

		// display ManiaLink message
		if (count($player->msgs) > 1) {
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else {
			// == 1
			$aseco->sendChatMessage('{#server}» {#error}No muted players found!', $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_refresh ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// spew buffer back to player
		if (!empty($player->mutebuf)) {
			$buf = '';
			foreach ($player->mutebuf as $line) {
				// double '$z' to avoid match with $this->global_pattern that would cause
				// spewed buffer to be buffered again
				$buf .= LF . '$z$z$s' . $line;
			}
			$aseco->sendChatMessage($buf, $player->login);
		}
	}
}

?>
