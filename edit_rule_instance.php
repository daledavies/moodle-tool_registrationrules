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
 * @package tool_registrationrules
 * @copyright 2024 eDaktik GmbH {@link https://www.edaktik.at/}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_registrationrules\local\rule_settings;

require_once(__DIR__ . '/../../../config.php');

require_login();

$ruleinstanceid = optional_param('id', 0, PARAM_INT);
$addruletype = optional_param('addruletype', null, PARAM_ALPHANUM);

$PAGE->set_url('/admin/tool/registrationrules/edit_rule_instance.php');
$PAGE->set_context(context_system::instance());

if (empty($ruleinstanceid) && empty($addruletype)) {
    throw new coding_exception('Either id of rule instance to be edited or type of rule instance to add needs to be specified.');
}

if (!empty($ruleinstanceid)) {
    $PAGE->set_title('Edit rule instance');
    // TODO replace with name…
    $PAGE->set_heading('Edit rule instance ' . $ruleinstanceid);
    $mform = rule_settings::from_rule_instance($ruleinstanceid);
} else {
    $PAGE->set_title('Add new rule instance');
    $PAGE->set_heading('Add new rule instance');
    $mform = new rule_settings($addruletype);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/tool/registrationrules/manageruleinstances.php'));
}

if ($fromform = $mform->get_data()) {
    $controller = new \tool_registrationrules\local\ruleinstancescontroller();
    // TODO: Process add/update here and redirect to our management page!

    // TODO: add id and/or addruletype to the hidden form fields!
    if (!empty($fromform->id)) {
        $controller->update_instance($fromform);
        redirect(new moodle_url('/admin/tool/registrationrules/manageruleinstances.php'));
    } else {
        // It's a new registration rule instance.
        $controller->add_instance($fromform);
        redirect(new moodle_url('/admin/tool/registrationrules/manageruleinstances.php'));
    }
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
