<?php defined('_JEXEC') or die; ?>


<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">
<div class="width-100">
	<h3 class="title fltlft">
		<?php echo JText::_('Site Preview'); ?>
	</h3>
	<h3 class="title fltrt">
		<?php echo JHtml::_('link', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->id, JText::_('Open in new window'), array('target' => '_blank')); ?>
	</h3>
	<div class="clr"></div>
	<div class="width-100 temprev">
		<?php echo JHtml::_('iframe', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->id,'previewframe',  array('class' => 'previewframe')) ?>
	</div>
</div>

<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->template; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
