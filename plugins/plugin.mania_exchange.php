<?php
/*
 * Plugin: Mania Exchange
 * ~~~~~~~~~~~~~~~~~~~~~~
 * » Handles maps from ManiaExchange and provides MX records message at start of each map.
 * » Based upon plugin.mxinfo.php from XAseco2/1.03 written by Xymph
 * » and plugin.rasp_jukebox.php from XAseco2/1.03 written by Xymph and others
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

	require_once('includes/maniaexchange/mxinfosearcher.inc.php');		// Provides MX searches


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

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Handles maps from ManiaExchange and provides MX records message at start of each map.');

		$this->addDependence('PluginWelcomeCenter',	Dependence::WANTED,	'1.0.0', null);

		$this->registerEvent('onSync',			'onSync');
		$this->registerEvent('onLoadingMap',		'onLoadingMap');

		$this->registerChatCommand('mxlist',		'chat_mxlist',		'Lists maps on ManiaExchange (see: /mxlist help)',		Player::PLAYERS);
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
		unset($settings['SETTINGS']);

		$this->config['mx_section']		 = 'TM2';

		$this->config['show_records']		= (int)$settings['SHOW_RECORDS'][0];

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
				$message = new Message('plugin.mania_exchange', 'chat_records');
				$message->addPlaceholders(
					$aseco->formatTime($aseco->server->maps->current->mx->recordlist[0]['replaytime']),
					$aseco->server->maps->current->mx->recordlist[0]['username']
				);
				$message->sendChatMessage();

//				if ($this->config['show_records'] === 2) {
//					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
//				}
//				else {
//					$aseco->sendChatMessage($message);
//				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mxlist ($aseco, $login, $chat_command, $chat_parameter) {

		$maps = new MXInfoSearcher($this->config['mx_section'], '', '', '', true);

		// List all found maps
		$data = array();
		foreach ($maps as $row) {

		}


//	["id"]=>
//	int(118768)
//	["name"]=>
//	string(7) "WBC_cbw"
//	["author"]=>
//	string(6) "papy54"
//	["uploaded"]=>
//	string(23) "2017-04-09T13:18:30.793"
//	["updated"]=>
//	string(23) "2017-04-09T13:18:30.793"
//	["envir"]=>
//	string(6) "Valley"
//	["mood"]=>
//	string(3) "Day"
//	["laps"]=>
//	int(1)
//	["awards"]=>
//	int(0)
//	["pageurl"]=>
//	string(48) "https://tm.mania-exchange.com/tracks/view/118768"
//	["thumburl"]=>
//	string(60) "https://tm.mania-exchange.com/tracks/screenshot/small/118768"
//	["dloadurl"]=>
//	string(52) "https://tm.mania-exchange.com/tracks/download/118768"



		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons64x64_1,ToolLeague1',
			'textcolors'		=> array('FF5F', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 2,
			'widths'		=> array(25, 75),
			'textcolors'		=> array('FF5F', 'FFFF'),
			'heading'		=> array('Image', 'Description'),
		);
		$settings_content = array(
			'title'			=> new Message('plugin.mania_exchange', 'window_title_mxlist'),
			'data'			=> $data,
			'about'			=> 'MANIA EXCHANGE/'. $this->getVersion(),
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}
}

?>
