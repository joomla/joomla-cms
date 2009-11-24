<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'multiselect.js');

$user = JFactory::getUser();
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSearch_Filter_Label'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('Modules_Modules_Filter_Search_Desc'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
				<option value="*"><?php echo JText::_('Modules_Option_Select_Client'); ?></option>
				<?php echo JHtml::_('select.options', ModulesHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>

			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Published');?></option>
				<?php echo JHtml::_('select.options', ModulesHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'), true);?>
			</select>

			<select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Access');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_position" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('Modules_Option_Select_Position');?></option>
				<?php echo JHtml::_('select.options', ModulesHelper::getPositions($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.position'));?>
			</select>

			<select name="filter_module" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('Modules_Option_Select_Module');?></option>
				<?php echo JHtml::_('select.options', ModulesHelper::getModules($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.module'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist" id="modules-mgr">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Title', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="20%">
					<?php echo JHtml::_('grid.sort',  'Modules_Heading_Position', 'a.position', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'Modules_Heading_Pages', 'a.pages', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'Modules_Heading_Module', 'a.module', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Enabled', 'a.enabled', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Ordering', 'a.ordering', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'modules.saveorder'); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Access', 'a.access', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Language', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGrid_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($this->state->get('list.ordering') == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_modules');
			$canEdit	= $user->authorise('core.edit',			'com_modules');
			$canChange	= $user->authorise('core.edit.state',	'com_modules');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<?php if ($canCreate || $canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
							<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->position; ?>
				</td>
				<td class="center">
					<?php
						if (is_null($item->pages)) {
							echo JText::_('Modules_Assigned_None');
						} else if ($item->pages != 0) {
							echo JText::_('Modules_Assigned_Varies');
						} else {
							echo JText::_('Modules_Assigned_All');
						}
					?>
				</td>
				<td class="left">
					<?php echo $item->module; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'modules.', $canChange);?>
				</td>
				<td class="order">
					<?php if ($canChange) : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, true, 'modules.orderup', 'JGrid_Move_Up', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'modules.orderdown', 'JGrid_Move_Down', $ordering); ?></span>
						<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<?php echo $item->language ? $this->escape($item->language) : JText::_('JDefault'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
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
