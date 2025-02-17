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

namespace tool_registrationrules\local\logger;

use tool_registrationrules\local\rule\rule_interface;

/**
 * Log info object for rules to return with a result.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class log_info {
    /** @var rule_interface Rule instance object related to this log info. */
    private rule_interface $ruleinstance;

    /** @var null|string Optional additional information to include in log. */
    private ?string $additionalinfo;

    /**
     * Construct a new log info object.
     *
     * @param rule_interface $ruleinstance The rule instance object related to this log info.
     * @param string|null $additionalinfo Optional additional information to include in log.
     */
    public function __construct(rule_interface $ruleinstance, ?string $additionalinfo = null) {
        $this->ruleinstance = $ruleinstance;
        $this->additionalinfo = $additionalinfo;
    }

    /**
     * Get the rule instance type associated with this log info.
     *
     * @return string
     */
    public function get_rule_type(): string {
        return $this->ruleinstance->get_type();
    }

    /**
     * Get the rule instance name associated with this log info.
     *
     * @return string
     */
    public function get_rule_name(): string {
        return get_string('pluginname', 'registrationrule_' . $this->ruleinstance->get_type());
    }

    /**
     * Get the number of points set for the rule instance.
     *
     * @return integer
     */
    public function get_rule_points(): int {
        return $this->ruleinstance->get_points();
    }

    /**
     * Get the rule instance ID for this log info.
     *
     * @return int rule instance ID
     */
    public function get_rule_instance_id(): int {
        return $this->ruleinstance->get_id();
    }

    /**
     * Get the additional information added by the rule.
     *
     * @return string
     */
    public function get_additional_info() {
        return $this->additionalinfo;
    }
}
