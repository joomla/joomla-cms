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
class hikashopFieldsType {
	var $allValues;
	var $externalValues;
	var $externalOptions;
	var $options;

	function __construct() {
		$this->externalValues = null;
		$this->externalOptions = null;
		$this->options = array();
	}

	function load($type = '') {
		$this->allValues = array(
			'text' => array(
				'name' => JText::_('FIELD_TEXT'),
				'options' => array("size","required","default","columnname","filtering","maxlength","readonly","placeholder","translatable")
			),
			'link' => array(
				'name' => JText::_('LINK'),
				'options' => array("size","required","default","columnname","filtering","maxlength","readonly")
			),
			'textarea' => array(
				'name' => JText::_('FIELD_TEXTAREA'),
				'options' => array("cols","rows","required","default","columnname","filtering","readonly","maxlength","placeholder","translatable")
			),
			'wysiwyg' => array(
				'name' => JText::_('WYSIWYG'),
				'options' => array("cols","rows","required","default","columnname","filtering","translatable")
			),
			'radio' => array(
				'name' => JText::_('FIELD_RADIO'),
				'options' => array("multivalues","required","default","columnname")
			),
			'checkbox' => array(
				'name' => JText::_('FIELD_CHECKBOX'),
				'options' => array("multivalues","required","default","columnname")
			),
			'singledropdown' => array(
				'name' => JText::_('FIELD_SINGLEDROPDOWN'),
				'options' => array("multivalues","required","default","columnname")
			),
			'multipledropdown' => array(
				'name' => JText::_('FIELD_MULTIPLEDROPDOWN'),
				'options' => array("multivalues","size","default","columnname")
			),
			'date' => array(
				'name' => JText::_('FIELD_DATE'),
				'options' => array("required","format","size","default","columnname","allow")
			),
			'zone' => array(
				'name' => JText::_('FIELD_ZONE'),
				'options' => array("required","zone","default","columnname","pleaseselect")
			),
		);

		if(hikashop_level(2)) {
			if($type == 'entry'|| empty($type)) {
				$this->allValues["coupon"] = array(
					'name' => JText::_('HIKASHOP_COUPON'),
					'options' => array("size","required","default","columnname")
				);
			}
			$this->allValues["file"] = array(
				'name' => JText::_('HIKA_FILE'),
				'options' => array("required","default","columnname")
			);
			$this->allValues["image"] = array(
				'name' => JText::_('HIKA_IMAGE'),
				'options' => array("required","default","columnname")
			);
			$this->allValues['ajaxfile'] = array(
				'name' => JText::_('FIELD_AJAX_FILE'),
				'options' => array("required","default","columnname")
			);
			$this->allValues['ajaximage'] = array(
				'name' => JText::_('FIELD_AJAX_IMAGE'),
				'options' => array("required","default","columnname","imagesize")
			);
		}
		$this->allValues["customtext"] = array(
			'name' => JText::_('CUSTOM_TEXT'),
			'options' => array("customtext")
		);

		if($this->externalValues == null) {
			$this->externalValues = array();
			$this->externalOptions = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onFieldsLoad', array( &$this->externalValues, &$this->externalOptions ) );
		}

		if(!empty($this->externalValues)) {
			foreach($this->externalValues as $value) {
				if(substr($value->name,0,4) != 'plg.')
					$value->name = 'plg.'.$value->name;
				$this->allValues[$value->name] = array(
					'name' => $value->text,
					'options' => @$value->options
				);
			}
		}

		foreach($this->allValues as $v) {
			if(!empty($v['options'])) {
				foreach($v['options'] as $o) {
					$this->options[$o] = $o;
				}
			}
		}
	}

	function addJS(){
		$this->load();
		$externalJS = '';
		if(!empty($this->externalValues)){
			foreach($this->externalValues as $value) {
				if(!empty($value->js))
					$externalJS .= "\r\n\t".$value->js;
			}
		}

		$types = array();
		foreach($this->allValues as $k => $v) {
			$t = '"' . $k . '": [';
			if(!empty($v['options'])) {
				$t .= '"' . implode('","', $v['options']) . '"';
			}
			$t.=']';
			$types[] = $t;
		}

		$options = '';
		if(!empty($this->options)) {
			$options = '"' . implode('","', $this->options) . '"';
		}

		$js = '
function updateFieldType() {
	var d = document,
		newType = "",
		key = "",
		el = d.getElementById("fieldtype"),
		hiddenAll = ['.$options.'],
		allTypes = {
			'.implode(",\r\n\t\t\t", $types).'
		};'.$externalJS.'

	if(el)
		newType = el.value;

	for(var i = 0; i < hiddenAll.length; i++) {
		key = hiddenAll[i]
		el = d.getElementById("fieldopt_" + key);
		if(el) {
			el.style.display = "none";
		} else {
			var j = 0;
			el = d.getElementById("fieldopt_" + key + "_" + j);
			while(el) {
				el.style.display = "none";
				j++;
				el = d.getElementById("fieldopt_" + key + "_" + j);
			}
		}
	}
	for(var i = 0; i < allTypes[newType].length; i++) {
		key = allTypes[newType][i];
		el = d.getElementById("fieldopt_" + key);
		if(el) {
			el.style.display = "";
		} else {
			var j = 0;
			el = d.getElementById("fieldopt_" + key + "_" + j);
			while(el) {
				el.style.display = "";
				j++;
				el = d.getElementById("fieldopt_" + key + "_" + j);
			}
		}
	}
}
window.hikashop.ready(function(){updateFieldType();});
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	function display($map,$value,$type){
		$this->load($type);
		$this->addJS();

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal) {
			$this->values[] = JHTML::_('select.option', $oneType, $oneVal['name']);
		}

		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onchange="updateFieldType();"', 'value', 'text', (string)$value, 'fieldtype');
	}
}
