<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$attributes = [];

if ($item->anchor_title) {
    $attributes['title'] = $item->anchor_title;
}

$attributes['class'] = 'mod-menu__separator separator';
$attributes['class'] .= $item->anchor_css ? ' ' . $item->anchor_css : null;

if ($showAll && $item->deeper) {
    $attributes['class'] .= ' mm-collapsed mm-toggler mm-toggler-nolink';
    $attributes['aria-haspopup'] = 'true';
    $attributes['aria-expanded'] = 'false';
    echo '<button ' . ArrayHelper::toString($attributes) . '>' . $item->menu_linktype . '</button>';
} else {
    echo '<span ' . ArrayHelper::toString($attributes) . '>' . $item->menu_linktype . '</span>';
}
