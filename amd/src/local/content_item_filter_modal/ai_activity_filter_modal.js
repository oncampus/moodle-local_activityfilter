import Selectors from "./selectors";
import {
    fetchContentItemsRanking,
    getLanguageString,
    LanguageStrings,
    renderContentItemModal,
} from "./repository";
import ContentItemRankingList from "./content_item_ranking_list";
import Modal from "core/modal";

export default class AIActivityFilterModal {
    /**
     * @param {Modal} modal
     */
    constructor(modal) {
        const modalRoot = modal.getRoot()[0];

        this.resultArea = this.getElement(Selectors.OUTPUTS.AI_RESULTS, modalRoot);
        this.searchPromptTextArea = this.getElement(Selectors.INPUTS.SEARCH_PROMPT, modalRoot);
        this.searchButton = this.getElement(Selectors.ACTIONS.SEARCH, modalRoot);
        this.searchIconLabel = this.getElement(Selectors.SearchIcon, this.searchButton);
        this.spinner = this.getElement(Selectors.OUTPUTS.LOADING_SPINNER, this.searchButton);

        this.registerEventListeners();
    }

    /**
     * Opens the activity filter modal.
     *
     * @returns {Promise<AIActivityFilterModal>}
     */
    static async create() {
        const modal = await Modal.create({
            title: await getLanguageString(LanguageStrings.ModalHeader),
            body: await renderContentItemModal(),
            footer: "",
        });

        modal.removeOnClose = true;
        await modal.show();

        return new AIActivityFilterModal(modal);
    }

    /**
     * Get user input in search prompt.
     *
     * @returns {string} Search prompt input
     */
    getSearchPrompt() {
        return (this.searchPromptTextArea.value || "").trim();
    }

    /**
     * Runs the search.
     *
     * @returns {Promise<void>}
     */
    async search() {
        this.setLoading(true);

        try {
            await this.getSearchResult();
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Fetches and renders the search results.
     *
     * @returns {Promise<void>}
     */
    async getSearchResult() {
        const prompt = this.getSearchPrompt();
        const response = await fetchContentItemsRanking(prompt);

        if (!response.ok) {
            window.console.error("Error while fetching content items rankings", response.error);

            const errorText = await getLanguageString(LanguageStrings.ErrorAICall);

            this.resultArea.innerHTML = `
                <div class="alert alert-danger">
                    ${errorText} - ${response.error}
                </div>
            `;

            return;
        }

        const rankingList = ContentItemRankingList.createFromRaw(response.data);
        await rankingList.render(this.resultArea);
    }

    /**
     * Sets the button to loading / ready.
     *
     * @param {boolean} loading Set to loading / ready
     */
    setLoading(loading) {
        this.searchButton.disabled = loading;
        this.searchButton.classList.toggle("disabled", loading);
        this.searchIconLabel.classList.toggle("d-none", loading);
        this.spinner.classList.toggle("d-none", !loading);
    }

    /**
     * Registers event listeners.
     */
    registerEventListeners() {
        this.searchButton.addEventListener("click", async() => {
            await this.search();
        });

        this.searchPromptTextArea.addEventListener("keydown", async(e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                await this.search();
            }
        });
    }

    /**
     * Finds an element or throws an error.
     *
     * @param {string} selector
     * @param {Element} root
     * @returns {Element}
     */
    getElement(selector, root) {
        const element = root.querySelector(selector);

        if (!element) {
            throw new Error(`Could not find element ${selector}`);
        }

        return element;
    }
}
