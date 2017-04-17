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
?>
<?php
$height=$this->newSizes->height;
$width=$this->newSizes->width;
$duration=$this->params->get('product_effect_duration');
if(empty($duration)){ $duration=400; }

$pane_percent_height=$this->params->get('pane_height');
$link = $this->getLink($this->row);
$htmlLink="";
$cursor="";

if($this->params->get('link_to_product_page',1)){
	$htmlLink='onclick = "window.location.href = \''.$link.'\'';
	$cursor="cursor:pointer;";
}
?>
 <div id="window_<?php echo $this->row->category_id;  ?>" style="margin: auto; <?php echo $cursor; ?> height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; overflow:hidden; position:relative" <?php echo $htmlLink; ?>" >
 	<div id="product_<?php echo $this->row->category_id;  ?>" style="height:<?php echo $height; ?>px; width:<?php echo $width; ?>px; " >

		<!-- CATEGORY IMG -->
			<div style="height:<?php echo $this->image->main_thumbnail_y;?>px;width:<?php echo $this->image->main_thumbnail_x;?>px;text-align:center;margin:auto" class="hikashop_product_image">
				<a href="<?php echo $link;?>" title="<?php echo $this->escape($this->row->category_name); ?>">
					<?php
					$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
					$img = $this->image->getThumbnail(@$this->row->file_path, array('width' => $this->image->main_thumbnail_x, 'height' => $this->image->main_thumbnail_y), $image_options);
					if($img->success) {
						echo '<img class="hikashop_product_listing_image" title="'.$this->escape(@$this->row->file_description).'" alt="'.$this->escape(@$this->row->file_name).'" src="'.$img->url.'"/>';
					}
					?>
				</a>
			</div>
		<!-- EO CATEGORY IMG -->

		<?php
			$paneHeight='';
			if(!empty($pane_percent_height)){
				 $paneHeight='height:'.$pane_percent_height.'px;';
			}
		?>
		<div class="hikashop_img_pane_panel" style="width:<?php echo $width; ?>px; <?php echo $paneHeight; ?>">
			<!-- CATEGORY NAME -->
				<span class="hikashop_category_name">
					<a href="<?php echo $link;?>">
						<?php

						echo $this->row->category_name;
						if($this->params->get('number_of_products',0)){
							echo ' ('.$this->row->number_of_products.')';
						}
						?>
					</a>
				</span>
			<!-- EO CATEGORY NAME -->
		</div>
	</div>
</div>
