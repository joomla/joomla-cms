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
class ToggleController extends HikashopBridgeController {

	function __construct($config = array()) {
		parent::__construct($config);
		$this->registerDefaultTask('toggle');
		if(!headers_sent()) {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );
		}
	}

	function toggle() {
		$completeTask = JRequest::getCmd('task');
		$task = substr($completeTask,0,strrpos($completeTask,'-'));
		$elementPkey = substr($completeTask,strrpos($completeTask,'-') +1);
		$value =  JRequest::getVar('value','','','cmd');
		$controllerName =  JRequest::getVar('table','','','word');

		$controller = hikashop_get('controller.'.$controllerName);
		if(empty($controller)) {
			echo 'No controller';
			exit;
		}

		if(!$controller->authorize('toggle')) {
			echo 'Not authorized';
			exit;
		}
		$function = $controllerName.$task;
		if(method_exists($this,$function)){
			$this->$function($elementPkey,$value);
		}else{
			if(isset($controller->type)){
				$tableName=$controller->type;
			}else if(!empty($controller->table)){
				if(is_array($controller->table)&&count($controller->table)){
					$tableName=reset($controller->table);
				}else{
					$tableName=$controller->table;
				}
			}else{
				$tableName=$controllerName;
			}
			$class = hikashop_get('class.'.$tableName);
			if(empty($class->toggle[$task])){
				echo 'Forbidden';
				exit;
			}
			$obj = new stdClass();
			$obj->$task = $value;
			$id = $class->toggle[$task];
			$obj->$id = $elementPkey;
			if(!$class->save($obj)){
				if(method_exists($class,'getTable')){
					$table = $class->getTable();
				}else{
					$table = hikashop_table($controllerName);
				}
				if($table == null){
					$table = $controllerName;
				}
				$db	= JFactory::getDBO();
				$db->setQuery('SELECT '.$task.' FROM '.$table.' WHERE '.$class->toggle[$task].' = '.$db->Quote($elementPkey).' LIMIT 1');
				$value = $db->loadResult();
			}
		}
		$toggleClass = hikashop_get('helper.toggle');
		$extra = JRequest::getVar('extra',array(),'','array');
		if(!empty($extra)){
			foreach($extra as $key => $val){
				$extra[$key] = urldecode($val);
			}
		}
		echo $toggleClass->toggle(JRequest::getCmd('task',''),$value,$controllerName,$extra);
		exit;
	}

	function pluginsPublished($elementPkey,&$value){
		return $this->pluginsEnabled($elementPkey,$value,'published');
	}

	function pluginsEnabled($elementPkey,&$value,$task='enabled'){
		$plugins = hikashop_get('class.plugins');
		$obj = new stdClass();
		if(!HIKASHOP_J16) {
			$obj->id = $elementPkey;
		}else{
			$obj->extension_id = $elementPkey;
		}
		$obj->$task = $value;

		$plugins->save($obj);
		$result = $plugins->get($elementPkey);
		if($result){
			if($result->$task!=$value){
				$value = $result->$task;
			}
			if($result->folder != 'hikashop'){
				$type = str_replace('hikashop','',$result->folder);
				$db = JFactory::getDBO();
				$type_name = $type.'_type';

				if($type == 'payment' || $type == 'shipping') {
					$db->setQuery('SELECT * FROM '.hikashop_table($type).' WHERE '.$type_name.'=\''.$result->element.'\'');
					$data = $db->loadObject();
				}

				if(empty($data)){
					$plugin = hikashop_import($result->folder,$result->element);

					if($plugin && method_exists($plugin,'onPaymentConfiguration')){
						$obj = null;
						$plugin->onPaymentConfiguration($obj);
						if(!empty($obj) && is_array($obj) && count($obj)>0){
							$obj = reset($obj);
							$params_name = $type.'_params';
							if(!empty($obj->$params_name) && !is_string($obj->$params_name)){
								$obj->$params_name = serialize($obj->$params_name);
							}
							$class = hikashop_get('class.'.$type);
							$class->save($obj);
							$pluginsClass = hikashop_get('class.plugins');
							$pluginsClass->cleanPluginCache();
						}
					}
				}
			}
		}
	}

	function configconfig_value($elementPkey,$value){
		$data = array($elementPkey=>$value);
		$config =& hikashop_config();
		$config->save($data);
	}

	function delete(){
		list($value1,$value2) = explode('-', JRequest::getCmd('value'), 2);
		$table =  JRequest::getVar('table','','','word');

		$controller = hikashop_get('controller.'.$table);
		if(empty($controller)) {
			echo 'No controller';
			exit;
		}

		if(!$controller->authorize('delete')) {
			echo 'Forbidden';
			exit;
		}

		$function = 'delete'.$table;
		if(method_exists($this,$function)) {
			$this->$function($value1,$value2);
			exit;
		}

		$class = hikashop_get('class.'.$table);
		if(empty($class->deleteToggle)) {
			echo 'Forbidden';
			exit;
		}

		list($key1,$key2) = reset($class->deleteToggle);
		$table = key($class->deleteToggle);
		if(empty($key1) || empty($key2) || empty($value1) || empty($value2)) {
			echo 'No value';
			exit;
		}

		$db	= JFactory::getDBO();
		$db->setQuery('DELETE FROM '.hikashop_table($table).' WHERE '.$key1.' = '.$db->Quote($value1).' AND '.$key2.' = '.$db->Quote($value2));
		$db->query();
		exit;
	}

	function deleteconfig($namekey,$val){
		$config =& hikashop_config();
		$newConfig = new stdClass();
		$newConfig->$namekey = $val;
		$config->save($newConfig);
	}

	function deleteemail($value,$val){
		$namekey = preg_replace('#_[0-9]*$#','.attach',$value);
		$toRemove = preg_replace('#.*_#','',$value);
		$config = hikashop_config();
		$confValue = $config->get($namekey);
		$confValue = unserialize($confValue);
		foreach($confValue as $k => $result){
			if((int)$k == (int)$toRemove)
				unset($confValue[$k]);
		}
		$val = serialize($confValue);
		$newConfig = new stdClass();
		$newConfig->$namekey = $val;
		$config->save($newConfig);
	}
}
