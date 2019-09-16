<?php
/*
 * Plugin: Donate
 * ~~~~~~~~~~~~~~
 * » Processes planet donations to and payments from the server.
 * » Based upon plugin.donate.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginDonate();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginDonate extends Plugin {
	public $bills		= array();
	public $payments	= array();
	public $mindonation	= 10;
	public $publicappr	= 50;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setCoAuthors('aca');
		$this->setVersion('1.0.1');
		$this->setBuild('2019-09-16');
		$this->setCopyright('2014 - 2019 by undef.de');
		$this->setDescription(new Message('plugin.donate', 'plugin_description'));

		$this->registerEvent('onSync',						'onSync');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onBillUpdated',				'onBillUpdated');

		$this->registerChatCommand('donate',  'chat_donate',	new Message('plugin.donate', 'slash_donate_description'),	Player::PLAYERS);
		$this->registerChatCommand('topdons', 'chat_topdons',	new Message('plugin.donate', 'slash_topdons_description'),	Player::PLAYERS);

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Read Configuration
		if (!$this->config = $aseco->parser->xmlToArray('config/donate.xml', true, true)) {
			trigger_error('[Donate] Could not read/parse config file "config/donate.xml"!', E_USER_ERROR);
		}
		$this->config = $this->config['SETTINGS'];
		unset($this->config['SETTINGS']);

		$this->mindonation	= (int)$this->config['MINIMUM_DONATION'][0];
		$this->publicappr	= (int)$this->config['PUBLIC_APPRECIATION_THRESHOLD'][0];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_donate ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}
		$planets = $chat_parameter;

		// check for valid amount
		if ($planets !== '' && is_numeric($planets)) {
			$planets = (int) $planets;
			// check for minimum donation
			if ($planets >= $this->mindonation) {
				// start the transaction
				$msg = new Message('plugin.donate', 'message_donation');
				$msg->addPlaceholders($planets, $aseco->server->name);
				$msg->sendChatMessage($login);

				$billid = $aseco->client->query('SendBill', $player->login, $planets, $msg->finish($login), '');
				$this->bills[$billid] = array($player->login, $player->nickname, $planets);
			}
			else {
				$msg = new Message('plugin.donate', 'message_donate_minimum');
				$msg->addPlaceholders($this->mindonation);
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('plugin.donate', 'message_donate_help');
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_topdons ($aseco, $login, $chat_command, $chat_parameter) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		$top = 100;
		$query = "
		SELECT
			`Nickname`,
			`Donations`
		FROM `%prefix%players`
		WHERE `Donations` != 0
		ORDER BY `Donations` DESC
		LIMIT ". $top .";
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$dons = array();
				$i = 1;
				while ($row = $res->fetch_object()) {
					$nickname = $row->Nickname;
					if (!$aseco->settings['lists_colornicks']) {
						$nickname = $aseco->stripStyles($nickname);
					}
					$dons[] = array(
						$i .'.',
						$aseco->formatNumber($row->Donations, 0),
						$nickname,
					);
					$i++;
				}


				// Setup settings for Window
				$settings_styles = array(
					'icon'			=> 'Icons128x128_1,Coppers',
					'textcolors'		=> array('FFFF', 'FFFF', 'FFFF'),
				);
				$settings_columns = array(
					'columns'		=> 4,
					'widths'		=> array(11, 22, 67),
					'halign'		=> array('right', 'right', 'left'),
					'textcolors'		=> array('EEEF', 'EEEF', 'FFFF'),
					'heading'		=> array('#', 'Planets', 'Player'),
				);
				$settings_content = array(
					'title'			=> 'Current TOP 100 Donators',
					'data'			=> $dons,
					'about'			=> 'DONATE/'. $this->getVersion(),
					'mode'			=> 'columns',
				);

				$window = new Window();
				$window->setStyles($settings_styles);
				$window->setColumns($settings_columns);
				$window->setContent($settings_content);
				$window->send($player, 0, false);
			}
			else {
				$message = new Message('plugin.donate', 'message_no_donators_found');
				$message->sendChatMessage($login);
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
		if ($target !== '' && $amount !== '' && is_numeric($amount) && $amount > 0) {
			// check for this server
			if ($target !== $aseco->server->login) {
				// get current server planets
				$planets = $aseco->client->query('GetServerPlanets');

				// check for sufficient balance, including Nadeo tax (2 + 5%)
				if ($amount <= $planets - 2 - floor($amount * 0.05)) {
					// remember payment to be made
					$msg = new Message('plugin.donate', 'message_payment');
					$msg->addPlaceholders($amount, $target);
					$label = $msg->finish($login);
					$this->payments[$login] = array($target, (int) $amount, $label);
					$this->display_payment($login, $label);
				}
				else {
					$msg = new Message('plugin.donate', 'message_pay_insuff');
					$msg->addPlaceholders($planets);
					$msg->sendChatMessage($login);
				}
			}
			else {
				$msg = new Message('plugin.donate', 'message_pay_server');
				$msg->sendChatMessage($login);
			}
		}
		else {
			$msg = new Message('plugin.donate', 'message_pay_help');
			$msg->sendChatMessage($login);
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

		// Get Player object
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Setup the styles
		$settings_style = array(
			'textcolor'		=> '09FF',
			'icon'			=> 'Icons64x64_1,Outbox',
		);


		// Build the buttons
		$buttons = array(
			array(
				'title'		=> (new Message('common', 'yes'))->finish($player->login),
				'action'	=> 'PluginDonate?Action=Payout&Answer=Confirm',
			),
			array(
				'title'		=> (new Message('common', 'no'))->finish($player->login),
				'action'	=> 'PluginDonate?Action=Payout&Answer=Cancel',
			),
		);

		// Setup content
		$settings_content = array(
			'title'			=> 'Initiating payment from server "'. $aseco->stripStyles($aseco->server->name) .'"',
			'message'		=> $label . LF .'Would you like to pay now?',
			'buttons'		=> $buttons,
		);

		// Create the Dialog
		$dialog = new Dialog();
		$dialog->setStyles($settings_style);
		$dialog->setContent($settings_content);
		$dialog->send($player);
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
			$billid = $aseco->client->query('Pay', $this->payments[$login][0], $this->payments[$login][1], $aseco->formatColors($this->payments[$login][2]));

			// store negative bill
			$this->bills[$billid] = array($login, $this->payments[$login][0], -$this->payments[$login][1]);
		}
		else {
			$msg = new Message('plugin.donate', 'message_pay_cancel');
			$msg->addPlaceholders($this->payments[$login][0]);
			$msg->sendChatMessage($login);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $param) {

		// Get Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// PluginDonate?Action=Payout&Answer=[Confirm|Cancel]
		if ($param['Action'] === 'Payout' && $param['Answer'] === 'Confirm') {
			$aseco->console('[Donate] Player [{1}] confirmed command "/admin pay"', $player->login);
			$aseco->plugins['PluginDonate']->admin_pay($aseco, $player->login, true);
		}
		else if ($param['Action'] === 'Payout' && $param['Answer'] === 'Cancel') {
			$aseco->console('[Donate] Player [{1}] cancelled command "/admin pay"', $player->login);
			$aseco->plugins['PluginDonate']->admin_pay($aseco, $player->login, false);
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
							$msg = new Message('plugin.donate', 'message_thanks_all');
							$msg->addPlaceholders($aseco->server->name, $planets, $nickname);
							$msg->sendChatMessage();
						}
						else {
							$msg = new Message('plugin.donate', 'message_thanks_you');
							$msg->addPlaceholders($planets);
							$msg->sendChatMessage($login);
						}
						$aseco->console('[Donate] Player [{1}] donated {2} Planets to this server (TxId {3})', $login, $planets, $txid);
						$this->updateDonations($login, $planets);

						// throw 'donation' event
						$aseco->releaseEvent('onDonation', array($login, $planets));
					}
					else {
						// $planets < 0, get new server planets
						$newplanets = $aseco->client->query('GetServerPlanets');

						$msg = new Message('plugin.donate', 'message_pay_confirm');
						$msg->addPlaceholders(abs($planets), $nickname, $newplanets);
						$msg->sendChatMessage($login);

						$aseco->console('[Donate] Server paid {1} Planets to [{2}] (TxId {3})', abs($planets), $login, $txid);
					}
					unset($this->bills[$billid]);
					break;

				case 5:  // Refused
					$msg = new Message('plugin.donate', 'message_transaction_refused');
					$msg->sendChatMessage($login);

					$aseco->console('[Donate] Refused transaction of {1} by login [{2}] (TxId {3})', $planets, $login, $txid);
					unset($this->bills[$billid]);
					break;

				case 6:  // Error
					$msg = new Message('plugin.donate', 'message_transaction_failed');
					$msg->addPlaceholders($bill[2]);

					if ($login !== '') {
						$msg->sendChatMessage($login);
					}
					else {
						$msg->sendChatMessage();
					}
					$aseco->console('[Donate] Failed transaction of {1} to login "{2}" (TxId {3})', $planets, $login, $txid);
					unset($this->bills[$billid]);
					break;

				default:  // CreatingTransaction/Issued/ValidatingPay(e)ment
					break;
			}
		}
		else {
			$aseco->console('[Donate] BillUpdated for unknown BillId {1} {2} (TxId {3})', $billid, $bill[2], $txid);
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
		FROM `%prefix%players`
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerIdByLogin($login) .";
		";

		$result = $aseco->db->query($query);
		if ($result) {
			$dbextra = $result->fetch_object();
			$result->free_result();
			return $dbextra->Donations;
		}
		else {
			trigger_error('[Donate] Could not get player\'s donations! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
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
		UPDATE `%prefix%players` SET
			`Donations` = `Donations` + ". $donation ."
		WHERE `PlayerId` = ". $aseco->server->players->getPlayerIdByLogin($login) .";
		";

		$result = $aseco->db->query($query);
		if (!$result) {
			trigger_error('[Donate] Could not update player\'s donations! ('. $aseco->db->errmsg() .')'. CRLF .'sql = '. $query, E_USER_WARNING);
		}
	}
}

?>
