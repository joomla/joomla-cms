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
echo $this->leftmenu(
	'main',
	array(
		'#main_global' => JText::_('MAIN'),
		'#main_currency' => JText::_('CURRENCY'),
		'#main_tax' => JText::_('TAXES'),
		'#main_product' => JText::_('PRODUCT'),
		'#main_cart' => JText::_('HIKASHOP_CHECKOUT_CART'),
		'#main_order' => JText::_('HIKASHOP_ORDER'),
		'#main_files' => JText::_('FILES'),
		'#main_images' => JText::_('HIKA_IMAGES'),
		'#main_emails' => JText::_('EMAILS'),
		'#main_advanced' => JText::_('HIKA_ADVANCED_SETTINGS')
	)
);
?>
<div id="page-main" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">
<table style="width:100%;">
<tr>
	<td valign="top" width="50%">
	<!-- MAIN - GLOBAL -->
		<div id="main_global">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'MAIN' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('PUT_STORE_OFFLINE'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist', "config[store_offline]",'onchange="if(this.checked && this.value==1) alert(\''.JText::_('STORE_OFFLINE_WARNING',true).'\');"',$this->config->get('store_offline',0));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('STORE_ADDRESS'); ?></td>
						<td>
							<textarea class="inputbox" name="config_store_address" cols="30" rows="5"><?php echo $this->config->get('store_address'); ?></textarea>
						</td>
					</tr>
					<tr>
						<td class="key" ><?php echo JText::_('HIKA_EDITOR'); ?></td>
						<td><?php
							echo $this->elements->editor;
						?></td>
					</tr>
					<tr>
						<td class="key" ><?php echo JText::_('READ_MORE'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist', "config[readmore]",'',$this->config->get('readmore'));
						?></td>
					</tr>
				</table>
			</fieldset>
		</div>
		<?php if($this->config->get('default_type')!='individual'){ ?>
	<!-- ADDRESS -->
		<div id="main_address">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'ADDRESS' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('DEFAULT_ADDRESS_TYPE'); ?></td>
						<td><?php
							echo $this->tax->display('config[default_type]',$this->config->get('default_type'));
						?></td>
					</tr>
				</table>
			</fieldset>
		</div>
		<?php } ?>
	<!-- CURRENCY -->
		<div id="main_currency">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'CURRENCY' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('MAIN_CURRENCY'); ?></td>
						<td>
							<?php echo $this->currency->display('config[main_currency]',$this->config->get('main_currency')); ?>
							<a href="<?php echo hikashop_completeLink('currency');?>">
								<img src="<?php echo HIKASHOP_IMAGES.'go.png';?>" title="Go to the currencies management" alt="Go to the currencies management"/>
							</a>
						</td>
					</tr>
<?php if($this->rates_active){ ?>
					<tr>
						<td class="key"><?php echo JText::_('RATES_REFRESH_FREQUENCY'); ?></td>
						<td><?php
							echo $this->delayTypeRates->display('params[hikashop][rates][frequency]',@$this->rates_params['frequency'],3);
						?></td>
					</tr>
<?php } ?>
				</table>
			</fieldset>
		</div>
	<!-- TAX -->
		<div id="main_tax">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'TAXES' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('DETAILED_TAX_DISPLAY');?></td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', 'config[detailed_tax_display]' , '',@$this->config->get('detailed_tax_display')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ZONE_TAX_ADDRESS_TYPE'); ?></td>
						<td><?php
							echo $this->tax_zone->display('config[tax_zone_type]',$this->config->get('tax_zone_type'));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('MAIN_TAX_ZONE'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[main_tax_zone]',
									@$this->zone->zone_id,
									hikashopNameboxType::NAMEBOX_SINGLE,
									'zone',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
										'type' => 'id'
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('VAT_CHECK'); ?></td>
						<td><?php
							echo $this->vat->display('config[vat_check]',$this->config->get('vat_check'));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('SHOW_TAXED_PRICES'); ?></td>
						<td><?php
							echo $this->pricetaxType->display('config[price_with_tax]' , $this->config->get('price_with_tax',@$this->default_params['price_with_tax']));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ROUND_PRICES_DURING_CALCULATIONS'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist','config[round_calculations]' , '', $this->config->get('round_calculations',0));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('FLOATING_TAX_PRICES'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist','config[floating_tax_prices]' , '', $this->config->get('floating_tax_prices',0));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('APPLY_DISCOUNTS'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist', "config[discount_before_tax]",'',$this->config->get('discount_before_tax'),JTEXT::_('BEFORE_TAXES'),JTEXT::_('AFTER_TAXES'));
						?></td>
					</tr>
				</table>
			</fieldset>
		</div>
	<!-- PRODUCT -->
		<div id="main_product">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'PRODUCT' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('DEFAULT_PRODUCT_TYPE'); ?></td>
						<td><?php
							$arr = array(
								JHTML::_('select.option', 'shippable', JText::_('WIZARD_REAL')),
								JHTML::_('select.option', 'virtual', JText::_('WIZARD_VIRTUAL'))
							);
							echo JHTML::_('hikaselect.genericlist', $arr, "config[default_product_type]" , '','value', 'text', $this->config->get('default_product_type','virtual')); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('DEFAULT_VARIANT_PUBLISH'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist', "config[variant_default_publish]" , '',$this->config->get('variant_default_publish',1) );
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PRODUCT_SHOW_MODULES'); ?></td>
						<td>
							<input type="hidden" name="config[product_show_modules]'; ?>" id="menumodules"  value="<?php echo $this->config->get('product_show_modules'); ?>" />
							<?php
								echo $this->popup->display(
									JText::_('SELECT'),
									'SELECT_MODULES',
									'\''.hikashop_completeLink('modules&task=selectmodules&control=menu&name=modules',true).'\'+\'&modules=\'+document.getElementById(\'menumodules\').value',
									'linkmenumodules',
									750, 375, '', '', 'button',true
								);
							?>
							<br/>
							<?php
								$modules = explode(',',$this->config->get('product_show_modules'));
								$modulesClass = hikashop_get('class.modules');
								foreach($modules as $module){
									$element = $modulesClass->get($module);
									if(!empty($element->title)){
										echo '<a href="'.hikashop_completeLink('modules&task=edit&cid[]='.@$element->id).'">'.JText::sprintf('OPTIONS_FOR_X',@$element->title).'</a><br/>';
									}
								}
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CHARACTERISTICS_DISPLAY'); ?></td>
						<td>
							<?php echo $this->characteristicdisplayType->display('config[characteristic_display]',$this->config->get('characteristic_display'));?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CHARACTERISTICS_VALUES_ORDER'); ?></td>
						<td>
							<?php echo $this->characteristicorderType->display('config[characteristics_values_sorting]',$this->config->get('characteristics_values_sorting'));?>
						</td>
					</tr>
<?php if(!$this->config->get('append_characteristic_values_to_product_name',1)){ ?>
					<tr>
						<td class="key"><?php echo JText::_('APPEND_CHARACTERISTICS_VALUE_TO_PRODUCT_NAME'); ?></td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', 'config[append_characteristic_values_to_product_name]','',$this->config->get('append_characteristic_values_to_product_name',1));?>
						</td>
					</tr>
<?php } ?>
					<tr>
						<td class="key"><?php echo JText::_('CHARACTERISTICS_DISPLAY_TEXT'); ?></td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', 'config[characteristic_display_text]','',$this->config->get('characteristic_display_text'));?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('UPDATE_AFTER_ORDER_CONFIRM'); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "config[update_stock_after_confirm]",'',$this->config->get('update_stock_after_confirm')); ?>
						</td>
					</tr>
				<tr>
					<td class="key"><?php echo JText::_('DIMENSIONS_DISPLAY'); ?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[dimensions_display]','',$this->config->get('dimensions_display',0));?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('WEIGHT_DISPLAY'); ?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[weight_display]','',$this->config->get('weight_display',0));?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_ADD_TO_CART_BUTTON_FOR_FREE_PRODUCT'); ?></td>
					<td>
						<?php echo JHTML::_('hikaselect.booleanlist', 'config[display_add_to_cart_for_free_products]','',$this->config->get('display_add_to_cart_for_free_products'));?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('QUANTITY_FIELD'); ?></td>
					<td>
						<?php echo $this->quantity->display('config[show_quantity_field]',$this->config->get('show_quantity_field'));?>
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('DISPLAY_CONTACT_BUTTON'); ?></td>
					<td>
						<?php if(hikashop_level(1)){
							echo $this->contact->display('config[product_contact]',$this->config->get('product_contact',0));
						}else{
							echo hikashop_getUpgradeLink('essential');
						} ?>
					</td>
				</tr>
				<tr>
						<td class="key"><?php echo JText::_('PRODUCT_ASSOCIATION_IN_BOTH_WAYS'); ?></td>
						<td><?php
							echo JHTML::_('hikaselect.booleanlist', "config[product_association_in_both_ways]" , '',$this->config->get('product_association_in_both_ways',1) );
						?></td>
				</tr>
				</table>
			</fieldset>
		</div>
	<!-- CART -->
		<div id="main_cart">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'HIKASHOP_CHECKOUT_CART' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('CART_RETAINING_PERIOD'); ?></td>
						<td>
							<?php echo $this->delayTypeRetaining->display('config[cart_retaining_period]',$this->config->get('cart_retaining_period',2592000)); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CART_RETAINING_PERIOD_CHECK_FREQUENCY'); ?></td>
						<td>
							<?php echo $this->delayTypeCarts->display('config[cart_retaining_period_check_frequency]',$this->config->get('cart_retaining_period_check_frequency',86400));?><br/>
							<?php echo JText::sprintf('LAST_CHECK',hikashop_getDate($this->config->get('cart_retaining_period_checked')));?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CLEAN_CART_WHEN_ORDER_IS'); ?></td>
						<td><?php
							$values = array(
								JHTML::_('select.option', 'order_created',JText::_('CREATED')),
								JHTML::_('select.option', 'order_confirmed',JText::_('CONFIRMED'))
							);
							echo JHTML::_('select.genericlist', $values, 'config[clean_cart]', 'class="inputbox" size="1"', 'value', 'text', $this->config->get('clean_cart','order_created'));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('AFTER_ADD_TO_CART'); ?></td>
						<td>
							<?php echo $this->cart_redirect->display('config[redirect_url_after_add_cart]',$this->config->get('redirect_url_after_add_cart'));?>
						</td>
					</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('LIMIT_NUMBER_OF_ITEMS_IN_CART'); ?>
							</td>
							<td>
								<?php
								if(hikashop_level(1)){
									$item_limit = $this->config->get('cart_item_limit',0);
									if(empty($item_limit)){
										$item_limit = JText::_('UNLIMITED');
									}
									?>
									<input name="config[cart_item_limit]" type="text" value="<?php echo $item_limit;?>" onfocus="if(this.value=='<?php echo JText::_('UNLIMITED',true); ?>') this.value='';" />
								<?php }else{
									echo hikashop_getUpgradeLink('essential');
								} ?>
							</td>
						</tr>
					<tr>
						<td class="key"><?php echo JText::_('NOTICE_POPUP_DISPLAY_TIME'); ?></td>
						<td>
							<input type="text" class="inputbox" size="10" name="config[popup_display_time]" value="<?php echo (int)$this->config->get('popup_display_time',2000);?>"/>ms
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('WHEN_CART_IS_EMPTY'); ?></td>
						<td>
							<input type="text" class="inputbox" name="config[redirect_url_when_cart_is_empty]" value="<?php echo $this->escape($this->config->get('redirect_url_when_cart_is_empty'));?>"/>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ALLOW_USERS_TO_PRINT_CART'); ?></td>
						<td>
							<?php 	echo JHTML::_('hikaselect.booleanlist', 'config[print_cart]','',$this->config->get('print_cart',0)); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('GROUP_OPTIONS_WITH_PRODUCT'); ?></td>
						<td>
							<?php 	echo JHTML::_('hikaselect.booleanlist', 'config[group_options]','',$this->config->get('group_options',0)); ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ADD_TO_CART_POPUP_SIZE'); ?></td>
						<td>
							<input type="text" style="width:50px;" class="inputbox" name="config[add_to_cart_popup_width]" value="<?php echo $this->escape($this->config->get('add_to_cart_popup_width','480'));?>"/>
							x
							<input type="text" style="width:50px;" class="inputbox" name="config[add_to_cart_popup_height]" value="<?php echo $this->escape($this->config->get('add_to_cart_popup_height','140'));?>"/>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	<!-- ORDER -->
		<div id="main_order">
			<fieldset class="adminform">
			<legend><?php echo JText::_( 'HIKASHOP_ORDER' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('ORDER_NUMBER_FORMAT'); ?></td>
						<td>
							<?php
							if(hikashop_level(1)){ ?>
								<input class="inputbox" type="text" name="config[order_number_format]" value="<?php echo $this->escape($this->config->get('order_number_format','{automatic_code}')); ?>">
							<?php }else{
								echo hikashop_getUpgradeLink('essential');
							}?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('INVOICE_NUMBER_FORMAT'); ?></td>
						<td><?php if(hikashop_level(1)){ ?>
							<input class="inputbox" type="text" name="config[invoice_number_format]" value="<?php echo $this->escape($this->config->get('invoice_number_format','{automatic_code}')); ?>">
						<?php
							}else{
								echo hikashop_getUpgradeLink('essential');
							}
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('INVOICE_RESET_FREQUENCY'); ?></td>
						<td><?php
							if(hikashop_level(1)){
								$values = array(
									JHTML::_('select.option', '', JText::_('HIKA_NONE')),
									JHTML::_('select.option', 'year', JText::_('EVERY_YEARS')),
									JHTML::_('select.option', 'month', JText::_('EVERY_MONTHS')),
								);
								$value = $this->config->get('invoice_reset_frequency', '');
								if(strpos($value, '/') !== false) {
									$values[] = JHTML::_('select.option', $value, $value);
								}
								echo JHTML::_('select.genericlist', $values, 'config[invoice_reset_frequency]', 'class="inputbox" size="1"', 'value', 'text', $value);
							}else{
								echo hikashop_getUpgradeLink('essential');
							}
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('DEFAULT_ORDER_STATUS'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[order_created_status]',
									$this->config->get('order_created_status'),
									hikashopNameboxType::NAMEBOX_SINGLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('UNPAID_ORDER_STATUSES'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[order_unpaid_statuses]',
									explode(',',$this->config->get('order_unpaid_statuses')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CONFIRMED_ORDER_STATUS'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[order_confirmed_status]',
									$this->config->get('order_confirmed_status'),
									hikashopNameboxType::NAMEBOX_SINGLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('INVOICE_ORDER_STATUSES'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[invoice_order_statuses]',
									explode(',',$this->config->get('invoice_order_statuses')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CANCELLED_ORDER_STATUS'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[cancelled_order_status]',
									explode(',',$this->config->get('cancelled_order_status')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CANCELLABLE_ORDER_STATUS'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[cancellable_order_status]',
									explode(',',$this->config->get('cancellable_order_status')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PAYMENT_CAPTURE_ORDER_STATUS'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[payment_capture_order_status]',
									explode(',',$this->config->get('payment_capture_order_status')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ALLOW_CUSTOMERS_TO_PAY_ORDERS_AFTERWARD'); ?></td>
						<td>
							<?php if(hikashop_level(1)){
								echo JHTML::_('hikaselect.booleanlist', 'config[allow_payment_button]','onchange="displayPaymentChange(this.value)"',$this->config->get('allow_payment_button'));
							}else{
								echo hikashop_getUpgradeLink('essential');
							} ?>
						</td>
					</tr>
					<?php $payment_change='';
					if(!$this->config->get('allow_payment_button')){
						$payment_change='style="display:none;"';
					} ?>
					<tr id="hikashop_payment_change_row" <?php echo $payment_change; ?>>
						<td class="key"><?php echo JText::_('ALLOW_CUSTOMERS_TO_CHANGE_THEIR_PAYMENT_METHOD_AFTER_CHECKOUT'); ?></td>
						<td>
							<?php if(hikashop_level(1)){
								echo JHTML::_('hikaselect.booleanlist', 'config[allow_payment_change]','',$this->config->get('allow_payment_change',1));
							}else{
								echo hikashop_getUpgradeLink('essential');
							} ?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ALLOW_CUSTOMERS_TO_REORDER'); ?></td>
						<td>
							<?php if(hikashop_level(1)){
								echo JHTML::_('hikaselect.booleanlist', 'config[allow_reorder]','',$this->config->get('allow_reorder',0));
							}else{
								echo hikashop_getUpgradeLink('essential');
							} ?>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	<!-- FILES -->
		<div id="main_files">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'FILES' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('ALLOWED_FILES'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[allowedfiles]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedfiles'))); ?>" />
						</td>
					</tr>

					<tr>
						<td class="key"><?php echo JText::_('UPLOAD_SECURE_FOLDER'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[uploadsecurefolder]" size="50" value="<?php echo $this->config->get('uploadsecurefolder'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('UPLOAD_FOLDER'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[uploadfolder]" size="50" value="<?php echo $this->config->get('uploadfolder'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PAYMENT_LOG_FILE'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[payment_log_file]" size="50" value="<?php echo $this->config->get('payment_log_file'); ?>" />
							<?php
							echo $this->popup->display(
								'<button class="btn" onclick="return false">'.JText::_('REPORT_SEE').'</button>',
								'PAYMENT_LOG_FILE',
								hikashop_completeLink('config&task=seepaymentreport',true),
								'hikashop_log_file',
								760, 480, '', '', 'link'
							);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ORDER_STATUS_FOR_DOWNLOAD'); ?></td>
						<td>
							<?php
								echo $this->nameboxType->display(
									'config[order_status_for_download]',
									explode(',',$this->config->get('order_status_for_download')),
									hikashopNameboxType::NAMEBOX_MULTIPLE,
									'order_status',
									array(
										'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
									)
								);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('DOWNLOAD_TIME_LIMIT'); ?></td>
						<td><?php
							echo $this->delayTypeDownloads->display('config[download_time_limit]',$this->config->get('download_time_limit',0),3);
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('DOWNLOAD_NUMBER_LIMIT'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[download_number_limit]" value="<?php echo $this->config->get('download_number_limit'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CSV_SEPARATOR'); ?></td>
						<td><?php
							echo $this->csvType->display('config[csv_separator]',$this->config->get('csv_separator',';'));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CSV_DECIMAL_SEPARATOR'); ?></td>
						<td><?php
							echo $this->csvDecimalType->display('config[csv_decimal_separator]',$this->config->get('csv_decimal_separator','.'));
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ENABLE_CUSTOMER_DOWNLOADLIST'); ?></td>
						<td>
							<?php if(hikashop_level(1)){
								echo JHTML::_('hikaselect.booleanlist', 'config[enable_customer_downloadlist]','',$this->config->get('enable_customer_downloadlist'));
							}else{
								echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
							} ?>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	<!-- IMAGES -->
		<div id="main_images">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'HIKA_IMAGES' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td class="key"><?php echo JText::_('ALLOWED_IMAGES'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[allowedimages]" size="50" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedimages'))); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('DEFAULT_IMAGE'); ?></td>
						<td>
<?php
	$options = array(
		'upload' => true,
		'gallery' => true,
		'text' => JText::_('HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD'),
		'uploader' => array('config', 'default_image'),
	);
	$params = new stdClass();
	$params->file_path = $this->config->get('default_image', '');
	$params->field_name = 'config[default_image]';
	$img = $this->imageHelper->getThumbnail($params->file_path, array(100, 100), array('default' => true));
	if($img->success) {
		$params->thumbnail_url = $img->url;
		$params->origin_url = $img->origin_url;
	}
	$js = '';
	$content = hikashop_getLayout('upload', 'image_entry', $params, $js);
	echo $this->uploaderType->displayImageSingle('hikashop_config_default_image', $content, $options);
?>
						</td>
					</tr>
					<tr>
						<td class="key" ><?php echo JText::_('THUMBNAIL'); ?></td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "config[thumbnail]" , '',$this->config->get('thumbnail') );?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('THUMBNAIL_X'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[thumbnail_x]" value="<?php echo $this->config->get('thumbnail_x'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('THUMBNAIL_Y'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[thumbnail_y]" value="<?php echo $this->config->get('thumbnail_y'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PRODUCT_PAGE_IMAGE_X'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[product_image_x]" value="<?php echo $this->config->get('product_image_x'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PRODUCT_PAGE_IMAGE_Y'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[product_image_y]" value="<?php echo $this->config->get('product_image_y'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key" ><?php echo JText::_('IMAGE_FORCE_SIZE'); ?></td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "config[image_force_size]" , '',$this->config->get('image_force_size',true) );?>
						</td>
					</tr>
					<tr>
						<td class="key" ><?php echo JText::_('IMAGE_SCALE_MODE'); ?></td>
						<td>
							<?php
							$arr = array(
								JHTML::_('select.option', 'inside', JText::_('IMAGE_KEEP_RATIO') ),
								JHTML::_('select.option', 'outside', JText::_('IMAGE_CROP') ),
							);
							echo JHTML::_('hikaselect.genericlist', $arr, "config[image_scale_mode]" , '', 'value', 'text',$this->config->get('image_scale_mode','inside') );?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('IMAGE_X'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[image_x]" value="<?php echo $this->config->get('image_x'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('IMAGE_Y'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[image_y]" value="<?php echo $this->config->get('image_y'); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('WATERMARK_ON_IMAGES'); ?></td>
						<td><?php if(hikashop_level(2)){ ?>
<?php
	$options = array(
		'upload' => true,
		'gallery' => true,
		'text' => JText::_('HIKA_DEFAULT_IMAGE_EMPTY_UPLOAD'),
		'uploader' => array('config', 'watermark'),
	);
	$params = new stdClass();
	$params->file_path = $this->config->get('watermark', '');
	$params->delete = true;
	$params->uploader_id = 'hikashop_config_watermark_image';
	$params->field_name = 'config[watermark]';
	$js = '';
	$content = hikashop_getLayout('upload', 'image_entry', $params, $js);
	if(!empty($params->empty)) $options['empty'] = true;
	echo $this->uploaderType->displayImageSingle('hikashop_config_watermark_image', $content, $options);
							}else{
								echo hikashop_getUpgradeLink('business');
							}
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('WATERMARK_OPACITY'); ?></td>
						<td>
							<?php if(hikashop_level(2)){ ?>
								<input class="inputbox" type="text" name="config[opacity]" value="<?php echo $this->config->get('opacity',0); ?>" size="3" />%
							<?php  }else{
								echo hikashop_getUpgradeLink('business');
							}?>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
	<!-- EMAILS -->
		<div id="main_emails">
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'EMAILS' ); ?></legend>
				<table class="admintable table" style="width:100%" cellspacing="1">
					<tr>
						<td width="185" class="key"><?php echo JText::_('FROM_NAME'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[from_name]" size="40" value="<?php echo $this->escape($this->config->get('from_name')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('FROM_ADDRESS'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[from_email]" size="40" value="<?php echo $this->escape($this->config->get('from_email')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('REPLYTO_NAME'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[reply_name]" size="40" value="<?php echo $this->escape($this->config->get('reply_name')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('REPLYTO_ADDRESS'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[reply_email]" size="40" value="<?php echo $this->escape($this->config->get('reply_email')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('BOUNCE_ADDRESS'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[bounce_email]" size="40" value="<?php echo $this->escape($this->config->get('bounce_email')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('PAYMENTS_NOTIFICATIONS_EMAIL_ADDRESS'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[payment_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('payment_notification_email')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ORDER_CREATION_NOTIFICATION_EMAIL_ADDRESS'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[order_creation_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('order_creation_notification_email')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ADD_NAMES'); ?></td>
						<td><?php
							echo $this->elements->add_names;
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('ENCODING_FORMAT'); ?></td>
						<td><?php
							echo $this->elements->encoding_format;
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('CHARSET'); ?></td>
						<td><?php
							echo $this->elements->charset;
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('WORD_WRAPPING'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[word_wrapping]" size="10" value="<?php echo $this->config->get('word_wrapping',0) ?>">
						</td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('EMBED_IMAGES'); ?></td>
						<td><?php
							echo $this->elements->embed_images;
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('EMBED_ATTACHMENTS'); ?></td>
						<td><?php
							echo $this->elements->embed_files;
						?></td>
					</tr>
					<tr>
						<td class="key"><?php echo JText::_('MULTIPLE_PART'); ?></td>
						<td><?php
							echo $this->elements->multiple_part;
						?></td>
					</tr>
					<?php if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){ ?>
					<tr>
						<td class="key"><?php echo JText::_('MAIL_FOLDER'); ?></td>
						<td>
							<input class="inputbox" type="text" name="config[mail_folder]" size="60" value="<?php echo $this->escape($this->config->get('mail_folder')); ?>">
						</td>
					</tr>
					<?php } ?>
				</table>
			</fieldset>
		</div>
	<!-- Advanced -->
		<div id="main_advanced">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'HIKA_ADVANCED_SETTINGS' ); ?></legend>
					<table class="admintable table" style="width:100%" cellspacing="1">
						<tr>
							<td class="key"><?php echo JText::_('DIMENSION_SYMBOLS'); ?></td>
							<td>
								<input class="inputbox" type="text" name="config[volume_symbols]" value="<?php echo $this->config->get('volume_symbols'); ?>">
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('WEIGHT_SYMBOLS'); ?></td>
							<td>
								<input class="inputbox" type="text" name="config[weight_symbols]" value="<?php echo $this->config->get('weight_symbols'); ?>">
							</td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('USE_AJAX_WHEN_POSSIBLE_FOR_ADD_TO_CART'); ?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', 'config[ajax_add_to_cart]','',$this->config->get('ajax_add_to_cart',0));
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('DEACTIVATE_BUFFERING_AND_COMPRESSION'); ?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', 'config[deactivate_buffering_and_compression]','',$this->config->get('deactivate_buffering_and_compression',0));
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('REDIRECT_POST_MODE'); ?></td>
							<td><?php
								echo JHTML::_('hikaselect.booleanlist', 'config[redirect_post]','',$this->config->get('redirect_post',0));
							?></td>
						</tr>
						<tr>
							<td class="key"><?php echo JText::_('SERVER_CURRENT_URL_MODE'); ?></td>
							<td><?php
							$arr = array(
								JHTML::_('select.option', '0', JText::_('HIKA_AUTOMATIC') ),
								JHTML::_('select.option', 'REDIRECT_URL',  JText::_('REDIRECT_URL') ),
								JHTML::_('select.option', 'REQUEST_URI',  JText::_('REQUEST_URI') ),
							);
							echo JHTML::_('hikaselect.genericlist', $arr, "config[server_current_url_mode]" , '', 'value', 'text',$this->config->get('server_current_url_mode','REQUEST_URI') );?></td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('AFFILIATE');?>
							</td>
							<td>
								<input name="config[partner_id]" type="text" value="<?php echo $this->config->get('partner_id')?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('VERSION');?>
							</td>
							<td>
								HikaShop <?php echo $this->config->get('level').' '.$this->config->get('version'); ?> [1510211553]
							</td>
						</tr>
<?php
?>
					</table>
				</fieldset>
				</div>
			</td>
		</tr>
	</table>
</div>
