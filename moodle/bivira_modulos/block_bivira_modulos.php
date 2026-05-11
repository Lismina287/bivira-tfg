<?php
defined('MOODLE_INTERNAL') || die();

class block_bivira_modulos extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_bivira_modulos');
    }

    public function get_content() {
        global $DB, $USER, $COURSE, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = get_string('notloggedin', 'block_bivira_modulos');
            return $this->content;
        }

        $html = '';
        $isadmin = is_siteadmin($USER->id);

	// ── Panel de administración (solo admin) ────────────────────────────
        if ($isadmin) {
            $pendientes = $DB->count_records('block_bivira_solicitudes', ['estado' => 'pendiente']);
            $urlAdmin   = new moodle_url('/blocks/bivira_modulos/admin_solicitudes.php');

            if ($pendientes > 0) {
                $html .= '<a href="'.$urlAdmin.'" class="btn btn-sm btn-danger w-100 mb-2">
                            📋 Solicitudes pendientes
                            <span class="badge bg-white text-danger ms-1">'.$pendientes.'</span>
                          </a>';
            } else {
                $html .= '<a href="'.$urlAdmin.'" class="btn btn-sm btn-outline-secondary w-100 mb-2">
                            📋 Gestionar solicitudes
                          </a>';
            }
            $html .= '<hr>';
        }

	// ── Dentro de un curso ──────────────────────────────────────────────
        if ($COURSE->id != SITEID) {
            $context = context_course::instance($COURSE->id);

            $siguiendo = $DB->record_exists('block_bivira_seguidos', [
                'userid'   => $USER->id,
                'courseid' => $COURSE->id
            ]);

            $urlSeguir = new moodle_url('/blocks/bivira_modulos/action.php', [
                'courseid' => $COURSE->id,
                'action'   => $siguiendo ? 'dejar' : 'seguir',
                'sesskey'  => sesskey()
            ]);

            if ($siguiendo) {
                $html .= '<a href="'.$urlSeguir.'" class="btn btn-sm btn-danger w-100 mb-3">✖ Dejar de seguir este módulo</a>';
            } else {
                $html .= '<a href="'.$urlSeguir.'" class="btn btn-sm btn-success w-100 mb-3">➕ Seguir este módulo</a>';
            }

            if (!$isadmin && !has_capability('mod/resource:addinstance', $context)) {
                $solicitud = $DB->get_record('block_bivira_solicitudes', [
                    'userid'   => $USER->id,
                    'courseid' => $COURSE->id
                ]);

                if (!$solicitud) {
                    $urlSolicitar = new moodle_url('/blocks/bivira_modulos/request.php', [
                        'courseid' => $COURSE->id,
                        'action'   => 'solicitar',
                        'sesskey'  => sesskey()
                    ]);
                    $html .= '<a href="'.$urlSolicitar.'" class="btn btn-sm btn-warning w-100 mb-3">📝 Solicitar acceso como Colaborador</a>';
                } else if ($solicitud->estado === 'pendiente') {
                    $html .= '<div class="alert alert-info p-2 small">⏳ Solicitud pendiente de aprobación</div>';
                } else if ($solicitud->estado === 'aprobada') {
                    $html .= '<div class="alert alert-success p-2 small">✅ Eres Colaborador en este módulo</div>';
                } else if ($solicitud->estado === 'rechazada') {
                    $html .= '<div class="alert alert-danger p-2 small">❌ Solicitud rechazada</div>';
                }
            }

            if ($isadmin) {
                $solicitudes = $DB->get_records('block_bivira_solicitudes', [
                    'courseid' => $COURSE->id,
                    'estado'   => 'pendiente'
                ]);

                if (!empty($solicitudes)) {
                    $html .= '<hr><h6><strong>📋 Solicitudes pendientes</strong></h6>';
                    foreach ($solicitudes as $sol) {
                        $soluser = $DB->get_record('user', ['id' => $sol->userid]);
                        $urlAprobar = new moodle_url('/blocks/bivira_modulos/request.php', [
                            'courseid' => $COURSE->id,
                            'userid'   => $sol->userid,
                            'action'   => 'aprobar',
                            'sesskey'  => sesskey()
                        ]);
                        $urlRechazar = new moodle_url('/blocks/bivira_modulos/request.php', [
                            'courseid' => $COURSE->id,
                            'userid'   => $sol->userid,
                            'action'   => 'rechazar',
                            'sesskey'  => sesskey()
                        ]);
                        $html .= '<div class="mb-2 small"><strong>'.fullname($soluser).'</strong><br>
                                  <a href="'.$urlAprobar.'" class="btn btn-xs btn-success">✅ Aprobar</a>
                                  <a href="'.$urlRechazar.'" class="btn btn-xs btn-danger">❌ Rechazar</a></div>';
                    }
                }
            }
        }


	// ── Módulos seguidos ────────────────────────────────────────────────
        $seguidos = $DB->get_records('block_bivira_seguidos', ['userid' => $USER->id]);

        if (!empty($seguidos)) {
            $html .= '<hr><h6><strong>📚 Mis módulos seguidos</strong></h6><ul class="list-unstyled">';
            foreach ($seguidos as $seguido) {
                $course = $DB->get_record('course', ['id' => $seguido->courseid]);
                if ($course) {
                    $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
                    $html .= '<li>📖 <a href="'.$courseurl.'">'.format_string($course->fullname).'</a></li>';
                }
            }
            $html .= '</ul>';
        } else {
            $html .= '<p class="text-muted small">Aún no sigues ningún módulo.</p>';
        }

	// ── Buscador ────────────────────────────────────────────────────────

	$searchurl = new moodle_url('/course/search.php');
	$html .= '<hr>
          	<h6><strong>🔍 Buscar módulo</strong></h6>
          	<form method="get" action="'.$searchurl.'">
              		<input type="text"
                     		name="search"
                     		class="form-control form-control-sm mb-2"
                     		placeholder="Nombre del módulo...">
              		<button type="submit" class="btn btn-sm btn-primary w-100">Buscar</button>
          	</form>';


        $this->content->text = $html;
        return $this->content;
    }

    public function applicable_formats() {
        return ['all' => true];
    }

    public function instance_allow_multiple() {
        return false;
    }
}
