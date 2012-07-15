<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state',	'com_plugins');
$saveOrder	= $listOrder == 'ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_plugins&view=plugins'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div id="sidebar" class="span2">
		<div class="sidebar-nav">
			<?php
				// Display the submenu position modules
				$this->modules = JModuleHelper::getModules('submenu');
				foreach ($this->modules as $module) {
					$output = JModuleHelper::renderModule($module);
					$params = new JRegistry;
					$params->loadString($module->params);
					echo $output;
				}
			?>
			<div class="filter-select">
				<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
				<select name="filter_state" class="span12 small" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
					<?php echo JHtml::_('select.options', PluginsHelper::stateOptions(), 'value', 'text', $this->state->get('filter.state'), true);?>
				</select>
				<hr class="hr-condensed" />
				<select name="filter_folder" class="span12 small" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_PLUGINS_OPTION_FOLDER');?></option>
					<?php echo JHtml::_('select.options', PluginsHelper::folderOptions(), 'value', 'text', $this->state->get('filter.folder'));?>
				</select>
				<hr class="hr-condensed" />
				<select name="filter_access" class="span12 small" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
				</select>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_PLUGINS_SEARCH_IN_TITLE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_PLUGINS_SEARCH_IN_TITLE'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn tip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'COM_PLUGINS_NAME_HEADING', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="18%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'ordering', $listDirn, $listOrder); ?>
						<?php if ($canOrder && $saveOrder) :?>
							<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'plugins.saveorder'); ?>
						<?php endif; ?>
					</th>

					<th class="nowrap" width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_PLUGINS_FOLDER_HEADING', 'folder', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_PLUGINS_ELEMENT_HEADING', 'element', $listDirn, $listOrder); ?>
					</th>
	                <th width="5%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="1%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="12">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering	= ($listOrder == 'ordering');
				$canEdit	= $user->authorise('core.edit',			'com_plugins');
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
				$canChange	= $user->authorise('core.edit.state',	'com_plugins') && $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->extension_id); ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'plugins.', $canChange); ?>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'plugins.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id='.(int) $item->extension_id); ?>">
								<?php echo $item->name; ?></a>
						<?php else : ?>
								<?php echo $item->name; ?>
						<?php endif; ?>
					</td>
					<td class="order nowrap">
						<?php if ($canChange) : ?>
							<div class="input-prepend">
								<?php if ($saveOrder) :?>
									<?php if ($listDirn == 'asc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i-1]->folder == $item->folder), 'plugins.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span><span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i+1]->folder == $item->folder), 'plugins.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php elseif ($listDirn == 'desc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i-1]->folder == $item->folder), 'plugins.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span><span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i+1]->folder == $item->folder), 'plugins.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
								<?php if(!$disabled = $saveOrder) : echo "<span class=\"add-on tip\" title=\"".JText::_('JDISABLED')."\"><i class=\"icon-ban-circle\"></i></span>"; endif;?><input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="width-20 text-area-order" />
							</div>
						<?php else : ?>
							<?php echo $item->ordering; ?>
						<?php endif; ?>
					</td>

					<td class="nowrap small">
						<?php echo $this->escape($item->folder);?>
					</td>
					<td class="nowrap small">
						<?php echo $this->escape($item->element);?>
					</td>
					<td class="small">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="center">
						<?php echo (int) $item->extension_id;?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		</div>
		<!-- End Content -->
	</div>
</form>
