<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<ul>
	<li>
		<?php if ($links['previous']) : ?>
		<a href="<?php echo $links['previous']; ?>">
			<?php echo trim(str_repeat(Text::_('JGLOBAL_LT'), 2) . ' ' . Text::_('JPREV')); ?>
		</a>
		<?php else: ?>
		<?php echo Text::_('JPREV'); ?>
		<?php endif; ?>
	</li>
	<li>
		<?php if ($links['next']) : ?>
		<a href="<?php echo $links['next']; ?>">
			<?php echo trim(Text::_('JNEXT') . ' ' . str_repeat(Text::_('JGLOBAL_GT'), 2)); ?>
		</a>
		<?php else: ?>
		<?php echo Text::_('JNEXT'); ?>
		<?php endif; ?>
	</li>
</ul>
