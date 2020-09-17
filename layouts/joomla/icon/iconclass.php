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

// Get fixed width icon or not
$iconFixed  = $displayData['fixed'] ?? null;

// Set default prefix to be fontawesome
$iconPrefix = $displayData['prefix'] ?? 'fas fa-';

// Get other classNames if set, like icon-white, text-danger
$iconSuffix = $displayData['suffix'] ?? null;

// Get other attributes besides classNames
$tabindex   = $displayData['tabindex'] ?? null;
$title      = $displayData['title'] ?? null;

// Default output in <span>. ClassNames if set to false
$html       = $displayData['html'] ?? true;

switch ($icon)
{
	case (strpos($icon, 'icon-') !== false):
	case (strpos($icon, 'fa ') !== false):
	case (strpos($icon, 'fas ') !== false):
	case (strpos($icon, 'fab ') !== false):
		$iconPrefix = $displayData['prefix'] ?? null;
		break;

	case (strpos($icon, 'fa-') !== false):
		$iconPrefix = $displayData['prefix'] ?? 'fas ';
		break;

	case 'file':
		$icon = 'file';
		break;

	case 'archive':
	case 'folder':
	case 'folder-close':
	case 'folder-folder-2':
	case 'folder-minus':
	case 'folder-plus-2':
	case 'folder-remove':
	case 'drawer-2':
		$icon = 'folder';
		break;

	case 'folder-open':
		$icon = 'folder-open';
		break;

	case 'check':
	case 'publish':
		$icon = 'check';
		break;

	case 'check-circle':
		$icon = 'check-circle';
		break;

	case 'unpublish':
	case 'cancel':
	case 'delete':
	case 'remove':
	case 'times':
		$icon = 'times';
		break;

	case 'times-cancel':
		$icon = 'times-cancel';
		break;

	case 'new':
	case 'save-new':
	case 'add':
	case 'collapse':
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

	case 'hits';
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

	case 'select-file':
	case 'save-copy':
		$icon = 'copy';
		break;

	case 'success':
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

	case 'previous':
	case 'nextRtl':
		$icon = 'chevron-left';
		break;

	case 'next':
	case 'previousRtl':
		$icon = 'chevron-right';
		break;

	case 'move':
		$icon = 'arrows-alt';
		break;

	case 'loading':
		$icon = 'spinner';
		break;

	case 'question':
		$icon = 'question';
		break;

	case 'register':
		$icon = 'arrow-alt-circle-right';
		break;

	case 'search':
		$icon = 'search';
		break;

	case 'search-plus':
		$icon = 'search-plus';
		break;

	case 'ellipsis-v':
		$icon = 'ellipsis-v';
		break;

	case 'ellipsis-h':
		$icon = 'ellipsis-h';
		break;

	case 'sort':
		$icon = 'sort';
		break;

	case 'user':
		$icon = 'user';
		break;

	case 'info':
		$icon = 'info-circle';
		break;

	case 'error':
		$icon = 'exclamation';
		break;

	case 'warning':
		$icon = 'exclamation-circle';
		break;

	case 'warning-2':
		$icon = 'exclamation-triangle';
		break;

	case 'paginationStart':
	case 'paginationEndRtl':
		$icon = 'fa-angle-double-left';
		break;

	case 'paginationStartRtl':
	case 'paginationEnd':
		$icon = 'fa-angle-double-right';
		break;

	case 'paginationNext':
	case 'paginationPrevRtl':
		$icon = 'fa-angle-left';
		break;

	case 'paginationNextRtl':
	case 'paginationPrev':
		$icon = 'fa-angle-right';
		break;

	default:
		$icon = 'icon-' . $icon;
		break;
}

if ($iconFixed)
{
	$iconFixed = 'fa-fw';
}

// Just render icon as className
$icon = trim(implode(' ', [$iconPrefix . $icon, $iconFixed, $iconSuffix]));

// Convert icon to html output when HTML !== false
if ($html !== false)
{
	$iconAttribs = [
		'class'       => $icon,
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

echo $icon;
