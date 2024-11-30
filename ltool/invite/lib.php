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
 * ltool plugin "Learning Tools Invite" - library file.
 *
 * @package   ltool_invite
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Learning tools invite template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_invite_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_invite/invite', $templatecontent);
}

/**
 * Load invite js.
 * @return void
 */
function ltool_invite_load_js_config() {
    global $PAGE, $USER, $DB;
    $params = [];
    if (!empty($PAGE->course->id)) {
        $params['course'] = $PAGE->course->id;
        $params['user'] = $USER->id;
        $params['contextid'] = $PAGE->context->id;
        $params['contextlevel'] = $PAGE->context->contextlevel;
        $params['pageurl'] = $PAGE->url->out(false);
        $params['strinviteusers'] = get_string('inviteusers', 'local_learningtools');
        $params['strinvitelist'] = get_string('inviteuserslist', 'local_learningtools');
        $params['strinvitenow'] = get_string('invitenow', 'local_learningtools');
        $params['strcancel'] = get_string('cancel');
        $params['showreports'] = has_capability('ltool/invite:createinvite', $PAGE->context);
        $params['showinvitebox'] = $DB->record_exists('enrol', array('courseid' => $PAGE->course->id, 'enrol' => 'manual'));
        $PAGE->requires->js_call_amd('ltool_invite/ltoolsinvite', 'init', array($params));
    }
}

/**
 * Load the invite users form
 * @param array $args page arguments
 * @return string Display the html invite users form.
 */
function ltool_invite_output_fragment_get_inviteusers_form($args) {
    global $OUTPUT;
    if ($args['showinvitebox']) {
        $inviteform = new ltool_inviteusers_mform($args['pageurl']);
        $formhtml = html_writer::start_tag('div', array('id' => 'invite-users-area'));
        $formhtml .= $inviteform->render();
        $formhtml .= html_writer::end_tag('div');
        return $formhtml;
    } else {
        return get_string('invite_maunalenrolerr', 'local_learningtools');
    }
}

/**
 * Action of invite users email.
 * @param mixed $params info
 * @param mixed $data user data
 * @return bool status
 */
function ltool_invite_users_action($params, $data) {
    global $DB, $PAGE, $USER;
    if (isset($data['inviteusers']) && !empty($data['inviteusers'])) {
        $useremails = $data['inviteusers'];
        if (!is_array($useremails)) {
            $useremails = explode("\n", $useremails);
        }
        $teacher = $DB->get_record('user', array('id' => $USER->id));
        $course = $DB->get_record('course', array('id' => $params->course));
        $coursecontext = context_course::instance($course->id);
        $PAGE->set_context($coursecontext);
        $plugin = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
        $studentroleids = array_keys(get_roles_with_capability('local/learningtools:studentcontroller'));
        if (!empty($useremails) && !empty($studentroleids) && has_capability("ltool/invite:createinvite", $coursecontext)) {
            foreach ($useremails as $useremail) {
                $record = new stdClass;
                $record->teacher = $teacher->id;
                $record->course = $course->id;
                $record->email = $useremail;
                $record->timecreated = time();
                $useremail = trim($useremail);
                if ($DB->record_exists('user', array('email' => $useremail))) {
                    $user = $DB->get_record('user', array('email' => $useremail));
                    $record->userid = $user->id;
                    if (!empty($user) && !$user->suspended) {
                        if (!is_enrolled($coursecontext, $user)) {
                            $plugin->enrol_user($instance, $user->id, $studentroleids[0], time());
                            $record->status = 'enrolled';
                            $record->enrolled = 1;
                        } else {
                            $record->status = "alredyenrolled";
                            $record->enrolled = 0;
                        }
                    } else if ($user->suspended) {
                        $record->status = "suspended";
                        $record->enrolled = 0;
                    }
                } else {
                    if (validate_email($useremail)) {
                        // Create user and enroll to the instance course.
                        $donotcreateusers = get_config('ltool_invite', 'donotcreateusers');
                        if (!$donotcreateusers) {
                            $newuserid = ltool_invite_create_user($useremail);
                            $newuser = $DB->get_record('user', array('id' => $newuserid));
                            if (!empty($newuser)) {
                                $plugin->enrol_user($instance, $newuser->id, $studentroleids[0]);
                                $record->userid = $newuser->id;
                                $record->status = 'registerandenrolled';
                                $record->enrolled = 1;
                            }
                        } else {
                            $record->status = "invaildemail";
                            $record->enrolled = 0;
                        }
                    } else {
                        continue;
                    }
                }
                $DB->insert_record('ltool_invite_data', $record);
            }
        }
        return true;
    }
    return false;
}

/**
 * Create a user through the specific email.
 * @param string $email user email.
 */
function ltool_invite_create_user($email) {
    global $CFG, $PAGE;
    require_once($CFG->dirroot.'/user/lib.php');
    $user = new StdClass;
    $user->username = $email;
    $user->course = 1;
    $user->email = $email;
    $user->confirmed = 1;
    $user->deleted = 0;
    $user->auth = 'manual';
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->preference_auth_forcepasswordchange = 1;
    $user->sesskey = sesskey();
    // Create a user.
    $userid = user_create_user($user, false, false);
    $user->id = $userid;
    setnew_password_and_mail($user);
    // Set force password.
    set_user_preference('auth_forcepasswordchange', 1, $user);
    return $userid;
}

/**
 * Add the invite tools reports page link to course administration section under reports category.
 *
 * @param  navigation_node $navigation Navigation nodes.
 * @param  stdclass $course Current course object.
 * @param  stdclass $context Course context object.
 * @return void
 */
function ltool_invite_extend_navigation_course($navigation, $course, $context) {
    global $USER, $PAGE;
    $node = $navigation->get('coursereports');
    if (has_capability('ltool/invite:createinvite', $context) && !empty($node)) {
        $url = new moodle_url('/local/learningtools/ltool/invite/list.php',
            ['id' => $USER->id, 'courseid' => $course->id]);
        $node->add(get_string('inviteuserslist', 'local_learningtools'), $url,
            navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Display invite user email textarea
 */
class ltool_inviteusers_mform extends moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('textarea', 'inviteusers', get_string('usersemail', 'local_learningtools'),
             'wrap="virtual" rows="15" cols=50"');
        $mform->addHelpButton('inviteusers', 'inviteusers', 'ltool_email');
        $submitlabel = get_string('savechanges');
        $mform->addElement('submit', 'submitbutton', $submitlabel, array('id' => 'inviteuser-action',
            'class' => 'inviteuser-action'));
        $mform->closeHeaderBefore('submitbutton');
    }
}


