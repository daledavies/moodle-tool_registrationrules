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

namespace registrationrule_hibp;

use coding_exception;
use curl;
use tool_registrationrules\local\logger\log_info;
use tool_registrationrules\local\rule\post_data_check;
use tool_registrationrules\local\rule_check_result;
use tool_registrationrules\local\rule\rule_interface;
use tool_registrationrules\local\rule\rule_trait;

/**
 * Registration rule restricting registrations based on data from HaveIBeenPwnd.
 *
 * @package   registrationrule_hibp
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Lukas MuLu Müller <info@mulu.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule implements rule_interface, post_data_check {
    use rule_trait;

    /**
     * Perform rule's checks based on form input and user behaviour after signup form is submitted.
     *
     * @param array $data the data array from submitted form values.
     * @return rule_check_result a rule_check_result object.
     * @throws coding_exception
     */
    public function post_data_check(array $data): rule_check_result {
        if (!isset($data['password'])) {
            return null;
        }

        // Create the hash of the password and UPPER it for the API.
        $hash = strtoupper(sha1($data['password']));

        // Get cache.
        $cache = \cache::make('registrationrule_hibp', 'pwhashes');
        $cacheresult = $cache->get($hash);

        // Default is "not matched".
        $matched = false;

        if ($cacheresult !== false) {
            $matched = (bool)$cacheresult;
            // Not found, do an API request.
        } else {
            // Prefix used for search.
            $hashprefix = substr($hash, 0, 5);

            // Call the HIBP-Api with the prefix.
            $curl = new curl();
            $curl->setopt([
                'CURLOPT_CONNECTTIMEOUT' => 2,
                'CURLOPT_TIMEOUT' => 2,
            ]);
            $response = $curl->get('https://api.pwnedpasswords.com/range/' . $hashprefix);

            // Who knows if we are pwned, something went wrong during the API call.
            if ($curl->get_errno()) {
                return $this->deny(
                    score: $this->get_fallbackpoints(),
                    feedbackmessage: get_string('fallbackfailuremessage', 'registrationrule_hibp'),
                    loginfo: new log_info($this, get_string('logmessage', 'registrationrule_hibp'))
                );
            }

            // Loop through the list of given hashes.
            foreach (explode("\n", $response) as $testhash) {
                // If the hash matches, the password exists in a breach.
                if ($hash == $hashprefix . substr($testhash, 0, 35)) {
                    $matched = true;
                }
            }

            // Set cache-data for this hash.
            $cache->set($hash, (int)$matched);
        }

        // Looks like we might have been pwned.
        if ($matched) {
            return $this->deny(
                score: $this->get_points(),
                validationmessages: ['password' => get_string('failuremessage', 'registrationrule_hibp')],
            );
        }

        // We got to this point so looks like we have not been pwned after all.
        return $this->allow();
    }
}
