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
class plgHikashoppaymentAdyen extends hikashopPaymentPlugin
{
	var $accepted_currencies = array('EUR', 'USD');
	var $multiple = true;
	var $name = 'adyen';

	var $pluginConfig = array(
		'merchantaccount' => array('Merchant Account', 'input'),
		'hmacKey' => array('Hmac Key', 'input'),
		'skincode' => array('Skin Code', 'input'),
		'url' => array('Adyen Payment Platform URL', 'input'),
		'language_code' => array('shopper Locale', 'input'),
		'notification' => array('allow notification from adyen', 'boolean', '0'),
		'debug' => array('DEBUG', 'boolean', '0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'allowedmethods' => array('Allowed Methods',' input'),
		'blockedmethods' => array('Blocked Methods', 'input'),
		'sessionvalidity' => array('Session Validity (Days)', 'input')

	);

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if (empty($this->payment_params->sessionvalidity)) {
			$this->payment_params->sessionvalidity = 1;
		}

		if (empty($this->payment_params->merchantaccount))
		{
			$this->app->enqueueMessage('You have to configure a merchant account for the adyen plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}

		if (empty($this->payment_params->skincode))
		{
			$this->app->enqueueMessage('You have to configure the Skin Code for the adyen plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		elseif (empty($this->payment_params->hmacKey))
		{
			$this->app->enqueueMessage('You have to configure the Hmac Key for the adyen plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax, 2) * 100;

		list($shipping_house, $shipping_street) = $this->splitStreet($order->cart->shipping_address->address_street);
		list($billing_house, $billing_street) = $this->splitStreet($order->cart->billing_address->address_street);

		$resURL = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=adyen&tmpl=component' . $this->url_itemid;

		$vars = array(
			'merchantReference' => $order->order_id,
			'paymentAmount' => $amount,
			'currencyCode' => $this->currency->currency_code,
			'shipBeforeDate' => date('Y-m-d', strtotime('+3 days')),
			'skinCode' => trim($this->payment_params->skincode),
			'merchantAccount' => trim($this->payment_params->merchantaccount),
			'sessionValidity' => date('c',strtotime('+' . (int)$this->payment_params->sessionvalidity . ' days')),
			'shopperLocale' => trim($this->payment_params->language_code),
			'shopperEmail' => $this->user->user_email,
			'shopperReference' => $this->user->user_id,
			'resURL' => $resURL,
			'allowedMethods' => preg_replace('#[^a-z0-9_\-,]#i', '', $this->payment_params->allowedmethods),
			'blockedMethods' => preg_replace('#[^a-z0-9_\-,]#i', '', $this->payment_params->blockedmethods),
			'billingAddress.street' => $billing_street,
			'billingAddress.houseNumberOrName' => $billing_house,
			'billingAddress.city' => $order->cart->billing_address->address_city,
			'billingAddress.stateOrProvince' => $order->cart->billing_address->address_state->zone_name,
			'billingAddress.country' => $order->cart->billing_address->address_country->zone_code_2,
			'billingAddress.postalCode' => @$order->cart->billing_address->address_post_code,
			'deliveryAddress.street' => $shipping_street,
			'deliveryAddress.houseNumberOrName' => $shipping_house,
			'deliveryAddress.city' => $order->cart->shipping_address->address_city,
			'deliveryAddress.postalCode' => @$order->cart->shipping_address->address_post_code,
			'deliveryAddress.stateOrProvince' => @$order->cart->shipping_address->address_state->zone_name,
			'deliveryAddress.country' => $order->cart->shipping_address->address_country->zone_code_2,
			'shopper.firstName' => @$order->cart->billing_address->address_firstname,
			'shopper.lastName' => @$order->cart->billing_address->address_lastname,
			'shopper.telephoneNumber' => @$order->cart->billing_address->address_telephone,
		);

		$vars['merchantSig'] = base64_encode(pack('H*', hash_hmac('sha1',
			$vars['paymentAmount'] . $vars['currencyCode'] . $vars['shipBeforeDate'] . $vars['merchantReference'] . $vars['skinCode'] . $vars['merchantAccount'] . $vars['sessionValidity'] . $vars['shopperEmail'] . $vars['shopperReference'] . $vars['allowedMethods'] . $vars['blockedMethods'],
			trim($this->payment_params->hmacKey)
		)));

		$vars['billingAddressSig'] = base64_encode(pack('H*', hash_hmac('sha1',
			$vars['billingAddress.street'] . $vars['billingAddress.houseNumberOrName'] . $vars['billingAddress.city'] . $vars['billingAddress.postalCode'] . $vars['billingAddress.stateOrProvince'] . $vars['billingAddress.country'],
			trim($this->payment_params->hmacKey)
		)));

		$vars['deliveryAddressSig'] = base64_encode(pack('H*', hash_hmac('sha1',
			$vars['deliveryAddress.street'] . $vars['deliveryAddress.houseNumberOrName'] . $vars['deliveryAddress.city'] . $vars['deliveryAddress.postalCode'] . $vars['deliveryAddress.stateOrProvince'] . $vars['deliveryAddress.country'],
			trim($this->payment_params->hmacKey)
		)));

		$vars['shopperSig'] = base64_encode(pack('H*', hash_hmac('sha1',
			$vars['shopper.firstName'] . $vars['shopper.lastName'] . $vars['shopper.telephoneNumber'],
			trim($this->payment_params->hmacKey)
		)));

		$this->vars = $vars;
		return $this->showPage('end');
	}

	function splitStreet($street)
	{
		if (preg_match('#[0-9]+#',$street,$matches))
		{
			return array($matches[0], preg_replace('#'.$matches[0].'#', '', $street, 1));
		}
		return array('', $street);
	}

	function getPaymentDefaultValues(&$element)
	{
		$element->payment_name = 'adyen';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->notification = 1;
		$element->payment_params->url = 'https://test.adyen.com/hpp/pay.shtml';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
		$element->payment_params->pending_status = 'created';
	}

	function onPaymentNotification(&$statuses)
	{
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value)
		{
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[$key]=$value;
		}

		$order_id = (int)@$vars['merchantReference'];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;

		if (!$this->payment_params->notification)
		{
			$this->app->redirect($return_url);
			return false;
		}

		if (isset($vars['authResult']))
		{
			$hmacKey = base64_encode(pack('H*',hash_hmac('sha1',
				$vars['authResult'] . $vars['pspReference'] . $vars['merchantReference'] . $vars['skinCode'],
				trim($this->payment_params->hmacKey)
			)));

			if($vars['merchantSig'] !== $hmacKey)
			{
				if ($this->payment_params->debug)
					echo 'Hash error ' . $vars['merchantSig'] . ' - ' . $hmacKey . "\n\n\n";
				return false;
			}
		}
		else //POST Method
		{
			$vars['authResult'] = 'UNKNOWN';
			if($vars['eventCode'] === 'AUTHORISATION')
			{
				if($vars['success'] === 'true')
				{
					$vars['authResult'] = 'AUTHORISED';
				}
				else if(!empty($vars['reason']))
				{
					$vars['authResult'] = 'REFUSED';
				}
			}
		}

		if ($this->payment_params->debug)
		{
			echo print_r($vars, true)."\n\n\n";
			echo print_r($dbOrder, true)."\n\n\n";
			if (isset($hmacKey))
			{
				echo print_r($hmacKey, true)."\n\n\n";
			}
		}

		if ($vars['authResult'] != 'PENDING' && $vars['authResult'] != 'AUTHORISED')
		{
			if($this->payment_params->debug)
				echo 'payment ' . $vars['authResult'] . "\n\n\n";

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);

			$this->app->redirect($cancel_url);
			return false;
		}

		if ($vars['authResult'] == 'AUTHORISED')
		{
			$order_status = $this->payment_params->verified_status;
		}
		else if ($vars['authResult'] == 'PENDING')
		{
			$order_status = $this->payment_params->pending_status;
		}

		if ($dbOrder->order_status == $order_status)
		{
			$this->app->redirect($return_url);
			return true;
		}

		if (!empty($order_status))
		{
			$this->modifyOrder($order_id, $order_status, true, true);
		}

		$this->app->redirect($return_url);
		return true;
	}
}
