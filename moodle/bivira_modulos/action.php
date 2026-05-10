<?php
require_once('../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$action   = required_param('action', PARAM_ALPHA);

require_sesskey();

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
require_capability('moodle/course:view', $context);

if ($action === 'seguir') {
    $existe = $DB->record_exists('block_bivira_seguidos', [
        'userid'   => $USER->id,
        'courseid' => $courseid
    ]);
    if (!$existe) {
        $record = new stdClass();
        $record->userid      = $USER->id;
        $record->courseid    = $courseid;
        $record->timecreated = time();
        $DB->insert_record('block_bivira_seguidos', $record);
    }
} else if ($action === 'dejar') {
    $DB->delete_records('block_bivira_seguidos', [
        'userid'   => $USER->id,
        'courseid' => $courseid
    ]);
}

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
