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
