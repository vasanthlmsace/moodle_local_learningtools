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
 * @package   ltool_invite
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Test cases custom function for invite tool.
 *
 * @package   ltool_invite
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_ltinvite extends behat_base {

    /**
     * Check that the invite event.
     *
     * @Given /^I add learning tools page activity to course :coursefullname section :sectionnum$/
     * @param string $coursefullname
     * @param string $sectionnum
     *
     */
    public function i_add_learning_tools_page_activity_to_course(string $coursefullname, string $sectionnum): void {
        global $CFG;

        if ($CFG->branch >= 401) {
            // Moodle-401 and above.
            $this->execute("behat_forms::i_add_to_course_section", ["Page", $coursefullname, $sectionnum]);
        } else {
            // Moodle-400.
            $this->execute("behat_course::i_add_to_section", ["Page", $sectionnum]);
        }
        $this->execute("behat_forms::i_expand_all_fieldsets");
        $this->execute("behat_forms::i_set_the_field_to", ["Name", "Page 1"]);
        $this->execute("behat_forms::i_set_the_field_to", ["Description", "Test"]);
        $this->execute("behat_forms::i_set_the_field_to", ["Page content", "Test"]);
        if ($CFG->branch <= 403) {
            $this->execute("behat_forms::i_set_the_field_to", ["Completion tracking", "2"]);
        } else {
            $this->execute("behat_forms::i_set_the_field_to", ["Page content", "Test"]);
            $this->execute("behat_forms::i_set_the_field_to", ["Page content", "Test"]);
        }
    }
}
