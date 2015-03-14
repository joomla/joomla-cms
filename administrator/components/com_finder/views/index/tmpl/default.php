<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.popover');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$lang = JFactory::getLanguage();
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

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=index');?>" method="post" name="adminForm" id="adminForm">
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
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_FINDER_FILTER_SEARCH_DESCRIPTION'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php if (!$this->pluginState['plg_content_finder']->enabled) : ?>
			<div class="alert fade in">
				<button class="close" data-dismiss="alert">×</button>
				<?php echo JText::_('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED'); ?>
			</div>
		<?php endif; ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'l.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
					</th>
					<th class="hidden-phone"></th>
					<th width="5%" class="hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_FINDER_INDEX_HEADING_INDEX_TYPE', 'l.type_id', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_FINDER_INDEX_HEADING_INDEX_DATE', 'l.indexdate', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($this->items) == 0) : ?>
				<tr class="row0">
					<td align="center" colspan="6">
						<?php
						if ($this->total == 0)
						{
							echo JText::_('COM_FINDER_INDEX_NO_DATA') . '  ' . JText::_('COM_FINDER_INDEX_TIP');
						} else {
							echo JText::_('COM_FINDER_INDEX_NO_CONTENT');
						}
						?>
					</td>
				</tr>
				<?php endif; ?>

				<?php $canChange = JFactory::getUser()->authorise('core.manage', 'com_finder'); ?>
				<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->link_id); ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'index.', $canChange, 'cb'); ?>
					</td>
					<td>
						<label for="cb<?php echo $i ?>">
							<strong>
								<?php echo $this->escape($item->title); ?>
							</strong>
							<small class="muted">
							<?php
								if (strlen($item->url) > 80)
								{
									echo substr($item->url, 0, 70) . '...';
								} else {
									echo $item->url;
								}
							?>
							</small>
						</label>
					</td>
					<td class="hidden-phone">
						<?php if (intval($item->publish_start_date) or intval($item->publish_end_date) or intval($item->start_date) or intval($item->end_date)) : ?>
							<i class="icon-calendar pull-right pop hasPopover" data-placement="left" title="<?php echo JText::_('JDETAILS');?>" data-content="<?php echo JText::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date);?>"></i>
						<?php endif; ?>
					</td>
					<td class="small nowrap hidden-phone">
						<?php
						$key = FinderHelperLanguage::branchSingular($item->t_title);
						echo $lang->hasKey($key) ? JText::_($key) : $item->t_title;
						?>
					</td>
					<td class="small nowrap hidden-phone">
						<?php echo JHtml::_('date', $item->indexdate, JText::_('DATE_FORMAT_LC4')); ?>
					</td>
				</tr>

				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="6" class="nowrap">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<input type="hidden" name="task" value="display" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
