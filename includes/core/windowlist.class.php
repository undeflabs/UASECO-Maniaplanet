<?php
/*
 * Class: WindowList
 * ~~~~~~~~~~~~~~~~~
 * » Handles actions for Manialink windows created with the Class Window.
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-07-03
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
 *  - includes/core/window.class.php
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class WindowList {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($aseco) {

		// Register callbacks for this new Window
		$aseco->registerEvent('onPlayerManialinkPageAnswer', array($this, 'onPlayerManialinkPageAnswer'));
		$aseco->registerEvent('onPlayerDisconnectPrepare', array($this, 'onPlayerDisconnectPrepare'));
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
		if ( $this->existsPlayerData($player, 'ClassWindow') ) {
			$window = $this->getPlayerData($player, 'ClassWindow');
		}


		$send = false;
		if ($answer['Action'] == 'ClassWindowPagePrev') {

			$window->content['page'] -= 1;
			if ($window->content['page'] < 0) {
				$window->content['page'] = 0;
			}
			$send = true;

		}
		else if ($answer['Action'] == 'ClassWindowPagePrevTwo') {

			$window->content['page'] -= 2;
			if ($window->content['page'] < 0) {
				$window->content['page'] = 0;
			}
			$send = true;

		}
		else if ($answer['Action'] == 'ClassWindowPageFirst') {

			$window->content['page'] = 0;
			$send = true;

		}
		else if ($answer['Action'] == 'ClassWindowPageNext') {

			$window->content['page'] += 1;
			if ($window->content['page'] > $window->content['maxpage']) {
				$window->content['page'] = $window->content['maxpage'];
			}
			$send = true;

		}
		else if ($answer['Action'] == 'ClassWindowPageNextTwo') {

			$window->content['page'] += 2;
			if ($window->content['page'] > $window->content['maxpage']) {
				$window->content['page'] = $window->content['maxpage'];
			}
			$send = true;

		}
		else if ($answer['Action'] == 'ClassWindowPageLast') {

			$window->content['page'] = $window->content['maxpage'];
			$send = true;

		}

		else if ($answer['Action'] == 'ClassWindowRefreshPage') {

			// Just send the current Page again
			$send = true;

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

		if (get_class($player) == 'Player' && $player->id > 0) {

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
			if ( is_array($window->content['data']) ) {
				$xml = str_replace(
					array(
						'%content%',
						'%page%',
						'%buttons%',
						'%maniascript%',
					),
					array(
						$window->buildColumns(),
						$window->buildPageinfo(),
						$window->buildButtons(),
						$window->buildManiascript(),
					),
					$window->buildWindow()
				);
			}
			else if ( is_string($window->content['data']) ) {
				$xml = str_replace(
					array(
						'%content%',
						'%page%',
						'%buttons%',
						'%maniascript%',
					),
					array(
						$window->content['data'],
						$window->buildPageinfo(),
						$window->buildButtons(),
						$window->buildManiascript(),
					),
					$window->buildWindow()
				);
			}

			// Send to Player
			$aseco->sendManialink($xml, $player->login, $timeout, $hideclick);
		}
		else {
			$aseco->console('[ClassWindowList] Given Player does not exists in the current PlayerList.');
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
		if ( (isset($key)) && (isset($player->data['ClassWindow'][$key])) ) {
			unset($player->data['ClassWindow'][$key]);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function existsPlayerData ($player, $key) {
		if ( (isset($key)) && (isset($player->data['ClassWindow'][$key])) ) {
			return true;
		}
		return false;
	}
}

?>
