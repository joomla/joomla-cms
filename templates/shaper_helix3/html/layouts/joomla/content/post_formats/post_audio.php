<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

if ( $displayData['params']->get('audio') ) {
	?>
	<div class="entry-audio embed-responsive embed-responsive-16by9">
		<?php echo $displayData['params']->get('audio'); ?>
	</div>
	<?php
}
