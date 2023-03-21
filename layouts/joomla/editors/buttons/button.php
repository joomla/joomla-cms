<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

$button = $displayData;

$class   = 'btn btn-secondary';
$class  .= $button->get('class') ? ' ' . $button->get('class') : null;
$class  .= $button->get('modal') ? ' modal-button' : null;
$href    = '#' . $button->get('editor') . '_' . strtolower($button->get('name', '')) . '_modal';
$link    = $button->get('link');
$onclick = $button->get('onclick') ? ' onclick="' . $button->get('onclick') . '"' : '';
$title   = $this->escape($button->get('title') ? $button->get('title') : $button->get('text', ''));
$icon    = $button->get('icon');
$action  = $this->escape($button->get('action', ''));
$options = (array) $button->get('options');

if ($link) {
    $link = str_contains($link, '&amp;') ? htmlspecialchars_decode($link) : $link;
    $link = Uri::base(true) . '/' . $link;
    $options['src'] = $link;
}

$optStr  = $this->escape(json_encode($options));

?>
<button type="button" data-joomla-editor-button-action="<?php echo $action; ?>" data-joomla-editor-button-options="<?php echo $optStr; ?>"
    class="xtd-button btn btn-secondary <?php echo $class; ?>" title="<?php echo $title; ?>" <?php echo $onclick; ?>
    <?php echo !$action && $button->get('modal') ? 'data-bs-toggle="modal" data-bs-target="' . $href . '"' : '' ?>>
    <?php if ($icon): ?>
    <span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
    <?php endif; ?>
    <?php echo $button->get('text'); ?>
</button>
