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
 * Strings for registrationrule_stopforumspam registration rule sublugin.
 *
 * @package   registrationrule_stopforumspam
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['emailfailuremessage'] = 'Your email address was found in the Stop Forum Spam database and has been blocked from registering.';
$string['fallbackfailuremessage'] = 'Unable to verify registration details with the Stop Forum Spam database.';
$string['ipfailuremessage'] = 'Your IP address was found in the Stop Forum Spam database and has been blocked from registering.';
$string['plugindescription'] = 'Check if user IP address, email address or username are listed in the Stop Forum Spam database';
$string['pluginname'] = 'Stop Forum Spam';
$string['privacy:metadata:stopforumspam'] = 'This plugin does not store any user related data on its own. Before accepting new user account registration, your IP address, username and email address are forwarded to the Stop Forum Spam service for comparison against a database of known spammers.';
$string['privacy:metadata:stopforumspam:email'] = 'Your requested email address is sent from Moodle so it can be checked against the Stop Forum Spam database.';
$string['privacy:metadata:stopforumspam:ip'] = 'Your IP address (at the time of account registration) is sent from Moodle so it can be checked against the Stop Forum Spam database.';
$string['privacy:metadata:stopforumspam:username'] = 'Your requested username is sent from Moodle so it can be checked against the Stop Forum Spam database.';
$string['registrationrule:instance:name'] = 'Stop Forum Spam';
$string['usernamefailuremessage'] = 'Your chosen username was found in the Stop Forum Spam database and has been blocked from registering.';
