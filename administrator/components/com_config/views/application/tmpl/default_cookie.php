<fieldset class="adminform">
	<legend><?php echo JText::_('Cookie Settings'); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
			<?php
			foreach ($this->form->getFields('cookie') as $field):
			?>
			<tr>
				<td width="185" class="key">
					<?php echo $field->label; ?>
				</td>
				<td>
					<?php echo $field->input; ?>
				</td>
			</tr>
			<?php
			endforeach;
			?>
		</tbody>
	</table>
</fieldset>
