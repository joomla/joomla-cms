<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Editor\Button\Button $button */
$button = $displayData;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa       = Factory::getApplication()->getDocument()->getWebAssetManager();
$btnAsset = 'editor-button.' . $button->getButtonName();

// Enable the button assets if any
if ($wa->assetExists('style', $btnAsset)) {
    $wa->useStyle($btnAsset);
}
if ($wa->assetExists('script', $btnAsset)) {
    $wa->useScript($btnAsset);
}

$class   = 'btn btn-secondary';
$class  .= $button->get('class') ? ' ' . $button->get('class') : null;
$class  .= $button->get('modal') ? ' modal-button' : null;
$href    = '#' . $button->get('editor') . '_' . strtolower($button->get('name', '')) . '_modal';
$link    = $button->get('link');
$onclick = $button->get('onclick') ? ' onclick="' . $button->get('onclick') . '"' : '';
$title   = $button->get('title') ? $button->get('title') : $button->get('text', '');
$icon    = $button->get('icon');
$action  = $button->get('action', '');
$options = (array) $button->get('options');

// Correct the link, check for legacy with &amp; in it, and prepend a base Uri
if ($link && $link[0] !== '#') {
    $link           = str_contains($link, '&amp;') ? htmlspecialchars_decode($link) : $link;
    $link           = Uri::base(true) . '/' . $link;
    $options['src'] = $options['src'] ?? $link;
}

// Detect a legacy BS modal, and set action to "modal" for legacy buttons, when possible
$legacyModal = $button->get('modal');

// Prepare default values for modal
if ($action === 'modal') {
    $wa->useScript('joomla.dialog');
    $legacyModal = false;

    $options['popupType']  = $options['popupType'] ?? 'iframe';
    $options['textHeader'] = $options['textHeader'] ?? $title;
    $options['iconHeader'] = $options['iconHeader'] ?? 'icon-' . $icon;
}

$optStr = $options && $action ? json_encode($options, JSON_UNESCAPED_SLASHES) : '';

?>

<button type="button" data-joomla-editor-button-action="<?php echo $this->escape($action); ?>" data-joomla-editor-button-options="<?php echo $this->escape($optStr); ?>"
        class="xtd-button btn btn-secondary <?php echo $class; ?>" title="<?php echo $this->escape($title); ?>" <?php echo $onclick; ?>
    <?php echo $legacyModal ? 'data-bs-toggle="modal" data-bs-target="' . $href . '"' : '' ?>>
    <?php if ($icon) : ?>
        <span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
    <?php endif; ?>
    <?php echo $button->get('text'); ?>
</button>

