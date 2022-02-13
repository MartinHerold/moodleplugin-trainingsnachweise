<?php

function xmldb_hittrainingsnachweis_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020112807) {

        // Define field abzeichnerid to be added to hittrainingsnachweis_entry.
        $table = new xmldb_table('hittrainingsnachweis_entry');
        $field = new xmldb_field('attachmentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'importsrc');

        // Conditionally launch add field abzeichnerid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hittrainingsnachweis savepoint reached.
        upgrade_mod_savepoint(true, 2020112807, 'hittrainingsnachweis');
    }


    return true;

    return true;
}