<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$id      = empty($displayData['id']) ? '' : (' id="' . $displayData['id'] . '"');
$target  = empty($displayData['target']) ? '' : (' target="' . $displayData['target'] . '"');
$onclick = empty($displayData['onclick']) ? '' : (' data-onclick="' . $this->escape($displayData['onclick']) . '"');

if (isset($displayData['ajaxurl'])) {
    $dataUrl = 'data-url="' . $displayData['ajaxurl'] . '"';
} else {
    $dataUrl = '';
}

// The title for the link (a11y)
$title = empty($displayData['title']) ? '' : (' title="' . $this->escape($displayData['title']) . '"');

// The information
$text = empty($displayData['text']) ? '' : ('<span class="j-links-link">' . $displayData['text'] . '</span>');

// Depending on status, determine additional class
$class        = '';
$filterStatus = 'healthy'; // Default to "healthy"
if (isset($displayData['status'])) {
    switch ($displayData['status']) {
        case 'success':
            $class = 'success';
            $filterStatus = 'healthy';
            break;
        case 'warning':
            $class = 'warning';
            $filterStatus = 'warning';
            break;
        case 'error':
            $class = 'danger';
            $filterStatus = 'critical';
            break;
        default:
            $class = 'info';
            $filterStatus = 'healthy';
    }
}
$class .= empty($displayData['class']) ? '' : (' ' . $this->escape($displayData['class']));
?>
<?php // If it is a button with two links: make it a list
if (isset($displayData['linkadd'])) : ?>
    <li class="quickicon-group" data-healthcheck-status="<?php echo $filterStatus; ?>">
    <ul class="list-unstyled d-flex w-100">
        <li class="quickicon">
<?php else : ?>
    <li class="quickicon quickicon-single" data-healthcheck-status="<?php echo $filterStatus; ?>">
<?php endif; ?>
        <a<?php echo $id; ?> class="<?php echo $class; ?>" href="<?php echo $displayData['link']; ?>"<?php echo $target . $onclick . $title; ?>>
            <div class="quickicon-info">
                <div class="quickicon-icon">
                <?php if (isset($displayData['image'])) : ?>
                    <div><img src="<?php echo $displayData['image']; ?>" width="50" height="50" alt="<?php echo empty($displayData['title']) ? '' : $this->escape($displayData['title']); ?>" /></div>
                <?php elseif (isset($displayData['icon'])) : ?>
                    <div class="<?php echo $displayData['icon']; ?>" aria-hidden="true"></div>
                <?php endif; ?>
                </div>
                <?php if (isset($displayData['ajaxurl'])) : ?>
                    <div class="quickicon-amount" <?php echo $dataUrl ?> aria-hidden="true">
                        <span class="icon-spinner" aria-hidden="true"></span>
                    </div>
                    <div class="quickicon-sr-desc visually-hidden"></div>
                <?php endif; ?>
                <?php if (isset($displayData['amount'])) : ?>
                    <?php if (isset($displayData['image']) || isset($displayData['icon'])) : ?>
                        <div class="quickicon-amount">
                            <div><?php echo $displayData['amount']; ?></div>
                        </div>
                    <?php else : ?>
                        <div class="quickicon-noicon" style="font-size: xxx-large;">
                            <div><?php echo $displayData['amount']; ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php // Name indicates the component
            if (isset($displayData['name'])) : ?>
                <div class="quickicon-name d-flex align-items-end" <?php echo isset($displayData['ajaxurl']) ? ' aria-hidden="true"' : ''; ?>>
                    <?php echo Text::_($displayData['name']); ?>
                </div>
            <?php endif; ?>
            <?php // Information or action from plugins
            if (isset($displayData['text'])) : ?>
                <div class="quickicon-name d-flex align-items-center">
                    <?php echo $text; ?>
                </div>
            <?php endif; ?>
        </a>
    </li>
    <?php // Add the link to the edit-form
    if (isset($displayData['linkadd'])) : ?>
        <li class="quickicon-linkadd j-links-link d-flex">
            <a class="d-flex" href="<?php echo $displayData['linkadd']; ?>" title="<?php echo Text::_($displayData['name'] . '_ADD'); ?>">
                <span class="icon-plus" aria-hidden="true"></span>
            </a>
        </li>
    </ul>
    </li>
    <?php endif; ?>
