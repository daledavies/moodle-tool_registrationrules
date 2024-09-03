<?php

namespace tool_registrationrules\local;

use core_component;

class rule_checker {
    public static function is_registration_allowed($data = null): rule_check_result {
        return new rule_check_result(false, 'No new registrations allowed');
    }

}
