<?php
/*
 * Plugin: Force Mod
 * ~~~~~~~~~~~~~~~~~
 * » Force environment Mods for Maps.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-10-25
 * Copyright:	2015 by undef.de
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
	$_PLUGIN = new PluginForceMod();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginForceMod extends Plugin {
	public $override	= false;
	public $mods		= array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Force environment Mods for Maps.');

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');
		$this->registerEvent('onUnloadingMap',		'onUnloadingMap');

		$this->registerChatCommand('modsreload',	'chat_modsreload',	'Reload the "Force Mod" settings.',	Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_modsreload ($aseco, $login, $chat_command, $chat_parameter) {

		// Reload the "force_mod.xml"
		$this->onSync($aseco);
		$aseco->sendChatMessage('{#admin}>> Reload of the configuration "force_mod.xml" done.', $login);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$xml = $aseco->parser->xmlToArray('config/force_mod.xml', true, true)) {
			trigger_error('[Checkpoints] Could not read/parse config file "config/force_mod.xml"!', E_USER_ERROR);
		}
		$xml = $xml['SETTINGS'];
		unset($xml['SETTINGS']);

		// Setup
		$this->override = ((strtoupper($xml['OVERRIDE'][0]) == 'TRUE') ? true : false);
		$this->mods = array();
		foreach ($xml['MODS'][0]['MOD'] as $mod) {
			if (strtoupper($mod['ENABLED'][0]) == 'TRUE') {
				$mod['ENVIRONMENT'][0] = ucfirst($mod['ENVIRONMENT'][0]);
				$this->mods[$mod['ENVIRONMENT'][0]][] = array(
					'Env'	=> $mod['ENVIRONMENT'][0],
					'Url'	=> str_replace(' ', '%20', $mod['URL'][0]),
				);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {
		if ($aseco->startup_phase === true) {
			$this->setupModforEnvironment($aseco, $map);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $map) {
		$this->setupModforEnvironment($aseco, $aseco->server->maps->getNextMap());
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setupModforEnvironment ($aseco, $map) {
		if (isset($this->mods[$map->environment]) && count($this->mods[$map->environment]) > 0) {
			try {
				// Getting next random index
				$index = mt_rand(0, count($this->mods[$map->environment])-1);
				$result = $aseco->client->query('SetForcedMods', $this->override, array($this->mods[$map->environment][$index]));
				if ($result === true) {
					$aseco->console('[ForceMod] Mod ['. $this->mods[$map->environment][$index]['Url'] .'] for environment ['. $map->environment .'] successfully installed');
				}
				else {
					$aseco->console('[ForceMod] Failed to setup Mod ['. $this->mods[$map->environment][$index]['Url'] .'] for environment ['. $map->environment .']!');
				}
			}
			catch (Exception $exception) {
				$aseco->console('[ForceMod] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SetForcedMods');
			}
		}
		else {
			$aseco->console('[ForceMod] No enabled Mod for environment ['. $map->environment .'] found, setup the defaults');
			try {
				$result = $aseco->client->query('SetForcedMods', false, array());
			}
			catch (Exception $exception) {
				$aseco->console('[ForceMod] Exception occurred: ['. $exception->getCode() .'] "'. $exception->getMessage() .'" - SetForcedMods');
			}
		}
	}
}

?>
