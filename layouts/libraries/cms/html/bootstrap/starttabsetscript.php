<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   © 2013 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];

echo
	'jQuery(function($){ ',
		'$(', json_encode('#' . $selector . ' a'), ')',
			'.click(function (e) {',
				'e.preventDefault();',
				'$(this).tab("show");',
			'});',
	'});';
