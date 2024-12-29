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

namespace tool_registrationrules\local\logger;

/**
 * Simple class for managing logging of information from rules.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logger {
    /** @var log_info[] Array of log_items to log */
    protected array $logitems = [];

    /**
     * Get an array of logged items.
     *
     * @return log_info[] Array of log_items.
     */
    public function get_log_items(): array {
        return $this->logitems;
    }

    /**
     * Log an item.
     *
     * @param log_info $loginfo Info to log.
     */
    public function log(log_info $loginfo) {
        $this->logitems[] = $loginfo;
    }

    /**
     * Create and return a string representing basic information from logged items.
     *
     * @return string|null
     */
    public function create_rule_details_string(): ?string {
        if (empty($this->logitems)) {
            debugging('logger::create_rule_details_string() called but there are no log items');
            return null;
        }
        $rules = [];
        $totalpoints = 0;
        foreach ($this->logitems as $item) {
            $totalpoints += $item->get_rule_points();
            $rules[] = $item->get_rule_name() . ' (' . $item->get_rule_type() . '):' . PHP_EOL
                        . '- Points: ' . $item->get_rule_points() . PHP_EOL
                        . ($item->get_additional_info() ? '- ' . $item->get_additional_info() . PHP_EOL : '');
        }
        return 'Total points = ' . $totalpoints . '/' . get_config('tool_registrationrules', 'maxpoints')
            . PHP_EOL . PHP_EOL . implode(PHP_EOL, $rules);
    }
}
