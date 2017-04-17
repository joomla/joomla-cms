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

if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');


$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
define('HIKASHOP_J16',version_compare($jversion,'1.6.0','>=') ? true : false);
define('HIKASHOP_J17',version_compare($jversion,'1.7.0','>=') ? true : false);
define('HIKASHOP_J25',version_compare($jversion,'2.5.0','>=') ? true : false);
define('HIKASHOP_J30',version_compare($jversion,'3.0.0','>=') ? true : false);

define('HIKASHOP_PHP5',version_compare(PHP_VERSION,'5.0.0', '>=') ? true : false);

class hikashop{
	function getDate($time = 0,$format = '%d %B %Y %H:%M'){ return hikashop_getDate($time,$format); }
	function isAllowed($allowedGroups,$id=null,$type='user'){ return hikashop_isAllowed($allowedGroups,$id,$type); }
	function addACLFilters(&$filters,$field,$table='',$level=2){ return hikashop_addACLFilters($filters,$field,$table,$level); }
	function currentURL($checkInRequest=''){ return hikashop_currentURL($checkInRequest); }
	function getTime($date){ return hikashop_getTime($date); }
	function getIP(){ return hikashop_getIP(); }
	function encode(&$data,$type='order',$format='') { return hikashop_encode($data,$type,$format); }
	function base($id){ return hikashop_base($id); }
	function decode($str,$type='order') { return hikashop_decode($str,$type); }
	function &array_path(&$array, $path) { return hikashop_array_path($array, $path); }
	function toFloat($val){ return hikashop_toFloat($val); }
	function loadUser($full=false,$reset=false){ return hikashop_loadUser($full,$reset); }
	function getZone($type='shipping'){ return hikashop_getZone($type); }
	function getCurrency(){ return hikashop_getCurrency(); }
	function cleanCart(){ return hikashop_cleanCart(); }
	function import( $type, $name, $dispatcher = null ){ return hikashop_import( $type, $name, $dispatcher); }
	function createDir($dir,$report = true){ return hikashop_createDir($dir,$report); }
	function initModule(){ return hikashop_initModule(); }
	function absoluteURL($text){ return hikashop_absoluteURL($text); }
	function setTitle($name,$picture,$link){ return hikashop_setTitle($name,$picture,$link); }
	function getMenu($title="",$menu_style='content_top'){ return hikashop_getMenu($title,$menu_style); }
	function getLayout($controller,$layout,$params,&$js){ return hikashop_getLayout($controller,$layout,$params,$js); }
	function setExplorer($task,$defaultId=0,$popup=false,$type=''){ return hikashop_setExplorer($task,$defaultId,$popup,$type); }
	function frontendLink($link,$popup = false){ return hikashop_frontendLink($link,$popup); }
	function backendLink($link,$popup = false){ return hikashop_backendLink($link,$popup); }
	function bytes($val) { return hikashop_bytes($val); }
	function display($messages,$type = 'success',$return = false){ return hikashop_display($messages,$type,$return); }
	function completeLink($link,$popup = false,$redirect = false){ return hikashop_completeLink($link,$popup,$redirect); }
	function table($name,$component = true){ return hikashop_table($name,$component); }
	function secureField($fieldName){ return hikashop_secureField($fieldName); }
	function increasePerf(){ hikashop_increasePerf(); }
	function &config($reload = false){ return hikashop_config($reload); }
	function level($level){ return hikashop_level($level); }
	function footer(){ return hikashop_footer(); }
	function search($searchString,$object,$exclude=''){ return hikashop_search($searchString,$object,$exclude); }
	function get($path){ return hikashop_get($path); }
	function getCID($field = '',$int=true){ return hikashop_getCID($field,$int); }
	function tooltip($desc,$title='', $image='tooltip.png', $name = '',$href='', $link=1){ return hikashop_tooltip($desc,$title, $image, $name,$href, $link); }
	function checkRobots(){ return hikashop_checkRobots(); }
}

function hikashop_getDate($time = 0,$format = '%d %B %Y %H:%M'){
	if(empty($time))
		return '';

	if(is_numeric($format))
		$format = JText::_('DATE_FORMAT_LC'.$format);
	$format_key = '';
	$clean_format = trim($format);
	if($clean_format=='%d %B %Y'){
		$format_key = 'HIKASHOP_DATE_FORMAT';
	}elseif($clean_format=='%d %B %Y %H:%M'){
		$format_key = 'HIKASHOP_EXTENDED_DATE_FORMAT';
	}
	if(!empty($format_key)){
		$language_format = JText::_($format_key);
		if($language_format!=$format_key){
			$format = $language_format;
		}
	}

	if(HIKASHOP_J16){
		$format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'),array('l','d','F','m','Y','y','H','i','s','D'),$format);
		return JHTML::_('date',$time,$format,false);
	}

	static $timeoffset = null;
	if($timeoffset === null) {
		$config = JFactory::getConfig();
		$timeoffset = $config->getValue('config.offset');
	}
	return JHTML::_('date', $time - date('Z'), $format, $timeoffset);
}

function hikashop_isAllowed($allowedGroups,$id=null,$type='user'){
	if($allowedGroups == 'all') return true;
	if($allowedGroups == 'none') return false;

	if(!is_array($allowedGroups)) $allowedGroups = explode(',',$allowedGroups);
	if(!HIKASHOP_J16){
		if($type=='user'){
			$my = JFactory::getUser($id);
			if(empty($my->id)){
				$group = 29;
			}else{
				$group = (int)@$my->gid;
			}
		}else{
			$group = $id;
		}
		return in_array($group,$allowedGroups);
	}

	if($type=='user'){
		jimport('joomla.access.access');
		$my = JFactory::getUser($id);
		$config =& hikashop_config();
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access'));
	}else{
		$userGroups = array($id);
	}
	$inter = array_intersect($userGroups,$allowedGroups);
	if(empty($inter)) return false;
	return true;
}

function hikashop_addACLFilters(&$filters, $field, $table='', $level=2, $allowNull=false, $user_id=0){
	if(!hikashop_level($level))
		return;

	if(empty($user_id) || (int)$user_id == 0) {
		$my = JFactory::getUser();
	} else {
		$userClass = hikashop_get('class.user');
		$hkUser = $userClass->get($user_id);
		$my = JFactory::getUser($hkUser->user_cms_id);
	}
	if(!HIKASHOP_J16) {
		if(empty($my->id))
			$userGroups = array(29);
		else
			$userGroups = array($my->gid);
	} else {
		jimport('joomla.access.access');
		$config =& hikashop_config();
		$userGroups = JAccess::getGroupsByUser($my->id, (bool)$config->get('inherit_parent_group_access'));//$my->authorisedLevels();
	}
	if(empty($userGroups))
		return;

	if(!empty($table))
			$table .= '.';
	$acl_filters = array($table.$field." = 'all'");
	foreach($userGroups as $userGroup) {
		$acl_filters[]=$table.$field." LIKE '%,".(int)$userGroup.",%'";
	}
	if($allowNull)
		$acl_filters[] = 'ISNULL(' . $table.$field . ')';
	$filters[] = '(' . implode(' OR ', $acl_filters) . ')';
}

function hikashop_currentURL($checkInRequest='',$safe=true){
	if(!empty($checkInRequest)){
		$url = JRequest::getVar($checkInRequest,'');
		if(!empty($url)){
			if(strpos($url,'http')!==0&&strpos($url,'/')!==0){
				if($checkInRequest=='return_url'){
					$url = base64_decode(urldecode($url));
				}elseif($checkInRequest=='url'){
					$url = urldecode($url);
				}
			}
			if($safe){
				$url = str_replace(array('"',"'",'<','>',';'),array('%22','%27','%3C','%3E','%3B'),$url);
			}
			return $url;
		}
	}

	$config = hikashop_config();
	$mode = $config->get('server_current_url_mode','REQUEST_URI');

	switch($mode){
		case '0':
		case 0:
		case '':
		default:
			if(!empty($_SERVER["REDIRECT_URL"]) && preg_match('#.*index\.php$#',$_SERVER["REDIRECT_URL"]) && empty($_SERVER['QUERY_STRING'])&&(empty($_SERVER['REDIRECT_QUERY_STRING']) || strpos($_SERVER['REDIRECT_QUERY_STRING'],'&')===false) && !empty($_SERVER["REQUEST_URI"])){
				$requestUri = $_SERVER["REQUEST_URI"];
				if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
			}elseif(!empty($_SERVER["REDIRECT_URL"]) && preg_match('#.*index\.php$#',$_SERVER["REDIRECT_URL"]) && !empty($_SERVER["REQUEST_URI"])){
				$requestUri = $_SERVER["REQUEST_URI"];
			}elseif(!empty($_SERVER["REDIRECT_URL"]) && (isset($_SERVER['QUERY_STRING'])||isset($_SERVER['REDIRECT_QUERY_STRING']))){
				$requestUri = $_SERVER["REDIRECT_URL"];
				if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
				elseif (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
			}elseif(isset($_SERVER["REQUEST_URI"])){
				$requestUri = $_SERVER["REQUEST_URI"];
			}else{
				$requestUri = $_SERVER['PHP_SELF'];
				if (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
			}
			break;
		case 'REQUEST_URI':
			$requestUri = $_SERVER["REQUEST_URI"];
			if (!empty($_SERVER["REDIRECT_URL"]) && !empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
			break;
		case 'REDIRECT_URL':
			$requestUri = $_SERVER["REQUEST_URI"];
			if (!empty($_SERVER['REDIRECT_QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['REDIRECT_QUERY_STRING'];
			elseif (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
			break;
	}

	$result = (hikashop_isSSL() ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$requestUri;
	if($safe){
		$result = str_replace(array('"',"'",'<','>',';'),array('%22','%27','%3C','%3E','%3B'),$result);
	}
	return $result;
}

function hikashop_getTime($date){
	static $timeoffset = null;
	if($timeoffset === null){
		$config = JFactory::getConfig();
		if(!HIKASHOP_J30){
			$timeoffset = $config->getValue('config.offset');
		} else {
			$timeoffset = $config->get('offset');
		}
		if(HIKASHOP_J16){
			$dateC = JFactory::getDate($date,$timeoffset);
			$timeoffset = $dateC->getOffsetFromGMT(true);
		}
	}
	if(!is_numeric($date)) $date = strtotime($date);
	return $date - $timeoffset *60*60 + date('Z');
}

function hikashop_getIP(){
	$ip = '';
	if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}elseif( !empty($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP']) > 6){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 6){
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return strip_tags($ip);
}

function hikashop_isSSL() {
	if((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) || $_SERVER['SERVER_PORT'] == 443 ||
		(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ) {
		return true;
	}
	return false;
}

function hikashop_getUpgradeLink($tolevel) {
	$config =& hikashop_config();
	$text = '';
	if($tolevel=='essential')
		$text = 'ONLY_COMMERCIAL';
	elseif($tolevel=='business')
		$text = 'ONLY_FROM_HIKASHOP_BUSINESS';
	return ' <a class="hikaupgradelink" href="'.HIKASHOP_REDIRECT.'upgrade-hikashop-'.strtolower($config->get('level')).'-to-'.$tolevel.'" target="_blank">'.JText::_($text).'</a>';
}

function hikashop_encode(&$data,$type='order', $format = '') {
	$id = null;
	if(is_object($data)){
		if($type=='order')
			$id = $data->order_id;
		if($type=='invoice')
			$id = $data->order_invoice_id;
	}else{
		$id = $data;
	}
	if(is_object($data) && ($type=='order' || $type=='invoice') && hikashop_level(1)){
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$result='';
		$trigger_name = 'onBefore'.ucfirst($type).'NumberGenerate';
		$dispatcher->trigger($trigger_name, array( &$data, &$result) );
		if(!empty($result)){
			return $result;
		}

		$config =& hikashop_config();
		if(empty($format)) {
			$format = $config->get($type.'_number_format','{automatic_code}');
		}
		if(preg_match('#\{id *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#Ui',$format,$matches)){
			$copy = $id;
			if(!empty($matches[1])){
				$copy = sprintf('%0'.$matches[1].'d', $copy);
			}
			$format = str_replace($matches[0],$copy,$format);
		}
		$matches=null;
		if(preg_match('#\{date *format ?= ?(?:"|\')(.*)(?:"|\') *\}#Ui',$format,$matches)){
			$format = str_replace($matches[0],date($matches[1],$data->order_modified),$format);
		}
		if(strpos($format,'{automatic_code}')!==false){
				$format = str_replace('{automatic_code}',hikashop_base($id),$format);
		}
		if(preg_match_all('#\{user ([a-z_0-9]+)\}#i',$format,$matches)){
			if(empty($data->customer)){
				$class = hikashop_get('class.user');
				$data->customer = $class->get($data->order_user_id);
			}
			foreach($matches[1] as $match){
				if(isset($data->customer->$match)){
					$format = str_replace('{user '.$match.'}',$data->customer->$match,$format);
				}else{
					$format = str_replace('{user '.$match.'}','',$format);
				}
			}
		}
		if(preg_match_all('#\{([a-z_0-9]+) *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#i',$format,$matches)){
			foreach($matches[1] as $k => $match){
				$copy = @$data->$match;
				if(!empty($matches[2][$k])){
					$copy = sprintf('%0'.$matches[2][$k].'d', $copy);
				}
				$format = str_replace($matches[0][$k],$copy,$format);
			}
		}
		return $format;
	}
	return hikashop_base($id);
}

function hikashop_base($id){
	$base=23;
	$chars='ABCDEFGHJKLMNPQRSTUWXYZ';
	$str = '';
	$val2=(string)$id;
	do {
		$i = $id % $base;
		$str = $chars[$i].$str;
		$id = ($id - $i) / $base;
	} while($id > 0);
	$str2='';
	$size = strlen($val2);
	for($i=0;$i<$size;$i++){
		if(isset($str[$i]))$str2.=$str[$i];
		$str2.=$val2[$i];
	}
	if($i<strlen($str)){
		$str2.=substr($str,$i);
	}
	return $str2;
}

function hikashop_decode($str,$type='order') {
	$config =& hikashop_config();
	if($type=='order' && hikashop_level(1)){
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$result='';
		$dispatcher->trigger( 'onBeforeOrderNumberRevert', array( & $str) );
		if(!empty($result)){
			return $result;
		}

		$format = $config->get('order_number_format','{automatic_code}');
		$format = str_replace(array('^','$','.','[',']','|','(',')','?','*','+'),array('\^','\$','\.','\[','\]','\|','\(','\)','\?','\*','\+'),$format);
		if(preg_match('#\{date *format ?= ?(?:"|\')(.*)(?:"|\') *\}#Ui',$format,$matches)){
			$format = str_replace($matches[0],'(?:'.preg_replace('#[a-z]+#i','[0-9a-z]+',$matches[1]).')',$format);
		}
		if(preg_match('#\{id *(?:size ?= ?(?:"|\')(.*)(?:"|\'))? *\}#Ui',$format,$matches)){
			$format = str_replace($matches[0],'([0-9]+)',$format);
		}
		if(strpos($format,'{automatic_code}')!==false){
				$format = str_replace('{automatic_code}','([0-9a-z]+)',$format);
		}
		if(preg_match_all('#\{([a-z_0-9]+)\}#i',$format,$matches)){
			foreach($matches[1] as $match){
				if(isset($data->$match)){
					$format = str_replace('{'.$match.'}','.*',$format);
				}else{
					$format = str_replace('{'.$match.'}','',$format);
				}
			}
		}

		$format = str_replace(array('{','}'),array('\{','\}'),$format);

		if(preg_match('#'.$format.'#i',$str,$matches)){
			foreach($matches as $i => $match){
				if($i){
					return ltrim(preg_replace('#[^0-9]#','',$match),'0');
				}
			}
		}
	}
	return preg_replace('#[^0-9]#','',$str);
}

function &hikashop_array_path(&$array, $path) {
	settype($path, 'array');
	$offset =& $array;
	foreach ($path as $index) {
		if (!isset($offset[$index])) {
			return false;
		}
		$offset =& $offset[$index];
	}
	return $offset;
}

function hikashop_toFloat($val){
	if(is_string($val) && preg_match_all('#-?[0-9]+#',$val,$parts) && count($parts[0])>1){
		$dec=array_pop($parts[0]);
		return (float) implode('',$parts[0]).'.'.$dec;
	}
	return (float) $val;
}

function hikashop_loadUser($full=false,$reset=false){
	static $user = null;
	if($reset){
		$user = null;
		return true;
	}
	if(!isset($user) || $user === null){
		$app = JFactory::getApplication();
		$user_id = (int)$app->getUserState( HIKASHOP_COMPONENT.'.user_id' );
		$class = hikashop_get('class.user');
		if(empty($user_id)){
			$userCMS = JFactory::getUser();
			if(!$userCMS->guest){
				$joomla_user_id = $userCMS->get('id');
				$user_id = $class->getID($userCMS->get('id'));
				$app->setUserState( HIKASHOP_COMPONENT.'.user_id',$user_id);
			}else{
				$app->setUserState( HIKASHOP_COMPONENT.'.user_id',0);
				return $user;
			}
		}

		$user = $class->get($user_id);
	}
	if($full)
		return $user;
	return (int)@$user->user_id;
}

function hikashop_getZone($type = 'shipping') {
	if(empty($type)) {
		$config =& hikashop_config();
		$type = $config->get('tax_zone_type', 'shipping');
	}
	$app = JFactory::getApplication();
	$shipping_address = $app->getUserState( HIKASHOP_COMPONENT.'.'.$type.'_address', 0);
	$zone_id = 0;
	if(empty($shipping_address) && $type == 'shipping')
		$shipping_address = $app->getUserState( HIKASHOP_COMPONENT.'.'.'billing_address', 0);

	if(!empty($shipping_address)) {
		$useMainZone = false;
		$id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id', '');
		if(!empty($id)) {
			if(is_array($id))
				$id = reset($id);

			$shippingClass = hikashop_get('class.shipping');
			$shipping = $shippingClass->get($id);
			if(!empty($shipping->shipping_params))
				$params = unserialize($shipping->shipping_params);

			if($type == 'shipping' && !empty($params->override_tax_zone) && is_numeric($params->override_tax_zone)){
				return (int)$params->override_tax_zone;
			}

			$override = 0;
			if(isset($params->shipping_override_address))
				$override = (int)$params->shipping_override_address;

			if($override && $type == 'shipping') {
				$config =& hikashop_config();
				$zone_id = explode(',',$config->get('main_tax_zone', $zone_id));
				if(count($zone_id))
					$zone_id = array_shift($zone_id);
				else
					$zone_id = 0;
				return (int)$zone_id;
			}
		}

		$addressClass = hikashop_get('class.address');
		$address = $addressClass->get($shipping_address);
		if(!empty($address)) {
			$field = 'address_country';
			if(!empty($address->address_state))
				$field = 'address_state';

			static $zones = array();
			if(empty($zones[$address->$field])) {
				$zoneClass = hikashop_get('class.zone');
				$zones[$address->$field] = $zoneClass->get($address->$field);
			}
			if(!empty($zones[$address->$field]))
				$zone_id = $zones[$address->$field]->zone_id;
		}

	}
	if(empty($zone_id)) {
		$zone_id = $app->getUserState( HIKASHOP_COMPONENT.'.zone_id', 0);
		if(empty($zone_id)) {
			$config =& hikashop_config();
			$zone_id = explode(',', $config->get('main_tax_zone', $zone_id));
			if(count($zone_id))
				$zone_id = array_shift($zone_id);
			else
				$zone_id = 0;
			$app->setUserState( HIKASHOP_COMPONENT.'.zone_id', $zone_id);
		}
	}
	return (int)$zone_id;
}

function hikashop_getCurrency(){
	$config =& hikashop_config();
	$main_currency = (int)$config->get('main_currency',1);
	$app = JFactory::getApplication();
	$currency_id = (int)$app->getUserState( HIKASHOP_COMPONENT.'.currency_id', $main_currency );

	if($currency_id!=$main_currency && !$app->isAdmin()){
		static $checked = array();
		if(!isset($checked[$currency_id])){
			$checked[$currency_id]=true;
			$db = JFactory::getDBO();
			$db->setQuery('SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_id = '.$currency_id. ' AND ( currency_published=1 OR currency_displayed=1 )');
			$currency_id = $db->loadResult();
		}
	}

	if(empty($currency_id)){
		$app->setUserState( HIKASHOP_COMPONENT.'.currency_id', $main_currency );
		$currency_id=$main_currency;
	}
	return $currency_id;
}

function hikashop_cleanCart(){
	$config =& hikashop_config();
	$period = $config->get('cart_retaining_period');
	$check = $config->get('cart_retaining_period_check_frequency',86400);
	$checked = $config->get('cart_retaining_period_checked',0);
	$max = time()-$check;
	if(!$checked || $checked<$max){
		$database = JFactory::getDBO();
		$query = 'SELECT cart_id FROM '.hikashop_table('cart').' WHERE cart_type = '.$database->Quote('cart').' AND cart_modified < '.(time()-$period);
		$database->setQuery($query);
		if(!HIKASHOP_J25){
			$ids = $database->loadResultArray();
		} else {
			$ids = $database->loadColumn();
		}
		if(!empty($ids)){
			$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_id IN ('.implode(',',$ids).')';
			$database->setQuery($query);
			$database->query();
			$query = 'DELETE FROM '.hikashop_table('cart').' WHERE cart_id IN ('.implode(',',$ids).')';
			$database->setQuery($query);
			$database->query();
		}
		$options = array('cart_retaining_period_checked'=>time());
		$config->save($options);
	}
}

function hikashop_import( $type, $name, $dispatcher = null ){
	$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $type);
	$name = preg_replace('#[^A-Z0-9_\.-]#i', '', $name);
	if(!HIKASHOP_J16){
		$path = JPATH_PLUGINS.DS.$type.DS.$name.'.php';
	}else{
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS.$name.'.php';
	}
	$instance=false;
	if (file_exists( $path )){
		require_once( $path );
		if($type=='editors-xtd') $typeName = 'Button';
		else $typeName = $type;
		$className = 'plg'.$typeName.$name;
		if(class_exists($className)){
			if($dispatcher==null){
				$dispatcher = JDispatcher::getInstance();
			}
			$instance = new $className($dispatcher, array('name'=>$name,'type'=>$type));
		}
	}
	return $instance;
}

function hikashop_createDir($dir,$report = true){
	if(is_dir($dir)) return true;

	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');

	$indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

	if(!JFolder::create($dir)){
		if($report) hikashop_display('Could not create the directly '.$dir,'error');
		return false;
	}
	if(!JFile::write($dir.DS.'index.html',$indexhtml)){
		if($report) hikashop_display('Could not create the file '.$dir.DS.'index.html','error');
	}
	return true;
}

function hikashop_initModule(){
	static $done = false;
	if(!$done){
		$fe = JRequest::getVar('hikashop_front_end_main',0);
		if(empty($fe)){
			$done = true;
			$lang = JFactory::getLanguage();
			if(HIKASHOP_J25 && !method_exists($lang, 'publicLoadLanguage'))
				$lang = new hikaLanguage($lang);
			$override_path = JLanguage::getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$lang->getTag().'.override.ini';
			$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
			if(!HIKASHOP_J16 && file_exists($override_path))
				$lang->_load($override_path, 'override');
			elseif(HIKASHOP_J25)
				$lang->publicLoadLanguage($override_path, 'override');
		}
	}
	return true;
}

//		//-->";

function hikashop_absoluteURL($text){
	static $mainurl = '';
	if(empty($mainurl)){
		$urls = parse_url(HIKASHOP_LIVE);
		if(!empty($urls['path'])){
			$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
		}else{
			$mainurl = HIKASHOP_LIVE;
		}
	}
	$text = str_replace(array('href="../undefined/','href="../../undefined/','href="../../../undefined//','href="undefined/'),array('href="'.$mainurl,'href="'.$mainurl,'href="'.$mainurl,'href="'.HIKASHOP_LIVE),$text);
	$text = preg_replace('#href="(/?administrator)?/({|%7B)#Uis','href="$2',$text);
	$replace = array();
	$replaceBy = array();
	if($mainurl !== HIKASHOP_LIVE){
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./)#i';
		$replaceBy[] = '$1="'.substr(HIKASHOP_LIVE,0,strrpos(rtrim(HIKASHOP_LIVE,'/'),'/')+1);
	}
	$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:|/))(?:\.\./|\./)?#i';
	$replaceBy[] = '$1="'.HIKASHOP_LIVE;
	$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\#|[a-z]{3,7}:))/#i';
	$replaceBy[] = '$1="'.$mainurl;
	$replace[] = '#((background-image|background)[ ]*:[ ]*url\(\'?"?(?!([a-z]{3,7}:|/|\'|"))(?:\.\./|\./)?)#i';
	$replaceBy[] = '$1'.HIKASHOP_LIVE;
	return preg_replace($replace,$replaceBy,$text);
}

function hikashop_disallowUrlRedirect($url){
	$url = str_replace(array('http://www.','https://www.','https://'), array('http://','http://','http://'),strtolower($url));
	$live = str_replace(array('http://www.','https://www.','https://'), array('http://','http://','http://'),strtolower(HIKASHOP_LIVE));
	if(strpos($url,$live)!==0 && preg_match('#^http://.*#',$url)) return true;
	jimport('joomla.filter.filterinput');
	$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
	if($safeHtmlFilter->clean($url,'string') != $url) return true;
	return false;
}

function hikashop_setTitle($name,$picture,$link){
	$config =& hikashop_config();
	$menu_style = $config->get('menu_style','title_bottom');
	if(HIKASHOP_J30) $menu_style = 'content_top';
	$html = '<a href="'. hikashop_completeLink($link).'">'.$name.'</a>';
	if($menu_style != 'content_top') {
		$html = hikashop_getMenu($html,$menu_style);
	}
	JToolBarHelper::title( $html , $picture.'.png' );
	if(HIKASHOP_J25) {
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$doc->setTitle($app->getCfg('sitename'). ' - ' .JText::_('JADMINISTRATION').' - '.$name);
	}
}

function hikashop_setPageTitle($title){
	$doc = JFactory::getDocument();
	$app = JFactory::getApplication();
	if(!empty($title)){
		$key = str_replace(',','_',$title);
		$title_name = JText::_($key);
		if($title_name==$key){
			$title_name = $title;
		}
	}
	if (empty($title_name)) {
		$title_name = $app->getCfg('sitename');
	}
	elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
		$title_name = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title_name);
	}
	elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
		$title_name = JText::sprintf('JPAGETITLE', $title_name, $app->getCfg('sitename'));
	}
	$doc->setTitle( strip_tags($title_name) );
}

function hikashop_getMenu($title = '', $menu_style = 'content_top') {
	if(HIKASHOP_J30) $menu_type = 'content_top';
	$document = JFactory::getDocument();
	$controller = new hikashopBridgeController(array('name'=>'menu'));
	$viewType = $document->getType();
	if(empty($viewType)) $viewType = 'html';
	if(!HIKASHOP_PHP5)
		$view =& $controller->getView('', $viewType, '');
	else
		$view = $controller->getView('', $viewType, '');

	$view->setLayout('default');
	ob_start();
	$view->display(null, $title, $menu_style);
	return ob_get_clean();
}

function hikashop_getLayout($controller,$layout,$params,&$js,$backend = false){
	$base_path=HIKASHOP_FRONT;
	$app = JFactory::getApplication();
	if($app->isAdmin() || $backend){
		$base_path=HIKASHOP_BACK;
	}
	$base_path=rtrim($base_path,DS);
	$document = JFactory::getDocument();

	$controller = new hikashopBridgeController(array('name'=>$controller,'base_path'=>$base_path));
	$viewType = $document->getType();
	if(empty($viewType)) $viewType = 'html';
	if(!HIKASHOP_PHP5) {
		$view = & $controller->getView( '', $viewType, '',array('base_path'=>$base_path));
	} else {
		$view = $controller->getView( '', $viewType, '',array('base_path'=>$base_path));
	}

	$folder	= $base_path.DS.'views'.DS.$view->getName().DS.'tmpl';
	$view->addTemplatePath($folder);
	$folder	= JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.HIKASHOP_COMPONENT.DS.$view->getName();
	$view->addTemplatePath($folder);
	$old = $view->setLayout($layout);
	ob_start();
	$view->display(null,$params);
	$js = @$view->js;
	if(!empty($old))
		$view->setLayout($old);
	return ob_get_clean();
}

function hikashop_setExplorer($task, $defaultId = 0, $popup = false, $type = '') {
	$document = JFactory::getDocument();
	$controller = new hikashopBridgeController(array('name' => 'explorer'));
	$viewType = $document->getType();
	$view = $controller->getView('', $viewType, '');
	$view->setLayout('default');
	ob_start();
	$view->display(null, $task, $defaultId, $popup, $type);
	return ob_get_clean();
}

function hikashop_frontendLink($link,$popup = false){
	if($popup) $link .= '&tmpl=component';

	$menusClass = hikashop_get('class.menus');
	$id = 0;
	$to_be_replaced = '';
	if(preg_match('#Itemid=([0-9]+)#',$link,$match)){
		$to_be_replaced = $match[0];
		$new_id = $menusClass->loadAMenuItemId('','',$id);
	}
	if(empty($id)){
		$new_id = $menusClass->loadAMenuItemId('','');
	}

	$by = (empty($new_id)?'':'Itemid='.$new_id);
	if(empty($to_be_replaced)){
		$link .= '&'.$by;
	}else{
		$link = str_replace($to_be_replaced,$by,$link);
	}

	$config = hikashop_config();
	$app = JFactory::getApplication();
	if(!$app->isAdmin() && $config->get('activate_sef',0)){
		$link = ltrim(JRoute::_($link,false),'/');
	}

	static $mainurl = '';
	static $otherarguments = false;
	if(empty($mainurl)){
		$urls = parse_url(HIKASHOP_LIVE);
		if(isset($urls['path']) AND strlen($urls['path'])>0){
			$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
			$otherarguments = trim(str_replace($mainurl,'',HIKASHOP_LIVE),'/');
			if(strlen($otherarguments) > 0) $otherarguments .= '/';
		}else{
			$mainurl = HIKASHOP_LIVE;
		}
	}

	if($otherarguments && strpos($link,$otherarguments) === false){
		$link = $otherarguments.$link;
	}

	return $mainurl.$link;
}

function hikashop_backendLink($link,$popup = false){
	static $mainurl = '';
	static $otherarguments = false;
	if(empty($mainurl)){
		$urls = parse_url(HIKASHOP_LIVE);
		if(!empty($urls['path'])){
			$mainurl = substr(HIKASHOP_LIVE,0,strrpos(HIKASHOP_LIVE,$urls['path'])).'/';
			$otherarguments = trim(str_replace($mainurl,'',HIKASHOP_LIVE),'/');
			if(!empty($otherarguments)) $otherarguments .= '/';
		}else{
			$mainurl = HIKASHOP_LIVE;
		}
	}
	if($otherarguments && strpos($link,$otherarguments) === false){
		$link = $otherarguments.$link;
	}
	return $mainurl.$link;
}

function hikashop_bytes($val) {
	$val = trim($val);
	if(empty($val))
		return 0;
	$last = strtolower($val[strlen($val)-1]);
	switch($last) {
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return (int)$val;
}

function hikashop_display($messages, $type = 'success', $return = false, $close = true){
	if(empty($messages))
		return;
	if(!is_array($messages))
		$messages = array($messages);
	$app = JFactory::getApplication();
	if(($app->isAdmin() && !HIKASHOP_BACK_RESPONSIVE) || (!$app->isAdmin() && !HIKASHOP_RESPONSIVE)) {
		$html = '<div id="hikashop_messages_'.$type.'" class="hikashop_messages hikashop_'.$type.'"><ul><li>'.implode('</li><li>',$messages).'</li></ul></div>';
	} else {
		$html = '<div class="alert alert-'.$type.' alert-block">'.($close?'<button type="button" class="close" data-dismiss="alert">×</button>':'').'<p>'.implode('</p><p>',$messages).'</p></div>';
	}

	if($return)
		return $html;
	echo $html;
}

function hikashop_completeLink($link,$popup = false,$redirect = false, $js = false){
	if($popup) $link .= '&tmpl=component';
	$ret = JRoute::_('index.php?option='.HIKASHOP_COMPONENT.'&ctrl='.$link,!$redirect);
	if($js) return str_replace('&amp;', '&', $ret);
	return $ret;
}

function hikashop_contentLink($link,$object,$popup = false,$redirect = false, $js = false){
	$config = hikashop_config();
	$force_canonical = $config->get('force_canonical_urls',1);
	if($force_canonical){
		$url = null;
		if(!empty($object->product_canonical)){
			$url = hikashop_cleanURL($object->product_canonical);
		}elseif(!empty($object->product_parent_id)){
			$class = hikashop_get('class.product');
			$parent = $class->get($object->product_parent_id);
			if(!empty($parent->product_canonical))
				$url = hikashop_cleanURL($parent->product_canonical);
		}elseif(!empty($object->category_canonical)){
			$url = hikashop_cleanURL($object->category_canonical);
		}

		if(!empty($url)){
			if($popup){
				if(strpos($url,'?')!==false){
					$url.='&';
				}else{
					$url.='?';
				}
				$url .= 'tmpl=component';
			}
			if($js) return str_replace('&amp;', '&', $url);
			return $url;
		}
	}

	if(preg_match('#Itemid=([0-9]+)#',$link,$match)){
		$menusClass = hikashop_get('class.menus');
		$type = '';
		if(!empty($object->product_id)){
			$type = 'category';
		}elseif(!empty($object->category_id)){
			if(isset($object->category_type) && $object->category_type=='manufacturer'){
				$type = 'manufacturer';
			}else{
				$type = 'category';
			}
		}
		if(!empty($type)){
			$id = $menusClass->loadAMenuItemId($type,'listing',$match[1]);
			if(empty($id)){
				$id = $menusClass->loadAMenuItemId('product','listing',$match[1]);
				if(empty($id)){
					$id = $menusClass->loadAMenuItemId($type,'listing');
					$link = str_replace('Itemid='.$match[1],'Itemid='.$id,$link);
				}
			}

		}
	}

	$url = hikashop_completeLink($link,$popup,$redirect, $js);
	if($force_canonical==2){
		if(!empty($object->product_id)){
			$newObj = new stdClass();
			$newObj->product_id = $object->product_id;
			$newObj->product_canonical = $url;
			$productClass = hikashop_get('class.product');
			$productClass->save($newObj);
		}elseif(!empty($object->category_id)){
			$newObj = new stdClass();
			$newObj->category_id = $object->category_id;
			$newObj->category_canonical = $url;
			$categoryClass = hikashop_get('class.category');
			$categoryClass->save($newObj);
		}
	}
	return $url;
}

function hikashop_table($name,$component = true){
	$prefix = $component ? HIKASHOP_DBPREFIX : '#__';
	return $prefix.$name;
}

function hikashop_secureField($fieldName){
	if (!is_string($fieldName) || preg_match('|[^a-z0-9#_.-]|i',$fieldName) !== 0 ){
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		die('field "'.htmlentities($safeHtmlFilter->clean($fieldName,'string')) .'" not secured');
	}
	return $fieldName;
}

function hikashop_increasePerf(){
	@ini_set('max_execution_time',0);
	if(hikashop_bytes(@ini_get('memory_limit')) < 60000000){
		$config = hikashop_config();
		if($config->get('hikaincreasemem','1')){
			if(!empty($_SESSION['hikaincreasemem'])){
				$newConfig = new stdClass();
				$newConfig->hikaincreasemem = 0;
				$config->save($newConfig);
				unset($_SESSION['hikaincreasemem']);
				return;
			}
			if(isset($_SESSION)) $_SESSION['hikaincreasemem'] = 1;
			@ini_set('memory_limit','64M');
			if(isset($_SESSION['hikaincreasemem'])) unset($_SESSION['hikaincreasemem']);
		}
	}
}

function &hikashop_config($reload = false){
	static $configClass = null;
	if($configClass === null || $reload || !is_object($configClass) || $configClass->get('configClassInit',0) == 0){
		$configClass = hikashop_get('class.config');
		$configClass->load();
		$configClass->set('configClassInit',1);
	}
	return $configClass;
}

function hikashop_level($level){
	$config =& hikashop_config();
	if($config->get($config->get('level'),0) >= $level) return true;
	return false;
}

function hikashop_footer(){
	$config =& hikashop_config();
	if($config->get('show_footer',true)=='-1') return '';
	$description = $config->get('description_'.strtolower($config->get('level')),'Joomla!<sup>&reg;</sup> Ecommerce System');
	$link = 'http://www.hikashop.com';
	$aff = $config->get('partner_id');
	if(!empty($aff)){
		$link.='?partner_id='.$aff;
	}
	$text = '<!--  HikaShop Component powered by '.$link.' -->
	<!-- version '.$config->get('level').' : '.$config->get('version').' [1510211553] -->';
	if(!$config->get('show_footer',true)) return $text;
	$text .= '<div class="hikashop_footer" style="text-align:center" align="center"><a href="'.$link.'" target="_blank" title="'.HIKASHOP_NAME.' : '.strip_tags($description).'">'.HIKASHOP_NAME.' ';
	$app= JFactory::getApplication();
	if($app->isAdmin()){
		$text .= $config->get('level').' '.$config->get('version');
	}
	$text .= ', '.$description.'</a></div>';
	return $text;
}

function hikashop_search($searchString,$object,$exclude=''){
	if(empty($object) || is_numeric($object))
		return $object;
	if(is_string($object)){
		return preg_replace('#('.str_replace(array('#','(',')','.','[',']','?','*'),array('\#','\(','\)','\.','\[','\]','\?','\*'),$searchString).')#i','<span class="searchtext">$1</span>',$object);
	}
	if(is_array($object)){
		foreach($object as $key => $element){
			$object[$key] = hikashop_search($searchString,$element,$exclude);
		}
	}elseif(is_object($object)){
		foreach($object as $key => $element){
			if((is_string($exclude) && $key != $exclude) || (is_array($exclude) && !in_array($key, $exclude)))
				$object->$key = hikashop_search($searchString,$element,$exclude);
		}
	}
	return $object;
}

function hikashop_get($path) {
	if(strpos($path, '/') !== false || strpos($path, '\\') !== false)
		return null;
	list($group, $class) = explode('.', strtolower($path));
	if($group == 'controller')
		$className = $class.ucfirst($group);
	else
		$className = 'hikashop'.ucfirst(str_replace('-', '', $class)).ucfirst($group);

	if(class_exists($className.'Override'))
		$className .= 'Override';
	if(!class_exists($className)) {
		$class = str_replace('-', DS, $class);
		$app = JFactory::getApplication();
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'com_hikashop'.DS.'administrator'.DS;
		$override = str_replace(HIKASHOP_BACK, $path, constant(strtoupper('HIKASHOP_'.$group))).$class.'.override.php';

		if(JFile::exists($override)) {
			$originalFile = constant('HIKASHOP_'.strtoupper($group)).$class.'.php';
			include_once($override);
			$className .= 'Override';
		} else {
			$include_file = constant('HIKASHOP_'.strtoupper($group)).$class.'.php';
			if(JFile::exists($include_file))
				include_once($include_file);
		}
	}
	if(!class_exists($className)) return null;

	$args = func_get_args();
	array_shift($args);
	switch(count($args)){
		case 3:
			return new $className($args[0],$args[1],$args[2]);
		case 2:
			return new $className($args[0],$args[1]);
		case 1:
			return new $className($args[0]);
		case 0:
		default:
			return new $className();
	}
}

function hikashop_getPluginController($ctrl) {
	if(empty($ctrl))
		return false;

	JPluginHelper::importPlugin('hikashop');
	$dispatcher = JDispatcher::getInstance();
	$controllers = $dispatcher->trigger('onHikashopPluginController', array($ctrl));

	if(empty($controllers))
		return false;
	foreach($controllers as $k => &$c) {
		if(!is_array($c) && is_string($c))
			$c = array('name' => $c);
		if(empty($c['name'])) {
			unset($controllers[$k]);
			continue;
		}
		if(empty($c['type']))
			$c['type'] = 'hikashop';
	}
	unset($c);

	if(count($controllers) > 1)
		return false;

	$controller = reset($controllers);

	if(empty($controller['prefix']))
		$controller['prefix'] = 'ctrl';

	$type = preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['type']);
	$name = preg_replace('#[^A-Z0-9_\.-]#i', '', $controller['name']);
	$prefix = preg_replace('#[^A-Z0-9]#i', '', $controller['prefix']);
	if(!HIKASHOP_J16)
		$path = JPATH_PLUGINS.DS.$type.DS;
	else
		$path = JPATH_PLUGINS.DS.$type.DS.$name.DS;

	jimport('joomla.filesystem.folder');
	jimport('joomla.filesystem.file');
	if(!JFile::exists($path.$name.'_'.$prefix.'.php') || !JFolder::exists($path.'views'.DS))
		return false;

	include_once($path.$name.'_'.$prefix.'.php');
	return true;
}

function hikashop_getCID($field = '',$int=true){
	$oneResult = JRequest::getVar('cid', array(), '', 'array');
	if(is_array($oneResult)) $oneResult = reset($oneResult);
	if(empty($oneResult) && !empty($field)) $oneResult = JRequest::getCmd($field, 0);
	if($int) return intval($oneResult);
	return $oneResult;
}

function hikashop_tooltip($desc, $title = '', $image = 'tooltip.png', $name = '', $href = '', $link = 1) {
	static $class = null;
	if($class === null) {
		$class = HIKASHOP_J30 ? 'hasTooltip' : 'hasTip';
		if(HIKASHOP_J30) {
			$app = JFactory::getApplication();
			$config = hikashop_config();
			if($app->isAdmin() || $config->get('bootstrap_design', HIKASHOP_J30))
				JHtml::_('bootstrap.tooltip');
			else
				$class = 'hasTip';
		}
	}
	return JHTML::_('tooltip', str_replace(array("'", "::"), array("&#039;", ": : "), $desc . ' '), str_replace(array("'", '::'), array("&#039;", ': : '), $title), $image, str_replace(array("'", '"', '::'), array("&#039;", "&quot;", ': : '), $name . ' '), $href, $link, $class);
}

function hikashop_checkRobots(){
	if(preg_match('#(libwww-perl|python)#i',@$_SERVER['HTTP_USER_AGENT']))
		die('Not allowed for robots. Please contact us if you are not a robot');
}

function hikashop_loadJslib($name){
	static $loadLibs = array();
	$doc = JFactory::getDocument();
	$name = strtolower($name);
	$ret = false;
	if(isset($loadLibs[$name]))
		return $loadLibs[$name];

	switch($name) {
		case 'mootools':
			if(!HIKASHOP_J30)
				JHTML::_('behavior.mootools');
			else
				JHTML::_('behavior.framework');
			break;
		case 'jquery':
			$doc->addScript(HIKASHOP_JS.'jquery.min.js');
			$doc->addScript(HIKASHOP_JS.'jquery-ui.min.js');
			$ret = true;
			break;
		case 'tooltip':
			hikashop_loadJslib('jquery');
			$doc->addScript(HIKASHOP_JS.'tooltip.js');
			$doc->addStyleSheet(HIKASHOP_CSS.'tooltip.css');
			$doc->addScriptDeclaration('
hkjQuery(function () { hkjQuery(\'[data-toggle="hk-tooltip"]\').hktooltip({"html": true,"container": "body"}); });
');
			$ret = true;
			break;
		case 'otree':
			$doc->addScript(HIKASHOP_JS.'otree.js?v='.HIKASHOP_RESSOURCE_VERSION);
			$doc->addStyleSheet(HIKASHOP_CSS.'otree.css?v='.HIKASHOP_RESSOURCE_VERSION);
			$ret = true;
			break;
		case 'opload':
			$doc->addScript(HIKASHOP_JS.'opload.js?v='.HIKASHOP_RESSOURCE_VERSION);
			$doc->addStyleSheet(HIKASHOP_CSS.'opload.css?v='.HIKASHOP_RESSOURCE_VERSION);
			$ret = true;
			break;
	}

	$loadLibs[$name] = $ret;
	return $ret;
}

function hikashop_cleanURL($url, $forceInternURL=false){
	$parsedURL = parse_url($url);
	$parsedCurrent = parse_url(JURI::base());

	if($forceInternURL == false && isset($parsedURL['scheme']))
		return $url;

	if(preg_match('#https?://#',$url)){
		return $url;
	}

	if(preg_match('#www.#',$url)){
		return $parsedCurrent['scheme'].'://'.$url;
	}

	if($parsedURL['path'][0]!='/'){
		$parsedURL['path']='/'.$parsedURL['path'];
	}

	if(!isset($parsedURL['query']))
		$endUrl = $parsedURL['path'];
	else
		$endUrl = $parsedURL['path'].'?'.$parsedURL['query'];

	$port = '';
	if(!empty($parsedCurrent['port']) && $parsedCurrent['port']!= 80){
		$port = ':'.$parsedCurrent['port'];
	}

	if(isset($parsedCurrent['path']) && !preg_match('#^/?'.$parsedCurrent['path'].'#',$endUrl))
		$parsedCurrent['path'] = preg_replace('#/$#','',$parsedCurrent['path']);
	else
		$parsedCurrent['path'] = '';

	$cleanUrl = $parsedCurrent['scheme'].'://'.$parsedCurrent['host'].$port.$parsedCurrent['path'].$endUrl;
	return $cleanUrl;
}

function hikashop_orderStatus($order_status) {
	$order_upper = JString::strtoupper($order_status);
	$tmp = 'ORDER_STATUS_' . $order_upper;
	$ret = JText::_($tmp);
	if($ret != $tmp)
		return $ret;
	$ret = JText::_($order_upper);
	if($ret != $order_upper)
		return $ret;
	return $order_status;
}

function hikashop_getEscaped($text, $extra = false) {
	$db = JFactory::getDBO();
	if(HIKASHOP_J30)
		return $db->escape($text, $extra);
	return $db->getEscaped($text, $extra);
}

function hikashop_nocache() {
	if(headers_sent())
		return false;

	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Expires: Wed, 17 Sep 1975 21:32:10 GMT');
	return true;
}

function hikashop_limitString($string, $limit, $replacement = '...', $tooltip = false) {
	if(empty($string) || !is_string($string))
		return '';
	$l = strlen($string);
	if($l <= $limit)
		return $string;

	$nbExtra = $l - $limit + strlen($replacement);
	$new_string = substr($string, 0, $l - ceil(($l + $nbExtra) / 2)) . $replacement . substr($string, floor(($l + $nbExtra) / 2));
	if($tooltip)
		return hikashop_tooltip($string, '', '', $new_string, '', 0);
	return $new_string;
}

function hikashop_acl($acl) {
	return true;
}

if(!HIKASHOP_J30){
	function hikashop_getFormToken() {
		return JUtility::getToken();
	}
} else {
	function hikashop_getFormToken() {
		return JSession::getFormToken();
	}
}

if(!class_exists('hikashopBridgeController')){
	if(!HIKASHOP_J30){
		class hikashopBridgeController extends JController {}
	} else {
		class hikashopBridgeController extends JControllerLegacy {}
	}
}

class hikashopController extends hikashopBridgeController {
	var $pkey = array();
	var $table = array();
	var $groupMap = '';
	var $groupVal = null;
	var $orderingMap ='';

	var $display = array('listing','show','cancel','');
	var $local_display = array();
	var $modify_views = array('edit','selectlisting','childlisting','newchild');
	var $add = array('add');
	var $modify = array('apply','save','save2new','store','orderdown','orderup','saveorder','savechild','addchild','toggle');
	var $delete = array('delete','remove');
	var $publish_return_view = 'listing';
	var $pluginCtrl = null;

	function __construct($config = array(),$skip=false){
		if(!empty($this->pluginCtrl) && is_array($this->pluginCtrl)) {
			if(!HIKASHOP_J16)
				$config['base_path'] = JPATH_PLUGINS.DS.$this->pluginCtrl[0].DS;
			else
				$config['base_path'] = JPATH_PLUGINS.DS.$this->pluginCtrl[0].DS.$this->pluginCtrl[1].DS;
		}
		if(!$skip) {
			parent::__construct($config);
			$this->registerDefaultTask('listing');
		}
		if(!empty($this->local_display))
			$this->display = array_merge($this->display, $this->local_display);
	}
	function listing(){
		JRequest::setVar('layout', 'listing');
		return $this->display();
	}
	function show(){
		JRequest::setVar('layout', 'show');
		return $this->display();
	}
	function edit(){
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar('layout', 'form');
		return $this->display();
	}
	function add(){
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar('layout', 'form');
		return $this->display();
	}
	function apply(){
		$status = $this->store();
		return $this->edit();
	}
	function save(){
		$this->store();
		return $this->listing();
	}
	function save2new(){
		$this->store(true);
		return $this->edit();
	}
	function orderdown(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = $this->pkey;
			$orderClass->table = $this->table;
			$orderClass->groupMap = $this->groupMap;
			$orderClass->groupVal = $this->groupVal;
			$orderClass->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderClass->main_pkey = $this->main_pkey;
			}
			$orderClass->order(true);
		}
		return $this->listing();
	}
	function orderup(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = $this->pkey;
			$orderClass->table = $this->table;
			$orderClass->groupMap = $this->groupMap;
			$orderClass->groupVal = $this->groupVal;
			$orderClass->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderClass->main_pkey = $this->main_pkey;
			}
			$orderClass->order(false);
		}
		return $this->listing();
	}
	function saveorder(){
		if(!empty($this->table)&&!empty($this->pkey)&&(empty($this->groupMap)||isset($this->groupVal))&&!empty($this->orderingMap)){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = $this->pkey;
			$orderClass->table = $this->table;
			$orderClass->groupMap = $this->groupMap;
			$orderClass->groupVal = $this->groupVal;
			$orderClass->orderingMap = $this->orderingMap;
			if(!empty($this->main_pkey)){
				$orderClass->main_pkey = $this->main_pkey;
			}
			$orderClass->save();
		}
		return $this->listing();
	}

	function store($new=false){
		if(!HIKASHOP_PHP5) {
			$app =& JFactory::getApplication();
		} else {
			$app = JFactory::getApplication();
		}
		$class = hikashop_get('class.'.$this->type);
		$status = $class->saveForm();
		if($status) {
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			if(!$new) JRequest::setVar( 'cid', $status  );
			else JRequest::setVar( 'cid', 0  );
			JRequest::setVar( 'fail', null  );
		} else {
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
		return $status;
	}

	function remove(){
		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$class = hikashop_get('class.'.$this->type);
		$num = $class->delete($cids);
		if($num){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',count($cids)), 'message');
		}
		return $this->listing();
	}

	function publish(){
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		return $this->_toggle($cid,1);
	}

	function unpublish(){
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		return $this->_toggle($cid,0);
	}

	function _toggle($cid, $publish){
		if (empty( $cid )) {
			JError::raiseWarning( 500, 'No items selected' );
		}
		if(in_array($this->type,array('product','category'))){
			JPluginHelper::importPlugin( 'hikashop' );
			$dispatcher = JDispatcher::getInstance();
			$unset = array();
			$objs = array();
			foreach($cid as $k => $id){
				$element = new stdClass();
				$name = reset($this->toggle);
				$element->$name = $id;
				$publish_name = key($this->toggle);
				$element->$publish_name = (int)$publish;
				$do = true;
				$dispatcher->trigger( 'onBefore'.ucfirst($this->type).'Update', array( & $element, & $do) );
				if(!$do){
					$unset[]=$k;
				}else{
					$objs[$k]=& $element;
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($cid[$u]);
				}
			}
		}
		$cids = implode( ',', $cid );
		$db = JFactory::getDBO();
		$query = 'UPDATE '.hikashop_table($this->type) . ' SET '.key($this->toggle).' = ' . (int)$publish . ' WHERE '.reset($this->toggle).' IN ( '.$cids.' )';
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseWarning( 500, $db->getErrorMsg() );
		}elseif(in_array($this->type,array('product','category'))){
			if(!empty($objs)){
				foreach($objs as $element){
					$dispatcher->trigger( 'onAfter'.ucfirst($this->type).'Update', array( & $element ) );
				}
			}
		}
		$task = $this->publish_return_view;
		return $this->$task();
	}

	function getModel($name = '', $prefix = '', $config = array(),$do=false) {
		if($do) return parent::getModel($name, $prefix , $config);
		return false;
	}

	function authorise($task){
		return $this->authorize($task);
	}

	function authorize($task){
		if(!$this->isIn($task,array('modify_views','add','modify','delete','display'))){
			return false;
		}
		if($this->isIn($task,array('modify','delete')) && !JRequest::checkToken('request')){
			return false;
		}

		$app = JFactory::getApplication();

		if($app->isAdmin()){

			if(method_exists($this,'getACLName')){
				$name = $this->getACLName($task);
			}else{
				$name = $this->getName();
				if(!empty($name)) $name = 'acl_'.$name.'_'.$task;
			}
			if(!empty($name)){
				if(hikashop_level(2)){
					$config =& hikashop_config();
					if($this->isIn($task,array('display'))){
						$task = 'view';
					}elseif($this->isIn($task,array('modify_views','add','modify'))){
						$task = 'manage';
					}elseif($this->isIn($task,array('delete'))){
						$task = 'delete';
					}else{
						return true;
					}

					if(!hikashop_isAllowed($config->get($name,'all'))){
						hikashop_display(JText::_('RESSOURCE_NOT_ALLOWED'),'error');
						return false;
					}
				}
			}
		}

		return true;
	}

	function isIn($task,$lists){
		foreach($lists as $list){
			if(in_array($task,$this->$list)){
				return true;
			}
		}
		return false;
	}

	function execute($task){
		if(substr($task,0,12)=='triggerplug-'){
			JPluginHelper::importPlugin( 'hikashop' );
			$dispatcher = JDispatcher::getInstance();
			$parts = explode('-',$task,2);
			$event = 'onTriggerPlug'.array_pop($parts);
			$dispatcher->trigger( $event, array( ) );
			return true;
		}
		if(HIKASHOP_J30) {
			if(empty($task))
				$task = @$this->taskMap['__default'];
			if(!empty($task) && !$this->authorize($task))
				return JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}
		return parent::execute($task);
	}

	function display($cachable = false, $urlparams = false) {
		if(HIKASHOP_J30) {
			$document = JFactory::getDocument();
			$view = $this->getView('', $document->getType(), '', array('base_path' => $this->basePath));
			if($view->getLayout() == 'default' && JRequest::getString('layout', '') != '')
				$view->setLayout(JRequest::getString('layout'));
		}

		$config =& hikashop_config();
		$menu_style = $config->get('menu_style','title_bottom');
		if(HIKASHOP_J30) $menu_style = 'content_top';
		if($menu_style == 'content_top') {
			$app = JFactory::getApplication();
			if($app->isAdmin() && JRequest::getString('tmpl') !== 'component') {
				echo hikashop_getMenu('',$menu_style);
			}
		}
		return parent::display();
	}

	function getUploadSetting($upload_key, $caller = '') {
		return false;
	}

	function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') { }
}

class hikashopClass extends JObject{
	var $tables = array();
	var $pkeys = array();
	var $namekeys = array();

	function  __construct( $config = array() ){
		$this->database = JFactory::getDBO();
		return parent::__construct($config);
	}
	function save(&$element){
		$pkey = end($this->pkeys);
		if(empty($pkey)){
			$pkey = end($this->namekeys);
		}elseif(empty($element->$pkey)){
			$tmp = end($this->namekeys);
			if(!empty($tmp)){
				if(!empty($element->$tmp)){
					$pkey = $tmp;
				}else{
					$element->$tmp=$this->getNamekey($element);
					if($element->$tmp===false){
						return false;
					}
				}
			}
		}

		if(!empty($this->fields_whitelist) && is_array($this->fields_whitelist) && count($this->fields_whitelist)){
			foreach(get_object_vars($element) as $key => $var){
				if(!in_array($key, $this->fields_whitelist)){
					unset($element->$key);
				}
			}
		}

		if(!HIKASHOP_J16){
			$obj = new JTable($this->getTable(),$pkey,$this->database);
			$obj->setProperties($element);
		}else{
			$obj =& $element;
		}
		if(empty($element->$pkey)){
			$query = $this->_getInsert($this->getTable(),$obj);
			$this->database->setQuery($query);
			$status = $this->database->query();
		}else{
			if(count((array) $element) > 1){
				$status = $this->database->updateObject($this->getTable(),$obj,$pkey);
			}else{
				$status = true;
			}
		}
		if($status){
			return empty($element->$pkey) ? $this->database->insertid() : $element->$pkey;
		}
		return false;
	}

	function getTable(){
		return hikashop_table(end($this->tables));
	}

	function _getInsert($table, &$object, $keyName = null) {
		if(!HIKASHOP_J30){
			$fmtsql = 'INSERT IGNORE INTO '.$this->database->nameQuote($table).' ( %s ) VALUES ( %s ) ';
		} else {
			$fmtsql = 'INSERT IGNORE INTO '.$this->database->quoteName($table).' ( %s ) VALUES ( %s ) ';
		}
		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL or $k[0] == '_') {
				continue;
			}
			if(!HIKASHOP_J30){
				$fields[] = $this->database->nameQuote( $k );
				$values[] = $this->database->isQuoted( $k ) ? $this->database->Quote( $v ) : (int) $v;
			} else {
				$fields[] = $this->database->quoteName( $k );
				$values[] = $this->database->Quote( $v );
			}
		}
		return sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	}


	function delete(&$elementsToDelete){
		if(!is_array($elementsToDelete)){
			$elements = array($elementsToDelete);
		}else{
			$elements = $elementsToDelete;
		}

		$isNumeric = is_numeric(reset($elements));
		$strings = array();
		foreach($elements as $key => $val){
			$strings[$key] = $this->database->Quote($val);
		}

		$columns = $isNumeric ? $this->pkeys : $this->namekeys;

		if(empty($columns) || empty($elements)) return false;

		$otherElements=array();
		$otherColumn='';
		foreach($columns as $i => $column){
			if(empty($column)){
				$query = 'SELECT '.($isNumeric?end($this->pkeys):end($this->namekeys)).' FROM '.$this->getTable().' WHERE '.($isNumeric?end($this->pkeys):end($this->namekeys)).' IN ( '.implode(',',$strings).');';
				$this->database->setQuery($query);
				if(!HIKASHOP_J25){
					$otherElements = $this->database->loadResultArray();
				} else {
					$otherElements = $this->database->loadColumn();
				}
				foreach($otherElements as $key => $val){
					$otherElements[$key] = $this->database->Quote($val);
				}
				break;
			}
		}

		$result = true;
		$tables=array();
		if(empty($this->tables)){
			$tables[0]=$this->getTable();
		}else{
			foreach($this->tables as $i => $oneTable){
				$tables[$i]=hikashop_table($oneTable);
			}
		}
		foreach($tables as $i => $oneTable){
			$column = $columns[$i];
			if(empty($column)){
				$whereIn = ' WHERE '.($isNumeric?$this->namekeys[$i]:$this->pkeys[$i]).' IN ('.implode(',',$otherElements).')';
			}else{
				$whereIn = ' WHERE '.$column.' IN ('.implode(',',$strings).')';
			}
			$query = 'DELETE FROM '.$oneTable.$whereIn;
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
		}
		return $result;
	}

	function get($element,$default=null){
		if(empty($element)) return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		$query = 'SELECT * FROM '.$this->getTable().' WHERE '.$pkey.'  = '.$this->database->Quote($element).' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	public function getRaw($element, $default = null) {
		static $multiTranslation = null;
		if(empty($element))
			return null;
		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		$table = hikashop_table(end($this->tables));
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}
		if($multiTranslation === null) {
			$translationHelper = hikashop_get('helper.translation');
			$multiTranslation = $translationHelper->isMulti(true);
		}
		$query = 'SELECT * FROM '.$table.' WHERE '.$pkey.' = '.$this->database->Quote($element);
		$this->database->setQuery($query, 0, 1);
		if($multiTranslation) {
			$app = JFactory::getApplication();
			if(!$app->isAdmin() && class_exists('JFalangDatabase')) {
				$ret = $this->database->loadObject('stdClass', false);
			} elseif(!$app->isAdmin() && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$ret = $this->database->loadObject(false);
			} else {
				$ret = $this->database->loadObject();
			}
		} else {
			$ret = $this->database->loadObject();
		}
		return $ret;
	}
}

if(!class_exists('hikashopBridgeView')){
	if(!HIKASHOP_J30){
		class hikashopBridgeView extends JView {}
	} else {
		class hikashopBridgeView extends JViewLegacy {}
	}
}

class hikashopView extends hikashopBridgeView {
	var $triggerView = false;
	var $displayView = true;
	var $toolbar = array();
	var $direction = 'ltr';
	var $chosen = true;
	var $extrafilters = array();

	function display($tpl = null) {
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) $this->direction = 'rtl';

		if($this->triggerView) {
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHikashopBeforeDisplayView', array(&$this));
		}

		if(!empty($this->toolbar)) {
			$toolbarHelper = hikashop_get('helper.toolbar');
			$toolbarHelper->process($this->toolbar);
		}

		if(HIKASHOP_J30 && $this->chosen){
			$app = JFactory::getApplication();
			if($app->isAdmin()){
				if($_REQUEST['option']==HIKASHOP_COMPONENT){
					JHTML::_('behavior.framework');
					if(@$_REQUEST['ctrl']!='massaction'){
						JHtml::_('formbehavior.chosen', 'select');
					}
				}
			}else{
				$configClass =& hikashop_config();
				if($configClass->get('bootstrap_forcechosen', 0)){
					JHTML::_('behavior.framework');
					JHtml::_('formbehavior.chosen', 'select');
				}
			}
		}

		if($this->displayView)
			parent::display($tpl);

		if($this->triggerView) {
			$dispatcher->trigger('onHikashopAfterDisplayView', array( &$this));
		}
	}

	function &getPageInfo($default = '', $dir = 'asc', $filters = array()) {
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string');

		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($this->paramBase.'.filter_order', 'filter_order', $default, 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($this->paramBase.'.filter_order_Dir', 'filter_order_Dir',	$dir, 'word');

		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if(JRequest::getVar('search') != $app->getUserState($this->paramBase.'.search')) {
			$app->setUserState($this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		} else {
			$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}

		if(!empty($filters)) {
			$reset = false;
			foreach($filters as $k => $v) {
				$type = 'string';
				if(is_int($v)) $type = 'int';

				if(!$reset) $oldValue = $app->getUserState($this->paramBase.'.filter_'.$k, $v);
				$newValue = $app->getUserStateFromRequest($this->paramBase.'.filter_'.$k, 'filter_'.$k, $v, $type);
				$reset = $reset || ($oldValue != $newValue);
				$pageInfo->filter->$k = $newValue;
			}
			if($reset) {
				$app->setUserState($this->paramBase.'.limitstart',0);
				$pageInfo->limit->start = 0;
			}
		}

		$pageInfo->search = JString::strtolower($app->getUserStateFromRequest($this->paramBase.'.search', 'search', '', 'string'));
		$pageInfo->search = trim($pageInfo->search);

		$pageInfo->elements = new stdClass();

		$this->assignRef('pageInfo', $pageInfo);
		return $pageInfo;
	}

	function getPageInfoTotal($query, $countValue = '*') {
		if(empty($this->pageInfo))
			return false;

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$db->setQuery('SELECT COUNT('.$countValue.') '.$query);
		$this->pageInfo->elements->total = (int)$db->loadResult();
		if((int)$this->pageInfo->limit->start >= $this->pageInfo->elements->total) {
			$this->pageInfo->limit->start = 0;
			$app->setUserState($this->paramBase.'.limitstart', 0);
		}
	}

	function processFilters(&$filters, &$order, $searchMap = array(), $orderingAccept = array()) {
		if(!empty($this->pageInfo->search)) {
			$db = JFactory::getDBO();
			if(!HIKASHOP_J30) {
				$searchVal = '\'%' . $db->getEscaped(JString::strtolower($this->pageInfo->search), true) . '%\'';
			} else {
				$searchVal = '\'%' . $db->escape(JString::strtolower($this->pageInfo->search), true) . '%\'';
			}
			$filters[] = '('.implode(' LIKE '.$searchVal.' OR ',$searchMap).' LIKE '.$searchVal.')';
		}
		if(!empty($filters)) {
			$filters = ' WHERE '. implode(' AND ', $filters);
		} else {
			$filters = '';
		}

		if(!empty($this->pageInfo->filter->order->value)) {
			$t = '';
			if(strpos($this->pageInfo->filter->order->value, '.') !== false)
				list($t,$v) = explode('.', $this->pageInfo->filter->order->value, 2);

			if(empty($orderingAccept) || in_array($t.'.', $orderingAccept) || in_array($this->pageInfo->filter->order->value, $orderingAccept))
				$order = ' ORDER BY '.$this->pageInfo->filter->order->value.' '.$this->pageInfo->filter->order->dir;
		}
	}

	function getPagination($max = 500, $limit = 100) {
		if(empty($this->pageInfo))
			return false;

		if($this->pageInfo->limit->value == $max)
			$this->pageInfo->limit->value = $limit;

		if(HIKASHOP_J30) {
			$pagination = hikashop_get('helper.pagination', $this->pageInfo->elements->total, $this->pageInfo->limit->start, $this->pageInfo->limit->value);
		} else {
			jimport('joomla.html.pagination');
			$pagination = new JPagination($this->pageInfo->elements->total, $this->pageInfo->limit->start, $this->pageInfo->limit->value);
		}

		$this->assignRef('pagination', $pagination);
		return $pagination;
	}

	function getOrdering($value = '', $doOrdering = true) {
		$this->assignRef('doOrdering', $doOrdering);

		$ordering = new stdClass();
		$ordering->ordering = false;

		if($doOrdering) {
			$ordering->ordering = false;
			$ordering->orderUp = 'orderup';
			$ordering->orderDown = 'orderdown';
			$ordering->reverse = false;
			if(!empty($this->pageInfo) && $this->pageInfo->filter->order->value == $value) {
				$ordering->ordering = true;
				if($this->pageInfo->filter->order->dir == 'desc') {
					$ordering->orderUp = 'orderdown';
					$ordering->orderDown = 'orderup';
					$ordering->reverse = true;
				}
			}
		}
		$this->assignRef('ordering', $ordering);

		return $ordering;
	}

	protected function loadRef($refs) {
		foreach($refs as $key => $name) {
			$obj = hikashop_get($name);
			if(!empty($obj))
				$this->assignRef($key, $obj);
			unset($obj);
		}
	}
}

class hikashopPlugin extends JPlugin {
	var $db;
	var $type = 'plugin';
	var $multiple = false;
	var $plugin_params = null;
	var $toolbar = array();

	function __construct(&$subject, $config) {
		$this->db = JFactory::getDBO();
		parent::__construct($subject, $config);
	}

	function pluginParams($id = 0) {
		if(!empty($this->name) && in_array($this->type, array('payment', 'shipping', 'plugin'))) {
			static $pluginsCache = array();
			$key = $this->type.'_'.$this->name.'_'.$id;
			if(!isset($pluginsCache[$key])){
				$query = 'SELECT * FROM '.hikashop_table($this->type).' WHERE '.$this->type.'_type = '.$this->db->Quote($this->name);
				if($id > 0) {
					$query .= ' AND '.$this->type.'_id = ' . (int)$id;
				}
				$this->db->setQuery($query);
				$pluginsCache[$key] = $this->db->loadObject();
			}
			if(!empty($pluginsCache[$key])) {
				$params = $this->type.'_params';
				$this->plugin_params = unserialize($pluginsCache[$key]->$params);
				$this->plugin_data = $pluginsCache[$key];
				return true;
			}
		}
		$this->plugin_params = null;
		$this->plugin_data = null;
		return false;
	}

	function isMultiple() {
		return $this->multiple;
	}

	function configurationHead() {
		return array();
	}

	function configurationLine($id = 0) {
		return null;
	}

	function listPlugins($name, &$values, $full = true, $aclFilter = false) {
		if(!in_array($this->type, array('payment', 'shipping', 'plugin')))
			return;

		if(!$this->multiple) {
			$values['plg.'.$name] = $name;
			return;
		}

		$where = array(
			$this->type.'_type = ' . $this->db->Quote($name),
			$this->type.'_published = 1'
		);

		if(!empty($aclFilter)) {
			$app = JFactory::getApplication();
			if(is_int($aclFilter) && $aclFilter > 0)
				hikashop_addACLFilters($where, $this->type.'_access', '', 2, false, (int)$aclFilter);
			else if(!$app->isAdmin())
				hikashop_addACLFilters($where, $this->type.'_access');
		}
		$where = '('.implode(') AND (', $where).')';

		$key = $this->type.$where;
		static $pluginsCache = array();
		if(!isset($pluginsCache[$key])){
			$query = 'SELECT '.$this->type.'_id as id, '.$this->type.'_name as name FROM '.hikashop_table($this->type).' WHERE '.$where.' ORDER BY '.$this->type.'_ordering';
			$this->db->setQuery($query);
			$pluginsCache[$key] = $this->db->loadObjectList();
		}
		if($full) {
			foreach($pluginsCache[$key] as $plugin) {
				$values['plg.'.$name.'-'.$plugin->id] = $name.' - '.$plugin->name;
			}
		} else {
			foreach($pluginsCache[$key] as $plugin) {
				$values[] = $plugin->id;
			}
		}
	}

	function showPage($name = 'thanks') {
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');

		$folder = 'hikashop';
		if(!empty($this->type) && $this->type != 'plugin')
			$folder .= $this->type;

		$app = JFactory::getApplication();
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.$folder.DS.$this->name.'_'.$name.'.php';
		if(!file_exists($path)) {
			if(version_compare(JVERSION,'1.6','<'))
				$path = JPATH_PLUGINS .DS.$folder.DS.$this->name.'_'.$name.'.php';
			else
				$path = JPATH_PLUGINS .DS.$folder.DS.$this->name.DS.$this->name.'_'.$name.'.php';
		}
		if(!file_exists($path)) {
		}

		if(!file_exists($path))
			return false;
		require($path);
		return true;
	}

	function pluginConfiguration(&$elements) {
		$app = JFactory::getApplication();

		$this->plugins =& $elements;
		$this->pluginName = JRequest::getCmd('name', $this->type);
		$this->pluginView = '';

		$plugin_id = JRequest::getInt('plugin_id',0);
		if($plugin_id == 0) {
			$plugin_id = JRequest::getInt($this->type.'_id', 0);
		}

		if($app->isAdmin()) {
			$this->toolbar = array(
				'save',
				'apply',
				'cancel' => array('name' => 'link', 'icon' => 'cancel', 'alt' => JText::_('HIKA_CANCEL'), 'url' => hikashop_completeLink('plugins')),
			);
			if(!empty($this->doc_form)) {
				$this->toolbar[] = '|';
				$this->toolbar[] = array('name' => 'pophelp', 'target' => $this->type.'-'.$this->doc_form.'-form');
			}
		}


		if(empty($this->title)) {
			$this->title = JText::_('HIKASHOP_PLUGIN_METHOD');
		}
		if($app->isAdmin()) {
			if($plugin_id == 0) {
				hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit');
			} else {
				hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask='.$this->type.'_edit&'.$this->type.'_id='.$plugin_id);
			}
		}
	}

	function pluginMultipleConfiguration(&$elements) {
		if(!$this->multiple)
			return;

		$app = JFactory::getApplication();
		$this->plugins =& $elements;
		$this->pluginName = JRequest::getCmd('name', $this->type);
		$this->pluginView = 'sublisting';
		$this->subtask = JRequest::getCmd('subtask','');
		$this->task = JRequest::getVar('task');

		if(empty($this->title)) { $this->title = JText::_('HIKASHOP_PLUGIN_METHOD'); }

		if($this->subtask == 'copy') {
			if(!in_array($this->task, array('orderup', 'orderdown', 'saveorder'))) {
				$pluginIds = JRequest::getVar('cid', array(), '', 'array');
				JArrayHelper::toInteger($pluginIds);
				$result = true;
				if(!empty($pluginIds) && in_array($this->type, array('payment','shipping'))) {
					$this->db->setQuery('SELECT * FROM '.hikashop_table($this->type).' WHERE '.$this->type.'_id IN ('.implode(',',$pluginIds).')');
					$plugins = $this->db->loadObjectList();
					$helper = hikashop_get('class.'.$this->type);
					$plugin_id = $this->type . '_id';
					foreach($plugins as $plugin) {
						unset($plugin->$plugin_id);
						if(!$helper->save($plugin)) {
							$result = false;
						}
					}
				}
				if($result) {
					$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'message');
					$app->redirect(hikashop_completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName, false, true));
				}
			}
		}

		if($app->isAdmin()) {
			$this->toolbar = array(
				array('name' => 'link', 'icon'=>'new','alt' => JText::_('HIKA_NEW'), 'url' => hikashop_completeLink('plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName.'&subtask=edit')),
				'cancel',
				'|',
				array('name' => 'pophelp', 'target' => 'plugins-'.$this->doc_listing.'sublisting')
			);
			hikashop_setTitle($this->title, 'plugin', 'plugins&plugin_type='.$this->type.'&task=edit&name='.$this->pluginName);
		}

		$this->toggleClass = hikashop_get('helper.toggle');
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination(count($this->plugins), 0, false);
		$this->order = new stdClass();
		$this->order->ordering = true;
		$this->order->orderUp = 'orderup';
		$this->order->orderDown = 'orderdown';
		$this->order->reverse = false;
		$app->setUserState(HIKASHOP_COMPONENT.'.plugin_type.'.$this->type, $this->pluginName);
	}
}

class hikashopPaymentPlugin extends hikashopPlugin {
	var $type = 'payment';
	var $accepted_currencies = array();
	var $doc_form = 'generic';
	var $features = array(
		'authorize_capture' => false,
		'recurring' => false,
		'refund' => false
	);

	function onPaymentDisplay(&$order, &$methods, &$usable_methods) {
		if(empty($methods) || empty($this->name))
			return true;

		if(!empty($order->total)) {
			$currencyClass = hikashop_get('class.currency');
			$null = null;
			$currency_id = intval(@$order->total->prices[0]->price_currency_id);
			$currency = $currencyClass->getCurrencies($currency_id, $null);
			if(!empty($currency) && !empty($this->accepted_currencies) && !in_array(@$currency[$currency_id]->currency_code, $this->accepted_currencies))
				return true;

			$this->currency = $currency;
			$this->currency_id = $currency_id;
		}

		$currencyClass = hikashop_get('class.currency');
		$this->currencyClass = $currencyClass;
		$shippingClass = hikashop_get('class.shipping');
		$volumeHelper = hikashop_get('helper.volume');
		$weightHelper = hikashop_get('helper.weight');

		foreach($methods as $method) {
			if($method->payment_type != $this->name || !$method->enabled || !$method->payment_published)
				continue;

			if(method_exists($this, 'needCC')) {
				$this->needCC($method);
			} else if(!empty($this->ask_cc)) {
				$method->ask_cc = true;
				if(!empty($this->ask_owner))
					$method->ask_owner = true;
				if(!empty($method->payment_params->ask_ccv))
					$method->ask_ccv = true;
			}

			$price = null;

			if(@$method->payment_params->payment_price_use_tax) {
				if(isset($order->order_full_price))
					$price = $order->order_full_price;
				if(isset($order->total->prices[0]->price_value_with_tax))
					$price = $order->total->prices[0]->price_value_with_tax;
				if(isset($order->full_total->prices[0]->price_value_with_tax))
					$price = $order->full_total->prices[0]->price_value_with_tax;
				if(isset($order->full_total->prices[0]->price_value_without_payment_with_tax))
					$price = $order->full_total->prices[0]->price_value_without_payment_with_tax;
			} else {
				if(isset($order->order_full_price))
					$price = $order->order_full_price;
				if(isset($order->total->prices[0]->price_value))
					$price = $order->total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value))
					$price = $order->full_total->prices[0]->price_value;
				if(isset($order->full_total->prices[0]->price_value_without_payment))
					$price = $order->full_total->prices[0]->price_value_without_payment;
			}

			if(!empty($method->payment_params->payment_min_price) && hikashop_toFloat($method->payment_params->payment_min_price) > $price) {
				$method->errors['min_price'] = (hikashop_toFloat($method->payment_params->payment_min_price) - $price);
				continue;
			}

			if(!empty($method->payment_params->payment_max_price) && hikashop_toFloat($method->payment_params->payment_max_price) < $price){
				$method->errors['max_price'] = ($price - hikashop_toFloat($method->payment_params->payment_max_price));
				continue;
			}

			if(!empty($method->payment_params->payment_max_volume) && bccomp((float)@$method->payment_params->payment_max_volume, 0, 3)) {
				$method->payment_params->payment_max_volume_orig = $method->payment_params->payment_max_volume;
				$method->payment_params->payment_max_volume = $volumeHelper->convert($method->payment_params->payment_max_volume, @$method->payment_params->payment_size_unit);
				if($method->payment_params->payment_max_volume < $order->volume){
					$method->errors['max_volume'] = ($method->payment_params->payment_max_volume - $order->volume);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_volume) && bccomp((float)@$method->payment_params->payment_min_volume, 0, 3)) {
				$method->payment_params->payment_min_volume_orig = $method->payment_params->payment_min_volume;
				$method->payment_params->payment_min_volume = $volumeHelper->convert($method->payment_params->payment_min_volume, @$method->payment_params->payment_size_unit);
				if($method->payment_params->payment_min_volume > $order->volume){
					$method->errors['min_volume'] = ($order->volume - $method->payment_params->payment_min_volume);
					continue;
				}
			}

			if(!empty($method->payment_params->payment_max_weight) && bccomp((float)@$method->payment_params->payment_max_weight, 0, 3)) {
				$method->payment_params->payment_max_weight_orig = $method->payment_params->payment_max_weight;
				$method->payment_params->payment_max_weight = $weightHelper->convert($method->payment_params->payment_max_weight, @$method->payment_params->payment_weight_unit);
				if($method->payment_params->payment_max_weight < $order->weight){
					$method->errors['max_weight'] = ($method->payment_params->payment_max_weight - $order->weight);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_weight) && bccomp((float)@$method->payment_params->payment_min_weight,0,3)){
				$method->payment_params->payment_min_weight_orig = $method->payment_params->payment_min_weight;
				$method->payment_params->payment_min_weight = $weightHelper->convert($method->payment_params->payment_min_weight, @$method->payment_params->payment_weight_unit);
				if($method->payment_params->payment_min_weight > $order->weight){
					$method->errors['min_weight'] = ($order->weight - $method->payment_params->payment_min_weight);
					continue;
				}
			}

			if(!empty($method->payment_params->payment_max_quantity) && (int)$method->payment_params->payment_max_quantity) {
				if($method->payment_params->payment_max_quantity < $order->total_quantity){
					$method->errors['max_quantity'] = ($method->payment_params->payment_max_quantity - $order->total_quantity);
					continue;
				}
			}
			if(!empty($method->payment_params->payment_min_quantity) && (int)$method->payment_params->payment_min_quantity){
				if($method->payment_params->payment_min_quantity > $order->total_quantity){
					$method->errors['min_quantity'] = ($order->total_quantity - $method->payment_params->payment_min_quantity);
					continue;
				}
			}

			$method->features = $this->features;

			if(!$this->checkPaymentDisplay($method, $order))
				continue;

			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['recurring']) && empty($method->features['recurring']))
				continue;
			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['term']) && empty($method->features['authorize_capture']))
				continue;
			if(!empty($order->paymentOptions) && !empty($order->paymentOptions['refund']) && empty($method->features['refund']))
				continue;

			if((int)$method->payment_ordering > 0 && !isset($usable_methods[(int)$method->payment_ordering]))
				$usable_methods[(int)$method->payment_ordering] = $method;
			else
				$usable_methods[] = $method;
		}

		return true;
	}

	function onPaymentSave(&$cart, &$rates, &$payment_id) {
		$usable = array();
		$this->onPaymentDisplay($cart, $rates, $usable);
		$payment_id = (int)$payment_id;

		foreach($usable as $usable_method) {
			if($usable_method->payment_id == $payment_id)
				return $usable_method;
		}

		return false;
	}

	function onPaymentConfiguration(&$element) {
		$this->pluginConfiguration($element);

		if(empty($element) || empty($element->payment_type)) {
			$element = new stdClass();
			$element->payment_type = $this->pluginName;
			$element->payment_params= new stdClass();
			$this->getPaymentDefaultValues($element);
		}

		$this->order_statuses = hikashop_get('type.categorysub');
		$this->order_statuses->type = 'status';
		$this->currency = hikashop_get('type.currency');
		$this->weight = hikashop_get('type.weight');
		$this->volume = hikashop_get('type.volume');
	}

	function onPaymentConfigurationSave(&$element) {
		if(empty($this->pluginConfig))
			return true;
		$formData = JRequest::getVar('data', array(), '', 'array', JREQUEST_ALLOWRAW);
		if(!isset($formData['payment']['payment_params']))
			return true;
		foreach($this->pluginConfig as $key => $config) {
			if($config[1] == 'textarea' || $config[1] == 'big-textarea') {
				$element->payment_params->$key = @$formData['payment']['payment_params'][$key];
			}
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return true;

		if(empty($order->order_payment_method) || $order->order_payment_method != $this->name)
			return true;

		if(!empty($order->order_type) && $order->order_type != 'sale')
			return true;

		$this->loadOrderData($order);
		$this->loadPaymentParams($order);
		if(empty($this->payment_params)) {
			$do = false;
			return true;
		}
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		$method = $methods[$method_id];
		$this->payment_params =& $method->payment_params;
		$this->payment_name = $method->payment_name;
		$this->loadOrderData($order);
	}

	function onPaymentNotification(&$statuses) {
	}

	function onOrderPaymentCapture(&$order, $total) { return false; }

	function onOrderAuthorizationCancel(&$order) { return false; }

	function onOrderAuthorizationRenew(&$order) { return false; }

	function onOrderPaymentRefund(&$order, $total) { return false; }

	function getOrder($order_id) {
		$ret = null;
		if(empty($order_id))
			return $ret;
		$orderClass = hikashop_get('class.order');
		$ret = $orderClass->get($order_id);
		return $ret;
	}

	function modifyOrder(&$order_id, $order_status, $history = null, $email = null, $payment_params = null) {
		if(is_object($order_id)) {
			$order =& $order_id;
		} else {
			$order = new stdClass();
			$order->order_id = $order_id;
		}

		if($order_status !== null)
			$order->order_status = $order_status;

		$history_notified = 0;
		$history_amount = '';
		$history_data = '';
		$history_type = '';
		if(!empty($history)) {
			if($history === true) {
				$history_notified = 1;
			} else if(is_array($history)) {
				$history_notified = (int)@$history['notified'];
				$history_amount = @$history['amount'];
				$history_data = @$history['data'];
				$history_type = @$history['type'];
			} else {
				$history_notified = (int)@$history->notified;
				$history_amount = @$history->amount;
				$history_data = @$history->data;
				$history_type = @$history->type;
			}
		}

		$order->history = new stdClass();
		$order->history->history_reason = JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
		$order->history->history_notified = $history_notified;
		$order->history->history_payment_method = $this->name;
		$order->history->history_type = 'payment';
		if(!empty($history_amount))
			$order->history->history_amount = $history_amount;
		if(!empty($history_data))
			$order->history->history_data = $history_data;
		if(!empty($history_type))
			$order->history->history_type = $history_type;

		if($payment_params !== null) {
			if(isset($order->order_payment_params)) {
				foreach($payment_params as $k => $v) {
					$order->order_payment_params->$k = $v;
				}
			} else {
				$order->order_payment_params = $payment_params;
			}
		}

		if(!is_object($order_id) && $order_id !== false) {
			$orderClass = hikashop_get('class.order');
			$orderClass->save($order);
		}

		$mailer = JFactory::getMailer();
		$config =& hikashop_config();

		$recipients = trim($config->get('payment_notification_email', ''));
		if(empty($email) || empty($recipients))
			return;

		$sender = array(
			$config->get('from_email'),
			$config->get('from_name')
		);
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',', $recipients));

		$payment_status = $order_status;
		$mail_status = hikashop_orderStatus($order_status);
		$order_number = '';

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		if(is_object($order_id)) {
			$subject = JText::sprintf('PAYMENT_NOTIFICATION', $this->name, $payment_status);
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing'. $this->url_itemid;
			if(isset($order->order_number))
				$order_number = $order->order_number;
		} elseif($order_id !== false) {
			$dbOrder = $orderClass->get($order_id);
			$order_number = $dbOrder->order_number;
			$subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', $this->name, $payment_status, $order_number);
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id . $this->url_itemid;
		}

		$order_text = '';
		if(is_string($email))
			$order_text = "\r\n\r\n" . $email;

		$body = str_replace('<br/>', "\r\n", JText::sprintf('PAYMENT_NOTIFICATION_STATUS', $this->name, $payment_status)) . ' ' .
			JText::sprintf('ORDER_STATUS_CHANGED', $mail_status) .
			"\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $order_number, HIKASHOP_LIVE).
			"\r\n".str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url)) . $order_text;

		if(is_object($email)) {
			if(!empty($email->subject))
				$subject = $email->subject;
			if(!empty($email->body))
				$body = $email->body;
		}

		$mailer->setSubject($subject);
		$mailer->setBody($body);
		$mailer->Send();
	}

	function loadOrderData(&$order) {
		$this->app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$currencyClass = hikashop_get('class.currency');
		$cartClass = hikashop_get('class.cart');

		$this->currency = 0;
		if(!empty($order->order_currency_id)) {
			$currencies = null;
			$currencies = $currencyClass->getCurrencies($order->order_currency_id, $currencies);
			$this->currency = $currencies[$order->order_currency_id];
		}

		hikashop_loadUser(true, true);
		$this->user = hikashop_loadUser(true);

		$this->locale = strtolower(substr($lang->get('tag'), 0, 2));

		global $Itemid;
		$this->url_itemid = empty($Itemid) ? '' : '&Itemid=' . $Itemid;

		$billing_address = $this->app->getUserState(HIKASHOP_COMPONENT.'.billing_address');
		if(!empty($billing_address))
			$cartClass->loadAddress($order->cart, $billing_address, 'object', 'billing');

		$shipping_address = $this->app->getUserState(HIKASHOP_COMPONENT.'.shipping_address');
		if(!empty($shipping_address))
			$cartClass->loadAddress($order->cart, $shipping_address, 'object', 'shipping');
	}

	function loadPaymentParams(&$order) {
		$payment_id = @$order->order_payment_id;
		$this->payment_params = null;
		if(!empty($order->order_payment_method) && $order->order_payment_method == $this->name && !empty($payment_id) && $this->pluginParams($payment_id))
			$this->payment_params =& $this->plugin_params;
	}

	function ccLoad($ccv = true) {
		if(!isset($this->app))
			$this->app = JFactory::getApplication();
		$this->cc_number = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_number');
		if(!empty($this->cc_number)) $this->cc_number = base64_decode($this->cc_number);

		$this->cc_month = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_month');
		if(!empty($this->cc_month)) $this->cc_month = base64_decode($this->cc_month);

		$this->cc_year = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_year');
		if(!empty($this->cc_year)) $this->cc_year = base64_decode($this->cc_year);

		$this->cc_type = $this->app->getUserState( HIKASHOP_COMPONENT.'.cc_type');
		if(!empty($this->cc_type)){
			$this->cc_type = base64_decode($this->cc_type);
		}
		$this->cc_owner = $this->app->getUserState( HIKASHOP_COMPONENT.'.cc_owner');
		if(!empty($this->cc_owner)){
			$this->cc_owner = base64_decode($this->cc_owner);
		}
		$this->cc_CCV = '';
		if($ccv) {
			$this->cc_CCV = $this->app->getUserState(HIKASHOP_COMPONENT.'.cc_CCV');
			if(!empty($this->cc_CCV)) $this->cc_CCV = base64_decode($this->cc_CCV);
		}
	}

	function ccClear() {
		if(!isset($this->app))
			$this->app = JFactory::getApplication();
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_number', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_month', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_year', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_type', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_owner', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_CCV', '');
		$this->app->setUserState(HIKASHOP_COMPONENT.'.cc_valid', 0);
	}

	function cronCheck() {
		if(empty($this->name))
			return false;

		$pluginsClass = hikashop_get('class.plugins');
		$type = 'hikashop';
		if($this->type == 'payment')
			$type = 'hikashoppayment';
		if($this->type == 'shipping')
			$type = 'hikashopshipping';
		$plugin = $pluginsClass->getByName($type, $this->name);
		if(empty($plugin))
			return false;
		if(empty($plugin->params['period']))
			$plugin->params['period'] = 7200; // 2 hours

		if(!empty($plugin->params['last_cron_update']) && ((int)$plugin->params['last_cron_update'] + (int)$plugin->params['period']) > time())
			return false;

		$plugin->params['last_cron_update'] = time();
		$pluginsClass->save($plugin);
		return true;
	}

	function renewalOrdersAuthorizations(&$messages) {
		$db = JFactory::getDBO();

		$date = hikashop_getDate(time(), '%Y/%m/%d');
		$search = hikashop_getEscaped('s:18:"payment_auth_renew";s:10:"'.$date.'";');
		$query = 'SELECT * FROM '.hikashop_table('order').
				' WHERE order_type = \'sale\' AND order_payment_method = '.$db->Quote($this->name).' AND order_payment_params LIKE \'%'.$search.'%\''.
				' ORDER BY order_payment_id';
		$db->setQuery($query);
		$orders = $db->loadObjectList();
		if(!empty($orders)) {
			$cpt = 0;
			foreach($orders as $order) {
				$order->order_payment_params = unserialize($order->order_payment_params);
				$ret = $this->onOrderAuthorizationRenew($order);

				if($ret) {
					$order_payment_params = serialize($order->order_payment_params);
					$query = 'UPDATE '.hikashop_table('order').' SET order_payment_params = '.$db->quote($order_payment_params).' WHERE order_id = '.(int)$order->order_id;
					$db->setQuery($query);
					$db->query();

					$cpt++;
				}

				unset($order_payment_params);
				unset($order->order_payment_params);
				unset($order);
			}

			if($cpt > 0)
				$messages[] = '['.ucfirst($this->name).'] '.JText::_sprintf('X_ORDERS_AUTHORIZATION_RENEW', $cpt);
		}
	}

	function writeToLog($data = null) {
		$dbg = ($data === null) ? ob_get_clean() : $data;
		if(!empty($dbg)) {
			$dbg = '-- ' . date('m.d.y H:i:s') . ' --'. (empty($this->name) ? ('['.$this->name.']') : '') . "\r\n" . $dbg;

			jimport('joomla.filesystem.file');
			$config = hikashop_config();
			$file = $config->get('payment_log_file', '');
			$file = rtrim(JPath::clean(html_entity_decode($file)), DS . ' ');
			if(!preg_match('#^([A-Z]:)?/.*#',$file) && (!$file[0] == '/' || !file_exists($file)))
				$file = JPath::clean(HIKASHOP_ROOT . DS . trim($file, DS . ' '));
			if(!empty($file) && defined('FILE_APPEND')) {
				if(!file_exists(dirname($file))) {
					jimport('joomla.filesystem.folder');
					JFolder::create(dirname($file));
				}
				file_put_contents($file, $dbg, FILE_APPEND);
			}
		}
		if($data === null)
			ob_start();
	}

	function getPaymentDefaultValues(&$element){}

	function checkPaymentDisplay(&$method, &$order) { return true; }
}

class hikashopShippingPlugin extends hikashopPlugin {
	var $type = 'shipping';
	var $use_cache = true;

	function onShippingDisplay(&$order, &$dbrates, &$usable_rates, &$messages) {
		$config =& hikashop_config();
		if(!$config->get('force_shipping') && bccomp(@$order->weight, 0, 5) <= 0)
			return false;
		if(empty($dbrates) || empty($this->name))
			return false;

		$rates = array();
		foreach($dbrates as $k => $rate) {
			if($rate->shipping_type == $this->name && !empty($rate->shipping_published)) {
				$rates[] = $rate;
			}
		}
		if(empty($rates))
			return false;

		if($this->use_cache) {
			if($this->loadShippingCache($order, $usable_rates, $messages))
				return true;
			$local_cache_shipping = array();
			$local_cache_errors = array();
		}

		$currencyClass = hikashop_get('class.currency');
		$shippingClass = hikashop_get('class.shipping');
		$this->volumeHelper = hikashop_get('helper.volume');
		$this->weightHelper = hikashop_get('helper.weight');

		foreach($rates as &$rate) {
			$rate->shippingkey = $shippingClass->getShippingProductsData($order, $order->products);
			$shipping_prices = $order->shipping_prices[$rate->shippingkey];

			if(!isset($rate->shipping_params->shipping_price_use_tax)) $rate->shipping_params->shipping_price_use_tax = 1;

			if(!isset($rate->shipping_params->shipping_virtual_included) || $rate->shipping_params->shipping_virtual_included) {
				if($rate->shipping_params->shipping_price_use_tax)
					$price = $shipping_prices->all_with_tax;
				else
					$price = $shipping_prices->all_without_tax;
			} else {
				if($rate->shipping_params->shipping_price_use_tax)
					$price = $shipping_prices->real_with_tax;
				else
					$price = $shipping_prices->real_without_tax;
			}

			if(bccomp($price, 0, 5) && isset($rate->shipping_params->shipping_percentage) && bccomp($rate->shipping_params->shipping_percentage, 0, 3))
				$rate->shipping_price = $currencyClass->round($rate->shipping_price + $price * $rate->shipping_params->shipping_percentage / 100, $currencyClass->getRounding($rate->shipping_currency_id, true));
			else
				$rate->shipping_price = $currencyClass->round($rate->shipping_price, $currencyClass->getRounding($rate->shipping_currency_id, true));
			if(!empty($rate->shipping_params->shipping_min_price) && hikashop_toFloat($rate->shipping_params->shipping_min_price) > $price)
				$rate->errors['min_price'] = (hikashop_toFloat($rate->shipping_params->shipping_min_price) - $price);

			if(!empty($rate->shipping_params->shipping_max_price) && hikashop_toFloat($rate->shipping_params->shipping_max_price) < $price)
				$rate->errors['max_price'] = ($price - hikashop_toFloat($rate->shipping_params->shipping_max_price));

			if(!empty($rate->shipping_params->shipping_max_volume) && bccomp((float)@$rate->shipping_params->shipping_max_volume, 0, 3)) {
				$rate->shipping_params->shipping_max_volume_orig = $rate->shipping_params->shipping_max_volume;
				$rate->shipping_params->shipping_max_volume = $this->volumeHelper->convert($rate->shipping_params->shipping_max_volume, @$rate->shipping_params->shipping_size_unit);
				if($rate->shipping_params->shipping_max_volume < $shipping_prices->volume)
					$rate->errors['max_volume'] = ($rate->shipping_params->shipping_max_volume - $shipping_prices->volume);
			}
			if(!empty($rate->shipping_params->shipping_min_volume) && bccomp((float)@$rate->shipping_params->shipping_min_volume, 0, 3)) {
				$rate->shipping_params->shipping_min_volume_orig = $rate->shipping_params->shipping_min_volume;
				$rate->shipping_params->shipping_min_volume = $this->volumeHelper->convert($rate->shipping_params->shipping_min_volume, @$rate->shipping_params->shipping_size_unit);
				if($rate->shipping_params->shipping_min_volume > $shipping_prices->volume)
					$rate->errors['min_volume'] = ($shipping_prices->volume - $rate->shipping_params->shipping_min_volume);
			}

			if(!empty($rate->shipping_params->shipping_max_weight) && bccomp((float)@$rate->shipping_params->shipping_max_weight, 0, 3)) {
				$rate->shipping_params->shipping_max_weight_orig = $rate->shipping_params->shipping_max_weight;
				$rate->shipping_params->shipping_max_weight = $this->weightHelper->convert($rate->shipping_params->shipping_max_weight, @$rate->shipping_params->shipping_weight_unit);
				if($rate->shipping_params->shipping_max_weight < $shipping_prices->weight)
					$rate->errors['max_weight'] = ($rate->shipping_params->shipping_max_weight - $shipping_prices->weight);
			}
			if(!empty($rate->shipping_params->shipping_min_weight) && bccomp((float)@$rate->shipping_params->shipping_min_weight,0,3)){
				$rate->shipping_params->shipping_min_weight_orig = $rate->shipping_params->shipping_min_weight;
				$rate->shipping_params->shipping_min_weight = $this->weightHelper->convert($rate->shipping_params->shipping_min_weight, @$rate->shipping_params->shipping_weight_unit);
				if($rate->shipping_params->shipping_min_weight > $shipping_prices->weight)
					$rate->errors['min_weight'] = ($shipping_prices->weight - $rate->shipping_params->shipping_min_weight);
			}

			if(!empty($rate->shipping_params->shipping_max_quantity) && (int)$rate->shipping_params->shipping_max_quantity) {
				if($rate->shipping_params->shipping_max_quantity < $shipping_prices->total_quantity)
					$rate->errors['max_quantity'] = ($rate->shipping_params->shipping_max_quantity - $shipping_prices->total_quantity);
			}
			if(!empty($rate->shipping_params->shipping_min_quantity) && (int)$rate->shipping_params->shipping_min_quantity){
				if($rate->shipping_params->shipping_min_quantity > $shipping_prices->total_quantity)
					$rate->errors['min_quantity'] = ($shipping_prices->total_quantity - $rate->shipping_params->shipping_min_quantity);
			}

			if(isset($rate->shipping_params->shipping_per_product) && $rate->shipping_params->shipping_per_product) {
				if(!isset($order->shipping_prices[$rate->shippingkey]->price_per_product)){
					$order->shipping_prices[$rate->shippingkey]->price_per_product = array();
				}
				$order->shipping_prices[$rate->shippingkey]->price_per_product[$rate->shipping_id] = array(
					'price' => (float)$rate->shipping_params->shipping_price_per_product,
					'products' => array()
				);
			}

			unset($rate);
		}

		foreach($order->shipping_prices as $key => $shipping_price) {
			if(!empty($shipping_price->price_per_product) && !empty($shipping_price->products)) {
				$shipping_ids = array_keys($shipping_price->price_per_product);
				JArrayHelper::toInteger($shipping_ids);
				$product_ids = array_keys($shipping_price->products);
				JArrayHelper::toInteger($product_ids);
				$query = 'SELECT a.shipping_id, a.shipping_price_ref_id as `ref_id`, a.shipping_price_min_quantity as `min_quantity`, a.shipping_price_value as `price`, a.shipping_fee_value as `fee` '.
					' FROM ' . hikashop_table('shipping_price') . ' AS a '.
					' WHERE a.shipping_id IN (' . implode(',', $shipping_ids) . ') '.
					' AND a.shipping_price_ref_id IN (' . implode(',', $product_ids) . ') AND a.shipping_price_ref_type = \'product\' '.
					' ORDER BY a.shipping_id, a.shipping_price_ref_id, a.shipping_price_min_quantity';
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$ret = $db->loadObjectList();
				if(!empty($ret)) {
					foreach($ret as $ship) {
						if($ship->min_quantity <= $shipping_price->products[$ship->ref_id]) {
							$order->shipping_prices[$key]->price_per_product[$ship->shipping_id]['products'][$ship->ref_id] = ($ship->price * $shipping_price->products[$ship->ref_id]) + $ship->fee;
						}
					}
				}
			}
		}

		foreach($rates as &$rate) {
			if(!isset($rate->shippingkey))
				continue;

			$shipping_prices =& $order->shipping_prices[$rate->shippingkey];

			if(isset($shipping_prices->price_per_product[$rate->shipping_id]) && !empty($order->products)) {
				$rate_prices =& $order->shipping_prices[$rate->shippingkey]->price_per_product[$rate->shipping_id];

				$price = 0;
				foreach($order->products as $k => $row) {
					if(!empty($rate->products) && !in_array($row->product_id, $rate->products))
						continue;

					if(isset($rate_prices['products'][$row->product_id])) {
						$price += $rate_prices['products'][$row->product_id];
						$rate_prices['products'][$row->product_id] = 0;
					} elseif(isset($rate_prices['products'][$row->product_parent_id])) {
						$price += $rate_prices['products'][$row->product_parent_id];
						$rate_prices['products'][$row->product_parent_id] = 0;
					} elseif(!isset($rate->shipping_params->shipping_virtual_included) || $rate->shipping_params->shipping_virtual_included || $row->product_weight > 0) {
						$price += $rate_prices['price'] * $row->cart_product_quantity;
					}
				}
				if($price > 0) {
					if(!isset($rate->shipping_price_base))
						$rate->shipping_price_base = hikashop_toFloat($rate->shipping_price);
					else
						$rate->shipping_price = $rate->shipping_price_base;
					$rate->shipping_price = $currencyClass->round($rate->shipping_price + $price, $currencyClass->getRounding($rate->shipping_currency_id, true));
				}
				if($price < 0) {
					if(!isset($rate->errors['product_excluded']))
						$rate->errors['product_excluded'] = 0;
					$rate->errors['product_excluded']++;
				}
				unset($rate_prices);
			}

			unset($shipping_prices);

			if(empty($rate->errors)) {
				$usable_rates[$rate->shipping_id] = $rate;
				if($this->use_cache)
					$local_cache_shipping[$rate->shipping_id] = $rate;
			} else {
				$messages[] = $rate->errors;
				if($this->use_cache)
					$local_cache_errors[] = $rate->errors;
			}
		}
		if($this->use_cache)
			$this->setShippingCache($order, $local_cache_shipping, $local_cache_errors);

		return true;
	}

	function onShippingSave(&$cart, &$methods, &$shipping_id, $warehouse_id = null) {
		$usable_methods = array();
		$errors = array();
		$shipping = hikashop_get('class.shipping');
		$usable_methods = $shipping->getShippings($cart);
		if(is_numeric($warehouse_id)) $warehouse_id = (int)$warehouse_id;

		foreach($usable_methods as $k => $usable_method) {
			if(is_numeric($usable_method->shipping_warehouse_id)) $usable_method->shipping_warehouse_id = (int)$usable_method->shipping_warehouse_id;
			if(($usable_method->shipping_id == $shipping_id) && ($warehouse_id === null || (isset($usable_method->shipping_warehouse_id) && $usable_method->shipping_warehouse_id === $warehouse_id)))
				return $usable_method;
		}
		return false;
	}

	function onShippingConfiguration(&$element) {
		$this->pluginConfiguration($element);

		if(empty($element) || empty($element->shipping_type)) {
			$element = new stdClass();
			$element->shipping_type = $this->pluginName;
			$element->shipping_params = new stdClass();
			$this->getShippingDefaultValues($element);
		}

		$this->currency = hikashop_get('type.currency');
		$this->weight = hikashop_get('type.weight');
		$this->volume = hikashop_get('type.volume');
	}

	function onShippingConfigurationSave(&$element) {
		if(!empty($this->pluginConfig)) {
			$formData = JRequest::getVar('data', array(), '', 'array', JREQUEST_ALLOWRAW);
			if(isset($formData['shipping']['shipping_params'])) {
				foreach($this->pluginConfig as $key => $config) {
					if($config[1] == 'textarea' || $config[1] == 'big-textarea') {
						$element->shipping_params->$key = @$formData['shipping']['shipping_params'][$key];
					}
				}
			}
		}
		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		return true;
	}

	function getShippingCache(&$order) {
		if(empty($this->name) || empty($order->cache->shipping) || empty($order->cache->shipping_key))
			return false;
		$key = $order->cache->shipping_key;
		if(empty($order->cache->shipping[$key]))
			return false;
		if(isset($order->shipping_warehouse_id)) {
			if(isset($order->cache->shipping[$key][(int)$order->shipping_warehouse_id][$this->name]))
				return $order->cache->shipping[$key][(int)$order->shipping_warehouse_id][ $this->name ];
			return false;
		}
		if(isset($order->cache->shipping[$key][$this->name]))
			return $order->cache->shipping[$key][ $this->name ];
		return false;
	}

	function loadShippingCache(&$order, &$usable_rates, &$messages) {
		$cache = $this->getShippingCache($order);
		if($cache === false)
			return false;

		list($methods, $msg) = $cache;
		if(!empty($methods)) {
			foreach($methods as $i => $rate) {
				$usable_rates[$rate->shipping_id] = $rate;
			}
		}
		if(!empty($msg)) {
			foreach($msg as $i => $err) {
				$messages[] = $err;
			}
		}
		return true;
	}

	function setShippingCache(&$order, $data, $messages = null) {
		if(empty($this->name) || empty($order->cache->shipping_key))
			return false;
		$key = $order->cache->shipping_key;

		if(empty($order->cache->shipping)) $order->cache->shipping = array();
		if(empty($order->cache->shipping[$key])) $order->cache->shipping[$key] = array();

		if(isset($order->shipping_warehouse_id)) {
			if(empty($order->cache->shipping[$key][(int)$order->shipping_warehouse_id]))
				$order->cache->shipping[$key][(int)$order->shipping_warehouse_id] = array();
			$order->cache->shipping[$key][(int)$order->shipping_warehouse_id][$this->name] = array($data, $messages);
			return true;
		}
		$order->cache->shipping[$key][ $this->name ] = array($data, $messages);
		return false;
	}

	function getShippingAddress($id = 0) {
		$app = JFactory::getApplication();
		if($id == 0 && !$app->isAdmin()) {
			$id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			if(!empty($id) && is_array($id))
				$id = (int)reset($id);
			else
				$id = 0;
		}elseif(is_array($id)){
			$id = (int)reset($id);
		}

		if(empty($id))
			return false;

		$shippingClass = hikashop_get('class.shipping');
		$shipping = $shippingClass->get($id);
		if($shipping->shipping_type != $this->name)
			return false;

		$params = unserialize($shipping->shipping_params);
		$override = 0;
		if(isset($params->shipping_override_address)) {
			$override = (int)$params->shipping_override_address;
		}

		switch($override) {
			case 4:
				if(!empty($params->shipping_override_address_text))
					return $params->shipping_override_address_text;
				break;
			case 3:
				if(!empty($params->shipping_override_address_text))
					return str_replace(array("\r\n","\n","\r"),"<br/>", htmlentities($params->shipping_override_address_text, ENT_COMPAT, 'UTF-8') );
				break;
			case 2:
				return '';
			case 1:
				$config =& hikashop_config();
				return str_replace(array("\r\n","\n","\r"),"<br/>", $config->get('store_address'));
			case 0:
			default:
				return false;
		}
		return false;
	}

	function getShippingDefaultValues(&$element) {}

	function getOrderPackage(&$order, $options = array()) {
		$ret = array();
		if(empty($order->products))
			return array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0);

		$weight_unit = !empty($order->weight_unit) ? $order->weight_unit : 'lb';
		$volume_unit = !empty($order->volume_unit) ? $order->volume_unit : 'in';

		if(!empty($options['weight_unit']))
			$weight_unit = $options['weight_unit'];
		if(!empty($options['volume_unit']))
			$volume_unit = $options['volume_unit'];

		$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0);
		$error = false;
		foreach($order->products as $k => $product) {
			$qty = 1;
			if(isset($product->cart_product_quantity))
				$qty = (int)$product->cart_product_quantity;
			if(isset($product->order_product_quantity))
				$qty = (int)$product->order_product_quantity;

			if($qty == 0)
				continue;

			$weight = 0;
			if($product->product_weight_unit == $weight_unit) {
				$weight += ((float)$product->product_weight);
			} else if(!empty($product->product_weight_unit_orig) && $product->product_weight_unit_orig == $weight_unit) {
				$weight += ((float)hikashop_toFloat($product->product_weight_orig));
			} else {
				if(empty($this->weightHelper))
					$this->weightHelper = hikashop_get('helper.weight');
				$weight += ((float)$this->weightHelper->convert($product->product_weight, $product->product_weight_unit, $weight_unit));
			}

			if($weight == 0)
				continue;

			$w = (float)hikashop_toFloat($product->product_width);
			$h = (float)hikashop_toFloat($product->product_height);
			$l = (float)hikashop_toFloat($product->product_length);
			if($product->product_dimension_unit !== $volume_unit) {
				if(empty($this->volumeHelper))
					$this->volumeHelper = hikashop_get('helper.volume');
				if(!empty($w))
					$w = $this->volumeHelper->convert($w, $product->product_dimension_unit, $volume_unit, 'dimension');
				if(!empty($h))
					$h = $this->volumeHelper->convert($h, $product->product_dimension_unit, $volume_unit, 'dimension');
				if(!empty($l))
					$l = $this->volumeHelper->convert($l, $product->product_dimension_unit, $volume_unit, 'dimension');
			}

			$d = array($w,$h,$l);
			sort($d); // x = d[0] // y = d[1] // z = d[2]
			$p = array(
				'w' => $weight,
				'x' => $d[0],
				'y' => $d[1],
				'z' => $d[2]
			);

			if(!empty($options['required_dimensions'])) {
				if(!$this->checkDimensions($product, $p, $options['required_dimensions'])) {
					$error = true;
					continue;
				}
			}
			if(!empty($options['limit'])) {
				$total_quantity = $qty;
				while ($total_quantity > 0) {
					foreach ($options['limit'] as $limit_key => $limit_value) {
						$valid = $this->processPackageLimit($limit_key, $limit_value , $p, $total_quantity, $current, array('weight' => $weight_unit, 'volume' => $volume_unit));

						if ($valid === false)
							$total_quantity = 0;
						else if (is_int($valid))
							$total_quantity = min($total_quantity, $valid);

						if ($total_quantity === 0)
							break;
					}

					if ($total_quantity === 0) {
						if(empty($current['w']) && empty($current['x']) && empty($current['y']) && empty($current['z']))
							return false;

						$ret[] = $current;
						$total_quantity = $qty;
						$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0);
					} else if($total_quantity < $qty) {

						$factor = 1;
						if(empty($current['w']) && empty($current['x']) && empty($current['y']) && empty($current['z']) && $total_quantity*2 <= $qty)
							$factor = floor($qty / $total_quantity);

						$current['w'] += $weight * $total_quantity;
						$current['x'] += ($d[0] * $total_quantity);
						$current['y'] = max($current['y'], $d[1]);
						$current['z'] = max($current['z'], $d[2]);
						$ret[] = $current;

						for($i = 1; $i < $factor; $i++) {
							$ret[] = $current;
						}

						$current = array('w' => 0, 'x' => 0, 'y' => 0, 'z' => 0);
						$qty -= $total_quantity * $factor;
						$total_quantity = $qty;
					} else
						$total_quantity = 0;
				}
			}

			$current['w'] += $weight * $qty;
			$current['x'] += ($d[0] * $qty);
			$current['y'] = max($current['y'], $d[1]);
			$current['z'] = max($current['z'], $d[2]);
		}
		if($error)
			return false;
		if(empty($ret))
			return $current;
		$ret[] = $current;
		return $ret;
	}

	function checkDimensions($product, $dimensions, $requirements = array()) {
		if(empty($requirements) || !count($requirements))
			return true;

		if(empty($dimensions['w']) && empty($dimensions['x']) && empty($dimensions['y']) && empty($dimensions['z']))
			return true;

		$available_requirements = array(
			'w' => 'PRODUCT_WEIGHT',
			'x' => 'PRODUCT_WIDTH',
			'y' => 'PRODUCT_LENGTH',
			'z' => 'PRODUCT_HEIGHT',
		);

		$return = true;
		static $already = array();
		foreach($requirements as $requirement){
			if(!empty($dimensions[$requirement]))
				continue;

			if(!isset($available_requirements[$requirement]))
				continue;
			$dimension = $available_requirements[$requirement];

			if(empty($already[$dimension . '_' . $product->product_id])) {
				$already[$dimension . '_' . $product->product_id] = true;
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('THE_X_IS_MISSING_FOR_THE_PRODUCT_X', JText::_($dimension), $product->product_name));
			}
			$return = false;
		}
		return $return;
	}

	function processPackageLimit($limit_key, $limit_value , $product, $qty, $package, $units) {
		switch ($limit_key) {
			case 'unit':
				if($qty > $limit_value)
					return (int)$limit_value;
				return (int)$qty;
			case 'x':
				if(empty($product['x']) || $product['x'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['x']);
			case 'y':
				if(empty($product['y']) || $product['y'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['y']);
			case 'z':
				if(empty($product['z']) || $product['z'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['z']);
			case 'w':
				if(empty($product['w']) || $product['w'] > $limit_value)
					return false;
				return (int)floor($limit_value / $product['w']);
		}
		return 0;
	}

	function groupPackages(&$data, $caracs) {
		$data['weight_unit'] = $caracs['weight_unit'];
		$data['dimension_unit'] = $caracs['dimension_unit'];
		$tmpHeight = $data['height'] + round($caracs['height'], 2);
		$tmpLength = $data['length'] + round($caracs['length'], 2);
		$tmpWidth = $data['width'] + round($caracs['width'], 2);
		$dim = $tmpLength + (2 * $tmpWidth) + (2 * $tmpHeight);

		$d = array($caracs['width'], $caracs['height'], $caracs['length']);
		sort($d);

		return array(
			'x' => $d[0],
			'y' => $d[1],
			'z' => $d[2],
			'dim' => $dim,
			'tmpHeight' => $tmpHeight,
			'tmpLength' => $tmpLength,
			'tmpWidth' => $tmpWidth,
		);
	}

	function _convertCharacteristics(&$product, $data, $forceUnit = false) {
		$carac = array();

		if(!isset($product->product_dimension_unit_orig))
			$product->product_dimension_unit_orig = $product->product_dimension_unit;
		if(!isset($product->product_weight_unit_orig))
			$product->product_weight_unit_orig = $product->product_weight_unit;
		if(!isset($product->product_weight_orig))
			$product->product_weight_orig = $product->product_weight;

		if($forceUnit) {
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$carac['weight'] = $this->weightHelper->convert($product->product_weight_orig, $product->product_weight_unit_orig, 'lb');
			$carac['weight_unit'] = 'LBS';
			$carac['height'] = $this->volumeHelper->convert($product->product_height, $product->product_dimension_unit_orig, 'in' , 'dimension');
			$carac['length'] = $this->volumeHelper->convert($product->product_length, $product->product_dimension_unit_orig, 'in', 'dimension');
			$carac['width'] = $this->volumeHelper->convert($product->product_width, $product->product_dimension_unit_orig, 'in', 'dimension');
			$carac['dimension_unit'] = 'IN';
			return $carac;
		}

		if(empty($data['units']))
			$data['units'] = 'kg';
		$c = ($data['units'] == 'kg') ? array('v' => 'kg', 'vu' => 'KGS', 'd' => 'cm', 'du' => 'CM' ) : array('v' => 'lb', 'vu' => 'LBS', 'd' => 'in', 'du' => 'IN');
		if($product->product_weight_unit_orig == $c['v']){
			$carac['weight'] = $product->product_weight_orig;
			$carac['weight_unit'] = $this->convertUnit[$product->product_weight_unit_orig];
		} else {
			if(empty($this->weightHelper))
				$this->weightHelper = hikashop_get('helper.weight');
			$carac['weight'] = $this->weightHelper->convert($product->product_weight_orig, $product->product_weight_unit_orig, $c['v']);
			$carac['weight_unit'] = $c['vu'];
		}

		if($product->product_dimension_unit_orig == $c['d']) {
			$carac['height'] = $product->product_height;
			$carac['length'] = $product->product_length;
			$carac['width'] = $product->product_width;
			$carac['dimension_unit'] = $this->convertUnit[$product->product_dimension_unit_orig];
		} else {
			if(empty($this->volumeHelper))
				$this->volumeHelper = hikashop_get('helper.volume');
			$carac['height'] = $this->volumeHelper->convert($product->product_height, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['length'] = $this->volumeHelper->convert($product->product_length, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['width'] = $this->volumeHelper->convert($product->product_width, $product->product_dimension_unit_orig, $c['d'], 'dimension');
			$carac['dimension_unit'] = $c['du'];
		}
		return $carac;
	}

	function _currencyConversion(&$usableMethods, &$order) {
		$currency = $this->shipping_currency_id;
		$currencyClass = hikashop_get('class.currency');
		foreach($usableMethods as $i => $method){
			if((int)$method['currency_id'] == (int)$currency)
				continue;

			$usableMethods[$i]['value'] = $currencyClass->convertUniquePrice($method['value'], (int)$method['currency_id'], $currency);
			$usableMethods[$i]['old_currency_id'] = (int)$usableMethods[$i]['currency_id'];
			$usableMethods[$i]['old_currency_code'] = $usableMethods[$i]['currency_code'];
			$usableMethods[$i]['currency_id'] = (int)$currency;
			$usableMethods[$i]['currency_code'] = $this->shipping_currency_code;
		}
		return $usableMethods;
	}

	function displayDelaySECtoDAY($value, $type) {
		$c = array(
			0 => 60, // Min
			1 => 3600, // Hour
			2 => 86400 // Day
		);
		if(!empty($c[$type]))
			return round( (int)$value / $c[$type] );
		return $value;
	}
}

JHTML::_('select.booleanlist','hikashop');
class hikaParameter extends JRegistry {
	function get($path, $default = null) {
		$value = parent::get($path, 'noval');
		if($value==='noval') $value = parent::get('data.'.$path,$default);
		return $value;
	}
}
if(HIKASHOP_J25) {
	class hikaLanguage extends JLanguage {
		function __construct($old = null) {
			if(is_string($old)) {
				parent::__construct($old);
				$old = JFactory::getLanguage($old);
			}else{
				parent::__construct($old->lang);
			}
			if(is_object($old)) {
				$this->strings = $old->strings; $this->override = $old->override; $this->paths = $old->paths;
				$this->metadata = $old->metadata; $this->locale = $old->locale; $this->lang = $old->lang;
				$this->default = $old->default; $this->debug = $old->debug; $this->orphans = $old->orphans;
			}
		}
		function publicLoadLanguage($filename, $extension = 'unknown') {
			if($extension == 'override')
				return $this->reloadOverride($filename);
			return $this->loadLanguage($filename, $extension);
		}
		function reloadOverride($filename = null) {
			$ret = false;
			if(empty($this->lang) && empty($file)) return $ret;
			if(empty($filename))
				$filename = JPATH_BASE.'/language/overrides/'.$this->lang.'.override.ini';
			if(file_exists($filename) && $contents = $this->parse($filename)) {
				if(is_array($contents)) {
					$this->override = $contents;
					$this->strings = array_merge($this->strings, $this->override);
					$ret = true;
				}
				unset($contents);
			}
			return $ret;
		}
	}
	JFactory::$language = new hikaLanguage(JFactory::$language);
}

define('HIKASHOP_COMPONENT', 'com_hikashop');
define('HIKASHOP_LIVE', rtrim(JURI::root(),'/').'/');
define('HIKASHOP_ROOT', rtrim(JPATH_ROOT,DS).DS);
define('HIKASHOP_FRONT', rtrim(JPATH_SITE,DS).DS.'components'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_BACK', rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_HELPER', HIKASHOP_BACK.'helpers'.DS);
define('HIKASHOP_BUTTON', HIKASHOP_BACK.'buttons');
define('HIKASHOP_CLASS', HIKASHOP_BACK.'classes'.DS);
define('HIKASHOP_INC', HIKASHOP_BACK.'inc'.DS);
define('HIKASHOP_VIEW', HIKASHOP_BACK.'views'.DS);
define('HIKASHOP_TYPE', HIKASHOP_BACK.'types'.DS);
define('HIKASHOP_MEDIA', HIKASHOP_ROOT.'media'.DS.HIKASHOP_COMPONENT.DS);
define('HIKASHOP_DBPREFIX', '#__hikashop_');
$app = JFactory::getApplication();

if(!HIKASHOP_PHP5) {
	$lang =& JFactory::getLanguage();
	$doc =& JFactory::getDocument();
} else {
	$lang = JFactory::getLanguage();
	$doc = JFactory::getDocument();
}
$override_path = JLanguage::getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$lang->getTag().'.override.ini';
$lang->load(HIKASHOP_COMPONENT,JPATH_SITE);
if(file_exists($override_path)) {
	if(!HIKASHOP_J16) {
		$lang->_load($override_path,'override');
	} elseif(HIKASHOP_J25) {
		$lang->publicLoadLanguage($override_path,'override');
	}
}

if(defined('HIKASHOP_INSTALL_PRECHECK')){
	$databaseHelper = hikashop_get('helper.database');
	$databaseHelper->checkdb();
}

$configClass =& hikashop_config();
$responsive = $configClass->get('bootstrap_design', HIKASHOP_J30);
if($responsive) {
	define('HIKASHOP_RESPONSIVE', true);
	switch($responsive){
		case 'bootstrap2':
		default:
			define('HK_GRID_ROW', 'row-fluid');
			define('HK_GRID_THUMBNAILS', 'thumbnails');
			define('HK_GRID_COL_12', 'span12');
			define('HK_GRID_COL_10', 'span10');
			define('HK_GRID_COL_8', 'span8');
			define('HK_GRID_COL_6', 'span6');
			define('HK_GRID_COL_4', 'span4');
			define('HK_GRID_COL_3', 'span3');
			define('HK_GRID_COL_2', 'span2');
			define('HK_GRID_COL_1', 'span1');
			define('HK_GRID_BTN', 'btn');
			break;
		case 'bootstrap3':
			define('HK_GRID_ROW', 'row');
			define('HK_GRID_THUMBNAILS', 'hk-thumbnails');
			define('HK_GRID_COL_12', 'col-md-12');
			define('HK_GRID_COL_10', 'col-md-10');
			define('HK_GRID_COL_8', 'col-md-8');
			define('HK_GRID_COL_6', 'col-md-6');
			define('HK_GRID_COL_4', 'col-md-4');
			define('HK_GRID_COL_3', 'col-md-3');
			define('HK_GRID_COL_2', 'col-md-2');
			define('HK_GRID_COL_1', 'col-md-1');
			define('HK_GRID_BTN', 'btn btn-default');
			break;
		case 'hikashop_responsive':
			define('HK_GRID_ROW', 'hk-row');
			define('HK_GRID_THUMBNAILS', 'hk-thumbnails');
			define('HK_GRID_COL_12', 'hkc-md-12');
			define('HK_GRID_COL_10', 'hkc-md-10');
			define('HK_GRID_COL_8', 'hkc-md-8');
			define('HK_GRID_COL_6', 'hkc-md-6');
			define('HK_GRID_COL_4', 'hkc-md-4');
			define('HK_GRID_COL_3', 'hkc-md-3');
			define('HK_GRID_COL_2', 'hkc-md-2');
			define('HK_GRID_COL_1', 'hkc-md-1');
			define('HK_GRID_BTN', 'hk-btn');
			break;
	}
} else {
	define('HIKASHOP_RESPONSIVE', false);
	define('HK_GRID_THUMBNAILS', false);
	define('HK_GRID_ROW', '');
	define('HK_GRID_COL_12', '');
	define('HK_GRID_COL_10', '');
	define('HK_GRID_COL_8', '');
	define('HK_GRID_COL_6', '');
	define('HK_GRID_COL_4', '');
	define('HK_GRID_COL_3', '');
	define('HK_GRID_COL_2', '');
	define('HK_GRID_COL_1', '');
	define('HK_GRID_BTN', '');
}
if($configClass->get('bootstrap_back_design', HIKASHOP_J30)) {
	define('HIKASHOP_BACK_RESPONSIVE', true);
} else {
	define('HIKASHOP_BACK_RESPONSIVE', false);
}

if(HIKASHOP_J30 && (($app->isAdmin() && HIKASHOP_BACK_RESPONSIVE) || (!$app->isAdmin() && HIKASHOP_RESPONSIVE))) {
	include_once(dirname(__FILE__).DS.'joomla30.php');
} else {
	class JHtmlHikaselect extends JHTMLSelect {}
}

define('HIKASHOP_RESSOURCE_VERSION', str_replace('.', '', $configClass->get('version')));
if($app->isAdmin()) {
	define('HIKASHOP_CONTROLLER', HIKASHOP_BACK.'controllers'.DS);
	define('HIKASHOP_IMAGES', '../media/'.HIKASHOP_COMPONENT.'/images/');
	define('HIKASHOP_CSS', '../media/'.HIKASHOP_COMPONENT.'/css/');
	define('HIKASHOP_JS', '../media/'.HIKASHOP_COMPONENT.'/js/');
	$css_type = 'backend';
	$doc->addScript(HIKASHOP_JS.'hikashop.js?v='.HIKASHOP_RESSOURCE_VERSION);
	$doc->addStyleSheet(HIKASHOP_CSS.'menu.css?v='.HIKASHOP_RESSOURCE_VERSION);
} else {
	define('HIKASHOP_CONTROLLER',HIKASHOP_FRONT.'controllers'.DS);
	define('HIKASHOP_IMAGES',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/images/');
	define('HIKASHOP_CSS',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/css/');
	define('HIKASHOP_JS',JURI::base(true).'/media/'.HIKASHOP_COMPONENT.'/js/');
	$css_type = 'frontend';
	$doc->addScript(HIKASHOP_JS.'hikashop.js?v='.HIKASHOP_RESSOURCE_VERSION);
}
$css = $configClass->get('css_'.$css_type,'default');
if(!empty($css)) {
	$doc->addStyleSheet(HIKASHOP_CSS.$css_type.'_'.$css.'.css?t='.@filemtime(HIKASHOP_MEDIA.'css'.DS.$css_type.'_'.$css.'.css'));
}

if(!$app->isAdmin()) {
	$style = $configClass->get('css_style','');
	if(!empty($style)) {
		$doc->addStyleSheet(HIKASHOP_CSS.'style_'.$style.'.css?t='.@filemtime(HIKASHOP_MEDIA.'css'.DS.'style_'.$style.'.css'));
	}
}

if($lang->isRTL()) {
	$doc->addStyleSheet(HIKASHOP_CSS.'rtl.css?v='.HIKASHOP_RESSOURCE_VERSION);
}



define('HIKASHOP_NAME','HikaShop');
define('HIKASHOP_TEMPLATE',HIKASHOP_FRONT.'templates'.DS);
define('HIKASHOP_URL','https://www.hikashop.com/');
define('HIKASHOP_UPDATEURL',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=update&task=');
define('HIKASHOP_HELPURL',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=doc&component='.HIKASHOP_NAME.'&page=');
define('HIKASHOP_REDIRECT',HIKASHOP_URL.'index.php?option=com_updateme&ctrl=redirect&page=');
if (is_callable("date_default_timezone_set")) date_default_timezone_set(@date_default_timezone_get());

if(!function_exists('bccomp')) {
	function bccomp($num1, $num2, $scale = 0) {
		if(!preg_match("/^\+?(\d+)(\.\d+)?$/", $num1, $tmp1) || !preg_match("/^\+?(\d+)(\.\d+)?$/", $num2, $tmp2))
			return 0;
		$num1 = ltrim($tmp1[1], '0');
		$num2 = ltrim($tmp2[1], '0');
		if(strlen($num1) > strlen($num2))
			return 1;
		if(strlen($num1) < strlen($num2))
			return -1;
		$dec1 = isset($tmp1[2]) ? rtrim(substr($tmp1[2], 1), '0') : '';
		$dec2 = isset($tmp2[2]) ? rtrim(substr($tmp2[2], 1), '0') : '';
		if($scale != null) {
			$dec1 = substr($dec1, 0, $scale);
			$dec2 = substr($dec2, 0, $scale);
		}
		$DLen = max(strlen($dec1), strlen($dec2));
		$num1 .= str_pad($dec1, $DLen, '0');
		$num2 .= str_pad($dec2, $DLen, '0');
		for($i = 0; $i < strlen($num1); $i++) {
			if((int)$num1{$i} > (int)$num2{$i})
				return 1;
			if((int)$num1{$i} < (int)$num2{$i})
				return -1;
		}
		return 0;
	}
}
