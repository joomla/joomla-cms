<fieldset class="adminform">
	<legend><?php echo JText::_( 'Site Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
	<tbody>
		<tr>
			<td width="185" class="key">
			<?php echo JText::_( 'Site Offline' ); ?>
			</td>
			<td>
			<?php echo $lists['offline']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Offline Message' ); ?>::<?php echo JText::_( 'TIPIFYOURSITEISOFFLINE' ); ?>">
					<?php echo JText::_( 'Offline Message' ); ?>
				</span>
			</td>
			<td>
				<textarea class="text_area" cols="60" rows="2" style="width:400px; height:40px" name="offline_message"><?php echo $row->offline_message; ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Site Name' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="sitename" size="50" value="<?php echo $row->sitename; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Default WYSIWYG Editor' ); ?>
			</td>
			<td>
				<?php echo $lists['editor']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'List Length' ); ?>::<?php echo JText::_( 'TIPSETSDEFAULTLENGTHLISTS' ); ?>">
					<?php echo JText::_( 'List Length' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['list_limit']; ?>
			</td>
		</tr>
	</tbody>
	</table>
</fieldset>
