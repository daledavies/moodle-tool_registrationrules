<?php

namespace registrationrule_nope;

use \tool_registrationrules\local\rule_check_result;

class rule implements \tool_registrationrules\local\rule\rule_interface {

    /**
     * Determines the result of this rule check.
     *
     * @param mixed $data The data to be checked by the rule.
     * @return rule_check_result
     */
    public function get_result($data): rule_check_result {
        return new rule_check_result(true, 'Sorry, but nope!', 50);
    }

}
