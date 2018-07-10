<?php
/*
 * Class: Country
 * ~~~~~~~~~~~~~~
 * » Provides lists of Countries and IOC codes, and converter methods.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * Documentation:
 * » http://en.wikipedia.org/wiki/List_of_IOC_country_codes
 * » http://en.wikipedia.org/wiki/Comparison_of_IOC,_FIFA,_and_ISO_3166_country_codes
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


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Country extends BaseClass {
	public $country_list = array();
	public $ioc_list = array();

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct () {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2018-06-13');
		$this->setCopyright('2014 - 2018 by undef.de');
		$this->setDescription('Provides lists of Countries and IOC codes, and converter methods.');

		$this->country_list = array(
			'Afghanistan'			=> 'AFG',
			'Albania'			=> 'ALB',
			'Algeria'			=> 'ALG',
			'Andorra'			=> 'AND',
			'Angola'			=> 'ANG',
			'Antigua and Barbuda'		=> 'ANT',
			'Argentina'			=> 'ARG',
			'Armenia'			=> 'ARM',
			'Aruba'				=> 'ARU',	// 2018-06-13: missing ARU.dds
			'Australia'			=> 'AUS',
			'Austria'			=> 'AUT',
			'Azerbaijan'			=> 'AZE',
			'Bahamas'			=> 'BAH',
			'Bahrain'			=> 'BRN',
			'Bangladesh'			=> 'BAN',
			'Barbados'			=> 'BAR',
			'Belarus'			=> 'BLR',
			'Belgium'			=> 'BEL',
			'Belize'			=> 'BIZ',
			'Benin'				=> 'BEN',
			'Bermuda'			=> 'BER',		// 2018-06-13: missing BER.dds
			'Bhutan'			=> 'BHU',
			'Bolivia'			=> 'BOL',
			'Bosnia&Herzegovina'		=> 'BIH',
			'Bosnia and Herzegovina'	=> 'BIH',
			'Botswana'			=> 'BOT',
			'Brazil'			=> 'BRA',
			'Brunei'			=> 'BRU',
			'Bulgaria'			=> 'BUL',
			'Burkina Faso'			=> 'BUR',
			'Burundi'			=> 'BDI',
			'Cambodia'			=> 'CAM',
			'Cameroon'			=> 'CAR',		// actually CMR
			'Canada'			=> 'CAN',
			'Cape Verde'			=> 'CPV',
			'Central African Republic'	=> 'CAF',
			'Chad'				=> 'CHA',
			'Chile'				=> 'CHI',
			'China'				=> 'CHN',
			'Chinese Taipei'		=> 'TPE',
			'Colombia'			=> 'COL',
			'Congo'				=> 'COG',		// actually IOC:CGO ISO:COG, COG.dds exists
			'Costa Rica'			=> 'CRC',
			'Croatia'			=> 'CRO',
			'Cuba'				=> 'CUB',
			'Cyprus'			=> 'CYP',
			'Czech Republic'		=> 'CZE',
			'Czech republic'		=> 'CZE',
			'DR Congo'			=> 'COD',
			'Denmark'			=> 'DEN',
			'Djibouti'			=> 'DJI',
			'Dominica'			=> 'DMA',
			'Dominican Republic'		=> 'DOM',
			'Ecuador'			=> 'ECU',
			'Egypt'				=> 'EGY',
			'El Salvador'			=> 'ESA',
			'Eritrea'			=> 'ERI',
			'Estonia'			=> 'EST',
			'Ethiopia'			=> 'ETH',
			'Fiji'				=> 'FIJ',
			'Finland'			=> 'FIN',
			'France'			=> 'FRA',
			'Gabon'				=> 'GAB',
			'Gambia'			=> 'GAM',
			'Georgia'			=> 'GEO',
			'Germany'			=> 'GER',
			'Ghana'				=> 'GHA',
			'Greece'			=> 'GRE',
			'Grenada'			=> 'GRN',
			'Guam'				=> 'GUM',
			'Guatemala'			=> 'GUA',
			'Guinea'			=> 'GUI',
			'Guinea-Bissau'			=> 'GBS',
			'Guyana'			=> 'GUY',
			'Haiti'				=> 'HAI',
			'Honduras'			=> 'HON',
			'Hong Kong'			=> 'HKG',
			'Hungary'			=> 'HUN',
			'Iceland'			=> 'ISL',
			'India'				=> 'IND',
			'Indonesia'			=> 'INA',
			'Iran'				=> 'IRI',
			'Iraq'				=> 'IRQ',
			'Ireland'			=> 'IRL',
			'Israel'			=> 'ISR',
			'Italy'				=> 'ITA',
			'Ivory Coast'			=> 'CIV',
			'Jamaica'			=> 'JAM',
			'Japan'				=> 'JPN',
			'Jordan'			=> 'JOR',
			'Kazakhstan'			=> 'KAZ',
			'Kenya'				=> 'KEN',
			'Kiribati'			=> 'KIR',
			'South Korea'			=> 'KOR',
			'Kuwait'			=> 'KUW',
			'Kyrgyzstan'			=> 'KGZ',
			'Laos'				=> 'LAO',
			'Latvia'			=> 'LAT',
			'Lebanon'			=> 'LIB',
			'Lesotho'			=> 'LES',
			'Liberia'			=> 'LBR',
			'Libya'				=> 'LBA',
			'Liechtenstein'			=> 'LIE',
			'Lithuania'			=> 'LTU',
			'Luxembourg'			=> 'LUX',
			'Macedonia'			=> 'MKD',
			'Malawi'			=> 'MAW',
			'Malaysia'			=> 'MAS',
			'Mali'				=> 'MLI',
			'Malta'				=> 'MLT',
			'Mauritania'			=> 'MTN',
			'Mauritius'			=> 'MRI',
			'Mexico'			=> 'MEX',
			'Moldova'			=> 'MDA',
			'Monaco'			=> 'MON',
			'Mongolia'			=> 'MGL',
			'Montenegro'			=> 'MNE',
			'Morocco'			=> 'MAR',
			'Mozambique'			=> 'MOZ',
			'Myanmar'			=> 'MYA',
			'Namibia'			=> 'NAM',
			'Nauru'				=> 'NRU',
			'Nepal'				=> 'NEP',
			'Netherlands'			=> 'NED',
			'New Zealand'			=> 'NZL',
			'Nicaragua'			=> 'NCA',
			'Niger'				=> 'NIG',
			'Nigeria'			=> 'NGR',
			'Norway'			=> 'NOR',
			'Oman'				=> 'OMA',
			'Other Countries'		=> 'OTH',
			'Pakistan'			=> 'PAK',
			'Palau'				=> 'PLW',
			'Palestine'			=> 'PLE',
			'Panama'			=> 'PAN',
			'Paraguay'			=> 'PAR',
			'Peru'				=> 'PER',
			'Philippines'			=> 'PHI',
			'Poland'			=> 'POL',
			'Portugal'			=> 'POR',
			'Puerto Rico'			=> 'PUR',
			'Qatar'				=> 'QAT',
			'Romania'			=> 'ROM',		// actually ROU
			'Russia'			=> 'RUS',
			'Rwanda'			=> 'RWA',
			'Samoa'				=> 'SAM',
			'San Marino'			=> 'SMR',
			'Saudi Arabia'			=> 'KSA',
			'Senegal'			=> 'SEN',
			'Serbia'			=> 'SCG',		// actually SRB
			'Sierra Leone'			=> 'SLE',
			'Singapore'			=> 'SIN',
			'Slovakia'			=> 'SVK',
			'Slovenia'			=> 'SLO',
			'Somalia'			=> 'SOM',
			'South Africa'			=> 'RSA',
			'Spain'				=> 'ESP',
			'Sri Lanka'			=> 'SRI',
			'Sudan'				=> 'SUD',
			'Suriname'			=> 'SUR',
			'Swaziland'			=> 'SWZ',
			'Sweden'			=> 'SWE',
			'Switzerland'			=> 'SUI',
			'Syria'				=> 'SYR',
			'Taiwan'			=> 'TWN',
			'Tajikistan'			=> 'TJK',
			'Tanzania'			=> 'TAN',
			'Thailand'			=> 'THA',
			'Togo'				=> 'TOG',
			'Tonga'				=> 'TGA',
			'Trinidad and Tobago'		=> 'TRI',
			'Tunisia'			=> 'TUN',
			'Turkey'			=> 'TUR',
			'Turkmenistan'			=> 'TKM',
			'Tuvalu'			=> 'TUV',
			'Uganda'			=> 'UGA',
			'Ukraine'			=> 'UKR',
			'United Arab Emirates'		=> 'UAE',
			'United Kingdom'		=> 'GBR',
			'United States'			=> 'USA',
			'United States of America'	=> 'USA',
			'Uruguay'			=> 'URU',
			'Uzbekistan'			=> 'UZB',
			'Vanuatu'			=> 'VAN',
			'Venezuela'			=> 'VEN',
			'Vietnam'			=> 'VIE',
			'Yemen'				=> 'YEM',
			'Zambia'			=> 'ZAM',
			'Zimbabwe'			=> 'ZIM',

			'Other'				=> 'WOR',	// WOR.dds
			'Other Countries'		=> 'WOR',	// Europe
			'Other Countries (AF)'		=> 'WOR',	// Africa
			'Other Countries (AS)'		=> 'WOR',	// Asia
			'Other Countries (ME)'		=> 'WOR',	// Middle East
			'Other Countries (NA)'		=> 'WOR',	// North America
			'Other Countries (OC)'		=> 'WOR',	// Oceania
			'Other Countries (SA)'		=> 'WOR',	// South America
		);

		$this->ioc_list = array(
			'AFG'	=> 'Afghanistan',
			'ALB'	=> 'Albania',
			'ALG'	=> 'Algeria',
			'AND'	=> 'Andorra',
			'ANG'	=> 'Angola',
			'ANT'	=> 'Antigua and Barbuda',
			'ARG'	=> 'Argentina',
			'ARM'	=> 'Armenia',
			'ARU'	=> 'Aruba',
			'AUS'	=> 'Australia',
			'AUT'	=> 'Austria',
			'AZE'	=> 'Azerbaijan',
			'BAH'	=> 'Bahamas',
			'BRN'	=> 'Bahrain',
			'BAN'	=> 'Bangladesh',
			'BAR'	=> 'Barbados',
			'BLR'	=> 'Belarus',
			'BEL'	=> 'Belgium',
			'BIZ'	=> 'Belize',
			'BEN'	=> 'Benin',
			'BER'	=> 'Bermuda',
			'BHU'	=> 'Bhutan',
			'BOL'	=> 'Bolivia',
			'BIH'	=> 'Bosnia and Herzegovina',
			'BOT'	=> 'Botswana',
			'BRA'	=> 'Brazil',
			'BRU'	=> 'Brunei',
			'BUL'	=> 'Bulgaria',
			'BUR'	=> 'Burkina Faso',
			'BDI'	=> 'Burundi',
			'CAM'	=> 'Cambodia',
			'CAR'	=> 'Cameroon',					// deprecated
			'CMR'	=> 'Cameroon',					// actually CMR
			'CAN'	=> 'Canada',
			'CPV'	=> 'Cape Verde',
			'CAF'	=> 'Central African Republic',
			'CHA'	=> 'Chad',
			'CHI'	=> 'Chile',
			'CHN'	=> 'China',
			'TPE'	=> 'Chinese Taipei',
			'COL'	=> 'Colombia',
			'CGO'	=> 'Congo',					// IOC
			'COG'	=> 'Congo',					// ISO
			'CRC'	=> 'Costa Rica',
			'CRO'	=> 'Croatia',
			'CUB'	=> 'Cuba',
			'CYP'	=> 'Cyprus',
			'CZE'	=> 'Czech Republic',				// 'Czech republic' removed
			'COD'	=> 'DR Congo',
			'DEN'	=> 'Denmark',
			'DJI'	=> 'Djibouti',
			'DMA'	=> 'Dominica',
			'DOM'	=> 'Dominican Republic',
			'ECU'	=> 'Ecuador',
			'EGY'	=> 'Egypt',
			'ESA'	=> 'El Salvador',
			'ERI'	=> 'Eritrea',
			'EST'	=> 'Estonia',
			'ETH'	=> 'Ethiopia',
			'FIJ'	=> 'Fiji',
			'FIN'	=> 'Finland',
			'FRA'	=> 'France',
			'GAB'	=> 'Gabon',
			'GAM'	=> 'Gambia',
			'GEO'	=> 'Georgia',
			'GER'	=> 'Germany',
			'GHA'	=> 'Ghana',
			'GRE'	=> 'Greece',
			'GRN'	=> 'Grenada',
			'GUM'	=> 'Guam',
			'GUA'	=> 'Guatemala',
			'GUI'	=> 'Guinea',
			'GBS'	=> 'Guinea-Bissau',
			'GUY'	=> 'Guyana',
			'HAI'	=> 'Haiti',
			'HON'	=> 'Honduras',
			'HKG'	=> 'Hong Kong',
			'HUN'	=> 'Hungary',
			'ISL'	=> 'Iceland',
			'IND'	=> 'India',
			'INA'	=> 'Indonesia',
			'IRI'	=> 'Iran',
			'IRQ'	=> 'Iraq',
			'IRL'	=> 'Ireland',
			'ISR'	=> 'Israel',
			'ITA'	=> 'Italy',
			'CIV'	=> 'Ivory Coast',
			'JAM'	=> 'Jamaica',
			'JPN'	=> 'Japan',
			'JOR'	=> 'Jordan',
			'KAZ'	=> 'Kazakhstan',
			'KEN'	=> 'Kenya',
			'KIR'	=> 'Kiribati',
			'KOR'	=> 'South Korea',
			'KUW'	=> 'Kuwait',
			'KGZ'	=> 'Kyrgyzstan',
			'LAO'	=> 'Laos',
			'LAT'	=> 'Latvia',
			'LIB'	=> 'Lebanon',
			'LES'	=> 'Lesotho',
			'LBR'	=> 'Liberia',
			'LBA'	=> 'Libya',
			'LIE'	=> 'Liechtenstein',
			'LTU'	=> 'Lithuania',
			'LUX'	=> 'Luxembourg',
			'MKD'	=> 'Macedonia',
			'MAW'	=> 'Malawi',
			'MAS'	=> 'Malaysia',
			'MLI'	=> 'Mali',
			'MLT'	=> 'Malta',
			'MTN'	=> 'Mauritania',
			'MRI'	=> 'Mauritius',
			'MEX'	=> 'Mexico',
			'MDA'	=> 'Moldova',
			'MON'	=> 'Monaco',
			'MGL'	=> 'Mongolia',
			'MNE'	=> 'Montenegro',
			'MAR'	=> 'Morocco',
			'MOZ'	=> 'Mozambique',
			'MYA'	=> 'Myanmar',
			'NAM'	=> 'Namibia',
			'NRU'	=> 'Nauru',
			'NEP'	=> 'Nepal',
			'NED'	=> 'Netherlands',
			'NZL'	=> 'New Zealand',
			'NCA'	=> 'Nicaragua',
			'NIG'	=> 'Niger',
			'NGR'	=> 'Nigeria',
			'NOR'	=> 'Norway',
			'OMA'	=> 'Oman',
			'OTH'	=> 'Other Countries',
			'WOR'	=> 'Other Countries',				// WOR.dds
			'PAK'	=> 'Pakistan',
			'PLW'	=> 'Palau',
			'PLE'	=> 'Palestine',
			'PAN'	=> 'Panama',
			'PAR'	=> 'Paraguay',
			'PER'	=> 'Peru',
			'PHI'	=> 'Philippines',
			'POL'	=> 'Poland',
			'POR'	=> 'Portugal',
			'PUR'	=> 'Puerto Rico',
			'QAT'	=> 'Qatar',
			'ROM'	=> 'Romania',					// deprecated
			'ROU'	=> 'Romania',					// was ROM
			'RUS'	=> 'Russia',
			'RWA'	=> 'Rwanda',
			'SAM'	=> 'Samoa',
			'SMR'	=> 'San Marino',
			'KSA'	=> 'Saudi Arabia',
			'SEN'	=> 'Senegal',
			'SCG'	=> 'Serbia',					// deprecated
			'SRB'	=> 'Serbia',					// was SCG
			'SLE'	=> 'Sierra Leone',
			'SIN'	=> 'Singapore',
			'SVK'	=> 'Slovakia',
			'SLO'	=> 'Slovenia',
			'SOM'	=> 'Somalia',
			'RSA'	=> 'South Africa',
			'ESP'	=> 'Spain',
			'SRI'	=> 'Sri Lanka',
			'SUD'	=> 'Sudan',
			'SUR'	=> 'Suriname',
			'SWZ'	=> 'Swaziland',
			'SWE'	=> 'Sweden',
			'SUI'	=> 'Switzerland',
			'SYR'	=> 'Syria',
			'TWN'	=> 'Taiwan',
			'TJK'	=> 'Tajikistan',
			'TAN'	=> 'Tanzania',
			'THA'	=> 'Thailand',
			'TOG'	=> 'Togo',
			'TGA'	=> 'Tonga',
			'TRI'	=> 'Trinidad and Tobago',
			'TUN'	=> 'Tunisia',
			'TUR'	=> 'Turkey',
			'TKM'	=> 'Turkmenistan',
			'TUV'	=> 'Tuvalu',
			'UGA'	=> 'Uganda',
			'UKR'	=> 'Ukraine',
			'UAE'	=> 'United Arab Emirates',
			'GBR'	=> 'United Kingdom',
			'USA'	=> 'United States',
			'URU'	=> 'Uruguay',
			'UZB'	=> 'Uzbekistan',
			'VAN'	=> 'Vanuatu',
			'VEN'	=> 'Venezuela',
			'VIE'	=> 'Vietnam',
			'YEM'	=> 'Yemen',
			'ZAM'	=> 'Zambia',
			'ZIM'	=> 'Zimbabwe',
		);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Map country names to 3-letter Nation abbreviations
	public function countryToIoc ($country) {

		if (array_key_exists(ucfirst($country), $this->country_list)) {
			$nation = $this->country_list[ucfirst($country)];
		}
		else {
			$nation = 'OTH';
			if ($country !== '') {
				trigger_error('Country::countryToIoc(): Could not map country: '. $country, E_USER_WARNING);
			}
		}
		return $nation;

	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Map 3-letter Nation abbreviation to country names
	public function iocToCountry ($ioc) {

		if (array_key_exists($ioc, $this->ioc_list)) {
			$country = $this->ioc_list[$ioc];
		}
		else {
			$country = 'Other Countries';
			if ($ioc !== '') {
				trigger_error('Country::iocToCountry(): Could not map IOC: '. $ioc, E_USER_WARNING);
			}
		}
		return $country;

	}
}

?>
