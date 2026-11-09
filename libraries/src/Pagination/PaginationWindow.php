<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pagination;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Calculates which pages a responsive pagination shows at each container breakpoint.
 *
 * The first and the last page are always shown and are not part of the result. Around the
 * current page a window of pages is shown whose size depends on the breakpoint. Templates
 * hide and show the pages with container queries on the `show-at-<breakpoint>` classes.
 *
 * @since  __DEPLOY_VERSION__
 */
final class PaginationWindow
{
    /**
     * Number of middle pages shown per breakpoint, from narrow to wide.
     *
     * @var    array<string, int>
     * @since  __DEPLOY_VERSION__
     */
    public const BREAKPOINTS = ['narrow' => 1, 'small' => 3, 'medium' => 5, 'large' => 7, 'xlarge' => 9];

    /**
     * Calculate the visible middle pages (2 .. total - 1) and the needed ellipses per breakpoint.
     *
     * A gap of exactly one page between the window and the first or last page shows that page
     * instead of an ellipsis.
     *
     * @param   int  $current  The current page number
     * @param   int  $total    The total number of pages
     *
     * @return  array{pages: array<int, list<string>>, ellipsisStart: list<string>, ellipsisEnd: list<string>}
     *          `pages` maps each page number to the breakpoints where it is visible.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function calculate(int $current, int $total): array
    {
        $result = ['pages' => [], 'ellipsisStart' => [], 'ellipsisEnd' => []];
        $low    = 2;
        $high   = $total - 1;

        if ($high < $low) {
            return $result;
        }

        foreach (self::BREAKPOINTS as $breakpoint => $size) {
            $half  = intdiv($size - 1, 2);
            $start = $current - $half;
            $end   = $current + $half;

            // Shift the window inside the middle pages, keeping its size
            if ($start < $low) {
                $end += $low - $start;
                $start = $low;
            }

            if ($end > $high) {
                $start -= $end - $high;
                $end    = $high;
            }

            $start = max($low, $start);
            $end   = min($high, $end);

            // An ellipsis never stands for a single page
            if ($start === $low + 1) {
                $start = $low;
            }

            if ($end === $high - 1) {
                $end = $high;
            }

            for ($i = $start; $i <= $end; $i++) {
                $result['pages'][$i][] = $breakpoint;
            }

            if ($start > $low) {
                $result['ellipsisStart'][] = $breakpoint;
            }

            if ($end < $high) {
                $result['ellipsisEnd'][] = $breakpoint;
            }
        }

        ksort($result['pages']);

        return $result;
    }

    /**
     * Get the CSS classes for a list of breakpoints.
     *
     * @param   string[]  $breakpoints  Breakpoint names
     *
     * @return  string  Space separated `show-at-<breakpoint>` classes
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function classes(array $breakpoints): string
    {
        return implode(' ', array_map(static fn (string $breakpoint): string => 'show-at-' . $breakpoint, $breakpoints));
    }
}
