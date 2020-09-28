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
 *  External Web Service Template
 *
 * @package   block_latest_local_announcements
 * @category
 * @copyright 2020 Michael Vangelovski
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_announcements\persistents\announcement;
use local_announcements\providers\audiences\audience_mdlcourse;
use local_announcements\external\list_exporter;
if (file_exists($CFG->dirroot . '/local/announcements/locallib.php')) {
    require_once($CFG->dirroot . '/local/announcements/locallib.php');
}

/**
 * Initial announcements block data
 *
 */
function block_latest_local_announcements_init($instanceid) {
    global $PAGE, $COURSE, $USER, $OUTPUT, $DB, $CFG;

    // Local announcements must exist.
    if (!file_exists($CFG->dirroot . '/local/announcements/lib.php')) {
        return null;
    }

    // Get the configs.
    $globalconfig = get_config('block_latest_local_announcements');
    $blockrecord = $DB->get_record('block_instances', array('id' => $instanceid), '*');
    $config = unserialize(base64_decode($blockrecord->configdata));

    // The context of announcements is always system.
    $context = context_system::instance();

    $displaynum = DEFAULT_COURSE_ANN_DISPLAYNUM;
    if (!empty($config->displaynum)) {
        $displaynum = $config->displaynum;
    } else if (!empty($globalconfig->displaynum)) {
        $displaynum = $globalconfig->displaynum;
    }

    // Check whether block is on site home.
    $ishome = ($PAGE->pagetype == 'site-index' || $COURSE->id == 1);
    $iswebservice = ($PAGE->pagetype == 'webservice-rest-server'); // From mobile.

    // Get the announcements, depending on audit mode and audiences.
    if (is_auditing_on() && !$iswebservice) { // Mobile does not support auditing.
        if ($ishome) {
            $announcements = announcement::get_all(null, $displaynum);
        } else {
            $announcements = announcement::get_all_by_audience('mdlcourse', '', 
                $COURSE->idnumber, null, $displaynum);
        }
    } else {
        if ($ishome) {
            $announcements = announcement::get_by_username($USER->username, null, $displaynum);
        } else {
            if (can_view_all_in_context(context_course::instance($COURSE->id))) {
                $announcements = announcement::get_all_by_audience('mdlcourse', '', 
                    $COURSE->idnumber, null, $displaynum);
            } else {
                $announcements = announcement::get_by_username_and_audience($USER->username, 'mdlcourse', '', 
                    $COURSE->idnumber, null, $displaynum);
            }
        }
    }

    $relateds = [
        'context' => $context,
        'announcements' => $announcements,
        'page' => 0,
    ];
    $list = new list_exporter(null, $relateds);

    // Need to determine the audience type of the course we are in (e.g. academic vs house) for links.
    $audiencetype = '';
    if (!$ishome) {
        $audiencetype = \local_announcements\providers\audiences\audience_mdlcourse::get_audience_type($COURSE->id);
    }

    // Add new announcement link - based on context
    $addnewlinkparams = [];
    if (!$ishome) {
        $addnewlinkparams = [
            'type' => $audiencetype, 
            'code' => $COURSE->idnumber, 
        ];
    }
    $addnewlink = new \moodle_url('/local/announcements/post.php', $addnewlinkparams);

    // View more link - based on context
    $viewmoretitle = '';
    $viewmorelinkparams = [];
    if ($ishome) {
        $viewmoretitle = get_string('viewallannouncements', 'block_latest_local_announcements');
    } else {
        $viewmoretitle = get_string('viewmorefromcourse', 'block_latest_local_announcements', $COURSE->shortname);
        $viewmorelinkparams = [
            'type' => $audiencetype, 
            'code' => $COURSE->idnumber, 
        ];
    }
    $viewmorelink = new \moodle_url('/local/announcements/index.php', $viewmorelinkparams);

    // Contruct the data for rendering
    $data = array(
        'list' => $list->export($OUTPUT),
        'canpost' => can_user_post_announcement(),
        'instanceid' => $instanceid,
        'addnewurl' => $addnewlink->out(false),
        'viewmoreurl' => $viewmorelink->out(false),
        'viewmoretitle' => $viewmoretitle,
        'canaudit' => is_user_auditor(),
        'auditingon' => is_auditing_on(),
        'ishome' => $ishome,
    );

    return $data;
}