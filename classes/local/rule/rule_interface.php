<?php

namespace tool_registrationrules\local\rule;

interface rule_interface {

    /**
     * Returns true if rule determines spam.
     *
     * @return bool
     */
    public function get_result();

}
