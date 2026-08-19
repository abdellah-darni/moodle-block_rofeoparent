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
 * Renderer for the ROFEO parent overview block.
 *
 * @package    block_rofeoparent
 * @copyright  2026 ROFEO Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_rofeoparent\output;

use plugin_renderer_base;

defined('MOODLE_INTERNAL') || die();

class renderer extends plugin_renderer_base {

    /**
     * @param parent_overview $widget
     * @return string HTML string.
     */
    public function render_parent_overview(parent_overview $widget) {
        return $this->render_from_template('block_rofeoparent/parent_overview', $widget->export_for_template($this));
    }
}
