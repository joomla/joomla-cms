<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var array $templateparams */

$title      = $item->anchor_title ? ' title="' . $item->anchor_title . '"' : '';
$anchor_css = $item->anchor_css ?: '';

$linktype = $item->title;

$iconCaret = $templateparams->get('icondownCaret');
// Flips iconCaret if navbar fixed @ bottom.
if ($templateparams->get('nav_Location') === 'navbar-fixed-bottom')
{
	// Flip caret
	$iconCaret = $templateparams->get('iconupCaret');
}

// Add Bootstrap caret
if ($item->isParentAnchor)
{
	$linktype .= '<i class="' . $iconCaret . '"></i>';
}

if ($item->menu_image)
{
	if ($item->menu_image_css)
	{
		$image_attributes['class'] = $item->menu_image_css;
		$linktype                  = JHtml::_('image', $item->menu_image, $item->title, $image_attributes);
	}
	else
	{
		$linktype = JHtml::_('image', $item->menu_image, $item->title);
	}

	if ($item->params->get('menu_text', 1))
	{
		$linktype .= '<span class="image-title">' . $item->title . '</span>';
	}
}
// Allow icon instead of image.
elseif ($item->menu_image_css)
{
	$linktype = '<i class="' . $item->menu_image_css . '"> </i>' . $linktype;
}

?>
<span class="separator <?php echo $anchor_css; ?>"><?php echo $linktype ?></span>
