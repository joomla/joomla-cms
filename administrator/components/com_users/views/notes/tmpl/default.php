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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=notes'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
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
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
					</th>
					<th width="20%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_USER', 'u.name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone hidden-tablet">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_REVIEW', 'a.review_time', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
			<?php foreach ($this->items as $i => $item) :
				$canEdit    = $user->authorise('core.edit',       'com_users.category.' . $item->catid);
				$canCheckin = $user->authorise('core.admin',      'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
				$canChange  = $user->authorise('core.edit.state', 'com_users.category.' . $item->catid) && $canCheckin;
				$subject    = $item->subject ?: JText::_('COM_USERS_EMPTY_SUBJECT');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center checklist">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->state, $i, 'notes.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
							<?php // Create dropdown items and render the dropdown list.
							if ($canChange)
							{
								JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'notes');
								JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'notes');
								echo JHtml::_('actionsdropdown.render', $this->escape($subject));
							}
							?>
						</div>
					</td>
					<td>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'notes.', $canCheckin); ?>
						<?php endif; ?>
						<?php $subject = $item->subject ?: JText::_('COM_USERS_EMPTY_SUBJECT'); ?>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_users&task=note.edit&id=' . $item->id); ?>"><?php echo $this->escape($subject); ?></a>
						<?php else : ?>
							<?php echo $this->escape($subject); ?>
						<?php endif; ?>
						<div class="small">
							<?php echo JText::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
						</div>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape($item->user_name); ?>
					</td>
					<td class="hidden-phone hidden-tablet">
						<?php if ($item->review_time !== JFactory::getDbo()->getNullDate()) : ?>
							<?php echo JHtml::_('date', $item->review_time, JText::_('DATE_FORMAT_LC4')); ?>
						<?php else : ?>
							<?php echo JText::_('COM_USERS_EMPTY_REVIEW'); ?>
						<?php endif; ?>
					</td>
					<td class="hidden-phone">
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
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
