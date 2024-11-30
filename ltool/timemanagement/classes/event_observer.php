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
 * Event observer function  definition and returns.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_timemanagement;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__).'/lib.php');

/**
 * Event observer class define.
 */
class event_observer {

    /**
     * Callback function will role assigned the course.
     * @param \core\event\role_assigned $event event data
     * @return void create the user calendar events for activities dates.
     */
    public static function create_user_managedates(\core\event\role_assigned $event) {
        global $DB;
        $userid = $event->relateduserid;
        $contextid = $event->contextid;
        $context = \context::instance_by_id($event->contextid, MUST_EXIST);
        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }
        $courseid = $context->instanceid;
        $studentroleids = array_keys(get_roles_with_capability('local/learningtools:studentcontroller'));
        if ($studentroleids) {
            list($studentsql, $params) = $DB->get_in_or_equal($studentroleids, SQL_PARAMS_NAMED);
            $sql = "SELECT id FROM {role_assignments} WHERE contextid = :contextid AND userid = :userid AND roleid $studentsql ";
            $params['contextid'] = $contextid;
            $params['userid'] = $userid;
            if ($DB->record_exists_sql($sql, $params)) {
                $modinfo = get_fast_modinfo($courseid);
                // Update the module dates.
                if (!empty($modinfo->sections)) {
                    foreach ($modinfo->sections as $section => $cmids) {
                        $section = $modinfo->get_section_info($section);
                        if (!empty($cmids) && $section->uservisible) {
                            foreach ($cmids as $cmid) {
                                $mod = $modinfo->get_cm($cmid);
                                if ($DB->record_exists('course_modules', array('id' => $mod->id, 'deletioninprogress' => 0))
                                    && $mod->visible) {
                                    ltool_timemanagement_user_calendar_module_dates($userid, $courseid, $cmid);
                                }
                            }
                        }
                    }
                }

                // Update course due date.
                $courseinfo = ltool_timemanagement_get_course_userinfo($courseid, $userid);
                $course = get_course($courseid);
                if (isset($courseinfo['courseduedate'])) {
                    ltool_timemanagement_create_user_event_courseduedates($userid, $course, $courseinfo['courseduedate']);
                }
            }
        }
    }
}
