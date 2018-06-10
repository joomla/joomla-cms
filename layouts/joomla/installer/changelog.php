<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('JPATH_BASE') or die;

$output = '';

foreach ($displayData as $changeType => $items)
{
	switch ($changeType)
	{
		case 'security':
			$class = 'badge-danger';
			break;
		case 'fix':
			$class = 'badge-dark';
			break;
		case 'language':
			$class = 'badge-jlanguage';
			break;
		case 'addition':
			$class = 'badge-success';
			break;
		case 'change':
			$class = 'badge-warning';
			break;
		case 'removed':
			$class = 'badge-light';
			break;
		default:
		case 'note':
			$class = 'badge-info';
			break;
	}

	$output .= '<div class="pull-left">';
	$output .= '<div class="badge ' . $class . '">' . Text::_('COM_INSTALLER_CHANGELOG_' . $changeType) . '</div>';
	$output .= '<ul>';

	foreach ($items as $item)
	{
		$output .= '<li>' . $item . '</li>';
	}

	$output .= '</ul>';
	$output .= '</div>';
	$output .= '<div class="clearfix"></div>';
}

echo $output;