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
 * tool plugin "Learning Tools Custom styles" - settings file.
 * @package   ltool_customstyles
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Define icon background color.
    $name = "ltool_customstyles/customstylesiconbackcolor";
    $title = get_string('iconbackcolor', 'local_learningtools', "customstyles");
    $customstylesinfo = new \ltool_customstyles\customstyles();
    $default = $customstylesinfo->get_tool_iconbackcolor();
    $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
    $page->add($setting);

    // Define icon color.
    $name = "ltool_customstyles/customstylesiconcolor";
    $title = get_string('iconcolor', 'local_learningtools', "customstyles");
    $default = '#fff';
    $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
    $page->add($setting);

    // Define Sticky.
    $name = "ltool_customstyles/sticky";
    $title = get_string('sticky', 'local_learningtools');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, '', $default);
    $page->add($setting);
}
