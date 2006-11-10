<fieldset class="adminform">
	<legend><?php echo JText::_( 'Feed Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<?php echo JText::_( 'Show the most recent' ); ?>
			</td>
			<td>
				<?php echo $lists['feed_limit']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'For each article, show' ); ?>
			</td>
			<td>
				<?php echo $lists['feed_summary']; ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
