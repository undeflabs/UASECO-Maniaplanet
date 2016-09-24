<?php
/*
 * Plugin: Force Loadscreen
 * ~~~~~~~~~~~~~~~~~~~~~~~~
 * » Displays randomized images between the Map change.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-19
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
	$_PLUGIN = new PluginForceLoadscreen();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginForceLoadscreen extends Plugin {
	public $config = array();
	public $next_image_url = '';

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Displays randomized images between the Map change.');

		$this->registerEvent('onSync',		'onSync');
		$this->registerEvent('onLoadingMap',	'onLoadingMap');
		$this->registerEvent('onEndMap1',	'onEndMap1');
		$this->registerEvent('onUnloadingMap',	'onUnloadingMap');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Load the force_loadscreen.xml
		libxml_use_internal_errors(true);
		if (!$config = @simplexml_load_file('config/force_loadscreen.xml', null, LIBXML_COMPACT) ) {
			$aseco->console('[ForceLoadscreen] Could not read/parse config file "config/force_loadscreen.xml"!');
			foreach (libxml_get_errors() as $error) {
				$aseco->console("\t". $error->message);
			}
			libxml_clear_errors();
			trigger_error("[ForceLoadscreen] Please copy the 'force_loadscreen.xml' from this Package into the 'config' directory and do not forget to edit it!", E_USER_ERROR);
		}
		else {
			$aseco->console('[ForceLoadscreen] Parsed "config/force_loadscreen.xml" successfully...');
		}
		libxml_use_internal_errors(false);

		// Remove all comments
		unset($config->comment);


		// Setup/Store settings
		$this->config['manialinkid'] = 'ForceLoadscreen';

		$this->config['images'] = array();
		foreach ($config->images->image as $image) {
			if (strtolower((string)$image['enabled']) == 'true') {
				$list = strtolower((string)$image['env']);
				if (empty($list)) {
					$this->config['images'][] = array(
						'env'	=> array(),
						'url'	=> (string)$image['url'],
					);
				}
				else {
					$this->config['images'][] = array(
						'env'	=> explode(',', $list),
						'url'	=> (string)$image['url'],
					);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onLoadingMap ($aseco, $map) {

		$urls = array();
		foreach ($this->config['images'] as $image) {
			if (in_array(strtolower($map->environment), $image['env']) === true || count($image['env']) == 0) {
				$urls[] = $image['url'];
			}
		}
		$this->next_image_url = $urls[mt_rand(0, count($urls)-1)];

		$xml = '<manialink id="'. $this->config['manialinkid'] .'"></manialink>';			// Remove Main worker
		$xml .= '<manialink id="'. $this->config['manialinkid'] .'Preload" name="'. $this->config['manialinkid'] .'Preload" version="2">';
$maniascript = <<<EOL
<script><!--
/*
 * ----------------------------------
 * Function:	Preload @ plugin.force_loadscreen.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
PreloadImage("{$this->next_image_url}");
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= '</manialink>';

		$aseco->addManialink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $map) {

		$xml = '<manialink id="'. $this->config['manialinkid'] .'Preload"></manialink>';	// Remove Preloader
		$aseco->addManialink($xml, false);

		$xml = '<manialink id="'. $this->config['manialinkid'] .'" version="2">';
		$xml .= '<quad posn="-160.0 90.0 39.0" sizen="320.0 180.0" image="'. $this->next_image_url .'" hidden="true" id="Image"/>';

		// Set timeout
		$timeout = (($aseco->server->gameinfo->modebase['ChatTime'] * 1000) - 1500);

$maniascript = <<<EOL
<script><!--
/*
 * ----------------------------------
 * Function:	ForceLoadscreen @ plugin.force_loadscreen.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ----------------------------------
 */
Void QuadFadeIn (Text ChildId, Real ParamEndOpacity) {
	declare CMlQuad Quad <=> (Page.GetFirstChild(ChildId) as CMlQuad);
	declare Real EndOpacity = ParamEndOpacity;

	if (EndOpacity > 1.0) {
		EndOpacity = 1.0;
	}
	while (Quad.Opacity < EndOpacity) {
		if ((Quad.Opacity + 0.05) > 1.0) {
			Quad.Opacity = 1.0;
			break;
		}
		Quad.Opacity += 0.05;
		yield;
	}
}
main() {
	declare CMlQuad Image			<=> (Page.GetFirstChild("Image") as CMlQuad);

	declare Integer Timeout 		= (CurrentTime + {$timeout});
	declare Integer RefreshInterval		= 125;
	declare Integer RefreshTime		= CurrentTime;

	Image.Opacity				= 0.0;
	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}
		if (CurrentTime > RefreshTime) {
			if (CurrentTime >= Timeout) {
				Image.Visible = True;
 				QuadFadeIn("Image", 1.0);
				RefreshTime = CurrentTime + 120;
			}

			// Reset RefreshTime
			RefreshTime = (CurrentTime + RefreshInterval);
		}
	}
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= '</manialink>';

		$aseco->sendManialink($xml, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $map) {
		$xml = '<manialink id="'. $this->config['manialinkid'] .'"></manialink>';	// Remove Main worker
		$aseco->sendManialink($xml, false);
	}
}

?>
