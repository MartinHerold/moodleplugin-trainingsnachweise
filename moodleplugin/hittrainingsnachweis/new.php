<?php

require('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/new_form.php');

global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;


$cmid = required_param('cmid', PARAM_INT);  // Klassenbuch Course Module ID.

$cm = get_coursemodule_from_id('hittrainingsnachweis', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/hittrainingsnachweis/new.php', array('cmid' => $cmid));



//$options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0);
$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 50, 'context' => $context);
$customdata = array('filemanageropts' => $filemanageropts);

$mform = new createhittrainingsnachweisentry_form(null, $customdata);
$mformdata = new stdClass();
$mformdata->studentid = $USER->id;
$mformdata->coursemoduleid = $cmid;
$mformdata->cmid = $cmid;
$mformdata->hittrainingsnachweisid = $hittrainingsnachweis->id;
$mformdata->studentid = $USER->id;
$mform->set_data($mformdata);

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $courseurl = new moodle_url('/mod/hittrainingsnachweis/view.php', array('id' => 31, 'message' => 'canceled'));
    redirect($courseurl);
} else if ($data = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    if ($data->cmid) {
        // Adding new chapter.
        //var_dump($data);
        $data->timecreated = time();
        $data->timemodified = time();
        $data->abgezeichnet = 'N';
        $data->id = $DB->insert_record('hittrainingsnachweis_entry', $data);
        file_save_draft_area_files($data->attachment, $context->id, 'mod_hittrainingsnachweis', 'attachment',
            $data->id, array('subdirs' => 0, 'maxbytes' => 10485760, 'maxfiles' => 1));

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id, 'mod_hittrainingsnachweis', 'attachment',
            $data->id, $sort = "itemid, filepath, filename",
            $includedirs = true, $updatedsince = 0);
        if ($files != null) {
            $url = array();
            foreach ($files as $file) {
                $tmp = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),  // studentcomment
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false // $forcedownload
                );
                $url[] = $tmp;
            }
            // Save the array here ...
            var_dump($url);

            $data->attachmentid = $data->attachment;
            $DB->update_record('hittrainingsnachweis_entry', $data);
        }

        $trainers = get_users_by_capability($context, 'mod/hittrainingsnachweis:edit');
        foreach($trainers as $trainer){
            $message = new \core\message\message();
            $message->courseid = $course->id;
            $message->component = 'mod_hittrainingsnachweis'; // Your plugin's name
            $message->name = 'trainingsnachweisabgezeichnet'; // Your notification name from message.php
            $message->userfrom = $USER->id; // If the message is 'from' a specific user you can set them here
            $message->userto = $trainer->id; //get_users_by_capability($context, 'mod/hittrainingsnachweis:edit');
            $message->subject = 'Trainingsnachweis wartet auf Bewertung';
            $message->fullmessage = 'Ein neuer Trainingsnachweis wartet auf Bewertung';
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->smallmessage = 'small message';
            $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
            $message->contexturl = (new \moodle_url('/mod/hittrainingsnachweis/view_studententries.php?cmid='.$cmid.'&studentid='.$USER->id))->out(false); // A relevant URL for the notification
            $message->contexturlname = 'Eintrag'; // Link title explaining where users get to for the contexturl
            $messageid = message_send($message);
        }

    } else {
        die("no cmid");
    }

    $courseurl = new moodle_url('/mod/hittrainingsnachweis/view.php', array('id' => $data->cmid, 'message' => 'success'));
    redirect($courseurl);
}


$PAGE->set_title("Trainingsnachweis Eintrag erstellen");
$PAGE->add_body_class('mod_hittrainingsnachweis');
$PAGE->set_heading(format_string($course->fullname));


echo $OUTPUT->header();
echo $OUTPUT->heading("Neuen Trainingsnachweis Eintrag anlegen");
$mform->display();

echo $OUTPUT->footer();

