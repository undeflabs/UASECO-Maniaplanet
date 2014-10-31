<?php
/*
 * Class: Map
 * ~~~~~~~~~~
 * » Stores information about a Map on the dedicated server.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2014-10-04
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

class Map {
	public $id;							// Database id
	public $uid;
	public $filename;
	public $name;
	public $name_stripped;
	public $comment;

	public $author;
	public $author_nickname;
	public $author_zone;
	public $author_continent;
	public $author_nation;

	public $author_score;
	public $author_time;
	public $goldtime;
	public $silvertime;
	public $bronzetime;

	public $nblaps;
	public $multilap;
	public $nbcheckpoints;

	public $cost;
	public $environment;
	public $mood;
	public $type;
	public $style;

	public $modname;
	public $modfile;
	public $modurl;

	public $songfile;
	public $songurl;

	public $validated;
	public $exeversion;
	public $exebuild;

	public $mx;
	public $karma;
	public $starttime;


	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	// $gbx contains a GBX object from GBXChallMapFetcher class
	public function __construct ($gbx = null, $filename = null) {
		global $aseco;

		if ($gbx != null) {
			$this->id		= 0;
			$this->uid		= $gbx->uid;
			$this->filename		= $filename;
			$this->name		= trim($aseco->stripNewlines($aseco->stripBOM($gbx->name)));
			$this->name_stripped	= $aseco->stripColors($this->name, true);
			$this->comment		= $gbx->comment;

			if ($gbx->authorLogin) {
				$this->author	= trim($gbx->authorLogin);
			}
			else if ($gbx->author) {
				$this->author	= trim($gbx->author);
			}
			else {
				$this->author	= 'Unknown';
			}

			$this->author_nickname	= trim($gbx->authorNick);
			if  (empty($this->author_zone) && $this->author == 'Nadeo') {
				$this->author_zone	= array('Europe', 'France', 'Île-de-France', 'Paris');
				$this->author_continent	= 'Europe';
				$this->author_nation	= 'FRA';
			}
			else {
				$this->author_zone = explode('|', substr($gbx->authorZone, 6));		// strip 'World|' and split into array()
				if (isset($this->author_zone[0])) {
					switch ($this->author_zone[0]) {
						case 'Europe':
						case 'Africa':
						case 'Asia':
						case 'Middle East':
						case 'North America':
						case 'South America':
						case 'Oceania':
							$this->author_continent = $this->author_zone[0];
							$this->author_nation = $aseco->country->countryToIoc($this->author_zone[1]);
							break;
						default:
							$this->author_continent = '';
							$this->author_nation = $aseco->country->countryToIoc($this->author_zone[0]);
					}
				}
				else {
					$this->author_continent = '';
					$this->author_nation = 'OTH';
				}
			}

			$this->author_score	= $gbx->authorScore;
			$this->author_time	= $gbx->authorTime;
			$this->goldtime		= $gbx->goldTime;
			$this->silvertime	= $gbx->silverTime;
			$this->bronzetime	= $gbx->bronzeTime;

			$this->nblaps		= $gbx->nbLaps;
			$this->multilap		= $gbx->multiLap;
			$this->nbcheckpoints	= $gbx->nbChecks;

			$this->cost		= $gbx->cost;
			$this->environment	= $gbx->envir;
			$this->mood		= str_replace('64x64', '', $gbx->mood);		// "64x64Day" to "Day", for Stadium 64x64 (with no Stadium decoration for custom titles)
			$this->type		= $gbx->mapType;
			$this->style		= trim($gbx->mapStyle);

			$this->modname		= $gbx->modName;
			$this->modfile		= $gbx->modFile;
			$this->modurl		= $gbx->modUrl;

			$this->songfile		= $gbx->songFile;
			$this->songurl		= $gbx->songUrl;

			$this->validated	= $gbx->validated;
			$this->exeversion	= $gbx->exeVer;
			$this->exebuild		= $gbx->exeBld;

			$this->mx		= false;
			$this->karma		= 0;
			$this->starttime	= 0;
		}
		else {
			$this->id		= 0;
			$this->uid		= false;
			$this->filename		= $filename;
			$this->name		= 'Unknown';
			$this->name_stripped	= 'Unknown';
			$this->comment		= '';

			$this->author		= 'Unknown';
			$this->author_nickname	= 'Unknown';
			$this->author_zone	= array();
			$this->author_continent	= '';
			$this->author_nation	= 'OTH';

			$this->author_score	= 0;
			$this->author_time	= 0;
			$this->goldtime		= 0;
			$this->silvertime	= 0;
			$this->bronzetime	= 0;

			$this->nblaps		= 0;
			$this->multilap		= false;
			$this->nbcheckpoints	= 0;

			$this->cost		= 0;
			$this->environment	= '';
			$this->mood		= '';
			$this->type		= '';
			$this->style		= '';

			$this->modname		= '';
			$this->modfile		= '';
			$this->modurl		= '';

			$this->songfile		= '';
			$this->songurl		= '';

			$this->validated	= false;
			$this->exeversion	= '';
			$this->exebuild		= '';

			$this->mx		= false;
			$this->karma		= 0;
			$this->starttime	= 0;
		}
	}
}

?>
