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

namespace registrationrule_stopforumspam\form;

use MoodleQuickForm_text;

/**
 * Registration rule restricting registrations based on data from the Stop Forum Spam
 * service (https://www.stopforumspam.com)
 *
 * @package   registrationrule_stopforumspam
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confidence_range_slider extends MoodleQuickForm_text {
    /**
     * Constructs a new MoodleQuickForm_text object for use as a range slider,
     * seting it's type to use PARAM_INT.
     *
     * @param ?string $elementname
     * @param ?string $elementlabel
     * @param ?array $attributes
     */
    public function __construct(?string $elementname=null, ?string $elementlabel=null, ?array $attributes=null) {
        parent::__construct($elementname, $elementlabel, $attributes);
        $this->setType($elementname, PARAM_INT);
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
                'label' => $this->getLabel(),
                'required' => $required,
                'helpbutton' => $helpbutton,
        ];
        $renderer->_html .= $OUTPUT->render_from_template('registrationrule_stopforumspam/confidence_range_slider', $context);
        // Anywhere this element is displayed will also require some JS to make it work properly.
        $PAGE->requires->js_call_amd(
            'registrationrule_stopforumspam/confidence_range_slider',
            'init',
            [
                $this->getAttribute('id'),
            ]
        );
    }
}
