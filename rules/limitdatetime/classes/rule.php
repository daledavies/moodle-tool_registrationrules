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

namespace registrationrule_limitdatetime;

use coding_exception;
use MoodleQuickForm;
use stdClass;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\rule_trait;
use tool_registrationrules\local\rule\pre_data_check;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule_check_result;

/**
 * Reference implementation of a registration rule subplugin.
 *
 * @package   registrationrule_limitdatetime
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, pre_data_check, instance_configurable {
    use rule_trait;

    /** @var stdClass rule plugin instance config. */
    protected stdClass $instanceconfig;

    /**
     * Set rule instance config object.
     *
     * @param stdClass $instanceconfig
     * @return void
     */
    public function set_instance_config(stdClass $instanceconfig): void {
        $this->instanceconfig = $instanceconfig;
    }

    /**
     * Get rule instance config object.
     *
     * @return stdClass
     */
    public function get_instance_config(): stdClass {
        return $this->instanceconfig;
    }



    /**
     * Return an array of settings fields names used to extend the instance
     * settings form via extend_settings_form().
     *
     * @return array
     */
    public static function get_instance_settings_fields(): array {
        return ['limitdatetime_from', 'limitdatetime_to'];
    }

    /**
     * Inject rule type specific settings into basic rule settings form if the type needs additional configuration.
     *
     * @param MoodleQuickForm $mform
     * @return void
     * @throws coding_exception
     */
    public static function extend_settings_form(MoodleQuickForm $mform): void {
        $mform->addElement('date_time_selector', 'limitdatetime_from', get_string('from'));

        $mform->addElement('date_time_selector', 'limitdatetime_to', get_string('to'));
    }

    /**
     * Perform rule's checks applicable without any user input before the signup form is displayed.
     *
     * @return rule_check_result A rule_check_result object.
     */
    public function pre_data_check(): rule_check_result {
        $now = time();

        /* A wizard is never late, nor is he early,
         * he arrives precisely when he means to.
         *
         * Just like users don't.
         * TODO: check timezone used in settings and maybe explain about used timezone as hint in UI?
         */
        $tooearly = $now < $this->get_instance_config()->limitdatetime_from;
        $toolate = $now > $this->get_instance_config()->limitdatetime_to;

        if ($tooearly || $toolate) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_limitdatetime'),
            );
        }

        return $this->allow();
    }

}
