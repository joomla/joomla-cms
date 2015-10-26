<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $link = $this->getLink($this->row);?>
<table>
	<tr>
		<!-- CATEGORY IMG -->
		<?php if($this->config->get('thumbnail',1)){ ?>
		<td width="<?php echo $this->image->main_thumbnail_x+30;?>px">
			<div class="hikashop_category_left_part" style="text-align:center;">
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
			</div>
		</td>
		<?php } ?>
		<!--EO CATEGORY IMG -->
		<td valign="top">
			<div class="hikashop_category_right_part">
				<h2>
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
				</h2>

				<!-- CATEGORY DESC -->
				<span class="hikashop_category_desc" style="text-align:<?php echo $this->align; ?>;">
					<?php
					echo preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->row->category_description);
					?>
				</span>
				<!-- EO CATEGORY DESC -->
			</div>
		</td>
	</tr>
</table>
