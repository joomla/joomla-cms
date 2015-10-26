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
class plgHikashopshippingFedEx extends hikashopShippingPlugin {
	var $multiple = true;
	var $name = 'fedex';
	var $doc_form = 'fedex';
	var $packages;    // array of packages
	var $packageCount;    // number of packages in this shipment
	var $fedex_methods = array(
		array('key'=>1,'code' => 'FEDEX_GROUND', 'name' => 'FedEx Ground', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172') , 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>2,'code' => 'FEDEX_2_DAY', 'name' => 'FedEx 2 Day', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>3,'code' => 'FEDEX_EXPRESS_SAVER', 'name' => 'FedEx Express Saver Time Pickup', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>4,'code' => 'FIRST_OVERNIGHT', 'name' => 'FedEx First Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>5,'code' => 'GROUND_HOME_DELIVERY', 'name' => 'FedEx Ground (Home Delivery)', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>6,'code' => 'PRIORITY_OVERNIGHT', 'name' => 'FedEx Priority Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>7,'code' => 'SMART_POST', 'name' => 'FedEx Smart Post', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>8,'code' => 'STANDARD_OVERNIGHT', 'name' => 'FedEx Standard Overnight', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('key'=>9,'code' => 'INTERNATIONAL_GROUND', 'name' => 'FedEx International Ground'),
		array('key'=>10,'code' => 'INTERNATIONAL_ECONOMY', 'name' => 'FedEx International Economy'),
		array('key'=>11,'code' => 'INTERNATIONAL_ECONOMY_DISTRIBUTION', 'name' => 'FedEx International Economy Distribution'),
		array('key'=>12,'code' => 'INTERNATIONAL_FIRST', 'name' => 'FedEx International First'),
		array('key'=>13,'code' => 'INTERNATIONAL_PRIORITY', 'name' => 'FedEx International Priority'),
		array('key'=>14,'code' => 'INTERNATIONAL_PRIORITY_DISTRIBUTION', 'name' => 'FedEx International Priority Distribution'),
		array('key'=>15,'code' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'name' => 'FedEx Europe First')
	);
	var $convertUnit=array(
		'kg' => 'KGS',
		'lb' => 'LBS',
		'cm' => 'CM',
		'in' => 'IN',
		'kg2' => 'kg',
		'lb2' => 'lb',
		'cm2' => 'cm',
		'in2' => 'in',
	);
	public $nbpackage = 0;

	function shippingMethods(&$main){
		$methods = array();
		if(!empty($main->shipping_params->methodsList)){
			$main->shipping_params->methods=unserialize($main->shipping_params->methodsList);
		}
		if(!empty($main->shipping_params->methods)){
			foreach($main->shipping_params->methods as $method){
				$selected = null;
				foreach($this->fedex_methods as $fedex){
					if($fedex['code']==$method) {
						$selected = $fedex;
						break;
					}
				}
				if($selected){
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
				}
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		if(!hikashop_loadUser())
			return false;

		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);
		if($ret === false)
			return false;
		$currentShippingZone = null;
		$currentCurrencyId = null;
		$currencyClass=hikashop_get('class.currency');
		foreach($local_usable_rates as $k => $rate){
			if(empty($rate->shipping_params->methodsList)) {
				$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the FedEx shipping plugin options';
				continue;
			}
			$rate->shipping_params->methods = unserialize($rate->shipping_params->methodsList);
			if($order->weight <= 0 || ($order->volume <= 0 && @$rate->shipping_params->use_dimensions == 1))
				continue;

			$this->freight = false;
			$this->classicMethod = false;
			$heavyProduct = false;
			$weightTotal = 0;
			if(!empty($rate->shipping_params->methods)) {
				foreach($rate->shipping_params->methods as $method) {
					if($method=='TDCB' || $method=='TDA' || $method=='TDO' || $method=='308' || $method=='309' || $method=='310')
						$this->freight = true;
					else
						$this->classicMethod = true;
				}
			}

			$data = null;
			if(empty($order->shipping_address)) {
				$messages['no_shipping_methods_configured'] = 'No shipping address is configured.';
				return true;
			}

			$this->shipping_currency_id=$currency= hikashop_getCurrency();
			$db = JFactory::getDBO();
			$query='SELECT currency_code FROM '.hikashop_table('currency').' WHERE currency_id IN ('.$this->shipping_currency_id.')';
			$db->setQuery($query);
			$this->shipping_currency_code = $db->loadResult();
			$cart = hikashop_get('class.cart');
			$null = null;
			$cart->loadAddress($null,$order->shipping_address->address_id,'object', 'shipping');
			$currency = hikashop_get('class.currency');

			$receivedMethods=$this->_getRates($rate, $order, $heavyProduct, $null);
			if(empty($receivedMethods)) {
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
				continue;
			}
			$i = 0;
			$local_usable_rates = array();
			foreach($receivedMethods as $method) {
				$usableMethods[] = $method;
				$local_usable_rates[$i]=(!HIKASHOP_PHP5) ? $rate : clone($rate);
				$local_usable_rates[$i]->shipping_price += round($method['value'], 2);
				$selected_method = '';
				$name = '';
				foreach($this->fedex_methods as $fedex_method) {
					if($fedex_method['code'] == $method['code'] && ($method['old_currency_code'] == 'CAD' || !isset($fedex_method['double']))) {
						$name = $fedex_method['name'];
						$selected_method = $fedex_method['key'];
						break;
					}
				}
				$local_usable_rates[$i]->shipping_name=$name;
				if(!empty($selected_method))
					$local_usable_rates[$i]->shipping_id .= '-' . $selected_method;
				$sep = '';
				if(@$rate->shipping_params->show_eta) {
					if(@$rate->shipping_params->show_eta_delay) {
						if($method['delivery_delay']!=-1 && $method['day']>0){
							$local_usable_rates[$i]->shipping_description.=$sep.JText::sprintf( 'ESTIMATED_TIME_AFTER_SEND', $method['delivery_delay']);
						}else{
							$local_usable_rates[$i]->shipping_description.=$sep.JText::_( 'NO_ESTIMATED_TIME_AFTER_SEND');
						}
					} else {
						if($method['delivery_day']!=-1 && $method['day']>0){
							$local_usable_rates[$i]->shipping_description.=$sep.JText::sprintf( 'ESTIMATED_TIME_AFTER_SEND', $method['delivery_day']);
						}else{
							$local_usable_rates[$i]->shipping_description.=$sep.JText::_( 'NO_ESTIMATED_TIME_AFTER_SEND');
						}
					}
					$sep = '<br/>';
					if($method['delivery_time']!=-1 && $method['day']>0){
						if(@$rate->shipping_params->show_eta_format == '12')
							$local_usable_rates[$i]->shipping_description.=$sep.JText::sprintf( 'DELIVERY_HOUR', date('h:i:s a', strtotime($method['delivery_time'])));
						else
							$local_usable_rates[$i]->shipping_description.=$sep.JText::sprintf( 'DELIVERY_HOUR', $method['delivery_time']);
					}else{
						$local_usable_rates[$i]->shipping_description.=$sep.JText::_( 'NO_DELIVERY_HOUR');
					}
				}
				if(@$rate->shipping_params->show_notes && !empty($method['notes'])) {
					foreach($method['notes'] as $note){
						if($note->Code != '820' && $note->Code != '819' && !empty($note->LocalizedMessage) ) {
							$local_usable_rates[$i]->shipping_description.=$sep.implode('<br/>', $note->LocalizedMessage);
							$sep = '<br/>';
						}
					}
				}
				if($rate->shipping_params->group_package && $this->nbpackage>0)
					$local_usable_rates[$i]->shipping_description.='<br/>'.JText::sprintf('X_PACKAGES', $this->nbpackage);
				$i++;
			}
			foreach($local_usable_rates as $i => $finalRate){
				if(isset($finalRate->shipping_price_orig) || isset($finalRate->shipping_currency_id_orig)){
					if($finalRate->shipping_currency_id_orig == $finalRate->shipping_currency_id)
						$finalRate->shipping_price_orig = $finalRate->shipping_price;
					else
						$finalRate->shipping_price_orig = $currencyClass->convertUniquePrice($finalRate->shipping_price, $finalRate->shipping_currency_id, $finalRate->shipping_currency_id_orig);
				}
				$usable_rates[$finalRate->shipping_id]=$finalRate;
			}
		}
	}
	function getShippingDefaultValues(&$element){
		$element->shipping_name='FedEx';
		$element->shipping_description='';
		$element->group_package=0;
		$element->debug=0;
		$element->shipping_images='fedex';
		$element->shipping_params->post_code='';
		$element->shipping_currency_id = $this->main_currency;
		$element->shipping_params->pickup_type='01';
		$element->shipping_params->destination_type='auto';
	}
	function onShippingConfiguration(&$element){
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency', 1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_get('class.currency');
		$this->currencyCode = $currency->get($this->main_currency)->currency_code;
		$this->currencySymbol = $currency->get($this->main_currency)->currency_symbol;
		$this->fedex = JRequest::getCmd('name','fedex');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';
		$this->nameboxType = hikashop_get('type.namebox');

		parent::onShippingConfiguration($element);

		$js="
			function checkAllBox(id, type){
				var toCheck = document.getElementById(id).getElementsByTagName('input');
				for (i = 0 ; i < toCheck.length ; i++) {
					if (toCheck[i].type == 'checkbox') {
						if(type == 'check'){
							toCheck[i].checked = true;
						}else{
							toCheck[i].checked = false;
						}
					}
				}
			}";
		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$element){
		$app = JFactory::getApplication();
		$methods=array();
		if(empty($element->shipping_params->account_number) ||
			empty($element->shipping_params->origination_postcode) ||
			empty($element->shipping_params->meter_id) ||
			empty($element->shipping_params->api_key) ||
			empty($element->shipping_params->api_password) ||
			empty($element->shipping_params->sender_company) ||
			empty($element->shipping_params->sender_phone) ||
			empty($element->shipping_params->sender_address) ||
			empty($element->shipping_params->sender_city) ||
			empty($element->shipping_params->sender_state) ||
			empty($element->shipping_params->sender_country) ||
			empty($element->shipping_params->sender_postcode)
		 ){
			$app->enqueueMessage(JText::sprintf('ENTER_INFO', 'FedEx', JText::_('SENDER_INFORMATIONS').' ('. JText::_( 'FEDEX_ORIGINATION_POSTCODE' ).', '.JText::_( 'FEDEX_ACCOUNT_NUMBER' ).', '.JText::_( 'FEDEX_METER_ID' ).', '.JText::_( 'FEDEX_API_KEY' ).', '.JText::_( 'HIKA_PASSWORD' ).', '.JText::_( 'COMPANY' ).', '.JText::_( 'TELEPHONE' ).', '.JText::_( 'ADDRESS' ).', '.JText::_( 'CITY' ).', '.JText::_( 'COUNTRY' ).', '.JText::_( 'POST_CODE' ).')'));
		}
		if(isset($_REQUEST['data']['shipping_methods'])){
			foreach($_REQUEST['data']['shipping_methods'] as $method){
				foreach($this->fedex_methods as $fedexMethod){
					$name=strtolower($fedexMethod['name']);
					$name=str_replace(' ','_', $name);
					if($name==$method['name']){
						$obj = new stdClass();
						$methods[strip_tags($method['name'])]=strip_tags($fedexMethod['code']);
					}
				}
			}
		} else {
			$app->enqueueMessage(JText::sprintf('CHOOSE_SHIPPING_SERVICE'));
		}
		$element->shipping_params->methodsList = serialize($methods);
		return true;
	}


	function _getRates(&$rate, &$order, $heavyProduct, $null){
		$db = JFactory::getDBO();
		$total_price = 0;
		foreach($order->products as $k=>$v){
			foreach($v->prices as $price){
				$total_price = $total_price + $price->price_value;
			}
		}

		$data['fedex_account_number']=@$rate->shipping_params->account_number;
		$data['fedex_meter_number']=@$rate->shipping_params->meter_id;
		$data['fedex_api_key']=@$rate->shipping_params->api_key;
		$data['fedex_api_password']=@$rate->shipping_params->api_password;
		$data['show_eta']=@$rate->shipping_params->show_eta;
		$data['show_eta_format']=@$rate->shipping_params->show_eta_format;
		$data['packaging_type']=@$rate->shipping_params->packaging_type;
		$data['include_price']=@$rate->shipping_params->include_price;
		$data['currency_code']= $this->shipping_currency_code;
		$data['weight_approximation']=@$rate->shipping_params->weight_approximation;
		$data['use_dimensions']=@$rate->shipping_params->use_dimensions;
		$data['dim_approximation_l']=@$rate->shipping_params->dim_approximation_l;
		$data['dim_approximation_w']=@$rate->shipping_params->dim_approximation_w;
		$data['dim_approximation_h']=@$rate->shipping_params->dim_approximation_h;
		$data['methods']=@$rate->shipping_params->methods;
		$data['destZip']=@$null->shipping_address->address_post_code;
		$data['destCountry']=@$null->shipping_address->address_country->zone_code_2;
		$data['zip']=@$rate->shipping_params->origination_postcode;
		$data['total_insured']=@$total_price;
		$data['sender_company']=@$rate->shipping_params->sender_company;
		$data['sender_phone']=@$rate->shipping_params->sender_phone;
		$data['sender_address']=@$rate->shipping_params->sender_address;
		$data['sender_city']=@$rate->shipping_params->sender_city;

		$state_zone = '';
		$state_zone=@$rate->shipping_params->sender_state;
		$query="SELECT zone_id, zone_code_2, zone_code_3 FROM ".hikashop_table('zone')." WHERE zone_namekey IN (".$db->Quote($state_zone).")";
		$db->setQuery($query);
		$state = $db->loadObject();
		$data['sender_state'] = '';
		if(isset($state->zone_code_2) && strlen($state->zone_code_2) == 2)
			$data['sender_state'] = $state->zone_code_2;
		elseif(strlen($state->zone_code_3) == 2)
			$data['sender_state']=$state->zone_code_3;

		$data['sender_postcode']=$rate->shipping_params->sender_postcode;
		$data['recipient']=$null->shipping_address;

		$czone_code = '';
		$czone_code=@$rate->shipping_params->sender_country;
		$query="SELECT zone_id, zone_code_2 FROM ".hikashop_table('zone')." WHERE zone_namekey IN (".$db->Quote($czone_code).")";
		$db->setQuery($query);
		$czone = $db->loadObject();
		$data['country'] = $czone->zone_code_2;

		$data['XMLpackage']='';
		$data['pickup_type']=@$rate->shipping_params->pickup_type;
		$this->nbpackage = 0;
		if(!$rate->shipping_params->group_package || $rate->shipping_params->group_package == 0){
			$data['weight']=0;
			$data['height']=0;
			$data['length']=0;
			$data['width']=0;
			$data['price']=0;
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							$caracs=parent::_convertCharacteristics($variant, $data);
							$data['weight_unit']=$caracs['weight_unit'];
							$data['dimension_unit']=$caracs['dimension_unit'];
							$data['weight']+=round($caracs['weight'],2)*$variant->cart_product_quantity;
							if($caracs['height'] != '' && $caracs['height'] != '0.00' && $caracs['height'] != 0){
								$data['height']+=round($caracs['height'],2)*$variant->cart_product_quantity;
								$data['length']+=round($caracs['length'],2)*$variant->cart_product_quantity;
								$data['width']+=round($caracs['width'],2)*$variant->cart_product_quantity;
							}

							$data['price']+=$variant->prices[0]->price_value_with_tax*$variant->cart_product_quantity;
						}
					}
					else{
						$caracs=parent::_convertCharacteristics($product, $data);
						$data['weight_unit']=$caracs['weight_unit'];
						$data['dimension_unit']=$caracs['dimension_unit'];
						$data['weight']+=round($caracs['weight'],2)*$product->cart_product_quantity;
						if($caracs['height'] != '' && $caracs['height'] != '0.00' && $caracs['height'] != 0){
							$data['height']+=round($caracs['height'],2)*$product->cart_product_quantity;
							$data['length']+=round($caracs['length'],2)*$product->cart_product_quantity;
							$data['width']+=round($caracs['width'],2)*$product->cart_product_quantity;
						}
						$data['price']+=$product->prices[0]->price_value_with_tax*$product->cart_product_quantity;
					}
				}
			}
			if(($this->freight==true && $this->classicMethod==false) || ($heavyProduct==true && $this->freight==true))
				$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order );
			else
				$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );

			$usableMethods=$this->_FEDEXrequestMethods($data,$rate);
			return $usableMethods;
		}
		else{
			$data['weight']=0;
			$data['height']=0;
			$data['length']=0;
			$data['width']=0;
			$data['price']=0;
			$current_package = array();

			$limitation = array(
				'length' => 150,
				'weight' => 119,
				'dimension' => 300
			);
			if(!empty($rate->shipping_params->methods)) {
				foreach($rate->shipping_params->methods as $k => $v) {
					$l_lenght = 0; $l_weight = 0; $l_dimension = 0;
					switch($v) {
						case 'FEDEX_GROUND':
							$l_length = 150;
							$l_weight =108;
							$l_dimension=165;
							break;
						case 'FEDEX_EXPRESS_SAVER':
							$l_length = 150;
							$l_weight =119;
							$l_dimension=130;
							break;
					}

					if($l_length > 0 && $limitation['length'] > $l_length) {
						$limitation['length'] = $l_length;
					}
					if($l_weight > 0 && $limitation['weight'] > $l_weight) {
						$limitation['weight'] = $l_weight;
					}
					if($l_dimension > 0 && $limitation['dimension'] > $l_dimension) {
						$limitation['dimension'] = $l_dimension;
					}
				}
			}

			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							for($i=0;$i<$variant->cart_product_quantity;$i++){
								$caracs=parent::_convertCharacteristics($variant, $data);
								$current_package = parent::groupPackages($data, $caracs);
								if($data['weight']+round($caracs['weight'],2)>$limitation['weight'] || $current_package['dim']>$limitation['dimension'] || $data['width']>$limitation['length']){
									$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
									$data['weight']=round($caracs['weight'],2);
									$data['height']=$current_package['y'];
									$data['length']=$current_package['z'];
									$data['width']=$current_package['x'];
									$data['price']=$variant->prices[0]->price_value_with_tax;
								}
								else{
									$data['weight']+=round($caracs['weight'],2);
									$data['height']=max($data['height'],$current_package['y']);
									$data['length']=max($data['length'],$current_package['z']);
									$data['width']+=$current_package['x'];
									$data['price']+=$variant->prices[0]->price_value_with_tax;
								}
							}
						}
					}
					else{
						for($i=0;$i<$product->cart_product_quantity;$i++){
							$caracs=parent::_convertCharacteristics($product, $data);
							$current_package = parent::groupPackages($data, $caracs);
							if($data['weight']+round($caracs['weight'],2)>$limitation['weight'] || $current_package['dim']>$limitation['dimension'] || $data['width']>$limitation['length']){
								$this->nbpackage++;
								$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
								$data['weight']=round($caracs['weight'],2);
								$data['height']=$current_package['y'];
								$data['length']=$current_package['z'];
								$data['width']=$current_package['x'];
								$data['price']=$product->prices[0]->price_value_with_tax;
							}
							else{
								$data['weight']+=round($caracs['weight'],2);
								$data['height']=max($data['height'],$current_package['y']);
								$data['length']=max($data['length'],$current_package['z']);
								$data['width']+=$current_package['x'];
								$data['price']+=$product->prices[0]->price_value_with_tax;
							}
						}
					}
				}
			}
			if (($data['weight']+$data['height']+$data['length']+$data['width'])>0){
				$this->nbpackage++;
				$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true);
			}
			$usableMethods=$this->_FEDEXrequestMethods($data,$rate);
		}
		if(empty($usableMethods)){
			return false;
		}
		$currencies=array();
		foreach($usableMethods as $method){
			$currencies[$method['currency_code']]='"'.$method['currency_code'].'"';
		}
		$db = JFactory::getDBO();
		$query='SELECT currency_code, currency_id FROM '.hikashop_table('currency').' WHERE currency_code IN ('.implode(',',$currencies).')';
		$db->setQuery($query);
		$currencyList = $db->loadObjectList();
		$currencyList=reset($currencyList);
		foreach($usableMethods as $i => $method){
			$usableMethods[$i]['currency_id']=$currencyList->currency_id;
		}
		$usableMethods = parent::_currencyConversion($usableMethods, $order);


		return $usableMethods;
	}

	function _createPackage(&$data, &$product, &$rate, &$order, $includeDimension=false){
		if(empty($data['weight'])){
			$caracs=parent::_convertCharacteristics($product, $data);
			$data['weight_unit']=$caracs['weight_unit'];
			$data['dimension_unit']=$caracs['dimension_unit'];
			$data['weight']=round($caracs['weight'],2);
			if($caracs['height'] != '' && $caracs['height'] != '0.00' && $caracs['height'] != 0){
				$data['height']=round($caracs['height'],2);
				$data['length']=round($caracs['length'],2);
				$data['width']=round($caracs['width'],2);
			}
		}
		if($data['weight_unit'] == 'KGS') $data['weight_unit'] = 'KG';
		if($data['weight_unit'] == 'LBS') $data['weight_unit'] = 'LB';
		$currencyClass=hikashop_get('class.currency');
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_getCurrency();
		if(isset($data['price'])){
			$price=$data['price'];
		}
		else{
			$price=$product->prices[0]->price_value;
		}
		if(@$this->shipping_currency_id!=@$data['currency'] && !empty($data['currency'])){
			$price=$currencyClass->convertUniquePrice($price, $this->shipping_currency_id,@$data['currency']);
		}
		if(!empty($rate->shipping_params->weight_approximation)){
			$data['weight']=$data['weight']+$data['weight']*$rate->shipping_params->weight_approximation/100;
		}
		if(@$data['weight']<1){
			$data['weight']=1;
		}
		if(!empty($rate->shipping_params->dim_approximation_h) && @$rate->shipping_params->use_dimensions == 1){
			$data['height']=$data['height']+$data['height']*$rate->shipping_params->dim_approximation_h/100;
		}
		if(!empty($rate->shipping_params->dim_approximation_l) && @$rate->shipping_params->use_dimensions == 1){
			$data['length']=$data['length']+$data['length']*$rate->shipping_params->dim_approximation_l/100;
		}
		if(!empty($rate->shipping_params->dim_approximation_w) && @$rate->shipping_params->use_dimensions == 1){
			$data['width']=$data['width']+$data['width']*$rate->shipping_params->dim_approximation_w/100;
		}
		$options='';
		$dimension='';
		if(@$rate->shipping_params->include_price){
			$options='<PackageServiceOptions>
						<InsuredValue>
							<CurrencyCode>'.$data['currency_code'].'</CurrencyCode>
							<MonetaryValue>'.$price.'</MonetaryValue>
						</InsuredValue>
					</PackageServiceOptions>';
		}
		if($includeDimension){
			if($data['height'] != '' && $data['height'] != 0 && $data['height'] != '0.00'){
				$dimension='<Dimensions>
							<UnitOfMeasurement>
								<Code>'.$data['dimension_unit'].'</Code>
							</UnitOfMeasurement>
							<Length>'.$data['length'].'</Length>
							<Width>'.$data['width'].'</Width>
							<Height>'.$data['height'].'</Height>
						</Dimensions>';
			}
		}
		static $id = 0;
		$xml='<Package'.$id.'>
				<PackagingType>
					<Code>02</Code>
				</PackagingType>
				<Description>Shop</Description>
				'.$dimension.'
				<PackageWeight>
					<UnitOfMeasurement>
						<Code>'.$data['weight_unit'].'</Code>
					</UnitOfMeasurement>
					<Weight>'.$data['weight'].'</Weight>
				</PackageWeight>
				'.$options.'
			</Package'.$id.'>';
		$id++;
		return $xml;
	}
	function _FEDEXrequestMethods($data,$rate){
		global $fedex_methods;

		$path_to_wsdl = dirname(__FILE__).DS.'fedex_rate.wsdl';

		ini_set("soap.wsdl_cache_enabled","0");
		if(!class_exists('SoapClient')){
			$app = JFactory::getApplication();
			$app->enqueueMessage('The FEDEX shipping plugin needs the SOAP library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}
		$client = new SoapClient($path_to_wsdl, array('exceptions' => false));


		$shipment= array();
		foreach($data['methods'] as $k=>$v){
			$request['WebAuthenticationDetail'] = array(
				'UserCredential' =>array(
					'Key' => $data['fedex_api_key'],
					'Password' => $data['fedex_api_password']
				)
			);
			$request['ClientDetail'] = array(
				'AccountNumber' => $data['fedex_account_number'],
				'MeterNumber' => $data['fedex_meter_number']
			);
			$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v10 using PHP ***');
			$request['Version'] = array(
				'ServiceId' => 'crs',
				'Major' => '10',
				'Intermediate' => '0',
				'Minor' => '0'
			);

			$request['ReturnTransitAndCommit'] = true;
			$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
			$request['RequestedShipment']['ShipTimestamp'] = date('c');
			$request['RequestedShipment']['ServiceType'] = $v; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			$request['RequestedShipment']['PackagingType'] = $data['packaging_type']; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			$request['RequestedShipment']['TotalInsuredValue']=array('Ammount'=>$data['total_insured'],'Currency'=>'USD');
			$request['RequestedPackageDetailType'] = 'PACKAGE_SUMMARY';

			$shipper = array(
				'Contact' => array(
					'PersonName' => $data['sender_company'],
					'CompanyName' => $data['sender_company'],
					'PhoneNumber' => $data['sender_phone']),
				'Address' => array(
					'StreetLines' => array($data['sender_address']),
					'City' => $data['sender_city'],
					'StateOrProvinceCode' => $data['sender_state'],
					'PostalCode' => $data['sender_postcode'],
					'CountryCode' => $data['country'])
			);

			$recipient_StateOrProvinceCode = '';
			if(isset($data['recipient']->address_state->zone_code_2) && strlen($data['recipient']->address_state->zone_code_2) == 2)
				$recipient_StateOrProvinceCode = $data['recipient']->address_state->zone_code_2;
			elseif(strlen($data['recipient']->address_state->zone_code_3) == 2)
				$recipient_StateOrProvinceCode = $data['recipient']->address_state->zone_code_3;
			$recipient = array(
				'Contact' => array(
					'PersonName' => $data['recipient']->address_title." ".$data['recipient']->address_firstname." ".$data['recipient']->address_lastname,
					'CompanyName' => $data['recipient']->address_company,
					'PhoneNumber' => $data['recipient']->address_telephone
				),
				'Address' => array(
					'StreetLines' => array($data['recipient']->address_street),
					'City' => $data['recipient']->address_city,
					'StateOrProvinceCode' => $recipient_StateOrProvinceCode,
					'PostalCode' => $data['recipient']->address_post_code,
					'CountryCode' => $data['recipient']->address_country->zone_code_2,
					'Residential' => true)
			);
			if(@$rate->shipping_params->destination_type=='res'){
				$recipient['Address']['Residential'] = true;
			}
			if(@$rate->shipping_params->destination_type=='com' || (@$rate->shipping_params->destination_type=='auto' && $v == 'FEDEX_GROUND')){
				$recipient['Address']['Residential'] = false;
			}
			$shippingChargesPayment = array(
				'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
				'Payor' => array(
					'AccountNumber' => $data['fedex_account_number'],
					'CountryCode' => $data['country'])
			);

			$pkg_values = $this->xml2array('<root>'.$data['XMLpackage'].'</root>');
			$pkg_values = $pkg_values['root'];
			$pkg_count = count($pkg_values);

			$request['RequestedShipment']['Shipper'] = $shipper;
			$request['RequestedShipment']['Recipient'] = $recipient;
			$request['RequestedShipment']['ShippingChargesPayment'] = $shippingChargesPayment;
			$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT';
			if(empty($rate->shipping_params->rate_types) || $rate->shipping_params->rate_types != 'ACCOUNT'){
				$request['RequestedShipment']['RateRequestTypes'] = 'LIST';
			}
			$request['RequestedShipment']['PackageCount'] = $pkg_count;
			$request['RequestedShipment']['RequestedPackageLineItems'] = $this->addPackageLineItem($pkg_values);

			if(@$rate->shipping_params->debug){
				echo "<br/> Request $v : <br/>";
				echo '<pre>' . var_export($request, true) . '</pre>';
			}


				$response = $client->getRates($request);


			if(isset($response->HighestSeverity) && $response->HighestSeverity == "ERROR") {
				static $notif = false;
				if(!$notif && isset($response->Notifications->Message) && $response->Notifications->Message == 'Authentication Failed') {
					$app = JFactory::getApplication();
					$app->enqueueMessage('FEDEX Authentication Failed');
					$notif = true;
				}
				if(!$notif && !empty($response->Notifications->Message) && strpos($response->Notifications->Message,'Service is not allowed') === FALSE) {
					$app = JFactory::getApplication();
					$app->enqueueMessage('The FedEx request failed with the message : ' . $response->Notifications->Message);
				}
			}
			if(@$rate->shipping_params->debug){
				echo "<br/> Response $v : <br/>";
				echo '<pre>' . var_export($response, true) . '</pre>';
			}
			if(!empty($response->HighestSeverity) && ($response->HighestSeverity == "SUCCESS" || $response->HighestSeverity == "NOTE" || $response->HighestSeverity == "WARNING")) {
				$code = '';
				$notes = array();
				if($response->HighestSeverity == "NOTE" || $response->HighestSeverity == "WARNING") {
					$notes = $response->Notifications;
				}

				foreach($this->fedex_methods as $k=>$v) {
					if($v['code'] == $response->RateReplyDetails->ServiceType){
						$code = $v['code'];
					}
				}
				$delayType = hikashop_get('type.delay');
				if(!empty($response->RateReplyDetails->DeliveryTimestamp))
					$timestamp = strtotime($response->RateReplyDetails->DeliveryTimestamp);
				else {
					$timestamp = 0;
					$response->RateReplyDetails->DeliveryTimestamp=0;
				}
				$totalNetPrice = 0;
				$discountAmount = 0;
				if(is_array($response->RateReplyDetails->RatedShipmentDetails)){
					$totalNetPrice = $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;

					if($request['RequestedShipment']['RateRequestTypes'] != 'ACCOUNT'){
						$discountAmount = $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalFreightDiscounts->Amount;
					}
					$shipment[] = array(
						'value'=>$totalNetPrice + $discountAmount,
						'code'=>$code,
						'delivery_timestamp' => $timestamp,
						'day'=>$response->RateReplyDetails->DeliveryTimestamp,
						'delivery_day' => date("m/d/Y", $timestamp),
						'delivery_delay' => parent::displayDelaySECtoDAY($timestamp - strtotime('now'),2),
						'delivery_time' => date("H:i:s", $timestamp),
						'currency_code'=>$response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency,
						'old_currency_code' => $response->RateReplyDetails->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Currency,
						'notes' => $notes
					);

				} else if(is_object($response->RateReplyDetails->RatedShipmentDetails)){
					$totalNetPrice = $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount;

					if($request['RequestedShipment']['RateRequestTypes'] != 'ACCOUNT'){
						$discountAmount = $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalFreightDiscounts->Amount;
					}
					$shipment[] = array(
						'value'=>$totalNetPrice + $discountAmount,
						'code'=>$code,
						'delivery_timestamp' => $timestamp,
						'day'=>$response->RateReplyDetails->DeliveryTimestamp,
						'delivery_day' => date("m/d/Y", $timestamp),
						'delivery_delay' => parent::displayDelaySECtoDAY($timestamp - strtotime('now'),2),
						'delivery_time' => date("H:i:s", $timestamp),
						'currency_code'=>$response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency,
						'old_currency_code' => $response->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency,
						'notes' => $notes
					);
				}
			} else if(!empty($response->HighestSeverity) && ($response->HighestSeverity == "ERROR")) {
				static $errorsDisplayed = array();

				$acceptedCodes = array(836);

				if(!empty($response->Notifications)) {
					foreach($response->Notifications as $notif) {
						if(!is_object($notif))
							continue;
						$errorCode = $notif->Code;

						if(!in_array($errorCode, $acceptedCodes)) {
							if(!isset($errorsDisplayed[$errorCode])) {
								$app = JFactory::getApplication();
								$app->enqueueMessage($notif->Message);
							}
							$errorsDisplayed[$errorCode] = true;
						}
					}

				}
			}
		}
		return $shipment;
	}

	function printSuccess($client, $response) {
		echo '<h2>Transaction Successful</h2>';
		echo "\n";
		printRequestResponse($client);
	}
	function printRequestResponse($client){
		echo '<h2>Request</h2>' . "\n";
		echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';
		echo "\n";

		echo '<h2>Response</h2>'. "\n";
		echo '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';
		echo "\n";
	}

	function printFault($exception, $client) {
		echo '<h2>Fault</h2>' . "<br>\n";
		echo "<b>Code:</b>{$exception->faultcode}<br>\n";
		echo "<b>String:</b>{$exception->faultstring}<br>\n";
		writeToLog($client);
	}

	function writeToLog($client){
		if (!$logfile = fopen(TRANSACTIONS_LOG_FILE, "a")) {
			error_func("Cannot open " . TRANSACTIONS_LOG_FILE . " file.\n", 0);
			exit(1);
		}

		fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()));
	}

	function getProperty($var){
		if($var == 'check') Return true;
		if($var == 'shipaccount') Return 'XXX';
		if($var == 'billaccount') Return 'XXX';
		if($var == 'dutyaccount') Return 'XXX';
		if($var == 'accounttovalidate') Return 'XXX';
		if($var == 'meter') Return 'XXX';
		if($var == 'key') Return 'XXX';
		if($var == 'password') Return '';
		if($var == 'shippingChargesPayment') Return 'SENDER';
		if($var == 'internationalPaymentType') Return 'SENDER';
		if($var == 'readydate') Return '2010-05-31T08:44:07';
		if($var == 'readytime') Return '12:00:00-05:00';
		if($var == 'closetime') Return '20:00:00-05:00';
		if($var == 'closedate') Return date("Y-m-d");
		if($var == 'pickupdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'pickuptimestamp') Return mktime(8, 0, 0, date("m")  , date("d")+1, date("Y"));
		if($var == 'pickuplocationid') Return 'XXX';
		if($var == 'pickupconfirmationnumber') Return '00';
		if($var == 'dispatchdate') Return date("Y-m-d", mktime(8, 0, 0, date("m")  , date("d")+1, date("Y")));
		if($var == 'dispatchtimestamp') Return mktime(8, 0, 0, date("m")  , date("d")+1, date("Y"));
		if($var == 'dispatchlocationid') Return 'XXX';
		if($var == 'dispatchconfirmationnumber') Return '00';
		if($var == 'shiptimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'tag_readytimestamp') Return mktime(10, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'tag_latesttimestamp') Return mktime(15, 0, 0, date("m"), date("d")+1, date("Y"));
		if($var == 'trackingnumber') Return 'XXX';
		if($var == 'trackaccount') Return 'XXX';
		if($var == 'shipdate') Return '2010-06-06';
		if($var == 'account') Return 'XXX';
		if($var == 'phonenumber') Return '1234567890';
		if($var == 'closedate') Return '2010-05-30';
		if($var == 'expirationdate') Return '2011-06-15';
		if($var == 'hubid') Return '5531';
		if($var == 'begindate') Return '2011-05-20';
		if($var == 'enddate') Return '2011-05-31';
		if($var == 'address1') Return array('StreetLines' => array('10 Fed Ex Pkwy'),
			'City' => 'Memphis',
			'StateOrProvinceCode' => 'TN',
			'PostalCode' => '38115',
			'CountryCode' => 'US');
		if($var == 'address2') Return array('StreetLines' => array('13450 Farmcrest Ct'),
			'City' => 'Herndon',
			'StateOrProvinceCode' => 'VA',
			'PostalCode' => '20171',
			'CountryCode' => 'US');
		if($var == 'locatoraddress') Return array(array('StreetLines'=>'240 Central Park S'),
			'City'=>'Austin',
			'StateOrProvinceCode'=>'TX',
			'PostalCode'=>'78701',
			'CountryCode'=>'US');
		if($var == 'recipientcontact') Return array('ContactId' => 'arnet',
			'PersonName' => 'Recipient Contact',
			'PhoneNumber' => '1234567890');
		if($var == 'freightaccount') Return 'XXX';
		if($var == 'freightbilling') Return array(
			'Contact'=>array(
				'ContactId' => 'freight1',
				'PersonName' => 'Big Shipper',
				'Title' => 'Manager',
				'CompanyName' => 'Freight Shipper Co',
				'PhoneNumber' => '1234567890'
			),
			'Address'=>array(
				'StreetLines'=>array('1202 Chalet Ln', 'Do Not Delete - Test Account'),
				'City' =>'Harrison',
				'StateOrProvinceCode' => 'AR',
				'PostalCode' => '72601-6353',
				'CountryCode' => 'US'
			)
		);
	}

	function setEndpoint($var){
		if($var == 'changeEndpoint') Return false;
		if($var == 'endpoint') Return '';
	}

	function printNotifications($notes){
		foreach($notes as $noteKey => $note){
			if(is_string($note)){
				echo $noteKey . ': ' . $note . Newline;
			} else{
				printNotifications($note);
			}
		}
		echo Newline;
	}

	function printError($client, $response){
		echo '<h2>Error returned in processing transaction</h2>';
		echo "\n";
		printNotifications($response->Notifications);
		printRequestResponse($client, $response);
	}

	function addPackageLineItem($pkg_values){
		$packageLineItem[] = array();
		$ct = count($pkg_values);
		$x = 1;
		foreach($pkg_values as $pkg) {
			if($pkg['PackageWeight']['UnitOfMeasurement']['Code'] == "LBS"){
				$uom = "LB";
			} else {
				$uom = $pkg["PackageWeight"]["UnitOfMeasurement"]['Code'];
			}
			if(is_array($pkg['Dimensions'])){
				$dimensions = array("Dimensions"=>array(
					'Length' => $pkg['Dimensions']['Length'],
					'Width' => $pkg['Dimensions']['Width'],
					'Height' => $pkg['Dimensions']['Height'],
					'Units' => $pkg['Dimensions']['UnitOfMeasurement']['Code'])
				);
			}

			$packageLineItem = array(
				'SequenceNumber'=>$x,
				'GroupPackageCount'=>$ct,
				'Weight' => array(
					'Value' => $pkg['PackageWeight']['Weight'],
					'Units' => $uom
				),
				$dimensions
			);
			$x++;
		}

		return $packageLineItem;
	}

	function xml2array($contents, $get_attributes = 1, $priority = 'tag') {
		if (!function_exists('xml_parser_create')) {
			return array ();
		}
		$parser = xml_parser_create('');

		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values)
			return; //Hmm...
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array ();
		foreach ($xml_values as $data) {
			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();
			if (isset ($value)) {
				if ($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value;
			}
			if (isset ($attributes) and $get_attributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
			if ($type == "open") {
				$parent[$level -1] = & $current;
				if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				} else {
					if (isset ($current[$tag][0])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array (
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			} elseif ($type == "complete") {
				if (!isset ($current[$tag])) {
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				} else {
					if (isset ($current[$tag][0]) and is_array($current[$tag])) {
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					} else {
						$current[$tag] = array (
							$current[$tag],
							$result
						);
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes) {
							if (isset ($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			} elseif ($type == 'close') {
				$current = & $parent[$level -1];
			}
		}
		return ($xml_array);
	}
}
