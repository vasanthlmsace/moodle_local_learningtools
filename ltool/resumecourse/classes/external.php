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
 * @package   ltool_resumecourse
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_resumecourse;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/externallib.php');
/**
 * Define external class.
 */
class external extends \external_api {

    /**
     * Parameters defintion to last access the module url.
     *
     * @return array list of option parameters
     */
    public static function lastaccess_activity_parameters() {

        return new \external_function_parameters(
            array(
                'params' => new \external_value(PARAM_RAW, 'The parameters of info')
            )
        );
    }

    /**
     * Get the last access the module url
     * @param mixed $params
     * @return strign module url
     */
    public static function lastaccess_activity($params) {
        global $CFG;
        require_once($CFG->dirroot."/local/learningtools/ltool/resumecourse/lib.php");
        require_login();
        $validparams = self::validate_parameters(self::lastaccess_activity_parameters(),
            array('params' => $params));
        $params = json_decode($validparams['params']);
        $context = \context_system::instance();
        require_capability("ltool/resumecourse:createresumecourse", $context);
        return ltool_resumecourse_lastaccess_activity_action($params);
    }

    /**
     * Return parameters define for last acces the module url.
     */
    public static function lastaccess_activity_returns() {
        return new \external_single_structure(
            array(
            'url' => new \external_value(PARAM_URL, 'Return last acces module url' ),
            'message' => new \external_value(PARAM_TEXT, 'Return status message'),
            )
        );
    }
}

