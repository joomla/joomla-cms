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
class plgHikashoppaymentHSBC extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'GBP',
		'USD',
		'EUR',
		'AUD',
		'CAD'
	);
	var $synced_currencies = array(
		'GBP' => '826',
		'USD' => '840',
		'EUR' => '978',
		'AUD' => '036',
		'CAD' => '124'
	);

	var $country_codes = array(
		'AF' => '004', 'AL' => '008', 'DZ' => '012', 'AS' => '016', 'AD' => '020', 'AO' => '024', 'AI' => '660', 'AQ' => '010', 'AG' => '028', 'AR' => '032',
		'AM' => '051', 'AW' => '533', 'AU' => '036', 'AT' => '040', 'AZ' => '031', 'BS' => '044', 'BH' => '048', 'BD' => '050', 'BB' => '052', 'BY' => '112',
		'BE' => '056', 'BZ' => '084', 'BJ' => '204', 'BM' => '060', 'BT' => '064', 'BO' => '068', 'BA' => '070', 'BW' => '072', 'BV' => '074', 'BR' => '076',
		'IO' => '086', 'BN' => '096', 'BG' => '100', 'BF' => '854', 'BI' => '108', 'KH' => '116', 'CM' => '120', 'CA' => '124', 'CV' => '132', 'KY' => '136',
		'CF' => '140', 'TD' => '148', 'CL' => '152', 'CN' => '156', 'CX' => '162', 'CC' => '166', 'CO' => '170', 'KM' => '174', 'CG' => '178', 'CK' => '184',
		'CR' => '188', 'CI' => '384', 'HR' => '191', 'CU' => '192', 'CY' => '196', 'CZ' => '203', 'DK' => '208', 'DJ' => '262', 'DM' => '212', 'DO' => '214',
		'TP' => '626', 'EC' => '218', 'EG' => '818', 'SV' => '222', 'GQ' => '226', 'ER' => '232', 'EE' => '233', 'ET' => '231', 'FK' => '238', 'FO' => '234',
		'FJ' => '242', 'FI' => '246', 'FR' => '250', 'GF' => '254', 'PF' => '258', 'TF' => '260', 'GA' => '266', 'GM' => '270', 'GE' => '268', 'DE' => '276',
		'GH' => '288', 'GI' => '292', 'GR' => '300', 'GL' => '304', 'GD' => '308', 'GP' => '312', 'GU' => '316', 'GT' => '320', 'GN' => '324', 'GW' => '624',
		'GY' => '328', 'HT' => '332', 'HM' => '334', 'HN' => '340', 'HK' => '344', 'HU' => '348', 'IS' => '352', 'IN' => '356', 'ID' => '360', 'IR' => '364',
		'IQ' => '368', 'IE' => '372', 'IL' => '376', 'IT' => '380', 'JM' => '388', 'JP' => '392', 'JO' => '400', 'KZ' => '398', 'KE' => '404', 'KI' => '296',
		'KP' => '408', 'KW' => '414', 'KG' => '417', 'LA' => '418', 'LV' => '428', 'LB' => '422', 'LS' => '426', 'LR' => '430', 'LY' => '434', 'LI' => '438',
		'LT' => '440', 'LU' => '442', 'MO' => '446', 'MK' => '807', 'MG' => '450', 'MW' => '454', 'MY' => '458', 'MV' => '462', 'ML' => '466', 'MT' => '470',
		'MH' => '584', 'MQ' => '474', 'MR' => '478', 'MU' => '480', 'YT' => '175', 'MX' => '484', 'MD' => '498', 'MC' => '492', 'MN' => '496', 'MS' => '500',
		'MA' => '504', 'MZ' => '508', 'MM' => '104', 'NA' => '516', 'NR' => '520', 'NP' => '524', 'AN' => '530', 'NL' => '528', 'NC' => '540', 'NZ' => '554',
		'NI' => '558', 'NE' => '562', 'NG' => '566', 'NU' => '570', 'NF' => '574', 'MP' => '580', 'NO' => '578', 'OM' => '512', 'PK' => '586', 'PW' => '585',
		'PA' => '591', 'PG' => '598', 'PY' => '600', 'PE' => '604', 'PH' => '608', 'PN' => '612', 'PL' => '616', 'PT' => '620', 'PR' => '630', 'QA' => '634',
		'RE' => '638', 'RO' => '642', 'RU' => '643', 'RW' => '646', 'WS' => '882', 'SM' => '674', 'ST' => '678', 'SA' => '682', 'SN' => '686', 'SC' => '690',
		'SL' => '694', 'SG' => '702', 'SK' => '703', 'SI' => '705', 'SB' => '090', 'SO' => '706', 'ZA' => '710', 'GS' => '239', 'ES' => '724', 'LK' => '144',
		'SH' => '654', 'KN' => '659', 'LC' => '662', 'PM' => '666', 'VC' => '670', 'SD' => '736', 'SR' => '740', 'SJ' => '744', 'SZ' => '748', 'SE' => '752',
		'CH' => '756', 'SY' => '760', 'TW' => '158', 'TJ' => '762', 'TZ' => '834', 'TH' => '764', 'TG' => '768', 'TK' => '772', 'TO' => '776', 'TT' => '780',
		'TN' => '788', 'TR' => '792', 'TM' => '795', 'TC' => '796', 'TV' => '798', 'VI' => '850', 'UG' => '800', 'UA' => '804', 'AE' => '784', 'GB' => '826',
		'UK' => '826', 'UM' => '581', 'US' => '840', 'UY' => '858', 'UZ' => '860', 'VU' => '548', 'VA' => '336', 'VE' => '862', 'VN' => '704', 'WF' => '876',
		'EH' => '732', 'YE' => '887', 'YU' => '891', 'ZM' => '894', 'ZW' => '716'
	);

	var $errorCpiResultText = array(
		00 => 'The CPI result code was invalid.',
		01 => 'The user cancelled the transaction.',
		02 => 'The processor declined the transaction for an unknown reason.',
		03 => 'The transaction was declined because of a problem with the card. For example, an invalid card number or expiration date was specified.',
		04 => 'The processor did not return a response.',
		05 => 'The amount specified in the transactino was either too high or too low for the processor.',
		06 => 'The specified currency is not supported by either the processor or the card.',
		07 => 'The order is invalid because the order ID is a duplicated.',
		08 => 'The transaction was rejected by FraudShield.',
		09 => 'The transaction was placed in Review state by FraudShield.',
		10 => 'The transaction failed becaause of invalid input data.',
		11 => 'The transaction failed because the CPI was configured incorrectly.',
		12 => 'The transaction failed because the Storefront was configured incorrectly.',
		13 => 'The connection timed out.',
		14 => 'The transaction failed because the cardholder\'s browser refused a cookie.',
		15 => 'The customer\'s browser does not support 128-bit encryption.',
		16 => 'The CPI cannont communicate with the Secure ePayment engine.'
	);

	var $multiple = true;
	var $name = 'hsbc';

	var $pluginConfig = array(
		'merchantid' => array('MERCHANT_NUMBER', 'input'),
		'cpihash' => array('CPI_STORE_KEY', 'input'),
		'instant_capture' => array('INSTANTCAPTURE', 'boolean','1'),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	var $iv = 'bTxrBWPp';


	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$notify_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=hsbc&tmpl=component&lang='.$this->locale;
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=hsbc&tmpl=component&hsbc_return=1&lang='.$this->locale;
		$debug = $this->payment_params->debug?'T':'P';
		$vars = array(
			"CpiDirectResultUrl" => $notify_url,
			"CpiReturnUrl" => $return_url,
			"Mode" => $debug,
			"OrderDesc" => $order->order_number,
			"OrderId" => $order->order_id,
			"TransactionType" => $this->payment_params->instant_capture?'Capture':'Auth',
			"PurchaseAmount" => round($order->cart->full_total->prices[0]->price_value_with_tax * 100),
			"PurchaseCurrency" => $this->synced_currencies[ $this->currency->currency_code ],
			"StorefrontId" => $this->payment_params->merchantid,
			"TimeStamp" => time() . '000',
			"UserId" => $this->user->user_id,
			"ShopperEmail" => substr($this->user->user_email, 0, 30)
		);

		$address = $this->app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$type = 'billing';

		if(!empty($address)){
			$address_type = $type.'_address';
			$cart = hikashop_get('class.cart');
			$cart->loadAddress($order->cart,$address,'object',$type);
			$vars["BillingFirstName"] = substr( @$order->cart->$address_type->address_firstname, 0, 20);
			$vars["BillingLastName"] = substr( @$order->cart->$address_type->address_lastname, 0, 20);
			$address1 = '';
			$address2 = '';
			if(!empty($order->cart->$address_type->address_street)){
				if(strlen($order->cart->$address_type->address_street)>30){
					$address1 = substr($order->cart->$address_type->address_street,0,30);
					$address2 = substr($order->cart->$address_type->address_street,30,30);
				}else{
					$address1 = $order->cart->$address_type->address_street;
				}
			}
			$vars["BillingAddress1"] = $address1;
			if( !empty($adress2) )
				$vars["BillingAddress2"] = $address2;
			$vars["BillingPostal"] = substr(@$order->cart->$address_type->address_post_code, 0, 20);
			$vars["BillingCity"] = substr(@$order->cart->$address_type->address_city, 0, 25);
			$country_code_2 = @$order->cart->$address_type->address_country->zone_code_2;
			$vars["BillingCountry"] =  @$this->country_codes[$country_code_2];
			$vars["BillingCounty"] = substr(@$order->cart->$address_type->address_state->zone_name, 0, 25);
		}

		$address = $this->app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
		$type = 'shipping';
		if(!empty($address)){
			$address_type = $type.'_address';
			$cart = hikashop_get('class.cart');
			$cart->loadAddress($order->cart,$address,'object',$type);
			$vars["ShippingFirstName"] = substr(@$order->cart->$address_type->address_firstname, 0, 20);
			$vars["ShippingLastName"] = substr(@$order->cart->$address_type->address_lastname, 0, 20);
			$address1 = '';
			$address2 = '';
			if(!empty($order->cart->$address_type->address_street)){
				if(strlen($order->cart->$address_type->address_street)>60){
					$address1 = substr($order->cart->$address_type->address_street,0,30);
					$address2 = substr($order->cart->$address_type->address_street,30,30);
				}else{
					$address1 = $order->cart->$address_type->address_street;
				}
			}
			$vars["ShippingAddress1"] = $address1;
			if( !empty($adress2) )
				$vars["ShippingAddress2"] = $address2;
			$vars["ShippingPostal"] = substr(@$order->cart->$address_type->address_post_code, 0, 20);
			$vars["ShippingCity"] = substr(@$order->cart->$address_type->address_city, 0, 25);
			$country_code_2 = @$order->cart->$address_type->address_country->zone_code_2;
			$vars["ShippingCountry"] = @$this->country_codes[$country_code_2];
			$vars["ShippingCounty"] = substr(@$order->cart->$address_type->address_state->zone_name, 0, 25);
		} else {
			$vars["ShippingFirstName"] = $vars["BillingFirstName"];
			$vars["ShippingLastName"] = $vars["BillingLastName"];
			$vars["ShippingAddress1"] = $vars["BillingAddress1"];
			if( isset($vars["BillingAddress2"] ) )
				$vars["ShippingAddress2"] = $vars["BillingAddress2"];
			$vars["ShippingPostal"] = $vars["BillingPostal"];
			$vars["ShippingCity"] = $vars["BillingCity"];
			$vars["ShippingCountry"] = $vars["BillingCountry"];
			$vars["ShippingCounty"] = $vars["BillingCounty"];
		}

		$vars["OrderHash"] = $this->generate($vars, $this->payment_params->cpihash);
		$this->vars = $vars;

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$finalReturn = isset($_GET['hsbc_return']);
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$error_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order'.$this->url_itemid;

		$vars = array(
			'CpiResultsCode' => @$_POST['CpiResultsCode'],
			'PurchaseDate' => @$_POST['PurchaseDate'],
			'MerchantData' => @$_POST['MerchantData'],
			'OrderId' => @$_POST['OrderId'],
			'PurchaseAmount' => @$_POST['PurchaseAmount'],
			'PurchaseCurrency' => @$_POST['PurchaseCurrency'],
			'ShopperEmail' => @$_POST['ShopperEmail'],
			'StorefrontId' => @$_POST['StorefrontId']
		);

		$order_id = (int)$vars['OrderId'];
		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".$vars['OrderId'];
			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC'),'Invalid order');
				$this->app->redirect($error_url);
			}
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($this->payment_params->debug){
			echo print_r($vars,true)."\r\n\r\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if( empty($_POST['OrderHash']) ) {
			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC'),'Invalid Hash');
				$this->app->redirect($error_url);
			}
			return false;
		}

		if( $_POST['StorefrontId'] != $this->payment_params->merchantid ) {
			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC'),'Invalid store id');
				$this->app->redirect($error_url);
			}
			return false;
		}

		if( $_POST['OrderHash'] != $this->generate($vars, $element->payment_params->cpihash) ) {
			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC'),'Invalid processed Hash');
				$this->app->redirect($error_url);
			}
			return false;
		}

		$return_url =  $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;

		$history = new stdClass();
		$email = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['PurchaseAmount'].$vars['PurchaseCurrency'];
		$history->data = ob_get_clean();

		$orderPrice = round($dbOrder->order_full_price * 100);
		$orderCurrency = $this->synced_currencies[ $this->currency->currency_code ];

		if( $orderPrice != $vars['PurchaseAmount'] || $orderCurrency != $vars['PurchaseCurrency'] ) {
			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC').JText::_('INVALID_AMOUNT'));
				$this->app->redirect($cancel_url);
			}

			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC').JText::_('INVALID_AMOUNT');
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','HSBC',$history->amount,@$orderPrice.$this->currency->currency_code));
			$email->body = $body;

			$this->modifyOrder($order_id,$this->payment_params->invalid_status,$history,$email);

			return false;
		}

		$completed = ($vars['CpiResultsCode'] == '0');

		if($completed) {
			if($finalReturn)
				$this->app->redirect($return_url);

			$order_status = $this->payment_params->verified_status;
			$history->notified = 1;
			$payment_status = 'confirmed';
			$email = true;
		} else {
			$order_status = $this->payment_params->invalid_status;
			$payment_status = 'cancelled';
			$i = (int)$vars['CpiResultsCode'];
			if( !isset($this->errorCpiResultText[$i]) ) $i = 0;
			$email = $i . ' - ' . $this->errorCpiResultText[$i];

			if($finalReturn) {
				$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','HSBC').' '.$this->errorCpiResultText[$i]);
				$this->app->redirect($cancel_url);
			}
		}

		$this->modifyOrder($order_id, $order_status, $history, $email);
		return true;
	}


	function getPaymentDefaultValues(&$element) {
		$element->payment_name='HSBC';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function crypt($d,$k) {
		if( function_exists('mcrypt_module_open') && defined('MCRYPT_DES' ) ) {
			$module = mcrypt_module_open (MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
			$ks = mcrypt_enc_get_key_size($module);
			$key = substr($k, 0, $ks);
			mcrypt_generic_init($module, $key, $this->iv);
			$ret = mcrypt_generic($module, $d);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			return trim($ret);
		}

		return $this->crypt_DES(substr($k,0,8), $d, 1, 1, $this->iv, 0);
	}

	function decrypt($d,$k) {
		if( function_exists('mcrypt_module_open') && defined('MCRYPT_DES' ) ) {
			$module = mcrypt_module_open (MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
			$size = mcrypt_enc_get_key_size($module);
			$key = substr($k, 0, $size);
			mcrypt_generic_init($module, $key, $this->iv);
			$ret = mdecrypt_generic($module, $d);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			while( strlen($ret) > 0 && $ret[strlen($ret)-1] == "\4" ) {
				$ret = substr($ret, 0, -1);
			}
			return trim($ret);
		}

		$r =& $this->crypt_DES(substr($k,0,8), $d, 0, 1, $this->iv, 0);
		while( strlen($r) > 0 && $r[strlen($r)-1] == "\4" ) {
			$r = substr($r, 0, -1);
		}
		return $r;
	}

	function generate($data, $key) {
		if( (!is_array($data) && empty($data)) || empty($key) )
			return null;

		if( is_array($data) ) {
			asort($data,SORT_STRING);
			$data = implode($data);
		}

		$a = $this->decrypt(base64_decode($key), 'wsx1WSCOU/1LIPzFBNbR9QtTF2XmOUfRs4hGBBARAgAG');
		return base64_encode( $this->sha($data.$a, $a) );
	}

	function sha($data,$key) {
		if( function_exists('mhash') ) {
			return mhash(MHASH_SHA1, $data, $key);
		}

		if( !function_exists('sha1') ) {
			die('SHA1 function is not present');
		}
		if (strlen($key)>64)
			$key = pack('H*', sha1($key));
		$key = str_pad($key,64,chr(0x00));
		$ipad = str_repeat(chr(0x36),64);
		$opad = str_repeat(chr(0x5c),64);
		return pack('H*', sha1( ($key ^ $opad) . pack('H*', sha1(($key ^ $ipad) . $data)) ));
	}

	function crypt_DES($key, $message, $encrypt, $mode, $iv, $padding) {
		$spfunction1 = array (0x1010400,0,0x10000,0x1010404,0x1010004,0x10404,0x4,0x10000,0x400,0x1010400,0x1010404,0x400,0x1000404,0x1010004,0x1000000,0x4,0x404,0x1000400,0x1000400,0x10400,0x10400,0x1010000,0x1010000,0x1000404,0x10004,0x1000004,0x1000004,0x10004,0,0x404,0x10404,0x1000000,0x10000,0x1010404,0x4,0x1010000,0x1010400,0x1000000,0x1000000,0x400,0x1010004,0x10000,0x10400,0x1000004,0x400,0x4,0x1000404,0x10404,0x1010404,0x10004,0x1010000,0x1000404,0x1000004,0x404,0x10404,0x1010400,0x404,0x1000400,0x1000400,0,0x10004,0x10400,0,0x1010004);
		$spfunction2 = array (-0x7fef7fe0,-0x7fff8000,0x8000,0x108020,0x100000,0x20,-0x7fefffe0,-0x7fff7fe0,-0x7fffffe0,-0x7fef7fe0,-0x7fef8000,-0x80000000,-0x7fff8000,0x100000,0x20,-0x7fefffe0,0x108000,0x100020,-0x7fff7fe0,0,-0x80000000,0x8000,0x108020,-0x7ff00000,0x100020,-0x7fffffe0,0,0x108000,0x8020,-0x7fef8000,-0x7ff00000,0x8020,0,0x108020,-0x7fefffe0,0x100000,-0x7fff7fe0,-0x7ff00000,-0x7fef8000,0x8000,-0x7ff00000,-0x7fff8000,0x20,-0x7fef7fe0,0x108020,0x20,0x8000,-0x80000000,0x8020,-0x7fef8000,0x100000,-0x7fffffe0,0x100020,-0x7fff7fe0,-0x7fffffe0,0x100020,0x108000,0,-0x7fff8000,0x8020,-0x80000000,-0x7fefffe0,-0x7fef7fe0,0x108000);
		$spfunction3 = array (0x208,0x8020200,0,0x8020008,0x8000200,0,0x20208,0x8000200,0x20008,0x8000008,0x8000008,0x20000,0x8020208,0x20008,0x8020000,0x208,0x8000000,0x8,0x8020200,0x200,0x20200,0x8020000,0x8020008,0x20208,0x8000208,0x20200,0x20000,0x8000208,0x8,0x8020208,0x200,0x8000000,0x8020200,0x8000000,0x20008,0x208,0x20000,0x8020200,0x8000200,0,0x200,0x20008,0x8020208,0x8000200,0x8000008,0x200,0,0x8020008,0x8000208,0x20000,0x8000000,0x8020208,0x8,0x20208,0x20200,0x8000008,0x8020000,0x8000208,0x208,0x8020000,0x20208,0x8,0x8020008,0x20200);
		$spfunction4 = array (0x802001,0x2081,0x2081,0x80,0x802080,0x800081,0x800001,0x2001,0,0x802000,0x802000,0x802081,0x81,0,0x800080,0x800001,0x1,0x2000,0x800000,0x802001,0x80,0x800000,0x2001,0x2080,0x800081,0x1,0x2080,0x800080,0x2000,0x802080,0x802081,0x81,0x800080,0x800001,0x802000,0x802081,0x81,0,0,0x802000,0x2080,0x800080,0x800081,0x1,0x802001,0x2081,0x2081,0x80,0x802081,0x81,0x1,0x2000,0x800001,0x2001,0x802080,0x800081,0x2001,0x2080,0x800000,0x802001,0x80,0x800000,0x2000,0x802080);
		$spfunction5 = array (0x100,0x2080100,0x2080000,0x42000100,0x80000,0x100,0x40000000,0x2080000,0x40080100,0x80000,0x2000100,0x40080100,0x42000100,0x42080000,0x80100,0x40000000,0x2000000,0x40080000,0x40080000,0,0x40000100,0x42080100,0x42080100,0x2000100,0x42080000,0x40000100,0,0x42000000,0x2080100,0x2000000,0x42000000,0x80100,0x80000,0x42000100,0x100,0x2000000,0x40000000,0x2080000,0x42000100,0x40080100,0x2000100,0x40000000,0x42080000,0x2080100,0x40080100,0x100,0x2000000,0x42080000,0x42080100,0x80100,0x42000000,0x42080100,0x2080000,0,0x40080000,0x42000000,0x80100,0x2000100,0x40000100,0x80000,0,0x40080000,0x2080100,0x40000100);
		$spfunction6 = array (0x20000010,0x20400000,0x4000,0x20404010,0x20400000,0x10,0x20404010,0x400000,0x20004000,0x404010,0x400000,0x20000010,0x400010,0x20004000,0x20000000,0x4010,0,0x400010,0x20004010,0x4000,0x404000,0x20004010,0x10,0x20400010,0x20400010,0,0x404010,0x20404000,0x4010,0x404000,0x20404000,0x20000000,0x20004000,0x10,0x20400010,0x404000,0x20404010,0x400000,0x4010,0x20000010,0x400000,0x20004000,0x20000000,0x4010,0x20000010,0x20404010,0x404000,0x20400000,0x404010,0x20404000,0,0x20400010,0x10,0x4000,0x20400000,0x404010,0x4000,0x400010,0x20004010,0,0x20404000,0x20000000,0x400010,0x20004010);
		$spfunction7 = array (0x200000,0x4200002,0x4000802,0,0x800,0x4000802,0x200802,0x4200800,0x4200802,0x200000,0,0x4000002,0x2,0x4000000,0x4200002,0x802,0x4000800,0x200802,0x200002,0x4000800,0x4000002,0x4200000,0x4200800,0x200002,0x4200000,0x800,0x802,0x4200802,0x200800,0x2,0x4000000,0x200800,0x4000000,0x200800,0x200000,0x4000802,0x4000802,0x4200002,0x4200002,0x2,0x200002,0x4000000,0x4000800,0x200000,0x4200800,0x802,0x200802,0x4200800,0x802,0x4000002,0x4200802,0x4200000,0x200800,0,0x2,0x4200802,0,0x200802,0x4200000,0x800,0x4000002,0x4000800,0x800,0x200002);
		$spfunction8 = array (0x10001040,0x1000,0x40000,0x10041040,0x10000000,0x10001040,0x40,0x10000000,0x40040,0x10040000,0x10041040,0x41000,0x10041000,0x41040,0x1000,0x40,0x10040000,0x10000040,0x10001000,0x1040,0x41000,0x40040,0x10040040,0x10041000,0x1040,0,0,0x10040040,0x10000040,0x10001000,0x41040,0x40000,0x41040,0x40000,0x10041000,0x1000,0x40,0x10040040,0x1000,0x41040,0x10001000,0x40,0x10000040,0x10040000,0x10040040,0x10000000,0x40000,0x10001040,0,0x10041040,0x40040,0x10000040,0x10040000,0x10001000,0x10001040,0,0x10041040,0x41000,0x41000,0x1040,0x1040,0x40040,0x10000000,0x10041000);
		$masks = array (4294967295,2147483647,1073741823,536870911,268435455,134217727,67108863,33554431,16777215,8388607,4194303,2097151,1048575,524287,262143,131071,65535,32767,16383,8191,4095,2047,1023,511,255,127,63,31,15,7,3,1,0);

		$keys = $this->des_createKeysx ($key);
		$m=0;
		$len = strlen($message);
		$chunk = 0;
		$iterations = ((count($keys) == 32) ? 3 : 9); //single or triple des
		if ($iterations == 3) {$looping = (($encrypt) ? array (0, 32, 2) : array (30, -2, -2));}
		else {$looping = (($encrypt) ? array (0, 32, 2, 62, 30, -2, 64, 96, 2) : array (94, 62, -2, 32, 64, 2, 30, -2, -2));}

		if ($padding == 2) $message .= "        "; //pad the message with spaces
		else if ($padding == 1) {$temp = chr (8-($len%8)); $message .= $temp . $temp . $temp . $temp . $temp . $temp . $temp . $temp; if ($temp==8) $len+=8;} //PKCS7 padding
		else if (!$padding) $message .= (chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0) . chr(0)); //pad the message out with null bytes

		$result = "";
		$tempresult = "";

		if ($mode == 1) { //CBC mode
			$cbcleft = (ord($iv{$m++}) << 24) | (ord($iv{$m++}) << 16) | (ord($iv{$m++}) << 8) | ord($iv{$m++});
			$cbcright = (ord($iv{$m++}) << 24) | (ord($iv{$m++}) << 16) | (ord($iv{$m++}) << 8) | ord($iv{$m++});
			$m=0;
		}

		while ($m < $len) {
			$left = (ord($message{$m++}) << 24) | (ord($message{$m++}) << 16) | (ord($message{$m++}) << 8) | ord($message{$m++});
			$right = (ord($message{$m++}) << 24) | (ord($message{$m++}) << 16) | (ord($message{$m++}) << 8) | ord($message{$m++});

			if ($mode == 1) {if ($encrypt) {$left ^= $cbcleft; $right ^= $cbcright;} else {$cbcleft2 = $cbcleft; $cbcright2 = $cbcright; $cbcleft = $left; $cbcright = $right;}}

			$temp = (($left >> 4 & $masks[4]) ^ $right) & 0x0f0f0f0f; $right ^= $temp; $left ^= ($temp << 4);
			$temp = (($left >> 16 & $masks[16]) ^ $right) & 0x0000ffff; $right ^= $temp; $left ^= ($temp << 16);
			$temp = (($right >> 2 & $masks[2]) ^ $left) & 0x33333333; $left ^= $temp; $right ^= ($temp << 2);
			$temp = (($right >> 8 & $masks[8]) ^ $left) & 0x00ff00ff; $left ^= $temp; $right ^= ($temp << 8);
			$temp = (($left >> 1 & $masks[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);

			$left = (($left << 1) | ($left >> 31 & $masks[31]));
			$right = (($right << 1) | ($right >> 31 & $masks[31]));

			for ($j=0; $j<$iterations; $j+=3) {
				$endloop = $looping[$j+1];
				$loopinc = $looping[$j+2];
				for ($i=$looping[$j]; $i!=$endloop; $i+=$loopinc) { //for efficiency
				$right1 = $right ^ $keys[$i];
				$right2 = (($right >> 4 & $masks[4]) | ($right << 28 & 0xffffffff)) ^ $keys[$i+1];
				$temp = $left;
				$left = $right;
				$right = $temp ^ ($spfunction2[($right1 >> 24 & $masks[24]) & 0x3f] | $spfunction4[($right1 >> 16 & $masks[16]) & 0x3f]
						| $spfunction6[($right1 >>  8 & $masks[8]) & 0x3f] | $spfunction8[$right1 & 0x3f]
						| $spfunction1[($right2 >> 24 & $masks[24]) & 0x3f] | $spfunction3[($right2 >> 16 & $masks[16]) & 0x3f]
						| $spfunction5[($right2 >>  8 & $masks[8]) & 0x3f] | $spfunction7[$right2 & 0x3f]);
				}
				$temp = $left; $left = $right; $right = $temp; //unreverse left and right
			} //for either 1 or 3 iterations

			$left = (($left >> 1 & $masks[1]) | ($left << 31));
			$right = (($right >> 1 & $masks[1]) | ($right << 31));

			$temp = (($left >> 1 & $masks[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);
			$temp = (($right >> 8 & $masks[8]) ^ $left) & 0x00ff00ff; $left ^= $temp; $right ^= ($temp << 8);
			$temp = (($right >> 2 & $masks[2]) ^ $left) & 0x33333333; $left ^= $temp; $right ^= ($temp << 2);
			$temp = (($left >> 16 & $masks[16]) ^ $right) & 0x0000ffff; $right ^= $temp; $left ^= ($temp << 16);
			$temp = (($left >> 4 & $masks[4]) ^ $right) & 0x0f0f0f0f; $right ^= $temp; $left ^= ($temp << 4);

			if ($mode == 1) {if ($encrypt) {$cbcleft = $left; $cbcright = $right;} else {$left ^= $cbcleft2; $right ^= $cbcright2;}}
			$tempresult .= (chr($left>>24 & $masks[24]) . chr(($left>>16 & $masks[16]) & 0xff) . chr(($left>>8 & $masks[8]) & 0xff) . chr($left & 0xff) . chr($right>>24 & $masks[24]) . chr(($right>>16 & $masks[16]) & 0xff) . chr(($right>>8 & $masks[8]) & 0xff) . chr($right & 0xff));

			$chunk += 8;
			if ($chunk == 512) {$result .= $tempresult; $tempresult = ""; $chunk = 0;}
		}

		return ($result . $tempresult);
	}

	function des_createKeysx ($key) {
		$pc2bytes0  = array (0,0x4,0x20000000,0x20000004,0x10000,0x10004,0x20010000,0x20010004,0x200,0x204,0x20000200,0x20000204,0x10200,0x10204,0x20010200,0x20010204);
		$pc2bytes1  = array (0,0x1,0x100000,0x100001,0x4000000,0x4000001,0x4100000,0x4100001,0x100,0x101,0x100100,0x100101,0x4000100,0x4000101,0x4100100,0x4100101);
		$pc2bytes2  = array (0,0x8,0x800,0x808,0x1000000,0x1000008,0x1000800,0x1000808,0,0x8,0x800,0x808,0x1000000,0x1000008,0x1000800,0x1000808);
		$pc2bytes3  = array (0,0x200000,0x8000000,0x8200000,0x2000,0x202000,0x8002000,0x8202000,0x20000,0x220000,0x8020000,0x8220000,0x22000,0x222000,0x8022000,0x8222000);
		$pc2bytes4  = array (0,0x40000,0x10,0x40010,0,0x40000,0x10,0x40010,0x1000,0x41000,0x1010,0x41010,0x1000,0x41000,0x1010,0x41010);
		$pc2bytes5  = array (0,0x400,0x20,0x420,0,0x400,0x20,0x420,0x2000000,0x2000400,0x2000020,0x2000420,0x2000000,0x2000400,0x2000020,0x2000420);
		$pc2bytes6  = array (0,0x10000000,0x80000,0x10080000,0x2,0x10000002,0x80002,0x10080002,0,0x10000000,0x80000,0x10080000,0x2,0x10000002,0x80002,0x10080002);
		$pc2bytes7  = array (0,0x10000,0x800,0x10800,0x20000000,0x20010000,0x20000800,0x20010800,0x20000,0x30000,0x20800,0x30800,0x20020000,0x20030000,0x20020800,0x20030800);
		$pc2bytes8  = array (0,0x40000,0,0x40000,0x2,0x40002,0x2,0x40002,0x2000000,0x2040000,0x2000000,0x2040000,0x2000002,0x2040002,0x2000002,0x2040002);
		$pc2bytes9  = array (0,0x10000000,0x8,0x10000008,0,0x10000000,0x8,0x10000008,0x400,0x10000400,0x408,0x10000408,0x400,0x10000400,0x408,0x10000408);
		$pc2bytes10 = array (0,0x20,0,0x20,0x100000,0x100020,0x100000,0x100020,0x2000,0x2020,0x2000,0x2020,0x102000,0x102020,0x102000,0x102020);
		$pc2bytes11 = array (0,0x1000000,0x200,0x1000200,0x200000,0x1200000,0x200200,0x1200200,0x4000000,0x5000000,0x4000200,0x5000200,0x4200000,0x5200000,0x4200200,0x5200200);
		$pc2bytes12 = array (0,0x1000,0x8000000,0x8001000,0x80000,0x81000,0x8080000,0x8081000,0x10,0x1010,0x8000010,0x8001010,0x80010,0x81010,0x8080010,0x8081010);
		$pc2bytes13 = array (0,0x4,0x100,0x104,0,0x4,0x100,0x104,0x1,0x5,0x101,0x105,0x1,0x5,0x101,0x105);
		$masks = array (4294967295,2147483647,1073741823,536870911,268435455,134217727,67108863,33554431,16777215,8388607,4194303,2097151,1048575,524287,262143,131071,65535,32767,16383,8191,4095,2047,1023,511,255,127,63,31,15,7,3,1,0);

		$iterations = ((strlen($key) > 8) ? 3 : 1); //changed by Paul 16/6/2007 to use Triple DES for 9+ byte keys
		$keys = array (); // size = 32 * iterations but you don't specify this in php
		$shifts = array (0, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0);
		$m=0;
		$n=0;

		for ($j=0; $j<$iterations; $j++) { //either 1 or 3 iterations
			$left = (ord($key{$m++}) << 24) | (ord($key{$m++}) << 16) | (ord($key{$m++}) << 8) | ord($key{$m++});
			$right = (ord($key{$m++}) << 24) | (ord($key{$m++}) << 16) | (ord($key{$m++}) << 8) | ord($key{$m++});

			$temp = (($left >> 4 & $masks[4]) ^ $right) & 0x0f0f0f0f; $right ^= $temp; $left ^= ($temp << 4);
			$temp = (($right >> 16 & $masks[16]) ^ $left) & 0x0000ffff; $left ^= $temp; $right ^= ($temp << 16);
			$temp = (($left >> 2 & $masks[2]) ^ $right) & 0x33333333; $right ^= $temp; $left ^= ($temp << 2);
			$temp = (($right >> 16 & $masks[16]) ^ $left) & 0x0000ffff; $left ^= $temp; $right ^= ($temp << 16);
			$temp = (($left >> 1 & $masks[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);
			$temp = (($right >> 8 & $masks[8]) ^ $left) & 0x00ff00ff; $left ^= $temp; $right ^= ($temp << 8);
			$temp = (($left >> 1 & $masks[1]) ^ $right) & 0x55555555; $right ^= $temp; $left ^= ($temp << 1);

			$temp = ($left << 8) | (($right >> 20 & $masks[20]) & 0x000000f0);
			$left = ($right << 24) | (($right << 8) & 0xff0000) | (($right >> 8 & $masks[8]) & 0xff00) | (($right >> 24 & $masks[24]) & 0xf0);
			$right = $temp;

			for ($i=0; $i < count($shifts); $i++) {
				if ($shifts[$i] > 0) {
					$left = (($left << 2) | ($left >> 26 & $masks[26]));
					$right = (($right << 2) | ($right >> 26 & $masks[26]));
				} else {
					$left = (($left << 1) | ($left >> 27 & $masks[27]));
					$right = (($right << 1) | ($right >> 27 & $masks[27]));
				}
				$left = $left & -0xf;
				$right = $right & -0xf;

				$lefttemp = $pc2bytes0[$left >> 28 & $masks[28]] | $pc2bytes1[($left >> 24 & $masks[24]) & 0xf]
					| $pc2bytes2[($left >> 20 & $masks[20]) & 0xf] | $pc2bytes3[($left >> 16 & $masks[16]) & 0xf]
					| $pc2bytes4[($left >> 12 & $masks[12]) & 0xf] | $pc2bytes5[($left >> 8 & $masks[8]) & 0xf]
					| $pc2bytes6[($left >> 4 & $masks[4]) & 0xf];
				$righttemp = $pc2bytes7[$right >> 28 & $masks[28]] | $pc2bytes8[($right >> 24 & $masks[24]) & 0xf]
					| $pc2bytes9[($right >> 20 & $masks[20]) & 0xf] | $pc2bytes10[($right >> 16 & $masks[16]) & 0xf]
					| $pc2bytes11[($right >> 12 & $masks[12]) & 0xf] | $pc2bytes12[($right >> 8 & $masks[8]) & 0xf]
					| $pc2bytes13[($right >> 4 & $masks[4]) & 0xf];
				$temp = (($righttemp >> 16 & $masks[16]) ^ $lefttemp) & 0x0000ffff;
				$keys[$n++] = $lefttemp ^ $temp; $keys[$n++] = $righttemp ^ ($temp << 16);
			}
		}
		return $keys;
	}
}
