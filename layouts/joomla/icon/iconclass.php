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

switch (true)
{
	case (strpos($icon, 'fa-') !== false):
		$icon = 'fas ' . $icon;
		break;

	case (strpos($icon, 'icon-') !== false) :
		$icon = $icon;
		break;

	case ($icon === 'archive'):
	case ($icon === 'folder-close'):
	case ($icon === 'folder-folder-2'):
	case ($icon === 'folder-minus'):
	case ($icon === 'folder-plus-2'):
	case ($icon === 'folder-remove'):
	case ($icon === 'drawer-2'):
		$icon = 'fas fa-folder';
		break;

	case ($icon === 'publish') :
		$icon = 'fas fa-check';
		break;

	case ($icon === 'unpublish'):
	case ($icon === 'cancel'):
	case ($icon === 'delete'):
	case ($icon === 'remove'):
	case ($icon === 'times'):
		$icon = 'fas fa-times';
		break;

	case ($icon === 'new'):
	case ($icon === 'save-new') :
		$icon = 'fas fa-plus';
		break;

	case ($icon === 'apply'):
	case ($icon === 'save') :
		$icon = 'fas fa-save';
		break;

	case ($icon === 'mail') :
		$icon = 'fas fa-envelope';
		break;

	case ($icon === 'unfeatured'):
	case ($icon === 'asterisk') :
		$icon = 'fas fa-star';
		break;

	case ($icon === 'featured') :
		$icon = 'fas fa-star featured';
		break;

	case ($icon === 'checkedout'):
	case ($icon === 'protected') :
		$icon = 'fas fa-lock';
		break;

	case ($icon === 'eye-close') :
		$icon = 'fas fa-eye-slash';
		break;

	case ($icon === 'eye-open') :
		$icon = 'fas fa-eye';
		break;

	case ($icon === 'loop'):
	case ($icon === 'refresh'):
	case ($icon === 'unblock') :
		$icon = 'fas fa-sync';
		break;

	case ($icon === 'contract') :
		$icon = 'fas fa-compress';
		break;

	case ($icon === 'purge'):
	case ($icon === 'trash') :
		$icon = 'fas fa-trash';
		break;

	case ($icon === 'options') :
		$icon = 'fas fa-cog';
		break;

	case ($icon === 'expired') :
		$icon = 'fas fa-minus-circle';
		break;

	case ($icon === 'save-copy') :
		$icon = 'fas fa-copy';
		break;

	case ($icon === 'checkin') :
		$icon = 'fas fa-check-square';
		break;

	case ($icon === 'generic') :
		$icon = 'fas fa-dot-circle';
		break;

	case ($icon === 'list-2') :
		$icon = 'fas fa-list-ul';
		break;

	case ($icon === 'default') :
		$icon = 'fas fa-home';
		break;

	case ($icon === 'crop') :
		$icon = 'fas fa-crop';
		break;

	case ($icon === 'chevron-down') :
		$icon = 'fas fa-chevron-down';
		break;

	case ($icon === 'move'):
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
