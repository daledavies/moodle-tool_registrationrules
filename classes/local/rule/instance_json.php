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

namespace tool_registrationrules\local\rule;

use JsonSerializable;
use stdClass;

/**
 * Class for serialising a rule instance as JSON.
 *
 * @package tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_json implements JsonSerializable {
    /** @var array Properties all serialised json instance objects will definitely have. */
    public const EXPECTED_PROPERTIES = [
        'type',
        'enabled',
        'name',
        'description',
        'points',
        'fallbackpoints',
    ];

    /** @var stdClass $instancedata the instance data object to be serialised as json. */
    public stdClass $instancedata;

    /**
     * Extract the required data from the supplied rule instance object.
     *
     * @param rule_interface $ruleinstance
     */
    public function __construct(rule_interface $ruleinstance) {
        $this->instancedata = new stdClass();
        $this->instancedata->type = $ruleinstance->get_type();
        $this->instancedata->enabled = $ruleinstance->get_enabled();
        $this->instancedata->name = $ruleinstance->get_name();
        $this->instancedata->description = $ruleinstance->get_description();
        $this->instancedata->points = $ruleinstance->get_points();
        $this->instancedata->fallbackpoints = $ruleinstance->get_fallbackpoints();
        if ($ruleinstance instanceof instance_configurable) {
            $this->instancedata->instanceconfig = $ruleinstance->get_instance_config();
        }
    }

    /**
     * As we implement the JsonSerializable interface, whatever is returned here will be serialised
     * as json when this object is passed to json_encode.
     *
     * @return mixed
     */
    public function jsonSerialize(): stdClass {
        return $this->instancedata;
    }
}
