<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$n = count($this->items);

// build tag options
$tagOptions = array();
$tagOptions[] = JHtml::_('select.option', 'keywords', 'JOption_Keywords_Only' );
$tagOptions[] = JHtml::_('select.option', 'tags', 'JOption_Keyword_Tags_Only' );
$tagOptions[] = JHtml::_('select.option', 'all', 'JOption_Keywords_All' );
?>
<p>
<?php echo JText::_('KEYWORDS_REPAIR_DESC')?>
</p>
<p>
<?php echo JText::_('KEYWORDS_REBUILD_DESC')?>
</p>
<p>
<?php echo JText::_('KEYWORDS_PLUGIN_DESC')?>
</p>
<form action="<?php echo JRoute::_('index.php?option=com_content&view=keywords');?>" method="post" name="adminForm">
	<fieldset class="filter clearfix">
		<div class="left">
			<label for="search">
				<?php echo JText::_('JSearch_Filter_Label'); ?>
			</label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Content_Filter_Search_Desc'); ?>" />

			<button type="submit">
				<?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="$('filter_search').value='';this.form.submit();">
				<?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>

		<div class="right">
			<select name="filter_tags" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Tags');?></option>
				<?php echo JHtml::_('select.options', $tagOptions, 'value', 'text', $this->state->get('filter.tags'), true);?>
			</select>			
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Published');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Category');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_content'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'Keyword', 'm.keyword', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'Total Articles', 'total_articles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'Published', 'published_articles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'Unpublished', 'unpublished_articles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'Archived', 'archived_articles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'Trashed', 'trashed_articles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($this->state->get('list.ordering') == 'm.keyword');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td align="left">
					<?php echo $item->keyword; ?>
				</td>
				<td align="center">
					<?php echo $item->total_articles; ?>
				</td>
				<td align="center">
					<?php echo $item->published_articles; ?>
				</td>
				<td align="center">
					<?php echo $item->unpublished_articles; ?>
				</td>
				<td align="center">
					<?php echo $item->archived_articles; ?>
				</td>
				<td align="center">
					<?php echo $item->trashed_articles; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
