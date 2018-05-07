<?php
/*
 * Plugin: Access Control
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Controls player access by zone.
 *   Inspired by Apache's mod_access: http://httpd.apache.org/docs/2.0/mod/mod_access.html
 * » Based upon plugin.access.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginAccessControl();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginAccessControl extends Plugin {
	public $access_control;
	// ['order'] - boolean: allow,deny = true; deny,allow = false
	// ['allowall'] - boolean: Allow from all = true; otherwise false
	// ['allow'] - array of nations/zones to allow
	// ['denyall'] - boolean: Deny from all = true; otherwise false
	// ['deny'] - array of nations/zones to deny

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Controls player access by zone.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onPlayerConnectPostfix',	'onPlayerConnectPostfix');  // use post event after all join processing
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco, $reload = false) {

		// initialize access control
		$this->access_control = array();
		$this->access_control['order'] = false;
		$this->access_control['allowall'] = false;
		$this->access_control['allow'] = array();
		$this->access_control['denyall'] = false;
		$this->access_control['deny'] = array();
		$this->access_control['messages'] = array();

		$error = '';
		// log console message
		if (!$reload) {
			$aseco->console('[Access] Load player access control [config/access.xml]');
		}

		// read & parse config file
		if (!$settings = $aseco->parser->xmlToArray('config/access.xml', true, true)) {
			$error = 'Could not read/parse access control config file [config/access.xml]!';
		}

		// check/store Order section
		if (!$error) {
			if (isset($settings['ACCESS']['ORDER'][0])) {
				// strip all spaces
				$order = str_replace(' ', '', strtolower($settings['ACCESS']['ORDER'][0]));
				if ($order === 'allow,deny') {
					$this->access_control['order'] = true;
				}
				else if ($order === 'deny,allow') {
					$this->access_control['order'] = false;
				}
				else {
					$error = 'Access control config invalid \'order\' section: "' . $settings['ACCESS']['ORDER'][0] . '"';
				}
			}
			else {
				$error = 'Access control config missing section: order';
			}
		}

		// check/store Allow section
		if (!$error) {
			if (isset($settings['ACCESS']['ALLOW'][0])) {
				// check/store From entry(ies)
				if (isset($settings['ACCESS']['ALLOW'][0]['FROM'])) {
					foreach ($settings['ACCESS']['ALLOW'][0]['FROM'] as $from) {
						if ($from === 'all') {
							if (count($settings['ACCESS']['ALLOW'][0]['FROM']) > 1) {
								$error = 'Access control config \'allow\' section contains more besides "all" value';
								break;
							}
							else {
								$this->access_control['allowall'] = true;
							}
						}
						else {
							// !== 'all'
							if (in_array($from, $this->access_control['allow'])) {
								$error = 'Access control config \'allow\' section contains duplicate value: ' . $from;
								break;
							}
							else {
								if ($from !== '') {
									// ignore empty entries
									$this->access_control['allow'][] = $from;
								}
							}
						}
					}
				}
				else {
					$error = 'Access control config \'allow\' section must contain at least one \'from\' entry';
				}
			}
			else {
				$error = 'Access control config missing section: allow';
			}
		}

		// check/store Deny section
		if (!$error) {
			if (isset($settings['ACCESS']['DENY'][0])) {
				// check/store From entry(ies)
				if (isset($settings['ACCESS']['DENY'][0]['FROM'])) {
					foreach ($settings['ACCESS']['DENY'][0]['FROM'] as $from) {
						if ($from === 'all') {
							if (count($settings['ACCESS']['DENY'][0]['FROM']) > 1) {
								$error = 'Access control config \'deny\' section contains more besides "all" value';
								break;
							}
							else {
								$this->access_control['denyall'] = true;
							}
						}
						else {
							// !== 'all'
							if (in_array($from, $this->access_control['deny'])) {
								$error = 'Access control config \'deny\' section contains duplicate value: ' . $from;
								break;
							}
							else {
								if ($from !== '') {
									// ignore empty entries
									$this->access_control['deny'][] = $from;
								}
							}
						}
					}
				}
				else {
					$error = 'Access control config \'deny\' section must contain at least one \'from\' entry';
				}
			}
			else {
				$error = 'Access control config missing section: deny';
			}
		}

		// final consistency check
		if (!$error && $this->access_control['allowall'] && $this->access_control['denyall']) {
			$error = 'Access control config \'allow\' & \'deny\' sections cannot both use "all" value';
		}

		// load messages
		if (!$error) {
			if (isset($settings['ACCESS']['MESSAGES'][0])) {
				if (isset($settings['ACCESS']['MESSAGES'][0]['DENIED'][0])) {
					$this->access_control['messages']['denied'] = $settings['ACCESS']['MESSAGES'][0]['DENIED'][0];
				}
				else {
					$error = 'Access control config \'messages\' section missing value: denied';
				}
				if (isset($settings['ACCESS']['MESSAGES'][0]['DIALOG'][0])) {
					$this->access_control['messages']['dialog'] = $settings['ACCESS']['MESSAGES'][0]['DIALOG'][0];
				}
				else {
					$error = 'Access control config \'messages\' section missing value: dialog';
				}
				if (isset($settings['ACCESS']['MESSAGES'][0]['RELOAD'][0])) {
					$this->access_control['messages']['reload'] = $settings['ACCESS']['MESSAGES'][0]['RELOAD'][0];
				}
				else {
					$error = 'Access control config \'messages\' section missing value: reload';
				}
				if (isset($settings['ACCESS']['MESSAGES'][0]['XMLERR'][0])) {
					$this->access_control['messages']['xmlerr'] = $settings['ACCESS']['MESSAGES'][0]['XMLERR'][0];
				}
				else {
					$error = 'Access control config \'messages\' section missing value: xmlerr';
				}
				if (isset($settings['ACCESS']['MESSAGES'][0]['MISSING'][0])) {
					$this->access_control['messages']['missing'] = $settings['ACCESS']['MESSAGES'][0]['MISSING'][0];
				}
				else {
					$error = 'Access control config \'messages\' section missing value: missing';
				}
			}
			else {
				$error = 'Access control config missing section: messages';
			}
		}

		if (!$error) {
			// sort access lists
			sort($this->access_control['allow']);
			sort($this->access_control['deny']);

			// log console message
			if ($reload) {
				$aseco->console('[Access] Player access control reloaded from [config/access.xml]');
			}
			return true;
		}
		else {
			// log error message
			trigger_error('[Access] '. $error, E_USER_WARNING);
			$this->access_control = array();
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnectPostfix ($aseco, $player) {

		// if no access control, bail out immediately
		if (!$this->access_control) {
			return;
		}

		// get zone to check for access
		$access = implode('|', $player->zone);

		// check for empty zone
		if ($access === '') {
			if ($this->access_control['order']) {  // Allow,Deny
				;  // default denied
			}
			else {  // Deny,Allow
				return;  // default allowed
			}
		}
		else {
			if ($this->access_control['order']) {  // Allow,Deny
				// first check Allow list
				if ($this->access_control['allowall'] || $this->in_zones($access, $this->access_control['allow'])) {
					// then check Deny list
					if ($this->access_control['denyall'] || $this->in_zones($access, $this->access_control['deny'])) {
						;  // deny this nation
					}
					else {
						return;  // allow this nation
					}
				}
				else {
					;  // deny this nation
				}
			}
			else {
				// Deny,Allow
				// first check Deny list
				if ($this->access_control['denyall'] || $this->in_zones($access, $this->access_control['deny'])) {
					// then check Allow list
					if ($this->access_control['allowall'] || $this->in_zones($access, $this->access_control['allow'])) {
						return;  // allow this nation
					}
					else {
						;  // deny this nation
					}
				}
				else {
					return;  // allow this nation
				}
			}
		}

		// log & kick player
		$aseco->console('[Access] Player \'{1}\' denied access from "{2}" - kicking...', $player->login, $access);

		$message = $aseco->formatText($this->access_control['messages']['denied'],
			$aseco->stripStyles($player->nickname),
			'zone',
			$access
		);
		$aseco->sendChatMessage($message);
		$message = $aseco->formatText($this->access_control['messages']['dialog'],
			$access
		);
		$aseco->client->addCall('Kick', $player->login, $aseco->formatColors($message));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function in_zones ($access, $zones) {

		// check all zones for matching (leading part of) player's zone
		foreach ($zones as $zone) {
			if (in_array($access, $zone) === true) {
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

	public function admin_access ($aseco, $login, $param) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if ($param === 'help') {
			$header = '{#black}/admin access$g handles player access control:';
			$help = array();
			$help[] = array('...', '{#black}help',
			                'Displays this help information');
			$help[] = array('...', '{#black}list',
			                'Displays current access control settings');
			$help[] = array('...', '{#black}reload',
			                'Reloads updated access control settings');

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('Icons64x64_1', 'TrackInfo', -0.01), $help, array(0.8, 0.05, 0.15, 0.6), 'OK');
		}
		else if ($param === 'list') {
			$player->msgs = array();

			$head = 'Current player access control settings:';
			$info = array();
			// initialize with Order entry
			$info[] = array('Order:', '{#black}' . ($this->access_control['order'] ? 'Allow,Deny' : 'Deny,Allow'));
			$info[] = array();

			$lines = 2;
			$player->msgs[0] = array(1, $head, array(1.0, 0.2, 0.8), array('Icons128x128_1', 'ManiaZones'));

			// collect Allow entries
			$info[] = array('Allow:', '');
			$lines++;
			if ($this->access_control['allowall']) {
				$info[] = array('', '{#black}all');
				$lines++;
			}
			else {
				foreach ($this->access_control['allow'] as $from) {
					$info[] = array('', '{#black}' . $from);
					if (++$lines > 14) {
						$player->msgs[] = $info;
						$lines = 0;
						$info = array();
					}
				}
			}

			// insert spacer
			$info[] = array();
			if (++$lines > 14) {
				$player->msgs[] = $info;
				$lines = 0;
				$info = array();
			}

			// collect Deny entries
			$info[] = array('Deny:', '');
			$lines++;
			if ($this->access_control['denyall']) {
				$info[] = array('', '{#black}all');
				$lines++;
			}
			else {
				foreach ($this->access_control['deny'] as $from) {
					$info[] = array('', '{#black}' . $from);
					if (++$lines > 14) {
						$player->msgs[] = $info;
						$lines = 0;
						$info = array();
					}
				}
			}

			// add if last batch exists
			if (count($info) > 1) {
				$player->msgs[] = $info;
			}

			// display ManiaLink message
			$aseco->plugins['PluginManialinks']->display_manialink_multi($player);
		}
		else if ($param === 'reload') {
			// reload/check access control
			if ($this->onSync($aseco, true)) {
				$message = $this->access_control['messages']['reload'];
			}
			else {
				$this->access_control = array();
				$message = $this->access_control['messages']['xmlerr'];
			}
			$aseco->sendChatMessage($message, $player->login);
		}
		else {
			$message = $this->access_control['messages']['missing'];
			$aseco->sendChatMessage($message, $player->login);
		}
	}
}

?>
