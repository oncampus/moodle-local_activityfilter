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

import Selectors from "./local/content_item_filter_modal/selectors";
import {
    getLanguageString,
    LanguageStrings,
} from './local/content_item_filter_modal/repository';
import AIActivityFilterModal from "./local/content_item_filter_modal/ai_activity_filter_modal";
import PolicyPlacement from './local/content_item_filter_modal/policy_placement';

/**
 * Initializes the activity filter in the activity add menu.
 *
 * @param {boolean} checkCorePolicy Whether to check the core usage policy
 * @returns {Promise<void>}
 */
export async function init(checkCorePolicy) {
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
        button.addEventListener('click', async() => {
            if (checkCorePolicy) {
                const policy = new PolicyPlacement();
                if (!await policy.isPolicyAccepted()) {
                    await policy.displayPolicy(AIActivityFilterModal.create);
                    return;
                }
            }

            await AIActivityFilterModal.create();
        });

        if (newContentDropdown.children.length >= 1) {
            newContentDropdown.insertBefore(button, newContentDropdown.children[1]);
        } else {
            newContentDropdown.append(button);
        }
    });
}
