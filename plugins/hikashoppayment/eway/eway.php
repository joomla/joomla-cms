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
class plgHikashoppaymentEway extends hikashopPaymentPlugin
{
	var $sandboxData = array();
	var $multiple = true;
	var $name = 'eway';

	var $pluginConfig = array(
		'cust_id' => array('EWAY_CUSTOMER_ID', 'input'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		$method->ask_ccv = true;
		$method->ask_owner = true;
		return true;
	}

	function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The eWay payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$total = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits'])*100;

		if($this->payment_params->sandbox) {
			$this->app->enqueueMessage('NOTE : When you use the sandbox mode with a total amount with cents, your transaction will be declined!');
		}

		require_once( dirname(__FILE__) . DS . 'eway_lib.php' );

		if($this->payment_params->sandbox) {
			$eway = new EwayPaymentLib( '87654321', "https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp" );
		} else {
			$eway = new EwayPaymentLib( $this->payment_params->cust_id, 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp'  );
		}

		$eway->setCustomerInvoiceRef( uniqid( "order_" ) );
		$eway->setTrxnNumber( uniqid( "eway_" ) );

		$eway->setTotalAmount($total);

		$eway->setCustomerEmail( $this->user->user_email );

		if(!empty($order->cart->shipping_address)){
			$eway->setCustomerAddress( @$order->cart->shipping_address->address_street.', '.@$order->cart->shipping_address->address_city.', '.@$order->cart->shipping_address->address_state->zone_name_english );
			$eway->setCustomerPostcode( @$order->cart->shipping_address->address_post_code );
			$eway->setCustomerFirstname( @$order->cart->shipping_address->address_firstname );
			$eway->setCustomerLastname( @$order->cart->shipping_address->address_lastname );
		}elseif(!empty($order->cart->billing_address)){
			$eway->setCustomerAddress( @$order->cart->billing_address->address_street.', '.@$order->cart->billing_address->address_city.', '.@$order->cart->billing_address->address_state->zone_name_english );
			$eway->setCustomerPostcode( @$order->cart->billing_address->address_post_code );
			$eway->setCustomerFirstname( @$order->cart->billing_address->address_firstname );
			$eway->setCustomerLastname(@$order->cart->billing_address->address_lastname );
		}

		$order_item_name = array();
		foreach($order->cart->products as $product){
			$order_item_name[] = strip_tags($product->order_product_name);
		}
		$order_items = implode(' - ', $order_item_name);
		$eway->setCustomerInvoiceDescription( $order_items );

		$this->ccLoad();

		$eway->setCardHoldersName( $this->cc_owner );
		$eway->setCardNumber( $this->cc_number);
		$eway->setCardExpiryMonth( $this->cc_month );
		$eway->setCardExpiryYear( $this->cc_year );
		$eway->setCardCVN( $this->cc_CCV );

		switch($eway->doPayment()) {
			case EWAY_TRANSACTION_FAILED:
				$this->app->enqueueMessage('Your transaction was declined. Please reenter your credit card or another credit card information.');
				$error = $eway->getErrorMessage();
				if(!empty($error)){
					$this->app->enqueueMessage($error);
				}
				$this->ccClear();
				$do = false;
				break;
			case EWAY_TRANSACTION_UNKNOWN:
			default:
				$this->app->enqueueMessage('There was an error while processing your transaction: '.$eway->getErrorMessage());
				$this->ccClear();
				$do = false;
				break;
			case EWAY_TRANSACTION_OK:
				$history = new stdClass();
				$history->notified=0;
				$history->amount= round($order->cart->full_total->prices[0]->price_value_with_tax,2) . $this->currency->currency_code;
				$history->data = '';

				$email = new stdClass();
				$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','eWAY','Accepted',$order->order_number);
				$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
				$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$order->order_number,HIKASHOP_LIVE);
				$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','eWAY','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;
				$email->body = $body;

				$this->modifyOrder($order,$this->payment_params->verified_status,$history,$email);

				break;
		}
		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$this->removeCart = true;
		$method =& $methods[$method_id];
		$this->return_url = @$method->payment_params->return_url;
		return $this->showPage('thanks');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='eWAY';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->return_url=HIKASHOP_LIVE;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
