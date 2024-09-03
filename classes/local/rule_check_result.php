<?php

namespace tool_registrationrules\local;

class rule_check_result {
    public $allowed;
    public $message;

    public function __construct($allowed, $message) {
        $this->allowed = $allowed;
        $this->message = $message;
    }
}
