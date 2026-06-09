<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_activityfilter\activity_searcher;

use local_activityfilter\activity_searcher\contracts\content_item_info;

/**
 * Summarizes Infos about the Mod Plugins
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface i_content_item_summarizer {
    public function get_content_item_info(string $pluginname): content_item_info|false;

    /**
     * Summarizes infos about all active activities in moodle
     *
     * @return content_item_info[] List of activity data
     */
    public function get_content_item_infos(): array;

    /**
     * Gets all activities from moodle
     *
     * @return array|null List of mods in moodle
     */
    public function get_activities(): array|null;
}
