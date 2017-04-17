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
class HikaShopTagsHelper {
	var $_isCompatible = false;

	var $structure = array(
		'product' => array(
			'table' => 'product',
			'id' => 'product_id',
			'name' => 'PRODUCT',
			'router' => 'hikashopTagRouteHelper::getProductRoute',
			'mapping' => array(
				array(
					'type' => 'common',
					'core_content_item_id' => 'product_id',
					'core_title' => 'product_name',
					'core_state' => 'product_published',
					'core_alias' => 'product_alias',
					'core_created_time' => 'product_created',
					'core_modified_time' => 'product_modified',
					'core_body' => 'product_description',
					'core_hits' => 'product_hit',
					'core_metakey' => 'product_keywords',
					'core_metadesc' => 'product_meta_description',
				)
			)
		)
	);

	function __construct() {
		$this->_isCompatible = (version_compare(JVERSION, '3.1.4', '>'));

	}

	function isCompatible() {
		return $this->_isCompatible;
	}

	function addStructure($name, $data) {
		if(isset($this->structure[$name]))
			return;
		$this->structure[$name] = $data;
	}

	function renderInput($values = null, $options = array()) {
		if(!$this->_isCompatible)
			return '';

		$tags = array();
		if(!empty($values)) {
			foreach($values as $v) {
				if(is_object($v))
					$tags[] = $v->tag_id;
				else
					$tags[] = (int)$v;
			}
		}

		if(empty($options['name']))
			$options['name'] = 'tags';
		if(empty($options['mode']))
			$options['mode'] = 'ajax';
		if(!isset($options['class']))
			$options['class'] = 'inputbox span12 small';
		if(!isset($options['multiple']))
			$options['multiple'] = true;

		$xmlConf = new SimpleXMLElement('<field name="'.$options['name'].'" type="tag" mode="'.$options['mode'].'" label="" class="'.$options['class'].'" multiple="'.($options['multiple']?'true':'false').'"></field>');
		JFormHelper::loadFieldClass('tag');
		$jform = new JForm('hikashop');
		$fieldTag = new JFormFieldTag();
		$fieldTag->setForm($jform);
		$fieldTag->setup($xmlConf, $tags);
		return $fieldTag->input;
	}

	function loadTags($type, $element) {
		if(!isset($this->structure[$type]) || !$this->_isCompatible)
			return false;

		$structure = $this->structure[$type];
		$component = 'hikashop';
		if(!empty($structure['component']))
			$component = $structure['component'];
		$alias = 'com_'.$component.'.'.$structure['table'];

		$id = $structure['id'];
		$ret = false;
		if(!empty($element->$id)) {
			$tagsHelper = new JHelperTags();
			$ret = $tagsHelper->getItemTags($alias, $element->$id, false);
		}
		return $ret;
	}

	function saveUCM($type, $element, $tags = array()) {
		if(!isset($this->structure[$type]) || !$this->_isCompatible)
			return false;

		$structure = $this->structure[$type];
		$component = 'hikashop';
		if(!empty($structure['component']))
			$component = $structure['component'];
		$alias = 'com_'.$component.'.'.$structure['table'];

		$tagsHelper = new JHelperTags();
		$tagsHelper->typeAlias = $alias;
		$tagsTable = new JHikaShopTagTable($structure, $element);

		$tagsHelper->preStoreProcess($tagsTable);
		$ret = $tagsHelper->postStoreProcess($tagsTable, $tags);
	}

	function deleteUCM($type, $elements) {
		if(!isset($this->structure[$type]) || !$this->_isCompatible)
			return false;

		$structure = $this->structure[$type];
		$component = 'hikashop';
		if(!empty($structure['component']))
			$component = $structure['component'];
		$alias = 'com_'.$component.'.'.$structure['table'];

		$tagsHelper = new JHelperTags();
		$tagsHelper->typeAlias = $alias;

		$id = $structure['id'];
		$tagsTable = new JHikaShopTagTable($structure, null);

		$ret = true;
		foreach($elements as $element) {
			if(empty($element)) continue;
			$tagsTable->$id = $element;
			if (!$tagsHelper->deleteTagData($tagsTable, $element))
				$ret = false;
		}

		return $ret;
	}

	function initTags() {
		if(!$this->_isCompatible)
			return;

		$db = JFactory::getDBO();
		$mapping_keys = array('core_content_item_id','core_title','core_state','core_alias','core_created_time','core_modified_time','core_body','core_hits','core_publish_up','core_publish_down','core_access','core_params','core_featured','core_metadata','core_language','core_images','core_urls','core_version','core_ordering','core_metakey','core_metadesc','core_catid','core_xreference','asset_id');
		foreach($this->structure as $structure) {
			$component = 'hikashop';
			if(!empty($structure['component']))
				$component = $structure['component'];

			$name = ucfirst($component).' '.JText::_($structure['name']);
			$alias = 'com_'.$component.'.'.$structure['table'];

			$contentType = new JTableContenttype($db);
			if($contentType->load(array('type_alias' => $alias)))
				continue;

			if(substr($structure['table'], 0, 1) == '#')
				$table = $structure['table'];
			else
				$table = hikashop_table($structure['table']);

			$contentType->type_title = $name;
			$contentType->type_alias = $alias;
			$contentType->table = json_encode(array(
				'special' => array('dbtable'=>$table,'key'=>$structure['id'],'type'=>$name,'prefix'=>'JTable','config'=>'array()'),
				'common' => array('dbtable'=>'#__ucm_content','key'=>'ucm_id','type'=>'CoreContent','prefix'=>'JTable','config'=>'array()')
			));

			$mapping_data = array('common' => array(),'special' => array());
			foreach($structure['mapping'] as $mapping) {
				$type = @$mapping['type'];
				if(empty($type) || !isset($mapping_data[$type]))
					$type = 'common';
				unset($mapping['type']);

				$i = count($mapping_data[$type]);
				$mapping_data[$type][$i] = array();
				foreach($mapping_keys as $k) {
					$mapping_data[$type][$i][$k] = 'null';
				}
				foreach($mapping as $key => $value) {
					if(isset($mapping_data[$type][$i][$key]))
						$mapping_data[$type][$i][$key] = $value;
				}
			}
			if(empty($mapping_data['special']))
				$mapping_data['special'][0] = array();
			if(empty($mapping_data['common']))
				$mapping_data['common'][0] = array();
			$contentType->field_mappings = json_encode($mapping_data);

			$contentType->router = '';
			if(!empty($structure['router']))
				$contentType->router = $structure['router'];
			$contentType->store();
		}
	}
}

class JHikaShopTagTable extends JTable {
	function __construct($structure, $element) {
		$db = JFactory::getDBO();
		if(substr($structure['table'], 0, 1) == '#')
			$table = $structure['table'];
		else
			$table = hikashop_table($structure['table']);

		$mapping = reset($structure['mapping']);

		if(!empty($element)) {
			foreach($element as $k => $v) {
				if(!in_array($k, $mapping))
					continue;
				$this->$k = $v;
			}
		}
		parent::__construct($table, $structure['id'], $db);
	}
}
