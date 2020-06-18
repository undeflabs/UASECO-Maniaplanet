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
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-10-03');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.muting', 'plugin_description'));

		$this->addDependence('PluginManialinks',	Dependence::REQUIRED,	'1.0.0', null);
		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onPlayerChat',	'onPlayerChat');
		$this->registerEvent('onServerChat',	'onServerChat');

		$this->registerChatCommand('mute',	'chat_mute',		new Message('plugin.muting', 'slash_chat_mute_description'),		Player::PLAYERS);
		$this->registerChatCommand('unmute',	'chat_unmute',		new Message('plugin.muting', 'slash_chat_unmute_description'),		Player::PLAYERS);
		$this->registerChatCommand('mutelist',	'chat_mutelist',	new Message('plugin.muting', 'slash_chat_mutelist_description'),	Player::PLAYERS);
		$this->registerChatCommand('refresh',	'chat_refresh',		new Message('plugin.muting', 'slash_chat_refresh_description'),		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Define pattern for known global messages to reduce overhead
		$msg = new Message('plugin.muting', 'round');
		$this->global_pattern = '/'. $aseco->formatColors($aseco->formatText($msg->finish('en',false), '\d+'))
		             .'|'. $aseco->formatColors('$z$s{#server}» ')
		             .'|'. $aseco->formatColors('{#server}» ') .'/A';		// anchor at start
		$this->global_pattern = str_replace('$', '\$', $this->global_pattern);	// escape dollars

		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage(new Message('plugin.muting', 'info_message');
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
			$message = new Message('plugin.muting', 'error');
			$message->addPlaceholders($title,
				$aseco->stripStyles($target->nickname)
			);
			$message->sendChatMessage($player->login);
			return;
		}

		// check if not yet in mute list
		if (!in_array($target->login, $player->mutelist)) {
			// mute this player
			$player->mutelist[] = $target->login;

			$message = new Message('plugin.muting', 'mute');
			$message->addPlaceholders($target->nickname);
		}
		else {
			$message = new Message('plugin.muting', 'already_muted');
			$message->addPlaceholders($target->nickname);
		}

		// show chat message
		$message->sendChatMessage($player->login);
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

			$message = new Message('plugin.muting', 'unmute');
			$message->addPlaceholders($target->nickname);
		}
		else {
			$message = new Message('plugin.muting', 'not_in_mutelist');
			$message->addPlaceholders($target->nickname);
		}

		// show chat message
		$message->sendChatMessage($player->login);
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
			$message = new Message('plugin.muting', 'none_muted');
			$message->sendChatMessage($player->login);
			return;
		}

		$player->playerlist = array();

		$head = (new Message('plugin.muting', 'list_head'))->finish($login);
		$msg = array();
		$msg[] = array('Id', '{#nick}Nick $g/{#login} Login');
		$pid = 1;
		$lines = 0;
		$player->msgs = array();
		$player->msgs[0] = array(1, $head, array(0.9, 0.1, 0.8), array('Icons128x128_1', 'Padlock', 0.01));
		foreach ($player->mutelist as $pl) {
			if ($pl !== '') {
				$plarr = array();
				$plarr['login'] = $pl;
				$player->playerlist[] = $plarr;

				$nick = $aseco->server->players->getPlayerNickname($pl);
				if ($nick === false) {
					$nick = $pl;
				}

				$msg[] = array(str_pad($pid, 2, '0', STR_PAD_LEFT) .'.',
					'{#black}'. str_ireplace('$w', '', $nick)
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
			// === 1
			$message = new Message('plugin.muting', 'none_muted');
			$message->sendChatMessage($player->login);
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
