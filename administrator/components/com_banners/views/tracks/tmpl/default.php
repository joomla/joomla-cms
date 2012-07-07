<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal', 'a.modal');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=tracks'); ?>" method="post" name="adminForm" id="adminForm">
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
						<option value=""><?php echo JText::_('COM_BANNERS_SELECT_CLIENT');?></option>
						<?php echo JHtml::_('select.options', BannersHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
					</select>
					<hr class="hr-condensed" />
					<?php $category = $this->state->get('filter.category_id');?>
					<select name="filter_category_id" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_banners'), 'value', 'text', $category);?>
					</select>
					<hr class="hr-condensed" />
			        <select name="filter_type" class="span12 small" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '0', JText::_('COM_BANNERS_SELECT_TYPE')), JHtml::_('select.option', 1, JText::_('COM_BANNERS_IMPRESSION')), JHtml::_('select.option', 2, JText::_('COM_BANNERS_CLICK'))), 'value', 'text', $this->state->get('filter.type'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label class="filter-hide-lbl" for="filter_begin"><?php echo JText::_('COM_BANNERS_BEGIN_LABEL'); ?></label>
					<?php echo JHtml::_('calendar', $this->state->get('filter.begin'), 'filter_begin', 'filter_begin', '%Y-%m-%d' , array('size'=>10, 'onchange'=>"this.form.fireEvent('submit');this.form.submit()"));?>
				</div>
				<div class="filter-search btn-group pull-left">
					<label class="filter-hide-lbl" for="filter_end"><?php echo JText::_('COM_BANNERS_END_LABEL'); ?></label>
					<?php echo JHtml::_('calendar', $this->state->get('filter.end'), 'filter_end', 'filter_end', '%Y-%m-%d' , array('size'=>10, 'onchange'=>"this.form.fireEvent('submit');this.form.submit()"));?>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_NAME', 'b.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'cl.name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_TYPE', 'track_type', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_COUNT', 'count', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'JDATE', 'track_date', $listDirn, $listOrder); ?>
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
				<?php foreach ($this->items as $i => $item) :?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php echo $item->name;?>
							<div class="small">
								<?php echo $item->category_title;?>
							</div>
						</td>
						<td>
							<?php echo $item->client_name;?>
						</td>
						<td class="small">
							<?php echo $item->track_type==1 ? JText::_('COM_BANNERS_IMPRESSION'): JText::_('COM_BANNERS_CLICK');?>
						</td>
						<td>
							<?php echo $item->count;?>
						</td>
						<td>
							<?php echo JHtml::_('date', $item->track_date, JText::_('DATE_FORMAT_LC4').' H:i');?>
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
