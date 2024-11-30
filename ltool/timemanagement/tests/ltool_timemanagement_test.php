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
 * timemanagement ltool lib test cases defined.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_timemanagement;

use stdClass;

/**
 * timemanagement subplugin for learningtools phpunit test cases defined.
 */
class ltool_timemanagement_test extends \advanced_testcase {

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $DB, $CFG;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');
        $this->generator = $this->getDataGenerator();
        $this->create_course();
        $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->user1 = $this->generator->create_user();
        $this->user2 = $this->generator->create_user();
        $this->generator->enrol_user($this->user1->id, $this->course->id, $this->studentrole->id);
    }

    /**
     * Test create_course
     */
    public function create_course() {
        $this->course = $this->generator->create_course(array('enablecompletion' => 1));
        $this->coursecontext = \context_course::instance($this->course->id);
        $this->mod1 = $this->generator->create_module('page', [
            'course' => $this->course->id,
            'title' => 'Test page 1',
            'content' => 'Test page content 1'
        ], ['completion' => 1]);
        $this->mod2 = $this->generator->create_module('page', [
            'course' => $this->course->id,
            'title' => 'Test page 2',
            'content' => 'Test page content 2'
        ], ['completion' => 1]);
    }

    /**
     * Test ltool_timemanagement_get_course_user_enrollment.
     * @covers ::
     */
    public function test_ltool_timemanagement_get_course_user_enrollment() {
        $userinfo1 = ltool_timemanagement_get_course_user_enrollment($this->course->id, $this->user1->id);
        $userinfo2 = ltool_timemanagement_get_course_user_enrollment($this->course->id, $this->user2->id);
        $this->assertEquals(1, count($userinfo1));
        $this->assertEquals(0, count($userinfo2));
    }

    /**
     * Test ltool_timemanagement_cal_course_progress.
     * @covers ::
     */
    public function test_ltool_timemanagement_cal_course_progress() {
        $this->complete_module($this->user1->id, $this->course, $this->mod1->cmid, 'page');
        $this->complete_module($this->user1->id, $this->course, $this->mod2->cmid, 'page');
        $progress1  = ltool_timemanagement_cal_course_progress($this->course->id, $this->user1->id);
        $this->assertEquals("100% (2/2 activities)", $progress1);
        $this->complete_module($this->user2->id, $this->course, $this->mod1->cmid, 'page');
        $progress2  = ltool_timemanagement_cal_course_progress($this->course->id, $this->user2->id);
        $this->assertEquals("50% (1/2 activities)", $progress2);
    }

    /**
     * Completed the module.
     * @param int $userid
     * @param object $course
     * @param int $cmid
     * @param string $modulename
     */
    public function complete_module($userid, $course,  $cmid, $modulename) {
        $criteriadata = (object) [
            'id' => $course->id,
            'criteria_activity' => [$cmid => 1],
        ];
        $criterion = new \completion_criteria_activity();
        $criterion->update_config($criteriadata);
        $cminfo = get_coursemodule_from_id($modulename, $cmid);
        $completion = new \completion_info($course);
        $completion->update_state($cminfo, COMPLETION_COMPLETE, $userid);
    }

    /**
     * Test ltool_timemanagement_cal_course_duedate.
     * @covers ::
     */
    public function test_ltool_timemanagement_cal_course_duedate() {
        $usertimestart = strtotime("today midnight");
        $record = new stdClass();
        $record->duedatetype = 'custom';
        $record->duedatecustom = "2022/01/30";
        $duedate1 = ltool_timemanagement_cal_course_duedate($record, $usertimestart);
        $this->assertEquals(strtotime("2022/01/30"), $duedate1);
        $record->duedatetype = "after";
        $record->duedatedigits = 5;
        $record->duedateduration = "days";
        $duedate2 = ltool_timemanagement_cal_course_duedate($record, $usertimestart);
        $this->assertEquals(strtotime("+5days", $usertimestart), $duedate2);
    }

    /**
     * Test ltool_timemanagement_cal_coursemodule_managedates.
     * @covers ::
     */
    public function test_ltool_timemanagement_cal_coursemodule_managedates() {
        $usertimestart = strtotime("today midnight");
        $record = new stdClass();
        $record->startdatetype = "upon";
        list('startdate' => $startdate, 'duedate' => $duedate) = ltool_timemanagement_cal_coursemodule_managedates(
                $record, $usertimestart);
        $this->assertEquals($usertimestart, $startdate);
        $this->assertEquals(0, $duedate);
        $record->startdatetype = "after";
        $record->startdatedigits = 5;
        $record->startdateduration = "days";
        $record->duedatetype = "after";
        $record->duedatedigits = 1;
        $record->duedateduration = "months";
        list('startdate' => $startdate, 'duedate' => $duedate) = ltool_timemanagement_cal_coursemodule_managedates(
                $record, $usertimestart);
        $this->assertEquals(strtotime("+5days", $usertimestart), $startdate);
        $this->assertEquals(strtotime("+1months", $usertimestart), $duedate);
    }

    /**
     * Test ltool_timemanagement_get_enrolled_course_users.
     * @covers ::
     */
    public function test_ltool_timemanagement_get_enrolled_course_users() {
        $user = $this->generator->create_user();
        $this->generator->enrol_user($user->id, $this->course->id, $this->teacherrole->id);
        $res1 = ltool_timemanagement_get_enrolled_course_users($this->course->id);
        $this->assertEquals(1, count($res1));
        $res2 = ltool_timemanagement_get_enrolled_course_users($this->course->id);
        $this->assertEquals(1, count($res2));
        $user = $this->generator->create_user();
        $this->generator->enrol_user($user->id, $this->course->id, $this->studentrole->id);
        $res3 = ltool_timemanagement_get_enrolled_course_users($this->course->id);
        $this->assertEquals(2, count($res3));
    }

    /**
     * Test ltool_timemanagement_get_mod_user_info.
     * @covers ::ltool_timemanagement_get_mod_user_info
     */
    public function test_ltool_timemanagement_get_mod_user_info() {
        global $DB;
        $timecompleted = 1620000000;
        $this->complete_module($this->user1->id, $this->course, $this->mod1->cmid, 'page');
        $params = ['coursemoduleid' => $this->mod1->cmid, 'userid' => $this->user1->id];
        $DB->set_field('course_modules_completion', 'timemodified', $timecompleted, $params);
        $modinfo = get_fast_modinfo($this->course);
        $mod = $modinfo->get_cm($this->mod1->cmid);
        $data = ltool_timemanagement_get_mod_user_info($mod, $this->user1->id);
        $this->assertEquals($timecompleted, $data['completeiondate']);
    }

    /**
     * Test ltool_timemanagement_get_module_completion_info.
     * @covers ::ltool_timemanagement_get_module_completion_info
     */
    public function test_ltool_timemanagement_get_module_completion_info() {
        $modinfo = get_fast_modinfo($this->course);
        $mod = $modinfo->get_cm($this->mod1->cmid);
        $this->complete_module($this->user1->id, $this->course, $this->mod1->cmid, 'page');
        $completiondata1 = ltool_timemanagement_get_module_completion_info($mod, $this->user1->id);
        $this->assertEquals(1, $completiondata1->completionstate);
        $completiondata2 = ltool_timemanagement_get_module_completion_info($mod, $this->user2->id);
        $this->assertEquals(0, $completiondata2->completionstate);
    }

    /**
     * Create the module dates.
     * @param object $data
     * @param int $cmid
     * @return void
     */
    public function create_module_managedates($data, $cmid) {
        global $DB;
        $record = $DB->get_record('ltool_timemanagement_modules', array('cmid' => $cmid));
        if (!empty($record)) {
            $data->id = $record->id;
            $data->timemodified = time();
            $DB->update_record('ltool_timemanagement_modules', $data);
        } else {
            $data->timecreated = time();
            $DB->insert_record('ltool_timemanagement_modules', $data);
        }
    }
}
