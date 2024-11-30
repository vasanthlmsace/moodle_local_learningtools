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
 * ltool plugin "Learning Tools Force activity" - library file.
 *
 * @package   ltool_forceactivity
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Learning tools forceactivity template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_forceactivity_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_forceactivity/forceactivity', $templatecontent);
}

/**
 * Load js data
 */
function ltool_forceactivity_load_js_config() {
    global $PAGE, $USER;
    $params = [];
    if (!empty($PAGE->course->id)) {
        $params['course'] = $PAGE->course->id;
        $params['user'] = $USER->id;
        $params['contextid'] = $PAGE->context->id;
        $PAGE->requires->js_call_amd('ltool_forceactivity/forceactivity', 'init', array($params));
    }
}

/**
 * Load the force activity form
 * @param array $args page arguments
 * @return string Display the html invite users form.
 */
function ltool_forceactivity_output_fragment_get_forceactivitymodal_form($args) {
    global $DB;
    $forceactivitydata = $DB->get_record('ltool_forceactivity_data', array('courseid' => $args['course']));
    if (!empty($forceactivitydata)) {
        $args['cmid'] = $forceactivitydata->cmid;
        $args['message'] = $forceactivitydata->message;
    }
    $inviteform = new ltool_forceactivity_modalform(null, $args);
    $formhtml = html_writer::start_tag('div', array('id' => 'forceactivity-modalinfo'));
    $formhtml .= $inviteform->render();
    $formhtml .= html_writer::end_tag('div');
    return $formhtml;
}

/**
 * Get force activities list in course
 * @param int $courseid
 * @return array list of force activities
 */
function ltool_forceactivity_get_array_of_activities($courseid) {
    global $DB;
    $data = [];
    $data[] = get_string("noactivity", 'local_learningtools');
    $course = $DB->get_record('course', array('id' => $courseid));
    $activities = course_modinfo::get_array_of_activities($course);
    $modinfo = get_fast_modinfo($courseid);
    if (!empty($activities)) {
        foreach ($activities as $activity) {
            $cminfo = $modinfo->get_cm($activity->cm);
            $coursemodule = $DB->get_record('course_modules', array('id' => $activity->cm, 'deletioninprogress' => 0));
            if ($coursemodule->completion && $cminfo->url && $coursemodule->visible) {
                $data[$activity->cm]  = $activity->name;
            }
        }
    }
    return $data;
}

/**
 * store the Force activity info
 *
 * @param [object] $params
 * @param [array] $data
 * @return bool status
 */
function ltool_forceactivity_activityaction($params, $data) {
    global $DB, $USER;
    $context = context_course::instance($params->course);
    if (isset($data['forceactivity']) && has_capability("ltool/forceactivity:createforceactivity", $context)) {
        $record = new stdclass;
        $record->courseid = $params->course;
        $record->cmid = $data['forceactivity'];
        $record->teacher = $USER->id;
        $messageinfo = !empty($data['messageinfo']['text']) ? $data['messageinfo']['text'] : "";
        $record->message = trim($messageinfo);
        $existrecord = $DB->get_record('ltool_forceactivity_data', array('courseid' => $params->course));
        if (empty($existrecord)) {
            $record->timecreated = time();
            $DB->insert_record('ltool_forceactivity_data', $record);
        } else {
            $record->id = $existrecord->id;
            $record->timemodified = time();
            $DB->update_record('ltool_forceactivity_data', $record);
        }
        if (!$data['forceactivity']) {
            return false;
        }
        return true;
    }
    return false;
}

/**
 * Redirect the Force activity.
 * @param string $courseid course id.
 * @param string $pagetype page type
 * @return void
 */
function load_forceactivity_action_coursepage($courseid, $pagetype) {
    global $DB, $USER, $PAGE;
    $course = $DB->get_record('course', array('id' => $courseid));
    $record = $DB->get_record('ltool_forceactivity_data', array('courseid' => $courseid));
    $coursecontext = context_course::instance($course->id);
    if (is_enrolled($coursecontext, $USER)) {
        if (!empty($record)) {
            if ($PAGE->course->id == $course->id) {
                if (isset($PAGE->cm->id)) {
                    if ($PAGE->cm->id == $record->cmid) {
                        return '';
                    } else {
                        ltool_forceactivity_redirect_forceactivity($record);
                    }
                } else {
                    ltool_forceactivity_redirect_forceactivity($record);
                }
            }
        }
    }
}

/**
 * redirect to set forceactivity
 * @param stdclass $record
 * @return void
 */
function ltool_forceactivity_redirect_forceactivity($record) {
    global $DB, $USER, $PAGE;
    if (!$DB->record_exists('course_modules_completion', array('coursemoduleid' => $record->cmid,
        'userid' => $USER->id, 'completionstate' => 1))) {
        if (!empty($record->cmid)) {
            if ($DB->record_exists('course_modules', array('id' => $record->cmid, 'deletioninprogress' => 0))) {
                $modinfo = get_fast_modinfo($PAGE->course->id);
                $cminfo = $modinfo->get_cm($record->cmid);
                if ($cminfo->uservisible) {
                    $modinfo = new stdClass();
                    $modinfo->coursemodule = $record->cmid;
                    $modname = local_learningtools_get_module_name($modinfo, true);
                    $forceurl = "/mod/".$modname."/view.php";
                    $forceurl = new moodle_url($forceurl, ['id' => $record->cmid]);
                    redirect($forceurl, $record->message, null, \core\output\notification::NOTIFY_WARNING);
                } else {
                    $returnurl = new moodle_url('/course/view.php', ['id' => $record->courseid, 'forceactivity' => true]);
                    redirect( $returnurl, $record->message, null, \core\output\notification::NOTIFY_WARNING);
                }
            }
        }
    }
}
/**
 * Display invite user email textarea
 */
class ltool_forceactivity_modalform extends moodleform {

    /**
     * Add elements to form.
     */
    public function definition() {
        $mform = $this->_form;
        $courseid = $this->_customdata['course'];
        $cmid = !empty($this->_customdata['cmid']) ? $this->_customdata['cmid'] : 0;
        $message = !empty($this->_customdata['message']) ? $this->_customdata['message'] : '';
        $courseactivites = ltool_forceactivity_get_array_of_activities($courseid);
        $mform->addElement('select', 'forceactivity', get_string('courseactivity', 'local_learningtools'), $courseactivites);
        $mform->setDefault('forceactivity', $cmid);
        $mform->addElement('editor', 'messageinfo', get_string('message', 'local_learningtools'),
            array('autosave' => false))->setValue(array('text' => $message));
    }
}
