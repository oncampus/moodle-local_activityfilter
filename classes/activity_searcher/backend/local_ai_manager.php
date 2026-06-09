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

use core\di;
use core\exception\moodle_exception;
use local_ai_manager\local\tenant;
use local_ai_manager\manager;

/**
 * Mebis AI System
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2026, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_ai_manager implements ai_backend {
    /**
     * Only available and configured, if tenant is allowed
     * and singleprompt is configured
     *
     * @return bool Tenant allowed and singleprompt is configured
     */
    public function available(): bool {
        if (!class_exists('\local_ai_manager\local\tenant')) {
            return false;
        }

        $tenant = di::get(tenant::class);
        if (!$tenant->is_tenant_allowed()) {
            return false;
        }

        try {
            new manager('singleprompt');
        } catch (moodle_exception) {
            return false;
        }

        return true;
    }

    /**
     * Sends the request via local_ai_manager.
     *
     * @param string $prompttext The prompt text.
     * @param int $contextid The context ID.
     * @return string AI Response.
     * @throws moodle_exception
     */
    public function send_request(string $prompttext, int $contextid): string {
        $manager = new manager('singleprompt');
        $response = $manager->perform_request($prompttext, 'local_activityfilter', $contextid);
        if ($response->get_code() !== 200) {
            throw new moodle_exception(
                'error:ai_call',
                'local_activityfilter',
                '',
                $response->get_errormessage(),
                $response->get_debuginfo()
            );
        }
        // The ai_manager may wrap content in HTML tags (e.g. <p>...</p>), strip them for raw JSON.
        return trim(strip_tags($response->get_content()));
    }
}
