<fieldset class="adminform">
	<legend><?php echo JText::_( 'Session Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Session Lifetime' ); ?>::<?php echo JText::_( 'TIPAUTOLOGOUTTIMEOF' ); ?>">
						<?php echo JText::_( 'Session Lifetime' ); ?>
					</span>
				</td>
				<td>
					<input class="text_area" type="text" name="lifetime" size="10" value="<?php echo $row->lifetime; ?>" />
					&nbsp;<?php echo JText::_('minutes'); ?>&nbsp;
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Session Handler' ); ?>
				</td>
				<td>
					<strong><?php echo $lists['session_handlers']; ?></strong>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
