<?php
/*
 * Class: WindowList
 * ~~~~~~~~~~~~~~~~~
 * » Handles actions for Manialink windows created with the Class Window.
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
 * Dependencies:
 *  - includes/core/window.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WindowList extends BaseClass {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($aseco) {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-05-31');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription(new Message('class.window', 'windowlist_description'));

		// Register callbacks for this new Window
		$aseco->registerEvent('onPlayerManialinkPageAnswer', array($this, 'onPlayerManialinkPageAnswer'));
		$aseco->registerEvent('onPlayerDisconnectPrepare', array($this, 'onPlayerDisconnectPrepare'));

		$aseco->registerChatCommand('lastwin', array($this, 'chat_lastwin'), new Message('class.window', 'slash_lastwin'), Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_lastwin ($aseco, $login, $chat_command, $chat_parameter) {

		$answer['Action'] = 'ClassWindowRefreshPage';
		$this->onPlayerManialinkPageAnswer($aseco, $login, $answer);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnectPrepare ($aseco, $player) {

		// Remove temporary Player data, do not need to be stored into the database.
		$this->removePlayerData($player, 'ClassWindow');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		// Get Player object
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Restore stored data from Player object
		if ($this->existsPlayerData($player, 'ClassWindow')) {
			$window = $this->getPlayerData($player, 'ClassWindow');
		}


		$send = false;
		if ($answer['Action'] === 'StripStyles') {

			$window->settings['stripcodes'] = true;
			$send = true;

		}
		else if ($answer['Action'] === 'DefaultStyles') {

			$window->settings['stripcodes'] = false;
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPagePrev') {

			$window->content['page'] -= 1;
			if ($window->content['page'] < 0) {
				$window->content['page'] = 0;
			}
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPagePrevTwo') {

			$window->content['page'] -= 2;
			if ($window->content['page'] < 0) {
				$window->content['page'] = 0;
			}
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPageFirst') {

			$window->content['page'] = 0;
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPageNext') {

			$window->content['page'] += 1;
			if ($window->content['page'] > $window->content['maxpage']) {
				$window->content['page'] = $window->content['maxpage'];
			}
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPageNextTwo') {

			$window->content['page'] += 2;
			if ($window->content['page'] > $window->content['maxpage']) {
				$window->content['page'] = $window->content['maxpage'];
			}
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowPageLast') {

			$window->content['page'] = $window->content['maxpage'];
			$send = true;

		}
		else if ($answer['Action'] === 'ClassWindowRefreshPage') {

			// Just send the current Page again
			$send = true;

		}

		if (isset($answer['X']) && !empty($answer['X'])) {
			$window->layout['position']['x'] = $answer['X'];
		}
		if (isset($answer['Y']) && !empty($answer['Y'])) {
			$window->layout['position']['y'] = $answer['Y'];
		}


		if ($send == true) {
			$this->send($window, $player, $window->settings['hideclick'], $window->settings['hideclick']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function send ($window, $player, $timeout = 0, $hideclick = false) {
		global $aseco;

		if (isset($player) && is_object($player) && $player instanceof Player && $player->id > 0) {

			// Make sure there are haligns for inner columns
			if (count($window->settings['halign']) == 0) {
				$widths = (100 / $window->settings['columns']);
				for ($i = $widths; $i <= 100; $i += $widths) {
					$window->settings['halign'][] = 'left';
				}
			}

			// Make sure there are widths for inner columns
			if (count($window->settings['widths']) == 0) {
				$widths = (100 / $window->settings['columns']);
				for ($i = $widths; $i <= 100; $i += $widths) {
					$window->settings['widths'][] = $widths;
				}
			}

			// Make sure there are textcolors for inner columns
			if (count($window->settings['textcolors']) == 0) {
				$widths = (100 / $window->settings['columns']);
				for ($i = $widths; $i <= 100; $i += $widths) {
					$window->settings['textcolors'][] = 'FFFF';
				}
			}

			$window->settings['timeout'] = $timeout;
			$window->settings['hideclick'] = $hideclick;
			$window->settings['login'] = $player->login;

			// Store Window into Player object
			$this->storePlayerData($player, 'ClassWindow', $window);

			// Concat all the elements
			if ($window->settings['mode'] === 'columns') {
				$xml = str_replace(
					array(
						'%content%',
						'%buttons%',
						'%maniascript%',
					),
					array(
						$window->buildColumns($player->login),
						$window->buildButtons(),
						$window->buildManiascript(),
					),
					$window->buildWindow($player->login)
				);
			}
			else if ($window->settings['mode'] === 'pages') {
				$xml = str_replace(
					array(
						'%content%',
						'%buttons%',
						'%maniascript%',
					),
					array(
						$window->buildPages(),
						$window->buildButtons(),
						$window->buildManiascript(),
					),
					$window->buildWindow($player->login)
				);
			}

			// Send to Player
			$aseco->sendManialink($xml, $player->login, $timeout, $hideclick);
		}
		else {
			if ($aseco->debug) {
				if ($player->id == 0) {
					$aseco->console('[ClassWindowList] Ignoring the given Fakeplayer.');
				}
				else {
					$aseco->console('[ClassWindowList] Given Player does not exists in the current PlayerList.');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function storePlayerData ($player, $key, $data) {
		if (!isset($player)) {
			return;
		}
		$player->data['ClassWindow'][$key] = $data;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function getPlayerData ($player, $key) {
		if (!isset($player)) {
			return;
		}
		return $player->data['ClassWindow'][$key];
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function removePlayerData ($player, $key) {
		if (!isset($player)) {
			return;
		}
		if (isset($key) && isset($player->data['ClassWindow'][$key])) {
			unset($player->data['ClassWindow'][$key]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function existsPlayerData ($player, $key) {
		if (isset($key) && isset($player->data['ClassWindow'][$key])) {
			return true;
		}
		return false;
	}
}

?>
