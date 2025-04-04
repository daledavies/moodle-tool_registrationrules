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
 * Strings for component 'tool_profiling', language 'en', branch 'MOODLE_22_STABLE'
 *
 * @package   tool_registrationrules
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addnewruleinstance'] = 'Add new rule';
$string['addrule'] = 'Add rule';
$string['confirmdelete'] = 'Are you sure you want to delete the "{$a}" rule?';
$string['description'] = 'Description';
$string['editruleinstance'] = 'Edit rule';
$string['editruleinstancename'] = 'Edit rule: {$a}';
$string['event:registrationdenied'] = 'User registration denied';
$string['event:registrationdeniedloggingonly'] = 'User registration denied (logging only)';
$string['generalaftermessage'] = 'General message for users rejected AFTER user input';
$string['hideshow'] = 'Hide/Show';
$string['manageregistrationruleplugins'] = 'Plugins';
$string['onlyoneinstanceallowed'] = 'Only one instance of this rule is allowed!';
$string['pluginname'] = 'Registration rules';
$string['privacy:null_provider:reason'] = 'The Registration rules plugin does not store any user-related data. User related data stored by specific registration rule plugins get declared separately.';
$string['registrationrule:instance:description'] = 'Description';
$string['registrationrule:instance:enabled'] = 'Enabled';
$string['registrationrule:instance:fallbackpoints'] = 'Fallback points';
$string['registrationrule:instance:forgotpassword'] = 'Evaluate this rule on the Forgotten password form';
$string['registrationrule:instance:name'] = 'Name';
$string['registrationrule:instance:points'] = 'Points';
$string['registrationrule:instance:points_help'] = 'The number of points returned from this rule if registration is denied.';
$string['registrationrulepluginname'] = 'Registration rule';
$string['ruleinstancestable:actions'] = 'Actions';
$string['ruleinstancestable:addcaptcha'] = 'Add CAPTCHA';
$string['ruleinstancestable:addrule'] = 'Add rule';
$string['ruleinstancestable:areasused'] = 'Areas used';
$string['ruleinstancestable:captcha'] = 'CAPTCHA';
$string['ruleinstancestable:description'] = 'Description';
$string['ruleinstancestable:disabledarealabel'] = 'Rule not enabled for this area';
$string['ruleinstancestable:disabledmessage'] = 'The Registration Plugin is disabled, rules will not take affect until it is enabled on the <a href="/admin/settings.php?section=generalregistrationrulessettings">Registation rules settings</a> page.';
$string['ruleinstancestable:disabledpluginsmessage'] = 'There are <a href="/admin/settings.php?section=manageregistrationrules">{$a} disabled rule plugins</a>, new rules using these plugins cannot be created until they are enabled.';
$string['ruleinstancestable:enabled'] = 'Enabled';
$string['ruleinstancestable:enabledarealabel'] = 'Rule enabled for this area';
$string['ruleinstancestable:fallbackpoints'] = 'Fallback points';
$string['ruleinstancestable:forcednotification'] = 'Editing is disabled as rules are currently managed via <b>config.php</b>.';
$string['ruleinstancestable:forgotpasswordlabel'] = 'Forgotten password form';
$string['ruleinstancestable:instancesjson:getinstancesjson'] = 'View instances JSON';
$string['ruleinstancestable:instancesjson:modalbodytext'] = '<p>The following can be added to your <b>config.php</b> file to force use of the current active rule configuration.</p><p>When added to <b>config.php</b>, rule management will no longer be available via the UI.</p>';
$string['ruleinstancestable:instancesjson:ruleinstancesjson'] = 'Rule instances JSON';
$string['ruleinstancestable:move'] = 'Move';
$string['ruleinstancestable:name'] = 'Name';
$string['ruleinstancestable:nomorecaptchasmessage'] = 'Only one CAPTCHA type rule can be added at a time. New rules using these plugin types cannot be created until the current CAPTCHA instance is removed.';
$string['ruleinstancestable:norulesaddedyet'] = 'No rules have been added yet, begin by adding a rule and/or CAPTCHA using the dropdown menus below.';
$string['ruleinstancestable:notconfigured'] = 'Not configured';
$string['ruleinstancestable:plugindisabled'] = 'Plugin disabled';
$string['ruleinstancestable:points'] = 'Points';
$string['ruleinstancestable:points:decription'] = 'Number of points issued when registration is denied.';
$string['ruleinstancestable:rulenotenabled'] = 'Rule not enabled';
$string['ruleinstancestable:signuplabel'] = 'Signup form';
$string['ruleinstancestable:siterecaptchaenabled'] = 'reCAPTCHA is enabled in site settings, new CAPTCHA type rules cannot be added.';
$string['ruleinstancestable:type'] = 'Type';
$string['rulewillnotbeused'] = 'This rule will not be used yet as the plugin\'s <a href="{$a}">configuration options</a> have not been set.';
$string['settings:enable:description'] = 'Enable rule checking.';
$string['settings:forgotpassword'] = 'Evaluate appropriate rules on Forgotten password form';
$string['settings:forgotpassword:description'] = 'Include checking of some rules on the Forgotten password form, the rules used are indicated in the "Areas used" column of the rules table.';
$string['settings:generalbeforemessage'] = 'General message for pre-signup rules';
$string['settings:generalbeforemessage:description'] = 'Some rules (e.g. <b>limitdatetime</b> or <b>nope</b>) will stop the signup page from being displayed, this is a static message that will show up above any feedback from those rules.';
$string['settings:guidancemessage'] = '<p>You will need to tick "Enable rule checking" before Registration Rules can work.</p><p>It is recommended however that you first review and set up the available rules and CAPTCHA options, then create some <a href="/admin/tool/registrationrules/manageruleinstances.php">rules</a> before enabling rule checking.</p> You may also wish to consider enabling "Logging only" for a short while to assess the impact of your chosen rules.';
$string['settings:loggingonly'] = 'Logging only';
$string['settings:loggingonly:description'] = 'Evaluate rules as normal but only log the results instead of denying user registration.';
$string['settings:maxpoints'] = 'Max rejection points';
$string['settings:maxpoints:description'] = 'Registration will be denied if this threshold is reached after rules have been evaluated.';
$string['settings:registrationpagemessage'] = 'Message on top of the registration page';
$string['settings:registrationpagemessage:description'] = 'General message displayed at the top of the signup page';
$string['settings:registrationruleinstances'] = 'Rules';
$string['settings:registrationrulessettings'] = 'Settings';
$string['subplugintype_registrationrule'] = 'Registration rule';
$string['subplugintype_registrationrule_plural'] = 'Registration rules';
