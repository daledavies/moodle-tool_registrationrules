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

namespace registrationrule_ratelimit;

use MoodleQuickForm;
use stdClass;
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule\pre_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_trait;
use registrationrule_ratelimit\local\rate_limiter;

/**
 * Rule to limit the number of signup attempts within a time window, will check both
 * the number of attempts per session and per IP.
 *
 * @package   registrationrule_ratelimit
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, pre_data_check, post_data_check, instance_configurable {
    use rule_trait;

    /** @var stdClass rule plugin instance config. */
    protected stdClass $instanceconfig;

    /** @var rate_limiter A rate limiter instance. */
    private rate_limiter $ratelimiter;

    /**
     * Constructa  new instance of the rule.
     */
    public function __construct() {
        $this->ratelimiter = new rate_limiter();
    }

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
        return [
            'sessionenabled',
            'sessionlimit',
            'sessiontimewindow',
            'ipenabled',
            'iplimit',
            'iptimewindow',
        ];
    }

    /**
     * Inject rule type specific settings into basic rule settings form if the type needs
     * additional configuration.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public static function extend_settings_form(MoodleQuickForm $mform): void {
        global $OUTPUT;
        // General help message for this rule should be displayed above the generic "Enabled" setting.
        $mform->insertElementBefore(
            $mform->createElement(
                'static', 'registrationrule_ratelimit_help', '',
                $OUTPUT->notification(
                    get_string('registrationrule:instance:settings:help', 'registrationrule_ratelimit'),
                    'success', false,
                ),
            ),
            'enabled',
        );
        // Session rate limiting group.
        $mform->addElement('header', 'sessionratelimitgroup',
            get_string('registrationrule:instance:settings:sessionratelimitgroup', 'registrationrule_ratelimit'));
        $mform->setExpanded('sessionratelimitgroup');

        $mform->addElement(
            'selectyesno',
            'sessionenabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );
        $mform->setDefault('sessionenabled', 1);

        $mform->addElement('text', 'sessionlimit',
            get_string('registrationrule:instance:settings:limit', 'registrationrule_ratelimit'));
        $mform->setType('sessionlimit', PARAM_INT);
        $mform->setDefault('sessionlimit', 5);
        $mform->disabledIf('sessionlimit', 'sessionenabled', 'eq', 0);

        $mform->addElement('text', 'sessiontimewindow', get_string('registrationrule:instance:settings:timewindow',
            'registrationrule_ratelimit'));
        $mform->setType('sessiontimewindow', PARAM_INT);
        $mform->setDefault('sessiontimewindow', 120);
        $mform->disabledIf('sessiontimewindow', 'sessionenabled', 'eq', 0);

        // IP rate limiting group.
        $mform->addElement('header', 'ipratelimitgroup',
            get_string('registrationrule:instance:settings:ipratelimitgroup', 'registrationrule_ratelimit'));
        $mform->setExpanded('ipratelimitgroup');

        $mform->addElement(
            'selectyesno',
            'ipenabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );

        $mform->addElement('text', 'iplimit', get_string('registrationrule:instance:settings:limit', 'registrationrule_ratelimit'));
        $mform->setType('iplimit', PARAM_INT);
        $mform->setDefault('iplimit', 5);
        $mform->disabledIf('iplimit', 'ipenabled', 'eq', 0);

        $mform->addElement('text', 'iptimewindow', get_string('registrationrule:instance:settings:timewindow',
            'registrationrule_ratelimit'));
        $mform->setType('iptimewindow', PARAM_INT);
        $mform->setDefault('iptimewindow', 120);
        $mform->disabledIf('iptimewindow', 'ipenabled', 'eq', 0);
    }

    /**
     * Perform rule's checks applicable without any user input before the signup form is displayed.
     *
     * For both IP and session rate limiting, we are only checking if the limit has been reached here,
     * if it has then we can prevent the signup form from being displayed.
     *
     * Note we only increment the count when the form is actually submitted (see post_data_check()),
     * if we incremented the count here as well then for each submission there would be two counts.
     *
     * @return rule_check_result A rule_check_result object.
     */
    public function pre_data_check(): rule_check_result {
        // If IP limiting is enabled then check whether we have reached the limit for the configured time window.
        if ($this->instanceconfig->ipenabled &&
                !$this->ratelimiter->limit($this->instanceconfig->iplimit, $this->instanceconfig->iptimewindow,
                rate_limiter::IP_CHECK, rate_limiter::CHECK_RATE_COUNT)) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_ratelimit'),
                loginfo: new log_info(
                    $this, get_string('logmessage:ip', 'registrationrule_ratelimit', getremoteaddr())
                )
            );
        }

        // If session limiting is enabled then check whether we have reached the limit for the configured time window.
        if ($this->instanceconfig->sessionenabled &&
                !$this->ratelimiter->limit($this->instanceconfig->sessionlimit, $this->instanceconfig->sessiontimewindow,
                rate_limiter::SESSION_CHECK, rate_limiter::CHECK_RATE_COUNT)) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_ratelimit'),
                loginfo: new log_info(
                    $this, get_string('logmessage:session', 'registrationrule_ratelimit', getremoteaddr())
                )
            );
        }

        // We made it here so the limit hasn't yet been reached.
        return $this->allow();
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * On form submission we can both increment the count and deny submission.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function post_data_check(array $data): rule_check_result {
        if ($this->instanceconfig->ipenabled &&
                !$this->ratelimiter->limit($this->instanceconfig->iplimit, $this->instanceconfig->iptimewindow,
                rate_limiter::IP_CHECK, rate_limiter::INCREMENT_RATE_COUNT)) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_ratelimit'),
                loginfo: new log_info(
                    $this, get_string('logmessage:session', 'registrationrule_ratelimit')
                )
            );
        }

        if ($this->instanceconfig->sessionenabled &&
                !$this->ratelimiter->limit($this->instanceconfig->sessionlimit, $this->instanceconfig->sessiontimewindow,
                rate_limiter::SESSION_CHECK, rate_limiter::INCREMENT_RATE_COUNT)) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_ratelimit'),
                loginfo: new log_info(
                    $this, get_string('logmessage:session', 'registrationrule_ratelimit')
                )
            );
        }

        // We made it here so the limit hasn't yet been reached.
        return $this->allow();
    }
}
