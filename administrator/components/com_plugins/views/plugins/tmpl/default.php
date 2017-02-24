<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the default behaviours for plural form
JHtml::_('formbehavior.plural');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_plugins&task=plugins.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'pluginList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_plugins&view=plugins'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-warning alert-no-items">
				<?php echo JText::_('COM_PLUGINS_MSG_MANAGE_NO_PLUGINS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="pluginList">
				<thead>
					<tr>
						<th width="1%" class="nowrap text-center hidden-sm-down">
							<?php echo JHtml::_('searchtools.sort', '', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="nowrap text-center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap text-center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_PLUGINS_NAME_HEADING', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_PLUGINS_FOLDER_HEADING', 'folder', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'COM_PLUGINS_ELEMENT_HEADING', 'element', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-sm-down text-center">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
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
					$ordering   = ($listOrder == 'ordering');
					$canEdit    = $user->authorise('core.edit',       'com_plugins');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_plugins') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->folder; ?>">
						<td class="order nowrap text-center hidden-sm-down">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass; ?>">
								<span class="icon-menu"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
							<?php endif; ?>
						</td>
						<td class="text-center">
							<?php echo JHtml::_('grid.id', $i, $item->extension_id); ?>
						</td>
						<td class="text-center">
							<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'plugins.', $canChange); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'plugins.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . (int) $item->extension_id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
									<?php echo $item->name; ?></a>
							<?php else : ?>
									<?php echo $item->name; ?>
							<?php endif; ?>
						</td>
						<td class="nowrap small hidden-sm-down text-center">
							<?php echo $this->escape($item->folder); ?>
						</td>
						<td class="nowrap small hidden-sm-down text-center">
							<?php echo $this->escape($item->element); ?>
						</td>
						<td class="small hidden-sm-down text-center">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="hidden-sm-down text-center">
							<?php echo (int) $item->extension_id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
