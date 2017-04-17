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
$variant_name = '';
$variant_main = '_main';
$display_mode = '';
if(!empty($this->variant_name)) {
	$variant_name = $this->variant_name;
	if(substr($variant_name, 0, 1) != '_')
		$variant_name = '_' . $variant_name;
	$variant_main = $variant_name;
	$display_mode = 'display:none;';
}
?><div id="hikashop_product_image<?php echo $variant_main;?>" class="hikashop_global_image_div" style="<?php echo $display_mode;?>">
	<div id="hikashop_main_image_div<?php echo $variant_name;?>" class="hikashop_main_image_div">
		<?php
			if(!empty ($this->element->images)){
				$image = reset($this->element->images);
			}
			$height = (int)$this->config->get('product_image_y');
			$width = (int)$this->config->get('product_image_x');
			if(empty($height)) $height = (int)$this->config->get('thumbnail_y');
			if(empty($width)) $width = (int)$this->config->get('thumbnail_x');
			$divWidth = $width;
			$divHeight = $height;
			$this->image->checkSize($divWidth,$divHeight,$image);

			if (!$this->config->get('thumbnail')) {
				if(!empty ($this->element->images)){
					echo '<img src="' . $this->image->uploadFolder_url . $image->file_path . '" alt="' . $image->file_name . '" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
				}
			} else {
				$style = '';
				if (!empty ($this->element->images) && count($this->element->images) > 1) {
					if (!empty($height)) {
						$style = ' style="height:' . ($height + 20) . 'px;"';
					}
				}
				$variant_name='';
				if(isset($this->variant_name)){
					$variant_name=$this->variant_name;
				}

				?>

				<div class="hikashop_product_main_image_thumb" id="hikashop_image_main_thumb_div<?php echo $variant_name;?>" <?php echo $style;?> >
					<div style="<?php if(!empty($divHeight)){ echo 'height:'.($divHeight+20).'px;'; } ?>text-align:center;clear:both;" class="hikashop_product_main_image">
						<div style="position:relative;text-align:center;clear:both;<?php if(!empty($divWidth)){ echo 'width:'.$divWidth.'px;'; } ?>margin: auto;" class="hikashop_product_main_image_subdiv">
						<?php
							if($this->image->override) {
								echo $this->image->display(@$image->file_path,true,@$image->file_name,'id="hikashop_main_image'.$variant_name.'" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle"','id="hikashop_main_image_link"', $width,  $height);
							} else {
								if(empty($this->popup))
									$this->popup = hikashop_get('helper.popup');
								$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
								$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
								if(@$img->success) {
									$attr = 'title="'.$this->escape(@$image->file_description).'"';
									if (!empty ($this->element->images) && count($this->element->images) > 1) {
										$attr .= 'onclick="return window.localPage.openImage(\'hikashop_main_image'.$variant_name.'\', \''.$variant_name.'\', event);"';
									}
									$html = '<img id="hikashop_main_image'.$variant_name.'" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
									if(!empty($this->element->badges))
										$html .= $this->classbadge->placeBadges($this->image, $this->element->badges, '0', '0',false);

									echo $this->popup->image($html, $img->origin_url, null, $attr);
								}
							}
						?>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			</div>
			<div id="hikashop_small_image_div<?php echo $variant_name;?>" class="hikashop_small_image_div">
			<?php
			if (!empty ($this->element->images) && count($this->element->images) > 1) {
				$firstThunb = true;
				foreach ($this->element->images as $image) {
					if($this->image->override) {
						echo $this->image->display($image->file_path, 'hikashop_main_image'.$variant_name, $image->file_name, 'class="hikashop_child_image"','', $width,  $height);
					} else {
						if(empty($this->popup))
							$this->popup = hikashop_get('helper.popup');
						$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
						if(@$img->success) {
							$id = null;
							$classname='hikashop_child_image';
							if($firstThunb) {
								$id = 'hikashop_first_thumbnail'.$variant_name;
								$firstThunb = false;
								$classname.=' hikashop_child_image_active';
							}
							$attr = 'title="'.$this->escape(@$image->file_description).'" onmouseover="return window.localPage.changeImage(this, \'hikashop_main_image'.$variant_name.'\', \''.$img->url.'\', '.$img->width.', '.$img->height.', \''.str_replace("'","\'",@$image->file_description).'\', \''.str_replace("'","\'",@$image->file_name).'\');"';
							$html = '<img class="'.$classname.'" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
							if(empty($variant_name)) {
								echo $this->popup->image($html, $img->origin_url, $id, $attr, array('gallery' => 'hikashop_main_image'));
							} else {
								echo $this->popup->image($html, $img->origin_url, $id, $attr, array('gallery' => 'hikashop_main_image_VARIANT_NAME'));
							}
						}
					}
				}
			}

		?>
	</div>
</div>
<?php
if(empty($variant_name)) {
?>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
if(!window.localPage.images)
	window.localPage.images = {};
window.localPage.changeImage = function(el, id, url, width, height, title, alt) {
	var d = document, target = d.getElementById(id), w = window, o = window.Oby;
	if(!target) return false;
	target.src = url;
	target.width = width;
	target.height = height;
	target.title = title;
	target.alt = alt;
	var thumb_img = null, thumbs_div = d.getElementById('hikashop_small_image_div');
	if(thumbs_div) {
		thumbs_img = thumbs_div.getElementsByTagName('img');
		if(thumbs_img) {
			for(var i = thumbs_img.length - 1; i >= 0; i--) {
				o.removeClass(thumbs_img[i], 'hikashop_child_image_active');
			}
		}
	}
	thumb_img = el.getElementsByTagName('img');
	if(thumb_img) {
		for(var i = thumbs_img.length - 1; i >= 0; i--) {
			o.addClass(thumbs_img[i], 'hikashop_child_image_active');
		}
	}

	window.localPage.images[id] = el;
	return false;
};
window.localPage.openImage = function(id, variant_name, e) {
	if(!variant_name) variant_name = '';
	if(!window.localPage.images[id])
		window.localPage.images[id] = document.getElementById('hikashop_first_thumbnail' + variant_name);

	e = e || window.event;
	e.stopPropagation();
	e.cancelBubble = true;
	window.Oby.cancelEvent(e);
	window.localPage.images[id].click();
	return false;
};
</script>
<?php
}
