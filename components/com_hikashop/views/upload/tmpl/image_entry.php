<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<?php if(!empty($this->params->delete) && !empty($this->params->uploader_id)) { ?>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['<?php echo $this->params->uploader_id; ?>'].delImage(this);"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" border="0"></a>
<?php } ?>
	<div class="hikashop_image"><?php
		if(empty($this->params->thumbnail_url)) {
			$img = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true));
			if($img->success) {
				$content = '<img src="'.$img->url.'" alt="'.$img->filename.'" />';
				echo $this->popup->image($content, $img->origin_url);
			}
		} else {
			$content = '<img src="' . $this->params->thumbnail_url . '" alt="'.@$this->params->file_path . '" />';
			echo $this->popup->image($content, $this->params->origin_url);
		}

		if(!empty($this->params->field_name))
			echo '<input type="hidden" name="'.$this->params->field_name.'" value="'.$this->escape(@$this->params->file_path).'"/>';
		if(!empty($this->params->extra_fields)) {
			foreach($this->params->extra_fields as $key => $value) {
				echo '<input type="hidden" name="'.$this->escape($key).'" value="'.$this->escape($value).'"/>';
			}
		}
	?></div>
</div>
