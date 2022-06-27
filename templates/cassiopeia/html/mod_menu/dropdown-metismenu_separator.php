<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$attributes = [];

if ($item->anchor_title)
{
	$attributes['title'] = $item->anchor_title;
}

$attributes['class'] = 'mod-menu__separator separator';
$attributes['class'] .= $item->anchor_css ? ' ' . $item->anchor_css : null;

$linktype = $item->title;

if ($item->menu_icon)
{
	// The link is an icon
	if ($itemParams->get('menu_text', 1))
	{
		// If the link text is to be displayed, the icon is added with aria-hidden
		$linktype = '<span class="p-2 ' . $item->menu_icon . '" aria-hidden="true"></span>' . $item->title;
	}
	else
	{
		// If the icon itself is the link, it needs a visually hidden text
		$linktype = '<span class="p-2 ' . $item->menu_icon . '" aria-hidden="true"></span><span class="visually-hidden">' . $item->title . '</span>';
	}
}
elseif ($item->menu_image)
{
	// The link is an image, maybe with an own class
	$image_attributes = [];

	if ($item->menu_image_css)
	{
		$image_attributes['class'] = $item->menu_image_css;
	}

	$linktype = HTMLHelper::_('image', $item->menu_image, $item->title, $image_attributes);

	if ($itemParams->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}

if ($showAll && $item->deeper)
{
	$attributes['class'] .= ' mm-collapsed mm-toggler mm-toggler-nolink';
	$attributes['aria-haspopup'] = 'true';
	$attributes['aria-expanded'] = 'false';
	echo '<button ' . ArrayHelper::toString($attributes) . '>' . $linktype . '</button>';
}
else
{
	echo '<span ' . ArrayHelper::toString($attributes) . '>' . $linktype . '</span>';
}
