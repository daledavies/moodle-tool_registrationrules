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
 * Admin settings for tool_registrationrules plugin.
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add(
    'tools',
    new admin_category('toolregistrationrules', new lang_string('pluginname', 'tool_registrationrules')),
);

// The main registrationrules plugin settings.
$settings = new admin_settingpage(
    'generalsettings',
    get_string('registrationrulessettings', 'tool_registrationrules'),
    'moodle/site:config',
);
if ($ADMIN->fulltree) {
    // Enable/disable.
    $name = new lang_string('enable', 'tool_registrationrules');
    $settings->add(
        new admin_setting_configcheckbox(
            'tool_registrationrules/enable',
            $name,
            '',
            0,
        ),
    );
    // Max points.
    $setting = new admin_setting_configtext(
        'tool_registrationrules/maxpoints',
        new lang_string('maxpoints', 'tool_registrationrules'),
        '',
        100,
        PARAM_INT,
    );
    $settings->add($setting);
    // Generic message for registration page.
    $name = new lang_string('registrationpagemessage', 'tool_registrationrules');
    $setting = new admin_setting_configtextarea(
        'tool_registrationrules/registrationpagemessage',
        $name,
        '',
        '',
    );
    $settings->add($setting);
    // General message to show on the error page before any rule specific messages.
    $name = new lang_string('generalbeforemessage', 'tool_registrationrules');
    $setting = new admin_setting_configtextarea(
        'tool_registrationrules/generalbeforemessage',
        $name,
        '',
        '',
    );
    $settings->add($setting);
    // General message to show on the error page after any rule specific messages.
    $name = new lang_string('generalaftermessage', 'tool_registrationrules');
    $setting = new admin_setting_configtextarea(
        'tool_registrationrules/generalaftermessage',
        $name,
        '',
        '',
    );
    $settings->add($setting);
}
$ADMIN->add('toolregistrationrules', $settings);

// Link to registration rule subplugin management page.
$temp = new admin_settingpage('manageregistrationrules', get_string('manageregistrationruleplugins', 'tool_registrationrules'));
$temp->add(new tool_registrationrules\admin\manage_rule_plugins());
$ADMIN->add('toolregistrationrules', $temp);

// Link to rule instances management page.
$manageinstancespage = new admin_externalpage(
    'toolregistrationrules_instances',
    get_string('registrationruleinstances', 'tool_registrationrules'),
    new moodle_url('/admin/tool/registrationrules/manageruleinstances.php'),
    'moodle/site:config',
);
$ADMIN->add('toolregistrationrules', $manageinstancespage);

// Load the settings from registration rule subplugins and add a link to them.
$plugins = core_plugin_manager::instance()->get_plugins_of_type('registrationrule');
core_collator::asort_objects_by_property($plugins, 'displayname');
foreach ($plugins as $plugin) {
    $plugin->load_settings($ADMIN, 'toolregistrationrules', $hassiteconfig);
}
