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

namespace tool_registrationrules\local;

use coding_exception;
use dml_exception;
use MoodleQuickForm;
use stdClass;
use tool_registrationrules\local\rule\extend_signup_form;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule\pre_data_check;

/**
 * Facilitate exicution of registration rule plugins and enumeration/reposrting
 * of results.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_checker {
    /**
     * @var static[] rule checker singleton instances for each rule checker type instantiated
     */
    private static array $instances = [];

    /**
     * @var array<rule\rule_interface>
     */
    private array $rules;

    /**
     * @var array rule check results
     */
    private array $results;

    /**
     * @var stdClass|null site-wide config for tool_registrationrules
     */
    private ?\stdClass $adminconfig = null;

    /**
     * @var bool (at least) some checks ran already
     */
    private bool $checked = false;

    /**
     * Get singleton rule checker instance (given the specified type).
     *
     * @param string $type rule checker instance type to return
     * @return rule_checker
     */
    public static function get_instance(string $type): rule_checker {
        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = new rule_checker();
        }
        return self::$instances[$type];
    }

    /**
     * Rule checker constructor
     *
     * @throws dml_exception
     */
    private function __construct() {
        $this->clear();
        $this->adminconfig = $this->get_admin_config();
        // Only process active rules...
        $this->rules = (new \tool_registrationrules\local\rule_instances_controller())->get_active_rule_instances();
    }

    /**
     * Remove all rules from object, empty previous results and reset checked status.
     *
     * @return void
     */
    public function clear(): void {
        $this->rules = [];
        $this->results = [];
        $this->checked = false;
    }

    /**
     * Load and cache the admin config for this module.
     *
     * @return stdClass|null the plugin config
     * @throws dml_exception
     */
    public function get_admin_config(): ?stdClass {
        if ($this->adminconfig) {
            return $this->adminconfig;
        }
        $this->adminconfig = get_config('tool_registrationrules');
        return $this->adminconfig;
    }

    /**
     * Return rule checker's rule objects.
     *
     * @return rule\rule_interface[]
     */
    public function get_rules(): array {
        return $this->rules;
    }

    /**
     * Run all checks applicable before user input
     *
     * @return void
     */
    public function run_pre_data_checks(): void {
        foreach ($this->rules as $instance) {
            if ($instance instanceof pre_data_check) {
                $this->results[] = $instance->pre_data_check();
            }
        }
        $this->checked = true;
    }

    /**
     * Run all rules' checks in need of user input
     *
     * @param array $data the data array from submitted form values.
     * @return void
     */
    public function run_post_data_checks(array $data): void {
        foreach ($this->rules as $instance) {
            if ($instance instanceof post_data_check) {
                $this->results[] = $instance->post_data_check($data);
            }
        }

        $this->checked = true;
    }

    /**
     * Let configured rule instances extend the user signup form.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
        foreach ($this->rules as $instance) {
            if ($instance instanceof extend_signup_form) {
                $instance->extend_form($mform);
            }
        }
    }

    /**
     * Return if registration is allowed.
     *
     * TODO: Implement the "invert score feature".
     *
     * @return bool true if registration is allowed, false if registration should be blocked
     * @throws coding_exception
     */
    public function is_registration_allowed(): bool {
        if (!($this->adminconfig->enable ?? 0)) {
            return true;
        }
        if (!$this->checked) {
            throw new coding_exception(
                'rule_checker::check() must be called before using rule_checker::is_registration_allowed()',
            );
        }

        // Get a total of all points returned from rule checks.
        $totalpoints = 0;
        foreach ($this->results as $result) {
            if ($result->get_allowed()) {
                continue;
            }
            $totalpoints += $result->get_score();
        }
        // If the total of all points returned from rule checks is greater
        // than the maximum allowed points then registration is not allowed.
        if ($totalpoints >= $this->adminconfig->maxpoints) {
            return false;
        }

        return true;
    }

    /**
     * Return any general feedback messages added to a check's result object.
     *
     * TODO: improve documentation and discern from get_validation_messages()
     *
     * @return string[]
     * @throws coding_exception
     */
    public function get_feedback_messages(): array {
        if (!$this->checked) {
            throw new coding_exception(
                'rule_checker::check() must be called before using rule_checker::get_messages()',
            );
        }
        $messages = [];
        foreach ($this->results as $result) {
            // If registration is allowed we don't need to retrieve feedback messages
            // from the result.
            if ($result->get_allowed()) {
                continue;
            }
            // If we have a null or empty message then don't add it to the $messages array.
            $message = $result->get_feedback_message();
            if (empty($message)) {
                continue;
            }
            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Flatten the result of get_feedback_messages() to a string suitable for use in
     * form validation errors.
     *
     * @return string
     */
    public function get_feedback_messages_string(): string {
        return implode('<br>', $this->get_feedback_messages());
    }

    /**
     * Return the validation messages from each check as an array, to be displayed as
     * validation errors. E.g. a group of messages from each check per field.
     *
     * @return array
     * @throws coding_exception
     */
    public function get_validation_messages(): array {
        if (!$this->checked) {
            throw new coding_exception(
                'rule_checker::check() must be called before using rule_checker::get_validation_messages()',
            );
        }

        $validationmessages = [];

        foreach ($this->results as $result) {
            // If registration is allowed we don't need to retrieve messages from the result.
            if ($result->get_allowed()) {
                continue;
            }
            foreach ($result->get_validation_messages() as $field => $message) {
                $validationmessages[$field][] = $message;
            }
        }
        foreach ($validationmessages as $field => $messages) {
            $validationmessages[$field] = implode('<br>', $messages);
        }

        return $validationmessages;
    }

    /**
     * Get all messages as an array of strings indexed by field.
     *
     * @return array
     */
    public function get_all_messages(): array {
        $messages = $this->get_validation_messages();
        $feedbackmessages = $this->get_feedback_messages_string();
        if (!empty($feedbackmessages)) {
            $messages['tool_registrationrules_errors'] = $feedbackmessages;
        }

        return $messages;
    }

    /**
     * Inject a form element so we can append information about failed checks to it as validation errors.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function add_error_field(MoodleQuickForm $mform) {
        $mform->addElement('static', 'tool_registrationrules_errors');
    }
}
