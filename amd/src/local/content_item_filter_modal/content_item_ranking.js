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

import {getLanguageString, LanguageStrings} from './repository';

/**
 * Ranking item for a content item
 */
export default class ContentItemRanking {
    /**
     * Constructor
     *
     * @param {number} id ID of the ranking object
     * @param {string} pluginname Name of the plugin
     * @param {string} title Human-readable name of plugin
     * @param {string} reason Reason to use it for the use case
     * @param {string} hint Hint how to use it for the use case
     * @param {number} occurrences How often it occurs
     * @param {number} ranking Ranking rating from 1-10
     * @param {string} logohtml HTML of Logo
     */
    constructor(
        id,
        pluginname,
        title,
        reason,
        hint,
        occurrences,
        ranking,
        logohtml
    ) {
        window.console.assert(occurrences >= 0, `Occurrence ${this.occurrences} is smaller than zero`);
        this.id = id;
        this.pluginname = pluginname;
        this.title = title;
        this.reason = reason;
        this.hint = hint;
        this.occurrences = occurrences;
        this.ranking = ranking;
        this.logohtml = logohtml;
    }

    /**
     * Data for rendering
     *
     * @param {number} maxOccurrences Occurrence of most used plugin
     * @returns {Promise<
     * {reason: string, pluginname: string, hint: string,
     * activityicon: string, ranking: *, id: number,
     * occurences: string, stars: string[]}
     * >}
     */
    async export(maxOccurrences) {
        const rankFix = Math.max(0, Math.min(10, this.ranking));

        return {
            id: this.id,
            pluginname: this.title,
            hint: this.hint,
            ranking: rankFix,
            reason: this.reason,
            occurrences: await this.#getOccurranceString(maxOccurrences),
            stars: this.#convertRankingStars(rankFix),
            activityicon: this.logohtml,
        };
    }

    /**
     * Converts the occurrence number for output
     *
     * @param {number} maxOccurrence Occurrence of most used plugin
     * @returns {Promise<string>} Language string, how often this activity occurs in the moodle
     */
    async #getOccurranceString(maxOccurrence) {
        maxOccurrence = Math.max(maxOccurrence, 1);
        const frequencyRanking = Math.floor(this.occurrences * 5 / maxOccurrence);

        const keys = [
            LanguageStrings.OccurrenceVeryRare,
            LanguageStrings.OccurrenceRare,
            LanguageStrings.OccurrenceModerately,
            LanguageStrings.OccurrenceOften,
            LanguageStrings.OccurrenceVeryFrequent,
        ];

        const key = keys[Math.min(frequencyRanking, 4)];
        return getLanguageString(key);
    }

    /**
     * Converts an number from 0 to 10 into a UI-Star rating
     *
     * @param {number} ranking Rating from 0 to 10
     * @return {string[]} UI Data for star rating
     */
    #convertRankingStars(ranking) {
        let staricons = [];
        let stars = Math.floor(ranking / 2);

        for (let i = 0; i < stars; i++) {
            staricons.push('fa-solid fa-star');
        }

        if (ranking % 2 === 1) {
            staricons.push('fa-solid fa-star-half-stroke');
            stars++;
        }

        while (stars < 5) {
            staricons.push('fa-regular fa-star');
            stars++;
        }

        return staricons;
    }
}
