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
 * ltool plugin "Learning Tools Resume course" - library file.
 *
 * @package   ltool_email
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Learning tools email template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_email_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_email/email', $templatecontent);
}

/**
 * Load resume course js.
 * @return void
 */
function ltool_email_load_email_js_config() {
    global $PAGE;
    $PAGE->requires->js_call_amd('ltool_email/email', 'init');
}

/**
 * Sent to the email for role users.
 *
 * @param object $data message info.
 * @param object $context
 * @param int $courseid
 * @return void
 */
function ltool_email_sent_email_to_users($data, $context, $courseid) {
    global $USER, $DB;
    $supportuser = \core_user::get_support_user();
    $subject = $data->subject;
    $messagehtml = $data->message['text'];
    $message = html_to_text($messagehtml);
    $roleids = $data->recipients;
    // Store database record.
    $record = new stdClass();
    $record->subject = $subject;
    $record->message = $messagehtml;
    $record->roleids = json_encode($roleids);
    $record->teacher = $USER->id;
    $record->courseid = $courseid;
    $record->timecreated = time();
    $attachment = '';
    $attachementname = '';
    $fs = get_file_storage();
    if (property_exists($data, 'attachments')) {
        $record->attachementdraft = $data->attachments;
        $itemid = file_get_unused_draft_itemid();
        file_save_draft_area_files($data->attachments, $context->id, 'ltool_email', 'attachments',
                   $itemid);
        $fileinfo = file_get_drafarea_files($data->attachments);
        $attachementname = isset($fileinfo->list[0]) ? $fileinfo->list[0]->filename : '';
        if (isset($fileinfo->list[0])) {
            $file = $fs->get_file($context->id, 'ltool_email', 'attachments', $itemid,
                $fileinfo->list[0]->filepath, $fileinfo->list[0]->filename);
            $attachementname = $file->get_filename();
            $attachment = $file->copy_content_to_temp();
        }
    }
    $users = ltool_email_get_user_for_roleids($roleids, $context);
    $userids = [];
    if (!empty($users)) {
        foreach ($users as $user) {
            array_push($userids, $user->id);
            email_to_user($user, $supportuser, $subject, $message, $messagehtml,
                $attachment, $attachementname);
        }
        $record->tousers = json_encode($userids);
        $DB->insert_record('ltool_email_data', $record);
    }
}

/**
 * Get user info based on roleid for course context.
 * @param array $roleids roleids
 * @param object $context course context
 * @return array users info
 */
function ltool_email_get_user_for_roleids($roleids, $context) {
    $usersobject = [];
    if (!empty($roleids)) {
        foreach ($roleids as $roleid) {
            $roleinfo = get_role_users($roleid, $context, true);
            $usersobject += $roleinfo;
        }
    }
    return $usersobject;
}

/**
 * Add the email tools reports page link to course administration section under reports category.
 *
 * @param  navigation_node $navigation Navigation nodes.
 * @param  stdclass $course Current course object.
 * @param  stdclass $context Course context object.
 * @return void
 */
function ltool_email_extend_navigation_course($navigation, $course, $context) {
    global $USER;
    $node = $navigation->get('coursereports');
    if (has_capability('ltool/email:createemail', $context) && !empty($node)) {
        $url = new moodle_url('/local/learningtools/ltool/email/list.php',
            ['id' => $USER->id, 'courseid' => $course->id, 'sesskey' => sesskey()]);
        $node->add(get_string('listemailreports', 'local_learningtools'), $url,
            navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
