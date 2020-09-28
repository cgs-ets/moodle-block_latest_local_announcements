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

require_once($CFG->dirroot . '/blocks/latest_local_announcements/lib.php');
if (file_exists($CFG->dirroot . '/local/announcements/locallib.php')) {
    require_once($CFG->dirroot . '/local/announcements/locallib.php');
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
        global $USER, $OUTPUT, $CFG;

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

        $data = block_latest_local_announcements_init($this->instance->id);

        // Render the announcement list
        $this->content->text = $OUTPUT->render_from_template('block_latest_local_announcements/list', $data);

        $this->page->requires->js_call_amd('block_latest_local_announcements/content', 'init', array($USER->id));
        $this->page->requires->js_call_amd('local_announcements/list', 'init', array('rootselector' => '.block_latest_local_announcements-content'));

        return $this->content;
    }


}
