<?php
/*
 * Plugin: Mania Exchange Info
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Provides world record message at start of each map.
 * » Based upon plugin.mxinfo.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-11-18
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
 *  - includes/core/mxinfofetcher.class.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginManiaExchangeInfo();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginManiaExchangeInfo extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Provides world record message at start of each map.');

		$this->registerEvent('onBeginMap1',	'onBeginMap1');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onBeginMap1 ($aseco, $data) {

		// obtain MX records
		$mxdata = $aseco->server->maps->current->mx;
		if ($mxdata && !empty($mxdata->recordlist)) {
			// check whether to show MX record at start of map
			if ($aseco->settings['show_mxrec'] > 0) {
				$message = $aseco->formatText($aseco->getChatMessage('MXREC'),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $mxdata->recordlist[0]['stuntscore'] : $aseco->formatTime($mxdata->recordlist[0]['replaytime'])),
					$mxdata->recordlist[0]['username']
				);
				if ($aseco->settings['show_mxrec'] == 2) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}
}

?>
