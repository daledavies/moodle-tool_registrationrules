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

/**
 * List of rule instances an actions.
 *
 * @package tool_registrationrules
 * @copyright 2024 eDaktik GmbH {@link https://www.edaktik.at/}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local;

use action_menu;
use action_menu_filler;
use coding_exception;
use dml_exception;
use moodle_exception;
use pix_icon;
use renderable;
use renderer_base;
use stdClass;

/**
 * Class rule_instances_controller
 *
 * @package tool_registrationrules
 * @copyright 2024 eDaktik GmbH {@link https://www.edaktik.at/}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_instances_controller implements renderable, \templatable {
    /** @var stdClass[] external representation of rule instance records, sorted by "sortorder" */
    protected $ruleinstances = [];

    /** @var stdClass[] internal representation of $ruleinstances (used for tracking changes) */
    protected $ruleinstancesinternal = [];

    /**
     * Constructor
     *
     * @throws dml_exception
     */
    public function __construct() {
        global $DB;
        // Get a sorted list of rule instance records from the database.
        $instancerecords = $DB->get_records(
            table: 'tool_registrationrules',
            sort: 'sortorder ASC',
        );
        // Get a list of enabled rule plugins and update each instance record
        // to show if it refers to an enabled or disabled plugin.
        $enabledplugins = \tool_registrationrules\plugininfo\registrationrule::get_enabled_plugins();
        foreach ($instancerecords as $key => $instance) {
            $instancerecords[$key]->pluginenabled = false;
            if (in_array($instance->type, $enabledplugins)) {
                $instancerecords[$key]->pluginenabled = true;
            }
        }
        // Initialise the external and internal representation of instance records.
        $this->ruleinstances = $this->ruleinstancesinternal = $instancerecords;
    }

    /**
     * Commit all changes to rule instances to the database, sorts the new
     * list of rule instance records and resets the internal representation.
     *
     * @return void
     */
    public function commit(): void {
        global $DB;
        // We'll possibly be committing a number of new records and updates
        // to the databse so best to create a new transaction.
        $transaction = $DB->start_delegated_transaction();
        // Iterate over the internal rule insatnces list, determine if we have
        // anything to commit and perform the appropriate database operation.
        foreach ($this->ruleinstancesinternal as $internal) {
            // New instances.
            if (!isset($internal->id)) {
                $internal->id = $DB->insert_record('tool_registrationrules', $internal);
                $this->ruleinstances[$internal->id] = $internal;
            }
            // Modified instances.
            if (isset($internal->modified)) {
                unset($internal->modified);
                $DB->update_record('tool_registrationrules', $internal);
                $this->ruleinstances[$internal->id] = $internal;
            }
            // Deleted instances.
            if (isset($internal->deleted)) {
                $DB->delete_records('tool_registrationrules', ['id' => $internal->id]);
                unset($this->ruleinstances[$internal->id]);
            }
        }
        // Signify that we are happy for the changes within this transaction to be
        // committed when ready.
        $transaction->allow_commit();
        // Reorder array of ruleinstances based on each instance's sortorder.
        uasort($this->ruleinstances, function($a, $b) {
            return ($a->sortorder < $b->sortorder) ? -1 : 1;
        });
        // Reset the internal list of rule instances.
        $this->ruleinstancesinternal = $this->ruleinstances;
    }

    /**
     * Find the rule instance ID that comes after the given instance in the sorted
     * list of rule instances.
     *
     * @param int $instanceid
     * @return int|null
     */
    protected function find_previous_instance_id(int $instanceid): ?int {
        $previnstancekey = null;
        // Iterate over the rule instances until we find a find an instance with a
        // sortorder that is greater than or equal to the given instances sortorder
        // value, keeping a record of the current iterations sortorder as we go.
        foreach ($this->ruleinstancesinternal as $key => $instance) {
            if ($instance->sortorder >= $this->ruleinstancesinternal[$instanceid]->sortorder) {
                break;
            }
            $previnstancekey = $key;
        }
        // Return the key of the instance found during the last iteration.
        return $previnstancekey;
    }

    /**
     * Find the rule instance ID that comes before the given instance in the sorted
     * list of rule instances.
     *
     * @param int $instanceid
     * @return int|null
     */
    protected function find_next_instance_id(int $instanceid): ?int {
        // Iterate over the rule instances, skipping all the one's that have
        // a sortorder that is less than or equal to the given instance's sortorder.
        foreach ($this->ruleinstancesinternal as $key => $instance) {
            if ($instance->sortorder <= $this->ruleinstancesinternal[$instanceid]->sortorder) {
                continue;
            }
            // Return the next instance's key.
            return $key;
        }
    }

    /**
     * Return an up to date array of rule instance DB records in the correct order.
     *
     * @return array Array of rule instance records.
     */
    public function get_rule_instance_records(): array {
        return $this->ruleinstances;
    }

    /**
     * Return a list of hydrated rule instance objects.
     *
     * @return array
     */
    public function get_rule_instances(): array {
        $instances = [];
        foreach ($this->get_rule_instance_records() as $instance) {
            $pluginrule = 'registrationrule_' . $instance->type . '\rule';

            // Parse additional config and add to instance.
            foreach (json_decode($instance->other) as $configkey => $configvalue) {
                $instance->$configkey = $configvalue;
            }
            $ruleinstance = new $pluginrule($instance);
            if (!$ruleinstance instanceof rule\rule_base) {
                debugging("Rule $pluginrule does not extend rule_base", DEBUG_DEVELOPER);
                continue;
            }
            $instances[$ruleinstance->get_id()] = $ruleinstance;
        }

        return $instances;
    }

    /**
     * Return a rule instance object matching the given instanceid.
     *
     * @param int $instanceid
     * @return rule\rule_base|null A single rule instance record.
     */
    public function get_rule_instance_by_id(int $instanceid): rule\rule_base {
        $instances = $this->get_rule_instances();
        if (!isset($instances[$instanceid])) {
            throw new coding_exception('Invalid instance ID');
        }

        return $instances[$instanceid];
    }

    /**
     * Return a list of active rule instance objects, excluding those where either the
     * rule plugin or instance are disabled, or the rule plugin has not been configured.
     *
     * @return array
     */
    public function get_active_rule_instances(): array {
        $activeinstances = [];
        foreach ($this->get_rule_instances() as $instance) {
            $instanceconfig = $instance->get_config();
            if (!$instanceconfig->pluginenabled || !$instanceconfig->enabled) {
                continue;
            }
            // If this rule plugin class implements plugin_configurable then we can cheeck if the
            // requierd configuration options have been satisfied.
            if (is_subclass_of($instance, 'tool_registrationrules\local\rule\plugin_configurable')) {
                if (!$instance::is_plugin_configured()) {
                    continue;
                }
            }
            $activeinstances[] = $instance;
        }

        return $activeinstances;
    }

    /**
     * Return all rule instance records matching the given type.
     *
     * @param string $type
     * @return array Array of rule instance records
     */
    public function get_rule_instances_by_type(string $type) {
        $instances = array_filter($this->ruleinstances, function ($instance) use ($type) {
            return $instance->type === $type;
        });
        if (!$instances) {
            throw new coding_exception('Invalid rule plugin type');
        }

        return $instances;
    }

    /**
     * Add a rule instance to the database using submitted data from rule_settings form.
     *
     * @param stdClass $formdata
     * @return rule_instances_controller
     */
    public function add_instance(stdClass $formdata): rule_instances_controller {
        // Extract the standard rule config from the form and create a new
        // instance record object.
        $instance = $this->extract_instancedata($formdata);
        // Encode rule specific config data from the form and add to
        // the instance record.
        $instance->other = $this->encode_instance_config($formdata);
        // Find the highest sortorder in the list of rule instances so far.
        $highestsortorder = 0;
        foreach ($this->ruleinstancesinternal as $i) {
            if (!isset($i->deleted) && $i->sortorder > $highestsortorder) {
                $highestsortorder = $i->sortorder;
            }
        }
        // Increment the new instance's sortorder by 1 so it is added to
        // the end of the list.
        $instance->sortorder = $highestsortorder + 1;
        // Add the new object to the internal list of rule instances.
        $this->ruleinstancesinternal[] = $instance;

        return $this;
    }

    /**
     * Update a rule instance in the database using submitted rule_settings form's data.
     *
     * @param stdClass $formdata
     * @return rule_instances_controller
     */
    public function update_instance(stdClass $formdata): rule_instances_controller {
         $formdata->type = $this->ruleinstancesinternal[$formdata->id]->type;
        // Update default fields in record.
        foreach ($this->extract_instancedata($formdata) as $property => $value) {
            if ($property != 'type') {
                $this->ruleinstancesinternal[$formdata->id]->{$property} = $value;
            }
        }
        // Encode rule specific config data from the form and add to the instance record.
        $this->ruleinstancesinternal[$formdata->id]->other = $this->encode_instance_config($formdata);
        // Signify we have made a modification.
        $this->ruleinstancesinternal[$formdata->id]->modified = true;

        return $this;
    }

    /**
     * Delete a rule instance with the given id.
     *
     * @param int $instanceid
     * @return rule_instances_controller
     */
    public function delete_instance(int $instanceid): rule_instances_controller {
        // Set the internal version of this record as deleted.
        $this->ruleinstancesinternal[$instanceid]->deleted = true;

        return $this;
    }

    /**
     * Delete all instances of a given rule plugin.
     *
     * @param string $plugintype
     * @return void
     */
    public function delete_all_instances_of_plugin(string $plugintype): void {
        $ruleinstances = $this->get_rule_instances_by_type($plugintype);
        // Delete all instances found but don't commit until after we've
        // done all of them.
        foreach ($ruleinstances as $instance) {
            $this->delete_instance($instance->id, false);
        }
        // Finally we can commit all the changes in one go.
        $this->commit();
    }

    /**
     * Enable a single rule instance.
     *
     * TODO: replace magic value with ENUM or constant!
     *
     * @param int $instanceid
     * @return rule_instances_controller
     */
    public function enable_instance(int $instanceid): rule_instances_controller {
        $this->ruleinstancesinternal[$instanceid]->enabled = 1;
        // Signify we have made a modification.
        $this->ruleinstancesinternal[$instanceid]->modified = true;

        return $this;
    }

    /**
     * Disable a single rule instance.
     *
     * @param int $instanceid
     * @return rule_instances_controller
     */
    public function disable_instance(int $instanceid): rule_instances_controller {
        $this->ruleinstancesinternal[$instanceid]->enabled = 0;
        // Signify we have made a modificatio.
        $this->ruleinstancesinternal[$instanceid]->modified = true;

        return $this;
    }

    /**
     * Move the given rule instance a single position up.
     *
     * @param int $instanceid
     * @return rule_instances_controller
     */
    public function move_instance_up(int $instanceid): rule_instances_controller {
        // If the instance is already at the top of the list then do nothing.
        if ($instanceid !== array_key_first($this->ruleinstancesinternal)) {
            // Find the array key of the previous instance in the list.
            $previnstancekey = $this->find_previous_instance_id($instanceid);
            // Swap the sortorder for the given rule instance and the previous instance.
            $thisinstancesortorder = $this->ruleinstancesinternal[$instanceid]->sortorder;
            $previnstancesortorder = $this->ruleinstancesinternal[$previnstancekey]->sortorder;
            $this->ruleinstancesinternal[$instanceid]->sortorder = $previnstancesortorder;
            $this->ruleinstancesinternal[$previnstancekey]->sortorder = $thisinstancesortorder;
            // Signify we have made a modification and commit the update to the database.
            $this->ruleinstancesinternal[$instanceid]->modified = true;
            $this->ruleinstancesinternal[$previnstancekey]->modified = true;
        }

        return $this;
    }

    /**
     * Move the given rule instance a single position down.
     *
     * @param int $instanceid
     * @return rule_instances_controller
     */
    public function move_instance_down(int $instanceid): rule_instances_controller {
        // If the instance is already at the bottom of the list then do nothing.
        if ($instanceid !== array_key_last($this->ruleinstances)) {
            // Find the array key of the next instance in the list.
            $nextinstancekey = $this->find_next_instance_id($instanceid);
            // Swap the sortorder for the given rule instance and the previous instance.
            $thisinstancesortorder = $this->ruleinstancesinternal[$instanceid]->sortorder;
            $nextinstancesortorder = $this->ruleinstancesinternal[$nextinstancekey]->sortorder;
            $this->ruleinstancesinternal[$instanceid]->sortorder = $nextinstancesortorder;
            $this->ruleinstancesinternal[$nextinstancekey]->sortorder = $thisinstancesortorder;
            // Signify we have made a modification and commit the update to the database.
            $this->ruleinstancesinternal[$instanceid]->modified = true;
            $this->ruleinstancesinternal[$nextinstancekey]->modified = true;
        }

        return $this;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        $context = (object)[
            'instances' => [],
            'types' => $this->get_types_for_add_menu(),
        ];

        foreach ($this->get_rule_instances() as $key => $ruleinstance) {
            $moveuplink = $movedownlink = null;

            // Determine if we should add a link to move the instance up or down.
            $index = array_search($key, array_column($this->ruleinstances, 'id'));
            // If we are at the top then there should be no move up link.
            if ($index != 0) {
                $moveuplink = new \moodle_url(
                    '/admin/tool/registrationrules/manageruleinstances.php',
                    [
                        'instanceid' => $ruleinstance->get_id(),
                        'action' => 'moveup',
                        'sesskey' => sesskey(),
                    ],
                );
            }
            // If we are at the bottom then there should be no move down.
            if ($index != count($this->ruleinstances) - 1) {
                $movedownlink = new \moodle_url(
                    '/admin/tool/registrationrules/manageruleinstances.php',
                    [
                        'instanceid' => $ruleinstance->get_id(),
                        'action' => 'movedown',
                        'sesskey' => sesskey(),
                    ],
                );
            }

            // Get a list of action links to add to our action menu.
            $actions = [
                new \action_menu_link_primary(
                url: new \moodle_url(
                    '/admin/tool/registrationrules/editruleinstance.php',
                    [
                        'id' => $ruleinstance->get_id(),
                    ],
                ),
                icon: new pix_icon('t/edit', get_string('edit')),
                text: get_string('edit'),
                ),
                new action_menu_filler(),
                new \action_menu_link_primary(
                    url: new \moodle_url(
                        '/admin/tool/registrationrules/manageruleinstances.php',
                        [
                            'instanceid' => $ruleinstance->get_id(),
                            'action' => 'delete',
                        ],
                    ),
                    icon: new pix_icon('t/delete', get_string('delete')),
                    text: get_string('delete'),
                ),
            ];

            $dimmedreasons = [];
            if (is_subclass_of($ruleinstance, 'tool_registrationrules\local\rule\plugin_configurable')) {
                if (!$ruleinstance::is_plugin_configured()) {
                    $dimmedreasons[] = get_string('notconfigured', 'tool_registrationrules');
                }
            }
            if (!$ruleinstance->get_config()->pluginenabled) {
                $dimmedreasons[] = get_string('plugindisabled', 'tool_registrationrules');
            }

            // Add the instance row details to our template context.
            $context->instances[] = (object)[
                'id' => $ruleinstance->get_id(),
                'name' => $ruleinstance->get_config()->name,
                'type' => new \lang_string('pluginname', 'registrationrule_' . $ruleinstance->get_config()->type),
                'points' => $ruleinstance->get_config()->points,
                'fallbackpoints' => $ruleinstance->get_config()->fallbackpoints,
                'enabled' => $output->render(
                    new \action_menu_link_primary(
                        url: new \moodle_url(
                            '/admin/tool/registrationrules/manageruleinstances.php',
                            [
                                'instanceid' => $ruleinstance->get_id(),
                                'action' => $ruleinstance->get_config()->enabled ? 'disable' : 'enable',
                                'sesskey' => sesskey(),
                            ],
                        ),
                        icon: $ruleinstance->get_config()->enabled ? new pix_icon('t/hide', get_string('disable'))
                                                     : new pix_icon('t/show', get_string('enable')),
                        text: $ruleinstance->get_config()->enabled ? get_string('disable') : get_string('enable'),
                    ),
                ),
                'sortorder' => $ruleinstance->get_config()->sortorder,
                'moveuplink' => $moveuplink,
                'movedownlink' => $movedownlink,
                'actions' => (new action_menu($actions))->export_for_template($output),
                'dimmedrow' => count($dimmedreasons),
                'dimmedmessage' => implode(', ', $dimmedreasons),
            ];
        }

        return $context;
    }


    /**
     * Return an object ready to be written to DB from form data.
     *
     * @param stdClass $formdata
     * @return stdClass
     */
    private function extract_instancedata(stdClass $formdata): stdClass {
        global $USER;

        $instance = (object)[
            'type' => $formdata->type,
            'enabled' => $formdata->enabled,
            'name' => $formdata->name,
            'description' => $formdata->description,
            'points' => $formdata->points,
            'fallbackpoints' => $formdata->fallbackpoints,
            'other' => $this->encode_instance_config($formdata),
            'usermodified' => $USER->id,
            'timemodified' => time(),
        ];

        if (!empty($formdata->id)) {
            $instance->id = $formdata->id;
        }

        return $instance;
    }

    /**
     * Encode rule type's extra form field's data into json for storage in DB.
     *
     * @param stdClass $formdata
     * @return string
     */
    public function encode_instance_config(stdClass $formdata): string {
        $extradata = [];

        // Class of our rule.
        $class = 'registrationrule_' . $formdata->type . '\rule';

        // Extract only the rule specific settings fields from the form
        // data if the rule defines any.
        if (is_subclass_of($class, 'tool_registrationrules\local\rule\instance_configurable')) {
            foreach ($class::get_instance_settings_fields() as $field) {
                $extradata[$field] = $formdata->$field;
            }
        }
        return json_encode($extradata);
    }

    /**
     * Get enabled rule types to generate the add rule instance menu.
     *
     * @return array
     * @throws moodle_exception
     */
    public function get_types_for_add_menu(): array {
        $types = [];
        $ruletypes = \tool_registrationrules\plugininfo\registrationrule::get_enabled_plugins();
        foreach ($ruletypes as $ruleplugin) {
            $types[] = (object)[
                'addurl' => new \moodle_url(
                    '/admin/tool/registrationrules/editruleinstance.php',
                    ['addruletype' => $ruleplugin],
                ),
                'name' => new \lang_string('pluginname', 'registrationrule_' . $ruleplugin),
            ];
        }
        return $types;
    }
}
