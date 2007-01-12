<fieldset class="adminform">
	<legend><?php echo JText::_( 'Database Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<?php echo JText::_( 'Database type' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="dbtype" size="30" value="<?php echo $row->dbtype; ?>" />
			</td>
		</tr>
		<tr>
			<td width="185" class="key">
				<?php echo JText::_( 'Hostname' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="host" size="30" value="<?php echo $row->host; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Username' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="user" size="30" value="<?php echo $row->user; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Database' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="db" size="30" value="<?php echo $row->db; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Database Prefix' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="dbprefix" size="10" value="<?php echo $row->dbprefix; ?>" />
				&nbsp;
				<span class="error hasTip" title="<?php echo JText::_( 'Warning' );?>::<?php echo JText::_( 'WARNDONOTCHANGEDATABASETABLESPREFIX' ); ?>">
					<?php echo ConfigApplicationView::WarningIcon(); ?>
				</span>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
