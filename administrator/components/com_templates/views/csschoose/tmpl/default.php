<?php defined('_JEXEC') or die; ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<table cellpadding="1" cellspacing="1" border="0" width="100%">
<tr>
	<td width="220">
		<span class="componentheading">&nbsp;</span>
	</td>
</tr>
</table>
<table class="adminlist">
<tr>
	<th width="5%" align="left">
		<?php echo JText::_('Num'); ?>
	</th>
	<th width="85%" align="left">
		<?php echo $this->t_dir; ?>
	</th>
	<th width="10%">
		<?php echo JText::_('Writable'); ?>/<?php echo JText::_('Unwritable'); ?>
	</th>
</tr>
<?php
$k = 0;
for ($i = 0, $n = count($this->files); $i < $n; $i++) {
	$file = & $this->files[$i];
?>
	<tr class="<?php echo 'row'. $k; ?>">
		<td width="5%">
			<input type="radio" id="cb<?php echo $i;?>" name="filename" value="<?php echo htmlspecialchars($file, ENT_COMPAT, 'UTF-8'); ?>" onClick="isChecked(this.checked);" />
		</td>
		<td width="85%">
			<?php echo $file; ?>
		</td>
		<td width="10%">
			<?php echo is_writable($this->t_dir.DS.$file) ? '<span class="writable"> '. JText::_('Writable') .'</span>' : '<span class="unwritable"> '. JText::_('Unwritable') .'</span>' ?>
		</td>
	</tr>
<?php
	$k = 1 - $k;
}
?>
</table>
<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->template; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
</form>
