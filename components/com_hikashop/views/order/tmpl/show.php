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
$colspan = 2;
global $Itemid;
$url_itemid = '';
if(!empty($Itemid)) {
	$url_itemid = '&Itemid=' . $Itemid;
}
$config = hikashop_config();
?>
<div id="hikashop_order_main">
<fieldset>
	<div class="header hikashop_header_title">
		<h1>
<?php
	if($this->invoice_type == 'order' || empty($this->element->order_invoice_number)) {
		echo JText::_('HIKASHOP_ORDER').':'.$this->element->order_number;
	} else {
		echo JText::_('INVOICE').':'.$this->element->order_invoice_number;
	}
?>
		</h1>
	</div>
<?php
	if($this->invoice_type == 'order') {
?>
	<div class="toolbar hikashop_header_buttons" id="toolbar">
		<table>
			<tr>
<?php
		if(hikashop_level(1)) {
			if($this->config->get('print_invoice_frontend') && !in_array($this->element->order_status, array('created','refunded','cancelled'))) {
?>
				<td><?php
					echo $this->popup->display(
						'<span class="icon-32-print" title="'. JText::_('PRINT_INVOICE').'"></span>'. JText::_('PRINT_INVOICE'),
						'PRINT_INVOICE',
						hikashop_completeLink('order&task=invoice&order_id='.$this->element->order_id.$url_itemid,true),
						'hikashop_print_invoice',
						760, 480, '', '', 'link'
					);
				?></td>
<?php
			}
		}
?>
				<td>
					<a id="hikashop_order_back_button" onclick="submitbutton('cancel'); return false;" href="#">
						<span class="icon-32-back" title="<?php echo JText::_('HIKA_BACK'); ?>"></span>
						<?php echo JText::_('HIKA_BACK'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
<?php
	}
?>
</fieldset>
<form action="<?php echo hikashop_completeLink('order'.$url_itemid); ?>" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td>
				<div id="hikashop_order_right_part" class="hikashop_order_right_part">
<?php
		if($this->invoice_type == 'order' || empty($this->element->order_invoice_created)) {
			echo JText::_('DATE').': '.hikashop_getDate($this->element->order_created, '%d %B %Y');
		} else {
			echo JText::_('DATE').': '.hikashop_getDate($this->element->order_invoice_created, '%d %B %Y');
		}
?>
				<br/>
<?php
		if($this->invoice_type == 'order' || empty($this->element->order_invoice_number)) {
			echo JText::_('HIKASHOP_ORDER').': '.@$this->element->order_number;
		} else {
			echo JText::_(strtoupper($this->invoice_type)).': '.@$this->element->order_invoice_number;
		}
?>
				</div>
				<div id="hikashop_order_left_part" class="hikashop_order_left_part"><?php
					echo $this->store_address;
				?></div>
			</td>
		</tr>
		<tr>
			<td>
				<table width="100%">
					<tr>
<?php
	$params = null;
	$js = '';
?>
<?php
	if(!empty($this->element->billing_address)) {
?>
						<td>
							<fieldset class="adminform" id="htmlfieldset_billing">
							<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_BILLING_ADDRESS'); ?></legend>
<?php
		$addressClass = hikashop_get('class.address');
		echo $addressClass->displayAddress($this->element->fields,$this->element->billing_address,'address');
?>
							</fieldset>
						</td>
<?php
	}

	if(!empty($this->element->order_shipping_id) && !empty($this->element->shipping_address)) {
?>
						<td>
							<fieldset class="adminform" id="htmlfieldset_shipping">
								<legend style="background-color: #FFFFFF;"><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
<?php
		$override = false;
		if(method_exists($this->currentShipping, 'getShippingAddress')) {
			$override = $this->currentShipping->getShippingAddress($this->element->order_shipping_id);
		}
		if($override !== false ) {
			echo $override;
		} else {
			$addressClass = hikashop_get('class.address');
			echo $addressClass->displayAddress($this->element->fields, $this->element->shipping_address, 'address');
		}
?>
							</fieldset>
						</td>
<?php
	}
?>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<fieldset class="adminform" id="htmlfieldset_products">
					<legend style="background-color: #FFFFFF;"><?php echo JText::_('PRODUCT_LIST'); ?></legend>
					<table cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="hikashop_order_item_name_title title"><?php
									echo JText::_('PRODUCT');
								?></th>
<?php
	$files = false;
	foreach($this->order->products as $product ){
		if(!empty($product->files)) {
			$files = true;
			break;
		}
	}
	if($this->invoice_type == 'order' && $files) {
		$colspan++;
?>
								<th class="hikashop_order_item_files_title title"><?php
									echo JText::_('HIKA_FILES');
								?></th>
<?php
	}
?>
								<th class="hikashop_order_item_price_title title"><?php
									echo JText::_('UNIT_PRICE');
								?></th>
								<th class="hikashop_order_item_quantity_title title titletoggle"><?php
									echo JText::_('PRODUCT_QUANTITY');
								?></th>
								<th class="hikashop_order_item_total_title title titletoggle"><?php
									echo JText::_('PRICE');
								?></th>
							</tr>
						</thead>
						<tbody>
<?php
	$k=0;
	$group = $this->config->get('group_options',0);
	foreach($this->order->products as $product) {
		if($group && $product->order_product_option_parent_id)
			continue;
?>
							<tr class="row<?php echo $k;?>">
								<td class="hikashop_order_item_name_value">
<?php if($this->invoice_type=='order' && !empty($product->product_id)) { ?>
									<a class="hikashop_order_product_link" href="<?php echo hikashop_contentLink('product&task=show&cid='.$product->product_id.$url_itemid,$product); ?>">
<?php } ?>
										<p class="hikashop_order_product_name">
											<?php echo $product->order_product_name; ?>
											<?php if($this->config->get('show_code')) { ?>
												<span class="hikashop_product_code_order"><?php echo $product->order_product_code; ?></span>
											<?php } ?>
										</p>
<?php
		if($group) {
			$display_item_price = false;
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;
				if($optionElement->order_product_price > 0) {
					$display_item_price = true;
				}

			}
			if($display_item_price) {
				if($this->config->get('price_with_tax')) {
					echo ' '.$this->currencyHelper->format($product->order_product_price + $product->order_product_tax, $this->order->order_currency_id);
				} else {
					echo ' '.$this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
				}
			}
		}
?>
<?php if($this->invoice_type == 'order' && !empty($product->product_id)) { ?>
									</a>
<?php } ?>
									<p class="hikashop_order_product_custom_item_fields">
<?php
		if(hikashop_level(2) && !empty($this->fields['item'])) {
			foreach($this->fields['item'] as $field) {
				$namekey = $field->field_namekey;
				if(!empty($product->$namekey) && strlen($product->$namekey)) {
					echo '<p class="hikashop_order_item_'.$namekey.'">' .
						$this->fieldsClass->getFieldName($field) . ': ' .
						$this->fieldsClass->show($field,$product->$namekey) .
						'</p>';
				}
			}
		}
		if($group) {
			foreach($this->order->products as $j => $optionElement) {
				if($optionElement->order_product_option_parent_id != $product->order_product_id)
					continue;

				$product->order_product_price += $optionElement->order_product_price;
				$product->order_product_tax += $optionElement->order_product_tax;
				$product->order_product_total_price += $optionElement->order_product_total_price;
				$product->order_product_total_price_no_vat += $optionElement->order_product_total_price_no_vat;
?>
										<p class="hikashop_order_option_name">
<?php
				echo $optionElement->order_product_name;
				if($optionElement->order_product_price > 0) {
					if($this->config->get('price_with_tax')) {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price + $optionElement->order_product_tax, $this->order->order_currency_id).' )';
					} else {
						echo ' ( + '.$this->currencyHelper->format($optionElement->order_product_price, $this->order->order_currency_id).' )';
					}
				}
?>
										</p>
<?php
			}
		}
?>
									</p>
<?php
		if(!empty($product->extraData))
			echo '<p class="hikashop_order_product_extra">' . (is_string($product->extraData) ? $product->extraData : implode('<br/>', $product->extraData)) . '</p>';
?>
								</td>
<?php if($this->invoice_type == 'order' && $files) { ?>
								<td class="hikashop_order_item_files_value">
<?php
		if(!empty($product->files) && ($this->order_status_download_ok || bccomp($product->order_product_price, 0, 5) == 0)) {
			$html = array();
			foreach($product->files as $file) {
				$fileHtml = '';
				if(!empty($this->download_time_limit) && ($this->download_time_limit + (!empty($this->order->order_invoice_created) ? $this->order->order_invoice_created : $this->order->order_created)) < time()) {
					$fileHtml = JText::_('TOO_LATE_NO_DOWNLOAD');
				}
				if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
					$download_number_limit = $file->file_limit;
					if($download_number_limit < 0)
						$download_number_limit = 0;
				} else {
					$download_number_limit = $this->download_number_limit;
				}

				if(!empty($download_number_limit) && $download_number_limit<=$file->download_number) {
					$fileHtml = JText::_('MAX_REACHED_NO_DOWNLOAD');
				}

				if(empty($fileHtml)) {
					if(empty($file->file_name)) {
						$file->file_name = JText::_('DOWNLOAD_NOW');
					}
					$file_pos = '';
					if(!empty($file->file_pos)) {
						$file_pos = '&file_pos='.$file->file_pos;
					}
					$fileHtml = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id.$file_pos.$url_itemid).'">'.$file->file_name.'</a>';

					$order_created = (empty($this->order->order_invoice_created) ? $this->order->order_created : $this->order->order_invoice_created);
					if(!empty($this->download_time_limit))
						$fileHtml .= ' / ' . JText::sprintf('UNTIL_THE_DATE', hikashop_getDate($order_created + $this->download_time_limit));
					if(!empty($download_number_limit))
						$fileHtml .= ' / '. JText::sprintf('X_DOWNLOADS_LEFT', $download_number_limit - $file->download_number);
				} else {
					if(empty($file->file_name)) {
						$file->file_name = JText::_('EMPTY_FILENAME');
					}
					$fileHtml = $file->file_name . ' ' . $fileHtml;
				}
				$html[] = $fileHtml;
			}
			echo implode('<br/>', $html);
		}
?>
								</td>
<?php } ?>
								<td class="hikashop_order_item_price_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($product->order_product_price + $product->order_product_tax, $this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($product->order_product_price, $this->order->order_currency_id);
									}
								?></td>
								<td class="hikashop_order_item_quantity_value"><?php
									echo $product->order_product_quantity;
								?></td>
								<td class="hikashop_order_item_total_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($product->order_product_total_price,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($product->order_product_total_price_no_vat,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
		$k = 1 - $k;
	}
?>
							<tr>
								<td style="border-top:2px solid #B8B8B8;" colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_subtotal_title" style="border-top:2px solid #B8B8B8;" class="key">
									<label><?php
										echo JText::_( 'SUBTOTAL' );
									?></label>
								</td>
								<td class="hikashop_order_subtotal_value" style="border-top:2px solid #B8B8B8;"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	$taxes = round($this->order->order_subtotal - $this->order->order_subtotal_no_vat + $this->order->order_shipping_tax + $this->order->order_payment_tax - $this->order->order_discount_tax,$this->currencyHelper->getRounding($this->order->order_currency_id, true));

	if(!empty($this->order->order_discount_code)) {
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_coupon_title key">
									<label><?php
										echo JText::_( 'HIKASHOP_COUPON' );
									?></label>
								</td>
								<td class="hikashop_order_coupon_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format(($this->order->order_discount_price-@$this->order->order_discount_tax)*-1.0,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}

	if(!empty($this->order->order_shipping_method)) {
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_shipping_title key">
									<label><?php
										echo JText::_( 'SHIPPING' );
									?></label>
								</td>
								<td class="hikashop_order_shipping_value"><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_shipping_price-@$this->order->order_shipping_tax,$this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}

	if(!empty($this->order->additional)) {
		$exclude_additionnal = explode(',', $this->config->get('order_additional_hide', ''));
		foreach($this->order->additional as $additional) {
			if(in_array($additional->order_product_name, $exclude_additionnal))
				continue;
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_additionall_title key">
									<label><?php
										echo JText::_($additional->order_product_name);
									?></label>
								</td>
								<td class="hikashop_order_additional_value"><?php
									if(!empty($additional->order_product_price)) {
										$additional->order_product_price = (float)$additional->order_product_price;
									}
									if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
										if($config->get('price_with_tax')){
											echo $this->currencyHelper->format($additional->order_product_price + @$additional->order_product_tax, $this->order->order_currency_id);
										}else{
											echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
										}
									} else {
										echo $additional->order_product_options;
									}
								?></td>
							</tr>
<?php
		}
	}

	if(!empty($this->order->order_payment_method) && $this->order->order_payment_price != 0) {
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_payment_title key">
									<label><?php
										echo JText::_( 'HIKASHOP_PAYMENT' );
									?></label>
								</td>
								<td class="hikashop_order_payment_value" ><?php
									if($this->config->get('price_with_tax')) {
										echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id);
									} else {
										echo $this->currencyHelper->format($this->order->order_payment_price - @$this->order->order_payment_tax, $this->order->order_currency_id);
									}
								?></td>
							</tr>
<?php
	}

	if($taxes != 0){
		if($this->config->get('detailed_tax_display') && !empty($this->order->order_tax_info)) {
			foreach($this->order->order_tax_info as $tax) {
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_tax_title key">
									<label><?php
										echo $tax->tax_namekey;
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($tax->tax_amount, $this->order->order_currency_id);
								?></td>
							</tr>
<?php
				}
		} else {
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_tax_title key">
									<label><?php
										echo JText::_( 'VAT' );
									?></label>
								</td>
								<td class="hikashop_order_tax_value"><?php
									echo $this->currencyHelper->format($taxes,$this->order->order_currency_id);
								?></td>
							</tr>
<?php
		}
	}
?>
							<tr>
								<td colspan="<?php echo $colspan; ?>">
								</td>
								<td class="hikashop_order_total_title key">
									<label><?php
										echo JText::_( 'HIKASHOP_TOTAL' );
									?></label>
								</td>
								<td class="hikashop_order_total_value" ><?php
									echo $this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id);
								?></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td>
<?php
	if(!empty($this->shipping)) {
		echo JText::_('HIKASHOP_SHIPPING_METHOD') . ' : ';
		if(is_string($this->order->order_shipping_method)) {
			if(strpos($this->order->order_shipping_id, '-') !== false)
				echo $this->shippingClass->getShippingName($this->order->order_shipping_method, $this->order->order_shipping_id);
			else
				echo $this->shipping->getName($this->order->order_shipping_method, $this->order->order_shipping_id);
		} else
			echo implode(', ', $this->order->order_shipping_method);
		echo '<br/>';
	}
?>
<?php
	if(!empty($this->payment)) {
		echo JText::_('HIKASHOP_PAYMENT_METHOD') . ' : ' . $this->payment->getName($this->order->order_payment_method, $this->order->order_payment_id);
	}
?>
			</td>
		</tr>
		<tr>
			<td>
<?php
	JPluginHelper::importPlugin('hikashop');
	JPluginHelper::importPlugin('hikashopshipping');
	JPluginHelper::importPlugin('hikashoppayment');
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_front_show'));
?>
			</td>
		</tr>
<?php if(hikashop_level(2) && !empty($this->fields['order'])) { ?>
		<tr>
			<td>
				<fieldset class="hikashop_order_custom_fields_fieldset">
					<legend><?php echo JText::_('ADDITIONAL_INFORMATION'); ?></legend>
					<table class="hikashop_order_custom_fields_table adminlist" cellpadding="1" width="100%">
<?php
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
			if(!@$oneExtraField->field_frontcomp || empty($this->order->$fieldName))
				continue;
?>
						<tr class="hikashop_order_custom_field_<?php echo $fieldName;?>_line">
							<td class="key"><?php
								echo $this->fieldsClass->getFieldName($oneExtraField);
							?></td>
							<td><?php
								echo $this->fieldsClass->show($oneExtraField,$this->order->$fieldName);
							?></td>
						</tr>
<?php
		}
?>
					</table>
				</fieldset>
			</td>
		</tr>
<?php } ?>
<?php if(hikashop_level(2) && !empty($this->order->entries)) { ?>
		<tr>
			<td>
				<fieldset class="htmlfieldset_entries">
					<legend><?php echo JText::_('HIKASHOP_ENTRIES'); ?></legend>
					<table class="hikashop_entries_table adminlist" cellpadding="1" width="100%">
						<thead>
							<tr>
								<th class="title titlenum"><?php
									echo JText::_( 'HIKA_NUM' );
								?></th>
<?php
		if(!empty($this->fields['entry'])) {
			foreach($this->fields['entry'] as $field) {
				echo '<th class="title">' . $this->fieldsClass->trans($field->field_realname) . '</th>';
			}
		}
?>
							</tr>
						</thead>
						<tbody>
<?php
		$k = 0;
		$i = 1;
		foreach($this->order->entries as $entry) {
?>
							<tr class="row<?php echo $k;?>">
								<td><?php
									echo $i;
								?></td>
<?php
			if(!empty($this->fields['entry'])) {
				foreach($this->fields['entry'] as $field) {
					$namekey = $field->field_namekey;
					if(!empty($entry->$namekey))
						echo '<td>'.$this->fieldsClass->show($field, $entry->$namekey).'</td>';
				}
			}
?>
							</tr>
<?php
			$k = 1 - $k;
			$i++;
		}
?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
<?php } ?>
	</table>
	<input type="hidden" name="cid" value="<?php echo (int)$this->element->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="cancel_redirect" value="<?php echo JRequest::getString('cancel_redirect'); ?>" />
	<input type="hidden" name="cancel_url" value="<?php echo JRequest::getString('cancel_url'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<div style="page-break-after:always"></div>
