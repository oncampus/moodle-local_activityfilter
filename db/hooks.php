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
 * Hooks are defined here
 *
 * @category    string
 * @copyright   Konrad Ebel <konrad.ebel@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\hook\output\before_html_attributes;
use core\hook\di_configuration;
use local_activityfilter\local\hook_callbacks;

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => di_configuration::class,
        'callback' => [hook_callbacks::class, 'di_configuration'],
    ],
    [
        'hook' => before_html_attributes::class,
        'callback' => [hook_callbacks::class, 'before_html_attributes'],
    ],
    [
        'hook' => \local_ai_manager\hook\purpose_usage::class,
        'callback' => [hook_callbacks::class, 'handle_purpose_usage'],
    ],
];
