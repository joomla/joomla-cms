<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$image = JHtml::_('image', $image->folder . '/' . $image->name, $image->name, array('width' => $image->width, 'height' => $image->height)); 

?>
<div class="random-image<?php echo $moduleclass_sfx; ?>">
	<?php if ($link) : ?>
		<a href="<?php echo $link; ?>">
			<?php echo $image; ?>
		</a>
	<?php else : ?>
		<?php echo $image; ?>
	<?php endif; ?>
</div>
