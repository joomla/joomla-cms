<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_messages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
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
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_MESSAGES_SEARCH_IN_SUBJECT'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-left hidden-phone">
				<select name="filter_state" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
					<?php echo JHtml::_('select.options', MessagesHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="20" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'COM_MESSAGES_HEADING_SUBJECT', 'a.subject', $listDirn, $listOrder); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_MESSAGES_HEADING_READ', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th width="15%">
							<?php echo JHtml::_('grid.sort', 'COM_MESSAGES_HEADING_FROM', 'a.user_id_from', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'JDATE', 'a.date_time', $listDirn, $listOrder); ?>
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
					$canChange = $user->authorise('core.edit.state', 'com_messages');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
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
						<td class="hidden-phone">
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
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
