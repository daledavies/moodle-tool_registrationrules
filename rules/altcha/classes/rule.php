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

namespace registrationrule_altcha;

defined('MOODLE_INTERNAL') || die();

// Require composer autoload for Altcha server library.
require_once($CFG->dirroot . '/admin/tool/registrationrules/rules/altcha/vendor/autoload.php');

use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Altcha;
use coding_exception;
use Exception;
use MoodleQuickForm;
use registrationrule_altcha\form\altcha_widget;
use registrationrule_altcha\form\complexity_range_slider;
use stdClass;
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\extend_signup_form;
use tool_registrationrules\local\rule\instance_configurable;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\rule_trait;

/**
 * Rule to restrict restrict registration based on Altcha challenge completion.
 *
 * @package   registrationrule_altcha
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, extend_signup_form, post_data_check, instance_configurable {
    use rule_trait;

    /** @var int Maximum complexity allowed for challenge */
    public const COMPLEXITY_MAX = 1000000;

    /** @var int Minimum complexity allowed for challenge */
    public const COMPLEXITY_MIN = 100000;

    /** @var int Steps for use when selecting complexity */
    public const COMPLEXITY_STEP = 100000;

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
        return [
            'complexity',
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
        global $CFG;

        // Register custom range element for use in this form.
        MoodleQuickForm::registerElementType(
            'complexity_range_slider',
            "$CFG->dirroot/$CFG->admin/tool/registrationrules/rules/altcha/classes/form/complexity_range_slider.php",
            complexity_range_slider::class,
        );

        $mform->addElement('complexity_range_slider', 'complexity', get_string('challengecomplexity', 'registrationrule_altcha'),
            static::COMPLEXITY_MIN, static::COMPLEXITY_MAX, static::COMPLEXITY_STEP);
        $mform->setDefault('complexity', 300000);
        $mform->addHelpButton('complexity', 'challengecomplexity', 'registrationrule_altcha');
    }

    /**
     * Inject additional fields into the signup form for usage by the rule instance after submission.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
        global $CFG, $SESSION;

        // Save random key to session if not already set, used when creating and verifying challenge.
        if (!isset($SESSION->registrationrule_altcha_key)) {
            $SESSION->registrationrule_altcha_key = openssl_random_pseudo_bytes(32);
        }

        // Create a new challenge.
        $challengeoptions = new ChallengeOptions([
            'hmacKey'   => $SESSION->registrationrule_altcha_key,
            'maxNumber' => $this->instanceconfig->complexity,
        ]);

        // Register complexity range slider element for use in this form.
        MoodleQuickForm::registerElementType(
            'altcha_widget',
            "$CFG->dirroot/$CFG->admin/tool/registrationrules/rules/altcha/classes/form/altcha_widget.php",
            altcha_widget::class,
        );

        $mform->addElement(
            'altcha_widget',
            'registrationrule_altcha',
            json_encode(Altcha::createChallenge($challengeoptions)),
            $this->instanceconfig->complexity,
        );
    }

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     * @throws coding_exception
     */
    public function post_data_check(array $data): rule_check_result {
        global $SESSION;

        // Decode base64 encoded JSON string from form data, if something goes wrong when deny with fallback points.
        if (($base64 = base64_decode($data['registrationrule_altcha'])) && $payload = json_decode($base64)) {
            $this->deny_with_fallback();
        }

        // Attempt to verify the solution, if something goes wrong when deny with fallback points.
        try {
            $verificationresult = Altcha::verifySolution((array) $payload, $SESSION->registrationrule_altcha_key, true);
        } catch (Exception $e) {
            $this->deny_with_fallback();
        }

        // If the verification process worked, but the solution was wrong, then deny registration for real.
        if ($verificationresult === false) {
            return $this->deny(
                score: $this->get_points(),
                feedbackmessage: get_string('failuremessage', 'registrationrule_altcha')
            );
        }

        // If we got this far then the solution was correctly verified.
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
            feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_altcha'),
            loginfo: new log_info($this, get_string('logmessage', 'registrationrule_altcha')),
        );
    }
}
