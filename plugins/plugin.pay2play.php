<?php

/* Pay2Play v1.02
*
* Plugin by Leigham.
*
* All settings are configurable in the pay2play.xml file.
*/

$_PLUGIN = new PluginPay2Play;

class PluginPay2Play extends Plugin {

	public $p2p = array();

	public function __construct() {

		$this->setVersion('1.02');
		$this->setAuthor('leigham');
		$this->setDescription('Charges planets to skip or replay a map');

		$this->addDependence('PluginRaspJukebox', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onStartup',			'setup');
		$this->registerEvent('onPlayerConnect',			'connect');
		$this->registerEvent('onBeginMap',			'check');
		$this->registerEvent('onWarmUpStatusChanged',		'check');
		$this->registerEvent('onEndRound',			'off');
		$this->registerEvent('onEndMap',			'off');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'click');
		$this->registerEvent('onBillUpdated',			'bill');
		$this->registerEvent('onEverySecond',			'time');
		$this->registerEvent('onShutdown',			'apocalypse');
	}

	public function setup($aseco) { //Read pay2play.xml and load settings

		$votes = array();

		if ($config = $aseco->parser->xmlToArray('config/pay2play.xml', true, true)) {
			$config = $config['SETTINGS'];

			if (strtolower($config['REPLAY'][0]['ENABLED'][0]) == 'true') {
				$this->p2p['replay']['active'] = true;
				$this->p2p['replay']['position'] = floatval($config['REPLAY'][0]['POSX'][0]).' '.floatval($config['REPLAY'][0]['POSY'][0]).' 1';
				$this->p2p['replay']['basecost'] = intval($config['REPLAY'][0]['COST'][0]);
				$this->p2p['replay']['cost'] = $this->p2p['replay']['basecost'];
				$this->p2p['replay']['max_val'] = intval($config['REPLAY'][0]['MAX_REPLAYS'][0]);
				$this->p2p['replay']['blink'] = ((strtolower($config['REPLAY'][0]['BLINK'][0]) == 'true') ? true : false);
				$this->p2p['replay']['ramp'] = ((strtolower($config['REPLAY'][0]['RAMPING'][0]) == 'true') ? true : false);
				if ($config['REPLAY'][0]['CVOTE_OFF'][0] == 'true') $votes[] = array('Command'	=> 'RestartMap', 'Ratio' => (float)-1);
				$this->p2p['replay']['total'] = 0;
				$this->p2p['replay']['success'] = false;
				$this->p2p['replay']['max'] = false;
			} else {
				$this->p2p['replay']['active'] = false;
			}

			if (strtolower($config['SKIP'][0]['ENABLED'][0]) == 'true') {
				$this->p2p['skip']['active'] = true;
				$this->p2p['skip']['position'] = floatval($config['SKIP'][0]['POSX'][0]).' '.floatval($config['SKIP'][0]['POSY'][0]).' 1';
				$this->p2p['skip']['cost'] = intval($config['SKIP'][0]['COST'][0]);
				$this->p2p['skip']['delay'] = intval($config['SKIP'][0]['DELAY'][0]);
				$this->p2p['skip']['blink'] = ((strtolower($config['SKIP'][0]['BLINK'][0]) == 'true') ? true : false);
				if ($config['REPLAY'][0]['CVOTE_OFF'][0] == 'true') $votes[] = array('Command'	=> 'NextMap', 'Ratio' => (float)-1);
				$this->p2p['skip']['success'] = false;
			} else {
				$this->p2p['skip']['active'] = false;
			}

			$this->p2p['style']['background_default']	= $config['STYLE'][0]['BACKGROUND_DEFAULT'][0];
			$this->p2p['style']['background_focus']		= $config['STYLE'][0]['BACKGROUND_FOCUS'][0];
			$this->p2p['style']['background_style']		= $config['STYLE'][0]['BACKGROUND_STYLE'][0];
			$this->p2p['style']['background_substyle']	= $config['STYLE'][0]['BACKGROUND_SUBSTYLE'][0];

			$this->p2p['score'] = false;
			$this->p2p['manialink'] = '12891';
			$this->p2p['thismap'] = array();
			$this->p2p['bills'] = array();
			$this->p2p['timelimit'] = -1;

			if (isset($votes[0])) {
				$this->p2p['oldvotes'] = $aseco->client->query('GetCallVoteRatios');
				$aseco->client->query('SetCallVoteRatios', $votes);
			}

		} else {
			trigger_error('[Pay2Play] Could not read/parse settings file [config/pay2play.xml]', E_USER_ERROR);
			return false;
		}
	}

	public function connect($aseco) { //Deal with new player

		if (!$this->p2p['score']) {
			if ($this->p2p['replay']['active']) {
				if (!$this->p2p['replay']['success']){
					if (!$this->p2p['replay']['max']) {
						$this->buildReplay($aseco);
					} else {
						$this->buildReplay($aseco, 'max');
					}
				} else {
					$this->buildReplay($aseco, 'success');
				}
			}
			if ($this->p2p['skip']['active']) {
				if (!$this->p2p['skip']['success']) {
					$this->buildSkip($aseco);
				} else {
					$this->buildSkip($aseco, 'success');
				}
			}
		}
	}

	public function check($aseco) { //Check map and load widgets

		$this->p2p['score'] = false;

		if ($this->p2p['replay']['active']) {
			$thismap = $aseco->server->maps->current->filename;

			if (isset($this->p2p['thismap']) && $this->p2p['thismap'] == $thismap) {
				if ($this->p2p['replay']['total'] >= $this->p2p['replay']['max_val'] && $this->p2p['replay']['max_val'] != 0) {
					$this->p2p['replay']['max'] = true;
					$this->buildReplay($aseco, 'max');
				} else {
					$this->p2p['replay']['max'] = false;
				}
			} else {
				$this->p2p['replay']['total'] = 0;
				$this->p2p['replay']['max'] = false;
			}
			$this->p2p['replay']['cost'] = $this->p2p['replay']['basecost'] * ($this->p2p['replay']['total'] + 1);
			$this->buildReplay($aseco);
			$this->p2p['thismap'] = $thismap;
		}
		if ($this->p2p['skip']['active'] == true && $this->p2p['replay']['total'] == 0) {
			$this->buildSkip($aseco);
		} elseif ($this->p2p['skip']['active']) {
			$this->buildSkip($aseco, 'replay');
		}

		if ($aseco->server->gameinfo->mode == Gameinfo::TIMEATTACK && !$aseco->warmup_phase) {
			$this->p2p['timelimit'] = time() + $aseco->server->gameinfo->time_attack['TimeLimit'];
		} elseif ($aseco->server->gameinfo->mode == Gameinfo::LAPS && !$aseco->warmup_phase) {
			$this->p2p['timelimit'] = time() + $aseco->server->gameinfo->laps['TimeLimit'];
		} else {
			$this->p2p['timelimit'] = 'disabled';
		}
	}

	public function off($aseco) { //Close widgets and reset variables

		$this->p2p['replay']['success'] = false;
		$this->p2p['skip']['success'] = false;
		$this->p2p['score'] = true;

		$xml = '<manialink id="'.$this->p2p['manialink'].'00"></manialink>
		        <manialink id="'.$this->p2p['manialink'].'01"></manialink>';
		$aseco->addManialink($xml, false, 0, false);
	}

	private function buildReplay($aseco, $state = false) { //Build replay widget

		if ($this->p2p['style']['background_default'] != '') {
			$layout = 'bgcolor="'. $this->p2p['style']['background_default'] .'" bgcolorfocus="'. $this->p2p['style']['background_focus'] .'"';
		}
		else {
			$layout = 'style="'. $this->p2p['style']['background_style'] .'" substyle="'. $this->p2p['style']['background_substyle'] .'"';
		}
		if ($state == 'success') {

			if ($this->p2p['replay']['blink']) {
				$a = array('style="TextTitle2Blink"', 0.8, 0.6, '$ccc', '$c90', 5);
			} else {
				$a = array('', 0.8, 0.6, '', '', 5);
			}

			$xml = '<manialink id="'.$this->p2p['manialink'].'00">
			<frame posn="'.$this->p2p['replay']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .'/>
			<label posn="2.25 -0.75 0.1" sizen="'.$a[5].' 2" halign="center" '.$a[0].' textsize="1" scale="'.$a[1].'" textcolor="FFFF" text="'.$a[3].'MAP"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center" '.$a[0].' textsize="1" scale="'.$a[2].'" textcolor="FC0F" text="'.$a[4].'WILL BE"/>
			<label posn="2.25 -3.55 0.1" sizen="'.$a[5].' 2" halign="center" '.$a[0].' textsize="1" scale="'.$a[1].'" textcolor="FFFF" text="'.$a[3].'REPLAYED"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center" '.$a[0].' textsize="1" scale="'.$a[2].'" textcolor="FC0F" text="'.$a[4].'NEXT!"/>
			</frame>
			</manialink>';

		} elseif ($state == 'max') {

			$xml = '<manialink id="'.$this->p2p['manialink'].'00">
			<frame posn="'.$this->p2p['replay']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .'/>
			<label posn="2.25 -0.75 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="MAXIMUM"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="REPLAY"/>
			<label posn="2.25 -3.55 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="LIMIT"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="REACHED!"/>
			</frame>
			</manialink>';

		} elseif ($state == 'skip') {

			$xml = '<manialink id="'.$this->p2p['manialink'].'00">
			<frame posn="'.$this->p2p['replay']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .'/>
			<label posn="2.25 -0.75 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="REPLAY"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="DISABLED"/>
			<label posn="2.25 -3.55 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="SKIP"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="INCOMING"/>
			</frame>
			</manialink>';

		} else {

			$xml = '<manialink id="'.$this->p2p['manialink'].'00">
			<frame posn="'.$this->p2p['replay']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .' action="'.$this->p2p['manialink'].'|replay"/>
			<label posn="2.25 -0.75 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="PAY '.$this->p2p['replay']['cost'].'"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="PLANETS"/>
			<label posn="2.25 -3.55 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="FOR"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="REPLAY"/>
			</frame>
			</manialink>';
		}
		$aseco->addManialink($xml, false, 0, false);
	}

	private function buildSkip($aseco, $state = false) { //Build skip widget

		if ($this->p2p['style']['background_default'] != '') {
			$layout = 'bgcolor="'. $this->p2p['style']['background_default'] .'" bgcolorfocus="'. $this->p2p['style']['background_focus'] .'"';
		}
		else {
			$layout = 'style="'. $this->p2p['style']['background_style'] .'" substyle="'. $this->p2p['style']['background_substyle'] .'"';
		}
		if ($state == 'success') {

			if ($this->p2p['skip']['blink']) {
				//$a = array('style="TextTitle2Blink"', 0.5, 0.35, '$ccc', '$c90', 7);
				$a = array('style="TextTitle2Blink"', 0.8, 0.6, '$ccc', '$c90', 5);
			} else {
				$a = array('', 0.8, 0.6, '', '', 5);
			}

			$xml = '<manialink id="'.$this->p2p['manialink'].'01">
			<frame posn="'.$this->p2p['skip']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .'/>
			<label posn="2.25 -0.75 0.1" sizen="'.$a[5].' 2" halign="center" '.$a[0].' textsize="1" textcolor="FFFF" scale="'.$a[1].'" text="'.$a[3].'MAP"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center" '.$a[0].' textsize="1" textcolor="FCOF" scale="'.$a[2].'" text="'.$a[4].'WILL BE"/>
			<label posn="2.25 -3.55 0.1" sizen="'.$a[5].' 2" halign="center" '.$a[0].' textsize="1" textcolor="FFFF" scale="'.$a[1].'" text="'.$a[3].'SKIPPED"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center" '.$a[0].' textsize="1" textcolor="FCOF" scale="'.$a[2].'" text="'.$a[4].'SHORTLY!"/>
			</frame>
			</manialink>';

		} elseif ($state == 'replay') {

			$xml = '<manialink id="'.$this->p2p['manialink'].'01">
			<frame posn="'.$this->p2p['skip']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .'/>
			<label posn="2.25 -0.75 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="SKIP"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="DISABLED"/>
			<label posn="2.25 -3.55 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="DURING"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="REPLAY"/>
			</frame>
			</manialink>';

		} else {

			$xml = '<manialink id="'.$this->p2p['manialink'].'01">
			<frame posn="'.$this->p2p['skip']['position'].'">
			<quad posn="0 0 0" sizen="4.6 6.5" '. $layout .' action="'.$this->p2p['manialink'].'|skip"/>
			<label posn="2.25 -0.75 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="PAY '.$this->p2p['skip']['cost'].'"/>
			<label posn="2.25 -2.3 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="PLANETS"/>
			<label posn="2.25 -3.55 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.9" textcolor="FFFF" text="FOR"/>
			<label posn="2.25 -5 0.1" sizen="5 2" halign="center"  textsize="1" scale="0.6" textcolor="FC0F" text="SKIP"/>
			</frame>
			</manialink>';

		}
		$aseco->addManialink($xml, false, 0, false);
	}

	public function click($aseco, $command) {  //Deal with button clicks

		$answer = $command[2];
		$player = $aseco->server->players->getPlayer($command[1]);

		if ($answer == $this->p2p['manialink'].'|replay') {
			$nextmap = $aseco->client->query('GetNextMapInfo');
			if ($this->p2p['thismap'] != $nextmap['FileName']) {
				$message = 'You need to pay '.$this->p2p['replay']['cost'].' planets to replay this map';
				$id = $aseco->client->query('SendBill', $player->login, $this->p2p['replay']['cost'], $message, '');
				$this->p2p['bills'][$id] = array($player->login, $player->nickname, 'replay');
			} else {
				$message = '>$f00 This track is already being replayed';
				$aseco->sendChatMessage($message, $player->login);
			}
		} elseif ($answer == $this->p2p['manialink'].'|skip') {
			if ($this->p2p['timelimit'] < ($this->p2p['skip']['delay'] + time() + 10) && $this->p2p['timelimit'] != 'disabled') {
				$message = '>$f00 This track will end before your action can be completed, please be patient.';
				$aseco->sendChatMessage($message, $player->login);
			} else {
				$message = 'You need to pay '.$this->p2p['skip']['cost'].' planets to skip this map';
				$id = $aseco->client->query('SendBill', $player->login, $this->p2p['skip']['cost'], $message, '');
				$this->p2p['bills'][$id] = array($player->login, $player->nickname, 'skip');
			}
		}
	}

	public function bill($aseco, $bill) {  //Deal with bill payments

		$id = $bill[0];
		// check for known bill ID
		if (array_key_exists($id, $this->p2p['bills'])) {
			// get bill info
			$login = $this->p2p['bills'][$id][0];
			$nickname = $this->p2p['bills'][$id][1];
			$state = $this->p2p['bills'][$id][2];
			$planets = $this->p2p[$state]['cost'];

			if ($state == 'replay') {
				// check bill state
				switch($bill[1]) {
					case 4:  // Payed (Paid)
					if (!$this->p2p['score']) {
						$uid = $aseco->server->maps->current->uid;
						$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['FileName'] = $aseco->server->maps->current->filename;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Name'] = $aseco->server->maps->current->name;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Env'] = $aseco->server->maps->current->environment;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Login'] = $login;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['Nick'] = $nickname;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['source'] = 'Pay2Play';
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['mx'] = false;
						$aseco->plugins['PluginRaspJukebox']->jukebox[$uid]['uid'] = $uid;
						$aseco->plugins['PluginRaspJukebox']->jukebox = array_reverse($aseco->plugins['PluginRaspJukebox']->jukebox, true);
						$aseco->releaseEvent('onJukeboxChanged', array('replay', $aseco->plugins['PluginRaspJukebox']->jukebox[$uid]));
					} else {
						if (isset($aseco->plugins['PluginAutotime'])) {
							$aseco->plugins['PluginAutotime']->restart = true;
						}
						$aseco->client->query('RestartMap');
					}
					$message = '$s$f90Player $z$s'.$nickname.'$z$s$f90  pays '.$planets.' planets and queues map for replay!';
					$aseco->sendChatMessage($message);
					$aseco->console('Player {1} paid {2} planets to replay the current track', $login, $planets);
					unset($this->p2p['bills'][$id]);
					$this->p2p['replay']['success'] = true;
					$this->p2p['replay']['total']++;
					if (!$this->p2p['score']) {
						$this->buildReplay($aseco, 'success');
					}
					break;
					case 5:  // Refused
					$message = '{#server}> {#error}Transaction refused!';
					$aseco->sendChatMessage($message, $login);
					unset($this->p2p['bills'][$id]);
					break;
					case 6:  // Error
					$message = '{#server}> {#error}Transaction failed: {#highlite}$i ' . $bill[2];
					if ($login != '')
						$aseco->sendChatMessage($message, $login);
					else
						$aseco->sendChatMessage($message);
					unset($this->p2p['bills'][$id]);
					break;
					default:  // CreatingTransaction/Issued/ValidatingPay(e)ment
					break;
				}
			} elseif ($state == 'skip') {
				// check bill state
				switch($bill[1]) {
					case 4:  // Payed (Paid)
					$time = time() + $this->p2p['skip']['delay'];
					$this->p2p['time']['skip'] = $time;
					$message = '$s$f90Player $z$s'.$nickname.'$z$s$f90  pays '.$planets.' planets. Map will be skipped shortly!';
					$aseco->sendChatMessage($message);
					$aseco->console('Player {1} paid {2} planets to skip the current track', $login, $planets);
					unset($this->p2p['bills'][$id]);
					$this->p2p['skip']['success'] = true;
					$this->buildSkip($aseco, 'success');
					$this->buildReplay($aseco, 'skip');
					break;
					case 5:  // Refused
					$message = '{#server}> {#error}Transaction refused!';
					$aseco->sendChatMessage($message, $login);
					unset($this->p2p['bills'][$id]);
					break;
					case 6:  // Error
					$message = '{#server}> {#error}Transaction failed: {#highlite}$i ' . $bill[2];
					if ($login != '')
						$aseco->sendChatMessage($message, $login);
					else
						$aseco->sendChatMessage($message);
					unset($this->p2p['bills'][$id]);
					break;
					default:  // CreatingTransaction/Issued/ValidatingPay(e)ment
					break;
				}
			}
		}
	}

	public function time($aseco) { //Deal with time functions

		if (isset($this->p2p['time']['skip'])) {
			$time = time();

			if ($time >= $this->p2p['time']['skip']) {
				// load the next map
				// don't clear scores if in Cup mode
				if ($aseco->server->gameinfo->mode == 5)
				$aseco->client->query('NextMap', true);
				else
				$aseco->client->query('NextMap');
				unset($this->p2p['time']['skip']);
			}
		}
	}

	public function apocalypse($aseco) { //Restore altered callvotes

		$aseco->client->query('SetCallVoteRatios', $this->p2p['oldvotes']);
	}

}

?>