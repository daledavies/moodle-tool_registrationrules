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

        $instances = (new \tool_registrationrules\local\rule_instances_controller())->get_rule_instance_records();

        foreach ($instances as $instance) {
            if (get_config('registrationrule_' . $instance->type, 'disabled')) {
                continue;
            }

            $pluginrule = 'registrationrule_' . $instance->type . '\rule';

            // Parse additional config and add to instance.
            foreach (json_decode($instance->other) as $configkey => $configvalue) {
                $instance->$configkey = $configvalue;
            }
            $ruleinstance = new $pluginrule($instance);
            if (!$ruleinstance instanceof rule\rule_base) {
                debugging("Rule $pluginrule does not extend rule_base", DEBUG_DEVELOPER);
                continue;
            }
            $this->rules[] = $ruleinstance;
        }
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
            $result = $instance->pre_data_check();

            // Ignore rules without post data check.
            if ($result !== null) {
                $this->results[] = $result;
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
            $result = $instance->post_data_check($data);

            // Ignore rules without post data check.
            if ($result !== null) {
                $this->results[] = $result;
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
            $instance->extend_form($mform);
        }
    }

    /**
     * Return if registration is allowed.
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

        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return the resulting messages from each check.
     *
     * TODO: improve documentation and discern from get_validation_messages()
     *
     * @return string[]
     * @throws coding_exception
     */
    public function get_messages(): array {
        if (!$this->checked) {
            throw new coding_exception(
                'rule_checker::check() must be called before using rule_checker::get_messages()',
            );
        }
        $messages = [];
        foreach ($this->results as $result) {
            // If not allowed, add an error message.
            if (!$result->get_allowed()) {
                continue;
            }
            $messages[] = $result->get_message();
        }
        return $messages;
    }

    /**
     * Return the aggregated messages from each check, to be displayed as validation errors.
     *
     * TODO: improve documentation and discern from get_messsages()
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
        $messages = [];

        foreach ($this->results as $result) {
            if (!$result->get_allowed()) {
                foreach ($result->get_validation_messages() as $field => $message) {
                    // Note that this will overwrite any previous message for the same field.
                    // We may want to consider some kind of aggregation here.
                    $messages[$field] = $message;
                }

                // General messages.
                if (!empty($result->get_message())) {
                    if (!isset($messages['tool_registrationrules_errors'])) {
                        $messages['tool_registrationrules_errors'] = '';
                    }
                    $messages['tool_registrationrules_errors'] .= '<br />' . $result->get_message();
                }
            }
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
