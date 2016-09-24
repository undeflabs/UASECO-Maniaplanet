<?php
/*
 * Plugin: Customize Quit Dialog
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Customize the Quit-Dialog when a Player wants to leave the server.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-23
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
	$_PLUGIN = new PluginCustomizeQuitDialog();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginCustomizeQuitDialog extends Plugin {
	public $config = array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Customize the Quit-Dialog when a Player wants to leave the server.');

		$this->registerEvent('onSync', 'onSync');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/customize_quit_dialog.xml', true, true)) {
			trigger_error('[CustomizeQuitDialog] Could not read/parse config file "config/customize_quit_dialog.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		$xml = @file_get_contents($this->config['MANIALINK_FILE'][0]);
		$this->config['PROPOSE_ADD_TO_FAVORITES'][0] = ((strtoupper($this->config['PROPOSE_ADD_TO_FAVORITES'][0]) == 'TRUE') ? true : false);

		if ($xml !== false) {
			$aseco->client->query('CustomizeQuitDialog', $xml, $this->config['SEND_TO_SERVER'][0], $this->config['PROPOSE_ADD_TO_FAVORITES'][0], ((int)$this->config['DELAY_QUIT_BUTTON'][0] * 1000));
		}
		else {
			trigger_error('[CustomizeQuitDialog] Could not read manialink file "'. $this->config['MANIALINK_FILE'][0] .'"!', E_USER_WARNING);
		}
	}
}

?>
