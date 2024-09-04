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
 * Reference implementation of a registration rule subplugin.
 *
 * @package    registrationrule
 * @subpackage nope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace registrationrule_nope;

use \tool_registrationrules\local\rule_check_result;

class rule implements \tool_registrationrules\local\rule\rule_interface {

    /**
     * Determines the result of this rule check.
     *
     * @param mixed $data The data to be checked by the rule.
     * @return rule_check_result
     */
    public function post_data_check($data): rule_check_result {
        return new rule_check_result(true, 'Sorry, but nope!', 50);
    }

}
