<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Contact\Site\Helper\Route as ContactHelperRoute;

$class = 'first';
if ($this->maxLevelcat != 0 && count($this->items[$this->parent->id]) > 0) :
?>
	<?php foreach ($this->items[$this->parent->id] as $id => $item) : ?>
		<?php
		if ($this->params->get('show_empty_categories_cat') || $item->numitems || count($item->getChildren())) :
			if (!isset($this->items[$this->parent->id][$id + 1]))
			{
				$class = 'last';
			}
			?>
			<div class="com-contact-categories__items <?php echo $class; ?>">
			<?php $class = ''; ?>
				<h3 class="page-header item-title">
					<a href="<?php echo JRoute::_(ContactHelperRoute::getCategoryRoute($item->id, $item->language)); ?>">
					<?php echo $this->escape($item->title); ?></a>
					<?php if ($this->params->get('show_cat_items_cat') == 1) :?>
						<span class="badge badge-info tip hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_CONTACT_NUM_ITEMS'); ?>">
							<?php echo JText::_('COM_CONTACT_NUM_ITEMS'); ?>&nbsp;
							<?php echo $item->numitems; ?>
						</span>
					<?php endif; ?>
					<?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
						<a id="category-btn-<?php echo $item->id; ?>" href="#category-<?php echo $item->id; ?>"
							data-toggle="collapse" data-toggle="button" class="btn btn-xs float-right" aria-label="<?php echo JText::_('JGLOBAL_EXPAND_CATEGORIES'); ?>"><span class="icon-plus" aria-hidden="true"></span></a>
					<?php endif; ?>
				</h3>
				<?php if ($this->params->get('show_subcat_desc_cat') == 1) : ?>
					<?php if ($item->description) : ?>
						<div class="category-desc">
							<?php echo JHtml::_('content.prepare', $item->description, '', 'com_contact.categories'); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ($this->maxLevelcat > 1 && count($item->getChildren()) > 0) : ?>
					<div class="collapse fade" id="category-<?php echo $item->id; ?>">
						<?php
						$this->items[$item->id] = $item->getChildren();
						$this->parent = $item;
						$this->maxLevelcat--;
						echo $this->loadTemplate('items');
						$this->parent = $item->getParent();
						$this->maxLevelcat++;
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?><?php endif; ?>
