<?php
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_url(new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('BiViR@ — Gestión de solicitudes');
$PAGE->set_heading('BiViR@ — Gestión de solicitudes de Colaborador');

// Procesar aprobación o rechazo desde esta página
$action  = optional_param('action', '', PARAM_ALPHA);
$solid   = optional_param('solid', 0, PARAM_INT);

if ($action && $solid && confirm_sesskey()) {
    $solicitud = $DB->get_record('block_bivira_solicitudes', ['id' => $solid]);
    if ($solicitud) {
        $context = context_course::instance($solicitud->courseid);
        if ($action === 'aprobar') {
            $rol      = $DB->get_record('role', ['shortname' => 'colaborador_bivira'], '*', MUST_EXIST);
            $enrol    = enrol_get_plugin('manual');
            $instance = $DB->get_record('enrol', [
                'courseid' => $solicitud->courseid,
                'enrol'    => 'manual'
            ], '*', MUST_EXIST);
            $enrol->enrol_user($instance, $solicitud->userid, $rol->id);
            $solicitud->estado = 'aprobada';
            $DB->update_record('block_bivira_solicitudes', $solicitud);
        } else if ($action === 'rechazar') {
            $solicitud->estado = 'rechazada';
            $DB->update_record('block_bivira_solicitudes', $solicitud);
        }
        redirect(new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php'),
                 $action === 'aprobar' ? 'Solicitud aprobada correctamente.' : 'Solicitud rechazada.',
                 null,
                 $action === 'aprobar' ? \core\output\notification::NOTIFY_SUCCESS : \core\output\notification::NOTIFY_WARNING);
    }
}

echo $OUTPUT->header();

// ── Pestaña activa ──────────────────────────────────────────────────────────
$tab = optional_param('tab', 'pendientes', PARAM_ALPHA);

$tabs = [
    new tabobject('pendientes',
        new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php', ['tab' => 'pendientes']),
        '⏳ Pendientes'),
    new tabobject('historial',
        new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php', ['tab' => 'historial']),
        '📋 Historial'),
];
echo $OUTPUT->tabtree($tabs, $tab);

// ── Pestaña Pendientes ──────────────────────────────────────────────────────
if ($tab === 'pendientes') {
    $pendientes = $DB->get_records('block_bivira_solicitudes', ['estado' => 'pendiente']);

    if (empty($pendientes)) {
        echo $OUTPUT->notification('No hay solicitudes pendientes.', 'notifysuccess');
    } else {
        $tabla  = new html_table();
        $tabla->head    = ['Usuario', 'Módulo', 'Fecha solicitud', 'Acciones'];
        $tabla->attributes['class'] = 'generaltable';

        foreach ($pendientes as $sol) {
            $usuario = $DB->get_record('user',   ['id' => $sol->userid]);
            $curso   = $DB->get_record('course', ['id' => $sol->courseid]);

            $urlAprobar  = new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php', [
                'action'  => 'aprobar',
                'solid'   => $sol->id,
                'sesskey' => sesskey(),
                'tab'     => 'pendientes'
            ]);
            $urlRechazar = new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php', [
                'action'  => 'rechazar',
                'solid'   => $sol->id,
                'sesskey' => sesskey(),
                'tab'     => 'pendientes'
            ]);

            $acciones = html_writer::link($urlAprobar,  '✅ Aprobar',  ['class' => 'btn btn-sm btn-success me-1']) .
                        html_writer::link($urlRechazar, '❌ Rechazar', ['class' => 'btn btn-sm btn-danger']);

            $tabla->data[] = [
                fullname($usuario),
                $curso ? format_string($curso->fullname) : '—',
                userdate($sol->timecreated),
                $acciones
            ];
        }
        echo html_writer::table($tabla);
    }
}

// ── Pestaña Historial ───────────────────────────────────────────────────────
if ($tab === 'historial') {
    $historial = $DB->get_records_select('block_bivira_solicitudes',
        "estado IN ('aprobada','rechazada')", [], 'timecreated DESC');

    if (empty($historial)) {
        echo $OUTPUT->notification('No hay solicitudes en el historial.', 'notifysuccess');
    } else {
        $tabla  = new html_table();
        $tabla->head    = ['Usuario', 'Módulo', 'Estado', 'Fecha solicitud'];
        $tabla->attributes['class'] = 'generaltable';

        foreach ($historial as $sol) {
            $usuario = $DB->get_record('user',   ['id' => $sol->userid]);
            $curso   = $DB->get_record('course', ['id' => $sol->courseid]);

            $estado = $sol->estado === 'aprobada'
                ? '<span class="badge bg-success">✅ Aprobada</span>'
                : '<span class="badge bg-danger">❌ Rechazada</span>';

            $tabla->data[] = [
                fullname($usuario),
                $curso ? format_string($curso->fullname) : '—',
                $estado,
                userdate($sol->timecreated)
            ];
        }
        echo html_writer::table($tabla);
    }
}

echo $OUTPUT->footer();
