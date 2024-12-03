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
 * Invite ltool lib test cases defined.
 *
 * @package   ltool_invite
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_invite;
use stdClass;

/**
 * Invite subplugin for learningtools phpunit test cases defined.
 */
class ltool_invite_test extends \advanced_testcase {

    /**
     * Summary of generator
     * @var object
     */
    public $generator;

    /**
     * Summary of student
     * @var object
     */
    public $student;

    /**
     * Summary of teacher
     * @var object
     */
    public $teacher;

    /**
     * @var object
     */
    public $course;

    /**
     * Undocumented variable
     *
     * @var object
     */
    public $coursecontext;

    /**
     * Summary of useremail
     * @var string
     */
    public $useremail;

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $DB, $PAGE, $CFG;
        require_once($CFG->dirroot.'/local/learningtools/ltool/invite/lib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->generator = $this->getDataGenerator();
        $this->student = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacher = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->course = $this->generator->create_course();
        $this->coursecontext = \context_course::instance($this->course->id);
        $this->useremail = "testuser1@gmail.com";
    }

    /**
     * Test invite tool users action.
     * @covers ::ltool_invite_users_action
     */
    public function test_ltool_invite_users_action(): void {
        global $CFG;
        $studentdata = new stdClass;
        $studentdata->email = $this->useremail;
        $studentuser = $this->generator->create_user($studentdata);
        $teacheruser = $this->generator->create_user();
        $data = new stdClass;
        $params = array();
        $params['inviteusers'] = array($studentuser->email);
        $data->user = $studentuser->id;
        $data->course = $this->course->id;
        ltool_invite_users_action($data, $params);
        $enrolstatus = is_enrolled($this->coursecontext, $studentuser);
        $this->assertTrue($enrolstatus);
    }
}
