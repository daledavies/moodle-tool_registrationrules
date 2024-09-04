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

class rule extends rule_base implements rule_interface {


    public function extend_form($mform): void {
        // TODO: Implement extend_form() method.
    }

    public function pre_data_check(): rule_check_result {
        return new rule_check_result(true, 'Email address is not on disposable-email-domains blocklist', 0);
    }

    public function post_data_check($data): rule_check_result {
        if (!array_key_exists('email', $data)) {
            return new rule_check_result(true, 'No email address provided', 0);
        }

        $email = $data['email'];

        if (strpos($email, '@') === false) {
            return new rule_check_result(true, 'Invalid email address', 0);
        }

        $domain = $this->extract_email_domain($email);

        $listmanager = new list_manager();
        $blockeddomains = $listmanager->get_blocked_domains();
        if (in_array($domain, $blockeddomains)) {
            return new rule_check_result(false, 'Disposable email address not allowed', 100, ['email' => 'Email domain is on a disposable email domain list.']);
        }
        return new rule_check_result(true, 'Email address is not on disposable-email-domains blocklist', 0);
    }

    /**
     * @param $email
     * @return rule_check_result|void
     */
    private function extract_email_domain($email) {
        $parts = explode('@', $email);
        return end($parts);
    }
    public static function extend_settings_form($mform) {
        $mform->addElement('static', 'test', 'Additional Settings', 'This rule type does not provide additional settings.');
    }
}
