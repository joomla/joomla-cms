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

// Set default prefix to be fontawesome
$iconPrefix = $displayData['prefix'] ?? 'fas fa-';

switch ($icon)
{
	case (strpos($icon, 'icon-') !== false):
	case (strpos($icon, 'fa ') !== false):
	case (strpos($icon, 'fas ') !== false):
	case (strpos($icon, 'fab ') !== false):
		$iconPrefix = $displayData['prefix'] ?? null;
		break;

	case 'archive':
	case 'folder-close':
	case 'folder-folder-2':
	case 'folder-minus':
	case 'folder-plus-2':
	case 'folder-remove':
	case 'drawer-2':
		$icon = 'folder';
		break;

	case 'publish':
		$icon = 'check';
		break;

	case 'unpublish':
	case 'cancel':
	case 'delete':
	case 'remove':
	case 'times':
		$icon = 'times';
		break;

	case 'new':
	case 'save-new':
		$icon = 'plus';
		break;

	case 'apply':
	case 'save':
		$icon = 'save';
		break;

	case 'mail':
		$icon = 'envelope';
		break;

	case 'unfeatured':
	case 'asterisk':
		$icon = 'star';
		break;

	case 'featured':
		$icon = 'star featured';
		break;

	case 'checkedout':
	case 'protected':
		$icon = 'lock';
		break;

	case 'eye-close':
		$icon = 'eye-slash';
		break;

	case 'eye-open':
		$icon = 'eye';
		break;

	case 'loop':
	case 'refresh':
	case 'unblock':
		$icon = 'sync';
		break;

	case 'contract':
		$icon = 'compress';
		break;

	case 'purge':
	case 'trash':
		$icon = 'trash';
		break;

	case 'options':
		$icon = 'cog';
		break;

	case 'expired':
		$icon = 'minus-circle';
		break;

	case 'save-copy':
		$icon = 'copy';
		break;

	case 'checkin':
		$icon = 'check-square';
		break;

	case 'generic':
		$icon = 'dot-circle';
		break;

	case 'list-2':
		$icon = 'list-ul';
		break;

	case 'default':
		$icon = 'home';
		break;

	case 'crop':
		$icon = 'crop';
		break;

	case 'chevron-down':
		$icon = 'chevron-down';
		break;

	case 'move':
		$icon = 'arrows-alt';
		break;

	default:
		$icon = 'icon-' . $icon;
		break;
}

if ($html !== false)
{
	$icon = '<span class="' . $iconPrefix . $icon . '" aria-hidden="true"></span>';
}

echo $iconPrefix . $icon;
