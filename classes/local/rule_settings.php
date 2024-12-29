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

use coding_exception;
use dml_exception;
use tool_registrationrules\local\rule\instance_configurable;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Registrationrule base settings form
 *
 * @package tool_registrationrules
 * @copyright 2024 eDaktik GmbH {@link https://www.edaktik.at/}
 * @author    Philipp Hager <philipp.hager@edaktik.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_settings extends \moodleform {
    /** @var string The registrationrule's type */
    protected string $type;

    /** @var string|null Optional instance ID */
    protected ?int $instanceid;

    /** @var string The class name for this rule instance object */
    protected string $ruleinstanceclass;

    /** @var bool Has this instance's plugin been configured correctly */
    protected bool $pluginconfigured;

    /** @var array $customfields the registrationrule type's added form fields' names */
    protected array $customfields = [];

    /**
     * Construct the form object from rule instance's id
     *
     * @param int $instanceid rule instance's database id
     * @return static
     * @throws dml_exception
     */
    public static function from_rule_instance(int $instanceid): static {
        $controller = new rule_instances_controller();
        $ruleinstance = $controller->get_rule_instance_by_id($instanceid);

        // Define data for standard form fields.
        $data = [
            'id' => $ruleinstance->get_id(),
            'type' => $ruleinstance->get_type(),
            'enabled' => $ruleinstance->get_enabled(),
            'name' => $ruleinstance->get_name(),
            'description' => $ruleinstance->get_description(),
            'points' => $ruleinstance->get_points(),
            'fallbackpoints' => $ruleinstance->get_fallbackpoints(),
            'sortorder' => $ruleinstance->get_sortorder(),
        ];

        // Merge in instance specific field data if rule plugin class specifies it.
        if ($ruleinstance instanceof instance_configurable) {
            $data = array_merge($data, (array) $ruleinstance->get_instance_config());
        }

        // Create our form and set it's data.
        $form = new static($ruleinstance->get_type(), $instanceid);
        $form->set_data($data);

        return $form;
    }

    /**
     * The constructor function calls the abstract function definition() and it will then
     * process, clean and attempt to validate incoming data.
     *
     * It will call your custom validate method to validate data and will also check any rules
     * you have specified in definition using addRule
     *
     * The name of the form (id attribute of the form) is automatically generated depending on
     * the name you gave the class extending moodleform.
     *
     * @param string $type rule instance type
     * @param int|null $instanceid rule instance id (when updating existing rule instance)
     *                             TODO: implement proper usage of $instanceid here during instance updates!
     * @param mixed $action the action attribute for the form. If left empty, defaults to auto-detect the current url.
     *                      If a moodle_url object then outputs params as hidden variables.
     * @param mixed $customdata if your form definition method needs access to data such as $course,  $cm, etc. to
     *                          construct the form definition then pass it in this array.
     * @param string $method if you set this to anything other than 'post' then _GET and _POST will
     *                       be merged and used as incoming data to the form.
     * @param string $target target frame for form submission. You will rarely use this. Don't use
     *                       it if you don't need to as the target attribute is deprecated in xhtml strict.
     * @param mixed $attributes you can pass a string of HTML attributes here or an array.
     *                          Special attribute 'data-random-ids' will randomise generated elements' id attributes.
     *                          This is necessary when there are several forms on the same page.
     *                          Special attribute 'data-double-submit-protection' set to 'off' will turn off
     *                          double-submit protection JavaScript - this may be necessary if your form sends
     *                          downloadable files in response to a submit-button, and can't call
     *                          \core_form\util::form_download_complete();
     * @param bool $editable
     * @param array $ajaxformdata Forms submitted via ajax, must pass their data here, instead of relying on _GET and _POST.
     */
    public function __construct(
        string $type,
        ?int $instanceid = null,
        $action = null,
        $customdata = null,
        $method = 'post',
        $target = '',
        $attributes = null,
        $editable = true,
        $ajaxformdata = null
    ) {
        // Check if a rule plugin of this type is actually installed.
        $plugins = \core_plugin_manager::instance()->get_installed_plugins('registrationrule');
        if (!in_array($type, array_keys($plugins))) {
            throw new coding_exception('Plugin type does not exist.');
        }

        $this->type = $type;
        $this->instanceid = $instanceid;
        $this->ruleinstanceclass = 'registrationrule_' . $this->type . '\rule';

        // Allow the rule instance to check if the plugin itself is properly configured.
        $this->pluginconfigured = true;
        if (is_subclass_of($this->ruleinstanceclass, 'tool_registrationrules\local\rule\plugin_configurable')) {
            $this->pluginconfigured = $this->ruleinstanceclass::is_plugin_configured();
        }

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Form definition.
     */
    protected function definition(): void {
        global $OUTPUT;

        $mform = $this->_form;

        // Quick and dirty hack to get our types/parameters...
        if ($addruletype = optional_param('addruletype', false, PARAM_ALPHANUM)) {
            $mform->addElement('hidden', 'addruletype', $addruletype);
            $mform->setType('addruletype', PARAM_ALPHANUM);
            $mform->addElement('hidden', 'type', $addruletype);
            $mform->setType('type', PARAM_ALPHANUM);
        }
        $mform->addElement('hidden', 'id', optional_param('id', 0, PARAM_INT));
        $mform->setType('id', PARAM_INT);

        // Add a notification to alert if the plugin has not been configured.
        if (!$this->pluginconfigured) {
            // Get an instance of plugininfo for the subplugin type. This allows us to get the
            // settings URL for the subplugin, rather than the parent plugin.
            $ruleplugin = \core_plugin_manager::instance()->get_plugins_of_type('registrationrule')[$this->type];
            $settingsurl = (string) $ruleplugin->get_settings_url();
            $notificationmessage = get_string('rulewillnotbeused', 'tool_registrationrules', $settingsurl);
            $info = $OUTPUT->notification($notificationmessage, 'error', false);
            $mform->addElement('html', $info);
        }

        // Begin adding normal form elements.
        $mform->addElement(
            'selectyesno',
            'enabled',
            get_string('registrationrule:instance:enabled', 'tool_registrationrules'),
        );
        $mform->setType('enabled', PARAM_BOOL);
        $mform->setDefault('enabled', 0);

        // Sortorder is only changeable via the list of registrationrule instances.

        $mform->addElement('text', 'name', get_string('registrationrule:instance:name', 'tool_registrationrules'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', get_string('registrationrule:instance:name', 'registrationrule_' . $this->type));

        // TODO: replace with WYSIWYG editor!
        $mform->addElement(
            'textarea',
            'description',
            get_string('registrationrule:instance:description', 'tool_registrationrules'),
        );
        $mform->setType('description', PARAM_RAW);
        $mform->setDefault('description', '');

        $mform->addElement('text', 'points', get_string('registrationrule:instance:points', 'tool_registrationrules'));
        $mform->setType('points', PARAM_INT);
        $mform->setDefault('points', 100);

        $mform->addElement(
            'text',
            'fallbackpoints',
            get_string('registrationrule:instance:fallbackpoints', 'tool_registrationrules'),
        );
        $mform->setType('fallbackpoints', PARAM_INT);
        $mform->setDefault('fallbackpoints', 0);

        // If this is defined as a configurable instance then allow it to extend the settings form.
        if (is_subclass_of($this->ruleinstanceclass, 'tool_registrationrules\local\rule\instance_configurable')) {
            $this->ruleinstanceclass::extend_settings_form($mform);
        }

        $this->add_action_buttons();
    }

    /**
     * Overrides formslib's add_action_buttons() method to allow changing
     * submit button label and/or disabling it.
     *
     * @param bool $cancel
     * @param string|null $submitlabel
     *
     * @return void
     */
    public function add_action_buttons($cancel = true, $submitlabel = null): void {
        $mform =& $this->_form;
        // Set submit button label based on if we are editing or adding a rule instance.
        $submitlabel = get_string('addrule', 'tool_registrationrules');
        if ($this->instanceid) {
            $submitlabel = get_string('savechanges');
        }
        // Recreate the standard button array.
        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
