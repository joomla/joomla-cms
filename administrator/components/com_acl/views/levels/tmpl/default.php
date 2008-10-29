<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	$type = $this->state->get('list.group_type');
	$editId = 4;
?>
<form action="<?php echo JRoute::_('index.php?option=com_acl&view=levels');?>" method="post" name="adminForm">
	<fieldset class="filter clearfix">
		<div class="left">
			<label for="search"><?php echo JText::_('Search'); ?>:</label>
			<input type="text" name="search" id="search" value="<?php echo $this->state->get('list.search'); ?>" size="60" title="<?php echo JText::_('ACL Search in name'); ?>" />
			<button type="submit"><?php echo JText::_('Search Go'); ?></button>
			<button type="button" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Search Clear'); ?></button>
		</div>
		<input type="hidden" name="group_type" value="<?php echo $type;?>" />
	</fieldset>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items);?>)" />
				</th>
				<th class="left">
					<?php echo JText::_('ACL Col Level Name'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('ACL Col Level Value'); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JText::_('Col ID'); ?>
				</th>
				<th width="40%">
					&nbsp;
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $i++ % 2; ?>">
				<td style="text-align:center">
					<?php if ($item->id > $editId) : ?>
						<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
					<?php endif; ?>
				</td>
				<td style="padding-left:<?php echo intval(($item->level-2)*15)+4; ?>px">
					<?php if ($item->id > $editId) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_acl&task=group.edit&id='.$item->id.'&group_type='.$this->state->get('list.group_type'));?>">
						<?php echo $item->name; ?></a>
					<?php else : ?>
					<?php echo $item->name; ?>
					<?php endif; ?>
				</td>
				<td align="center">
					<?php echo $item->value; ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
				<td>
					&nbsp;
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="group_type" value="<?php echo $this->state->get('list.group_type');?>" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('orderCol'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('orderDirn'); ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />

</form>
