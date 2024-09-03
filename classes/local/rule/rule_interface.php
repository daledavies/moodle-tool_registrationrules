<?php

namespace tool_registrationrules\local\rule;

use \tool_registrationrules\local\rule_check_result;

interface rule_interface {

    /**
     * Determines the result of this rule check.
     *
     * @param mixed $data The data to be checked by the rule.
     * @return rule_check_result
     */
    public function get_result($data): rule_check_result;

}
