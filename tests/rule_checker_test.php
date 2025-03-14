<?php
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

namespace tool_registrationrules;

use stdClass;
use tool_registrationrules\local\rule_checker;
use tool_registrationrules\local\rule_instances_controller;

/**
 * Tests for rule_checker class.
 *
 * @package tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rule_checker_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Tests that when checks are run, the correct number of results are returned and
     * they are of the expected type.
     *
     * @covers ::run_pre_data_checks
     */
    public function test_run_pre_data_checks(): void {
        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type, only one
        // instance of this rule should be allowed.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;

        // Add all instances using the controller.
        $controller->add_instance($formdata1)->commit();

        // Get an instance of rule_checker.
        $rulechecker = rule_checker::get_instance('test_run_pre_data_checks');

        // Run the checks and get the results.
        $rulechecker->run_pre_data_checks();
        $results = $rulechecker->get_results();

        // We should have one result returned.
        $this->assertCount(1, $results);
        // It should be an instance of rule_check_result.
        $this->assertContainsOnlyInstancesOf('tool_registrationrules\local\rule_check_result', $results);
    }

    /**
     * Tests that no checks are run if registration rules plugin is not enabled.
     *
     * @covers ::is_registration_allowed
     */
    public function test_is_registration_allowed_plugin_disabled(): void {
        // Initially disable registration rules plugin.
        set_config('enable', 0, 'tool_registrationrules');

        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type, only one
        // instance of this rule should be allowed.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;

        // Add all instances using the controller.
        $controller->add_instance($formdata1)->commit();
        $rulechecker = rule_checker::get_instance('test_is_registration_allowed_plugin_disabled');

        // Registration should be allowed as registration rules plugin is not enabled.
        $allowed = $rulechecker->is_registration_allowed();
        $this->assertTrue($allowed);
    }

    /**
     * Tests that rule_checker throws an exception if checks have not been run.
     *
     * @covers ::is_registration_allowed
     */
    public function test_is_registration_allowed_no_checks_run(): void {
        // Enable registration rules plugin.
        set_config('enable', 1, 'tool_registrationrules');

        $rulechecker = rule_checker::get_instance('test_is_registration_allowed_no_checks_run');

        // We have not called run_pre_data_checks so rule_checker should throw an exception.
        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage('rule_checker::check() must be called before using rule_checker::is_registration_allowed()');
        $rulechecker->is_registration_allowed();
    }

    /**
     * Tests that rule_checker correctly collates points from rules and denies registration
     * if the number of points are greater than or equal to the configured max points.
     *
     * @covers ::is_registration_allowed
     */
    public function test_is_registration_allowed_points_greater(): void {
        // Enable registration rules plugin.
        set_config('enable', 1, 'tool_registrationrules');
        // Initially set total points to a low figure.
        set_config('maxpoints', 50, 'tool_registrationrules');

        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type, the number of
        // points for this rule are higher than maxpoints above.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;

        // Add all instances using the controller.
        $controller->add_instance($formdata1)->commit();
        $rulechecker = rule_checker::get_instance('test_is_registration_allowed_points_greater');

        // Registration should not be allowed as results points are higher than maxpoints.
        $rulechecker->run_pre_data_checks();
        $allowed = $rulechecker->is_registration_allowed();
        $this->assertFalse($allowed);
    }

    /**
     * Tests that rule_checker correctly collates points from rules and allows registration
     * if the number of points are less than the configured max points.
     *
     * @covers ::is_registration_allowed
     */
    public function test_is_registration_allowed_points_less(): void {
        // Enable registration rules plugin.
        set_config('enable', 1, 'tool_registrationrules');
        // Initially set total points to a high figure.
        set_config('maxpoints', 150, 'tool_registrationrules');

        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type, the number of
        // points for this rule are lower than maxpoints above.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;

        // Add all instances using the controller.
        $controller->add_instance($formdata1)->commit();
        $rulechecker = rule_checker::get_instance('test_is_registration_allowed_points_less');

        // Registration should be allowed as results points are lower than maxpoints.
        $rulechecker->run_pre_data_checks();
        $allowed = $rulechecker->is_registration_allowed();
        $this->assertTrue($allowed);
    }
}
