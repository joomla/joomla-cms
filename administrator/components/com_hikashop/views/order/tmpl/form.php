<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-order">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-order" class="row-fluid">
	<div class="span6">
<?php } ?>
			<fieldset class="adminform" id="htmlfieldset_general">
				<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
				<table class="admintable table">
					<tr>
						<td class="key">
							<?php echo JText::_( 'ORDER_NUMBER' ); ?>
						</td>
						<td>
							<?php echo $this->order->order_number; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'ORDER_STATUS' ); ?>
						</td>
						<td>
							<?php
							echo $this->popup->display(
								JText::_('ORDER_STATUS'),
								'ORDER_STATUS',
								'/',
								'status_change_link_'.$this->order->order_id,
								760, 480, 'style="display:none;"', '', 'link'
							);
							$doc = JFactory::getDocument();
							$doc->addScriptDeclaration(' var '."default_filter_status_".$this->order->order_id.'=\''.$this->order->order_status.'\'; ');
							echo $this->category->display("filter_status_".$this->order->order_id,$this->order->order_status,'onchange="if(this.value==default_filter_status_'.$this->order->order_id.'){return;} hikashop.openBox(\'status_change_link_'.$this->order->order_id.'\',\''.hikashop_completeLink('order&task=changestatus&order_id='.$this->order->order_id,true).'&status=\'+this.value);this.value=default_filter_status_'.$this->order->order_id.';if(typeof(jQuery)!=\'undefined\'){jQuery(this).trigger(\'liszt:updated\');}"'); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'INVOICE_NUMBER' ); ?>
						</td>
						<td><?php echo @$this->order->order_invoice_number; ?></td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'DATE' ); ?>
						</td>
						<td><?php echo hikashop_getDate($this->order->order_created,'%Y-%m-%d %H:%M');?></td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'ID' ); ?>
						</td>
						<td><?php echo $this->order->order_id; ?></td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform" id="htmlfieldset_customer">
				<legend><?php echo JText::_('CUSTOMER'); ?></legend>
				<div style="float:right">
					<?php
						echo $this->popup->display(
							'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
							'HIKA_EDIT',
							hikashop_completeLink('order&task=user&order_id='.$this->order->order_id,true),
							'hikashop_edit_customer',
							760, 480, '', '', 'link'
						);
					?>
				</div>
				<table class="admintable table">
<?php
	if(!empty($this->order->customer)){
		if(!empty($this->order->customer->name)){
?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_USER_NAME' ); ?>
						</td>
						<td><?php echo $this->order->customer->name.' ('.$this->order->customer->username.')'; ?></td>
					</tr>
<?php
		}
?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_EMAIL' ); ?>
						</td>
						<td>
							<?php echo $this->order->customer->user_email; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'ID' ); ?>
						</td>
						<td>
							<?php echo $this->order->customer->user_id; ?>
							<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->order->customer->user_id.'&order_id='.$this->order->order_id); ?>">
								<img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
							</a>
						</td>
					</tr>
<?php
	}
?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'IP' ); ?>
						</td>
						<td><?php
							echo $this->order->order_ip;
							if(!empty($this->order->geolocation) && $this->order->geolocation->geolocation_country!='Reserved'){
								echo ' ( '.$this->order->geolocation->geolocation_city.' '.$this->order->geolocation->geolocation_state.' '.$this->order->geolocation->geolocation_country.' )';
							}
						?></td>
					</tr>
				</table>
			</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
			<fieldset class="adminform" id="htmlfieldset_additional">
				<legend><?php echo JText::_('ORDER_ADD_INFO'); ?></legend>
				<?php
					echo $this->popup->display(
						'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
						'HIKA_EDIT',
						hikashop_completeLink('order&task=changeplugin&order_id='.$this->order->order_id,true),
						'plugin_change_link',
						760, 480, 'style="display:none;"', '', 'link'
					);
					?>
				<table class="admintable table">
					<tr>
						<td class="key">
							<?php echo JText::_( 'SUBTOTAL' ); ?>
						</td>
						<td><?php
							echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id);
						?></td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKASHOP_COUPON' ); ?>
						</td>
						<td>
							<?php
								echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id);
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=discount&order_id='.$this->order->order_id,true),
									'hikashop_edit_coupon',
									760, 480, '', '', 'link'
								);
								echo ' '.$this->order->order_discount_code; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'SHIPPING' ); ?>
						</td>
						<td>
							<?php
								echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id);
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=changeplugin&plugin='.$this->order->order_shipping_method.'_'.$this->order->order_shipping_id.'&type=shipping&order_id='.$this->order->order_id,true),
									'hikashop_edit_shipping',
									760, 480, '', '', 'link'
								);
								if(!empty($this->shipping)){
									$this->shipping->order = $this->order;
									echo $this->shipping->display('data[order][shipping]',$this->order->order_shipping_method,$this->order->order_shipping_id);
								}?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKASHOP_PAYMENT' ); ?>
						</td>
						<td>
							<?php
								echo $this->currencyHelper->format($this->order->order_payment_price,$this->order->order_currency_id);
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=changeplugin&plugin='.$this->order->order_payment_method.'_'.$this->order->order_payment_id.'&type=payment&order_id='.$this->order->order_id,true),
									'hikashop_edit_payment',
									760, 480, '', '', 'link'
								);
								if(!empty($this->payment)){
									$this->payment->order = $this->order;
									echo $this->payment->display('data[order][payment]',$this->order->order_payment_method,$this->order->order_payment_id);
								}?>
						</td>
					</tr>
<?php
	if($this->config->get('detailed_tax_display') && !empty($this->order->order_tax_info)){
		foreach($this->order->order_tax_info as $tax){
?>
					<tr>
						<td class="key">
							<?php echo $tax->tax_namekey; ?>
						</td>
						<td><?php
							echo $this->currencyHelper->format($tax->tax_amount,$this->order->order_currency_id);
						?></td>
					</tr>
<?php
		}
	}
	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
?>
					<tr>
						<td class="key">
							<?php echo JText::_($additional->order_product_name); ?>
						</td>
						<td><?php
							if(!empty($additional->order_product_price)) {
								$additional->order_product_price = (float)$additional->order_product_price;
							}
							if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
								echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
							} else {
								echo $additional->order_product_options;
							}
						?></td>
					</tr>
<?php
		}
	}
?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKASHOP_TOTAL' ); ?>
						</td>
						<td><?php
							echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id);
						?></td>
					</tr>
<?php
		if(!empty($this->fields['order'])){
			foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
					<tr>
						<td class="key">
							<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
						</td>
						<td>
							<?php echo $this->fieldsClass->show($oneExtraField,@$this->order->$fieldName); ?>
						</td>
					</tr>
<?php
		}
?>
					<tr>
						<td colspan="2">
							<div style="float:right">
								<?php
									echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=fields&order_id='.$this->order->order_id,true),
									'hikashop_edit_fields',
									760, 480, '', '', 'link'
								);
								?>
							</div>
						</td>
					</tr>
<?php
	}
?>
				</table>
			</fieldset>
<?php if(!empty($this->order->partner)){ ?>
		<fieldset class="adminform" id="htmlfieldset_partner">
			<legend><?php echo JText::_('PARTNER'); ?></legend>
				<table class="admintable table">
					<tr>
						<td class="key">
							<?php echo JText::_( 'PARTNER_EMAIL' ); ?>
						</td>
						<td>
							<?php echo $this->order->partner->user_email;?>
							<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='. $this->order->partner->user_id.'&order_id='.$this->order->order_id); ?>">
								<img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="go" />
							</a>
							<?php
								echo $this->popup->display(
								'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
								'HIKA_EDIT',
								hikashop_completeLink('order&task=partner&order_id='.$this->order->order_id,true),
								'hikashop_edit_partner',
								760, 480, '', '', 'link'
							);
							?>
						</td>
					</tr>
<?php if(!empty($this->order->partner->name)){ ?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'PARTNER_NAME' ); ?>
						</td>
						<td><?php
							echo $this->order->partner->name;
						?></td>
					</tr>
<?php } ?>
					<tr>
						<td class="key">
							<?php echo JText::_( 'PARTNER_FEE' ); ?>
						</td>
						<td>
							<?php
								echo $this->currencyHelper->format($this->order->order_partner_price,$this->order->order_partner_currency_id);
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=partner&order_id='.$this->order->order_id,true),
									'hikashop_edit_partner2',
									760, 480, '', '', 'link'
								);

								if(empty($this->order->order_partner_paid)){
									echo JText::_('NOT_PAID').'<img src="'.HIKASHOP_IMAGES.'delete2.png" />';
								}else{
									echo JText::_('PAID').'<img src="'.HIKASHOP_IMAGES.'ok.png" />';
								}
							?>
						</td>
					</tr>
				</table>
			</fieldset>
<?php } ?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-order2">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%" id="hikashop_billing_address">
<?php } else { ?>
<div id="page-order2" class="row-fluid">
	<div class="span6" id="hikashop_billing_address">
<?php } ?>
			<?php $this->type = 'billing'; echo $this->loadTemplate('address');?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%" id="hikashop_shipping_address">
<?php } else { ?>
	</div>
	<div class="span6" id="hikashop_shipping_address">
<?php } ?>
<?php
			if(empty($this->order->override_shipping_address)) {
				$this->type = 'shipping'; echo $this->loadTemplate('address');
			} else {
				?><fieldset class="adminform" id="htmlfieldset_shipping">
					<legend><?php echo JText::_('HIKASHOP_SHIPPING_ADDRESS'); ?></legend>
					<?php echo $this->order->override_shipping_address;?>
				</fieldset><?php
			}
		?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
			<fieldset class="adminform" id="htmlfieldset_products">
				<legend><?php echo JText::_('PRODUCT_LIST'); ?></legend>
				<div style="float:right">
					<?php
					echo $this->popup->display(
						'<img style="vertical-align:middle;" alt="'.JText::_('ADD').'" src="'. HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
						'ADD',
						hikashop_completeLink('order&task=product&order_id='.$this->order->order_id,true),
						'hikashop_add_product',
						760, 480, '', '', 'button'
					);
					echo $this->popup->display(
						'<img style="vertical-align:middle;" alt="'.JText::_('ADD_EXISTING_PRODUCT').'" src="'. HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD_EXISTING_PRODUCT'),
						'ADD_EXISTING_PRODUCT',
						hikashop_completeLink('order&task=product_select&order_id='.$this->order->order_id,true),
						'hikashop_add_existing_product',
						760, 480, '', '', 'button'
					);

					?>
				</div>
				<table id="hikashop_order_product_listing" class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="hikashop_order_item_name_title title"><?php
								echo JText::_('PRODUCT');
							?></th>
							<th class="hikashop_order_item_files_title title"><?php
								echo JText::_('HIKA_FILES');
							?></th>
							<th class="hikashop_order_item_price_title title"><?php
								echo JText::_('UNIT_PRICE');
							?></th>
							<th class="hikashop_order_item_quantity_title title titletoggle"><?php
								echo JText::_('PRODUCT_QUANTITY');
							?></th>
							<th class="hikashop_order_item_total_price_title title titletoggle"><?php
								echo JText::_('PRICE');
							?></th>
							<th class="hikashop_order_item_action_title title titletoggle"><?php
								echo JText::_('ACTIONS');
							?></th>
						</tr>
					</thead>
					<tbody>
<?php
	foreach($this->order->products as $k => $product){
?>
						<tr>
							<td class="hikashop_order_item_name_value">
								<span class="hikashop_order_item_name">
									<?php
									echo $this->popup->display(
										$product->order_product_name.' '.$product->order_product_code,
										$product->order_product_name.' '.$product->order_product_code,
										hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id,true),
										'hikashop_see_product_'.$product->product_id,
										760, 480, '', '', 'link'
									);
									$config =& hikashop_config();
									$manage = hikashop_isAllowed($config->get('acl_product_manage','all'));
									if($manage){ ?>
										<a target="_blank" href="<?php echo hikashop_completeLink('product&task=edit&cid[]='. $product->product_id); ?>">
											<img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>go.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>" />
										</a>
									<?php } ?>
								</span>
								<p class="hikashop_order_product_custom_item_fields"><?php
								if(hikashop_level(2) && !empty($this->fields['item'])){
									foreach($this->fields['item'] as $field){
										$namekey = $field->field_namekey;
										if(empty($product->$namekey)){
											continue;
										}
										echo '<p class="hikashop_order_item_'.$namekey.'">'.$this->fieldsClass->getFieldName($field).': '.$this->fieldsClass->show($field,$product->$namekey).'</p>';
									}
								}?></p>
							</td>
							<td class="hikashop_order_item_files_value">
								<?php
									if(!empty($product->files)){
										$html = array();
										foreach($product->files as $file){
											if(empty($file->file_name)){
												$file->file_name = $file->file_path;
											}
											$fileHtml = '';
											if(!empty($this->order_status_for_download) && !in_array($this->order->order_status,explode(',',$this->order_status_for_download))){
												$fileHtml .= ' / <b>'.JText::_('BECAUSE_STATUS_NO_DOWNLOAD').'</b>';
											}
											if(!empty($this->download_time_limit)){
													if(($this->download_time_limit+(!empty($this->order->order_invoice_created)?$this->order->order_invoice_created:$this->order->order_created))<time()){
														$fileHtml .= ' / <b>'.JText::_('TOO_LATE_NO_DOWNLOAD').'</b>';
													}else{
														$fileHtml .= ' / '.JText::sprintf('UNTIL_THE_DATE',hikashop_getDate((!empty($this->order->order_invoice_created)?$this->order->order_invoice_created:$this->order->order_created)+$this->download_time_limit));
													}
											}
											if(!empty($file->file_limit) && (int)$file->file_limit != 0) {
												$download_number_limit = $file->file_limit;
												if($download_number_limit < 0)
													$download_number_limit = 0;
											} else {
												$download_number_limit = $this->download_number_limit;
											}
											if(!empty($download_number_limit)){
												if($download_number_limit<=$file->download_number){
													$fileHtml .= ' / <b>'.JText::_('MAX_REACHED_NO_DOWNLOAD').'</b>';
												}else{
													$fileHtml .= ' / '.JText::sprintf('X_DOWNLOADS_LEFT',$download_number_limit-$file->download_number);
												}
												if($file->download_number){
													$fileHtml .= '<a href="'.hikashop_completeLink('file&task=resetdownload&file_id='.$file->file_id.'&order_id='.$this->order->order_id.'&'.hikashop_getFormToken().'=1&return='.urlencode(base64_encode(hikashop_completeLink('order&task=edit&cid='.$this->order->order_id,false,true)))).'"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="'.JText::_('HIKA_DELETE').'" /></a>';
												}
											}
											$file_pos = '';
											if($file->file_pos > 0) {
												$file_pos = '&file_pos='.$file->file_pos;
											}
											$fileLink = '<a href="'.hikashop_completeLink('order&task=download&file_id='.$file->file_id.'&order_id='.$this->order->order_id.$file_pos).'">'.$file->file_name.'</a>';
											$html[]=$fileLink.' '.$fileHtml;
										}
										echo implode('<br/>',$html);
									}
								?>
							</td>
							<td class="hikashop_order_item_price_value"><?php
								echo $this->currencyHelper->format($product->order_product_price,$this->order->order_currency_id);
								if(bccomp($product->order_product_tax,0,5)){
									echo ' '.JText::sprintf('PLUS_X_OF_VAT',$this->currencyHelper->format($product->order_product_tax,$this->order->order_currency_id));
								}
							?></td>
							<td class="hikashop_order_item_quantity_value"><?php
								echo $product->order_product_quantity;
							?></td>
							<td class="hikashop_order_item_total_price_value"><?php
								echo $this->currencyHelper->format($product->order_product_total_price,$this->order->order_currency_id);
							?></td>
							<td class="hikashop_order_item_action_value">
								<?php
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
									'HIKA_EDIT',
									hikashop_completeLink('order&task=product&product_id='.$product->order_product_id,true),
									'hikashop_edit_product'.$product->order_product_id,
									760, 480, '', '', 'link'
								);
								echo $this->popup->display(
									'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_DELETE').'" src="'. HIKASHOP_IMAGES.'delete.png"/>',
									'HIKA_DELETE',
									hikashop_completeLink('order&task=product_delete&product_id='.$product->order_product_id,true),
									'hikashop_delete_product'.$product->order_product_id,
									760, 480, '', '', 'link'
								); ?>
							</td>
						</tr>
<?php
	}
?>
					</tbody>
				</table>
			</fieldset>
<?php
	JPluginHelper::importPlugin('hikashop');
	$dispatcher = JDispatcher::getInstance();
	$dispatcher->trigger('onAfterOrderProductsListingDisplay', array(&$this->order, 'order_back_show'));
?>

<?php if(!empty($this->order->history)) { ?>
			<fieldset class="adminform" id="htmlfieldset_history">
				<legend><?php echo JText::_('HISTORY'); ?></legend>
				<table id="hikashop_order_history_listing" class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title"><?php
								echo JText::_('HIKA_TYPE');
							?></th>
							<th class="title"><?php
								echo JText::_('ORDER_STATUS');
							?></th>
							<th class="title"><?php
								echo JText::_('REASON');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_USER').' / '.JText::_('IP');
							?></th>
							<th class="title"><?php
								echo JText::_('DATE');
							?></th>
							<th class="title"><?php
								echo JText::_('INFORMATION');
							?></th>
						</tr>
					</thead>
					<tbody>
<?php
	foreach($this->order->history as $k => $history){
?>
						<tr>
							<td><?php
								$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($history->history_type));
								$trans = JText::_($val);
								if($val!=$trans){
									$history->history_type = $trans;
								}
								echo $history->history_type;
							?></td>
							<td><?php
								echo $this->category->get($history->history_new_status);
							?></td>
							<td><?php
								echo $history->history_reason;
							?></td>
							<td><?php
								if(!empty($history->history_user_id)){
									$class = hikashop_get('class.user');
									$user = $class->get($history->history_user_id);
									echo $user->username.' / ';
								}
								echo $history->history_ip;
							?></td>
							<td><?php
								echo hikashop_getDate($history->history_created,'%Y-%m-%d %H:%M');
							?></td>
							<td><?php
								echo $history->history_data;
							?></td>
						</tr>
<?php
	}
?>
					</tbody>
				</table>
			</fieldset>
<?php }?>
<?php if(hikashop_level(2) && !empty($this->order->entries)) { ?>
			<fieldset class="adminform" id="htmlfieldset_history">
				<legend><?php echo JText::_('HIKASHOP_ENTRIES'); ?></legend>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum"><?php
								echo JText::_('HIKA_NUM');
							?></th>
							<th class="title"><?php
								echo JText::_('HIKA_EDIT');
							?></th>
<?php
	if(!empty($this->fields['entry'])){
		foreach($this->fields['entry'] as $field){
			echo '<th class="title">'.$this->fieldsClass->trans($field->field_realname).'</th>';
		}
	}
?>
							<th class="title titlenum"><?php
								echo JText::_('ID');
							?></th>
						</tr>
					</thead>
					<tbody>
<?php
	$k=0;
	$i=1;
	foreach($this->order->entries as $entry){
?>
		<tr class="row<?php echo $k;?>">
			<td><?php
				echo $i;
			?></td>
			<td>
				<?php
				echo $this->popup->display(
					'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
					'HIKA_EDIT',
					hikashop_completeLink('entry&task=edit&entry_id='.$entry->entry_id,true),
					'hikashop_edit_entry'.$entry->entry_id,
					760, 480, '', '', 'link'
				); ?>
				<a onclick="return confirm('<?php echo JText::_('VALIDDELETEITEMS',true); ?>');" href="<?php echo hikashop_completeLink('order&task=deleteentry&entry_id='.$entry->entry_id.'&'.hikashop_getFormToken().'=1');?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/>
				</a>
			</td>
<?php
	if(!empty($this->fields['entry'])){
		foreach($this->fields['entry'] as $field){
			$namekey = $field->field_namekey;
			echo '<td>'.$entry->$namekey.'</td>';
		}
	}
?>
			<td><?php
				echo $entry->entry_id;
			?></td>
		</tr>
<?php
		$k=1-$k;
		$i++;
	}
?>
					</tbody>
				</table>
			</fieldset>
<?php }
