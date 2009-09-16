<?php defined('_JEXEC') or die; ?>

<?php
	JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="adminForm">
<div class="width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Styles'); ?></legend>

		<table class="admintable">
		<tr>
			<th width="120"><?php echo JText::_('Style'); ?></th>
			<th width="70"><?php echo JText::_('Default'); ?></th>
			<th width="70"><?php echo JText::_('Delete'); ?></th>
		</tr>
		<?php
		$i = 1;
		foreach($this->paramsets as $set)
		{ ?>
		<tr>
			<td><a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&template='.$this->template.'&id='.$set->id.'&client='.$this->client->id); ?>">
				<?php echo JText::_($this->template); ?> (<?php echo $set->description; ?>)
			</a></td>
			<td><?php
			if($set->home)
			{
				echo '<img src="templates/'.$this->template.'/images/menu/icon-16-default.png" alt="'.JText::_('Default').'" />';
			} else {
				echo '<a href="'.JRoute::_('index.php?option=com_templates&task=setdefault&id='.$set->id).'">default</a>';
			} ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_templates&task=delete&template='.$this->template.'&id='.$set->id); ?>">
			
			
				<?php echo '<img src="templates/'.$this->template.'/images/menu/icon-16-delete.png"  alt="'.JText::_('Delete').'" />' ; ?> 
				</a>
			</td>
		</tr>
		<?php } ?>
		</table>
	</fieldset>

</div>

<div class="width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

		<table class="admintable">
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Name'); ?>:
			</td>
			<td>
				<strong>
					<?php echo JText::_($this->template); ?> - <input class="inputbox" type="text" name="description" id="description" size="40" maxlength="255" value="<?php echo $this->params->description; ?>" />

				</strong>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Description'); ?>:
			</td>
			<td>
				<?php echo JText::_($this->data->description); ?>
			</td>
		</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('Parameters'); ?></legend>
		<table class="admintable">
		<tr>
			<td>
			<?php
			if (!is_null($this->params->params)) {
				echo $this->params->params->render();
			} else {
				echo '<div class="noparams-notice">' . JText :: _('No Parameters') . '</div>';
			}
			?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="id" value="<?php echo $this->params->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->template; ?>" />

<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
