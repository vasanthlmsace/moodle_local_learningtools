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
 * Behat customstyles Tool related steps definitions.
 *
 * @package   ltool_customstyles
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Test cases custom function for customstyles tool.
 *
 * @package   ltool_customstyles
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_customstyles extends behat_base {

    /**
     * Check that the focus mode enable.
     *
     * @Given /^I check header color "(?P<color>(?:[^"]|\\")*)"$/
     * @param string $color
     * @throws ExpectationException
     */
    public function i_check_header_color($color): void {
        $coursestylejs = '
            return (
                Y.one("#page-header").getComputedStyle("background-color")
            )
        ';
        if (!$this->evaluate_script($coursestylejs) == $color) {
            throw new ExpectationException("Doesn't working course style", $this->getSession());
        }
    }

    /**
     * Set the navbar hide
     * @Given /^I set the navbar hide$/
     * @throws ExpectationException
     */
    public function i_set_navbar_hide(): void {
        $hidenavbar = '
            return (
                Y.one("nav.navbar").setAttribute("style", "display: none;")
            )
        ';
        $this->evaluate_script($hidenavbar);
    }
}
