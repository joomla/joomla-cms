<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("product&task=selectimage&legacy=1&product_id=".@$this->element->product_id,true),
			'image_add_button',
			860, 480, '', '', 'button'
		);
	?>
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'go.png"/>'.JText::_('SELECT'),
			'SELECT',
			hikashop_completeLink("product&task=galleryimage&product_id=".@$this->element->product_id,true),
			'image_gallery_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title"><?php
								echo JText::_('HIKA_EDIT');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_IMAGE');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_NAME');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_ORDER');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_DELETE');
							?></th>
							<th class="title"><?php
								echo JText::_('ID');
							?></th>
						</tr>
					</thead>
					<tbody id="image_listing">
						<?php
							if(!empty($this->element->images) && isset($this->element->images[0])){
								$k = 0;
								for($i = 0,$a = count($this->element->images);$i<$a;$i++){
									$row =& $this->element->images[$i];
									$id=rand();
							?>
								<tr class="row<?php echo $k; ?>" id="image_<?php echo $row->file_id.'_'.$id;?>">
									<td>
										<?php
											echo $this->popup->display(
												'<img src="'.HIKASHOP_IMAGES.'edit.png"/>',
												'HIKA_EDIT',
												hikashop_completeLink("product&task=selectimage&legacy=1&cid=".$row->file_id."&product_id=".@$this->element->product_id.'&id='.$id,true ),
												'image_edit_button'.$row->file_id,
												860, 480, '', '', 'link'
											);
										?>
									</td>
									<td class="hikashop_product_image_thumbnail">
										<?php
										$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
										$img = $this->image->getThumbnail(@$row->file_path, array('width' => 100, 'height' => 100), $image_options);
										if(!empty($img) && $img->success) {
											echo '<img class="hikashop_product_image" title="'.$this->escape(@$row->file_description).'" alt="'.$this->escape(@$row->file_name).'" src="'.$img->url.'"/>';
										}
										?>
									</td>
									<td>
										<?php echo $row->file_name; ?>
									</td>
									<td class="order"><input type="text" size="5" value="<?php echo $row->file_ordering;?>" name="imageorder[<?php echo $row->file_id;?>]" class="text_area" style="text-align:center"/></td>
									<td width="1%" align="center">
										<a href="#" onclick="return deleteRow('image_div_<?php echo $row->file_id.'_'.$id;?>','image[<?php echo $row->file_id;?>][<?php echo $id;?>]','image_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
									</td>
									<td width="1%" align="center">
										<?php echo $row->file_id; ?>
										<div id="image_div_<?php echo $row->file_id.'_'.$id;?>">
											<input type="hidden" name="image[<?php echo $row->file_id;?>]" id="image[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
										</div>
									</td>
								</tr>
							<?php
									$k = 1-$k;
								}
							}
						?>
					</tbody>
				</table>
