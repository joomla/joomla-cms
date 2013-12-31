<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=debuguser&user_id=' . (int) $this->state->get('filter.user_id'));?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_USERS_SEARCH_ASSETS'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_RESET'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="left">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ASSET_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap left">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ASSET_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<?php foreach ($this->actions as $key => $action) : ?>
					<th width="5%" class="nowrap center">
						<span class="hasTooltip" title="<?php echo JHtml::tooltipText($key, $action[1]); ?>"><?php echo JText::_($key); ?></span>
					</th>
					<?php endforeach; ?>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_LFT', 'a.lft', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
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
							$name  = $action[0];
							$check = $item->checks[$name];
							if ($check === true) :
								$class  = 'icon-ok';
								$button = 'btn-success';
							elseif ($check === false) :
								$class  = 'icon-remove';
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

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
