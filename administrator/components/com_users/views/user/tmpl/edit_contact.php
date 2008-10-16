<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
		<fieldset class="adminform">
		<legend><?php echo JText::_( 'Contact Information' ); ?></legend>
		<?php if ( !$this->contact ) { ?>
			<table class="admintable">
				<tr>
					<td>
						<br />
						<span class="note">
							<?php echo JText::_( 'No Contact details linked to this User' ); ?>:
							<br />
							<?php echo JText::_( 'SEECOMPCONTACTFORDETAILS' ); ?>.
						</span>
						<br /><br />
					</td>
				</tr>
			</table>
		<?php } else { ?>
			<table class="admintable">
				<tr>
					<td width="120" class="key">
						<?php echo JText::_( 'Name' ); ?>
					</td>
					<td>
						<strong>
							<?php echo $this->contact[0]->name;?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Position' ); ?>
					</td>
					<td >
						<strong>
							<?php echo $this->contact[0]->con_position;?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Telephone' ); ?>
					</td>
					<td >
						<strong>
							<?php echo $this->contact[0]->telephone;?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Fax' ); ?>
					</td>
					<td >
						<strong>
							<?php echo $this->contact[0]->fax;?>
						</strong>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Misc' ); ?>
					</td>
					<td >
						<strong>
							<?php echo $this->contact[0]->misc;?>
						</strong>
					</td>
				</tr>
				<?php if ($this->contact[0]->image) { ?>
				<tr>
					<td class="key">
						<?php echo JText::_( 'Image' ); ?>
					</td>
					<td valign="top">
						<img src="<?php echo JURI::root() . $cparams->get('image_path') . '/' . $this->contact[0]->image; ?>" align="middle" alt="<?php echo JText::_( 'Contact' ); ?>" />
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td class="key">&nbsp;</td>
					<td>
						<div>
							<br />
							<input class="button" type="button" value="<?php echo JText::_( 'change Contact Details' ); ?>" onclick="gotocontact( '<?php echo $this->contact[0]->id; ?>' )" />
							<i>
								<br /><br />
								'<?php echo JText::_( 'Components -> Contact -> Manage Contacts' ); ?>'
							</i>
						</div>
					</td>
				</tr>
			</table>
			<?php } ?>
		</fieldset>
