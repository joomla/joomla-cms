<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$params		= (isset($this->state->params)) ? $this->state->params : new JObject;
?>

<form action="<?php echo JRoute::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
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
					<select name="filter_state" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_BANNERS_SEARCH_IN_TITLE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_BANNERS_SEARCH_IN_TITLE'); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="30%">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CONTACT', 'contact', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_ACTIVE', 'nbanners', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap">
							<?php echo JText::_('COM_BANNERS_HEADING_METAKEYWORDS'); ?>
						</th>
						<th width="10%">
							<?php echo JText::_('COM_BANNERS_HEADING_PURCHASETYPE'); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="8">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering	= ($listOrder == 'ordering');
					$canCreate	= $user->authorise('core.create',		'com_banners');
					$canEdit	= $user->authorise('core.edit',			'com_banners');
					$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
					$canChange	= $user->authorise('core.edit.state',	'com_banners') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="nowrap">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'clients.', $canChange);?>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_banners&task=client.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->name); ?></a>
							<?php else : ?>
									<?php echo $this->escape($item->name); ?>
							<?php endif; ?>
						</td>
						<td class="small">
							<?php echo $item->contact;?>
						</td>
						<td class="center">
							<?php echo $item->nbanners; ?>
						</td>
						<td class="small">
							<?php echo $item->metakey; ?>
						</td>
						<td class="small">
							<?php if ($item->purchase_type<0):?>
								<?php echo JText::sprintf('COM_BANNERS_DEFAULT', JText::_('COM_BANNERS_FIELD_VALUE_'.$params->get('purchase_type')));?>
							<?php else:?>
								<?php echo JText::_('COM_BANNERS_FIELD_VALUE_'.$item->purchase_type);?>
							<?php endif;?>
						</td>
						<td class="center">
							<?php echo $item->id; ?>
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
