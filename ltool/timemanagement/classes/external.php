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
 * External functions definition and returns.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_timemanagement;

use context_course;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/externallib.php');

/**
 * define external class.
 */
class external extends \external_api {
    /**
     * Parameters defintion to Activities date convetor.
     *
     * @return array list of option parameters
     */
    public static function update_managedates_parameters() {

        return new \external_function_parameters(
            array(
                'data' => new \external_single_structure(
                    array(
                        'courseid' => new \external_value(PARAM_INT, 'The context id for the course'),
                        'pagedate' => new \external_value(PARAM_TEXT, 'The data from the user bookmarks'),
                        'type' => new \external_value(PARAM_TEXT, 'The date type'),
                        'time' => new  \external_value(PARAM_TEXT, 'time', VALUE_OPTIONAL),
                    )
                )
            )
        );
    }

    /**
     * Activities date convetor based on the type.
     * @param array $data user data
     * @return string the formatted date/time.
     */
    public static function update_managedates($data) {
        require_login();
        $params = self::validate_parameters(self::update_managedates_parameters(),
            array('data' => $data));
        $data = $params['data'];
        $context = context_course::instance($data['courseid']);
        require_capability("ltool/timemanagement:managedates", $context);
        $date = '';
        if ($data['type'] == 'after') {
            $date = strtotime("+" . $data['time'], strtotime($data['pagedate']));
        } else if ($data['type'] == 'custom') {
            $date = strtotime($data['time']);
        }
        return !empty($date) ? userdate($date, get_string('strftimeyearmonth', 'local_learningtools'), '', false) : '';
    }

    /**
     * Return parameters define for Activities date convetor.
     */
    public static function update_managedates_returns() {

        return  new \external_value(PARAM_TEXT, 'calculate date');
    }

    /**
     * Parameters defintion to user manage dates.
     *
     * @return array list of option parameters
     */
    public static function change_viewdates_parameters() {
        return new \external_function_parameters(
            array(
                'data' => new \external_single_structure(
                    array(
                        'course' => new \external_value(PARAM_INT, 'Course id'),
                        'contextid' => new \external_value(PARAM_INT, 'The context id for the course'),
                        'modalheader' => new \external_value(PARAM_TEXT, 'Header string'),
                        'user' => new  \external_value(PARAM_INT, 'User id'),
                        'relateduser' => new \external_value(PARAM_INT, 'Related userid', VALUE_OPTIONAL),
                        'themeurl' => new \external_value(PARAM_URL, "theme style url", VALUE_OPTIONAL)
                    )
                ),
                'type' => new \external_value(PARAM_TEXT, 'Type of form'),
            )
        );
    }

    /**
     * Get the user manage dates.
     * @param array $data info.
     * @param string $type content area.
     */
    public static function change_viewdates($data, $type) {
        global $CFG, $PAGE;
        require_login();
        require_once($CFG->dirroot."/local/learningtools/ltool/timemanagement/lib.php");
        $params = self::validate_parameters(self::change_viewdates_parameters(),
            array('data' => $data, 'type' => $type));
        $data = $params['data'];
        $context = context_course::instance($data['course']);
        $PAGE->set_context($context);
        require_capability("ltool/timemanagement:viewothersdates", $context);
        if ($params['type'] == 'header') {
            return ltool_timemanagement_header_form($data);
        } else if ($params['type'] == 'body') {
            return ltool_timemanagement_body_form($data, $data['relateduser']);
        }
    }

    /**
     * Return parameters define for user manage dates.
     */
    public static function change_viewdates_returns() {
        return  new \external_value(PARAM_RAW, 'html content');
    }
}

