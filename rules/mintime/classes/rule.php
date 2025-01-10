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

namespace registrationrule_mintime;

use MoodleQuickForm;
use stdClass;
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\extend_signup_form;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_trait;

/**
 * User registration must take longer than configured time in seconds, anything
 * faster than this is considered to be a bot.
 *
 * @package   registrationrule_mintime
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, post_data_check, instance_configurable, extend_signup_form {
    use rule_trait;

    /** @var string Cipher method used for encryption of timestamp */
    private const CIPHER = 'AES-256-CBC';

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
     * Get the rule instance's description.
     *
     * @return string the rule instance description.
     */
    public function get_display_description(): string {
        $desc = get_string('displaydescription', 'registrationrule_mintime', ['time' => $this->instanceconfig->mintime]);
        return !$this->description ? $desc : $this->description;
    }

    /**
     * Add an encrypted timestamp to the signup form, also using this callback to set
     * a session param with initialisation vector and key for encryption.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
        global $SESSION;

        // If it doesn't exist, set up the initialisation vector and encryption key.
        if (!isset($SESSION->registrationrule_mintime_encdata)) {
            $ivlen = openssl_cipher_iv_length(static::CIPHER);
            $SESSION->registrationrule_mintime_encdata = [
                'iv' => openssl_random_pseudo_bytes($ivlen),
                'key' => openssl_random_pseudo_bytes(32),
            ];
        }

        // Encrypt a timestamp and add it as a hidden field on the signup form.
        $encyptedtime = openssl_encrypt(time(), static::CIPHER,
            $SESSION->registrationrule_mintime_encdata['key'], 0, $SESSION->registrationrule_mintime_encdata['iv']
        );
        $mform->addElement('hidden', 'registrationrule_mintime_time', $encyptedtime);
        $mform->setType('registrationrule_mintime_time', PARAM_RAW);
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function post_data_check(array $data): rule_check_result {
        global $SESSION;

        // Get the timestamp at the earliest point we can.
        $currenttime = time();

        // Decrypt the submitted timestamp, this should be max 24 chars long.
        $decryptedtime = openssl_decrypt(substr($data['registrationrule_mintime_time'], 0, 24), static::CIPHER,
            $SESSION->registrationrule_mintime_encdata['key'], 0, $SESSION->registrationrule_mintime_encdata['iv']
        );

        // If decrypted time is false then perhaps someone messed with the submitted timestamp.
        if (!$decryptedtime) {
            return $this->deny(
                score: $this->get_fallbackpoints(),
                feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_mintime'),
                loginfo: new log_info(
                    $this, get_string('fallbackfailuremessage', 'registrationrule_mintime')
                )
            );
        }

        // Did we take less time to submit the form that the allowed threshold?
        if (($currenttime - $decryptedtime) <= (int) $this->instanceconfig->mintime) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_mintime'),
                loginfo: new log_info(
                    $this, get_string('logmessage', 'registrationrule_mintime',
                        ['time' => $this->instanceconfig->mintime]
                    )
                )
            );
        }

        // Time took longer than the threshold so we'll allow the registration.
        return $this->allow();
    }

    /**
     * Return an array of settings fields names used to extend the instance
     * settings form via extend_settings_form().
     *
     * @return array
     */
    public static function get_instance_settings_fields(): array {
        return [
            'mintime',
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
        $options = [];
        for ($x = 2; $x <= 15; $x++) {
            $options[$x] = get_string('numseconds', 'core', $x);
        }
        $mform->addElement('select', 'mintime', 'Minimum completion time', $options);
        $mform->setDefault('mintime', 3);
    }
}
