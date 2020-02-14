<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// get item params and decode it
$item_decode = json_decode($item->params);

// Note. It is important to remove spaces between elements.
$class = $item->anchor_css ? 'class="' . $item->anchor_css . '" ' : '';
$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';

if ($item->menu_image)
{
	$item->params->get('menu_text', 1) ?
	$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
	$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
}
else
{
	$linktype = $item->title;
}

$icon = '';
if (isset($item_decode->icon) && $item_decode->icon) {
	$icon = ' <i class="fa ' . $item_decode->icon . '"></i>';
}

switch ($item->browserNav)
{
	default:
	case 0:
?><a <?php echo $class; ?>href="<?php echo $item->flink; ?>" <?php echo $title; ?>><?php echo $icon . ' ' . $linktype; ?></a><?php
		break;
	case 1:
		// _blank
?><a <?php echo $class; ?>href="<?php echo $item->flink; ?>" target="_blank" <?php echo $title; ?>><?php echo $icon . ' ' .$linktype; ?></a><?php
		break;
	case 2:
	// Use JavaScript "window.open"
?><a <?php echo $class; ?>href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;" <?php echo $title; ?>><?php echo $icon . ' ' .$linktype; ?></a>
<?php
		break;
}

if(($module->position == 'offcanvas') && ($item->deeper)) {
	echo '<span class="offcanvas-menu-toggler collapsed" data-toggle="collapse" data-target="#collapse-menu-'. $item->id .'"><i class="open-icon fa fa-angle-down"></i><i class="close-icon fa fa-angle-up"></i></span>';
}
