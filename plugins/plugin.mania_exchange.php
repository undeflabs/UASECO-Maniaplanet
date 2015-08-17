<?php
/*
 * Plugin: Mania Exchange
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Provides world record message at start of each map.
 * » Based upon plugin.mxinfo.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-17
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
 *  - plugins/plugin.welcome_center.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginManiaExchange();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginManiaExchange extends Plugin {
	public $config;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides world record message at start of each map.');

		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');

//		$this->registerChatCommand('mx', 'chat_mx', 'xxxxx', Player::MASTERADMINS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onSync ($aseco) {

		if (!$settings = $aseco->parser->xmlToArray('config/mania_exchange.xml', true, true)) {
			trigger_error('[ManiaExchange] Could not read/parse config file [config/mania_exchange.xml]!', E_USER_ERROR);
		}
		$settings = $settings['SETTINGS'];

		$this->config['show_records']		= (int)$settings['SHOW_RECORDS'][0];

		$this->config['messages']['records']	= $settings['MESSAGES'][0]['RECORDS'][0];

//		if (isset($aseco->plugins['PluginWelcomeCenter'])) {
//			$aseco->plugins['PluginWelcomeCenter']->addInfoMessage('Find the MX info for a map with the "/mxinfo" command!');
//		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onLoadingMap ($aseco, $data) {

		// Obtain MX records
		if ($aseco->server->maps->current->mx && !empty($aseco->server->maps->current->mx->recordlist)) {
			// check whether to show MX record at start of map
			if ($this->config['show_records'] > 0) {
				$message = $aseco->formatText($this->config['messages']['records'],
					$aseco->formatTime($aseco->server->maps->current->mx->recordlist[0]['replaytime']),
					$aseco->server->maps->current->mx->recordlist[0]['username']
				);
				if ($this->config['show_records'] == 2) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}

//	/*
//	#///////////////////////////////////////////////////////////////////////#
//	#									#
//	#///////////////////////////////////////////////////////////////////////#
//	*/
//
//	public function chat_mx ($aseco, $login, $chat_command, $chat_parameter) {
//
//		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
//			return;
//		}
//
//	}
}

?>
