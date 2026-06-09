<?php

namespace local_activityfilter\activity_searcher\backend;

use core_ai\aiactions\generate_text;
use core_ai\manager;
use Exception;

class core_ai implements ai_backend {
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