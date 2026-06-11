// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

import {call as fetchMany} from 'core/ajax';
import {getString} from 'core/str';
import Templates from 'core/templates';

export const LanguageStrings = {
    ModalHeader: 'modal_title',
    OpenButtonText: 'open_activityfilter',
    OccurrenceVeryRare: 'occurences:very_rare',
    OccurrenceRare: 'occurences:rare',
    OccurrenceModerately: 'occurences:moderately',
    OccurrenceOften: 'occurences:often',
    OccurrenceVeryFrequent: 'occurences:very_frequent'
};

export const getLanguageString = (key) => getString(
    key,
    'local_activityfilter'
);

const success = (data) => ({
    ok: true,
    data,
    error: null,
});

const failure = (error) => ({
    ok: false,
    data: null,
    error: error && error.message ? error.message : '',
});

/**
 * Sanitize a prompt string before sending it to the server.
 *
 * Trims whitespace, collapses repeated whitespace/newlines into single
 * spaces, and strips control characters that could cause PARAM_TEXT
 * validation to fail.
 *
 * @param {string} text The raw input text.
 * @returns {string} The sanitized text.
 */
function sanitizePrompt(text) {
    if (typeof text !== 'string') {
        return '';
    }

    return text
        // Strip HTML tags.
        .replace(/<[^>]*>/g, '')
        // Collapse all whitespace (including newlines/tabs) into single spaces.
        .replace(/\s+/g, ' ')
        .trim();
}

/**
 * Calls content item ranking by ajax
 *
 * @param {string} prompt User prompt
 * @returns {Promise<{data: *, ok: boolean, error: *}>} List of ratings data in response
 */
export async function fetchContentItemsRanking(prompt) {
    const courseId = getCourseId();

    try {
        const result = await fetchMany([{
            methodname: 'local_activityfilter_filter_activities',
            args: {courseid: courseId, prompt: sanitizePrompt(prompt)}
        }])[0];
        return success(result);
    } catch (error) {
        return failure(error);
    }
}

/**
 * Calls max content item occurrence
 *
 * @returns {Promise<{data: *, ok: boolean, error: *}>} Response with max content item occurrence
 */
export async function fetchMaxContentItemOccurrence() {
    const courseId = getCourseId();

    try {
        const result = await fetchMany([{
            methodname: 'local_activityfilter_get_max_content_item_occurrence',
            args: {courseid: courseId}
        }])[0];
        return success(result);
    } catch (error) {
        return failure(error);
    }
}

/**
 * Render the activity filter modal content.
 *
 * @returns {Promise<string>} The rendered HTML for the activity filter modal.
 */
export function renderContentItemModal() {
    return Templates.render(
        'local_activityfilter/activityfilter_modal',
        []
    );
}

/**
 * Renders an error into the element
 *
 * @param {HTMLElement} targetElement Target
 * @param {string} errorMessage Message of the error
 * @returns {Promise<void>}
 */
export async function renderAIError(targetElement, errorMessage) {
    const result = await Templates.renderForPromise(
        'local_activityfilter/ai_response_error',
        {'error_msg': errorMessage}
    );
    Templates.replaceNodeContents(targetElement, result.html, result.js);
}

/**
 * Renders the content item ranking in body of this element
 *
 * @param {HTMLElement} targetElement Target
 * @param {Array} templateData Data for rendering
 * @returns {Promise<void>}
 */
export async function renderContentItemRankingListOn(targetElement, templateData) {
    const result = await Templates.renderForPromise(
        'local_activityfilter/content_item_rating_list',
        templateData
    );
    Templates.replaceNodeContents(targetElement, result.html, result.js);
}

/**
 * Find course id from body tag
 *
 * @returns {number} course id
 */
function getCourseId() {
    const classes = document.body.className.split(' ');

    for (const cls of classes) {
        if (cls.startsWith('course-')) {
            const id = parseInt(cls.replace('course-', ''), 10);
            if (!Number.isNaN(id)) {
                return id;
            }
        }
    }

    throw new Error("Course ID not found");
}
