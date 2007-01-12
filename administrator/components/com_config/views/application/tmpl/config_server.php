<fieldset class="adminform">
	<legend><?php echo JText::_( 'Server Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
			<tr>
				<td valign="top" class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Path to Temp-folder' ); ?>::<?php echo JText::_( 'TIPTMPFOLDER' ); ?>">
						<?php echo JText::_( 'Path to Temp-folder' ); ?>
					</span>
				</td>
				<td>
					<input class="text_area" type="text" size="50" name="tmp_path" value="<?php echo $row->tmp_path; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'GZIP Page Compression' ); ?>::<?php echo JText::_( 'Compress buffered output if supported' ); ?>">
						<?php echo JText::_( 'GZIP Page Compression' ); ?>
					</span>
				</td>
				<td>
					<?php echo $lists['gzip']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Error Reporting' ); ?>
				</td>
				<td>
					<?php echo $lists['error_reporting']; ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
