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
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};

$js ='';
$params->set('from_module',$module->id);
hikashop_initModule();
$config =& hikashop_config();
$module_options = $config->get('params_'.$module->id);

if(empty($module_options)){
	$module_options = $config->get('default_params');
}

$data = $params->get('hikashopcartmodule');
if(HIKASHOP_J30 && (empty($data) || !is_object($data))){
	$db = JFactory::getDBO();
	$query = 'SELECT params FROM '.hikashop_table('modules',false).' WHERE id = '.(int)$module->id;
	$db->setQuery($query);
	$itemData = json_decode($db->loadResult());
	if(!empty($itemData->hikashopcartmodule) && is_object($itemData->hikashopcartmodule)){
		$data = $itemData->hikashopcartmodule;
		$params->set('hikashopcartmodule',$data);
	}
}
if(!empty($data) && is_object($data)){
	foreach($data as $k => $v){
		$module_options[$k] = $v;
	}
}

if(is_array($module_options)){
	foreach($module_options as $key => $option){
		if($key !='moduleclass_sfx'){
			$params->set($key,$option);
		}
	}
}

foreach(get_object_vars($module) as $k => $v){
	if(!is_object($v) && $params->get($k,null)==null){
		$params->set($k,$v);
	}
}

$params->set('cart_type','cart');
$params->set('from','module');
$html = trim(hikashop_getLayout('product','cart',$params,$js));
require(JModuleHelper::getLayoutPath('mod_hikashop_cart'));
