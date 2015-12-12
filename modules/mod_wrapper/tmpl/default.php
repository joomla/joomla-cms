<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::script('com_wrapper/iframe-height.min.js', false, true);
?>
<iframe <?php echo $load; ?>
	id="blockrandom"
	name="<?php echo $target; ?>"
	src="<?php echo $url; ?>"
	width="<?php echo $width; ?>"
	height="<?php echo $height; ?>"
	scrolling="<?php echo $scroll; ?>"
	frameborder="<?php echo $frameborder; ?>"
	class="wrapper<?php echo $moduleclass_sfx; ?>" >
	<?php echo JText::_('MOD_WRAPPER_NO_IFRAMES'); ?>
</iframe>
