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

namespace local_activityfilter\external;

use context_course;
use core\di;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use moodle_database;

/**
 * Webservice to get most used content item occurrence count
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_max_content_item_occurrence extends external_api {
    /**
     * Get most used content item occurrence count
     *
     * @param int $courseid ID of the course
     * @return int most used content item occurrence count
     *             0 if no content item exists
     */
    public static function execute(int $courseid): int {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
        ]);

        $ctx = context_course::instance($params['courseid']);
        self::validate_context($ctx);
        require_capability('local/activityfilter:get_max_content_item_occurrence', $ctx);

        $db = di::get(moodle_database::class);
        $record = $db->get_record_sql(
            'SELECT MAX(module_count_table.mc) AS maxcount
                 FROM (
                    SELECT COUNT(*) AS mc
                    FROM {course_modules} cm
                    JOIN {modules} m ON m.id = cm.module
                    GROUP BY m.name
                ) module_count_table'
        );

        return $record ? $record->maxcount : 0;
    }

    /**
     * Get expected input parameter structure for webservice
     *
     * @return external_function_parameters Function parameter structure
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT),
        ]);
    }

    /**
     * Get expected output format of webservice
     *
     * @return external_value expected output format
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_INT, 'Max content item occurrence count');
    }
}
