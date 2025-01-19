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
use tool_registrationrules\local\rule_instances_controller;

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
     * Finds all disabled plugins.
     *
     * @return array of disabled rule plugin names
     */
    public static function get_disabled_plugins(): array {
        $pluginssortorder = get_config('tool_registrationrules', 'plugins_sortorder');
        $enabled = $pluginssortorder ? explode(',', $pluginssortorder) : [];
        $plugins = \core_plugin_manager::instance()->get_installed_plugins('registrationrule');
        $disabled = array_diff(array_keys($plugins), $enabled);

        return $disabled;
    }

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     *
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_enabled_plugins() {
        $pluginssortorder = get_config('tool_registrationrules', 'plugins_sortorder');
        $order = $pluginssortorder ? explode(',', $pluginssortorder) : [];
        if ($order) {
            $plugins = \core_plugin_manager::instance()->get_installed_plugins('registrationrule');
            $order = array_intersect($order, array_keys($plugins));
        }
        return array_combine($order, $order);
    }

    /**
     * Enable or disable a plugin.
     * When possible, the change will be stored into the config_log table, to let admins check when/who has modified it.
     *
     * @param string $pluginname The plugin name to enable/disable.
     * @param int $enabled Whether the pluginname should be enabled (1) or not (0). This is an integer because some plugins, such
     * as filters or repositories, might support more statuses than just enabled/disabled.
     *
     * @return bool Whether $pluginname has been updated or not.
     */
    public static function enable_plugin(string $pluginname, int $enabled): bool {
        $haschanged = false;
        $plugins = [];
        $pluginssortorder = get_config('tool_registrationrules', 'plugins_sortorder');
        if ($pluginssortorder) {
            $plugins = array_flip(explode(',', $pluginssortorder));
        }
        // Only set visibility if it's different from the current value.
        if ($enabled && !array_key_exists($pluginname, $plugins)) {
            $plugins[$pluginname] = $pluginname;
            $haschanged = true;
        } else if (!$enabled && array_key_exists($pluginname, $plugins)) {
            unset($plugins[$pluginname]);
            $haschanged = true;
        }

        if ($haschanged) {
            add_to_config_log('plugins_sortorder', !$enabled, $enabled, $pluginname);
            self::set_enabled_plugins(array_flip($plugins));
        }

        return $haschanged;
    }

    /**
     * Set the registrationrule plugin as enabled or disabled
     * When enabling tries to guess the sortorder based on default rank returned by the plugin.
     *
     * @param bool $newstate
     */
    public function set_enabled(bool $newstate = true) {
        self::enable_plugin($this->name, $newstate);
    }

    /**
     * Set the list of enabled registrationrule plugins in the specified sort order
     * To be used when changing settings or in unit tests
     *
     * @param string|array $list list of plugin names without frankenstyle prefix - comma-separated string or an array
     */
    public static function set_enabled_plugins($list): void {
        if (empty($list)) {
            $list = [];
        } else if (!is_array($list)) {
            $list = explode(',', $list);
        }
        if ($list) {
            $plugins = \core_plugin_manager::instance()->get_installed_plugins('registrationrule');
            $list = array_intersect($list, array_keys($plugins));
        }
        set_config('plugins_sortorder', join(',', $list), 'tool_registrationrules');
        \core_plugin_manager::reset_caches();
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
     * Pre-uninstall hook, removes rule instances of this plugin.
     *
     * @return void
     */
    public function uninstall_cleanup(): void {
        $controller = rule_instances_controller::get_instance();
        $controller->delete_all_instances_of_plugin($this->name);
        parent::uninstall_cleanup();
    }

    /**
     * Return URL used for management of registrationrule-type plugins.
     *
     * @return moodle_url
     * @throws moodle_exception
     */
    public static function get_manage_url(): \moodle_url {
        return new \moodle_url('/admin/settings.php?section=manageregistrationrules');
    }

    /**
     * Returns the node name used in admin settings menu for this plugin settings (if applicable)
     *
     * @return null|string node name or null if plugin does not create settings node (default)
     */
    public function get_settings_section_name(): string {
        return 'toolregistrationrules' . $this->name;
    }

    /**
     * Returns the URL of the plugin settings screen
     *
     * Null value means that the plugin either does not have the settings screen
     * or its location is not available via this library.
     *
     * @return null|moodle_url
     */
    public function get_settings_url(): ?moodle_url {
        $section = $this->get_settings_section_name();
        if ($section === null) {
            return null;
        }

        $settings = admin_get_root()->locate($section);
        if ($settings && $settings instanceof \core_admin\local\settings\linkable_settings_page) {
            return $settings->get_settings_page_url();
        }

        return null;
    }

    /**
     * Loads rule subplugin settings to the settings tree.
     *
     * This function usually includes settings.php file in plugins folder.
     * Alternatively it can create a link to some settings page (instance of admin_externalpage)
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig): void {
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig || !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);

        include($this->full_path('settings.php'));

        $adminroot->add($parentnodename, $settings);
    }
}
