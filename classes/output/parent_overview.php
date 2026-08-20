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
 * Renderable for the ROFEO parent overview block.
 *
 * @package    block_rofeoparent
 * @copyright  2026 ROFEO Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rofeoparent\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * Shapes the repository's children/courses data for the mustache template:
 * rounds percentages, builds the grade/activity report URLs (only when the
 * course actually allows them), and never emits a placeholder for a
 * progress or grade value that isn't available.
 */
class parent_overview implements renderable, templatable {

    /** @var array */
    protected $children;

    /**
     * @param array $children Output of children_repository::get_for_user().
     */
    public function __construct(array $children) {
        $this->children = $children;
    }

    public function export_for_template(renderer_base $output) {
        $children = [];

        foreach ($this->children as $child) {
            $courses = [];
            foreach ($child->courses as $course) {
                $courseexport = [
                    'fullname' => $course->fullname,
                    'hasprogress' => $course->progress !== null,
                    'progress' => $course->progress !== null ? (int) round($course->progress) : null,
                ];
                if ($courseexport['hasprogress']) {
                    $courseexport['progresslabel'] = get_string('courseprogress', 'block_rofeoparent', $course->fullname);
                }

                $courseexport['hasgrade'] = $course->grade !== null;
                $courseexport['grade'] = $course->grade;

                $courseexport['showgradelink'] = $course->showgrades;
                if ($course->showgrades) {
                    $courseexport['gradeurl'] = (new moodle_url('/course/user.php', [
                        'mode' => 'grade',
                        'id' => $course->id,
                        'user' => $child->id,
                    ]))->out(false);
                }

                $courseexport['showactivitylink'] = $course->showreports;
                if ($course->showreports) {
                    $courseexport['activityurl'] = (new moodle_url('/report/outline/user.php', [
                        'id' => $child->id,
                        'course' => $course->id,
                        'mode' => 'outline',
                    ]))->out(false);
                }

                $courses[] = $courseexport;
            }

            $children[] = [
                'fullname' => $child->fullname,
                'courses' => $courses,
            ];
        }

        return ['children' => $children];
    }
}
