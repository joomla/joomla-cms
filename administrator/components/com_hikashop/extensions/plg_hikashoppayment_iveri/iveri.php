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
class plgHikashoppaymentIveri extends hikashopPaymentPlugin
{
	var $accepted_currencies = array('ZAR');
	var $multiple = true;
	var $name = 'iveri';
	var $pluginConfig = array(
		'applicationid' => array('Application ID', 'input'),
		'invoice_prefix' => array('Invoice Prefix', 'input'),
		'domain' => array('Payment Gateway', 'list',array(
			'backoffice.iveri.co.za' => 'backoffice.iveri.co.za',
			'backoffice.host.iveri.com' => 'backoffice.host.iveri.com',
			'backoffice.ctlnigeria.iveri.com' => 'backoffice.ctlnigeria.iveri.com')
		),
		'ask_ccv' => array('Ask for CCV', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		$method->ask_owner = true;
		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The iveri payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$do = false;
			return false;
		}

		$this->ccLoad();

		$address_type = 'billing_address';
		$address1 = ''; $address2 = ''; $address3 = '';

		if(!empty($order->cart->$address_type->address_street)) {
			if(strlen($order->cart->$address_type->address_street)>20) {
				$address1 = substr($order->cart->$address_type->address_street,0,20);
				$address2 = @substr($order->cart->$address_type->address_street,20,20);
				$address3 = @substr($order->cart->$address_type->address_street,40,20);
			}else{
				$address1 = $order->cart->$address_type->address_street;
			}
		}
		$country_code_2 = @$order->cart->$address_type->address_country->zone_code_3;

		if( isset($order->order_id) )
			$uuid = $order->order_id;
		else
			$uuid = uniqid('');


		$this->appId = '{' . trim($this->payment_params->applicationid, " {}\t\r\n\0") . '}';

		$prefix = empty($this->payment_params->invoice_prefix)?'inv':$this->payment_params->invoice_prefix;

		$amount = (int)round($order->cart->full_total->prices[0]->price_value_with_tax * 100);

		$vars = array (
			'Lite_Version' => '2.0',
			'Lite_Merchant_ApplicationId' => $this->appId,
			'Lite_Order_Amount' => $amount,
			'Lite_Order_Terminal' => 'web',
			'Lite_Website_Successful_Url' => 'http://127.0.0.1/success',
			'Lite_Website_Fail_Url' => 'http://127.0.0.1/fail',
			'Lite_Website_TryLater_Url' => 'http://127.0.0.1/trylater',
			'Lite_Website_Error_Url' => 'http://127.0.0.1/error',

			'Lite_Order_LineItems_Product_1' => 'Your order',
			'Lite_Order_LineItems_Amount_1' => $amount,
			'Lite_Order_LineItems_Quantity_1' => 1,

			'Lite_ConsumerOrderID_PreFix' => $prefix,
			'Lite_Authorisation' => 'false',

			'Ecom_TransactionComplete' => 'false',
			'Ecom_SchemaVersion' => '',

			'Ecom_Payment_Card_Protocols' => 'iVeri',
			'Ecom_Payment_Card_StartDate_Day' => '00',
			'Ecom_Payment_Card_StartDate_Month' => '04',
			'Ecom_Payment_Card_StartDate_Year' => '2000',
			'Ecom_Payment_Card_ExpDate_Day' => '00',

			'Ecom_BillTo_Postal_Name_First' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_BillTo_Postal_Name_Last' => substr( @$order->cart->$address_type->address_lastname, 0, 20),
			'Ecom_BillTo_Postal_Street_Line1' => $address1,
			'Ecom_BillTo_Postal_Street_Line2' => $address2,
			'Ecom_BillTo_Postal_Street_Line3' => $address3,
			'Ecom_BillTo_Postal_City' => substr( @$order->cart->$address_type->address_city, 0, 22),
			'Ecom_BillTo_Postal_PostalCode' => substr( @$order->cart->$address_type->address_post_code, 0, 20),
			'Ecom_BillTo_Postal_CountryCode' => @$order->cart->$address_type->address_country->zone_code_2,
			'Ecom_BillTo_Online_Email' => substr($this->user->user_email, 0, 40),

			'Ecom_ShipTo_Postal_Name_First' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_ShipTo_Postal_Name_Last' => substr( @$order->cart->$address_type->address_firstname, 0, 20),
			'Ecom_ShipTo_Postal_Street_Line1' => $address1,
			'Ecom_ShipTo_Postal_Street_Line2' => $address2,
			'Ecom_ShipTo_Postal_Street_Line3' => $address3,
			'Ecom_ShipTo_Postal_City' => substr( @$order->cart->$address_type->address_city, 0, 22),
			'Ecom_ShipTo_Postal_PostalCode' => substr( @$order->cart->$address_type->address_post_code, 0, 14),
			'Ecom_ShipTo_Postal_CountryCode' => @$order->cart->$address_type->address_country->zone_code_2,

			'Ecom_Payment_Card_Name' => $this->cc_owner,
			'Ecom_Payment_Card_Number' => $this->cc_number,
			'Ecom_Payment_Card_Verification' => @$this->cc_CCV,
			'Ecom_Payment_Card_ExpDate_Month' => $this->cc_month,
			'Ecom_Payment_Card_ExpDate_Year' => $this->cc_year,
			'Ecom_ConsumerOrderID' => $uuid,
		);

		$session = curl_init();
		curl_setopt($session, CURLOPT_FRESH_CONNECT,  true);
		curl_setopt($session, CURLOPT_HEADER,         0);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_COOKIEFILE,     "");
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = $this->payment_params->domain;
		$url = '/Lite/Transactions/New/Authorise.aspx';

		curl_setopt($session, CURLOPT_URL, 'https://' . $domain . $url);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);

		$result = curl_exec($session);
		$error = curl_error($session);

		$inputs = $this->getHiddenInputValues($result, true);

		if( !empty($error) || !isset($inputs['__viewstate']) ) {
			$this->app->enqueueMessage('Error while connecting to the Payment Gateway.');
			$do = false;
		} else {
			$inputs = $this->getHiddenInputValues($result);

			curl_setopt($session, CURLOPT_REFERER, 'https://' . $domain . $url);
			curl_setopt($session, CURLOPT_POSTFIELDS, $inputs);

			$result = curl_exec($session);
			$error = curl_error($session);


			$inputs = $this->getHiddenInputValues($result, true);

			if( empty($error) && isset($inputs['lite_payment_card_status']) ) {
				$err = $inputs['lite_payment_card_status'];
				if( $err == 0 ) {

					$this->modifyOrder($order,$this->payment_params->verified_status,true,true);

				} else if($err == 1 || $err == 2 || $err == 5 || $err == 9) {
					$this->app->enqueueMessage('The transaction could not be processed.');
					$do = false;
				} else if($err == 14) {
					$this->app->enqueueMessage('Invalid card number.');
					$do = false;
				} else if($err == 255) {
					$this->app->enqueueMessage('The transaction could not be processed due incorrect or missing information.');
					$do = false;
				} else {
					$this->app->enqueueMessage('The transaction has been declined.');
					$do = false;
				}
			} else {
				$this->app->enqueueMessage('An error occurred.');
				$do = false;
			}
		}
		curl_close($session);

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$this->removeCart = true;

		return $this->showPage('end');
	}

	function getInputTags(&$content) {
		$results = '';
		preg_match_all('/<input\s.*?>/i', $content, $results);
		return $results;
	}

	function getHiddenInputTags(&$content) {
		$input_tags = $this->getInputTags($content);
		$results = array();
		foreach($input_tags[0] as $tag) {
			if (preg_match('/type\s*=\s*(\'|")hidden(\'|")/i', $tag) > 0) {
				array_push($results, $tag);
			}
		}
		return $results;
	}

	function getHiddenInputValues(&$content, $lowerK = false, $lowerV = false) {
		$tags = $this->getHiddenInputTags($content);
		$nameValues = array();

		foreach ($tags as $tag) {
			$name = trim($this->getAttributeValue('name', $tag));
			if ( empty($name) ) continue;
			if( $lowerK ) $name = strtolower($name);
			$value = trim($this->getAttributeValue('value', $tag));
			if( $lowerV ) $value = strtolower($value);
			$nameValues[$name] = $value;
		}

		return $nameValues;
	}

	function getAttributeValue($name, $tag) {
		$regex1 = '/[\s]' . $name . '[\s]*=[\s]*["\'][-\]\\_!@#$%^&*()_+=[|}{;:\/?.,\w\s]*(?=["\'])/i';
		$regex2 = '/[\s]' . $name . '[\s]*=[\s]*["\']/i';

		preg_match_all($regex1, $tag, $matches);
		if (count($matches[0]) != 1) return '';

		return preg_replace($regex2, "", $matches[0][0]);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name = 'IVERI';
		$element->payment_description = 'You can pay by credit card using this payment method';
		$element->payment_images = 'MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->domain = 'backoffice.iveri.co.za';
		$element->payment_params->invoice_prefix = 'inv';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}
}
