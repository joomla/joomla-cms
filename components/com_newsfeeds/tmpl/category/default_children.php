<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\Component\Newsfeeds\Site\Helper\Route as NewsfeedsHelperRoute;

defined('_JEXEC') or die;

?>
<?php $class = ' class="first"'; ?>
<?php if ($this->maxLevel != 0 && count($this->children[$this->category->id]) > 0) : ?>
	<ul>
		<?php foreach ($this->children[$this->category->id] as $id => $child) : ?>
			<?php if ($this->params->get('show_empty_categories') || $child->numitems || count($child->getChildren())) : ?>
				<?php if (!isset($this->children[$this->category->id][$id + 1])) : ?>
					<?php $class = ' class="last"'; ?>
				<?php endif; ?>
				<li<?php echo $class; ?>>
					<?php $class = ''; ?>
					<span class="item-title">
						<a href="<?php echo JRoute::_(NewsfeedsHelperRoute::getCategoryRoute($child->id)); ?>">
							<?php echo $this->escape($child->title); ?>
						</a>
					</span>
					<?php if ($this->params->get('show_subcat_desc') == 1) : ?>
						<?php if ($child->description) : ?>
							<div class="category-desc">
								<?php echo JHtml::_('content.prepare', $child->description, '', 'com_newsfeeds.category'); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ($this->params->get('show_cat_items') == 1) : ?>
						<dl class="newsfeed-count">
							<dt>
								<?php echo JText::_('COM_NEWSFEEDS_CAT_NUM'); ?>
							</dt>
							<dd>
								<?php echo $child->numitems; ?>
							</dd>
						</dl>
					<?php endif; ?>
					<?php if (count($child->getChildren()) > 0) : ?>
						<?php $this->children[$child->id] = $child->getChildren(); ?>
						<?php $this->category = $child; ?>
						<?php $this->maxLevel--; ?>
						<?php echo $this->loadTemplate('children'); ?>
						<?php $this->category = $child->getParent(); ?>
						<?php $this->maxLevel++; ?>
					<?php endif; ?>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif;
