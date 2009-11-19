<?php defined('_JEXEC') or die; ?>
<table id="global-checkin" class="adminlist">
	<thead>
		<tr>
			<th class="left"><?php echo JText::_('DATABASE_TABLE'); ?></th>
			<th><?php echo JText::_('ITEMS_CHECKED_IN'); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	foreach ($this->tables as $table => $count): ?>
		<tr class="row<?php echo $k; ?>">
			<td>
				<?php echo JText::_('Checking').' <em>'.$table.'</em> '.JText::_('table'); ?>
			</td>
			
			<?php if ($count > 0): ?> 
				<td width="100" class="active center">
					<span class="success"><?php echo $count; ?></span>
				</td>
			<?php else: ?>
				<td width="100" class="center">
					<?php echo $count; ?>
				</td>
			<?php endif; ?>
			
			<td width="50">
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
		<td colspan="3" class="center">
			<span class="stat-notice success"><?php echo JText::_('Checked out items have now been all checked in'); ?></span>
		</td>
	</tr>
</table>
