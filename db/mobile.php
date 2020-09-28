<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Mobile definitions
 *
 * @package    block_latest_local_announcements
 * @copyright  2020 Michael Vangelovski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$addons = array(
    'block_latest_local_announcements' => array(
        'handlers' => array( // Different places where the add-on will display content.
            'mobile_course_view' => array( // Handler unique name (can be anything)
                'displaydata' => array(
                    'title' => '',
                    'class' => 'block_latest_local_announcements'
                ),
                'styles' => [
                    'url' => $CFG->wwwroot . '/blocks/latest_local_announcements/mobileapp.css?v=2020092302',
                    'version' => 2020092302
                ],
                'delegate' => 'CoreBlockDelegate', // Delegate (where to display the link to the add-on)
                'method' => 'mobile_course_view', // Main function in \block_my_day_timetable\output\mobile
            )
        ),
        'lang' => array(
        )
    )
);
