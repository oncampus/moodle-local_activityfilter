<?php

namespace local_activityfilter\activity_searcher\backend;

use RuntimeException;

class no_ai implements ai_backend {

    public function available(): bool {
        return false;
    }

    public function send_request(string $prompttext, int $contextid): string {
        throw new RuntimeException('No Backend AI set');
    }
}
