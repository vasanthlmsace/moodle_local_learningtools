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
 * @package   ltool_forceactivity
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_forceactivity;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__).'/lib.php');

/**
 * Event observer class define.
 */
class eventobservers {

    /**
     * Observer that monitors course module deleted event and delete user subscriptions.
     *
     * @param \core\event\course_module_deleted $event the event object.
     */
    public function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;
        $cmid = $event->contextinstanceid;
        $DB->delete_records('ltool_forceactivity_data', array('cmid' => $cmid));
    }
}
