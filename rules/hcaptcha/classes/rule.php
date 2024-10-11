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
use MoodleQuickForm;
use tool_registrationrules\local\rule\configurable;
use tool_registrationrules\local\rule_check_result;

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
class rule extends \tool_registrationrules\local\rule\rule_base implements configurable {
    /** Names of fields added to the rule's settings form */
    const SETTINGS_FIELDS = ['hcaptcha_sitekey', 'hcaptcha_secret'];

    /**
     * Inject rule type specific settings into basic rule settings form if the type needs additional configuration.
     *
     * @param MoodleQuickForm $mform
     * @return void
     * @throws coding_exception
     */
    public static function extend_settings_form(MoodleQuickForm $mform): void {
        $mform->addElement('text', 'hcaptcha_sitekey', get_string('sitekey', 'registrationrule_hcaptcha'));
        $mform->addRule('hcaptcha_sitekey', get_string('required'), 'required');
        $mform->setType('hcaptcha_sitekey', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'hcaptcha_secret', get_string('secret', 'registrationrule_hcaptcha'));
        $mform->addRule('hcaptcha_secret', get_string('required'), 'required');
        $mform->setType('hcaptcha_secret', PARAM_ALPHANUMEXT);
    }

    /**
     * Inject additional fields into the signup form for usage by the rule instance after submission.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {

        // This is the basic JS for hCaptcha.
        $html = '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';

        // But we also need to add the HTML for the result.
        $sitekey = htmlspecialchars($this->config->hcaptcha_sitekey, ENT_COMPAT);
        $html .= '<div class="h-captcha" data-sitekey="' . $sitekey . '"></div>';

        $mform->addElement('hidden', 'h-captcha-response', '');
        $mform->addElement('html', $html);
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result|null a rule_check_result object or null if check not applicable for this type.
     * @throws coding_exception
     */
    public function post_data_check(array $data): ?rule_check_result {
        // Build the data used for validation.
        $validationpost = [
            'secret' => $this->config->hcaptcha_secret,
            'sitekey' => $this->config->hcaptcha_sitekey,
            'response' => $data['h-captcha-response'],
        ];

        // Call the hCaptcha API for validation.
        $ch = curl_init('https://api.hcaptcha.com/siteverify');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $validationpost);

        // Get and decode response.
        $response = json_decode(curl_exec($ch));
        $error = curl_error($ch);
        curl_close($ch);

        // Something went wrong when connecting to hCaptcha API.
        if ($error) {
            return $this->deny(
                score: $this->config->fallbackpoints,
                feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_hcaptcha')
            );
        }

        // If empty or false the captcha failed and the result is negative.
        if (!empty($response->success)) {
            return $this->deny(
                score: $this->config->points,
                validationmessages: ['email' => get_string('failuremessage', 'registrationrule_hcaptcha')],
            );
        }

        // We got to this point so the captcha check passed.
        return $this->allow();
    }

    /**
     * Perform rule's checks applicable without any user input before the signup form is displayed.
     *
     * @return rule_check_result|null A rule_check_result object or null if check not applicable for this type.
     */
    public function pre_data_check(): ?rule_check_result {
        return null;
    }
}
