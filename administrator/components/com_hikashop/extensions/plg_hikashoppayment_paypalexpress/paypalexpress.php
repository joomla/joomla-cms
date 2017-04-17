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
class plgHikashoppaymentPaypalExpress extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','BRL','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD','SEK',
		'DKK','PLN','NOK','HUF','CZK','MXN','MYR','PHP','TWD','THB','ILS','TRY'
	);

	var $pluginConfig = array(
		'apiuser' => array("API_USERNAME",'input'),
		'apipassword' => array("API_PASSWORD",'input'),
		'apisignature' => array("API_SIGNATURE",'input'),
		'apiversion' => array("API_VERSION",'input'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'landingpage' => array('Express Checkout as guest by default', 'boolean','0'),
		'cartdetail' => array('SEND_CART_DETAIL', 'boolean','0'),
		'displaycheckout' => array('DISPLAY_BUTTON_CHECKOUT', 'boolean','0'),
		'displaycart' => array('DISPLAY_BUTTON_CART', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	var $multiple = true;
	var $name = 'paypalexpress';
	var $doc_form = 'paypalexpress';
	var $button = '';

	function __construct(&$subject, $config)
	{
		$notif = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&amp;notif_payment='.$this->name.'&tmpl=component';
		$this->pluginConfig['notification'][0] =  JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X','Paypal');
		$lang = JFactory::getLanguage();
		$this->button = '<div id="paypal_express_button" style="width:144px; height:46px; margin-top:15px;"><a href="'.$notif.'&setExpressCheckout=1"><img src="https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecshortcut&locale='.str_replace('-','_',$lang->get('tag')).'" align="left"></a></div>';
		return parent::__construct($subject, $config);
	}

	function onPaymentDisplay(&$order,&$methods,&$usable_methods)
	{
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id)
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

	}


	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		$element->payment_name='PaypalExpress';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->address_type="billing";
		$element->payment_params->apiversion='109.0';
		$element->payment_params->landingpage=1;
		$element->payment_params->notification=1;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}


	function onPaymentNotification(&$statuses)
	{
		$cartClass = hikashop_get('class.cart');
		$cart = $cartClass->loadFullCart(true);
		$currencyClass = hikashop_get('class.currency');
		$currency = $currencyClass->get($cart->full_total->prices[0]->price_currency_id);
		$config = hikashop_config();
		$discountstate = $config->get('discount_before_tax');
		$app = JFactory::getApplication();
		if(!empty($_SESSION['paypal_express_checkout_payment_method'])){
			$this->pluginParams($_SESSION['paypal_express_checkout_payment_method']->payment_id);
		}else{
			$this->pluginParams();
		}

		$menuClass = hikashop_get('class.menus');
		$url_menu_id = $menuClass->getCheckoutMenuIdForURL();

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$url_menu_id;
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component'.$url_menu_id;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end'.$url_menu_id;
		if (isset($cart->full_total->prices[0]->price_value_without_payment_with_tax))
			$amountTheorical = round($cart->full_total->prices[0]->price_value_without_payment_with_tax,2);
		else
			$amountTheorical = round($cart->full_total->prices[0]->price_value_with_tax,2);

		$vars = $this->getRequestDatas();


		if (isset($vars['setExpressCheckout']))
		{
			if ($this->plugin_params->cartdetail)
			{
				$i = 0;
				$tax = 0;
				$amountCalculated = 0;
				$items = array();
				$config = hikashop_config();
				$group = $config->get('group_options',0);
				foreach ($cart->products as $p)
				{
					$productprice = 0;
					$optionalProdDesc = '';
					if($p->cart_product_quantity<=0) continue;
					if($group)
					{
						if($p->cart_product_option_parent_id)
							continue;
						foreach ($cart->products as $p2) {
							if ($p2->cart_product_option_parent_id==$p->cart_product_id)
							{
								if(isset($p2->prices[0]->unit_price)){
									$unit2 =& $p2->prices[0]->unit_price;
								}else{
									$unit2 =& $p2->prices[0];
								}
								$productprice += round($unit2->price_value,2);
								$tax += (round($unit2->price_value_with_tax,2) - round($unit2->price_value,2))*$p->cart_product_quantity;
								$amountCalculated += $p->cart_product_quantity*round($unit2->price_value,2);
								$optionalProdDesc .= $p2->product_name.',';
							}
						}
					}
					if(isset($p->prices[0]->unit_price)){
						$unit =& $p->prices[0]->unit_price;
					}else{
						$unit =& $p->prices[0];
					}
					$tax += (round($unit->price_value_with_tax,2) - round($unit->price_value,2))*$p->cart_product_quantity;
					$amountCalculated += $p->cart_product_quantity*round($unit->price_value,2);
					$productprice += round($unit->price_value,2);
					$item = array(
						'L_PAYMENTREQUEST_0_NAME'.$i => strip_tags($p->product_name),
						'L_PAYMENTREQUEST_0_NUMBER'.$i => $p->product_id,
						'L_PAYMENTREQUEST_0_AMT'.$i =>$productprice,
						'L_PAYMENTREQUEST_0_QTY'.$i => $p->cart_product_quantity,
					);
					if (!empty($optionalProdDesc)){
						$optionalProdDesc = rtrim($optionalProdDesc,',');
						if(strlen($optionalProdDesc)>=127){
							$optionalProdDesc = substr($optionalProdDesc,0,123).'...';
						}
						$item['L_PAYMENTREQUEST_0_DESC'.$i] = $optionalProdDesc;
					}
					$i++;
					$items = array_merge($items,$item);
				}

				$shipping = 0;
				if (!empty($cart->shipping))
					$shipping = round($cart->shipping[0]->shipping_price_with_tax,2);

				$discount = 0;
				if (!empty($cart->coupon))
					$discount = round($cart->coupon->discount_value,2);

				if ($this->plugin_data->payment_price>0 or $this->plugin_params->payment_percentage>0)
				{
					$feesValue = round($this->plugin_data->payment_price + $amountTheorical * $this->plugin_params->payment_percentage / 100,2);
					$item = array(
						'L_PAYMENTREQUEST_0_NAME'.$i => JText::_('HIKASHOP_PAYMENT'),
						'L_PAYMENTREQUEST_0_NUMBER'.$i => 99999, //?
						'L_PAYMENTREQUEST_0_AMT'.$i => $feesValue,
						'L_PAYMENTREQUEST_0_QTY'.$i => 1,
					);
					$amountCalculated += $feesValue;
					$items = array_merge($items,$item);
				}

				$amountTheorical += round($amountTheorical * $this->plugin_params->payment_percentage / 100,2);
				$amountTheorical += round($this->plugin_data->payment_price,2);

				$endItem = array(
					'PAYMENTREQUEST_0_ITEMAMT' => $amountCalculated,
					'PAYMENTREQUEST_0_TAXAMT' => $tax,
					'PAYMENTREQUEST_0_SHIPPINGAMT' => $shipping,
					'PAYMENTREQUEST_0_SHIPDISCAMT' => -$discount,
					'PAYMENTREQUEST_0_HANDLINGAMT' => 0,
					'PAYMENTREQUEST_0_AMT' => $amountTheorical,
					'PAYMENTREQUEST_0_CURRENCYCODE' => $currency->currency_code,
					'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
					'ALLOWNOTE' => 1
				);

				$varform = array_merge($items,$endItem);
			}

			if(empty($this->plugin_params->landingpage)) $this->plugin_params->landingpage = 'Login';
			else $this->plugin_params->landingpage ='Billing';

			$arrayparams = array(
				'USER' => $this->plugin_params->apiuser,
				'PWD' => $this->plugin_params->apipassword,
				'SIGNATURE' => $this->plugin_params->apisignature,
				'VERSION' => $this->plugin_params->apiversion,
				'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
				'SOLUTIONTYPE' => 'Sole',
				'LANDINGPAGE' => $this->plugin_params->landingpage,
				'PAYMENTREQUEST_0_AMT' => $amountTheorical,
				'PAYMENTREQUEST_0_CURRENCYCODE' => $currency->currency_code,
				'RETURNURL' => $notify_url,
				'CANCELURL' => $cancel_url,
				'METHOD' => 'SetExpressCheckout'
			);

			if ($this->plugin_params->cartdetail)
				$varform = array_merge($arrayparams, $varform);
			else
				$varform = $arrayparams;


			$request = $this->initCurlToPaypal($varform,$this->plugin_params->sandbox);
			$post_response = curl_exec($request);

			if(empty($post_response))
			{
				$app->enqueueMessage('The connection to the payment plateform did not succeed. It is often caused by the hosting company blocking external connections so you should contact him for further guidance. The cURL error message was: '.curl_error($request),'error');
				curl_close ($request);
				return false;
			}
			else
			{
				curl_close ($request);
				$vars = $this->getPostDatas($post_response);

				$urlstring = $_SERVER['HTTP_REFERER'];
				$post = $this->getPostDatas($urlstring);

				if ($vars['ACK']=='Success')
				{
					if ($this->plugin_params->sandbox)
						$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$vars['TOKEN'];
					else
						$url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$vars['TOKEN'];
					$order = $this->createOrder($cart);
					$orderClass = hikashop_get('class.order');
					$order->order_payment_id = $this->plugin_data->payment_id;
					$order->order_payment_method = $this->name;
					$order->history->history_data = $vars['TOKEN'];
					$order->order_id = $orderClass->save($order);

					$app->redirect($url);
				}
				else
				{
					$error = 'Connection failure - error code : '.$vars['L_ERRORCODE0'].' , error message : '.$vars['L_LONGMESSAGE0'];
					if($this->plugin_params->debug){
						$this->writeToLog('Fail at step 0 :'.$error);
						$this->writeToLog(print_r($varform,true));
					}
					$app->enqueueMessage($error);
					(isset($post['step'])) ? $add = '&step='.$post['step'] : $add = '&step=0';
					$app->redirect($cancel_url.$add);
					return false;
				}
			}
		}
		else
		{

			$datas = $this->loadOrderId($vars['token']);
			$orderid = $datas[0]->history_order_id;

			$dbOrder = $this->getOrder($orderid);
			$this->loadOrderData($dbOrder);
			if(!empty($dbOrder->order_payment_id)){
				$this->pluginParams($dbOrder->order_payment_id);
			}

			$cancel_url .= '&order_id='.$orderid.$this->url_itemid;
			$return_url .= '&order_id='.$orderid.$this->url_itemid;

			$varform = array(
				'USER' => $this->plugin_params->apiuser,
				'PWD' => $this->plugin_params->apipassword,
				'SIGNATURE' => $this->plugin_params->apisignature,
				'VERSION' => $this->plugin_params->apiversion,
				'TOKEN' => $vars['token'],
				'METHOD' => 'GetExpressCheckoutDetails'
			);

			$request = $this->initCurlToPaypal($varform,$this->plugin_params->sandbox);
			$post_response = curl_exec($request);

			if(empty($post_response))
			{
				if($this->plugin_params->debug)
					$this->writeToLog('Fail at step 1 :'.curl_error($request));
				$app->enqueueMessage('The connection to the payment plateform did not succeed. It is often caused by the hosting company blocking external connections so you should contact him for further guidance. The cURL error message was: '.curl_error($request),'error');
				curl_close ($request);
				$this->modifyOrder($orderid, $this->plugin_params->invalid_status, true, true);
				$app->redirect($cancel_url);
				return false;
			}
			else
			{
				curl_close ($request);
				$vars = $this->getPostDatas($post_response);
				if ($vars['ACK']!='Success')
				{
					if($this->plugin_params->debug)
						$this->writeToLog('Fail at step 2 :'.curl_error($request));
					$app->enqueueMessage('An error has been encountered - error code : '.$vars['L_ERRORCODE0'].' , error message : '.$vars['L_LONGMESSAGE0']);
					$this->modifyOrder($orderid, $this->plugin_params->invalid_status, true, true);
					$app->redirect($cancel_url);
					return false;
				}
				else
				{
					if (empty($dbOrder->order_user_id))
					{
						$user = $this->createUser($vars);
						$userClass = hikashop_get('class.user');
						$getuser = $userClass->get($vars['EMAIL'],'email');
						if (empty($getuser))
							$userid = $userClass->save($user);
						else
							$userid = $getuser->user_id;
						if($this->plugin_params->debug)
							$this->writeToLog('user : '.$userid);
					}
					else
						$userid = $dbOrder->order_user_id;

					if (!isset($dbOrder->order_shipping_address_id) || $dbOrder->order_shipping_address_id==0)
					{
						$address = $this->createAddress($vars,$userid);
						$addressClass = hikashop_get('class.address');
						$addressid = $addressClass->save($address);
						$dbOrder->order_shipping_address_id = $addressid;
						$dbOrder->order_billing_address_id = $addressid;
					}

					$orderClass = hikashop_get('class.order');
					$dbOrder->order_user_id = $userid;
					$orderClass->save($dbOrder);

					$varform = array(
						'USER' => $this->plugin_params->apiuser,
						'PWD' => $this->plugin_params->apipassword,
						'SIGNATURE' => $this->plugin_params->apisignature,
						'VERSION' => $this->plugin_params->apiversion,
						'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
						'PAYERID' => $vars['PAYERID'],
						'TOKEN' => $vars['TOKEN'],
						'PAYMENTREQUEST_0_AMT' => $amountTheorical,
						'PAYMENTREQUEST_0_CURRENCYCODE' => $currency->currency_code,
						'METHOD' => 'DoExpressCheckoutPayment'
					);

					$request = $this->initCurlToPaypal($varform,$this->plugin_params->sandbox);
					$post_response = curl_exec($request);
					if(empty($post_response))
					{
						if($this->plugin_params->debug)
							$this->writeToLog('Fail at step 3 :'.curl_error($request));
						$app->enqueueMessage('The connection to the payment plateform did not succeed. It is often caused by the hosting company blocking external connections so you should contact him for further guidance. The cURL error message was: '.curl_error($request),'error');
						curl_close ($request);
						$this->modifyOrder($orderid, $this->plugin_params->invalid_status, true, true);
						$app->redirect($cancel_url);
						return false;
					}
					else
					{
						curl_close ($request);
						$vars = $this->getPostDatas($post_response);
						if ($vars['ACK']!='Success')
						{
							if($this->plugin_params->debug)
								$this->writeToLog('Fail at step 4 :'.curl_error($request));
							$app->enqueueMessage('An error has been encountered - error code : '.$vars['L_ERRORCODE0'].' , error message : '.$vars['L_LONGMESSAGE0']);
							$this->modifyOrder($orderid, $this->plugin_params->invalid_status, true, true);
							$app->redirect($cancel_url);
							return false;
						}
						else
						{
							if($this->plugin_params->debug)
								$this->writeToLog('SUCCESS !');
							$history = new stdClass();
							$history->notified = 1;
							$history->data = 'PayPal transaction id: '.$vars['PAYMENTINFO_0_TRANSACTIONID'] . "\r\n\r\n";
							$this->modifyOrder($orderid, $this->plugin_params->verified_status, $history, true);
							$this->app->redirect($return_url);
							return true;
						}
					}

				}
			}
		}
	}


	function onHikashopBeforeDisplayView(&$element)
	{
		$this->pluginParams();
		$this->layout =  $element->getLayout();
		if (isset($element->ctrl))
			if($element->ctrl=='checkout' && $this->layout=='step' && $this->canDisplayButton($element))
				ob_start();
	}


	function onHikashopAfterDisplayView(&$element)
	{
		if (isset($element->ctrl))
		{
			if ($element->ctrl=='product' && $this->layout=='cart' && $element->element->cart_type=='cart' && $this->canDisplayButton($element,'cart'))
				echo $this->button;
			elseif($element->ctrl=='checkout' && $this->layout=='step' && $this->canDisplayButton($element))
			{

				$contenttable = array();
				$contentth = array();
				$inserthtml = '<tr>';
				$html = ob_get_clean();

				if (preg_match_all('#<div id="hikashop_checkout_cart"(.*?)</table>#iUs',$html,$contenttable))
				{
					$old_cart = $contenttable[0][0];
					preg_match_all('#</th>#i',$old_cart,$contentth);
					for ($i=0 ; $i<count($contentth[0])-2 ; $i++) //2 dynamic
						$inserthtml .= '<td></td>';
					$inserthtml .= '<td colspan="2">'.$this->button.'</td></tr></tbody>';
					$new_cart = str_replace('</tbody>',$inserthtml,$old_cart);
					$html = str_replace($old_cart,$new_cart,$html);
				}
				echo $html;
			}
		}
	}

	function canDisplayButton(&$view,$type='checkout'){
		static $method = null;
		if(is_null($method)){
			$class = hikashop_get('class.cart');
			$cart = $class->loadFullCart(true);
			$methods = $this->loadPaymentMethod('','all',$cart);
			if($methods){
				$already = array();
				$max = 0;
				foreach($methods as $k => $method) {
					if(!empty($method->payment_params)){
						$methods[$k]->payment_params = @unserialize($method->payment_params);
					}
					$methods[$k]->enabled = true;
					if(empty($method->ordering)) {
						$max++;
						$methods[$k]->ordering = $max;
					}
					while(isset($already[$methods[$k]->ordering])) {
						$max++;
						$methods[$k]->ordering = $max;
					}
					$already[$methods[$k]->ordering] = true;
				}
				$usable_methods = array();
				parent::onPaymentDisplay($cart,$methods,$usable_methods);
				if(count($usable_methods)){
					$method = reset($usable_methods);
				}
			}
		}

		if(is_object($method) && empty($method->errors)){
			if ($method->payment_params->displaycart && $type=='cart')
				return true;
			elseif ($method->payment_params->displaycheckout && $type=='checkout')
				return true;
		}
		return false;
	}



	function getPostDatas($string)
	{
		$datas = explode('&',$string);
		$vars = array();
		foreach ($datas as $d)
		{
			$value = explode('=',$d);
			$vars[$value[0]]=urldecode($value[1]);
		}
		return $vars;
	}


	function getRequestDatas()
	{
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value)
		{
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[$key]=$value;
		}
		return $vars;
	}


	function initCurlToPaypal($varform, $sandbox)
	{
		$post_string = '';
		if ($sandbox)
			$url = 'https://api-3t.sandbox.paypal.com/nvp';
		else
			$url = 'https://api-3t.paypal.com/nvp';

		foreach( $varform as $key => $value )
			$post_string .= "$key=" . urlencode( $value ) . "&";

		$post_string = rtrim( $post_string, "& " );
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);

		return $request;
	}


	function createOrder($cart)
	{
		$app = JFactory::getApplication();
		$config =& hikashop_config();
		$shippings = array();

		$shipping = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_method');
		$shipping_id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id');
		if(!empty($shipping)) {
			foreach($shipping as $ship) {
				$ship = explode('@', $ship, 2);
				$current_id = 0;
				foreach($shipping_id as $sid) {
					list($i, $k) = explode('@', $sid, 2);
					if($k == $ship[1]) {
						$current_id = $i;
						break;
					}
				}
				$shippings[$ship[1]] = array('id' => $current_id, 'name' => $ship[0]);
			}

			$shippingClass = hikashop_get('class.shipping');
			$methods =& $shippingClass->getShippings($cart);
			$shipping_groups = $shippingClass->getShippingGroups($cart, $methods);
		}
		$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address');

		$order = new stdClass();
		$order->order_user_id = @hikashop_loadUser();
		$order->order_status = $config->get('order_created_status');
		$order->order_shipping_address_id = $shipping_address;
		$order->order_billing_address_id = $billing_address;
		$order->order_discount_code = @$cart->coupon->discount_code;
		$order->order_currency_id = $cart->full_total->prices[0]->price_currency_id;
		$order->order_type = 'sale';
		$order->order_full_price = $cart->full_total->prices[0]->price_value_with_tax;
		$order->order_tax_info = @$cart->full_total->prices[0]->taxes;

		$products = array();
		foreach($cart->products as $product) {
			if($product->cart_product_quantity > 0) {
				$orderProduct = new stdClass();
				$orderProduct->product_id = $product->product_id;
				$orderProduct->order_product_quantity = $product->cart_product_quantity;
				$orderProduct->order_product_name = $product->product_name;
				$orderProduct->cart_product_id = $product->cart_product_id;
				$orderProduct->cart_product_option_parent_id = $product->cart_product_option_parent_id;
				$orderProduct->order_product_code = $product->product_code;
				$orderProduct->order_product_price = @$product->prices[0]->unit_price->price_value;
				$orderProduct->order_product_wishlist_id = $product->cart_product_wishlist_id;
				$orderProduct->product_subscription_id = @$product->product_subscription_id;

				$tax = 0;
				if(!empty($product->prices[0]->unit_price->price_value_with_tax) && bccomp($product->prices[0]->unit_price->price_value_with_tax,0,5))
					$tax = $product->prices[0]->unit_price->price_value_with_tax-$product->prices[0]->unit_price->price_value;
				$orderProduct->order_product_tax = $tax;

				$characteristics = '';
				if(!empty($product->characteristics))
					$characteristics = serialize($product->characteristics);
				$orderProduct->order_product_options = $characteristics;

				if(!empty($product->discount))
					$orderProduct->discount = $product->discount;

				if(!empty($itemFields)) {
					foreach($itemFields as $field) {
						$namekey = $field->field_namekey;
						if(isset($product->$namekey))
							$orderProduct->$namekey = $product->$namekey;
					}
				}

				if(isset($product->prices[0]->unit_price->taxes))
					$orderProduct->order_product_tax_info = $product->prices[0]->unit_price->taxes;

				if(isset($product->files))
					$orderProduct->files =& $product->files;

				if(!empty($shipping)) {
					$shipping_done = false;
					foreach($shipping_groups as $group_key => $group_products) {
						if(!isset($shippings[$group_key]))
							continue;
						foreach($group_products->products as $group_product) {
							if((int)$group_product->cart_product_id == (int)$product->cart_product_id) {
								$orderProduct->order_product_shipping_id = $shippings[$group_key]['id'] . '@' . $group_key;
								$orderProduct->order_product_shipping_method = $shippings[$group_key]['name'];
								$shipping_done = true;
								break;
							}
						}
						if($shipping_done)
							break;
					}
				}
				$products[] = $orderProduct;
			}
		}
		$cart->products = &$products;

		$order->order_shipping_price = 0.0;
		$order->order_shipping_tax = 0.0;
		$order->order_shipping_params = null;
		if(!empty($cart->shipping)) {
			$order->order_shipping_params = new stdClass();
			$order->order_shipping_params->prices = array();
			foreach($cart->shipping as $cart_shipping) {
				$price_key = $cart_shipping->shipping_id;
				if(isset($cart_shipping->shipping_warehouse_id))
					$price_key .= '@' . $cart_shipping->shipping_warehouse_id;

				$order->order_shipping_params->prices[$price_key] = new stdClass();
				$order->order_shipping_params->prices[$price_key]->price_with_tax = $cart_shipping->shipping_price_with_tax;

				$order->order_shipping_price += $cart_shipping->shipping_price_with_tax;

				if(!empty($cart_shipping->shipping_price_with_tax) && !empty($cart_shipping->shipping_price)) {
					$order->order_shipping_tax += $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;
					$order->order_shipping_params->prices[$price_key]->tax = $cart_shipping->shipping_price_with_tax - $cart_shipping->shipping_price;
					if(!empty($cart_shipping->taxes)) {
						$order->order_shipping_params->prices[$price_key]->taxes = array();
						foreach($cart_shipping->taxes as $tax) {
							$order->order_shipping_params->prices[$price_key]->taxes[$tax->tax_namekey] = $tax->tax_amount;
							if(isset($order->order_tax_info[$tax->tax_namekey])) {
								if(empty($order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping))
									$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = 0;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping += $tax->tax_amount;
							} else {
								$order->order_tax_info[$tax->tax_namekey] = $tax;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_shipping = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
								$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
							}
						}
					}
				}
			}
		}

		$discount_price = 0;
		$discount_tax=0;

		if(!empty($cart->coupon)&& !empty($cart->coupon->total->prices[0]->price_value_without_discount_with_tax)){
			$discount_price=@$cart->coupon->total->prices[0]->price_value_without_discount_with_tax-@$cart->coupon->total->prices[0]->price_value_with_tax;
			if(!empty($cart->coupon->total->prices[0]->price_value_with_tax)&&!empty($cart->coupon->total->prices[0]->price_value)){
				$discount_tax = (@$cart->coupon->total->prices[0]->price_value_without_discount_with_tax-@$cart->coupon->total->prices[0]->price_value_without_discount)-(@$cart->coupon->total->prices[0]->price_value_with_tax-@$cart->coupon->total->prices[0]->price_value);
				if(isset($cart->coupon->taxes)){
					foreach($cart->coupon->taxes as $tax){
						if(isset($order->order_tax_info[$tax->tax_namekey])){
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $tax->tax_amount;
						}else{
							$order->order_tax_info[$tax->tax_namekey]=$tax;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount_for_coupon = $order->order_tax_info[$tax->tax_namekey]->tax_amount;
							$order->order_tax_info[$tax->tax_namekey]->tax_amount = 0;
						}
					}
				}
			}
		}
		$order->order_discount_tax = $discount_tax;
		$order->order_discount_price = $discount_price;
		$order->order_shipping_id = $shipping_id;
		$order->order_shipping_method = $shipping;
		$order->cart =& $cart;
		$order->history = new stdClass();
		$order->history->history_reason = JText::_('ORDER_CREATED');
		$order->history->history_notified = 0;
		$order->history->history_type = 'creation';

		if(!empty($shippings)) {
			if(count($shippings) == 1) {
				$s = reset($shippings);
				$order->order_shipping_id = $s['id'];
				$order->order_shipping_method = $s['name'];
			} else {
				$ids = array();
				foreach($shippings as $key => $ship)
					$ids[] = $ship['id'] . '@' . $key;
				$order->order_shipping_id = implode(';', $ids);
				$order->order_shipping_method = '';
			}
		}

		return $order;
	}


	function loadOrderId($token)
	{
		$db = JFactory::getDBO();
		$sql = 'SELECT history_order_id FROM `#__hikashop_history` hh INNER JOIN `#__hikashop_order` ho ON hh.history_order_id = ho.order_id WHERE history_data = '.$db->Quote(htmlspecialchars($token)).';';
		$db->setQuery($sql);
		$db->query();
		$datas = $db->loadObjectList();
		return $datas;
	}


	function loadPaymentMethod($name,$type,&$cart)
	{
		static $datas = array();
		if(empty($name)){
			$name = $this->name;
		}
		if(!isset($datas[$name])){
			$db = JFactory::getDBO();
			$where = array('payment_type = '.$db->Quote($name),'payment_published=\'1\'');

			$shipping = '';
			if(!empty($cart->shipping))
				$shipping = $cart->shipping[0]->shipping_type.'_'.$cart->shipping[0]->shipping_id;
			if(!empty($shipping)){
				$where[] = '(payment_shipping_methods IN (\'\',\'_\') OR payment_shipping_methods LIKE \'%\n'.$shipping.'\n%\' OR payment_shipping_methods LIKE \''.$shipping.'\n%\' OR payment_shipping_methods LIKE \'%\n'.$shipping.'\' OR payment_shipping_methods LIKE \''.$shipping.'\')';
			}
			$currency = hikashop_getCurrency();
			if(!empty($currency)){
				$where[] = "(payment_currency IN ('','_','all') OR payment_currency LIKE '%,".intval($currency).",%')";
			}

			$app = JFactory::getApplication();
			if(!$app->isAdmin()){
				hikashop_addACLFilters($where,'payment_access');
			}

			if(!empty($where)) {
				$where = ' WHERE '.implode(' AND ',$where);
			} else {
				$where = '';
			}

			$sql = 'SELECT * FROM `#__hikashop_payment`'.$where.' ORDER BY payment_ordering';
			$db->setQuery($sql);
			$db->query();
			$datas[$name] = $db->loadObjectList();
		}

		if (!empty($datas[$name])){
			if($type=='id'){
				return $datas[$name][0]->payment_id;
			}elseif($type=='first'){
				return $datas[$name][0];
			}else{
				return $datas[$name];
			}
		}

		return false;
	}


	function createUser($vars)
	{
		$user = new stdClass();
		$user->user_cms_id = 0;
		$user->user_email = $vars['EMAIL'];
		return $user;
	}


	function createAddress($vars,$userid)
	{
		if (empty($userid))
			return false;
		else
		{
			$db = JFactory::getDBO();

			$sql = 'SELECT zone_namekey FROM `#__hikashop_zone` hz WHERE zone_name_english = '.$db->Quote($vars['PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME']).' AND zone_type = \'country\';';
			$db->setQuery($sql);
			$db->query();
			$datas = $db->loadObjectList();
			if (!empty($datas) && count($datas)==1)
				$country = $datas[0];
			else
				$country = $vars['PAYMENTREQUEST_0_SHIPTOCOUNTRYNAME'];

			if (isset($vars['PAYMENTREQUEST_0_SHIPTOSTATE']))
			{
				$sql ='SELECT zone_namekey FROM `#__hikashop_zone` hz WHERE zone_code_2 = '.$db->Quote($vars['PAYMENTREQUEST_0_SHIPTOSTATE']).' AND zone_type = \'state\';';
				$db->setQuery($sql);
				$db->query();
				$datas = $db->loadObjectList();
				if (!empty($datas) && count($datas)==1)
					$state = $datas[0];
				else
					$state = $vars['PAYMENTREQUEST_0_SHIPTOSTATE'];
			}
			else
			{
				$state = "NULL";
			}

			$address = new stdClass();
			$address->address_user_id = $userid;
			$address->address_firstname = $vars['FIRSTNAME'];
			$address->address_lastname = $vars['LASTNAME'];
			$address->address_street = $vars['PAYMENTREQUEST_0_SHIPTOSTREET'];
			$address->address_post_code = $vars['PAYMENTREQUEST_0_SHIPTOZIP'];
			$address->address_city = $vars['PAYMENTREQUEST_0_SHIPTOCITY'];
			$address->address_state = $state;
			$address->address_country = $country;
		}

		return $address;
	}
}
