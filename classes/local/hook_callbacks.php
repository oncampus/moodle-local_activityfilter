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

namespace local_activityfilter\local;

use context_course;
use core\di;
use core\hook\output\before_html_attributes;
use core\hook\di_configuration;
use core_ai\manager;
use local_activityfilter\activity_searcher\backend\ai_backend;
use local_activityfilter\activity_searcher\backend\core_ai;
use local_activityfilter\activity_searcher\backend\demo_ai;
use local_activityfilter\activity_searcher\backend\local_ai_manager;
use local_activityfilter\activity_searcher\backend\no_ai;
use local_activityfilter\activity_searcher\content_item_manager;
use local_activityfilter\activity_searcher\content_item_summarizer;
use local_activityfilter\activity_searcher\ai_searcher;
use local_activityfilter\activity_searcher\contracts\i_activity_searcher;
use local_activityfilter\activity_searcher\i_content_item_summarizer;
use local_activityfilter\activity_searcher\i_text_compressor;
use local_activityfilter\activity_searcher\stopword_remover;
use local_ai_manager\hook\purpose_usage;

/**
 * Hook Callback definitions.
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Dependency injection configuration method
     *
     * @param di_configuration $hook DI Instance
     * @return void
     */
    public static function di_configuration(di_configuration $hook): void {
        $hook->add_definition(
            id: ai_backend::class,
            definition: function (
                manager $coreaimanager
            ): ai_backend {
                $backend = get_config('local_activityfilter', 'backend');

                return match ($backend) {
                    'dummy_mode' => new demo_ai(),
                    'local_ai_manager' => new local_ai_manager(),
                    'core_ai_subsystem' => new core_ai($coreaimanager),
                    default => new no_ai(),
                };
            }
        );

        $hook->add_definition(
            id: i_text_compressor::class,
            definition: function (): i_text_compressor {
                return new stopword_remover();
            }
        );

        $hook->add_definition(
            id: i_content_item_summarizer::class,
            definition: function (): i_content_item_summarizer {
                return new content_item_summarizer(
                    new content_item_manager(),
                );
            }
        );

        $hook->add_definition(
            id: i_activity_searcher::class,
            definition: function (
                i_content_item_summarizer $summerizer,
                ai_backend $aimanager,
            ): i_activity_searcher {
                return new ai_searcher(
                    $summerizer,
                    new stopword_remover(),
                    $aimanager
                );
            }
        );
    }

    /**
     * Injects JS to add activity filter to course section menu.
     *
     * @param before_html_attributes $hook After config hook.
     * @return void
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        global $PAGE;
        if (!$PAGE->context instanceof context_course) {
            return;
        }

        $aibackend = di::get(ai_backend::class);
        if (!$aibackend->available()) {
            return;
        }

        if (
            !has_all_capabilities([
                'local/activityfilter:filter_activities',
                'local/activityfilter:get_max_content_item_occurrence',
            ], $PAGE->context)
        ) {
            return;
        }

        $PAGE->requires->js_call_amd(
            'local_activityfilter/auto_resize_text_field',
            'init'
        );
        $PAGE->requires->js_call_amd(
            'local_activityfilter/content_item_filter_modal',
            'init',
            [
                'check_core_policy' => $aibackend instanceof core_ai,
            ]
        );
    }

    /**
     * Provide additional information about which purposes are being used by this plugin.
     *
     * @param purpose_usage $hook The purpose_usage hook object.
     */
    public static function handle_purpose_usage(purpose_usage $hook): void {
        $hook->set_component_displayname(
            'local_activityfilter',
            get_string('pluginname', 'local_activityfilter')
        );
        $hook->add_purpose_usage_description(
            'singleprompt',
            'local_activityfilter',
            get_string('purposeplacedescription_singleprompt', 'local_activityfilter')
        );
    }
}
