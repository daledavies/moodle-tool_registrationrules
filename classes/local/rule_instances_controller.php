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
    protected function commit(): void {
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
     * Return an up to date array of rule instances in teh correct order.
     *
     * @return array Array of rule instance records.
     */
    public function get_rule_instance_records(): array {
        return $this->ruleinstances;
    }

    /**
     * Return the rule instance record matching the given instanceid.
     *
     * @param int $instanceid
     * @return \stdClass|null A single rule instance record.
     */
    public function get_rule_instance_by_id(int $instanceid) {
        $instance = array_column($this->ruleinstances, null, 'id')[$instanceid];
        if (!$instance) {
            throw new coding_exception('Invalid instance ID');
        }

        return $instance;
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
     * The change will be committed immediately, if $commit is false you will need to
     * call $this->commit when you have finished making changes.
     *
     * @param stdClass $formdata
     * @param bool $commit Commit the change now
     * @return void
     */
    public function add_instance(stdClass $formdata, bool $commit = true): void {
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
        // Commit the update to the database now if required.
        if ($commit) {
            $this->commit();
        }
    }

    /**
     * Update a rule instance in the database using submitted rule_settings form's data.
     *
     * The change will be committed immediately, if $commit is false you will need to
     * call $this->commit when you have finished making changes.
     *
     * @param stdClass $formdata
     * @param bool $commit Commit the change now
     * @return void
     */
    public function update_instance(stdClass $formdata, bool $commit = true): void {
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
        // Commit the update to the database now if required.
        if ($commit) {
            $this->commit();
        }
    }

    /**
     * Delete a rule instance with the given id.
     *
     * The change will be committed immediately, if $commit is false you will need to
     * call $this->commit when you have finished making changes.
     *
     * @param int $instanceid
     * @param bool $commit Commit the change now
     * @return void
     */
    public function delete_instance(int $instanceid, bool $commit = true): void {
        // Set the internal version of this record as deleted.
        $this->ruleinstancesinternal[$instanceid]->deleted = true;
        // Commit the update to the database now if required.
        if ($commit) {
            $this->commit();
        }
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
     * The change will be committed immediately, if $commit is false you will need to
     * call $this->commit when you have finished making changes.
     *
     * TODO: replace magic value with ENUM or constant!
     *
     * @param int $instanceid
     * @param bool $commit Commit the change now
     * @return void
     */
    public function enable_instance(int $instanceid, bool $commit = true): void {
        $this->ruleinstancesinternal[$instanceid]->enabled = 1;
        // Signify we have made a modification.
        $this->ruleinstancesinternal[$instanceid]->modified = true;
        // Commit the update to the database now if required.
        if ($commit) {
            $this->commit();
        }
    }

    /**
     * Disable a single rule instance.
     *
     * The change will be committed immediately, if $commit is false you will need to
     * call $this->commit when you have finished making changes.
     *
     * @param int $instanceid
     * @param bool $commit Commit the change now
     * @return void
     */
    public function disable_instance(int $instanceid, bool $commit = true): void {
        $this->ruleinstancesinternal[$instanceid]->enabled = 0;
        // Signify we have made a modificatio.
        $this->ruleinstancesinternal[$instanceid]->modified = true;
        // Commit the update to the database now if required.
        if ($commit) {
            $this->commit();
        }
    }

    /**
     * Move the given rule instance a single position up.
     *
     * The change will be committed immediately.
     *
     * @param int $instanceid
     * @return void
     */
    public function move_instance_up(int $instanceid): void {
        // If the instance is already at the top of the list then do nothing.
        if ($instanceid === array_key_first($this->ruleinstancesinternal)) {
            return;
        }
        // Find the array key of the previous instance in the list.
        $previnstancekey = $this->find_previous_instance_id($instanceid);
        // Swap the sortorder for the given rule instance and the previous instance.
        if (isset($previnstancekey)) {
            $thisinstancesortorder = $this->ruleinstancesinternal[$instanceid]->sortorder;
            $previnstancesortorder = $this->ruleinstancesinternal[$previnstancekey]->sortorder;
            $this->ruleinstancesinternal[$instanceid]->sortorder = $previnstancesortorder;
            $this->ruleinstancesinternal[$previnstancekey]->sortorder = $thisinstancesortorder;
            // Signify we have made a modification and commit the update to the database.
            $this->ruleinstancesinternal[$instanceid]->modified = true;
            $this->ruleinstancesinternal[$previnstancekey]->modified = true;
            $this->commit();
        }
    }

    /**
     * Move the given rule instance a single position down.
     *
     * The change will be committed immediately.
     *
     * @param int $instanceid
     * @return void
     */
    public function move_instance_down(int $instanceid): void {
        // If the instance is already at the bottom of the list then do nothing.
        if ($instanceid === array_key_last($this->ruleinstances)) {
            return;
        }
        // Find the array key of the next instance in the list.
        $nextinstancekey = $this->find_next_instance_id($instanceid);
        // Swap the sortorder for the given rule instance and the previous instance.
        if (isset($nextinstancekey)) {
            $thisinstancesortorder = $this->ruleinstancesinternal[$instanceid]->sortorder;
            $nextinstancesortorder = $this->ruleinstancesinternal[$nextinstancekey]->sortorder;
            $this->ruleinstancesinternal[$instanceid]->sortorder = $nextinstancesortorder;
            $this->ruleinstancesinternal[$nextinstancekey]->sortorder = $thisinstancesortorder;
            // Signify we have made a modification and commit the update to the database.
            $this->ruleinstancesinternal[$instanceid]->modified = true;
            $this->ruleinstancesinternal[$nextinstancekey]->modified = true;
            $this->commit();
        }
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

        foreach ($this->ruleinstances as $key => $ruleinstance) {
            $moveuplink = $movedownlink = null;

            // Determine if we should add a link to move the instance up or down.
            $index = array_search($key, array_column($this->ruleinstances, 'id'));
            // If we are at the top then there should be no move up link.
            if ($index != 0) {
                $moveuplink = new \moodle_url(
                    '/admin/tool/registrationrules/manageruleinstances.php',
                    [
                        'instanceid' => $ruleinstance->id,
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
                        'instanceid' => $ruleinstance->id,
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
                        'id' => $ruleinstance->id,
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
                            'instanceid' => $ruleinstance->id,
                            'action' => 'delete',
                        ],
                    ),
                    icon: new pix_icon('t/delete', get_string('delete')),
                    text: get_string('delete'),
                ),
            ];

            // Add the instance row details to our template context.
            $context->instances[] = (object)[
                'id' => $ruleinstance->id,
                'name' => $ruleinstance->name,
                'type' => new \lang_string('pluginname', 'registrationrule_' . $ruleinstance->type),
                'points' => $ruleinstance->points,
                'fallbackpoints' => $ruleinstance->fallbackpoints,
                'enabled' => $output->render(
                    new \action_menu_link_primary(
                        url: new \moodle_url(
                            '/admin/tool/registrationrules/manageruleinstances.php',
                            [
                                'instanceid' => $ruleinstance->id,
                                'action' => $ruleinstance->enabled ? 'disable' : 'enable',
                                'sesskey' => sesskey(),
                            ],
                        ),
                        icon: $ruleinstance->enabled ? new pix_icon('t/hide', get_string('disable'))
                                                     : new pix_icon('t/show', get_string('enable')),
                        text: $ruleinstance->enabled ? get_string('disable') : get_string('enable'),
                    ),
                ),
                'pluginenabled' => $ruleinstance->pluginenabled,
                'sortorder' => $ruleinstance->sortorder,
                'moveuplink' => $moveuplink,
                'movedownlink' => $movedownlink,
                'actions' => (new action_menu($actions))->export_for_template($output),
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
        if (defined("$class::SETTINGS_FIELDS")) {
            foreach ($class::SETTINGS_FIELDS as $field) {
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
