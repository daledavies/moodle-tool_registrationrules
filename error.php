<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Error page used to display specific information about why the registration has been blocked.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * As the registration has been blocked, we can't be logged in at this point.
 * phpcs:ignoreFile moodle.Files.RequireLogin.Missing
 */

require_once(__DIR__ . '/../../../config.php');

$ver = optional_param('ver', null, PARAM_TEXT);

$PAGE->set_url('/admin/tool/registrationrules/error.php');
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();

if ($ver === 'before') {
    \core\notification::warning(get_config('tool_registrationrules', 'generalbeforemessage'));
} else if ($ver === 'after') {
    \core\notification::warning(get_config('tool_registrationrules', 'generalaftermessage'));
}

echo $OUTPUT->footer();
