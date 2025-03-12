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
 * Strings for registrationrule_nope registration rule psublugin.
 *
 * @package   registrationrule_limitdatetime
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowbetweendates'] = 'ALLOW between dates';
$string['denybetweendates'] = 'DENY between dates';
$string['failuremessage:allowbetween'] = 'Registration is allowed between {$a->from} and {$a->to} ({$a->timezone})';
$string['failuremessage:denybetween'] = 'Registration is not allowed between {$a->from} and {$a->to} ({$a->timezone})';
$string['from'] = 'From';
$string['logmessage:allowbetween'] = 'Registration was not between {$a->from} and {$a->to} ({$a->timezone})';
$string['logmessage:denybetween'] = 'Registration was between {$a->from} and {$a->to} ({$a->timezone})';
$string['plugindescription'] = 'Restrict user registration around date/time windows.';
$string['pluginname'] = 'Limit by date';
$string['privacy:null_provider:reason'] = 'There is no user related data stored or processed by the plugin.';
$string['registrationrule:instance:name'] = 'Limit by date';
$string['restrictionmode'] = 'Restriction mode';
$string['resultmessage'] = 'Sorry, the captcha was not solved.';
$string['timezonelabel'] = 'Timezone: {$a}';
$string['to'] = 'To';
