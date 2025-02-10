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
// Cache to store AI rate limits.

/**
 * Cache definitions for storing log of hits per IP and session.
 *
 * @package   registrationrule_ratelimit
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'ipratelimit' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true, // Keys only contain "a-zA-Z0-9_".
        'simpledata' => true, // Cache stores an array of strings.
        'staticacceleration' => true,
    ],
    'sessionratelimit' => [
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true, // Keys only contain "a-zA-Z0-9_".
        'simpledata' => true, // Cache stores an array of strings.
        'staticacceleration' => true,
    ],
];
