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

/**
 * Example of use in callback without passing data to rule_checker::check().
 *
 * @return void
 */
function tool_registrationrules_pre_signup_requests() {
    $rule_checker = rule_checker::get_instance();
    $rule_checker->run_pre_data_checks();
    if ($rule_checker->is_registration_allowed()) {
        return;
    }
    $messages = implode('<br>', $rule_checker->get_messages());
    \core\notification::warning($messages);
    redirect(
        new moodle_url(
            '/admin/tool/registrationrules/error.php',
            ['ver' =>'before']
        )
    );
}

/**
 * Add general information about registration rules.
 *
 * @param MoodleQuickForm $mform
 * @return void
 */
function tool_registrationrules_extend_signup_form($mform): void {
    //$mform->addElement('hidden', 'registrationrules_information', '');
 }


function tool_registrationrules_validate_extend_signup_form($data) {
    $rule_checker = rule_checker::get_instance();
    $rule_checker->run_post_data_checks($data);
    if ($rule_checker->is_registration_allowed()) {
        return;
    }
    $messages = implode('<br>', $rule_checker->get_messages());
    \core\notification::warning($messages);
    redirect(
        new moodle_url(
            '/admin/tool/registrationrules/error.php',
            ['ver' =>'after']
        )
    );
}

