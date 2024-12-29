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

use coding_exception;
use stdClass;

/**
 * Factory class for creating rule instance objects.
 *
 * @package tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_factory {
    /**
     * Creates an instance of an appropriate rule plugin class from a raw DB record, performs
     * required validation etc.
     *
     * @param stdClass $dbrecord Raw DB record from tool_registrationrules table.
     * @return rule_interface Rule plugin class instance, configured where appropriate.
     */
    public static function create_instance_from_record(stdClass $dbrecord): rule_interface {
        $ruleclass = 'registrationrule_' . $dbrecord->type . '\rule';

        // Create a new instance of the rule plugin.
        $ruleinstance = new $ruleclass();

        // The rule plugin must at least implement rule_interface so we know
        // expect later on.
        if (!$ruleinstance instanceof rule_interface) {
            throw new coding_exception("Rule $ruleclass does not implement rule_interface", DEBUG_DEVELOPER);
        }

        // Add basic params applicable to all rule class types.
        $ruleinstance->set_id($dbrecord->id);
        $ruleinstance->set_type($dbrecord->type);
        $ruleinstance->set_enabled($dbrecord->enabled);
        $ruleinstance->set_name($dbrecord->name);
        $ruleinstance->set_description($dbrecord->description);
        $ruleinstance->set_points($dbrecord->points);
        $ruleinstance->set_fallbackpoints($dbrecord->fallbackpoints);
        $ruleinstance->set_sortorder($dbrecord->sortorder);

        // If the rule instance allows configuration then decode and validate config json.
        if ($ruleinstance instanceof instance_configurable) {
            // Decode raw json from DB record containing instance related config object.
            $instanceconfig = json_decode($dbrecord->other);
            if ($instanceconfig === null) {
                throw new coding_exception('Instance config JSON could not be decoded');
            }
            // Validate that the supplied instance config contains the fields defined by
            // get_instance_settings_fields().
            $fielddiff = array_diff(
                array_values($ruleinstance::get_instance_settings_fields()),
                array_keys((array) $instanceconfig)
            );
            if ($fielddiff) {
                throw new coding_exception('Instance config must contain the fields defined by get_instance_settings_fields()');
            }
            // Finally add the validated config object to the instance.
            $ruleinstance->set_instance_config($instanceconfig);
        }

        return $ruleinstance;
    }
}
