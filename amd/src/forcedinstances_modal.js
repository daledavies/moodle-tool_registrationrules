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
 * Create a modal containing details of the forced rule instance $CFG option, this
 * will show the correct config to use for the current set of rule instances.
 *
 * The modal will show when the getinstncejson button is clicked.
 *
 * @module tool_registrationrules/forcedinstances_modal
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Modal from 'core/modal';
import Templates from 'core/templates';
import {getString} from 'core/str';

/**
 * Main function of this module.
 */
export const init = () => {
    const button = document.getElementById('getinstancejson');
    button.addEventListener('click', async(e) => {
        e.preventDefault();
        await Modal.create({
            title: getString('ruleinstancestable:instancesjson:ruleinstancesjson', 'tool_registrationrules'),
            body: Templates.render('tool_registrationrules/forcedinstances_modal', {
                'forcedinstancesjson': atob(button.dataset.forcedinstancesjson),
            }),
            large: true,
            isVerticallyCentered: true,
            show: true,
        });
    });
};
