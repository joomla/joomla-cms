<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

$lang = JFactory::getLanguage();
JText::script('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT');
JText::script('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT');
?>

<script type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
	if (pressbutton == 'index.purge') {
		if (confirm(Joomla.JText._('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT'))) {
			Joomla.submitform(pressbutton);
		} else {
			return false;
		}
	}
	if (pressbutton == 'index.delete') {
		if (confirm(Joomla.JText._('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT'))) {
			Joomla.submitform(pressbutton);
		} else {
			return false;
		}
	}

	Joomla.submitform(pressbutton);
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=index');?>" method="post" name="adminForm" id="adminForm">
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
						<option value=""><?php echo JText::_('COM_FINDER_INDEX_FILTER_BY_STATE');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('finder.statelist'), 'value', 'text', $this->state->get('filter.state'));?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_type" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_FINDER_INDEX_TYPE_FILTER');?></option>
						<?php echo JHtml::_('select.options', JHtml::_('finder.typeslist'), 'value', 'text', $this->state->get('filter.type'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_FINDER_FILTER_SEARCH_DESCRIPTION'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_FINDER_FILTER_SEARCH_DESCRIPTION'); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
				</div>
			</div>
			<div class="clearfix"> </div>
			<?php if (!$this->pluginState['plg_content_finder']->enabled) : ?>
				<div class="alert fade in">
					<button class="close" data-dismiss="alert">×</button>
		            <?php
						echo JText::_('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED');
					?>
				</div>
			<?php endif; ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
						</th>
						<th> </th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_FINDER_INDEX_HEADING_INDEX_TYPE', 'l.type_id', $listDirn, $listOrder); ?>
						</th>
						<th width="15%">
							<?php echo JHtml::_('grid.sort', 'COM_FINDER_INDEX_HEADING_INDEX_DATE', 'l.indexdate', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($this->items) == 0): ?>
					<tr class="row0">
						<td align="center" colspan="7">
							<?php
							if ($this->total == 0) {
								echo JText::_('COM_FINDER_INDEX_NO_DATA') . '  ' . JText::_('COM_FINDER_INDEX_TIP');
							} else {
								echo JText::_('COM_FINDER_INDEX_NO_CONTENT');
							}
							?>
						</td>
					</tr>
					<?php endif; ?>

					<?php $canChange	= JFactory::getUser()->authorise('core.manage',	'com_finder'); ?>
					<?php foreach ($this->items as $i => $item): ?>

					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->link_id); ?>
						</td>
						<td>
							<h4>
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'index.', $canChange, 'cb'); ?>
							<?php echo $this->escape($item->title); ?>
							<?php
							echo '<small>';
							if (strlen($item->url) > 80) {
								echo substr($item->url, 0, 70) . '...';
							} else {
								echo $item->url;
							}
							echo '</small>';
							?>
							</h4>
						</td>
						<td>
							<?php if (intval($item->publish_start_date) or intval($item->publish_end_date) or intval($item->start_date) or intval($item->end_date)) : ?>
								<i class="icon-calendar pull-right pop" rel="popover" data-placement="left" title="<?php echo JText::_('JDETAILS');?>" data-content="<?php echo JText::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date);?>"></i>
							<?php endif; ?>
						</td>
						<td class="small nowrap">
							<?php
							$key = FinderHelperLanguage::branchSingular($item->t_title);
							echo $lang->hasKey($key) ? JText::_($key) : $item->t_title;
							?>
						</td>
						<td class="small nowrap">
							<?php echo JHtml::_('date', $item->indexdate, JText::_('DATE_FORMAT_LC4')); ?>
						</td>
					</tr>

					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7" class="nowrap">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
			</table>

			<input type="hidden" name="task" value="display" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
