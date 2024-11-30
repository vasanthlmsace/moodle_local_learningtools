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
 * Define plugin services.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'ltool_timemanagement_update_managedates' => array(
        'classname'   => 'ltool_timemanagement\external',
        'methodname'  => 'update_managedates',
        'description' => 'Update the dates',
        'type'        => 'write',
        'capabilities' => 'ltool/timemanagement:managedates',
        'ajax'          => true,
        'loginrequired' => true,
    ),
    'ltool_timemanagement_change_viewdates' => array(
        'classname'   => 'ltool_timemanagement\external',
        'methodname'  => 'change_viewdates',
        'description' => 'Change to the view user dates',
        'type'        => 'write',
        'capabilities' => 'ltool/timemanagement:viewothersdates',
        'ajax'          => true,
        'loginrequired' => true,
    ),
);
