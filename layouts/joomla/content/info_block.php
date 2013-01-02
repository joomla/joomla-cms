<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$params  = $displayData->params;
$info    = $params->get('info_block_position', 0);
?>

		<div class="article-info muted">
			<dl class="article-info">
			<dt class="article-info-term"><?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?></dt>
			<?php if (!($info == 2 && $displayData->location == 'bottom')): ?>

				<?php if ($params->get('show_author') && !empty($displayData->author )) : ?>
					<dd class="createdby">
						<?php $author = $displayData->created_by_alias ? $displayData->created_by_alias : $displayData->author; ?>
						<?php if (!empty($displayData->contactid) && $params->get('link_author') == true) : ?>
							<?php
							$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
							$menu = JFactory::getApplication()->getMenu();
							$item = $menu->getItems('link', $needle, true);
							$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
							?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', JRoute::_($cntlink), $author)); ?>
						<?php else: ?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
						<?php endif; ?>
					</dd>
				<?php endif; ?>
				<?php if ($params->get('show_parent_category') && !empty($displayData->parent_slug)) : ?>
					<dd class="parent-category-name">
						<?php $title = $this->escape($displayData->parent_title);
						$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($displayData->parent_slug)).'">'.$title.'</a>';?>
						<?php if ($params->get('link_parent_category') && !empty($displayData->parent_slug)) : ?>
							<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
						<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
						<?php endif; ?>
					</dd>
				<?php endif; ?>
				<?php if ($params->get('show_category')) : ?>
					<dd class="category-name">
						<?php $title = $this->escape($displayData->category_title);
						$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($displayData->catslug)) . '">' . $title . '</a>';?>
						<?php if ($params->get('link_category') && $displayData->catslug) : ?>
							<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
						<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
						<?php endif; ?>
					</dd>
				<?php endif; ?>

				<?php if ($params->get('show_publish_date')) : ?>
					<dd class="published">
						<span class="icon-calendar"></span> <?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', JHtml::_('date', $displayData->publish_up, JText::_('DATE_FORMAT_LC3'))); ?>
					</dd>
				<?php endif; ?>
			<?php endif; ?>


			<?php if ($info == 0 && $displayData->location == 'top' || ($info ==1 && $displayData->location == 'bottom')  || ($info == 2 && $displayData->location == 'bottom')): ?>
				<?php if ($params->get('show_modify_date')) : ?>
					<dd class="modified">
						<span class="icon-calendar"></span>
							<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date', $displayData->modified, JText::_('DATE_FORMAT_LC3'))); ?>
					</dd>
				<?php endif; ?>
				<?php if ($params->get('show_create_date')) : ?>
					<dd class="create">
						<span class="icon-calendar"></span> <?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date', $displayData->created, JText::_('DATE_FORMAT_LC3'))); ?>
					</dd>
				<?php endif; ?>

				<?php if ($params->get('show_hits')) : ?>
					<dd class="hits">
						<span class="icon-eye-open"></span> <?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $displayData->hits); ?>
					</dd>
				<?php endif; ?>
			<?php endif; ?>
			</dl>
		</div>

