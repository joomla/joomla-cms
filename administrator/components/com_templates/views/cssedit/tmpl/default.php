<?php defined('_JEXEC') or die; ?>

<?php
	$css_path = $this->client->path .DS. 'templates' .DS. $this->template .DS. 'css' .DS. $this->filename;
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<?php if ($this->ftp): ?>
<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
	<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

	<?php echo JText::_('DESCFTP'); ?>

	<?php if (JError::isError($this->ftp)): ?>
		<p><?php echo JText::_($this->ftp->message); ?></p>
	<?php endif; ?>

	<table class="adminform nospace">
	<tbody>
	<tr>
		<td width="120">
			<label for="username"><?php echo JText::_('Username'); ?>:</label>
		</td>
		<td>
			<input type="text" id="username" name="username" class="input_box" size="70" value="" />
		</td>
	</tr>
	<tr>
		<td width="120">
			<label for="password"><?php echo JText::_('Password'); ?>:</label>
		</td>
		<td>
			<input type="password" id="password" name="password" class="input_box" size="70" value="" />
		</td>
	</tr>
	</tbody>
	</table>
</fieldset>
<?php endif; ?>

<table class="adminform">
<tr>
	<th>
		<?php echo $css_path; ?>
	</th>
</tr>
<tr>
	<td>
		<textarea style="width:100%;height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $this->content; ?></textarea>
	</td>
</tr>
</table>

<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->id; ?>" />
<input type="hidden" name="filename" value="<?php echo $this->filename; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
