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

namespace local_activityfilter\output;

use core\di;
use local_activityfilter\activity_searcher\contracts\activity_ranking;
use moodle_database;
use templatable;
use renderable;
use core\output\renderer_base;

class activity_rating_box implements renderable, templatable {
    const PLUGIN_NAME = 'local_activityfilter';

    public function __construct(
        private readonly array $activityrating
    ) {
    }

    private function get_max_activity_usage_amount(): int {
        $db = di::get(moodle_database::class);
        $record = $db->get_record_sql(
            'SELECT COUNT(*) AS count
                 FROM {course_modules} cm
                 JOIN {modules} m ON m.id = cm.module
                 GROUP BY m.name
                 ORDER BY COUNT(*) DESC',
            strictness: IGNORE_MULTIPLE
        );
        if ($record === false) {
            debugging('No activities available for counting');
            return 0;
        }
        return $record->count;
    }

    private function get_occurance_string() {
        $maxusage = max($this->get_max_activity_usage_amount(), 1);
        $frequencyranking = $this->activityrating["occurences"] * 5 / $maxusage;
        $frequencyranking = floor($frequencyranking);

        switch ($frequencyranking) {
            case 0:
                return get_string('occurences:very_rare', self::PLUGIN_NAME);
            case 1:
                return get_string('occurences:rare', self::PLUGIN_NAME);
            case 2:
                return get_string('occurences:moderately', self::PLUGIN_NAME);
            case 3:
                return get_string('occurences:often', self::PLUGIN_NAME);
            case 4:
                return get_string('occurences:very_frequent', self::PLUGIN_NAME);
            default:
                return get_string('occurences:very_frequent', self::PLUGIN_NAME);
        }
    }

    private static function convert_ranking_stars(int $ranking): array {
        $staricons = [];

        $stars = intdiv($ranking, 2);
        for ($i = 1; $i <= $stars; $i++) {
            $staricons[] = 'fa-star';
        }

        if ($ranking % 2 == 1) {
            $staricons[] = 'fa-star-half-full';
            $stars++;
        }

        for ($i = 1; $i <= (5 - $stars); $i++) {
            $staricons[] = 'fa-star-o';
        }

        return $staricons;
    }

    public function export_for_template(?renderer_base $output = null): array {
        global $OUTPUT;
        $rating = $this->activityrating;
        $rankfix = (int)$rating['ranking'] ?? 0;
        $rankfix = max(0, min(10, $rankfix));

        $popularityfix = (int)$rating['popularity'] ?? 0;
        $popularityfix = max(0, min(10, $popularityfix));

        $pluginname = get_string('pluginname', "mod_" . $rating['pluginname']);
        if ($pluginname == "[[pluginname]]") {
            $pluginname = $rating['pluginname'];
        }

        return [
            'id' => $rating['id'],
            'pluginname' => $pluginname,
            'description' => $rating['description'],
            'hint' => $rating['hint'],
            'ranking' => $rankfix,
            'reason' => $rating['reason'],
            'occurences' => $this->get_occurance_string(),
            'stars' => self::convert_ranking_stars($rankfix),
            'activityicon' => $OUTPUT->image_icon('monologo', '', $rating['pluginname']),
        ];
    }
}
