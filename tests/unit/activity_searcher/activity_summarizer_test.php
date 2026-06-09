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
use core_course\local\entity\content_item;
use local_activityfilter\activity_searcher\content_item_summerizer;
use local_activityfilter\activity_searcher\content_item_manager;
use local_activityfilter\activity_searcher\contracts\content_item_info;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/content_item_generator.php');

/**
 * Unit test for Activity Summarizer.
 *
 * @covers \local_activityfilter\activity_searcher\content_item_summerizer
 * @author Konrad Ebel <konrad.ebel@oncampus.de>
 * @copyright 2025, oncampus GmbH
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class activity_summarizer_test extends advanced_testcase {
    /** @var content_item_summerizer Object to test */
    private content_item_summerizer $activitysummerizer;
    /** @var content_item Testing object */
    private content_item $contentitem;

    /**
     * Constructor.
     *
     * Mocking → core_plugin_manager and activity_plugins
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->contentitem = content_item_generator::generate_content_item(
            'myplugin',
            []
        );
        $activities = $this->createMock(content_item_manager::class);
        $activities->method('get_all')
            ->willReturn([$this->contentitem]);

        $this->activitysummerizer = new content_item_summerizer(
            $activities
        );
    }

    /**
     * Tests, if get activities will exclude subsection,
     * but includes other plugins.
     *
     * @covers ::get_activities
     * @return void
     */
    public function test_get_activities(): void {
        $expected = [$this->contentitem];

        $activities = $this->activitysummerizer->get_activities();

        $this->assertEquals($expected, $activities);
    }

    /**
     * Tests if the plugin infos are correctly exported
     *
     * @covers ::get_activity_data
     * @return void
     */
    public function test_get_activity_data(): void {
        $expected = [
            new content_item_info($this->contentitem),
        ];
        set_config('ai_hint_myplugin', 'my ai help', 'local_activityfilter');

        $data = $this->activitysummerizer->get_content_item_infos();

        $this->assertEquals($expected, $data);
    }
}
