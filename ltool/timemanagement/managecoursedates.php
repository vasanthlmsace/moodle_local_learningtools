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
 * List of the user timemanagement.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once($CFG->dirroot. '/course/classes/list_element.php');
$courseid = required_param('course', PARAM_INT);
$context = context_course::instance($courseid);
require_capability('ltool/timemanagement:managedates', $context);
require_sesskey();
$course = get_course($courseid);
require_login($course);
$courselistelement = new core_course_list_element($course);
$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/timemanagement/managecoursedates.php',
    array('course' => $courseid, 'sesskey' => sesskey()));
$PAGE->set_title($courselistelement->get_formatted_shortname());
$PAGE->set_heading($courselistelement->get_formatted_fullname());
$PAGE->requires->css(new moodle_url("/local/learningtools/ltool/timemanagement/styles/datepicker.css"));
echo $OUTPUT->header();
if ($formdata = data_submitted()) {
    if (isset($formdata->managementupdatedates)) {
        ltool_timemanagement_updated_managedates((array)$formdata, $course->id);
        echo $OUTPUT->notification(get_string('savechanges'), 'success');
    }
}
$courseinfo = ltool_timemanagement_get_course_section_mod_info($course->id);
$template['datainfo'] = $courseinfo;
$manageurl = new moodle_url("/local/learningtools/ltool/timemanagement/managecoursedates.php");
$template['pageurl'] = $manageurl->out(false);
$template['sesskey'] = sesskey();
$template['courseid'] = $courseid;
$params = array('contextid' => $context->id, 'courseid' => $courseid);
$PAGE->requires->js_call_amd('ltool_timemanagement/managedates', 'init', array('params' => $params));
echo $OUTPUT->render_from_template('ltool_timemanagement/managedates', $template);
echo $OUTPUT->footer();
