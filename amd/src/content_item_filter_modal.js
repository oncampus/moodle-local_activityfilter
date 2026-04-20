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

import ContentItemRankingList from "./local/content_item_filter_modal/content_item_ranking_list";
import Selectors from "./local/content_item_filter_modal/selectors";
import {
    fetchContentItemsRanking,
    getLanguageString,
    LanguageStrings,
    renderContentItemModal
} from './local/content_item_filter_modal/repository';
import Modal from 'core/modal';

/**
 * Get user input in search prompt
 *
 * @param {Element} modalRoot Root of modal
 * @returns {string} Search prompt input
 */
function getSearchPrompt(modalRoot) {
    const searchPrompt = modalRoot.querySelector(Selectors.searchPrompt);
    if (!searchPrompt) {
        window.console.log(`Search prompt ${Selectors.searchPrompt} not found`);
        return '';
    }
    return (searchPrompt.value || '').trim();
}

/**
 *
 * @param {Element} modalRoot
 * @returns {Promise<void>}
 */
async function search(modalRoot) {
    const results = modalRoot.querySelector(Selectors.resultArea);
    if (!results) {
        window.console.error(`Result area ${Selectors.resultArea} could not be found.`);
        return;
    }

    const prompt = getSearchPrompt(modalRoot);
    const response = await fetchContentItemsRanking(prompt);
    if (!response.ok) {
        window.console.error("Error while fetching content items rankings", response.error);
        const errorText = await getLanguageString(LanguageStrings.ErrorAICall);
        results.innerHTML = `
        <div class="alert alert-danger">
            ${errorText}
        </div>`;
        return;
    }

    const rankingList = ContentItemRankingList.createFromRaw(response.data);
    await rankingList.render(results);
}

/**
 * Sets the button to loading / ready
 *
 * @param {HTMLElement} button Prompt submit button
 * @param {boolean} loading Set to loading / ready
 */
function setLoading(button, loading) {
    const label = button.querySelector(Selectors.SearchIcon);
    const spinner = button.querySelector(Selectors.LoadingSpinner);

    button.disabled = loading;
    button.classList.toggle('disabled', loading);
    label.classList.toggle('d-none', loading);
    spinner.classList.toggle('d-none', !loading);
}

/**
 * Opens the activity filter module
 *
 * @returns {Promise<void>}
 */
async function openActivityFilter() {
    const modal = await Modal.create({
        title: getLanguageString(LanguageStrings.ModalHeader),
        body: await renderContentItemModal(),
        footer: '',
    });
    await modal.show();

    const modalRoot = modal.getRoot()[0];
    const searchButton = modalRoot.querySelector(Selectors.searchButton);
    if (!searchButton) {
        window.console.error(`Search button ${Selectors.searchButton} not found`);
        return;
    }

    const doSearch = async() => {
        setLoading(searchButton, true);
        await search(modalRoot);
        setLoading(searchButton, false);
    };

    searchButton.addEventListener('click', doSearch);

    const searchPrompt = modalRoot.querySelector(Selectors.searchPrompt);
    if (searchPrompt) {
        searchPrompt.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                doSearch();
            }
        });
    }
}

/**
 * Initializes the activity filter in the activity add menu
 *
 * @returns {Promise<void>}
 */
export async function init() {
    const newContentDropdowns = document.querySelectorAll(Selectors.newContentDropdown);
    let openButtonText = await getLanguageString(LanguageStrings.OpenButtonText);

    newContentDropdowns.forEach(newContentDropdown => {
        const icon = document.createElement('i');
        icon.classList.add('icon', 'fa-solid', 'fa-search');

        const text = document.createTextNode(openButtonText);

        const button = document.createElement('button');
        button.classList.add('dropdown-item', 'open-activityfilter');
        button.append(icon);
        button.append(text);
        button.addEventListener('click', openActivityFilter);

        if (newContentDropdown.children.length >= 1) {
            newContentDropdown.insertBefore(button, newContentDropdown.children[1]);
        } else {
            newContentDropdown.append(button);
        }
    });
}
