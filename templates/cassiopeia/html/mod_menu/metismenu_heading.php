<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$attributes          = [];
$attributes['title'] = $item->anchor_title ? $item->anchor_title : null;
$attributes['class'] = 'mod-menu__heading nav-header';
$attributes['class'] .= $item->anchor_css ? $item->anchor_css : null;

if ($item->deeper)
{
	$attributes['aria-haspopup'] = 'true';
	$attributes['aria-expanded'] = 'false';
}

$linktype = $item->title;

if ($item->menu_image)
{
	$linktype = HTMLHelper::image($item->menu_image, $item->title);

	if ($item->menu_image_css)
	{
		$image_attributes['class'] = $item->menu_image_css;
		$linktype                  = HTMLHelper::image($item->menu_image, $item->title, $image_attributes);
	}

	if ($itemParams->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}

?>
<span <?php echo ArrayHelper::toString($attributes); ?>><?php echo $linktype; ?></span>
