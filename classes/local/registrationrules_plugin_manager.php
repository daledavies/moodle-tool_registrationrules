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
 * Facilitate management of registration rule plugins.
 *
 * @package    tool_registrationrules
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_registrationrules\local;

require_once $CFG->libdir.'/adminlib.php';

/**
 * Class that handles the display and configuration of the list of submission plugins.
 *
 * @package   tool_registrationrules
 */
class registrationrules_plugin_manager {

    /** @var object the url of the manage submission plugin page */
    private $pageurl;
    /** @var string any error from the current action */
    private $error = '';
    /** @var string either submission or feedback */
    private $subtype = '';

    /**
     * Constructor for this plugin manager
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->pageurl = new \moodle_url('/admin/tool/registrationrules/managerules.php', ['subtype' => $subtype]);
        $this->subtype = $subtype;
    }


    /**
     * Return a list of plugins sorted by the order defined in the admin interface
     *
     * @return array The list of plugins
     */
    public function get_sorted_plugins_list() {
        $names = \core_component::get_plugin_list($this->subtype);

        $result = [];

        foreach ($names as $name => $path) {
            $idx = get_config($this->subtype . '_' . $name, 'sortorder');
            if (!$idx) {
                $idx = 0;
            }
            while (array_key_exists($idx, $result)) {
                $idx += 1;
            }
            $result[$idx] = $name;
        }
        ksort($result);

        return $result;
    }


    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $plugin URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link($action, $plugin, $icon, $alt) {
        global $OUTPUT;

        $url = $this->pageurl;

        if ($action === 'delete') {
            $url = \core_plugin_manager::instance()->get_uninstall_url($this->subtype.'_'.$plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return \html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        return $OUTPUT->action_icon(new \moodle_url($url,
                ['action' => $action, 'plugin' => $plugin, 'sesskey' => sesskey()]),
                new \pix_icon($icon, $alt, 'moodle', ['title' => $alt]),
                null, ['title' => $alt]) . ' ';
    }

    /**
     * Write the HTML for the submission plugins table.
     *
     * @return None
     */
    private function view_plugins_table() {
        global $OUTPUT, $CFG;
        require_once($CFG->libdir . '/tablelib.php');

        // Set up the table.
        $this->view_header();
        $table = new \flexible_table($this->subtype . 'pluginsadminttable');
        $table->define_baseurl($this->pageurl);
        $table->define_columns(['pluginname', 'version', 'settings', 'uninstall']);
        $table->define_headers([get_string($this->subtype . 'pluginname', 'tool_registrationrules'),
                get_string('version'), get_string('settings'), get_string('uninstallplugin', 'core_admin')]);
        $table->set_attribute('id', $this->subtype . 'plugins');
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();

        $plugins = $this->get_sorted_plugins_list();
        $shortsubtype = substr($this->subtype, strlen('registration'));

        foreach ($plugins as $idx => $plugin) {
            $row = [];
            $class = '';

            $row[] = get_string('pluginname', $this->subtype . '_' . $plugin);
            $row[] = get_config($this->subtype . '_' . $plugin, 'version');

            $exists = file_exists($CFG->dirroot . '/admin/tool/registrationrules/' . $shortsubtype . '/' . $plugin . '/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = \html_writer::link(new \moodle_url('/admin/settings.php',
                        ['section' => $this->subtype . '_' . $plugin]), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $row[] = $this->format_icon_link('delete', $plugin, 't/delete', get_string('uninstallplugin', 'core_admin'));

            $table->add_data($row, $class);
        }

        $table->finish_output();
        $this->view_footer();
    }

    /**
     * Write the page header
     *
     * @return None
     */
    private function view_header() {
        global $OUTPUT;
        admin_externalpage_setup('manage' . $this->subtype . 'plugins');
        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manage' . $this->subtype . 'plugins', 'tool_registrationrules'));
    }

    /**
     * Write the page footer
     *
     * @return None
     */
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the list of installed plugins
     *
     * @return None
     */
    private function check_permissions() {
        // Check permissions.
        require_login();
        $systemcontext = \context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * This is the entry point for this controller class.
     *
     * @param string $action - The action to perform
     * @param string $plugin - Optional name of a plugin type to perform the action on
     * @return None
     */
    public function execute($action, $plugin) {
        if ($action == null) {
            $action = 'view';
        }

        $this->check_permissions();

        // View.
        if ($action == 'view') {
            $this->view_plugins_table();
        }
    }
}
