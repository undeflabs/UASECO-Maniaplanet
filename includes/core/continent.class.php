<?php
/*
 * Class: Continent
 * ~~~~~~~~~~~~~~~~
 * » Provides lists of Continents and converter methods.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-24
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
 *  - none
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Continent {

	// Continent constants
	const EUROPE		= 'EU';
	const AFRICA		= 'AF';
	const ASIA		= 'AS';
	const MIDDLE_EAST	= 'ME';
	const NORTH_AMERICA	= 'NA';
	const SOUTH_AMERICA	= 'SA';
	const OCEANIA		= 'OC';


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Convert continent to abbreviation
	public function continentToAbbr ($continent) {

		switch ($continent) {
			case 'Europe':
				return self::EUROPE;

			case 'Africa':
				return self::AFRICA;

			case 'Asia':
				return self::ASIA;

			case 'Middle East':
				return self::MIDDLE_EAST;

			case 'North America':
				return self::NORTH_AMERICA;

			case 'South America':
				return self::SOUTH_AMERICA;

			case 'Oceania':
				return self::OCEANIA;

			default:
				return '';
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// Convert abbreviation to continent
	public function abbrToContinent ($abbreviation) {

		switch ($abbreviation) {
			case self::EUROPE:
				return 'Europe';

			case self::AFRICA:
				return 'Africa';

			case self::ASIA:
				return 'Asia';

			case self::MIDDLE_EAST:
				return 'Middle East';

			case self::NORTH_AMERICA:
				return 'North America';

			case self::SOUTH_AMERICA:
				return 'South America';

			case self::OCEANIA:
				return 'Oceania';

			default:
				return '';
		}
	}
}

?>
