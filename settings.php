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

/**
 * Plugin administration pages are defined here.
 *
 * @copyright   Team 13 MoodleMoot
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\di;
use local_activityfilter\activity_searcher\i_content_item_summarizer;
use local_activityfilter\local\overwritten_content_item_description;

defined('MOODLE_INTERNAL') || die();

if (!$hassiteconfig) {
    return;
}

$settings = new admin_settingpage(
    'local_activityfilter',
    new lang_string('pluginname', 'local_activityfilter')
);
$ADMIN->add("localplugins", $settings);

if (!$ADMIN->fulltree) {
    return;
}

$settings->add(
    new admin_setting_configtextarea(
        'local_activityfilter/systemprompt',
        get_string('settings:systemprompt', 'local_activityfilter'),
        get_string('settings:systemprompt_desc', 'local_activityfilter'),
        (
        'You are an assistant that analyses a user query and selects the most relevant activities.

Task: Evaluate the query and return the best-matching activities.

Output format: A JSON array (string) where each object contains (dont give any comments):
[{
"pluginname": "Name without mod_",
"ranking": 1–10,
"hint": "Short usage summary",
"reason": "Why this plugin fits the query"
}, ...]'
        ),
        PARAM_TEXT
    )
);

$settings->add(
    new admin_setting_configselect(
        'local_activityfilter/backend',
        get_string('settings:backend', 'local_activityfilter'),
        get_string('settings:backend_desc', 'local_activityfilter'),
        'core_ai_subsystem',
        [
            'disabled' => get_string('settings:disabled', 'local_activityfilter'),
            'dummy_mode' => get_string('settings:backend_dummy_mode', 'local_activityfilter'),
            'core_ai_subsystem' => get_string('settings:backend_coreai', 'local_activityfilter'),
            'local_ai_manager' => get_string('settings:backend_localaimanager', 'local_activityfilter'),
        ]
    )
);

$activitysummerizer = di::get(i_content_item_summarizer::class);
$activities = $activitysummerizer->get_activities();

foreach ($activities as $activity) {
    $itemid = $activity->get_name();
    $displayname = $activity->get_title()->get_value();

    $settings->add(
        new admin_setting_configcheckbox(
            "local_activityfilter/ai_hint_{$itemid}_use_default",
            get_string('settings:use_default', 'local_activityfilter', $displayname),
            get_string('settings:use_default_desc', 'local_activityfilter', $displayname),
            true
        )
    );

    $defaulthelp = overwritten_content_item_description::get_overwritten_default($itemid);
    if (!$defaulthelp) {
        $defaulthelp = overwritten_content_item_description::clean_core_help($activity->get_help());
    }

    $settings->add(
        new admin_setting_configtextarea(
            "local_activityfilter/ai_hint_{$itemid}",
            $displayname,
            get_string('settings:plugin_ai_hint', 'local_activityfilter'),
            $defaulthelp,
        )
    );
}
