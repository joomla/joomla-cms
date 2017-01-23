<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Create some shortcuts.
$params    = &$this->item->params;
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

// For B/C we also add the css classes inline. This will be removed in 4.0.
JFactory::getDocument()->addStyleDeclaration('
.hide { display: none; }
.table-noheader { border-collapse: collapse; }
.table-noheader thead { display: none; }
');

$tableClass = $this->params->get('show_headings') != 1 ? ' table-noheader' : '';
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
<?php if ($this->params->get('filter_field') !== 'hide' || $this->params->get('show_pagination_limit')) : ?>
	<fieldset class="filters btn-toolbar clearfix">
		<legend class="hide"><?php echo JText::_('COM_CONTENT_FORM_FILTER_LEGEND'); ?></legend>
		<?php if ($this->params->get('filter_field') !== 'hide') : ?>
			<div class="btn-group">
				<?php if ($this->params->get('filter_field') !== 'tag') : ?>
					<label class="filter-search-lbl element-invisible" for="filter-search">
						<?php echo JText::_('COM_CONTENT_' . $this->params->get('filter_field') . '_FILTER_LABEL') . '&#160;'; ?>
					</label>
					<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_CONTENT_' . $this->params->get('filter_field') . '_FILTER_LABEL'); ?>" />
				<?php else : ?>
					<select name="filter_tag" id="filter_tag" onchange="document.adminForm.submit();" >
						<option value=""><?php echo JText::_('JOPTION_SELECT_TAG'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('tag.options', true, true), 'value', 'text', $this->state->get('filter.tag')); ?>
					</select>
				<?php endif; ?>
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

		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
		<input type="hidden" name="task" value="" />
	</fieldset>

	<div class="control-group hide pull-right">
		<div class="controls">
			<button type="submit" name="filter_submit" class="btn btn-primary"><?php echo JText::_('COM_CONTENT_FORM_FILTER_SUBMIT'); ?></button>
		</div>
	</div>

<?php endif; ?>

<?php if (empty($this->items)) : ?>
	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>
<?php else : ?>

	<table class="category table table-striped table-bordered table-hover">
		<?php
		$headerTitle    = '';
		$headerDate     = '';
		$headerAuthor   = '';
		$headerHits     = '';
		$headerVotes    = '';
		$headerRatings  = '';
		$headerEdit     = '';
		?>
		<?php if ($this->params->get('show_headings')) : ?>
			<?php
			$headerTitle    = 'headers="categorylist_header_title"';
			$headerDate     = 'headers="categorylist_header_date"';
			$headerAuthor   = 'headers="categorylist_header_author"';
			$headerHits     = 'headers="categorylist_header_hits"';
			$headerVotes    = 'headers="categorylist_header_votes"';
			$headerRatings  = 'headers="categorylist_header_ratings"';
			$headerEdit     = 'headers="categorylist_header_edit"';
			?>
			<thead>
			<tr>
				<th scope="col" id="categorylist_header_title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<?php if ($date = $this->params->get('list_show_date')) : ?>
					<th scope="col" id="categorylist_header_date">
						<?php if ($date === 'created') : ?>
							<?php echo JHtml::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.created', $listDirn, $listOrder); ?>
						<?php elseif ($date === 'modified') : ?>
							<?php echo JHtml::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.modified', $listDirn, $listOrder); ?>
						<?php elseif ($date === 'published') : ?>
							<?php echo JHtml::_('grid.sort', 'COM_CONTENT_' . $date . '_DATE', 'a.publish_up', $listDirn, $listOrder); ?>
						<?php endif; ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_author')) : ?>
					<th scope="col" id="categorylist_header_author">
						<?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_hits')) : ?>
					<th scope="col" id="categorylist_header_hits">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
					<th scope="col" id="categorylist_header_votes">
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_VOTES', 'rating_count', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
					<th scope="col" id="categorylist_header_ratings">
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_RATINGS', 'rating', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
				<?php if ($isEditable) : ?>
					<th scope="col" id="categorylist_header_edit"><?php echo JText::_('COM_CONTENT_EDIT_ITEM'); ?></th>
				<?php endif; ?>
			</tr>
			</thead>
		<?php endif; ?>
		<tbody>
		<?php foreach ($this->items as $i => $article) : ?>
			<?php if ($this->items[$i]->state == 0) : ?>
				<tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
			<?php else : ?>
				<tr class="cat-list-row<?php echo $i % 2; ?>" >
			<?php endif; ?>
			<td headers="categorylist_header_title" class="list-title">
				<?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>
					<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)); ?>">
						<?php echo $this->escape($article->title); ?>
					</a>
					<?php if (JLanguageAssociations::isEnabled() && $this->params->get('show_associations')) : ?>
						<?php $associations = ContentHelperAssociation::displayAssociations($article->id); ?>
						<?php foreach ($associations as $association) : ?>
							<?php if ($this->params->get('flags', 1)) : ?>
								<?php $flag = JHtml::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
								&nbsp;<a href="<?php echo JRoute::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
							<?php else : ?>
								<?php $class = 'label label-association label-' . $association['language']->sef; ?>
								&nbsp;<a class="' . <?php echo $class; ?> . '" href="<?php echo JRoute::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>
					<?php
					echo $this->escape($article->title) . ' : ';
					$menu   = JFactory::getApplication()->getMenu();
					$active = $menu->getActive();
					$itemId = $active->id;
					$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
					$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)));
					?>
					<a href="<?php echo $link; ?>" class="register">
						<?php echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?>
					</a>
					<?php if (JLanguageAssociations::isEnabled() && $this->params->get('show_associations')) : ?>
						<?php $associations = ContentHelperAssociation::displayAssociations($article->id); ?>
						<?php foreach ($associations as $association) : ?>
							<?php if ($this->params->get('flags', 1)) : ?>
								<?php $flag = JHtml::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true); ?>
								&nbsp;<a href="<?php echo JRoute::_($association['item']); ?>"><?php echo $flag; ?></a>&nbsp;
							<?php else : ?>
								<?php $class = 'label label-association label-' . $association['language']->sef; ?>
								&nbsp;<a class="' . <?php echo $class; ?> . '" href="<?php echo JRoute::_($association['item']); ?>"><?php echo strtoupper($association['language']->sef); ?></a>&nbsp;
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($article->state == 0) : ?>
					<span class="list-published label label-warning">
								<?php echo JText::_('JUNPUBLISHED'); ?>
							</span>
				<?php endif; ?>
				<?php if (strtotime($article->publish_up) > strtotime(JFactory::getDate())) : ?>
					<span class="list-published label label-warning">
								<?php echo JText::_('JNOTPUBLISHEDYET'); ?>
							</span>
				<?php endif; ?>
				<?php if ((strtotime($article->publish_down) < strtotime(JFactory::getDate())) && $article->publish_down != JFactory::getDbo()->getNullDate()) : ?>
					<span class="list-published label label-warning">
								<?php echo JText::_('JEXPIRED'); ?>
							</span>
				<?php endif; ?>
			</td>
			<?php if ($this->params->get('list_show_date')) : ?>
				<td headers="categorylist_header_date" class="list-date small">
					<?php
					echo JHtml::_(
						'date', $article->displayDate,
						$this->escape($this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))
					); ?>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_author', 1)) : ?>
				<td headers="categorylist_header_author" class="list-author">
					<?php if (!empty($article->author) || !empty($article->created_by_alias)) : ?>
						<?php $author = $article->author ?>
						<?php $author = $article->created_by_alias ?: $author; ?>
						<?php if (!empty($article->contact_link) && $this->params->get('link_author') == true) : ?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', $article->contact_link, $author)); ?>
						<?php else : ?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
						<?php endif; ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_hits', 1)) : ?>
				<td headers="categorylist_header_hits" class="list-hits">
							<span class="badge badge-info">
								<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $article->hits); ?>
							</span>
						</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_votes', 0) && $this->vote) : ?>
				<td headers="categorylist_header_votes" class="list-votes">
					<span class="badge badge-success">
						<?php echo JText::sprintf('COM_CONTENT_VOTES_COUNT', $article->rating_count); ?>
					</span>
				</td>
			<?php endif; ?>
			<?php if ($this->params->get('list_show_ratings', 0) && $this->vote) : ?>
				<td headers="categorylist_header_ratings" class="list-ratings">
					<span class="badge badge-warning">
						<?php echo JText::sprintf('COM_CONTENT_RATINGS_COUNT', $article->rating); ?>
					</span>
				</td>
			<?php endif; ?>
			<?php if ($isEditable) : ?>
				<td headers="categorylist_header_edit" class="list-edit">
					<?php if ($article->params->get('access-edit')) : ?>
						<?php echo JHtml::_('icon.edit', $article, $params); ?>
					<?php endif; ?>
				</td>
			<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php // Code to add a link to submit an article. ?>
<?php if ($this->category->getParams()->get('access-create')) : ?>
	<?php echo JHtml::_('icon.create', $this->category, $this->category->params); ?>
<?php endif; ?>

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
<?php endif; ?>
</form>
