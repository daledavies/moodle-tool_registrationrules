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

$string['cachedef_pwhashes'] = 'Cache for hashed password responses from HiBW';
$string['failuremessage'] = 'Password found in HIBP database, please try a different one.';
$string['fallbackfailuremessage'] = 'Cannot verify password, problem verifying with HIBP database.';
$string['logmessage'] = 'Error connecting to HIBP API';
$string['plugindescription'] = 'Check if user password is listed on Have I Been Pwnd';
$string['pluginname'] = 'Have I been pwned?!';
$string['privacy:metadata:passwordhash'] = 'The first 5 characters of a user\'s hashed password.';
$string['privacy:metadata:summary'] = 'This plugin does not store any user related data on its own. Before accepting a new user registration, a hash of the first 5 characters of a user\'s password are forwarded to the HAve I Been Pwned API for comparison against a database of know compromised passwords.';
$string['registrationrule:instance:name'] = 'Have I been pwned';
