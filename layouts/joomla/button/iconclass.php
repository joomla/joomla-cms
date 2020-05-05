<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Convert icomoon to fa
$icon = $displayData['icon'];

if ($icon === 'archive' || $icon === 'folder-close' || $icon === 'folder-folder-2' || $icon === 'folder-minus' || $icon === 'folder-plus-2' || $icon === 'folder-remove' || $icon === 'drawer-2')
{
	$icon = 'folder';
}
elseif ($icon === 'publish')
{
	$icon = 'check';
}
elseif ($icon === 'unpublish' || $icon === 'cancel' || $icon === 'delete' || $icon === 'remove')
{
	$icon = 'times';
}
elseif ($icon === 'new' || $icon === 'save-new')
{
	$icon = 'plus';
}
elseif ($icon === 'apply')
{
	$icon = 'save';
}
elseif ($icon === 'mail')
{
	$icon = 'envelope';
}
elseif ($icon === 'featured' || $icon === 'unfeatured' || $icon === 'asterisk' || $icon === 'default')
{
	$icon = 'star';
}
elseif ($icon === 'checkedout')
{
	$icon = 'lock';
}
elseif ($icon === 'eye-close')
{
	$icon = 'eye-slash';
}
elseif ($icon === 'eye-open')
{
	$icon = 'eye';
}
elseif ($icon === 'loop' || $icon === 'refresh' || $icon === 'unblock')
{
	$icon = 'sync';
}
elseif ($icon === 'contract')
{
	$icon = 'compress';
}
elseif ($icon === 'purge')
{
	$icon = 'trash';
}
elseif ($icon === 'options')
{
	$icon = 'cog';
}
elseif ($icon === 'expired')
{
	$icon = 'minus-circle';
}
elseif ($icon === 'save-copy')
{
	$icon = 'copy';
}
elseif ($icon === 'checkin')
{
	$icon = 'check-square';
}
elseif ($icon === 'generic')
{
	$icon = 'dot-circle';
}
elseif ($icon === 'list-2')
{
	$icon = 'list-ul';
}

?>
	fas fa-<?php echo $icon;
