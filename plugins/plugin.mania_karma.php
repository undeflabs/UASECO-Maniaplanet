<?php
/*
 * Plugin: Mania-Karma
 * ~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Mania-Karma.php
 *
 * ----------------------------------------------------------------------------------
 * Author:		undef.de
 * Version:		2.0.0
 * Date:		2014-11-01
 * Copyright:		2009 - 2014 by undef.de
 * System:		UASECO/1.0.0+
 * Game:		ManiaPlanet Trackmania2 (TM2)
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
 *  - includes/core/webaccess.inc.php
 *
 */

/* The following manialink id's are used in this plugin (the 911 part of id can be changed on trouble in onSync()):
 *
 * ManialinkID's
 * ~~~~~~~~~~~~~
 * 91101		id for manialink ReminderWindow and ManiaExchange-Link
 * 91102		id for manialink Windows
 * 91103		id for manialink Skeleton Widget
 * 91104		id for manialink Player-Marker for his/her Vote
 * 91105		id for manialink Cups, Karma-Value and Karma-Votes
 * 91106		id for manialink ConnectionStatus
 * 91107		id for manialink LoadingIndicator
 *
 * ActionID's
 * ~~~~~~~~~~
 * 91101		id for action open HelpWindow
 * 91102		id for action open KarmaDetailsWindow
 * 91103		id for action open WhoKarmaWindow
 * 91110		id for action vote + (1)
 * 91111		id for action vote ++ (2)
 * 91112		id for action vote +++ (3)
 * 91113		id for action vote undecided (0)
 * 91114		id for action vote - (-1)
 * 91115		id for action vote -- (-2)
 * 91116		id for action vote --- (-3)
 * 91117		id for action on disabled (red) buttons, tell the Player to finish this Map x times
 * 91118		id for action that is ignored
 */

	// Start the plugin
	$_PLUGIN = new PluginManiaKarma();

/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class PluginManiaKarma extends Plugin {
	public $config	= array();
	public $karma	= array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setVersion('2.0.0');
		$this->setAuthor('undef.de');
		$this->setDescription('Global Karma Database for Map votings.');

		$this->addDependence('PluginRaspKarma', Dependence::DISALLOWED, '1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerChat',			'onPlayerChat');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnectPrepare',	'onPlayerDisconnectPrepare');
		$this->registerEvent('onPlayerDisconnect',		'onPlayerDisconnect');
		$this->registerEvent('onPlayerFinish',			'onPlayerFinish');
		$this->registerEvent('onBeginMap',			'onBeginMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMap1',			'onEndMap1');
		$this->registerEvent('onUnloadingMap',			'onUnloadingMap');
		$this->registerEvent('onPlayerManialinkPageAnswer',	'onPlayerManialinkPageAnswer');
		$this->registerEvent('onKarmaChange',			'onKarmaChange');
		$this->registerEvent('onShutdown',			'onShutdown');

		$this->registerChatCommand('karma',			'chat_karma',	'Shows karma for the current Map (see: /karma help)',	Player::PLAYERS);
		$this->registerChatCommand('+++',			'chat_votes',	'Set "Fantastic" karma for the current Map',		Player::PLAYERS);
		$this->registerChatCommand('++',			'chat_votes',	'Set "Beautiful" karma for the current Map',		Player::PLAYERS);
		$this->registerChatCommand('+',				'chat_votes',	'Set "Good" karma for the current Map',			Player::PLAYERS);
		$this->registerChatCommand('-',				'chat_votes',	'Set "Bad" karma for the current Map',			Player::PLAYERS);
		$this->registerChatCommand('--',			'chat_votes',	'Set "Poor" karma for the current Map',			Player::PLAYERS);
		$this->registerChatCommand('---',			'chat_votes',	'Set "Waste" karma for the current Map',		Player::PLAYERS);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onSync ($aseco) {

		// Check for the right UASECO-Version
		$uaseco_min_version = '1.0.0';
		if ( defined('UASECO_VERSION') ) {
			if ( version_compare(UASECO_VERSION, $uaseco_min_version, '<') ) {
				trigger_error('[ManiaKarma] Not supported USAECO version ('. UASECO_VERSION .')! Please update to min. version '. $uaseco_min_version .'!', E_USER_ERROR);
			}
		}
		else {
			trigger_error('[ManiaKarma] Can not identify the System, "UASECO_VERSION" is unset! This plugin runs only with UASECO/'. $uaseco_min_version .'+', E_USER_ERROR);
		}


		// Set internal Manialink ID
		$this->config['manialink_id'] = '911';

		// http://en.wikipedia.org/wiki/ISO_3166-1			(Last-Modified: 11:29, 10 January 2010 Chanheigeorge)
		// http://en.wikipedia.org/wiki/List_of_countries_by_continent	(Last-Modified: 09:25, 21 January 2010 Anna Lincoln)
		//	ISO		Countries					Continent
		$iso3166Alpha3 = array(
			'ABW' => array("Aruba",						'NORTHAMERICA'),
			'AFG' => array("Afghanistan",					'ASIA'),
			'AGO' => array("Angola",					'AFRICA'),
			'AIA' => array("Anguilla",					'NORTHAMERICA'),
			'ALA' => array("Åland Islands",					'EUROPE'),
			'ALB' => array("Albania",					'EUROPE'),
			'AND' => array("Andorra",					'EUROPE'),
			'ANT' => array("Netherlands Antilles",				'NORTHAMERICA'),
			'ARE' => array("United Arab Emirates",				'ASIA'),
			'ARG' => array("Argentina",					'SOUTHAMERICA'),
			'ARM' => array("Armenia",					'ASIA'),
			'ASM' => array("American Samoa",				'OCEANIA'),
			'ATA' => array("Antarctica",					'WORLDWIDE'),
			'ATF' => array("French Southern Territories",			'WORLDWIDE'),
			'ATG' => array("Antigua and Barbuda",				'NORTHAMERICA'),
			'AUS' => array("Australia",					'OCEANIA'),
			'AUT' => array("Austria",					'EUROPE'),
			'AZE' => array("Azerbaijan",					'ASIA'),
			'BDI' => array("Burundi",					'AFRICA'),
			'BEL' => array("Belgium",					'EUROPE'),
			'BEN' => array("Benin",						'AFRICA'),
			'BFA' => array("Burkina Faso",					'AFRICA'),
			'BGD' => array("Bangladesh",					'ASIA'),
			'BGR' => array("Bulgaria",					'EUROPE'),
			'BHR' => array("Bahrain",					'ASIA'),
			'BHS' => array("Bahamas",					'NORTHAMERICA'),
			'BIH' => array("Bosnia and Herzegovina",			'EUROPE'),
			'BLM' => array("Saint Barthélemy",				'NORTHAMERICA'),
			'BLR' => array("Belarus",					'EUROPE'),
			'BLZ' => array("Belize",					'NORTHAMERICA'),
			'BMU' => array("Bermuda",					'NORTHAMERICA'),
			'BOL' => array("Bolivia",					'SOUTHAMERICA'),
			'BRA' => array("Brazil",					'SOUTHAMERICA'),
			'BRB' => array("Barbados",					'NORTHAMERICA'),
			'BRN' => array("Brunei Darussalam",				'ASIA'),
			'BTN' => array("Bhutan",					'ASIA'),
			'BVT' => array("Bouvet Island",					'WORLDWIDE'),
			'BWA' => array("Botswana",					'AFRICA'),
			'CAF' => array("Central African Republic",			'AFRICA'),
			'CAN' => array("Canada",					'NORTHAMERICA'),
			'CCK' => array("Cocos (Keeling) Islands",			'ASIA'),
			'CHE' => array("Switzerland",					'EUROPE'),
			'CHL' => array("Chile",						'SOUTHAMERICA'),
			'CHN' => array("China",						'ASIA'),
			'CIV' => array("Côte d'Ivoire",					'AFRICA'),
			'CMR' => array("Cameroon",					'AFRICA'),
			'COD' => array("Democratic Republic of Congo",			'AFRICA'),
			'COG' => array("Republic of Congo",				'AFRICA'),
			'COK' => array("Cook Islands",					'OCEANIA'),
			'COL' => array("Colombia",					'SOUTHAMERICA'),
			'COM' => array("Comoros",					'AFRICA'),
			'CPV' => array("Cape Verde",					'AFRICA'),
			'CRI' => array("Costa Rica",					'NORTHAMERICA'),
			'CUB' => array("Cuba",						'NORTHAMERICA'),
			'CXR' => array("Christmas Island",				'ASIA'),
			'CYM' => array("Cayman Islands",				'NORTHAMERICA'),
			'CYP' => array("Cyprus",					'ASIA'),
			'CZE' => array("Czech Republic",				'EUROPE'),
			'DEU' => array("Germany",					'EUROPE'),
			'DJI' => array("Djibouti",					'AFRICA'),
			'DMA' => array("Dominica",					'NORTHAMERICA'),
			'DNK' => array("Denmark",					'EUROPE'),
			'DOM' => array("Dominican Republic",				'NORTHAMERICA'),
			'DZA' => array("Algeria",					'AFRICA'),
			'ECU' => array("Ecuador",					'SOUTHAMERICA'),
			'EGY' => array("Egypt",						'AFRICA'),
			'ERI' => array("Eritrea",					'AFRICA'),
			'ESH' => array("Western Sahara",				'AFRICA'),
			'ESP' => array("Spain",						'EUROPE'),
			'EST' => array("Estonia",					'EUROPE'),
			'ETH' => array("Ethiopia",					'AFRICA'),
			'FIN' => array("Finland",					'EUROPE'),
			'FJI' => array("Fiji",						'OCEANIA'),
			'FLK' => array("Falkland Islands",				'SOUTHAMERICA'),
			'FRA' => array("France",					'EUROPE'),
			'FRO' => array("Faroe Islands",					'EUROPE'),
			'FSM' => array("Micronesia",					'OCEANIA'),
			'GAB' => array("Gabon",						'AFRICA'),
			'GBR' => array("United Kingdom",				'EUROPE'),
			'GEO' => array("Georgia",					'ASIA'),
			'GGY' => array("Guernsey",					'EUROPE'),
			'GHA' => array("Ghana",						'AFRICA'),
			'GIB' => array("Gibraltar",					'EUROPE'),
			'GIN' => array("Guinea",					'AFRICA'),
			'GLP' => array("Guadeloupe",					'NORTHAMERICA'),
			'GMB' => array("Gambia",					'AFRICA'),
			'GNB' => array("Guinea-Bissau",					'AFRICA'),
			'GNQ' => array("Equatorial Guinea",				'AFRICA'),
			'GRC' => array("Greece",					'EUROPE'),
			'GRD' => array("Grenada",					'NORTHAMERICA'),
			'GRL' => array("Greenland",					'NORTHAMERICA'),
			'GTM' => array("Guatemala",					'NORTHAMERICA'),
			'GUF' => array("French Guiana",					'SOUTHAMERICA'),
			'GUM' => array("Guam",						'OCEANIA'),
			'GUY' => array("Guyana",					'SOUTHAMERICA'),
			'HKG' => array("Hong Kong",					'ASIA'),
			'HMD' => array("Heard Island and McDonald Islands",		'WORLDWIDE'),
			'HND' => array("Honduras",					'NORTHAMERICA'),
			'HRV' => array("Croatia",					'EUROPE'),
			'HTI' => array("Haiti",						'NORTHAMERICA'),
			'HUN' => array("Hungary",					'EUROPE'),
			'IDN' => array("Indonesia",					'ASIA'),
			'IMN' => array("Isle of Man",					'EUROPE'),
			'IND' => array("India",						'ASIA'),
			'IOT' => array("British Indian Ocean Territory",		'ASIA'),
			'IRL' => array("Ireland",					'EUROPE'),
			'IRN' => array("Iran",						'ASIA'),
			'IRQ' => array("Iraq",						'ASIA'),
			'ISL' => array("Iceland",					'EUROPE'),
			'ISR' => array("Israel",					'ASIA'),
			'ITA' => array("Italy",						'EUROPE'),
			'JAM' => array("Jamaica",					'NORTHAMERICA'),
			'JEY' => array("Jersey",					'EUROPE'),
			'JOR' => array("Jordan",					'ASIA'),
			'JPN' => array("Japan",						'ASIA'),
			'KAZ' => array("Kazakhstan",					'ASIA'),
			'KEN' => array("Kenya",						'AFRICA'),
			'KGZ' => array("Kyrgyzstan",					'ASIA'),
			'KHM' => array("Cambodia",					'ASIA'),
			'KIR' => array("Kiribati",					'OCEANIA'),
			'KNA' => array("Saint Kitts and Nevis",				'NORTHAMERICA'),
			'KOR' => array("South Korea",					'ASIA'),
			'KWT' => array("Kuwait",					'ASIA'),
			'LAO' => array("Lao People's Democratic Republic",		'ASIA'),
			'LBN' => array("Lebanon",					'ASIA'),
			'LBR' => array("Liberia",					'AFRICA'),
			'LBY' => array("Libyan Arab Jamahiriya",			'AFRICA'),
			'LCA' => array("Saint Lucia",					'NORTHAMERICA'),
			'LIE' => array("Liechtenstein",					'EUROPE'),
			'LKA' => array("Sri Lanka",					'ASIA'),
			'LSO' => array("Lesotho",					'AFRICA'),
			'LTU' => array("Lithuania",					'EUROPE'),
			'LUX' => array("Luxembourg",					'EUROPE'),
			'LVA' => array("Latvia",					'EUROPE'),
			'MAC' => array("Macao",						'ASIA'),
			'MAF' => array("Saint Martin",					'NORTHAMERICA'),
			'MAR' => array("Morocco",					'AFRICA'),
			'MCO' => array("Monaco",					'EUROPE'),
			'MDA' => array("Moldova",					'EUROPE'),
			'MDG' => array("Madagascar",					'AFRICA'),
			'MDV' => array("Maldives",					'ASIA'),
			'MEX' => array("Mexico",					'NORTHAMERICA'),
			'MHL' => array("Marshall Islands",				'OCEANIA'),
			'MKD' => array("Macedonia",					'EUROPE'),
			'MLI' => array("Mali",						'AFRICA'),
			'MLT' => array("Malta",						'EUROPE'),
			'MMR' => array("Myanmar",					'ASIA'),
			'MNE' => array("Montenegro",					'EUROPE'),
			'MNG' => array("Mongolia",					'ASIA'),
			'MNP' => array("Northern Mariana Islands",			'OCEANIA'),
			'MOZ' => array("Mozambique",					'AFRICA'),
			'MRT' => array("Mauritania",					'AFRICA'),
			'MSR' => array("Montserrat",					'NORTHAMERICA'),
			'MTQ' => array("Martinique",					'NORTHAMERICA'),
			'MUS' => array("Mauritius",					'AFRICA'),
			'MWI' => array("Malawi",					'AFRICA'),
			'MYS' => array("Malaysia",					'ASIA'),
			'MYT' => array("Mayotte",					'AFRICA'),
			'NAM' => array("Namibia",					'AFRICA'),
			'NCL' => array("New Caledonia",					'OCEANIA'),
			'NER' => array("Niger",						'AFRICA'),
			'NFK' => array("Norfolk Island",				'OCEANIA'),
			'NGA' => array("Nigeria",					'AFRICA'),
			'NIC' => array("Nicaragua",					'NORTHAMERICA'),
			'NIU' => array("Niue",						'OCEANIA'),
			'NLD' => array("Netherlands",					'EUROPE'),
			'NOR' => array("Norway",					'EUROPE'),
			'NPL' => array("Nepal",						'ASIA'),
			'NRU' => array("Nauru",						'OCEANIA'),
			'NZL' => array("New Zealand",					'OCEANIA'),
			'OMN' => array("Oman",						'ASIA'),
			'PAK' => array("Pakistan",					'ASIA'),
			'PAN' => array("Panama",					'NORTHAMERICA'),
			'PCN' => array("Pitcairn Islands",				'OCEANIA'),
			'PER' => array("Peru",						'SOUTHAMERICA'),
			'PHL' => array("Philippines",					'ASIA'),
			'PLW' => array("Palau",						'OCEANIA'),
			'PNG' => array("Papua New Guinea",				'OCEANIA'),
			'POL' => array("Poland",					'EUROPE'),
			'PRI' => array("Puerto Rico",					'NORTHAMERICA'),
			'PRK' => array("North Korea",					'ASIA'),
			'PRT' => array("Portugal",					'EUROPE'),
			'PRY' => array("Paraguay",					'SOUTHAMERICA'),
			'PSE' => array("Palestinian Territory, Occupied",		'ASIA'),
			'PYF' => array("French Polynesia",				'OCEANIA'),
			'QAT' => array("Qatar",						'ASIA'),
			'REU' => array("Réunion",					'AFRICA'),
			'ROU' => array("Romania",					'EUROPE'),
			'RUS' => array("Russian Federation",				'RUSSIA'),
			'RWA' => array("Rwanda",					'AFRICA'),
			'SAU' => array("Saudi Arabia",					'ASIA'),
			'SDN' => array("Sudan",						'AFRICA'),
			'SEN' => array("Senegal",					'AFRICA'),
			'SGP' => array("Singapore",					'ASIA'),
			'SGS' => array("South Georgia and the South Sandwich Islands",	'WORLDWIDE'),
			'SHN' => array("Saint Helena",					'AFRICA'),
			'SJM' => array("Svalbard and Jan Mayen",			'EUROPE'),
			'SLB' => array("Solomon Islands",				'OCEANIA'),
			'SLE' => array("Sierra Leone",					'AFRICA'),
			'SLV' => array("El Salvador",					'NORTHAMERICA'),
			'SMR' => array("San Marino",					'EUROPE'),
			'SOM' => array("Somalia",					'AFRICA'),
			'SPM' => array("Saint Pierre and Miquelon",			'NORTHAMERICA'),
			'SRB' => array("Serbia",					'EUROPE'),
			'STP' => array("Sao Tome and Principe",				'AFRICA'),
			'SUR' => array("Suriname",					'SOUTHAMERICA'),
			'SVK' => array("Slovakia",					'EUROPE'),
			'SVN' => array("Slovenia",					'EUROPE'),
			'SWE' => array("Sweden",					'EUROPE'),
			'SWZ' => array("Swaziland",					'AFRICA'),
			'SYC' => array("Seychelles",					'AFRICA'),
			'SYR' => array("Syrian Arab Republic",				'ASIA'),
			'TCA' => array("Turks and Caicos Islands",			'NORTHAMERICA'),
			'TCD' => array("Chad",						'AFRICA'),
			'TGO' => array("Togo",						'AFRICA'),
			'THA' => array("Thailand",					'ASIA'),
			'TJK' => array("Tajikistan",					'ASIA'),
			'TKL' => array("Tokelau",					'OCEANIA'),
			'TKM' => array("Turkmenistan",					'ASIA'),
			'TLS' => array("Timor-Leste",					'ASIA'),
			'TON' => array("Tonga",						'OCEANIA'),
			'TTO' => array("Trinidad and Tobago",				'NORTHAMERICA'),
			'TUN' => array("Tunisia",					'AFRICA'),
			'TUR' => array("Turkey",					'ASIA'),
			'TUV' => array("Tuvalu",					'OCEANIA'),
			'TWN' => array("Taiwan",					'ASIA'),
			'TZA' => array("Tanzania",					'AFRICA'),
			'UGA' => array("Uganda",					'AFRICA'),
			'UKR' => array("Ukraine",					'EUROPE'),
			'UMI' => array("United States Minor Outlying Islands",		'WORLDWIDE'),
			'URY' => array("Uruguay",					'SOUTHAMERICA'),
			'USA' => array("United States of America",			'NORTHAMERICA'),
			'UZB' => array("Uzbekistan",					'ASIA'),
			'VAT' => array("Holy See (Vatican City State)",			'EUROPE'),
			'VCT' => array("Saint Vincent and the Grenadines",		'NORTHAMERICA'),
			'VEN' => array("Venezuela, Bolivarian Republic of",		'SOUTHAMERICA'),
			'VGB' => array("Virgin Islands, British",			'NORTHAMERICA'),
			'VIR' => array("Virgin Islands, U.S.",				'NORTHAMERICA'),
			'VNM' => array("Viet Nam",					'ASIA'),
			'VUT' => array("Vanuatu",					'OCEANIA'),
			'WLF' => array("Wallis and Futuna",				'OCEANIA'),
			'WSM' => array("Samoa",						'OCEANIA'),
			'YEM' => array("Yemen",						'ASIA'),
			'ZAF' => array("South Africa",					'AFRICA'),
			'ZMB' => array("Zambia",					'AFRICA'),
			'ZWE' => array("Zimbabwe",					'AFRICA')
		);

		$aseco->console('[ManiaKarma] ********************************************************');
		$aseco->console('[ManiaKarma] Starting version '. $this->getVersion() .' - Maniaplanet');

		// Load the mania_karma.xml
		libxml_use_internal_errors(true);
		if (!$xmlcfg = @simplexml_load_file('config/mania_karma.xml', null, LIBXML_COMPACT) ) {
			$aseco->console('[ManiaKarma] Could not read/parse config file "config/mania_karma.xml"!');
			foreach (libxml_get_errors() as $error) {
				$aseco->console("\t". $error->message);
			}
			libxml_clear_errors();
			trigger_error("[ManiaKarma] Please copy the 'mania_karma.xml' from this Package into the 'config' directory and do not forget to edit it!", E_USER_ERROR);
		}
		else {
			$aseco->console('[ManiaKarma] Parsed "config/mania_karma.xml" successfully, starting checks...');
		}
		libxml_use_internal_errors(false);

		// Remove all comments
		unset($xmlcfg->comment);


		if ((string)$xmlcfg->urls->api_auth == '') {
			trigger_error("[ManiaKarma] <urls><api_auth> is empty in config file 'mania_karma.xml'!", E_USER_ERROR);
		}

		if ((string)$xmlcfg->nation == '') {
			trigger_error("[ManiaKarma] <nation> is empty in config file 'mania_karma.xml'!", E_USER_ERROR);
		}
		else if ((string)$xmlcfg->nation == 'YOUR_SERVER_NATION') {
			trigger_error("[ManiaKarma] <nation> is not set in config file 'mania_karma.xml'! Please change 'YOUR_SERVER_NATION' with your server nation code.", E_USER_ERROR);
		}
		else if (! $iso3166Alpha3[strtoupper((string)$xmlcfg->nation)][1] ) {
			trigger_error("[ManiaKarma] <nation> is not valid in config file 'mania_karma.xml'! Please change <nation> to valid ISO-3166 ALPHA-3 nation code!", E_USER_ERROR);
		}


		// Set Url for API-Call Auth
		$this->config['urls']['api_auth'] = (string)$xmlcfg->urls->api_auth;

		// Check the given config timeouts and set defaults on too low or on empty timeouts
		if ( ((int)$xmlcfg->wait_timeout < 40) || ((int)$xmlcfg->wait_timeout == '') ) {
			$this->config['wait_timeout'] = 40;
		}
		else {
			$this->config['wait_timeout'] = (int)$xmlcfg->wait_timeout;
		}
		if ( ((int)$xmlcfg->connect_timeout < 30) || ((int)$xmlcfg->connect_timeout == '') ) {
			$this->config['connect_timeout'] = 30;
		}
		else {
			$this->config['connect_timeout'] = (int)$xmlcfg->connect_timeout;
		}
		if ( ((int)$xmlcfg->keepalive_min_timeout < 100) || ((int)$xmlcfg->keepalive_min_timeout == '') ) {
			$this->config['keepalive_min_timeout'] = 100;
		}
		else {
			$this->config['keepalive_min_timeout'] = (int)$xmlcfg->keepalive_min_timeout;
		}

		// Set connection status to 'all fine'
		$this->config['retrytime'] = 0;

		// 15 min. wait until try to reconnect
		$this->config['retrywait'] = (10 * 60);

		// Set login data
		$this->config['account']['login']	= strtolower((string)$aseco->server->login);
		$this->config['account']['nation']	= strtoupper((string)$xmlcfg->nation);

		// Create a User-Agent-Identifier for the authentication
		$this->config['user_agent'] = 'UASECO/'. UASECO_VERSION .' mania-karma/'. $this->getVersion() .' '. $aseco->server->game .'/'. $aseco->server->build .' php/'. phpversion() .' '. php_uname('s') .'/'. php_uname('r') .' '. php_uname('m');

		$aseco->console('[ManiaKarma] » Set Server location to "'. $iso3166Alpha3[$this->config['account']['nation']][0] .'"');
		$aseco->console('[ManiaKarma] » Trying to authenticate with central database "'. $this->config['urls']['api_auth'] .'"...');

		// Generate the url for the first Auth-Request
		$api_url = sprintf("%s?Action=Auth&login=%s&name=%s&game=%s&zone=%s&nation=%s",
			$this->config['urls']['api_auth'],
			urlencode( $this->config['account']['login'] ),
			base64_encode( $aseco->server->name ),
			urlencode( $aseco->server->game ),
			urlencode( implode('|', $aseco->server->zone) ),
			urlencode( $this->config['account']['nation'] )
		);


		// Start an async GET request
		$response = $aseco->webaccess->request($api_url, null, 'none', false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
		if ($response['Code'] == 200) {
			// Read the response
			if (!$xml = @simplexml_load_string($response['Message'], null, LIBXML_COMPACT) ) {
				$this->config['retrytime'] = (time() + $this->config['retrywait']);
				$this->config['account']['authcode'] = false;
				$this->config['urls']['api'] = false;

				// Fake import done to do not ask a MasterAdmin to export
				$this->config['import_done'] = true;

				$aseco->console('[ManiaKarma] » Could not read/parse response from mania-karma.com "'. $response['Message'] .'"!');
				$aseco->console('[ManiaKarma] » Connection failed with '. $response['Code'] .' ('. $response['Reason'] .') for url ['. $api_url .'], retry again later.');
				$aseco->console('[ManiaKarma] ********************************************************');
			}
			else {
				if ((int)$xml->status == 200) {
					$this->config['retrytime'] = 0;
					$this->config['account']['authcode'] = (string)$xml->authcode;
					$this->config['urls']['api'] = (string)$xml->api_url;

					$this->config['import_done'] = ((strtoupper((string)$xml->import_done) == 'TRUE') ? true : false);

					$aseco->console('[ManiaKarma] » Successfully started with async communication.');
					$aseco->console('[ManiaKarma] » The API set the Request-URL to "'. $this->config['urls']['api'] .'"');
					$aseco->console('[ManiaKarma] ********************************************************');
				}
				else {
					$this->config['retrytime'] = (time() + $this->config['retrywait']);
					$this->config['account']['authcode'] = false;
					$this->config['urls']['api'] = false;

					// Fake import done to do not ask a MasterAdmin to export
					$this->config['import_done'] = true;

					$aseco->console('[ManiaKarma] » Authentication failed with error code "'. $xml->status .'", votes are not possible!!!');
					$aseco->console('[ManiaKarma] ********************************************************');
				}
			}
		}
		else {
			$this->config['retrytime'] = (time() + $this->config['retrywait']);
			$this->config['account']['authcode'] = false;
			$this->config['urls']['api'] = false;

			// Fake import done to do not ask a MasterAdmin to export
			$this->config['import_done'] = true;

			$aseco->console('[ManiaKarma] » Connection failed with '. $response['Code'] .' ('. $response['Reason'] .') for url ['. $api_url .'], retry again later.');
			$aseco->console('[ManiaKarma] ********************************************************');
		}

		// Erase $iso3166Alpha3
		unset($iso3166Alpha3);


		// Are the position configured?
		if ( !isset($xmlcfg->reminder_window->race->pos_x) ) {
			$this->config['reminder_window']['race']['pos_x'] = -41;
		}
		else {
			$this->config['reminder_window']['race']['pos_x'] = (float)$xmlcfg->reminder_window->race->pos_x;
		}
		if ( !isset($xmlcfg->reminder_window->race->pos_y) ) {
			$this->config['reminder_window']['race']['pos_y'] = -29.35;
		}
		else {
			$this->config['reminder_window']['race']['pos_y'] = (float)$xmlcfg->reminder_window->race->pos_y;
		}
		if ( !isset($xmlcfg->reminder_window->score->pos_x) ) {
			$this->config['reminder_window']['score']['pos_x'] = -41;
		}
		else {
			$this->config['reminder_window']['score']['pos_x'] = (float)$xmlcfg->reminder_window->score->pos_x;
		}
		if ( !isset($xmlcfg->reminder_window->score->pos_y) ) {
			$this->config['reminder_window']['score']['pos_y'] = -29.35;
		}
		else {
			$this->config['reminder_window']['score']['pos_y'] = (float)$xmlcfg->reminder_window->score->pos_y;
		}


		$gamemodes = array(
			'score'		=> 0,
			'rounds'	=> Gameinfo::ROUNDS,
			'time_attack'	=> Gameinfo::TIMEATTACK,
			'team'		=> Gameinfo::TEAM,
			'laps'		=> Gameinfo::LAPS,
			'cup'		=> Gameinfo::CUP,
			'stunts'	=> Gameinfo::STUNTS,
			'team_attack'	=> Gameinfo::TEAMATTACK,
		);
		foreach ($gamemodes as $mode => $id) {
			if ( isset($xmlcfg->karma_widget->gamemode->$mode) ) {
				$this->config['widget']['states'][$id]['enabled']	= ((strtoupper((string)$xmlcfg->karma_widget->gamemode->$mode->enabled) == 'TRUE') ? true : false);
				$this->config['widget']['states'][$id]['pos_x']		= (float)($xmlcfg->karma_widget->gamemode->$mode->pos_x ? $xmlcfg->karma_widget->gamemode->$mode->pos_x : 0);
				$this->config['widget']['states'][$id]['pos_y']		= (float)($xmlcfg->karma_widget->gamemode->$mode->pos_y ? $xmlcfg->karma_widget->gamemode->$mode->pos_y : 0);
				$this->config['widget']['states'][$id]['scale']		= ($xmlcfg->karma_widget->gamemode->$mode->scale ? sprintf("%.1f", $xmlcfg->karma_widget->gamemode->$mode->scale) : 1.0);
			}
		}
		unset($gamemodes);


		// Set the current state for the KarmaWidget
		$this->config['widget']['current_state']			= $aseco->server->gameinfo->mode;

		// Set the config
		$this->config['urls']['website']				= (string)$xmlcfg->urls->website;
		$this->config['show_welcome']					= ((strtoupper((string)$xmlcfg->show_welcome) == 'TRUE')		? true : false);
		$this->config['allow_public_vote']				= ((strtoupper((string)$xmlcfg->allow_public_vote) == 'TRUE')		? true : false);
		$this->config['show_at_start']					= ((strtoupper((string)$xmlcfg->show_at_start) == 'TRUE')		? true : false);
		$this->config['show_details']					= ((strtoupper((string)$xmlcfg->show_details) == 'TRUE')		? true : false);
		$this->config['show_votes']					= ((strtoupper((string)$xmlcfg->show_votes) == 'TRUE')			? true : false);
		$this->config['show_karma']					= ((strtoupper((string)$xmlcfg->show_karma) == 'TRUE')			? true : false);
		$this->config['require_finish']					= (int)$xmlcfg->require_finish;
		$this->config['remind_to_vote']					= strtoupper((string)$xmlcfg->remind_to_vote);
		$this->config['reminder_window']['display']			= strtoupper((string)$xmlcfg->reminder_window->display);
		$this->config['score_mx_window']				= ((strtoupper((string)$xmlcfg->score_mx_window) == 'TRUE')		? true : false);
		$this->config['messages_in_window']				= ((strtoupper((string)$xmlcfg->messages_in_window) == 'TRUE')		? true : false);
		$this->config['show_player_vote_public']			= ((strtoupper((string)$xmlcfg->show_player_vote_public) == 'TRUE')	? true : false);
		$this->config['save_karma_also_local']				= ((strtoupper((string)$xmlcfg->save_karma_also_local) == 'TRUE')	? true : false);
		$this->config['sync_global_karma_local']			= ((strtoupper((string)$xmlcfg->sync_global_karma_local) == 'TRUE')	? true : false);
		$this->config['images']['widget_open_left']			= (string)$xmlcfg->images->widget_open_left;
		$this->config['images']['widget_open_right']			= (string)$xmlcfg->images->widget_open_right;
		$this->config['images']['mx_logo_normal']			= (string)$xmlcfg->images->mx_logo_normal;
		$this->config['images']['mx_logo_focus']			= (string)$xmlcfg->images->mx_logo_focus;
		$this->config['images']['button_normal']			= (string)$xmlcfg->images->button_normal;
		$this->config['images']['button_focus']				= (string)$xmlcfg->images->button_focus;
		$this->config['images']['cup_gold']				= (string)$xmlcfg->images->cup_gold;
		$this->config['images']['cup_silver']				= (string)$xmlcfg->images->cup_silver;
		$this->config['images']['maniakarma_logo']			= (string)$xmlcfg->images->maniakarma_logo;
		$this->config['images']['progress_indicator']			= (string)$xmlcfg->images->progress_indicator;
		$this->config['uptodate_check']					= ((strtoupper((string)$xmlcfg->uptodate_check) == 'TRUE')		? true : false);
		$this->config['uptodate_info']					= strtoupper((string)$xmlcfg->uptodate_info);

		$this->config['karma_calculation_method']			= strtoupper((string)$xmlcfg->karma_calculation_method);

		// Config for Karma Lottery
		$this->config['karma_lottery']['enabled']			= ((strtoupper((string)$xmlcfg->karma_lottery->enabled) == 'TRUE')	? true : false);
		$this->config['karma_lottery']['minimum_players']		= ((int)$xmlcfg->karma_lottery->minimum_players ? (int)$xmlcfg->karma_lottery->minimum_players : 1);
		$this->config['karma_lottery']['planets_win']			= (int)$xmlcfg->karma_lottery->planets_win;
		$this->config['karma_lottery']['minimum_server_planets']	= (int)$xmlcfg->karma_lottery->minimum_server_planets;
		$this->config['karma_lottery']['total_payout']			= 0;

		// purge mem. usage
		unset($xmlcfg->messages->comment);

		// Misc. messages
		$this->config['messages']['welcome']				= (string)$xmlcfg->messages->welcome;
		$this->config['messages']['uptodate_ok']			= (string)$xmlcfg->messages->uptodate_ok;
		$this->config['messages']['uptodate_new']			= (string)$xmlcfg->messages->uptodate_new;
		$this->config['messages']['uptodate_failed']			= (string)$xmlcfg->messages->uptodate_failed;

		// Vote messages
		$this->config['messages']['karma_message']			= (string)$xmlcfg->messages->karma_message;
		$this->config['messages']['karma_your_vote']			= (string)$xmlcfg->messages->karma_your_vote;
		$this->config['messages']['karma_not_voted']			= (string)$xmlcfg->messages->karma_not_voted;
		$this->config['messages']['karma_details']			= (string)$xmlcfg->messages->karma_details;
		$this->config['messages']['karma_done']				= (string)$xmlcfg->messages->karma_done;
		$this->config['messages']['karma_change']			= (string)$xmlcfg->messages->karma_change;
		$this->config['messages']['karma_voted']			= (string)$xmlcfg->messages->karma_voted;
		$this->config['messages']['karma_remind']			= (string)$xmlcfg->messages->karma_remind;
		$this->config['messages']['karma_require_finish']		= (string)$xmlcfg->messages->karma_require_finish;
		$this->config['messages']['karma_no_public']			= (string)$xmlcfg->messages->karma_no_public;
		$this->config['messages']['karma_list_help']			= (string)$xmlcfg->messages->karma_list_help;
		$this->config['messages']['karma_help']				= (string)$xmlcfg->messages->karma_help;

		$this->config['messages']['karma_reminder_at_score']		= (string)$xmlcfg->messages->karma_reminder_at_score;
		$this->config['messages']['karma_vote_singular']		= (string)$xmlcfg->messages->karma_vote_singular;
		$this->config['messages']['karma_vote_plural']			= (string)$xmlcfg->messages->karma_vote_plural;
		$this->config['messages']['karma_you_have_voted']		= (string)$xmlcfg->messages->karma_you_have_voted;
		$this->config['messages']['karma_fantastic']			= (string)$xmlcfg->messages->karma_fantastic;
		$this->config['messages']['karma_beautiful']			= (string)$xmlcfg->messages->karma_beautiful;
		$this->config['messages']['karma_good']				= (string)$xmlcfg->messages->karma_good;
		$this->config['messages']['karma_undecided']			= (string)$xmlcfg->messages->karma_undecided;
		$this->config['messages']['karma_bad']				= (string)$xmlcfg->messages->karma_bad;
		$this->config['messages']['karma_poor']				= (string)$xmlcfg->messages->karma_poor;
		$this->config['messages']['karma_waste']			= (string)$xmlcfg->messages->karma_waste;
		$this->config['messages']['karma_show_opinion']			= (string)$xmlcfg->messages->karma_show_opinion;
		$this->config['messages']['karma_show_undecided']		= (string)$xmlcfg->messages->karma_show_undecided;

		// Lottery messages
		$this->config['messages']['lottery_mail_body']			= (string)$xmlcfg->messages->lottery_mail_body;
		$this->config['messages']['lottery_player_won']			= (string)$xmlcfg->messages->lottery_player_won;
		$this->config['messages']['lottery_low_planets']		= (string)$xmlcfg->messages->lottery_low_planets;
		$this->config['messages']['lottery_to_few_players']		= (string)$xmlcfg->messages->lottery_to_few_players;
		$this->config['messages']['lottery_total_player_win']		= (string)$xmlcfg->messages->lottery_total_player_win;
		$this->config['messages']['lottery_help']			= (string)$xmlcfg->messages->lottery_help;

		// Widget specific
		$this->config['widget']['buttons']['bg_positive_default']	= (string)$xmlcfg->widget_styles->vote_buttons->positive->bgcolor_default;
		$this->config['widget']['buttons']['bg_positive_focus']		= (string)$xmlcfg->widget_styles->vote_buttons->positive->bgcolor_focus;
		$this->config['widget']['buttons']['positive_text_color']	= (string)$xmlcfg->widget_styles->vote_buttons->positive->text_color;
		$this->config['widget']['buttons']['bg_negative_default']	= (string)$xmlcfg->widget_styles->vote_buttons->negative->bgcolor_default;
		$this->config['widget']['buttons']['bg_negative_focus']		= (string)$xmlcfg->widget_styles->vote_buttons->negative->bgcolor_focus;
		$this->config['widget']['buttons']['negative_text_color']	= (string)$xmlcfg->widget_styles->vote_buttons->negative->text_color;
		$this->config['widget']['buttons']['bg_vote']			= (string)$xmlcfg->widget_styles->vote_buttons->votes->bgcolor_vote;
		$this->config['widget']['buttons']['bg_disabled']		= (string)$xmlcfg->widget_styles->vote_buttons->votes->bgcolor_disabled;
		$this->config['widget']['race']['title']			= (string)$xmlcfg->widget_styles->race->title;
		$this->config['widget']['race']['icon_style']			= (string)$xmlcfg->widget_styles->race->icon_style;
		$this->config['widget']['race']['icon_substyle']		= (string)$xmlcfg->widget_styles->race->icon_substyle;
		$this->config['widget']['race']['background_color']		= (string)$xmlcfg->widget_styles->race->background_color;
		$this->config['widget']['race']['background_focus']		= (string)$xmlcfg->widget_styles->race->background_focus;
		$this->config['widget']['race']['background_style']		= (string)$xmlcfg->widget_styles->race->background_style;
		$this->config['widget']['race']['background_substyle']		= (string)$xmlcfg->widget_styles->race->background_substyle;
		$this->config['widget']['race']['border_style']			= (string)$xmlcfg->widget_styles->race->border_style;
		$this->config['widget']['race']['border_substyle']		= (string)$xmlcfg->widget_styles->race->border_substyle;
		$this->config['widget']['race']['title_background']		= (string)$xmlcfg->widget_styles->race->title_background;
		$this->config['widget']['race']['title_style']			= (string)$xmlcfg->widget_styles->race->title_style;
		$this->config['widget']['race']['title_substyle']		= (string)$xmlcfg->widget_styles->race->title_substyle;
		$this->config['widget']['score']['title']			= (string)$xmlcfg->widget_styles->score->title;
		$this->config['widget']['score']['icon_style']			= (string)$xmlcfg->widget_styles->score->icon_style;
		$this->config['widget']['score']['icon_substyle']		= (string)$xmlcfg->widget_styles->score->icon_substyle;
		$this->config['widget']['score']['background_color']		= (string)$xmlcfg->widget_styles->score->background_color;
		$this->config['widget']['score']['background_style']		= (string)$xmlcfg->widget_styles->score->background_style;
		$this->config['widget']['score']['background_substyle']		= (string)$xmlcfg->widget_styles->score->background_substyle;
		$this->config['widget']['score']['title_background']		= (string)$xmlcfg->widget_styles->score->title_background;
		$this->config['widget']['score']['title_style']			= (string)$xmlcfg->widget_styles->score->title_style;
		$this->config['widget']['score']['title_substyle']		= (string)$xmlcfg->widget_styles->score->title_substyle;

		// Check for Background-Colors
		if ($this->config['widget']['race']['background_color'] == '') {
			$this->config['widget']['race']['background_color'] = '0000';
		}
		if ($this->config['widget']['race']['background_focus'] == '') {
			$this->config['widget']['race']['background_focus'] = '0000';
		}


		// Define the formats for number_format()
		$this->config['number_format'] = strtolower((string)$xmlcfg->number_format);
		$this->config['NumberFormat'] = array(
			'english'	=> array(
				'decimal_sep'	=> '.',
				'thousands_sep'	=> ',',
			),
			'german'	=> array(
				'decimal_sep'	=> ',',
				'thousands_sep'	=> '.',
			),
			'french'	=> array(
				'decimal_sep'	=> ',',
				'thousands_sep'	=> ' ',
			),
		);

		// Load the templates
		$this->config['Templates']		= $this->loadTemplates();

		// Init
		if ($aseco->startup_phase == true) {
			$this->karma			= $this->setEmptyKarma(true);
			$this->karma['data']['uid']	= $aseco->server->maps->current->uid;
			$this->karma['data']['id']	= $aseco->server->maps->current->id;
			$this->karma['data']['name']	= $aseco->server->maps->current->name;
			$this->karma['data']['author']	= $aseco->server->maps->current->author;
			$this->karma['data']['env']	= $aseco->server->maps->current->environment;
			$this->karma['data']['tmx']	= (isset($aseco->server->maps->current->mx->id) ? $aseco->server->maps->current->mx->id : '');
			$this->karma['new']['players']	= array();
		}

		// Update the global/local $this->karma
		$this->calculateKarma(array('global','local'));

		// Prebuild the Widgets
		$this->config['widget']['skeleton']['race'] 	= $this->buildKarmaWidget($this->config['widget']['current_state']);
		$this->config['widget']['skeleton']['score']	= $this->buildKarmaWidget(0);

		if ($this->config['retrytime'] == 0) {
			// Update KarmaWidget for all connected Players
			if ($this->config['widget']['current_state'] == 0) {
				$this->sendWidgetCombination(array('skeleton_score', 'cups_values'), false);
			}
			else {
				$this->sendWidgetCombination(array('skeleton_race', 'cups_values'), false);
			}
			foreach ($aseco->server->players->player_list as $player) {
				$this->sendWidgetCombination(array('player_marker'), $player);
			}

			// Hide connection status
			$this->sendConnectionStatus(true, false);
		}


		// Add "/karma lottery" to "/karma help" if lottery is enabled
		if ($this->config['karma_lottery']['enabled'] == true) {
			$this->config['messages']['karma_help'] .= $this->config['messages']['lottery_help'];
		}

		// Split long message
		$this->config['messages']['karma_help'] = str_replace('{br}', LF, $aseco->formatColors($this->config['messages']['karma_help']));

		// Free mem.
		unset($xmlcfg);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerChat ($aseco, $chat) {

		if (!$player = $aseco->server->players->getPlayer($chat[1])) {
			return;
		}

		// Check if public vote is enabled
		if ($this->config['allow_public_vote'] == true) {
			// check for possible public karma vote
			if ($chat[2] == '+++') {
				$this->handlePlayerVote($player, 3);
			}
			else if ($chat[2] == '++') {
				$this->handlePlayerVote($player, 2);
			}
			else if ($chat[2] == '+') {
				$this->handlePlayerVote($player, 1);
			}
			else if ($chat[2] == '-') {
				$this->handlePlayerVote($player, -1);
			}
			else if ($chat[2] == '--') {
				$this->handlePlayerVote($player, -2);
			}
			else if ($chat[2] == '---') {
				$this->handlePlayerVote($player, -3);
			}
		}
		else if ( ($chat[2] == '+++') || ($chat[2] == '++') || ($chat[2] == '+') || ($chat[2] == '-') || ($chat[2] == '--') || ($chat[2] == '---') ) {
			$message = $aseco->formatText($this->config['messages']['karma_no_public'], '/'. $chat[2]);
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, ($player->login ? $player : false));
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_karma ($aseco, $login, $chat_command, $chat_parameter) {

		if (!$player = $aseco->server->players->getPlayer($login)) {
			return;
		}

		// Init
		$message = false;

		// Check optional parameter
		if ( (strtoupper($chat_parameter) == 'HELP') || (strtoupper($chat_parameter) == 'ABOUT') ) {
			$this->sendHelpAboutWindow($player->login, $this->config['messages']['karma_help']);
		}
		else if (strtoupper($chat_parameter) == 'DETAILS') {
			$message = $aseco->formatText($this->config['messages']['karma_details'],
				$this->karma['global']['votes']['karma'],
				$this->karma['global']['votes']['fantastic']['percent'],	$this->karma['global']['votes']['fantastic']['count'],
				$this->karma['global']['votes']['beautiful']['percent'],	$this->karma['global']['votes']['beautiful']['count'],
				$this->karma['global']['votes']['good']['percent'],		$this->karma['global']['votes']['good']['count'],
				$this->karma['global']['votes']['bad']['percent'],		$this->karma['global']['votes']['bad']['count'],
				$this->karma['global']['votes']['poor']['percent'],		$this->karma['global']['votes']['poor']['count'],
				$this->karma['global']['votes']['waste']['percent'],		$this->karma['global']['votes']['waste']['count']
			);
		}
		else if (strtoupper($chat_parameter) == 'RELOAD') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' reloads the configuration.');
				$message = '{#admin}Reloading the configuration "mania_karma.xml" now.';
				$this->onSync($aseco);
			}
		}
		else if (strtoupper($chat_parameter) == 'EXPORT') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' start the export of all local votes.');
				$this->exportVotes($player);
			}
		}
		else if (strtoupper($chat_parameter) == 'UPTODATE') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' start the up-to-date check.');
				$this->uptodateCheck($player);
			}
		}
		else if ( (strtoupper($chat_parameter) == 'LOTTERY') && ($this->config['karma_lottery']['enabled'] == true) ) {
			if  ( (isset($player->rights)) && ($player->rights) ) {
				$message = $aseco->formatText($this->config['messages']['lottery_total_player_win'],
					$this->getPlayerData($player, 'LotteryPayout')
				);
			}
		}
		else if (strtoupper($chat_parameter) == '') {
			$message = $this->createKarmaMessage($player->login, true);
		}

		// Show message
		if ($message != false) {
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) && ($this->config['widget']['current_state'] != 0) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function chat_votes ($aseco, $login, $chat_command, $chat_parameter) {

		if ($player = $aseco->server->players->getPlayer($login)) {
			if ($chat_parameter == '+++') {
				$this->handlePlayerVote($player, 3);
			}
			else if ($chat_parameter == '++') {
				$this->handlePlayerVote($player, 2);
			}
			else if ($chat_parameter == '+') {
				$this->handlePlayerVote($player, 1);
			}
			else if ($chat_parameter == '-') {
				$this->handlePlayerVote($player, -1);
			}
			else if ($chat_parameter == '--') {
				$this->handlePlayerVote($player, -2);
			}
			else if ($chat_parameter == '---') {
				$this->handlePlayerVote($player, -3);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onShutdown ($aseco) {

		// Save all Votes into the global and local (if enabled) Database
		$this->storeKarmaVotes();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onKarmaChange ($aseco, $unused) {

		// Update the KarmaWidget for all Players
		$this->sendWidgetCombination(array('cups_values'), false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerConnect ($aseco, $player) {

		// Show welcome message to the new player?
		if ($this->config['show_welcome'] == true) {
			$message = $aseco->formatText($this->config['messages']['welcome'],
					'http://'. $this->config['urls']['website'] .'/',
					$this->config['urls']['website']
			);
			$message = str_replace('{br}', LF, $message);  // split long message
			$aseco->sendChatMessage($message, $player->login);
		}


		// Check for a MasterAdmin
		if ($aseco->isMasterAdmin($player)) {
			// Do UpToDate check?
			if ($this->config['uptodate_check'] == true) {
				$this->uptodateCheck($player);
			}

			// Export already made?
			if ($this->config['import_done'] == false) {
				$message = '{#server}> {#emotic}#################################################'. LF;
				$message .= '{#server}> {#emotic}Please start the export of your current local votes with the command "/karma export". Thanks!'. LF;
				$message .= '{#server}> {#emotic}#################################################'. LF;
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// If karma lottery is enabled, then initialize (if player has related rights)
		if ($this->config['karma_lottery']['enabled'] == true) {
			if ( (isset($player->rights)) && ($player->rights) ) {
				$this->storePlayerData($player, 'LotteryPayout', 0);
			}
		}

		// Init the 'KarmaWidgetStatus' and 'KarmaReminderWindow' to the defaults
		$this->storePlayerData($player, 'ReminderWindow', false);

		// Init
		$this->storePlayerData($player, 'FinishedMapCount', 0);


		// Check if finishes are required
		if ($this->config['require_finish'] > 0) {
			// Find the amount of finish for this Player
			$this->findPlayersLocalRecords($aseco->server->maps->current->id, array($player));
		}

		// Do nothing at Startup!!
		if ($aseco->startup_phase == false) {
			// Check if Player is already in $this->karma,
			// for "unwished disconnects" and "reconnected" Players
			if ( ( !isset($this->karma['global']['players'][$player->login]) ) || ($aseco->server->maps->current->uid != $this->karma['data']['uid']) ) {

				if ( !isset($this->karma['global']['players'][$player->login]) ) {
					$this->karma['global']['players'][$player->login]['vote']	= 0;
					$this->karma['global']['players'][$player->login]['previous']	= 0;
				}

				if ( !isset($this->karma['local']['players'][$player->login]) ) {
					// Get the local votes for this Player
					$this->getLocalVotes($aseco->server->maps->current->id, $player->login);
				}

				// Get the Karma from remote for this Player
				$this->handleGetApiCall($aseco->server->maps->current, $player);

				// Check to see if it is required to sync global to local votes?
				if ($this->config['sync_global_karma_local'] == true) {
					$this->syncGlobaAndLocalVotes('local', false);
				}
			}

			// Display the complete KarmaWidget only for connected Player
			if ($this->config['widget']['current_state'] == 0) {
				$this->sendWidgetCombination(array('skeleton_score', 'cups_values', 'player_marker'), $player);
			}
			else {
				$this->sendWidgetCombination(array('skeleton_race', 'cups_values', 'player_marker'), $player);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnectPrepare ($aseco, $player) {

		// Remove temporary Player data, do not need to be stored into the database.
		$this->removePlayerData($player, 'LotteryPayout');
		$this->removePlayerData($player, 'FinishedMapCount');
		$this->removePlayerData($player, 'ReminderWindow');
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerDisconnect ($aseco, $player) {

		// Need to pay planets for lottery wins to this player?
		if ($this->config['karma_lottery']['enabled'] == true) {
			if ( (isset($player->rights)) && ($player->rights) ) {
				if ($this->getPlayerData($player, 'LotteryPayout') > 0) {
					// Pay planets to player
					$message = $aseco->formatText($this->config['messages']['lottery_mail_body'],
						$aseco->server->name,
						$this->getPlayerData($player, 'LotteryPayout'),
						$this->config['account']['login']
					);
					$message = str_replace('{br}', "%0A", $message);  // split long message

					$billid = false;
					try {
						$billid = $aseco->client->query('Pay', (string)$player->login, $this->getPlayerData($player, 'LotteryPayout'), (string)$aseco->formatColors($message) );
					}
					catch (Exception $exception) {
						$aseco->console('[ManiaKarma] (ManiaKarma lottery) Pay '. $this->getPlayerData($player, 'LotteryPayout') .' planets to player "'. $player->login .'" failed: [' . $exception->getCode() . '] ' . $exception->getMessage());
						return false;
					}

					// Payment done
					$aseco->console('[ManiaKarma] (ManiaKarma lottery) Pay '. $this->getPlayerData($player, 'LotteryPayout') .' planets to player "'. $player->login .'" done. (BillId #'. $billid .')');

					// Subtract paid amounts from total
					$this->config['karma_lottery']['total_payout'] -= (int)$this->getPlayerData($player, 'LotteryPayout');
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerFinish ($aseco, $finish_item) {

		// If no actual finish, bail out immediately
		if ($finish_item->score == 0) {
			return;
		}

		// Check if finishes are required
		if ($this->config['require_finish'] > 0) {
			// Save that the player finished this map
			$this->storePlayerData($finish_item->player, 'FinishedMapCount', ($this->getPlayerData($finish_item->player, 'FinishedMapCount') + 1));

			// Enable the vote possibilities for this player
			$this->sendWidgetCombination(array('player_marker'), $finish_item->player);
		}

		// If no finish reminders, bail out too (does not need to check $this->getPlayerData($player, 'FinishedMapCount'), because actually finished ;)
		if ( ($this->config['remind_to_vote'] == 'FINISHED') || ($this->config['remind_to_vote'] == 'ALWAYS') ) {

			// Check whether player already voted
			if ( ($this->karma['global']['players'][$finish_item->player->login]['vote'] == 0) && ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] <= $this->getPlayerData($finish_item->player, 'FinishedMapCount')) ) ) {
				if ( ($this->config['reminder_window']['display'] == 'FINISHED') || ($this->config['reminder_window']['display'] == 'ALWAYS') ) {
					// Show reminder window
					$this->showReminderWindow($finish_item->player->login);
					$this->storePlayerData($finish_item->player, 'ReminderWindow', true);
				}
				else {
					// Show reminder message
					$message = $this->config['messages']['karma_remind'];
					if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
						send_window_message($aseco, $message, $finish_item->player);
					}
					else {
						$aseco->sendChatMessage($message, $finish_item->player->login);
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onPlayerManialinkPageAnswer ($aseco, $answer) {

		// If id = 0, bail out immediately
		if ($answer[2] == 0) {
			return;
		}

		// Get Player
		if (!$player = $aseco->server->players->getPlayer($answer[1])) {
			return;
		}

		if ($answer[2] == $this->config['manialink_id'] .'01') {			// Open HelpWindow
			$this->sendHelpAboutWindow($player->login, $this->config['messages']['karma_help']);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'02') {		// Open KarmaDetailWindow
			$window = $this->buildKarmaDetailWindow($player->login);
			$this->sendWindow($player->login, $window);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'03') {		// Open WhoKarmaWindow
			$window = $this->buildWhoKarmaWindow($player->login);
			$this->sendWindow($player->login, $window);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'12') {		// Vote +++
			$this->handlePlayerVote($player, 3);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'11') {		// Vote ++
			$this->handlePlayerVote($player, 2);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'10') {		// Vote +
			$this->handlePlayerVote($player, 1);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'13') {		// Vote undecided
			$this->showUndecidedMessage($command);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'14') {		// Vote -
			$this->handlePlayerVote($player, -1);
	}
		else if ($answer[2] == $this->config['manialink_id'] .'15') {		// Vote --
			$this->handlePlayerVote($player, -2);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'16') {		// Vote ---
			$this->handlePlayerVote($player, -3);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'17') {		// Vote disabled on <require_finish> >= 1
			$this->handlePlayerVote($player, 0);
		}
		else if ($answer[2] == $this->config['manialink_id'] .'18') {		// No action, just ignore
			// do nothing
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $id) {

		// Set $gamemode for the KarmaWidget
		$this->config['widget']['current_state'] = $aseco->server->gameinfo->mode;

		// Close at all Players the reminder window
		$this->closeReminderWindow(false);

		// Remove all marker at all connected Players
		$this->sendWidgetCombination(array('hide_all'), false);

		// Save all Votes into the global and local (if enabled) Databases
		$this->storeKarmaVotes();

		if ($this->config['require_finish'] > 0) {
			// Remove the state that the player has finished this map (it is an new map now)
			// MUST placed here _BEFORE_ $this->handleGetApiCall() call, this sets
			// $this->getPlayerData($player, 'FinishedMapCount') to true if the player has voted this map
			foreach ($aseco->server->players->player_list as $player) {
				$this->storePlayerData($player, 'FinishedMapCount', 0);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onBeginMap ($aseco, $map) {

		// Reset and Setup data for MultiVotes
		$this->karma = $this->setEmptyKarma(true);
		$this->karma['data']['uid']		= $map->uid;
		$this->karma['data']['id']		= $map->id;
		$this->karma['data']['name']		= $map->name;
		$this->karma['data']['author']		= $map->author;
		$this->karma['data']['env']		= $map->environment;
		$this->karma['data']['tmx']		= (isset($map->mx->id) ? $map->mx->id : '');
		$this->karma['new']['players']		= array();

		// If there no players, bail out
		if (count($aseco->server->players->player_list) == 0) {

			if ($this->config['retrytime'] == 0) {
				// Start an async PING request
				// Generate the url for this Ping-Request
				$api_url = sprintf("%s?Action=Ping&login=%s&authcode=%s",
					$this->config['urls']['api'],
					urlencode( $this->config['account']['login'] ),
					urlencode( $this->config['account']['authcode'] )
				);

				$aseco->webaccess->request($api_url, array(array($this, 'handleWebaccess'), 'PING', $api_url), 'none', false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
			}

			// Get the local karma
			$this->getLocalKarma($aseco->server->maps->current->id);

			return;
		}

		// Get the local karma
		$this->getLocalKarma($aseco->server->maps->current->id);

		// Get the local votes for all Players
		$this->getLocalVotes($aseco->server->maps->current->id, false);

		// If <require_finish> is enabled
		if ($this->config['require_finish'] > 0) {
			// Find the amount of finish for all Players
			$this->findPlayersLocalRecords($aseco->server->maps->current->id, $aseco->server->players->player_list);
		}

		$this->sendLoadingIndicator(true, $this->config['widget']['current_state']);

		// Replace $this->karma from last Map with $this->karma of the current Map
		$this->handleGetApiCall($aseco->server->maps->current, false);

		// Check to see if it is required to sync global to local votes?
		if ($this->config['sync_global_karma_local'] == true) {
			$this->syncGlobaAndLocalVotes('local', false);
		}

		// Rebuild the Widget, it is an new Map (and possible Gamemode)
		$this->config['widget']['skeleton']['race']	= $this->buildKarmaWidget($aseco->server->gameinfo->mode);
		$this->config['widget']['skeleton']['score']	= $this->buildKarmaWidget(0);

		// Update KarmaWidget for all connected Players
		$this->sendWidgetCombination(array('skeleton_race', 'cups_values'), false);


		// Refresh the Player-Marker for all Players
		foreach ($aseco->server->players->player_list as $player) {
			// Display the Marker
			$this->sendWidgetCombination(array('player_marker'), $player);
		}

		// Display connection status
		if ($this->config['retrytime'] > 0) {
			$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
		}

		// Before draw a lottery winner, check if players has already voted, if lottery is enabled and if players has related rights (TMU)
		if ($this->config['karma_lottery']['enabled'] == true) {

			// Init message
			$message = false;

			// Is there not enough player on, bail out
			if (count($aseco->server->players->player_list) < $this->config['karma_lottery']['minimum_players']) {
				// Show to few players message to all players
				$message = $this->config['messages']['lottery_to_few_players'];
			}
			else {
				// Can all Player be paid with the new total? Add only Planets if Server is over minimum.
				if (($aseco->server->amount_planets - $this->config['karma_lottery']['minimum_server_planets']) > ($this->config['karma_lottery']['total_payout'] + $this->config['karma_lottery']['planets_win']) ) {

					// Init the lottery array
					$lottery_attendant = array();

					// Check all connected Players if they has voted
					foreach ($aseco->server->players->player_list as $player) {
						if ($this->karma['global']['players'][$player->login]['vote'] != 0) {
							array_push($lottery_attendant, $player->login);
						}
					}

					// Are enough Players online and has voted?
					if (count($lottery_attendant) >= $this->config['karma_lottery']['minimum_players']) {
						// Drawing of the lottery ("and the winner is")
						$winner = array_rand($lottery_attendant, 1);

						// If the Player is not already gone, go ahead
						if ($player = $aseco->server->players->getPlayer($lottery_attendant[$winner])) {
							// Add to Players total
							$this->storePlayerData($player, 'LotteryPayout', ($this->getPlayerData($player, 'LotteryPayout') + $this->config['karma_lottery']['planets_win']));

							// Add to total payout
							$this->config['karma_lottery']['total_payout'] += $this->config['karma_lottery']['planets_win'];

							// Show won message to all Players
							$message = $aseco->formatText($this->config['messages']['lottery_player_won'],
									$player->nickname,
									$this->config['karma_lottery']['planets_win']
							);
						}
						else {
							// Show to few Players message to all players
							$message = $this->config['messages']['lottery_to_few_players'];
						}
					}
					else {
						// Show to few players message to all players
						$message = $this->config['messages']['lottery_to_few_players'];
					}
				}
				else {
					// Show low planets message to all Players
					$message = $this->config['messages']['lottery_low_planets'];
				}
			}

			$message = str_replace('{br}', LF, $message);  // split long message
			if ( ($message !== false) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, false);
			}
			else {
				$aseco->sendChatMessage($message);
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onRestartMap ($aseco, $map) {

		// Close at all Players the reminder window
		$this->closeReminderWindow(false);

		// Set $gamemode for the KarmaWidget
		$this->config['widget']['current_state'] = $aseco->server->gameinfo->mode;


		// Make sure the Widget gets updated at all Players at Race
		$this->sendWidgetCombination(array('skeleton_race', 'cups_values'), false);


		// Display the Marker
		foreach ($aseco->server->players->player_list as $player) {
			// Update KarmaWidget only for given Player
			$this->sendWidgetCombination(array('player_marker'), $player);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onEndMap1 ($aseco, $data) {

		// If there no players, bail out immediately
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}


		// Finished run, set 'SCORE' for the KarmaWidget
		$this->config['widget']['current_state'] = 0;


		// Display connection status
		if ($this->config['retrytime'] > 0) {
			$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
		}

		// Update KarmaWidget for all connected Players
		$this->sendWidgetCombination(array('hide_window', 'skeleton_score', 'cups_values'), false);

		// Refresh the Player-Marker for all Players
		foreach ($aseco->server->players->player_list as $player) {
			// Update KarmaWidget only for given Player
			$this->sendWidgetCombination(array('player_marker'), $player);
		}


		// If no end race reminders, bail out immediately
		if ( ($this->config['remind_to_vote'] == 'SCORE') || ($this->config['remind_to_vote'] == 'ALWAYS') ) {

			// Check all connected Players
			$players_reminder = array();
			foreach ($aseco->server->players->player_list as $player) {

				// Skip if Player did not finished the map but it is required to vote
				if ( ($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish']) ) {
					continue;
				}

				// Check whether Player already voted
				if ($this->karma['global']['players'][$player->login]['vote'] == 0) {
					$players_reminder[] = $player->login;
					$this->storePlayerData($player, 'ReminderWindow', true);
				}
				else if ($this->config['score_mx_window'] == true) {
					// Show the MX-Link-Window
					$this->showManiaExchangeLinkWindow($player);
				}
			}

			if (count($players_reminder) > 0) {
				if ( ($this->config['reminder_window']['display'] == 'SCORE') || ($this->config['reminder_window']['display'] == 'ALWAYS') ) {
					// Show reminder Window
					$this->showReminderWindow(implode(',', $players_reminder));
				}
				else {
					// Show reminder message (not to the TMF-Message Window)
					$message = $this->config['messages']['karma_remind'];
					$aseco->sendChatMessage($message, implode(',', $players_reminder));
				}
			}
			unset($players_reminder);

		}
		else if ($this->config['score_mx_window'] == true) {
			// Check all connected Players
			foreach ($aseco->server->players->player_list as $player) {

				// Get current Player status and ignore Spectators
				if ($player->isspectator) {
					continue;
				}

				// Check whether Player already voted
				if ($this->karma['global']['players'][$player->login]['vote'] != 0) {
					// Show the MX-Link-Window
					$this->showManiaExchangeLinkWindow($player);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendWidgetCombination ($widgets, $player = false) {
		global $aseco;

		// If there no players, bail out immediately
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}

		$xml  = '';

		// Possible parameters: 'skeleton_race', 'skeleton_score', 'cups_values', 'player_marker', 'hide_window' and 'hide_all'
		foreach ($widgets as $widget) {
			if ($widget == 'hide_all') {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'02" name="Windows"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'06" name="ConnectionStatus"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'07" name="LoadingIndicator"></manialink>';
				break;
			}

			if ($widget == 'hide_window') {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'02" name="'. $this->config['manialink_id'] .'02"></manialink>';	// Windows
			}

			if ($this->config['widget']['states'][$this->config['widget']['current_state']]['enabled'] == true) {
				if ($widget == 'skeleton_race') {
					$xml .= $this->config['widget']['skeleton']['race'];
				}
				else if ($widget == 'skeleton_score') {
					$xml .= $this->config['widget']['skeleton']['score'];
				}
				else if ($widget == 'cups_values') {
					$xml .= $this->buildKarmaCupsValue($this->config['widget']['current_state']);
				}
				else if ($widget == 'player_marker') {
					$xml .= $this->buildPlayerVoteMarker($player, $this->config['widget']['current_state']);
				}
			}
			else {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue"></manialink>';
			}
		}

		if ($player != false) {
			$aseco->sendManialink($xml, $player->login, 0, false);
		}
		else {
			$aseco->sendManialink($xml, false, 0, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildKarmaWidget ($gamemode) {
		global $aseco;

		// No Placeholder here!
		$xml = '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget">';

		// MainWidget Frame
		$xml .= '<frame posn="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .' 10" id="'. $this->config['manialink_id'] .'03MainFrame">';
		if ($gamemode == 0) {
			// No action to open the full widget at 'Score'
			if ($this->config['widget']['score']['background_color'] != '') {
				$xml .= '<quad posn="0 0 0.02" sizen="15.76 10.75" bgcolor="'. $this->config['widget']['score']['background_color'] .'"/>';
			}
			else {
				$xml .= '<quad posn="0 0 0.02" sizen="15.76 10.75" style="'. $this->config['widget']['score']['background_style'] .'" substyle="'. $this->config['widget']['score']['background_substyle'] .'"/>';
			}
		}
		else {
			$xml .= '<label posn="0.1 -0.1 0" sizen="15.56 10.55" action="'. $this->config['manialink_id'] .'02" text=" " focusareacolor1="'. $this->config['widget']['race']['background_color'] .'" focusareacolor2="'. $this->config['widget']['race']['background_focus'] .'"/>';
			$xml .= '<quad posn="-0.2 0.3 0.01" sizen="16.16 11.35" style="'. $this->config['widget']['race']['border_style'] .'" substyle="'. $this->config['widget']['race']['border_substyle'] .'"/>';
			$xml .= '<quad posn="0 0 0.02" sizen="15.76 10.75" style="'. $this->config['widget']['race']['background_style'] .'" substyle="'. $this->config['widget']['race']['background_substyle'] .'"/>';
			if ($this->config['widget']['states'][$gamemode]['pos_x'] > 0) {
				$xml .= '<quad posn="-0.3 -7.4 0.05" sizen="3.5 3.5" image="'. $this->config['images']['widget_open_left'] .'"/>';
			}
			else {
				$xml .= '<quad posn="12.46 -7.4 0.05" sizen="3.5 3.5" image="'. $this->config['images']['widget_open_right'] .'"/>';
			}
		}

		// Vote Frame, different offset on default widget
		$xml .= '<frame posn="0 0 0">';


		// Window title
		if ($gamemode == 0) {
			if ($this->config['widget']['score']['title_background'] != '') {
				$xml .= '<quad posn="0.4 -0.4 3" sizen="14.96 2" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" bgcolor="'. $this->config['widget']['score']['title_background'] .'"/>';
			}
			else {
				$xml .= '<quad posn="0.4 -0.4 3" sizen="14.96 2" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" style="'. $this->config['widget']['score']['title_style'] .'" substyle="'. $this->config['widget']['score']['title_substyle'] .'"/>';
			}
		}
		else {
			if ($this->config['widget']['race']['title_background'] != '') {
				$xml .= '<quad posn="0.4 -0.4 3" sizen="14.96 2" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" bgcolor="'. $this->config['widget']['race']['title_background'] .'"/>';
			}
			else {
				$xml .= '<quad posn="0.4 -0.4 3" sizen="14.96 2" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" style="'. $this->config['widget']['race']['title_style'] .'" substyle="'. $this->config['widget']['race']['title_substyle'] .'"/>';
			}
		}

		if ($gamemode == 0) {
			$title = $this->config['widget']['score']['title'];
			$icon_style = $this->config['widget']['score']['icon_style'];
			$icon_substyle = $this->config['widget']['score']['icon_substyle'];
		}
		else {
			$title = $this->config['widget']['race']['title'];
			$icon_style = $this->config['widget']['race']['icon_style'];
			$icon_substyle = $this->config['widget']['race']['icon_substyle'];
		}

		if ($this->config['widget']['states'][$gamemode]['pos_x'] > 0) {
			// Position from icon and title to left
			$xml .= '<quad posn="0.6 -0.15 3.1" sizen="2.3 2.3" style="'. $icon_style .'" substyle="'. $icon_substyle .'"/>';
			$xml .= '<label posn="3.2 -0.6 3.2" sizen="10 0" halign="left" textsize="1" text="'. $title .'"/>';
		}
		else {
			// Position from icon and title to right
			$xml .= '<quad posn="13.1 -0.15 3.1" sizen="2.3 2.3" style="'. $icon_style .'" substyle="'. $icon_substyle .'"/>';
			$xml .= '<label posn="12.86 -0.6 3.2" sizen="10 0" halign="right" textsize="1" text="'. $title .'"/>';
		}

		// BG for Buttons to prevent flicker of the widget background (clickable too)
		$xml .= '<frame posn="1.83 -8.3 1">';
		$xml .= '<quad posn="0.2 -0.08 0.1" sizen="11.8 1.4" action="'. $this->config['manialink_id'] .'18" bgcolor="0000"/>';
		$xml .= '</frame>';

		// Button +++
		$xml .= '<frame posn="1.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'12" focusareacolor1="'. $this->config['widget']['buttons']['bg_positive_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.3 0.4" sizen="1.8 0" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="+++"/>';
		$xml .= '</frame>';

		// Button ++
		$xml .= '<frame posn="3.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'11" focusareacolor1="'. $this->config['widget']['buttons']['bg_positive_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.3 0.4" sizen="1.8 0" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="++"/>';
		$xml .= '</frame>';

		// Button +
		$xml .= '<frame posn="5.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'10" focusareacolor1="'. $this->config['widget']['buttons']['bg_positive_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.3 0.4" sizen="1.8 0" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="+"/>';
		$xml .= '</frame>';

		// Button -
		$xml .= '<frame posn="7.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'14" focusareacolor1="'. $this->config['widget']['buttons']['bg_negative_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.2 0.4" sizen="1.8 0" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="-"/>';
		$xml .= '</frame>';

		// Button --
		$xml .= '<frame posn="9.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'15" focusareacolor1="'. $this->config['widget']['buttons']['bg_negative_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.2 0.4" sizen="1.8 0" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="--"/>';
		$xml .= '</frame>';

		// Button ---
		$xml .= '<frame posn="11.83 -8.5 1">';
		$xml .= '<label posn="0.2 -0.08 0.2" sizen="1.8 1.4" action="'. $this->config['manialink_id'] .'16" focusareacolor1="'. $this->config['widget']['buttons']['bg_negative_default'] .'" focusareacolor2="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" text=" "/>';
		$xml .= '<label posn="1.12 -0.2 0.4" sizen="1.8 0" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="---"/>';
		$xml .= '</frame>';


		$xml .= '</frame>'; // Vote Frame
		$xml .= '</frame>'; // MainWidget Frame

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container <=> (Page.GetFirstChild(Page.MainFrame.ControlId ^ "MainFrame") as CMlFrame);
	Container.RelativeScale = {$this->config['widget']['states'][$gamemode]['scale']};
}
--></script>
EOL;

		$xml .= $maniascript;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildKarmaCupsValue ($gamemode) {
		global $aseco;

		$total_cups = 10;
		$cup_offset = array(
			0.8,
			0.85,
			0.85,
			0.875,
			0.90,
			0.925,
			0.95,
			0.975,
			1.0,
			1.025,
		);

		$cup_gold_amount = 0;
		if ($this->karma['global']['votes']['karma'] > 0) {
			if ($this->config['karma_calculation_method'] == 'RASP') {
				$positive = $this->karma['global']['votes']['fantastic']['count'] + $this->karma['global']['votes']['beautiful']['count'] + $this->karma['global']['votes']['good']['count'];
				$cup_gold_amount = round($positive / $this->karma['global']['votes']['total'] * $total_cups);
			}
			else {
				$cup_gold_amount = intval($this->karma['global']['votes']['karma'] / $total_cups);
			}
		}
		else if ($this->karma['local']['votes']['karma'] > 0) {
			if ($this->config['karma_calculation_method'] == 'RASP') {
				$positive = $this->karma['local']['votes']['fantastic']['count'] + $this->karma['local']['votes']['beautiful']['count'] + $this->karma['local']['votes']['good']['count'];
				$cup_gold_amount = round($positive / $this->karma['local']['votes']['total'] * $total_cups);
			}
			else {
				$cup_gold_amount = intval($this->karma['local']['votes']['karma'] / $total_cups);
			}
		}
		$cup_silver = '<quad posn="%x% 0 %z%" sizen="%width% %height%" valign="bottom" image="'. $this->config['images']['cup_silver'] .'"/>';
		$cup_gold = '<quad posn="%x% 0 %z%" sizen="%width% %height%" valign="bottom" image="'. $this->config['images']['cup_gold'] .'"/>';
		$cups_result = '';
		for ($i = 0 ; $i < $total_cups ; $i ++) {
			$layer = sprintf("0.%02d", ($i+1));
			$width = 1.1 + ($i / $total_cups) * $cup_offset[$i];
			$height = 1.5 + ($i / $total_cups) * $cup_offset[$i];
			if ($i < $cup_gold_amount) {
				$award = $cup_gold;
			}
			else {
				$award = $cup_silver;
			}
			$cups_result .= str_replace(array('%width%', '%height%', '%x%', '%z%'), array($width, $height, ($cup_offset[$i]*$i), $layer), $award);
		}


		$xml  = '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue">';
		$xml .= '<frame posn="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .' 10" id="'. $this->config['manialink_id'] .'05MainFrame">';

		// Cups
		$xml .= '<frame posn="2.23 -4.95 0.01">';
		$xml .= $cups_result;
		$xml .= '</frame>';

		// Global Value and Votes
		$globalcolor = 'FFFF';
		if ($this->config['karma_calculation_method'] == 'DEFAULT') {
			if ( ($this->karma['global']['votes']['karma'] >= 0) && ($this->karma['global']['votes']['karma'] <= 30) ) {
				$globalcolor = 'D00F';
			}
			else if ( ($this->karma['global']['votes']['karma'] >= 31) && ($this->karma['global']['votes']['karma'] <= 60) ) {
				$globalcolor = 'DD0F';
			}
			else if ( ($this->karma['global']['votes']['karma'] >= 61) && ($this->karma['global']['votes']['karma'] <= 100) ) {
				$globalcolor = '0D0F';
			}
		}

		// Local Value and Votes
		$localcolor = 'FFFF';
		if ($this->config['karma_calculation_method'] == 'DEFAULT') {
			if ( ($this->karma['local']['votes']['karma'] >= 0) && ($this->karma['local']['votes']['karma'] <= 30) ) {
				$localcolor = 'F00F';
			}
			else if ( ($this->karma['local']['votes']['karma'] >= 31) && ($this->karma['local']['votes']['karma'] <= 60) ) {
				$localcolor = 'FF0F';
			}
			else if ( ($this->karma['local']['votes']['karma'] >= 61) && ($this->karma['local']['votes']['karma'] <= 100) ) {
				$localcolor = '0F0F';
			}
		}

		// Global values and votes
		$xml .= '<frame posn="2.1 -5.35 0">';
		$xml .= '<quad posn="0 -0.1 1" sizen="0.1 2.85" bgcolor="FFF5"/>';
		$xml .= '<label posn="0.3 -0.1 1" sizen="4 1.1" textsize="1" scale="0.65" textcolor="FFFF" text="GLOBAL"/>';
		$xml .= '<label posn="3.3 0 1" sizen="3 1.4" textsize="1" scale="0.9" textcolor="'. $globalcolor .'" text="$O'. $this->karma['global']['votes']['karma'] .'"/>';
		$xml .= '<label posn="0.3 -1.3 1" sizen="6.6 1.2" textsize="1" scale="0.85" textcolor="0F3F" text="'. number_format($this->karma['global']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['global']['votes']['total'] == 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';
		$xml .= '</frame>';

		// Local values and votes
		$xml .= '<frame posn="8.75 -5.35 0">';
		$xml .= '<quad posn="0 -0.1 1" sizen="0.1 2.85" bgcolor="FFF5"/>';
		$xml .= '<label posn="0.3 -0.1 1" sizen="4 1.1" textsize="1" scale="0.65" textcolor="FFFF" text="LOCAL "/>';
		$xml .= '<label posn="3 0 1" sizen="3 1.4" textsize="1" scale="0.9" textcolor="'. $localcolor .'" text="$O'. $this->karma['local']['votes']['karma'] .'"/>';
		$xml .= '<label posn="0.3 -1.3 1" sizen="6.6 1.2" textsize="1" scale="0.85" textcolor="0F3F" text="'. number_format($this->karma['local']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['local']['votes']['total'] == 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';
		$xml .= '</frame>';


		$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container <=> (Page.GetFirstChild(Page.MainFrame.ControlId ^ "MainFrame") as CMlFrame);
	Container.RelativeScale = {$this->config['widget']['states'][$gamemode]['scale']};
}
--></script>
EOL;
		$xml .= $maniascript;
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendWindow ($login, $window) {
		global $aseco;

		$aseco->sendManialink($window, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildKarmaDetailWindow ($login) {
		global $aseco;

		// Frame for Previous/Next Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';

		// Reload button
		$buttons .= '<quad posn="16.65 -1 0.14" sizen="3 3" action="'. $this->config['manialink_id'] .'02" style="Icons64x64_1" substyle="Refresh"/>';

		// Previous button
		$buttons .= '<quad posn="19.95 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';

		// Next button
		$buttons .= '<quad posn="23.25 -1 0.12" sizen="3 3" action="'. $this->config['manialink_id'] .'03" style="Icons64x64_1" substyle="Maximize"/>';
		$buttons .= '<quad posn="23.65 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
		$buttons .= '<quad posn="23.45 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowRight2"/>';
		$buttons .= '</frame>';

		$xml = str_replace(
			array(
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'ManiaKarma detailed vote statistic',
				$buttons
			),
			$this->config['Templates']['WINDOW']['HEADER']
		);


		// Build Karma Headline

		// Global Karma
		$color = '$FFF';
		if ($this->config['karma_calculation_method'] == 'DEFAULT') {
			if ( ($this->karma['global']['votes']['karma'] >= 0) && ($this->karma['global']['votes']['karma'] <= 30) ) {
				$color = '$D00';
			}
			else if ( ($this->karma['global']['votes']['karma'] >= 31) && ($this->karma['global']['votes']['karma'] <= 60) ) {
				$color = '$DD0';
			}
			else if ( ($this->karma['global']['votes']['karma'] >= 61) && ($this->karma['global']['votes']['karma'] <= 100) ) {
				$color = '$0D0';
			}
		}
		$xml .= '<label posn="10.2 -6.5 0.03" sizen="20 0" textsize="2" scale="0.9" text="$FFFGlobal Karma: $O'. $color . $this->karma['global']['votes']['karma'] .'"/>';
		$xml .= '<label posn="38.2 -6.5 0.03" sizen="20 0" textsize="2" scale="0.9" halign="right" text="$FFF'. number_format($this->karma['global']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['global']['votes']['total'] == 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';

		// Local Karma
		$color = '$FFF';
		if ($this->config['karma_calculation_method'] == 'DEFAULT') {
			if ( ($this->karma['local']['votes']['karma'] >= 0) && ($this->karma['local']['votes']['karma'] <= 30) ) {
				$color = '$F00';
			}
			else if ( ($this->karma['local']['votes']['karma'] >= 31) && ($this->karma['local']['votes']['karma'] <= 60) ) {
				$color = '$FF0';
			}
			else if ( ($this->karma['local']['votes']['karma'] >= 61) && ($this->karma['local']['votes']['karma'] <= 100) ) {
				$color = '$0F0';
			}
		}
		$xml .= '<label posn="47.2 -6.5 0.03" sizen="20 0" textsize="2" scale="0.9" text="$FFFLocal Karma: $O'. $color . $this->karma['local']['votes']['karma'] .'"/>';
		$xml .= '<label posn="75.2 -6.5 0.03" sizen="20 0" textsize="2" scale="0.9" halign="right" text="$FFF'. number_format($this->karma['local']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['local']['votes']['total'] == 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';




		// BEGIN: Global vote frame
		$xml .= '<frame posn="3.2 -0.6 0.01">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<label posn="4.7 -11.35 0.03" sizen="3 0" halign="right" scale="0.8" text="100%"/>';
		$xml .= '<quad posn="5.5 -12 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -12 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -14.35 0.03" sizen="3 0" halign="right" scale="0.8" text="90%"/>';
		$xml .= '<quad posn="5.5 -15 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -15 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -17.35 0.03" sizen="3 0" halign="right" scale="0.8" text="80%"/>';
		$xml .= '<quad posn="5.5 -18 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -18 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -20.35 0.03" sizen="3 0" halign="right" scale="0.8" text="70%"/>';
		$xml .= '<quad posn="5.5 -21 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -21 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -23.35 0.03" sizen="3 0" halign="right" scale="0.8" text="60%"/>';
		$xml .= '<quad posn="5.5 -24 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -24 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -26.35 0.03" sizen="3 0" halign="right" scale="0.8" text="50%"/>';
		$xml .= '<quad posn="5.5 -27 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -27 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -29.35 0.03" sizen="3 0" halign="right" scale="0.8" text="40%"/>';
		$xml .= '<quad posn="5.5 -30 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -30 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -32.35 0.03" sizen="3 0" halign="right" scale="0.8" text="30%"/>';
		$xml .= '<quad posn="5.5 -33 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -33 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -35.35 0.03" sizen="3 0" halign="right" scale="0.8" text="20%"/>';
		$xml .= '<quad posn="5.5 -36 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -36 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -38.35 0.03" sizen="3 0" halign="right" scale="0.8" text="10%"/>';
		$xml .= '<quad posn="5.5 -39 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -39 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<quad posn="7.1 -42 0.04" sizen="28 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7 -12 0.03" sizen="0.1 30" bgcolor="FFFD"/>';

		$height['fantastic']	= (($this->karma['global']['votes']['fantastic']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['fantastic']['percent'] / 3.3333333333)) : 0);
		$height['beautiful']	= (($this->karma['global']['votes']['beautiful']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['beautiful']['percent'] / 3.3333333333)) : 0);
		$height['good']		= (($this->karma['global']['votes']['good']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['good']['percent'] / 3.3333333333)) : 0);
		$height['bad']		= (($this->karma['global']['votes']['bad']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['bad']['percent'] / 3.3333333333)) : 0);
		$height['poor']		= (($this->karma['global']['votes']['poor']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['poor']['percent'] / 3.3333333333)) : 0);
		$height['waste']	= (($this->karma['global']['votes']['waste']['percent'] != 0) ? sprintf("%.2f", ($this->karma['global']['votes']['waste']['percent'] / 3.3333333333)) : 0);

		$xml .= '<label posn="10.2 -'. (40 - $height['fantastic']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['fantastic']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="14.7 -'. (40 - $height['beautiful']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['beautiful']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="19.2 -'. (40 - $height['good']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['good']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.02" sizen="4 '. $height['fantastic'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.02" sizen="4 '. $height['beautiful'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.02" sizen="4 '. $height['good'] .'" halign="center" bgcolor="170F"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.03" sizen="4 '. $height['fantastic'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.03" sizen="4 '. $height['beautiful'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.03" sizen="4 '. $height['good'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.035" sizen="4.4 '. (($height['fantastic'] < 3) ? $height['fantastic'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.035" sizen="4.4 '. (($height['beautiful'] < 3) ? $height['beautiful'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.035" sizen="4.4 '. (($height['good'] < 3) ? $height['good'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';

		$xml .= '<label posn="23.7 -'. (40 - $height['bad']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['bad']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="28.2 -'. (40 - $height['poor']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['poor']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="32.7 -'. (40 - $height['waste']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['global']['votes']['waste']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.02" sizen="4 '. $height['bad'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.02" sizen="4 '. $height['poor'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.02" sizen="4 '. $height['waste'] .'" halign="center" bgcolor="701F"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.03" sizen="4 '. $height['bad'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.03" sizen="4 '. $height['poor'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.03" sizen="4 '. $height['waste'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.035" sizen="4.4 '. (($height['bad'] < 3) ? $height['bad'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.035" sizen="4.4 '. (($height['poor'] < 3) ? $height['poor'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.035" sizen="4.4 '. (($height['waste'] < 3) ? $height['waste'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';


		$xml .= '<label posn="3 -43 0.03" sizen="6 0" textcolor="FFFF" text="Votes:"/>';

		$xml .= '<label posn="10 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['fantastic']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="14.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['beautiful']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="19 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['good']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="23.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['bad']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="28 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['poor']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="32.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['global']['votes']['waste']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';

		$xml .= '<label posn="10 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_fantastic']) .'"/>';
		$xml .= '<label posn="14.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_beautiful']) .'"/>';
		$xml .= '<label posn="19 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_good']) .'"/>';
		$xml .= '<label posn="23.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_bad']) .'"/>';
		$xml .= '<label posn="28 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_poor']) .'"/>';
		$xml .= '<label posn="32.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_waste']) .'"/>';

		$xml .= '<label posn="10 -46.05 0.03" sizen="10 0" halign="center" text="$6C0+++"/>';
		$xml .= '<label posn="14.5 -46.05 0.03" sizen="10 0" halign="center" text="$6C0++"/>';
		$xml .= '<label posn="19 -46.05 0.03" sizen="10 0" halign="center" text="$6C0+"/>';
		$xml .= '<label posn="23.5 -46.05 0.03" sizen="10 0" halign="center" text="$F02-"/>';
		$xml .= '<label posn="28 -46.05 0.03" sizen="10 0" halign="center" text="$F02--"/>';
		$xml .= '<label posn="32.5 -46.05 0.03" sizen="10 0" halign="center" text="$F02---"/>';

		$xml .= '</frame>';
		// END: Global vote frame





		// BEGIN: Local vote frame
		$xml .= '<frame posn="40.2 -0.6 0.01">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<label posn="4.7 -11.35 0.03" sizen="3 0" halign="right" scale="0.8" text="100%"/>';
		$xml .= '<quad posn="5.5 -12 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -12 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -14.35 0.03" sizen="3 0" halign="right" scale="0.8" text="90%"/>';
		$xml .= '<quad posn="5.5 -15 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -15 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -17.35 0.03" sizen="3 0" halign="right" scale="0.8" text="80%"/>';
		$xml .= '<quad posn="5.5 -18 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -18 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -20.35 0.03" sizen="3 0" halign="right" scale="0.8" text="70%"/>';
		$xml .= '<quad posn="5.5 -21 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -21 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -23.35 0.03" sizen="3 0" halign="right" scale="0.8" text="60%"/>';
		$xml .= '<quad posn="5.5 -24 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -24 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -26.35 0.03" sizen="3 0" halign="right" scale="0.8" text="50%"/>';
		$xml .= '<quad posn="5.5 -27 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -27 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -29.35 0.03" sizen="3 0" halign="right" scale="0.8" text="40%"/>';
		$xml .= '<quad posn="5.5 -30 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -30 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -32.35 0.03" sizen="3 0" halign="right" scale="0.8" text="30%"/>';
		$xml .= '<quad posn="5.5 -33 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -33 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -35.35 0.03" sizen="3 0" halign="right" scale="0.8" text="20%"/>';
		$xml .= '<quad posn="5.5 -36 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -36 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<label posn="4.7 -38.35 0.03" sizen="3 0" halign="right" scale="0.8" text="10%"/>';
		$xml .= '<quad posn="5.5 -39 0.04" sizen="1.5 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7.1 -39 0.04" sizen="28 0.1" bgcolor="FFF5"/>';

		$xml .= '<quad posn="7.1 -42 0.04" sizen="28 0.1" bgcolor="FFFD"/>';
		$xml .= '<quad posn="7 -12 0.03" sizen="0.1 30" bgcolor="FFFD"/>';

		$height['fantastic']	= (($this->karma['local']['votes']['fantastic']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['fantastic']['percent'] / 3.3333333333)) : 0);
		$height['beautiful']	= (($this->karma['local']['votes']['beautiful']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['beautiful']['percent'] / 3.3333333333)) : 0);
		$height['good']		= (($this->karma['local']['votes']['good']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['good']['percent'] / 3.3333333333)) : 0);
		$height['bad']		= (($this->karma['local']['votes']['bad']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['bad']['percent'] / 3.3333333333)) : 0);
		$height['poor']		= (($this->karma['local']['votes']['poor']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['poor']['percent'] / 3.3333333333)) : 0);
		$height['waste']	= (($this->karma['local']['votes']['waste']['percent'] != 0) ? sprintf("%.2f", ($this->karma['local']['votes']['waste']['percent'] / 3.3333333333)) : 0);

		$xml .= '<label posn="10.2 -'. (40 - $height['fantastic']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['fantastic']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="14.7 -'. (40 - $height['beautiful']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['beautiful']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="19.2 -'. (40 - $height['good']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['good']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.02" sizen="4 '. $height['fantastic'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.02" sizen="4 '. $height['beautiful'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.02" sizen="4 '. $height['good'] .'" halign="center" bgcolor="170F"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.03" sizen="4 '. $height['fantastic'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.03" sizen="4 '. $height['beautiful'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.03" sizen="4 '. $height['good'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';

		$xml .= '<quad posn="10 -'. (42 - $height['fantastic']) .' 0.035" sizen="4.4 '. (($height['fantastic'] < 3) ? $height['fantastic'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="14.5 -'. (42 - $height['beautiful']) .' 0.035" sizen="4.4 '. (($height['beautiful'] < 3) ? $height['beautiful'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="19 -'. (42 - $height['good']) .' 0.035" sizen="4.4 '. (($height['good'] < 3) ? $height['good'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';

		$xml .= '<label posn="23.7 -'. (40 - $height['bad']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['bad']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="28.2 -'. (40 - $height['poor']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['poor']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label posn="32.7 -'. (40 - $height['waste']) .' 0.06" sizen="3.8 0" halign="center" textcolor="FFFF" scale="0.8" text="'. number_format($this->karma['local']['votes']['waste']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.02" sizen="4 '. $height['bad'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.02" sizen="4 '. $height['poor'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.02" sizen="4 '. $height['waste'] .'" halign="center" bgcolor="701F"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.03" sizen="4 '. $height['bad'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.03" sizen="4 '. $height['poor'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.03" sizen="4 '. $height['waste'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';

		$xml .= '<quad posn="23.5 -'. (42 - $height['bad']) .' 0.035" sizen="4.4 '. (($height['bad'] < 3) ? $height['bad'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="28 -'. (42 - $height['poor']) .' 0.035" sizen="4.4 '. (($height['poor'] < 3) ? $height['poor'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';
		$xml .= '<quad posn="32.5 -'. (42 - $height['waste']) .' 0.035" sizen="4.4 '. (($height['waste'] < 3) ? $height['waste'] : 3) .'" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerLine"/>';


		$xml .= '<label posn="3 -43 0.03" sizen="6 0" textcolor="FFFF" text="Votes:"/>';

		$xml .= '<label posn="10 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['fantastic']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="14.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['beautiful']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="19 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['good']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="23.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['bad']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="28 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['poor']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label posn="32.5 -43 0.03" sizen="10 0" halign="center" text="'. number_format($this->karma['local']['votes']['waste']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';

		$xml .= '<label posn="10 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_fantastic']) .'"/>';
		$xml .= '<label posn="14.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_beautiful']) .'"/>';
		$xml .= '<label posn="19 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_good']) .'"/>';
		$xml .= '<label posn="23.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_bad']) .'"/>';
		$xml .= '<label posn="28 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_poor']) .'"/>';
		$xml .= '<label posn="32.5 -45.05 0.03" sizen="10 0" halign="center" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_waste']) .'"/>';

		$xml .= '<label posn="10 -46.05 0.03" sizen="10 0" halign="center" text="$6C0+++"/>';
		$xml .= '<label posn="14.5 -46.05 0.03" sizen="10 0" halign="center" text="$6C0++"/>';
		$xml .= '<label posn="19 -46.05 0.03" sizen="10 0" halign="center" text="$6C0+"/>';
		$xml .= '<label posn="23.5 -46.05 0.03" sizen="10 0" halign="center" text="$F02-"/>';
		$xml .= '<label posn="28 -46.05 0.03" sizen="10 0" halign="center" text="$F02--"/>';
		$xml .= '<label posn="32.5 -46.05 0.03" sizen="10 0" halign="center" text="$F02---"/>';

		$xml .= '</frame>';
		// END: Local vote frame



		// BEGIN: Place Player marker, if Player has already voted
		if ( isset($this->karma['global']['players'][$login]) ) {
			// BEGIN: Global vote frame
			$xml .= '<frame posn="3.2 -48.5 0.02">';
			if ($this->karma['global']['players'][$login]['vote'] == 3) {
				$xml .= '<quad posn="10 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="10 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] == 2) {
				$xml .= '<quad posn="14.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="14.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] == 1) {
				$xml .= '<quad posn="19 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="19 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -1) {
				$xml .= '<quad posn="23.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="23.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -2) {
				$xml .= '<quad posn="28 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="28 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -3) {
				$xml .= '<quad posn="32.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="32.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			$xml .= '</frame>';
			// END: Global vote frame
		}

		if ( isset($this->karma['local']['players'][$login]) ) {
			// BEGIN: Local vote frame
			$xml .= '<frame posn="40.2 -48.5 0.02">';
			if ($this->karma['local']['players'][$login]['vote'] == 3) {
				$xml .= '<quad posn="10 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="10 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] == 2) {
				$xml .= '<quad posn="14.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="14.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] == 1) {
				$xml .= '<quad posn="19 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="19 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] == -1) {
				$xml .= '<quad posn="23.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="23.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] == -2) {
				$xml .= '<quad posn="28 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="28 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] == -3) {
				$xml .= '<quad posn="32.5 0 0.05" sizen="2.8 2.8" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label posn="32.5 -2.5 0.03" sizen="6 0" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			$xml .= '</frame>';
			// END: Local vote frame
		}
		// END: Place Player marker


		// Website-Link Frame
		$xml .= '<frame posn="28.6 -54.5 0.04">';
		$xml .= '<label posn="12 0 0.02" sizen="30 2.6" halign="center" textsize="1" scale="0.8" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $this->karma['data']['uid'] .'&amp;env='. $this->karma['data']['env'] .'&amp;game='. $aseco->server->game .'" text="MORE INFO ON MANIA-KARMA.COM" style="CardButtonMediumWide"/>';
		$xml .= '</frame>';

		$xml .= $this->config['Templates']['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWhoKarmaWindow () {
		global $aseco;

		// Frame for Previous/Next Buttons
		$buttons = '<frame posn="52.05 -53.3 0.04">';

		// Reload button
		$buttons .= '<quad posn="16.65 -1 0.14" sizen="3 3" action="'. $this->config['manialink_id'] .'03" style="Icons64x64_1" substyle="Refresh"/>';

		// Previous button
		$buttons .= '<quad posn="19.95 -1 0.12" sizen="3 3" action="'. $this->config['manialink_id'] .'02" style="Icons64x64_1" substyle="Maximize"/>';
		$buttons .= '<quad posn="20.35 -1.4 0.13" sizen="2.1 2.1" bgcolor="000F"/>';
		$buttons .= '<quad posn="20.15 -1.2 0.14" sizen="2.5 2.5" style="Icons64x64_1" substyle="ShowLeft2"/>';

		// Next button (display only if more pages to display)
		$buttons .= '<quad posn="23.25 -1.15 0.12" sizen="2.8 2.7" style="UIConstructionSimple_Buttons" substyle="Item"/>';
		$buttons .= '</frame>';

		$xml = str_replace(
			array(
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'ManiaKarma who voted what',
				$buttons
			),
			$this->config['Templates']['WINDOW']['HEADER']
		);


		$xml .= '<frame posn="3.2 -6.5 0.05">';
		$xml .= '<format textsize="1" textcolor="FFFF"/>';

		$xml .= '<quad posn="0 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="19.05 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="38.1 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';
		$xml .= '<quad posn="57.15 0.8 0.02" sizen="17.75 46.88" style="BgsPlayerCard" substyle="BgRacePlayerName"/>';

		$players = array();
		foreach ($aseco->server->players->player_list as $player) {
			$players[] = array(
				'id'		=> $player->id,
				'nickname'	=> $this->handleSpecialChars($player->nickname),
				'vote'		=> (($this->karma['global']['players'][$player->login]['vote'] == 0) ? -4 : $this->karma['global']['players'][$player->login]['vote']),
			);
		}

		// Build the arrays for sorting
		$votes = array();
		$ids = array();
		foreach ($players as $key => $row) {
			$votes[$key]	= $row['vote'];
			$ids[$key]	= $row['id'];
		}

		// Sort by Votes and PlayerId
		array_multisort($votes, SORT_NUMERIC, SORT_DESC, $ids, SORT_NUMERIC, SORT_ASC, $players);
		unset($ids, $votes);


		$vote_index = array(
			3	=> '+++',
			2	=> '++',
			1	=> '+',
			-1	=> '-',
			-2	=> '--',
			-3	=> '---',
			-4	=> 'none',	// Normaly 0, but that was bad for sorting...
		);


		$rank = 1;
		$line = 0;
		$offset = 0;
		foreach ($players as $player) {
			$xml .= '<quad posn="'. ($offset + 0.4) .' '. (((1.83 * $line - 0.2) > 0) ? -(1.83 * $line - 0.2) : 0.2) .' 0.03" sizen="16.95 1.83" style="BgsPlayerCard" substyle="BgCardSystem"/>';
			$xml .= '<label posn="'. (1 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="14 1.7" scale="0.9" text="'. $player['nickname'] .'"/>';
			$xml .= '<label posn="'. (16.6 + $offset) .' -'. (1.83 * $line) .' 0.04" sizen="3 1.7" halign="right" scale="0.9" textcolor="FFFF" text="'. $vote_index[$player['vote']] .'"/>';

			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 19.05;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		unset($item);
		$xml .= '</frame>';


		// Website-Link Frame
		$xml .= '<frame posn="28.6 -54.5 0.04">';
		$xml .= '<label posn="12 0 0.02" sizen="30 2.6" halign="center" textsize="1" scale="0.8" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $this->karma['data']['uid'] .'&amp;env='. $this->karma['data']['env'] .'&amp;game='. $aseco->server->game .'" text="MORE INFO ON MANIA-KARMA.COM" style="CardButtonMediumWide"/>';
		$xml .= '</frame>';

		$xml .= $this->config['Templates']['WINDOW']['FOOTER'];
		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPlayerVoteMarker ($player, $gamemode) {

		// Bail out if Player is already disconnected
		if ( !isset($player->login) ) {
			return;
		}

		// Build the colors for Player vote marker (RGBA)
		$preset = array();
		$preset['fantastic']['bgcolor']		= '0000';
		$preset['fantastic']['action']		= 17;
		$preset['beautiful']['bgcolor']		= '0000';
		$preset['beautiful']['action']		= 17;
		$preset['good']['bgcolor']		= '0000';
		$preset['good']['action']		= 17;
		$preset['bad']['bgcolor']		= '0000';
		$preset['bad']['action']		= 17;
		$preset['poor']['bgcolor']		= '0000';
		$preset['poor']['action']		= 17;
		$preset['waste']['bgcolor']		= '0000';
		$preset['waste']['action']		= 17;

		// Fantastic
		if ($this->karma['global']['players'][$player->login]['vote'] == 3) {
			$preset['fantastic']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['fantastic']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['fantastic']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Beautiful
		if ($this->karma['global']['players'][$player->login]['vote'] == 2) {
			$preset['beautiful']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['beautiful']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['beautiful']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Good
		if ($this->karma['global']['players'][$player->login]['vote'] == 1) {
			$preset['good']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['good']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['good']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}


		// Bad
		if ($this->karma['global']['players'][$player->login]['vote'] == -1) {
			$preset['bad']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['bad']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['bad']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Poor
		if ($this->karma['global']['players'][$player->login]['vote'] == -2) {
			$preset['poor']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['poor']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['poor']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Waste
		if ($this->karma['global']['players'][$player->login]['vote'] == -3) {
			$preset['waste']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['waste']['action'] = 18;
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] == 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['waste']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}


		// Init Marker
		$marker = false;


		// Button +++
		if ($preset['fantastic']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="1.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['fantastic']['action'] .'" bgcolor="'. $preset['fantastic']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}

		// Button ++
		if ($preset['beautiful']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="3.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['beautiful']['action'] .'" bgcolor="'. $preset['beautiful']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}

		// Button +
		if ($preset['good']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="5.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['good']['action'] .'" bgcolor="'. $preset['good']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}

		// Button -
		if ($preset['bad']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="7.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['bad']['action'] .'" bgcolor="'. $preset['bad']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}

		// Button --
		if ($preset['poor']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="9.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['poor']['action'] .'" bgcolor="'. $preset['poor']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}

		// Button ---
		if ($preset['waste']['bgcolor'] != '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame posn="11.83 -8.5 1">';
			$marker .= '<quad posn="0.2 -0.08 0.3" sizen="1.8 1.4" action="'. $this->config['manialink_id'] . $preset['waste']['action'] .'" bgcolor="'. $preset['waste']['bgcolor'] .'"/>';
			$marker .= '</frame>';
		}


		$xml = '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker">';

		// Send/Build MainWidget Frame only when required, if empty then the player can vote
		if ($marker != false) {
			$xml .= '<frame posn="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .' 10" id="'. $this->config['manialink_id'] .'04MainFrame">';
			$xml .= $marker;
			$xml .= '</frame>';
$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPLv3
 * ----------------------------------
 */
main () {
	declare CMlControl Container <=> (Page.GetFirstChild(Page.MainFrame.ControlId ^ "MainFrame") as CMlFrame);
	Container.RelativeScale = {$this->config['widget']['states'][$gamemode]['scale']};
}
--></script>
EOL;
			$xml .= $maniascript;

		}
		$xml .= '</manialink>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendConnectionStatus ($status = true, $gamemode) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialink_id'] .'06" name="ConnectionStatus">';
		if ($status === false) {
			$this->sendLoadingIndicator(false, $gamemode);
			$xml .= '<frame posn="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .' 20">';
			$xml .= '<quad posn="0.5 -5.2 0.9" sizen="1.4 1.4" style="Icons64x64_2" substyle="Disconnected" id="ManiaKarmaTooltipIcon" ScriptEvents="1"/>';
			$xml .= '<label posn="-0.4 -5.2 0.9" sizen="20 1.4" textsize="1" halign="right" scale="0.8" text="Connection failed, retrying shortly." hidden="true" id="ManiaKarmaTooltipMessage"/>';
			$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPL3
 * ----------------------------------
 */
main () {
	declare LabelTooltip <=> (Page.GetFirstChild("ManiaKarmaTooltipMessage") as CMlLabel);

	while (True) {
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseOut: {
					if (Event.ControlId == "ManiaKarmaTooltipIcon") {
						LabelTooltip.Visible = False;
					}
				}
				case CMlEvent::Type::MouseOver: {
					if (Event.ControlId == "ManiaKarmaTooltipIcon") {
						LabelTooltip.Visible = True;
					}
				}

			}
		}
		yield;
	}
}
--></script>
EOL;
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, false, 0, false);
	}


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendLoadingIndicator ($status = true, $gamemode) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialink_id'] .'07" name="LoadingIndicator">';
		if ($status === true) {
			$xml .= '<frame posn="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .' 20">';
			$xml .= '<quad posn="0.5 -5.2 0.9" sizen="1.4 1.4" image="'. $this->config['images']['progress_indicator'] .'" id="ManiaKarmaTooltipIcon" ScriptEvents="1"/>';
			$xml .= '<label posn="-0.4 -5.2 0.9" sizen="20 1.4" textsize="1" halign="right" scale="0.8" text="Loading global votes." hidden="true" id="ManiaKarmaTooltipMessage"/>';
			$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPL3
 * ----------------------------------
 */
main () {
	declare LabelTooltip <=> (Page.GetFirstChild("ManiaKarmaTooltipMessage") as CMlLabel);

	while (True) {
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseOut: {
					if (Event.ControlId == "ManiaKarmaTooltipIcon") {
						LabelTooltip.Visible = False;
					}
				}
				case CMlEvent::Type::MouseOver: {
					if (Event.ControlId == "ManiaKarmaTooltipIcon") {
						LabelTooltip.Visible = True;
					}
				}

			}
		}
		yield;
	}
}
--></script>
EOL;
			$xml .= $maniascript;
		}
		$xml .= '</manialink>';
		$aseco->sendManialink($xml, false, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handlePlayerVote ($player, $vote) {
		global $aseco;

		// Do nothing at Startup!!
		if ($aseco->startup_phase == true) {
			return;
		}


		// Close reminder-window if there is one for this Player
		$this->closeReminderWindow($player);


//		// $vote is "0" when the Player clicks on a red (no vote possible) or blue marked (same vote) button,
//		// in both situation we bail out now.
//		if ($vote == 0) {
//			return;
//		}


		// Check if finishes are required
		if ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] > $this->getPlayerData($player, 'FinishedMapCount')) ) {

			// Show chat message
			$message = $aseco->formatText($this->config['messages']['karma_require_finish'],
						$this->config['require_finish'],
						($this->config['require_finish'] == 1 ? '' : 's')
			);
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) && ($this->config['widget']['current_state'] != 0) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
			return;
		}


		// Before call the remote API, check if player has the same already voted
		if ($this->karma['global']['players'][$player->login]['vote'] == $vote) {
			// Same vote, does not need to call remote API, bail out immediately
			$message = $this->config['messages']['karma_voted'];
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
			return;
		}


		// Store the new vote for send them later with "MultiVote",
		// but only if the global is different to the current vote
		if ( (isset($this->karma['global']['players'][$player->login]['vote'])) && ($this->karma['global']['players'][$player->login]['vote'] != $vote) ) {
			$this->karma['new']['players'][$player->login] = $vote;
		}


	//	// Check if connection was failed
	//	if ($this->config['retrytime'] == 0) {
			// Remove the previous global Vote
			if ( isset($this->karma['global']['players'][$player->login]['vote']) ) {
				switch ($this->karma['global']['players'][$player->login]['vote']) {
					case 3:
						$this->karma['global']['votes']['fantastic']['count'] -= 1;
						break;
					case 2:
						$this->karma['global']['votes']['beautiful']['count'] -= 1;
						break;
					case 1:
						$this->karma['global']['votes']['good']['count'] -= 1;
						break;
					case -1:
						$this->karma['global']['votes']['bad']['count'] -= 1;
						break;
					case -2:
						$this->karma['global']['votes']['poor']['count'] -= 1;
						break;
					case -3:
						$this->karma['global']['votes']['waste']['count'] -= 1;
						break;
					default:
						// Do nothing
						break;
				}

				// Store previous vote
				$this->karma['global']['players'][$player->login]['previous'] = $this->karma['global']['players'][$player->login]['vote'];
			}
			else {
				// Set state "no previous vote"
				$this->karma['global']['players'][$player->login]['previous'] = 0;
			}
	//	}

		// Remove the previous local Vote
		if ( isset($this->karma['local']['players'][$player->login]['vote']) ) {
			switch ($this->karma['local']['players'][$player->login]['vote']) {
				case 3:
					$this->karma['local']['votes']['fantastic']['count'] -= 1;
					break;
				case 2:
					$this->karma['local']['votes']['beautiful']['count'] -= 1;
					break;
				case 1:
					$this->karma['local']['votes']['good']['count'] -= 1;
					break;
				case -1:
					$this->karma['local']['votes']['bad']['count'] -= 1;
					break;
				case -2:
					$this->karma['local']['votes']['poor']['count'] -= 1;
					break;
				case -3:
					$this->karma['local']['votes']['waste']['count'] -= 1;
					break;
				default:
					// Do nothing
					break;
			}
		}

	//	// Check if connection was failed, and store the current Vote (only local or both)
	//	if ($this->config['retrytime'] == 0) {
			$this->karma['global']['players'][$player->login]['vote'] = $vote;
	//	}
		$this->karma['local']['players'][$player->login]['vote'] = $vote;


	//	// Check if connection was failed
	//	if ($this->config['retrytime'] == 0) {
			// Add the new Vote into the counts (global/local)
			switch ($vote) {
				case 3:
					$this->karma['global']['votes']['fantastic']['count'] += 1;
					$this->karma['local']['votes']['fantastic']['count'] += 1;
					break;
				case 2:
					$this->karma['global']['votes']['beautiful']['count'] += 1;
					$this->karma['local']['votes']['beautiful']['count'] += 1;
					break;
				case 1:
					$this->karma['global']['votes']['good']['count'] += 1;
					$this->karma['local']['votes']['good']['count'] += 1;
					break;
				case -1:
					$this->karma['global']['votes']['bad']['count'] += 1;
					$this->karma['local']['votes']['bad']['count'] += 1;
					break;
				case -2:
					$this->karma['global']['votes']['poor']['count'] += 1;
					$this->karma['local']['votes']['poor']['count'] += 1;
					break;
				case -3:
					$this->karma['global']['votes']['waste']['count'] += 1;
					$this->karma['local']['votes']['waste']['count'] += 1;
					break;
				default:
					// Do nothing
					break;
			}
	//	}
	//	else {
	//		// Add the new Vote into the counts (only local)
	//		switch ($vote) {
	//			case 3:
	//				$this->karma['local']['votes']['fantastic']['count'] += 1;
	//				break;
	//			case 2:
	//				$this->karma['local']['votes']['beautiful']['count'] += 1;
	//				break;
	//			case 1:
	//				$this->karma['local']['votes']['good']['count'] += 1;
	//				break;
	//			case -1:
	//				$this->karma['local']['votes']['bad']['count'] += 1;
	//				break;
	//			case -2:
	//				$this->karma['local']['votes']['poor']['count'] += 1;
	//				break;
	//			case -3:
	//				$this->karma['local']['votes']['waste']['count'] += 1;
	//				break;
	//			default:
	//				// Do nothing
	//				break;
	//		}
	//	}


	//	// Check if connection was failed
	//	if ($this->config['retrytime'] == 0) {
			// Update the global/local $this->karma
			$this->calculateKarma(array('global','local'));
	//	}
	//	else {
	//		// Update only the local $this->karma
	//		$this->calculateKarma(array('local'));
	//	}


		// Show the MX-Link-Window (if enabled and we are at Score)
		if ($this->config['score_mx_window'] == true) {
			$this->showManiaExchangeLinkWindow($player);
		}


		// Tell the player the result for his/her vote
		if ($this->karma['global']['players'][$player->login]['previous'] == 0) {
			$message = $aseco->formatText($this->config['messages']['karma_done'], $aseco->stripColors($aseco->server->maps->current->name) );
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}

		}
		else if ($this->karma['global']['players'][$player->login]['previous'] != $vote) {
			$message = $aseco->formatText($this->config['messages']['karma_change'], $aseco->stripColors($aseco->server->maps->current->name) );
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// Show Map Karma (with details?)
		$message = $this->createKarmaMessage($player->login, false);
		if ($message != false) {
			if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// Update the KarmaWidget for given Player
		$this->sendWidgetCombination(array('player_marker'), $player);


		// Should all other player (except the vote given player) be informed/asked?
		if ($this->config['show_player_vote_public'] == true) {
			$logins = array();
			foreach ($aseco->server->players->player_list as $pl) {

				// Don't ask/tell the player that give the vote
				if ($pl->login == $player->login) {
					continue;
				}

				// Don't ask/tell Players they did not reached the <require_finish> limit
				if ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] > $this->getPlayerData($pl, 'FinishedMapCount')) ) {
					continue;
				}

				// Don't ask/tell players if she/he has already voted!
				if ($this->karma['global']['players'][$pl->login]['vote'] != 0) {
					continue;
				}

				// Don't ask/tell Spectator's
				if ($pl->isspectator) {
					continue;
				}

				// All other becomes this message.
				$logins[] = $pl->login;
			}

			// Build the message and send out
			if ($vote == 1) {
				$player_voted = $this->config['messages']['karma_good'];
			}
			else if ($vote == 2) {
				$player_voted = $this->config['messages']['karma_beautiful'];
			}
			else if ($vote == 3) {
				$player_voted = $this->config['messages']['karma_fantastic'];
			}
			else if ($vote == -1) {
				$player_voted = $this->config['messages']['karma_bad'];
			}
			else if ($vote == -2) {
				$player_voted = $this->config['messages']['karma_poor'];
			}
			else if ($vote == -3) {
				$player_voted = $this->config['messages']['karma_waste'];
			}
			$message = $aseco->formatText($this->config['messages']['karma_show_opinion'],
					$aseco->stripColors($player->nickname),
					$player_voted
			);
			$message = str_replace('{br}', LF, $message);  // split long message
			$aseco->sendChatMessage($message, implode(',', $logins));
			unset($logins);
		}


		// Release a KarmaChange Event
		$aseco->releaseEvent('onKarmaChange',
			array(
				'Karma'			=> $this->karma['global']['votes']['karma'],
				'Total'			=> $this->karma['global']['votes']['total'],
				'FantasticCount'	=> $this->karma['global']['votes']['fantastic']['count'],
				'FantasticPercent'	=> $this->karma['global']['votes']['fantastic']['percent'],
				'BeautifulCount'	=> $this->karma['global']['votes']['beautiful']['count'],
				'BeautifulPercent'	=> $this->karma['global']['votes']['beautiful']['percent'],
				'GoodCount'		=> $this->karma['global']['votes']['good']['count'],
				'GoodPercent'		=> $this->karma['global']['votes']['good']['percent'],
				'BadCount'		=> $this->karma['global']['votes']['bad']['count'],
				'BadPercent'		=> $this->karma['global']['votes']['bad']['percent'],
				'PoorCount'		=> $this->karma['global']['votes']['poor']['count'],
				'PoorPercent'		=> $this->karma['global']['votes']['poor']['percent'],
				'WasteCount'		=> $this->karma['global']['votes']['waste']['count'],
				'WastePercent'		=> $this->karma['global']['votes']['waste']['percent'],
			)
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendMapKarmaMessage ($login) {
		global $aseco;

		// Create message
		$message = $this->createKarmaMessage($login, false);

		// Show message
		if ($message != false) {
			if ($login) {
				if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
					if ($player = $aseco->server->players->getPlayer($login)) {
						send_window_message($aseco, $message, $player);
					}
				}
				else {
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				if ( ($this->config['messages_in_window'] == true) && (function_exists('send_window_message')) ) {
					send_window_message($aseco, $message, false);
				}
				else {
					$aseco->sendChatMessage($message);
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function createKarmaMessage ($login, $force_display = false) {
		global $aseco;

		// Init
		$message = false;

		// Show default Karma message
		if ( ($this->config['show_karma'] == true) || ($force_display == true) ) {
			$message = $aseco->formatText($this->config['messages']['karma_message'],
				$aseco->server->maps->current->name_stripped,
				$this->karma['global']['votes']['karma']
			);
		}

		// Optionally show player's actual vote
		if ( ($this->config['show_votes'] == true) || ($force_display == true) ) {
			if ($this->karma['global']['players'][$login]['vote'] == 1) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_good'], '/+');
			}
			else if ($this->karma['global']['players'][$login]['vote'] == 2) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_beautiful'], '/++');
			}
			else if ($this->karma['global']['players'][$login]['vote'] == 3) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_fantastic'], '/+++');
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -1) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_bad'], '/-');
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -2) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_poor'], '/--');
			}
			else if ($this->karma['global']['players'][$login]['vote'] == -3) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_waste'], '/---');
			}
			else {
				$message .= $this->config['messages']['karma_not_voted'];
			}

		}

		// Optionally show vote counts & percentages
		if ( ($this->config['show_details'] == true) || ($force_display == true) ) {
			$message .= $aseco->formatText(LF. $this->config['messages']['karma_details'],
				$this->karma['global']['votes']['karma'],
				$this->karma['global']['votes']['fantastic']['percent'],	$this->karma['global']['votes']['fantastic']['count'],
				$this->karma['global']['votes']['beautiful']['percent'],	$this->karma['global']['votes']['beautiful']['count'],
				$this->karma['global']['votes']['good']['percent'],		$this->karma['global']['votes']['good']['count'],
				$this->karma['global']['votes']['bad']['percent'],		$this->karma['global']['votes']['bad']['count'],
				$this->karma['global']['votes']['poor']['percent'],		$this->karma['global']['votes']['poor']['count'],
				$this->karma['global']['votes']['waste']['percent'],		$this->karma['global']['votes']['waste']['count']
			);
		}

		return $message;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// This shows the undecided message to other players.
	public function showUndecidedMessage ($command) {
		global $aseco;

		// Should all other player (except the vote given player) be informed/asked?
		if ($this->config['show_player_vote_public'] == true) {
			foreach ($aseco->server->players->player_list as $player) {

				// Show only to players that did not voted yes
				if ($this->karma['global']['players'][$player->login]['vote'] == 0) {
					// Don't ask/tell the player that give the vote
					if ($player->login == $command['author']->login) {
						continue;
					}

					// Don't ask/tell Spectator's
					if ($player->isspectator) {
						continue;
					}

					$message = $aseco->formatText($this->config['messages']['karma_show_undecided'],
							$aseco->stripColors($command['author']->nickname)
					);
					$message = str_replace('{br}', LF, $message);  // split long message
					$aseco->sendChatMessage($message, $player->login);
				}
			}
		}

		// Close reminder-window if there is one for this Player
		if ($this->getPlayerData($command['author'], 'ReminderWindow') == true) {
			$this->closeReminderWindow($command['author']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// This shows the reminder window to the given Players (comma seperated list)
	public function showReminderWindow ($players) {
		global $aseco;

		$gamestate = 'race';
		if ($this->config['widget']['current_state'] == 0) {
			$gamestate = 'score';
		}

		$content = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow">';
		$content .= '<frame posn="'. $this->config['reminder_window'][$gamestate]['pos_x'] .' '. $this->config['reminder_window'][$gamestate]['pos_y'] .' 2">';
		$content .= '<quad posn="0 1 0" sizen="81.8 6.3" style="Bgs1InRace" substyle="BgTitle2"/>';
		$content .= '<label posn="16.5 -0.8 1" sizen="18 1.8" textsize="2" scale="0.8" halign="right" text="$000'. $this->config['messages']['karma_reminder_at_score'] .'"/>';
		$content .= '<label posn="16.5 -2.5 1" sizen="18 0.2" textsize="1" scale="0.8" halign="right" text="$555powered by mania-karma.com"/>';

		$content .= '<frame posn="19.2 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'12" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0.02" sizen="7.5 2.8" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_fantastic']) .'"/>';
		$content .= '<label posn="4 -1.8 0.02" sizen="10 2.8" halign="center" textsize="1" text="$390+++"/>';
		$content .= '</frame>';

		$content .= '<frame posn="27.9 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'11" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_beautiful']) .'"/>';
		$content .= '<label posn="4 -1.8 0" sizen="10 2.8" halign="center" textsize="1" text="$390++"/>';
		$content .= '</frame>';

		$content .= '<frame posn="36.6 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'10" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_good']) .'"/>';
		$content .= '<label posn="4 -1.8 0" sizen="10 2.8" halign="center" textsize="1" text="$390+"/>';
		$content .= '</frame>';

		$content .= '<frame posn="45.3 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'13" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$888'. strtoupper($this->config['messages']['karma_undecided']) .'"/>';
		$content .= '<label posn="4 -2 0" sizen="10 2.8" halign="center" textsize="1" scale="0.7" text="$888???"/>';
		$content .= '</frame>';

		$content .= '<frame posn="54 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'14" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_bad']) .'"/>';
		$content .= '<label posn="4 -1.7 0" sizen="14 2.8" halign="center" textsize="1" text="$D02-"/>';
		$content .= '</frame>';

		$content .= '<frame posn="62.7 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'15" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_poor']) .'"/>';
		$content .= '<label posn="4 -1.7 0" sizen="14 2.8" halign="center" textsize="1" text="$D02--"/>';
		$content .= '</frame>';

		$content .= '<frame posn="71.4 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" action="'. $this->config['manialink_id'] .'16" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0" sizen="7.5 2.8" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_waste']) .'"/>';
		$content .= '<label posn="4 -1.7 0" sizen="14 2.8" halign="center" textsize="1" text="$D02---"/>';
		$content .= '</frame>';

		$content .= '</frame>';
		$content .= '</manialink>';

		$aseco->sendManialink($content, $players, 0, true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// This shows the MX-Link Window to the given Player
	public function showManiaExchangeLinkWindow ($player) {
		global $aseco;

		// Bail out immediately if not at Score
		if ($this->config['widget']['current_state'] != 0) {
			return;
		}

		// Find the Player vote
		switch ($this->karma['global']['players'][$player->login]['vote']) {
			case 3:
				$voted = '$390'. ucfirst($this->config['messages']['karma_fantastic']);
				$cmd = '$390+++';
				break;
			case 2:
				$voted = '$390'. ucfirst($this->config['messages']['karma_beautiful']);
				$cmd = '$390++';
				break;
			case 1:
				$voted = '$390'. ucfirst($this->config['messages']['karma_good']);
				$cmd = '$390+';
				break;
			case -1:
				$voted = '$D02'. ucfirst($this->config['messages']['karma_bad']);
				$cmd = '$D02-';
				break;
			case -2:
				$voted = '$D02'. ucfirst($this->config['messages']['karma_poor']);
				$cmd = '$D02--';
				break;
			case -3:
				$voted = '$D02'. ucfirst($this->config['messages']['karma_waste']);
				$cmd = '$D02---';
				break;
		}

		$gamestate = 'race';
		if ($this->config['widget']['current_state'] == 0) {
			$gamestate = 'score';
		}

		$content = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow">';
		$content .= '<frame posn="'. $this->config['reminder_window'][$gamestate]['pos_x'] .' '. $this->config['reminder_window'][$gamestate]['pos_y'] .' 2">';
		$content .= '<quad posn="0 1 0" sizen="81.8 6.3" style="Bgs1InRace" substyle="BgTitle2"/>';
		$content .= '<label posn="16.5 -0.8 1" sizen="18 1.8" textsize="2" scale="0.8" halign="right" text="$000'. $this->config['messages']['karma_you_have_voted'] .'"/>';
		$content .= '<label posn="16.5 -2.5 1" sizen="18 0.2" textsize="1" scale="0.8" halign="right" text="$555powered by mania-karma.com"/>';

		$content .= '<frame posn="19.2 -0.45 0.01">';
		$content .= '<quad posn="0 -0.2 0" sizen="8.01 3.01" image="'. $this->config['images']['button_normal'] .'" imagefocus="'. $this->config['images']['button_focus'] .'"/>';
		$content .= '<label posn="4 -0.5 0.02" sizen="7.5 2.8" textsize="1" halign="center" text="'. $voted .'"/>';
		$content .= '<label posn="4 -1.8 0.02" sizen="10 2.8" textsize="1" halign="center" text="'. $cmd .'"/>';
		$content .= '</frame>';

		if ( isset($aseco->server->maps->current->mx->pageurl) ) {
			// Show link direct to the last map
			$content .= '<frame posn="33 -0.2 1">';
			$content .= '<label posn="40.5 -1.3 0" sizen="50 0" halign="right" textsize="1" text="$000Visit &#187; '. preg_replace('/\$S/i', '', $aseco->server->maps->current->name) .'$Z$000 &#171; at"/>';
			$content .= '<quad posn="41.25 0.08 0" sizen="4 4" image="'. $this->config['images']['mx_logo_normal'] .'" imagefocus="'. $this->config['images']['mx_logo_focus'] .'" url="'. preg_replace('/(&)/', '&amp;', $aseco->server->maps->current->mx->pageurl) .'"/>';
			$content .= '</frame>';
		}
		else {
			// Show link to tm.mania-exchange.com
			$content .= '<frame posn="33 -0.2 1">';
			$content .= '<quad posn="41.25 0.08 0" sizen="4 4" image="'. $this->config['images']['mx_logo_normal'] .'" imagefocus="'. $this->config['images']['mx_logo_focus'] .'" url="http://tm.mania-exchange.com/"/>';
			$content .= '</frame>';
		}

		$content .= '</frame>';
		$content .= '</manialink>';

		$aseco->sendManialink($content, $player->login, 0, false);
		$this->storePlayerData($player, 'ReminderWindow', true);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// This close the reminder window from given Player or all Players
	public function closeReminderWindow ($player = false) {
		global $aseco;

		// If there no players, bail out immediately
		if (count($aseco->server->players->player_list) == 0) {
			return;
		}

		// Build the Manialink
		$xml = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow"></manialink>';

		if ($player != false) {
			if ($this->getPlayerData($player, 'ReminderWindow') == true) {
				$aseco->sendManialink($xml, $player->login, 0, false);
				$this->storePlayerData($player, 'ReminderWindow', false);
			}
		}
		else {
			// Reset state at all Players
			foreach ($aseco->server->players->player_list as $player) {
				if ($this->getPlayerData($player, 'ReminderWindow') == true) {
					$this->storePlayerData($player, 'ReminderWindow', false);
				}
			}

			// Send manialink to all Player
			$aseco->sendManialink($xml, false, 0, false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function sendHelpAboutWindow ($login, $message) {
		global $aseco;

		$buttons = '';
		$xml .= str_replace(
			array(
				'%window_title%',
				'%prev_next_buttons%'
			),
			array(
				'ManiaKarma/'. $this->getVersion() .' for UASECO',
				$buttons
			),
			$this->config['Templates']['WINDOW']['HEADER']
		);

		$xml = '<frame posn="3 -6 0">';
		$xml .= '<quad posn="54 4 0.05" sizen="23 23" image="'. $this->config['images']['maniakarma_logo'] .'" url="http://www.mania-karma.com"/>';
		$xml .= '<label posn="0 0 0.05" sizen="57 0" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		$xml .= '</frame>';

		// Website-Link Frame
		$xml .= '<frame posn="28.6 -54.5 0.04">';
		$xml .= '<label posn="12 0 0.02" sizen="30 2.6" halign="center" textsize="1" scale="0.8" url="http://'. $this->config['urls']['website'] .'" text="VISIT MANIA-KARMA.COM" style="CardButtonMediumWide"/>';
		$xml .= '</frame>';

		$xml .= $this->config['Templates']['WINDOW']['FOOTER'];
		$aseco->sendManialink($xml, $login, 0, false);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function storeKarmaVotes () {
		global $aseco;

		// Send the new vote from the last Map to the central database and store them local (if enabled)
		if ( (isset($this->karma['new']['players'])) && (count($this->karma['new']['players']) > 0) ) {

			// Check if connection was failed
			if ( ($this->config['retrytime'] > 0) && (time() >= $this->config['retrytime']) ) {
				// Reconnect to the database
				$this->onSync($aseco);
			}

			if ($this->config['retrytime'] == 0) {

				// Check for all required parameters for an remote API Call
				if ( (empty($this->karma['data']['uid'])) || (empty($this->karma['data']['name'])) || (empty($this->karma['data']['author'])) || (empty($this->karma['data']['env'])) ) {
					$aseco->console('[ManiaKarma] Could not do a remote API Call "Vote", one of the required parameter missed! uid:'. $this->karma['data']['uid'] .' name:'. $this->karma['data']['name'] .' author:'. $this->karma['data']['author'] .' env:'. $this->karma['data']['env']);
					return;
				}

				// Build the Player/Vote pairs
				$pairs = array();
				foreach ($this->karma['new']['players'] as $login => $vote) {
					$pairs[] = urlencode($login) .'='. $vote;

				}


				// Generate the url for this Votes
				$authortime = 0;
				$authorscore = 0;
				if ($aseco->server->gameinfo->mode == Gameinfo::STUNTS) {
					$authorscore = $aseco->server->maps->current->author_score;
				}
				else {
					$authortime = $aseco->server->maps->current->author_time;
				}
				$api_url = sprintf("%s?Action=Vote&login=%s&authcode=%s&uid=%s&map=%s&author=%s&atime=%s&ascore=%s&nblaps=%s&nbchecks=%s&mood=%s&env=%s&votes=%s&tmx=%s",
					$this->config['urls']['api'],
					urlencode( $this->config['account']['login'] ),
					urlencode( $this->config['account']['authcode'] ),
					urlencode( $this->karma['data']['uid'] ),
					base64_encode( $this->karma['data']['name'] ),
					urlencode( $this->karma['data']['author'] ),
					$authortime,
					$authorscore,
					urlencode( $aseco->server->maps->current->nblaps ),
					urlencode( $aseco->server->maps->current->nbcheckpoints ),
					urlencode( $aseco->server->maps->current->mood ),
					urlencode( $this->karma['data']['env'] ),
					implode('|', $pairs),
					$this->karma['data']['tmx']
				);

				// Start an async VOTE request
				$aseco->webaccess->request($api_url, array(array($this, 'handleWebaccess'), 'VOTE', $api_url), 'none', false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
			}


			// Check if karma should saved local also
			if ($this->config['save_karma_also_local'] == true) {

				$logins = array();
				foreach ($this->karma['new']['players'] as $login => $vote) {
					$logins[] = "'". $login ."'";
				}

				$query = "
				SELECT
					`p`.`Login`,
					`k`.`PlayerId`,
					`k`.`MapId`
				FROM `%prefix%ratings` AS `k`
				LEFT JOIN `%prefix%players` AS `p` ON `p`.`PlayerId`=`k`.`PlayerId`
				WHERE `p`.`Login` IN (". implode(',', $logins) .")
				AND `k`.`MapId`='". $this->karma['data']['id'] ."';
				";

				$updated = array();
				$res = $aseco->db->query($query);
				if ($res) {
					if ($res->num_rows > 0) {
						while ($row = $res->fetch_object()) {
							if ($row->MapId > 0) {
								$query2 = "UPDATE `%prefix%ratings`SET `Score`='". $this->karma['new']['players'][$row->Login] ."' WHERE `MapId`='". $row->MapId ."' AND `PlayerId`='". $row->PlayerId ."';";
								$result = $aseco->db->query($query2);
								if (!$result) {
									$aseco->console('[ManiaKarma] Could not UPDATE karma vote for "'. $row->Login .'" [for statement "'. $query2 .'"]!');
								}
							}

							// Mark for Updated
							$updated[$row->Login] = true;
						}
					}
					$res->free_result();
				}

				// INSERT all other Player they did not vote before
				$query2 = "INSERT INTO `%prefix%ratings` (`Score`, `PlayerId`, `MapId`) VALUES ";
				$values = array();
				foreach ($this->karma['new']['players'] as $login => $vote) {
					if ( !isset($updated[$login]) ) {
						$playerid = $aseco->server->players->getPlayerId($login);
						if ($playerid > 0) {
							// Add only Players with an PlayerId
							$values[] = "('". $vote ."', '". $playerid ."', '". $this->karma['data']['id'] ."')";
						}
					}
				}

				if (count($values) > 0) {
					$result = $aseco->db->query($query2 . implode(',', $values));
					if (!$result) {
						$aseco->console('[ManiaKarma] Could not INSERT karma votes... [for statement "'. $query2 . implode(',', $values) .'"]!');
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function syncGlobaAndLocalVotes ($source, $setup_global = false) {
		global $aseco;

		// Switch source and destination if required
		$destination = 'local';
		if ($source == 'local') {
			$destination = 'global';
		}

		// Bailout if unset
		if ( !isset($this->karma[$source]['players']) ) {
			return;
		}


		$found = false;
		foreach ($this->karma[$source]['players'] as $login => $votes) {
			// Skip "no vote" (value "0") from sync
			if ($votes['vote'] == 0) {
				continue;
			}

			// Is the votes are different, then replace $source with the $destination vote
			if ( (isset($this->karma[$destination]['players'][$login])) && ($this->karma[$destination]['players'][$login]['vote'] != $votes['vote']) ) {

				// Set to true to rebuild the $destination Karma and the Widget (Cups/Values)
				$found = true;

				// Set the $destination to the $source vote
				$this->karma[$destination]['players'][$login]['vote'] = $votes['vote'];

				// Set the sync'd vote as a new vote to store them into the database at onBeginMap
				if ($setup_global === true) {
					$this->karma['new']['players'][$login] = $votes['vote'];
				}

				// Count the vote too
				switch ($votes['vote']) {
					case 3:
						$this->karma[$destination]['votes']['fantastic']['count'] += 1;
						break;
					case 2:
						$this->karma[$destination]['votes']['beautiful']['count'] += 1;
						break;
					case 1:
						$this->karma[$destination]['votes']['good']['count'] += 1;
						break;
					case -1:
						$this->karma[$destination]['votes']['bad']['count'] += 1;
						break;
					case -2:
						$this->karma[$destination]['votes']['poor']['count'] += 1;
						break;
					case -3:
						$this->karma[$destination]['votes']['waste']['count'] += 1;
						break;
					default:
						// Do nothing
						break;
				}
			}
		}

		if ($found == true) {
			// Update the $destination $this->karma
			$this->calculateKarma(array($destination));

			// Update the KarmaWidget for all Players
			$this->sendWidgetCombination(array('cups_values'), false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleGetApiCall ($map, $target = false) {
		global $aseco;

		// If there no players, bail out immediately
		if (count($aseco->server->players->player_list) == 0) {
			$this->sendConnectionStatus(true, $this->config['widget']['current_state']);
			$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
			return;
		}

		// Bail out if map id was not found
		if ($map->id === 0) {
			$this->sendConnectionStatus(true, $this->config['widget']['current_state']);
			$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
			return;
		}

		// Check if connection was failed and try to reconnect
		if ( ($this->config['retrytime'] > 0) && (time() >= $this->config['retrytime']) ) {
			// Reconnect to the database
			$this->onSync($aseco);
		}

		if ($this->config['retrytime'] > 0) {
			// Connect failed, try again later
			return;
		}

		// Check for all required parameters for an remote API Call
		if ( (empty($map->uid)) || (empty($map->name)) || (empty($map->author)) || (empty($map->environment)) ) {
			$aseco->console('[ManiaKarma] Could not do a remote API Call "Get", one of the required parameter missed! uid:'. $map->uid .' name:'. $map->name .' author:'. $map->author .' env:'. $map->environment);
			return;
		}

		$players = array();
		if ($target !== false) {
			// Get Karma for ONE Player
			$player_list = array($target);
		}
		else {
			// Get Karma for ALL Players
			$player_list = $aseco->server->players->player_list;
		}
		foreach ($player_list as $player) {
			$players[] = urlencode($player->login);
		}

		// Generate the url for this Map-Karma-Request
		$api_url = sprintf("%s?Action=Get&login=%s&authcode=%s&uid=%s&map=%s&author=%s&env=%s&player=%s",
			$this->config['urls']['api'],
			urlencode( $this->config['account']['login'] ),
			urlencode( $this->config['account']['authcode'] ),
			urlencode( $map->uid ),
			base64_encode( $map->name ),
			urlencode( $map->author ),
			urlencode( $map->environment ),
			implode('|', $players)					// Already Url-Encoded
		);

		// Start an async GET request
		$aseco->webaccess->request($api_url, array(array($this, 'handleWebaccess'), 'GET', $api_url, $target), 'none', false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);

		// Return an empty set, get replaced with handleWebaccess()
		return;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleWebaccess ($response, $type, $url, $target = false) {
		global $aseco;

		if (empty($response['Error'])) {
			if ($response['Code'] == 200) {
				if ($type == 'GET') {
					// Read the response
					if (!$xml = @simplexml_load_string($response['Message'], null, LIBXML_COMPACT) ) {
						$aseco->console('[ManiaKarma] handleWebaccess() on type "'. $type .'": Could not read/parse response from mania-karma.com "'. $response['Message'] .'"!');
						$this->config['retrytime'] = (time() + $this->config['retrywait']);
						$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
						$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
					}
					else {
						if ($xml->status == 200) {
							$this->sendConnectionStatus(true, $this->config['widget']['current_state']);

							$this->karma['global']['votes']['fantastic']['percent']	= (float)$xml->votes->fantastic['percent'];
							$this->karma['global']['votes']['fantastic']['count']		= (int)$xml->votes->fantastic['count'];
							$this->karma['global']['votes']['beautiful']['percent']	= (float)$xml->votes->beautiful['percent'];
							$this->karma['global']['votes']['beautiful']['count']		= (int)$xml->votes->beautiful['count'];
							$this->karma['global']['votes']['good']['percent']		= (float)$xml->votes->good['percent'];
							$this->karma['global']['votes']['good']['count']		= (int)$xml->votes->good['count'];

							$this->karma['global']['votes']['bad']['percent']		= (float)$xml->votes->bad['percent'];
							$this->karma['global']['votes']['bad']['count']		= (int)$xml->votes->bad['count'];
							$this->karma['global']['votes']['poor']['percent']		= (float)$xml->votes->poor['percent'];
							$this->karma['global']['votes']['poor']['count']		= (int)$xml->votes->poor['count'];
							$this->karma['global']['votes']['waste']['percent']		= (float)$xml->votes->waste['percent'];
							$this->karma['global']['votes']['waste']['count']		= (int)$xml->votes->waste['count'];

							$this->karma['global']['votes']['karma']			= (int)$xml->votes->karma;
							$this->karma['global']['votes']['total']			= ($this->karma['global']['votes']['fantastic']['count'] + $this->karma['global']['votes']['beautiful']['count'] + $this->karma['global']['votes']['good']['count'] + $this->karma['global']['votes']['bad']['count'] + $this->karma['global']['votes']['poor']['count'] + $this->karma['global']['votes']['waste']['count']);

							// Insert the votes for every Player
							foreach ($aseco->server->players->player_list as $player) {
								foreach ($xml->players->player as $pl) {
									if ($player->login == $pl['login']) {
										$this->karma['global']['players'][$player->login]['vote']	= (int)$pl['vote'];
										$this->karma['global']['players'][$player->login]['previous']	= (int)$pl['previous'];
									}
								}
							}

							// If <require_finish> is enabled
							if ($this->config['require_finish'] > 0) {
								// Has the Player already vote this Map? If true, set to 9999 for max.
								foreach ($aseco->server->players->player_list as $player) {
									foreach ($xml->players->player as $pl) {
										if ( ($player->login == $pl['login']) && ((int)$pl['vote'] != 0) ) {
											// Set the state of finishing this map, if not already has a setup of a != 0 value
											if ($this->getPlayerData($player, 'FinishedMapCount') == 0) {
												$this->storePlayerData($player, 'FinishedMapCount', 9999);
											}
										}
									}
								}
							}

							// Check to see if it is required to sync global to local votes?
							if ($this->config['sync_global_karma_local'] == true) {
								$this->syncGlobaAndLocalVotes('local', true);
							}

							// Now sync local votes to global votes (e.g. on connection lost...)
							$this->syncGlobaAndLocalVotes('global', true);

							if ($this->config['karma_calculation_method'] == 'RASP') {
								// Update the global/local $this->karma
								$this->calculateKarma(array('global','local'));
							}

							// Display the Karma value of Map?
							if ($this->config['show_at_start'] == true) {
								// Show players' actual votes, or global karma message?
								if ($this->config['show_votes'] == true) {
									// Send individual player messages
									if ($target === false) {
										foreach ($aseco->server->players->player_list as $player) {
											$this->sendMapKarmaMessage($player->login);
										}
									}
									else {
										$this->sendMapKarmaMessage($target->login);
									}
								}
								else {
									// Send more efficient global message
									$this->sendMapKarmaMessage(false);
								}
							}

							if ($target === false) {
								$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);

								// Extract the MapImage and store them at the API
								if (strtoupper($xml->image_present) == 'FALSE') {
									$this->transmitMapImage();
								}
							}
						}
						else {
							$aseco->console('[ManiaKarma] handleWebaccess() on type "'. $type .'": Connection failed with "'. $xml->status .'" for url ['. $url .']');
							$this->config['retrytime'] = (time() + $this->config['retrywait']);
							$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
							$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
						}

						if ($target === false) {
							// Update KarmaWidget for all connected Players
							if ($this->config['widget']['current_state'] == 0) {
								$this->sendWidgetCombination(array('skeleton_score', 'cups_values'), false);
							}
							else {
								$this->sendWidgetCombination(array('skeleton_race', 'cups_values'), false);
							}
							foreach ($aseco->server->players->player_list as $player) {
								$this->sendWidgetCombination(array('player_marker'), $player);
							}
						}
						else {
							// Update KarmaWidget only for current Player
							if ($this->config['widget']['current_state'] == 0) {
								$this->sendWidgetCombination(array('skeleton_score', 'cups_values', 'player_marker'), $target);
							}
							else {
								$this->sendWidgetCombination(array('skeleton_race', 'cups_values', 'player_marker'), $target);
							}
						}
					}
				}
				else if ($type == 'VOTE') {
					// Read the response
					if ($xml = @simplexml_load_string($response['Message'], null, LIBXML_COMPACT) ) {
						if (!$xml->status == 200) {
							$aseco->console('[ManiaKarma] handleWebaccess() on type "'. $type .'":  Storing votes failed with returncode "'. $xml->status .'"');
						}
						unset($xml);
					}
					else {
						$aseco->console('[ManiaKarma] handleWebaccess() on type "'. $type .'": Could not read/parse response from mania-karma.com "'. $response['Message'] .'"!');
						$this->config['retrytime'] = (time() + $this->config['retrywait']);
						$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
					}
				}
				else if ($type == 'UPTODATE') {
					// Read the response
					if ($xml = @simplexml_load_string($response['Message'], null, LIBXML_COMPACT) ) {
						$current_release = $xml->uaseco;
						if ( version_compare($current_release, $this->getVersion(), '>') ) {
							$release_url = 'http://'. $this->config['urls']['website'] .'/Downloads/';
							$message = $aseco->formatText($this->config['messages']['uptodate_new'],
								$current_release,
								'$L[' . $release_url . ']' . $release_url . '$L'
							);
							$aseco->sendChatMessage($message, $target->login);
						}
						else {
							if ($this->config['uptodate_info'] == 'DEFAULT') {
								$message = $aseco->formatText($this->config['messages']['uptodate_ok'],
									$this->getVersion()
								);
								$aseco->sendChatMessage($message, $target->login);
							}
						}
					}
					else {
						$aseco->sendChatMessage($this->config['messages']['uptodate_failed'], $target->login);
						$aseco->console('[ManiaKarma] handleWebaccess() on type "'. $type .'": Could not read/parse xml response!');
						$this->config['retrytime'] = (time() + $this->config['retrywait']);
						$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
					}
				}
				else if ($type == 'EXPORT') {
					if ($response['Code'] == 200) {
						$this->config['import_done'] = true;		// Set to true, otherwise only after restart UASECO knows that
						$message = '{#server}>> {#admin}Export done. Thanks for supporting mania-karma.com!';
						$aseco->sendChatMessage($message, $target->login);
					}
					else if ($response['Code'] == 406) {
						$message = '{#server}>> {#error}Export rejected! Please check your <login> and <nation> in config file "mania_karma.xml"!';
						$aseco->sendChatMessage($message, $target->login);
					}
					else if ($response['Code'] == 409) {
						$message = '{#server}>> {#error}Export rejected! Export was already done, allowed only one time!';
						$aseco->sendChatMessage($message, $target->login);
					}
					else {
						$message = '{#server}>> {#error}Connection failed with '. $response['Code'] .' ('. $response['Reason'] .') for url ['. $api_url .']' ."\n\r";
						$aseco->sendChatMessage($message, $target->login);
					}
				}
				else if ($type == 'PING') {
					$this->config['retrytime'] = 0;
				}
				else if ($type == 'STOREIMAGE') {
					// Do nothing
				}
			}
			else {
				$aseco->console('[ManiaKarma] handleWebaccess() connection failed with "'. $response['Code'] .' - '. $response['Reason'] .'" for url ['. $url .']');
				$this->config['retrytime'] = (time() + $this->config['retrywait']);
				$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
			}
		}
		else {
			$aseco->console('[ManiaKarma] handleWebaccess() connection failed '. $response['Error'] .' for url ['. $url .']');
			$this->config['retrytime'] = (time() + $this->config['retrywait']);
			$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function findPlayersLocalRecords ($map_id, $player_list) {
		global $aseco;

		// Bail out if map id was not found
		if ($map_id === false) {
			return;
		}

		$player_ids = array();
		foreach ($player_list as $player) {
			$player_ids[] = $player->id;
		}

		$query = "
		SELECT
			`p`.`Login` AS `login`,
			COUNT(`t`.`MapId`) AS `count`
		FROM `%prefix%times` AS `t`
		LEFT JOIN `%prefix%players` AS `p` ON `p`.`PlayerId`=`t`.`PlayerId`
		WHERE `t`.`PlayerId` IN (". implode(',', $player_ids) .")
		AND `t`.`MapId`='". $map_id ."'
		GROUP BY `p`.`Login`;
		";
		$res = $aseco->db->query($query);

		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_object()) {
					foreach ($aseco->server->players->player_list as $player) {
						if ($player->login == $row->login) {
							$this->storePlayerData($player, 'FinishedMapCount', (int)$row->count);
						}
					}
				}
			}
			$res->free_result();
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getLocalKarma ($MapId = false) {
		global $aseco;

		// Bail out if $MapId is not given
		if ($MapId == false) {
			return;
		}

		$this->karma['local']['votes']['fantastic']['percent']	= 0;
		$this->karma['local']['votes']['fantastic']['count']		= 0;
		$this->karma['local']['votes']['beautiful']['percent']	= 0;
		$this->karma['local']['votes']['beautiful']['count']		= 0;
		$this->karma['local']['votes']['good']['percent']		= 0;
		$this->karma['local']['votes']['good']['count']		= 0;

		$this->karma['local']['votes']['bad']['percent']		= 0;
		$this->karma['local']['votes']['bad']['count']		= 0;
		$this->karma['local']['votes']['poor']['percent']		= 0;
		$this->karma['local']['votes']['poor']['count']		= 0;
		$this->karma['local']['votes']['waste']['percent']		= 0;
		$this->karma['local']['votes']['waste']['count']		= 0;

		$query = "
			SELECT
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='3'
			) AS `FantasticCount`,
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='2'
			) AS `BeautifulCount`,
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='1'
			) AS `GoodCount`,
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='-1'
			) AS `BadCount`,
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='-2'
			) AS `PoorCount`,
			(
			  SELECT COUNT(`Score`)
			  FROM `%prefix%ratings`
			  WHERE `MapId`='$MapId'
			  AND `Score`='-3'
			) AS `WasteCount`;
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$row = $res->fetch_object();

				$this->karma['local']['votes']['fantastic']['count']		= $row->FantasticCount;
				$this->karma['local']['votes']['beautiful']['count']		= $row->BeautifulCount;
				$this->karma['local']['votes']['good']['count']		= $row->GoodCount;
				$this->karma['local']['votes']['bad']['count']		= $row->BadCount;
				$this->karma['local']['votes']['poor']['count']		= $row->PoorCount;
				$this->karma['local']['votes']['waste']['count']		= $row->WasteCount;
			}
			$res->free_result();
		}

		// Update the local $this->karma
		$this->calculateKarma(array('local'));
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getLocalVotes ($MapId, $login = false) {
		global $aseco;

		// Bail out if $MapId is not given
		if ($MapId == false) {
			return;
		}


		// Build the Player votes Array
		$logins = array();
		if ($login == false) {
			// Add all Players
			foreach ($aseco->server->players->player_list as $player) {
				$logins[] = "'". $player->login ."'";
			}
		}
		else {
			// Add only given Player
			$logins[] = "'". $login ."'";
		}

		// Request the Player votes
		$votes = array();
		$query = "
		SELECT
			`p`.`Login`,
			`k`.`Score`
		FROM `%prefix%ratings` AS `k`
		LEFT JOIN `%prefix%players` AS `p` ON `p`.`PlayerId`=`k`.`PlayerId`
		WHERE `k`.`MapId`='". $MapId ."'
		AND `p`.`Login` IN (". implode(',', $logins) .");
		";

		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				while ($row = $res->fetch_object()) {
					$this->karma['local']['players'][$row->Login]['vote'] = (int)$row->Score;
				}
			}
			$res->free_result();
		}

		if ($login == false) {
			// If some Players has not vote this Map, we need to add them with Vote=0
			foreach ($aseco->server->players->player_list as $player) {
				if ( !isset($this->karma['local']['players'][$player->login]) ) {
					$this->karma['local']['players'][$player->login]['vote'] = 0;
				}
			}
		}
		else if ( !isset($this->karma['local']['players'][$login]) ) {
			$this->karma['local']['players'][$login]['vote'] = 0;
		}


		// Find out which Player already vote this Map? If true, set to 9999 for max.
		if ($this->config['require_finish'] > 0) {
			foreach ($aseco->server->players->player_list as $player) {
				if ($this->karma['local']['players'][$player->login]['vote'] != 0) {
					// Set the state of finishing this map, if not already has a setup of a != 0 value
					if ($this->getPlayerData($player, 'FinishedMapCount') == 0) {
						$this->storePlayerData($player, 'FinishedMapCount', 9999);
					}
				}
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setEmptyKarma ($reset_locals = false) {
		global $aseco;

		$empty = array();
		$empty['data']['uid']					= false;

		$empty['global']['votes']['karma']			= 0;
		$empty['global']['votes']['total']			= 0;

		$empty['global']['votes']['fantastic']['percent']	= 0;
		$empty['global']['votes']['fantastic']['count']		= 0;
		$empty['global']['votes']['beautiful']['percent']	= 0;
		$empty['global']['votes']['beautiful']['count']		= 0;
		$empty['global']['votes']['good']['percent']		= 0;
		$empty['global']['votes']['good']['count']		= 0;

		$empty['global']['votes']['bad']['percent']		= 0;
		$empty['global']['votes']['bad']['count']		= 0;
		$empty['global']['votes']['poor']['percent']		= 0;
		$empty['global']['votes']['poor']['count']		= 0;
		$empty['global']['votes']['waste']['percent']		= 0;
		$empty['global']['votes']['waste']['count']		= 0;

		$empty['global']['players']				= array();

		if ($reset_locals === true) {
			$empty['local']['votes']['karma']			= 0;
			$empty['local']['votes']['total']			= 0;

			$empty['local']['votes']['fantastic']['percent']	= 0;
			$empty['local']['votes']['fantastic']['count']		= 0;
			$empty['local']['votes']['beautiful']['percent']	= 0;
			$empty['local']['votes']['beautiful']['count']		= 0;
			$empty['local']['votes']['good']['percent']		= 0;
			$empty['local']['votes']['good']['count']		= 0;

			$empty['local']['votes']['bad']['percent']		= 0;
			$empty['local']['votes']['bad']['count']		= 0;
			$empty['local']['votes']['poor']['percent']		= 0;
			$empty['local']['votes']['poor']['count']		= 0;
			$empty['local']['votes']['waste']['percent']		= 0;
			$empty['local']['votes']['waste']['count']		= 0;

			$empty['local']['players']				= array();
		}

		foreach ($aseco->server->players->player_list as $player) {
			$empty['global']['players'][$player->login]['vote']	= 0;
			$empty['global']['players'][$player->login]['previous']	= 0;

			if ($reset_locals === true) {
				$empty['local']['players'][$player->login]['vote']	= 0;
				$empty['local']['players'][$player->login]['previous']	= 0;
			}
		}

		// Copy current $this->karma['local'] into the new array
		if ($reset_locals === false) {
			$empty['local'] = $this->karma['local'];
		}

		return $empty;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Checks plugin version at MasterAdmin connect
	public function uptodateCheck ($player) {
		global $aseco;

		// Check if connection was failed and try to reconnect
		if ( ($this->config['retrytime'] > 0) && (time() >= $this->config['retrytime']) ) {
			// Reconnect to the database
			$this->onSync($aseco);
		}

		if ($this->config['retrytime'] > 0) {
			// Connect failed, try again later
			return;
		}

		// Start an async UPTODATE request
		$url = 'http://'. $this->config['urls']['website'] .'/api/plugin-releases.xml';
		$aseco->webaccess->request($url, array(array($this, 'handleWebaccess'), 'UPTODATE', $url, $player), 'none', false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function calculateKarma ($which) {

		// Calculate the Global/Local-Karma
		foreach ($which as $location) {

			// Prevent negativ vote counts
			foreach (array('fantastic', 'beautiful', 'good', 'bad', 'poor', 'waste') as $type) {
				if ($this->karma[$location]['votes'][$type]['count'] < 0) {
					$this->karma[$location]['votes'][$type]['count'] = 0;
				}
			}

			$totalvotes = ($this->karma[$location]['votes']['fantastic']['count'] + $this->karma[$location]['votes']['beautiful']['count'] + $this->karma[$location]['votes']['good']['count'] + $this->karma[$location]['votes']['bad']['count'] + $this->karma[$location]['votes']['poor']['count'] + $this->karma[$location]['votes']['waste']['count']);

			// Prevention of "illegal division by zero"
			if ($totalvotes == 0) {
				$totalvotes = 0.0000000000001;
			}

			$this->karma[$location]['votes']['fantastic']['percent']	= sprintf("%.2f", ($this->karma[$location]['votes']['fantastic']['count']	/ $totalvotes * 100));
			$this->karma[$location]['votes']['beautiful']['percent']	= sprintf("%.2f", ($this->karma[$location]['votes']['beautiful']['count']	/ $totalvotes * 100));
			$this->karma[$location]['votes']['good']['percent']		= sprintf("%.2f", ($this->karma[$location]['votes']['good']['count']		/ $totalvotes * 100));
			$this->karma[$location]['votes']['bad']['percent']		= sprintf("%.2f", ($this->karma[$location]['votes']['bad']['count']		/ $totalvotes * 100));
			$this->karma[$location]['votes']['poor']['percent']		= sprintf("%.2f", ($this->karma[$location]['votes']['poor']['count']		/ $totalvotes * 100));
			$this->karma[$location]['votes']['waste']['percent']		= sprintf("%.2f", ($this->karma[$location]['votes']['waste']['count']		/ $totalvotes * 100));

			$good_votes = (
				($this->karma[$location]['votes']['fantastic']['count'] * 100) +
				($this->karma[$location]['votes']['beautiful']['count'] * 80) +
				($this->karma[$location]['votes']['good']['count'] * 60)
			);
			$bad_votes = (
				($this->karma[$location]['votes']['bad']['count'] * 40) +
				($this->karma[$location]['votes']['poor']['count'] * 20) +
				($this->karma[$location]['votes']['waste']['count'] * 0)
			);

			if ($this->config['karma_calculation_method'] == 'RASP') {
				$this->karma[$location]['votes']['karma'] = floor(
					($this->karma[$location]['votes']['fantastic']['count'] * 3) +
					($this->karma[$location]['votes']['beautiful']['count'] * 2) +
					($this->karma[$location]['votes']['good']['count'] * 1) +
					-($this->karma[$location]['votes']['bad']['count'] * 1) +
					-($this->karma[$location]['votes']['poor']['count'] * 2) +
					-($this->karma[$location]['votes']['waste']['count'] * 3)
				);
			}
			else {
				$this->karma[$location]['votes']['karma'] = floor( ($good_votes + $bad_votes) / $totalvotes);
			}

			$this->karma[$location]['votes']['total'] = intval($totalvotes);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function exportVotes ($player) {
		global $aseco;

		if ($this->config['import_done'] != false) {
			$message = "{#server}>> {#admin}Export of local votes already done, skipping...";
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$message = "{#server}>> {#admin}Collecting players with their votes on Maps...";
		$aseco->sendChatMessage($message, $player->login);

		// Generate the content for this export
		$csv = false;
		$query = "
		SELECT
			`m`.`Uid`,
			`m`.`Name`,
			`m`.`Author`,
			`m`.`Environment`,
			`p`.`Login`,
			`rs`.`Score`
		FROM `%prefix%ratings` AS `rs`
		LEFT JOIN `%prefix%maps` AS `m` ON `m`.`MapId`=`rs`.`MapId`
		LEFT JOIN `%prefix%players` AS `p` ON `p`.`PlayerId`=`rs`.`PlayerId`
		ORDER BY `m`.`Uid`;
		";
		$res = $aseco->db->query($query);
		if ($res) {
			if ($res->num_rows > 0) {
				$count = 1;
				while ($row = $res->fetch_object()) {
					if ( $row->Uid ) {
						$csv .= sprintf("%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
							$row->Uid,
							$row->Name,
							$row->Author,
							$row->Environment,
							$this->config['account']['login'],
							$this->config['account']['authcode'],
							$this->config['account']['nation'],
							$row->Login,
							$row->Score
						);
					}
					$count ++;
				}
			}
			$res->free_result();
		}

		$message = "{#server}>> {#admin}Found ". number_format($count, 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) ." votes in database.";
		$aseco->sendChatMessage($message, $player->login);


		// gzip the CSV
		$message = "{#server}>> {#admin}Compressing collected data...";
		$aseco->sendChatMessage($message, $player->login);
		$csv = gzencode($csv, 9, FORCE_GZIP);


		// Encode them Base64
		$message = "{#server}>> {#admin}Encoding data...";
		$aseco->sendChatMessage($message, $player->login);
		$csv = base64_encode($csv);


		$message = "{#server}>> {#admin}Sending now the export with size of ". number_format(strlen($csv), 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) ." bytes...";
		$aseco->sendChatMessage($message, $player->login);

		// Generate the url for the Import-Request
		$api_url = sprintf("%s?Action=Import&login=%s&authcode=%s&nation=%s",
			$this->config['urls']['api'],
			urlencode( $this->config['account']['login'] ),
			urlencode( $this->config['account']['authcode'] ),
			urlencode( $this->config['account']['nation'] )
		);

		// Start an async EXPORT request
		$aseco->webaccess->request($api_url, array(array($this, 'handleWebaccess'), 'EXPORT', $api_url, $player), $csv, false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function transmitMapImage () {
		global $aseco;

		$gbx = new GBXChallMapFetcher(true, true, false);
		try {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$gbx->processFile($aseco->server->mapdir . iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $aseco->stripBOM($aseco->server->maps->current->filename)));
			}
			else {
				$gbx->processFile($aseco->server->mapdir . $aseco->stripBOM($aseco->server->maps->current->filename));
			}
		}
		catch (Exception $e) {
			trigger_error('[ManiaKarma] Could not read Map ['. $aseco->server->mapdir . $aseco->stripBOM($aseco->server->maps->current->filename) .'] at transmitMapImage(): '. $e->getMessage(), E_USER_WARNING);
			return;
		}

		// Generate the url for this Map-Karma-Request
		$api_url = sprintf("%s?Action=StoreImage&login=%s&authcode=%s&uid=%s&env=%s&game=%s",
			$this->config['urls']['api'],
			urlencode( $this->config['account']['login'] ),
			urlencode( $this->config['account']['authcode'] ),
			urlencode( $aseco->server->maps->current->uid ),
			urlencode( $aseco->server->maps->current->environment ),
			urlencode( $aseco->server->game )
		);

		// Start an async STOREIMAGE request
		$aseco->webaccess->request($api_url, array(array($this, 'handleWebaccess'), 'STOREIMAGE', $api_url), base64_encode($gbx->thumbnail), false, $this->config['keepalive_min_timeout'], $this->config['connect_timeout'], $this->config['wait_timeout'], $this->config['user_agent']);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleSpecialChars ($string) {
		global $aseco;

		// Remove links, e.g. "$(L|H|P)[...]...$(L|H|P)"
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)\$(L|H|P)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)\[.*?\](.*?)/i', '$2', $string);
		$string = preg_replace('/\${1}(L|H|P)(.*?)/i', '$2', $string);

		// Remove $S (shadow)
		// Remove $H (manialink)
		// Remove $W (wide)
		// Remove $I (italic)
		// Remove $L (link)
		// Remove $O (bold)
		// Remove $N (narrow)
		$string = preg_replace('/\${1}[SHWILON]/i', '', $string);

		// Convert &
		// Convert "
		// Convert '
		// Convert >
		// Convert <
		$string = str_replace(
				array(
					'&',
					'"',
					"'",
					'>',
					'<'
				),
				array(
					'&amp;',
					'&quot;',
					'&apos;',
					'&gt;',
					'&lt;'
				),
				$string
		);
		$string = $aseco->stripNewlines($string);

		return $aseco->validateUTF8String($string);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function loadTemplates () {

		//--------------------------------------------------------------//
		// BEGIN: Window						//
		//--------------------------------------------------------------//
		// %window_title%
		// %prev_next_buttons%
		$header  = '<manialink id="'. $this->config['manialink_id'] .'02" name="Windows">';
		$header .= '<frame posn="-40.8 30.55 18" id="ManiaKarmaWindow">';	// BEGIN: Window Frame
		$header .= '<quad posn="-0.2 0.2 0.01" sizen="81.8 59" style="Bgs1InRace" substyle="BgTitle2" id="ManiaKarmaWindowBody" ScriptEvents="1"/>';
		$header .= '<quad posn="1.8 -4.1 0.02" sizen="77.7 49.9" bgcolor="0018"/>';

		// Header Line
		$header .= '<quad posn="-0.6 0.6 0.02" sizen="82.6 6" style="Bgs1InRace" substyle="BgTitle3_3"/>';
		$header .= '<quad posn="-0.6 0.6 0.03" sizen="82.6 6" style="Bgs1InRace" substyle="BgTitle3_3" id="ManiaKarmaWindowTitle" ScriptEvents="1"/>';

		// Title
		$header .= '<quad posn="1.8 -0.7 0.04" sizen="3.2 3.2" style="Icons64x64_1" substyle="ToolLeague1"/>';
		$header .= '<label posn="5.5 -1.7 0.04" sizen="75.4 0" textsize="2" scale="0.9" textcolor="000F" text="%window_title%"/>';

		// Close Button
		$header .= '<frame posn="76.7 -0.15 0.05">';
		$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" style="Icons64x64_1" substyle="ArrowUp" id="ManiaKarmaWindowClose" ScriptEvents="1"/>';
		$header .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$header .= '<quad posn="0.7 -0.7 0.03" sizen="3.1 3.1" style="Icons64x64_1" substyle="Close"/>';
		$header .= '</frame>';

		// Minimize Button
		$header .= '<frame posn="73.4 -0.15 0.05">';
		$header .= '<quad posn="0 0 0.01" sizen="4.5 4.5" style="Icons64x64_1" substyle="ArrowUp" id="ManiaKarmaWindowMinimize" ScriptEvents="1"/>';
		$header .= '<quad posn="1.2 -1.2 0.02" sizen="2 2" bgcolor="EEEF"/>';
		$header .= '<label posn="2.33 -2.4 0.03" sizen="6 0" halign="center" valign="center" textsize="3" textcolor="000F" text="$O-"/>';
		$header .= '</frame>';

		$header .= '<label posn="6.8 -55.8 0.04" sizen="14 2" halign="center" valign="center" textsize="1" scale="0.7" action="'. $this->config['manialink_id'] .'01" focusareacolor1="0000" focusareacolor2="FFF5" textcolor="000F" text="MANIAKARMA/'. $this->getVersion() .'"/>';
		$header .= '%prev_next_buttons%';

$maniascript = <<<EOL
<script><!--
 /*
 * ----------------------------------
 * Author:	undef.de
 * Website:	http://www.undef.name
 * Part of:	Mania-Karma
 * License:	GPL3
 * ----------------------------------
 */
Void WipeOut (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = 0.0;
	declare Real EndPosnY = 0.0;
	declare Real PosnDistanceX = (EndPosnX - Container.PosnX);
	declare Real PosnDistanceY = (EndPosnY - Container.PosnY);

	while (Container.RelativeScale > 0.0) {
		Container.PosnX += (PosnDistanceX / 20);
		Container.PosnY += (PosnDistanceY / 20);
		Container.RelativeScale -= 0.05;
		yield;
	}
	Container.Unload();
}
Void Minimize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = (-40.8 * 2.5);
	declare Real EndPosnY = (30.55 * 1.875);
	declare Real PosnDistanceX = (EndPosnX - Container.PosnX);
	declare Real PosnDistanceY = (EndPosnY - Container.PosnY);

	while (Container.RelativeScale > 0.2) {
		Container.PosnX += (PosnDistanceX / 16);
		Container.PosnY += (PosnDistanceY / 16);
		Container.RelativeScale -= 0.05;
		yield;
	}
}
Void Maximize (Text ChildId) {
	declare CMlControl Container <=> (Page.GetFirstChild(ChildId) as CMlFrame);
	declare Real EndPosnX = (-40.8 * 2.5);
	declare Real EndPosnY = (30.55 * 1.875);
	declare Real PosnDistanceX = (EndPosnX - Container.PosnX);
	declare Real PosnDistanceY = (EndPosnY - Container.PosnY);

	while (Container.RelativeScale < 1.0) {
		Container.PosnX += (PosnDistanceX / 16);
		Container.PosnY += (PosnDistanceY / 16);
		Container.RelativeScale += 0.05;
		yield;
	}
}
main () {
	declare CMlControl Container <=> (Page.GetFirstChild("ManiaKarmaWindow") as CMlFrame);
	declare CMlQuad Quad;
	declare Boolean MoveWindow = False;
	declare Boolean IsMinimized = False;
	declare Real MouseDistanceX = 0.0;
	declare Real MouseDistanceY = 0.0;

	while (True) {
		if (MoveWindow == True) {
			Container.PosnX = (MouseDistanceX + MouseX);
			Container.PosnY = (MouseDistanceY + MouseY);
		}
		if (MouseLeftButton == True) {
			foreach (Event in PendingEvents) {
				if (Event.ControlId == "ManiaKarmaWindowTitle") {
					MouseDistanceX = (Container.PosnX - MouseX);
					MouseDistanceY = (Container.PosnY - MouseY);
					MoveWindow = True;
				}
			}
		}
		else {
			MoveWindow = False;
		}
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					if (Event.ControlId == "ManiaKarmaWindowClose") {
						WipeOut("ManiaKarmaWindow");
					}
					else if ( (Event.ControlId == "ManiaKarmaWindowMinimize") && (IsMinimized == False) ) {
						Minimize("ManiaKarmaWindow");
						IsMinimized = True;
					}
					else if ( (Event.ControlId == "ManiaKarmaWindowBody") && (IsMinimized == True) ) {
						Maximize("ManiaKarmaWindow");
						IsMinimized = False;
					}
				}
			}
		}
		yield;
	}
}
--></script>
EOL;
		// Footer
		$footer  = '</frame>';				// END: Window Frame
		$footer .= $maniascript;
		$footer .= '</manialink>';

		$templates['WINDOW']['HEADER'] = $header;
		$templates['WINDOW']['FOOTER'] = $footer;

		unset($header, $footer);
		//--------------------------------------------------------------//
		// END: Window							//
		//--------------------------------------------------------------//


		return $templates;
	}
}

?>
