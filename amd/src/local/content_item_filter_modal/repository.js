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
    ErrorAICall: 'error:ai_call',
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
            args: {courseid: courseId, prompt: prompt}
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

export const renderContentItemModal = () =>
    Templates.render(
        'local_activityfilter/activityfilter_modal',
        []
    );

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
