<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$canEdit    = $user->authorise('core.edit', 'com_users');
$sortFields = $this->getSortFields();

JFactory::getDocument()->addScriptDeclaration('
	Joomla.orderTable = function()
	{
		table = document.getElementById("list_sortTable");
		direction = document.getElementById("list_directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != "' . $listOrder . '")
		{
			dirn = "asc";
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, "");
	};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=notes');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="5%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="left" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_USER_HEADING', 'u.name', $listDirn, $listOrder); ?>
					</th>
					<th class="left" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_SUBJECT_HEADING', 'a.subject', $listDirn, $listOrder); ?>
					</th>
					<th width="20%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_CATEGORY_HEADING', 'c.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_USERS_REVIEW_HEADING', 'a.review_time', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap">
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
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canChange = $user->authorise('core.edit.state', 'com_users'); ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center checklist">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'notes.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
					</td>
					<td>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_users&task=note.edit&id=' . $item->id);?>">
								<?php echo $this->escape($item->user_name); ?></a>
						<?php else : ?>
							<?php echo $this->escape($item->user_name); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($item->subject) : ?>
							<?php echo $this->escape($item->subject); ?>
						<?php else : ?>
							<?php echo JText::_('COM_USERS_EMPTY_SUBJECT'); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($item->catid && $item->cparams->get('image')) : ?>
							<?php echo JHtml::_('users.image', $item->cparams->get('image')); ?>
						<?php endif; ?>
						<?php echo $this->escape($item->category_title); ?>
					</td>
					<td>
						<?php if ($item->review_time !== JFactory::getDbo()->getNullDate()) : ?>
							<?php echo JHtml::_('date', $item->review_time, JText::_('DATE_FORMAT_LC4')); ?>
						<?php else : ?>
							<?php echo JText::_('COM_USERS_EMPTY_REVIEW'); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
