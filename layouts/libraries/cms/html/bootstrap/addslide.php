<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="accordion-group<?php echo $displayData['class']; ?>">
	<div class="accordion-heading">
		<strong><a href="#<?php echo $displayData['id']; ?>" data-parent="#<?php echo $displayData['selector']; ?>" data-toggle="collapse" class="accordion-toggle">
			<?php echo $text; ?>
		</a></strong>
	</div>
	<div class="accordion-body collapse<?php echo $displayData['in']; ?>" id="<?php echo $displayData['id']; ?>">
		<div class="accordion-inner">