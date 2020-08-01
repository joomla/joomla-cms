<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Convert icomoon to fa
$icon = $displayData['icon'];

if(strpos($icon, 'fa-') == true || strpos($icon, 'icon-') == true){
	$icon = $icon;
}
elseif  ($icon === 'archive' || $icon === 'folder-close' || $icon === 'folder-folder-2' || $icon === 'folder-minus' || $icon === 'folder-plus-2' || $icon === 'folder-remove' || $icon === 'drawer-2')
{
	$icon = 'fas fa-folder';
}
elseif ($icon === 'publish')
{
	$icon = 'fas fa-check';
}
elseif ($icon === 'unpublish' || $icon === 'cancel' || $icon === 'delete' || $icon === 'remove')
{
	$icon = 'fas fa-times';
}
elseif ($icon === 'new' || $icon === 'save-new')
{
	$icon = 'fas fa-plus';
}
elseif ($icon === 'apply' || $icon === 'save')
{
	$icon = 'fas fa-save';
}
elseif ($icon === 'mail')
{
	$icon = 'fas fa-envelope';
}
elseif ($icon === 'unfeatured' || $icon === 'asterisk')
{
	$icon = 'fas fa-star';
}
elseif ($icon === 'featured')
{
	$icon = 'fas fa-star featured';
}
elseif ($icon === 'checkedout')
{
	$icon = 'fas fa-lock';
}
elseif ($icon === 'eye-close')
{
	$icon = 'fas fa-eye-slash';
}
elseif ($icon === 'eye-open')
{
	$icon = 'fas fa-eye';
}
elseif ($icon === 'loop' || $icon === 'refresh' || $icon === 'unblock')
{
	$icon = 'fas fa-sync';
}
elseif ($icon === 'contract')
{
	$icon = 'fas fa-compress';
}
elseif ($icon === 'purge')
{
	$icon = 'fas fa-trash';
}
elseif ($icon === 'options')
{
	$icon = 'fas fa-cog';
}
elseif ($icon === 'expired')
{
	$icon = 'fas fa-minus-circle';
}
elseif ($icon === 'save-copy')
{
	$icon = 'fas fa-copy';
}
elseif ($icon === 'checkin')
{
	$icon = 'fas fa-check-square';
}
elseif ($icon === 'generic')
{
	$icon = 'fas fa-dot-circle';
}
elseif ($icon === 'list-2')
{
	$icon = 'fas fa-list-ul';
}
elseif ($icon === 'default')
{
	$icon = 'fas fa-home';
}
elseif ($icon === 'crop')
{
	$icon = 'fas fa-crop';
}
elseif ($icon === 'chevron-down')
{
	$icon = 'fas fa-chevron-down';
}
elseif ($icon === 'times')
{
	$icon = 'fas fa-times';
}
elseif ($icon === 'move')
{
	$icon = 'fas fa-arrows-alt';
}
else
{
	$icon = 'icon-' . $icon;
}
echo $icon;
