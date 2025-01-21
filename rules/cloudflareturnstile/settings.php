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
 * Admin settings for registrationrule_cloudflareturnstile plugin.
 *
 * @package   registrationrule_cloudflareturnstile
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (!\registrationrule_cloudflareturnstile\rule::is_plugin_configured()) {
    global $OUTPUT;
    $setting = new admin_setting_description(
        'registrationrule_cloudflareturnstile/settingsrequired',
        null,
        $OUTPUT->notification(get_string('settingsrequired', 'registrationrule_cloudflareturnstile'), 'error', false)
    );
    $settings->add($setting);
}

$settings->add(new admin_setting_configtext('registrationrule_cloudflareturnstile/cf_sitekey',
    new lang_string('sitekey', 'registrationrule_cloudflareturnstile'),
    null, null, PARAM_ALPHANUMEXT));

$settings->add(new admin_setting_configpasswordunmask('registrationrule_cloudflareturnstile/cf_secret',
    new lang_string('secret', 'registrationrule_cloudflareturnstile'),
    null, null, PARAM_ALPHANUMEXT));
