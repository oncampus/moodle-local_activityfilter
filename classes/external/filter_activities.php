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

use core\di;
use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_activityfilter\activity_searcher\contracts\i_activity_searcher;

/**
 * Filter activities
 *
 * @copyright   2025 Team 13 <team13@mailbox.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_activities extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'prompt' => new external_value(PARAM_TEXT),
        ]);
    }

    public static function execute(string $prompt): array {
        $params = self::validate_parameters(self::execute_parameters(), ['prompt' => $prompt]);
        self::validate_context(context_system::instance());
        $activitysearcher = di::get(i_activity_searcher::class);
        return $activitysearcher->filter_activities($params['prompt']);
    }

    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                    'pluginname' => new external_value(PARAM_ALPHANUM),
                    'ranking' => new external_value(PARAM_INT),
                    'occurences' => new external_value(PARAM_INT),
                    'hint' => new external_value(PARAM_TEXT),
                    'reason' => new external_value(PARAM_TEXT),
            ]),
            '',
            VALUE_REQUIRED,
            [],
            true
        );
    }
}
