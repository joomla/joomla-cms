<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for RBS Worldpay Business Gateway.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derive from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * In order to configure and use this plug-in you must have a Worldpay Business Gateway account.
 * Worldpay Business Gateway is sometimes refered to as 'Select Junior'.
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgHikashoppaymentbf_rbsbusinessgateway extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS'
	);
	var $debugData = array();
	var $multiple = true;
	var $name = 'bf_rbsbusinessgateway';

	var $pluginConfig = array(
		'instid' => array('Installation ID', 'input'),
		'url' => array('URL', 'input'),
		'descProductName' => array('Use Single Product Name in Description', 'boolean','0'),
		'desc' => array('Default Description', 'big-textarea'),
		'displayForGuests' => array('Display for guest users', 'boolean','0'),
		'address_type' => array('Customer Address', 'list',array(
			'' => 'NO_ADDRESS',
			'billing' => 'HIKASHOP_BILLING_ADDRESS',
			'shipping' => 'HIKASHOP_SHIPPING_ADDRESS',
			'billing,shipping' => 'Both addresses')
		),
		'fixContact' => array('Fix Contact', 'boolean','0'),
		'hideContact' => array('Hide Contact', 'boolean','0'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'showVars' => array('Show Parameters', 'boolean','0'),
		'testMode' => array('Test Mode', 'boolean','0'),
		'hostname' => array('Notification hostname', 'input'),
		'address_format' => array('Address name format', 'input'),
		'ips' => array('IPS', 'big-textarea'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'cancel_url' => array('Cancel URL', 'input'),
		'redirect_button' => array('Redirect to Worldpay Button', 'big-textarea')
	);

	function isShippingValid($shipping) {
		return true;
	}

	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if(!$this->isShippingValid(@$order->shipping))
			return true;
		$this->user = hikashop_loadUser(true);
		return parent::onPaymentDisplay($order, $methods, $usable_methods);
	}

	function checkPaymentDisplay(&$method, &$order) {
		if(!empty($method->payment_params->testMode)) {
			if(isset($this->user->user_tester) && @$this->user->user_tester != 'Y')
				return false;
		} else if(@$this->user->user_tester == 'Y') {
			return false;
		}
		if(!@$method->payment_params->displayForGuests && !$this->user) {
			return false;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order,&$do) {
		if(empty($order->order_payment_method) || $order->order_payment_method!='bf_rbsbusinessgateway') return;
		if (!$this->isShippingValid(@$order->cart->shipping)) {
			$do = false;
			JError::raiseWarning(100, 'Error - This payment method is not available with the selected shipping method.' );
		}
	}

	function addAddress($user, $order, $address_type, &$vars, $prefix=null) {
		$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
		if(!empty($address)) {
			$cart = hikashop_get('class.cart');
			$cart->loadAddress($order->cart,$address,'object',$address_type);
			if(empty($this->payment_params->address_format))$this->payment_params->address_format = '{address_lastname}, {address_firstname}, ';
			$vars[$prefix.'name'] = trim(str_replace(array('{address_lastname}','{address_firstname}','{address_middle_name}'),array(@$order->cart->$address_type->address_lastname,@$order->cart->$address_type->address_firstname,@$order->cart->$address_type->address_middle_name),$this->payment_params->address_format));
			$vars[$prefix.'address']=trim($order->cart->$address_type->address_street . ",\n" . @$order->cart->$address_type->address_city, ",\n ");
			$vars[$prefix.'postcode']=@$order->cart->$address_type->address_post_code;
			$vars[$prefix.'country']=@$order->cart->$address_type->address_country->zone_code_2;
			if (empty($vars[$prefix.'country']) && $vars[$prefix.'currency'] == 'GBP') {
				$vars[$prefix.'country'] = 'GB';
			}
			if (empty($prefix)) {
				$vars[$prefix.'email']=$user->user_email;
				$vars[$prefix.'tel']=@$order->cart->$address_type->address_telephone;
			}
		}
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$method =& $methods[$method_id];

		$x = isset($order->cart->products);
		$y = isset($order->products);
		$vars = array(
			'instId'   => $method->payment_params->instid,
			'cartId'   => $order->order_id,
			'amount'   => $order->order_full_price,
			'currency' => $this->currency->currency_code,
		);
		if (!empty($method->payment_params->descProductName) && count($order->cart->products) == 1) {
			foreach($order->cart->products as $product) {
				$vars['desc'] = substr($product->order_product_name, 0, 255);
			}
		}
		else $vars['desc'] = substr($method->payment_params->desc,0,255);
		if (!empty($method->payment_params->notification)) {
			$vars['MC_callback'] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=' . $method->payment_type . '&tmpl=component&lang='.$this->locale;
		}
		if (!empty($method->payment_params->fixContact)) $vars['fixContact'] = null;
		if (!empty($method->payment_params->hideContact)) $vars['hideContact'] = null;
		if(!empty($method->payment_params->address_type)) {
			switch ($method->payment_params->address_type) {
				case 'billing';
					$this->addAddress($this->user, $order, 'billing_address', $vars);
					break;
				case 'shipping';
					$this->addAddress($this->user, $order, 'shipping_address', $vars);
					break;
				case 'both';
					$this->addAddress($this->user, $order, 'billing_address', $vars);
					$vars['withDelivery'] = 'true';
					$this->addAddress($this->user, $order, 'shipping_address', $vars, 'delv');
					break;
			}
		}
		if (!empty($method->payment_params->testMode)) $vars['testMode'] = '100';
		if (empty($vars['name'])) $vars['name'] = $this->user->username;
		$i = 1;
		$tax_cart = 0;
		$config =& hikashop_config();
		$group = $config->get('group_options',0);
		foreach($order->cart->products as $product){
			if($group && $product->order_product_option_parent_id) continue;
			$vars["C_item_name_".$i]=substr($product->order_product_name,0,127);
			$vars["C_item_number_".$i]=$product->order_product_code;
			$vars["C_quantity_".$i]=$product->order_product_quantity;
			$amount_item=round($product->order_product_price,(int)$this->currency->currency_locale['int_frac_digits']);
			$tax_item =round($product->order_product_tax,(int)$this->currency->currency_locale['int_frac_digits']);
			if (!empty($method->payment_params->show_tax_amount)) $tax_cart+=($tax_item*$product->order_product_quantity);
			else $amount_item+=$tax_item;
			$vars["C_amount_".$i]=$amount_item;
			$i++;
		}
		if(!empty($order->order_shipping_price) || !empty($order->cart->shipping->shipping_name)){
			$vars["C_item_name_".$i]=JText::_('HIKASHOP_SHIPPING');
			if(!empty($order->order_shipping_price)){
				if (!empty($method->payment_params->show_tax_amount) && !empty($order->cart->shipping->shipping_price)) {
					$amount_item=round($order->cart->shipping->shipping_price,(int)$this->currency->currency_locale['int_frac_digits']);
					$tax_item=round($order->cart->shipping->shipping_price_with_tax,(int)$this->currency->currency_locale['int_frac_digits'])-$amount_item;
					$tax_cart+=$tax_item;
					$vars["C_amount_".$i]=$amount_item;
				}
				else $vars["C_amount_".$i]=round($order->order_shipping_price,(int)$this->currency->currency_locale['int_frac_digits']);
			}
			else $vars["C_amount_".$i] = 0;
			$vars["C_quantity_".$i]=1;
			if (empty($order->cart->shipping->shipping_name)) $vars["item_number_".$i]= $order->order_shipping_method;
			else $vars["C_item_number_".$i]= ucwords($order->cart->shipping->shipping_name);
			$i++;
		}
		if(bccomp($tax_cart,0,5)){
			$vars['C_tax_cart']=$tax_cart;
		}
		if(!empty($order->cart->coupon)){
			$vars["C_discount_amount_cart"]=round($order->cart->coupon->discount_value,(int)$this->currency->currency_locale['int_frac_digits']);
		}
		$this->vars = $vars;

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){

		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		foreach($_POST as $key => $value){
			$key = $filter->clean($key);
			if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
				$value = JRequest::getString($key);
				$vars[$key] = $value;
				$data[] = $key . '=' . urlencode($value);
			}
		}
		$data = implode('&',$data).'&cmd=_notify-validate';
		$order_id = (int)@$vars['cartId'];
		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['cartId'];
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}
		$this->loadOrderData($dbOrder);

		if(@$vars['instId'] != $this->payment_params->instid)
			return false;

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',hikashop_encode($dbOrder),HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		$hostError = -1;
		$ip = hikashop_getIP();
		if(!empty($element->payment_params->hostname)){ // \.outbound\.wp3\.rbsworldpay\.com
			$hostname = gethostbyaddr($ip);
			if (preg_match('#' . $this->payment_params->hostname . '#i', $hostname)) $hostError = 0;
			else $hostError = 1;
		}
		if ($hostError < 0 && !empty($this->payment_params->ips)) {
			$ips = str_replace(array('.','*',','),array('\.','[0-9]+','|'),$this->payment_params->ips);
			if (!empty($ips)) {
				if (preg_match('#('.implode('|',$ips).')#',$ip)) $hostError = 0;
				else $hostError = 1;
			}
		}
		if ($hostError > 0) {
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Worldpay Business Gateway').' '.JText::sprintf('IP_NOT_VALID',hikashop_encode($dbOrder));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Worldpay Business Gateway',$ip,'See Hostname / IPs defined in configuration'))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#ip').$order_text;
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return false;
		}
		switch ($vars['transStatus']) {
			case 'Y':
				break;
			default:
				$email = new stdClass();
				$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Worldpay Business Gateway',$vars['transStatus'],$dbOrder->order_number);
				$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Worldpay Business Gateway',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#status').$order_text;
				$email->body = $body;

				$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

				if($this->payment_params->debug) {
					echo 'payment '.$vars['transStatus']."\n\n\n";
					echo '[OK]';
				}
				$dbg = ob_get_clean();
				$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;
				echo '<meta http-equiv="refresh" content="5;url='.$return_url.'" />
		<style>
		.pageHeading {
			font-family: Verdana, Arial, sans-serif;
			font-size: 20px;
			font-weight: bold;
			color: #9a9a9a;
		}

		.main {
			font-family: Verdana, Arial, sans-serif;
			font-size: 11px;
			line-height: 1.5;
		}
		</style>

		<p class="pageHeading">'.JText::sprintf('TRANSACTION_PROCESSING_ERROR',$vars['transStatus']).'</p>

		<form action="'.$return_url.'" method="post">
			<div align="center">
				<input name="submit" type="submit" class="btn" value="'.JText::_('GO_BACK_TO_SHOP').'" />
				</div>
		</form>

		<p>&nbsp;</p>

		<WPDISPLAY ITEM=banner>';
				ob_start();
				if($this->payment_params->debug) {
					echo $dbg;
				}
			return false;
		}

		$history = new stdClass();
		$email = new stdClass();
		$history->notified=0;
		$history->amount= @$vars['amount'].@$vars['currency'];
		$history->data = '';
		$price_check = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits'] );

		if($price_check != @$vars['amount'] || $this->currency->currency_code != @$vars['currency']){
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Worldpay Business Gateway').JText::_('INVALID_AMOUNT');
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Worldpay Business Gateway',$history->amount,$price_check.$this->currency->currency_code))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#amount').$order_text;
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);
			return false;
		}
		switch ($vars['transStatus']) {
			case 'Y':
				$payment_status = 'Authenticated';
				$order_status = $this->payment_params->verified_status;
				$history->notified = 1;
				break;
			default:
				$payment_status = 'Unknown';
				$order_status = $this->payment_params->invalid_status;
				$order_text = JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-rbsworldpay-error#pending')."\r\n\r\n".$order_text;
		}
		$mail_status=$statuses[$order->order_status];
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Worldpay Business Gateway',$payment_status,$dbOrder->order_number);
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Worldpay Business Gateway',$order_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;
		$email->body = $body;

		$this->modifyOrder($order_id, $order_status, $history, $email);

		$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		if($this->payment_params->debug) {
			echo '[OK]';
		}
		$dbg = ob_get_clean();
		echo '<meta http-equiv="refresh" content="5;url='.$return_url.'" />
<style>
.pageHeading {
	font-family: Verdana, Arial, sans-serif;
	font-size: 20px;
	font-weight: bold;
	color: #9a9a9a;
}

.main {
	font-family: Verdana, Arial, sans-serif;
	font-size: 11px;
	line-height: 1.5;
}
</style>

<p class="pageHeading">'.JText::_('THANK_YOU_FOR_PURCHASE').'</p>

<form action="'.$return_url.'" method="post">
	<div align="center">
		<input name="submit" type="submit" class="btn" value="'.JText::_('GO_BACK_TO_SHOP').'" />
		</div>
</form>

<p>&nbsp;</p>

<WPDISPLAY ITEM=banner>';
		ob_start();
		if($element->payment_params->debug) {
			echo $dbg;
		}
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Worldpay Business Gateway';
		$element->payment_description='You can pay by debit or credit card using this payment method';

		$element->payment_params->url='https://secure-test.worldpay.com/wcc/purchase';
		$element->payment_params->notification=1;
		$element->payment_params->hostname = '\.outbound\.wp3\.rbsworldpay\.com';
		$element->payment_params->address_format = '{address_lastname}, {address_firstname}, ';
		$element->payment_params->ips = '';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->confirmed_status='confirmed';
		$element->payment_params->redirect_button='style="background: url(\'https://secure-test.wp3.rbsworldpay.com/images/rbswp/brand.gif\') top left no-repeat;' .
				 'width:139px;height:33px;border:solid 1px #7C98B7;cursor:pointer;margin:10px 100px;"';
	}
}
?>