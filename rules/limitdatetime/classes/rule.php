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
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\rule_trait;
use tool_registrationrules\local\rule\pre_data_check;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule\multiple_instances;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule_checker;
use tool_registrationrules\local\rule_instances_controller;

/**
 * Restrict user registration around date/time windows.
 *
 * @package   registrationrule_limitdatetime
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, pre_data_check, instance_configurable, multiple_instances {
    use rule_trait;

    /** @var stdClass rule plugin instance config. */
    protected stdClass $instanceconfig;

    /** @var string Only allow registration inside the time window */
    protected const OPTION_ALLOW_BETWEEN_DATES = 'allowbetween';

    /** @var string Only allow registration outside the time window */
    protected const OPTION_DENY_BETWEEN_DATES = 'denybetween';

    /** @var int $startdate Unix timestamp representing configured startdate value */
    private int $startdate;

    /** @var int $enddate Unix timestamp representing configured enddate value */
    private int $enddate;

    /** @var string $restrictionmode Configured restriction mode */
    private string $restrictionmode;

    /** @var array $stringparams The "from" and "to" params representing localised dates for use in strings */
    private array $stringparams;

    /**
     * Get the rule instance's description.
     *
     * @return string the rule instance description.
     */
    public function get_display_description(): string {
        if ($this->restrictionmode === static::OPTION_ALLOW_BETWEEN_DATES) {
            $desc = get_string('failuremessage:allowbetween', 'registrationrule_limitdatetime', $this->stringparams);
        } else {
            $desc = get_string('failuremessage:denybetween', 'registrationrule_limitdatetime', $this->stringparams);
        }
        return !$this->description ? $desc : $this->description;
    }

    /**
     * Set rule instance config object.
     *
     * @param stdClass $instanceconfig
     * @return void
     */
    public function set_instance_config(stdClass $instanceconfig): void {
        $this->instanceconfig = $instanceconfig;
        $this->startdate = $instanceconfig->limitdatetime_from;
        $this->enddate = $instanceconfig->limitdatetime_to;
        $this->restrictionmode = $instanceconfig->restrictionmode;
        $this->stringparams = [
            'from' => userdate($this->startdate),
            'to' => userdate($this->enddate),
        ];
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
        return ['limitdatetime_from', 'limitdatetime_to', 'restrictionmode'];
    }

    /**
     * Inject rule type specific settings into basic rule settings form if the type needs additional configuration.
     *
     * @param MoodleQuickForm $mform
     * @return void
     * @throws coding_exception
     */
    public static function extend_settings_form(MoodleQuickForm $mform): void {
        $mform->addElement('date_time_selector', 'limitdatetime_from', get_string('from', 'registrationrule_limitdatetime'));
        $mform->addElement('date_time_selector', 'limitdatetime_to', get_string('to', 'registrationrule_limitdatetime'));
        $mform->addElement('select', 'restrictionmode', get_string('restrictionmode', 'registrationrule_limitdatetime'), [
            static::OPTION_ALLOW_BETWEEN_DATES => get_string('allowbetweendates', 'registrationrule_limitdatetime'),
            static::OPTION_DENY_BETWEEN_DATES => get_string('denybetweendates', 'registrationrule_limitdatetime'),
        ]);
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

        // Is the current date/time in the window between the configured start and end dates?
        $nowisinsidewindow = (($now >= $this->startdate) && ($now <= $this->enddate));

        // For cases where a rule instance is configured to allow registration between two dates we need to first
        // check that we are not inside the window, then return a deferred result by calling deferred_deny() with a
        // closure that allows rule_checker to determine if the result is valid at a later time.
        //
        // In this case the result would not be considered valid if another instance (configured with OPTION_ALLOW_BETWEEN_DATES)
        // returns a result that is set to allow. We need to do this to allow an administrator to configure multiple instances
        // using this case, otherwise the first instance to deny registration would allow points to accumilate.
        if ($this->restrictionmode === static::OPTION_ALLOW_BETWEEN_DATES) {
            if (!$nowisinsidewindow) {
                return $this->deferred_deny(
                    score: $this->get_points(),
                    feedbackmessage: get_string('failuremessage:allowbetween', 'registrationrule_limitdatetime',
                        $this->stringparams
                    ),
                    loginfo: new log_info(
                        $this,
                        get_string('logmessage:allowbetween', 'registrationrule_limitdatetime', $this->stringparams)
                    ),
                    resolvecallback: function() {
                        // Get a static instance of rule_checker so we know what has been processed.
                        $rulechecker = rule_checker::get_instance('signup_form');
                        $controller = rule_instances_controller::get_instance();
                        foreach ($rulechecker->get_results() as $result) {
                            // The results log_info object holds the information we need about
                            // the rule instance that returned this result.
                            $loginfo = $result->get_log_info();
                            // Skip any results from rule types other that limitdatetime.
                            if (!$loginfo->get_rule_type() == 'limitdatetime') {
                                continue;
                            }
                            // Get an instance of the rule from it's ID and check how it is configured, we are only
                            // returning a deferred result in the case of OPTION_ALLOW_BETWEEN_DATES.
                            $id = $loginfo->get_rule_instance_id();
                            $resultinstance = $controller->get_rule_instance_by_id($id);
                            if ($resultinstance->get_instance_config()->restrictionmode === static::OPTION_ALLOW_BETWEEN_DATES) {
                                if ($result->get_allowed()) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    }
                );
            }
        }

        // This is the simple case where the rule instance is configured to deny registration between dates, here we do not
        // need to return a deferred result.
        if ($this->restrictionmode === static::OPTION_DENY_BETWEEN_DATES) {
            if ($nowisinsidewindow) {
                return $this->deny(
                    score: $this->get_points(),
                    feedbackmessage: get_string('failuremessage:denybetween', 'registrationrule_limitdatetime',
                        $this->stringparams
                    ),
                    loginfo: new log_info(
                        $this,
                        get_string('logmessage:denybetween', 'registrationrule_limitdatetime', $this->stringparams)
                    )
                );
            }
        }

        return $this->allow();
    }

}
