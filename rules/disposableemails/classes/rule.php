<?php

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

        $list_manager = new list_manager();
        $blockeddomains = $list_manager->get_blocked_domains();
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
}
