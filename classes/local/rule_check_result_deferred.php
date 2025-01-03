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

use Closure;

/**
 * Object to represent the result of a registration rule check.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_check_result_deferred extends rule_check_result {

    /**
     * A closure that allows rule_checker to determine if the result is valid at a later time,
     * once all other rule instances have been checked. This should return a boolean indicating
     * if the result is still valid.
     *
     * @var Closure $resolvecallback
     */
    private Closure $resolvecallback;

    /**
     * Set the resolvecallback closure.
     *
     * @param Closure $resolvecallback
     * @return void
     */
    public function set_resolvecallback(Closure $resolvecallback): void {
        $this->resolvecallback = $resolvecallback;
    }

    /**
     * Call the resolvecallback closure and return it's result.
     *
     * @return boolean
     */
    public function resolve(): bool {
        $callback = $this->resolvecallback;
        return $callback();
    }

}
