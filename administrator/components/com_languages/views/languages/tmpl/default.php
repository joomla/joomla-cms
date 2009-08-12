<?php defined('_JEXEC') or die; ?>
<?php
	// Add specific helper files for html generation
	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

	<?php echo $this->loadTemplate('ftp');?>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('Languages_Num'); ?>
				</th>
				<th width="30">
					&nbsp;
				</th>
				<th width="25%" class="title">
					<?php echo JText::_('Languages_Language'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('Languages_Default'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('Languages_Version'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('Languages_Date'); ?>
				</th>
				<th width="20%">
					<?php echo JText::_('Languages_Author'); ?>
				</th>
				<th width="25%">
					<?php echo JText::_('Languages_Author_Email'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php for ($i=0, $n=count($this->rows), $k=0; $i < $n; $i++,$k=1-$k):?>
			<?php $row = &$this->rows[$i];?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="20">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td width="20">
					<?php echo JHtml::_('languages.id',$i,$row->language);?>
				</td>
				<td width="25%">
					<?php echo $row->name;?>
				</td>
				<td width="5%" align="center">
					<?php echo JHtml::_('languages.published',$row->published);?>
				</td>
				<td align="center">
					<?php echo $row->version; ?>
				</td>
				<td align="center">
					<?php echo $row->creationdate; ?>
				</td>
				<td align="center">
					<?php echo $row->author; ?>
				</td>
				<td align="center">
					<?php echo $row->authorEmail; ?>
				</td>
			</tr>
		<?php endfor;?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>