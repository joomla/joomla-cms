<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<table class="adminlist">
	<thead>
		<tr>
			<th class="title row-number-col">
				<?php echo JText::_('COM_CACHE_NUM'); ?>
			</th>
			<th class="checkmark-col">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('TPL_HATHOR_CHECKMARK_ALL'); ?>" onclick="checkAll(this)" />
			</th>
			<th class="title nowrap">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
			</th>
			<th class="width-5 center nowrap">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
			</th>
			<th class="width-10 center">
				<?php echo JHtml::_('grid.sort',  'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
			</th>
		</tr>
	</thead>

	<tbody>
		<?php
		$i = 0;
		foreach ($this->data as $folder => $item): ?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" title="<?php echo JText::sprintf('JGRID_CHECKBOX_ROW_N', ($i + 1)); ?>" value="<?php echo $item->group; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
				<span class="bold">
					<?php echo $item->group; ?>
				</span>
			</td>
			<td class="center">
				<?php echo $item->count; ?>
			</td>
			<td class="center">
				<?php echo $item->size ?>
			</td>
		</tr>
		<?php $i++; endforeach; ?>
	</tbody>
</table>

<?php echo $this->pagination->getListFooter(); ?>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
