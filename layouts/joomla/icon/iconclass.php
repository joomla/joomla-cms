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
$html = $displayData['html'] ?? true;

// Allow for custom icon family & prefix
$iconPrefix = $displayData['prefix'] ?? 'fas fa-';

// Set to wide with if true.
$iconWide = $displayData['wide'] ? ' fa-fw' : '';

switch ($icon)
{
	case (strpos($icon, 'fa-') !== false):
		$icon = 'fas ' . str_ireplace('fas ', '', $icon);
		break;

	case (strpos($icon, 'icon-') !== false):
		break;

	case 'archive':
	case 'folder-close':
	case 'folder-folder-2':
	case 'folder-minus':
	case 'folder-plus-2':
	case 'folder-remove':
	case 'drawer-2':
		$icon = $iconPrefix . 'folder' . $iconWide;
		break;

	case 'publish':
		$icon = $iconPrefix . 'check' . $iconWide;
		break;

	case 'unpublish':
	case 'cancel':
	case 'delete':
	case 'remove':
	case 'times':
		$icon = $iconPrefix . 'times' . $iconWide;
		break;

	case 'new':
	case 'save-new':
		$icon = $iconPrefix . 'plus' . $iconWide;
		break;

	case 'apply':
	case 'save':
		$icon = $iconPrefix . 'save' . $iconWide;
		break;

	case 'mail':
		$icon = $iconPrefix . 'envelope' . $iconWide;
		break;

	case 'unfeatured':
	case 'asterisk':
		$icon = $iconPrefix . 'star' . $iconWide;
		break;

	case 'featured':
		$icon = $iconPrefix . 'star featured' . $iconWide;
		break;

	case 'checkedout':
	case 'protected':
		$icon = $iconPrefix . 'lock' . $iconWide;
		break;

	case 'eye-close':
		$icon = $iconPrefix . 'eye-slash' . $iconWide;
		break;

	case 'eye-open':
		$icon = $iconPrefix . 'eye' . $iconWide;
		break;

	case 'loop':
	case 'refresh':
	case 'unblock':
		$icon = $iconPrefix . 'sync' . $iconWide;
		break;

	case 'contract':
		$icon = $iconPrefix . 'compress' . $iconWide;
		break;

	case 'purge':
	case 'trash':
		$icon = $iconPrefix . 'trash' . $iconWide;
		break;

	case 'options':
		$icon = $iconPrefix . 'cog' . $iconWide;
		break;

	case 'expired':
		$icon = $iconPrefix . 'minus-circle' . $iconWide;
		break;

	case 'save-copy':
		$icon = $iconPrefix . 'copy' . $iconWide;
		break;

	case 'checkin':
		$icon = $iconPrefix . 'check-square' . $iconWide;
		break;

	case 'generic':
		$icon = $iconPrefix . 'dot-circle' . $iconWide;
		break;

	case 'list-2':
		$icon = $iconPrefix . 'list-ul' . $iconWide;
		break;

	case 'default':
		$icon = $iconPrefix . 'home' . $iconWide;
		break;

	case 'crop':
		$icon = $iconPrefix . 'crop' . $iconWide;
		break;

	case 'chevron-down':
		$icon = $iconPrefix . 'chevron-down' . $iconWide;
		break;

	case 'move':
		$icon = $iconPrefix . 'arrows-alt' . $iconWide;
		break;

	default:
		$icon = 'icon-' . $icon . $iconWide;
		break;
}

if ($html !== false)
{
	$icon = '<span class="' . $icon . $iconWide . '" aria-hidden="true"></span>';
}

echo $icon;
