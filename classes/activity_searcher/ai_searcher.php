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
use core_ai\aiactions\generate_text;
use core_ai\manager;
use dml_exception;
use Exception;
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
     * @param i_activity_summarizer $summerizer Activity summarizer
     * @param i_text_compressor $compressor Text compressor
     * @param manager $aimanager AI Manager
     */
    public function __construct(
        /** @var i_activity_summarizer Activity summarizer */
        private readonly i_activity_summarizer $summerizer,
        /** @var i_text_compressor Text compressor */
        private readonly i_text_compressor $compressor,
        /** @var manager AI Manager */
        private readonly manager $aimanager,
    ) {
    }

    /**
     * Searches for activities fitting to the given user request
     * It will return an array of activity rankings
     *
     * @param string $request User request
     * @return activity_ranking[] List of activity rankings
     * @throws dml_exception
     * @throws invalid_ai_response
     */
    public function filter_activities(string $request): array {
        $activitysummary = $this->summerizer->get_activity_data();
        $prompt = self::prepare_prompt($request, $activitysummary);
        $response = self::send_request($prompt);

        $json = $this->convert_ai_response_to_json($response);
        if ($json === false) {
            throw new invalid_ai_response("Invalid AI feedback: " . $response);
        }

        $ratings = $this->convert_json_to_ranking($json, $activitysummary);
        if ($ratings === false) {
            throw new invalid_ai_response("Invalid AI feedback: " . $response);
        }

        return $ratings;
    }

    /**
     * Prepares the complete AI Request
     *
     * @param string $userrequest User request
     * @param activity_data[] $activitydata List of activity data
     * @return generate_text Generate text task
     * @throws dml_exception
     */
    public function prepare_prompt(string $userrequest, array $activitydata): generate_text {
        global $USER;
        $plugindescription = json_encode($activitydata, JSON_UNESCAPED_UNICODE);
        $plugindescription = preg_replace('/<[^>]*>/', '', $plugindescription);
        $plugindescription = str_replace("\/innen", "", $plugindescription);
        $plugindescription = str_replace("\/", "/", $plugindescription);
        $plugindescription = str_replace('},{', "\n", $plugindescription);

        $promptmsg = (
            get_config('local_activityfilter', 'systemprompt') .
            'Plugin descriptions ' . $plugindescription . "\n" .
            'User request: ' . $this->compressor->compress($userrequest)
        );

        return new generate_text(
            context_system::instance()->id,
            $USER->id,
            $promptmsg
        );
    }

    /**
     * Sends the request to the AI Manager
     *
     * @param generate_text $prompts Generate text request
     * @return string AI Response
     * @throws Exception
     */
    public function send_request(generate_text $prompts): string {
        $response = $this->aimanager->process_action($prompts);
        if (!$response->get_success()) {
            throw new Exception($response->get_errormessage());
        }

        return $response->get_response_data()['generatedcontent'];
    }

    /**
     * Convert AI Response to an json
     *
     * @param string $text AI Response text
     * @return array|false Converted json object or false if not convertable
     */
    public function convert_ai_response_to_json(string $text): array|false {
        $directdecode = json_decode($text, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $directdecode;
        }

        $start = strpos($text, "```json");
        if ($start === false) {
            return false;
        }
        $start += strlen("```json");

        $end = strpos($text, "```", $start);
        if ($end === false) {
            return false;
        }

        $json = trim(substr($text, $start, $end - $start));

        $data = json_decode($json, true);
        return is_array($data) ? $data : false;
    }

    /**
     * Convert json object to ranking data
     *
     * @param mixed $json Array object or other data converted from json
     * @param activity_data[] $activitydata Unconverted activity data
     * @return activity_ranking[]|false Converted AI Response as rankings
     */
    public function convert_json_to_ranking(mixed $json, array $activitydata): array|false {
        $data = [];
        foreach ($json as $rankingdata) {
            $pluginname = $rankingdata["pluginname"] ?? false;
            if (!$pluginname) {
                continue;
            }

            $activity = $this->find_activity($rankingdata["pluginname"], $activitydata);
            if (!$activity) {
                continue;
            }

            $rankingdata['occurrences'] = $activity->get_usage_amount();
            $rankingdata['logohtml'] = $activity->get_logo_html();
            $rankingdata['title'] = $activity->get_title();
            $data[] = activity_ranking::from_stdclass((object)$rankingdata);
        }

        return $data;
    }

    /**
     * Searches if plugin exist in activity data
     *
     * @param string $pluginname Activity name
     * @param activity_data[] $activitydata List of all choose able activities
     * @return activity_data|false If plugin name is not found in given activity data
     */
    public function find_activity(string $pluginname, array $activitydata): activity_data|false {
        foreach ($activitydata as $activity) {
            if ($activity->get_name() == $pluginname) {
                return $activity;
            }
        }

        return false;
    }
}
