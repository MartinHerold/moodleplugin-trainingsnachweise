<?php
// This file is part of Klassenbuch module for Moodle - http://moodle.org/
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
 * Instance add/edit form
 *
 * @package    mod_hittrainingsnachweis
 * @copyright  2020, You Name <info@herolditservice.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $CFG;
//require_once($CFG->dirroot.'/mod/hittrainingsnachweis/locallib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_hittrainingsnachweis_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $config = get_config('hittrainingsnachweis');

        // -------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        if ($CFG->branch < 29) {
            $this->add_intro_editor($config->requiremodintro, get_string('summary'));
        } else {
            $this->standard_intro_elements(get_string('summary'));
        }

        $mform->addElement('select', 'wiederholungen', get_string('wiederholungen', 'hittrainingsnachweis'), array(
            1 => get_string('wiederholungen1', 'mod_hittrainingsnachweis'),
            2 => get_string('wiederholungen2', 'mod_hittrainingsnachweis'),
            3 => get_string('wiederholungen3', 'mod_hittrainingsnachweis'),
            4 => get_string('wiederholungen4', 'mod_hittrainingsnachweis'),
            5 => get_string('wiederholungen5', 'mod_hittrainingsnachweis'),
            6 => get_string('wiederholungen6', 'mod_hittrainingsnachweis'),
            7 => get_string('wiederholungen7', 'mod_hittrainingsnachweis'),
            8 => get_string('wiederholungen8', 'mod_hittrainingsnachweis'),
            9 => get_string('wiederholungen9', 'mod_hittrainingsnachweis'),
            10 => get_string('wiederholungen10', 'mod_hittrainingsnachweis'),
            11 => get_string('wiederholungen11', 'mod_hittrainingsnachweis'),
            12 => get_string('wiederholungen12', 'mod_hittrainingsnachweis'),
            13 => get_string('wiederholungen13', 'mod_hittrainingsnachweis'),
            14 => get_string('wiederholungen14', 'mod_hittrainingsnachweis'),
            15 => get_string('wiederholungen15', 'mod_hittrainingsnachweis'),
            16 => get_string('wiederholungen16', 'mod_hittrainingsnachweis'),
            17 => get_string('wiederholungen17', 'mod_hittrainingsnachweis'),
            18 => get_string('wiederholungen18', 'mod_hittrainingsnachweis'),
            19 => get_string('wiederholungen19', 'mod_hittrainingsnachweis'),
            20 => get_string('wiederholungen20', 'mod_hittrainingsnachweis'),
            21 => get_string('wiederholungen21', 'mod_hittrainingsnachweis'),
            22 => get_string('wiederholungen22', 'mod_hittrainingsnachweis'),
            23 => get_string('wiederholungen23', 'mod_hittrainingsnachweis'),
            24 => get_string('wiederholungen24', 'mod_hittrainingsnachweis'),
            25 => get_string('wiederholungen25', 'mod_hittrainingsnachweis'),
            26 => get_string('wiederholungen26', 'mod_hittrainingsnachweis'),
            27 => get_string('wiederholungen27', 'mod_hittrainingsnachweis'),
            28 => get_string('wiederholungen28', 'mod_hittrainingsnachweis'),
            29 => get_string('wiederholungen29', 'mod_hittrainingsnachweis'),
            30 => get_string('wiederholungen30', 'mod_hittrainingsnachweis'),
            31 => get_string('wiederholungen31', 'mod_hittrainingsnachweis'),
            32 => get_string('wiederholungen32', 'mod_hittrainingsnachweis'),
            33 => get_string('wiederholungen33', 'mod_hittrainingsnachweis'),
            34 => get_string('wiederholungen34', 'mod_hittrainingsnachweis'),
            35 => get_string('wiederholungen35', 'mod_hittrainingsnachweis'),
            36 => get_string('wiederholungen36', 'mod_hittrainingsnachweis'),
            37 => get_string('wiederholungen37', 'mod_hittrainingsnachweis'),
            38 => get_string('wiederholungen38', 'mod_hittrainingsnachweis'),
            39 => get_string('wiederholungen39', 'mod_hittrainingsnachweis'),
            40 => get_string('wiederholungen40', 'mod_hittrainingsnachweis'),
            41 => get_string('wiederholungen41', 'mod_hittrainingsnachweis'),
            42 => get_string('wiederholungen42', 'mod_hittrainingsnachweis'),
            43 => get_string('wiederholungen43', 'mod_hittrainingsnachweis'),
            44 => get_string('wiederholungen44', 'mod_hittrainingsnachweis'),
            45 => get_string('wiederholungen45', 'mod_hittrainingsnachweis'),
            46 => get_string('wiederholungen46', 'mod_hittrainingsnachweis'),
            47 => get_string('wiederholungen47', 'mod_hittrainingsnachweis'),
            48 => get_string('wiederholungen48', 'mod_hittrainingsnachweis'),
            49 => get_string('wiederholungen49', 'mod_hittrainingsnachweis'),
            50 => get_string('wiederholungen50', 'mod_hittrainingsnachweis'),

        ));
        $mform->addHelpButton('wiederholungen', 'wiederholungen', 'mod_hittrainingsnachweis');
        $mform->setDefault('wiederholungen', 10);

        // -------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();

        // -------------------------------------------------------------------------------
        $this->add_action_buttons();
    }


}
