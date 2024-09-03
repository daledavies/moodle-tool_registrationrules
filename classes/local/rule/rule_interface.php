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
 * Interface for registration rule subplugin classes.
 *
 * @package    tool
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local\rule;

use \tool_registrationrules\local\rule_check_result;

interface rule_interface {

    /**
     * Determines the result of this rule check.
     *
     * @param mixed $data The data to be checked by the rule.
     * @return rule_check_result
     */
    public function get_result($data): rule_check_result;

}