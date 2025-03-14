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
use tool_registrationrules\local\rule_instances_controller;

/**
 * Tests for rules instances controler.
 *
 * @package tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rule_instances_controller_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Tests getting a single instance of the controller.
     *
     * @covers ::get_instance
     */
    public function test_get_instance(): void {
        $instance = rule_instances_controller::get_instance();
        $this->assertInstanceOf(rule_instances_controller::class, $instance);
    }

    /**
     * Tests adding a rule instance using the controller.
     *
     * @covers ::add_instance
     */
    public function test_add_instance(): void {
        $controller = new rule_instances_controller();
        $formdata = new stdClass();
        $formdata->type = 'nope';
        $formdata->enabled = 1;
        $formdata->description = 'Test description';
        $formdata->points = 100;
        $formdata->fallbackpoints = 50;

        $controller->add_instance($formdata)->commit();
        $instances = $controller->get_rule_instances();
        $this->assertCount(1, $instances);
    }

    /**
     * Tests updating a rule instance using the controller.
     *
     * @covers ::update_instance
     */
    public function test_update_instance(): void {
        $controller = new rule_instances_controller();
        $formdata = new stdClass();
        $formdata->type = 'nope';
        $formdata->enabled = 1;
        $formdata->description = 'Test description';
        $formdata->points = 100;
        $formdata->fallbackpoints = 50;

        $controller->add_instance($formdata)->commit();
        $instances = $controller->get_rule_instances();
        $instance = reset($instances);

        $formdata->id = $instance->get_id();
        $formdata->description = 'Updated description';
        $controller->update_instance($formdata);

        $updatedinstance = $controller->get_rule_instance_by_id($instance->get_id());
        $this->assertEquals('Updated description', $updatedinstance->get_display_description());
    }

    /**
     * Tests deleting a rule instance using the controller.
     *
     * @covers ::delete_instance
     */
    public function test_delete_instance(): void {
        $controller = new rule_instances_controller();
        $formdata = new stdClass();
        $formdata->type = 'nope';
        $formdata->enabled = 1;
        $formdata->description = 'Test description';
        $formdata->points = 100;
        $formdata->fallbackpoints = 50;

        $controller->add_instance($formdata)->commit();
        $instances = $controller->get_rule_instances();
        $instance = reset($instances);

        $controller->delete_instance($instance->get_id())->commit();
        $instancesafterdeletion = $controller->get_rule_instances();
        $this->assertCount(0, $instancesafterdeletion);
    }

    /**
     * Tests rule instance sortorder is correctly decremented when instance
     * is moved up the list, also test sortorder does not change when moving
     * an instance already at the top of the list.
     *
     * @covers ::move_instance_up
     */
    public function test_move_instance_up(): void {
        $controller = new rule_instances_controller();

        // Raw data used to create first instance.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;
        // Raw data used to create second instance.
        $formdata2 = clone $formdata1;
        $formdata2->description = 'Instance 2';

        // Add both instances using the controller, commit the change so we can create their IDs.
        $controller->add_instance($formdata1)->add_instance($formdata2)->commit();

        // Get the rule instance records back using the controller.
        $instances1 = $controller->get_rule_instance_records();

        // At this point the instance records will be in the order they were added
        // so the last instance will have a sortorder of 2. So we will grab a copy
        // of the last one in the list.
        $instance = end($instances1);

        // Tell the controller to move the last instance up using it's ID.
        $controller->move_instance_up($instance->id);

        // Get the moved instance back using the controller.
        $movedinstance = $controller->get_rule_instance_by_id($instance->id);

        // The moved instance should now have a sortorder of 1.
        $this->assertEquals($movedinstance->get_sortorder(), 1);

        // Tell the controller to move the last instance up for a second time.
        $controller->move_instance_up($movedinstance->get_id());

        // The moved instance should still have a sortorder of 1 as it was
        // already at the top of the list.
        $this->assertEquals($movedinstance->get_sortorder(), 1);
    }

    /**
     * Tests rule instance sortorder is correctly incremented when instance
     * is moved down the list, also test sortorder does not change when moving
     * an instance already at the bottom of the list.
     *
     * @covers ::move_instance_down
     */
    public function test_move_instance_down(): void {
        $controller = new rule_instances_controller();

        // Raw data used to create first instance.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;
        // Raw data used to create second instance.
        $formdata2 = clone $formdata1;
        $formdata2->description = 'Instance 2';

        // Add both instances using the controller, commit the change so we can create their IDs.
        $controller->add_instance($formdata1)->add_instance($formdata2)->commit();

        // Get the rule instance records back using the controller.
        $instances1 = $controller->get_rule_instance_records();

        // At this point the instance records will be in the order they were added
        // so the first instance will have a sortorder of 1. So we will grab a copy
        // of the first one in the list.
        $instance = reset($instances1);

        // Tell the controller to move the first instance down using it's ID.
        $controller->move_instance_down($instance->id);

        // Get the moved instance back using the controller.
        $movedinstance = $controller->get_rule_instance_by_id($instance->id);

        // The moved instance should now have a sortorder of 1.
        $this->assertEquals($movedinstance->get_sortorder(), 2);

        // Tell the controller to move the first instance down for a second time.
        $controller->move_instance_down($movedinstance->get_id());

        // The moved instance should still have a sortorder of 2 as it was
        // already at the bottom of the list.
        $this->assertEquals($movedinstance->get_sortorder(), 2);
    }

    /**
     * Tests only active rule instance objects are returned, excluding those where either the
     * rule plugin or instance are disabled, or the rule plugin has not been configured.
     *
     * @covers ::get_active_rule_instances
     */
    public function test_get_active_rule_instances(): void {
        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;
        // Raw data used to create second instance of "nope" type.
        $formdata2 = clone $formdata1;
        $formdata2->description = 'Instance 2';
        // Raw data used to create first instance of "verifymx" type.
        $formdata3 = new stdClass();
        $formdata3->type = 'verifymx';
        $formdata3->enabled = 1;
        $formdata3->description = 'Instance 3';
        $formdata3->points = 100;
        $formdata3->fallbackpoints = 50;
        // Add all instances using the controller.
        $controller
            ->add_instance($formdata1)
            ->add_instance($formdata2)
            ->add_instance($formdata3)->commit();

        // We should see 3 instances.
        $instances = $controller->get_active_rule_instances();
        $this->assertCount(3, $instances);

        // Create a disabled instance.
        $formdata4 = clone $formdata1;
        $formdata4->enabled = 0;
        $formdata4->description = 'Instance 4';
        $controller->add_instance($formdata4)->commit();

        // We should still see 3 instances.
        $instances = $controller->get_active_rule_instances();
        $this->assertCount(3, $instances);

        // Disable the verifymx plugin.
        \tool_registrationrules\plugininfo\registrationrule::enable_plugin('verifymx', false);

        // We should now see 2 instances.
        $instances = $controller->get_active_rule_instances();
        $this->assertCount(2, $instances);

        // Disable the nope plugin.
        \tool_registrationrules\plugininfo\registrationrule::enable_plugin('nope', false);

        // There should now be no active instances.
        $instances = $controller->get_active_rule_instances();
        $this->assertCount(0, $instances);
    }

    /**
     * Tests that only rule instances of the specified type are returned.
     *
     * @covers ::get_rule_instances_by_type
     */
    public function test_get_rule_instances_by_type(): void {
        $controller = new rule_instances_controller();

        // Raw data used to create first instance of "nope" type.
        $formdata1 = new stdClass();
        $formdata1->type = 'nope';
        $formdata1->enabled = 1;
        $formdata1->description = 'Instance 1';
        $formdata1->points = 100;
        $formdata1->fallbackpoints = 50;
        // Raw data used to create second instance of "nope" type.
        $formdata2 = clone $formdata1;
        $formdata2->description = 'Instance 2';
        // Raw data used to create first instance of "verifymx" type.
        $formdata3 = new stdClass();
        $formdata3->type = 'verifymx';
        $formdata3->enabled = 1;
        $formdata3->description = 'Instance 3';
        $formdata3->points = 100;
        $formdata3->fallbackpoints = 50;

        // Add all instances using the controller.
        $controller
            ->add_instance($formdata1)
            ->add_instance($formdata2)
            ->add_instance($formdata3)->commit();

        $instances = $controller->get_rule_instances_by_type('nope');

        // We should see 2 instances.
        $this->assertCount(2, $instances);

        // Each instance should be the type we asked for.
        $this->assertContainsOnlyInstancesOf('registrationrule_nope\rule', $instances);
    }

    /**
     * Tests that get_rule_instances_by_type() throws the correct exception
     * if passed an invalid rule plugin type.
     *
     * @covers ::get_rule_instances_by_type
     */
    public function test_get_rule_instances_by_type_invalid_type(): void {
        $controller = new rule_instances_controller();
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage('Invalid rule plugin type');
        $controller->get_rule_instances_by_type('invalid');
    }

    /**
     * Tests that only one instance of a rule is allowed where this does not
     * allow multiple instances and that CAPTCHA plugins cannot be added if
     * site reCAPTCHA is enabled.
     *
     * @covers ::new_instance_of_type_allowed
     */
    public function test_new_instance_of_type_allowed(): void {
        global $CFG;
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

        // We should not be allowed to add a second instance.
        $allowed = $controller->new_instance_of_type_allowed('nope');
        $this->assertFalse($allowed);

        // We should be allowed to add a captcha rule.
        $allowed = $controller->new_instance_of_type_allowed('altcha');
        $this->assertTrue($allowed);

        // Enable site reCAPTCHA.
        $CFG->recaptchapublickey = 'randompublickey';
        $CFG->recaptchaprivatekey = 'randomprivatekey';
        $CFG->enableloginrecaptcha = true;

        // We should no longer be allowed to add a captcha rule.
        $allowed = $controller->new_instance_of_type_allowed('altcha');
        $this->assertFalse($allowed);
    }
}
