<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

// Convert icomoon to fa
$icon       = $displayData['icon'];

// Get fixed width icon or not
$iconFixed  = $displayData['fixed'] ?? null;

// Set default prefix to be fontawesome
$iconPrefix = $displayData['prefix'] ?? 'icon-';

// Get other classNames if set, like icon-white, text-danger
$iconSuffix = $displayData['suffix'] ?? null;

// Get other attributes besides classNames
$tabindex   = $displayData['tabindex'] ?? null;
$title      = $displayData['title'] ?? null;

// Default output in <span>. ClassNames if set to false
$html       = $displayData['html'] ?? true;

// Replace double set icon-icon-
// ToDo: Joomla should be cleaned so this replacement is not needed.
$icon       = str_replace('icon-icon-', 'icon-', $icon);

switch ($icon)
{
	case (strpos($icon, 'icon-') !== false):
		$iconPrefix = $displayData['prefix'] ?? null;
		break;

	case 'nextRtl':
		$icon = 'angle-left';
		break;

	case 'previousRtl':
		$icon = 'angle-right';
		break;

	case 'paginationStart':
	case 'paginationEndRtl':
		$icon = 'angle-double-left';
		break;

	case 'paginationStartRtl':
	case 'paginationEnd':
		$icon = 'angle-double-right';
		break;

	case 'paginationNext':
	case 'paginationPrevRtl':
		$icon = 'angle-left';
		break;

	case 'paginationNextRtl':
	case 'paginationPrev':
		$icon = 'angle-right';
		break;

	default:
		break;
}

if ($iconFixed)
{
	$iconFixed = 'icon-fw';
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
