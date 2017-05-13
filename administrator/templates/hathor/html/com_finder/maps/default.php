<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$lang      = JFactory::getLanguage();

JText::script('COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(pressbutton)
	{
		if (pressbutton == 'map.delete')
		{
			if (confirm(Joomla.JText._('COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT')))
			{
				Joomla.submitform(pressbutton);
			}
			else
			{
				return false;
			}
		}
		Joomla.submitform(pressbutton);
	}
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=maps');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::sprintf('COM_FINDER_SEARCH_LABEL', JText::_('COM_FINDER_MAPS')); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::sprintf('COM_FINDER_SEARCH_LABEL', JText::_('COM_FINDER_MAPS')); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_FINDER_FILTER_SEARCH_DESCRIPTION'); ?>" />
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select">
			<label class="selectlabel" for="filter_branch"><?php echo JText::sprintf('COM_FINDER_FILTER_BY', JText::_('COM_FINDER_MAPS')); ?></label>
			<select name="filter_branch" id="filter_branch">
				<?php echo JHtml::_('select.options', JHtml::_('finder.mapslist'), 'value', 'text', $this->state->get('filter.branch'));?>
			</select>

			<label class="selectlabel" for="filter_state"><?php echo JText::_('COM_FINDER_INDEX_FILTER_BY_STATE'); ?></label>
			<select name="filter_state" id="filter_state">
				<option value=""><?php echo JText::_('COM_FINDER_INDEX_FILTER_BY_STATE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('finder.statelist'), 'value', 'text', $this->state->get('filter.state')); ?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?>
			</button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap state-col">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap center">
					<?php echo JText::_('COM_FINDER_HEADING_NODES'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php if (count($this->items) == 0) : ?>
			<tr class="row0">
				<td class="center" colspan="5">
					<?php echo JText::_('COM_FINDER_MAPS_NO_CONTENT'); ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php $canChange = JFactory::getUser()->authorise('core.manage', 'com_finder'); ?>
		<?php foreach ($this->items as $i => $item) :?>
			<tr class="row<?php echo $i % 2; ?>">
				<th class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</th>
				<td>
					<?php if ((int) $item->num_children === 0) : ?>
						<span class="gi">&mdash;</span>
					<?php endif; ?>
					<?php
					$key = FinderHelperLanguage::branchSingular($item->title);
					$title = $lang->hasKey($key) ? JText::_($key) : $item->title;
					echo $this->escape(($title == '*') ? JText::_('JALL_LANGUAGE') : $title);
					?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'maps.', $canChange, 'cb'); ?>
				</td>
				<td class="center btns">
				<?php if ((int) $item->num_children === 0) : ?>
					<span class="badge <?php if ($item->num_nodes > 0) echo 'badge-info'; ?>"><?php echo $item->num_nodes; ?></span>
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
