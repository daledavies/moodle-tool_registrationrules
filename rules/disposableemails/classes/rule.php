<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace registrationrule_disposableemails;

use moodle_exception;
use registrationrule_disposableemails\local\list_manager;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule\rule_base;
use tool_registrationrules\local\rule_check_result;

/**
 * Registration rule based on usage of disposable mail addresses.
 *
 * @package   registrationrule_disposableemails
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule extends rule_base implements post_data_check {
    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     * @throws moodle_exception
     */
    public function post_data_check(array $data): rule_check_result {
        if (!array_key_exists('email', $data)) {
            return null;
        }

        $email = $data['email'];

        // Proper validation for email addresses should be handled by the
        // form instance, but we'll check here anyway and return null if this
        // isn't actually an email address.
        if (validate_email($email) === false) {
            return null;
        }

        // Get just the domain part of the user's email address.
        $domain = $this->extract_email_domain($email);

        // Get an instance of list_manager so we can begin checking the user's
        // email domain against a list of disposable email domains.
        $listmanager = new list_manager();

        // Retrieve a list of disposable email domains, if anything goes wrong while
        // retrieving the list then return a fail with the configured fallback points.
        try {
            $blockeddomains = $listmanager->get_blocked_domains();
        } catch (moodle_exception) {
            return $this->deny(
                score: $this->config->fallbackpoints,
                feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_disposableemails')
            );
        }

        // If we get a list of domains then check the provided domain and
        // return a failure with the configured score if the email domain is
        // on the list.
        if (in_array($domain, $blockeddomains)) {
            return $this->deny(
                score: $this->config->points,
                validationmessages: ['email' => get_string('failuremessage', 'registrationrule_disposableemails')],
            );
        }

        // If the email domain is not on the list the return a success result.
        return $this->allow();
    }

    /**
     * Extract domain part of the given mail address.
     *
     * @param string $email
     * @return false|string
     */
    private function extract_email_domain(string $email) {
        $parts = explode('@', $email);

        return end($parts);
    }
}
