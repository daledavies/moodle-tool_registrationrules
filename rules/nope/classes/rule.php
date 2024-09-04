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

class rule extends \tool_registrationrules\local\rule\rule_base {

    public function __construct() {
        // Do something with config
        $config = null;
        parent::__construct($config);
    }

    public function post_data_check($data): rule_check_result {
        return new rule_check_result(false, 'Nope', 50);
    }

    public function pre_data_check(): rule_check_result {
        return new rule_check_result(true, 'Double nope!');
    }

    public function extend_form($mform): void {
    }

}
