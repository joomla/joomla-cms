<tr class="<?php echo "row".$this->item->index % 2; ?>" >
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="uid[]" value="<?php echo $this->item->update_id; ?>" onclick="isChecked(this.checked);" />
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Description' );?>::<?php echo $this->item->description ? $this->item->description : JText::_('NODESC'); ?>">
		<?php echo $this->item->name; ?>
		</span>
	</td>
	<td align="center">
		<?php echo $this->item->extension_id ? JText::_('Update') : JText::_('New install') ?>
	</td>
	<td>
		<?php echo JText::_($this->item->type) ?>
	</td>
	<td align="center">
		<?php echo $this->item->version ?>
	</td>
	<td align="center"><?php echo @$this->item->folder != '' ? $this->item->folder : 'N/A'; ?></td>
	<td align="center"><?php echo @$this->item->client != '' ? JText::_($this->item->client) : 'N/A'; ?></td>
	<td>
		<?php echo $this->item->detailsurl ?>
	</td>
</tr>
