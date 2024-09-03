<?php

use tool_registrationrules\local\rule_checker;

function tool_registrationrules_pre_signup_requests() {
    $result = rule_checker::is_registration_allowed();
    if ($result->allowed) {
        return;
    }

    redirect(new moodle_url('/admin/tool/registrationrules/error.php', ['message' => $result->message]));
}

function tool_registrationrules_post_signup_requests($data) {
    $result = rule_checker::is_registration_allowed($data);
    if ($result->allowed) {
        return;
    }

    redirect(new moodle_url('/admin/tool/registrationrules/error.php', ['message' => $result->message]));
}

function tool_registrationrules_check_password_policy($password, $user) {
    // Doesn't appear to be called - because passwordpolicy is set to off?
}

function tool_registrationrules_validate_extend_signup_form($data) {
    return ['username' => 'No usernames are allowed'];
}
