<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_block_bivira_modulos_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025010101) {
        $table = new xmldb_table('block_bivira_solicitudes');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('estado', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pendiente');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('userid_courseid', XMLDB_INDEX_UNIQUE, array('userid', 'courseid'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_block_savepoint(true, 2025010101, 'bivira_modulos');
    }
    return true;
}
