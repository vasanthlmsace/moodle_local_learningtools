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
 * ltool plugin "Learning Tools Custom styles" - library file.
 *
 * @package   ltool_customstyles
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once($CFG->libdir.'/formslib.php');

/**
 * Define user edit the course customstyles form.
 */
class ltool_customstyle_stylebox extends moodleform {
    /**
     * Adds element to form
     */
    public function definition() {
        global $DB;

        $course = $this->_customdata['course'];
        $user = $this->_customdata['user'];
        $contextid = $this->_customdata['contextid'];
        $existrecord = $DB->get_record('ltool_customstyles_data', array('course' => $course));
        $existstyle = isset($existrecord->parsecustomstyles) ? $existrecord->parsecustomstyles : '';
        $mform = $this->_form;

        $mform->addElement('textarea', 'parsecustomstyles', '',
            'wrap="virtual" rows="20" cols="90"');
        $mform->setDefault('parsecustomstyles', $existstyle);

        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', $course);
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'user');
        $mform->setDefault('user', $user);
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'contextid');
        $mform->setDefault('contextid', $contextid);
        $mform->setType('contextid', PARAM_INT);
    }
}

/**
 * Learning tools customstyles template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_customstyles_tool_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_customstyles/customstylestool', $templatecontent);
}

/**
 * Load customstyles js files.
 * @return void
 */
function ltool_customstyles_load_customstylestool_js_config() {
    global $PAGE, $USER, $OUTPUT;
    $params['course'] = $PAGE->course->id;
    $params['user'] = $USER->id;
    $params['contextid'] = $PAGE->context->id;
    $coursename = local_learningtools_get_course_name($PAGE->course->id);
    $helpbutton = $OUTPUT->help_icon('parsecustomstyles', 'local_learningtools', true);
    $params['modalheader'] = get_string('coursecustomstyle', 'local_learningtools', ['name' => $coursename, 'help' => $helpbutton]);
    $PAGE->requires->js_call_amd('ltool_customstyles/customstylestool', 'init', array($params));
}

/**
 * Load the customstyles editor form
 * @param array $args page arguments
 * @return string Display the html note editor form.
 */
function ltool_customstyles_output_fragment_get_customstyles_editor($args) {

    $mform = new ltool_customstyle_stylebox(null, $args);
    $editorhtml = html_writer::start_tag('div', array('id' => 'ltoolcustomstyles-editorbox'));
    $editorhtml .= $mform->render();
    $editorhtml .= html_writer::end_tag('div');
    return $editorhtml;
}

/**
 * Save the courses styles.
 * @param array $args page arguments
 * @return bool update status
 */
function ltool_customstyles_output_fragment_save_course_customstyles($args) {
    parse_str($args['formdata'], $formdata);
    return ltool_customstyles_update_customstyles_info($formdata);
}

/**
 * Implement the update the data in db.
 *
 * @param object $formdata
 * @return bool
 */
function ltool_customstyles_update_customstyles_info($formdata) {
    global $DB, $USER;
    $context = context_course::instance($formdata['course']);
    if (has_capability('ltool/customstyles:createcustomstylestool', $context)) {
        $exitrecord = $DB->get_record('ltool_customstyles_data', array('course' => $formdata['course']));
        if (!$exitrecord) {
            $data = new stdClass;
            $data->userid = $USER->id;
            $data->course = $formdata['course'];
            $data->contextid = $formdata['contextid'];
            $data->parsecustomstyles = $formdata['parsecustomstyles'];
            $data->timecreated = time();
            $DB->insert_record('ltool_customstyles_data', $data);
        } else {
            $exitrecord->timemodified = time();
            $exitrecord->userid = $USER->id;
            $exitrecord->course = $formdata['course'];
            $exitrecord->parsecustomstyles = $formdata['parsecustomstyles'];
            $DB->update_record('ltool_customstyles_data', $exitrecord);
        }
        ltool_customstyles_create_customstylestool_course_customstylesfile($formdata['course']);
    }
    return true;
}

/**
 * Create a new file and import the course styles.
 *
 * @param int $courseid
 * @return stored_file
 */
function ltool_customstyles_create_customstylestool_course_customstylesfile($courseid) {
    global $DB;
    $fs = get_file_storage();
    $fileinfo = ltool_customstyles_get_customstylestool_fileinfo($courseid);
    $coursestyle = $DB->get_record('ltool_customstyles_data', array('course' => $courseid));
    if ($coursestyle) {
        if ($files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'])) {
            foreach ($files as $file) {
                if ($file) {
                    $file->delete();
                }
            }
        }
        $parsecustomstyles = !empty($coursestyle->parsecustomstyles) ? $coursestyle->parsecustomstyles : '';
        return $fs->create_file_from_string($fileinfo, $parsecustomstyles);
    }
}

/**
 * Describing file info for course style.
 *
 * @param int $courseid
 * @return array info
 */
function ltool_customstyles_get_customstylestool_fileinfo($courseid) {
    $fileinfo = array(
        'contextid' => context_course::instance($courseid)->id,
        'component' => 'ltool_customstyles',
        'filearea' => 'coursestyle',
        'itemid' => $courseid,
        'filepath' => '/',
        'filename' => 'coursestyle_'.time().'.css'
    );
    return $fileinfo;
}
/**
 * Implemented the course style.
 *
 * @param int $courseid
 * @return void
 */
function ltool_customstyles_load_course_customstyles($courseid) {
    global $DB, $PAGE;
    $fs = get_file_storage();
    $coursestyle = $DB->get_record('ltool_customstyles_data', array('course' => $courseid));
    if ($coursestyle) {
        $fileinfo = ltool_customstyles_get_customstylestool_fileinfo($courseid);
        $filename = $fileinfo['filename'];
        if ($files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'])) {
            foreach ($files as $file) {
                $filename = $file->get_filename();
            }
        }
        // TODO: FILE EXISTS CHECK.
        if ($fs->file_exists($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'], $filename)) {
            $url = moodle_url::make_pluginfile_url($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $filename, false);
            if (!$PAGE->requires->is_head_done()) {
                $PAGE->requires->css($url);
            }
        }
    }
}


/**
 * Serves the course styles file settings.
 *
 * @param   stdClass $course course object
 * @param   stdClass $cm course module object
 * @param   stdClass $context context object
 * @param   string $filearea file area
 * @param   array $args extra arguments
 * @param   bool $forcedownload whether or not force download
 * @param   array $options additional options affecting the file serving
 * @return  bool false|void
 */
function ltool_customstyles_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    if ($filearea !== 'coursestyle') {
        return false;
    }
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args);

    // Retrieve the file from the Files API.
    $itemid = $course->id;
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'ltool_customstyles', $filearea, $itemid, '/', $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
