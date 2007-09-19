<fieldset class="adminform">
	<legend><?php echo JText::_( 'Debug Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Enable Debugging' ); ?>::<?php echo JText::_('TIPDEBUGGINGINFO'); ?>">
					<?php echo JText::_( 'Debug System' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['debug']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Debug Language' ); ?>::<?php echo JText::_('TIPDEBUGLANGUAGE'); ?>">
					<?php echo JText::_( 'Debug Language' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['debug_lang']; ?>
			</td>
		</tr>
	</table>
</fieldset>
