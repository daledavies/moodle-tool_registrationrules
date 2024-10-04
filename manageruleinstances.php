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
 * Rule instances management
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use tool_registrationrules\local\rule_instances_controller;

$action = optional_param('action', null, PARAM_PLUGIN);
$confirm = optional_param('confirm', null, PARAM_INT);
$instanceid = optional_param('instanceid', null, PARAM_INT);
$sesskey = optional_param('sesskey', null, PARAM_RAW);

admin_externalpage_setup('toolregistrationrules_instances');

$PAGE->set_context(context_system::instance());

// Create the class for this controller.
$controller = new rule_instances_controller();

// Perform any required actions if we have the correct sesskey.
if ($action !== null && $sesskey) {
    // TODO: move to ruleinstancecontroller.
    switch ($action) {
        case 'moveup':
            require_sesskey();
            $controller->move_instance_up($instanceid);
            break;
        case 'movedown':
            require_sesskey();
            $controller->move_instance_down($instanceid);
            break;
        case 'delete':
            if ($confirm === 1 && confirm_sesskey()) {
                $controller->delete_instance($instanceid);
            }
            break;
        case 'enable':
            require_sesskey();
            $controller->enable_instance($instanceid);
            break;
        case 'disable':
            require_sesskey();
            $controller->disable_instance($instanceid);
            break;
    }
    // Redirect away to minimise CSRF frontend leaking.
    redirect($PAGE->url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('registrationruleinstances', 'tool_registrationrules'));

// If we have been asked to delete an instance but the user has not confirmed then
// display a confirmation step.
if ($action === 'delete' && $confirm === null) {
    $confirmurl = new \moodle_url(
        $PAGE->url,
        [
            'instanceid' => $instanceid,
            'action' => $action,
            'confirm' => 1,
            'sesskey' => sesskey(),
        ]
    );
    $ruleinstance = $controller->get_rule_instance_by_id($instanceid);
    echo $OUTPUT->confirm(
        get_string('confirmdelete', 'tool_registrationrules', $ruleinstance->name),
        $confirmurl,
        $PAGE->url,
        ['continuestr' => get_string('delete')]
    );
} else {
    echo $OUTPUT->render($controller);
}

echo $OUTPUT->footer();
