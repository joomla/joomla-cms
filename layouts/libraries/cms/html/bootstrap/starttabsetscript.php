<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];

echo
	'jQuery(function($){ ',
		'$(', json_encode('#' . $selector . ' a'), ')',
			'.click(function (e) {',
				'e.preventDefault();',
				'$(this).tab("show");',
			'});',
	'});';
