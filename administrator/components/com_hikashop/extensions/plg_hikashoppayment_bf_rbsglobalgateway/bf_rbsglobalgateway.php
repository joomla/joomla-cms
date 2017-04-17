<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for Worldpay Global Gateway using XML Redirect.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derived from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * See: http://www.worldpay.com/support/kb/gg/submittingtransactionsredirect/rxml.html
 *
 * In order to configure and use this plug-in you must have a Worldpay Global Gateway account.
 * Worldpay Global Gateway is sometimes referred to as 'BiBit'.
 */
defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__) . DS . 'bf_rbsglobalgateway_helper.php';
class plgHikashoppaymentbf_rbsglobalgateway extends JPlugin {
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS'
	);
	var $debugData = array();
	function isShippingValid($shipping) {
		return true;
	}
	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if (!$this->isShippingValid(@$order->shipping)) return false;
		if (empty($order->total)) return false;
		if (empty($methods)) return false;
		$user = hikashop_loadUser(true);
		if (!$user) return false;
		$found = false;
		foreach($methods as $method){
		if($method->payment_type!='bf_rbsglobalgateway') continue;
			if ($method->payment_params->showVars) {
				if (isset($user->user_tester)) {
					if (@$user->user_tester != 'Y') continue;
				}
			}
			else if (@$user->user_tester == 'Y') continue;
			if (!$method->enabled) continue;
			if(!empty($method->payment_zone_namekey)){
				$zoneClass=hikashop_get('class.zone');
				$zones = $zoneClass->getOrderZones($order);
				if(!in_array($method->payment_zone_namekey,$zones)) continue;
			}
			$currencyClass = hikashop_get('class.currency');
			$null=null;
			$currency_id = intval(@$order->total->prices[0]->price_currency_id);
			$currency = $currencyClass->getCurrencies($currency_id,$null);
			if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
				continue;
			}
			$usable_methods[$method->ordering]=$method;
			$found = true;
		}
		return $found;
	}
	function onBeforeOrderCreate(&$order,&$do) {
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return true;
		}
		if(empty($order->order_payment_method) || $order->order_payment_method!='bf_rbsglobalgateway') return;
		if (!$this->isShippingValid(@$order->cart->shipping)) {
			$do = false;
			JError::raiseWarning(100, 'Error - This payment method is not available with the selected shipping method.' );
		}
		if(!function_exists('curl_init')){
			$app->enqueueMessage('The WorldPay Global Gateway payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$do = false;
		}
		if(!class_exists('SimpleXMLElement')){
			$app->enqueueMessage('The WorldPay Global Gateway payment plugin needs the SimpleXML library of PHP5 installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$do = false;
		}
	}
	function onPaymentSave(&$cart,&$rates,&$payment_id){
		$usable = array();
		if ($this->onPaymentDisplay($cart,$rates,$usable)) {
			$payment_id = (int) $payment_id;
			foreach($usable as $usable_method){
				if($usable_method->payment_id==$payment_id){
					return $usable_method;
				}
			}
		}
		return false;
	}
	private function _display(&$params, &$order, &$user) {
		if (empty($params->htmllayout)) return null;
		ob_start();
		require_once dirname(__FILE__) . DS . $params->htmllayout;
		return ob_get_clean();
	}
	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		$method =& $methods[$method_id];
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency = $currencies[$order->order_currency_id];
		$method->payment_params->_exponent = (int)$currency->currency_locale['int_frac_digits'];
		$method->payment_params->_currency_symbol = $currency->currency_symbol;
		$method->payment_params->payment_type = $method->payment_type;
		$user = hikashop_loadUser(true);
		$lang = JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));
		if (!empty($method->payment_params->instid)) {
			$method->payment_params->contactInformation = null;
			$method->payment_params->billingNotice = null;
		}
		$xml = '<submit>';
		$xml .= '<order orderCode="' . $order->order_number . '"';
		if (!empty($method->payment_params->instid)) $xml .= ' installationId="' . $method->payment_params->instid . '"';
		$xml .= '>';
		$xml .= '<description>' . htmlspecialchars($method->payment_params->description) . '</description>';
		$xml .= '<amount value="' . round($order->order_full_price*pow(10, $method->payment_params->_exponent)) . '" ' .
						 'currencyCode="' . $currency->currency_code . '" ' .
						 'exponent="' . $method->payment_params->_exponent . '"/>';
		$cdata = null;
		if (empty($method->payment_params->instid)) {
			if (!empty($method->payment_params->orderContentCSS)) {
				$cdata .= '<style type="text/css">' . rbsglobalgateway_helper::parseCSS($method->payment_params->orderContentCSS) . '</style>';
			}
		}
		$cdata .= $this->_display($method->payment_params, $order, $user);
		$xml .= '<orderContent><![CDATA[' . $cdata . ']]></orderContent>';
		$xml .= '<paymentMethodMask>';
		foreach(preg_split('/[, ]+/', $method->payment_params->paymentMethodMask) as $include) {
			if (!empty($include)) $xml .= '<include code="' . $include . '"/>';
		}
		$xml .= '</paymentMethodMask>';
		$xml .= '<shopper>';
		$xml .= '<shopperEmailAddress>' . $user->user_email . '</shopperEmailAddress>';
		$xml .= '</shopper>';
		if(!empty($method->payment_params->address_type)) {
			switch ($method->payment_params->address_type) {
				case 'billing';
					$xml .= rbsglobalgateway_helper::xmlAddress($method->payment_params, $user, $order, 'billing_address', 'shippingAddress');
					break;
				case 'shipping';
				case 'billing,shipping';
				case 'shipping,billing';
					$xml .= rbsglobalgateway_helper::xmlAddress($method->payment_params, $user, $order, 'shipping_address', 'shippingAddress');
					break;
			}
		}
		$xml .= '</order>';
		$xml .= '</submit>';
		$xmlResult = rbsglobalgateway_helper::sendXML($method->payment_params, $xml);
		if (empty($xmlResult)) return false;
		$xmlElement = new SimpleXMLElement($xmlResult);
		$xmlArray = rbsglobalgateway_helper::xml2phpArray($xmlElement);
		if (!empty($method->payment_params->showVars)) rbsglobalgateway_helper::showXMLReply($xmlArray);
		if (!rbsglobalgateway_helper::validService($xmlArray, $method->payment_params)) return false;
		$error = @$xmlArray['reply'][0]['error'][0];
		if (!empty($error)) {
			self::raiseError($payment_params->showVars, 'ERROR (' . $error['code'] . ')<br />' . $error[0]);
			return false;
		}
		$orderStatus = $xmlArray['reply'][0]['orderStatus'][0];
		if ($orderStatus['orderCode'] != $order->order_number) {
			rbsglobalgateway_helper::raiseError($method->payment_params->showVars, 'ERROR -> Order ID mismatch.');
			return false;
		}
		$reference = $orderStatus['reference'][0];
		$RBSRedirectURL =  $reference[0];
		if (empty($RBSRedirectURL)) {
			rbsglobalgateway_helper::raiseError($method->payment_params->showVars, 'ERROR -> Redirect URL not specified.');
			return false;
		}
		$RBSPaymentReference = $xmlElement->reply->orderStatus->reference['id'];
		if (empty($RBSPaymentReference)) {
			rbsglobalgateway_helper::raiseError($method->payment_params->showVars, 'ERROR -> Payment reference not specified.');
			return false;
		}
		rbsglobalgateway_helper::saveRBSReference($order->order_id, $method->payment_params->paymentRefField, $RBSPaymentReference);
		$vars = Array(
			'country' => 'GB',
			'language' => 'EN'
								);
		if (empty($method->payment_params->instid)) {
			if (!empty($method->payment_params->bodyAttr)) $RBSRedirectURL .= '&bodyAttr=' . rbsglobalgateway_helper::encodeAttribute($method->payment_params->bodyAttr);
			if (!empty($method->payment_params->fontAttr)) $RBSRedirectURL .= '&fontAttr=' . rbsglobalgateway_helper::encodeAttribute($method->payment_params->fontAttr);
		}
		if($method->payment_params->notification) {
			$vars['successURL'] = rbsglobalgateway_helper::notificationURL($method->payment_params, $locale);
			$vars['pendingURL'] = rbsglobalgateway_helper::notificationURL($method->payment_params, $locale);
			$vars['failureURL'] = rbsglobalgateway_helper::notificationURL($method->payment_params, $locale);
			$vars['cancelURL']  = rbsglobalgateway_helper::notificationURL($method->payment_params, $locale);
		}
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
		$app = JFactory::getApplication();
		$name = $method->payment_type.'_end.php';
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
		if(!file_exists($path)){
			if(version_compare(JVERSION,'1.6','<')) $path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
			else $path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
			if(!file_exists($path)) return true;
		}
		require($path);
		return true;
	}
	function onPaymentNotification(&$statuses){
		$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','bf_rbsglobalgateway');
		if(empty($elements)) return false;
		$element = reset($elements);
		if(!$element->payment_params->notification) return false;
		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value){
			$key = $filter->clean($key);
			if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
				switch ($key) {
					case 'option':
					case 'ctrl':
					case 'task':
					case 'notif_payment':
					case 'tmpl':
					case 'lang':
					case 'status':
					case 'orderKey':
					case 'paymentStatus':
					case 'paymentAmount':
					case 'paymentCurrency':
					case 'orderAmount':
					case 'orderCurrency':
					case 'mac':
					case 'jlbz':
					case 'view':
						$value = JRequest::getString($key);
						$vars[$key] = $value;
						$data[] = $key . '=' . urlencode($value);
						break;
				}
			}
		}
		if (empty($vars['orderKey'])) {
			rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Missing Order Key');
			return false;
		}
		$orderKey = explode('^', @$vars['orderKey']);
		if ($orderKey[0] != @$element->payment_params->adminCode) {
			rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Invalid admin code: ' . $orderKey[0]);
		}
		if (@$orderKey[1] != @$element->payment_params->merchantCode) {
			rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Invalid merchant code: ' . $orderKey[1]);
		}
		$orderCode = @$orderKey[2];
		if (empty($orderCode)) {
			rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Missing Order Code');
			return false;
		}
		if (empty($vars['paymentCurrency'])) {
			$vars['paymentCurrency'] = @$vars['orderCurrency'];
			if (empty($vars['paymentCurrency'])) {
				rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Missing Payment Currency');
				 return false;
			}
		}
		if (empty($vars['paymentAmount'])) {
			$vars['paymentAmount'] = @$vars['orderAmount'];
			if (empty($vars['paymentAmount'])) {
				rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Missing Payment Amount');
				 return false;
			}
		}
		if (!empty($element->payment_params->macSecret)) {
			$mac = rbsglobalgateway_helper::calculateMAC(@$vars['orderKey'],
						@$vars['paymentAmount'], @$vars['paymentCurrency'], @$vars['paymentStatus'], $element->payment_params->macSecret);
			if ($mac != @$vars['mac']) {
				rbsglobalgateway_helper::raiseError($element->payment_params->showVars, 'Invalid MAC');
			}
		}
		if (empty($vars['paymentStatus'])) {
			$vars['paymentStatus'] = 'CANCELLED';
		}
		if($element->payment_params->debug) echo print_r($vars, true)."\n\n\n";
		$data = implode('&',$data).'&cmd=_notify-validate';
		$db = JFactory::getDBO();
		$query = 'SELECT order_id FROM '.hikashop_table('order').' WHERE order_number = '.$db->Quote($orderCode).' LIMIT 1';
		$db->setQuery($query);
		$order_id = $db->loadResult();
		$orderClass = hikashop_get('class.order');
		$dbOrder = $orderClass->get((int)$order_id);
		if(empty($dbOrder)){
			rbsglobalgateway_helper::raiseError($method->payment_params->showVars, 'Could not load order : ' . $orderCode);
			return false;
		}

		$order = new stdClass();
		$order->order_id = $dbOrder->order_id;
		$order->old_status->order_status = $dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',hikashop_encode($dbOrder),HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		if($element->payment_params->debug) echo print_r($dbOrder,true)."\n\n\n";
		$mailer = JFactory::getMailer();
		$config =& hikashop_config();
		$sender = array(
				$config->get('from_email'),
				$config->get('from_name')
									 );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));
		 $currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
		$currency=$currencies[$dbOrder->order_currency_id];
		$fracDigits = (int)$currency->currency_locale['int_frac_digits'];
		$paymentAmount = ((int)@$vars['paymentAmount']) / pow(10, $fracDigits);
		$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified=0;
		$order->history->history_amount=$paymentAmount.@$vars['paymentCurrency'];
		$order->history->history_payment_id = $element->payment_id;
		$order->history->history_payment_method =$element->payment_type;
		$order->history->history_data = ob_get_clean();
		$order->history->history_type = 'payment';
		if (@$vars['paymentStatus'] != 'CANCELLED') {
			 $price_check = round($dbOrder->order_full_price, $fracDigits );
			 if($price_check != $paymentAmount || $currency->currency_code != @$vars['paymentCurrency']){
				 $order->order_status = $element->payment_params->invalid_status;
				 $orderClass->save($order);
				 $mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Worldpay Global Gateway').JText::_('INVALID_AMOUNT'));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Worldpay Global Gateway',$order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				 return false;
			 }
		}
		switch ($vars['paymentStatus']) {
			case 'AUTHORISED':
				$payment_status = 'Authenticated';
				 $order_status = $element->payment_params->verified_status;
				 $message = $element->payment_params->verifiedMessage;
				 $url = $element->payment_params->verifiedURL;
				 $order->history->history_notified = 1;
				break;
			case 'PENDING':
				$payment_status = 'Pending';
				 $order_status = $element->payment_params->pending_status;
				 $message = $element->payment_params->pendingMessage;
				 $url = $element->payment_params->pendingURL;
				break;
			case 'REFUSED':
				$payment_status = 'Refused';
				 $order_status = $element->payment_params->invalid_status;
				 $message = $element->payment_params->invalidMessage;
				 $url = $element->payment_params->invalidURL;
				break;
			case 'CANCELLED':
				$payment_status = 'Cancelled';
				 $order_status = $element->payment_params->cancelled_status;
				 $message = $element->payment_params->cancelledMessage;
				 $url = $element->payment_params->cancelledURL;
				break;
			default:
				$payment_status = 'Unknown';
				 $order_status = $element->payment_params->invalid_status;
				 $message = $element->payment_params->invalidMessage;
				 $url = $element->payment_params->invalidURL;
				break;
		 }
		if (!empty($element->payment_params->responseRefField)) {
			$responseRefField = $element->payment_params->responseRefField;
			$response = rbsglobalgateway_helper::getOrderPaymentResponse($element->payment_params, $dbOrder->order_number);
			if (!empty($response)) $order->$responseRefField = $response;
		}
		if (!empty($order_status)) $order->order_status = $order_status;
		$order->mail_status=$statuses[$order->order_status];
		$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Worldpay Global Gateway',$payment_status,$dbOrder->order_number));
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Worldpay Global Gateway',$vars['paymentStatus'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
		$mailer->setBody($body);
		$mailer->Send();
		$orderClass->save($order);
		$dbg = null;
		if($element->payment_params->debug) {
			$dbg = ob_get_clean();
		}
		$app = JFactory::getApplication();
 		if (!empty($message)) {
			 $app->set( '_messageQueue', '' );
			 JError::raiseNotice(100, $message);
		}
 		if (!empty($url)) {
			if (empty($element->payment_params->showVars)) $app->redirect($url);
			else {
				echo '<a href="' . $url . '">Click here to continue...</a>';
				echo '<pre>';
				print_r($vars);
				echo '</pre>';
				if (!empty($dbg)) echo '<hr/><pre>' . $dbg . '</pre>';
				exit(0);
			}
		}
		if (!empty($dbg)) {
			ob_start();
			echo $dbg;
		}
		return true;
	}
	function onPaymentConfiguration(&$element){
		$this->bf_rbsglobalgateway = JRequest::getCmd('name','bf_rbsglobalgateway');
		if(empty($element)){
			$element = new stdClass();
			$element->payment_name='Worldpay Global Gateway';
			$element->payment_description='You can pay by debit or credit card using this payment method';
			$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
			$element->payment_type=$this->bf_rbsglobalgateway;
			$element->payment_params= new stdClass();
			$element->payment_params->htmllayout = 'default.php';
			$element->payment_params->orderContentCSS='' .
							'h1 { font-family: Arial, Helvetica, sans-serif; font-size: 18px; font-weight: bold; } ' .
							'.rbs-wrapper { margin:2px auto 10px auto; width: 976px;} ' .
							'.rbs-order { margin: 0 auto; padding-bottom: 10px; } ' .
							'.rbs-order-header-left { text-align: left; } ' .
							'.rbs-order-header-right { text-align: right; } ' .
							'.rbs-order-header th { background: none repeat scroll 0 0 #C3D9FF; padding: 4px; font-size: 13px; } ' .
							'.rbs-product_price, .rbs-product_quantity { text-align: right; } ' .
							'.rbs-product-item td { padding: 4px; font-size: 13px; } ' .
							'.rbs-column-1,.rbs-column-3 { vertical-align: top; } ' .
							'td.rbs-product_price { color:#990000; font-weight: bold; } ' .
							'td.rbs-order-total-price { color:#990000; text-align: right; font-weight: bold;} ' .
							'tr.rbs-order-total { font-weight: bold; text-align: right; }  ' .
							'tr.rbs-address-header, tr.rbs-email-address-header, tr.rbs-contact-info-header, tr.rbs-billing-notice-header { background: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 18px; } ' .
							'td.rbs-address-detail { padding-bottom: 10px; } ' .
							'td.rbs-site_logo { padding-bottom: 10px; } ' .
							'.rbs-cancel { text-align: center; } ' .
							'.rbs-cancel a:link { color: #4461A1; font-size: 1.2em; font-weight: bold; text-decoration: none } ' .
							'.rbs-cancel a:hover { color: #990000; text-decoration: underline } ' .
							'table#paymentmethods tr { display:inline; } ' .
							'#rbs-worldpay-header { text-align:left;width:100%; } ' .
							'#rbs-worldpay-about { background-color:#002469; } ' .
							'#rbs-worldpay-menu { margin-top:1px;padding:2px;background-color:#818FBE; } ' .
							'';
			$element->payment_params->bodyAttr='bgcolor="pink"';
			$element->payment_params->fontAttr='face="arial"+color="green"';
			$element->payment_params->description='This is a test order.';
			$element->payment_params->desc='You have ordered more than one product.';
			$element->payment_params->contactInformation='You can contact us on 000000000000.';
			$element->payment_params->paymentMethodMask='AMEX-SSL VISA-SSL ECMC-SSL DINERS-SSL';  // Or use ALL
			$element->payment_params->billingNotice="Your payment will be handled by WorldPay.\nThis name may appear on your bank statement\nhttp://www.worldpay.com";
			$element->payment_params->xmlurl='https://MERCHANTCODE:password@secure-test.worldpay.com/jsp/merchant/xml/paymentService.jsp';
			$element->payment_params->notification=1;
			$element->payment_params->paymentRefField = 'order_rbs_reference';
			$element->payment_params->houseNoField = 'address_house_number';
			$element->payment_params->houseNameField = 'address_house_name';
			$element->payment_params->invalid_status='cancelled';
			$element->payment_params->verified_status='confirmed';
			$element->payment_params->pending_status=null;
			$element->payment_params->cancelled_status='cancelled';
			$element->payment_params->image_button='style="background: url(\'https://secure-test.wp3.rbsworldpay.com/images/rbswp/brand.gif\') top left no-repeat;' .
																						 'width:139px;height:33px;border:solid 1px #7C98B7;cursor:pointer;margin:10px 100px;"';
			$element->payment_params->verifiedMessage='Thank you for your order, your payment has been successfully authorised.';
			$element->payment_params->pendingMessage='Thank you for your order, waiting for funds transfer.';
			$element->payment_params->invalidMessage='Sorry, we are unable to obtain payment authorisation for your order.';
			$element->payment_params->cancelledMessage='Your order has been cancelled.';
			$element->payment_params->verifiedURL='index.php';
			$element->payment_params->pendingURL='index.php';
			$element->payment_params->invalidURL='index.php';
			$element->payment_params->cancelledURL='index.php';
			$element->payment_params->redirect_button='style="background: url(\'https://secure-test.wp3.rbsworldpay.com/images/rbswp/brand.gif\') top left no-repeat;' .
																											 'width:139px;height:33px;border:solid 1px #7C98B7;cursor:pointer;margin:10px 100px;"';
			$element = array($element);
		}
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' =>'payment-bf_rbsglobalgateway-form')
		);

		hikashop_setTitle('Worldpay Global Gateway','plugin','plugins&plugin_type=payment&task=edit&name='.$this->bf_rbsglobalgateway);
		$app = JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->bf_rbsglobalgateway);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
}