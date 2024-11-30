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
 * customstyles ltool lib test cases defined.
 *
 * @package   ltool_customstyles
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_customstyles;

/**
 * customstyles subplugin for learningtools phpunit test cases defined.
 */
class ltool_customstyles_test extends \advanced_testcase {

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot."/local/learningtools/ltool/customstyles/lib.php");
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->coursecontext = \context_course::instance($this->course->id);
        $this->getDataGenerator()->role_assign($this->teacherrole->id, $this->user->id,
        $this->coursecontext->id);
        $this->parsecustomstyles = "#page-header { background:green;}";
    }

    /**
     * Case to test the external method to update course styles.
     * @covers ::ltool_customstyles_update_customstyles_info
     * @return void
     */
    public function test_ltool_customstyles_update_customstyles_info() {
        global $DB;
        $formdata = $this->get_customstylesdata_info();
        ltool_customstyles_update_customstyles_info($formdata);
        $datacount = $DB->count_records('ltool_customstyles_data', array('course' => $this->course->id));
        $this->assertEquals(1, $datacount);
    }

    /**
     * Case to test the external method to import styles into file.
     * @covers ::ltool_customstyles_create_customstylestool_course_customstylesfile
     * @return void
     */
    public function test_ltool_customstyles_create_customstylestool_course_customstylesfile() {
        $formdata = $this->get_customstylesdata_info();
        ltool_customstyles_update_customstyles_info($formdata);
        $file = ltool_customstyles_create_customstylestool_course_customstylesfile($this->course->id);
        $filecontent = $file->get_content();
        $this->assertEquals($this->parsecustomstyles, $filecontent);
    }

    /**
     * Get file info.
     *
     * @return array info
     */
    public function get_customstylesdata_info() {
        $formdata = [
            'user' => $this->user->id,
            'course' => $this->course->id,
            'contextid' => $this->coursecontext->id,
            'parsecustomstyles' => $this->parsecustomstyles,
        ];
        return $formdata;
    }
}
