<tr class="<?php echo "row".$this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="eid[<?php echo $this->item->id; ?>]" value="<?php echo $this->item->client_id; ?>" onclick="isChecked(this.checked);" <?php echo $this->item->cbd; ?> />
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td align="center">
		<?php echo $this->item->client_id == "0" ? JText::_( 'Site' ) : JText::_( 'Admin' ); ?>
	</td>
	<td align="center"><?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?></td>
	<td><?php echo @$this->item->creationdate != '' ? $this->item->creationdate : '&nbsp;'; ?></td>
	<td>
		<span onmouseover="return overlib('<?php echo $this->item->author_info; ?>', CAPTION, '<?php echo JText::_( 'Author Information' ); ?>', BELOW, LEFT);" onmouseout="return nd();">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
</tr>
