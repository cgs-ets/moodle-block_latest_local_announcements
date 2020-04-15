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
 * This block lists the latest announcements from the "local_announcements" plugin. 
 * Announcements are contextual based on the user and course.
 *
 * @package   block_latest_local_announcements
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class block_latest_local_announcements_edit_form extends block_edit_form
{

    protected function specific_definition($mform)
    {
        //number of announcements to display
        $type = 'text';
        $name = 'config_displaynum';
        $title = get_string('config:displaynumdesc', 'block_latest_local_announcements');
        $mform->addElement($type, $name, $title);
        $mform->setType('config_displaynum', PARAM_INT);
        $mform->addRule('config_displaynum', get_string('err_numeric', 'form'), 'numeric', null, 'client');
    }

}