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
 * A companion block for the "local_announcements" plugin that displays a configurable number 
 * of most recent announcements. Announcements are selected contextually, based on the user and 
 * course that the block is added to.
 *
 * @package   block_latest_local_announcements
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

use local_announcements\persistents\announcement;
use local_announcements\providers\audiences\audience_mdlcourse;
use local_announcements\external\list_exporter;
if (file_exists($CFG->dirroot . '/local/announcements/lib.php')) {
  require_once($CFG->dirroot . '/local/announcements/lib.php');
}

define('DEFAULT_COURSE_ANN_DISPLAYNUM', 5);

class block_latest_local_announcements extends block_base {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = '';
    }

    /**
    * We have global config/settings data.
    * @return bool
    */
    public function has_config() {
        return true;
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }


    /**
     * Set where the block should be allowed to be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }


    /**
     * Used to generate the content for the block.
     * @return object
     */
    public function get_content() {
        global $PAGE, $COURSE, $USER, $OUTPUT, $DB, $CFG;

        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (isguestuser() || !isloggedin()) {
            return $this->content;
        }

        // Check whether local_announcements is installed. 
        if (!file_exists($CFG->dirroot . '/local/announcements/lib.php')) {
            $notification = new \core\output\notification(get_string('nolocalplugin', 'block_latest_local_announcements'),
                \core\output\notification::NOTIFY_ERROR);
            $notification->set_show_closebutton(false);
            $this->content->text = $OUTPUT->render($notification);
            return $this->content;
        }

        // Get the global config.
        $globalconfig = get_config('block_latest_local_announcements');

        // The context of announcements is always system.
        $context = context_system::instance();

        $ishome = ($PAGE->pagetype == 'site-index');

        $displaynum = DEFAULT_COURSE_ANN_DISPLAYNUM;
        if (!empty($this->config->displaynum)) {
            $displaynum = $this->config->displaynum;
        } else if (!empty($globalconfig->displaynum)) {
            $displaynum = $globalconfig->displaynum;
        }
        
        // Get the announcements, depending on audit mode and audiences.
        if (is_auditing_on()) {
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
            'instanceid' => $this->instance->id,
            'addnewurl' => $addnewlink->out(false),
            'viewmoreurl' => $viewmorelink->out(false),
            'viewmoretitle' => $viewmoretitle,
            'canaudit' => is_user_auditor(),
            'auditingon' => is_auditing_on(),
            'ishome' => $ishome,
        );

        // Render the announcement list
        $this->content->text = $OUTPUT->render_from_template('block_latest_local_announcements/list', $data);

        $this->page->requires->js_call_amd('block_latest_local_announcements/content', 'init', array($USER->id));
        $this->page->requires->js_call_amd('local_announcements/list', 'init', array('rootselector' => '.block_latest_local_announcements-content'));

        return $this->content;
    }


}
