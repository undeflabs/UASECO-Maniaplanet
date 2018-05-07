<?php
/*
 * Plugin: Mania-Karma
 * ~~~~~~~~~~~~~~~~~~~
 * For a detailed description and documentation, please refer to:
 * http://www.undef.name/UASECO/Mania-Karma.php
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
 * OpenHelpWindow
 * OpenKarmaWindow
 * Vote
 * -> Fantastic
 * -> Beautiful
 * -> Good
 * -> Bad
 * -> Poor
 * -> Waste
 * -> Undecided
 * -> RequireFinish ((red) buttons, tell the Player to finish this Map x times)
 * -> Ignore
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

		$this->setAuthor('undef.de');
		$this->setVersion('2.0.0');
		$this->setBuild('2018-05-07');
		$this->setCopyright('2009 - 2018 by undef.de');
		$this->setDescription('Global Karma Database for Map votings.');

		$this->addDependence('PluginRaspKarma', Dependence::DISALLOWED, '1.0.0', null);

		$this->registerEvent('onSync',				'onSync');
		$this->registerEvent('onPlayerChat',			'onPlayerChat');
		$this->registerEvent('onPlayerConnect',			'onPlayerConnect');
		$this->registerEvent('onPlayerDisconnectPrepare',	'onPlayerDisconnectPrepare');
		$this->registerEvent('onPlayerDisconnect',		'onPlayerDisconnect');
		$this->registerEvent('onPlayerFinish',			'onPlayerFinish');
		$this->registerEvent('onLoadingMap',			'onLoadingMap');
		$this->registerEvent('onRestartMap',			'onRestartMap');
		$this->registerEvent('onEndMapRanking',			'onEndMapRanking');
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
		$uaseco_min_version = '0.9.0';
		if (defined('UASECO_VERSION')) {
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


		if ((string)$xmlcfg->urls->api_auth === '') {
			trigger_error("[ManiaKarma] <urls><api_auth> is empty in config file 'config/mania_karma.xml'!", E_USER_ERROR);
		}

		if ((string)$xmlcfg->nation === '') {
			trigger_error("[ManiaKarma] <nation> is empty in config file 'config/mania_karma.xml'!", E_USER_ERROR);
		}
		else if ((string)$xmlcfg->nation === 'YOUR_SERVER_NATION') {
			trigger_error("[ManiaKarma] <nation> is not set in config file 'config/mania_karma.xml'! Please change 'YOUR_SERVER_NATION' with your server nation code.", E_USER_ERROR);
		}
		else if (! $iso3166Alpha3[strtoupper((string)$xmlcfg->nation)][1] ) {
			trigger_error("[ManiaKarma] <nation> is not valid in config file 'config/mania_karma.xml'! Please change <nation> to valid ISO-3166 ALPHA-3 nation code!", E_USER_ERROR);
		}


		// Set Url for API-Call Auth
		$this->config['urls']['api_auth'] = (string)$xmlcfg->urls->api_auth;

		// Check the given config timeouts and set defaults on too low or on empty timeouts
		if ((int)$xmlcfg->timeout < 40 || (int)$xmlcfg->timeout === '') {
			$this->config['timeout'] = 40;
		}
		else {
			$this->config['timeout'] = (int)$xmlcfg->timeout;
		}
		if ((int)$xmlcfg->timeout_connect < 30 || (int)$xmlcfg->timeout_connect === '') {
			$this->config['timeout_connect'] = 30;
		}
		else {
			$this->config['timeout_connect'] = (int)$xmlcfg->timeout_connect;
		}
		if ((int)$xmlcfg->timeout_dns < 100 || (int)$xmlcfg->timeout_dns === '') {
			$this->config['timeout_dns'] = 100;
		}
		else {
			$this->config['timeout_dns'] = (int)$xmlcfg->timeout_dns;
		}

		// Set connection status to 'all fine'
		$this->config['retrytime'] = 0;

		// wait a little bit until try to reconnect (currently 60 sec.)
		$this->config['retrywait'] = 60;

		// Set login data
		$this->config['account']['login']	= strtolower((string)$aseco->server->login);
		$this->config['account']['nation']	= strtoupper((string)$xmlcfg->nation);

		// Create a User-Agent-Identifier for the authentication
		$this->config['user_agent'] = 'ManiaKarma/'. $this->getVersion() .' '. USER_AGENT;

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

		try {
			// Start sync GET request
			$params = array(
				'url'			=> $api_url,
				'sync'			=> true,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> $this->config['timeout_dns'],
				'timeout_connect'	=> $this->config['timeout_connect'],
				'timeout'		=> $this->config['timeout'],
			);
			$request = $aseco->webrequest->GET($params);
			if (isset($request->response['header']['code']) && $request->response['header']['code'] === 200) {
				// Read the request
				if (!$xml = @simplexml_load_string($request->response['content'], null, LIBXML_COMPACT) ) {
					$this->config['retrytime'] = (time() + $this->config['retrywait']);
					$this->config['account']['authcode'] = false;
					$this->config['urls']['api'] = false;

					// Fake import done to do not ask a MasterAdmin to export
					$this->config['import_done'] = true;

					$aseco->console('[ManiaKarma] » Could not read/parse response "'. $request->response['content'] .'"!');
					$aseco->console('[ManiaKarma] » Connection failed with "'. $request->response['header']['code'] .'" for url ['. $api_url .'], retry again later.');
					$aseco->console('[ManiaKarma] ********************************************************');
				}
				else {
					if ((int)$xml->status === 200) {
						$this->config['retrytime'] = 0;
						$this->config['account']['authcode'] = (string)$xml->authcode;
						$this->config['urls']['api'] = (string)$xml->api_url;

						$this->config['import_done'] = ((strtoupper((string)$xml->import_done) === 'TRUE') ? true : false);

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

				if (isset($request->response['header']['code'])) {
					$aseco->console('[ManiaKarma] » Connection failed with "'. $request->response['header']['code'] .'" for url ['. $api_url .'], retry again later.');
				}
				else {
					$aseco->console('[ManiaKarma] » Connection failed with "'. $aseco->dump($request) .'" for url ['. $api_url .'], retry again later.');
				}
				$aseco->console('[ManiaKarma] ********************************************************');
			}
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
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
			'time_attack'	=> Gameinfo::TIME_ATTACK,
			'team'		=> Gameinfo::TEAM,
			'laps'		=> Gameinfo::LAPS,
			'cup'		=> Gameinfo::CUP,
			'team_attack'	=> Gameinfo::TEAM_ATTACK,
			'chase'		=> Gameinfo::CHASE,
			'knockout'	=> Gameinfo::KNOCKOUT,
			'doppler'	=> Gameinfo::DOPPLER,
		);
		foreach ($gamemodes as $mode => $id) {
			if ( isset($xmlcfg->karma_widget->gamemode->$mode) ) {
				$this->config['widget']['states'][$id]['enabled']	= ((strtoupper((string)$xmlcfg->karma_widget->gamemode->$mode->enabled) === 'TRUE') ? true : false);
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
		$this->config['show_welcome']					= ((strtoupper((string)$xmlcfg->show_welcome) === 'TRUE')		? true : false);
		$this->config['allow_public_vote']				= ((strtoupper((string)$xmlcfg->allow_public_vote) === 'TRUE')		? true : false);
		$this->config['show_at_start']					= ((strtoupper((string)$xmlcfg->show_at_start) === 'TRUE')		? true : false);
		$this->config['show_details']					= ((strtoupper((string)$xmlcfg->show_details) === 'TRUE')		? true : false);
		$this->config['show_votes']					= ((strtoupper((string)$xmlcfg->show_votes) === 'TRUE')			? true : false);
		$this->config['show_karma']					= ((strtoupper((string)$xmlcfg->show_karma) === 'TRUE')			? true : false);
		$this->config['require_finish']					= (int)$xmlcfg->require_finish;
		$this->config['remind_to_vote']					= strtoupper((string)$xmlcfg->remind_to_vote);
		$this->config['reminder_window']['display']			= strtoupper((string)$xmlcfg->reminder_window->display);
		$this->config['score_mx_window']				= ((strtoupper((string)$xmlcfg->score_mx_window) === 'TRUE')		? true : false);
		$this->config['messages_in_window']				= ((strtoupper((string)$xmlcfg->messages_in_window) === 'TRUE')		? true : false);
		$this->config['show_player_vote_public']			= ((strtoupper((string)$xmlcfg->show_player_vote_public) === 'TRUE')	? true : false);
		$this->config['save_karma_also_local']				= ((strtoupper((string)$xmlcfg->save_karma_also_local) === 'TRUE')	? true : false);
		$this->config['sync_global_karma_local']			= ((strtoupper((string)$xmlcfg->sync_global_karma_local) === 'TRUE')	? true : false);
		$this->config['images']['widget_open_left']			= (string)$xmlcfg->images->widget_open_left;
		$this->config['images']['widget_open_right']			= (string)$xmlcfg->images->widget_open_right;
		$this->config['images']['mx_logo_normal']			= (string)$xmlcfg->images->mx_logo_normal;
		$this->config['images']['mx_logo_focus']			= (string)$xmlcfg->images->mx_logo_focus;
		$this->config['images']['cup_gold']				= (string)$xmlcfg->images->cup_gold;
		$this->config['images']['cup_silver']				= (string)$xmlcfg->images->cup_silver;
		$this->config['images']['maniakarma_logo']			= (string)$xmlcfg->images->maniakarma_logo;
		$this->config['images']['progress_indicator']			= (string)$xmlcfg->images->progress_indicator;
		$this->config['uptodate_check']					= ((strtoupper((string)$xmlcfg->uptodate_check) === 'TRUE')		? true : false);
		$this->config['uptodate_info']					= strtoupper((string)$xmlcfg->uptodate_info);

		$this->config['karma_calculation_method']			= strtoupper((string)$xmlcfg->karma_calculation_method);

		// Config for Karma Lottery
		$this->config['karma_lottery']['enabled']			= ((strtoupper((string)$xmlcfg->karma_lottery->enabled) === 'TRUE')	? true : false);
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
		$this->config['widget']['buttons']['text_disabled']		= (string)$xmlcfg->widget_styles->vote_buttons->votes->text_color_disabled;
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
		if ($this->config['widget']['race']['background_color'] === '') {
			$this->config['widget']['race']['background_color'] = '0000';
		}
		if ($this->config['widget']['race']['background_focus'] === '') {
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

		// Init
		if ($aseco->startup_phase === true) {
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

		if ($this->config['retrytime'] === 0) {
			// Update KarmaWidget for all connected Players
			if ($this->config['widget']['current_state'] === 0) {
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
		if ($this->config['karma_lottery']['enabled'] === true) {
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

		if (!$player = $aseco->server->players->getPlayerByLogin($chat[1])) {
			return;
		}

		// Check if public vote is enabled
		if ($this->config['allow_public_vote'] === true) {
			// check for possible public karma vote
			if ($chat[2] === '+++') {
				$this->handlePlayerVote($player, 3);
			}
			else if ($chat[2] === '++') {
				$this->handlePlayerVote($player, 2);
			}
			else if ($chat[2] === '+') {
				$this->handlePlayerVote($player, 1);
			}
			else if ($chat[2] === '-') {
				$this->handlePlayerVote($player, -1);
			}
			else if ($chat[2] === '--') {
				$this->handlePlayerVote($player, -2);
			}
			else if ($chat[2] === '---') {
				$this->handlePlayerVote($player, -3);
			}
		}
		else if ( ($chat[2] === '+++') || ($chat[2] === '++') || ($chat[2] === '+') || ($chat[2] === '-') || ($chat[2] === '--') || ($chat[2] === '---') ) {
			$message = $aseco->formatText($this->config['messages']['karma_no_public'], '/'. $chat[2]);
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
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

		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		// Init
		$message = false;

		// Check optional parameter
		if (strtoupper($chat_parameter) === 'HELP' || strtoupper($chat_parameter) === 'ABOUT') {
			$this->onPlayerManialinkPageAnswer($aseco, $player->login, array('Action' => 'OpenHelpWindow'));
		}
		else if (strtoupper($chat_parameter) === 'DETAILS') {
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
		else if (strtoupper($chat_parameter) === 'RELOAD') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' reloads the configuration.');
				$message = '{#admin}» Reload of the configuration "config/mania_karma.xml" done.';
				$this->onSync($aseco);
			}
		}
		else if (strtoupper($chat_parameter) === 'EXPORT') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' start the export of all local votes.');
				$this->exportVotes($player);
			}
		}
		else if (strtoupper($chat_parameter) === 'UPTODATE') {
			if ($aseco->isMasterAdmin($player)) {
				$aseco->console('[ManiaKarma] MasterAdmin '. $player->login .' start the up-to-date check.');
				$this->uptodateCheck($player);
			}
		}
		else if ( (strtoupper($chat_parameter) === 'LOTTERY') && ($this->config['karma_lottery']['enabled'] === true) ) {
			if  ( (isset($player->rights)) && ($player->rights) ) {
				$message = $aseco->formatText($this->config['messages']['lottery_total_player_win'],
					$this->getPlayerData($player, 'LotteryPayout')
				);
			}
		}
		else if (strtoupper($chat_parameter) === '') {
			$message = $this->createKarmaMessage($player->login, true);
		}

		// Show message
		if ($message !== false) {
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) && ($this->config['widget']['current_state'] !== 0) ) {
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

		if ($player = $aseco->server->players->getPlayerByLogin($login)) {
			if ($chat_command === '+++') {
				$this->handlePlayerVote($player, 3);
			}
			else if ($chat_command === '++') {
				$this->handlePlayerVote($player, 2);
			}
			else if ($chat_command === '+') {
				$this->handlePlayerVote($player, 1);
			}
			else if ($chat_command === '-') {
				$this->handlePlayerVote($player, -1);
			}
			else if ($chat_command === '--') {
				$this->handlePlayerVote($player, -2);
			}
			else if ($chat_command === '---') {
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
		if ($this->config['show_welcome'] === true) {
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
			if ($this->config['uptodate_check'] === true) {
				$this->uptodateCheck($player);
			}

			// Export already made?
			if ($this->config['import_done'] === false) {
				$message = '{#server}> {#emotic}#################################################'. LF;
				$message .= '{#server}> {#emotic}Please start the export of your current local votes with the command "/karma export". Thanks!'. LF;
				$message .= '{#server}> {#emotic}#################################################'. LF;
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// If karma lottery is enabled, then initialize (if player has related rights)
		if ($this->config['karma_lottery']['enabled'] === true) {
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
		if ($aseco->startup_phase === false) {
			// Check if Player is already in $this->karma,
			// for "unwished disconnects" and "reconnected" Players
			if ( ( !isset($this->karma['global']['players'][$player->login]) ) || ($aseco->server->maps->current->uid !== $this->karma['data']['uid']) ) {

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
				if ($this->config['sync_global_karma_local'] === true) {
					$this->syncGlobaAndLocalVotes('local', false);
				}
			}

			// Display the complete KarmaWidget only for connected Player
			if ($this->config['widget']['current_state'] === 0) {
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
		if ($this->config['karma_lottery']['enabled'] === true) {
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
		if ($finish_item->score === 0) {
			return;
		}

		$player = $aseco->server->players->getPlayerByLogin($finish_item->player_login);

		// Check if finishes are required
		if ($this->config['require_finish'] > 0) {
			// Save that the player finished this map
			$this->storePlayerData($player, 'FinishedMapCount', ($this->getPlayerData($player, 'FinishedMapCount') + 1));

			// Enable the vote possibilities for this player
			$this->sendWidgetCombination(array('player_marker'), $player);
		}

		// If no finish reminders, bail out too (does not need to check $this->getPlayerData($player, 'FinishedMapCount'), because actually finished ;)
		if ( ($this->config['remind_to_vote'] === 'FINISHED') || ($this->config['remind_to_vote'] === 'ALWAYS') ) {

			// Check whether player already voted
			if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] <= $this->getPlayerData($player, 'FinishedMapCount')) ) ) {
				if ( ($this->config['reminder_window']['display'] === 'FINISHED') || ($this->config['reminder_window']['display'] === 'ALWAYS') ) {
					// Show reminder window
					$this->showReminderWindow($player->login);
					$this->storePlayerData($player, 'ReminderWindow', true);
				}
				else {
					// Show reminder message
					$message = $this->config['messages']['karma_remind'];
					if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
						send_window_message($aseco, $message, $player);
					}
					else {
						$aseco->sendChatMessage($message, $player->login);
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

	public function onPlayerManialinkPageAnswer ($aseco, $login, $answer) {

		// Get Player
		if (!$player = $aseco->server->players->getPlayerByLogin($login)) {
			return;
		}

		if ($answer['Action'] === 'OpenHelpWindow') {
			$page_help = $this->buildHelpAboutWindow($this->config['messages']['karma_help']);

			// Setup settings for Window
			$settings_styles = array(
				'icon'			=> $this->config['widget']['race']['icon_style'] .','. $this->config['widget']['race']['icon_substyle'],
			);
			$settings_content = array(
				'title'			=> 'ManiaKarma help',
				'data'			=> array($page_help),
				'mode'			=> 'pages',
				'add_background'	=> true,
			);
			$settings_footer = array(
				'about_title'		=> 'MANIA-KARMA/'. $this->getVersion(),
				'about_link'		=> 'http://'. $this->config['urls']['website'],
			);

			$window = new Window();
			$window->setStyles($settings_styles);
			$window->setContent($settings_content);
			$window->setFooter($settings_footer);
			$window->send($player, 0, false);
		}
		else if ($answer['Action'] === 'OpenKarmaWindow') {
			$page_votes = $this->buildKarmaDetailWindow($player->login);
			$page_whokarma = $this->buildWhoKarmaWindow();

			// Setup settings for Window
			$settings_styles = array(
				'icon'			=> $this->config['widget']['race']['icon_style'] .','. $this->config['widget']['race']['icon_substyle'],
			);
			$settings_content = array(
				'title'			=> 'ManiaKarma votes overview',
				'data'			=> array($page_votes, $page_whokarma),
				'mode'			=> 'pages',
				'add_background'	=> true,
			);
			$settings_footer = array(
				'about_title'		=> 'MANIA-KARMA/'. $this->getVersion(),
				'about_link'		=> 'http://'. $this->config['urls']['website'],
				'button_title'		=> 'MORE INFO ON MANIA-KARMA.COM',
				'button_link'		=> 'http://'. $this->config['urls']['website'] .'/goto?uid='. $this->karma['data']['uid'] .'&amp;env='. $this->karma['data']['env'] .'&amp;game='. $aseco->server->game,
			);

			$window = new Window();
			$window->setStyles($settings_styles);
			$window->setContent($settings_content);
			$window->setFooter($settings_footer);
			$window->send($player, 0, false);
		}
		else if ($answer['Action'] === 'Vote') {
			if ($answer['Value'] === 'Fantastic') {						// Vote +++
				$this->handlePlayerVote($player, 3);
			}
			else if ($answer['Value'] === 'Beautiful') {					// Vote ++
				$this->handlePlayerVote($player, 2);
			}
			else if ($answer['Value'] === 'Good') {						// Vote +
				$this->handlePlayerVote($player, 1);
			}
			else if ($answer['Value'] === 'Undecided') {					// Vote undecided
				$this->showUndecidedMessage($player);
			}
			else if ($answer['Value'] === 'Bad') {						// Vote -
				$this->handlePlayerVote($player, -1);
			}
			else if ($answer['Value'] === 'Poor') {						// Vote --
				$this->handlePlayerVote($player, -2);
			}
			else if ($answer['Value'] === 'Waste') {						// Vote ---
				$this->handlePlayerVote($player, -3);
			}
			else if ($answer['Value'] === 'RequireFinish') {					// Vote disabled on <require_finish> >= 1
				$this->handlePlayerVote($player, 0);
			}
			else if ($answer['Value'] === 'Ignore') {					// Just ignore
				// do nothing
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function onUnloadingMap ($aseco, $uid) {

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

	public function onLoadingMap ($aseco, $map) {

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
		if (count($aseco->server->players->player_list) === 0) {

			if ($this->config['retrytime'] === 0) {
				// Start an async PING request
				// Generate the url for this Ping-Request
				$api_url = sprintf("%s?Action=Ping&login=%s&authcode=%s",
					$this->config['urls']['api'],
					urlencode( $this->config['account']['login'] ),
					urlencode( $this->config['account']['authcode'] )
				);

				try {
					// Start async GET request
					$params = array(
						'url'			=> $api_url,
						'callback'		=> array(array($this, 'handleWebrequest'), array('PING', $api_url)),
						'sync'			=> false,
						'user_agent'		=> $this->config['user_agent'],
						'timeout_dns'		=> $this->config['timeout_dns'],
						'timeout_connect'	=> $this->config['timeout_connect'],
						'timeout'		=> $this->config['timeout'],
					);
					$aseco->webrequest->GET($params);
				}
				catch (Exception $exception) {
					$aseco->console('[ManiaKarma] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
				}
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
		if ($this->config['sync_global_karma_local'] === true) {
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
		if ($this->config['karma_lottery']['enabled'] === true) {

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
						if ($this->karma['global']['players'][$player->login]['vote'] !== 0) {
							array_push($lottery_attendant, $player->login);
						}
					}

					// Are enough Players online and has voted?
					if (count($lottery_attendant) >= $this->config['karma_lottery']['minimum_players']) {
						// Drawing of the lottery ("and the winner is")
						$winner = array_rand($lottery_attendant, 1);

						// If the Player is not already gone, go ahead
						if ($player = $aseco->server->players->getPlayerByLogin($lottery_attendant[$winner])) {
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

	public function onEndMapRanking ($aseco, $data) {

		// If there no players, bail out immediately
		if (count($aseco->server->players->player_list) === 0) {
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
		if ( ($this->config['remind_to_vote'] === 'SCORE') || ($this->config['remind_to_vote'] === 'ALWAYS') ) {

			// Check all connected Players
			$players_reminder = array();
			foreach ($aseco->server->players->player_list as $player) {

				// Skip if Player did not finished the map but it is required to vote
				if ( ($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish']) ) {
					continue;
				}

				// Check whether Player already voted
				if ($this->karma['global']['players'][$player->login]['vote'] === 0) {
					$players_reminder[] = $player->login;
					$this->storePlayerData($player, 'ReminderWindow', true);
				}
				else if ($this->config['score_mx_window'] === true) {
					// Show the MX-Link-Window
					$this->showManiaExchangeLinkWindow($player);
				}
			}

			if (count($players_reminder) > 0) {
				if ( ($this->config['reminder_window']['display'] === 'SCORE') || ($this->config['reminder_window']['display'] === 'ALWAYS') ) {
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
		else if ($this->config['score_mx_window'] === true) {
			// Check all connected Players
			foreach ($aseco->server->players->player_list as $player) {

				// Get current Player status and ignore Spectators
				if ($player->is_spectator) {
					continue;
				}

				// Check whether Player already voted
				if ($this->karma['global']['players'][$player->login]['vote'] !== 0) {
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
		if (count($aseco->server->players->player_list) === 0) {
			return;
		}

		$xml  = '';

		// Possible parameters: 'skeleton_race', 'skeleton_score', 'cups_values', 'player_marker', 'hide_window' and 'hide_all'
		foreach ($widgets as $widget) {
			if ($widget === 'hide_all') {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'02" name="Windows" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'06" name="ConnectionStatus" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'07" name="LoadingIndicator" version="3"></manialink>';
				break;
			}

			if ($widget === 'hide_window') {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'02" name="Windows" version="3"></manialink>';
			}

			if (isset($this->config['widget']['states'][$this->config['widget']['current_state']]) && $this->config['widget']['states'][$this->config['widget']['current_state']]['enabled'] === true) {
				if ($widget === 'skeleton_race') {
					$xml .= $this->config['widget']['skeleton']['race'];
				}
				else if ($widget === 'skeleton_score') {
					$xml .= $this->config['widget']['skeleton']['score'];
				}
				else if ($widget === 'cups_values') {
					$xml .= $this->buildKarmaCupsValue($this->config['widget']['current_state']);
				}
				else if ($widget === 'player_marker') {
					$xml .= $this->buildPlayerVoteMarker($player, $this->config['widget']['current_state']);
				}
			}
			else {
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker" version="3"></manialink>';
				$xml .= '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue" version="3"></manialink>';
			}
		}

		if ($player !== false) {
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

		// Bail out on unsupported Gamemodes
		if (!isset($this->config['widget']['states'][$gamemode])) {
			return;
		}

		// No Placeholder here!
		$xml = '<manialink id="'. $this->config['manialink_id'] .'03" name="SkeletonWidget" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';

		// MainWidget Frame
		$xml .= '<frame pos="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .'" z-index="0" id="'. $this->config['manialink_id'] .'03MainFrame">';
		if ($gamemode === 0) {
			// No action to open the full widget at 'Score'
			if ($this->config['widget']['score']['background_color'] !== '') {
				$xml .= '<quad pos="0 0" z-index="0.01" size="39.4 20.15625" bgcolor="'. $this->config['widget']['score']['background_color'] .'"/>';
			}
			else {
				$xml .= '<quad pos="0 0" z-index="0.01" size="39.4 20.15625" style="'. $this->config['widget']['score']['background_style'] .'" substyle="'. $this->config['widget']['score']['background_substyle'] .'"/>';
			}
		}
		else {
			$xml .= '<quad pos="0.25 -0.1875" z-index="0.01" size="38.9 19.78125" action="PluginManiaKarma?Action=OpenKarmaWindow" text=" " bgcolor="'. $this->config['widget']['race']['background_color'] .'" bgcolorfocus="'. $this->config['widget']['race']['background_focus'] .'"/>';
			$xml .= '<quad pos="-0.5 0.5625" z-index="0.02" size="40.4 21.28125" style="'. $this->config['widget']['race']['border_style'] .'" substyle="'. $this->config['widget']['race']['border_substyle'] .'"/>';
			$xml .= '<quad pos="0 0" z-index="0.03" size="39.4 20.15625" style="'. $this->config['widget']['race']['background_style'] .'" substyle="'. $this->config['widget']['race']['background_substyle'] .'"/>';
			if ($this->config['widget']['states'][$gamemode]['pos_x'] > 0) {
				$xml .= '<quad pos="-0.25 -13.875" z-index="0.04" size="8.75 6.5625" image="'. $this->config['images']['widget_open_left'] .'"/>';
			}
			else {
				$xml .= '<quad pos="31.15 -13.875" z-index="0.04" size="8.75 6.5625" image="'. $this->config['images']['widget_open_right'] .'"/>';
			}
		}

		// Vote Frame, different offset on default widget
		$xml .= '<frame pos="0 0" z-index="0.05">';


		// Window title
		if ($gamemode === 0) {
			if ($this->config['widget']['score']['title_background'] !== '') {
				$xml .= '<quad pos="1 -0.75" z-index="0.01" size="37.4 3.75" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" bgcolor="'. $this->config['widget']['score']['title_background'] .'"/>';
			}
			else {
				$xml .= '<quad pos="1 -0.75" z-index="0.01" size="37.4 3.75" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" style="'. $this->config['widget']['score']['title_style'] .'" substyle="'. $this->config['widget']['score']['title_substyle'] .'"/>';
			}
		}
		else {
			if ($this->config['widget']['race']['title_background'] !== '') {
				$xml .= '<quad pos="1 -0.75" z-index="0.01" size="37.4 3.75" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" bgcolor="'. $this->config['widget']['race']['title_background'] .'"/>';
			}
			else {
				$xml .= '<quad pos="1 -0.75" z-index="0.01" size="37.4 3.75" url="http://'. $this->config['urls']['website'] .'/goto?uid='. $aseco->server->maps->current->uid .'&amp;env='. $aseco->server->maps->current->environment .'&amp;game='. $aseco->server->game .'" style="'. $this->config['widget']['race']['title_style'] .'" substyle="'. $this->config['widget']['race']['title_substyle'] .'"/>';
			}
		}

		if ($gamemode === 0) {
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
			$xml .= '<quad pos="3.3 -2.55" z-index="0.02" size="3.75 3.75" halign="center" valign="center2" style="'. $icon_style .'" substyle="'. $icon_substyle .'"/>';
			$xml .= '<label pos="5.45 -1.525" z-index="0.02" size="25 0" class="labels" textsize="1" text="'. $title .'"/>';
		}
		else {
			// Position from icon and title to right
			$xml .= '<quad pos="36.8 -2.55" z-index="0.02" size="3.75 3.75" halign="center" valign="center2" style="'. $icon_style .'" substyle="'. $icon_substyle .'"/>';
			$xml .= '<label pos="34.3 -1.525" z-index="0.02" size="25 0" class="labels" halign="right" textsize="1" text="'. $title .'"/>';
		}

		// BG for Buttons to prevent flicker of the widget background (clickable too, but ignored)
		$xml .= '<frame pos="4.575 -15.5625" z-index="0.9">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.1" size="29.5 2.625" action="PluginManiaKarma?Action=Vote&Value=Ignore" bgcolor="0000"/>';
		$xml .= '</frame>';

		// Button +++
		$xml .= '<frame pos="4.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_positive_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" id="Fantastic" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="+++"/>';
		$xml .= '</frame>';

		// Button ++
		$xml .= '<frame pos="9.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_positive_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" id="Beautiful" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="++"/>';
		$xml .= '</frame>';

		// Button +
		$xml .= '<frame pos="14.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_positive_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_positive_focus'] .'" id="Good" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['positive_text_color'] .'" text="+"/>';
		$xml .= '</frame>';

		// Button -
		$xml .= '<frame pos="19.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_negative_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" id="Bad" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.375" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="-"/>';
		$xml .= '</frame>';

		// Button --
		$xml .= '<frame pos="24.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_negative_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" id="Poor" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.375" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="--"/>';
		$xml .= '</frame>';

		// Button ---
		$xml .= '<frame pos="29.575 -15.9375" z-index="1">';
		$xml .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" bgcolor="'. $this->config['widget']['buttons']['bg_negative_default'] .'" bgcolorfocus="'. $this->config['widget']['buttons']['bg_negative_focus'] .'" id="Waste" scriptevents="1"/>';
		$xml .= '<label pos="2.8 -0.375" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.9" halign="center" textcolor="'. $this->config['widget']['buttons']['negative_text_color'] .'" text="---"/>';
		$xml .= '</frame>';


		$xml .= '</frame>'; // Vote Frame
		$xml .= '</frame>'; // MainWidget Frame

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	KarmaWidget @ plugin.mania_karma.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
 */
main () {
	declare CMlFrame Container <=> (Page.GetFirstChild("{$this->config['manialink_id']}03MainFrame") as CMlFrame);
	Container.RelativeScale = {$this->config['widget']['states'][$gamemode]['scale']};

	while (True) {
		yield;
		if (!PageIsVisible || InputPlayer == Null) {
			continue;
		}

		// Check for MouseEvents
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseClick : {
					TriggerPageAction("PluginManiaKarma?Action=Vote&Value="^ Event.ControlId);
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 0, 1.0);
				}
				case CMlEvent::Type::MouseOver : {
					Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
				}
			}
		}
	}
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
			2,
			2.125,
			2.125,
			2.1875,
			2.25,
			2.3125,
			2.375,
			2.4375,
			2.5,
			2.5625,
		);

		$cup_gold_amount = 0;
		if ($this->karma['global']['votes']['karma'] > 0) {
			if ($this->config['karma_calculation_method'] === 'RASP') {
				$positive = $this->karma['global']['votes']['fantastic']['count'] + $this->karma['global']['votes']['beautiful']['count'] + $this->karma['global']['votes']['good']['count'];
				$cup_gold_amount = round($positive / $this->karma['global']['votes']['total'] * $total_cups);
			}
			else {
				$cup_gold_amount = intval($this->karma['global']['votes']['karma'] / $total_cups);
			}
		}
		else if ($this->karma['local']['votes']['karma'] > 0) {
			if ($this->config['karma_calculation_method'] === 'RASP') {
				$positive = $this->karma['local']['votes']['fantastic']['count'] + $this->karma['local']['votes']['beautiful']['count'] + $this->karma['local']['votes']['good']['count'];
				$cup_gold_amount = round($positive / $this->karma['local']['votes']['total'] * $total_cups);
			}
			else {
				$cup_gold_amount = intval($this->karma['local']['votes']['karma'] / $total_cups);
			}
		}
		$cup_silver = '<quad pos="%x% 0" z-index="%z%" size="%width% %height%" valign="bottom" image="'. $this->config['images']['cup_silver'] .'"/>';
		$cup_gold = '<quad pos="%x% 0" z-index="%z%" size="%width% %height%" valign="bottom" image="'. $this->config['images']['cup_gold'] .'"/>';
		$cups_result = '';
		for ($i = 0 ; $i < $total_cups ; $i ++) {
			$layer = sprintf("0.%02d", ($i+1));
			$width = 2.75 + ($i / $total_cups) * $cup_offset[$i];
			$height = 2.2 + ($i / $total_cups) * $cup_offset[$i];
			if ($i < $cup_gold_amount) {
				$award = $cup_gold;
			}
			else {
				$award = $cup_silver;
			}
			$cups_result .= str_replace(array('%width%', '%height%', '%x%', '%z%'), array($width, $height, ($cup_offset[$i]*$i), $layer), $award);
		}


		$xml  = '<manialink id="'. $this->config['manialink_id'] .'05" name="KarmaCupsValue" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';
		$xml .= '<frame pos="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .'" z-index="0.01" id="'. $this->config['manialink_id'] .'05MainFrame">';

		// Cups
		$xml .= '<frame pos="5.575 -9.5" z-index="0.01">';
		$xml .= $cups_result;
		$xml .= '</frame>';

		// Global Value and Votes
		$globalcolor = 'FFFF';
		if ($this->config['karma_calculation_method'] === 'DEFAULT') {
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
		if ($this->config['karma_calculation_method'] === 'DEFAULT') {
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
		$xml .= '<frame pos="5.25 -10.03125" z-index="0.01">';
		$xml .= '<quad pos="0 -0.1875" z-index="0.01" size="0.25 5.34375" bgcolor="FFF5"/>';
		$xml .= '<label pos="0.75 -0.1875" z-index="0.01" class="labels" size="10 2.0625" textsize="1" scale="0.65" textcolor="FFFF" text="GLOBAL"/>';
		$xml .= '<label pos="8.25 0" z-index="0.01" size="7.5 2.625" class="labels" textsize="1" scale="0.9" textcolor="'. $globalcolor .'" text="$O'. $this->karma['global']['votes']['karma'] .'"/>';
		$xml .= '<label pos="0.75 -2.4375" z-index="0.01" size="16.5 2.25" class="labels" textsize="1" scale="0.85" textcolor="0F3F" text="'. number_format($this->karma['global']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['global']['votes']['total'] === 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';
		$xml .= '</frame>';

		// Local values and votes
		$xml .= '<frame pos="21.875 -10.03125" z-index="0.01">';
		$xml .= '<quad pos="0 -0.1875" z-index="0.01" size="0.25 5.34375" bgcolor="FFF5"/>';
		$xml .= '<label pos="0.75 -0.1875" z-index="0.01" size="10 2.0625" class="labels" textsize="1" scale="0.65" textcolor="FFFF" text="LOCAL "/>';
		$xml .= '<label pos="7.5 0" z-index="0.01" size="7.5 2.625" textsize="1" scale="0.9" textcolor="'. $localcolor .'" text="$O'. $this->karma['local']['votes']['karma'] .'"/>';
		$xml .= '<label pos="0.75 -2.4375" z-index="0.01" size="16.5 2.25" class="labels" textsize="1" scale="0.85" textcolor="0F3F" text="'. number_format($this->karma['local']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['local']['votes']['total'] === 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';
		$xml .= '</frame>';


		$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	KarmaCupsValue @ plugin.mania_karma.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
 */
main () {
	declare CMlFrame Container <=> (Page.GetFirstChild("{$this->config['manialink_id']}05MainFrame") as CMlFrame);
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


		// BEGIN: Global vote frame
		$xml = '<frame pos="0 8" z-index="1">';

		$color = '$FFF';
		if ($this->config['karma_calculation_method'] === 'DEFAULT') {
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
		$xml .= '<label pos="17.5 -12.5" z-index="0.03" size="50 0" class="labels" textsize="2" scale="0.9" text="$FFFGlobal Karma: $O'. $color . $this->karma['global']['votes']['karma'] .'"/>';
		$xml .= '<label pos="87.5 -12.5" z-index="0.03" size="50 0" class="labels" textsize="2" scale="0.9" halign="right" text="$FFF'. number_format($this->karma['global']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['global']['votes']['total'] === 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';


		$xml .= '<label pos="11.75 -21.28125" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="100%"/>';
		$xml .= '<quad pos="13.75 -22.5" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -22.5" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -26.90625" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="90%"/>';
		$xml .= '<quad pos="13.75 -28.125" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -28.125" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -32.53125" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="80%"/>';
		$xml .= '<quad pos="13.75 -33.75" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -33.75" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -38.15625" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="70%"/>';
		$xml .= '<quad pos="13.75 -39.375" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -39.375" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -43.78125" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="60%"/>';
		$xml .= '<quad pos="13.75 -45" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -45" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -49.40625" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="50%"/>';
		$xml .= '<quad pos="13.75 -50.625" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -50.625" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -55.03125" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="40%"/>';
		$xml .= '<quad pos="13.75 -56.25" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -56.25" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -60.65625" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="30%"/>';
		$xml .= '<quad pos="13.75 -61.875" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -61.875" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -66.28125" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="20%"/>';
		$xml .= '<quad pos="13.75 -67.5" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -67.5" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -71.90625" z-index="0.03" size="7.5 0" class="labels" halign="right" scale="0.8" text="10%"/>';
		$xml .= '<quad pos="13.75 -73.125" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -73.125" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<quad pos="17.75 -78.75" z-index="0.04" size="70 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.5 -22.5" z-index="0.03" size="0.25 56.25" bgcolor="FFFD"/>';

		$height['fantastic']	= (($this->karma['global']['votes']['fantastic']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['fantastic']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['beautiful']	= (($this->karma['global']['votes']['beautiful']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['beautiful']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['good']		= (($this->karma['global']['votes']['good']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['good']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['bad']		= (($this->karma['global']['votes']['bad']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['bad']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['poor']		= (($this->karma['global']['votes']['poor']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['poor']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['waste']	= (($this->karma['global']['votes']['waste']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['global']['votes']['waste']['percent'] / 3.3333333333 * 1.875)) : 0);

		$xml .= '<label pos="25.5 -'. (75 - $height['fantastic']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['fantastic']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="36.75 -'. (75 - $height['beautiful']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['beautiful']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="48 -'. (75 - $height['good']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['good']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad pos="25 -'. (78.75 - $height['fantastic']) .'" z-index="0.02" size="10 '. $height['fantastic'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad pos="36.25 -'. (78.75 - $height['beautiful']) .'" z-index="0.02" size="10 '. $height['beautiful'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad pos="47.5 -'. (78.75 - $height['good']) .'" z-index="0.02" size="10 '. $height['good'] .'" halign="center" bgcolor="170F"/>';

		$xml .= '<quad pos="25 -'. (78.75 - $height['fantastic']) .'" z-index="0.03" size="10 '. $height['fantastic'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad pos="36.25 -'. (78.75 - $height['beautiful']) .'" z-index="0.03" size="10 '. $height['beautiful'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad pos="47.5 -'. (78.75 - $height['good']) .'" z-index="0.03" size="10 '. $height['good'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';

		$xml .= '<label pos="59.25 -'. (75 - $height['bad']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['bad']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="70.5 -'. (75 - $height['poor']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['poor']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="81.75 -'. (75 - $height['waste']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['global']['votes']['waste']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad pos="58.75 -'. (78.75 - $height['bad']) .'" z-index="0.02" size="10 '. $height['bad'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad pos="70 -'. (78.75 - $height['poor']) .'" z-index="0.02" size="10 '. $height['poor'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad pos="81.25 -'. (78.75 - $height['waste']) .'" z-index="0.02" size="10 '. $height['waste'] .'" halign="center" bgcolor="701F"/>';

		$xml .= '<quad pos="58.75 -'. (78.75 - $height['bad']) .'" z-index="0.03" size="10 '. $height['bad'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad pos="70 -'. (78.75 - $height['poor']) .'" z-index="0.03" size="10 '. $height['poor'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad pos="81.25 -'. (78.75 - $height['waste']) .'" z-index="0.03" size="10 '. $height['waste'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';

		$xml .= '<label pos="7.5 -80.625" z-index="0.03" size="15 0" class="labels" text="Votes:"/>';

		$xml .= '<label pos="25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['fantastic']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="36.25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['beautiful']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="47.5 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['good']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="58.75 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['bad']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="70 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['poor']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="81.25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['global']['votes']['waste']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';

		$xml .= '<label pos="25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_fantastic']) .'"/>';
		$xml .= '<label pos="36.25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_beautiful']) .'"/>';
		$xml .= '<label pos="47.5 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_good']) .'"/>';
		$xml .= '<label pos="58.75 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_bad']) .'"/>';
		$xml .= '<label pos="70 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_poor']) .'"/>';
		$xml .= '<label pos="81.25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_waste']) .'"/>';

		$xml .= '<label pos="25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0+++"/>';
		$xml .= '<label pos="36.25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0++"/>';
		$xml .= '<label pos="47.5 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0+"/>';
		$xml .= '<label pos="58.75 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02-"/>';
		$xml .= '<label pos="70 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02--"/>';
		$xml .= '<label pos="81.25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02---"/>';

		$xml .= '</frame>';
		// END: Global vote frame





		// BEGIN: Local vote frame
		$xml .= '<frame pos="105 8" z-index="1">';

		$color = '$FFF';
		if ($this->config['karma_calculation_method'] === 'DEFAULT') {
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
		$xml .= '<label pos="17.5 -12.5" z-index="0.03" size="50 0" class="labels" textsize="2" scale="0.9" text="$FFFLocal Karma: $O'. $color . $this->karma['local']['votes']['karma'] .'"/>';
		$xml .= '<label pos="87.5 -12.5" z-index="0.03" size="50 0" class="labels" textsize="2" scale="0.9" halign="right" text="$FFF'. number_format($this->karma['local']['votes']['total'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .' '. (($this->karma['local']['votes']['total'] === 1) ? $this->config['messages']['karma_vote_singular'] : $this->config['messages']['karma_vote_plural']) .'"/>';

		$xml .= '<label pos="11.75 -21.28125" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="100%"/>';
		$xml .= '<quad pos="13.75 -22.5" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -22.5" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -26.90625" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="90%"/>';
		$xml .= '<quad pos="13.75 -28.125" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -28.125" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -32.53125" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="80%"/>';
		$xml .= '<quad pos="13.75 -33.75" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -33.75" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -38.15625" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="70%"/>';
		$xml .= '<quad pos="13.75 -39.375" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -39.375" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -43.78125" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="60%"/>';
		$xml .= '<quad pos="13.75 -45" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -45" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -49.40625" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="50%"/>';
		$xml .= '<quad pos="13.75 -50.625" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -50.625" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -55.03125" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="40%"/>';
		$xml .= '<quad pos="13.75 -56.25" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -56.25" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -60.65625" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="30%"/>';
		$xml .= '<quad pos="13.75 -61.875" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -61.875" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -66.28125" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="20%"/>';
		$xml .= '<quad pos="13.75 -67.5" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -67.5" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<label pos="11.75 -71.90625" z-index="0.03" size="7.5 0" halign="right" class="labels" scale="0.8" text="10%"/>';
		$xml .= '<quad pos="13.75 -73.125" z-index="0.04" size="3.75 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.75 -73.125" z-index="0.04" size="70 0.1875" bgcolor="FFF5"/>';

		$xml .= '<quad pos="17.75 -78.75" z-index="0.04" size="70 0.1875" bgcolor="FFFD"/>';
		$xml .= '<quad pos="17.5 -22.5" z-index="0.03" size="0.25 56.25" bgcolor="FFFD"/>';

		$height['fantastic']	= (($this->karma['local']['votes']['fantastic']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['fantastic']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['beautiful']	= (($this->karma['local']['votes']['beautiful']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['beautiful']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['good']		= (($this->karma['local']['votes']['good']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['good']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['bad']		= (($this->karma['local']['votes']['bad']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['bad']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['poor']		= (($this->karma['local']['votes']['poor']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['poor']['percent'] / 3.3333333333 * 1.875)) : 0);
		$height['waste']	= (($this->karma['local']['votes']['waste']['percent'] !== 0) ? sprintf("%.2f", ($this->karma['local']['votes']['waste']['percent'] / 3.3333333333 * 1.875)) : 0);

		$xml .= '<label pos="25.5 -'. (75 - $height['fantastic']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['fantastic']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="36.75 -'. (75 - $height['beautiful']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['beautiful']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="48 -'. (75 - $height['good']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['good']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad pos="25 -'. (78.75 - $height['fantastic']) .'" z-index="0.02" size="10 '. $height['fantastic'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad pos="36.25 -'. (78.75 - $height['beautiful']) .'" z-index="0.02" size="10 '. $height['beautiful'] .'" halign="center" bgcolor="170F"/>';
		$xml .= '<quad pos="47.5 -'. (78.75 - $height['good']) .'" z-index="0.02" size="10 '. $height['good'] .'" halign="center" bgcolor="170F"/>';

		$xml .= '<quad pos="25 -'. (78.75 - $height['fantastic']) .'" z-index="0.03" size="10 '. $height['fantastic'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad pos="36.25 -'. (78.75 - $height['beautiful']) .'" z-index="0.03" size="10 '. $height['beautiful'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';
		$xml .= '<quad pos="47.5 -'. (78.75 - $height['good']) .'" z-index="0.03" size="10 '. $height['good'] .'" halign="center" style="BgRaceScore2" substyle="CupFinisher"/>';

		$xml .= '<label pos="59.25 -'. (75 - $height['bad']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['bad']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="70.5 -'. (75 - $height['poor']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['poor']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';
		$xml .= '<label pos="81.75 -'. (75 - $height['waste']) .'" z-index="0.06" size="9.5 0" halign="center" class="labels" scale="0.8" text="'. number_format($this->karma['local']['votes']['waste']['percent'], 2, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'%"/>';

		$xml .= '<quad pos="58.75 -'. (78.75 - $height['bad']) .'" z-index="0.02" size="10 '. $height['bad'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad pos="70 -'. (78.75 - $height['poor']) .'" z-index="0.02" size="10 '. $height['poor'] .'" halign="center" bgcolor="701F"/>';
		$xml .= '<quad pos="81.25 -'. (78.75 - $height['waste']) .'" z-index="0.02" size="10 '. $height['waste'] .'" halign="center" bgcolor="701F"/>';

		$xml .= '<quad pos="58.75 -'. (78.75 - $height['bad']) .'" z-index="0.03" size="10 '. $height['bad'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad pos="70 -'. (78.75 - $height['poor']) .'" z-index="0.03" size="10 '. $height['poor'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';
		$xml .= '<quad pos="81.25 -'. (78.75 - $height['waste']) .'" z-index="0.03" size="10 '. $height['waste'] .'" halign="center" style="BgRaceScore2" substyle="CupPotentialFinisher"/>';


		$xml .= '<label pos="7.5 -80.625" z-index="0.03" size="15 0" class="labels" text="Votes:"/>';

		$xml .= '<label pos="25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['fantastic']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="36.25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['beautiful']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="47.5 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['good']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="58.75 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['bad']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="70 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['poor']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';
		$xml .= '<label pos="81.25 -80.625" z-index="0.03" size="25 0" halign="center" class="labels" text="'. number_format($this->karma['local']['votes']['waste']['count'], 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) .'"/>';

		$xml .= '<label pos="25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_fantastic']) .'"/>';
		$xml .= '<label pos="36.25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_beautiful']) .'"/>';
		$xml .= '<label pos="47.5 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$6C0'. ucfirst($this->config['messages']['karma_good']) .'"/>';
		$xml .= '<label pos="58.75 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_bad']) .'"/>';
		$xml .= '<label pos="70 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_poor']) .'"/>';
		$xml .= '<label pos="81.25 -84.46875" z-index="0.03" size="25 0" halign="center" class="labels" scale="0.8" text="$F02'. ucfirst($this->config['messages']['karma_waste']) .'"/>';

		$xml .= '<label pos="25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0+++"/>';
		$xml .= '<label pos="36.25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0++"/>';
		$xml .= '<label pos="47.5 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$6C0+"/>';
		$xml .= '<label pos="58.75 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02-"/>';
		$xml .= '<label pos="70 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02--"/>';
		$xml .= '<label pos="81.25 -86.34375" z-index="0.03" size="25 0" halign="center" class="labels" text="$F02---"/>';

		$xml .= '</frame>';
		// END: Local vote frame



		// BEGIN: Place Player marker, if Player has already voted
		if ( isset($this->karma['global']['players'][$login]) ) {
			// BEGIN: Global vote frame
			$xml .= '<frame pos="0 -80.5" z-index="1">';
			if ($this->karma['global']['players'][$login]['vote'] === 3) {
				$xml .= '<quad pos="25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] === 2) {
				$xml .= '<quad pos="36.25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="36.25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] === 1) {
				$xml .= '<quad pos="47.5 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="47.5 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -1) {
				$xml .= '<quad pos="58.75 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="58.75 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -2) {
				$xml .= '<quad pos="70 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="70 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -3) {
				$xml .= '<quad pos="81.25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="81.25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			$xml .= '</frame>';
			// END: Global vote frame
		}

		if ( isset($this->karma['local']['players'][$login]) ) {
			// BEGIN: Local vote frame
			$xml .= '<frame pos="105 -80.5" z-index="1">';
			if ($this->karma['local']['players'][$login]['vote'] === 3) {
				$xml .= '<quad pos="25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] === 2) {
				$xml .= '<quad pos="36.25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="36.25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] === 1) {
				$xml .= '<quad pos="47.5 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="47.5 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] === -1) {
				$xml .= '<quad pos="58.75 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="58.75 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] === -2) {
				$xml .= '<quad pos="70 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="70 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			else if ($this->karma['local']['players'][$login]['vote'] === -3) {
				$xml .= '<quad pos="81.25 0" z-index="0.05" size="5.25 5.25" halign="center" style="Icons64x64_1" substyle="YellowHigh"/>';
				$xml .= '<label pos="81.25 -5.2" z-index="0.03" size="15 0" class="labels" halign="center" textsize="1" scale="0.85" textcolor="FFFF" text="Your vote"/>';
			}
			$xml .= '</frame>';
			// END: Local vote frame
		}
		// END: Place Player marker

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildWhoKarmaWindow () {
		global $aseco;

		$xml = '<frame pos="0 0" z-index="0.05">';

		$xml .= '<quad pos="49.5 -1" z-index="0.02" size="0.2 88" bgcolor="FFFFFF33"/>';
		$xml .= '<quad pos="99.5 -1" z-index="0.02" size="0.2 88" bgcolor="FFFFFF33"/>';
		$xml .= '<quad pos="149.5 -1" z-index="0.02" size="0.2 88" bgcolor="FFFFFF33"/>';

		$players = array();
		foreach ($aseco->server->players->player_list as $player) {
			$players[] = array(
				'id'		=> $player->id,
				'nickname'	=> $this->handleSpecialChars($player->nickname),
				'vote'		=> (($this->karma['global']['players'][$player->login]['vote'] === 0) ? -4 : $this->karma['global']['players'][$player->login]['vote']),
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
		$xml .= '<frame pos="0 -1.2" z-index="0.05">';
		foreach ($players as $player) {
			$xml .= '<label pos="'. (2 + $offset) .' -'. (3.53 * $line) .'" z-index="0.04" size="7 3.3" class="labels" scale="0.9" text="'. $vote_index[$player['vote']] .'"/>';
			$xml .= '<label pos="'. (9 + $offset) .' -'. (3.53 * $line) .'" z-index="0.04" size="38.5 3.3" class="labels" text="'. $player['nickname'] .'"/>';
			$line ++;
			$rank ++;

			// Reset lines
			if ($line >= 25) {
				$offset += 50;
				$line = 0;
			}

			// Display max. 100 entries, count start from 1
			if ($rank >= 101) {
				break;
			}
		}
		$xml .= '</frame>';

		$xml .= '</frame>';

		return $xml;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function buildPlayerVoteMarker ($player, $gamemode) {

		// Bail out if Player is already disconnected
		if (!isset($player->login) || !isset($this->karma['global']['players'][$player->login])) {
			return;
		}

		// Build the colors for Player vote marker (RGBA)
		$preset = array();
		$preset['fantastic']['bgcolor']		= '0000';
		$preset['fantastic']['action']		= 'RequireFinish';
		$preset['beautiful']['bgcolor']		= '0000';
		$preset['beautiful']['action']		= 'RequireFinish';
		$preset['good']['bgcolor']		= '0000';
		$preset['good']['action']		= 'RequireFinish';
		$preset['bad']['bgcolor']		= '0000';
		$preset['bad']['action']		= 'RequireFinish';
		$preset['poor']['bgcolor']		= '0000';
		$preset['poor']['action']		= 'RequireFinish';
		$preset['waste']['bgcolor']		= '0000';
		$preset['waste']['action']		= 'RequireFinish';

		// Fantastic
		if ($this->karma['global']['players'][$player->login]['vote'] === 3) {
			$preset['fantastic']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['fantastic']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['fantastic']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Beautiful
		if ($this->karma['global']['players'][$player->login]['vote'] === 2) {
			$preset['beautiful']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['beautiful']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['beautiful']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Good
		if ($this->karma['global']['players'][$player->login]['vote'] === 1) {
			$preset['good']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['good']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['good']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}


		// Bad
		if ($this->karma['global']['players'][$player->login]['vote'] === -1) {
			$preset['bad']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['bad']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['bad']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Poor
		if ($this->karma['global']['players'][$player->login]['vote'] === -2) {
			$preset['poor']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['poor']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['poor']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}

		// Waste
		if ($this->karma['global']['players'][$player->login]['vote'] === -3) {
			$preset['waste']['bgcolor'] = $this->config['widget']['buttons']['bg_disabled'];
			$preset['waste']['action'] = 'Ignore';
		}
		else if ( ($this->karma['global']['players'][$player->login]['vote'] === 0) && (($this->config['require_finish'] > 0) && ($this->getPlayerData($player, 'FinishedMapCount') < $this->config['require_finish'])) ) {
			$preset['waste']['bgcolor'] = $this->config['widget']['buttons']['bg_vote'];
		}


		// Init Marker
		$marker = false;


		// Button +++
		if ($preset['fantastic']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="4.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['fantastic']['action'] .'" bgcolor="'. $preset['fantastic']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="+++"/>';
			$marker .= '</frame>';
		}

		// Button ++
		if ($preset['beautiful']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="9.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['beautiful']['action'] .'" bgcolor="'. $preset['beautiful']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="++"/>';
			$marker .= '</frame>';
		}

		// Button +
		if ($preset['good']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="14.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['good']['action'] .'" bgcolor="'. $preset['good']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="+"/>';
			$marker .= '</frame>';
		}

		// Button -
		if ($preset['bad']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="19.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['bad']['action'] .'" bgcolor="'. $preset['bad']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="-"/>';
			$marker .= '</frame>';
		}

		// Button --
		if ($preset['poor']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="24.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['poor']['action'] .'" bgcolor="'. $preset['poor']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="--"/>';
			$marker .= '</frame>';
		}

		// Button ---
		if ($preset['waste']['bgcolor'] !== '0000') {
			// Mark current vote or disable the vote possibility
			$marker .= '<frame pos="29.575 -15.9375" z-index="1">';
			$marker .= '<quad pos="0.5 -0.15" z-index="0.3" size="4.5 2.625" action="PluginManiaKarma?Action=Vote&Value='. $preset['waste']['action'] .'" bgcolor="'. $preset['waste']['bgcolor'] .'"/>';
			$marker .= '<label pos="2.8 -0.5625" z-index="0.4" size="4.5 0" class="labels" textsize="1" scale="0.8" halign="center" textcolor="'. $this->config['widget']['buttons']['text_disabled'] .'" text="---"/>';
			$marker .= '</frame>';
		}


		$xml = '<manialink id="'. $this->config['manialink_id'] .'04" name="PlayerVoteMarker" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';

		// Send/Build MainWidget Frame only when required, if empty then the player can vote
		if ($marker !== false) {
			$xml .= '<frame pos="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .'" z-index="0.01" id="'. $this->config['manialink_id'] .'04MainFrame">';
			$xml .= $marker;
			$xml .= '</frame>';
$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	PlayerVoteMarker @ plugin.mania_karma.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
 */
main () {
	declare CMlFrame Container <=> (Page.GetFirstChild("{$this->config['manialink_id']}04MainFrame") as CMlFrame);
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

	public function sendConnectionStatus ($status, $gamemode) {
		global $aseco;

		$xml = '<manialink id="'. $this->config['manialink_id'] .'06" name="ConnectionStatus" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';
		if ($status === false) {
			$this->sendLoadingIndicator(false, $gamemode);
			$xml .= '<frame pos="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .'" z-index="0.03">';
			$xml .= '<quad pos="1.25 -9.75" z-index="0.04" size="3.5 3.5" style="Icons64x64_2" substyle="Disconnected" id="ManiaKarmaTooltipIcon" ScriptEvents="1"/>';
			$xml .= '<label pos="-1 -9.75" z-index="0.04" size="50 2.625" class="labels" textsize="1" halign="right" scale="0.8" text="Connection failed, retrying shortly." hidden="true" id="ManiaKarmaTooltipMessage"/>';
			$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	ConnectionStatus @ plugin.mania_karma.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
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

	public function sendLoadingIndicator ($status, $gamemode) {
		global $aseco;

		// Bail out on unsupported Gamemodes
		if (!isset($this->config['widget']['states'][$gamemode])) {
			return;
		}

		$xml = '<manialink id="'. $this->config['manialink_id'] .'07" name="LoadingIndicator" version="3">';
		$xml .= '<stylesheet>';
		$xml .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$xml .= '</stylesheet>';
		if ($status === true) {
			$xml .= '<frame pos="'. $this->config['widget']['states'][$gamemode]['pos_x'] .' '. $this->config['widget']['states'][$gamemode]['pos_y'] .'" z-index="0.03">';
			$xml .= '<quad pos="1.25 -9.75" z-index="0.04" size="3.5 3.5" image="'. $this->config['images']['progress_indicator'] .'" id="ManiaKarmaTooltipIcon" ScriptEvents="1"/>';
			$xml .= '<label pos="-1 -9.75" z-index="0.04" size="50 2.625" class="labels" textsize="1" halign="right" scale="0.8" text="Loading global votes." hidden="true" id="ManiaKarmaTooltipMessage"/>';
			$xml .= '</frame>';

$maniascript = <<<EOL
<script><!--
 /*
 * ==================================
 * Function:	LoadingIndicator @ plugin.mania_karma.php
 * Author:	undef.de
 * Website:	http://www.undef.name
 * License:	GPLv3
 * ==================================
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
		if ($aseco->startup_phase === true) {
			return;
		}


		// Close reminder-window if there is one for this Player
		$this->closeReminderWindow($player);


//		// $vote is "0" when the Player clicks on a red (no vote possible) or blue marked (same vote) button,
//		// in both situation we bail out now.
//		if ($vote === 0) {
//			return;
//		}


		// Check if finishes are required
		if ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] > $this->getPlayerData($player, 'FinishedMapCount')) ) {

			// Show chat message
			$message = $aseco->formatText($this->config['messages']['karma_require_finish'],
						$this->config['require_finish'],
						($this->config['require_finish'] === 1 ? '' : 's')
			);
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) && ($this->config['widget']['current_state'] !== 0) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
			return;
		}


		// Before call the remote API, check if player has the same already voted
		if ($this->karma['global']['players'][$player->login]['vote'] === $vote) {
			// Same vote, does not need to call remote API, bail out immediately
			$message = $this->config['messages']['karma_voted'];
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
			return;
		}


		// Store the new vote for send them later with "MultiVote",
		// but only if the global is different to the current vote
		if ( (isset($this->karma['global']['players'][$player->login]['vote'])) && ($this->karma['global']['players'][$player->login]['vote'] !== $vote) ) {
			$this->karma['new']['players'][$player->login] = $vote;
		}


	//	// Check if connection was failed
	//	if ($this->config['retrytime'] === 0) {
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
	//	if ($this->config['retrytime'] === 0) {
			$this->karma['global']['players'][$player->login]['vote'] = $vote;
	//	}
		$this->karma['local']['players'][$player->login]['vote'] = $vote;


	//	// Check if connection was failed
	//	if ($this->config['retrytime'] === 0) {
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
	//	if ($this->config['retrytime'] === 0) {
			// Update the global/local $this->karma
			$this->calculateKarma(array('global','local'));
	//	}
	//	else {
	//		// Update only the local $this->karma
	//		$this->calculateKarma(array('local'));
	//	}


		// Show the MX-Link-Window (if enabled and we are at Score)
		if ($this->config['score_mx_window'] === true) {
			$this->showManiaExchangeLinkWindow($player);
		}


		// Tell the player the result for his/her vote
		if ($this->karma['global']['players'][$player->login]['previous'] === 0) {
			$message = $aseco->formatText($this->config['messages']['karma_done'], $aseco->server->maps->current->name_stripped);
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}

		}
		else if ($this->karma['global']['players'][$player->login]['previous'] !== $vote) {
			$message = $aseco->formatText($this->config['messages']['karma_change'], $aseco->server->maps->current->name_stripped);
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// Show Map Karma (with details?)
		$message = $this->createKarmaMessage($player->login, false);
		if ($message !== false) {
			if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
				send_window_message($aseco, $message, $player);
			}
			else {
				$aseco->sendChatMessage($message, $player->login);
			}
		}


		// Update the KarmaWidget for given Player
		$this->sendWidgetCombination(array('player_marker'), $player);


		// Should all other player (except the vote given player) be informed/asked?
		if ($this->config['show_player_vote_public'] === true) {
			$logins = array();
			foreach ($aseco->server->players->player_list as $pl) {

				// Don't ask/tell the player that give the vote
				if ($pl->login === $player->login) {
					continue;
				}

				// Don't ask/tell Players they did not reached the <require_finish> limit
				if ( ($this->config['require_finish'] > 0) && ($this->config['require_finish'] > $this->getPlayerData($pl, 'FinishedMapCount')) ) {
					continue;
				}

				// Don't ask/tell players if she/he has already voted!
				if ($this->karma['global']['players'][$pl->login]['vote'] !== 0) {
					continue;
				}

				// Don't ask/tell Spectator's
				if ($pl->is_spectator) {
					continue;
				}

				// All other becomes this message.
				$logins[] = $pl->login;
			}

			// Build the message and send out
			if ($vote === 1) {
				$player_voted = $this->config['messages']['karma_good'];
			}
			else if ($vote === 2) {
				$player_voted = $this->config['messages']['karma_beautiful'];
			}
			else if ($vote === 3) {
				$player_voted = $this->config['messages']['karma_fantastic'];
			}
			else if ($vote === -1) {
				$player_voted = $this->config['messages']['karma_bad'];
			}
			else if ($vote === -2) {
				$player_voted = $this->config['messages']['karma_poor'];
			}
			else if ($vote === -3) {
				$player_voted = $this->config['messages']['karma_waste'];
			}
			$message = $aseco->formatText($this->config['messages']['karma_show_opinion'],
					$aseco->stripStyles($player->nickname),
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
		if ($message !== false) {
			if ($login) {
				if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
					if ($player = $aseco->server->players->getPlayerByLogin($login)) {
						send_window_message($aseco, $message, $player);
					}
				}
				else {
					$aseco->sendChatMessage($message, $login);
				}
			}
			else {
				if ( ($this->config['messages_in_window'] === true) && (function_exists('send_window_message')) ) {
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
		if ( ($this->config['show_karma'] === true) || ($force_display === true) ) {
			$message = $aseco->formatText($this->config['messages']['karma_message'],
				$aseco->server->maps->current->name_stripped,
				$this->karma['global']['votes']['karma']
			);
		}

		// Optionally show player's actual vote
		if ( ($this->config['show_votes'] === true) || ($force_display === true) ) {
			if ($this->karma['global']['players'][$login]['vote'] === 1) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_good'], '/+');
			}
			else if ($this->karma['global']['players'][$login]['vote'] === 2) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_beautiful'], '/++');
			}
			else if ($this->karma['global']['players'][$login]['vote'] === 3) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_fantastic'], '/+++');
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -1) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_bad'], '/-');
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -2) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_poor'], '/--');
			}
			else if ($this->karma['global']['players'][$login]['vote'] === -3) {
				$message .= $aseco->formatText($this->config['messages']['karma_your_vote'], $this->config['messages']['karma_waste'], '/---');
			}
			else {
				$message .= $this->config['messages']['karma_not_voted'];
			}

		}

		// Optionally show vote counts & percentages
		if ( ($this->config['show_details'] === true) || ($force_display === true) ) {
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
	public function showUndecidedMessage ($caller) {
		global $aseco;

		// Should all other player (except the vote given player) be informed/asked?
		if ($this->config['show_player_vote_public'] === true) {
			foreach ($aseco->server->players->player_list as $player) {

				// Show only to players that did not voted yet
				if ($this->karma['global']['players'][$player->login]['vote'] === 0) {
					// Don't ask/tell the player that give the undecided vote
					if ($player->login === $caller->login) {
						continue;
					}

					// Don't ask/tell Spectator's
					if ($player->is_spectator) {
						continue;
					}

					$message = $aseco->formatText($this->config['messages']['karma_show_undecided'],
							$aseco->stripStyles($caller->nickname)
					);
					$message = str_replace('{br}', LF, $message);  // split long message
					$aseco->sendChatMessage($message, $player->login);
				}
			}
		}

		// Close reminder-window if there is one for this Player
		if ($this->getPlayerData($caller, 'ReminderWindow') === true) {
			$this->closeReminderWindow($caller);
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
		if ($this->config['widget']['current_state'] === 0) {
			$gamestate = 'score';
		}

		$content = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow" version="3">';
		$content .= '<stylesheet>';
		$content .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$content .= '</stylesheet>';
		$content .= '<frame pos="'. $this->config['reminder_window'][$gamestate]['pos_x'] .' '. $this->config['reminder_window'][$gamestate]['pos_y'] .'" z-index="0">';
		$content .= '<quad pos="0 0" z-index="0.01" size="196 8" bgcolor="032942DD"/>';
		$content .= '<label pos="41.25 -1.5" z-index="0.02" size="45 3.375" class="labels" textsize="2" scale="0.8" halign="right" text="'. $this->config['messages']['karma_reminder_at_score'] .'"/>';
		$content .= '<label pos="41.25 -4.6875" z-index="0.02" size="45 0.375" class="labels" textsize="1" scale="0.8" halign="right" text="powered by mania-karma.com"/>';

		$content .= '<frame pos="48 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Fantastic" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton1" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_fantastic']) .'"/>';
		$content .= '<label pos="10 -3.375" z-index="0.03" size="25 5.25" class="labels" halign="center" textsize="1" text="$390+++"/>';
		$content .= '</frame>';

		$content .= '<frame pos="69 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Beautiful" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton2" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_beautiful']) .'"/>';
		$content .= '<label pos="10 -3.375" z-index="0.03" size="25 5.25" class="labels" halign="center" textsize="1" text="$390++"/>';
		$content .= '</frame>';

		$content .= '<frame pos="90 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Good" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton3" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$390'. strtoupper($this->config['messages']['karma_good']) .'"/>';
		$content .= '<label pos="10 -3.375" z-index="0.03" size="25 5.25" class="labels" halign="center" textsize="1" text="$390+"/>';
		$content .= '</frame>';

		$content .= '<frame pos="111 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Undecided" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton4" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$888'. strtoupper($this->config['messages']['karma_undecided']) .'"/>';
		$content .= '<label pos="10 -3.75" z-index="0.03" size="25 5.25" class="labels" halign="center" textsize="1" scale="0.7" text="$888???"/>';
		$content .= '</frame>';

		$content .= '<frame pos="132 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Bad" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton5" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_bad']) .'"/>';
		$content .= '<label pos="10 -3.1875" z-index="0.03" size="35 5.25" class="labels" halign="center" textsize="1" text="$D02-"/>';
		$content .= '</frame>';

		$content .= '<frame pos="153 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Poor" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton6" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_poor']) .'"/>';
		$content .= '<label pos="10 -3.1875" z-index="0.03" size="35 5.25" class="labels" halign="center" textsize="1" text="$D02--"/>';
		$content .= '</frame>';

		$content .= '<frame pos="174 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.02" size="19.75 5.64375" action="PluginManiaKarma?Action=Vote&Value=Waste" bgcolor="FFFFFFFF" bgcolorfocus="FFFFFFDD" id="ManiaKarmaButton7" ScriptEvents="1"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.03" size="17.75 5.25" class="labels" halign="center" textsize="1" text="$D02'. strtoupper($this->config['messages']['karma_waste']) .'"/>';
		$content .= '<label pos="10 -3.1875" z-index="0.03" size="35 5.25" class="labels" halign="center" textsize="1" text="$D02---"/>';
		$content .= '</frame>';

		$content .= '</frame>';

$maniascript = <<<EOL
<script><!--
main () {
	declare CMlFrame Container <=> (Page.GetFirstChild("FrameReminderWindow") as CMlFrame);
	while (True) {
		yield;
		foreach (Event in PendingEvents) {
			switch (Event.Type) {
				case CMlEvent::Type::MouseOver : {
					if (
						Event.ControlId == "ManiaKarmaButton1" ||
						Event.ControlId == "ManiaKarmaButton2" ||
						Event.ControlId == "ManiaKarmaButton3" ||
						Event.ControlId == "ManiaKarmaButton4" ||
						Event.ControlId == "ManiaKarmaButton5" ||
						Event.ControlId == "ManiaKarmaButton6" ||
						Event.ControlId == "ManiaKarmaButton7"

					) {
						Audio.PlaySoundEvent(CAudioManager::ELibSound::Valid, 2, 1.0);
					}
				}
			}
		}
	}
}
--></script>
EOL;
		$content .= $maniascript;
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
		if ($this->config['widget']['current_state'] !== 0) {
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
		if ($this->config['widget']['current_state'] === 0) {
			$gamestate = 'score';
		}

		$content = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow" version="3">';
		$content .= '<stylesheet>';
		$content .= '<style class="labels" textsize="1" scale="1" textcolor="FFFF"/>';
		$content .= '</stylesheet>';
		$content .= '<frame pos="'. $this->config['reminder_window'][$gamestate]['pos_x'] .' '. $this->config['reminder_window'][$gamestate]['pos_y'] .'" z-index="0">';
		$content .= '<quad pos="0 0" z-index="0.01" size="196 8" bgcolor="032942DD"/>';
		$content .= '<label pos="41.25 -1.5" z-index="0.02" size="45 3.375" class="labels" textsize="2" scale="0.8" halign="right" text="'. $this->config['messages']['karma_you_have_voted'] .'"/>';
		$content .= '<label pos="41.25 -4.6875" z-index="0.02" size="45 0.375" class="labels" textsize="1" scale="0.8" halign="right" text="powered by mania-karma.com"/>';

		$content .= '<frame pos="48 -0.84375" z-index="0.02">';
		$content .= '<quad pos="0 -0.375" z-index="0.01" size="20.025 5.64375" bgcolor="FFFFFFFF"/>';
		$content .= '<label pos="10 -0.9375" z-index="0.02" size="18.75 5.25" class="labels" textsize="1" halign="center" text="'. $voted .'"/>';
		$content .= '<label pos="10 -3.375" z-index="0.02" size="25 5.25" class="labels" textsize="1" halign="center" text="'. $cmd .'"/>';
		$content .= '</frame>';

		if (isset($aseco->server->maps->current->mx->pageurl)) {
			// Show link direct to the last map
			$content .= '<frame pos="82.5 -0.375" z-index="0.02">';
			$content .= '<label pos="101.25 -2.4375" z-index="0.01" class="labels" size="125 0" halign="right" textsize="1" text="Visit &#187; '. preg_replace('/\$S/i', '', $aseco->handleSpecialChars($aseco->server->maps->current->name)) .'$Z$FFF &#171; at"/>';
			$content .= '<quad pos="103.125 0.15" z-index="0.01" class="labels" size="7.5 7.5" image="'. $this->config['images']['mx_logo_normal'] .'" imagefocus="'. $this->config['images']['mx_logo_focus'] .'" url="'. $aseco->handleSpecialChars($aseco->server->maps->current->mx->pageurl) .'"/>';
			$content .= '</frame>';
		}
		else {
			// Show link to tm.mania-exchange.com
			$content .= '<frame pos="82.5 -0.375" z-index="0.02">';
			$content .= '<quad pos="103.125 0.15" z-index="0.01" size="7.5 7.5" image="'. $this->config['images']['mx_logo_normal'] .'" imagefocus="'. $this->config['images']['mx_logo_focus'] .'" url="http:tm.mania-exchange.com/"/>';
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
		if (count($aseco->server->players->player_list) === 0) {
			return;
		}

		// Build the Manialink
		$xml = '<manialink id="'. $this->config['manialink_id'] .'01" name="ReminderWindow" version="3"></manialink>';

		if ($player !== false) {
			if ($this->getPlayerData($player, 'ReminderWindow') === true) {
				$aseco->sendManialink($xml, $player->login, 0, false);
				$this->storePlayerData($player, 'ReminderWindow', false);
			}
		}
		else {
			// Reset state at all Players
			foreach ($aseco->server->players->player_list as $player) {
				if ($this->getPlayerData($player, 'ReminderWindow') === true) {
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

	public function buildHelpAboutWindow ($message) {

		$xml = '<frame pos="0 0" z-index="1">';
		$xml .= '<quad pos="142.5 7.5" z-index="0.05" size="57.5 57.5" image="'. $this->config['images']['maniakarma_logo'] .'" url="http://www.mania-karma.com"/>';
		$xml .= '<label pos="2.5 -2.5" z-index="0.05" size="142.5 0" class="labels" autonewline="1" textsize="1" textcolor="FF0F" text="'. $message .'"/>';
		$xml .= '</frame>';

		return $xml;
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

			if ($this->config['retrytime'] === 0) {

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
				$api_url = sprintf("%s?Action=Vote&login=%s&authcode=%s&uid=%s&map=%s&author=%s&atime=%s&nblaps=%s&nbchecks=%s&mood=%s&env=%s&votes=%s&tmx=%s",
					$this->config['urls']['api'],
					urlencode( $this->config['account']['login'] ),
					urlencode( $this->config['account']['authcode'] ),
					urlencode( $this->karma['data']['uid'] ),
					base64_encode( $this->karma['data']['name'] ),
					urlencode( $this->karma['data']['author'] ),
					$aseco->server->maps->current->author_time,
					urlencode( $aseco->server->maps->current->nb_laps ),
					urlencode( $aseco->server->maps->current->nb_checkpoints ),
					urlencode( $aseco->server->maps->current->mood ),
					urlencode( $this->karma['data']['env'] ),
					implode('|', $pairs),
					$this->karma['data']['tmx']
				);

				try {
					// Start async GET request
					$params = array(
						'url'			=> $api_url,
						'callback'		=> array(array($this, 'handleWebrequest'), array('VOTE', $api_url)),
						'sync'			=> false,
						'user_agent'		=> $this->config['user_agent'],
						'timeout_dns'		=> $this->config['timeout_dns'],
						'timeout_connect'	=> $this->config['timeout_connect'],
						'timeout'		=> $this->config['timeout'],
					);
					$aseco->webrequest->GET($params);
				}
				catch (Exception $exception) {
					$aseco->console('[ManiaKarma] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
				}
			}


			// Check if karma should saved local also
			if ($this->config['save_karma_also_local'] === true) {

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
						$playerid = $aseco->server->players->getPlayerIdByLogin($login);
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
		if ($source === 'local') {
			$destination = 'global';
		}

		// Bailout if unset
		if ( !isset($this->karma[$source]['players']) ) {
			return;
		}


		$found = false;
		foreach ($this->karma[$source]['players'] as $login => $votes) {
			// Skip "no vote" (value "0") from sync
			if ($votes['vote'] === 0) {
				continue;
			}

			// Is the votes are different, then replace $source with the $destination vote
			if ( (isset($this->karma[$destination]['players'][$login])) && ($this->karma[$destination]['players'][$login]['vote'] !== $votes['vote']) ) {

				// Set to true to rebuild the $destination Karma and the Widget (Cups/Values)
				$found = true;

				// Set the $destination to the $source vote
				$this->karma[$destination]['players'][$login]['vote'] = $votes['vote'];

				// Set the sync'd vote as a new vote to store them into the database at onLoadingMap
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

		if ($found === true) {
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
		if (count($aseco->server->players->player_list) === 0) {
//			$this->sendConnectionStatus(true, $this->config['widget']['current_state']);
//			$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
			return;
		}

		// Bail out if map id was not found
		if ($map->id === 0) {
//			$this->sendConnectionStatus(true, $this->config['widget']['current_state']);
//			$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
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

		try {
			// Start async GET request
			$params = array(
				'url'			=> $api_url,
				'callback'		=> array(array($this, 'handleWebrequest'), array('GET', $api_url)),
				'sync'			=> false,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> $this->config['timeout_dns'],
				'timeout_connect'	=> $this->config['timeout_connect'],
				'timeout'		=> $this->config['timeout'],
			);
			$aseco->webrequest->GET($params);
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}

		// Return an empty set, get replaced with $this->handleWebrequest()
		return;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function handleWebrequest ($request, $params) {
		global $aseco;

		$target = false;
		if (isset($params[0])) {
			$type = $params[0];
		}
		if (isset($params[1])) {
			$url = $params[1];
		}
		if (isset($params[2])) {
			$target = $params[2];
		}

		if ($request->response['header']['code'] === 200) {
			if ($type === 'GET') {
				// Read the request
				if (!$xml = @simplexml_load_string($request->response['content'], null, LIBXML_COMPACT) ) {
					$aseco->console('[ManiaKarma] handleWebrequest() on type "'. $type .'": Could not read/parse request from mania-karma.com "'. $request->response['content'] .'"!');
					$this->config['retrytime'] = (time() + $this->config['retrywait']);
					$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
					$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
				}
				else {
					$this->sendConnectionStatus(true, $this->config['widget']['current_state']);

					if ((int)$xml->status === 200) {

						$this->karma['global']['votes']['fantastic']['percent']		= (float)$xml->votes->fantastic['percent'];
						$this->karma['global']['votes']['fantastic']['count']		= (int)$xml->votes->fantastic['count'];
						$this->karma['global']['votes']['beautiful']['percent']		= (float)$xml->votes->beautiful['percent'];
						$this->karma['global']['votes']['beautiful']['count']		= (int)$xml->votes->beautiful['count'];
						$this->karma['global']['votes']['good']['percent']		= (float)$xml->votes->good['percent'];
						$this->karma['global']['votes']['good']['count']		= (int)$xml->votes->good['count'];

						$this->karma['global']['votes']['bad']['percent']		= (float)$xml->votes->bad['percent'];
						$this->karma['global']['votes']['bad']['count']			= (int)$xml->votes->bad['count'];
						$this->karma['global']['votes']['poor']['percent']		= (float)$xml->votes->poor['percent'];
						$this->karma['global']['votes']['poor']['count']		= (int)$xml->votes->poor['count'];
						$this->karma['global']['votes']['waste']['percent']		= (float)$xml->votes->waste['percent'];
						$this->karma['global']['votes']['waste']['count']		= (int)$xml->votes->waste['count'];

						$this->karma['global']['votes']['karma']			= (int)$xml->votes->karma;
						$this->karma['global']['votes']['total']			= ($this->karma['global']['votes']['fantastic']['count'] + $this->karma['global']['votes']['beautiful']['count'] + $this->karma['global']['votes']['good']['count'] + $this->karma['global']['votes']['bad']['count'] + $this->karma['global']['votes']['poor']['count'] + $this->karma['global']['votes']['waste']['count']);

						// Insert the votes for every Player
						foreach ($aseco->server->players->player_list as $player) {
							foreach ($xml->players->player as $pl) {
								if ($player->login === $pl['login']) {
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
									if ( ($player->login === $pl['login']) && ((int)$pl['vote'] !== 0) ) {
										// Set the state of finishing this map, if not already has a setup of a !== 0 value
										if ($this->getPlayerData($player, 'FinishedMapCount') === 0) {
											$this->storePlayerData($player, 'FinishedMapCount', 9999);
										}
									}
								}
							}
						}

						// Check to see if it is required to sync global to local votes?
						if ($this->config['sync_global_karma_local'] === true) {
							$this->syncGlobaAndLocalVotes('local', true);
						}

						// Now sync local votes to global votes (e.g. on connection lost...)
						$this->syncGlobaAndLocalVotes('global', true);

						if ($this->config['karma_calculation_method'] === 'RASP') {
							// Update the global/local $this->karma
							$this->calculateKarma(array('global','local'));
						}

						// Display the Karma value of Map?
						if ($this->config['show_at_start'] === true) {
							// Show players' actual votes, or global karma message?
							if ($this->config['show_votes'] === true) {
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
							if (strtoupper($xml->image_present) === 'FALSE') {
								$this->transmitMapImage();
							}
						}
					}
					else {
						$aseco->console('[ManiaKarma] handleWebrequest() on type "'. $type .'": Connection failed with "'. $xml->status .'" for url ['. $url .']');
						$this->config['retrytime'] = (time() + $this->config['retrywait']);
						$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
						$this->sendLoadingIndicator(false, $this->config['widget']['current_state']);
					}

					if ($target === false) {
						// Update KarmaWidget for all connected Players
						if ($this->config['widget']['current_state'] === 0) {
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
						if ($this->config['widget']['current_state'] === 0) {
							$this->sendWidgetCombination(array('skeleton_score', 'cups_values', 'player_marker'), $target);
						}
						else {
							$this->sendWidgetCombination(array('skeleton_race', 'cups_values', 'player_marker'), $target);
						}
					}
				}
			}
			else if ($type === 'VOTE') {
				// Read the request
				if ($xml = @simplexml_load_string($request->response['content'], null, LIBXML_COMPACT) ) {
					if ((int)$xml->status !== 200) {
						$aseco->console('[ManiaKarma] handleWebrequest() on type "'. $type .'":  Storing votes failed with returncode "'. $xml->status .'"');
					}
					unset($xml);
				}
				else {
					$aseco->console('[ManiaKarma] handleWebrequest() on type "'. $type .'": Could not read/parse request from mania-karma.com "'. $request->response['content'] .'"!');
					$this->config['retrytime'] = (time() + $this->config['retrywait']);
					$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
				}
			}
			else if ($type === 'UPTODATE') {
				// Read the request
				if ($xml = @simplexml_load_string($request->response['content'], null, LIBXML_COMPACT) ) {
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
						if ($this->config['uptodate_info'] === 'DEFAULT') {
							$message = $aseco->formatText($this->config['messages']['uptodate_ok'],
								$this->getVersion()
							);
							$aseco->sendChatMessage($message, $target->login);
						}
					}
				}
				else {
					$aseco->sendChatMessage($this->config['messages']['uptodate_failed'], $target->login);
					$aseco->console('[ManiaKarma] handleWebrequest() on type "'. $type .'": Could not read/parse xml request!');
					$this->config['retrytime'] = (time() + $this->config['retrywait']);
					$this->sendConnectionStatus(false, $this->config['widget']['current_state']);
				}
			}
			else if ($type === 'EXPORT') {
				if ($request->response['header']['code'] === 200) {
					$this->config['import_done'] = true;		// Set to true, otherwise only after restart UASECO knows that
					$message = '{#server}» {#admin}Export done. Thanks for supporting mania-karma.com!';
					$aseco->sendChatMessage($message, $target->login);
				}
				else if ($request->response['header']['code'] === 406) {
					$message = '{#server}» {#error}Export rejected! Please check your <login> and <nation> in config file "config/mania_karma.xml"!';
					$aseco->sendChatMessage($message, $target->login);
				}
				else if ($request->response['header']['code'] === 409) {
					$message = '{#server}» {#error}Export rejected! Export was already done, allowed only one time!';
					$aseco->sendChatMessage($message, $target->login);
				}
				else {
					$message = '{#server}» {#error}Connection failed with '. $request->response['header']['code'] .' ('. $request->response['header']['code_text'] .') for url ['. $api_url .']' ."\n\r";
					$aseco->sendChatMessage($message, $target->login);
				}
			}
			else if ($type === 'PING') {
				$this->config['retrytime'] = 0;
			}
			else if ($type === 'STOREIMAGE') {
				// Do nothing
			}
		}
		else {
			$aseco->console('[ManiaKarma] handleWebrequest(): For type "'. $type .'" connection failed with "'. $request->response['header']['code'] .' - '. $request->response['header']['code_text'] .'" for url ['. $url .']');
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
						if ($player->login === $row->login) {
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
		if ($MapId === false) {
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
		if ($MapId === false) {
			return;
		}


		// Build the Player votes Array
		$logins = array();
		if ($login === false) {
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

		if ($login === false) {
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
				if ($this->karma['local']['players'][$player->login]['vote'] !== 0) {
					// Set the state of finishing this map, if not already has a setup of a !== 0 value
					if ($this->getPlayerData($player, 'FinishedMapCount') === 0) {
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

		$api_url = 'http://'. $this->config['urls']['website'] .'/api/plugin-releases.xml';
		try {
			// Start async GET request
			$params = array(
				'url'			=> $api_url,
				'callback'		=> array(array($this, 'handleWebrequest'), array('UPTODATE', $api_url, $player)),
				'sync'			=> false,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> $this->config['timeout_dns'],
				'timeout_connect'	=> $this->config['timeout_connect'],
				'timeout'		=> $this->config['timeout'],
			);
			$aseco->webrequest->GET($params);
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->GET(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
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
			if ($totalvotes === 0) {
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

			if ($this->config['karma_calculation_method'] === 'RASP') {
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

		if ($this->config['import_done'] !== false) {
			$message = "{#server}» {#admin}Export of local votes already done, skipping...";
			$aseco->sendChatMessage($message, $player->login);
			return;
		}

		$message = "{#server}» {#admin}Collecting players with their votes on Maps...";
		$aseco->sendChatMessage($message, $player->login);

		// Generate the content for this export
		$csv = false;
		$query = "
		SELECT
			`m`.`Uid`,
			`m`.`Name`,
			`a`.`Login` AS `AuthorLogin`,
			`m`.`Environment`,
			`p`.`Login` AS `PlayerLogin`,
			`rs`.`Score` AS `PlayerVote`
		FROM `%prefix%ratings` AS `rs`
		LEFT JOIN `%prefix%maps` AS `m` ON `m`.`MapId`=`rs`.`MapId`
		LEFT JOIN `%prefix%authors` AS `a` ON `a`.`AuthorId`=`m`.`AuthorId`
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
							$row->AuthorLogin,
							$row->Environment,
							$this->config['account']['login'],
							$this->config['account']['authcode'],
							$this->config['account']['nation'],
							$row->PlayerLogin,
							$row->PlayerVote
						);
					}
					$count ++;
				}

				$message = "{#server}» {#admin}Found ". number_format($count, 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) ." votes in database.";
				$aseco->sendChatMessage($message, $player->login);
			}
			$res->free_result();
		}

		// gzip the CSV
		$message = "{#server}» {#admin}Compressing collected data...";
		$aseco->sendChatMessage($message, $player->login);
		$csv = gzencode($csv, 9, FORCE_GZIP);


		// Encode them Base64
		$message = "{#server}» {#admin}Encoding data...";
		$aseco->sendChatMessage($message, $player->login);
		$csv = base64_encode($csv);


		$message = "{#server}» {#admin}Sending now the export with size of ". number_format(strlen($csv), 0, $this->config['NumberFormat'][$this->config['number_format']]['decimal_sep'], $this->config['NumberFormat'][$this->config['number_format']]['thousands_sep']) ." bytes...";
		$aseco->sendChatMessage($message, $player->login);

		// Generate the url for the Import-Request
		$api_url = sprintf("%s?Action=Import&login=%s&authcode=%s&nation=%s",
			$this->config['urls']['api'],
			urlencode( $this->config['account']['login'] ),
			urlencode( $this->config['account']['authcode'] ),
			urlencode( $this->config['account']['nation'] )
		);

		try {
			// Start async POST request
			$params = array(
				'url'			=> $api_url,
				'callback'		=> array(array($this, 'handleWebrequest'), array('EXPORT', $api_url, $player)),
				'data'			=> $csv,
				'sync'			=> false,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> $this->config['timeout_dns'],
				'timeout_connect'	=> $this->config['timeout_connect'],
				'timeout'		=> $this->config['timeout'],
			);
			$aseco->webrequest->POST($params);
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->POST(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
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
			if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN') {
				$gbx->processFile($aseco->server->mapdir . iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $aseco->stripBOM($aseco->server->maps->current->filename)));
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

		try {
			// Start async POST request
			$params = array(
				'url'			=> $api_url,
				'callback'		=> array(array($this, 'handleWebrequest'), array('STOREIMAGE', $api_url)),
				'data'			=> base64_encode($gbx->thumbnail),
				'sync'			=> false,
				'user_agent'		=> $this->config['user_agent'],
				'timeout_dns'		=> $this->config['timeout_dns'],
				'timeout_connect'	=> $this->config['timeout_connect'],
				'timeout'		=> $this->config['timeout'],
			);
			$aseco->webrequest->POST($params);
		}
		catch (Exception $exception) {
			$aseco->console('[ManiaKarma] webrequest->POST(): '. $exception->getCode() .' - '. $exception->getMessage() ."\n". $exception->getTraceAsString(), E_USER_WARNING);
		}
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
}

?>
