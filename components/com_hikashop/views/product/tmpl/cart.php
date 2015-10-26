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

if($this->cart_type == 'cart') {
	$convertText = JText::_('CART_TO_WISHLIST');
	$displayText = JText::_('DISPLAY_THE_CART');
	$displayAllText = JText::_('DISPLAY_THE_CARTS');
	$emptyText = JText::_('CART_EMPTY');
} else {
	$convertText = JText::_('WISHLIST_TO_CART');
	$displayText = JText::_('DISPLAY_THE_WISHLIST');
	$displayAllText = JText::_('DISPLAY_THE_WISHLISTS');
	$emptyText = JText::_('WISHLIST_EMPTY');
}

$desc = trim($this->params->get('msg'));
if(empty($desc) && $desc != '0') {
	$this->params->set('msg', $emptyText);
	$desc = $emptyText;
}

if(empty($this->rows)) {
	if(!empty($desc) || $desc == '0') {
		echo $this->notice_html;
?>
		<div id="hikashop_cart" class="hikashop_cart"><?php
			echo $desc;
		?></div>
		<div class="clear_both"></div>
<?php
	}

	if(JRequest::getWord('tmpl', '') == 'component') {
		if(!headers_sent())
			header('Content-Type: text/css; charset=utf-8');
		exit;
	}
	return;
}

$this->setLayout('listing_price');
$this->params->set('show_quantity_field', 0);

?>
<div id="hikashop_cart" class="hikashop_cart">
<?php
	if($this->config->get('print_cart', 0) && JRequest::getVar('tmpl', '') != 'component' && $this->cart_type == 'cart') {
?>
	<div class="hikashop_checkout_cart_print_link">
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'print.png" alt="'.JText::_('HIKA_PRINT').'" />',
			'HIKA_PRINT',
			hikashop_completeLink('checkout&task=printcart',true),
			'hikashop_print_popup',
			760, 480, '', '', 'link'
		);
	?>
	</div>
<?php
	} else if($this->params->get('from','display') != 'module') {
?>
	<div class="hikashop_product_cart_links">
		<div class="hikashop_product_cart_show_carts_link"><?php
			echo $this->cartHelper->displayButton($displayAllText, 'cart', $this->params, hikashop_completeLink('cart&task=showcarts&cart_type='.$this->cart_type . $this->cart_itemid), '');
		?>
		</div>
		<div class="hikashop_product_cart_mail_link">
		<?php
			echo $this->popup->display(
				'<img src="'.HIKASHOP_IMAGES.'go.png" alt="'.JText::_('HIKA_EMAIL').'" />',
				'HIKA_EMAIL',
				hikashop_completeLink('product&task=sendcart',true),
				'hikashop_email_popup',
				760, 480, '', '', 'link'
			);
		?>
		</div>
		<div class="hikashop_product_cart_print_link">
		<?php
			echo $this->popup->display(
				'<img src="'.HIKASHOP_IMAGES.'print.png" alt="'.JText::_('HIKA_PRINT').'" />',
				'HIKA_PRINT',
				hikashop_completeLink('product&task=printcart',true),
				'hikashop_print_popup',
				760, 480, '', '', 'link'
			);
		?>
		</div>
	</div>
	<div class="clear_both"></div>
<?php
	}

	echo $this->notice_html;
	$row_count = 0;

	if($this->params->get('small_cart')) {
		$this->row = $this->total;
		if($this->params->get('show_cart_quantity', 1)) {
			$qty = 0;
			$group = $this->config->get('group_options', 0);
			foreach($this->rows as $i => $row) {
				if(empty($row->cart_product_quantity) && $this->cart_type == 'cart')
					continue;
				if($group && $row->cart_product_option_parent_id)
					continue;

				$qty += $row->cart_product_quantity;
			}

			if($qty == 1 && JText::_('X_ITEM_FOR_X') != 'X_ITEM_FOR_X') {
				$text = JText::sprintf('X_ITEM_FOR_X', $qty, $this->loadTemplate());
			} else {
				$text = JText::sprintf('X_ITEMS_FOR_X', $qty, $this->loadTemplate());
			}
		} else {
			$text = JText::sprintf('TOTAL_IN_CART_X', $this->loadTemplate());
		}

		if($this->cart_type == 'cart') {
?>
			<a class="hikashop_small_cart_checkout_link" href="<?php echo $this->url_checkout; ?>">
				<span class="hikashop_small_cart_total_title"><?php echo $text; ?></span>
			</a>
<?php
			if($this->params->get('show_cart_delete',1)) {
				$delete = hikashop_completeLink('product&task=cleancart');
				$delete .= (strpos($delete, '?') ? '&amp;' : '?');
?>
			<a class="hikashop_small_cart_clean_link" onclick="window.location='<?php echo $delete.'return_url='; ?>'+window.btoa(window.location); return false;" href="#" >
				<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="clean cart" />
			</a>
<?php
			}
			if($this->params->get('show_cart_proceed', 1) && $this->cart_type == 'cart' && $this->params->get('from','display') == 'module') {
				echo $this->cartHelper->displayButton(JText::_('PROCEED_TO_CHECKOUT'),'checkout',$this->params,$this->url_checkout,'this.disabled=true; window.location=\''.$this->url_checkout.'\';return false;');
			}
		} else {
?>
			<a class="hikashop_small_cart_checkout_link" href="<?php echo hikashop_completeLink('cart&task=showcart&cart_id='.$this->element->cart_id.'&cart_type='.$this->cart_type.$this->cart_itemid); ?>">
				<span class="hikashop_small_cart_total_title"><?php echo $text; ?></span>
			</a>
<?php
		}
	} else {
		$form = 'hikashop_' . $this->cart_type . '_form';
?>
	<form action="<?php echo hikashop_completeLink('product&task=updatecart'.$this->url_itemid, false, true); ?>" method="post" name="<?php echo $form;?>">
		<table width="100%">
			<thead>
				<tr>
<?php
		if($this->params->get('image_in_cart', 0)) {
			$row_count++;
?>
					<th class="hikashop_cart_module_product_image_title hikashop_cart_title"><?php
						echo JText::_('CART_PRODUCT_IMAGE');
					?></th>
<?php
		}
		if($this->params->get('show_cart_product_name', 1)) {
		 	$row_count++;
?>
					<th class="hikashop_cart_module_product_name_title hikashop_cart_title"><?php
						echo JText::_('CART_PRODUCT_NAME');
					?></th>
<?php
		}
		if($this->params->get('show_cart_quantity', 1)) {
			$row_count++;
?>
					<th class="hikashop_cart_module_product_quantity_title hikashop_cart_title"><?php
						echo JText::_('CART_PRODUCT_QUANTITY');
					?></th>
<?php
		}
		if($this->params->get('show_price', 1)) {
			$row_count++;
?>
					<th class="hikashop_cart_module_product_price_title hikashop_cart_title"><?php
						echo JText::_('CART_PRODUCT_PRICE');
					?></th>
<?php
		}
		if($this->params->get('show_cart_delete', 1)) {
			$row_count++;
?>
					<th class="hikashop_cart_title"></th>
<?php
		}
		if($row_count < 1) {
?>
					<th></th>
<?php
		}
?>
				</tr>
			</thead>
<?php if($this->params->get('show_price', 1) && $this->cart_type == 'cart') { ?>
			<tfoot>
				<tr>
					<td colspan="<?php echo $row_count; ?>">
						<hr />
					</td>
				</tr>
<?php if($this->params->get('show_coupon', 0) && !empty($this->element->coupon)) { ?>
				<tr>
<?php
			switch($row_count) {
				case 0:
				case 1:
?>
					<td class="hikashop_cart_module_coupon_value"><?php
						if(!$this->params->get('price_with_tax'))
							echo $this->currencyClass->format(@$this->element->coupon->discount_value_without_tax * -1, @$this->element->coupon->discount_currency_id);
						else
							echo $this->currencyClass->format(@$this->element->coupon->discount_value * -1, @$this->element->coupon->discount_currency_id);
					?></td>
<?php
					break;

				default:
					$colspan = $row_count - 1;
					if($this->params->get('show_cart_delete', 1))
						$colspan--;

					if($colspan > 0) {
?>
					<td class="hikashop_cart_module_coupon_title" colspan="<?php echo $colspan; ?>">
						<?php echo JText::_('HIKASHOP_COUPON'); ?>
					</td>
<?php
					}
?>
					<td class="hikashop_cart_module_coupon_value"><?php
						if(!$this->params->get('price_with_tax'))
							echo $this->currencyClass->format(@$this->element->coupon->discount_value_without_tax * -1, @$this->element->coupon->discount_currency_id);
						else
							echo $this->currencyClass->format(@$this->element->coupon->discount_value * -1, @$this->element->coupon->discount_currency_id);
					?></td>
<?php
					if($this->params->get('show_cart_delete', 1)) {
?>
					<td></td>
<?php
					}
					break;
			}
?>
				</tr>
<?php } ?>
<?php
		if($this->params->get('show_shipping', 0) && !empty($this->element->shipping)) {
			$shipping_price = null;
			foreach($this->element->shipping as $shipping) {
				if(!isset($shipping->shipping_price) && isset($shipping->shipping_price_with_tax) ) {
					$shipping->shipping_price = $shipping->shipping_price_with_tax;
				}
				if(isset($shipping->shipping_price)) {
					if($shipping_price === null)
						$shipping_price = 0.0;
					if(!$this->params->get('price_with_tax') || !isset($shipping->shipping_price_with_tax)) {
						$shipping_price += $shipping->shipping_price;
					} else {
						$shipping_price += $shipping->shipping_price_with_tax;
					}
				}
			}
			if($shipping_price !== null) {
				$shipping_price = $this->currencyClass->format($shipping_price, $this->total->prices[0]->price_currency_id);
			}
			if($shipping_price) {
?>
				<tr>
<?php
				switch($row_count) {
					case 0:
					case 1:
?>
					<td class="hikashop_cart_module_shipping_value"><?php
						echo $shipping_price;
					?></td>
<?php
						break;

					default:
						$colspan = $row_count - 1;
						if($this->params->get('show_cart_delete', 1))
							$colspan--;
						if($colspan > 0) {
?>
					<td class="hikashop_cart_module_shipping_title" colspan="<?php echo $colspan; ?>"><?php
						echo JText::_('HIKASHOP_SHIPPING');
					?></td>
<?php
						}
?>
					<td class="hikashop_cart_module_shipping_value"><?php
						echo $shipping_price;
					?></td>
<?php
						if($this->params->get('show_cart_delete', 1)) {
?>
					<td>
					</td>
<?php
						}
						break;
				}
?>
				</tr>
<?php
			}
		}
?>
				<tr>
<?php
		switch($row_count) {
			case 0:
			case 1:
?>
					<td class="hikashop_cart_module_product_total_value"><?php
						$this->row = $this->total;
						echo $this->loadTemplate();
					?></td>
<?php
				break;

			default:
				$colspan = $row_count - 1;
				if($this->params->get('show_cart_delete', 1))
					$colspan--;

				if($colspan > 0) {
?>
					<td class="hikashop_cart_module_product_total_title" colspan="<?php echo $colspan; ?>"><?php
						echo JText::_('HIKASHOP_TOTAL');
					?></td>
<?php
				}
?>
					<td class="hikashop_cart_module_product_total_value"><?php
						$this->row = $this->total;
						echo $this->loadTemplate();
					?></td>
<?php
				if($this->params->get('show_cart_delete', 1)) {
?>
					<td>
					</td>
<?php
				}
				break;
		}
?>
				</tr>
			</tfoot>
<?php } ?>
			<tbody>
<?php
	$this->cart_product_price = true;

	$group = $this->config->get('group_options',0);
	$defaultParams = $this->config->get('default_params');

	$image_height = $this->config->get('thumbnail_y');
	$image_width = $this->config->get('thumbnail_x');
	$image_options = array(
		'default' => true,
		'forcesize' => $this->config->get('image_force_size',true),
		'scale' => $this->config->get('image_scale_mode','inside')
	);

	$k = 0;
	foreach($this->rows as $i => $row) {
		if(empty($row->cart_product_quantity) || @$row->hide == 1)
			continue;
		if($group && $row->cart_product_option_parent_id)
			continue;

		$this->productClass->addAlias($row);
		$input = '';
?>
				<tr class="row<?php echo $k; ?>">
<?php
		if(@$this->params->get('image_in_cart')) {
?>
					<td class="hikashop_cart_module_product_image hikashop_cart_value" style="vertical-align:middle !important; text-align:center;">
<?php
			$img = $this->image->getThumbnail(@$row->images[0]->file_path, array('width' => $image_width, 'height' => $image_height), $image_options);
			if($img->success)
				echo '<img class="hikashop_product_cart_image" title="'.$this->escape(@$row->images[0]->file_description).'" alt="'.$this->escape(@$row->images[0]->file_name).'" src="'.$img->url.'"/>';
?>
					</td>
<?php
		}

		if($this->params->get('show_cart_product_name', 1)) {
?>
					<td class="hikashop_cart_module_product_name_value hikashop_cart_value">
						<?php if(@$defaultParams['link_to_product_page']) { ?> <a href="<?php echo hikashop_contentLink('product&task=show&cid='.$row->product_id.'&name='.$row->alias.$this->url_itemid, $row);?>" ><?php } ?>
						<?php echo $row->product_name; ?>
						<?php if ($this->config->get('show_code')) { ?><span class="hikashop_product_code_cart"><?php echo $row->product_code; ?></span><?php } ?>
						<?php if(@$defaultParams['link_to_product_page']) { ?></a><?php } ?>
<?php
			ob_start();
			if(hikashop_level(2) && !empty($this->itemFields)) {
				foreach($this->itemFields as $field) {
					$namekey = $field->field_namekey;
					if(!empty($row->$namekey) && strlen($row->$namekey)) {
						echo '<p class="hikashop_cart_item_'.$namekey.'">' .
							$this->fieldsClass->getFieldName($field) . ': ' .
							$this->fieldsClass->show($field, $row->$namekey) .
							'</p>';
					}
				}
			}
			if($group) {
				foreach($this->rows as $j => $optionElement) {
					if($optionElement->cart_product_option_parent_id != $row->cart_product_id)
						continue;
					if(empty($optionElement->variant_name)){
						if(empty($optionElement->characteristics_text)){
							$text = $optionElement->product_name;
						}else{
							$text = $optionElement->characteristics_text;
						}
					}else{
						$text = $optionElement->variant_name;
					}
					echo '<p class="hikashop_cart_option_name">'. $text.'</p>';
					$input .='document.getElementById(\'cart_product_option_'.$optionElement->cart_product_id.'\').value=qty_field.value;';
					echo '<input type="hidden" id="cart_product_option_'.$optionElement->cart_product_id.'" name="item['.$optionElement->cart_product_id.'][cart_product_quantity]" value="'.$row->cart_product_quantity.'"/>';
				}
			}

			$html = ob_get_clean();
			if(!empty($html)) {
				echo '<p class="hikashop_cart_product_custom_item_fields">'.$html.'</p>';
			}
?>
					</td>
<?php
		}

		if($group) {
			foreach($this->rows as $j => $optionElement) {
				if($optionElement->cart_product_option_parent_id != $row->cart_product_id)
					continue;
				if(empty($optionElement->prices[0]))
					continue;
				if(!isset($row->prices[0])) {
					$row->prices[0]->price_value = 0;
					$row->prices[0]->price_value_with_tax = 0;
					$row->prices[0]->price_currency_id = hikashop_getCurrency();
				}
				foreach(get_object_vars($row->prices[0]) as $key => $value) {
					if(strpos($key, 'price_value') === false)
						continue;
					if(is_object($value)) {
						foreach(get_object_vars($value) as $key2 => $var2) {
							$row->prices[0]->$key->$key2 += @$optionElement->prices[0]->$key->$key2;
						}
					} else {
						$row->prices[0]->$key += @$optionElement->prices[0]->$key;
					}
				}
			}
		}

		if($this->params->get('show_cart_quantity', 1)) {
?>
					<td class="hikashop_cart_module_product_quantity_value hikashop_cart_value">
<?php
			if(empty($session))
				$session = new stdClass();
			$session->cart_id = $this->app->getUserState( HIKASHOP_COMPONENT.'.'.$this->cart_type.'_id', 0, 'int' );

			if($row->product_parent_id != 0 && isset($row->main_product_quantity_layout))
				$row->product_quantity_layout = $row->main_product_quantity_layout;
			if($this->element->cart_id == $session->cart_id && $this->params->get('from','display') != 'module') {
				if($row->product_quantity_layout == 'show_select' || (empty($this->row->product_quantity_layout) && $this->config->get('product_quantity_display', '') == 'show_select')) {
					$min_quantity = $row->product_min_per_order;
					$max_quantity = $row->product_max_per_order;
					if($min_quantity == 0)
						$min_quantity = 1;
					if($max_quantity == 0)
						$max_quantity = (int)$min_quantity * 15;
?>
						<select id="hikashop_wishlist_quantity_select_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>" onchange="var qty_field = document.getElementById('hikashop_wishlist_quantity_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>'); qty_field.value = this.value; if (qty_field){<?php echo $input; ?> } document.<?php echo $form; ?>.submit(); return false;">
<?php
							for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity) {
								$selected = '';
								if($j == $row->cart_product_quantity)
									$selected = 'selected="selected"';
								echo '<option value="'.$j.'" '.$selected.'>'.$j.'</option>';
							}
?>
						</select>
						<input id="hikashop_wishlist_quantity_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>" type="hidden" name="item[<?php echo $row->cart_product_id;?>][cart_product_quantity]" value="<?php echo $row->cart_product_quantity; ?>"/>
<?php
				} else {
?>
						<input id="hikashop_wishlist_quantity_<?php echo $row->cart_product_id;?>" type="text" name="item[<?php echo $row->cart_product_id;?>][cart_product_quantity]" class="hikashop_product_quantity_field" value="<?php echo $row->cart_product_quantity; ?>" onchange="var qty_field = document.getElementById('hikashop_wishlist_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field){<?php echo $input; ?> } document.<?php echo $form; ?>.submit(); return false;" />
<?php
				}
?>

						<div class="hikashop_cart_product_quantity_refresh">
							<a href="#" onclick="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field && qty_field.value != '<?php echo $row->cart_product_quantity; ?>'){<?php echo $input; ?> qty_field.form.submit(); } return false;" title="<?php echo JText::_('HIKA_REFRESH'); ?>">
								<img src="<?php echo HIKASHOP_IMAGES . 'refresh.png';?>" border="0" alt="<?php echo JText::_('HIKA_REFRESH'); ?>" />
							</a>
						</div>
<?php
			} else {
				if($row->product_quantity_layout == 'show_select' || (empty($row->product_quantity_layout) && $this->config->get('product_quantity_display', '') == 'show_select')) {
					$min_quantity = $row->product_min_per_order;
					$max_quantity = $row->product_max_per_order;
					if($min_quantity == 0)
						$min_quantity = 1;
					if($max_quantity == 0)
						$max_quantity = (int)$min_quantity * 15;
?>
						<select id="hikashop_cart_quantity_select_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>" class="tochosen" onchange="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>'); qty_field.value = this.value; if (qty_field){<?php echo $input; ?> } document.<?php echo $form; ?>.submit(); return false;">
<?php
							for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity){
								$selected = '';
								if($j == $row->cart_product_quantity)
									$selected = 'selected="selected"';
								echo '<option value="'.$j.'" '.$selected.'>'.$j.'</option>';
							}
?>
						</select>
						<input id="hikashop_cart_quantity_<?php echo $this->params->get('id','0').'_'.$row->cart_product_id;?>" type="hidden" name="item[<?php echo $row->cart_product_id;?>][cart_product_quantity]" value="<?php echo $row->cart_product_quantity; ?>" />
<?php
					}else{
?>
						<input id="hikashop_cart_quantity_<?php echo $row->cart_product_id;?>" type="text" name="item[<?php echo $row->cart_product_id;?>][cart_product_quantity]" class="hikashop_product_quantity_field" value="<?php echo $row->cart_product_quantity; ?>" onchange="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $row->cart_product_id;?>'); if (qty_field){<?php echo $input; ?> } document.<?php echo $form; ?>.submit(); return false;" />
<?php
					}
				}

				if($this->params->get('show_delete',1) && $this->params->get('from','display') != 'module'){
?>
						<div class="hikashop_cart_product_quantity_delete">
							<a href="<?php echo hikashop_completeLink('product&task=updatecart&product_id='.$row->product_id.$this->url_itemid.'&quantity=0&return_url='.urlencode(base64_encode(hikashop_currentURL('return_url')))); ?>" onclick="var qty_field = document.getElementById('hikashop_checkout_quantity_<?php echo $row->cart_product_id;?>'); if(qty_field){qty_field.value=0; <?php echo $input; ?> qty_field.form.submit();} return false;" title="<?php echo JText::_('HIKA_DELETE'); ?>">
								<img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" />
							</a>
						</div>
<?php
				}
?>
					</td>
<?php
			}

			if($this->params->get('show_price', 1)) {
?>
					<td class="hikashop_cart_module_product_price_value hikashop_cart_value"><?php
						$this->row=&$row;
						$this->unit=false;
						echo $this->loadTemplate();
					?></td>
<?php
			}

			if($this->params->get('show_cart_delete', 1)) {
?>
					<td class="hikashop_cart_module_product_delete_value hikashop_cart_value">
						<a href="<?php echo hikashop_completeLink('product&task=updatecart&cart_type='.$this->cart_type.'&cart_product_id='.$row->cart_product_id.'&quantity=0&return_url='.urlencode(base64_encode(urldecode($this->params->get('url'))))); ?>" onclick="var qty_field = document.getElementById('hikashop_cart_quantity_<?php echo $row->cart_product_id;?>'); if(qty_field){qty_field.value=0;<?php echo $input; ?> document.hikashop_cart_form.submit(); return false;}else{ return true;}"  title="<?php echo JText::_('HIKA_DELETE'); ?>"><img src="<?php echo HIKASHOP_IMAGES . 'delete2.png';?>" border="0" alt="<?php echo JText::_('HIKA_DELETE'); ?>" /></a>
					</td>
<?php
			}

			if($this->cart_type == 'wishlist' && $this->params->get('from','display') != 'module') {
?>
					<td class="hikashop_wishlist_display_add_to_cart">
						<!-- Add 'ADD_TO_CART' button -->
<?php
				$form = ',\'hikashop_wishlist_form\'';

				$this->ajax = '
if(qty_field == null) {
	var qty_field = document.getElementById(\'hikashop_wishlist_quantity_'.$row->cart_product_id.'\').value;
}
if(hikashopCheckChangeForm(\'item\',\'hikashop_wishlist_form\'))
	return hikashopModifyQuantity(\'' . $this->row->product_id . '\',qty_field,1,\'hikashop_wishlist_form\',\'cart\');
return false;
';

				$this->setLayout('quantity');
				echo $this->loadTemplate();
				$this->setLayout('listing_price');
?>
					</td>
<?php
			}

			if($row_count < 1) {
?>
					<td></td>
<?php
			}
?>
				</tr>
<?php
			$k = 1 - $k;
		}
		$this->cart_product_price = false;
?>
			</tbody>
		</table>
<?php
		if($this->params->get('show_cart_quantity', 1)) {
?>
		<noscript>
			<input type="submit" class="btn button" name="refresh" value="<?php echo JText::_('REFRESH_CART');?>"/>
		</noscript>
<?php
		}
		if($this->cart_type == 'cart' && $this->params->get('from', 'display') == 'module') {
			if($this->params->get('show_cart_proceed', 1))
				echo $this->cartHelper->displayButton(JText::_('PROCEED_TO_CHECKOUT'), 'checkout', $this->params, $this->url_checkout, 'this.disabled = true;window.location.href = \''.$this->url_checkout.'\';return false;');
		} else {
?>
		<div class="hikashop_display_cart_show_convert_button">
<?php
			if($this->params->get('from', 'display') != 'module') {
				echo $this->cartHelper->displayButton($convertText, 'wishlist', $this->params, hikashop_completeLink('cart&task=convert' . $this->url_itemid . '&cart_type='.$this->cart_type), 'window.location.href = \''.hikashop_completeLink('cart&task=convert'.$this->url_itemid . '&cart_type='.$this->cart_type).'\';return false;');
			} else {
				echo $this->cartHelper->displayButton($displayText, 'wishlist', $this->params, hikashop_completeLink('cart&task=showcart&cart_id=' . $this->element->cart_id . $this->cart_itemid . '&cart_type='.$this->cart_type), 'window.location.href = \''.hikashop_completeLink('cart&task=showcart&cart_id='.$this->element->cart_id . $this->cart_itemid . '&cart_type='.$this->cart_type).'\';return false;');
			}
?>
		</div>
<?php
		}
?>
		<input type="hidden" name="url" value="<?php echo $this->params->get('url');?>"/>
		<input type="hidden" name="ctrl" value="product"/>
		<input type="hidden" name="cart_type" value="<?php echo $this->cart_type; ?>"/>
		<input type="hidden" name="task" value="updatecart"/>
	</form>
<?php } ?>
</div>
<div class="clear_both"></div>
<?php
if(JRequest::getWord('tmpl', '') == 'component') {
	if(!headers_sent()) {
		header('Content-Type: text/css; charset=utf-8');
	}
	exit;
} else {
	$module_id = (int)$this->params->get('id', 0);
}
