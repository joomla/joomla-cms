<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_messages&view=messages'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_MESSAGES_SEARCH_IN_SUBJECT'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MESSAGES_SEARCH_IN_SUBJECT'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button class="btn tip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn tip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-left">
			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', MessagesHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
			</select>
		</div>
	</div>
	<div class="clearfix"> </div>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
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
				<th width="20%" class="nowrap">
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
			$canChange	= $user->authorise('core.edit.state', 'com_messages');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->message_id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_messages&view=message&message_id='.(int) $item->message_id); ?>">
						<?php echo $this->escape($item->subject); ?></a>
				</td>
				<td class="center">
					<?php echo JHtml::_('messages.state', $item->state, $i, $canChange); ?>
				</td>
				<td>
					<?php echo $item->user_from; ?>
				</td>
				<td>
					<?php echo JHtml::_('date', $item->date_time, JText::_('DATE_FORMAT_LC2')); ?>
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
</form>
