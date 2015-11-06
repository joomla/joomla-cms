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
	<?php if ($link_prev) : ?>
		<?php $direction = $lang->isRtl() ? 'right' : 'left'; ?>
		<li class="previous">
			<a href="<?php echo $link_prev; ?>" rel="prev">
				<?php echo '<span class="icon-chevron-' . $direction . '"></span>' . JText::_('JPREV'); ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if ($link_next) : ?>
		<?php $direction = $lang->isRtl() ? 'left' : 'right'; ?>
		<li class="next">
			<a href="<?php echo $link_next; ?>" rel="next">
				<?php echo JText::_('JNEXT') . '<span class="icon-chevron-' . $direction . '"></span>'; ?>
			</a>
		</li>
	<?php endif; ?>
	</ul>
</div>
