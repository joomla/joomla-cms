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
class hikashopModulesClass extends hikashopClass{
	var $pkeys=array('id');
	var $toggle = array('published'=>'id');
	function getTable(){
		return hikashop_table('modules',false);
	}

	function get($id,$default=''){
		$obj = parent::get($id);
		$config =& hikashop_config();
		if(is_null($obj)){
			$obj= new stdClass();
		}
		if(!empty($obj->id)){
			$obj->hikashop_params = $config->get('params_'.$obj->id,null);
		}
		if(empty($obj->hikashop_params)){
			$obj->hikashop_params = $config->get('default_params',null);
		}
		$this->loadParams($obj);
		return $obj;
	}

	function loadParams(&$result){
		if(!empty($result->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$lines = explode("\n",$result->params);
				$result->params = array();
				foreach($lines as $line){
					$param = explode('=',$line,2);
					if(count($param)==2){
						$result->params[$param[0]]=$param[1];
					}
				}
			}else{
				$registry = new JRegistry;
				if(!HIKASHOP_J30)
					$registry->loadJSON($result->params);
				else
					$registry->loadString($result->params);
				$result->params = $registry->toArray();
			}
		}
	}

	function saveForm($id=null){
		$module = new stdClass();
		$formData = JRequest::getVar( 'module', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		if(!empty($formData)){
			foreach($formData as $column => $value){
				hikashop_secureField($column);
				if(is_array($value)){
					$module->$column=array();
					foreach($value as $k2 => $v2){
						hikashop_secureField($k2);
						$module->{$column}[$k2] = $safeHtmlFilter->clean(strip_tags($v2), 'string');
					}
				}else{
					$module->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
				}
			}
		}

		$element = array();
		$formData = JRequest::getVar( 'config', array(), '', 'array' );
		if (isset($module->id) && empty($id))
			$params_name = 'params_'.(int)$module->id;
		else
			$params_name = 'params_'.(int)$id;

		if(!empty($formData[$params_name])){
			foreach($formData[$params_name] as $column => $value){
				hikashop_secureField($column);
				$element[$column] = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
			if(empty($element['selectparentlisting'])){
				$cat = hikashop_get('class.category');
				$mainProductCategory = 'product';
				$cat->getMainElement($mainProductCategory);
				$element['selectparentlisting']=$mainProductCategory;
			}
		}

		$formData = JRequest::getVar( 'moduleconfig', array(), '', 'array' );

		if(!empty($formData[$params_name])){
			foreach($formData[$params_name] as $column => $value){
				hikashop_secureField($column);
				$module->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}
		$module->hikashop_params =& $element;
		$result = $this->save($module);
		return $result;
	}

	function save(&$element){

		if(!empty($element->params)&&is_array($element->params)){
			if(version_compare(JVERSION,'1.6','<')){
				$params = '';
				foreach($element->params as $k => $v){
					$params.=$k.'='.$v."\n";
				}
				$element->params = rtrim($params,"\n");
			}else{
				$handler = JRegistryFormat::getInstance('JSON');
				$element->params = $handler->objectToString($element->params);
			}
		}
		$element->id = parent::save($element);

		if($element->id && !empty($element->hikashop_params)){
			$configClass =& hikashop_config();
			$config=new stdClass();
			$params_name = 'params_'.$element->id;
			$config->$params_name = $element->hikashop_params;
			if($configClass->save($config)){
				$configClass->set($params_name,$element->hikashop_params);
			}


			if(!HIKASHOP_J30) return $element->id;

			$plugin = JPluginHelper::getPlugin('system', 'cache');
			$params = new JRegistry(@$plugin->params);

			$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->get('browsercache', false),
				'caching'		=> false,
			);

			$cache		= JCache::getInstance('page', $options);
			$cache->clean();
		}
		return $element->id;
	}

	function delete(&$elements){
		$result = parent::delete($elements);
		if($result){
			if(!is_array($elements)){
				$elements=array($elements);
			}
			if(!empty($elements)){
				$ids = array();
				foreach($elements as $id){
					$ids[]=$this->database->Quote('params_'.(int)$id);
				}
				$query = 'DELETE FROM '.hikashop_table('config').' WHERE config_namekey IN ('.implode(',',$ids).');';
				$this->database->setQuery($query);
				return $this->database->query();
			}
		}
		return $result;
	}
}
