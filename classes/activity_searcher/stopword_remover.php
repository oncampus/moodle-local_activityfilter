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

namespace local_activityfilter\activity_searcher;

use NlpTools\Documents\TokensDocument;
use NlpTools\Tokenizers\PennTreeBankTokenizer;
use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../vendor/autoload.php');

/**
 * Text compressor using stopword library.
 *
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stopword_remover implements i_text_compressor {
    /**
     * Applies a shortening of the text for german and english texts
     *
     * @param string $text English or german text
     * @return string Shortened english or german text
     * @throws StopWordsLanguageNotExists Language not found in stopword library
     */
    public function compress(string $text): string {
        $text = strtolower($text);
        $tokens = $this->tokenize($text);
        $this->apply_exclude_stopwords($tokens);
        $shorttext = implode(" ", $tokens->getDocumentData());
        return preg_replace('/\s+([.,!?;:])/', '$1', $shorttext);
    }

    /**
     * Tokenizes text, notice this is only possible with
     * languages like german, english… not with chinese
     *
     * @param string $text
     * @return TokensDocument
     */
    public function tokenize(string $text): TokensDocument {
        $tokenizer = new PennTreeBankTokenizer();
        $tokens = $tokenizer->tokenize($text);
        return new TokensDocument($tokens);
    }

    /**
     * Remove german and english stopwords
     *
     * @param TokensDocument $tokens Tokens to check
     * @return void
     * @throws StopWordsLanguageNotExists Language not found in stopword library
     */
    public function apply_exclude_stopwords(TokensDocument $tokens): void {
        $stopwords = new StopWords();
        $words = array_merge(
            $stopwords->getStopWordsFromLanguage('en'),
            $stopwords->getStopWordsFromLanguage('de')
        );

        $transformation = new \NlpTools\Utils\StopWords($words);
        $tokens->applyTransformation($transformation);
    }
}
