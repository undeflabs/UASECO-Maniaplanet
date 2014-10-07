<?php
/*
 * Plugin: Mania Exchange Info
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * » Displays MX map info and records, and provides world record message  at start of each map.
 * » Based upon plugin.mxinfo.php from XAseco2/1.03 written by Xymph
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-07
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
 *  - plugins/plugin.manialinks.php
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
	public $mxdata;							// cached MX data

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Selects ManiaLink panel templates.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onBeginMap1',	'onBeginMap1');

		$this->registerChatCommand('mxinfo',	'chat_mxinfo',	'Displays MX info {Map_ID/MX_ID}',	Player::PLAYERS);
		$this->registerChatCommand('mxrecs',	'chat_mxrecs',	'Displays MX records {Map_ID/MX_ID}',	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	public function onBeginMap1 ($aseco, $data) {

		// obtain MX records
		$this->mxdata = $aseco->server->maps->current->mx;
		if ($this->mxdata && !empty($this->mxdata->recordlist)) {
			// check whether to show MX record at start of map
			if ($aseco->settings['show_mxrec'] > 0) {
				$message = $aseco->formatText($aseco->getChatMessage('MXREC'),
					($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->mxdata->recordlist[0]['stuntscore'] : $aseco->formatTime($this->mxdata->recordlist[0]['replaytime'])),
					$this->mxdata->recordlist[0]['username']
				);
				if ($aseco->settings['show_mxrec'] == 2) {
					$aseco->releaseEvent('onSendWindowMessage', array($message, false));
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
			$aseco->releaseEvent('onManiaExchangeBestLoaded', ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? $this->mxdata->recordlist[0]['stuntscore'] : $this->mxdata->recordlist[0]['replaytime']));
		}
		else {
			$aseco->releaseEvent('onManiaExchangeBestLoaded', ($aseco->server->gameinfo->mode == Gameinfo::STUNTS ? 0 : 0));
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mxinfo ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));

		// check for optional Map/MX ID parameter
		$id = $aseco->server->maps->current->uid;
		$name = $aseco->server->maps->current->name;
		$game = 'TM2';
		if ($command['params'][0] != '') {
			if (is_numeric($command['params'][0]) && $command['params'][0] > 0) {
				$tid = ltrim($command['params'][0], '0');
				// check for possible map ID
				if ($tid <= count($player->maplist)) {
					// find UID by given map ID
					$tid--;
					$id = $player->maplist[$tid]['uid'];
					$name = $player->maplist[$tid]['name'];
				}
				else {
					// consider it a MX ID
					$id = $tid;
					$name = '';
				}
			}
			else {
				$message = '{#server}» {#highlite}' . $command['params'][0] . '{#error} is not a valid Map/MX ID!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}

		// obtain MX info
		if (isset($this->mxdata->uid) && $this->mxdata->uid == $id) {
			$data = $this->mxdata;  // use cached data
		}
		else {
			$data = new MXInfoFetcher($game, $id, false);
		}
		if (!$data || $data->error != '') {
			$message = '{#server}» {#highlite}' . ($name != '' ? $aseco->stripColors($name) : $id) . '{#error} is not a known MX map, or MX is down!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// compile & send message
		$header = 'MX Info for: {#black}' . $data->name;
		$links = array($data->imageurl . '?.jpg', false,
			'$l[' . $data->pageurl . ']Visit MX Page',
			'$l[' . $data->dloadurl . ']Download Map'
		);
		$stats = array();
		$stats[] = array('MX ID', '{#black}' . $data->id,
			'Type/Style', '{#black}' . $data->type . '$g / {#black}' . $data->style
		);
		$stats[] = array('UID', '{#black}$n' . $data->uid,
			'Env/Mood', '{#black}' . $data->envir . '$g / {#black}' . $data->mood
		);
		$stats[] = array('Author', '{#black}' . $data->author,
			'Routes', '{#black}' . $data->routes
		);
		$stats[] = array('Display cost', '{#black}' . $data->dispcost,
			'Difficulty', '{#black}' . $data->diffic
		);
		$stats[] = array('Uploaded', '{#black}' . str_replace('T', ' ', preg_replace('/:\d\d\.\d\d\d$/', '', $data->uploaded)),
			'Length', '{#black}' . $data->length
		);
		$stats[] = array('Updated', '{#black}' . str_replace('T', ' ', preg_replace('/:\d\d\.\d\d\d$/', '', $data->updated)),
			'Awards', '{#black}' . $data->awards
		);
		$stats[] = array('Track Value', '{#black}' . $data->trkvalue,
			 'Replay', ($data->replayurl ?
			 '{#black}$l[' . $data->replayurl . ']Download$l' : '<none>')
		);

		// display custom ManiaLink message
		$this->display_manialink_map($player->login, $header, array('Icons64x64_1', 'Maximize', -0.01), $links, $stats, array(1.15, 0.2, 0.45, 0.2, 0.3), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_mxrecs ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$command['params'] = explode(' ', preg_replace('/ +/', ' ', $chat_parameter));

		// check for optional Map/MX ID parameter
		$id = $aseco->server->maps->current->uid;
		$name = $aseco->server->maps->current->name;
		$game = 'TM2';
		if ($command['params'][0] != '') {
			if (is_numeric($command['params'][0]) && $command['params'][0] > 0) {
				$tid = ltrim($command['params'][0], '0');
				// check for possible map ID
				if ($tid <= count($player->maplist)) {
					// find UID by given map ID
					$tid--;
					$id = $player->maplist[$tid]['uid'];
					$name = $player->maplist[$tid]['name'];
				}
				else {
					// consider it a MX ID
					$id = $tid;
					$name = '';
				}
			}
			else {
				$message = '{#server}» {#highlite}' . $tid . '{#error} is not a valid Map/MX ID!';
				$aseco->sendChatMessage($message, $player->login);
				return;
			}
		}

		// obtain MX records
		if (isset($this->mxdata->uid) && $this->mxdata->uid == $id) {
			$data = $this->mxdata;  // use cached data
		}
		else {
			$data = new MXInfoFetcher($game, $id, true);
		}
		if (!$data || $data->error != '') {
			$message = '{#server}» {#highlite}' . ($name != '' ? $aseco->stripColors($name) : $id) . '{#error} is not a known MX map, or MX is down!';
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		if (empty($data->recordlist)) {
			$message = '{#server}» {#error}No MX records found for {#highlite}$i ' . $data->name;
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		// compile message
		$header = 'MX Top-15 Records: {#black}' . $data->name;
		$recs = array();
		$top = 15;
		$bgn = '{#black}';  // name begin

		for ($i = 0; $i < count($data->recordlist) && $i < $top; $i++) {
			$recs[] = array(str_pad($i+1, 2, '0', STR_PAD_LEFT) . '.',
				$bgn . $data->recordlist[$i]['username'],
				($data->type == 'Stunts' ?
				$data->recordlist[$i]['stuntscore'] :
				$aseco->formatTime($data->recordlist[$i]['replaytime']))
			);
		}

		// display ManiaLink message
		$aseco->plugins['PluginManialinks']->display_manialink($player->login, $header, array('BgRaceScore2', 'Podium'), $recs, array(0.9, 0.1, 0.5, 0.3), 'OK');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays custom MX map ManiaLink window to a player
	 *
	 * $login : player login to send window to
	 * $header: string
	 * $icon  : array( $style, $substyle {, $sizechg} )
	 * $links : array( $image, $square, $page, $download )
	 * $data  : array( $line1=array($col1, $col2, ...), $line2=array(...) )
	 * $widths: array( $overal, $col1, $col2, ...)
	 * $button: string
	 *
	 * A $line with one $col will occupy the full window width,
	 * otherwise all $line's must have the same number of columns,
	 * as should $widths (+1 for $overall).
	 */
	public function display_manialink_map ($login, $header, $icon, $links, $data, $widths, $button) {
		global $aseco;

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}
		$style = $player->style;
		$square = $links[1];

		$hsize = $style['HEADER'][0]['TEXTSIZE'][0];
		$bsize = $style['BODY'][0]['TEXTSIZE'][0];
		$lines = count($data);

		// build manialink header & window
		$xml  = '<manialink id="UASECO-1"><frame pos="' . ($widths[0]/2) . ' 0.47 0">' .
		        '<quad size="' . $widths[0] . ' ' . (0.42+($square?0.1:0)+2*$hsize+$lines*$bsize) .
		        '" style="' . $style['WINDOW'][0]['STYLE'][0] .
		        '" substyle="' . $style['WINDOW'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add header
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -0.01 -0.1" size="' . ($widths[0]-0.02) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER'][0]['STYLE'][0] .
		        '" substyle="' . $style['HEADER'][0]['SUBSTYLE'][0] . '"/>' . LF;
		if (is_array($icon)) {
			$isize = $hsize;
			if (isset($icon[2]))
				$isize += $icon[2];
			$xml .= '<quad pos="-0.055 -0.045 -0.2" size="' . $isize . ' ' . $isize .
			        '" halign="center" valign="center" style="' . $icon[0] . '" substyle="' . $icon[1] . '"/>' . LF;
			$xml .= '<label pos="-0.10 -0.025 -0.2" size="' . ($widths[0]-0.12) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		} else {
			$xml .= '<label pos="-0.03 -0.025 -0.2" size="' . ($widths[0]-0.05) . ' ' . $hsize .
			        '" halign="left" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
			        '" text="' . $aseco->handleSpecialChars($header) . '"/>' . LF;
		}

		// add image
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.02+$hsize) .
		        ' -0.2" size="0.4 ' . ($square ? '0.4' : '0.3') . '" halign="center" image="' . $aseco->handleSpecialChars($links[0]) . '"/>' . LF;

		// add body
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.33+($square?0.1:0)+$hsize) .
		        ' -0.1" size="' . ($widths[0]-0.02) . ' ' . (0.015+$hsize+$lines*$bsize) .
		        '" halign="center" style="' . $style['BODY'][0]['STYLE'][0] .
		        '" substyle="' . $style['BODY'][0]['SUBSTYLE'][0] . '"/>' . LF;

		// add lines with optional columns
		$xml .= '<format style="' . $style['BODY'][0]['TEXTSTYLE'][0] . '"/>' . LF;
		$cnt = 0;
		foreach ($data as $line) {
			$cnt++;
			if (!empty($line)) {
				for ($i = 0; $i < count($widths)-1; $i++) {
					$xml .= '<label pos="-' . (0.025+array_sum(array_slice($widths,1,$i))) .
					        ' -' . (0.305+($square?0.1:0)+$hsize+$cnt*$bsize) .
					        ' -0.2" size="' . $widths[$i+1] . ' ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['BODY'][0]['TEXTSTYLE'][0] .
					        '" text="' . $aseco->handleSpecialChars($line[$i]) . '"/>' . LF;
				}
			}
		}

		// add links
		$xml .= '<format style="' . $style['HEADER'][0]['TEXTSTYLE'][0] . '"/>' . LF;
		$xml .= '<label pos="-' . ($widths[0]*0.25) . ' -' . (0.36+($square?0.1:0)+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . ($widths[0]/2) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
		        '" text="' . $aseco->handleSpecialChars($links[2]) . '"/>' . LF;
		$xml .= '<label pos="-' . ($widths[0]*0.75) . ' -' . (0.36+($square?0.1:0)+$hsize+$lines*$bsize) .
		        ' -0.2" size="' . ($widths[0]/2) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER'][0]['TEXTSTYLE'][0] .
		        '" text="' . $aseco->handleSpecialChars($links[3]) . '"/>' . LF;

		// add button (action "0" = close) & footer
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.35+($square?0.1:0)+2*$hsize+$lines*$bsize) .
		        ' -0.2" size="0.08 0.08" halign="center" style="Icons64x64_1" substyle="Close" action="0"/>' . LF;
		$xml .= '</frame></manialink>';
		$xml = str_replace('{#black}', $style['WINDOW'][0]['BLACKCOLOR'][0], $xml);

		//$aseco->console_text($xml);
		$aseco->client->addCall('SendDisplayManialinkPageToLogin', $login, $aseco->formatColors($xml), 0, true);
	}
}

?>
