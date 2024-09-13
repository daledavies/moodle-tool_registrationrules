<?php
// This file is part of Moodle - https://moodle.org/
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

namespace registrationrule_disposableemails\local;

use context_system;
use core\exception\moodle_exception;
use curl;
use file_exception;

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
class list_manager {
    private $filerecord;

    public function __construct() {
        $this->filerecord = [
            'contextid' => context_system::instance()->id,
            'component' => 'registrationrule_disposableemails',
            'filearea' => 'disposable_email_blocklist',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'disposable_email_blocklist.conf',
        ];
    }

    public function get_blocked_domains() {
        if (!$this->list_file_exists()) {
            $this->download_list();
        }
        if (!$this->list_file_exists()) {
            throw new moodle_exception('errorlistdownloadfailed', 'registrationrule_disposableemails');
        }
        $fs = get_file_storage();
        $file = $fs->get_file(
            $this->filerecord['contextid'],
            $this->filerecord['component'],
            $this->filerecord['filearea'],
            $this->filerecord['itemid'],
            $this->filerecord['filepath'],
            $this->filerecord['filename']
        );
        $content = $file->get_content();
        return explode("\n", $content);
    }

    /**
     * @return void
     * @throws file_exception
     */
    public function download_list() {
        $url = 'https://raw.githubusercontent.com/' .
            'disposable-email-domains/disposable-email-domains/' . // User and repository.
            'master/' . // Branch.
            'disposable_email_blocklist.conf'; // File to fetch.

        $fs = get_file_storage();
        if ($this->list_file_exists()) {
            $fs->delete_area_files(
                $this->filerecord['contextid'],
                $this->filerecord['component'],
                $this->filerecord['filearea'],
                $this->filerecord['itemid'],
            );
        }

        $fs->create_file_from_url(
            $this->filerecord,
            $url,
        );
    }

    /**
     * @return bool
     */
    private function list_file_exists(): bool {
        $fs = get_file_storage();
        return $fs->file_exists(
            $this->filerecord['contextid'],
            $this->filerecord['component'],
            $this->filerecord['filearea'],
            $this->filerecord['itemid'],
            $this->filerecord['filepath'],
            $this->filerecord['filename'],
        );
    }
}
