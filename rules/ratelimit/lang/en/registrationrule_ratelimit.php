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
 * Strings for registrationrule_ratelimit registration rule psublugin.
 *
 * @package   registrationrule_ratelimit
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['failuremessage'] = 'The number of allowed signup attempts has been reached, please try again later.';
$string['logmessage:ip'] = 'IP address was rate limited... {$a}';
$string['logmessage:session'] = 'Session was rate limited';
$string['plugindescription'] = 'Limit the number of signup attempts within a time window';
$string['pluginname'] = 'Rate Limit';
$string['privacy:null_provider:reason'] = 'This rule does not store identifiable user data.';
$string['registrationrule:instance:name'] = 'Rate Limit';
$string['registrationrule:instance:settings:help'] = 'This rule allows administrators to set individual parameters for both session and IP-based rate limiting, specifying the maximum number of allowed attempts within a defined time window. If the limit is exceeded, further registration attempts are temporarily blocked.';
$string['registrationrule:instance:settings:ipratelimitgroup'] = 'IP rate limiting options';
$string['registrationrule:instance:settings:limit'] = 'Limit';
$string['registrationrule:instance:settings:sessionratelimitgroup'] = 'Session rate limiting options';
$string['registrationrule:instance:settings:timewindow'] = 'Time window in seconds';
