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
class plgHikashopKashflow extends JPlugin{
	var $message = '';
	var $params = null;

	public function plgHikashopKashflow(&$subject, $config){
		parent::__construct($subject, $config);
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','kashflow');
		$this->params = @$plugin->params;
	}

	public function onAfterOrderUpdate(&$order){
		$this->sendInvoiceIfNecessary($order);
	}
	public function onAfterOrderCreate(&$order){
		$this->sendInvoiceIfNecessary($order);
	}

	private function sendInvoiceIfNecessary(&$order){
		$config = hikashop_config();
		$confirmed_statuses = explode(',', trim($config->get('invoice_order_statuses','confirmed,shipped'), ','));
		$cancelled_statuses = explode(',', trim($config->get('cancelled_order_status','cancelled'), ','));

		if(empty($confirmed_statuses))
			$confirmed_statuses = array('confirmed','shipped');
		if(empty($cancelled_statuses))
			$cancelled_statuses = array('cancelled');

		if(empty($order->order_status)){
			return true;
		}

		if(in_array($order->order_status,$confirmed_statuses) && (empty($order->old->order_status) || !in_array($order->old->order_status,$confirmed_statuses))){
			return $this->sendInvoice($order);
		}
	}

	private function sendInvoice(&$order){
		$orderClass = hikashop_get('class.order');
		$data = $orderClass->loadfullOrder($order->order_id,false,false);

		if($data->order_type!='sale'){
			return true;
		}

		if(!$this->initConnection()){
			return false;
		}

		$customer_id = $this->getKashflowCustomerID($data);

		if($customer_id){
			$lines = array();
			foreach($data->products as $product){
				$line = array(
					"LineID"      => 0,
					"Quantity"    => $product->order_product_quantity,
					"Description" => $product->order_product_name,
					"Rate"        => $product->order_product_price,
					"ChargeType"  => $this->getProductNominalCode($product),
					"VatAmount"   => $product->order_product_quantity * $product->order_product_tax,
					"VatRate"     => 100*$product->order_product_tax/$product->order_product_price,
					"Sort"        => 1,
					"ProductID"   => 0,
					"ProjID"      => 0,
				);
				$lines[]=new SoapVar($line,0,"InvoiceLine","KashFlow");
			}

			if($data->order_discount_price>0){
				$line = array(
					"LineID"      => 0,
					"Quantity"    => 1,
					"Description" => JText::_('HIKASHOP_COUPON'),
					"Rate"        => -($data->order_discount_price-$data->order_discount_tax),
					"ChargeType"  => (int)$this->getNominalCode(@$this->params['coupon_nominal_code']),
					"VatAmount"   => -$data->order_discount_tax,
					"VatRate"     => 100*$data->order_discount_tax/($data->order_discount_price-$data->order_discount_tax),
					"Sort"        => 1,
					"ProductID"   => 0,
					"ProjID"      => 0,
				);
				$lines[]=new SoapVar($line,0,"InvoiceLine","KashFlow");
			}
			if($data->order_shipping_price>0){
				$line = array(
					"LineID"      => 0,
					"Quantity"    => 1,
					"Description" => JText::_('HIKASHOP_SHIPPING'),
					"Rate"        => ($data->order_shipping_price-$data->order_shipping_tax),
					"ChargeType"  => (int)$this->getNominalCode(@$this->params['shipping_nominal_code']),
					"VatAmount"   => $data->order_shipping_tax,
					"VatRate"     => 100*$data->order_shipping_tax/($data->order_shipping_price-$data->order_shipping_tax),
					"Sort"        => 1,
					"ProductID"   => 0,
					"ProjID"      => 0,
				);
				$lines[]=new SoapVar($line,0,"InvoiceLine","KashFlow");
			}

			$invoice= array(
				"InvoiceDBID" => 0,
				"InvoiceNumber" => $data->order_invoice_number,
				"InvoiceDate" => date('c',$data->order_invoice_created),
				"DueDate" => date('c',$data->order_invoice_created),
				"SuppressTotal" => 1,
				"ProjectID" => (int)@$this->params['project_id'],
				"CurrencyCode" => (int)$this->getCurrencyCode($data->order_currency_id),
				"ExchangeRate" => 1,
				"Paid" => 0,
				"CustomerID" => $customer_id,
				"EstimateCategory" => "",
				"NetAmount" => 0,
				"VATAmount" => 0,
				"AmountPaid" => 0,
				"Permalink" => "",
				"UseCustomDeliveryAddress" => true,
				"Lines" => $lines,
			);

			$parameters = array('Inv'=>$invoice);
			$result = $this->makeRequest('InsertInvoice',$parameters);
			if(!$result){
				return false;
			}
			return true;
		}
		return false;
	}

	private function getProductNominalCode(&$product){
		$defaultNominalCode = @$this->params['nominal_code'];
		if(is_numeric($defaultNominalCode)){
			return $defaultNominalCode;
		}
		if(isset($product->$defaultNominalCode)){
			$defaultNominalCode = $product->$defaultNominalCode;
			if(is_numeric($defaultNominalCode)){
				return $defaultNominalCode;
			}
		}

		return $this->getNominalCode($defaultNominalCode);
	}

	private function getNominalCode($code){
		if(is_numeric($code) && $code){
			return $code;
		}

		$codes = $this->makeRequest('GetNominalCodes',array());
		if(!$codes){
			return 0;
		}

		if(!is_array($codes->GetNominalCodesResult->NominalCode)){
			$codes->GetNominalCodesResult->NominalCode = array($codes->GetNominalCodesResult->NominalCode);
		}
		foreach($codes->GetNominalCodesResult->NominalCode as $nominalCode){
			if($nominalCode->Code==$code || $nominalCode->Name==$code){
				return $nominalCode->id;
			}
		}

		return 0;
	}

	private function getCustomerSource(&$customer,&$order){
		if(!empty($customer['Source'])){
			return $customer['Source'];
		}

		$defaultSource = @$this->params['source'];
		if(!empty($defaultSource)){
			if(is_numeric($defaultSource)){
				return $defaultSource;
			}
			if(!empty($order->customer->$defaultSource)){
				if(is_numeric( $order->customer->$defaultSource)){
					return $order->customer->$defaultSource;
				}
				$defaultSource = $order->customer->$defaultSource;
			}
		}

		$sources = $this->makeRequest('GetCustomerSources',array());
		if(!$sources){
			return 0;
		}

		if(!is_array($sources->GetCustomerSourcesResult->BasicDataset)){
			$sources->GetCustomerSourcesResult->BasicDataset = array($sources->GetCustomerSourcesResult->BasicDataset);
		}
		foreach($sources->GetCustomerSourcesResult->BasicDataset as $source){
			if(empty($defaultSource)){
				return $source->ID;
			}
			if($defaultSource==$source->Name){
				return $source->ID;
			}
		}

		return @$sources->GetCustomerSourcesResult->BasicDataset[0]->ID;
	}

	private function getCurrencyCode($currency_id){
		$currency_code = '';
		if($currency_id){
			$class = hikashop_get('class.currency');
			$dbCurrencyData = $class->get($currency_id);
			if($dbCurrencyData){
				$currency_code = $dbCurrencyData->currency_code;
			}
		}
		return $currency_code;
	}

	private function getCustomerCurrency(&$customer,&$order){
		if(!empty($customer['CurrencyID'])){
			return $customer['CurrencyID'];
		}


		if(!empty($order->order_currency_id)){
			$currency_id = $order->order_currency_id;
		}else{
			$currency_id = hikashop_getCurrency();
		}

		$currencies = $this->makeRequest('GetCurrencies',array());

		if(!$currencies){
			return 0;
		}

		$currency_code = $this->getCurrencyCode($currency_id);

		if(!is_array($currencies->GetCurrenciesResult->Currencies)){
			$currencies->GetCurrenciesResult->Currencies = array($currencies->GetCurrenciesResult->Currencies);
		}
		foreach($currencies->GetCurrenciesResult->Currencies as $currency){
			if(empty($currency_id)){
				return $currency->CurrencyId;
			}
			if($currency_code && $currency->CurrencyCode==$currency_code){
				return  $currency->CurrencyId;
			}
		}

		return @$currencies->GetCurrenciesResult->Currencies[0]->CurrencyId;
	}

	private function getCustomerInformation(&$customer,&$order,$information='Discount',$default=0){
		if(!empty($customer[$information])){
			return $customer[$information];
		}
		$defaultInfo = @$this->params[strtolower($information)];
		if(!empty($defaultInfo)){
			if(is_numeric($defaultInfo)){
				return $defaultInfo;
			}
			if(!empty($order->customer->$defaultInfo)){
				if(is_numeric( $order->customer->$defaultInfo)){
					return $order->customer->$defaultInfo;
				}
			}
		}
		return 0;
	}

	private function getCustomer($email){
		$parameters = array('CustomerEmail'=>$email);
		$customer = $this->makeRequest('GetCustomerByEmail',$parameters,false);
		if(!$customer){
			return array('CustomerID'=>0);
		}
		return get_object_vars($customer->GetCustomerByEmailResult);
	}

	private function getKashflowCustomerID(&$order){
		$customer = $this->getCustomer($order->customer->user_email);
		$customer_id = (int)$customer['CustomerID'];

		$parameters = array('custr'=>$customer);
		$parameters['custr']['CustomerID']=$customer_id;
		$parameters['custr']['Name']=@$order->billing_address->address_firstname.' '.@$order->billing_address->address_lastname;
		$parameters['custr']['ContactTitle']=@$order->billing_address->address_title;
		$parameters['custr']['ContactFirstName']=@$order->billing_address->address_firstname;
		$parameters['custr']['ContactLastName']=@$order->billing_address->address_lastname;
		$parameters['custr']['Telephone']=@$order->billing_address->address_telephone;
		$parameters['custr']['Address1']=@$order->billing_address->address_street;
		$parameters['custr']['Address2']=@$order->billing_address->address_street2;
		$parameters['custr']['Address3']=@$order->billing_address->address_city;
		$parameters['custr']['Address4']=@$order->billing_address->address_country;
		$parameters['custr']['Postcode']=@$order->billing_address->address_postcode;
		$parameters['custr']['Email']=$order->customer->user_email;
		if(!isset($parameters['custr']['OutsideEC'])) $parameters['custr']['OutsideEC']=0;
		$parameters['custr']['Source']=$this->getCustomerSource($customer,$order);
		$parameters['custr']['Discount']=$this->getCustomerInformation($customer,$order,'Discount');
		$parameters['custr']['ShowDiscount']=(bool)$this->getCustomerInformation($customer,$order,'ShowDiscount');
		$parameters['custr']['PaymentTerms']=(bool)$this->getCustomerInformation($customer,$order,'PaymentTerms');
		if(!isset($parameters['custr']['Created'])) $parameters['custr']['Created']=date('c');
		$parameters['custr']['Updated']=date('c');
		$parameters['custr']['CurrencyID']=(int)$this->getCustomerCurrency($customer,$order);


		if(!empty($order->shipping_address->address_vat)){
			$vat_number = $order->shipping_address->address_vat;
		}elseif(!empty($order->billing_address->address_vat)){
			$vat_number = $order->billing_address->address_vat;
		}
		if(!empty($vat_number)){
			$parameters['custr']['EC'] = 1;
			$parameters['custr']['VATNumber'] = $vat_number;
		}else{
			$parameters['custr']['EC'] = 0;
			$parameters['custr']['VATNumber'] = '';
		}
		if(!empty($order->shipping_address)){
			$parameters['custr']['CustHasDeliveryAddress'] = 1;
			$parameters['custr']['DeliveryAddress1'] = @$order->shipping_address->address_street;
			$parameters['custr']['DeliveryAddress2'] = @$order->shipping_address->address_street2;
			$parameters['custr']['DeliveryAddress3'] = @$order->shipping_address->address_city;
			$parameters['custr']['DeliveryAddress4'] = @$order->shipping_address->address_country;
			$parameters['custr']['DeliveryPostcode'] = @$order->shipping_address->address_postcode;
		}else{
			if(!isset($parameters['custr']['CustHasDeliveryAddress'])) $parameters['custr']['CustHasDeliveryAddress'] = 0;
			if(!isset($parameters['custr']['DeliveryAddress1'])) $parameters['custr']['DeliveryAddress1'] = '';
			if(!isset($parameters['custr']['DeliveryAddress2'])) $parameters['custr']['DeliveryAddress2'] = '';
			if(!isset($parameters['custr']['DeliveryAddress3'])) $parameters['custr']['DeliveryAddress3'] = '';
			if(!isset($parameters['custr']['DeliveryAddress4'])) $parameters['custr']['DeliveryAddress4'] = '';
			if(!isset($parameters['custr']['DeliveryPostcode'])) $parameters['custr']['DeliveryPostcode'] = '';
		}
		$i=1;
		while($i<=20){
			if(!isset($parameters['custr']['CheckBox'.$i])) $parameters['custr']['CheckBox'.$i]=0;
			$i++;
		}

		if($customer_id){
			$method = 'UpdateCustomer';
		}else{
			$method = 'InsertCustomer';
		}

		$result = $this->makeRequest($method,$parameters);

		if(!$result){
			return false;
		}
		if(!$customer_id){
			$customer_id = $result->InsertCustomerResult;
		}
		return $customer_id;
	}

	private function makeRequest($fct,$params,$displayError=true){
		try{
			$response = $this->kashflow->makeRequest($fct,$params);
		}catch(Exception $e){
			if($displayError){
				hikashop_display('KashFlow error: '.$e->getMessage(),'error');
			}
			return false;
		}
		return $response;
	}

	private function initConnection(){

		$app = JFactory::getApplication();
		if(!class_exists('SoapClient')){
			hikashop_display('Please Activate the SOAP PHP extension on your web server in order to use the Kashflow plugin. Please ask your hosting company if you don\'t know how to change your php.ini in order to do that.','error');
			return false;
		}

		include_once(dirname(__FILE__).DS.'library'.DS.'kashflow.inc.php');

		if(empty($this->params['password'])||empty($this->params['username'])){
			$app->enqueueMessage('Please configure your KashFlow plugin via the Joomla plugins manager');
			return false;
		}
		try{
			$this->kashflow = new Kashflow($this->params['username'],$this->params['password']);
		}catch(Exception $e){
			hikashop_display('KashFlow error: '.$e->getMessage(),'error');
			return false;
		}
		return true;
	}

}
