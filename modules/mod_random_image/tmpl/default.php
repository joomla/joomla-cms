<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="random-image<?php echo $moduleclass_sfx; ?>">
<?php if ($link) : ?>
<a href="<?php echo $link; ?>">
<?php endif; ?>
<?php if ($resp) : ?>
	<?php echo '<img src="' . $image->folder . '/' . $image->name . '" style="max-width:100%; height:auto;" />'; ?>
<?php else : ?>
	<?php echo JHtml::_('image', $image->folder . '/' . $image->name, $image->name, array('width' => $image->width, 'height' => $image->height)); ?>
<?php endif; ?>
<?php if ($link) : ?>
</a>
<?php endif; ?>
</div>
