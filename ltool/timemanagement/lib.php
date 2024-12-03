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
 * ltool plugin "Learning Tools Time management" - library file.
 *
 * @package   ltool_timemanagement
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once($CFG->libdir.'/formslib.php');
use core_course\output\activity_information;

/**
 * Learning tools timemanagement template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_timemanagement_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_timemanagement/timemanagement', $templatecontent);
}

/**
 * Load time management js files.
 * @return void
 */
function ltool_timemanagement_load_js_config() {
    global $PAGE, $USER, $CFG;
    $params['contextid'] = $PAGE->context->id;
    $params['user'] = $USER->id;
    $params['course'] = $PAGE->course->id;
    $coursename = local_learningtools_get_course_name($PAGE->course->id);
    $params['modalheader'] = get_string('timemanagementheader', 'local_learningtools', ['name' => $coursename]);
    $themeconfig = theme_config::load($CFG->theme);
    $themeurls = $themeconfig->css_urls($PAGE);
    $params['themeurl'] = $themeurls[0]->out(false);
    $PAGE->requires->js_call_amd('ltool_timemanagement/timemanagement', 'init', array($params));
}

/**
 * Display header modal content.
 * @param array $args info.
 * @return string header html.
 */
function ltool_timemanagement_output_fragment_get_timemanagement_headerform($args) {
    return ltool_timemanagement_header_form($args);
}

/**
 * Get header modal content.
 * @param array $args info.
 * @return string header html.
 */
function ltool_timemanagement_header_form($args) {
    global $OUTPUT;
    $formdata = $args;
    $course = get_course($args['course']);
    $relateduserid = isset($args['relateduser']) ? $args['relateduser'] : 0;
    $enrollusers = ltool_timemanagement_get_enrolled_course_users($args['course'], $relateduserid);
    if ($course->enablecompletion && !empty($enrollusers)) {
        $coursecontext = context_course::instance($args['course']);
        $formdata['viewothersdates'] = has_capability("ltool/timemanagement:viewothersdates", $coursecontext);
        $formdata['managedates'] = has_capability("ltool/timemanagement:managedates", $coursecontext);
        if (!has_capability("ltool/timemanagement:viewothersdates", $coursecontext)
            && !has_capability("ltool/timemanagement:managedates", $coursecontext)) {
            $formdata['fulltitle'] = true;
        } else {
            $formdata['fulltitle'] = false;
        }
        $formdata['managementheader'] = true;
        $formdata['managecourseurl'] = new moodle_url('/local/learningtools/ltool/timemanagement/managecoursedates.php');
        $userid = isset($enrollusers[0]) ? $enrollusers[0]['id'] : 0;
        $userid = !empty($relateduserid) ? $relateduserid : $userid;
        $formdata['user']  = $userid;
        $formdata['profileurl'] = new moodle_url('/user/profile.php', array('id' => $userid));
        $formdata['messageurl'] = new moodle_url('/message/index.php', array('id' => $userid));
        $formdata['enrollusers'] = $enrollusers;
        $formdata['enrolluserstatus'] = !empty($enrollusers) ? true : false;
        $formdata['print'] = true;
        $formdata['sesskey'] = sesskey();
        $formdata['printurl'] = new moodle_url("/local/learningtools/ltool/timemanagement/pdf.php");
        return $OUTPUT->render_from_template('ltool_timemanagement/headermodal', $formdata);
    } else {
        $formdata['managementheader'] = true;
        return $OUTPUT->render_from_template('ltool_timemanagement/headermodal', $formdata);
    }
}

/**
 * Display modal body content.
 * @param array $args info.
 * @return string body html.
 */
function ltool_timemanagement_output_fragment_get_timemanagement_bodyform($args) {
    return ltool_timemanagement_body_form($args);
}

/**
 * Get modal body content.
 * @param array $args info.
 * @param int $userid
 * @return string body html.
 */
function ltool_timemanagement_body_form($args, $userid = 0) {
    global $OUTPUT;
    $formdata = $args;
    $course = get_course($args['course']);
    $enrollusers = ltool_timemanagement_get_enrolled_course_users($args['course']);
    if ($course->enablecompletion && !empty($enrollusers)) {
        $enrollusers = ltool_timemanagement_get_enrolled_course_users($args['course']);
        $coursecontext = context_course::instance($args['course']);
        if (!empty($enrollusers)) {
            $relateduserid = $args['user'];
            if (has_capability("ltool/timemanagement:viewothersdates", $coursecontext)) {
                // Get enrol first user.
                $relateduserid = $enrollusers[0]['id'];
            }
            if (isset($args['relateduser'])) {
                $relateduserid = $args['relateduser'];
            }
            if ($userid) {
                $relateduserid = $userid;
            }
            $formdata['courseinfo'] = ltool_timemanagement_get_course_userinfo($args['course'], $relateduserid);
            $moddata = ltool_timemanagement_get_course_section_mod_data($course, false, $relateduserid);
            $formdata = array_merge($formdata, $moddata);
        }
        return $OUTPUT->render_from_template('ltool_timemanagement/viewmanagement', $formdata);
    } else {
        if (!$course->enablecompletion) {
            $template['enablecompletion'] = true;
        } else if (empty($enrollusers)) {
            $template['enrollusers'] = true;
        }
        return $OUTPUT->render_from_template('ltool_timemanagement/modalinfo', $template);
    }
}

/**
 * Get user current enrollment filter in course.
 * @param int $courseid course id.
 * @param int $userid user id.
 * @return array
 */
function ltool_timemanagement_get_course_user_enrollment($courseid, $userid) {
    global $DB, $CFG, $PAGE;
    require_once($CFG->dirroot. "/enrol/locallib.php");
    $course = $DB->get_record('course', array('id' => $courseid));
    $manager = new course_enrolment_manager($PAGE, $course);
    $userenrolments = $manager->get_user_enrolments($userid);
    $usercourseenrollinfo = [];
    if (!empty($userenrolments)) {
        if (count($userenrolments) == 1) {
            foreach ($userenrolments as $ue) {
                $enrolinfo = [];
                $enrolinfo['timestart'] = $ue->timestart;
                $enrolinfo['timeend'] = $ue->timeend;
                $enrolinfo['timeenrolled'] = $ue->timecreated;
                $enrolinfo['instancename'] = $ue->enrolmentinstancename;
                $usercourseenrollinfo[] = $enrolinfo;
            }
        } else {
            // Check current first one.
            foreach ($userenrolments as $ue) {
                if ($ue->timestart < time()) {
                    if (isset($ue->timeend)) {
                        if ($ue->timeend < time()) {
                            continue;
                        }
                    }
                    $enrolinfo['timestart'] = $ue->timestart;
                    $enrolinfo['timeend'] = $ue->timeend;
                    $enrolinfo['timeenrolled'] = $ue->timecreated;
                    $enrolinfo['instancename'] = $ue->enrolmentinstancename;
                    $usercourseenrollinfo[] = $enrolinfo;
                    break;
                }
            }
        }
        if (empty($usercourseenrollinfo)) {
            foreach ($userenrolments as $ue) {
                $enrolinfo = [];
                $enrolinfo['timestart'] = $ue->timestart;
                $enrolinfo['timeend'] = $ue->timeend;
                $enrolinfo['timeenrolled'] = $ue->timecreated;
                $enrolinfo['instancename'] = $ue->enrolmentinstancename;
                $usercourseenrollinfo[] = $enrolinfo;
                break;
            }
        }
    }
    return $usercourseenrollinfo;
}

/**
 * Get the course managedates info for user.
 * @param int $courseid
 * @param int $userid
 * @return array course dates info.
 */
function ltool_timemanagement_get_course_userinfo($courseid, $userid) {
    global $DB;
    $data = [];
    $usercourseenrollinfo = ltool_timemanagement_get_course_user_enrollment($courseid, $userid);
    $data['userenrolinfo'] = $usercourseenrollinfo;
    $data['showenrolinstance'] = (count($usercourseenrollinfo) > 1) ? true : false;
    $coursedatesinfo = $DB->get_record('ltool_timemanagement_course', array('course' => $courseid));
    if ($coursedatesinfo) {
        $data['courseduedate'] = ltool_timemanagement_cal_course_duedate($coursedatesinfo, $usercourseenrollinfo[0]['timestart']);
    }
    $data['courseprogress'] = ltool_timemanagement_cal_course_progress($courseid, $userid);
    $sql = "SELECT * FROM {course_completions}
    WHERE course = :course AND userid = :userid AND timecompleted IS NOT NULL";
    $coursecompletion = $DB->get_record_sql($sql, ['userid' => $userid, 'course' => $courseid]);
    $data['coursecompletion'] = !empty($coursecompletion) ? $coursecompletion->timecompleted : '';
    return $data;
}

/**
 * Calculate the user for course progress.
 * @param int $courseid
 * @param int $userid
 * @return string course progress.
 */
function ltool_timemanagement_cal_course_progress($courseid, $userid) {
    global $DB;
    $result = '';
    $modinfo = get_fast_modinfo($courseid);
    $totmods = 0;
    $compmods = 0;
    $progress = 0;
    if (!empty($modinfo->sections)) {
        foreach ($modinfo->sections as $section => $modnumbers) {
            $section = $modinfo->get_section_info($section);
            if ($modnumbers && $section->uservisible) {
                foreach ($modnumbers as $modnumb) {
                    $mod = $modinfo->get_cm($modnumb);
                    $completioninfo = new completion_info($mod->get_course());
                    if ($mod->visible && $completioninfo->is_enabled($mod) != COMPLETION_TRACKING_NONE && $mod->available &&
                        $DB->record_exists('course_modules', array('id' => $mod->id, 'deletioninprogress' => 0))) {
                        $totmods ++;
                        $completiondata = $completioninfo->get_data($mod, true, $userid);
                        $completionstate = $completiondata->completionstate;
                        if ($completionstate == COMPLETION_COMPLETE || $completionstate == COMPLETION_COMPLETE_PASS) {
                            $compmods++;
                        }
                    }
                }
            }
        }
    }
    if ($totmods) {
        $progress = round($compmods / $totmods * 100);
    }
    $stractivities = get_string('activities', 'local_learningtools');
    $result = "$progress% ($compmods/$totmods $stractivities)";
    return $result;
}

/**
 * Calculate course duedate for user.
 * @param stdclass $record
 * @param int $usertimestart enrollment start.
 * @return int return calculate course duedate.
 */
function ltool_timemanagement_cal_course_duedate($record, $usertimestart) {
    $duedate = '';
    $usertimestart = strtotime("midnight", $usertimestart);
    if ($record->duedatetype == 'custom') {
        $duedate = strtotime($record->duedatecustom);
    } else if ($record->duedatetype == 'after') {
        $date = "+" . $record->duedatedigits . $record->duedateduration;
        $duedate = strtotime($date, $usertimestart);
    }
    return $duedate;
}

/**
 * Get enrolled students in the course.
 * @param int $courseid course id.
 * @param int $selectuser
 * @return array studnets info.
 */
function ltool_timemanagement_get_enrolled_course_users($courseid, $selectuser = 0) {
    global $DB;
    // Get course Student.
    $data = [];
    $enrollusers = ltool_timemanagement_course_student_archetype($courseid);
    if (!empty($enrollusers)) {
        foreach ($enrollusers as $enrolluser) {
            $userinfo = [];
            $userinfo['id'] = $enrolluser->id;
            $userinfo['firstname'] = $enrolluser->firstname;
            $userinfo['lastname'] = $enrolluser->lastname;
            $userinfo['username'] = $enrolluser->username;
            if ($selectuser == $enrolluser->id) {
                $userinfo['selected'] = true;
            }
            $data[] = $userinfo;
        }
    }
    return $data;
}

/**
 * Get student archetype users in coursecontext.
 * @param int $courseid
 * @return array students info.
 */
function ltool_timemanagement_course_student_archetype($courseid) {
    $coursecontext = context_course::instance($courseid);
    $students = get_enrolled_users($coursecontext, 'local/learningtools:studentcontroller');
    return $students;
}

/**
 * Get course section & mod info.
 * @param int $courseid
 * @return array
 */
function ltool_timemanagement_get_course_section_mod_info($courseid) {
    global $DB;
    $data = [];
    $course = get_course($courseid);
    $moddata = ltool_timemanagement_get_course_section_mod_data($course, true);
    $data = array_merge($data, ltool_timemanagement_get_coursemanage_dateinfo($course), $moddata);
    $data['moddatastaus'] = !empty($moddata) ? true : false;
    return $data;
}

/**
 * Get course module dates info.
 * @param object $course
 * @param bool $datainfo backend date options.
 * @param int $userid user id.
 * @return array
 */
function ltool_timemanagement_get_course_section_mod_data($course, $datainfo = false, $userid = '') {
    global $DB;
    $modinfo = get_fast_modinfo($course);
    $coursecontext = context_course::instance($course->id);
    $moddata = [];
    if (!empty($modinfo->sections)) {
        foreach ($modinfo->sections as $section => $modnumbers) {
            $sectioninfo = [];
            $moddetails = [];
            $section = $modinfo->get_section_info($section);
            $sectioninfo['sectionname'] = $section->name;
            $sectioninfo['sectionid'] = $section->id;
            $i = 0;
            if (!empty($modnumbers) && $section->uservisible) {
                foreach ($modnumbers as $modnumber) {
                    $info = [];
                    $mod = $modinfo->cms[$modnumber];
                    if ($DB->record_exists('course_modules', array('id' => $mod->id, 'deletioninprogress' => 0))
                        && !empty($mod) ) {
                        if ($userid) {
                            if (!$mod->visible) {
                                continue;
                            }
                        }
                        $info['modname'] = $mod->name;
                        $info['availableinfo'] = !has_capability("ltool/timemanagement:viewothersdates", $coursecontext)
                        && !has_capability("ltool/timemanagement:managedates", $coursecontext) && $mod->availableinfo;
                        $info['url'] = $mod->url;
                        $info['cmid'] = $mod->id;
                        $info['modtype'] = $mod->get_module_type_name();
                        $info['editurl'] = new moodle_url('/course/modedit.php', array('update' => $mod->id));
                        if ($i != 0) {
                            if ($i % 2 == 0) {
                                $info['trclass'] = "gray-block";
                            } else {
                                $info['trclass'] = "white-block";
                            }
                        }
                        if ($i == 0) {
                            $info['modsectionname'] = $section->name;
                            $info['nosection'] = empty($section->name) ? true : false;
                        }
                        if ($datainfo) {
                            $info = array_merge($info, ltool_timemanagement_get_module_update_dateinfo($mod));
                        }
                        if ($userid) {
                            $info = array_merge($info, ltool_timemanagement_get_mod_user_info($mod, $userid));
                        }
                        $moddetails[] = $info;
                        $i++;
                    }
                }
            }
            $sectioninfo['modinfo'] = $moddetails;
            $moddata[] = $sectioninfo;
        }
    }
    return ["moddata" => $moddata];
}

/**
 * Get module user info.
 * @param object $mod
 * @param int $userid
 * @param bool $duestatus
 * @return array|int
 */
function ltool_timemanagement_get_mod_user_info($mod, $userid, $duestatus = false) {
    global $DB;
    $data = [];
    $i = 0;
    $course = $mod->get_course();
    $userenrolments = ltool_timemanagement_get_course_user_enrollment($course->id, $userid);
    if (!empty($userenrolments)) {
        $timestarted = $userenrolments[0]['timestart'];
        $record = $DB->get_record('ltool_timemanagement_modules', array('cmid' => $mod->id));
        $modulecompletion = ltool_timemanagement_get_module_completion_info($mod, $userid);
        $modcompledate = '';
        if (!empty($modulecompletion)) {
            if ($modulecompletion->completionstate == COMPLETION_COMPLETE_PASS
            || $modulecompletion->completionstate == COMPLETION_COMPLETE) {
                $modcompledate = $modulecompletion->timemodified;
            }
        }
        $data['completeiondate'] = $modcompledate;
        if ($record) {
            list('startdate' => $startdate, 'duedate' => $duedate) = ltool_timemanagement_cal_coursemodule_managedates(
                    $record, $timestarted);
            $data['startdate'] = $startdate;
            $data['duedate'] = '';
            if (!empty($duedate)) {
                $today = new DateTime(date('y-m-d'));
                $duedatetime = new DateTime(date('y-m-d', $duedate));
                $dueclass = '';
                $data['duetoday'] = false;
                if (empty($modcompledate)) {
                    if ($today == $duedatetime) {
                        // Due Today.
                        $dueclass = 'text-warning';
                        $data['duetoday'] = true;
                        $i++;
                    } else if ($duedatetime < $today) {
                        // Past Due.
                        $dueclass = 'text-danger';
                        $i++;
                    }
                }
                $data['dueclass'] = $dueclass;
                $data['duedate'] = $duedate;
            }
        }
        if ($duestatus) {
            return $i;
        }
        $userprogressinfo = ltool_timemanagement_get_mod_userprogress_info($mod, $userid);
        $data['progressinfo'] = $userprogressinfo['cmprogress'];
        $data['modbuttonstr'] = $userprogressinfo['modbuttonstr'];
    }
    if ($duestatus) {
        return $i;
    }
    return $data;
}

/**
 * Get counts the course due and overdue.
 * @param int $courseid
 * @param int $userid
 * @return array dues info.
 */
function ltool_timemanagement_get_due_overdue_course($courseid, $userid) {
    global $DB;
    $dues = 0;
    $overdues = 0;
    $modinfo = get_fast_modinfo($courseid);
    $userenrolments = ltool_timemanagement_get_course_user_enrollment($courseid, $userid);
    if (!empty($modinfo->sections) && !empty($userenrolments)) {
        foreach ($modinfo->sections as $modnumbers) {
            if (!empty($modnumbers)) {
                foreach ($modnumbers as $modnumber) {
                    $mod = $modinfo->cms[$modnumber];
                    if ($DB->record_exists('course_modules', array('id' => $mod->id, 'deletioninprogress' => 0))
                        && !empty($mod) && $mod->uservisible) {
                        $record = $DB->get_record('ltool_timemanagement_modules', array('cmid' => $mod->id));
                        $modulecompletion = ltool_timemanagement_get_module_completion_info($mod, $userid);
                        $modcompledate = '';
                        if (!empty($modulecompletion)) {
                            if ($modulecompletion->completionstate == COMPLETION_COMPLETE_PASS
                            || $modulecompletion->completionstate == COMPLETION_COMPLETE) {
                                $modcompledate = $modulecompletion->timemodified;
                            }
                        }
                        if (!empty($record)) {
                            if (!empty($userenrolments)) {
                                $timestarted = $userenrolments[0]['timestart'];
                                list('startdate' => $startdate,
                                'duedate' => $duedate) = ltool_timemanagement_cal_coursemodule_managedates($record, $timestarted);
                                if (!empty($duedate)) {
                                    $today = new DateTime(date('y-m-d'));
                                    $duedatetime = new DateTime(date('y-m-d', $duedate));
                                    if (empty($modcompledate)) {
                                        if ($today == $duedatetime) {
                                            // Due Today.
                                            $dues++;
                                        } else if ($duedatetime < $today) {
                                            // Past Due.
                                             $overdues++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return ['dues' => $dues, 'overdues' => $overdues];
}

/**
 * Calculate the module dates.
 * @param object $record
 * @param int $timestarted
 */
function ltool_timemanagement_cal_coursemodule_managedates($record, $timestarted) {
    $startdate = '';
    $timestarted = strtotime("midnight", $timestarted);
    $startdatetype = $record->startdatetype ? $record->startdatetype : '';
    if ($startdatetype == 'custom') {
        $startdate = strtotime($record->startdatecustom);
    } else if ($startdatetype == 'after') {
        $startdate = strtotime($record->startdatedigits . $record->startdateduration, $timestarted);
    } else if ($startdatetype == 'upon') {
        $startdate = $timestarted;
    }
    $duedate = 0;
    $duedatetype = isset($record->duedatetype) ? $record->duedatetype : '';
    if ($duedatetype == 'custom') {
        $duedate = strtotime($record->duedatecustom);
    } else if ($duedatetype == 'after') {
        $duedate = strtotime($record->duedatedigits . $record->duedateduration, $timestarted);
    }
    return ['startdate' => $startdate, 'duedate' => $duedate];
}

/**
 * Get module completion criteria info.
 * @param object $cm
 * @param object $current completion data.
 * @param int $userid
 * @param object $completioninfo
 * @return array
 */
function ltool_timemanagement_get_completion_criteria_report($cm, $current, $userid, $completioninfo) {
    global $USER, $CFG;
    $details = [];
    $hasoverride = isset($current->overrideby) ? (int) $current->overrideby : false;
    $hasoverride = !empty($hasoverride) ? true : false;
    $course = $cm->get_course();
    // Get user ID.
    if (!$userid) {
        $userid = $USER->id;
    }

    // Completion rule: Student must view this activity.
    if ($cm->completionview == COMPLETION_VIEW_REQUIRED) {
        if (!$hasoverride) {
            $status = COMPLETION_INCOMPLETE;
            if ($current->viewed == COMPLETION_VIEWED) {
                $status = COMPLETION_COMPLETE;
            }
        } else {
            $status = $current->completionstate;
        }

        $details['completionview'] = (object)[
            'status' => $status,
            'description' => get_string('view', 'local_learningtools'),
        ];
    }
     // Completion rule: Student must receive a grade.
    if (!is_null($cm->completiongradeitemnumber)) {
        if (!$hasoverride) {
            $status = COMPLETION_INCOMPLETE;
            require_once($CFG->libdir.'/gradelib.php');
            $item = grade_item::fetch(array('courseid' => $cm->course, 'itemtype' => 'mod',
                'itemmodule' => $cm->modname, 'iteminstance' => $cm->instance,
                'itemnumber' => $cm->completiongradeitemnumber));
            if ($item) {
                $grades = grade_grade::fetch_users_grades($item, array($userid), false);
                if (!empty($grades)) {
                    if (count($grades) == 1) {
                        $newstate = $completioninfo::internal_get_grade_state($item, reset($grades));
                        if ($newstate != COMPLETION_INCOMPLETE) {
                            $status = COMPLETION_COMPLETE;
                        }
                    }
                }
            }
        } else {
            $status = $current->completionstate;
        }
        $details['completionusegrade'] = (object)[
            'status' => $status,
            'description' => get_string('receivegrade', 'local_learningtools'),
        ];
    }

    if (plugin_supports('mod', $cm->modname, FEATURE_COMPLETION_HAS_RULES)) {
        $status = COMPLETION_INCOMPLETE;
        $function = $cm->modname.'_get_completion_state';
        if (function_exists($function)) {
            if ($function($course, $cm, $userid, COMPLETION_AND)) {
                $status = COMPLETION_COMPLETE;
            }
        }
        $details['plugincompletionstate'] = (object)[
                'status' => $status,
                'description' => get_string('completeactivity', 'local_learningtools')
        ];
    }
    $data = [];
    if (!empty($details)) {
        foreach ($details as $key => $detail) {
            $detail->key = $key;
            $detail->statuscomplete = in_array($detail->status, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS]);
            $detail->statuscompletefail = $detail->status == COMPLETION_COMPLETE_FAIL;
            $detail->statusincomplete = $detail->status == COMPLETION_INCOMPLETE;
            // We don't need the status in the template.
            unset($detail->status);
            $data[] = $detail;
        }
    }
    return $data;
}

/**
 * Get module completion data.
 * @param object $mod
 * @param int $userid
 * @return object|string
 */
function ltool_timemanagement_get_module_completion_info($mod, $userid) {
    $completioninfo = new completion_info($mod->get_course());
    if ($completioninfo->is_enabled($mod)) {
        return $completioninfo->get_data($mod, false, $userid);
    }
    return '';
}

/**
 * Calculate user progress for coursemodule.
 * @param object $mod
 * @param int $userid
 */
function ltool_timemanagement_get_mod_userprogress_info($mod, $userid) {
    global $CFG, $PAGE;
    $cmprogress = [];
    $completedmod = false;
    $incompletemod = false;
    $completioninfo = new completion_info($mod->get_course());
    if ($completioninfo->is_enabled($mod)) {
        if ($completioninfo->is_enabled($mod) == COMPLETION_TRACKING_AUTOMATIC) {
            if (class_exists('core_course\\output\\activity_information')) {

                $cminfo = cm_info::create($mod);
                $completiondetails = \core_completion\cm_completion_details::get_instance($cminfo, $userid);
                $activitydates = \core\activity_dates::get_dates_for_module($cminfo, $userid);
                if (!$completiondetails->has_completion() && empty($activitydates)) {
                    // No need to render the activity information when there's no completion info and activity dates to show.
                    return '';
                }
                if ($CFG->branch > 400) {
                    $activityinfo = new \core_course\output\activity_completion($cminfo, $completiondetails);
                } else {
                    $activityinfo = new activity_information($cminfo, $completiondetails, $activitydates);
                }
                $output = $PAGE->get_renderer('core', 'course');
                $cmprogress = $activityinfo->export_for_template($output)->completiondetails;
            } else {
                $current = $completioninfo->get_data($mod, false, $userid);
                $cmprogress = ltool_timemanagement_get_completion_criteria_report($mod, $current, $userid, $completioninfo);
            }
            if ($cmprogress) {
                $completedmodcriteria = 0;
                foreach ($cmprogress as $progress) {
                    if ($progress->statuscomplete) {
                        $completedmodcriteria++;
                    }
                }
                if (count($cmprogress) == $completedmodcriteria) {
                    $completedmod = true;
                } else if ($completedmodcriteria > 0) {
                    $incompletemod = true;
                }
            }
        } else {
            $completedstatus = false;
            $completiondata = $completioninfo->get_data($mod, false, $userid);
            if ($completiondata->completionstate == COMPLETION_COMPLETE_PASS ||
                $completiondata->completionstate == COMPLETION_COMPLETE) {
                $completedstatus = true;
            }
            $cmprogress = [array('description' => 'manual', 'statuscomplete' => $completedstatus)];
            if ($completedstatus) {
                $completedmod = true;
            }
        }
    }
    $modbuttonstr = '';
    if ($completedmod) {
        $modbuttonstr = get_string('review', 'local_learningtools');
    } else if ($incompletemod) {
        $modbuttonstr = get_string('resume', 'local_learningtools');
    } else {
        $modbuttonstr = get_string('open', 'local_learningtools');
    }
    return ['cmprogress' => $cmprogress, 'modbuttonstr' => $modbuttonstr];
}

/**
 * Get user due activies.
 * @param int $courseid
 * @return int count due activites.
 */
function ltool_timemanagement_get_user_dueactivities($courseid) {
    global $DB, $USER;
    $i = 0;
    $duecount = 0;
    $coursecontext = context_course::instance($courseid);
    if (has_capability("ltool/timemanagement:viewothersdates", $coursecontext)
        || has_capability("ltool/timemanagement:managedates", $coursecontext)) {
        return '';
    }
    $userenrolments = ltool_timemanagement_get_course_user_enrollment($courseid, $USER->id);
    $modinfo = get_fast_modinfo($courseid);
    if (!empty($modinfo->sections) && !empty($userenrolments)) {
        foreach ($modinfo->sections as $modnumbers) {
            if (!empty($modnumbers)) {
                foreach ($modnumbers as $modnumber) {
                    $mod = $modinfo->cms[$modnumber];
                    if ($DB->record_exists('course_modules', array('id' => $mod->id, 'deletioninprogress' => 0))
                        && !empty($mod) && $mod->uservisible) {
                            $duecount += ltool_timemanagement_get_mod_user_info($mod, $USER->id, true);
                    }
                }
            }
        }
    }
    return $duecount;
}
/**
 * Get course modules start/due dates.
 * @param object $mod modinfo.
 * @return array start/due dates.
 */
function ltool_timemanagement_get_module_update_dateinfo($mod) {
    global $DB;
    $startdate = [];
    $duedate = [];
    $record = $DB->get_record('ltool_timemanagement_modules', array('cmid' => $mod->id));
    $course = $mod->get_course();
    $courserecord = $DB->get_record('ltool_timemanagement_course', array('course' => $course->id));
    if ($record) {
        $startdate[$record->startdatetype] = true;
        if ($record->startdateduration) {
            $startdate[$record->startdateduration] = true;
        }
        $startdate['startdatedigits'] = $record->startdatedigits;
        $startdate['startdatecustom'] = $record->startdatecustom;
        if ($courserecord) {
            $demoenrolldate = isset($courserecord->demoenrolldate) ? $courserecord->demoenrolldate : 0;
            if ($record->startdatetype == 'after') {
                if ($record->startdatedigits && $record->startdateduration) {
                    $startdate['enrollcaldate'] = strtotime($record->startdatedigits.$record->startdateduration, $demoenrolldate);
                } else {
                    $startdate['enrollcaldate'] = $demoenrolldate;
                }
            } else if ($record->startdatetype == 'upon') {
                $startdate['enrollcaldate'] = $demoenrolldate;
            }
        }

        $duedate[$record->duedatetype] = true;
        if ($record->duedateduration) {
            $duedate[$record->duedateduration] = true;
        }
        $duedate['duedatedigits'] = $record->duedatedigits;
        $duedate['duedatecustom'] = $record->duedatecustom;
        if ($courserecord) {
            $demoenrolldate = isset($courserecord->demoenrolldate) ? $courserecord->demoenrolldate : 0;
            if ($record->duedatetype == 'after') {
                if ($record->duedatedigits && $record->duedateduration) {
                    $duedate['enrollcaldate'] = strtotime($record->duedatedigits.$record->duedateduration, $demoenrolldate);
                } else {
                    $duedate['enrollcaldate'] = $demoenrolldate;
                }
            }
        }
    }
    $startdate['status'] = true;
    $duedate['status'] = true;
    $completioninfo = new completion_info($mod->get_course());
    $duedate['completeionenabled'] = $completioninfo->is_enabled($mod);
    return ['modstartdate' => $startdate, 'modduedate' => $duedate];
}

/**
 * Get Course managedates.
 * @param object $course
 * @return array
 */
function ltool_timemanagement_get_coursemanage_dateinfo($course) {
    global $DB;
    $courseinfo = [];
    $record = $DB->get_record('ltool_timemanagement_course', array('course' => $course->id));
    $demoenrolldate = isset($record->demoenrolldate) ? $record->demoenrolldate : time();
    if ($record) {
        $courseinfo[$record->duedatetype] = true;
        $courseinfo[$record->duedateduration] = true;
        $courseinfo['duedatedigits'] = $record->duedatedigits;
        $courseinfo['duedatecustom'] = $record->duedatecustom;
        if ($record->duedatetype == 'after') {
            if ($record->duedatedigits && $record->duedateduration) {
                $courseinfo['enrollcaldate'] = strtotime($record->duedatedigits.$record->duedateduration, $demoenrolldate);
            } else {
                $courseinfo['enrollcaldate'] = $demoenrolldate;
            }
        }
    }
    $courseinfo['demoenrolldate'] = $demoenrolldate;
    $courseinfo['status'] = true;
    return ['courseinfo' => $courseinfo];
}

/**
 * Update course managedates.
 * @param array $formdata
 * @param int $courseid
 * @return void
 */
function ltool_timemanagement_update_course_managedates($formdata, $courseid) {
    global $DB;
    $context = context_course::instance($courseid);
    $coursemanagement = new stdClass();
    $coursemanagement->course = $courseid;
    $coursemanagement->contextid = $context->id;
    $coursemanagement->duedatetype = $formdata['type-course-due-date'];
    $coursemanagement->duedatecustom = $formdata['course-custom-duedate'];
    $coursemanagement->duedateduration = ($coursemanagement->duedatetype == 'after')
                    ? $formdata['course-duedate-duration'] : '';
    $coursemanagement->duedatedigits = ($coursemanagement->duedatetype == 'after')
                    ? $formdata['course-duedate-digits'] : '';
    $coursemanagement->demoenrolldate = strtotime($formdata['enrolluserdate']);
    $record = $DB->get_record('ltool_timemanagement_course', array('course' => $courseid));
    if (!empty($record)) {
        $coursemanagement->id = $record->id;
        $coursemanagement->timemodified = time();
        $DB->update_record('ltool_timemanagement_course', $coursemanagement);
    } else {
        $coursemanagement->timecreated = time();
        $DB->insert_record('ltool_timemanagement_course', $coursemanagement);
    }
}

/**
 * Update coursemodule managedates.
 * @param array $formdata
 * @param int $courseid
 * @return void
 */
function ltool_timemanagement_update_module_managedates($formdata, $courseid) {
    global $DB;
    $coursemodules = ltool_timemanagement_get_course_modules($courseid);
    if (!empty($coursemodules)) {
        foreach ($coursemodules as $cm) {
            $context = context_module::instance($cm);
            $data = new stdClass();
            $data->course = $courseid;
            $data->cmid = $cm;
            $data->contextid = $context->id;
            $data->startdatetype = isset($formdata["mod$cm-startdate-type"]) ? $formdata["mod$cm-startdate-type"] : '';
            $startdatedigits = isset($formdata["mod$cm-startdate-digits"]) ? $formdata["mod$cm-startdate-digits"] : '';
            $data->startdatedigits = ($data->startdatetype == 'after') ? $startdatedigits : '';
            $startdateduration = isset($formdata["mod$cm-startdate-duration"]) ? $formdata["mod$cm-startdate-duration"] : '';
            $data->startdateduration = ($data->startdatetype == 'after') ? $startdateduration : '';
            $data->startdatecustom = isset($formdata["mod$cm-custom-startdate"]) ? $formdata["mod$cm-custom-startdate"] : '';
            $data->duedatetype = isset($formdata["mod$cm-duedate-type"]) ? $formdata["mod$cm-duedate-type"] : '';
            $duedatedigits = isset($formdata["mod$cm-duedate-digits"]) ? $formdata["mod$cm-duedate-digits"] : '';
            $data->duedatedigits = ($data->duedatetype == 'after') ? $duedatedigits : '';
            $duedateduration = isset($formdata["mod$cm-duedate-duration"]) ? $formdata["mod$cm-duedate-duration"] : '';
            $data->duedateduration = ($data->duedatetype == 'after') ? $duedateduration : '';
            $data->duedatecustom = isset($formdata["mod$cm-custom-duedate"]) ? $formdata["mod$cm-custom-duedate"] : '';
            $record = $DB->get_record('ltool_timemanagement_modules', array('cmid' => $cm));
            if (!empty($record)) {
                $data->id = $record->id;
                $data->timemodified = time();
                $DB->update_record('ltool_timemanagement_modules', $data);
            } else {
                $data->timecreated = time();
                $DB->insert_record('ltool_timemanagement_modules', $data);
            }
            ltool_timemanagement_updated_calendar_module_dates($cm, $courseid);
            ltool_timemanagement_updated_calendar_course_dates($courseid);
        }
    }
}

/**
 * Call module calendar events.
 * @param int $cmid
 * @param int $courseid
 * @return void
 */
function ltool_timemanagement_updated_calendar_module_dates($cmid, $courseid) {
    global $DB;
    $enrollusers = ltool_timemanagement_course_student_archetype($courseid);
    if (!empty($enrollusers)) {
        foreach ($enrollusers as $enrolluser) {
            ltool_timemanagement_user_calendar_module_dates($enrolluser->id, $courseid, $cmid);
        }
    }
}

/**
 * Call course calendar events.
 * @param int $courseid
 * @return void
 */
function ltool_timemanagement_updated_calendar_course_dates($courseid) {
    global $DB;
    $course = get_course($courseid);
    $enrollusers = ltool_timemanagement_course_student_archetype($courseid);
    if (!empty($enrollusers)) {
        foreach ($enrollusers as $enrolluser) {
            $courseinfo = ltool_timemanagement_get_course_userinfo($courseid, $enrolluser->id);
            $courseduedate = $courseinfo['courseduedate'];
            ltool_timemanagement_create_user_event_courseduedates($enrolluser->id, $course, $courseduedate);
        }
    }
}

/**
 * Call calendar events course students.
 * @param int $userid
 * @param int $courseid
 * @param int $cmid
 * @return void
 */
function ltool_timemanagement_user_calendar_module_dates($userid, $courseid, $cmid) {
    $modinfo = get_fast_modinfo($courseid);
    $mod = $modinfo->get_cm($cmid);
    $usermodinfo = ltool_timemanagement_get_mod_user_info($mod, $userid);
    if (isset($usermodinfo['startdate'])) {
        if ($startdate = $usermodinfo['startdate']) {
            ltool_timemanagement_create_user_event_modmanagedates($userid, $cmid, $courseid, $startdate, 'expectstarton');
        }
    }
    if (isset($usermodinfo['duedate'])) {
        $duedate = $usermodinfo['duedate'];
        ltool_timemanagement_create_user_event_modmanagedates($userid, $cmid, $courseid, $duedate, 'expectdueon');

    }
}

/**
 * Create user event for course manage dates.
 * @param int $userid
 * @param object $course
 * @param int $expecteddate time.
 */
function ltool_timemanagement_create_user_event_courseduedates($userid, $course, $expecteddate) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/calendar/lib.php');
    $courseelement = new core_course_list_element($course);
    $event = new stdClass();
    $courseurl = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
        get_string('gotocourse', 'local_learningtools'));
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = "coursedueon";
    if ($event->id = $DB->get_field('event', 'id', array('courseid' => $course->id, 'userid' => $userid,
        'eventtype' => "coursedueon", "component" => "local_learningtools"))) {
        if (!$expecteddate) {
            $calendarevent = \calendar_event::load($event->id);
            $calendarevent->delete();
        } else {
            $event->name = $courseelement->get_formatted_fullname() . " " . get_string('expectdueon', 'local_learningtools');
            $event->format = FORMAT_HTML;
            $event->component = "local_learningtools";
            $event->courseid = $course->id;
            $event->timestart = $expecteddate;
            $event->timesort = $expecteddate;
            $event->visible = 1;
            $calendarevent = \calendar_event::load($event->id);
            $calendarevent->update($event, false);
        }
    } else {
        $event->name = $courseelement->get_formatted_fullname() . get_string('expectdueon', 'local_learningtools');;
        $event->description = $course->summary;
        $event->format = FORMAT_HTML;
        $event->userid = $userid;
        $event->courseid = $course->id;
        $event->timestart = $expecteddate;
        $event->timesort = $expecteddate;
        $event->component = "local_learningtools";
        $event->visible = 1;
        $event->timeduration = 0;
        calendar_event::create($event);
    }
}

/**
 * Create user event for course module manage dates.
 * @param int $userid
 * @param int $cmid
 * @param int $courseid
 * @param int $expecteddate time.
 * @param string $eventtype
 */
function ltool_timemanagement_create_user_event_modmanagedates($userid, $cmid, $courseid, $expecteddate, $eventtype = '') {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/calendar/lib.php');
    $modinstance = ltool_timemanagement_get_course_module_instance($cmid);
    $moddetails = ltool_timemanagement_get_course_module_moduleinfo($cmid);
    $event = new stdClass();
    $event->type = CALENDAR_EVENT_TYPE_ACTION;
    $event->eventtype = $eventtype;
    if ($event->id = $DB->get_field('event', 'id', array('modulename' => $moddetails->name,
        'instance' => $modinstance->id, 'userid' => $userid, 'eventtype' => $eventtype))) {
        if (!$expecteddate) {
            $calendarevent = \calendar_event::load($event->id);
            $calendarevent->delete();
        } else {
            // Calendar event exists so update it.
            $event->name = $modinstance->name . " " . get_string($eventtype, 'local_learningtools');
            $event->description = format_module_intro($moddetails->name, $modinstance, $cmid, false);
            $event->format = FORMAT_HTML;
            $event->timestart = $expecteddate;
            $event->timesort = $expecteddate;
            $event->visible = instance_is_visible($moddetails->name, $modinstance);
            $calendarevent = \calendar_event::load($event->id);
            $calendarevent->update($event, false);
        }
    } else {
        $event->name = $modinstance->name . " " . get_string($eventtype, 'local_learningtools');
        $event->description = format_module_intro($moddetails->name, $modinstance, $cmid, false);
        $event->format = FORMAT_HTML;
        $event->modulename = $moddetails->name;
        $event->instance = $modinstance->id;
        $event->groupid = 0;
        $event->userid = $userid;
        $event->timestart = $expecteddate;
        $event->timesort = $expecteddate;
        $event->visible = instance_is_visible($moddetails->name, $modinstance);
        $event->timeduration = 0;
        calendar_event::create($event);
    }
}

/**
 * Module info.
 * @param int $cmid
 * @return object module info.
 */
function ltool_timemanagement_get_course_module_moduleinfo($cmid) {
    global $DB;
    $coursemoduleinfo = $DB->get_record('course_modules', array('id' => $cmid));
    $moduleinfo = $DB->get_record('modules', array('id' => $coursemoduleinfo->module));
    return $moduleinfo;
}

/**
 * Module instance.
 * @param int $cmid
 * @return object instance info.
 */
function ltool_timemanagement_get_course_module_instance($cmid) {
    global $DB;
    $coursemoduleinfo = $DB->get_record('course_modules', array('id' => $cmid));
    $moduleinfo = $DB->get_record('modules', array('id' => $coursemoduleinfo->module));
    $instance = $DB->get_record($moduleinfo->name , array('id' => $coursemoduleinfo->instance), '*', IGNORE_MISSING);
    return $instance;
}

/**
 * Get course modules.
 * @param int $courseid
 * @return array course activities.
 */
function ltool_timemanagement_get_course_modules($courseid) {
    global $DB;
    $course = get_course($courseid);
    $activities = course_modinfo::get_array_of_activities($course);
    $data = [];
    if ($activities) {
        foreach ($activities as $activity) {
            if ($DB->record_exists('course_modules', array('id' => $activity->cm, 'deletioninprogress' => 0))) {
                $data[] = $activity->cm;
            }
        }
    }
    return $data;
}

/**
 * Call to update course dates.
 * @param array $formdata
 * @param int $courseid
 */
function ltool_timemanagement_updated_managedates($formdata, $courseid) {
    ltool_timemanagement_update_course_managedates($formdata, $courseid);
    ltool_timemanagement_update_module_managedates($formdata, $courseid);
}

/**
 * User Demo enroll date updated.
 * @param int $courseid
 * @param int $date
 * @return bool
 */
function ltool_timemanagement_update_user_course_demoenrollment($courseid, $date) {
    global $DB;
    $record = $DB->get_record('ltool_timemanagement_course', array('course' => $courseid));
    if ($record) {
        $record->demoenrolldate = strtotime($date);
        $record->timemodified = time();
        $DB->update_record('ltool_timemanagement_course', $record);
        return true;
    }
    return false;
}

/**
 * Calculate user date.
 * @param array $args info
 * @return string
 */
function ltool_timemanagement_output_fragment_user_calculatedate($args) {
    if ($args['type'] == 'after') {
        return strtotime($args['aftertime'], $args['pagedate']);
    }
}

/**
 * Add the timemanagment tools reports page link to course administration section under reports category.
 *
 * @param  navigation_node $navigation Navigation nodes.
 * @param  stdclass $course Current course object.
 * @param  stdclass $context Course context object.
 * @return void
 */
function ltool_timemanagement_extend_navigation_course($navigation, $course, $context) {
    $enrollusers = ltool_timemanagement_get_enrolled_course_users($course->id);
    if ($course->enablecompletion && !empty($enrollusers)) {
        $node = $navigation->get('coursereports');
        if (has_capability('ltool/timemanagement:managedates', $context) && !empty($node)) {
            $url = new moodle_url('/local/learningtools/ltool/timemanagement/managecoursedates.php',
                ['course' => $course->id]);
            $node->add(get_string('managetimemanagment', 'local_learningtools'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
        }
    }
}
