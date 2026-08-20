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
 * Data provider for block_rofeoparent.
 *
 * @package    block_rofeoparent
 * @copyright  2026 ROFEO Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rofeoparent\local;

use context;
use context_course;
use context_user;
use core\user;
use core_completion\progress;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');

/**
 * Builds the per-child, per-course data shown by the block, applying the
 * mandatory capability gate before touching any of a child's data.
 */
class children_repository {

    /**
     * Returns the current user's children, each with their courses.
     *
     * A child is entirely omitted, along with all of their course data, when
     * the current user lacks moodle/user:viewuseractivitiesreport in that
     * child's user context. That capability is what grade_report_overview
     * and report_outline actually check for a parent viewing a child's
     * report, so checking it here is what makes the same links work when
     * clicked rather than being an IDOR into data the user can't reach.
     *
     * @param int $userid The current user's id.
     * @param context $blockcontext The block instance context, used for name display rules.
     * @return array List of stdClass{id, fullname, courses[]}.
     */
    public static function get_for_user(int $userid, context $blockcontext): array {
        global $DB;

        $userfieldsapi = \core_user\fields::for_name();
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false);
        [$usersort] = users_order_by_sql('u', null, $blockcontext, $userfieldssql->mappings);

        $childrecords = $DB->get_records_sql(
            "SELECT u.id, $userfieldssql->selects
               FROM {role_assignments} ra
               JOIN {context} c ON c.id = ra.contextid
               JOIN {user} u ON u.id = c.instanceid
              WHERE ra.userid = :userid
                AND c.contextlevel = :contextlevel
           ORDER BY $usersort",
            ['userid' => $userid, 'contextlevel' => CONTEXT_USER]
        );

        $children = [];
        foreach ($childrecords as $child) {
            if (!has_capability('moodle/user:viewuseractivitiesreport', context_user::instance($child->id))) {
                continue;
            }

            $entry = new stdClass();
            $entry->id = $child->id;
            $entry->fullname = user::get_fullname($child, $blockcontext);
            $entry->courses = self::get_courses_for_child((int) $child->id);
            $children[] = $entry;
        }

        return $children;
    }

    /**
     * Returns one child's courses with progress and grade data.
     *
     * @param int $childid
     * @return array List of stdClass{id, fullname, progress, showgrades, grade, showreports}.
     */
    private static function get_courses_for_child(int $childid): array {
        $courses = enrol_get_users_courses($childid, true, 'showgrades, showreports, enablecompletion');

        $courseentries = [];
        foreach ($courses as $course) {
            $coursecontext = context_course::instance($course->id);

            $entry = new stdClass();
            $entry->id = $course->id;
            $entry->fullname = format_string(get_course_display_name_for_list($course), true, ['context' => $coursecontext]);

            $entry->progress = progress::get_course_progress_percentage($course, $childid);

            $entry->showgrades = (bool) $course->showgrades;
            $entry->grade = null;
            if ($entry->showgrades) {
                $grade = grade_get_course_grade($childid, $course->id);
                if ($grade && !$grade->hidden && $grade->grade !== null && $grade->grade !== false) {
                    $entry->grade = $grade->str_long_grade;
                }
            }

            $entry->showreports = (bool) $course->showreports;

            $courseentries[] = $entry;
        }

        return $courseentries;
    }
}
