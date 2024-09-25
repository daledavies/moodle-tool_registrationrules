<?php
// This file is part of Moodle - https://moodle.org
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Class managing lists of restricted domains (for disposable mail address restriction).
 *
 * @package   registrationrule_disposableemails
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace registrationrule_disposableemails\cache;

// For 4.1 compatibility - this is data_source_interface in later versions.
use cache_data_source;
use core_cache\definition;
use registrationrule_disposableemails\local\list_manager;

/**
 * Data source for the cache of blocked email domains.
 */
class blockedemail_datasource implements cache_data_source {

    /**
     * The list manager used by the data source.
     *
     * @var list_manager
     */
    private list_manager $listmanager;

    /**
     * Constructor.
     *
     * @param list_manager $listmanager The list manager to use.
     */
    public function __construct(list_manager $listmanager) {
        $this->listmanager = $listmanager;
    }

    /**
     * Returns an instance of the data source class that the cache can use for loading data using the other methods
     * specified by this interface.
     *
     * @param definition $definition
     * @return object
     */
    public static function get_instance_for_cache(definition $definition) {
        return new self(new list_manager());
    }

    /**
     * Loads the data for the key provided ready formatted for caching.
     *
     * @param string|int $key The key to load.
     * @return mixed What ever data should be returned, or false if it can't be loaded.
     */
    public function load_for_cache($key) {
        $blockeddomains = $this->listmanager->get_blocked_domains();
        return in_array($key, $blockeddomains);
    }

    /**
     * Loads several keys for the cache.
     *
     * @param array $keys An array of keys each of which will be string|int.
     * @return array An array of matching data items.
     */
    public function load_many_for_cache(array $keys) {
        $blockeddomains = $this->listmanager->get_blocked_domains();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = in_array($key, $blockeddomains);
        }
        return $result;
    }
}
