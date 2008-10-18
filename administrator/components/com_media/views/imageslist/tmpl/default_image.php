<?php defined('_JEXEC') or die('Restricted access'); ?>
		<div class="item">
			<a href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_img->path_relative; ?>')">
				<img src="<?php echo $this->baseURL.'/'.$this->_tmp_img->path_relative; ?>"  width="<?php echo $this->_tmp_img->width_60; ?>" height="<?php echo $this->_tmp_img->height_60; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo MediaHelper::parseSize($this->_tmp_img->size); ?>" />
				<span><?php echo $this->_tmp_img->name; ?></span></a>
		</div>
