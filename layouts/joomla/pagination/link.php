<?php

/**
 * @package         Joomla.Site
 * @subpackage      Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since           __DEPLOY_VERSION__
 */

/**
 * Layout file for individual pagination link
 *
 * This layout renders a single pagination item (page number, previous, next, or ellipsis).
 * Uses modern PHP 8.0+ features including match expressions and nullsafe operators.
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

$item    = $displayData['data'];
$display = $item->text;
$app     = Factory::getApplication();

// Check if this is an ellipsis early (special non-link element)
$isEllipsis = str_contains($displayData['modifier'] ?? '', 'ellipsis');

if ($isEllipsis) {
    // Build minimal li attributes for ellipsis and return early
    $liAttributes = [
        'class'       => 'page-item ' . $displayData['modifier'],
        'aria-hidden' => 'true'
    ];
    ?>
    <li <?php
    echo ArrayHelper::toString($liAttributes); ?>>
        <span class="page-link"><?php
            echo $display; ?></span>
    </li>
    <?php
    return;
}

// Cache RTL check for performance
$isRtl = $app->getLanguage()->isRtl();

// Build li attributes array
$liAttributes = ['class' => 'page-item'];

// Add modifier class if provided (e.g., 'first', 'last')
if (isset($displayData['modifier'])) {
    $liAttributes['class'] .= ' page-item--' . $displayData['modifier'];
}

// Add extra classes if provided (e.g., visibility classes)
if (!empty($displayData['extraClasses'] ?? [])) {
    $liAttributes['class'] .= ' ' . implode(' ', $displayData['extraClasses']);
}

// Add data-page-number attribute for numbered pages
if (is_numeric($item->text)) {
    $liAttributes['data-page-number'] = (int)$item->text;
}

// Determine icon and aria label using match expression
[$icon, $aria] = match ($item->text) {
    Text::_('JPREV') => [
        $isRtl ? 'icon-angle-right' : 'icon-angle-left',
        Text::_('JLIB_HTML_GOTO_POSITION_PREVIOUS')
    ],
    Text::_('JNEXT') => [
        $isRtl ? 'icon-angle-left' : 'icon-angle-right',
        Text::_('JLIB_HTML_GOTO_POSITION_NEXT')
    ],
    default => [
        null,
        Text::sprintf('JLIB_HTML_GOTO_PAGE', strtolower($item->text))
    ],
};

// Use icon if provided
if ($icon !== null) {
    $display = '<span class="' . $icon . '" aria-hidden="true"></span>';
}

// Build anchor attributes array
$anchorAttributes = [
    'class'      => 'page-link',
    'aria-label' => $aria
];

// Initialize URL
$url = '#';

if ($displayData['active']) {
    // Active clickable link
    if ($app->isClient('administrator')) {
        $limit                       = $item->base > 0 ? 'limitstart.value=' . $item->base : 'limitstart.value=0';
        $anchorAttributes['onclick'] = 'document.adminForm.' . $item->prefix . $limit . '; Joomla.submitform();return false;';
    } elseif ($app->isClient('site')) {
        $url = $item->link;
    }
} elseif (isset($item->active) && $item->active) {
    // Active current page
    $liAttributes['class']            .= ' active';
    $anchorAttributes['aria-current'] = 'true';
    $anchorAttributes['aria-label']   = Text::sprintf('JLIB_HTML_PAGE_CURRENT', strtolower($item->text));
} else {
    // Disabled link
    $liAttributes['class'] .= ' disabled';
}

?>
<li <?php
echo ArrayHelper::toString($liAttributes); ?>>
    <?php  if ($displayData['active'] || (isset($item->active) && $item->active)) : ?>
        <?php echo HTMLHelper::_('link', $url, $display, $anchorAttributes); ?>
    <?php else : ?>
        <span class="page-link" aria-hidden="true"><?php echo $display; ?></span>
    <?php endif; ?>
</li>
