<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" width="10">
				<?php echo JText::_('Num'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->data);?>);" />
			</th>
			<th class="title" nowrap="nowrap">
				<?php echo JText::_('CACHE_GROUP'); ?>
			</th>
			<th width="5%" align="center" nowrap="nowrap">
				<?php echo JText::_('NUMBER_OF_FILES'); ?>
			</th>
			<th width="10%" align="center">
				<?php echo JText::_('Size'); ?>
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
		<?php
		$i = 0;
		foreach ($this->data as $folder => $item): ?>
		<tr class="row<?php echo $i % 2; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->group; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
				<span class="bold">
					<?php echo $item->group; ?>
				</span>
			</td>
			<td align="center">
				<?php echo $item->count; ?>
			</td>
			<td align="center">
				<?php echo $item->size ?>
			</td>
		</tr>
		<?php $i++; endforeach; ?>
	</tbody>
</table>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
