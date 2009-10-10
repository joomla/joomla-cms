<?php defined('_JEXEC') or die; ?>
<div id="tablecell">
	<table class="adminform">
		<tr>
			<th><?php echo JText::_('Database Table'); ?></th>
			<th><?php echo JText::_('Num of Items'); ?></th>
			<th><?php echo JText::_('Checked-In'); ?></th>
		</tr>
	<?php
	$k = 0;
	foreach ($this->tables as $table => $count): ?>
		<tr class="row<?php echo $k; ?>">
			<td width="350">
				<?php echo JText::_('Checking table').' - '.$table; ?>
			</td>
			<td width="150">
				<?php echo JText::_('Checked-In').' '; ?><strong><?php echo $count; ?></strong><?php echo ' '.JText::_('items'); ?>
			</td>
			<td>
				<?php if ($count > 0): ?>
				<div class="checkin-tick"><?php echo JText::_('tick'); ?></div>
				<?php else: ?>
				&nbsp;
				<?php endif; ?>
			</td>
		</tr>
	<?php
	$k = 1 - $k;
	endforeach;
	?>
	<tr>
		<td colspan="3">
			<span class="stat-notice"><?php echo JText::_('Checked out items have now been all checked in'); ?></span>
		</td>
	</tr>
	</table>
</div>
