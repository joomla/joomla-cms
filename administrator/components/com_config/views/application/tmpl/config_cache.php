<fieldset class="adminform">
	<legend><?php echo JText::_( 'Cache Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Cache' ); ?>
			</td>
			<td>
				<?php echo $lists['caching']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Cache Time' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="cachetime" size="5" value="<?php echo $row->cachetime; ?>" />
					<?php echo JText::_( 'seconds' ); ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
