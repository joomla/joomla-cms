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

defined('_JEXEC') or die('Restricted access');

class plgHikashopshippingCANPAR extends hikashopShippingPlugin {
	var $canpar_methods = array(
		array('key' => '1', 'code'=>'1', 'name' => 'Ground', 'countries' => 'CANADA'),
		array('key' => '2', 'code'=>'2','name' => 'USA', 'countries' => 'USA'),
		array('key' => '3', 'code'=>'3','name' => 'Select Letter', 'countries' => 'CANADA'),
		array('key' => '4', 'code'=>'4','name' => 'Select Pak', 'countries' => 'CANADA'),
		array('key' => '5', 'code'=>'5','name' => 'Select Parcel', 'countries' => 'CANADA'),
		array('key' => '6', 'code'=>'C','name' => 'Express Letter', 'countries' => 'CANADA'),
		array('key' => '7', 'code'=>'D','name' => 'Express Pak', 'countries' => 'CANADA'),
		array('key' => '8', 'code'=>'E','name' => 'Express Parcel', 'countries' => 'CANADA'),
		array('key' => '9', 'code'=>'F','name' => 'USA Select Letter', 'countries' => 'USA'),
		array('key' => '10', 'code'=>'G','name' => 'USA Select Pak', 'countries' => 'USA'),
		array('key' => '11', 'code'=>'H','name' => 'USA Select Parcel', 'countries' => 'USA')
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
	public $package_added = 0;

	var $multiple = true;
	var $name = 'canpar';
	var $doc_form = 'canpar';

	function shippingMethods(&$main) {
		$methods = array();
		if (!empty($main -> shipping_params -> methodsList)) {
			$main -> shipping_params -> methods = unserialize($main -> shipping_params -> methodsList);
		}
		if (!empty($main -> shipping_params -> methods)) {
			foreach ($main->shipping_params->methods as $key => $value) {
				$selected = null;
				foreach ($this->canpar_methods as $canpar) {
					if ($canpar['name'] == $key)
						$selected = $canpar;
				}
				if ($selected) {
					$methods[$main->shipping_id . '-' . $selected['key']] = $selected['name'];
				}
			}
		}
		return $methods;
	}

	function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
		if(!hikashop_loadUser())
			return false;
		$local_usable_rates = array();
		$local_messages = array();
		$ret = parent::onShippingDisplay($order, $dbrates, $local_usable_rates, $local_messages);

		if($ret === false)
			return false;

		$currentShippingZone = null;
		$currentCurrencyId = null;
		$found = true;
		$usableWarehouses = array();
		$zoneClass = hikashop_get('class.zone');
		$zones = $zoneClass -> getOrderZones($order);

		foreach ($local_usable_rates as $k =>$rate) {

			if (!empty($rate -> shipping_params -> warehousesList)) {
				$rate -> shipping_params -> warehouses = unserialize($rate -> shipping_params -> warehousesList);
			} else {
				$messages['no_warehouse_configured'] = 'No warehouse configured in the CANPAR shipping plugin options';
				continue;
			}

			foreach ($rate->shipping_params->warehouses as $warehouse) {
				if (empty($warehouse -> zone) || $warehouse -> zone == '-' || in_array($warehouse -> zone, $zones)) {
					$usableWarehouses[] = $warehouse;
				}
			}
			if (empty($usableWarehouses)) {
				$messages['no_warehouse_configured'] = 'No available warehouse found for your location';
				continue;
			}
			if (!empty($rate -> shipping_params -> methodsList)) {
				$rate -> shipping_params -> methods = unserialize($rate -> shipping_params -> methodsList);
			} else {
				$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the CANPAR shipping plugin options';
				continue;
			}
			if ($order -> weight <= 0 || $order -> volume <= 0) {
				return true;
			}

			$data = null;
			if (empty($order -> shipping_address)) {
				$messages['no_shipping_address_found'] = 'No shipping address entered';
				continue;
			}

			$this -> shipping_currency_id = hikashop_getCurrency();
			$db = JFactory::getDBO();
			$query = 'SELECT currency_code FROM ' . hikashop_table('currency') . ' WHERE currency_id IN (' . $this -> shipping_currency_id . ')';
			$db -> setQuery($query);
			$this -> shipping_currency_code = $db -> loadResult();
			$cart = hikashop_get('class.cart');
			$null = null;
			$cart -> loadAddress($null, $order -> shipping_address -> address_id, 'object', 'shipping');
			$currency = hikashop_get('class.currency');

			$receivedMethods = $this -> _getBestMethods($rate, $order, $usableWarehouses, $null);

			if (empty($receivedMethods)) {
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
				continue;
			}
			$i = 0;
			$local_usable_rates = array();
			foreach ($receivedMethods as $method) {
				$local_usable_rates[$i] = (!HIKASHOP_PHP5) ? $rate : clone($rate);
				$local_usable_rates[$i] -> shipping_price += $method['value'];
				$selected_method = '';
				$name = '';
				foreach ($this->canpar_methods as $canpar_method) {
					if ($canpar_method['name'] == $method['name']) {
						$name = $canpar_method['name'];
						$selected_method = $canpar_method['key'];
					}
				}
				$local_usable_rates[$i]->shipping_name = $name;
				if(!empty($selected_method))
					$local_usable_rates[$i]->shipping_id .= '-' . $selected_method;

				if ($method['deliveryDate'] != 'www.canpar.ca') {
					if (is_numeric($method['deliveryDate'])) {
						$timestamp = strtotime($method['deliveryDate']);
						$time =  parent::displayDelaySECtoDAY($timestamp - strtotime('now'), 2);
						$local_usable_rates[$i] -> shipping_description .= 'Estimated delivery date:  ' . $time;
					} else {
						$time = $method['deliveryDate'];
						$local_usable_rates[$i] -> shipping_description .= 'Estimated delivery date:  ' . $time;
					}

				} else {
					$local_usable_rates[$i] -> shipping_description .= ' ' . JText::_('NO_ESTIMATED_TIME_AFTER_SEND');
				}
				if ($rate -> shipping_params -> group_package == 1 && $this -> nbpackage > 1)
					$local_usable_rates[$i] -> shipping_description .= '<br/>' . JText::sprintf('X_PACKAGES', $this -> nbpackage);
				$i++;
			}
			foreach ($local_usable_rates as $i => $rate) {
				$usable_rates[$rate->shipping_id] = $rate;
			}
		}
	}

	function getShippingDefaultValues(&$element){
		$element -> shipping_name = 'CANPAR';
		$element -> shipping_description = '';
		$element -> group_package = 0;
		$element -> shipping_images = 'canpar';
		$element -> shipping_params -> post_code = '';
		$element -> shipping_currency_id = $this -> main_currency;
	}

	function onShippingConfiguration(&$element) {
		$config = &hikashop_config();
		$this -> main_currency = $config -> get('main_currency', 1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_get('class.currency');
		$this -> currencyCode = $currency -> get($this -> main_currency)->currency_code;
		$this -> currencySymbol = $currency -> get($this -> main_currency)->currency_symbol;

		$this -> canpar = JRequest::getCmd('name', 'canpar');
		$this -> categoryType = hikashop_get('type.categorysub');
		$this -> categoryType -> type = 'tax';
		$this -> categoryType -> field = 'category_id';

		parent::onShippingConfiguration($element);
		$elements = array($element);
		$key = key($elements);
		if (!empty($elements[$key] -> shipping_params -> warehousesList)) {
			$elements[$key] -> shipping_params -> warehouse = unserialize($elements[$key] -> shipping_params -> warehousesList);
		}
		if (!empty($elements[$key] -> shipping_params -> methodsList)) {
			$elements[$key] -> shipping_params -> methods = unserialize($elements[$key] -> shipping_params -> methodsList);
		}
		$js = '
function deleteRow(divName,inputName,rowName){
	var d = document.getElementById(divName);
	var olddiv = document.getElementById(inputName);
	if(d && olddiv){
		d.removeChild(olddiv);
		document.getElementById(rowName).style.display=\'none\';
	}
	return false;
}
function deleteZone(zoneName){
	var d = document.getElementById(zoneName);
	if(d){
		d.innerHTML="";
	}
	return false;
}
';
	 	$js.= "
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
}
";

		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->\n" );
	}

	function onShippingConfigurationSave(&$element) {
		$warehouses = JRequest::getVar('warehouse', array(), '', 'array');
		$cats = array();
		$methods = array();
		$db = JFactory::getDBO();
		$zone_keys = '';

		if (isset($_REQUEST['data']['shipping_methods'])) {
			foreach ($_REQUEST['data']['shipping_methods'] as $method) {
				foreach ($this->canpar_methods as $canparMethod) {
					$name = $canparMethod['name'];
					if ($name == $method['name']) {
						$obj = new stdClass();
						$methods[strip_tags($method['name'])] = '';
					}
				}
			}
		}

		$element -> shipping_params -> methodsList = serialize($methods);

		if (!empty($warehouses)) {
			foreach ($warehouses as $id => $warehouse) {
				if (!empty($warehouse['zone']))
					$zone_keys .= 'zone_namekey=' . $db -> Quote($warehouse['zone']) . ' OR ';
			}
			$zone_keys = substr($zone_keys, 0, -4);
			if (!empty($zone_keys)) {
				$query = ' SELECT zone_namekey, zone_id, zone_name_english FROM ' . hikashop_table('zone') . ' WHERE ' . $zone_keys;
				$db -> setQuery($query);
				$zones = $db -> loadObjectList();
			}
			foreach ($warehouses as $id => $warehouse) {
				$warehouse['zone_name'] = '';
				if (!empty($zones)) {
					foreach ($zones as $zone) {
						if ($zone -> zone_namekey == $warehouse['zone'])
							$warehouse['zone_name'] = $zone -> zone_id . ' ' . $zone -> zone_name_english;
					}
				}
				if (empty($_REQUEST['warehouse'][$id]['zip'])) {
					$_REQUEST['warehouse'][$id]['zip'] = '-';
				}
				if (@$_REQUEST['warehouse'][$id]['zip'] != '-') {
					$obj = new stdClass();
					$obj -> name = strip_tags($_REQUEST['warehouse'][$id]['name']);
					$obj -> zip = strip_tags($_REQUEST['warehouse'][$id]['zip']);
					$obj -> zone = @strip_tags($_REQUEST['warehouse'][$id]['zone']);
					$obj -> zone_name = $warehouse['zone_name'];
					$obj -> units = strip_tags($_REQUEST['warehouse'][$id]['units']);
					$cats[] = $obj;
				}
			}
			$element -> shipping_params -> warehousesList = serialize($cats);
		}
		if (empty($cats)) {
			$obj = new stdClass();
			$obj -> name = '-';
			$obj -> zip = '-';
			$obj -> zone = '-';
			$void[] = $obj;
			$element -> shipping_params -> warehousesList = serialize($void);
		}
		return true;
	}

	function _getBestMethods(&$rate, &$order, &$usableWarehouses, $null) {
		$db = JFactory::getDBO();
		$usableMethods = array();
		$query = 'SELECT zone_id, zone_code_2 FROM ' . hikashop_table('zone') . ' WHERE zone_id = 38';
		$db -> setQuery($query);
		$warehouses_namekey = $db -> loadObjectList();

		foreach ($usableWarehouses as $warehouse) {
			foreach ($warehouses_namekey as $zone) {
				if ($zone -> zone_id == 38) {
					$warehouse -> country_ID = $zone -> zone_code_2;
				}
			}
		}
		foreach ($usableWarehouses as $k => $warehouse) {
			$usableWarehouses[$k] -> methods = $this -> _getShippingMethods($rate, $order, $warehouse, $null);
		}

		if (empty($usableWarehouses)) {
			return false;
		}
		$method_available = '';

		foreach ($usableWarehouses as $k => $warehouse) {
			if (!empty($warehouse -> methods)) {
				$j = 0;
				foreach ($rate->shipping_params->methods as $shipping_method => $empty) {
					$method_available[$j] = $shipping_method;
					$j++;
				}
				foreach ($warehouse->methods as $i => $method) {
					if (!in_array($method['name'], $method_available))
						unset($usableWarehouses[$k] -> methods[$i]);
				}
			}
		}
		$bestPrice = 99999999;

		foreach ($usableWarehouses as $id => $warehouse) {
			if (!empty($warehouse -> methods)) {
				foreach ($warehouse->methods as $method) {
					if ($method['value'] < $bestPrice) {
						$bestPrice = $method['value'];
						$bestWarehouse = $id;
					}
				}
			}
		}

		if (isset($bestWarehouse)) {
			return $usableWarehouses[$bestWarehouse] -> methods;
		} else {
			return false;
		}
	}


	function _getShippingMethods(&$rate, &$order, &$warehouse, $null) {
		$data = array();
		$data['destCity'] = $null -> shipping_address -> address_city;
		$data['destState'] = $null -> shipping_address -> address_state;
		$data['destZip'] = $null -> shipping_address -> address_post_code;
		$data['destCountry'] = $null -> shipping_address -> address_country -> zone_code_2;
		$data['units'] = $warehouse -> units;
		$data['zip'] = $warehouse -> zip;

		$totalPrice = 0;

		if (!$rate -> shipping_params -> group_package || $rate -> shipping_params -> group_package == 0) {

			$data['weight'] = 0;
			$data['height'] = 0;
			$data['length'] = 0;
			$data['width'] = 0;
			$data['price'] = 0;
			$data['quantity'] = 0;
			$data['name'] = '';

			foreach ($order->products as $product) {
				if ($product -> product_parent_id == 0) {
					if (isset($product -> variants)) {

						foreach ($product->variants as $variant) {
							$data['units'] = 'kg';
							$caracs = parent::_convertCharacteristics($variant, $data);
							$data['weight'] = round($caracs['weight'], 2) * $variant -> cart_product_quantity;
							$data['height'] = round($caracs['height'], 2) * $variant -> cart_product_quantity;
							$data['length'] = round($caracs['length'], 2) * $variant -> cart_product_quantity;
							$data['width'] = round($caracs['width'], 2) * $variant -> cart_product_quantity;
							$data['price'] = $variant -> prices[0] -> unit_price -> price_value_with_tax * $variant -> cart_product_quantity;
							$data['name'] = $variant -> main_product_name . ' :' . $variant -> characteristics_text;
							$data['quantity'] = $variant -> cart_product_quantity;

							$data['XMLpackage'][] = $this -> _createPackage($data, $product, $rate, $order);
						}
					} else {
						$data['units'] = 'kg';
						$caracs = parent::_convertCharacteristics($product, $data);
						$data['weight'] = round($caracs['weight'], 2) * $product -> cart_product_quantity;
						$data['height'] = round($caracs['height'], 2) * $product -> cart_product_quantity;
						$data['length'] = round($caracs['length'], 2) * $product -> cart_product_quantity;
						$data['width'] = round($caracs['width'], 2) * $product -> cart_product_quantity;
						$data['price'] = $product -> prices[0] -> price_value_with_tax * $product -> cart_product_quantity;
						$data['name'] = $product -> product_name;
						$data['quantity'] = $product -> cart_product_quantity;

						$data['XMLpackage'][] = $this -> _createPackage($data, $product, $rate, $order);

					}
				}
			}

			$usableMethods = $this -> _RequestMethods($data, $data['XMLpackage']);

			return $usableMethods;

		} else {
			$data['weight'] = 0;
			$data['height'] = 0;
			$data['length'] = 0;
			$data['width'] = 0;
			$data['price'] = 0;
			$data['quantity'] = 1;
			$data['name'] = 'grouped package';
			$this -> package_added = 0;
			$this -> nbpackage = 0;
			$limitation = array('length' => 50, 'weight' => 10, 'width' => 50, 'height' => 50);
			if (!empty($rate -> shipping_params -> dim_approximation_l)) {
				$l_length = $rate -> shipping_params -> dim_approximation_l;
				$l_weight = $rate -> shipping_params -> dim_approximation_kg;
				$l_height = $rate -> shipping_params -> dim_approximation_h;
				$l_width = $rate -> shipping_params -> dim_approximation_w;
			}
			if ($l_length > 0 && $limitation['length'] > $l_length) {
				$limitation['length'] = $l_length;
			}
			if ($l_weight > 0 && $limitation['weight'] > $l_weight) {
				$limitation['weight'] = $l_weight;
			}
			if ($l_height > 0 && $limitation['height'] > $l_height) {
				$limitation['height'] = $l_height;
			}
			if ($l_width > 0 && $limitation['width'] > $l_width) {
				$limitation['width'] = $l_width;
			}
		}

		foreach ($order->products as $product) {
			if ($product -> product_parent_id != 0)
				continue;

			if (isset($product -> variants)) {
				foreach ($product->variants as $variant) {
					for ($i = 0; $i < $variant -> cart_product_quantity; $i++) {
						$data['units'] = 'kg';
						$caracs = parent::_convertCharacteristics($variant, $data);
						$current_package = parent::groupPackages($data, $caracs);
						if ($data['weight'] + round($caracs['weight'], 2) > $limitation['weight'] || $current_package['tmpWidth'] > $limitation['length'] || $current_package['tmpHeight'] > $limitation['height'] || $current_package['tmpLength'] > $limitation['width']) {
							if ($this -> package_added == 0)
								$this -> nbpackage++;
							$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
							$data['weight'] = round($caracs['weight'], 2);
							$data['height'] = $current_package['y'];
							$data['length'] = $current_package['z'];
							$data['width'] = $current_package['x'];
							$data['price'] = $variant -> prices[0] -> unit_price -> price_value_with_tax;
						} else {
							$data['weight'] += round($caracs['weight'], 2);
							$data['height'] = max($data['height'], $current_package['y']);
							$data['length'] = max($data['length'], $current_package['z']);
							$data['width'] += $current_package['x'];
							$data['price'] += $variant -> prices[0] -> unit_price -> price_value_with_tax;

						}
					}
				}
			} else {
				for ($i = 0; $i < $product -> cart_product_quantity; $i++) {
					$data['units'] = 'kg';
					$caracs = parent::_convertCharacteristics($product, $data);
					$current_package = parent::groupPackages($data, $caracs);
					if ($data['weight'] + round($caracs['weight'], 2) > $limitation['weight'] || $current_package['tmpWidth'] > $limitation['length'] || $current_package['tmpHeight'] > $limitation['height'] || $current_package['tmpLength'] > $limitation['width']) {
						if ($this -> package_added == 0)
							$this -> nbpackage++;
						$data['XMLpackage'] .= $this -> _createPackage($data, $product, $rate, $order);
						$data['weight'] = round($caracs['weight'], 2);
						$data['height'] = $current_package['y'];
						$data['length'] = $current_package['z'];
						$data['width'] = $current_package['x'];
						$data['price'] = $product -> prices[0] -> price_value_with_tax;
					} else {
						$data['weight'] += round($caracs['weight'], 2);
						$data['height'] = max($data['height'], $current_package['y']);
						$data['length'] = max($data['length'], $current_package['z']);
						$data['width'] += $current_package['x'];
						$data['price'] += $product -> prices[0] -> price_value_with_tax;
					}
				}
			}
		}
		if (($data['weight'] + $data['height'] + $data['length'] + $data['width']) > 0 && $this -> package_added == 0) {
			$this -> package_added = 1;
			$this -> nbpackage++;

			$data['XMLpackage'][] = $this -> _createPackage($data, $product, $rate, $order);
		}

		$usableMethods = $this -> _RequestMethods($data, $data['XMLpackage']);

		return $usableMethods;
	}

	function _createPackage(&$data, &$product, &$rate, &$order) {
		if (empty($data['weight']) && !empty($product -> product_weight_orig)) {
			$data['weight'] = round($product -> product_weight_orig, 2);
			$data['height'] = round($product -> product_height, 2);
			$data['length'] = round($product -> product_length, 2);
			$data['width'] = round($product -> product_width, 2);
		}

		if (isset($data['price'])) {
			$price = $data['price'];
		} else {
			$price = $product -> prices[0] -> unit_price -> price_value;
		}
		if (!empty($rate -> shipping_params -> weight_approximation)) {
			$data['weight'] = $data['weight'] + $data['weight'] * $rate -> shipping_params -> weight_approximation / 100;
		}
		if ($data['weight'] < 1) {
			$data['weight'] = 1;
		}
		if (!empty($rate -> shipping_params -> dim_approximation)) {
			$data['height'] = $data['height'] + $data['height'] * $rate -> shipping_params -> dim_approximation / 100;
			$data['length'] = $data['length'] + $data['length'] * $rate -> shipping_params -> dim_approximation / 100;
			$data['width'] = $data['width'] + $data['width'] * $rate -> shipping_params -> dim_approximation / 100;
		}

		$xml = array(
			'quantity'=>$data['quantity'],
			'weight'=>$data['weight'],
			'length'=>$data['width'],
			'width'=>$data['length'],
			'height'=>$data['height'],
			'description'=>$data['name'],
			'city'=> $data['destCity'],
			'provOrState'=> $data['destState'],
			'country'=> $data['destCountry'],
			'postalCode'=>$data['destZip']
		);

		return $xml;
	}


	function _RequestMethods($data, $packages) {
		$app = JFactory::getApplication();


		$total_quantity = $total_weight = 0;
		foreach($packages as $package){
			if($package['weight']== 0 && $package['length']==0 && $package['width']==0 && $package['height']==0)
				continue;

			$total_quantity = $total_quantity + $package['quantity'];
			$total_weight = $total_weight + $package['weight'];
		}

		$srcFSA = substr(strtoupper($data['zip']), 0, 7);
		$desFSA = substr(strtoupper($data['destZip']), 0, 7);
		$srcFSA = str_replace (" ", "", $srcFSA );
		$desFSA = str_replace (" ", "", $desFSA);

		$srcFSA1stLetter = substr(strtoupper($data['zip']), 0, 1);
		$desFSA1stLetter = substr(strtoupper($data['destZip']), 0, 1);

		$unit = ($data['units']=='lb')?'L':'K';


		$xtra_care = 0;


		$total_count = $total_quantity;
		$shipping_weight = round($total_weight);

		$PkgWT = $shipping_weight;

		if( is_numeric( $desFSA1stLetter ) ) {
			$service_typename = 'USA';
			$request = join('&', array('service=2', 'quantity=' . $total_count, 'unit=' . $unit, 'origin=' . $srcFSA, 'dest=' . $desFSA, 'cod=0', 'weight=' . intval($shipping_weight), 'put=0', 'xc=' . $xtra_care, 'dec=0'));
		} else {
			$service_typename = 'Ground';
			$request = join('&', array('service=1','quantity=' . $total_count , 'unit=' . $unit, 'origin=' . $srcFSA, 'dest=' . $desFSA, 'cod=0', 'weight=' . $PkgWT, 'put=0', 'xc=' . $xtra_care, 'dec=0'));
		}


		$xml_feedback = file_get_contents('http://www.canpar.com/XML/BaseRateXML.jsp?' . $request);

		$xml_parser = xml_parser_create();
		$vals = null;
		$index = null;
		xml_parse_into_struct($xml_parser, $xml_feedback, $vals, $index);
		xml_parser_free($xml_parser);


		$params = array();
		$level = array();
		$shipmentNumber = 0;
		$errorNumber = 0;
		foreach ($vals as $xml_elem) {
			if ($xml_elem['type'] == 'open') {
				if (array_key_exists('attributes', $xml_elem)) {
					list($level[$xml_elem['level']], $extra) = array_values($xml_elem['attributes']);
					if ($xml_elem['tag'] == 'PRODUCT') {
						$shipmentNumber++;
						$xml_elem['tag'] = $xml_elem['tag'] . '_' . $shipmentNumber;
						$level[$xml_elem['level']] = $xml_elem['tag'];
					}
				} else {
					$level[$xml_elem['level']] = $xml_elem['tag'];
				}
			}
			if ($xml_elem['type'] == 'complete') {
				if ($xml_elem['tag'] == 'CANPARRATEERROR') {
					$stat = (int)trim($xml_elem['value']);
					if ($stat != 1) {
						$errorNumber++;
					}
				}
				if (empty($xml_elem['value'])) {
					$xml_elem['value'] = -1;
				}
				$start_level = 1;
				$php_stmt = '$params';
				while ($start_level < $xml_elem['level']) {
					$php_stmt .= '[$level[' . $start_level . ']]';
					$start_level++;
				}
				$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
				eval($php_stmt);
			}
		}

		$params = $params['http://www.w3.org/2001/XMLSchema-instance'];

		if (empty($params['CANPARRATEERRORS']['CANPARRATEERROR'])) {
			$shipment = array();
			$rate = $params['CANPARCHARGES']['BASERATE'];
			if(!empty($params['CANPARCHARGES']['CODCHARGE']))
				$rate += $params['CANPARCHARGES']['CODCHARGE'];
			if(!empty($params['CANPARCHARGES']['EXTRACARECHARGE']))
				$rate += $params['CANPARCHARGES']['EXTRACARECHARGE'];
			$shipment[] = array(
				'value'=>$rate,
				'name'=> $service_typename,
				'shippingDate' => $params['CANPARSHIPMENT']['SHIPPINGDATE'],
				'deliveryDate' => @$params['ESTIMATEDDELIVERYDATE'],
				'deliveryDayOfWeek'=>'CANPAR SHIPPING COST',
				'nextDayAM'=>'CANPAR SHIPPING COST',
				'status_code'=>'1',
				'status_message'=>'CANPAR SHIPPING',
			);
			return $shipment;
		} else {
			$app -> enqueueMessage('Message: '.$params['CANPARRATEERRORS']['CANPARRATEERROR']);
		}

	}
}
