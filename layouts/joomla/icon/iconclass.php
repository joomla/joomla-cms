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
		$icon = 'fas fa-folder';
		break;

	case 'publish':
		$icon = 'fas fa-check';
		break;

	case 'unpublish':
	case 'cancel':
	case 'delete':
	case 'remove':
	case 'times':
		$icon = 'fas fa-times';
		break;

	case 'new':
	case 'save-new':
		$icon = 'fas fa-plus';
		break;

	case 'apply':
	case 'save':
		$icon = 'fas fa-save';
		break;

	case 'mail':
		$icon = 'fas fa-envelope';
		break;

	case 'unfeatured':
	case 'asterisk':
		$icon = 'fas fa-star';
		break;

	case 'featured':
		$icon = 'fas fa-star featured';
		break;

	case 'checkedout':
	case 'protected':
		$icon = 'fas fa-lock';
		break;

	case 'eye-close':
		$icon = 'fas fa-eye-slash';
		break;

	case 'eye-open':
		$icon = 'fas fa-eye';
		break;

	case 'loop':
	case 'refresh':
	case 'unblock':
		$icon = 'fas fa-sync';
		break;

	case 'contract':
		$icon = 'fas fa-compress';
		break;

	case 'purge':
	case 'trash':
		$icon = 'fas fa-trash';
		break;

	case 'options':
		$icon = 'fas fa-cog';
		break;

	case 'expired':
		$icon = 'fas fa-minus-circle';
		break;

	case 'save-copy':
		$icon = 'fas fa-copy';
		break;

	case 'checkin':
		$icon = 'fas fa-check-square';
		break;

	case 'generic':
		$icon = 'fas fa-dot-circle';
		break;

	case 'list-2':
		$icon = 'fas fa-list-ul';
		break;

	case 'default':
		$icon = 'fas fa-home';
		break;

	case 'crop':
		$icon = 'fas fa-crop';
		break;

	case 'chevron-down':
		$icon = 'fas fa-chevron-down';
		break;

	case 'move':
		$icon = 'fas fa-arrows-alt';
		break;

	default:
		$icon = 'icon-' . $icon;
		break;
}

if ($html !== false)
{
	$icon = '<span class="' . $icon . '" aria-hidden="true"></span>';
}

echo $icon;
