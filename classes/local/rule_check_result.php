<?php

namespace tool_registrationrules\local;

class rule_check_result {
    private bool $allowed;
    private string $message;
    private int $score;

    public function __construct(bool $allowed, string $message, int $score) {
        $this->allowed = $allowed;
        $this->message = $message;
        $this->score = $score;
    }

    public function get_allowed(): bool {
        return $this->allowed;
    }

    public function get_message(): string {
        return $this->message;
    }

    public function get_score(): int {
        return $this->score;
    }
}
