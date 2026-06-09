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

namespace local_activityfilter;

use advanced_testcase;
use core\di;
use local_activityfilter\activity_searcher\content_item_manager;
use local_activityfilter\activity_searcher\content_item_summarizer;
use local_activityfilter\activity_searcher\contracts\content_item_info;
use local_activityfilter\activity_searcher\contracts\activity_ranking;
use local_activityfilter\activity_searcher\contracts\i_activity_searcher;
use local_activityfilter\activity_searcher\i_content_item_summarizer;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/content_item_generator.php');

/**
 * Unit test for AI Searcher.
 *
 * @covers \local_activityfilter\activity_searcher\ai_searcher
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ai_searcher_test extends advanced_testcase {
    /**
     * Data provider for test_convert_ai_response_to_json
     *
     * @return array[] Case Name → [Raw AI Data, Converted Data]
     */
    public static function convert_ai_response_to_json_dataprovider(): array {
        return [
            'json inside markdown block' => [
                // phpcs:ignore moodle.Strings.ForbiddenStrings.Found -- Test parsing of Markdown JSON code.
                'Ich habe die JSON generiert hier bitte: ```json [{"name":"Kekse"}]```',
                [['name' => 'Kekse']],
            ],
            'valid direct array' => [
                '[{"name":"Kekse"}]',
                [['name' => 'Kekse']],
            ],
            'invalid JSON direct' => [
                "{ungültig:123}",
                false,
            ],
            'invalid JSON in codeblock' => [
                // phpcs:ignore moodle.Strings.ForbiddenStrings.Found -- Test parsing of Markdown JSON code.
                "```json\n{nope:}\n```",
                false,
            ],
            'no codeblock' => [
                "Da steht nichts JSON-artiges",
                false,
            ],
            'empty input' => [
                "",
                false,
            ],
        ];
    }

    /**
     * Test if cleanup of AI Answers is working properly
     *
     * @covers ::convert_ai_response_to_json
     * @param string $jsontext Raw AI Text
     * @param array|false $expectedjsonobject Array or false if not convertable
     * @dataProvider convert_ai_response_to_json_dataprovider
     */
    public function test_convert_ai_response_to_json(string $jsontext, array|false $expectedjsonobject): void {
        $aisearcher = di::get(i_activity_searcher::class);

        $jsonobject = $aisearcher->convert_ai_response_from_json($jsontext);

        $this->assertEquals($expectedjsonobject, $jsonobject);
    }

    /**
     * Data provider for test_convert_json_to_ranking
     *
     * @return array[] Case Name → [Json Data, Expected Activity Ranking]
     */
    public static function convert_json_to_ranking_dataprovider(): array {
        return [
            'valid data' => [
                [['pluginname' => 'kekse', 'reason' => 'My Reason', 'hint' => 'My Hint', 'ranking' => 6]],
                [new activity_ranking('kekse', 'Kekse', 'My Reason', 'My Hint', 0, 6, '')],
            ],
            'missing key' => [
                [['pluginname' => 'kekse', 'hint' => 'My Hint', 'ranking' => 6]],
                [new activity_ranking('kekse', 'Kekse', '', 'My Hint', 0, 6, '')],
            ],
            'missing pluginname' => [
                [['reason' => 'My Reason', 'hint' => 'My Hint', 'ranking' => 6]],
                [],
            ],
            'missing plugin' => [
                [['pluginname' => 'my_non_existing', 'reason' => 'My Reason', 'hint' => 'My Hint', 'ranking' => 6]],
                [],
            ],
        ];
    }

    /**
     * Test if plugin get converted correctly from json into an ranking
     *
     * @covers ::convert_json_to_ranking
     * @param mixed $jsonobject
     * @param array $expectedrankings
     * @dataProvider convert_json_to_ranking_dataprovider
     */
    public function test_convert_json_to_ranking(mixed $jsonobject, array $expectedrankings): void {
        $contentitemmng = new content_item_summarizer(
            di::get(content_item_manager::class),
            [
                new content_item_info(content_item_generator::generate_content_item('kekse', ['help' => 'Hilfe'])),
                new content_item_info(content_item_generator::generate_content_item('leber', ['help' => 'Hilfe2'])),
            ]
        );
        di::set(i_content_item_summarizer::class, $contentitemmng);
        $aisearcher = di::get(i_activity_searcher::class);

        $rankings = $aisearcher->convert_data_to_ranking($jsonobject);

        $this->assertEquals($expectedrankings, $rankings);
    }
}
