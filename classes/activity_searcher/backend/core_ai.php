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

use core_ai\aiactions\generate_text;
use core_ai\manager;
use Exception;

/**
 * Moodle core AI subsystem
 */
class core_ai implements ai_backend {
    /**
     * Constructor
     *
     * @param manager $manager Moodle AI subsystem manager
     */
    public function __construct(
        private manager $manager,
    ) {
    }

    public function available(): bool {
        return $this->manager->is_action_available(generate_text::class);
    }

    /**
     * Sends the request via core_ai subsystem.
     *
     * @param string $prompttext The prompt text.
     * @param int $contextid The context ID.
     * @return string AI Response.
     * @throws Exception
     */
    public function send_request(string $prompttext, int $contextid): string {
        global $USER;
        $action = new generate_text(
            $contextid,
            $USER->id,
            $prompttext
        );

        $response = $this->manager->process_action($action);
        if (!$response->get_success()) {
            throw new Exception($response->get_errormessage());
        }

        return $response->get_response_data()['generatedcontent'];
    }
}