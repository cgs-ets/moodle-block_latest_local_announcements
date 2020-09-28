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
 * Mobile output class
 *
 * @package    block_latest_local_announcements
 * @copyright  2020 Michael Vangelovski
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_latest_local_announcements\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/latest_local_announcements/lib.php');

class mobile {

    /**
     * Returns the content for the mobile app.
     *
     * @param array $args Web service args, courseid is required.
     * @return array Web service response: template, javascript and initial page of messages.
     */
    public static function mobile_course_view(array $args) {
        global $OUTPUT, $PAGE, $DB, $CFG;
        if ($args['contextlevel'] != 'course') {
            return;
        }

        $course = get_course($args['instanceid']);
        $PAGE->set_course($course);
       
        $blockinstanceid = self::find_blockinstanceid($course, 'latest_local_announcements');
        if (empty($blockinstanceid)) {
            // return early with no content.
            return ['templates' => [['id' => 'latest_local_announcements_content','html' => ' ']]];
        }

        $OUTPUT = $PAGE->get_renderer('core');

        $data = block_latest_local_announcements_init($blockinstanceid);
        $html = $OUTPUT->render_from_template('block_latest_local_announcements/mobile', $data);

        return [
            'templates' => [
                [
                    'id' => 'latest_local_announcements_content',
                    'html' => $html
                ]
            ],
        ];
    }

    /**
     * Attempts to find the block instance id based on the course.
     *
     * @param array $course The course record
     * @param string $blockname.
     * @return int instance id.
     */
    public static function find_blockinstanceid($course, $blockname) {
        global $DB;

        $context = \context_course::instance($course->id);

        // The block instance is not passed in as an arg which sucks. 
        // This is a problem for blocks that support multiple instances as we can only select one.
        $sql = "SELECT bi.id
                  FROM {block_instances} bi
                 WHERE parentcontextid = ?
                   AND blockname = ? ";
        $params = array($context->id, $blockname);

        // Exclude hidden blocks.
        $hiddenblockssql = "SELECT bi.id
                              FROM {block_instances} bi
                        INNER JOIN {block_positions} bp ON bp.blockinstanceid = bi.id
                             WHERE bi.parentcontextid = ?
                               AND bi.blockname = ?
                               AND bp.contextid = ?
                               AND bp.visible = 0
                               AND (bp.pagetype = ? OR bp.pagetype = 'site-index')";
        $hiddenblocksparams = array($context->id, $blockname, $context->id, 'course-view-' . $course->format);
        $hiddenblocks = array_column($DB->get_records_sql($hiddenblockssql, $hiddenblocksparams), 'id');
        if ($hiddenblocks) {
            list($hiddenblockssql, $hiddenblocksparams) = $DB->get_in_or_equal($hiddenblocks);
            $not = (strpos($hiddenblockssql, 'IN') !== false) ? 'NOT ' : '!';
            $sql .= "AND bi.id $not$hiddenblockssql ";
            $params = array_merge($params, $hiddenblocksparams);
        }

        // Exclude disabledblocks from tool_mobilecgs
        $disabledblockssql = "SELECT value
                FROM {config_plugins}
                WHERE plugin = 'tool_mobilecgs'
                AND name = 'disabledblocks'";
        $disabledblocks = $DB->get_field_sql($disabledblockssql);
        $disabledblocks = explode(',', $disabledblocks);
        if ($disabledblocks) {
            list($disabledblockssql, $disabledblocksparams) = $DB->get_in_or_equal($disabledblocks);
            $not = (strpos($disabledblockssql, 'IN') !== false) ? 'NOT ' : '!';
            $sql .= "AND bi.id $not$disabledblockssql ";
            $params = array_merge($params, $disabledblocksparams);
        }

        // ORDER BY SQL
        $sql .= "ORDER BY bi.id";
        $blockinstanceid = $DB->get_field_sql($sql, $params, IGNORE_MULTIPLE);

        return $blockinstanceid;
    }

}
