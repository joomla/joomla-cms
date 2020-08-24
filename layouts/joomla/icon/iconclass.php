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
$html = isset($displayData['html']) ? $displayData['html'] : false;

if (strpos($icon, 'fa-') !== false)
{
	$icon = 'fas ' . $icon;
}
else if (strpos($icon, 'icon-') !== false)
{
	$icon = $icon;
}
else if ($icon === 'archive' || $icon === 'folder-close' || $icon === 'folder-folder-2' || $icon === 'folder-minus' || $icon === 'folder-plus-2' || $icon === 'folder-remove' || $icon === 'drawer-2')
{
	$icon = 'fas fa-folder';
}
else if ($icon === 'publish')
{
	$icon = 'fas fa-check';
}
else if ($icon === 'unpublish' || $icon === 'cancel' || $icon === 'delete' || $icon === 'remove')
{
	$icon = 'fas fa-times';
}
else if ($icon === 'new' || $icon === 'save-new')
{
	$icon = 'fas fa-plus';
}
else if ($icon === 'apply' || $icon === 'save')
{
	$icon = 'fas fa-save';
}
else if ($icon === 'mail')
{
	$icon = 'fas fa-envelope';
}
else if ($icon === 'unfeatured' || $icon === 'asterisk')
{
	$icon = 'fas fa-star';
}
else if ($icon === 'featured')
{
	$icon = 'fas fa-star featured';
}
else if ($icon === 'checkedout' || $icon === 'protected')
{
	$icon = 'fas fa-lock';
}
else if ($icon === 'eye-close')
{
	$icon = 'fas fa-eye-slash';
}
else if ($icon === 'eye-open')
{
	$icon = 'fas fa-eye';
}
else if ($icon === 'loop' || $icon === 'refresh' || $icon === 'unblock')
{
	$icon = 'fas fa-sync';
}
else if ($icon === 'contract')
{
	$icon = 'fas fa-compress';
}
else if ($icon === 'purge' || $icon === 'trash')
{
	$icon = 'fas fa-trash';
}
else if ($icon === 'options')
{
	$icon = 'fas fa-cog';
}
else if ($icon === 'expired')
{
	$icon = 'fas fa-minus-circle';
}
else if ($icon === 'save-copy')
{
	$icon = 'fas fa-copy';
}
else if ($icon === 'checkin')
{
	$icon = 'fas fa-check-square';
}
else if ($icon === 'generic')
{
	$icon = 'fas fa-dot-circle';
}
else if ($icon === 'list-2')
{
	$icon = 'fas fa-list-ul';
}
else if ($icon === 'default')
{
	$icon = 'fas fa-home';
}
else if ($icon === 'crop')
{
	$icon = 'fas fa-crop';
}
else if ($icon === 'chevron-down')
{
	$icon = 'fas fa-chevron-down';
}
else if ($icon === 'times')
{
	$icon = 'fas fa-times';
}
else if ($icon === 'move')
{
	$icon = 'fas fa-arrows-alt';
}
else
{
	$icon = 'icon-' . $icon;
}

$output = $icon;
if ($html !== false)
{
	$output = '<span class="' . $icon . '" aria-hidden="true"></span>';
}

echo $output;
