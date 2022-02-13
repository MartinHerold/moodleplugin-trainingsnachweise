<?php

require('../../config.php');
require_once('lib.php');

global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;


$cmid = required_param('cmid', PARAM_INT);  // Klassenbuch Course Module ID.
$entryid = required_param('entryid', PARAM_INT); // Entry ID to update
$studentid = required_param('studentid', PARAM_INT); // Entry ID to update
$abgezeichnet = required_param('bewertung', PARAM_INT); // Entry ID to update

$cm = get_coursemodule_from_id('hittrainingsnachweis', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);
$hittrainingsnachweisentry = $DB->get_record('hittrainingsnachweis_entry', array('id' => $entryid), '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/hittrainingsnachweis:edit', $context);

//In this case you process validated data. $mform->get_data() returns data posted in form.

// Adding new chapter.
$data = new stdClass();
$data->id = $entryid;
$abgezeichnetStates = array("Y", "N");
//if(){

    $data->bewertung = $abgezeichnet;
    if($data->bewertung > 0){
        $data->abgezeichnet = 'Y';
    } else {
        $data->abgezeichnet = 'N';
    }
    $data->abzeichnerid = $USER->id;
    $DB->update_record('hittrainingsnachweis_entry', $data, $bulk=false);
    $courseurl = new moodle_url('/mod/hittrainingsnachweis/view_studententries.php', array(
        'cmid' => $cmid,
        'studentid' => $studentid,
        'status' => 'success',
    ));
    redirect($courseurl);
/*} else {
    $courseurl = new moodle_url('/mod/hittrainingsnachweis/view_studententries.php', array(
        'cmid' => $cmid,
        'studentid' => $studentid,
        'status' => 'error',
    ));
    redirect($courseurl);
    exit;
}*/


