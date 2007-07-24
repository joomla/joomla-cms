<fieldset class="adminform">
	<legend><?php echo JText::_( 'Database Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Database type' ); ?>::<?php echo JText::_( 'TIPDTATABASETYPE' ); ?>">
						<?php echo JText::_( 'Database type' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="dbtype" size="30" value="<?php echo $row->dbtype; ?>" />
			</td>
		</tr>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Hostname' ); ?>::<?php echo JText::_( 'TIPDATABASEHOSTNAME' ); ?>">
						<?php echo JText::_( 'Hostname' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="host" size="30" value="<?php echo $row->host; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Username' ); ?>::<?php echo JText::_( 'TIPDATABASEUSERNAME' ); ?>">
						<?php echo JText::_( 'Username' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="user" size="30" value="<?php echo $row->user; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Database' ); ?>::<?php echo JText::_( 'TIPDATABASENAME' ); ?>">
						<?php echo JText::_( 'Database' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="db" size="30" value="<?php echo $row->db; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Database Prefix' ); ?>::<?php echo JText::_( 'TIPDATABASEPREFIX' ); ?>">
						<?php echo JText::_( 'Database Prefix' ); ?>
					</span>
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
