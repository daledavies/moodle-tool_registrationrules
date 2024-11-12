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

namespace tool_registrationrules\local\rule;

use coding_exception;
use MoodleQuickForm;
use stdClass;
use tool_registrationrules\local\rule_check_result;

/**
 * Interface for registration rule subplugin classes.
 *
 * @package    tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class rule_base implements rule_interface {

    /** @var stdClass rule plugin instance config. */
    protected stdClass $instanceconfig;

    /** @var int rule plugin instance id. */
    protected int $id;

    /** @var string rule plugin instance type. */
    protected string $type;

    /** @var bool true if the rule instance is enabled. */
    protected bool $enabled;

    /** @var string rule plugin instance name. */
    protected string $name;

    /** @var int rule plugin instance points. */
    protected int $points;

    /** @var int rule plugin instance fallback points. */
    protected int $fallbackpoints;

    /** @var int rule plugin instance sort order. */
    protected int $sortorder;

    /** @var rule_check_result object representing the result this rule will return. */
    protected rule_check_result $result;

    /**
     * Constructor
     *
     * @param int $id rule plugin instance id.
     * @param string $type rule plugin instance type.
     * @param bool $enabled true if the rule instance is enabled.
     * @param string $name rule instance name.
     * @param int $points rule plugin instance points.
     * @param int $fallbackpoints rule plugin instance fallback points.
     * @param int $sortorder rule plugin instance sort order.
     * @param stdClass $instanceconfig rule plugin instance config.
     */
    public function __construct(
        int $id,
        string $type,
        bool $enabled,
        string $name,
        int $points,
        int $fallbackpoints,
        int $sortorder,
        stdClass $instanceconfig,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->enabled = $enabled;
        $this->name = $name;
        $this->points = $points;
        $this->fallbackpoints = $fallbackpoints;
        $this->sortorder = $sortorder;
        $this->instanceconfig = $instanceconfig;
        $this->result = new rule_check_result();
        // If the subclass inherits the instance_configurable interface then validate that
        // the supplied instance config contains the fields defined by get_instance_settings_fields().
        if (
            $this instanceof instance_configurable &&
            array_diff(array_values(static::get_instance_settings_fields()), array_keys((array) $this->instanceconfig))
        ) {
            throw new coding_exception('Instance config must contain the fields defined by get_instance_settings_fields()');
        }
    }

    /**
     * Get rule instance ID.
     *
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Get the type of rule instance plugin used for this instance.
     *
     * @return string the rule instance type.
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * Is the rule plugin instance enabled?
     *
     * @return bool true if enabled.
     */
    public function get_enabled(): bool {
        return $this->enabled;
    }

    /**
     * Get the rule instance's name.
     *
     * @return string the rule instance name.
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Get the points configured for thie rule instance.
     *
     * @return int the rule instance points.
     */
    public function get_points(): int {
        return $this->points;
    }

    /**
     * Get the fallback points configured for thie rule instance.
     *
     * @return int the rule instance fallback points.
     */
    public function get_fallbackpoints(): int {
        return $this->fallbackpoints;
    }

    /**
     * Get the rule instance's sort order.
     *
     * @return int the rule instance sort order.
     */
    public function get_sortorder(): int {
        return $this->sortorder;
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
     * Return a result indicating this check will allow user registration.
     *
     * @return rule_check_result
     */
    public function allow(): rule_check_result {
        $this->result->set_allowed(true);

        return $this->result;
    }

    /**
     * Return a result indicating this check will deny user registration.
     *
     * @param int $score
     * @param string $feedbackmessage
     * @param array $validationmessages
     * @throws coding_exception
     *
     * @return rule_check_result
     */
    public function deny(
        int $score,
        string $feedbackmessage = '',
        array $validationmessages = []
    ): rule_check_result {
        // At least one of $feedbackmessage or $validationmessages must be set...
        if (!empty($feedbackmessage) && !empty($validationmessages)) {
            throw new coding_exception('One of feedbackmessage or validationmessages params must be set');
        }
        $this->result->set_allowed(false);
        $this->result->set_score($score);
        $this->result->set_feedback_message($feedbackmessage);
        $this->result->set_validation_messages($validationmessages);

        return $this->result;
    }

    /**
     * Inject additional fields into the signup form for usage by the rule instance after submission.
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    public function extend_form(MoodleQuickForm $mform): void {
    }

    /**
     * Perform rule's checks applicable without any user input before the signup form is displayed.
     *
     * @return rule_check_result|null A rule_check_result object or null if check not applicable for this type.
     */
    abstract public function pre_data_check(): ?rule_check_result;

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result|null a rule_check_result object or null if check not applicable for this type.
     */
    abstract public function post_data_check(array $data): ?rule_check_result;
}
