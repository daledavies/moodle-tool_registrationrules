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
 * Object to represent the result of a registration rule check.
 *
 * @package    tool
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local;

class rule_check_result {
    private bool $allowed;
    private string $message;
    private int $score;
    private array $validationmessages;

    public function __construct(bool $allowed, string $message = '', int $score = 100, array $validationmessages = []) {
        $this->allowed = $allowed;
        $this->message = $message;
        $this->score = $score;
        $this->validationmessages = $validationmessages;
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

    public function get_validation_messages(): array {
        return $this->validationmessages;
    }
}
