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
 * Defines the global settings of the block
 *
 * @package   block_latest_local_announcements
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('block_latest_local_announcements_settings', '', 
        get_string('pluginname_desc', 'block_latest_local_announcements')
    ));

    // Display number.
    $name = 'block_latest_local_announcements/displaynum';
    $title = get_string('config:displaynum', 'block_latest_local_announcements');
    $description = get_string('config:displaynumdesc', 'block_latest_local_announcements');
    $default = 5;
    $type = PARAM_INT;
    $setting = new admin_setting_configtext($name, $title, $description, $default, $type);
    $settings->add($setting);

}
