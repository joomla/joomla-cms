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
class plgHikashoppaymentAtossips extends hikashopPaymentPlugin {

	var $multiple = true;
	var $name = 'atossips';
	var $uniq_merchant = true;

	var $reponse_codes = array (
		'00'=>"Transaction approved or processed successfully",
		'02'=>"Contact card issuer",
		'03'=>"Invalid acceptor",
		'04'=>"Retain the card",
		'05'=>"Do not honour",
		'07'=>"Retain the card, special circumstances",
		'08'=>"Approve after obtaining identification",
		'12'=>"Invalid transaction",
		'13'=>"Invalid amount",
		'14'=>"Invalid cardholder number",
		'15'=>"Card issuer unknown",
		'30'=>"Format error",
		'31'=>"Identifier of acquirer entity unknown",
		'33'=>"Card is past expiry date",
		'34'=>"Suspicion of fraud",
		'41'=>"Card lost",
		'43'=>"Card stolen",
		'51'=>"Insufficient funds or credit limit exceeded",
		'54'=>"Card is past expiry date",
		'56'=>"Card missing from file",
		'57'=>"Transaction not permitted for this cardholder",
		'58'=>"Transaction prohibited at terminal",
		'59'=>"Suspicion of fraud",
		'60'=>"The acceptor of the card must contact the Acquirer",
		'61'=>"Exceeds the withdrawal amount limit",
		'63'=>"Security rules not observed",
		'68'=>"Response not received or received too late",
		'90'=>"Momentary system crash",
		'91'=>"Card issuer inaccessible",
		'96'=>"System functioning incorrectly",
		'97'=>"Expiry of the global monitoring delay",
		'98'=>"Server unavailable network routing further request",
		'99'=>"Incident field initiator"
	);

	var $sync_currencies = array(
		'EUR'=>'978','USD'=>'840','GBP'=>'826','JPY'=>'392','CAD'=>'124','AUD'=>'036','CHF'=>'756',
		'MXN'=>'484','TRY'=>'949','NZD'=>'554','NOK'=>'578','BRL'=>'986','ARS'=>'032','KHR'=>'116',
		'TWD'=>'901','SEK'=>'752','DKK'=>'208','KRW'=>'410','SGD'=>'702','XAF'=>'952'
	);

	var $accepted_currencies = array(
		'EUR','USD','GBP','JPY','CAD','AUD','CHF',
		'MXN','TRY','NZD','NOK','BRL','ARS','KHR',
		'TWD','SEK','DKK','KRW','SGD','XAF'
	);

	var $pluginConfig = array(
		'merchantID' => array("MERCHANT_ID",'input'),
		'secretKey'=>array("Secret Key",'input'),
		'keyVersion'=>array('Key Version','input'),
		'notification'=>array("ALLOW_NOTIFICATIONS_FROM_X",'boolean','0'),
		'testmode'=>array('TEST_MODE', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if (empty ($this->payment_params->merchantID) )
		{
			$this->app->enqueueMessage('You have to configure a merchant ID for the Atos sips plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}

		if (empty($this->payment_params->keyVersion))
		{
			$this->app->enqueueMessage('You have to configure the Key Version for the Atos sips plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		if (empty($this->payment_params->secretKey))
		{
			$this->app->enqueueMessage('You have to configure the Secret Key for the Atos sips plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}

		$PostUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$userPostUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&tmpl=component&user_return=1&lang='.$this->locale . $this->url_itemid;

		if ($this->payment_params->testmode == true) {
			$url = 'https://payment-webinit.simu.sips-atos.com/paymentInit';
		}
		else {
			$url = 'https://payment-webinit.sips-atos.com/paymentInit';
		}

		$vars0 = array(
			"currencyCode" => @$this->sync_currencies[$this->currency->currency_code],
			"merchantId" => trim($this->payment_params->merchantID),
			"normalReturnUrl" => $userPostUrl,
			"amount" => str_replace(array('.',','),'',round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100),
			"transactionReference" => $order->order_number,
			"keyVersion" => trim ($this->payment_params->keyVersion),
			"automaticResponseUrl" => $PostUrl,
			"orderId" => $order->order_id,
			"statementReference" => $order->order_number //add the order number in the merchant bank account:
		);

		$this->payment_params->url = $url;

		$data ='';
		foreach( $vars0 as $key => $val )
		{
			if(!empty($val))
			{
				$data .= $key .'='. ( trim( $val ) ) .'|';
			}
		}

		$data = substr( $data, 0, -1 );

		$secretKey = utf8_encode($this->payment_params->secretKey);

		$seal = hash('sha256', utf8_encode ($data.$secretKey) );

		$vars = array (
			"Data" => $data,
			"InterfaceVersion" => "HP_2.3",
			"Seal" => $seal,
		);

		if($this->payment_params->debug)
		{
			$this->writeToLog("Data sent to Atos Sips: \n\n\n");
			$this->writeToLog(print_r($vars,true));
		}

		$this->vars = $vars;
		return $this->showPage('end');

	}

	function onPaymentNotification(&$statuses){

		parse_str(strtr($_POST["Data"], '=|', '=&'), $arr);
		$response = array_map('trim', $arr);

		$order_id = (int)$response["orderId"];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);

		if($this->payment_params->debug) {

			$this->writeToLog("Data recieved from Atos Sips :\n\n ");
			$this->writeToLog($_POST["Data"]);
		}

		if(empty($this->payment_params) ) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Unable to read Atos Sips Response or Unknow Order! \n\n");
			}
			return false;
		}
		$this->loadOrderData($dbOrder);

		$user_return = !empty($_GET['user_return']);

		if($user_return && $dbOrder->order_status == $this->payment_params->verified_status) {
			$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id . $this->url_itemid;
			$this->app->redirect($EndUrl);
		}

		$secretKey = utf8_encode($this->payment_params->secretKey);

		$seal = hash('sha256', utf8_encode ($_POST["Data"].$secretKey) );

		if ($seal != $_POST["Seal"]) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Seals mismatch !!! Data has been modified! \n See the generated hash: \n\n");
				$this->writeToLog($seal,true);
				$this->writeToLog("compare with the recieved one :\n");
				$this->writeToLog($_POST["Seal"],true);
			}
			return('Invalid Seal!');
		}

		$amount = str_replace(array('.',','),'',round($dbOrder->order_full_price,2,2)*100);

		if ($amount != $response["amount"]) {

			if($this->payment_params->debug)
			{
				$this->writeToLog("Amount mismatch !!!\n Waited amount: " . $amount . ",\n");
				$this->writeToLog("Amount recieved: " . $response["amount"] . "\n");
			}

			return('Invalid Amount!');
		}


		$repCode = trim ($response["acquirerResponseCode"]);
		$notified = 0;

		switch( $repCode ) {

			case '00':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Atos Sips " . $details . "\n\r /Atos Sips authorisation Id :".$response["authorisationId"];

				$status = "Accepted";

				$message ="";

				$order_status = $this->payment_params->verified_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$dbOrder->order_id . $this->url_itemid;

				$notified = 1;

				break;

			case '08':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Atos Sips " . $details . " : Pending status.\n\r /Atos Sips authorisation Id :".$response["authorisationId"];

				$status = "Pending";

				$message ="";

				$order_status = $this->payment_params->pending_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$dbOrder->order_id . $this->url_itemid;

				break;

			case '02':
			case '03':
			case '04':
			case '05':
			case '07':
			case '12':
			case '13':
			case '14':
			case '15':
			case '30':
			case '31':
			case '33':
			case '34':
			case '41':
			case '43':
			case '51':
			case '54':
			case '56':
			case '57':
			case '58':
			case '59':
			case '60':
			case '61':
			case '63':
			case '68':
			case '90':
			case '91':
			case '96':
			case '97':
			case '98':
			case '99':

				$details =  @$this->reponse_codes[$repCode];
				$details = "Response from Atos Sips " . $details . " : Invalid status.\n\r /Atos Sips authorisation Id :".$response["authorisationId"];

				$status = "Declined";

				$message =JText::_("TRANSACTION_DECLINED");

				$order_status = $this->payment_params->invalid_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$dbOrder->order_id . $this->url_itemid;

				break;

			default:

				$details = "Unknow response from Atos Sips " . $repCode."\n\r /Atos Sips authorisation Id :".$response["authorisationId"];

				$status = "Declined";

				$message =JText::_("TRANSACTION_DECLINED");

				$order_status = $this->payment_params->invalid_status;

				$EndUrl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$dbOrder->order_id . $this->url_itemid;

				break;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Atos Sips',$status,$dbOrder->order_number);
		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Atos Sips',$status)).' '.JText::_('STATUS_NOT_CHANGED')
		."\r\n" . $details . "\r\n".$order_text;
		$email->body = $body;

		$history = new stdClass();
		$history->notified = $notified;
		$history->amount = $amount.$this->currency->currency_code;
		$history->data = $details;

		if (empty($user_return) ) {

			$this->modifyOrder($order_id, $order_status, $history, $email);
		}

		if($this->payment_params->debug) {

			$this->writeToLog("Transaction Result :\n ".$details."\n\n");
		}

		if($user_return) {
			$this->writeToLog();

			$this->app->enqueueMessage($message);
			$this->app->redirect($EndUrl);
		}
		return false;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='ATOS SIPS';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification= 1;
		$element->payment_params->testmode= 0;
		$element->payment_params->debug = 0;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
