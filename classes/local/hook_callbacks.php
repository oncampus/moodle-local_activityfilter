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

use context_system;
use core\di;
use core\hook\output\before_html_attributes;
use core\hook\di_configuration;
use core_ai\aiactions\generate_text;
use core_ai\manager;
use core_plugin_manager;
use local_activityfilter\activity_searcher\ai_dummy_searcher;
use local_activityfilter\activity_searcher\content_item_manager;
use local_activityfilter\activity_searcher\activity_summarizer;
use local_activityfilter\activity_searcher\ai_searcher;
use local_activityfilter\activity_searcher\contracts\i_activity_searcher;
use local_activityfilter\activity_searcher\i_activity_summarizer;
use local_activityfilter\activity_searcher\i_text_compressor;
use local_activityfilter\activity_searcher\stopword_remover;
use moodle_database;

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
            id: i_text_compressor::class,
            definition: function (): i_text_compressor {
                return new stopword_remover();
            }
        );

        $hook->add_definition(
            id: i_activity_summarizer::class,
            definition: function (): i_activity_summarizer {
                return new activity_summarizer(
                    new content_item_manager(),
                );
            }
        );

        $hook->add_definition(
            id: i_activity_searcher::class,
            definition: function (
                i_activity_summarizer $summerizer,
                manager $aimanager,
            ): i_activity_searcher {
                if (get_config('local_activityfilter', 'dummy_mode')) {
                    return new ai_dummy_searcher();
                }

                return new ai_searcher(
                    $summerizer,
                    new stopword_remover(),
                    $aimanager
                );
            }
        );
    }

    /**
     * Injects JS to add activity filter to course section menu
     *
     * @param before_html_attributes $hook After config hook
     * @return void
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        if (
            !di::get(manager::class)->is_action_available(generate_text::class)
            && !get_config('local_activityfilter', 'dummy_mode')
        ) {
            return;
        }

        global $PAGE;
        if (
            !has_all_capabilities([
                'local/activityfilter:filter_activities',
                'local/activityfilter:get_max_content_item_occurrence',
            ], $PAGE->context)
        ) {
            return;
        }

        global $PAGE;
        $PAGE->requires->js_call_amd(
            'local_activityfilter/auto_resize_text_field',
            'init'
        );
        $PAGE->requires->js_call_amd(
            'local_activityfilter/content_item_filter_modal',
            'init'
        );
    }
}
