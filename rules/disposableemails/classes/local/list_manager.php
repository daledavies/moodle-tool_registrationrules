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

// For 4.1 compatibility - this is data_source_interface in later versions.
use cache_data_source;
use coding_exception;
use context_system;
use core_cache\definition;
use dml_exception;
use moodle_exception;
use file_exception;
use stored_file;

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
class list_manager implements cache_data_source {

    /**
     * URL of the list of disposable email domains.
     */
    const LIST_URL = 'https://raw.githubusercontent.com/' .
        'disposable-email-domains/disposable-email-domains/' . // User and repository.
        'master/' . // Branch.
        'disposable_email_blocklist.conf'; // File to fetch.

    /**
     * Temporary filename for the list of disposable email domains.
     */
    const TEMP_FILENAME = 'disposable_email_blocklist.conf.tmp';

    /** @var array files table record */
    private $filerecord;

    /** @var list_manager the singleton instance of this class. */
    protected static $instance = null;

    /**
     * List manager constructor
     *
     * @throws dml_exception
     */
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

    /**
     * Returns array of blocked domains, not to be accepted as user email address domains.
     *
     * @return string[]
     * @throws file_exception
     * @throws moodle_exception
     */
    public function get_blocked_domains(): array {
        if (!$this->list_file_exists()) {
            $this->download_list();
        }
        if (!$this->list_file_exists()) {
            throw new moodle_exception('errorlistdownloadfailed', 'registrationrule_disposableemails');
        }

        $file = $this->get_file();
        $content = $file->get_content();

        return explode("\n", $content);
    }

    /**
     * Check is a domain is on the list and should be blocked.
     *
     * @param string $domain
     * @return boolean
     */
    public function is_domain_blocked(string $domain): bool {
        // Get an instance of the cache, so we can see if the provided domain exists in it.
        $cache = \cache::make('registrationrule_disposableemails', 'blockedemaildomains');
        return $cache->get($domain);
    }

    /**
     * Fetch the up-to-date list of disposable email domains from a public GitHub repository and store it here.
     *
     * @return void
     * @throws file_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function download_list(): void {
        $url = 'https://raw.githubusercontent.com/' .
            'disposable-email-domains/disposable-email-domains/' . // User and repository.
            'master/' . // Branch.
            'disposable_email_blocklist.conf'; // File to fetch.
        $url = self::LIST_URL;

        $fs = get_file_storage();

        if (!$this->list_file_exists()) {
            $fs->create_file_from_url(
                $this->filerecord,
                $url,
            );
            return;
        }

        // If the list already exists, download it to a temporary file first to minimise
        // the time the list is unavailable.
        $tempfile = $this->get_file(self::TEMP_FILENAME);
        if ($tempfile !== false) {
            $tempfile->delete();
        }

        $tempfilerecord = $this->filerecord;
        $tempfilerecord['filename'] = self::TEMP_FILENAME;

        $tempfile = $fs->create_file_from_url(
            $tempfilerecord,
            $url
        );

        $realfile = $this->get_file();
        $realfile->replace_file_with($tempfile);
        if ($this->list_file_exists()) {
            $cache = \cache::make('registrationrule_disposableemails', 'blockedemaildomains');
            $cache->purge();
            $tempfile->delete();
        } else {
            throw new moodle_exception('errorlistdownloadfailed', 'registrationrule_disposableemails');
        }
    }

    /**
     * Return if a list of disposable mail address-domains is currently stored in Moodle.
     *
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

    /**
     * Get the given file from this plugin's file area.
     *
     * @param string $filename
     * @return bool|stored_file
     */
    private function get_file(string $filename = 'disposable_email_blocklist.conf'): bool|stored_file {
        $fs = get_file_storage();
        return $fs->get_file(
            $this->filerecord['contextid'],
            $this->filerecord['component'],
            $this->filerecord['filearea'],
            $this->filerecord['itemid'],
            $this->filerecord['filepath'],
            $filename
        );
    }

    /**
     * Returns an instance of the data source class that the cache can use for loading data using the other methods
     * specified by this interface.
     *
     * @param definition $definition
     * @return object
     */
    public static function get_instance_for_cache(definition $definition): list_manager {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Loads the data for the key provided ready formatted for caching.
     *
     * @param string|int $key The key to load.
     * @return mixed What ever data should be returned, or false if it can't be loaded.
     */
    public function load_for_cache($key): mixed {
        $blockeddomains = $this->get_blocked_domains();
        return in_array($key, $blockeddomains);
    }

    /**
     * Loads several keys for the cache.
     *
     * @param array $keys An array of keys each of which will be string|int.
     * @return array An array of matching data items.
     */
    public function load_many_for_cache(array $keys): array {
        $blockeddomains = $this->get_blocked_domains();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = in_array($key, $blockeddomains);
        }
        return $result;
    }
}
