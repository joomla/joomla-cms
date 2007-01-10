<fieldset title="<?php echo $this->state->message; ?>">
	<legend><?php echo $this->state->message; ?></legend>
	<table class="adminform">
		<tbody>
			<tr>
				<td><?php echo $this->install->message; ?></td>
			</tr>
			<?php if($this->install->ext_message) { ?>
			<tr>
				<td><?php echo $this->install->ext_message; ?></td>
			</tr><?php } ?>
		</tbody>
	</table>
</fieldset>
