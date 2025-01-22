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
 * Strings for registrationrule_hcaptcha registration rule subplugin.
 *
 * @package   registrationrule_hcaptcha
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['failuremessage'] = 'The captcha was not solved.';
$string['fallbackfailuremessage'] = 'The captcha cannot be verified at the moment, please try again later.';
$string['logmessage'] = 'Error verifying captcha';
$string['plugindescription'] = 'Enable hCaptcha on registration form';
$string['pluginname'] = 'hCaptcha';
$string['privacy:metadata:payload'] = 'Various information about a user during their session while registering for an account (e.g. IP address, how long the visitor has been on the website or app, or mouse movements made by the user) necessary for hCaptcha\'s function.';
$string['privacy:metadata:summary'] = 'hCaptcha evaluates various information (e.g. IP address, how long the visitor has been on the website or app, or mouse movements made by the user). The data collected during the analysis will be forwarded to Intuition Machines, Inc. hCaptcha analysis in "invisible mode" may take place completely in the background. hCaptcha rule does not store any user related data, and only necessary data is sent Intuition Machines, Inc.';
$string['registrationrule:instance:name'] = 'hCaptcha';
$string['secret'] = 'Secret';
$string['settingsrequired'] = '<p>The Site Key and Secret are both required for this plugin to work.</p>You can find these on the <a href="https://dashboard.hcaptcha.com/sites" target="_blank">hCaptcha Sites</a> page, more information can be found in the <a href="https://docs.hcaptcha.com/switch#get-your-hcaptcha-sitekey-and-secret-key">hCaptcha documentation</a>.';
$string['sitekey'] = 'Site Key';
