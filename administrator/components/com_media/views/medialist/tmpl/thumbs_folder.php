<?php defined('_JEXEC') or die('Restricted access'); ?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="<?php echo JRoute::_('index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=' . $this->_tmp_folder->path_relative); ?>" target="folderframe">
						<img src="components/com_media/images/folder.png" width="80" height="80" border="0" /></a>
				</div>
			</div>
			<div class="controls">
				<a class="delete-item" href="<?php echo JRoute::_('index.php?option=com_media&amp;task=folder.delete&amp;tmpl=component&amp;folder=' . $this->state->folder . '&amp;rm[]=' . $this->_tmp_folder->name); ?>" rel="<?php echo $this->_tmp_folder->name; ?>' :: <?php echo $this->_tmp_folder->files+$this->_tmp_folder->folders; ?>"><img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" /></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo JRoute::_('index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=' . $this->_tmp_folder->path_relative); ?>" target="folderframe"><?php echo substr( $this->_tmp_folder->name, 0, 10 ) . ( strlen( $this->_tmp_folder->name ) > 10 ? '...' : ''); ?></a>
			</div>
		</div>
