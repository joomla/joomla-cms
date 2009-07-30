<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$float = JFactory::getLanguage()->isRTL() ? 'right' : 'left';
?>

<div style="float: <?php echo $float; ?>;">
	<div class="icon">
		<a href="<?php echo $button['link']; ?>">
			<?php echo JHtml::_('image.site', $button['image'], $button['imagePath'], NULL, NULL, $button['text']); ?>
			<span><?php echo $button['text']; ?></span></a>
	</div>
</div>