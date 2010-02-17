<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
$n = count($this->items);
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search">
				<?php echo JText::_('JSearch_Filter'); ?>
			</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('COM_LANGS_SEARCH_IN_TITLE'); ?>" />

			<button type="submit">
				<?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('search').value='';this.form.submit();">
				<?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="right">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Published');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('languages.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('JGrid_Heading_Row_Number'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'COM_LANGS_HEADING_TITLE', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'COM_LANGS_HEADING_TITLE_NATIVE', 'a.title_native', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGS_HEADING_LANG_CODE', 'a.lang_code', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGS_HEADING_PUBLISHED', 'a.published', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGrid_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->lang_id); ?>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('JCommon_Edit_item');?>::<?php echo $item->title; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_languages&task=language.edit&id='.(int) $item->lang_id); ?>">
							<?php echo $item->title; ?></a></span>
				</td>
				<td align="center">
					<?php echo $item->title_native; ?>
				</td>
				<td align="center">
					<?php echo $item->lang_code; ?>
				</td>
				<td align="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'languages.');?>
				</td>
				<td align="center">
					<?php echo $item->lang_id; ?>
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
