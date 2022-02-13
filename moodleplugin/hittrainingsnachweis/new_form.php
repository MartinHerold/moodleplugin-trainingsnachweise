<?php
defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->libdir . '/formslib.php');

class createhittrainingsnachweisentry_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('header', 'displayinfo', get_string('mandatoryfields', 'mod_hittrainingsnachweis'));
        $filemanageropts = $this->_customdata['filemanageropts'];
        $filemanageropts['maxfiles'] = 1;

        $mform->addElement('textarea', 'beschreibung', get_string('beschreibung', 'hittrainingsnachweis'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('beschreibung', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('beschreibung', '');        //Default value

        $mform->addElement('date_time_selector', 'training_dttm', get_string('datum', 'hittrainingsnachweis'));

        $mform->addElement('hidden','studentid');
        $mform->setType('studentid', PARAM_INT);

        $mform->addElement('hidden','coursemoduleid');
        $mform->setType('coursemoduleid', PARAM_INT);

        $mform->addElement('hidden','cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden','hittrainingsnachweisid');
        $mform->setType('hittrainingsnachweisid', PARAM_INT);

        $mform->addElement('filemanager', 'attachment', get_string('attachment', 'mod_hittrainingsnachweis'), null,
            array('subdirs' => 0, 'maxbytes' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
                'accepted_types' => array('image'), 'return_types'=> 3 ));


        $this->add_action_buttons($cancel = true, $submitlabel=get_string('eintragen', 'hittrainingsnachweis'));

    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}