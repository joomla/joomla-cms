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
class hikashopProductslotType {

	function load(){
		$this->values = array(
			'show' => array(
				'topBegin',
				'topEnd',
				'leftBegin',
				'leftEnd',
				'rightBegin',
				'rightMiddle',
				'rightEnd',
				'bottomBegin',
				'bottomMiddle',
				'bottomEnd'
			),
			'listing' => array(
				'top',
				'afterProductName',
				'bottom'
			)		
		);
	}

	function display($map, $value, $type = 'show') {
		$this->load();
		if(!isset($this->values[$type])) {
			$type = 'show';
		}
		return JHTML::_('select.genericlist', $this->values[$type], $map, 'class="inputbox" size="1"', 'value', 'text', $value);
	}
}
