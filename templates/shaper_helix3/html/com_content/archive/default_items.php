<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$params = $this->params;
$useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
			|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category'));
$tpl_params 	= JFactory::getApplication()->getTemplate(true)->params;
?>

<div id="archive-items">
	<?php foreach ($this->items as $i => $item) : ?>
		<?php $info = $item->params->get('info_block_position', 0); ?>
		<div class="row<?php echo $i % 2; ?>" itemscope itemtype="http://schema.org/Article">

			<div class="entry-header">
				<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
					<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
				<?php endif; ?>
				<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>
			</div>

			<?php if (!$params->get('show_intro')) : ?>
				<?php echo $item->event->afterDisplayTitle; ?>
			<?php endif; ?>
			<?php echo $item->event->beforeDisplayContent; ?>
			<?php if ($params->get('show_intro')) :?>
				<div class="intro" itemprop="articleBody"> <?php echo JHtml::_('string.truncateComplex', $item->introtext, $params->get('introtext_limit')); ?> </div>
			<?php endif; ?>

			<?php if ($useDefList && ($info == 1 || $info == 2)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
			<?php endif; ?>

		<?php echo $item->event->afterDisplayContent; ?>
	</div>
	<?php endforeach; ?>
</div>
<div class="pagination">
	<p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
