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
 * Strings for block_latest_local_announcements
 *
 * @package   block_latest_local_announcements
 * @copyright 2020 Michael Vangelovski <michael.vangelovski@hotmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
$string['pluginname'] = 'Latest local announcements';
$string['nolocalplugin'] = 'The "Latest local announcements" block requires "local_announcements" plugin to be installed.';
$string['pluginname_desc'] = 'A companion block for the "local_announcements" plugin that displays a configurable number of most recent announcements. Announcements are selected contextually, based on the user and course that the block is added to.';
$string['title'] = 'Announcements';
$string['latest_local_announcements:addinstance'] = 'Add a "Latest local announcements" block';
$string['latest_local_announcements:edit'] = 'Edit a "Latest local announcements" block';
$string['latest_local_announcements:myaddinstance'] = 'Add a "Latest local announcements" block to the Dashboard';
$string['privacy:metadata'] = 'The "Latest local announcements" block does not store any personal data.';
$string['viewmorefromcourse'] = 'View more'; 
$string['viewallannouncements'] = 'View all';
$string['addanewannouncement'] = 'Add';

/* Settings strings */
$string['config:displaynum'] = 'Number of announcements';
$string['config:displaynumdesc'] = 'Number of most recent announcements to display';

$string['auditingonicon'] = '<i class="fa fa-eye fa-fw" aria-hidden="true"></i>';
$string['auditingontitle'] = 'You are currently in audit mode which means you see all announcements. Click to turn off and view announcements targeted specifically to you.';
$string['auditingofficon'] = '<i class="fa fa-eye-slash fa-fw" aria-hidden="true"></i>';
$string['auditingofftitle'] = 'You are currently viewing announcements targeted specifically to you. Click to turn auditing mode on.';