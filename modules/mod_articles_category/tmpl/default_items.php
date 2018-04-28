<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php foreach ($items as $item) : ?>
	<li>
		<?php if ($params->get('link_titles', 1)) : ?>
			<a class="mod-articles-category-title<?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
				<?php echo $item->title; ?>
			</a>
		<?php else : ?>
			<?php echo $item->title; ?>
		<?php endif; ?>
		<?php if ($params->get('show_hits', 0)) : ?>
			<span class="mod-articles-category-hits">
				(<?php echo $item->hits; ?>)
			</span>
		<?php endif; ?>
		<?php if ($params->get('show_author', 0)) : ?>
			<span class="mod-articles-category-writtenby">
				<?php echo $item->displayAuthorName; ?>
			</span>
		<?php endif; ?>
		<?php if ($params->get('show_category', 0)) : ?>
			<span class="mod-articles-category-category">
				(<?php echo $item->displayCategoryTitle; ?>)
			</span>
		<?php endif; ?>
		<?php if ($params->get('show_date', 0)) : ?>
			<span class="mod-articles-category-date">
				<?php echo $item->displayDate; ?>
			</span>
		<?php endif; ?>
		<?php if ($params->get('show_tags', 0) && $item->tags->itemTags) : ?>
			<div class="mod-articles-category-tags">
				<?php echo $tagLayout->render($item->tags->itemTags); ?>
			</div>
		<?php endif; ?>
		<?php if ($params->get('show_introtext', 0)) : ?>
			<p class="mod-articles-category-introtext">
				<?php echo $item->displayIntrotext; ?>
			</p>
		<?php endif; ?>
		<?php if ($params->get('show_readmore', 0)) : ?>
			<p class="mod-articles-category-readmore">
				<a class="mod-articles-category-title<?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
					<?php if ($item->params->get('access-view') === false) : ?>
						<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
					<?php elseif (isset($item->alternative_readmore)) : ?>
						<?php echo $item->alternative_readmore; ?>
						<?php if ($params->get('show_readmore_title', 1)) : ?>
							<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit', 15)); ?>
						<?php endif; ?>
					<?php elseif (!$params->get('show_readmore_title', 1)) : ?>
						<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
					<?php else : ?>
						<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
						<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit', 15)); ?>
					<?php endif; ?>
				</a>
			</p>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
