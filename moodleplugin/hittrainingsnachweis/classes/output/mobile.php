<?php

namespace mod_hittrainingsnachweis\output;

use context_module;
use mod_hittrainingsnachweis_external;

/**
 * Mobile output class for hittrainingsnachweis
 *
 * @package    mod_hittrainingsnachweis
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile
{

    /**
     * Returns the hittrainingsnachweis course view for the mobile app.
     * @param array $args Arguments from tool_mobile_get_content WS
     *
     * @return array       HTML, javascript and otherdata
     */
    public static function mobile_trainingsnachweis_view($args)
    {
        global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE;

        $args = (object) $args;
        $cm = get_coursemodule_from_id('hittrainingsnachweis', $args->cmid);
        // Capabilities check.
        require_course_login($args->courseid , false , $cm, true, true);
        $context = \context_module::instance($cm->id);

        require_capability ('mod/hittrainingsnachweis:read', $context);
        $allowedit = has_capability('mod/hittrainingsnachweis:edit', $context);

        $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);


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
                    $student['cmid'] = $cm->id;
                    $student['courseid'] = $args->courseid;
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
                    'courseid' => $args->courseid,
                ];
                return [
                    'templates' => [
                        [
                            'id' => 'main',
                            'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_viewtrainer', $templatecontext),
                        ],
                    ],
                    'javascript' => '',
                    'otherdata' => '',
                ];

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
                            $tmp = \moodle_url::make_pluginfile_url(
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
                'cmid' => $cm->id,
                'courseid' => $args->courseid,
                "cmurl" => new \moodle_url('/mod/hittrainingsnachweis/view.php', array('id' => $cm->id)),
            ];
            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_viewstudent', $templatecontext),
                    ],
                ],
                'javascript' => '',
                'otherdata' => '',
            ];


        } else {
            echo "no access for guests";
        }


    }

    public static function mobile_trainingsnachweis_new($args){

        global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;
        $args = (object) $args;
        $cmid = $args->cmid;
        $cm = get_coursemodule_from_id('hittrainingsnachweis', $args->cmid);
        $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);

        require_course_login($args->courseid , false , $cm, true, true);
        $context = \context_module::instance($cm->id);


        //$options = array('noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'maxbytes' => 0);
        $filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 50, 'context' => $context);
        $customdata = array('filemanageropts' => $filemanageropts);

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_trainingsnachweis_new', array(
                        "cmid" => $cmid,
                        "cmurl" => new \moodle_url('/mod/hittrainingsnachweis/new.php', array('cmid' => $cmid)),
                        "courseid" => $hittrainingsnachweis->course,
                        "context" => $context,
                        "hittrainingsnachweisid" => $hittrainingsnachweis->id,
                        "studentid" => $USER->id,
                    )),
                ],
            ],
            'javascript' => file_get_contents($CFG->dirroot . '/mod/hittrainingsnachweis/appjs/new.js'),
            'otherdata' => '',
        ];



    }

    public static function mobile_trainingsnachweis_viewstudententries($args){

        global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;
        $args = (object) $args;

        $cmid = $args->cmid;
        $studentid = $args->studentid;
        $cm = get_coursemodule_from_id('hittrainingsnachweis', $args->cmid);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);
        require_course_login($course, true, $cm);
        $context = context_module::instance($cm->id);
        require_capability('mod/hittrainingsnachweis:read', $context);
        $allowedit = has_capability('mod/hittrainingsnachweis:edit', $context);

        if (!empty($USER->id) && !isguestuser()) {
            if($allowedit){
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
                    $entry->cmid = $cmid;
                    $entry->courseid = $cm->course;
                    $entry->studentid = $studentid;
                    if($entry->abgezeichnet == 'Y'){
                        $entry->abgezeichnet = 'Ja';
                    } else {
                        $entry->abgezeichnet = 'Nein';
                    }
                    if($entry->abzeichner_lastname){
                        $entry->abgezeichnet .= " (durch ".$entry->abzeichner_firstname." ".$entry->abzeichner_lastname.")";
                    }
                    $entry->image = \moodle_url::make_pluginfile_url(
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
                                $tmp = \moodle_url::make_pluginfile_url(
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
                    "cmurl" => new \moodle_url('/mod/hittrainingsnachweis/view.php', array('id' => $cmid)),
                ];
                return [
                    'templates' => [
                        [
                            'id' => 'main',
                            'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_viewstudententries', $templatecontext),
                        ],
                    ],
                    'javascript' => '',
                    'otherdata' => '',
                ];

            } else {
                // Students
                //echo "Dir fehlen Berechtigungen um Trainingsnachweise abzuzeichnen";

            }

        } else {
           // echo "no access for guests";
        }

    }

    public static function mobile_trainingsnachweis_viewstudententries_abzeichnen($args){

        global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;
        $args = (object) $args;

        $cmid = $args->cmid;
        $entryid = $args->id;
        $studentid = $args->studentid;
        $cm = get_coursemodule_from_id('hittrainingsnachweis', $args->cmid);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $hittrainingsnachweis = $DB->get_record('hittrainingsnachweis', array('id' => $cm->instance), '*', MUST_EXIST);
        require_course_login($course, true, $cm);
        $context = context_module::instance($cm->id);
        require_capability('mod/hittrainingsnachweis:read', $context);
        $allowedit = has_capability('mod/hittrainingsnachweis:edit', $context);

        if (!empty($USER->id) && !isguestuser()) {
            if($allowedit){
                $myentries = $DB->get_records_sql(
                    "SELECT 
                    a.*, u.firstname as abzeichner_firstname, u.lastname as abzeichner_lastname
                  from {hittrainingsnachweis_entry} as a
                    left join mdl_user as u on a.abzeichnerid=u.id
                  where hittrainingsnachweisid = :hittnid and a.id = :id",
                    array( "hittnid" => $hittrainingsnachweis->id, "id" => $entryid));
                $myentries = array_values($myentries);
                foreach ($myentries as &$entry) {
                    $entry->training_dttm = date('Y-m-d H:i:s',$entry->training_dttm);
                    $entry->cmid = $cmid;
                    $entry->courseid = $cm->course;
                    if($entry->abgezeichnet == 'Y'){
                        $entry->abgezeichnet = 'Ja';
                    } else {
                        $entry->abgezeichnet = 'Nein';
                    }
                    if($entry->abzeichner_lastname){
                        $entry->abgezeichnet .= " (durch ".$entry->abzeichner_firstname." ".$entry->abzeichner_lastname.")";
                    }
                    $entry->image = \moodle_url::make_pluginfile_url(
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
                                $tmp = \moodle_url::make_pluginfile_url(
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
                    'courseid' => $course->id,
                    'hittrainingsnachweisid' => $hittrainingsnachweis->id ,
                    'studentid' => $studentid,
                    'studentname' => $student->firstname." ".$student->lastname,
                    'intro' => $hittrainingsnachweis->intro,
                    'wiederholungen' => $hittrainingsnachweis->wiederholungen,
                    'image' => $myentries[0]->image,
                    'training_dttm' => $myentries[0]->training_dttm,
                    'beschreibung' => $myentries[0]->beschreibung,
                    'abgezeichnet' => $myentries[0]->abgezeichnet,
                    "id" => $myentries[0]->id,
                    'bewertung' => $myentries[0]->bewertung,
                    'training_wiederholungen' => $hittrainingsnachweis->wiederholungen,
                    'training_wiederholt' => sizeof($myentries),
                    "cmurl" => new \moodle_url('/mod/hittrainingsnachweis/view.php', array('id' => $cmid)),
                ];
                return [
                    'templates' => [
                        [
                            'id' => 'main',
                            'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_viewstudententries_abzeichnen', $templatecontext),
                        ],
                    ],
                    'javascript' => '',
                    'otherdata' => '',
                ];

            } else {
                // Students
                //echo "Dir fehlen Berechtigungen um Trainingsnachweise abzuzeichnen";

            }

        } else {
            // echo "no access for guests";
        }

    }

    public static function mobile_new_trainingsnachweis($args){
        $args = (object) $args;
        global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE;
        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('mod_hittrainingsnachweis/mobile_viewstudent', array()),
                ],
            ],
            'javascript' => '',
            'otherdata' => '',
        ];

    }
}
