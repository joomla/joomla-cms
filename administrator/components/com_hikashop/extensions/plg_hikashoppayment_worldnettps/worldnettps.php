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
class plgHikashoppaymentWorldnettps extends hikashopPaymentPlugin
{
	var $accepted_currencies = array('EUR','GBP','USD');
	var $multiple = true;
	var $name = 'worldnettps';
	var $doc_form = 'worldnettps';
	var $pluginConfig = array(
		'terminal_id' => array('TERMINAL_ID', 'input'),
		'secret_key' => array('SECRET_KEY', 'input'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','1'),
		'ask_owner' => array('CREDIT_CARD_OWNER', 'boolean','0'),
		'ask_cctype' => array('CARD_TYPE', 'big-textarea'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
	);

	function __construct(&$subject, $config)
	{
		return parent::__construct($subject, $config);
	}

	function needCC(&$method)
	{
		$method->ask_cc=true;
		$method->ask_ccv = @$method->payment_params->ask_ccv;
		$method->ask_owner = @$method->payment_params->ask_owner;
		$method->ask_cctype = @$method->payment_params->ask_cctype;
		if(!empty($method->ask_cctype)){
			$types = explode(',',$method->ask_cctype);
			$method->ask_cctype = array();
			foreach($types as $type){
				$method->ask_cctype[$type]=$type;
			}
		}
		return true;
	}


	function onBeforeOrderCreate(&$order,&$do)
	{
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init'))
		{
			$this->app->enqueueMessage('The Payment Express payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		if(empty($this->payment_params->terminal_id) || empty($this->payment_params->secret_key))
		{
			$this->app->enqueueMessage('Please check your &quot;WorldNet&quot; plugin configuration : the Terminal ID and the secret key need to be configured.');
			$do = false;
		}

	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id)
	{
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;

		$this->ccLoad();
		$cc_number=$this->cc_number;
		$cc_owner=$this->cc_owner;
		$cc_month=$this->cc_month;
		$cc_year=$this->cc_year;
		$cc_type=@$this->cc_type;
		$cc_ccv=@$this->cc_CCV;

		$amout = round($order->cart->full_total->prices[0]->price_value_with_tax,2);
		$date = date('d-m-Y:H:i:s').':000';
		$currency = $this->currency->currency_code;
		$hasharray = array(
			'TERMINALID' => $this->payment_params->terminal_id,
			'ORDERID' => $order->order_id,
			'AMOUNT' => $amout,
			'DATETIME' => $date
		);
		$hash = $this->hash_generator($this->payment_params->secret_key,$hasharray,$this->payment_params->debug,false);

		$xml = '<PAYMENT>
					<ORDERID>'.$order->order_id.'</ORDERID>
					<TERMINALID>'.$this->payment_params->terminal_id.'</TERMINALID>
					<AMOUNT>'.$amout.'</AMOUNT>
					<DATETIME>'.$date.'</DATETIME>
					<CARDNUMBER>'.$cc_number.'</CARDNUMBER>
					<CARDTYPE>'.$cc_type.'</CARDTYPE>
					<CARDEXPIRY>'.$cc_month.$cc_year.'</CARDEXPIRY>
					<CARDHOLDERNAME>'.$cc_owner.'</CARDHOLDERNAME>
					<HASH>'.$hash.'</HASH>
					<CURRENCY>'.$currency.'</CURRENCY>
					<TERMINALTYPE>2</TERMINALTYPE>
					<TRANSACTIONTYPE>7</TRANSACTIONTYPE>';

		if ($this->payment_params->ask_cctype)
			$xml .= '<CVV>'.$cc_ccv.'</CVV>';
		$xml .= '</PAYMENT>';

		if ($this->payment_params->sandbox)
			$curlUrl = 'https://testpayments.worldnettps.com/merchant/xmlpayment';
		else
			$curlUrl = 'https://payments.worldnettps.com/merchant/xmlpayment';

		$session = curl_init();
		curl_setopt($session, CURLOPT_URL, $curlUrl);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_HEADER, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_POSTFIELDS, $xml);

		$result = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		if ($result)
		{
			$split = explode('<?xml version="1.0" encoding="UTF-8"?>',$result);
			$params = $this->getTagValue($result);
			if (isset($params['ERRORSTRING']))
			{
				if ($this->payment_params->debug)
					echo 'Error : '.$params['ERRORSTRING'];
				return false;
			}
			else
			{
				$params['TERMINALID']=$this->payment_params->terminal_id;
				$params['AMOUNT']=$amout;
				$hash = $this->hash_generator($this->payment_params->secret_key,$params,$this->payment_params->debug,true);
				if (strcasecmp($hash,$params['HASH'])!=0)
				{
					if($this->payment_params->debug)
						echo 'Hash error '.$params['HASH'].' - '.$hash."\n\n\n";
					$this->modifyOrder($order->order_id, $this->payment_params->invalid_status, true, true);
					$this->app->enqueueMessage('The generated Hash is not valid. Please make sure that you entered a valid secredt key in the options of the WorldNet TPS payment method.','error');
					$this->app->redirect($cancel_url);
					return false;
				}
				elseif ($params['RESPONSECODE']!='A' || $params['RESPONSETEXT']!='APPROVAL')
				{
					if($this->payment_params->debug)
						echo 'payment '.$params['RESPONSETEXT']."\n\n\n";
					$this->modifyOrder($order->order_id, $this->payment_params->invalid_status, true, true);
					$this->app->redirect($cancel_url);
					return false;
				}
				else
				{
					$this->modifyOrder($order->order_id, $this->payment_params->verified_status, true, true);
					$this->app->redirect($return_url);
					return true;
				}
			}
		}
		else if ($this->payment_params->debug)
		{
			echo "<br/>---------------------- Curl Result SIGN -------------------------------------<br/>";
			echo "CURL RESULT :<br/>";
			echo 'Error : '.$error.' - '.$err_msg.'<br/>';
			echo "<br/>------------------ EO Curl Result sign -----------------------------------------";
			return false;
		}

	}

	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'WorldNetTPS';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card';

		$element->payment_params->debug=false;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->pending_status='created';
		$element->payment_params->ask_cctype='VISA,VISA DEBIT,ELECTRON,MASTERCARD,DEBIT MASTERCARD,MAESTRO,LASER,AMEX,DINERS,JCB,DISCOVER';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->verified_status = 'confirmed';
	}

	function hash_generator($secret, $parameters, $debug=false, $decode=false)
	{
		$clear_string = '';
		$expectedKey = array (
			'TERMINALID',
			'ORDERID',
			'AMOUNT',
			'DATETIME',
		);

		if ($decode)
		{
			$clear_string = $parameters['TERMINALID'].$parameters['UNIQUEREF'].$parameters['AMOUNT'].$parameters['DATETIME'].$parameters['RESPONSECODE'].$parameters['RESPONSETEXT'];
		}
		else
		{
			foreach ($parameters as $key => $value)
			{
				if (in_array($key,$expectedKey))
					$clear_string .= $value;
			}
		}
		$clear_string .= $secret;

		if (PHP_VERSION_ID < 50102) //Php >= 5.1.2 needed
		{
			$this->app->enqueueMessage('The WorldNetTPS payment plugin requires at least the PHP 5.1.2 version to work, but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		else
		{
			if($debug) echo 'Hash : '.$clear_string;
			return hash('md5', $clear_string);
		}
	}

	function getTagValue($string)
	{
		$arraypreg=array();
		$params=array();
		preg_match_all('#<([A-Z]+)>([^<>]*)</[A-Z]+>#iU',$string,$arraypreg);
		if ($arraypreg[1][0]=='ERRORSTRING')
			$params[$arraypreg[1][0]] = $arraypreg[2][0];
		else
		{
			for ($i=0 ; $i<count($arraypreg[0]) ; $i++)
				$params[$arraypreg[1][$i]] = $arraypreg[2][$i];
		}
		return $params;
	}

}
