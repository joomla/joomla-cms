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
class plgHikashoppaymentPaygate extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'paygate';
	var $doc_form = 'paygate';

	var $pluginConfig = array(
		'identifier' => array("Identifier",'input'),
		'key' => array('Key','input'),
		'payment_url' => array("Payment URL",'input'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);


	function __construct(&$subject, $config)
	{
		return parent::__construct($subject, $config);
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id) //On the checkout
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if (empty($this->payment_params->identifier))
		{
			$this->app->enqueueMessage(JText::sprintf('CONFIGURE_X_PAYMENT_PLUGIN_ERROR','an identifer','Paygate'),'error');
			return false;
		}
		elseif (empty($this->payment_params->key))
		{
			$this->app->enqueueMessage(JText::sprintf('CONFIGURE_X_PAYMENT_PLUGIN_ERROR','a key','Paygate'),'error');
			return false;
		}
		elseif (empty($this->payment_params->payment_url))
		{
			$this->app->enqueueMessage(JText::sprintf('CONFIGURE_X_PAYMENT_PLUGIN_ERROR','a payment url','Paygate'),'error');
			return false;
		}
		else
		{
			$date = date('Y-m-d h:i:s');
			$reference = $order->order_id.'-'.$order->order_number;
			$currency = $this->currency->currency_code;
			$amout =round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;
			$notif_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&amp;notif_payment='.$this->name.'&tmpl=component';

			if ($this->payment_params->sandbox)
			{
				$id = '10011013800';
				$key = 'secret';
			}
			else
			{
				$id = $this->payment_params->identifier;
				$key = $this->payment_params->key;
			}

			$vars = array(
				'PAYGATE_ID' => $id,
				'REFERENCE' => $reference,
				'AMOUNT' => $amout,
				'CURRENCY' => $currency,
				'RETURN_URL' => $notif_url,
				'TRANSACTION_DATE' => $date, //YYYY-MM-DD HH:MM:SS
			);

			$vars['CHECKSUM'] = $this->paygate_signature($key,$vars);

			if ($this->payment_params->debug)
				var_dump($vars);

			$this->vars = $vars;

			return $this->showPage('end');
		}
	}


	function onPaymentConfiguration(&$element)
	{
		parent::onPaymentConfiguration($element);
	}

	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		$element->payment_name='Paygate';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->payment_url = 'https://www.paygate.co.za/PayWebv2/process.trans';
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

		$explode = explode('-',$vars['REFERENCE']);
		$order_id = $explode[0];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order';

		$checksum = $this->paygate_signature($this->payment_params->key,$vars,false,true);
		if($this->payment_params->debug)
		{
			echo print_r($vars,true)."\n\n\n";
			echo print_r($this->plugin_params,true)."\n\n\n";
			echo print_r($checksum,true)."\n\n\n";
		}

		if (strcasecmp($checksum,$vars['CHECKSUM'])!=0)
		{
			if($this->payment_params->debug)
				$this->writeToLog('Hash error '.$vars['CHECKSUM'].' - '.$checksum."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR','Invalid hash'),'error');
			$this->app->redirect($cancel_url);
			return false;
		}
		elseif (strcmp($order_id.'-'.$dbOrder->order_number,$vars['REFERENCE'])!=0)
		{
			if($this->payment_params->debug)
				$this->writeToLog('Reference error '.$vars['REFERENCE'].' - '.$this->payment_params->reference."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR','Invalid reference'),'error');
			$this->app->redirect($cancel_url);
			return false;
		}
		elseif(substr($vars['RISK_INDICATOR'],0,1)!='A')
		{
			if($this->payment_params->debug)
				$this->writeToLog('Card validation error : '.$vars['RISK_INDICATOR']." - Authentication was attempted but NOT successful..\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR','Invalid card'),'error');
			$this->app->redirect($cancel_url);
			return false;
		}
		elseif($vars['TRANSACTION_STATUS']!='1' or $vars['RESULT_CODE']!='990017')
		{
			if($this->payment_params->debug)
				$this->writeToLog('The payment has been declined. Transaction status : '.$vars['TRANSACTION_STATUS'].' / '.$vars['RESULT_CODE'].' - '.$vars['RESULT_DESC']."\n\n\n");
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			$this->app->enqueueMessage(JText::sprintf('TRANSACTION_PROCESSING_ERROR','Invalid Paygate\' transaction status'),'error');
			$this->app->redirect($cancel_url);
			return false;
		}
		else
		{
			$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
			$this->app->redirect($return_url);
			return true;
		}
	}


	function paygate_signature($pwd, $parameters, $debug=false, $decode=false)
	{
		$clear_string = '';
		$expectedKey = array (
			'PAYGATE_ID',
			'REFERENCE',
			'TRANSACTION_STATUS',
			'RESULT_CODE',
			'AUTH_CODE',
			'AMOUNT',
			'RESULT_DESC',
			'TRANSACTION_ID',
			'RISK_INDICATOR'
		);

		foreach ($parameters as $key => $value)
		{
			if ($decode)
			{
				if (in_array($key,$expectedKey))
					$clear_string .= $value . '|';
			}
			else
				$clear_string .= $value . '|';
		}
		$clear_string .= $pwd;


		if (PHP_VERSION_ID < 50102) //Php >= 5.1.2 needed
		{
			$this->app->enqueueMessage('The Paygate payment plugin requires at least the PHP 5.1.2 version to work, but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		else
		{
			if ($debug)
				return $clear_string;
			else
				return hash('md5', $clear_string);
		}
	}

}
