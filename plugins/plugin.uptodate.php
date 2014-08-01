<?php
/*
 * Plugin: Uptodate
 * ~~~~~~~~~~~~~~~~
 * » Checks UASECO version at start-up & MasterAdmin connect, and provides "/admin uptodate" command.
 *   Also merges global blacklist at MasterAdmin connect, and provides "/admin mergegbl" command.
 * » Based upon plugin.uptodate.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-07-19
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

	// Start the plugin
	$_PLUGIN = new PluginUptodate();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginUptodate extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Checks UASECO version at start-up & MasterAdmin connect.');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onPlayerConnect',	'onPlayerConnect');

		$this->registerChatCommand('uptodate',	'chat_uptodate', 'Checks current version of UASECO', Player::OPERATORS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkUasecoUptodate ($aseco) {

		$version_url = UASECO_WEBSITE .'uptodate/current_release.txt';  // URL to current version file

		// grab version file
		$current = trim($aseco->http_get_file($version_url));
		if ($current && $current != -1) {
			// compare versions
			if ($current != UASECO_VERSION) {
				$message = $aseco->formatText($aseco->getChatMessage('UPTODATE_NEW'), $current,
					'$l['. UASECO_WEBSITE .']'. UASECO_WEBSITE .'$l'
				);
			}
			else {
				$message = $aseco->formatText($aseco->getChatMessage('UPTODATE_OK'), $current);
			}
		}
		else {
			$message = false;
		}
		return $message;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $command) {

		// check version but ignore error
		if ($aseco->settings['uptodate_check'] && $message = $aseco->plugins['PluginUptodate']->checkUasecoUptodate($aseco)) {
			// show chat message
			$aseco->client->query('ChatSendServerMessage', $aseco->formatColors($message));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// check for a master admin
		if ($aseco->isMasterAdmin($player)) {
			// check version but ignore error
			if ($aseco->settings['uptodate_check'] && $message = $aseco->plugins['PluginUptodate']->checkUasecoUptodate($aseco)) {
				// check whether out of date
				if (!preg_match('/' . $aseco->formatText($aseco->getChatMessage('UPTODATE_OK'), '.*') . '/', $message)) {
					// strip 1 leading '>' to indicate a player message instead of system-wide
					$message = str_replace('{#server}» ', '{#server}» ', $message);

					// show chat message
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $player->login);
				}
			}

			// check whether to merge global black list
			if ($aseco->settings['global_blacklist_merge'] && $aseco->settings['global_blacklist_url'] != '') {
				$this->admin_mergegbl($aseco, 'MasterAdmin', $player->login, false, $aseco->settings['global_blacklist_url']);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_uptodate ($aseco, $login, $chat_command, $chat_parameter) {

		// check version or report error
		if ($message = $aseco->plugins['PluginUptodate']->checkUasecoUptodate($aseco)) {
			// strip 1 leading '>' to indicate a player message instead of system-wide
			$message = str_replace('{#server}» ', '{#server}» ', $message);

			// show chat message
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
		else {
			$message = '{#server}» {#error}Error: can\'t access the last version!';
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function admin_mergegbl ($aseco, $logtitle, $login, $manual, $url) {

		if ( !isset($aseco->plugins['PluginChatAdmin']) ) {
			return;
		}

		// download & parse global black list
		$blacklist = $aseco->http_get_file($url);
		if ($blacklist && $blacklist != -1) {
			if ($globals = $aseco->parser->xmlToArray($blacklist, false)) {
				// get current black list
				$blacks = $aseco->plugins['PluginChatAdmin']->getBlacklist($aseco);  // from chat.admin.php

				// merge new global entries
				$new = 0;
				foreach ($globals['BLACKLIST']['PLAYER'] as $black) {
					if (!array_key_exists($black['LOGIN'][0], $blacks)) {
						$aseco->client->addCall('BlackList', array($black['LOGIN'][0]));
						$new++;
					}
				}

				// send all entries and ignore results
				if (!$aseco->client->multiquery(true)) {
					trigger_error('[' . $this->client->getErrorCode() . '] BlackList (merge) - ' . $this->client->getErrorMessage(), E_USER_ERROR);
				}

				// update black list file if necessary
				if ($new > 0) {
					$filename = $aseco->settings['blacklist_file'];
					$aseco->client->addCall('SaveBlackList', array($filename));
				}

				// check whether to report new mergers
				if ($new > 0 || $manual) {
					// log console message
					$aseco->console('{1} [{2}] merged global blacklist [{3}] new: {4}', $logtitle, $login, $url, $new);

					// show chat message
					$message = $aseco->formatText('{#server}» {#highlite}{1} {#server}new login{2} merged into blacklist',
						$new,
						($new == 1 ? '' : 's')
					);
					$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
				}
			}
			else {
				$message = $aseco->formatText('{#server}» {#error}Error: can\'t parse {#highlite}$i{1}{#error}!',
					$url
				);
				$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
			}
		}
		else {
			$message = $aseco->formatText('{#server}» {#error}Error: can\'t access {#highlite}$i{1}{#error}!',
				$url
			);
			$aseco->client->query('ChatSendServerMessageToLogin', $aseco->formatColors($message), $login);
		}
	}
}

?>
