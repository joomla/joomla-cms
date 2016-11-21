<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JLoader::register('UserlogsHelper', JPATH_COMPONENT . '/helpers/userlogs.php');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "userlogs.exportSelectedLogs" || task == "userlogs.exportLogs")
		{
			var form = document.getElementById("adminForm");
			Joomla.submitform(task, form);
			form.task.value = "";
		}
		else
		{
			Joomla.submitform(task);
		}
	};
');

?>
<form action="<?php echo JRoute::_('index.php?option=com_userlogs&view=userlogs'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-hover" id="logsList">
				<thead>
					<th width="2%">
						<?php echo JHtml::_('searchtools.sort', '', 'a.id', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
							onclick="Joomla.checkAll(this)" />
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_USERLOGS_MESSAGE', 'a.message', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_USERLOGS_DATE', 'a.log_date', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_USERLOGS_EXTENSION', 'a.extension', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_USERLOGS_USER', 'a.user_id', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'COM_USERLOGS_IP_ADDRESS', 'a.ip_address', $listDirn, $listOrder); ?>
					</th>
				</thead>
				<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<span class="sortable-handler inactive tip-top hasTooltip">
									<i class="icon-menu"></i>
								</span>
							</td>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php JEventDispatcher::getInstance()->trigger('onLogMessagePrepare', array (&$item->message, $item->extension)); ?>
								<?php echo $this->escape($item->message); ?>
							</td>
							<td>
								<?php echo $this->escape($item->log_date); ?>
							</td>
							<td>
								<?php echo UserlogsHelper::translateExtensionName(strtoupper(strtok($this->escape($item->extension), '.'))); ?>
							</td>
							<td>
								<?php echo JUser::getInstance($item->user_id)->name; ?>
							</td>
							<td>
								<?php echo JText::_($this->escape($item->ip_address)); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif;?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
