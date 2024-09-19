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

namespace registrationrule_nope;

use tool_registrationrules\local\rule\configurable;
use tool_registrationrules\local\rule_check_result;

/**
 * Reference implementation of a registration rule subplugin.
 *
 * @package   registrationrule_nope
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule extends \tool_registrationrules\local\rule\rule_base
    implements configurable {

    private \stdClass $config;

    public function __construct($config) {
        $this->config = $config;
        parent::__construct($config);
    }

    public function post_data_check($data): rule_check_result {
        return new rule_check_result(false, 'Nope');
    }

    public function pre_data_check(): rule_check_result {
        return new rule_check_result(true);
    }

    public function extend_form($mform): void {
    }

    public static function extend_settings_form($mform) {
        $mform->addElement(
            'static',
            'test',
            'Additional Settings',
            'This rule type does not provide additional settings.',
        );
    }
}
