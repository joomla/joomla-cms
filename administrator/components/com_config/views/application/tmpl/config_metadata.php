<fieldset class="adminform">
	<legend><?php echo JText::_( 'Metadata Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" valign="top" class="key">
				<?php echo JText::_( 'Global Site Meta Description' ); ?>
			</td>
			<td>
				<textarea class="text_area" cols="50" rows="3" style="width:400px; height:50px" name="MetaDesc"><?php echo $row->MetaDesc; ?></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_( 'Global Site Meta Keywords' ); ?>
			</td>
			<td>
				<textarea class="text_area" cols="50" rows="3" style="width:400px; height:50px" name="MetaKeys"><?php echo $row->MetaKeys; ?></textarea>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Show Title Meta Tag' ); ?>::<?php echo JText::_( 'TIPSHOWTITLEMETATAGITEMS' ); ?>">
					<?php echo JText::_( 'Show Title Meta Tag' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['MetaTitle']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Show Author Meta Tag' ); ?>::<?php echo JText::_( 'TIPSHOWAUTHORMETATAGITEMS' ); ?>">
					<?php echo JText::_( 'Show Author Meta Tag' ); ?>
				</span>
			</td>
			<td>
				<?php echo $lists['MetaAuthor']; ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
