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

namespace registrationrule_stopforumspam;

use curl;
use MoodleQuickForm;
use stdClass;
use registrationrule_stopforumspam\form\confidence_range_slider;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_trait;

/**
 * Registration rule restricting registrations based on data from the Stop Forum Spam
 * service (https://www.stopforumspam.com)
 *
 * @package   registrationrule_stopforumspam
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, post_data_check, instance_configurable {
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
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function post_data_check(array $data): rule_check_result {
        // Validate the supplied data contains the fields required for checking
        // with the Stop Forum Spam databases.
        $requireddatakeys = ['username', 'email'];
        $exists = count(array_diff_key(array_flip($requireddatakeys), $data)) === 0;
        if (!$exists) {
            return null;
        }

        // Make a request to the API.
        $curl = new curl();
        $curl->setopt([
            'CURLOPT_CONNECTTIMEOUT' => 2,
            'CURLOPT_TIMEOUT' => 2,
        ]);
        $response = $curl->post(
            'https://api.stopforumspam.org/api',
            [
                'ip' => getremoteaddr(),
                'username' => $data['username'],
                'email' => $data['email'],
                'confidence' => true,
                'json' => true,
            ]
        );

        // If we have nothing or something went wrong with the API request
        // then deny the registration with fallback points.
        if ($response === false || $curl->get_errno() || !($response = json_decode($response)) || !isset($response->success)) {
            return $this->deny_with_fallback();
        }
        // If we got a response from the API but the lookup wasn't successful then
        // also deny the registration with fallback points.
        if ($response->success !== 1) {
            return $this->deny_with_fallback();
        }

        $ipresponseok = isset($response->ip) && isset($response->ip->frequency);
        // If the IP appears in the database with a confidence equal to or greater than the threshold.
        if ($this->instanceconfig->ipenabled && $ipresponseok && $response->ip->frequency > 0) {
            if ($response->ip->confidence >= $this->instanceconfig->ipconfidence) {
                return $this->deny(
                    score: $this->get_points(),
                    feedbackmessage: get_string('ipfailuremessage', 'registrationrule_stopforumspam'),
                );
            }
        }

        $usernameresponseok = isset($response->username) && isset($response->username->frequency);
        // If the username appears in the database with a confidence equal to or greater than the threshold.
        if ($this->instanceconfig->usernameenabled && $usernameresponseok && $response->username->frequency > 0) {
            if ($response->username->confidence >= $this->instanceconfig->usernameconfidence) {
                return $this->deny(
                    score: $this->get_points(),
                    validationmessages: ['username' => get_string('usernamefailuremessage', 'registrationrule_stopforumspam')],
                );
            }
        }

        $emailresponseok = isset($response->email) && isset($response->email->frequency);
        // If the email address appears in the database with a confidence equal to or greater than the threshold.
        if ($this->instanceconfig->emailenabled && $emailresponseok && $response->email->frequency > 0) {
            if ($response->email->confidence >= $this->instanceconfig->emailconfidence) {
                return $this->deny(
                    score: $this->get_points(),
                    validationmessages: ['email' => get_string('emailfailuremessage', 'registrationrule_stopforumspam')],
                );
            }
        }

        // None of this user's details appear in the Stop Forum Spam database.
        return $this->allow();
    }

    /**
     * Deny registration with fallback message and points.
     *
     * @return rule_check_result
     */
    public function deny_with_fallback(): rule_check_result {
        return $this->deny(
            score: $this->get_fallbackpoints(),
            feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_stopforumspam')
        );
    }

    /**
     * Return an array of settings fields names used to extend the instance
     * settings form via extend_settings_form().
     *
     * @return array
     */
    public static function get_instance_settings_fields(): array {
        return [
            'ipconfidence',
            'ipenabled',
            'usernameenabled',
            'usernameconfidence',
            'emailenabled',
            'emailconfidence',
        ];
    }

    /**
     * Inject rule type specific settings into basic rule settings form if the type needs additional configuration.
     *
     * Here we have individual options for IP address, username and email address to enable/disable
     * each one and set a threshold for the confidence figure returned from SFS, above which we will
     * deny registration.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public static function extend_settings_form(MoodleQuickForm $mform): void {
        global $CFG;

        // Register custom confidence range element for use in this form.
        MoodleQuickForm::registerElementType(
            'confidence_range_slider',
            "$CFG->dirroot/$CFG->admin/tool/registrationrules/rules/stopforumspam/classes/form/confidence_range_slider.php",
            confidence_range_slider::class,
        );

        // IP address check.
        $mform->addElement('header', 'ipconfidencegroup', 'Check IP address');
        $mform->setExpanded('ipconfidencegroup');
        $mform->addElement(
            'selectyesno',
            'ipenabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );
        $mform->setType('ipenabled', PARAM_BOOL);
        $mform->setDefault('ipenabled', 1);
        $mform->addElement(
            'confidence_range_slider',
            'ipconfidence',
            'Confidence threshold',
        );
        $mform->setDefault('ipconfidence', 90);

        // Username check.
        $mform->addElement('header', 'usernameconfidencegroup', 'Check username');
        $mform->setExpanded('usernameconfidencegroup');
        $mform->addElement(
            'selectyesno',
            'usernameenabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );
        $mform->setType('usernameenabled', PARAM_BOOL);
        $mform->setDefault('usernameenabled', 1);
        $mform->addElement(
            'confidence_range_slider',
            'usernameconfidence',
            'Confidence threshold',
        );
        $mform->setDefault('usernameconfidence', 90);

        // Email check.
        $mform->addElement('header', 'emailconfidencegroup', 'Check email address');
        $mform->setExpanded('emailconfidencegroup');
        $mform->addElement(
            'selectyesno',
            'emailenabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );
        $mform->setType('emailenabled', PARAM_BOOL);
        $mform->setDefault('emailenabled', 1);
        $mform->addElement(
            'confidence_range_slider',
            'emailconfidence',
            'Confidence threshold',
        );
        $mform->setDefault('emailconfidence', 90);
    }
}
