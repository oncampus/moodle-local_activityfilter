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

use core\exception\moodle_exception;
use Exception;

/**
 * AI Backend for activity_searcher plugin
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2026, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface ai_backend {
    /**
     * Backend is available and configured
     *
     * @return bool True if available and configured
     */
    public function available(): bool;

    /**
     * Send singleprompt to AI and receive answer as string
     *
     * @param string $prompttext Prompt (User + Systemprompt)
     * @param int $contextid Context, where AI is called
     * @return string Response of the AI
     * @throws moodle_exception|Exception AI Call failed
     */
    public function send_request(string $prompttext, int $contextid): string;
}
