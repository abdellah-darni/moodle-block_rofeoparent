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
 * ROFEO parent overview block.
 *
 * @package    block_rofeoparent
 * @copyright  2026 ROFEO Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_rofeoparent\local\children_repository;
use block_rofeoparent\output\parent_overview;

defined('MOODLE_INTERNAL') || die();

class block_rofeoparent extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_rofeoparent');
    }

    public function applicable_formats() {
        return ['my' => true];
    }

    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $children = children_repository::get_for_user((int) $USER->id, $this->context);

        // Leave $this->content->text unset when there is nothing to show, so
        // block_base::is_empty() suppresses the instance entirely.
        if ($children) {
            $renderable = new parent_overview($children);
            $renderer = $this->page->get_renderer('block_rofeoparent');
            $this->content->text = $renderer->render($renderable);
        }

        return $this->content;
    }
}
