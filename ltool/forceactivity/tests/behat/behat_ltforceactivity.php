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
 * Behat Invite Tool related steps definitions.
 *
 * @package   ltool_forceactivity
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Test cases custom function for invite tool.
 *
 * @package   ltool_forceactivity
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_ltforceactivity extends behat_base {

    /**
     * Check that the invite event.
     *
     * @Given /^I click on enroll users page$/
     *
     */
    public function i_click_on_enroll_users_page(): void {
        global $CFG;

        if (round($CFG->version) < 2022031100) {
            // Moodle-3.11 and below.
            $this->execute("behat_navigation::i_navigate_to_in_current_page_administration", "Users > Enrolled users");
        } else {
            // Moodle-4.0.
            $this->execute("behat_navigation::i_am_on_page_instance", ["Course 1", "Enrolled users"]);
        }
    }
}
