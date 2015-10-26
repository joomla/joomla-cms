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
class plgHikashoppaymentPxpay extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','HKD','SGD','BND','INR','THB','CAD','JPY','TOP','CHF','KWD','USD','EUR','MYR','VUV','FJD','NZD','WST','FRF','PGK','ZAR','GBP'
	);
	var $multiple = true;
	var $name = 'pxpay';
	var $pluginConfig = array(
		'userid' => array('PxPay User ID', 'input'),
		'key' => array('PxPay Key', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		global $Itemid;
		$tax_total = '';
		$discount_total = '';

		$pxPay = 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx';

		$data = '<GenerateRequest>
	<PxPayUserId>'.@$this->payment_params->userid.'</PxPayUserId>
	<PxPayKey>'.@$this->payment_params->key.'</PxPayKey>
	<MerchantReference>'.@$this->payment_params->merchant_reference.'</MerchantReference>
	<TxnType>Purchase</TxnType>
	<AmountInput>'.number_format($order->cart->full_total->prices[0]->price_value_with_tax, 2, '.', '').'</AmountInput>
	<CurrencyInput>'.$this->currency->currency_code.'</CurrencyInput>
	<TxnData1>'.$order->order_id.'</TxnData1>
	<TxnData2>'.$order->order_number.'</TxnData2>
	<TxnData3>'.@$Itemid.'</TxnData3>
	<EmailAddress>'.$this->user->user_email.'</EmailAddress>
	<UrlSuccess>'.HIKASHOP_LIVE.'pxpay_'.$method_id.'.php</UrlSuccess>
	<UrlFail>'.HIKASHOP_LIVE.'pxpay_'.$method_id.'.php</UrlFail>
</GenerateRequest>';

		if( @$this->payment_params->debug ) {
			echo 'Data Sent<pre>';
			echo var_export($data, true);
			echo '</pre>';
		}

		$ret = $this->sendXml($pxPay, $data);
		$this->url = '';
		if(preg_match('#<URI>(.*)</URI>#iU', $ret, $res) !== false) {
			$this->url = $res[1];
		} else {
			$this->app->enqueueMessage(JText::_('ERROR'));
		}

		if( @$this->payment_params->debug ) {
			echo 'Data received<pre>';
			echo var_export($ret, true);
			echo '</pre>';
		}

		$data = '';
		$ret = '';

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses) {
		$method_id = JRequest::getInt('notif_id', 0);
		$this->pluginParams($method_id);
		$this->payment_params =& $this->plugin_params;
		if(empty($this->payment_params))
			return false;

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order'.$this->url_itemid;

		$url = 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx';
		$result = @$_GET['result'];
		if(empty($result)) {
			echo 'empty result';
			return;
		}

		$data = '<ProcessResponse>
	<PxPayUserId>'.@$this->payment_params->userid.'</PxPayUserId>
	<PxPayKey>'.@$this->payment_params->key.'</PxPayKey>
	<Response>'.$result.'</Response>
</ProcessResponse>';

		if(isset($this->payment_params->debug) && $this->payment_params->debug) {
			echo $data."\r\n\r\n";
		}
		$ret = $this->sendXml($url, $data);

		$result = '';
		$order_id = 0;
		$txnId = '';
		$responseText = '';
		$amount = '';

		if(preg_match('#<Success>(.*)</Success>#iU', $ret, $res) !== false) { $result = $res[1]; }
		if(preg_match('#<TxnData1>(.*)</TxnData1>#iU', $ret, $res) !== false) { $order_id = (int)$res[1]; }
		if(preg_match('#<TxnId>(.*)</TxnId>#iU', $ret, $res) !== false) { $txnId = $res[1]; }
		if(preg_match('#<ResponseText>(.*)</ResponseText>#iU', $ret, $res) !== false) { $responseText = $res[1]; }
		if(preg_match('#<AmountSettlement>(.*)</AmountSettlement>#iU', $ret, $res) !== false) {	$amount = $res[1]; }
		if(preg_match('#<CurrencySettlement>(.*)</CurrencySettlement>#iU', $ret, $res) !== false) { $amount .= $res[1]; }
		if(preg_match('#<TxnData3>(.*)</TxnData3>#iU', $ret, $res) !== false) {
			if(!empty($res[1]) && (int)$res[1] > 0) {
				$this->url_itemid='&Itemid='.(int)$res[1];
			}
		}

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			$this->app->enqueueMessage('Could not load any order for your notification '.$order_id);
			$this->app->redirect($cancel_url);
			return false;
		}
		if($method_id != $dbOrder->order_payment_id)
			return false;
		$this->loadOrderData($dbOrder);

		$history = new stdClass();
		$email = new stdClass();

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;

		if($dbOrder->order_status == $this->payment_params->verified_status) {
			$this->app->redirect($return_url);
			return true;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id.$this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$completed = ($result == '1');
		$history->notified = 0;
		$history->amount = $amount;
		$history->data =  ob_get_clean();

		if( !$completed ) {
			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code '.$responseText;

			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','PxPay',$order_status)).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','PxPay',$vars['Status'],$dbOrder->order_number);

			$this->modifyOrder($order_id, $order_status, $history, $email);

			$this->app->enqueueMessage('Transaction Failed: '.$responseText);
			$this->app->redirect($cancel_url);
			return false;
		}

		$history->notified = 1;
		$order_status = $this->payment_params->verified_status;
		$payment_status = 'Accepted';

		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','PxPay', $payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED', $statuses[$order_status])."\r\n\r\n".$order_text;
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'PxPay', $payment_status, $dbOrder->order_number);

		$this->modifyOrder($order_id, $order_status, $history, $email);

		$this->app->redirect($return_url);
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'PXPAY';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}

	function onPaymentConfigurationSave(&$element) {
		parent::onPaymentConfigurationSave($element);

		if(empty($element->payment_id)) {
			$pluginClass = hikashop_get('class.payment');
			$status = $pluginClass->save($element);
			if(!$status)
				return true;
			$element->payment_id = $status;
		}

		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		$pxpay='<?php
	$_GET[\'option\']=\'com_hikashop\';
	$_GET[\'tmpl\']=\'component\';
	$_GET[\'ctrl\']=\'checkout\';
	$_GET[\'task\']=\'notify\';
	$_GET[\'notif_payment\']=\'pxpay\';
	$_GET[\'format\']=\'html\';
	$_GET[\'lang\']=\''.$locale.'\';
	$_GET[\'notif_id\']=\''.$element->payment_id.'\';
	$_REQUEST[\'option\']=\'com_hikashop\';
	$_REQUEST[\'tmpl\']=\'component\';
	$_REQUEST[\'ctrl\']=\'checkout\';
	$_REQUEST[\'task\']=\'notify\';
	$_REQUEST[\'notif_payment\']=\'pxpay\';
	$_REQUEST[\'format\']=\'html\';
	$_REQUEST[\'lang\']=\''.$locale.'\';
	$_REQUEST[\'notif_id\']=\''.$element->payment_id.'\';
	include(\'index.php\');
';
		JFile::write(JPATH_ROOT.DS.'pxpay_'.$element->payment_id.'.php', $pxpay);

		return true;
	}

	function sendXml($url, $data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}
