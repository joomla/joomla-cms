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
class CartmodulesViewCartmodules extends hikashopView{
	var $include_module = false;
	var $ctrl= 'modules';
	var $nameListing = 'MODULES';
	var $nameForm = 'MODULE';
	var $icon = 'module';

	function display($tpl = null,$params=null)
	{
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}

	function options(&$params){
		$this->id = $params->get('id');
		$this->name = str_replace('[]', '', $params->get('name'));
		$this->element = $params->get('value');
		$this->pricetaxType = hikashop_get('type.pricetax');
		$this->discountDisplayType = hikashop_get('type.discount_display');
		$this->priceDisplayType = hikashop_get('type.priceDisplay');
		$this->arr = array(
			JHTML::_('select.option',  '-1', JText::_( 'HIKA_INHERIT' ) ),
			JHTML::_('select.option',  '1', JText::_( 'HIKASHOP_YES' ) ),
			JHTML::_('select.option',  '0', JText::_( 'HIKASHOP_NO' ) ),
		);
		$this->arr[0]->class = 'btn-primary';
		$this->arr[1]->class = 'btn-success';
		$this->arr[2]->class = 'btn-danger';

		$this->type = 'cart';
		if(preg_match('/wishlist/',$this->name))
			$this->type = 'wishlist';

		$cid = JRequest::getInt('id','');
		if(empty($cid))
			$cid = hikashop_getCID();
		$modulesClass = hikashop_get('class.modules');
		$module = $modulesClass->get($cid);
		if(empty($this->element)) {
			$this->element = $module->hikashop_params;
		}
		$config = hikashop_config();
		$this->default_params = $config->get('default_params');
	}
}
