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
 * @package    tool_registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use tool_registrationrules\local\ruleinstancescontroller;

$action = optional_param('action', null, PARAM_PLUGIN);
$instanceid = optional_param('instanceid', null, PARAM_INT);

// Create the class for this controller.
$controller = new ruleinstancescontroller();

$PAGE->set_context(context_system::instance());


// TODO: move to ruleinstancecontroller.
switch ($action) {
    case 'moveup':
        $controller->move_instance_up($instanceid);
        break;
    case 'movedown':
        $controller->move_instance_down($instanceid);
        break;
    case 'delete':
        $controller->delete_instance($instanceid);
        break;
    case 'enable':
        $controller->enable_instance($instanceid);
        break;
    case 'disable':
        $controller->disable_instance($instanceid);
        break;
}

echo $OUTPUT->header();

echo $OUTPUT->render($controller);

echo $OUTPUT->footer();
