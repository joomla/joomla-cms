<?php defined('_JEXEC') or die; ?>

<?php
	$template_path = $this->client->path .DS. 'templates' .DS. $this->template .DS. 'index.php';
	jimport('joomla.html.editor');

	$editor = &JEditor::getInstance('codemirror');
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<?php if ($this->ftp): ?>
<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
	<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

	<?php echo JText::_('DESCFTP'); ?>

	<?php if (JError::isError($this->ftp)): ?>
		<p class="error"><?php echo JText::_($this->ftp->message); ?></p>
	<?php endif; ?>

	<table class="adminform">
	<tbody>
	<tr>
		<td width="120">
			<label for="username"><?php echo JText::_('Username'); ?>:</label>
		</td>
		<td>
			<input type="text" id="username" name="username" class="inputbox" size="70" value="" />
		</td>
	</tr>
	<tr>
		<td width="120">
			<label for="password"><?php echo JText::_('Password'); ?>:</label>
		</td>
		<td>
			<input type="password" id="password" name="password" class="inputbox" size="70" value="" />
		</td>
	</tr>
	</tbody>
	</table>
</fieldset>
<?php endif; ?>

		<h3><?php echo $template_path; ?></h3>
		<div class="clr"></div>
		<div class="editor-border">
			<?php echo $editor->display('filecontent', $this->content, '100%', '300px', '30', '50', false); ?>
		</div>
		<div class="clr"></div>

<input type="hidden" name="template" value="<?php echo $this->template; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
