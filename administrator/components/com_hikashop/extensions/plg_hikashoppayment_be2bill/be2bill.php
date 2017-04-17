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
class plgHikashoppaymentBe2Bill extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( "EUR" );
	var $multiple = true;
	var $name = 'be2bill';
	var $doc_form = 'be2bill';

	var $pluginConfig = array(
		'identifier' => array("Identifier",'input'),
		'password' => array("HIKA_PASSWORD",'input'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'payment_url' => array("Payment URL",'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL_DEFINE','html',''),
		'return_url' => array('RETURN_URL_DEFINE', 'html',''),
		'notify_url' => array('NOTIFY_URL_DEFINE','html',''),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);


	function __construct(&$subject, $config)
	{
		$this->pluginConfig['notification'][0] =  JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X','Be2Bill');
		$this->pluginConfig['cancel_url'][2] = HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=order&task=cancel_order";
		$this->pluginConfig['return_url'][2] = HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=checkout&task=after_end";
		$this->pluginConfig['notify_url'][2] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&amp;notif_payment='.$this->name.'&tmpl=component';


		return parent::__construct($subject, $config);
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id) //On the checkout
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if (empty($this->payment_params->identifier))
		{
			$this->app->enqueueMessage('You have to configure an identifer for the Be2Bill plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		elseif (empty($this->payment_params->password))
		{
			$this->app->enqueueMessage('You have to configure a password for the Be2Bill plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		elseif (empty($this->payment_params->payment_url))
		{
			$this->app->enqueueMessage('You have to configure a payment url for the Be2Bill plugin payment first : check your plugin\'s parameters, on your website backend','error');
			return false;
		}
		else
		{
			$version = 2.0;
			$amout =round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;

			$vars = array(
				'IDENTIFIER' => $this->payment_params->identifier,
				'OPERATIONTYPE' => "payment",
				'CLIENTIDENT' => $order->order_user_id,
				'CLIENTEMAIL' => @$order->customer->email,
				'CARDFULLNAME' => @$order->customer->name,
				'DESCRIPTION' => "ordernumber ".$order->order_number,
				'ORDERID' => $order->order_id,
				'VERSION' => $version,
				'AMOUNT' => $amout //Amount in cents

			);

			$vars['HASH'] = $this->be2bill_signature($this->payment_params->password,$vars);
			$this->vars = $vars;

			return $this->showPage('end');
		}
	}


	function onPaymentConfiguration(&$element)
	{
		parent::onPaymentConfiguration($element);
		if(empty($element->payment_params->email))
		{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();

			$js = "window.hikashop.ready( function() {
					var element = document.getElementById('link_to_plateform');
					element.onclick = function() {
						document.getElementById('hikashop_be2bill_form').submit();
						return false;
					};
				});";
			$config = hikashop_config();
			$email = $config->get('from_email');
			$form = '<form id="hikashop_be2bill_form" name="hikashop_be2bill_form" action="https://setup.be2bill.com/ouverture-vad" method="post">
			<input type="hidden" name="partner-code" value="P-6a0831" />
			<input type="hidden" name="email" value="'.$email.'" />
			<input type="hidden" name="website" value="'.$_SERVER['HTTP_HOST'].'" />
			<input type="hidden" name="ecommerce-platform" value="Hikashop" />
			</form>';
			$doc->addScriptDeclaration($js);
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED', 'Be2bill', 'identifer', 'Be2bill', '').$form);
		}

	}

	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		$element->payment_name='Be2bill';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->address_type="billing";
		$element->payment_params->notification=1;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
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

		$order_id = (int)@$vars['ORDERID'];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);



		$hash = $this->be2bill_signature($this->payment_params->password,$vars,false,true);
		if($this->payment_params->debug)
		{
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
			echo print_r($hash,true)."\n\n\n";
		}

		if (strcasecmp($hash,$vars['HASH'])!=0)
		{
			if($this->payment_params->debug)
				echo 'Hash error '.$vars['HASH'].' - '.$hash."\n\n\n";
			return false;
		}
		elseif($vars['EXECCODE']!='0000')
		{
			if($this->payment_params->debug)
				echo 'payment '.$vars['MESSAGE']."\n\n\n";
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			return false;
		}
		else
		{
			$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
			return true;
		}
	}


	function be2bill_signature($password, $parameters, $debug=false, $decode=false)
	{
		ksort($parameters);
		$clear_string = $password;
		$expectedKey = array (
			'IDENTIFIER',
			'OPERATIONTYPE',
			'TRANSACTIONID',
			'CLIENTIDENT',
			'CLIENTEMAIL',
			'ORDERID',
			'VERSION',
			'LANGUAGE',
			'CURRENCY',
			'EXTRADATA',
			'CARDCODE',
			'CARDCOUNTRY',
			'EXECCODE',
			'MESSAGE',
			'DESCRIPTOR',
			'ALIAS',
			'3DSECURE',
			'AMOUNT',
		);

		foreach ($parameters as $key => $value)
		{
			if ($decode)
			{
				if (in_array($key,$expectedKey))
					$clear_string .= $key . '=' . $value . $password;
			}
			else
				$clear_string .= $key . '=' . $value . $password;
		}


		if (PHP_VERSION_ID < 50102) //Php >= 5.1.2 needed
		{
			$this->app->enqueueMessage('The Be2Bill payment plugin requires at least the PHP 5.1.2 version to work, but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		else
		{
			if ($debug)
				return $clear_string;
			else
				return hash('sha256', $clear_string);
		}
	}

}
