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
<?php
if(!empty($this->params->delete) && !empty($this->params->uploader_id)) {
	$p = '';
	if(!empty($this->params->field_name))
		$p = ',\'' . $this->params->field_name . '\'';
?>
	<a href="#delete" class="deleteImg" onclick="return window.hkUploaderList['<?php echo $this->params->uploader_id; ?>'].delImage(this<?php echo $p;?>);"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" border="0"></a>
<?php
}

if(empty($this->params->thumbnail_url))
	$img = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), null);
if(!empty($this->params->thumbnail_url) || (!empty($img) && $img->success)) {
?>
	<div class="hikashop_image"><?php
		$img_url = (!empty($this->params->thumbnail_url)) ? $this->params->thumbnail_url : $img->url;
		$origin_url = (!empty($this->params->origin_url)) ? $this->params->origin_url : @$img->origin_url;
		$filename = (!empty($img->filename)) ? $img->filename : $this->params->file_path;

		$content = '<img src="'.$img_url.'" alt="'.$filename.'" />';
		echo $this->popup->image($content, $origin_url);
	?></div>
<?php
} else {
	$this->params->empty = true;
}
if(!empty($this->params->field_name))
	echo '<input type="hidden" name="'.$this->params->field_name.'" value="'.$this->escape(@$this->params->file_path).'"/>';
if(!empty($this->params->extra_fields)) {
	foreach($this->params->extra_fields as $key => $value) {
		echo '<input type="hidden" name="'.$this->escape($key).'" value="'.$this->escape($value).'"/>';
	}
}
?>
</div>
