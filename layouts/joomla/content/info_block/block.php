<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$blockPosition = $displayData['params']->get('info_block_position', 0);

?>

<dl class="article-info muted">

	<?php if ($displayData['position'] == 'above' && ($blockPosition == 0 || $blockPosition == 2)
			|| $displayData['position'] == 'below' && ($blockPosition == 1)
			) : ?>

		<dt class="article-info-term">
			<?php // TODO: implement info_block_show_title param to hide article info title ?>
			<?php if ($displayData['params']->get('info_block_show_title', 1)) : ?>
				<?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?>
			<?php endif; ?>
		</dt>

		<?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author )) : ?>
			<dd class="createdby" itemprop="author" itemscope itemtype="http://schema.org/Person">
				<?php echo JLayoutHelper::render('joomla.content.info_block.author', $displayData); ?>
			</dd>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
			<dd class="parent-category-name">
				<?php echo JLayoutHelper::render('joomla.content.info_block.parent_category', $displayData); ?>
			</dd>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_category')) : ?>
			<dd class="category-name">
				<?php echo JLayoutHelper::render('joomla.content.info_block.category', $displayData); ?>
			</dd>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_publish_date')) : ?>
			<dd class="published">
				<?php echo JLayoutHelper::render('joomla.content.info_block.publish_date', $displayData); ?>
			</dd>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ($displayData['position'] == 'above' && ($blockPosition == 0)
			|| $displayData['position'] == 'below' && ($blockPosition == 1 || $blockPosition == 2)
			) : ?>
		<?php if ($displayData['params']->get('show_create_date')) : ?>
			<dd class="create">
				<?php echo JLayoutHelper::render('joomla.content.info_block.create_date', $displayData); ?>
			</dd>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_modify_date')) : ?>
			<dd class="modified">
				<?php echo JLayoutHelper::render('joomla.content.info_block.modify_date', $displayData); ?>
			</dd>
		<?php endif; ?>

		<?php if ($displayData['params']->get('show_hits')) : ?>
			<dd class="hits">
				<?php echo JLayoutHelper::render('joomla.content.info_block.hits', $displayData); ?>
			</dd>
		<?php endif; ?>
	<?php endif; ?>

</dl>
