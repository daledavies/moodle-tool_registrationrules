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
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_registrationrules\event\registration_denied;
use tool_registrationrules\event\registration_denied_logging_only;
use tool_registrationrules\local\helpers;
use tool_registrationrules\local\rule_checker;

/**
 * Run the pre-registration checks from any instances that define them and
 * output an error page if any checks deny registration.
 *
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function tool_registrationrules_pre_signup_requests() {
    global $OUTPUT, $PAGE;
    // Exit if the plugin is not enabled.
    if (!get_config('tool_registrationrules', 'enable')) {
        return;
    }
    // Run the pre-registration checks from any instances that define them.
    $rulechecker = rule_checker::get_instance('signup_form');
    $rulechecker->run_pre_data_checks();
    if ($rulechecker->is_registration_allowed()) {
        return;
    }
    // The loggingonly setting has been enabled so create an event but do not
    // deny registration.
    if (get_config('tool_registrationrules', 'loggingonly')) {
        registration_denied_logging_only::log_event($rulechecker->get_logger());
        return;
    }
    // If we get here then registration is not allowed. So first create an event to
    // store log information from rules that denied registration.
    registration_denied::log_event($rulechecker->get_logger());

    // Display error page and exit.
    helpers::error_page(get_string('newaccount'), $rulechecker->get_feedback_messages_string());
}

/**
 * Add general information about registration rules.
 *
 * @param MoodleQuickForm $mform
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function tool_registrationrules_extend_signup_form($mform): void {
    // Exit if the plugin is not enabled.
    if (!get_config('tool_registrationrules', 'enable')) {
        return;
    }
    $registrationpagemessage = get_config('tool_registrationrules', 'registrationpagemessage');
    if ($registrationpagemessage) {
        $mform->insertElementBefore(
            $mform->createElement(
                'static', 'registrationrules_information', '',
                format_text($registrationpagemessage, FORMAT_HTML),
            ),
            'username',
        );
    }
    $rulechecker = rule_checker::get_instance('signup_form');
    $rulechecker->add_error_field($mform);
    $rulechecker->extend_signup_form($mform);
}

/**
 * Inject our own rule instance based validation into the signup form.
 *
 * @param array $data
 * @return string[]
 * @throws coding_exception
 */
function tool_registrationrules_validate_extend_signup_form($data): array {
    // Plugin not enabled.
    if (!get_config('tool_registrationrules', 'enable')) {
        return [];
    }

    $rulechecker = rule_checker::get_instance('signup_form');
    $rulechecker->run_post_data_checks($data);

    if ($rulechecker->is_registration_allowed()) {
        return [];
    }

    // The loggingonly setting has been enabled so create an event but do not
    // deny registration.
    if (get_config('tool_registrationrules', 'loggingonly')) {
        registration_denied_logging_only::log_event($rulechecker->get_logger());
        return [];
    }
    // If we get here then registration is not allowed. So first create an event to
    // store log information from rules that denied registration.
    registration_denied::log_event($rulechecker->get_logger());

    // Then return an array of messages to trigger a validation failure on the
    // registration form.
    return $rulechecker->get_all_messages();
}

/**
 * Allow rules to extend the forgot password form.
 *
 * @param MoodleQuickForm $mform
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
function tool_registrationrules_extend_forgot_password_form($mform): void {
    // Plugin not enabled.
    if (!get_config('tool_registrationrules', 'enable')) {
        return;
    }

    $rulechecker = rule_checker::get_instance('forgot_password_form');
    $rulechecker->add_error_field($mform);
    $rulechecker->extend_forgot_password_form($mform);
}

/**
 * Inject our own rule instance based validation into the forgot password form.
 *
 * @param array $data
 * @return string[]
 * @throws coding_exception
 */
function tool_registrationrules_validate_extend_forgot_password_form($data): array {
    // Plugin not enabled.
    if (!get_config('tool_registrationrules', 'enable')) {
        return [];
    }

    $rulechecker = rule_checker::get_instance('forgot_password_form');
    $rulechecker->validate_forgot_password_form($data);

    if ($rulechecker->is_registration_allowed()) {
        return [];
    }

    // The loggingonly setting has been enabled so create an event but do not
    // deny registration.
    if (get_config('tool_registrationrules', 'loggingonly')) {
        registration_denied_logging_only::log_event($rulechecker->get_logger());
        return [];
    }
    // If we get here then registration is not allowed. So first create an event to
    // store log information from rules that denied registration.
    registration_denied::log_event($rulechecker->get_logger());

    // Then return an array of messages to trigger a validation failure on the
    // registration form.
    return $rulechecker->get_all_messages();
}
