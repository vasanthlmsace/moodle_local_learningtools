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
 * The class defines the Bookmarks ltool.
 *
 * @package   ltool_invite
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_invite;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the Bookmarks ltool
 */
class invite extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'invite';

    /**
     * Tool context level
     * @var string
     */
    public $contextlevel = 'course';

    /**
     * invite name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('invite', 'local_learningtools');
    }

    /**
     * invite icon
     */
    public function get_tool_icon() {

        return 'fa fa-user-plus';
    }

    /**
     * invite icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Load the required javascript files for invite.
     *
     * @return void
     */
    public function load_js() {
        // Load invite tool js configuration.
        ltool_invite_load_js_config();
    }
    /**
     * Get the invite tool  content.
     *
     * @return string display tool invite plugin html.
     */
    public function get_tool_records() {
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['ltoolinvite'] = true;
        $data['invitehovername'] = get_string('invite', 'local_learningtools');
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        return $data;
    }

    /**
     * Return the template of invite fab button.
     *
     * @return string invite tool fab button html.
     */
    public function render_template() {
        global $PAGE, $SITE, $DB;
        if (local_learningtools_can_visible_tool_incourse()) {
            $coursecontext = \context_course::instance($PAGE->course->id);
            if (has_capability("ltool/invite:createinvite", $coursecontext)) {
                $data = $this->get_tool_records();
                return ltool_invite_render_template($data);
            }
        }
        return '';
    }

}
