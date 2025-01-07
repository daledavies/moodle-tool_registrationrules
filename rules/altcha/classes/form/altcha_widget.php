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

namespace registrationrule_altcha\form;

use MoodleQuickForm_text;

/**
 * Custom form element for an Altcha challenge widget.
 *
 * @package   registrationrule_altcha
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class altcha_widget extends MoodleQuickForm_text {

    /** @var ?string JSON encoded Altcha challenge array */
    private ?string $challengejson;

    /** @var ?int Maximum number for the random number generator */
    private ?int $maxnumber;

    /**
     * Constructs a new Moodle form object for use as Altcha widget.
     *
     * @param ?string $elementname
     * @param ?string $challengejson JSON encoded Altcha challenge array
     * @param ?int $maxnumber Maximum number for the random number generator
     */
    public function __construct(?string $elementname = null, ?string $challengejson = '', ?int $maxnumber = 100000) {
        parent::__construct($elementname, '', '');
        $this->setType($elementname, PARAM_RAW);
        $this->challengejson = $challengejson;
        $this->maxnumber = $maxnumber;
    }

    /**
     * Manipulate the form renderer to use our own template.
     *
     * @param object $renderer An HTML_QuickForm_Renderer object
     * @param bool $required Whether an element is required
     * @param string $error An error message associated with an element
     *
     * @return void
     */
    public function accept(&$renderer, $required = false, $error = null): void {
        global $OUTPUT;

        // Generate the correct ID for this form element.
        $this->_generateId();

        // Strings for ues as JSON encoded object when passed to widget template.
        $strings = [
            'ariaLinkLabel' => get_string('altcha:string:arialinklabel', 'registrationrule_altcha'),
            'error' => get_string('altcha:string:error', 'registrationrule_altcha'),
            'expired' => get_string('altcha:string:expired', 'registrationrule_altcha'),
            'footer' => get_string('altcha:string:footer', 'registrationrule_altcha'),
            'label' => get_string('altcha:string:label', 'registrationrule_altcha'),
            'verified' => get_string('altcha:string:verified', 'registrationrule_altcha'),
            'verifying' => get_string('altcha:string:verifying', 'registrationrule_altcha'),
            'waitAlert' => get_string('altcha:string:waitalert', 'registrationrule_altcha'),
        ];

        // Set the template context and render the HTML for use by the form renderer.
        $context = [
                'element' => $this->export_for_template($OUTPUT),
                'label' => $this->getLabel(),
                'challengejson' => $this->challengejson,
                'maxnumber' => $this->maxnumber,
                'strings' => json_encode($strings),
        ];
        $renderer->_html .= $OUTPUT->render_from_template('registrationrule_altcha/altcha_widget', $context);
    }
}
