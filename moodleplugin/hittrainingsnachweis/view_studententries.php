<?php
require('../../config.php');
require_once('lib.php');

global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE;

$cmid = optional_param('cmid', 0, PARAM_INT); // Course Module ID.
$studentid = optional_param('studentid', 0, PARAM_INT); // Course Module ID.

if ($cmid) {
    $cm = get_coursemodule_from_id('hittrainingsnachweis', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $bid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('hittrainingsnachweis', $hittrainingsnachweis->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cmid = $cm->id;
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hittrainingsnachweis:read', $context);
$allowedit = has_capability('mod/hittrainingsnachweis:edit', $context);


//$PAGE->set_context(\context_system::instance());
$PAGE->set_title("Trainingsnachweis");
$PAGE->add_body_class('mod_hittrainingsnachweis');
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_cm($cm, $course); // sets up global $COURSE

echo $OUTPUT->header();

if (!empty($USER->id) && !isguestuser()) {
    if($allowedit){
        // Trainer
        /*$myentries = $DB->get_records('hittrainingsnachweis_entry', array(
            'hittrainingsnachweisid' => $hittrainingsnachweis->id ,
            'studentid' => $studentid,
        ));*/
        $myentries = $DB->get_records_sql(
            "SELECT 
                    a.*, u.firstname as abzeichner_firstname, u.lastname as abzeichner_lastname
                  from {hittrainingsnachweis_entry} as a
                    left join mdl_user as u on a.abzeichnerid=u.id
                  where hittrainingsnachweisid = :hittnid and studentid = :studentid",
            array( "hittnid" => $hittrainingsnachweis->id, "studentid" => $studentid));
        $myentries = array_values($myentries);
        foreach ($myentries as &$entry) {
            $entry->training_dttm = date('Y-m-d H:i:s',$entry->training_dttm);
            if($entry->abgezeichnet == 'Y'){
                $entry->abgezeichnet = 'Ja';
            } else {
                $entry->abgezeichnet = 'Nein';
            }
            if($entry->abzeichner_lastname){
                $entry->abgezeichnet .= " (durch ".$entry->abzeichner_firstname." ".$entry->abzeichner_lastname.")";
            }
            $entry->image = moodle_url::make_pluginfile_url(
                $context->id, $cm->id, 'entityuploads',
                $entry->id, '/', 'photo.jpg', false)->__toString();
            if($entry->attachmentid){
                $fs = get_file_storage();
                $files = $fs->get_area_files(
                    $context->id, 'mod_hittrainingsnachweis', 'attachment',
                    $entry->id, $sort = "itemid, filepath, filename",
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
                    $entry->image = $url[1];
                }
            } else {
                $entry->image = "https://www.herolditservice.de/wp-content/uploads/2022/02/placeholder.png";
            }
            if(!$entry->bewertung){
                $entry->bewertung = 0;
            }
        }

        $student = $DB->get_record('user', array('id' => $studentid), '*', MUST_EXIST);

        $templatecontext = (object)[
            'cmid' => $cmid,
            'hittrainingsnachweisid' => $hittrainingsnachweis->id ,
            'studentid' => $studentid,
            'studentname' => $student->firstname." ".$student->lastname,
            'intro' => $hittrainingsnachweis->intro,
            'wiederholungen' => $hittrainingsnachweis->wiederholungen,
            'entries' => $myentries,
            'training_wiederholungen' => $hittrainingsnachweis->wiederholungen,
            'training_wiederholt' => sizeof($myentries),
        ];
        echo $OUTPUT->render_from_template('mod_hittrainingsnachweis/viewstudententries', $templatecontext);


    } else {
        // Students
        echo "Dir fehlen Berechtigungen um Trainingsnachweise abzuzeichnen";

    }

} else {
    echo "no access for guests";
}


echo $OUTPUT->download_dataformat_selector(
    get_string('Download Trainingsnachweise', 'hittrainingsnachweis'),
    '/mod/hittrainingsnachweis/download.php',
    'documenttype',
    array(
        'cmid' => $cmid,
        'hittrainingsnachweisid' => $hittrainingsnachweis->id ,
        'studentid' => $studentid,
    )
);


echo $OUTPUT->footer();