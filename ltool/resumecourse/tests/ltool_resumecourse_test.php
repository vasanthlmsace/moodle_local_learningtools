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
 * Course resume ltool lib test cases defined.
 *
 * @package   ltool_resumecourse
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_resumecourse;
use stdClass;

/**
 * Resume course subplugin for learningtools phpunit test cases defined.
 */
class ltool_resumecourse_test extends \advanced_testcase {


    /**
     * @var object
     */
    public $generator;

    /**
     * @var object
     */
    public $user;

    /**
     * Summary of cm
     * @var object
     */
    public $cm;

    /**
     * Summary of course
     * @var object
     */
    public $course;

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $CFG, $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->generator = $this->getDataGenerator();
        $this->course = $this->generator->create_course();
        $options = array('course' => $this->course->id);
        $quiz = $this->getDataGenerator()->create_module('quiz', $options);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
        $this->cm = $cm;
        $quizcontext = \context_module::instance($cm->id);
        $PAGE = new \moodle_page();
        $PAGE->set_course($this->course);
        $PAGE->set_context($quizcontext);
        $PAGE->set_cm($cm);
        $PAGE->set_title('Course 1: Quiz test 1');
        $PAGE->set_url(new \moodle_url('/mod/quiz/view.php', ['id' => $cm->id]));
        $this->user = $this->generator->create_user();
    }

    /**
     * Test store_user_access_data.
     * @covers ::ltool_resumecourse_store_user_access_data
     */
    public function test_ltool_resumecourse_store_user_access_data() {
        global $DB, $PAGE;
        $this->setUser($this->user);
        ltool_resumecourse_store_user_access_data();
        $userrecord = $DB->count_records('ltool_resumecourse_data', array('userid' => $this->user->id));
        $this->assertEquals(1, $userrecord);
        $userrecord = new stdClass();
        $userrecord->userid = $this->user->id;
        $userrecord->courseid = $this->course->id;
        $useraccessurl = ltool_resumecourse_lastaccess_activity_action($userrecord);
        $this->assertEquals($PAGE->url->out(false), $useraccessurl['url']);
    }
}
