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
$this->setLayout('listing_price');
$this->params->set('show_quantity_field', 0);
$comp_description = $this->params->get('comp_description');
if(empty($comp_description)){
	$this->params->set('comp_description',JText::_('CART_EMPTY'));
}
if($this->paymentType == 'no'){
	$this->nextButton = false;
}
$app = JFactory::getApplication();
?>
<div id="hikashop_checkout_cart" class="hikashop_checkout_cart">
<?php
	if(empty($this->rows)) {
		echo $this->params->get('comp_description');
	} else {
		if($this->config->get('print_cart',0)&&JRequest::getVar('tmpl','')!='component'){ ?>
			<div class="hikashop_checkout_cart_print_link">
				<a title="<?php echo JText::_('HIKA_PRINT');?>" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink('checkout&task=printcart',true); ?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>print.png" alt="<?php echo JText::_('HIKA_PRINT');?>"/>
				</a>
			</div>
<?php
		}

		foreach($this->rows as $i => $row) {
			if(empty($row->cart_product_quantity)) continue;
			if(!empty($row->product_min_per_order)) {
				if($row->product_min_per_order>$row->cart_product_quantity) {
					$this->nextButton = false;
					$app->enqueueMessage(JText::sprintf('YOU_NEED_TO_ORDER_AT_LEAST_X_X',$row->product_min_per_order,$row->product_name));
				}
			}
		}
		$row_count = 4;
?>
	<br/>
		<table class="table table-striped table-hover" width="100%">
			<thead>
				<tr>
					<?php if($this->params->get('show_cart_image')){ $row_count++;?>
						<th id="hikashop_cart_product_image_title" class="hikashop_cart_product_image_title hikashop_cart_title">
							<?php echo JText::_('CART_PRODUCT_IMAGE'); ?>
						</th>
					<?php } ?>
					<th id="hikashop_cart_product_name_title" class="hikashop_cart_product_name_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_NAME'); ?>
					</th>
					<th id="hikashop_cart_product_price_title" class="hikashop_cart_product_price_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_UNIT_PRICE'); ?>
					</th>
					<th id="hikashop_cart_product_quantity_title" class="hikashop_cart_product_quantity_title hikashop_cart_title">
						<?php echo JText::_('PRODUCT_QUANTITY'); ?>
					</th>
					<th id="hikashop_cart_product_total_title" class="hikashop_cart_product_total_title hikashop_cart_title">
						<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php
					$k = 0;
					$group = $this->config->get('group_options',0);
					$defaultParams = $this->config->get('default_params');
					$productClass = hikashop_get('class.product');
					$quantityDisplayType = hikashop_get('type.quantitydisplay');

					foreach($this->rows as $i => $row){
						if(empty($row->cart_product_quantity)) continue;
						if($group && $row->cart_product_option_parent_id) continue;
						$productClass->addAlias($row);
						?>
						<tr class="row<?php echo $k; ?>">
							<?php if($this->params->get('show_cart_image')){ ?>
								<td data-title="<?php echo JText::_('CART_PRODUCT_IMAGE'); ?>" class="hikashop_cart_product_image_value">
									<?php
										$width = $this->config->get('thumbnail_x');
										$height = $this->config->get('thumbnail_y');
										if(!empty($row->images)){
											$image = reset($row->images);
											$this->image->checkSize($width,$height,$image);
										}else{
											$image = null;
										}
										if($image && !$this->config->get('thumbnail')){
											echo '<img src="'.$this->image->uploadFolder_url.$image->file_path.'" alt="'.$image->file_name.'" id="hikashop_main_image" style="margin-top:10px;margin-bottom:10px;display:inline-block;vertical-align:middle" />';
										}else{ ?>
											<div class="hikashop_cart_product_image_thumb" >
											<?php
												$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
												$img = $this->image->getThumbnail(@$image->file_path, array('width' => $width, 'height' => $height), $image_options);
												if($img->success) {
													echo '<img class="hikashop_product_checkout_cart_image" title="'.$this->escape(@$image->file_description).'" alt="'.$this->escape(@$image->file_name).'" src="'.$img->url.'"/>';
												}
												?>
											</div>
									<?php } ?>
								</td>
							<?php } ?>
							<td data-title="<?php echo JText::_('CART_PRODUCT_NAME'); ?>" class="hikashop_cart_product_name_value">
								<p class="hikashop_cart_product_name">
									<?php
										global $Itemid;
										$checkout_itemid = $this->config->get('checkout_itemid');
										if(!empty($checkout_itemid )){
											$Itemid = $checkout_itemid ;
										}
										$url_itemid='';
										if(!empty($Itemid)){
											$url_itemid='&Itemid='.$Itemid;
										} ?>
									<?php if(@$defaultParams['link_to_product_page']){ ?><a class="hikashop_no_print" href="<?php echo hikashop_contentLink('product&task=show&cid='.$row->product_id.'&name='.$row->alias.$url_itemid,$row);?>" ><?php } ?>
										<?php echo $row->product_name; ?>
										<?php if ($this->config->get('show_code')) { ?>
											<span class="hikashop_product_code_checkout"><?php echo $row->product_code; ?></span>
										<?php } ?>
									<?php if(@$defaultParams['link_to_product_page']){ ?></a><?php } ?>
									<?php
									if($group){
										$display_item_price=false;
										foreach($this->rows as $j => $optionElement){
											if($optionElement->cart_product_option_parent_id != $row->cart_product_id) continue;
											if(!empty($optionElement->prices[0])){
												$display_item_price = true;
											}

										}
										if($display_item_price){
											$this->row=&$row;
											$this->unit=true;
											echo  ' <span class="hikashop_product_base_price">'.strip_tags($this->loadTemplate()).'</span>';
										}
									}
									?>
								</p>
								<?php
								ob_start();
								if(hikashop_level(2) && !empty($this->extraFields['item'])){
									foreach($this->extraFields['item'] as $field){
										$namekey = $field->field_namekey;
										if(empty($row->$namekey) || !strlen($row->$namekey)) continue;
										echo '<p class="hikashop_cart_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$row->$namekey).'</p>';
									}
								}
								$input='';
								if($group){
									foreach($this->rows as $j => $optionElement){
										if($optionElement->cart_product_option_parent_id != $row->cart_product_id) continue;
										if(!empty($optionElement->prices[0])){
											if(!isset($row->prices[0])){
												$row->prices[0] = new stdClass();
												$row->prices[0]->price_value=0;
												$row->prices[0]->price_value_with_tax=0;
												$row->prices[0]->price_currency_id = hikashop_getCurrency();
											}
											foreach(get_object_vars($row->prices[0]) as $key => $value){
												if(is_object($value)){
													foreach(get_object_vars($value) as $key2 => $var2){
														if(strpos($key2,'price_value')!==false) $row->prices[0]->$key->$key2 +=@$optionElement->prices[0]->$key->$key2;
													}
												}else{
													if(strpos($key,'price_value')!==false) $row->prices[0]->$key+=@$optionElement->prices[0]->$key;
												}
											}
										}
										if(empty($optionElement->variant_name)){
											if(empty($optionElement->characteristics_text)){
												$text = $optionElement->product_name;
											}else{
												$text = $optionElement->characteristics_text;
											}
										}else{
											$text = $optionElement->variant_name;
										}
										echo '<p class="hikashop_cart_option_name">'.$text;
										if(@$optionElement->prices[0]->price_value_with_tax>0){
											echo ' ( + ';
											$this->row=&$optionElement;
											$this->unit=true;
											echo strip_tags($this->loadTemplate()).' )';
										}
										echo '</p>';
										$input .='document.getElementById(\'product_option_'.$optionElement->cart_product_id.'\').value=qty_field.value;';
										if(empty($this->disable_modifications)) echo '<input type="hidden" id="product_option_'.$optionElement->cart_product_id.'" name="item['.$optionElement->cart_product_id.']" value="'.$row->cart_product_quantity.'"/>';
									}
								}
								$html = ob_get_clean();
								if(!empty($html)){
									echo '<div class="hikashop_cart_product_custom_item_fields">'.$html.'</div>';
								}
								?>
							</td>
							<td data-title="<?php echo JText::_('CART_PRODUCT_UNIT_PRICE'); ?>" class="hikashop_cart_product_price_value">
								<?php
									$this->row =& $row;
									$this->unit = true;
									echo $this->loadTemplate();
								?>
								<?php if(HIKASHOP_RESPONSIVE){ ?><span class="visible-phone"><?php echo JText::_('PER_UNIT'); ?></span><?php } ?>
							</td>
							<td data-title="<?php echo JText::_('PRODUCT_QUANTITY'); ?>" class="hikashop_cart_product_quantity_value">
								<?php
								if(empty($this->disable_modifications)){
									if($row->product_parent_id != 0 && isset($row->main_product_quantity_layout)){
										$row->product_quantity_layout = $row->main_product_quantity_layout;
									}
									if($row->product_quantity_layout == 'show_select' || (empty($row->product_quantity_layout) && $this->config->get('product_quantity_display', '') == 'show_select')){
										$min_quantity = $this->row->product_min_per_order;
										$max_quantity = $this->row->product_max_per_order;
										if($min_quantity == 0)
											$min_quantity = 1;
										if($max_quantity == 0)
											$max_quantity = (int)$min_quantity * 15;
										$values = array();
										if($this->params->get('show_delete',1)){
											$values[] = JHTML::_('select.option', 0, '0');
										}
										for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity){
											$values[] = JHTML::_('select.option', $j, $j);
										}
										echo JHTML::_('select.genericlist', $values, 'item['.$row->cart_product_id.']', '', 'value', 'text', $row->cart_product_quantity,'hikashop_checkout_quantity_'.$row->cart_product_id);
									}else{
										?>
										<input id="hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>" type="text" name="item[<?php echo $row->cart_product_id;?>]" class="hikashop_product_quantity_field" value="<?php echo $row->cart_product_quantity; ?>" onchange="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field){<?php echo $input; ?>}; return true;" />
										<?php
									}
								?>
									<div class="hikashop_cart_product_quantity_refresh">
										<a class="hikashop_no_print" href="#" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field && qty_field.value != '<?php echo $row->cart_product_quantity; ?>'){<?php echo $input; ?> qty_field.form.submit(); } return false;" title="<?php echo JText::_('HIKA_REFRESH'); ?>">
											<img src="<?php echo HIKASHOP_IMAGES . 'refresh.png';?>" border="0" alt="<?php echo JText::_('HIKA_REFRESH'); ?>" />
										</a>
									</div>
									<?php if($this->params->get('show_delete',1)){ ?>
										<div class="hikashop_cart_product_quantity_delete">
											<a class="hikashop_no_print" href="<?php echo hikashop_completeLink('product&task=updatecart&product_id='.$row->product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($this->params->get('url'))))); ?>" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if(qty_field){qty_field.value=0; <?php echo $input; ?> qty_field.form.submit();} return false;" title="<?php echo JText::_('HIKA_DELETE'); ?>">
												<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" />
											</a>
										</div>
									<?php }
								}else{
									echo $row->cart_product_quantity;
								}
								 ?>
							</td>
							<td data-title="<?php echo JText::_('CART_PRODUCT_TOTAL_PRICE'); ?>" class="hikashop_cart_product_total_value">
								<?php
									$this->row=&$row;
									$this->unit=false;
									echo $this->loadTemplate();
								?>
							</td>
						</tr>
						<?php
						$k = 1-$k;
					}
				?>

<?php

		if($this->params->get('show_cart_image'))
			$td = '<td colspan="3" class="hikashop_cart_empty_footer"></td>';
		else
			$td = '<td colspan="2" class="hikashop_cart_empty_footer"></td>';

		$taxes = round($this->full_total->prices[0]->price_value_with_tax-$this->full_total->prices[0]->price_value,$this->currencyHelper->getRounding($this->full_total->prices[0]->price_currency_id));
		if(!empty($this->coupon) || !empty($this->shipping) || !empty($this->additional) || bccomp($taxes,0,5)) {
?>
						<tr class="margin"><?php echo $td; ?><td></td></tr>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_total2_title" class="hikashop_cart_subtotal_title hikashop_cart_title">
						<?php echo JText::_('SUBTOTAL'); ?>
					</td>
					<td class="hikashop_cart_subtotal_value" data-title="<?php echo JText::_('SUBTOTAL'); ?>">
						<span class="hikashop_checkout_cart_subtotal">
							<?php
								if($this->params->get('price_with_tax')){
									echo $this->currencyHelper->format(@$this->total->prices[0]->price_value_with_tax,@$this->total->prices[0]->price_currency_id);
								}else{
									echo $this->currencyHelper->format(@$this->total->prices[0]->price_value,@$this->total->prices[0]->price_currency_id);
								}
							?>
						</span>
					</td>
				</tr>
<?php
		}
		if(!empty($this->coupon)) {
?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_coupon_title" class="hikashop_cart_coupon_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_COUPON'); ?>
					</td>
					<td class="hikashop_cart_coupon_value" data-title="<?php echo JText::_('HIKASHOP_COUPON'); ?>">
						<span class="hikashop_checkout_cart_coupon">
						<?php
							if(bccomp($taxes,0,5)==0 || !$this->params->get('price_with_tax')){
								echo $this->currencyHelper->format(@$this->coupon->discount_value_without_tax*-1,@$this->coupon->discount_currency_id);
							}else{
								echo $this->currencyHelper->format(@$this->coupon->discount_value*-1,@$this->coupon->discount_currency_id);
							}
						?>
						</span>
					</td>
				</tr>
<?php
		}

		if(!empty($this->shipping)) {
?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_shipping_title" class="hikashop_cart_shipping_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_SHIPPING'); ?>
					</td>
					<td class="hikashop_cart_shipping_value" data-title="<?php echo JText::_('HIKASHOP_SHIPPING'); ?>">
						<span class="hikashop_checkout_cart_shipping">
<?php
			if(isset($this->value)) {
				echo $this->value;
			} else {
				$shipping_price = null;
				foreach($this->shipping as $shipping) {
					if(!isset($shipping->shipping_price) && isset($shipping->shipping_price_with_tax) ) {
						$shipping->shipping_price = $shipping->shipping_price_with_tax;
					}
					if(isset($shipping->shipping_price)) {
						if($shipping_price === null)
							$shipping_price = 0.0;
						if(bccomp($taxes,0,5)==0 || !$this->params->get('price_with_tax') || !isset($shipping->shipping_price_with_tax)) {
							$shipping_price += $shipping->shipping_price;
						} else {
							$shipping_price += $shipping->shipping_price_with_tax;
						}
					}
				}
				if($shipping_price !== null) {
					echo $this->currencyHelper->format($shipping_price, $this->full_total->prices[0]->price_currency_id);
				}
			}
?>
						</span>
					</td>
				</tr>
<?php
		}

		if(!empty($this->additional)) {
			$exclude_additionnal = explode(',', $this->config->get('order_additional_hide', ''));
			foreach($this->additional as $k => $additional) {
				if(in_array($additional->name, $exclude_additionnal))
					continue;
?>
				<tr id="hikashop_checkout_cart_additional_<?php echo str_replace(' ','_',$k); ?>_line" >
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_additional_<?php echo str_replace(' ','_',$k); ?>_title" class="hikashop_cart_additional_title hikashop_cart_title"><?php
						echo JText::_($additional->name);
					?></td>
					<td class="hikashop_cart_additional_value" data-title="<?php echo JText::_($additional->name); ?>">
						<span class="hikashop_checkout_cart_additional">
<?php
				if(!empty($additional->price_value) || empty($additional->value)) {
					if(bccomp($taxes,0,5)==0 || !$this->params->get('price_with_tax')){
						echo $this->currencyHelper->format(@$additional->price_value,$additional->price_currency_id);
					}else{
						echo $this->currencyHelper->format(@$additional->price_value_with_tax,$additional->price_currency_id);
					}
				} else {
					echo $additional->value;
				}
?>
						</span>
					</td>
				</tr>
<?php
			}
		}

		if(bccomp($taxes,0,5)){
			if($this->config->get('detailed_tax_display') && isset($this->full_total->prices[0]->taxes)) {
				foreach($this->full_total->prices[0]->taxes as $tax) {
?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_tax_title" class="hikashop_cart_tax_title hikashop_cart_title">
						<?php echo $tax->tax_namekey; ?>
					</td>
					<td class="hikashop_cart_tax_value" data-title="<?php echo $tax->tax_namekey; ?>">
						<span class="hikashop_checkout_cart_taxes">
						<?php
							echo $this->currencyHelper->format($tax->tax_amount,$this->full_total->prices[0]->price_currency_id);
						?>
						</span>
					</td>
				</tr>
<?php
				}
			} else {
?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_tax_title" class="hikashop_cart_tax_title hikashop_cart_title">
						<?php echo JText::_('TAXES'); ?>
					</td>
					<td class="hikashop_cart_tax_value" data-title="<?php echo Jtext::_('TAXES'); ?>">
						<span class="hikashop_checkout_cart_taxes">
						<?php
							echo $this->currencyHelper->format($taxes,$this->full_total->prices[0]->price_currency_id);
						?>
						</span>
					</td>
				</tr>
<?php
			}
		}

		 if(!empty($this->payment) && $this->payment->payment_price != 0) {
?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_payment_title" class="hikashop_cart_payment_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_PAYMENT'); ?>
					</td>
					<td class="hikashop_cart_payment_value" data-title="<?php echo Jtext::_('HIKASHOP_PAYMENT'); ?>">
						<span class="hikashop_checkout_cart_payment">
						<?php
							if(!isset($this->payment->payment_price) && isset($this->payment->payment_price_with_tax) ) {
								if(isset($this->value)) {
									echo $this->value;
								} else {
									$this->payment->payment_price = 0.0;
									$this->payment->payment_price_with_tax = 0.0;
								}
							}
							if(isset($this->payment->payment_price)) {
								if(bccomp($taxes,0,5)==0 || !$this->params->get('price_with_tax') || !isset($this->payment->payment_price_with_tax) ){
									echo $this->currencyHelper->format(@$this->payment->payment_price,$this->full_total->prices[0]->price_currency_id);
								}else{
									echo $this->currencyHelper->format(@$this->payment->payment_price_with_tax,$this->full_total->prices[0]->price_currency_id);
								}
							}
						?>
						</span>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<?php echo $td; ?>
					<td id="hikashop_checkout_cart_final_total_title" class="hikashop_cart_total_title hikashop_cart_title">
						<?php echo JText::_('HIKASHOP_TOTAL'); ?>
					</td>
					<td class="hikashop_cart_total_value" data-title="<?php echo Jtext::_('HIKASHOP_TOTAL'); ?>">
						<span class="hikashop_checkout_cart_final_total">
						<?php
							echo $this->currencyHelper->format($this->full_total->prices[0]->price_value_with_tax,$this->full_total->prices[0]->price_currency_id);
						?>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
		if(empty($this->disable_modifications) && $this->params->get('show_quantity')){ ?>
			<noscript>
				<input id="hikashop_checkout_cart_quantity_button" class="btn button" type="submit" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
			</noscript>
		<?php }
	}
	$config =& hikashop_config();
	if(hikashop_level(1) && $config->get('checkout_convert_cart') && $config->get('enable_wishlist') && (($config->get('hide_wishlist_guest', 1) && hikashop_loadUser() != null) || !$config->get('hide_wishlist_guest', 1))){
		$this->params->set('cart_type','wishlist');
		echo $this->cart->displayButton(JText::_('CART_TO_WISHLIST'),'wishlist',$this->params,hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$this->full_cart->cart_id.$url_itemid),'window.location.href = \''.hikashop_completeLink('cart&task=convert&cart_type=cart&cart_id='.$this->full_cart->cart_id.$url_itemid).'\';return false;');
	}
?>
</div>
