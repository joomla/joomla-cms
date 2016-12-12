<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

JFactory::getDocument()->addStyleDeclaration(
	"
	@media (min-width: 768px) {
		div.modal {
			left: none;
			width: 500px;
			margin-left: -250px;
		}
	}
	"
);
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"></div>
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
						<th class="title nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_MESSAGES_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'COM_MESSAGES_HEADING_READ', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_MESSAGES_HEADING_FROM', 'a.user_id_from', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap hidden-tablet hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.date_time', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="5">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canChange = $user->authorise('core.edit.state', 'com_messages');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->message_id); ?>
						</td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_messages&view=message&message_id=' . (int) $item->message_id); ?>">
								<?php echo $this->escape($item->subject); ?></a>
						</td>
						<td class="center">
							<?php echo JHtml::_('messages.status', $i, $item->state, $canChange); ?>
						</td>
						<td>
							<?php echo $item->user_from; ?>
						</td>
						<td class="hidden-phone hidden-tablet">
							<?php echo JHtml::_('date', $item->date_time, JText::_('DATE_FORMAT_LC2')); ?>
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
