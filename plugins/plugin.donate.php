<?php
/*
 * Plugin: Donate
 * ~~~~~~~~~~~~~~
 * » Processes planet donations to and payments from the server.
 * » Based upon plugin.donate.php from XAseco2/1.03 written by Xymph
 *
 *   Important: you must make an initial donation from a player login
 *   to your server login via the in-game message system, so that
 *   there are sufficient planets in the account to pay the Nadeo tax
 *   on the first /donate transaction.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-09-26
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
 *  - plugins/plugin.manialinks.php
 *
 */

	// Start the plugin
	$_PLUGIN = new PluginDonate();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginDonate extends Plugin {
	public $bills		= array();
	public $payments	= array();
	public $mindonation	= 10;						// minimum donation amount (because of Nadeo tax)
	public $publicappr	= 100;						// public appreciation threshold (show Thank You to all)
	public $donation_values	= array(20, 50, 100, 200, 500, 1000, 2000);	// default planets values for donate panel


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('1.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Processes planet donations to and payments from the server.');

		$this->addDependence('PluginManialinks', Dependence::REQUIRED, '1.0.0', null);

		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onBillUpdated',			'onBillUpdated');

		$this->registerChatCommand('donate',	'chat_donate',	'Donates planets to server',		Player::PLAYERS);
		$this->registerChatCommand('topdons',	'chat_topdons',	'Displays top 100 highest donators',	Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_donate ($aseco, $login, $chat_command, $chat_parameter) {

		$player = $aseco->server->players->getPlayer($login);
		$planets = $chat_parameter;

		// check for valid amount
		if ($planets != '' && is_numeric($planets)) {
			$planets = (int) $planets;
			// check for minimum donation
			if ($planets >= $this->mindonation) {
				// start the transaction
				$message = $aseco->formatText($aseco->getChatMessage('DONATION'),
					$planets,
					$aseco->server->name
				);

				$aseco->client->query('SendBill', $player->login, $planets, $aseco->formatColors($message), '');
				$billid = $aseco->client->getResponse();
				$this->bills[$billid] = array($player->login, $player->nickname, $planets);
			}
			else {
				$message = $aseco->formatText($aseco->getChatMessage('DONATE_MINIMUM'),
					$this->mindonation
				);
				$aseco->sendChatMessage($message, $player->login);
			}
		}
		else {
			$message = $aseco->getChatMessage('DONATE_HELP');
			$aseco->sendChatMessage($message, $player->login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_topdons ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		$player = $aseco->server->players->getPlayer($login);
		$top = 100;

		$query = "
		SELECT
			`p`.`NickName`,
			`x`.`Donations`
		FROM `players` AS `p`
		LEFT JOIN `players_extra` AS `x` ON `p`.`Id` = `x`.`PlayerId`
		WHERE `x`.`Donations` != 0
		ORDER BY `x`.`Donations` DESC
		LIMIT ". $top .";
		";

		$res = $aseco->mysqli->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$dons = array();
				$i = 1;
				while ($row = $res->fetch_object()) {
					$nickname = $row->NickName;
					if (!$aseco->settings['lists_colornicks']) {
						$nickname = $aseco->stripColors($nickname);
					}
					$dons[] = array(
						$i .'.',
						$aseco->formatNumber($row->Donations, 0),
						$nickname,
					);
					$i++;
				}


				// Setup settings for Window
				$settings_title = array(
					'icon'	=> 'Icons128x128_1,Coppers',
				);
				$settings_heading = array(
					'textcolors'	=> array('FFFF', 'FFFF', 'FFFF'),
				);
				$settings_columns = array(
					'columns'	=> 4,
					'widths'	=> array(11, 22, 67),
					'halign'	=> array('right', 'right', 'left'),
					'textcolors'	=> array('EEEF', 'EEEF', 'FFFF'),
					'heading'	=> array('#', 'Planets', 'Player'),
				);
				$window = new Window();
				$window->setLayoutTitle($settings_title);
				$window->setLayoutHeading($settings_heading);
				$window->setColumns($settings_columns);
				$window->setContent('Current TOP 100 Donators', $dons);
				$window->send($player, 0, false);
			}
			else {
				$message = '{#server}» {#error}No donator(s) found!';
				$aseco->sendChatMessage($message, $player->login);
			}

			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function admin_payment ($aseco, $login, $target, $amount) {

		// check parameters
		if ($target != '' && $amount != '' && is_numeric($amount) && $amount > 0) {
			// check for this server
			if ($target != $aseco->server->login) {
				// get current server planets
				$aseco->client->query('GetServerPlanets');
				$planets = $aseco->client->getResponse();

				// check for sufficient balance, including Nadeo tax (2 + 5%)
				if ($amount <= $planets - 2 - floor($amount * 0.05)) {
					// remember payment to be made
					$label = $aseco->formatText($aseco->getChatMessage('PAYMENT'), $amount, $target);
					$this->payments[$login] = array($target, (int) $amount, $label);
					$this->display_payment($login, $label);
				}
				else {
					$message = $aseco->formatText($aseco->getChatMessage('PAY_INSUFF'), $planets);
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				$message = $aseco->getChatMessage('PAY_SERVER');
				$aseco->sendChatMessage($message, $login);
			}
		}
		else {
			$message = $aseco->getChatMessage('PAY_HELP');
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Displays a payment dialog
	 *
	 * $login : player login to send dialog to
	 * $label : payment label string
	 */
	public function display_payment ($login, $label) {
		global $aseco;

		// Build manialink
		$xml = '<manialink id="UASECO-1">';
		$xml .= '<frame pos="0.4 0.15 0">';
		$xml .= '<quad size="0.8 0.3" style="Bgs1" substyle="BgTitlePage"/>';
		$xml .= '<label pos="-0.04 -0.04 -0.2" textsize="2" text="$i$159Initiating payment from server $fff'. $aseco->server->name .'$z $fff:"/>';
		$xml .= '<label pos="-0.04 -0.08 -0.2" textsize="2" text="$i$159Label: '. $aseco->formatColors($label) .'"/>';
		$xml .= '<label pos="-0.04 -0.12 -0.2" textsize="2" text="$159Would you like to pay now?"/>';
		$xml .= '<label pos="-0.22 -0.19 -0.2" halign="center" style="CardButtonMedium" text="Yes" action="28"/>';
		$xml .= '<label pos="-0.58 -0.19 -0.2" halign="center" style="CardButtonMedium" text="No" action="29"/>';
		$xml .= '</frame>';
		$xml .= '</manialink>';

		// Disable dialog once clicked
		$aseco->addManialink($xml, $login, 0, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function admin_pay ($aseco, $login, $answer) {

		// check for confirmation
		if ($answer) {
			// send server planets to login
			$aseco->client->query('Pay', $this->payments[$login][0], $this->payments[$login][1], $aseco->formatColors($this->payments[$login][2]));
			$billid = $aseco->client->getResponse();

			// store negative bill
			$this->bills[$billid] = array($login, $this->payments[$login][0], -$this->payments[$login][1]);
		}
		else {
			$message = $aseco->formatText($aseco->getChatMessage('PAY_CANCEL'),
				$this->payments[$login][0]
			);
			$aseco->sendChatMessage($message, $login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=PlayerUid, [1]=Login, [2]=Answer, [3]=Entries
	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// Get Player
		$player = $aseco->server->players->getPlayer($answer[1]);

		$action = (int)$answer[2];

		// check player answer
		switch ($action) {
			// Payment dialog buttons
			case 28:
				// log clicked command
				$aseco->console('[Player] Player [{1}] confirmed command "/admin pay"', $player->login);
				$aseco->plugins['PluginDonate']->admin_pay($aseco, $player->login, true);
				return;

			case 29:
				// log clicked command
				$aseco->console('Player [{1}] cancelled command "/admin pay"', $player->login);
				$aseco->plugins['PluginDonate']->admin_pay($aseco, $player->login, false);
				return;

			// Donate panel buttons
			case 30:
				// donate panel field 1
				$aseco->releaseChatCommand('/donate '. $this->donation_values[0], $player->login);
				return;

			case 31:
				// donate panel field 2
				$aseco->releaseChatCommand('/donate '. $this->donation_values[1], $player->login);
				return;

			case 32:
				// donate panel field 3
				$aseco->releaseChatCommand('/donate '. $this->donation_values[2], $player->login);
				return;

			case 33:
				// donate panel field 4
				$aseco->releaseChatCommand('/donate '. $this->donation_values[3], $player->login);
				return;

			case 34:
				// donate panel field 5
				$aseco->releaseChatCommand('/donate '. $this->donation_values[4], $player->login);
				return;

			case 35:
				// donate panel field 6
				$aseco->releaseChatCommand('/donate '. $this->donation_values[5], $player->login);
				return;

			case 36:
				// donate panel field 7
				$aseco->releaseChatCommand('/donate '. $this->donation_values[6], $player->login);
				return;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// [0]=BillId, [1]=State, [2]=StateName, [3]=TransactionId
	public function onBillUpdated ($aseco, $bill) {

		$billid = $bill[0];
		$txid = $bill[3];
		// check for known bill ID
		if (array_key_exists($billid, $this->bills)) {
			// get bill info
			$login		= $this->bills[$billid][0];
			$nickname	= $this->bills[$billid][1];
			$planets	= $this->bills[$billid][2];

			// check bill state
			switch ($bill[1]) {
				case 4:  // Payed (Paid)
					// check for donation or payment
					if ($planets > 0) {
						// check for public appreciation threshold
						if ($planets >= $this->publicappr) {
							$message = $aseco->formatText($aseco->getChatMessage('THANKS_ALL'),
								$aseco->server->name,
								$planets,
								$nickname
							);
							$aseco->sendChatMessage($message);
						}
						else {
							$message = $aseco->formatText($aseco->getChatMessage('THANKS_YOU'),
								$planets
							);
							$aseco->sendChatMessage($message, $login);
						}
						$aseco->console('[Donate] Player [{1}] donated {2} planets to this server (TxId {3})', $login, $planets, $txid);
						$this->updateDonations($login, $planets);

						// throw 'donation' event
						$aseco->releaseEvent('onDonation', array($login, $planets));
					}
					else {
						// $planets < 0, get new server planets
						$aseco->client->query('GetServerPlanets');
						$newplanets = $aseco->client->getResponse();

						$message = $aseco->formatText($aseco->getChatMessage('PAY_CONFIRM'),
							abs($planets),
							$nickname,
							$newplanets
						);
						$aseco->sendChatMessage($message, $login);
						$aseco->console('Server paid {1} planets to login "{2}" (TxId {3})', abs($planets), $login, $txid);
					}
					unset($this->bills[$billid]);
					break;

				case 5:  // Refused
					$message = '{#server}» {#error}Transaction refused!';
					$aseco->sendChatMessage($message, $login);
					$aseco->console('Refused transaction of {1} to login "{2}" (TxId {3})', $planets, $login, $txid);
					unset($this->bills[$billid]);
					break;

				case 6:  // Error
					$message = '{#server}» {#error}Transaction failed: {#highlite}$i ' . $bill[2];
					if ($login != '') {
						$aseco->sendChatMessage($message, $login);
					}
					else {
						$aseco->sendChatMessage($message);
					}
					$aseco->console('Failed transaction of {1} to login "{2}" (TxId {3})', $planets, $login, $txid);
					unset($this->bills[$billid]);
					break;

				default:  // CreatingTransaction/Issued/ValidatingPay(e)ment
					break;
			}
		}
		else {
			$aseco->console('BillUpdated for unknown BillId {1} {2} (TxId {3})', $billid, $bill[2], $txid);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getDonations ($login) {
		global $aseco;

		// Get player's donations
		$query = "
		SELECT
			`Donations`
		FROM `players_extra`
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if ($result) {
			$dbextra = $result->fetch_object();
			$result->free_result();
			return $dbextra->Donations;
		}
		else {
			trigger_error('[Donate] Could not get player\'s donations! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function updateDonations ($login, $donation) {
		global $aseco;

		// Update player's donations
		$query = "
		UPDATE `players_extra` SET
			`Donations` = `Donations` + ". $donation ."
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerId($login) .";
		";

		$result = $aseco->mysqli->query($query);
		if (!$result) {
			trigger_error('[Donate] Could not update player\'s donations! ('. $aseco->mysqli->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}
}

?>
