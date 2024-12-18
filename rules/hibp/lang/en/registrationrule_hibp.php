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
 * Strings for registrationrule_hibp registration rule sublugin.
 *
 * @package   registrationrule_hibp
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['failuremessage'] = 'Password found in HIBP database, please try a different one.';
$string['fallbackfailuremessage'] = 'Cannot verify password, problem verifying with HIBP database.';
$string['plugindescription'] = 'Check if user password is listed on Have I Been Pwnd';
$string['pluginname'] = 'Have I been pwned?!';
$string['privacy:forwarded:range'] = 'Sends the first 5 characters of the user\'s hashed password.';
$string['privacy:forwarded:summary'] = 'Have I been pwned rule accesses Have I been pwned\'s API by submitting the first 5 characters of the hashed password provided by the user and does not store any user related data on its own.';
$string['registrationrule:instance:name'] = 'Have I been pwned';
