<?php defined('_JEXEC') or die('Restricted access'); ?>

<form>
<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0" >
	<tr>
		<td class="moduleheading" colspan="2"><?php echo $this->poll->title; ?></td>
	</tr>
	<?php foreach ($this->options as $option)
	{
		if ($option->text <> "")
		{?>
		<tr>
			<td valign="top" height="30"><input type="radio" name="poll" value="<?php echo $option->text; ?>"></td>
			<td class="poll" width="100%" valign="top"><?php echo $option->text; ?></td>
		</tr>
		<?php }
	} ?>
	<tr>
		<td valign="middle" height="50" colspan="2" align="center"><input type="button" name="submit" value="<?php echo JText::_( 'Vote' ); ?>">&nbsp;&nbsp;<input type="button" name="result" value="<?php echo JText::_( 'Results' ); ?>"></td>
	</tr>
</table>
</form>