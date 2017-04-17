<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php echo JText::sprintf('HI_CUSTOMER',@$data->customer->name);?>


<?php
$url = $data->order_number;
$config =& hikashop_config();
if($config->get('simplified_registration',0)!=2){
	$url .= ' ( '.$data->order_url.' )';
}
echo JText::sprintf('ORDER_CREATION_SUCCESS_ON_WEBSITE_AT_DATE',$url,HIKASHOP_LIVE, hikashop_getDate(time(),'%d %B %Y'), hikashop_getDate(time(),'%H:%M'));?>

--------------------------------------
 <?php echo JText::_('SUMMARY_OF_YOUR_ORDER');?>
--------------------------------------

<?php echo JText::_('CART_PRODUCT_NAME')."\t".JText::_('CART_PRODUCT_UNIT_PRICE')."\t".JText::_('CART_PRODUCT_QUANTITY')."\t".JText::_('HIKASHOP_TOTAL');?>

<?php
foreach($data->cart->products as $item){
	$price = $item->order_product_price*$item->order_product_quantity;
	echo strip_tags($item->order_product_name) . "\t" . $currencyHelper->format($item->order_product_price,$data->order_currency_id)."\t".$item->order_product_quantity."\t".$currencyHelper->format($price,$data->order_currency_id)."\n";
}

if(bccomp($data->order_discount_price,0,5)){
	echo JText::_('HIKASHOP_COUPON').' : '.$currencyHelper->format($data->order_discount_price*-1,$data->order_currency_id)."\n";
}
if(bccomp($data->order_shipping_price,0,5)){
	echo JText::_('HIKASHOP_SHIPPING_METHOD').' : '.$currencyHelper->format($data->order_shipping_price,$data->order_currency_id)."\n";
}
if(!empty($data->additional)) {
	$exclude_additionnal = explode(',', $config->get('order_additional_hide', ''));
	foreach($data->additional as $additional) {
		if(in_array($additional->name, $exclude_additionnal)) continue;
		echo JText::_($additional->order_product_name).' : ';
		if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
			echo $currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
		} else {
			echo $additional->order_product_options;
		}
		echo "\n";
	}
}
if($data->cart->full_total->prices[0]->price_value!=$data->cart->full_total->prices[0]->price_value_with_tax){
	if($config->get('detailed_tax_display') && !empty($data->order_tax_info)){
		foreach($data->order_tax_info as $tax){
			echo $tax->tax_namekey. ' : '.$currencyHelper->format($tax->tax_amount,$data->order_currency_id)."\n";
		}
	}else{
		echo JText::sprintf('TOTAL_WITHOUT_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value,$data->order_currency_id))."\n";
	}
	echo JText::sprintf('TOTAL_WITH_VAT',$currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->order_currency_id))."\n\n";
}else{
	echo JText::_('HIKASHOP_TOTAL'). ' : '.$currencyHelper->format($data->cart->full_total->prices[0]->price_value_with_tax,$data->order_currency_id)."\n";
}

$app = JFactory::getApplication();
if($app->isAdmin()){
	$view = 'order';
}else{
	$view = 'address';
}
$addressClass = hikashop_get('class.address');
if(!empty($data->cart->billing_address)){
	echo JText::_('HIKASHOP_BILLING_ADDRESS')."\n";
	echo $addressClass->displayAddress($data->order_addresses_fields,$data->order_addresses[$data->cart->billing_address->address_id],$view,true);
}
if(!empty($data->order_shipping_method)) {
	$currentShipping = hikashop_import('hikashopshipping',$data->order_shipping_method);
	if(method_exists($currentShipping, 'getShippingAddress')) {
		$override = $currentShipping->getShippingAddress($data->order_shipping_id);
		if($override !== false) {
			$data->override_shipping_address = $override;
		}
	}
}
if(!empty($data->cart->has_shipping) && (!empty($data->cart->shipping_address) || !empty($data->override_shipping_address))) {
	if( !empty($data->override_shipping_address) ) {
		echo str_replace('<br/>',"\r\n",$data->override_shipping_address);
	} else {
		echo JText::_('HIKASHOP_SHIPPING_ADDRESS')."\n";
		echo $addressClass->displayAddress($data->order_addresses_fields,$data->order_addresses[$data->cart->shipping_address->address_id],$view,true);
	}
}

$fields = $fieldsClass->getFields('frontcomp',$data,'order','');
foreach($fields as $fieldName => $oneExtraField) {
	$fieldData = trim(@$data->$fieldName);
	if(empty($fieldData)) continue;
	echo $fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField,$data->$fieldName)."\r\n";
}

if(!$app->isAdmin()){
	if($data->cart->full_total->prices[0]->price_value_with_tax>0) echo JText::_('ORDER_VALID_AFTER_PAYMENT')."\n\n";
	echo JText::sprintf('THANK_YOU_FOR_YOUR_ORDER',HIKASHOP_LIVE)."\n\n";
}
echo str_replace('<br/>',"\n",JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name));?>
