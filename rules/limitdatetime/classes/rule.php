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

namespace registrationrule_limitdatetime;

use \tool_registrationrules\local\rule_check_result;


class rule extends \tool_registrationrules\local\rule\rule_base {
    public \StdClass $config;
    
    const SETTINGS_FIELDS = ['limitdatetime_from', 'limitdatetime_to'];
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public static function extend_settings_form($mform): void {
        $mform->addElement('date_time_selector', 'limitdatetime_from', get_string('from'));
        
        $mform->addElement('date_time_selector', 'limitdatetime_to', get_string('to'));
    }
    
    public function pre_data_check(): rule_check_result {
        $now = time();
        
        return new rule_check_result(($now < $this->config->limitdatetime_from || $now > $this->config->limitdatetime_to), 'Outside date');
    }
    
    public function post_data_check($data): ?rule_check_result { return null; }
}

