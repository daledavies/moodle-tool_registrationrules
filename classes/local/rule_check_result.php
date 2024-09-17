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

namespace tool_registrationrules\local;

/**
 * Object to represent the result of a registration rule check.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_check_result {
    /** @var bool true if this result does allow the protected action (e.g. registration) */
    private bool $allowed;

    /** @var string feedback message to be returned based on check's outcome */
    private string $message;

    /** @var string[] validation messages to be displayed in the form based on check's outcome */
    private array $validationmessages;

    /**
     * Rule check result constructor
     *
     * TODO: add score!
     *
     * @param bool $allowed does this rule's check result allow proceeding?
     * @param string $message check result's feedback message
     * @param array $validationmessages check result's validation messages to be displayed appropriately
     */
    public function __construct(bool $allowed, string $message = '', array $validationmessages = []) {
        $this->allowed = $allowed;
        $this->message = $message;
        $this->score = $score;
        $this->validationmessages = $validationmessages;
    }

    /**
     * Get allowance to proceed based on this check result
     *
     * @return bool
     */
    public function get_allowed(): bool {
        return $this->allowed;
    }

    /**
     * Get this check result's feedback message
     *
     * @return string
     */
    public function get_message(): string {
        return $this->message;
    }

    /**
     * Get this check result's form validation messages
     *
     * @return array|string[]
     */
    public function get_validation_messages(): array {
        return $this->validationmessages;
    }
}
