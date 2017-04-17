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
class hikashopBannerClass extends hikashopClass{
	var $tables = array('banner');
	var $pkeys = array('banner_id');
	var $toggle = array('banner_published'=>'banner_id');

	function saveForm(){
		$element = new stdClass();
		$element->banner_id = hikashop_getCID('banner_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['banner'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value);
			if($column!='banner_comment'){
				$element->$column = strip_tags($element->$column);
			}
		}
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);
		$result = $this->save($element);
		if($result){
			$class->handleTranslations('banner',$result,$element);
		}
		return $result;
	}

	function save(&$element){
		$status = parent::save($element);

		if(!$status){
			return false;
		}
		if(empty($element->banner_id)){
			$element->banner_id = $status;
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'banner_id';
			$orderClass->table = 'banner';
			$orderClass->orderingMap = 'banner_ordering';
			$orderClass->reOrder();
		}
		return $status;
	}
}
