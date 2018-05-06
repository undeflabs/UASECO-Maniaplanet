<?php
/*
 * Plugin: Chat Server
 * ~~~~~~~~~~~~~~~~~~~
 * » Displays server and UASECO info
 * » Based upon chat.server.php from XAseco2/1.03 written by Xymph
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
	$_PLUGIN = new PluginChatServer();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginChatServer extends Plugin {

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2018-05-06');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription(new Message('chat.server', 'plugin_description'));

		$this->registerChatCommand('uaseco',		'chat_uaseco',		new Message('chat.server', 'slash_uaseco_description'),		Player::PLAYERS);
		$this->registerChatCommand('contact',		'chat_contact',		new Message('chat.server', 'slash_contact_description'),	Player::PLAYERS);
		$this->registerChatCommand('masteradmins',	'chat_masteradmins',	new Message('chat.server', 'slash_masteradmins_description'),	Player::PLAYERS);
		$this->registerChatCommand('admins',		'chat_admins',		new Message('chat.server', 'slash_admins_description'),		Player::PLAYERS);
		$this->registerChatCommand('operators',		'chat_operators',	new Message('chat.server', 'slash_operators_description'),	Player::PLAYERS);
		$this->registerChatCommand('plugins',		'chat_plugins',		new Message('chat.server', 'slash_plugins_description'),	Player::PLAYERS);
		$this->registerChatCommand('time',		'chat_time',		new Message('chat.server', 'slash_time_description'),		Player::PLAYERS);
		$this->registerChatCommand('uptime',		'chat_uptime',		new Message('chat.server', 'slash_uptime_description'),		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_uaseco ($aseco, $login, $chat_command, $chat_parameter) {

		// Show chat message
		$msg = new Message('chat.server', 'slash_uaseco_chat_message');
		$msg->addPlaceholders(
			UASECO_WEBSITE,
			UASECO_NAME,
			UASECO_VERSION,
			UASECO_BUILD
		);
		$msg->sendChatMessage($login);

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_contact ($aseco, $login, $chat_command, $chat_parameter) {

		// Show chat message
		if (strtolower($aseco->settings['admin_contact']) != 'your@email.com') {
			$msg = new Message('chat.server', 'slash_contact_chat_message');
			$msg->addPlaceholders(
				$aseco->settings['admin_contact']
			);
		}
		else {
			$msg = new Message('chat.server', 'slash_contact_chat_message_no_contact');
		}
		$msg->sendChatMessage($login);

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_masteradmins ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Create list of all MasterAdmins
		$data = array();
		if (count($aseco->masteradmin_list) > 0 && $aseco->masteradmin_list['TMLOGIN'] !== null) {
			foreach ($aseco->masteradmin_list['TMLOGIN'] as $lgn) {
				// Skip any LAN logins
				if (!empty($lgn) && !$aseco->isLANLogin($lgn)) {
					$data[] = array($aseco->server->players->getPlayerNickname($lgn) .'$Z');
				}
			}
		}
		else {
			$data[] = array('NO MASTERADMINS CONFIGURED');
		}

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons128x128_1,Hotseat',
		);
		$settings_columns = array(
			'columns'		=> 4,
			'widths'		=> array(100),
			'textcolors'		=> array('FFFF'),
		);
		$settings_content = array(
			'title'			=> (new Message('chat.server', 'slash_masteradmins_window_title'))->finish($login),
			'data'			=> $data,
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_admins ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Create list of all Admins
		$data = array();
		if (count($aseco->admin_list) > 0 && $aseco->admin_list['TMLOGIN'] !== null) {
			foreach ($aseco->admin_list['TMLOGIN'] as $lgn) {
				// Skip any LAN logins
				if (!empty($lgn) && !$aseco->isLANLogin($lgn)) {
					$data[] = array($aseco->server->players->getPlayerNickname($lgn) .'$Z');
				}
			}
		}
		else {
			$data[] = array('NO ADMINS CONFIGURED');
		}

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons128x128_1,Hotseat',
		);
		$settings_columns = array(
			'columns'		=> 4,
			'widths'		=> array(100),
			'textcolors'		=> array('FFFF'),
		);
		$settings_content = array(
			'title'			=> (new Message('chat.server', 'slash_admins_window_title'))->finish($login),
			'data'			=> $data,
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_operators ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Create list of all Operator
		$data = array();
		if (count($aseco->operator_list) > 0 && $aseco->operator_list['TMLOGIN'] !== null) {
			foreach ($aseco->operator_list['TMLOGIN'] as $lgn) {
				// Skip any LAN logins
				if (!empty($lgn) && !$aseco->isLANLogin($lgn)) {
					$data[] = array($aseco->server->players->getPlayerNickname($lgn) .'$Z');
				}
			}
		}
		else {
			$data[] = array('NO OPERATORS CONFIGURED');
		}

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons128x128_1,Hotseat',
		);
		$settings_columns = array(
			'columns'		=> 4,
			'widths'		=> array(100),
			'textcolors'		=> array('FFFF'),
		);
		$settings_content = array(
			'title'			=> (new Message('chat.server', 'slash_operators_window_title'))->finish($login),
			'data'			=> $data,
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_plugins ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Create list of plugins
		$plugins = $aseco->plugins;
		ksort($plugins);

		$data = array();
		foreach ($plugins as $plugin) {
			$description = $plugin->getDescription();

			$data[] = array(
				'$N'. $plugin->getFilename(),
				'$N'. ($description instanceof Message ? $description->finish($player->login) : $description),
				'$N'. $plugin->getVersion() .' ('. $plugin->getBuild() .')',
			);
		}
		unset($plugins);

		// Setup settings for Window
		$settings_styles = array(
			'icon'			=> 'Icons128x128_1,Browse',
			'textcolors'		=> array('FF5F', 'FFFF'),
		);
		$settings_columns = array(
			'columns'		=> 1,
			'widths'		=> array(17.5, 72.5, 10),
			'textcolors'		=> array('FF5F', 'FFFF', 'FFFF', 'FFFF'),
			'heading'		=> array(
				(new Message('chat.server', 'slash_plugins_heading_filename'))->finish($login),
				(new Message('chat.server', 'slash_plugins_heading_description'))->finish($login),
				(new Message('chat.server', 'slash_plugins_heading_version_build'))->finish($login),
			),
		);
		$settings_content = array(
			'title'			=> (new Message('chat.server', 'slash_plugins_window_title'))->finish($login),
			'data'			=> $data,
			'mode'			=> 'columns',
		);

		$window = new Window();
		$window->setStyles($settings_styles);
		$window->setColumns($settings_columns);
		$window->setContent($settings_content);
		$window->send($player, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_time ($aseco, $login, $chat_command, $chat_parameter) {

		// Show chat message
		$msg = new Message('chat.server', 'slash_time_chat_message');
		$msg->addPlaceholders(
			date('H:i:s T'),
			date('Y-m-d')
		);
		$msg->sendChatMessage($login);
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_uptime ($aseco, $login, $chat_command, $chat_parameter) {

		// Show chat message
		$msg = new Message('chat.server', 'slash_uptime_chat_message');
		$msg->addPlaceholders(
			$aseco->timeString($aseco->server->networkstats['Uptime'])
		);
		$msg->sendChatMessage($login);
	}
}

?>
