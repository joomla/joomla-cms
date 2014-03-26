<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 * 	$hiddenLabel     : (boolean) Do we have to show the label
 * 	$label           : (string) The html code for the label (not required if hiddenLabel is true)
 * 	$input           : (string)  The input field html code
 */

?>

<div class="control-group">
	<?php if (!isset($displayData['hiddenLabel']) || isset($displayData['hiddenLabel']) && $displayData['hiddenLabel'] == false) : ?>
		<div class="control-label"><?php echo $displayData['label']; ?></div>
	<?php endif; ?>
	<div class="controls"><?php echo $displayData['input']; ?></div>
</div>