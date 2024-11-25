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

namespace registrationrule_hcaptcha\privacy;

use coding_exception;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy provider for HCaptcha registration rule plugin—storing no data on its own,
 * but sending necessary data to HCaptcha servers.
 *
 * @package registrationrule_hcaptcha
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author Philipp Hager <philipp.hager@edaktik.at>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadata_provider {
    /**
     * Returns meta-data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection     A listing of user data stored through this system.
     * @throws coding_exception
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'Intuition Machines, Inc., a Delaware US Corporation',
            [
                'payload' => get_string('privacy:forwarded:payload', 'registrationrule_hcaptcha'),
            ],
            get_string('privacy:forwarded:summary', 'registrationrule_hcaptcha'),
        );
        return $collection;
    }
}
