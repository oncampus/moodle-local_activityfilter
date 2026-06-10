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

namespace local_activityfilter\activity_searcher\backend;

use moodle_url;
use core\context;

/**
 * Dummy, that always returns the same answer
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2026, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class demo_ai implements ai_backend {
    /**
     * Cannot be disabled or be unconfigured
     *
     * @return bool Always true
     */
    public function available(): bool {
        return true;
    }

    /**
     * Return dummy reply
     *
     * @param string $prompttext Ignored request
     * @param int $contextid Ignored context ID.
     * @return string Stale dummy reply for activity search
     */
    public function send_request(string $prompttext, int $contextid = 0): string {
        return file_get_contents(__DIR__ . '/dummydata.json');
    }
}
