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

namespace tool_registrationrules\local;

/**
 * Miscellaneous helper functions that don't fit well into the context of any other
 * classes.
 *
 * @package   tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helpers {
    /**
     * Generic error page to display when access is blocked.
     *
     * @param string $pagetitle
     * @param string $messages
     * @return never
     */
    public static function error_page(string $pagetitle, string $messages): never {
        global $OUTPUT, $PAGE;
        // Set an appropriate status code.
        header("HTTP/1.0 403 Forbidden");
        // Build and output the page with appropriate messages.
        $login = get_string('login');
        $PAGE->navbar->add($login);
        $PAGE->navbar->add($pagetitle);
        $PAGE->set_pagelayout('login');
        $PAGE->set_title($pagetitle);
        $PAGE->set_heading($pagetitle);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($pagetitle);
        if ($generalmessage = get_config('tool_registrationrules', 'generalbeforemessage')) {
            echo $OUTPUT->notification(format_text($generalmessage, FORMAT_HTML), 'info', false);
        }
        echo $OUTPUT->notification($messages, 'warning', false);
        echo $OUTPUT->footer();
        // Exit here to prevent the any further output from the calling script.
        die();
    }
}
