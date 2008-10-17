<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'System Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
			<tr>
				<td width="185" class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Secret Word' ); ?>::<?php echo JText::_( 'TIPSECRETWORD' ); ?>">
					<?php echo JText::_( 'Secret Word' ); ?>
				</span>
				</td>
				<td>
					<strong><?php echo $this->row->secret; ?></strong>
				</td>
			</tr>
			<tr>
				<td valign="top" class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'Path to Log-folder' ); ?>::<?php echo JText::_( 'TIPLOGFOLDER' ); ?>">
						<?php echo JText::_( 'Path to Log-folder' ); ?>
					</span>
				</td>
				<td>
					<input class="text_area" type="text" size="50" name="log_path" value="<?php echo $this->row->log_path; ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_( 'ENABLE WEB SERVICES' ); ?>::<?php echo JText::_( 'TIPENABLEWEBSERVICES' ); ?>">
					<?php echo JText::_( 'ENABLE WEB SERVICES' ); ?>
				</span>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', 'xmlrpc_server', 'class="inputbox"', $this->row->xmlrpc_server); ?>
				</td>
			</tr>
			<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Help Server' ); ?>::<?php echo JText::_( 'TIPHELPSERVER' ); ?>">
					<?php echo JText::_( 'Help Server' ); ?>
				</span>
			</td>
			<td>
				<?php echo JHTML::_('config.helpSites', $this->row->helpurl); ?>
				<input type="button" onclick="submitbutton('refreshhelp')" value="<?php echo JText::_('Refresh'); ?>" />
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
