<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Finder\Administrator\Helper\FinderHelperLanguage;

JHtml::_('bootstrap.popover');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$lang      = JFactory::getLanguage();

JText::script('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT');
JText::script('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(pressbutton)
	{
		if (pressbutton == "index.purge")
		{
			if (confirm(Joomla.JText._("COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT")))
			{
				Joomla.submitform(pressbutton);
			}
			else
			{
				return false;
			}
		}
		if (pressbutton == "index.delete")
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
<form action="<?php echo JRoute::_('index.php?option=com_finder&view=index'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th style="width:1%" class="nowrap text-center">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th style="width:1%" class="nowrap text-center">
								<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'l.published', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap">
								<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_TYPE', 't.title', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="nowrap d-none d-md-table-cell text-center">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_DATE', 'l.indexdate', $listDirn, $listOrder); ?>
							</th>
							<th style="width:15%" class="nowrap text-center d-none d-md-table-cell text-center">
								<?php echo JText::_('COM_FINDER_INDEX_HEADING_DETAILS'); ?>
							</th>
							<th style="width:30%" class="nowrap d-none d-md-table-cell">
								<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_LINK_URL', 'l.url', $listDirn, $listOrder); ?>
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
						<?php $canChange = JFactory::getUser()->authorise('core.manage', 'com_finder'); ?>
						<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo JHtml::_('grid.id', $i, $item->link_id); ?>
							</td>
							<td class="text-center">
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'index.', $canChange, 'cb'); ?>
							</td>
							<td>
								<label for="cb<?php echo $i; ?>">
									<?php echo $this->escape($item->title); ?>
								</label>
							</td>
							<td class="small nowrap d-none d-md-table-cell text-center">
								<?php
								$key = FinderHelperLanguage::branchSingular($item->t_title);
								echo $lang->hasKey($key) ? JText::_($key) : $item->t_title;
								?>
							</td>
							<td class="small nowrap d-none d-md-table-cell text-center">
								<?php echo JHtml::_('date', $item->indexdate, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="text-center d-none d-md-table-cell text-center">
								<?php if ((int) $item->publish_start_date or (int) $item->publish_end_date or (int) $item->start_date or (int) $item->end_date) : ?>
                                    <span class="icon-calendar pop hasPopover" aria-hidden="true" data-placement="left" title="<?php echo JText::_('COM_FINDER_INDEX_DATE_INFO_TITLE'); ?>" data-content="<?php echo JText::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date); ?>"></span>
                                    <span class="sr-only"><?php echo JText::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date); ?></span>
								<?php endif; ?>
							</td>
							<td class="small break-word d-none d-md-table-cell">
								<?php echo (strlen($item->url) > 80) ? substr($item->url, 0, 70) . '...' : $item->url; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="task" value="display">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
