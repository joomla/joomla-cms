<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

// Convert icomoon to fa
$icon       = $displayData['icon'];
$iconFixed  = $displayData['fixed'] ?? null;
$iconPrefix = 'fas fa-';
$iconSuffix = $displayData['suffix'] ?? null;
$tabindex   = $displayData['tabindex'] ?? null;
$title      = $displayData['title'] ?? null;
$html       = $displayData['html'] ?? true;

switch ($icon)
{
	case (strpos($icon, 'fa-') !== false):
		$icon = 'fas ' . str_ireplace('fas ', '', $icon);
		break;

	case (strpos($icon, 'icon-') !== false):
		break;

	case 'file':
		$icon = $iconPrefix . 'file';
		break;

	case 'archive':
	case 'folder':
	case 'folder-close':
	case 'folder-folder-2':
	case 'folder-minus':
	case 'folder-plus-2':
	case 'folder-remove':
	case 'drawer-2':
		$icon = $iconPrefix . 'folder';
		break;

	case 'folder-open':
		$icon = $iconPrefix . 'folder-open';
		break;

	case 'check':
	case 'publish':
		$icon = $iconPrefix . 'check';
		break;

	case 'check-circle':
		$icon = $iconPrefix . 'check-circle';
		break;

	case 'unpublish':
	case 'cancel':
	case 'delete':
	case 'remove':
	case 'times':
		$icon = $iconPrefix . 'times';
		break;

	case 'times-cancel':
		$icon = $iconPrefix . 'times-cancel';
		break;

	case 'new':
	case 'save-new':
	case 'add':
	case 'collapse':
		$icon = $iconPrefix . 'plus';
		break;

	case 'apply':
	case 'save':
		$icon = $iconPrefix . 'save';
		break;

	case 'mail':
		$icon = $iconPrefix . 'envelope';
		break;

	case 'unfeatured':
	case 'asterisk':
		$icon = $iconPrefix . 'star';
		break;

	case 'featured':
		$icon = $iconPrefix . 'star featured';
		break;

	case 'checkedout':
	case 'protected':
		$icon = $iconPrefix . 'lock';
		break;

	case 'eye-close':
		$icon = $iconPrefix . 'eye-slash';
		break;

	case 'eye-open':
		$icon = $iconPrefix . 'eye';
		break;

	case 'loop':
	case 'refresh':
	case 'unblock':
		$icon = $iconPrefix . 'sync';
		break;

	case 'contract':
		$icon = $iconPrefix . 'compress';
		break;

	case 'purge':
	case 'trash':
		$icon = $iconPrefix . 'trash';
		break;

	case 'options':
		$icon = $iconPrefix . 'cog';
		break;

	case 'expired':
		$icon = $iconPrefix . 'minus-circle';
		break;

	case 'select-file':
	case 'save-copy':
		$icon = $iconPrefix . 'copy';
		break;

	case 'success':
	case 'checkin':
		$icon = $iconPrefix . 'check-square';
		break;

	case 'generic':
		$icon = $iconPrefix . 'dot-circle';
		break;

	case 'list-2':
		$icon = $iconPrefix . 'list-ul';
		break;

	case 'default':
		$icon = $iconPrefix . 'home';
		break;

	case 'crop':
		$icon = $iconPrefix . 'crop';
		break;

	case 'chevron-down':
		$icon = $iconPrefix . 'chevron-down';
		break;

	case 'previous':
	case 'nextRtl':
		$icon = $iconPrefix . 'chevron-left';
		break;

	case 'next':
	case 'previousRtl':
		$icon = $iconPrefix . 'chevron-right';
		break;

	case 'move':
		$icon = $iconPrefix . 'arrows-alt';
		break;

	case 'loading':
		$icon = $iconPrefix . 'spinner';
		break;

	case 'question':
		$icon = $iconPrefix . 'question';
		break;

	case 'register':
		$icon = $iconPrefix . 'arrow-alt-circle-right';
		break;

	case 'search':
		$icon = $iconPrefix . 'search';
		break;

	case 'search-plus':
		$icon = $iconPrefix . 'search-plus';
		break;

	case 'sort':
		$icon = $iconPrefix . 'sort';
		break;

	case 'user':
		$icon = $iconPrefix . 'user';
		break;

	case 'info':
		$icon = $iconPrefix . 'info-circle';
		break;

	case 'error':
		$icon = $iconPrefix . 'exclamation';
		break;

	case 'warning':
		$icon = $iconPrefix . 'exclamation-circle';
		break;

	case 'warning-2':
		$icon = $iconPrefix . 'exclamation-triangle';
		break;

	default:
		$icon = 'icon-' . $icon;
		break;
}

if ($iconFixed)
{
	$iconFixed = 'fa-fw';
}

if ($html !== false)
{
	$iconAttribs = [
		'class'       => implode(' ', [$icon, $iconFixed, $iconSuffix]),
		'aria-hidden' => "true"
	];

	if ($tabindex)
	{
		$iconAttribs['tabindex'] = $tabindex;
	}

	if ($title)
	{
		$iconAttribs['title'] = $title;
	}

	$icon = '<span ' . ArrayHelper::toString($iconAttribs) . '></span>';
}

echo implode(' ', [$icon, $iconFixed, $iconSuffix]);
