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

use Closure;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule_check_result_deferred;
use tool_registrationrules\local\logger\log_info;

/**
 * Main interface for registration rule subplugin classes, all rule classes
 * must implement at least this interface.
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
interface rule_interface {
    /**
     * Get rule instance ID.
     *
     * @return int
     */
    public function get_id(): int;

    /**
     * Set rule instance ID.
     *
     * @param int $id
     * @return int
     */
    public function set_id(int $id): int;

    /**
     * Get the type of rule instance plugin used for this instance.
     *
     * @return string the rule instance type.
     */
    public function get_type(): string;

    /**
     * Set the type of rule instance plugin used for this instance.
     *
     * @param string $type
     * @return string
     */
    public function set_type(string $type): string;

    /**
     * Is the rule plugin instance enabled?
     *
     * @return bool true if enabled.
     */
    public function get_enabled(): bool;

    /**
     * Enable the rule plugin instance.
     *
     * @param bool $enabled
     * @return bool
     */
    public function set_enabled(bool $enabled): bool;

    /**
     * Get the rule instance's name.
     *
     * @return string the rule instance name.
     */
    public function get_name(): string;

    /**
     * Set  the rule instance's name.
     *
     * @param string $name
     * @return string
     */
    public function set_name(string $name): string;

    /**
     * Get the rule instance's description as configured in it's settings.
     *
     * @return string the rule instance description.
     */
    public function get_description(): string;

    /**
     * Get description to display, allows plugins to set a default description for
     * display if the description setting is not configured.
     *
     * @return string
     */
    public function get_display_description(): string;

    /**
     * Set the rule instance's description.
     *
     * @param string $description
     * @return string
     */
    public function set_description(string $description): string;

    /**
     * Get the points configured for thie rule instance.
     *
     * @return int the rule instance points.
     */
    public function get_points(): int;

    /**
     * Set the points configured for thie rule instance.
     *
     * @param int $points
     * @return int
     */
    public function set_points(int $points): int;

    /**
     * Get the fallback points configured for thie rule instance.
     *
     * @return int the rule instance fallback points.
     */
    public function get_fallbackpoints(): int;

    /**
     * Set the fallback points configured for thie rule instance.
     *
     * @param int $fallbackpoints
     * @return int
     */
    public function set_fallbackpoints(int $fallbackpoints): int;

    /**
     * Get the rule instance's sort order.
     *
     * @return int the rule instance sort order.
     */
    public function get_sortorder(): int;

    /**
     * Set the rule instance's sort order.
     *
     * @param int $sortorder
     * @return int
     */
    public function set_sortorder(int $sortorder): int;

    /**
     * Return a result indicating this check will allow user registration.
     *
     * @return rule_check_result
     */
    public function allow(): rule_check_result;

    /**
     * Return a result indicating this check will deny user registration.
     *
     * @param int $score
     * @param string $feedbackmessage
     * @param array $validationmessages
     * @param ?log_info $loginfo Info to log.
     * @throws coding_exception
     *
     * @return rule_check_result
     */
    public function deny(
        int $score,
        string $feedbackmessage = '',
        array $validationmessages = [],
        ?log_info $loginfo = null,
    ): rule_check_result;

    /**
     * Return a result indicating this check will deny user registration, deferred
     * until all rules have been evaluated and the rule instance that issued this result has
     * validated that the deferred result.
     *
     * A closure must be provided for the resolvecallback parameter that allows rule_checker to
     * determine if the result is valid once all other rule instances have been checked. This should
     * return a boolean indicating if the result is still valid.
     *
     * @param Closure $resolvecallback
     * @param int $score
     * @param string $feedbackmessage
     * @param array $validationmessages
     * @param ?log_info $loginfo Info to log.
     * @throws coding_exception
     *
     * @return rule_check_result_deferred
     */
    public function deferred_deny(
        Closure $resolvecallback,
        int $score,
        string $feedbackmessage = '',
        array $validationmessages = [],
        ?log_info $loginfo = null,
    ): rule_check_result_deferred;
}
