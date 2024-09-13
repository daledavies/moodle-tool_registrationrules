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

use registrationrule_disposableemails\local\list_manager;
use tool_registrationrules\local\rule\rule_base;
use tool_registrationrules\local\rule\rule_interface;
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
class rule extends rule_base implements rule_interface {
    public function pre_data_check(): ?rule_check_result {
        return null;
    }

    public function post_data_check($data): ?rule_check_result {
        if (!array_key_exists('email', $data)) {
            return null;
        }

        $email = $data['email'];

        if (strpos($email, '@') === false) {
            return new rule_check_result(true, 'Invalid email address', 0);
        }

        $domain = $this->extract_email_domain($email);

        $listmanager = new list_manager();
        $blockeddomains = $listmanager->get_blocked_domains();
        if (in_array($domain, $blockeddomains)) {
            return new rule_check_result(false, '', ['email' => 'Email domain is on a disposable email domain list.']);
        }
        return new rule_check_result(true);
    }

    /**
     * @param $email
     * @return false|string
     */
    private function extract_email_domain($email) {
        $parts = explode('@', $email);
        return end($parts);
    }
}
