<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
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
				<hr />
				<div class="filter-select">
					<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
					<select name="filter_client_id" class="span12 small" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', CacheHelper::getClientOptions(), 'value', 'text', $this->state->get('clientId'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="title" width="10">
							<?php echo JText::_('COM_CACHE_NUM'); ?>
						</th>
						<th width="20">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="title nowrap">
							<?php echo JHtml::_('grid.sort',  'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="center nowrap">
							<?php echo JHtml::_('grid.sort',  'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="center">
							<?php echo JHtml::_('grid.sort',  'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$i = 0;
					foreach ($this->data as $folder => $item): ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo $this->pagination->getRowOffset($i); ?>
						</td>
						<td>
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->group; ?>" onclick="Joomla.isChecked(this.checked);" />
						</td>
						<td>
							<strong><?php echo $item->group; ?></strong>
						</td>
						<td class="center">
							<?php echo $item->count; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('number.bytes', $item->size*1024); ?>
						</td>
					</tr>
					<?php $i++; endforeach; ?>
				</tbody>
			</table>
			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
		<!-- End Content -->
	</div>
</form>
