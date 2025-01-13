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
 * Strings for registrationrule_altcha registration rule subplugin.
 *
 * @package   registrationrule_altcha
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['altcha:string:arialinklabel'] = 'Visit Altcha.org';
$string['altcha:string:error'] = 'Verification failed. Try again later.';
$string['altcha:string:expired'] = 'Verification expired. Try again.';
$string['altcha:string:footer'] = 'Protected by <a href="https://altcha.org/" target="_blank" aria-label="Visit Altcha.org">ALTCHA</a>';
$string['altcha:string:label'] = 'I\'m not a robot';
$string['altcha:string:verified'] = 'Verified';
$string['altcha:string:verifying'] = 'Verifying...';
$string['altcha:string:waitalert'] = 'Verifying... please wait.';
$string['challengecomplexity'] = 'Challenge complexity';
$string['challengecomplexity_help'] = 'Altcha works by creating a challenge that must be solved by a user\'s web browser when they tick the "I\'m not a robot" box. The complexity refers to the level of computational effort required to solve this challenge. Higher complexity relates to extra security but requires a longer wait while the challenge is solved.';
$string['failuremessage'] = 'Please tick the box to confirm you are not a bot.';
$string['fallbackfailuremessage'] = 'Problem varifying anti-spam challenge';
$string['logmessage'] = 'Error verifying Altcha challenge';
$string['plugindescription'] = 'Enable Altcha on registration form';
$string['pluginname'] = 'Altcha challenge';
$string['privacy:null_provider:reason'] = 'No user related data is stored, processed or transmitted by this plugin.';
$string['registrationrule:instance:name'] = 'Altcha challenge';
