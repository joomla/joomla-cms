<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Pagination;

use Joomla\CMS\Pagination\PaginationWindow;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for PaginationWindow.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 * @since       __DEPLOY_VERSION__
 */
class PaginationWindowTest extends UnitTestCase
{
    private const ALL = ['narrow', 'small', 'medium', 'large', 'xlarge'];

    /**
     * Expected visible ranges per breakpoint and ellipsis breakpoints.
     *
     * Element order: current, total, ranges [breakpoint => [first, last]], ellipsisStart, ellipsisEnd
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataCalculate(): array
    {
        $all = self::ALL;

        return [
            'p1 of 18'    => [1, 18, ['narrow' => [2, 2], 'small' => [2, 4], 'medium' => [2, 6], 'large' => [2, 8], 'xlarge' => [2, 10]], [], $all],
            'p3 of 18'    => [3, 18, ['narrow' => [2, 3], 'small' => [2, 4], 'medium' => [2, 6], 'large' => [2, 8], 'xlarge' => [2, 10]], [], $all],
            'p4 of 18'    => [4, 18, ['narrow' => [4, 4], 'small' => [2, 5], 'medium' => [2, 6], 'large' => [2, 8], 'xlarge' => [2, 10]], ['narrow'], $all],
            'p5 of 18'    => [5, 18, ['narrow' => [5, 5], 'small' => [4, 6], 'medium' => [2, 7], 'large' => [2, 8], 'xlarge' => [2, 10]], ['narrow', 'small'], $all],
            'p9 of 18'    => [9, 18, ['narrow' => [9, 9], 'small' => [8, 10], 'medium' => [7, 11], 'large' => [6, 12], 'xlarge' => [5, 13]], $all, $all],
            'p15 of 18'   => [15, 18, ['narrow' => [15, 15], 'small' => [14, 17], 'medium' => [13, 17], 'large' => [11, 17], 'xlarge' => [9, 17]], $all, ['narrow']],
            'p16 of 18'   => [16, 18, ['narrow' => [16, 17], 'small' => [15, 17], 'medium' => [13, 17], 'large' => [11, 17], 'xlarge' => [9, 17]], $all, []],
            'p18 of 18'   => [18, 18, ['narrow' => [17, 17], 'small' => [15, 17], 'medium' => [13, 17], 'large' => [11, 17], 'xlarge' => [9, 17]], $all, []],
            'p2 of 3'     => [2, 3, ['narrow' => [2, 2], 'small' => [2, 2], 'medium' => [2, 2], 'large' => [2, 2], 'xlarge' => [2, 2]], [], []],
            'p101 of 230' => [101, 230, ['narrow' => [101, 101], 'small' => [100, 102], 'medium' => [99, 103], 'large' => [98, 104], 'xlarge' => [97, 105]], $all, $all],
        ];
    }

    /**
     * Tests the visible pages and ellipses per breakpoint.
     *
     * @param   int    $current        Current page
     * @param   int    $total          Total pages
     * @param   array  $ranges         Expected [first, last] visible middle page per breakpoint
     * @param   array  $ellipsisStart  Expected breakpoints with a start ellipsis
     * @param   array  $ellipsisEnd    Expected breakpoints with an end ellipsis
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataCalculate')]
    public function testCalculate(int $current, int $total, array $ranges, array $ellipsisStart, array $ellipsisEnd): void
    {
        $expectedPages = [];

        foreach ($ranges as $breakpoint => [$first, $last]) {
            for ($i = $first; $i <= $last; $i++) {
                $expectedPages[$i][] = $breakpoint;
            }
        }

        ksort($expectedPages);

        $result = PaginationWindow::calculate($current, $total);

        $this->assertSame($expectedPages, $result['pages']);
        $this->assertSame($ellipsisStart, $result['ellipsisStart']);
        $this->assertSame($ellipsisEnd, $result['ellipsisEnd']);
    }

    /**
     * Tests that one or two pages have no middle pages and no ellipses.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoMiddlePages(): void
    {
        foreach ([[1, 1], [1, 2], [2, 2]] as [$current, $total]) {
            $this->assertSame(
                ['pages' => [], 'ellipsisStart' => [], 'ellipsisEnd' => []],
                PaginationWindow::calculate($current, $total),
                "current $current of $total"
            );
        }
    }

    /**
     * Tests rules that must hold for every current page and total.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testInvariants(): void
    {
        foreach ([1, 2, 3, 4, 10, 11, 18, 230] as $total) {
            for ($current = 1; $current <= $total; $current++) {
                $label  = "current $current of $total";
                $result = PaginationWindow::calculate($current, $total);

                // Every rendered middle page is visible at the widest breakpoint (the view without CSS)
                foreach ($result['pages'] as $page => $breakpoints) {
                    $this->assertContains('xlarge', $breakpoints, "$label: page $page not visible at xlarge");
                }

                if ($total < 3) {
                    continue;
                }

                foreach (self::ALL as $breakpoint) {
                    $visible = array_keys(array_filter($result['pages'], static fn ($bps) => \in_array($breakpoint, $bps, true)));

                    if ($current >= 2 && $current <= $total - 1) {
                        $this->assertContains($current, $visible, "$label: current page hidden at $breakpoint");
                    }

                    $this->assertSame(range(min($visible), max($visible)), $visible, "$label: gap at $breakpoint");

                    $this->assertSame(
                        min($visible) > 2,
                        \in_array($breakpoint, $result['ellipsisStart'], true),
                        "$label: start ellipsis at $breakpoint"
                    );
                    $this->assertSame(
                        max($visible) < $total - 1,
                        \in_array($breakpoint, $result['ellipsisEnd'], true),
                        "$label: end ellipsis at $breakpoint"
                    );
                }
            }
        }
    }

    /**
     * Tests the class string for a list of breakpoints.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testClasses(): void
    {
        $this->assertSame('show-at-narrow show-at-small', PaginationWindow::classes(['narrow', 'small']));
        $this->assertSame('', PaginationWindow::classes([]));
    }
}
