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
use registrationrule_altcha\rule;

/**
 * Range slider form input used for selecting complexity level in Altcha
 * registration rule plugin settings.
 *
 * @package   registrationrule_altcha
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class complexity_range_slider extends MoodleQuickForm_text {

    /** @var ?int Maximum complexity number */
    protected ?int $max;

    /** @var ?int Minimum complexity number */
    protected ?int $min;

    /** @var ?int Step granularity for range slider */
    protected ?int $step;

    /**
     * Constructs a new MoodleQuickForm_text object for use as a range slider.
     *
     * @param ?string $elementname
     * @param ?string $elementlabel
     * @param ?int $min Minimum complexity number
     * @param ?int $max Maximum complexity number
     * @param ?int $step Step granularity for range slider
     */
    public function __construct(?string $elementname=null, ?string $elementlabel=null,
        ?int $min = rule::COMPLEXITY_MIN, ?int $max = rule::COMPLEXITY_MAX, ?int $step = rule::COMPLEXITY_STEP) {
        parent::__construct($elementname, $elementlabel, '');
        $this->setType($elementname, PARAM_INT);
        $this->max = $max;
        $this->min = $min;
        $this->step = $step;
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
        global $OUTPUT, $PAGE;
        // Generate the correct ID for this form element.
        $this->_generateId();
        // Get the help button for our template cotntext.
        $helpbutton = '';
        if (method_exists($this, 'getHelpButton')) {
            $helpbutton = $this->getHelpButton();
        }
        // Set the template context and render the HTML for use by the form renderer.
        $context = [
                'element' => $this->export_for_template($OUTPUT),
                'max' => $this->max,
                'min' => $this->min,
                'step' => $this->step,
                'label' => $this->getLabel(),
                'required' => $required,
                'helpbutton' => $helpbutton,
        ];
        $renderer->_html .= $OUTPUT->render_from_template('registrationrule_altcha/complexity_range_slider', $context);
        // Anywhere this element is displayed will also require some JS to make it work properly.
        $PAGE->requires->js_call_amd(
            'registrationrule_altcha/complexity_range_slider',
            'init',
            [
                $this->getAttribute('id'),
            ]
        );
    }
}
