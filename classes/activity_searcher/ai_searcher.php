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

use context_system;
use core\exception\moodle_exception;
use dml_exception;
use local_activityfilter\activity_searcher\backend\ai_backend;
use local_activityfilter\activity_searcher\contracts\content_item_info;
use local_activityfilter\activity_searcher\contracts\activity_ranking;
use local_activityfilter\activity_searcher\contracts\i_activity_searcher;

/**
 * Filter activities
 *
 * @copyright   2025 Team 13 <team13@mailbox.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_searcher implements i_activity_searcher {
    /**
     * Constructor.
     *
     * @param i_content_item_summarizer $summerizer Activity summarizer
     * @param i_text_compressor $compressor Text compressor
     * @param ai_backend $aimanager AI Manager
     */
    public function __construct(
        /** @var i_content_item_summarizer Activity summarizer */
        private readonly i_content_item_summarizer $summerizer,
        /** @var i_text_compressor Text compressor */
        private readonly i_text_compressor $compressor,
        /** @var ai_backend AI Manager */
        private readonly ai_backend $aimanager,
    ) {
    }

    /**
     * Searches for activities fitting to the given user request.
     * It will return an array of activity rankings.
     *
     * @param string $request User request.
     * @param int|null $contextid The context ID for the AI request.
     * @return activity_ranking[] List of activity rankings.
     * @throws dml_exception|invalid_ai_response|moodle_exception
     */
    public function filter_activities(string $request, ?int $contextid = null): array {
        $prompttext = $this->build_prompt_text($request);
        $response = $this->aimanager->send_request($prompttext, $contextid ?? context_system::instance()->id);

        $json = $this->convert_ai_response_from_json($response);
        if ($json === false) {
            throw new invalid_ai_response("Invalid AI feedback: " . $response);
        }

        $ratings = $this->convert_data_to_ranking($json);
        if ($ratings === false) {
            throw new invalid_ai_response("Invalid AI feedback: " . $response);
        }

        return $ratings;
    }

    /**
     * Builds the prompt text from user request and activity data.
     *
     * @param string $userrequest User request.
     * @return string The prompt text.
     * @throws dml_exception
     */
    public function build_prompt_text(string $userrequest): string {
        $contentiteminfos = $this->summerizer->get_content_item_infos();
        $contentiteminfos = json_encode($contentiteminfos, JSON_UNESCAPED_UNICODE);
        $contentiteminfos = preg_replace('/<[^>]*>/', '', $contentiteminfos);
        $contentiteminfos = str_replace("\/innen", "", $contentiteminfos);
        $contentiteminfos = str_replace("\/", "/", $contentiteminfos);
        $contentiteminfos = str_replace('},{', "\n", $contentiteminfos);

        return get_config('local_activityfilter', 'systemprompt') .
            'Plugin descriptions ' . $contentiteminfos . "\n" .
            'User request: ' . $this->compressor->compress($userrequest);
    }

    /**
     * Convert AI Response to an json
     *
     * @param string $jsontext AI Response text
     * @return array|false Converted json object or false if not convertable
     */
    public function convert_ai_response_from_json(string $jsontext): array|false {
        $directdecode = json_decode($jsontext, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $directdecode;
        }

        // phpcs:ignore moodle.Strings.ForbiddenStrings.Found -- Parse Markdown JSON code fences from AI responses.
        $startneedle = '```json';
        $start = strpos($jsontext, $startneedle);
        if ($start === false) {
            return false;
        }
        $start += strlen($startneedle);

        // phpcs:ignore moodle.Strings.ForbiddenStrings.Found -- Parse Markdown JSON code fences from AI responses.
        $end = strpos($jsontext, "```", $start);
        if ($end === false) {
            return false;
        }

        $json = trim(substr($jsontext, $start, $end - $start));

        $data = json_decode($json, true);
        return is_array($data) ? $data : false;
    }

    /**
     * Convert json object to ranking data
     *
     * @param mixed $json Array object or other data converted from json
     * @return activity_ranking[]|false Converted AI Response as rankings
     */
    public function convert_data_to_ranking(mixed $json): array|false {
        $data = [];
        foreach ($json as $rankingdata) {
            $pluginname = $rankingdata["pluginname"] ?? false;
            if (!$pluginname) {
                continue;
            }

            if (!$activity = $this->summerizer->get_content_item_info($pluginname)) {
                continue;
            }

            $data[] = activity_ranking::create(
                $pluginname,
                $rankingdata['reason'] ?? '',
                $rankingdata['hint'] ?? '',
                intval($rankingdata['ranking']),
                $activity
            );
        }

        return $data;
    }
}
