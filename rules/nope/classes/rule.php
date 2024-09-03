<?php

namespace registrationrule_nope;

class rule implements \tool_registrationrules\local\rule\rule_interface {

    /**
     * Returns true if rule determines spam.
     *
     * @return bool
     */
    public function get_result() {
        return true;
    }

}
