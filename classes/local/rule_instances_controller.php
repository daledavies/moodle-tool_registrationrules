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
use action_menu_link;
use core_component;
use pix_icon;
use renderable;
use renderer_base;
use stdClass;
use tool_registrationrules\local\rule\rule_interface;

/**
 * Class rule_instances_controller
 *
 * @package tool_registrationrules
 * @copyright 2024 eDaktik GmbH {@link https://www.edaktik.at/}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_instances_controller implements renderable, \templatable {
    protected $ruleinstances = [];

    public function __construct() {
        global $DB;

        $instancerecords = $DB->get_records(
            table: 'tool_registrationrules',
            sort: 'sortorder ASC',
        );
        $this->ruleinstances = $instancerecords;
        /*foreach ($instancerecords as $instancerecord) {
            $ruleclass = 'registrationrule_' . $instancerecord->type . '\rule';
            $rule = new $ruleclass();
            $this->instancerecords[$rule->id] = $instancerecord;
        }*/
    }

    /**
     * Get the rule instance records from the DB.
     *
     * @return array Array of rule instance DB records.
    */
    public function get_rule_instance_records(): array {
        return $this->ruleinstances;
    }

    public function add_instance($formdata) {
        global $DB;

        $instance = $this->extract_instancedata($formdata);
        // Ugly hack to get a new sortorder value for now. TODO: fix!
        $instance->sortorder = $DB->count_records('tool_registrationrules') + 1;
        $instance->other = $this->encode_instance_config($formdata);

        $instance->id = $DB->insert_record('tool_registrationrules', $instance);

        $this->ruleinstances[$instance->id] = $instance;
    }

    public function update_instance($formdata) {
        global $DB;

        $record = $DB->get_record('tool_registrationrules', ['id' => $formdata->id]);
        $formdata->type = $record->type;
        // Update default fields in record.
        foreach ($this->extract_instancedata($formdata) as $property => $value) {
            if($property != 'type') {
                $record->{$property} = $value;
            }
        }

        unset($record->timecreated);
        unset($record->sortorder);
        $record->other = $this->encode_instance_config($formdata);
        
        $DB->update_record('tool_registrationrules', $record);
    }

    public function delete_instance(int $instanceid) {
        global $DB;
        $DB->delete_records('tool_registrationrules', ['id' => $instanceid]);
        unset($this->ruleinstances[$instanceid]);
    }

    /**
     * Enable instance.
     *
     * TODO: replace magic value with ENUM or constant!
     *
     * @param int $instanceid
     * @return void
     * @throws \dml_exception
     */
    public function enable_instance(int $instanceid) {
        global $DB;

        $DB->set_field('tool_registrationrules', 'enabled', 1, ['id' => $instanceid]);
        $this->ruleinstances[$instanceid]->enabled = 1;
    }

    public function disable_instance(int $instanceid) {
        global $DB;

        $DB->set_field('tool_registrationrules', 'enabled', 0, ['id' => $instanceid]);
        $this->ruleinstances[$instanceid]->enabled = 0;
    }

    public function move_instance_up(int $instanceid) {
        global $DB;

        if ($instanceid === array_key_first($this->ruleinstances)) {
            // No more moving up!
            return;
        }

        // TODO: finish!
    }

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
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $context = (object)[
            'instances' => [],
            'types' => $this->get_types_for_add_menu(),
        ];

        foreach ($this->ruleinstances as $ruleinstance) {
            $actions = new action_menu([
                new \action_menu_link_primary(
                    url: new \moodle_url(
                        '/admin/tool/registrationrules/edit_rule_instance.php',
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
     * @param $formdata
     * @return stdClass
     */
    private function extract_instancedata($formdata): stdClass {
        global $USER;

        $instance = (object)[
            'type' => $formdata->type,
            'enabled' => $formdata->enabled,
            'sortorder' => $formdata->sortorder,
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
     * @param $formdata
     * @return string
     */
    public function encode_instance_config($formdata): string {
        $extradata = [];
        

        // Class of our rule
        $class = 'registrationrule_' . $formdata->type . '\rule';
        
        if (defined("$class::SETTINGS_FIELDS")) {
            foreach($class::SETTINGS_FIELDS as $field) {
                $extradata[$field] = $formdata->$field;
            }
        }
        return json_encode($extradata);
    }

    public function get_types_for_add_menu(): array {
        $types = [];
        $pluginmanager = \core_plugin_manager::instance();
        $ruletypes = $pluginmanager->get_installed_plugins('registrationrule');
        foreach (array_keys($ruletypes) as $ruleplugin) {
            $types[] = (object)[
                'addurl' => new \moodle_url(
                    '/admin/tool/registrationrules/edit_rule_instance.php',
                    ['addruletype' => $ruleplugin],
                ),
                'name' => $ruleplugin,
            ];
        }
        return $types;
    }
}