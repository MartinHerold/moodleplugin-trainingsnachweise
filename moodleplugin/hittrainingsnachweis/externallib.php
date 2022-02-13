<?php



/**
 * Certificate module external functions
 *
 * @package    mod_hittrainingsnachweis
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hittrainingsnachweis_external extends external_api {

    /**
     * Describes the parameters for mobile_create_new_trainingsnachweis_.
     *
     * @return external_function_parameters
     */
    public static function mobile_create_new_trainingsnachweis_parameters() {
        return new external_function_parameters (
            array(
                'hittrainingsnachweisid' => new external_value(PARAM_INT, 'trainingsnachweis id'),
                'studentid' => new external_value(PARAM_INT, 'student id'),
                'beschreibung' => new external_value(PARAM_TEXT, 'beschreibung', false),
                'timestamp' =>  new external_value(PARAM_TEXT, 'datum und zeit', false),
                'file' =>  new external_value(PARAM_FILE, 'bild', false),
            )
        );
    }

    /**
     * Create new certificate record, or return existing record.
     *
     * @param int $certificateid the certificate instance id
     * @return array of warnings and status result
     * @throws moodle_exception
     */
    public static function mobile_create_new_trainingsnachweis($trainingsnachweisid, $studentid, $beschreibung, $timestamp, $file) {
        global $USER;

        $params = self::validate_parameters(self::mobile_create_new_trainingsnachweis_parameters(),
            array(
                'hittrainingsnachweisid' => $trainingsnachweisid,
                'studentid' => $studentid,
                'beschreibung' => $beschreibung,
                'timestamp' => $timestamp,
                'file' => $file
            )
        );
        $warnings = array();

        // Request and permission validation.
        list($trainingsnachweis, $course, $cm, $context) = self::check_can_create_trainingsnachweis($params['hittrainingsnachweisid']);

        //$issue = certificate_get_issue($course, $USER, $certificate, $cm);
        //self::add_extra_issue_data($issue, $certificate, $course, $cm, $context);
        $returncode = hittrainingsnachweis_entry_add($course, $context, $cm, $trainingsnachweisid, $studentid, $beschreibung, $timestamp, $file);

        $result = array();
        //$result['issue'] = $issue;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function mobile_create_new_trainingsnachweis_returns() {

        return new external_single_structure(
            array(
                'issue' => "", //self::issued_structure(),
                'warnings' => new external_warnings()
            )
        );
    }



    /**
     * Describes the parameters for mobile_create_new_trainingsnachweis_.
     *
     * @return external_function_parameters
     */
    public static function mobile_trainingsnachweis_abzeichnen_parameters() {
        return new external_function_parameters (
            array(
                'hittrainingsnachweisid' => new external_value(PARAM_INT, 'hid', false),
                'studentid' => new external_value(PARAM_INT, 'studentid', false),
                'entryid' => new external_value(PARAM_INT, 'id'),
                'bewertung' => new external_value(PARAM_INT, 'bewertung'),
                'abgezeichnet' => new external_value(PARAM_INT, 'abgezeichnet', false),
            )
        );
    }

    /**
     * Create new certificate record, or return existing record.
     *
     * @param
     * @return array of warnings and status result
     * @throws moodle_exception
     */
    public static function mobile_trainingsnachweis_abzeichnen($hittrainingsnachweisid, $studentid, $entryid, $bewertung, $abgezeichnet) {
        global $USER;

        $params = self::validate_parameters(self::mobile_trainingsnachweis_abzeichnen_parameters(),
            array(
                'hittrainingsnachweisid' => $hittrainingsnachweisid,
                'studentid' => $studentid,
                'entryid' => $entryid,
                'bewertung' => $bewertung,
                'abgezeichnet' => $abgezeichnet,
            )
        );
        $warnings = array();

        // Request and permission validation.
        list($trainingsnachweis, $course, $cm, $context) = self::check_can_abzeichnen_trainingsnachweis($params['hittrainingsnachweisid']);

        //$issue = certificate_get_issue($course, $USER, $certificate, $cm);
        //self::add_extra_issue_data($issue, $certificate, $course, $cm, $context);
        $returncode = hittrainingsnachweis_entry_abzeichnen($course, $context, $cm, $entryid, $bewertung, $abgezeichnet);

        $result = array();
        //$result['issue'] = $issue;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function mobile_trainingsnachweis_abzeichnen_returns() {

        return new external_single_structure(
            array(
                'issue' => "", //self::issued_structure(),
                'warnings' => new external_warnings()
            )
        );
    }



    /**
     * Check if the user can create a new trainingsnachweis.
     *
     * @param
     * @return
     */
    private static function check_can_create_trainingsnachweis($hittrainingsnachweisid) {
        global $DB;

        $trainingsnachweise = $DB->get_record('hittrainingsnachweis', array('id' => $hittrainingsnachweisid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($trainingsnachweise, 'hittrainingsnachweis');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/hittrainingsnachweis:read', $context);

        return array($trainingsnachweise, $course, $cm, $context);
    }

    /**
     * Check if the user can edit a trainingsnachweis.
     *
     * @param
     * @return
     */
    private static function check_can_abzeichnen_trainingsnachweis($hittrainingsnachweisid) {
        global $DB;

        $trainingsnachweise = $DB->get_record('hittrainingsnachweis', array('id' => $hittrainingsnachweisid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($trainingsnachweise, 'hittrainingsnachweis');

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/hittrainingsnachweis:edit', $context);

        return array($trainingsnachweise, $course, $cm, $context);
    }



}





function hittrainingsnachweis_entry_add($course, $context, $cm, $hittraininsnachweisid, $studentid, $beschreibung, $timestamp, $fileinput){
    global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;
    $data = (object) array();
    $data->timecreated = time();
    $data->timemodified = time();
    $data->abgezeichnet = 'N';
    $data->studentid = $studentid;
    $data->hittrainingsnachweisid = $hittraininsnachweisid;
    $data->beschreibung = $beschreibung;
    if($timestamp != ""){
        $dtime = DateTime::createFromFormat("Y-m-d H:i:s", str_replace("Z", "", str_replace("T", " ", $timestamp)));;
        $data->training_dttm = $dtime->getTimestamp();
    }
    $data->id = $DB->insert_record('hittrainingsnachweis_entry', $data);
    file_save_draft_area_files($fileinput, $context->id, 'mod_hittrainingsnachweis', 'attachment',
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
        $message->contexturl = (new \moodle_url('/mod/hittrainingsnachweis/view_studententries.php?cmid='.$cm->id.'&studentid='.$USER->id))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Eintrag'; // Link title explaining where users get to for the contexturl
        $messageid = message_send($message);
    }
    return true;
}

function hittrainingsnachweis_entry_abzeichnen($course, $context, $cm, $entryid, $bewertung, $abgezeichnet){
    global $CFG, $DB, $USER, $PAGE, $OUTPUT, $SITE, $SESSION;
    $data = (object) array();
    $data->id = $entryid;
    $data->bewertung = $bewertung;
    if($bewertung > 0){
        $data->abgezeichnet = 'Y';
    } else {
        $data->abgezeichnet = 'N';
    }
    $data->abzeichnerid = $USER->id;
    $DB->update_record('hittrainingsnachweis_entry', $data, $bulk=false);

    return true;
}

