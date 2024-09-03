<?php

namespace tool_registrationrules\local;

use core_component;

class rule_checker {
    public static function is_registration_allowed($data = null): rule_check_result {
        $rules = core_component::get_plugin_list_with_class('registrationrule', 'rule');
        foreach ($rules as $ruleplugin => $rule) {
            $instance = new $rule();
            if (!$instance instanceof rule\rule_interface) {
                debugging("Rule $ruleplugin does not implement rule_interface", DEBUG_DEVELOPER);
                continue;
            }

            if ($instance->get_result()) {
                return new rule_check_result(false, "Rule $ruleplugin says no");
            }
        }
        return new rule_check_result(true, 'All rules passed');
    }

}
