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
 * Basic admin_externalpage wrapper to allow finding tool_registrationrules subplugins
 * for admin settings pages.
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
class admin_page_rule_plugins extends \admin_externalpage {
    /** @var string the name of plugin subtype */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new \moodle_url('/admin/tool/registrationrules/managerules.php', ['subtype' => $subtype]);
        parent::__construct(
            'manage' . $subtype . 'plugins',
            get_string('manageregistrationruleplugins', 'tool_registrationrules'),
            $url,
        );
    }

    /**
     * Search plugins for the specified string
     *
     * @param string $query The string to search for
     * @return array
     */
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (\core_component::get_plugin_list($this->subtype) as $name => $notused) {
            $position = strpos(\core_text::strtolower(get_string('pluginname', $this->subtype . '_' . $name)), $query);
            if ($position !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new \stdClass();
            $result->page     = $this;
            $result->settings = [];
            return [$this->name => $result];
        } else {
            return [];
        }
    }
}
