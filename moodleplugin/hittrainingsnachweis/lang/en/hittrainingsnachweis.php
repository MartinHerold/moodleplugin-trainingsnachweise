<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'book', language 'de', branch 'MOODLE_22_STABLE'
 *
 * @package   book
 * @copyright 2020, You Name <info@herolditservice.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Training records';
$string['modulenameplural'] = 'Training records';
$string['pluginname'] = 'Training records';
$string['hittrainingsnachweis:edit'] = 'Edit training records';


$string['beschreibung'] = 'Description';
$string['datum'] = 'Date';

for($i=1;$i<51;$i++){
    $string['wiederholungen'.$i] = $i.' repetitions';
}

$string['eintragen'] = 'Entry';

$string['messageprovider:trainingsnachweiserstellt'] = 'Notification when a training record entry has been created.';
$string['messageprovider:trainingsnachweisabgezeichnet'] = 'Notification when a training record has been signed.';

$string['mandatoryfields'] = 'Input fields';
$string['Download Trainingsnachweise'] = 'Download training records';

$string['attachment'] = 'Attachment';
