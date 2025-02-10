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

namespace registrationrule_ratelimit\local;

use core\exception\coding_exception;
use core_cache\cache;

/**
 * Basic implementation of a sliding window rate limiting algorithm.
 *
 * @package   registrationrule_ratelimit
 * @copyright 2025 Catalyst IT Europe {@link https://www.catalyst-eu.net}
 *            2025 eDaktik GmbH {@link https://www.edaktik.at/}
 *            2025 lern.link GmbH {@link https://lern.link/}
 *            2025 University of Strathclyde {@link https://www.strath.ac.uk}
 * @author    Dale Davies <dale.davies@catalyst-eu.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rate_limiter {
    /** @var string Used for instructing limit() to rate limit IP address. */
    public const IP_CHECK = 'ip';

    /** @var string Used for instructing limit() to rate limit session. */
    public const SESSION_CHECK = 'session';

    /** @var string Used for instructing limit() to increate the count. */
    public const INCREMENT_RATE_COUNT = 'increment';

    /** @var string Used for instructing limit() to not increase the count. */
    public const CHECK_RATE_COUNT = 'check';

    /** @var array cache definition and key name for IP limiting. */
    private array $ipcache;

    /** @var array cache definition and key name for session limiting. */
    private array $sessioncache;

    /**
     * Construct a new rate_limiter object.
     */
    public function __construct() {
        $this->ipcache = [
            'cache' => cache::make('registrationrule_ratelimit', 'ipratelimit'),
            'key' => 'ip_' . hash('sha256', getremoteaddr()),
        ];
        $this->sessioncache = [
            'cache' => cache::make('registrationrule_ratelimit', 'sessionratelimit'),
            'key' => 'sigupsession',
        ];
    }

    /**
     * Limit (or just check) the number of hits within the allowed time window.
     *
     * @param int $limit Max number of allowed hits.
     * @param int $timewindow Time window to check within.
     * @param string $type Are we limiting per IP or per session.
     * @param string $recordhit Should the hit count be incremented or just checked.
     * @throws coding_exception
     *
     * @return bool False if rate has been reached within time window, true otherwise.
     */
    public function limit(int $limit, int $timewindow, string $type, string $recordhit): bool {
        // Validate the $type and $recordhit params.
        $this->validate_limit_params($type, $recordhit);

        // Determine the correct cache to use.
        $cache = $this->get_cache_type($type);

        // Get the array of hit timestamps from cache, or initialise a new empty array if nothing
        // has been cached.
        $hits = $cache['cache']->get($cache['key']) ?: [];

        // Snapshot of the current timestamp.
        $currenttime = (new \DateTimeImmutable())->getTimestamp();

        // Remove any hits from the array that occured longer ago than the beginning of the time window.
        $hits = array_filter($hits, function($timestamp) use($timewindow, $currenttime) {
            return ($currenttime - $timestamp ) < $timewindow;
        });

        // If there have been more hits than allowed the return false, come back later please.
        if (count($hits) > $limit) {
            return false;
        }

        // If asked to record a hit then add the current timestamp to hits array and commit to cache.
        if ($recordhit === self::INCREMENT_RATE_COUNT) {
            $hits[] = $currenttime;
            $cache['cache']->set($cache['key'], $hits);
        }

        // If we get here then the limit has not been reached.
        return true;
    }

    /**
     * Validate the params for limit() to ensure they contain values found in required
     * constants.
     *
     * @param string $type
     * @param string $recordhit
     * @return void
     */
    private function validate_limit_params(string $type, string $recordhit): void {
        $typevalues = [self::IP_CHECK, self::SESSION_CHECK];
        $recordhitvalues = [self::CHECK_RATE_COUNT, self::INCREMENT_RATE_COUNT];
        if (!in_array($type, $typevalues)) {
            throw new coding_exception('Unexpected $type value');
        }
        if (!in_array($recordhit, $recordhitvalues)) {
            throw new coding_exception('Unexpected $recordhit value');
        }
    }

    /**
     * Determine the correct cache to use, either IP or session.
     *
     * @param string $type
     * @return array
     */
    private function get_cache_type(string $type): array {
        if ($type === self::IP_CHECK) {
            return $this->ipcache;
        }

        return $this->sessioncache;
    }
}
