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
 * Registration rules admin tool lib.php
 *
 * @package    tool
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
