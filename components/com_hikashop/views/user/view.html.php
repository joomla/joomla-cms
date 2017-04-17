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
class userViewUser extends HikaShopView {

	var $extraFields=array();
	var $requiredFields = array();
	var	$validMessages = array();

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function after_register(){

	}

	function cpanel(){
		$config =& hikashop_config();
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$buttons = array();
		$buttons['address'] = array('link'=>hikashop_completeLink('address'.$url_itemid),'level'=>0,'image'=>'address','text'=>JText::_('ADDRESSES'),'description'=>'<ul><li>'.JText::_('MANAGE_ADDRESSES').'</li></ul>');
		$buttons['order'] = array('link'=>hikashop_completeLink('order'.$url_itemid),'level'=>0,'image'=>'order','text'=>JText::_('ORDERS'),'description'=>'<ul><li>'.JText::_('VIEW_ORDERS').'</li></ul>');
		if(hikashop_level(1)){
			if($config->get('enable_multicart'))
				$buttons['cart'] = array('link'=>hikashop_completeLink('cart&task=showcarts&cart_type=cart'.$url_itemid),'level'=>0,'image'=>'cart','text'=>JText::_('CARTS'),'description'=>'<ul><li>'.JText::_('DISPLAY_THE_CARTS').'</li></ul>');
			else
				$buttons['cart'] = array('link'=>hikashop_completeLink('cart&task=showcart&cart_type=cart'.$url_itemid),'level'=>0,'image'=>'cart','text'=>JText::_('CARTS'),'description'=>'<ul><li>'.JText::_('DISPLAY_THE_CART').'</li></ul>');
			if($config->get('enable_wishlist')){
				if($config->get('enable_multicart'))
					$buttons['wishlist'] = array('link'=>hikashop_completeLink('cart&task=showcarts&cart_type=wishlist'.$url_itemid),'level'=>0,'image'=>'wishlist','text'=>JText::_('WISHLISTS'),'description'=>'<ul><li>'.JText::_('DISPLAY_THE_WISHLISTS').'</li></ul>');
				else
					$buttons['wishlist'] = array('link'=>hikashop_completeLink('cart&task=showcart&cart_type=wishlist'.$url_itemid),'level'=>0,'image'=>'wishlist','text'=>JText::_('WISHLISTS'),'description'=>'<ul><li>'.JText::_('DISPLAY_THE_WISHLIST').'</li></ul>');
			}
			if($config->get('enable_customer_downloadlist'))
			$buttons['download'] = array('link'=>hikashop_completeLink('user&task=downloads'.$url_itemid),'level'=>0,'image'=>'downloads','text'=>JText::_('DOWNLOADS'),'description'=>'<ul><li>'.JText::_('DISPLAY_THE_DOWNLOADS').'</li></ul>');
		}
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashoppayment');
		JPluginHelper::importPlugin('hikashopshipping');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onUserAccountDisplay', array(&$buttons));

		$this->assignRef('buttons',$buttons);
		if(!HIKASHOP_PHP5) {
			$app =& JFactory::getApplication();
			$pathway =& $app->getPathway();
		} else {
			$app = JFactory::getApplication();
			$pathway = $app->getPathway();
		}
		$items = $pathway->getPathway();
		if(!count($items))
			$pathway->addItem(JText::_('CUSTOMER_ACCOUNT'),hikashop_completeLink('user'));

		hikashop_setPageTitle('CUSTOMER_ACCOUNT');
	}

	function form(){
		$this->registration();

		hikashop_setPageTitle('HIKA_REGISTRATION');
	}

	function registration(){

		$js ='
		function hikashopSubmitForm(form, action)
		{
			var button = document.getElementById(\'login_view_action\'),
				 currentForm = document.forms[form];
			if(!currentForm)
				return false;

			if (form=="hikashop_checkout_form")
			{
				if(action && action == "login") {
					hikashopSubmitFormLog(form,button,currentForm);
					return false;
				}
				if(action && action == "register") {
					hikashopSubmitFormRegister(form,button,currentForm);
					return false;
				}

				var registrationMethod = currentForm.elements["data[register][registration_method]"];
				if (registrationMethod)
				{
					if (registrationMethod[0].id == "data[register][registration_method]login" && registrationMethod[0].checked)
						hikashopSubmitFormLog(form,button,currentForm);
					else
						hikashopSubmitFormRegister(form,button,currentForm);
				}
				else
				{
					var usernameValue = "", passwdValue = "", d = document, el = null;
					el = d.getElementById("username");
					if(el) usernameValue = el.value;

					el = d.getElementById("passwd");
					if(el) passwdValue = el.value;

					var registeremailValue = "", registeremailconfValue = "", firstnameValue = "", lastnameValue = "";
					el = d.getElementById("register_email");
					if(el) registeremailValue = el.value;
					el = d.getElementById("register_email_confirm");
					if(el) registeremailconfValue = el.value;

					el = d.getElementById("address_firstname");
					if(el) firstnameValue = el.value;
					el = d.getElementById("address_lastname");
					if(el) lastnameValue = el.value;

					if (usernameValue != "" && passwdValue != "") {
						hikashopSubmitFormLog(form,button,currentForm);
					} else if ((usernameValue != "" ||  passwdValue != "") && (registeremailValue == "" && registeremailconfValue == "" && firstnameValue == "" && lastnameValue == "")) {
						hikashopSubmitFormLog(form,button,currentForm);
					} else {
						hikashopSubmitFormRegister(form,button,currentForm);
					}
				}
			} else if(form == "hikashop_registration_form") {
				hikashopSubmitFormRegister(form,button,currentForm);
			}
			return false;
		}

		function hikashopSubmitFormRegister(form,button,currentForm)
		{
			if ( hikashopCheckChangeForm("register",form) && hikashopCheckChangeForm("user",form) && hikashopCheckChangeForm("address",form) )
			{
				if(button)
					button.value="register";
				currentForm.submit();
			}

		}

		function hikashopSubmitFormLog(form,button,currentForm)
		{
			if(button)
				button.value="login";
			currentForm.submit();
		}

		var hkKeyPress = function(e){
			var keyCode = (window.event) ? e.which : e.keyCode;
			if (keyCode != 13)
				return true;

			if (e.srcElement)  elem = e.srcElement;
			else if (e.target) elem = e.target;

			if( elem.name == "username" || elem.name == "passwd" ){
				var button = document.getElementById("login_view_action"),
				currentForm = document.forms["hikashop_checkout_form"];
				if(currentForm && button){
					hikashopSubmitFormLog("hikashop_checkout_form",button,currentForm);
					if (e.stopPropagation) {
						e.stopPropagation();
						e.preventDefault();
					}
					e.returnValue = false;
					return false;
				}
			}else{
			}
			return true;
		}
		if(document.addEventListener)
			document.addEventListener("keypress", hkKeyPress);
		else
			document.attachEvent("onkeypress", hkKeyPress);
		';

		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$mainUser = JFactory::getUser();
		$data = @$_SESSION['hikashop_main_user_data'];
		if(!empty($data)){
			foreach($data as $key => $val){
				$mainUser->$key = $val;
			}
		}

		$this->assignRef('mainUser',$mainUser);
		$lang = JFactory::getLanguage();
		$lang->load('com_user',JPATH_SITE);
		$user_id = hikashop_loadUser();

		JHTML::_('behavior.formvalidation');

		$user = @$_SESSION['hikashop_user_data'];
		$address = @$_SESSION['hikashop_address_data'];
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->skipAddressName=true;

		$extraFields['user'] = $fieldsClass->getFields('frontcomp',$user,'user');

		$config =& hikashop_config();

		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('user',$user);

		$simplified_reg = $config->get('simplified_registration',1);
		$this->assignRef('config',$config);
		$this->assignRef('simplified_registration',$simplified_reg);
		$display_method = $config->get('display_method',0);
		if(!hikashop_level(1)) $display_method = 0;
		$this->assignRef('display_method',$display_method);

		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['user'],$user,0);

		$values = array('user'=>$user);

		if($config->get('address_on_registration',1)){
			$extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address');
			$this->assignRef('address',$address);
			$fieldsClass->jsToggle($this->extraFields['address'],$address,0);
			$values['address']=$address;
		}


		$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);

		$main = array('name','username', 'email','password','password2');
		if($simplified_reg && $simplified_reg !=3 && $simplified_reg!=0){
			$main = array('email');
		}
		else if ($simplified_reg == 3) {
			$main = array('email','password','password2');
		}

		if($config->get('show_email_confirmation_field'))
		{
			$i=0;
			foreach ($main as $k)
			{
				$i++;
				if ($k=='email')
					array_splice( $main, $i, 0, 'email_confirm' );

			}
		}

		foreach($main as $field){
			$this->requiredFields['register'][] = $field;
			if($field=='name')$field = 'HIKA_USER_NAME';
			if($field=='username')$field = 'HIKA_USERNAME';
			if($field=='email')$field = 'HIKA_EMAIL';
			if($field=='email_confirm')$field = 'HIKA_EMAIL_CONFIRM';
			if($field=='password')$field = 'HIKA_PASSWORD';
			if($field=='password2')$field = 'HIKA_VERIFY_PASSWORD';
			$this->validMessages['register'][] = addslashes(JText::sprintf('FIELD_VALID',$fieldsClass->trans($field)));
		}

		$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('register','user','address'));
		jimport('joomla.html.parameter');
		$params=new HikaParameter('');
		$class = hikashop_get('helper.cart');
		$this->assignRef('url_itemid',$url_itemid);
		$this->assignRef('params',$params);
		$this->assignRef('cartClass',$class);

		$affiliate = $config->get( 'affiliate_registration_default',0);
		if($affiliate){
			$affiliate = 'checked="checked"';
		}else{
			$affiliate = '';
		}
		$this->assignRef('affiliate_checked',$affiliate);
	}

	function downloads() {
		$user = hikashop_loadUser(true);
		if(hikashop_loadUser() == null)
			return false;

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$order_statuses = explode(',', $config->get('order_status_for_download', 'shipped,confirmed'));
		foreach($order_statuses as $k => $o) {
			$order_statuses[$k] = $db->Quote( trim($o) );
		}
		$download_time_limit = $config->get('download_time_limit',0);
		$this->assignRef('download_time_limit', $download_time_limit);

		$paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.'.filter_order', 'filter_order', 'max_order_created', 'cmd');
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest($paramBase.'.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.'.search', 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$oldValue = $app->getUserState($paramBase.'.list_limit');
		$searchMap = array(
			'op.order_product_name',
			'f.file_name'
		);
		$order = '';
		if(!empty($pageInfo->filter->order->value)) {
			if($pageInfo->filter->order->value == 'f.file_name')
				$order = ' ORDER BY f.file_name '.$pageInfo->filter->order->dir.', f.file_path '.$pageInfo->filter->order->dir;
			else
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$filters = array(
			'o.order_type = \'sale\'',
			'o.order_status IN ('.implode(',', $order_statuses).')',
			'f.file_ref_id > 0',
			'f.file_type = \'file\'',
			'o.order_user_id = ' . $user->user_id,
		);
		if(!empty($pageInfo->search)) {
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = '('.implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal.')';
			$filters[] =  $filter;
		}
		$filters = implode(' AND ',$filters);

		if(empty($oldValue)) {
			$oldValue = $app->getCfg('list_limit');
		}

		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if($oldValue!=$pageInfo->limit->value) {
			$pageInfo->limit->start = 0;
			$app->setUserState($paramBase.'.limitstart',0);
		}

		$select = 'o.order_id, o.order_created, p.*, f.*, op.* ';
		$selectSum = ', MIN(o.order_created) as min_order_created, MAX(o.order_created) as max_order_created, SUM(op.order_product_quantity) as file_quantity ';
		$selectUniq = ', IF( REPLACE(LEFT(f.file_path, 1) , \'#\', \'@\') = \'@\', CONCAT(f.file_id, \'@\', o.order_id), f.file_id ) as uniq_id';
		$query = ' FROM '.hikashop_table('order').' AS o ' .
			' INNER JOIN '.hikashop_table('order_product').' AS op ON op.order_id = o.order_id ' .
			' INNER JOIN '.hikashop_table('product').' AS p ON op.product_id = p.product_id ' .
			' INNER JOIN '.hikashop_table('file').' AS f ON (op.product_id = f.file_ref_id OR p.product_parent_id = f.file_ref_id) ' .
			' WHERE ' . $filters;
		$groupBy = ' GROUP BY uniq_id ';

		$db->setQuery('SELECT '. $select . $selectSum . $selectUniq . $query . $groupBy . $order, (int)$pageInfo->limit->start, (int)$pageInfo->limit->value);
		$downloadData = $db->loadObjectList('uniq_id');

		if(!empty($pageInfo->search)) {
			$downloadData = hikashop_search($pageInfo->search,$downloadData,'order_id');
		}
		$db->setQuery('SELECT COUNT(*) as all_results_count FROM (SELECT f.file_id ' . $selectUniq . $query . $groupBy . ') AS all_results');

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $db->loadResult('total');
		$pageInfo->elements->page = count($downloadData);

		$file_ids = array();
		$order_ids = array();

		foreach($downloadData as $k => $data) {
			if((int)$data->order_id > 0)
				$order_ids[(int)$data->order_id] = (int)$data->order_id;
			$downloadData[$k]->download_total = 0;
			$downloadData[$k]->downloads = array();
			$downloadData[$k]->orders = array();
			if(strpos($k,'@') === false)
				$file_ids[] = $k;
		}

		if(!empty($file_ids)) {
			$db->setQuery('SELECT ' . $select . $query . ' AND f.file_id IN (' . implode(',', $file_ids) . ')');
			$orders = $db->loadObjectList();
			foreach($orders as $o) {
				if(isset($downloadData[$o->file_id])) {
					$downloadData[$o->file_id]->orders[(int)$o->order_id] = $o;
					$downloadData[$o->file_id]->orders[(int)$o->order_id]->file_qty = 0;
					$downloadData[$o->file_id]->orders[(int)$o->order_id]->download_total = 0;
				}
				$order_ids[(int)$o->order_id] = (int)$o->order_id;
			}
		}

		if(!empty($order_ids)) {
			$db->setQuery('SELECT * FROM ' . hikashop_table('download') . ' WHERE order_id IN (' . implode(',', $order_ids) . ')');
			$downloads = $db->loadObjectList();
			foreach($downloads as $download) {
				$uniq_id = $download->file_id . '@' . $download->order_id;
				if(isset($downloadData[$uniq_id])) {
					$downloadData[$uniq_id]->download_total += (int)$download->download_number;
					$downloadData[$uniq_id]->downloads[$download->file_pos] = $download;
				} else if(isset($downloadData[$download->file_id])) {
					$downloadData[$download->file_id]->download_total += (int)$download->download_number;
					if(isset($downloadData[$download->file_id]->orders[$download->order_id])) {
						$downloadData[$download->file_id]->orders[$download->order_id]->file_qty++;
						$downloadData[$download->file_id]->orders[$download->order_id]->download_total += (int)$download->download_number;
					}
				}
			}
		}

		jimport('joomla.html.pagination');
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '';

		$this->assignRef('pagination',$pagination);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('downloadData', $downloadData);
	}
}
