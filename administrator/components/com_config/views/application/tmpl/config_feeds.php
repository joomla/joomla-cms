<fieldset class="adminform">
	<legend><?php echo JText::_( 'Feed Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Show the most recent' ); ?>::<?php echo JText::_( 'TIPSHOWMOSTRECENT' ); ?>">
					<?php echo JText::_( 'Show the most recent' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['feed_limit']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'For each article, show' ); ?>::<?php echo JText::_( 'TIPFOREACHSHOW' ); ?>">
					<?php echo JText::_( 'For each article, show' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['feed_summary']; ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
