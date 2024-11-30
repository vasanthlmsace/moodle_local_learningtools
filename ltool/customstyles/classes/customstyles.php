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
 * The class defines the Css ltool.
 *
 * @package   ltool_customstyles
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_customstyles;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the customstyles ltool
 */
class customstyles extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'customstyles';

    /**
     * Tool context level
     * @var string
     */
    public $contextlevel = 'course';

    /**
     * customstyles name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('customstyles', 'local_learningtools');
    }

    /**
     * customstyles icon
     */
    public function get_tool_icon() {

        return 'fa fa-paint-brush';
    }

    /**
     * customstyles icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Get the customstyles tool  content.
     *
     * @return string display tool customstyles plugin html.
     */
    public function get_tool_records() {
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['ltoolcustomstyles'] = true;
        $data['customstylestoolhovername'] = get_string('customstyles', 'local_learningtools');
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        return $data;
    }

    /**
     * Return the template of customstyles fab button.
     *
     * @return string customstyles tool fab button html.
     */
    public function render_template() {
        global $PAGE, $SITE;
        if (local_learningtools_can_visible_tool_incourse()) {
            $coursecontext = \context_course::instance($PAGE->course->id);
            if (has_capability("ltool/customstyles:createcustomstylestool", $coursecontext)) {
                $data = $this->get_tool_records();
                return ltool_customstyles_tool_render_template($data);
            }
        }
    }

    /**
     * Load the required javascript files for note.
     *
     * @return void
     */
    public function load_js() {
        // Load note tool js configuration.
        ltool_customstyles_load_customstylestool_js_config();
    }

    /**
     * Defined required load the tool function.
     *
     * @return void
     */
    public function required_load_data() {
        global $PAGE, $SITE;
        if (!empty($PAGE->course->id) && $PAGE->course->id != $SITE->id) {
            ltool_customstyles_load_course_customstyles($PAGE->course->id);
        }
    }

    /**
     * Customstyle active tool status.
     * @return string customstyles tool fab button html.
     */
    public function tool_active_condition() {
        global $DB, $PAGE, $SITE;
        if (!empty($PAGE->course->id) && $PAGE->course->id != $SITE->id) {
            $record = $DB->get_record('ltool_customstyles_data', array('course' => $PAGE->course->id));
            if (!empty($record->parsecustomstyles)) {
                return $this->render_template();
            }
        }
    }
}

