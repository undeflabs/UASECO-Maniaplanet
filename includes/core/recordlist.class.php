<?php
/*
 * Class: Records List
 * ~~~~~~~~~~~~~~~~~~~
 * » Manages a list of records, add records to the list and remove them.
 * » Based upon types.inc.php from XAseco2/1.03 written by Xymph and others
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

class RecordList extends BaseClass {
	public $record_list;
	public $max_records;

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function __construct ($max_records) {

		$this->setAuthor('undef.de');
		$this->setVersion('1.0.0');
		$this->setBuild('2017-04-22');
		$this->setCopyright('2014 - 2017 by undef.de');
		$this->setDescription('Manages a list of records, add records to the list and remove them.');

		$this->record_list = array();
		$this->max_records = $max_records;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function count () {
		return count($this->record_list);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function clear () {
		$this->record_list = array();
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setMaxRecords ($limit) {
		$this->max_records = $limit;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getMaxRecords () {
		return $this->max_records;
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function getRecord ($rank) {
		if (isset($this->record_list[$rank])) {
			return $this->record_list[$rank];
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function setRecord ($rank, $record) {
		if (isset($this->record_list[$rank])) {
			return $this->record_list[$rank] = $record;
		}
		else {
			return false;
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function moveRecord ($from, $to) {
		global $aseco;
		$aseco->moveArrayElement($this->record_list, $from, $to);
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function addRecord ($record, $rank = -1) {
		global $aseco;

		// if no rank was set for this record, then put it to the end of the list
		if ($rank == -1) {
			$rank = count($this->record_list);
		}

		// do not insert a record behind the border of the list
		if ($rank >= $this->max_records) {
			return false;
		}

		// do not insert a record with no score
		if ($record->score <= 0) {
			return false;
		}

		// if the given object is a record
		if (get_class($record) == 'Record') {

			// if records are getting too much, drop the last from the list
			if (count($this->record_list) >= $this->max_records) {
				array_pop($this->record_list);
			}

			// insert the record at the specified position
			return $aseco->insertArrayElement($this->record_list, $record, $rank);
		}
	}

	/*
	#///////////////////////////////////////////////////////////////////////#
	#									#
	#///////////////////////////////////////////////////////////////////////#
	*/

	public function deleteRecord ($rank = -1) {
		global $aseco;

		// do not remove a record outside the current list
		if ($rank < 0 || $rank >= count($this->record_list)) {
			return false;
		}

		// remove the record from the specified position
		return $aseco->removeArrayElement($this->record_list, $rank);
	}
}

?>
