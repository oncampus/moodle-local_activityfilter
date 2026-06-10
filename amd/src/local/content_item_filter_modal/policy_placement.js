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

/**
 * Handles displaying and accepting the AI policy before opening the AI modal.
 *
 * @module     local_yourplugin/policy_placement
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import Notification from 'core/notification';
import Policy from 'core_ai/policy';
import Selectors from './selectors';
import Modal from 'core/modal';

/**
 * Manages the AI policy placement flow.
 *
 * The policy is shown in a modal when required. After the user accepts or
 * continues from the policy block, the provided callback can open the AI modal.
 */
export default class PolicyPlacement {
    /**
     * Checks whether the current user has already accepted the AI policy.
     *
     * @returns {Promise<boolean>} Resolves to true when the policy has been accepted.
     */
    async isPolicyAccepted() {
        return await Policy.getPolicyStatus(M.cfg.userId);
    }

    /**
     * Accepts the AI policy for the current user.
     *
     * @returns {Promise<Object>} Result returned by the Moodle AI policy API.
     */
    acceptPolicy() {
        return Policy.acceptPolicy();
    }

    /**
     * Displays the AI policy modal and registers its event listeners.
     *
     * @param {Function} openAIModal Callback used to open the AI modal after the policy action.
     * @returns {Promise<void>}
     */
    async displayPolicy(openAIModal) {
        const modal = await Modal.create({
            title: '',
            body: '',
            footer: '',
        });
        modal.removeOnClose = true;

        Templates.render('core_ai/policyblock', {})
            .then((html) => {
                modal.getRoot()[0].innerHTML = html;
                this.registerPolicyEventListeners(openAIModal);
            })
            .catch(Notification.exception);

        await modal.show();
    }

    /**
     * Registers click handlers for actions inside the policy modal.
     *
     * @param {Function} openAIModal Callback used to open the AI modal after the policy action.
     */
    registerPolicyEventListeners(openAIModal) {
        const acceptAction = document.querySelector(Selectors.ACTIONS.ACCEPT);

        if (acceptAction) {
            acceptAction.addEventListener('click', () => {
                openAIModal();
            });
        }
    }
}
