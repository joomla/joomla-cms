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
class CheckoutViewCheckout extends hikashopView{
	var $ctrl= 'checkout';
	var $nameListing = 'CHECKOUT';
	var $nameForm = 'CHECKOUT';
	var $icon = 'checkout';
	var $extraFields=array();
	var $requiredFields = array();
	var $validMessages = array();
	var $triggerView = true;

	function display($tpl = null, $params = array()) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		jimport('joomla.html.parameter');
		$params = new HikaParameter('');
		$this->assignRef('params',$params);
		$conf =& hikashop_config();
		$checkout = trim($conf->get('checkout','login_address_shipping_payment_confirm_coupon_cart_status,end'));
		$this->steps = explode(',',$checkout);

		if(method_exists($this,$function)) $this->$function();

		if(JRequest::getInt('popup') && empty($_COOKIE['popup']) && JRequest::getVar('tmpl')!='component'){
			$class = hikashop_get('helper.cart');
			$this->init();
			$class->getJS($this->params->get('url'));
			$doc = JFactory::getDocument();
			$js = '
			window.hikashop.ready( function() {
				SqueezeBox.fromElement(\'hikashop_notice_box_trigger_link\',{parse: \'rel\'});
			});
			';
			$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
		}
		$this->assignRef('config',$conf);
		parent::display($tpl);
	}

	function notice() {
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)) {
			$url_itemid='&Itemid='.$Itemid;
		}
		jimport('joomla.html.parameter');

		$class = hikashop_get('helper.cart');
		$this->assignRef('url_itemid',$url_itemid);
		$this->assignRef('cartClass',$class);
		$config =& hikashop_config();
		$this->assignRef('config',$config);

		JRequest::setVar('cart_type',JRequest::getVar('cart_type',''));
	}

	function step() {
		$module = hikashop_get('helper.module');
		$module->initialize($this);

		$config =& hikashop_config();
		$this->display_checkout_bar = $config->get('display_checkout_bar',2);
		$this->continueShopping = $config->get('continue_shopping');

		$step = JRequest::getInt('step',0);
		if(!isset($this->steps[$step])) {
			$step=0;
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();

		$display = trim($this->steps[$step]);
		$layouts = explode('_',$display);
		foreach($layouts as $layout) {
			$layout = trim($layout);
			if(method_exists($this, $layout)) {
				$this->$layout();
			} else {
				$dispatcher->trigger('onInitCheckoutStep', array($layout, &$this));
			}
		}
		$this->assignRef('steps',$this->steps);
		$this->assignRef('step',$step);
		$this->assignRef('layouts',$layouts);

		$js='
function isSelected(radiovar){
	if(radiovar.checked){
		return true;
	}
	for(var a=0; a < radiovar.length; a++){
		if(radiovar[a].checked && radiovar[a].value.length>0) return true;
	}
	return false;
}

function hikashopCheckMethods() {
	var varform =  document["hikashop_checkout_form"];

	if(typeof varform.elements["hikashop_payment"] != "undefined" && !isSelected(varform.elements[\'hikashop_payment\'])) {
		alert("'. JText::_('SELECT_PAYMENT',true).'");
		return false;
	}

	if(typeof varform.elements["hikashop_shippings"] != "undefined") {
		var shippings = varform.elements["hikashop_shippings"];
		if(shippings) {
			shippings = shippings.value.split(";");
			if(shippings.length > 1) {
				for(var i = 0; i < shippings.length; i++) {
					if(!varform.elements["hikashop_shipping_" + shippings[i] ] || !isSelected(varform.elements["hikashop_shipping_" + shippings[i] ])) {
						alert("'. JText::_('SELECT_SHIPPING',true).'");
						return false;
					}
				}
			}else{
				if(typeof varform.elements["hikashop_shipping"] != "undefined" && !isSelected(varform.elements[\'hikashop_shipping\'])) {
					alert("'. JText::_('SELECT_SHIPPING',true).'");
					return false;
				}
			}
		}
	}

	return true;
}';

		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
		$this->assignRef('doc', $doc);

		$app = JFactory::getApplication();
		$this->assignRef('app', $app);

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid)) {
			$url_itemid = '&Itemid='.$Itemid;
		}
		$this->assignRef('url_itemid', $url_itemid);

		$this->assignRef('continueShopping',$this->continueShopping);
		$this->assignRef('display_checkout_bar',$this->display_checkout_bar);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);

		hikashop_setPageTitle('CHECKOUT');
	}

	function cartstatus(){
		return $this->cart();
	}

	function cart() {
		$cart = $this->initCart();
		if(!empty($cart->total->prices[0]->price_currency_id)&& $cart->total->prices[0]->price_currency_id!=hikashop_getCurrency()){
			$app = JFactory::getApplication();
			$app->enqueueMessage( JText::_('CURRENCY_NOT_ACCEPTED_FOR_PAYMENT'));
		}

		$cartClass = hikashop_get('class.cart');
		$paymentType = $cartClass->checkSubscription($cart);
		$this->assignRef('paymentType',$paymentType);

		$cartClass = hikashop_get('helper.cart');
		$cartClass->cartCount(true);

		$this->assignRef('coupon',$cart->coupon);
		$this->assignRef('shipping',$cart->shipping);
		$this->assignRef('payment', $cart->payment);

		$this->assignRef('additional',$cart->additional);

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = hikashop_config();
		$this->assignRef('total',$cart->total);
		$this->assignRef('rows',$cart->products);
		$cart=hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$this->init();
		$cart->cartCount(1);
		$cart->cartCount(1);
		$cart->getJS($this->params->get('url'));
		$config =& hikashop_config();
		$this->params->set('show_delete',$config->get('checkout_cart_delete',1));
		$show_image = $config->get('show_cart_image');
		$this->params->set('show_cart_image',$show_image);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$image = hikashop_get('helper.image');
		$this->assignRef('image',$image);
		if(hikashop_level(2)){
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$null = null;
			$this->extraFields['item'] = $fieldsClass->getFields('frontcomp',$null,'item');
			$this->assignRef('extraFields',$this->extraFields);
		}
	}

	function &initCart() {
		static $done = false;
		if(!$done){
			$cartClass = hikashop_get('class.cart');
			$done = $cartClass->loadFullCart(true);
			$app = JFactory::getApplication();
			if(empty($done)){
				$config =& hikashop_config();
				$redirect_url = $config->get('redirect_url_when_cart_is_empty');
				if(!preg_match('#^https?://#',$redirect_url)) $redirect_url = JURI::base().ltrim($redirect_url,'/');
				$app->redirect( JRoute::_($redirect_url,false), JText::_('CART_EMPTY'));
				return true;
			}
			$shippingClass = hikashop_get('class.shipping');
			$usable_rates =& $shippingClass->getShippings($done);
			if(empty($usable_rates) && empty($shippingClass->errors)){
				$shipping = false;
			}else{
				$shipping = true;

				if(is_array($usable_rates) && count($usable_rates)){
					$method = reset($usable_rates);
					$config =& hikashop_config();
					$auto_select_default = $config->get('auto_select_default',2);
					if($auto_select_default == 1 && count($usable_rates) > 1)
						$auto_select_default = 0;
					$shipping_id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id', null);
					if(empty($shipping_id) && $auto_select_default){
						$shipping_groups = $done->shipping_groups;
						$shipping_methods = array();
						$shipping_ids = array();
						$rates = array();
						foreach($shipping_groups as $key => $shipping_group) {
							$p = reset($shipping_group->shippings);
							foreach($usable_rates as $rate) {
								if($rate->shipping_id == $p && (!isset($rate->shipping_warehouse_id) || $rate->shipping_warehouse_id === $key)) {
									$rates[] = $rate;
									$shipping_ids[] = $rate->shipping_id.'@'.$key;
									$shipping_methods[] = $rate->shipping_type.'@'.$key;

									break;
								}
							}
						}
						$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', $shipping_methods);
						$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', $shipping_ids);
						$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', $rates);
						$done = $cartClass->loadFullCart(true);
					}
				}
			}
			$config =& hikashop_config();
			$this->params->set('price_with_tax',$config->get('price_with_tax'));
			$this->has_shipping = $shipping || $config->get('force_shipping');
			$this->assignRef('has_shipping',$this->has_shipping);
			$this->assignRef('full_total',$done->full_total);
			$this->assignRef('full_cart',$done);
		}
		return $done;
	}

	function init() {
		$url = $this->params->get('url');
		if(empty($url)){
			$url = hikashop_currentURL();
		}
		$this->params->set('url',urlencode($url));
	}

	function coupon() {
		$cart = $this->initCart();

		$js="
function hikashopCheckCoupon(id){
	var el = document.getElementById(id);
	if(el){
		if(el.value==''){
			el.className = 'hikashop_red_border';
			return false;
		}
		el.form.submit();
	}
	return false;
}";
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( "\n<!--\n".$js."\n//-->\n" );
		$coupon_error_message = JRequest::getVar('coupon_error_message','');
		if(!empty($coupon_error_message)){
			$app = JFactory::getApplication();
			$app->enqueueMessage( $coupon_error_message ,'notice');
		}
		if(isset($cart->coupon)) $this->assignRef('coupon',$cart->coupon);
	}

	function login(){
		$mainUser = JFactory::getUser();
		if(empty($mainUser->id)){
			$data = @$_SESSION['hikashop_main_user_data'];
			if(!empty($data)){
				foreach($data as $key => $val){
					$mainUser->$key = $val;
				}
			}
		}

		$this->assignRef('mainUser',$mainUser);
		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		$user_id = hikashop_loadUser();
		$identified = $user_id ? true : false;
		$this->assignRef('identified',$identified);

		JHTML::_('behavior.formvalidation');

		$user = @$_SESSION['hikashop_user_data'];
		$address = @$_SESSION['hikashop_address_data'];
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;

		$this->extraFields['user'] = $fieldsClass->getFields('frontcomp',$user,'user');
		$this->extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address');
		$this->assignRef('extraFields',$this->extraFields);
		$this->assignRef('user',$user);
		$this->assignRef('address',$address);

		$config =& hikashop_config();
		$simplified_reg = $config->get('simplified_registration',1);
		$this->assignRef('simplified_registration',$simplified_reg);
		$display_method = $config->get('display_method', 0);
		if(!hikashop_level(1)) $display_method = 0;
		$this->assignRef('display_method',$display_method);

		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['user'],$user,0);
		$fieldsClass->jsToggle($this->extraFields['address'],$address,0);

		$values = array('address'=>$address,'user'=>$user);
		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);

		$main = array('email');
		$main = array('name','username','email','password','password2');
		if($simplified_reg){
			$main = array('email');
		}
		foreach($main as $field){
			$this->requiredFields['register'][] = $field;
			$this->validMessages['register'][] = addslashes(JText::sprintf('FIELD_VALID',$fieldsClass->trans($field)));
		}
		$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('register','user','address'));

		$js = '
function displayRegistration(el)
{
	if(!el) return;
	var value = el.value,
	checked = el.checked,
	name = document.getElementById("hikashop_registration_name_line"),
	username = document.getElementById("hikashop_registration_username_line"),
	pwd = document.getElementById("hikashop_registration_password_line"),
	pwd2 = document.getElementById("hikashop_registration_password2_line"),
	registration_div = document.getElementById("hikashop_checkout_registration"),
	login_div = document.getElementById("hikashop_checkout_login_form");

	if(value=="login" && checked==true) {
		if(login_div) login_div.className="";
		if(registration_div) registration_div.className="hikashop_hidden_checkout";
	} else if((value==0 || value==1 || value==3) && checked==true) {
		if(login_div) login_div.className="hikashop_hidden_checkout";
		if(registration_div) registration_div.className="";
		document.getElementById("hika_registration_type").innerHTML="'.JText::_('HIKA_REGISTRATION',true).'";
		document.getElementById("hikashop_register_form_button").value="'.JText::_('HIKA_REGISTER',true).'";
		if(value==0)
		{
			if(name) name.style.display="";
			if(username) username.style.display="";
			if(pwd) pwd.style.display="";
			if(pwd2) pwd2.style.display="";
		} else if(value==1) {
			if(name) name.style.display="none";
			if(username) username.style.display="none";
			if(pwd) pwd.style.display="none";
			if(pwd2) pwd2.style.display="none";
		} else if(value==3) {
			if(pwd) pwd.style.display="";
			if(pwd2) pwd2.style.display="";
		}
	} else if(value==2 && checked==true) {
		if(login_div) login_div.className="hikashop_hidden_checkout";
		if(registration_div) registration_div.className="";
		document.getElementById("hika_registration_type").innerHTML="'.JText::_('GUEST',true).'";
		document.getElementById("hikashop_register_form_button").value="'.JText::_('HIKA_NEXT',true).'";

		if(name) name.style.display="none";
		if(username) username.style.display="none";
		if(pwd) pwd.style.display="none";
		if(pwd2) pwd2.style.display="none";
	}
}
';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	function activate(){

	}

	function activate_page(){

	}

	function fields(){
		if(hikashop_level(2)){
			JHTML::_('behavior.formvalidation');

			$app = JFactory::getApplication();
			$order = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_fields',null);

			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$cart = $this->initCart();
			$order->products =& $cart->products;
			$this->extraFields['order'] = $fieldsClass->getFields('frontcomp',$order,'order');
			$this->assignRef('extraFields',$this->extraFields);

			$null=array();
			$fieldsClass->addJS($null,$null,$null);
			$fieldsClass->jsToggle($this->extraFields['order'],$order,0);

			$this->assignRef('order',$order);

			$values = array('order'=>$order);
			$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);
			$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('order'));
		}
	}

	function state(){
		$database	= JFactory::getDBO();
		$namekey = JRequest::getCmd('namekey','');
		if(!headers_sent()){
			header('Content-Type:text/html; charset=utf-8');
		}
		if(!empty($namekey)){
			$field_namekey = JRequest::getString('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = JRequest::getString('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = JRequest::getString('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$class = hikashop_get('type.country');
			$query = 'SELECT * FROM '.hikashop_table('field').' WHERE field_namekey = '.$database->Quote($field_namekey);
			$database->setQuery($query,0,1);
			$field = $database->loadObject();
			echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type,'', $field->field_options);
		} else {
			echo '<span class="state_no_country">'.JText::_('PLEASE_SELECT_COUNTRY_FIRST').'</span>';
		}
		exit;
	}

	function address(){

		$app = JFactory::getApplication();
		$addresses = array();
		$fields = null;
		$user_id = hikashop_loadUser();

		if($user_id){
			$class = hikashop_get('class.address');
			$addresses = $class->loadUserAddresses($user_id);
			if(!empty($addresses)){
				$addressClass = hikashop_get('class.address');
				$addressClass->loadZone($addresses);
				$fields =& $addressClass->fields;
			}
		}

		$cart = $this->initCart();
		if(!$this->has_shipping) {
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_address', null);
		}
		$this->assignRef('fields',$fields);
		$this->assignRef('addresses',$addresses);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$identified = (bool)$user_id;
		$this->assignRef('identified',$identified);
		JHTML::_('behavior.modal');

		$billing_address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address' );

		if(empty($billing_address) && count($addresses)) {
			$address = reset($addresses);
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address->address_id );
			$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address->address_id );
			$class = hikashop_get('class.cart');
			$class->loadAddress($cart,$address->address_id);
			$cart->billing_address =& $cart->shipping_address;
		}

		$shipping_address = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address' );
		$billing_address = $app->getUserState( HIKASHOP_COMPONENT.'.billing_address' );

		$this->assignRef('shipping_address',$shipping_address);
		$this->assignRef('billing_address',$billing_address);

		$shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
		if(empty($shipping_method))
			$shipping_method = array();
		if(!is_array($shipping_method))
			$shipping_method = array($shipping_method);

		$currentShipping = array();
		if(count($shipping_method)==1){
			foreach($shipping_method as $method){
				$method = explode('@',$method);
				$method = $method[0];
				$currentShipping[] = hikashop_import('hikashopshipping', $method);
			}
		}
		$this->assignRef('currentShipping', $currentShipping);

		$js = "
function hikashopEditAddress(obj,val,new_address){
	var same_address = document.getElementById('same_address');
	if(val && same_address && (new_address && same_address.checked || !new_address && !same_address.checked)){
		var nextChar = '?';
		if(obj.href.indexOf('?')!='-1'){ nextChar='&'; }
		obj.href+=nextChar+'makenew=1';
	}
	window.hikashop.openBox(obj,obj.href);
	return false;
}
function hikashopSameAddress(value){
	var shipdiv = document.getElementById('hikashop_checkout_shipping_div');
	if(shipdiv){
		if(!value){
			shipdiv.style.display='';
		}else{
			shipdiv.style.display='none';
		}
	}
	return true;
}";
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	function shipping() {
		$app = JFactory::getApplication();

		$order =& $this->initCart();
		$shippingClass = hikashop_get('class.shipping');
		$usable_rates =& $shippingClass->getShippings($order);

		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);

		$config =& hikashop_config();
		$this->params->set('price_with_tax',$config->get('price_with_tax'));

		if($this->params->get('show_original_price','-1') == '-1') {
			$defaultParams = $config->get('default_params');
			$this->params->set('show_original_price',$defaultParams['show_original_price']);
		}
		if(empty($usable_rates)) {
			$user_id = hikashop_loadUser(false);
			if(!empty($user_id) && !$shippingClass->displayErrors() && $this->has_shipping) {
				$app->enqueueMessage(JText::_('NO_SHIPPING_METHOD_FOUND'));
			}
		} else {

			$shipping_groups = $shippingClass->getShippingGroups($order, $usable_rates);
			$this->assignRef('shipping_groups', $shipping_groups);

			$currencyClass = hikashop_get('class.currency');

			$currencyClass->processShippings($usable_rates,$order);

			$shipping_method = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_method');
			$shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');

			$config =& hikashop_config();
			$auto_select_default = $config->get('auto_select_default', 2);
			if($auto_select_default == 1 && count($usable_rates) > 1)
				$auto_select_default = 0;

			if($auto_select_default && empty($shipping_id) && count($usable_rates)) {
				$rates = array();
				$shipping_id = array();
				$shipping_method = array();
				foreach($shipping_groups as $key => $shipping_group) {
					foreach($usable_rates as $rate) {
						if(in_array($rate->shipping_id, $shipping_group->shippings)) {
							$rates[] = $rate;
							$shipping_id[] = $rate->shipping_id.'@'.$key;
							$shipping_method[] = $rate->shipping_type.'@'.$key;

							break;
						}
					}
				}

				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', $rates);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', $shipping_id);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', $shipping_method);

				$order->shipping = $rates;

				$currencyClass->processShippings($order->shipping,$order);

				$order->full_total =& $currencyClass->addShipping($order->shipping, $order->full_total);
			}

			if(empty($shipping_id))
				$shipping_id = array();
			if(!is_array($shipping_id))
				$shipping_id = array($shipping_id);

			if(empty($shipping_method))
				$shipping_method = array();
			if(!is_array($shipping_method))
				$shipping_method = array($shipping_method);

			$this->assignRef('shipping_messages', $shippingClass->errors);
			$this->assignRef('currencyHelper', $currencyClass);
			$this->assignRef('rates', $usable_rates);
			$this->assignRef('orderInfos', $order);
			$this->assignRef('shipping_method', $shipping_method);
			$this->assignRef('shipping_id', $shipping_id);
		}

		$this->_getImagesName('shipping');
	}

	function payment(){

		$order = $this->initCart();

		$this->assignRef('orderInfos',$order);
		if(!isset($order->full_total->prices[0]->price_value_with_tax) || bccomp($order->full_total->prices[0]->price_value_with_tax,0,5)==0){
			return true;
		}

		$class = hikashop_get('class.payment');
		$usable_methods = $class->getPayments($order);
		$app = JFactory::getApplication();
		$payment_method=$app->getUserState( HIKASHOP_COMPONENT.'.payment_method' );
		$payment_id=$app->getUserState( HIKASHOP_COMPONENT.'.payment_id' );

		$this->assignRef('methods',$usable_methods);
		$this->assignRef('payment_method',$payment_method);
		$this->assignRef('payment_id',$payment_id);
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
		$js = "
function moveOnMax(field,nextFieldID){
	if(field.value.length >= field.maxLength){
		document.getElementById(nextFieldID).focus();
	}
}
window.hikashop.ready( function(){
";
		$done=false;
		if(empty($usable_methods)) {
			if(count($class->errors)) {
				foreach($class->errors as $error){
					if(!empty($error)) $app->enqueueMessage($error);
				}
			}
		}else{
			$config =& hikashop_config();
			$auto_select_default = $config->get('auto_select_default',2);
			if($auto_select_default == 0) $done = true;
			foreach($usable_methods as $method){
				$show = false;
				if(($payment_method==$method->payment_type && $payment_id==$method->payment_id)|| (empty($payment_id)&&!$done)){
					$done = true;
					$show = true;
				}
				$js.="
	var mySlide_".$method->payment_type.'_'.$method->payment_id." = new Fx.Slide('hikashop_credit_card_".$method->payment_type.'_'.$method->payment_id."');
";
				if(!$show){
					$js.="
	mySlide_".$method->payment_type.'_'.$method->payment_id.".hide();
	var hikashop_last_opened_slide = null;
";
				}else{
					$js.="
	var hikashop_last_opened_slide = mySlide_".$method->payment_type.'_'.$method->payment_id.";
";
				}
				$js.="
	if(typeof document.id == 'function' ){
		document.id('radio_".$method->payment_type.'_'.$method->payment_id."').addEvent('click', function(e){
			if(hikashop_last_opened_slide) {
				if(mySlide_".$method->payment_type.'_'.$method->payment_id." == hikashop_last_opened_slide)
					return;
				hikashop_last_opened_slide.toggle();
			}
			mySlide_".$method->payment_type.'_'.$method->payment_id.".toggle();
			hikashop_last_opened_slide = mySlide_".$method->payment_type.'_'.$method->payment_id.";
		});
	}else{
		$('radio_".$method->payment_type.'_'.$method->payment_id."').addEvent('click', function(e){
			if(typeof(hikashop_last_opened_slide)!='undefined') hikashop_last_opened_slide.toggle();
			mySlide_".$method->payment_type.'_'.$method->payment_id.".toggle();
			hikashop_last_opened_slide = mySlide_".$method->payment_type.'_'.$method->payment_id.";
		});
	}";
			}
		}
		$js.="
});

var ccHikaErrors = new Array();
ccHikaErrors [3] = '".JText::_('CREDIT_CARD_INVALID')."';
ccHikaErrors [5] = '".JText::_('CREDIT_CARD_EXPIRED')."';
";
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
		JHTML::_('behavior.tooltip');
		$this->_getImagesName('payment');

		$currencyClass = hikashop_get('class.currency');
		$currencyClass->processPayments($this->methods);
		$this->assignRef('currencyHelper',$currencyClass);
	}

	function _getImagesName($type){
		$images_folder = HIKASHOP_MEDIA .'images'.DS.$type.DS;
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($images_folder);
		$images = array();
		if(!empty($files)){
			foreach($files as $file){
				$parts = explode('.',$file);
				array_pop($parts);
				$name = implode('.',$parts);
				$images[$name] = $file;
			}
		}
		$this->assignRef('images_'.$type,$images);
	}

	function confirm(){
	}

	function after_end(){
		$order_id = JRequest::getInt('order_id');
		if(empty($order_id)){
			$app = JFactory::getApplication();
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		$order =null;
		if(!empty($order_id)){
			$orderClass = hikashop_get('class.order');
			$order = $orderClass->loadFullOrder($order_id,false,false);
		}

		$this->assignRef('order',$order);
	}

	function status(){
		$app = JFactory::getApplication();

		$shipping_method = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_method');
		$shipping_id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id');
		$shipping_data = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_data');
		$payment_method = $app->getUserState( HIKASHOP_COMPONENT.'.payment_method');
		$payment_id = $app->getUserState( HIKASHOP_COMPONENT.'.payment_id');
		$payment_data = $app->getUserState( HIKASHOP_COMPONENT.'.payment_data');
		$address = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');

		if(empty($shipping_id))
			$shipping_id = array();
		if(!is_array($shipping_id))
			$shipping_id = array($shipping_id);

		if(empty($shipping_method))
			$shipping_method = array();
		if(!is_array($shipping_method))
			$shipping_method = array($shipping_method);

		if(empty($shipping_data))
			$shipping_data = array();
		if(!is_array($shipping_data))
			$shipping_data = array($shipping_data);

		$this->assignRef('payment_method', $payment_method);
		$this->assignRef('payment_id', $payment_id);
		$this->assignRef('payment_data', $payment_data);
		$this->assignRef('shipping_method', $shipping_method);
		$this->assignRef('shipping_id', $shipping_id);
		$this->assignRef('shipping_data', $shipping_data);
	}

	function terms(){
		$app = JFactory::getApplication();
		$terms = $app->getUserState( HIKASHOP_COMPONENT.'.checkout_terms' );
		if($terms){
			$terms = 'checked="checked"';
		}else{
			$terms = '';
		}
		$this->assignRef('terms_checked',$terms);
	}

	function end(){
		$html = JRequest::getVar('hikashop_plugins_html','','default','string',JREQUEST_ALLOWRAW);
		$this->assignRef('html',$html);
		$noform = JRequest::getVar('noform',1,'default','int');
		$this->assignRef('noform',$noform);
	}

	function printcart(){
		$this->cart();
		$this->status();
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
	}

	function ccinfo(){
		$app = JFactory::getApplication();

		$payment_method=$app->getUserState( HIKASHOP_COMPONENT.'.payment_method' );
		$payment_id=$app->getUserState( HIKASHOP_COMPONENT.'.payment_id' );
		$payment_data=$app->getUserState( HIKASHOP_COMPONENT.'.payment_data' );

		$display_form = true;

		$cart = hikashop_get('helper.cart');

		$this->assignRef('cart',$cart);
		$this->assignRef('display_form',$display_form);
		$this->assignRef('method',$payment_data);
		$this->assignRef('payment_method',$payment_method);
		$this->assignRef('payment_id',$payment_id);
		$this->assignRef('payment_data',$payment_data);

		$js = "
function moveOnMax(field,nextFieldID){
	if(field.value.length >= field.maxLength){
		document.getElementById(nextFieldID).focus();
	}
}";
		$js .= "
var ccHikaErrors = new Array ();
ccHikaErrors [3] = '".JText::_('CREDIT_CARD_INVALID')."';
ccHikaErrors [5] = '".JText::_('CREDIT_CARD_EXPIRED')."';
				";
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
		JHTML::_('behavior.tooltip');
	}
}
