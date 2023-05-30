<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;

$attributes = [];

if ($item->anchor_title) {
    $attributes['title'] = $item->anchor_title;
}

if ($item->anchor_css) {
    $attributes['class'] = $item->anchor_css;
}

if ($item->anchor_rel) {
    $attributes['rel'] = $item->anchor_rel;
}

if ($item->id == $active_id) {
    $attributes['aria-current'] = 'location';

    if ($item->current) {
        $attributes['aria-current'] = 'page';
    }
}

if ($item->browserNav == 1) {
    $attributes['target'] = '_blank';
} elseif ($item->browserNav == 2) {
    $options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes';

    $attributes['onclick'] = "window.open(this.href, 'targetWindow', '" . $options . "'); return false;";
}

echo HTMLHelper::link(OutputFilter::ampReplace(htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8', false)), $item->menu_linktype, $attributes);

if ($showAll && $item->deeper) {
    echo '<button class="mm-collapsed mm-toggler mm-toggler-link" aria-haspopup="true" aria-expanded="false" aria-label="' . $item->title . '"></button>';
}
