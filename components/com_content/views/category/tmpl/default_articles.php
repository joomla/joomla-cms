<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$n = count($this->articles);

?>
<!-- from mootools core for grid.sort -->
<script language="javascript" type="text/javascript">
	function tableOrdering(order, dir, task) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit(task);
}
</script>

<?php if (empty($this->articles)) : ?>
	<!--  no articles -->
<?php else : ?>
	<form action="<?php echo $this->action; ?>" method="post" name="adminForm">

	<?php if ($this->params->get('filter_field') != 'hide') :?>
	<fieldset class="filter">
	<legend class="element-invisible"><?php echo JText::_('JContent_Filter_Label'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('Content_'.$this->params->get('filter_field').'_Filter_Label').'&nbsp;'; ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php /* echo $this->escape($this->lists['filter']);*/ ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('Content_Filter_Search_Desc'); ?>" />
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="display">
			<?php echo JText::_('Display Num'); ?>&nbsp;
			<!-- @TODO pagination -->
			<?php // echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>
	</fieldset>

<table class="category">
	<?php if ($this->params->get('show_headings')) :?>
	<thead><tr>
		<?php if ($this->params->get('show_title')) : ?>
		<th class="item-title" id="tableOrdering">
			<?php  echo JHTML::_('grid.sort', 'Content_Heading_Title', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')) ; ?>
		</th>
		<?php endif; ?>
		<?php if ($this->params->get('show_date') != 'hide') : ?>
			<th class="item-date" id="tableOrdering2">
				<?php echo JHTML::_('grid.sort', 'Content_'.$this->params->get('show_date').'_Date', 'a.created', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
		<?php endif; ?>
		<?php if ($this->params->get('list_author')) : ?>
			<th class="item-author" id="tableOrdering3">
				<?php echo JHTML::_('grid.sort', 'Content_Author', 'a.author', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
		<?php endif; ?>
		<?php if ($this->params->get('list_hits')) : ?>
			<th class="item-hits" id="tableOrdering4">
				<?php echo JHTML::_('grid.sort', 'Content_Hits', 'a.hits', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
		<?php endif; ?>
	</tr></thead>
	<?php endif; ?>

	<tbody>
		<?php foreach ($this->articles as $i => &$article) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a href="<?php echo JRoute::_(ContentRoute::article($article->slug, $article->catslug)); ?>">
					<?php echo $this->escape($article->title); ?></a>
				</td>
				<?php if ($this->params->get('show_date') != 'hide') : ?>
					<td>
						<?php echo JHTML::_('date', $article->displayDate, $this->escape(
						$this->params->get('date_format', JText::_('DATE_FORMAT_LC3')))); ?>
					</td>
				<?php endif; ?>
				<?php if ($this->params->get('list_author')) : ?>
					<td>
						<?php echo ($article->created_by_alias ? $article->created_by_alias : $article->author_name); ?>
					</td>
				<?php endif; ?>
				<?php if ($this->params->get('list_hits')) : ?>
					<td>
						<?php echo $article->hits; ?>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
	</table>

	<?php if ($this->params->get('show_pagination')) : ?>
	 <div class="pagination">
	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
                        <p class="counter">
                                <?php // echo $this->pagination->getPagesCounter(); ?>
                        </p>
   <?php endif; ?>
			Pagination Links will be here
			<?php // echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	
	<!-- @TODO add hidden inputs -->
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
</form>
<?php endif; ?>




