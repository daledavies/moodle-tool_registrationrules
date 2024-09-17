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
 * Facilitate exicution of registration rule plugins and enumeration/reposrting
 * of results.
 *
 * @package    tool_registrationrules
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local;

use core_component;

class rule_checker {
    private static array $instances = [];

    /**
     * @var array<rule\rule_interface>
     */
    private array $rules;
    private array $results;
    private ?\stdClass $adminconfig = null;

    private bool $checked = false;

    public static function get_instance($type): rule_checker {
        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = new rule_checker();
        }
        return self::$instances[$type];
    }

    private function __construct() {
        $this->clear();
        $this->adminconfig = $this->get_admin_config();

        $instances = (new \tool_registrationrules\local\rule_instances_controller())->get_rule_instance_records();

        foreach ($instances as $instance) {
            if (get_config('registrationrule_' . $instance->type, 'disabled')) {
                continue;
            }

            $pluginrule = 'registrationrule_' . $instance->type . '\rule';

            // Parse additional config and add to instance.
            foreach(json_decode($instance->other) as $configkey => $configvalue) {
                $instance->$configkey = $configvalue;
            }
            $ruleinstance = new $pluginrule($instance);
            if (!$ruleinstance instanceof rule\rule_base) {
                debugging("Rule $ruleplugin does not extend rule_base", DEBUG_DEVELOPER);
                continue;
            }
            $this->rules[] = $ruleinstance;

        }
    }

    public function clear() {
        $this->rules = [];
        $this->results = [];
        $this->checked = false;
    }

    /**
     * Load and cache the admin config for this module.
     *
     * @return stdClass the plugin config
     */
    public function get_admin_config() {
        if ($this->adminconfig) {
            return $this->adminconfig;
        }
        $this->adminconfig = get_config('tool_registrationrules');
        return $this->adminconfig;
    }

    public function get_rules(): array {
        return $this->rules;
    }

    public function run_pre_data_checks() {
        foreach ($this->rules as $instance) {
            $result = $instance->pre_data_check();
            
            // Ignore rules without post data check.
            if ($result !== null) {
                $this->results[] = $result;
            }
        }
        $this->checked = true;
    }

    public function run_post_data_checks($data) {
        foreach ($this->rules as $instance) {
            $result = $instance->post_data_check($data);

            // Ignore rules without post data check.
            if ($result !== null) {
                $this->results[] = $result;
            }
        }
        
        $this->checked = true;
    }

    public function extend_form($mform) {
        foreach ($this->rules as $instance) {
            $instance->extend_form($mform);
        }
    }

    public function is_registration_allowed(): bool {
        if (!($this->adminconfig->enable ?? 0)) {
            return true;
        }
        if (!$this->checked) {
            throw new \coding_exception('rule_checker::check() must be called before using rule_checker::is_registration_allowed()');
        }

        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                return false;
            }
        }

        return true;
    }

    public function get_messages(): array {
        if (!$this->checked) {
            throw new \coding_exception('rule_checker::check() must be called before using rule_checker::get_messages()');
        }
        $messages = [];
        foreach ($this->results as $result) {
            
            // If not allowed add error message.
            if (!$result->get_allowed()) {
                continue;
            }
            $messages[] = $result->get_message();
        }
        return $messages;
    }

    public function get_validation_messages(): array {
        if (!$this->checked) {
            throw new \coding_exception('rule_checker::check() must be called before using rule_checker::get_validation_messages()');
        }
        $messages = [];
        
        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                foreach ($result->get_validation_messages() as $field => $message) {
                    // Note that this will overwrite any previous message for the same field.
                    // We may want to consider some kind of aggregation here.
                    $messages[$field] = $message;
                }
                
                // General messages.
                if (!empty($result->get_message())) {
                    if (!isset($messages['tool_registrationrules_errors'])) {
                        $messages['tool_registrationrules_errors'] = '';
                    }
                    $messages['tool_registrationrules_errors'] .= '<br />' . $result->get_message();
                }
            }
        }
        return $messages;
    }
    
    public function add_error_field($mform) {
        $mform->addElement('static', 'tool_registrationrules_errors');
    }
}
