<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!count($images))
{
	echo Text::_('MOD_RANDOM_IMAGE_NO_IMAGES');

	return;
}
?>

<div class="mod-randomimage random-image">
<?php if ($link) : ?>
<a href="<?php echo $link; ?>">
<?php endif; ?>
	<?php echo HTMLHelper::_('image', $image->folder . '/' . htmlspecialchars($image->name, ENT_COMPAT, 'UTF-8'), htmlspecialchars($image->name, ENT_COMPAT, 'UTF-8'), array('width' => $image->width, 'height' => $image->height)); ?>
<?php if ($link) : ?>
</a>
<?php endif; ?>
</div>
