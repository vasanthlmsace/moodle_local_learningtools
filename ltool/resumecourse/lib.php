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
 * ltool plugin "Learning Tools Resume course" - library file.
 *
 * @package   ltool_resumecourse
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Learning tools resumecourse template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_resumecourse_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_resumecourse/resumecourse', $templatecontent);
}

/**
 * Load resume course js.
 * @return void
 */
function ltool_resumecourse_load_js_config() {
    global $PAGE, $USER;
    $params = [];
    $params['userid'] = $USER->id;
    $params['courseid'] = $PAGE->course->id;
    $params['contextid'] = $PAGE->context->id;
    $PAGE->requires->js_call_amd('ltool_resumecourse/resumecourse', 'init', array($params));
}

/**
 * Save the user access data
 * @return void
 */
function ltool_resumecourse_store_user_access_data() {
    global $DB, $PAGE, $USER;
    if ($PAGE->context->contextlevel == CONTEXT_MODULE) {
        $userrecord = $DB->get_record('ltool_resumecourse_data', array('userid' => $USER->id,
            'courseid' => $PAGE->course->id));
        if (empty($userrecord)) {
            $userrecord = new stdClass();
            $userrecord->userid = $USER->id;
            $userrecord->contexid = $PAGE->context->id;
            $userrecord->pageurl = $PAGE->url->out(false);
            $userrecord->timecreated = time();
            $userrecord->timemodified = time();
            $userrecord->courseid = $PAGE->course->id;
            $DB->insert_record('ltool_resumecourse_data', $userrecord);
        } else {
            $userrecord->contextid = $PAGE->context->id;
            $userrecord->pageurl = $PAGE->url->out(false);
            $userrecord->timemodified = time();
            $DB->update_record('ltool_resumecourse_data', $userrecord);
        }
    }
}

/**
 * Get the user last access the module url
 * @param object $params
 * @return string module url
 */
function ltool_resumecourse_lastaccess_activity_action($params) {
    global $DB, $SITE, $USER;
    $url = '';
    $message = '';
    if ($SITE->id != $params->courseid) {
        $userrecord = $DB->get_record('ltool_resumecourse_data', array('userid' => $USER->id,
            'courseid' => $params->courseid));
        if (!empty($userrecord)) {
            $url = $userrecord->pageurl;
        } else {
            $url = '';
            $message = get_string('donotresumecourse', 'local_learningtools');
        }
    } else {
        $sql = "SELECT * from {ltool_resumecourse_data} WHERE userid = :userid
        ORDER BY timemodified DESC
        LIMIT 1";
        $parmas = ['userid' => $USER->id];
        $userrecord = $DB->get_record_sql($sql, $parmas);
        if (!empty($userrecord)) {
            $url = $userrecord->pageurl;
        } else {
            $url = '';
            $message = get_string('donotresumecourse', 'local_learningtools');
        }
    }
    return ['url' => $url, 'message' => $message];
}
