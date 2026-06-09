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

use RuntimeException;

/**
 * No AI backend system selected, plugin is effectively disabled
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2026, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class no_ai implements ai_backend {
    /**
     * Not available, cause no AI selected
     *
     * @return bool Always false
     */
    public function available(): bool {
        return false;
    }

    /**
     * Throws a not implemented error, should never be called
     *
     * @param string $prompttext
     * @param int $contextid
     * @return string
     */
    public function send_request(string $prompttext, int $contextid): string {
        throw new RuntimeException('No Backend AI set');
    }
}
