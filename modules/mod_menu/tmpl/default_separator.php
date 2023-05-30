<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
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

?>
<span <?php echo ArrayHelper::toString($attributes); ?>><?php echo $item->menu_linktype; ?></span>
