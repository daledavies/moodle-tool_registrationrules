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
    private static rule_checker $instance;

    /**
     * @var array<rule\rule_interface>
     */
    private array $rules;
    private array $results;
    private ?\stdClass $adminconfig = null;

    private bool $checked = false;

    public static function get_instance(): rule_checker {
        if (!isset(self::$instance)) {
            self::$instance = new rule_checker();
        }
        return self::$instance;
    }

    private function __construct() {
        global $DB;
        $this->adminconfig = $this->get_admin_config();

        $instances = $DB->get_records('tool_registrationrules');

        foreach ($instances as $instance) {
            $ruleplugins = core_component::get_plugin_list_with_class('registrationrule', 'rule');
            foreach ($ruleplugins as $ruleplugin => $rule) {
                $disabled = get_config($ruleplugin, 'disabled');
                if ($disabled) {
                    continue;
                }
                if ($instance->type == str_replace('registrationrule_', '', $ruleplugin)) {
                    // TODO: Replace dummy config with actual config.
                    $ruleinstance = new $rule($instance);
                    if (!$ruleinstance instanceof rule\rule_base) {
                        debugging("Rule $ruleplugin does not extend rule_base", DEBUG_DEVELOPER);
                        continue;
                    }
                    $this->rules[] = $ruleinstance;
                }
            }
        }
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
            $this->results[] = $instance->pre_data_check();
        }
        $this->checked = true;
    }

    public function run_post_data_checks($data) {
        foreach ($this->rules as $instance) {
            $this->results[] = $instance->post_data_check($data);
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
            if ($result->get_allowed()) {
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
            foreach ($result->get_validation_messages() as $field => $message) {
                // Note that this will overwrite any previous message for the same field.
                // We may want to consider some kind of aggregation here.
                $messages[$field] = $message;
            }
        }
        return $messages;
    }

}
