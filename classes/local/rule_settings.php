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

use tool_registrationrules\local\rule\rule_interface;

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

    public static function from_rule_instance(int $instanceid) {
        global $DB;
        
        $instancerecord = $DB->get_record('tool_registrationrules', ['id' => $instanceid]);

        $extrafields = json_decode($instancerecord->other);
        
        foreach($extrafields as $fieldname => $value) {
            $instancerecord->$fieldname = $value;
        }
        
        $form = new static($instancerecord->type, $instanceid);
        $form->set_data($instancerecord);

        return $form;
    }

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
        $this->type = $type;
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;

        // Quick and dirty hack to get our types/parameters:
        if ($addruletype = optional_param('addruletype', false, PARAM_ALPHANUM)) {
            $mform->addElement('hidden', 'addruletype', $addruletype);
            $mform->addElement('hidden', 'type', $addruletype);
        }
        $mform->addElement('hidden', 'id', optional_param('id', 0, PARAM_INT));

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
        $mform->setDefault('points', 0);

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
