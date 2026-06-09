<?php

namespace local_activityfilter\activity_searcher\backend;

class demo_ai implements ai_backend {
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
