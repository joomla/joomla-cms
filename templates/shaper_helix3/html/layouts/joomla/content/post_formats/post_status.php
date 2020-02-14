<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

if( $displayData['params']->get('post_status') ) {
	echo '<div class="entry-status">';
	echo $displayData['params']->get('post_status');
	echo '</div>';
}