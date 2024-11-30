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
 * The class defines the Time management ltool.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_timemanagement;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the timemanagement ltool
 */
class timemanagement extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'timemanagement';

    /**
     * Tool context level
     * @var string
     */
    public $contextlevel = 'course';

    /**
     * timemanagement name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('timemanagement', 'local_learningtools');
    }

    /**
     * timemanagement icon
     */
    public function get_tool_icon() {

        return 'fa fa-calendar-check-o';
    }

    /**
     * timemanagement icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Get the timemanagement tool  content.
     *
     * @return string display tool timemanagement plugin html.
     */
    public function get_tool_records() {
        global $PAGE;
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['ltooltimemanagement'] = true;
        $data['timemanagementhovername'] = get_string('timemanagement', 'local_learningtools');
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        $data['duecounts'] = ltool_timemanagement_get_user_dueactivities($PAGE->course->id);
        return $data;
    }

    /**
     * Return the template of timemanagement fab button.
     *
     * @return string timemanagement tool fab button html.
     */
    public function render_template() {
        global $PAGE;
        if (local_learningtools_can_visible_tool_incourse()) {
            $coursecontext = \context_course::instance($PAGE->course->id);
            if (has_capability("ltool/timemanagement:createtimemanagement", $coursecontext)) {
                $course = get_course($PAGE->course->id);
                $data = $this->get_tool_records();
                if (!has_capability("ltool/timemanagement:viewothersdates", $coursecontext)
                    && !has_capability("ltool/timemanagement:managedates", $coursecontext)) {
                    $data['fulltitle'] = true;
                } else {
                    $data['fulltitle'] = false;
                }
                if ($course->enablecompletion) {
                    return ltool_timemanagement_render_template($data);
                } else {
                    if (has_capability("ltool/timemanagement:managedates", $coursecontext)) {
                        return ltool_timemanagement_render_template($data);
                    }
                }
            }
        }
    }

    /**
     * Load the required javascript files for timemanagement.
     *
     * @return void
     */
    public function load_js() {
        // Load timemanagement tool js configuration.
        ltool_timemanagement_load_js_config();
    }

    /**
     * Time management active tool status.
     * @return string Time management tool fab button html.
     */
    public function tool_active_condition() {
        global $PAGE, $SITE;
        if (!empty($PAGE->course->id) && $PAGE->course->id != $SITE->id) {
            if (ltool_timemanagement_get_user_dueactivities($PAGE->course->id)) {
                return $this->render_template();
            }
        }
    }
}

