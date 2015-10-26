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
jimport('joomla.log.log');
class plgHikashoppaymentHikabitcoin extends JPlugin
{
	var $debugData = array();

	function onPaymentConfiguration(&$element){
		$this->hikabitcoin = JRequest::getCmd('name','hikabitcoin');
		if(empty($element)){
			$element = new stdClass();
			$element->payment_name='hikabitcoin';
			$element->payment_description='Pay with bitcoin';
			$element->payment_images='bitcoin.png';
			$element->payment_type=$this->hikabitcoin;
			$element->payment_params= new stdClass();
			$element->payment_params->apiKey='';
			$element->payment_params->notificationEmail='';
			$element->payment_params->transactionSpeed='low';
			$element->payment_params->paid_status='created';
			$element->payment_params->confirmed_status='created';
			$element->payment_params->complete_status='confirmed';
			$element = array($element);
		}
		$obj = reset($element);
		if(empty($obj->payment_params->apiKey)){
			$app = JFactory::getApplication();
			$lang = JFactory::getLanguage();
			$locale=strtolower(substr($lang->get('tag'),0,2));
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','Bitcoin','Merchant apiKey','Merchant','https://bitpay.com/start'));
		}

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' =>'payment-hikabitcoin-form')
		);

		$app = JFactory::getApplication();
		if($app->isAdmin())
			hikashop_setTitle('Bitcoin','plugin','plugins&plugin_type=payment&task=edit&name='.$this->hikabitcoin);
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->hikabitcoin);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';
	}

	function onPaymentConfigurationSave(&$element){
		return true;
	}

	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if(!empty($methods)){
			foreach($methods as $method){
			if($method->payment_type!='hikabitcoin' || !$method->enabled){
				continue;
			}

			if(!empty($method->payment_zone_namekey)){
				$zoneClass=hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
				if(!in_array($method->payment_zone_namekey,$zones)){
					return true;
				}
			}

			$currencyClass = hikashop_get('class.currency');
			$null = null;
			if(!empty($order->total)){
				$currency_id = intval(@$order->total->prices[0]->price_currency_id);
				$currency = $currencyClass->getCurrencies($currency_id,$null);
			}

			$usable_methods[$method->ordering]=$method;
			}
		}
		return true;
	}

	function onPaymentSave(&$cart,&$rates,&$payment_id){
		$usable = array();
		$this->onPaymentDisplay($cart,$rates,$usable);
		$payment_id = (int) $payment_id;
		foreach($usable as $usable_method){
			if($usable_method->payment_id==$payment_id){
				return $usable_method;
			}
		}

		return false;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){

		require(dirname(__FILE__).DIRECTORY_SEPARATOR.'bitpay/bp_lib.php');
		$method 			=& $methods[$method_id];
		$tax_total 		 = '';
		$discount_total 	= '';
		$currencyClass 	 = hikashop_get('class.currency');
		$currencies		= null;
		$currencies 		= $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency		  = $currencies[$order->order_currency_id];
		if($currency->currency_locale['int_frac_digits']>2)$currency->currency_locale['int_frac_digits']=2;
		hikashop_loadUser(true,true); //reset user data in case the emails were changed in the email code
		$user 	= hikashop_loadUser(true);
		$lang 	= JFactory::getLanguage();
		$locale  =strtolower(substr($lang->get('tag'),0,2));

		if(!isset($method->payment_params->no_shipping)) $method->payment_params->no_shipping = 1;
		if(!empty($method->payment_params->rm)) $method->payment_params->rm = 2;
		$vars 	  = array();
		$sessionid = $order->order_id;
		$lang 	  = JFactory::getLanguage();
		$locale	= strtolower(substr($lang->get('tag'),0,2));
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&bitpay_callback=true&task=notify&notif_payment=hikabitcoin&tmpl=component&lang='.$locale.$url_itemid;
		$options['currency'] 		  = $currency->currency_code;
		$options['notificationURL']   = $notify_url;
		$options['notificationEmail'] = $method->payment_params->notificationEmail;
		$return_url 				   = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$url_itemid;
		$options['redirectURL'] 	   = $return_url."&sessionid=".$sessionid;
		$options['transactionSpeed']  = $method->payment_params->transactionSpeed;
		$options['apiKey'] 		    = $method->payment_params->apiKey;
		$options['posData'] 		   = $order->order_id;
		$options['fullNotifications'] = true;
		$options['orderID'] 		   = $order->order_id;


		if(!empty($method->payment_params->address_type)){
			$address_type = $method->payment_params->address_type.'_address';
			$app = JFactory::getApplication();
			$address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
			if(!empty($address)){
				if(!isset($method->payment_params->address_override)){
					$method->payment_params->address_override = '1';
				}
				$vars["address_override"]=$method->payment_params->address_override;
				$cart = hikashop_get('class.cart');
				$cart->loadAddress($order->cart,$address,'object',$method->payment_params->address_type);

				$vars["bill_first_name"]=@$order->cart->$address_type->address_firstname;
				$vars["bill_last_name"]=@$order->cart->$address_type->address_lastname;

				$address1 = '';
				$address2 = '';
				if(!empty($order->cart->$address_type->address_street2)){
					$address2 = substr($order->cart->$address_type->address_street2,0,99);
				}
				if(!empty($order->cart->$address_type->address_street)){
					if(strlen($order->cart->$address_type->address_street)>100){
						$address1 = substr($order->cart->$address_type->address_street,0,99);
						if(empty($address2)) $address2 = substr($order->cart->$address_type->address_street,99,199);
					}else{
						$address1 = $order->cart->$address_type->address_street;
					}
				}

				if (isset($vars["bill_first_name"])){
					$options['buyerName'] = $vars["bill_first_name"];
					if (isset($vars["bill_last_name"]))
						$options['buyerName'] .= ' '.$vars["bill_last_name"];
				}
				$options["buyerAddress1"]=$address1;
				$options["buyerAddress2"]=$address2;
				$options["buyerZip"]=@$order->cart->$address_type->address_post_code;
				$options["buyerCity"]=@$order->cart->$address_type->address_city;
				$options["buyerState"]=@$order->cart->$address_type->address_state->zone_code_3;
				$options["buyerCountry"]=@$order->cart->$address_type->address_country->zone_code_2;
				$options["buyerEmail"]=$user->user_email;
				$options["buyerPhone"]=@$order->cart->$address_type->address_telephone;
			}elseif(!empty($order->cart->billing_address->address_country->zone_code_2)){
				$options["buyerCountry"]=$order->cart->billing_address->address_country->zone_code_2;
			}
		}elseif(!empty($order->cart->billing_address->address_country->zone_code_2)){
			$options["buyerCountry"]=$order->cart->billing_address->address_country->zone_code_2;
		}



		if (count($order->cart->products) == 1){
			$item = $order->cart->products[0];
			$options['itemDesc'] = $item->order_product_name;
			if ( $item->order_product_quantity > 1 )
				$options['itemDesc'] = $item->order_product_quantity.'x '.$options['itemDesc'];
			if(strlen($options['itemDesc']) >= 100)
				$options['itemDesc'] = substr($options['itemDesc'], 0, 95). ' ...';
		}else{
			foreach($order->cart->products as $item)
				$quantity += $item->order_product_quantity;
			$options['itemDesc'] = $quantity.' items';
		}

		foreach(array("buyerName","buyerAddress1","buyerAddress2","buyerCity","buyerState","buyerZip","buyerCountry","buyerEmail","buyerPhone") as $k){
			if(isset($options[$k])){
				$options[$k] = substr($options[$k], 0, 100);
			}
		}

		$price   = sprintf('%.2f',$order->cart->full_total->prices[0]->price_value_with_tax,(int)$currency->currency_locale['int_frac_digits']);

		$invoice = bpCreateInvoice($sessionid, $price, $sessionid, $options);
		if (isset($invoice['error'])) {
			bpLog($invoice);
			if(isset($invoice['error']['message'])){
				$invoice['error'] = $invoice['error']['message'];
			}
			JFactory::getApplication()->enqueueMessage('Sorry your transaction did not go through successfully, please try again.<br/>Error:'.$invoice['error'], 'error');
			return false;
		}else{
			header("Location: ".$invoice['url']);
			exit();
		}
	}

	function onPaymentNotification(&$statuses){

		$pluginsClass = hikashop_get('class.plugins');
		$elements 	 = $pluginsClass->getMethods('payment','hikabitcoin');
		if(empty($elements)) return false;

		$element 		= reset($elements);
		$payment_params = $element->payment_params;

		$mailer 	= JFactory::getMailer();
		$config 	=& hikashop_config();
		$sender = array(
		$config->get('from_email'),
		$config->get('from_name') );
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));


		if($payment_params->test){
			echo "\n\n params".print_r($payment_params,true) ;
		}

		if(!$payment_params->notification){
			return false;
		}

		require(dirname(__FILE__).DIRECTORY_SEPARATOR.'bitpay/bp_lib.php');

		$response = bpVerifyNotification($payment_params->apiKey);
		if($payment_params->test){
			echo "\n\n response".print_r($response,true) ;
		}

		if (is_string($response) || !empty($response['error'])){
			bpLog($response);
			if(is_array($response)){
				$response = $response['error'];
			}
			$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bitcoin').'invalid response Server Response:'.$response);
			$body = JText::sprintf("Hello,\r\n A bitcoin notification was refused because the response from the bitcoin server was invalid");
			$mailer->setBody($body);
			$mailer->Send();
			return false;
		}else{

			$id = $orderid = $response['posData'];

			$orderClass 	   = hikashop_get('class.order');
			$dbOrder 		  = $orderClass->get((int)$id );
			$order 			= new stdClass();
			$order->order_id  = $dbOrder->order_id;
			$order->old_status->order_status = $dbOrder->order_status;
			$url 			  = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text 	   = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text   	  .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
			$isValid = true;
			if($id > 0) {
				if(empty($dbOrder)) {
					$isValid = false;
				}
			} else {
				$isValid = false;
			}
			if(!$isValid){
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bitcoin').'invalid transaction Server Response:'.$response['message']);
				$body = JText::sprintf("Hello,\r\n A bitcoin notification was refused because it could not be verified by the bitcoin server").$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				if($element->payment_params->test){
					echo 'invalid transaction'."\n\n\n";
				}
				return false;
			}

			echo 'Status: '.$response['status']."\n\n\n";
			echo 'Invoice id: '.$response['id']."\n\n\n";
			echo 'Url: '.$response['url']."\n\n\n";
			echo 'posData: '.$response['posData']."\n\n\n";
			echo 'price: '.$response['price']."\n\n\n";
			echo 'btcPrice: '.$response['btcPrice']."\n\n\n";

			$order->history->history_reason	 = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
			$order->history->history_notified   = 0;
			$order->history->history_amount	 = @$response['price'];
			$order->history->history_payment_id = $element->payment_id;
			$order->history->history_payment_method =$element->payment_type;
			$order->history->history_data 	   = ob_get_clean();
			$order->history->history_type 	   = 'payment';

			$currencyClass = hikashop_get('class.currency');
			$currencies	= null;
			$currencies 	= $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
			$currency	  = $currencies[$dbOrder->order_currency_id];
			$price_check = sprintf('%.2f',$dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits'] );
			if($price_check != @$response['price']){
				$order->order_status = $element->payment_params->invalid_status;
				$orderClass->save($order);
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bitcoin').JText::_('INVALID_AMOUNT'));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Bitcoin',$order->history->history_amount,$price_check.$currency->currency_code))."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				return false;
			}

			$send_mail = false;
			switch($response['status'])
			{
				case 'paid':
					$send_mail = true;
					$order->order_status = $element->payment_params->paid_status;
					$order_text .= "Payment has been received for order number".$dbOrder->order_number.", but the transaction has not been confirmed on the bitcoin network. " .
								 "You will receive another email when the transaction has been confirmed.";
					break;

				case 'confirmed':
					$send_mail = true;
					$order->order_status = $element->payment_params->confirmed_status;
					if (get_option('bitpay_transaction_speed') == 'high') {
						$order_text .= "Payment has been received, and the transaction has been confirmed on the bitcoin network for order number".$dbOrder->order_number.". " .
									 "You will receive another email when the transaction is complete.";
					} else {
						$order_text .=  "Transaction has now been confirmed on the bitcoin network order number".$dbOrder->order_number.". " .
									 "You will receive another email when the transaction is complete.";

					}
					break;

				case 'complete':
					$send_mail = true;
					$order->order_status = $element->payment_params->complete_status;
					$order->history->history_notified = 1;
					$order_text .= "Transaction is now complete! for order number".$dbOrder->order_number;
					break;
				case 'invalid':
					$send_mail = true;
					$order->order_status = $element->payment_params->invalid_status;
					$order_text .= "Invalid transaction for order number".$dbOrder->order_number ;
					break;
			}


			if($dbOrder->order_status == $order->order_status) return true;
			if($send_mail != true){
				return true;
			}
			$order->mail_status = $statuses[$order->order_status];
			$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Bitcoin',$response['status'],$dbOrder->order_number));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Bitcoin',$response['status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->mail_status)."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			$orderClass->save($order);
			return true;
		}

	}

}

