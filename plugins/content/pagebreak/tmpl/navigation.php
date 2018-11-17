<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul>
	<li>
		<?php if ($links['previous']) : ?>
		<a href="<?php echo $links['previous']; ?>">
			<?php echo trim(str_repeat(JText::_('JGLOBAL_LT'), 2) . ' ' . JText::_('JPREV')); ?>
		</a>
		<?php else: ?>
		<?php echo JText::_('JPREV'); ?>
		<?php endif; ?>
	</li>
	<li>
		<?php if ($links['next']) : ?>
		<a href="<?php echo $links['next']; ?>">
			<?php echo trim(JText::_('JNEXT') . ' ' . str_repeat(JText::_('JGLOBAL_GT'), 2)); ?>
		</a>
		<?php else: ?>
		<?php echo JText::_('JNEXT'); ?>
		<?php endif; ?>
	</li>
</ul>
