<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');

$client		= $this->state->get('filter.client_id') ? 'administrator' : 'site';
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$canOrder	= $user->authorise('core.edit.state', 'com_modules');
$saveOrder	= $listOrder == 'ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_modules&task=modules.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_modules'); ?>" method="post" name="adminForm" id="adminForm">
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
				<ul id="submenu" class="nav nav-list">
					<li class="<?php if(($this->state->get('filter.client_id')) == 0): echo "active"; endif;?>">
						<a href="index.php?option=com_modules&filter_client_id=0">
							<?php echo JText::_('JSITE'); ?>
						</a>
					</li>
					<li class="<?php if(($this->state->get('filter.client_id')) == 1): echo "active"; endif;?>">
						<a href="index.php?option=com_modules&filter_client_id=1">
							<?php echo JText::_('JADMINISTRATOR'); ?>
						</a>
					</li>
				</ul>
				<hr />
				<div class="filter-select hidden-phone">
					<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
					<label for="filter_client_id" class="element-invisible"><?php echo JText::_('JSITE');?></label>
					<select name="filter_client_id" id="filter_client_id" class="span12 small" onchange="this.form.submit()">
						<?php echo JHtml::_('select.options', ModulesHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
					</select>
					<hr class="hr-condensed" />
					<label for="filter_state" class="element-invisible"><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></label>
		             <select name="filter_state" id="filter_state" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo JHtml::_('select.options', ModulesHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
					</select>
					<hr class="hr-condensed" />
					<label for="filter_position" class="element-invisible"><?php echo JText::_('COM_MODULES_OPTION_SELECT_POSITION');?></label>
					<select name="filter_position" id="filter_position" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_POSITION');?></option>
						<?php echo JHtml::_('select.options', ModulesHelper::getPositions($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.position'));?>
					</select>
					<hr class="hr-condensed" />
					<label for="filter_module" class="element-invisible"><?php echo JText::_('COM_MODULES_OPTION_SELECT_MODULE');?></label>
		            <select name="filter_module" id="filter_module" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_MODULE');?></option>
						<?php echo JHtml::_('select.options', ModulesHelper::getModules($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.module'));?>
					</select>
					<hr class="hr-condensed" />
					<label for="filter_access" class="element-invisible"><?php echo JText::_('JOPTION_SELECT_ACCESS');?></label>
					<select name="filter_access" id="filter_access" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
					</select>
					<hr class="hr-condensed" />
					<label for="filter_language" class="element-invisible"><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></label>
					<select name="filter_language" id="filter_language" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_BANNERS_SEARCH_IN_TITLE');?></label>
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" />
				</div>
				<div class="btn-group pull-left hidden-phone">
					<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="btn-group pull-right hidden-phone">
					<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
					<select name="directionTable" id="directionTable" class="input-small" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
						<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
						<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
					</select>
				</div>
				<div class="btn-group pull-right">
					<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
					<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
					</select>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped" id="articleList">
				<thead>
					<tr>
						<th width="1%" class="center hidden-phone" nowrap="nowrap">
							<i class="icon-menu-2 hasTip" title="<?php echo JText::_('JGRID_HEADING_ORDERING'); ?>"></i>
						</th>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th width="5%" class="center">
							<?php echo JText::_('JSTATUS'); ?>
						</th>
						<th class="title">
							<?php echo JText::_('JGLOBAL_TITLE'); ?>
						</th>
						<th width="15%" class="left hidden-phone">
							<?php echo JText::_('COM_MODULES_HEADING_POSITION'); ?>
						</th>
						<th width="10%" class="left hidden-phone" >
							<?php echo JText::_('COM_MODULES_HEADING_MODULE'); ?>
						</th>
						<th width="10%" class="visible-desktop">
							<?php echo JText::_('COM_MODULES_HEADING_PAGES'); ?>
						</th>
						<th width="10%" class="hidden-phone">
							<?php echo JText::_('JGRID_HEADING_ACCESS'); ?>
						</th>
						<th width="5%" class="hidden-phone">
							<?php echo JText::_('JGRID_HEADING_LANGUAGE'); ?>
						</th>
						<th width="1%" class="nowrap visible-desktop">
							<?php echo JText::_('JGRID_HEADING_ID'); ?>
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
					$ordering   = ($listOrder == 'ordering');
					$canCreate  = $user->authorise('core.create',     'com_modules');
					$canEdit    = $user->authorise('core.edit',       'com_modules');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_modules') && $canCheckin;
				?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->position?>">
						<td class="order nowrap center hidden-phone">
						<?php if ($canChange) :
							$disableClassName = '';
							$disabledLabel	  = '';
							if (!$saveOrder) :
								$disabledLabel    = JText::_('JORDERINGDISABLED');
								$disableClassName = 'inactive tip-top';
							endif; ?>
							<span class="sortable-handler <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>" rel="tooltip">
								<i class="icon-menu"></i>
							</span>
							<input type="text" style="display:none"  name="order[]" size="5"
							value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
						<?php else : ?>
							<span class="sortable-handler inactive" >
								<i class="icon-menu"></i>
							</span>
						<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('modules.state', $item->published, $i, $canChange, 'cb'); ?>
						</td>
						<td class="has-context">
							<div class="pull-left">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'modules.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
										<?php echo $this->escape($item->title); ?>
								<?php endif; ?>

								<?php if (!empty($item->note)) : ?>
									<div class="small">
										<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note));?>
									</div>
								<?php endif; ?>
							</div>
							<div class="pull-left">
								<?php
									// Create dropdown items
									JHtml::_('dropdown.edit', $item->id, 'module.');
									JHtml::_('dropdown.divider');
									if ($item->published) :
										JHtml::_('dropdown.unpublish', 'cb' . $i, 'modules.');
									else :
										JHtml::_('dropdown.publish', 'cb' . $i, 'modules.');
									endif;

									JHtml::_('dropdown.divider');

									if ($item->checked_out) :
										JHtml::_('dropdown.checkin', 'cb' . $i, 'modules.');
									endif;

									if ($trashed) :
										JHtml::_('dropdown.untrash', 'cb' . $i, 'modules.');
									else :
										JHtml::_('dropdown.trash', 'cb' . $i, 'modules.');
									endif;

									// render dropdown list
									echo JHtml::_('dropdown.render');
									?>
							</div>
						</td>
						<td class="small hidden-phone">
							<?php if ($item->position) : ?>
								<span class="label label-info">
									<?php echo $item->position; ?>
								</span>
							<?php else : ?>
								<span class="label">
									<?php echo JText::_('JNONE'); ?>
								</span>
							<?php endif; ?>
						</td>
		                <td class="small hidden-phone">
							<?php echo $item->name;?>
						</td>
						<td class="small visible-desktop">
							<?php echo $item->pages; ?>
						</td>

						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small hidden-phone">
							<?php if ($item->language == ''):?>
								<?php echo JText::_('JDEFAULT'); ?>
							<?php elseif ($item->language == '*'):?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else:?>
								<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
						</td>
						<td class="center visible-desktop">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php //Load the batch processing form. ?>
			<?php echo $this->loadTemplate('batch'); ?>

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
