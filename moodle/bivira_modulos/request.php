<?php
require_once('../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$action   = required_param('action', PARAM_ALPHA);

require_sesskey();

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

if ($action === 'solicitar') {
    $existe = $DB->record_exists('block_bivira_solicitudes', [
        'userid'   => $USER->id,
        'courseid' => $courseid
    ]);
    if (!$existe) {
        $record = new stdClass();
        $record->userid      = $USER->id;
        $record->courseid    = $courseid;
        $record->estado      = 'pendiente';
        $record->timecreated = time();
        $DB->insert_record('block_bivira_solicitudes', $record);
    }
} else if ($action === 'aprobar') {
    require_capability('moodle/role:assign', $context);
    $userid = required_param('userid', PARAM_INT);
    $solicitud = $DB->get_record('block_bivira_solicitudes', [
        'userid'   => $userid,
        'courseid' => $courseid
    ]);
    if ($solicitud) {
        $rol = $DB->get_record('role', ['shortname' => 'colaborador_bivira'], '*', MUST_EXIST);
        $enrol = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', [
            'courseid' => $courseid,
            'enrol'    => 'manual'
        ], '*', MUST_EXIST);
        $enrol->enrol_user($instance, $solicitud->userid, $rol->id);
        $solicitud->estado = 'aprobada';
        $DB->update_record('block_bivira_solicitudes', $solicitud);
    }
} else if ($action === 'rechazar') {
    require_capability('moodle/role:assign', $context);
    $userid = required_param('userid', PARAM_INT);
    $DB->set_field('block_bivira_solicitudes', 'estado', 'rechazada', [
        'userid'   => $userid,
        'courseid' => $courseid
    ]);
}

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
