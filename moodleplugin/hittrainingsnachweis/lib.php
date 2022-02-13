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
 * @package   mod_hittrainingsnachweis
 * @copyright 2020, You Name <info@herolditservice.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function mod_hittrainingsnachweis_before_footer(){
   // \core\notification::add("test", \core\output\notification::NOTIFY_INFO);
}


function hittrainingsnachweis_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    return $DB->insert_record('hittrainingsnachweis', $data);
}

function hittrainingsnachweis_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    // SYNERGY LEARNING - add 'show classplan' checkbox START.
    if (!isset($data->showclassplan)) {
        $data->showclassplan = 0;
    }
    // SYNERGY LEARNING - add 'show classplan' checkbox END.

    $DB->update_record('hittrainingsnachweis', $data);

    $trainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $data->id));
    $DB->set_field('hittrainingsnachweis', 'revision', $trainingsnachweis->revision + 1, array('id' => $trainingsnachweis->id));

    return true;
}

function hittrainingsnachweis_delete_instance($id) {
    global $DB;

    if (!$hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('hittrainingsnachweis_entry', array('hittrainingsnachweisid' => $hittrainingsnachweis->id));
    $DB->delete_records('hittrainingsnachweis', array('id' => $hittrainingsnachweis->id));

    return true;
}


function hittrainingsnachweis_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'attachment') {
        return false;
    }
    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);
    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/hittrainingsnachweis:read', $context)) {
        return false;
    }
    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.
    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }
    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_hittrainingsnachweis', $filearea, $itemid, $filepath, $filename);

    if (!$file) {
        return false; // The file does not exist.
    }
    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, false);
}