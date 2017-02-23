<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;


JHtml::_('bootstrap.tooltip');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

JText::script('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(pressbutton)
	{
		if (pressbutton == "filters.delete")
		{
			if (confirm(Joomla.JText._("COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT")))
			{
				Joomla.submitform(pressbutton);
			}
			else
			{
				return false;
			}
		}
		Joomla.submitform(pressbutton);
	};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_finder&view=filters'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
				<div class="alert alert-warning alert-no-items">
					<?php echo JText::_('COM_FINDER_NO_RESULTS_OR_FILTERS'); ?>
				</div>
				<?php else : ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%" class="nowrap text-center">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th width="1%" class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
							</th>
							<th width="10%" class="nowrap hidden-sm-down">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_HEADING_CREATED_BY', 'a.created_by_alias', $listDirn, $listOrder); ?>
							</th>
							<th width="10%" class="nowrap hidden-sm-down">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_HEADING_CREATED_ON', 'a.created', $listDirn, $listOrder); ?>
							</th>
							<th width="5%" class="nowrap hidden-sm-down">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_HEADING_MAP_COUNT', 'a.map_count', $listDirn, $listOrder); ?>
							</th>
							<th width="1%" class="nowrap hidden-sm-down">
								<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.filter_id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="7">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach ($this->items as $i => $item) :
						$canCreate  = $user->authorise('core.create',     'com_finder');
						$canEdit    = $user->authorise('core.edit',       'com_finder');
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
						$canChange  = $user->authorise('core.edit.state', 'com_finder') && $canCheckin;
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo JHtml::_('grid.id', $i, $item->filter_id); ?>
							</td>
							<td class="text-center nowrap">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'filters.', $canChange); ?>
							</td>
							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'filters.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_finder&task=filter.edit&filter_id=' . (int) $item->filter_id); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>
							<td class="nowrap hidden-sm-down">
								<?php echo $item->created_by_alias ? $item->created_by_alias : $item->user_name; ?>
							</td>
							<td class="nowrap hidden-sm-down">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="nowrap hidden-sm-down">
								<?php echo $item->map_count; ?>
							</td>
							<td class="hidden-sm-down">
								<?php echo (int) $item->filter_id; ?>
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
		</div>
	</div>
</form>
