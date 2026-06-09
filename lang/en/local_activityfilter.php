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
 * Plugin strings are defined here.
 *
 * @category    string
 * @copyright   2025 Team 13 <team13@mailbox.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityfilter:filter_activities'] = 'Can filter content items with AI for the most useful for a use case';
$string['activityfilter:get_max_content_item_occurrence'] = 'Can fetch the occurrences (count) of most used content items';

$string['error:ai_call'] = 'The AI request failed. Please try again later.';

$string['hintcolumn'] = 'Description';
$string['modal_title'] = 'Suggested Activities';
$string['noresults'] = 'No results yet - please enter a prompt to begin.';

$string['occurences:moderately'] = 'moderately';
$string['occurences:often'] = 'often';
$string['occurences:rare'] = 'rare';
$string['occurences:very_frequent'] = 'very frequent';
$string['occurences:very_rare'] = 'very rare';

$string['open_activityfilter'] = 'Open AI activity search';
$string['pluginname'] = 'Activity Filter';
$string['popularitycolumn'] = 'Usage';
$string['privacy:null_reason'] = 'The plugin do not safe any user data, but sends data to the ai provider';
$string['promptdesc'] = 'What kind of activity would you like to find?';
$string['purposeplacedescription_singleprompt'] = 'Used to query the AI for activity recommendations based on user input.';
$string['reasoncolumn'] = 'Reason';
$string['search'] = 'Search';

$string['settings:backend'] = 'AI backend';
$string['settings:backend_desc'] = 'Select which AI backend to use for activity filtering.';
$string['settings:disabled'] = 'Disabled';
$string['settings:backend_coreai'] = 'Moodle core AI subsystem';
$string['settings:backend_dummy_mode'] = 'Dummy mode';
$string['settings:backend_localaimanager'] = 'local_ai_manager (mebis)';
$string['settings:plugin_ai_hint'] = 'Guidance text that informs the AI about the plugin\'s features and behaviour.';
$string['settings:systemprompt'] = 'System prompt';
$string['settings:systemprompt_desc'] = 'Defines the system prompt used to guide the behaviour and responses of the AI model.';
$string['settings:use_default'] = 'Use default ({$a})';
$string['settings:use_default_desc'] = 'Use default setting for the plugin {$a}';
