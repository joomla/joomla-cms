<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$lang = JFactory::getLanguage();
JText::script('COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(pressbutton)
	{
		if (pressbutton == "map.delete")
		{
			if (confirm(Joomla.JText._("COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT")))
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

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=maps');?>" method="post" name="adminForm" id="adminForm">
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
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="10%">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (count($this->items) == 0) : ?>
				<tr class="row0">
					<td class="center" colspan="5">
						<?php echo JText::_('COM_FINDER_MAPS_NO_CONTENT'); ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php if ($this->state->get('filter.branch') != 1) : ?>
				<tr class="row1">
					<td colspan="5" class="center">
						<a href="#" onclick="document.getElementById('filter_branch').value='1';document.adminForm.submit();">
							<?php echo JText::_('COM_FINDER_MAPS_RETURN_TO_BRANCHES'); ?></a>
					</td>
				</tr>
				<?php endif; ?>

				<?php $canChange = JFactory::getUser()->authorise('core.manage', 'com_finder'); ?>
				<?php foreach ($this->items as $i => $item) : ?>

				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php
							$key = FinderHelperLanguage::branchSingular($item->title);
							$title = $lang->hasKey($key) ? JText::_($key) : $item->title;
						?>
						<?php if ($this->state->get('filter.branch') == 1 && $item->num_children) : ?>
							<a href="#" onclick="document.getElementById('filter_branch').value='<?php echo (int) $item->id;?>';document.adminForm.submit();" title="<?php echo JText::_('COM_FINDER_MAPS_BRANCH_LINK'); ?>">
								<?php echo $this->escape($title); ?></a>
						<?php else: ?>
							<?php echo $this->escape(($title == '*') ? JText::_('JALL_LANGUAGE') : $title); ?>
						<?php endif; ?>
						<?php if ($item->num_children > 0) : ?>
							<small>(<?php echo $item->num_children; ?>)</small>
						<?php elseif ($item->num_nodes > 0) : ?>
							<small>(<?php echo $item->num_nodes; ?>)</small>
						<?php endif; ?>
						<?php if ($this->escape(trim($title, '**')) == 'Language' && JLanguageMultilang::isEnabled()) : ?>
							<strong><?php echo JText::_('COM_FINDER_MAPS_MULTILANG'); ?></strong>
						<?php endif; ?>
					</td>
					<td class="center nowrap">
						<?php echo JHtml::_('jgrid.published', $item->state, $i, 'maps.', $canChange, 'cb'); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="9" class="nowrap">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>

		<input type="hidden" name="task" value="display" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn ?>" />
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
