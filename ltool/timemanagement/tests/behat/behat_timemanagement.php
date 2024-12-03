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
 * Behat Schedule Tool related steps definitions.
 *
 * @package   ltool_timemanagement
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Test cases custom function for schedule tool.
 *
 * @package   ltool_timemanagement
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_timemanagement extends behat_base {

    /**
     * Fill the value in the dates field.
     *
     * @Given /^I fill "(?P<selector>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @param string $selector
     * @param string $text
     */
    public function fillwith($selector, $text) {
        $element = $this->getSession()->getPage()->find('css', $selector);
        if ($element === null) {
            throw new ExpectationException("Element $selector not found", $this->getSession());
        } else {
            $element->setValue($text);
        }
    }

    /**
     * Check the value in the dates field.
     *
     * @Given /^I see "(?P<date>(?:[^"]|\\")*)" date in the "(?P<element_string>(?:[^"]|\\")*)"$/
     * @param string $date
     * @param string $selector
     */
    public function ishouldseevalueelement($date, $selector) {
        $text = behat_context_helper::escape($date);
        $text = str_replace("'", "", $text);
        $this->execute("behat_general::assert_element_contains_text", [$text, $selector, "css_element"]);
    }

    /**
     * Sets the specified value to the field.
     *
     * @Given /^I set the field section in the "(?P<con_str>[^"]*)" "(?P<str>[^"]*)" to "(?P<string>[^"]*)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @param string $containerelement Element we look in
     * @param string $containerselectortype The type of selector where we look in
     * @param string $value
     */
    public function i_set_the_field_in_container_to($containerelement, $containerselectortype, $value) {
        global $CFG;
        if ($CFG->branch > 403) {
            $this->execute("behat_forms::set_field_value_in_container", ["Edit section name",
                $value, $containerselectortype, $containerelement]);
        } else {
            $this->execute("behat_forms::set_field_value_in_container", ["Edit topic name",
                $value, $containerselectortype, $containerelement]);
        }

    }
}
