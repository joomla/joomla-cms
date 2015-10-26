<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$type = (!empty($this->params->product_type) && $this->params->product_type == 'variant') ? 'variant' : 'product';
?><a href="#delete" class="deleteImg" onclick="return window.productMgr.delImage(this, '<?php echo $type; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" border="0"></a>
<div class="hikashop_image">
<?php
	if(empty($this->params->file_id))
		$this->params->file_id = 0;
	$image = $this->imageHelper->getThumbnail(@$this->params->file_path, array(100, 100), array('default' => true));
	if(!empty($image) && $image->success) {
		$content = '<img src="'.$image->url.'" alt="'.$image->filename.'" />';
	} else {
		$content = '<img src="" alt="'.@$this->params->file_name.'" />';
	}
	echo $this->popup->display(
		$content,
		'HIKASHOP_IMAGE',
		hikashop_completeLink('product&task=selectimage&cid='.@$this->params->file_id.'&pid='.@$this->params->product_id,true),
		'',
		750, 460, 'onclick="return window.productMgr.editImage(this, '.$this->params->file_id.', \''.$type.'\');"', '', 'link'
	);
?>
</div><input type="hidden" name="data[<?php echo $type; ?>][product_images][]" value="<?php echo @$this->params->file_id;?>"/>
