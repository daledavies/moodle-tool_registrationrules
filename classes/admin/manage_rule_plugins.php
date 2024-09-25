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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Facilitate management of registration rule plugins.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_rule_plugins extends \admin_setting_manage_plugins {
    /**
     * Get the admin settings section title (use get_string).
     *
     * @return string
     */
    public function get_section_title(): string {
        return get_string('manageregistrationruleplugins', 'tool_registrationrules');
    }

    /**
     * Get the type of plugin to manage.
     *
     * @return string
     */
    public function get_plugin_type(): string {
        return 'registrationrule';
    }

    /**
     * Get the name of the second column.
     *
     * @return string
     */
    public function get_info_column_name() {
        return get_string('description', 'tool_registrationrules');
    }

    /**
     * Get the rule plugin descriptopm.
     *
     * @param plugininfo $pluginfo The plugin info class.
     * @return string
     */
    public function get_info_column($pluginfo) {
        return get_string('plugindescription', 'registrationrule_' . $pluginfo->name);
    }

    /**
     * The URL for the management page for this plugintype.
     *
     * @return moodle_url
     */
    public function get_manage_url(): \moodle_url {
        return new \moodle_url('/admin/tool/registrationrules/managerulepluginactions.php');
    }
}
