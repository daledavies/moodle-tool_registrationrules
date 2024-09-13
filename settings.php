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
 * @package    tool_registrationrules
 * @subpackage registrationrules
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add(
    'tools',
    new admin_category('toolregistrationrules', new lang_string('pluginname', 'tool_registrationrules')),
);

$settings = new admin_settingpage(
    'generalsettings',
    get_string('registrationrulessettings', 'tool_registrationrules'),
    'moodle/site:config',
);

if ($ADMIN->fulltree) {
    $name = new lang_string('enable', 'tool_registrationrules');
    $settings->add(
        new admin_setting_configcheckbox(
            'tool_registrationrules/enable',
            $name,
            '',
            0,
        ),
    );

    $setting = new admin_setting_configtext(
        'tool_registrationrules/maxpoints',
        new lang_string('maxpoints', 'tool_registrationrules'),
        '',
        100,
        PARAM_INT,
    );
    $settings->add($setting);

    $name = new lang_string('registrationpagemessage', 'tool_registrationrules');
    $setting = new admin_setting_configtextarea(
        'tool_registrationrules/registrationpagemessage',
        $name,
        '',
        '',
    );
    $settings->add($setting);

    $name = new lang_string('generalbeforemessage', 'tool_registrationrules');
    $setting = new admin_setting_configtextarea(
        'tool_registrationrules/generalbeforemessage',
        $name,
        '',
        '',
    );
    $settings->add($setting);

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

$manageinstancespage = new admin_externalpage(
    'toolregistrationrules_instances',
    get_string('registrationruleinstances', 'tool_registrationrules'),
    new moodle_url('/admin/tool/registrationrules/manageruleinstances.php'),
    'moodle/site:config',
);

$ADMIN->add('toolregistrationrules', $manageinstancespage);

$ADMIN->add('toolregistrationrules', new \tool_registrationrules\local\admin_page_rule_plugins('registrationrule'));
