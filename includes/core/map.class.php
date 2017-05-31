<?php
/*
 * Class: Map
 * ~~~~~~~~~~
 * » Stores information about a Map on the dedicated server.
 * » Based upon basic.inc.php from XAseco2/1.03 written by Xymph and others
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
 *  - none
 *
 */


/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class Map extends BaseClass {
	public $id;							// Database id
	public $uid;
	public $filename;
	public $name;
	public $name_stripped;
	public $name_slug;
	public $comment;

	public $author;
	public $author_nickname;
	public $author_zone;
	public $author_continent;
	public $author_nation;

	public $author_score;
	public $author_time;
	public $gold_time;
	public $silver_time;
	public $bronze_time;

	public $nb_laps;
	public $multi_lap;
	public $nb_checkpoints;

	public $cost;
	public $environment;
	public $mood;
	public $type;
	public $style;

	public $mod_name;
	public $mod_file;
	public $mod_url;

	public $song_file;
	public $song_url;

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

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.1');
		$this->setBuild('2017-05-31');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Stores information about a Map on the dedicated server.');

		if ($gbx != null) {
			$this->id		= 0;
			$this->uid		= $gbx->uid;
			$this->filename		= $filename;
			$this->name		= trim($aseco->stripNewlines($aseco->stripBOM($gbx->name)));
			$this->name_stripped	= $aseco->stripStyles($this->name, true);
			$this->name_slug	= $aseco->slugify($this->name_stripped);
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
			if (empty($this->author_zone) && $this->author == 'Nadeo') {
				$this->author_zone	= array('Europe', 'France', 'Île-de-France', 'Paris');
				$this->author_continent	= 'EU';
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
							$this->author_continent = $aseco->continent->continentToAbbreviation($this->author_zone[0]);
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
			$this->gold_time	= $gbx->goldTime;
			$this->silver_time	= $gbx->silverTime;
			$this->bronze_time	= $gbx->bronzeTime;

			$this->nb_laps		= $gbx->nbLaps;
			$this->multi_lap	= $gbx->multiLap;
			$this->nb_checkpoints	= $gbx->nbChecks;

			$this->cost		= $gbx->cost;
			$this->environment	= $gbx->envir;
			$this->mood		= str_replace(array('64x64', '48'), '', $gbx->mood);		// "64x64Day" to "Day" for Stadium 64x64 (with no Stadium decoration for custom titles); "Day48" to "Day for Valley maps (mostly returned by ListMethod GetCurrentMapInfo())
			$this->type		= $gbx->mapType;
			$this->style		= trim($gbx->mapStyle);

			$this->mod_name		= $gbx->modName;
			$this->mod_file		= $gbx->modFile;
			$this->mod_url		= $gbx->modUrl;

			$this->song_file	= $gbx->songFile;
			$this->song_url		= $gbx->songUrl;

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
			$this->name_slug	= 'Unknown';
			$this->comment		= '';

			$this->author		= 'Unknown';
			$this->author_nickname	= 'Unknown';
			$this->author_zone	= array();
			$this->author_continent	= '';
			$this->author_nation	= 'OTH';

			$this->author_score	= 0;
			$this->author_time	= 0;
			$this->gold_time	= 0;
			$this->silver_time	= 0;
			$this->bronze_time	= 0;

			$this->nb_laps		= 0;
			$this->multi_lap	= false;
			$this->nb_checkpoints	= 0;

			$this->cost		= 0;
			$this->environment	= '';
			$this->mood		= '';
			$this->type		= '';
			$this->style		= '';

			$this->mod_name		= '';
			$this->mod_file		= '';
			$this->mod_url		= '';

			$this->song_file	= '';
			$this->song_url		= '';

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
