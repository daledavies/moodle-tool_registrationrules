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

defined('MOODLE_INTERNAL') || die;

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
        $instancerecord = $controller->get_rule_instance_by_id($instanceid);

        $extrafields = json_decode($instancerecord->other);

        foreach ($extrafields as $fieldname => $value) {
            $instancerecord->$fieldname = $value;
        }

        $form = new static($instancerecord->type, $instanceid);
        $form->set_data($instancerecord);

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
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Form definition.
     */
    protected function definition(): void {
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

        // TODO: replace with WYSIWYG editor!
        $mform->addElement(
            'textarea',
            'message',
            get_string('registrationrule:instance:message', 'tool_registrationrules'),
        );
        $mform->setType('message', PARAM_RAW);
        $mform->setDefault('message', '');
        $mform->addElement(
            'checkbox',
            'displaymessage',
            '',
            get_string('registrationrule:instance:displaymessage', 'tool_registrationrules'),
        );
        $mform->setType('displaymessage', PARAM_BOOL);
        $mform->setDefault('displaymessage', 0);

        $mform->addElement('text', 'points', get_string('registrationrule:instance:points', 'tool_registrationrules'));
        $mform->setType('points', PARAM_INT);
        $mform->setDefault('points', 100);

        $mform->addElement(
            'selectyesno',
            'invert_rule',
            get_string('registrationrule:instance:invert_rule', 'tool_registrationrules'),
        );
        $mform->setDefault('invert_rule', 0);
        $mform->setAdvanced('invert_rule');

        $mform->addElement(
            'text',
            'fallbackpoints',
            get_string('registrationrule:instance:fallbackpoints', 'tool_registrationrules'),
        );
        $mform->setType('fallbackpoints', PARAM_INT);
        $mform->setDefault('fallbackpoints', 0);

        // Give the registration_rule the option to extend our settings form.
        call_user_func(
            ['registrationrule_' . $this->type . '\rule', 'extend_settings_form'],
            $mform,
        );

        $this->add_action_buttons();
    }
}
