<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="random-image<?php echo $moduleclass_sfx; ?>">
<?php if ($link) : ?>
<a href="<?php echo $link; ?>">
<?php endif; ?>
	<?php echo '<img src="' . $image->folder . '/' . $image->name . '" style="max-width:' . ($image->width>100?100:$image->width) . '%; height:auto;" />'; ?>
<?php if ($link) : ?>
</a>
<?php endif; ?>
</div>
