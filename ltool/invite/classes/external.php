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
 * @package   ltool_invite
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_invite;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/externallib.php');
/**
 * Define external class.
 */
class external extends \external_api {

    /**
     * Parameters defintion to invite users.
     *
     * @return array list of option parameters
     */
    public static function teachersinvite_users_parameters() {

        return new \external_function_parameters(
            array(
                'params' => new \external_value(PARAM_RAW, 'The parameters of info'),
                'formdata' => new \external_value(PARAM_RAW, 'The data from the invite users email')
            )
        );
    }

    /**
     * Invite users actions
     * @param mixed $params context id
     * @param mixed $formdata user data
     * @return bool status
     */
    public static function teachersinvite_users($params, $formdata) {
        global $CFG;
        require_once($CFG->dirroot."/local/learningtools/ltool/invite/lib.php");
        require_login();
        $validparams = self::validate_parameters(self::teachersinvite_users_parameters(),
            array('params' => $params, 'formdata' => $formdata));
        // Parse serialize form data.
        parse_str($validparams['formdata'], $data);
        $params = json_decode($validparams['params']);
        $context = \context_course::instance($params->course);
        require_capability("ltool/invite:createinvite", $context);
        return ltool_invite_users_action($params, $data);
    }

    /**
     * Return parameters define for Invite users status.
     */
    public static function teachersinvite_users_returns() {
        return new \external_value(PARAM_BOOL, 'Invite users status');
    }
}
