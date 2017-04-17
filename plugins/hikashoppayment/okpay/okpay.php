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
class plgHikashoppaymentOkpay extends hikashopPaymentPlugin{

	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','SGD',
		'DKK','PLN','NOK','CZK','MXN','MYR','PHP','TWD','ILS',
		'RUB','CNY','NGN'
	);
	var $debugData = array();
	var $multiple = true;
	var $name = 'okpay';
	var $pluginConfig = array(
		'url' => array('URL', 'input'),
		'walletid' => array('OKPay Wallet ID', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$notify_url 	= HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=okpay&tmpl=component&&invoice='.$order->order_id.'lang='.$this->locale.$this->url_itemid;
		$return_url		= HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$cancel_url		= HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;

		$vars = array(
			"ok_receiver"			=> $this->payment_params->walletid,
			"ok_currency"			=> $this->currency->currency_code,
			"invoice"				=> $order->order_id,
			"ok_return_success"		=> $return_url,
			"ok_ipn"				=> $notify_url,
			"ok_return_fail"		=> $cancel_url
		);

		$i = 1;

		$config =& hikashop_config();
		$group = $config->get('group_options',0);
		foreach($order->cart->products as $product){
			if($group && $product->order_product_option_parent_id) continue;
			$item_price							= round($product->order_product_price,(int)$this->currency->currency_locale['int_frac_digits']) + round($product->order_product_tax,(int)$this->currency->currency_locale['int_frac_digits'])*$product->order_product_quantity;
			$vars["ok_item_".$i."_name"]		= substr(strip_tags($product->order_product_name),0,127);
			$vars["ok_item_".$i."_quantity"]	= $product->order_product_quantity;
			$vars["ok_item_".$i."_price"]		= $item_price;
			$i++;
		}

		$this->vars = $vars;

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$vars	= array();
		$data	= array();
		$filter	=& JFilterInput::getInstance();

		foreach($_REQUEST as $key => $val){
			$vars[$key] = $val;
		}

		$order_id = (int)@$vars['invoice'];
		$order_status = '';
		$dbOrder	= $this->getOrder($order_id);

		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['invoice'];
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		$url			= HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text		= "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text		.= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$email = new stdClass();
		$history = new stdClass();
		$response = $vars['ok_txn_status'];

		$verified = preg_match( "#completed#i", $response);

		$req = 'ok_verify=true';

		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		$header  = "POST /ipn-verify.html HTTP/1.0\r\n";
		$header .= "Host: www.okpay.com\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.okpay.com', 80, $errno, $errstr, 30);

		if (!$fp)
		{
		} else
		{
			fputs ($fp, $header . $req);
			while (!feof($fp))
			{
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0)
			{
				$vars['ok_response'] = $res;
			}
			else if (strcmp ($res, "INVALID") == 0)
			{
				$vars['ok_response'] = $res;
			}
			else if (strcmp ($res, "TEST")== 0)
			{
				$vars['ok_response'] = $res;
			}
			}
			fclose ($fp);
		}

		$vars['ok_verified'] = preg_match('/verified/i', @$vars['ok_response']);

		if(!$verified && !$vars['ok_verified']){
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','OKPay').'invalid transaction';
			$email->body = JText::sprintf("Hello,\r\n A okpay notification was refused because it could not be verified by the okpay server")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-okpay-error#invalidtnx').$order_text;

			$this->modifyOrder($order_id,null,false,$email);
			return false;
		} else {
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','OKPay').'invalid transaction';
			$email->body = JText::sprintf("Hello,\r\n A okpay notification was refused because it could not be verified by the okpay server")."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-okpay-error#invalidtnx').$order_text;

			$this->modifyOrder($order_id,null,false,$email);
		}

		$history->notified=0;
		$history->amount=@$vars['ok_txn_gross'].@$vars['ok_txn_currency'];
		$history->data = ob_get_clean();

		$price_check = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits'] );

		if($price_check != @$vars['ok_txn_gross'] || $this->currency->currency_code != @$vars['ok_txn_currency']){
			$order_status = $this->payment_params->invalid_status;

			$email->subject=JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','OKPay').JText::_('INVALID_AMOUNT');
			$email->body=str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','OKPay',$history->amount,$price_check.$this->currency->currency_code))."\r\n\r\n".JText::sprintf('CHECK_DOCUMENTATION',HIKASHOP_HELPURL.'payment-okpay-error#amount').$order_text;

			$this->modifyOrder($order_id,$order_status,$history,$email);
			return false;
		}

		$order_status = $this->payment_params->verified_status;
		$history->notified=1;

		if($dbOrder->order_status == $order_status)
			return true;
		$mail_status=$statuses[$order_status];
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','OKPay',$vars['ok_txn_status'],$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','OKPay',$vars['ok_txn_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='OKPay';
		$element->payment_description='You can pay by OKPay using this payment method';
		$element->payment_images='';

		$element->payment_params->url='https://www.okpay.com/process.html';
		$element->payment_params->notification=1;
		$element->payment_params->details=0;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->address_override=1;
	}
}
