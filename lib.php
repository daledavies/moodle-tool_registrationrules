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

use tool_registrationrules\local\rule_checker;

/**
 * Example of use in callback without passing data to rule_checker::check().
 *
 * @return void
 */
function tool_registrationrules_pre_signup_requests() {
    $rulechecker = rule_checker::get_instance('signup_form');

    $rulechecker->run_pre_data_checks();
    if ($rulechecker->is_registration_allowed()) {
        return;
    }

    $messages = implode('<br>', $rulechecker->get_messages());
    \core\notification::warning($messages);
    redirect(
        new moodle_url(
            '/admin/tool/registrationrules/error.php',
            ['ver' => 'before'],
        ),
    );
    exit;
}

/**
 * Add general information about registration rules.
 *
 * @param MoodleQuickForm $mform
 * @return void
 */
function tool_registrationrules_extend_signup_form($mform): void {
    $mform->insertElementBefore(
        $mform->createElement(
            'static',
            'registrationrules_information',
            get_string('pluginname', 'tool_registrationrules'),
            get_config('tool_registrationrules', 'registrationpagemessage'),
        ),
        'username',
    );
    $rule_checker = rule_checker::get_instance('signup_form');
    $rule_checker->add_error_field($mform);
    $rule_checker->extend_form($mform);
}

function tool_registrationrules_validate_extend_signup_form($data) {
    $rulechecker = rule_checker::get_instance('signup_form');
    $rulechecker->run_post_data_checks($data);

    if ($rulechecker->is_registration_allowed()) {
        return [];
    }

    return $rulechecker->get_validation_messages();

    return ['tool_registrationrules_errors' => implode('<br />', $errors)];
}
