<?php
require('../../config.php');
require_once('lib.php');

global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE;

$id = optional_param('id', 0, PARAM_INT); // Course Module ID.

if ($id) {
    $cm = get_coursemodule_from_id('hittrainingsnachweis', $id, 0, false, MUST_EXIST);
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

// Check if user is Student by Capability
    // Display New Form
    // Display list of Entries

// check if user is Teacher
    // Display Students
        // Display show Form Buttons
    // Display Edit Activity BTN




if (!empty($USER->id) && !isguestuser()) {

    if($allowedit){
        // Trainer
        $enrolledStudents = get_users_by_capability($context, 'mod/hittrainingsnachweis:read');
        $enrolledStudents = array_values($enrolledStudents);

        $trainingsnachweisEntries = $DB->get_records('hittrainingsnachweis_entry', array(
            'hittrainingsnachweisid' => $hittrainingsnachweis->id,
        ));
        $trainingsnachweisEntries = array_values($trainingsnachweisEntries);
        foreach ($trainingsnachweisEntries as &$entry) {
            $entry->training_dttm = date('Y-m-d H:i:s',$entry->training_dttm);
        }

        $data = array();
        foreach($enrolledStudents as &$enrolledStudent){
            $student = array();
            $student["id"] = $enrolledStudent->id;
            $student["username"] = $enrolledStudent->username;
            $student["entries"] = array();
            foreach($trainingsnachweisEntries as &$entry){
                if($entry->studentid == $enrolledStudent->id){
                    array_push($student["entries"], $entry);
                }
            }
            $student["cnt_entries"] = sizeof($student["entries"]);
            array_push($data, $student);
        }



        $templatecontext = (object)[
            'intro' => $hittrainingsnachweis->intro,
            'cmid' => $cm->id,
            'wiederholungen' => $hittrainingsnachweis->wiederholungen,
            'new_url' => '/mod/hittrainingsnachweis/new.php?cmid='.$cm->id,
            'entries' => $data,
        ];
        echo $OUTPUT->render_from_template('mod_hittrainingsnachweis/viewtrainer', $templatecontext);


    } else {

    }
    // Students
    $myentries = $DB->get_records_sql(
        "SELECT 
                    a.*, u.firstname as abzeichner_firstname, u.lastname as abzeichner_lastname
                  from {hittrainingsnachweis_entry} as a
                    left join mdl_user as u on a.abzeichnerid=u.id
                  where hittrainingsnachweisid = :hittrainingsnachweisid and studentid = :studentid",
        array( "hittrainingsnachweisid" => $hittrainingsnachweis->id, "studentid" => $USER->id));

    $myentries = array_values($myentries);
    foreach ($myentries as &$entry) {
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

        $entry->training_dttm = date('Y-m-d H:i:s',$entry->training_dttm);
        if($entry->abgezeichnet == 'Y'){
            $entry->abgezeichnet = 'Ja';
        } else {
            $entry->abgezeichnet = 'Nein';
        }
        if($entry->abzeichner_lastname){
            $entry->abgezeichnet .= " (durch ".$entry->abzeichner_firstname." ".$entry->abzeichner_lastname.")";
        }
        switch($entry->bewertung){
            case "0":
                $entry->bewertung = 'nicht bewertet';
                break;
            case "1":
                $entry->bewertung = 'sehr gut';
                break;
            case "2":
                $entry->bewertung = 'gut';
                break;
            case "3":
                $entry->bewertung = 'neutral';
                break;
            case "4":
                $entry->bewertung = 'schlecht';
                break;
            case "5":
                $entry->bewertung = 'sehr schlecht';
                break;
            default:
                $entry->bewertung = 'nicht bewertet';
        }
    }

    $templatecontext = (object)[
        'intro' => $hittrainingsnachweis->intro,
        'wiederholungen' => $hittrainingsnachweis->wiederholungen,
        'new_url' => '/mod/hittrainingsnachweis/new.php?cmid='.$cm->id,
        'entries' => $myentries,
        'training_wiederholungen' => $hittrainingsnachweis->wiederholungen,
        'training_wiederholt' => sizeof($myentries),
    ];
    echo $OUTPUT->render_from_template('mod_hittrainingsnachweis/viewstudent', $templatecontext);





} else {
    echo "no access for guests";
}




echo $OUTPUT->footer();