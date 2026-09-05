<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getApplication()->getLanguage()->load('mod_healthcheck', JPATH_ADMINISTRATOR);

// Get gauge parameters with defaults
$id         = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$label      = $displayData['label'] ?? '';
$sublabel   = $displayData['sublabel'] ?? '';
$note       = $displayData['note'] ?? '';
$link       = $displayData['link'] ?? '';
$linktitle  = $displayData['linktitle'] ?? '';
$linktarget = '';

// Auto-detect external links and set target to _blank
if (!empty($link) && empty($linktarget)) {
    // Check if link is external (starts with http/https or contains a different domain)
    if (preg_match('/^https?:\/\//', $link)) {
        // Extract current domain for comparison
        $currentDomain = $_SERVER['HTTP_HOST'] ?? '';
        $linkDomain    = parse_url($link, PHP_URL_HOST);

        // If different domain or no current domain info, treat as external
        if (empty($currentDomain) || $linkDomain !== $currentDomain) {
            $linktarget = '_blank';
        }
    }
}

$score                   = (float) ($displayData['score'] ?? 0);
$unit                    = $displayData['unit'] ?? '%';
$score_min               = (float) ($displayData['score_min'] ?? 0);
$score_max               = (float) ($displayData['score_max'] ?? 100);
$score_threshold_error   = (float) ($displayData['score_threshold_error'] ?? 0);
$score_threshold_warning = (float) ($displayData['score_threshold_warning'] ?? 50);
$score_threshold_success = (float) ($displayData['score_threshold_success'] ?? 90);
$filterStatus            = 'healthy';

if (isset($displayData['status'])) {
    switch ($displayData['status']) {
        case 'warning':
            $filterStatus = 'warning';
            break;
        case 'error':
            $filterStatus = 'critical';
            break;
        case 'success':
        default:
            $filterStatus = 'healthy';
    }
} elseif ($score < $score_threshold_warning) {
    $filterStatus = 'critical';
} elseif ($score < $score_threshold_success) {
    $filterStatus = 'warning';
}

// Prepare link attributes
$hasLink = !empty($link);
if ($hasLink) {
    $linkAttributes = 'href="' . htmlspecialchars($link) . '"';
    if (!empty($linktarget)) {
        $linkAttributes .= ' target="' . htmlspecialchars($linktarget) . '"';
        if ($linktarget === '_blank') {
            $linkAttributes .= ' rel="noopener noreferrer"';
        }
    }
}

// Calculate percentage for the pie chart
$percentage = ($score_max > $score_min) ? (($score - $score_min) / ($score_max - $score_min)) * 100 : 0;
$percentage = max(0, min(100, $percentage)); // Clamp between 0-100

// Calculate SVG path for pie chart
$radius           = 45;
$circumference    = 2 * M_PI * $radius;
$strokeDasharray  = $circumference;
$strokeDashoffset = $circumference * (1 - $percentage / 100);

// SVG viewBox and center
$size   = 120;
$center = $size / 2;

// Linked gauges are focusable through the anchor, non-linked gauges need their own tab stop.
$itemRole = $hasLink ? 'group' : 'img';
?>
<li class="healthcheck-gauge"<?php echo $id; ?>
    role="<?php echo $itemRole; ?>"
    <?php if (!$hasLink) : ?> tabindex="0"<?php endif; ?>
     aria-label="<?php echo htmlspecialchars(Text::sprintf($hasLink ? 'MOD_HEALTHCHECK_GAUGE_ITEM_ARIA_LABEL_LINK' : 'MOD_HEALTHCHECK_GAUGE_ITEM_ARIA_LABEL', $label, $score, $unit, $score_max)); ?>"
     data-healthcheck-status="<?php echo $filterStatus; ?>"
     data-score="<?php echo $score; ?>"
     data-max="<?php echo $score_max; ?>"
     data-percentage="<?php echo number_format($percentage, 1); ?>">

    <?php if ($hasLink) : ?>
        <a <?php echo $linkAttributes; ?> class="gauge-link d-block text-decoration-none"
           aria-label="<?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_LINK_ARIA_LABEL', $linktitle ?: $label, $score, $unit)); ?>">
    <?php endif; ?>

    <div class="gauge-container text-center">
        <?php if (!empty($label)) : ?>
            <h4 class="gauge-label mb-1" id="gauge-title-<?php echo md5($label); ?>"><?php echo htmlspecialchars($label); ?></h4>
        <?php endif; ?>

        <?php if (!empty($sublabel)) : ?>
            <p class="gauge-sublabel text-muted small mb-2" id="gauge-subtitle-<?php echo md5($label); ?>"><?php echo htmlspecialchars($sublabel); ?></p>
        <?php endif; ?>

        <div class="gauge-chart-container position-relative d-inline-block"
             aria-labelledby="<?php echo !empty($label) ? 'gauge-title-' . md5($label) : ''; ?><?php echo !empty($sublabel) ? ' gauge-subtitle-' . md5($label) : ''; ?>"
             aria-describedby="gauge-description-<?php echo md5($label); ?><?php echo !empty($note) ? ' gauge-note-' . md5($label) : ''; ?>">

            <svg width="<?php echo $size; ?>"
                 height="<?php echo $size; ?>"
                 viewBox="0 0 <?php echo $size; ?> <?php echo $size; ?>"
                 class="gauge-svg"
                 role="img"
                 aria-hidden="true"
                 focusable="false">

                <title><?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_SVG_TITLE', $label, $score, $unit)); ?></title>
                <desc><?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_SVG_DESC', $score, $unit, $score_max, number_format($percentage, 1))); ?></desc>

                <!-- Background circle -->
                <circle
                    cx="<?php echo $center; ?>"
                    cy="<?php echo $center; ?>"
                    r="<?php echo $radius; ?>"
                    class="gauge-track-circle"
                    fill="none"
                    stroke-width="8"
                />

                <!-- Progress circle -->
                <circle
                    cx="<?php echo $center; ?>"
                    cy="<?php echo $center; ?>"
                    r="<?php echo $radius; ?>"
                    class="gauge-progress-circle"
                    fill="none"
                    stroke-width="8"
                    stroke-linecap="round"
                    stroke-dasharray="<?php echo $strokeDasharray; ?>"
                    stroke-dashoffset="<?php echo $strokeDashoffset; ?>"
                    transform="rotate(-90 <?php echo $center; ?> <?php echo $center; ?>)"
                />

                <!-- Score text in center -->
                <text
                    x="<?php echo $center; ?>"
                    y="<?php echo $center - 5; ?>"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    class="gauge-score-text"
                    aria-hidden="true"
                >
                    <?php echo $score; ?>
                </text>

                <!-- Unit text -->
                <text
                    x="<?php echo $center; ?>"
                    y="<?php echo $center + 15; ?>"
                    text-anchor="middle"
                    dominant-baseline="middle"
                    class="gauge-unit-text"
                    aria-hidden="true"
                >
                    <?php echo htmlspecialchars($unit); ?>
                </text>
            </svg>

            <!-- Screen reader accessible description -->
            <div id="gauge-description-<?php echo md5($label); ?>" class="sr-only">
                <?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_SR_SCORE', $score, $unit, $score_max)); ?>
                <?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_SR_RANGE', number_format($percentage, 1), $score_min, $score_max)); ?>
                <?php if ($score >= $score_threshold_success) : ?>
                    <?php echo htmlspecialchars(Text::_('MOD_HEALTHCHECK_GAUGE_STATUS_EXCELLENT')); ?>
                <?php elseif ($score >= $score_threshold_warning) : ?>
                    <?php echo htmlspecialchars(Text::_('MOD_HEALTHCHECK_GAUGE_STATUS_GOOD')); ?>
                <?php else : ?>
                    <?php echo htmlspecialchars(Text::_('MOD_HEALTHCHECK_GAUGE_STATUS_ATTENTION')); ?>
                <?php endif; ?>
            </div>

            <!-- Percentage indicator -->
            <div class="gauge-percentage small text-muted mt-1" aria-hidden="true">
                <?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_PERCENT_OF_RANGE', number_format($percentage, 2))); ?>
            </div>
        </div>

        <?php if (!empty($note)) : ?>
            <p class="gauge-note small mt-2" id="gauge-note-<?php echo md5($label); ?>"><?php echo htmlspecialchars($note); ?></p>
        <?php endif; ?>

        <!-- Raw data display (optional, for debugging) -->
        <?php if (defined('JDEBUG') && JDEBUG) : ?>
            <div class="gauge-debug small mt-2">
                <?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_DEBUG_RANGE', $score_min, $score_max)); ?> |
                <?php echo htmlspecialchars(Text::sprintf('MOD_HEALTHCHECK_GAUGE_DEBUG_THRESHOLDS', $score_threshold_error, $score_threshold_warning, $score_threshold_success)); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($hasLink) : ?>
        </a>
    <?php endif; ?>
</li>
