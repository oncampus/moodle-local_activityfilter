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

use coding_exception;
use core_course\local\entity\content_item;
use dml_exception;
use local_activityfilter\activity_searcher\contracts\content_item_info;
use RuntimeException;

/**
 * Collects more infos about activity plugins and converts
 * them into the AI format.
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_item_summerizer implements i_content_item_summarizer {
    private static ?array $contentiteminfocache = null;

    /**
     * Constructor.
     *
     * @param content_item_manager $contentitemmng Activity plugin manager
     */
    public function __construct(
        private readonly content_item_manager $contentitemmng,
    ) {
    }

    /**
     * Combines all activity data for an option summary
     *
     * @return content_item_info[] List of activity data
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_content_item_infos(): array {
        if (self::$contentiteminfocache) {
            return self::$contentiteminfocache;
        }

        $contentitems = $this->contentitemmng->get_all();
        if (empty($contentitems)) {
            throw new RuntimeException("No content items found");
        }

        self::$contentiteminfocache = array_map(function (content_item $item): content_item_info {
            return new content_item_info($item);
        }, $contentitems);
        return self::$contentiteminfocache;
    }

    /**
     * Searches if plugin exist in activity data
     *
     * @param string $pluginname Activity name
     * @return content_item_info|false If plugin name is not found in given activity data
     */
    public function get_content_item_info(string $pluginname): content_item_info|false {
        $activities = $this->get_content_item_infos();

        foreach ($activities as $activity) {
            if ($activity->get_name() == $pluginname) {
                return $activity;
            }
        }

        return false;
    }

    /**
     * Get all activities, choose able in the activity chooser (including disabled)
     *
     * @return array List of all choose able activity plugins
     */
    public function get_activities(): array {
        return $this->contentitemmng->get_all();
    }
}
