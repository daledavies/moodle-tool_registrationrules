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

namespace tool_registrationrules\plugininfo;

use dml_exception;
use moodle_exception;
use moodle_url;

/**
 * Plugin info for registration rule sub plugin type.
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
class registrationrule extends \core\plugininfo\base {
    /**
     * Enable or disable a plugin.
     * When possible, the change will be stored into the config_log table, to let admins check when/who has modified it.
     *
     * @param string $pluginname The plugin name to enable/disable.
     * @param int $enabled Whether the pluginname should be enabled (1) or not (0). This is an integer because some plugins, such
     * as filters or repositories, might support more statuses than just enabled/disabled.
     *
     * @return bool Whether $pluginname has been updated or not.
     * @throws dml_exception
     */
    public static function enable_plugin(string $pluginname, int $enabled): bool {
        $haschanged = false;

        $plugin = 'registrationrule_' . $pluginname;
        $oldvalue = get_config($plugin, 'disabled');
        $disabled = !$enabled;
        // Only set value if there is no config setting or if the value is different from the previous one.
        if ($oldvalue === false || ((bool) $oldvalue != $disabled)) {
            set_config('disabled', $disabled, $plugin);
            $haschanged = true;

            add_to_config_log('disabled', $oldvalue, $disabled, $plugin);
            \core_plugin_manager::reset_caches();
        }

        return $haschanged;
    }

    /**
     * Should there be a way to uninstall the plugin via the administration UI.
     *
     * By default, uninstallation is not allowed; plugin developers must enable it explicitly!
     *
     * @return bool
     */
    public function is_uninstall_allowed(): bool {
        return true;
    }

    /**
     * Return URL used for management of registrationrule-type plugins.
     *
     * @return moodle_url
     * @throws moodle_exception
     */
    public static function get_manage_url() {
        return new \moodle_url('/admin/tool/registrationrules/managerules.php', ['subtype' => 'registrationrule']);
    }
}
