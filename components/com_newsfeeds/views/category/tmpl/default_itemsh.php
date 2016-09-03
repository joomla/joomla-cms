<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$n         = count($this->items);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>

<?php if (empty($this->items)) : ?>
	<p><?php echo JText::_('COM_NEWSFEEDS_NO_ARTICLES'); ?></p>
<?php else : ?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString(), ENT_COMPAT, 'UTF-8'); ?>" method="post" name="adminForm" id="adminForm">
	<?php 
	  if (($this->params->get('filter_field') != 'hide') || $this->params->get('show_pagination_limit')) :
	?>
	<fieldset class="filters btn-toolbar">
		<?php if (($this->params->get('filter_field') != 'hide') && ($this->params->get('filter_field') == '1')) :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_NEWSFEEDS_FILTER_LABEL') . '&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_NEWSFEEDS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_NEWSFEEDS_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>

	<table class="category table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th id="categorylist_header_title">
					<?php echo JText::_('JGLOBAL_TITLE'); ?>
				</th>
				<?php if ($this->params->get('show_articles')) : ?>
					<th id="categorylist_header_hits">
						<?php echo JText::_('COM_NEWSFEEDS_NUM_ARTICLES'); ?>
					</th>
				<?php endif; ?>
			</tr>
		</thead>
 
		<?php foreach ($this->items as $i => $item) : ?>
			<?php if ($this->items[$i]->published == 0) : ?>
					<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
			<?php else: ?>
					<tr class="cat-list-row<?php echo $i % 2; ?>" >
			<?php endif; ?>
			<td class="list-title">
				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<a href="<?php echo JRoute::_(NewsFeedsHelperRoute::getNewsfeedRoute($item->slug, $item->catid, $item->language)); ?>">
						<?php echo $this->escape($item->name); ?>
					</a>
				<?php else: ?>
					<?php
					echo $this->escape($item->name) . ' : ';
					$menu   = JFactory::getApplication()->getMenu();
					$active = $menu->getActive();
					$itemId = $active->id;
					$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
					$link->setVar('return', base64_encode(NewsFeedsHelperRoute::getNewsfeedRoute($item->slug, $item->catid, $item->language)));
					?>
					<a href="<?php echo $link; ?>" class="register">
						<?php echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?>
					</a>
				<?php endif; ?>
			<?php if ($this->params->get('show_articles', 1)) : ?>
				<td class="list-hits">
					<span class="badge badge-info">
						<?php echo JText::sprintf('COM_NEWSFEEDS_NUM_ARTICLES_COUNT', $item->numarticles); ?>
					</span>
				</td>
			<?php endif; ?>
			</tr>

			<?php  if ($this->params->get('show_link')) : ?>
				<tr>
					<td colspan=2>
						<?php $link = JStringPunycode::urlToUTF8($item->link); ?>
						<span class="list pull-left">
							<a href="<?php echo $item->link; ?>"><?php echo $link; ?></a>
						</span>
					</td>
				</tr>
			<?php  endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>

		<?php // Add pagination links ?>
		<?php if (!empty($this->items)) : ?>
			<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
				<div class="pagination">
					<?php if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="counter pull-right">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif; ?>
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
		<?php  endif; ?>
	</form>
<?php endif; ?>
