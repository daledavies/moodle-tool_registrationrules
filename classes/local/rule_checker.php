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
 * Facilitate exicution of registration rule plugins and enumeration/reposrting
 * of results.
 *
 * @package    tool
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local;

use core_component;

class rule_checker {
    private array $instances;
    private array $results;

    public function __construct() {
        $rules = core_component::get_plugin_list_with_class('registrationrule', 'rule');
        foreach ($rules as $ruleplugin => $rule) {
            $instance = new $rule();
            if (!$instance instanceof rule\rule_interface) {
                debugging("Rule $ruleplugin does not implement rule_interface", DEBUG_DEVELOPER);
                continue;
            }
            $this->instances[] = $instance;
        }
    }

    public function get_instances(): array {
        return $this->instances;
    }

    public function check($data = null) {
        foreach ($$this->instances as $instance) {
            $results[] = $instance->get_results($data);
        }
    }

    public function is_registration_allowed(): bool {
        if ($this->is_checked()) {
            throw new \coding_exception('rule_checker::check() must be called before using rule_checker::is_registration_allowed()');
        }
        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                return false;
            }
        }
        return true;
    }

    private function is_checked(): bool {
        return !empty($this->results);
    }
}
