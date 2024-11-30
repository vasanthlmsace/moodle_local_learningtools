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
 * Define upgrade function
 * @package    ltool_timemanagement
 * @copyright  bdecent GmbH 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * ltool_timemanagement upgrade function.
 * @param int $oldversion old plugin version
 * @return bool
 */
function xmldb_ltool_timemanagement_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2022022600) {
        $oldtable = new xmldb_table('timemanagment_modules_dates');
        if ($dbman->table_exists($oldtable)) {
            $dbman->rename_table($oldtable, 'ltool_timemanagement_modules');
        }
        $table = new xmldb_table('timemanagment_course_dates');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'ltool_timemanagement_course');
        }
        upgrade_plugin_savepoint(true, 2022022600, 'ltool', 'timemanagement');
    }
    return true;
}
