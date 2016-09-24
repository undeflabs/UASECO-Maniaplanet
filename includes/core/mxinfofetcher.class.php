<?php
/*
 * Class: MXInfoFetcher
 * ~~~~~~~~~~~~~~~~~~~~
 * » Fetch info/records for TM2/SM/QM maps from ManiaExchange.
 * » Based upon mxinfofetcher.inc.php written by Xymph based on:
 *   http://api.mania-exchange.com/
 *   http://tm.mania-exchange.com/api
 *   http://sm.mania-exchange.com/api
 *   http://tm.mania-exchange.com/threads/view/218
 *   Derived from TMXInfoFetcher
 *
 *   v1.7b: Changes made by undef.de
 *          - Added USER_AGENT constant and "X-ManiaPlanet-ServerLogin" for MX "Who downloaded?" function
 *          - Changed the records limit from 15 to 25
 *          - Added $original_acomment (which contains the unchanged author comment)
 *   v1.7a: Changes made by undef.de
 *          - Added $timestamp_fetched for caching functions
 *   v1.7:  Added $titlepack (TM2/SM)
 *   v1.6:  Allowed 24-char UIDs too
 *   v1.5:  Added $maptype (TM2/SM)
 *   v1.4:  Updated to use MX API v2.0 and add/fix support for SM; added
 *          $trkvalue (TM2, equals deprecated $lbrating), $unlimiter (TM2/SM),
 *          $rating/$ratingex/$ratingcnt (SM)
 *   v1.3:  Added URLs to downloadable replays
 *   v1.2:  Added the replays list in $recordlist
 *   v1.1:  Allowed 25-char UIDs too
 *   v1.0:  Initial release
 *
 * ----------------------------------------------------------------------------------
 * Author:	undef.de
 * Date:	2015-08-02
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
 *  - none
 *
 */



/*
#///////////////////////////////////////////////////////////////////////#
#									#
#///////////////////////////////////////////////////////////////////////#
*/

class MXInfoFetcher {
	public $section;
	public $prefix;
	public $uid;
	public $id;
	public $records;
	public $error;
	public $name;
	public $userid;
	public $author;
	public $uploaded;
	public $updated;
	public $type;
	public $maptype;
	public $titlepack;
	public $style;
	public $envir;
	public $mood;
	public $dispcost;
	public $lightmap;
	public $modname;
	public $exever;
	public $exebld;
	public $routes;
	public $length;
	public $unlimiter;
	public $laps;
	public $diffic;
	public $lbrating;
	public $trkvalue;
	public $replaytyp;
	public $replayid;
	public $replaycnt;
	public $acomment;
	public $original_acomment;
	public $awards;
	public $comments;
	public $rating;
	public $ratingex;
	public $ratingcnt;
	public $timestamp_fetched;
	public $pageurl;
	public $replayurl;
	public $imageurl;
	public $thumburl;
	public $dloadurl;
	public $recordlist;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	/**
	 * Fetches all available data for a ManiaExchange map
	 *
	 * @param String $game
	 *        MX section for 'TM2', 'SM', 'QM'
	 * @param String $id
	 *        The map UID to search for (if a 24-27 char alphanum string),
	 *        otherwise the MX ID to search for (if a number)
	 * @param Boolean $records
	 *        If true, the script also returns the world records (max. 10)
	 *        [not yet available]
	 * @return MXInfoFetcher
	 *        If $error is not an empty string, it's an error message
	 */
	public function __construct ($game, $id, $records) {

		$this->section = $game;
		switch ($game) {
			case 'TM2':
				$this->prefix = 'tm';
				break;
			case 'SM':
				$this->prefix = 'sm';
				break;
			case 'QM':
				$this->prefix = 'qm';
				break;
			default:
				$this->prefix = '';
				return;
		}

		$this->error = '';
		$this->records = $records;

		// check for UID string
		if (preg_match('/^\w{24,27}$/', $id)) {
			$this->uid = $id;
			$this->getData(true);
		}
		else if (is_numeric($id) && $id > 0) {
			// check for MX ID
			$this->id = floor($id);
			$this->getData(false);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public static function __set_state ($import) {

		$mx = new MXInfoFetcher('', 0, false);

		$mx->section		= $import['section'];
		$mx->prefix		= $import['prefix'];
		$mx->uid		= (int)$import['uid'];
		$mx->id			= (int)$import['id'];
		$mx->records		= (bool)$import['records'];
		$mx->error		= '';
		$mx->name		= $import['name'];
		$mx->userid		= (int)$import['userid'];
		$mx->author		= $import['author'];
		$mx->uploaded		= $import['uploaded'];
		$mx->updated		= $import['updated'];
		$mx->type		= $import['type'];
		$mx->maptype		= isset($import['maptype']) ? $import['maptype'] : '';
		$mx->titlepack		= isset($import['titlepack']) ? $import['titlepack'] : '';
		$mx->style		= $import['style'];
		$mx->envir		= $import['envir'];
		$mx->mood		= $import['mood'];
		$mx->dispcost		= (int)$import['dispcost'];
		$mx->lightmap		= (int)$import['lightmap'];
		$mx->modname		= $import['modname'];
		$mx->exever		= $import['exever'];
		$mx->exebld		= $import['exebld'];
		$mx->routes		= $import['routes'];
		$mx->length		= $import['length'];
		$mx->unlimiter		= isset($import['unlimiter']) ? (bool)$import['unlimiter'] : false;
		$mx->laps		= (int)$import['laps'];
		$mx->diffic		= $import['diffic'];
		$mx->lbrating		= isset($import['lbrating']) ? (int)$import['lbrating'] : 0;
		$mx->trkvalue		= isset($import['trkvalue']) ? (int)$import['trkvalue'] : 0;
		$mx->replaytyp		= $import['replaytyp'];
		$mx->replayid		= (int)$import['replayid'];
		$mx->replaycnt		= (int)$import['replaycnt'];
		$mx->acomment		= $import['acomment'];
		$mx->original_acomment	= $import['acomment'];
		$mx->awards		= (int)$import['awards'];
		$mx->comments		= (int)$import['comments'];
		$mx->rating		= isset($import['rating']) ? (float)$import['rating'] : 0.0;
		$mx->ratingex		= isset($import['ratingex']) ? (float)$import['ratingex'] : 0.0;
		$mx->ratingcnt		= isset($import['ratingcnt']) ? (int)$import['ratingcnt'] : 0;
		$mx->timestamp_fetched	= time();
		$mx->pageurl		= $import['pageurl'];
		$mx->replayurl		= $import['replayurl'];
		$mx->imageurl		= $import['imageurl'];
		$mx->thumburl		= $import['thumburl'];
		$mx->dloadurl		= $import['dloadurl'];
		$mx->recordlist		= null;

		if ($mx->trkvalue == 0 && $mx->lbrating > 0) {
			$mx->trkvalue = $mx->lbrating;
		}
		else if ($mx->lbrating == 0 && $mx->trkvalue > 0) {
			$mx->lbrating = $mx->trkvalue;
		}

		return $mx;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	private function getData ($isuid) {

		// get map info
		if ($this->prefix == 'tm') {
			$dir = 'tracks';
		}
		else {
			// 'sm' || 'qm'
			$dir = 'maps';
		}

		$url = 'http://api.mania-exchange.com/' . $this->prefix . '/' . $dir . '/' . ($isuid ? $this->uid : $this->id);
		$file = $this->getFile($url);
		if ($file === false) {
			$this->error = '[MXInfoFetcher] Connection or response error on '. $url;
			return;
		}
		else if ($file === -1) {
			$this->error = '[MXInfoFetcher] Timed out while reading data from '. $url;
			return;
		}
		else if ($file == '') {
			$this->error = '[MXInfoFetcher] No data returned from '. $url;
			return;
		}

		// process map info
		$mx = json_decode($file);
		if ($mx === null) {
			$this->error = '[MXInfoFetcher] Cannot decode JSON data from '. $url;
			return;
		}
		if (empty($mx)) {
			$this->error = '[MXInfoFetcher] No data returned from '. $url;
			return;
		}

		$mx = $mx[0];
		if ($isuid) {
			$this->id      = ($this->prefix == 'tm') ? $mx->TrackID : $mx->MapID;
		}

		$this->name			= $mx->Name;
		$this->userid			= $mx->UserID;
		$this->author			= $mx->Username;
		$this->uploaded			= $mx->UploadedAt;
		$this->updated			= $mx->UpdatedAt;
		$this->type			= $mx->TypeName;
		$this->maptype			= isset($mx->MapType) ? $mx->MapType : '';
		$this->titlepack		= isset($mx->TitlePack) ? $mx->TitlePack : '';
		$this->style			= isset($mx->StyleName) ? $mx->StyleName : '';
		$this->envir			= $mx->EnvironmentName;
		$this->mood			= $mx->Mood;
		$this->dispcost			= $mx->DisplayCost;
		$this->lightmap			= $mx->Lightmap;
		$this->modname			= isset($mx->ModName) ? $mx->ModName : '';
		$this->exever			= $mx->ExeVersion;
		$this->exebld			= $mx->ExeBuild;
		$this->routes			= isset($mx->RouteName) ? $mx->RouteName : '';
		$this->length			= isset($mx->LengthName) ? $mx->LengthName : '';
		$this->unlimiter		= isset($mx->UnlimiterRequired) ? $mx->UnlimiterRequired : false;
		$this->laps			= isset($mx->Laps) ? $mx->Laps : 0;
		$this->diffic			= $mx->DifficultyName;
		$this->lbrating			= isset($mx->LBRating) ? $mx->LBRating : 0;
		$this->trkvalue			= isset($mx->TrackValue) ? $mx->TrackValue : 0;
		$this->replaytyp		= isset($mx->ReplayTypeName) ? $mx->ReplayTypeName : '';
		$this->replayid			= isset($mx->ReplayWRID) ? $mx->ReplayWRID : 0;
		$this->replaycnt		= isset($mx->ReplayCount) ? $mx->ReplayCount : 0;
		$this->acomment			= $mx->Comments;
		$this->original_acomment	= $mx->Comments;
		$this->awards			= isset($mx->AwardCount) ? $mx->AwardCount : 0;
		$this->comments			= $mx->CommentCount;
		$this->rating			= isset($mx->Rating) ? $mx->Rating : 0.0;
		$this->ratingex			= isset($mx->RatingExact) ? $mx->RatingExact : 0.0;
		$this->ratingcnt		= isset($mx->RatingCount) ? $mx->RatingCount : 0;
		$this->timestamp_fetched	= time();

		if ($this->trkvalue == 0 && $this->lbrating > 0) {
			$this->trkvalue = $this->lbrating;
		}
		else if ($this->lbrating == 0 && $this->trkvalue > 0) {
			$this->lbrating = $this->trkvalue;
		}

		$search = array(chr(31), '[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[url]', '[/url]');
		$replace = array('<br/>', '<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<i>', '</i>');
		$this->acomment  = str_ireplace($search, $replace, $this->acomment);
		$this->acomment  = preg_replace('/\[url=.*\]/', '<i>', $this->acomment);

		$this->pageurl   = 'http://'. $this->prefix .'.mania-exchange.com/'. $dir .'/view/'. $this->id;
		$this->imageurl  = 'http://'. $this->prefix .'.mania-exchange.com/'. $dir .'/screenshot/normal/'. $this->id;
		$this->thumburl  = 'http://'. $this->prefix .'.mania-exchange.com/'. $dir .'/screenshot/small/'. $this->id;
		$this->dloadurl  = 'http://'. $this->prefix .'.mania-exchange.com/'. $dir .'/download/'. $this->id;

		if ($this->replayid > 0) {
			$this->replayurl = 'http://'. $this->prefix .'.mania-exchange.com/replays/download/'. $this->replayid;
		}
		else {
			$this->replayurl = '';
		}

		// fetch records too?
		$this->recordlist = array();
		if ($this->prefix == 'tm' && $this->records) {
			$limit = 25;
			$url = 'http://api.mania-exchange.com/' . $this->prefix . '/replays/' . $this->id . '/' . $limit . '/';
			$file = $this->getFile($url);
			if ($file === false) {
				$this->error = '[MXInfoFetcher] Connection or response error on ' . $url;
				return;
			}
			else if ($file === -1) {
				$this->error = '[MXInfoFetcher] Timed out while reading data from ' . $url;
				return;
			}
			else if ($file == '') {
				$this->error = '[MXInfoFetcher] No data returned from ' . $url;
				return;
			}

			// process replays info
			$mx = json_decode($file);
			if ($mx === null) {
				$this->error = '[MXInfoFetcher] Cannot decode JSON data from ' . $url;
				return;
			}

			$i = 0;
			while ($i < $limit && isset($mx[$i])) {
				$this->recordlist[$i] = array(
					'replayid'	=> $mx[$i]->ReplayID,
					'userid'	=> $mx[$i]->UserID,
					'username'	=> $mx[$i]->Username,
					'uploadedat'	=> $mx[$i]->UploadedAt,
					'replaytime'	=> $mx[$i]->ReplayTime,
					'stuntscore'	=> $mx[$i]->StuntScore,
					'respawns'	=> $mx[$i]->Respawns,
					'beaten'	=> $mx[$i]->Beaten,
					'percentage'	=> $mx[$i]->Percentage,
					'replaypnts'	=> $mx[$i]->ReplayPoints,
					'nadeopnts'	=> $mx[$i]->NadeoPoints,
					'replayurl'	=> 'http://'. $this->prefix .'.mania-exchange.com/replays/download/'. $mx[$i]->ReplayID,
				);
				$i++;
			}
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/
	// Simple HTTP Get function with timeout
	// ok: return string || error: return false || timeout: return -1
	private function getFile ($url) {
		global $aseco;

		$url = parse_url($url);
		$port = isset($url['port']) ? $url['port'] : 80;
		$query = isset($url['query']) ? '?' . $url['query'] : '';

		$fp = @fsockopen($url['host'], $port, $errno, $errstr, 4);
		if (!$fp) {
			return false;
		}

		$request  = "GET ". $url['path'].$query ." HTTP/1.0\r\n";
		$request .= "Host: ". $url['host'] ."\r\n";
		$request .= "Content-Type: application/json\r\n";
		$request .= "X-ManiaPlanet-ServerLogin: ". $aseco->server->login ."\r\n";
		$request .= "User-Agent: ". USER_AGENT ." ". get_class($this) ."/1.7b\r\n\r\n";
		fwrite($fp, $request);

		stream_set_timeout($fp, 8);

		$res = '';
		$info['timed_out'] = false;
		while (!feof($fp) && !$info['timed_out']) {
			$res .= fread($fp, 512);
			$info = stream_get_meta_data($fp);
		}
		fclose($fp);

		if ($info['timed_out']) {
			return -1;
		}
		else {
			if (substr($res, 9, 3) != '200') {
				return false;
			}
			$page = explode("\r\n\r\n", $res, 2);
			return trim($page[1]);
		}
	}
}

?>
