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

namespace tool_registrationrules\local\rule;

use MoodleQuickForm;
use tool_registrationrules\local\rule_check_result;

/**
 * Rule plugins must implement this interface if they wish to extend the
 * forgot password form.
 *
 * @package   tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface extend_forgot_password_form {
    /**
     * Inject additional fields into the forgot password form.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_forgot_password_form(MoodleQuickForm $mform): void;

    /**
     * Perform rule's checks after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function validate_forgot_password_form(array $data): rule_check_result;

    /**
     * Is this rule enabled for the forgotten password form?
     *
     * @return bool true if enabled.
     */
    public function get_forgotpasswordenabled(): bool;

    /**
     * Enable the rule for the forgotten password form.
     *
     * @param bool $forgotpasswordenabled
     * @return bool
     */
    public function set_forgotpasswordenabled(bool $forgotpasswordenabled): bool;
}
