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

namespace registrationrule_disposableemails\task;

use core\task\scheduled_task;
use registrationrule_disposableemails\local\list_manager;

/**
 * Scheduled task to update the list of blocked email domains.
 *
 * @package   registrationrule_disposableemails
 * @copyright 2024 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2024 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2024 lern.link GmbH {@link https://lern.link/}
 *            2024 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Michael Aherne <michael.aherne@strath.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_blocked_domains_list extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskupdateblockeddomainslist', 'registrationrule_disposableemails');
    }

    /**
     * Download a fresh list of domains and purge othe existing cache.
     *
     * @return void
     */
    public function execute(): void {
        $listmanager = new list_manager();
        $listmanager->download_list();
        $cache = \cache::make('registrationrule_disposableemails', 'blockedemaildomains');
        $cache->purge();
    }
}
