<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
?>

<div class="pager">
	<ul class="pager pagenav">
	<?php if ($displayData['link_prev']) : ?>
		<?php $direction = $lang->isRtl() ? 'right' : 'left'; ?>
		<li class="previous">
			<a href="<?php echo $displayData['link_prev']; ?>" rel="prev">
				<span class="icon-chevron-<?php echo $direction; ?>"></span> <?php echo JText::_('JPREV'); ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if ($displayData['link_next']) : ?>
		<?php $direction = $lang->isRtl() ? 'left' : 'right'; ?>
		<li class="next">
			<a href="<?php echo $displayData['link_next']; ?>" rel="next">
				<?php echo JText::_('JNEXT'); ?> <span class="icon-chevron-<?php echo $direction; ?>"></span>
			</a>
		</li>
	<?php endif; ?>
	</ul>
</div>
