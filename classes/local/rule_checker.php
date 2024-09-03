<?php

namespace tool_registrationrules\local;

use core_component;

class rule_checker {
    private array $instances;
    private array $results;

    public function __construct() {
        $rules = core_component::get_plugin_list_with_class('registrationrule', 'rule');
        foreach ($rules as $ruleplugin => $rule) {
            $instance = new $rule();
            if (!$instance instanceof rule\rule_interface) {
                debugging("Rule $ruleplugin does not implement rule_interface", DEBUG_DEVELOPER);
                continue;
            }
            $this->instances[] = $instance;
        }
    }

    public function get_instances(): array {
        return $this->instances;
    }

    public function check($data = null) {
        foreach ($$this->instances as $instance) {
            $results[] = $instance->get_results($data);
        }
    }

    public function is_registration_allowed(): bool {
        if ($this->is_checked()) {
            throw new \coding_exception('rule_checker::check() must be called before using rule_checker::is_registration_allowed()');
        }
        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                return false;
            }
        }
        return true;
    }

    private function is_checked(): bool {
        return !empty($this->results);
    }
}
