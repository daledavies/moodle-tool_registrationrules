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
 * Strings for registrationrule_mintime registration rule sublugin.
 *
 * @package   registrationrule_mintime
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['displaydescription'] = 'Account registration must take longer {$a->time} seconds';
$string['failuremessage'] = 'Registration form completed too quickly, are you a bot?';
$string['fallbackfailuremessage'] = 'Failed to decode registration time.';
$string['logmessage'] = 'Account registration took less than {$a->time} seconds.';
$string['plugindescription'] = 'Account registration must take longer than configured time in seconds';
$string['pluginname'] = 'Minimum completion time';
$string['privacy:null_provider:reason'] = 'This plugin does not store any user related data.';
$string['registrationrule:instance:name'] = 'Minimum completion time';
