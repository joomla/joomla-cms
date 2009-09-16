<?php defined('_JEXEC') or die; ?>


<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<th width="50%" class="title">
			<?php echo JText::_('Site Preview'); ?>
		</th>
		<th width="50%" style="text-align:right">
			<?php echo JHtml::_('link', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->id, JText::_('Open in new window'), array('target' => '_blank')); ?>
		</th>
	</tr>
	<tr>
		<td width="100%" valign="top" colspan="2">
			<?php echo JHtml::_('iframe', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->id,'previewFrame',  array('class' => 'previewFrame')) ?>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->template; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
