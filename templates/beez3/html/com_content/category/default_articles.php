<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();


JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');

$n = count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<?php if (empty($this->items)) : ?>

	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>

<?php else : ?>

<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('filter_field') != 'hide') : ?>
	<fieldset class="filters">
		<legend class="element-invisible">
			<?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?>
		</legend>

		<div class="filter-search">
			<label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTENT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
			<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('filter_field') != 'hide') :?>
	</fieldset>
	<?php endif; ?>

	<table class="category">
		<?php if ($this->params->get('show_headings')) :?>
		<thead>
			<tr>

				<th class="list-title" id="tableOrdering">
					<?php echo JHtml::_('grid.sort', 'COM_CONTENT_HEADING_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>

				<?php if ($date = $this->params->get('list_show_date')) : ?>
				<th class="list-date" id="tableOrdering2">
					<?php if ($date == "created") : ?>
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
					<?php elseif ($date == "modified") : ?>
						<?php echo JHtml::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.modified', $listDirn, $listOrder); ?>
					<?php elseif ($date == "published") : ?>
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
			</tr>
		</thead>
		<?php endif; ?>

		<tbody>

			<?php foreach ($this->items as $i => &$article) : ?>
			<tr class="cat-list-row<?php echo $i % 2; ?>">

				<?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>

					<td class="list-title">
						<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid)); ?>">
							<?php echo $this->escape($article->title); ?></a>
					</td>

					<?php if ($this->params->get('list_show_date')) : ?>
					<td class="list-date">
						<?php
						echo JHtml::_(
							'date', $article->displayDate, $this->escape(
								$this->params->get('date_format', JText::_('DATE_FORMAT_LC3'))
							)
						); ?>
					</td>
					<?php endif; ?>

					<?php if ($this->params->get('list_show_author', 1)) : ?>
					<td class="list-author">
						<?php if(!empty($article->author) || !empty($article->created_by_alias)) : ?>
							<?php $author = $article->author ?>
							<?php $author = ($article->created_by_alias ? $article->created_by_alias : $author);?>

							<?php if (!empty($article->contactid ) &&  $this->params->get('link_author') == true):?>
								<?php echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_contact&view=contact&id='.$article->contactid),
										$author
								); ?>

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

				<?php else : ?>
				<td>
					<?php
						echo $this->escape($article->title).' : ';
						$menu		= JFactory::getApplication()->getMenu();
						$active		= $menu->getActive();
						$itemId		= $active->id;
						$link = JRoute::_('index.php?option=com_users&view=login&Itemid='.$itemId);
						$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug));
						$fullURL = new JURI($link);
						$fullURL->setVar('return', base64_encode($returnURL));
					?>
					<a href="<?php echo $fullURL; ?>" class="register">
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
	<?php echo JHtml::_('icon.create', $this->category, $this->category->params); ?>
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
		<!-- @TODO add hidden inputs -->
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
	</div>
</form>
<?php endif; ?>
