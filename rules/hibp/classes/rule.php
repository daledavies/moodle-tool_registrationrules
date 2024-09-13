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

use tool_registrationrules\local\rule_check_result;

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
class rule extends \tool_registrationrules\local\rule\rule_base {
    public function post_data_check($data): ?rule_check_result {

        if (!isset($data['password'])) {
            return null;
        }

        // Create the hash of the password and UPPER it for the API.
        $hash = strtoupper(sha1($data['password']));

        // Get cache.
        $cache = \cache::make('registrationrule_hibp', 'pwhashes');

        $cacheresult = $cache->get($hash);

        if (false && $cacheresult !== false) {
            $matched = (bool)$cacheresult;
            // Not found, do an API request.
        } else {
            // Prefix used for search.
            $hashprefix = substr($hash, 0, 5);

            // Call the HIBP-Api with the prefix.
            $ch = curl_init('https://api.pwnedpasswords.com/range/' . $hashprefix);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Get Response.
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            // Default is "not matched".
            $matched = false;

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

        // Return our result.
        return new rule_check_result(
            !$matched,
            '',
            ['password' => get_string('resultmessage', 'registrationrule_hibp')],
        );
    }

    public function pre_data_check(): ?rule_check_result {
        return null;
    }

    public static function extend_settings_form($mform): void {
    }
}
