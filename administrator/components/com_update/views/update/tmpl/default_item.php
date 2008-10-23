<tr class="<?php echo "row".$this->item->index % 2; ?>" >
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
			<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="eid[]" value="<?php echo $this->item->extension_id; ?>" onclick="isChecked(this.checked);" />
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td>
		<?php echo $this->item->type ?>
	</td>
	<td align="center">
		<?php echo $this->item->version ?>
	</td>
	<td align="center"><?php echo @$this->item->folder != '' ? $this->item->folder : 'N/A'; ?></td>
	<td align="center"><?php echo @$this->item->client != '' ? $this->item->client : 'N/A'; ?></td>
	<td>
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Author Information' );?>::<?php echo $this->item->author_info; ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
</tr>
