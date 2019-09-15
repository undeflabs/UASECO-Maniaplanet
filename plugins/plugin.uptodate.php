<?php
/*
 * Plugin: Uptodate
 * ~~~~~~~~~~~~~~~~
 * » Checks if there is a more recent version of UASECO at start-up & MasterAdmin connect, and provides "/admin uptodate" command.
 *   Also merges global blacklist at MasterAdmin connect, and provides "/admin mergegbl" command.
 * » Based upon plugin.uptodate.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginUptodate();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginUptodate extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('askuri');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-09-15');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.uptodate', 'plugin_description'));

		$this->addDependence('PluginChatAdmin',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onPlayerConnect',	'onPlayerConnect');

		$this->registerChatCommand('uptodate',	'chat_uptodate', new Message('plugin.uptodate', 'chat_uptodate'), Player::OPERATORS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_uptodate ($aseco, $login, $chat_command, $chat_parameter) {
		$this->checkUasecoUptodate($login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Load config file
		$config_file = 'config/uptodate.xml';
		if (file_exists($config_file)) {
			$aseco->console('[UpToDate] Load config file ['. $config_file .']');
			if ($xml = $aseco->parser->xmlToArray($config_file, true, true)) {
				$this->config = $xml['SETTINGS'];
			}
			else {
				trigger_error('[UpToDate] Could not read/parse config file ['. $config_file .']!', E_USER_WARNING);
			}
		}
		else {
			trigger_error('[UpToDate] Could not find config file ['. $config_file .']!', E_USER_WARNING);
		}

		// Transform 'TRUE' or 'FALSE' from string to boolean
		$this->config['UPTODATE_CHECK'][0] = ((strtoupper($this->config['UPTODATE_CHECK'][0]) === 'TRUE') ? true : false);

		// Setup defaults, if required
		if ($this->config['UPTODATE_URL'][0] === '') {
			$this->config['UPTODATE_URL'][0] = UASECO_WEBSITE .'/uptodate/current_release.txt';
		}
		if ($this->config['GLOBAL_BLACKLIST_URL'][0] === '') {
			$this->config['GLOBAL_BLACKLIST_URL'][0] = UASECO_WEBSITE .'/uptodate/trackmania_blacklist_dedimania.xml';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Check for a MasterAdmin
		if ($aseco->isMasterAdmin($player)) {
			// Check version
			if ($this->config['UPTODATE_CHECK'][0] === true) {
				$this->checkUasecoUptodate($player->login);
			}

			// Check whether to merge global black list
			if ($this->config['GLOBAL_BLACKLIST_MERGE'][0] === true) {
				$this->admin_mergegbl($aseco, 'MasterAdmin', $player->login, false);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function checkUasecoUptodate ($login = false) {
		global $aseco;

		try {
			// Grab version file
			$params = array(
				'url'		=> $this->config['UPTODATE_URL'][0],
				'callback'	=> array(array($this, 'handleWebrequest'), $login),
			);
			return $aseco->webrequest->GET($params);
		}
		catch (Exception $exception) {
			$aseco->console('[UpToDate] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function handleWebrequest ($request, $login = false) {
		global $aseco;

		if ($request->response['header']['code'] === 200) {
			if ($request->response['content']) {
				// Compare versions
				if ($aseco->versionCheck($request->response['content'], UASECO_VERSION, '>') === true) {
					$msg = new Message('plugin.uptodate', 'uptodate_new');
					$msg->addPlaceholders($request->response['content'], '$L['. UASECO_WEBSITE .']'. UASECO_WEBSITE .'$L');
					$msg->sendChatMessage($login);
				}
				else {
					$msg = new Message('plugin.uptodate', 'uptodate_ok');
					$msg->addPlaceholders(UASECO_VERSION);
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('plugin.uptodate', 'uptodate_failed');
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('plugin.uptodate', 'uptodate_failed');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function admin_mergegbl ($aseco, $logtitle, $login, $manual, $url = false) {

		if (!isset($aseco->plugins['PluginChatAdmin'])) {
			return;
		}

		if ($url === false) {
			$url = $this->config['GLOBAL_BLACKLIST_URL'][0];
		}

		try {
			// Download & parse global black list
			$params = array(
				'url'			=> $url,
				'sync'			=> true,
				'user_agent'		=> USER_AGENT,
			);
			$request = $aseco->webrequest->GET($params);
			if (isset($request->response['header']['code']) && $request->response['header']['code'] === 200) {
				if ($request->response['content']) {
					if ($globals = $aseco->parser->xmlToArray($request->response['content'], false)) {

						// Get current black list
						$blacks = $aseco->plugins['PluginChatAdmin']->getBlacklist($aseco);

						// Merge new global entries
						$new = 0;
						foreach ($globals['BLACKLIST']['PLAYER'] as $black) {
							if (!array_key_exists($black['LOGIN'][0], $blacks)) {
								$aseco->client->addCall('BlackList', $black['LOGIN'][0]);
								$new++;
							}
						}

						// Update black list file if necessary
						if ($new > 0) {
							$filename = $aseco->settings['blacklist_file'];
							$aseco->client->addCall('SaveBlackList', $filename);
						}

						// Check whether to report new mergers
						if ($new > 0 || $manual) {
							// Log console message
							$aseco->console('[UpToDate] {1} [{2}] merged global blacklist [{3}] new: {4}', $logtitle, $login, $url, $new);

							$msg = new Message('plugin.uptodate', 'admin_merged');
							$msg->addPlaceholders($new);
							$msg->sendChatMessage($login);
						}
					}
					else {
						$msg = new Message('plugin.uptodate', 'admin_can_not_parse');
						$msg->addPlaceholders($url);
						$msg->sendChatMessage($login);
					}
				}
				else {
					$msg = new Message('plugin.uptodate', 'admin_can_not_access');
					$msg->addPlaceholders($url);
					$msg->sendChatMessage($login);
				}
			}
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->get(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
	}
}

?>
