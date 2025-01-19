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
 * Edit a single registration rule instance
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_registrationrules\local\rule_instances_controller;
use tool_registrationrules\local\rule_settings;

require_once(__DIR__ . '/../../../config.php');

// Must be logged in and have moodle/site:config capability.
require_admin();

$ruleinstanceid = optional_param('id', 0, PARAM_INT);
$addruletype = optional_param('addruletype', null, PARAM_ALPHANUM);

$PAGE->set_url('/admin/tool/registrationrules/editruleinstance.php');
$PAGE->set_context(context_system::instance());

if (empty($ruleinstanceid) && empty($addruletype)) {
    throw new coding_exception('Either id of rule instance to be edited, or type of rule instance to be added must be specified.');
}

$managementurl = new moodle_url('/admin/tool/registrationrules/manageruleinstances.php');

$controller = rule_instances_controller::get_instance();

// If we have a rule instance ID supplied then we are editing, otherwise
// we are adding a new rule instance.
if (!empty($ruleinstanceid)) {
    $ruleinstance = $controller->get_rule_instance_by_id($ruleinstanceid);
    $PAGE->set_title(get_string('editruleinstance', 'tool_registrationrules'));
    $PAGE->set_heading(get_string('editruleinstance', 'tool_registrationrules', $ruleinstance->get_name()));
    $mform = rule_settings::from_rule_instance($ruleinstance->get_id());
} else {
    if (!$controller->new_instance_of_type_allowed($addruletype)) {
        redirect(
            $managementurl,
            get_string('onlyoneinstanceallowed', 'tool_registrationrules'),
            null, \core\output\notification::NOTIFY_ERROR
        );
    }
    $PAGE->set_title(get_string('addnewruleinstance', 'tool_registrationrules'));
    $PAGE->set_heading(get_string('addnewruleinstance', 'tool_registrationrules'));
    $mform = new rule_settings($addruletype);
}

if ($mform->is_cancelled()) {
    redirect($managementurl);
}

if ($fromform = $mform->get_data()) {
    // If there is an ID supplied from the hidden form field then we are updating,
    // if not we are adding a new instance.
    if (!empty($fromform->id)) {
        $controller->update_instance($fromform)->commit();
    } else {
        $controller->add_instance($fromform)->commit();
    }
    redirect($managementurl);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
