<?php defined('_JEXEC') or die('Restricted access'); ?>

<style type="text/css">
.previewFrame {
	border: none;
	width: 95%;
	height: 600px;
	padding: 0px 5px 0px 10px;
}
</style>

<table class="adminform">
	<tr>
		<th width="50%" class="title">
			<?php echo JText::_( 'Site Preview' ); ?>
		</th>
		<th width="50%" style="text-align:right">
			<?php echo JHtml::_('link', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->template, JText::_( 'Open in new window' ), array('target' => '_blank')); ?>
		</th>
	</tr>
	<tr>
		<td width="100%" valign="top" colspan="2">
			<?php echo JHtml::_('iframe', $this->url.'index.php?tp='.$this->tp.'&amp;template='.$this->template,'previewFrame',  array('class' => 'previewFrame')) ?>
		</td>
	</tr>
</table>
