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

// Notificar a todos los administradores del sitio
        $admins = get_admins();
        foreach ($admins as $admin) {
            $message                    = new \core\message\message();
            $message->component         = 'block_bivira_modulos';
            $message->name              = 'nueva_solicitud';
            $message->userfrom          = $USER;
            $message->userto            = $admin;
            $message->subject           = 'BiViR@: Nueva solicitud de Colaborador';
            $message->fullmessage       = fullname($USER) . ' ha solicitado el rol Colaborador en el módulo "' . format_string($course->fullname) . '".';
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml   = '<p>' . fullname($USER) . ' ha solicitado el rol <strong>Colaborador BiViR@</strong> en el módulo <strong>' . format_string($course->fullname) . '</strong>.</p>
                <p><a href="' . (new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php', ['tab' => 'pendientes']))->out() . '">Ver solicitudes pendientes</a></p>';
            $message->smallmessage      = fullname($USER) . ' solicita ser Colaborador en "' . format_string($course->fullname) . '"';
            $message->notification      = 1;
            $message->contexturl        = (new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php'))->out();
            $message->contexturlname    = 'Gestionar solicitudes';
            message_send($message);
        }
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
