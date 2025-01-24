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
 * Strings for registrationrule_turnstile registration rule subplugin.
 *
 * @package   registrationrule_cloudflareturnstile
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['failuremessage'] = 'Please tick the box to verify you are human.';
$string['fallbackfailuremessage'] = 'The captcha cannot be verified at the moment, please try again later.';
$string['logmessage'] = 'Error verifying captcha';
$string['plugindescription'] = 'Enable Cloudflare Turnstile on registration form';
$string['pluginname'] = 'Cloudflare Turnstile';
$string['privacy:metadata:payload'] = 'Information about a user during their session while registering for an account (e.g. IP address, browser fingerprint) necessary for Cloudflare Turnstile\'s function.';
$string['privacy:metadata:summary'] = 'Coudflare Turnstile evaluates various information (e.g. IP address, (e.g. IP address, browser fingerprint). The data collected during the analysis will be forwarded to Cloudflare. This plugin does not store any user related data, and only necessary data is sent to Cloudflare.';
$string['registrationrule:instance:name'] = 'Cloudflare Turnstile';
$string['secret'] = 'Secret';
$string['settingsrequired'] = '<p>If you choose to use Cloudflare Turnstile, you will first need to generate a Site Key and Secret.</p> You can generate these by logging into the Cloudflare dashboard and clicking "Turnstyle", more information can be found in the <a href="https://developers.cloudflare.com/turnstile/get-started/">Cloudflare Turnstile documentation</a>.';
$string['sitekey'] = 'Site Key';
