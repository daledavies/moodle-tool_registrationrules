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

namespace tool_registrationrules\admin;

/**
 * This is to fool upgradesettings.php into showing the description as it would
 * usually only show actual setting fields.
 *
 * @package   tool_registrationrules
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_visible_description extends \admin_setting_description {
    /**
     * Will return null if on upgradesettings.php and a sesskey get/post param is part of
     * the request, otherwise will return true.
     *
     * A normal admin_setting_description would just return true from this method and
     * upgradesettings.php would not display the description, but if it sees a null returned
     * instead then it will show the description.
     *
     * If we only returned a null then upgradesettings.php would show the description but would not
     * allow us to continue as it's form processing would not see a value submitted. The combination
     * of the URL base being "/admin/upgradesettings.php" and there NOT being a sesskey param allows us
     * to determine if we're on the correct page and whether it has been submitted.
     *
     * @return ?bool
     */
    public function get_setting(): ?bool {
        global $PAGE;
        $sesskey = optional_param('sesskey', false, PARAM_TEXT);
        if ($PAGE->url->compare(new \moodle_url('/admin/upgradesettings.php'), URL_MATCH_BASE) && !$sesskey) {
            return null;
        }

        return true;
    }
}
