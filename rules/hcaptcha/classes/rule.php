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

namespace registrationrule_hcaptcha;

use coding_exception;
use curl;
use MoodleQuickForm;
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\captcha_rule;
use tool_registrationrules\local\rule\extend_forgot_password_form;
use tool_registrationrules\local\rule\extend_signup_form;
use tool_registrationrules\local\rule\forgot_password_trait;
use tool_registrationrules\local\rule\plugin_configurable;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\rule_trait;

/**
 * Registration rule restricting registrations based on hCaptcha detecting human and automated threats.
 *
 * For further information see {@link https://www.hcaptcha.com/}
 *
 * @package   registrationrule_hcaptcha
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, extend_signup_form, plugin_configurable, post_data_check,
        captcha_rule, extend_forgot_password_form {
    use rule_trait, forgot_password_trait;

    /**
     * Inject additional fields into the signup form for usage by the rule instance after submission.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
        $hcaptchasitekey = get_config('registrationrule_hcaptcha', 'hcaptcha_sitekey');
        // Return early if the config has not been set.
        if (!$hcaptchasitekey) {
            return;
        }

        // This is the basic JS for hCaptcha.
        $html = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';

        // But we also need to add the HTML for the result.
        $sitekey = htmlspecialchars($hcaptchasitekey, ENT_COMPAT);
        $html .= '<div class="h-captcha" data-sitekey="' . $sitekey . '"></div>';

        $mform->addElement('hidden', 'h-captcha-response', '');
        $mform->setType('h-captcha-response', PARAM_ALPHANUMEXT);
        $mform->addElement('html', $html);
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     * @throws coding_exception
     */
    public function post_data_check(array $data): rule_check_result {
        $hcaptchasecret = get_config('registrationrule_hcaptcha', 'hcaptcha_secret');
        $hcaptchasitekey = get_config('registrationrule_hcaptcha', 'hcaptcha_sitekey');
        // Return early if the config has not been set.
        if (!$hcaptchasecret || !$hcaptchasitekey) {
            return null;
        }
        $postparams = [
            'secret' => $hcaptchasecret,
            'sitekey' => $hcaptchasitekey,
            'response' => $data['h-captcha-response'],
            'remoteip' => getremoteaddr(),
        ];

        // Call the hCaptcha API for validation.
        $curl = new curl();
        $curl->setopt([
            'CURLOPT_CONNECTTIMEOUT' => 2,
            'CURLOPT_TIMEOUT' => 2,
        ]);
        $response = $curl->post(
            'https://api.hcaptcha.com/siteverify',
            $postparams
        );

        // If either the API call was unseuccessful, the json could not be decoded
        // or for some reason it does not contain the expected success field.
        if ($curl->get_errno() || !($response = json_decode($response)) || !isset($response->success)) {
            return $this->deny(
                score: $this->get_fallbackpoints(),
                feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_hcaptcha'),
                loginfo: new log_info($this, get_string('logmessage', 'registrationrule_hcaptcha'))
            );
        }

        // The captcha failed so deny registration.
        if (!$response->success) {
            return $this->deny(
                score: $this->get_points(),
                validationmessages: ['tool_registrationrules_errors' => get_string('failuremessage', 'registrationrule_hcaptcha')],
            );
        }

        // We got to this point so the captcha check passed.
        return $this->allow();
    }

    /**
     * Confirm if the rule plugin has been properly configured.
     *
     * @return boolean
     */
    public static function is_plugin_configured(): bool {
        $hcaptchasecret = get_config('registrationrule_hcaptcha', 'hcaptcha_secret');
        $hcaptchasitekey = get_config('registrationrule_hcaptcha', 'hcaptcha_sitekey');
        if (!$hcaptchasecret || !$hcaptchasitekey) {
            return false;
        }

        return true;
    }

    /**
     * Inject additional fields into the forgot password form.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_forgot_password_form(MoodleQuickForm $mform): void {
        $mform->addElement('header', 'captcha', '', '');
        $this->extend_form($mform);
    }

    /**
     * Perform rule's checks after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     */
    public function validate_forgot_password_form(array $data): rule_check_result {
        return $this->post_data_check($data);
    }
}
