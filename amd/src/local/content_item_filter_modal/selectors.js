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

const Selectors = {
    INPUTS: {
        SEARCH_PROMPT: '#activitysearchprompt',
    },
    OUTPUTS: {
        LOADING_SPINNER: '.spinner-border',
        AI_RESULTS: '[data-region="activityfilter-results"]',
    },
    newContentDropdown: ".course-content .course-section .divider .dropdown-menu",
    SearchIcon: '.icon',
    ACTIONS: {
        SEARCH: '[data-action="activityfilter-search"]',
        ACCEPT: '.ai-policy-block [data-action="accept"]',
    }
};

export default Selectors;
