<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

if ( $displayData['params']->get('link_url') ) {

	$link_title = ( $displayData['params']->get('link_title') ) ? $displayData['params']->get('link_title') : $displayData['params']->get('link_url');

	?>
	<div class="entry-link">
		<a target="_blank" href="<?php echo $displayData['params']->get('link_url'); ?>"><h4><?php echo $link_title; ?></h4></a>
	</div>
	<?php
}
