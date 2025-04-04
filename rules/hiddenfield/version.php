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

/**
 * Adds a hidden honeypot field to the registration form, a human would not see this
 * field so if it is submitted with data then we must have a bot.
 *
 * @package   registrationrule_hiddenfield
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version      = 2025010600; // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires     = 2022112813; // Support Moodle 4.0 and higher.
$plugin->component    = 'registrationrule_hiddenfield';
$plugin->release      = 'v0.1';
$plugin->maturity     = MATURITY_STABLE;
$plugin->dependencies = ['tool_registrationrules' => 2024090303];
