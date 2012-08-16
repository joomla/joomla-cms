<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=debuguser&user_id='.(int) $this->state->get('filter.user_id'));?>" method="post" name="adminForm" id="adminForm">
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
					<select name="filter_component" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_USERS_OPTION_SELECT_COMPONENT');?></option>
						<?php if (!empty($this->components)) {
							echo JHtml::_('select.options', $this->components, 'value', 'text', $this->state->get('filter.component'));
						}?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_level_start" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_USERS_OPTION_SELECT_LEVEL_START');?></option>
						<?php echo JHtml::_('select.options', $this->levels, 'value', 'text', $this->state->get('filter.level_start'));?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_level_end" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_USERS_OPTION_SELECT_LEVEL_END');?></option>
						<?php echo JHtml::_('select.options', $this->levels, 'value', 'text', $this->state->get('filter.level_end'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_USERS_SEARCH_ASSETS'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_ASSETS'); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn tip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button type="button" class="btn tip" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_RESET'); ?>"><i class="icon-remove"></i></button>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="left">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ASSET_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class="left">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ASSET_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<?php foreach ($this->actions as $key => $action) : ?>
						<th width="5%">
							<span class="hasTip" title="<?php echo htmlspecialchars(JText::_($key) . '::' . JText::_($action[1]), ENT_COMPAT, 'UTF-8'); ?>"><?php echo JText::_($key); ?></span>
						</th>
						<?php endforeach; ?>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_LFT', 'a.lft', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="3%">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
					<tr class="row1">
						<td colspan="15">
							<div>
								<?php echo JText::_('COM_USERS_DEBUG_LEGEND'); ?>
								<span class="btn disabled btn-micro btn-warning"><i class="icon-white icon-ban-circle"></i></span> <?php echo JText::_('COM_USERS_DEBUG_IMPLICIT_DENY');?>
								<span class="btn disabled btn-micro btn-success"><i class="icon-white icon-ok"></i></span> <?php echo JText::_('COM_USERS_DEBUG_EXPLICIT_ALLOW');?>
								<span class="btn disabled btn-micro btn-danger"><i class="icon-white icon-remove"></i></span> <?php echo JText::_('COM_USERS_DEBUG_EXPLICIT_DENY');?>
							</div>
						</td>
					</tr>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row0">
							<td>
								<?php echo $this->escape($item->title); ?>
							</td>
							<td class="nowrap">
								<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level) ?>
								<?php echo $this->escape($item->name); ?>
							</td>
							<?php foreach ($this->actions as $action) : ?>
								<?php
								$name	= $action[0];
								$check	= $item->checks[$name];
								if ($check === true) :
									$class  = 'icon-ok';
									$button = 'btn-success';
								elseif ($check === false) :
									$class	= 'icon-remove';
									$button = 'btn-danger';
								elseif ($check === null) :
									$class  = 'icon-ban-circle';
									$button = 'btn-warning';
								else :
									$class  = '';
									$button = '';
								endif;
								?>
							<td class="center">
								<span class="btn disabled btn-micro <?php echo $button; ?>">
									<i class="icon-white <?php echo $class; ?>"></i>
								</span>
							</td>
							<?php endforeach; ?>
							<td class="center">
								<?php echo (int) $item->lft; ?>
								- <?php echo (int) $item->rgt; ?>
							</td>
							<td class="center">
								<?php echo (int) $item->id; ?>
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
