<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.framework');

$n         = count($this->items);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Check for at least one editable article
$isEditable = false;

if (!empty($this->items))
{
	foreach ($this->items as $article)
	{
		if ($article->params->get('access-edit'))
		{
			$isEditable = true;
			break;
		}
	}
}
?>

<?php if (empty($this->items)) : ?>

	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>

<?php else : ?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('filter_field') !== 'hide') : ?>
	<fieldset class="filters">
		<legend class="hidelabeltxt">
			<?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?>
		</legend>
		<div class="filter-search">
			<?php if ($this->params->get('filter_field') === 'tag') :?>
			<select name="filter_tag" id="filter_tag" onchange="document.adminForm.submit();">
				<option value=""><?php echo JText::_('JOPTION_SELECT_TAG'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tag')); ?>
			</select>
			<?php elseif ($this->params->get('filter_field') === 'month') : ?>
			<select name="filter-search" id="filter-search" onchange="document.adminForm.submit();">
				<option value=""><?php echo JText::_('JOPTION_SELECT_MONTH'); ?></option>
				<?php echo JHtml::_('select.options', JHtml::_('content.months', $this->state), 'value', 'text', $this->state->get('list.filter')); ?>
			</select>
			<?php else : ?>		
			<label class="filter-search-lbl element-invisible" for="filter-search">
				<?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?>
			</label>
			<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL'); ?>" />
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('filter_field') !== 'hide') :?>
	</fieldset>
	<?php endif; ?>

	<div class="clr"></div>

	<table class="category">
		<?php if ($this->params->get('show_headings')) : ?>
		<thead>
			<tr>
				<th class="list-title" id="tableOrdering">
					<?php echo JHtml::_('grid.sort', 'COM_CONTENT_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>

				<?php if ($date = $this->params->get('list_show_date')) : ?>
				<th class="list-date" id="tableOrdering2">
					<?php if ($date === 'created') : ?>
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
					<?php elseif ($date === 'modified') : ?>
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
					<?php elseif ($date === 'published') : ?>
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
					<?php endif; ?>
				</th>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_author', 1)) : ?>
				<th class="list-author" id="tableOrdering3">
					<?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_hits', 1)) : ?>
				<th class="list-hits" id="tableOrdering4">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
					<th id="categorylist_header_votes">
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_VOTES', 'rating_count', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
					<th id="categorylist_header_ratings">
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_RATINGS', 'rating', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($isEditable) : ?>
					<th id="categorylist_header_edit"><?php echo JText::_('COM_CONTENT_EDIT_ITEM'); ?></th>
				<?php endif; ?>
			</tr>
		</thead>
		<?php endif; ?>

		<tbody>

			<?php foreach ($this->items as $i => &$article) : ?>
			<tr class="cat-list-row<?php echo $i % 2; ?>">

				<?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>

					<td class="list-title">
						<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)); ?>">
							<?php echo $this->escape($article->title); ?></a>
					</td>

					<?php if ($this->params->get('list_show_date')) : ?>
					<td class="list-date">
						<?php
							echo JHtml::_(
									'date', $article->displayDate, $this->escape(
											$this->params->get('date_format', JText::_('DATE_FORMAT_LC3'))
									)
							);
						?>
					</td>
					<?php endif; ?>

					<?php if ($this->params->get('list_show_author', 1)) : ?>
					<td class="list-author">
						<?php if (!empty($article->author) || !empty($article->created_by_alias)) : ?>
							<?php $author = $article->created_by_alias ?: $article->author; ?>
							<?php if (!empty($article->contact_link) && $this->params->get('link_author') == true):?>
								<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', $article->contact_link, $author)); ?>
							<?php else :?>
								<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<?php endif; ?>

					<?php if ($this->params->get('list_show_hits', 1)) : ?>
					<td class="list-hits">
						<?php echo $article->hits; ?>
					</td>
					<?php endif; ?>

					<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
						<td class="list-votes">
							<?php echo $article->rating_count; ?>
						</td>
					<?php endif; ?>
					<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
						<td class="list-ratings">
							<?php echo $article->rating; ?>
						</td>
					<?php endif; ?>
					<?php if ($isEditable) : ?>
						<td class="list-edit">
							<?php if ($article->params->get('access-edit')) : ?>
								<?php echo JHtml::_('icon.edit', $article, $article->params, array(), true); ?>
							<?php endif; ?>
						</td>
					<?php endif; ?>
				<?php else : ?>
				<td>
					<?php
						echo $this->escape($article->title).' : ';
						$menu = JFactory::getApplication()->getMenu();
						$active = $menu->getActive();
						$itemId = $active->id;
						$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
						$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)));
					?>
					<a href="<?php echo $link; ?>" class="register">
					<?php echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?></a>
				</td>
				<?php endif; ?>

			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php // Code to add a link to submit an article. ?>
<?php if ($this->category->getParams()->get('access-create')) : ?>
	<?php echo JHtml::_('icon.create', $this->category, $this->category->params, array(), true); ?>
<?php  endif; ?>

<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>
	<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<div class="pagination">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			 <p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php  endif; ?>

		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
	</div>
</form>
<?php endif; ?>
