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
     * Return an up to date array of rule instances in teh correct order.
     *
     * @return array Array of rule instance records.
     */
    public function get_rule_instance_records(): array {
        return $this->ruleinstances;
    }

    /**
     * Add a rule instance to the database using submitted data from rule_settings form.
     *
     * @param stdClass $formdata
     * @return void
     */
    public function add_instance(stdClass $formdata): void {
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
        // Add the new object to the internal list of rule instances and
        // commit the update to the database.
        $this->ruleinstancesinternal[] = $instance;
        $this->commit();
    }

    /**
     * Update a rule instance in the database using submitted rule_settings form's data.
     *
     * @param stdClass $formdata
     * @return void
     */
    public function update_instance(stdClass $formdata): void {
         $formdata->type = $this->ruleinstancesinternal[$formdata->id]->type;
        // Update default fields in record.
        foreach ($this->extract_instancedata($formdata) as $property => $value) {
            if ($property != 'type') {
                $this->ruleinstancesinternal[$formdata->id]->{$property} = $value;
            }
        }
        // Encode rule specific config data from the form and add to the instance record.
        $this->ruleinstancesinternal[$formdata->id]->other = $this->encode_instance_config($formdata);
        // Signify we have made a modification and commit the update to the database.
        $this->ruleinstancesinternal[$formdata->id]->modified = true;
        $this->commit();
    }

    /**
     * Delete a rule instance with the given id.
     *
     * @param int $instanceid
     * @return void
     */
    public function delete_instance(int $instanceid): void {
        // Set the internal version of this record as deleted commit the update to the database.
        $this->ruleinstancesinternal[$instanceid]->deleted = true;
        $this->commit();
    }

    /**
     * Enable a single rule instance.
     *
     * TODO: replace magic value with ENUM or constant!
     *
     * @param int $instanceid
     * @return void
     */
    public function enable_instance(int $instanceid): void {
        $this->ruleinstancesinternal[$instanceid]->enabled = 1;
        // Signify we have made a modification and commit the update to the database.
        $this->ruleinstancesinternal[$instanceid]->modified = true;
        $this->commit();
    }

    /**
     * Disable a single rule instance.
     *
     * @param int $instanceid
     * @return void
     */
    public function disable_instance(int $instanceid): void {
        $this->ruleinstancesinternal[$instanceid]->enabled = 0;
        // Signify we have made a modification and commit the update to the database.
        $this->ruleinstancesinternal[$instanceid]->modified = true;
        $this->commit();
    }

    /**
     * Move the given rule instance a single position up.
     *
     * @param int $instanceid
     * @return void
     */
    public function move_instance_up(int $instanceid) {
        global $DB;

        if ($instanceid === array_key_first($this->ruleinstances)) {
            // No more moving up!
            return;
        }

        // TODO: finish!
    }

    /**
     * Move the given rule instance a single position down.
     * @param int $instanceid
     * @return void
     */
    public function move_instance_down(int $instanceid) {
        global $DB;

        if ($instanceid === array_key_last($this->ruleinstances)) {
            // No more moving down!
            return;
        }

        // TODO: finish!
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info required for the template is pre-calculated (e.g. capability checks).
     *
     * TODO: Need to use a sesskey for all actions!
     * TODO: Only add up icon if not at top of the list.
     * TODO: Only add down icon if not at bottom of the list.
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

        foreach ($this->ruleinstances as $ruleinstance) {
            $actions = new action_menu([
                new \action_menu_link_primary(
                    url: new \moodle_url(
                        '/admin/tool/registrationrules/editruleinstance.php',
                        [
                            'id' => $ruleinstance->id,
                        ],
                    ),
                    icon: new pix_icon('t/edit', 'edit'),
                    text: 'edit',
                ),
                new \action_menu_link_primary(
                    url: new \moodle_url(
                        '/admin/tool/registrationrules/manageruleinstances.php',
                        [
                            'instanceid' => $ruleinstance->id,
                            'action' => 'moveup',
                        ],
                    ),
                    icon: new pix_icon('t/up', 'up'),
                    text: 'moveup',
                ),
                new \action_menu_link_primary(
                    url: new \moodle_url(
                        '/admin/tool/registrationrules/manageruleinstances.php',
                        [
                            'instanceid' => $ruleinstance->id,
                            'action' => 'movedown',
                        ],
                    ),
                    icon: new pix_icon('t/down', 'down'),
                    text: 'movedown',
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
                    icon: new pix_icon('t/delete', 'delete'),
                    text: 'delete',
                ),
            ]);

            $context->instances[] = (object)[
                'id' => $ruleinstance->id,
                'name' => $ruleinstance->name,
                'type' => $ruleinstance->type,
                'points' => $ruleinstance->points,
                'fallbackpoints' => $ruleinstance->fallbackpoints,
                'enabled' => $output->render(
                    new \action_menu_link_primary(
                        url: new \moodle_url(
                            '/admin/tool/registrationrules/manageruleinstances.php',
                            [
                                'instanceid' => $ruleinstance->id,
                                'action' => $ruleinstance->enabled ? 'disable' : 'enable',
                            ],
                        ),
                        icon: $ruleinstance->enabled ? new pix_icon('t/hide', 'hide') : new pix_icon('t/show', 'show'),
                        text: $ruleinstance->enabled ? 'disable' : 'enable',
                    ),
                ),
                'sortorder' => $ruleinstance->sortorder,
                'actions' => $actions->export_for_template($output),
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
     * Get available rule types to generate the add rule instance menu.
     *
     * @return array
     * @throws moodle_exception
     */
    public function get_types_for_add_menu(): array {
        $types = [];
        $pluginmanager = \core_plugin_manager::instance();
        $ruletypes = $pluginmanager->get_installed_plugins('registrationrule');
        foreach (array_keys($ruletypes) as $ruleplugin) {
            $types[] = (object)[
                'addurl' => new \moodle_url(
                    '/admin/tool/registrationrules/editruleinstance.php',
                    ['addruletype' => $ruleplugin],
                ),
                'name' => $ruleplugin,
            ];
        }
        return $types;
    }
}