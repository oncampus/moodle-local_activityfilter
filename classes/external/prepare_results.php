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

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_activityfilter\output\activity_rating_list;

class prepare_results extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'items' => new external_multiple_structure(new external_single_structure([
                'pluginname' => new external_value(PARAM_TEXT, 'module name (e.g. assign, wiki)'),
                'ranking'    => new external_value(PARAM_INT,  '1..10'),
                'occurences' => new external_value(PARAM_INT,  'optional', VALUE_OPTIONAL),
                'hint'       => new external_value(PARAM_RAW,  'optional', VALUE_OPTIONAL),
                'reason'     => new external_value(PARAM_RAW,  'optional', VALUE_OPTIONAL),
            ])),
        ]);
    }

    public static function execute($items): array {
        global $OUTPUT;

        $params = self::validate_parameters(self::execute_parameters(), ['items' => $items]);
        self::validate_context(context_system::instance());

        $ratinglist = new activity_rating_list($params['items']);
        return ['html' => $OUTPUT->render($ratinglist)];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'Rendered HTML for results'),
        ]);
    }
}
