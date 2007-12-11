<?php defined('_JEXEC') or die('Restricted access'); ?>
		<tr>
			<td>
				<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>"><img src="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" width="<?php echo $this->_tmp_img->width_16; ?>" height="<?php echo $this->_tmp_img->height_16; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo MediaHelper::parseSize($this->_tmp_img->size); ?>" border="0" /></a>
			</td>
			<td class="description">
				<a href="<?php echo  COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" rel="preview"><?php echo $this->escape( $this->_tmp_img->name); ?></a>
			</td>
			<td>
				<?php echo $this->_tmp_img->width; ?> x <?php echo $this->_tmp_img->height; ?>
			</td>
			<td>
				<?php echo MediaHelper::parseSize($this->_tmp_img->size); ?>
			</td>
			<td>
				<a class="delete-item" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=component&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>"><img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" /></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
			</td>
		</tr>
