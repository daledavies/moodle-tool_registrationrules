<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade steps
 *
 * @package tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute steps to upgrade the database to the expected state.
 *
 * @param int $oldversion the currently installed plugin version
 * @return void
 * @throws ddl_exception
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_tool_registrationrules_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024090302) {
        // Define table tool_registrationrules to be created.
        $table = new xmldb_table('tool_registrationrules');

        // Adding fields to table tool_registrationrules.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '144', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_CHAR, '1333', null, null, null, null);
        $table->add_field('points', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('fallbackpoints', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('other', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table tool_registrationrules.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for tool_registrationrules.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Registrationrules savepoint reached.
        upgrade_plugin_savepoint(true, 2024090302, 'tool', 'registrationrules');
    }

    if ($oldversion < 2024090304) {
        // Change the default of points column on tool_registrationrules from 0 to 100.
        $table = new xmldb_table('tool_registrationrules');
        $field = new xmldb_field('points', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100');
        $dbman->change_field_default($table, $field);

        // Registrationrules savepoint reached.
        upgrade_plugin_savepoint(true, 2024090304, 'tool', 'registrationrules');
    }

    if ($oldversion < 2025020701) {
        // Drop usermodified key.
        $table = new xmldb_table('tool_registrationrules');
        $key = new xmldb_key('primary', XMLDB_KEY_FOREIGN, ['usermodified']);
        $dbman->drop_key($table, $key);
        // Drop usermodified field.
        $field = new xmldb_field('usermodified');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        // Drop timecreated field.
        $field = new xmldb_field('timecreated');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        // Drop timemodified field.
        $field = new xmldb_field('timemodified');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Registrationrules savepoint reached.
        upgrade_plugin_savepoint(true, 2025020701, 'tool', 'registrationrules');
    }

    // Apparently we must always return true here, or we get an "unkown error" dusing upgrade.
    // See https://moodledev.io/docs/guides/upgrade#dbupgradephp for info.
    return true;
}
