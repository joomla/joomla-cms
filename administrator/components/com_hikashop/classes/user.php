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
class hikashopUserClass extends hikashopClass {
	var $tables = array('user');
	var $pkeys = array('user_id');

	function get($id, $type = 'hikashop', $geoloc = false) {
		static $data = array();

		if($id === false) {
			$data = array();
			return true;
		}

		if(!empty($data[$type.'_'.$id]))
			return $data[$type.'_'.$id];

		$field = 'user_id';
		switch($type) {
			case 'hikashop':
				$field = 'user_id';
				$id = (int)$id;
				break;
			case 'email':
				$field = 'user_email';
				$id = $this->database->Quote(trim($id));
				break;
			case 'cms':
			default:
				$field = 'user_cms_id';
				$id = (int)$id;
				break;
		}

		$geo = '';
		$select = 'a.*,b.*';
		if($geoloc && hikashop_level(2)) {
			$geo = ' LEFT JOIN '.hikashop_table('geolocation').' AS c ON a.user_id=c.geolocation_ref_id AND c.geolocation_type=\'user\'';
			$select .= ',c.*';
		}

		$query = 'SELECT '.$select.' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users', false).' AS b ON a.user_cms_id = b.id ' . $geo . ' WHERE a.' . $field . ' = ' . $id;
		$this->database->setQuery($query);
		$user = $this->database->loadObject();

		if(!empty($user->user_params)) {
			$user->user_params = unserialize($user->user_params);
		} elseif(!empty($user)) {
			$user->user_params = new stdClass();
		}
		$data[$type.'_'.$id] = $user;

		return $data[$type.'_'.$id];
	}

	function getID($cms_id, $type = 'cms') {
		$user = $this->get($cms_id, $type);
		$id = (int)@$user->user_id;

		if(!empty($id) || $type != 'cms')
			return $id;

		$this->database->setQuery('SELECT * FROM '.hikashop_table('users',false).' WHERE id='.(int)$cms_id);
		$userData = $this->database->loadObject();

		if(!empty($userData)) {
			$user = new stdClass();
			$user->user_cms_id = $cms_id;
			$user->user_email = $userData->email;
			$id = $this->save($user);
		}
		return $id;
	}

	function save(&$element,$skipJoomla=false){
		$new = empty($element->user_id);
		if($new) {
			if(empty($element->user_created_ip))
				$element->user_created_ip = hikashop_getIP();

			if(empty($element->user_created))
				$element->user_created = time();

			if(empty($element->user_email) && !empty($element->user_cms_id)) {
				$user = JFactory::getUser($element->user_cms_id);
				$element->user_email = $user->email;
			} elseif(!empty($element->user_email)&&empty($element->user_cms_id)) {
			}
		}

		if(isset($element->user_currency_id)) {
			$app = JFactory::getApplication();
			$config =& hikashop_config();

			$user = $this->get($element->user_id);
			if(empty($user->user_currency_id))
				$user->user_currency_id = $config->get('partner_currency');

			$previousPartnerCurrency = $user->user_currency_id;

			if($app->isAdmin()) {
				if($element->user_currency_id == $config->get('partner_currency')) {
					$element->user_currency_id = 0;
				}
			} elseif($config->get('allow_currency_selection')) {
				$currencyClass = hikashop_get('class.currency');
				$currency = $currencyClass->get($element->user_currency_id);
				if(empty($currency->currency_published))
					unset($element->user_currency_id);
			} else {
				unset($element->user_currency_id);
			}
			if(!empty($element->user_currency_id))
				$element->user_currency_id = (int)$element->user_currency_id;
		}

		if(!empty($element->user_params))
			$element->user_params = serialize($element->user_params);

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if($new) {
			$dispatcher->trigger( 'onBeforeUserCreate', array( & $element, & $do) );
		} else {
			$dispatcher->trigger( 'onBeforeUserUpdate', array( & $element, & $do) );
		}

		if(!$do)
			return false;

		$element->user_id = parent::save($element);

		if(empty($element->user_id))
			return $element->user_id;

		if($new) {
			$dispatcher->trigger( 'onAfterUserCreate', array( & $element ) );
		} else {
			$dispatcher->trigger( 'onAfterUserUpdate', array( & $element ) );
		}

		if($element->user_id == hikashop_loadUser()) {
			hikashop_loadUser(null,true);
			$this->get(false);
		}

		if($new) {
			$plugin = JPluginHelper::getPlugin('system', 'hikashopgeolocation');
			if(!empty($plugin) && hikashop_level(2)){
				jimport('joomla.html.parameter');
				$params = new HikaParameter( $plugin->params );
				if(!empty($params)){
					if($params->get('user',1)){
						$geo = new stdClass();
						$geo->geolocation_ref_id = $element->user_id;
						$geo->geolocation_type = 'user';
						$geo->geolocation_ip = $element->user_created_ip;
						$class = hikashop_get('class.geolocation');
						$class->params =& $params;
						$class->save($geo);
					}
				}
			}
		} else {
			if(!$skipJoomla && !empty($element->user_email)){
				if(empty($element->user_cms_id)){
					$userData = $this->get($element->user_id);
					$element->user_cms_id = $userData->user_cms_id;
				}
				$user = JFactory::getUser($element->user_cms_id);
				if(!empty($user) && $element->user_email!=$user->email){
					$user->email = $element->user_email;
					$user->save();
				}
			}
			if(isset($element->user_currency_id)) {
				$config =& hikashop_config();

				if(empty($element->user_currency_id))
					$element->user_currency_id = $config->get('partner_currency');

				if($element->user_currency_id != $previousPartnerCurrency) {
					$currencyClass = hikashop_get('class.currency');

					$main_currency = (int)$config->get('main_currency', 1);
					$null = null;
					$ids = array(
						$previousPartnerCurrency => $previousPartnerCurrency,
						$element->user_currency_id => $element->user_currency_id,
						$main_currency => $main_currency
					);

					$currencies = $currencyClass->getCurrencies($ids, $null);

					$srcCurrency = $currencies[$previousPartnerCurrency];
					$dstCurrency = $currencies[$element->user_currency_id];
					$mainCurrency = $currencies[$main_currency];

					$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'click');
					$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'order');
					$this->_updatePartnerPrice($srcCurrency, $dstCurrency, $mainCurrency, $element, 'user');
				}
			}
		}
		return $element->user_id;
	}

	function _updatePartnerPrice(&$srcCurrency,&$dstCurrency,&$mainCurrency,&$element,$type='click'){
		$amount='';
		if($srcCurrency->currency_id!=$mainCurrency->currency_id){
			$amount='('.$type.'_partner_price/ ((1+ '.floatval($srcCurrency->currency_percent_fee).'/100)*'.floatval($srcCurrency->currency_rate).' )) ';
			if($dstCurrency->currency_id!=$mainCurrency->currency_id){
				$amount = '('.$amount.'*'.floatval($dstCurrency->currency_rate).')*(1+'.floatval($dstCurrency->currency_percent_fee).'/100)';
			}
		}elseif($dstCurrency->currency_id!=$mainCurrency->currency_id){
			$amount = '('.$type.'_partner_price *'.floatval($dstCurrency->currency_rate).')*(1+'.floatval($dstCurrency->currency_percent_fee).'/100)';
		}
		if(!empty($amount)){
			$amount = ','.$type.'_partner_price='.$amount;
		}
		$orCurrencyConfig = ($srcCurrency->currency_id == $mainCurrency->currency_id) ? ' OR '.$type.'_partner_currency_id=0' : '';

		$query = 'UPDATE '.hikashop_table($type).' SET '.$type.'_partner_currency_id='.$element->user_currency_id.$amount.' WHERE '.$type.'_partner_id='.$element->user_id.' AND '.$type.'_partner_paid=0 AND ('.$type.'_partner_currency_id='.$srcCurrency->currency_id.$orCurrencyConfig.')';
		$this->database->setQuery($query);
		$this->database->query();
	}

	function saveForm(){
		$oldUser = null;
		$user_id = hikashop_getCID('user_id');
		if($user_id){
			$oldUser = $this->get($user_id);
		}
		$fieldsClass = hikashop_get('class.field');
		$element = $fieldsClass->getInput('user',$oldUser);
		if(empty($element)){
			return false;
		}
		$element->user_id = $user_id;

		$status = $this->save($element);
		if($status){
			hikashop_loadUser(null,true);
			$this->get(false);
		}
		return $status;
	}

	function delete(&$elements,$fromCMS=false){
		$result = true;
		if(!empty($elements)){
			if(!is_array($elements)){
				$elements = array((int)$elements);
			}else{
				JArrayHelper::toInteger($elements);
			}
			JPluginHelper::importPlugin( 'hikashop' );
			$dispatcher = JDispatcher::getInstance();
			$do=true;
			$dispatcher->trigger( 'onBeforeUserDelete', array( & $elements, & $do) );
			if(!$do){
				return false;
			}
			foreach($elements as $el){
				$query = 'SELECT count(*) FROM '.hikashop_table('order').' WHERE order_user_id='.$el;
				$this->database->setQuery($query);
				$hasOrders = $this->database->loadResult();
				if(empty($hasOrders)){
					$result = parent::delete($el);
					if($result){
						$address = hikashop_get('class.address');
						$addresses = $address->loadUserAddresses($el);

						foreach($addresses as $id => $data){
							$address->delete($id);
						}
					}
				}else{
					$app = JFactory::getApplication();
					if($app->isAdmin()){
						$data = $this->get($el);
						$app->enqueueMessage('The user with the email address "'.$data->user_email.'" could not be deleted in HikaShop because he has orders attached to him. If you want to delete this user in HikaShop as well, you first need to delete his orders.');
						$result = false;
					}
					if($fromCMS){
						$query = 'UPDATE '.hikashop_table('user').' SET user_cms_id=0 WHERE user_id IN ('.implode(',',$elements).')';
						$this->database->setQuery($query);
						$result = $this->database->query();
					}
				}
			}
			if($result){
				$dispatcher->trigger( 'onAfterUserDelete', array( & $elements ) );
			}
		}

		return $result;
	}

	function loadPartnerData(&$user){
		$config=&hikashop_config();

		if(empty($user->user_params->user_custom_fee)){
			if(!isset($user->user_params) || !is_object($user->user_params)){
				if(is_null($user)) $user = new stdClass();
				$user->user_params = new stdClass();
			}
			$user->user_params->user_partner_click_fee = $config->get('partner_click_fee',0);
			$user->user_params->user_partner_lead_fee = $config->get('partner_lead_fee',0);
			$user->user_params->user_partner_percent_fee = $config->get('partner_percent_fee',0);
			$user->user_params->user_partner_flat_fee = $config->get('partner_flat_fee',0);
		}

		$user->accumulated=array();
		$db = JFactory::getDBO();
		if(!empty($user->user_partner_activated)){

			$minDelay = $config->get('affiliate_payment_delay',0);
			$maxTime = intval(time() - $minDelay);


			$user->accumulated['currentclicks']=$user->accumulated['clicks']=$user->accumulated['paidclicks']=0;
			if(bccomp($user->user_params->user_partner_click_fee,0,5)){
				$query='SELECT SUM(click_partner_price) AS clicks_total,click_partner_paid FROM '.hikashop_table('click').' WHERE click_partner_id='.$user->user_id.' GROUP BY click_partner_paid';
				$db->setQuery($query);
				$results = $db->loadObjectList('click_partner_paid');
				$user->accumulated['currentclicks']=$user->accumulated['clicks']=@$results[0]->clicks_total*1;
				$user->accumulated['paidclicks'] = @$results[1]->clicks_total*1;

				if(!empty($minDelay)){
					$query='SELECT SUM(click_partner_price) AS clicks_total FROM '.hikashop_table('click').' WHERE click_partner_id='.$user->user_id.' AND click_created < '.$maxTime.' AND click_partner_paid=0 GROUP BY click_partner_id';
					$db->setQuery($query);
					$user->accumulated['currentclicks']=$db->loadResult()*1;
				}

			}
			$user->accumulated['currentleads']=$user->accumulated['leads']=$user->accumulated['paidleads']=0;
			if(bccomp($user->user_params->user_partner_lead_fee,0,5)){
				$query='SELECT SUM(user_partner_price) AS leads_total,user_partner_paid FROM '.hikashop_table('user').' WHERE user_partner_id='.$user->user_id.' GROUP BY user_partner_paid';
				$db->setQuery($query);
				$results = $db->loadObjectList('user_partner_paid');
				$user->accumulated['currentleads']=$user->accumulated['leads']=@$results[0]->leads_total*1;
				$user->accumulated['paidleads'] = @$results[1]->leads_total*1;
				if(!empty($minDelay)){
					$query='SELECT SUM(user_partner_price) AS leads_total FROM '.hikashop_table('user').' WHERE user_partner_id='.$user->user_id.' AND user_created < '.$maxTime.' AND user_partner_paid=0 GROUP BY user_partner_id';
					$db->setQuery($query);
					$user->accumulated['currentleads']=$db->loadResult()*1;
				}
			}

			$user->accumulated['currentsales']=$user->accumulated['sales']=$user->accumulated['paidsales']=0;
			if(bccomp($user->user_params->user_partner_percent_fee,0,5) || bccomp($user->user_params->user_partner_flat_fee,0,5)){
				$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
				foreach($partner_valid_status_list as $k => $partner_valid_status){
					$partner_valid_status_list[$k]= $this->database->Quote($partner_valid_status);
				}
				$query='SELECT SUM(order_partner_price) AS sales_total, order_partner_paid FROM '.hikashop_table('order').' WHERE order_partner_id='.$user->user_id.' AND order_type=\'sale\' AND order_status IN ('.implode(',',$partner_valid_status_list).') GROUP BY order_partner_paid';
				$db->setQuery($query);
				$results = $db->loadObjectList('order_partner_paid');
				$user->accumulated['currentsales']=$user->accumulated['sales']=@$results[0]->sales_total*1;
				$user->accumulated['paidsales'] = @$results[1]->sales_total*1;
				if(!empty($minDelay)){
					$query='SELECT SUM(order_partner_price) AS sales_total FROM '.hikashop_table('order').' WHERE order_partner_id='.$user->user_id.' AND order_created < '.$maxTime.' AND order_type=\'sale\' AND order_partner_paid=0 AND order_status IN ('.implode(',',$partner_valid_status_list).') GROUP BY order_partner_id';
					$db->setQuery($query);
					$user->accumulated['currentsales']=$db->loadResult()*1;
				}

			}
			$user->accumulated['total'] = round($user->accumulated['sales'] + $user->accumulated['leads'] + $user->accumulated['clicks'],2);
			$user->accumulated['currenttotal'] = round($user->accumulated['currentsales'] + $user->accumulated['currentleads'] + $user->accumulated['currentclicks'],2);
			$user->accumulated['paidtotal'] = round($user->accumulated['paidsales'] + $user->accumulated['paidleads'] + $user->accumulated['paidclicks'],2);
		}

	}

	function register(&$checkout,$page='checkout',$redirect=true){
		$config =& hikashop_config();
		$app = JFactory::getApplication();

		$user 		= clone(JFactory::getUser());
		$authorize	= JFactory::getACL();
		$simplified = $config->get('simplified_registration',0);
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams( 'com_users' );

		$display = $config->get('display_method',0);
		if(!hikashop_level(1)) $display = 0;
		if($display==1) {
			$simplified = explode(',',$simplified);
			if($page=='checkout') {
				$formData = JRequest::getVar( 'data', array(), '', 'array' );
				if(in_array(@$formData['register']['registration_method'],$simplified)){
					$simplified = $formData['register']['registration_method'];
				}else{
					$simplified = array_shift($simplified);
				}
			} elseif($page=='user') {
				$simplified = array_shift($simplified);
			}
		}

		if($simplified!=2){
			if ($params->get('allowUserRegistration') == '0') {
				JError::raiseError( 403, JText::_( 'Access Forbidden' ));
				return false;
			}

			$newUsertype = $params->get( 'new_usertype' );
			if (!$newUsertype) {
				if(!HIKASHOP_J16){
					$newUsertype = 'Registered';
				}else{
					$newUsertype = 2;
				}
			}
			$userGroupRegistration = $config->get('user_group_registration', '');
			if(HIKASHOP_J16 && !empty($userGroupRegistration) && (int)$userGroupRegistration > 0) {
				$newUsertype = (int)$userGroupRegistration;
			}
		}

		$fieldClass = hikashop_get('class.field');
		$old = null;
		$this->registerData = $fieldClass->getInput('register',$old,!@$checkout->cart_update);
		$userData = $fieldClass->getInput('user',$old,!@$checkout->cart_update);

		if($config->get('address_on_registration',1)){
			$addressData = $fieldClass->getInput('address',$old,!@$checkout->cart_update);
		}else{
			$addressData = new stdClass();
		}


		if($this->registerData===false || $addressData===false || $userData===false){
			return false;
		}

		if(empty($this->registerData->name)){
			$this->registerData->name = @$addressData->address_firstname.(!empty($addressData->address_middle_name)?' '.$addressData->address_middle_name:'').(!empty($addressData->address_lastname)?' '.$addressData->address_lastname:'');
			if(empty($this->registerData->name) && !empty($this->registerData->email)){
				$parts = explode('@',$this->registerData->email);
				$this->registerData->name = array_shift($parts);
			}
		}

		if($simplified == 1){
			$this->registerData->username = $this->registerData->email;
			jimport('joomla.user.helper');
			$this->registerData->password = JUserHelper::genRandomPassword();
			$this->registerData->password2 = $this->registerData->password;
		}else if ($simplified == 3) {
			$this->registerData->username = $this->registerData->email;
		}

		if(!empty($this->registerData->username)){
			$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
			$this->registerData->username = $safeHtmlFilter->clean($this->registerData->username,'USERNAME');
		}

		if($simplified == 0 || $simplified ==3){
			if(empty($this->registerData->password)){
				$app->enqueueMessage( JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED') );
				return false;
			}
			$minimumLength = (int)$params->get('minimum_length');
			$minimumIntegers = (int)$params->get('minimum_integers');
			$minimumSymbols = (int)$params->get('minimum_symbols');
			$minimumUppercase = (int)$params->get('minimum_uppercase');
			$language = JFactory::getLanguage();
			$language->load('com_users', JPATH_SITE, $language->getTag(), true);
			if(!empty($minimumLength)){
				if(strlen((string) $this->registerData->password) < $minimumLength){
					$app->enqueueMessage( JText::plural('COM_USERS_MSG_PASSWORD_TOO_SHORT_N', $minimumLength), 'warning' );
					return false;
				}
			}
			if (!empty($minimumIntegers)) {
				$nInts = preg_match_all('/[0-9]/', $this->registerData->password, $imatch);
				if ($nInts < $minimumIntegers){
					$app->enqueueMessage(JText::plural('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N', $minimumIntegers),'warning');
					return false;
				}
			}
			if (!empty($minimumSymbols)) {
				$nsymbols = preg_match_all('[\W]', $this->registerData->password, $smatch);
				if ($nsymbols < $minimumSymbols) {
					$app->enqueueMessage( JText::plural('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols), 'warning');
					return false;
				}
			}
			if (!empty($minimumUppercase)){
				$nUppercase = preg_match_all("/[A-Z]/", $this->registerData->password, $umatch);
				if ($nUppercase < $minimumUppercase){
					$app->enqueueMessage( JText::plural('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase), 'warning' );
					return false;
				}
			}
		}

		$data = array(
			 'name' => @$this->registerData->name,
			 'username' => @$this->registerData->username,
			 'email' => @$this->registerData->email,
			 'password' => @$this->registerData->password,
			 'password2' => @$this->registerData->password2,
		);

		$_SESSION['hikashop_main_user_data'] = $data;
		if(!empty($addressData->address_vat)){
			$vat = hikashop_get('helper.vat');
			if(!$vat->isValid($addressData)){
				$app->enqueueMessage( JText::_('VAT_NUMBER_NOT_VALID') );
				return false;
			}
		}

		if($simplified!=2){

			if(HIKASHOP_J16){
				$data['groups']=array($newUsertype=>$newUsertype);
			}
			if(HIKASHOP_J25){
				$jconfig = JFactory::getConfig();
				if(HIKASHOP_J30){
					$locale = $jconfig->get('language');
				}else{
					$locale = $jconfig->getValue('config.language');
				}
				$data['params']=array('site_language'=>$locale,'language'=>$locale);
			}
			if(HIKASHOP_J25){
				$language = JFactory::getLanguage();
				$language->load('lib_joomla', JPATH_SITE);
			}

			if (!$user->bind( $data, 'usertype' )) {
				JError::raiseError( 500, $user->getError());
			}

			$user->set('id', 0);
			if(!HIKASHOP_J16){
				$user->set('usertype', $newUsertype);
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
			}
			$date = JFactory::getDate();
			if(HIKASHOP_J30){
				$user->set('registerDate', $date->toSql());
			}else{
				$user->set('registerDate', $date->toMySQL());
			}

			$useractivation = $params->get( 'useractivation' );
			if ($useractivation > 0){
				jimport('joomla.user.helper');
				if(HIKASHOP_J30){
					$user->set('activation', JApplication::getHash( JUserHelper::genRandomPassword()) );
				}else{
					$user->set('activation', JUtility::getHash( JUserHelper::genRandomPassword()) );
				}
				$user->set('block', '1');
			}

			if ( !$user->save() ){
				JError::raiseWarning('', JText::_( $user->getError()));
				return false;
			}
			$this->get(false);
			$newUser = $this->get($user->id,'cms');

		}

		if(!empty($newUser)){
			$userData->user_id = $newUser->user_id;
		}elseif(!empty($user->id)){
			$userData->user_cms_id = $user->id;
		}else{
			$userData->user_email = $this->registerData->email;
		}

		if($config->get('affiliate_registration',0)){
			if(JRequest::getInt('hikashop_affiliate_checkbox',0)){
				$userData->user_partner_activated=1;
				$this->registerData->user_partner_activated=1;
			}
		}

		if($simplified==2){
			$this->database->setQuery('SELECT * FROM '.hikashop_table('user').' WHERE user_email = '.$this->database->Quote($userData->user_email));
			$userInDB = $this->database->loadObject();
			if(@$userInDB->user_cms_id){
				JError::raiseWarning('', JText::_('EMAIL_ADDRESS_ALREADY_USED'));
				return false;
			}
			$this->user_id = 0;
			if(isset($userInDB->user_id)){
				$this->user_id = $userInDB->user_id;
			}
			if($this->user_id){
				$userInDB->user_created_ip = hikashop_getIP();
				$this->save($userInDB);
			}
			else{
				$this->user_id = $this->save($userData);
			}

			$query = 'UPDATE '.hikashop_table('address').' AS a SET a.address_published=0 WHERE a.address_user_id='.(int)$this->user_id.' and a.address_published=1';
			$this->database->setQuery($query);
			$this->database->query();

			$cartClass = hikashop_get('class.cart');
			$cart = $cartClass->loadCart();
			if(!is_object($cart)) $cart = new stdClass();
			$cart->user_id = $this->user_id;
			$cartClass->save($cart);
		}else{
			$this->user_id = $this->save($userData);
		}

		if($config->get('address_on_registration',1)){
			if(isset($addressData->address_id)){
				unset($addressData->address_id);
			}
			$this->registerData->user_id = $this->user_id;
			if(!empty($addressData)){
				$addressData->address_user_id = $this->user_id;
				$addressClass = hikashop_get('class.address');
				$this->address_id = $addressClass->save($addressData);
			}
		}

		if($simplified!=2){
			$mailClass = hikashop_get('class.mail');
			$this->registerData->user_data =& $userData;
			$this->registerData->address_data =& $addressData;
			$this->registerData->password = preg_replace('/[\x00-\x1F\x7F]/', '', @$this->registerData->password); //Disallow control chars in the email
			$this->registerData->active = $useractivation;
			$vars = urlencode(base64_encode(json_encode(array('passwd'=>$this->registerData->password,'username'=>$this->registerData->username))));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}

			$this->registerData->activation_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=activate&activation='.$user->get('activation').'&infos='.$vars.'&page='.$page.'&id='.$this->user_id.$url.'&lang='.@$_GET['lang'];
			$this->registerData->partner_url= HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=affiliate&task=show'.$url;
			$mail = $mailClass->get('user_account',$this->registerData);
			if(!empty($this->registerData->email)){
				$mail->subject = JText::sprintf($mail->subject,@$this->registerData->name,HIKASHOP_LIVE);
				$mail->dst_email =& $this->registerData->email;
				if(!empty($this->registerData->name)){
					$mail->dst_name =& $this->registerData->name;
				}else{
					$mail->dst_name = '';
				}
				$mailClass->sendMail($mail);
				$mailSent = $mailClass->mail_success;

				if($params->get( 'mail_to_admin', '0')){
					$mail = $mailClass->get('user_account_admin_notification',$this->registerData);
					$mail->subject = JText::sprintf($mail->subject,@$this->registerData->name,HIKASHOP_LIVE);
					if(empty($mail->dst_email)) $mail->dst_email = explode(',',$config->get('from_email'));
					$mailClass->sendMail($mail);
				}
			}
			if ($useractivation > 0 && $redirect){
				$lang = JFactory::getLanguage();
				$lang->load('com_user',JPATH_SITE);
				if($mailSent){
					$message  = JText::_( 'HIKA_REG_COMPLETE_ACTIVATE' );
					$app->enqueueMessage($message);
					if($page=='checkout'){
						$message  = JText::_( 'WHEN_CLICKING_ACTIVATION' );
						$app->enqueueMessage($message);
					}
				}else{
					$message  = JText::_( 'HIKA_MAIL_ISSUE_ACTIVATION' );
					$app->enqueueMessage($message);
				}
				$locale = strtolower(substr($lang->get('tag'),0,2));
				$app->redirect(hikashop_completeLink('checkout&task=activate_page&lang='.$locale,false,true));
			}elseif(file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')){
				$newUser = $this->get($this->user_id);
				$this->addAndConfirmUserInCB($newUser,$addressData);
			}

		}

		return true;
	}

	function addAndConfirmUserInCB($newUser, $addressData = null) {
		if(is_null($addressData)) {
			$addressClass = hikashop_get('class.address');
			$addresses = $addressClass->getByUser($newUser->user_id);
			$addressData = reset($addresses);
		}

		$fields = array(
			'cbactivation' => $this->database->Quote(''),
			'id' => (int)$newUser->user_cms_id,
			'user_id' => (int)$newUser->user_cms_id,
			'approved' => 1,
			'confirmed' => 1
		);

		if(!empty($addressData->address_firstname))
			$fields['firstname'] = $this->database->Quote($addressData->address_firstname);

		if(!empty($addressData->address_middle_name))
			$fields['middlename'] = $this->database->Quote($addressData->address_middle_name);

		if(!empty($addressData->address_lastname))
			$fields['lastname'] = $this->database->Quote($addressData->address_lastname);

		$query = 'REPLACE #__comprofiler (' . implode(',', array_keys($fields)) . ') VALUES (' . implode(',', $fields) . ')';
		$this->database->setQuery($query);
		$this->database->query();
	}
}
