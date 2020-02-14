<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title      = $item->anchor_title ? ' title="' . $item->anchor_title . '"' : '';
$anchor_css = $item->anchor_css ? $item->anchor_css : '';

$linktype   = $item->title;

if ($item->menu_image)
{
	$linktype = JHtml::_('image', $item->menu_image, $item->title);

	if ($item->params->get('menu_text', 1))
	{
		$linktype .= '<a class="image-title">' . $item->title . '</a>';
	}
}
?>
<a class="separator <?php echo $anchor_css; ?>"<?php echo $title; ?>><?php echo $linktype; ?></a>
<?php
if(($module->position == 'offcanvas') && ($item->deeper)) {
	echo '<span class="offcanvas-menu-toggler collapsed" data-toggle="collapse" data-target="#collapse-menu-'. $item->id .'"><i class="open-icon fa fa-angle-down"></i><i class="close-icon fa fa-angle-up"></i></span>';
}
