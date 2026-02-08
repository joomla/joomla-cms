<?php

/**
 * Layout file for complete pagination links
 *
 * This layout renders the full pagination structure with responsive visibility
 * using container queries. Pages are shown/hidden based on breakpoint configurations.
 * Uses modern PHP 8.0+ features including match expressions, typed properties, and array destructuring.
 *
 * Breakpoints:
 * - narrow  (< 480px):  1 page around active
 * - small   (480-640px): 3 pages around active
 * - medium  (640-768px): 5 pages around active
 * - large   (768-896px): 7 pages around active
 * - xlarge  (896px+):    9 pages around active
 *
 * @package         Joomla.Site
 * @subpackage      Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since           6.1
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

$list  = $displayData['list'];
$pages = $list['pages'];
$total = $list['total'];

$options = new Registry($displayData['options']);

$showLimitBox     = $options->get('showLimitBox', false);
$showPagesLinks   = $options->get('showPagesLinks', true);
$showLimitStart   = $options->get('showLimitStart', true);
$showItemPosition = $options->get('showItemPosition', true);

// Calculate current page and total pages
$currentPage = 1;
$totalPages  = $list['pagesTotal'] ?? 0;

if (!empty($pages['pages'])) {
    foreach ($pages['pages'] as $k => $page) {
        if ($page['data']?->active) {
            $currentPage = $k;
            break;
        }
    }
}

// Determine which pages to show (let Joomla decide the range)
$visiblePages     = array_keys($pages['pages'] ?? []);
$firstVisiblePage = !empty($visiblePages) ? min($visiblePages) : 1;
$lastVisiblePage  = !empty($visiblePages) ? max($visiblePages) : $totalPages;

// Show ellipsis when there's a gap
$showStartEllipsis = ($firstVisiblePage > 2);
$showEndEllipsis   = ($lastVisiblePage < $totalPages - 1);

// Define breakpoint configurations (cached for performance)
const BREAKPOINT_CONFIGS = [
    'narrow' => ['range' => 0, 'maxPages' => 1, 'edgeThreshold' => 3],   // < 480px
    'small'  => ['range' => 1, 'maxPages' => 3, 'edgeThreshold' => 4],   // 480-640px
    'medium' => ['range' => 2, 'maxPages' => 5, 'edgeThreshold' => 5],   // 640-768px
    'large'  => ['range' => 3, 'maxPages' => 7, 'edgeThreshold' => 6],   // 768-896px
    'xlarge' => ['range' => 4, 'maxPages' => 9, 'edgeThreshold' => 8],   // 896px+
];

// Define pagination breakpoint names
const PAGINATION_BREAKPOINTS = ['narrow', 'small', 'medium', 'large', 'xlarge'];

/**
 * Calculate which pages should be visible at each breakpoint
 * Based on the pattern from user specifications
 *
 * @param   int     $pageNum      The page number to check
 * @param   int     $currentPage  The current active page
 * @param   int     $totalPages   Total number of pages
 * @param   string  $breakpoint   Breakpoint name (narrow, small, medium, large, xlarge)
 *
 * @return  bool  True if the page should be visible at this breakpoint
 *
 * @since   6.1
 */
function getPagesForBreakpoint(int $pageNum, int $currentPage, int $totalPages, string $breakpoint): bool
{
    $config = BREAKPOINT_CONFIGS[$breakpoint] ?? BREAKPOINT_CONFIGS['narrow'];
    ['range' => $range, 'maxPages' => $maxPages, 'edgeThreshold' => $edgeThreshold] = $config;

    // Determine if we're near the start or end
    $nearStart = ($currentPage <= $edgeThreshold);
    $nearEnd   = ($currentPage >= $totalPages - $edgeThreshold + 1);

    if ($nearStart) {
        // Show consecutive pages from page 2 (page 1 is separate)
        $start = 2;
        $end   = min($totalPages - 1, max($currentPage, 2 + $maxPages - 1));
    } elseif ($nearEnd) {
        // Show consecutive pages to page totalPages-1 (last page is separate)
        $end   = $totalPages - 1;
        $start = max(2, min($currentPage, $end - $maxPages + 1));
    } else {
        // Show centered window around active page
        $start = max(2, $currentPage - $range);
        $end   = min($totalPages - 1, $currentPage + $range);

        // Ensure we show at least maxPages
        $pagesInWindow = $end - $start + 1;
        if ($pagesInWindow < $maxPages) {
            $deficit = $maxPages - $pagesInWindow;
            $end     = min($totalPages - 1, $end + ceil($deficit / 2));
            $start   = max(2, $start - floor($deficit / 2));
        }
    }

    // Check if this page should be shown
    return ($pageNum >= $start && $pageNum <= $end);
}

/**
 * Helper function to render first or last page
 *
 * @param   array   $pages        The pages array from Joomla pagination
 * @param   string  $type         Type of page to render ('first' or 'last')
 * @param   int     $currentPage  The current active page number
 * @param   int     $totalPages   Total number of pages
 *
 * @return  string  Rendered HTML for the page link
 *
 * @since   6.1
 */
function renderPageNumber(array $pages, string $type, int $currentPage, int $totalPages): string
{
    [$pageNum, $dataKey] = match ($type) {
        'first' => [1, 'start'],
        'last' => [$totalPages, 'end'],
    };

    $isActive       = ($currentPage === $pageNum);
    $pageData       = $pages[$dataKey]['data'];
    $pageData->text = (string)$pageNum;

    if ($isActive) {
        $pageData->active = true;
    }

    return LayoutHelper::render('joomla.pagination.link', [
        'data'     => $pageData,
        'active'   => !$isActive,
        'modifier' => $type
    ]);
}

$first = ($currentPage - 1) * $list['limit'] + 1;
$last  = $first + $list['limit'] - 1;
$last  = $last > $total ? $total : $last;

?>
<?php
if (!empty($pages) || $showItemPosition) : ?>
    <nav class="pagination__wrapper" aria-label="<?php
    echo Text::_('JLIB_HTML_PAGINATION'); ?>">
        <?php
        if ($showItemPosition) : ?>
            <div class="text-end me-3">
                <?php
                echo Text::sprintf('JLIB_HTML_PAGINATION_NUMBERS', $first, $last, $total); ?>
            </div>
        <?php
        endif; ?>

        <div class="pagination-toolbar text-center mt-0">

            <?php
            if ($showLimitBox) : ?>
                <div class="limit float-end">
                    <?php
                    echo Text::_('JGLOBAL_DISPLAY_NUM') . $list['limitfield']; ?>
                </div>
            <?php
            endif; ?>

            <?php
            if ($showPagesLinks && !empty($pages)) : ?>
                <ul class="pagination ms-auto me-0" data-current-page="<?php
                echo $currentPage; ?>"
                    data-total-pages="<?php
                    echo $totalPages; ?>">

                    <?php
                    echo LayoutHelper::render('joomla.pagination.link', $pages['previous']); ?>

                    <?php
                    if (!empty($pages['start']['data'])) : ?>
                        <?php
                        echo renderPageNumber($pages, 'first', $currentPage, $totalPages); ?>
                    <?php
                    endif; ?>

                    <?php
                    if ($showStartEllipsis) : ?>
                        <?php
                        echo LayoutHelper::render('joomla.pagination.link', [
                            'data'     => (object)['text' => '...'],
                            'active'   => false,
                            'modifier' => 'ellipsis ellipsis--start'
                        ]);
                        ?>
                    <?php
                    endif; ?>

                    <?php
                    foreach ($pages['pages'] as $k => $page) : ?>
                        <?php
                        $isFirstPage = ($k === 1);
                        $isLastPage  = ($k === $totalPages);

                        // Skip first and last pages (rendered separately)
                        if ($isFirstPage || $isLastPage) {
                            continue;
                        }

                        // Calculate visibility classes for each breakpoint
                        $visibilityClasses = [];

                        foreach (PAGINATION_BREAKPOINTS as $breakpoint) {
                            if (getPagesForBreakpoint($k, $currentPage, $totalPages, $breakpoint)) {
                                $visibilityClasses[] = 'show-at-' . $breakpoint;
                            }
                        }

                        // Pass visibility classes to link.php
                        $page['extraClasses'] = $visibilityClasses;
                        echo LayoutHelper::render('joomla.pagination.link', $page);
                        ?>
                    <?php
                    endforeach; ?>

                    <?php
                    if ($showEndEllipsis) : ?>
                        <?php
                        echo LayoutHelper::render('joomla.pagination.link', [
                            'data'     => (object)['text' => '...'],
                            'active'   => false,
                            'modifier' => 'ellipsis ellipsis--end'
                        ]);
                        ?>
                    <?php
                    endif; ?>

                    <?php
                    if (!empty($pages['end']['data'])) : ?>
                        <?php
                        echo renderPageNumber($pages, 'last', $currentPage, $totalPages); ?>
                    <?php
                    endif; ?>

                    <?php
                    echo LayoutHelper::render('joomla.pagination.link', $pages['next']); ?>

                </ul>
            <?php
            endif; ?>

            <?php
            if ($showLimitStart) : ?>
                <input type="hidden" name="<?php
                echo $list['prefix']; ?>limitstart"
                       value="<?php
                       echo $list['limitstart']; ?>">
            <?php
            endif; ?>

        </div>
    </nav>
<?php
endif; ?>
