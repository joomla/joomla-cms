<?php defined('_JEXEC') or die; ?>

<?php
	JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="adminForm">
<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Styles'); ?></legend>

		<table class="admintable">
		<tr>
			<th><?php echo JText::_('#'); ?></th>
			<th><?php echo JText::_('Style'); ?></th>
			<th><?php echo JText::_('Default'); ?></th>
			<th><?php echo JText::_('Delete'); ?></th>
		</tr>
		<?php
		$i = 1;
		foreach($this->style as $style)
		{ ?>
		<tr>
			<td><?php echo $i; ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&cid[]='.$style->id.'&client='.$this->client->id); ?>">
				<?php echo JText::_($this->row->template); ?> - <?php echo $style->description; ?>
			</a></td>
			<td><?php
			if($style->home)
			{
				echo '<img src="templates/khepri/images/menu/icon-16-default.png" alt="'.JText::_('Default').'" />';
			} else {
				echo '<a href="'.JRoute::_('index.php?option=com_templates&task=setdefault&id='.$style->id).'">default</a>';
			} ?></td>
			<td><a href="<?php echo JRoute::_('index.php?option=com_templates&task=delete&id='.$style->id); ?>">delete</a></td>
		</tr>
		<?php } ?>
		</table>
	</fieldset>

</div>

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Details'); ?></legend>

		<table class="admintable">
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Name'); ?>:
			</td>
			<td>
				<strong>
					<?php echo JText::_($this->row->template); ?> - <input class="inputbox" type="text" name="description" id="description" size="40" maxlength="255" value="<?php echo $this->row->description; ?>" />

				</strong>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<?php echo JText::_('Description'); ?>:
			</td>
			<td>
				<?php echo JText::_($this->row->xmldata->description); ?>
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
			if (!is_null($this->params)) {
				echo $this->params->render();
			} else {
				echo '<i>' . JText :: _('No Parameters') . '</i>';
			}
			?>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="template" value="<?php echo $this->row->template; ?>" />

<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="client" value="<?php echo $this->client->id;?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
