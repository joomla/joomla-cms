<?php defined('_JEXEC') or die; ?>
<?php if ($this->ftp): ?>
	<fieldset title="<?php echo JText::_('Languages_Desc_FTP_Title'); ?>">
		<legend><?php echo JText::_('Languages_Desc_FTP_Title'); ?></legend>

		<?php echo JText::_('Languages_Desc_FTP'); ?>

		<?php if (JError::isError($ftp)): ?>
			<p><?php echo JText::_($ftp->message); ?></p>
		<?php endif; ?>

		<table class="adminform nospace">
			<tbody>
				<tr>
					<td width="120">
						<label for="username"><?php echo JText::_('Languages_Username'); ?>:</label>
					</td>
					<td>
						<input type="text" id="username" name="username" class="input_box" size="70" value="" />
					</td>
				</tr>
				<tr>
					<td width="120">
						<label for="password"><?php echo JText::_('Languages_Password'); ?>:</label>
					</td>
					<td>
						<input type="password" id="password" name="password" class="input_box" size="70" value="" />
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
<?php endif; ?>