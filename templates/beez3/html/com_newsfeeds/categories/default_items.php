<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$class = ' class="first"';
if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0) :
?>
<ul>
<?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
	<?php
	if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
	if (!isset($this->items[$this->parent->id][$id + 1]))
	{
		$class = ' class="last"';
	}
	?>
	<li<?php echo $class; ?>>
	<?php $class = ''; ?>
		<span class="item-title"><a href="<?php echo JRoute::_(NewsfeedsHelperRoute::getCategoryRoute($item->id));?>">
			<?php echo $this->escape($item->title); ?></a>
		</span>
		<?php if ($this->params->get('show_subcat_desc_cat') == 1) :?>
		<?php if ($item->description) : ?>
			<div class="category-desc">
				<?php echo JHtml::_('content.prepare', $item->description, '', 'com_newsfeeds.categories'); ?>
			</div>
		<?php endif; ?>
        <?php endif; ?>
		<?php if ($this->params->get('show_cat_items_cat') == 1) :?>
			<dl class="newsfeed-count"><dt>
				<?php echo JText::_('COM_NEWSFEEDS_CAT_NUM'); ?></dt>
				<dd><?php echo $item->numitems; ?></dd>
			</dl>
		<?php endif; ?>

		<?php if (count($item->getChildren()) > 0) :
			$this->items[$item->id] = $item->getChildren();
			$this->parent = $item;
			$this->maxLevelcat--;
			echo $this->loadTemplate('items');
			$this->parent = $item->getParent();
			$this->maxLevelcat++;
		endif; ?>

	</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>
