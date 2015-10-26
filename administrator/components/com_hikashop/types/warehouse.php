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
class hikashopWarehouseType{

	var $values = array();

	function __construct() {
		$this->app = JFactory::getApplication();
	}

	function load($value) {
		$this->values = array();
		$db = JFactory::getDBO();

		$query = 'SELECT COUNT(*) FROM '.hikashop_table('warehouse').' WHERE warehouse_published = 1';
		$db->setQuery($query);
		$ret = (int)$db->loadResult();
		if($ret > 10) {
			$this->values = $ret;
			return;
		}

		$query = 'SELECT * FROM '.hikashop_table('warehouse').' WHERE warehouse_published = 1';
		$db->setQuery($query);
		$warehouses = $db->loadObjectList();
		$this->values[] = JHTML::_('select.option', 0, JText::_('NO_WAREHOUSE'));
		if(!empty($warehouses)){
			foreach($warehouses as $warehouse){
				if($warehouse->warehouse_id == 0 || $warehouse->warehouse_id == 1)
					continue;
				$this->values[] = JHTML::_('select.option', $warehouse->warehouse_id, $warehouse->warehouse_id.' '.$warehouse->warehouse_name);
			}
		}
	}

	public function displayDropdown($map, $value, $delete = false, $options = '', $id = '') {
		if(empty($this->values))
			$this->load($value);
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value, $id);
	}

	function initJs() {
		static $jsInit = null;
		if($jsInit === true)
			return;

		$warehouse_format = 'data.warehouse_name';
		if($this->app->isAdmin())
			$warehouse_format = 'data.id + " - " + data.warehouse_name';

		$js = '
if(!window.localPage)
	window.localPage = {};
window.localPage.fieldSetWarehouse = function(el, name) {
	window.hikashop.submitFct = function(data) {
		var d = document,
			tInput = d.getElementById(name + "_input_id"),
			tSpan = d.getElementById(name + "_span_id");
		if(tInput) { tInput.value = data.id; }
		if(tSpan) { tSpan.innerHTML = '.$warehouse_format.'; }
	};
	window.hikashop.openBox(el,null,(el.getAttribute("rel") == null));
	return false;
};
window.localPage.fieldRemWarehouse = function(el, name) {
	var d = document,
		tInput = d.getElementById(name + "_input_id"),
		tSpan = d.getElementById(name + "_span_id");
	if(tInput) { tInput.value = ""; }
	if(tSpan) { tSpan.innerHTML = " - "; }
	return false;
};
';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$jsInit = true;
	}

	function display($map, $value, $delete = false) {
		$this->initJs();

		$warehouseClass = hikashop_get('class.warehouse');
		$popup = hikashop_get('helper.popup');

		$name = str_replace(array('][','[',']'), '_', $map);
		$warehouse_id = (int)$value;
		$warehouse = $warehouseClass->get($warehouse_id);
		$warehouse_name = '';
		if(!empty($warehouse)) {
			$warehouse_name = @$warehouse->warehouse_name;
		} else {
			$warehouse_id = '';
		}

		$warehouse_display_name = $warehouse_name;
		if($this->app->isAdmin())
			$warehouse_display_name = $warehouse_id.' - '.$warehouse_name;

		$ret = '<span id="'.$name.'_span_id">'.$warehouse_display_name.'</span>' .
			'<input type="hidden" id="'.$name.'_input_id" name="'.$map.'" value="'.$warehouse_id.'"/> '.
			$popup->display(
				'<img src="'.HIKASHOP_IMAGES.'edit.png" style="vertical-align:middle;"/>',
				'WAREHOUSE_SELECTION',
				hikashop_completeLink('warehouse&task=selection&single=true', true),
				'hikashop_set_warehouse_'.$name,
				760, 480, 'onclick="return window.localPage.fieldSetWarehouse(this,\''.$name.'\');"', '', 'link'
			);

		if($delete)
			$ret .= ' <a title="'.JText::_('HIKA_DELETE').'" href="#'.JText::_('HIKA_DELETE').'" onclick="return window.localPage.fieldRemWarehouse(this, \''.$name.'\');"><img src="'.HIKASHOP_IMAGES.'delete.png" style="vertical-align:middle;"/></a>';

		return $ret;
	}
}
