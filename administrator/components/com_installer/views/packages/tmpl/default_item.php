<tr class="<?php echo "row".$this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
		<input type="radio" id="cb<?php echo $this->item->index;?>" name="eid[<?php echo $this->item->manifest_file; ?>]" value="0" onclick="isChecked(this.checked);" <?php echo $this->item->cbd; ?> />
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td align="center"><?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?></td>
	<td>
		<a target="_blank" href="<?php echo $this->item->authorurl ?>"><?php echo $this->item->author  ?></a>
	</td>	
	<td>
		<a target="_blank" href="<?php echo $this->item->packagerurl ?>"><?php echo $this->item->packager ?></a>
	</td>
</tr>