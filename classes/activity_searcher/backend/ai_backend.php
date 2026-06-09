<?php

namespace local_activityfilter\activity_searcher\backend;

interface ai_backend {
    public function available(): bool;
    public function send_request(string $prompttext, int $contextid): string;
}
