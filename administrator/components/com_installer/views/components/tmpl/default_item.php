<tr class="<?php echo "row".$this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
		<input type="radio" id="cb<?php echo $this->item->index;?>" name="eid" value="<?php echo $this->item->id; ?>" onclick="isChecked(this.checked);" <?php echo $this->item->cbd; ?> />
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td align="center">
		<?php if (!$this->item->option) : ?>
		<strong>X</strong>
		<?php else : ?>
		<a href="index.php?option=com_installer&amp;type=components&amp;task=<?php echo $this->item->task; ?>&amp;eid[]=<?php echo $this->item->id; ?>&amp;limitstart=<?php echo $this->pagination->limitstart; ?>&amp;<?php echo JUtility::getToken();?>=1"><img src="images/<?php echo $this->item->img; ?>" border="0" title="<?php echo $this->item->action; ?>" alt="<?php echo $this->item->alt; ?>" /></a>
		<?php endif; ?>
	</td>
	<td align="center"><?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?></td>
	<td><?php echo @$this->item->creationdate != '' ? $this->item->creationdate : '&nbsp;'; ?></td>
	<td>
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Author Information' );?>::<?php echo $this->item->author_info; ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
</tr>
